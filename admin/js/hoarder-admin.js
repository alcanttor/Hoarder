(function( $ ) {
	'use strict';

})( jQuery );

function hoarder_show_hide(obj,primary,secondary,trinary)
{	
	a = jQuery(obj).is(':checked');
	if (a == true)
	 {
		jQuery('#'+primary).show(500);
		if(secondary!='')
		{
			jQuery('#'+secondary).hide(500);
		}
		if(trinary!='')
		{
			jQuery('#'+trinary).hide(500);
		}
				
	}
	else 
	{
		jQuery('#'+primary).hide(500);
		if(secondary!='')
		{
			jQuery('#'+secondary).show(500);
		}
		if(trinary!='')
		{
			jQuery('#'+trinary).show(500);
		}
           
	}
	
}

function verify_hoarder()
{
    //return false;
    var apikey = jQuery('#hoarder_api_key').val();
    var data = {'action': 'hoarder_api_verification','apikey': apikey};
    jQuery.post(hoarder_ajax_object.ajax_url, data, function(response) {
        jQuery('#hoarder_response').html(response);
    });
}