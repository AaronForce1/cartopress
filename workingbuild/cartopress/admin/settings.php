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
		 * Holds the values to be used in the fields callbacks
		 */
		private $options;
		
	
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
			register_setting( 'cartopress-settings-group', 'cartopress_admin_options', array( $this, 'sanitize' ) );
			add_settings_section( 'cartopress_required_info', null, array($this, 'cartopress_required_info'), 'cartopress-settings-group' );
			add_settings_section( 'cartopress_collect_info', null, array($this, 'cartopress_collect_info'), 'cartopress-settings-group' );
			add_settings_section( 'cartopress_sync_info', null, array($this, 'cartopress_sync_info'), 'cartopress-settings-group' );
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
		
	
	}
	
	
	if( is_admin() )
		$cartopress_settings = new cartopress_settings();
    
} //end if class exists
?>