<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }

/**
* Displays the log
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
*
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/admin/partials
*/

if ( !current_user_can('import') )
    wp_die('You do not have sufficient permissions to access this page.');

$action_log = new KVP_Action_Log_Table();
?>
<div class="wrap">
	<h2><?php _e('Activity Log', 'kvp'); ?>
	<div id="katalyst-admin-general" class="metabox-holder">
	    <div id="post-body">
	        <div id="post-body-content">
	        	<form method="post">
	        	<?php
	        		settings_fields('kvp');
	        		$action_log->prepare_items();
	        		$action_log->display();
	        	?>
				</form>
	        </div>
	    </div>
	</div>
</div>