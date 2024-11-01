<?php

namespace WPSWLR\Facebook\PostTypes;

class FacebookPostTemplateAlbum extends FacebookPostTemplate
{

	const SLUG = WPSWLR_KEY . '-fbp-tpl-g';

	protected function slug()
	{
		return self::SLUG;
	}

	protected function label()
	{
		return __('Facebook Post Album Templates', 'wpswlr');
	}

	protected function template()
	{
		return [FacebookPostTemplateBlocks::ALBUM, FacebookPostTemplateBlocks::PARAGRAPH];
	}
}
