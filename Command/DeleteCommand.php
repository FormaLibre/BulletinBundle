<?php

namespace FormaLibre\BulletinBundle\Command;

use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Deletes all periodes.
 */
class DeleteCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:bulletin:delete')->setDescription('Deletes all periodes.');
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
        $this->periodesDumpSql($consoleLogger);
        $bulletinManager->deleteAllPeriodes();
    }

    protected function periodesDumpSql(ConsoleLogger $logger)
    {
        $container = $this->getContainer();
        $fileSystem = $container->get('filesystem');
        $logger->log(LogLevel::INFO, 'Dumping sql for tables linked to periodes...');
        $dbName = $container->getParameter('database_name');
        $dbUser = $container->getParameter('database_user');
        $dbPwd = $container->getParameter('database_password');
        $now = new \DateTime();
        $sqlDir = $container->getParameter('formalibre.directories.pdf').'dumpsql';

        if (!is_dir($sqlDir)) {
            $fileSystem->mkdir($sqlDir, 0775);
        }
        $fileName = $sqlDir.DIRECTORY_SEPARATOR.'bulletins-'.$now->format('Y-m-d-H-i').'.sql';
        $cmd = 'mysqldump -u'.$dbUser.' -p'.$dbPwd.' '.$dbName.' '.
            'formalibre_bulletin_periode '.
            'formalibre_bulletin_periode_matieres '.
            'formalibre_bulletin_periode_point_divers '.
            'formalibre_bulletin_lock_status '.
            'formalibre_bulletin_periode_eleve_decision '.
            'formalibre_bulletin_periode_eleve_decision_matieres '.
            'formalibre_bulletin_periode_eleve_matiere_point '.
            'formalibre_bulletin_periode_eleve_matiere_remarque '.
            'formalibre_bulletin_periode_eleve_pointdivers_point '.
            '> '.$fileName;

        exec($cmd);
        $logger->log(LogLevel::INFO, 'Periode-relative tables have been dumped.');
    }
}
