<?php

namespace WPSWLR\Facebook\PostTypes;

class FacebookPostTemplateGeneral extends FacebookPostTemplate
{

	const SLUG = WPSWLR_KEY . '-fbp-tpl';

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
		return [FacebookPostTemplateBlocks::IMAGE, FacebookPostTemplateBlocks::PARAGRAPH];
	}
}
