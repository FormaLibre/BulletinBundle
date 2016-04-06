<?php

namespace FormaLibre\BulletinBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CursusBundle\Entity\CourseSession;
use Doctrine\ORM\EntityManager;
use FormaLibre\BulletinBundle\Entity\MatiereOptions;
use FormaLibre\BulletinBundle\Entity\Periode;
use FormaLibre\BulletinBundle\Entity\LockStatus;
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
    private $pagerFactory;
    private $platformConfigHandler;

    private $groupeTitulaireRepo;
    private $matiereOptionsRepo;
    private $pempRepo;
    private $pepdpRepo;
    private $pointDiversRepo;
    private $periodeRepo;

    /**
     * @DI\InjectParams({
     *     "em"                    = @DI\Inject("doctrine.orm.entity_manager"),
     *     "eventDispatcher"       = @DI\Inject("claroline.event.event_dispatcher"),
     *     "om"                    = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory"          = @DI\Inject("claroline.pager.pager_factory"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(
        EntityManager $em,
        StrictDispatcher $eventDispatcher,
        ObjectManager $om,
        PagerFactory $pagerFactory,
        PlatformConfigurationHandler $platformConfigHandler
    )
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;
        $this->platformConfigHandler = $platformConfigHandler;

        $this->groupeTitulaireRepo = $om->getRepository('FormaLibreBulletinBundle:GroupeTitulaire');
        $this->matiereOptionsRepo = $om->getRepository('FormaLibreBulletinBundle:MatiereOptions');
        $this->pepdpRepo = $om->getRepository('FormaLibreBulletinBundle:PeriodeElevePointDiversPoint');
        $this->pempRepo = $om->getRepository('FormaLibreBulletinBundle:PeriodeEleveMatierePoint');
        $this->pointDiversRepo = $om->getRepository('FormaLibreBulletinBundle:PointDivers');
        $this->lockStatusRepo = $om->getRepository('FormaLibreBulletinBundle:LockStatus');
        $this->periodeRepo = $om->getRepository('FormaLibreBulletinBundle:Periode');
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
        $options = array();
        $coefficient = $periode->getCoefficient();
        $sessions = $periode->getCourseSessions();
        $allMatieresOptions = $this->getMatiereOptionsBySessions($sessions);

        foreach ($allMatieresOptions as $matiereOptions) {
            $matiereId = $matiereOptions->getMatiere()->getId();
            $totalMatiere = $matiereOptions->getTotal();
            $total = empty($totalMatiere) ?
                null :
                ceil($coefficient * $totalMatiere);
            $options[$matiereId] = array();
            $options[$matiereId]['total'] = $total;
            $options[$matiereId]['position'] = $matiereOptions->getPosition();
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

        $optionsDivers = array();
        $pointsDivers = $periode->getPointDivers();

        foreach ($pointsDivers as $divers) {
            $pointDiversId = $divers->getId();
            $totalDivers = $divers->getTotal();
            $total = empty($totalDivers) || !$divers->getWithTotal() ?
                null :
                ceil($coefficient * $totalDivers);
            $optionsDivers[$pointDiversId] = array();
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
            $lockStatus->setLockStatus(false);
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
//        $matieresDatas = array();
        $userMatieresDatas = array();
        $allMatieresDatas = $this->getAllMatieresDatas();
        $allMatieres = $allMatieresDatas['matieres'];
        $periodesMatieres = $allMatieresDatas['periodesMatieres'];
        $userMatieres = $this->getAllUserMatieres($user, $allMatieres);
        $userPeriodesDatas = $this->getUserPeriodesDatasFromMatieres($userMatieres, $periodesMatieres);
        $userMatieresPeriodes = $userPeriodesDatas['matieresPeriodes'];
        $matiereOptions = $this->getMatiereOptionsByMatieres($userMatieres);

        foreach ($matiereOptions as $options) {
            $matiere = $options->getMatiere();
            $total = $options->getTotal();
            $position = $options->getPosition();
            $matiereId = $matiere->getId();
            $matiereName = $matiere->getCourseTitle();

            $datas = array(
                'matiereId' => $matiereId,
                'matiereName' => $matiereName,
                'total' => $total,
                'position' => $position,
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

                $periodes[$periodeId] = array(
                    'id' => $periodeId,
                    'name' => $periodeName,
                    'onlyPoint' => $periodeOnlyPoint,
                    'degre' => $periodeDegre,
                    'annee' => $periodeAnnee,
                    'coefficient' => $periodeCoefficient,
                    'pointsDivers' => array()
                );
                $matieresPeriodes[$matiereId][$periodeId] = array(
                    'id' => $periodeId,
                    'name' => $periodeName,
                    'onlyPoint' => $periodeOnlyPoint,
                    'degre' => $periodeDegre,
                    'annee' => $periodeAnnee,
                    'coefficient' => $periodeCoefficient
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
                }
            }
        }

        return array('periodes' => $periodes, 'matieresPeriodes' => $matieresPeriodes);
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

    public function updatePoints(array $pemps, array $pepdps, array $pointsDatas, array $pointsDiversDatas)
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
        $this->om->endFlushSuite();

        return array('points' => $points, 'pointsDivers' => $pointsDivers);
    }
}
