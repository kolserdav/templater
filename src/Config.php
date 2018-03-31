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
    /**
     * Cache catalog path
     * @var string
     */
    public static $cache;

    /**
     * @param array $args
     */
    public function setConfig(array $args = array())
    {
        static::$cache = $args['cache'];
    }
}