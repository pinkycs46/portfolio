var url_admin		= rd_admin_js_var.site_url;
var ajax_url		= rd_admin_js_var.ajax_url;
var path			= rd_admin_js_var.path;
var host			= rd_admin_js_var.host;
var notimag_flag 	= false;
var exceedimag_size	= false;
var moreimgcounter 	= 0;
var imageid 		= 0;
var image_extension = ['tif','tiff','bmp','jpg','jpeg','gif','png'];

/*
var nonce_admin_qa	= rd_admin_js_var.rd_pq_admin_nonce;
var validemail_text 	= rd_admin_js_var.validemail_text;
*/

jQuery(document).ready(function(){

	reviewip_url=jQuery( "tbody#the-comment-list  tr.review td.column-author a:last-child").attr('href')
	reviewip_url+='&comment_type=review';
	jQuery( "tbody#the-comment-list  tr.review td.column-author a:last-child").attr('href',reviewip_url);

 	if('editcomment'==jQuery.urlParam('action')){
		//jQuery( "form#post").attr( "enctype", "multipart/form-data").attr( "encoding", "multipart/form-data" )
		jQuery( "form#post").attr( "enctype", "multipart/form-data");
	}
	if('review'==jQuery.urlParam('comment_type')){
		jQuery('title').html("Reviews");
		jQuery('#adv-settings fieldset.metabox-prefs').html(jQuery('#adv-settings fieldset.metabox-prefs').html().replace('In response to','Product'));
		jQuery('#adv-settings fieldset.view-mode').remove();

		jQuery( "#wpbody .wrap h1.wp-heading-inline" ).text('Reviews');	
		jQuery( "#comments-form p.search-box input#search-submit").val('Search');
		jQuery( "#filter-by-comment-type").append('<option selected=selected value="review">Review</option>');
		jQuery( "#filter-by-comment-type").hide();
		jQuery( "#post-query-submit").hide();

		jQuery('ul#adminmenu li#menu-comments').removeClass('current');
		jQuery('ul#adminmenu li#toplevel_page_advanced-review').addClass('current');

		jQuery('ul#adminmenu li#toplevel_page_advanced-review').removeClass('wp-has-current-submenu');
		jQuery('ul#adminmenu li#toplevel_page_advanced-review').addClass('wp-has-current-submenu wp-menu-open');
		jQuery('ul.wp-submenu-wrap li.wp-first-item').addClass('current');

		var notfond = jQuery('tbody#the-comment-list tr.no-items').text();
		jQuery('tbody#the-comment-list tr.no-items td.colspanchange').text(notfond.replace("comments", "review"));

		jQuery('span#editlegend').text("Edit Review");
		jQuery('span#replyhead').text('Reply to Review');
		jQuery('span#addhead').text('Add new Review');
		jQuery('form div h1').text('Edit Review');

		jQuery('div.response-links span.post-com-count-wrapper').hide();
	}
	if('editcomment'===jQuery.urlParam('action')) {
		jQuery( "button#show-settings-link").hide();
	}

	//jQuery('#aw_ar_file').hide();	
	jQuery('body').on('change', '#aw_ar_file', function(){			
		var images_str 		= '';
		var invalidimg_count= 0;
		var invalid_string 	= '';
		var fileExtension 	= [];
		var allowfile_ext 	= jQuery.trim(jQuery('#aw_ar_allowed_image_ext').val());
		var allowfile_size	= jQuery.trim(jQuery('#aw_ar_allowed_image_size').val());
		fileExtension 		= allowfile_ext.split(',');
		var $preview 		= jQuery('#preview').empty();
		if(this.files.length>0)
		{
			for(i=0;i<this.files.length;i++){
				moreimgcounter = i;
				file = this.files[i];
				name = file.name
				filext = name.substring(name.lastIndexOf(".")+1);
				if(jQuery.inArray(filext,image_extension)>0 )
				{
					if(Math.round(file.size/(1024*1024)) <= allowfile_size){
						if (this.files && this.files[i]) {
			                moreimgcounter += 1; //increementing global variable by 1
							console.log(this.files[i]);
							var z = moreimgcounter - 1;
			                var x = jQuery(this).parent().find('#aw_ar_previewimg' + z).remove();
						    var reader 		= new FileReader();
			                reader.onload 	= imageIsLoaded;
			                reader.readAsDataURL(this.files[i]);
		            	} else {
		            		exceedimag_size = true;
		            		invalid_string +=  name+', ' ;
		            	} 	
	            	} else {
		            		exceedimag_size = true;
		            		invalid_string +=  name+', ' ;
		            	}
				} else {
					notimag_flag= true;
					invalid_string +=  name+', ' ;
					invalidimg_count++;
				}
			}
			if(notimag_flag)
			{	
				 
				jQuery('#aw_ar_file').val('');
				alert(invalid_string +"  invalid file type !");
				return false;
			}
			if(exceedimag_size)
			{	
				 
				jQuery('#aw_ar_file').val('');
				alert(invalid_string +"  files size not allowed !");
				return false;
			}	
		}
	});

	//To preview image 
	function imageIsLoaded(e) {
        if(notimag_flag){
        	jQuery('#preview').empty();
        } else{
        	console.log(imageid++);
        	jQuery('#preview').append(jQuery('<img/>',{id: 'image'+imageid}).attr('src', e.target.result).attr('width','20%').attr('height','20%')).append(jQuery("<img/>", {id: 'aw_ar_img'+imageid, class:'aw_ar_close', src: url_admin+'/wp-content/plugins/advanced-reviews-by-aheadworks/admin/images/x.png', alt: 'delete', onclick:'aw_ar_deleteimage_before_save('+imageid+')'}))
        }
    };

    //To add new input file field dynamically, on click of "Add More Files" button below function will be executed
    jQuery('#aw_ar_add_more').click(function() {
    	jQuery(this).attr('disabled','disabled');
        jQuery(this).before(jQuery("<div/>", {id: 'aw_ar_filediv'}).fadeIn('slow').append(
                jQuery("<input/>", {name: 'aw_ar_file[]', type: 'file', id: 'aw_ar_file'}),        
                jQuery("<br/><br/>")
                ));
    });

});	


var getQueryString = function ( field, url ) {
	var href = url ? url : window.location.href;
	var reg = new RegExp( '[?&]' + field + '=([^&#]*)', 'i' );
	var string = reg.exec(href);
	return string ? string[1] : null;
};

function aw_ar_checkIt(evt,allowed = '')
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
	/*if(evt.target.value.length>1) {
		status = "";
		status = "This field accepts on less than 10 number.";
		 
		return false;	
	}*/
	 
	/*if(Number(evt.target.value)>2) {
		status = "";
		status = "Only below 2 Mb upload file size allowed.";
		alert(status);
		return false;	
	}*/
	status = "";
	return true;
}

/*function aw_ar_checkIt_slug(evt,classname)
{
	evt = (evt) ? evt : window.event;
	 
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if(charCode === 32 || charCode === 96 || ((charCode > 8 && charCode < 14) || (charCode > 31 || charCode > 47) || (charCode > 58 || charCode > 95)|| (charCode > 122 || charCode > 254)))
	{
		jQuery('.'+classname).css( 'border','1px solid red'); 
		jQuery('.'+classname+'_msg').text('Special character not allowed');
		return false;	
	} 
	return true;
}*/

function aw_ar_checkIt_text(evt)
{
	evt = (evt) ? evt : window.event;
	 
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if(charCode === 32 || ((charCode > 48 && charCode < 57) ||(charCode > 64 && charCode < 91) || (charCode > 96 || charCode > 123)))
	{
		if(evt.target.value.length>=20) {
			status = "This field accepts only 20 character.";
			
			return false;	
		}
		return true;	
	} else {
		status = "This field accepts number,characher only.";
		
		return false;
	}
	status = "";
	return true;
}

/*function aw_ar_checkIt_textarea(evt)
{
	evt = (evt) ? evt : window.event;
	 
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	alert(evt.target.value.length);
	if(evt.target.value.length>=100) {
		status = "This field accepts only 100 character.";
		alert(status);
		return false;	
	}
	status = "";
	return true;
}*/

/*function aw_ar_checkIt_image(evt)
{
	evt = (evt) ? evt : window.event;
	 
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if(charCode === 32 || charCode === 44 || ((charCode > 64 && charCode < 91) || (charCode > 96 || charCode > 123)))
	{
		if(evt.target.value.length>=20) {
			status = "This field accepts only 20 character.";
			alert(status);
			return false;	
		}
		return true;	
	} else {
		status = "This field accepts comma and characher only.";
		alert(status);
		return false;
	}
	status = "";
	return true;
}*/

jQuery.urlParam = function(name){
		var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
		if(results!=null)
			return results[1] || 0;
		else
			return 0;
}

jQuery(window).load(function() {
	/*jQuery('#filter-by-comment-type').append(jQuery("<option></option>").attr("value",'q_and_a').text("Q & A"));
	var action = jQuery.urlParam('comment_type'); // name
	jQuery('#filter-by-comment-type option[value='+action+']').attr('selected','selected');*/

	/*jQuery(".reply .comment-inline.button-link").click(function(){
		var classes = jQuery(this).closest('tr').attr('class');
		var id 		= jQuery(this).data('comment-id');
		classes 	= classes.split(' ');
		if (classes.length>0) {
			jQuery(".save #replybtn").removeClass();
			jQuery(".comment-reply").append('<input type="hidden" value="'+classes[0]+'" class="comment_type" name="comment_type"/>');
			jQuery(".save #replybtn").addClass('comment-'+id);
		}
	});*/

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

/*	jQuery("#rd_setting_form").submit(function(e){
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
	});*/

 	
 	var tab = jQuery.urlParam('tab');
 	if(tab =='aw-ar-emails'){
 		var i, tabcontent, tablinks;
		tabcontent = document.getElementsByClassName("tabcontent");
		for (i = 0; i < tabcontent.length; i++)
		{
			tabcontent[i].style.display = "none";
		}
		jQuery( ".tablinks" ).removeClass( "active" );
		jQuery( ".tab button" ).last().addClass( "active" );
		document.getElementById('aw_ar_email-setting-tab').style.display = "block";
 	}
});

function check_nothelp_help_value()
{
	flag = 1;

	jQuery('.aw_ar_txt_required').each(function(index) {
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
				scrollTop: jQuery(".aw_ar_txt_required").offset().top
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

function ae_ar_not_allowed(evt){
	evt = (evt) ? evt : window.event;
	 
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if(charCode === 32)
	{
	    return false;
	}
}
 
function aw_ar_email_templvalidateForm() {
	var admin_template 	= ['Admin Email','Abuse Report Email','Critical Report Email'];
	var flag 			= false;
	var emailList 		= jQuery.trim(jQuery('.aw_ar_emaillist').val());
	var emails 			= emailList.replace(/\s/g,'').split(",");
	var emailsubject	= jQuery.trim(jQuery('.aw_ar_mailsubject').val());
	var email_heading 	= jQuery.trim(jQuery('.aw_ar_email_heading').val());
	var additional_content = jQuery.trim(jQuery('.aw_ar_additional_content').val());
	var emailformname 	= jQuery.trim(jQuery('.emailformname').val());
	var regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	
	 
	if(jQuery.inArray(emailformname,admin_template)>0 ){
		if (emailList == "") {
			jQuery('span.aw_ar_emaillist').css( 'border','1px solid red');
			jQuery('span.aw_ar_emaillist_msg').text('Email id is required').css({'color':'red'});
	    	flag = true;
		} else{
			for (var i = 0; i < emails.length; i++) {
				if(! regex.test(emails[i]) ) {
					jQuery('span.aw_ar_emaillist').css( 'border','1px solid red');
					jQuery('span.aw_ar_emaillist_msg').text('Please enter a valid comma seprated email id').css({'color':'red'});
					flag = true;
					break;
				} else {
					jQuery('span.aw_ar_emaillist').css( 'border','');
					jQuery('span.aw_ar_emaillist_msg').text('');
				}
			}
		}	
	}
	

	if(emailsubject == ""){
		jQuery('span.aw_ar_mailsubject').css( 'border','1px solid red');
		jQuery('span.aw_ar_mailsubject_msg').text('Subject is required').css({'color':'red'});
	    flag = true;
	} else {
		jQuery('span.aw_ar_mailsubject').css( 'border','');
		jQuery('span.aw_ar_mailsubject_msg').text('');	
	}

	if(email_heading == ""){
		jQuery('span.aw_ar_email_heading').css( 'border','1px solid red');
		jQuery('span.aw_ar_email_heading_msg').text('Email heading is required').css({'color':'red'});
	    flag = true;
	} else {
		jQuery('span.aw_ar_email_heading').css( 'border','');
		jQuery('span.aw_ar_email_heading_msg').text('');
	}
	
	if(additional_content == ""){
		jQuery('span.aw_ar_additional_content').css( 'border','1px solid red');
		jQuery('span.aw_ar_additional_content_msg').text('Additional content is required').css({'color':'red'});
	    flag = true;
	} else {
		jQuery('span.aw_ar_additional_content').css( 'border','');
		jQuery('span.aw_ar_additional_content_msg').text('');
	}

	if(flag == true){
		return false;
	}
	return true;
}

function awar_deleteimage(imageid,reviewid='') {
	jQuery("#ar_aw_preview"+imageid).remove();
	request_Url = url_admin+'/wp-admin/admin-ajax.php?action=aw_ar_review_image_delete&reviewid='+reviewid+'&imageid='+imageid;
	jQuery.ajax({
	url: request_Url,
	type:'POST',
		success:function(data) {
			  alert(data);
		},
		error: function(errorThrown){
			console.log(errorThrown);
		}
	});
}

function aw_ar_deleteimage_before_save(imageid){
	jQuery('#image'+imageid).remove();
	jQuery('#aw_ar_img'+imageid).remove();
}

function aw_ar_show_lightbox(id){
	jQuery('.gallery-'+id+' a.ar_aw_light').lightbox(); 
}

function handleSelectChange(values, id){
	jQuery('#'+id).removeClass();
	if('yes'==values){
		jQuery('#'+id).show();
	} else {
		jQuery('#'+id).hide();
	}
}

function aw_ar_setting_submit(event){
	var specialChars 	= "<>@!$%^&*()_+[]{}?:;|'\"\\,./~`=";
	var image_extension = ['tif','tiff','bmp','jpg','jpeg','gif','png'];
	var flag 			= false;
	var allowfileupload = jQuery('#aw_ar_isattach_file').find(":selected").text();
	var maxfile_size 	= jQuery.trim(jQuery(".aw_ar_max_filesize").val());
	var allowfile_ext 	= jQuery.trim(jQuery(".aw_ar_allowfile_extensions").val());	
	var reviewpage_slug	= jQuery.trim(jQuery(".aw_ar_reviewpage_endppoint").val());
	if(allowfileupload == 'Yes' ) {

	 	if(maxfile_size == '') {
			jQuery('.aw_ar_max_filesize').css( 'border','1px solid red'); 
			jQuery('.aw_ar_max_filesize_msg').text('Max upload file size is required field').css('color','red');
			flag = true;
	 	}else {
	 		jQuery('.aw_ar_max_filesize_msg').text('');
	 		if(parseInt(maxfile_size)>5){
	 			jQuery('.aw_ar_max_filesize').css( 'border','1px solid red'); 
	 			jQuery('.aw_ar_max_filesize_msg').text('Max 5 MB should be there').css('color','red');
	 			flag = true;
	 		} else{
	 			jQuery('.aw_ar_max_filesize').css( 'border',''); 	
	 			jQuery('.aw_ar_max_filesize_msg').text('');
	 		}
	 		
	 	}
	 	if(allowfile_ext == '') {
			jQuery('.aw_ar_allowfile_extensions').css( 'border','1px solid red'); 
			jQuery('.aw_ar_allowfile_extensions_msg').text('Allow file extensions is required field').css('color','red');
			flag = true;
	 	}else {
 			var extensions = allowfile_ext.split(',');
			if(extensions.length>0){
				var formates= '';
				extensions.forEach(function(values){
					formates =values.trim();
					if(image_extension.indexOf(formates)== -1 ) {
						jQuery('.aw_ar_allowfile_extensions').css( 'border','1px solid red'); 
						jQuery('.aw_ar_allowfile_extensions_msg').text('Invalid image extensions').css('color','red');
						flag =true;
					}
				});
			} else if(image_extension.indexOf(allowfile_ext)== -1 ){
						jQuery('.aw_ar_allowfile_extensions').css( 'border','1px solid red'); 
						jQuery('.aw_ar_allowfile_extensions_msg').text('Invalid image extensions').css('color','red');
				flag =true;
			}else {
					jQuery('.aw_ar_allowfile_extensions_msg').text('');
 					jQuery('.aw_ar_allowfile_extensions').css( 'border',''); 
			}
	 	}
	 }

	for(i = 0; i < specialChars.length;i++){
		if(reviewpage_slug.indexOf(specialChars[i]) > -1){
			jQuery('.aw_ar_reviewpage_endppoint').css( 'border','1px solid red');
			jQuery('.aw_ar_reviewpage_endppoint_msg').text('Special character not allowed').css('color','red');
			flag = true;
			break;
		} else {
			jQuery('.aw_ar_reviewpage_endppoint').css( 'border','');
			jQuery('.aw_ar_reviewpage_endppoint_msg').text('');	
		}
	}


	
	if( flag == true) {
 		event.preventDefault()
 		return false;
 	} 
	return true;
}