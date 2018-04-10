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
    public $cardJson;
    public  static $refactor;


    /**
     * Templater constructor.
     * @param $temp_dir
     * @param $temp_file
     */
    public function __construct($temp_dir, $temp_file)
    {
        $root = $this->getRoot();

         //File with paths
        $fileDirs = $root. '/storage/dirs.yaml';
        $pars = Yaml::parseFile($fileDirs);

            //Construct
        if ($temp_file !== null){

                //User cache construct
            if (Config::$userCache) {
                $this->bg = new Background();
                $configCache = Config::$cache;
                $usersDir = Config::$usersDir;
                $this->cardJson = Config::$cardJson;
                if (!$this->cardJson) {
                    $this->cardJson = Config::setCardJson();
                }

                //Writing in yaml  user card.json file name
                $fieldCardJson = 'cardJson';
                if ($this->cardJson && !$pars[$fieldCardJson]) {
                    $this->writeInYamlDirs($fileDirs, "\n$fieldCardJson : $this->cardJson");
                } //Custom change user card.json file name
                else if ($pars[$fieldCardJson] !== $this->cardJson) {
                    $this->changeYamlData($pars, $this->cardJson, $fileDirs, $fieldCardJson);
                }

                //$root/$configUserCache/$prefixUsersDir
                $this->usersDir = $this->bg->setUserCacheCatalog($root) . '/' . $usersDir;

                //Typing card.json file
                $this->jsonPath = "$root/storage/$this->cardJson";

                //Users cache catalog set in Config::setConfig
                $configUserCache = Config::$userCache;

                //Writing typing card.json file in dirs.yaml
                if (!$pars['jsonDef']) {
                    $this->writeInYamlDirs($fileDirs, "\njsonDef : $this->jsonPath");
                }

                //Writing cache dir in yaml
                if ($configUserCache && !$pars['userCache']) {
                    $this->writeInYamlDirs($fileDirs, "\nuserCache : $configUserCache");
                }

                //Writing in yaml custom cache catalog
                if ($configCache && !$pars['cache']) {
                    $this->writeInYamlDirs($fileDirs, "\ncache : $configCache");
                } //Custom change user cache catalog
                else if ($pars['cache'] !== $configCache) {
                    $this->changeYamlData($pars, $configCache, $fileDirs, 'cache');
                }

                //Writing cache usersDir in yaml
                if (!$pars['userDir']) {
                    $this->writeInYamlDirs($fileDirs, "\nuserDir : $this->usersDir");
                } //Custom change users cache catalog
                else if ($pars['userCache'] !== $configUserCache) {
                    $this->changeYamlData($pars, $configUserCache, $fileDirs, 'userCache');
                }

                //Create users dir
                $this->checkAndCreateUsersDir($this->usersDir);
            }
            if ($temp_dir) {

                    //Fill paths parameters
                $this->serverName = $_SERVER['SERVER_NAME'];
                $this->protocol = $this->getProtocol();
                $this->root = $root;
                $this->tempDir = "$this->root/$temp_dir";
                $this->tempFile = "$this->tempDir/$temp_file";
                $this->viewDir = "$this->tempDir/views/";
            }
        }

            //Constructor for ajax request
        else {
            $data = Yaml::parseFile($fileDirs);
            $this->ajaxData = $temp_dir;
            $c = new Config();
            $c->setConfig([
                'fileDirs' => $fileDirs,
                'cardJson' => $data['cardJson']
            ]);
        }
    }
    public function changeYamlData($data, $dir, $dirsFile, $key, $i = 0)
    {
        if ($i < count($data)) {
            $keys = array_keys($data);
            if ($data[$key] !== $dir) {
                if ($i === 0) {
                    $this->writeInYamlDirs($dirsFile, '', 'w');
                }
                $data[$key] = $dir;
                static::$refactor = true;
            }
            if (self::$refactor) {
                $activeKey = $keys[$i];
                $this->writeInYamlDirs($dirsFile, "\n$activeKey : $data[$activeKey]", 'a');
                return $this->changeYamlData($data, $dir, $dirsFile, $key, $i + 1);
            }
            return false;
        }
        else {
            return true;
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
    public function checkAndCreateUsersDir($userCacheDir)
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