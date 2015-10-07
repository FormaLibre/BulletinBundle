<?php

namespace FormaLibre\BulletinBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CursusBundle\Entity\CourseSession;
use Doctrine\ORM\EntityManager;
use FormaLibre\BulletinBundle\Entity\Periode;
use JMS\DiExtraBundle\Annotation as DI;
//use Claroline\CursusBundle\Entity\CourseSession;

/**
 * @DI\Service("formalibre.manager.bulletin_manager")
 */
class BulletinManager
{
    private $em;
    private $eventDispatcher;
    private $om;

    private $groupeTitulaireRepo;
    private $pointDiversRepo;

    /**
     * @DI\InjectParams({
     *      "em"              = @DI\Inject("doctrine.orm.entity_manager"),
     *      "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *      "om"              = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        EntityManager $em,
        StrictDispatcher $eventDispatcher,
        ObjectManager $om
    )
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->om = $om;

        $this->groupeTitulaireRepo = $om->getRepository('FormaLibreBulletinBundle:GroupeTitulaire');
        $this->pointDiversRepo = $om->getRepository('FormaLibreBulletinBundle:PointDivers');
    }

    public function getTaggedGroups()
    {
        $params = array(
            'tag' => 'Classe',
            'strict' => true,
            'class' => 'Claroline\CoreBundle\Entity\Group',
            'object_response' => true,
            'ordered_by' => 'name',
            'order' => 'ASC'
        );
        $event = $this->eventDispatcher->dispatch(
            'claroline_retrieve_tagged_objects',
            'GenericDatas',
            array($params)
        );
        $taggedGroups = $event->getResponse();
        $groups = empty($taggedGroups) ? array() : $taggedGroups;

        return $groups;
    }

    public function getMatiereGroupsByUserAndPeriode(User $user, Periode $periode)
    {
        $datas = array();
        $matieres = $this->getMatieresByProf($user, $periode);

        foreach ($matieres as $matiere) {
            $datas[$matiere->getId()] = array();
            $datas[$matiere->getId()]['matiere'] = $matiere;
            $datas[$matiere->getId()]['groups'] = $this->getGroupsByMatiere($matiere);
        }

        return $datas;
    }

    public function getMatieresByProf(User $user, Periode $periode)
    {
        $matieres = array();
        $sessions = $periode->getMatieres();

        if (count($sessions) > 0) {
            $qb = $this->em->createQueryBuilder();
            $qb->select('csu')
                ->from('Claroline\CursusBundle\Entity\CourseSessionUser', 'csu')
                ->where('csu.user = :user')
                ->andWhere('csu.userType = :userType')
                ->andWhere('csu.session IN (:sessions)')
                ->setParameter('user', $user)
                ->setParameter('userType', 1)
                ->setParameter('sessions', $sessions);
            $query = $qb->getQuery();
            $sessionUsers = $query->getResult();

            foreach ($sessionUsers as $sessionUser) {
                $matieres[] = $sessionUser->getSession();
            }
        }

        return $matieres;
    }

    public function getGroupsByMatiere(CourseSession $matiere)
    {
        $groups = array();
        $taggedGroups = $this->getTaggedGroups();

        if (count($taggedGroups) > 0) {
            $qb = $this->em->createQueryBuilder();
            $qb->select('csg')
                ->from('Claroline\CursusBundle\Entity\CourseSessionGroup', 'csg')
                ->where('csg.groupType = :groupType')
                ->andWhere('csg.session = :session')
                ->andWhere('csg.group IN (:groups)')
                ->setParameter('groupType', 0)
                ->setParameter('session', $matiere)
                ->setParameter('groups', $taggedGroups);
            $query = $qb->getQuery();
            $sessionGroups = $query->getResult();

            foreach ($sessionGroups as $sessionGroup) {
                $groups[] = $sessionGroup->getGroup();
            }
        }

        return $groups;
    }

    public function getGroupsByTitulaire(User $titulaire)
    {
        $groups = array();
        $groupeTitulaires = $this->groupeTitulaireRepo->findByUser($titulaire);

        foreach ($groupeTitulaires as $groupeTitulaire) {
            $groups[] = $groupeTitulaire->getGroup();
        }

        return $groups;
    }

    public function getClasseByEleve(User $eleve)
    {
        $group = null;
        $taggedGroups = $this->getTaggedGroups();

        if (count($taggedGroups) > 0) {
            $userGroups = $eleve->getGroups();

            foreach ($userGroups as $userGroup) {

                if (in_array($userGroup, $taggedGroups)) {
                    $group = $userGroup;
                    break;
                }
            }
        }

        return $group;
    }

    public function getAllPointDivers()
    {
        return $this->pointDiversRepo->findAll();
    }
}
