<?php
/**
 * CartoPress Settings
 * 
 * The main settings class.
 *
 * @package cartopress
 */
 

if (!class_exists('cartopress_settings')) {
	
	 /**
	  * Register CartoPress settings
	  * 
	  *	@since 0.1.0
	  */
	class cartopress_settings
	{
		/**
		 * Constructor for enqueing styles and scripts and adding ajax action hooks
		 * 
		 * @since 0.1.0
		 */
		public function __construct() {
			
			// add settings menu
			add_action( 'admin_menu', 'cartopress_add_settings_menu' );
			
			/**
			 * Adds the settings menu link to the dashboard panel
			 * 
			 * @since 0.1.0
			 */
			function cartopress_add_settings_menu() {
				$admin_page = add_options_page( 'CartoPress Settings', 'CartoPress', 'manage_options', 'cartopress-settings', array('cartopress_settings', 'cartopress_get_settings_page') );
				add_action( 'admin_print_styles-' . $admin_page, array('cartopress_settings', 'cartopress_get_admin_styles') );
				add_action( 'admin_print_scripts-' . $admin_page, array('cartopress_settings', 'cartopress_get_admin_scripts') );
			} // end cartopress_options_menu
			
			// initialize settings
			add_action( 'admin_init', array( $this, 'initialize_settings' ) );
			
			// add ajax calls
			add_action('wp_ajax_cartopress_generate_table', array('cartopress_sync','cartopress_generate_table') );
			add_action('wp_ajax_cartopressify_table', array('cartopress_sync','cartopress_cartopressify_table') );
			add_action('wp_ajax_cartopress_create_column', array('cartopress_sync','cartopress_create_column') );
			add_action('wp_ajax_cartopress_delete_column', array('cartopress_sync','cartopress_delete_column') );
			
		} //end __construct
		
		/**
		 * Adds the Settings page
		 * 
		 * @since 0.1.0
		 */
		public static function cartopress_get_settings_page() {
			if ( !current_user_can( 'manage_options' ) )  {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}
			require( CARTOPRESS_ADMIN_DIR . 'cp-options.php' );
		} // end cartopress_options
		
		/**
		 * Enqueue Styles
		 * 
		 * @since 0.1.0
		 */
		public static function cartopress_get_admin_styles() {
		   wp_enqueue_style( 'cartopress-settings' );
		   wp_enqueue_style ( 'google_fonts');
	    } // end cartopress_admin_styles
	    
	    /**
		 * Enqueue Scripts
		 * 
		 * @since 0.1.0
		 */
	    public static function cartopress_get_admin_scripts() {
	    	wp_enqueue_script('cartopress-admin-script');
			wp_localize_script('cartopress-admin-script','cartopress_admin_ajax', array(
					"cartopress_admin_nonce" => wp_create_nonce('cartopress_admin_nonce'),
					"cartopress_cartopressify_nonce" => wp_create_nonce('cartopress_cartopressify_nonce'),
					"cartopress_create_column_nonce" => wp_create_nonce('cartopress_create_column_nonce'),
					"cartopress_delete_column_nonce" => wp_create_nonce('cartopress_delete_column_nonce')
				)
			);
	    } //end cartopress_admin_scripts
		    
		/**
		 * Initializes the settings, registers the settings and defines the settings sections
		 * 
		 * @since 0.1.0
		 */
		public function initialize_settings() {        
			register_setting( 'cartopress-settings', 'cartopress_admin_options', array( $this, 'sanitize' ) );
			register_setting( 'cartopress-settings', 'cartopress_custom_fields');
			add_settings_section( 'cartopress_required_info', null, array($this, 'cartopress_required_info'), 'cartopress-settings-group' );
			add_settings_section( 'cartopress_collect_info', null, array($this, 'cartopress_collect_info'), 'cartopress-settings-group' );
			add_settings_section( 'cartopress_sync_info', null, array($this, 'cartopress_sync_info'), 'cartopress-settings-group' );
			add_settings_section( 'cartopress_sync_customfields', null, array($this, 'cartopress_sync_customfields'), 'cartopress-customfields-group' );
		}
		
		/**
		* Update custom field settings
		* 
		* @since 0.1.0
		* @param string $custom_field The custom field name
		* @param string $cartodb_column The corresponding CartoDB column name
		* @param string $action String for action to perform ('set' or 'unset')
		* @return boolean Returns true when run
		*/
		public static function update_customfield_settings($custom_field, $cartodb_column, $action) {
			$option = get_option('cartopress_custom_fields');
			if ('set' === $action) {
		    	$option[$cartodb_column] = array('sync'=>1,'custom_field'=>$custom_field,'cartodb_column'=>$cartodb_column);
			} elseif ('unset' === $action) {
				unset($option[$cartodb_column]);
			}
		    update_option('cartopress_custom_fields', $option);
			return true;
		}
		
		/**
		* Helper method to create column names
		 * 
		* @since 0.1.0
		* @param string $string The string to convert to column name
		* @return string $string Returns string with spaces and special chars replaced with underscores
		*/
		public static function create_column_name($string) {
		    $string = strtolower($string);
		    $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
		    $string = preg_replace("/[\s-]+/", " ", $string);
		    //Convert whitespaces and underscore to dash
		    $string = preg_replace("/[\s_]/", "_", $string);
		    return $string;
		} //end create_column_name()
		
		/**
		* Generate array of all non-hidden metakeys
		* 
		* @since 0.1.0
		* @return array $meta_keys Returns string with spaces and special chars replaced with underscores
		*/
		public static function generate_metakeys(){
		    global $wpdb;
		    $query = "
		        SELECT DISTINCT($wpdb->postmeta.meta_key) 
		        FROM $wpdb->posts 
		        LEFT JOIN $wpdb->postmeta 
		        ON $wpdb->posts.ID = $wpdb->postmeta.post_id 
		        WHERE $wpdb->postmeta.meta_key != '' 
		        AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)' 
		        AND $wpdb->postmeta.meta_key NOT RegExp '(^[0-9]+$)'
		        ORDER BY $wpdb->postmeta.meta_key ASC
		    ";
		    $meta_keys = $wpdb->get_col($query);
		    //set_transient('all_meta_keys', $meta_keys, 60*60*24); (maybe use this in the future)
		    return $meta_keys;
		} //end generate_metakeys()
		
		/**
		 * Generates option tags for the custom field select menu
		 * 
		 * @since 0.1.0
		 */
		public static function get_metakey_menu() {
			$meta_keys = cartopress_settings::generate_metakeys();
			if ($meta_keys == null) {
				echo '<option value="" disabled>You have no custom fields</option>';
			} else {
				foreach ($meta_keys as $value) {
					$column = cartopress_settings::create_column_name($value);
					echo '<option value="cp_post_customfield_' . $column . '" id="cp_post_customfield_' . $column . '">' . $value . '</option>
					';
				}
		    } // end if else
		} // end get_metakeys()
	
		/**
		 * Sanitize each setting field as needed
		 * 
		 * @since 0.1.0
		 * @param array $input Contains all settings fields as array keys
		 * @return string Returns sanitized inputs
		 */
		public function sanitize( $input ) {
			$new_input = array();
			foreach($input as $key => $val) {
				$new_input[ $key ] = sanitize_text_field( $val );
			}
			return $new_input;
		}

		/** 
		 * Prints the Custom Field settings on the settings page
		 * 
		 * @since 0.1.0
		 */
		public function cartopress_sync_customfields() {
			$customfield_options = get_option('cartopress_custom_fields');
			if (!empty($customfield_options)) {
				foreach ($customfield_options as $key=>$value) {
					if (!isset($value['sync'])) {
						$value['sync'] = false;
					}
					echo '<tr id="cpdb_rowfor_' . $value['cartodb_column'] . '">
							<td align="center"><input type="checkbox" name="cartopress_custom_fields[' . $value['cartodb_column'] . '][sync]" id="cartopress_custom_fields_sync_'.$value['cartodb_column'].'"  value="1"' . checked( 1, $value['sync'], false ) . '/></td>
							<td><input type="text" name="cartopress_custom_fields[' . $value['cartodb_column'] . '][custom_field]" id="cartopress_custom_fields_fieldname_'.$value['cartodb_column'].'" value="'.esc_attr($value['custom_field']).'" class="disabled" readonly/></td>
							<td><input type="text" name="cartopress_custom_fields[' . $value['cartodb_column'] . '][cartodb_column]" id="cartopress_custom_fields_cartodbcol_'.$value['cartodb_column'].'" value="'.esc_attr($value['cartodb_column']).'" class="disabled" readonly/></td>
							<td><div class="deletebutton button" id="delete_' . $value['cartodb_column'] . '">Remove</div></td>
						  </tr>';
				}
			}
		}
		
		/** 
		 * Adds the settings fields for the Required Info section
		 * 
		 * @since 0.1.0
		 */
		public function cartopress_required_info() {
			add_settings_field( 'cartopress_cartodb_apikey', 'API Key:', array( $this, 'cartopress_cartodb_apikey_callback' ), 'cartopress-settings', 'cartopress_required_info', array( 'label_for' => 'cartopress_cartodb_apikey', 'type' => 'text') );      
			add_settings_field( 'cartopress_cartodb_username', 'Username:', array( $this, 'cartopress_cartodb_username_callback' ), 'cartopress-settings', 'cartopress_required_info', array( 'label_for' => 'cartopress_cartodb_username', 'type' => 'text') );      
			add_settings_field( 'cartopress_cartodb_tablename', 'Table Name:', array( $this, 'cartopress_cartodb_tablename_callback' ), 'cartopress-settings', 'cartopress_required_info', array( 'label_for' => 'cartopress_cartodb_tablename', 'type' => 'text') );      
			add_settings_field( 'cartopress_cartodb_verified', null, array( $this, 'cartopress_cartodb_verified_callback' ), 'cartopress-settings', 'cartopress_required_info', array( 'type' => 'text') );      
		}
		
		/** 
		 * Adds the settings fields for the Collect Data section
		 * 
		 * @since 0.1.0
		 */
		public function cartopress_collect_info() {
			add_settings_field('cartopress_collect_posts', null, array($this, 'cartopress_cartodb_posts_callback'), 'cartopress-settings', 'cartopress_collect_info', array( 'type' => 'checkbox') );
			add_settings_field('cartopress_collect_pages', null, array($this, 'cartopress_cartodb_pages_callback'), 'cartopress-settings', 'cartopress_collect_info', array( 'type' => 'checkbox') );
			add_settings_field('cartopress_collect_media', null, array($this, 'cartopress_cartodb_media_callback'), 'cartopress-settings', 'cartopress_collect_info', array( 'type' => 'checkbox') );
		}
		
		/** 
		 * Adds the settings fields for the Sync Info section
		 * 
		 * @since 0.1.0
		 */
		public function cartopress_sync_info() {
			add_settings_field('cartopress_collect_postcontent', null, array($this, 'cartopress_cartodb_postcontent_callback'), 'cartopress-settings', 'cartopress_sync_info', array( 'type' => 'checkbox') );
			add_settings_field('cartopress_collect_categories', null, array($this, 'cartopress_cartodb_categories_callback'), 'cartopress-settings', 'cartopress_sync_info', array( 'type' => 'checkbox') );
			add_settings_field('cartopress_collect_tags', null, array($this, 'cartopress_cartodb_tags_callback'), 'cartopress-settings', 'cartopress_sync_info', array( 'type' => 'checkbox') );
			add_settings_field('cartopress_collect_featuredimage', null, array($this, 'cartopress_cartodb_featuredimage_callback'), 'cartopress-settings', 'cartopress_sync_info', array( 'type' => 'checkbox') );
			add_settings_field('cartopress_collect_format', null, array($this, 'cartopress_cartodb_format_callback'), 'cartopress-settings', 'cartopress_sync_info', array( 'type' => 'checkbox') );
			add_settings_field('cartopress_collect_author', null, array($this, 'cartopress_cartodb_author_callback'), 'cartopress-settings', 'cartopress_sync_info', array( 'type' => 'checkbox') );
			add_settings_field('cartopress_collect_customfields', null, array($this, 'cartopress_cartodb_customfields_callback'), 'cartopress-settings', 'cartopress_sync_info', array( 'type' => 'checkbox') );
		}
		
		/** 
		 * Post Content setting
		 * 
		 * Get the settings option array and print one of its values
		 * 
		 * @since 0.1.0
		 */
		public function cartopress_cartodb_postcontent_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			if (!isset($cpoptions['cartopress_sync_postcontent'])) {
				$cpoptions['cartopress_sync_postcontent'] = 0;
			}	
			$theoption = esc_attr($cpoptions['cartopress_sync_postcontent']);
			printf(
				'<input type="checkbox" name="cartopress_admin_options[cartopress_sync_postcontent]" id="cartopress_sync_postcontent"  value="1"' . checked( 1, $theoption, false ) . '/><label for="cartopress_sync_postcontent" class="label">Post Content</label>'
			);
		} 

		/** 
		 * Categories setting
		 * 
		 * Get the settings option array and print one of its values
		 * 
		 * @since 0.1.0
		 */
		public function cartopress_cartodb_categories_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			if (!isset($cpoptions['cartopress_sync_categories'])) {
				$cpoptions['cartopress_sync_categories'] = 0;
			}	
			$theoption = esc_attr($cpoptions['cartopress_sync_categories']);
			printf(
				'<input type="checkbox" name="cartopress_admin_options[cartopress_sync_categories]" id="cartopress_sync_categories"  value="1"' . checked( 1, $theoption, false ) . '/><label for="cartopress_sync_categories" class="label">Categories</label>'
			);
		}

		/** 
		 * Tags setting
		 * 
		 * Get the settings option array and print one of its values
		 * 
		 * @since 0.1.0
		 */
		public function cartopress_cartodb_tags_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			if (!isset($cpoptions['cartopress_sync_tags'])) {
				$cpoptions['cartopress_sync_tags'] = 0;
			}
			$theoption = esc_attr($cpoptions['cartopress_sync_tags']);
			printf(
				'<input type="checkbox" name="cartopress_admin_options[cartopress_sync_tags]" id="cartopress_sync_tags"  value="1"' . checked( 1, $theoption, false ) . '/><label for="cartopress_sync_tags" class="label">Tags</label>'
			);
		}

		/** 
		 * Featured Image setting
		 * 
		 * Get the settings option array and print one of its values
		 * 
		 * @since 0.1.0
		 */
		public function cartopress_cartodb_featuredimage_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			if (!isset($cpoptions['cartopress_sync_featuredimage'])) {
				$cpoptions['cartopress_sync_featuredimage'] = 0;
			}
			$theoption = esc_attr($cpoptions['cartopress_sync_featuredimage']);
			printf(
				'<input type="checkbox" name="cartopress_admin_options[cartopress_sync_featuredimage]" id="cartopress_sync_featuredimage"  value="1"' . checked( 1, $theoption, false ) . '/><label for="cartopress_sync_featuredimage" class="label">Featured Image</label>'
			);
		}
		
		/** 
		 * Custom Field setting
		 * 
		 * Get the settings option array and print one of its values
		 * 
		 * @since 0.1.0
		 */
		public function cartopress_cartodb_customfields_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			if (!isset($cpoptions['cartopress_sync_customfields'])) {
				$cpoptions['cartopress_sync_customfields'] = 0;
			}
			$theoption = esc_attr($cpoptions['cartopress_sync_customfields']);
			printf(
				'<input type="checkbox" name="cartopress_admin_options[cartopress_sync_customfields]" id="cartopress_sync_customfields"  value="1"' . checked( 1, $theoption, false ) . '/><label for="cartopress_sync_customfields" class="label">Custom Fields</label>'
			);
		}
		
		/** 
		 * Author setting
		 * 
		 * Get the settings option array and print one of its values
		 * 
		 * @since 0.1.0
		 */
		public function cartopress_cartodb_author_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			if (!isset($cpoptions['cartopress_sync_author'])) {
				$cpoptions['cartopress_sync_author'] = 0;
			}
			$theoption = esc_attr($cpoptions['cartopress_sync_author']);
			printf(
				'<input type="checkbox" name="cartopress_admin_options[cartopress_sync_author]" id="cartopress_sync_author"  value="1"' . checked( 1, $theoption, false ) . '/><label for="cartopress_sync_author" class="label">Author</label>'
			);
		}
		
		/** 
		 * Post Format setting
		 * 
		 * Get the settings option array and print one of its values
		 * 
		 * @since 0.1.0
		 */
		public function cartopress_cartodb_format_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			if (!isset($cpoptions['cartopress_sync_format'])) {
				$cpoptions['cartopress_sync_format'] = 0;
			}
			$theoption = esc_attr($cpoptions['cartopress_sync_format']);
			printf(
				'<input type="checkbox" name="cartopress_admin_options[cartopress_sync_format]" id="cartopress_sync_format"  value="1"' . checked( 1, $theoption, false ) . '/><label for="cartopress_sync_format" class="label">Post Format</label>'
			);
		}
		
		/** 
		 * Collect Posts setting
		 * 
		 * Get the settings option array and print one of its values
		 * 
		 * @since 0.1.0
		 */
		public function cartopress_cartodb_posts_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			if (!isset($cpoptions['cartopress_collect_posts'])) {
				$cpoptions['cartopress_collect_posts'] = 0;
			}
			$theoption = esc_attr($cpoptions['cartopress_collect_posts']);
			printf(
				'<input type="checkbox" name="cartopress_admin_options[cartopress_collect_posts]" id="cartopress_collect_posts"  value="1"' . checked( 1, $theoption, false ) . '/><label for="cartopress_collect_posts" class="label">Posts</label>'
			);
		}
		
		/** 
		 * Collect Pages setting
		 * 
		 * Get the settings option array and print one of its values
		 * 
		 * @since 0.1.0
		 */
		public function cartopress_cartodb_pages_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			if (!isset($cpoptions['cartopress_collect_pages'])) {
				$cpoptions['cartopress_collect_pages'] = 0;
			}
			$theoption = esc_attr($cpoptions['cartopress_collect_pages']);
			printf(
				'<input type="checkbox" name="cartopress_admin_options[cartopress_collect_pages]" id="cartopress_collect_pages"  value="1"' . checked( 1, $theoption, false ) . '/><label for="cartopress_collect_pages" class="label">Pages</label>'
			);
		}
		
		/** 
		 * Collect Attachment setting
		 * 
		 * Get the settings option array and print one of its values
		 * 
		 * @since 0.1.0
		 */
		public function cartopress_cartodb_media_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			if (!isset($cpoptions['cartopress_collect_media'])) {
				$cpoptions['cartopress_collect_media'] = 0;
			}
			$theoption = esc_attr($cpoptions['cartopress_collect_media']);
			printf(
				'<input type="checkbox" name="cartopress_admin_options[cartopress_collect_media]" id="cartopress_collect_media"  value="1"' . checked( 1, $theoption, false ) . '/><label for="cartopress_collect_media" class="label">Media</label>'
			);
		}
		
		/** 
		 * CartoDB API Key setting
		 * 
		 * Get the settings option array and print one of its values
		 * 
		 * @since 0.1.0
		 */
		public function cartopress_cartodb_apikey_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			if (isset($cpoptions['cartopress_cartodb_apikey'])) {
				$str = esc_attr($cpoptions['cartopress_cartodb_apikey']);
			} else {
				$str = null;
			}
			printf(
				'<input type="text" name="cartopress_admin_options[cartopress_cartodb_apikey]" id="cartopress_cartodb_apikey" placeholder="Enter CartoDB API Key" value="%s" />', $str
			);
		}
		
		/** 
		 * CartoDB Account Verified setting
		 * 
		 * Get the settings option array and print one of its values
		 * 
		 * @since 0.1.0
		 */
		public function cartopress_cartodb_verified_callback()
		{
			$str = esc_attr(get_option('cartopress_cartodb_verified'));
			printf(
				'<input type="hidden" name="cartopress_cartodb_verified" id="cartopress_cartodb_verified" value="%s" />', $str
			);
		}
		
		/** 
		 * CartoDB Username setting
		 * 
		 * Get the settings option array and print one of its values
		 * 
		 * @since 0.1.0
		 */
		public function cartopress_cartodb_username_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			if (isset($cpoptions['cartopress_cartodb_username'])) {
				$str = esc_attr($cpoptions['cartopress_cartodb_username']);
			} else {
				$str = null;
			}
			printf(
				'<input type="text" name="cartopress_admin_options[cartopress_cartodb_username]" id="cartopress_cartodb_username" placeholder="Enter CartoDB Username" value="%s" />', $str
			);
		}
		
		/** 
		 * CartoDB Dataset Name setting
		 * 
		 * Get the settings option array and print one of its values
		 * 
		 * @since 0.1.0
		 */
		public function cartopress_cartodb_tablename_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			if (isset($cpoptions['cartopress_cartodb_tablename'])) {
				$str = esc_attr($cpoptions['cartopress_cartodb_tablename']);
			} else {
				$str = null;
			}
			printf(
				'<p class="cpdb-specialinstruction">Please enter a table name. You may use an existing table from your CartoDB database, or if you use a unique table name, a new table will be created in your CartoDB account (recommended).</p>
				<img src="' . admin_url('/images/yes.png') . '" id="cpbd_tableconnect_connected" /><input type="text" name="cartopress_admin_options[cartopress_cartodb_tablename]" id="cartopress_cartodb_tablename" placeholder="Enter A Unique Name for Your CartoDB Table" value="%s" />', $str
			);
		}
	
	}
    
} //end if class exists
?>