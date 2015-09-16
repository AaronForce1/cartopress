<?php
/**
 * CartoPress Settings Page
 *
 * @package cartopress
 */
 
 /* main cartopress settings class
 *	@since 0.1.0
 */

?>


<div class="wrap">
	<header>
		<?php echo '<img src="' . cartopress_url . '/admin/images/cartopress-logo.png" id="cartopress-logo" />' ?>
	</header>
	<div id="content">
		<h1 id="title">CartoPress — CartoDB for WordPress</h1>
    	<p><em>This plugin allows you to take advantage of the CartoDB's visualization tools while using your existing WordPress database.</em></p>
		<?php echo "<h2 id=" . "settings" . ">" . __( 'Settings', 'menu-test' ) . "</h2>";?> 
		<p>CartoPress requires a CartoDB account. Please visit <a href="https://cartodb.com/signup" target="_blank"><span class="button">cartodb.com/signup</span></a> to create an account.</p>
		<h3>Required CartoDB Account Information</h3>
		<form id="cpdb-account-settings" action="options.php" method="post">
			<?php
                // This prints out all hidden setting fields
                settings_fields( 'cartopress_settings_group' );   
                do_settings_sections( 'cartopress-settings' );
            	submit_button(); 
        	?>
		</form>
		<p>You can take advantage of CartoDB features and extensive custom styling options with CartoCSS by using the CartoDB interface. Please note that for automatic syncing you must have a paid CartoDB account if you are using this plugin for commercial purposes. If you are an individual user or nonprofit organization, please <a href="mailto:support@cartodb.com">contact</a> CartoDB to allow database syncing on your free account.</p>
		<p>Login to your CartoDB account here in order to style your map using CartoCSS: <span class="button"><a href="http://cartodb.com/login">Login</a></span>
		<hr/>
		<h3>Data Collection</h3>
		<h4>Collect Data For:</h4>
		<p>Note: Checking these boxes will allow CartoPress to add the geolocator widget post, page and media edit screens. Geolocation data will be automatically collected for comments based on the user's current location</p> 
		<form id="cpdb-collectdata">
			<input type="checkbox" name="posts" />&nbsp;<label for="posts">Posts</label>
			<input type="checkbox" name="pages" />&nbsp;<label for="pages">Pages</label>
			<input type="checkbox" name="media" />&nbsp;<label for="media">Media</label>
			<input type="checkbox" name="comments"/>&nbsp;<label for="comments">Comments</label>
		</form>
		<h4>Data Sync Options</h4>
		<p>CartoDB for Wordpress will automatically sync data for Name, Content, Permalink, and Date to your CartoDB account. Please select from the following additional options:</p> 
		<form id="cpdb-datasync">
			<input type="checkbox" name="categories" />&nbsp;<label for="categories">Categories</label>
			<input type="checkbox" name="tags" />&nbsp;<label for="tags">Tags</label>
			<input type="checkbox" name="featuredimage" />&nbsp;<label for="featuredimage">Featured Image</label>
			<input type="checkbox" name="customfields"/>&nbsp;<label for="customfields">Custom Fields</label>
		</form>
		<p>Checking these boxes will add these features to sync so they can be featured and styled in the CartoDB infobox.</p>
		<hr/>
		<!--***FOR VERSION 2***
		<h3>Publishing</h3>
				<h4>Preview Map</h4>
				<p>This is the map displayed when creating or updating the post. This will not be published, but is for the purpose of geolocating your posts in the Dashboard.</p>
					<h5>Providers</h5>
					<select id="cpdb-previewmap-select">
						<option>Acetate</option>
						<option>BasemapAT</option>
						<option>CartoDB</option>
						<option>ESRI</option>
						<option>FreeMapSK</option>
						<option>HERE (formerly Nokia)</option>
						<option>HikeBike</option>
						<option>Hydda</option>
						<option>MapBox</option>
						<option>MapQuest</option>
						<option>MtbMap</option>
						<option>NASAGIBS</option>
						<option>OpenMapSurfer</option>
						<option>OpenSeaMap</option>
						<option>OpenStreetMap</option>
						<option>OpenWeatherMap</option>
						<option>Stamen</option>
						<option>Thunderforest</option>
						<option>Other</option>
					</select>
				<p>This is where the Map Select will go</p>
				<h4>Single Map</h4>
				<p>A single map is used for displaying the location of a single post, page, comment or media object. This must be inserted into the post that is being georeferenced. Use the following shortcode to insert into your post, page or comment template or insert into your post content. <strong>[cartodb_single_map]</strong>. See <a href="#">documentation</a> for shortcode options including setting the size.</p>
				<form id="cpdb-publishtype-singlepost">
					<table>
						<tr>
							<td><input type="radio" name="CartoDBJPG" />
							</td>
							<td><label for="CartoDBJPG">CartoDB JPG Option</label> <p>This will insert a JPG instead of a dynamic map.</p>
							</td>
						</tr>
						<tr>
							<td><input type="radio" name="iframe" />
							</td>
							<td><label for="iframe">Use iframe embed</label> <p>This will use CartoDB iframe embed code instead of using the API. It requires the CartoDB share URL.</p>
							</td>
						</tr>
						<tr>
							<td><label for="iframe_url">URL:</label>
							</td>
							<td><input type="text" name="iframe_url" placeholder="Enter CartoDB share URL"/>
							</td>
						</tr>
						<tr>
							<td><input type="radio" name="javascript" />
							</td>
							<td><label for="javascript">Use Javascript embed</label> <p>This allows for greater customization. This must be selected to include a layer filter and search.</p>
							</td>
						</tr>
						<tr>
							<td>CartoDB URL:
							</td>
							<td><input type="text" name="iframe_url" placeholder="Enter CartoDB share URL"/>
							</td>
						</tr>
					</table>
				</form>-->
		
    </div>
</div>
