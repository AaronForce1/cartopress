// create the database tables @since 0.1.0
		public static function cartopress_install() {
			global $wpdb;
			global $cartopress_db_version;
			$cartopress_db_version = '1.0';
		
			$table_one = $wpdb->prefix . 'cartopress_options';
			$table_two = $wpdb->prefix . 'cartopress_georeference';
			
			$charset_collate = $wpdb->get_charset_collate();
		
			$sql = "CREATE TABLE $table_one (
				cartopress_option_id mediumint(9) NOT NULL AUTO_INCREMENT,
				cartopress_option_name tinytext NOT NULL,
				cartopress_option_value text NOT NULL,
				UNIQUE KEY id (cartopress_option_id)
			) $charset_collate;
			
			CREATE TABLE $table_two (
				geoid mediumint(9) NOT NULL AUTO_INCREMENT,
				geoid_postid mediumint NOT NULL,
				geoid_post_type text NOT NULL,
				time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				lat decimal(9,7) NOT NULL,
				lng decimal(9,7) NOT NULL,
				address text NOT NULL,
				post_code int NOT NULL,
				city_local text NOT NULL,
				prov_code char(2) NOT NULL,
				country_code char(2) NOT NULL,
				url varchar(55) DEFAULT '' NOT NULL,
				UNIQUE KEY id (geoid)
			) $charset_collate;";
			
		
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		
			add_option( 'cartopress_db_version', $cartopress_db_version );
		
		} // public static function cartopress_install
		
		// begin dummy data 1
		public static function cartopress_install_sampleoptions() { 
			global $wpdb;
			
			$option_name = 'cartodb_api_key';
			$option_value = 'k45466nggfgd00945hnd4';
			
			$table_one = $wpdb->prefix . 'cartopress_options';
			
			$wpdb->insert( 
				$table_one, 
				array( 
					'cartopress_option_name' => $option_name, 
					'cartopress_option_value' => $option_value, 
				) 
			);
		} //end dummy data 1
		
		// begin dummy data 2
		public static function cartopress_install_samplegeoreference() { 
			global $wpdb;
			
			$lat = '40.2772980';
			$lng = '-75.5859375';
			$address = '227 North 7th Street';
			$city_local = 'Gilbertsville';
			$post_code = '19525';
			$state_prov = 'PA';
			$country_code = 'US';
			
			$table_two = $wpdb->prefix . 'cartopress_georeference';
			
			$wpdb->insert( 
				$table_two, 
				array( 
					'time' => current_time( 'mysql' ), 
					'lat' => $lat,
					'lng' => $lng,
					'address' => $address, 
					'city_local' => $city_local,
					'post_code' => $post_code,
					'prov_code' => $state_prov,
					'country_code' => $country_code,
				) 
			);
		} //end dummy data 2
		
		// check for database upgrade @since 0.1.0
		public static function upgradedb() {
		
			global $wpdb;
			$installed_ver = get_option( "cartopress_db_version" );
			
			if ( $installed_ver != $cartopress_db_version ) {
			
				$table_one = $wpdb->prefix . 'cartopress_options';
				$table_two = $wpdb->prefix . 'cartopress_georeference';
			
				$sql = "CREATE TABLE $table_one (
					cartopress_option_id mediumint(9) NOT NULL AUTO_INCREMENT,
					cartopress_option_name tinytext NOT NULL,
					cartopress_option_value text NOT NULL,
					UNIQUE KEY id (cartopress_option_id)
				) $charset_collate;
				
				CREATE TABLE $table_two (
					geoid mediumint(9) NOT NULL AUTO_INCREMENT,
					geoid_postid mediumint NOT NULL,
					geoid_post_type text NOT NULL,
					time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
					lat decimal(9,7) NOT NULL,
					lng decimal(9,7) NOT NULL,
					address text NOT NULL,
					post_code int NOT NULL,
					city_local text NOT NULL,
					prov_code char(2) NOT NULL,
					country_code char(2) NOT NULL,
					url varchar(55) DEFAULT '' NOT NULL,
					UNIQUE KEY id (geoid)
				) $charset_collate;";
			
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta( $sql );
			
				update_option( "cartopress_db_version", $cartopress_db_version );
			}
			
			function cartopress_update_db_check() {
				global $cartopress_db_version;
				if ( get_site_option( 'cartopress_db_version' ) != $cartopress_db_version ) {
					cartopress_install();
				}
			}
			add_action( 'plugins_loaded', 'cartopress_update_db_check' );

		} //end upgradedb