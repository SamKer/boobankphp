<?php

namespace SamKer\BoobankBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BoobankMailTestCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('boobank:mail:test')
            ->setDescription('just send 2 mail for test templates');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $boobank = $this->getContainer()->get('boobank');
        $boobank->sendMail("test","123456789", [
            0 => ["date"=>"2015-02-01", "label"=>"virment quelconque", "amount"=>123.4],
            1 => ["date"=>"2015-04-01", "label"=>"virement autres", "amount"=>456.7]
        ]);

        $boobank->sendMail("test","123456789", [
            "date"=>"2015-02-01", "label"=>"votre compte", "balance"=>1234.52
        ], "list");

        $output->writeln("mails send");

    }
}
