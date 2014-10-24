<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* Extends WP_List_Table to display the action log.
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/admin
* @author     Keiser Media <support@keisermedia.com>
*/

class KVP_Action_Log_Table extends WP_List_Table {
	
	/**
	 * Retrieves essential data
	 *
	 * @since    2.0.0
	 */
	public function __construct() {
		
		parent::__construct( array(
            'singular'  => 'item',
            'plural'    => 'items',
            'ajax'      => true
        ) );
        
		$this->services = apply_filters( 'kvp_services', array() );
        $this->accounts	= get_option( 'kvp_accounts', array() );
        $this->items	= get_option( 'kvp_action_log', array() );
		
	}
	
	/**
	 * Override parent columns and defines custom columns
	 *
	 * @since    2.0.0
	 * @return array
	 */
	public function get_columns() {
		
		$columns = array(
            'title'		=> __( 'Title', 'kvp' ),
            'message'	=> __( 'Message', 'kvp' ),
            'type'		=> __( 'Type', 'kvp' ),
            'date'		=> __( 'Date', 'kvp' ),
        );
        
        return $columns;
		
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
            'title'		=> array( 'username', false ),     //true means it's already sorted
            'type'		=> array( 'type', false ),
            'date'		=> array( 'date', true ),
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
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['ID'] );
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
            case 'message':
            case 'type':
                return $item[$column_name];
			
            case 'date':
            	return date_i18n( get_option('date_format') . ' ' . get_option('time_format' ), $item[$column_name] );

            default:
            	
                return print_r( $item, true );
        }

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
		
		$data = $this->items;
		
		function usort_reorder( $a, $b ){
            $orderby = ( !empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'date'; //If no sort, default to title
            $order = ( !empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
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
		
		_e( 'Log is empty.', 'kvp' );
		
	}
	
}