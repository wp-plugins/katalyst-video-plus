<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* Houses essential upgrades between versions.
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/admin
* @author     Keiser Media <support@keisermedia.com>
*/
class Katalyst_Video_Plus_Upgrade {
	
	/**
	 * The version of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string  The current version of this plugin.
	 */
	private $plugin_version;
	
	/**
	 * The version of this plugin running.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string  The current version of this plugin.
	 */
	private $installed_version;
	
	/**
	 * Controls which upgrade functions to activate
	 * 
	 * @since 2.0.0
	 */
	public function __construct( $plugin_version ) {
		
		$this->plugin_version = $plugin_version;
		$this->installed_version = get_option( 'kvp_version', '0.0.0' );
		
		if( version_compare( $this->installed_version, $this->plugin_version, '<') )
			$this->_1_0_0_to_2_0_0();
		
	}
	
	/**
	 * Upgrades from pre-2.0.0 to 2.0.0
	 * 
	 * @since 2.0.0
	 */
	private function _1_0_0_to_2_0_0() {
		global $wpdb;
		
		kvp_purge_cron();
		
		//Deletes previous log identifier
		delete_option( 'kvp_log' );
		
		//Rearranges and saves kvp_source as kvp_accounts
		$sources = get_option( 'kvp_sources', array() );
		
		if( !empty( $sources ) ) {
			
			$accounts = get_option( 'kvp_accounts', array() );
			$queued   = get_option( 'kvp_queue', array() );
			
			foreach( $sources as $id => $source ) {
				
				if( isset($source['import']) ) {
					
					foreach( $source['import']['queued'] as $video_id => $import_info ) {
						
						if( !empty( $import_info['post_id'] ) )
							continue;
						
						$queued[] = array(
							'account'	=> $id,
							'video_id'	=> $video_id,
							'service'	=> $source['provider'],
							'username'	=> $source['username'],
						);
						
						update_option( 'kvp_queue', $queued );
						
					}
					
				}
				
				unset( $source['import'] );
				
				if( !isset( $accounts[$id] ) )
					$accounts[$id] = $source;
				
				if( isset($accounts[$id]['provider']) ) {
					$accounts[$id]['service'] = $accounts[$id]['provider'];
					unset( $accounts[$id]['provider'] );
				}
				
				if( isset($accounts[$id]['api_key']) ) {
					// Changes api_key label to developer_key
					$accounts[$id]['developer_key'] = $accounts[$id]['api_key'];
					unset( $accounts[$id]['api_key'] );
				}
				
				update_option( 'kvp_accounts', $accounts );
				
			}
			
		}
		
		// Deletes previous account identifier
		delete_option( 'kvp_sources' );
		
		// Updates all posts to fit new format
		$accounts	= get_option( 'kvp_accounts', array() );
		$posts_meta = $wpdb->get_col( $wpdb->prepare( "SELECT pm.meta_value FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = '%s'", '_kvp') );
		
		foreach( $posts_meta as $post_meta ) {
			
			$post_meta = unserialize( $post_meta );
			
			if( isset($post_meta['provider']) ) {
				$post_meta['service'] = $post_meta['provider'];
				unset( $post_meta['provider'] );
			}
			
			if( isset($post_meta['ID']) ) {
				$post_meta['video_id'] = $post_meta['ID'];
				unset( $post_meta['ID'] );
			}
			
			foreach( $accounts as $account ) {
				
				if( $account['service'] == $post_meta['service'] && $account['username'] == $post_meta['username'] )
					$post_meta['account'] = $account['ID'];
				
			}
			
			update_post_meta( $post_meta['post_id'], '_kvp', $post_meta );
			
		}
		
		// Adds kvp_version to options
		update_option( 'kvp_version', '2.0.0' );
		
	}
	
}