<?php

namespace WPSWLR\Facebook\Loaders;

use WPSWLR\Core\Settings\FacebookOptions;
use WPSWLR\Facebook\Exceptions\FacebookClientException;
use WPSWLR\Facebook\Exceptions\FacebookException;

interface FacebookLoader
{

	/**
	 * @return void
	 *
	 * @throws FacebookClientException
	 * @throws FacebookException
	 */
	function load();

	/**
	 * @param FacebookOptions $prev_options
	 * @param FacebookOptions $options
	 *
	 * @return void
	 */
	function options_changed($prev_options, $options);

	/**
	 * @return void
	 */
	function clean();
}
