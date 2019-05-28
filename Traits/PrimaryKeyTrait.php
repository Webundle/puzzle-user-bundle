<?php
namespace Puzzle\UserBundle\Traits;

use Doctrine\ORM\Mapping as ORM;
use Puzzle\UserBundle\Entity\User;

/**
 * PrimaryKeyTrait
 *
 * @author AGNES Gnagne Cedric <cecenho55@gmail@com>
 */
trait PrimaryKeyTrait
{
    /**
	 * @ORM\Column(name="id", type="string")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="CUSTOM")
	 * @ORM\CustomIdGenerator(class="Puzzle\UserBundle\Service\KeygenManager")
	 */
	private $id;
    
    public function getId() {
       return $this->id;
    }
    
    public function clearId() {
        $this->id = null;
        return $this;
    }
}
