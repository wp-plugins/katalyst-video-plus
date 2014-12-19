<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* Handles all CRON functionality.
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/inc
* @author     Keiser Media <support@keisermedia.com>
*/

class Katalyst_Video_Plus_CRON {
	
	/**
	 * Schedules cron events
	 *
	 * @since    2.0.0
	 */
	public function setup_cron() {
		
		$settings = get_option( 'kvp_settings', array() );
		
		$import_schedule	= isset($settings['import_schedule']) ? $settings['import_schedule'] : 'hourly';
		$audit_schedule		= isset($settings['audit_schedule']) ? $settings['audit_schedule'] : 'daily';
		
		if( !wp_next_scheduled('kvp_import_cron') )
			wp_schedule_event( time() + ( 60 * 60 ), $import_schedule, 'kvp_import_cron' );
		
		elseif( is_admin() && isset( $_POST['_kvp_import_nonce'] ) && wp_verify_nonce( $_POST['_kvp_import_nonce'], 'kvp_force_import' ) ) {
			
            wp_unschedule_event( wp_next_scheduled('kvp_import_cron'), 'kvp_import_cron' );
            wp_schedule_single_event( time() - 1, 'kvp_import_cron' );
				
		}
		
		if( !wp_next_scheduled('kvp_audit_cron') )
			wp_schedule_event( time() + ( 24 * 60 * 60 ), $audit_schedule, 'kvp_audit_cron' );
		
		elseif( is_admin() && isset( $_POST['_kvp_audit_nonce'] ) && wp_verify_nonce( $_POST['_kvp_audit_nonce'], 'kvp_force_audit' ) ) {
			
			wp_unschedule_event( wp_next_scheduled('kvp_audit_cron'), 'kvp_audit_cron' );
            wp_schedule_single_event( time() - 1, 'kvp_audit_cron' );
            
        }
		
		if( !wp_next_scheduled('kvp_purge_log_cron') )
			wp_schedule_event( time(), 'daily', 'kvp_purge_log_cron' );
		
	}
	
	/**
	 * Initilizes regular import event
	 * 
	 * @since 2.0.0
	 */
	public function import_event() {
		
		$kvp_youtube_import = new Katalyst_Video_Plus_Import;
		$kvp_youtube_import->import();
		
	}
	
	/**
	 * Initilizes regular audit event
	 * 
	 * @since 2.0.0
	 */
	public function audit_event() {
		
		$kvp_youtube_import = new Katalyst_Video_Plus_Import;
		$kvp_youtube_import->audit();
		
	}
	
	/**
	 * Purges action log
	 * 
	 * @since 2.0.0
	 */
	public function purge_log() {
		
		$settings = get_option( 'kvp_settings', array() );
		
		if( 'locked' === get_transient( 'kvp_import_lock' ) || ( isset($settings['purge_log']) && 'false' == $settings['purge_log'] ) )
			return;
		
		if( !isset($settings['purge_log']) || ( isset($settings['purge_log']) && false === $settings['purge_log'] ) )
			return;
		
		$limit = ( $settings['purge_log'] * 24 * 60 * 60 );
		
		$action_log = get_option( 'kvp_action_log', array() );
		
		foreach( $action_log as $key => $item ) {
			
			$time_diff = ( time() - $item['date'] );
			
			if( $limit < $time_diff ) {
				
				unset($action_log[$key]);
				update_option( 'kvp_action_log', $action_log );
				
			}
			
			
		}
		
	}
	
	
}