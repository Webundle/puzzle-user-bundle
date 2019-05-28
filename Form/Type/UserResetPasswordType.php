<?php
namespace Puzzle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * @author AGNES Gnagne Cédric <cedric.agnes@veone.net>
 * 
 */
class UserResetPasswordType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
				->add('password', Type\RepeatedType::class, [
						'required' => true,
						'type' => Type\PasswordType::class,
						'options' => [
							'translation_domain' => 'app'
						],
						'first_options' => [
							'label' => 'user.property.password',
					        'required' => true
						],
						'second_options' => [
							'label' => 'user.property.password_confirmation',
					        'required' => true
						]
					])
				;
	}
	
	public function configureOptions(OptionsResolver $resolver) {
	    parent::configureOptions($resolver);
	    
	    $resolver->setDefault('csrf_token_id', 'user_reset_password');
	    $resolver->setDefault('validation_groups', ['Reset']);
	}
	
	public function getBlockPrefix() {
	    return 'app_user_reset_password';
	}
}
