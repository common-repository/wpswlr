<?php

namespace WPSWLR\Facebook\Services;

use WPSWLR\Core\SettingsService;
use WPSWLR\Facebook\Client\FacebookClient;
use WPSWLR\Facebook\Exceptions\FacebookClientException;
use WPSWLR\Facebook\Exceptions\FacebookException;

class ConnectService
{

	const PAGE_CONNECTED_ACTION = WPSWLR_KEY . '_facebook_connected';
	const PAGE_DISCONNECTED_ACTION = WPSWLR_KEY . '_facebook_disconnected';

	/** @var FacebookClient $facebook_client */
	private $facebook_client;
	/** @var SettingsService $settings_service */
	private $settings_service;

	/**
	 * @param FacebookClient $facebook_client
	 * @param SettingsService $settings_service
	 */
	public function __construct($facebook_client, $settings_service)
	{
		$this->facebook_client = $facebook_client;
		$this->settings_service = $settings_service;
	}

	/**
	 * @param string $page_id
	 * @param string $access_token
	 *
	 * @return void
	 *
	 * @throws FacebookClientException
	 * @throws FacebookException
	 */
	public function connect($page_id, $access_token)
	{
		$saved_settings = $this->settings_service->get_facebook_info();
		if (!empty($saved_settings->id) && $saved_settings->id !== $page_id) {
			throw FacebookClientException::of(__('You must disconnect existing page before connecting a new one.', 'wpswlr'));
		}

		$page_data = $this->facebook_client->get_page_info($access_token, $page_id);

		$info = $this->settings_service->get_facebook_info();
		$info->id = $page_id;
		$info->name = $page_data['name'];
		$info->token = $access_token;
		$info->about = $page_data['about'];
		$info->picture = $page_data['picture']['data']['url'] ?? null;

		$this->settings_service->save_facebook_info($info);

		do_action(self::PAGE_CONNECTED_ACTION);
	}

	/**
	 * @return void
	 */
	public function disconnect()
	{
		$this->settings_service->delete_facebook_info();

		do_action(self::PAGE_DISCONNECTED_ACTION);
	}
}
