<?php
/**
 * Created by kolserdav
 * User: Sergey Kol'miller
 * Date: 16.04.2018
 * Time: 22:15
 */

namespace Avir\Templater\Module;


use Avir\Re\Re;

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
            $firstPage = '/'.$ren->cacheDir().'/pages/'.$jsonData->pages->$page->$key.'.html';
            if (!array_search($firstPage, $ren->arrayManifest['CACHE:'])) {
                $ren->arrayManifest['CACHE:'][] = $firstPage;
            }
            if (!array_search($key, $ren->arrayManifest['CACHE:'])) {
                $ren->arrayManifest['CACHE:'][] = $key;
            }
        }
    }
    public function writeUserJsonFileToManifest(Render $ren, $userJsonUrl)
    {
        if (!array_search($userJsonUrl, $ren->arrayManifest['CACHE:'])) {
            $ren->arrayManifest['CACHE:'][] = $userJsonUrl;
        }
    }
    public function writeTemplaterJsToManifest(Render $ren, $scriptTemplater)
    {
        if (!array_search($scriptTemplater, $ren->arrayManifest['CACHE:'])) {
            $ren->arrayManifest['CACHE:'][] = $scriptTemplater;
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
}