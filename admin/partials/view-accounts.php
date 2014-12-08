<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }

/**
* Lists KVP accounts
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
*
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/admin/partials
*/

if ( !current_user_can('import') )
    wp_die('You do not have sufficient permissions to access this page.');

$accounts = new KVP_Accounts_Table();

$sidebar = ( current_user_can('publish_posts') ) ? ' columns-2' : '';

?>
<div class="wrap">
	<h2>
		<?php _e('Accounts', 'kvp'); ?>
	</h2>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder<?php echo $sidebar; ?>">
	        <div id="post-body-content">
	        	<form method="post">
	        		<?php settings_fields('kvp_accounts'); ?>
	        		<?php $accounts->prepare_items(); ?>
	        		<?php $accounts->display(); ?>
				</form>
				<?php
				if ( $accounts->has_items() )
					$accounts->inline_edit();
				?>
				<div id="ajax-response"></div>
	        </div>
			<?php if ( !empty( $sidebar ) ) : ?>
		    <div id="postbox-container-1" class="postbox-container">
		    <?php do_meta_boxes( 'kvp_accounts', 'side', '' ); ?>
		    </div>
		    <?php endif; ?>
		</div>
	</div>
</div>