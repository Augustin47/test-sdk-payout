<?php

namespace App\SDK\payouts\V2\Sandbox\tests;

use App\SDK\Sandbox\src\PaymentClass;
use App\SDK\Sandbox\src\PayoutClass;
use App\SDK\Sandbox\src\Utilities;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

class PaymentClassTest extends TestCase
{
    protected PaymentClass $paymentClass;
    protected PayoutClass $payoutClass;

    public function testDo()
    {
        $this->setUp();
        $payload = [
            "merchant_transaction_id" => Str::uuid(),
            "amount" => 500,
            "currency" => "XOF",
            "description" => "Payment description",
            "channel" => "mobile_money",
            "country_code" => "CI",
            "receiver_account" => "+2250707000200",
            "receiver_first_name" => "Amidou",
            "receiver_last_name" => "Amada",
            "webhook_url" => "https://webhook.site/dcfa6085-3e7f-469e-9ff6-3c9bc0ed1c1d",
            "custom_field" => "any_string"
        ];

        $result = $this->payoutClass->do($payload);

        $this->assertIsString($result['type']);
        Log::info('test', [$payload['merchant_transaction_id'], $result['transaction_id']]);
        $this->assertSame($payload['merchant_transaction_id']->toString(), $result['transaction_id']);
//        $this->assertArrayHasKey($result['status'], Utilities::listStatusCode());
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
            'transaction_id' => '43259603299',
            'partner_transaction_id' => random_int(100000000, 99999999999),
            'phone_number' => '+2250707070707',
        ];
        $result = $this->payoutClass->check($payload);
        $this->assertIsString($result['type']);
//        $this->assertSame($payload['transaction_id'], $result['transaction_id']);
        $this->assertArrayHasKey($result['status'], Utilities::listStatusCode());
        $this->assertIsString($result['partner_transaction_id']);
        $this->assertIsString($result['partner_payment_id']);
        $this->assertArrayHasKey('instruction', $result['data']);
        $this->assertArrayHasKey('orig_data', $result);
        return $result;
    }

    protected function setUp(): void
    {
//        $this->paymentClass = new PaymentClass('test_tag', ['test_key' => 'test_value']);
        $this->payoutClass = new PayoutClass('test_tag',
            ['base_url' => 'https://api-sandbox.magmasend.com', 'email' => 'v2@magmasend.com', 'password' => 'test']);
    }
}
