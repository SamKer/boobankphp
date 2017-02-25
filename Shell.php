<?php
namespace Boobank;

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
    public static function run($cmd) {
        $return = exec($cmd, $output);
        return array($return, $output);
    }


    /**
     * Is command available on system
     *
     * @param string $cmd
     * @return boolean
     */
    public static function isCommandAvailable($cmd) {
        $cmd="type $cmd";
        var_dump(self::run($cmd));
        die('test');
    }

    public static function isPackageInstalled($package) {
        die('test');
    }

    public static function installPaquet($paquet) {
        die('method not implemented yet');
        $cmd = "sudo apt-get install " . $paquet;
        return self::cmd($cmd);
    }

    /**
     * Install dependencies
     *
     * @param string|array $packages list package
     * @return boolean
     */
    public static function needs($packages) {
        if(is_string($packages)) {
            $packages = array($packages);
        }
        die('test');
        foreach ($packages as $package) {
            if (!self::isExistPackage($package)) {
                self::installPackage($package);
            }
        }

    }

    /**
     * Donne l'utilisateur
     * @return Ambigous <boolean, string>
     */
    public static function whoami() {
        if (! self::$user) {
            self::$user = self::cmd("whoami");
        }
        return self::$user;
    }
}