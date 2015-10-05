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

class LoadGroupData extends AbstractFixture implements ContainerAwareInterface
{
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $roleManager = $this->container->get('claroline.manager.role_manager');
        $roleRepository = $manager->getRepository('ClarolineCoreBundle:Role');

        if (!$roleRepository->findOneByName('ROLE_BULLETIN_ADMIN')){
            $roleManager->createBaseRole('ROLE_BULLETIN_ADMIN', 'Bulletin Admin');
        }

    }
}
