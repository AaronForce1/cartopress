function hideResults() {
	if ($(window).width() > 1200) {
		$('#cartopress_locator #results').animate({'width':'0px','opacity':'0','padding':'0px'}, {duration: 100});
		$('#cartopress_locator .cpdb-maincontent').animate({'width': '100%'}, {duration: 200});
	} else {
		$('#cartopress_locator #results').animate({'height':'0px','opacity':'0','padding':'0px'}, {duration: 100});
	}
	$('#toggle-in-map').css({'display':'block'});	
}

function showResults() {
	if ($(window).width() > 1200) {
		$('#cartopress_locator #results').animate({'width':'32.5%','opacity':'1','padding':'10px'}, {duration: 200});
		$('#cartopress_locator .cpdb-maincontent').animate({'width': ($('#cpdb-metabox-wrapper').width() * .68) - 20 + 'px'}, {duration: 100});
	} else {
		$('#cartopress_locator #results').animate({'height':'100%','opacity':'1','padding':'10px'}, {duration: 200});
	}
	$('#toggle-in-map').css({'display':'none'});
}

function toggleResults() {
	if ($('#cartopress_locator #results').width() == 0 || $('#cartopress_locator #results').height() == 0) {
		showResults();
	} else {
		hideResults();
	}
}

jQuery(document).ready(function($){
	$( window ).resize(function() {
		$('#cartopress_locator #results[style], #cartopress_locator .cpdb-maincontent[style]').removeAttr('style');
	});
	
	$('input#unlock_manual_edit').click(function() {
	    if ($(this).is(':checked')) {
			$('#cartopress_locator #cpdb-geocode-values span input').removeClass('disabled ent');
			$('#cartopress_locator #cpdb-geocode-values span input').attr('readonly', false);
			$('#cartopress_locator #cpdb-geocode-values label').css('color','#666666');
	    } else {
	    	$('#cartopress_locator #cpdb-geocode-values span input').addClass('disabled');
	    	$('#cartopress_locator #cpdb-geocode-values span input').attr('readonly', true);
	    	$('#cartopress_locator #cpdb-geocode-values label').css('color','#AAAAAA');
	    }
	});
	
	$('#toggle-in-map, #toggle-in-search').click(function() {
		toggleResults();
	});
	
	
});
