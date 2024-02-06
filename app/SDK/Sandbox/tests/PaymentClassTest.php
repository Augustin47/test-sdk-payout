<?php

namespace App\SDK\Sandbox\tests;

use App\SDK\Sandbox\src\PaymentClass;
use App\SDK\Sandbox\src\Utilities;
use PHPUnit\Framework\TestCase;

class PaymentClassTest extends TestCase
{
    protected PaymentClass $paymentClass;

    public function testDo(): void
    {
        $payload = [
            'amount' => '200',
            'otp_code' => null,
            'phone_number' => '+2250707070707',
            'transaction_id' => random_int(100000000, 99999999999),
            'description' => 'Merchant Payment',
            'callback_url' => 'https://myurl.com',
        ];

        $result = $this->paymentClass->do($payload);

        $this->assertIsString($result['type']);
        $this->assertSame($payload['transaction_id'], $result['transaction_id']);
        $this->assertArrayHasKey($result['status'], Utilities::listStatusCode());
        $this->assertIsString($result['partner_transaction_id']);
        $this->assertIsString($result['partner_payment_id']);
        $this->assertArrayHasKey('instruction', $result['data']);
        $this->assertArrayHasKey('orig_data', $result);
    }

    public function testCheck(): void
    {
        $payload = [
            'transaction_id' => random_int(100000000, 99999999999),
            'partner_transaction_id' => random_int(100000000, 99999999999),
            'phone_number' => '+2250707070707',
        ];

        $result = $this->paymentClass->check($payload);

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
        $this->paymentClass = new PaymentClass('test_tag', ['test_key' => 'test_value']);
    }
}
