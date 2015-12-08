<?php

namespace FormaLibre\BulletinBundle\Controller;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use FormaLibre\BulletinBundle\Entity\Decision;
use FormaLibre\BulletinBundle\Entity\GroupeTitulaire;
use FormaLibre\BulletinBundle\Entity\Pemps;
use FormaLibre\BulletinBundle\Entity\Periode;
use FormaLibre\BulletinBundle\Entity\PeriodeEleveDecision;
use FormaLibre\BulletinBundle\Entity\PointDivers;
use FormaLibre\BulletinBundle\Form\Admin\BulletinConfigurationType;
use FormaLibre\BulletinBundle\Form\Admin\DecisionType;
use FormaLibre\BulletinBundle\Form\Admin\GroupeTitulaireType;
use FormaLibre\BulletinBundle\Form\Admin\MatiereOptionsCollectionType;
use FormaLibre\BulletinBundle\Form\Admin\PeriodeType;
use FormaLibre\BulletinBundle\Form\Admin\PeriodeOptionsType;
use FormaLibre\BulletinBundle\Form\Admin\PointDiversType;
use FormaLibre\BulletinBundle\Form\Admin\UserDecisionCreateType;
use FormaLibre\BulletinBundle\Form\Admin\UserDecisionEditType;
use FormaLibre\BulletinBundle\Manager\BulletinManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use JMS\Serializer\SerializationContext;

class BulletinAdminController extends Controller
{
    private $authorization;
    private $bulletinManager;
    private $em;
    private $formFactory;
    private $om;
    /** @var  string */
    private $pdfDir;
    private $request;
    private $roleManager;
    private $router;
    private $toolManager;
    private $userManager;

    private $decisionRepo;
    private $groupeTitulaireRepo;
    /** @var MatiereRepository */
    private $matiereRepo;
    /** @var PeriodeElevePointDiversPointRepository */
    private $pemdRepo;
    /** @var PeriodeEleveMatierePointRepository */
    private $pempRepo;
    private $periodeEleveDecisionRepo;
    /** @var PeriodeRepository */
    private $periodeRepo;
    /** @var UserRepository */
    private $userRepo;


    /**
     * @DI\InjectParams({
     *     "authorization"         = @DI\Inject("security.authorization_checker"),
     *     "bulletinManager"       = @DI\Inject("formalibre.manager.bulletin_manager"),
     *     "em"                    = @DI\Inject("doctrine.orm.entity_manager"),
     *     "formFactory"           = @DI\Inject("form.factory"),
     *     "om"                    = @DI\Inject("claroline.persistence.object_manager"),
     *     "pdfDir"                = @DI\Inject("%formalibre.directories.pdf%"),
     *     "requestStack"          = @DI\Inject("request_stack"),
     *     "roleManager"           = @DI\Inject("claroline.manager.role_manager"),
     *     "router"                = @DI\Inject("router"),
     *     "toolManager"           = @DI\Inject("claroline.manager.tool_manager"),
     *     "userManager"           = @DI\Inject("claroline.manager.user_manager")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        BulletinManager $bulletinManager,
        EntityManager $em,
        FormFactory $formFactory,
        ObjectManager $om,
        $pdfDir,
        RequestStack $requestStack,
        RoleManager $roleManager,
        RouterInterface $router,
        ToolManager $toolManager,
        UserManager $userManager
    )
    {
        $this->authorization = $authorization;
        $this->bulletinManager = $bulletinManager;
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->pdfDir = $pdfDir;
        $this->request = $requestStack->getCurrentRequest();
        $this->roleManager = $roleManager;
        $this->router = $router;
        $this->toolManager = $toolManager;
        $this->userManager = $userManager;

        $this->decisionRepo = $om->getRepository('FormaLibreBulletinBundle:Decision');
        $this->groupeTitulaireRepo = $om->getRepository('FormaLibreBulletinBundle:GroupeTitulaire');
        $this->matiereRepo = $om->getRepository('ClarolineCursusBundle:CourseSession');
        $this->pemdRepo = $om->getRepository('FormaLibreBulletinBundle:PeriodeElevePointDiversPoint');
        $this->pempRepo = $om->getRepository('FormaLibreBulletinBundle:PeriodeEleveMatierePoint');
        $this->periodeEleveDecisionRepo = $om->getRepository('FormaLibreBulletinBundle:PeriodeEleveDecision');
        $this->periodeRepo = $om->getRepository('FormaLibreBulletinBundle:Periode');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
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

        $classe = $this->bulletinManager->getClasseByEleve($user);
        $dirName = is_null($classe) ? 'CLASSE_INDEFINIE' : $classe->getName();

        $filename = $user->getLastName() . $user->getFirstName(). '-'. date("Y-m-d-H-i-s") . '.pdf';
        $dir = $this->pdfDir . $dirName . '/' . $filename;

        $eleveUrl = $this->generateUrl(
            'formalibreBulletinPrintEleve',
            array('periode' => $periode->getId(), 'eleve' => $user->getId()), true
        );

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
     * @EXT\Template("FormaLibreBulletinBundle::Admin/PeriodeModalForm.html.twig")
     */
    public function adminSchoolPeriodeAddAction(Request $request)
    {
        $this->checkOpen();
        $periode = new Periode();
        $form = $this->createForm(new PeriodeType(1), $periode);

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->em->persist($periode);
                $this->em->flush();

                return new JsonResponse('success', 200);
            }
        }

        return array(
            'form' => $form->createView(),
            'action' => $this->generateUrl('formalibreBulletinPeriodeAdd'),
            'title' => 'Ajouter une période'
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/periode/{periode}/edit",
     *     name="formalibreBulletinPeriodeEdit",
     *     options = {"expose"=true}
     * )
     *
     * @param Periode $periode
     * @EXT\Template("FormaLibreBulletinBundle::Admin/PeriodeModalForm.html.twig")
     */
    public function adminSchoolPeriodeEditAction(Request $request, Periode $periode)
    {
        $this->checkOpen();
        $form = $this->createForm(new PeriodeType(), $periode);

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->em->persist($periode);
                $this->em->flush();

                return new JsonResponse('success', 200);
            }
        }

        return array(
            'form' => $form->createView(),
            'action' => $this->generateUrl(
                'formalibreBulletinPeriodeEdit',
                array('periode' => $periode->getId())
            ),
            'title' => 'Modifier une période'
        );
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
            new UserDecisionEditType($decision, $this->bulletinManager),
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
            new UserDecisionEditType($decision, $this->bulletinManager),
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
        $groups = $this->bulletinManager->getTaggedGroups();
        $form = $this->formFactory->create(new GroupeTitulaireType($groups), new GroupeTitulaire());

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
        $groups = $this->bulletinManager->getTaggedGroups();
        $form = $this->formFactory->create(new GroupeTitulaireType($groups), $groupeTitulaire);
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
        $groups = $this->bulletinManager->getTaggedGroups();
        $form = $this->formFactory->create(new GroupeTitulaireType($groups), $groupeTitulaire);

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
        $groups = $this->bulletinManager->getTaggedGroups();
        $form = $this->formFactory->create(new GroupeTitulaireType($groups), $groupeTitulaire);
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

    /**
     * @EXT\Route(
     *     "/admin/point/divers/management",
     *     name="formalibre_bulletin_point_divers_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/pointDiversManagement.html.twig")
     */
    public function pointDiversManagementAction()
    {
        $this->checkOpen();
        $allPointDivers = $this->bulletinManager->getAllPointDivers();

        return array('allPointDivers' => $allPointDivers);
    }

    /**
     * @EXT\Route(
     *     "/admin/point/divers/create/form",
     *     name="formalibre_bulletin_point_divers_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/pointDiversCreateModalForm.html.twig")
     */
    public function pointDiversCreateFormAction()
    {
        $this->checkOpen();
        $form = $this->formFactory->create(new PointDiversType(), new PointDivers());

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/admin/point/divers/create",
     *     name="formalibre_bulletin_point_divers_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/pointDiversCreateModalForm.html.twig")
     */
    public function pointDiversCreateAction()
    {
        $this->checkOpen();
        $pointDivers = new PointDivers();
        $form = $this->formFactory->create(new PointDiversType(), $pointDivers);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->om->persist($pointDivers);
            $this->om->flush();

            return new JsonResponse('success', 200);
        } else {

            return array('form' => $form->createView());
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/point/divers/{pointDivers}/edit/form",
     *     name="formalibre_bulletin_point_divers_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/pointDiversEditModalForm.html.twig")
     */
    public function pointDiversEditFormAction(PointDivers $pointDivers)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(new PointDiversType(), $pointDivers);

        return array('form' => $form->createView(), 'pointDivers' => $pointDivers);
    }

    /**
     * @EXT\Route(
     *     "/admin/point/divers/{pointDivers}/edit",
     *     name="formalibre_bulletin_point_divers_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/pointDiversModalEditForm.html.twig")
     */
    public function pointDiversEditAction(PointDivers $pointDivers)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(new PointDiversType(), $pointDivers);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->om->persist($pointDivers);
            $this->om->flush();

            return new JsonResponse('success', 200);
        } else {

            return array(
                'form' => $form->createView(),
                'pointDivers' => $pointDivers
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/point/divers/{pointDivers}/delete",
     *     name="formalibre_bulletin_point_divers_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function pointDiversDeleteAction(PointDivers $pointDivers)
    {
        $this->checkOpen();
        $this->om->remove($pointDivers);
        $this->om->flush();

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/periode/{periode}/page/{page}/limit/{limit}/sessions.json",
     *     name="formalibre_bulletin_get_sessions",
     *     defaults={"page"=0, "limit"=99999},
     *     options = {"expose"=true}
     * )
     */
    public function getAdminSessionAction(Periode $periode, $page, $limit)
    {
        $this->checkOpen();
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

        $context = new SerializationContext();
        $context->setGroups('bulletin');
        $data = $this->container->get('serializer')->serialize($sessions, 'json', $context);
        $sessions = json_decode($data);
        $response = new JsonResponse(array('sessions' => $sessions, 'total' => $this->bulletinManager->getAvailableSessions(true)));

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/admin/periode/{periode}/page/{page}/limit/{limit}/search/sessions.json",
     *     name="formalibre_bulletin_search_sessions",
     *     defaults={"page"=0, "limit"=99999},
     *     options = {"expose"=true}
     * )
     */
    public function searchAdminSessionAction(Periode $periode, $page, $limit)
    {
        $this->checkOpen();

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

        $context = new SerializationContext();
        $context->setGroups('bulletin');
        $data = $this->container->get('serializer')->serialize($sessions, 'json', $context);
        $sessions = json_decode($data);
        $response = new JsonResponse(
            array(
                'sessions' => $sessions, 
                'total' => $this->bulletinManager->searchAvailableSessions($searches, true)
            )
        );

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/admin/periode/{periode}/add/search/sessions.json",
     *     name="formalibre_bulletin_add_search_sessions",
     *     options = {"expose"=true}
     * )
     */
    public function checkAllSessionsFromSearchAction(Periode $periode)
    {
        $this->checkOpen();
        $searches = $this->request->query->all();
        $sessions = $this->bulletinManager->searchAvailableSessions($searches, false);
        $this->bulletinManager->addSessionsToPeriode($sessions, $periode);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/periode/{periode}/remove/search/sessions.json",
     *     name="formalibre_bulletin_remove_search_sessions",
     *     options = {"expose"=true}
     * )
     */
    public function removeAllSessionsFromSearchAction(Periode $periode)
    {
        $this->checkOpen();
        $searches = $this->request->query->all();
        $sessions = $this->bulletinManager->searchAvailableSessions($searches, false);
        $this->bulletinManager->removeSessionsFromPeriode($sessions, $periode);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/periode/{periode}/add/sessions",
     *     name="formalibre_bulletin_add_sessions_to_periode",
     *     options = {"expose"=true}
     * )
     */
    public function addSessionsToPeriode(Periode $periode)
    {
        $this->checkOpen();
        $sessions = $this->request->request->all();
        $sessionIds = array();

        foreach ($sessions as $session) {
            $sessionIds[] = $session['id'];
        }

        $sessions = $this->om->findByIds('Claroline\CursusBundle\Entity\CourseSession', $sessionIds);
        $periode->setCourseSessions($sessions);
        $this->om->persist($periode);
        $this->om->flush();

        return new JsonResponse('done');
    }

    /**
     * @EXT\Route(
     *     "/admin/periode/{periode}/add/session/{session}",
     *     name="formalibre_bulletin_add_session_to_periode",
     *     options = {"expose"=true}
     * )
     */
    public function addSessionToPeriode(Periode $periode, CourseSession $session)
    {
        $this->checkOpen();
        $periode->addMatiere($session);
        $this->om->persist($periode);
        $this->om->flush();

        return new JsonResponse('done');
    }

    /**
     * @EXT\Route(
     *     "/admin/periode/{periode}/remove/session/{session}",
     *     name="formalibre_bulletin_remove_session_from_periode",
     *     options = {"expose"=true}
     * )
     */
    public function removeSessionFromPeriode(Periode $periode, CourseSession $session)
    {
        $this->checkOpen();
        $periode->removeMatiere($session);
        $this->om->persist($periode);
        $this->om->flush();

        return new JsonResponse('done');
    }

    /**
     * @EXT\Route(
     *     "/admin/periode/{periode}/invert/sessions",
     *     name="formalibre_bulletin_invert_session_from_periode",
     *     options = {"expose"=true}
     * )
     */
    public function invertSessionsPeriode(Periode $periode)
    {
        $this->checkOpen();
        $sessions = $this->request->request->all();
        $sessionIds = array();

        foreach ($sessions as $session) {
            $sessionIds[] = $session['id'];
        }

        $sessions = $this->om->findByIds('Claroline\CursusBundle\Entity\CourseSession', $sessionIds);
        $this->om->startFlushSuite();

        foreach ($sessions as $session) {
            $this->bulletinManager->invertSessionPeriode($periode, $session);
        }

        $this->om->endFlushSuite();

        return new JsonResponse('done');
    }

    /**
     * @EXT\Route(
     *     "/admin/session/fields.json",
     *     name="formalibre_bulletin_get_sessions_fields",
     *     options = {"expose"=true}
     * )
     */
    public function getAdminSessionFieldsAction()
    {
        return new JsonResponse(array(
            'title',
            'name',
            'code',
        ));
    }

    /**
     * @EXT\Route(
     *     "/admin/periode/{periode}/options/edit/form",
     *     name="formalibre_bulletin_periode_options_edit_form",
     *     options = {"expose"=true}
     * )
     *
     * @param Periode $periode
     * @EXT\Template("FormaLibreBulletinBundle::Admin/PeriodeOptionsEditForm.html.twig")
     */
    public function adminPeriodeOptionsEditFormAction(Periode $periode)
    {
        $this->checkOpen();
        $pointsDivers = $periode->getPointDivers();
        $pointDiversIds = array();

        foreach ($pointsDivers as $pointDivers) {
            $pointDiversIds[] = $pointDivers->getId();
        }

        $allPointDivers = $this->bulletinManager->getAllPointDivers();
        $sessions = $this->bulletinManager->getAvailableSessions();
        $datas = array();
        $form = $this->createForm(new PeriodeOptionsType(), $periode);

        return array(
            'form' => $form->createView(),
            'periode' => $periode,
            'datas' => $datas,
            'allPointDivers' => $allPointDivers,
            'pointDiversIds' => $pointDiversIds
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/periode/{periode}/options/form",
     *     name="formalibre_bulletin_periode_options_edit",
     *     options = {"expose"=true}
     * )
     *
     * @param Periode $periode
     * @EXT\Template("FormaLibreBulletinBundle::Admin/PeriodeOptionsEditForm.html.twig")
     */
    public function adminPeriodeOptionsEditAction(Periode $periode)
    {
        $this->checkOpen();
        $form = $this->createForm(new PeriodeOptionsType(), $periode);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->om->persist($periode);
            $this->om->flush();

            return new RedirectResponse(
                $this->router->generate('formalibreBulletinAdminIndex')
            );
        } else {
            foreach ($pointsDivers as $pointDivers) {
                $pointDiversIds[] = $pointDivers->getId();
            }

            $allPointDivers = $this->bulletinManager->getAllPointDivers();
            $sessions = $this->bulletinManager->getAvailableSessions();
            $datas = array();

            return array(
                'form' => $form->createView(),
                'periode' => $periode,
                'datas' => $datas,
                'matiereIds' => $matiereIds,
                'allPointDivers' => $allPointDivers,
                'pointDiversIds' => $pointDiversIds
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/matieres/options/management/page/{page}/max/{max}",
     *     name="formalibre_bulletin_matieres_options_management",
     *     defaults={"page"=1, "max"=20},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/matieresOptionsManagement.html.twig")
     */
    public function matieresOptionsManagementAction($page = 1, $max = 20)
    {
        $this->checkOpen();
        $matieresOptions = $this->bulletinManager->getAllMatieresOptions(true, $page, $max);

        $formCollection = new Pemps();

        foreach ($matieresOptions as $matiereOptions) {
            $formCollection->getPemps()->add($matiereOptions);
        }
        $form = $this->formFactory->create(new MatiereOptionsCollectionType(), $formCollection);

        if ($this->request->isMethod('POST')) {
            $form->handleRequest($this->request);
            $this->om->startFlushSuite();

            foreach ($formCollection as $matiereOptions){
                $this->em->persist($matiereOptions);
            }
            $this->om->endFlushSuite();

//            return new RedirectResponse(
//                $this->router->generate('formalibreBulletinAdminIndex')
//            );
        }

        return array(
            'form' => $form->createView(),
            'pager' => $matieresOptions,
            'page' => $page,
            'max' => $max
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/bulletin/configure/form",
     *     name="formalibre_bulletin_configure_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/bulletinConfigureForm.html.twig")
     */
    public function bulletinConfigureFormAction()
    {
        $this->checkOpen();
        $form = $this->formFactory->create(
            new BulletinConfigurationType($this->bulletinManager)
        );

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/admin/bulletin/configure",
     *     name="formalibre_bulletin_configure",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreBulletinBundle::Admin/bulletinConfigureForm.html.twig")
     */
    public function bulletinConfigureAction()
    {
        $this->checkOpen();
        $form = $this->formFactory->create(
            new BulletinConfigurationType($this->bulletinManager)
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $withSecondPoint = $form->get('useSecondPoint')->getData();
            $withThirdPoint = $form->get('useThirdPoint')->getData();
            $secondPointName = $form->get('secondPointName')->getData();
            $thirdPointName = $form->get('thirdPointName')->getData();

            $this->bulletinManager->setBulletinParameter(
                'bulletin_use_second_point',
                $withSecondPoint
            );
            $this->bulletinManager->setBulletinParameter(
                'bulletin_use_third_point',
                $withThirdPoint
            );
            $this->bulletinManager->setBulletinParameter(
                'bulletin_second_point_name',
                $secondPointName
            );
            $this->bulletinManager->setBulletinParameter(
                'bulletin_third_point_name',
                $thirdPointName
            );

            return new RedirectResponse(
                $this->router->generate('formalibreBulletinAdminIndex')
            );
        } else {

            return array('form' => $form->createView());
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/periode/{periode}/options/refresh",
     *     name="formalibre_bulletin_periode_options_refresh",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     *
     * @param Periode $periode
     */
    public function refreshPeriodeOptionsAction(Periode $periode)
    {
        $this->checkOpen();
        $this->bulletinManager->refresh($periode);

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

