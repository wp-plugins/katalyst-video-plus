<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }

if ( !current_user_can('import') )
    wp_die('You do not have sufficient permissions to access this page.');

?>
<div class="wrap">
	<?php screen_icon('upload'); ?>
	<h2><?php _e('Error Log', 'kvp'); ?>
	<?php echo sprintf('<a href="?page=%s&action=%s&_wpnonce=%s" class="add-new-h2">%s</a>', $_REQUEST['page'], 'repair', wp_create_nonce('kvp_repair_nonce'), __('Repair All', 'kvp')); ?></h2>
	<div id="katalyst-admin-general" class="metabox-holder">
	    <div id="post-body">
	        <div id="post-body-content">
	        	<form method="post">
	        		<?php settings_fields('kvp'); ?>
					<?php $kvp_errors->display(); ?>
				</form>
	        </div>
	    </div>
	</div>
</div>