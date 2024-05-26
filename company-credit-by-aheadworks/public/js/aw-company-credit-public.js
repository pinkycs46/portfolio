function getUrlVars(url)
{
    var vars = [], hash;
    var hashes = url.slice(url.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}

jQuery( document ).ajaxComplete(function( event, xhr, settings ) {

  console.log(xhr);
  console.log(settings);

	var split_url= null;
	var dataurl	= null;
  	if(settings.url){
  		var str_url = settings.url;
  		split_url = str_url.split('?')[1];
  	}

  	if(settings.data){
  		var dataurl = settings.data;
  	}

    if(dataurl!=null ) {
      if(dataurl.search(/companycredit_payment/i)) {
        var credit_limit      = parseInt(getUrlVars(dataurl)["aw_cc_credit_limit"]);
        var available_credit  = parseInt(getUrlVars(dataurl)["aw_cc_available_credit"]);
        var cart_total        = parseInt(getUrlVars(dataurl)["aw_cc_cart_total"]);
        var error_msg         = '<li><div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout"><ul class="woocommerce-error" role="alert"><li>Insufficient credit funds</li></ul></div></li>';
        ischecked = jQuery('#payment_method_companycredit_payment').prop('checked');
        if(credit_limit < cart_total) {
          event.preventDefault();
          if(ischecked){
            //jQuery('ul.wc_payment_methods').append(error_msg);
          }
        } else if(cart_total > available_credit) {
            if(ischecked) {
              //jQuery('ul.wc_payment_methods').append(error_msg);
            }
        } 
      }
    }
});