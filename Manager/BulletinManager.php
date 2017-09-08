<?php

namespace FormaLibre\BulletinBundle\Manager;

use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CursusBundle\Entity\CourseSession;
use Doctrine\ORM\EntityManager;
use FormaLibre\BulletinBundle\Entity\EleveMatiereOptions;
use FormaLibre\BulletinBundle\Entity\Periode;
use FormaLibre\BulletinBundle\Entity\LockStatus;
use FormaLibre\BulletinBundle\Entity\PeriodeEleveMatierePoint;
use FormaLibre\BulletinBundle\Entity\PeriodeElevePointDiversPoint;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RouterInterface;

/**
 * @DI\Service("formalibre.manager.bulletin_manager")
 */
class BulletinManager
{
    use LoggableTrait;

    private $em;
    private $eventDispatcher;
    private $fileSystem;
    private $om;
    private $pagerFactory;
    private $platformConfigHandler;
    private $pdfDir;
    private $pdfGenerator;
    private $router;

    private $groupeTitulaireRepo;
    private $groupRepo;
    private $matiereOptionsRepo;
    private $pempRepo;
    private $pepdpRepo;
    private $pointDiversRepo;
    private $periodeRepo;
    private $pointCodeRepo;
    private $eleveMatiereOptionsRepo;
    private $sessionRepo;
    private $userRepo;

    /**
     * @DI\InjectParams({
     *     "em"                    = @DI\Inject("doctrine.orm.entity_manager"),
     *     "eventDispatcher"       = @DI\Inject("claroline.event.event_dispatcher"),
     *     "fileSystem"            = @DI\Inject("filesystem"),
     *     "om"                    = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory"          = @DI\Inject("claroline.pager.pager_factory"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "pdfDir"                = @DI\Inject("%formalibre.directories.pdf%"),
     *     "pdfGenerator"          = @DI\Inject("knp_snappy.pdf"),
     *     "router"                = @DI\Inject("router")
     * })
     */
    public function __construct(
        EntityManager $em,
        StrictDispatcher $eventDispatcher,
        Filesystem $fileSystem,
        ObjectManager $om,
        PagerFactory $pagerFactory,
        PlatformConfigurationHandler $platformConfigHandler,
        $pdfDir,
        $pdfGenerator,
        RouterInterface $router
    )
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->fileSystem = $fileSystem;
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->pdfDir = $pdfDir;
        $this->pdfGenerator = $pdfGenerator;
        $this->router = $router;

        $this->groupeTitulaireRepo = $om->getRepository('FormaLibreBulletinBundle:GroupeTitulaire');
        $this->groupRepo = $om->getRepository('ClarolineCoreBundle:Group');
        $this->matiereOptionsRepo = $om->getRepository('FormaLibreBulletinBundle:MatiereOptions');
        $this->pepdpRepo = $om->getRepository('FormaLibreBulletinBundle:PeriodeElevePointDiversPoint');
        $this->pempRepo = $om->getRepository('FormaLibreBulletinBundle:PeriodeEleveMatierePoint');
        $this->pointDiversRepo = $om->getRepository('FormaLibreBulletinBundle:PointDivers');
        $this->lockStatusRepo = $om->getRepository('FormaLibreBulletinBundle:LockStatus');
        $this->periodeRepo = $om->getRepository('FormaLibreBulletinBundle:Periode');
        $this->pointCodeRepo = $om->getRepository('FormaLibreBulletinBundle:PointCode');
        $this->eleveMatiereOptionsRepo = $om->getRepository('FormaLibreBulletinBundle:EleveMatiereOptions');
        $this->sessionRepo = $om->getRepository('ClarolineCursusBundle:CourseSession');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
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
            'GenericData',
            array($params)
        );
        $taggedGroups = $event->getResponse();
        $groups = empty($taggedGroups) ? array() : $taggedGroups;

        return $groups;
    }

    public function getUntaggedGroups()
    {
        $untaggedGroups = [];
        $classIds = $this->getTaggedGroupIds();
        $groups = $this->groupRepo->findBy([], ['name' => 'ASC']);

        foreach ($groups as $group) {
            if (!in_array($group->getId(),$classIds)) {
                $untaggedGroups[] = $group;
            }
        }

        return $untaggedGroups;
    }

    public function getTaggedGroupIds()
    {
        $groupIds = array();
        $groups = $this->getTaggedGroups();

        foreach ($groups as $group) {
            $groupIds[] = $group->getId();
        }

        return $groupIds;
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

    public function getGroupsByPeriode(Periode $periode)
    {
        $matieres = $periode->getMatieres();

        return $this->getGroupsByMatieres($matieres);
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
                $group = $sessionGroup->getGroup();
                $groupId = $group->getId();

                if (!isset($groups[$groupId])) {
                    $groups[$groupId] = $group;
                }
            }
        }

        return $groups;
    }

    public function getGroupsByMatieres(array $matieres)
    {
        $groups = array();

        if (count($matieres) > 0) {
            $taggedGroups = $this->getTaggedGroups();

            if (count($taggedGroups) > 0) {
                $qb = $this->em->createQueryBuilder();
                $qb->select('csg')
                    ->from('Claroline\CursusBundle\Entity\CourseSessionGroup', 'csg')
                    ->where('csg.groupType = :groupType')
                    ->andWhere('csg.session IN (:sessions)')
                    ->andWhere('csg.group IN (:groups)')
                    ->setParameter('groupType', 0)
                    ->setParameter('sessions', $matieres)
                    ->setParameter('groups', $taggedGroups);
                $query = $qb->getQuery();
                $sessionGroups = $query->getResult();

                foreach ($sessionGroups as $sessionGroup) {
                    $group = $sessionGroup->getGroup();
                    $groupId = $group->getId();

                    if (!isset($groups[$groupId])) {
                        $groups[$groupId] = $group;
                    }
                }
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
        $taggedGroupIds = $this->getTaggedGroupIds();

        if (count($taggedGroupIds) > 0) {
            $userGroups = $eleve->getGroups();

            foreach ($userGroups as $userGroup) {
                $groupId = $userGroup->getId();

                if (in_array($groupId, $taggedGroupIds)) {
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

    public function getPempByPeriodeAndUserAndMatiere(Periode $periode, User $eleve, CourseSession $matiere)
    {
        $pemp = $this->pempRepo->findPeriodeMatiereEleve($periode, $eleve, $matiere);

        if (is_null($pemp)) {
            $defaultPointCode = $this->getDefaultPointCode();
            $defaultCode = is_null($defaultPointCode) ? null : $defaultPointCode->getCode();
            $coefficient = $periode->getCoefficient();
            $totalMatiere = $matiere->getTotal();
            $total = empty($totalMatiere) ? null : ceil($coefficient * $totalMatiere);
            $pemp = new PeriodeEleveMatierePoint();
            $pemp->setEleve($eleve);
            $pemp->setMatiere($matiere);
            $pemp->setTotal($total);
            $pemp->setPoint($defaultCode);
            $pemp->setPeriode($periode);
            $pemp->setPosition($matiere->getDisplayOrder());
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
        $defaultPointCode = $this->getDefaultPointCode();
        $defaultCode = is_null($defaultPointCode) ? null : $defaultPointCode->getCode();

        foreach ($pemps as $pemp) {
            $matiereIds[] = $pemp->getMatiere()->getId();
        }
        $this->om->startFlushSuite();

        foreach ($matieres as $matiere) {
            $matiereId = $matiere->getId();
            $coefficient = $periode->getCoefficient();
            $totalMatiere = $matiere->getTotal();
            $total = empty($totalMatiere) ? null : ceil($coefficient * $totalMatiere);

            if (!in_array($matiereId, $matiereIds)) {
                $pemp = new PeriodeEleveMatierePoint();
                $pemp->setEleve($eleve);
                $pemp->setMatiere($matiere);
                $pemp->setTotal($total);
                $pemp->setPoint($defaultCode);
                $pemp->setPeriode($periode);
                $pemp->setPosition($matiere->getDisplayOrder());
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

    public function getMatiereOptionsBySessions(array $sessions)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('mo')
            ->from('FormaLibre\BulletinBundle\Entity\MatiereOptions', 'mo')
            ->join('mo.matiere', 'session')
            ->where('session IN (:sessions)')
            ->setParameter('sessions', $sessions);

        return $qb->getQuery()->getResult();
    }

    public function getAllMatieresOptions($count = false, $page = null, $limit = null)
    {
        $qb = $this->em->createQueryBuilder();
        $count ? $qb->select('count(mo)'): $qb->select('mo');
        $qb->from('FormaLibre\BulletinBundle\Entity\MatiereOptions', 'mo');
        $query = $qb->getQuery();

        if ($limit) {
            $query->setMaxResults($limit);
            $query->setFirstResult($page * $limit);
        }

        return $count ? $query->getSingleScalarResult(): $query->getResult();
    }

    public function searchSessions($searches = array(), $count = false, $page = null, $limit = null)
    {
        $qb = $this->em->createQueryBuilder();
        $count ? $qb->select('count(cs)'): $qb->select('cs');
        $qb->from('Claroline\CursusBundle\Entity\CourseSession', 'cs')->join('cs.course', 'c');
        $courseProperties = array('title', 'code');
        $sessionProperties = array('name');

        foreach ($searches as $key => $search) {
            foreach ($search as $id => $el) {
                if (in_array($key, $courseProperties)) {
                    $qb->andWhere("UPPER (c.{$key}) LIKE :{$key}{$id}");
                    $qb->setParameter($key . $id, '%' . strtoupper($el) . '%');
                } elseif (in_array($key, $sessionProperties)) {
                    $qb->andWhere("UPPER (cs.{$key}) LIKE :{$key}{$id}");
                    $qb->setParameter($key . $id, '%' . strtoupper($el) . '%');
                }
            }
        }
        $query = $qb->getQuery();

        if ($limit) {
            $query->setMaxResults($limit);
            $query->setFirstResult($page * $limit);
        }

        return $count ? $query->getSingleScalarResult(): $query->getResult();
    }

    public function searchMatieresOptions($searches = array(), $count = false, $page = null, $limit = null)
    {
        $qb = $this->em->createQueryBuilder();
        $count ? $qb->select('count(mo)'): $qb->select('mo');
        $qb->from('FormaLibre\BulletinBundle\Entity\MatiereOptions', 'mo')
            ->join('mo.matiere', 'cs')
            ->join('cs.course', 'c');

        $courseProperties = array('title', 'code');
        $sessionProperties = array('name');

        foreach ($searches as $key => $search) {
            foreach ($search as $id => $el) {
                if (in_array($key, $courseProperties)) {
                    $qb->andWhere("UPPER (c.{$key}) LIKE :{$key}{$id}");
                    $qb->setParameter($key . $id, '%' . strtoupper($el) . '%');
                } elseif (in_array($key, $sessionProperties)) {
                    $qb->andWhere("UPPER (cs.{$key}) LIKE :{$key}{$id}");
                    $qb->setParameter($key . $id, '%' . strtoupper($el) . '%');
                }
            }
        }

        $query = $qb->getQuery();

        if ($limit) {
            $query->setMaxResults($limit);
            $query->setFirstResult($page * $limit);
        }

        return $count ? $query->getSingleScalarResult(): $query->getResult();
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
                ->join('csu.session', 's')
                ->where('csu.user = :user')
                ->andWhere('csu.userType = :userType')
                ->andWhere('s IN (:sessions)')
                ->setParameter('user', $eleve)
                ->setParameter('userType', 0)
                ->setParameter('sessions', $matieres)
                ->orderBy('s.displayOrder', 'ASC');
            $query = $qb->getQuery();
            $sessionUsers = $query->getResult();

            foreach ($sessionUsers as $sessionUser) {
                $eleveMatieres[] = $sessionUser->getSession();
            }
        }

        return $eleveMatieres;
    }

    public function getPempsByPeriode(Periode $periode)
    {
        return $this->pempRepo->findByPeriode($periode);
    }

    public function getPepdpsByPeriode(Periode $periode)
    {
        return $this->pepdpRepo->findByPeriode($periode);
    }

    //this method should not exist
    //totaux et positions devraient être récupérés depuis la matière et pas les pemps
    public function refresh(Periode $periode)
    {
        $options = [];
        $coefficient = $periode->getCoefficient();
        $sessions = $periode->getCourseSessions();

        foreach ($sessions as $session) {
            $matiereId = $session->getId();
            $totalMatiere = $session->getTotal();
            $total = empty($totalMatiere) ? null : ceil($coefficient * $totalMatiere);
            $options[$matiereId] = [];
            $options[$matiereId]['total'] = $total;
            $options[$matiereId]['position'] = $session->getDisplayOrder();
        }

        $pemps = $this->getPempsByPeriode($periode);
        $this->om->startFlushSuite();

        foreach ($pemps as $pemp) {
            $matiereId = $pemp->getMatiere()->getId();

            if (isset($options[$matiereId])) {
                $pemp->setTotal($options[$matiereId]['total']);
                $pemp->setPosition($options[$matiereId]['position']);
                $this->om->persist($pemp);
            }
        }
        $this->om->endFlushSuite();

        $optionsDivers = [];
        $pointsDivers = $periode->getPointDivers();

        foreach ($pointsDivers as $divers) {
            $pointDiversId = $divers->getId();
            $totalDivers = $divers->getTotal();
            $total = empty($totalDivers) || !$divers->getWithTotal() ? null : ceil($coefficient * $totalDivers);
            $optionsDivers[$pointDiversId] = [];
            $optionsDivers[$pointDiversId]['total'] = $total;
            $optionsDivers[$pointDiversId]['position'] = $divers->getPosition();
        }
        $pepdps = $this->getPepdpsByPeriode($periode);
        $this->om->startFlushSuite();

        foreach ($pepdps as $pepdp) {
            $diversId = $pepdp->getDivers()->getId();

            if (isset($optionsDivers[$diversId])) {
                $pepdp->setTotal($optionsDivers[$diversId]['total']);
                $pepdp->setPosition($optionsDivers[$diversId]['position']);
                $this->om->persist($pepdp);
            }
        }
        $this->om->endFlushSuite();
    }

    public function deletePemp(PeriodeEleveMatierePoint $pemp)
    {
        $this->om->remove($pemp);
        $this->om->flush();
    }

    public function searchAvailableSessions($searches, $count = false, $page = null, $limit = null)
    {
        $status = array(CourseSession::SESSION_NOT_STARTED, CourseSession::SESSION_OPEN);
        $courseProperties = array('code', 'title');
        $sessionProperties = array('name');

        $qb = $this->em->createQueryBuilder();
        $count ? $qb->select('count(cs)'): $qb->select('cs');
        $qb->from('Claroline\CursusBundle\Entity\CourseSession', 'cs')
            ->join('cs.course', 'c')
            ->where('cs.sessionStatus IN (:status)')
            ->setParameter('status', $status)
            ->orderBy('c.title', 'ASC');

        foreach ($searches as $key => $search) {
            foreach ($search as $id => $el) {
                if (in_array($key, $courseProperties)) {
                    $qb->andWhere("UPPER (c.{$key}) LIKE :{$key}{$id}");
                    $qb->setParameter($key . $id, '%' . strtoupper($el) . '%');
                } elseif (in_array($key, $sessionProperties)) {
                    $qb->andWhere("UPPER (cs.{$key}) LIKE :{$key}{$id}");
                    $qb->setParameter($key . $id, '%' . strtoupper($el) . '%');
                }
            }
        }

        $query = $qb->getQuery();

        if ($page && $limit) {
            $query->setMaxResults($limit);
            $query->setFirstResult($page * $limit);
        }

        return $count ? $query->getSingleScalarResult(): $query->getResult();
    }

    public function invertSessionPeriode($periode, $session)
    {
        $periode->invertSession($session);
        $this->om->persist($periode);
        $this->om->flush();
    }

    public function addSessionsToPeriode(array $sessions, Periode $periode)
    {
        foreach ($sessions as $session) {
            $periode->addMatiere($session);
        }

        $this->om->persist($periode);
        $this->om->flush();
    }

    public function removeSessionsFromPeriode(array $sessions, Periode $periode)
    {
        foreach ($sessions as $session) {
            $periode->removeMatiere($session);
        }

        $this->om->persist($periode);
        $this->om->flush();
    }

    public function removePeriode(Periode $periode)
    {
        $this->om->remove($periode);
        $this->om->flush();
    }

    public function getLockStatus(User $user, CourseSession $session, Periode $periode) 
    {
        $lockStatus = $this->lockStatusRepo->findOneBy(array('matiere' => $session, 'periode' => $periode));

        if (!$lockStatus) {
            $lockStatus = new LockStatus();
            $lockStatus->setMatiere($session);
            $lockStatus->setPeriode($periode);
            $lockStatus->setTeacher($user);
            $lockStatus->setLockStatus(true);
            $this->om->persist($lockStatus);
            $this->om->flush();
        }

        return $lockStatus;
    }

    public function searchGroupsUsers($groups = array(), $searches = array())
    {
        return count($groups) === 0 ? array() : $this->periodeRepo->findSearchedGroupsUsers($groups, $searches);
    }

    public function getAllMatieresDatas()
    {
        $matieres = array();
        $periodesDatas = array();
        $periodes = $this->periodeRepo->findAll();

        foreach ($periodes as $periode) {
            $periodeId = $periode->getId();
            $periodeMatieres = $periode->getMatieres();

            foreach ($periodeMatieres as $matiere) {
                $matiereId = $matiere->getId();

                if (!isset($matieres[$matiereId])) {
                    $matieres[$matiereId] = $matiere;
                }

                if (!isset($periodesDatas[$matiereId])) {
                    $periodesDatas[$matiereId] = array();
                }
                $periodesDatas[$matiereId][] = $periode;
            }
        }

        return array ('matieres' => $matieres, 'periodesMatieres' => $periodesDatas);
    }

    public function getAllPeriodesUserMatieresDatas(User $user)
    {
        $userMatieresDatas = array();
        $allMatieresDatas = $this->getAllMatieresDatas();
        $allMatieres = $allMatieresDatas['matieres'];
        $periodesMatieres = $allMatieresDatas['periodesMatieres'];
        $userMatieres = $this->getAllUserMatieres($user, $allMatieres);
        $userPeriodesDatas = $this->getUserPeriodesDatasFromMatieres($userMatieres, $periodesMatieres);
        $userMatieresPeriodes = $userPeriodesDatas['matieresPeriodes'];

        foreach ($userMatieres as $matiere) {
            $total = $matiere->getTotal();
            $position = $matiere->getDisplayOrder();
            $matiereId = $matiere->getId();
            $matiereName = $matiere->getCourseTitle();
            $eleveMatiereOptions = $this->getEleveMatiereOptions($user, $matiere);

            $datas = array(
                'matiereId' => $matiereId,
                'matiereName' => $matiereName,
                'certificated' => $matiere->getCertificated(),
                'total' => $total,
                'position' => $position,
                'optionsId' => $eleveMatiereOptions->getId(),
                'deliberated' => $eleveMatiereOptions->isDeliberated(),
                'options' => $eleveMatiereOptions->getOptions(),
                'periodes' => isset($userMatieresPeriodes[$matiereId]) ? $userMatieresPeriodes[$matiereId] : array()
            );
            $userMatieresDatas[$matiereId] = $datas;
        }

        return array('periodesDatas' => $userPeriodesDatas, 'userMatieresDatas' => $userMatieresDatas);
    }

    public function getAllUserMatieres(User $user, array $allMatieres)
    {
        $matieres = array();
        $sessionsUsers = count($allMatieres) > 0 ?
            $this->matiereOptionsRepo->findAllSessionsUsersFromSessions($user, $allMatieres) :
            array();

        foreach ($sessionsUsers as $sessionUser){
            $matiere = $sessionUser->getSession();
            $matiereId = $matiere->getId();
            $matieres[$matiereId] = $matiere;
        }

        return $matieres;
    }

    public function getMatiereOptionsByMatieres(array $matieres)
    {
        return $this->matiereOptionsRepo->findMatiereOptionsByMatieres($matieres);
    }

    private function getUserPeriodesDatasFromMatieres(array $matieres, array $periodesDatas)
    {
        $periodes = array();
        $matieresPeriodes = array();
        $pointsDiversDatas = array();

        foreach ($matieres as $matiere) {
            $matiereId = $matiere->getId();
            $matierePeriodes = isset($periodesDatas[$matiereId]) ? $periodesDatas[$matiereId] : array();
            $matieresPeriodes[$matiereId] = array();

            foreach ($matierePeriodes as $periode) {
                $periodeId = $periode->getId();
                $periodeName = $periode->getName();
                $periodeOnlyPoint = $periode->getOnlyPoint();
                $periodeDegre = $periode->getDegre();
                $periodeAnnee = $periode->getAnnee();
                $periodeCoefficient = $periode->getCoefficient();
                $pointsDivers = $periode->getPointDivers();
                $periodeSet = $periode->getPeriodeSet();
                $template = $periode->getTemplate();

                $periodes[$periodeId] = array(
                    'id' => $periodeId,
                    'name' => $periodeName,
                    'onlyPoint' => $periodeOnlyPoint,
                    'degre' => $periodeDegre,
                    'annee' => $periodeAnnee,
                    'coefficient' => $periodeCoefficient,
                    'pointsDivers' => array(),
                    'periodeSet' => $periodeSet,
                    'template' => $template
                );
                $matieresPeriodes[$matiereId][$periodeId] = array(
                    'id' => $periodeId,
                    'name' => $periodeName,
                    'onlyPoint' => $periodeOnlyPoint,
                    'degre' => $periodeDegre,
                    'annee' => $periodeAnnee,
                    'coefficient' => $periodeCoefficient,
                    'periodeSet' => $periodeSet,
                    'template' => $template
                );

                foreach($pointsDivers as $divers) {
                    $diversId = $divers->getId();
                    $periodes[$periodeId]['pointsDivers'][$diversId] = array(
                        'id' => $diversId,
                        'name' => $divers->getName(),
                        'officialName' => $divers->getOfficialName(),
                        'withTotal' => $divers->getWithTotal(),
                        'total' => $divers->getTotal(),
                        'position' => $divers->getPosition()
                    );

                    if (!isset($pointsDiversDatas[$diversId])) {
                        $pointsDiversDatas[$diversId] = array(
                            'id' => $diversId,
                            'name' => $divers->getName(),
                            'officialName' => $divers->getOfficialName(),
                            'withTotal' => $divers->getWithTotal(),
                            'total' => $divers->getTotal(),
                            'position' => $divers->getPosition()
                        );
                    }
                }
            }
        }

        return array('periodes' => $periodes, 'matieresPeriodes' => $matieresPeriodes, 'pointsDiversDatas' => $pointsDiversDatas);
    }

    public function getPeriodesDatasByUser(User $user)
    {
        $allMatieresDatas = $this->getAllMatieresDatas();
        $allMatieres = $allMatieresDatas['matieres'];
        $periodesMatieres = $allMatieresDatas['periodesMatieres'];
        $userMatieres = $this->getAllUserMatieres($user, $allMatieres);
        $userPeriodesDatas = $this->getUserPeriodesDatasFromMatieres($userMatieres, $periodesMatieres);

        return $userPeriodesDatas['periodes'];
    }

    public function getAllUserPoints(User $user)
    {
        return $this->pempRepo->findByEleve($user);
    }

    public function getAllUserPointsDivers(User $user)
    {
        return $this->pepdpRepo->findByEleve($user);
    }

    public function getPempsByUserAndIds(User $user, array $ids = array())
    {
        return count($ids) > 0 ? $this->pempRepo->findPempsByUserAndIds($user, $ids) : array();
    }

    public function getPepdpsByUserAndIds(User $user, array $ids = array())
    {
        return count($ids) > 0 ? $this->pepdpRepo->findPepdpsByUserAndIds($user, $ids) : array();
    }

    public function getEleveMatiereOptionsByUserAndIds(User $user, array $ids = array())
    {
        return count($ids) > 0 ? $this->eleveMatiereOptionsRepo->findEleveMatiereOptionsByUserAndIds($user, $ids) : array();
    }

    public function getDefaultCode()
    {
        $defaultCodes = $this->pointCodeRepo->findBy(['isDefaultValue' => true]);

        return count($defaultCodes) > 0 ? $defaultCodes[0]->getCode() : null;
    }

    public function truncate($value, $nbDecimals = 2)
    {
        $strValue = strval($value);
        $parts = explode('.', $strValue);

        if (count($parts) < 2 || strlen($parts[1]) <= $nbDecimals) {
            return $value;
        } else {
            $newDecimalPart = substr($parts[1], 0, $nbDecimals);

            return floatval($parts[0].'.'.$newDecimalPart);
        }
    }

    public function updateMatierePoints(array $pointsData)
    {
        $pemps = [];
        $defaultCode = $this->getDefaultCode();
        $this->om->startFlushSuite();

        foreach ($pointsData as $pointData) {
            $pemp = $this->pempRepo->findOneById($pointData['id']);
            $periode = $pemp->getPeriode();
            $point = isset($pointData['point']) && (is_int($pointData['point']) || is_float($pointData['point'])) ?
                $this->truncate($pointData['point']) :
                $defaultCode;
            $pemp->setPoint($point);

            if (!$periode->getOnlyPoint()) {
                $presence = isset($pointData['presence']) && (is_int($pointData['presence']) || is_float($pointData['presence'])) ?
                    $this->truncate($pointData['presence']) :
                    $defaultCode;
                $comportement = isset($pointData['comportement']) && (is_int($pointData['comportement']) || is_float($pointData['comportement'])) ?
                    $this->truncate($pointData['comportement']) :
                    $defaultCode;
                $pemp->setPresence($presence);
                $pemp->setComportement($comportement);
            }
            $this->om->persist($pemp);
            $pemps[] = $pemp;
        }
        $this->om->endFlushSuite();

        return $pemps;
    }

    public function updatePointDiversPoints(array $pointsData)
    {
        $pepdps = [];
        $this->om->startFlushSuite();

        foreach ($pointsData as $pointData) {
            $pepdp = $this->pepdpRepo->findOneById($pointData['id']);
            $point = isset($pointData['point']) && (is_int($pointData['point']) || is_float($pointData['point'])) ?
                $this->truncate($pointData['point']) :
                null;
            $pepdp->setPoint($point);
            $this->om->persist($pepdp);
            $pepdps[] = $pepdp;
        }
        $this->om->endFlushSuite();

        return $pepdps;
    }

    public function updatePoints(array $pemps, array $pepdps, array $eleveMatieresOptions, array $pointsDatas, array $pointsDiversDatas, array $delibaratedDatas)
    {
        $this->om->startFlushSuite();
        $points = array();
        $pointsDivers = array();

        foreach ($pemps as $pemp) {
            $id = $pemp->getId();

            if (isset($pointsDatas[$id])) {
                $pemp->setPoint(floatval($pointsDatas[$id]));
                $points[$id] = $pemp->getPoint();
            }
        }
        foreach ($pepdps as $pepdp) {
            $id = $pepdp->getId();

            if (isset($pointsDiversDatas[$id])) {
                $pepdp->setPoint(floatval($pointsDiversDatas[$id]));
                $pointsDivers[$id] = $pepdp->getPoint();
            }
        }
        foreach ($eleveMatieresOptions as $emo) {
            $id = $emo->getId();

            if (isset($delibaratedDatas[$id])) {
                $deliberated = $delibaratedDatas[$id] === 'true';
                $emo->setDeliberated($deliberated);
                $this->om->persist($emo);
            }
        }
        $this->om->endFlushSuite();

        return array('points' => $points, 'pointsDivers' => $pointsDivers);
    }

    public function generateMissingPemps(User $user, array $matieresDatas)
    {
        $pemps = array();
        $toFlush = false;
        $defaultPointCode = $this->getDefaultPointCode();
        $defaultCode = is_null($defaultPointCode) ? null : $defaultPointCode->getCode();

        foreach ($matieresDatas as $matiereId => $datas) {
            foreach ($datas['periodes'] as $periodeId => $periodeDatas) {
                if (!isset($periodeDatas['pempId'])) {
                    $matiere = $this->sessionRepo->findOneById($matiereId);
                    $periode = $this->periodeRepo->findOneById($periodeId);

                    if (!is_null($matiere) && !is_null($periode)) {
                        $pemp = new PeriodeEleveMatierePoint();
                        $pemp->setPeriode($periode);
                        $pemp->setEleve($user);
                        $pemp->setMatiere($matiere);
                        $matiereTotal = $matiere->getTotal();
                        $total = !empty($matiereTotal) ? $matiereTotal * $periode->getCoefficient() : null;
                        $pemp->setTotal($total);
                        $pemp->setPoint($defaultCode);
                        $this->om->persist($pemp);
                        $pemps[] = $pemp;
                        $toFlush = true;
                    }
                }
            }
        }

        if($toFlush) {
            $this->om->flush();
        }

        return $pemps;
    }

    public function generateMissingPepdps(User $user, array $periodesDatas)
    {
        $pepdps = array();
        $toFlush = false;

        foreach ($periodesDatas as $periodeId => $datas) {
            foreach ($datas['pointsDivers'] as $pointDiversId => $pointDiversDatas) {
                if (!isset($pointDiversDatas['pepdpId'])) {
                    $pointDivers = $this->pointDiversRepo->findOneById($pointDiversId);
                    $periode = $this->periodeRepo->findOneById($periodeId);

                    if (!is_null($pointDivers) && !is_null($periode)) {
                        $pepdp = new PeriodeElevePointDiversPoint();
                        $pepdp->setPeriode($periode);
                        $pepdp->setEleve($user);
                        $pepdp->setDivers($pointDivers);
                        $pepdp->setPosition($pointDivers->getPosition());
                        $pepdp->setTotal($pointDivers->getTotal());
                        $this->om->persist($pepdp);
                        $pepdps[] = $pepdp;
                        $toFlush = true;
                    }
                }
            }
        }

        if($toFlush) {
            $this->om->flush();
        }

        return $pepdps;
    }

    public function getDefaultPointCode()
    {
        $defaultCodes = $this->getDefaultPointCodes();

        return count($defaultCodes) > 0 ? $defaultCodes[0] : null;
    }

    public function getDefaultPointCodes()
    {
        return $this->pointCodeRepo->findDefaultPointCodes();
    }

    public function getPointCodeByCode($code)
    {
        return $this->pointCodeRepo->findOneByCode($code);
    }

    public function getAllPointCodes()
    {
        return $this->pointCodeRepo->findAll();
    }

    public function getIgnoredCodes()
    {
        $ignoredCodes = array();
        $pointCodes = $this->pointCodeRepo->findIgnoredPointCodes();

        foreach ($pointCodes as $pointCode) {
            $ignoredCodes[] = $pointCode->getCode();
        }

        return $ignoredCodes;
    }

    public function getPublishedPeriodes()
    {
        return $this->periodeRepo->findByPublished(true);
    }

    public function getAllUserPointsDatas(User $user)
    {
        $datas = $this->getAllPeriodesUserMatieresDatas($user);
        $periodesDatas = $datas['periodesDatas'];
        $userMatieresDatas = $datas['userMatieresDatas'];
        $pointsDiversDatas = $periodesDatas['pointsDiversDatas'];
        $pemps = $this->getAllUserPoints($user);
        $pepdps = $this->getAllUserPointsDivers($user);
        $pempsDatas = array();
        $pepdpsDatas = array();
        $totalMatieres = array();
        $totalPeriodes = array();
        $totalPointsDivers = array();
        $totalPeriodeSets = array();
        $finalPercentage = 0;
        $finalPoints = 0;
        $finalTotal = 0;

        foreach ($pemps as $pemp) {
            $pempId = $pemp->getId();
            $point = $pemp->getPoint();
            $matiere = $pemp->getMatiere();
            $periode = $pemp->getPeriode();
            $matiereId = $matiere->getId();
            $periodeId = $periode->getId();

            if (isset($userMatieresDatas[$matiereId]['periodes'][$periodeId])) {
                $userMatieresDatas[$matiereId]['periodes'][$periodeId]['pempId'] = $pempId;
                $userMatieresDatas[$matiereId]['periodes'][$periodeId]['point'] = $point;
                $matiereTotal = $matiere->getTotal();
                $total = !empty($matiereTotal) ? $matiereTotal * $periode->getCoefficient() : null;
                $userMatieresDatas[$matiereId]['periodes'][$periodeId]['total'] = $total;
                $userMatieresDatas[$matiereId]['periodes'][$periodeId]['certificated'] = $matiere->getCertificated();
                $pempsDatas[$pempId] = $point;
            }
        }

        foreach ($pepdps as $pepdp) {
            $pepdpId = $pepdp->getId();
            $point = $pepdp->getPoint();
            $pointDivers = $pepdp->getDivers();
            $pointDiversId = $pointDivers->getId();
            $periode = $pepdp->getPeriode();
            $periodeId = $periode->getId();

            if (isset($periodesDatas['periodes'][$periodeId]['pointsDivers'][$pointDiversId])) {
                $periodesDatas['periodes'][$periodeId]['pointsDivers'][$pointDiversId]['pepdpId'] = $pepdpId;
                $periodesDatas['periodes'][$periodeId]['pointsDivers'][$pointDiversId]['point'] = $point;
                $pepdpsDatas[$pepdpId] = $point;
            }
        }
        $createdPemps = $this->generateMissingPemps($user, $userMatieresDatas);
        $createdPepdps = $this->generateMissingPepdps($user, $periodesDatas['periodes']);

        foreach ($createdPemps as $pemp) {
            $pempId = $pemp->getId();
            $point = $pemp->getPoint();
            $periode = $pemp->getPeriode();
            $matiere = $pemp->getMatiere();
            $periodeId = $periode->getId();
            $matiereId = $matiere->getId();
            $userMatieresDatas[$matiereId]['periodes'][$periodeId]['pempId'] = $pempId;
            $userMatieresDatas[$matiereId]['periodes'][$periodeId]['point'] = $point;
            $matiereTotal = $matiere->getTotal();
            $total = !empty($matiereTotal) ? $matiereTotal * $periode->getCoefficient() : null;
            $userMatieresDatas[$matiereId]['periodes'][$periodeId]['total'] = $total;
            $userMatieresDatas[$matiereId]['periodes'][$periodeId]['certificated'] = $matiere->getCertificated();
            $pempsDatas[$pempId] = $point;
        }

        foreach ($createdPepdps as $pepdp) {
            $pepdpId = $pepdp->getId();
            $point = $pepdp->getPoint();
            $pointDiversId = $pepdp->getDivers()->getId();
            $periodeId = $pepdp->getPeriode()->getId();
            $periodesDatas['periodes'][$periodeId]['pointsDivers'][$pointDiversId]['pepdpId'] = $pepdpId;
            $periodesDatas['periodes'][$periodeId]['pointsDivers'][$pointDiversId]['point'] = $point;
            $pepdpsDatas[$pepdpId] = $point;
        }
        $codes = array();
        $pointCodes = $this->getAllPointCodes();

        foreach ($pointCodes as $pointCode) {
            $code = $pointCode->getCode();
            $codes[$code] = array(
                'code' => $code,
                'info' => $pointCode->getInfo(),
                'shortInfo' => $pointCode->getShortInfo(),
                'isDefaultValue' => $pointCode->getIsDefaultValue(),
                'ignored' => $pointCode->getIgnored()
            );
        }

        foreach ($userMatieresDatas as $matiereId => $matiereDatas) {
            $points = 0;
            $total = 0;
            $periodeSetsDatas = array();

            foreach ($matiereDatas['periodes'] as $datas) {
                $periodeSet = $datas['periodeSet'];

                if (!isset($periodeSetsDatas[$periodeSet])) {
                    $periodeSetsDatas[$periodeSet] = array('point' => 0, 'total' => 0);
                }

                if (!isset($totalPeriodeSets[$periodeSet])) {
                    $totalPeriodeSets[$periodeSet] = array('point' => 0, 'total' => 0);
                }

                if (isset($datas['point']) && isset($datas['total']) && isset($datas['certificated']) && $datas['certificated']) {
                    $ignored = isset($codes[$datas['point']]) && $codes[$datas['point']]['ignored'];

                    if (!$ignored) {
                        $total += $datas['total'];
                        $points += $datas['point'];
                        $finalTotal += $datas['total'];
                        $finalPoints += $datas['point'];
                        $periodeSetsDatas[$periodeSet]['total'] += $datas['total'];
                        $periodeSetsDatas[$periodeSet]['point'] += $datas['point'];
                        $totalPeriodeSets[$periodeSet]['total'] += $datas['total'];
                        $totalPeriodeSets[$periodeSet]['point'] += $datas['point'];
                    }
                }
            }
            $totalMatieres[$matiereId] = array();
            $totalMatieres[$matiereId]['periodeSetsTotal'] = array();

            foreach ($periodeSetsDatas as $periodeSet => $periodeSetsData) {
                if ($periodeSetsData['total'] > 0) {
                    $ratio = $periodeSetsData['total'] / 100;
                    $percentage = round($periodeSetsData['point'] / $ratio, 1);
                    $totalMatieres[$matiereId]['periodeSetsTotal'][$periodeSet]['percentage'] = $percentage;
                    $totalMatieres[$matiereId]['periodeSetsTotal'][$periodeSet]['total'] = $periodeSetsData['total'];
                    $totalMatieres[$matiereId]['periodeSetsTotal'][$periodeSet]['point'] = $periodeSetsData['point'];
                }
            }

            if ($total > 0) {
                $ratio = $total / 100;
                $percentage = round($points / $ratio, 1);
                $totalMatieres[$matiereId]['final'] = $percentage;
            }
        }

        foreach ($totalPeriodeSets as $periodeSet => $totalPeriodeSet) {
            if ($totalPeriodeSet['total'] > 0) {
                $ratio = $totalPeriodeSet['total'] / 100;
                $percentage = round($totalPeriodeSet['point'] / $ratio, 1);
                $totalPeriodeSets[$periodeSet]['percentage'] = $percentage;
            }
        }

        if ($finalTotal > 0) {
            $ratio = $finalTotal / 100;
            $finalPercentage = round($finalPoints / $ratio, 1);
        }

        if (isset($periodesDatas['periodes'])) {
            foreach ($periodesDatas['periodes'] as $periodeId => $periodeDatas) {
                $points = 0;
                $total = 0;

                foreach ($userMatieresDatas as $matiereDatas) {
                    if (isset($matiereDatas['periodes'][$periodeId]) &&
                        isset($matiereDatas['periodes'][$periodeId]['point']) &&
                        isset($matiereDatas['periodes'][$periodeId]['total']) &&
                        $matiereDatas['periodes'][$periodeId]['certificated']) {

                        $point = $matiereDatas['periodes'][$periodeId]['point'];
                        $ignored = isset($codes[$point]) && $codes[$point]['ignored'];

                        if (!$ignored) {
                            $total += $matiereDatas['periodes'][$periodeId]['total'];
                            $points += $point;
                        }
                    }
                }

                if ($total > 0) {
                    $ratio = $total / 100;
                    $percentage = round($points / $ratio, 1);
                    $totalPeriodes[$periodeId] = $percentage;
                }
            }
        }

        foreach ($pointsDiversDatas as $diversDatas) {
            $points = 0;
            $total = 0;
            $pointDiversId = $diversDatas['id'];

            foreach ($periodesDatas['periodes'] as $periodeDatas) {
                if (isset($periodeDatas['pointsDivers'][$pointDiversId])) {
                    if ($periodeDatas['pointsDivers'][$pointDiversId]['withTotal']) {
                        $total += $periodeDatas['pointsDivers'][$pointDiversId]['total'];
                    }
                    $pepdpId = $periodeDatas['pointsDivers'][$pointDiversId]['pepdpId'];

                    if (!empty($pepdpId) && isset($pepdpsDatas[$pepdpId])) {
                        $points += $pepdpsDatas[$pepdpId];
                    }
                }
            }

            if ($total > 0) {
                $ratio = $total / 100;
                $percentage = round($points / $ratio, 1);
                $totalPointsDivers[$pointDiversId] = $percentage;
            } else {
                $totalPointsDivers[$pointDiversId] = $points;
            }
        }
        ksort($userMatieresDatas);
        ksort($periodesDatas['periodes']);

        return array(
            'matieres' => $userMatieresDatas,
            'periodes' => $periodesDatas['periodes'],
            'matieresPeriodes' => $periodesDatas['matieresPeriodes'],
            'nbUserPoints' => count($pemps),
            'nbUserPointsDivers' => count($pepdps),
            'nbCreatedUserPoints' => count($createdPemps),
            'nbCreatedUserPointsDivers' => count($createdPepdps),
            'pemps' => $pempsDatas,
            'pepdps' => $pepdpsDatas,
            'codes' => $codes,
            'totalMatieres' => $totalMatieres,
            'totalPeriodes' => $totalPeriodes,
            'totalPointsDivers' => $totalPointsDivers,
            'finalPercentage' => $finalPercentage,
            'pointsDiversDatas' => $pointsDiversDatas,
            'totalPeriodeSets' => $totalPeriodeSets
        );
    }

    public function getEleveMatiereOptions(User $eleve, CourseSession $matiere)
    {
        $eleveMatiereOptions = $this->getEleveMatiereOptionsByEleveAndMatiere($eleve, $matiere);

        if (is_null($eleveMatiereOptions)) {
            $eleveMatiereOptions = new EleveMatiereOptions();
            $eleveMatiereOptions->setEleve($eleve);
            $eleveMatiereOptions->setMatiere($matiere);
            $this->om->persist($eleveMatiereOptions);
            $this->om->flush();
        }

        return $eleveMatiereOptions;
    }

    public function getEleveMatiereOptionsByEleveAndMatiere(User $eleve, CourseSession $matiere)
    {
        return $this->eleveMatiereOptionsRepo->findEleveMatiereOptionsByEleveAndMatiere($eleve, $matiere);
    }

    public function checkLockStatus(User $user, CourseSession $session, Periode $periode)
    {
        $isLocked = true;
        $locks = $this->lockStatusRepo->findBy(['matiere' => $session, 'periode' => $periode]);

        if (count($locks) === 0) {
            $lockStatus = new LockStatus();
            $lockStatus->setMatiere($session);
            $lockStatus->setPeriode($periode);
            $lockStatus->setTeacher($user);
            $lockStatus->setLockStatus(true);
            $this->om->persist($lockStatus);
            $this->om->flush();
        } else {
            $isLocked = $locks[0]->getLockStatus();
        }

        return $isLocked;
    }

    public function editLockStatus(CourseSession $session, Periode $periode, $lockValue)
    {
        $locks = $this->lockStatusRepo->findBy(['matiere' => $session->getId(), 'periode' => $periode->getId()]);
        $this->om->startFlushSuite();

        foreach ($locks as $lock) {
            $lock->setLockStatus($lockValue);
            $this->om->persist($lock);
        }
        $this->om->endFlushSuite();
    }

    public function switchLockStatus(CourseSession $session, Periode $periode)
    {
        $locks = $this->lockStatusRepo->findBy(['matiere' => $session->getId(), 'periode' => $periode->getId()], ['id' => 'ASC']);

        if (count($locks) > 0) {
            $value = !$locks[0]->getLockStatus();
            $this->om->startFlushSuite();

            foreach ($locks as $lock) {
                $lock->setLockStatus($value);
                $this->om->persist($lock);
            }
            $this->om->endFlushSuite();
        }
    }

    public function archiveAllBulletins()
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->log('Archiving all bulletins...');
        $now = new \DateTime();
        $year = $now->format('Y');
        $archivesDir = $this->pdfDir.'archives';
        $baseDir = $archivesDir.$ds.$year.$ds;

        $periodes = $this->periodeRepo->findAll();

        foreach ($periodes as $periode) {
            $groups = $this->getGroupsByPeriode($periode);

            foreach ($groups as $group) {
                $this->log('Generating PDF for Group ['.$group->getName().'] and Periode ['.$periode->getName().']...');
                $fileName = $group->getName().'-'.$periode->getName().'.pdf';
                $dir = $baseDir.$group->getName().$ds.$fileName;
                $eleves = $this->userRepo->findByGroup($group);
                $elevesUrl = [];

                foreach ($eleves as $eleve){
                    $elevesUrl[] = $this->router->generate(
                        'formalibreBulletinPrintEleve',
                        ['periode' => $periode->getId(), 'eleve' => $eleve->getId()],
                        true
                    );
                }
                $template = $periode->getTemplate();
                $options = ($template === 'CompletePrint' || $template === 'CompletePrintLarge') ?
                    ['orientation' => 'landscape', 'page-size' => 'A3'] :
                    [];

                try {
                    $this->pdfGenerator->generate($elevesUrl, $dir, $options, true);
                } catch (\Exception $e) {
                    $this->log($e->getMessage(), LogLevel::ERROR);
                }
            }
        }
        if (is_dir($archivesDir)) {
            $this->log('Checking directories permissions...');
            $this->fileSystem->chmod($archivesDir, 0775, 0000, true);
        }
        $this->log('Done.');
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }
}
