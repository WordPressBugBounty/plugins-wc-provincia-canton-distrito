<?php
/**
 * Override billing & shipping admin order fields
 */
function wcpcd_admin_order_fields( $fields ) {
	if ( !is_admin() )
		return $fields;
	
	unset( $fields['city'] );
	unset( $fields['state'] );

	$country_classes = $fields['country']['class'];
	$fields['country']['class'] = $country_classes . ' country_select country_to_state';

	$fields['state'] = array(
		'label' => apply_filters( 'wcpcd_state_field_label', __( 'State', 'wc-prov-cant-dist' ) ),
		'class' => 'js_field-state select wide form-row-wide state_select',
		'wrapper_class' => 'form-field-wide',
		'show'  => false,
	);

	$fields['city'] = array(
		'label' => apply_filters( 'wcpcd_city_field_label', __( 'City-District', 'wc-prov-cant-dist' ) ),
		'class' => 'select wide city_select',
		'wrapper_class' => 'form-field-wide',
		'show'  => false,
		'placeholder' => apply_filters( 'wcpcd_city_field_placeholder', __( 'Choose a city', 'wc-prov-cant-dist' ) )
	);
	
	return $fields;
}
add_filter( 'woocommerce_admin_billing_fields', 'wcpcd_admin_order_fields', 99 );
add_filter( 'woocommerce_admin_shipping_fields', 'wcpcd_admin_order_fields', 99 );