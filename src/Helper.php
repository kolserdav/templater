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
     * @param string $data
     * @return array
     */
    public static function searchEt(string $data): array
    {
        preg_match_all('%\#?\@[\w]+%', $data, $res);
        return $res;
    }

    /**
     * @param array $tags
     * @return array
     */
    public static function filterComment(array $tags)
    {
        return array_filter($tags,  function ($var)
        {
            return (!preg_match('%\#%',$var));
        });

    }
    public static function searchFor($data)
    {
        if (preg_match_all('#\{\%.*for.+in.*\%\}.*\n?\{\%.*\%\}.*\n?\{\%.*endfor.*\%\}#', $data, $result)) {
            return $result;
        }
        else {
            return false;
        }
    }
    public static function getPatch($res, $i, $args)
    {
        @$dataF = file_get_contents($args['viewDir'] . $args['files'][$res[$i]]);
        return $dataF;
    }
    public static function getScript($nameVar)
    {
        $value = "\$value";
        return '<?'." foreach (\$$nameVar as $value){\n\t echo $value; \n\t}\n".'?>';
    }
    public static function getNameVarDelIn($res, $i)
    {
        preg_match('#in.?\w*#', $res[$i], $m);
        return trim(str_replace('in', '', $m[0]));
    }
    public static function filterCurly($data)
    {
        if (preg_match_all('%\{\{.?\w+.?}\}%', $data, $res)){
            return $res[0];
        }
        else {
            return false;
        }
    }
    public static function delCurly($res, $i, $args)
    {
        $nameVar = trim(str_replace(['{', '}'], '',$res[$i]));
        return $args[$nameVar];
    }
}