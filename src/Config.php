<?php
/**
 * Created by kolserdav
 * User: Sergey Kol'miller
 * Date: 31.03.2018
 * Time: 2:09
 */

namespace Avir\Templater;


class Config
{
    public static $cache;

    public function setConfig(array $args = array())
    {
        static::$cache = $args['cache'];
    }
}