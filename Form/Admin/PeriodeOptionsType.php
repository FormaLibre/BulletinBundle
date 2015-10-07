<?php

namespace FormaLibre\BulletinBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PeriodeOptionsType extends AbstractType
{
    private $datas;

    public function __construct(array $datas = array())
    {
        $this->datas = $datas;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'matieres',
            'entity',
            array(
                'class' => 'ClarolineCursusBundle:CourseSession',
                'property' => 'name',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'label' => 'Matières'
            )
        )
        ->add(
            'pointDivers',
            'entity',
            array(
                'class' => 'FormaLibreBulletinBundle:PointDivers',
                'property' => 'name',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'label' => 'Point divers'
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
        return 'periode_options_form';
    }
}