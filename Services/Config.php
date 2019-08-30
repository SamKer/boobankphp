<?php


namespace SamKer\BoobankPHP\Services;


use SamKer\BoobankPHP\Services\Shell;
use Symfony\Component\Filesystem\Filesystem;

class Config
{


    /**
     * @var array|null
     */
    public $params = null;

    /**
     * @var Shell|null
     */
    private $shell = null;

    /**
     * Config constructor.
     * @param array $params
     * @param Shell $shell
     */
    public function __construct($params = [], Shell $shell)
    {
        $this->params = $params;
        $this->shell = $shell;
    }

    public function testConfig($throwException = false) {
        $b = true;
        $test = [
            "params" => [
                "storage_path" => ["present" => false, "check" => false],
                "dateInterval" => ["present" => false, "check" => false],
                "filters" => ["present" => false, "check" => false],
                "watch" => ["present" => false, "check" => false],
            ],
            "programs" => [
                "weboob" => ["name" => "weboob", "present" =>false, "path" => ""],
                "weboob-config" => ["name" => "weboob-config", "present" =>false, "path" => ""],
                "boobank" => ["name" => "boobank", "present" =>false, "path" => ""]
            ]
        ];

            $fs = new Filesystem();
        //params---------------------------------------------

        // storage_path
        if (isset($this->params['storage_path'])) {
            $test['params']['storage_path']["present"] = true;
        } else {
            $b = false;
        }
        if ($fs->exists($this->params['storage_path']) && is_writable($this->params['storage_path'])) {
            $test['params']['storage_path']["check"] = true;
        } else {
            $b = false;
        }

        // dateinterval
        if (isset($this->params['dateInterval'])) {
            $test['params']['dateInterval']['present'] = true;
        } else {
            $b = false;
        }
        if(is_string($this->params['dateInterval'])) {
            $test['params']['dateInterval']['check'] = true;
        } else {
            $b = false;
        }

        //list
        if (isset($this->params['filters'])) {
            $test['params']['filters']['present'] = true;
        } else {
            $b = false;
        }
        if(is_array($this->params['filters'])) {
            array_push($this->params['filters']['list'], 'id');
            array_push($this->params['filters']['history'], 'id');
            $test['params']['filters']['check'] = true;
        } else {
            $b = false;
        }


        //--------------------------------------------------

        //define pathcommands-----------
        if ($fs->exists($this->params["binaries"]['weboob'])) {
            $test['programs']['weboob']['present'] = true;
            $test['programs']['weboob']['path'] = $this->params["binaries"]['weboob'];

        } else {
            $b = false;
        }

        if ($fs->exists($this->params["binaries"]['weboob-config'])) {
            $test['programs']['weboob-config']['present'] = true;
            $test['programs']['weboob-config']['path'] = $this->params["binaries"]['weboob-config'];

        } else {
            $b = false;
        }

        if ($fs->exists($this->params["binaries"]['boobank'])) {
            $test['programs']['boobank']['present'] = true;
            $test['programs']['boobank']['path'] = $this->params["binaries"]['boobank'];
        } else {
            $b = false;
        }

        //----------

        //setting home
        $home = $this->params["storage_path"];
        if (!$fs->exists($home)) {
            throw new \Exception("no home dir at " . $home);
        }


        //backend created by weboob at backend
        $this->params['backend_path'] = $home . "/backends";
        if (!$fs->exists($this->params['backend_path'])) {
            $fs->touch($this->params['backend_path']);
        }

        //specific rules at
        $this->params['watch_rules'] = $home . "/watchrules";
        if (!$fs->exists($this->params['watch_rules'])) {
            $fs->mkdir($this->params['watch_rules']);
        }

        if($throwException) {
            if($b === false) {
                throw new \Exception("config error");
            } else {
                return $this->params;
            }
        }
        return $test;
    }

}