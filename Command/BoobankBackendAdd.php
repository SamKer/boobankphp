<?php

namespace SamKer\BoobankPHP\Command;

use SamKer\BoobankBundle\Services\BooBank;
use SamKer\BoobankPHP\Services\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class BoobankBackendAdd extends Command
{

    protected static $defaultName = 'bbk:backend:add';


    protected function configure()
    {
        $this
            ->setDescription('add backend')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'backend name');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $boobank = $this->boobank;

        $helper = $this->getHelper('question');
        $backend = $input->getOption('name');
        if (!$backend) {
            $question = new Question('Please enter the name of the backend: ');
            $backend = $helper->ask($input, $output, $question);
        }

        //list modules
        $availableModules = $boobank->getAvailableModules();
        $question = new ChoiceQuestion("select module: ", $availableModules, 0);
        $module = $helper->ask($input, $output, $question);

        //login
        $question = new Question('Please enter the login for connection to ' . $backend . ': ');
        $login = $helper->ask($input, $output, $question);

        $question = new Question('What is the password for connection to ' . $backend . '?: ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $password = $helper->ask($input, $output, $question);

        $question = new Question('Please enter the mail for ' . $backend . ': ', false);
        $mail = $helper->ask($input, $output, $question);

        $boobank->addBackend($backend, $module, $login, $password, $mail);

        $output->writeln("backend created");
    }

}
