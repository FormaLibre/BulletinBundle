<?php

namespace FormaLibre\BulletinBundle\Form\Admin;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserDecisionCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'decision',
            'entity',
            array(
                'label' => 'Décision',
                'class' => 'FormaLibreBulletinBundle:Decision',
                'choice_translation_domain' => true,
                'query_builder' => function (EntityRepository $er) {

                    return $er->createQueryBuilder('d')
                        ->orderBy('d.content', 'ASC');
                },
                'property' => 'content',
                'multiple' => false,
                'required' => true
            )
        );
    }

    public function getName()
    {
        return 'user_decision_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }
}
