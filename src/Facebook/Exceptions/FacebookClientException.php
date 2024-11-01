<?php

namespace WPSWLR\Facebook\Exceptions;

use WP_Error;

class FacebookClientException extends FacebookException
{

	/** @var string|null $type */
	protected $type = null;
	/** @var int|null $sub_code */
	protected $sub_code = null;

	/**
	 * Create Exception from WordPress Error Object
	 *
	 * @param WP_Error $error
	 *
	 * @return static
	 */
	public static function of_wp_error($error)
	{
		$message = $error->get_error_message();

		return new static($message, $error->get_error_code());
	}

	/**
	 * Create Exception from Facebook Graph API response array
	 *
	 * @param array $response
	 *
	 * @return static
	 */
	public static function of_response($response)
	{
		$e = new static($response['message'], intval($response['code'] ?? 0));
		$e->type = $response['type'] ?? 'Unknown';
		$e->sub_code = intval($response['error_subcode'] ?? 0);

		return $e;
	}

	/**
	 * @return string|null
	 */
	public function get_type()
	{
		return $this->type;
	}

	/**
	 * @return int|null
	 */
	public function get_sub_code()
	{
		return $this->sub_code;
	}
}
