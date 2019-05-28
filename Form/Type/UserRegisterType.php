<?php

namespace Puzzle\UserBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Puzzle\UserBundle\Form\Model\AbstractUserType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * 
 * @author AGNES Gnagne Cédric <cecenho55@gmail.com>
 * 
 */
class UserRegisterType extends AbstractUserType
{
    public function buildForm(FormBuilderInterface $builder, array $options){
        parent::buildForm($builder, $options);
        $builder
            ->remove('phoneNumber')
            ->remove('credentialsExpiresAt')
            ->remove('accountExpiresAt')
            ->remove('enabled')
            ->remove('picture')
            ->remove('locked');
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        
        $resolver->setDefault('csrf_token_id', 'user_register');
        $resolver->setDefault('validation_groups', ['Register']);
    }
    
    public function getBlockPrefix() {
        return 'app_user_register';
    }
}