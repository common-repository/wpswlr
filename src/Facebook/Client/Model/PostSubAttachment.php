<?php

namespace WPSWLR\Facebook\Client\Model;

class PostSubAttachment
{

	/** @var string $media_type */
	private $media_type;
	/** @var Media $image */
	private $image;

	private function __construct()
	{
	}

	/**
	 * @param array|null $data
	 *
	 * @return PostSubAttachment|null
	 */
	public static function from_data($data)
	{
		if (empty($data) || empty($data['media_type'])) {
			return null;
		}
		$image = Media::from_data($data['media']['image'] ?? null);
		if (empty($image)) {
			return null;
		}

		$sub_attachment = new PostSubAttachment();
		$sub_attachment->media_type = $data['media_type'];
		$sub_attachment->image = $image;

		return $sub_attachment;
	}

	/**
	 * @return string
	 */
	public function get_media_type()
	{
		return $this->media_type;
	}

	/**
	 * @return Media
	 */
	public function get_image()
	{
		return $this->image;
	}
}
