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


        if(!$backend ||!$account) {
            throw new \Exception("a backend & account must be defined with -a & -b options");
        }


        $boobank = $this->getContainer()->get('boobank');

        $rules = $boobank->getWatchRules($backend, $account);


        $output->writeln("current config for $backend:$account:");
        dump($rules);

        $output->writeln('------------new survey config definition----------:');
        $question = new Question($rules['survey']['history'] . '<= survey.history:(true|false)?  ', $rules['survey']['history']);
        $question->setValidator(function ($value) {
            if (trim($value) === 'true' || trim($value) === "1") {
                return true;
            }
            return false;
        });
        $rules['survey']['history'] = $helper->ask($input, $output, $question);

        $question = new Question($rules['survey']['list'] . '<= survey.list:(true|false)?  ', $rules['survey']['list']);
        $question->setValidator(function ($value) {
            if (trim($value) === 'true' || trim($value) === "1") {
                return true;
            }
            return false;
        });
        $rules['survey']['list'] = $helper->ask($input, $output, $question);


        $question = new Question($rules['action']['database'] . '<= action.database:(true|false)?  ', $rules['action']['database']);
        $question->setValidator(function ($value) {
            if (trim($value) === 'true' || trim($value) === "1") {
                return true;
            }
            return false;
        });
        $rules['action']['database'] = $helper->ask($input, $output, $question);

        $question = new Question($rules['action']['mail'] . '<= action.mail:(true|false)?  ', $rules['action']['mail']);
        $question->setValidator(function ($value) {
            if (trim($value) === 'true' || trim($value) === "1") {
                return true;
            }
            return false;
        });
        $rules['action']['mail'] = $helper->ask($input, $output, $question);


        $boobank->saveWatchRules($backend, $account, $rules);
        $output->writeln('job done');
    }

}
