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
	public function connect_account() {
		
		include_once( ABSPATH . 'wp-admin/includes/meta-boxes.php' );
		
		$category_args = array();
		$services = apply_filters( 'kvp_services', array() );
		
		include( 'partials/meta-box-connect-account.php' );
		
	}
	
}