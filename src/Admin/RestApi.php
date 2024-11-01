<?php

namespace WPSWLR\Admin;

use Exception;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WPSWLR\Core\InitListener;
use WPSWLR\Core\Settings\FacebookOptions;
use WPSWLR\Core\SettingsService;
use WPSWLR\Core\Utils;
use WPSWLR\Facebook\Services\ConnectService;
use WPSWLR\Facebook\Services\FacebookLoaderService;
use WPSWLR\Facebook\Services\FacebookPostTemplateService;

class RestApi implements InitListener
{

	const URL_PREFIX = WPSWLR_KEY . '-api/v1';
	const EMPTY_JSON = [];

	/** @var SettingsService $settings_service */
	private $settings_service;
	/** @var FacebookPostTemplateService $post_template_service */
	private $post_template_service;
	/** @var ConnectService  $facebook_connect_service*/
	private $facebook_connect_service;
	/** @var FacebookLoaderService $facebook_loader */
	private $facebook_loader;

	/**
	 * @param SettingsService $settings_service
	 * @param FacebookPostTemplateService $post_template_service
	 * @param ConnectService $facebook_connect_service
	 * @param FacebookLoaderService $facebook_loader
	 */
	public function __construct($settings_service, $post_template_service, $facebook_connect_service, $facebook_loader)
	{
		$this->settings_service = $settings_service;
		$this->post_template_service = $post_template_service;
		$this->facebook_connect_service = $facebook_connect_service;
		$this->facebook_loader = $facebook_loader;
	}

	public function on_init()
	{
		if (Utils::user_can_edit()) {
			add_action('rest_api_init', [$this, 'add_routes']);
		}
	}

	/**
	 * @return void
	 */
	public function add_routes()
	{
		register_rest_route(
			self::URL_PREFIX,
			'/facebook/options',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [$this, 'get_facebook_options'],
					'permission_callback' => [$this, 'permissions'],
				],
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [$this, 'save_facebook_options'],
					'permission_callback' => [$this, 'permissions'],
					'args' => [
						'load_album_posts' => [
							'type' => 'boolean',
						],
						'load_video_posts' => [
							'type' => 'boolean',
						],
						'load_thumbnails' => [
							'type' => 'boolean',
						],
						'load_thumbnails_album' => [
							'type' => 'boolean',
						],
						'load_include_patterns' => [
							'type' => 'string',
							'validate_callback' => function ($param, $request, $key) {
								if (empty($param)) {
									return true;
								}
								$v = json_decode($param);

								return (!json_last_error()) && is_array($v);
							},
						],
						'load_exclude_patterns' => [
							'type' => 'string',
							'validate_callback' => function ($param, $request, $key) {
								if (empty($param)) {
									return true;
								}
								$v = json_decode($param);

								return (!json_last_error()) && is_array($v);
							},
						],
						'posts_slug' => [
							'type' => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ($param, $request, $key) {
								if (empty($param) || strlen($param) < 3 || strlen($param) > 20) {
									return false;
								}

								return preg_match('/^[a-z][a-z\-]+[a-z]$/', $param);
							},
						],
						'posts_limit' => [
							'type' => 'integer',
							'required' => true,
							'minimum' => 0,
						],
						'posts_title' => [
							'type' => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						],
						'post_category_general' => [
							'type' => 'integer',
						],
						'post_category_album' => [
							'type' => 'integer',
						],
						'post_category_video' => [
							'type' => 'integer',
						],
						'post_excerpt' => [
							'type' => 'boolean',
						],
						'post_excerpt_length' => [
							'type' => 'integer',
							'required' => true,
							'minimum' => 5,
						],
						'post_excerpt_text' => [
							'type' => 'string',
							'required' => true,
							'sanitize_callback' => 'sanitize_text_field',
						],
					],
				],
			]
		);

		register_rest_route(
			self::URL_PREFIX,
			'/facebook/info',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [$this, 'get_facebook_info'],
					'permission_callback' => [$this, 'permissions'],
				],
			]
		);

		register_rest_route(
			self::URL_PREFIX,
			'/facebook/connect',
			[
				[
					'methods' => WP_REST_Server::CREATABLE,
					'callback' => [$this, 'facebook_connect'],
					'permission_callback' => [$this, 'permissions'],
					'args' => [
						'id' => [
							'type' => 'string',
							'required' => true,
							'sanitize_callback' => 'sanitize_text_field',
						],
						'token' => [
							'type' => 'string',
							'required' => true,
							'sanitize_callback' => 'sanitize_text_field',
						],
					],
				],
			]
		);

		register_rest_route(
			self::URL_PREFIX,
			'/facebook/disconnect',
			[
				[
					'methods' => WP_REST_Server::CREATABLE,
					'callback' => [$this, 'facebook_disconnect'],
					'permission_callback' => [$this, 'permissions'],
				],
			]
		);

		register_rest_route(
			self::URL_PREFIX,
			'/facebook/update',
			[
				[
					'methods' => WP_REST_Server::CREATABLE,
					'callback' => [$this, 'update_facebook'],
					'permission_callback' => [$this, 'permissions'],
				],
			]
		);
	}

	/**
	 * @return bool
	 */
	public function permissions()
	{
		return Utils::user_can_edit();
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_HTTP_Response
	 */
	public function get_facebook_options($request)
	{
		$options = $this->settings_service->get_facebook_options()->to_array();
		$options['templates'] = $this->post_template_service->get_templates();

		return new WP_REST_Response($options);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_HTTP_Response
	 *
	 */
	public function save_facebook_options($request)
	{
		$this->settings_service->save_facebook_options(new FacebookOptions($request->get_params()));

		return new WP_REST_Response(self::EMPTY_JSON);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_HTTP_Response
	 */
	public function get_facebook_info($request)
	{
		return new WP_REST_Response($this->settings_service->get_facebook_info()->to_array());
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_HTTP_Response
	 */
	public function facebook_connect($request)
	{
		try {
			$this->facebook_connect_service->connect($request->get_param('id'), $request->get_param('token'));
		} catch (Exception $e) {
			return new WP_REST_Response(['message' => $e->getMessage()], 500);
		}

		return new WP_REST_Response(self::EMPTY_JSON);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_HTTP_Response
	 */
	public function facebook_disconnect($request)
	{
		$this->facebook_connect_service->disconnect();

		return new WP_REST_Response(self::EMPTY_JSON);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_HTTP_Response
	 */
	public function update_facebook($request)
	{
		try {
			$this->facebook_loader->load();
		} catch (Exception $e) {
			return new WP_REST_Response(['message' => $e->getMessage()], 500);
		}

		return new WP_REST_Response(self::EMPTY_JSON);
	}
}
