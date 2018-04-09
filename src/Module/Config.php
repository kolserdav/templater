<?php
/**
 * Created by kolserdav
 * User: Sergey Kol'miller
 * Date: 31.03.2018
 * Time: 2:09
 */

namespace Avir\Templater\Module;


class Config
{
    /**
     * Cache catalog path
     * @var string
     */
    public static $cache;

    /**
     * Caching for user side
     * @var string
     */
    public static $userCache;
    public static $cookieName;


    /**
     * @param array $args
     */
    public function setConfig(array $args = array())
    {
        static::$cache = $args['cache'];
        static::$userCache = $args['userCache'];

    }
    public function setCookie(string $name = 'name')
    {
        static::$cookieName = $name;

    }
}