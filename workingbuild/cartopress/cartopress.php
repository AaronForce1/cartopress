<?php
/**
 * Plugin Name: CartoPress
 * Plugin URI: http://baide.me
 * Description: This plugin allows you to use your exisitng WordPress database as source data for your CartoDB account. Georeference your posts, pages, media and comments and CartoPress will sync to your CartoDB account—allowing you to take advantage of the CartoDB user interface and CartoCSS styling options.
 * Version: 0.1.0
 * Author: Aaron Baideme and Troy Andrew Hallisey
 * Author URI: http://baide.me
 * License: GPL2
 */
 
 /*  Copyright 2015  CartoPress — CartoDB for WordPress

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2 or later, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Main CartoPress file
 * @package cartopress
 */
 
if (!class_exists('cartopress')) {

	class cartopress {
	
		public static function start() {
		cartopress::definitions();
		cartopress::activate();
		cartopress::add_geolocator();
				
		}
		
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
		
		
		// add constants
		private static function definitions() {
			define('cartopress_plugin_name', plugin_basename(__FILE__));
			define('cartopress_dir_path', dirname( __FILE__ ));
			define('cartopress_admin_dir', cartopress_dir_path . '/admin/' );
			define('cartopress_url', trim( plugin_dir_url( __FILE__ ), '/' ) );
			define('cartopress_dir', dirname( cartopress_plugin_name ) );
			define('cartopress_vers', '0.1.0');
		}
		

	
		// activation @since 0.1.0
		private static function activate() {
			
			register_activation_hook( __FILE__, array( 'cartopress', 'cartopress_install') );
			register_activation_hook( __FILE__, array( 'cartopress', 'cartopress_install_sampleoptions') ); //for dummy data
			register_activation_hook( __FILE__,  array( 'cartopress', 'cartopress_install_samplegeoreference') ); // for dummy data
		
			// hook in the settings page @since 0.1.0
			if (is_admin()) {
				
				require( cartopress_admin_dir . 'settings.php' );
				
				add_action( 'admin_init', 'get_admin_styles');
				add_action( 'admin_init', 'get_admin_scripts');
				add_action( 'admin_menu', 'cartopress_options_menu' );
				
				$cartopress_settings;
				
				function get_admin_styles() {
					// add google fonts
					$query_args = array( 'family' => 'Montserrat:400,700', 'subset' => 'latin,latin-ext' ); 
					wp_register_style( 'cartopress', cartopress_url . '/admin/css/cartopress-settings.css', array(), cartopress_vers );
					wp_register_style( 'google_fonts', add_query_arg( $query_args, "//fonts.googleapis.com/css" ), array(), null );
				} // end get_admin_styles
				
				function get_admin_scripts() {
					wp_deregister_script('jquery');
					wp_register_script('jquery', "http" . ($_SERVER['SERVER_PORT'] == 443 ? "s" : "") . "://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js", false, null);
				
				} // end get_admin_scripts
				
				function cartopress_options_menu() {
					$admin_page = add_options_page( 'CartoPress Settings', 'CartoPress', 'manage_options', 'cartopress-settings', 'cartopress_options' );
					add_action( 'admin_print_styles-' . $admin_page, 'cartopress_admin_styles' );
					add_action( 'admin_print_scripts-' . $admin_page, 'cartopress_admin_scripts' );
				} // end cartopress_options_menu
				
				function cartopress_options() {
					if ( !current_user_can( 'manage_options' ) )  {
						wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
					}
					
					require( cartopress_admin_dir . 'options.php' );
					
				} // end cartopress_options
				
				function cartopress_admin_styles() {
				   /*
					* It will be called only on your plugin admin page, enqueue our stylesheet here
					*/
				   wp_enqueue_style( 'cartopress' );
				   wp_enqueue_style ( 'google_fonts');
			    } // end cartopress_admin_styles
			    
			    function cartopress_admin_scripts() {
			    	wp_enqueue_script('jquery');
			    } //end cartopress_admin)_scripts
			    
			    
			    // add settings link
			    function cartopress_settings_link($links) { 
				  $settings_link = '<a href="options-general.php?page=cartopress-settings.php">Settings</a>'; 
				  array_unshift($links, $settings_link); 
				  return $links; 
				} //end cartopress_settings_link
 
				$plugin = cartopress_plugin_name; 
				add_filter("plugin_action_links_$plugin", 'cartopress_settings_link' );
			
			} else {
			
			// else
			
			}
			
		} // end activate()
		
		
		/**
		* add geolocator
		* @since 0.1.0
		*/
		private static function add_geolocator() {
		
			if (is_admin()) {
				// add the geolocator metabox
				require( cartopress_admin_dir . 'locations.php' );
				
				function get_cartopress_geolocator() { // create instance
					$add_location_metabox = new add_location_metabox();
					$add_location_metbaox;
				}
				
				add_action( 'load-post.php', 'get_cartopress_geolocator' );
    			add_action( 'load-post-new.php', 'get_cartopress_geolocator' );
			
			} else {
			
				//else
				
			}
		
		} //end add_geolocator

	} //end class cartopress
	
	cartopress::start();

} //end if class exists
?>