<?php
/**
 * CartoPress Locations
 * 
 * The main Geocoder class.
 *
 * @package cartopress
 */
 
if (!class_exists('geocoder_metabox')) {
	
	 /** 
	  * Geocoder metabox class
	  * 
	  *	@since 0.1.0
	  */
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
				if (isset($cpoptions['cartopress_collect_posts']) && $cpoptions['cartopress_collect_posts'] == 1) {
					$posts = 'post';
				} else {
					$posts = null;
				}
				if (isset($cpoptions['cartopress_collect_pages']) && $cpoptions['cartopress_collect_pages'] == 1) {
					$pages = 'page';
				} else {
					$pages = null;
				}
				if (isset($cpoptions['cartopress_collect_media']) && $cpoptions['cartopress_collect_media'] == 1) {
					$media = 'attachment';
				} else {
					$media = null;
				}
				
				//limit meta box to certain post types
				$post_types = array($posts, $pages, $media);
				if ( in_array( $post_type, $post_types )) {
					
					add_meta_box('cartopress_locator', __( 'CartoPress Geolocator', 'cartopress_textdomain' ), array( $this, 'cartopress_geocoder_content' ), $post_type, 'normal', 'high' );
					
					/**
					 * Enqueue dependencies only if geolocator is present
					 * 
					 * @param string $location The php file to which to load to
					 */
					function load_geocoder_dependencies($location) {
					   if( 'post.php' == $location || 'post-new.php' == $location ) {
					     wp_enqueue_script('jquery');
						 wp_enqueue_style('google_fonts');
						 wp_enqueue_style('cartopress-leaflet');
					     wp_enqueue_script(array('cartopress-geocode-script'));
						 wp_enqueue_script(array('cartopress-geocode-helper-script'));
						 wp_enqueue_script('cartopress-leaflet-markers');
						 wp_enqueue_script('cartopress-leaflet');
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
			if (isset($_POST['cpdb-donotsync']) && $_POST['cpdb-donotsync'] == 1) {
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
			$geodata = get_post_meta( $post->ID, '_cp_post_geo_data', true );
			
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
			include(CARTOPRESS_ADMIN_DIR . 'cp-geocoderview.php');
		
		} //end cartopress_geocoder_content
		
	} // end class geocoder metabox
		
} //end if class exists
?>
