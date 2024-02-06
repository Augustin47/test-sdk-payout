<?php

namespace App\SDK\payouts\V2\Sandbox\src;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
        $status = Utilities::phoneNumberStatus($payload['receiver_account'])[0];
        // Connect to magmaSend Api with email and password
        $connectResponse = $this->connectToMagmaSendApi();
        if (!$connectResponse['code'] == "00") {
            return [
                'status' => 400,
                'message' => "Une erreur s'est produite veuillez réessayer plus tard"
            ];
        }
        // Create Sender reference
        $sender = [
            "type" => "sender",
            "first_name" => $payload['receiver_first_name'],
            "last_name" => $payload['receiver_last_name'],
            "phone_number" => $payload['receiver_account']
        ];
        $senderResponse = $this->createReference($sender, $connectResponse['access_token']);
        if (!$senderResponse['code'] == 201) {
            return [
                'status' => 400,
                'message' => "Une erreur s'est produite veuillez réessayer plus tard"
            ];
        }
        // Create Receiver reference
        $receiver = [
            "type" => "receiver",
            "first_name" => $payload['receiver_first_name'],
            "last_name" => $payload['receiver_last_name'],
            "phone_number" => $payload['receiver_account']
        ];
        $receiverResponse = $this->createReference($receiver, $connectResponse['access_token']);
        if (!$receiverResponse['code'] == 201) {
            return [
                'status' => 400,
                'message' => "Une erreur s'est produite veuillez réessayer plus tard"
            ];
        }
        // Create a payout transaction
        $payoutPayload = [
            "transaction_id" => $payload['merchant_transaction_id'],
            "notify_url" => $payload['webhook_url'],
            "payment_method" => $this->matchPaymentMethod($payload['channel']),
            "payout_amount" => $payload['amount'],
            "payout_currency" => $payload['currency'],
            "payout_country" => $payload['country_code'],
            "relation" => "love",
            "sender_reference" => $senderResponse['customer']['reference'],
            "receiver_reference" => $receiverResponse['customer']['reference'],
            "receiver_phone_number" => $payload['receiver_account'],
        ];
        $payoutResponse = $this->createPayoutTransaction($payoutPayload, $connectResponse['access_token']);
        if (!$payoutResponse['code'] == 201) {
            return [
                'status' => 400,
                'message' => "Une erreur s'est produite veuillez réessayer plus tard"
            ];
        }
        // Confirm Payout
        $confirmPayoutResponse = $this->confirmPayout($payoutResponse['transaction']['reference'],
            $connectResponse['access_token']);
        if (!$confirmPayoutResponse['code'] == 200) {
            return [
                'status' => 400,
                'message' => "Une erreur s'est produite veuillez réessayer plus tard"
            ];
        }
        // Return response
        return [
            'status' => $status,
            'type' => 'DIRECT',
            'transaction_id' => $confirmPayoutResponse['transaction']['transaction_id'],
            'message' => Utilities::listStatusCode()[$status]['message'],
            'partner_transaction_id' => $confirmPayoutResponse['transaction']['transaction_id'],
            'partner_payment_id' => $confirmPayoutResponse['transaction']['transaction_id'],
            'data' => [
                'instruction' => 'Veuillez confirmer votre paiement sur votre téléphone mobile en composant la syntaxe #120# puis suivre les indications'
            ],
            'orig_data' => $confirmPayoutResponse,
        ];
    }

    /**
     * @param  array  $payload
     * @return array
     * @description This method is called when a payment is checked at the processor side
     */
    public function check(array $payload): array
    {
        $status = Utilities::phoneNumberStatus($payload['phone_number'])[1];
        // Connect to magmaSend Api with email and password
        $connectResponse = $this->connectToMagmaSendApi();
        if (!$connectResponse['code'] == "00") {
            return [
                'status' => 400,
                'message' => "Une erreur s'est produite veuillez réessayer plus tard"
            ];
        }
        // Check transaction reference {{baseUrl}}/v2/transaction/{{transaction_reference}}
        $payoutResponse = $this->checkTransactionByReference($payload['transaction_id'], $connectResponse['access_token']);
        if (!$payoutResponse['code'] == 200) {
            return [
                'status' => 400,
                'message' => "Une erreur s'est produite veuillez réessayer plus tard"
            ];
        }
        return [
            'status' => $status,
            'type' => 'DIRECT',
            'transaction_id' => $payoutResponse['transaction']['transaction_id'],
            'message' => Utilities::listStatusCode()[$status]['message'],
            'partner_transaction_id' => $payoutResponse['transaction']['transaction_id'],
            'partner_payment_id' => $payoutResponse['transaction']['transaction_id'],
            'data' => [
                'instruction' => '...'
            ],
            'orig_data' => $payoutResponse,
        ];
    }

    private function matchPaymentMethod(string $payment_method)
    {
        return match ($payment_method) {
            'mobile_money' => 'MobileMoney',
            default => 'MobileMoney',
        };
    }

    private function connectToMagmaSendApi()
    {
        return Cache::remember('TOKEN_CONNEXION', now()->addSeconds(68090), function () {
            $baseUrl = $this->credentials['base_url'];
            $email = $this->credentials['email'];
            $password = $this->credentials['password'];
            $response = Http::post($baseUrl.'/v1/oauth/login', [
                'email' => $email,
                'password' => $password,
            ]);
            return $response->json();
        });

    }

    private function createReference(array $payload, string $token)
    {
        $baseUrl = $this->credentials['base_url'];
        $response = Http::withToken($token)->post($baseUrl.'/v2/customer', $payload);

        return $response->json();
    }


    private function createPayoutTransaction(array $payload, string $token)
    {
        $baseUrl = $this->credentials['base_url'];
        $response = Http::withToken($token)->post($baseUrl.'/v2/transaction', $payload);
        return $response->json();
    }

    private function confirmPayout(string $reference, $token)
    {
        $baseUrl = $this->credentials['base_url'];
        $response = Http::withToken($token)->post($baseUrl.'/v2/transaction/confirm', [
            'reference' => $reference,
        ]);
        return $response->json();
    }

    private function checkTransactionByReference(string $reference, $token)
    {
        $baseUrl = $this->credentials['base_url'];
        $response = Http::withToken($token)->get($baseUrl.'/v2/transaction/'.$reference);
        return $response->json();
    }
}
