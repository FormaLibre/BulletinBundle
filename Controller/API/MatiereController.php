<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FormaLibre\BulletinBundle\Controller\API;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FormaLibre\BulletinBundle\Entity\Periode;
use FormaLibre\BulletinBundle\Entity\MatiereOptions;
use FormaLibre\BulletinBundle\Manager\BulletinManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Claroline\CoreBundle\Persistence\ObjectManager;

/**
 * @NamePrefix("api_")
 */
class MatiereController extends FOSRestController
{
    /**
     * @DI\InjectParams({
     *     "request"         = @DI\Inject("request"),
     *     "bulletinManager" = @DI\Inject("formalibre.manager.bulletin_manager"),
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        Request $request,
        BulletinManager $bulletinManager,
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om
    )
    {
        $this->bulletinManager = $bulletinManager;
        $this->request         = $request;
        $this->authorization   = $authorization;
        $this->om              = $om;
    }

    /**
     * @View(serializerGroups={"api_bulletin"})
     */
    public function getSearchMatiereOptionsAction($page, $limit)
    {
        $this->throwExceptionIfNotBulletinAdmin();

        $searches = $this->request->query->all();
        $matiereOptions = $this->bulletinManager->searchMatieresOptions($searches, false, $page, $limit);
        $total = $this->bulletinManager->searchMatieresOptions($searches, true);

        return array('options' => $matiereOptions, 'total' => $total);
    }

    public function getMatiereOptionsSearchableFieldsAction()
    {
        $this->throwExceptionIfNotBulletinAdmin();

        return array('title', 'code', 'name');
    }

    /**
     * @View(serializerGroups={"api_bulletin"})
     */
    public function setMatiereoptionPositionAction(MatiereOptions $matiereOptions, $position)
    {
        $this->throwExceptionIfNotBulletinAdmin();
        $matiereOptions->setPosition($position);
        $this->om->persist($matiereOptions);
        $this->om->flush();

        return $matiereOptions;
    }

    /**
     * @View(serializerGroups={"api_bulletin"})
     */
    public function setMatiereoptionTotalAction(MatiereOptions $matiereOptions, $total)
    {
        $this->throwExceptionIfNotBulletinAdmin();
        $matiereOptions->setTotal($total);
        $this->om->persist($matiereOptions);
        $this->om->flush();

        return $matiereOptions;
    }

    /**
     * @View(serializerGroups={"api_bulletin"})
     */
    public function setMatiereoptionColorAction(MatiereOptions $matiereOptions, $color)
    {
        $this->throwExceptionIfNotBulletinAdmin();
        $matiereOptions->setColor($color);
        $this->om->persist($matiereOptions);
        $this->om->flush();

        return $matiereOptions;
    }

    private function throwExceptionIfNotBulletinAdmin()
    {
        if ($this->authorization->isGranted('ROLE_BULLETIN_ADMIN')) {
            return true;
        }

        throw new AccessDeniedException();
    }
}