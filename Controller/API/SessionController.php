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
use Claroline\CursusBundle\Entity\CourseSession;
use FormaLibre\BulletinBundle\Manager\BulletinManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Claroline\CoreBundle\Persistence\ObjectManager;

/**
 * @NamePrefix("api_")
 */
class SessionController extends FOSRestController
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
        $this->request = $request;
        $this->authorization = $authorization;
        $this->bulletinManager = $bulletinManager;
        $this->om = $om;
    }

    /**
     * @View(serializerGroups={"api_bulletin"})
     */
    public function getSearchPeriodeAdminSessionAction(Periode $periode, $page, $limit)
    {
        $this->throwExceptionIfNotBulletinAdmin();

        //wtf
        foreach ($periode->getCourseSessions() as $link) {
            $linkedSessionsIds[] = $link->getId();
        }

        $searches = $this->request->query->all();
        $sessions = $this->bulletinManager->searchAvailableSessions($searches, false, $page, $limit);

        foreach ($sessions as $session) {
            //wtf again
            (in_array($session->getId(), $linkedSessionsIds)) ?
                $session->setExtra(array('linked' => true)):
                $session->setExtra(array('linked' => false));
        }

        return array('sessions' => $sessions, 'total' => $this->bulletinManager->searchAvailableSessions($searches, true));
    }

    /**
     * @View(serializerGroups={"api_bulletin"})
     */
    public function addSessionToPeriodeAction(Periode $periode, CourseSession $session)
    {
        $this->throwExceptionIfNotBulletinAdmin();
        $periode->addMatiere($session);
        $this->om->persist($periode);
        $this->om->flush();

        return $session;
    }

    /**
     * @View(serializerGroups={"api_bulletin"})
     */
    public function removeSessionFromPeriodeAction(Periode $periode, CourseSession $session)
    {
        $this->throwExceptionIfNotBulletinAdmin();
        $periode->removeMatiere($session);
        $this->om->persist($periode);
        $this->om->flush();

        return $session;
    }

    /**
     * @View(serializerGroups={"api_bulletin"})
     */
    public function checkAllSessionsFromSearchAction(Periode $periode)
    {
        $this->throwExceptionIfNotBulletinAdmin();
        $searches = $this->request->query->all();
        $sessions = $this->bulletinManager->searchAvailableSessions($searches, false);
        $this->bulletinManager->addSessionsToPeriode($sessions, $periode);

        return $periode;
    }

    /**
     * @View(serializerGroups={"api_bulletin"})
     */
    public function removeAllSessionsFromSearchAction(Periode $periode)
    {
        $this->throwExceptionIfNotBulletinAdmin();
        $searches = $this->request->query->all();
        $sessions = $this->bulletinManager->searchAvailableSessions($searches, false);
        $this->bulletinManager->removeSessionsFromPeriode($sessions, $periode);

        return $periode;
    }

    public function getSessionSearchableFieldsAction()
    {
        return array('title', 'name', 'code');
    }

    private function throwExceptionIfNotBulletinAdmin()
    {
        if ($this->authorization->isGranted('ROLE_BULLETIN_ADMIN')) {
            return true;
        }

        throw new AccessDeniedException();
    }
}