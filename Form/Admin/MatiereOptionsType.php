<?php

namespace FormaLibre\BulletinBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Range;

class MatiereOptionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder->add(
            'matiere',
            'entity',
            array(
                'required' => false,
                'disabled' => true,
                'read_only' => true,
                'class' => 'Claroline\CursusBundle\Entity\CourseSession',
                'property' => 'courseTitle'
            )
        );
        $builder->add(
            'total',
            'integer',
            array(
                'required' => false,
                'label' => 'Total',
                'constraints' => new Range(array('min' => 0)),
                'attr' => array('min' => 0)
            )
        );
        $builder->add(
            'position',
            'integer',
            array(
                'required' => false,
                'label' => 'Position',
                'constraints' => new Range(array('min' => 0)),
                'attr' => array('min' => 0)
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver){
        $resolver->setDefaults(array(
            'data_class' => 'FormaLibre\BulletinBundle\Entity\MatiereOptions',
        ));
    }

    public function getName(){

        return 'matiere_options_form';
    }
}
