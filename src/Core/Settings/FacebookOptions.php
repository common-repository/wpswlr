<?php

namespace WPSWLR\Core\Settings;

/**
 * @property bool $load_album_posts
 * @property bool $load_video_posts
 * @property string|null $load_include_patterns
 * @property string|null $load_exclude_patterns
 * @property bool $load_thumbnails
 * @property bool $load_thumbnails_album
 * @property string $posts_slug
 * @property int $posts_limit
 * @property string $posts_title
 * @property int $post_category_general
 * @property int $post_category_album
 * @property int $post_category_video
 * @property int $post_excerpt
 * @property int $post_excerpt_length
 * @property int $post_excerpt_text
 */
class FacebookOptions extends AbstractSettings
{

	const KEY = WPSWLR_KEY . '_settings_facebook_options';

	const DEFAULTS = [
		'load_album_posts' => true,
		'load_video_posts' => true,
		'load_include_patterns' => null,
		'load_exclude_patterns' => null,
		'load_thumbnails' => true,
		'load_thumbnails_album' => true,
		'posts_slug' => WPSWLR_KEY . '-facebook-post',
		'posts_limit' => 50,
		'posts_title' => null,
		'post_category_general' => 0,
		'post_category_album' => 0,
		'post_category_video' => 0,
		'post_excerpt' => false,
		'post_excerpt_length' => 250,
		'post_excerpt_text' => 'Read more',
	];

	protected function get_defaults()
	{
		return self::DEFAULTS;
	}
}
