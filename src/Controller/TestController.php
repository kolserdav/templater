<?php
/**
 * Created by kolserdav
 * User: Sergey Kol'miller
 * Date: 27.03.2018
 * Time: 1:46
 */

namespace Avir\Templater\Controller;

use Avir\Templater\Module\Render;
use Avir\Templater\Module\Config;

class TestController
{

    public function test()
    {

        $c = new Config();
        $c->setConfig([
            'cache' => 'var/cache',
            'userCache' => 'public/cache'
        ]);
        $r = new Render('template', '/template.twig');
        $sss = "<hr>ffffff<hr>";
        $man = 'http://'.$_SERVER['SERVER_NAME']."/.manifest.appcache";
        $r->render(
            [
                'manifest'=>$man,
                'test1' => 'aaa',
                'test2' => 'bbb',
                'test3' => 'ccc',
                'for_array' => [1,2,3,$sss],
                'for_array2' => ['f','E'],
                'for_array3' => ['a','f','s']
            ],[
            'manif' => 'app/manifest.twig',
            'field1' => 'index.twig',
            'field2' => 'app/ind.twig',
            'title' => 'app/title.twig'
        ]);
    }
    public function tests()
    {
        $time1 = microtime();
        $c = new Config();
        $c->setConfig([
            'cache' => 'var/cache',
            'userCache' => 'public/cache'
        ]);
        $r = new Render('template', '/template.twig', 'users');
        $sss = "<hr>ffffff<hr>";
        $r->render(
            [
                'test1' => 'aaa',
                'test2' => 'bbb',
                'test3' => 'ccc',
                'for_array' => [1,2,3,$sss],
                'for_array2' => ['f','E'],
                'for_array3' => ['a','f','s']
            ],[
            'field1' => 'index.twig',
            'field2' => 'app/ind.twig',
            'title' => 'app/title.twig'
        ]);
        $time2 = microtime();

        echo '<br>'.($time2 - $time1);
        return true;
    }
}