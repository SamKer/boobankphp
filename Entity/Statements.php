<?php

namespace SamKer\BoobankBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Statements
 *
 * @ORM\Table(name="boobank_statements")
 * @ORM\Entity(repositoryClass="SamKer\BoobankBundle\Repository\StatementsRepository")
 */
class Statements
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
     * @var Backends
     *
     * @ORM\ManyToOne(targetEntity="SamKer\BoobankBundle\Entity\Backends")
     * @ORM\JoinColumn(nullable=false)
     */
    private $backend;

    /**
     * @var Accounts
     *
     * @ORM\ManyToOne(targetEntity="SamKer\BoobankBundle\Entity\Accounts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $account;

    /**
     * @var string
     *
     * @ORM\Column(name="hash", type="string", length=64, unique=true)
     */
    private $hash;

    /**
     * @var \DateTime
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="state_label", type="string", length=255)
     */
    private $stateLabel;

    /**
     * @var float
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
     * Set backend
     *
     * @param Backends $backend
     *
     * @return Statements
     */
    public function setBackend($backend)
    {
        $this->backend = $backend;

        return $this;
    }

    /**
     * Get backend
     *
     * @return Backends
     */
    public function getBackend()
    {
        return $this->backend;
    }
    /**
     * Set Account
     *
     * @param Accounts $account
     *
     * @return Statements
     */
    public function setAccount($account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get Account
     *
     * @return Accounts
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set Hash
     *
     * @param string $hash
     *
     * @return Statements
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get Hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set Date
     *
     * @param \DateTime $date
     *
     * @return Statements
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get Date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set Label
     *
     * @param string $label
     *
     * @return Statements
     */
    public function setLabel($label)
    {
        $this->stateLabel = $label;

        return $this;
    }

    /**
     * Get Label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->stateLabel;
    }
    /**
     * Set Amount
     *
     * @param float $amount
     *
     * @return Statements
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get Amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }



}

