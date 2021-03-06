<?php

namespace SamKer\BoobankBundle\Repository;
use SamKer\BoobankBundle\Entity\Accounts;
use SamKer\BoobankBundle\Entity\Backends;

/**
 * AccountsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AccountsRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * get AccountEntity
     * @param $name
     * @return false|Accounts
     */
    public function findByName($name, $backend)
    {
        $r = $this->findBy(["account" => $name, "backend" => $backend]);
        if($r && count($r) === 1) {
            return $r[0];
        } else {
            return false;
        }
    }


    /**
     * add account
     * @param Backends $entityBackend
     * @param string $id account
     * @param string $label
     * @param float $amount
     * @return Accounts
     * @throws \Doctrine\ORM\OptimisticLockException
     */
public function addAccount($entityBackend, $id, $label, $amount) {
        $em = $this->getEntityManager();
        if($this->findByName($id, $entityBackend->getId())){
           return false;
        }
        $account = new Accounts();
        $account->setAccount($id);
        $account->setAccountLabel($label);
        $account->setAmount($amount);
        $account->setLastModif(new \DateTime());
        $account->setBackend($entityBackend);
        $em->persist($account);
        $em->flush();
    return $account;
}

    /**
     * Remove all account linked to a backend
     * @param $backendId
     */
public function removeAccountByBackend($backendId) {
    $qb = $this->createQueryBuilder("a")
        ->delete()
        ->where("a.backend = :backendid")
        ->setParameter("backendid", $backendId);
    $qb->getQuery()->execute();
}


    /**
     * @param string $backend
     * @param string $account
     * @param array $rules
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
public function saveWatchRules($backend, $account, $rules) {


    $accountEntity = $this->findByName($account, $backend);
    if(!$accountEntity) {
        throw new \Exception("account $account not exist in database");
    }
    $accountEntity->setSurvey($rules['survey']);
    $accountEntity->setAction($rules['action']);
    $em = $this->getEntityManager();
    $em->persist($accountEntity);
    $em->flush();
}
}
