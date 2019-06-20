<?php
namespace Unifreak\Fetcher;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class FetcherServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('fetcher', function ($app) {
            return new Fetcher(app(Client::class));
        });
    }
}