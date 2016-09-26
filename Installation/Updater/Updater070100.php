<?php

namespace FormaLibre\BulletinBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater070100 extends Updater
{
    private $bulletinManager;
    private $om;

    public function __construct(ContainerInterface $container)
    {
        $this->bulletinManager = $container->get('formalibre.manager.bulletin_manager');
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->transferMatiereOptions();
    }

    public function transferMatiereOptions()
    {
        $this->log('Copying matiere options to sessions...');
        $matieresOptions = $this->bulletinManager->getAllMatieresOptions();

        foreach ($matieresOptions as $mo) {
            $session = $mo->getMatiere();
            $details = $session->getDetails();

            if (is_null($details)) {
                $details = [];
            }
            $position = $mo->getPosition();
            $total = $mo->getTotal();
            $color = $mo->getColor();

            if (!is_null($position)) {
                $session->setDisplayOrder($position);
            }
            $details['color'] = $color;
            $details['total'] = $total;
            $session->setDetails($details);
            $this->om->persist($session);
        }

        $this->om->flush();
    }
}