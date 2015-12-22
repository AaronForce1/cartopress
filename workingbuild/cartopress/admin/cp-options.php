<?php
/**
 * CartoPress Settings Page
 *
 * @package cartopress
 */
 
 /**
 * HTML markup for the settings page
 */
?>
<div class="wrap">
	<header>
		<?php echo '<img src="' . CARTOPRESS_URL . '/admin/images/cartopress-logo.png" id="cartopress-logo" />' ?>
	</header>
	<div id="content">
		<h1 id="title">CartoPress — A CartoDB Client for WordPress</h1>
    	<p>This plugin allows you to take advantage of the CartoDB's visualization tools while using your existing WordPress database as a data source. Input your CartoDB account settings and set your Data Collection options to sync your Wordpress site with your CartoDB account. <a href="https://github.com/MasterBaideme1021/cartopress/wiki" target="_blank">Documentation</a></p>
		<?php echo "<h2 id=" . "settings" . ">" . __( 'Settings', 'menu-test' ) . "</h2>";?> 
		<p>CartoPress requires a CartoDB account. Please visit <a href="https://cartodb.com/signup" target="_blank"><span class="button">cartodb.com/signup</span></a> to create an account.</p>
		<form id="cpdb-form-settings" action="options.php" method="post">
			<?php
                settings_fields( 'cartopress-settings');   
                do_settings_sections( 'cartopress-settings-group' );
			?>
			<h3>Required CartoDB Account Information</h3>
			<table class="form-table">
				<tr class="row">
					<td><?php do_settings_fields( 'cartopress-settings', 'cartopress_required_info' ); ?></td>
				</tr>	
			</table>
			<input type="button" class="button" id="generate_table" name="generate_table" value="Connect to CartoDB" />
        	<img src="<?php echo admin_url('/images/wpspin_light.gif'); ?>" class="waiting" id="cpbd_tableconnect_loading" />
			<div class="cpdb-settingsection">
				<h3>Data Collection</h3>
				<h4>Collect Data For:</h4>
				<p>Note: Checking these boxes will allow CartoPress to add the geolocator widget to the post, page and media edit screens.</p> 
				<?php do_settings_fields( 'cartopress-settings', 'cartopress_collect_info' ); ?>
	        	<h4>Data Sync Options</h4>
				<p>CartoPress will automatically sync the Post Title, Summary Description, Permalink, Post Date, and all Geo Data to your CartoDB account. You may also select from the following additional options. Checking these boxes will add these features to sync so they can be featured and styled in the CartoDB infobox.</p>
				<p><em>Note: Unchecking will not remove existing data from your CartoDB table. Any new data, however, will not sync.</em> </p> 
				<?php do_settings_fields( 'cartopress-settings', 'cartopress_sync_info' ); ?>
	        	<div id="cpdb-customfields-select">
					<h5>Custom Field Selection</h5>
					<p>Please select a custom field from the drop down menu and press the Add Custom Field button to start syncing that custom field to CartoDB. Unchecking the box under the &ldquo;Syncing&rdquo; heading will stop syncing to for that field. Clicking &ldquo;Remove&rdquo; will remove the column (and all of its data) from CartoDB.</p>
					<select id="cpdb-customfield-select-menu">
						<option value="" disabled selected id="placeholder">Select a Custom Field</option>
						<?php cartopress_settings::get_metakey_menu(); ?>
					</select>
					<input type="button" class="button" id="add_column" name="add_column" value="Add Custom Field" />
					<div id="cpdb-customfield-display">
						<table>
							<thead>
								<tr>
									<td align="center">Syncing</td>
									<td>Custom Field Name</td>
									<td>CartoDB Column</td>
									<td align="center">Remove</td>
								</tr>
							</thead>
							<tbody><?php do_settings_sections( 'cartopress-customfields-group' ); ?></tbody>
						</table>
						<p id="cpdb-comment"></p>
					</div>
				</div>
			</div>
			<div class="cpdb-breakout">
				<h3>Styling &amp; Publishing</h3>
				<p>Login to your CartoDB account in order to take advantage of CartoDB&rsquo;s <a href="http://docs.cartodb.com/cartodb-editor/maps/" target="_blank">built-in</a> and <a href="http://docs.cartodb.com/cartodb-platform/cartocss/" target="_blank">customizable</a> styling options.</p>
				<p>You can use CartoDB&rsquo;s <a href="http://docs.cartodb.com/cartodb-editor/maps/#publish-and-share-your-map" target="_blank">publishing</a> options to publish maps on your site.</p>
				<p><a href="http://cartodb.com/login" target="_blank"><span class="button">Login</span></a>
			</div>
			<?php submit_button(); ?>
		</form>
		<p class="cpdb-attribution">CartoPress is not developed by, or a product of CartoDB.</p>
    </div>
</div>