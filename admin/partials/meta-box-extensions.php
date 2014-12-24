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
?>
<div class="wrap" id="kvp-extensions">
<?php if ( false === ( $cache = get_transient( 'katalystvideoplus_extensions_feed' ) ) ) :
	$feed = wp_remote_get( 'http://katalystvideoplus.com/?feed=extensions', array( 'sslverify' => false ) );
	if ( ! is_wp_error( $feed ) ) {
		if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
			$cache = wp_remote_retrieve_body( $feed );
			set_transient( 'katalystvideoplus_extensions_feed', $cache, 3600 );
		}
	} else {
		$cache = '<div class="error"><p>' . __( 'There was an error retrieving the extensions list from the server. Please try again later.', 'kvp' ) . '</div>';
	}
endif; ?>
	<a href="http://katalystvideoplus.com/extensions/?utm_source=plugin-dashboard&utm_medium=plugin&utm_content=Random%20Extension&utm_campaign=KVP%20Plugin%20Dashboard" class="button-primary" title="<?php _e( 'Browse All Extensions', 'kvp' ); ?>" target="_blank"><?php _e( 'Browse All Extensions', 'kvp' ); ?></a>
</div>