var url    				= js_wgc_var.site_url;
var aw_wgc_front_nonce  = js_wgc_var.aw_wgc_front_nonce;
var ajax_url    		= js_wgc_var.ajax_url;
var PAGINATION_SIZE 	= 5;

var modal = document.getElementById("aw_gc_users_gift_card_Modal");
// Get the <span> element that closes the modal
var span = document.getElementsByClassName("bal_modal_close")[0];

function open_a_gift_card() {
	jQuery("#aw_gc_users_gift_card_Modal").show();
}

function add_new_gift_card() {
	var new_gc_code = jQuery.trim(jQuery("#new_gift_card_input").val());
	if(new_gc_code == "")
	{
		jQuery("#aw_gc_new_gift_card_txt").text("Enter gift card code");
		jQuery("#new_gift_card_input").val("");
		jQuery("#new_gift_card_input").focus();
	}
	else
	{
		jQuery("#aw_gc_new_gift_card_txt").text("");
		
		var site_url = url+'/wp-admin/admin-ajax.php?';
		jQuery.ajax({
			url: site_url,
			type: 'POST',
			data: {action: "aw_gc_add_new_gift_card_ajax",  new_gc_code:new_gc_code, aw_gc_front_nonce_ajax:aw_wgc_front_nonce},
			success:function(data) {
				if(data == '0') {
					jQuery("#aw_gc_new_gift_card_txt").text("Specified code doesn't exist");
				}
				else {
					jQuery("#aw_gc_new_gift_card_txt").text(data);
					if(data == 'Code added successfully') {
						fclose_modal_gift_card();
					}
				}
			},
			error: function(errorThrown) {
				console.log(errorThrown);
			}
		})	
	}
}

function close_modal_gift_card() {
	jQuery("#aw_gc_new_gift_card_txt").text("");
	jQuery("#new_gift_card_input").val("");
	jQuery("#aw_gc_users_gift_card_Modal").hide();
}

function fclose_modal_gift_card() {
	jQuery("#aw_gc_users_gift_card_Modal").hide();
	parent.location.reload();
}


jQuery(window).load(function() {
    /*Assign selected price option value in hidden input box*/
    var price = jQuery('#aw_wgc_amount_option').find(":selected").val();
    jQuery('#aw_wgc_amount').val(price);
    // On page load display Remove link if gift code already applied 
    aw_gc_check_giftcode_applied();
});
 
function aw_gift_cart_price(sel) {
	jQuery('#aw_wgc_amount').val(sel.value);
	var selected = jQuery('#aw_wgc_amount_option').find('option:selected');
	var extra = selected.data('value');
	jQuery('#aw_wgc_product_id').val(extra);
	jQuery('#add-to-cart').val(extra);
}
jQuery(document).ready(function() {

	jQuery('.cart_validation').click(function() {
		var email_to = jQuery.trim(jQuery('#aw_wgc_email_to').val());
		var email_from = jQuery.trim(jQuery('#aw_wgc_sender_email').val());
		jQuery('#aw_wgc_email_to').val(email_to);
		jQuery('#aw_wgc_sender_email').val(email_from);
	});
	
	jQuery('#aw_wgc_textarea_counter').text(462 + ' character(-s) is remaining');
	jQuery('#aw_wgc_additional_text').on('keyup',function() {
		var left;
	    left = 500 - jQuery(this).val().length;
	    if (left < 0) {
	        left = 0;
	    }
	    jQuery('#aw_wgc_textarea_counter').text('');
	    jQuery('#aw_wgc_textarea_counter').text(left + ' character(-s) is remaining');
	});
	jQuery('#aw_wgc_text_counter').text(61 + ' character(-s) is remaining');
	jQuery('#aw_wgc_email_heading').on('keyup',function() {
		var left;
		left = 100 - jQuery(this).val().length;	
 	    
	    if (left < 0) {
	        left = 0;
	    }
	    jQuery('#aw_wgc_text_counter').text('');
	    jQuery('#aw_wgc_text_counter').text(left + ' character(-s) is remaining');
	});
});

jQuery( function( $ ) {
	var wc_checkout_gifts = {
		init: function() {
			$( document.body ).on( 'click', 'a.showgift', this.show_gift_form );
			$( document.body ).on( 'click', '.woocommerce-remove-gift', this.remove_gift );
			$( 'form.checkout_gift' ).hide().submit( this.submit );
			if ($("#aw_use_balance_chk").is(':checked')) {
				$('#aw_gift_code').attr('disabled','disabled');
				$('#aw_gift_code').css({'cursor': 'not-allowed'});
			} else {
				$('#aw_gift_code').removeAttr('disabled');
				$('#aw_gift_code').css({'cursor': ''});
			}
			//$( '#aw_gc_cardbalance' ).text($( '#awgc_avail_bal').val());
		},
		show_gift_form: function() {
			$( '.checkout_gift' ).slideToggle( 400, function() {
				$( '.checkout_gift' ).find( ':input:eq(0)' ).focus();
			});
			return false;
		},
		submit: function() {
			var $form = $( this );
			if ( $form.is( '.processing' ) ) {
				return false;
			}

			$form.addClass( 'processing' ).block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			
			var data = {
				action : 	'aw_gift_code_apply_ajax',
				aw_gc_front_nonce_ajax : aw_wgc_front_nonce,
				gift_code:	$form.find( 'input[name="gift_code"]' ).val(),
				screen: $("#awgift_apply_btn").attr('data-value')
			};

			$.ajax({
				type: 		'POST',
				url:		url+'/wp-admin/admin-ajax.php?',//wc_checkout_params.wc_ajx_url.toStrinag().replace( '%%endpoint%%', 'apply_gift' ),
				data:		data,
				success:	function( code ) {
					$( '.woocommerce-error, .woocommerce-message' ).remove();
					$form.removeClass( 'processing' ).unblock();

					if ( code ) {
						$form.before( code );
						$form.slideUp();

						$( document.body ).trigger( 'applied_gift_in_checkout', [ data.gift_code ] );
						$( document.body ).trigger( 'update_checkout', { update_shipping_method: false } );
					}
				},
				dataType: 'html'
			});
			return false;
		},
		remove_gift: function( e ) {
			e.preventDefault();

			var container = $( this ).parents( '.woocommerce-checkout-review-order' ),
				coupon    = $( this ).data( 'coupon' );

			container.addClass( 'processing' ).block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});

			var data = {
				action 		: 'aw_gift_code_remove_ajax',
				aw_gc_front_nonce_ajax : aw_wgc_front_nonce,
				gift_code 	: $form.find( 'input[name="gift_code"]' ).val(),
				screen 		: 'checkout'
				 
			};

			$.ajax({
				type:    'POST',
				url:     url+'/wp-admin/admin-ajax.php?',//wc_checkout_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'remove_gift' ),
				data:    data,
				success: function( code ) {
					$( '.woocommerce-error, .woocommerce-message' ).remove();
					container.removeClass( 'processing' ).unblock();

					if ( gift_code ) {
						$( 'form.woocommerce-checkout' ).before( gift_code );

						$( document.body ).trigger( 'update_checkout', { update_shipping_method: false } );

						// Remove coupon code from coupon field
						$( 'form.checkout_gift' ).find( 'input[name="gift_code"]' ).val( '' );
					}
				},
				error: function ( jqXHR ) {
					if ( wc_checkout_params.debug_mode ) {
						/* jshint devel: true */
						console.log( jqXHR.responseText );
					}
				},
				dataType: 'html'
			});
		}
	};

	wc_checkout_gifts.init();
});	
 
function apply_aw_gift(me, screen, cart_total= 0, giftcode= 0 )
{
	var site_url 	= url+'/wp-admin/admin-ajax.php';
	var btn_type 	= me.value;
	var name 		= "";
	var opt_type 	= "";
	var balance 	= 0;
	var usebal_chk 	= false;
	var data 		= null;
	var applied_code='';
	applied_code= jQuery("#awgc_applied_gift").val();
	var input_code	= jQuery("#aw_gift_code").val();
	if(input_code.length>0) {
		aw_gift_code 	= jQuery("#aw_gift_code").val();
	}else if(applied_code){
		aw_gift_code 	= jQuery("#awgc_applied_gift").val();
		jQuery("#aw_use_balance_chk").val(0);
		usebal_chk 		= 0;	
	} else if ( jQuery("#aw_use_balance_chk").is(':checked') && 0!=giftcode) {
		aw_gift_code 	= giftcode;
		usebal_chk 		= 1;	
	} 
	
	if("" == aw_gift_code) {
		jQuery("#aw_gift_code").focus();
		return false;
	}
	jQuery.ajax({
		url: site_url,
		type: 'POST',
		data: {action: "aw_gift_code_apply_ajax", gift_code: aw_gift_code, usebalance:usebal_chk, screen:screen, aw_gc_front_nonce_ajax:aw_wgc_front_nonce},
		success:function(data) {
				jQuery('[name="update_cart"]').prop("disabled", false);
				jQuery('[name="update_cart"]').trigger('click');
				jQuery('#aw_gc_cardbalance').html(data);
		},complete: function(data) {
			if(data) {
				jQuery('#aw_gc_cardbalance').html(data.responseText);
			}
		},
		error: function(errorThrown) {
			console.log(errorThrown);
		}
	});
}
function remove_awgift_clicked(){
	var site_url = url+'/wp-admin/admin-ajax.php';
	var name = "";
	var opt_type = "";
	var msg 	="Gift card code has been removed.";
	jQuery.ajax({
		url: site_url,
		type: 'POST',
		data: {action: "aw_gift_code_remove_ajax",screen:'cart', message:msg, message_type:'success' ,aw_gc_front_nonce_ajax : aw_wgc_front_nonce},
		success:function(data) {
				jQuery('[name="update_cart"]').prop("disabled", false);
				jQuery('[name="update_cart"]').trigger('click');
				jQuery( document.body ).trigger( 'update_checkout', { update_shipping_method: false } );

				jQuery('#aw_use_balance_chk').val('false');
				jQuery('#aw_use_balance_chk').prop('checked', false);
		},
		complete: function() {
			jQuery("#gift_code").val('');
		},
		error: function(errorThrown) {
			console.log(errorThrown);
		}
	});
}
function remove_aw_gift(screen = '', message ='')
{
	var site_url = url+'/wp-admin/admin-ajax.php';
	var name = "";
	var opt_type = "";
	jQuery.ajax({
		url: site_url,
		type: 'POST',
		data: {action: "aw_gift_code_remove_ajax",screen:screen, message:message ,aw_gc_front_nonce_ajax : aw_wgc_front_nonce},
		success:function(data) {
				jQuery('[name="update_cart"]').prop("disabled", false);
				jQuery('[name="update_cart"]').trigger('click');
				jQuery( document.body ).trigger( 'update_checkout', { update_shipping_method: false } );
				
				jQuery('#aw_use_balance_chk').val('false');
				jQuery('#aw_use_balance_chk').prop('checked', false);
		},
		complete: function() {
			jQuery("#gift_code").val('');
		},
		error: function(errorThrown) {
			console.log(errorThrown);
		}
	});
}

function aw_gc_check_giftcode_applied()
{
	if(jQuery('.shop_table > tbody > tr').hasClass('fee')) {
		jQuery('.fee').find('td').append('&nbsp;&nbsp;<a href="javascript:void(0)" onclick="remove_awgift_clicked()" class="woocommerce-remove-giftcode">[Remove]</a>');
	}
}

jQuery( document ).ajaxComplete(function( event, xhr, settings ) {
	var split_url= null;
	var dataurl	= null;
	if(settings.url){
		var str_url = settings.url;
		split_url = str_url.split('?')[1];
	}

	if(settings.data){
		var dataurl = settings.data;
	}
 	
  	if(split_url!=null && split_url!="" ) {
  		if(split_url.search(/update_order_review/i)==-1 ) {
  			var screen = "'checkout'";
  		  	jQuery('a.woocommerce-remove-giftcode').remove();
		   	jQuery('.fee').find('td').append(' <a href="javascript:void(0)" onclick="remove_aw_gift('+screen+')" class="woocommerce-remove-giftcode">[Remove]</a>');
		}
		if(split_url.search(/get_refreshed_fragments/i)==-1 ) {
			if(getUrlVars(dataurl)["aw_use_balance_check"] == 'true'){
				jQuery("#aw_use_balance_chk").prop('checked', true);
				jQuery('#aw_gift_code').attr('disabled','disabled');
				jQuery('#aw_gift_code').css({'cursor': 'not-allowed'});
			} 
			if(getUrlVars(dataurl)["aw_use_balance_check"] == 'false'){
				jQuery('#aw_gift_code').removeAttr('disabled');
				jQuery('#aw_gift_code').css({'cursor': ''});
			}	
		}				
  	
	  	if(split_url.search(/remove_item/i)==-1) {
	  		jQuery('a.woocommerce-remove-giftcode').remove();
		   	jQuery('.fee').find('td').append(' <a href="javascript:void(0)" onclick="remove_aw_gift()" class="woocommerce-remove-giftcode">[Remove]</a>');
 	  	}
 	  	if(split_url.indexOf("apply_coupon")>1 || split_url.indexOf("update_shipping_method")>1) {
 	  		
 	  		if(settings.data){
				var partdata = settings.data;
				 if(getUrlVars(partdata)['coupon_code']) {
				 	coupon = getUrlVars(partdata)['coupon_code']
				 	aw_get_coupon_and_gift_applied_status(coupon);
				 }
				 /* Below Condition work when change shipping from non free to free shipping method */
			 	if(partdata.indexOf("free_shipping")>1 && 0 == jQuery('#awgc_total_before_gc').val()) {
				 	remove_aw_gift('cart','Gift Card can not be applied on 0 total');
				 }
			}
 	  	}
  	}

  	if(dataurl!=null ) {
  		if(dataurl.search(/aw_gift_code_apply_ajax/i)==-1) {
  			jQuery('a.woocommerce-remove-giftcode').remove();
		   	jQuery('.fee').find('td').append(' <a href="javascript:void(0)" onclick="remove_awgift_clicked()" class="woocommerce-remove-giftcode">[Remove]</a>');

		   	if(getUrlVars(dataurl)["aw_use_balance_check"] == 'true') {
				jQuery("#aw_use_balance_chk").prop('checked', true);
				jQuery('#aw_gift_code').attr('disabled','disabled');
				jQuery('#aw_gift_code').css({'cursor': 'not-allowed'});
			} 
			if(getUrlVars(dataurl)["aw_use_balance_check"] == 'false') {
				jQuery('#aw_gift_code').removeAttr('disabled');
				jQuery('#aw_gift_code').css({'cursor': ''});
			}	
			if(getUrlVars(dataurl)["update_cart"] == 'Update+Cart') {
				if(0 == jQuery('#awgc_total_before_gc').val()){
					remove_aw_gift('cart','Gift Card can not be applied on 0 total');
				}
			} 
		}
		if(getUrlVars(dataurl)['post_data']) {
			postdata = getUrlVars(dataurl)['post_data'];
			if(postdata.indexOf("update_order_review")>1) {
				jQuery( ".appendeddiv" ).remove();
				jQuery('.checkout_gift').before('<div class="woocommerce-message appendeddiv"  role="alert">Have a gift card? <a href="#" class="showgift">Click here to enter your code</a></div>');
			}	
		}
  	}
});

jQuery(document).on('change', '#aw_use_balance_chk', function() {
	if(jQuery(this).is(':checked')) {
		jQuery('#aw_gift_code').val('');
		jQuery('#aw_use_balance_chk').val('true');
		jQuery('#aw_gift_code').attr('disabled','disabled');
		jQuery('#aw_gift_code').css({'cursor': 'not-allowed'});
	} else{
		jQuery('#aw_use_balance_chk').val('');
		jQuery('#aw_use_balance_chk').val('false');
		jQuery('#aw_gift_code').removeAttr('disabled');
		jQuery('#aw_gift_code').css({'cursor': ''});
	}
});

function getUrlParams(urlOrQueryString) {
  if ((i = urlOrQueryString.indexOf('&')) >= 0) {
    const queryString = urlOrQueryString.substring(i+1);
    if (queryString) {
      return _mapUrlParams(queryString);
    } 
  }

  return {};
}

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

function aw_get_coupon_and_gift_applied_status(coupon){
	var site_url = url+'/wp-admin/admin-ajax.php?';
	jQuery.ajax({
		url: site_url,
		type: 'POST',
		data: {action: "aw_gift_code_check_coupon_and_gift", coupon_code: coupon,aw_gc_front_nonce_ajax:aw_wgc_front_nonce},
		success:function(data) {
				if('remove' == data){
					remove_aw_gift('cart','Gift Card can not be applied on 0 total');
				}
		},
		error: function(errorThrown) {
			console.log(errorThrown);
		}
	});
}