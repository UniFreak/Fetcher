<?php
namespace Unifreak\Fetcher\Tests;


use Unifreak\Fetcher\Signer\Signer;

class TestSigner implements Signer
{
    public function sign(array $payload)
    {
        return $payload + ['_sn' => md5(json_encode($payload))];
    }
}