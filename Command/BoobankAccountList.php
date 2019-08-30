<?php

namespace SamKer\BoobankPHP\Command;

use SamKer\BoobankBundle\Services\Boobank;
use SamKer\BoobankPHP\Services\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BoobankAccountList extends Command
{

    protected static $defaultName = "bbk:account:list";

    protected function configure()
    {
        $this
            ->setDescription('List account')
            ->addArgument('name', InputArgument::REQUIRED, 'backend name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $backend = $input->getArgument('name');

        $account = $this->boobank->listAccount($backend);
        dump($account);
    }

}
