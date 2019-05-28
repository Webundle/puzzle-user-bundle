<?php
namespace Puzzle\UserBundle\Twig;

use Doctrine\ORM\EntityManager;
use Puzzle\UserBundle\Entity\User;
use Puzzle\UserBundle\Entity\Group;

/**
 *
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 *
 */
class UserExtension extends \Twig_Extension
{
	/**
	 * @var EntityManager $em
	 */
    protected $em;
	
	public function __construct(EntityManager $em) {
	    $this->em = $em;
	}
	
	public function getFunctions() {
		return [
		    new \Twig_SimpleFunction('puzzle_user_groups', [$this, 'getGroups'], ['needs_environment' => false, 'is_safe' => ['html']]),
		    new \Twig_SimpleFunction('puzzle_user_group', [$this, 'getGroups'], ['needs_environment' => false, 'is_safe' => ['html']]),
		    new \Twig_SimpleFunction('puzzle_users', [$this, 'getUsers'], ['needs_environment' => false, 'is_safe' => ['html']]),
		    new \Twig_SimpleFunction('puzzle_user', [$this, 'getUser'], ['needs_environment' => false, 'is_safe' => ['html']])
		];
	}
	
	public function getUserById($id) {
	    try {
	        return $this->em->getRepository(User::class)->find($id);
	    } catch (\Exception $e) {
	        return null;
	    }
	}
	
	public function getGroups(array $fields = [], array $joins =[], array $criteria = [], array $orderBy = ['createdAt' => 'DESC'], $limit = null, int $page = 1) {
	    if (is_int($limit) === true) {
	        $query = $this->em->getRepository(Group::class)->customGetQuery($fields, $joins, $criteria, $orderBy, null, null);
	        return $this->paginator->paginate($query, $page, $limit);
	    }
	    
	    return  $this->em->getRepository(Group::class)->customFindBy($fields, $joins, $criteria, $orderBy, null, null);
	}
	
	public function getGroup($id) {
	    if (!$group = $this->em->find(Group::class, $id)) {
	        $group =  $this->em->getRepository(Group::class)->findOneBy(['slug' => $id]);
	    }
	    
	    return $group;
	}
	
	public function getUsers(array $fields = [], array $joins =[], array $criteria = [], array $orderBy = ['createdAt' => 'DESC'], $limit = null, int $page = 1) {
	    if (is_int($limit) === true) {
	        $query = $this->em->getRepository(User::class)->customGetQuery($fields, $joins, $criteria, $orderBy, null, null);
	        return $this->paginator->paginate($query, $page, $limit);
	    }
	    
	    return  $this->em->getRepository(User::class)->customFindBy($fields, $joins, $criteria, $orderBy, null, null);
	}
	
	public function getUser($id) {
	    if (!$user = $this->em->find(User::class, $id)) {
	        $user =  $this->em->getRepository(User::class)->findOneBy(['email' => $id]);
	    }
	    
	    return $user;
	}
}
