<?php


namespace SamKer\BoobankPHP\Command;


use SamKer\BoobankPHP\Services\Command;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BoobankCheck extends Command
{

    protected static $defaultName = 'bbk:check';


    protected function configure()
    {
        $this->setDescription('Check boobank dependencies.')
        ->setHelp('This command check if program weboob and its module boobank...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->writeln("checking dependencies");

        $table = new Table($io);
        $table->setHeaders(["Program", "Installed", "bin Path"]);

        $r = $this->config->testConfig();

        $table->setRows(array_values($r['programs']));


        $table->render();


    }
}