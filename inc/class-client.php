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
	 * The source information
	 *
	 * @since    2.0.0
	 * @var      array Account Information
	 */
	protected $source;
	
	/**
	 * The base url for the api
	 *
	 * @since    2.0.0
	 * @var      string API URL
	 */
	protected $api_url;
	
	/**
	 * Specifies the maximum number of items returned
	 *
	 * @since    2.0.0
	 * @var      integer
	 */
	protected $limit = 50;
	
	/**
	 * Contains service resources
	 * 
	 * @since 3.0.0
	 * @var array
	 */
	protected $resources = array();
	
	/**
	 * Make an API request
	 * 
	 * @since 3.0.0
	 * @param  string  $path      An API endpoint
	 * @param  array   $params    An array of parameters to send to the endpoint
	 * @param  string  $method    The HTTP method of the request
	 * @param  boolean $json_body
	 * @return array             This array contains three keys, 'status' is the status code, 'body' is the object representation of the json response body, and 'headers' are an associated array of response headers
	 */
	public function request( $path, $params = array(), $method = 'GET', $json_body = true ) {
		
		if( !isset($this->resources[$path]) )
			return kvp_activity_log( __( 'Request', 'kvp' ), 'error', array( 'message' => sprintf( __( '<em>%s</em> is not a valid path for <em>%s</em>', 'kvp' ), $path, $this->source['service'] ) ) );
		
		$endpoints = explode( '/', $path );
		$parameters = array();
		
		foreach( $params as $param_name => $param_value ) {
			
			if( in_array( $param_name, $endpoints ) && in_array( $param_name, $this->resources[$path]['endpoints'] ) ) {
				
				$path = str_replace( $param_name, $param_value, $path );
				unset($params[$param_name]);
			}
			
		}
		
		foreach( $this->resources[$path]['parameters'] as $param_name => $param_spec ) {
			
			if( isset($param_spec['required']) && $param_spec['required'] && !isset($params[$param_name]) )
				return kvp_activity_log( __( 'Request', 'kvp' ), 'error', array( 'message' => sprintf( __( 'Required parameter <em>%s</em> is not set.', 'kvp' ), $param_name ), 'path' => $path, 'params' => $params ) );
			
			if( !empty($parameters[$param_name]) && gettype($param_spec['type']) !== $param_spec['type'] )
				continue;
			
			if( isset($params[$param_name]) ) {
				
				$value	= $params[$param_name];
				$parameters[$param_name]  = $value;
				
			} else
				unset($parameters[$param_name]);
		
		}
		
		$parameters = apply_filters( 'kvp_' . $this->source['service'] . '_parameters', $parameters );
		
		$url = $this->api_url . '/' . $path . '?' . http_build_query( $parameters, '', '&' );
		
		$response = (array) wp_remote_get( $url, array( 'timeout' => 5, 'sslverify' => false ) );
		
		if( is_wp_error($response) ) {

			kvp_activity_log( __( 'Request', 'kvp' ), 'error', array( 'message' => $response->get_error_message(), 'url' => $url ) );
			return $response;

		}
		
		return json_decode( $response['body'], true );
	}
	
	abstract function filter_response( $response );
	
}