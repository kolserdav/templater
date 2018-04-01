<?php
/**
 * Created by kolserdav
 * User: Sergey Kol'miller
 * Date: 01.04.2018
 * Time: 16:41
 */

namespace Avir\Templater;


class Helper
{
    /**
     * @param $args array
     * @return bool|string
     */
    public static function getDataTemplate(array $args)
    {
        return @file_get_contents($args['tempFile']);
    }

    /**
     * @param string $data
     * @return bool|array
     */
    public static function searchEt(string $data)
    {
        preg_match_all('%\#?\@[\w]+%', $data, $res);
        $result = self::filterComment($res[0]);
        if ($result !== false) {
            return $result;
        }
        else {
            return false;
        }
    }

    /**
     * @param array $tags
     * @return bool|array
     */
    protected static function filterComment(array $tags)
    {
        $noKeys = array_filter($tags,  function ($var)
        {
            return (!preg_match('%\#%',$var));
        });
        $count = count($noKeys);
        if ($count == 0){
            return false;
        }
        else {
            $arrayKeys = range(0, $count - 1);
        }
        $result = array_combine($arrayKeys, $noKeys);
        if (!empty($result)) {
            return $result;
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
        if (preg_match_all('#\{\%.*for.+in.*\%\}.*\n?\{\%.*\%\}.*\n?\{\%.*endfor.*\%\}#', $data, $result)) {
            return $result;
        }
        else {
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
        @$dataF = file_get_contents($args['viewDir'] . $args['files'][$res[$i]]);
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
     * @return string
     */
    public static function getNameVarDelIn(array $res, int $i): string
    {
        preg_match('#in.?\w*#', $res[$i], $m);
        return trim(str_replace('in', '', $m[0]));
    }

    /**
     * @param string $data
     * @return array
     */
    public static function filterCurly(string $data): array
    {
        preg_match_all('%\{\{.?\w+.?}\}%', $data, $res);
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
        return $args[$nameVar];
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
}