<?php
/**
 * CartoPress Sync Actions
 * 
 * Globally accessible actions for accessing geo and post data.
 * 
 * @package cartopress
 */
 

/**
 * Gets CartoPress Post ID
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @return int Returns $cp_post_id as integer
 */
function get_cartopress_postid($post_id) {
	$cp_post_id = $post_id;
	return $cp_post_id;
}

/**
 * Gets CartoPress Post Type
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @return string Returns $cp_post_type as string
 */ 
function get_cartopress_posttype($post_id) {
	$cp_post_type = get_post_type($post_id);
	return $cp_post_type;
}

/**
 * Gets CartoPress Post Title
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @param boolean $esc Return escaped value, default true.
 * @return string Returns $cp_post_title as escaped or unescaped string
 */
function get_cartopress_posttitle($post_id, $esc = true) {
	$cp_post_title = get_the_title($post_id);
	if ($esc == false) {
		return $cp_post_title;
	} else {
		return esc_html($cp_post_title);
	}
}

/**
 * Gets CartoPress Post Permalink
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @return string Returns $cp_post_permalink as string
 */
function get_cartopress_permalink($post_id) {
	$cp_post_permalink = get_permalink($post_id);
	return $cp_post_permalink;
}

/**
 * Gets CartoPress Post Date
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @return date Returns $cp_post_date as date
 */
function get_cartopress_postdate($post_id) {
	$cp_post_date = get_the_date(get_option('date_format'), $post_id);
	return $cp_post_date;
}			
			
/**
 * Gets CartoPress Post Description
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @param boolean $esc Return escaped value, default true.
 * @return string Returns $cp_post_description as escaped or unescaped string
 */			
function get_cartopress_description($post_id, $esc = true) {
	$desc = get_post_meta( $post_id, '_cp_post_description', true );
	if (!empty($desc)) {
		$cp_post_description = get_post_meta( $post_id, '_cp_post_description', true );
	} else {
		$excerpt = get_post_field('post_excerpt', $post_id);
		if (!empty ($excerpt)) {
			$cp_post_description = get_post_field('post_excerpt', $post_id);
		} else {
			$cp_post_content = get_post_field('post_content', $post_id);
			$cp_post_description = wp_trim_words( $cp_post_content, 55, '...' );
		}
	} //end if
	if ($esc == false) {
		return $cp_post_description;
	} else {
		return esc_html($cp_post_description);
	}
}
			
/**
 * Gets CartoPress Post Content
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @param boolean $esc Return escaped value, default true.
 * @return string Returns $cp_post_content as escaped or unescaped string
 */ 
function get_cartopress_postcontent($post_id, $esc = true) {
	$cpoptions = get_option( 'cartopress_admin_options', '' );
	if (isset($cpoptions['cartopress_sync_postcontent']) && $cpoptions['cartopress_sync_postcontent'] == 1) {
		$cp_post_content = get_post_field('post_content', $post_id);
	} else {
		$cp_post_content = null;
	} //end if
	if ($esc == false) {
		if (!empty($cp_post_content)) {
			return $cp_post_content;
		}
		else {
			return null;
		}
	} else {
		if (!empty($cp_post_content)) {
			return esc_html($cp_post_content);
		}
		else {
			return null;
		}
	}
}

/**
 * Gets CartoPress Post Categories
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @param boolean $esc Return escaped value, default true.
 * @return string Returns $cp_post_categories as escaped or unescaped string of comma separated categories
 */			
function get_cartopress_categories($post_id, $esc = true) {
	//defines objects based on user settings
	$cpoptions = get_option( 'cartopress_admin_options', '' );
	if (isset($cpoptions['cartopress_sync_categories']) && $cpoptions['cartopress_sync_categories'] == 1) {
		$cats = array();
		foreach(wp_get_post_categories($post_id) as $c) {
			$cat = get_category($c);
			array_push($cats,$cat->name);
			if(sizeOf($cats)>0) {
				$cp_post_categories = implode(', ',$cats);
			} else {
				$cp_post_categories = null;
			}
		} //end foreach
	} else {
		$cp_post_categories = null;
	} //end if
	if ($esc == false) {
		if (!empty($cp_post_categories)) {
			return $cp_post_categories;
		}
		else {
			return null;
		}
	} else {
		if (!empty($cp_post_categories)) {
			return esc_html($cp_post_categories);
		}
		else {
			return null;
		}
	}
}			

/**
 * Gets CartoPress Post Tags
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @param boolean $esc Return escaped value, default true.
 * @return string Returns $cp_post_tags as escaped or unescaped string of comma separated tags
 */				
function get_cartopress_tags($post_id, $esc = true) {
	$cpoptions = get_option( 'cartopress_admin_options', '' );
	if (isset($cpoptions['cartopress_sync_tags']) && $cpoptions['cartopress_sync_tags'] == 1) {
		$tags = array();
		foreach(wp_get_post_tags($post_id) as $tag) {
			array_push($tags,$tag->name);
			if(sizeOf($tags)>0) {
				$cp_post_tags = implode(', ',$tags);
			} else {
				$cp_post_tags = null;
			}
		} //end foreach
	} else {
		$cp_post_tags = null;
	} //end if
	if ($esc == false) {
		if (!empty($cp_post_tags)) {
			return $cp_post_tags;
		}
		else {
			return null;
		}
	} else {
		if (!empty($cp_post_tags)) {
			return esc_html($cp_post_tags);
		}
		else {
			return null;
		}
	}
}		

/**
 * Gets CartoPress Featured Image Url. Gets Attachment URL for Attachment Post Type.
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @return string Returns $cp_post_featuredimage_url as string
 */				
function get_cartopress_featuredimageurl($post_id) {
	$cpoptions = get_option( 'cartopress_admin_options', '' );
	if (isset($cpoptions['cartopress_sync_featuredimage']) && $cpoptions['cartopress_sync_featuredimage'] == 1) {
		$post_type = get_post_type($post_id);
		if ('attachment' === $post_type) {
			$cp_post_featuredimage_url = wp_get_attachment_url($post_id);
		} else {
			if (has_post_thumbnail( $post_id )) {
				$cp_post_featuredimage_url = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'single-post-thumbnail' );
				$cp_post_featuredimage_url = $cp_post_featuredimage_url[0];
			} else {
				$cp_post_featuredimage_url = null;
			} //end if
		} // end if
	} else {
		$cp_post_featuredimage_url = null;
	} //end if
	return $cp_post_featuredimage_url;
}			

/**
 * Gets CartoPress Post Format
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @return string Returns $cp_post_format as string
 */			
function get_cartopress_postformat($post_id) {
	$cpoptions = get_option( 'cartopress_admin_options', '' );
	if (isset($cpoptions['cartopress_sync_format']) && $cpoptions['cartopress_sync_format'] == 1) {
		$cp_post_format = get_post_format( $post_id );
		if ( false === $cp_post_format ) {
			$cp_post_format = 'standard';
		}
	} else {
		$cp_post_format = null;
	} //end if
	return $cp_post_format;
}			

/**
 * Gets CartoPress Post Author
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @return string Returns $cp_post_author as string
 */				
function get_cartopress_author($post_id) {
	$cpoptions = get_option( 'cartopress_admin_options', '' );
	if (isset($cpoptions['cartopress_sync_author']) && $cpoptions['cartopress_sync_author'] == 1) {
		$the_author_id = get_post_field( 'post_author', $post_id );
		$cp_post_author = get_the_author_meta( 'display_name', $the_author_id );
	} else {
		$cp_post_author = null;
	} //end if
	return $cp_post_author;
}			

/**
 * Gets CartoPress Custom Fields
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @return array Returns array $fields as key/value pairs for each custom fields that is to be synced
 */			
function get_cartopress_customfields($post_id) {
	$cpoptions = get_option( 'cartopress_admin_options', '' );
	if (isset($cpoptions['cartopress_sync_customfields']) && $cpoptions['cartopress_sync_customfields'] == 1) {
		$customfield_options = get_option('cartopress_custom_fields');
		$fields = array();
		if (!empty($customfield_options)) {
			foreach ($customfield_options as $key=>$value) {
				if ($value['sync'] == 1) {
					$adds = array($key => esc_html(get_post_meta($post_id, $value['custom_field'], true)));
					$fields = array_merge($fields, $adds);
					
				}
			}
		}
	} else {
		$fields = null;
	}
	return $fields;
}			

/**
 * Gets CartoPress Display Name
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @return string Returns display name from postmeta
 */				
function get_cartopress_displayname($post_id) {
	$geodata = get_post_meta( $post_id, '_cp_post_geo_data', true );
	$cp_geo_displayname = $geodata['cp_geo_displayname'];
	return wp_specialchars($cp_geo_displayname);
}

/**
 * Gets CartoPress Latitude
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @return string Returns latitire from postmeta
 */	
function get_cartopress_latitude($post_id) {
	$geodata = get_post_meta( $post_id, '_cp_post_geo_data', true );
	$cp_geo_lat = $geodata['cp_geo_lat'];
	return $cp_geo_lat;
}		

/**
 * Gets CartoPress Longitude
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @return string Returns longitude from postmeta
 */	
function get_cartopress_longitude($post_id) {
	$geodata = get_post_meta( $post_id, '_cp_post_geo_data', true );
	$cp_geo_long = $geodata['cp_geo_long'];
	return $cp_geo_long;
}

/**
 * Gets CartoPress Street Number
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @return string Returns street number from postmeta
 */	
function get_cartopress_streetnumber($post_id) {
	$geodata = get_post_meta( $post_id, '_cp_post_geo_data', true );
	$cp_geo_streetnumber = $geodata['cp_geo_streetnumber'];
	return $cp_geo_streetnumber;
}			

/**
 * Gets CartoPress Street
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @return string Returns street name from postmeta
 */				
function get_cartopress_street($post_id) {
	$geodata = get_post_meta( $post_id, '_cp_post_geo_data', true );
	$cp_geo_street = $geodata['cp_geo_street'];
	return wp_specialchars($cp_geo_street);
}			

/**
 * Gets CartoPress Postal Code
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @return string Returns postal code from postmeta
 */	
function get_cartopress_postcode($post_id) {
	$geodata = get_post_meta( $post_id, '_cp_post_geo_data', true );
	$cp_geo_postal = $geodata['cp_geo_postal'];
	return $cp_geo_postal;
}	

/**
 * Gets CartoPress Admin Level 4
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @return string Returns Admin Level 4 from postmeta
 */	
function get_cartopress_adminlevel4_vill_neigh($post_id) {
	$geodata = get_post_meta( $post_id, '_cp_post_geo_data', true );
	$cp_geo_adminlevel4_vill_neigh = $geodata['cp_geo_adminlevel4_vill_neigh'];
	return wp_specialchars($cp_geo_adminlevel4_vill_neigh);
}		

/**
 * Gets CartoPress Admin Level 3
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @return string Returns Admin Level 3 from postmeta
 */				
function get_cartopress_adminlevel3_city($post_id) {
	$geodata = get_post_meta( $post_id, '_cp_post_geo_data', true );
	$cp_geo_adminlevel3_city = $geodata['cp_geo_adminlevel3_city'];
	return wp_specialchars($cp_geo_adminlevel3_city);
}			

/**
 * Gets CartoPress Admin Level 2
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @return string Returns Admin Level 2 from postmeta
 */		
function get_cartopress_adminlevel2_county($post_id) {
	$geodata = get_post_meta( $post_id, '_cp_post_geo_data', true );
	$cp_geo_adminlevel2_county = $geodata['cp_geo_adminlevel2_county'];
	return wp_specialchars($cp_geo_adminlevel2_county);
}		

/**
 * Gets CartoPress Admin Level 1
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @return string Returns Admin Level 1 from postmeta
 */		
function get_cartopress_adminlevel1_st_prov_region($post_id) {
	$geodata = get_post_meta( $post_id, '_cp_post_geo_data', true );
	$cp_geo_adminlevel1_st_prov_region = $geodata['cp_geo_adminlevel1_st_prov_region'];
	return wp_specialchars($cp_geo_adminlevel1_st_prov_region);
}

/**
 * Gets CartoPress Admin Level 0
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @return string Returns Admin Level 0 from postmeta
 */	
function get_cartopress_adminlevel0_country($post_id) {
	$geodata = get_post_meta( $post_id, '_cp_post_geo_data', true );
	$cp_geo_adminlevel0_country = $geodata['cp_geo_adminlevel0_country'];
	return wp_specialchars($cp_geo_adminlevel0_country);
}

/**
 * Gets CartoPress Sync Fields as array
 * @since 0.1.0
 * @param int $post_id The global post id.
 * @return array $args Returns array of all of the fields to be synced to CartoDB.
 */	
function get_cartopress_sync_fields($post_id) {
	// set up the fields
	$args = array(
		'cp_post_id' => get_cartopress_postid($post_id),
		'cp_post_title' => get_cartopress_posttitle($post_id, true),
		'cp_post_content' => get_cartopress_postcontent($post_id, true),
		'cp_post_description' => get_cartopress_description($post_id, true),
		'cp_post_date' => get_cartopress_postdate($post_id),
		'cp_post_type' => get_cartopress_posttype($post_id),
		'cp_post_permalink' => get_cartopress_permalink($post_id),
		'cp_post_categories' => get_cartopress_categories($post_id, true), 
		'cp_post_tags' => get_cartopress_tags($post_id, true),
		'cp_post_featuredimage_url' => get_cartopress_featuredimageurl($post_id),
		'cp_post_format' => get_cartopress_postformat($post_id),
		'cp_post_author' => get_cartopress_author($post_id),
		'cp_geo_displayname' => get_cartopress_displayname($post_id),
		'cp_geo_streetnumber' => get_cartopress_streetnumber($post_id),
		'cp_geo_street' => get_cartopress_street($post_id),
		'cp_geo_postal' => get_cartopress_postcode($post_id),
		'cp_geo_adminlevel4_vill_neigh' => get_cartopress_adminlevel4_vill_neigh($post_id),
		'cp_geo_adminlevel3_city' => get_cartopress_adminlevel3_city($post_id),
		'cp_geo_adminlevel2_county' => get_cartopress_adminlevel2_county($post_id),
		'cp_geo_adminlevel1_st_prov_region' => get_cartopress_adminlevel1_st_prov_region($post_id),
		'cp_geo_adminlevel0_country' => get_cartopress_adminlevel0_country($post_id),
		'cp_geo_lat' => get_cartopress_latitude($post_id),
		'cp_geo_long' => get_cartopress_longitude($post_id)
		);
	
	// merge in custom fields
	$cpoptions = get_option( 'cartopress_admin_options', '' );
	if (isset($cpoptions['cartopress_sync_customfields']) && $cpoptions['cartopress_sync_customfields'] == 1) {
		$fields = get_cartopress_customfields($post_id);
		$args = array_merge($args, $fields);
	}
	
	//return
	return $args;
}

/**
 * Performs add, update and delete functions for selected CartoDB row
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 */	
function cartopress_update_row($post_id) {
	if (get_post_meta($post_id, '_cp_post_donotsync', true) == 1) {
		return;
	} else {
		if (get_post_status( $post_id ) != 'publish') {
			cartopress_sync::cartodb_delete($post_id);
		} else {
			// Special case: Post Format is not updated until after 'save_post' is called when using the Bulk Edit tool. The below sets the post format during the save_post process if the $_POST data is available.
			if ( isset( $_REQUEST['post_format'] ) && $_REQUEST['post_format'] != -1 ) {
				set_post_format($post_id, $_REQUEST['post_format']);
			}
			cartopress_sync::cartodb_sync($post_id);
		} // end if
	} // end if
}

/**
 * Delete function for attachment post type
 * 
 * @since 0.1.0
 * @param int $post_id The global post id.
 */	
function cartopress_delete_attachment($post_id) {
	$post_type = get_post_type($post_id);
	if ('attachment' === $post_type) {
		cartopress_sync::cartodb_delete($post_id);
	} else {
		return;
	}
}

?>