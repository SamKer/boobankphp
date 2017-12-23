<?php

namespace SamKer\BoobankBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BoobankWatchCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('boobank:watch')
            ->setDescription('survey account')
//            ->addOption('backend', 'b', InputOption::VALUE_OPTIONAL, 'watch specific backend')
//            ->addOption('all', null, InputOption::VALUE_OPTIONAL, 'watch all backends')
//            ->addOption('account', 'a', InputOption::VALUE_OPTIONAL, 'watch specific account for selected backend')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        $backend = $input->getOption('backend');
//        $all = $input->getOption('backend');
//        $account = $input->getOption('backend');
//
//        if($all !== null) {
//            //all backends
//        } elseif ($backend === null) {
//            throw new \Exception("a specific backend have to be define, with --backend option");
//        }
//
//        if($account !== null && $backend === null) {
//            throw new \Exception("a specific backend have to be define for specific account, with --backend option");
//        }

        $this->getContainer()->get('boobank')->watch();

        $output->writeln('Command result.');
    }

}
