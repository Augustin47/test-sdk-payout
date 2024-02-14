<?php

namespace App\SDK\Ben\Bank_transfer\src;

interface PayoutInterface
{
    public function __construct(string $tag, array $credentials, $channel);

    public function do(array $payload): array;

    public function check(array $payload): array;

    public function balance(array $payload): array;

}
