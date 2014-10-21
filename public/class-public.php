<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* The public-facing functionality of the plugin.
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/public
* @author     Keiser Media <support@keisermedia.com>
*/
class Katalyst_Video_Plus_Public {

	/**
	 * The readable name of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string The ID of this plugin.
	 */
	private $name;
	
	/**
	 * The ID of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string The ID of this plugin.
	 */
	private $slug;

	/**
	 * The version of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string  The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 * @var      string    $slug       The slug of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $name, $slug, $version ) {
		
		$this->name = $name;
		$this->slug = $slug;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->slug, plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/kvp.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->slug, plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/kvp.js', array( 'jquery' ), $this->version, false );

	}
	/**
	 * Audit current KVP page
	 * 
	 * @since 2.0.0
	 */
	public function audit_single() {
		
		if( !is_single() )
			return;
		
		$post_id = get_the_ID();
		$post_meta	= get_post_meta( $post_id, '_kvp', true );
		
		if( isset($post_meta['post_id']) ) {
			
			$kvp_youtube_import = new Katalyst_Video_Plus_Import;
			$kvp_youtube_import->audit( $post_meta['post_id'] );
			
		}
		
	}
	
	/**
	 * Filters the_content to display video embed code
	 * 
	 * @since 2.0.0
	 */
	public function the_content( $content ) {
		
		$settings	= get_option( 'kvp_settings' );
		$post_meta	= get_post_meta( get_the_ID(), '_kvp', true );
		
		if( empty($post_meta['service']) || empty($post_meta['video_id']) || get_post_type() !== 'post' || ( !is_single() && !isset($settings['show_video_in_lists']) ) )
			return $content;
		
		if( !isset($settings['show_video_in_lists']) && has_post_thumbnail() && !is_single() )
			return the_post_thumbnail();
		
		$format = apply_filters( 'kvp_' . $post_meta['service'] . '_video_embed', '', $post_meta);
		$size	= $this->get_thumbnail_size();
		
		$atts = array(
			'video_id'	=> $post_meta['video_id'],
			'username'	=> $post_meta['username'],
			'height'	=> ( $size[0] * 9 / 16 ),
			'width'		=> $size[0],
		);
		
		$video_html = apply_filters( 'kvp_' . $post_meta['service'] . '_video_embed', '', $atts );
		
		if( !is_single() )
			return $video_html;
		
		return $video_html . $content;
		
	}
	
	/**
	 * Removes thumbnail from single posts
	 * 
	 * @since 2.0.0
	 */
	public function post_thumbnail_html( $html ) {
		
		$post_meta = get_post_meta( get_the_ID(), '_kvp', true );
		
		if( !empty($post_meta) && is_single() )
			return '';
		
		return $html;
		
	}
	
	/**
	 * Gets appropriate thumbnail size for videos
	 */
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
 		
 		$set_size = apply_filters( 'post_thumbnail_size', 'post-thumbnail' );
 		
 		if( isset($sizes[$set_size]) && is_array($sizes[$set_size]) )
 			return $sizes[$set_size];
 		
 		return array( 640, 385 );
 		
	}
	
}