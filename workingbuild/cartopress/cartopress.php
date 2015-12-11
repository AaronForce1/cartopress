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
			
			require( CARTOPRESS_ADMIN_DIR . 'cp-sql.php' );
			require( CARTOPRESS_ADMIN_DIR . 'cp-actions.php' );
			
			add_action( 'admin_init', 'register_admin_styles');
			add_action( 'admin_init', 'register_admin_scripts');
			
			function register_admin_styles() {
				// add google fonts
				$query_args = array( 'family' => 'Montserrat:400,700', 'subset' => 'latin,latin-ext' ); 
				wp_register_style( 'google_fonts', add_query_arg( $query_args, "//fonts.googleapis.com/css" ), array(), null );
				wp_register_style( 'cartopress', CARTOPRESS_URL . '/admin/css/cartopress-settings.css', array(), CARTOPRESS_VERS );
				wp_register_style( 'cartopress-geocode-styles', CARTOPRESS_URL . '/admin/css/geocoder-styles.css', array(), CARTOPRESS_VERS );
				wp_register_style( 'leaflet', 'http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.css');
				wp_register_style( 'ionicons', 'http://code.ionicframework.com/ionicons/1.5.2/css/ionicons.min.css');
				wp_register_style( 'cartopress-leaflet-styles', CARTOPRESS_URL . '/admin/css/leaflet-awesome-markers.css', array(), CARTOPRESS_VERS );

			} // end get_admin_styles
			
			function register_admin_scripts() {
				wp_register_script('jquery2.1.4', 'https://code.jquery.com/jquery-2.1.4.min.js');
				wp_register_script('leaflet', 'http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.js');
				wp_register_script('cartodb', 'http://libs.cartocdn.com/cartodb.js/v3/3.11/cartodb.js');
				wp_register_script('ionicons', CARTOPRESS_URL . '/admin/js/leaflet.awesome-markers.min.js', array(), CARTOPRESS_VERS);
				wp_register_script('admin-script', CARTOPRESS_URL . '/admin/js/admin.js', array('jquery'), CARTOPRESS_VERS );
				wp_register_script('cartopress-geocode-script', CARTOPRESS_URL . '/admin/js/geocoder.js', array('jquery2.1.4','leaflet'), CARTOPRESS_VERS );
				wp_register_script('cartopress-geocode-helper-script', CARTOPRESS_URL . '/admin/js/geocoder-helper.js', array('jquery'), CARTOPRESS_VERS );
			} // end get_admin_scripts
				
			add_action( 'save_post', 'cartopress_update_row', 2000);
			add_action( 'edit_attachment', 'cartopress_update_row', 2000);
			add_action( 'delete_post', 'cartopress_delete_attachment' );

		} // initialize()
		
		/**
		* load constants
		* @since 0.1.0
		*/
		private static function load_constants() {
			define('CARTOPRESS_PLUGIN_NAME', plugin_basename(__FILE__));
			define('CARTOPRESS_DIR_PATH', dirname( __FILE__ ));
			define('CARTOPRESS_ADMIN_DIR', CARTOPRESS_DIR_PATH . '/admin/' );
			define('CARTOPRESS_URL', trim( plugin_dir_url( __FILE__ ), '/' ) );
			define('CARTOPRESS_DIR', dirname( CARTOPRESS_PLUGIN_NAME ) );
			define('CARTOPRESS_VERS', '0.1.0');
			
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			if (isset($cpoptions['cartopress_cartodb_apikey'])) {
				define('CARTOPRESS_APIKEY', $cpoptions['cartopress_cartodb_apikey'] );
			}
			if (isset($cpoptions['cartopress_cartodb_username'])) {
				define('CARTOPRESS_USERNAME', $cpoptions['cartopress_cartodb_username'] );
			}
			if (isset($cpoptions['cartopress_cartodb_tablename'])) {
				define('CARTOPRESS_TABLE', $cpoptions['cartopress_cartodb_tablename'] );
			}
		} // end load_constants()
		
		/**
		* load settings
		* @since 0.1.0
		*/
		private static function add_settings() {
			
			if (is_admin()) {
				
				require( CARTOPRESS_ADMIN_DIR . 'cp-settings.php' );
				$cartopress_settings = new cartopress_settings();
				
				 // add settings link
			    function cartopress_settings_link($links) { 
				  $settings_link = '<a href="options-general.php?page=cartopress-settings.php">Settings</a>'; 
				  array_unshift($links, $settings_link); 
				  return $links; 
				} //end cartopress_settings_link
				$plugin = CARTOPRESS_PLUGIN_NAME; 
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
				require( CARTOPRESS_ADMIN_DIR . 'cp-locations.php' );
				
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
				require( CARTOPRESS_ADMIN_DIR . 'cp-bulkactions.php' );
				new cartopress_bulkactions();
			}
		
		} //end add_bulkactions

	} //end class cartopress
	
	cartopress::initialize();

} //end if class exists
?>