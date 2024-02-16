<?php


namespace App\SDK\Payquinn\Bank_transfer\tests;

use App\SDK\Ben\Bank_transfer\src\PayoutClass;
use App\SDK\Ben\Bank_transfer\src\Utilities;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;
use Faker\Factory as Faker;

class PayoutClassTest extends TestCase
{
    protected PayoutClass $payoutClass;

    public function testDo()
    {
        $this->setUp();
        $faker = Faker::create();
        $payload = [
            'sender_name' => "Helios Oprhus",
            'sender_mobile' => "+233264391256",
            'receiver_name' => 'Gokus Pokus',
            "receiver_mobile" => "+233264371234",
            'receiver_iban' => $faker->iban(),
            "amount" => 300,
            "operator" => 'orange-ci',
            "payment_method" => 'mobile-money-transfer',
            'notify_url' => "https://webhook.site/9beefee6-5d28-4732-831b-5b2718da2d5e",
        ];

        $result = $this->payoutClass->do($payload);
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
