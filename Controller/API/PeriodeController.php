<?php

namespace FormaLibre\BulletinBundle\Controller\API;

use FormaLibre\BulletinBundle\Manager\BulletinManager;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;

/**
 * @NamePrefix("api_")
 */
class PeriodeController extends FOSRestController
{
    private $bulletinManager;
    private $request;

    /**
     * @DI\InjectParams({
     *     "bulletinManager" = @DI\Inject("formalibre.manager.bulletin_manager"),
     *     "request"         = @DI\Inject("request")
     * })
     */
    public function __construct(BulletinManager $bulletinManager, Request $request)
    {
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
//        $this->throwExceptionIfNotBulletinAdmin();
//        throw new \Exception(count($classes));

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
//        $matiereOptions = $this->bulletinManager->searchMatieresOptions($searches, false, $page, $limit);
//        $total = $this->bulletinManager->searchMatieresOptions($searches, true);
//
//        return array('options' => $matiereOptions, 'total' => $total);
    }
}