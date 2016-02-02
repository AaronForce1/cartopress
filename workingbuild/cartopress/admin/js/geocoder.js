$ = jQuery;
// VARIABLE CONTAINERS FOR FUNCTION SETTINGS!
	var pro = {};
	var assets = {};
	var marker = {};
	var inputs = {};

$(document).ready(function(){
	pro.InitializeGeocoder();
});

pro.InitializeGeocoder = function() {
	// DECLARATION OF ASSETS
	//    Currently used for accessing variables from the DOM... May not be necessarily publicly available in future.
	assets.AMap = false;

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

	// Load MAP TILES
	pro.mapping.create("init");

	// SETTING UP INTERFACE BINDINGS
	$( window ).resize(function() {
		$('#cartopress_locator #results[style], #cartopress_locator .cpdb-maincontent[style]').removeAttr('style');
		$('#toggle-in-map').css({'display':'block'});
	});
	
	$('input#unlock_manual_edit').click(function() {
	    if ($(this).is(':checked')) {
			$('#cartopress_locator #cpdb-geocode-values span input, #cartopress_locator #cpdb-geocode-values span textarea').removeClass('disabled ent');
			$('#cartopress_locator #cpdb-geocode-values span input, #cartopress_locator #cpdb-geocode-values span textarea').attr('readonly', false);
			$('#cartopress_locator #cpdb-geocode-values label').css('color','#666666');
	    } else {
	    	$('#cartopress_locator #cpdb-geocode-values span input, #cartopress_locator #cpdb-geocode-values span textarea').addClass('disabled');
	    	$('#cartopress_locator #cpdb-geocode-values span input, #cartopress_locator #cpdb-geocode-values span textarea').attr('readonly', true);
	    	$('#cartopress_locator #cpdb-geocode-values label').css('color','#AAAAAA');
	    }
	});
	
	$('#toggle-in-map, #toggle-in-search').click(function() {
		pro.results.toggle();
	});
	
	if ($('#cartodb-id').html() == '' || $('#cartodb-id').html() == null){
		$('#cpdb-cartodb-id').css({'display':'none'});
	} else {
		$('#cpdb-cartodb-id').css({'display':'block'});
	}
};

pro.results = new Object;
	pro.results.hide = function() {
		if ($(window).width() > 1200) {
			$('#cartopress_locator #results').animate({'width':'0px','opacity':'0','padding':'0px'}, {duration: 100});
			$('#cartopress_locator .cpdb-maincontent').animate({'width': '100%'}, {duration: 200});
		} else {
			$('#cartopress_locator #results').animate({'height':'0px','opacity':'0','padding':'0px'}, {duration: 100});
		}
		$('#toggle-in-map').css({'display':'block'});};
	pro.results.show = function() {
		if ($(window).width() > 1200) {
			$('#cartopress_locator #results').animate({'width':'32.5%','opacity':'1','padding':'10px'}, {duration: 200});
			$('#cartopress_locator .cpdb-maincontent').animate({'width': ($('#cpdb-metabox-wrapper').width() * .68) - 20 + 'px'}, {duration: 100});
		} else {
			$('#cartopress_locator #results').animate({'height':'100%','opacity':'1','padding':'10px'}, {duration: 200});
		}
		$('#toggle-in-map').css({'display':'none'});};
	pro.results.toggle = function() {
		if ($('#cartopress_locator #results').width() == 0 || $('#cartopress_locator #results').height() == 0) {
			pro.results.show();
		} else {
			pro.results.hide();
		}};

pro.mapping = new Object;
	pro.mapping.clean = function() {
		if ( assets.AMap == true && $('div#results').hasClass('queried')) {
			var resultsLength = $('.cpdb-result-item').length;
			for (x=resultsLength; x>=0; x--) {
				if (x===0) {
					$('div#results').removeClass('queried');
					$('section .cpdb-result-item').remove();
					delete marker.res;
					$('#cpdb-geocode-fields input, #cpdb-geocode-fields textarea').val("").removeClass("ent");
					$(pro.mapMe).dequeue();
				}
				else {
					assets.map.removeLayer(marker.res["map"+(x-1)]);
				}
			}
		}
		else if (marker.current) {
			assets.map.removeLayer(marker.current);
			setTimeout(function(){delete marker.current;}, 200);
			$('#cpdb-geocode-fields input, #cpdb-geocode-fields textarea').val("").removeClass("ent");
			$(pro.mapMe).dequeue();
		}
		else if (marker.init) {
			assets.map.removeLayer(marker.init);
			setTimeout(function(){delete marker.init;}, 200);
			$(pro.mapMe).dequeue();
		}
		else {
			$(pro.mapMe).dequeue();
		}
	};
	pro.mapping.create = function(type, geo) {
		pro.mapMe = new Object;
		$(pro.mapMe).queue(function(){
			pro.mapping.clean();
		})
		.queue(function(){
			switch (assets.AMap) {
				case false:
					if (type == "init") { if ($('#cp_geo_lat').val().length !== 0 && $('#cp_geo_long').val().length !== 0) { geo = { lat : $('#cp_geo_lat').val(), lon : $('#cp_geo_long').val(), zoom : 18 }; } else { geo = { lat : 38, lon : -08.10546875, zoom : 2 }; } }
					else {}
					createMap();
					break;
				default:
					pro.mapping.populate(type, geo);
					break;
			}

			function createMap() {
				assets.map = new L.Map('map', {
					center: [geo.lat, geo.lon],
					zoom: geo.zoom,
					maxzoom: 16
				});
				
				// INITIAL MAP LOAD
				L.tileLayer('https://cartodb-basemaps-{s}.global.ssl.fastly.net/light_all/{z}/{x}/{y}.png', {
					attribution: '© <a href=\"http://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors © <a href= \"http://cartodb.com/attributions\">CartoDB</a>'
				}).addTo(assets.map);

				assets.AMap = true;

				pro.mapping.populate(type, geo);
			}
		});
		
	};
	pro.mapping.populate = function(type, geo) {
		switch (type) {
			case "current":
				marker.current = L.marker([geo.lat, geo.lon], {icon:assets.primaryMARK}).addTo(assets.map);
				assets.map.fitBounds([[geo.lat, geo.lon]], {maxzoom: geo.zoom});
				pro.mapping.bind(type, geo);
				break;
			case "query":
				marker.res = {};
				var r = geo;
				if (r.length != 0) {
					for(x = 0; x<r.length; x++) {
						var HTML = "<div class='cpdb-result-item' data-georef='id"+x+"' data-markerref='map"+x+"'><h1>"+r[x].display_name+"</h1><h2>Latitude: <span>"+r[x].lat+"</span></h2><h2>Longitude: <span>"+r[x].lon+"</span></h2></div>";
						$('#results section').append(HTML);
						
						marker.res["id"+x] = [r[x].lat, r[x].lon, {title: r[x].display_name, address: r[x].address}];
						
						marker.res["map"+x] = L.marker([r[x].lat, r[x].lon], {
							draggable: "true",
							title: r[x].display_name,
							opacity: 0.8,
							icon: assets.primaryMARK
						}).addTo(assets.map);
						assets.map.addLayer(marker.res["map"+x]);
	
						if ( (x+1) == r.length ) {
							// FIT MAP TO MARKERS
							var length = r.length;
							bounds = new Array;
							
							for (i = 0; i<r.length; i++) {bounds[i] = [r[i].lat, r[i].lon];}
							assets.map.fitBounds(bounds, {maxzoom: 10});
							
						}
						else {}
						
					}
				} else {
					var HTML = "<div class='cpdb-result-item'><span class='howto'>There are no results. Please try a new search.</span></div>";
					$('#results section').append(HTML);
				}
				
				pro.mapping.bind(type, geo);
				break;
			case "init":
				if ($('#cp_geo_lat').val().length !== 0 && $('#cp_geo_long').val().length !== 0) {
					marker.init = L.marker([geo.lat, geo.lon], {icon:assets.primaryMARK}).addTo(assets.map);
				}
				break;
			default:
				// Display ERROR
				break;
		}
	};
	pro.mapping.bind = function(type, geo) {
		switch (type) {
			case "current":
				inputs.display_name.val("Current Location").addClass('ent');
				inputs.lat.val(geo.lat).addClass('ent');
				inputs.lon.val(geo.lon).addClass('ent');
				break;

			case "query":
				var resultBox = $('.cpdb-result-item');
				
				resultBox.mouseenter(function(){
						var i = this.dataset.markerref;
						marker.res[i].setIcon(assets.alertMARK);
					}).mouseleave(function(){
						var i = this.dataset.markerref;
						marker.res[i].setIcon(assets.primaryMARK);
					});
				resultBox.click(function(){
					var i = this.dataset.markerref;
					for(x=0; x<resultBox.length; x++) {
						if (("map"+x) === i) {
							marker.res[i].setIcon(assets.selectedMARK);
							marker.res[i].setZIndexOffset(1000);
							$(this).unbind('mouseenter').unbind('mouseleave');

							propogateResultData(x, resultBox);
						}
						else {primaryBinding(x, resultBox);}
					}
				});

				function primaryBinding(x, resultBox) {
					$($(resultBox)[x]).removeClass('selected');

					$($(resultBox)[x]).mouseenter(function(){
						var i = this.dataset.markerref;
						marker.res[i].setIcon(assets.alertMARK);
					}).mouseleave(function(){
						var i = this.dataset.markerref;
						marker.res[i].setIcon(assets.primaryMARK);
					});

					var i = $(resultBox)[x].dataset.markerref;
					marker.res[i].setIcon(assets.primaryMARK);
					marker.res[i].setZIndexOffset(0);
				}

				function propogateResultData(x, resultBox) {
					$($(resultBox)[x]).addClass('selected');

					pro.MRpropo = new Object;
					$(pro.MRpropo)
						.queue(function(){sanitizeInput();})
						.queue(function(){
							// INITIAL DATA ENTRY
							inputs.display_name.val(marker.res["id"+x][2].title).addClass('ent');
							inputs.lat.val(marker.res["id"+x][0]).addClass('ent');
							inputs.lon.val(marker.res["id"+x][1]).addClass('ent');
							$(pro.MRpropo).dequeue();
						})
						.queue(function(){
							var e = marker.res["id"+x][2].address;
							// SPECIFIC DETAILS
							if (e.house_number) {inputs.address.no.val(e.house_number).addClass('ent');} else {}
							if (e.pedestrian) {inputs.address.street.val(e.pedestrian).addClass('ent');} else {}
							if (e.street) {inputs.address.street.val(e.street).addClass('ent');} else {}
							if (e.road) {inputs.address.street.val(e.road).addClass('ent');} else {}
							if (e.postcode) {inputs.zip.val(e.postcode).addClass('ent');} else {}
							if (e.neighbourhood) {inputs.L4.val(e.neighbourhood).addClass('ent');} else {}
							if (e.suburb) {inputs.L4.val(e.suburb).addClass('ent');} else {}
							if (e.village) {inputs.L4.val(e.village).addClass('ent');} else {}
							if (e.hamlet) {inputs.L4.val(e.hamlet).addClass('ent');} else {}
							if (e.city) {inputs.city.val(e.city).addClass('ent');} else {}
							if (e.town) {inputs.city.val(e.town).addClass('ent');} else {}
							if (e.locality) {inputs.city.val(e.locality).addClass('ent');} else {}
							if (e.county) {inputs.L2.val(e.county).addClass('ent');} else {}
							if (e.state) {inputs.L1.val(e.state).addClass('ent');} else {}
							if (e.province) {inputs.L1.val(e.province).addClass('ent');} else {}
							if (e.country) {inputs.country.val(e.country).addClass('ent');} else {}

							$(pro.Mrpropo).dequeue();
						});
					function sanitizeInput() {
						$('#cpdb-geocode-fields input').val("");
						$(pro.MRpropo).dequeue();
					}	
				}
				$('div#results').addClass('queried');
				break;	
			case "init":
				break;
			default:
				// Display ERROR
				break;
		}
	};
