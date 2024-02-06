<?php

namespace App\SDK\Sandbox\src;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayoutClass implements PaymentInterface
{

    protected string $tag;
    protected array $credentials;

    public function __construct($tag, $credentials, $channel = 'mobile_money')
    {
        $this->tag = $tag;
        $this->credentials = $credentials;
    }

    /**
     * @param  array  $payload
     * @return array
     * @description This method is called when a payment is made at the processor side
     */
    public function do(array $payload): array
    {
        $data = [
            "phone_number" => $payload['phone_number'],
            "country" => "CI",
            "operator" => "ORANGE_CI",
            "amount" => $payload['amount'],
            "currency" => $payload['currency'],
            "transaction_id" => $payload['transaction_id'],
            "notify_url" => $payload['callback_url'],
        ];
        return $this->httpRequest('/v1/transfer', $data);
    }

    /**
     * @param  array  $payload
     * @return array
     * @description This method is called to get a balance in country
     */
    public function balance(array $payload): array
    {
        return $this->httpRequest('/v1/balance/all', $payload, 'get');
    }

    /**
     * @param  array  $payload
     * @return array
     * @description This method is called when a payment is checked at the processor side
     */
    public function check(array $payload): array
    {
        return $this->httpRequest('/v1/transfer/'.$payload['transaction_id'], $payload, 'get');
    }

    /**
     * Make an HTTP request to the Magma Send API.
     *
     * @param  string  $url  The endpoint URL to which the request is made.
     * @param  array  $data  The data to be sent with the request.
     * @param  string  $method  The HTTP method to be used for the request (default is 'post').
     *
     * @return array The response data from the HTTP request.
     */
    private function httpRequest(string $url, $data = [], $method = 'post'): array
    {
        try {
            $responseAuth = $this->connectToMagmaSendApi();
            $responseRequest = $this->request($url, $data, $responseAuth['access_token'], $method);
            return @$responseRequest['balance'] != null ? $this->getResponseBalance($responseRequest,
                $data) : $this->getResponseDoAndCheck($responseRequest, $data);

        } catch (\Exception $e) {
            $jsonResponse = json_decode($e->getMessage(), true);
            return [
                'status' => @$jsonResponse['code'],
                'type' => 'DIRECT',
                'transaction_id' => @$data['transaction_id'] ?? '',
                'message' => @$jsonResponse['status'] ?? "FAILED",
                'partner_transaction_id' => @$data['transaction_id'] ?? '',
                'partner_payment_id' => @$jsonResponse['operator_transaction_id'] ?? '',
                'data' => [
                    'instruction' => @$jsonResponse['comment'] ?? ''
                ],
                'orig_data' => $jsonResponse,
            ];
        }
    }

    /**
     * Connect to the Magma Send API and retrieve an authentication token.
     *
     * @return array The JSON response containing the authentication token.
     *
     * @throws \Exception If the response code in the JSON is not '00'.
     */
    private function connectToMagmaSendApi()
    {
        return Cache::remember('TOKEN_CONNEXION_'.$this->credentials['email'], now()->addSeconds(68090), function () {
            $baseUrl = $this->credentials['base_url'];
            $email = $this->credentials['email'];
            $password = $this->credentials['password'];

            $response = Http::post($baseUrl.'/v1/oauth/login', [
                'email' => $email,
                'password' => $password,
            ]);

            $jsonResponse = $response->json();
            throw_if($jsonResponse['code'] != '00', \Exception::class, json_encode($jsonResponse));
            return $jsonResponse;
        });

    }

    /**
     * Perform an HTTP request.
     *
     * @param  string  $url  The endpoint URL to which the request is made.
     * @param  array  $payload  The data to be sent with the request.
     * @param  string  $token  The authentication token to be included in the request header.
     * @param  string  $method  The HTTP method to be used for the request (default is 'post').
     *
     * @return array The JSON response from the HTTP request.
     *
     * @throws \Exception If the response code in the JSON is not '00'.
     */
    private function request(string $url, array $payload, string $token, string $method = 'post')
    {
        $baseUrl = $this->credentials['base_url'];
        $builder = Http::withToken($token);
        if ($method == 'post') {
            $response = $builder->post($baseUrl.$url, $payload);
        } else {
            $response = $builder->get($baseUrl.$url, $payload);
        }
        $jsonResponse = $response->json();
        throw_if($jsonResponse['code'] != '00', \Exception::class, json_encode($jsonResponse));
        return $jsonResponse;
    }


    private function getResponseDoAndCheck(array $response, array $payload): array
    {
        $responseCode = @$response['code'] != null ? ((int) $response['code'] == 0 ? 100 : (int) $response['code']) : 300;
        $status = @$payload['phone_number'] != null ? Utilities::phoneNumberStatus($payload['phone_number'])[0] : $responseCode;
        return [
            'status' => $status,
            'type' => 'DIRECT',
            'transaction_id' => @$payload['transaction_id'] ?? '',
            'partner_transaction_id' => @$payload['transaction_id'] ?? '',
            'message' => Utilities::listStatusCode()[$status]['message'],
            'partner_payment_id' => @$response['operator_transaction_id'] ?? '',
            'data' => [
                'instruction' => Utilities::listStatusCode()[$status]['description']
            ],
            'orig_data' => $response,
        ];
    }

    private function getResponseBalance(array $response, array $payload): array
    {
        $response['balance'] = collect($response['balance'])->where('country',
            $this->credentials['country'])->map(function ($item) {
            return [
                'balance' => $item['balance'],
                'currency' => $item['currency'],
            ];
        })->first();
        return [
            'status' => 200,
            'type' => 'DIRECT',
            'message' => "SUCCESS",
            'data' => $response['balance'],
            'orig_data' => $response,
        ];
    }

}
