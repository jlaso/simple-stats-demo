<?php

namespace App\Command;

use JLaso\SimpleStats\Stats;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use JLaso\SimpleLogger\PlainFileLogger as Logger;

class PostDeployCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('post:deploy')
            ->setDescription('Actions to take after deploy')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);

        $folders = array(
            '/public/cache' => 0777,
            '/app/cache' => 0777,
            'app/db' => 0777,
        );
        $rootDir = dirname(dirname(dirname(__DIR__)));
        $logger = Logger::getInstance();
        foreach($folders as $folder=>$perms) {
            $logger->info("Creating folder {$folder}");
            @mkdir($rootDir.$folder, $perms, true);
        }
        
        // force creation of DB
        $stats = new Stats();
        $logger->info("Created sqlite database file on ".$stats->getDataBaseFile());
        
        $timeTaken = intval((microtime(true)-$start)*1000);
        $logger->info("Process completed in {$timeTaken} msec");
        $output->writeln("Process completed in {$timeTaken} msec");
    }
}
