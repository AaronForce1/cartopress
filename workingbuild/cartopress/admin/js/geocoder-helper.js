jQuery(document).ready(function($){
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


});
