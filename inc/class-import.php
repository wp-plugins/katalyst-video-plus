<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* Imports videos from registered accounts.
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/inc
* @author     Keiser Media <support@keisermedia.com>
*/
class Katalyst_Video_Plus_Import {
	
	/**
	 * KVP Settings.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      array Settings.
	 */
	private $settings = array();
	
	/**
	 * The registered accounts.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      array Accounts.
	 */
	private $account = array();
	
	/**
	 * Active services.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      array Services.
	 */
	private $services = array();
	
	/**
	 * Queued videos.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      array Queued.
	 */
	private $queue = array();
	
	/**
	 * Initializes important variables
	 * 
	 * @since 2.0.0
	 */
	public function __construct() {
		
		$this->settings = get_option( 'kvp_settings', array() );
		$this->accounts = get_option( 'kvp_accounts', array() );
		$this->services = apply_filters( 'kvp_services', array() );
		
	}
	
	/**
	 * Initilizes audit for all posts
	 * 
	 * @since 2.0.0
	 */
	public function audit( $single = false ) {
		
		if( false === $single )
			kvp_action_log( __( 'Full Audit Begin', 'kvp' ), __( '', 'kvp' ), __( 'Core Import', 'kvp' ) );
		
		$posts_meta		= $this->get_posts_meta();
		$posts_audit	= ( false === $single ) ? $posts_meta : array( get_post_meta( $single, '_kvp', true ) );
		
		foreach( $posts_audit as $audit_meta ) {
			
			if( false === get_post_status($audit_meta['post_id']) )
				continue;
			
			foreach( $posts_meta as $key => $post_meta ) {
				
				if( $post_meta['post_id'] != $audit_meta['post_id'] && $post_meta['video_id'] == $audit_meta['video_id'] && $post_meta['service'] == $audit_meta['service'] ) {
					
					if( $audit_meta['post_id'] < $post_meta['post_id'] ) {
						
						$success = wp_delete_post( $post_meta['post_id'], true );
						
						if( false === $success ) 
							kvp_action_log( __( 'Failure to Delete Post', 'kvp' ), __( 'Post ID: ', 'kvp' ) . $post_meta['post_id'], __( 'Core Import', 'kvp' ) );
						
						else
							kvp_action_log( __( 'Deleted Duplicate Post', 'kvp' ), __( 'Post ID: ', 'kvp' ) . $post_meta['post_id'], __( 'Core Import', 'kvp' ) );
						
					} else {
						kvp_action_log( __( 'Match IDs Skip', 'kvp' ), $audit_meta['post_id'] . ': ' . $post_meta['post_id'], __( 'Core Import', 'kvp' ) );
						continue 2;
						
					}
					
					
				}
				
			}
			
			if( false !== get_post_status($audit_meta['post_id']) )
				$this->import( $audit_meta['post_id'] );
			
		}
		
		if( false === $single )
			kvp_action_log( __( 'Full Audit Completed', 'kvp' ), __( '', 'kvp' ), __( 'Core Import', 'kvp' ) );
		
	}
	
	/**
	 * Initializes import for all accounts
	 * 
	 * @since 2.0.0
	 */
	public function import( $audit = false ) {
		
		if( false === $audit ) {
			
			if( 'locked' === get_transient( 'kvp_import_lock' ) )
				return kvp_action_log( __( 'Import Cancelled', 'kvp' ), __( 'Import is already in progress.', 'kvp' ), __( 'Core Import', 'kvp' ) );
			
			kvp_action_log( __( 'Regular Import Begin', 'kvp' ), __( '', 'kvp' ), __( 'Core Import', 'kvp' ) );
			
			set_transient( 'kvp_import_lock', 'locked', ( 5 * 60 ) );
			
			$this->queue_videos();
		
		} else {
			
			$this->queue[0] = get_post_meta( $audit, '_kvp', true );
			
			if( isset( $this->queue[0]['last_audit'] ) && ( ( 60 * 60 ) > ( current_time( 'timestamp', true ) - $this->queue[0]['last_audit'] ) ) )
				return true;
			
		}
		
		foreach( $this->queue as $key => $item ) {
			
			if( !isset($this->accounts[$item['account']]['ext_status']) || !isset($this->accounts[$item['account']]['ext_status']['video']) || 'active' != $this->accounts[$item['account']]['ext_status']['video'] )
				continue;
				
			if( false === $audit )
				set_transient( 'kvp_import_lock', 'locked', ( 5 * 60 ) );
			
			$service	= 'KVP_' . str_replace( ' ', '_', $this->services[$item['service']]['label'] ) . '_Client';
			$service	= new $service( $this->accounts[$item['account']] );
			
			$video_info = $service->get_video( $item['video_id'] );
			
			if( is_wp_error( $video_info ) ) {
				
				kvp_action_log( sprintf( __( 'Video Info Request Error for Video ID: %s under Service: %s and username: %s', 'kvp'), '<i>' . $item['video_id'] . '</i>', '<i>' . $item['service'] . '</i>', '<i>' . $item['username'] . '</i>' ), $video_info, __( 'Core Import', 'kvp' ) );
				continue;
				
			}
			
			$post_id = $this->process_post( $video_info, $item, $audit );
			
			$post_meta = get_post_meta( $post_id, '_kvp', true );
			
			add_action( 'kvp_' . $item['service'] . '_import', array( $this, 'process_featured_image' ), 10, 3 );
			
			do_action( 'kvp_' . $item['service'] . '_import', $post_id, $video_info, $post_meta );
			
			unset( $this->queue[$key] );
			
			if( false === $audit && !kvp_in_test_mode() )
				update_option( 'kvp_queue', $this->queue );
			
		}
		
		if( false === $audit )
			delete_transient('kvp_import_lock');
		
		if( false !== $audit )
			return;
		
		kvp_action_log( __( 'Regular Import Completed', 'kvp' ), __( '', 'kvp' ), __( 'Core Import', 'kvp' ) );
	}
	
	/**
	 * Returns list of all meta data for KVP posts
	 * 
	 * @since 2.0.0
	 * @return array Contains all posts meta for KVP posts
	 */
	private function get_posts_meta() {
		global $wpdb;
		
		$posts_meta = $wpdb->get_col( $wpdb->prepare( "SELECT pm.meta_value FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = '%s'", '_kvp') );
		$new_meta	= array();
		
		foreach( $posts_meta as $post_meta ) {
			
			$new_meta[] = unserialize( $post_meta );
			
		}
		
		return $new_meta;
		
	}
	
	/**
	 * Adds videos to import queue
	 * 
	 * @since 2.0.0
	 */
	private function queue_videos() {
		
		$this->queue = get_option( 'kvp_queue', array() );
		
		if( empty($this->queue) ) {
			
			foreach ( $this->accounts as $id => $info ) {
				
				$service	= 'KVP_' . str_replace( ' ', '_', $this->services[$info['service']]['label']) . '_Client';
				
				if( !class_exists($service) ) {
					
					kvp_action_log( __( 'Class does not exist.', 'kvp' ), $service, __( 'Core Import', 'kvp' ) );
					continue;
					
				}
				
				$service	= new $service( $info );
				$video_ids	= $service->get_videos();
				
				if( is_wp_error( $video_ids ) ) {
					
					kvp_action_log( sprintf( __( 'Queue Video Request Error for Service: %s and username: %s', 'kvp' ), '<i>' . $info['service'] . '</i>', '<i>' . $info['username'] . '</i>' ), $video_ids, __( 'Core Import', 'kvp' ) );
					continue;
					
				}
				
				$posts_meta = $this->get_posts_meta();
				
				foreach( $posts_meta as $post_meta ) {
					
					$search_key = array_search( $post_meta['video_id'], $video_ids );
					
					if( false !== $search_key && $post_meta['service'] == $info['service'] && $post_meta['username'] == $info['username'] )
						unset( $video_ids[$search_key] );
					
				}
				
				foreach( $video_ids as $video_id ) {
					
					$this->queue[] = array(
						'account'	=> $id,
						'video_id'	=> $video_id,
						'service'	=> $info['service'],
						'username'	=> $info['username'],
					);
					
				}
				
				if( !kvp_in_test_mode() )
					update_option( 'kvp_queue', $this->queue );
				
			}
			
			return $this->queue;
		
		}
		
	}
	
	/**
	 * Creates post in WordPress
	 * 
	 * @since 2.0.0
	 * @return array Contains post meta for the post created
	 */
	private function process_post( $video_info, $item, $post_id ) {
		
		$account	= $this->accounts[$item['account']];
		$post_date	= apply_filters( 'kvp_' . $account['service'] . '_post_date', current_time( 'mysql' ), $video_info );
		
		$post = array(
			'post_title'	=> apply_filters( 'kvp_' . $account['service'] . '_post_title', '', $video_info ),
			'post_content'	=> apply_filters( 'kvp_' . $account['service'] . '_post_content', '', $video_info ),
			'post_status'	=> apply_filters( 'kvp_' . $account['service'] . '_post_status', 'publish', $video_info ),
			'post_date'		=> get_date_from_gmt( date('Y-m-d H:i:s', strtotime($post_date)), 'Y-m-d H:i:s'),
			'post_date_gmt'	=> date('Y-m-d H:i:s', strtotime($post_date) ),
			'post_author'	=> $account['author'],
			'post_category'	=> $account['categories'],
		);
		
		$post = apply_filters( 'kvp' . $account['service'] . '_post', $post );
		
		if( kvp_in_test_mode() )
			return kvp_action_log( sprintf( __( 'Post Creation Ready for Service: %s and username: %s', 'kvp' ), '<i>' . $item['service'] . '</i>', '<i>' . $item['username'] . '</i>' ), $post, __( 'Core Import', 'kvp' ) );
		
		if( is_int($post_id) )
			$post = array_merge( array( 'ID' => $post_id ), $post );
		
		$post_id = wp_insert_post( $post );
		
		update_post_meta( $post_id, '_kvp', array( 'post_id' => $post_id, 'video_id' => $item['video_id'], 'account' => $account['ID'],  'service' => $account['service'], 'username' => $account['username'], 'last_audit' => time() ) );
		
		return $post_id;
		
	}
	
	/**
	 * Creates a featured image for post
	 * 
	 * @since 2.0.0
	 */
	public function process_featured_image( $post_id, $video_info, $post_meta ) {
		
		$featured			= get_post_meta( $post_id, '_thumbnail_id', true );
		$video_thumb		= apply_filters( 'kvp_' . $post_meta['service'] . '_post_thumbnail', null, $video_info );
		
		if( empty($video_thumb) || !empty($featured) )
			return true;
		
		if( kvp_in_test_mode() )
			return kvp_action_log( __( 'Post Featured Image', 'kvp' ), $video_thumb, __( 'Core Import', 'kvp' ) );
		
		$upload_dir = wp_upload_dir();
		
		$post = get_post( $post_id );
		
		$attachment = array(
			'post_status'	=> 'inherit',
			'upload_date'	=> $post->post_date,
			'post_date'		=> $post->post_date,
			'post_date_gmt'	=> $post->post_date,
			'post_author'	=> $post->post_author,
		);
		
		$attach_id = $this->process_attachment( $attachment, $video_thumb );
		update_post_meta( $post_id, '_thumbnail_id', $attach_id, true );
		
	}
	
	/**
	 * Creates an attachement for the post
	 * 
	 * @since 2.0.0
	 */
	private function process_attachment( $post, $url ) {
		
		$upload = $this->fetch_remote_file( $url, $post );
		
		if( is_wp_error( $upload ) )
			return kvp_action_log( __( 'Attachment Upload Error', 'kvp' ), $upload, __( 'Core Import', 'kvp' ) );
		
		if( $info = wp_check_filetype( $upload['file'] ) )
			$post['post_mime_type'] = $info['type'];
		
		else
			return kvp_action_log( __( 'Attachment Processing Error', 'kvp' ), __( 'Invalid file type.', 'kvp' ), __( 'Core Import', 'kvp' ) );
		
		$post['guid'] = $upload['url'];
		
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		
		$post_id = wp_insert_attachment( $post, $upload['file'] );
		wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $upload['file'] ) );
		
		return $post_id;
		
	}
	
	/**
	 * Creates file in uploads folder
	 * 
	 * @since 2.0.0
	 * @return array File data
	 */
	private function fetch_remote_file( $url, $post ) {
		
		$file_name = basename( $url );
		$upload = wp_upload_bits( $file_name, 0, '', $post['upload_date'] );
		
		if( $upload['error'] )
			return kvp_action_log( __( 'Import File Error', 'kvp' ), $upload['error'], __( 'Core Import', 'kvp' ) );
		
		$headers = wp_get_http( $url, $upload['file'] );
		
		if( !$headers ) {
			@unlink( $upload['file'] );
			return kvp_action_log( __( 'Import File Error', 'kvp' ), __( 'Remote server did not respond.', 'kvp' ), __( 'Core Import', 'kvp' ) );
		}
		
		if( '200' != $headers['response'] ) {
			
			@unlink( $upload['file'] );
			return kvp_action_log( __( 'Import File Error', 'kvp' ), sprintf( __('Remote server returned error response %d %s.', 'kvp'), esc_html($headers['response']), get_status_header_desc($headers['response']) ), __( 'Core Import', 'kvp' ) );
			
		}
		
		$file_size = filesize( $upload['file'] );
		
		if ( isset( $headers['content-length'] ) && $file_size != $headers['content-length'] ) {
			
			@unlink( $upload['file'] );
			return kvp_action_log( __( 'Import File Error', 'kvp' ), __( 'Remote file is incorrect size.', 'kvp' ), __( 'Core Import', 'kvp' ) );
			
		}

		if ( 0 == $file_size ) {
			@unlink( $upload['file'] );
			return kvp_action_log( __( 'Import File Error', 'kvp' ), __( 'Zero size file downloaded.', 'kvp' ), __( 'Core Import', 'kvp' ) );
		}
		
		return $upload;
		
	}
	
}