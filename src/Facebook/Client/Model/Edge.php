<?php

namespace WPSWLR\Facebook\Client\Model;

use ArrayIterator;
use Closure;
use IteratorAggregate;

class Edge implements IteratorAggregate
{

	/** @var array $items */
	private $items;
	/** @var bool $has_cursor_after */
	private $has_cursor_after;
	/** @var string|null $cursor_after */
	private $cursor_after;

	private function __construct()
	{
	}

	/**
	 * @param array $data
	 *
	 * @return Edge
	 */
	public static function from_data($data)
	{
		$edge = new Edge();
		$edge->items = $data['data'] ?? [];
		$edge->has_cursor_after = !empty($data['paging']['next']) && !empty($data['paging']['cursors']['after']);
		$edge->cursor_after = $edge->has_cursor_after ? $data['paging']['cursors']['after'] : null;

		return $edge;
	}

	/**
	 * @return array
	 */
	public function get_items()
	{
		return $this->items;
	}

	/**
	 * @return string|null
	 */
	public function get_cursor_after()
	{
		return $this->cursor_after;
	}

	/**
	 * @return bool
	 */
	public function has_cursor_after()
	{
		return $this->has_cursor_after;
	}

	/**
	 * @param Closure $callback
	 *
	 * @return Edge
	 */
	public function map($callback)
	{
		$edge = new Edge();
		$edge->has_cursor_after = $this->has_cursor_after;
		$edge->cursor_after = $this->cursor_after;
		$edge->items = array_filter(array_map($callback, $this->items));

		return $edge;
	}

	/**
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->items);
	}
}
