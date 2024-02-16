<?php

namespace App\SDK\Payquinn\Wallet\src;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
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
            "paymentMethod" => $payload['payment_method'],
            "operator" => $payload['operator'],
            "senderName" => $payload['sender_name'],
            "senderPhoneNumber" => $payload['sender_mobile'],
            "amount" => $payload['amount'],
            "receiverName" => $payload['receiver_name'],
            "receiverPhoneNumber" => $payload['receiver_mobile'],
            "uriNotify" => $payload['notify_url'],
        ];
        return $this->httpRequest('/transfer/' . $this->credentials['partner_name'], $data);
    }

    /**
     * @param  array  $payload
     * @return array
     * @description This method is called when a payment is checked at the processor side
     */
    public function check(array $payload): array
    {
        return $this->httpRequest('/transfer-status/' . $payload['transaction_id'], $payload, 'get');
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
            $responseRequest = $this->request($url, $data, $method);
            return $this->getResponse($responseRequest, $data);
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
        $responseToken = $this->getToken();
        throw_if($responseToken['error'] ?? false, \Exception::class, json_encode($responseToken));
        $options = [
            'headers' => [
                'Authorization' => 'Bearer '.$responseToken['token'],
                'Content-Type' => 'application/json',
            ], 'json' => $payload
        ];
        try {
            if ($method == 'post') {
                $response = $client->post($url, $options);
            } else {
                $response = $client->get($url, $options);
            }
            $jsonResponse = json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            $jsonResponse = json_decode($e->getResponse()->getBody(), true);
            throw_if($jsonResponse['error'] ?? false, \Exception::class, json_encode($jsonResponse));
        }
        return $jsonResponse;
    }


    private function getResponse(array $response, array $payload): array
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

    private function getToken()
    {
        $baseUrl = $this->credentials['base_url'];
        $client = new Client([
            'base_uri' => $baseUrl,
        ]);
        $payload = [
            "userName" => $this->credentials['username'],
            "password" => $this->credentials['password'],
        ];
        try {
            $response = $client->post("/token", ['json' => $payload]);
            $jsonResponse = json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            $jsonResponse = json_decode($e->getResponse()->getBody(), true);
            throw_if($jsonResponse['error'] ?? false, \Exception::class, json_encode($jsonResponse));
        }
        return $jsonResponse;
    }
}
