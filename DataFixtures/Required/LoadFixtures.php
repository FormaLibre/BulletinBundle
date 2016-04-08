<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FormaLibre\BulletinBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use FormaLibre\BulletinBundle\Entity\PeriodesGroup;
use FormaLibre\BulletinBundle\Entity\PointCode;

class LoadGroupData extends AbstractFixture implements ContainerAwareInterface
{
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $om = $this->container->get('doctrine.orm.entity_manager');
        $roleManager = $this->container->get('claroline.manager.role_manager');
        $roleRepository = $manager->getRepository('ClarolineCoreBundle:Role');

        if (!$roleRepository->findOneByName('ROLE_BULLETIN_ADMIN')){
            $roleManager->createBaseRole('ROLE_BULLETIN_ADMIN', 'Bulletin Admin');
        }

        // Creates default code points
        $defaultCode = new PointCode();
        $defaultCode->setIsDefaultValue(true);
        $defaultCode->setCode(900);
        $defaultCode->setIgnored(true);
        $defaultCode->setInfo('no_set_point');
        $defaultCode->setShortInfo('PNM');
        $om->persist($defaultCode);

        $notEvaluatedCode = new PointCode();
        $notEvaluatedCode->setCode(999);
        $notEvaluatedCode->setIgnored(true);
        $notEvaluatedCode->setInfo('not_evaluated');
        $notEvaluatedCode->setShortInfo('NE');
        $om->persist($notEvaluatedCode);

        $medicalCertificateCode = new PointCode();
        $medicalCertificateCode->setCode(888);
        $medicalCertificateCode->setIgnored(true);
        $medicalCertificateCode->setInfo('medical_certificate');
        $medicalCertificateCode->setShortInfo('CM');
        $om->persist($medicalCertificateCode);

        $om->flush();
    }
}
