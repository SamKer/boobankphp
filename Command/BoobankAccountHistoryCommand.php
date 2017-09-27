<?php

namespace Sam\BoobankBundle\Command;

use Sam\BoobankBundle\Services\Boobank;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BoobankAccountHistoryCommand extends ContainerAwareCommand
{
    /**
     * @var Boobank
     */
    private $boobank;
    protected function configure()
    {
        $this
            ->setName('boobank:account:history')
            ->setDescription('get history account')
            ->addOption('backend', null, InputOption::VALUE_REQUIRED, 'backend name')
            ->addOption('account', null, InputOption::VALUE_REQUIRED, "account id")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $backend = $input->getOption('backend');
        $account = $input->getOption('account');

        $this->boobank = $this->getContainer()->get('boobank');

        $account = $this->boobank->getHistory($account, $backend);
        dump($account);
    }

}
