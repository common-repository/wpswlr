<?php

namespace WPSWLR\Core;

interface UninstallListener
{

	/**
	 * @return void
	 */
	public function on_uninstall();
}
