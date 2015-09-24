<?php

namespace SensioLabs\JobBoardBundle\Form\Type;

use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JobType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('company')
            ->add('country', 'country', [
                'empty_value' => 'Select a country',
            ])
            ->add('city')
            ->add('company')
            ->add('description', 'ckeditor')
            ->add('contractType', 'choice', [
                'choices' => Job::getReadableContractTypes(),
                'empty_value' => 'Type of contract',
            ])
            ->add('howToApply', 'text')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Job::class,
        ));
    }

    public function getName()
    {
        return 'job';
    }
}
