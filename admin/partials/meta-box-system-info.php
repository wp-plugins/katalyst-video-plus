<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* Displays system information
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
*
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/admin/partials
*/

$next_import = ( 'locked' === get_transient( 'kvp_import_lock' ) ) ? __( 'Import in Process', 'kvp' ) : human_time_diff( current_time( 'timestamp', true ), wp_next_scheduled( 'kvp_import_cron' ) );

?>
<ul id="kvp-system-info">
	<li class="next_import"><strong><?php _e( 'Next Import', 'kvp' ); ?>:</strong> <?php echo $next_import ?></li>
<?php if( 'locked' !== get_transient( 'kvp_import_lock' ) ) : ?>
	<form class="import_submit" method="post">
	<?php wp_nonce_field('kvp_force_import', '_kvp_import_nonce'); ?>
	<input type="submit" name="import_submit" id="import_submit" class="button button-primary button-large" value="<?php _e( 'Force Import', 'kvp'); ?>" />
	</form>
	<form class="audit_submit" method="post">
	<?php wp_nonce_field('kvp_force_audit', '_kvp_audit_nonce'); ?>
	<input type="submit" name="audit_submit" id="audit_submit" class="button button-large" value="<?php _e( 'Force Audit', 'kvp'); ?>" />
	</form>
<?php endif; ?>
</form>
</ul>