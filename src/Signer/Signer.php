<?php
namespace Unifreak\Fetcher\Signer;

interface Signer
{
    /**
     * Sign request payload
     *
     * @author FangHao <email:fanghao@xin.com, phone:15369997084>
     *
     * @param  array  $payload reqeust payload
     *
     * @return array           signed request payload
     */
    public function sign(array $payload);
}