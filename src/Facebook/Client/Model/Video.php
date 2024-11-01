<?php

namespace WPSWLR\Facebook\Client\Model;

class Video
{

	const FACEBOOK_FIELDS = [
		'id',
		'source',
		'thumbnails{width,height,uri,is_preferred,scale}',
	];

	/** @var string $src */
	private $src;
	/** @var Media|null $thumb */
	private $thumb;

	private function __construct()
	{
	}

	/**
	 * @param array|null $data
	 *
	 * @return Video|null
	 */
	public static function from_data($data)
	{
		if (empty($data) || empty($data['source'])) {
			return null;
		}

		$video = new Video();
		$video->src = $data['source'];

		if (!empty($data['thumbnails']) && !empty($data['thumbnails']['data'])) {
			$thumb_data = null;
			foreach ($data['thumbnails']['data'] as $t) {
				if ($t['is_preferred']) {
					$thumb_data = $t;
					break;
				}
			}
			if (!$thumb_data) {
				foreach ($data['thumbnails']['data'] as $t) {
					if ($t['scale'] === 1) {
						$thumb_data = $t;
						break;
					}
				}
			}
			if (!$thumb_data) {
				$thumb_data = $data['thumbnails']['data'][0];
			}
			$video->thumb = Media::from_data([
				'src' => $thumb_data['uri'] ?? null,
				'width' => $thumb_data['width'] ?? null,
				'height' => $thumb_data['height'] ?? null,
			]);
		}


		return $video;
	}

	/**
	 * @return string
	 */
	public function get_src()
	{
		return $this->src;
	}

	/**
	 * @return Media|null
	 */
	public function get_thumb()
	{
		return $this->thumb;
	}
}
