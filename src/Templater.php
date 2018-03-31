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

    /**
     * Templater constructor.
     * @param $temp_dir
     * @param $temp_file
     */
    public function __construct($temp_dir, $temp_file)
    {
        $this->root = $this->getRoot();
        $this->tempDir = "$this->root/$temp_dir";
        $this->tempFile = "$this->tempDir/$temp_file";
        $this->viewDir = "$this->tempDir/views/";

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