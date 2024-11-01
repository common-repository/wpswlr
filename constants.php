<?php

if (!defined('WPSWLR_KEY')) {
	define('WPSWLR_KEY', 'wpswlr');
}

if (!defined('WPSWLR_VERSION')) {
	define('WPSWLR_VERSION', '1.2.9');
}

if (!defined('WPSWLR_BASE_PATH')) {
	define('WPSWLR_BASE_PATH', __DIR__ . DIRECTORY_SEPARATOR);
}

if (!defined('WPSWLR_BASE_URL')) {
	if (function_exists('plugin_dir_url')) {
		define('WPSWLR_BASE_URL', plugin_dir_url(__FILE__));
	} else {
		define('WPSWLR_BASE_URL', '');
	}
}
