<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }

class KVP_Settings {
	
	private $options = array();
	
	public function __construct() {
		
		$this->options = $this->get_settings();
		
		add_action('admin_init', array($this, 'register_settings') );
		
	}
	
	/**
	* Populate saved options
	*
	* @since 1.0
	* @return array
	*/
	public function get_settings() {
		
		$settings = get_option('kvp_settings', array());
		
		return apply_filters( 'kvp_get_settings', $settings );
		
	}
	
	public function register_settings() {
		
		if ( false == get_option('kvp_settings') )
			add_option('kvp_settings', $this->options);
	
		foreach( $this->get_registered_settings() as $tab => $settings ) {
	
			add_settings_section('kvp_settings_' . $tab, __return_null(), '__return_false', 'kvp_settings_' . $tab);
	
			foreach ( $settings as $option ) {
				add_settings_field(
					'kvp_settings[' . $option['id'] . ']',
					$option['name'],
					array($this,
						method_exists($this, $option['type'] . '_callback') ? $option['type'] . '_callback' : 'missing_callback'
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
		
		register_setting( 'kvp_settings', 'kvp_settings', array($this, 'settings_sanitize') );
		
		
	}
	
	private function get_registered_settings() {
	
		$pages = get_pages();
		$pages_options = array( 0 => '' );
		
		if ( $pages ) {
			foreach ( $pages as $page )
				$pages_options[ $page->ID ] = $page->post_title;
		}
		
	
		$settings = array(
			/** General Settings */
			'general' => apply_filters( 'kvp_settings_general',
				array(
					'test_mode' => array(
						'id' => 'test_mode',
						'name' => __( 'Test Mode', 'kvp' ),
						'desc' => __( 'While in test mode posts will not be processed.', 'kvp' ),
						'type' => 'checkbox'
					),
				)
			),
			/** Display Settings */
			'display' => apply_filters('kvp_settings_display',
				array(
					'show_video_in_list' => array(
						'id' => 'show_video_in_list',
						'name' => __( 'Show Video in Archive List', 'kvp' ),
						'desc' => __( 'Check this to show the video in archive lists instead of the featured image.', 'kvp' ),
						'type' => 'checkbox'
					),
				)
			),
			/** Import Settings */
			'import' => apply_filters('kvp_settings_import',
				array(
					'post_format' => array(
						'id' => 'post_format',
						'name' => __( 'Import Post Format', 'kvp' ),
						'desc' => __( 'Changing this option will change the post format on newly imported or audited posts.', 'kvp' ),
						'type' => 'select',
						'options' => apply_filters('kvp_post_formats', array(
								'standard'	=> 'Standard',
								'video'		=> 'Video',
								
							)
						),
					),
				)
			),
			/** Extension Settings */
			'extensions' => apply_filters('kvp_settings_extensions',
				array()
			),
			/** Licenses Settings */
			'licenses' => apply_filters('kvp_settings_licenses',
				array()
			),
			/** Misc Settings */
			'misc' => apply_filters('kvp_settings_misc',
				array()
			)
		);
		
		return $settings;
		
	}
	
	public function header_callback( $args ) {
		echo '';
	}
	
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
	
	public function checkbox_callback( $args ) {
		
		$checked = isset($this->options[$args['id']]) ? checked(1, $this->options[$args['id']], false) : '';
		$html	 = '<input type="checkbox" id="kvp_settings_' . $args['section'] . '[' . $args['id'] . ']" name="kvp_settings_' . $args['section'] . '[' . $args['id'] . ']" value="1" ' . $checked . '/>';
		$html	.= '<label for="kvp_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
	
		echo $html;
		
	}
	
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
	
	function license_key_callback( $args ) {
		
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
	
	public function missing_callback( $args ) {
		printf( __( 'The callback function used for the <strong>%s</strong> setting is missing.', 'kvp' ), $args['id'] );
	}

	public function settings_sanitize( $input = array() ) {
	
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
	
				if( empty( $_POST[ 'kvp_settings_' . $tab ][ $key ] ) ) {
					unset( $this->options[ $key ] );
				}
	
			}
		}
		
		// Merge our new settings with the existing
		$output = array_merge( $this->options, $output );
	
		add_settings_error( 'kvp-notices', '', __( 'Settings Updated', 'kvp' ), 'updated' );
	
		return $output;
	
	}
	
	public function get_settings_tabs() {
		
		$settings = $this->get_registered_settings();
	
		$tabs             = array();
		$tabs['general'] = __( 'General', 'kvp' );
		$tabs['display'] = __( 'Display', 'kvp' );
		$tabs['import']	 = __( 'Import', 'kvp' );
	
		if( ! empty( $settings['extensions'] ) ) {
			$tabs['extensions'] = __( 'Extensions', 'kvp' );
		}
		
		if( ! empty( $settings['licenses'] ) ) {
			$tabs['licenses'] = __( 'Licenses', 'edd' );
		}
	
		$tabs['misc']	 = __( 'Misc', 'kvp' );
	
		return apply_filters( 'kvp_settings_tabs', $tabs );
	}
	
}