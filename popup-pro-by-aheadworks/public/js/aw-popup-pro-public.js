var flag=0;
function unescapeHtml(safe) {
    return jQuery('<div />').html(safe).text();
}
jQuery('body').on('added_to_cart', function(e, fragments, cart_hash, this_button) {
	var product_id = jQuery(this_button).attr('data-product_id');
	var popup_id = jQuery(this_button).attr('data-popup-id');
	var url = js_var.site_url;
	if(jQuery.type(jQuery(this_button).attr('data-prevent')) === "undefined")
	{
		var site_url = url+'/wp-admin/admin-ajax.php?action=aw_popup_pro_product_add_to_cart_ajx&product_id='+product_id;
		jQuery('#popup-pro-show-tb_prod_ajx').html('');
		jQuery('#popup-pro-show-tb_prod_jquery').html('');
		jQuery.ajax({
			url: site_url,
			type: 'GET',
			success:function(data){
			data2 = unescapeHtml(data);
			 setTimeout(function(){
				if(data2 != "" && flag == 0)
				{
					flag=1;
					$i=0;
					var script = "";
					var total =  0;
					var inscript="";
					script += '<script type="text/javascript">';

					myobj= JSON.parse(data2);
					obj = Object.keys(myobj).map(function (key) { return myobj[key]; });

					total = parseInt(obj.length);
					if(total > 0)
					{
						//console.log(obj[0]);
						var all_data 	= obj[0].split('::');
						var content 	= all_data[0];
						var popup_type 	= all_data[1];
						var post_id 	= all_data[2];
						var title 		= all_data[3];
						var num_popup 	= all_data[4];
						var num_product = all_data[5];
						var func_name	= 'popup-pro-content-'+popup_type+'_'+post_id+'_1';
						setTimeout(function(){
						  jQuery('#popup-pro-show-tb_prod_ajx').append(content);
						},1000)
						
						script += 'function '+popup_type+'_'+post_id+'_1() {';
						script += 'setTimeout(function()'
						script += '{tb_show("'+title+'","#TB_inline?inlineId='+func_name+'",null)}, 1000);'//*'+$i+');'
						script += '}'+'\n';
						script += popup_type+'_'+post_id+'_1();'+'\n';
						script += 'resize_tb('+num_product+')';
						jQuery('html').addClass('noscroll');
						jQuery('#popup-pro-show-tb_prod_jquery').append(script);
						var $next=0;
						jQuery("body").on("thickbox:removed", function() {
							flag=0;
							if($i==0)
							{
								$next = 0;
							}  

							$next++;
							$i++;
							if(total > 1 && $next <= total-1)
							{
								var all_data 	= null;
								if(!jQuery.isEmptyObject(obj[$next]))
								{
									all_data 		= obj[$next].split('::');
									var content 	= all_data[0];
									var popup_type 	= all_data[1];
									var post_id 	= all_data[2];
									var title 		= all_data[3];
									var num_popup 	= all_data[4];
									var num_product = all_data[5];
									jQuery('#popup-pro-show-tb_prod_ajx').append(content);
									if(jQuery("#tb_unload_count_"+post_id+"_1").val()=="show") 
									{
										var func_name	= 'popup-pro-content-'+popup_type+'_'+post_id+'_1';
										setTimeout(function(){tb_show(title,"#TB_inline?inlineId="+func_name+"",null);},1000);
										resize_tb(num_product);
										jQuery("#tb_unload_count_"+post_id+"_1").val("hide");
										return false;
									}	
							    }
							}
							
						});
					}
				}

			  },1000);

			},
			error: function(qXHR,error,errorThrown)
			{
				console.log(errorThrown);
			}
		});
	}
	else
	{
		var site_url = url+'/wp-admin/admin-ajax.php?action=aw_popup_pro_product_add_to_cart&product_id='+product_id+'&popup_id='+popup_id;
	    jQuery.ajax({
	        url: site_url,
			type: 'GET',
	        success:function(data){
				if(data==1)
				{
					this_button.addClass('popup_pro_product_added');
					this_button.bind('click', false);
				}
	        },
	        error: function(errorThrown){
	            console.log(errorThrown);
	        }
	    });
	}
});

jQuery(document).keydown(function(e) {
  if (e.keyCode == 27) {
       jQuery('html').removeClass('noscroll');
    }
});
function close_tb()
{
	jQuery('html').removeClass('noscroll');
	tb_remove();
}

function resize_tb(product_count)
{
	jQuery("html").addClass("noscroll");
	var win_wid = jQuery(window).width();
	wid = 200 * product_count;
	var TB_WIDTH = ((product_count*win_wid)/win_wid )* 200;
	wid = TB_WIDTH;
	var TB_HEIGHT = 350;//315;

 	if(TB_WIDTH > win_wid )
	{
		TB_WIDTH = jQuery(window).width();
		TB_WIDTH = TB_WIDTH - 100;
	}

	if(product_count > 7)
	{
		var TB_HEIGHT = 600;//590;
	}

	if(win_wid > '1600' && product_count <= 10)
	{
		var TB_HEIGHT = 335;
	}

	setTimeout(function() {
		var title = jQuery("#TB_ajaxWindowTitle").text();
		var title_len = title.length;

		jQuery("#TB_window").css({
			marginLeft: '-' + parseInt((TB_WIDTH / 2), 10) + 'px',
			width: TB_WIDTH + 'px',
			marginTop: '-' + parseInt((TB_HEIGHT / 2), 10) + 'px',
			'background-color': 'white'
		});
		jQuery("#TB_ajaxContent").css({
			width: TB_WIDTH + 'px',
			height: TB_HEIGHT + 'px'
		});
		jQuery('.popup-pro-main-dv ').css('text-align','center');
		if(wid > win_wid )
		{
			jQuery('.popup-pro-main-dv ').css('text-align','left');
		}
	}, 1000);
}

function do_popup_pro_subscribe(this_btn)
{
	var flag = 0;

	var post_id = jQuery(this_btn).closest("#TB_ajaxContent").find("input[name='popup-pro-subscribe-post-id']").val();
	var name = jQuery(this_btn).closest("#TB_ajaxContent").find("input[name='popup-pro-subscribe-name']").val();
	var email = jQuery(this_btn).closest("#TB_ajaxContent").find("input[name='popup-pro-subscribe-email']").val();

	name = jQuery.trim(name);
	email = jQuery.trim(email);

	if(name == "")
	{
		alert("Enter Name");
		jQuery(this_btn).closest("#TB_ajaxContent").find("input[name='popup-pro-subscribe-name']").val("");
		jQuery(this_btn).closest("#TB_ajaxContent").find("input[name='popup-pro-subscribe-name']").focus();
	}
	else if(email == "")
	{
		alert("Enter Email");
		jQuery(this_btn).closest("#TB_ajaxContent").find("input[name='popup-pro-subscribe-email']").val("");
		jQuery(this_btn).closest("#TB_ajaxContent").find("input[name='popup-pro-subscribe-email']").focus();
	}
	else if(!validateEmail(email))
	{
		alert("Enter vaild email");
	}
	else
	{
		jQuery(this_btn).closest("#TB_ajaxContent").find("input[name='popup-pro-subscribe-name']").css('border', '');
		jQuery(this_btn).closest("#TB_ajaxContent").find("input[name='popup-pro-subscribe-email']").css('border', '');
		flag = 1;
	}
	if(flag == 1)
	{
		var url = js_var.site_url;
		var site_url = url+'/wp-admin/admin-ajax.php?action=aw_popup_pro_add_subscriber&post_id='+post_id+'&name='+name+'&email='+email;

		jQuery.ajax({
			url: site_url,
			type: 'GET',
			success:function(data){
				var response = jQuery.trim(data);
				if(response != "")
				{
					alert(response);
				}
				jQuery('input[id^=tb_unload_count_]').val("hide");
				tb_remove();
			},
			error: function(qXHR,error,errorThrown)
			{
				console.log(errorThrown);
			}
		});
	}
}

function validateEmail(sEmail)
{
	var filter = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
	if (filter.test(sEmail))
	{
		return true;
	}
	else
	{
		return false;
	}
}
function sortByKey(array, key)
{
    return array.sort(function(a, b) {
        var x = a[key]; var y = b[key];
        return ((x < y) ? -1 : ((x > y) ? 1 : 0));
    });
}

jQuery(window).load(function(){
	var arr = jQuery('input[name="allpopup[]"]').map(function () {return this.value;}).get();
	var num_pro = 0;
	var new_func_name = "";
	
	var my_vars = new Array();
	var reversed_arr = new Array();
	
	reversed_arr = arr;	

	if(reversed_arr.length > 0)
	{
		my_vars       = reversed_arr[0].split("::");
		popup_type    = my_vars[0];
		new_func_name = my_vars[1];
		
		var chk_string = JSON.stringify(reversed_arr);

		sub = chk_string.includes("subscribe");
		lin = chk_string.includes("linked");

		if(lin && sub)
		{
			reversed_arr  = reorder_array(arr);
			my_vars       = reversed_arr[0].split("::");
			popup_type    = my_vars[0];
			new_func_name = my_vars[1];
		}

		if(popup_type=="linked")
		{
			jQuery('html').addClass('noscroll');
			num_pro       = my_vars[2];

			if(jQuery("a").hasClass("popup-pro-show-tb_btn add_to_cart_button ajax_add_to_cart"))
			{
					jQuery('.popup-pro-show-tb_btn').each(function( index ) {
						var hrf = jQuery(this).attr('href');
						var product_id = getUrlVars(hrf)["add-to-cart"];
						var popup_id = jQuery(this).attr('data-popup-id');
						if(jQuery.type(product_id) != "undefined")
						{
							jQuery(this).attr("data-product_id", product_id);
							jQuery(this).attr("data-popup_id", popup_id);
						}
					});
			}
		}
	 	if(typeof window[new_func_name] === 'function')// && my_vars[0]=="linked")
		{
			window[new_func_name]();
			if(popup_type == "linked")
			{
				resize_tb(num_pro);
			}
		}
	}
})
var indexing = 1;
jQuery( 'body' ).on( 'thickbox:removed', function() {
	jQuery('html').removeClass('noscroll');
	var arr 	= jQuery('input[name="allpopup[]"]').map(function () {return this.value;}).get();
	var num_pro = 0;
	var reversed_arr = arr;
	var currentpopup = jQuery(".current_popup_id").val();
	var flag='';

	var chk_string = JSON.stringify(arr);
	sub = chk_string.includes("subscribe");
	lin = chk_string.includes("linked");

	if(lin && sub)
	{
		var reversed_arr  = reorder_array(arr);
	}
	if(typeof reversed_arr[indexing] != 'undefined')
	{
		var my_vars 		= reversed_arr[indexing].split("::");
		var popup_type 		= my_vars[0];
		var new_func_name 	= my_vars[1];

		if(popup_type=="linked")
		{
			num_pro = my_vars[2];
			popup_id = my_vars[3];
			flag = '_0';
		}
		else
		{
			popup_id = my_vars[3];
		}
		if(jQuery('#tb_unload_count_'+popup_id+flag).val() == 'show')
		{
			jQuery('#tb_unload_count_'+popup_id+'').val('hide');
			if(typeof reversed_arr[indexing] != 'undefined')
			{
				my_vars		  =	reversed_arr[indexing].split("::");		  
				popup_type    = my_vars[0];
				new_func_name = my_vars[1];
				window[new_func_name]();
				if(popup_type=="linked")
				{
					resize_tb(num_pro);
				}
			}
		}
	}
	else
	{
		return false;
	}
	indexing++;	 
});

function reorder_array(arr)
{
	var reverse 	= new Array();
	var ary_srt 	= new Array();
	var sorted_arr  = new Array();
	jQuery.each(arr, function( index, value ){
				var get_id = value.split("::");
				ary_srt.push(get_id[3]);
				sorted_arr.push(get_id[3]);
	});
	sorted_arr.sort(function(a, b){
				    return a - b;
	});
	jQuery.each(sorted_arr, function( index, value ){
		var orderindex	  = ary_srt.indexOf(value.toString());
		var my_vars 	  = arr[orderindex].split("::");
		var popup_type    = my_vars[0];
		var new_func_name = my_vars[1];
		reverse.push(arr[orderindex])
	});
	return reverse;
}
function getUrlVars(hrf)
{
    var vars = [], hash;
    var hashes = hrf.slice(hrf.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}