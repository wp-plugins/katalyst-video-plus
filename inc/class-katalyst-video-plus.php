<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* Defines the core plugin class
*
* A class definition that includes attributes and functions used across both the
* public-facing side of the site and the dashboard.
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/inc
* @author     Keiser Media <support@keisermedia.com>
*/
class Katalyst_Video_Plus {

	/**
	 * @var Katalyst_Video_Plus_Loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * @var string The string used to identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * @var string The string used to uniquely identify this plugin.
	 */
	protected $plugin_slug;

	/**
	 * @var string The current version of the plugin.
	 */
	protected $version;

	/**
	 * @var string Minimum WordPress version requirement.
	 */
	protected $wp_version;

	/**
	 * Define the core functionality.
	 *
	 * @since    2.0.0
	 */
	public function __construct( $plugin_name, $version, $wp_version ) {

		$this->plugin_name = $plugin_name;
		$this->plugin_slug = strtolower( str_replace( ' ', '-', $plugin_name ) );
		$this->version = $version;
		$this->wp_version = $wp_version;

		$this->load_dependencies();
		$this->set_locale();
		$this->process_upgrade();
		$this->register_settings();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->init_services();
		$this->setup_CRON();

	}

	/**
	 * Load the required dependencies.
	 * 
	 * @since    2.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/class-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/class-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/class-upgrade.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/class-updater.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/class-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-public.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/class-service.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'modules/youtube-basic/class-service.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/class-client.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'modules/youtube-basic/class-client.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/class-cron.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/class-import.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/functions.php';

		$this->loader = new Katalyst_Video_Plus_Loader();

	}

	/**
	 * Define the locale for internationalization.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function set_locale() {

		$katalyst_video_plus_i18n = new Katalyst_Video_Plus_i18n();
		$katalyst_video_plus_i18n->set_domain( $this->get_plugin_info( 'slug' ) );

		$this->loader->add_action( 'plugins_loaded', $katalyst_video_plus_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Process all upgrade logic
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function process_plugin_updates() {
		
		
	}

	/**
	 * Process plugin updates
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function process_upgrade() {

		$katalyst_video_plus_update = new Katalyst_Video_Plus_Upgrade( $this->get_plugin_info( 'version' ) );
		
	}

	/**
	 * Register settings
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function register_settings() {
		
		$katalyst_video_plus_settings = new Katalyst_Video_Plus_Settings();

		$this->loader->add_action( 'admin_init', $katalyst_video_plus_settings, 'register_settings' );
		
	}

	/**
	 * Register all of the hooks related to the dashboard functionality
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$katalyst_video_plus_admin = new Katalyst_Video_Plus_Admin( $this->get_plugin_info( 'name' ), $this->get_plugin_info( 'slug' ), $this->get_plugin_info( 'version' ) );

		$this->loader->add_action( 'admin_enqueue_scripts', $katalyst_video_plus_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $katalyst_video_plus_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $katalyst_video_plus_admin, 'setup_menu' );
		$this->loader->add_action( 'admin_init', $katalyst_video_plus_admin, 'setup_meta_boxes' );
		$this->loader->add_action( 'wp_ajax_kvp_inline_save', $katalyst_video_plus_admin, 'edit_account_ajax' );
		
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$katalyst_video_plus_public = new Katalyst_Video_Plus_Public( $this->get_plugin_info( 'name' ), $this->get_plugin_info( 'slug' ), $this->get_plugin_info( 'version' ) );

		$this->loader->add_action( 'wp_enqueue_scripts', $katalyst_video_plus_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $katalyst_video_plus_public, 'enqueue_scripts' );
		$this->loader->add_action( 'post_thumbnail_html', $katalyst_video_plus_public, 'post_thumbnail_html' );
		$this->loader->add_action( 'wp', $katalyst_video_plus_public, 'audit_single' );
		$this->loader->add_action( 'the_content', $katalyst_video_plus_public, 'the_content' );

	}
	
	/**
	 * Initializes services
	 * 
	 * @since 2.0.0
	 */
	public function init_services() {
		
		$kvp_youtube_service = new KVP_YouTube_Basic_Service;
		
	}

	/**
	 * Sets up CRON
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function setup_CRON() {
		
		$katalyst_video_plus_CRON = new Katalyst_Video_Plus_CRON();
//$katalyst_video_plus_CRON->audit_event();
		$this->loader->add_action( 'init', $katalyst_video_plus_CRON, 'setup_cron' );
		$this->loader->add_action( 'kvp_import_cron', $katalyst_video_plus_CRON, 'import_event' );
		$this->loader->add_action( 'kvp_audit_cron', $katalyst_video_plus_CRON, 'audit_event' );
		$this->loader->add_action( 'kvp_purge_log_cron', $katalyst_video_plus_CRON, 'purge_log' );
		
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    2.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The plugin slug used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     2.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_info( $key ) {
		
		switch ( $key ) {
			
			case 'name':
				$value = $this->plugin_name;
				break;
			
			case 'slug':
				$value = $this->plugin_slug;
				break;
				
			case 'version':
				$value = $this->version;
				break;
			
			default:
				$value = null;
				break;
		}
		
		return $value;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     2.0.0
	 * @return    Katalyst_Video_Plus_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		
		return $this->loader;
		
	}

}