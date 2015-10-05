<?php

namespace FormaLibre\BulletinBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PeriodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'name'))
            ->add(
                'start',
                'datepicker',
                array(
                    'label' => 'Date de début',
                    'required'  => false,
                    'widget'    => 'single_text',
                    'format'    => 'dd-MM-yyyy',
                    'autoclose' => true
                )
            )
            ->add(
                'end',
                'datepicker',
                array(
                    'label' => 'Date de fin',
                    'required'  => false,
                    'widget'    => 'single_text',
                    'format'    => 'dd-MM-yyyy',
                    'autoclose' => true
                )
            )
            ->add('ReunionParent', 'tinymce', array('required' => false, 'label' => 'Réunion des parents'))
            ->add('template', 'text', array('label' => 'Template'))
            ->add('onlyPoint', 'checkbox', array('label' => 'Uniquement des points'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FormaLibre\BulletinBundle\Entity\Periode'
        ));
    }

    public function getName()
    {
        return 'PeriodeForm';
    }

}