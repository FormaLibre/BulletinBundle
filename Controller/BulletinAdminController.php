<?php

namespace FormaLibre\BulletinBundle\Controller;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\TagBundle\Manager\TagManager;
use Doctrine\ORM\EntityManager;
use FormaLibre\BulletinBundle\Entity\Decision;
use FormaLibre\BulletinBundle\Entity\PeriodesGroup;
use FormaLibre\BulletinBundle\Entity\GroupeTitulaire;
use FormaLibre\BulletinBundle\Entity\Periode;
use FormaLibre\BulletinBundle\Entity\PeriodeEleveDecision;
use FormaLibre\BulletinBundle\Entity\PointDivers;
use FormaLibre\BulletinBundle\Form\Admin\BulletinConfigurationType;
use FormaLibre\BulletinBundle\Form\Admin\DecisionType;
use FormaLibre\BulletinBundle\Form\Admin\GroupeTitulaireType;
use FormaLibre\BulletinBundle\Form\Admin\PeriodeType;
use FormaLibre\BulletinBundle\Form\Admin\PeriodeOptionsType;
use FormaLibre\BulletinBundle\Form\Admin\PointDiversType;
use FormaLibre\BulletinBundle\Form\Admin\UserDecisionCreateType;
use FormaLibre\BulletinBundle\Form\Admin\UserDecisionEditType;
use FormaLibre\BulletinBundle\Form\Admin\PeriodesGroupType;
use FormaLibre\BulletinBundle\Manager\BulletinManager;
use JMS\DiExtraBundle\Annotation as DI;
use Ramsey\Uuid\Uuid;
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

class BulletinAdminController extends Controller
{
    private $authorization;
    private $bulletinManager;
    private $configHandler;
    private $em;
    private $formFactory;
    private $om;
    /** @var  string */
    private $pdfDir;
    private $request;
    private $roleManager;
    private $router;
    private $tagManager;
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
     /** @var PeriodesGroupRepository */
    private $periodesGroupRepo;


    /**
     * @DI\InjectParams({
     *     "authorization"         = @DI\Inject("security.authorization_checker"),
     *     "bulletinManager"       = @DI\Inject("formalibre.manager.bulletin_manager"),
     *     "configHandler"         = @DI\Inject("claroline.config.platform_config_handler"),
     *     "em"                    = @DI\Inject("doctrine.orm.entity_manager"),
     *     "formFactory"           = @DI\Inject("form.factory"),
     *     "om"                    = @DI\Inject("claroline.persistence.object_manager"),
     *     "pdfDir"                = @DI\Inject("%formalibre.directories.pdf%"),
     *     "requestStack"          = @DI\Inject("request_stack"),
     *     "roleManager"           = @DI\Inject("claroline.manager.role_manager"),
     *     "router"                = @DI\Inject("router"),
     *     "tagManager"            = @DI\Inject("claroline.manager.tag_manager"),
     *     "toolManager"           = @DI\Inject("claroline.manager.tool_manager"),
     *     "userManager"           = @DI\Inject("claroline.manager.user_manager")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        BulletinManager $bulletinManager,
        PlatformConfigurationHandler $configHandler,
        EntityManager $em,
        FormFactory $formFactory,
        ObjectManager $om,
        $pdfDir,
        RequestStack $requestStack,
        RoleManager $roleManager,
        RouterInterface $router,
        TagManager $tagManager,
        ToolManager $toolManager,
        UserManager $userManager
    )
    {
        $this->authorization = $authorization;
        $this->bulletinManager = $bulletinManager;
        $this->configHandler = $configHandler;
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->pdfDir = $pdfDir;
        $this->request = $requestStack->getCurrentRequest();
        $this->roleManager = $roleManager;
        $this->router = $router;
        $this->tagManager = $tagManager;
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
        $this->periodesGroupRepo = $om->getRepository('FormaLibreBulletinBundle:PeriodesGroup');
    }

    /**
     * @EXT\Route("/admin/", name="formalibreBulletinAdminIndex")
     */
    public function indexAction()
    {
        $this->checkOpen();
        
        $periodesGroup =$this->periodesGroupRepo->findBy(Array(),Array('id'=>'DESC'));
        
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

        return $this->render('FormaLibreBulletinBundle::Admin/BulletinAdminIndex.html.twig', 
                array('periodes' => $periodes, 
                      'periodeCompleted' => $periodeCompleted,
                      'periodesGroup' => $periodesGroup));
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
        $template = $periode->getTemplate();
        $options = ($template === 'CompletePrint' || $template === 'CompletePrintLarge') ?
            ['orientation' => 'landscape', 'page-size' => 'A3'] :
            [];
        $this->get('knp_snappy.pdf')->generate($elevesUrl, $dir, $options);

        $headers = array(
            'Content-Type'          => 'application/pdf',
            'Content-Disposition'   => 'attachment; filename="'.$filename.'"'
        );

        return new Response(file_get_contents($dir), 200, $headers);
    }

    /**
     * @EXT\Route(
     *     "/admin/periode/{periode}/group/{group}/empty/pdf/",
     *     name="formalibreBulletinPrintGroupPdfEmpty",
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
    public function PrintEmptyPdfGroupAction(Periode $periode, Group $group)
    {
        $this->checkOpen();
        $filename = $group->getName(). '-'. date("Y-m-d-H-i-s") . '.pdf';
        $dir = $this->pdfDir . $group->getName() . '/' . $filename;

        $eleves = $this->userRepo->findByGroup($group);
        $elevesUrl = array();
        foreach ($eleves as $eleve){
            $elevesUrl[] = $this->generateUrl('formalibreBulletinPrintEleveEmpty', array('periode' => $periode->getId(), 'eleve' => $eleve->getId()), true);
        }
        $template = $periode->getTemplate();
        $options = ($template === 'CompletePrint' || $template === 'CompletePrintLarge') ?
            ['orientation' => 'landscape', 'page-size' => 'A3'] :
            [];
        $this->get('knp_snappy.pdf')->generate($elevesUrl, $dir, $options);

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
//        $this->checkOpen();

        $classe = $this->bulletinManager->getClasseByEleve($user);
        $dirName = is_null($classe) ? 'CLASSE_INDEFINIE' : $classe->getName();

        $filename = $user->getLastName() . $user->getFirstName(). '-'. date("Y-m-d-H-i-s") . '.pdf';
        $dir = $this->pdfDir . $dirName . '/' . $filename;

        $eleveUrl = $this->generateUrl(
            'formalibreBulletinPrintEleve',
            array('periode' => $periode->getId(), 'eleve' => $user->getId()), true
        );
        $template = $periode->getTemplate();
        $options = ($template === 'CompletePrint' || $template === 'CompletePrintLarge') ?
            ['orientation' => 'landscape', 'page-size' => 'A3'] :
            [];

        $this->get('knp_snappy.pdf')->generate($eleveUrl, $dir, $options);

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
        $form = $this->createForm(new PeriodeOptionsType(), $periode);

        return array(
            'form' => $form->createView(),
            'periode' => $periode
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

        return array(
            'form' => $form->createView(),
            'periode' => $periode
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/periode/{periode}/options/form/submit",
     *     name="formalibre_bulletin_periode_options_edit_submit",
     *     options = {"expose"=true}
     * )
     *
     * @param Periode $periode
     * @EXT\Template("FormaLibreBulletinBundle::Admin/PeriodeOptionsEditForm.html.twig")
     */
    public function submitAdminPeriodeOptionsEditAction(Periode $periode)
    {
        $this->checkOpen();
        $form = $this->createForm(new PeriodeOptionsType(), $periode);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->om->persist($periode);
            $this->om->flush();
        } else {
        //stupid hack because if there are no PointDivers, the form is always wrong with no errors (can't find why)
            $periode->setPointDivers(array());
        }

        $this->om->persist($periode);
        $this->om->flush();

        return new RedirectResponse(
            $this->router->generate('formalibreBulletinAdminIndex')
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/matieres/options/management",
     *     name="formalibre_bulletin_matieres_options_management",
     *     options={"expose"=true}
     * )
     * @EXT\Template("FormaLibreBulletinBundle::Admin/matieresOptionsManagement.html.twig")
     */
    public function matieresOptionsManagementAction()
    {
        $this->checkOpen();

        return array();
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
     *     "/admin/bulletin/remove/{periode}",
     *     name="formalibre_bulletin_remove_periode",
     *     options={"expose"=true}
     * )
     */
    public function removePeriodeAction(Periode $periode)
    {
        $this->checkOpen();
        $this->bulletinManager->removePeriode($periode);

        return new JsonResponse('success');
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
    
    
     /**
     * @EXT\Route("/admin/periodesGroup", name="formalibreBulletinPeriodesGroup", options = {"expose"=true})
     *
     * @EXT\Template("FormaLibreBulletinBundle::Admin/PeriodesGroupeForm.html.twig")
     */
    public function adminSchoolPeriodesGroupAction()
    {
       $periodesGroup =$this->periodesGroupRepo->findBy(Array(),Array('id'=>'DESC'));
       
        return array('periodesGroup'=>$periodesGroup);
    }
    
    /**
     * @EXT\Route("/admin/periodesGroup/supprimer/periodesGroupId/{periodesGroupId}", 
     * name="formalibre_periodesGroup_supprimer", options = {"expose"=true})
     *
     */
    public function adminPeriodesGroupSupprimerAction(PeriodesGroup $periodesGroupId)
    {   
        $this->checkOpen();
        $periodesToChange= $this->periodeRepo->findBy(array('periodesGroup'=>$periodesGroupId));
        foreach ($periodesToChange as $onePeriodeToChange){
            
            $onePeriodeToChange->setPeriodesGroup(null);
            $this->om->persist($onePeriodeToChange);  
        }

        $this->om->remove($periodesGroupId);
        $this->om->flush();

        return new JsonResponse('success', 200);
    }
     
     /**
     * @EXT\Route("/admin/periodesGroupNew", name="formalibreBulletinPeriodesGroupNew", options = {"expose"=true})
     *
     * @EXT\Template("FormaLibreBulletinBundle::Admin/PeriodesGroupeFormNew.html.twig")
     */
    public function adminSchoolPeriodesGroupNewAction()
    {
        $actualPeriodesGroup = new PeriodesGroup();
        $form = $this->createForm(new PeriodesGroupType(), $actualPeriodesGroup);
       
        $form->handleRequest($this->request);
      
            if ($form->isValid()){
                
                $this->om->persist($actualPeriodesGroup);
                $this->om->flush();
                
                 return new JsonResponse('success', 200);
            } 

        return array('form' => $form->createView());
    }
    
         /**
     * @EXT\Route("/admin/periodesGroupEdit/periodesGroupId/{periodesGroupId}", name="formalibreBulletinPeriodesGroupEdit", options = {"expose"=true})
     *
     * @EXT\Template("FormaLibreBulletinBundle::Admin/PeriodesGroupeFormEdit.html.twig")
     */
    public function adminSchoolPeriodesGroupEditAction(PeriodesGroup $periodesGroupId)
     {   
        $form = $this->createForm(new PeriodesGroupType(), $periodesGroupId);
        
        $form->handleRequest($this->request);
      
            if ($form->isValid()){
                
                $this->om->persist($periodesGroupId);
                $this->om->flush();
                
                 return new JsonResponse('success', 200);
            } 
        return array('periodesGroupId'=>$periodesGroupId,
                     'form' => $form->createView());    
    }

    /**
     * @EXT\Route(
     *     "/admin/all/periodes/points/edition",
     *     name="formalibre_bulletin_all_periodes_points_edition",
     *     options = {"expose"=true}
     * )
     *
     * @EXT\Template("FormaLibreBulletinBundle::Admin/allPeriodesPointsEdition.html.twig")
     */
    public function adminAllPeriodesPointsEditionAction()
    {
        $this->checkOpen();

        return array();
    }

    /**
     * @EXT\Route(
     *     "/admin/groups/management",
     *     name="formalibre_bulletin_groups_management",
     *     options = {"expose"=true}
     * )
     *
     * @EXT\Template("FormaLibreBulletinBundle::Admin/groupsManagement.html.twig")
     */
    public function adminGroupsManagementAction()
    {
        $data = [];
        $this->checkOpen();
        $groups = $this->bulletinManager->getTaggedGroups();

        foreach ($groups as $group) {
            $data[] = ['id' => $group->getId(), 'name' => $group->getName()];
        }

        return ['classes' => $data];
    }

    /**
     * @EXT\Route(
     *     "/admin/group/{group}/tag/as/class",
     *     name="formalibre_bulletin_group_tag_as_class",
     *     options = {"expose"=true}
     * )
     */
    public function adminGroupTagAsClassAction(Group $group)
    {
        $this->tagManager->tagObject(['Classe'], $group);

        return new JsonResponse(['id' => $group->getId(), 'name' => $group->getName()], 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/group/{group}/remove/class/tag",
     *     name="formalibre_bulletin_group_remove_class_tag",
     *     options = {"expose"=true}
     * )
     */
    public function adminGroupRemoveClassTagAction(Group $group)
    {
        $this->tagManager->removeTaggedObjectByTagNameAndObjectIdAndClass(
            'Classe',
            $group->getId(),
            'Claroline\CoreBundle\Entity\Group'
        );

        return new JsonResponse(['id' => $group->getId()], 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/archives/download",
     *     name="formalibre_bulletin_archives_download",
     *     options = {"expose"=true}
     * )
     */
    public function archivesDownloadAction()
    {
        $this->checkOpen();
        $archivesDir = $this->pdfDir.'archives'.DIRECTORY_SEPARATOR;
        $archivesDir = str_replace('\\', '/', realpath($archivesDir));
        $tempDir = $this->configHandler->getParameter('tmp_dir');
        $hashName = Uuid::uuid4()->toString();
        $archivePath = $tempDir.DIRECTORY_SEPARATOR.$hashName.'.zip';
        $archive = new \ZipArchive();
        $archive->open($archivePath, \ZipArchive::CREATE);
        $nbFiles = 0;

        if (is_dir($archivesDir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($archivesDir),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                if (in_array(substr($file, strrpos($file, '/') + 1), ['.', '..'])) {
                    continue;
                }
                if (is_dir($file)) {
                    $archive->addEmptyDir(str_replace($archivesDir . '/', '', $file));
                    ++$nbFiles;
                } elseif (is_file($file)) {
                    $str = str_replace($archivesDir . '/', '', '/' . $file);
                    $archive->addFromString($str, file_get_contents($file));
                    ++$nbFiles;
                }
            }
        }
        if ($nbFiles === 0) {
            $archive->addFromString('empty', '');
        }
        $archive->close();

        $headers = array(
            'Content-Type'          => 'application/pdf',
            'Content-Disposition'   => 'attachment; filename="archives.zip"'
        );

        return new Response(file_get_contents($archivePath), 200, $headers);
    }
}
