<?php
namespace Sam\BoobankBundle\Services;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Class d'extraction de données banquaire via l'utilitaire boobank, composant
 * de weboob
 *
 * @author Samir Keriou
 * @since 01/02/2014
 * @version 1
 *
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
    const CMD_EXPORT_LIST_COMPTE = "#PATH_CMD# list -f csv --select label,iban,balance";
    /**
     * Chemin du fichier backends servant de connexion à boobank dans repertoire
     * home du www-data
     *
     * @var string path file backends
     */
    private $sBackendsPath = false;
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
    private $aBackEndModel = array(
        "backendID" => array(
            "_backend" => "bankID",
            "website" => "par",
            "login" => "log",
            "password" => "pass"
        )
    );
    /**
     * Liste de clé pour les exports de données
     *
     * @var array
     */
    private $aSerial = array(
        'date',
        'raw',
        'amount'
    );
    /**
     * Instance of Shell
     *
     * @var Shell
     */
    private $shell = false;

    /**
     * Chemin du bin user
     *
     * @var string path to bin
     */
    private $cmdPathWeboob = false;
    private $cmdPathBoobank = false;
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
    public function __construct(Shell $shell, $params = ["bin_path"=>"/usr/bin"])
    {
        // dependances
        $this->shell = $shell;
        $this->fs = new Filesystem();

        //define pathcommands-----------
        $this->cmdPathWeboob = $this->shell->getPathCommand("weboob");
        if(!$this->cmdPathWeboob) {
            $this->cmdPathWeboob = $params["bin_path"] . "/weboob";
            if (!$this->fs->exists($this->cmdPathWeboob)) {
                throw new \Exception("class php Boobank needs weboob command");
            }
        }
        $this->cmdPathWeboobConfig = $this->shell->getPathCommand("weboob-config");
        if(!$this->cmdPathWeboobConfig) {
            $this->cmdPathWeboobConfig = $params["bin_path"] . "/weboob-config";
            if (!$this->fs->exists($this->cmdPathWeboobConfig)) {
                throw new \Exception("class php Boobank needs weboob-config command");
            }
        }
        $this->cmdPathBoobank = $this->shell->getPathCommand("boobank");
        if(!$this->cmdPathBoobank) {
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
     * Liste les comptes
     */
    public function listComptes($sIdBackEnd = false)
    {
        $result = $this->exportListeComptes();
        if($result['code'] !== 0) {
            throw new \Exception("list account failed: " . $result['error']);
        }
        if (!is_array($aFile)) {
            if (preg_match("#Error#", $aFile)) {
                throw new \Exception($aFile);
            }
        }
        if (count($aFile) == 0) {
            return false;
        }
        $aList = array();
        $aTri = array();
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
            $d = explode("@", $aList[$i]['id']);
            $aList[$i]['backend'] = $d[1];
            $aList[$i]['compte_id'] = $d[0];
            if ($sIdBackEnd && $aList[$i]['backend'] == $sIdBackEnd) {
                $aTri[$aList[$i]['id']] = $aList[$i];
            } else {
                $aTri[$i] = $aList[$i];
            }
        }
        return $aTri;
    }

    /**
     * Export dans un fichier csv la liste des comptes d'un backend
     *
     * @param string $sIdBackEnd
     */
    private function exportListeComptes()
    {
        $command = str_replace("#PATH_CMD#", $this->cmdPathBoobank, self::CMD_EXPORT_LIST_COMPTE);


        return $this->shell->run($command);
    }



    /**
     * Donne les backends déjà défini
     *
     * @return array
     */
    public function getConnexions()
    {
        return $this->getBackEnds();
    }

    /**
     * Renvoi les backends disponibles
     *
     * @return array
     */
    private function getBackEnds()
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
     */
    public function addConnexion($sIdBackEnd, $sIdBank, $sLogin, $sPassword)
    {
        $sIdBackEnd = strtoupper($sIdBackEnd);
        $sIdBank = strtolower($sIdBank);
        $aBackEnds = $this->getBackEnds();
        if (!is_array($aBackEnds)) {
            $aBackEnds = array();
        }
        if (!array_key_exists($sIdBackEnd, $aBackEnds)) {
            $aBackend = $this->aBackEndModel;
            $aBackend['_module'] = $sIdBank;
            $aBackend['login'] = $sLogin;
            $aBackend['password'] = $sPassword;
            $aBackEnds[$sIdBackEnd] = $aBackend;
            $this->setBackEnds($aBackEnds);
            return true;
        } else {
            return $sIdBackEnd . " already exist as connexion";
        }
    }

    /**
     * Enregistre le backend dans on état actuel
     *
     * @param array $aBackEnds
     * @return void
     */
    private function setBackEnds($aBackEnds)
    {
        $sBackEnds = "";
        foreach ($aBackEnds as $key => $aBackEnd) {
            $sBackEnds .= "[" . $key . "]\n";
            foreach ($aBackEnd as $key2 => $value) {
                $sBackEnds .= $key2 . "=" . $value . "\n";
            }
            $sBackEnds .= "\n";
        }
        file_put_contents($this->sBackendsPath, $sBackEnds);
    }

    /**
     * Enlève une connexion
     *
     * @param string $sIDBackEnd
     */
    public function removeConnexion($sIdBackEnd)
    {
        $sIdBackEnd = strtoupper($sIdBackEnd);
        $aBackEnds = $this->getBackEnds();
        if (in_array($sIdBackEnd, $aBackEnds)) {
            unset($aBackEnds[$sIdBackEnd]);
            $this->setBackEnds($aBackEnds);
            return true;
        } else {
            return $sIdBackEnd . " n'existe pas comme backends";
        }
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
    public function getConnexion($backend)
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
}

//-------------------TESTS-------------------------//
// $bb = new BooBank();
// $a = $bb->listConnexions();
// print_r($a);
// $bb->addConnexion("test", BooBank::BANK_BP, "1530988630", "729729");
// $a = $bb->getHistory("5452663N020", "test");
// print_r($a);



