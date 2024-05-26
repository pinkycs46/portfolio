var url  = js_var.site_url;
var nonce = js_var.rd_nonce;

function apply_rd_points(me)
{
	var site_url = url+'/wp-admin/admin-ajax.php';
	var btn_type = me.value;
	var name = "";
	var opt_type = "";

	if(btn_type == "Apply Points")
	{
		opt_type = "apply";
		name = "Remove Points";
	}
	else
	{
		opt_type = "remove";
		name = "Apply Points";
	}

	jQuery.ajax({
		url: site_url,
		type: 'POST',
		data: {action: "aw_apply_points", opt_type: opt_type, nonce_ajax: nonce},
		success:function(data){
				obj = JSON.parse(data);
				jQuery('[name="update_cart"]').prop("disabled", false);
				jQuery('[name="update_cart"]').trigger('click');
				jQuery('#rd_reward_customer_balance').val(obj.customer_points);
				setTimeout(function(){
					jQuery("#rd_reward_points").html(data.type);
					jQuery("#rd_reward_points").val(obj.type);
					//jQuery(".rd_customer_points").html('Points: '+obj.customer_points);
				},1000);
		},
		error: function(errorThrown){
			console.log(errorThrown);
		}
	});
}