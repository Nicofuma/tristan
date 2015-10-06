<?php

namespace SensioLabs\JobBoardBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use SensioLabs\JobBoardBundle\Entity\Company;
use SensioLabs\JobBoardBundle\Form\DataTransformer\CompanyTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyType extends AbstractType
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new CompanyTransformer($this->manager);
        $builder
            ->add('country', 'country', [
                'empty_value' => 'Select a country',
            ])
            ->add('city')
            ->add('name')
            ->addModelTransformer($transformer)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Company::class,
        ));
    }

    public function getName()
    {
        return 'company';
    }
}
