<?php
/**
 *@author longbo
 */
namespace Tesa\Libs;

use Closure;

class Container
{
    private static $service;

    public static function register($name, Closure $callback)
    {
        self::$service[$name] = $callback;
    }

    public static function book($name)
    {
        if (self::$service[$name] instanceof Closure) {
            $callback = self::$service[$name];
            return $callback();
        }
    }

}

