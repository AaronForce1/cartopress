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
		public function __construct()
		{
			add_action( 'admin_init', array( $this, 'initialize_settings' ) );
		}
	
		
	
		/**
		 * Register and add settings
		 */
		public function initialize_settings()
		{        
			register_setting( 'cartopress_settings_group', 'cartopress_admin_options', array( $this, 'sanitize' ) );
			add_settings_section( 'cartodb_required_info', ' ', array($this, 'cartodb_required_info'), 'cartopress-settings' );  
		}
	
		/**
		 * Sanitize each setting field as needed
		 *
		 * @param array $input Contains all settings fields as array keys
		 */
		public function sanitize( $input )
		{
			$new_input = array();
			if( isset( $input['cartopress_cartodb_apikey'] ) )
				$new_input['cartopress_cartodb_apikey'] = sanitize_text_field( $input['cartopress_cartodb_apikey'] );
	
			if( isset( $input['cartopress_cartodb_username'] ) )
				$new_input['cartopress_cartodb_username'] = sanitize_text_field( $input['cartopress_cartodb_username'] );
			
			if( isset( $input['cartopress_cartodb_password'] ) )
				$new_input['cartopress_cartodb_password'] = sanitize_text_field( $input['cartopress_cartodb_password'] );
			
			if( isset( $input['cartopress_cartodb_tablename'] ) )
				$new_input['cartopress_cartodb_tablename'] = sanitize_text_field( $input['cartopress_cartodb_tablename'] );
	
			return $new_input;
		}
	
		/** 
		 * Print the Section text
		 */
		public function cartodb_required_info()
		{
			add_settings_field( 'cartopress_cartodb_apikey', 'API Key:', array( $this, 'cartopress_cartodb_apikey_callback' ), 'cartopress-settings', 'cartodb_required_info', array( 'label_for' => 'cartopress_cartodb_apikey') );      
			add_settings_field( 'cartopress_cartodb_username', 'Username:', array( $this, 'cartopress_cartodb_username_callback' ), 'cartopress-settings', 'cartodb_required_info', array( 'label_for' => 'cartopress_cartodb_username') );      
			add_settings_field( 'cartopress_cartodb_password', 'Password:', array( $this, 'cartopress_cartodb_password_callback' ), 'cartopress-settings', 'cartodb_required_info', array( 'label_for' => 'cartopress_cartodb_password') );      
			add_settings_field( 'cartopress_cartodb_tablename', 'Table Name:', array( $this, 'cartopress_cartodb_tablename_callback' ), 'cartopress-settings', 'cartodb_required_info', array( 'label_for' => 'cartopress_cartodb_tablename') );      
		}
	
		/** 
		 * Get the settings option array and print one of its values
		 */
		public function cartopress_cartodb_apikey_callback()
		{
			printf(
				'<input type="text" name="cartopress_admin_options[cartopress_cartodb_apikey]" id="cartopress_cartodb_apikey" placeholder="Enter CartoDB API Key" value="%s" />',
				isset( $this->options['cartopress_cartodb_apikey'] ) ? esc_attr( $this->options['cartopress_cartodb_apikey']) : ''
			);
		}
	
		public function cartopress_cartodb_username_callback()
		{
			printf(
				'<input type="text" name="cartopress_admin_options[cartopress_cartodb_username]" id="cartopress_cartodb_username" placeholder="Enter CartoDB Username" value="%s" />',
				isset( $this->options['cartopress_cartodb_username'] ) ? esc_attr( $this->options['cartopress_cartodb_username']) : ''
			);
		}
		
		public function cartopress_cartodb_password_callback()
		{
			printf(
				'<input type="text" name="cartopress_admin_options[cartopress_cartodb_password]" id="cartopress_cartodb_password" placeholder="Enter CartoDB Account Password" value="%s" />',
				isset( $this->options['cartopress_cartodb_password'] ) ? esc_attr( $this->options['cartopress_cartodb_password']) : ''
			);
		}
		
		public function cartopress_cartodb_tablename_callback()
		{
			printf(
				'<input type="text" name="cartopress_admin_options[cartopress_cartodb_tablename]" id="cartopress_cartodb_tablename" placeholder="Enter A Unique Name for Your CartoDB Table" value="%s" />',
				isset( $this->options['cartopress_cartodb_tablename'] ) ? esc_attr( $this->options['cartopress_cartodb_tablename']) : ''
			);
		}
	}
	
	if( is_admin() )
		$cartopress_settings = new cartopress_settings();
    
} //end if class exists
?>