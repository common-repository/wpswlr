<?php

namespace WPSWLR\Facebook\Loaders;

use WP_Post;
use WP_Query;
use WPSWLR\Core\Settings\FacebookOptions;
use WPSWLR\Core\SettingsService;
use WPSWLR\Facebook\Client\FacebookClient;
use WPSWLR\Facebook\Client\Model\Post;
use WPSWLR\Facebook\Exceptions\FacebookClientException;
use WPSWLR\Facebook\PostTypes\FacebookPost;

class PostsLoader implements FacebookLoader
{
	/** @var FacebookClient $facebook_client */
	private $facebook_client;
	/** @var PostContentRenderer $content_formatter */
	private $content_formatter;
	/** @var FacebookOptions $options */
	private $options;

	/**
	 * @param SettingsService $settings_service
	 * @param FacebookClient $facebook_client
	 * @param PostContentRenderer $content_formatter
	 */
	public function __construct($settings_service, $facebook_client, $content_formatter)
	{
		$this->facebook_client = $facebook_client;
		$this->content_formatter = $content_formatter;
		$this->options = $settings_service->get_facebook_options();
	}

	public function load()
	{
		/* @var $loaded_posts Post[] */
		$loaded_posts = [];
		foreach ($this->fetch_posts() as $loaded_post) {
			$loaded_posts[$loaded_post->get_id()] = $loaded_post;
		}
		/* @var $wp_posts WP_Post[] */
		$wp_posts = [];
		foreach ($this->get_wp_posts() as $wp_post) {
			$wp_posts[PostUtils::get_external_id($wp_post)] = $wp_post;
		}

		// insert/update posts
		foreach ($loaded_posts as $loaded_post) {
			$wp_post = $wp_posts[$loaded_post->get_id()] ?? null;
			$this->save_post($loaded_post, $wp_post);
		}

		// delete old posts
		foreach ($wp_posts as $wp_post) {
			if (!isset($loaded_posts[PostUtils::get_external_id($wp_post)])) {
				$this->delete_post($wp_post);
			}
		}
	}

	public function options_changed($prev_options, $options)
	{
		if ($prev_options->posts_slug === $options->posts_slug) {
			return;
		}
		$all_posts = $this->get_wp_posts($prev_options->posts_slug);
		foreach ($all_posts as $post) {
			wp_update_post([
				'ID' => $post->ID,
				'post_type' => $options->posts_slug,
			]);
		}
	}

	public function clean()
	{
		foreach ($this->get_wp_posts() as $post) {
			$this->delete_post($post);
		}
	}

	/**
	 * @return Post[]
	 * @throws FacebookClientException
	 */
	private function fetch_posts()
	{
		$limit = $this->options->posts_limit;
		$include_patterns = PostUtils::prepare_patterns($this->options->load_include_patterns);
		$exclude_patterns = PostUtils::prepare_patterns($this->options->load_exclude_patterns);
		/* @var $posts Post[] */
		$posts = [];

		$posts_edge = $this->facebook_client->get_posts_edge();
		while (count($posts) < $limit) {
			/* @var $posts_edge Post[] */
			foreach ($posts_edge as $post) {
				if (
					!PostUtils::is_eligible($post, $this->options)
					|| !PostUtils::match_filters($post, $include_patterns, $exclude_patterns)
				) {
					continue;
				}

				if (PostUtils::is_video($post)) {
					$video = $this->facebook_client->get_video($post->get_attachment()->get_target());
					$post->set_video($video);
				}

				$posts[] = $post;
				if (count($posts) === $limit) {
					break;
				}
			}

			if (!$posts_edge->has_cursor_after()) {
				break;
			}

			$posts_edge = $this->facebook_client->get_posts_edge($posts_edge->get_cursor_after());
		}

		return $posts;
	}

	/**
	 * @param string|null $type
	 *
	 * @return  WP_Post[]
	 */
	private function get_wp_posts($type = null)
	{
		$type = $type ?? $this->options->posts_slug;

		return (new WP_Query())->query([
			'posts_per_page' => -1,
			'post_type' => $type,
			'post_status' => ['publish', 'pending', 'draft', 'future', 'private', 'inherit', 'trash'],
			'no_found_rows' => true,
		]);
	}

	/**
	 * @param Post $post
	 * @param WP_Post|null $wp_post
	 */
	private function save_post($post, $wp_post)
	{
		if ($wp_post) {
			$ID = $wp_post->ID;
			$post_status = $wp_post->post_status;
		} else {
			$ID = 0;
			$post_status = 'publish';
		}
		$post_categories = wp_get_post_categories($wp_post->ID);
		$post_category = PostUtils::create_category($post, $this->options);
		if (!empty($post_category) && !in_array($post_category, $post_categories)) {
			$post_categories[] = $post_category;
		}

		$title = $this->options->posts_title;
		if (!empty($title)) {
			$formatted_date = wp_date(get_option('date_format'), strtotime($post->get_created_time()));
			$title = str_replace('{date}', $formatted_date, $title);
			$title = trim($title);
		}

		$created_time = date('Y-m-d H:i:s', strtotime($post->get_created_time()));
		$options = [
			'ID' => $ID,
			'post_name' => $post->get_id(),
			'post_status' => $post_status,
			'post_type' => $this->options->posts_slug,
			'post_date' => $created_time,
			'post_date_gmt' => $created_time,
			'post_modified' => $created_time,
			'post_modified_gmt' => $created_time,
			'post_excerpt' => $wp_post ? PostUtils::create_excerpt($post, $ID, $this->options) : null,
			'post_category' => $post_categories,
			'post_title' => $title,
			'post_content' => $this->content_formatter->render_content($post),
			'meta_input' => [
				'external_id' => $post->get_id(),
				'permalink_url' => $post->get_permalink_url(),
			],
		];

		$post_id = wp_insert_post($options, false, false);
		if (!$post_id) {
			return;
		}

		if (!$wp_post) {
			wp_update_post([
				'post_excerpt' => PostUtils::create_excerpt($post, $ID, $this->options),
			], false, false);
			set_post_format($post_id, PostUtils::create_post_format($post));
		}

		$thumbnail_id = $this->save_thumbnail_attachment($post, $post_id);
		if ($thumbnail_id) {
			set_post_thumbnail($post_id, $thumbnail_id);
		}
	}


	/**
	 * @param Post $post
	 * @param int $post_id
	 *
	 * @return int
	 */
	private function save_thumbnail_attachment($post, $post_id)
	{
		$thumbnail = ($this->options->load_thumbnails || $this->options->load_thumbnails_album)
			? PostUtils::create_thumbnail_attachment($post, $this->options)
			: null;
		$saved_thumbnail_id = get_post_thumbnail_id($post_id);

		if (empty($thumbnail)) {
			if ($saved_thumbnail_id) {
				wp_delete_attachment($saved_thumbnail_id, true);
			}

			return 0;
		}

		$options = [
			'ID' => $saved_thumbnail_id ?: 0,
			'guid' => WPSWLR_KEY . '-thumb-' . $post_id,
			'post_mime_type' => 'image/jpeg',
			'post_title' => 'Thumbnail',
			'post_content' => '',
			'post_status' => 'inherit',
			'meta_input' => [
				FacebookPost::ATTACHMENT_META_KEY => 'image',
			],
		];

		$attachment_id = wp_insert_attachment($options, false, $post_id, false, false);
		if (!$attachment_id) {
			return 0;
		}

		update_attached_file($attachment_id, $thumbnail['src']);

		// create metadata
		$sizes = ['medium', 'large', 'thumbnail', 'medium_large'];
		$attachment_meta = [
			'width' => $thumbnail['width'],
			'height' => $thumbnail['height'],
			'file' => $thumbnail['src'],
			'sizes' => [],
			'image_meta' => [],
		];
		foreach ($sizes as $size) {
			$attachment_meta['sizes'][$size] = [
				'width' => $thumbnail['width'],
				'height' => $thumbnail['height'],
				'file' => $thumbnail['src'],
				'mime-type' => 'image/jpeg',
			];
		}

		wp_update_attachment_metadata($attachment_id, $attachment_meta);

		return $attachment_id;
	}

	/**
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	private function delete_post($post)
	{
		$thumbnail_id = get_post_thumbnail_id($post->ID);
		if ($thumbnail_id) {
			wp_delete_attachment($thumbnail_id, true);
		}

		wp_delete_post($post->ID, true);
	}
}
