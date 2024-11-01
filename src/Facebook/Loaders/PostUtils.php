<?php

namespace WPSWLR\Facebook\Loaders;

use WP_Post;
use WPSWLR\Core\Settings\FacebookOptions;
use WPSWLR\Facebook\Client\Model\Post;

class PostUtils
{

	/**
	 * @param Post $post
	 *
	 * @return bool
	 */
	public static function is_image($post)
	{
		return !empty($post->get_attachment())
			&& !empty($post->get_attachment()->get_image())
			&& !empty($post->get_attachment()->get_image()->get_src())
			&& $post->get_attachment()->get_type() === 'photo'
			&& $post->get_attachment()->get_media_type() === 'photo';
	}

	/**
	 * @param Post $post
	 *
	 * @return bool
	 */
	public static function is_album($post)
	{
		return !empty($post->get_attachment())
			&& !empty($post->get_attachment()->get_sub_attachments())
			&& $post->get_status_type() === 'added_photos'
			&& $post->get_attachment()->get_type() === 'album';
	}

	/**
	 * @param Post $post
	 *
	 * @return bool
	 */
	public static function is_video($post)
	{
		return !empty($post->get_attachment())
			&& !empty($post->get_attachment()->get_target())
			&& !empty($post->get_attachment()->get_source())
			&& $post->get_status_type() === 'added_video';
	}


	/**
	 * @param Post $post
	 * @param string $post_id
	 * @param FacebookOptions $options
	 *
	 * @return string|null
	 */
	public static function create_excerpt($post, $post_id, $options)
	{
		if (!$options->post_excerpt) {
			return null;
		}
		$excerpt_length = $options->post_excerpt_length;
		$excerpt_text = str_replace(' ', '&nbsp;', $options->post_excerpt_text);
		$story = $post->get_story();
		$message = $post->get_message();

		$base = $story;
		if (!empty($story)) {
			$base = $story;
		} else if (!empty($message)) {
			$base = $message;
		} else {
			return null;
		}

		$words = preg_split('/\s+/', $base);
		$counter = 0;
		foreach ($words as $w) {
			$c = mb_strlen($w);
			if ($counter + $c < $excerpt_length) {
				$counter += $c + 1;
			} else {
				break;
			}
		}

		$excerpt = mb_substr($base, 0, $counter);
		$excerpt = trim($excerpt);
		$excerpt = trim($excerpt, '.');
		$excerpt = PostContentRenderer::linkify($excerpt);
		$excerpt .= '... <a href="' . get_permalink($post_id) . '">' . $excerpt_text . '</a>';

		return $excerpt;
	}

	/**
	 * @param Post $post
	 *
	 * @return string
	 */
	public static function create_post_format($post)
	{
		if (PostUtils::is_image($post)) {
			return 'image';
		}
		if (PostUtils::is_album($post)) {
			return 'gallery';
		}
		if (PostUtils::is_video($post)) {
			return 'video';
		}

		return '';
	}

	/**
	 * @param Post $post
	 * @param FacebookOptions $options
	 *
	 * @return int
	 */
	public static function create_category($post, $options)
	{
		if (PostUtils::is_album($post) && !empty($options->post_category_album)) {
			return $options->post_category_album;
		}
		if (PostUtils::is_video($post) && !empty($options->post_category_video)) {
			return $options->post_category_video;
		}

		return $options->post_category_general;
	}

	/**
	 * @param Post $post
	 * @param FacebookOptions $options
	 *
	 * @return array|null
	 */
	public static function create_thumbnail_attachment($post, $options)
	{
		$img = null;

		if ($options->load_thumbnails && PostUtils::is_image($post)) {
			$img = $post->get_attachment()->get_image();
		}

		if ($options->load_thumbnails_album && PostUtils::is_album($post)) {
			$img = $post->get_attachment()->get_sub_attachments()[0]->get_image();
		}

		if (!empty($img)) {
			return [
				'src' => $img->get_src(),
				'width' => $img->get_width(),
				'height' => $img->get_height(),
			];
		}

		return null;
	}

	/**
	 * @param Post $post
	 * @param FacebookOptions $options
	 *
	 * @return bool
	 */
	public static function is_eligible($post, $options)
	{
		if (
			!$post->get_is_published()
			|| $post->get_is_hidden()
			|| $post->get_is_expired()
			|| $post->get_privacy() !== 'EVERYONE'
		) {
			return false;
		}

		if (empty($post->get_attachment())) {
			if (empty($post->get_message())) {
				return false;
			}
		} else {
			// geo share
			if (
				($post->get_attachment()->get_type() === 'map')
				&& ($post->get_attachment()->get_media_type() === 'link')
			) {
				return false;
			}

			// cover photo
			if (
				($post->get_attachment()->get_type() === 'cover_photo')
				&& ($post->get_attachment()->get_media_type() === 'photo')
			) {
				return false;
			}

			// profile photo
			if (
				($post->get_attachment()->get_type() === 'profile_media')
				&& ($post->get_attachment()->get_media_type() === 'photo')
			) {
				return false;
			}

			// events
			if (
				($post->get_attachment()->get_type() === 'event')
				&& ($post->get_attachment()->get_media_type() === 'event')
			) {
				return false;
			}

			// link without target and image is usually page info update
			if ($post->get_attachment()->get_media_type() === 'link') {
				if (empty($post->get_attachment()->get_target()) && empty($post->get_attachment()->get_image())) {
					return false;
				}
			}
		}

		if ($post->get_status_type() === 'added_photos') {
			// invalid photo without attachment
			if (empty($post->get_attachment())) {
				return false;
			}

			// empty album
			if (
				$post->get_attachment()->get_type() === 'album'
				&& empty($post->get_attachment()->get_sub_attachments())
			) {
				return false;
			}
		}

		if ($post->get_status_type() === 'added_video') {
			// invalid video without attachment
			if (empty($post->get_attachment())) {
				return false;
			}

			if (empty($post->get_attachment()->get_source())) {
				return false;
			}
		}

		if (!$options->load_album_posts && PostUtils::is_album($post)) {
			return false;
		}
		if (!$options->load_video_posts && PostUtils::is_video($post)) {
			return false;
		}

		return true;
	}

	/**
	 * @param string $patterns_str
	 *
	 * @return string[]
	 */
	public static function prepare_patterns($patterns_str)
	{
		if (empty($patterns_str)) {
			return [];
		}
		$patterns = json_decode($patterns_str);
		if (json_last_error() || empty($patterns) || !is_array($patterns)) {
			return [];
		}
		$patterns_prepared = [];
		foreach ($patterns as $pattern) {
			if (!is_string($pattern)) {
				continue;
			}
			$pattern_prepared = preg_quote($pattern);
			$pattern_prepared = str_replace(['\*', '\?'], ['.*?', '.?'], $pattern_prepared);
			$pattern_prepared = '/' . $pattern_prepared . '/i';
			$patterns_prepared[] = $pattern_prepared;
		}

		return $patterns_prepared;
	}

	/**
	 * @param Post $post
	 * @param string[] $include_patterns
	 * @param string[] $exclude_patterns
	 *
	 * @return bool
	 */
	public static function match_filters($post, $include_patterns, $exclude_patterns)
	{
		if (!empty($include_patterns)) {
			$include_found = false;
			foreach ($include_patterns as $pattern) {
				if (preg_match($pattern, $post->get_message())) {
					$include_found = true;
					break;
				}
			}
			if (!$include_found) {
				return false;
			}
		}

		if (!empty($exclude_patterns)) {
			foreach ($exclude_patterns as $pattern) {
				if (preg_match($pattern, $post->get_message())) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public static function get_external_id($post)
	{
		return get_post_meta($post->ID, 'external_id', true) ?? $post->post_name;
	}
}
