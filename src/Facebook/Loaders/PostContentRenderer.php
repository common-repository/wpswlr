<?php

namespace WPSWLR\Facebook\Loaders;

use WPSWLR\Core\Settings\FacebookOptions;
use WPSWLR\Core\SettingsService;
use WPSWLR\Facebook\Client\Model\Post;
use WPSWLR\Facebook\Services\FacebookPostTemplateService;

class PostContentRenderer
{

	/** @var FacebookPostTemplateService  $template_service */
	private $template_service;
	/** @var FacebookOptions $options */
	private $options;

	/**
	 * @param FacebookPostTemplateService $template_service
	 * @param SettingsService $settings_service
	 */
	public function __construct($template_service, $settings_service)
	{
		$this->template_service = $template_service;
		$this->options = $settings_service->get_facebook_options();
	}

	/**
	 * Convert all links in string to html links
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	public static function linkify($str)
	{
		$link_regex = '@(http(s)?)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s.]+[^\s]*)+[^,.\s])@';
		$link_html = '<a href="http$2://$4" target="_blank" title="$0" rel="noopener">$0</a>';
		return preg_replace($link_regex, $link_html, htmlspecialchars($str));
	}

	/**
	 * @param Post $post
	 *
	 * @return string
	 */
	public function render_content($post)
	{
		if (PostUtils::is_album($post)) {
			return $this->render_album_post($post);
		}

		if (PostUtils::is_video($post)) {
			return $this->render_video_post($post);
		}

		return $this->render_general_post($post);
	}

	/**
	 * @param Post $post
	 *
	 * @return string
	 */
	private function render_general_post($post)
	{
		$content = $this->template_service->get_general_template();
		$content = $this->render_text($content, $post);
		$content = $this->render_image($content, $post);

		return trim($content);
	}

	/**
	 * @param Post $post
	 *
	 * @return string
	 */
	private function render_album_post($post)
	{
		$content = $this->template_service->get_album_template();
		$content = $this->render_text($content, $post);
		$content = $this->render_album($content, $post);

		return trim($content);
	}

	/**
	 * @param Post $post
	 *
	 * @return string
	 */
	private function render_video_post($post)
	{
		$content = $this->template_service->get_video_template();
		$content = $this->render_text($content, $post);
		$content = $this->render_video($content, $post);

		return trim($content);
	}

	/**
	 * @param string $tpl
	 * @param Post $post
	 *
	 * @return string
	 */
	private function render_text($tpl, $post)
	{
		$message = $post->get_message();
		$story = $post->get_story();
		$parts = [];
		if (!empty($story)) {
			$parts[] = '<sub>' . htmlspecialchars($story) . '</sub>';
		}
		if (!empty($message)) {
			$parts[] = PostContentRenderer::linkify($message);
		}

		if (!empty($parts)) {
			// render content
			$content = implode('<br />', $parts);

			return str_replace('[fb-content]', $content, $tpl);
		} else {
			// remove paragraphs with [fb-content]
			return preg_replace_callback('/<!--\s+wp:paragraph.+?-->.*?<!--\s+\/wp:paragraph\s+-->/si', function ($paragraph) {
				if (empty($paragraph) || !mb_strpos($paragraph[0], '[fb-content]') === false) {
					return '';
				}

				return $paragraph[0];
			}, $tpl);
		}
	}

	/**
	 * @param string $tpl
	 * @param Post $post
	 *
	 * @return string
	 */
	private function render_image($tpl, $post)
	{
		return preg_replace_callback('/(<!--\s+wp:image.+?-->.*?)(<img .*?\/>)(.*?<!--\s+\/wp:image\s+-->)/si', function ($image) use ($post) {
			if (count($image) !== 4 || $this->options->load_thumbnails || !PostUtils::is_image($post)) {
				return '';
			}
			$image_src = $post->get_attachment()->get_image()->get_src();

			return $image[1] . "<img src=\"${image_src}\"/>" . $image[3];
		}, $tpl);
	}

	/**
	 * @param string $tpl
	 * @param Post $post
	 *
	 * @return string
	 */
	private function render_album($tpl, $post)
	{
		return preg_replace_callback('/(<!--\s+wp:gallery.*?-->).*?(<figure.*?>).*?(<\/figure>).*?(<!--\s+\/wp:gallery\s+-->)/si', function ($album) use ($post) {
			if (count($album) !== 5) {
				return '';
			}
			$sub_attachments = $post->get_attachment()->get_sub_attachments();

			$content = '';
			$content .= $album[1];
			$content .= $album[2];
			$content .= '<ul class="blocks-gallery-grid">';
			foreach ($sub_attachments as $sub_attachment) {
				$image_src = null;
				if (!empty($sub_attachment->get_image())) {
					$image_src = $sub_attachment->get_image()->get_src();
				}
				if (!empty($image_src)) {
					$content .= '<li class="blocks-gallery-item">';
					$content .= '<figure>';
					$content .= "<img src=\"{$image_src}\" alt=\"{$image_src}\" data-full-url=\"{$image_src}\" />";
					$content .= '</figure>';
					$content .= '</li>';
				}
			}
			$content .= '</ul>';
			$content .= $album[3];
			$content .= $album[4];

			return $content;
		}, $tpl);
	}

	/**
	 * @param string $tpl
	 * @param Post $post
	 *
	 * @return string
	 */
	private function render_video($tpl, $post)
	{
		return preg_replace_callback('/(<!--\s+wp:video.+?-->.*?)<video(.*?)><\/video>(.*?<!--\s+\/wp:video\s+-->)/si', function ($video) use ($post) {
			if (count($video) !== 4) {
				return '';
			}
			$video_src = 'src="' . ($post->get_video() === null
				? $post->get_attachment()->get_source()
				: $post->get_video()->get_src()) . '"';
			$poster = '';
			if ($post->get_video() !== null && $post->get_video()->get_thumb() !== null) {
				$poster = 'poster="' . $post->get_video()->get_thumb()->get_src() . '"';
			}

			$attrs = $video[2];
			$attrs = trim(preg_replace('/src=".*?"/', $video_src, $attrs));
			$attrs = trim(preg_replace('/poster=".*?"/', $poster, $attrs));

			return $video[1] . "<video $attrs></video>" . $video[3];
		}, $tpl);
	}
}
