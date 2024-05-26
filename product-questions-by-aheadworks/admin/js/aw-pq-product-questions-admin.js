var url_admin			= rd_admin_js_var.site_url;
var ajax_url			= rd_admin_js_var.ajax_url;
var path				= rd_admin_js_var.path;
var host				= rd_admin_js_var.host;
var nonce_admin_qa		= rd_admin_js_var.rd_pq_admin_nonce;
var validemail_text 	= rd_admin_js_var.validemail_text;
var required_email 		= rd_admin_js_var.required_email;
var invalid_file_type 	= rd_admin_js_var.invalid_file_type; 
var default_lan_applied	= rd_admin_js_var.default_language_applied; 

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

function aw_pq_checkIt(evt,allowed = '')
{
	evt = (evt) ? evt : window.event;
	 
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if(charCode === 46 && allowed == true)
	{
		return true;	
	}
	if(charCode > 31 && (charCode <= 47 || charCode > 57))
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
	jQuery("#pq_langauge_csv").change(function() {
		var file = jQuery("#pq_langauge_csv")[0].files[0];
		var path = url_admin+'/wp-content/plugins/product-questions-by-aheadworks/admin/language/'+file.name;
		var name = file.name;
		var filext = name.substring(name.lastIndexOf(".")+1);
		
		if(file)
		{
			if(filext != "csv")
			{
				jQuery('#pq_langauge_csv').val('');
				alert(invalid_file_type);
				return false;
			}	
		}
	});

});
jQuery(window).load(function() {
	jQuery('#filter-by-comment-type').append(jQuery("<option></option>").attr("value",'q_and_a').text("Q & A"));
	var action = jQuery.urlParam('comment_type'); // name
	jQuery('#filter-by-comment-type option[value='+action+']').attr('selected','selected');

	jQuery(".reply .comment-inline.button-link").click(function(){
		var classes = jQuery(this).closest('tr').attr('class');
		var id 		= jQuery(this).data('comment-id');
		classes 	= classes.split(' ');
		if (classes.length>0) {
			jQuery(".save #replybtn").removeClass();
			jQuery(".comment-reply").append('<input type="hidden" value="'+classes[0]+'" class="comment_type" name="comment_type"/>');
			jQuery(".save #replybtn").addClass('comment-'+id);
		}
	});

	jQuery(".rd_text_helpful , .rd_text_nothelpful").keyup(function(e){
		if(e.which == 13){
			check_nothelp_help_value();
		}
	});

	jQuery("#submitcomment #publishing-action #save").click(function(event){
		if(jQuery('.pinned-qa-detail').length)
		{
			if(check_nothelp_help_value() == false)
			{
				event.preventDefault();
			}
		}
	});

	jQuery("#rd_setting_form").submit(function(e){
		var days_val = jQuery.trim(jQuery(".rd_setting_cookie_days").val());

		if(days_val == "")
		{
			jQuery('.rd_setting_cookie_days').css( 'border','1px solid red'); 
			jQuery('.rd_setting_cookie_days_error').text('This is a required field');
			return false;
		}
		else if(days_val.startsWith("0"))
		{
			jQuery('.rd_setting_cookie_days').css( 'border','1px solid red'); 
			jQuery('.rd_setting_cookie_days_error').text('Enter a number greater than 0 in this field');
			return false;
		}
		else
		{
  			jQuery(".rd_setting_cookie_days").css( 'border','');
			return true;
  		}
	});

	jQuery(".pq_replycolor_reset").click(function(){
		jQuery('#choose_adminreply_color').val('');
		jQuery('#choose_adminreply_color').css({'background-color':''});
		jQuery('#adminreply_color').val('');
	})
	jQuery(".pq_numbercolor_reset").click(function(){
		jQuery('#choose_number_color').val('');
		jQuery('#choose_number_color').css({'background-color':''});
		jQuery('#pqnumbercolor').val('');
	})
 	
 	var tab = jQuery.urlParam('tab');
 	if(tab =='emails'){
 		var i, tabcontent, tablinks;
		tabcontent = document.getElementsByClassName("tabcontent");
		for (i = 0; i < tabcontent.length; i++)
		{
			tabcontent[i].style.display = "none";
		}
		jQuery( ".tablinks" ).removeClass( "active" );
		jQuery( ".tab button" ).last().addClass( "active" );
		document.getElementById('email-setting-tab').style.display = "block";
 	}
});

function check_nothelp_help_value()
{
	flag = 1;

	jQuery('.pq_txt_required').each(function(index){
		var val_chk 	= jQuery.trim(jQuery(this).val());
		var class_name 	= jQuery(this).attr('class');
		class_name 		= class_name.split(' ');
		class_name 		= class_name[0];

		if(val_chk == "")
		{
			jQuery(this).css( 'border','1px solid red');
			jQuery('.'+class_name+'_error').text('This is a required field');
			flag = 0;
		}
		else if(val_chk.startsWith("0") && val_chk != "0")
		{
			jQuery(this).css( 'border','1px solid red');
			jQuery('.'+class_name+'_error').text('Enter a number only 0 or greater than 0 in this field');
			flag = 0;
		}
		else
		{
			jQuery(this).css( 'border','');
			jQuery('.'+class_name+'_error').text('');
		}
	});

	if(flag == 0)
	{
		jQuery([document.documentElement, document.body]).animate({
				scrollTop: jQuery(".pq_txt_required").offset().top
		}, 1000);
		return false;
	}
	else
	{
		return true;
	}
}

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

 
function checkemaillist() {
	var emailList = jQuery('.aw_pq_emaillist').val();
	var emails = emailList.replace(/\s/g,'').split(",");
	var regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

	for (var i = 0; i < emails.length; i++) {
		if (emails[i] == "") {
			jQuery('span.error_msg').text(required_email).css({'color':'red'});
	        return false;
		}
	    if(! regex.test(emails[i])){
	        jQuery('span.error_msg').text(validemail_text).css({'color':'red'});
	        return false;
	    }
	}
}

function aw_pq_defaultlang(e)
{
	request_Url = url_admin+'/wp-admin/admin-ajax.php';//?action=aw_pq_default_lang_setting';
	jQuery.ajax({
	url: request_Url,
	data:{action:'aw_pq_default_lang_setting',pq_nonce_admin_ajax: nonce_admin_qa},
	type:'POST',
		success:function(data) {
			  alert(default_lan_applied);
			  location.reload();
		},
		error: function(errorThrown){
			console.log(errorThrown);
		}
	});
}
