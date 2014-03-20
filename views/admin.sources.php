<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }

if ( !current_user_can('import') )
    wp_die('You do not have sufficient permissions to access this page.');

?>
<div class="wrap">
	<?php screen_icon('upload'); ?>
	<h2>
		<?php _e('Sources', 'kvp'); ?>
	</h2>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder<?php echo $sidebar; ?>">
	        <div id="post-body-content">
	        	<form method="post">
	        		<?php settings_fields('kvp'); ?>
	        		<?php $kvp_sources = new KVP_Sources_Table(); ?>
					<?php $kvp_sources->prepare_items(); ?>
					<?php $kvp_sources->display(); ?>
				</form>
				<?php
				if ( $kvp_sources->has_items() )
					$kvp_sources->inline_edit();
				?>
				<div id="ajax-response"></div>
	        </div>
			<?php if ( current_user_can('publish_posts') ) : ?>
		    <div id="postbox-container-1" class="postbox-container">
		    <?php do_meta_boxes('kvp_sources', 'side', ''); ?>
		    </div>
		    <?php endif; ?>
		</div>
	</div>
</div>