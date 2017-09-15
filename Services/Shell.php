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
        //hiding output with redirect to temp file
        //$cmd .= " > /tmp/output.boobank";
        ob_start();
        $return = exec($cmd, $output, $returnCode);
        ob_end_clean();
        return array($return, $output, $returnCode);
    }


    /**
     * Is command available on system
     *
     * @param string $cmd
     * @return boolean
     */
    public function isCommandAvailable($cmd) {
        $cmd="type $cmd";
        list($return, $output, $code) = self::run($cmd);
        if($code === 1) {
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
            list($user, $op) = self::run("whoami");
            self::$user = $user;

        }
        return self::$user;
    }
}