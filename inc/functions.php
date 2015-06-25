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
* Adds messages to activity log
* 
* @since 3.0.0
*/
function kvp_activity_log( $action = '', $type, $args = array() ) {
	
	$args  = ( !is_array($args) ) ? array( 'message' => $args ) : $args;
	
	if( isset($args['message']) && ( is_array($args['message']) || is_object($args['message']) ) )
		$args['message'] = print_r( $args['message'], true );
	
	$log_item = array(
		'action'	=> $action,
		'type'		=> $type,
		'args'		=> $args,
		'date'		=> current_time( 'timestamp' ),
	);
	
	$activity_log	= get_option( 'kvp_activity_log' );
	$activity_log[]	= $log_item;
	
	update_option( 'kvp_activity_log', $activity_log );
	
	if( true === WP_DEBUG )
		return $log_item;
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

/**
 * Retrieves information from KVP posts
 * 
 * @since 3.0.0
 */
function kvp_get_posts( $query = '' ) {
	
	$defaults 	= array(
		'post_type'			=> 'kvp_video',
		'posts_per_page'	=> -1,
		'fields'			=> 'ids',
	);
	
	$static		= array(
		'meta_key'	=> '_kvp',
	);
	
	$query = ( !empty($query) ) ? wp_parse_args($query) : array();
	$query = array_merge( $defaults, $query, $static );
	
	$posts = new WP_Query($query);
	
	if( 'ids' == $query['fields'] )
		return $posts->get_posts();
	
	if( $posts->have_posts() ) :
	    
	    while( $posts->have_posts() ) : $posts->the_post();
	    
	        echo get_the_ID() . '<br />';
	    
	    endwhile;
	    
	endif;
	
	wp_reset_postdata();
	
}

/**
 * Retrieves all KVP post meta from all KVP posts
 * 
 * @since 3.0.0
 */
function kvp_get_posts_meta() {
	
	global $wpdb;
		
	$posts_meta = $wpdb->get_col( $wpdb->prepare( "SELECT pm.meta_value FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = '%s'", '_kvp') );
	$new_meta	= array();
	
	foreach( $posts_meta as $post_meta ) {
		
		$new_meta[] = unserialize( $post_meta );
		
	}
	
	return $new_meta;
	
}

/**
 * Generates a unique id not in array
 * 
 * @since 3.0.0
 * @param  arr $array Array by which to create a unique id
 * @return str        Unique id
 */
function kvp_unique_id( $array ) {
	
	$id = uniqid();
	
	if( array_key_exists( $id, $array ) )
		return kvp_unique_id( $array );
	
	return $id;
	
}

/**
 * Returns available source types
 * 
 * @since 3.0.0
 * @param  str $service Service slug
 * @return array          Source types
 */
function kvp_get_source_types( $service = null ) {
	
	$source_types = array(
		
		'channels'		=> __( 'Channels by ID', 'kvp' ),
		'playlists'		=> __( 'Playlists by ID', 'kvp' ),
		'search'	=> __( 'Search Terms', 'kvp' ),
		
	);
	
	return $source_types;
	
}