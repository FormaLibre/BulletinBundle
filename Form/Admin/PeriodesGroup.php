<?php

namespace FormaLibre\BulletinBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DecisionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'content',
            'textarea',
            array('required' => true, 'label' => 'Texte')
        );
        $builder->add(
            'withMatiere',
            'checkbox',
            array('required' => true, 'label' => 'Peut afficher des matières')
        );
    }

    public function getName()
    {
        return 'decision_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }
}
