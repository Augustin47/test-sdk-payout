<?php

namespace App\SDK\Payquinn\Bank_transfer\src;

interface PayoutInterface
{
    public function __construct(string $tag, array $credentials, $channel);

    public function do(array $payload): array;

    public function check(array $payload): array;

}
