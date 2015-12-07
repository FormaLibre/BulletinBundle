<?php

namespace FormaLibre\BulletinBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PeriodesGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            array('required' => true, 'label' => 'Nom')
        );
    }

    public function getName()
    {
        return 'PeriodesGroup';
    }
}
