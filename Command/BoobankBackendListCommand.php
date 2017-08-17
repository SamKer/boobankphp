<?php

namespace Sam\BoobankBundle\Command;

use Sam\BoobankBundle\Services\BooBank;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BoobankBackendListCommand extends ContainerAwareCommand
{
    /**
     * @var BooBank
     */
    private $boobank;
    protected function configure()
    {
        $this
            ->setName('boobank:backend:list')
            ->setDescription('list backend')
            ->addArgument('name', InputArgument::OPTIONAL, 'Argument description')
            //->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;


    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $argument = $input->getArgument('name');
        $this->boobank = $this->getContainer()->get('boobank');

$list = $this->boobank->getConnexions();
dump($list);
        $output->writeln('Command result.');
    }

}
