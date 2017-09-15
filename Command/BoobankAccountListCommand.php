<?php

namespace Sam\BoobankBundle\Command;

use Sam\BoobankBundle\Services\Boobank;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BoobankAccountListCommand extends ContainerAwareCommand
{
    /**
     * @var Boobank
     */
    private $boobank;
    protected function configure()
    {
        $this
            ->setName('boobank:account:list')
            ->setDescription('List account')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'backend name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $backend = $input->getOption('name');
        $this->boobank = $this->getContainer()->get('boobank');

        $account = $this->boobank->listComptes($backend);
dump($account);
        $output->writeln('Command result.');
    }

}
