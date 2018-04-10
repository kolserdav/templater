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
    private $arrayManifest;
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

            $this->userCache($fileName, $dataTwo);
        }
        if ($cacheCatalog == '.') {
            unlink($fileName);
            return false;
        }
        return true;
    }
    public function userCache($fileName, $data)
    {


        $userDir = $this->getCookie(Config::$cookieName);

        $userCacheDir = $this->usersDir.'/'.$userDir;

            //Create user dir and card.json file
        $this->checkAndCreateUsersDir($userCacheDir);
        $jsonName = $userCacheDir.'/'.$this->cardJson;
        $this->checkAndCreateFile($this->jsonPath, $jsonName);

            //Create user manifest file
        $fileManifest = $userCacheDir.'/'.'.manifest.appcache';
        $this->checkAndCreateFile($this->manifestPath, $fileManifest);
        $manifestData = explode("\n", file_get_contents($fileManifest));

            //Preprocessor the cache-file.html
        if (preg_match('%\<\?%', $data)) {
            $htmlData = shell_exec("php $fileName");
        }
        else {
            $htmlData = $data;
        }

            //Getting cache file name
        $title = Helper::searchTitle($htmlData);
        if (!$title) {
            $htmlFileName = $this->getHtmlFileName($this->userCacheCatalog, $htmlData);
        } else {
            $htmlFileName = $this->getHtmlTitleFile($this->userCacheCatalog, $title);
        }

            //Create user cache file
        $this->copyWriteFile($htmlFileName, $htmlData);

            //Writing current page to file manifest
        $this->readManifestFile($manifestData);
        $addrPage = $this->addressPage($title);
        if (!array_search($addrPage, $this->arrayManifest['CACHE:'])) {
            $this->arrayManifest['CACHE:'][] = $addrPage;
            $stringManifest = $this->manifestToString();
            $this->writeInFile($fileManifest, $stringManifest, 'w');
        }

       //var_dump($this->arrayManifest);



        require $htmlFileName;

    }


    public function manifestToString()
    {
        $stringManifest['CACHE:'][] = implode("\n", $this->arrayManifest['CACHE:']);
        $stringManifest['NETWORK:'][] = implode("\n",$this->arrayManifest['NETWORK:']);
        $stringManifest['FALLBACK:'][] = implode("\n",$this->arrayManifest['FALLBACK:']);
        $date = date('d.m.Y H:i:s');
        return "CACHE MANIFEST\n"."# $date\n"."CACHE:\n".$stringManifest['CACHE:'][0].
            "\nNETWORK:\n".$stringManifest['NETWORK:'][0]."\nFALLBACK:\n".$stringManifest['FALLBACK:'][0];
    }

    public function  addressPage($title)
    {
        preg_match('%\w*\/%', Config::$userCache, $m);
        $cacheDir = str_replace($m[0], '', Config::$userCache);
        return '/'.$cacheDir.'/pages/'.$title.".html";
    }

    public function readManifestFile($data, $array = array(), $i = 3, $y = 0)
    {
        $count = count($data);
        $data[$i] = trim($data[$i]);
        if ($data[$i] !== "NETWORK:" && $y === 0) {
            $array["CACHE:"][] = $data[$i];
            $this->readManifestFile($data, $array, $i + 1);
        }
        else if ($data[$i] === "NETWORK:" && $y === 0){
            $this->readManifestFile($data, $array, $i + 1, 1);
        }
        else if ($y === 1 && $data[$i] !== "FALLBACK:"){
            $array["NETWORK:"][] = $data[$i];
            $this->readManifestFile($data, $array, $i + 1, 1);
        }
        else if ($data[$i] === "FALLBACK:" && $y === 1){
            $this->readManifestFile($data, $array, $i + 1, 2);
        }
        else if ($y === 2 && $i < $count){
            $array["FALLBACK:"][] = $data[$i];
            $this->readManifestFile($data, $array,$i + 1, 2);
        }
        else if ($y === 2 && $i === $count){
            $this->arrayManifest = $array;
            return $array;
        }
    }



    /**
     * @param $jsonName
     * @param $jsonPath
     */
    public function checkAndCreateFile($jsonPath, $jsonName)
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