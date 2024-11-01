<?php

namespace WPSWLR\Core\Settings;

/**
 * @property string|null $id
 * @property string|null $name
 * @property string|null $token
 * @property string|null $about
 * @property string|null $picture
 * @property string[] $update_error
 */
class FacebookInfo extends AbstractSettings
{

	const KEY = WPSWLR_KEY . '_settings_facebook_info';

	const DEFAULTS = [
		'id' => null,
		'name' => null,
		'token' => null,
		'about' => null,
		'picture' => null,
		'update_error' => [],
	];

	protected function get_defaults()
	{
		return self::DEFAULTS;
	}
}
