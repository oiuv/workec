<?php

namespace Oiuv\WorkEc;

use Illuminate\Support\Facades\Facade as LaravelFacade;

class Facade extends LaravelFacade
{
    public static function getFacadeAccessor()
    {
        return EC::class;
    }
}
