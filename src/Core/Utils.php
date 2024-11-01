<?php

namespace WPSWLR\Core;

class Utils
{

	/**
	 * @param array $params
	 * @param bool $prefix
	 *
	 * @return string
	 */
	public static function qs($params = [], $prefix = true)
	{
		if (empty($params)) {
			return '';
		}
		$qs = [];
		foreach ($params as $key => $value) {
			$qs[] = $key . '=' . urlencode($value);
		}
		$p = $prefix ? '?' : '';

		return $p . implode('&', $qs);
	}

	/**
	 * @return bool
	 */
	public static function user_can_edit()
	{
		return current_user_can('manage_options');
	}

	/**
	 * @return string
	 */
	public static function wp_minor_version()
	{
		$version = get_bloginfo('version');
		$version = explode('.', $version);

		return $version[0] . '.' . ($version[1] ?? '0');
	}
}
