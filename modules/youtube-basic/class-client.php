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
	 * The api key for daily queries
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string API Key
	 */
	protected $api_key;
	
	/**
	 * Creates url from parameter array
	 * 
	 * @since 2.0.0
	 */
	public function __construct( $source ) {
		
		$this->api_url	= 'https://www.googleapis.com/youtube/v3';
		$this->source	= $source;
		$this->limit	= 50;
		
		$settings = get_option( 'kvp_settings', array() );
		
		if( isset($settings['youtube_api_key']) )
			$this->api_key = $settings['youtube_api_key'];
		
		$this->resources = array(
			'channels'	=> array(
				'endpoints'	=> array(),
				'parameters'	=> array(
					'key' => array(
						'type' => 'string',
						'required' => true,
					),
					'part' => array(
						'type' => 'string',
						'required' => true,
					),
					'managedByMe' => array(
						'type' => 'boolean',
					),
					'onBehalfOfContentOwner' => array(
						'type' => 'string',
					),
					'forUsername' => array(
						'type' => 'string',
					),
					'mine' => array(
						'type' => 'boolean',
					),
					'maxResults' => array(
						'type' => 'integer',
					),
					'id' => array(
						'type' => 'string',
					),
					'pageToken' => array(
						'type' => 'string',
					),
					'mySubscribers' => array(
						'type' => 'boolean',
					),
					'categoryId' => array(
						'type' => 'string',
					),
				),
				'responses' => array(
					
				),
			),
			'playlistItems'	=> array(
				'path'	=> 'playlistItems',
				'parameters'	=> array(
					'key' => array(
						'type' => 'string',
						'required' => true,
					),
					'part' => array(
						'type' => 'string',
						'required' => true,
					),
					'onBehalfOfContentOwner' => array(
						'type' => 'string',
					),
					'playlistId' => array(
						'type' => 'string',
					),
					'videoId' => array(
						'type' => 'string',
					),
					'maxResults' => array(
						'type' => 'integer',
					),
					'pageToken' => array(
						'type' => 'string',
					),
					'id' => array(
						'type' => 'string',
					),
				),
			),
			'playlists'	=> array(
				'path'	=> 'playlists',
				'parameters'	=> array(
					'key' => array(
						'type' => 'string',
						'required' => true,
					),
					'part' => array(
						'type' => 'string',
						'required' => true,
					),
					'onBehalfOfContentOwner' => array(
						'type' => 'string',
					),
					'onBehalfOfContentOwnerChannel' => array(
						'type' => 'string',
					),
					'channelId' => array(
						'type' => 'string',
					),
					'mine' => array(
						'type' => 'boolean',
					),
					'maxResults' => array(
						'type' => 'integer',
					),
					'pageToken' => array(
						'type' => 'string',
					),
					'id' => array(
						'type' => 'string',
					),
				),
			),
			'search'	=> array(
				'path'	=> 'search',
				'parameters'	=> array(
					'key' => array(
						'type' => 'string',
						'required' => true,
					),
					'part' => array(
						'type' => 'string',
						'required' => true,
					),
					'eventType' => array(
						'type' => 'string',
					),
					'channelId' => array(
						'type' => 'string',
					),
					'videoSyndicated' => array(
						'type' => 'string',
					),
					'channelType' => array(
						'type' => 'string',
					),
					'videoCaption' => array(
						'type' => 'string',
					),
					'publishedAfter' => array(
						'type' => 'string',
					),
					'onBehalfOfContentOwner' => array(
						'type' => 'string',
					),
					'pageToken' => array(
						'type' => 'string',
					),
					'forContentOwner' => array(
						'type' => 'boolean',
					),
					'regionCode' => array(
						'type' => 'string',
					),
					'location' => array(
						'type' => 'string',
					),
					'locationRadius' => array(
						'type' => 'string',
					),
					'videoType' => array(
						'type' => 'string',
					),
					'type' => array(
						'type' => 'string',
					),
					'topicId' => array(
						'type' => 'string',
					),
					'publishedBefore' => array(
						'type' => 'string',
					),
					'videoDimension' => array(
						'type' => 'string',
					),
					'videoLicense' => array(
						'type' => 'string',
					),
					'maxResults' => array(
						'type' => 'integer',
					),
					'relatedToVideoId' => array(
						'type' => 'string',
					),
					'videoDefinition' => array(
						'type' => 'string',
					),
					'videoDuration' => array(
						'type' => 'string',
					),
					'relevanceLanguage' => array(
						'type' => 'string',
					),
					'forMine' => array(
						'type' => 'boolean',
					),
					'q' => array(
						'type' => 'string',
					),
					'safeSearch' => array(
						'type' => 'string',
					),
					'videoEmbeddable' => array(
						'type' => 'string',
					),
					'videoCategoryId' => array(
						'type' => 'string',
					),
					'order' => array(
						'type' => 'string',
					),
				),
			),
			'videoCategories'	=> array(
				'path'	=> 'videoCategories',
				'parameters'	=> array(
					'key' => array(
						'type' => 'string',
						'required' => true,
					),
					'part' => array(
						'type' => 'string',
						'required' => true,
					),
					'regionCode' => array(
						'type' => 'string',
					),
					'id' => array(
						'type' => 'string',
					),
					'hl' => array(
						'type' => 'string',
					),
				),
			),
			'videos'	=> array(
				'path'	=> 'videos',
				'parameters'	=> array(
					'key' => array(
						'type' => 'string',
						'required' => true,
					),
					'part' => array(
						'type' => 'string',
						'required' => true,
					),
					'onBehalfOfContentOwner' => array(
						'type' => 'string',
					),
					'regionCode' => array(
						'type' => 'string',
					),
					'locale' => array(
						'type' => 'string',
					),
					'videoCategoryId' => array(
						'type' => 'string',
					),
					'chart' => array(
						'type' => 'string',
					),
					'maxResults' => array(
						'type' => 'integer',
					),
					'pageToken' => array(
						'type' => 'string',
					),
					'hl' => array(
						'type' => 'string',
					),
					'myRating' => array(
						'type' => 'string',
					),
					'id' => array(
						'type' => 'string',
					),
				),
			),
		);
		
	}
	
	/**
	 * Filters request response
	 * 
	 * @since 3.0.0
	 * @param  array $response Unfiltered response
	 * @return array           Filtered response
	 */
	public function filter_response( $response ) {
		
		$filtered = array(
			'page_info'	=> array(),
			'items'		=> array(),
		);
		
		foreach( $response as $key => $value ) {
			
			switch( $key ) {
				
				case 'pageInfo':
					
					if( isset($value['totalResults']) )
						$filtered['page_info']['total_results'] = $value['totalResults'];
					
					break;
				
				case 'prevPageToken':
					$filtered['prev_token'] = $value;
					break;
				
				case 'nextPageToken':
					$filtered['next_token'] = $value;
					break;
				
				case 'items':
					
					foreach( $value as $item ) {
						
						if( isset($item['snippet']) )
							$part = 'snippet';
						
						if( isset($item['contentDetails']) )
							$part = 'contentDetails';
						
						if( isset($item['id']) )
							$new_item['id'] = $item['id'];
						
						if( isset($item['videoId']) )
							$new_item['video_id'] = $item['video_id'];
						
						if( isset($item[$part]['title']) )
							$new_item['title'] = $item[$part]['title'];
						
						if( isset($item[$part]['description']) )
							$new_item['description'] = $item[$part]['description'];
						
						if( isset($item[$part]['thumbnails']) ) {
							
							if( isset($item[$part]['thumbnails']['maxres']['url']) )
								$new_item['thumbnail'] = $item[$part]['thumbnails']['maxres']['url'];
							
							else
							if( isset($item[$part]['thumbnails']['default']['url']) )
								$new_item['thumbnail'] = $item[$part]['thumbnails']['default']['url'];
							
						}
						
						$filtered['items'][] = $new_item;
						
					}
					
					break;
				
				default:
					
					break;
				
			}
			
		}
		
		return $filtered;
		
	}
	
	/**
	 * Creates url from parameter array
	 * 
	 * @since 2.0.0
	 * @return array returns array of video ids
	 */
	public function get_videos( $query = array() ) {
		
		$results = array(
			'page_info' => array(
				'scanned' => 0,
				'duplicates' => 0,
				'total'	=> 0,
			),
			'items' => array(),
		);
		
		if( 'channels' == $this->source['type'] ) {
			
			$channel_ids = implode( ',', $this->source['items']);
			$response = $this->request( 'channels', array( 'part' => 'contentDetails', 'id' => $channel_ids, 'key' => $this->api_key ) );
			
			$playlist_ids = array();
			
			if( !isset($response['items']) )
				return $results;
			
			foreach( $response['items'] as $item )
				$playlist_ids[] = $item['contentDetails']['relatedPlaylists']['uploads'];
			
		}
		
		if( 'playlists' == $this->source['type'] || isset($playlist_ids) ) {
			
			$playlist_ids = ( isset($playlist_ids) ) ? $playlist_ids : $this->source['items'];
			$params = array( 'part' => 'contentDetails', 'maxResults' => $this->limit, 'pageToken' => null, 'key' => $this->api_key );
			$posts_meta = kvp_get_posts_meta();
			$posts_video_ids = array();
			
			$videos = array();
			
			foreach( $posts_meta as $post_meta )
				$posts_video_ids[] = $post_meta['video_id'];
			
			foreach( $playlist_ids as $playlist ) {
				
				do {
					
					$response = $this->request( 'playlistItems', array_merge( $params, array( 'playlistId' => $playlist ) ) );
					
					if( is_wp_error( $response ) ) {
						kvp_activity_log( __( 'YouTube Get Videos', 'kvp' ), 'error', array( 'message' => $response ) );
						continue;
					}
					
					if( !isset($response['items']) )
						return $results;
					
					foreach( $response['items'] as $item ) {
						
						$results['page_info']['scanned']++;
						
						if( in_array( $item['contentDetails']['videoId'], $posts_video_ids ) ) {
							$results['page_info']['duplicates']++;
							continue;
						}
						
						$videos[] = $item['contentDetails']['videoId'];
						
					}
					
					if( isset($response['nextPageToken']) )
						$params['pageToken'] = $response['nextPageToken'];
					
					else
						$params['pageToken'] = null;
					
				} while( count($videos) <= $this->limit && !empty($params['pageToken']) );
				
				$results['page_info']['total'] = $results['page_info']['total'] + $response['pageInfo']['totalResults'];
				
			}
			
			$videos = array_unique( $videos );
			
		}
		
		if( 'search' == $this->source['type'] ) {
			
			$params = array( 'part' => 'snippet', 'type' => 'video', 'q' => implode( ',', $this->source['items']), 'maxResults' => $this->limit, 'pageToken' => null, 'key' => $this->api_key );
			$posts_meta = kvp_get_posts_meta();
			$posts_video_ids = array();
			
			foreach( $posts_meta as $post_meta )
				$posts_video_ids[] = $post_meta['video_id'];
			
			$videos = array();
			
			do {
				
				$response = $this->request( 'search', $params );
				
				if( is_wp_error( $response ) ) {
					kvp_activity_log( __( 'YouTube Get Videos', 'kvp' ), 'error', array( 'message' => $response ) );
					continue;
				}
				
				if( !isset($response['items']) )
					return $results;
				
				foreach( $response['items'] as $item ) {
					
					$results['page_info']['scanned']++;
					
					if( in_array( $item['id']['videoId'], $posts_video_ids ) ) {
						$results['page_info']['duplicates']++;
						continue;
					}
						
					$videos[] = $item['id']['videoId'];
					
				}
				
				if( isset($response['nextPageToken']) )
					$params['pageToken'] = $response['nextPageToken'];
				
				else
					$params['pageToken'] = null;
				
			} while( count($videos) <= $this->limit && !empty($params['pageToken']) );
			
			$results['page_info']['total'] = $results['page_info']['total'] + $response['pageInfo']['totalResults'];
			
			$videos = array_unique( $videos );
			
		}
		
		if( 0 == $results['page_info']['total'] )
			return $results;
		
		$query = array_merge( $query, array( 'pageToken' => null, 'id' => implode( ',', $videos ) ) );
		
		$item_offset = $this->limit;
		$all_items = ( isset($query['id']) ) ? explode( ',', $query['id'] ) : array();
		
		do {
			
			if( isset($query['id']) && count($all_items) > $this->limit ) {
				
				$sectioned_items = $all_items;
				
				array_splice( $sectioned_items, $item_offset );
				$query['id'] = implode( ',', $sectioned_items );
				$item_offset = $item_offset + $this->limit;
				
			}
			
			$response = $this->request( 'videos', array_merge( $query, array( 'part' => 'snippet', 'maxResults' => $this->limit, 'pageToken' => null, 'key' => $this->api_key ) ) );
			
			if( is_wp_error( $response ) )
				return $response;
			
			if( !isset($response['items']) )
				$response['items'] = array();
			
			foreach( $response['items'] as $item ) {
				
				$results['items'][] = array(
					'id' => ( isset($item['id']) ) ? $item['id'] : $item['snippet']['videoId'],
					'title' => $item['snippet']['title'],
					'thumbnail' => ( isset($item['snippet']['thumbnails']['high']) ) ? $item['snippet']['thumbnails']['high']['url'] : $item['snippet']['thumbnails']['default']['url'],
				);
				
			}
			
			if( isset($response['nextPageToken']) )
				$params['pageToken'] = $response['nextPageToken'];
			
			else
				$params['pageToken'] = null;
			
		} while( count($results['items']) < $this->limit && count($results['items']) > $results['page_info']['total'] );
		
		return $results;
		
	}
	
	/**
	 * Returns video information from ID
	 * 
	 * @since 2.0.0
	 * @return array Returns video response information
	 */
	public function get_video( $video_id ) {
		
		$response = $this->request( 'videos', array( 'part' => 'snippet', 'id' => $video_id, 'key' => $this->api_key ) );
		
		if( !isset($response['items'][0]['snippet']) || is_wp_error( $response ) )
			return kvp_activity_log( __( 'Get Video', 'kvp' ), 'error', array( 'message' => sprintf( 'There was an error retrieving video: <em>%s</em>.', $video_id ) ) );
		
		return $response['items'][0]['snippet'];
		
	}
	
}