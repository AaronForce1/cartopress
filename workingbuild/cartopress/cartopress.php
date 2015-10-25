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
		
		// add constants @since 0.1.0
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
			
			// hook in the settings page @since 0.1.0
			if (is_admin()) {
				
				require( cartopress_admin_dir . 'settings.php' );
				
				add_action( 'admin_init', 'get_admin_styles');
				add_action( 'admin_init', 'get_admin_scripts');
				add_action( 'admin_menu', 'cartopress_options_menu' );

				function get_admin_styles() {
					// add google fonts
					$query_args = array( 'family' => 'Montserrat:400,700', 'subset' => 'latin,latin-ext' ); 
					wp_register_style( 'google_fonts', add_query_arg( $query_args, "//fonts.googleapis.com/css" ), array(), null );
					wp_register_style( 'cartopress', cartopress_url . '/admin/css/cartopress-settings.css', array(), cartopress_vers );
				} // end get_admin_styles
				
				function get_admin_scripts() {
					wp_register_script('admin-script', plugin_dir_url( __FILE__ ) . 'admin/js/admin.js', array('jquery'), cartopress_vers );
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
				
				// connecting to cartodb
				function process_curl($ch, $sql, $apikey, $username){
					//curl init	
					$ch = curl_init("https://".$username.".cartodb.com/api/v2/sql");
					$query = http_build_query(array('q'=>$sql,'api_key'=>$apikey));
					
					//curl opts
					curl_setopt($ch, CURLOPT_POST, TRUE);
				   	curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
				   	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
					
					//process results
					$result = curl_exec($ch);
					curl_close($ch);
					$result = json_decode($result);
					
					//return results
					return $result;
				}
				
				//process ajax
				function process_generate_table() {
				   	
				   // checks the referer to ensure authorized access
				   if (!isset( $_POST['cartopress_admin_nonce'] ) || !wp_verify_nonce($_POST['cartopress_admin_nonce'], 'cartopress_admin_nonce') )
				   	die('Unauthorized access denied.');
				   
				   // defines the post vars
				   $apikey = $_POST['apikey'];
				   $username = $_POST['username'];
				   $tablename = $_POST['tablename'];
				   
				   //SQL create table statment
				   $sql_create = "DO $$ BEGIN CREATE TABLE " . (string)$tablename . " (cp_post_id integer, cp_post_title text, cp_post_content text, cp_post_description text, cp_post_date date, cp_post_type text, cp_permalink_guid text, cp_post_categories text, cp_post_tags text, cp_post_featuredimage_url text, cp_post_customfields text, cp_geo_streetnumber text, cp_geo_street text, cp_geo_adminlevel4_vill_neigh text, cp_geo_adminlevel3_city text, cp_geo_adminlevel2_county text, cp_geo_adminlevel1_st_prov_region text, cp_geo_adminlevel0_country text, cp_geo_postal text, cp_geo_lat float, cp_geo_long float, cp_geo_placeid integer, cp_geo_locationtype text, cp_geo_displayname text); RAISE NOTICE 'Success'; END; $$";
				   
				   //process curl
				   $result_create = process_curl($ch, $sql_create, $apikey, $username);
				   
				   //parse result
				   $error = $result_create->error[0];
				   $success = $result_create->notices[0];
				   
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
						process_curl($ch, $sql_select, $apikey, $username);
						update_option('cartopress_cartodb_verified', 'verified');
						die('success');
				   
				   } else {
						die('Unknown error: ' . print_r($result_create) );
				   }
				   
				}
				
			    add_action('wp_ajax_cartopress_generate_table', 'process_generate_table');
				
				//checks existing tables to ensure correct columns are in place
				function cartopress_cartopressify_table() {
				   	
				   // checks the referer to ensure authorized access
				   if (!isset( $_POST['cartopress_cartopressify_nonce'] ) || !wp_verify_nonce($_POST['cartopress_cartopressify_nonce'], 'cartopress_cartopressify_nonce') )
				   	die('Unauthorized access denied.');
				   
				   $apikey = $_POST['apikey'];
				   $username = $_POST['username'];
				   $tablename = $_POST['tablename'];
				   $args = [
				   		['name' => 'cp_post_id', 'type' => 'integer'], 
				   		['name' => 'cp_post_title', 'type' => 'text'], 
				   		['name' => 'cp_post_content', 'type' => 'text'],
				   		['name' => 'cp_post_description', 'type' => 'text'],
				   		['name' => 'cp_post_date', 'type' => 'date'],
				   		['name' => 'cp_post_type', 'type' => 'text'],
				   		['name' => 'cp_post_permalink', 'type' => 'text'],
				   		['name' => 'cp_post_categories', 'type' => 'text'],
				   		['name' => 'cp_post_tags', 'type' => 'text'],
				   		['name' => 'cp_post_featuredimage_url', 'type' => 'text'],
				   		['name' => 'cp_post_customfields', 'type' => 'text'],
				   		['name' => 'cp_geo_streetnumber', 'type' => 'text'],
				   		['name' => 'cp_geo_street', 'type' => 'text'],
				   		['name' => 'cp_geo_adminlevel4_vill_neigh', 'type' => 'text'],
				   		['name' => 'cp_geo_adminlevel3_city', 'type' => 'text'],
				   		['name' => 'cp_geo_adminlevel2_county', 'type' => 'text'],
				   		['name' => 'cp_geo_adminlevel1_st_prov_region', 'type' => 'text'],
						['name' => 'cp_geo_adminlevel0_country', 'type' => 'text'],
						['name' => 'cp_geo_postal', 'type' => 'text'],
						['name' => 'cp_geo_lat', 'type' => 'float'],
						['name' => 'cp_geo_long', 'type' => 'float'],
						['name' => 'cp_geo_placeid', 'type' => 'integer'],
						['name' => 'cp_geo_locationtype', 'type' => 'text'],
						['name' => 'cp_geo_displayname', 'type' => 'text'],
					];
				   
				   foreach ($args as $col) {
				   		$col_name = $col['name'];
					    $col_type = $col['type'];
				   		
				   		$cartopressify = "DO $$ BEGIN ALTER TABLE " . $tablename . " ADD COLUMN " . $col_name . " " . $col_type . "; RAISE NOTICE 'Column " . $col_name . " was created.'; UPDATE " . $tablename . " SET " . $col_name . " = " . $col_name . "; RAISE NOTICE 'Column name is set.'; EXCEPTION WHEN duplicate_column THEN RAISE NOTICE '" . $col_name . " text already exists in " . $tablename . ".'; END; $$";	
				   		
				   		$result = process_curl($ch, $cartopressify, $apikey, $username);
				   }
				   update_option('cartopress_cartodb_verified', 'verified');
				   die("Success");  
				   
				   
				}
				add_action('wp_ajax_cartopressify_table', 'cartopress_cartopressify_table');
				
				function cartopress_admin_styles() {
				   /*
					* It will be called only on your plugin admin page, enqueue our stylesheet here
					*/
				   wp_enqueue_style( 'cartopress' );
				   wp_enqueue_style ( 'google_fonts');
			    } // end cartopress_admin_styles
			    
			    function cartopress_admin_scripts() {
			    	wp_enqueue_script('admin-script');
					wp_localize_script('admin-script','cartopress_admin_ajax', array(
							"cartopress_admin_nonce" => wp_create_nonce('cartopress_admin_nonce'),
							"cartopress_cartopressify_nonce" => wp_create_nonce('cartopress_cartopressify_nonce')
						)
					);
			    } //end cartopress_admin_scripts
			    
			    
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