<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* Extends WP_List_Table to display the accounts.
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/admin
* @author     Keiser Media <support@keisermedia.com>
*/

class KVP_Accounts_Table extends WP_List_Table {
	
	/**
	 * Retrieves essential data
	 *
	 * @since    2.0.0
	 */
	public function __construct() {
		
		parent::__construct( array(
            'singular'  => 'account',
            'plural'    => 'accounts',
            'ajax'      => true
        ) );
        
		$this->services	= apply_filters('kvp_services', array() );
        $this->items	= get_option('kvp_accounts', array() );
		
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
            'title'			=> __( 'Username', 'kvp' ),
            'service'		=> __( 'Service', 'kvp' ),
            'author'		=> __( 'Author', 'kvp' ),
            'categories'	=> __( 'Categories', 'kvp' ),
            'status'		=> __( 'Status', 'kvp' ),
        );
        
        return apply_filters( 'manage_kvp_account_columns', $columns );
		
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
            'service'	=> array( 'service', false ),
            'author'	=> array( 'author', false ), 
            'status'	=> array( 'status', false ),
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
            	return $item['username'];
            
            case 'service':
            	
            	if( isset($this->services[$item[$column_name]]['label']) )
            		return $this->services[$item[$column_name]]['label'];
            	
            	return __( 'Service does not exist for ', 'kvp' ) . '<i>' . $item[$column_name] . '</i>';
            
            case 'author':
            	$author = get_user_by( 'id', $item[$column_name] );
            	return $author->display_name;
            
            case 'categories':
				$output = array();

				foreach($item[$column_name] as $key => $cat_id) {
					
					if( 0 == $cat_id )
						continue;

					$term = get_term( $cat_id, 'category');
					$output[] = '<a href="' . get_category_link( $cat_id ) . '" title="' . esc_attr( sprintf( __( "View all posts in %s" ), get_the_category_by_ID((string)$cat_id) ) ) . '">' . get_the_category_by_ID((string)$cat_id) . '</a>';
					
				}
				return join( __( ', ' ), $output );
            
            case 'status':
            	$service = 'KVP_' . str_replace( ' ', '_', $this->services[$item['service']]['label']) . '_Client';
            	$service = new $service( $item );
            	return $service->check_status();
            
            default:
            	do_action( 'manage_kvp_account_custom_column', $column_name, $item );
                return;
        }

    }
    
    /**
     * Creates account actions
     * @since 2.0.0
     * @param  array $item Account
     * @return string Account actions
     */
    protected function column_title( $item ) {

        $import_nonce = wp_create_nonce('kvp_import_nonce');
        $delete_nonce = wp_create_nonce('kvp_delete_nonce');
		
		$actions = array();
		
        //Build row actions
        if( 'locked' !== get_transient('kvp_' . $item['ID'] . '_lock') ) {
			if ( current_user_can('edit_posts') )
				$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="' . esc_attr( __( 'Edit this item inline' ) ) . '">' . __( 'Edit', 'kvp' ) . '</a>';
				
			$actions['delete']	= sprintf('<a href="?page=%s&action=%s&account=%s&_wpnonce=%s">Delete</a>', 'kvp-accounts', 'delete_accounts', $item['ID'], $delete_nonce );
			
		}
        
		$inline  = '<div class="hidden" id="inline_' . $item['ID'] . '">';
	    $inline .= '    <div class="username">' . $item['username'] . '</div>';
	    $inline .= '    <div class="service">' . $item['service'] . '</div>';
	    $inline .= '    <div class="author">' . $item['author'] . '</div>';
	    
	    if( isset($this->services[ $item['service'] ]['features']) ) {
		    
		    if( in_array('developer_key', $this->services[ $item['service'] ]['features']) )
		    	$inline .= '    <div class="developer_key">' . $item['developer_key'] . '</div>';
		    
		    
			if( in_array('oauth', $this->services[ $item['service'] ]['features']) ) {
		    	$inline .= '    <div class="oauth_id">' . $item['oauth_id'] . '</div>';
				$inline .= '    <div class="oauth_secret">' . $item['oauth_secret'] . '</div>';
			}
		
		}
		
		$inline .= '	<div class="category" id="category_' . $item['ID'] . '">' . implode( ',', $item['categories'] ) . '</div>';
		
		$ext_status = isset($item['ext_status']) ? $item['ext_status'] : array( 'video' => 'active' );
		
		$inline .= '	<div class="ext_status">' . json_encode( $ext_status ) . '</div>';
		
	    //$inline .= '    <div class="title_filter">' . $item['title_filter'] . '</div>\r\n';
	    //$inline .= '    <div class="content_filter">' . $item['content_filter'] . '</div>\r\n';
	    //$inline .= '    <div class="tag_filter">' . $item['tag_filter'] . '</div>\r\n';
		$inline .= '</div>';

        //Return the title contents
        return sprintf( '%1$s %2$s %3$s', $item['username'], $this->row_actions($actions), $inline );
    }
    
	/**
	 * Returns bulk actions
	 *
	 * @since    2.0.0
	 * @return array
	 */
	public function get_bulk_actions() {
		
		$actions =array(
			'delete_accounts'	=> __( 'Delete', 'kvp' ),
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
			
			case 'connect_account':
				if(! current_user_can( 'edit_others_posts') || empty($_POST['_kvp_nonce']) || !wp_verify_nonce( $_POST['_kvp_nonce'], 'kvp_connect_account' ) )
					return false;
				
				$this->process_account();
				
				break;
			
			case 'delete_accounts':
				
				$accounts = ( isset($_REQUEST['account']) ) ? $_REQUEST['account'] : null;
    			$accounts = ( is_array($accounts) ) ? $accounts : array($accounts);
				
				
				if( empty($accounts) )
					return false;

	        	foreach( $accounts as $id ) {

	        		if( !current_user_can('delete_users') )
						break;

					if( isset($this->items[$id]) ) {
						unset($this->items[$id]);
						update_option( 'kvp_accounts', $this->items );
					}

				}

				break;
			
		}
		
	}
	
	
	public function edit_account() {
		
		if( !check_ajax_referer( 'kvp_edit_account', 'kvp_inline_edit' ) )
			wp_die(  __( 'You are not allowed to edit accounts.', 'kvp' ) );
		
		if ( !current_user_can( 'edit_others_posts') )
			wp_die( __( 'You are not allowed to edit this account.', 'kvp' ) );
		
		$status = $this->process_account();
		
		if( true !== $status )
			wp_die( __( 'There was an error saving the account.' . $status, 'kvp' ) );
		
		$this->items = get_option( 'kvp_accounts' );
		$this->single_row( $this->items[$_POST['ID']] );
		
		wp_die('Here');
		
	}
	
	/**
	 * Processes account actions
	 * 
	 * @since 2.0.0
	 * @return boolean Process success
	 */
	private function process_account() {
		
		if( !current_user_can('delete_users') || ( !isset($_POST['new_account']) && !isset($_POST['edit_account']) ) )
			return $this->admin_notice( __( 'You are not allowed to edit accounts.', 'kvp' ), true );
			
		if( isset($_POST['edit_account']) && !isset($_POST['ID']) )
			return $this->admin_notice( __( 'There was a problem identifying the account.', 'kvp' ), true );
		
		$account = ( isset($_POST['new_account']) ) ? array_merge( array( 'ID' => uniqid() ), $_POST['new_account'] ) : array_merge( array( 'ID' => $_POST['ID'] ), $_POST['edit_account'] );
		$account['categories'] = $_POST['post_category'];
		$account['ext_status'] = ( isset($_POST['ext_status']) ) ? $_POST['ext_status'] : array( 'video' => 'active' );
		$account['developer_key'] = isset($account['developer_key']) ? $account['developer_key'] : '';
		
		if( 0 < count($account['categories']) )
			unset($account['categories'][0]);
			
		foreach( $this->items as $key => $values ) {

			if( isset($account['service']) ) {
				
				if( $values['ID'] !== $account['ID'] && $values['service'] == $account['service'] && $values['username'] == $account['username'] )
					return $this->admin_notice( __('Account already exists.', 'kvp'), true );
					
			} else {
				
				if( $values['ID'] == $account['ID'] && $values['username'] !== $account['username'] )
					return $this->admin_notice( __('Account already exists.', 'kvp'), true );
				
				if( $values['ID'] == $account['ID'] )
					$account['service'] = $values['service'];
				
			}

		}
		
		if( !in_array( 'developer_key', $this->services[ $account['service'] ]['features'] ) )
			unset($account['developer_key']);
		
		if( !in_array( 'oauth', $this->services[ $account['service'] ]['features'] ) ) {
			unset($account['oauth_id']);
			unset($account['oauth_secret']);
		}
		
		$account = apply_filters( 'kvp_account_save', $account );
		
		if( !isset($accounts[$account['ID']]) )
			$accounts[$account['ID']] = array();
		
		$this->items[$account['ID']] = array_merge( $accounts[$account['ID']], $account );
		
		update_option( 'kvp_accounts', $this->items );
		
		return true;
		
	}
	
	/**
	 * Customizes row to include id
	 * @since 2.0.0
	 */
	public function single_row( $item ) {
		
		static $row_class = '';
		
		$row_class = ( $row_class == '' ? ' class="alternate"' : '' );

		echo '<tr id="account-' . $item['ID'] . '"' . $row_class . '>';
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
            $orderby = ( !empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'username'; //If no sort, default to title
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
		
		_e( 'No accounts found.', 'kvp' );
		
	}
	
	/**
	 * Provides an inline edit box
	 *
	 * @since    2.0.0
	 */
	public function inline_edit() {
		
		include 'partials/view-inline-edit.php';
		
	}
	
}