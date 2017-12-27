<?php

namespace SamKer\BoobankBundle\Entity;

use Doctrine\DBAL\Types\FloatType;
use Doctrine\ORM\Mapping as ORM;

/**
 * Backends
 *
 * @ORM\Table(name="boobank_backends")
 * @ORM\Entity(repositoryClass="SamKer\BoobankBundle\Repository\BackendsRepository")
 */
class Backends
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
     * @ORM\Column(name="name", type="string", length=20, unique=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="module", type="string", length=20)
     */
    private $module;

    /**
     * @var string
     *
     * @ORM\Column(name="login", type="string", length=50)
     */
    private $login;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=100)
     */
    private $password;


    /**
     * @var string
     *
     * @ORM\Column(name="mail", type="string", length=255)
     */
    private $mail;


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
     * Set name
     *
     * @param string $name
     *
     * @return Backends
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set module
     *
     * @param string $module
     *
     * @return Backends
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get Module
     *
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set login
     *
     * @param string $login
     *
     * @return Backends
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Get Login
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set Password
     *
     * @param string $password
     *
     * @return Backends
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get Password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
    /**
     * Set Mail
     *
     * @param string $mail
     *
     * @return Backends
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Get Mail
     *
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }








}

