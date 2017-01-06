<?php

namespace GDCE\LaravelDreamfactory\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Active facade class
 *
 * @author HENG Sotharith
 */
class GDCE extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'gdce';
    }

}
