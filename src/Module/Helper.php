<?php
/**
 * Created by kolserdav
 * User: Sergey Kol'miller
 * Date: 01.04.2018
 * Time: 16:41
 */

namespace Avir\Templater\Module;


class Helper
{
    private static $keysArr;
    public static $data;
    /**
     * @param $args array
     * @return bool|string
     */
    public static function getDataTemplate(array $args)
    {
        return @file_get_contents($args['tempFile']);
    }

    /**
     * @param string|array $data
     * @return bool|array
     */
    public static function searchEt($data)
    {
        if (is_string($data)) {
            $dirt = self::searchEtDirt($data);
        }
        else {
            $dirt = $data;
        }
        $res = array_map(function ($arr){
            return trim(str_replace(['%', '{', '}'], '', $arr));
            }, $dirt);
        $result = self::rangeArray($res);
        if (!empty($result)) {
            return $result;
        }
        else {
            return false;
        }
    }

    /**
     * @param $data
     * @return array|bool
     */
    public static function searchEtDirt($data)
    {
        if( preg_match_all('#\{\%.*[\w]*.*\%\}#', $data, $res)) {
            $res =  array_filter($res[0], function ($var) {
                return (!self::forPreg($var));
            });
            return self::rangeArray($res);
        }
        else {
            return false;
        }
    }

    /**
     * @param array $tags
     * @return bool|array
     */
    protected static function rangeArray(array $tags)
    {
        $count = count($tags);
        if ($count == 0){
            return false;
        }
        else {
            $arrayKeys = range(0, $count - 1);
        }
        $result = array_combine($arrayKeys, $tags);
        if (!empty($result)) {
            return $result;
        }
        else {
            return false;
        }

    }

    /**
     * @param string $data
     * @return bool
     */
    public static function forPreg(string $data, $i = false)
    {
        if ($i == true){
            $pattern = '#\n?\{\%\s*for.+in\s+\w*\s*\%\}.*\{\{\s*\w*\s*\}\}.*\{\%\s*endfor\s*\%\}\s*#';
            $val = preg_match($pattern, $data, $result);
            if ($val) {
                return $result;
            } else {
                return false;
            }
        }
        else {
            $pattern = '#\n?\{\%\s*for.+in\s+\w*\s*\%\}.*\{\{\s*\w*\s*\}\}.*\{\%\s*endfor\s*\%\}\s*#';
            $val = preg_match_all($pattern, $data, $result);
            if ($val) {
                return $result;
            } else {
                return false;
            }
        }
    }

    /**
     * @param $var
     * @return bool
     */
    public static function forPregIn($var)
    {
        $val = preg_match('%\s*\{\%\s*for\s+\w*.+in\s+\w*\s*\%\}\s*%', $var, $result);
        if ($val){
            return $result[0];
        }
        else {
            return false;
        }
    }

    /**
     * @param $res
     * @param $args
     */
    public static function checkForAttrib($res, $args)
    {
        $var = self::getNameVarDelIn($res);
            //Check args keys  and del 'for_' prefix;
        $val = self::delFor(self::rangeArray(self::searchLineFor(array_keys($args))));
        if ($val == null){
            try {
                throw new \Exception("Empty 'render()' function parameters as [for_$var[0] => [variables]]");
            }
            catch (\Exception $e){
                echo $e->getMessage();
                exit();
            }
        }
        $vol = array_diff($var, $val);
        $vol = self::rangeArray($vol);
        if (!empty($vol)){
            try {
                throw new \Exception("Empty 'render()' function parameter as [for_$vol[0] => [variables]]");
            }
            catch (\Exception $e){
                echo $e->getMessage();
                exit();
            }
        }
    }

    /**
     * @param $var
     * @return bool
     */
    public static function forPregEnd($var)
    {
        $val = preg_match('%\s*\{\%\s*endfor\s*\%\}\s*%', $var, $result);
        if ($val){
            return $result[0];
        }
        else {
            return false;
        }
    }

    /**
     * @param string $data
     * @return bool|array
     */
    public static function searchFor(string $data)
    {
        if ($result = self::forPreg($data)) {
            return $result;
        }
        else {
            return false;
        }
    }

    /**
     * @param $preRes
     * @return mixed
     */
    public static function filterFor($preRes)
    {
        $res['value'] = array_map(function ($val){
            return self::searchValue($val);
        }, $preRes[0]);
        $res['forIn'] = array_map(function ($val){
            return self::forPregIn($val);
        }, $preRes[0]);
        $res['endFor'] = array_map(function ($val){
            return self::forPregEnd($val);
        }, $preRes[0]);
        return $res;
    }

    /**
     * @param $data
     * @return bool
     */
    public static function searchValue($data)
    {
        $res = preg_match('%\s*\{\{\s*\w*\s*\}\}\s*%', $data, $result);
        if ($res) {
            return $result[0];
        } else {
            return false;
        }

    }


    /**
     * @param array$res
     * @param int $i
     * @param array $args
     * @return bool|string
     */
    public static function getPatch(array $res, int $i, array $args)
    {
        $key = Helper::searchEt($res);
        $file = $args['viewDir'] . $args['files'][$key[$i]];
        @$dataF = file_get_contents($file);
        return $dataF;
    }

    /**
     * @param string $nameVar
     * @return string
     */
    public static function getScript(string $nameVar): string
    {
        $value = "\$value";
        return '<?'." foreach (\$$nameVar as $value){\n\t echo $value; \n\t}\n".'?>';
    }

    /**
     * @param array $res
     * @param int $i
     * @param array $args
     * @return string|array|bool
     */
    public static function getNameVarDelIn(array $res, int $i = -1, $args = array())
    {
        if ($i != -1) {
            if (!empty($args['resFor'])) {
                $res = $args['resFor'];
            }
            $m = self::delIn($res, $i);

            return trim(str_replace('in', '', $m[0]));
        }
        else {
            $val = preg_match_all('#in\s*\w*#', implode($res), $m);
            $result = array_map(function ($val){
                return trim(str_replace('in', '', $val));
            },$m[0]);
            if ($val){
                return $result;
            }
            else {
                return false;
            }
        }
    }

    /**
     * @param $res
     * @param int $i
     * @return array
     */
    public static function delIn($res, $i = -1)
    {
        if ($i != -1) {
            preg_match('#in\s*\w*#', $res[$i], $m);
        }
        else {
            $m = array_map(function ($val){
                preg_match('#in\s*\w*#', $val, $m);
                return $m[0];
            }, $res);
        }
        return $m;
    }

    /**
     * @param string $data
     * @return array
     */
    public static function filterCurly(string $data): array
    {
        preg_match_all('%\{\{.?[\w]*.{0,4}\}\}%', $data, $res);
        return $res[0];

    }

    /**
     * @param array $res
     * @param int $i
     * @param array $args
     * @return string
     */
    public static function delCurly(array $res, int $i, array $args): string
    {
        $nameVar = trim(str_replace(['{', '}'], '',$res[$i]));
        if ($args[$nameVar] == null){
            try {
                throw new \Exception("Problem with 'for in' syntax in your template file.<br>
                Construction {%for value in array%}{{value}}{%endfor%} must be in one line located.
                Or no data for {{ value }} in render(['value' => parameters]).  ");
            }
            catch (\Exception $e){
                echo $e->getMessage();
                exit();
            }
        }
        return $args[$nameVar];
    }

    /**
     * @param $keys
     * @param int $i
     * @return array
     */
    public static function searchLineFor($keys, $i = -1)
    {
        if ($i != -1){
        preg_match('%for\_[\w]*%', $keys[$i], $m);
        return $m;
        }
        else {
            $arr = array_filter($keys, function ($key){
                return (preg_match('%for\_[\w]*%', $key, $m));
            });

            return $arr;
        }
    }

    /**
     * Replace any vars module
     * @param array $res
     * @param string $data
     * @param array $args
     * @param callable $dataCall
     * @return string
     */
    public static function replaceVars(array $res, string $data, array $args, callable $dataCall): string
    {
        $count = count($res);
        global $newData;
        $newData = null;
        for ($i = 0; $i < $count; $i++) {

            $dataF = $dataCall($res,  $i, $args);

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

    /**
     * @param $key
     * @param int $i
     * @return mixed
     */
    public static function delFor($key, $i = 0)
    {
        if(is_array($key)) {
            if ($i < count($key)) {
                static::$keysArr[] = str_replace('for_', '', $key[$i]);

                return self::delFor($key, $i + 1);

            }
            else {
                return self::$keysArr;
            }
        }
        else {
            return str_replace('for_', '', $key);
        }
    }

    public static function searchTitle($data)
    {
        if (!preg_match('%\<title\>.*\<\/title\>%', $data, $res)){
            return false;
        }
        else {
            return trim(str_replace(['<','>','/','title'], '', $res[0]));
        }
    }
}