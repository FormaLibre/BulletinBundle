<?php

namespace FormaLibre\BulletinBundle\Controller\API;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use FormaLibre\BulletinBundle\Manager\BulletinManager;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @NamePrefix("api_")
 */
class PeriodeController extends FOSRestController
{
    private $authorization;
    private $bulletinManager;
    private $request;

    /**
     * @DI\InjectParams({
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "bulletinManager" = @DI\Inject("formalibre.manager.bulletin_manager"),
     *     "request"         = @DI\Inject("request")
     * })
     */
    public function __construct(AuthorizationCheckerInterface $authorization, BulletinManager $bulletinManager, Request $request)
    {
        $this->authorization = $authorization;
        $this->bulletinManager = $bulletinManager;
        $this->request = $request;
    }

    /**
     * @View(serializerGroups={"api_bulletin"})
     */
    public function getClassUsersAction($page, $limit)
    {
        $users = array();
        $classes = $this->bulletinManager->getTaggedGroups();
        $searches = $this->request->query->all();
        $userDatas = $this->bulletinManager->searchGroupsUsers($classes, $searches);

        foreach ($userDatas as $data) {
            $users[] = array(
                'id' => $data['id'],
                'firstName' => $data['first_name'],
                'lastName' => $data['last_name'],
                'groupId' => $data['group_id'],
                'groupName' => $data['group_name']
            );
        }

        return $users;
    }

    /**
     * @View(serializerGroups={"api_bulletin"})
     */
    public function getAllUsersPointsAction(User $user)
    {
        $this->checkBulletinAdmin();
        $userDatas = array(
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName()
        );
        $datas = $this->bulletinManager->getAllPeriodesUserMatieresDatas($user);
        $periodesDatas = $datas['periodesDatas'];
        $userMatieresDatas = $datas['userMatieresDatas'];
        $allUserPoints = $this->bulletinManager->getAllUserPoints($user);
        $allUserPointsDivers = $this->bulletinManager->getAllUserPointsDivers($user);

        foreach ($allUserPoints as $point) {
            $matiere = $point->getMatiere();
            $periode = $point->getPeriode();
            $matiereId = $matiere->getId();
            $periodeId = $periode->getId();

            if (isset($userMatieresDatas[$matiereId]['periodes'][$periodeId])) {
                $userMatieresDatas[$matiereId]['periodes'][$periodeId]['pempId'] = $point->getId();
                $userMatieresDatas[$matiereId]['periodes'][$periodeId]['point'] = $point->getPoint();
                $userMatieresDatas[$matiereId]['periodes'][$periodeId]['total'] = $point->getTotal();
            }
        }

        foreach ($allUserPointsDivers as $pointDiversPoint) {
            $pointDivers = $pointDiversPoint->getDivers();
            $pointDiversId = $pointDivers->getId();
            $periode = $pointDiversPoint->getPeriode();
            $periodeId = $periode->getId();

            if (isset($periodesDatas['periodes'][$periodeId]['pointsDivers'][$pointDiversId])) {
                $periodesDatas['periodes'][$periodeId]['pointsDivers'][$pointDiversId]['pepdpId'] = $pointDiversPoint->getId();
                $periodesDatas['periodes'][$periodeId]['pointsDivers'][$pointDiversId]['point'] = $pointDiversPoint->getPoint();
            }
        }

        return array(
            'user' => $userDatas,
            'matieres' => $userMatieresDatas,
            'periodes' => $periodesDatas['periodes'],
            'matieresPeriodes' => $periodesDatas['matieresPeriodes'],
            'nbUserPoints' => count($allUserPoints),
            'nbUserPointsDivers' => count($allUserPointsDivers)
        );
    }

    /**
     * @View(serializerGroups={"api_bulletin"})
     */
    public function putAllUsersPointsAction(User $user, $points, $pointsDivers)
    {
        $this->checkBulletinAdmin();
        $pointsDatas = json_decode($points, true);
        $pointsDiversDatas = json_decode($pointsDivers, true);
        $pointsIds = array();
        $pointsDiversIds = array();

        foreach ($pointsDatas as $pempId => $point) {
            $pointsIds[] = $pempId;
        }

        foreach ($pointsDiversDatas as $pepdpId => $point) {
            $pointsDiversIds[] = $pepdpId;
        }
        $pemps = $this->bulletinManager->getPempsByUserAndIds($user, $pointsIds);
        $pepdps = $this->bulletinManager->getPepdpsByUserAndIds($user, $pointsDiversIds);
        $datas = $this->bulletinManager->updatePoints($pemps, $pepdps, $pointsDatas, $pointsDiversDatas);

        return $datas;
    }

    private function checkBulletinAdmin()
    {
        if (!$this->authorization->isGranted('ROLE_BULLETIN_ADMIN')) {

            throw new AccessDeniedException();
        }
    }
}