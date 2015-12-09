<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FormaLibre\BulletinBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FormaLibre\BulletinBundle\Entity\MatiereOptions;

class Updater060101 extends Updater
{
    private $container;
    private $bulletinManager;
    private $om;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->bulletinManager = $this->container->get('formalibre.manager.bulletin_manager');
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->log('Creating missing "matiere options"...');

        $sessions = $this->bulletinManager->getAvailableSessions();
        $matieresOptions = $this->bulletinManager->getAllMatieresOptions();
        $sessionIds = [];
            
        foreach ($matieresOptions as $matiereOptions) {
            $sessionsIds[] = $matiereOptions->getCourseSession()->getId();
        }

        $i = 0;

        foreach ($sessions as $session) {

            $sessionId = $session->getId();

            if (!in_array($sessionId, $sessionsIds)) {
                $matiereOption = new MatiereOptions();
                $matiereOption->setCourseSession($session);
                $this->om->persist($matiereOption);
                $i++;

                if ($i % 200 === 0) {
                    $this->log('Flushing 200 items');
                    $this->om->flush();
                }
            }
        }

        $this->log('Final flush');
        $this->om->flush();
    }
} 