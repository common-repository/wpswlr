<?php

namespace WPSWLR\Facebook\Loaders;

use WPSWLR\Core\SettingsService;
use WPSWLR\Facebook\Client\FacebookClient;

class InfoLoader implements FacebookLoader
{

	/** @var SettingsService $settings_service */
	private $settings_service;
	/** @var FacebookClient $facebook_client */
	private $facebook_client;

	/**
	 * @param SettingsService $settings_service
	 * @param FacebookClient $facebook_client
	 */
	public function __construct($settings_service, $facebook_client)
	{
		$this->settings_service = $settings_service;
		$this->facebook_client = $facebook_client;
	}

	public function load()
	{
		$page_data = $this->facebook_client->get_page_info();

		$info = $this->settings_service->get_facebook_info();
		$info->name = $page_data['name'];
		$info->about = $page_data['about'];
		$info->picture = $page_data['picture']['data']['url'] ?? null;

		$this->settings_service->save_facebook_info($info);
	}

	public function options_changed($prev_options, $options)
	{
		// nothing to do
	}

	public function clean()
	{
		// nothing to clean
	}
}
