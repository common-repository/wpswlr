<?php

namespace WPSWLR\Facebook\Client;

use WP_Error;
use WPSWLR\Core\SettingsService;
use WPSWLR\Core\Utils;
use WPSWLR\Facebook\Client\Model\Edge;
use WPSWLR\Facebook\Client\Model\Post;
use WPSWLR\Facebook\Client\Model\Video;
use WPSWLR\Facebook\Exceptions\FacebookClientException;
use WPSWLR\Facebook\Facebook;

class FacebookClient
{

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
	 * @param string|null $after
	 *
	 * @return Edge
	 * @throws FacebookClientException
	 */
	public function get_posts_edge($after = null)
	{
		$params = [
			'limit' => 100,
			'fields' => implode(',', Post::FACEBOOK_FIELDS),
		];
		if ($after) {
			$params['after'] = $after;
		}
		$facebook_info = $this->settings_service->get_facebook_info();
		if (empty($facebook_info->token)) {
			throw FacebookClientException::of(__('Missing Facebook Token', 'wpswlr'));
		}

		$edge_data = $this->send_request($facebook_info->token, $facebook_info->id . '/posts', $params);

		if (!is_array($edge_data) || !is_array($edge_data['data'])) {
			throw FacebookClientException::of(__('Wrong Facebook response', 'wpswlr'));
		}

		return Edge::from_data($edge_data)->map(function ($data) {
			return Post::from_data($data);
		});
	}

	/**
	 * @param string $id
	 *
	 * @return Video
	 * @throws FacebookClientException
	 */
	public function get_video($id)
	{
		$params = [
			'fields' => implode(',', Video::FACEBOOK_FIELDS),
		];
		$facebook_info = $this->settings_service->get_facebook_info();
		if (empty($facebook_info->token)) {
			throw FacebookClientException::of(__('Missing Facebook Token', 'wpswlr'));
		}

		$video_data = $this->send_request($facebook_info->token, $id, $params);

		if (!is_array($video_data)) {
			throw FacebookClientException::of(__('Wrong Facebook response', 'wpswlr'));
		}

		return Video::from_data($video_data);
	}

	/**
	 * @param string|null $access_token
	 * @param int|null $page_id
	 *
	 * @return array
	 * @throws FacebookClientException
	 */
	public function get_page_info($access_token = null, $page_id = null)
	{
		if (empty($access_token) && empty($page_id)) {
			$info = $this->settings_service->get_facebook_info();
			$access_token = $info->token;
			$page_id = $info->id;
		}
		if (empty($access_token)) {
			throw FacebookClientException::of(__('Missing Facebook Token', 'wpswlr'));
		}
		if (empty($page_id)) {
			throw FacebookClientException::of(__('Missing Facebook Page ID', 'wpswlr'));
		}

		return $this->send_request($access_token, $page_id, [
			'fields' => 'id,name,about,picture{url}',
		]);
	}

	/**
	 * @param string $access_token
	 * @param string $endpoint
	 * @param array $params
	 *
	 * @return array
	 * @throws FacebookClientException
	 */
	public function send_request($access_token, $endpoint, $params = [])
	{
		$url = Facebook::GRAPH_URL . $endpoint . Utils::qs($params);
		$headers = [];
		if (!empty($access_token)) {
			$headers['Authorization'] = "Bearer $access_token";
		}
		$response = wp_remote_get($url, [
			'headers' => $headers,
		]);

		$body = wp_remote_retrieve_body($response);
		if ($body instanceof WP_Error) {
			throw FacebookClientException::of_wp_error($body);
		}
		$data = json_decode($body, true, 128, JSON_OBJECT_AS_ARRAY);
		if (json_last_error() !== JSON_ERROR_NONE) {
			throw FacebookClientException::of(sprintf(__('Wrong Facebook response: %s', 'wpswlr'), json_last_error_msg()));
		}
		if (!is_array($data)) {
			throw FacebookClientException::of(__('Wrong Facebook response', 'wpswlr'));
		}
		if (isset($data['error']) && is_array($data['error'])) {
			throw FacebookClientException::of_response($data['error']);
		}

		return $data;
	}
}
