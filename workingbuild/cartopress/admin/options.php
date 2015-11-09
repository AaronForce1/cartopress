<?php
/**
 * CartoPress Settings Page
 *
 * @package cartopress
 */
 
 /* main cartopress settings markup
 *	@since 0.1.0
 */

?>
<div class="wrap">
	<header>
		<?php echo '<img src="' . cartopress_url . '/admin/images/cartopress-logo.png" id="cartopress-logo" />' ?>
	</header>
	<div id="content">
		<h1 id="title">CartoPress — CartoDB Client for WordPress</h1>
    	<p>This plugin allows you to take advantage of the CartoDB's visualization tools while using your existing WordPress database as a data source. Input your CartoDB account settings and set your Data Collection options to sync your Wordpress site with your CartoDB account. <a href="#" target="_blank">Documentation</a></p>
		<?php echo "<h2 id=" . "settings" . ">" . __( 'Settings', 'menu-test' ) . "</h2>";?> 
		<p>CartoPress requires a CartoDB account. Please visit <a href="https://cartodb.com/signup" target="_blank"><span class="button">cartodb.com/signup</span></a> to create an account.</p>
		<form id="cpdb-form-settings" action="options.php" method="post">
			<?php
                settings_fields( 'cartopress-settings-group' );   
                do_settings_sections( 'cartopress-settings-group' );
			?>
			<h3>Required CartoDB Account Information</h3>
			<table class="form-table">
				<tr class="row">
					<td><?php do_settings_fields( 'cartopress-settings', 'cartopress_required_info' );?></td>
				</tr>	
			</table>
			<input type="button" class="button" id="generate_table" name="generate_table" value="Connect to CartoDB" />
        	<img src="<?php echo admin_url('/images/wpspin_light.gif'); ?>" class="waiting" id="cpbd_tableconnect_loading" />
			<div class="cpdb-breakout">
				<p>You can take advantage of CartoDB features and extensive custom styling options with CartoCSS by using the CartoDB interface.</p>
				<p>Login to your CartoDB account here in order to style your map using CartoCSS: <a href="http://cartodb.com/login" target="_blank"><span class="button">Login</span></a>
			</div>
			<h3>Data Collection</h3>
			<h4>Collect Data For:</h4>
			<p>Note: Checking these boxes will allow CartoPress to add the geolocator widget to the post, page and media edit screens.</p> 
			<?php                 
                do_settings_fields( 'cartopress-settings', 'cartopress_collect_info' );
        	?>
        	<h4>Data Sync Options</h4>
			<p>CartoPress will automatically sync data for Name, Content, Description, Permalink, Date, and Geo Data to your CartoDB account. Please select from the following additional options:</p> 
			<?php                 
                do_settings_fields( 'cartopress-settings', 'cartopress_sync_info' );
        	?>
        	<div id="cpdb-customfields-select">
				<h5>Custom Field Selection</h5>
				<p>Please select a custom field from the drop down menu and press the Add Custom Field button to start syncing that custom field to CartoDB. Uncheck the box to right of the added field to stop syncing that custom field. Deleting the custom field will remove all data for that field from your CartoDB table.</p>
				<select id="cpdb-customfield-select-menu">
					<option value="" disabled selected>Select a Custom Field</option>
			<?php
				$meta_keys = cartopress_settings::generate_metakeys();
				if ($meta_keys == null) {
					echo '<option value="" disabled>You have no custom fields</option>';
				} else {
					foreach ($meta_keys as $value) {
						$column = cartopress::create_column_name($value);
						echo '<option value="cp_post_customfield_' . $column . '" id="cp_post_customfield_' . $column . '">' . $value . '</option>
						';
					}
			    } // end if else
			?>
				</select>
				<input type="button" class="button" id="add_column" name="add_column" value="Add Custom Field" />
			</div>
			<p style="margin-top:10px;">Checking these boxes will add these features to sync so they can be featured and styled in the CartoDB infobox. <em>Note: Unchecking will not remove existing data from your CartoDB table, any new data, however, will not sync.</em> </p>
			
			<?php
            	submit_button(); 
        	?>
        	
		</form>
		<p class="cpdb-attribution">CartoPress is not developed by, or a product of CartoDB.</p>
    </div>
</div>
