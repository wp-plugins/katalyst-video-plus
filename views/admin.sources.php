<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }

if ( !current_user_can('import') )
    wp_die('You do not have sufficient permissions to access this page.');

?>
<div class="wrap">
	<?php screen_icon('upload'); ?>
	<h2>
		<?php _e('Sources', 'kvp'); ?>
	</h2>
	<div id="katalyst-admin-general" class="metabox-holder<?php echo $sidebar; ?>">
		<?php if ( current_user_can('publish_posts') ) : ?>
	    <div id="side-info-column" class="inner-sidebar">
	    <?php do_meta_boxes('kvp_sources', 'side', ''); ?>
	    </div>
	    <?php endif; ?>
	    <div id="post-body">
	        <div id="post-body-content">
	        	<form method="post">
	        		<?php settings_fields('kvp'); ?>
					<?php $kvp_sources->display(); ?>
				</form>
	        </div>
	    </div>
	</div>
</div>