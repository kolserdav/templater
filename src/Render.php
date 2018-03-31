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
    public function render(array $files = [], array $args = [])
    {
        $bg = new Background();
        $argv = [
            'tempFile' => $this->tempFile,
            'viewDir' => $this->viewDir,
            'args' => $args
        ];
        $data = $bg->prepareCurly($argv);
        $dataTwo = $bg->prepareEt($data, $files, $argv);
        $cacheCatalog = $bg->setCacheCatalog($this->root);
        $fileName = $this->getFileName($cacheCatalog, $dataTwo);
        if (!file_exists($fileName)) {
            copy($this->tempFile, $fileName);
            $res = fopen($fileName, 'w');
            fwrite($res, $dataTwo);
            fclose($res);
        }
        require $fileName;
        if ($cacheCatalog == '.') {
            unlink($fileName);
        }
    }

    public function getFileName($cache_catalog, $file_cache)
    {
        return $cache_catalog.'/'.md5($file_cache).'.php';
    }

}