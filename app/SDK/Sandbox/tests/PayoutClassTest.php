<?php


namespace App\SDK\Sandbox\tests;

use App\SDK\Sandbox\src\PayoutClass;
use App\SDK\Sandbox\src\Utilities;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

class PayoutClassTest extends TestCase
{
    protected PayoutClass $payoutClass;

    public function testDo()
    {
        $this->setUp();
        $payload = [
            'amount' => 200,
            'otp_code' => null,
            'phone_number' => '+2250707000201',
            "currency" => "XOF",
            'transaction_id' => (Str::uuid())->toString(),
            'description' => 'Merchant Payment',
            'callback_url' => 'https://myurl.com',
        ];

        $result = $this->payoutClass->do($payload);
        $this->assertIsString($result['type']);
        $this->assertSame($payload['transaction_id'], $result['transaction_id']);
        $this->assertArrayHasKey($result['status'], Utilities::listStatusCode());
        $this->assertIsString($result['partner_transaction_id']);
        $this->assertIsString($result['partner_payment_id']);
        $this->assertArrayHasKey('instruction', $result['data']);
        $this->assertArrayHasKey('orig_data', $result);
        return $result;
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
        return $result;
    }

    public function testBalance()
    {
        $this->setUp();
        $payload = [];
        $result = $this->payoutClass->balance($payload);
        $this->assertIsString($result['type']);
        $this->assertArrayHasKey($result['status'], Utilities::listStatusCode());
        $this->assertArrayHasKey('balance', $result['data']);
        $this->assertArrayHasKey('currency', $result['data']);
        $this->assertArrayHasKey('orig_data', $result);
        return $result;
    }

    protected function setUp(): void
    {
        $this->payoutClass = new PayoutClass('test_tag', [
            'base_url' => 'https://api-sandbox.magmasend.com',
            'email' => 'hello@email.com',
            'password' => 'hello@email.com',
            'country' => 'CI',
        ]);
    }
}
