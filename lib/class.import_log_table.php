<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }

class KVP_Log_Table extends WP_List_Table {
	
	private	$providers	= array();
	private $sources	= array();
	
	public function __construct() {
		
		parent::__construct( array(
            'singular'  => 'item',
            'plural'    => 'items',
            'ajax'      => true
        ) );
        
		$this->providers = apply_filters('kvp_providers', array() );
        $this->sources	= get_option('kvp_sources', array() );
        $this->items	= get_option('kvp_log', array() );
		
	}

    public function get_columns(){
        $columns = array(
            'cb'			=> '<input type="checkbox" />',
            'title'			=> __('Username', 'kvp'),
            'provider'		=> __('Provider', 'kvp'),
            'message'		=> __('Message', 'kvp'),
			'video_id'		=> __('Video ID', 'kvp'),
            'date'			=> __('Date', 'kvp'),
        );
        return $columns;
    }

    public function get_sortable_columns() {
        $sortable_columns = array(
            'title'		=> array('username', false),     //true means it's already sorted
            'provider'	=> array('provider', false),
            'date'		=> array('date', true),
        );
        return $sortable_columns;
    }

    protected function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['ID'] );
    }
    
    protected function column_default( $item, $column_name ) {
		
        switch( $column_name ) {
            case 'title':
				return $this->sources[$item['ID']]['username'];
			
            case 'provider':
            	if( isset($this->providers[$this->sources[$item['ID']]['provider']]['title']) )
            		return $this->providers[$this->sources[$item['ID']]['provider']]['title'];
            		
            	return __('Provider not installed.', 'kvp');

            case 'message':
                return $item[$column_name];
                
            case 'video_id':
				if( is_array($item['active']) && isset($item['active']['ID']) )
					return $item['active']['ID'];
				
				return __('N/A', 'kvp');
			
            case 'date':
            	return date(get_option('date_format') . ' ' . get_option('time_format'), $item[$column_name]);

            default:
                return print_r($item, true);
        }

    }

    public function prepare_items() {
        global $wpdb;

        $per_page = 20;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $data = $this->items;

        function usort_reorder( $a, $b ){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'date'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
            $result = strcmp( $a[$orderby], $b[$orderby] ); //Determine sort order
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
		_e('Log empty.', 'kvp');
	}
	
}