<hr>
<h2><?= __('Settings', 'wc-prov-cant-dist') ?></h2>

<?php do_action('wcpcd_before_table_settings'); ?>

<table class="form-table">
	<tr>
		<th><?= __('Remove priority on the Checkout', 'wc-prov-cant-dist') ?></th>
		<td>
			<label>
				<input type="checkbox" name="wcpcd_priority_override" value="1" <?php checked( $this->wcpcd_priority_override, 1 ); ?> /> <?= __('Yes', 'wc-prov-cant-dist') ?> <br/>
			</label>
			<p class="description"><?= __('It continues using the WooCommerce priority and labels for the fields state, city, address_1, address_2.', 'wc-prov-cant-dist') ?></p>
		</td>
	</tr>
	<tr>
		<th><?= __('Hide postcode', 'wc-prov-cant-dist') ?></th>
		<td>
			<label>
				<input type="checkbox" name="wcpcd_hide_zipcode" value="1" <?php checked( $this->wcpcd_hide_zipcode, 1 ); ?> /> <?= __('Yes', 'wc-prov-cant-dist') ?> <br/>
			</label>
			<p class="description"><?= __('It hides de postcode field on the Checkout and shipping calculator form.', 'wc-prov-cant-dist') ?></p>
		</td>
	</tr>
	<tr>
		<th><?= __('Set an empty State', 'wc-prov-cant-dist') ?></th>
		<td>
			<label>
				<input type="checkbox" name="wcpcd_set_empty_province" value="1" <?php checked( $this->wcpcd_set_empty_province, 1 ); ?> /> <?= __('Yes', 'wc-prov-cant-dist') ?> <br/>
			</label>
			<p class="description"><?= __('It adds an empty field to province dropdown to force the customers to choose a province.', 'wc-prov-cant-dist') ?></p>
		</td>
	</tr>
	<tr>
		<th><?= __('Set an empty City-District', 'wc-prov-cant-dist') ?></th>
		<td>
			<label>
				<input type="checkbox" name="wcpcd_set_empty_city_district" value="1" <?php checked( $this->wcpcd_set_empty_city_district, 1 ); ?> /> <?= __('Yes', 'wc-prov-cant-dist') ?> <br/>
			</label>
			<p class="description"><?= __('Leave the field City-District empty after the province is selected. Otherwise, first option is selected.', 'wc-prov-cant-dist') ?></p>
		</td>
	</tr>
	<tr>
		<th><?= __('Debug JS', 'wc-prov-cant-dist') ?></th>
		<td>
			<label>
				<input type="checkbox" name="wcpcd_debug_js" value="1" <?php checked( $this->wcpcd_debug_js, 1 ); ?> /> <?= __('Yes', 'wc-prov-cant-dist') ?> <br/>
			</label>
			<p class="description"><?= __('It prints the .js on production. Default prints .min.js.', 'wc-prov-cant-dist') ?></p>
		</td>
	</tr>
	<tr>
		<th><?= __('Locations', 'wc-prov-cant-dist') ?></th>
		<td>
			<label>
				<textarea name="wcpcd_locations" rows="7" class="widefat"><?= $this->wcpcd_locations ?></textarea> <br/>
			</label>
			<p class="description"><?= __('Optional, add a formatted JSON of locations to override the ones loaded from the file.', 'wc-prov-cant-dist') ?></p>
		</td>
	</tr>
</table>
<p class="description"><?= sprintf(
	/* translators: %s Version of the plugin */
	__('Current version: %s', 'wc-prov-cant-dist'), WPCD_PLUGIN_VERSION
	) ?></p>

<?php do_action('wcpcd_after_table_settings'); ?>

<hr>
<h4><?= __('Testing JSON of Locations', 'wc-prov-cant-dist') ?></h4>
<p class="description"><?= __('Shows the current locations loaded into the WC dropdowns.', 'wc-prov-cant-dist') ?></p>
<div class="testing-json-data">
	<?php
	$json_data = $this->json_data;
	$original_data = $this->wcpcd_get_provincia_canton_distrito();

	if ( $this->wcpcd_locations_from_settings() ) {
		?>
		<p><strong><?= __( 'Locations loading from settings of the plugin', 'wc-prov-cant-dist' ) ?></strong></p>
		<?php
	} else {
		?>
		<p><?= sprintf( '<strong>File path: </strong> %s', str_replace( content_url(), '', $json_data ) ) ?></p>
		<?php
	}
	?>

	<?php if ( is_null( $original_data ) ) { ?>
		<p><?= sprintf(
			/* translators: %1$s Plugin file path, %2$s Theme file path */
			__('The locations couldn\'t be loaded to your site. Move the file %1$s from the plugin to your theme or child theme in %2$s. Then, add the snippet below to override the locations.', 'wc-prov-cant-dist'), '<em>/assets/js/prov-cant-dist.json</em>', '<em>your-theme/assets/js/prov-cant-dist.json</em>'
		) ?></p>
<pre><code><?php echo "/**
 * Adding custom json file locations from child theme
 * WC Provincia-Canton-Distrito
 */
function kmchild_prov_cant_dist_json( \$json_file ) {
	\$json_file = get_stylesheet_directory_uri() . '/assets/js/prov-cant-dist.json';
	
	return \$json_file;
}
add_filter('wcpcd_prov_cant_dist_json', 'kmchild_prov_cant_dist_json');"; ?></code></pre>
	<p>Still having problems with locations? <a href="mailto:contacto@keylormendoza.com">Contact me</a></p>
		<?php
	} else {
		?>
		<pre style="max-height: 200px; max-width: 100%; overflow: auto; white-space: pre-wrap;"><?php echo json_encode( $original_data ); ?></pre>
		<?php
	}
	?>
</div>
<hr>
