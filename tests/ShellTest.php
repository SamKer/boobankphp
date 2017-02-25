<?php
/**
 * Created by PhpStorm.
 * User: Sam
 * Date: 25/02/2017
 * Time: 21:19
 */

namespace Boobank;


class ShellTest extends \PHPUnit_Framework_TestCase
{

    private $shell = false;
    protected function setup() {
        $this->shell = new Shell();
    }

    protected function tearDown() {
        $this->shell = false;
    }
    public static function testRun($cmd) {
        list($t1,$t2)  = $this->shell->run("echo 'test'");
        $this->assertEquals($t1, "test");
        $this->assertEquals($t2, "test");
    }
}