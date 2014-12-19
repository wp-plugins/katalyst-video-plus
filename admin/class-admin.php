<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* The dashboard-specific functionality of the plugin.
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/admin
* @author     Keiser Media <support@keisermedia.com>
*/

class Katalyst_Video_Plus_Admin {

	/**
	 * The readable name of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string The ID of this plugin.
	 */
	private $name;
	
	/**
	 * The ID of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string The ID of this plugin.
	 */
	private $slug;

	/**
	 * The version of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string  The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 * @var      string    $slug       The slug of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $name, $slug, $version ) {
		
		$this->name = $name;
		$this->slug = $slug;
		$this->version = $version;
		
		$this->load_dependencies();
		
	}
	
	/**
	 * Load the required dependencies.
	 * 
	 * @since    2.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		
		require_once plugin_dir_path( __FILE__ ) . 'class-meta-boxes.php';

	}

	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->slug, plugin_dir_url( dirname( __FILE__ ) ) . '/assets/css/kvp-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->slug, plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/kvp-admin.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->slug . '-chart', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/chart.min.js', array( 'jquery' ), '1.0.1-beta.4', false );
		
		if( isset($_GET['page']) && 'kvp-accounts' == $_GET['page'] ) {
			
			wp_enqueue_script( 'post' );
			wp_enqueue_script( 'kvp-inline-edit', plugins_url( 'assets/js/inline-edit.js' , dirname(__FILE__ ) ), array('jquery'), null, true );
			
		}

	}
	
	/**
	 * Registers top-level page and all sub pages.
	 *
	 * @since    2.0.0
	 */
	public function setup_menu() {
		
		$menu_slug = 'kvp-dashboard';
		
		$admin_page = add_menu_page( __( 'KVP Dashboard', 'kvp' ), 'Video Plus' . $this->get_error_count(), 'import', $menu_slug, array( $this, 'view_dashboard' ), 'dashicons-video-alt3', 76 );
		
		$default_submenu = array(
			array( __( 'Dashboard', 'kvp' ), __( 'Dashboard', 'kvp' ), 'import', $menu_slug, array( $this, 'view_dashboard' ) ),
			array( __( 'Accounts', 'kvp' ), __( 'Accounts', 'kvp' ) . $this->get_error_count('accounts'), 'import', 'kvp-accounts', array( $this, 'view_accounts' ) ),
			array( __( 'Log', 'kvp' ), __( 'Log', 'kvp' ) . $this->get_error_count('log'), 'import', 'kvp-log', array( $this, 'view_log' ) ),
			array( __( 'Settings', 'kvp' ), __( 'Settings', 'kvp' ), 'manage_options', 'kvp-settings', array( $this, 'view_settings' ) ),
		);
		
		$submenu = apply_filters( 'kvp_admin_submenu', $default_submenu );
		
		foreach ( $submenu as $item )
			add_submenu_page( $menu_slug, $item[0], $item[1], $item[2], $item[3], $item[4] );
		
	}
	
	/**
	 * Registers top-level page and all sub pages.
	 *
	 * @since    2.0.0
	 */
	public function get_error_count( $section = false ) {
		
		$errors = 0;
		
		// Check accounts
		if( in_array( $section, array( false, 'accounts' ) ) ) {
			
			$accounts = get_option( 'kvp_accounts', array() );
			$services = apply_filters( 'kvp_services', array() );
			
			foreach( $accounts as $account ) {
				
				if( !isset($services[$account['service']]) ) {
					++$errors;
					continue;
				}
		        	
	        	$service = 'KVP_' . str_replace( ' ', '_', $services[$account['service']]['label']) . '_Client';
	        	
	        	if( class_exists($service) ) {
	        		$service = new $service( $account );
	        		$status = $service->check_status();
	        		
	        		if( 'Connected' !== $status ) {
						++$errors;
						continue;
	        		}
	        	}
		        
		    }
		    
		}
		
		if( 0 == $errors )
			return;
		
		return sprintf( '<span class="update-plugins count-%1$d" title="%2$s"><span class="update-count">%1$d</span></span>', $errors, __('Import Errors', 'kvp') );

	}
	
	/**
	 * Displays Dashboard
	 *
	 * @since    2.0.0
	 */
	public function view_dashboard() {
		
		include 'partials/view-dashboard.php';
		
	}
	
	/**
	 * Displays accounts
	 *
	 * @since    2.0.0
	 */
	public function view_accounts() {
		
		if( current_user_can( 'import' ) ) {
		
			include_once 'class-accounts-table.php';
			include 'partials/view-accounts.php';
			
		}
		
	}
	
	/**
	 * Displays log of events occuring within KVP
	 *
	 * @since    2.0.0
	 */
	public function view_log() {
		
		include_once 'class-action-log-table.php';
		include 'partials/view-log.php';
		
	}
	
	/**
	 * Displays KVP settings
	 *
	 * @since    2.0.0
	 */
	public function view_settings() {
		
		if( current_user_can( 'manage_options' ) )
			include 'partials/view-settings.php';
		
	}
	
	/**
	 * Registers admin meta boxes.
	 *
	 * @since    2.0.0
	 */
	public function setup_meta_boxes() {
		
		$default_meta_boxes = array(
			array( 'kvp-statistics', __( 'Operation Statistics', 'kvp' ), array( new Katalyst_Video_Plus_Meta_Boxes, 'statistics' ), 'kvp_dashboard', 'normal', 'default', null ),
			array( 'kvp-system-info', __( 'Katalyst Video Plus Status', 'kvp' ), array( new Katalyst_Video_Plus_Meta_Boxes, 'system_info' ), 'kvp_dashboard', 'side', 'default', null ),
			array( 'kvp-rate-us', __( 'Rate Katalyst Video Plus', 'kvp' ), array( new Katalyst_Video_Plus_Meta_Boxes, 'rate_us' ), 'kvp_dashboard', 'side', 'default', null ),
			//array( 'kvp-extensions', __( 'Random Extension', 'kvp' ), array( new Katalyst_Video_Plus_Meta_Boxes, 'extensions' ), 'kvp_dashboard', 'side', 'default', null ),
			array( 'kvp-connect-account', __( 'Connect Account', 'kvp' ), array( new Katalyst_Video_Plus_Meta_Boxes, 'connect_account' ), 'kvp_accounts', 'side', 'default', null ),
		);
		
		$meta_boxes = apply_filters( 'kvp_meta_boxes', $default_meta_boxes );
		
		foreach ( $meta_boxes as $meta_box )
			add_meta_box( $meta_box[0], $meta_box[1], $meta_box[2], $meta_box[3], $meta_box[4], $meta_box[5], $meta_box[6] );
		
	}
	
	/**
	 * Ajax function for editing account.
	 *
	 * @since    2.0.0
	 */
	public function edit_account_ajax() {
		
		include_once 'class-accounts-table.php';
		
		ob_clean();
		
		$accounts = new KVP_Accounts_Table();
		$accounts->edit_account();
		
	}

}
