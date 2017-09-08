<?php

namespace FormaLibre\BulletinBundle\Command;

use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Archives all bulletins.
 */
class ArchiveCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:bulletin:archive')->setDescription('Archives all bulletins.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $verbosityLevelMap = [
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG => OutputInterface::VERBOSITY_NORMAL,
        ];
        $consoleLogger = new ConsoleLogger($output, $verbosityLevelMap);
        $bulletinManager = $this->getContainer()->get('formalibre.manager.bulletin_manager');
        $bulletinManager->setLogger($consoleLogger);
        $bulletinManager->archiveAllBulletins();
    }
}
