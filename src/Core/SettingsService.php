<?php

namespace WPSWLR\Core;

use WPSWLR\Core\Settings\FacebookInfo;
use WPSWLR\Core\Settings\FacebookOptions;

class SettingsService implements DeactivateListener, UninstallListener
{

	const FB_OPTIONS_CHANGED = WPSWLR_KEY . '_fb_options_changed';

	public function on_deactivate()
	{
		$info = $this->get_facebook_info();
		$info->update_error = [];
		$this->save_facebook_info($info);
	}

	public function on_uninstall()
	{
		$this->delete_facebook_options();
		$this->delete_facebook_info();
	}

	/**
	 * @return FacebookOptions
	 */
	public function get_facebook_options()
	{
		$options = get_option(FacebookOptions::KEY);

		return is_array($options) ? new FacebookOptions($options) : new FacebookOptions();
	}

	/**
	 * @return FacebookInfo
	 */
	public function get_facebook_info()
	{
		$options = get_option(FacebookInfo::KEY);

		return is_array($options) ? new FacebookInfo($options) : new FacebookInfo();
	}

	/**
	 * @param FacebookOptions $options
	 *
	 * @return bool
	 */
	public function save_facebook_options($options)
	{
		$old_options = $this->get_facebook_options();
		$saved = update_option(FacebookOptions::KEY, $options->to_array());

		if ($saved) {
			do_action(self::FB_OPTIONS_CHANGED, $old_options, $options);
		}

		return $saved;
	}

	/**
	 * @param FacebookInfo $info
	 *
	 * @return bool
	 */
	public function save_facebook_info($info)
	{
		return update_option(FacebookInfo::KEY, $info->to_array());
	}

	/**
	 * @return bool
	 */
	public function delete_facebook_options()
	{
		return delete_option(FacebookOptions::KEY);
	}

	/**
	 * @return bool
	 */
	public function delete_facebook_info()
	{
		return delete_option(FacebookInfo::KEY);
	}
}
