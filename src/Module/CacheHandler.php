<?php
/**
 * Created by kolserdav
 * User: Sergey Kol'miller
 * Date: 16.04.2018
 * Time: 22:15
 */

namespace Avir\Templater\Module;


class CacheHandler
{
    /**
     * Insert script src tag
     * @param string $script
     * @param string $data
     * @return string
     */
    public function addScriptBeforeBody(string $script, string $data): string
    {
        $script = "<script src=$script></script>";
        preg_match('%\<\/body\>%', $data, $m);
        return str_replace($m[0], "$script\n$m[0]", $data);
    }

    /**
     * @param string $urlFile
     * @param string $data
     * @param string $userData
     * @return string
     */
    public function addScriptJsonFile(string $urlFile, string $data, string $userData): string
    {
        $script = "<script id = \"jsonFile\" type=\"text/x-json\" src=$urlFile></script>";
        preg_match('%\<script\s*src\=https?\:\/\/\w*.*.js\><\/script\>%', $data, $m);
        return str_replace($m[0], "\n$script\n$m[0]", $userData);
    }

    public function settingCookieName($fileJs, $nameCookie, Render $ren)
    {
        $jsData = file_get_contents($fileJs);
        $newJs = preg_replace('%let\s*e\=a\(\"\w*\"\)%', "let e=a(\"$nameCookie\")",$jsData);
        $ren->writeInFile($fileJs, $newJs, 'w');
    }

    public function createTemplaterJs($catalog, Render $ren)
    {
        $ren->checkAndCreateDir($catalog.'/js');
        $templaterJs = $catalog.'/js/templater.js';
        $ren->checkAndCreateFile(__DIR__.'/../../storage/templater.js', $templaterJs);
        return $templaterJs;
    }

    public function writeFirstPageInToManifest(Render $ren, $jsonData)
    {
        if ($jsonData->pages->count == 2) {
            $page = 'page-0';
            $key = array_keys(get_object_vars($jsonData->pages->$page))[0];
            $firstPage = '/'.$this->cacheDir().'/pages/'.$jsonData->pages->$page->$key.'.html';
            $this->writeToManifest($ren, $firstPage);
            $this->writeToManifest($ren, $key);
        }
    }
    public function writeToManifest(Render $render, string $data): bool
    {
        if (!array_search($data, $render->arrayManifest['CACHE:'])) {
            $render->arrayManifest['CACHE:'][] = $data;
            return true;
        }
        else{
            return false;
        }
    }

    public function addPrefixForTitle($host, $title)
    {
        preg_match('%\D*%', $host, $m);
        $res = str_replace($m[0], '', $host);
        return $title."-$res";
    }
    public function checkAndCreateAliasesFile(Render $ren)
    {
        $cacheAliases = $ren->userCacheCatalog.'/aliases/';
        $aliasesFile = $cacheAliases.'data-urls.json';
        $ren->checkAndCreateDir($cacheAliases);
        $ren->checkAndCreateFile($ren->jsonPath, $aliasesFile);
        return $aliasesFile;
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
        return false;
    }


    /**
     * @param Render
     * @return string
     */
    public function manifestToString(Render $ren)
    {
        $stringManifest['CACHE:'][] = implode("\n", $ren->arrayManifest['CACHE:']);
        $stringManifest['NETWORK:'][] = implode("\n",$ren->arrayManifest['NETWORK:']);
        $stringManifest['FALLBACK:'][] = implode("\n",$ren->arrayManifest['FALLBACK:']);
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
     * @param Render $ren
     * @param array $array
     * @param int $i
     * @param int $y
     * @return array|bool
     */
    public function readManifestFile(Render $ren, array $data, array $array = array(), int $i = 3, int $y = 0)
    {
        $count = count($data);
        $data[$i] = trim($data[$i]);
        if ($data[$i] !== "NETWORK:" && $y === 0) {
            $array["CACHE:"][] = $data[$i];
            $this->readManifestFile($ren, $data, $array, $i + 1);
        }
        else if ($data[$i] === "NETWORK:" && $y === 0){
            $this->readManifestFile($ren, $data, $array, $i + 1, 1);
        }
        else if ($y === 1 && $data[$i] !== "FALLBACK:"){
            $array["NETWORK:"][] = $data[$i];
            $this->readManifestFile($ren, $data, $array, $i + 1, 1);
        }
        else if ($data[$i] === "FALLBACK:" && $y === 1){
            $this->readManifestFile($ren, $data, $array, $i + 1, 2);
        }
        else if ($y === 2 && $i < $count){
            $array["FALLBACK:"][] = $data[$i];
            $this->readManifestFile($ren, $data, $array,$i + 1, 2);
        }
        else if ($y === 2 && $i === $count){
            $ren->arrayManifest = $array;
            return $array;
        }
        return false;
    }

    public function getScriptUrlTemplaterJs($domain, $cacheDir)
    {
        return $domain.'/'.$cacheDir.'/pages/js/templater.js';
    }


    /**
     * @param string $userDir
     * @param string $domain
     * @param string $cacheDir
     * @return string
     */
    public function getUserCatalogUrl($domain, string $userDir, $cacheDir): string
    {
        return $domain.'/'.$cacheDir.'/'.Config::$usersDir.'/'.$userDir;
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
     * @param string $userCacheCatalog
     * @param string $fileCache
     * @return string
     */
    public function getHtmlFileName(string $userCacheCatalog, string $fileCache): string
    {
        return $userCacheCatalog.'/'.md5($fileCache).'.html';
    }
}