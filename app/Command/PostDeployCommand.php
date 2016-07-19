<?php

namespace App\Command;

use JLaso\SimpleStats\Stats;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

        $rootDir = dirname(dirname(dirname(__DIR__)));
        @mkdir("{$rootDir}/public/cache", 0777, true);
        @mkdir("{$rootDir}/app/cache", 0777, true);
        @mkdir("{$rootDir}/app/db", 0777, true);

        // force creation of DB
        $stats = new Stats();

        $output->writeln('Process completed in '.intval((microtime(true)-$start)*1000).' msec');
    }
}
