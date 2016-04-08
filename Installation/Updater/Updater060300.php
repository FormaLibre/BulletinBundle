<?php

namespace FormaLibre\BulletinBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use FormaLibre\BulletinBundle\Entity\PointCode;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater060300 extends Updater
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
        $this->createPointCodes();
    }

    public function createPointCodes()
    {
        $this->log('Creating point codes...');
        $defaultCodes = $this->bulletinManager->getDefaultPointCodes();

        if (count($defaultCodes) === 0) {
            $defaultCode = new PointCode();
            $defaultCode->setIsDefaultValue(true);
            $defaultCode->setCode(900);
            $defaultCode->setIgnored(true);
            $defaultCode->setInfo('no_set_point');
            $defaultCode->setShortInfo('PNM');
            $this->om->persist($defaultCode);
        }
        $notEvaluatedCode = $this->bulletinManager->getPointCodeByCode(999);

        if (is_null($notEvaluatedCode)) {
            $notEvaluatedCode = new PointCode();
            $notEvaluatedCode->setCode(999);
            $notEvaluatedCode->setIgnored(true);
            $notEvaluatedCode->setInfo('not_evaluated');
            $notEvaluatedCode->setShortInfo('NE');
            $this->om->persist($notEvaluatedCode);
        }
        $medicalCertificateCode = $this->bulletinManager->getPointCodeByCode(888);

        if (is_null($medicalCertificateCode)) {
            $medicalCertificateCode = new PointCode();
            $medicalCertificateCode->setCode(888);
            $medicalCertificateCode->setIgnored(true);
            $medicalCertificateCode->setInfo('medical_certificate');
            $medicalCertificateCode->setShortInfo('CM');
            $this->om->persist($medicalCertificateCode);
        }

        $this->om->flush();
    }
}