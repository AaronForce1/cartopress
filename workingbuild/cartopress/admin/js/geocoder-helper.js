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
	
	
	function deleteRecord() {
		var post_id = $('#cpdb-delete-button').attr('data-post_id');
		var data = {
			action: 'cartopress_delete_row',
			post_id: post_id,
			cartopress_delete_row_nonce: cartopress_geocoder_ajax.cartopress_delete_row_nonce
		};
		$.post(ajaxurl, data, function(response){
			
			alert(response);
		});
	}
	
	$(document).ready(function(){
		// show/hide admin panel
		var visible = {"height":"100%","opacity":"1", "border-top-width":"1px", "border-bottom-width":"0px"};
		var hidden = {"height":"0px","opacity":"0", "border-top-width":"0px"};
		function show_admin_panel(){
			$('#cpdb-admin-panel').css({"display":"block"}).animate(visible, {duration: 200});
		}
		function hide_admin_panel(){
			$('#cpdb-admin-panel').animate(hidden, {duration: 100, complete: function() {
				$('#cpdb-admin-panel').css({"display":"none"});
			}});
		}
		$('#cpdb-admin-toggle').click(function(){
			if ($('#cpdb-admin-panel').height() == 0) {
				show_admin_panel();
			} else {
				hide_admin_panel();
			}
		}); // end show/hide custom fields
		
		$('#cpdb-delete-button').click(function(){
			var confirm_exists = confirm("Are you sure you want to do this? This will delete the record in your CartoDB and remove all geo data associated with this post.");
			if (confirm_exists == true) {
				$('#cpdb-cartodb-id').remove();
				$('#cp_geo_displayname').val('');
				$('#cp_geo_lat').val('');
				$('#cp_geo_long').val('');
				$('#cp_geo_streetnumber').val('');
				$('#cp_geo_street').val('');
				$('#cp_geo_postal').val('');
				$('#cp_geo_adminlevel4_vill_neigh').val('');
				$('#cp_geo_adminlevel3_city').val('');
				$('#cp_geo_adminlevel2_county').val('');
				$('#cp_geo_adminlevel1_st_prov_region').val('');
				$('#cp_geo_adminlevel0_country').val('');
				deleteRecord();
				hide_admin_panel();
			} else {
				return;
			}
		});
	});
