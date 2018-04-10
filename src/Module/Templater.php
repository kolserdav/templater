<?php
/**
 * Created by kolserdav
 * User: Sergey Kol'miller
 * Date: 30.03.2018
 * Time: 22:25
 */

namespace Avir\Templater\Module;

use Symfony\Component\Yaml\Yaml;

abstract class Templater
{
    /**
     * Template catalog
     * @var string
     */
    public $tempDir;

    /**
     * Views catalog %tempDir%/views
     * @var string
     */
    public $viewDir;

    /**
     * Template file
     * @var string
     */
    public $tempFile;

    /**
     * Root the project
     * @var string
     */
    public $root;

    /**
     * @var
     */
    public $ajaxData;

    /**
     * @var string
     */
    public $usersDir;

    /**
     * @var Background
     */
    public $bg;

    /**
     * @var mixed
     */
    public $protocol;

    /**
     * @var string
     */
    public $serverName;

    /**
     * @var string
     */
    public $userCacheCatalog;

    /**
     * @var string
     */
    public $jsonPath;


    /**
     * Templater constructor.
     * @param $temp_dir
     * @param $temp_file
     * @param $users_dir
     */
    public function __construct($temp_dir, $temp_file, $users_dir = null)
    {
        $this->bg = new Background();
        $root = $this->getRoot();
        $fileDirs = $root. '/storage/dirs.yaml';

        $pars = Yaml::parseFile($fileDirs);

        $configUserCache = Config::$userCache;
            //Writing cache dirs in yaml
        if ($configUserCache && !$pars['userCache']){
            $this->writeInYamlDirs($fileDirs, "\nuserCache : $configUserCache");
        }
        $configCache = Config::$cache;
        if ($configCache && !$pars['cache']){
            $this->writeInYamlDirs($fileDirs, "\ncache : $configCache");
        }
        if ($pars['userCache'] !== $configUserCache){
            $pars['userCache'] = $configUserCache;

           // $this->writeInYamlDirs($fileDirs, implode($pars), 'w');
        }
        if ($pars['cache'] !== Config::$cache){
            $pars['cache'] = Config::$cache;
           // $this->writeInYamlDirs($fileDirs, implode($pars), 'w');
        }

        $this->jsonPath = "$root/storage/card.json";

            //Constructor for ajax request
        if ($users_dir !== null){
            $c = new Config();
            $c->setConfig([
                'cache' => $pars['cache'],
                'userCache' => $pars['userCache']
            ]);
            $this->usersDir = $this->bg->setUserCacheCatalog($root).'/'.$users_dir;

            if(!$pars['userDir']) {
                $this->writeInYamlDirs($fileDirs, "\nuserDir : $this->usersDir");
            }
            if(!$pars['jsonDef']) {
                $this->writeInYamlDirs($fileDirs, "\njsonDef : $this->jsonPath");
            }
            $this->checkAndCreateJsonDir($this->bg->setUserCacheCatalog($root));
            $this->checkAndCreateJsonDir($this->usersDir);

        }

            //TODO what is this
        else {
            $this->usersDir = $this->bg->setUserCacheCatalog($root);
            $this->checkAndCreateJsonDir($this->usersDir);
        }
        if ($temp_file !== null) {
            $this->serverName = $_SERVER['SERVER_NAME'];
            $this->protocol = $this->getProtocol();
            $this->root = $root;
            $this->tempDir = "$this->root/$temp_dir";
            $this->tempFile = "$this->tempDir/$temp_file";
            $this->viewDir = "$this->tempDir/views/";
        }
            //TODO what is this
        else {
            $this->ajaxData = $temp_dir;
        }

    }

    /**
     * @param $fileDirs
     * @param $mode = 'a'
     * @param $string
     */
    public function writeInYamlDirs($fileDirs, $string, $mode = 'a')
    {
        $res = fopen($fileDirs, $mode);
        fwrite($res, $string);
        fclose($res);
    }

    /**
     * @return mixed
     */
    public function getProtocol()
    {
        return preg_replace('%\/\d*\.\d*%','', $_SERVER['SERVER_PROTOCOL']);
    }

    /**
     * @param $userCacheDir
     */
    public function checkAndCreateJsonDir($userCacheDir)
    {
        if (!is_dir($userCacheDir)) {
            @mkdir($userCacheDir);
        }
    }

    /**
     * Get root the project
     * @return string
     */
    public function getRoot(): string
    {
        preg_match("%.*src%",dirname(__DIR__),$m);
        return preg_filter('%.{1}src%','',$m[0]);
    }


}