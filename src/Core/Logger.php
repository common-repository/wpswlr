<?php

namespace WPSWLR\Core;

use Exception;
use WPSWLR\Facebook\Exceptions\FacebookClientException;
use WPSWLR\Facebook\Exceptions\FacebookException;

class Logger
{

	const PREFIX = WPSWLR_KEY . ": ";

	/**
	 * @param string $message
	 * @param mixed $meta
	 *
	 * @return void
	 */
	public static function log($message, $meta = null)
	{
		if ($meta === null) {
			$context_string = '';
		} else if ($meta instanceof FacebookClientException) {
			$context_string = ': ' . $meta->getMessage() . ' ('
				. $meta->getCode() . ':' . ($meta->get_type() ?? '') . ':' . ($meta->get_sub_code() ?? 0) . ")\n"
				. $meta->getTraceAsString();
		} else if ($meta instanceof FacebookException) {
			$context_string = ': ' . $meta->getMessage() . "\n" . $meta->getTraceAsString();
		} else if ($meta instanceof Exception) {
			$context_string = $meta->getMessage() . ' ('
				. $meta->getCode() . ') '
				. $meta->getFile() . ':' . $meta->getLine() . "\n"
				. $meta->getTraceAsString();
		} else if (is_string($meta) || is_numeric($meta) || is_bool($meta)) {
			$context_string = " <$meta>";
		} else {
			$context_string = "\n" . json_encode($meta);
		}

		error_log(self::PREFIX . " $message$context_string");
	}
}
