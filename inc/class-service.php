<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* Abstract class for services
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/inc
* @author     Keiser Media <support@keisermedia.com>
*/
abstract class Katalyst_Video_Plus_Service {
	
	/**
	 * Identifies the service slug
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string The ID of the service
	 */
	protected $slug;
	
	/**
	 * Initilizes essential settings for services
	 * 
	 * @since 2.0.0
	 */
	public function __construct( $service_slug ) {
		
		$this->slug = $service_slug;
		
		add_filter( 'kvp_services', array( $this, 'labels' ) );
		add_filter( 'kvp_' . $this->slug . '_post_title', array( $this, 'post_title' ), 10, 2 );
		add_filter( 'kvp_' . $this->slug . '_post_content', array( $this, 'post_content' ), 10, 2 );
		add_filter( 'kvp_' . $this->slug . '_post_date', array( $this, 'post_date' ), 10, 2 );
		add_filter( 'kvp_' . $this->slug . '_video_embed', array( $this, 'video_embed' ), 10, 2 );
		
	}
	
	/**
	 * Registers labels for service
	 * 
	 * @since 2.0.0
	 */
	abstract public function labels( $labels );
	
	/**
	 * Filters API for post title
	 * 
	 * @since 2.0.0
	 * @return string post title
	 */
	abstract public function post_title( $element, $video_info );
	
	/**
	 * Filters API for post content
	 * 
	 * @since 2.0.0
	 * @return string post content
	 */
	abstract public function post_content( $element, $video_info );
	
	/**
	 * Filters API for post date
	 * 
	 * @since 2.0.0
	 * @return string post date
	 */
	abstract public function post_date( $element, $video_info );
	
	/**
	 * Registers video embed template for service
	 * 
	 * @since 2.0.0
	 */
	abstract public function video_embed( $content = null, $atts );
	
}