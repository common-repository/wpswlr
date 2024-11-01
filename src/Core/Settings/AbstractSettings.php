<?php

namespace WPSWLR\Core\Settings;

abstract class AbstractSettings
{

	/** @var array $options */
	protected $options = [];

	/**
	 * @return array
	 */
	protected abstract function get_defaults();

	/**
	 * @param array $options
	 */
	public function __construct($options = [])
	{
		foreach ($options as $key => $value) {
			$this->$key = $value;
		}
	}

	public function __isset($name)
	{
		$defaults = $this->get_defaults();

		return array_key_exists($name, $defaults) && (isset($this->options[$name]) || isset($defaults[$name]));
	}

	public function __unset($name)
	{
		unset($this->options[$name]);
	}

	public function __get($name)
	{
		$defaults = $this->get_defaults();

		if (!array_key_exists($name, $defaults)) {
			return null;
		}
		if (!array_key_exists($name, $this->options)) {
			return $defaults[$name];
		}

		return $this->options[$name];
	}

	public function __set($name, $value)
	{
		if (!array_key_exists($name, $this->get_defaults())) {
			return;
		}
		$this->options[$name] = $value;
	}

	/**
	 * @return array
	 */
	public function to_array()
	{
		return array_merge([], $this->get_defaults(), $this->options);
	}
}
