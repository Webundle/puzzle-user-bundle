<?php

namespace Puzzle\UserBundle\Service;

/**
 * 
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 *
 */
class Utils
{
	/**
	 * sha512 encoder
	 * 
	 * @param string $pass
	 * @param string $salt
	 * @return string
	 */
	public static function sha512(string $pass, string $salt)
	{
		$iterations = 5000; // Par défaut
		$salted = $pass.'{'.$salt.'}';
		
		$digest = hash('sha512', $salted, true);
		for ($i = 1; $i < $iterations; $i++) {
			$digest = hash('sha512', $digest.$salted, true);
		}
		
		return base64_encode($digest);
	}
}