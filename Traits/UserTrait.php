<?php
namespace Puzzle\UserBundle\Traits;

use Doctrine\ORM\Mapping as ORM;
use Puzzle\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;

/**
 * UserTrait
 *
 * @author AGNES Gnagne Cedric <cecenho55@gmail@com>
 */
trait UserTrait
{
    /**
     * @ORM\Column(name="creator", type="string", nullable=true)
     * @var string
     */
    private $creator;
    
    /**
     * @ORM\Column(name="last_editor", type="string", nullable=true)
     * @var string
     */
    private $lastEditor;
    
    public function setCreator($creator) : self {
        $this->creator = $creator;
        $this->lastEditor = $creator;
        return $this;
    }
    
    public function getCreator() {
        return $this->creator;
    }
    
    public function setLastEditor($lastEditor) {
        $this->lastEditor = $lastEditor;
        return $this;
    }
    
    public function getLastEditor() {
        return $this->lastEditor;
    }
    
    public function isCreator(User $user) {
        return $this->creator === $user;
    }
    
}
