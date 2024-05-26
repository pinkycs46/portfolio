var url  = js_wgc_var.site_url;
var nonce_wgc  = js_wgc_var.aw_wgc_nonce;

jQuery.urlParam = function(name){
		var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
		if(results!=null)
			return results[1] || 0;
		else
			return 0;
	}
jQuery(document).ready(function() {

   var price_array = {};
   var index = 0;
   jQuery("#aw_wgc_price_btn").click(function() {
   		var price		= parseFloat(jQuery("#aw_wgc_price_input").val());
   		var product_id  = jQuery("#post_ID").val();
		 if(isNaN(price)) {
			jQuery('#aw_wgc_price_input').css( 'border','1px solid red'); 
			jQuery('span.error_msg').text('This is a required field').css({'color':'red'});
			return false;
		 }
		 else {
			jQuery('#aw_wgc_price_input').css( 'border',''); 
			jQuery('span.error_msg').text('');
			aw_update_gift_price_post_meta('',price,product_id); 
		 }
   });

   jQuery("#aw_wgc_config_form").submit(function(e) {
     
      var expiry_val = jQuery.trim(jQuery(".aw_wgc_expiration").val());
      if (expiry_val.startsWith("0")) {
         jQuery('.aw_wgc_expiration').css( 'border','1px solid red'); 
         jQuery('.aw_wgc_expiration_error').css('color','red');
         return false;
      }
      var codelength_val = jQuery.trim(jQuery("#aw_wgc_length").val());
      if (codelength_val == "") {
         jQuery('#aw_wgc_length').css( 'border','1px solid red'); 
         jQuery('.aw_wgc_length_error').text('This is a required field').css('color','red');
         return false;
      } else if (parseInt(codelength_val) <=2 || parseInt(codelength_val) >12) {
         jQuery('#aw_wgc_length').css( 'border','1px solid red'); 
         jQuery('.aw_wgc_length_error').text('Minimum value is 3 and maximum is 12').css('color','red');
         return false;
      } else {
         jQuery('#aw_wgc_length').css( 'border',''); 
         jQuery('.aw_wgc_length_error').text('');
      }

	  var position_val = jQuery.trim(jQuery("#aw_wgc_dash_position").val());
      if (parseInt(position_val) == 0 || parseInt(codelength_val) < 0) {
         jQuery('#aw_wgc_dash_position').css( 'border','1px solid red'); 
         jQuery('.aw_wgc_dash_position_error').text('Enter a value greater than 0 or leave empty').css('color','red');
         return false;
      } else {
         jQuery('#aw_wgc_dash_position').css( 'border',''); 
         jQuery('.aw_wgc_dash_position_error').text(' ');
      }
   });

/*   jQuery(".refund_order_item_qty").on("change paste keyup", function() {
		var qty =   jQuery(this).val();
		//alert(value);
		var giftamt				= jQuery(".aw_gc_hidden_gift_price").val();
		var total_qty 			= jQuery(".aw_gc_hidden_total_ordered_qty").val(); 
		var refund_line_total 	= jQuery(".refund_line_total").val();
		
		var deduct_gift 		= parseFloat(total_qty) / parseFloat(giftamt);
		var refundamount 		= jQuery("#refund_amount").val();	
		//if(1 == qty){
		//	giftamt = refund_line_total - parseFloat(giftamt);
		//} else {
		//	giftamt = giftamt+
		//}
		//giftamt = parseFloat(refund_line_total) - parseFloat(giftamt);
		//(giftamt);
		//var value 	= parseFloat(value) - 2 ;
		alert(refundamount +'-'+ deduct_gift);
		jQuery("#refund_amount").removeAttr('readonly');
		jQuery("#refund_amount").val(refundamount - deduct_gift);
		jQuery("#refund_amount").attr('readonly',true);
	});*/
});
function aw_remove_price(id){
   var price_array   = {};
   var product_id    = jQuery("#post_ID").val();
   jQuery("#aw_price_close_hover-"+id).parent().remove();
   aw_update_gift_price_post_meta(id, '', product_id);
}

function aw_update_gift_price_post_meta(removeid, price, product_id)
{
    var site_url = url+'/wp-admin/admin-ajax.php';
      jQuery.ajax({
         url : site_url,
         type: 'POST',
         data: {action: "aw_gift_price_display_on_product", removeid:removeid, price:price ,product_id:product_id ,aw_gc_nonce_ajax:nonce_wgc},
         success:function(data) {
            if(data) {
               var data  = JSON.parse(data);
			   if(data == "Already added this price") {
				   jQuery("#aw_wgc_price_input").focus();
				   alert(data);
			   }
			   else {
					var span = '';
					jQuery("._price_field").find('span').remove();
					jQuery.each(data, function(index, price){
					span += '<span class="aw_wgc_prices"><span class="aw_added_price">'+price+'</span><span class="aw_price_close_hover" id="aw_price_close_hover-'+index+'" onclick="aw_remove_price('+index+')">&times;</span><input type="hidden" id="hidden_input_wgc_price" value="'+ price + '" name="aw_wgc_price[]"></span>';  
					});
					/*var span = '<span class="aw_wgc_prices"><span class="aw_added_price">'+price+'</span><span class="aw_price_close_hover">&times;</span></span>';
					*/
					jQuery("._price_field").append(span);
					jQuery("#aw_wgc_price_input").val('');
			   }
            }
         }
      });
}

function aw_gc_checkItExp(evt,allowed = '')
{
	evt = (evt) ? evt : window.event;

	var charCode = (evt.which) ? evt.which : evt.keyCode;
	var textLenExp = jQuery('#expiration').val().length;

	if(charCode === 46 && allowed == '0')
	{
		return true;	
	}
	if(charCode > 31 && (charCode < 48 || charCode > 57))
	{
		status = "This field accepts numbers only.";
		return false;
	}
	status = "";
	return true;
}

function aw_gc_checkItLen(evt,allowed = '')
{
	evt = (evt) ? evt : window.event;

	var charCode = (evt.which) ? evt.which : evt.keyCode;
	var textLenExp = jQuery('#expiration').val().length;

	if(charCode === 46 && allowed == true)
	{
		return true;	
	}
	if(charCode > 31 && (charCode < 48 || charCode > 57))
	{
		status = "This field accepts numbers only.";
		return false;
	}
	status = "";
	return true;
}

function aw_gc_checkItPos(evt,allowed = '')
{
   evt = (evt) ? evt : window.event;

	var charCode = (evt.which) ? evt.which : evt.keyCode;
	var textLenExp = jQuery('#expiration').val().length;

	if(charCode === 46 && allowed == true)
	{
		return true;	
	}
	if(charCode > 31 && (charCode < 48 || charCode > 57))
	{
		status = "This field accepts numbers only.";
		return false;
	}
	status = "";
	return true;
}

function aw_gc_checkIts(evt, allowed)
{
   evt = (evt) ? evt : window.event;
   var charCode = (evt.which) ? evt.which : evt.keyCode;

   if(charCode === 46 && allowed == '.')
   {
      return true;   
   }
   if(charCode > 31 && (charCode < 48 || charCode > 57))
   {
      status = "This field accepts numbers only.";
      return false;
   }
   status = "";
   return true;
}


