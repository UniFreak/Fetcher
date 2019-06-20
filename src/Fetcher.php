<?php
namespace Unifreak\Fetcher;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Unifreak\Fetcher\Signer\AsIsSigner;
use Unifreak\Fetcher\Fetched\FetchedResponse;
use Unifreak\Fetcher\Fetched\FetchedException;

/**
 * Request api according to api configs and return a fetched result
 */
class Fetcher
{
    private $client = null;
    private $configs = [];
    private $defaults = [
        'method' => 'GET',
        'codeField' => 'code',
        'messageField' => 'message',
        'dataField' => 'data',
        'successCode' => 1,
        'timeout' => 0,
    ];
    private $api = [];


    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Fetch result for api configs
     *
     * @author FangHao <email:fanghao@xin.com, phone:15369997084>
     *
     * @param  array  $configs configs
     * @param  array  $payload get|post request parameters
     * @param  array  $cookies cookies
     *
     * @return Fetched  fetch result
     */
    public function fetch(
        array $configs,
        array $payload = [],
        array $cookies = [],
        array $headers = []
    ) {
        $this->configs = $configs;

        return $this->request($this->api(), $payload, $cookies, $headers);
    }

    /**
     * Make normalized api array
     *
     * @todo: api should be a class
     */
    private function api()
    {
        if (empty($this->configs['url'])) {
            throw new \Exception("invalid api config: no url");
        }
        if (strpos($this->configs['url'], 'http') === false) {
            $this->configs['url'] = 'http://'.$this->configs['url'];
        }

        return array_merge($this->defaults, $this->configs);
    }

    /**
     * Do request for api
     *
     * @author FangHao <email:fanghao@xin.com, phone:15369997084>
     *
     * @param  array  $api     api configs
     * @param  array  $payload request payload
     * @param  array  $cookies cookies
     * @param  array  $headers headers
     *
     * @return Fetched  fetch result
     */
    private function request(array $api, array $payload, array $cookies, array $headers)
    {
        // @?: is this expected so-called `wrap excpetion` ?
        try {
            $options = $this->makeOptionFor($api, $payload, $cookies, $headers);
            $response = $this->client->request($api['method'], $api['url'], $options);
            return new FetchedResponse($api, $response);
        } catch (\Exception $e) {
            return new FetchedException($api, $e);
        }
    }

    /**
     * Make GuzzleHttp Request's option
     *
     * @author FangHao <email:fanghao@xin.com, phone:15369997084>
     *
     * @param  array  $api     api config
     * @param  array  $payload request payload
     * @param  array  $cookies request cookies
     * @param  array  $headers headers
     *
     * @return array option
     */
    private function makeOptionFor(array $api, array $payload, array $cookies, array $headers)
    {
        $signed = $this->signerFor($api)->sign($payload);
        $option = ($api['method'] == 'GET') ? ['query' => $signed] : ['form_params' => $signed];

        $parsed = parse_url($api['url']);
        $option['cookies'] = CookieJar::fromArray($cookies, $parsed['host']);
        $option['connect_timeout'] = $api['timeout'];
        $option['headers'] = $headers;

        return $option;
    }

    /**
     * make signer for api configs
     *
     * @author FangHao <email:fanghao@xin.com, phone:15369997084>
     *
     * @param  array  $api api config
     *
     * @return Signer signer
     */
    private function signerFor(array $api)
    {
        if (empty($api['signer'])) {
            return new AsIsSigner();
        }
        if (! class_exists($api['signer'])) {
            throw new \Exception("can not find signer class: {$api['signer']}");
        }
        return new $api['signer'];
    }
}