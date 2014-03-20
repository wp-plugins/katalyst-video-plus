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
		
		$default_arr = array(
			'username'			=> null,
			'author'			=> null,
			'api_key'			=> null,
			'oauth_id'			=> null,
			'oauth_secret'		=> null,
			'title_filter'		=> null,
			'content_filter'	=> null,
			'tag_filter' 		=> null,
			
		);
		
		foreach( $this->items as $id => $info ) {
			
			$this->items[$id] = array_merge( $default_arr, $info );
			
		}
		

    }
    
    private function admin_notice( $message, $error = false ) {
	    
	    $type = ( false === $error ) ? 'updated' : 'error';
	    
	    echo '<div class="' . $type . '"><p>' . $message . '</p></div>';
	    
	    return false;
	    
    }

    protected function column_default( $item, $column_name ) {

        switch( $column_name ) {
            case 'username':
            	echo get_transient('kvp_' . $this->source['ID'] . '_lock');
            	echo get_transient('kvp_' . $this->source['ID'] . '_halt');
                return $item[$column_name];

            case 'provider':
            	if( !isset($this->providers[$item[$column_name]]['title']) )
            		return __('Provider not installed.', 'kvp');

            	return $this->providers[$item[$column_name]]['title'];

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
			
			case 'status':
				if( 'locked' === get_transient('kvp_' . $item['ID'] . '_lock') )
					return __('Importing', 'kvp');
				
				if( !isset($item['import']['last_import']) )
					return __('Initial Import Pending', 'kvp');
				
				return __('Import Completed', 'kvp');
					
			
            case 'last_import':
            	if( isset($item['import'][$column_name]) )
            		return date(get_option('date_format') . ' ' . get_option('time_format'), $item['import'][$column_name]);

            	return __('N/A', 'kvp');
            
            case 'next_import':
            	return get_date_from_gmt( date( get_option('date_format') . ' ' . get_option('time_format'), wp_next_scheduled('kvp_import_cron') ), get_option('date_format') . ' ' . get_option('time_format') );

            default:
                return print_r($item, true);
        }

    }

    protected function column_title( $item ) {

        $import_nonce = wp_create_nonce('kvp_import_nonce');
        $delete_nonce = wp_create_nonce('kvp_delete_nonce');
		
		$actions = array();
		
        //Build row actions
        if( 'locked' !== get_transient('kvp_' . $item['ID'] . '_lock') ) {
			if ( current_user_can('edit_posts') )
				$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="' . esc_attr( __( 'Edit this item inline' ) ) . '">' . __( 'Edit', 'kvp' ) . '</a>';
				
			$actions['import']	= sprintf('<a href="?page=%s&action=%s&source=%s&_wpnonce=%s">%s</a>', 'kvp-sources', 'import', $item['ID'], $import_nonce, __('Import', 'kvp') );
			$actions['delete']	= sprintf('<a href="?page=%s&action=%s&source=%s&_wpnonce=%s">Delete</a>', 'kvp-sources', 'delete', $item['ID'], $delete_nonce );
			
		}
        
		$inline  = '<div class="hidden" id="inline_' . $item['ID'] . '">';
	    $inline .= '    <div class="username">' . $item['username'] . '</div>';
	    $inline .= '    <div class="provider">' . $item['provider'] . '</div>';
	    $inline .= '    <div class="author">' . $item['author'] . '</div>';
	    
	    if( isset($this->providers[ $item['provider'] ]['features']) ) {
		    
		    if( in_array('api_key', $this->providers[ $item['provider'] ]['features']) )
		    	$inline .= '    <div class="api_key">' . $item['api_key'] . '</div>';
		    
		    
			if( in_array('oauth', $this->providers[ $item['provider'] ]['features']) ) {
		    	$inline .= '    <div class="oauth_id">' . $item['oauth_id'] . '</div>';
				$inline .= '    <div class="oauth_secret">' . $item['oauth_secret'] . '</div>';
			}
		
		}
		
		$inline .= '	<div class="category" id="category_' . $item['ID'] . '">' . implode( ',', $item['categories'] ) . '</div>';
		
	    //$inline .= '    <div class="title_filter">' . $item['title_filter'] . '</div>\r\n';
	    //$inline .= '    <div class="content_filter">' . $item['content_filter'] . '</div>\r\n';
	    //$inline .= '    <div class="tag_filter">' . $item['tag_filter'] . '</div>\r\n';
		$inline .= '</div>';

        //Return the title contents
        return sprintf( '%1$s %2$s %3$s', $item['username'], $this->row_actions($actions), $inline );
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
			'status'		=> __('Status', 'kvp'),
            'last_import'	=> __('Last Import', 'kvp'),
            'next_import'	=> __('Next Import', 'kvp'),
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
		
	    $all_sources = get_option('kvp_sources', array() );
    	
    	$sources = ( isset($_REQUEST['source']) ) ? $_REQUEST['source'] : null;
    	$sources = ( is_array($sources) ) ? $sources : array($sources);
		
        switch( $this->current_action() ) {
			
			case 'add':
				$this->add_source();
				break;
			
	        case 'delete':

				if( empty($sources) )
					return false;

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
			
			case 'import':

				if( empty($sources) )
					return false;
				
				foreach( $sources as $id )
					wp_reschedule_event( time(), 'hourly', 'kvp_' . $id . '_cron', array('id' => $id));
				
				break;
				
        }

    }
	
	private function add_source() {
		
		if( empty($_POST['_kvp_nonce']) || !wp_verify_nonce( $_POST['_kvp_nonce'], 'kvp_add_source' ) )
			return false;
		
		if ( ! current_user_can( 'edit_others_posts') )
			return false;
		
		$this->process_source();
		
	}
	
	public function edit_source() {
		
		if( !check_ajax_referer( 'kvp_edit_source', 'kvp_inline_edit' ) )
			wp_die(  __( 'You are not allowed to edit sources.', 'kvp' ) );
		
		if ( !current_user_can( 'edit_others_posts') )
			wp_die( __( 'You are not allowed to edit this source.', 'kvp' ) );
		
		$status = $this->process_source();
		echo $status;
		if( true !== $status )
			wp_die( __( 'There was an error saving the source.', 'kvp' ) );
		
		$sources = get_option('kvp_sources');
		
		$this->single_row( $sources[$_POST['ID']] );
		
		wp_die();
		
	}
    
    private function process_source() {
		
		if( !current_user_can('delete_users') )
			return $this->admin_notice( __( 'You are not allowed to edit sources.', 'kvp' ), true );
		
		if( !isset($_POST['new_source']) && !isset($_POST['edit_source']) )
			return $this->admin_notice( __( 'You are not allowed to edit sources.', 'kvp' ), true );
			
		if( isset($_POST['edit_source']) && !isset($_POST['ID']) )
			return $this->admin_notice( __( 'There was a problem identifying the source.', 'kvp' ), true ); 
		
		$this->providers = apply_filters('kvp_providers', $this->providers );
		
		
		$source = ( isset($_POST['new_source']) ) ? array_merge( array( 'ID' => uniqid() ), $_POST['new_source'] ) : array_merge( array( 'ID' => $_POST['ID'] ), $_POST['edit_source'] );
		$source['categories'] = $_POST['post_category'];

		if( 0 < count($source['categories']) )
			unset($source['categories'][0]);
		
		
		$sources = get_option('kvp_sources', array() );
		
		foreach( $sources as $key => $values ) {

			if( isset($source['provider']) ) {
				
				if( $values['ID'] !== $source['ID'] && $values['provider'] == $source['provider'] && $values['username'] == $source['username'] )
					return $this->admin_notice( __('Source already exists.', 'kvp'), true );
					
			} else {
				
				if( $values['ID'] == $source['ID'] && $values['username'] !== $source['username'] )
					return $this->admin_notice( __('Source already exists.', 'kvp'), true );
				
				if( $values['ID'] == $source['ID'] )
					$source['provider'] = $values['provider'];
				
			}

		}
		
		if( !in_array( 'api_key', $this->providers[ $source['provider'] ]['features'] ) )
			unset($source['api_key']);
		
		if( !in_array( 'oauth', $this->providers[ $source['provider'] ]['features'] ) ) {
			unset($source['oauth_id']);
			unset($source['oauth_secret']);
		}
		
		if( !isset($sources[$source['ID']]) )
			$sources[$source['ID']] = array();
		
		$sources[$source['ID']] = array_merge( $sources[$source['ID']], $source );
		
		update_option('kvp_sources', $sources);
		return true;

	}

    public function extra_tablenav( $which ) {
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

		echo '<tr id="source-' . $item['ID'] . '"' . $row_class . '>';
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

        function usort_reorder( $a, $b ){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'ID'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
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
		_e('No sources found.', 'kvp');
	}

	public function inline_edit() {

        $screen = $this->screen;

        $post = get_default_post_to_edit( $screen->post_type );
        $post_type_object = get_post_type_object( $screen->post_type );

        $taxonomy_names = get_object_taxonomies( 'post' );
        $hierarchical_taxonomies = array();
        $flat_taxonomies = array();
        foreach ( $taxonomy_names as $taxonomy_name ) {
                $taxonomy = get_taxonomy( $taxonomy_name );

                if ( !$taxonomy->show_ui )
                        continue;

                if ( $taxonomy->hierarchical )
                        $hierarchical_taxonomies[] = $taxonomy;
                else
                        $flat_taxonomies[] = $taxonomy;
        }

        $can_publish = current_user_can( 'publish_posts' );
        $core_columns = array( 'cb' => true, 'date' => true, 'title' => true, 'categories' => true, 'tags' => true, 'comments' => true, 'author' => true );

?>

<form method="get" action=""><table style="display: none"><tbody id="inlineedit">
        <tr id="inline-edit" class="inline-edit-row inline-edit-row-post inline-edit-source quick-edit-row quick-edit-row-post inline-edit-source" style="display: none">
        <td colspan="<?php echo $this->get_column_count(); ?>" class="colspanchange">

        <fieldset class="inline-edit-col-left"><div class="inline-edit-col">
                <h4><?php _e( 'Edit' ); ?></h4>
                <label>
                        <span class="title"><?php _e('Username', 'kvp'); ?></span>
                        <span class="input-text-wrap"><input type="text" name="edit_source[username]" class="ptitle" value="" /></span>
                </label>

                <?php
                $authors_dropdown = '';

                if ( is_super_admin() || current_user_can( 'edit_others_posts' ) ) :
                        $users_opt = array(
                                'hide_if_only_one_author' => false,
                                'who' => 'authors',
                                'name' => 'edit_source[author]',
                                'class'=> 'authors',
                                'multi' => 1,
                                'echo' => 0
                        );

                        if ( $authors = wp_dropdown_users( $users_opt ) ) :
                                $authors_dropdown  = '<label class="inline-edit-author">';
                                $authors_dropdown .= '<span class="title">' . __( 'Author' ) . '</span>';
                                $authors_dropdown .= $authors;
                                $authors_dropdown .= '</label>';
                        endif;
                endif; // authors

				echo $authors_dropdown; ?>
				
				<label>
                        <span class="title"><?php _e('API Key', 'kvp'); ?></span>
                        <span class="input-text-wrap"><input type="text" name="edit_source[api_key]" class="api_key" value="" /></span>
                </label>
				<label>
                        <span class="title"><?php _e('OAuth ID', 'kvp'); ?></span>
                        <span class="input-text-wrap"><input type="text" name="edit_source[oauth_id]" class="api_key" value="" /></span>
                        <span class="title"><?php _e('OAuth Secret', 'kvp'); ?></span>
                        <span class="input-text-wrap"><input type="text" name="edit_source[oauth_secret]" class="api_key" value="" /></span>
                </label>
        </div></fieldset>

<?php if ( count( $hierarchical_taxonomies ) ) : ?>

        <fieldset class="inline-edit-col-center inline-edit-categories"><div class="inline-edit-col">

<?php foreach ( $hierarchical_taxonomies as $taxonomy ) : ?>

                <span class="title inline-edit-categories-label"><?php echo esc_html( $taxonomy->labels->name ) ?></span>
                <input type="hidden" name="post_category[]" value="0" />
                <ul class="cat-checklist <?php echo esc_attr( $taxonomy->name )?>-checklist">
                        <?php wp_terms_checklist( null ) ?>
                </ul>

<?php endforeach; //$hierarchical_taxonomies as $taxonomy ?>

        </div></fieldset>

<?php endif; // count( $hierarchical_taxonomies ) ?>
<!--
        <fieldset class="inline-edit-col-right"><div class="inline-edit-col">

                <label class="inline-title-filter">
                    <span class="title"><?php _e('Title Filter', 'kvp'); ?></span>
                    <textarea cols="22" rows="1" name="edit_source[title_filter]" class="title_filter"></textarea>
                </label>
                <label class="inline-content-filter">
                    <span class="title"><?php _e('Content Filter', 'kvp'); ?></span>
                    <textarea cols="22" rows="1" name="edit_source[content_filter]" class="content_filter"></textarea>
                </label>
                <label class="inline-tags-filter">
                    <span class="title"><?php _e('Tags Filter', 'kvp'); ?></span>
                    <textarea cols="22" rows="1" name="edit_source[tags_filter]" class="tags_filter"></textarea>
                </label>


                <div class="inline-edit-group">
                	<label class="inline-edit-status alignleft">
                    </label>
                </div>

        </div></fieldset> -->

<?php
        list( $columns ) = $this->get_column_info();

        foreach ( $columns as $column_name => $column_display_name ) {
                if ( isset( $core_columns[$column_name] ) )
                        continue;
                do_action('kvp_edit_box', $column_name);
        }
?>
        <p class="submit inline-edit-save">
                <a accesskey="c" href="#inline-edit" class="button-secondary cancel alignleft"><?php _e( 'Cancel' ); ?></a>
                <?php wp_nonce_field( 'kvp_edit_source', 'kvp_inline_edit', false ); ?>
                        <a accesskey="s" href="#inline-edit" class="button-primary save alignright"><?php _e( 'Update' ); ?></a>
                        <span class="spinner"></span>
                <span class="error" style="display:none"></span>
                <br class="clear" />
        </p>
        </td></tr>
        </tbody></table></form>
<?php
}

}