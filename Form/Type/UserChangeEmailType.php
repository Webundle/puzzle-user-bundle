<?php

namespace Puzzle\UserBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Puzzle\UserBundle\Form\Model\AbstractUserType;

/**
 * 
 * @author AGNES Gnagne Cédric <cecenho55@gmail.com>
 * 
 */
class UserChangeEmailType extends AbstractUserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->remove('phoneNumber')
            ->remove('picture')
            ->remove('password')
            ->remove('lastName')
            ->remove('firstName')
            ->remove('accountExpiresAt')
            ->remove('credentialsExpiresAt')
            ->remove('enabled')
            ->remove('locked')
        ;
    }
    
    
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        
        $resolver->setDefault('csrf_token_id', 'user_change_email');
        $resolver->setDefault('validation_groups', ['Update']);
    }
    
    public function getBlockPrefix() {
        return 'user_change_email';
    }
}