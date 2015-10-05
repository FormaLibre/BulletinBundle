<?php

namespace FormaLibre\BulletinBundle\Form\Admin;

use Claroline\CoreBundle\Persistence\ObjectManager;
use FormaLibre\BulletinBundle\Entity\PeriodeEleveDecision;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserDecisionEditType extends AbstractType
{
    private $decision;
    private $om;

    public function __construct(PeriodeEleveDecision $decision, ObjectManager $om)
    {
        $this->decision = $decision;
        $this->om = $om;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $matieres = $this->getAvailableMatieres();

        $builder->add(
            'matieres',
            'entity',
            array(
                'label' => 'Matières',
                'class' => 'LaurentSchoolBundle:Matiere',
                'choice_translation_domain' => true,
                'choices' => $matieres,
                'property' => 'officialName',
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
        $pempRepo = $this->om->getRepository('FormaLibreBulletinBundle:PeriodeEleveMatierePoint');
        $pemps = $pempRepo->findPeriodeEleveMatiere($user, $periode);
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
