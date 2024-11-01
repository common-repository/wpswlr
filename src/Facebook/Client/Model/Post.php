<?php

namespace WPSWLR\Facebook\Client\Model;

class Post
{

	const FACEBOOK_FIELDS = [
		'id',
		'from{id}',
		'is_published',
		'is_hidden',
		'is_expired',
		'privacy{value}',
		'created_time',
		'message',
		'story',
		'permalink_url',
		'status_type',
		'attachments{type,media_type,target{id},media{image{src,width,height},source},subattachments{media_type,media{image{src,width,height}}}}',
	];

	/** @var string $id */
	private $id;
	/** @var string $created_time */
	private $created_time;
	/** @var string $permalink_url */
	private $permalink_url;
	/** @var bool $is_published */
	private $is_published;
	/** @var bool $is_hidden */
	private $is_hidden;
	/** @var bool $is_expired */
	private $is_expired;
	/** @var string|null $privacy */
	private $privacy;
	/** @var string|null $from */
	private $from;
	/** @var string|null $message */
	private $message;
	/** @var string|null $story */
	private $story;
	/** @var string|null $status_type */
	private $status_type;
	/** @var PostAttachment|null $attachment */
	private $attachment;
	/** @var Video|null $video */
	private $video = null;

	private function __construct()
	{
	}

	/**
	 * @param array|null $data
	 *
	 * @return Post|null
	 */
	public static function from_data($data)
	{
		if (empty($data) || empty($data['id']) || empty($data['created_time'])) {
			return null;
		}

		$post = new Post();
		$post->id = $data['id'];
		$post->created_time = $data['created_time'];
		$post->permalink_url = $data['permalink_url'] ?? '';
		$post->is_published = $data['is_published'] ?? false;
		$post->is_hidden = $data['is_hidden'] ?? false;
		$post->is_expired = $data['is_expired'] ?? false;
		$post->privacy = $data['privacy']['value'] ?? null;
		$post->from = $data['from']['id'] ?? null;
		$post->message = $data['message'] ?? null;
		$post->story = $data['story'] ?? null;
		$post->status_type = $data['status_type'] ?? null;
		$post->attachment = PostAttachment::from_data($data['attachments']['data'][0] ?? null);

		return $post;
	}

	/**
	 * @return string
	 */
	public function get_id()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function get_created_time()
	{
		return $this->created_time;
	}

	/**
	 * @return string
	 */
	public function get_permalink_url()
	{
		return $this->permalink_url;
	}

	/**
	 * @return bool
	 */
	public function get_is_published()
	{
		return $this->is_published;
	}

	/**
	 * @return bool
	 */
	public function get_is_hidden()
	{
		return $this->is_hidden;
	}

	/**
	 * @return bool
	 */
	public function get_is_expired()
	{
		return $this->is_expired;
	}

	/**
	 * @return string|null
	 */
	public function get_privacy()
	{
		return $this->privacy;
	}

	/**
	 * @return string|null
	 */
	public function get_from()
	{
		return $this->from;
	}

	/**
	 * @return string|null
	 */
	public function get_message()
	{
		return $this->message;
	}

	/**
	 * @return string|null
	 */
	public function get_story()
	{
		return $this->story;
	}

	/**
	 * @return string|null
	 */
	public function get_status_type()
	{
		return $this->status_type;
	}

	/**
	 * @return PostAttachment|null
	 */
	public function get_attachment()
	{
		return $this->attachment;
	}

	/**
	 * @return Video|null
	 */
	public function get_video()
	{
		return $this->video;
	}

	/**
	 * @param Video|null $video
	 */
	public function set_video($video)
	{
		$this->video = $video;
	}
}
