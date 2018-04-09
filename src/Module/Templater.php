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
    public $ajaxData;
    public $usersDir;
    public $bg;
    public $protocol;
    public $serverName;
    public $userCacheCatalog;
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
        $this->jsonPath = "$root/storage/card.json";
        if ($users_dir !== null){

            $this->usersDir = $this->bg->setUserCacheCatalog($root).'/'.$users_dir;
            $fileDirs = $root. '/storage/dirs.yaml';
            $pars = Yaml::parseFile($fileDirs);
            if(!$pars['userDir']) {
                $res = fopen($fileDirs, 'a');
                fwrite($res, "userDir : $this->usersDir");
                fclose($res);
            }
            if(!$pars['jsonDef']) {
                $res = fopen($fileDirs, 'a');
                fwrite($res, "\njsonDef : $this->jsonPath");
                fclose($res);
            }
            if (!is_dir($this->bg->setUserCacheCatalog($root))){
                @mkdir($this->bg->setUserCacheCatalog($root));
            }
            if (!is_dir($this->usersDir)){
                @mkdir($this->usersDir);
            }

        }
        else {
            $this->usersDir = $this->bg->setUserCacheCatalog($root);
            if (!is_dir($this->usersDir)){
                @mkdir($this->usersDir);
            }
        }
        if ($temp_file !== null) {
            $this->serverName = $_SERVER['SERVER_NAME'];
            $this->protocol = preg_replace('%\/\d*\.\d*%','', $_SERVER['SERVER_PROTOCOL']);
            $this->root = $root;
            $this->tempDir = "$this->root/$temp_dir";
            $this->tempFile = "$this->tempDir/$temp_file";
            $this->viewDir = "$this->tempDir/views/";
        }
        else {
            $this->ajaxData = $temp_dir;
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