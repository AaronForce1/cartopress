$ = jQuery;
function locateMe() {
	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(showPosition);
	} else {
		$('#comments').innerHTML = "Geolocation is not supported by this browser.";
	}
		
	function showPosition(position) {
		assets.currentPOS = new Object;
			assets.currentPOS.lat = position.coords.latitude;
			assets.currentPOS.lon = position.coords.longitude;
			assets.currentPOS.zoom = 6;
		pro.results.hide();
		pro.mapping.create("current", assets.currentPOS);
	}	
}
	
function doClick() {
	pro.OpenStreetQUERY = new Object;
	pro.results.show();
	var HOST_URL = "http://nominatim.openstreetmap.org/search?format=json&q=MY_LOC&addressdetails=1";
	
	var newLoc = $('#omnibar').val();
	var newURL = HOST_URL.replace('MY_LOC', newLoc);				
	
	$('div.cpdb-result-item').remove();
	
	$.ajax({
		type: 'GET',
		url: newURL,
		dataType: 'jsonp',
		jsonp: 'json_callback',
		error: function(){console.log('ERROR');},
		success: bindResponse
	});			
	
	function bindResponse(response) {pro.mapping.create("query", response);}
}

// delete record ajax
function deleteRecord() {
	var post_id = $('#cpdb-delete-button').attr('data-post_id');
	var data = {
		action: 'cartopress_delete_row',
		post_id: post_id,
		cartopress_delete_row_nonce: cartopress_geocoder_ajax.cartopress_delete_row_nonce
	};
	$.post(ajaxurl, data, function(response){
		if (response = "success") {
			$('#comments').html('<p class="success">All geo data for this post has been deleted and the record removed from CartoDB.</p>');
		} else {
			$('#comments').html('<p class="error">' + response + '</p>');
		}
	});
}

// delete record ajax
function resetRecord() {
	var post_id = $('#cpdb-reset-button').attr('data-post_id');
	var data = {
		action: 'cartopress_reset_record',
		post_id: post_id,
		cartopress_resetrecord_nonce: cartopress_geocoder_ajax.cartopress_resetrecord_nonce
	};
	$.post(ajaxurl, data, function(r){
		r = $.parseJSON(r.toString());
		$.each(r,function(k,v) {
			$('#'+k).val(v);
		});
		$('#comments').html('<p class="success">The geo data fields have been reverted to the most recent WordPress saved data.</p>');
	});
}

$(document).ready(function(){
	var visible = {"height":"100%","opacity":"1", "border-top-width":"1px", "border-bottom-width":"0px"};
	var hidden = {"height":"0px","opacity":"0", "border-top-width":"0px"};
	
	// functions to show/hide/toggle the admin menu
	function show_admin_panel(){
		$('#cpdb-admin-panel').css({"display":"block"}).animate(visible, {duration: 200});
	}
	function hide_admin_panel(){
		$('#cpdb-admin-panel').animate(hidden, {duration: 100, complete: function() {
			$('#cpdb-admin-panel').css({"display":"none"});
		}});
	}
	function toggle_admin_panel(){
		if ($('#cpdb-admin-panel').height() == 0) {
			show_admin_panel();
		} else {
			hide_admin_panel();
		}
	}
	
	// function to clear the geo field inputs
	function clear_geo_fields(){
		$('#cpdb-cartodb-id').remove();
		$('#cpdb-geocode-fields input, #cpdb-geocode-fields textarea').val('');
	}
	
	// function to disable the delte button
	function disable_delete_reset(){
		$('#cpdb-delete-button').attr('disabled', true).addClass('disabled');
		$('#cpdb-reset-button').attr('disabled', true).addClass('disabled');
	}
	
	// show/hide admin panel
	$('#cpdb-admin-toggle').click(function(){
		toggle_admin_panel();
	}); // end show/hide admin panel
	
	// disable delete button if no geodata is present on page load
	if ($('#cp_geo_lat').val().length === 0 || $('#cp_geo_long').val().length === 0) {
		disable_delete_reset();
	}
	
	// geo data delete button action
	$('#cpdb-delete-button').click(function(){
		var confirm_exists = confirm("Are you sure you want to do this? This will delete the record in your CartoDB and remove all geo data associated with this post.");
		if (confirm_exists == true) {
			deleteRecord();
			clear_geo_fields();
			disable_delete_reset();
			hide_admin_panel();
		} else {
			return;
		}
	}); // end geo data delete button action
	
	// geo data reset button action
	$('#cpdb-reset-button').click(function(){
		$('#cpdb-geocode-fields input, #cpdb-geocode-fields textarea').removeClass('ent');
		resetRecord();
	}); //end geo data reset button action
});
