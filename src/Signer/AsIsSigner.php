<?php
namespace Unifreak\Fetcher\Signer;

class AsIsSigner implements Signer
{
    public function sign(array $payload)
    {
        return $payload;
    }
}