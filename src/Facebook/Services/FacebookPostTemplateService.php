<?php

namespace WPSWLR\Facebook\Services;

use WP_Post;
use WP_Query;
use WPSWLR\Facebook\PostTypes\FacebookPostTemplateAlbum;
use WPSWLR\Facebook\PostTypes\FacebookPostTemplateGeneral;
use WPSWLR\Facebook\PostTypes\FacebookPostTemplateVideo;

class FacebookPostTemplateService
{

	const GENERAL_TEMPLATE = <<<TPL
<!-- wp:image {"align":"center"} -->
<div class="wp-block-image"><figure class="aligncenter"><img src="https://wpswlr.sk/wp-content/plugins/wpswlr/assets/placeholder800x600.jpg" alt=""/></figure></div>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>[fb-content]</p>
<!-- /wp:paragraph -->
TPL;

	const ALBUM_TEMPLATE = <<<TPL
<!-- wp:gallery {"columns":3} -->
<figure class="wp-block-gallery columns-3 is-cropped"><ul class="blocks-gallery-grid"><li class="blocks-gallery-item"><figure><img src="https://wpswlr.sk/wp-content/plugins/wpswlr/assets/placeholder800x600.jpg" data-full-url="https://wpswlr.sk/wp-content/plugins/wpswlr/assets/placeholder800x600.jpg" data-link="https://wpswlr.sk/wp-content/plugins/wpswlr/assets/placeholder800x600.jpg"/></figure></li><li class="blocks-gallery-item"><figure><img src="https://wpswlr.sk/wp-content/plugins/wpswlr/assets/placeholder800x600.jpg" data-full-url="https://wpswlr.sk/wp-content/plugins/wpswlr/assets/placeholder800x600.jpg" data-link="https://wpswlr.sk/wp-content/plugins/wpswlr/assets/placeholder800x600.jpg"/></figure></li><li class="blocks-gallery-item"><figure><img src="https://wpswlr.sk/wp-content/plugins/wpswlr/assets/placeholder800x600.jpg" data-full-url="https://wpswlr.sk/wp-content/plugins/wpswlr/assets/placeholder800x600.jpg" data-link="https://wpswlr.sk/wp-content/plugins/wpswlr/assets/placeholder800x600.jpg"/></figure></li><li class="blocks-gallery-item"><figure><img src="https://wpswlr.sk/wp-content/plugins/wpswlr/assets/placeholder800x600.jpg" data-full-url="https://wpswlr.sk/wp-content/plugins/wpswlr/assets/placeholder800x600.jpg" data-link="https://wpswlr.sk/wp-content/plugins/wpswlr/assets/placeholder800x600.jpg"/></figure></li><li class="blocks-gallery-item"><figure><img src="https://wpswlr.sk/wp-content/plugins/wpswlr/assets/placeholder800x600.jpg" data-full-url="https://wpswlr.sk/wp-content/plugins/wpswlr/assets/placeholder800x600.jpg" data-link="https://wpswlr.sk/wp-content/plugins/wpswlr/assets/placeholder800x600.jpg"/></figure></li><li class="blocks-gallery-item"><figure><img src="https://wpswlr.sk/wp-content/plugins/wpswlr/assets/placeholder800x600.jpg" data-full-url="https://wpswlr.sk/wp-content/plugins/wpswlr/assets/placeholder800x600.jpg" data-link="https://wpswlr.sk/wp-content/plugins/wpswlr/assets/placeholder800x600.jpg"/></figure></li></ul></figure>
<!-- /wp:gallery -->

<!-- wp:paragraph -->
<p>[fb-content]</p>
<!-- /wp:paragraph -->
TPL;

	const VIDEO_TEMPLATE = <<<TPL
<!-- wp:video {"align":"center"} -->
<figure class="wp-block-video aligncenter"><video controls muted preload="none" src="https://wpswlr.sk/wp-content/plugins/wpswlr/assets/placeholder.mp4"></video></figure>
<!-- /wp:video -->

<!-- wp:paragraph -->
<p>[fb-content]</p>
<!-- /wp:paragraph -->
TPL;

	private $general_template = null;
	private $album_template = null;
	private $video_template = null;

	/**
	 * @return string
	 */
	public function get_general_template()
	{
		if ($this->general_template === null) {
			$post = $this->get_wp_post(FacebookPostTemplateGeneral::SLUG);
			$this->general_template = empty($post) ? self::GENERAL_TEMPLATE : $post->post_content;
		}

		return $this->general_template;
	}

	/**
	 * @return string
	 */
	public function get_album_template()
	{
		if ($this->album_template === null) {
			$post = $this->get_wp_post(FacebookPostTemplateAlbum::SLUG);
			$this->album_template = empty($post) ? self::ALBUM_TEMPLATE : $post->post_content;
		}

		return $this->album_template;
	}

	/**
	 * @return string
	 */
	public function get_video_template()
	{
		if ($this->video_template === null) {
			$post = $this->get_wp_post(FacebookPostTemplateVideo::SLUG);
			$this->video_template = empty($post) ? self::VIDEO_TEMPLATE : $post->post_content;
		}

		return $this->video_template;
	}


	/**
	 * @return array
	 */
	public function get_templates()
	{
		$general_post = $this->get_wp_post(FacebookPostTemplateGeneral::SLUG);
		$album_post = $this->get_wp_post(FacebookPostTemplateAlbum::SLUG);
		$video_post = $this->get_wp_post(FacebookPostTemplateVideo::SLUG);

		return [
			'general' => [
				'id' => empty($general_post) ? 0 : $general_post->ID,
				'type' => FacebookPostTemplateGeneral::SLUG,
			],
			'album' => [
				'id' => empty($album_post) ? 0 : $album_post->ID,
				'type' => FacebookPostTemplateAlbum::SLUG,
			],
			'video' => [
				'id' => empty($video_post) ? 0 : $video_post->ID,
				'type' => FacebookPostTemplateVideo::SLUG,
			],
		];
	}

	/**
	 * @param string $type
	 *
	 * @return  WP_Post
	 */
	private function get_wp_post($type)
	{
		$posts = (new WP_Query())->query([
			'posts_per_page' => 1,
			'post_type' => $type,
			'no_found_rows' => true,
			'post_status' => ['draft', 'publish'],
			'orderby' => 'post_modified',
		]);

		return empty($posts) ? null : $posts[0];
	}
}
