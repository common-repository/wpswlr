<?php

namespace WPSWLR\Facebook\Services;

use Exception;
use WPSWLR\Core\ActivateListener;
use WPSWLR\Core\DeactivateListener;
use WPSWLR\Core\InitListener;
use WPSWLR\Core\Logger;
use WPSWLR\Core\Settings\FacebookOptions;
use WPSWLR\Core\SettingsService;
use WPSWLR\Core\Utils;
use WPSWLR\Facebook\Client\FacebookClient;
use WPSWLR\Facebook\Exceptions\FacebookClientException;
use WPSWLR\Facebook\Exceptions\FacebookException;
use WPSWLR\Facebook\Loaders\FacebookLoader;
use WPSWLR\Facebook\Loaders\InfoLoader;
use WPSWLR\Facebook\Loaders\PostContentRenderer;
use WPSWLR\Facebook\Loaders\PostsLoader;

class FacebookLoaderService implements InitListener, ActivateListener, DeactivateListener
{

	const CRON_INFO_HOOK_NAME = 'wpswlr_facebook_load_info';
	const CRON_POSTS_HOOK_NAME = 'wpswlr_facebook_load_posts';

	/** @var SettingsService $settings_service */
	private $settings_service;
	/** @var  FacebookLoader[] $loaders */
	private $loaders;

	/**
	 * @param SettingsService $settings_service
	 * @param FacebookClient $facebook_client
	 * @param PostContentRenderer $content_formatter
	 */
	public function __construct($settings_service, $facebook_client, $content_formatter)
	{
		$this->settings_service = $settings_service;
		$this->loaders = [
			'info' => new InfoLoader($settings_service, $facebook_client),
			'posts' => new PostsLoader($settings_service, $facebook_client, $content_formatter),
		];
	}

	public function on_init()
	{
		if (Utils::user_can_edit()) {
			add_action(SettingsService::FB_OPTIONS_CHANGED, [$this, 'options_changed'], 10, 2);
			add_action(ConnectService::PAGE_CONNECTED_ACTION, [$this, 'page_connected']);
			add_action(ConnectService::PAGE_DISCONNECTED_ACTION, [$this, 'page_disconnected']);
		}

		add_action(self::CRON_INFO_HOOK_NAME, [$this, 'cron_info']);
		add_action(self::CRON_POSTS_HOOK_NAME, [$this, 'cron_posts']);
	}

	public function on_activate()
	{
		if (!empty($this->settings_service->get_facebook_info()->id)) {
			$this->schedule_cron();
		}
	}

	public function on_deactivate()
	{
		$this->unschedule_cron();
		$this->clean_loaders();
	}

	/**
	 * @return void
	 */
	public function page_connected()
	{
		$this->schedule_cron();
	}

	/**
	 * @return void
	 */
	public function page_disconnected()
	{
		$this->unschedule_cron();
		$this->clean_loaders();
	}

	/**
	 * @return void
	 */
	private function schedule_cron()
	{
		if (!wp_next_scheduled(self::CRON_INFO_HOOK_NAME)) {
			wp_schedule_event(time(), 'daily', self::CRON_INFO_HOOK_NAME);
		}
		if (!wp_next_scheduled(self::CRON_POSTS_HOOK_NAME)) {
			wp_schedule_event(time(), 'twicedaily', self::CRON_POSTS_HOOK_NAME);
		}
	}

	/**
	 * @return void
	 */
	private function unschedule_cron()
	{
		$timestamp = wp_next_scheduled(self::CRON_INFO_HOOK_NAME);
		if ($timestamp) {
			wp_unschedule_event($timestamp, self::CRON_INFO_HOOK_NAME);
		}

		$timestamp = wp_next_scheduled(self::CRON_POSTS_HOOK_NAME);
		if ($timestamp) {
			wp_unschedule_event($timestamp, self::CRON_POSTS_HOOK_NAME);
		}
	}

	/**
	 * @return void
	 */
	private function clean_loaders()
	{
		foreach ($this->loaders as $loader) {
			$loader->clean();
		}
	}

	/**
	 * @return void
	 */
	public function cron_info()
	{
		$info = $this->settings_service->get_facebook_info();
		if (empty($info->id) || empty($info->token)) {
			return;
		}
		try {
			$this->loaders['info']->load();
			$this->update_error_message('info', null);
		} catch (Exception $e) {
			Logger::log(__('Failed to load Facebook data', 'wpswlr'), $e);
			$this->update_error_message('info', $e->getMessage());
		}
	}

	/**
	 * @return void
	 */
	public function cron_posts()
	{
		$info = $this->settings_service->get_facebook_info();
		if (empty($info->id) || empty($info->token)) {
			return;
		}
		try {
			$this->loaders['posts']->load();
			$this->update_error_message('posts', null);
		} catch (Exception $e) {
			Logger::log(__('Failed to load Facebook data', 'wpswlr'), $e);
			$this->update_error_message('posts', $e->getMessage());
		}
	}

	/**
	 * @return void
	 *
	 * @throws FacebookClientException
	 * @throws FacebookException
	 * @throws Exception
	 */
	public function load()
	{
		foreach ($this->loaders as $key => $loader) {
			try {
				$loader->load();
				$this->update_error_message($key, null);
			} catch (Exception $e) {
				$this->update_error_message($key, $e->getMessage());
				throw $e;
			}
		}
	}

	/**
	 * @param FacebookOptions $prev_options
	 * @param FacebookOptions $options
	 *
	 * @return void
	 */
	public function options_changed($prev_options, $options)
	{
		foreach ($this->loaders as $loader) {
			$loader->options_changed($prev_options, $options);
		}
	}

	/**
	 * @param string $key
	 * @param string|null $message
	 */
	private function update_error_message($key, $message)
	{
		$info = $this->settings_service->get_facebook_info();
		$err = $info->update_error;
		if (!is_array($err)) {
			$err = [];
		}
		if ($message) {
			$err[$key] = $message;
		} else {
			unset($err[$key]);
		}
		$info->update_error = $err;
		$this->settings_service->save_facebook_info($info);
	}
}
