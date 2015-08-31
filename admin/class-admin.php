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
	 * The slug of this plugin.
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
	 * Add inline style to admin head.
	 *
	 * @since    3.1.2
	 */
	public function admin_head() {

		if( isset($_GET['post_type']) && 'kvp_video' == $_GET['post_type'] ) {

			$enable_new_posts = apply_filters( 'kvp_enable_new_posts', false );
			
			if( true != $enable_new_posts )
				echo '<style type="text/css"> .post-type-kvp_video .wrap .add-new-h2 { display: none; } </style>';
			
		}

	}

	/**
	 * Disables new KVP posts.
	 *
	 * @since    3.1.2
	 */
	public function disable_new_posts() {

		$enable_new_posts = apply_filters( 'kvp_enable_new_posts', false );
		
		if( true == $enable_new_posts )
			return;

		global $submenu;

		unset($submenu['edit.php?post_type=kvp_video'][10]);

	}

	/**
	 * Disables new KVP posts in Admin Menu.
	 *
	 * @since    3.2.0
	 */
	public function disable_admin_bar_new_posts() {

		$enable_new_posts = apply_filters( 'kvp_enable_new_posts', false );
		
		if( true == $enable_new_posts )
			return;

		global $wp_admin_bar;

		$wp_admin_bar->remove_node('new-kvp_video');

	}

	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->slug, plugin_dir_url( dirname( __FILE__ ) ) . '/assets/css/kvp-admin.css', array(), $this->version, 'all' );
		
		if( isset($_GET['page']) && 'kvp-sources' == $_GET['page'] ) {
			
			wp_enqueue_style( 'jquery-timepicker', plugin_dir_url( dirname( __FILE__ ) ) . '/assets/css/jquery.timepicker.css', array(), '1.6.0', 'all' );
			
		}
		
	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->slug, plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/kvp-admin.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->slug . '-chart', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/chart.min.js', array( 'jquery' ), '1.0.1-beta.4', false );
		
		if( isset($_GET['page']) && 'kvp-sources' == $_GET['page'] ) {
			
			wp_enqueue_script( 'post' );
			wp_enqueue_script( 'kvp-inline-edit', plugins_url( 'assets/js/inline-edit.js' , dirname(__FILE__ ) ), array('jquery'), null, true );
			wp_enqueue_script( 'jquery-timepicker', plugins_url( 'assets/js/jquery.timepicker.min.js' , dirname(__FILE__ ) ), array('jquery'), '1.6.0', true );
			
		}

	}
	
	/**
	 * Registers top-level page and all sub pages.
	 *
	 * @since    2.0.0
	 */
	public function setup_menu() {
		
		add_dashboard_page( __( 'Welcome to Katalyst Video Plus', 'kvp' ), __( 'Welcome to Katalyst Video Plus', 'kvp' ), 'read', 'kvp-about', array( $this, 'about_screen' ) );
		
		$menu_slug = 'edit.php?post_type=kvp_video';
		
		$default_submenu = array(
			array( __( 'Sources', 'kvp' ), __( 'Sources', 'kvp' ), 'import', 'kvp-sources', array( $this, 'view_sources' ) ),
			array( __( 'Activity Log', 'kvp' ), __( 'Activity Log', 'kvp' ), 'import', 'kvp-activity-log', array( $this, 'view_activity_log' ) ),
			//array( __( 'Statistics', 'kvp' ), __( 'Statistics', 'kvp' ), 'import', 'kvp-statistics', array( $this, 'view_statistics' ) ),
			array( __( 'Settings', 'kvp' ), __( 'Settings', 'kvp' ), 'manage_options', 'kvp-settings', array( $this, 'view_settings' ) ),
		);
		
		$submenu = apply_filters( 'kvp_admin_submenu', $default_submenu );
		
		foreach ( $submenu as $item )
			add_submenu_page( $menu_slug, $item[0], $item[1], $item[2], $item[3], $item[4] );
		
	}
	
	/**
	 * Removes about page item from menu
	 * 
	 * @since 3.0.0
	 */
	public function remove_about_menu() {
		
		remove_submenu_page( 'index.php', 'kvp-about' );
		
	}
	
	/**
	 * Redirects to about page on activation
	 * 
	 * @since 3.0.0
	 */
	public function about_screen_redirect() {
		
		if ( ! get_transient( '_kvp_about_screen' ) )
			return;
		
		delete_transient( '_kvp_about_screen' );
		
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
			return;
		
		wp_safe_redirect( add_query_arg( array( 'page' => 'kvp-about' ), admin_url( 'index.php' ) ) );
		
	}
	
	/**
	 * Displays about page
	 * 
	 * @since 3.0.0
	 */
	public function about_screen() {
		
		include 'partials/view-about-screen.php';
		
	}
	
	/**
	 * Displays sources
	 *
	 * @since    3.0.0
	 */
	public function view_sources() {
		
		if( current_user_can( 'import' ) ) {
		
			include_once 'class-sources-table.php';
			include 'partials/view-sources.php';
			
		}
		
	}
	
	/**
	 * Displays log of events occuring within KVP
	 *
	 * @since    3.0.0
	 */
	public function view_activity_log() {
		
		include_once 'class-activity-log-table.php';
		include 'partials/view-activity-log.php';
		
	}
	
	/**
	 * Displays Statistics
	 *
	 * @since    3.0.0
	 */
	public function view_statistics() {
		
		include 'partials/view-statistics.php';
		
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
	 * Creates image sizes array for video dimensions settings
	 *
	 * @since  3.2.0
	 */
	public function video_dimensions( $sizes ) {
		global $_wp_additional_image_sizes;
 		
 		foreach( get_intermediate_image_sizes() as $s ){
 			
 			if( in_array( $s, array( 'thumbnail', 'medium', 'large' ) ) ){
 				
 				$width = get_option( $s . '_size_w' );
 				$sizes[ $width ] = $s . ' (' . $width . 'px)';
 			
 			} else {
 				if( isset( $_wp_additional_image_sizes ) && isset( $_wp_additional_image_sizes[ $s ] ) )
 					$sizes[ $_wp_additional_image_sizes[ $s ]['width'] ] = $s . ' (' . $_wp_additional_image_sizes[ $s ]['width'] . 'px)';
 			}
 		}

 		return $sizes;

	}
	
	/**
	 * Registers admin meta boxes.
	 *
	 * @since    2.0.0
	 */
	public function setup_meta_boxes() {
		
		$default_meta_boxes = array(
			array( 'kvp-statistics', __( 'Operation Statistics', 'kvp' ), array( new Katalyst_Video_Plus_Meta_Boxes, 'statistics' ), 'kvp_statistics', 'normal', 'default', null ),
			array( 'kvp-stress-forecast', __( 'Stress Forecast', 'kvp' ), array( new Katalyst_Video_Plus_Meta_Boxes, 'stress_forecast' ), 'kvp_statistics', 'normal', 'default', null ),
			array( 'kvp-system-info', __( 'Katalyst Video Plus Status', 'kvp' ), array( new Katalyst_Video_Plus_Meta_Boxes, 'system_info' ), 'kvp_statistics', 'side', 'default', null ),
			array( 'kvp-rate-us', __( 'Rate Katalyst Video Plus', 'kvp' ), array( new Katalyst_Video_Plus_Meta_Boxes, 'rate_us' ), 'kvp_statistics', 'side', 'default', null ),
			//array( 'kvp-extensions', __( 'Random Extension', 'kvp' ), array( new Katalyst_Video_Plus_Meta_Boxes, 'extensions' ), 'kvp_statistics', 'side', 'default', null ),
			array( 'kvp-connect-source', __( 'Connect Source', 'kvp' ), array( new Katalyst_Video_Plus_Meta_Boxes, 'connect_source' ), 'kvp_sources', 'side', 'default', null ),
		);
		
		$meta_boxes = apply_filters( 'kvp_meta_boxes', $default_meta_boxes );
		
		foreach ( $meta_boxes as $meta_box )
			add_meta_box( $meta_box[0], $meta_box[1], $meta_box[2], $meta_box[3], $meta_box[4], $meta_box[5], $meta_box[6] );
		
	}
	
	/**
	 * Ajax function for editing source.
	 *
	 * @since    2.0.0
	 */
	public function edit_source_ajax() {
		
		include_once 'class-sources-table.php';
		
		ob_clean();
		
		$sources = new KVP_Sources_Table();
		$sources->edit_source();
		
	}
	
	/**
	 * Ajax function for testing source.
	 * 
	 * @since 3.0.0
	 */
	public function test_source_ajax() {
		
		$services	= apply_filters( 'kvp_services', array() );
		$sources	= get_option( 'kvp_sources', array() );
		
		if( !isset($_GET['id']) || !isset($sources[$_GET['id']]) )
			die('<div class="no-video-results">' . __( 'Source does not exist.', 'kvp' ) . '</div>');
		
		$source	= $sources[$_GET['id']];
		
		$kvp_source_test = new Katalyst_Video_Plus_Import;
		$results = $kvp_source_test->test_source( $source );
		
		include_once 'partials/view-source-test.php';
		
		die();
		
	}

}
