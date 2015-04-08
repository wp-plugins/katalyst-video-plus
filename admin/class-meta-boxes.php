<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* The dashboard-specific functionality of the plugin.
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/admin
* @author     Keiser Media <support@keisermedia.com>
*/
class Katalyst_Video_Plus_Meta_Boxes {
	
	/**
	 * Displays KVP usage statistics
	 *
	 * @since    2.0.0
	 */
	public function statistics() {
		
		include( 'partials/meta-box-statistics.php' );
		
	}
	/**
	 * Displays KVP stress forecast
	 *
	 * @since    2.0.0
	 */
	public function stress_forecast() {
		
		include( 'partials/meta-box-stress-forecast.php' );
		
	}
	
	/**
	 * Displays system information on dashboard
	 *
	 * @since    2.0.0
	 */
	public function system_info() {
		
		include( 'partials/meta-box-system-info.php' );
		
	}
	
	/**
	 * Encourages users to rate on WordPress.org
	 *
	 * @since    2.0.0
	 */
	public function rate_us() {
		
		include( 'partials/meta-box-rate-us.php' );
		
	}
	
	/**
	 * Displays a random extension
	 *
	 * @since    2.0.0
	 */
	public function extensions() {
		
		include( 'partials/meta-box-extensions.php' );
		
	}
	
	/**
	 * Form to connect WP to service
	 *
	 * @since    2.0.0
	 */
	public function connect_source() {
		
		include_once( ABSPATH . 'wp-admin/includes/meta-boxes.php' );
		
		$category_args = array( 'args' => array( 'taxonomy' => 'kvp_video_category' ) );
		$services = apply_filters( 'kvp_services', array() );
		$services_types = array();
		
		foreach( $services as $service => $settings )
			$services_types[$service]['types'] = $settings['types'];
		
		$types = apply_filters( 'kvp_source_types', kvp_get_source_types() );
		
		include( 'partials/meta-box-connect-source.php' );
		
	}
	
}