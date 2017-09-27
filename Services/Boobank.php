<?php
namespace Sam\BoobankBundle\Services;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class d'extraction de données banquaire via l'utilitaire boobank, composant
 * de weboob
 *
 * @author Samir Keriou
 * @since 01/02/2014
 *
 */
class Boobank
{


    /**
     * identifiant banque postale
     *
     * @var string
     */
    const BANK_BP = "bp";

    /**
     * identifiant banque cic
     *
     * @var string
     */
    const BANK_CIC = "cic";

    /**
     * identifiant paypal
     *
     * @var string
     */
    const BANK_PAYPAL = "paypal";
    /**
     * commande pour lister les connexion
     *
     * @var string
     */
    const CMD_LIST_BACKENDS = "#PATH_CMD#/weboob-config list";
    /**
     * commande pour obtenir l'historique d'un compte particulier
     *
     * @var string
     */
    const CMD_LIST_COMPTE = "#PATH_CMD#/boobank history #IDCOMPTE#@#IDBACKEND#";
    /**
     * commande pour exporter l'historique d'un compte en particulier
     *
     * @var cmd
     */
    const CMD_EXPORT_HISTORY_COMPTE = "#PATH_CMD#/boobank history #IDCOMPTE#@#IDBACKEND# -f csv";

    /**
     * commande pour lister les comptes avec leur montant courant
     * liste de tous les backends
     *
     * @var cmd
     */
    const CMD_EXPORT_LIST_COMPTE = "#PATH_CMD# list -f csv --select id,label,iban,balance";


    private $availableModules = [
        self::BANK_BP,
        self::BANK_CIC
    ];


    /**
     * Chemin du fichier backends servant de connexion à boobank dans repertoire
     * home du www-data
     *
     * @var string path file backends
     */
    private $sBackendsPath;

    /**
     * path to export csv file
     * @var string
     */
    //private $exportPath;

    /**
     * Liste des backends
     *
     * @var array
     */
    private $aBackEnds = false;

    /**
     * Exemple model de backends
     *
     * @var array
     */
    private $aBackEndModel = [
        "_backend" => "bankID",
        "website" => "par",
        "login" => "log",
        "password" => "pass"
    ];

    /**
     * Liste de clé pour les exports de données
     *
     * @var array
     */
    private $aSerial = [
        'date',
        'raw',
        'amount'
    ];

    /**
     * Instance of Shell
     *
     * @var Shell
     */
    private $shell = false;

    /**
     * Chemin du bin user
     *
     * @var string path to bin weboob
     */
    private $cmdPathWeboob = false;

    /**
     * @var bool|string path to bin weboob-config
     */
    private $cmdPathBoobank = false;

    /**
     * @var bool|string path to bin boobank
     */
    private $cmdPathWeboobConfig = false;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * crée le dossier local boobank pour d'éventuels exports
     *
     * @param Shell $shell
     * @param array $params
     */
    public function __construct(Shell $shell, $params = ["bin_path" => "/usr/bin"])
    {
        // dependances
        $this->shell = $shell;
        $this->fs = new Filesystem();

        //define pathcommands-----------
        $this->cmdPathWeboob = $this->shell->getPathCommand("weboob");
        if (!$this->cmdPathWeboob) {
            $this->cmdPathWeboob = $params["bin_path"] . "/weboob";
            if (!$this->fs->exists($this->cmdPathWeboob)) {
                throw new \Exception("class php Boobank needs weboob command");
            }
        }
        $this->cmdPathWeboobConfig = $this->shell->getPathCommand("weboob-config");
        if (!$this->cmdPathWeboobConfig) {
            $this->cmdPathWeboobConfig = $params["bin_path"] . "/weboob-config";
            if (!$this->fs->exists($this->cmdPathWeboobConfig)) {
                throw new \Exception("class php Boobank needs weboob-config command");
            }
        }
        $this->cmdPathBoobank = $this->shell->getPathCommand("boobank");
        if (!$this->cmdPathBoobank) {
            $this->cmdPathBoobank = $params["bin_path"] . "/boobank";
            if (!$this->fs->exists($this->cmdPathBoobank)) {
                throw new \Exception("class php Boobank needs boobank command");
            }
        }
        //----------

        //setting home
        $home = $this->shell->home();
        if (!$this->fs->exists($home)) {
            throw new \Exception("no home dir at" . $home);
        }
        if (!$this->fs->exists($home . "/.config")) {
            $this->fs->mkdir($home . "/.config");
        }
        if (!$this->fs->exists($home . "/.config/weboob")) {
            $this->fs->mkdir($home . "/.config/weboob");
        }

        //backend created by weboob at backend
        $this->sBackendsPath = $this->shell->home() . "/.config/weboob/backends";
        if (!$this->fs->exists($this->sBackendsPath)) {
            $this->fs->touch($this->sBackendsPath);
        }

        $this->getBackEnds();
        /*
                //export file
                $this->exportPath = $this->shell->home() . "/.config/weboob/export.csv";
                if (!$this->fs->exists($this->exportPath)) {
                    $this->fs->touch($this->exportPath);
                }
        */
    }


    /**
     * Definit une clé serialisé par 3champs
     *
     * @param string $p1
     * @param string $p2
     * @param string $p3
     */
    public function setSerialKeys($p1, $p2, $p3)
    {
        $this->aSerial = array(
            $p1,
            $p2,
            $p3
        );
    }

    /**
     * Ajoute une clé pour le serial
     *
     * @param string $p
     */
    public function addToSerialKey($p)
    {
        $this->aSerial[] = $p;
    }

    /**
     * List account for specific backend
     * @param string $backend
     * @return array
     */
    public function listAccount($backend)
    {
        if (!isset($this->aBackEnds[$backend])) {
            throw new \Exception("backend " . $backend . " not exist");
        }
        $result = $this->exportListeComptes();

        if ($result['code'] !== 0) {
            throw new \Exception("list account failed: " . $result['error']);
        }

        //filter for backend
        $csv = $this->parseCSV($this->shell->getOutputFile());
        $list = array_filter($csv, function ($r) use($backend) {
            return (substr($r["id"], -strlen($backend)) === $backend);
        });
        //replace id
        $list = array_map(function($r) use($backend) {
            $r['id'] = substr($r['id'], 0, -(strlen($backend)+1));
            return $r;
        }, $list);


        return $list;

    }

    /**
     * Export dans un fichier csv la liste des comptes d'un backend
     *
     * @param string $sIdBackEnd
     */
    private function exportListeComptes()
    {
        $command = preg_replace(
            [
                "#\#PATH_CMD\##"
            ],
            [
                $this->cmdPathBoobank
            ],
            self::CMD_EXPORT_LIST_COMPTE);

        return $this->shell->run($command);
    }


    /**
     * Renvoi les backends disponibles
     *
     * @return array
     */
    public function getBackEnds()
    {
        if ($this->aBackEnds == false) {
            $this->aBackEnds = parse_ini_file($this->sBackendsPath, true);
        }
        return $this->aBackEnds;
    }

    /**
     * Ajoute un backend de connexion à boobank
     *
     * @param string $sIDBackEnd
     *            l'identifiant de connexion boobank
     * @param string $sIdBank
     *            identifiant de la banque
     * @param string|integer $sLogin
     *            login
     * @param string|integer $sPassword
     *            password
     * @return void
     */
    public function addBackend($sIdBackEnd, $sIdBank, $sLogin, $sPassword)
    {

        //test if backend already exist
        if (isset($this->aBackEnds[$sIdBackEnd])) {
            throw new \Exception("backend " . $sIdBackEnd . " already exist");
        }
        //test if module exist
        if (!in_array($sIdBank, $this->availableModules)) {
            throw new \Exception("module " . $sIdBank . " doesn't exist");
        }

        //test login
        if (!$sLogin) {
            throw new \Exception("a login is expected, false given");
        }
        //test password
        if (!$sPassword) {
            throw new \Exception("password expeced, false given");
        }

        $this->aBackEnds[$sIdBackEnd] = $this->aBackEndModel;
        $this->aBackEnds[$sIdBackEnd]['_backend'] = $sIdBank;
        $this->aBackEnds[$sIdBackEnd]['login'] = $sLogin;
        $this->aBackEnds[$sIdBackEnd]['password'] = $sPassword;
        $this->saveBackends();
    }

    /**
     * remove backend
     * @param string $backend
     * @throws \Exception
     */
    public function removeBackend($backend)
    {
        //test if backend already exist
        if (!isset($this->aBackEnds[$backend])) {
            throw new \Exception("backend " . $backend . " doesn't exist or already removed");
        }
        unset($this->aBackEnds[$backend]);
        $this->saveBackends();
    }

    /**
     * rewrite backend file config with current values
     * return void
     */
    private function saveBackends()
    {
        $sBackEnds = "";
        foreach ($this->getBackEnds() as $key => $aBackEnd) {
            $sBackEnds .= "[" . $key . "]\n";
            foreach ($aBackEnd as $key2 => $value) {
                $sBackEnds .= $key2 . "=" . $value . "\n";
            }
            $sBackEnds .= "\n";
        }
        file_put_contents($this->sBackendsPath, $sBackEnds);
    }

    /**
     * Get available module ( bank list module boobank)
     * @return array
     */
    public function getAvailableModules()
    {
        return $this->availableModules;
    }


    /**
     * Donne l'historique d'un compte en particulier
     *
     * @param string $sIdCompte
     * @param string $sIdBackEnd
     * @return array
     */
    public function getHistory($sIdCompte, $sIdBackEnd, $fromDate = false)
    {
        die('todo hist');
        $sIdBackEnd = strtoupper($sIdBackEnd);
        $aFile = $this->exportCompte($sIdCompte, $sIdBackEnd, $fromDate);
        $aHistory = array();
        $sHead = array_shift($aFile);
        $aHead = explode(";", $sHead);
        foreach ($aFile as $i => $ligne) {
            $aLigne = explode(";", $ligne);
            $aHistory[$i] = array();
            for ($j = 0; $j < count($aHead); $j++) {
                $aHead[$j] = preg_replace("#[\\r\\n]#", "", $aHead[$j]);
                $aLigne[$j] = preg_replace("#[\\r\\n]#", "", $aLigne[$j]);
                $aHistory[$i][$aHead[$j]] = $aLigne[$j];
            }
            // serial key for this raw
            $serial = "#";
            foreach ($this->aSerial as $k) {
                $serial .= $aHistory[$i][$k] . "#";
            }
            $aHistory[$i]['serial'] = $serial;
        }
        return $aHistory;
    }

    /**
     * Export dans un fichier csv l'historique d'un compte
     *
     * @param string $sIdCompte
     * @param string $sIdBackEnd
     */
    private function exportCompte($sIdCompte, $sIdBackEnd, $fromDate = false)
    {
        $sIdBackEnd = strtoupper($sIdBackEnd);
        $command = preg_replace(array(
            "#\\#IDCOMPTE\\##",
            "#\\#IDBACKEND\\##",
            "#\\#PATH_CMD\\##"
        ), array(
            $sIdCompte,
            $sIdBackEnd,
            $this->cmdPath
        ), self::CMD_EXPORT_HISTORY_COMPTE);
        if ($fromDate) {
            $command .= " " . $fromDate;
        }
        return $this->cmd($command, true);
    }

    /**
     * Get backend parameters
     * @param string $backend
     * @return array|false
     * @throws \Exception
     */
    public function getBackend($backend)
    {
        $this->getBackEnds();
        if (!isset($this->aBackEnds[$backend])) {
            throw new \Exception("backend $backend not found");
        }
        return $this->aBackEnds[$backend];
    }

    /**
     * Give the current amount
     *
     * @param string $sIdCompte
     *            number bank cp
     * @param string $sIdBackEnd
     *            backend name
     * @return float $amount
     */
    public function getCurrentAmount($sIdCompte, $sIdBackEnd)
    {
        $sIdBackEnd = strtoupper($sIdBackEnd);
        $aFile = $this->exportListeComptes();
        if (count($aFile) == 0) {
            return false;
        }
        $aList = array();
        $sHead = array_shift($aFile);
        $aHead = explode(";", $sHead);
        foreach ($aFile as $i => $ligne) {
            $aLigne = explode(";", $ligne);
            $aList[$i] = array();
            for ($j = 0; $j < count($aHead); $j++) {
                $aHead[$j] = preg_replace("#[\\r\\n]#", "", $aHead[$j]);
                $aLigne[$j] = preg_replace("#[\\r\\n]#", "", $aLigne[$j]);
                $aList[$i][$aHead[$j]] = $aLigne[$j];
            }
        }
        $result = array();
        foreach ($aList as $raw) {
            if ($raw['id'] == $sIdCompte . "@" . $sIdBackEnd) {
                return $raw['balance'];
            }
        }
    }

    private function exportConnexions()
    {
        $command = preg_replace(array(
            "#\\#PATH_CMD\\##"
        ), array(
            $this->cmdPath
        ), self::CMD_LIST_BACKENDS);
        return $this->cmd($command, true);
    }


    /**
     * parse csv file to array
     * @param string $file
     */
    public function parseCSV($file)
    {
        $resource = (new File($this->shell->getOutputFile()))->openFile();
        $a = [];
        $headers = explode(";", $resource->fgetcsv()[0]);

        while ($row = $resource->fgetcsv()) {
            $r = explode(";", $row[0]);
            if ($r[0] != "") {
                $a[] = array_combine($headers, $r);
            }
        }
        return $a;
    }
}

//-------------------TESTS-------------------------//
// $bb = new BooBank();
// $a = $bb->listConnexions();
// print_r($a);
// $bb->addConnexion("test", BooBank::BANK_BP, "1530988630", "729729");
// $a = $bb->getHistory("5452663N020", "test");
// print_r($a);



