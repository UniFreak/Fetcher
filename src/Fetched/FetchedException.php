<?php
namespace Unifreak\Fetcher\Fetched;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;

class FetchedException extends Fetched
{
    private static $message = 'something went wrong...';

    private $exception = null;
    private $messagePrefixOf = [
        ClientException::class => '[client error] ',
        ServerException::class => '[server error] ',
        ConnectException::class => '[connect error] ',
        'default' => '[error] '
    ];

    public function __construct(array $api, \Exception $e)
    {
        $this->api = $api;
        $this->exception = $e;
    }

    public function ok()
    {
        return false;
    }

    public function code()
    {
        return ! empty($this->exception->getCode())
            ? $this->exception->getCode()
            : 500;
    }

    public function message()
    {
        $prefix = isset($this->messagePrefixOf[get_class($this->exception)])
                ? $this->messagePrefixOf[get_class($this->exception)]
                : $this->messagePrefixOf['default'];
        return $prefix . $this->exception->getMessage();
    }

    public function data()
    {
        return [];
    }
}
