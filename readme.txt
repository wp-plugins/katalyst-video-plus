=== Plugin Name ===
Contributors: Keiser Media
Donate link: http://keisermedia.com/projects/katalyst-video-plus/
Tags: import, audit, youtube, thumbnail, twitch, vimeo, ustream, 
Requires at least: 3.5
Tested up to: 4.0.0
Stable tag: 2.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create a Multiple Source Video Network with WordPress.

== Description ==

Katalyst Video Plus enables automatic content syncing between a video content or streaming services and WordPress.

**Who is using Katalyst Video Plus?**
Take a look at some of the best sites in the [Katalyst Video Plus Showcase](http://katalystvideoplus.com/showcase/ "Katalyst Video Plus Showcase").

**Extensions**
Katalyst Video Plus has free and premium extensions to expand functionality. All officially approved extensions can be found in the [Katalyst Videos Plus Add-ons](http://katalystvideoplus.com/extensions/ "Katalyst Videos Plus Add-ons") section.

**More Information**
Visit the Katalyst Video Plus website for more information on [Katalyst Videos Plus](http://katalystvideoplus.com/ "Katalyst Videos Plus").

Must be used with KVP service bridge plugins to interact with hosting services.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `katalyst-video-plus` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Why do I need an developer key for YouTube? =
YouTube's anonymous api has a limited amount of requests per day. In most cases, that limit is exhausted rather quickly. A developer key allows for 50,000,000 requests per day.

= Why do only a few users show up as authors? =
By default, authorship can only be attributed to users with the 'author' role or greater.

== Screenshots ==

1. Front-end Display
2. Dashboard
3. Accounts
4. Action Log

== Changelog ==

= 2.1.0 =
* [Changed] YouTube Basic api item limit increased to 50.
* [Changed] YouTube Basic playlist id identifier.
* [Fixed] Upgrade code running every page load.

= 2.0.1 =
* [Changed] Image only imports if the featured image is not set.
* [Fixed] Thumbnail display in archives.

= 2.0.0 =
* [Added] Dashboard featuring statistics with a force import and force audit option.
* [Added] Full audit performed once every 24 hours.
* [Added] Single post audit performed if post visited within an hour since last audit.
* [Added] Duplicate posts are deleted automatically.
* [Added] YouTube Basic is now integrated into the core plugin.
* [Added] Action log can now be purged based on days since entry was added.
* [Added] More actions and filters for customization.
* [Changed] 'Sources' is now termed as 'Accounts'.
* [Changed] 'Error Log' is now termed as 'Action Log'.
* [Changed] Accounts now report connection status.
* [Removed] Changing import post format.

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
* [Fixed] 'Repair All' feature for multiple sources.
* [Fixed] Misplaced request arguments.
* [Removed] Endpoint verification.
* [Removed] 'Processing comments' €notification as feature is not currently active.

= 1.0.1 =
* [Fixed] Multiple inclusions of file.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 2.0.0 =
Deactivate and remove KVP: YouTube Lite from the plugins as it is integrated into the core plugin.

= 1.2.0 =
Upgrade with provider plugins for compatability