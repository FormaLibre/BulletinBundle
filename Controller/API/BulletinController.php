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

/**
 * @NamePrefix("api_")
 */
class BulletinController extends FOSRestController
{
    /**
     * @DI\InjectParams({
     *     "request" = @DI\Inject("request")
     * })
     */
    public function __construct(
        Request $request
    )
    {
        $this->request = $request;
    }

    /**
     * @View(serializerGroups={"api_bulletin"})
     */
    public function getAdminSessionAction(Periode $periode, $page, $limit)
    {
        $this->throwExceptionIfNotBulletinAdmin();
        $linkedSessionsIds = array();

        foreach ($periode->getCourseSessions() as $link) {
            $linkedSessionsIds[] = $link->getId();
        }

        $sessions = $this->bulletinManager->getAvailableSessions(false, $page, $limit);

        foreach ($sessions as $session) {
            (in_array($session->getId(), $linkedSessionsIds)) ?
                $session->setExtra(array('linked' => true)):
                $session->setExtra(array('linked' => false));
        }

        return array('sessions' => $sessions, 'total' => $this->bulletinManager->getAvailableSessions(true));
    }

    /**
     * @View(serializerGroups={"api_bulletin"})
     */
    public function searchAdminSessionAction(Periode $periode, $page, $limit)
    {
        $this->throwExceptionIfNotBulletinAdmin();

        foreach ($periode->getCourseSessions() as $link) {
            $linkedSessionsIds[] = $link->getId();
        }

        $searches = $this->request->query->all();
        $sessions = $this->bulletinManager->searchAvailableSessions($searches, false, $page, $limit);

        foreach ($sessions as $session) {
            (in_array($session->getId(), $linkedSessionsIds)) ?
                $session->setExtra(array('linked' => true)):
                $session->setExtra(array('linked' => false));
        }

        return array('sessions' => $sessions, 'total' => $this->bulletinManager->getAvailableSessions(true));
    }

    private function throwExceptionIfNotBulletinAdmin()
    {
        if ($this->authorization->isGranted('ROLE_BULLETIN_ADMIN')) {
            return true;
        }

        throw new AccessDeniedException();
    }
}