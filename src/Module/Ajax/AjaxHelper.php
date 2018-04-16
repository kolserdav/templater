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
    /**
     * @return bool
     */
    public function jsonHandler(): bool
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


            //Form user card.json file
        $userFileCard = $userDir.'/'.Config::$cardJson;
        $this->checkAndCreateDir($userDir);
        $this->checkAndCreateFile($jsonPath, $userFileCard);
        $aliasesFile = (Yaml::parseFile($yaml))['dataUrls'];

            //Write in all users file
        $this->formJsonFile($data, $aliasesFile);

            //Write in the user file
        return $this->formJsonFile($data, $userFileCard, $nameDir);
    }

    public function formJsonFile($data, $userFileCard, $nameDir = 'All_Users')
    {

        $userFileData = json_decode(file_get_contents($userFileCard));
        if ($userFileData->info->name === 'Firstname_Lastname') {
            $userFileData->info->codename = $data->name->nameCookie->encode;
            $userFileData->info->name = $nameDir;
        }
        if ($nameDir === 'All_Users'){
            $userFileData->info->codename = base64_encode($nameDir);
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