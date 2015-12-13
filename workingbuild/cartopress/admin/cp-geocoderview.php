<?php
/**
 * CartoPress Geocoder
 *
 * @package cartopress
 */
 
 /**
 * HTML markup for the Geocoder content. Included in 'cartopress_geocoder_content' method of the geocoder_metabox class.
 */
?>
<p class="howto">Use the search bar to find lookup an address. Select the correct address from the results, or fill in the location fields manually. Note: Latitude and longitude cooridnates are required.</p>
<div id="cpdb-metabox-wrapper">
	<h4>Choose a Location</h4>
	<section id="addressQuery">
        <form id="geo_search">
            <input id="omnibar" type="text" placeholder="Search Address (Neighborhood | City | State | Country)"/>
            <input type="button" class="getCurrentPosition button" onclick="locateMe()" value="Current Location"/>
            <input type="button" id="cpdb-search-button" class="button" onclick="doClick()" value="Search"/>
        </form>
    </section>
    
	
    <div id="results">
    	<a id="toggle-in-search" class="dashicons dashicons-dismiss cpdb-toggle-results"></a>
    	<h4>Search Results</h4>
        <section>
        	<div class="cpdb-result-item">
        		<p class="howto">There are no results to show.</p>
        	</div>
        </section>
    </div>
	
	
    <div class="cpdb-maincontent">
    	<a id="toggle-in-map" class="dashicons dashicons-dismiss cpdb-toggle-results"></a>
    	<div id="map"></div>
    	<div id="cpdb-geocode-values">
    		<h4>Location</h4>
    		<span id="cpdb-admin-toggle" class="dashicons dashicons-admin-generic"></span>
    		<p id="cpdb-cartodb-id">CartoDB ID: <span id="cartodb-id"><?php echo $cartodb_id ?></span></p>
    		<input type="checkbox" id="unlock_manual_edit" name="unlock_manual_edit" /><label for="unlock_manual_edit">Allow Geo Data Editing</label>
    		<section id="cpdb-admin-panel">
    			<input type="checkbox" id="cpdb-donotsync" name="cpdb-donotsync" value="1" <?php echo checked( 1, $donotsync_value, false ) ?>/><label for="cpdb-donotsync" id="cpdb-donotsync-label">Do Not Sync</label>
    			<input type="button" id="cpdb-reset-button" class="button" data-post_id="<?php echo $post->ID ?>" value="Revert to Saved Data"/>
    			<input type="button" id="cpdb-delete-button" class="button" data-post_id="<?php echo $post->ID ?>" value="Delete Geo Data"/>
    		</section>
    		<section id="cpdb-geocode-fields">
    			<div class="row">
    				<div class="cp-col-12"><label for="cp_geo_displayname">Location Display Name: </label><span><textarea rows="3" id="cp_geo_displayname" name="cp_geo_displayname" class="disabled" value="<?php echo esc_attr( $cp_geo_displayname ) ?>" placeholder="i.e. 200 North 7th Street, Brooklyn, NY" readonly="readonly"><?php echo esc_attr( $cp_geo_displayname ) ?></textarea></span></div>
    			</div>
    			<div class="row">
    				<div class="cp-col-6"><label for="cp_geo_lat">Latitude: </label><span class="smaller" id="span_cp_geo_lat"><input type="text" id="cp_geo_lat" name="cp_geo_lat" class="disabled" value="<?php echo esc_attr( $cp_geo_lat ) ?>" placeholder="i.e. 40.7165" readonly="readonly"/></span></div>
    				<div class="cp-col-6"><label for="cp_geo_long">Longitude: </label><span><input type="text" id="cp_geo_long" name="cp_geo_long" class="disabled" value="<?php echo esc_attr( $cp_geo_long ) ?>" placeholder="i.e. -73.9553" readonly="readonly"/></span></div>
    			</div>
    			<div class="row">
    				<div class="cp-col-4"><label for="cp_geo_streetnumber">Address Number: </label><span class="smaller" id="span_cp_geo_streetnumber"><input type="text" id="cp_geo_streetnumber" name="cp_geo_streetnumber" class="disabled" value="<?php echo esc_attr( $cp_geo_streetnumber ) ?>" placeholder="i.e. 200" readonly="readonly"/></span></div>
    				<div class="cp-col-4"><label for="cp_geo_street">Address Street: </label><span><input type="text" id="cp_geo_street" name="cp_geo_street" class="disabled" value="<?php echo esc_attr( $cp_geo_street ) ?>" placeholder="i.e. North 7th Street" readonly="readonly"/></span></div>
    				<div class="cp-col-4"><label for="cp_geo_postal">Postal Code: </label><span class="smaller" id="span_cp_geo_postal"><input type="text" id="cp_geo_postal" name="cp_geo_postal" class="disabled" value="<?php echo esc_attr( $cp_geo_postal ) ?>" placeholder="i.e. 11211" readonly="readonly"/></span></div>
    			</div>
    			<div class="row">
    				<div class="cp-col-4"><label for="cp_geo_adminlevel4_vill_neigh">Village/Neighborhood: </label><span><input type="text" id="cp_geo_adminlevel4_vill_neigh" name="cp_geo_adminlevel4_vill_neigh" class="disabled" value="<?php echo esc_attr( $cp_geo_adminlevel4_vill_neigh ) ?>" placeholder="i.e. Williamsburg" readonly="readonly"/></span></div>
    				<div class="cp-col-4"><label for="cp_geo_adminlevel3_city">Town/City: </label><span class="smaller" id="span_cp_geo_adminlevel3_city"><input type="text" id="cp_geo_adminlevel3_city" name="cp_geo_adminlevel3_city" class="disabled" value="<?php echo esc_attr( $cp_geo_adminlevel3_city ) ?>" placeholder="i.e. Brooklyn" readonly="readonly"/></span></div>
    				<div class="cp-col-4"><label for="cp_geo_adminlevel2_county">County/District: </label><span><input type="text" id="cp_geo_adminlevel2_county" name="cp_geo_adminlevel2_county" class="disabled" value="<?php echo esc_attr( $cp_geo_adminlevel2_county ) ?>" placeholder="i.e. Kings" readonly="readonly"/></span></div>
    			</div>
    			<div class="row">
    				<div class="cp-col-6"><label for="cp_geo_adminlevel1_st_prov_region">State/Province/Region: </label><span class="smaller" id="span_cp_geo_adminlevel1_st_prov_region"><input type="text" id="cp_geo_adminlevel1_st_prov_region" name="cp_geo_adminlevel1_st_prov_region" class="disabled" value="<?php echo esc_attr( $cp_geo_adminlevel1_st_prov_region ) ?>" placeholder="i.e. New York" readonly="readonly"/></span></div>
    				<div class="cp-col-6"><label for="cp_geo_adminlevel0_country">Country: </label><span><input type="text" id="cp_geo_adminlevel0_country" name="cp_geo_adminlevel0_country" class="disabled" value="<?php echo esc_attr( $cp_geo_adminlevel0_country ) ?>" placeholder="i.e. United States" readonly="readonly"/></span></div>
    			</div>
    		</section>
    		<h4>Summary Description</h4>
    		<p class="howto">Add a custom summary description which will be available for display in the CartoDB infowindow. If you leave it blank, CartoPress will attempt to use the post excerpt. If the excerpt is empty, CartoPress will create a summary description using the first 55 words of the post content.</p>
    		<section>
    			<textarea placeholder="Enter a Summary Description" name="cp_post_description" id="cp_post_description"><?php echo esc_attr( $cp_post_description ) ?></textarea>
    		</section>
			<?php
			if (empty($geodata)) {
				echo '<div id="comments"></div>';
			} elseif (!empty($geodata) && $cp_post[1] == false && $donotsync_value != 1) {
				echo '<div id="comments"><p class="warning">Geo data is not synced with CartoDB. Update the post to sync.</p></div>';
			} elseif (!empty($geodata) && $cp_post[1] == true && $donotsync_value != 1) {
				echo '<div id="comments"><p class="success">Geo data is synced with CartoDB.</p></div>';
			} elseif ($donotsync_value == 1) {
				echo '<div id="comments"><p class="warning">"Do Not Sync" is currently set to True. Data is not being synced to CartoDB.</p></div>';
			}
    		?>
    	</div>
    </div>
    <div style="clear:both;"></div>
 </div>
		