<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }

final class KVP_Admin {
	
	private $settings;
	private $providers	= array();
	private $errors		= null;
	
	public function __construct() {
		
		include_once(KVP__PLUGIN_DIR . 'models/settings.php');
		$this->settings = new KVP_Settings();
		
		add_action('admin_enqueue_scripts', array($this, 'enqueue_assets') );
		add_action('admin_menu', array($this, 'add_menu_pages') );
		add_action('admin_init', array($this, 'load_dependencies') );
		add_action('delete_post', array($this, 'delete_post_meta') );
		
		do_action('kvp_init');
		
	}
	
	public function enqueue_assets() {
		
		wp_enqueue_script('post');
		
		wp_enqueue_style('kvp-admin', plugins_url( 'views/css/admin.css' , dirname(__FILE__ )) );
		
	}
	
	public function add_menu_pages() {
		global $kvp_admin_page;
		
		$menu_slug = 'kvp-sources';
		
		$kvp_admin_page = add_menu_page( __('Sources', 'kvp'), __('Video Plus', 'kvp') . $this->get_error_count(), 'import', $menu_slug, array($this, 'admin_sources') );
		
		
		$default_submenu	= array(
			array( __('Sources', 'kvp'), __('Sources', 'kvp'), 'import', 'kvp-sources', array($this, 'admin_sources') ),
			array( __('Error Log', 'kvp'), __('Error Log', 'kvp') . $this->get_error_count(), 'import', 'kvp-error-log', array($this, 'admin_error_log') ),
			array( __('Settings', 'kvp'), __('Settings', 'kvp'), 'manage_options', 'kvp-settings', array($this, 'admin_settings') ),
		);
		
		$submenu = apply_filters('kvp_submenu', $default_submenu);
		
		foreach( $submenu as $item) {
			
			if( !isset($item[4]) )
				$item[4] = '';
			
			add_submenu_page( $menu_slug, $item[0], $item[1], $item[2], $item[3], $item[4] );
		}
		
	}
	
	public function load_dependencies() {
		
		if( !class_exists( 'EDD_SL_Plugin_Updater' ) )
			include_once(KVP__PLUGIN_DIR . 'lib/EDD_SL_Plugin_Updater.php');
		
	}
	
	private function get_error_count() {
		
		if( null == $this->errors ) {
			
			$sources = get_option('kvp_sources', array());
			$count	 = 0;
			
			foreach( $sources as $source ) {
				
				if( !isset($source['import']) || !isset($source['import']['errors']) )
					continue;
				
				foreach( $source['import']['errors'] as $error )
					$count++;
				
			}
			
			$this->errors = $count;
			
		}
		
		if( 0 == $this->errors )
			return;
		
		return sprintf( '<span class="update-plugins count-%1$d" title="%2$s"><span class="update-count">%1$d</span></span>', $this->errors, __('Import Errors', 'kvp') );
		
	}
	
	public function delete_post_meta( $post_id ) {
		delete_post_meta($post_id, '_kvp');
	}
	
	public function admin_dashboard() {
		include_once(KVP__PLUGIN_DIR . 'views/admin.dashboard.php');
	}
	
	public function admin_sources() {
		
		$vars				= array_merge( array('action' => ''), $_REQUEST );
		$this->providers	= apply_filters('kvp_providers', $this->providers );
		
		switch( $vars['action'] ) {
			
			case 'audit':
			case 'import':
				include_once( KVP__PLUGIN_DIR . 'models/import.php' );
				
				$sources	= get_option('kvp_sources');
				$source_ids = ( is_array($_REQUEST['source']) ) ? $_REQUEST['source'] : array($_REQUEST['source']);
				
				include( KVP__PLUGIN_DIR . 'views/admin.import.php' );
				break;
			
			default:
				
				if( current_user_can('import') )
					add_meta_box( 'kvp-add-source', __('Add Source', 'kvp'), array($this, 'meta_box_add_source'), 'kvp_sources', 'side', 'high' );
				
				$this->add_source();
				
				include_once( KVP__PLUGIN_DIR . 'lib/class.sources_table.php' );

				$kvp_sources = new KVP_Sources_Table();
				$kvp_sources->prepare_items();
				
				$sidebar = ( current_user_can('import') ) ? ' has-right-sidebar' : '';
				
				include( KVP__PLUGIN_DIR . 'views/admin.sources.php' );
				break;
			
		}
		
	}
	
	public function admin_error_log() {
		
		$vars				= array_merge( array('action' => ''), $_REQUEST );
		$this->providers	= apply_filters('kvp_providers', $this->providers );
		
		switch( $vars['action'] ) {
			
			case 'repair':
				include_once( KVP__PLUGIN_DIR . 'models/import.php' );
				
				$sources = get_option('kvp_sources');
				$source_ids = ( empty($_REQUEST['source']) ) ? array_keys($sources) : $_REQUEST['source'];
				$source_ids = ( is_array($source_ids) ) ? $source_ids : array($source_ids);
				
				include( KVP__PLUGIN_DIR . 'views/admin.repair.php' );
				break;
			
			default:
				
				include_once( KVP__PLUGIN_DIR . 'lib/class.errors_table.php' );

				$kvp_errors = new KVP_Errors_Table();
				$kvp_errors->prepare_items();
				
				include( KVP__PLUGIN_DIR . 'views/admin.error_log.php' );
				break;
			
		}
		
	}
	
	public function admin_settings() {
		
		include_once(KVP__PLUGIN_DIR . 'views/admin.settings.php');
		
	}
	
	public function meta_box_add_source() {

		include_once ABSPATH . 'wp-admin/includes/meta-boxes.php';
		
		$provider_opts	= '';
		$category_args	= array();
		
		include_once KVP__PLUGIN_DIR . 'views/meta-box.add-source.php';
	}
	
	
	
	private function add_source() {
		
		if( !empty($_POST['_kvp_nonce']) && wp_verify_nonce( $_POST['_kvp_nonce'], 'kvp_add_source' ) ) {
			
			$new_source = $_POST['new_source'];
			$new_source['categories'] = $_POST['post_category'];
		
			if( !current_user_can('delete_users') )
				return false;
			
			if( 0 < count($new_source['categories']) )
				unset($new_source['categories'][0]);
			
			$id = uniqid();
			$sources = get_option('kvp_sources', array() );
			
			foreach( $sources as $key => $values ) {
				
				if( $values['provider'] == $new_source['provider'] && $values['username'] == $new_source['username'] )
					return __('Source already exists.', 'kvp');
				
			}
		
			$new_source = array_merge( array('ID' => $id), $new_source );
			
			$sources[$id] = $new_source;
			
			update_option('kvp_sources', $sources);
		
		}
		
	}
	
}