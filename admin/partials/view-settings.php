<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }

/**
* Displays settings information
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
*
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/admin/partials
*/

if ( !current_user_can('manage_options') )
    wp_die('You do not have sufficient permissions to access this page.');

$admin_notices = get_settings_errors( 'kvp-settings-notices' );

$tabs['general'] = __( 'General', 'kvp' );
$tabs['display'] = __( 'Display', 'kvp' );
$tabs['import']	 = __( 'Audit & Import', 'kvp' );

if( false != apply_filters( 'kvp_settings_extensions', array() ) ) {
	$tabs['extensions'] = __( 'Extensions', 'kvp' );
}

if( false != apply_filters( 'kvp_settings_licenses', array() ) ) {
	$tabs['licenses'] = __( 'Licenses', 'edd' );
}

$tabs['misc']	 = __( 'Misc', 'kvp' );

$active_tab = isset( $_GET[ 'tab' ] ) && array_key_exists( $_GET['tab'], $tabs ) ? $_GET[ 'tab' ] : 'general';
?>
<div class="wrap">
	<h2><?php _e('KVP Settings', 'kvp'); ?></h2>
	<?php if( !empty($admin_notices) ) : ?>
		<?php foreach( $admin_notices as $notice ) : ?>
	<div class="<?php echo $notice['type']; ?>">
		<p><?php echo $notice['message']; ?></p>
    </div>
    	<?php endforeach; ?>
    <?php endif; ?>
	<h2 class="nav-tab-wrapper">
		<?php
		foreach( $tabs as $tab_id => $tab_name ) {
			
			if( empty( $tab_name ) )
				continue;
			
			$tab_url = add_query_arg( array(
				'settings-updated' => false,
				'tab' => $tab_id
			) );

			$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

			echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">';
				echo esc_html( $tab_name );
			echo '</a>';
		}
		?>
	</h2>
	<div id="tab_container">
		<form method="post" action="options.php">
			<table class="form-table">
			<?php
			settings_fields( 'kvp_settings' );
			do_settings_fields( 'kvp_settings_' . $active_tab, 'kvp_settings_' . $active_tab );
			?>
			</table>
			<?php submit_button(); ?>
		</form>
	</div><!-- #tab_container-->
</div><!-- .wrap -->