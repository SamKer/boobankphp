<?php

namespace SamKer\BoobankPHP\Services;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints\DateTime;

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
    # const CMD_LIST_BACKENDS = "#PATH_CMD# list";
    /**
     * commande pour obtenir l'historique d'un compte particulier
     *
     * @var string
     */
    #const CMD_LIST_COMPTE = "#PATH_CMD# -b #IDBACKEND# history #IDCOMPTE#@#IDBACKEND#";
    /**
     * commande pour exporter l'historique d'un compte en particulier
     *
     * @var cmd
     */
    const CMD_HISTORY = "#PATH_CMD# -b #IDBACKEND# history #IDCOMPTE#@#IDBACKEND# -f csv #FILTERS# #DATE#";

    /**
     * commande pour lister les comptes avec leur montant courant
     * liste de tous les backends
     *
     * @var cmd
     */
    const CMD_LIST = "#PATH_CMD# -b #IDBACKEND# list -f csv  #FILTERS#";


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
     * Path to rules for awtch command
     * @var string path to dir
     */
    private $watchRulesDir;

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
        "password" => "pass",
        "mail" => "mail"
    ];

    private $watchModel = [
        "survey" => ["history" => false, "list" => false],
        "action" => ["database" => false, "mail" => false],
        "lastchanged" => ["list" => false, "history" => true]

    ];


    /**
     * Instance of Shell
     *
     * @var Shell
     */
    private $shell;

    /**
     * Database service boobank
     * @var Database
     */
    private $database = false;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var string mail Admin
     */
    private $mailAdmin;

    /**
     * @var \Twig_Environment
     */
    private $twig;

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
     * optionnal params
     * @var array
     */
    private $params = [
        "bin_path" => "/usr/bin",
        "database" => false,
        "dateInterval" => "P1D",
        "filters" => [
            "list" => ["id"],
            "history" => ["id"]
        ]
    ];


    /**
     * crée le dossier local boobank pour d'éventuels exports
     *
     * @param Shell $shell
     * @param Config $config
     * @param \Swift_Mailer $mailer
     * @params \Twig_Environment $twig
     */
    public function __construct(Shell $shell, Config $config, \Swift_Mailer $mailer, \Twig_Environment $twig)
    {
        // dependances
        $this->shell = $shell;

        $this->mailer = $mailer;
        $this->mailAdmin = $config->params['mail_admin'];
        $this->twig = $twig;

        $this->fs = new Filesystem();
        $this->params = $config->testConfig(true);

        //define pathcommands-----------

        $this->cmdPathWeboob = $this->params["binaries"]['weboob'];
        $this->cmdPathWeboobConfig = $this->params["binaries"]['weboob-config'];
        $this->cmdPathBoobank = $this->params["binaries"]['boobank'];

        //----------

        //backend created by weboob at backend
        $this->sBackendsPath = $this->params['backend_path'];

        //specific rules at
        $this->watchRulesDir = $this->params['watch_rules'];

        $this->getBackEnds();
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
        $result = $this->runList($backend);

        if ($result['code'] !== 0) {
            throw new \Exception("list account failed: " . $result['error']);
        }
        if ($result['error'] !== "") {
            throw new \Exception("list account failed: " . $result['error']);
        }
        //filter for backend
        $csv = $this->parseCSV();
        $list = $this->filter($csv, $backend);


        return $list;

    }

    /**
     * list account in csv
     *
     * @param string $backend
     * @return array
     */
    private function runList($backend)
    {
        $command = preg_replace(
            [
                "#\#PATH_CMD\##",
                "#\#IDBACKEND\##",
                "#\#FILTERS\##"
            ],
            [
                $this->cmdPathBoobank,
                $backend,
                $this->getFilters("list")
            ],
            self::CMD_LIST);

        return $this->shell->run($command);
    }


    /**
     * Renvoi les backends disponibles
     *
     * @return array
     */
    public function getBackEnds()
    {
        if ($this->aBackEnds === false) {
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
    public function addBackend($sIdBackEnd, $sIdBank, $sLogin, $sPassword, $mail)
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
        //save in file config, used by boobank cmd
        $this->saveBackends();

        if ($this->database === true) {
            //save in database, do at first a list account for populate database
            $listAccounts = $this->listAccount($sIdBackEnd);
            //add backend and accounts linked in database
            $this->database->addBackend($sIdBackEnd, $sIdBank, $sLogin, $sPassword, $listAccounts);
        }
    }

    /**
     * @param string $sIdBackEnd
     * @param string $sIdBank
     * @param string $sLogin
     * @param string $sPassword
     * @param string $mail
     */
    public function editBackend($sIdBackEnd, $sIdBank, $sLogin, $sPassword, $mail)
    {
        $this->aBackEnds[$sIdBackEnd] = $this->aBackEndModel;
        $this->aBackEnds[$sIdBackEnd]['_backend'] = $sIdBank;
        $this->aBackEnds[$sIdBackEnd]['login'] = $sLogin;
        $this->aBackEnds[$sIdBackEnd]['password'] = $sPassword;
        $this->aBackEnds[$sIdBackEnd]['mail'] = $mail;
        //save in file config, used by boobank cmd
        $this->saveBackends();
        if ($this->database) {
            //save in database, do at first a list account for populate database
            $listAccounts = $this->listAccount($sIdBackEnd);
            //add backend and accounts linked in database
            $this->database->updateBackend($sIdBackEnd, $sIdBank, $sLogin, $sPassword, $mail, $listAccounts);
        }
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
        $a = $this->aBackEnds;
        unset($a[$backend]);
        $this->aBackEnds = $a;
        //remove in config
        $this->saveBackends();
        if ($this->database) {
            //remove in database
            $this->database->removeBackend($backend);
        }
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
     * @param string $account
     * @param string $backend
     * @return array
     */
    public function getHistory($account, $backend, $date = false, $filters = false)
    {
        $result = $this->runHistory($account, $backend, $date, $filters);
        if ($result['code'] !== 0) {
            throw new \Exception("history failed");
        }
        if ($result['error'] !== "") {
            throw new \Exception("history failed: " . $result['error']);
        }
//        dump($result);die;
        $csv = $this->parseCSV();
        $list = $this->filter($csv, $backend, $account);

        return $list;
    }

    /**
     * Export dans un fichier csv l'historique d'un compte
     *
     * @param string $account
     * @param string $backend
     * @return csv
     */
    private function runHistory($account, $backend, $date = false, $select = false)
    {
        $command = preg_replace(array(
            "#\\#IDCOMPTE\\##",
            "#\\#IDBACKEND\\##",
            "#\\#PATH_CMD\\##",
            "#\\#FILTERS\\##",
            "#\\#DATE\\##"
        ), array(
            $account,
            $backend,
            $this->cmdPathBoobank,
            $this->getFilters("history", $select),
            $this->getDate($date)
        ), self::CMD_HISTORY);
//die($command);
        return $this->shell->run($command);
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
     *
     */
    public function parseCSV()
    {
        $a = [];
        $content = \file($this->shell->getOutputFile());

        $content = array_map(function ($r) {
            return str_replace("\r\n", "", trim($r));
        }, $content);


        if(count($content) === 0) {
            return $a;
        }
        $headers = explode(";", $content[0]);
        for ($i = 1; $i <= count($content) - 1; $i++) {
            $r = explode(";", $content[$i]);
            if ($r[0] != "") {
                $a[] = array_combine($headers, $r);
            }
        }

        return $a;
    }

    /**
     * filter csv
     * @param $csv
     * @param $backend
     * @param string $account account id , default to false
     * @return array
     */
    public function filter($csv, $backend, $account = false)
    {
        $list = array_filter($csv, function ($r) use ($backend) {
            return (substr($r["id"], -strlen($backend)) === $backend);
        });
        //replace id
        $list = array_map(function ($r) use ($backend, $account) {
            $r['id'] = substr($r['id'], 0, -(strlen($backend) + 1));
            if ($account && $r['id'] == "") {
                $r['id'] = $account;
            }
            $r['hash'] = hash("sha256", implode(";", $r));
            return $r;
        }, $list);
        return $list;
    }

    /**
     * get filter select
     * @param string $cmd
     * @param string $filter filter
     *
     * @return string $filter command option
     */
    private function getFilters($cmd, $filters = false)
    {
        if ($filters) {

            return $filters = "--select id," . $filters;
        }

        if (count($this->params["filters"][$cmd]) === 1) {
            $filters = "";
        } else {
            $filters = implode(",", $this->params["filters"][$cmd]);
            $filters = "--select " . $filters;
        }

        return $filters;
    }


    /**
     * Get filter by date
     * @param false|string|\DateTime
     * @return date $date defined by param dateinterval
     */
    private function getDate($date = false)
    {
        if ($date) {
            if (is_string($date)) {
                return $date;
            }
            if ($date instanceof \DateTime) {
                return $date->format("Y-m-d");
            }
        }
        return (new \DateTime())->sub(new \DateInterval($this->params['dateInterval']))->format("Y-m-d");
    }


    /**
     * Survey account
     *
     * @param string $backend
     * @param string $account
     * @param date $date
     * @return array report
     */
    public function watch($backend = false, $account = false, $date = false)
    {
        $result = [];

        foreach ($this->getBackEnds() as $backendId => $b) {
            if ($backend !== false && $backendId !== $backend) {
                continue;
            }
            $accounts = $this->getWatchRules($backendId);
//dump($accounts);die;
            if ($accounts && count($accounts) > 0) {
                foreach ($accounts as $accountid => $rules) {
                    if (!$account || $account === $accountid) {
                        //survey return all changes
                        $survey = $this->survey($backendId, $accountid, $rules['survey'], $rules['lastchanged'], $date);

                        //we pass report to action
                        $result[$backendId] = $this->action($backendId, $accountid, $rules['action'], $survey);
                        if (count($survey['history']) > 0) {
                            $rules['lastchanged']['history'] = $survey['history'][count($survey['history']) - 1]['hash'];
                        }
                        if (count($survey['list']) > 0) {
                            $rules['lastchanged']['list'] = $survey['list']['balance'];
                        }
                        $this->saveWatchRules($backendId, $accountid, $rules);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Get specific rules for watch command for specific account in backend
     * @param string $backend
     * @param string $account
     * @return array $rules
     */
    public function getWatchRules($backend, $account = false)
    {
        $pathRules = $this->watchRulesDir . "/" . $backend;
        if (!$this->fs->exists($pathRules)) {
            //create rules
            $this->saveWatchRules($backend, $account, $this->watchModel);
        }

        $watchrules = json_decode(file_get_contents($pathRules), true);
        if (!$account) {
            return $watchrules;
        }

        if (!isset($watchrules[$account])) {
            $watchrules[$account] = $this->watchModel;
            $this->saveWatchRules($backend, $account, $this->watchModel);
        }

        return $watchrules[$account];

    }

    /**
     * Save rule for watch command
     * @param $backend
     * @param $account
     * @param $rules
     */
    public function saveWatchRules($backend, $account = false, $rules = false)
    {
        $pathRules = $this->watchRulesDir . "/" . $backend;

        if (!$this->fs->exists($pathRules)) {
            file_put_contents($pathRules, json_encode([]));
        }
        if (!$account) {
            return;
        }
        $backendRules = json_decode(file_get_contents($pathRules), true);

        if (!isset($backendRules[$account])) {
            $backendRules[$account] = $this->watchModel;
        }

        if (!$rules) {
            $backendRules[$account] = $this->watchModel;
        } else {
            $backendRules[$account] = $rules;
        }

        file_put_contents($pathRules, json_encode($backendRules));
        if ($this->database && $account) {
            $this->database->saveWatchRules($backend, $account, $rules);
        }
    }

    /**
     * Run command specified in parameters for watch
     * @param string $backend
     * @param string $account
     * @param array $rules
     * @param array $lastChanged
     * @param string $date (Y-m-d)
     * @return array $listResult (history or account info)
     * @throws \Exception
     */
    private function survey($backend, $account, $rules, $lastChanged = ["history" => false, "list" => false], $date = false)
    {
        $result = ["list" => [], "history" => []];
        foreach ($rules as $rule => $v) {
            if ($v !== true) {
                continue;
            }
//        dump($backend);dump($account);dump($rule);die;
            switch ($rule) {
                case 'list':
                    $list = $this->listAccount($backend);
                    foreach ($list as $a) {
                        if ($a['id'] == $account) {
                            if ($a['balance'] != $lastChanged['list']) {
                                $result['list'] = $a;
                                break;
                            }
                        }
                    }
                    break;
                case 'history':
                    $list = $this->getHistory($account, $backend, $date);
                    $b = 0;
                    foreach ($list as $i => $item) {
                        if ($item['hash'] == $lastChanged['history']) {
                            $b = $i+1;
                            break;
                        }
                    }
                    $list = array_splice($list, $b);
                    $result['history'] = $list;
                    break;
                default:
                    throw new \Exception("rule $rule not expected here");
                    break;
            }
        }
        return $result;
    }

    /**
     * do action specified in parmaeters
     * @param string $backend
     * @param string $account
     * @param array $rule
     * @param array $survey
     * @return array [database => nb inserted, mail => nb mail send]
     * @throws \Exception
     */
    private function action($backend, $account, $rule, $survey)
    {
//        dump($backend);dump($account);
        $inserts = 0;
        $mails = 0;
        foreach ($rule as $action => $v) {
            if ($v !== true) {
                continue;
            }
            switch ($action) {
                case 'mail':
                    //mail list
                    if (count($survey['list']) > 0) {
                        $mails++;
                        $this->sendMail($backend, $account, $survey['list'], "list");
                    }
                    //mail history
                    if (count($survey['history']) > 0) {
                        $mails++;
                        $this->sendMail($backend, $account, $survey['history']);
                    }


                    break;
                case 'database':
                    //list

                    //history
                    //tri inversé
                    $survey['history'] = array_reverse($survey['history']);
                    foreach ($survey['history'] as $row) {
                        $i = $this->database->addTransaction($backend, $account, $row);
                        if ($i) {
                            $inserts++;
                        }
                    }
                    break;
                default:
                    throw new \Exception("action $action not expected");
                    break;
            }
        }
        return [
            "database" => $inserts,
            "mail" => $mails
        ];
    }

    public function sendMail($backend, $account, $rows, $model = "history")
    {
        $backendParams = $this->getBackend($backend);

        $message = (new \Swift_Message('[RYUKENTEAM WATCH] Survey Bank for you and found new transactions'))
            ->setFrom($this->mailAdmin)
            ->setTo($backendParams['mail']);
        if ($model === "history") {
            $message->setBody(
                $this->twig->render("SamKerBoobankBundle:Mail:history.html.twig", ["account" => $account, "list" => $rows]),
                'text/html'
            );
        } else {
            $rows['date'] = (new \DateTime())->format('Y-m-d');
            $message->setBody(
                $this->twig->render("SamKerBoobankBundle:Mail:list.html.twig", ["account" => $account, "list" => $rows]),
                'text/html'
            );
        }/*
             * If you also want to include a plaintext version of the message
            ->addPart(
                $this->renderView(
                    'emails/registration.txt.twig',
                    array('name' => $name)
                ),
                'text/plain'
            )
            */;
        $this->mailer->send($message);

    }

}