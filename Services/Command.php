<?php


namespace SamKer\BoobankPHP\Services;


abstract class Command extends \Symfony\Component\Console\Command\Command
{
    protected $boobank;

    /**
     * Command constructor.
     * @param Boobank $config
     */
    public function __construct(Boobank $boobank)
    {
        $this->boobank = $boobank;
        parent::__construct();
    }

}