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
            ->addOption('name', null, InputArgument::OPTIONAL, 'backend name')
        ;

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $backend = $input->getOption('name');
        $this->boobank = $this->getContainer()->get('boobank');

        $list = $this->boobank->getConnexions();
        if($backend !== null){
            $list = $this->boobank->getConnexion($backend);
        }
        dump($list);
        $output->writeln('Command result.');
    }

}
