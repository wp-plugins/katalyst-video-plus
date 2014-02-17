<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }

if ( !current_user_can('install_plugins') )
    wp_die('You do not have sufficient permissions to access this page.');

?>
<div class="wrap">
	<?php screen_icon('upload'); ?>
	<h2>
		<?php _e('Import Log', 'kvp'); ?>
	</h2>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder">
	        <div id="post-body-content">
    		<?php $kvp_import_log = new KVP_Log_Table(); ?>
			<?php $kvp_import_log->prepare_items(); ?>
			<?php $kvp_import_log->display(); ?>
	        </div>
		</div>
	</div>
</div>