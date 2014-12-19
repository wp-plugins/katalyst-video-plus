<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* Abstract class for api clients
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/inc
* @author     Keiser Media <support@keisermedia.com>
*/
abstract class Katalyst_Video_Plus_Client {
	
	/**
	 * The account information
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      array Account Information
	 */
	protected $account;
	
	/**
	 * The base url for the api
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      string API URL
	 */
	protected $api_url;
	
	/**
	 * Specifies the maximum number of items returned
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      integer
	 */
	protected $limit = 50;
	
	/**
	 * Creates a url from parameter array
	 * 
	 * @since 2.0.0
	 */
	abstract protected function create_url( $query, $resource, $endpoint );
	
	/**
	 * Fetches the url and returns the json as an array
	 * 
	 * @since 2.0.0
	 */
	protected static function request( $url, $check = false ) {
		
		$response = (array) wp_remote_get( $url, array( 'timeout' => 5, 'sslverify' => false ) );
		
		if( !isset($response['response']) || !isset($response['response']['code']) )
			return false;
		
		if( $check )
			return $response['response']['code'];
		
		if( !isset($response['response']['code']) || 200 != $response['response']['code'] || is_wp_error($response) )
			return new WP_Error( 'response_code', array_merge( array( 'request_url' => $url ), $response ) );
		
		return json_decode( $response['body'], true );
		
	}
	
	/**
	 * Checks the status of the account
	 * 
	 * @since 2.0.0
	 * @return string Readable status
	 */
	abstract public function check_status();
	
	/**
	 * Generates list of video ids
	 * 
	 * @since 2.0.0
	 */
	abstract public function get_videos( array $query );
	
	/**
	 * Retrieves video information
	 * 
	 * @since 2.0.0
	 */
	abstract public function get_video( $video_id );
	
}