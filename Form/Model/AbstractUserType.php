<?php

namespace Puzzle\UserBundle\Form\Model;

use Puzzle\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * @author AGNES Gnagne Cédric <cecenho55@gmail.com>
 */
class AbstractUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('email', EmailType::class)
            ->add('phoneNumber', TextType::class, ['required' => false])
            ->add('picture', HiddenType::class)
            ->add('username', TextType::class)
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'options' => ['required' => false],
                'first_options'  => [],
                'second_options'  => [],
                'required' => false
            ))
            ->add('credentialsExpiresAt', TextType::class, [
                'mapped' => false,
                'required' => false
            ])
            ->add('accountExpiresAt', TextType::class, [
                'mapped' => false,
                'required' => false
            ])
            ->add('enabled', CheckboxType::class, array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('locked', CheckboxType::class, array(
                'required' => false,
                'mapped' => false,
            ))
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults(array(
            'data_class' => User::class,
            'validation_groups' => array(
                User::class,
                'determineValidationGroups',
            ),
        ));
    }
}