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
    public $manifestPath;
    public $offlinePage;
    public $fileDirs;
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
        $this->fileDirs = $root. '/storage/dirs.yaml';
        $this->manifestPath = $root. '/storage/.manifest.appcache';
        $pars = Yaml::parseFile($this->fileDirs);

            //Construct
        if ($temp_file !== null){

            $configCache = Config::$cache;
            if (Config::$cache) {
                //Create the cache catalog
                $this->checkAndCreateDir($root . '/' . $configCache);
            }

                //User cache construct
            if (Config::$userCache) {
                $this->bg = new Background();
                $usersDir = Config::$usersDir;
                $this->cardJson = Config::$cardJson;
                if (!$this->cardJson) {
                    $this->cardJson = Config::setCardJson();
                }

                //Writing in yaml  user card.json file name
                $fieldCardJson = 'cardJson';
                if ($this->cardJson && !$pars[$fieldCardJson]) {
                    $this->writeInFile($this->fileDirs, "\n$fieldCardJson : $this->cardJson");
                } //Custom change user card.json file name
                else if ($pars[$fieldCardJson] !== $this->cardJson) {
                    $this->changeYamlData($pars, $this->cardJson, $this->fileDirs, $fieldCardJson);
                }

                    //User cache catalog
                $userCache = $this->bg->setUserCacheCatalog($root);

                     //$root/$configUserCache/$prefixUsersDir
                $this->usersDir =  $userCache. '/' . $usersDir;

                //Typing card.json file
                $this->jsonPath = "$root/storage/$this->cardJson";

                //Users cache catalog set in Config::setConfig
                $configUserCache = Config::$userCache;

                //Writing typing card.json file in dirs.yaml
                if (!$pars['jsonDef']) {
                    $this->writeInFile($this->fileDirs, "\njsonDef : $this->jsonPath");
                }



                //Writing the aliases file data-urls.json in dirs.yaml
                if (!$pars['dataUrls']) {
                    $aliasesFile = $root.'/'.Config::$userCache.'/pages/aliases/data-urls.json';
                    $this->writeInFile($this->fileDirs, "\ndataUrls : $aliasesFile");
                }

                //Writing cache dir in yaml
                if ($configUserCache && !$pars['userCache']) {
                    $this->writeInFile($this->fileDirs, "\nuserCache : $configUserCache");
                }

                //Writing in yaml custom cache catalog
                if ($configCache && !$pars['cache']) {
                    $this->writeInFile($this->fileDirs, "\ncache : $configCache");
                }
                    //Custom change user cache catalog
                else if ($pars['cache'] !== $configCache) {
                    $this->changeYamlData($pars, $configCache, $this->fileDirs, 'cache');
                }

                //Writing cache usersDir in yaml
                if (!$pars['userDir']) {
                    $this->writeInFile($this->fileDirs, "\nuserDir : $this->usersDir");
                } //Custom change users cache catalog
                else if ($pars['userCache'] !== $configUserCache) {
                    $this->changeYamlData($pars, $configUserCache, $this->fileDirs, 'userCache');
                }

                    //Create user cache catalog
                $this->checkAndCreateDir($userCache);

                    //Create pages dir
                $this->checkAndCreateDir($userCache.'/pages');

                    //Create users dir
                $this->checkAndCreateDir($this->usersDir);
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
            $data = Yaml::parseFile($this->fileDirs);
            $this->ajaxData = $temp_dir;
            $c = new Config();
            $c->setConfig([
                'fileDirs' => $this->fileDirs,
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
                    $this->writeInFile($dirsFile, '', 'w');
                }
                $data[$key] = $dir;
                static::$refactor = true;
            }
            if (self::$refactor) {
                $activeKey = $keys[$i];
                $this->writeInFile($dirsFile, "\n$activeKey : $data[$activeKey]", 'a');
                return $this->changeYamlData($data, $dir, $dirsFile, $key, $i + 1);
            }
            return false;
        }
        else {
            return true;
        }

    }

    /**
     * @param $filePath
     * @param $mode = 'a'
     * @param $string
     */
    public function writeInFile($filePath, $string, $mode = 'a')
    {
        $res = fopen($filePath, $mode);
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
     * @param $dir
     * @return bool
     */
    public function checkAndCreateDir($dir)
    {
        if (!is_dir($dir)) {
            @mkdir($dir);
            return true;
        }
        else {
            return false;
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