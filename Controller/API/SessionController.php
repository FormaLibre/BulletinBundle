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
use FormaLibre\BulletinBundle\Manager\BulletinManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @NamePrefix("api_")
 */
class SessionController extends FOSRestController
{
    /**
     * @DI\InjectParams({
     *     "request"         = @DI\Inject("request"),
     *     "bulletinManager" = @DI\Inject("formalibre.manager.bulletin_manager"),
     *     "authorization"   = @DI\Inject("security.authorization_checker")
     * })
     */
    public function __construct(
        Request $request,
        BulletinManager $bulletinManager,
        AuthorizationCheckerInterface $authorization
    )
    {
        $this->request = $request;
        $this->authorization = $authorization;
        $this->bulletinManager = $bulletinManager;
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
    public function addSessionToPeriode(Periode $periode, CourseSession $session)
    {
        $this->checkOpen();
        $periode->addMatiere($session);
        $this->om->persist($periode);
        $this->om->flush();

        return $session;
    }

    /**
     * @View(serializerGroups={"api_bulletin"})
     */
    public function removeSessionFromPeriode(Periode $periode, CourseSession $session)
    {
        $this->checkOpen();
        $periode->removeMatiere($session);
        $this->om->persist($periode);
        $this->om->flush();

        return $session;
    }

    /**
     * @View(serializerGroups={"api_bulletin"})
     */
    public function invertSessionsPeriode(Periode $periode)
    {
        $this->checkOpen();
        $sessions = $this->request->request->all();
        $sessionIds = array();

        foreach ($sessions as $session) {
            $sessionIds[] = $session['id'];
        }

        $sessions = $this->om->findByIds('Claroline\CursusBundle\Entity\CourseSession', $sessionIds);
        $this->om->startFlushSuite();

        foreach ($sessions as $session) {
            $this->bulletinManager->invertSessionPeriode($periode, $session);
        }

        $this->om->endFlushSuite();

        return $periode;
    }

    /**
     * @View(serializerGroups={"api_bulletin"})
     */
    public function checkAllSessionsFromSearchAction(Periode $periode)
    {
        $this->checkOpen();
        $searches = $this->request->query->all();
        $sessions = $this->bulletinManager->searchAvailableSessions($searches, false);
        $this->bulletinManager->addSessionsToPeriode($sessions, $periode);

        return new JsonResponse('success', 200);
    }

    /**
     * @View(serializerGroups={"api_bulletin"})
     */
    public function removeAllSessionsFromSearchAction(Periode $periode)
    {
        $this->checkOpen();
        $searches = $this->request->query->all();
        $sessions = $this->bulletinManager->searchAvailableSessions($searches, false);
        $this->bulletinManager->removeSessionsFromPeriode($sessions, $periode);

        return new JsonResponse('success', 200);
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