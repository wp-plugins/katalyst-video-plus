=== Plugin Name ===
Contributors: Keiser Media
Donate link: http://keisermedia.com/projects/katalyst-video-plus/
Tags: import, youtube, thumbnail, twitch, vimeo, ustream, 
Requires at least: 3.5
Tested up to: 3.8.1
Stable tag: 1.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically import video from video hosting providers to your website.

== Description ==

Katalyst Video Plus enables automatic content syncing between a video content or streaming provider and WordPress.

**Who is using Katalyst Video Plus?**
Take a look at some of the best sites in the [Katalyst Video Plus Showcase](http://katalystvideoplus.com/showcase/ "Katalyst Video Plus Showcase").

**Extensions**
Katalyst Video Plus has free and premium extensions to expand functionality. All officially approved extensions can be found in the [Katalyst Videos Plus Add-ons](http://katalystvideoplus.com/extensions/ "Katalyst Videos Plus Add-ons") section.

**More Information**
Visit the Katalyst Video Plus website for more information on [Katalyst Videos Plus](http://katalystvideoplus.com/ "Katalyst Videos Plus").

Must be used with KVP provider plugins to interact with hosting providers.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `katalyst-video-plus` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Upload and activate Provider plugins.

== Frequently Asked Questions ==

== Screenshots ==

1. Front-end display with Import Post Format settings as Standard
2. Source List
3. Import Process
4. Error Log

== Changelog ==

= 1.2.2 =
* [Added] 'Next Import' Column to the Sources Page to show when the next import will occur.
* [Changed] 'Add Source'metabox now links to the KVP extension page.
* [Fixed] 'Initial Import Pending' for most status states on Source page

= 1.2.1 =
* [Added] License Options

= 1.2.0 =
* [Added] Ability to edit pre-existing sources.
* [Changed] 'Add Source'metabox now provides a link on where to get provider plugins if not providers are installed.
* [Changed] Multiple imports from the same source now prevented.
* [Updated] 'Sources' Admin Page now responsive.
* [Removed] 'kvp_add_source_fields' action.

= 1.1.2 =
* [Changed] 'the_content' filter now only effects 'post' post types.
* [Changed] Both post thumbnail and video do not show in archive and single posts.

= 1.1.1 =
* [Fixed] Wrong inclusion path.

= 1.1.0 =
* [Added] Audit feature.
* [Fixed] Undefined index on repair page.
* [Fixed] “Repair All” feature for multiple sources.
* [Fixed] Misplaced request arguments.
* [Removed] Endpoint verification.
* [Removed] "Processing comments.”notification as feature is not currently active.

= 1.0.1 =
* [Fixed] Multiple inclusions of file.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.2.0 =
Upgrade with provider plugins for compatability