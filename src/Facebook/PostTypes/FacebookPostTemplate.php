<?php

namespace WPSWLR\Facebook\PostTypes;

use WP_Query;
use WPSWLR\Core\InitListener;
use WPSWLR\Core\UninstallListener;
use WPSWLR\Core\Utils;

abstract class FacebookPostTemplate implements InitListener, UninstallListener
{

	const TEMPLATE_SCRIPTS = [
		'*' => 'post-template-58.js',
		'6.1' => 'post-template-58.js',
		'6.0' => 'post-template-58.js',
		'5.9' => 'post-template-58.js',
		'5.8' => 'post-template-58.js',
	];

	public function on_init()
	{
		if (is_admin()) {
			add_action('wp_print_scripts', [$this, 'register_editor_script']);
		}

		$this->register_post_type();
	}

	public function on_uninstall()
	{
		$posts = (new WP_Query())->query([
			'posts_per_page' => -1,
			'post_type' => $this->slug(),
			'no_found_rows' => true,
		]);

		foreach ($posts as $post) {
			wp_delete_post($post->ID, true);
		}
	}

	public function register_editor_script()
	{
		if (get_post_type() !== $this->slug()) {
			return;
		}

		wp_enqueue_style($this->slug(), WPSWLR_BASE_URL . 'assets/post-template.css', [], WPSWLR_VERSION);
		wp_enqueue_script($this->slug(), WPSWLR_BASE_URL . 'assets/' . (self::TEMPLATE_SCRIPTS[Utils::wp_minor_version()] ?? self::TEMPLATE_SCRIPTS['*']), [
			'wp-blocks',
			'wp-edit-post',
		], WPSWLR_VERSION, true);
		wp_localize_script($this->slug(), 'WPSWLR', [
			'post_type' => 'general',
			'blocks' => $this->template(),
		]);
	}

	private function register_post_type()
	{
		register_post_type($this->slug(), [
			'label' => $this->label(),
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => false,
			'show_in_rest' => true,
			'map_meta_cap' => true,
			'supports' => ['editor', 'revisions'],
			'taxonomies' => [],
			'can_export' => false,
			'delete_with_user' => false,
			'template' => $this->template(),
		]);
	}

	/**
	 * @return string
	 */
	protected abstract function slug();

	/**
	 * @return string
	 */
	protected abstract function label();

	/**
	 * @return array
	 */
	protected abstract function template();
}
