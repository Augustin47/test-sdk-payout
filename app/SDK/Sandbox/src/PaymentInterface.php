<?php

namespace App\SDK\Sandbox\src;

interface PaymentInterface
{
    public function __construct(string $tag, array $credentials, $channel);

    public function do(array $payload): array;

    public function check(array $payload): array;

}
