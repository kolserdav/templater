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
        preg_match_all('#\{\%.*for.+in.*\%\}.*\n?\{\%.*\%\}.*\n?\{\%.*endfor.*\%\}#', $data, $res);
        return $res;
    }
    public static function getPatch($view_dir, $files, $res, $i)
    {
        @$dataF = file_get_contents($view_dir . $files[$res[$i]]);
        return $dataF;
    }
    public static function getScript($nameVar)
    {
        $value = "\$value";
        return '<?'." foreach (\$$nameVar as $value){\n\t echo $value; \n\t}\n".'?>';
    }
}