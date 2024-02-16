<?php


namespace App\SDK\Cross_swift\Wallet\tests;

use App\SDK\Cross_swift\Wallet\src\PayoutClass;
use App\SDK\Cross_swift\Wallet\src\Utilities;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

class PayoutClassTest extends TestCase
{
    protected PayoutClass $payoutClass;

    public function testDo()
    {
        // MTN +233535183103
        // AIRTELTIGO +233267598629
        // VODAFONE +233205767493
        $this->setUp();
        $payload = [
            'sender_name' => "Akpagni MAGMA",
            'sender_mobile' => "+2250789234939",
            'receiver_name' => "OUATTARA LETICIA",
            "receiver_mobile" => "+233205767493",
            'currency' => "GHS",
            'amount' => '1',
            'channel' => 'VODAFONE',
            'transaction_id' => (Str::uuid())->toString(),
        ];

        $result = $this->payoutClass->do($payload);
        print_r("testDo");
        print_r($result);
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
            'transaction_id' => "ef808917-bd45-4141-96db-9ac926a7b1d5",
//            'transaction_id' => "fa824e56-cd2d-4716-ba05-f07550f3e1e2",
//            'transaction_id' => "96eb9555-9ece-4fcc-a42d-b86740b63188",
//            'transaction_id' => "101f3647-1730-4990-8800-e28d1c45ceb1",
        ];
        $result = $this->payoutClass->check($payload);
        print_r("testCheck");
        print_r($result);
        $this->assertIsString($result['type']);
        $this->assertSame($payload['transaction_id'], $result['transaction_id']);
        $this->assertArrayHasKey($result['status'], Utilities::listStatusCode());
        $this->assertIsString($result['partner_transaction_id']);
        $this->assertIsString($result['partner_payment_id']);
        $this->assertArrayHasKey('instruction', $result['data']);
        $this->assertArrayHasKey('orig_data', $result);
    }

    public function testBalance()
    {
        $this->setUp();
        $payload = [];
        $result = $this->payoutClass->balance($payload);
        print_r("testBalance");
        print_r($result);
        $this->assertIsString($result['type']);
        $this->assertArrayHasKey($result['status'], Utilities::listStatusCode());
        $this->assertArrayHasKey('balance', $result['data']);
        $this->assertArrayHasKey('orig_data', $result);
    }

    protected function setUp(): void
    {
        $this->payoutClass = new PayoutClass('test_tag', [
            'base_url' => 'https://api.cs-pay.app',
            'app_id' => '1408942785',
            'app_key' => '64337243',
        ]);
    }
}
