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
    public $arrayManifest;
    public $uri;
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

        if (!$this->userCacheCatalog){
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
        $cac = new CacheHandler();

        $nameCookie = Config::$cookieName;
        $userDir = $this->getCookie($nameCookie);

        $userCacheDir = $this->usersDir.'/'.$userDir;


            //Create user dir and card.json file
        $this->checkAndCreateDir($userCacheDir);
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
        $this->uri = $_SERVER['REQUEST_URI'];

        if (!$title) {

            $title = mb_convert_case(trim(str_replace('/', ' ', $this->uri)),MB_CASE_TITLE);
            $titleTag = "<title>$title</title>";
            $head = '</head>';
            $htmlData = str_replace($head, "$titleTag\n$head", $htmlData);

        }
        else {

            $aliasesFile = $cac->checkAndCreateAliasesFile($this);

            $dataUrls = json_decode(file_get_contents($aliasesFile));

            if($dataUrls->pages->count > 0){

                $host = strtolower($this->protocol).'://'.$this->serverName.$this->uri;

                $res = $this->searchHostInUrls($dataUrls, $host, $title);

                    //Adding a prefix for title and the cache file name.
                if ($res !== false) {
                    $titleNew = $cac->addPrefixForTitle($host, $title);
                    $htmlData = str_replace($title, $titleNew, $htmlData);
                    $title = $titleNew;
                }
            }
        }
        $htmlFileName = $this->getHtmlTitleFile($this->userCacheCatalog, $title);

            /**
             * Writing pages and files to file manifest
             */
        $this->readManifestFile($manifestData);
        $addrPage = $this->addressPage($title);

            //Getting the user card.json file url
        $userCardJson = (Yaml::parseFile($this->fileDirs))['cardJson'];
        $userJsonUrl = $this->getUserCatalogUrl($userDir)."/$userCardJson";

            //String html templater.js script
        $scriptTemplater = $this->getScriptUrlTemplaterJs();

        if (!array_search($addrPage, $this->arrayManifest['CACHE:'])) {

            $this->arrayManifest['CACHE:'][] = $addrPage;
            $jsonData = json_decode(file_get_contents("$userCacheDir/$this->cardJson"));

                //Writing first page in the manifest file
            $cac->writeFirstPageInToManifest($this, $jsonData);

                //Writing the user json file in the manifest file
            $cac->writeUserJsonFileToManifest($this, $userJsonUrl);

                //Writing the templater.js file in the manifest file
            $cac->writeTemplaterJsToManifest($this, $scriptTemplater);

                //Writing all changes
            $stringManifest = $this->manifestToString();
            $this->writeInFile($fileManifest, $stringManifest, 'w');
        }

            //Create templater.js
        $templaterJs = $cac->createTemplaterJs($this->userCacheCatalog, $this);

            //Setting cookie name in templater.js
        $cac->settingCookieName($templaterJs, $nameCookie, $this);

             //Add templater.js in the html
        $htmlData = $cac->addScriptBeforeBody($scriptTemplater, $htmlData);

            //Getting the html manifest string
        $userManifest = $this->getUserCatalogUrl($userDir).'/.manifest.appcache';


        if (!empty($this->getCookie($nameCookie))) {

                 //Add the user manifest tag
            $htmlDataU = str_replace('<html>', "<html manifest=\"$userManifest\">", $htmlData);

                //Add the file json tag
            $htmlDataUser = $cac->addScriptJsonFile($userJsonUrl, $htmlData, $htmlDataU);

        }
        else {
            $htmlDataUser = $htmlData;
        }


        echo $htmlDataUser;

            //Create the user cache file
        $this->copyWriteFile($htmlFileName, $htmlData);

    }

    public function getScriptUrlTemplaterJs()
    {
        return strtolower($this->protocol).'://'.$this->serverName.'/'.$this->cacheDir().'/pages/js/templater.js';
    }

    /**
     * @param $data
     * @param $host
     * @param $title
     * @param int $i
     * @return bool|int
     */
    public function searchHostInUrls(\stdClass $data, string $host, string $title, int $i = 0)
    {
        $page = 'page-'.$i;
        if ($i < $data->pages->count) {
            $prop = get_object_vars($data->pages->$page);
            if ($prop){
                $key = (array_keys($prop))[0];
                if ($prop[$key] == $title){
                    if (property_exists($data->pages->$page, $host)){
                        return $this->searchHostInUrls($data, $host, $title, $i + 1);
                    }
                    else {
                        return $i;
                    }
                }
                else {
                    return $this->searchHostInUrls($data, $host, $title, $i + 1);
                }
            }
            else {
                $this->searchHostInUrls($data, $host, $title, $i + 1);
            }

        }
        else {
            return false;
        }
    }

    /**
     * @param string $userDir
     * @return string
     */
    public function getUserCatalogUrl(string $userDir): string
    {
        $cacheDir = $this->cacheDir();
        return (strtolower($this->protocol)).'://'.$this->serverName.'/'
            .$cacheDir.'/'.Config::$usersDir.'/'.$userDir;
    }


    /**
     * @return string
     */
    public function manifestToString()
    {
        $stringManifest['CACHE:'][] = implode("\n", $this->arrayManifest['CACHE:']);
        $stringManifest['NETWORK:'][] = implode("\n",$this->arrayManifest['NETWORK:']);
        $stringManifest['FALLBACK:'][] = implode("\n",$this->arrayManifest['FALLBACK:']);
        $date = date('d.m.Y H:i:s');
        return "CACHE MANIFEST\n"."# $date\n"."CACHE:\n".$stringManifest['CACHE:'][0].
            "\nNETWORK:\n".$stringManifest['NETWORK:'][0]."\nFALLBACK:\n".$stringManifest['FALLBACK:'][0];
    }

    /**
     * @param string $title
     * @return string
     */
    public function  addressPage(string $title): string
    {
        $cacheDir = $this->cacheDir();
        return '/'.$cacheDir.'/pages/'.$title.".html";
    }

    /**
     * @return string
     */
    public function cacheDir(): string
    {
        $cache = Config::$userCache;
        preg_match('%\w*\/%', $cache, $m);
        return str_replace($m[0], '', $cache);
    }

    /**
     * @param array $data
     * @param array $array
     * @param int $i
     * @param int $y
     * @return array|bool
     */
    public function readManifestFile(array $data, array $array = array(), int $i = 3, int $y = 0)
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
        return false;
    }


    /**
     * @param $cookieName
     * @return bool|string
     */
    public function getCookie($cookieName)
    {
        return base64_decode($_COOKIE[$cookieName]);
    }

    /**
     * Ajax request method
     * @return bool
     */
    public function ajax()
    {
        $ajax = new AjaxHelper($this->ajaxData, null);
        return $ajax->jsonHandler();
    }

    /**
     * @param string $userFileCard
     * @param \stdClass $userFileData
     */
    public function writeInJson(string $userFileCard, \stdClass $userFileData)
    {
        $res = fopen($userFileCard, 'w');
        fwrite($res, json_encode($userFileData));
        fclose($res);
    }

    /**
     * @param mixed $data
     * @param mixed $userFileData
     * @return mixed
     */
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

    /**
     * @param mixed $data
     * @return string
     */
    public function getPageN($data):string
    {
        return 'page-'.$data->pages->count;
    }

    /**
     * @param mixed $data
     * @param string $host
     * @param int $i
     * @param int $y
     * @return bool
     */
    public function searchHost(\stdClass $data, string $host, int $i = 0, int $y = 0): bool
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
     * @param $cacheCatalog string
     * @param $fileCache string
     * @return string
     */
    public function getFileName(string $cacheCatalog, string $fileCache): string
    {
        return $cacheCatalog.'/'.md5($fileCache).'.php';
    }

    /**
     * @param string $userCacheCatalog
     * @param string $fileCache
     * @return string
     */
    public function getHtmlFileName(string $userCacheCatalog, string $fileCache): string
    {
        return $userCacheCatalog.'/'.md5($fileCache).'.html';
    }

    /**
     * @param $fileName
     * @param $data
     * @param string $mode
     */
    public function copyWriteFile($fileName, $data, $mode = 'w')
    {
        try{
            if (!@copy($this->tempFile, $fileName)){
                throw new \Exception("Please check on the path correctness in 'setConfig' function attributes.");
            }
        }
        catch (\Exception $e){
            echo $e->getMessage();
            exit();
        }
        $res = fopen($fileName, $mode);
        fwrite($res, $data);
        fclose($res);
    }

    /**
     * @param string $userCacheCatalog
     * @param string $title
     * @return string
     */
    public function getHtmlTitleFile(string $userCacheCatalog, string $title): string
    {
        return $userCacheCatalog.'/'.$title.'.html';
    }
}