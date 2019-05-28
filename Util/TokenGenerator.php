<?php
namespace Puzzle\UserBundle\Util;

final class TokenGenerator
{
	/**
	 * @param int $randomLength
	 * @return string
	 */
	public static function generate(int $randomLength = 3) :string {
		return rtrim(strtr(base64_encode(random_bytes($randomLength)), '+/', '-_'), '=');
	}
}

