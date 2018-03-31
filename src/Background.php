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
     * Filter template file, and replace {{ value }} on $args['value']
     * @param array $args
     * @param $argv
     * @return bool|mixed|string
     */
    public function prepareCurly(array $args, $argv)
    {
        $data = $this->getDataTemplate($argv);
        $filterCurly = preg_match_all('%\{\{.?\w+.?}\}%', $data, $res);
        if ($filterCurly){
            $count = count($res[0]);
            global $newData1;
            for ($i = 0; $i < $count; $i ++){
                $nameVariable = trim(str_replace(['{', '}'], '',$res[0][$i]));
                $variable = $args[$nameVariable];
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
                return $this->replaceFor($newData2);
            }
            else {
                return $newData2;
            }
        }
        else {
            return $data;
        }
    }
    public function prepareFor($data, $argv, $file)
    {
        $filterFor = preg_match_all('#\{\%.*for.+in.*\%\}.*\n?\{\{.*\}\}.*\n?\{\%.*endfor.*\%\}#', $data);
        if ($filterFor) {
            preg_match_all('%\@\w+%', $data, $res);
            $count = count($res[0]);
            global $newData3;
            for ($i = 0; $i < $count; $i ++){
                @$dataF = file_get_contents($argv['viewDir'].$file[$res[0][$i]]);
                $dataF = $this->replaceFor($dataF);
                if ($newData3 == null) {
                    $newData3 = str_replace($res[0][$i], $dataF, $data);
                }
                else {
                    $newData3 = str_replace($res[0][$i], $dataF, $newData3);
                }
            }
            return $newData3;
        }
        else {
            return $data;
        }

    }

    /*public function writeFor($data, $argv, $file)
    {
        $keys = array_keys($argv['args']);
        global $keysFor;
        $count = count($keys);
        for ($i = 0, $keysFor = []; $i < $count; $i ++ ) {
            $arrayFor = preg_match('%for_.*%', $keys[$i], $m1);
            if ($arrayFor) {
                $keysFor[] = $m1[0];
            }
            else {
                continue;
            }
        }
        return $this->prepareFor($data, $argv, $file);
    }*/

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
                $script = '<?'."foreach (\$$nameVariable as $value){echo $value;}".'?>';
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