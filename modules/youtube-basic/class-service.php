<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* Provides a service bridge from YouTube
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/modules/youtube-basic
* @author     Keiser Media <support@keisermedia.com>
*/
class KVP_YouTube_Basic_Service extends Katalyst_Video_Plus_Service {
	
	/**
	 * Initilizes essential settings for services
	 * 
	 * @since 2.0.0
	 */
	public function __construct() {
		
		parent::__construct( 'youtube' );
		add_filter( 'kvp_youtube_post_thumbnail', array( $this, 'post_thumbnail' ), 10, 2 );
		add_filter( 'kvp_settings_misc', array( $this, 'add_settings' ) );
		
	}
	
	/**
	 * Registers labels for service
	 * 
	 * @since 2.0.0
	 */
	public function labels( $labels ) {
		
		$labels['youtube'] = array(
			'label'		=> __( 'YouTube Basic', 'kvp' ),
			'color'		=> '#e74c3c',
			'highlight'	=> '#c0392b',
			'features'	=> array(
				'developer_key',
			),
		);
		
		return $labels;
	}
	
	/**
	 * Filters API for post title
	 * 
	 * @since 2.0.0
	 * @return string post title
	 */
	public function post_title( $element, $video_info ) {
		
		$element = $video_info['title'];
		
		return $element;
		
	}
	
	/**
	 * Filters API for post content
	 * 
	 * @since 2.0.0
	 * @return string post content
	 */
	public function post_content( $element, $video_info ) {
		
		$element = $video_info['description'];
		
		return $element;
		
	}
	
	/**
	 * Filters API for post date
	 * 
	 * @since 2.0.0
	 * @return string post date
	 */
	public function post_date( $element, $video_info ) {
		
		$element = $video_info['publishedAt'];
		
		return $element;
		
	}
	
	/**
	 * Filters API for thumbnail url
	 * 
	 * @since 2.0.0
	 * @return string post date
	 */
	public function post_thumbnail( $element, $video_info ) {
		
		$element = isset($video_info['thumbnails']['maxres']['url']) ? $video_info['thumbnails']['maxres']['url'] : $video_info['thumbnails']['default']['url'];
		
		return $element;
		
	}
	
	/**
	 * Registers template for service
	 * 
	 * @since 2.0.0
	 */
	public function video_embed( $content, $atts ) {
		
		extract( shortcode_atts( array(
			'video_id'	=> null,
			'width'   	=> 560,
			'height'  	=> 315,
		), $atts ) );
		
		return '<iframe id="ytplayer-' . $video_id . '" type="text/html" width="' . $width . '" height="' . $height . '" src="http://www.youtube.com/embed/' . $video_id . '?origin=' . get_site_url() . '" frameborder="0"></iframe>';
		
	}
	
	/**
	 * Adds settings to misc
	 * 
	 * @since 2.1.0
	 * @return string post date
	 */
	public function add_settings( $settings ) {
		
		$settings['youtube_basic_header'] = array(
			'id'	=> 'youtube_basic_header',
			'name'	=> __( 'YouTube Basic', 'kvp' ),
			'type'	=> 'header',
		);
		
		$settings['youtube_api_fallback'] = array(
			'id'	=> 'youtube_api_fallback',
			'name'	=> __( 'YouTube API Key Fallback', 'kvp' ),
			'desc'	=> __( 'If a YouTube account has issues with its API, this API will be used to prevent an interruption of service.', 'kvp' ),
			'type'	=> 'text',
		);
		
		return $settings;
		
	}
	
}