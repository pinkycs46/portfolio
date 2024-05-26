var url    			= js_batc_var.site_url;
var aw_nonce_batc  	= js_batc_var.js_batc_nonce;
var ajax_url    	= js_batc_var.ajax_url;
var PAGINATION_SIZE = 5;

function aw_batc_public_checkIt(evt,allowed = '')
{
	evt = (evt) ? evt : window.event;
	 
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
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

jQuery(window).load(function(){
    /* Muliselect checkbox in data table*/
    jQuery(".hidecartbutton").hide();
    jQuery('.prod_checkbox').change(function() {
        if (!jQuery(this).is(":checked")) {
            jQuery(".aw-allselect_chk").prop('checked', false);
        }
        overallsummation();
    });


    jQuery(".aw-allselect_chk").click(function(){
        jQuery('#batc-list tr td input:checkbox').not(this).prop('checked', this.checked);
        jQuery(".aw-allselect_chk").not(this).prop('checked', this.checked);
        overallsummation();
    });

    jQuery(".batc_add_to_cart_button").click(function(e){
        e.preventDefault();
        var product       = {};
        var product_id    = '';
        var tab_id        = '';
        var id            = ''; 
        var rowid         = '';  
        var variation_id  = ''; 
        var single_prod   = [];  
        var flag          = true;
        var msg           = '';

        var redirect_to_cart= jQuery(".redirect_to_cart").val();
        var cart_page_url   = jQuery(".cart_page_url").val();

        var selected_pro= jQuery('input:checkbox:checked').map(function(index) {
                                if(jQuery.isNumeric(this.value)) {
                                    var product_id  = this.value;
                                    var currentRow  = jQuery(this).closest("tr");
                                    var row_id      = jQuery(this).closest("tr").attr('class');
                                    var rowid       = row_id.split('-');
                                    var tab_id      = rowid[rowid.length-1];    
                                    var quantity    = currentRow.find("input.batcquantity").val();
                                    var max_qty     = jQuery("#quantity-"+row_id).attr('max'); 

                                    if (parseInt(quantity) > parseInt(max_qty)) {
                                        msg ='Value must be less than or equal to ' + max_qty;
                                        jQuery("#quantity-"+row_id).css({'border':'1px solid red'});
                                        flag = false;
                                    }
                                    if (parseInt(quantity) <= 0 || quantity == "") {
                                        msg ='Value must be greater than 0';
                                        jQuery("#quantity-"+row_id).css({'border':'1px solid red'});
                                        flag = false;
                                    }
									if (flag == true) {
										jQuery("#quantity-"+row_id).css({'border':''});
									}

                                    var variation_id= currentRow.find("input.variation_id").val();
                                    product = {product_id:product_id, quantity: quantity, variation_id:variation_id};
                                    jQuery(currentRow.find('input[type="hidden"].any_variation_val')).each(function (index) {
                                        attribute_name = jQuery(this).attr('data-value');
                                        product[attribute_name] = jQuery(this).val();
                                    });
                                   return product;
                                }
                            }).get();

        if ( selected_pro.length == 0 ) {
            alert("Please select at least one product and try again");
            return false;
        }
        if(false == flag ) {
            alert(msg);
            return false;
        }

       jQuery(this).addClass('loading');
       var site_url = url+'/wp-admin/admin-ajax.php?';
       jQuery.ajax({
            url: site_url,
            type: 'POST',
            data: {action: "aw_add_multiple_products_to_cart",  products:selected_pro , redirect_to_cart:redirect_to_cart, nonce_batc_ajax:aw_nonce_batc},
            success:function(data) {
                
                if(data == 'no'){
                    jQuery('.batc_add_to_cart_button').removeClass('loading');
                    jQuery('.hidecartbutton').show();
                    jQuery.post(
                        woocommerce_params.ajax_url,
                        {'action': 'aw_batc_mode_theme_update_mini_cart'},
                        function(response) {
                            if(response){
                                var data = response.split('~');
                                jQuery('.widget_shopping_cart_content').html(data[0]);
                                var html = '<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol"></span>&nbsp;'+data[1]+'</span> <span class="count">'+data[2]+' items</span>';
                                jQuery('a.cart-contents').html( html);
                            }
                        }
                    );
                } else if(data == 'yes'){
                    window.location.href = cart_page_url;
                }

            },
            error: function(errorThrown) {
                console.log(errorThrown);
            }
        });
    });

    /* onkey press get row total */
    jQuery(document).on('change', '.batcquantity', function(e) {
        var currentRow  = jQuery(this).closest("tr");
        var price       = parseFloat(currentRow.find('td.price').find("input.product_price").val());
        var current_sym = currentRow.find("span.symbol").text();
        var quantity    = parseInt(jQuery(this).val());
        var row_total   = price * quantity;

        var site_url = url+'/wp-admin/admin-ajax.php';
            jQuery.ajax({
                url: site_url,
                type: 'POST',
                data: {action: "aw_get_price_quantity_calculate",row_total:row_total, nonce_batc_ajax:aw_nonce_batc, screen: "site"},
                success:function(data) {

                     if(data.length>0) {
                        currentRow.find("td span.totalamount").html(data);
                        currentRow.find("td span.totalamount").attr('data-rowtotal',row_total);
                        currentRow.find("td input.totalamount").val(row_total);
                        overallsummation();
                     }
                }
            });       
        });

    overallsummation();
});

function seelct_any_variation(sel,$rowid)
{
    jQuery("#any_variation_"+$rowid).val(sel.value);
}

function change_variation_product(sel, key, product_id, $rowid)
{
    var value    = sel.value;
    var site_url = url+'/wp-admin/admin-ajax.php';
    var quantity = jQuery('#quantity-'+$rowid).val(); 
    var exist_vid= jQuery('#variation_id_'+$rowid).val(); 
    jQuery(".batc_add_to_cart_button").attr('disabled','disabled');
    jQuery.ajax({
        url: site_url,
        type: 'POST',
        data: {action: "aw_ajax_filterVariations_front", product_id:product_id, exist_vid:exist_vid,/*variation_arr:variation_arr,*/ key:key, value:value, quantity:quantity, nonce_batc_ajax:aw_nonce_batc},
        success:function(data) {

            if(data != 0){
             var data  = JSON.parse(data);
             if(data.appendto.length>0 && data.options.length>0) {
                jQuery("#selected-"+data.appendto+'-'+$rowid).find('option').removeAttr("selected") ;  
                jQuery("#selected-"+data.appendto+'-'+$rowid).empty();
                jQuery("#selected-"+data.appendto+'-'+$rowid).append(data.options);
                
                if (data.image_tag.length >0) {
                    jQuery(".product-image-"+$rowid).html("");
                    jQuery(".product-image-"+$rowid).append(data.image_tag);    
                }
                jQuery("#variation_id_"+$rowid).val(data.variation_id);
                if (data.price.length >0) {
                    
                    jQuery("#product_price-"+$rowid).val(data.price); 
                }
                if (data.rowtotal.length >0) {
                    total = quantity*data.price;
                    jQuery("#row_total-"+$rowid).val(total);   
                    jQuery("#rowtotalamt-"+$rowid).html(data.rowtotal);  
                }
                overallsummation();
                jQuery(".batc_add_to_cart_button").removeAttr('disabled');
             }
            } 
        }
    });
}

/* Total Summation of row total assign it to below table */
function overallsummation()
{
    var sum = 0;
    var current_sym = '';
    jQuery('table > tbody  > tr').each(function(index, tr) { 
         //var totasl = jQuery(tr + " span.totalamount").html();
         if(jQuery('input.prod_checkbox' , tr).is(":checked") ){
            var value = jQuery("input.totalamount", tr).val();   
            
            if(!isNaN(value)){
                sum += parseFloat(value);//Number(jQuery(this).text());   
            }
             jQuery("input.batcquantity",tr).prop('disabled', false);
         } else {
             jQuery("input.batcquantity",tr).prop('disabled', true);            
         }
    });
    var site_url = url+'/wp-admin/admin-ajax.php';
    jQuery.ajax({
        url: site_url,
        type: 'POST',
        data: {action: "aw_get_price_quantity_calculate",row_total:sum, nonce_batc_ajax:aw_nonce_batc, screen: "site"},
        success:function(data) {
             if(data.length>0) {
                jQuery('.overallsummation').text(data);
             }
        }
    });
    
}

function aw_batc_public_checkIt(evt,allowed = '')
{
	evt = (evt) ? evt : window.event;
	 
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if(charCode === 46 && allowed == true)
	{
		return true;
	}
	if(charCode > 31 && (charCode < 47 || charCode > 57))
	{
		status = "This field accepts numbers only.";
		return false;
	}
	status = "";
	return true;
}

function validation()
{
    var $flag = 0;
    var error_msg = [];
    error_msg[0] = '';
    error_msg[1] = 'Quantity is a required field';
    error_msg[2] = 'Numeric value is required';
    error_msg[3] = 'Quantity must be greater than 0';
    error_msg[4] = 'Quantity must be without decimal';
    error_msg[5] = 'Quantity must be numeric';
    jQuery('.batcquantity').each(function(index) {
        var pattern = /^\d+$/;
        var inputval= jQuery(this).val();
        var value   = parseInt(inputval);
        if (inputval.length == 0) {
            event.preventDefault();
            jQuery(this).css({'border':'1px solid red'});
            jQuery('span.errormsg-'+index).text(error_msg[1]).css({'color':'red'});
            $flag = 1;
        } 
        else if (value <=0) {
            event.preventDefault();
            jQuery(this).css({'border':'1px solid red'});
            jQuery('span.errormsg-'+index).text(error_msg[3]).css({'color':'red'});
            $flag = 3;
        } else if(!pattern.test(inputval)) 
        {
            event.preventDefault();
            jQuery(this).css({'border':'1px solid red'});
            jQuery('span.errormsg-'+index).text(error_msg[2]).css({'color':'red'});
            $flag = 4;
        } else if(!jQuery.isNumeric(inputval)) {
            event.preventDefault();
            jQuery(this).css({'border':'1px solid red'});
            jQuery('span.errormsg-'+index).text(error_msg[2]).css({'color':'red'});
            $flag = 1;
        } else {
            jQuery(this).css({'border':''});
            jQuery('span.errormsg-'+index).text(error_msg[0]).css('');
        }
    });
    if ($flag ) {
            return false;
    }
     return true;
}