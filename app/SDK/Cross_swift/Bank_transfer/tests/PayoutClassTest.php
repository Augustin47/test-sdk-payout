<?php


namespace App\SDK\Cross_swift\Bank_transfer\tests;

use App\SDK\Cross_swift\Bank_transfer\src\PayoutClass;
use App\SDK\Cross_swift\Bank_transfer\src\Utilities;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

class PayoutClassTest extends TestCase
{
    protected PayoutClass $payoutClass;

    public function testDo()
    {
        $this->setUp();
        $payload = [
            'sender_name' => "Akpagni MAGMA",
            'sender_mobile' => "+2250789234939",
            'receiver_name' => 'OUATTARA LETICIA',
            "receiver_mobile" => "+233535183103",
            'currency' => "GHS",
            'amount' => '2',
            'receiver_bank_branch_code' => "130116",
            'receiver_bank_name' => "Ecobank Ghana",
            'receiver_bank_account' => "1441004602059",
            'receiver_bank_account_title' => "MARIAM LETICIA OUATTARA MERESSO YAO",
            'transaction_id' => (Str::uuid())->toString(),
        ];

        $result = $this->payoutClass->do($payload);
        print_r("testDo");
        print_r($result);
        $this->assertIsString($payload['currency']);
        $this->assertIsString($payload['amount']);
        $this->assertIsString($payload['receiver_bank_branch_code']);
        $this->assertIsString($payload['receiver_bank_account']);
        $this->assertIsString($payload['receiver_bank_account_title']);
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
            'transaction_id' => "645e2d3e-1647-481b-8f12-65c8f4db864f",
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
