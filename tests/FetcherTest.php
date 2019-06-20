<?php
namespace Unifreak\Fetcher\Tests;

use GuzzleHttp\Client;
use Unifreak\Fetcher\Fetcher;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;

class FetcherTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->mock = new MockHandler();
        $stack = HandlerStack::create($this->mock);

        $client = new Client(['handler' => $stack]);
        $this->fetcher = new Fetcher($client);
    }

    public function testSendPostRequest()
    {
        $this->mock->append(new Response());
        $api = [
            'url' => 'https://dummy.com/dummy/path',
            'method' => 'POST',
        ];
        $payload = ['foo' => 'bar'];
        $this->fetcher->fetch($api, $payload);

        $last = $this->mock->getLastRequest();
        $this->assertEquals('POST', $last->getMethod());
        $this->assertEquals('https://dummy.com/dummy/path', $last->getUri());
        $this->assertEquals('foo=bar', (string) $last->getBody());
    }

    public function testSendDefaultGetRequest()
    {
        $this->mock->append(new Response());
        $api = ['url' => 'https://dummy.com/dummy/path'];
        $payload = ['foo' => 'bar'];
        $this->fetcher->fetch($api, $payload);

        $last = $this->mock->getLastRequest();
        $this->assertEquals('GET', $last->getMethod());
        $this->assertEquals('https://dummy.com/dummy/path?foo=bar', (string) $last->getUri());
        $this->assertEquals('foo=bar', (string) $last->getUri()->getQuery());
    }

    public function testSendCookies()
    {
        $this->mock->append(new Response());
        $api = ['url' => 'dummy.com/dummy/path'];
        $cookies = ['bar' => 'foo', 'hi' => 'ya'];
        $payload = [];
        $this->fetcher->fetch($api, [], $cookies);

        $last = $this->mock->getLastRequest();
        $this->assertEquals('http://dummy.com/dummy/path', (string) $last->getUri());
        $this->assertEquals('bar=foo; hi=ya', $last->getHeaderLine('Cookie'));
    }

    public function testSendHeaders()
    {
        $this->mock->append(new Response());
        $api = ['url' => 'dummy.com'];

        $headers = [
            'Test-Header' => 'Test Value',
            'Test-Header-Array' => ['Value 1', 'Value 2'],
        ];
        $this->fetcher->fetch($api, [], [], $headers);

        $last = $this->mock->getLastRequest();
        $this->assertTrue($last->hasHeader('Test-Header'));
        $this->assertEquals(['Test Value'], $last->getHeader('Test-Header'));
        $this->assertTrue($last->hasHeader('Test-Header-Array'));
        $this->assertEquals(['Value 1', 'Value 2'], $last->getHeader('Test-Header-Array'));
    }

    public function testSigner()
    {
        $this->mock->append(new Response());
        $api = ['url' => 'dummy.com/dummy/path', 'signer' => TestSigner::class];
        $payload = ['foo' => 'bar', 'hi' => 'ya'];
        $this->fetcher->fetch($api, $payload);

        $signer = new TestSigner();
        $last = $this->mock->getLastRequest();
        $this->assertEquals(
            (string) $last->getUri()->getQuery(),
            http_build_query($signer->sign($payload))
        );
    }

    public function responseProvider()
    {
        $config = ['url' => 'dummy.com'];
        return [
            'default' => [
                $config,
                200,
                Psr7\stream_for('{"code":1,"message":"success","data":{"default":"success"}}'),
                1,
                'success',
                ['default' => 'success'],
            ],
            'flat' => [
                $config + ['codeField' => 'coder', 'messageField' => 'msg', 'dataField' => 'dater'],
                200,
                Psr7\stream_for('{"coder":1,"msg":"suc","dater":{"flat":"success"}}'),
                1,
                'suc',
                ['flat' => 'success'],
            ],
            'nested' => [
                $config + ['codeField' => 'a.code', 'messageField' => 'a.msg', 'dataField' => 'a.b.data'],
                200,
                Psr7\stream_for('{"a":{"code":1,"msg":"hiya","b":{"data":{"nested":"success"}}}}'),
                1,
                'hiya',
                ['nested' => 'success'],
            ],
            'error' => [
                $config + ['successCode' => 1],
                200,
                Psr7\stream_for('{"code":2,"message":"some thing went wrong","data":{"error":"passed"}}'),
                2,
                'some thing went wrong',
                ['error' => 'passed'],
                false
            ],
            'exception' => [
                $config + ['successCode' => 1],
                500,
                Psr7\stream_for('{"code":2,"message":"some thing went wrong","data":{"error":"out"}}'),
                500,
                '[server error]',
                [],
                false
            ]
        ];
    }

    /**
     * @dataProvider responseProvider
     */
    public function testFetch(
        $api,
        $statusCode,
        $body,
        $expectedCode,
        $expectedMsg,
        $expectedData,
        $ok = true
    ) {
        $this->mock->append(new Response($statusCode, [], $body));

        $fetched = $this->fetcher->fetch($api);
        $this->assertEquals($ok, $fetched->ok());
        $this->assertEquals($expectedCode, $fetched->code());
        $this->assertContains($expectedMsg, $fetched->message());
        $this->assertEquals($expectedData, $fetched->data());
    }

    public function testTimeout()
    {
        $this->mock->append(
            new ConnectException('Time Out', new Request('GET', 'dummy.com'))
        );
        $fetched = $this->fetcher->fetch(['url' => 'dummy.com']);
        $this->assertEquals('[connect error] Time Out', $fetched->message());
        $this->assertEquals(500, $fetched->code());
    }
}
