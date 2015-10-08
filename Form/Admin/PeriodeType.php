<?php

namespace FormaLibre\BulletinBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class PeriodeType extends AbstractType
{
    private $type;

    public function __construct($type = 0)
    {
        $this->type = $type;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->type === 1) {
            $templateOptions = array(
                'label' => 'Template',
                'constraints' => new NotBlank(),
                'data' => 'PeriodePrint'
            );
        } else {
            $templateOptions = array(
                'label' => 'Template',
                'constraints' => new NotBlank()
            );
        }
        $builder
            ->add(
                'name',
                'text',
                array(
                    'label' => 'Nom',
                    'constraints' => new NotBlank()
                )
            )
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
            ->add(
                'template',
                'text',
                $templateOptions
            )
            ->add('onlyPoint', 'checkbox', array('label' => 'Uniquement des points'))
            ->add(
                'coefficient',
                'number',
                array(
                    'label' => 'Coefficient'
                )
            );
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