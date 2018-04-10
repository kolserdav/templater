<?php
/**
 * Created by kolserdav
 * User: Sergey Kol'miller
 * Date: 10.04.2018
 * Time: 23:43
 */

namespace Avir\Templater\Module\Ajax;

use Avir\Templater\Module\Render;
use Symfony\Component\Yaml\Yaml;
use Avir\Templater\Module\Config;

class AjaxHelper extends Render
{
    public function jsonHandler()
    {
            //Get data from ajax request
        $data = json_decode($this->ajaxData['cookie']);

            //User cookie name
        $nameDir = $data->name->nameCookie->clear;

            //Path for paths file
        $yaml = Config::$fileDirs;

            //Read paths file
        $usersDir = (Yaml::parseFile($yaml))['userDir'];
        $jsonPath = (Yaml::parseFile($yaml))['jsonDef'];

            //User catalog name
        $userDir = $usersDir.'/'.$nameDir;

             //User card.json catalog
        $this->checkAndCreateUsersDir($userDir);

            //Form user card.json file
        $userFileCard = $userDir.'/'.Config::$cardJson;
        $this->checkAndCreateJsonFile($jsonPath, $userFileCard);
        $userFileData = json_decode(file_get_contents($userFileCard));
        if ($userFileData->info->name === 'Firstname_Lastname') {
            $userFileData->info->codename = $data->name->nameCookie->encode;
            $userFileData->info->name = $nameDir;
        }
        $userFileData = $this->formDate($data, $userFileData);
        $currentPage = $this->getPageN($userFileData);
        $pag = new \stdClass();
        $host = $this->ajaxData['host'];
        if(!$this->searchHost($userFileData, $host)) {
            $pag->$host = $this->ajaxData['title'];
            $userFileData->pages->$currentPage = $pag;
            $userFileData->pages->count++;
            $this->writeInJson($userFileCard, $userFileData);

            return false;
        }

        $this->writeInJson($userFileCard, $userFileData);

        return true;
    }
}