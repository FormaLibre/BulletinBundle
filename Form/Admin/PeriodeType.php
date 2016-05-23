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
            ->add('periodesGroup','entity',array(
                'required' => false,
                'class' => 'FormaLibre\BulletinBundle\Entity\PeriodesGroup',
                'property' => 'name',
                'label' => 'Ajouter au groupe',
                'empty_value' => 'Non classées',
                'required' => false
            ))
            ->add('periodeSet',
                'choice',
                array(
                    'label' => 'Semestre',
                    'choices'=> array(
                        1 => '1er semestre',
                        2 => '2ème semestre'
                    )
                )
            )
            ->add('ReunionParent', 'tinymce', array('required' => false, 'label' => 'Réunion des parents'))
            ->add('template',
                'choice',
                array(
                    'label' => 'Template',
                    'choices'=> array(
                        'ExamPrint'=>'Examen Premier trimestre (2 périodes)',
                        'FinalExamPrint'=>'Bulletin final',
                        'PeriodePrint'=>'Une période',
                        'ExamPrintWithOnlyOnePeriodePrint'=>'Examen Premier trimestre (1 période)',
                        'CompletePrint' => 'Bulletin complet',
                        'CompletePrintLarge' => 'Bulletin complet (Large)'
                    )
                ),
                $templateOptions
            )
            ->add('oldPeriode1','entity',array(
                'required' => false,
                'class' => 'FormaLibre\BulletinBundle\Entity\Periode',
                'property' => 'name',
                'label' => 'Première période',
                'empty_value' => 'Choisissez une période',
                'required' => false
            ))
            ->add('oldPeriode2','entity',array(
                'required' => false,
                'class' => 'FormaLibre\BulletinBundle\Entity\Periode',
                'property' => 'name',
                'label' => 'Deuxième période',
                'empty_value' => 'Choisissez une période',
                'required' => false
            ))
            ->add('oldPeriode3','entity',array(
                'required' => false,
                'class' => 'FormaLibre\BulletinBundle\Entity\Periode',
                'property' => 'name',
                'label' => 'Examens',
                'empty_value' => 'Choisissez une période',
                'required' => false
            ))
            ->add('oldPeriode4','entity',array(
                'required' => false,
                'class' => 'FormaLibre\BulletinBundle\Entity\Periode',
                'property' => 'name',
                'label' => 'Troisième période',
                'empty_value' => 'Choisissez une période',
                'required' => false
            ))    
            ->add('oldPeriode5','entity',array(
                'required' => false,
                'class' => 'FormaLibre\BulletinBundle\Entity\Periode',
                'property' => 'name',
                'label' => 'Quatrième période',
                'empty_value' => 'Choisissez une période',
                'required' => false
            ))     
            ->add(
                'coefficient',
                'number',
                array(
                    'label' => 'Coefficient'
                )
            )
            ->add(
                'locked',
                'checkbox',
                array(
                    'label' => 'locked'
                )
            )
            ->add(
                'published',
                'checkbox',
                array(
                    'label' => 'published'
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