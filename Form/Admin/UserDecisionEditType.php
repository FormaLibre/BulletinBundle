<?php

namespace FormaLibre\BulletinBundle\Form\Admin;

use FormaLibre\BulletinBundle\Entity\PeriodeEleveDecision;
use FormaLibre\BulletinBundle\Manager\BulletinManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserDecisionEditType extends AbstractType
{
    private $bulletinManager;
    private $decision;
    private $om;

    public function __construct(
        PeriodeEleveDecision $decision,
        BulletinManager $bulletinManager
    )
    {
        $this->bulletinManager = $bulletinManager;
        $this->decision = $decision;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $matieres = $this->getAvailableMatieres();

        $builder->add(
            'matieres',
            'entity',
            array(
                'label' => 'Matières',
                'class' => 'ClarolineCursusBundle:CourseSession',
                'choice_translation_domain' => true,
                'choices' => $matieres,
                'property' => 'name',
                'expanded' => true,
                'multiple' => true,
                'required' => false
            )
        );
    }

    public function getName()
    {
        return 'user_decision_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    private function getAvailableMatieres()
    {
        $matieres = array();
        $user = $this->decision->getUser();
        $periode = $this->decision->getPeriode();
        $pemps = $this->bulletinManager->getPempsByEleveAndPeriode($user, $periode);
        $temp = array();

        foreach ($pemps as $pemp) {
            $matiere = $pemp->getMatiere();
            $matiereId = $matiere->getId();

            if (!isset($temp[$matiereId])) {
                $temp[$matiereId] = true;
                $matieres[] = $matiere;
            }
        }

        return $matieres;
    }
}
