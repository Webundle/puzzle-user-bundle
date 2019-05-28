<?php 

namespace Puzzle\UserBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Puzzle\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 */
class UserEvent extends Event
{
	
	/**
	 * @var User $user
	 */
	protected $user;
	
	/**
	 * @var Response $response
	 */
	protected $response;
	
	/**
	 * @var array $data
	 */
	protected $data;
	
	
	public function __construct(User $user, $data = null) {
	    $this->user = $user;
	    $this->data = $data;
	}
	
	public function getUser() :User {
	    return $this->user;
	}
	
	public function getResponse() :?Response {
	    return $this->response;
	}
	
	public function setResponse(Response $response) :self {
	    $this->response = $response;
	    return $this;
	}
	
	public function getData(){
	    return $this->data;
	}
}

?>