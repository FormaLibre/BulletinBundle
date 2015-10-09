<?php

namespace FormaLibre\BulletinBundle\Form\Admin;

use FormaLibre\BulletinBundle\Manager\BulletinManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class BulletinConfigurationType extends AbstractType
{
    private $bulletinManager;

    public function __construct(BulletinManager $bulletinManager)
    {
        $this->bulletinManager = $bulletinManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $useSecondPoint = $this->bulletinManager->hasSecondPoint();
        $useThirdPoint = $this->bulletinManager->hasThirdPoint();
        $secondPointName = $this->bulletinManager->getSecondPointName();
        $thirdPointName = $this->bulletinManager->getThirdPointName();

        $builder->add(
            'useSecondPoint',
            'checkbox',
            array(
                'required' => false,
                'data' => $useSecondPoint,
                'label' => 'Utiliser le 2ème champs de points'
            )
        )
        ->add(
            'useThirdPoint',
            'checkbox',
            array(
                'required' => false,
                'data' => $useThirdPoint,
                'label' => 'Utiliser le 3ème champs de points'
            )
        )->add(
            'secondPointName',
            'text',
            array(
                'required' => true,
                'data' => $secondPointName,
                'label' => 'Intitulé du 2ème champs de points',
                'constraints' => new NotBlank()
            )
        )->add(
            'thirdPointName',
            'text',
            array(
                'required' => true,
                'data' => $thirdPointName,
                'label' => 'Intitulé du 3ème champs de points',
                'constraints' => new NotBlank()
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getName()
    {
        return 'bulletin_configuration_form';
    }
}
