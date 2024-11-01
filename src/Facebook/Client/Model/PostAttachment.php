<?php

namespace WPSWLR\Facebook\Client\Model;

class PostAttachment
{

	/** @var string $type */
	private $type;
	/** @var string $media_type */
	private $media_type;
	/** @var string|null $target */
	private $target;
	/** @var string|null $source */
	private $source;
	/** @var Media|null $image */
	private $image;
	/** @var PostSubAttachment[] $sub_attachment */
	private $sub_attachments;

	private function __construct()
	{
	}

	/**
	 * @param array|null $data
	 *
	 * @return PostAttachment|null
	 */
	public static function from_data($data)
	{
		if (empty($data) || empty($data['type']) || empty($data['media_type'])) {
			return null;
		}

		$attachment = new PostAttachment();
		$attachment->type = $data['type'];
		$attachment->media_type = $data['media_type'];
		$attachment->target = $data['target']['id'] ?? null;
		$attachment->source = $data['media']['source'] ?? null;
		$attachment->image = Media::from_data($data['media']['image'] ?? null);

		$sub_attachments = [];
		foreach ($data['subattachments']['data'] ?? [] as $sa) {
			$s = PostSubAttachment::from_data($sa);
			if (!empty($s)) {
				$sub_attachments[] = $s;
			}
		}
		$attachment->sub_attachments = $sub_attachments;

		return $attachment;
	}

	/**
	 * @return string
	 */
	public function get_type()
	{
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function get_media_type()
	{
		return $this->media_type;
	}

	/**
	 * @return string|null
	 */
	public function get_target()
	{
		return $this->target;
	}

	/**
	 * @return string|null
	 */
	public function get_source()
	{
		return $this->source;
	}

	/**
	 * @return Media|null
	 */
	public function get_image()
	{
		return $this->image;
	}

	/**
	 * @return PostSubAttachment[]
	 */
	public function get_sub_attachments()
	{
		return $this->sub_attachments;
	}
}
