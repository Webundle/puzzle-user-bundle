<?php

namespace Puzzle\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Puzzle\UserBundle\Entity\User;
use Symfony\Component\Console\Question\Question;

/**
 * 
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 *
 */
class CreateAdminCommand extends ContainerAwareCommand
{
	private $firstName;
	private $lastName;
	private $email;
	private $password;
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Console\Command\Command::configure()
	 */
	protected function configure()
	{
		$this
    		->setName('puzzle:user:create-admin')
    		->setDescription('Creates a new admin account');
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Console\Command\Command::interact()
	 */
	protected function interact(InputInterface $input, OutputInterface $output)
	{
		$dialog = $this->getHelper('question');
		
		while(!$this->firstName){
		    $this->firstName = $dialog->ask($input, $output, new Question('First Name: '));
		}
		
		$this->lastName = $dialog->ask($input, $output, new Question('Last Name: '));
		
		while(!$this->email){
		    $this->email = $dialog->ask($input, $output, new Question('E-mail: '));
		}
		
		while(!$this->password){
		    $this->password = $dialog->ask($input, $output, new Question('Password: '));
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Console\Command\Command::execute()
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$user = new User();
		$em = $this->getContainer()->get('doctrine.orm.entity_manager');
		
		$user->setEmail($this->email);
		$user->setUsername($this->email);
		$user->setFirstName($this->firstName);
		$user->setLastName($this->lastName);
		$user->setPassword(hash('sha512', $this->password));
		$user->setRoles(array("ROLE_ADMIN"));
		
		$em->persist($user);
		$em->flush();
		
		$output->writeln('Admin account is created !');
		$output->writeln(sprintf('Username <info>%s</info>',$this->email));
		$output->writeln(sprintf('Password <info>%s</info>',$this->password));
	}
}