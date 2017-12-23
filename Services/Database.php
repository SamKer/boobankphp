<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 22/12/2017
 * Time: 21:06
 */

namespace SamKer\BoobankBundle\Services;


use Doctrine\ORM\EntityManager;
use SamKer\BoobankBundle\Entity\Backends;
use SamKer\BoobankBundle\Repository\BackendsRepository;
use Symfony\Component\DependencyInjection\Container;

class Database
{
    /**
     * @var Container $container
     */
    private $container;

    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    /**
     * Database constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->entityManager = $container->get('doctrine.orm.default_entity_manager');
    }

    public function addBackend($backend, $module, $login, $password, $listAccounts) {
        $repoBackends = $this->entityManager->getRepository("SamKerBoobankBundle:Backends");
        $entityBackend = $repoBackends->addBackend($backend, $module, $login, $password);

        $repoAccounts = $this->entityManager->getRepository("SamKerBoobankBundle:Accounts");
        foreach ($listAccounts as $account) {
            $repoAccounts->addAccount($entityBackend, $account['id'], $account['label'], $account['balance']);
        }

    }

    public function removeBackend($backend) {
        $repoBackends = $this->entityManager->getRepository("SamKerBoobankBundle:Backends");
        $repoAccounts = $this->entityManager->getRepository("SamKerBoobankBundle:Accounts");


        //delete account at first
        $entityBackend = $repoBackends->findByName($backend);
        if($entityBackend) {
            $repoAccounts->removeAccountByBackend($entityBackend->getId());
        }

        //delete backend
        $repoBackends->removeBackend($backend);

    }


    public function addTransaction($backend, $account, $row) {
        $repoBackends = $this->entityManager->getRepository("SamKerBoobankBundle:Backends");
        $entityBackend = $repoBackends->findByName($backend);
        if(!$entityBackend) {
            throw new \Exception("backend $backend not exist in database");
        }
        $repoAccount = $this->entityManager->getRepository("SamKerBoobankBundle:Accounts");
        $entityAccount = $repoAccount->findByName($account, $entityBackend->getId());
        if(!$entityAccount) {
            throw new \Exception("account  $account not exist in database");
        }

        $repoStatements = $this->entityManager->getRepository("SamKerBoobankBundle:Statements");
        $date = explode("-", $row['date']);
        $date = (new \DateTime())->setDate($date[0], $date[1], $date[2]);
        return $repoStatements->addState($entityBackend, $entityAccount, $row['label'], $date, $row['amount'], $row['hash']);

    }

}