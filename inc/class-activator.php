<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }

/**
 * Fired during plugin activation
 *
 * @link       http://katalystvideoplus.com
 * @since      2.0.0
 *
 * @package    Katalyst_Video_Plus
 * @subpackage Katalyst_Video_Plus/inc
 * @author     Keiser Media <support@keisermedia.com>
 */
class Katalyst_Video_Plus_Activator {

	/**
	 * Run during plugin activation
	 *
	 * @since    2.0.0
	 */
	public static function activate() {
		
		set_transient( '_kvp_about_screen', true, 30 );
		kvp_purge_cron();
		
	}

}