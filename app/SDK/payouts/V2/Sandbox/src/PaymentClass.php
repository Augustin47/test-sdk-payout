<?php

namespace App\SDK\payouts\V2\Sandbox\src;

class PaymentClass implements PaymentInterface
{

    protected string $tag;
    protected array $credentials;

    public function __construct($tag, $credentials, $channel = 'mobile_money')
    {
        $this->tag = $tag;
        $this->credentials = $credentials;
    }

    /**
     * @param array $payload
     * @return array
     * @description This method is called when a payment is made at the processor side
     */
    public function do(array $payload): array
    {
        $status = Utilities::phoneNumberStatus($payload['phone_number'])[0];
        return [
            'status' => $status,
            'type' => 'DIRECT',
            'transaction_id' => $payload['transaction_id'],
            'message' => Utilities::listStatusCode()[$status]['message'],
            'partner_transaction_id' => (string)rand(1000000000, 9999999999),
            'partner_payment_id' => (string)rand(1000000000, 9999999999),
            'data' => [
                'instruction' => 'Veuillez confirmer votre paiement sur votre téléphone mobile en composant la syntaxe #120# puis suivre les indications'
            ],
            'orig_data' => null,
        ];
    }

    /**
     * @param array $payload
     * @return array
     * @description This method is called when a payment is checked at the processor side
     */
    public function check(array $payload): array
    {
        $status = Utilities::phoneNumberStatus($payload['phone_number'])[1];

        return [
            'status' => $status,
            'type' => 'DIRECT',
            'transaction_id' => $payload['transaction_id'],
            'message' => Utilities::listStatusCode()[$status]['message'],
            'partner_transaction_id' => (string)rand(1000000000, 9999999999),
            'partner_payment_id' => (string)rand(1000000000, 9999999999),
            'data' => [
                'instruction' => '...'
            ],
            'orig_data' => null,
        ];
    }
}
