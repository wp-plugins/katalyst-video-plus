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
		add_filter( 'kvp_save_source', array( $this, 'save_source' ) );
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
			'types'		=> array(
				'channels',
				'playlists',
				'videos',
				'search',
			),
			'features'	=> array(
				'developer_key',
			),
		);
		
		return $labels;
	}
	
	public function save_source( $source ) {
		
		if( 'youtube' != $source['service'] || 'channels' != $source['type'] )
			return $source;
		
		$settings = get_option( 'kvp_settings', array() );
		
		$query = array( 'part' => 'id', 'id' => implode( ',', $source['items'] ) );
		
		if( isset($settings['youtube_api_key']) )
			$query = array_merge( $query, array( 'key' => $settings['youtube_api_key'] ) );
		
		$service = new KVP_YouTube_Basic_Client( $source );
		$response = $service->request( 'channels', $query );
		
		if( is_wp_error($response) )
			return $source;

		if( count($source['items']) == $response['pageInfo']['totalResults'] )
			return $source;
		
		$channels = array();
		
		if( !isset($response['items']) )
			$response['items'] = array();
		
		foreach( $response['items'] as $item )
			$channels[] = $item['id'];
		
		foreach( $source['items'] as $key => $item ) {
			
			if( in_array( $item, $channels ) )
				continue;
			
			$query = array( 'part' => 'id', 'forUsername' => $item );
			
			if( isset($settings['youtube_api_key']) )
				$query = array_merge( $query, array( 'key' => $settings['youtube_api_key'] ) );
			
			$response = $service->request( 'channels', $query );
			
			if( !isset($response['items'][0]['id']) ) {
				
				unset( $source['items'][$key] );
				$name = ( isset($source['name']) ) ? $source['name'] : __( 'Not Set', 'kvp' );
				kvp_activity_log( __( 'Save YouTube Source', 'kvp' ), 'error', array( 'message' => sprintf( __( 'Unidentifiable Channel <em>%s</em> removed from source <em></em>.', 'kvp' ), $item, $source['name'] ) ) );
				continue;
			}
			
			$source['items'][$key] = $response['items'][0]['id'];
			
		}
		
		return $source;
		
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
		
		return '<iframe id="kvp-player-' . $video_id . '" type="text/html" width="' . $width . '" height="' . $height . '" src="http://www.youtube.com/embed/' . $video_id . '?origin=' . get_site_url() . '" frameborder="0"></iframe>';
		
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
		
		$settings['youtube_api_key'] = array(
			'id'	=> 'youtube_api_key',
			'name'	=> __( 'YouTube API Key', 'kvp' ),
			'desc'	=> sprintf( __( '%s required to access YouTube videos.', 'kvp' ), '<a href="https://developers.google.com/youtube/v3/getting-started#before-you-start">' . __( 'API key' ) . '</a>' ),
			'type'	=> 'text',
		);
		
		return $settings;
		
	}
	
}