var url  		= order_js_var.site_url;
var ajax_url	= order_js_var.ajax_url;
var path		= order_js_var.path;
var host		= order_js_var.host;
var rd_order_nonce	= order_js_var.rd_order_nonce;

jQuery(document).ready(function(){
});

jQuery(window).load(function(){
	if(jQuery('#customer_user').length)
	{
		jQuery("#customer_user").change(function() {
			get_rd_admin_points();
		});
	}
});

function get_rd_admin_points()
{
	//var user_id = parseInt(jQuery('#customer_user option:last').val());

	//jQuery("#rd_rp_user_id").val("");

	var org_user_id 	= jQuery("#rd_rp_user_id").val();
	var user_id 		= jQuery("#select2-customer_user-container").attr('title');
	var name 			= jQuery("#select2-customer_user-container span").html();
	var product_count 	= jQuery("#order_line_items tr").length;
	var order_id 		= jQuery("#post_ID").val();
	var points_reduced 	= jQuery("#rd_rp_points_reduced").val();
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
			jQuery("#rd_rp_user_id").val(result);

			jQuery.ajax({
				url: ajax_url,
				type: 'POST',
				data: {action: "aw_admin_get_points", user_id: result, user_name: name, order_id: order_id, nonce_odr_ajax: rd_order_nonce},
				success:function(data){
						obj = JSON.parse(data);
						var balance = parseInt(obj.balance);

						var present_date 	= new Date(fullDate.getFullYear(), twoDigitMonth, twoDigitDate);
						var expiry_check	= 0;
						
						var customer_rates_check = 0;
						
						var customer_rates = obj.customer_rates;
						if(customer_rates != null)
						{
							customer_rates_check = 1;
						}

						var get_expiry_date = obj.expiration_date;

						if(get_expiry_date != null)
						{
							var expiry_date = get_expiry_date.split('-');
							expiry_date 	= new Date(expiry_date[0], expiry_date[1], expiry_date[2]);
							
							if (expiry_date >= present_date)
							{
								expiry_check = 1;
							}
						}
						else
						{
							expiry_check = 1;
						}

						if(balance > 0 && product_count > 0 && name != "Guest" && expiry_check == 1 && customer_rates_check == 1)
						{
							jQuery('#rd_reward_admin_points').prop("disabled", false);
							jQuery('#rd_admin_points_txt').removeAttr("style");

							if(points_reduced == "zero")
							{
								jQuery('#rd_admin_points_txt').html("Points: "+balance);
							}

							var chk_recalculate = jQuery("#rd_rp_recalculate").val();
							if(chk_recalculate != "1")
							{
								jQuery("#rd_reward_admin_points").html("Apply Points");
								jQuery('#rd_admin_points_txt').html("Points: "+balance);
							}
							if(chk_recalculate == "1" && points_reduced != "zero")
							{
								jQuery('#rd_reward_admin_points').prop("disabled", true);
								jQuery('#rd_admin_points_txt').css({"color":'#a0a5aa !important'});
							}
						}
						else
						{
							if(obj.point_exists != "0" && obj.item_id != "0")
							{
								jQuery('tr[data-order_item_id='+ obj.item_id +']').remove();
								jQuery('#rd_rp_points_reduced').val("zero");
								//alert("here 3");
								jQuery('button.calculate-action').trigger('click');
								jQuery('#rd_admin_points_txt').html("Points: "+balance);
							}
							jQuery('#rd_reward_admin_points').prop("disabled", true);
							jQuery("#rd_reward_admin_points").html("Apply Points");
							jQuery('#rd_admin_points_txt').html("");
							jQuery('#rd_admin_points_txt').css({"color":'#a0a5aa !important'});
						}
				},
				error: function(errorThrown){
					console.log(errorThrown);
				}
			});
		}
	}
}

function apply_rd_admin_points(me)
{
	var chk_recalculate = jQuery("#rd_rp_recalculate").val();
	if(chk_recalculate != "1")
	{
		alert("Please click Recalculate Button before you can apply points");
		return false;
	}

	var btn_type = me.value;
	var user_id = jQuery("#rd_rp_user_id").val();
	var order_id = jQuery("#post_ID").val();

	var name = "";
	var opt_type = "";

	if(btn_type == "Apply Points")
	{
		opt_type = "apply";
		name = "Apply Points";
	}
	else
	{
		opt_type = "remove";
		name = "Apply Points";
	}

	jQuery("#rd_admin_points_main_dv").addClass("rd_admin_order_loader");
	jQuery("#rd_admin_points_main").hide();

	jQuery.ajax({
		url: ajax_url,
		type: 'POST',
		async: false,
		data: {action: "aw_admin_apply_points", opt_type: opt_type, user_id: user_id, order_id: order_id, nonce_odr_ajax: rd_order_nonce},
		success:function(data){
				obj = JSON.parse(data);

				setTimeout(function(){
					if(obj.msg != "")
					{
						alert(obj.msg);
					}
					
					if(obj.disp != "New")
					{
						jQuery("input[name='order_item_name["+obj.item_id+"]']").val(obj.pts_rdcd);
						jQuery("input[name='line_total["+obj.item_id+"]']").val(-obj.ln_ttl);
					}

					jQuery('button.calculate-action').trigger('click');

					jQuery("#rd_reward_admin_points").html(obj.type);
					jQuery("#rd_reward_admin_points").val(obj.type);
					//jQuery("#rd_admin_points_txt").html("Points: " + obj.customer_points);
				},1000);
		},
		error: function(errorThrown){
			console.log(errorThrown);
		}
	});
	jQuery("#rd_admin_points_main_dv").removeClass("rd_admin_order_loader noClass");
	jQuery("#rd_admin_points_main").show();
}