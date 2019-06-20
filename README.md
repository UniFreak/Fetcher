`Fetcher` wraps around `GuzzleHttp` package, it can be used to send http requests according to configured api entries

You can configure an api's url, request method, response structure, api signers etc. `Fetcher` then will send corresponding http request according to the configuration and decide whether received response is okay

# Installation

Simply run `composer require unifreak/fetcher`

## For Laravel/Lumen: Registering Service Provider and Facade

If you are not using `laravel/lumen`, skip this step

If you are using `laravel` greater than version 5.5, then service provider and facade will be auto registerd (see <https://laravel-news.com/package-auto-discovery>), so you can skip this step, too

If you are using `lumen`, simply add the following lines into your project's `/bootstrap/app.php` file:

```php
<?php
// register facade
if (!class_exists('Fetcher')) {
    class_alias(Unifreak\Fetcher\FetcherFacade::class, 'Fetcher');
}

// register service provider
$app->register(Unifreak\Fetcher\FetcherServiceProvider::class);
```

If you are using `laravel`, the file to be modified maybe different, see laravel [official doc](https://laravel.com/docs/5.7)

# Usage

```php
<?php
/**
 * Api configuration
 * Better keep those configurations in a seperated config files
 */
$api = [
    'url' => 'http://example.com/demo/api', // Api url, **NOTE without any query parameter**
    'method' => 'POST', // Api request method, default to 'GET'
    'timeout' => 0.01, // Api request timeout seconds
    'codeField' => 'payload.code', // Response code field, can nest with `.`, default to 'code'
    'dataField' => 'payload.data', // Response data field, can nest with `.`, default to 'data'
    'messageField' => 'payload.msg', // Response message field, can nest with `.`, default to 'message'
    'successCode' => 2, // When response code equal to this configured value, Fetcher considers api call success. default to 1
    'signer' => DemoSigner::class, // Signer class, see below
];

/**
 * Assume you have registered facade, you can use `Fetcher::fetch()` to do api calls
 * Otherwise, you need to create Fetcher instance manually like: `new Fetcher(new GuzzleHttp\Client());`
 *
 * fetch() accepts the following parameters:
 * 1. required, array, a api configuration
 * 2. optional, array, request payload
 * 3. optional, array, request cookies
 * 4. optional, array, request headers
 */
$fetched = Fetcher::fetch(
    $api,
    ['parameter' => 'value'],
    ['debug_token' => 'some debug token cookie'],
    ['Header1' => ['Value1', 'Value2'], 'Header2' => 'Value3']
);

/**
 * `fetch()` will always return a `Fetched` Object (here we store it in `$fetched` variable),
 * We can use this object to see whether the response is ok
 */
if ($fetched->ok()) { // whether response ok
    var_dump([
        $fetched->code(), // response code
        $fetched->message(), // response message
        $fetched->data() // response data
    ]);
}
```

```php
<?php
namespace App\Signers;

use Unifreak\Fetcher\Signer\Signer;

class DemoSigner implements Signer
{
    /**
     * `Signer` must implement a `sign()` method. `sign()` method do the real signing logic
     */
    public function sign(array $payload) {
        return $payload + ['_sn' => 'abcdefg'];
    }
}
```
