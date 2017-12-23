<?php

namespace SamKer\BoobankBundle\Command;

use SamKer\BoobankBundle\Services\BooBank;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class BoobankBackendDelCommand extends ContainerAwareCommand
{
    /**
     * @var BooBank
     */
    private $boobank;

    protected function configure()
    {
        $this
            ->setName('boobank:backend:remove')
            ->setDescription('remove backend')
            ->addOption('name', null, InputArgument::OPTIONAL, 'backend name');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $boobank = $this->getContainer()->get('boobank');
        $helper = $this->getHelper('question');
        $backend = $input->getOption('name');
        if (!$backend) {
            $question = new Question('Please enter the backend to remove: ');
            $backend = $helper->ask($input, $output, $question);
        }

        $boobank->removeBackend($backend);

        $output->writeln("backend removed");
    }

}
