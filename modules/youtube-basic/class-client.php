<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* Provides YouTube client functions
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/modules/youtube-basic
* @author     Keiser Media <support@keisermedia.com>
*/
class KVP_YouTube_Basic_Client extends Katalyst_Video_Plus_Client {
	
	/**
	 * The developer key for daily queries
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string Developer Key
	 */
	protected $developer_key;
	
	/**
	 * Creates url from parameter array
	 * 
	 * @since 2.0.0
	 */
	public function __construct( $account ) {
		
		$this->api_url = 'https://www.googleapis.com/youtube/v3';
		$this->account = $account;
		
		if( isset($account['developer_key']) )
			$this->developer_key = $account['developer_key'];
		
	}
	
	/**
	 * Creates url from parameter array
	 * 
	 * @since 2.0.0
	 */
	protected function create_url( $query, $resource = 'channels' ) {
		
		if( $resource == 'channels' ) {
			
			$url_query = sprintf( '%s/channels?forUsername=%s&part=contentDetails&key=%s', $this->api_url, urlencode( $this->account['username'] ), $this->developer_key );
			return $url_query;
			
		}
		
		if( $resource == 'playlistItems' && isset($query['uploads_id']) ) {
			
			$url_query = sprintf( '%s/playlistItems?maxresults=%s&playlistId=%s&part=contentDetails&key=%s', $this->api_url, $this->limit, $query['uploads_id'], $this->developer_key );
			
			if( isset($query['page_token']) && !empty($query['page_token']) )
				$url_query .= '&pageToken=' . $query['page_token'];
			
			return $url_query;
			
		}
		
		if( $resource == 'videos' ) {
			
			$url_query = sprintf( '%s/videos?id=%s&part=snippet&key=%s', $this->api_url, $query, $this->developer_key );
			return $url_query;
			
		}
		
	}
	
	/**
	 * Checks the status of the account
	 * 
	 * @since 2.0.0
	 * @return string Readable status
	 */
	public function check_status() {
		
		$channel_query	= $this->create_url( array(), 'channels' );
		$channel_code	= $this->request( $channel_query, true );
		
		switch( $channel_code ) {
			case 200:
				$status = __( 'Connected', 'kvp' );
				break;
			
			default:
				$status = __( 'Error Connecting', 'kvp' );
				break;
		}
		
		return $status;
		
	}
	
	/**
	 * Creates url from parameter array
	 * 
	 * @since 2.0.0
	 * @return array returns array of video ids
	 */
	public function get_videos( array $query = array() ) {
		
		$channel_query = $this->create_url( $query, 'channels' );
		
		// First request to retrieve "uploads" playlist
		$channel_response = $this->request( $channel_query );
		
		if( is_wp_error( $channel_response ) )
				return $channel_response;
		
		if( !isset($channel_response['pageInfo']['totalResults']) || 0 == $channel_response['pageInfo']['totalResults'] )
			return false;
		
		$playlist_params['uploads_id']	= $channel_response['items'][0]['contentDetails']['relatedPlaylists']['uploads'];
		$playlist_params['page_token']	= null;
		
		$video_ids = array();
		
		do {
			
			$playlist_query = $this->create_url( $playlist_params, 'playlistItems' );
			$playlist_response = $this->request( $playlist_query );
			
			if( is_wp_error( $playlist_response ) )
				return $playlist_response;
			
			foreach( $playlist_response['items'] as $item ) {
				//echo '<pre>';
				//print_r($item);
				//echo '</pre>';
				$video_ids[] = $item['contentDetails']['videoId'];
			}
			
			if( isset($playlist_response['nextPageToken']) )
				$playlist_params['page_token'] = $playlist_response['nextPageToken'];
			
			else
				$playlist_params['page_token'] = null;
			
		} while( !empty($playlist_params['page_token']) );
		
		return $video_ids;
		
	}
	
	/**
	 * Returns video information from ID
	 * 
	 * @since 2.0.0
	 * @return array Returns video response information
	 */
	public function get_video( $video_id ) {
		
		$video_query = $this->create_url( $video_id, 'videos' );
		$video_response = $this->request( $video_query );
		
		if( is_wp_error( $video_response ) )
			return $video_response;
		
		return $video_response['items'][0]['snippet'];
		
	}
	
}