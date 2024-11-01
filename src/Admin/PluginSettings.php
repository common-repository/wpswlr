<?php

namespace WPSWLR\Admin;

use WPSWLR\Core\InitListener;
use WPSWLR\Core\SettingsService;
use WPSWLR\Core\Utils;
use WPSWLR\Facebook\Facebook;

class PluginSettings implements InitListener
{
	const ASSETS_URL = WPSWLR_BASE_URL . 'assets/admin';
	const SLUG = WPSWLR_KEY;

	/** @var SettingsService $settings_service */
	private $settings_service;

	/**
	 * @param SettingsService $settings_service
	 */
	public function __construct($settings_service)
	{
		$this->settings_service = $settings_service;
	}

	/**
	 * @return void
	 */
	public function on_init()
	{
		if (is_admin() && Utils::user_can_edit()) {
			add_action('admin_menu', [$this, 'add_settings_page']);
			add_action('admin_enqueue_scripts', [$this, 'register_assets']);
		}
	}

	/**
	 * @return void
	 */
	public function add_settings_page()
	{
		$notification_count = 0;
		if (!empty($this->settings_service->get_facebook_info()->update_error)) {
			$notification_count++;
		}
		$menu_title = $notification_count
			? sprintf('WPSwlr <span class="awaiting-mod">%d</span>', $notification_count)
			: 'WPSwlr';

		add_menu_page(
			'WPSwlr',
			$menu_title,
			'manage_options',
			self::SLUG,
			function () {
				echo <<<HTML
	<div class="wrap">
		<div id="progress-bar-container">
	        <div class="progress-bar">
	            <div class="progress-bar-value"></div>
	        </div>
	    </div>
		<div id="wpswlr-r"></div>
	</div>
HTML;
			},
			self::ASSETS_URL . '/logo-20.png'
		);
	}

	/**
	 * @param string $current_page
	 *
	 * @return void
	 */
	public function register_assets($current_page)
	{
		if ($current_page !== "toplevel_page_" . self::SLUG) {
			return;
		}
		remove_action('admin_print_scripts', 'print_emoji_detection_script');
		remove_action('admin_print_styles', 'print_emoji_styles');

		wp_enqueue_style(self::SLUG, self::ASSETS_URL . '/style.css', [], WPSWLR_VERSION);
		wp_enqueue_script(self::SLUG, self::ASSETS_URL . '/app.iife.js', ['jquery'], WPSWLR_VERSION, true);
		wp_set_script_translations(self::SLUG, 'wpswlr');
		wp_localize_script(self::SLUG, 'WPSWLR', [
			'public_url' => WPSWLR_BASE_URL,
			'rest_url' => rest_url(),
			'post_create_url' => add_query_arg(['post_type' => '{post_type}'], admin_url('post-new.php')),
			'post_edit_url' => add_query_arg([
				'post' => '{post_id}',
				'action' => 'edit',
			], admin_url('post.php')),
			'api' => [
				'url' => esc_url_raw(rest_url(RestApi::URL_PREFIX)),
				'nonce' => wp_create_nonce('wp_rest'),
			],
			'facebook_info' => [
				'api_version' => Facebook::GRAPH_VERSION,
				'scope' => implode(',', Facebook::SCOPE),
			],
		]);
	}
}
