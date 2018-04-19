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
    public function userCache(string $fileName,string $data)
    {
        $cac = new CacheHandler();

        $nameCookie = Config::$cookieName;
        $userDir = $cac->getCookie($nameCookie);

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

                $res = $cac->searchHostInUrls($dataUrls, $host, $title);

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
        $cac->readManifestFile($this, $manifestData);

        $addrPage = $cac->addressPage($title);

            //Getting the user card.json file url
        $cacheDir = $cac->cacheDir();
        $domain = (strtolower($this->protocol)).'://'.$this->serverName;
        $userCardJson = (Yaml::parseFile($this->fileDirs))['cardJson'];
        $userJsonUrl = $cac->getUserCatalogUrl($domain, $userDir, $cacheDir)."/$userCardJson";

            //String html templater.js script
        $scriptTemplater = $cac->getScriptUrlTemplaterJs($domain, $cacheDir);

        if ($cac->writeToManifest($this, $addrPage)) {

            $jsonData = json_decode(file_get_contents("$userCacheDir/$this->cardJson"));

                //Writing first page in the manifest file
            $cac->writeFirstPageInToManifest($this, $jsonData);

                //Writing the user json file in the manifest file
            $cac->writeToManifest($this, $userJsonUrl);

                //Writing the templater.js file in the manifest file
            $cac->writeToManifest($this, $scriptTemplater);

                //Writing all changes
            $stringManifest = $cac->manifestToString($this);
            $this->writeInFile($fileManifest, $stringManifest, 'w');
        }

            //Create templater.js
        $templaterJs = $cac->createTemplaterJs($this->userCacheCatalog, $this);

            //Setting cookie name in templater.js
        $cac->settingCookieName($templaterJs, $nameCookie, $this);

             //Add templater.js in the html
        $htmlData = $cac->addScriptBeforeBody($scriptTemplater, $htmlData);

            //Getting the html manifest string
        $userManifest = $cac->getUserCatalogUrl($domain, $userDir, $cacheDir).'/.manifest.appcache';


        if (!empty($cac->getCookie($nameCookie))) {

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
     * @param $fileName
     * @param $data
     * @param string $mode
     */
    public function copyWriteFile($fileName, $data, $mode = 'w')
    {
        try{
            if (!@copy($this->tempFile, $fileName)){
                throw new \Exception("Please check on the path correctness in 'setConfig' function attributes. Or 
                create used cache catalog.");
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

    /**
     * @param $cacheCatalog string
     * @param $fileCache string
     * @return string
     */
    public function getFileName(string $cacheCatalog, string $fileCache): string
    {
        return $cacheCatalog.'/'.md5($fileCache).'.php';
    }
}