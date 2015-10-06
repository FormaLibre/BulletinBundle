<?php

namespace FormaLibre\BulletinBundle\Form\Admin;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class GroupeTitulaireType extends AbstractType
{
    private $groups;

    public function __construct(array $groups = array())
    {
        $this->groups = $groups;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'user',
            'userpicker',
            array(
                'picker_name' => 'groupe_titulaire',
                'picker_title' => 'Sélection du titulaire',
                'required' => true,
                'multiple' => false,
                'show_all_users' => true,
                'label' => 'Titulaire',
                'constraints' => new NotBlank()
            )
        );
        $builder->add(
            'group',
            'entity',
            array(
                'class' => 'ClarolineCoreBundle:Group',
//                'query_builder' => function (EntityRepository $er) {
//
//                    return $er->createQueryBuilder('g')
//                        ->orderBy('g.name', 'ASC');
//                },
                'choices' => $this->groups,
                'property' => 'name',
                'required' => true,
                'multiple' => false,
                'label' => 'Groupe',
                'constraints' => new NotBlank()
            )
        );
    }

    public function getName()
    {
        return 'groupe_titulaire_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FormaLibre\BulletinBundle\Entity\GroupeTitulaire'
        ));
    }
}
