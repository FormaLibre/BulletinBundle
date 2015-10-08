<?php

namespace FormaLibre\BulletinBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MatiereOptionsCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder->add(
            'pemps',
            'collection',
            array('type' => new MatiereOptionsType())
        );
        $builder->add(
            'save',
            'submit',
            array(
                'label'=>'Enregistrer',
                'attr' => array('class' => 'btn btn-primary')
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver){
        $resolver->setDefaults(array(
            'data_class' => 'FormaLibre\BulletinBundle\Entity\Pemps',
            ));
    }

    public function getName(){
        return 'matiere_options_collection_form';
    }
}
