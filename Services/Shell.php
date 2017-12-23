<?php
namespace SamKer\BoobankBundle\Services;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

/**
 * command runner
 *
 * @author samir.keriou
 *
 */
class Shell
{
    /**
     * Log dir
     */
    const LOG_DIR = "/tmp/boobank";

    private $output = self::LOG_DIR . "/output.log";
    private $error = self::LOG_DIR . "/error.log";
    private $history = self::LOG_DIR . "/history.log";

    /**
     * @var File
     */
    private $fileOutput;
    /**
     * @var File
     */
    private $fileError;
    private $fileHistory;

    private $opCommand = "";
    private $cmd = "";
    private $lastCommand = "";

    /**
     * @var Filesystem
     */
    private $fs;
    /**
     * run as
     * @var string
     */
    public static $user = false;

    public function __construct()
    {
        $this->fs = new Filesystem();

        if (!$this->fs->exists(self::LOG_DIR)) {
            $this->fs->mkdir(self::LOG_DIR);
        }
        if (!$this->fs->exists($this->output)) {
            $this->fs->touch($this->output);
            $this->fs->chmod($this->output, 0777);
        }
        if (!$this->fs->exists($this->error)) {
            $this->fs->touch($this->error);
            $this->fs->chmod($this->error, 0777);
        }
        if (!$this->fs->exists($this->history)) {
            $this->fs->touch($this->history);
            $this->fs->chmod($this->history, 0777);
        }
        $this->fileOutput = new File($this->output);
        $this->fileError = new File($this->error);
        $this->fileHistory = new File($this->history);


        $this->opCommand .= " 1> " . $this->output;
        $this->opCommand .= " 2> " . $this->error;


    }

    /**
     * add log to command
     */
    private function addOPCommand()
    {
        $this->cmd .= $this->opCommand;
    }

    /**
     * Run command
     * use a command with 1> /tmp/boobank/output.log 2> /tmp/boobank/error.log
     *
     * @param string $cmd bash command
     * @return array return, output
     */
    public function run($cmd)
    {
        //hiding output with redirect to temp file
        $this->cmd = $cmd;
        $this->addOPCommand();

        ob_start();
        exec($this->cmd, $output, $returnCode);
        ob_end_clean();
        /*if($returnCode !== 0) {
            throw new \Exception("command failed: " . trim($this->fileError->openFile('r')->fgets()));
        }*/
        $output = trim(file_get_contents($this->output));
        $error = str_replace("\n", ",", trim(file_get_contents($this->error)));

        $return = [
            "cmd" => $this->cmd,
            "code" => $returnCode,
            "output" => $output,//trim($this->fileOutput->openFile('r')->fgets()),
            "error" => $error//trim($this->fileError->openFile('r')->fgets()),
        ];
        //history cmd
        exec("echo '" . $this->cmd . "' >> " . $this->history);
        $this->lastCommand = $this->cmd;
        $this->cmd = "";

        return $return;
    }


    /**
     * Is command available on system
     *
     * @param string $cmd
     * @return boolean
     */
    public function isCommandAvailable($cmd)
    {
        try {
            $cmd = "type $cmd";
            $result = self::run($cmd);
            if ($result["code"] !== 0) {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * get path command
     * @param $cmd
     * @return bin path or false
     */
    public function getPathCommand($cmd)
    {
        $cmd = "which $cmd";
        $result = self::run($cmd);
        if ($result["code"] === 0) {
            return $result["output"];
        }
        return false;

    }


    /**
     * Donne l'utilisateur
     * @return Ambigous <boolean, string>
     */
    public function whoami()
    {
        if (!self::$user) {
            $result = $this->run("whoami");
            if ($result["output"] !== "") {
                self::$user = $result["output"];
            } else {
                throw new \Exception("command failed:" . $result['error']);
            }
        }
        return self::$user;
    }


    /**
     * Get home dir
     * @return mixed
     * @throws \Exception
     */
    public function home()
    {
        $result = $this->run("echo \$HOME");
        if ($result["output"] !== "") {
            return $result["output"];
        } else {
            throw new \Exception("command failed:" . $result['error']);
        }
    }

    /**
     * @return string path/to/output.log
     */
    public function getOutputFile() {
        return $this->fileOutput;
    }
}