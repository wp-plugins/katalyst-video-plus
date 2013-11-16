<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }

abstract class KVP_Importer {
	
	private $action		= false;
	private $test_mode	= false;
	private $provider	= array();
	private $requests	= array();
	private $sources	= array();
	private $active;
	private $settings	= array();
	private $total		= 0;
	
	protected $source	= array();
	
	public function __construct() {
		
		$this->settings = get_option('kvp_settings');
		
		if( isset($this->settings['test_mode']) && true == $this->settings['test_mode'] )
			$this->test_mode = true;
				
	}
	
	protected function status($message, $error = false) {
		
		if( 'cron' === $this->action || 'ajax' === $this->action ) {
				trigger_error($message);
			
		}
		
		switch( $error ) {
			
			case true:
				$message = '<span class="error-message">' . $message . '</span>';
				break;
			
			case false:
				$message = '<span>' . $message . '</span>';
				break;
			
		}
		
		echo $message . '<br />';
		return true;
		
	}
	
	protected function update_source() {
		
		$this->sources = array_merge(get_option('kvp_sources'), array( $this->source['ID'] => $this->source ) );
		
		update_option('kvp_sources', $this->sources );
		
	}
	
	protected function request( $endpoint, $args = array() ) {
		
		if( !isset($this->requests['endpoints'][$endpoint]) )
			return $this->status( sprintf( __('"%s" endpoint not valid.', 'kvp'), $endpoint), true );
		
		$args = is_array($args) ? array_filter($args) : $args;
		$args = array_merge($args, array('timeout' => 5, 'sslverify' => false) );
		$args = is_array($args) ? http_build_query($args) : $args;
		
		$response = wp_remote_get( $this->requests['base_url'] . $endpoint . '?' . $args );
		
		if ( is_wp_error($response) )
			return $response;
		
		try {
			$json = json_decode( $response['body'] );
		} catch ( Exception $ex) {
			$json = null;
		}
		
		return $json;
		
	}
	
	public function import( $source, $action = false ) {
		
		ob_implicit_flush();
		
		$this->source = $source;
		$this->action = $action;
		
		if( isset($this->source['import']['is_importing']) && true == $this->source['import']['is_importing'] && isset($this->source['import']['action']) && $this->action !== $this->source['import']['action'] && false !== $this->source['import']['action'] ) {
			$this->status(__('Importing is already in process by another means.', 'kvp'), true);
			return;
		}
		
		$this->import_start();
		
		foreach( $this->source['import']['queued'] as $video_id => $values ) {
			
			$this->active = $this->get_video_info($video_id);
			
			if( is_wp_error($this->active) ) {
				$this->source['import']['queued'][$video_id]['ID']  = $this->active->get_error_message();
				$this->source['import']['errors'][$video_id] = $this->source['import']['queued'][$video_id];
				$this->status($this->source['import']['queued'][$video_id]['ID'], true);
				unset($this->source['import']['queued'][$video_id]);
				$this->update_source();
				continue;
			}
			
			$this->active = array_merge($this->active, array('ID' => $video_id) );
			
			$this->status( sprintf( __('Importing %d of %d videos: %s', 'kvp'), ( $this->total - count($this->source['import']['queued']) + 1 ), $this->total, '<i>' . $video_id . '</i>' ) );
			
			$this->source['import']['queued'][$video_id]['tags']	 = $this->process_tags();
			$this->update_source();
			
			$this->source['import']['queued'][$video_id]['post_id']	 = $this->process_post();
			
			if( !is_int($this->source['import']['queued'][$video_id]['post_id']) ) {
				$this->source['import']['errors'][$video_id] = $this->source['import']['queued'][$video_id];
				unset($this->source['import']['queued'][$video_id]);
				$this->update_source();
				continue;
			}
			
			$this->update_source();
			
			$this->source['import']['queued'][$video_id]['image']	 = $this->process_featured_image();
			$this->update_source();
			
			$this->source['import']['queued'][$video_id]['comments'] = $this->process_comments();
			$this->update_source();
			
			if( false == $this->source['import']['queued'][$video_id]['image'] || false == $this->source['import']['queued'][$video_id]['tags'] || false == $this->source['import']['queued'][$video_id]['comments'] )
				$this->source['import']['errors'][$video_id] = $this->source['import']['queued'][$video_id];
			
			unset($this->source['import']['queued'][$video_id]);
			$this->update_source();
			
		}
		
		$this->import_end();
		
	}
	
	private function import_start() {
		global $wpdb;
		
		if( !isset($this->source['import']) )
			$this->source['import'] = array(
				'is_importing'	=> false,
				'action'		=> $this->action,
				'queued'		=> array(),
				'active'		=> array(),
				'errors'		=> array(),
			);
		
		$this->requests	= apply_filters('kvp_' . $this->source['provider'] . '_request_data', $this->requests);
		
		$this->status( __('Beginning import', 'kvp') );
		
		$this->status( __('Retrieving video list...', 'kvp') );
		
		if( 'repair' === $this->source['import']['action'] && false === $this->source['import']['is_importing'] )
			$this->repair();
		
		if( in_array($this->source['import']['action'], array(false, 'audit', 'cron', 'ajax') ) && empty($this->source['import']['queued']) ) {
			$this->source['import']['queued'] = $this->get_video_ids();
			
			if( is_wp_error($this->source['import']['queued']) ) {
				$this->status($this->source['import']['queued']->get_error_message(), true);
				$this->source['import']['queued'] = array();
				return $this->update_source();
			}
			
			$imported_ids = $wpdb->get_col( $wpdb->prepare( "SELECT pm.meta_value FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = '%s'", '_kvp') );
			$import_ids	  = array();
			
			$this->source['import']['queued'] = array_fill_keys($this->source['import']['queued'], array(
					'ID'	   => null,
					'post_id'  => null,
					'image'	   => null,
					'tags'	   => null,
					'comments' => null,
				)
			);
			
			foreach( $imported_ids as $import_id ) {
				
				$import_id = unserialize($import_id);
				
				if(  $import_id['provider'] === $this->source['provider'] ){
					
					if( true == array_key_exists($import_id['ID'], $this->source['import']['queued']) )
						unset($this->source['import']['queued'][$import_id['ID']]);
					
				}
				
				
				
			}
			
			$this->update_source();
			
		}
		
		if( empty($this->source['import']['queued']) )
			return $this->status(__('No videos returned from provider.', 'kvp'));
		echo $this->source['import']['action'];
		if( in_array($this->source['import']['action'], array(false, 'audit', 'cron', 'ajax') ) && false == $this->source['import']['is_importing'] )
			$this->source['import']['last_import'] = current_time('timestamp');
		
		$this->source['import']['is_importing'] = true;
		
		$this->update_source();
		
		$this->total = count($this->source['import']['queued']);
		
		return $this->status( sprintf( __('Preparing to import %s videos.', 'kvp'), $this->total) );
		
	}
	
	private function import_end() {
		
		$this->source['import']['is_importing'] = false;
		$this->update_source();
		
		$this->status( __('Import Complete', 'kvp') );
		
	}
	
	private function repair() {
		
		$this->source['import']['queued'] = $this->source['import']['errors'];
		$this->source['import']['errors'] = array();
	}
		
	private function process_post() {
		
		if( isset($this->source['import']['queued'][$this->active['ID']]['post_id']) && is_int($this->source['import']['queued'][$this->active['ID']]['post_id']) )
			return $this->source['import']['queued'][$this->active['ID']]['post_id'];
		
		$this->status( __('Creating post.', 'kvp') );
		
		$defaults	= array(
			'post_title'		=> '',
			'post_content'		=> '',
			'post_date'			=> '',
			'tags_input'		=> '',
		);
		
		$post_info	= array_intersect_key($this->active, $defaults);
		$post_info = array_merge($defaults, $post_info);
		
		$post = array(
			'post_title'		=> apply_filters('kvp_' . $this->source['provider'] . '_title', $post_info['post_title']),
			'post_content'		=> apply_filters('kvp_' . $this->source['provider'] . '_content', $post_info['post_content']),
			'post_status'		=> 'publish',
			'post_date'			=> get_date_from_gmt( date('Y-m-d H:i:s', strtotime($post_info['post_date'])), 'Y-m-d H:i:s'),
			'post_date_gmt'		=> date('Y-m-d H:i:s', strtotime($post_info['post_date']) ),
			'post_author'		=> $this->source['author'],
			'post_category'		=> $this->source['categories'],
			'tags_input'		=> $post_info['tags_input'],
		);
		
		if( isset($this->source['import']['queued'][$this->active['ID']]['post_id']) )
			$post['ID'] = isset($this->source['import']['queued'][$this->active['ID']]['post_id']);
		
		if( true == $this->test_mode )
			return 1337;
			
		$post_id = wp_insert_post($post);
		
		if( isset($this->settings['post_format']) && 'standard' !== $this->settings['post_format'] )
			set_post_format($post_id, 'video');
		
		add_post_meta($post_id, '_kvp', array( 'ID' => $this->active['ID'], 'provider' => $this->source['provider'], 'post_id' => $post_id) );
		
		$this->update_source();
		
		return $post_id;
	}
	
	private function process_featured_image() {
		
		if( true == $this->test_mode || true == $this->source['import']['queued'][$this->active['ID']]['image'] )
			return true;
		
		if( false == $this->source['import']['queued'][$this->active['ID']]['post_id'] )
			return false;
			
		if( !isset($this->active['image']) || empty($this->active['image']) )
			return true;
		
		$featured = get_post_meta($this->source['import']['queued'][$this->active['ID']]['post_id'], '_thumbnail_id', true);
		
		if( $featured === $this->source['import']['queued'][$this->active['ID']]['image'] )
			return $this->source['import']['queued'][$this->active['ID']]['image'];
		
		$this->status( __('Attaching featured image.', 'kvp') );
		
		$wp_upload_dir = wp_upload_dir();
		
		$post = get_post($this->source['import']['queued'][$this->active['ID']]['post_id']);
		
		$attachment = array(
			'post_status'	=> 'inherit',
			'upload_date'	=> $post->post_date,
			'post_date'		=> $post->post_date,
			'post_date_gmt'	=> $post->post_date,
		);
		
		$attach_id = is_int($this->source['import']['queued'][$this->active['ID']]['image']) ? $this->source['import']['queued'][$this->active['ID']]['image'] : $this->process_attachment($attachment, $this->active['image']);
		
		update_post_meta($this->source['import']['queued'][$this->active['ID']]['post_id'], '_thumbnail_id', $attach_id, true);
		
		return $attach_id;
		
	}
	
	private function process_tags() {
		
		if( !isset($this->active['tags']) || empty($this->active['tags']) )
			return true;
		
		$this->status( __('Processing tags.', 'kvp') );
		
	}
	
	private function process_comments() {
		$this->status( __('Processing comments.', 'kvp') );
		return true;
	}
	
	private function process_attachment( $post, $url ) {
		
		if ( preg_match( '|^/[\w\W]+$|', $url ) )
			$url = rtrim( $this->base_url, '/' ) . $url;

		$upload = $this->fetch_remote_file( $url, $post );
		if ( is_wp_error( $upload ) )
			return $upload;
		
		if ( $info = wp_check_filetype( $upload['file'] ) )
			$post['post_mime_type'] = $info['type'];
			
		else
			return new WP_Error( 'attachment_processing_error', __('Invalid file type', 'kvp') );
		
		$post['guid'] = $upload['url'];
		
		require_once ( ABSPATH . 'wp-admin/includes/image.php' );
		
		$post_id = wp_insert_attachment( $post, $upload['file'] );
		wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $upload['file'] ) );

		return $post_id;
	}
	
	private function fetch_remote_file( $url, $post ) {
		
		$file_name = basename( $url );
		$upload = wp_upload_bits( $file_name, 0, '', $post['upload_date'] );
		
		if ( $upload['error'] )
			return new WP_Error( 'upload_dir_error', $upload['error'] );
		
		$headers = wp_get_http( $url, $upload['file'] );
		
		if ( ! $headers ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', __('Remote server did not respond', 'kvp') );
		}
		
		if ( $headers['response'] != '200' ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', sprintf( __('Remote server returned error response %1$d %2$s', 'kvp'), esc_html($headers['response']), get_status_header_desc($headers['response']) ) );
		}

		$filesize = filesize( $upload['file'] );

		if ( isset( $headers['content-length'] ) && $filesize != $headers['content-length'] ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', __('Remote file is incorrect size', 'kvp') );
		}

		if ( 0 == $filesize ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', __('Zero size file downloaded', 'kvp') );
		}

		return $upload;
	}
	
	abstract protected function get_video_ids();
	
	abstract protected function get_video_info( $id );
	
}