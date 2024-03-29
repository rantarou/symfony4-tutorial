<?php

namespace App\Security;

class TokenGenerator
{
	private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz01234567890';

	public function getRandomSecureToken(int $length): string 
	{
		$maxNumber = strlen(self::ALPHABET);
		$token = '';

		for ($i = 0; $i < $length; $i++) {
			$token .= self::ALPHABET[random_int(0, $maxNumber - 1)];
		}

		return $token;
	}
}