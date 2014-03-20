<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }

class KVP_Errors_Table extends WP_List_Table {
	
	private $providers;
	
	public function __construct() {
        global $kvp, $status, $page;
                
        parent::__construct( array(
            'singular'  => 'error',
            'plural'    => 'errors',
            'ajax'      => true
        ) );
        
		$sources	 = get_option('kvp_sources', array());
		$this->items = array();
		
		foreach( $sources as $source => $info ) {
			
			if( !isset($info['import']['errors']) )
				continue;
			
			foreach( $info['import']['errors'] as $video_id => $error ) {
				
				$this->items[$video_id] = array(
					'source'		=> $source,
					'video_id'		=> $video_id,
					'username'		=> $info['username'],
					'provider'		=> $info['provider'],
					'request'		=> $error['ID'],
					'post'			=> $error['post_id'],
					'image'			=> $error['image'],
					'tag_s'			=> $error['tags'],
					'comment_s'		=> $error['comments'],
				);
				
				
				$this->items[$video_id]['last_import']	= isset($info['import']['last_import']) ? $info['import']['last_import'] : 0;
				
			}
			
		}
		
		$this->providers = apply_filters('kvp_providers', array() );
        
    }
    
    protected function column_default( $item, $column_name ) {
    	
        switch( $column_name ) {
        	case 'video_id':
            case 'username':
                return $item[$column_name];
                
            case 'provider':
            	return $this->providers[$item[$column_name]]['title'];
            	
            case 'request':
            case 'post':
            case 'image':
            case 'tag_s':
            case 'comment_s':
            	
            	switch( $item[$column_name] ) {
	            	
	            	case null:
	            		return __('Not processed', 'kvp');
	            	
	            	default:
	            		return $item[$column_name];
	            	
            	}
            	
            case 'last_import':
            	return date(get_option('date_format') . ' ' . get_option('time_format'), $item[$column_name]);
            	
            default:
                return print_r($item, true);
        }
        
    }
    
    protected function column_title( $item ) {
        
        $repair_nonce = wp_create_nonce('kvp_repair_nonce');
        
        
        $actions = array();
        
        //Build row actions
        //$actions['repair']	= sprintf('<a href="?page=%s&action=%s&source=%s&_wpnonce=%s">%s</a>', $_REQUEST['page'], 'repair', $item['source'], $repair_nonce, __('Repair Source', 'kvp') );
        
        //Return the title contents
        return sprintf( '%1$s %2$s', $item['video_id'], $this->row_actions($actions) );
    }
    
    protected function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['video_id'] );
    }
    
    public function get_columns(){
        $columns = array(
            'cb'			=> '<input type="checkbox" />',
            'title'			=> __('Video ID', 'kvp'),
            'username'		=> __('Username', 'kvp'),
            'provider'		=> __('Provider', 'kvp'),
            'request'		=> __('Request', 'kvp'),
            'post'			=> __('Post', 'kvp'),
			'image'			=> __('Image', 'kvp'),
			'tag_s'			=> __('Tags', 'kvp'),
			'comment_s'		=> __('Comments', 'kvp'),
            'last_import'	=> __('Last Import', 'kvp'),
        );
        return $columns;
    }
    
    public function get_sortable_columns() {
        $sortable_columns = array(
            'username'		=> array('username', false),     //true means it's already sorted
            'provider'		=> array('provider', false),
            'last_import'	=> array('last_import', false),
        );
        return $sortable_columns;
    }
    
    public function get_bulk_actions() {
        $actions = array(
        	'repair'	=> __('Repair', 'kvp'),
            'delete'	=> __('Delete', 'kvp')
        );
        return $actions;
    }
    
    private function process_bulk_action() {
        
    	$sources = ( isset($_REQUEST['source']) ) ? $_REQUEST['source'] : null;
    	
    	if( empty($sources) )
    		return false;
    	
    	$sources = ( is_array($sources) ) ? $sources : array($sources);
        
        switch( $this->current_action() ) {
			
	        case 'delete':
	        	
	        	$all_sources = get_option('kvp_sources', array() );
	        	
	        	foreach( $sources as $id ) {
	        		
	        		if( !current_user_can('delete_users') )
						break;
					
					if( isset($all_sources[$id]) ) {
						unset($all_sources[$id]);
						update_option('kvp_sources', $all_sources);
						$this->items = $all_sources;
					}
				
				}
				
				break;
        
        }
        
    }
    
    function extra_tablenav( $which ) {
		global $cat;
?>
		<div class="alignleft actions">
<?php
		if ( 'top' == $which && !is_singular() ) {

			if ( is_object_in_taxonomy( 'post', 'category' ) ) {
				$dropdown_options = array(
					'show_option_all' => __( 'View all categories' ),
					'hide_empty' => 0,
					'hierarchical' => 1,
					'show_count' => 0,
					'orderby' => 'name',
					'selected' => $cat
				);
				wp_dropdown_categories( $dropdown_options );
			}
			do_action( 'restrict_manage_posts' );
			submit_button( __( 'Filter' ), 'button', false, false, array( 'id' => 'post-query-submit' ) );
		}
?>
		</div>
<?php
	}
    
    public function single_row( $item ) {
		static $row_class = '';
		$row_class = ( $row_class == '' ? ' class="alternate"' : '' );

		echo '<tr id="source_' . $item['provider'] . '_' . $item['video_id'] . '"' . $row_class . '>';
		$this->single_row_columns( $item );
		echo '</tr>';
	}
    
    public function prepare_items() {
        global $wpdb;
        
        $per_page = 20;
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        $this->process_bulk_action();
        
        $data = $this->items;
        
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'last_import'; //If no sort, default to last import
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        
        usort($data, 'usort_reorder');
        
        $current_page = $this->get_pagenum();
        $total_items  = count($data);
        
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        $this->items = $data;
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
	
	public function no_items() {
		_e('No errors found.', 'kvp');
	}
	
}