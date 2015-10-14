jQuery(document).ready(function($){
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
			} else {
				alert("Unknown error: " + response);
			}
		});
	});
});