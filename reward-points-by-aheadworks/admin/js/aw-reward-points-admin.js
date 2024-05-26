var balance_column = ['id','user_nicename','user_email','lifetime_sale','balance','earnedpoints','spendpoints','expiration_date'];
var transaction_column = ['id_t','user_nicename_t','user_email_t','comments','transaction_description','balance_change','transaction_balance','transaction_date'];
// Get the modal
var modal = document.getElementById("update_bal_Modal");
// Get the <span> element that closes the modal
var span = document.getElementsByClassName("bal_modal_close")[0];


jQuery.urlParam = function(name){
		var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
		if(results!=null)
			return results[1] || 0;
		else
			return 0;
	}
	
jQuery.urlParam_extrs = function(url,name){
		var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(url);
		if(results!=null)
			return results[1] || 0;
		else
			return 0;
	}

jQuery(document).ready(function(){

	jQuery('.current-page').keypress(function(event){
		var screen  = jQuery("button.tablinks.active").attr('data-screen');
	    var keycode = (event.keyCode ? event.keyCode : event.which);
	    if(keycode == '13') {
	    	if(screen== 'balance-tab') {
	    		jQuery("#balance-table").append("<input type='hidden' name='page' value='reward-transaction-balance'>");	
	    		jQuery("#balance-table").append("<input type='hidden' name='screen' value='"+screen+"'>");
	    	} else {
	    		jQuery("#transaction-table").append("<input type='hidden' name='page' value='reward-transaction-balance'>");	
	    		jQuery("#transaction-table").append("<input type='hidden' name='screen' value='"+screen+"'>");
	    	}
			jQuery("input[name=_wp_http_referer]").remove(); 
			jQuery("input[name=_wpnonce]").remove('');
	    }
	});

});
jQuery(window).load(function(){
	jQuery.urlParam = function(name){
		var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
		if(results!=null)
			return results[1] || 0;
		else
			return 0;
	}

	var tabName = jQuery.urlParam('screen'); // name
	var page = jQuery.urlParam('page');

	if(page == "reward-configuration")
	{
		document.getElementById('earnpoints-tab').style.display = "block";
	}
	if(page == "reward-transaction-balance")
	{
		document.getElementById('balance-tab').style.display = "block";
	}	
	tablinks = document.getElementsByClassName("tablinks");
	tablinks[0].className = 'tablinks active';

	hide_column_checkbox(transaction_column);

	if(tabName == "0")
	{
		set_noitem_colspan(1); // for no item display grid
		jQuery(".wp-heading-inline").html("Customer Balance");
		set_grid_column_url();
	}

	if(tabName=='balance-tab')
	{
		jQuery(".wp-heading-inline").html("Customer Balance");
		document.getElementById(tabName).style.display = "block";
		document.getElementById('transaction-tab').style.display = "none";
		tablinks = document.getElementsByClassName("tablinks");
		tablinks[0].className = 'tablinks active';
		tablinks[1].className = 'tablinks';
		hide_column_checkbox(transaction_column);
		show_column_checkbox(balance_column);
		set_noitem_colspan(1); // for no item display grid
	}

	if(tabName=='transaction-tab')
	{
		jQuery(".wp-heading-inline").html("Transaction History");
		document.getElementById(tabName).style.display = "block";
		document.getElementById('balance-tab').style.display = "none";
		tablinks = document.getElementsByClassName("tablinks");
		tablinks[1].className = 'tablinks active';
		tablinks[0].className = 'tablinks';
		hide_column_checkbox(balance_column);
		show_column_checkbox(transaction_column);
		set_noitem_colspan(0); // for no item display grid
	}	
	jQuery("#transaction-tab .bulkactions").remove();
	/***************************/
	/* For bulk action Apply button */
	/*jQuery("#balance-tab #doaction").attr('type','button');*/
	jQuery("#balance-tab .action").attr('type','button');
	jQuery('#the-list th input').addClass("rd_checkbox_id");
	/***************************/

	responsive_row_headname();
	jQuery("#wpfooter").append('<input type="hidden" value="false" id="hidden_responsiv_var"/>');
	/*console.log(jQuery(window).width());
	if(jQuery(window).width() < 450)
	{
		jQuery("#screen-meta-links").hide();
	}*/
	if(jQuery("#reward_display_earn option:selected").val() == 'NO') {
		jQuery("li#hide_nodisplay").hide();
		jQuery("#displayearnyes_info").hide();
	}

}); 

 
jQuery(document).on('click','.toggle-row',function(e) {
	var screen  = jQuery("button.tablinks.active").attr('data-screen');
	if(jQuery("#hidden_responsiv_var").val()==='true') {
		jQuery(this).closest("tr").toggleClass('is-expanded');	
	} 
	if(screen == 'transaction-tab') {
		var id = jQuery(this).closest("tr").find('td.column-id_t').text();		
		var name = jQuery(this).closest("tr.is-expanded").find('td.user_nicename_t').attr("data-colname");
		if(jQuery(this).closest("tr").hasClass('is-expanded')) 
		{
			jQuery(this).closest("tr.is-expanded").find('td.user_nicename_t').attr("data-colname" ,'Customer Name');
			jQuery(this).closest("tr.is-expanded").find('td.user_nicename_t').text(name);

			jQuery(this).closest("tr.is-expanded").find('td.user_nicename_t').addClass('td-'+id);
		} else {
			var name = jQuery('.td-'+id).text();
			jQuery('.td-'+id).attr("data-colname" ,name);
			jQuery('.td-'+id).text('');
		}	
	}
	
});
jQuery(document).on('click','.configuration_submit',function(e){
	var tab = jQuery(this).attr('data-value');
	var $flag = 0;
	var error_msg = [];
	var sorted_arr = [];
	var duplicate=[];
	var total = jQuery('#'+tab+'_table .txt_required').length;
	error_msg[0] = ' ';
	error_msg[1] = 'This is a required field';
	error_msg[2] = 'Numberic value is required';
	error_msg[3] = 'Enter a number greater than 0 in this field';
	error_msg[4] = 'Values cannot be decimal';
	error_msg[5] = 'Lifetime Purchase values can\'t be the same';
	error_msg[6] = 'Should be equal or below 100';
	jQuery('#'+tab+'_table .lfsale').each(function(index){
		sorted_arr.push(parseFloat(jQuery(this).val()));
		sorted_arr = sorted_arr.sort();
		for(var i = 0; i < sorted_arr.length - 1; i++) 
		{  
            if(sorted_arr[i + 1] == sorted_arr[i]) 
            {  
                duplicate.push(sorted_arr[i]);  
            }   
        } 
	});
	if((jQuery.trim(jQuery(".cover_per_txt").val())>100) && tab === 'spend')
	{
		$flag = 6;
		jQuery(".cover_per_txt").css( 'border','1px solid red');
		jQuery("#cover_per_span").text(error_msg[$flag]).css('color','red');
	}
	else if(parseInt(jQuery.trim(jQuery(".cover_per_txt").val()))===0)
	{
		$flag = 3;
		jQuery(".cover_per_txt").css( 'border','1px solid red');
		jQuery("#cover_per_span").text(error_msg[$flag]).css('color','red');
	}
	else
	{
		jQuery(".cover_per_txt").css( 'border','1px solid lightgrey');
		jQuery("#cover_per_span").text('');
	}

	jQuery('#'+tab+'_table .txt_required').each(function(index){
		id = jQuery(this).attr('data-value');
		if(jQuery.trim(jQuery( this ).val()) == '')
		{	
			$flag = 1;
			jQuery(this).css( 'border','1px solid red');
			jQuery("#"+id).text(error_msg[$flag]).css('color','red');
		}
		else if(!jQuery.isNumeric(jQuery.trim(jQuery( this ).val())))
		{
			$flag = 2;
			jQuery(this).css( 'border','1px solid red');
			jQuery("#"+id).text(error_msg[$flag]).css('color','red');
		}
		else if(jQuery.trim(jQuery( this ).val()) <= 0  && jQuery(this).attr('data-allowed') === 'false')
		{
			$flag = 3;
			jQuery(this).css( 'border','1px solid red');
			jQuery("#"+id).text(error_msg[$flag]).css('color','red');
		}
		else if(index%3 == 0)
		{
			if(jQuery.inArray(parseFloat(jQuery(this).val()),duplicate)!= -1)
			{
				$flag = 5;
				jQuery(this).css( 'border','1px solid red');
				jQuery("#"+id).text(error_msg[$flag]).css('color','red');	
			}
			else
			{
				jQuery(this).css( 'border','1px solid lightgrey');
				jQuery("#"+id).text('');					
			}
		}
		else
		{
				jQuery(this).css( 'border','1px solid lightgrey');
				jQuery("#"+id).text('');	
		}

		if(total == index+1)
		{
			if($flag > 0)
			{
				if(tab==='earn')
					$remove = 'spend';
				else
					$remove = 'earn';
				openTab(event, tab+'points-tab');
				jQuery("#design_"+$remove+"points_tab").removeClass('active');
				jQuery("#design_"+tab+"points_tab").addClass('active');
				e.preventDefault();
				return false;
			}
			else
			{
				jQuery(this).css('border' , '1px solid lightgrey');
				jQuery("#"+id).text(error_msg[$flag]);
				return true;
			}
		}
	});

	if(tab=='storefront' && jQuery('#'+tab+'_prmotext').val()=="" && jQuery("#reward_display_earn option:selected").val() == "YES") {
		$flag = 1;
		jQuery('#'+tab+'_prmotext').css( 'border','1px solid red');
		jQuery('#error_'+tab).text(error_msg[$flag]).css('color','red');	
	}
	 

	if((total==0 && $flag==6)||$flag==3||$flag==1	)
		return false;
});

function addmorerate(type)
{
	var url  = js_var.site_url;
	rownumber= parseInt(jQuery("#addmore"+type).attr('data-row'));
	type_str = "'"+jQuery.trim(type)+"'";
 	$append  = '<tr id="'+type+'_row_'+rownumber+'"><td><input type="text" name="'+type+'rates['+rownumber+'][lifetime_sale]" class="txt_required lfsale" data-allowed="true" value="" data-value="'+type+'_error_'+rownumber+'_0"  onkeypress="return checkIt(event,true)"><br/><span id="'+type+'_error_'+rownumber+'_0"></span></td><td><input type="text" name="'+type+'rates['+rownumber+'][base_currency]" class="txt_required"  value="" data-allowed="false" data-value="'+type+'_error_'+rownumber+'_1"  onkeypress="return checkIt(event,false)"><br><span id="'+type+'_error_'+rownumber+'_1"></span></td><td><input type="text" name="'+type+'rates['+rownumber+'][points]" class="txt_required" value="" data-allowed="false" data-value="'+type+'_error_'+rownumber+'_2"  onkeypress="return checkIt(event,false)"><span id="'+type+'_error_'+rownumber+'_2"><br></span></td><td><a href="javascript:void(0)" onclick="return deleterate('+type_str+','+rownumber+')"><img src="'+url+'/wp-content/plugins/reward-points-by-aheadworks/admin/images/aw_trash-icon.png"></a><input type="hidden" class="current_row" value="'+rownumber+'"></td></tr>';		
	jQuery("#"+type+"_table tbody").append($append);
	rownumber = rownumber+1;
	jQuery("#addmore"+type).attr('data-row',rownumber);
 
}
function deleterate(type,id)
{
	jQuery("#"+type+'_row_'+id).remove();
}
function openTab(evt, tabName, screen='')
{
	evt.preventDefault();
	var i, tabcontent, tablinks;

	if(screen == "reward-configuration")
	{
		document.getElementById('earnpoints-tab').style.display = "none";
	}
	if(screen == "reward-transaction-balance")
	{
		document.getElementById('balance-tab').style.display = "none";
	}


	tablinks = document.getElementsByClassName("tablinks");
	tablinks[0].className =  tablinks[0].className.replace("active", "");

	tabcontent = document.getElementsByClassName("tabcontent");
	for(i = 0; i < tabcontent.length; i++)
	{
		tabcontent[i].style.display = "none";
	}

	for(i = 0; i < tablinks.length; i++)
	{
		tablinks[i].className = tablinks[i].className.replace("active", "");
	}
	document.getElementById(tabName).style.display = "block";
	evt.currentTarget.className += " active";
	var url = js_var.site_url;
	var tab= jQuery('.active').text();
	tab = tab.replace(/\s/g, '')
	tab = tab.toLowerCase()

	if(screen === 'balance')
	{
		hide_column_checkbox(transaction_column);
		show_column_checkbox(balance_column)
		jQuery(".wp-heading-inline").html("Customer Balance");
	}
	else if(screen === 'transaction')
	{
		hide_column_checkbox(balance_column);
		show_column_checkbox(transaction_column);
		jQuery(".wp-heading-inline").html("Transaction History");
	}
	if(tabName == 'transaction-tab' || tabName == 'balance-tab')
	{
		rd_reward_points_tab_content(screen+'-tab','direct');
	}
}

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
function checkupdateval(evt,allowed = '')
{
	evt = (evt) ? evt : window.event;
	 
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if(charCode === 45 && allowed == true)
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

function hide_column_checkbox(hidden_array)
{
	jQuery.each(hidden_array,function(index,value){
		jQuery("table .column-"+value).hide();
    	jQuery("table ."+value).hide();
    	jQuery("#"+value+"-hide").parent().css('display','none');
	})
}

function show_column_checkbox(hidden_array)
{
	jQuery.each(hidden_array,function(index,value){
		jQuery("table .column-"+value).removeAttr('style');
    	jQuery("table ."+value).removeAttr('style');
    	jQuery("#"+value+"-hide").parent().removeAttr('style');
	})
}

function set_noitem_colspan(totaldisplay)
{
	var screen = jQuery.urlParam('screen');
	var column = [];
	if(screen==='balance-tab')
	{
		column = balance_column;
	}
	else
	{
		column = transaction_column;		
	}
	jQuery("table thead a").each(function(index){
		column_name = jQuery(this).parent().prop('id'); 
		if((!jQuery(this).parent().is(":hidden")) && (jQuery.inArray(column_name,column) !== -1))
		{
			totaldisplay++;
		}
	}); 
	jQuery(".colspanchange").attr('colspan',totaldisplay); 
}

function responsive_row_headname()
{
	jQuery('.transactions tbody tr').each(function(index) {
	  	var screen  = jQuery("button.tablinks.active").attr('data-screen');
	  	index = index+1;
		jQuery(this).find('td').each(function (key, val) {
			 	var $class 	=jQuery(this).attr("class");
			 	$class 	= $class.split(' ');
			 	var originlaclass = $class[1].split('-');
			 	var classname = originlaclass[1];

				if( classname.length>0 && screen == "transaction-tab" &&   classname.localeCompare("user_nicename_t") == 0 && jQuery(window).width() < 769)
				{
					name =jQuery('.transactions tbody tr:nth-child('+(index)+')').find('td.'+classname).text();
					parentclassname = classname.replace('_t','');
					jQuery('.transactions tbody tr:nth-child('+index+')').find('td.'+classname).attr("data-colname" ,name);
					jQuery('.transactions tbody tr:nth-child('+index+')').find('td.'+classname).text("");
				}
				if( classname.length>0 && screen == "balance-tab" && "user_nicename_t" == classname && jQuery(window).width() < 769)
				{
					name =jQuery('.transactions tbody tr:nth-child('+(index)+')').find('td.'+classname).text();
					parentclassname = classname.replace('_t','');
					jQuery('.transactions tbody tr:nth-child('+index+')').find('td.'+parentclassname).attr("data-colname" ,name);
				}
		});
	});
}

function hide_display_toggle_checked_column()
{	
	var hidden_column = [];
	var block_column = [];
	var i = 0;
	var j = 0;
	jQuery(".hide-column-tog").each(function(){
		if((!jQuery(this).parent().attr('style')))
		{
			if(jQuery(this).prop("checked")== false) 
			{ 
				var value = jQuery(this).val();
    			jQuery("table thead #"+value).removeAttr('style');
    			jQuery("table thead #"+value).addClass('hidden');

    			jQuery("table tbody ."+value).removeAttr('style');
    			jQuery("table tbody ."+value).addClass('hidden');

    			jQuery("table tfoot .column-"+value).removeAttr('style');
    			jQuery("table tfoot .column-"+value).addClass('hidden');
			}
		}
	}); 
}

function set_grid_column_url()
{
	var url = js_var.site_url+'/wp-admin/admin.php?page=reward-transaction-balance&';
	jQuery(".transactions thead a").each(function(){
		var id= jQuery(this).parent().prop('id');
		var oldUrl = jQuery(this).attr("href");
/*		if(jQuery.inArray(id, balance_column) !== -1 )
		{
			screen = 'balance-tab';
		}
		if(jQuery.inArray(id, transaction_column) !== -1)
		{
			screen = 'transaction-tab';
		}*/

		var screen  = jQuery("button.tablinks.active").attr('data-screen');

 		var newUrl = oldUrl+'&screen='+screen; 
		var part = newUrl.split("?")[1];
		changedUrl = url+part;
		jQuery(this).attr("href", changedUrl);
	});
	jQuery(".transactions tfoot th").each(function(){
		var $class 	= jQuery(this).attr('class');
		$class 		= $class.split(' ');
		var originlaclass = $class[1].split('-');
		var id 		= originlaclass[1];
		var oldUrl 	= jQuery(this).find( "a" ).attr("href");
/*		if(jQuery.inArray(id, balance_column) !== -1 )
		{
			screen = 'balance-tab';
		}
		if(jQuery.inArray(id, transaction_column) !== -1)
		{
			screen = 'transaction-tab';
		}*/
		var screen  = jQuery("button.tablinks.active").attr('data-screen');
 		var newUrl = oldUrl+'&screen='+screen; 
		var part = newUrl.split("?")[1];
		changedUrl = url+part;
		jQuery(this).find("a").attr("href", '');
		jQuery(this).find("a").attr("href", changedUrl);
	});
}

function set_pagination_tab_url()
{
	var url 	= js_var.site_url+'/wp-admin/admin.php?page=reward-transaction-balance';
	var screen  = jQuery("button.tablinks.active").attr('data-screen');
	var oldurl  = jQuery(".tablenav-pages a").attr('href');
	var urlParams = new URLSearchParams(oldurl);
	var con_url = decodeURIComponent(urlParams.toString());
	var parturl = '';

	jQuery.urlParam = function(name){
		var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(con_url);
		if(results!=null)
			return results[1] || 0;
		else
			return 0;
	}

	if(jQuery.urlParam('s'))
		parturl+= 's='+jQuery.urlParam('s')+'&';		
	/*if(jQuery.urlParam('orderby'))
		parturl+= 'orderby='+jQuery.urlParam('orderby')+'&';		
	if(jQuery.urlParam('order'))
		parturl+= 'order='+jQuery.urlParam('order')+'&';*/
	if(jQuery.urlParam('paged'))
		parturl+= 'paged='+jQuery.urlParam('paged');

	var changedurl = url+'&screen='+screen+'&'+parturl;
	jQuery(".tablenav-pages a").attr('href',changedurl);
}

function rd_reward_points_tab_content(screen,from='')
{
	jQuery.urlParam = function(name){
		var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
		if(results!=null)
			return results[1] || 0;
		else
			return 0;
	}
	var tabName= jQuery.urlParam('screen');
	var parturl='';
	if( tabName === screen && from == '')  
	{
		if(jQuery.urlParam('s'))
			parturl+= 's='+jQuery.urlParam('s')+'&';		
		if(jQuery.urlParam('orderby'))
			parturl+= 'orderby='+jQuery.urlParam('orderby')+'&';		
		if(jQuery.urlParam('order'))
			parturl+= 'order='+jQuery.urlParam('order')+'&';
	}

	jQuery("#"+screen).html('')
	jQuery("#"+screen).addClass('loader');

	var url = js_var.site_url;
	request_data = 'action=aw_reward_points_tabcontent&tabName='+screen+'&'+parturl;
	var searchform = '<form id="posts-filter" method="get"><p class="search-box"><input type="hidden" name="page" class="page" value="reward-transaction-balance"><input type="hidden" name="screen" class="post_status_page" value="'+screen+'"><input type="search" id="post-search-input" name="s" value=""><input type="submit" id="search-submit" class="button" value="Search Customer"></p></form>';
	if(screen == 'transaction-tab')
	{
		var searchform = '<form id="posts-filter" method="get"><p class="search-box"><input type="hidden" name="page" class="page" value="reward-transaction-balance"><input type="hidden" name="screen" class="post_status_page" value="'+screen+'"><input type="search" id="post-search-input" name="s" value=""><input type="submit" id="search-submit" class="button" value="Search Customer" title="Search Customer by Customer Name or Customer Email or Order #"></p></form>';
	}
	jQuery.ajax({
		url: js_var.ajax_url,
		type:'POST',
		data: request_data,
		success:function(data){
			jQuery("#"+screen).html('');
			jQuery("#"+screen).removeClass('loader');
			jQuery("#"+screen).append(searchform);
			jQuery("#"+screen).append(data);
			hide_display_toggle_checked_column();

			if(screen==='balance-tab')
			{
				hide_column_checkbox(transaction_column);
				show_column_checkbox(balance_column)
				set_noitem_colspan(0);
				/* For balance update popup */
				/*jQuery("#balance-tab #doaction").attr('type','button');*/
				jQuery("#balance-tab .action").attr('type','button');
				jQuery('#the-list th input').addClass("rd_checkbox_id");
				/****************************/

				/* For Pagination input Start */
				jQuery("#balance-table").append("<input type='hidden' name='page' value='reward-transaction-balance'><input type='hidden' name='screen' value='balance-tab'>");
				jQuery("input[name=_wp_http_referer]").remove(); 
				jQuery("input[name=_wpnonce]").remove('');
			}
			else 
			{
				hide_column_checkbox(balance_column);
				show_column_checkbox(transaction_column);
				set_noitem_colspan(0);

				/* For Pagination input Start */
				jQuery("#transaction-table").append("<input type='hidden' name='page' value='reward-transaction-balance'>");
				jQuery("#transaction-table").append("<input type='hidden' name='screen' value='transaction-tab'>");
				jQuery("input[name=_wp_http_referer]").remove(); 
				jQuery("input[name=_wpnonce]").remove('');
			}

			set_pagination_tab_url();
			set_grid_column_url();
			jQuery("#hidden_responsiv_var").val("true");
			responsive_row_headname();
		},
		error: function(errorThrown){
			console.log(errorThrown);
		}
	});
}

function displayearn(me){
	var option = me.value;  
	if(me.value == "YES") {
		jQuery("#hide_nodisplay").show();
		jQuery("#displayearnyes_info").show();
		jQuery('#reward_display_earn option[value="YES"]').attr("selected","selected");
		jQuery('#reward_display_earn option[value="NO"]').attr("selected",null);
	}
	if(me.value == "NO") {
		jQuery("#hide_nodisplay").hide();
		jQuery("#displayearnyes_info").hide();
		jQuery('#reward_display_earn option[value="YES"]').attr("selected",null);
		jQuery('#reward_display_earn option[value="NO"]').attr("selected","selected");
	}
}

//jQuery(document).on('click','#balance-tab #doaction',function(e){
jQuery(document).on('click','#balance-tab .action',function(e){
	var selected_option_t = jQuery("#bulk-action-selector-top option:selected").val();
	var selected_option_b = jQuery("#bulk-action-selector-bottom option:selected").val();
	if(selected_option_t != -1 || selected_option_b != -1)
	{
		var checked_ids = [];
		var allcheckbox = false;

		jQuery('.rd_checkbox_id').each(function(){
	       if(jQuery(this).prop('checked') == true)
	       {
		       var value = jQuery(this).val();
		       checked_ids.push(value);
		       allcheckbox = true;
	       }
	    });
	    jQuery(".allids").val(checked_ids.toString());
	    if(allcheckbox == true)
	    {
	    	jQuery("#update_bal_Modal").show();	
	    }
	    
	}
});	
jQuery(document).on('click','.bal_modal_close',function(e){	
	jQuery("#update_bal_Modal").hide();
});
jQuery(document).on('click','#apply_button',function(e){
 	var error_msg = [];
	error_msg[0] = ' ';
	error_msg[1] = 'This is a required field';
	if(jQuery.trim(jQuery("#updatepoint_input").val()) === '')
	{
		$flag = 1;
		jQuery(".cover_per_txt").css( 'border','1px solid red');
		jQuery("#updatepoint_txt").text(error_msg[$flag]).css('color','red');
		return false;
	}
	return true;
});
