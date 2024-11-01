<?php

namespace WPSWLR\Facebook\PostTypes;

class FacebookPostTemplateVideo extends FacebookPostTemplate
{

	const SLUG = WPSWLR_KEY . '-fbp-tpl-v';

	protected function slug()
	{
		return self::SLUG;
	}

	protected function label()
	{
		return __('Facebook Post Video Templates', 'wpswlr');
	}

	protected function template()
	{
		return [FacebookPostTemplateBlocks::VIDEO, FacebookPostTemplateBlocks::PARAGRAPH];
	}
}
