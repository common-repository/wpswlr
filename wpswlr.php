<?php
/**
 * Plugin Name: WPSwlr
 * Plugin URI: https://wpswlr.bttrs.org
 * Description: Load posts from your Facebook Page feed to the WordPress website in a simple way.
 * Version: 1.2.9
 * Author: bttrs
 * Author URI: https://bttrs.org
 * Text Domain: wpswlr
 * Domain Path: /languages
 * License: GPLv3
 **/
if (!defined('WPINC')) {
	die;
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'constants.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use WPSWLR\Admin\PluginSettings;
use WPSWLR\Admin\RestApi;
use WPSWLR\Core\SettingsService;
use WPSWLR\Facebook\Client\FacebookClient;
use WPSWLR\Facebook\Loaders\PostContentRenderer;
use WPSWLR\Facebook\PostTypes\FacebookPost;
use WPSWLR\Facebook\PostTypes\FacebookPostTemplateAlbum;
use WPSWLR\Facebook\PostTypes\FacebookPostTemplateGeneral;
use WPSWLR\Facebook\PostTypes\FacebookPostTemplateVideo;
use WPSWLR\Facebook\Services\ConnectService;
use WPSWLR\Facebook\Services\FacebookLoaderService;
use WPSWLR\Facebook\Services\FacebookPostTemplateService;
use WPSWLR\PluginContainer;

function wpswlr_create_container()
{
	$container = PluginContainer::getInstance();

	$container[SettingsService::class] = new SettingsService();
	$container[FacebookPost::class] = new FacebookPost(
		$container[SettingsService::class]
	);
	$container[FacebookPostTemplateGeneral::class] = new FacebookPostTemplateGeneral();
	$container[FacebookPostTemplateAlbum::class] = new FacebookPostTemplateAlbum();
	$container[FacebookPostTemplateVideo::class] = new FacebookPostTemplateVideo();
	$container[FacebookClient::class] = new FacebookClient(
		$container[SettingsService::class]
	);
	$container[FacebookPostTemplateService::class] = new FacebookPostTemplateService();
	$container[PostContentRenderer::class] = new PostContentRenderer(
		$container[FacebookPostTemplateService::class],
		$container[SettingsService::class]
	);
	$container[ConnectService::class] = new ConnectService(
		$container[FacebookClient::class],
		$container[SettingsService::class]
	);
	$container[FacebookLoaderService::class] = new FacebookLoaderService(
		$container[SettingsService::class],
		$container[FacebookClient::class],
		$container[PostContentRenderer::class]
	);
	$container[PluginSettings::class] = new PluginSettings(
		$container[SettingsService::class]
	);
	$container[RestApi::class] = new RestApi(
		$container[SettingsService::class],
		$container[FacebookPostTemplateService::class],
		$container[ConnectService::class],
		$container[FacebookLoaderService::class]
	);
}

function wpswlr_on_init()
{
	$container = PluginContainer::getInstance();
	$container->init();
}

function wpswlr_on_uninstall()
{
	$container = PluginContainer::getInstance();
	$container->uninstall();
}

function wpswlr_on_activation()
{
	register_uninstall_hook(__FILE__, 'wpswlr_on_uninstall');
	$container = PluginContainer::getInstance();
	$container->activate();
}

function wpswlr_on_deactivation()
{
	$container = PluginContainer::getInstance();
	$container->deactivate();
}

wpswlr_create_container();
register_activation_hook(__FILE__, 'wpswlr_on_activation');
register_deactivation_hook(__FILE__, 'wpswlr_on_deactivation');
add_action('plugins_loaded', function () {
	load_plugin_textdomain('wpswlr', false, WPSWLR_BASE_PATH . 'languages');
});
add_action('init', 'wpswlr_on_init');
