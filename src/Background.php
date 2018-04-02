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
     * Replace 'field' on $file['value'] content
     * And prepare 'for in' constructions
     * @param $data string
     * @param $args array
     * @param $i = 0 int
     * @return string
     */
    public function prepareEt(array $args, string $data, int $i = 0): string
    {
        if (!$arr = Helper::searchEtDirt($data)){
            return $data;
        }
        else {
            if ($i == 50){
                try{
                    throw new \Exception("The server performed a cyclic redirect. 
                    Variable $arr[0] use recursively." );
                }
                catch (\Exception $e){
                   echo $e->getMessage();
                    exit();
                }
            }
                //Processes on 'field' replace
            $newData = $this->replaceEt($data, $args);

            return $this->prepareEt($args, $newData, $i + 1);
        }
    }

    /**
     * Replace '@'field on file.patch content
     * @param string $data
     * @param array $args
     * @return mixed|null|string
     */
    public function replaceEt(string $data, array $args)
    {
        $res = Helper::searchEtDirt($data);

        if ($res) {
            return Helper::replaceVars($res, $data, $args,
                function ($res, $i, $args){
                    return Helper::getPatch($res, $i, $args);
                });
        }
        else {
            return $data;
        }
    }

    /**
     * Filter template file, and replace {{ value }} on $argv['args']['value']
     * @param $args array
     * @param $dataTwo
     * @return bool|mixed|string
     * @throws
     */
    public function prepareCurly(array $args, string $dataTwo)
    {
        $data = $this->getData($args, $dataTwo);

            //Search and replace {{value}}
        $res = Helper::filterCurly($data);
        if (!empty($res)){
            return Helper::replaceVars($res, $data, $args,
                function ($res, $i, $args){
                    return Helper::delCurly($res, $i, $args);
                });
        }
        else {
            return $data;
        }
    }

    public function prepareFor($args, $newData)
    {
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
     * @param $data string
     * @param $args array
     * @return string
     */
    public function replaceFor(string $data, $args): string
    {
        if ($preRes = Helper::searchFor($data)){
            $res = Helper::filterFor($preRes);

            $args['resFor'] = $res['forIn'];
            return Helper::replaceVars($res['value'], $data, $args, function($res, $i, $resFor){
                $nameVar = Helper::getNameVarDelIn($res, $i, $resFor);
                return Helper::getScript($nameVar);
            });

        }
        else {
            return $data;
        }
    }
    public function clearForDirt($data)
    {
        $in = Helper::forPregIn($data);
        $end = Helper::forPregEnd($data);
        if (!$in && !$end)
        {
            return $data;
        }
        else {
            $newData = str_replace([$in,$end],'',$data);
            return $this->clearForDirt($newData);
        }
    }

    /**
     * Read sent params with (names key array) for 'for in'
     * @param string $data
     * @param array $args
     * @return string
     */
    private function readVariableArrays(string $data, array $args): string
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
    private function saveVarArr(string $data, array $variables, array $args):string
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
     * @param string $root
     * @return string
     */
    public function setCacheCatalog(string $root): string
    {
        if (parent::$cache){
            return $root.'/'.parent::$cache;
        }
        else {
            return '.';
        }
    }

    /**
     * @param $args
     * @param null $dataTwo
     * @return bool|null|string
     */
    public function getData(array $args, $dataTwo = null)
    {
        //Checking second enter for initial {{value}} in patch files
        if ($dataTwo != null){
            return $dataTwo;
        }
        else {
            $data = Helper::getDataTemplate($args);

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
            return $data;
        }
    }
}