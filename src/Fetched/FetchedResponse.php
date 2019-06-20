<?php
namespace Unifreak\Fetcher\Fetched;

use Illuminate\Support\Arr;
use GuzzleHttp\Psr7\Response;

class FetchedResponse extends Fetched
{
    private $response = null;

    private $code;
    private $message;
    private $data;

    public function __construct(array $api, Response $response)
    {
        // @todo: maybe accessing data & message should also be hooked in
        // @todo: xml, others?
        $this->api = $api;
        $this->response = json_decode((string) $response->getBody(), true);
    }

    public function ok()
    {
        return $this->code() == $this->api['successCode'];
    }

    public function code()
    {
        return Arr::get($this->response, $this->api['codeField']);
    }

    public function message()
    {
        return Arr::get($this->response, $this->api['messageField']);
    }

    public function data()
    {
        return Arr::get($this->response, $this->api['dataField']);
    }
}
