<?php
namespace Unifreak\Fetcher;

use Illuminate\Support\Facades\Facade;

class FetcherFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'fetcher';
    }
}
