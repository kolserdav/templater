<?php
/**
 * Created by kolserdav
 * User: Sergey Kol'miller
 * Date: 30.03.2018
 * Time: 20:48
 */

namespace Avir\Templater\Module;

use Avir\Templater\Module\Ajax\AjaxHelper;

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
        if (!$title) {
            $htmlFileName = $this->getHtmlFileName($this->userCacheCatalog, $htmlData);
        }
        else {
            $cacheAliases = $this->userCacheCatalog.'/aliases/';
            $aliasesFile = $cacheAliases.'data-urls.json';
            if($this->checkAndCreateDir($cacheAliases)){
                $this->checkAndCreateFile($this->jsonPath, $aliasesFile);
            }
            else {
                $dataUrls = json_decode(file_get_contents($aliasesFile));
                if($dataUrls->pages->count > 0){

                    $host = strtolower($this->protocol).'://'.$this->serverName.$_SERVER['REQUEST_URI'];
                    $res = $this->searchHostInUrls($dataUrls, $host, $title);

                    if ($res !== false) {
                        $page = 'page-'.$res;
                        $old = get_object_vars($dataUrls->pages->$page);
                        $r = array_diff_assoc($old, [$host => $title]);
                        var_dump($r);

                    }
                }

            }
            $htmlFileName = $this->getHtmlTitleFile($this->userCacheCatalog, $title);
        }

            //Writing current page to file manifest
        $this->readManifestFile($manifestData);
        $addrPage = $this->addressPage($title);
        if (!array_search($addrPage, $this->arrayManifest['CACHE:'])) {
            $this->arrayManifest['CACHE:'][] = $addrPage;
            $stringManifest = $this->manifestToString();
            $this->writeInFile($fileManifest, $stringManifest, 'w');
        }

            //Getting the html manifest string
        $userManifest = $this->getUserCatalogUrl($userDir).'/.manifest.appcache';

            //Getting the user card.json file url
        $userJsonUrl = $this->getUserCatalogUrl($userDir).'/card.json';

           //Add the user manifest tag
        //$htmlData = str_replace('<html>', "<html manifest=\"$userManifest\">", $htmlData);

            //Add the file json tag
        $script = "<script type=\"text/x-json\" src=$userJsonUrl></script>";
        preg_match('%\<script\s*src\=https?\:\/\/\w*\.\w*\/\w*\.js\><\/script\>%', $htmlData, $m);
        $htmlDataUser = str_replace($m[0], "\n$script\n$m[0]", $htmlData);

        echo $htmlDataUser;
            //Create the user cache file
        $this->copyWriteFile($htmlFileName, $htmlData);

        //require $htmlFileName;

    }

    public function searchHostInUrls($data, $host, $title, $i = 0)
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
        return $this->protocol.'://'.$this->serverName.'/'.$this->cacheDir().'/'.Config::$usersDir.'/'.$userDir;
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
        preg_match('%\w*\/%', Config::$userCache, $m);
        return str_replace($m[0], '', Config::$userCache);
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
     * @param $fileName
     * @param $jsonPath
     * @return  bool
     */
    public function checkAndCreateFile(string $jsonPath, string $fileName): bool
    {
        if (!file_exists($fileName)) {
            copy($jsonPath, $fileName);
            return true;
        }
        else {
            return false;
        }
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
        if ($this->ajaxData['load']){
            var_dump(1);
        }
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
    public function getHtmlFileName(string $userCacheCatalog, string $fileCache): string
    {
        return $userCacheCatalog.'/'.md5($fileCache).'.html';
    }
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