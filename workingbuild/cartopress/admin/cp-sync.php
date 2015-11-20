<?php
/**
 * CartoPress Sync
 *
 * @package cartopress
 */
 
 /* syncs to CartoDB
 *	@since 0.1.0
 */

if (!class_exists('cartopress_sync')) {

	class cartopress_sync {
		/**
		 * Select row in CartoDB
		 *
		 * @since 0.1.0
		 * @param $post_id The post id of the WP post.
		 */
		public static function cartodb_select($post_id) {
			$sql_select = 'SELECT * FROM ' . cartopress_table . ' WHERE cp_post_id = ' . $post_id;
			$cp_post = cartopress::process_curl($sql_select, cartopress_apikey, cartopress_username, true);
			if ($cp_post->total_rows == 1) {
				$status = true;
			} else {
				$status = false;
			}
			return array($cp_post, $status);
		} // end CartoDB select
	
	
	
		/**
		 * Add to or Update CartoDB
		 *
		 * @since 0.1.0
		 * @param $post_id The post id of the WP post.
		 */
		public static function cartodb_sync($post_id) {
			
			//defining the post objects to insert
			$cp_post_id = $post_id;
			$cp_post_content = get_post_field('post_content', $post_id);
			$cp_post_type = get_post_type($post_id);
			$cp_post_title = get_the_title($post_id);
			$cp_post_permalink = get_permalink($post_id);
			$cp_post_date = get_the_date(get_option('date_format'), $post_id);
			
			if (!empty(get_post_meta( $post_id, '_cp_post_description', true ))) {
				$cp_post_description = get_post_meta( $post_id, '_cp_post_description', true );
			} else {
				if (!empty(get_the_excerpt($post_id))) {
					$cp_post_description = get_the_excerpt($post_id);
				} else {
					$cp_post_description = wp_trim_words( $cp_post_content, 55, '...' );
				} //end if
			} //end if
			
			//defines objects based on user settings
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			if ($cpoptions['cartopress_sync_categories'] == 1) {
				$cats = array();
				foreach(wp_get_post_categories($post_id) as $c) {
					$cat = get_category($c);
					array_push($cats,$cat->name);
					if(sizeOf($cats)>0) {
						$cp_post_categories = implode(', ',$cats);
					} else {
						$cp_post_categories = null;
					}
				} //end foreach
			} else {
				$cp_post_categories = null;
			} //end if
			
			if ($cpoptions['cartopress_sync_tags'] == 1) {
				$tags = array();
				foreach(wp_get_post_tags($post_id) as $tag) {
					array_push($tags,$tag->name);
					if(sizeOf($tags)>0) {
						$cp_post_tags = implode(', ',$tags);
					} else {
						$cp_post_tags = null;
					}
				} //end foreach
			} else {
				$cp_post_tags = null;
			} //end if
			
			if ($cpoptions['cartopress_sync_featuredimage'] == 1) {
				$post_type = get_post_type($post_id);
				if ('attachment' === $post_type) {
					$cp_post_featuredimage_url = wp_get_attachment_url($post_id);
				} else {
					if (has_post_thumbnail( $post_id )) {
						$cp_post_featuredimage_url = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'single-post-thumbnail' );
						$cp_post_featuredimage_url = $cp_post_featuredimage_url[0];
					} else {
						$cp_post_featuredimage_url = null;
					} //end if
				} // end if
			} else {
				$cp_post_featuredimage_url = null;
			} //end if
			
			if ($cpoptions['cartopress_sync_format'] == 1) {
				$cp_post_format = get_post_format( $post_id );
				if ( false === $cp_post_format ) {
					$cp_post_format = 'standard';
				}
			} else {
				$cp_post_format = null;
			} //end if
			
			if ($cpoptions['cartopress_sync_author'] == 1) {
				$the_author_id = get_post_field( 'post_author', $post_id );
				$cp_post_author = get_the_author_meta( 'display_name', $the_author_id );
			} else {
				$cp_post_author = null;
			} //end if
			
			if ($cpoptions['cartopress_sync_customfields'] == 1) {
				$customfield_options = get_option('cartopress_custom_fields');
				$fields = array();
				foreach ($customfield_options as $key=>$value) {
					if ($value['sync'] == 1) {
						$adds = array($key => get_post_meta($post_id, $value['custom_field'], true));
						$fields = array_merge($fields, $adds);
					}
				}
			}
			
			// defines the geodata objects to insert
			$geodata = get_post_meta( $post_id, '_cp_post_geo_data', true );
			
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
			
			// set up the fields
			$args = array(
				'cp_post_id' => $cp_post_id,
				'cp_post_title' => esc_html($cp_post_title),
				'cp_post_content' => esc_html($cp_post_content),
				'cp_post_description' => esc_html($cp_post_description),
				'cp_post_date' => $cp_post_date,
				'cp_post_type' => $cp_post_type,
				'cp_post_permalink' => $cp_post_permalink,
				'cp_post_categories' => esc_html($cp_post_categories), 
				'cp_post_tags' => esc_html($cp_post_tags),
				'cp_post_featuredimage_url' => $cp_post_featuredimage_url,
				'cp_post_format' => $cp_post_format,
				'cp_post_author' => $cp_post_author,
				'cp_geo_displayname' => $cp_geo_displayname,
				'cp_geo_streetnumber' => $cp_geo_streetnumber,
				'cp_geo_street' => $cp_geo_street,
				'cp_geo_postal' => $cp_geo_postal,
				'cp_geo_adminlevel4_vill_neigh' => $cp_geo_adminlevel4_vill_neigh,
				'cp_geo_adminlevel3_city' => $cp_geo_adminlevel3_city,
				'cp_geo_adminlevel2_county' => $cp_geo_adminlevel2_county,
				'cp_geo_adminlevel1_st_prov_region' => $cp_geo_adminlevel1_st_prov_region,
				'cp_geo_adminlevel0_country' => $cp_geo_adminlevel0_country,
				'cp_geo_lat' => $cp_geo_lat,
				'cp_geo_long' => $cp_geo_long
				);
			
			// merge in custom fields
			$args = array_merge($args, $fields);
			
			// removes null fields and creates array for inserting null values
			$args_remove = array();
			foreach($args as $key=>$value) {
				if(is_null($value) || $value == '') {
			        unset($args[$key]);
					$args_remove[$key] = 'null';
				}
			}
			
			// determines whether or not to run the curl process and runs it
			if (is_null($cp_geo_lat) || $cp_geo_lat == '' || is_null($cp_geo_long) || $cp_geo_long == '') {
				return;
			} else {
				$sql_verifyupdate = 'SELECT COUNT(*) FROM ' . cartopress_table . ' WHERE cp_post_id = ' .$post_id;
				$count = cartopress::process_curl($sql_verifyupdate, cartopress_apikey, cartopress_username, true);
				$count = $count->rows[0]->count;
				
				if ($count == 0) {
					$sql_insert = sprintf('INSERT INTO ' . cartopress_table . '(%s, the_geom) VALUES (\'%s\', (CDB_LatLng('.$cp_geo_lat.', '.$cp_geo_long.')));', implode(', ',array_keys($args)), implode('\', \'',array_values($args)) );
					cartopress::process_curl($sql_insert, cartopress_apikey, cartopress_username, true);
				} 
				if ($count >= 2) {
					wp_die('Multiple records exist for Post ID: ' . $post_id . '. Please check your CartoDB table and eliminate any duplicates.');
				} 
				if ($count == 1) {
					$sql_update = 'UPDATE ' . cartopress_table . ' SET the_geom = CDB_LatLng('.$cp_geo_lat.', '.$cp_geo_long.'), ';
					foreach($args as $key=>$value) {
					   if(is_numeric($value))
					      $sql_update .= $key . " = " . $value . ", "; 
					   else
					      $sql_update .= $key . " = " . "'" . $value . "'" . ", "; 
					}
					foreach($args_remove as $key=>$value) {
					   $sql_update .= $key . " = NULLIF(" . $value . ", 'null'), "; //inserts null values
					}
					$sql_update = trim($sql_update, ' ');
					$sql_update = trim($sql_update, ',');
					$sql_update .= ' WHERE cp_post_id = ' . $post_id;
					
					cartopress::process_curl($sql_update, cartopress_apikey, cartopress_username, true);
				}
			} //end if
			
			
		} // end cartodb sync
		
		/**
		 * Delete from CartoDB
		 *
		 * @since 0.1.0
		 * @param $post_id The post id of the WP post.
		 */
		public static function cartodb_delete($post_id) {
			$sql_verifyupdate = 'SELECT COUNT(*) FROM ' . cartopress_table . ' WHERE cp_post_id = ' .$post_id;
			$count = cartopress::process_curl($sql_verifyupdate, cartopress_apikey, cartopress_username, true);
			$count = $count->rows[0]->count;
			
			if ($count == 0) {
				return false;
			} 
			if ($count >= 2) {
				wp_die('Multiple records exist for Post ID: ' . $post_id . '. Please check your CartoDB table and eliminate any duplicates.');
			} 
			if ($count == 1) {
				$sql_delete = 'DELETE FROM ' . cartopress_table .  ' WHERE cp_post_id = ' . $post_id;
				cartopress::process_curl($sql_delete, cartopress_apikey, cartopress_username, true);
			}
			
		} //end cartodb delete
		
			
	} // end class cartopress_sync
		
} //end if class exists
?>