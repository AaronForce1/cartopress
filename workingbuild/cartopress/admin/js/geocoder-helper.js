jQuery(document).ready(function($){
	$('input#unlock_manual_edit').click(function() {
	    if ($(this).is(':checked')) {
			$('#cartopress_locator #cpdb-geocode-values span input').attr('disabled', false);
			$('#cartopress_locator #cpdb-geocode-values label').css('color','#666666');
	    } else {
	    	$('#cartopress_locator #cpdb-geocode-values span input').attr('disabled', true);
	    	$('#cartopress_locator #cpdb-geocode-values label').css('color','#AAAAAA');
	    }
	});


});