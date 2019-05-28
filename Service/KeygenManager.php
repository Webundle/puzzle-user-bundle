<?php

namespace Puzzle\UserBundle\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;

/**
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 */
class KeygenManager extends AbstractIdGenerator
{
	static $cache = array();
	
	public function generate(EntityManager $entityManager, $entity)
	{
		$prefix = mb_substr(md5(uniqid()), 0, 10);
		$date = new \DateTime();
		$value = $prefix.$date->getTimestamp();

		return $value;
	}
}