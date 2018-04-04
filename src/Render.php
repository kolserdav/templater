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
            $htmlData = shell_exec("php $fileName");
            $htmlFileName = $this->getHtmlFileName($userCacheCatalog, $htmlData);
            $this->copyWriteFile($htmlFileName,$htmlData);
            require $htmlFileName;
        }
        if ($cacheCatalog == '.') {
            unlink($fileName);
            return false;
        }
        return true;
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
}