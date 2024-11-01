<?php

namespace WPSWLR\Facebook\Client\Model;

class Media
{

	/** @var string $src */
	private $src;
	/** @var int|null $width */
	private $width;
	/** @var int|null $height */
	private $height;

	private function __construct()
	{
	}

	/**
	 * @param array|null $data
	 *
	 * @return Media|null
	 */
	public static function from_data($data)
	{
		if (empty($data) || empty($data['src'])) {
			return null;
		}

		$media = new Media();
		$media->src = $data['src'];
		$media->width = $data['width'] ?? null;
		$media->height = $data['height'] ?? null;

		return $media;
	}

	/**
	 * @return string
	 */
	public function get_src()
	{
		return $this->src;
	}

	/**
	 * @return int|null
	 */
	public function get_width()
	{
		return $this->width;
	}

	/**
	 * @return int|null
	 */
	public function get_height()
	{
		return $this->height;
	}
}
