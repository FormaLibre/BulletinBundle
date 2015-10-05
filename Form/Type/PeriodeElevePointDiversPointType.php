<?php

namespace FormaLibre\BulletinBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PeriodeElevePointDiversPointType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder->add('divers', 'entity', array('required' => false, 'disabled' => true, 'read_only' => true, 'class' => 'FormaLibre\BulletinBundle\Entity\PointDivers'));
        $builder->add('point', 'text', array('required'  => false));
        $builder->add('total', 'text', array('required'  => false, 'read_only' => True));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver){
        $resolver->setDefaults(array(
            'data_class' => 'FormaLibre\BulletinBundle\Entity\PeriodeElevePointDiversPoint',
            ));
    }

    public function getName(){
        return 'PeriodeElevePointDiversPoint';
    }
}