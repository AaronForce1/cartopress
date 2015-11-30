<?php
/**
 * CartoPress Bulk actions
 *
 * @package cartopress
 */
 
 /* adds Bulk Action capabilities to CartoPress
 *	@since 0.1.0
 */


if (!class_exists('cartopress_bulkactions')) {
 
	class cartopress_bulkactions {
		
		public function __construct() {
			
			if(is_admin()) {
				// admin actions/filters
				require( cartopress_admin_dir . 'cp-sql.php' );
				
				//add_action('admin_footer-edit.php', array(&$this, 'custom_bulk_admin_footer'));
				add_action('admin_footer', array(&$this, 'custom_bulk_admin_footer'));
				add_action('load-edit.php', array(&$this, 'custom_bulk_action'));
				add_action('admin_notices', array(&$this, 'custom_bulk_admin_notices'));
			}
		}
		
		
		/**
		 * Step 1: add the custom Bulk Action to the select menus
		 */
		function custom_bulk_admin_footer() {
			global $post_type; $pagenow;
			
			if ($post_type == 'post' || $post_type == 'page') {
				?>
					<script type="text/javascript">
						jQuery(document).ready(function() {
							jQuery('<option>').val('cartopress_delete').text('<?php _e('Delete From CartoDB')?>').appendTo("select[name='action']");
							jQuery('<option>').val('cartopress_delete').text('<?php _e('Delete From CartoDB')?>').appendTo("select[name='action2']");
							jQuery('<option>').val('cartopress_restore').text('<?php _e('Restore To CartoDB')?>').appendTo("select[name='action']");
							jQuery('<option>').val('cartopress_restore').text('<?php _e('Restore To CartoDB')?>').appendTo("select[name='action2']");
						});
					</script>
				<?php
	    	}
		
		}
		
		
		/**
		 * Step 2: handle the custom Bulk Action
		 * 
		 * Based on the post http://wordpress.stackexchange.com/questions/29822/custom-bulk-action
		 */
		function custom_bulk_action() {
			global $typenow; $pagenow;
			$post_type = $typenow;
			
			if($post_type == 'post' || $post_type == 'page') {
				
				// get the action
				$wp_list_table = _get_list_table('WP_Posts_List_Table');  // depending on your resource type this could be WP_Users_List_Table, WP_Comments_List_Table, etc
				$action = $wp_list_table->current_action();
				
				$allowed_actions = array("cartopress_delete", "cartopress_restore");
				if(!in_array($action, $allowed_actions)) {
					return;
				}
				
				// security check
				check_admin_referer('bulk-posts');
				
				// make sure ids are submitted.  depending on the resource type, this may be 'media' or 'ids'
				if(isset($_REQUEST['post'])) {
					$post_ids = array_map('intval', $_REQUEST['post']);
				}
				
				if(empty($post_ids)) {
					return;
				}
				
				// this is based on wp-admin/edit.php
				$sendback = remove_query_arg( array('cartopress_deleted', 'cartopress_restored', 'untrashed', 'deleted', 'ids'), wp_get_referer() );
				if ( ! $sendback ) {
					$sendback = admin_url( "edit.php?post_type=$post_type" );
				}
				$pagenum = $wp_list_table->get_pagenum();
				$sendback = add_query_arg( 'paged', $pagenum, $sendback );
				
				switch($action) {
					case 'cartopress_delete':
						
						// if we set up user permissions/capabilities, the code might look like:
						//if ( !current_user_can($post_type_object->cap->export_post, $post_id) )
						//	wp_die( __('You are not allowed to export this post.') );
						$sql_distinct = 'SELECT DISTINCT cp_post_id FROM ' . cartopress_table;
						$cartopress_ids = cartopress_sync::update_cartodb($sql_distinct, cartopress_apikey, cartopress_username, true);
						$cartopress_ids = $cartopress_ids->rows;
						$temp = array();
						foreach ($cartopress_ids as $key=>$value) {
							array_push($temp, $value->cp_post_id);
						}
						
						
						$deleted = 0;
						foreach( $post_ids as $post_id ) {
							if (in_array($post_id, $temp)) {
								cartopress_sync::cartodb_delete($post_id);
								$deleted++;
							} else {
								return false;
							}
						}
						
						$sendback = add_query_arg( array('cartopress_deleted' => $deleted, 'ids' => join(',', $post_ids) ), $sendback );
					break;
					
					case 'cartopress_restore':
						$restored = 0;
						foreach( $post_ids as $post_id ) {
							cartopress_sync::cartodb_sync($post_id);
							$restored++;
						}
						$sendback = add_query_arg( array('cartopress_restored' => $restored, 'ids' => join(',', $post_ids) ), $sendback );
					break;
					
					default: return;
				}
				
				$sendback = remove_query_arg( array('action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status',  'post', 'bulk_edit', 'post_view'), $sendback );
				
				wp_redirect($sendback);
				exit();
			} //end

		}
		
		
		/**
		 * Step 3: display an admin notice on the Posts page after exporting
		 */
		function custom_bulk_admin_notices() {
			global $post_type, $pagenow;
			
			if($pagenow == 'edit.php' && $post_type == 'post' && isset($_REQUEST['cartopress_deleted']) && (int) $_REQUEST['cartopress_deleted']) {
				$message = sprintf( _n( 'Post Deleted From CartoDB.', '%s posts deleted from CartoDB.', $_REQUEST['cartopress_deleted'] ), number_format_i18n( $_REQUEST['cartopress_deleted'] ) );
				echo "<div class=\"updated\"><p>{$message}</p></div>";
			}
			if($pagenow == 'edit.php' && $post_type == 'post' && isset($_REQUEST['cartopress_restored']) && (int) $_REQUEST['cartopress_restored']) {
				$message = sprintf( _n( 'Post Restored To CartoDB.', '%s posts restored to CartoDB.', $_REQUEST['cartopress_restored'] ), number_format_i18n( $_REQUEST['cartopress_restored'] ) );
				echo "<div class=\"updated\"><p>{$message}</p></div>";
			}
			if($pagenow == 'edit.php' && $post_type == 'page' && isset($_REQUEST['cartopress_deleted']) && (int) $_REQUEST['cartopress_deleted']) {
				$message = sprintf( _n( 'Page Deleted From CartoDB.', '%s pages deleted from CartoDB.', $_REQUEST['cartopress_deleted'] ), number_format_i18n( $_REQUEST['cartopress_deleted'] ) );
				echo "<div class=\"updated\"><p>{$message}</p></div>";
			}
			if($pagenow == 'edit.php' && $post_type == 'page' && isset($_REQUEST['cartopress_restored']) && (int) $_REQUEST['cartopress_restored']) {
				$message = sprintf( _n( 'Page Restored To CartoDB.', '%s pages restored to CartoDB.', $_REQUEST['cartopress_restored'] ), number_format_i18n( $_REQUEST['cartopress_restored'] ) );
				echo "<div class=\"updated\"><p>{$message}</p></div>";
			}
		}
		
	}
}
