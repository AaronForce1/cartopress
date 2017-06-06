<?php
/**
 * CartoPress Sync
 *
 * Methods for performing SQL queries with CartoDB. File contains cartopress_sync class.
 * 
 * @package cartopress
 * 
 */
 

if (!class_exists('cartopress_sync')) {
	
	 /** 
	  * Syncs to CartoDB
	  * 
	  * Defines static methods for syncing to CartoDB.
	  * 
	  *	@since 0.1.0
	  */
	class cartopress_sync {
		
		/**
		* Perform CartoDB queries
		*
		* Uses curl to connect to CartoDB. 
		*
		* @since 0.1.0
		* @todo This should be eventually replaced with the WordPress HTTP API instead of calling curl directly.
		* @param string $sql The SQL query. Use PostgreSQL for CartoDB.
		* @param string $username CartoDB username. Either the global constant or POST variable if used in AJAX.
		* @param string $apikey CartoDB API Key. Either the global constant or POST variable if used in AJAX.
		* @param boolean $return _Optional._ Setting to false will reset CURL_RETURNTRANSFER to false.
		* @return array $result _A php decoded json object._ 
		*/
		public static function update_cartodb($sql, $apikey, $username, $return = true){
			//curl init
			$ch = curl_init("https://" . $username . ".carto.com/api/v2/sql");
			$query = http_build_query(array('q'=>$sql,'api_key'=>$apikey));
			
			//curl opts
			curl_setopt($ch, CURLOPT_POST, TRUE);
		   	curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
		   	curl_setopt($ch, CURLOPT_RETURNTRANSFER, $return);
			
			//process results
			$result = curl_exec($ch);
			curl_close($ch);
			$result = json_decode($result);
			
			//return results
			return $result;
		} //end update_cartodb()
		
		
		/**
		* Create table in CartoDB
		*
		* AJAX handler for generating CartoDB tables from the settings page.
		*
		* @since 0.1.0
		* @return string Sends a string response to the ajax function.
		* <ul>
		* 	<li>_exists:_ If the dataset already exists in CartoDB</li>
		* 	<li>_notfound:_ Usually the result of a 500 error if the username is not set or incorrect</li>
		* 	<li>_badapikey:_ If the username is correct but the API Key auth is incorrect</li>
		* 	<li>_specialchar:_ The table name input contains invalid characters</li>
		* 	<li>_success:_ The table was successfully created</li>
		* 	<li>_Unknown error:_ Catch all for other error types, prints the response directly</li>
		* </ul>
		*/
		public static function cartopress_generate_table() {
		   	
		   // checks the referer to ensure authorized access
		   if (!isset( $_POST['cartopress_admin_nonce'] ) || !wp_verify_nonce($_POST['cartopress_admin_nonce'], 'cartopress_admin_nonce') )
		   	die('Unauthorized access denied.');
		   
		   // defines the post vars
		   $apikey = $_POST['apikey'];
		   $username = $_POST['username'];
		   $tablename = $_POST['tablename'];
		   
		   //SQL create table statment
		   $sql_create = "DO $$ BEGIN CREATE TABLE " . $tablename . " (cp_post_id integer, cp_post_title text, cp_post_content text, cp_post_description text, cp_post_date date, cp_post_type text, cp_post_permalink text, cp_post_categories text, cp_post_tags text, cp_post_featuredimage_url text, cp_post_format text, cp_post_author text, cp_geo_streetnumber text, cp_geo_street text, cp_geo_adminlevel4_vill_neigh text, cp_geo_adminlevel3_city text, cp_geo_adminlevel2_county text, cp_geo_adminlevel1_st_prov_region text, cp_geo_adminlevel0_country text, cp_geo_postal text, cp_geo_lat float, cp_geo_long float, cp_geo_displayname text); RAISE NOTICE 'Success'; END; $$";
		   
		   //process curl
		   $result_create = cartopress_sync::update_cartodb($sql_create, $apikey, $username, true);
		   
		   //parse result
		   if ($result_create) {
			   $error = $result_create->error[0];
			   $success = $result_create->notices[0];
		   } else {
		   	   $error = null;
			   $success = null;
		   }
		   
		   if ($error == (string)('relation "' . $tablename . '" already exists')) {
			   update_option('cartopress_cartodb_verified', 'notverified');
		   	   die('exists');
		   
		   } elseif ($result_create == NULL) {
				update_option('cartopress_cartodb_verified', 'notverified');
		   		die('notfound');
				
		   } elseif ($error == "permission denied for schema public") {
				update_option('cartopress_cartodb_verified', 'notverified');
		   		die('badapikey');
		  
		   } elseif (fnmatch('syntax error at or near "*"' , $error)) {
				update_option('cartopress_cartodb_verified', 'notverified');
		   		die('specialchar');
		   
		   } elseif ($success == 'Success')  {
				$sql_select = "SELECT cdb_cartodbfytable('" . (string)$tablename . "');";
				cartopress_sync::update_cartodb($sql_select, $apikey, $username, true);
				update_option('cartopress_cartodb_verified', 'verified');
				die('success');
		   
		   } else {
				die('Unknown error: ' . print_r($result_create) );
		   }
		   
		} //end cartopress_generate__table()
		
		/**
		* Checks for and adds required default CartoPress columns to CartoDB table
		*
		* AJAX handler for formatting new and existing tables into the CartoPress schema.
		* 
		* @since 0.1.0
		* @return string Sends a string response to the ajax function.
		* <ul>
		*	<li>_success:_ The function was completed successfully</li>
		* </ul>
		*/
		public static function cartopress_cartopressify_table() {
		   	
		   // checks the referer to ensure authorized access
		   if (!isset( $_POST['cartopress_cartopressify_nonce'] ) || !wp_verify_nonce($_POST['cartopress_cartopressify_nonce'], 'cartopress_cartopressify_nonce') )
		   	die('Unauthorized access denied.');
		   
		   $apikey = $_POST['apikey'];
		   $username = $_POST['username'];
		   $tablename = $_POST['tablename'];
		   $args = array(
		   		array('name' => 'cp_post_id', 'type' => 'integer'), 
		   		array('name' => 'cp_post_title', 'type' => 'text'), 
		   		array('name' => 'cp_post_content', 'type' => 'text'),
		   		array('name' => 'cp_post_description', 'type' => 'text'),
		   		array('name' => 'cp_post_date', 'type' => 'date'),
		   		array('name' => 'cp_post_type', 'type' => 'text'),
		   		array('name' => 'cp_post_permalink', 'type' => 'text'),
		   		array('name' => 'cp_post_categories', 'type' => 'text'),
		   		array('name' => 'cp_post_tags', 'type' => 'text'),
		   		array('name' => 'cp_post_featuredimage_url', 'type' => 'text'),
		   		array('name' => 'cp_post_format', 'type' => 'text'),
		   		array('name' => 'cp_post_author', 'type' => 'text'),
		   		array('name' => 'cp_geo_streetnumber', 'type' => 'text'),
		   		array('name' => 'cp_geo_street', 'type' => 'text'),
		   		array('name' => 'cp_geo_adminlevel4_vill_neigh', 'type' => 'text'),
		   		array('name' => 'cp_geo_adminlevel3_city', 'type' => 'text'),
		   		array('name' => 'cp_geo_adminlevel2_county', 'type' => 'text'),
		   		array('name' => 'cp_geo_adminlevel1_st_prov_region', 'type' => 'text'),
				array('name' => 'cp_geo_adminlevel0_country', 'type' => 'text'),
				array('name' => 'cp_geo_postal', 'type' => 'text'),
				array('name' => 'cp_geo_lat', 'type' => 'float'),
				array('name' => 'cp_geo_long', 'type' => 'float'),
				array('name' => 'cp_geo_displayname', 'type' => 'text'),
			);
		   
		   foreach ($args as $col) {
		   		$col_name = $col['name'];
			    $col_type = $col['type'];
		   		
		   		$cartopressify = "DO $$ BEGIN ALTER TABLE " . $tablename . " ADD COLUMN " . $col_name . " " . $col_type . "; RAISE NOTICE 'Column " . $col_name . " was created.'; UPDATE " . $tablename . " SET " . $col_name . " = " . $col_name . "; RAISE NOTICE 'Column name is set.'; EXCEPTION WHEN duplicate_column THEN RAISE NOTICE '" . $col_name . " text already exists in " . $tablename . ".'; END; $$";	
		   		
		   		$result = cartopress_sync::update_cartodb($cartopressify, $apikey, $username, true);
		   }
		   update_option('cartopress_cartodb_verified', 'verified');
		   die("Success");  
		   
		} //end cartopressify()
		
		/**
		* Create column in CartoDB and save the settings
		*
		* AJAX handler for adding custom field columns to the CartoDB table.
		*
		* @since 0.1.0
		* @return array $return Encoded json containing a user message, option_status boolean, cartodb status boolean
		*/
		public static function cartopress_create_column() {
		   	
		   // checks the referer to ensure authorized access
		   if (!isset( $_POST['cartopress_create_column_nonce'] ) || !wp_verify_nonce($_POST['cartopress_create_column_nonce'], 'cartopress_create_column_nonce') )
		   	die('Unauthorized access denied.');
		   
		   $apikey = $_POST['apikey'];
		   $username = $_POST['username'];
		   $tablename = $_POST['tablename'];
		   $cartodb_column = $_POST['cartodb_column'];
		   $custom_field = $_POST['custom_field'];
		   
		   $sql_add_custom_col = "DO $$ BEGIN ALTER TABLE " . $tablename . " ADD COLUMN " . $cartodb_column . " text; RAISE NOTICE 'Column Created'; UPDATE " . $tablename . " SET " . $cartodb_column . " = " . $cartodb_column . "; RAISE NOTICE 'Column name is set.'; EXCEPTION WHEN duplicate_column THEN RAISE NOTICE 'Column Exists'; END; $$";	
		   $result = cartopress_sync::update_cartodb($sql_add_custom_col, $apikey, $username, true);
		   $notice = $result->notices[0];
		   
		   if ($notice == "Column Exists") {
		   		$message = "CartoPress will resume syncing Custom Field \"" . $custom_field . "\" with CartoDB column \"" . $cartodb_column . "\"";
		  		$option = cartopress_settings::update_customfield_settings($custom_field, $cartodb_column, 'set');
				$cdb_status = true;
		   } elseif ($notice == "Column Created") {
			   	$message = "The column \"" . $cartodb_column . "\" has been created in your CartoDB table. Custom Field \"" . $custom_field . "\" will sync to it.";
		   		$option = cartopress_settings::update_customfield_settings($custom_field, $cartodb_column, 'set');
		   		$cdb_status = true;
		   } else {
		   		$message = "An error occurred connecting to your CartoDB table and the column could not be created.";
		   		$option = false;
				$cdb_status = false;
		   }
		   $return = array('message'=>$message, 'option_status'=>$option, 'cdb_status'=>$cdb_status);
		   return print_r(json_encode($return));
		   die();
		} //end cartopress_create_column()
		
		/**
		* Delete column in CartoDB and save the settings
		*
		* AJAX handler for deleting custom field columns from the CartoDB table.
		*
		* @since 0.1.0
		* @return array $return Encoded json containing a user message, option_status boolean, cartodb status boolean
		*/
		public static function cartopress_delete_column() {
		   	
		   // checks the referer to ensure authorized access
		   if (!isset( $_POST['cartopress_delete_column_nonce'] ) || !wp_verify_nonce($_POST['cartopress_delete_column_nonce'], 'cartopress_delete_column_nonce') )
		   	die('Unauthorized access denied.');
		   
		   $apikey = $_POST['apikey'];
		   $username = $_POST['username'];
		   $tablename = $_POST['tablename'];
		   $cartodb_column = $_POST['cartodb_column'];
		   $custom_field = $_POST['custom_field'];
		   
		   $sql_delete_custom_col = "DO $$ BEGIN ALTER TABLE " . $tablename . " DROP COLUMN " . $cartodb_column . "; RAISE NOTICE 'Column Dropped'; END; $$";	
		   $result = cartopress_sync::update_cartodb($sql_delete_custom_col, $apikey, $username, true);
		   $notice = $result->notices[0];
		   
		   if ($notice == "Column Dropped") {
		   		$message = "The CartoDB column " . $cartodb_column . " has been deleted";
		  		$option = cartopress_settings::update_customfield_settings(null, $cartodb_column, 'unset');
		   } else {
		   		$message = "An error occurred and your column could not be deleted. Your settings have not been changed.";
		   }
		   $return = array('message'=>$message, 'option_status'=>$option);
		   return print_r(json_encode($return));
		   die();
		} //end cartopress_delete_column()
				
		
		/**
		 * Select row in CartoDB
		 * 
		 * Used to get CartoDB information for any given Post ID.
		 * 
		 * **Example:** _Gets the CartoDB ID for a Post_
		 * `$cp_post = cartopress_sync::update_cartodb($sql_select, CARTOPRESS_APIKEY, CARTOPRESS_USERNAME, true);`
		 *
		 * `$cp_values = $cp_post[0]->rows[0];`
		 * 
		 * `$cartodb_id = $cp_values->cartodb_id;`
		 *
		 * @since 0.1.0
		 * @param $post_id The post id of the WP post.
		 * @return array $cp_post, $status Returns decoded json data in [0] and boolean value if single row is selected in [1]
		 */
		public static function cartodb_select($post_id) {
			$sql_select = 'SELECT * FROM ' . CARTOPRESS_TABLE . ' WHERE cp_post_id = ' . $post_id;
			$cp_post = cartopress_sync::update_cartodb($sql_select, CARTOPRESS_APIKEY, CARTOPRESS_USERNAME, true);
			if ($cp_post && $cp_post->total_rows == 1) {
				$status = true;
			} else {
				$status = false;
			}
			return array($cp_post, $status);
		} // end CartoDB select
	
	
		/**
		 * Add or update row in CartoDB
		 * 
		 * The main sync function when saving and adding posts. Only syncs to CartoDB if latitude and longitude data are present and the Post Status is set to 'Publish.' Will delete from CartoDB if Post Status is changed from 'Publish' to any other format.
		 *
		 * @since 0.1.0
		 * @param $post_id The post id of the WP post.
		 * @return void
		 */
		public static function cartodb_sync($post_id) {
			$args = get_cartopress_sync_fields($post_id);
			
			// removes null fields and creates array for inserting null values
			$args_remove = array();
			foreach($args as $key=>$value) {
				if(is_null($value) || $value == '') {
			        unset($args[$key]);
					$args_remove[$key] = 'null';
				}
			}
			// determines whether add or update and executes
			if (is_null($args['cp_geo_lat']) || $args['cp_geo_lat'] == '' || is_null($args['cp_geo_long']) || $args['cp_geo_long'] == '') {
				return;
			} else {
				$sql_verifyupdate = 'SELECT COUNT(*) FROM ' . CARTOPRESS_TABLE . ' WHERE cp_post_id = ' .$post_id;
				$count = cartopress_sync::update_cartodb($sql_verifyupdate, CARTOPRESS_APIKEY, CARTOPRESS_USERNAME, true);
				if (!$count) {
					return;
				} else {
					$count = $count->rows[0]->count;
				}
				
				if ($count == 0) {
					$sql_insert = sprintf('INSERT INTO ' . CARTOPRESS_TABLE . '(%s, the_geom) VALUES (\'%s\', (CDB_LatLng(' . $args['cp_geo_lat'] . ', ' . $args['cp_geo_long'] . ')));', implode(', ',array_keys($args)), implode('\', \'',array_values($args)) );
					cartopress_sync::update_cartodb($sql_insert, CARTOPRESS_APIKEY, CARTOPRESS_USERNAME, true);
				} 
				if ($count >= 2) {
					wp_die('Multiple records exist for Post ID: ' . $post_id . '. Please check your CartoDB table and eliminate any duplicates.');
				} 
				if ($count == 1) {
					$sql_update = 'UPDATE ' . CARTOPRESS_TABLE . ' SET the_geom = CDB_LatLng(' . $args['cp_geo_lat'] . ', ' . $args['cp_geo_long'] . '), ';
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
					cartopress_sync::update_cartodb($sql_update, CARTOPRESS_APIKEY, CARTOPRESS_USERNAME, true);
				}
			} //end if
		} // end cartodb sync
		
		
		/**
		 * Delete row from CartoDB
		 * 
		 * Method for deleting from CartoDB. Primarily used when Post Status is changed to anything but Publish and when trashing.
		 *
		 * @since 0.1.0
		 * @param $post_id The post id of the WP post.
		 */
		public static function cartodb_delete($post_id) {
			$sql_verifyupdate = 'SELECT COUNT(*) FROM ' . CARTOPRESS_TABLE . ' WHERE cp_post_id = ' .$post_id;
			$count = cartopress_sync::update_cartodb($sql_verifyupdate, CARTOPRESS_APIKEY, CARTOPRESS_USERNAME, true);
			if (!$count) {
				return;
			} else {
				$count = $count->rows[0]->count;
			}
			
			if ($count == 0) {
				return false;
			} 
			if ($count >= 2) {
				wp_die('Multiple records exist for Post ID: ' . $post_id . '. Please check your CartoDB table and eliminate any duplicates.');
			} 
			if ($count == 1) {
				$sql_delete = 'DELETE FROM ' . CARTOPRESS_TABLE .  ' WHERE cp_post_id = ' . $post_id;
				cartopress_sync::update_cartodb($sql_delete, CARTOPRESS_APIKEY, CARTOPRESS_USERNAME, true);
			}
			
		} //end cartodb delete
		
		
		/**
		 * Delete row from CartoDB
		 * 
		 * AJAX handler for removing all geo data. Deletes both CartoDB record and all geo data stored in wp_postmeta.
		 *
		 * @since 0.1.0
		 * @return string $message Reponse message indicating success or error
		 */
		public static function cartopress_delete_row() {
		   // checks the referer to ensure authorized access
		   if (!isset( $_POST['cartopress_delete_row_nonce'] ) || !wp_verify_nonce($_POST['cartopress_delete_row_nonce'], 'cartopress_delete_row_nonce') )
		   	die('Unauthorized access denied.');
		   $post_id = $_POST['post_id'];
		   $sql_delete = 'DELETE FROM ' . CARTOPRESS_TABLE .  ' WHERE cp_post_id = ' . $post_id;
		   $results = cartopress_sync::update_cartodb($sql_delete, CARTOPRESS_APIKEY, CARTOPRESS_USERNAME, true);
		   if ($results && $results->total_rows == 1) {
		   	$message = "success";
		   } else {
		   	$message = "An unknown error occured: " . $results;
		   }
		   delete_post_meta($post_id, '_cp_post_geo_data');
		   die(print_r($message, true));
		} //end cartopress_delete_row()
		
		
		/**
		 * Resets the geodata to most recently saved.
		 * 
		 * AJAX handler to retrieve the most recent geodata saved in wp_postmeta.
		 *
		 * @since 0.1.0
		 * @return array Encoded json data for use in AJAX to populate the geo fields of the geocoder
		 */
		public static function cartopress_reset_record() {
		   // checks the referer to ensure authorized access
		   if (!isset( $_POST['cartopress_resetrecord_nonce'] ) || !wp_verify_nonce($_POST['cartopress_resetrecord_nonce'], 'cartopress_resetrecord_nonce') )
		   	die('Unauthorized access denied.');
		   $post_id = $_POST['post_id'];
		   $geodata = get_post_meta( $post_id, '_cp_post_geo_data', true );
		   $geofields = array(
			   'cp_geo_displayname' => $geodata['cp_geo_displayname'],
			   'cp_geo_lat' => $geodata['cp_geo_lat'],
			   'cp_geo_long' => $geodata['cp_geo_long'],
			   'cp_geo_streetnumber' => $geodata['cp_geo_streetnumber'],
			   'cp_geo_street' => $geodata['cp_geo_street'],
			   'cp_geo_postal' => $geodata['cp_geo_postal'],
			   'cp_geo_adminlevel4_vill_neigh' => $geodata['cp_geo_adminlevel4_vill_neigh'],
			   'cp_geo_adminlevel3_city' => $geodata['cp_geo_adminlevel3_city'],
			   'cp_geo_adminlevel2_county' => $geodata['cp_geo_adminlevel2_county'],
			   'cp_geo_adminlevel1_st_prov_region' => $geodata['cp_geo_adminlevel1_st_prov_region'],
			   'cp_geo_adminlevel0_country' => $geodata['cp_geo_adminlevel0_country']
		   );
		   die(print_r(json_encode($geofields), true));
		} //end cartopress_delete_row()
		
		/**
		 * Updates the geo field data stored in wp_postmeta with the values from CartoDB.
		 * 
		 * AJAX handler to update the geodata saved in wp_postmeta with the corresponding CartoDB value.
		 *
		 * @since 0.1.0
		 */
		public static function cartopress_update_postmeta($post_id){
			$cp_post = cartopress_sync::cartodb_select($post_id);
			$donotsync_value = get_post_meta( $post_id, '_cp_post_donotsync', true );
			if ($cp_post[1] == true && $donotsync_value != 1) {
				$cp_values = $cp_post[0]->rows[0];
				
				$geodata = array(
					'cp_geo_displayname' => $cp_values->cp_geo_displayname,
					'cp_geo_lat' => $cp_values->cp_geo_lat,
					'cp_geo_long' => $cp_values->cp_geo_long,
					'cp_geo_streetnumber' => $cp_values->cp_geo_streetnumber,
					'cp_geo_street' => $cp_values->cp_geo_street,
					'cp_geo_postal' => $cp_values->cp_geo_postal,
					'cp_geo_adminlevel4_vill_neigh' => $cp_values->cp_geo_adminlevel4_vill_neigh,
					'cp_geo_adminlevel3_city' => $cp_values->cp_geo_adminlevel3_city,
					'cp_geo_adminlevel2_county' => $cp_values->cp_geo_adminlevel2_county,
					'cp_geo_adminlevel1_st_prov_region' => $cp_values->cp_geo_adminlevel1_st_prov_region,
					'cp_geo_adminlevel0_country' => $cp_values->cp_geo_adminlevel0_country
				);
				update_post_meta( $post_id, '_cp_post_geo_data', $geodata );
				
			} else {
				//default values
				$geodata = array(
					'cp_geo_displayname' => '',
					'cp_geo_lat' => '',
					'cp_geo_long' => '',
					'cp_geo_streetnumber' => '',
					'cp_geo_street' => '',
					'cp_geo_postal' => '',
					'cp_geo_adminlevel4_vill_neigh' => '',
					'cp_geo_adminlevel3_city' => '',
					'cp_geo_adminlevel2_county' => '',
					'cp_geo_adminlevel1_st_prov_region' => '',
					'cp_geo_adminlevel0_country' => ''
				);
			} //end if
			
		} //end cartopress_update_postmeta()
		
			
	} // end class cartopress_sync
		
} //end if class exists
?>