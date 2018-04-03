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

            //Get name file of cache and operation with him
        $fileName = $this->getFileName($cacheCatalog, $dataTwo);

            //Checking the cache file
        if (!file_exists($fileName)) {

                //Creating a cache file
            copy($this->tempFile, $fileName);
            $res = fopen($fileName, 'w');
            fwrite($res, $dataTwo);
            fclose($res);
        }

            //Require ready content file
        require $fileName;
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

}