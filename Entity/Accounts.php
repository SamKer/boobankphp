<?php

namespace SamKer\BoobankBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Accounts
 *
 * @ORM\Table(name="boobank_accounts")
 * @ORM\Entity(repositoryClass="SamKer\BoobankBundle\Repository\AccountsRepository")
 */
class Accounts
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="account", type="string", length=100, unique=true)
     */
    private $account;

    /**
     * @var string
     *
     * @ORM\Column(name="account_label", type="string", length=255)
     */
    private $accountLabel;


    /**
     * @var Backends
     *
     * @ORM\ManyToOne(targetEntity="SamKer\BoobankBundle\Entity\Backends")
     * @ORM\JoinColumn(nullable=false)
     */
    private $backend;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_modification", type="datetime")
     */
    private $lastModif;

    /**
     * @var FloatType
     *
     * @ORM\Column(name="amount", type="float", scale=2)
     */
    private $amount;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set account
     *
     * @param string $account
     *
     * @return Accounts
     */
    public function setAccount($account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return string
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set accountLabel
     *
     * @param string $accountLabel
     *
     * @return Accounts
     */
    public function setAccountLabel($accountLabel)
    {
        $this->accountLabel = $accountLabel;

        return $this;
    }

    /**
     * Get accountLabel
     *
     * @return string
     */
    public function getAccountLabel()
    {
        return $this->accountLabel;
    }


    /**
     * get backend
     * @return Backends
     */
    public function getBackend() {
        return $this->backend;
    }

    /**
     * set backend
     * @param Backends $backend
     */
    public function setBackend(Backends $backend) {
        $this->backend = $backend;
    }

    /**
     * Set Date of last modif
     *
     * @param \DateTime $date
     *
     * @return Backends
     */
    public function setLastModif($date)
    {
        $this->lastModif = $date;

        return $this;
    }

    /**
     * Get Date last modif
     *
     * @return \DateTime
     */
    public function getLastModif()
    {
        return $this->lastModif;
    }
    /**
     * Set Amount
     *
     * @param FloatType $amount
     *
     * @return Backends
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return FloatType
     */
    public function getAmount()
    {
        return $this->amount;
    }
}

