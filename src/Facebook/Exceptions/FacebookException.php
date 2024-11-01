<?php

namespace WPSWLR\Facebook\Exceptions;

use Exception;

class FacebookException extends Exception
{

	/**
	 * @param string $message
	 * @param Exception|null $previous
	 *
	 * @return static
	 */
	public static function of($message = "", $previous = null)
	{
		return new static ($message, 0, $previous);
	}
}
