<?php
/**
 * Created by kolserdav
 * User: Sergey Kol'miller
 * Date: 30.03.2018
 * Time: 22:25
 */

namespace Avir\Templater;


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

    /**
     * Templater constructor.
     * @param $temp_dir
     * @param $temp_file
     * @param $users_dir
     */
    public function __construct($temp_dir, $temp_file, $users_dir = null)
    {
        $this->bg = new Background();
        if ($users_dir !== null){
            $this->usersDir = $this->bg->setUserCacheCatalog($this->getRoot()).'/'.$users_dir;
            if (!is_dir($this->bg->setUserCacheCatalog($this->getRoot()))){
                @mkdir($this->bg->setUserCacheCatalog($this->getRoot()));
            }
            if (!is_dir($this->usersDir)){
                @mkdir($this->usersDir);
            }
        }
        else {
            $this->usersDir = $this->bg->setUserCacheCatalog($this->getRoot());
            if (!is_dir($this->usersDir)){
                @mkdir($this->usersDir);
            }
        }
        if ($temp_file !== null) {
            $this->root = $this->getRoot();
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
        preg_match("%.*templater%",dirname(__DIR__),$m);
        return preg_filter('%.{1}templater%','',$m[0]);
    }


}