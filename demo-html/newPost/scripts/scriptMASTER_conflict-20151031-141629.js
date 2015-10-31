	var pro = {}
	
	pro.init = new Object;
	
	var currentPOS_lat;
	var currentPOS_lon;
	
	res = new Array;
	var markersArray = {};
	
	var map;
	var newSQL;
	
	$(pro.init)
		.queue(function(){console.log('INIT1');
			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(showPosition);
			} else {
				$('#comments').innerHTML = "Geolocation is not supported by this browser.";
			}
			
			function showPosition(position) {
				currentPOS_lat = position.coords.latitude;
				currentPOS_lon = position.coords.longitude;
				
				$(pro.init).dequeue();	
			}
		
		})
		
		.queue(function(){console.log('INIT2');
			map = new L.Map('map', {
				center: [currentPOS_lat, currentPOS_lon],
				zoom: 6,
				maxzoom: 16
			});
			
			L.tileLayer('https://cartodb-basemaps-{s}.global.ssl.fastly.net/light_all/{z}/{x}/{y}.png', {
    				attribution: '© <a href=\"http://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors © <a href= \"http://cartodb.com/attributions\">CartoDB</a>'
    			}).addTo(map);
			
			var layerUrl = 'http://troyhallisey.cartodb.com/api/v2/viz/4673bf8a-afe8-11e4-8a51-0e018d66dc29/viz.json';
			
			
			cartodb.createLayer(map, layerUrl)
			  .addTo(map)
			  .on('done', function(layer) {
				  var marker = L.marker([currentPOS_lat, currentPOS_lon]).addTo(map);
			  }).on('error', function() {
				//log the error
			  });
			  
			  
			  
			$(pro.init).dequeue();
		})

	window.onload = pro.init;
	
	
	function doClick() {console.log('DOCLICK');
		pro.OpenStreetQUERY = new Object;
		
		var res;
		
		$(pro.OpenStreetQUERY)
			.queue(function(){console.log('Checking Map...');
				checkMap();	
			})
		
			.queue(function(){console.log('OS Query 1');
				// OPENMAPQUEST'S API ACCESS POINT WHICH USES OPEN STREETMAPS
				//var HOST_URL = "http://open.mapquestapi.com/nominatim/v1/search.php?format=json&q=MY_LOC&json_callback=renderResults";
				//var HOST_URL2 = "http://open.mapquestapi.com/nominatim/v1/search.php?format=json&q=MY_LOC";
				var HOST_URL = "http://nominatim.openstreetmap.org/search?format=json&q=MY_LOC&json_callback=renderResults";
				var HOST_URL2 = "http://nominatim.openstreetmap.org/search?format=json&q=MY_LOC";
				
				var newLoc = $('input#omnibar').val();
				var newURL = HOST_URL2.replace('MY_LOC', newLoc);				
				
				$('div.result').remove();
				
				$.ajax({
					type: 'GET',
					url: newURL,
					dataType: 'jsonp',
					jsonp: 'json_callback',
					error: function(){console.log('ERROR')},
					success: renderResults
				});
				
				console.log(newLoc);
				console.log(newURL);
				
			})
			
			.queue(function(){
				bindMapItems();
				completeQuery();
			})
		
	}
	
	function renderResults(response) {console.log('    Render Results');
		res = response;
		
		// -- var markersLayer = new L.LayerGroup();	//layer contain searched elements
		// -- map.addLayer(markersLayer);
		
		for(x = 0; x<res.length; x++) {
			var HTML = "<div class='result' data-geoRef='id"+x+"'><h1>Address: "+res[x].display_name+"</h1><h2>Latitude: "+res[x].lat+"</h2><h2>Longitude: "+res[x].lon+"</h2></div>"
			$('section#results').append(HTML);
			
			markersArray["id"+x] = [res[x].lat, res[x].lon, {title: res[x].display_name}];
			
			// -- L.marker(L.latLng(res[x].lat, res[x].lon), {title: res[x].display_name}).addTo( markersLayer );
			
			
			
			markersArray["id"+x] = L.marker([res[x].lat, res[x].lon], {
				draggable: true,
				title: 'Hover Text',
				opacity: 0.8
			})/*.addTo(map);*/
			map.addLayer(markersArray["id"+x]);

			if ( (x+1) == res.length ) {
				// FIT MAP TO MARKERS
				
				var length = res.length;
				bounds = new Array;
				
				for (i = 0; i<res.length; i++) {bounds[i] = [res[i]	.lat, res[i].lon]}
				map.fitBounds(bounds, {maxzoom: 10});
				
				/*var list = new L.Control.ListMarkers({layer: markersLayer, itemIcon: null});
					list.on('item-mouseover', function(e) {
						e.layer.setIcon(L.icon({
							iconUrl: '../images/select-marker.png'
						}))
					}).on('item-mouseout', function(e) {
						e.layer.setIcon(L.icon({
							iconUrl: L.Icon.Default.imagePath+'/marker-icon.png'
						}))
					});
				
				map.addControl( list );*/
				
				dequeue();
				
			}
			else {}
			
		}
		
		// ORIGINAL LOCATION OF CartoDBInitialize Function - Temporary SQL Database Creation. Located in ETC Now.
		// CartoDBInitialize(newSQL);
		
		function dequeue(){$(pro.OpenStreetQUERY).dequeue();}
	}
	
	function checkMap() {
		var resultsLength = $('section#results>.result').length
		
		if ($('section#results').hasClass('queried')) {
			for (x=resultsLength; x>=0; x--) {
				if (x===0) {
					$('section#results').removeClass('queried');
					markersArray = {};
					$(pro.OpenStreetQUERY).dequeue();
					
					console.log('    Map Reset');
				}
				
				else {
					map.removeLayer('markersArray.id'+x);
					$('section#results>.result')[x-1].remove();
				}
				
			}
		}
		
		else{$(pro.OpenStreetQUERY).dequeue(); console.log('    Map Checked and Clean');}
	}
	
	function bindMapItems() {
		var resultBox = $('div.result');
		
		resultBox.on('mouseover', function(){
			var i = this.dataset.georef
			
			
		})
	}
	
	function completeQuery() {
		$('section#results').addClass('queried')
	}
