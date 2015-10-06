<?php

namespace FormaLibre\BulletinBundle\Controller;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FormaLibre\BulletinBundle\Entity\Decision;
use FormaLibre\BulletinBundle\Entity\GroupeTitulaire;
use FormaLibre\BulletinBundle\Entity\Periode;
use FormaLibre\BulletinBundle\Entity\PeriodeEleveDecision;
use FormaLibre\BulletinBundle\Entity\PeriodeEleveMatierePoint;
use FormaLibre\BulletinBundle\Entity\PeriodeElevePointDiversPoint;
use FormaLibre\BulletinBundle\Form\Admin\PeriodeType;
use FormaLibre\BulletinBundle\Form\Admin\DecisionType;
use FormaLibre\BulletinBundle\Form\Admin\GroupeTitulaireType;
use FormaLibre\BulletinBundle\Form\Admin\UserDecisionCreateType;
use FormaLibre\BulletinBundle\Form\Admin\UserDecisionEditType;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\CourseSession;

class BulletinAdminController extends Controller
{
    private $authorization;
    private $toolManager;
    private $roleManager;
    private $userManager;
    /** @var GroupRepository */
    private $groupRepo;
    /** @var UserRepository */
    private $userRepo;
    /** @var MatiereRepository */
    private $matiereRepo;
    /** @var ClasseRepository */
    private $classeRepo;
    /** @var DiversRepository */
    private $diversRepo;
    /** @var PeriodeRepository */
    private $periodeRepo;
    /** @var PeriodeEleveMatierePointRepository */
    private $pempRepo;
    /** @var PeriodeElevePointDiversPointRepository */
    private $pemdRepo;
    private $decisionRepo;
    private $periodeEleveDecisionRepo;
    private $groupeTitulaireRepo;
    private $om;
    private $em;
    /** @var  string */
    private $pdfDir;
    private $formFactory;
    private $request;

    /**
     * @DI\InjectParams({
     *      "authorization"      = @DI\Inject("security.authorization_checker"),
     *      "toolManager"        = @DI\Inject("claroline.manager.tool_manager"),
     *      "roleManager"        = @DI\Inject("claroline.manager.role_manager"),
     *      "userManager"        = @DI\Inject("claroline.manager.user_manager"),
     *      "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *      "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     *      "pdfDir"             = @DI\Inject("%laurent.directories.pdf%"),
     *      "formFactory"        = @DI\Inject("form.factory"),
     *      "requestStack"       = @DI\Inject("request_stack")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ToolManager $toolManager,
        RoleManager $roleManager,
        UserManager $userManager,
        ObjectManager $om,
        EntityManager $em,
        $pdfDir,
        FormFactory $formFactory,
        RequestStack $requestStack
    )
    {
        $this->authorization      = $authorization;
        $this->toolManager        = $toolManager;
        $this->roleManager        = $roleManager;
        $this->userManager        = $userManager;
        $this->pdfDir             = $pdfDir;
        $this->formFactory        = $formFactory;
        $this->request            = $requestStack->getCurrentRequest();

        $this->om                 = $om;
        $this->em                 = $em;
        $this->groupRepo          = $om->getRepository('ClarolineCoreBundle:Group');
        $this->userRepo          = $om->getRepository('ClarolineCoreBundle:User');
        $this->matiereRepo        = $om->getRepository('ClarolineCursusBundle:CourseSession');
//        $this->classeRepo        = $om->getRepository('LaurentSchoolBundle:Classe');
        $this->diversRepo        = $om->getRepository('FormaLibreBulletinBundle:PointDivers');
        $this->periodeRepo        = $om->getRepository('FormaLibreBulletinBundle:Periode');
        $this->pempRepo           = $om->getRepository('FormaLibreBulletinBundle:PeriodeEleveMatierePoint');
        $this->pemdRepo           = $om->getRepository('FormaLibreBulletinBundle:PeriodeElevePointDiversPoint');
        $this->decisionRepo       = $om->getRepository('FormaLibreBulletinBundle:Decision');
        $this->periodeEleveDecisionRepo = $om->getRepository('FormaLibreBulletinBundle:PeriodeEleveDecision');
        $this->groupeTitulaireRepo = $om->getRepository('FormaLibreBulletinBundle:GroupeTitulaire');
    }

    /**
     * @EXT\Route("/admin/", name="formalibreBulletinAdminIndex")
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

        return $this->render('FormaLibreBulletinBundle::Admin/BulletinAdminIndex.html.twig', array('periodes' => $periodes, 'periodeCompleted' => $periodeCompleted));
    }

    /**
     * @EXT\Route(
     *     "/admin/periode/{periode}/group/{group}/pdf/",
     *     name="formalibreBulletinPrintGroupPdf",
     *     options = {"expose"=true}
     * )
     *
     *
     * @param Periode $periode
     * @param Group $group
     *
     *@EXT\Template("FormaLibreBulletinBundle::Admin/BulletinPrintPdf.html.twig")
     *
     */
    public function PrintPdfGroupAction(Periode $periode, Group $group)
    {
        $this->checkOpen();
        $filename = $group->getName(). '-'. date("Y-m-d-H-i-s") . '.pdf';
        $dir = $this->pdfDir . $group->getName() . '/' . $filename;

        $eleves = $this->userRepo->findByGroup($group);
        $elevesUrl = array();
        foreach ($eleves as $eleve){
            $elevesUrl[] = $this->generateUrl('formalibreBulletinPrintEleve', array('periode' => $periode->getId(), 'eleve' => $eleve->getId()), true);
        }

        $this->get('knp_snappy.pdf')->generate($elevesUrl, $dir);

        $headers = array(
            'Content-Type'          => 'application/pdf',
            'Content-Disposition'   => 'attachment; filename="'.$filename.'"'
        );

        return new Response(file_get_contents($dir), 200, $headers);
    }

    /**
     * @EXT\Route(
     *     "/admin/periode/{periode}/eleve/{user}/pdf/",
     *     name="formalibreBulletinPrintElevePdf",
     *     options = {"expose"=true}
     * )
     *
     *
     * @param Periode $periode
     * @param User $user
     *
     *@EXT\Template("FormaLibreBulletinBundle::Admin/BulletinPrintPdf.html.twig")
     *
     */
    public function PrintPdfEleveAction(Periode $periode, User $user)
    {
        $this->checkOpen();

        $classe = $this->classeRepo->findUserClasse($user);
        $filename = $user->getLastName() . $user->getFirstName(). '-'. date("Y-m-d-H-i-s") . '.pdf';
        $dir = $this->pdfDir . $classe->getName() . '/' . $filename;

        $eleveUrl = $this->generateUrl('formalibreBulletinPrintEleve', array('periode' => $periode->getId(), 'eleve' => $user->getId()), true);


        $this->get('knp_snappy.pdf')->generate($eleveUrl, $dir);

        $headers = array(
            'Content-Type'          => 'application/pdf',
            'Content-Disposition'   => 'attachment; filename="'.$filename.'"'
        );

        return new Response(file_get_contents($dir), 200, $headers);

    }


    /**
     * @EXT\Route("/admin/periode/add", name="formalibreBulletinPeriodeAdd", options = {"expose"=true})
     *
     * @EXT\Template("FormaLibreBulletinBundle::Admin/PeriodeForm.html.twig")
     */
    public function adminSchoolPeriodeAddAction(Request $request)
    {
        $this->checkOpen();
        $periode = new Periode();
        $sessions = $this->matiereRepo->findBySessionStatus(CourseSession::SESSION_OPEN);
        $datas = array();

        foreach ($sessions as $session) {
            $course = $session->getCourse();
            $courseName = $course->getTitle() . ' [' . $course->getCode() . ']';

            if (!isset($datas[$courseName])) {
                $datas[$courseName] = array();
            }
            $datas[$courseName][$session->getId()] = $session;
        }

        $form = $this->createForm(new PeriodeType($datas), $periode);

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->em->persist($periode);
                $this->em->flush();
            }
        }
        return array('form' => $form->createView(), 'action' => $this->generateUrl('formalibreBulletinPeriodeAdd'));
    }

    /**
     * @EXT\Route(
     *     "/admin/periode/{periode}/edit",
     *     name="formalibreBulletinPeriodeEdit",
     *     options = {"expose"=true}
     * )
     *
     * @param Periode $periode
     * @EXT\Template("FormaLibreBulletinBundle::Admin/PeriodeForm.html.twig")
     */
    public function adminSchoolPeriodeEditAction(Request $request, Periode $periode)
    {
        $this->checkOpen();
        $sessions = $this->matiereRepo->findBySessionStatus(CourseSession::SESSION_OPEN);
        $datas = array();

        foreach ($sessions as $session) {
            $course = $session->getCourse();
            $courseName = $course->getTitle() . ' [' . $course->getCode() . ']';

            if (!isset($datas[$courseName])) {
                $datas[$courseName] = array();
            }
            $datas[$courseName][$session->getId()] = $session;
        }

        $form = $this->createForm(new PeriodeType($datas), $periode);

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->em->persist($periode);
                $this->em->flush();
            }
        }
        return array('form' => $form->createView(), 'action' => $this->generateUrl('formalibreBulletinPeriodeEdit', array('periode' => $periode->getId())));
    }

    /**
     * @EXT\Route(
     *     "/admin/decisions/list",
     *     name="formalibreBulletinDecisionsList",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/decisionsList.html.twig")
     */
    public function decisionsListAction()
    {
        $this->checkOpen();
        $decisions = $this->decisionRepo->findAll();

        return array('decisions' => $decisions);
    }

    /**
     * @EXT\Route(
     *     "/admin/decision/create/form",
     *     name="formalibreBulletinDecisionCreateFrom",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/decisionCreateModalForm.html.twig")
     */
    public function decisionCreateFormAction()
    {
        $this->checkOpen();
        $form = $this->formFactory->create(new DecisionType(), new Decision());

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/admin/decision/create",
     *     name="formalibreBulletinDecisionCreate",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/decisionCreateModalForm.html.twig")
     */
    public function decisionCreateAction()
    {
        $this->checkOpen();
        $decision = new Decision();
        $form = $this->formFactory->create(new DecisionType(), $decision);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->om->persist($decision);
            $this->om->flush();

            return new JsonResponse('success', 200);
        } else {

            return array('form' => $form->createView());
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/decision/{decision}/edit/form",
     *     name="formalibreBulletinDecisionEditFrom",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/decisionEditModalForm.html.twig")
     */
    public function decisionEditFormAction(Decision $decision)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(new DecisionType(), $decision);

        return array('form' => $form->createView(), 'decision' => $decision);
    }

    /**
     * @EXT\Route(
     *     "/admin/decision/{decision}/edit",
     *     name="formalibreBulletinDecisionEdit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/decisionEditModalForm.html.twig")
     */
    public function decisionEditAction(Decision $decision)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(new DecisionType(), $decision);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->om->persist($decision);
            $this->om->flush();

            return new JsonResponse('success', 200);
        } else {

            return array('form' => $form->createView(), 'decision' => $decision);
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/decision/{decision}/delete",
     *     name="formalibreBulletinDecisionDelete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function decisionDeleteAction(Decision $decision)
    {
        $this->checkOpen();
        $this->om->remove($decision);
        $this->om->flush();

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/user/{user}/periode/{periode}/decisions/list",
     *     name="formalibreBulletinUserDecisionsList",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/userDecisionsList.html.twig")
     */
    public function userDecisionsListAction(User $user, Periode $periode)
    {
        $this->checkOpen();
        $userDecisions = $this->periodeEleveDecisionRepo->findBy(
            array('user' => $user->getId(), 'periode' => $periode->getId())
        );

        return array('user' => $user, 'periode' => $periode, 'userDecisions' => $userDecisions);
    }

    /**
     * @EXT\Route(
     *     "/user/{user}/periode/{periode}/decision/create/form",
     *     name="formalibreBulletinUserDecisionCreateFrom",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/userDecisionCreateModalForm.html.twig")
     */
    public function userDecisionCreateFormAction(User $user, Periode $periode)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(new UserDecisionCreateType(), new PeriodeEleveDecision());

        return array(
            'form' => $form->createView(),
            'user' => $user,
            'periode' => $periode
        );
    }

    /**
     * @EXT\Route(
     *     "/user/{user}/periode/{periode}/decision/create",
     *     name="formalibreBulletinUserDecisionCreate",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/userDecisionCreateModalForm.html.twig")
     */
    public function userDecisionCreateAction(User $user, Periode $periode)
    {
        $this->checkOpen();
        $periodeEleveDecision = new PeriodeEleveDecision();
        $form = $this->formFactory->create(new UserDecisionCreateType(), $periodeEleveDecision);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $periodeEleveDecision->setUser($user);
            $periodeEleveDecision->setPeriode($periode);
            $this->om->persist($periodeEleveDecision);
            $this->om->flush();

            return new JsonResponse('success', 200);
        } else {

            return array(
                'form' => $form->createView(),
                'user' => $user,
                'periode' => $periode
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/user/decision/{decision}/edit/form",
     *     name="formalibreBulletinUserDecisionEditFrom",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/userDecisionEditModalForm.html.twig")
     */
    public function userDecisionEditFormAction(PeriodeEleveDecision $decision)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(
            new UserDecisionEditType($decision, $this->om),
            $decision
        );

        return array('form' => $form->createView(), 'decision' => $decision);
    }

    /**
     * @EXT\Route(
     *     "/user/decision/{decision}/edit",
     *     name="formalibreBulletinUserDecisionEdit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/userDecisionEditModalForm.html.twig")
     */
    public function userDecisionEditAction(PeriodeEleveDecision $decision)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(
            new UserDecisionEditType($decision, $this->om),
            $decision
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->om->persist($decision);
            $this->om->flush();

            return new JsonResponse('success', 200);
        } else {

            return array('form' => $form->createView(), 'decision' => $decision);
        }
    }

    /**
     * @EXT\Route(
     *     "/user/decision/{decision}/delete",
     *     name="formalibreBulletinUserDecisionDelete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function userDecisionDeleteAction(PeriodeEleveDecision $decision)
    {
        $this->checkOpen();
        $this->om->remove($decision);
        $this->om->flush();

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/groupe/titulaires/list",
     *     name="formalibreBulletinGroupeTitulairesList",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/groupeTitulairesList.html.twig")
     */
    public function groupeTitulairesListAction()
    {
        $this->checkOpen();
        $groupeTitulaires = $this->groupeTitulaireRepo->findAll();
        $titulaireGroups = array();

        foreach ($groupeTitulaires as $groupeTitulaire) {
            $user = $groupeTitulaire->getUser();
            $group = $groupeTitulaire->getGroup();

            if (!isset($titulaireGroups[$user->getId()])) {
                $titulaireGroups[$user->getId()] = array();
                $titulaireGroups[$user->getId()]['user'] = $user;
                $titulaireGroups[$user->getId()]['groups'] = array();
            }
            $datas = array(
                'group' => $group,
                'groupeTitulaireId' => $groupeTitulaire->getId()
            );
            $titulaireGroups[$user->getId()]['groups'][] = $datas;
        }

        return array('titulaireGroups' => $titulaireGroups);
    }

    /**
     * @EXT\Route(
     *     "/admin/groupe/titulaire/create/form",
     *     name="formalibreBulletinGroupeTitulaireCreateForm",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/groupeTitulaireCreateModalForm.html.twig")
     */
    public function groupeTitulaireCreateFormAction()
    {
        $this->checkOpen();
        $form = $this->formFactory->create(new GroupeTitulaireType(), new GroupeTitulaire());

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/admin/groupe/titulaire/create",
     *     name="formalibreBulletinGroupeTitulaireCreate",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/groupeTitulaireCreateModalForm.html.twig")
     */
    public function groupeTitulaireCreateAction()
    {
        $this->checkOpen();
        $groupeTitulaire = new GroupeTitulaire();
        $form = $this->formFactory->create(new GroupeTitulaireType(), $groupeTitulaire);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->om->persist($groupeTitulaire);
            $this->om->flush();

            return new JsonResponse('success', 200);
        } else {

            return array('form' => $form->createView());
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/groupe/titulaire/{groupeTitulaire}/edit/form",
     *     name="formalibreBulletinGroupeTitulaireEditForm",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/groupeTitulaireEditModalForm.html.twig")
     */
    public function groupeTitulaireEditFormAction(GroupeTitulaire $groupeTitulaire)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(new GroupeTitulaireType(), $groupeTitulaire);

        return array('form' => $form->createView(), 'groupeTitulaire' => $groupeTitulaire);
    }

    /**
     * @EXT\Route(
     *     "/admin/groupe/titulaire/{groupeTitulaire}/edit",
     *     name="formalibreBulletinGroupeTitulaireEdit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/groupeTitulaireEditModalForm.html.twig")
     */
    public function groupeTitulaireEditAction(GroupeTitulaire $groupeTitulaire)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(new GroupeTitulaireType(), $groupeTitulaire);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->om->persist($groupeTitulaire);
            $this->om->flush();

            return new JsonResponse('success', 200);
        } else {

            return array(
                'form' => $form->createView(),
                'groupeTitulaire' => $groupeTitulaire
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/groupe/titulaire/{groupeTitulaire}/delete",
     *     name="formalibreBulletinGroupeTitulaireDelete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function groupeTitulaireDeleteAction(GroupeTitulaire $groupeTitulaire)
    {
        $this->checkOpen();
        $this->om->remove($groupeTitulaire);
        $this->om->flush();

        return new JsonResponse('success', 200);
    }

    private function checkOpen()
    {
        if ($this->authorization->isGranted('ROLE_BULLETIN_ADMIN')) {
            return true;
        }

        throw new AccessDeniedException();
    }
}

