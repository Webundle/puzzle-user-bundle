<?php

namespace Puzzle\UserBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Puzzle\UserBundle\Form\Model\AbstractUserType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

/**
 * 
 * @author AGNES Gnagne Cédric <cecenho55@gmail.com>
 * 
 */
class UserChangePasswordType extends AbstractUserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('currentPassword', PasswordType::class, [
                'required' => true,
                'mapped' => false
            ])
            ->remove('phoneNumber')
            ->remove('picture')
            ->remove('email')
            ->remove('lastName')
            ->remove('firstName')
            ->remove('accountExpiresAt')
            ->remove('credentialsExpiresAt')
            ->remove('enabled')
            ->remove('locked')
            ->remove('username')
        ;
    }
    
    
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        
        $resolver->setDefault('csrf_token_id', 'user_change_password');
        $resolver->setDefault('validation_groups', ['Update']);
    }
    
    public function getBlockPrefix() {
        return 'user_change_password';
    }
}