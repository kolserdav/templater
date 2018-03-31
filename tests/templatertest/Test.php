<?php
/**
 * Created by kolserdav
 * User: Sergey Kol'miller
 * Date: 01.04.2018
 * Time: 1:33
 */


use PHPUnit\Framework\TestCase;
use Avir\Templater\Background;
use Avir\Templater\Render;
use Avir\Templater\Config;

class Test extends TestCase
{
    protected $argv;
    protected $root;

    protected function setUp ()
    {
        $r = new Render('template','test.template.php');
        $this->root = $r->getRoot();
        $this->argv = [

        ];
    }
    public function testBackground()
    {

    }
    public function testRender()
    {
        $r = new Render('template','test.template.php');
        $bg = new Background();
        $this->assertInternalType('bool', $r->render());
        $this->assertFalse($r->render());
        $conf = new Config();
        $conf->setConfig([
            'cache' => 'var/cache'
        ]);
        $this->assertTrue($r->render());
    }


}