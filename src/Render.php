<?php
/**
 * Created by kolserdav
 * User: Sergey Kol'miller
 * Date: 30.03.2018
 * Time: 20:48
 */

namespace Avir\Templater;

class Render extends Templater
{

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
        $userCacheCatalog = $bg->setUserCacheCatalog($this->root);

            //Get name file of cache and operation with him
        $fileName = $this->getFileName($cacheCatalog, $dataTwo);

            //Checking the cache file
        if (!file_exists($fileName)) {

                //Creating a cache file
            $this->copyWriteFile($fileName, $dataTwo);
        }
        if (!$userCacheCatalog){
                //Require ready content file
            require $fileName;
        }
        else {
           //    echo file_get_contents(__DIR__.'./storage/cookie.html');
            $this->userCache($fileName, $userCacheCatalog);
        }
        if ($cacheCatalog == '.') {
            unlink($fileName);
            return false;
        }
        return true;
    }
    public function userCache($fileName, $userCacheCatalog)
    {
        $bg = new Background();
        $cookie = $this->getCookie('test');
        if(empty($cookie)){
            $userDir = false;
        }
        else {
            $userDir = $this->getCookie('test');
        }
        if($this->ajaxData) {var_dump(2222);exit();

        }
        else {
            $htmlData = shell_exec("php $fileName");
            $title = Helper::searchTitle($htmlData);
            if ($userDir){
                $userCacheDir = $this->usersDir.'/'.$userDir;
                if (!is_dir($userCacheDir)) {
                    @$userCacheDir = mkdir($userCacheDir);
                }
            }
            if (!$title) {
                $htmlFileName = $this->getHtmlFileName($userCacheCatalog, $htmlData);
            } else {
                $htmlFileName = $this->getHtmlTitleFile($userCacheCatalog, $title);
            }
            $this->copyWriteFile($htmlFileName, $htmlData);
            require $htmlFileName;
        }
    }
    public function getCookie($cookie_name)
    {
        return base64_decode($_COOKIE[$cookie_name]);
    }

    public function ajax()
    {
       //var_dump($this->ajaxData);
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