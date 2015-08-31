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
		
		// Set Transients for Admin Notices
		if( $this->plugin_version !== $this->installed_version && '0.0.0' != $this->installed_version )
			set_transient( 'kvp_upgrade_notices', $this->installed_version, ( 7 * 24 * 60 * 60 ) );
		
		// Upgrade to Current Version
		if( version_compare( $this->installed_version, '2.0.0', '<' ) )
			$this->_1_0_0_to_2_0_0();
		
		if( version_compare( $this->installed_version, '2.0.3', '<' ) )
			$this->_2_0_0_to_2_0_3();
		
		if( version_compare( $this->installed_version, '3.0.0', '<' ) )
			$this->_2_0_3_to_3_0_0();
		
		if( version_compare( $this->installed_version, '3.1.0', '<' ) )
			$this->_3_0_0_to_3_1_0();
		
		if( !version_compare( $this->installed_version, $this->plugin_version, '==' ) )
			update_option( 'kvp_version', $this->plugin_version );
		
	}
	
	/**
	 * Checks Upgrade Transient and Adds Message to Queue
	 * 
	 * @since 3.0.0
	 */
	public function upgrade_notices() {
		
		$upgrade_notices = array(
			'3.0.0' => array(
				sprintf( __( 'Create new video %s and set the %s.', 'kvp' ), '<a href="' . admin_url('edit-tags.php?taxonomy=kvp_video_category&post_type=kvp_video') . '">' . __( 'categories', 'kvp' ) . '</a>', '<a href="' . admin_url('edit.php?post_type=kvp_video&page=kvp-sources') . '">' . __( 'categories', 'kvp' ) . '</a>' ),
				sprintf( __( 'Sources from previous versions of KVP must be %s.', 'kvp' ), '<a href="' . admin_url('edit.php?post_type=kvp_video&page=kvp-sources') . '">' . __( 'activated', 'kvp' ) . '</a>' ),
			)	
		);
		
		if ( isset($_GET['kvp_ignore']) && '1' == $_GET['kvp_ignore'] )
			delete_transient('kvp_upgrade_notices');
		
		$upgrade_version = get_transient('kvp_upgrade_notices');
		
		if( false === $upgrade_version )
			return;
		
		foreach( $upgrade_notices as $version => $notices ) {
			
			if( version_compare( $upgrade_version, $version, '<' ) ) {
				
				foreach( $notices as $notice )
					echo '<div class="updated"><p>' . $notice . ' | <a href="' . esc_url( add_query_arg( 'kvp_ignore', true ) ) . '">' . __( 'Hide All KVP Notices', 'kvp' ) . '</a></p></div>';
				
			}
			
		}
		
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
		
	}
	
	/**
	 * Upgrades from 2.0.0 to 2.0.3
	 * 
	 * @since 2.0.0
	 */
	private function _2_0_0_to_2_0_3() {
		
		$accounts = get_option( 'kvp_accounts', array() );
		
		foreach( $accounts as $id => $account ) {
			
			if( !isset($account['ext_status']) )
				$accounts[$id]['ext_status'] = array( 'video' => 'active' );
			
		}
		
		update_option( 'kvp_accounts', $accounts );
		
	}
	
	/**
	 * Upgrades from 2.0.3 to 3.0.0
	 * 
	 * @since 3.0.0
	 */
	private function _2_0_3_to_3_0_0() {
		
		delete_option( 'kvp_action_log' );
		delete_option( 'kvp_queue' );
		
		// Restructures settings
		$settings	= get_option( 'kvp_settings', array() );
		$changes	= array(
			'youtube_api_fallback' => 'youtube_api_key',	
		);
		
		foreach( $changes as $old => $new ) {
			
			if( isset($settings[$old]) ) {
				
				$value = $settings[$old];
				$settings[$new] = $value;
				unset($settings[$old]);
				
			}
			
		}
		
		update_option( 'kvp_settings', $settings );
		
		// Restructures accounts into sources
		$accounts	= get_option( 'kvp_accounts', array() );
		$sources	= array();
		
		foreach( $accounts as $id => $account ) {
			
			$source = array(
				
				'id'				=> $id,
				'name'				=> $account['username'],
				'service'			=> $account['service'],
				'type'				=> 'channels',
				'items'				=> array( $account['username'] ),
				'creator'			=> get_current_user_id(),
				'author'			=> $account['author'],
				'tax_input'			=> array( 'kvp_video_category' => array() ),
				'comments'			=> 'open',
				'publish'			=> 'publish',
				'schedule_time'		=> time(),
				'schedule_freq'		=> 'hourly',
				'limit'				=> null,
				'status'			=> 'inactive',
					
			);
			
			$sources[$id] = apply_filters( 'kvp_save_source', $source );
		}
		
		update_option( 'kvp_sources', $sources );
		delete_option( 'kvp_accounts' );
		
		kvp_purge_cron();
		
		// Coverts kvp posts into kvp_video
		$post_args	= array( 'post_type' => 'post', 'numberposts' => -1 );
		$posts		= get_posts( $post_args );
		
		foreach( $posts as $post ) {
			
			$video_settings = get_post_meta( $post->ID, '_kvp', true );
			
			if( empty($video_settings) )
				continue;
			
			set_post_type( $post->ID, 'kvp_video' );
			
			if( isset($video_settings['account']) )
				unset($video_settings['account']);
			
			if( isset($video_settings['username']) )
				unset($video_settings['username']);
			
			update_post_meta( $post->ID, '_kvp', $video_settings );
			
		}
		
		wp_reset_postdata();
		
	}
	
	/**
	 * Upgrades from 3.0.0 to 3.1.0
	 * 
	 * @since 3.1.0
	 */
	private function _3_0_0_to_3_1_0() {

		$sources = get_option('kvp_sources');

		foreach( $sources as $source => $id ) {

			if( 'videos' == $source['type'] )
				unset($sources[$id]);

		}

		update_option( 'kvp_sources', $sources );

	}
	
}