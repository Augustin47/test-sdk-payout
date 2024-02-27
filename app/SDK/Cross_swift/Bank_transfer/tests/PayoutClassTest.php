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
            'receiver_bank_branch_sort_code' => "130149",
            'receiver_bank_code' => "300312",
            'receiver_bank_name' => "Ecobank Ghana",
            'receiver_bank_account' => "1441004602059",
            'receiver_bank_account_title' => "MARIAM LETICIA OUATTARA MERESSO YAO",
            'transaction_id' => (Str::uuid())->toString(),
        ];

        $result = $this->payoutClass->do($payload);
        $this->assertIsString($payload['currency']);
        $this->assertIsString($payload['amount']);
        $this->assertIsString($payload['receiver_bank_branch_sort_code']);
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
            'transaction_id' => "e6491c6b-2689-4207-9534-69c641b0d720",
//            'transaction_id' => "645e2d3e-1647-481b-8f12-65c8f4db864f",
//            'transaction_id' => "89b45787-24f1-4adc-8f6d-bef25a4f4d5f",
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

    public function testBalance()
    {
        $this->setUp();
        $payload = [];
        $result = $this->payoutClass->balance($payload);
        $this->assertIsString($result['type']);
        $this->assertArrayHasKey($result['status'], Utilities::listStatusCode());
        $this->assertArrayHasKey('balance', $result['data']);
        $this->assertArrayHasKey('orig_data', $result);
    }

    public function testAccount()
    {
        $this->setUp();
        $payload = [
            "bank_name" => "Ecobank Ghana",
            "bank_branch_sort_code" => "130149",
            "bank_code" => "300312",
            "bank_account" => "1441004602059",
            "bank_account_title" => "MARIAM LETICIA OUATTARA MERESSO YAO",
        ];
        $result = $this->payoutClass->checkAccount($payload);
        $this->assertIsString($result['type']);
        $this->assertArrayHasKey($result['status'], Utilities::listStatusCode());
        $this->assertArrayHasKey('name', $result['data']);
        $this->assertArrayHasKey('reference', $result['data']);
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
