<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }

final class KVP_Admin {
	
	private $settings;
	private $providers	= array();
	private $errors		= null;
	private $table		= null;
	
	public function __construct() {
		
		include_once(KVP__PLUGIN_DIR . 'models/settings.php');
		$this->settings = new KVP_Settings();
		
		if( isset($_GET['page']) && in_array($_GET['page'], array('kvp-sources') ) )
			add_action('admin_enqueue_scripts', array($this, 'enqueue_assets') );
		
		add_action('wp_ajax_kvp_inline_save', array($this, 'edit_source_ajax') );
		
		add_action('admin_menu', array($this, 'add_menu_pages') );
		add_action('admin_init', array($this, 'load_dependencies'), 9 );
		add_action('delete_post', array($this, 'delete_post_meta') );
		
		do_action('kvp_init');
		
	}
	
	public function enqueue_assets() {
		
		wp_enqueue_script('post');
		wp_enqueue_script('kvp-inline-edit', plugins_url( 'assets/js/inline-edit.js' , dirname(__FILE__ ) ), array('jquery'), null, true );
		
		wp_enqueue_style('kvp-admin', plugins_url( 'views/css/admin.css' , dirname(__FILE__ )) );
		
	}
	
	public function add_menu_pages() {
		global $kvp_admin_page;
		
		$menu_slug = 'kvp-sources';
		
		$kvp_admin_page = add_menu_page( __('Sources', 'kvp'), __('Video Plus', 'kvp') . $this->get_error_count(), 'import', $menu_slug, array($this, 'sources_page') );
		
		
		$default_submenu	= array(
			array( __('Sources', 'kvp'), __('Sources', 'kvp'), 'import', 'kvp-sources', array($this, 'sources_page') ),
			array( __('Import Log', 'kvp'), __('Import Log', 'kvp'), 'import', 'kvp-import-log', array($this, 'admin_import_log') ),
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
		
		if( !class_exists( 'KVP_License' ) )
			include_once(KVP__PLUGIN_DIR . 'models/updater.php');
		
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
	
	public function dashboard_page() {
		include_once(KVP__PLUGIN_DIR . 'views/admin.dashboard.php');
	}
	
	public function sources_page() {

		$this->providers	= apply_filters('kvp_providers', $this->providers );
		
		if( current_user_can('import') )
			add_meta_box( 'kvp-add-source', __('Add Source', 'kvp'), array($this, 'meta_box_add_source'), 'kvp_sources', 'side', 'high' );
	
		include_once( KVP__PLUGIN_DIR . 'lib/class.sources_table.php' );
	
		$sidebar = ( current_user_can('import') ) ? ' columns-2' : '';
	
		include( KVP__PLUGIN_DIR . 'views/admin.sources.php' );

	}
	
	public function edit_source_ajax() {
		
		include_once( KVP__PLUGIN_DIR . 'lib/class.sources_table.php' );
		ob_clean();
		$kvp_sources = new KVP_Sources_Table();
		$kvp_sources->edit_source();
		
	}
	
	public function admin_import_log() {
		
		$vars				= array_merge( array('action' => ''), $_REQUEST );
		$this->providers	= apply_filters('kvp_providers', $this->providers );
		
		include_once( KVP__PLUGIN_DIR . 'lib/class.import_log_table.php' );
		include( KVP__PLUGIN_DIR . 'views/admin.import_log.php' );
		
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
	
}