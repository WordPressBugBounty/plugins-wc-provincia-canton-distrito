<?php

/**
 * @version 1.5.3
 */
class WCPCD_Misc
{
    /**
     * Instance variable
     *
     * @var $instance The reference the *Singleton* instance of this class
     */
    private static $instance;

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return The|WCPCD_Misc $instance The *Singleton* instance.
     */
    public static function get_instance()
	{
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
		add_filter( 'woocommerce_states', array( $this, 'wcpcd_cr_states' ), 60 );
        add_filter( 'woocommerce_default_address_fields', array( $this, 'wcpcd_address_fields' ), 20 );
    }

	/**
	 * Load states to WC
     * 
     * @version 1.5.3
     * @since 1.x
     * 
     * @param array $states     WC States
     * 
     * @return array
	 */
	public function wcpcd_cr_states( $states )
	{
		$states['CR'] = WCPCD()->wcpcd_get_provincias();

		return $states;
	}

	/**
	 * Manage address field in checkout page
	 * Valid fixing WC 3.5 checkout fields order bug
	 * 
	 * @version 1.5.3
	 * @since 1.0.5
     * 
     * @param array $fields
     * @param string $main_key
     * 
     * @return array
	 */
	public function wcpcd_order_fields( $fields, $main_key = '' )
	{
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

    /**
     * Manage address field in checkout page
     * 
     * @version 1.5.3
     * @since 1.x
     * 
     * @param array $fields
     * 
     * @return array
     */
	public function wcpcd_address_fields( $fields )
	{
        $wcpcd = WCPCD();
		if ( !$wcpcd->wcpcd_priority_override ) {
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

		if ( $wcpcd->wcpcd_hide_zipcode ) {
			$fields['postcode']['class'] = array( 'hide-zipcode' );
		}

		return $fields;
	}
}

function wcpcd_misc() {
    return WCPCD_Misc::get_instance();
}
WCPCD_MISC();