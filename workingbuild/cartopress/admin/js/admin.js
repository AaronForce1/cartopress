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
				alert("Could not connect to your CartoDB account. Please verify your user name is correct.");
				$('#cartopress_cartodb_username').val("");
			} else if (response == 'specialchar') {
				alert("Your table name is invalid. Please use only alpha-numeric characters and underscores. Example: tablename1 or table_name1");
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
	}
	function show_customfield_selector(){
		$('#cpdb-customfields-select').animate(customfields_visible, {duration: 200});
	}
	function hide_customfield_selector(){
		$('#cpdb-customfields-select').animate(customfields_hidden, {duration: 200});
	}
	$('#cartopress_sync_customfields').click(function(){
		if ($(this).is(':checked')) {
			show_customfield_selector();
		} else {
			hide_customfield_selector();
		}
	}); // end show/hide custom fields
	
	$('#add_column').click(function() {
		alert($('#cpdb-customfield-select-menu').val());
	});
	
}); //end document ready