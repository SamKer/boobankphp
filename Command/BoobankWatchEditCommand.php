<?php

namespace SamKer\BoobankBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class BoobankWatchEditCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('boobank:watch:edit')
            ->setDescription('survey account')
            ->addOption('backend', 'b', InputOption::VALUE_REQUIRED, 'watch specific backend', false)
            ->addOption('account', 'a', InputOption::VALUE_REQUIRED, 'watch specific account for selected backend', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $backend = $input->getOption('backend');
        $account = $input->getOption('account');
        $helper = $this->getHelper('question');


        $boobank = $this->getContainer()->get('boobank');

        $rules = $boobank->getWatchRules($backend, $account);

        $output->writeln('survey config:');
        $question = new Question('new survey history:', $rules['survey']['history']);
        $rules['survey']['history'] = $helper->ask($input, $output, $question);

        $output->writeln('survey config:');
        $question = new Question('new survey list:', $rules['survey']['list']);
        $rules['survey']['list'] = $helper->ask($input, $output, $question);


        $output->writeln('action config:');
        $question = new Question('new action database:', $rules['action']['database']);
        $rules['action']['database'] = $helper->ask($input, $output, $question);

        $output->writeln('action config:');
        $question = new Question('new action mail:', $rules['action']['mail']);
        $rules['action']['mail'] = $helper->ask($input, $output, $question);


        $boobank->saveWatchRules($backend, $account, $rules);
        $output->writeln('job done');
    }

}
