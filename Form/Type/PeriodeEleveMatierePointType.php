<?php

namespace FormaLibre\BulletinBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PeriodeEleveMatierePointType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder->add('matiere', 'entity', array('required' => false, 'disabled' => true, 'read_only' => true, 'class' => 'Claroline\CursusBundle\Entity\CourseSession'));
        $builder->add('point', 'text', array('required'  => false));
        $builder->add('total', 'text', array('required'  => false, 'read_only' => True));
        $builder->add('comportement', 'text', array('required'  => false));
        $builder->add('presence', 'text', array('required'  => false));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver){
        $resolver->setDefaults(array(
            'data_class' => 'FormaLibre\BulletinBundle\Entity\PeriodeEleveMatierePoint',
            ));
    }

    public function getName(){
        return 'PeriodeEleveMatierePoint';
    }
}