<?php

namespace FormaLibre\BulletinBundle\Form\Type;

use FormaLibre\BulletinBundle\Entity\PeriodeElevePointDiversPoint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MatiereType extends AbstractType
{   private $lockStatus;
    
    public function __construct($lockStatus = false) {
        $this->lockStatus = $lockStatus;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder->add('pemps', 'collection', array('type' => new PeriodeEleveProfPointType($this->lockStatus)));
        $builder->add(
            'save', 
            'submit', 
            array('label'=>'Enregistrer et verouiller', 'attr' => array('class' => 'btn btn-primary'))
        );
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