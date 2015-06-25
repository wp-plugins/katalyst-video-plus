<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* Extends WP_List_Table to display the sources.
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/admin
* @author     Keiser Media <support@keisermedia.com>
*/

class KVP_Sources_Table extends WP_List_Table {
	
	/**
	 * Contains all available services.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      array  Service and service data.
	 */
	private $services = array();
	
	/**
	 * Contains all available sources.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      array  Source types and source type data.
	 */
	private $source_types = array();

	/**
	 * Contains import locked ID
	 * 
	 * @since	3.1.0
	 * @access   private
	 * @var      string  Source ID.
	 */
	private $import_lock = false;
	
	/**
	 * Retrieves essential data
	 *
	 * @since    2.0.0
	 */
	public function __construct() {
		
		parent::__construct( array(
            'singular'  => 'source',
            'plural'    => 'sources',
            'ajax'      => true
        ) );
        
		$this->services		= apply_filters('kvp_services', array() );
		$this->source_types	= kvp_get_source_types();
        $this->items		= get_option('kvp_sources', array() );
		$this->import_lock	= get_transient('kvp_import_lock');
		
	}
	
	/**
	 * Override parent columns and defines custom columns
	 *
	 * @since    2.0.0
	 * @return array
	 */
	public function get_columns() {
		
		$columns = array(
			'cb'			=> '<input type="checkbox" />',
            'title'			=> __( 'Name', 'kvp' ),
            'service'		=> __( 'Service', 'kvp' ),
            'type'			=> __( 'Type', 'kvp' ),
            'author'		=> __( 'Author', 'kvp' ),
            'categories'	=> __( 'Categories', 'kvp' ),
            'publish'		=> __( 'Auto Publish', 'kvp' ),
            'frequency'		=> __( 'Frequency', 'kvp' ),
            'status'		=> __( 'Status', 'kvp' ),
        );
        
        return apply_filters( 'manage_kvp_source_columns', $columns );
		
	}
	
	/**
	 * Define which columns are hidden
	 *
	 * @since    2.0.0
	 * @return array
	 */
	public function get_hidden_columns() {
		
		return array();
		
	}
	
	/**
	 * Override parent sortable columns and defines custom columns
	 *
	 * @since    2.0.0
	 * @return array
	 */
	protected function get_sortable_columns() {
        
        $sortable_columns = array(
            'title'			=> array( 'name', false ),     //true means it's already sorted
            'service'		=> array( 'service', false ),
            'type'			=> array( 'type', false ),
            'author'		=> array( 'author', false ),
            'frequency'		=> array( 'frequency', false ),
            'status'		=> array( 'status', false ),
        );
        
        return $sortable_columns;
    }
	
	/**
	 * Override parent checkbox and defines custom checkbox
	 *
	 * @since    2.0.0
	 * @return string
	 */
	protected function column_cb( $item ) {
		
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id'] );
    }
    
	/**
	 * Processes custom columns
	 *
	 * @since    2.0.0
	 * @return string
	 */
    protected function column_default( $item, $column_name ) {
		
        switch( $column_name ) {
            
            case 'title':
            	return $item['name'];
            
            case 'service':
            	
            	if( isset($this->services[$item[$column_name]]['label']) )
            		return $this->services[$item[$column_name]]['label'];
            	
            	return __( 'Service does not exist for ', 'kvp' ) . '<i>' . $item[$column_name] . '</i>';
            
            case 'type':
            	
            	if( array_key_exists( $item[$column_name], $this->source_types ) )
            		return $this->source_types[$item[$column_name]];
            	
            	return __( 'Source type not supported.', 'kvp' );
            
            case 'author':
            	$author = get_user_by( 'id', $item[$column_name] );
            	return $author->display_name;
            
            case 'categories':
				$output = array();
				
				if( empty($item['tax_input']['kvp_video_category']) )
					return;
				
				foreach($item['tax_input']['kvp_video_category'] as $key => $cat_id) {
					
					if( 0 == $cat_id )
						continue;

					$term = get_term( $cat_id, 'kvp_video_category');
					
					if( empty($term) )
						continue;
					
					$output[] = '<a href="' . get_category_link( $term->term_id ) . '" title="' . esc_attr( sprintf( __( "View all posts in %s" ), $term->name ) ) . '">' . $term->name . '</a>';
					
				}
				return join( __( ', ' ), $output );
			
			case 'publish':
				
				return ( 'publish' == $item[$column_name] ) ? __( 'Publish' ) : __( 'Draft' );
			
			case 'frequency':
				$schedules = wp_get_schedules();
				
				return $schedules[$item['schedule_freq']]['display'];
            
            case 'status':
            	
            	return ( 'active' == $item[$column_name] ) ? __( 'Active', 'kvp' ) : __( 'Inactive', 'kvp' );
            
            default:
            	do_action( 'manage_kvp_source_custom_column', $column_name, $item );
                return;
        }

    }
    
    /**
     * Creates source actions
     * @since 2.0.0
     * @param  array $item source
     * @return string source actions
     */
    protected function column_title( $item ) {

        $import_nonce	= wp_create_nonce('kvp_import_nonce');
        $run_nonce		= wp_create_nonce('kvp_run_nonce');
        $delete_nonce	= wp_create_nonce('kvp_delete_nonce');
		
		$actions = array();
		
        //Build row actions
		if ( current_user_can('edit_posts') )
			$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="' . esc_attr( __( 'Edit this source inline' ) ) . '">' . __( 'Edit', 'kvp' ) . '</a>';
		
		$actions['test hide-if-no-js'] = '<a href="' . esc_url( add_query_arg( array( 'action' => 'kvp_source_test', 'id' => $item['id'] ), 'admin-ajax.php') ) . '&width=600&height=550" class="thickbox" title="' . __( 'Source Test Results: ' ) . $item['name'] . '">' . __( 'Test', 'kvp' ) . '</a>';
		

				
		if( isset($_REQUEST['action']) && 'run_source' == $_REQUEST['action'] && isset($_REQUEST['source']) && $_REQUEST['source'] == $item['id'] ) {

			$actions['inline hide-if-no-js'] = '<span>' . __( 'Edit', 'kvp' ) . '</span>';
			$actions['run']	= '<span>' . __( 'Queued', 'kvp' ) . '</span>';
			$actions['delete']	= '<span>' . __( 'Delete', 'kvp' ) . '</span>';

		} elseif( false === $this->import_lock ) {
			
			if( 'active' == $item['status'] )
				$actions['run'] = sprintf('<a href="?post_type=kvp_video&page=%s&action=%s&source=%s&_wpnonce=%s" title="' . esc_attr( __( 'Run source' ) ) . '">' . __( 'Run', 'kvp' ) . '</a>', 'kvp-sources', 'run_source', $item['id'], $run_nonce );
			
			$actions['delete']	= sprintf('<a href="?post_type=kvp_video&page=%s&action=%s&source=%s&_wpnonce=%s" title="' . esc_attr( __( 'Delete this source' ) ) . '">' . __( 'Delete', 'kvp' ) . '</a>', 'kvp-sources', 'delete_sources', $item['id'], $delete_nonce );
			
		} else {
			
			if( $this->import_lock == $item['id'] ) {
				$actions['inline hide-if-no-js'] = '<span>' . __( 'Edit', 'kvp' ) . '</span>';
				$actions['run']	= '<span>' . __( 'Running', 'kvp' ) . '</span>';
				$actions['delete']	= '<span>' . __( 'Delete', 'kvp' ) . '</span>';
			} else {

				if( 'active' == $item['status'] )
					$actions['run'] = sprintf('<a href="?post_type=kvp_video&page=%s&action=%s&source=%s&_wpnonce=%s" title="' . esc_attr( __( 'Queue source' ) ) . '">' . __( 'Queue', 'kvp' ) . '</a>', 'kvp-sources', 'run_source', $item['id'], $run_nonce );
				
				$actions['delete']	= sprintf('<a href="?post_type=kvp_video&page=%s&action=%s&source=%s&_wpnonce=%s" title="' . esc_attr( __( 'Delete this source' ) ) . '">' . __( 'Delete', 'kvp' ) . '</a>', 'kvp-sources', 'delete_sources', $item['id'], $delete_nonce );
			}
			
		}
        
        $categories = ( empty($item['tax_input']['kvp_video_category']) ) ? array() : $item['tax_input']['kvp_video_category'];
        
		$inline  = '<div class="hidden" id="inline_' . $item['id'] . '">';
	    $inline .= '    <div class="name">' . $item['name'] . '</div>';
	    $inline .= '    <div class="service">' . $item['service'] . '</div>';
	    $inline .= '    <div class="type">' . $item['type'] . '</div>';
	    $inline .= '    <div class="items">' . implode( ', ', $item['items'] ) . '</div>';
	    $inline .= '    <div class="author">' . $item['author'] . '</div>';
		$inline .= '	<div class="tax_input" id="kvp_video_category_' . $item['id'] . '">' . implode( ',', $categories ) . '</div>';
	    $inline .= '    <div class="schedule_time">' . date( get_option('time_format'), $item['schedule_time']) . '</div>';
	    $inline .= '    <div class="schedule_freq">' . $item['schedule_freq'] . '</div>';
	    $inline .= '    <div class="limit">' . $item['limit'] . '</div>';
	    $inline .= '    <div class="publish">' . $item['publish'] . '</div>';
	    $inline .= '    <div class="status">' . $item['status'] . '</div>';
		$inline .= '</div>';

        //Return the title contents
        return sprintf( '%1$s %2$s %3$s', $item['name'], $this->row_actions($actions), $inline );
    }
    
	/**
	 * Returns bulk actions
	 *
	 * @since    2.0.0
	 * @return array
	 */
	public function get_bulk_actions() {
		
		$actions = array(
			'delete_sources'	=> __( 'Delete', 'kvp' ),
		);
		
		return $actions;
		
	}
    
	/**
	 * Returns bulk actions
	 *
	 * @since    2.0.0
	 * @return array
	 */
	public function process_bulk_actions() {
		
		switch( $this->current_action() ) {
			
			case 'connect_source':
				
				if(! current_user_can( 'edit_others_posts') || empty($_POST['_kvp_nonce']) || !wp_verify_nonce( $_POST['_kvp_nonce'], 'kvp_connect_source' ) )
					return false;
				
				$this->process_source();
				
				break;
			
			case 'run_source':
				
				if( empty($_GET['_wpnonce']) || !wp_verify_nonce( $_GET['_wpnonce'], 'kvp_run_nonce' ) )
					return false;
				
				$sources = ( isset($_REQUEST['source']) ) ? $_REQUEST['source'] : null;
    			$sources = ( is_array($sources) ) ? $sources : array($sources);
				
				
				if( empty($sources) )
					return false;
				
				foreach( $sources as $id ) {

					if( isset($this->items[$id]) ) {
						
						if( $this->import_lock == $id )
							continue;
						
						wp_unschedule_event( wp_next_scheduled( 'kvp_import_' . $id, array( $id ) ), 'kvp_import_' . $id, array( $id ) );
						wp_schedule_single_event( time() - 3600, 'kvp_import_' . $id, array( $id ) );
						
					}

				}

				break;
			
			case 'delete_sources':
				
				if( empty($_REQUEST['_wpnonce']) || ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'kvp_delete_nonce' ) && !wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-sources' ) ) )
					return false;
				
				$sources = ( isset($_REQUEST['source']) ) ? $_REQUEST['source'] : null;
    			$sources = ( is_array($sources) ) ? $sources : array($sources);
				
				if( empty($sources) )
					return false;

	        	foreach( $sources as $id ) {

	        		if( !current_user_can('delete_users') )
						break;
					
					if( false !== $this->import_lock && $this->import_lock == $id )
						continue;
							
					if( isset($this->items[$id]) ) {
						wp_unschedule_event( wp_next_scheduled( 'kvp_import_' . $id, array( $id ) ), 'kvp_import_' . $id, array( $id ) );
						unset($this->items[$id]);
						update_option( 'kvp_sources', $this->items );
					}

				}

				break;
			
		}
		
	}
	
	
	public function edit_source() {
		
		if( !check_ajax_referer( 'kvp_edit_source', 'kvp_inline_edit' ) )
			wp_die(  __( 'You are not allowed to edit sources.', 'kvp' ) );
		
		if ( !current_user_can( 'edit_others_posts') )
			wp_die( __( 'You are not allowed to edit this source.', 'kvp' ) );
		
		$status = $this->process_source();
		
		if( true !== $status )
			wp_die( __( 'There was an error saving the source.' . $status, 'kvp' ) );
		
		$this->items = get_option( 'kvp_sources' );
		$this->single_row( $this->items[$_POST['ID']] );
		
	}
	
	/**
	 * Processes source actions
	 * 
	 * @since 2.0.0
	 * @return boolean Process success
	 */
	private function process_source() {
		
		if( !current_user_can('delete_users') || ( !isset($_POST['new_source']) && !isset($_POST['edit_source']) ) )
			return $this->admin_notice( __( 'You are not allowed to edit sources.', 'kvp' ), true );
			
		$source = ( isset($_POST['new_source']) ) ? $_POST['new_source'] : $_POST['edit_source'];
		$source_id = ( isset($_POST['new_source']) ) ? kvp_unique_id( get_option( 'kvp_sources', array() ) ) : $_POST['ID'];
		
		if( isset( $_POST['edit_source'] ) && !isset( $this->items[$source_id] ) )
			return $this->admin_notice( __( 'There was a problem identifying the source.', 'kvp' ), true );
		
		// Sanitize variables
		if( isset( $source['items'] ) ) {
			$source['items'] = htmlspecialchars($source['items']);
			$source['items'] = preg_split( '/[\\s]*[,]+[\\s]*/', $source['items']);
		}
		
		$source['tax_input']['kvp_video_category'] = $_POST['tax_input']['kvp_video_category'];
		
		if( 0 < count($source['tax_input']['kvp_video_category']) )
			unset($source['tax_input']['kvp_video_category'][0]);
		
		$source['schedule_time'] = ( empty($source['schedule_time']) ) ? time() : strtotime( $source['schedule_time'] );
		
		$source['limit']	= ( isset($source['limit']) && ctype_digit($source['limit']) ) ? $source['limit'] : null;
		
		$source['publish']	= ( isset($source['publish']) ) ? $source['publish'] : 'publish';
		$source['status']	= ( isset($source['status']) ) ? $source['status'] : 'inactive';
		
		if( !isset( $this->items[$source_id] ) ) 
			$this->items[$source_id] = array( 'id' => $source_id );
		
		$this->items[$source_id] = array_merge( $this->items[$source_id], $source );
		
		$this->items[$source_id] = apply_filters( 'kvp_save_source', $this->items[$source_id] );
		
		update_option( 'kvp_sources', $this->items );
		
		return true;
		
	}
	
	/**
	 * Customizes row to include id
	 * @since 2.0.0
	 */
	public function single_row( $item ) {
		
		static $row_class = '';
		
		$row_class = ( $row_class == '' ? ' class="alternate"' : '' );
		
		echo '<tr id="source-' . $item['id'] . '"' . $row_class . '>';
		$this->single_row_columns( $item );
		echo '</tr>';
	}
	
	/**
	 * Prepares items for display
	 *
	 * @since    2.0.0
	 */
	public function prepare_items() {
		
		$per_page = 20;
		
		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		
		$this->_column_headers = array( $columns, $hidden, $sortable );
		
        $this->process_bulk_actions();
		
		$data = $this->items;
		
		function usort_reorder( $a, $b ){
            $orderby = ( !empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'name'; //If no sort, default to title
            $order = ( !empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp( $a[$orderby], $b[$orderby] ); //Determine sort order
            return ( $order==='asc' ) ? $result : -$result; //Send final sort direction to usort
        }

        usort($data, 'usort_reorder');

        $current_page = $this->get_pagenum();
        $total_items  = count( $data );

        $data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
        $this->items = $data;

        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil( $total_items / $per_page )   //WE have to calculate the total number of pages
        ) );
        
	}
	
	/**
	 * Override parent 'no items'
	 *
	 * @since    2.0.0
	 * @return string
	 */
	public function no_items() {
		
		_e( 'No sources found.', 'kvp' );
		
	}
	
	/**
	 * Provides an inline edit box
	 *
	 * @since    2.0.0
	 */
	public function inline_edit() {
		
		include 'partials/view-inline-edit.php';
		
	}
	
	/**
	 * Provides a thickbox for source testing
	 * 
	 * @since 3.0.0
	 */
	public function source_test() {
		
		include 'partials/view-source-test.php';
		
	}
	
}