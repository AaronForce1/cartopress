<?php
/**
 * CartoPress Settings
 *
 * @package cartopress
 */
 
 /* register cartopress-settings
 *	@since 0.1.0
 */

if (!class_exists('cartopress_settings')) {
	
	class cartopress_settings
	{
		/**
		 * Start up
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'initialize_settings' ) );
		}
	
		/**
		 * Register and add settings
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
		 * Sanitize each setting field as needed
		 *
		 * @param array $input Contains all settings fields as array keys
		 */
		public function sanitize( $input ) {
			$new_input = array();
			foreach($input as $key => $val) {
				$new_input[ $key ] = sanitize_text_field( $val );
			}
			return $new_input;
		}

		/** 
		 * Print the Section text
		 */
		public function cartopress_sync_customfields() {
			$customfield_options = get_option('cartopress_custom_fields');
			foreach ($customfield_options as $key=>$value) {
				echo '<tr id="cpdb_rowfor_' . $value['cartodb_column'] . '">
						<td align="center"><input type="checkbox" name="cartopress_custom_fields[' . $value['cartodb_column'] . '][sync]" id="cartopress_custom_fields_sync_'.$value['cartodb_column'].'"  value="1"' . checked( 1, $value['sync'], false ) . '/></td>
						<td><input type="text" name="cartopress_custom_fields[' . $value['cartodb_column'] . '][custom_field]" id="cartopress_custom_fields_fieldname_'.$value['cartodb_column'].'" value="'.esc_attr($value['custom_field']).'" class="disabled" readonly/></td>
						<td><input type="text" name="cartopress_custom_fields[' . $value['cartodb_column'] . '][cartodb_column]" id="cartopress_custom_fields_cartodbcol_'.$value['cartodb_column'].'" value="'.esc_attr($value['cartodb_column']).'" class="disabled" readonly/></td>
						<td><div class="deletebutton button" id="delete_' . $value['cartodb_column'] . '">Remove</div></td>
					  </tr>';
				
			}
		}
		
		public function cartopress_required_info() {
			add_settings_field( 'cartopress_cartodb_apikey', 'API Key:', array( $this, 'cartopress_cartodb_apikey_callback' ), 'cartopress-settings', 'cartopress_required_info', array( 'label_for' => 'cartopress_cartodb_apikey', 'type' => 'text') );      
			add_settings_field( 'cartopress_cartodb_username', 'Username:', array( $this, 'cartopress_cartodb_username_callback' ), 'cartopress-settings', 'cartopress_required_info', array( 'label_for' => 'cartopress_cartodb_username', 'type' => 'text') );      
			add_settings_field( 'cartopress_cartodb_tablename', 'Table Name:', array( $this, 'cartopress_cartodb_tablename_callback' ), 'cartopress-settings', 'cartopress_required_info', array( 'label_for' => 'cartopress_cartodb_tablename', 'type' => 'text') );      
			add_settings_field( 'cartopress_cartodb_verified', null, array( $this, 'cartopress_cartodb_verified_callback' ), 'cartopress-settings', 'cartopress_required_info', array( 'type' => 'text') );      
		}

		public function cartopress_collect_info() {
			add_settings_field('cartopress_collect_posts', null, array($this, 'cartopress_cartodb_posts_callback'), 'cartopress-settings', 'cartopress_collect_info', array( 'type' => 'checkbox') );
			add_settings_field('cartopress_collect_pages', null, array($this, 'cartopress_cartodb_pages_callback'), 'cartopress-settings', 'cartopress_collect_info', array( 'type' => 'checkbox') );
			add_settings_field('cartopress_collect_media', null, array($this, 'cartopress_cartodb_media_callback'), 'cartopress-settings', 'cartopress_collect_info', array( 'type' => 'checkbox') );
		}
		
		public function cartopress_sync_info() {
			add_settings_field('cartopress_collect_categories', null, array($this, 'cartopress_cartodb_categories_callback'), 'cartopress-settings', 'cartopress_sync_info', array( 'type' => 'checkbox') );
			add_settings_field('cartopress_collect_tags', null, array($this, 'cartopress_cartodb_tags_callback'), 'cartopress-settings', 'cartopress_sync_info', array( 'type' => 'checkbox') );
			add_settings_field('cartopress_collect_featuredimage', null, array($this, 'cartopress_cartodb_featuredimage_callback'), 'cartopress-settings', 'cartopress_sync_info', array( 'type' => 'checkbox') );
			add_settings_field('cartopress_collect_format', null, array($this, 'cartopress_cartodb_format_callback'), 'cartopress-settings', 'cartopress_sync_info', array( 'type' => 'checkbox') );
			add_settings_field('cartopress_collect_author', null, array($this, 'cartopress_cartodb_author_callback'), 'cartopress-settings', 'cartopress_sync_info', array( 'type' => 'checkbox') );
			add_settings_field('cartopress_collect_customfields', null, array($this, 'cartopress_cartodb_customfields_callback'), 'cartopress-settings', 'cartopress_sync_info', array( 'type' => 'checkbox') );
		}
		
		
		/** 
		 * Get the settings option array and print one of its values
		 */
		public function cartopress_cartodb_categories_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );	
			$theoption = esc_attr($cpoptions['cartopress_sync_categories']);
			printf(
				'<input type="checkbox" name="cartopress_admin_options[cartopress_sync_categories]" id="cartopress_sync_categories"  value="1"' . checked( 1, $theoption, false ) . '/><label for="cartopress_sync_categories" class="label">Categories</label>'
			);
		}
		public function cartopress_cartodb_tags_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			$theoption = esc_attr($cpoptions['cartopress_sync_tags']);
			printf(
				'<input type="checkbox" name="cartopress_admin_options[cartopress_sync_tags]" id="cartopress_sync_tags"  value="1"' . checked( 1, $theoption, false ) . '/><label for="cartopress_sync_tags" class="label">Tags</label>'
			);
		}
		public function cartopress_cartodb_featuredimage_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			$theoption = esc_attr($cpoptions['cartopress_sync_featuredimage']);
			printf(
				'<input type="checkbox" name="cartopress_admin_options[cartopress_sync_featuredimage]" id="cartopress_sync_featuredimage"  value="1"' . checked( 1, $theoption, false ) . '/><label for="cartopress_sync_featuredimage" class="label">Featured Image</label>'
			);
		}
		public function cartopress_cartodb_customfields_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			$theoption = esc_attr($cpoptions['cartopress_sync_customfields']);
			printf(
				'<input type="checkbox" name="cartopress_admin_options[cartopress_sync_customfields]" id="cartopress_sync_customfields"  value="1"' . checked( 1, $theoption, false ) . '/><label for="cartopress_sync_customfields" class="label">Custom Fields</label>'
			);
		}
		public function cartopress_cartodb_author_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			$theoption = esc_attr($cpoptions['cartopress_sync_author']);
			printf(
				'<input type="checkbox" name="cartopress_admin_options[cartopress_sync_author]" id="cartopress_sync_author"  value="1"' . checked( 1, $theoption, false ) . '/><label for="cartopress_sync_author" class="label">Author</label>'
			);
		}
		public function cartopress_cartodb_format_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			$theoption = esc_attr($cpoptions['cartopress_sync_format']);
			printf(
				'<input type="checkbox" name="cartopress_admin_options[cartopress_sync_format]" id="cartopress_sync_format"  value="1"' . checked( 1, $theoption, false ) . '/><label for="cartopress_sync_format" class="label">Post Format</label>'
			);
		}
		public function cartopress_cartodb_posts_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			$theoption = esc_attr($cpoptions['cartopress_collect_posts']);
			printf(
				'<input type="checkbox" name="cartopress_admin_options[cartopress_collect_posts]" id="cartopress_collect_posts"  value="1"' . checked( 1, $theoption, false ) . '/><label for="cartopress_collect_posts" class="label">Posts</label>'
			);
		}
		
		public function cartopress_cartodb_pages_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			$theoption = esc_attr($cpoptions['cartopress_collect_pages']);
			printf(
				'<input type="checkbox" name="cartopress_admin_options[cartopress_collect_pages]" id="cartopress_collect_pages"  value="1"' . checked( 1, $theoption, false ) . '/><label for="cartopress_collect_pages" class="label">Pages</label>'
			);
		}
		
		public function cartopress_cartodb_media_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			$theoption = esc_attr($cpoptions['cartopress_collect_media']);
			printf(
				'<input type="checkbox" name="cartopress_admin_options[cartopress_collect_media]" id="cartopress_collect_media"  value="1"' . checked( 1, $theoption, false ) . '/><label for="cartopress_collect_media" class="label">Media</label>'
			);
		}
	
		public function cartopress_cartodb_apikey_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			$str = esc_attr($cpoptions['cartopress_cartodb_apikey']);
			printf(
				'<input type="text" name="cartopress_admin_options[cartopress_cartodb_apikey]" id="cartopress_cartodb_apikey" placeholder="Enter CartoDB API Key" value="%s" />', $str
			);
		}
		
		public function cartopress_cartodb_verified_callback()
		{
			$str = esc_attr(get_option('cartopress_cartodb_verified'));
			printf(
				'<input type="hidden" name="cartopress_cartodb_verified" id="cartopress_cartodb_verified" value="%s" />', $str
			);
		}

		public function cartopress_cartodb_username_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			$str = esc_attr($cpoptions['cartopress_cartodb_username']);
			printf(
				'<input type="text" name="cartopress_admin_options[cartopress_cartodb_username]" id="cartopress_cartodb_username" placeholder="Enter CartoDB Username" value="%s" />', $str
			);
		}
		
		public function cartopress_cartodb_tablename_callback()
		{
			$cpoptions = get_option( 'cartopress_admin_options', '' );
			$str = esc_attr($cpoptions['cartopress_cartodb_tablename']);
			printf(
				'<p class="cpdb-specialinstruction">Please enter a table name. You may use an existing table from your CartoDB database, or if you use a unique table name, a new table will be created in your CartoDB account (recommended).</p>
				<img src="' . admin_url('/images/yes.png') . '" id="cpbd_tableconnect_connected" /><input type="text" name="cartopress_admin_options[cartopress_cartodb_tablename]" id="cartopress_cartodb_tablename" placeholder="Enter A Unique Name for Your CartoDB Table" value="%s" />', $str
			);
		}
		
		// generate array of all non-hidden metakeys
		public function generate_metakeys(){
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
		    $meta_keys = $wpdb->get_col($wpdb->prepare($query));
		    //set_transient('all_meta_keys', $meta_keys, 60*60*24); (maybe use this in the future)
		    return $meta_keys;
		} //end generate_metakeys()

	
	}
	
	
	if( is_admin() )
		$cartopress_settings = new cartopress_settings();
    
} //end if class exists
?>