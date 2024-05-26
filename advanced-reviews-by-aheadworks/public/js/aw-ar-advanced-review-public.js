var url    				= js_aw_ar_var.site_url;
var aw_ar_front_nonce  	= js_aw_ar_var.aw_ar_front_nonce;
var notimag_flag 		= false;
var exceedimag_size		= false;
var imageid 			= 0;
var image_extension 	= ['bmp','jpg','jpeg','gif','png'];
var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
 

jQuery(window).load(function(){
	jQuery(".aw_ar_review_frm #author").attr('required');
});
jQuery(document).ready(function() {

	var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('#');
	var hashes_scroll = window.location.href.slice(window.location.href.indexOf('?') + 1).split('+');

	if(hashes[hashes.length-1].length>0)
	{
		var tab = hashes[hashes.length-1];
		if('tab-reviews'==tab)
		{
			jQuery('ul.tabs li').removeClass('active');
			jQuery('ul.tabs #tab-title-reviews').addClass('active');

			jQuery(".woocommerce-Tabs-panel").hide();
			jQuery("#"+tab).show();
		}
	}

	if(hashes_scroll[hashes_scroll.length-1].length>0)
	{
		var id = hashes_scroll[0].split('-');

		var tab_scroll = hashes_scroll[hashes_scroll.length-1];
		if('tab-reviews'==tab_scroll) {
			jQuery('ul.tabs li').removeClass('active');
			jQuery('ul.tabs #tab-title-reviews').addClass('active');

			jQuery(".woocommerce-Tabs-panel").hide();
			jQuery("#"+tab_scroll).show();

			jQuery('html, body').animate({ 
				scrollTop: jQuery('#div-comment-'+id[2]).offset().top
			}, 1000);
		}
	}
	var myfile= [];
	jQuery('body').on('change', '#aw_ar_file', function(){	
		

		var filename 		= [];
		var imageid 		= -1;		
		var invalid_string	= '';
		var invalidimg_count= 0;
		var $preview 		= jQuery('#preview').empty();
		var allowfile_size	= jQuery.trim(jQuery('#aw_ar_max_filesize').val());
		myfile = this.files;
		/*if(isSafari && this.files.length>1) {
			alert('Single file allowed in safari');
			jQuery('#aw_ar_file').val('');
			return false;
		}*/
		if(this.files.length>0)
		{
			for(i=0;i<this.files.length;i++){
				moreimgcounter = i;
				file = this.files[i];
				name = file.name
				filext = name.substring(name.lastIndexOf(".")+1);
				filext = filext.trim();
				if(jQuery.inArray(filext,image_extension)>0 ) 
				{	 
					if(Math.round(file.size/(1024*1024)) <= allowfile_size){
						if (this.files && this.files[i]) {
			                moreimgcounter += 1; //increementing global variable by 1
			                var z = moreimgcounter - 1;
			                if(isSafari) {
			                	imageid++;
			                	jQuery('#preview').append('<p id="aw_ar_individal_file-'+imageid+'">'+name+'&nbsp;<img src="'+url+'/wp-content/plugins/advanced-reviews-by-aheadworks/admin/images/x.png" onclick="aw_ar_deleteimage_before_save('+imageid+')" /></p>');
			                } else {
			                	var reader 		= new FileReader;
								reader.onload 	= function(e) {
			                 		imageid++;
			                 		jQuery('#preview').append(jQuery('<img/>',{id: 'image'+imageid}).attr('src', e.target.result).attr('width','20%').attr('height','20%')).append(jQuery("<img/>", {id: 'aw_ar_img'+imageid, class:'aw_ar_close', src: url+'/wp-content/plugins/advanced-reviews-by-aheadworks/admin/images/x.png', alt: 'delete', onclick:'aw_ar_deleteimage_before_save('+imageid+')'}))
				                }
				                reader.readAsDataURL(this.files[i]);
			                }

							filename.push(name); 
			                notimag_flag = false;
			                
		            	}	
	            	} else {
		            		exceedimag_size = true;
		            		invalid_string +=  name+', ' ;
		            	}
				} else {
					notimag_flag= true;
					invalid_string += name + ', ';
					invalidimg_count++;
				}
			}
			jQuery('#aw_ar_total_files').val(filename.toString());
			
			if(notimag_flag)
			{
				jQuery('#aw_ar_file').val('');
				alert(invalid_string +" image with invalid file type !");
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

    /* #################  */
	jQuery(".aw_ar_like_dislike_img").click(function(){

		var site_url = url+'/wp-admin/admin-ajax.php';
		var comment_id = jQuery(this).data('review-id');
		var trigger_type= jQuery(this).data('trigger-type');
		var user_id = jQuery(this).data('user-id');
		var image_id = jQuery(this).attr('id');

		if (!jQuery('.aw_ar_like_dislike_img').is('[disabled=disabled]')) {
			jQuery(this).parent('div').parent('div').addClass("aw_ar_gif_loader");
			jQuery('.aw_ar_like_dislike_img').attr('disabled',true);
			jQuery.ajax({
				url: site_url,
				type: 'POST',
				data: {action:"aw_advanced_review_like_dislike", comment_id:comment_id, trigger_type:trigger_type, user_id:user_id, aw_ar_nonce_ajax: aw_ar_front_nonce},
				success:function(data) {
					if(data.length>0)
					{
						var obj = jQuery.parseJSON( data );
						jQuery('#'+image_id).attr('src',obj.changed_image);
						jQuery('#helpfulcount-'+comment_id).text(obj.rd_helpful);
						if (0 != obj.rd_not_helpful) {
							//var nothelpful = "-"+obj.rd_not_helpful;
							var nothelpful = obj.rd_not_helpful;
						} else {
							var nothelpful = obj.rd_not_helpful;
						}
						jQuery('#nothelpfulcount-'+comment_id).text(nothelpful);
						jQuery('#rd-helpful-'+comment_id).attr('src',obj.rd_helpful_image);
						jQuery('#rd-not-helpful-'+comment_id).attr('src',obj.rd_not_helpful_image);
						jQuery('.aw_ar_like_dislike_img').removeAttr('disabled');
						jQuery('.aw_ar_like_dislike_img').parent('div').parent('div').removeClass("aw_ar_gif_loader");
					}
				},
				error: function(errorThrown){
				console.log(errorThrown);
				}
			});
		}
	});

	/* Abusement popup open*/
	jQuery(".aw_ar_abuse_img").click(function() {
		var id = jQuery(this).attr('id');
		jQuery("#aw_ar_abuse_Modal-"+id).show();
	});
	/**/
	jQuery(window).click(function(event) {
		var msg_modal = document.getElementById("aw_ar_message_Modal");
		if (event.target == msg_modal) {
			jQuery("#aw_ar_message_Modal").hide();
		}
		if (jQuery('.aw_ar_abuse_modal').is(':visible')) {
			var id = jQuery('.aw_ar_abuse_modal').attr('id');
			var abuse_modal = document.getElementById(id);
			if (event.target == abuse_modal) {
				jQuery("#"+id).hide();
			}
		}
	})
    
    jQuery(document).keydown(function(e) {
    	// ESCAPE key pressed
	    if (e.keyCode == 27) {
	        aw_ar_close_modal();
	    }
	});

	jQuery('#submit').click(function(){
		flag = false;
		emailflag = false;
		var author 		= jQuery.trim(jQuery("input#author").val());
		var email 		= jQuery.trim(jQuery("input#email").val());
		var comment		= jQuery.trim(jQuery("textarea#comment").val());
		var is_logged_in= jQuery.trim(jQuery("input#is_user_logged_in").val());
		var regex 	= /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
		if ("" === author && is_logged_in==0) {
			if ("" != comment) {	
				jQuery("#author").css( 'border','1px solid red');
			}
			flag = true;
		} else{
			jQuery("#author").css( 'border','');
		}

		if ("" === email  && is_logged_in==0 ) {
			if ("" != comment) {
				jQuery("#email").css( 'border','1px solid red');
			}
			flag = true;
		} else {
			 
				if(! regex.test(email)  && is_logged_in==0 ) {
					jQuery('#email').css( 'border','1px solid red');
					emailflag = true;
				} else {
					emailflag = false;
					flag = false;
					jQuery('#email').css( 'border','');
				}
			}

		if ("" === comment) {	
			jQuery("#comment").css( 'border','1px solid red');
			flag = false;
		} else {
			jQuery("#comment").css( 'border','');
		} 

		if(emailflag == true) {
			alert('Enter valid email id');	
			return false;
		}

		if(flag == true) {
			alert('Enter required field');	
			return false;
		}
	});

});


 	
//To preview image 
function imageIsLoaded(e) {

    if(notimag_flag){
    	jQuery('#preview').empty();
    } else{
    	jQuery('#preview').append(jQuery('<img/>',{id: 'image'+imageid}).attr('src', e.target.result).attr('width','20%').attr('height','20%')).append(jQuery("<img/>", {id: 'aw_ar_img'+imageid, class:'aw_ar_close', src: url+'/wp-content/plugins/advanced-reviews-by-aheadworks/admin/images/x.png', alt: 'delete', onclick:'aw_ar_deleteimage_before_save('+imageid+')'}))
    }
};


function aw_ar_askreview(position){
	 jQuery("#review_form").toggle('slow');	
	 if('aw_ar_write_review_bottom'==position) {
	 	jQuery('html,body').animate({scrollTop: jQuery("#comments").offset().top},'slow');
	 }
}

 
function aw_ar_apply_filter_ajax(obj, active, text1) {

	var data 		= [];
	var withimage 	= '';
	var verified 	= '';
	var starrate 	= ''
	var sortyby 	= '';
	var order	 	= 'desc';
	if(active == false){
		active = false;
	}
	if(text1 == ''){
		text1 = '';
	}
	if(active!=false)
	{
		order = jQuery("ul.aw_ar_ulsort li:nth-child("+active+")").hasClass('aw_ar_desc')? 'asc':'desc'
		
		if(jQuery('ul.aw_ar_ulsort li.active').hasClass('_ascend')){
			jQuery('ul.aw_ar_ulsort li.active').removeClass('_ascend');
		}
		if(jQuery('ul.aw_ar_ulsort li.active').hasClass('_descend')){
			jQuery('ul.aw_ar_ulsort li.active').removeClass('_descend');
		}
		jQuery('ul.aw_ar_ulsort li').removeClass('active');
		jQuery('ul.aw_ar_ulsort li:nth-child('+active+')').toggleClass('aw_ar_desc aw_ar_asc'); 
		jQuery('ul.aw_ar_ulsort li:nth-child('+active+')').toggleClass('_ascend _descend'); 
		jQuery("ul.aw_ar_ulsort li:nth-child("+active+")" ).addClass("active");
		sortyby = text1;
	} else {
		if(jQuery("ul.aw_ar_ulsort li").hasClass('active')){
			sortyby = jQuery("ul.aw_ar_ulsort li.active span").text().toLowerCase();
			order 	= jQuery("ul.aw_ar_ulsort li").hasClass('aw_ar_desc')? 'asc':'desc'
		}
	}

	jQuery("input:checkbox.buttontohref").each(function() {
		if(this.checked) {
			value = jQuery(this).val();
			if(value == 'aw-ar-reviewimage'){
				withimage = 'aw-ar-reviewimage';
			}
			if(value == 'verified'){
				verified = 'verified';
			}
		}
	});

	var star_rating_filter = jQuery(".aw_ar_rating_type").find("option:selected").val();
	if(star_rating_filter!='all'){
		starrate = star_rating_filter;
	} 
	var product_id 	= jQuery(".aw_ar_product_id").val();
	var checked_val = Object.assign({}, data);
	jQuery("#unfeaturedreview").html('');
	var site_url = url+'/wp-admin/admin-ajax.php?';
		jQuery.ajax({
			url: site_url,
			type: 'POST',
			data: {action: "aw_ar_get_filtered_review_ajax",product_id: product_id , withimage:withimage, verified:verified, starrate:starrate, sortyby:sortyby, order:order, aw_ar_nonce_ajax:aw_ar_front_nonce},
			success:function(data) {
				if(data){
					jQuery("#unfeaturedreview").html(data);
				}
			},
			error: function(errorThrown) {
				console.log(errorThrown);
			}
		});	

}

function aw_ar_deleteimage_before_save(imageid){
	
	var filename = []; 
	var jsonstr = '';
	obj = jQuery('#aw_ar_file')[0].files;
	for(i= 0 ;i<obj.length;i++){
		if(i!=imageid){
			filename.push(obj[i].name);
		}
	}		
	jQuery('#aw_ar_total_files').val(filename.toString());
	if(navigator.userAgent.indexOf("Safari") > -1) {
		jQuery('#aw_ar_individal_file-'+imageid).remove();
		jQuery('#aw_ar_img'+imageid).remove();
	} else {
		jQuery('#image'+imageid).remove();
		jQuery('#aw_ar_img'+imageid).remove();
	}
}

function aw_ar_absument(comment_id, product_id,user_id) {
	aw_ar_close_modal();
	jQuery('.aw_ar_abuseimge-'+comment_id).parent('div').parent('div').addClass("aw_ar_gif_loader");
	jQuery('.aw_ar_abuseimge-'+comment_id).attr('disabled',true);
	var site_url = url+'/wp-admin/admin-ajax.php?';
	jQuery.ajax({
		url: site_url,
		type: 'POST',
		data: {action: "aw_ar_abuse_on_review_ajax",comment_id:comment_id, product_id: product_id , user_id:user_id, aw_ar_nonce_ajax:aw_ar_front_nonce},
		success:function(data) {
			if(data) {
				jQuery('.aw_ar_abuseimge-'+comment_id).hide();
				jQuery('#loadersection'+comment_id).removeClass("rd_gif_loader");
				alert(data);
				jQuery('.aw_ar_abuseimge-'+comment_id).removeAttr('disabled');
				jQuery('.aw_ar_abuseimge-'+comment_id).parent('div').parent('div').removeClass("aw_ar_gif_loader");
			}
		},
		error: function(errorThrown) {
			console.log(errorThrown);
		}
	})
}

function aw_ar_toggle_comment_form(comment_id) {
	jQuery("#awarcommentform-"+comment_id).toggle('fast');
}
function aw_ar_submit_comment_on_review(e , comment_id, product_id) {
	$flag = 0;
	e = e || window.event;
    e.preventDefault();

	var nickname 	= jQuery("input#nickname_text-"+comment_id).val();
	var comment 	= jQuery("textarea#comment_text-"+comment_id).val();
	if ("" === nickname) {	
		jQuery("#nickname_text-"+comment_id).css( 'border','1px solid red');
		jQuery(".error_nickname-"+comment_id).text( 'This is a required field.').css( 'color','red');;
		$flag = 1; 
	} else{
		jQuery("#nickname_text-"+comment_id).css( 'border','');
		jQuery(".error_nickname-"+comment_id).text( '');
	}
	if ("" === comment) {	
		jQuery("#comment_text-"+comment_id).css( 'border','1px solid red');
		jQuery(".error_comment-"+comment_id).text( 'This is a required field.').css( 'color','red');
		$flag = 1; 
	} else {
		jQuery("#comment_text-"+comment_id).css( 'border','');
		jQuery(".error_comment-"+comment_id).text( '');
	} 

 	if(0==$flag) {
 		jQuery('.arcommntform-'+comment_id).addClass("aw_ar_gif_loader");
 		jQuery('button.aw_ar_commentfrm_btn').prop("disabled", true); 
 		var site_url = url+'/wp-admin/admin-ajax.php?';
		jQuery.ajax({
			url: site_url,
			type: 'POST',
			data: {action: "save_comment_on_review_ajax", comment_id:comment_id, product_id:product_id, nickname:nickname , comment:comment, aw_ar_nonce_ajax:aw_ar_front_nonce},
			success:function(data) {
				if(data) {
					alert(data);
					aw_ar_toggle_comment_form(comment_id);
					jQuery("input#nickname_text-"+comment_id).val('');
					jQuery("textarea#comment_text-"+comment_id).val('');
					jQuery('.arcommntform-'+comment_id).removeClass("aw_ar_gif_loader");
					jQuery('button.aw_ar_commentfrm_btn').prop("disabled", false); 
				}
			},
			error: function(errorThrown) {
				console.log(errorThrown);
			}
		})	
 	}
	
}

function aw_ar_show_lightbox(id){
	jQuery('.gallery-'+id+' a').lightbox(); 
}
function aw_ar_display_termcondi(){
	jQuery("#aw_ar_message_Modal").show();
}
function aw_ar_close_modal()
{
	if(jQuery("#aw_ar_message_Modal").is(':visible')){
		jQuery("#aw_ar_message_Modal").hide();
	}
	if(jQuery(".aw_ar_abuse_modal").is(':visible')){
		jQuery(".aw_ar_abuse_modal").hide();
	}
}