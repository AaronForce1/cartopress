	var pro = {}
	var assets = {}
	var marker = {}
	var inputs = {}

	// INITIALIZATION OF DOM
	$('document').ready(function(){
		assets.AMap = 0;

		inputs.display_name = $('#cp_geo_displayname');
		inputs.lat = $('#cp_geo_lat');
		inputs.lon = $('#cp_geo_long');
		inputs.address = {
			no : $('#cp_geo_streetnumber'),
			street : $('#cp_geo_street')
		};
		inputs.zip = $('#cp_geo_postal');
		inputs.L4 = $('#cp_geo_adminlevel4_vill_neigh');
		inputs.city = $('#cp_geo_adminlevel3_city');
		inputs.L2 = $('#cp_geo_adminlevel2_county');
		inputs.L1 = $('#cp_geo_adminlevel1_st_prov_region');
		inputs.country = $('#cp_geo_adminlevel0_country');

		// CUSTOM MARKER STYLES
		assets.alertMARK = L.AwesomeMarkers.icon({icon:'', markerColor: 'red'});
		assets.primaryMARK = L.AwesomeMarkers.icon({icon:'', markerColor: 'blue'});
		assets.selectedMARK = L.AwesomeMarkers.icon({icon:'', markerColor: 'green'});
	})

	function locateMe() {
		if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(showPosition);
			} else {
				$('#comments').innerHTML = "Geolocation is not supported by this browser.";
			}
			
			function showPosition(position) {
				assets.currentPOS_lat = position.coords.latitude;
				assets.currentPOS_lon = position.coords.longitude;

				currentLOC(0)
			}
	}

	function currentLOC(ID) {
		pro.engage = new Object

		$(pro.engage)
		.queue(function(){
			// CHECKS TO SEE IF MAP HAS ALREADY BEEN ACTIVATED
			// IF SO, WILL PROMPT TO CLEAR RESULTS.
			if (assets.AMap === 1) {$(pro.engage).stop()}
			else {
				// DETERMINE CURRENT LOCATION COORDINATES
				assets.AMap = 1;
				assets.map = new L.Map('map', {
					center: [assets.currentPOS_lat, assets.currentPOS_lon],
					zoom: 6,
					maxzoom: 16
				});
				
				// INITIAL MAP LOAD
				L.tileLayer('https://cartodb-basemaps-{s}.global.ssl.fastly.net/light_all/{z}/{x}/{y}.png', {
    				attribution: '© <a href=\"http://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors © <a href= \"http://cartodb.com/attributions\">CartoDB</a>'
    			}).addTo(assets.map);

				// ADDING CURRENT LOCATION MARKER TO THE MAP
    			marker.current = L.marker([assets.currentPOS_lat, assets.currentPOS_lon], {icon:assets.primaryMARK}).addTo(assets.map);
    			$(pro.engage).dequeue()
			}
		})
		.queue(function(){
				// PROPOGATE CURRENT LOCATION FIELDS
				inputs.display_name.val("Current Location").addClass('ent')
				inputs.lat.val(assets.currentPOS_lat).addClass('ent')
				inputs.lon.val(assets.currentPOS_lon).addClass('ent')
				$(pro.engage).dequeue();
		});
	}
	res = new Array;
	var newSQL;
	
	
	function doClick() {console.log('DOCLICK');
		pro.OpenStreetQUERY = new Object;
		
		$(pro.OpenStreetQUERY)
			.queue(function(){console.log('Checking Map...');
				checkMap();	
			})
		
			.queue(function(){console.log('OS Query 1');
				// OPENMAPQUEST'S API ACCESS POINT WHICH USES OPEN STREETMAPS
				var HOST_URL = "http://nominatim.openstreetmap.org/search?format=json&q=MY_LOC";
				
				var newLoc = $('#omnibar').val();
				var newURL = HOST_URL.replace('MY_LOC', newLoc);				
				
				$('div.cpdb-result-item').remove();
				
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
	
	function renderResults(r) {console.log('    Render Results');
		marker.res = {}
		// -- var markersLayer = new L.LayerGroup();	//layer contain searched elements
		// map.addLayer(markersLayer);
		
		for(x = 0; x<r.length; x++) {
			var HTML = "<div class='cpdb-result-item' data-georef='id"+x+"' data-markerref='map"+x+"'><h1>Address: "+r[x].display_name+"</h1><h2>Latitude: "+r[x].lat+"</h2><h2>Longitude: "+r[x].lon+"</h2></div>"
			$('#results section').append(HTML);
			
			marker.res["id"+x] = [r[x].lat, r[x].lon, {title: r[x].display_name}];
			
			// -- L.marker(L.latLng(res[x].lat, res[x].lon), {title: res[x].display_name}).addTo( markersLayer );
			
			marker.res["map"+x] = L.marker([r[x].lat, r[x].lon], {
				draggable: "true",
				title: "Hover Text",
				opacity: 0.8,
				icon: assets.primaryMARK
			}).addTo(assets.map);
			assets.map.addLayer(marker.res["map"+x]);

			if ( (x+1) == r.length ) {
				// FIT MAP TO MARKERS
				var length = r.length;
				bounds = new Array;
				
				for (i = 0; i<r.length; i++) {bounds[i] = [r[i].lat, r[i].lon]}
				assets.map.fitBounds(bounds, {maxzoom: 10});
				
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
				
				$(pro.OpenStreetQUERY).dequeue();
				
			}
			else {}
			
		}
		
		function dequeue(){$(pro.OpenStreetQUERY).dequeue();}
	}
	
	function checkMap() {
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

		else if (assets.AMap == 0) {
			assets.AMap = 1;
			assets.map = new L.Map('map', {
				zoom: 6,
				maxzoom: 16
			});
			
			// INITIAL MAP LOAD
			L.tileLayer('https://cartodb-basemaps-{s}.global.ssl.fastly.net/light_all/{z}/{x}/{y}.png', {
				attribution: '© <a href=\"http://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors © <a href= \"http://cartodb.com/attributions\">CartoDB</a>'
			}).addTo(assets.map);

			$(pro.OpenStreetQUERY).dequeue(); console.log('Blank Map Engaged');
		}
		
		else{$(pro.OpenStreetQUERY).dequeue(); console.log('    Map Checked and Clean');}
	}
	
	function bindMapItems() {

		var resultBox = $('.cpdb-result-item');
		
		resultBox.mouseenter(function(){
				var i = this.dataset.markerref
				marker.res[i].setIcon(assets.alertMARK)
			}).mouseleave(function(){
				var i = this.dataset.markerref
				marker.res[i].setIcon(assets.primaryMARK)
			});
		resultBox.click(function(){
			var i = this.dataset.markerref
			for(x=0; x<resultBox.length; x++) {
				if (("map"+x) === i) {
					marker.res[i].setIcon(assets.selectedMARK)
					marker.res[i].setZIndexOffset(1000)
					$(this).unbind('mouseenter').unbind('mouseleave');

					propogateResultData(x, resultBox);
				}
				else {primaryBinding(x, resultBox);}
			}
			
		});

		function primaryBinding(x, resultBox) {

			$($(resultBox)[x]).removeClass('selected')

			$($(resultBox)[x]).mouseenter(function(){
				var i = this.dataset.markerref
				marker.res[i].setIcon(assets.alertMARK)
			}).mouseleave(function(){
				var i = this.dataset.markerref
				marker.res[i].setIcon(assets.primaryMARK)
			});

			var i = $(resultBox)[x].dataset.markerref
			marker.res[i].setIcon(assets.primaryMARK)
			marker.res[i].setZIndexOffset(0)
		}

		function propogateResultData(x, resultBox) {
			$($(resultBox)[x]).addClass('selected')

			pro.MRpropo = new Object;
			$(pro.MRpropo)
				.queue(function(){sanitizeInput();})
				.queue(function(){
					inputs.display_name.val(marker.res["id"+x][2].title).addClass('ent')
					inputs.lat.val(marker.res["id"+x][0]).addClass('ent');
					inputs.lon.val(marker.res["id"+x][1]).addClass('ent');
				})

			function sanitizeInput() {
				$('div#cpdb-geocode-values input').val("");
				$(pro.MRpropo).dequeue();
			}

			
		}

	}
	
	function completeQuery() {
		$('section#results').addClass('queried');
	}
