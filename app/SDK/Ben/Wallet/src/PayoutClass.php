<?php

namespace App\SDK\Ben\Wallet\src;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PayoutClass implements PayoutInterface
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
            "app_id" => $this->credentials['app_id'],
            "app_key" => $this->credentials['app_key'],
            "transaction_date" => Carbon::now()->toDateTimeString(),
            "expiry_date" => Carbon::now()->addDay()->toDateTimeString(),
            "transaction_type" => "local",
            "Payment_mode" => "MMT",
            "payer_name" => $payload['sender_name'],
            "payer_mobile" => $payload['sender_mobile'],
            "payee_name" => $payload['receiver_name'],
            "Payee_mobile" => $payload['receiver_mobile'],
            "currency" => $payload['currency'],
            "amount" => $payload['amount'],
            "mobile_network" => $payload['channel'],
            "merchant_ref" => $payload['transaction_id'],
        ];
        return $this->httpRequest('/v2/api/Cashout/CreateCashout', $data);
    }

    /**
     * @param  array  $payload
     * @return array
     * @description This method is called when a payment is checked at the processor side
     */
    public function check(array $payload): array
    {
        $data = [
            "app_id" => $this->credentials['app_id'],
            "app_key" => $this->credentials['app_key'],
            "merchant_ref" => $payload['transaction_id'],
        ];
        return $this->httpRequest('/v2/api/Cashout/CheckStatus', $data, 'get');
    }

    /**
     * @param  array  $payload
     * @return array
     * @description This method is called to get a balance
     */
    public function balance(array $payload): array
    {
        return $this->httpRequest('/v2/api/Cashout/CheckAvailableBalance', $payload, 'post', 'balance');
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
    private function httpRequest(string $url, $data = [], $method = 'post', $action = 'do_check'): array
    {
        try {
            $responseRequest = $this->request($url, $data, $method);
            return $action == 'do_check' ? $this->getResponseDoAndCheck($responseRequest,
                $data) : $this->getResponseBalance($responseRequest);
        } catch (\Exception $e) {
            $jsonResponse = json_decode($e->getMessage(), true);
            $status = Utilities::phoneNumberStatus($jsonResponse['status_code']);
            return [
                'status' => $status,
                'type' => 'DIRECT',
                'transaction_id' => $data['merchant_ref'] ?? '',
                'message' => Utilities::listStatusCode()[$status]['message'],
                'partner_transaction_id' => $data['merchant_ref'] ?? '',
                'partner_payment_id' => $jsonResponse['operator_transaction_id'] ?? '',
                'data' => [
                    'instruction' => Utilities::listStatusCode()[$status]['description']
                ],
                'orig_data' => $jsonResponse,
            ];
        }
    }

    /**
     * Perform an HTTP request.
     *
     * @param  string  $url  The endpoint URL to which the request is made.
     * @param  array  $payload  The data to be sent with the request.
     * @param  string  $method  The HTTP method to be used for the request (default is 'post').
     *
     * @return array The JSON response from the HTTP request.
     *
     * @throws \Exception If the response code in the JSON is not 'CO_SUBMITTED'.
     */
    private function request(string $url, array $payload, string $method = 'post')
    {
        $baseUrl = $this->credentials['base_url'];
        $client = new Client([
            'base_uri' => $baseUrl,
        ]);

        try {
            if ($method == 'post') {
                $response = $client->post($url, ['json' => $payload]);
            } else {
                $response = $client->get($url, ['json' => $payload]);
            }
            $jsonResponse = json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            $jsonResponse = json_decode($e->getResponse()->getBody(), true);
            throw_if($jsonResponse['status_code'] != 'CO_SUBMITTED', \Exception::class, json_encode($jsonResponse));
        }

        return $jsonResponse;
    }


    private function getResponseDoAndCheck(array $response, array $payload): array
    {
        $status = Utilities::phoneNumberStatus($response['status_code']);
        return [
            'status' => $status,
            'type' => 'DIRECT',
            'transaction_id' => $payload['merchant_ref'] ?? '',
            'partner_transaction_id' => $payload['merchant_ref'] ?? '',
            'message' => Utilities::listStatusCode()[$status]['message'],
            'partner_payment_id' => $response['merchant_ref'] ?? '',
            'data' => [
                'instruction' => Utilities::listStatusCode()[$status]['description']
            ],
            'orig_data' => $response,
        ];
    }

    private function getResponseBalance(array $response): array
    {
        return [
            'status' => 200,
            'type' => 'DIRECT',
            'message' => "SUCCESS",
            'data' => $response['available_Balance'],
            'orig_data' => $response,
        ];
    }
}
