<?php
/**
 * Created by kolserdav
 * User: Sergey Kol'miller
 * Date: 30.03.2018
 * Time: 20:48
 */

namespace Avir\Templater\Module;

use Avir\Templater\Module\Ajax\AjaxHelper;
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
        $this->userCacheCatalog = $bg->setUserCacheCatalog($this->root, true);

            //Get name file of cache and operation with him
        $fileName = $this->getFileName($cacheCatalog, $dataTwo);

            //Checking the cache file
        if (!file_exists($fileName)) {

                //Creating a cache file
            $this->copyWriteFile($fileName, $dataTwo);
        }
        $cookieName = Config::$cookieName;
        if (!$cookieName){
            $cookieName = Config::setCookie();
        }
        $cookie = $this->getCookie($cookieName);
        if (!$this->userCacheCatalog || empty($cookie)){
                //Require ready content file
            require $fileName;
        }
        else {

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


        $userDir = $this->getCookie(Config::$cookieName);

        $userCacheDir = $this->usersDir.'/'.$userDir;

            //Create user dir and card.json file
        $this->checkAndCreateUsersDir($userCacheDir);
        $jsonName = $userCacheDir.'/'.$this->cardJson;
        $this->checkAndCreateJsonFile($this->jsonPath, $jsonName);

        $dataJson = json_decode(file_get_contents($jsonName));


        var_dump($dataJson);




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



    /**
     * @param $jsonName
     * @param $jsonPath
     */
    public function checkAndCreateJsonFile($jsonPath, $jsonName)
    {
        if (!file_exists($jsonName)) {
            copy($jsonPath, $jsonName);
        }
    }

    /**
     * @param $cookie_name
     * @return bool|string
     */
    public function getCookie($cookie_name)
    {
        return base64_decode($_COOKIE[$cookie_name]);
    }

    public function ajax()
    {
        $ajax = new AjaxHelper($this->ajaxData, null);
        return $ajax->jsonHandler();
    }

    public function writeInJson($userFileCard, $userFileData)
    {
        $res = fopen($userFileCard, 'w');
        fwrite($res, json_encode($userFileData));
        fclose($res);
    }

    public function formDate($data, $userFileData)
    {
        $firstVisit = $userFileData->info->firstVisit;
        if ($firstVisit == 'Date') {
            $userFileData->info->firstVisit = $data->name->time;
            $userFileData->info->lastVisit = $data->name->time;
        } else {
            $userFileData->info->lastVisit = $data->name->time;
        }
        return $userFileData;
    }

    public function getPageN($data)
    {
        return 'page-'.$data->pages->count;
    }

    public function searchHost($data, $host, $i = 0, $y = 0)
    {
        $page = 'page-'.$i;
        if ($i < $data->pages->count){
            $data->pages->$page;
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