<?php

namespace WPSWLR\Facebook\PostTypes;

class FacebookPostTemplateBlocks
{

	const PLACEHOLDER_IMAGE = WPSWLR_BASE_URL . 'assets/placeholder800x600.jpg';
	const PLACEHOLDER_VIDEO = WPSWLR_BASE_URL . 'assets/placeholder.mp4';

	const PARAGRAPH = [
		'core/paragraph',
		[
			'content' => '[fb-content]',
		],
	];
	const IMAGE = [
		'core/image',
		[
			'url' => self::PLACEHOLDER_IMAGE,
			'align' => 'center',
		],
	];
	const ALBUM = [
		'core/gallery',
		[
			'columns' => 3,
			'images' => [
				[
					'url' => self::PLACEHOLDER_IMAGE,
					'fullUrl' => self::PLACEHOLDER_IMAGE,
					'link' => self::PLACEHOLDER_IMAGE,
				],
				[
					'url' => self::PLACEHOLDER_IMAGE,
					'fullUrl' => self::PLACEHOLDER_IMAGE,
					'link' => self::PLACEHOLDER_IMAGE,
				],
				[
					'url' => self::PLACEHOLDER_IMAGE,
					'fullUrl' => self::PLACEHOLDER_IMAGE,
					'link' => self::PLACEHOLDER_IMAGE,
				],
				[
					'url' => self::PLACEHOLDER_IMAGE,
					'fullUrl' => self::PLACEHOLDER_IMAGE,
					'link' => self::PLACEHOLDER_IMAGE,
				],
				[
					'url' => self::PLACEHOLDER_IMAGE,
					'fullUrl' => self::PLACEHOLDER_IMAGE,
					'link' => self::PLACEHOLDER_IMAGE,
				],
				[
					'url' => self::PLACEHOLDER_IMAGE,
					'fullUrl' => self::PLACEHOLDER_IMAGE,
					'link' => self::PLACEHOLDER_IMAGE,
				],
			],
		],
	];
	const VIDEO = [
		'core/video',
		[
			'align' => 'center',
			'autoplay' => false,
			'loop' => false,
			'muted' => true,
			'preload' => 'none',
			'src' => self::PLACEHOLDER_VIDEO,
		],
	];
}
