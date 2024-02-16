<?php


namespace App\SDK\Ben\Wallet\tests;

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
            'currency' => "GHS",
            'amount' => '3.5',
            'channel' => 'MTN',
            'transaction_id' => (Str::uuid())->toString(),
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

    public function testBalance()
    {
        $this->setUp();
        $payload = [];
        $result = $this->payoutClass->balance($payload);
        $this->assertIsString($result['type']);
        $this->assertArrayHasKey($result['status'], Utilities::listStatusCode());
//        $this->assertArrayHasKey('balance', $result['data']);
//        $this->assertArrayHasKey('currency', $result['data']);
        $this->assertArrayHasKey('orig_data', $result);
    }

    protected function setUp(): void
    {
        $this->payoutClass = new PayoutClass('test_tag', [
            'base_url' => 'https://devsrv.cspay.app/v2/api',
            'app_id' => '9612649838',
            'app_key' => '06754043',
        ]);
    }
}