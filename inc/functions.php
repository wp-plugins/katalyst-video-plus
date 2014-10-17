<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* Miscellanous functions
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/inc
* @author     Keiser Media <support@keisermedia.com>
*/

/**
* Adds messages to action log
* 
* @since 2.0.0
*/
function kvp_action_log( $title = '', $message = '', $type = null ) {
	
	if( is_array($message) || is_object($message) )
		$message = print_r( $message, true );
	
	$log_item = array(
		'title'		=> $title,
		'message'	=> $message,
		'type'		=> $type,
		'date'		=> current_time( 'timestamp' ),
	);
	
	$action_log		= get_option( 'kvp_action_log' );
	$action_log[]	= $log_item;
	
	update_option( 'kvp_action_log', $action_log );
	
}

/**
 * Checks if KVP is in test mode
 * 
 * @since 2.0.0
 * @return boolean Value of test mode
 */
function kvp_in_test_mode() {
	
	$options = get_option( 'kvp_settings' );
	
	if( isset( $options['test_mode'] ) && true == $options['test_mode'] )
		return true;
	
	return false;
	
}

/**
 * Removes KVP scheduling from wp cron
 * 
 * @since 2.0.0
 */
function kvp_purge_cron() {
	$crons = get_option('cron');
	
	foreach( $crons as $timestamp => $hooks ) {
		
		if( !is_array($hooks) )
			continue;
		
		foreach( $hooks as $hook => $keys ) {
			
			if( false === strpos($hook, 'kvp_') )
				continue;
				
			foreach( $keys as $k => $v )
				wp_unschedule_event($timestamp, $hook, $v['args']);
			
		}
		
	}
	
}