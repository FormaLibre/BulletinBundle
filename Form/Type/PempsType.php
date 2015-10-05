<?php

namespace FormaLibre\BulletinBundle\Form\Type;

use FormaLibre\BulletinBundle\Entity\PeriodeElevePointDiversPoint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PempsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder->add('pemps', 'collection', array('type' => new PeriodeEleveMatierePointType()));
        $builder->add('pemds', 'collection', array('type' => new PeriodeElevePointDiversPointType()));
        $builder->add('save', 'submit', array('label'=>'Enregistrer','attr' => array('class' => 'btn btn-primary')));
        $builder->add('saveAndAdd', 'submit', array('label'=>'Enregistrer','attr' => array('class' => 'btn btn-primary')));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver){
        $resolver->setDefaults(array(
            'data_class' => 'FormaLibre\BulletinBundle\Entity\Pemps',
            ));
    }

    public function getName(){
        return 'Pemps';
    }
}