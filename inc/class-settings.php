<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* Registers settings and appropriate callback functions.
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/admin
* @author     Keiser Media <support@keisermedia.com>
*/
class Katalyst_Video_Plus_Settings {
	
	/**
	 * Contains the settings.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      array KVP Settings.
	 */
	private $options = array();
	
	/**
	 * Populate saved options
	 *
	 * @since  	 2.0.0
	 * @return 	 array Settings
	 */
	public function __construct() {
		
		if( empty( $this->options ) )
			$this->options = get_option( 'kvp_settings', array() );
		
		$this->options = apply_filters( 'kvp_get_settings', $this->options );
		
	}
	
	/**
	 * Register settings within WP
	 * 
	 * @since 2.0.0
	 */
	public function register_settings() {
		
		foreach( $this->get_registered_settings() as $tab => $settings ) {
			
			add_settings_section( 'kvp_settings_' . $tab, __return_null(), '__return_false', 'kvp_settings_' . $tab );
			
			foreach( $settings as $option ) {
				
				add_settings_field(
					'kvp_settings[' . $option['id'] . ']',
					$option['name'],
					array( $this,
						method_exists( $this, $option['type'] . '_callback' ) ? $option['type'] . '_callback' : 'missing_callback'
					),
					'kvp_settings_' . $tab,
					'kvp_settings_' . $tab,
					array(
						'id'      => $option['id'],
						'desc'    => ! empty( $option['desc'] ) ? $option['desc'] : '',
						'name'    => $option['name'],
						'section' => $tab,
						'size'    => isset( $option['size'] ) ? $option['size'] : null,
						'options' => isset( $option['options'] ) ? $option['options'] : '',
						'std'     => isset( $option['std'] ) ? $option['std'] : ''
					)
				);
				
			}
			
		}
		
		register_setting( 'kvp_settings', 'kvp_settings', array( $this, 'sanitize_settings' ) );
		
	}
	
	/**
	 * Retrieves predefined settings
	 * 
	 * @since 2.0.0
	 */
	private function get_registered_settings() {
		
		$cron_schedules = wp_get_schedules();
		
		foreach( $cron_schedules as $key => $schedule )
			$cron_schedules[$key] = $schedule['display'];
		
		
		
		$settings = array(
			// General Settings
			'general' => apply_filters( 'kvp_settings_general',
				
				array(
					
					'test_mode' => array(
						'id'	=> 'test_mode',
						'name'	=> __( 'Test Mode', 'kvp' ),
						'desc'	=> __( 'While in test mode, posts will not be generated.', 'kvp' ),
						'type'	=> 'checkbox'
					),
					
				)
				
			),
			
			// Display Settings
			'display' => apply_filters( 'kvp_settings_display',
				
				array(
					
					'show_videos_in_main_query' => array(
						'id'	=> 'show_videos_in_main_query',
						'name'	=> __( 'Show Video in Blog', 'kvp' ),
						'desc'	=> __( 'If checked, videos will be included in the blog.', 'kvp' ),
						'type'	=> 'checkbox'
					),
					
					'show_video_in_lists' => array(
						'id'	=> 'show_video_in_lists',
						'name'	=> __( 'Show Video in Archive Lists', 'kvp' ),
						'desc'	=> __( 'If checked, archive lists will display the video instead of the thumbnail.', 'kvp' ),
						'type'	=> 'checkbox'
					),
					
					'force_video_into_content' => array(
						'id'	=> 'force_video_into_content',
						'name'	=> __( 'Force Video into Content', 'kvp' ),
						'desc'	=> __( 'If checked, videos will appear at the beginning of content.', 'kvp' ),
						'type'	=> 'checkbox'
					),
					
					'video_display_header' => array(
						'id'	=> 'video_display_header',
						'name'	=> __( 'Video Display', 'kvp' ),
						'type'	=> 'header',
					),

					'display_width' => array(
						'id'	=> 'display_width',
						'name'	=> __( 'Display Width', 'kvp' ),
						'desc'	=> __( 'Set the video width.', 'kvp' ),
						'type'	=> 'select',
						'options' => apply_filters( 'kvp_display_width', array(
								'automatic'		=> __( 'Automatic', 'kvp' ),
							)
						),
						'std'	=> 'automatic',
					),
					
					'custom_display_width' => array(
						'id'	=> 'custom_display_width',
						'name'	=> __( 'Custom Display Width', 'kvp' ),
						'desc'	=> __( 'Enter video width in px to override theme settings. Leave blank to use dropdown.', 'kvp' ),
						'type'	=> 'text',
						'size'	=> 'small',
					),
					
				)
				
			),
			
			// Import Settings
			'import' => apply_filters( 'kvp_settings_import',
				
				array(
					
					'audit_schedule' => array(
						'id'	=> 'audit_schedule',
						'name'	=> __( 'Audit Schedule', 'kvp' ),
						'desc'	=> __( 'Sets the recurrance schedule for the full audit. <strong>Note: Force an audit from the KVP dashboard to flush the old setting.</strong>', 'kvp' ),
						'type'	=> 'select',
						'options' => apply_filters( 'kvp_audit_schedules', $cron_schedules ),
						'std'	=> 'daily',
					),
					
					'purge_log' => array(
						'id'	=> 'purge_log',
						'name'	=> __( 'Purge Log', 'kvp' ),
						'desc'	=> __( 'Automatically perges log enteries older than setting.', 'kvp' ),
						'type'	=> 'select',
						'options' => apply_filters( 'kvp_import_post_formats', array(
								'1'		=> __( 'Daily', 'kvp' ),
								'30'	=> __( '30 Days', 'kvp' ),
								'60'	=> __( '60 Days', 'kvp' ),
							)
						),
						'std'	=> 'false',
					),
					
				)
				
			),
			
			//Extension Settings
			'extensions' => apply_filters( 'kvp_settings_extensions',
				array()
			),
			
			//License Settings
			'licenses' => apply_filters( 'kvp_settings_licenses',
				array()
			),
			
			//Misc Settings
			'misc' => apply_filters( 'kvp_settings_misc',
				array()
			),
			
		);
	
		return $settings;
		
	}
	
	/**
	 * Callback used to announce missing callbacks
	 * 
	 * @since 2.0.0
	 */
	public function missing_callback( $args ) {
		printf( __( 'The callback function used for the <strong>%s</strong> setting is missing.', 'kvp' ), $args['id'] );
	}
	
	/**
	 * Sanitizes all settings before save
	 * 
	 * @since 2.0.0
	 * @return array Sanitized option
	 */
	public function sanitize_settings( $input = array() ) {
		
		parse_str( $_POST['_wp_http_referer'], $referrer );
		
		$output    = array();
		$settings  = $this->get_registered_settings();
		$tab       = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';
		$post_data = isset( $_POST[ 'kvp_settings_' . $tab ] ) ? $_POST[ 'kvp_settings_' . $tab ] : array();
		
		$input = apply_filters( 'kvp_settings_' . $tab . '_sanitize', $post_data );
		
		// Loop through each setting being saved and pass it through a sanitization filter
		foreach( $input as $key => $value ) {
			
			// Get the setting type (checkbox, select, etc)
			$type = isset( $settings[$tab][ $key ][ 'type' ] ) ? $settings[$tab][ $key ][ 'type' ] : false;
			
			if( $type ) {
				// Field type specific filter
				$output[ $key ] = apply_filters( 'kvp_settings_sanitize_' . $type, $value, $key );
			}
	
			// General filter
			$output[ $key ] = apply_filters( 'kvp_settings_sanitize', $value, $key );
		}
	
	
		// Loop through the whitelist and unset any that are empty for the tab being saved
		if( ! empty( $settings[ $tab ] ) ) {
			foreach( $settings[ $tab ] as $key => $value ) {
	
				// settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
				if( is_numeric( $key ) ) {
					$key = $value['id'];
				}
	
				if( empty( $_POST[ 'kvp_settings_' . $tab ][ $key ] ) )
					unset( $this->options[ $key ] );
	
			}
		}
		
		// Merge our new settings with the existing
		$output = array_merge( $this->options, $output );
	
		add_settings_error( 'kvp-settings-notices', '', __( 'Settings Updated', 'kvp' ), 'updated' );
	
		return $output;
		
	}
	
	/**
	 * Displays Headers
	 * 
	 * @since 2.0.0
	 */
	public function header_callback( $args ) {
		
		echo '<hr />';
		
	}
	
	/**
	 * Displays Text Input
	 * 
	 * @since 2.0.0
	 */
	public function text_callback( $args ) {
	
		if ( isset( $this->options[ $args['id'] ] ) )
			$value = $this->options[ $args['id'] ];
		else
			$value = isset( $args['std'] ) ? $args['std'] : '';
	
		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . $size . '-text" id="kvp_settings_' . $args['section'] . '[' . $args['id'] . ']" name="kvp_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
		$html .= '<label for="kvp_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
	
		echo $html;
	}
	
	/**
	 * Displays Checkbox Input
	 * 
	 * @since 2.0.0
	 */
	public function checkbox_callback( $args ) {
		
		$checked = isset($this->options[$args['id']]) ? checked( 1, $this->options[$args['id']], false ) : '';
		$html	 = '<input type="checkbox" id="kvp_settings_' . $args['section'] . '[' . $args['id'] . ']" name="kvp_settings_' . $args['section'] . '[' . $args['id'] . ']" value="1" ' . $checked . '/>';
		$html	.= '<label for="kvp_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
	
		echo $html;
		
	}
	
	/**
	 * Displays Select Input
	 * 
	 * @since 2.0.0
	 */
	public function select_callback($args) {
		
		if ( isset( $this->options[ $args['id'] ] ) )
			$value = $this->options[ $args['id'] ];
		else
			$value = isset( $args['std'] ) ? $args['std'] : '';
	
		$html = '<select id="kvp_settings_' . $args['section'] . '[' . $args['id'] . ']" name="kvp_settings_' . $args['section'] . '[' . $args['id'] . ']"/>';
	
		foreach ( $args['options'] as $option => $name ) :
			$selected = selected( $option, $value, false );
			$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
		endforeach;
	
		$html .= '</select>';
		$html .= '<label for="kvp_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
	
		echo $html;
	}
	
	/**
	 * Displays License Key Input
	 * 
	 * @since 2.0.0
	 */
	public function license_key_callback( $args ) {
		
		if ( isset( $this->options[ $args['id'] ] ) )
			$value = $this->options[ $args['id'] ];
		else
			$value = isset( $args['std'] ) ? $args['std'] : '';

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . $size . '-text" id="kvp_settings_' . $args['section'] . '[' . $args['id'] . ']" name="kvp_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';

		if ( 'valid' == get_option( $args['options']['is_valid_license_option'] ) ) {
			$html .= wp_nonce_field( $args['id'] . '_nonce', $args['id'] . '_nonce', false );
			$html .= '<input type="submit" class="button-secondary" name="' . $args['id'] . '_deactivate" value="' . __( 'Deactivate License',  'kvp' ) . '"/>';
		}

		$html .= '<label for="kvp_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

		echo $html;
	}
	
}