<?php

namespace FormaLibre\BulletinBundle\Form\Admin;

use Claroline\CursusBundle\Entity\CourseSession;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PeriodeType extends AbstractType
{
    private $datas;

    public function __construct(array $datas = array())
    {
        $this->datas = $datas;
    }

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
            ->add('onlyPoint', 'checkbox', array('label' => 'Uniquement des points'))
            ->add(
                'matieres',
                'entity',
                array(
                    'class' => 'ClarolineCursusBundle:CourseSession',
                    'choices' => $this->datas,
                    'property' => 'name',
                    'required' => false,
                    'multiple' => true,
                    'label' => 'Matières'
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