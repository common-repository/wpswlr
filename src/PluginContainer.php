<?php

namespace WPSWLR;

use ArrayAccess;
use WPSWLR\Core\ActivateListener;
use WPSWLR\Core\DeactivateListener;
use WPSWLR\Core\InitListener;
use WPSWLR\Core\UninstallListener;

class PluginContainer implements ArrayAccess
{

	/** @var PluginContainer|null $instance */
	private static $instance = null;
	/** @var array $components */
	private $components = [];

	private function __construct()
	{
	}

	/**
	 * @return PluginContainer
	 */
	public static function getInstance()
	{
		if (self::$instance === null) {
			self::$instance = new PluginContainer();
		}

		return self::$instance;
	}

	public function __get($name)
	{
		return $this[$name];
	}

	public function offsetExists($offset)
	{
		return isset($this->components[$offset]);
	}

	public function offsetGet($offset)
	{
		if (is_callable($this->components[$offset])) {
			return call_user_func($this->components[$offset], $this);
		}

		return $this->components[$offset] ?? null;
	}

	public function offsetSet($offset, $value)
	{
		$this->components[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->components[$offset]);
	}

	/**
	 *
	 */
	public function init()
	{
		foreach ($this->components as $key => $_) {
			$component = $this[$key];

			if ($component instanceof InitListener) {
				$component->on_init();
			}
		}
	}

	/**
	 *
	 */
	public function activate()
	{
		foreach ($this->components as $key => $_) {
			$component = $this[$key];

			if ($component instanceof ActivateListener) {
				$component->on_activate();
			}
		}
	}

	/**
	 *
	 */
	public function deactivate()
	{
		foreach ($this->components as $key => $_) {
			$component = $this[$key];

			if ($component instanceof DeactivateListener) {
				$component->on_deactivate();
			}
		}
	}

	/**
	 *
	 */
	public function uninstall()
	{
		foreach ($this->components as $key => $_) {
			$component = $this[$key];

			if ($component instanceof UninstallListener) {
				$component->on_uninstall();
			}
		}
	}

}
