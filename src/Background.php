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
     * @return bool|mixed|string
     */
    public function prepareCurly(array $argv)
    {
        $data = $this->getDataTemplate($argv);
        $filterCurly = preg_match_all('%\{\{.?\w+.?}\}%', $data, $res);
        if ($filterCurly){
            $count = count($res[0]);
            global $newData1;
            for ($i = 0; $i < $count; $i ++){
                $nameVariable = trim(str_replace(['{', '}'], '',$res[0][$i]));
                $variable = $argv['args'][$nameVariable];
                if ($newData1 == null) {
                    $newData1 = str_replace($res[0][$i], $variable, $data);
                }
                else {
                    $newData1 = str_replace($res[0][$i], $variable, $newData1);
                }
            }
            return $newData1;
        }
        else {
            $newData = $data;
            return $newData;
        }
    }

    /**
     * Replace \@value on $file['value'] content
     * @param $data
     * @param $file
     * @param $argv
     * @return mixed
     */
    public function prepareEt($data, $file, $argv)
    {
        $filterEt = preg_match_all('%\@\w+%', $data, $res);
        if ($filterEt){
            $count = count($res[0]);
            global $newData2;
            for ($i = 0; $i < $count; $i ++){
                @$dataF = file_get_contents($argv['viewDir'].$file[$res[0][$i]]);
                if ($newData2 == null) {
                    $newData2 = str_replace($res[0][$i], $dataF, $data);
                }
                else {
                    $newData2 = str_replace($res[0][$i], $dataF, $newData2);
                }
            }
            $filterFor = preg_match_all('#\{\%.*for.+in.*\%\}.*\n?\{\{.*\}\}.*\n?\{\%.*endfor.*\%\}#', $newData2, $res);
            if ($filterFor) {
                $result = $this->replaceFor($newData2);
                return $this->writeVariableArrays($result, $argv['args']);
            }
            else {
                return $newData2;
            }
        }
        else {
            return $data;
        }
    }

    public function writeVariableArrays($data, $args)
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
        return $this->saveVarArr($data, $variables, $args);
    }

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
        return $saveField.$data;
    }

    public function setCacheCatalog($root)
    {
        if (parent::$cache){
            return $root.'/'.parent::$cache;
        }
        else {
            return '.';
        }
    }

    public function replaceFor($data)
    {
        if (preg_match_all('#\{\%.*for.+in.*\%\}.*\n?\{\{.*\}\}.*\n?\{\%.*endfor.*\%\}#', $data, $m)){
            global $result;
            $value = "\$value";
            $count = count($m[0]);
            for ($i = 0; $i < $count; $i ++) {
                preg_match('#in.?[\w]*#', $m[0][$i], $m1);
                $nameVariable = trim(str_replace('in', '', $m1[0]));
                $script = '<?'." foreach (\$$nameVariable as $value){\n\t echo $value; \n\t}\n".'?>';
                if ($result == null) {
                    $result = str_replace([$m[0][$i]], $script, $data);
                }
                else {
                    $result = str_replace([$m[0][$i]], $script, $result);
                }
            }
            return $result;
        }
        else {
            return $data;
        }
    }

    /**
     * @param $argv
     * @return bool|string
     */
    public function getDataTemplate($argv)
    {
        return file_get_contents($argv['tempFile']);
    }
}