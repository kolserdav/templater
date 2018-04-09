<?php
/**
 * Created by kolserdav
 * User: Sergey Kol'miller
 * Date: 30.03.2018
 * Time: 20:48
 */

namespace Avir\Templater\Module;

use Avir\Templater\Module\Helper;
use Symfony\Component\Yaml\Yaml;

class Render extends Templater
{
    private static $y;
    /**
     * This is the module collector
     * @param array $files
     * @param array $args
     * @return bool
     */
    public function render(array $args = [], array $files = []): bool
    {
        $bg = new Background();

        $argv = [
            'tempFile' => $this->tempFile,
            'viewDir' => $this->viewDir,
            'files' => $files
        ];

        $args = array_merge($argv, $args);

            //Get data from template file
        $data = $bg->getData($args);

            //Replace 'fields' with '@'-selector
        $dataTwo = $bg->prepareEt($args, $data);


            //Replace {% for in %}construction on 'foreach(){}'
        $dataTwo = $bg->prepareFor($args, $dataTwo);

        $dataTwo = $bg->clearForDirt($dataTwo);

        //Replace {{ value }} variables on 'echo $value;'
        $dataTwo = $bg->prepareCurly($args, $dataTwo);

            //Get custom cache catalog
        $cacheCatalog = $bg->setCacheCatalog($this->root);
        $this->userCacheCatalog = $bg->setUserCacheCatalog($this->root);

            //Get name file of cache and operation with him
        $fileName = $this->getFileName($cacheCatalog, $dataTwo);

            //Checking the cache file
        if (!file_exists($fileName)) {

                //Creating a cache file
            $this->copyWriteFile($fileName, $dataTwo);
        }
        $cookie = $this->getCookie('test');
        if (!$this->userCacheCatalog || empty($cookie)){
                //Require ready content file
            require $fileName;
        }
        else {
           //    echo file_get_contents(__DIR__.'./storage/cookie.html');
            $this->userCache($fileName);
        }
        if ($cacheCatalog == '.') {
            unlink($fileName);
            return false;
        }
        return true;
    }
    public function userCache($file_name)
    {


        $userDir = $this->getCookie('test');

        $userCacheDir = $this->usersDir.'/'.$userDir;

        if (!is_dir($userCacheDir)) {
            @mkdir($userCacheDir);
        }
        $jsonPath = __DIR__.'/../storage/card.json';
        $jsonName = $userCacheDir.'/'.'card.json';

        if (!file_exists($jsonName)) {
            copy($jsonPath, $jsonName);
        }

        $dataJson = json_decode(file_get_contents($jsonName));

        $dataJson = json_encode($dataJson);
        //$this->copyWriteFile($jsonName, $dataJson);
        //var_dump(json_decode($dataJson));
        //var_dump($jsonName);


        $htmlData = shell_exec("php $file_name");
        $title = Helper::searchTitle($htmlData);

        if (!$title) {
            $htmlFileName = $this->getHtmlFileName($this->userCacheCatalog, $htmlData);
        } else {
            $htmlFileName = $this->getHtmlTitleFile($this->userCacheCatalog, $title);
        }
        $this->copyWriteFile($htmlFileName, $htmlData);
        require $htmlFileName;

    }
    public function getCookie($cookie_name)
    {
        return base64_decode($_COOKIE[$cookie_name]);
    }

    public function ajax()
    {
        $data = json_decode($this->ajaxData['cookie']);
        $nameDir = $data->name->nameCookie->clear;
        $usersDir = (Yaml::parseFile(__DIR__ . '/../../storage/dirs.yaml'))['userDir'];
        $userFileCard = $usersDir.'/'.$nameDir.'/'.'card.json';
        $userFileData = json_decode(file_get_contents($userFileCard));
        if ($userFileData->info->name === 'Firstname_Lastname') {
            $userFileData->info->codename = $data->name->nameCookie->encode;
            $userFileData->info->name = $nameDir;
        }
        $firstVisit = $userFileData->info->firstVisit;
        if ($firstVisit == 'Date') {
            $userFileData->info->firstVisit = $data->name->time;
        } else {
            $userFileData->info->lastVisit = $data->name->time;
        }
        $currentPage = $this->getPageN($userFileData);
        $pag = new \stdClass();
        $host = $this->ajaxData['host'];
        if(!$this->searchHost($userFileData, $host)) {
            $pag->$host = $this->ajaxData['title'];
            $userFileData->pages->$currentPage = $pag;
            $userFileData->pages->count++;
            $res = fopen($userFileCard, 'w');
            fwrite($res, json_encode($userFileData));
            fclose($res);
            return false;
        }
        return true;
    }

    public function getPageN($data)
    {
        return 'page-'.$data->pages->count;
    }

    public function searchHost($data, $host, $i = 0, $y = 0)
    {
        $page = 'page-'.$i;
        if ($i < $data->pages->count){
            if (property_exists($data->pages->$page, $host)){
                $y = 1;
                return self::searchHost($data, $host, $i + 1, $y);
            }
            else{
                return self::searchHost($data, $host, $i + 1, $y);
            }
        }
        else {
            if ($y === 1){
                var_dump($y);
                return true;
            }
            else {
                return false;
            }
        }
    }


    /**
     * @param $cache_catalog string
     * @param $file_cache string
     * @return string
     */
    public function getFileName(string $cache_catalog, string $file_cache): string
    {
        return $cache_catalog.'/'.md5($file_cache).'.php';
    }
    public function getHtmlFileName(string $user_cache_catalog, string $file_cache): string
    {
        return $user_cache_catalog.'/'.md5($file_cache).'.html';
    }
    public function copyWriteFile($file_name, $data)
    {
        try{
            if (!@copy($this->tempFile, $file_name)){
                throw new \Exception("Please check on the path correctness in 'setConfig' function attributes.");
            }
        }
        catch (\Exception $e){
            echo $e->getMessage();
            exit();
        }
        $res = fopen($file_name, 'w');
        fwrite($res, $data);
        fclose($res);
    }

    public function getHtmlTitleFile($user_cache_catalog, $title)
    {
        return $user_cache_catalog.'/'.$title.'.html';
    }
}