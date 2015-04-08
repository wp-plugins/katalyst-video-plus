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
		
		$settings	= get_option( 'kvp_settings', array() );
		$sources	= get_option( 'kvp_sources', array() );
		$schedules	= wp_get_schedules();
		
		$audit_schedule		= isset($settings['audit_schedule']) ? $settings['audit_schedule'] : 'daily';
		
		foreach( $sources as $source ) {
			
			if( !wp_next_scheduled( 'kvp_import_' . $source['id'], array( $source['id'] ) ) && ( 'active' == $source['status'] ) ) {
				
				$schedule	= isset($schedules[$source['schedule_freq']]) ? $source['schedule_freq'] : 'hourly';
				$time		= ( isset($source['schedule_time']) && 3600 < $schedules[$schedule]['interval'] ) ? ( $source['schedule_time'] + $schedules[$schedule]['interval'] ) : time() + $schedules[$schedule]['interval'];
				wp_schedule_event( $time, $schedule, 'kvp_import_' . $source['id'], array( $source['id'] ) );
				
			} elseif( is_admin() && isset( $_POST['_kvp_import_nonce'] ) && wp_verify_nonce( $_POST['_kvp_import_nonce'], 'kvp_force_import' ) ) {

				wp_unschedule_event( wp_next_scheduled( 'kvp_import_' . $source['id'], array( $source['id'] ) ), 'kvp_import_' . $source['id'], array( $source['id'] ) );
				wp_schedule_single_event( time() - 1, 'kvp_import_' . $source['id'], array( $source['id'] ) );
				
			}
			
			if( wp_next_scheduled( 'kvp_import_' . $source['id'], array( $source['id'] ) ) && ( 'inactive' == $source['status'] ) )
				wp_unschedule_event( wp_next_scheduled( 'kvp_import_' . $source['id'], array( $source['id'] ) ), 'kvp_import_' . $source['id'], array( $source['id'] ) );
			
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
	public function import_event( $source_id ) {
		
		$kvp_youtube_import = new Katalyst_Video_Plus_Import;
		$results = $kvp_youtube_import->import( $source_id );
		
		if( false !== $results )
			kvp_activity_log( __( 'Core Import' ), 'automatic', $results );
		
	}
	
	/**
	 * Initilizes regular audit event
	 * 
	 * @since 2.0.0
	 */
	public function audit_event() {
		
		$kvp_youtube_import = new Katalyst_Video_Plus_Import;
		$results = $kvp_youtube_import->audit();
		
		if( false !== $results )
			kvp_activity_log( __( 'Core Import' ), 'audit', $results );
		
	}
	
	/**
	 * Purges action log
	 * 
	 * @since 2.0.0
	 */
	public function purge_log() {
		
		$settings = get_option( 'kvp_settings', array() );
		
		if( !isset($settings['purge_log']) )
			$settings['purge_log'] = 1;
		
		$limit = ( $settings['purge_log'] * 24 * 60 * 60 );
		
		$activity_log = get_option( 'kvp_activity_log', array() );
		
		foreach( $activity_log as $key => $item ) {
			
			$time_diff = ( time() - $item['date'] );
			
			if( $limit < $time_diff ) {
				
				unset($activity_log[$key]);
				
			}
			
		}
		
		update_option( 'kvp_activity_log', $activity_log );
		
	}
	
	
}