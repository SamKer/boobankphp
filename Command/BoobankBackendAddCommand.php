<?php

namespace Sam\BoobankBundle\Command;

use Sam\BoobankBundle\Services\BooBank;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class BoobankBackendAddCommand extends ContainerAwareCommand
{
    /**
     * @var BooBank
     */
    private $boobank;
    protected function configure()
    {
        $this
            ->setName('boobank:backend:add')
            ->setDescription('list backend')
            ->addOption('name', null, InputArgument::OPTIONAL, 'backend name')
        ;

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $boobank = $this->getContainer()->get('boobank');
        $helper = $this->getHelper('question');
        $backend = $input->getOption('name');
        if(!$backend) {
            $question = new Question('Please enter the name of the backend');
            $backend = $helper->ask($input, $output, $question);
        }

        $question = new Question('Please enter the login for connection to '.$backend);
            $login = $helper->ask($input, $output, $question);

$question = new Question('What is the password for connection to '. $backend . '?');
    $question->setHidden(true);
    $question->setHiddenFallback(false);

    $password = $helper->ask($input, $output, $question);

    $boobank->
    }

}
