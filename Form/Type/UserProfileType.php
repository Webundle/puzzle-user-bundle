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
class UserProfileType extends AbstractUserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->remove('email')
            ->remove('password')
            ->remove('accountExpiresAt')
            ->remove('credentialsExpiresAt')
            ->remove('enabled')
            ->remove('locked')
        ;
    }
    
    
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        
        $resolver->setDefault('csrf_token_id', 'user_update_profile');
        $resolver->setDefault('validation_groups', ['Register']);
    }
    
    public function getBlockPrefix() {
        return 'user_update_profile';
    }
}