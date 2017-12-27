<?php

namespace SamKer\BoobankBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class BoobankBackendEditCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('boobank:backend:edit')
            ->setDescription('Modify a backend')
            ->addArgument('backend', InputArgument::REQUIRED, 'backend to edit')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $backend = $input->getArgument('backend');

        $boobank = $this->getContainer()->get('boobank');

        $params = $boobank->getBackend($backend);


        $helper = $this->getHelper('question');
        //list modules
        $availableModules = $boobank->getAvailableModules();
        $defaultModule = array_search($params['_module'], $availableModules);
        $question = new ChoiceQuestion("select module: ", $availableModules, $defaultModule);
        $module = $helper->ask($input, $output, $question);

        //login
        $question = new Question('Please enter the login for connection to ' . $backend . ': ', $params['login']);
        $login = $helper->ask($input, $output, $question);

        $question = new Question('What is the password for connection to ' . $backend . '?: ', $params['password']);
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $password = $helper->ask($input, $output, $question);

        if(!isset($params['mail'])) {
            $params['mail'] = "";
        }
        $question = new Question('Please enter the mail for ' . $backend . ': ', $params['mail']);
        $mail = $helper->ask($input, $output, $question);

        $boobank->editBackend($backend, $module, $login, $password, $mail);

        $output->writeln("backend modified");
    }

}
