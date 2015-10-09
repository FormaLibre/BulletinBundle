<?php

namespace FormaLibre\BulletinBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CursusBundle\Entity\CourseSession;
use Doctrine\ORM\EntityManager;
use FormaLibre\BulletinBundle\Entity\MatiereOptions;
use FormaLibre\BulletinBundle\Entity\Periode;
use FormaLibre\BulletinBundle\Entity\PeriodeEleveMatierePoint;
use FormaLibre\BulletinBundle\Entity\PeriodeElevePointDiversPoint;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("formalibre.manager.bulletin_manager")
 */
class BulletinManager
{
    private $em;
    private $eventDispatcher;
    private $om;
    private $platformConfigHandler;

    private $groupeTitulaireRepo;
    private $matiereOptionsRepo;
    private $pempRepo;
    private $pepdpRepo;
    private $pointDiversRepo;

    /**
     * @DI\InjectParams({
     *     "em"                    = @DI\Inject("doctrine.orm.entity_manager"),
     *     "eventDispatcher"       = @DI\Inject("claroline.event.event_dispatcher"),
     *     "om"                    = @DI\Inject("claroline.persistence.object_manager"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(
        EntityManager $em,
        StrictDispatcher $eventDispatcher,
        ObjectManager $om,
        PlatformConfigurationHandler $platformConfigHandler
    )
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->om = $om;
        $this->platformConfigHandler = $platformConfigHandler;

        $this->groupeTitulaireRepo = $om->getRepository('FormaLibreBulletinBundle:GroupeTitulaire');
        $this->matiereOptionsRepo = $om->getRepository('FormaLibreBulletinBundle:MatiereOptions');
        $this->pepdpRepo = $om->getRepository('FormaLibreBulletinBundle:PeriodeElevePointDiversPoint');
        $this->pempRepo = $om->getRepository('FormaLibreBulletinBundle:PeriodeEleveMatierePoint');
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

    public function getAvailableSessions()
    {
        $status = array(CourseSession::SESSION_NOT_STARTED, CourseSession::SESSION_OPEN);

        $qb = $this->em->createQueryBuilder();
        $qb->select('cs')
            ->from('Claroline\CursusBundle\Entity\CourseSession', 'cs')
            ->join('cs.course', 'c')
            ->where('cs.sessionStatus IN (:status)')
            ->setParameter('status', $status)
            ->orderBy('c.title', 'ASC');
        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function getPempByPeriodeAndUserAndMatiere(
        Periode $periode,
        User $eleve,
        CourseSession $matiere
    )
    {
        $pemp = $this->pempRepo->findPeriodeMatiereEleve($periode, $eleve, $matiere);

        if (is_null($pemp)) {
            $matiereOptions = $this->getOptionsByMatiere($matiere);
            $coefficient = $periode->getCoefficient();
            $totalMatiere = $matiereOptions->getTotal();
            $total = empty($totalMatiere) ?
                null :
                ceil($coefficient * $totalMatiere);
            $pemp = new PeriodeEleveMatierePoint();
            $pemp->setEleve($eleve);
            $pemp->setMatiere($matiere);
            $pemp->setTotal($total);
            $pemp->setPeriode($periode);
            $pemp->setPosition($matiereOptions->getPosition());
            $this->om->persist($pemp);
            $this->om->flush();
        }

        return $pemp;
    }

    public function getPempsByEleveAndPeriode(User $eleve, Periode $periode)
    {
        $matieres = $this->getMatieresByEleveAndPeriode($eleve, $periode);
        $pemps = $this->pempRepo->findPeriodeEleveMatiere($eleve, $periode);
        $matiereIds = array();

        foreach ($pemps as $pemp) {
            $matiereIds[] = $pemp->getMatiere()->getId();
        }
        $this->om->startFlushSuite();

        foreach ($matieres as $matiere) {
            $matiereId = $matiere->getId();
            $matiereOptions = $this->getOptionsByMatiere($matiere);
            $coefficient = $periode->getCoefficient();
            $totalMatiere = $matiereOptions->getTotal();
            $total = empty($totalMatiere) ?
                null :
                ceil($coefficient * $totalMatiere);

            if (!in_array($matiereId, $matiereIds)) {
                $pemp = new PeriodeEleveMatierePoint();
                $pemp->setEleve($eleve);
                $pemp->setMatiere($matiere);
                $pemp->setTotal($total);
                $pemp->setPeriode($periode);
                $pemp->setPosition($matiereOptions->getPosition());
                $this->om->persist($pemp);
                $pemps[] = $pemp;
            }
        }
        $this->om->endFlushSuite();

        return $pemps;
    }

    public function getPepdpsByEleveAndPeriode(User $eleve, Periode $periode)
    {
        $pointDivers = $periode->getPointDivers();
        $pepdps = $this->pepdpRepo->findPeriodeElevePointDivers($eleve, $periode);
        $pointDiversIds = array();

        foreach ($pepdps as $pepdp) {
            $pointDiversIds[] = $pepdp->getDivers()->getId();
        }
        $this->om->startFlushSuite();

        foreach ($pointDivers as $pd) {
            $pdId = $pd->getId();
            $coefficient = $periode->getCoefficient();
            $totalPd = $pd->getWithTotal() ? $pd->getTotal() : null;
            $total = empty($totalPd) ?
                null :
                ceil($coefficient * $totalPd);

            if (!in_array($pdId, $pointDiversIds)) {
                $pepdp = new PeriodeElevePointDiversPoint();
                $pepdp->setDivers($pd);
                $pepdp->setEleve($eleve);
                $pepdp->setPeriode($periode);
                $pepdp->setPosition($pd->getPosition());
                $pepdp->setTotal($total);
                $this->om->persist($pepdp);
                $pepdps[] = $pepdp;
            }
        }
        $this->om->endFlushSuite();

        return $pepdps;
    }

    public function getOptionsByMatiere(CourseSession $matiere)
    {
        return $this->matiereOptionsRepo->findOneByMatiere($matiere);
    }

    public function getAllMatieresOptions()
    {
        $matieres = $this->getAvailableSessions();

        if (count($matieres) > 0) {
            $qb = $this->em->createQueryBuilder();
            $qb->select('mo')
                ->from('FormaLibre\BulletinBundle\Entity\MatiereOptions', 'mo')
                ->where('mo.matiere IN (:matieres)')
                ->setParameter('matieres', $matieres);
            $query = $qb->getQuery();
            $matieresOptions = $query->getResult();
            $matiereIds = array();
            
            foreach ($matieresOptions as $matiereOptions) {
                $matiereIds[] = $matiereOptions->getMatiere()->getId();
            }

            foreach ($matieres as $matiere) {
                $matiereId = $matiere->getId();

                if (!in_array($matiereId, $matiereIds)) {
                    $matiereOptions = new MatiereOptions();
                    $matiereOptions->setMatiere($matiere);
                    $this->om->persist($matiereOptions);
                    $matieresOptions[] = $matiereOptions;
                }
            }
            $this->om->flush();
        } else {
            $matieresOptions = array();
        }

        return $matieresOptions;
    }

    public function hasSecondPoint()
    {
        $withSecond = $this->platformConfigHandler->getParameter('bulletin_use_second_point');

        return is_null($withSecond) ? true : $withSecond;
    }

    public function hasThirdPoint()
    {
        $withThird = $this->platformConfigHandler->getParameter('bulletin_use_third_point');

        return is_null($withThird) ? true : $withThird;
    }

    public function getSecondPointName()
    {
        $secondName = $this->platformConfigHandler->getParameter('bulletin_second_point_name');

        return empty($secondName) ? 'Présence' : $secondName;
    }

    public function getThirdPointName()
    {
        $thirdName = $this->platformConfigHandler->getParameter('bulletin_third_point_name');

        return empty($thirdName) ? 'Comportement' : $thirdName;
    }

    public function setBulletinParameter($name, $value)
    {
        $this->platformConfigHandler->setParameter($name, $value);
    }

    public function getMatieresByEleveAndPeriode(User $eleve, Periode $periode)
    {
        $matieres = $periode->getMatieres();
        $eleveMatieres = array();

        if (count($matieres) > 0) {
            $qb = $this->em->createQueryBuilder();
            $qb->select('csu')
                ->from('Claroline\CursusBundle\Entity\CourseSessionUser', 'csu')
                ->where('csu.user = :user')
                ->andWhere('csu.userType = :userType')
                ->andWhere('csu.session IN (:sessions)')
                ->setParameter('user', $eleve)
                ->setParameter('userType', 0)
                ->setParameter('sessions', $matieres);
            $query = $qb->getQuery();
            $sessionUsers = $query->getResult();

            foreach ($sessionUsers as $sessionUser) {
                $eleveMatieres[] = $sessionUser->getSession();
            }
        }

        return $eleveMatieres;
    }
}
