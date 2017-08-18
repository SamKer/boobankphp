<?php
namespace Sam\BoobankBundle\Services;

/**
 * command runner
 *
 * @author samir.keriou
 *
 */
class Shell {

    /**
     * run as
     * @var string
     */
    public static $user = false;


    /**
     * Run command
     *
     * @param string $cmd bash command
     * @return array return, output
     */
    public function run($cmd) {
        $return = exec($cmd, $output);
        return array($return, $output);
    }


    /**
     * Is command available on system
     *
     * @param string $cmd
     * @return boolean
     */
    public function isCommandAvailable($cmd) {
        $cmd="type $cmd";
        list($return, $output) = self::run($cmd);
        if(preg_match("#not found#", $return)) {
            return false;
        }
        return true;
    }







    /**
     * Donne l'utilisateur
     * @return Ambigous <boolean, string>
     */
    public static function whoami() {
        if (! self::$user) {
            self::$user = self::run("whoami");
        }
        return self::$user;
    }
}