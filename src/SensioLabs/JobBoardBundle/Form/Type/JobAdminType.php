<?php

namespace SensioLabs\JobBoardBundle\Form\Type;

use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class JobAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('publishedAt', 'datetime', [
                'date_widget' => 'single_text',
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy',
            ])
            ->add('endedAt', 'datetime', [
                'date_widget' => 'single_text',
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy',
            ])
            ->add('isValidated')
        ;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['isAdmin'] = true;
    }

    public function getName()
    {
        return 'job_admin';
    }

    public function getParent()
    {
        return 'job';
    }
}
