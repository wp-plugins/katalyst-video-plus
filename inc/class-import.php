<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* Imports videos from registered sources.
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
	 * The registered sources.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      array sources.
	 */
	private $sources = array();
	
	/**
	 * Active services.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      array Services.
	 */
	private $services = array();
	
	/**
	 * Initializes important variables
	 * 
	 * @since 2.0.0
	 */
	public function __construct() {
		
		$this->settings = get_option( 'kvp_settings', array() );
		$this->services = apply_filters( 'kvp_services', array() );
		$this->sources	= get_option( 'kvp_sources', array() );
		
	}
	
	/**
	 * Initilizes audit for all posts
	 * 
	 * @since 2.0.0
	 */
	public function audit( $single = false ) {
		
		$results = array(
			'total'			=> 0,
			'duplicates'	=> 0,
			'deleted'		=> 0,
			'start_time'	=> microtime(true),
			'end_time'		=> 0,
		);
		
		$posts_audit	= ( false === $single ) ? kvp_get_posts() : array( $single );
		$posts_meta		= kvp_get_posts_meta();
		
		foreach( $posts_audit as $post_id ) {
			
			if( false === get_post_status($post_id) )
				continue;
				
			$single_post_meta = get_post_meta( $post_id, '_kvp', true );
			
			if( false === $single ) {
				
				foreach( $posts_meta as $key => $post_meta ) {
					
					if( $post_meta['post_id'] != $post_id && $post_meta['video_id'] == $single_post_meta['video_id'] && $post_meta['service'] == $single_post_meta['service'] ) {
						
						if( $post_id < $post_meta['post_id'] ) {
							
							$results['duplicates']++;
							
							$success = wp_delete_post( $post_meta['post_id'], true );
							
							if( false === $success ) 
								kvp_activity_log( __( 'Core Audit', 'kvp' ), 'error', array( 'message' => sprintf( __( 'Failure to Delete Duplicate Post: <em>%s</em>', 'kvp' ), $post_meta['post_id'] ) ) );
							
							else {
								$results['deleted']++;
								kvp_activity_log( __( 'Core Audit', 'kvp' ), 'notice', array( 'message' => sprintf( __( 'Deleted Duplicate Post: <em>%s</em>', 'kvp' ), $post_meta['post_id'] ) ) );
							}
							
						} else {
							kvp_activity_log( __( 'Core Audit', 'kvp' ), 'notice', array( 'message' => sprintf( __( 'Skipped Matching IDs: <em>%s</em>', 'kvp' ), $post_id . ' - ' . $post_meta['post_id'] ) ) );
							continue 2;
							
						}
						
						
					}
					
				}
				
			}
			
			if( false !== get_post_status($post_id) ) {
				
				$service	= 'KVP_' . str_replace( ' ', '_', $this->services[$single_post_meta['service']]['label'] ) . '_Client';
				$service	= new $service( $single_post_meta );
				
				$video_id	= $single_post_meta['video_id'];
				$video_info = $service->get_video( $single_post_meta['video_id'] );
				
				if( empty($video_info) ) {
					kvp_activity_log( __( 'Core Audit', 'kvp' ), 'error', sprintf( __( 'Video Request Error for video: %s in service: %s', 'kvp' ), '<i>' . $single_post_meta['video_id'] . '</i>', '<i>' . $single_post_meta['service'] . '</i>' ) );
					continue;
				}
				
				$this->process_post( array_merge( $video_info, $single_post_meta ), $post_id );
				$results['total']++;
				
			}
			
		}
		
		$results['end_time'] = microtime(true);
		
		return $results;
		
	}
	
	/**
	 * Initializes import for all sources
	 * 
	 * @since 2.0.0
	 */
	public function import( $source_id ) {
		
		if( empty($this->sources[$source_id]) || !isset($this->sources[$source_id]) )
			return kvp_activity_log( __( 'Core Import', 'kvp' ), 'error', array( 'message' => sprintf( __( 'Source <em>%s</em> does not exist.', 'kvp' ), $source_id ) ) );
		
		if( 'inactive' == $this->sources[$source_id]['status'] )
			return wp_unschedule_event( wp_next_scheduled( 'kvp_import_' . $source_id, array( $source_id ) ), 'kvp_import_' . $source_id, array( $source_id ) );
		
		$service = 'KVP_' . str_replace( ' ', '_', $this->services[$this->sources[$source_id]['service']]['label']) . '_Client';
		
		if( !class_exists($service) )
			return kvp_activity_log( __( 'Core Import', 'kvp' ), 'error', array( 'message' => __( 'Class does not exist.', 'kvp' ) ) );
		
		$import_lock = get_transient( 'kvp_import_lock' );
		
		if( false !== $import_lock ) {
			
			if( $import_lock == $source_id )
				return false;
			
			wp_unschedule_event( wp_next_scheduled( 'kvp_import_' . $source_id, array( $source_id ) ), 'kvp_import_' . $source_id, array( $source_id ) );
			wp_schedule_single_event( time() + ( 6 * 60 ), 'kvp_import_' . $source_id, array( $source_id ) );
			
			return kvp_activity_log( __( 'Core Import', 'kvp' ), 'notice', array( 'message' => sprintf( __( 'Import for another source already in progress. This source rescheduled: <em>%s</em>', 'kvp' ), $source_id ) ) );
		}
		
		set_transient( 'kvp_import_lock', $source_id, ( 5 * 60 ) );
		
		$start_time = microtime(true);
		
		$service = new $service( $this->sources[$source_id] );
		$results = $service->get_videos();
		$queue 	 = isset($results['items']) ? $results['items'] : array();
		
		if( is_wp_error( $results ) ) {
			
			kvp_activity_log( sprintf( __( 'Queue Video Request Error for name %s Service: %s', 'kvp' ), '<em>' . $source['name'] . '</em>', '<em>' . $source['service'] . '</em>' ), $video_ids, __( 'Core Import', 'kvp' ) );
			continue;
			
		}
		
		if( !isset($results['items']) ) {
			delete_transient('kvp_import_lock');
			return kvp_activity_log( __( 'Core Import', 'kvp' ), 'error', array( 'message' => sprintf( __( 'The service %s did not return items.', 'kvp' ), '<em>' . $source['service'] . '</em>' ) ) );
		}
		
		foreach( $queue as $key => $video_info ) {
				
			set_transient( 'kvp_import_lock', $source_id, ( 5 * 60 ) );
			
			$service	= 'KVP_' . str_replace( ' ', '_', $this->services[$this->sources[$source_id]['service']]['label'] ) . '_Client';
			$service	= new $service( $this->sources[$source_id] );
			
			$video_id	= $video_info['id'];
			$video_info = $service->get_video( $video_id );
			
			if( empty($video_info) )
				kvp_activity_log( __( 'Core Import', 'kvp' ), 'error', sprintf( __( 'Video Request Error for video: %s in service: %s', 'kvp' ), '<i>' . $video_id . '</i>', '<i>' . $this->sources[$source_id]['service'] . '</i>' ) );
			
			$post_id = $this->process_post( array_merge( $this->sources[$source_id], array( 'video_id' => $video_id ), $video_info ) );
			
			unset( $queue[$key] );
			
		}
		
		delete_transient('kvp_import_lock');
		
		$end_time = microtime(true);
		
		return array_merge( $results['page_info'], array( 'source_id' => $source_id, 'start_time' => $start_time, 'end_time' => $end_time ) );
		
	}
	
	public function test_source( $source ) {
		
		$results = array(
			'page_info' => array(
				'scanned' => 0,
				'duplicates' => 0,
				'total'	=> 0,
				'execution_time' => 0,
			),
			'items' => array(),
		);
		
		if( 0 !== get_current_user_id() )
			$results['page_info']['exec_author'] = get_current_user_id();
		
		if( !isset($this->services[$source['service']]) )
			return $results;
		
		$service	= 'KVP_' . str_replace( ' ', '_', $this->services[$source['service']]['label']) . '_Client';
		
		if( !class_exists($service) ) {
			
			kvp_activity_log( __( 'Class does not exist.', 'kvp' ), $service, __( 'Core Import', 'kvp' ) );
			continue;
			
		}
		
		$start_time = microtime(true);
		
		$service = new $service( $source );
		$videos = $service->get_videos();
		
		if( is_wp_error( $videos ) )
			return kvp_activity_log( sprintf( __( 'Test Video Request Error for Service: %s and username: %s', 'kvp' ), '<i>' . $source['service'] . '</i>', '<i>' . $source['name'] . '</i>' ), $video_ids, __( 'Core Import', 'kvp' ) );
		
		$results = array_merge( $results, $videos );
		$results['page_info']['execution_time'] = round( microtime(true) - $start_time, 4 );
		
		return $results;
		
	}
	
	/**
	 * Creates post in WordPress
	 * 
	 * @since 2.0.0
	 * @return array Contains post meta for the post created
	 */
	private function process_post( $video_info, $post_id = false ) {
		
		if( !isset($this->services[$video_info['service']]) )
			return kvp_activity_log( __( 'Invalid Service', 'kvp' ), __( 'Could not find "' . $video_info['service'] . '"" service.', 'kvp' ), __( 'Core Import', 'kvp' ) );
		
		$service	= 'KVP_' . str_replace( ' ', '_', $this->services[$video_info['service']]['label'] ) . '_Client';
		$service	= new $service( $video_info['service'] );
		
		if( is_wp_error( $video_info ) )
			return kvp_activity_log( sprintf( __( 'Video Info Request Error for Video ID: %s under Service: %s and username: %s', 'kvp'), '<i>' . $video_info['video_id'] . '</i>', '<i>' . $video_info['service'] . '</i>', '<i>' . $video_info['username'] . '</i>' ), $video_info, __( 'Core Import', 'kvp' ) );
		
		$post_date	= apply_filters( 'kvp_' . $video_info['service'] . '_post_date', current_time( 'mysql' ), $video_info );
		
		$post_status = ( isset($video_info['publish']) ) ? $video_info['publish'] : 'publish';
		
		$post = array(
			'post_title'	=> apply_filters( 'kvp_' . $video_info['service'] . '_post_title', '', $video_info ),
			'post_content'	=> apply_filters( 'kvp_' . $video_info['service'] . '_post_content', '', $video_info ),
			'post_status'	=> apply_filters( 'kvp_' . $video_info['service'] . '_post_status', $post_status, $video_info ),
			'post_type'		=> 'kvp_video',
			'post_date'		=> get_date_from_gmt( date('Y-m-d H:i:s', strtotime($post_date)), 'Y-m-d H:i:s'),
			'post_date_gmt'	=> date('Y-m-d H:i:s', strtotime($post_date) ),
		);
		
		if( isset($video_info['author']) )
			$post['post_author'] = $video_info['author'];
		
		if( isset($video_info['tax_input']['kvp_video_category']) )
			$post['tax_input']['kvp_video_category'] = $video_info['tax_input']['kvp_video_category'];
		
		$post = apply_filters( 'kvp' . $video_info['service'] . '_post', $post );
		
		if( kvp_in_test_mode() )
			return kvp_activity_log( __( 'Core Import', 'kvp' ), 'notice', array( 'message' => sprintf( __( 'Post Creation Ready for Service: %s', 'kvp' ), '<i>' . $video_info['service'] . '</i>' ) ) );
		
		if( is_int($post_id) )
			$post = array_merge( array( 'ID' => $post_id ), $post );

		$post_id = ( !$post_id ) ? wp_insert_post( $post ) : wp_update_post( $post );
		
		$post_meta = array( 'post_id' => $post_id, 'video_id' => $video_info['video_id'], 'service' => $video_info['service'], 'last_audit' => time() );
		
		if( isset($video_info['tax_input']['kvp_video_category']) )
			$post_meta = array_merge( $post_meta, array( 'tax_input' => array( 'kvp_video_category' => $video_info['tax_input']['kvp_video_category'] ) ) );
		
		update_post_meta( $post_id, '_kvp', $post_meta );
		
		if( defined('DOING_CRON') && ! empty( $post['tax_input'] ) ) {
			
			foreach ( $post['tax_input'] as $taxonomy => $tags ) {
				
				$taxonomy_obj = get_taxonomy($taxonomy);
				
				if ( is_array( $tags ) )
					$tags = array_filter($tags);
				
				$tags = array_map( 'intval', $tags );
				
				wp_set_object_terms( $post_id, $tags, $taxonomy );
				
			}
			
		}
		
		add_action( 'kvp_' . $video_info['service'] . '_import', array( $this, 'process_featured_image' ), 10, 3 );
		
		do_action( 'kvp_' . $video_info['service'] . '_import', $post_id, $video_info, $post_meta );
		
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
			return kvp_activity_log( __( 'Post Featured Image', 'kvp' ), $video_thumb, __( 'Core Import', 'kvp' ) );
		
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
			return kvp_activity_log( __( 'Attachment Upload Error', 'kvp' ), $upload, __( 'Core Import', 'kvp' ) );
		
		if( $info = wp_check_filetype( $upload['file'] ) )
			$post['post_mime_type'] = $info['type'];
		
		else
			return kvp_activity_log( __( 'Attachment Processing Error', 'kvp' ), __( 'Invalid file type.', 'kvp' ), __( 'Core Import', 'kvp' ) );
		
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
		
		$file_name = md5( uniqid( rand(), true ) ) . '_' . basename( $url );
		$upload = wp_upload_bits( $file_name, 0, '', $post['upload_date'] );
		
		if( $upload['error'] )
			return kvp_activity_log( __( 'Import File Error', 'kvp' ), __( 'Core Import', 'kvp' ), $upload['error'] );
		
		$headers = wp_get_http( $url, $upload['file'] );
		
		if( !$headers ) {
			@unlink( $upload['file'] );
			return kvp_activity_log( __( 'Import File Error', 'kvp' ), __( 'Core Import', 'kvp' ), __( 'Remote server did not respond.', 'kvp' ) );
		}
		
		if( '200' != $headers['response'] ) {
			
			@unlink( $upload['file'] );
			return kvp_activity_log( __( 'Import File Error', 'kvp' ), __( 'Core Import', 'kvp' ), sprintf( __('Remote server returned error response %d %s.', 'kvp'), esc_html($headers['response']), get_status_header_desc($headers['response']) ) );
			
		}
		
		$file_size = filesize( $upload['file'] );
		
		if ( isset( $headers['content-length'] ) && $file_size != $headers['content-length'] ) {
			
			@unlink( $upload['file'] );
			return kvp_activity_log( __( 'Import File Error', 'kvp' ), __( 'Core Import', 'kvp' ), __( 'Remote file is incorrect size.', 'kvp' ) );
			
		}

		if ( 0 == $file_size ) {
			@unlink( $upload['file'] );
			return kvp_activity_log( __( 'Import File Error', 'kvp' ), __( 'Core Import', 'kvp' ), __( 'Zero size file downloaded.', 'kvp' ) );
		}
		
		return $upload;
		
	}
	
}