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
class BulletinController extends FOSRestController
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
        $datas = $this->bulletinManager->getAllUserPointsDatas($user);
        $datas['user'] = $userDatas;

        return $datas;
    }

    /**
     * @View(serializerGroups={"api_bulletin"})
     */
    public function putAllUsersPointsAction(User $user)
    {
        $this->checkBulletinAdmin();
        $datas = $this->request->query->all();
        $pointsDatas = isset($datas['points']) ? $datas['points'] : array();
        $pointsDiversDatas = isset($datas['pointsDivers']) ? $datas['pointsDivers'] : array();
        $eleveMatiereOptionsDatas = isset($datas['eleveMatiereOptions']) ? $datas['eleveMatiereOptions'] : array();
        $deliberatedDatas = array();
        $pointsIds = array();
        $pointsDiversIds = array();
        $eleveMatieresOptionsIds = array();

        foreach ($pointsDatas as $pempId => $point) {
            $pointsIds[] = $pempId;
        }

        foreach ($pointsDiversDatas as $pepdpId => $point) {
            $pointsDiversIds[] = $pepdpId;
        }

        foreach ($eleveMatiereOptionsDatas as $datas) {
            $eleveMatieresOptionsIds[] = $datas['id'];
            $deliberatedDatas[$datas['id']] = $datas['deliberated'];
        }
        $pemps = $this->bulletinManager->getPempsByUserAndIds($user, $pointsIds);
        $pepdps = $this->bulletinManager->getPepdpsByUserAndIds($user, $pointsDiversIds);
        $eleveMatieresOptions = $this->bulletinManager->getEleveMatiereOptionsByUserAndIds($user, $eleveMatieresOptionsIds);
        $datas = $this->bulletinManager->updatePoints($pemps, $pepdps, $eleveMatieresOptions, $pointsDatas, $pointsDiversDatas, $deliberatedDatas);

        return $datas;
    }

    /**
     * @View(serializerGroups={"api_bulletin"})
     */
    public function getPointCodesAction()
    {
        $codes = array();
        $pointCodes = $this->bulletinManager->getAllPointCodes();

        foreach ($pointCodes as $pointCode) {
            $codes[] = array(
                'id' => $pointCode->getId(),
                'code' => $pointCode->getCode(),
                'info' => $pointCode->getInfo(),
                'shortInfo' => $pointCode->getShortInfo(),
                'isDefaultValue' => $pointCode->getIsDefaultValue(),
                'ignored' => $pointCode->getIgnored()
            );
        }

        return $codes;
    }

    private function checkBulletinAdmin()
    {
        if (!$this->authorization->isGranted('ROLE_BULLETIN_ADMIN')) {

            throw new AccessDeniedException();
        }
    }
}