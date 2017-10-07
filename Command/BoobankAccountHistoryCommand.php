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
            ->addOption('backend', "b", InputOption::VALUE_REQUIRED, 'backend name')
            ->addOption('account', "a", InputOption::VALUE_REQUIRED, "account id")
            ->addOption('date', "d", InputOption::VALUE_OPTIONAL, "from date: Y-m-d", false)
            ->addOption('select', "f", InputOption::VALUE_OPTIONAL, "filters: label,date...", false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $backend = $input->getOption('backend');
        $account = $input->getOption('account');
        $date = $input->getOption('date');
        $select = $input->getOption('select');


        $this->boobank = $this->getContainer()->get('boobank');

        $account = $this->boobank->getHistory($account, $backend, $date, $select);
        dump($account);
        $output->writeln("<---history");
    }

}
