var url_admin	= js_var.site_url;
var post_type   = js_var.post_type;
jQuery(document).ready(function(){

	jQuery("#closebackimage").css("display","none");
	jQuery("#artclosebackimage").css("display","none");
	var one=jQuery("#faq_category_image").val();
	if(one !== "")
	{
		jQuery("#closebackimage").css("display","block");
	}
	var two=jQuery("#faq_art_image").val();

	if(two !== "")
	{
		jQuery("#artclosebackimage").css("display","block");
	}
	
	jQuery(".txt_required").keyup(function(){
	var select_category	= jQuery("#select_category").children("option:selected").val();
	});

	jQuery(".txt_required").change(function(){
	var select_category	= jQuery("#select_category").children("option:selected").val();
	});

	if (post_type == 'faq_article') {
		jQuery('select option[value=draft]').text('Disable');
		jQuery('select option[value=publish]').text('Enable');
		jQuery('.post-state').each(function(index,value){
		jQuery(".post-state").text('Disable');
		});

		if(jQuery.trim(jQuery("#post-status-display").text())=="Draft")
		{
			jQuery("#post-status-display").text('Disable');
		}
		else if(jQuery.trim(jQuery("#post-status-display").text())=="Published")
		{
			jQuery("#post-status-display").text('Enable');
		}
		jQuery("#save-post").val('Save as Disable');
	}

	jQuery("#faq_category_icon").change(function() {
		var file = jQuery("#faq_category_icon")[0].files[0];
		var path = url_admin+'/wp-content/plugins/product-questions-by-aheadworks/admin/language/'+file.name;
		var name = file.name;
		var filext = name.substring(name.lastIndexOf(".")+1);
		jQuery('#faq_category_image').val('');
		if(file)
		{
			if(filext == "jpeg" || filext == "jpg" || filext == "png" || filext == "bmp" || filext == "gif" || filext == "JPEG" || filext == "JPG" || filext == "PNG" || filext == "BMP" || filext == "GIF")
			{
				var reader = new FileReader();
				reader.readAsDataURL(file);
				reader.onload = function(e) {
					jQuery("#uploadedimage").text("");
					jQuery('#faq_category_image').attr('data-value',e.target.result);
					jQuery('#faq_category_image').val(path);
					jQuery('#faq_category_display-image').attr('src',e.target.result);
					jQuery("#faq_category_display-image").attr('image-name',name);
					jQuery("#closebackimage").show();
					jQuery("#faq_category_display-image").show();
				};
			}
			else
			{
				alert("Invalid file type !");
				return false;
			}	
		}
	});

	jQuery("#closebackimage").click(function(){
		
		var url = js_var.site_url;
		var postid = jQuery(this).attr('post-id');
		request_Url = url+'/wp-admin/admin-ajax.php?action=aw_faq_category_image_delete&postid='+postid;
		jQuery.ajax({
			url: request_Url,
			type:'POST',
			success:function(data){
				if(data!=0)
				alert(data);
                jQuery("#faq_category_icon").css("display","block");
				jQuery("#faq_category_icon").val("");
				jQuery("#uploadedimage").text("");
				jQuery("#faq_category_image").val('');
				jQuery("#faq_category_image").attr('data-value','');
				jQuery("#faq_category_display-image").attr('src','');
				jQuery("#faq_category_display-image").hide();
				jQuery("#closebackimage").hide();

			},
			error: function(errorThrown){
				console.log(errorThrown);
			}
		});

		jQuery("#faq_category_display-image").attr('image-name','');
	});
	
	jQuery("#faq_article_icon").change(function() {
		var file = jQuery("#faq_article_icon")[0].files[0];
		var path = url_admin+'/wp-content/plugins/product-questions-by-aheadworks/admin/language/'+file.name;
		var name = file.name;
		var filext = name.substring(name.lastIndexOf(".")+1);
		jQuery('#faq_art_image').val('');
		if(file)
		{
			if(filext == "jpeg" || filext == "jpg" || filext == "png" || filext == "bmp" || filext == "gif" || filext == "JPEG" || filext == "JPG" || filext == "PNG" || filext == "BMP" || filext == "GIF")
			{
				var reader = new FileReader();
				reader.readAsDataURL(file);
				reader.onload = function(e) {
					jQuery("#artuploadedimage").text("");
					jQuery('#faq_art_image').attr('data-value',e.target.result);
					jQuery('#faq_art_image').val(path);
					jQuery('#faq_art_display-image').attr('src',e.target.result);
					jQuery("#faq_art_display-image").attr('image-name',name);
					jQuery("#artclosebackimage").show();
					jQuery("#faq_art_display-image").show();
				};
			}
			else
			{
				alert("Invalid file type !");
				return false;
			}	
		}
	});

	jQuery("#artclosebackimage").click(function(){		
		var url = js_var.site_url;
		var postid = jQuery(this).attr('post-id');
		console.log(postid);
		request_Url = url+'/wp-admin/admin-ajax.php?action=aw_faq_art_image_delete&postid='+postid;
		jQuery.ajax({
			url: request_Url,
			type:'POST',
			success:function(data){
				if(data!=0)				  
				alert(data);
				jQuery("#faq_article_icon").css("display","block");
				jQuery("#faq_art_icon").val("");
				jQuery("#artuploadedimage").text("");
				jQuery("#faq_art_image").val('');
				jQuery("#faq_art_image").attr('data-value','');
				jQuery("#faq_art_display-image").attr('src','');
				jQuery("#faq_art_display-image").hide();
				jQuery("#artclosebackimage").hide();

			},
			error: function(errorThrown){
				console.log(errorThrown);
			}
		});

		jQuery("#faq_art_display-image").attr('image-name','');
	});
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
function aw_faq_setting_submit(event){
	var specialChars 	= "<>#&@!$%^*()_+[]{}?:;|'\"\\,.~`=";
	var art_slug	= jQuery.trim(jQuery(".faq_setting_article_url_suffix").val());
	for(i = 0; i < specialChars.length;i++){
		if(art_slug.indexOf(specialChars[i]) > -1){
			jQuery('.faq_setting_article_url_suffix_error').text('Special character not allowed').css('color','red');
			return false;
		} else {
			jQuery('.faq_setting_article_url_suffix_error').text('');	
		}
	}
	var cat_slug	= jQuery.trim(jQuery(".faq_setting_category_url_suffix").val());
	for(i = 0; i < specialChars.length;i++){
		if(cat_slug.indexOf(specialChars[i]) > -1){
			jQuery('.faq_setting_category_url_suffix_error').text('Special character not allowed').css('color','red');
			return false;
		} else {
			jQuery('.faq_setting_category_url_suffix_error').text('');	
		}
	}


}
function checkSpace(evt,allowed = '')
{
	
	evt = (evt) ? evt : window.event;

	var charCode = (evt.which) ? evt.which : evt.keyCode;
	
	if ( charCode != 32 )
	{
		return true;	
	}
	else
	{
		status = "This field space not allowed.";
		return false;
	}
	status = "";
	return true;

}
function checkNum(evt,allowed = '')
{
	 evt = (evt) ? evt : window.event;
	 
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if ((charCode > 64 && charCode < 91) || (charCode > 96 && charCode < 123) || charCode == 8)
	{
		return true;	
	}
	else
	{
		status = "This field character numbers only.";
		return false;
	}
	status = "";
	return true;

}
function aw_gc_checkItExp(evt,allowed = '')
{
	evt = (evt) ? evt : window.event;

	var charCode = (evt.which) ? evt.which : evt.keyCode;
	
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
function checkSpecialchar(evt,allowed = '')
{
	evt = (evt) ? evt : window.event;

	var charCode = (evt.which) ? evt.which : evt.keyCode;
	
	if ((charCode > 64 && charCode < 91) || (charCode > 96 && charCode < 123) || charCode == 8 ||(charCode > 47 && charCode < 57))
	{
		return true;	
	}
	else
	{
		status = "This field character and  numbers only.";
		return false;
	}
	status = "";
	return true;

}

function faqcheckform() {
	var faq_name = jQuery('.faq_setting_name').val();
	var faq_slug = jQuery('.faq_setting_slug').val();
	var emailList = jQuery('.faq_setting_email_address').val();
	var emails = emailList.replace(/\s/g,'').split(",");
	var regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	var specialChars = "<>#&@!$%^*()_+[]{}?:;|'\"\\,.~`=";
	var faq_slug = jQuery.trim(jQuery(".faq_setting_slug").val());
	for(i = 0; i < specialChars.length;i++) {
		if(faq_slug.indexOf(specialChars[i]) > -1){
			jQuery('.faq_setting_slug_error').text('Special character not allowed in slug field').css('color','red');
			return false;
		} else {
			jQuery('.faq_setting_slug_error').text('');
		}
	}

	if(faq_name.trim()==""){
		jQuery('span.faq_setting_name_error').text('Field is required').css({'color':'red'});
		return false;	
	}
	else{
		jQuery('span.faq_setting_name_error').text('Field  is required').css({'display':'none'});
	}

	if(faq_slug.trim()==""){
		jQuery('span.faq_setting_slug_error').text('Field is required').css({'color':'red'});
		return false;	
	}
	else{
		jQuery('span.faq_setting_slug_error').text('Field  is required').css({'display':'none'});
	}

	for (var i = 0; i < emails.length; i++) {
		if (emails[i] == "") {
			jQuery('span.faq_setting_email_address_error').text('Field  is required').css({'color':'red'});
	        return false;
		}

	    if(! regex.test(emails[i]) && emails[i] != "" ){
	        jQuery('span.faq_setting_email_address_error').text('Please enter a valid comma seprated email id').css({'color':'red'});
	        return false;
	    }
	   
	}
	

}
function faqcheckcategory() {
	var cat_name = jQuery('.faq_category_name').val();

	if(cat_name.trim()==""){
		jQuery('span.faq_category_name_error').text('Field is required').css({'color':'red'});
		return false;	
	}
	else{
		jQuery('span.faq_category_name_error').text('Field  is required').css({'display':'none'});
	}
}


jQuery.urlParam = function(name){
		var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
		if(results!=null)
			return results[1] || 0;
		else
			return 0;
}

jQuery(window).load(function(){

	jQuery('#filter-by-comment-type').append(jQuery("<option></option>").attr("value",'faq_comment').text("FAQ Comments"));
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

	jQuery("#title-prompt-text").html("Name");
	jQuery("#title").addClass("required");
	jQuery('#publish').click(function(e){
		var flag = 0;
		jQuery('.required').each(function(){
				if( jQuery.trim(jQuery( this ).val()) == '' )
				{
					e.preventDefault();
					jQuery(this).css('border' , '1px solid red');
					if(this.id != "title")
					{
						jQuery([document.documentElement, document.body]).animate({
							scrollTop: jQuery("#"+this.id).offset().top
						}, 1000);
					}
					flag = 0;
					return false;
				}
				else
				{
					jQuery(this).css( 'border' , '1px solid lightgrey' );
					flag = 1;
					return true;
				}
		});
		if(flag == 1)
		{
			jQuery('#save-post').val("Save as Disable");
		}
	});

	jQuery('#save-post').click(function(e){
		jQuery('#save-post').hide();
		jQuery('#save-post').val("Save as Disable");
	});

	jQuery('.cancel-post-status').click(function(e){
		jQuery('#save-post').val("Save as Disable");
	});

	jQuery('.save-post-status').click(function(e){
		jQuery('#save-post').val("Save as Disable");
	});
});
