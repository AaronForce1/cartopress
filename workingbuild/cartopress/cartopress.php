<?php
/**
 * Plugin Name: CartoPress
 * Plugin URI: https://github.com/MasterBaideme1021/cartopress
 * Description: This plugin allows you to connect your WordPress database to your CartoDB account in order to sync your posts, pages, media attachments and other content.
 * Version: 0.1.0
 * Author: Aaron Baideme and Troy Andrew Hallisey
 * Author URI: http://troyhallisey.com
 * License: GPL2
 */
 
 /*  Copyright 2015  CartoPress — CartoDB Client for WordPress

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
		
		/**
		* cartopress initialize
		* @since 0.1.0
		*/
		public static function initialize() {
			cartopress::load_constants();
			cartopress::add_settings();
			cartopress::add_geolocator();
			cartopress::add_bulkactions();
			
			require( cartopress_admin_dir . 'cp-sql.php' );
			require( cartopress_admin_dir . 'cp-actions.php' );
			
			add_action( 'admin_init', 'register_admin_styles');
			add_action( 'admin_init', 'register_admin_scripts');
			
			function register_admin_styles() {
				// add google fonts
				$query_args = array( 'family' => 'Montserrat:400,700', 'subset' => 'latin,latin-ext' ); 
				wp_register_style( 'google_fonts', add_query_arg( $query_args, "//fonts.googleapis.com/css" ), array(), null );
				wp_register_style( 'cartopress', cartopress_url . '/admin/css/cartopress-settings.css', array(), cartopress_vers );
				wp_register_style( 'cartopress-geocode-styles', cartopress_url . '/admin/css/geocoder-styles.css', array(), cartopress_vers );
				wp_register_style( 'leaflet', 'http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.css');
				wp_register_style( 'ionicons', 'http://code.ionicframework.com/ionicons/1.5.2/css/ionicons.min.css');
				wp_register_style( 'cartopress-leaflet-styles', cartopress_url . '/admin/css/leaflet-awesome-markers.css', array(), cartopress_vers );

			} // end get_admin_styles
			
			function register_admin_scripts() {
				wp_register_script('jquery2.1.4', 'https://code.jquery.com/jquery-2.1.4.min.js');
				wp_register_script('leaflet', 'http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.js');
				wp_register_script('cartodb', 'http://libs.cartocdn.com/cartodb.js/v3/3.11/cartodb.js');
				wp_register_script('ionicons', cartopress_url . '/admin/js/leaflet.awesome-markers.min.js', array(), cartopress_vers);
				wp_register_script('admin-script', cartopress_url . '/admin/js/admin.js', array('jquery'), cartopress_vers );
				wp_register_script('cartopress-geocode-script', cartopress_url . '/admin/js/geocoder.js', array('jquery2.1.4','leaflet'), cartopress_vers );
				wp_register_script('cartopress-geocode-helper-script', cartopress_url . '/admin/js/geocoder-helper.js', array('jquery'), cartopress_vers );
			} // end get_admin_scripts
				
			add_action( 'save_post', 'update_row', 2000);
			add_action( 'edit_attachment', 'update_row', 2000);
			add_action( 'delete_post', 'delete_attachment' );

		} // initialize()
		
		/**
		* load constants
		* @since 0.1.0
		*/
		private static function load_constants() {
			define('cartopress_plugin_name', plugin_basename(__FILE__));
			define('cartopress_dir_path', dirname( __FILE__ ));
			define('cartopress_admin_dir', cartopress_dir_path . '/admin/' );
			define('cartopress_url', trim( plugin_dir_url( __FILE__ ), '/' ) );
			define('cartopress_dir', dirname( cartopress_plugin_name ) );
			define('cartopress_vers', '0.1.0');
			
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			if (isset($cpoptions['cartopress_cartodb_apikey'])) {
				define('cartopress_apikey', $cpoptions['cartopress_cartodb_apikey'] );
			}
			if (isset($cpoptions['cartopress_cartodb_username'])) {
				define('cartopress_username', $cpoptions['cartopress_cartodb_username'] );
			}
			if (isset($cpoptions['cartopress_cartodb_tablename'])) {
				define('cartopress_table', $cpoptions['cartopress_cartodb_tablename'] );
			}
		} // end load_constants()
		
		/**
		* load settings
		* @since 0.1.0
		*/
		private static function add_settings() {
			
			if (is_admin()) {
				
				require( cartopress_admin_dir . 'cp-settings.php' );
				$cartopress_settings = new cartopress_settings();
				
				 // add settings link
			    function cartopress_settings_link($links) { 
				  $settings_link = '<a href="options-general.php?page=cartopress-settings.php">Settings</a>'; 
				  array_unshift($links, $settings_link); 
				  return $links; 
				} //end cartopress_settings_link
				$plugin = cartopress_plugin_name; 
				add_filter("plugin_action_links_$plugin", 'cartopress_settings_link' );
		
			} // end is admin()
			
		} // end get_settings()
		
		
		/**
		* add geolocator
		* @since 0.1.0
		*/
		private static function add_geolocator() {
		
			if (is_admin()) {
					
				// add the geolocator metabox
				require( cartopress_admin_dir . 'cp-locations.php' );
				
				function get_cartopress_geolocator() {
					$geocoder_metabox = new geocoder_metabox();
				}
				
				add_action( 'load-post.php', 'get_cartopress_geolocator' );
    			add_action( 'load-post-new.php', 'get_cartopress_geolocator' );
				
				// add the ajax
				add_action('wp_ajax_cartopress_delete_row', array('cartopress_sync','cartopress_delete_row') );
				add_action('wp_ajax_cartopress_reset_record', array('cartopress_sync','cartopress_reset_record') );
				
			} // end is admin
		
		} //end add_geolocator
		
		/**
		* add bulk actions
		* @since 0.1.0
		*/
		private static function add_bulkactions() {
		
			if (is_admin()) {
				require( cartopress_admin_dir . 'cp-bulkactions.php' );
				new cartopress_bulkactions();
			}
		
		} //end add_bulkactions

	} //end class cartopress
	
	cartopress::initialize();

} //end if class exists
?>