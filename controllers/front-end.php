<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }

final class KVP_Front_End {
	
	public function __construct(){
		
		add_filter('post_thumbnail_html', array($this, 'thumbnail_html') );
		add_filter('the_content', array($this, 'the_content') );
	}
	
	public function the_content($content) {
		
		$this->get_metadata();
		$settings = get_option('kvp_settings');
		
		if( (isset($this->settings['post_format']) && 'standard' !== $this->settings['post_format']) && !isset($settings['show_video_in_list']) && has_post_thumbnail() && !is_single() )
			return the_post_thumbnail();
		
		if( empty($this->metadata['provider']) || empty($this->metadata['ID']) )
			return false;
		
		$format = apply_filters('kvp_' . $this->metadata['provider'] . '_video_embed', '');
		$size	= $this->get_thumbnail_size();
		
		$video_html = sprintf( $format, $this->metadata['ID'], $size[0], ($size[0] * 9 / 16) );
		
		if( !is_single() )
			return $video_html;
		
		return $video_html . $content;
	}
	
	public function thumbnail_html( $html ) {
		
		$this->get_metadata();
		
		if( !empty($this->metadata) && is_single() )
			return '';
		
		return $html;
		
	}
	
	private function get_thumbnail_size() {	
		global $_wp_additional_image_sizes;
     	
     	if( !empty($this->thumbnail_size) )
     		return $this->thumbnail_size;
     	
     	$sizes = array();
 		
 		foreach( get_intermediate_image_sizes() as $s ){
 			$sizes[ $s ] = array( 0, 0 );
 			
 			if( in_array( $s, array( 'thumbnail', 'medium', 'large' ) ) ){
 			
 				$sizes[ $s ][0] = get_option( $s . '_size_w' );
 				$sizes[ $s ][1] = get_option( $s . '_size_h' );
 			
 			} else {
 				if( isset( $_wp_additional_image_sizes ) && isset( $_wp_additional_image_sizes[ $s ] ) )
 					$sizes[ $s ] = array( $_wp_additional_image_sizes[ $s ]['width'], $_wp_additional_image_sizes[ $s ]['height'], );
 			}
 		}
 		
 		$set_size = apply_filters('post_thumbnail_size', 'post-thumbnail');
 		
 		if( isset($sizes[$set_size]) && is_array($sizes[$set_size]) )
 			return $sizes[$set_size];
 		
 		return array(640, 385);
 		
	}
	
	private function get_metadata() {
		
		$this->metadata = get_post_meta(get_the_ID(), '_kvp', true);
		
		return $this->metadata;
		
	}
	
}