<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* Automates licence registration and updates for premium, dependency (child) plugins.
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/inc
* @author     Keiser Media <support@keisermedia.com>
*/
class KVP_Plugin_Updater {
	
	/**
	 * Extension primary file.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string File location.
	 */
	private $file;
	
	/**
	 * Extension license key.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string License key.
	 */
	private $license;
	
	/**
	 * Extension Name.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string Extension name.
	 */
	private $item_name;
	
	/**
	 * Extension slug.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string Extension slug.
	 */
	private $item_slug;
	
	/**
	 * Extension version.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string Extension version.
	 */
	private $version;
	
	/**
	 * Extension author.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string Extension author.
	 */
	private $author;
	private $api_url = 'http://katalystvideoplus.com';

	function __construct( $_file, $_item_name, $_version, $_author, $_api_url = null ) {
		$options = get_option('kvp_settings');

		$this->file         = $_file;
		$this->item_name    = $_item_name;
		$this->item_slug	= 'kvp_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $this->item_name ) ) );
		$this->version      = $_version;
		$this->license      = isset( $kvp_options[ $this->item_slug . '_license_key' ] ) ? trim( $kvp_options[ $this->item_slug . '_license_key' ] ) : '';
		$this->author       = $_author;
		$this->api_url      = is_null( $_api_url ) ? $this->api_url : $_api_url;

		// Setup hooks
		$this->includes();
		$this->hooks();
		$this->auto_updater();
	}

	/**
	 * Include the updater class
	 *
	 * @access  private
	 * @return  void
	 */
	private function includes() {
		if ( ! class_exists( 'EDD_Plugin_Updater' ) ) require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/class-edd-sl-plugin-updater.php';
	}

	/**
	 * Setup hooks
	 *
	 * @access  private
	 * @return  void
	 */
	private function hooks() {
		// Register settings
		add_filter( 'kvp_settings_licenses', array( $this, 'settings' ), 1 );

		// Activate license key on settings save
		add_action( 'admin_init', array( $this, 'activate_license' ) );

		// Deactivate license key
		add_action( 'admin_init', array( $this, 'deactivate_license' ) );
	}

	/**
	 * Auto updater
	 *
	 * @access  private
	 * @global  array $kvp_options
	 * @return  void
	 */
	private function auto_updater() {
		// Setup the updater
		$kvp_updater = new EDD_SL_Plugin_Updater(
			$this->api_url,
			$this->file,
			array(
				'version'   => $this->version,
				'license'   => $this->license,
				'item_name' => $this->item_name,
				'author'    => $this->author
			)
		);
	}


	/**
	 * Add license field to settings
	 *
	 * @access  public
	 * @param array   $settings
	 * @return  array
	 */
	public function settings( $settings ) {
		$kvp_license_settings = array(
			array(
				'id'      => $this->item_slug . '_license_key',
				'name'    => sprintf( __( '%1$s License Key', 'kvp' ), $this->item_name ),
				'desc'    => '',
				'type'    => 'license_key',
				'options' => array( 'is_valid_license_option' => $this->item_slug . '_license_active' ),
				'size'    => 'regular'
			)
		);

		return array_merge( $settings, $kvp_license_settings );
	}


	/**
	 * Activate the license key
	 *
	 * @access  public
	 * @return  void
	 */
	public function activate_license() {
		if ( ! isset( $_POST['kvp_settings_licenses'] ) )
			return;

		if ( ! isset( $_POST['kvp_settings_licenses'][ $this->item_slug . '_license_key' ] ) )
			return;

		if ( 'valid' == get_option( $this->item_slug . '_license_active' ) )
			return;

		$license = sanitize_text_field( $_POST['kvp_settings_licenses'][ $this->item_slug . '_license_key' ] );

		// Data to send to the API
		$api_params = array(
			'kvp_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( $this->item_name ),
			'url'        => home_url()
		);

		// Call the API
		$response = wp_remote_get(
			add_query_arg( $api_params, $this->api_url ),
			array(
				'timeout'   => 15,
				'body'      => $api_params,
				'sslverify' => false
			)
		);

		// Make sure there are no errors
		if ( is_wp_error( $response ) )
			return;

		// Decode license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		update_option( $this->item_slug . '_license_active', $license_data->license );
	}


	/**
	 * Deactivate the license key
	 *
	 * @access  public
	 * @return  void
	 */
	public function deactivate_license() {
		if ( ! isset( $_POST['kvp_settings_licenses'] ) )
			return;

		if ( ! isset( $_POST['kvp_settings_licenses'][ $this->item_slug . '_license_key' ] ) )
			return;

		// Run on deactivate button press
		if ( isset( $_POST[ $this->item_slug . '_license_key_deactivate' ] ) ) {
			// Run a quick security check
			if ( check_admin_referer( $this->item_slug . '_license_key_nonce', $this->item_slug . '_license_key_nonce' ) )
				return;

			// Data to send to the API
			$api_params = array(
				'kvp_action' => 'deactivate_license',
				'license'    => $this->license,
				'item_name'  => urlencode( $this->item_name ),
				'url'        => home_url()
			);

			// Call the API
			$response = wp_remote_get(
				add_query_arg( $api_params, $this->api_url ),
				array(
					'timeout'   => 15,
					'sslverify' => false
				)
			);

			// Make sure there are no errors
			if ( is_wp_error( $response ) )
				return;

			// Decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( $license_data->license == 'deactivated' )
				delete_option( $this->item_slug . '_license_active' );
		}
	}
}