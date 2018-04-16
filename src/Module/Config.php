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
    public static $usersDir;
    public static $cookieName;
    public static $fileDirs;
    public static $cardJson;


    /**
     * @param array $args
     */
    public function setConfig(array $args = array())
    {

        static::$cache = $args['cache'];
        static::$userCache = $args['userCache'];
        static::$usersDir = self::setUsersDir($args);
        static::$fileDirs = $args['fileDirs'];
        static::$cardJson = $args['cardJson'];
        static::$cookieName = self::setCookie($args['cookieName']);

    }
    public static function setCookie($name = 'name')
    {
        if (empty($name)){
            $name = 'name';
        }
        static::$cookieName = $name;
        return $name;

    }
    public static function setCardJson($jsonFileName = 'card.json')
    {
        static::$cardJson = $jsonFileName;
        return $jsonFileName;
    }
    public static function setUsersDir($args)
    {
        if ($args['usersDir']){
            return $args['usersDir'];
        }
        else {
            return 'users';
        }
    }
}