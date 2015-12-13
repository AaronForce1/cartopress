<?php
/**
 * Plugin Name: CartoPress
 * Plugin URI: https://github.com/MasterBaideme1021/cartopress
 * Description: This plugin allows you to easily make CartoDB maps with your WordPress data. Simply connect to your CartoDB account and set your options in the settings, and CartoPress will automatically sync your WordPress data to your CartoDB account. Utilize the intuitive CartoDB user interface to visualize and style maps with your data. Publish on your site using CartoDB's powerful and customizable publishing options.
 * Version: 0.1.0
 * Author: Troy Andrew Hallisey and Aaron Baideme
 * Author URI: https://github.com/MasterBaideme1021/cartopress
 * License: GPL2
 */
 
 /*  Copyright 2015  CartoPress — A CartoDB Client for WordPress

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
 * 
 * @package cartopress
 * @since 0.1.0
 */
 
if (!class_exists('cartopress')) {
	/** 
	  * CartoPress class definition. Used as a single source for initializing CartoPress classes using :: operator and adding hooking globally accessible actions.
	  *
	  *	@since 0.1.0
	  */
	class cartopress {
		
		/**
		* Initializes the CartoPress plugin
		*
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
			
			/**
			 * Registers all stylesheets required by CartoPress.
			 */ 
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
			
			/**
			 * Registers all linked scripts required by CartoPress.
			 * 
			 * @todo Use default WordPress install of jQuery instead of 2.1.4 for cartopress-geocode-script
			 */ 
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
		* Loads the global constants
		*
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
		* Loads the settings
		*
		* @since 0.1.0
		*/
		private static function add_settings() {
			
			if (is_admin()) {
				
				require( CARTOPRESS_ADMIN_DIR . 'cp-settings.php' );
				$cartopress_settings = new cartopress_settings();
				
				/**
				* Adds the settings link to the array of plugin actions links
				 * 
				* @param array The plugin action links
				* @return array $links The new plugin action links
				*/
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
		* Loads the geocoder
		*
		* @since 0.1.0
		*/
		private static function add_geolocator() {
		
			if (is_admin()) {
					
				// add the geolocator metabox
				require( CARTOPRESS_ADMIN_DIR . 'cp-locations.php' );
				
				/**
				* Creates a new instance of the Geocoder class
				*/
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
		* Loads custom bulk actions
		*
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