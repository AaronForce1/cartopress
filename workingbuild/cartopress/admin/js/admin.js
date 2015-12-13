jQuery(document).ready(function($){
	
	// show checkmark when account settings are situated
	function show_verified() {
		if ($('#cartopress_cartodb_verified').val() == 'verified') {
			$('#cpbd_tableconnect_connected').show();
			$('#cartopress_cartodb_apikey').on('input', function() {
				$('#cartopress_cartodb_verified').val('notverified');
				$('#cpbd_tableconnect_connected').hide();
			});
			$('#cartopress_cartodb_username').on('input', function() {
				$('#cartopress_cartodb_verified').val('notverified');
				$('#cpbd_tableconnect_connected').hide();
			});
			$('#cartopress_cartodb_tablename').on('input', function() {
				$('#cartopress_cartodb_verified').val('notverified');
				$('#cpbd_tableconnect_connected').hide();
			});
		}
		if ($('#cartopress_cartodb_verified').val() == 'notverified') {
			$('#cpbd_tableconnect_connected').hide();
		}
		
	}
	show_verified();
	
	// ajax used in the ConnectED to CartoDB process
	$('#generate_table').click(function(){
		$('#cpbd_tableconnect_loading').show();
		$(this).attr('disabled', true);
		var apikey = $('#cartopress_cartodb_apikey').val();
		var username = $('#cartopress_cartodb_username').val();
		var tablename = $('#cartopress_cartodb_tablename').val();
		var data = {
			action: 'cartopress_generate_table',
			apikey: apikey,
			username: username,
			tablename: tablename,
			cartopress_admin_nonce: cartopress_admin_ajax.cartopress_admin_nonce
			
		};
		$.post(ajaxurl, data, function(response) {
			$('#cpbd_tableconnect_loading').hide();
			$('#generate_table').attr('disabled', false);
			if (response == 'exists') {
				var confirm_exists = confirm("A table with this name already exists in your CartoDB account. Would you like to use this table anyway?");
				if (confirm_exists == true) {
					//
					function cartopressify(){
						var apikey = $('#cartopress_cartodb_apikey').val();
						var username = $('#cartopress_cartodb_username').val();
						var tablename = $('#cartopress_cartodb_tablename').val();
						var data = {
							action: 'cartopressify_table',
							apikey: apikey,
							username: username,
							tablename: tablename,
							cartopress_cartopressify_nonce: cartopress_admin_ajax.cartopress_cartopressify_nonce
						};
						$.post(ajaxurl, data, function(response) {
						 if (response == "Success") {
						 	$('#cpbd_tableconnect_connected').show();
						 }
						});	
					}
					cartopressify();
					//
					
					return;
				} else {
					$('#cartopress_cartodb_tablename').val("");
				}
			} else if (response == 'badapikey') {
				alert("Your API Key is not valid or does not match your CartoDB account. Please re-enter your API Key.");
				$('#cartopress_cartodb_apikey').val("");
			} else if (response == 'notfound') {
				alert("CartoPress could not connect to your CartoDB account. Please verify your user name is correct.");
				$('#cartopress_cartodb_username').val("");
			} else if (response == 'specialchar') {
				alert("Your table name is invalid. Please use only alpha-numeric characters and underscores. Example: tablename or table_name");
				$('#cartopress_cartodb_tablename').val("");
			} else if (response == 'success') {
				alert("Your table has been successfully created in your CartoDB account. Please note it may take a few moments to appear. If you are currently logged into your account, you may need to refresh your browser to see it among your datasets.");
				$('#cpbd_tableconnect_connected').show();
			} else {
				alert("Unknown error: " + response);
			}
		});
	}); // end Connect to CartoDB ajax
	
	// show/hide custom fields selector when option is checked
	var customfields_visible = {"height":"100%","opacity":"1", "margin-top":"10px", "padding":"20px 20px", "border-top-width":"1px", "border-bottom-width":"1px"};
	var customfields_hidden = {"height":"0px","opacity":"0", "margin-top":"0px", "padding":"0px 20px", "border-top-width":"0px", "border-bottom-width":"0px"};
	if ($('#cartopress_sync_customfields').is(':checked')) { //shows the customfield select if the option is already checked on page load
		$('#cpdb-customfields-select').css(customfields_visible);
	} else {
		$('#cpdb-customfields-select').css(customfields_hidden);
		$('#cpdb-customfields-select').css({"display":"none"});
	}
	function show_customfield_selector(){
		$('#cpdb-customfields-select').css({"display":"block"}).animate(customfields_visible, {duration: 200});
	}
	function hide_customfield_selector(){
		$('#cpdb-customfields-select').animate(customfields_hidden, {duration: 200, complete: function() {
			$('#cpdb-customfields-select').css({"display":"none"});
		}});
	}
	$('#cartopress_sync_customfields').click(function(){
		if ($(this).is(':checked')) {
			show_customfield_selector();
		} else {
			hide_customfield_selector();
		}
	}); // end show/hide custom fields
	
	// add column ajax
	function create_column() {
		var apikey = $('#cartopress_cartodb_apikey').val();
		var username = $('#cartopress_cartodb_username').val();
		var tablename = $('#cartopress_cartodb_tablename').val();
		var cartodb_column = $('#cpdb-customfield-select-menu option:selected').val();
		var custom_field = $('#cpdb-customfield-select-menu option:selected').text();
		
		if ($('#cartopress_cartodb_verified').val() != 'verified') {
			alert("Your CartoDB credentials have not been verified. Please check your credentials in the section above and click the Connect to CartoDB button. If your table is verified, a green checkmark will appear to left of the table name.");
		} else {
			var data = {
				action: 'cartopress_create_column',
				apikey: apikey,
				username: username,
				tablename: tablename,
				cartodb_column: cartodb_column,
				custom_field: custom_field,
				cartopress_create_column_nonce: cartopress_admin_ajax.cartopress_create_column_nonce
				
			};
			$.post(ajaxurl, data, function(response) {
				response = (response.slice(0,-1)).toString();
				var obj = $.parseJSON(response);
				$('#cpdb-comment').css({"display":"block"});
				if (obj.option_status == false && obj.cdb_status == false) {
					$('#cpdb-comment').removeAttr('class');
					$('#cpdb-comment').addClass('error');
					$('#cpdb-comment').html("An error occured. Column was not created in CartoDB and the Custom Field will not sync.");
				} else if (obj.option_status == false && obj.cdb_status == true) {
					$('#cpdb-comment').removeAttr('class');
					$('#cpdb-comment').addClass('error');
					$('#cpdb-comment').html("The column was created in CartoDB however the Custom Field option will not sync.");
				} else if (obj.option_status == true && obj.cdb_status == false) {
					$('#cpdb-comment').removeAttr('class');
					$('#cpdb-comment').addClass('error');
					$('#cpdb-comment').html("The Custom Field setting is set to sync, but the column could not be created in CartoDB.");
				} else if (obj.option_status == true && obj.cdb_status == true) {
					if ($('#cpdb-customfield-display table tbody tr#cpdb_rowfor_' + cartodb_column).length == 0) {
						$('#cpdb-customfield-display').css({'display':'block'});
						$('#cpdb-customfield-display table tbody').append('<tr id="cpdb_rowfor_' + cartodb_column + '"><td align="center"><input type="checkbox" name="cartopress_custom_fields[' + cartodb_column + '][sync]" id="cartopress_custom_fields_sync_' + cartodb_column + '"  value="1" checked/></td><td><input type="text" name="cartopress_custom_fields[' + cartodb_column + '][custom_field]" id="cartopress_custom_fields_fieldname_' + cartodb_column + '" value="' + custom_field + '" class="disabled" readonly/></td><td><input type="text" name="cartopress_custom_fields[' + cartodb_column + '][cartodb_column]" id="cartopress_custom_fields_cartodbcol_' + cartodb_column + '" value="' + cartodb_column + '" class="disabled" readonly/></td><td><div class="deletebutton button disabled" id="delete_' + cartodb_column + '">Remove</div></td></tr>');
						$('#cpdb-comment').removeAttr('class');
						$('#cpdb-comment').addClass('success');
						$('#cpdb-comment').html(obj.message);
					} else {
						$('#cpdb-comment').removeAttr('class');
						$('#cpdb-comment').addClass('warning');
						$('#cpdb-comment').html(obj.message);
					}
				} //end if
				
			}); //end post
		} //end else
	} //end create_column()
	
	// ajax event handler for create column
	$('#add_column').click(function() {
		if ($('#cpdb-customfield-select-menu option:selected').attr('id') == 'placeholder') {
			alert("You must select a custom field.");
		} else {
			create_column();
		}
	});
	
	// delete column
	function delete_column(cartodb_column, custom_field){
		var apikey = $('#cartopress_cartodb_apikey').val();
		var username = $('#cartopress_cartodb_username').val();
		var tablename = $('#cartopress_cartodb_tablename').val();
		
		if ($('#cartopress_cartodb_verified').val() != 'verified') {
			alert("Your CartoDB credentials have not been verified. Please check your credentials in the section above and click the Connect to CartoDB button. If your table is verified, a green checkmark will appear to left of the table name.");
		} else {
			var data = {
				action: 'cartopress_delete_column',
				apikey: apikey,
				username: username,
				tablename: tablename,
				cartodb_column: cartodb_column,
				custom_field: custom_field,
				cartopress_delete_column_nonce: cartopress_admin_ajax.cartopress_delete_column_nonce
				
			};
			$.post(ajaxurl, data, function(response) {
				response = (response.slice(0,-1)).toString();
				var obj = $.parseJSON(response);
				$('#cpdb-comment').css({"display":"block"});
				$('#cpdb-comment').html(obj.message);
				$('#cpdb_rowfor_' + cartodb_column).remove();
				if ( $('#cpdb-customfield-display table tbody').is(':empty') ) {
					$('#cpdb-customfield-display').css({'display':'none'});
				} else {
					$('#cpdb-customfield-display').css({'display':'table'});
				}
	
			}); //end post
		} //end else
	} // end delete_column()
	
	// ajax event handler for delete column
	$('.deletebutton').click(function() {
		var cartodb_column = (this.id).replace('delete_','');
		var custom_field = $('#cartopress_custom_fields_fieldname_' + cartodb_column).val();
		var confirm_exists = confirm("Are you sure you want to do this? All of your data in that column will be lost");
		if (confirm_exists == true) {
			delete_column(cartodb_column, custom_field);
		} else {
			return;
		}
	});
	
	
	// display the list of custom fields if there is content to display
	if ( $('#cpdb-customfield-display table tbody').is(':empty') ) {
		$('#cpdb-customfield-display').css({'display':'none'});
	} else {
		$('#cpdb-customfield-display').css({'display':'table'});
	}
	
	// blurs the readonly inputs in the custom field area
	$("#cpdb-customfield-display input[type=text]").on("focus", function(){
	  $(this).blur();
	});
	
	
}); //end document ready