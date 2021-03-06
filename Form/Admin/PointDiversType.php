<?php

namespace FormaLibre\BulletinBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class PointDiversType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            array(
                'required' => true,
                'label' => 'Nom',
                'constraints' => new NotBlank()
            )
        );
        $builder->add(
            'officialName',
            'text',
            array(
                'required' => true,
                'label' => 'Nom officiel',
                'constraints' => new NotBlank()
            )
        );
        $builder->add(
            'withTotal',
            'checkbox',
            array(
                'required' => false,
                'label' => 'Avec total'
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

    public function getName()
    {
        return 'point_divers_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }
}
