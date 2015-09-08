<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }

/**
* Provides a statistics dashboard
*
* @link       http://katalystvideoplus.com
* @since      3.0.0
*
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/admin/partials
*/

if ( !current_user_can('edit_dashboard') )
    wp_die('You do not have sufficient permissions to access this page.');

?>
<div class="wrap">
	<h2>
		<?php _e('Statistics', 'kvp'); ?>
	</h2>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
	        <div id="post-body-content">
		    <?php do_meta_boxes('kvp_statistics', 'normal', ''); ?>
	        </div>
		    <div id="postbox-container-1" class="postbox-container">
		    <?php do_meta_boxes('kvp_statistics', 'side', ''); ?>
		    </div>
		</div>
	</div>
</div>