<?php

namespace WPSWLR\Facebook;

class Facebook
{
	const GRAPH_VERSION = 'v12.0';
	const GRAPH_URL = 'https://graph.facebook.com/' . self::GRAPH_VERSION . '/';
	const SCOPE = ['pages_show_list', 'pages_read_engagement', 'pages_read_user_content'];
}
