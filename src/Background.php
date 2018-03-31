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
     * @param $argv
     * @param $dataTwo
     * @return bool|mixed|string
     * @throws
     */
    public function prepareCurly(array $argv, $dataTwo = null)
    {
            //Checking second enter for initial {{value}} in patch files
        if ($dataTwo != null){
            $data = $dataTwo;
        }
        else {
            $data = $this->getDataTemplate($argv);
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
        $filterCurly = preg_match_all('%\{\{.?\w+.?}\}%', $data, $res);
        if ($filterCurly){
            $count = count($res[0]);
            global $newData;
            $newData = null;
            for ($i = 0; $i < $count; $i ++){
                $nameVar = trim(str_replace(['{', '}'], '',$res[0][$i]));
                $var = $argv['args'][$nameVar];
                if ($newData == null) {
                    $newData = str_replace($res[0][$i], $var, $data);
                }
                else {
                    $newData = str_replace($res[0][$i], $var, $newData);
                }
            }
            $result = $newData;
            unset($newData);
            return $result;
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
    public function prepareEtAndFor(string $data, array $file, array $argv): string
    {
            //Processes on 'field' replace
        $filterEt = preg_match_all('%\@\w+%', $data, $res);
        if ($filterEt){
            $count = count($res[0]);
            global $newData;
            $newData = null;
            for ($i = 0; $i < $count; $i ++){
                @$dataF = file_get_contents($argv['viewDir'].$file[$res[0][$i]]);
                if ($newData == null) {
                    $newData = str_replace($res[0][$i], $dataF, $data);
                }
                else {
                    $newData = str_replace($res[0][$i], $dataF, $newData);
                }
            }
            $pattern = '#\{\%.*for.+in.*\%\}.*\n?\{\{.*\}\}.*\n?\{\%.*endfor.*\%\}#';

                //Processes on {% for in %} replace
            $filterFor = preg_match_all($pattern, $newData, $res);
            if ($filterFor) {
                $result = $this->replaceFor($newData);
                unset($newData);
                return $this->readVariableArrays($result, $argv['args']);
            }
            else {
                $result = $newData;
                unset($newData);
                return $result;
            }
        }
        else {
            return $data;
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
    public function replaceFor(string $data): string
    {
        if (preg_match_all('#\{\%.*for.+in.*\%\}.*\n?\{\{.*\}\}.*\n?\{\%.*endfor.*\%\}#', $data, $m)){
            global $result;
            $value = "\$value";
            $count = count($m[0]);
            for ($i = 0; $i < $count; $i ++) {
                preg_match('#in.?[\w]*#', $m[0][$i], $m1);
                $nameVar = trim(str_replace('in', '', $m1[0]));
                $script = '<?'." foreach (\$$nameVar as $value){\n\t echo $value; \n\t}\n".'?>';
                if ($result == null) {
                    $result = str_replace([$m[0][$i]], $script, $data);
                }
                else {
                    $result = str_replace([$m[0][$i]], $script, $result);
                }
            }
            $res = $result;
            unset($result);
            return $res;
        }
        else {
            return $data;
        }
    }

    /**
     * @param $argv array
     * @return bool|string
     */
    public function getDataTemplate(array $argv)
    {
        return @file_get_contents($argv['tempFile']);
    }
}