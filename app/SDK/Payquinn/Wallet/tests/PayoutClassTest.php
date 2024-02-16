<?php


namespace App\SDK\Payquinn\Wallet\tests;

use App\SDK\Ben\Wallet\src\PayoutClass;
use App\SDK\Ben\Wallet\src\Utilities;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

class PayoutClassTest extends TestCase
{
    protected PayoutClass $payoutClass;

    public function testDo()
    {
        $this->setUp();
        $payload = [
            'sender_name' => "Helios Oprhus",
            'sender_mobile' => "+233264391256",
            'receiver_name' => 'Gokus Pokus',
            "receiver_mobile" => "+233264371234",
            "amount" => 300,
            "operator" => 'orange-ci',
            "payment_method" => 'mobile-money-transfer',
            'notify_url' => "https://webhook.site/9beefee6-5d28-4732-831b-5b2718da2d5e",
        ];

        $result = $this->payoutClass->do($payload);
        $this->assertIsString($payload['currency']);
        $this->assertIsString($payload['amount']);
        $this->assertIsString($payload['channel']);
        $this->assertIsString($payload['transaction_id']);
        $this->assertIsString($result['type']);
        $this->assertSame($payload['transaction_id'], $result['transaction_id']);
        $this->assertArrayHasKey($result['status'], Utilities::listStatusCode());
        $this->assertIsString($result['partner_transaction_id']);
        $this->assertIsString($result['partner_payment_id']);
        $this->assertArrayHasKey('instruction', $result['data']);
        $this->assertArrayHasKey('orig_data', $result);
    }

    public function testCheck()
    {
        $this->setUp();
        $payload = [
            'transaction_id' => "4ae7befc-d310-408e-99a9-299205f39b7e",
        ];
        $result = $this->payoutClass->check($payload);
        $this->assertIsString($result['type']);
        $this->assertSame($payload['transaction_id'], $result['transaction_id']);
        $this->assertArrayHasKey($result['status'], Utilities::listStatusCode());
        $this->assertIsString($result['partner_transaction_id']);
        $this->assertIsString($result['partner_payment_id']);
        $this->assertArrayHasKey('instruction', $result['data']);
        $this->assertArrayHasKey('orig_data', $result);
    }



    protected function setUp(): void
    {
        $this->payoutClass = new PayoutClass('test_tag', [
            'base_url' => 'https://prod.payqin.com/api/merchant',
            'username' => 'string',
            'password' => 'string',
            'partner_name' => 'string',
        ]);
    }
}
