<?php
/**
 * Created by kolserdav
 * User: Sergey Kol'miller
 * Date: 30.03.2018
 * Time: 22:41
 */

namespace Avir\Templater;


class Background extends Config
{

    /**
     * Filter template file, and replace {{ value }} on $argv['args']['value']
     * @param $args array
     * @param $dataTwo
     * @return bool|mixed|string
     * @throws
     */
    public function prepareCurly(array $args, $dataTwo = null)
    {
            //Checking second enter for initial {{value}} in patch files
        if ($dataTwo != null){
            $data = $dataTwo;
        }
        else {
            $data = $this->getDataTemplate($args);
        }

            //Checking setting file template path
        if (!$data){
            try {
                $message = 'Error get content. Please check your template-file path settings';
                throw new \Exception($message);
            }
            catch (\Exception $e){
                echo $e->getMessage();
                exit();
            }
        }
            //Search and replace {{value}}
        $res = Helper::filterCurly($data);
        if ($res){
            return $this->replaceVars($res, $data, $args,
                function ($res, $i, $args){
                    return Helper::delCurly($res, $i, $args);
                });
        }
        else {
            return $data;
        }
    }

    /**
     * Replace 'field' on $file['value'] content
     * And prepare 'for in' constructions
     * @param $data string
     * @param $file array
     * @param $argv array
     * @return string
     */
    public function prepareEtAndFor(string $data, array $args): string
    {
            //Processes on 'field' replace
       $newData = $this->replaceEt($data, $args);

            //Processes on {% for in %} replace
        $filterFor = Helper::searchFor($newData);
        if ($filterFor) {
            $result = $this->replaceFor($newData, $args);
            return $this->readVariableArrays($result, $args);
        }
        else {
            return $newData;
        }

    }

    /**
     * Read sent params with (names key array) for 'for in'
     * @param $data
     * @param $args
     * @return string
     */
    public function readVariableArrays(string $data, array $args): string
    {
        $keys = array_keys($args);
        $count = count($keys);
        global $variables;
        for ($i = 0; $i < $count; $i ++) {
            if (preg_match('%for\_[\w]*%', $keys[$i], $m)) {
                $variables[] = $m[0];
            }
            else {
                continue;
            }
        }
        $vars = $variables;
        unset($variables);
        return $this->saveVarArr($data, $vars, $args);
    }

    /**
     * Write appointment variables expression it the file-assembly top;
     * @param string $data
     * @param array $variables
     * @param array $args
     * @return string
     */
    public function saveVarArr(string $data, array $variables, array $args):string
    {
        global $vars, $vals;
        $count = count($variables);
        for ($i = 0; $i < $count; $i ++) {
            $key = $variables[$i];
            $val = str_replace('for_', '', $key );
            foreach ($args[$key] as $value){
                if (gettype($value) == 'string'){
                    $vals .= "'$value',";
                }
                else {
                    $vals .= "$value,";
                }
            }
            $vals = trim($vals, ',');
            $vals = "[$vals]";
            $vars .= "\$$val = $vals;\n";
            unset($vals);
        }
        $saveField = "<?\n$vars ?>\n";
        unset($vars);
        return $saveField.$data;
    }

    /**
     * @param $root
     * @return string
     */
    public function setCacheCatalog($root)
    {
        if (parent::$cache){
            return $root.'/'.parent::$cache;
        }
        else {
            return '.';
        }
    }

    /**
     * @param $data string
     * @return string
     */
    public function replaceFor(string $data, $args): string
    {
        if ($res = Helper::searchFor($data)){
            return $this->replaceVars($res[0], $data, $args, function($res, $i){
                $nameVar = Helper::getNameVarDelIn($res, $i);
                return Helper::getScript($nameVar);
            });

        }
        else {
            return $data;
        }
    }

    /**
     * @param $argv array
     * @return bool|string
     */
    public function getDataTemplate(array $args)
    {
        return @file_get_contents($args['tempFile']);
    }

    public function replaceEt(string $data, array $args)
    {
        $search = Helper::searchEt($data);
        $res = Helper::filterComment($search[0]);
        if ($res) {
            return $this->replaceVars($res, $data, $args,
                function ($res, $i, $args){
                return Helper::getPatch($res, $i, $args);
            });
        }
        else {
            return $data;
        }
    }
    public function replaceVars($res, $data, $args, $dataC)
    {
        $count = count($res);
        global $newData;
        $newData = null;
        for ($i = 0; $i < $count; $i++) {
            if (is_callable($dataC)){
                $dataF = $dataC($res,  $i, $args);
            }
            else{
                $dataF = $dataC;
            }
            if ($newData == null) {
                $newData = str_replace($res[$i], $dataF, $data);
            } else {
                $newData = str_replace($res[$i], $dataF, $newData);
            }
        }
        $result = $newData;
        unset($newData);
        return $result;
    }
}