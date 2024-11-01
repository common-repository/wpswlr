=== WPSwlr ===
Contributors: bttrs
Tags: facebook, posts, feed, integration
Requires at least: 5.8
Tested up to: 6.2
Requires PHP: 7.4
Stable tag: 1.2.9
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Load posts from your Facebook Page feed to the WordPress website in a simple way.

== Description ==

WPSwlr is a WordPress plugin for Facebook integration, It allows users to load posts from Facebook to their webpage.
This is useful if you add posts to your Facebook page regularly (like a daily menu in restaurants) and you want to have
actual information also on your webpage without having to create posts manually.

There are multiple similar plugins, but we decided to create our own because none of them are able to load posts as
WordPress posts but usually work with a shortcode. WPSwlr loads posts as if you create them directly in WordPress and
perfectly fits your page design without any appearance customization.

### Key features

* Loads posts from Facebook as a WordPress posts. It means that they can be used anywhere at the page like posts created
  directly in the WordPress.
* You can exclude Facebook posts from being loaded.
* Assigns selected category to loaded posts.
* Edit post templates to change layout and appearance.

### How it works

* Posts are pulled from the Facebook two times per day with a cron job. You can run the load process manually if you
  need.
* For each Facebook post, a new WordPress post is created.
* If a post has multiple images, it is created as a classic WordPress gallery - it works with different LightBox
  plugins.
* Images from Facebook are not downloaded and stored locally but only link to the Facebook's CDN is used on the front
  page.

### Requirements

* **Facebook Page** with administrator rights - you need to be the administrator of the Page you want to use.
* **Facebook Access Token** is required to get data from Facebook. Token has to have allowed _pages_read_engagement_ and
  _pages_read_user_content_ [permissions](https://developers.facebook.com/docs/permissions/reference#permissions).

If you don't know how to create Access Token, you can follow
our **[step-by-step guide](https://wpswlr.bttrs.org/how-to/create-facebook-access-token)**

== Screenshots ==

1. Connect Facebook Page
2. Plugin Settings
3. Imported Facebook Posts
4. Single Facebook Post
5. Frontend page with Facebook posts
6. Frontend page with single Facebook post

== Changelog ==
= 1.2.9 =
* WP 6.2 compatibility

= 1.2.8 =
* Facebook page can be reconnected for example when access token expired.

= 1.2.7 =
* It is now possible to configure post title

= 1.2.6 =
* Added possibility to enable excerpts for long posts

= 1.2.5 =
* Fix posts with album rendering

= 1.2.4 =
* WordPress 6.1 compatibility
* Remove compatibility of older WordPress and php

= 1.2.3 =
* WordPress 6.0 compatibility

== Changelog ==
= 1.2.2 =
* Bug fixes

= 1.2.1 =
* Compatibility with WP 5.9

= 1.2.0 =
* New: Separate "photos as Featured Images" setting for galleries
* New: You can now edit post templates to edit posts layout and appearance
* Changed: Posts are displayed in admin with block editor
* Several minor fixes and enhancements

= 1.1.0 =
* New: Possibility to filter loaded facebook posts by patterns
* New: Post photos can be saved as Featured Image
* Changed: minimum supported version of php is now 7.0
* Changed: minimum supported version of WordPress is now 5.3
* Several minor fixes and enhancements
