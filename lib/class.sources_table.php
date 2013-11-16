<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }

class KVP_Sources_Table extends WP_List_Table {
	
	private $providers;
	
	public function __construct() {
        global $kvp, $status, $page;
                
        parent::__construct( array(
            'singular'  => 'source',
            'plural'    => 'sources',
            'ajax'      => true
        ) );
        
		$this->items = get_option('kvp_sources', array());
		$this->providers = apply_filters('kvp_providers', array() );
        
    }
    
    protected function column_default( $item, $column_name ) {
    	
        switch( $column_name ) {
            case 'username':
                return $item[$column_name];
            case 'provider':
            	return $this->providers[$item[$column_name]]['name'];
            case 'author':
            	$user = get_user_by('id', $item[$column_name]);
            	return $user->display_name;
            case 'categories':
				$output = array();
				
				foreach($item[$column_name] as $key => $cat_id) {
					if( 0 == $cat_id )
						continue;
					
					$term = get_term( $cat_id, 'category');
					$output[] = '<a href="' . get_category_link( $cat_id ) . '" title="' . esc_attr( sprintf( __( "View all posts in %s" ), get_the_category_by_ID((string)$cat_id) ) ) . '">' . get_the_category_by_ID((string)$cat_id) . '</a>';
				}
				return join( __( ', ' ), $output );
            case 'last_import':
            	if( isset($item['import'][$column_name]) )
            		return date(get_option('date_format') . ' ' . get_option('time_format'), $item['import'][$column_name]);
            	
            	return __('N/A', 'kvp');
            default:
                return print_r($item, true);
        }
        
    }
    
    protected function column_title( $item ) {
        
        $import_nonce = wp_create_nonce('kvp_import_nonce');
        $delete_nonce = wp_create_nonce('kvp_delete_nonce');
        
        //Build row actions
        $actions = array(
        	'import'	=> sprintf('<a href="?page=%s&action=%s&source=%s&_wpnonce=%s">%s</a>', $_REQUEST['page'], 'import', $item['ID'], $import_nonce, __('Import', 'kvp')),
        	//'audit'	=> sprintf('<a href="?page=%s&action=%s&source=%s&_wpnonce=%s">%s</a>', $_REQUEST['page'], 'audit', $item['ID'], $import_nonce, __('Audit', 'kvp')),
            //'edit'		=> sprintf('<a href="?page=%s&action=%s&source=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['ID']),
            'delete'	=> sprintf('<a href="?page=%s&action=%s&source=%s&_wpnonce=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['ID'], $delete_nonce),
        );
        
        //Return the title contents
        return sprintf( '%1$s %2$s', $item['username'], $this->row_actions($actions) );
    }
    
    protected function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['ID'] );
    }
    
    public function get_columns(){
        $columns = array(
            'cb'			=> '<input type="checkbox" />',
            'title'			=> __('Username', 'kvp'),
            'provider'		=> __('Provider', 'kvp'),
            'author'		=> __('Author', 'kvp'),
			'categories'	=> __('Categories', 'kvp'),
            'last_import'	=> __('Last Import', 'kvp'),
        );
        return $columns;
    }
    
    public function get_sortable_columns() {
        $sortable_columns = array(
            'title'			=> array('username', false),     //true means it's already sorted
            'provider'		=> array('provider', false),
            'last_import'	=> array('last_import', false),
        );
        return $sortable_columns;
    }
    
    public function get_bulk_actions() {
        $actions = array(
        	'import'	=> __('Import', 'kvp'),
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

		echo '<tr id="source_' . $item['ID'] . '"' . $row_class . '>';
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
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'ID'; //If no sort, default to title
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
		_e('No sources found.', 'kvp');
	}

	
}