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
			pro.results.hide()
			pro.mapping.create("current", assets.currentPOS)
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
			error: function(){console.log('ERROR')},
			success: bindResponse
		});			
		
		function bindResponse(response) {pro.mapping.create("query", response);}
	}