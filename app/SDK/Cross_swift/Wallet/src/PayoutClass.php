<?php

namespace App\SDK\Cross_swift\Wallet\src;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class PayoutClass implements PayoutInterface
{

    protected string $tag;
    protected array $credentials;
    protected $channel;

    public function __construct($tag, $credentials, $channel = ['mobile_money'])
    {
        $this->tag = $tag;
        $this->credentials = $credentials;
        $this->channel = $channel;
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
        return $this->httpRequest('/v2/api/Cashout/GetTxnStatus', $data,);
    }

    /**
     * @param  array  $payload
     * @return array
     * @description This method is called to get a balance
     */
    public function balance(array $payload): array
    {
        $data = [
            "app_id" => $this->credentials['app_id'],
            "app_key" => $this->credentials['app_key'],
        ];
        return $this->httpRequest('/v2/api/Cashout/CheckAvailableBalance', $data, 'post', 'balance');
    }

    /**
     * @param  array  $payload
     * @return array
     * @description This method is called to check an account
     */
    public function checkAccount(array $payload): array|null
    {
        $data = [
            "app_id" => $this->credentials['app_id'],
            "app_key" => $this->credentials['app_key'],
            "mobile" => $payload['phone_number'],
            "network" => $payload['phone_operator'],
        ];
        return $this->httpRequest('/v2/api/Cashout/GetAccountProfile', $data, 'get', 'account');
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
    private function httpRequest(string $url, $data = [], $method = 'post', $action = 'do_check'): array|null
    {
        try {
            Log::stack([$this->channel])->info($this->tag.'[start]', [
                'payload' => $data,
            ]);
            $responseRequest = $this->request($url, $data, $method);
            $response = match ($action) {
                'balance' => $this->getResponseBalance($responseRequest),
                'account' => $this->getResponseAccount($responseRequest),
                default => $this->getResponseDoAndCheck($responseRequest,
                    $data),
            };
            Log::stack([$this->channel])->info($this->tag.'[end]'.'success', [
                'payload' => $response,
            ]);
            return $response;
        } catch (\Exception $e) {
            $jsonResponse = json_decode($e->getMessage(), true);
            $status = Utilities::phoneNumberStatus('CO_SUCCESS');
            $response = [
                'status' => $status,
                'type' => 'MOBILE MONEY',
                'transaction_id' => $data['merchant_ref'] ?? '',
                'message' => Utilities::listStatusCode()[$status]['message'],
                'partner_transaction_id' => $data['merchant_ref'] ?? '',
                'partner_payment_id' => $jsonResponse['operator_transaction_id'] ?? '',
                'data' => [
                    'instruction' => $status == 400 ? $jsonResponse['status_message'] : Utilities::listStatusCode()[$status]['description']
                ],
                'orig_data' => $jsonResponse,
            ];
            Log::stack([$this->channel])->info($this->tag.'[end]'.'error', [
                'payload' => $response,
            ]);
            return $response;
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
                $response = $client->post($url, [
                    'json' => $payload
                ]);
                $jsonResponse = json_decode($response->getBody(), true);
            } else {

                $newPayload = json_encode($payload);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $baseUrl.$url);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $newPayload);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: '.strlen($newPayload),
                ]);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);
                $jsonResponse = json_decode($response, true);
            }

        } catch (RequestException $e) {
            $jsonResponse = json_decode($e->getResponse()->getBody(), true);
            throw_if($jsonResponse['status_code'] != 'CO_SUBMITTED' || $jsonResponse['status_code'] != 'CO_SUCCESS',
                \Exception::class, json_encode($jsonResponse));
        }
        return $jsonResponse;
    }


    private function getResponseDoAndCheck(array $response, array $payload): array
    {
        $status = Utilities::phoneNumberStatus($response['status_code']);
        return [
            'status' => $status,
            'type' => 'MOBILE MONEY',
            'transaction_id' => $payload['merchant_ref'] ?? '',
            'partner_transaction_id' => $payload['merchant_ref'] ?? '',
            'message' => Utilities::listStatusCode()[$status]['message'],
            'partner_payment_id' => $response['merchant_ref'] ?? '',
            'data' => [
                'instruction' => $status == 400 ? $response['status_message'] : Utilities::listStatusCode()[$status]['description']
            ],
            'orig_data' => $response,
        ];
    }

    private function getResponseBalance(array $response): array
    {
        return [
            'status' => 200,
            'type' => 'MOBILE MONEY',
            'message' => "SUCCESS",
            'data' => ['balance' => $response['available_Balance']],
            'orig_data' => $response,
        ];
    }

    private function getResponseAccount(array $response): null|array
    {
        return $response['firstname'] == null || $response['firstname'] == '' || $response['firstname'] == '2051' ? null : [
            'status' => 200,
            'type' => 'MOBILE MONEY',
            'message' => "SUCCESS",
            'data' => [
                'name' => $response['firstname'],
                'reference' => null,
            ],
            'orig_data' => $response,
        ];
    }
}
