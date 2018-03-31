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
            'args' => $args
        ];

            //Get first data with replace {{values}} on 'echo $values;'
        if (!empty($args)){
            $data = $bg->prepareCurly($argv);
        }
        else {
            $data = $bg->getDataTemplate($argv);
        }

            /**
             * Get second data with replace @field on custom .html files content
             * And replace {% for in block %} on foreach(){} construction
             */
        if (!empty($args) || !empty($files)){
            $dataTwo = $bg->prepareEtAndFor($data, $files, $argv);
        }
        else {
            $dataTwo = $data;
        }

            //Get custom cache catalog
        $cacheCatalog = $bg->setCacheCatalog($this->root);

            //Get name file of cache and operation with him
        $fileName = $this->getFileName($cacheCatalog, $dataTwo);
        if (!file_exists($fileName)) {
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