<?php
/**
 * CartoPress Locations
 *
 * @package cartopress
 */
 
 /* add geocoder meta box
 *	@since 0.1.0
 */

if (!class_exists('geocoder_metabox')) {

	class geocoder_metabox {
	
		/**
		 * Hook into the appropriate actions when the class is constructed.
		 * 
		 * @since 0.1.0
		 */
		public function __construct() {
						
			// add the metabox	
			add_action( 'add_meta_boxes', array( $this, 'cartopress_add_geocoder' ) );
			
			// hook the save function
			add_action( 'save_post', array( $this, 'save_geodata' ) );
			add_action( 'edit_attachment', array ( $this, 'save_geodata'), 10, 1 );
	
		} // end __construct
		
		
		/**
		 * Adds the geolocator according to post type.
		 *
		 * @since 0.1.0
		 * @param string $post_type The post type of the post being saved.
		 */
		public function cartopress_add_geocoder( $post_type ) {
				$cpoptions = get_option( 'cartopress_admin_options', '' );
				if ($cpoptions['cartopress_collect_posts'] == 1) {
					$posts = 'post';
				} else {
					$posts = null;
				}
				if ($cpoptions['cartopress_collect_pages'] == 1) {
					$pages = 'page';
				} else {
					$pages = null;
				}
				if ($cpoptions['cartopress_collect_media'] == 1) {
					$media = 'attachment';
				} else {
					$media = null;
				}
				
				//limit meta box to certain post types
				$post_types = array($posts, $pages, $media);
				if ( in_array( $post_type, $post_types )) {
					
					add_meta_box('cartopress_locator', __( 'CartoPress Geolocator', 'cartopress_textdomain' ), array( $this, 'cartopress_geocoder_content' ), $post_type, 'normal', 'high' );
					
					//enqueue dependencies only if geolocator is present
					function load_geocoder_dependencies($location) {
					   if( 'post.php' == $location || 'post-new.php' == $location ) {
					     wp_enqueue_script('jquery2');
						 wp_enqueue_style('google_fonts');
						 wp_enqueue_style('leaflet');
						 wp_enqueue_style('ionicons');
					     wp_enqueue_script(array('cartopress-geocode-script'));
						 wp_enqueue_script(array('cartopress-geocode-helper-script'));
						 wp_enqueue_script('ionicons');
					     wp_enqueue_style(array('cartopress-geocode-styles', 'cartopress-leaflet-styles'));
					  }
					}
					
					add_action( 'admin_enqueue_scripts', 'load_geocoder_dependencies' );
					wp_localize_script('cartopress-geocode-helper-script','cartopress_geocoder_ajax', array(
							"cartopress_delete_row_nonce" => wp_create_nonce('cartopress_delete_row_nonce'),
							"cartopress_resetrecord_nonce" => wp_create_nonce('cartopress_resetrecord_nonce'),
						)
					);
					
				} // end if
				
		} // end cartopress_add_geocoder
		
		
		
		/**
		 * Save the geodata when the post is saved.
		 *
		 * @since 0.1.0
		 * @param int $post_id The ID of the post being saved.
		 */
		public static function save_geodata( $post_id ) {
	
			// Check if the nonce is set.
			if ( ! isset( $_POST['cartopress_inner_custom_box_nonce'] ) ) {
				return $post_id;
			}

			// Verify that the nonce is valid.
			$nonce = $_POST['cartopress_inner_custom_box_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'cartopress_inner_custom_box' ) ) {
				return $post_id;
			}
			
			// If this is an autosave, the form has not been submitted so we don't want to do anything.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}
			
			// Check the user's permissions.
			if ( 'page' == $_POST['post_type'] ) {
				if ( ! current_user_can( 'edit_page', $post_id ) ) {
					return $post_id;
				}
			} else {
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return $post_id;
				}
			} //end if

			// Sanitize the geo data.
			$geodata = array(
				'cp_geo_displayname'=>$_POST['cp_geo_displayname'],
				'cp_geo_lat'=>$_POST['cp_geo_lat'],
				'cp_geo_long'=>$_POST['cp_geo_long'],
				'cp_geo_streetnumber'=>$_POST['cp_geo_streetnumber'],
				'cp_geo_street'=>$_POST['cp_geo_street'],
				'cp_geo_postal'=>$_POST['cp_geo_postal'],
				'cp_geo_adminlevel4_vill_neigh'=>$_POST['cp_geo_adminlevel4_vill_neigh'],
				'cp_geo_adminlevel3_city'=>$_POST['cp_geo_adminlevel3_city'],
				'cp_geo_adminlevel2_county'=>$_POST['cp_geo_adminlevel2_county'],
				'cp_geo_adminlevel1_st_prov_region'=>$_POST['cp_geo_adminlevel1_st_prov_region'],
				'cp_geo_adminlevel0_country'=>$_POST['cp_geo_adminlevel0_country']
			);
			
			foreach($geodata as $key=>$field) {
				$field = sanitize_text_field( $field );
			}
			
			// Sanitize the description field
			$description = sanitize_text_field( $_POST['cp_post_description'] );
			
			// Set the "Do Not Sync" status
			if ($_POST['cpdb-donotsync'] == 1) {
				$donotsync = 1;
			} else {
				$donotsync = 0;
			}
			
			// Update the postmeta fields.
			if (count(array_unique($geodata)) === 1 && end($geodata) === '') { // update geodata only if one of the fields is not empty
				update_post_meta( $post_id, '_cp_post_description', $description );
			} else {
				update_post_meta( $post_id, '_cp_post_geo_data', $geodata );
				update_post_meta( $post_id, '_cp_post_description', $description );
				if ($donotsync == 1) {
					update_post_meta( $post_id, '_cp_post_donotsync', $donotsync );
				} else {
					delete_post_meta($post_id, '_cp_post_donotsync');
				}
			}
			
		} //end save function
		
		/**
		 * Display the Geolocator.
		 *
		 * @since 0.1.0
		 * @param WP_Post $post The post object.
		 */
		public function cartopress_geocoder_content( $post ) {
	
			// Add an nonce field so we can check for it later.
			wp_nonce_field( 'cartopress_inner_custom_box', 'cartopress_inner_custom_box_nonce' );

			// Use get_post_meta to retrieve an existing value from the database.
			$donotsync_value = get_post_meta( $post->ID, '_cp_post_donotsync', true );
			
			//redefine $vars if cartodb values are true
			$cp_post = cartopress_sync::cartodb_select($post->ID);
			
			if ($cp_post[1] == true && $donotsync_value != 1) {
				$cp_values = $cp_post[0]->rows[0];
				$cartodb_id = $cp_values->cartodb_id;
				$cp_geo_displayname = $cp_values->cp_geo_displayname;
				$cp_geo_lat = $cp_values->cp_geo_lat;
				$cp_geo_long = $cp_values->cp_geo_long;
				$cp_geo_streetnumber = $cp_values->cp_geo_streetnumber;
				$cp_geo_street = $cp_values->cp_geo_street;
				$cp_geo_postal = $cp_values->cp_geo_postal;
				$cp_geo_adminlevel4_vill_neigh = $cp_values->cp_geo_adminlevel4_vill_neigh;
				$cp_geo_adminlevel3_city = $cp_values->cp_geo_adminlevel3_city;
				$cp_geo_adminlevel2_county = $cp_values->cp_geo_adminlevel2_county;
				$cp_geo_adminlevel1_st_prov_region = $cp_values->cp_geo_adminlevel1_st_prov_region;
				$cp_geo_adminlevel0_country = $cp_values->cp_geo_adminlevel0_country;
				$cp_post_description = $cp_values->cp_post_description;
			} else {
				$geodata = get_post_meta( $post->ID, '_cp_post_geo_data', true );
				
				if ( $geodata != null) {
					$cartodb_id = null;
					$cp_geo_displayname = $geodata['cp_geo_displayname'];
					$cp_geo_lat = $geodata['cp_geo_lat'];
					$cp_geo_long = $geodata['cp_geo_long'];
					$cp_geo_streetnumber = $geodata['cp_geo_streetnumber'];
					$cp_geo_street = $geodata['cp_geo_street'];
					$cp_geo_postal = $geodata['cp_geo_postal'];
					$cp_geo_adminlevel4_vill_neigh = $geodata['cp_geo_adminlevel4_vill_neigh'];
					$cp_geo_adminlevel3_city = $geodata['cp_geo_adminlevel3_city'];
					$cp_geo_adminlevel2_county = $geodata['cp_geo_adminlevel2_county'];
					$cp_geo_adminlevel1_st_prov_region = $geodata['cp_geo_adminlevel1_st_prov_region'];
					$cp_geo_adminlevel0_country = $geodata['cp_geo_adminlevel0_country'];
					$cp_post_description = get_post_meta( $post->ID, '_cp_post_description', true );
				} else {
					//default values
					$cartodb_id = null;
					$cp_geo_displayname = '';
					$cp_geo_lat = '';
					$cp_geo_long = '';
					$cp_geo_streetnumber = '';
					$cp_geo_street = '';
					$cp_geo_postal = '';
					$cp_geo_adminlevel4_vill_neigh = '';
					$cp_geo_adminlevel3_city = '';
					$cp_geo_adminlevel2_county = '';
					$cp_geo_adminlevel1_st_prov_region = '';
					$cp_geo_adminlevel0_country = '';
					$cp_post_description = '';
				}
				
			}

			// Display the metabox
			echo '<p class="howto">Use the search bar to find lookup an address. Select the correct address from the results, or fill in the location fields manually. Note: Latitude and longitude cooridnates are required.</p>';
			echo '<div id="cpdb-metabox-wrapper">
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
			        		<p id="cpdb-cartodb-id">CartoDB ID: <span id="cartodb-id">'. $cartodb_id .'</span></p>
			        		<input type="checkbox" id="unlock_manual_edit" name="unlock_manual_edit" /><label for="unlock_manual_edit">Allow Geo Data Editing</label>
			        		<section id="cpdb-admin-panel">
			        			<input type="checkbox" id="cpdb-donotsync" name="cpdb-donotsync" value="1" '. checked( 1, $donotsync_value, false ) .'/><label for="cpdb-donotsync" id="cpdb-donotsync-label">Do Not Sync</label>';
			        			echo '<input type="button" id="cpdb-reset-button" class="button" data-post_id="' . $post->ID . '" value="Revert to Saved Data"/>
			        			<input type="button" id="cpdb-delete-button" class="button" data-post_id="' . $post->ID . '" value="Delete Geo Data"/>
			        		</section>
			        		<section id="cpdb-geocode-fields">
			        			<div class="row">
			        				<div class="col-12"><label for="cp_geo_displayname">Location Display Name: </label><span><textarea rows="3" id="cp_geo_displayname" name="cp_geo_displayname" class="disabled" value="' . esc_attr( $cp_geo_displayname ) . '" placeholder="i.e. 200 North 7th Street, Brooklyn, NY" readonly="readonly">' . esc_attr( $cp_geo_displayname ) . '</textarea></span></div>
			        			</div>
			        			<div class="row">
			        				<div class="col-6"><label for="cp_geo_lat">Latitude: </label><span class="smaller" id="span_cp_geo_lat"><input type="text" id="cp_geo_lat" name="cp_geo_lat" class="disabled" value="' . esc_attr( $cp_geo_lat ) . '" placeholder="i.e. 40.7165" readonly="readonly"/></span></div>
			        				<div class="col-6"><label for="cp_geo_long">Longitude: </label><span><input type="text" id="cp_geo_long" name="cp_geo_long" class="disabled" value="' . esc_attr( $cp_geo_long ) . '" placeholder="i.e. -73.9553" readonly="readonly"/></span></div>
			        			</div>
			        			<div class="row">
			        				<div class="col-4"><label for="cp_geo_streetnumber">Address Number: </label><span class="smaller" id="span_cp_geo_streetnumber"><input type="text" id="cp_geo_streetnumber" name="cp_geo_streetnumber" class="disabled" value="' . esc_attr( $cp_geo_streetnumber ) . '" placeholder="i.e. 200" readonly="readonly"/></span></div>
			        				<div class="col-4"><label for="cp_geo_street">Address Street: </label><span><input type="text" id="cp_geo_street" name="cp_geo_street" class="disabled" value="' . esc_attr( $cp_geo_street ) . '" placeholder="i.e. North 7th Street" readonly="readonly"/></span></div>
			        				<div class="col-4"><label for="cp_geo_postal">Postal Code: </label><span class="smaller" id="span_cp_geo_postal"><input type="text" id="cp_geo_postal" name="cp_geo_postal" class="disabled" value="' . esc_attr( $cp_geo_postal ) . '" placeholder="i.e. 11211" readonly="readonly"/></span></div>
			        			</div>
			        			<div class="row">
			        				<div class="col-4"><label for="cp_geo_adminlevel4_vill_neigh">Village/Neighborhood: </label><span><input type="text" id="cp_geo_adminlevel4_vill_neigh" name="cp_geo_adminlevel4_vill_neigh" class="disabled" value="' . esc_attr( $cp_geo_adminlevel4_vill_neigh ) . '" placeholder="i.e. Williamsburg" readonly="readonly"/></span></div>
			        				<div class="col-4"><label for="cp_geo_adminlevel3_city">Town/City: </label><span class="smaller" id="span_cp_geo_adminlevel3_city"><input type="text" id="cp_geo_adminlevel3_city" name="cp_geo_adminlevel3_city" class="disabled" value="' . esc_attr( $cp_geo_adminlevel3_city ) . '" placeholder="i.e. Brooklyn" readonly="readonly"/></span></div>
			        				<div class="col-4"><label for="cp_geo_adminlevel2_county">County/District: </label><span><input type="text" id="cp_geo_adminlevel2_county" name="cp_geo_adminlevel2_county" class="disabled" value="' . esc_attr( $cp_geo_adminlevel2_county ) . '" placeholder="i.e. Kings" readonly="readonly"/></span></div>
			        			</div>
			        			<div class="row">
			        				<div class="col-6"><label for="cp_geo_adminlevel1_st_prov_region">State/Province/Region: </label><span class="smaller" id="span_cp_geo_adminlevel1_st_prov_region"><input type="text" id="cp_geo_adminlevel1_st_prov_region" name="cp_geo_adminlevel1_st_prov_region" class="disabled" value="' . esc_attr( $cp_geo_adminlevel1_st_prov_region ) . '" placeholder="i.e. New York" readonly="readonly"/></span></div>
			        				<div class="col-6"><label for="cp_geo_adminlevel0_country">Country: </label><span><input type="text" id="cp_geo_adminlevel0_country" name="cp_geo_adminlevel0_country" class="disabled" value="' . esc_attr( $cp_geo_adminlevel0_country ) . '" placeholder="i.e. United States" readonly="readonly"/></span></div>
			        			</div>
			        		</section>
			        		<h4>Summary Description</h4>
			        		<p class="howto">Add a custom summary description which will be available for display in the CartoDB infowindow. If you leave it blank, CartoPress will attempt to use the post excerpt. If the excerpt is empty, CartoPress will create a summary description using the first 55 words of the post content.</p>
			        		<section>
			        			<textarea placeholder="Enter a Summary Description" name="cp_post_description" id="cp_post_description">' . esc_attr( $cp_post_description ) . '</textarea>
			        		</section>';
							
							if (empty($geodata)) {
								echo '<div id="comments"></div>';
							} elseif (!empty($geodata) && $cp_post[1] == false && $donotsync_value != 1) {
								echo '<div id="comments"><p class="warning">Geo data is not synced with CartoDB. Update the post to sync.</p></div>';
							} elseif (!empty($geodata) && $cp_post[1] == true && $donotsync_value != 1) {
								echo '<div id="comments"><p class="success">Geo data is synced with CartoDB.</p></div>';
							} elseif ($donotsync_value == 1) {
								echo '<div id="comments"><p class="warning">"Do Not Sync" is currently set to True. Data is not being synced to CartoDB.</p></div>';
							}
			        		
			        	echo '</div>
			        </div>
			        <div style="clear:both;"></div>
			 </div>';
		
		} //end cartopress_geocoder_content
		
	} // end class geocoder metabox
		
} //end if class exists
?>
