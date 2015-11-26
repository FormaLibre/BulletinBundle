<?php

namespace FormaLibre\BulletinBundle\Controller;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Manager\CursusManager;
use Doctrine\ORM\EntityManager;
use FormaLibre\BulletinBundle\Form\Type\MatiereType;
use FormaLibre\BulletinBundle\Form\Type\PempsType;
use FormaLibre\BulletinBundle\Entity\Pemps;
use FormaLibre\BulletinBundle\Entity\Periode;
use FormaLibre\BulletinBundle\Entity\LockStatus;
use FormaLibre\BulletinBundle\Manager\BulletinManager;
use FormaLibre\BulletinBundle\Manager\TotauxManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BulletinController extends Controller
{
    private $authorization;
    private $bulletinManager;
    private $cursusManager;
    private $em;
    private $om;
    private $roleManager;
    private $toolManager;
    private $totauxManager;
    private $userManager;

    /** @var GroupRepository */
    private $groupRepo;
    /** @var PeriodeElevePointDiversPointRepository */
    private $pemdRepo;
    /** @var PeriodeEleveMatierePointRepository */
    private $pempRepo;
    private $periodeEleveDecisionRepo;
    /** @var PeriodeRepository */
    private $periodeRepo;
    /** @var UserRepository */
    private $userRepo;
    /** @var LockStatusRepository */
    private $lockStatusRepo;

    /**
     * @DI\InjectParams({
     *     "authorization"         = @DI\Inject("security.authorization_checker"),
     *     "bulletinManager"       = @DI\Inject("formalibre.manager.bulletin_manager"),
     *     "cursusManager"         = @DI\Inject("claroline.manager.cursus_manager"),
     *     "em"                    = @DI\Inject("doctrine.orm.entity_manager"),
     *     "om"                    = @DI\Inject("claroline.persistence.object_manager"),
     *     "roleManager"           = @DI\Inject("claroline.manager.role_manager"),
     *     "toolManager"           = @DI\Inject("claroline.manager.tool_manager"),
     *     "totauxManager"         = @DI\Inject("formalibre.manager.totaux_manager"),
     *     "userManager"           = @DI\Inject("claroline.manager.user_manager"),
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        BulletinManager $bulletinManager,
        CursusManager $cursusManager,
        EntityManager $em,
        ObjectManager $om,
        RoleManager $roleManager,
        ToolManager $toolManager,
        TotauxManager $totauxManager,
        UserManager $userManager
    )
    {
        $this->authorization = $authorization;
        $this->bulletinManager = $bulletinManager;
        $this->cursusManager = $cursusManager;
        $this->em = $em;
        $this->om = $om;
        $this->roleManager = $roleManager;
        $this->toolManager = $toolManager;
        $this->totauxManager = $totauxManager;
        $this->userManager = $userManager;

        $this->groupRepo = $om->getRepository('ClarolineCoreBundle:Group');
        $this->pemdRepo = $om->getRepository('FormaLibreBulletinBundle:PeriodeElevePointDiversPoint');
        $this->pempRepo = $om->getRepository('FormaLibreBulletinBundle:PeriodeEleveMatierePoint');
        $this->periodeEleveDecisionRepo = $om->getRepository('FormaLibreBulletinBundle:PeriodeEleveDecision');
        $this->periodeRepo = $om->getRepository('FormaLibreBulletinBundle:Periode');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->lockStatusRepo = $om->getRepository('FormaLibreBulletinBundle:LockStatus');
    }

    /**
     * @EXT\Route("/", name="formalibreBulletinIndex")
     */
    public function indexAction()
    {
        $this->checkOpen();

        $periodes = $this->periodeRepo->findAll();

        $periodeCompleted = array();

        foreach ($periodes as $periode){
            $id = $periode->getId();
            $total = 0;
            $nbComp = 0;

            $pemps = $this->pempRepo->findByPeriode($periode);
            foreach ($pemps as $pemp){
                $total = $total + 3;
                if ($pemp->getPoint() >= 0){
                    $nbComp = $nbComp + 1;
                }
                elseif ($pemp->getPresence() >= 0){
                    $nbComp = $nbComp + 1;
                }
                elseif ($pemp->getComportement() >= 0){
                    $nbComp = $nbComp + 1;
                }
            }
            $pemds = $this->pemdRepo->findByPeriode($periode);
            foreach ($pemds as $pemd){
                $total = $total + 1;
                if ($pemd->getPoint() >= 0){
                    $nbComp = $nbComp + 1;
                }

            }
            if ($total != 0) {$pourcent = $nbComp / $total * 100;}
            else {$pourcent = 0;}

            $periodeCompleted[$id] = number_format($pourcent,0);
        }

        return $this->render('FormaLibreBulletinBundle::BulletinIndex.html.twig', array('periodes' => $periodes, 'periodeCompleted' => $periodeCompleted));
    }

    /**
     * @EXT\Route(
     *     "/periode/{periode}/{group}/list/",
     *     name="formalibreBulletinListEleve",
     *     options = {"expose"=true}
     * )
     *
     * @param Periode $periode
     * @param Group $group
     *
     *@EXT\Template("FormaLibreBulletinBundle::BulletinListEleves.html.twig")
     *
     * @return array|Response
     */
    public function listEleveAction(Periode $periode, Group $group)
    {
        $this->checkOpen();
        $eleves = $this->userRepo->findByGroup($group,true,'lastName','ASC');
        $userDecisions = $this->periodeEleveDecisionRepo->findDecisionsByUsersAndPeriode(
            $eleves,
            $periode
        );
        $decisions = array();

        foreach ($userDecisions as $userDecision) {
            $userId = $userDecision->getUser()->getId();

            if (!isset($decisions[$userId])) {
                $decisions[$userId] = 1;
            } else {
                $decisions[$userId]++;
            }
        }

        return array(
            'periode' => $periode,
            'eleves' => $eleves,
            'group' => $group,
            'decisions' => $decisions
        );
    }

    /**
     * @EXT\Route(
     *     "/periode/{periode}/list/",
     *     name="formalibreBulletinListClasse",
     *     options = {"expose"=true}
     * )
     *
     * @param Periode $periode
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @param User         $user         the user
     *
     *
     * @return array|Response
     */
    public function listClasseAction(Periode $periode, User $user)
    {
        $this->checkOpen();
        $groups = array();
        if ($this->authorization->isGranted('ROLE_BULLETIN_ADMIN')){
            $groups = $this->bulletinManager->getTaggedGroups();

            $content = $this->renderView('FormaLibreBulletinBundle::Admin/BulletinListClasses.html.twig',
                array('periode' => $periode, 'groups' => $groups)
                );
            return new Response($content);
        }

        elseif ($this->authorization->isGranted('ROLE_PROF')){
            $myGroups = $this->bulletinManager->getGroupsByTitulaire($user);
            $datas = $this->bulletinManager->getMatiereGroupsByUserAndPeriode(
                $user,
                $periode
            );
            $content = $this->renderView('FormaLibreBulletinBundle::BulletinListGroups.html.twig',
                array('periode' => $periode, 'datas' => $datas, 'myGroups' => $myGroups)
                );
            return new Response($content);
        }

        else { return $this->redirect('http://google.be');}


    }

    /**
     * @EXT\Route(
     *     "/prof/periode/{periode}/list/",
     *     name="formalibreBulletinListMyGroup",
     *     options = {"expose"=true}
     * )
     *
     * @param Periode $periode
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @param User         $user         the user
     *
     *
     * @return array|Response
     */
    public function listMyGroupAction(Periode $periode, User $user)
    {
        $this->checkOpen();

        if ($this->authorization->isGranted('ROLE_PROF')){
            $myGroups = $this->bulletinManager->getGroupsByTitulaire($user);
            $matieres = $this->bulletinManager->getMatieresByProf($user, $periode);
            $content = $this->renderView(
                'FormaLibreBulletinBundle::BulletinListGroups.html.twig',
                array('periode' => $periode, 'matieres' => $matieres, 'myGroups' => $myGroups)
            );
            return new Response($content);
        }

        throw new AccessDeniedException();


    }

    /**
     * @EXT\Route(
     *     "/prof/periode/{periode}/matiere/{matiere}/list/",
     *     name="formalibreBulletinListEleveProf",
     *     options = {"expose"=true}
     * )
     *
     *
     * @param Periode $periode
     * @param CourseSession $matiere
     *
     *@EXT\Template("FormaLibreBulletinBundle::BulletinListEleves.html.twig")
     *
     * @return array|Response
     */
    public function listEleveProfAction(Periode $periode, CourseSession $matiere)
    {
        $this->checkOpen();
        $editMatiereUrl = $this->generateUrl(
            'formalibreBulletinEditMatiere',
            array(
                'periode' => $periode->getId(),
                'matiere' => $matiere->getId()
            )
        );

        return $this->redirect($editMatiereUrl);
    }

    /**
     * @EXT\Route(
     *     "/periode/{periode}/{eleve}/edit/",
     *     name="formalibreBulletinEditEleve",
     *     options = {"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @param Periode $periode
     * @param User $eleve
     *
     *@EXT\Template("FormaLibreBulletinBundle::Admin/BulletinEdit.html.twig")
     *
     * @return array|Response
     */
    public function editEleveAction(Request $request, Periode $periode, User $eleve, User $user)
    {   
        $matiere = $this->bulletinManager->getMatieresByEleveAndPeriode($eleve, $periode);
        $this->checkOpen();
        $isBulletinAdmin = $this->authorization->isGranted('ROLE_BULLETIN_ADMIN') ||
            $this->authorization->isGranted('ROLE_ADMIN');

        $pemps = $this->bulletinManager->getPempsByEleveAndPeriode($eleve, $periode);
        $pemds = $this->bulletinManager->getPepdpsByEleveAndPeriode($eleve, $periode);

        $pempCollection = new Pemps();
        
        foreach ($pemps as $pemp) {
            $lock = $this->lockStatusRepo->findLockStatus($user, $pemp->getMatiere(), $pemp->getPeriode());
            $pemp->setLocked($lock);
            $pempCollection->getPemps()->add($pemp);
            
        }

        
        $form = $this->createForm(new PempsType(), $pempCollection);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
                
                $list=$form->get('pemps')->getData();
                foreach ($list as $test){
                $actualLockStatus = $this->lockStatusRepo->findLockStatus($user, $test->getMatiere(), $test->getPeriode());
                
                if ($actualLockStatus == true){
                    $this->em->refresh($test);
                }

              }
              $this->em->flush();

            $nextAction = $form->get('saveAndAdd')->isClicked()
                ? 'task_new'
                : $this->generateUrl('formalibreBulletinEditEleve', array('periode' => $periode->getId(), 'eleve' => $eleve->getId()));

                return $this->redirect($nextAction);
        }
        $hasSecondPoint = $this->bulletinManager->hasSecondPoint();
        $hasThirdPoint = $this->bulletinManager->hasThirdPoint();
        $secondPointName = $this->bulletinManager->getSecondPointName();
        $thirdPointName = $this->bulletinManager->getThirdPointName();

        return array(
            'form' => $form->createView(),
            'eleve' => $eleve,
            'periode' => $periode,
            'hasSecondPoint' => $hasSecondPoint,
            'hasThirdPoint' => $hasThirdPoint,
            'secondPointName' => $secondPointName,
            'thirdPointName' => $thirdPointName,
            'isBulletinAdmin' => $isBulletinAdmin,
            'matieres'=> $matiere
        );
    }

    /**
     * @EXT\Route(
     *     "/periode/{periode}/matiere/{matiere}/edit/",
     *     name="formalibreBulletinEditMatiere",
     *     options = {"expose"=true}
     * )
     * @param Periode $periode
     * @param CourseSession $matiere
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::BulletinEditMatiere.html.twig")
     *
     * @return array|Response
     */
    public function editMatiereAction(User $user, Request $request, Periode $periode, CourseSession $matiere)
    {
        $this->checkOpen();
        $eleves = $this->cursusManager->getUsersBySessionAndType($matiere, 0);
        $pempCollection = new Pemps;
        foreach ( $eleves as $eleve){
            $pempCollection->getPemps()->add(
                $this->bulletinManager->getPempByPeriodeAndUserAndMatiere(
                    $periode,
                    $eleve,
                    $matiere
                )
            );
        }

        $lock=$this->lockStatusRepo->findLockStatus($user,$matiere,$periode);
        $form = $this->createForm(new MatiereType($lock) , $pempCollection);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            foreach ($pempCollection as $pemp){
                $this->em->persist($pemp);
            }
            $this->em->flush();

            return $this->redirect($this->generateUrl(
                'formalibreBulletinEditMatiere',
                array(
                    'periode' => $periode->getId(),
                    'matiere' => $matiere->getId()
                )
            ));
        }
        $hasSecondPoint = $this->bulletinManager->hasSecondPoint();
        $hasThirdPoint = $this->bulletinManager->hasThirdPoint();
        $secondPointName = $this->bulletinManager->getSecondPointName();
        $thirdPointName = $this->bulletinManager->getThirdPointName();

        return array(
            'form' => $form->createView(),
            'matiere' => $matiere,
            'periode' => $periode,
            'hasSecondPoint' => $hasSecondPoint,
            'hasThirdPoint' => $hasThirdPoint,
            'secondPointName' => $secondPointName,
            'thirdPointName' => $thirdPointName,
            'eleves' => $eleves,
            'lock' => $lock
        );
    }

    /**
     * @EXT\Route(
     *     "/periode/{periode}/eleve/{eleve}/print/",
     *     name="formalibreBulletinPrintEleve",
     *     options = {"expose"=true}
     * )
     *
     *
     * @param Periode $periode
     * @param User $eleve
     *
     * @return array|Response
     */
    public function printEleveAction(Request $request, Periode $periode, User $eleve)
    {
        $this->checkOpenPrintPdf($request);

        if ($periode->getTemplate() === 'FinalExamPrint') {

            return $this->printFinalExam($periode, $eleve);
        }
        $totaux = [];
        $totauxMatieres = [];
        $recap = 0;

        if (!$periode->getOnlyPoint()){
            $pemps = $this->bulletinManager->getPempsByEleveAndPeriode($eleve, $periode);
            $pemds = $this->bulletinManager->getPepdpsByEleveAndPeriode($eleve, $periode);
            $totaux = $this->totauxManager->getTotalPeriode($periode, $eleve);
            $recap += $totaux['totalPourcentage'];
        } else {
            $pemps = array();
            $pemds = array();

            $periodes = array(1, 2, 3);

            foreach ($periodes as $per){
                $periode = $this->periodeRepo->findOneById($per);
                $pemps[] = $this->bulletinManager->getPempsByEleveAndPeriode($eleve, $periode);
                $pemds[] = $this->bulletinManager->getPepdpsByEleveAndPeriode($eleve, $periode);

                $totaux[] = $this->totauxManager->getTotalPeriode($periode, $eleve);

            }
            $totauxMatieres = $this->totauxManager->getTotalPeriodes($eleve);

            foreach ($totaux as $total) {
                $recap += $total['totalPourcentage'] / 3;
            }
        }

        $template = 'FormaLibreBulletinBundle::Templates/'.$periode->getTemplate().'.html.twig';

        $recap = round($recap, 1);
        $hasSecondPoint = $this->bulletinManager->hasSecondPoint();
        $hasThirdPoint = $this->bulletinManager->hasThirdPoint();
        $secondPointName = $this->bulletinManager->getSecondPointName();
        $thirdPointName = $this->bulletinManager->getThirdPointName();
        $classe = $this->bulletinManager->getClasseByEleve($eleve);
        $isBulletinAdmin = $this->isBulletinAdmin();

        $params = array(
            'pemps' => $pemps,
            'pemds' => $pemds,
            'eleve' => $eleve,
            'periode' => $periode,
            'totaux' => $totaux,
            'totauxMatieres' => $totauxMatieres,
            'recap' => $recap,
            'hasSecondPoint' => $hasSecondPoint,
            'hasThirdPoint' => $hasThirdPoint,
            'secondPointName' => $secondPointName,
            'thirdPointName' => $thirdPointName,
            'classe' => $classe,
            'isBulletinAdmin' => $isBulletinAdmin
        );

        return $this->render($template, $params);
    }


    /**
     * @EXT\Route(
     *     "/periode/{periode}/eleve/{eleve}/matiere/{matiere}/delete",
     *     name="formalibre_bulletin_pemp_delete",
     *     options = {"expose"=true}
     * )
     * @param Periode $periode
     * @param User $eleve
     * @param CourseSession $matiere
     *
     * @return JsonResponse
     */
    public function deletePempAction(Periode $periode, User $eleve, CourseSession $matiere)
    {
        if ($this->authorization->isGranted('ROLE_BULLETIN_ADMIN') ||
            $this->authorization->isGranted('ROLE_ADMIN')) {

            $pemp = $this->bulletinManager->getPempByPeriodeAndUserAndMatiere(
                $periode,
                $eleve,
                $matiere
            );
            $this->bulletinManager->deletePemp($pemp);
        }

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/chart/eleve/{eleve}",
     *     name="formalibreBulletinShowEleveDataChart",
     *     options = {"expose"=true}
     * )
     *
     * @param User $eleve
     *
     * @return Response
     */
    public function showDataChartAction(Request $request, User $eleve)
    {
        $this->checkOpenPrintPdf($request);
        $json = $this->totauxManager->getDataChart($eleve, true);
        $jsonNoCeb = $this->totauxManager->getDataChart($eleve, false);

        return $this->render(
            'FormaLibreBulletinBundle::BulletinShowDataChart.html.twig',
            array('json' => $json, 'jsonNoCeb' => $jsonNoCeb, 'eleve' => $eleve)
        );
    }

    /**
     * @EXT\Route("/user/{user}/bulletinWidget/", name="formalibreBulletinWidget")
     *
     * @param User $user
     *
     */
    public function bulletinWidgetAction(User $user)
    {

        $totauxMatieres = $this->totauxManager->getTotalPeriodesMatiere($user);
        $periodes = $this->periodeRepo->findAll();

        $matCeb = array("Français", "Math", "Néerlandais", "Histoire", "Géographie", "Sciences");
        $cebWithPoints = array();
        $nocebWithPoints = array();

        foreach ($totauxMatieres as $matiereId => $datas) {
            $matiereName = $datas['name'];

            if (in_array($matiereName, $matCeb)) {
                $cebWithPoints[$matiereId] = $datas;
            } else {
                $nocebWithPoints[$matiereId] = $datas;
            }
        }
        $params = array(
            'user' => $user,
            'totauxMatieresCeb' => $cebWithPoints,
            'totauxMatieresNoCeb' => $nocebWithPoints,
            'periodes' => $periodes
        );

        return $this->render('FormaLibreBulletinBundle::BulletinWidget.html.twig', $params);
    }

    /**
     * @EXT\Route(
     *     "/user/{user}/printable/bulletinWidget/",
     *     name="formalibrePrintableBulletinWidget"
     * )
     *
     * @param User $user
     *
     */
    public function printableBulletinWidgetAction(User $user)
    {
        $totauxMatieres = $this->totauxManager->getTotalPeriodesMatiere($user);
        $periodes = $this->periodeRepo->findAll();

        $matCeb = array("Français", "Math", "Néerlandais", "Histoire", "Géographie", "Sciences");
        $cebWithPoints = array();
        $nocebWithPoints = array();

        foreach ($totauxMatieres as $matiereId => $datas) {
            $matiereName = $datas['name'];

            if (in_array($matiereName, $matCeb)) {
                $cebWithPoints[$matiereId] = $datas;
            } else {
                $nocebWithPoints[$matiereId] = $datas;
            }
        }
        $params = array(
            'user' => $user,
            'totauxMatieresCeb' => $cebWithPoints,
            'totauxMatieresNoCeb' => $nocebWithPoints,
            'periodes' => $periodes
        );

        return $this->render('FormaLibreBulletinBundle::printableBulletinWidget.html.twig', $params);
    }

    /**
     * @EXT\Route(
     *     "/user/{user}/bulletinPresenceWidget/",
     *     name="formalibreBulletinPresenceWidget"
     * )
     *
     * @param User $user
     */
    public function bulletinPresenceWidgetAction(User $user)
    {
        $presences = $this->totauxManager->getMoyennePresence($user);

        $params = array('presences' => $presences);

        return $this->render('FormaLibreBulletinBundle::BulletinPresenceWidget.html.twig', $params);
    }

    /**
     * @EXT\Route(
     *     "/user/{user}/bulletinComportementWidget/",
     *     name="formalibreBulletinComportementWidget"
     * )
     *
     * @param User $user
     */
    public function bulletinComportementWidgetAction(User $user)
    {
        $comportements = $this->totauxManager->getMoyenneComportement($user);

        $params = array('comportements' => $comportements);

        return $this->render('FormaLibreBulletinBundle::BulletinComportementWidget.html.twig', $params);
    }

    /**
     * @EXT\Route(
     *     "/user/{user}/bulletinPointsDiversWidget/",
     *     name="formalibreBulletinPointsDiversWidget"
     * )
     *
     * @param User $user
     */
    public function bulletinPointsDiversWidgetAction(User $user)
    {
        $pointsDivers = $this->totauxManager->getMoyennePointsDivers($user);

        $params = array('pointsDivers' => $pointsDivers);

        return $this->render('FormaLibreBulletinBundle::BulletinPointsDiversWidget.html.twig', $params);
    }

    /**
     * @EXT\Route(
     *     "/user/{user}/printable/bulletinPointsDiversWidget/",
     *     name="formalibrePrintableBulletinPointsDiversWidget"
     * )
     *
     * @param User $user
     */
    public function printableBulletinPointsDiversWidgetAction(User $user)
    {
        $pointsDivers = $this->totauxManager->getMoyennePointsDivers($user);

        $params = array('pointsDivers' => $pointsDivers);

        return $this->render('FormaLibreBulletinBundle::printableBulletinPointsDiversWidget.html.twig', $params);
    }

    private function printFinalExam(Periode $periode, User $eleve)
    {
        $totaux = array();
        $recap = 0;
        $periodes = $this->periodeRepo->findAll();
        $pemps = $this->bulletinManager->getPempsByEleveAndPeriode($eleve, $periode);

        foreach ($periodes as $per){
            $periodeId = $per->getId();
            $totaux[$periodeId] = $this->totauxManager->getTotalPeriode($per, $eleve);

        }
        $totauxMatieres = $this->totauxManager->getFinalTotalPeriodes($eleve);

        foreach ($totaux as $total) {

            if (count($periodes) > 0) {
                $recap += $total['totalPourcentage'] / count($periodes);
            }
        }

        $recap = round($recap, 1);
        $userDecisions = $this->periodeEleveDecisionRepo->findBy(
            array('user' => $eleve->getId(), 'periode' => $periode->getId())
        );

        $params = array(
            'pemps' => $pemps,
            'eleve' => $eleve,
            'periode' => $periode,
            'totaux' => $totaux,
            'totauxMatieres' => $totauxMatieres,
            'recap' => $recap,
            'userDecisions' => $userDecisions
        );

        return $this->render('FormaLibreBulletinBundle::Templates/FinalExamPrint.html.twig', $params);
    }

    private function checkOpen()
    {
        if ($this->authorization->isGranted('ROLE_BULLETIN_ADMIN') or $this->authorization->isGranted('ROLE_PROF')) {
            return true;
        }

        throw new AccessDeniedException();
    }

    private function isBulletinAdmin()
    {
        return $this->authorization->isGranted('ROLE_BULLETIN_ADMIN');
    }

    private function checkOpenPrintPdf(Request $request = NULL)
    {
        //$ServerIp =  system("curl -s ipv4.icanhazip.com");

        if ($this->authorization->isGranted('ROLE_BULLETIN_ADMIN') or $this->authorization->isGranted('ROLE_PROF')) {
            return true;
        }
        elseif (!is_null($request) && $request->getClientIp() === '127.0.0.1'){
            return true;
        }

        elseif (!is_null($request) && $request->getClientIp() == '91.121.211.13'){
            return true;
        }

        throw new AccessDeniedException();
    }
    
     /**
     * @EXT\Route(
     *     "/bulletin/lockPoints/periode/{periode}/session/{session}",
     *     name="formalibreBulletinLockPoint",
     *     options = {"expose"=true}
     * )
     *
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})

     */
    public function lockPointsAction(User $user, CourseSession $session, Periode $periode)
    {
        $actualLockStatus = $this->lockStatusRepo->findOneBy(array('teacher'=>$user,
                                                             'matiere'=>$session,
                                                             'periode'=>$periode));
        if (is_null($actualLockStatus))
        {
            $newLockStatus = new LockStatus();
            $newLockStatus -> setMatiere($session);
            $newLockStatus -> setPeriode($periode);
            $newLockStatus -> setTeacher($user);
            $newLockStatus -> setLockStatus(true);
            $this->em->persist($newLockStatus);
            $this->em->flush();
        }
        else
        {
            $this->em->remove($actualLockStatus);
            $this->em->flush();
        }
        
        return new JsonResponse('success', 200);
    }
}

