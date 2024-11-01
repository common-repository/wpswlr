<?php

namespace WPSWLR\Facebook\PostTypes;

use WP_Post;
use WP_Query;
use WPSWLR\Core\ActivateListener;
use WPSWLR\Core\DeactivateListener;
use WPSWLR\Core\InitListener;
use WPSWLR\Core\Settings\FacebookOptions;
use WPSWLR\Core\SettingsService;
use WPSWLR\Core\Utils;

class FacebookPost implements InitListener, ActivateListener, DeactivateListener
{

	const SLUG = WPSWLR_KEY . '-facebook-post';
	const ATTACHMENT_META_KEY = '_' . WPSWLR_KEY . '_attachment';
	const READONLY_STYLES = [
		'*' => 'post-readonly-59.css',
		'6.1' => 'post-readonly-59.css',
		'6.0' => 'post-readonly-59.css',
		'5.9' => 'post-readonly-59.css',
		'5.8' => 'post-readonly-58.css',
	];

	/** @var FacebookOptions $options */
	private $options;

	/**
	 * @param SettingsService $settings_service
	 */
	public function __construct($settings_service)
	{
		$this->options = $settings_service->get_facebook_options();
	}

	public function on_activate()
	{
		$this->register_post_type();
		flush_rewrite_rules();
	}

	public function on_deactivate()
	{
		unregister_post_type($this->options->posts_slug);
		flush_rewrite_rules();
	}

	public function on_init()
	{
		if (Utils::user_can_edit()) {
			add_action(SettingsService::FB_OPTIONS_CHANGED, [$this, 'options_changed'], 1, 2);
		}

		if (is_admin()) {
			add_action('wp_print_scripts', [$this, 'register_editor_script']);
			add_filter('post_row_actions', [$this, 'replace_posts_list_actions'], 10, 2);
			add_filter("bulk_actions-edit-{$this->options->posts_slug}", [$this, 'remove_posts_list_bulk_actions']);
		}

		add_action('pre_get_posts', [$this, 'show_in_posts_main_query']);
		add_filter('the_title', [$this, 'remove_post_title'], 10, 2);
		if ($this->options->load_thumbnails || $this->options->load_thumbnails_album) {
			add_filter('wp_get_attachment_url', [$this, 'resolve_attachment_url'], 999, 2);
			add_filter('wp_prepare_attachment_for_js', [$this, 'resolve_attachment_for_js'], 999, 3);
			add_filter('wp_get_attachment_image_src', [$this, 'get_attachment_image_src'], 999, 4);
			add_filter('wp_get_attachment_image_attributes', [$this, 'get_attachment_image_attributes'], 999, 3);
		}

		$this->register_post_type();
	}

	/**
	 * @param FacebookOptions $prev_options
	 * @param FacebookOptions $options
	 *
	 * @return void
	 */
	public function options_changed($prev_options, $options)
	{
		if ($prev_options->posts_slug !== $options->posts_slug) {
			unregister_post_type($prev_options->posts_slug);
			$this->register_post_type();
			flush_rewrite_rules();
		}
	}

	/**
	 * @return void
	 */
	private function register_post_type()
	{
		$labels = [
			'name' => __('Facebook Posts', 'wpswlr'),
			'singular_name' => __('Facebook Post', 'wpswlr'),
			'all_items' => __('All Facebook Posts', 'wpswlr'),
			'edit_item' => __('Facebook Post Preview', 'wpswlr'),
			'view_item' => __('View Facebook Post', 'wpswlr'),
			'view_items' => __('View Facebook Posts', 'wpswlr'),
			'search_items' => __('Search Facebook Posts', 'wpswlr'),
			'not_found' => __('No Facebook Posts found', 'wpswlr'),
			'not_found_in_trash' => __('No Facebook Posts found in trash', 'wpswlr'),
			'filter_items_list' => __('Filter Facebook Posts list', 'wpswlr'),
			'items_list_navigation' => __('Facebook Posts list navigation', 'wpswlr'),
			'items_list' => __('Facebook Posts list', 'wpswlr'),
			'attributes' => __('Facebook Posts attributes', 'wpswlr'),
			'item_published' => __('Facebook Post published.', 'wpswlr'),
			'item_published_privately' => __('Facebook Post published privately.', 'wpswlr'),
			'item_reverted_to_draft' => __('Facebook Post reverted to draft.', 'wpswlr'),
			'item_scheduled' => __('Facebook Post scheduled.', 'wpswlr'),
		];

		register_post_type($this->options->posts_slug, [
			'labels' => $labels,
			'description' => __('Posts loaded from Facebook feed', 'wpswlr'),
			'public' => true,
			'show_in_admin_bar' => false,
			'show_in_rest' => true,
			'menu_position' => 5,
			'menu_icon' => 'dashicons-facebook-alt',
			'capabilities' => ['create_posts' => false],
			'map_meta_cap' => true,
			'supports' => ['post-formats', 'thumbnail', 'editor'],
			'register_meta_box_cb' => [$this, 'add_meta_boxes'],
			'taxonomies' => ['category'],
			'can_export' => false,
			'delete_with_user' => false,
		]);
	}

	/**
	 * Add custom type to main query. This allows list facebook posts on FE page
	 *
	 * @param WP_Query $query
	 *
	 * @return void
	 */
	public function show_in_posts_main_query($query)
	{
		$post_types = $query->get('post_type');
		if (is_admin() || !$query->is_main_query()) {
			return;
		}
		if ('any' === $post_types) {
			return;
		}
		if ($query->is_attachment || $query->is_page) {
			return;
		}
		if (!($query->is_posts_page || $query->is_home() || $query->is_archive() || $query->is_single)) {
			return;
		}

		$post_types = $query->get('post_type');
		if (empty($post_types)) {
			$post_types = [];
		} else if (is_string($post_types)) {
			$post_types = explode(',', $post_types);
		}
		$post_types = array_map('trim', $post_types);
		$post_types = array_filter($post_types);
		if (!in_array('post', $post_types)) {
			$post_types[] = 'post';
		}
		$post_types[] = $this->options->posts_slug;
		$query->set('post_type', $post_types);
	}

	/**
	 * Remove post title. Hide "Untitled"
	 *
	 * @param string $title
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function remove_post_title($title, $post_id = 0)
	{
		if (!$post_id || get_post_type($post_id) !== $this->options->posts_slug || !empty($this->options->posts_title)) {
			return $title;
		}
		return '';
	}

	/**
	 * Replace wrong external URLs in admin list
	 *
	 * @param array $response
	 * @param WP_Post $attachment
	 * @param array $m
	 *
	 * @return array
	 */
	public function resolve_attachment_for_js($response, $attachment, array $m)
	{
		$meta = get_post_meta($attachment->ID);
		if (!isset($meta[self::ATTACHMENT_META_KEY]) || !isset($meta['_wp_attached_file'][0])) {
			return $response;
		}
		$response['link'] = $meta['_wp_attached_file'][0];
		foreach ($response['sizes'] as &$value) {
			$value['url'] = $meta['_wp_attached_file'][0];
		}

		return $response;
	}

	/**
	 * Replace wrong external URLs on frontend
	 *
	 * @param string $url
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function resolve_attachment_url($url, $post_id)
	{
		$meta = get_post_meta($post_id);
		if (isset($meta[self::ATTACHMENT_META_KEY]) && isset($meta['_wp_attached_file'][0])) {
			return $meta['_wp_attached_file'][0];
		}

		return $url;
	}

	/**
	 * Replace wrong external URLs on frontend
	 *
	 * @param array $image
	 * @param string $attachment_id
	 * @param string|array $size
	 * @param bool $icon
	 *
	 * @return array
	 */
	public function get_attachment_image_src($image, $attachment_id, $size, $icon)
	{
		$post_meta = get_post_meta($attachment_id);
		if (isset($post_meta[self::ATTACHMENT_META_KEY]) && isset($post_meta['_wp_attached_file'][0])) {
			$attachment_metadata = wp_get_attachment_metadata($attachment_id);

			return [
				$post_meta['_wp_attached_file'][0],
				$attachment_metadata['width'],
				$attachment_metadata['height'],
				$icon,
			];
		}

		return $image;
	}

	/**
	 * Replace wrong external URLs on frontend
	 *
	 * @param array $attr
	 * @param WP_Post $attachment
	 * @param string|array $size
	 *
	 * @return array
	 */
	public function get_attachment_image_attributes($attr, $attachment, $size)
	{
		$post_meta = get_post_meta($attachment->ID);
		if (isset($post_meta[self::ATTACHMENT_META_KEY]) && isset($post_meta['_wp_attached_file'][0])) {
			$attr['src'] = $post_meta['_wp_attached_file'][0];
		}

		return $attr;
	}

	// **************************************************************************************************************
	//
	//                                                ADMIN
	//
	// **************************************************************************************************************

	/**
	 * Add meta box with facebook info
	 *
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function add_meta_boxes($post)
	{
		$thumb_url = get_the_post_thumbnail_url($post, [266, 266]);
		if (!empty($thumb_url)) {
			add_meta_box(
				'wpswlr-fimg-meta-box',
				__('Featured Image', 'wpswlr'),
				function ($post) use ($thumb_url) {
					$caption = get_the_post_thumbnail_caption($post);
					echo "<p><img src=\"${thumb_url}\" class=\"attachment-266x266 size-266x266\" style=\"max-width: 100%;\" loading=\"lazy\" /></p>";

					if (!empty($caption)) {
						echo "<p>${caption}</p>";
					}
				},
				$this->options->posts_slug,
				'side',
				'low'
			);
		}

		add_meta_box(
			'wpswlr-info-meta-box',
			__('Facebook Info', 'wpswlr'),
			function ($post) {
				$meta = get_post_meta($post->ID);

				if (!empty($meta['external_id'][0])) {
					echo '<div class="misc-pub-section">'
						. __('Post ID', 'wpswlr')
						. ': <br />'
						. "<span style=\"font-weight: 600; word-break: break-all;\">{$meta['external_id'][0]}</span>"
						. '</div>';
				}
				if (!empty($meta['permalink_url'][0])) {
					echo '<div class="misc-pub-section">'
						. __('URL', 'wpswlr')
						. ': <br />'
						. "<a href=\"{$meta['permalink_url'][0]}\" target=\"_blank\" style=\"word-break: break-all;\">{$meta['permalink_url'][0]}</a"
						. '></div>';
				}
			},
			$this->options->posts_slug,
			'side',
			'low'
		);
	}

	/**
	 * Add script and style loaded in post edit page that will make editor readonly.
	 */
	public function register_editor_script()
	{
		if (get_post_type() !== $this->options->posts_slug) {
			return;
		}

		wp_enqueue_style(self::SLUG, WPSWLR_BASE_URL . 'assets/' . (self::READONLY_STYLES[Utils::wp_minor_version()] ?? self::READONLY_STYLES['*']), [], WPSWLR_VERSION);
		wp_enqueue_script(self::SLUG, WPSWLR_BASE_URL . 'assets/post-readonly.js', [
			'wp-blocks',
			'wp-edit-post',
		], WPSWLR_VERSION, true);
	}

	/**
	 * Remove unused actions from the posts list and add the "View" action
	 *
	 * @param array $actions
	 * @param WP_Post $post
	 *
	 * @return array
	 */
	public function replace_posts_list_actions($actions, $post)
	{
		if ($post->post_type !== $this->options->posts_slug) {
			return $actions;
		}
		$readonly_actions = [];

		$view_html = '<a href="%s" title="%s">%s</a>';
		$view_label = __('Preview', 'wpswlr');
		$readonly_actions['view'] = sprintf($view_html, get_edit_post_link($post->ID), $view_label, $view_label);

		$external_url = get_post_meta($post->ID, 'permalink_url', true);
		if (!empty($external_url)) {
			$view_html = '<a href="%s" title="%s" target="_blank">%s</a>';
			$view_label = __('Open Original', 'wpswlr');
			$readonly_actions['external_url'] = sprintf($view_html, $external_url, $view_label, $view_label);
		}

		return array_merge([], $readonly_actions, array_filter($actions, function ($key) {
			return $key !== 'edit' && $key !== 'view';
		}, ARRAY_FILTER_USE_KEY));
	}

	/**
	 * Remove edit from bulk actions from the posts list
	 *
	 * @param array $actions
	 *
	 * @return array
	 */
	public function remove_posts_list_bulk_actions($actions)
	{
		unset($actions['edit']);

		return $actions;
	}
}
