<?php

namespace SamKer\BoobankPHP\Command;

use SamKer\BoobankBundle\Services\BooBank;
use SamKer\BoobankPHP\Services\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BoobankBackendList extends Command
{

     protected static $defaultName = 'bbk:backend:list';

    protected function configure()
    {
        $this
            ->setDescription('list backend')
            ->addOption('name', null, InputArgument::OPTIONAL, 'backend name')
        ;

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $backend = $input->getOption('name');

        $list = $this->boobank->getBackEnds();
        if($backend !== null){
            $list = $this->boobank->getBackend($backend);
        }
        dump($list);
    }

}
