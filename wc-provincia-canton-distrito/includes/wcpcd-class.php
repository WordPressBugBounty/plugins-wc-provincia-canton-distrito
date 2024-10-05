<?php
/**
 * WPCD Class
 * 
 * @version 1.5.0
 * @since 1.0.x
 */
class WC_PROV_CANT_DIST {
	public $id = '';
	public $name = '';
	public $title = '';
	public $description = '';
	public $json_data = '';
	public $wcpcd_locations = '';
	public $wcpcd_debug_js = false;
	public $wcpcd_priority_override = false;
	public $wcpcd_hide_zipcode = false;
	public $wcpcd_set_empty_city_district = false;
	public $wcpcd_set_empty_province = false;

	public function __construct() {
		// Check if WC is enable in the site
		if ( !class_exists( 'WooCommerce' ) ) return;

		$this->includes();

		$this->id = 'wcpcd';
		$this->name = 'wc-prov-cant-dist';
		$this->title = 'Custom states, cities and postcodes for WooCommerce.';
		$this->description = __('This plugin allows you to populate your custom states, cities, and postcodes for WooCommerce. It started working only for Costa Rica but now it is compatible with multi countries.', 'wc-prov-cant-dist');
		
		foreach ( $this->wcpcd_fields() as $wcpcd_field ) {
			$this->$wcpcd_field = get_option( $wcpcd_field );
		}

		$this->json_data = $this->wcpcd_get_json_file();

		add_action( 'wp_enqueue_scripts', array( $this, 'wcpcd_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wcpcd_scripts' ) );
		add_filter( 'woocommerce_states', array( $this, 'wcpcd_cr_states' ), 20 );

		add_filter( 'woocommerce_default_address_fields', array( $this, 'wcpcd_address_fields' ), 20 );

		add_action( 'admin_menu', array( $this, 'wcpcd_admin_page' ) );
		add_action( 'admin_init', array( $this, 'wcpcd_register_settings' ) );
		add_filter( 'plugin_action_links_' . WPCD_PLUGIN_FILE,  array($this, 'wcpcd_links' ) );

		add_action( 'wp_head', array( $this, 'wcpcd_hide_styles' ) );
	}

	public function includes() {
		include_once WPCD_PLUGIN_PATH . '/includes/wcpcd-admin.php';
	}

	/**
	 * Plugin locations allowed
	 * 
	 * @since 1.2.5
	 */
	private function wcpcd_locations_allowed() {
		global $pagenow, $post;

		$is_valid = ( is_cart() || is_checkout() || is_account_page() ) ? true : ( is_admin() && isset( $post->post_type ) && $post->post_type == 'shop_order' );

		return $is_valid;
	}

	/**
	 * Get json data
	 */
	public function wcpcd_get_json_file() {
		$json_file = apply_filters('wcpcd_prov_cant_dist_json', WPCD_PLUGIN_DIR_URL . 'assets/js/prov-cant-dist.json');

		return $json_file;
	}

	/**
	 * Load scripts
	 */
	public function wcpcd_scripts() {
		if ( $this->wcpcd_locations_allowed() ) {
			$min = ( !$this->wcpcd_debug_js && !isset( $_GET['wcpcd_debug'] ) ) ? '.min' : '';

			wp_enqueue_script( 'wcpcd-script', WPCD_PLUGIN_DIR_URL . 'assets/js/prov-cant-dist' . $min . '.js', array( 'jquery' ), WPCD_PLUGIN_VERSION, true );

			// Declare it to use ajax
			wp_localize_script( 'wcpcd-script', 'wcpcd_ajax', 
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'city_blank' => $this->wcpcd_set_empty_city_district,
					'city_first_option' => apply_filters( 'wcpcd_city_field_placeholder', __( 'Choose a city', 'wc-prov-cant-dist' ) ),
					'json' => $this->wcpcd_get_provincia_canton_distrito()
				) );
		}
	}

	/**
	 * Populate CR States to Woocommerce states field
	 */
	public function wcpcd_get_provincias( $key = '' ) {
		$provincias = apply_filters( 'wcpcd_cr_states', array(
			'SJ' => 'San José',
			'AL' => 'Alajuela',
			'CG' => 'Cartago',
			'HD' => 'Heredia',
			'GT' => 'Guanacaste',
			'PT' => 'Puntarenas',
			'LM' => 'Limón'
		) );

		if ( $this->wcpcd_set_empty_province ) {
			$provincias = wp_parse_args( $provincias, array( '0' => __( 'Choose a state', 'wc-prov-cant-dist' ) ) );
		}

		if ( !empty( $key ) ) {
			$cont = 1;
			foreach ($provincias as $pv => $provincia) {
				if ($pv == $key)
					break;

				$cont++;
			}
			return $cont;
			
		}

		return $provincias;
	}

	/**
	 * Load states to WC
	 */
	public function wcpcd_cr_states( $states ) {
		$states['CR'] = $this->wcpcd_get_provincias();

		return $states;
	}

	/**
	 * Get JSON provincias
	 * 
	 * @version 1.4.1
	 * @since 1.0.0
	 * 
	 * @return bool|array	Array of locations or false if file cannot be loaded
	 */
	public function wcpcd_get_provincia_canton_distrito() {
		$json = $this->wcpcd_locations_from_settings();
		
		if ( ! $json && $this->wcpcd_file_exists( $this->json_data ) ) {
			$string = $this->wcpcd_file_exists( $this->json_data );
			$json = $string ? json_decode( $string, true ) : false;
		}


		return apply_filters( 'wcpcd_get_provincia_canton_distrito', $json );
	}

	/**
	 * Check whether file exists
	 * 
	 * @version 1.4.0
	 * @since 1.0.0
	 * 
	 * @param string $file	File path
	 * 
	 * @return bool|string	Text for locations or bool if file cannot be loaded
	 */
	public function wcpcd_file_exists( $file ) {
		$ssl_verify = false;
		$data = wp_safe_remote_get( $file, array(
			'sslverify' => $ssl_verify
		) );

		if ( is_wp_error( $data ) ) {
			// Use CURL if wp_remote_fopen not working
			$temp_url = curl_init ( $file );
			curl_setopt( $temp_url, CURLOPT_HEADER, 0 );
			curl_setopt( $temp_url, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $temp_url, CURLOPT_SSL_VERIFYPEER, $ssl_verify );
			$curl_data = curl_exec( $temp_url );
			$code = curl_getinfo( $temp_url, CURLINFO_HTTP_CODE );
			curl_close( $temp_url );

			if ( $code == 200 ) {
				return $curl_data;
			}
		} else {
			return wp_remote_retrieve_body( $data );
		}

		return false;
	}

	/**
	 * Check whether locations are loading from setting option
	 * 
	 * @version 1.4.1
	 * 
	 * @return bool|array False if locations are loading from file. Array when locations are loading from settings
	 */
	public function wcpcd_locations_from_settings() {
		$json = false;

		if ( !empty( $this->wcpcd_locations ) ) {
			$locations = json_decode( $this->wcpcd_locations, true );
			$json = is_array( $locations ) ? $locations : false;
		}

		return $json;
	}

	/**
	 * Manage address field in checkout page
	 * Valid fixing WC 3.5 checkout fields order bug
	 */
	private function wcpcd_order_fields( $fields, $main_key = '' ) {
		$checkout_new_order = array();

		foreach ( $fields as $key => $single_key ) {
			$checkout_new_order[$key] = $fields[$key];
			if ( preg_match( '/country/', $key ) ) {
				$checkout_new_order[$main_key . 'state'] = $fields[$main_key . 'state'];
				$checkout_new_order[$main_key . 'city'] = $fields[$main_key . 'city'];
				$checkout_new_order[$main_key . 'address_1'] = $fields[$main_key . 'address_1'];
				$checkout_new_order[$main_key . 'address_2'] = $fields[$main_key . 'address_2'];
			}
		}

		return $checkout_new_order;
	}

	public function wcpcd_address_fields( $fields ) {
		if ( !$this->wcpcd_priority_override ) {
			$fields['state']['label'] = apply_filters( 'wcpcd_state_field_label', __( 'State', 'wc-prov-cant-dist' ) );
			$fields['city']['label'] = apply_filters( 'wcpcd_city_field_label', __( 'City-District', 'wc-prov-cant-dist' ) );
			$fields['city']['placeholder'] = apply_filters( 'wcpcd_city_field_placeholder', __( 'Choose a city', 'wc-prov-cant-dist' ) );
			$fields['city']['class'] = array( 'city_select', 'input-text' );
	
			// Set priority 40+, after country field
			$fields['state']['priority'] = 42;
			$fields['city']['priority'] = 43;
			$fields['address_1']['priority'] = 44;
			$fields['address_2']['priority'] = 45;

			/* Fix WC 3.5 */
			$fields = $this->wcpcd_order_fields( $fields );
		}

		if ( $this->wcpcd_hide_zipcode ) {
			$fields['postcode']['class'] = array( 'hide-zipcode' );
		}

		return $fields;
	}

	/**
	 * Settings page
	 */		
	private function wcpcd_fields() { 
		$wcpcd_fields = array( 'wcpcd_priority_override', 'wcpcd_hide_zipcode', 'wcpcd_debug_js', 'wcpcd_set_empty_province', 'wcpcd_set_empty_city_district', 'wcpcd_locations' );
		$custom_fields = apply_filters( 'wcpcd_register_custom_settings', array() );

		if( !empty( $custom_fields ) ) {
			$wcpcd_fields = array_merge( $wcpcd_fields, $custom_fields );
		}

		return $wcpcd_fields;
	}

	public function wcpcd_admin_page() {
		add_options_page( 'WC Provincia-Canton-Distrito', 'WC Provincia-Canton-Distrito', 'manage_options', $this->id, array( $this, 'wcpcd_settings_page' ) );
	}

	public function wcpcd_register_settings() {
		foreach ( $this->wcpcd_fields() as $wcpcd_field ) {
			register_setting( 'wcpcd-plugin-settings', $wcpcd_field );
		}
	}

	public function wcpcd_settings_page() {
		?>
		<div class="wrap">
			<h1><?= $this->title; ?></h1>
			<p><?= $this->description; ?></p>
			<form action="options.php" id="wpcd-form" method="post">
				<?php
				settings_fields( 'wcpcd-plugin-settings' );
				
				include_once WPCD_PLUGIN_PATH . '/includes/admin/wcpcd-settings.php';
				?>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Hides postcode field in shipping calculator
	 */
	public function wcpcd_hide_styles() {
		if ( $this->wcpcd_hide_zipcode && $this->wcpcd_locations_allowed() ) {
			?>
			<style type="text/css">
				.hide-zipcode,
				#calc_shipping_postcode_field {
					display: none !important;
				}
			</style>
			<?php
		}
	}

	/**
	 * Add Settings action links
	 */
	public function wcpcd_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=' . $this->id ) . '">' . __( 'Settings', 'wc-prov-cant-dist' ) . '</a>',
		);

		// Merge our new link with the default ones
		return array_merge( $plugin_links, $links );    
	}

	/**
	 * Validate deprecated method
	 * @version 1.2.3
	 */
	public function wpcd_get_provincias( $key ) {
		return $this->wcpcd_get_provincias( $key );
	}
}

