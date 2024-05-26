var url_admin			= aw_cc_admin_js_var.site_url;
var ajax_url			= aw_cc_admin_js_var.ajax_url;
var path				= aw_cc_admin_js_var.path;
var host				= aw_cc_admin_js_var.host;
var aw_cc_admin_nonce	= aw_cc_admin_js_var.aw_cc_admin_nonce;
var msgcounterflag 		= 1;
var norepeatmessage 	= 0; 
function checkIt(evt,allowed = '')
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

function aw_cc_checkIt(evt,allowed = '')
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

function aw_cc_checkIt_minus(evt , allowed = '',allowed_minus = '')
{
	evt = (evt) ? evt : window.event;
	var charCode = (evt.which) ? evt.which : evt.keyCode;

	if(charCode === 46 && allowed == '.')
	{
	  return true;   
	}
	if(charCode === 45 && allowed_minus == '-')
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

jQuery.urlParam = function(name){
	var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
	if(results!=null)
		return results[1] || 0;
	else
		return 0;
}
jQuery(document).ready(function(){
	jQuery('.current-page').keypress(function(event){
		//var screen  = jQuery("button.tablinks.active").attr('data-screen');
		 
	    var keycode = (event.keyCode ? event.keyCode : event.which);
	    if(keycode == '13'){
			jQuery("#cc-userlist-table").append("<input type='hidden' name='page' value='customer-credit-balance'>");	
			jQuery("input[name=_wp_http_referer]").remove(); 
			jQuery("input[name=_wpnonce]").remove('');
			//alert(screen);
	    }
	});
});


jQuery(window).load(function() {
	var tab = jQuery.urlParam('tab');
	if(tab =='aw-cc-emails') {
		var i, tabcontent, tablinks;
		tabcontent = document.getElementsByClassName("tabcontent");
		for(i = 0; i < tabcontent.length; i++)
		{
			tabcontent[i].style.display = "none";
		}
		jQuery( ".tablinks" ).removeClass( "active" );
		jQuery( ".tab button" ).last().addClass( "active" );
		document.getElementById('aw_cc_email-setting-tab').style.display = "block";
	}

	if(jQuery('#customer_user').length)
	{
		jQuery("#customer_user").change(function() {
			msgcounterflag = 1 ;
			aw_cc_admin_get_order_details('recalculate');
		});
	}
	if(jQuery('#_payment_method').length)
	{
		jQuery("#_payment_method").change(function() {
			msgcounterflag = 1 ;
			aw_cc_admin_get_order_details('recalculate');
		});
	}
	
	aw_ca_set_grid_column_url();
	
});

function openTab(evt, tabName, me)
{
	evt.preventDefault();
	var i, tabcontent, tablinks;
	tabcontent = document.getElementsByClassName("tabcontent");
	for (i = 0; i < tabcontent.length; i++)
	{
		tabcontent[i].style.display = "none";
	}
	jQuery( ".tablinks" ).removeClass( "active" );
	jQuery(me).addClass("active");
	document.getElementById(tabName).style.display = "block";
}

function aw_cc_email_templvalidateForm() {
	var admin_template 	= ['Admin Email','Abuse Report Email','Critical Report Email'];
	var flag 			= false;
	//var recipient 		= jQuery.trim(jQuery('.aw_cc_recipient').val());
	//var emails 			= emailList.replace(/\s/g,'').split(",");
	var emailsubject	= jQuery.trim(jQuery('.aw_cc_mailsubject').val());
	var email_heading 	= jQuery.trim(jQuery('.aw_cc_email_heading').val());
	var additional_content = jQuery.trim(jQuery('.aw_cc_additional_content').val());
	var emailformname 	= jQuery.trim(jQuery('.emailformname').val());
	var regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	
	if(emailsubject == ""){
		jQuery('span.aw_cc_mailsubject').css( 'border','1px solid red');
		jQuery('span.aw_cc_mailsubject_msg').text('Subject is required').css({'color':'red'});
	    flag = true;
	} else {
		jQuery('span.aw_cc_mailsubject').css( 'border','');
		jQuery('span.aw_cc_mailsubject_msg').text('');	
	}

	if(email_heading == ""){
		jQuery('span.aw_cc_email_heading').css( 'border','1px solid red');
		jQuery('span.aw_cc_email_heading_msg').text('Email heading is required').css({'color':'red'});
	    flag = true;
	} else {
		jQuery('span.aw_cc_email_heading').css( 'border','');
		jQuery('span.aw_cc_email_heading_msg').text('');
	}
	
	if(additional_content == ""){
		jQuery('span.aw_cc_additional_content').css( 'border','1px solid red');
		jQuery('span.aw_cc_additional_content_msg').text('Additional content is required').css({'color':'red'});
	    flag = true;
	} else {
		jQuery('span.aw_cc_additional_content').css( 'border','');
		jQuery('span.aw_cc_additional_content_msg').text('');
	}

	if(flag == true){
		return false;
	}
	return true;
}

function aw_cc_admin_get_order_details(action='')
{
	var org_user_id 	= jQuery("#rd_rp_user_id").val();
	var user_id 		= jQuery("#select2-customer_user-container").attr('title');
	var name 			= jQuery("#select2-customer_user-container span").html();
	var product_count 	= jQuery("#order_line_items tr").length;
	var order_id 		= jQuery("#post_ID").val();
	var count 			= 0;
	var payment_method  = jQuery("#_payment_method").val();
	var user_changed	= 0;

	var fullDate 		= new Date();
	var twoDigitMonth 	= fullDate.getMonth()+1+"";
	if(twoDigitMonth.length==1)
	{
		twoDigitMonth="0" +twoDigitMonth;
	}	
	var twoDigitDate 	= fullDate.getDate()+"";
	if(twoDigitDate.length==1)
	{
		twoDigitDate="0" +twoDigitDate;
	}	
	var currentDate 	= fullDate.getFullYear() + "-" + twoDigitDate + "-" + twoDigitMonth;

	if(jQuery.type(user_id) == "undefined")
	{
		return false;
	}
	else
	{
		var result = user_id.split('#');
		result = result[1].split(" ");
		result = parseInt(result[0]);
		if(result > 0)
		{
			jQuery("#aw_cc_user_id").val(result);
			jQuery.ajax({
				url: ajax_url,
				type: 'POST',
				async: false,
				data: {action: "aw_cc_admin_get_order_detail", user_id: result, user_name: name, order_id: order_id, nonce_cc_odr_ajax: aw_cc_admin_nonce},
				success:function(data) {
						obj = JSON.parse(data);
						var chk_recalculate = jQuery("#aw_cc_recalculate").val();
						order_total = parseInt(obj.order_total);
						if(msgcounterflag ==1 && chk_recalculate == "1" && action == 'recalculate' && product_count > 0 && order_total>0 && (obj.credit_limit != "0" || obj.credit_limit == null )&& obj.item_id != "0" && obj.error_msg !='' &&  payment_method=='companycredit_payment'){
							alert(obj.error_msg);	
							msgcounterflag++;
							jQuery("#aw_cc_recalculate").val('1');
							action == '';
							//jQuery('#_payment_method option[value=""]').remove();
							jQuery('#_payment_method > option').each(function(i, item) {
								if(jQuery(item).attr("selected") === "selected"){
									alert(item.label)
									jQuery(item).removeAttr("selected");
								}	
							})
							jQuery('#_payment_method').val('');
						}
						else if(product_count > 0 && name != "Guest" &&  payment_method=='companycredit_payment')
						{
							if(chk_recalculate == "0" && action == 'addnewitem' )
							{
								alert("Please press Recalculate Button to get order total");
								jQuery("#aw_cc_recalculate").val("1");
								msgcounterflag = 0;
								action == '';
							}
							msgcounterflag = 0;
						} else if(product_count > 0 && payment_method =='' && norepeatmessage == 0) {
							alert('Please select payment method and press Recalculate Button');
							norepeatmessage = 1;
						} else if(obj.credit_limit ==0 || obj.credit_limit == null ) {
							if('' != jQuery('#_payment_method').val() && payment_method=='companycredit_payment') {
								alert('Customer does not have enough credit limit for this payment method');
								jQuery('#_payment_method > option').each(function(i, item) {
									if(jQuery(item).attr("selected") === "selected"){
										alert(item.label)
										jQuery(item).removeAttr("selected");
									}	
								})
								jQuery('#_payment_method').val('');
							}
						}
				},
				error: function(errorThrown){
					console.log(errorThrown);
				}
			});

		}
	}
}

function aw_ca_set_grid_column_url()
{

	var url = url_admin+'/wp-admin/admin.php?page=customer-credit-history&';
	jQuery("#aw_cc_hostory_grid .transactions thead a").each(function(){
		var id= jQuery(this).parent().prop('id');
		var oldUrl = jQuery(this).attr("href");
 		var screen  = jQuery("button.tablinks.active").attr('data-screen');

 		var newUrl = oldUrl+'#aw_cc_hostory_grid';
		var part = newUrl.split("?")[1];
		changedUrl = url+part;
		jQuery(this).attr("href", changedUrl);
	});
	jQuery("aw_cc_hostory_grid .transactions tfoot th").each(function(){
		var $class 	= jQuery(this).attr('class');
		$class 		= $class.split(' ');
		var originlaclass = $class[1].split('-');
		var id 		= originlaclass[1];
		var oldUrl 	= jQuery(this).find( "a" ).attr("href");
		var screen  = jQuery("button.tablinks.active").attr('data-screen');
 		var newUrl = oldUrl+'#aw_cc_hostory_grid';
		var part = newUrl.split("?")[1];
		changedUrl = url+part;
		jQuery(this).find("a").attr("href", '');
		jQuery(this).find("a").attr("href", changedUrl);
	});
}

