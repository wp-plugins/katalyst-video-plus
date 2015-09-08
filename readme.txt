=== Katalyst Video Plus ===
Contributors: Keiser Media
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=billing@katalystvideoplus.com&item_name=Donation+for+Katalyst+Video+Plus
Tags: import, audit, youtube, thumbnail, twitch, vimeo, ustream, 
Requires at least: 3.4.0
Tested up to: 4.3
Stable tag: 3.2.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Katalyst Video Plus is a powerful plugin that automatically creates video posts and syncs content from video hosting services.

== Description ==

Katalyst Video Plus enables automatic content syncing between a video content or streaming services and WordPress. Add a source, like a playlist from YouTube, and KVP will automatically create posts for that source.

= Extensions =
Katalyst Video Plus has free and premium extensions to expand functionality. All officially approved extensions can be found in the [Katalyst Videos Plus Add-ons](http://katalystvideoplus.com/extensions/ "Katalyst Videos Plus Add-ons") section.

= Who is using Katalyst Video Plus? =
Take a look at some of the best sites in the [Katalyst Video Plus Showcase](http://katalystvideoplus.com/showcase/ "Katalyst Video Plus Showcase").

= More Information =
Visit the Katalyst Video Plus website for more information on [Katalyst Videos Plus](http://katalystvideoplus.com/ "Katalyst Videos Plus").

== Installation ==

1. Upload `katalyst-video-plus` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Why do I need an developer key for YouTube? =
YouTube's anonymous api has a limited amount of requests per day. In most cases, that limit is exhausted rather quickly. A developer key allows for 50,000,000 requests per day.

= Why do only a few users show up as authors? =
By default, authorship can only be attributed to users with the 'author' role or greater.

= Why are some of the 'Total Videos' for the Channel Type inaccurate? =
The 'Total Videos' statistic is returned from YouTube; however, this statistic may includes non-public videos.

== Screenshots ==

1. Front-end Display
2. Video Index
3. Categories
4. Source Index
5. Source Menu
6. Source Test Screen
7. Activity Log
8. Settings

== Changelog ==

= 3.2.1 ( Sept. 8, 2015 ) =
* [Fixed] Video category taxonomy priority. (Issue #28)

= 3.2.0 ( Sept. 1, 2015 ) =
* [Added] Settings to change video display dimensions. (Issue #25)
* [Changed] 'New Video' link for admin bar disabled by default. (Issue #27)

= 3.1.2 ( Jul. 3, 2015 ) =
* [Fixed] Flush rewrite rules. (Issue #19)
* [Fixed] Inline style breaking in admin. (Issue #23)
* [Fixed] Undefined Index if API Key is not set. (Issue #24)

= 3.1.1 (Jun. 25, 2015) =
* [Fixed] Improperly Named Function

= 3.1.0 (Jun. 25, 2015) =
* [Added] Filter 'kvp_enable_new_posts' to reactivate new video links.
* [Changed] 'Add New Video' links disabled by default.
* [Fixed] Activity Log user ID logging.
* [Fixed] Activity Log variables not defined.
* [Improved] Video category labels. (Issue #20)
* [Improved] Manual run now disables if previously clicked. (Issue #21)
* [Removed]	'Videos' source type.

= 3.0.1 (Apr. 22, 2015) =
* [Fixed] “add_query_args” exploit.
* [Fixed] Missing empty variables.
* [Changed] YouTube player ID.

= 3.0.0 (Apr. 8, 2015) =
* [Added] Sources can now contain Channels, Playlists, Videos, or Search Terms.
* [Added] Test menu item for sources.
* [Changed] Videos now import as a custom post type.
* [Changed] "Accounts" now referred to as "Sources".
* [Changed] "Action Log" to "Activity Log".

= 2.1.2 (Dec. 23, 2014) =
* [Changed]	get_video return values.
* [Improved] Account connection status messages.
* [Removed] Variable 'username' from video embed attributes.

= 2.1.1 (Dec. 19, 2014) =
* [Added] Settings Update Notification.
* [Added] Option to change scheduling for import and audit fequency.
* [Changed] Import and Audit are no longer dependent upon setup accounts.
* [Changed] 'Log' page title changed to 'Action Log'.
* [Fixed] Initial import post format issue.
* [Fixed] Perge log settings identifier.

= 2.1.0 (Dec. 8, 2014) =
* [Added] Change import post format.
* [Added] YouTube API Fallback option.
* [Added] Menu notification for errors with account connections.
* [Changed] Made improvements on how the video embed displays and interacts with post thumbnails and the content.
* [Changed] Duplicate post check only occurs on full audits.
* [Fixed] Imported images show import author.
* [Fixed] Inactive service errors in Accounts.
* [Fixed] Inactive service errors and display in Dashboard.
* [Fixed] Precautionary checks for connection status.

= 2.0.5 (Nov. 28, 2014) =
* [Fixed] Queue looping issue.

= 2.0.4 =
* [Changed] Descriptions for premium extension automated updates.

= 2.0.3 =
* [Added] Options to include and exclude accounts in actions (Video importing included by default).
* [Changed] Actions and filters added to account editor.
* [Fixed] Settings not showing tabs properly.
* [Removed] Code redundency in video embed.

= 2.0.2 =
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