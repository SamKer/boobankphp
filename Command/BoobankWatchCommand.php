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
            ->addOption('backend', 'b', InputOption::VALUE_OPTIONAL, 'watch specific backend', false)
            ->addOption('account', 'a', InputOption::VALUE_OPTIONAL, 'watch specific account for selected backend', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $backend = $input->getOption('backend');
        $account = $input->getOption('account');

        if($backend === null && $account !== null) {
            throw new \Exception("a specific backend have to be defined, with --backend option");
        }
        $boobank = $this->getContainer()->get('boobank');


            $result = $boobank->watch($backend, $account);
            dump($result);





        $output->writeln('job done');
    }

}
