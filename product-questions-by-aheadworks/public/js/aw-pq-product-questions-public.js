var url 			= js_qa_var.site_url;
var nonce_qa 		= js_qa_var.rd_qa_nonce;
var ask_a_question 	= js_qa_var.ask_a_question;
var text_hide		= js_qa_var.text_hide;
var add_answer 		= js_qa_var.add_answer; 
var edit_answer 	= js_qa_var.edit_answer;
var edit_question 	= js_qa_var.edit_question;

var emailReg 	= /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
var letterNumber= /^[0-9a-zA-Z- ]+?$/;
var allkeyletter= /^[0-9a-zA-Z.!#$%&@(’) *+\/=?^_`{|}~-]+?$/; 
var error_msg	= [];
var $flag		= 0;
error_msg[0]	= 0;
error_msg[1]	= js_qa_var.required_field;//'Enter required field';
error_msg[2]	= js_qa_var.valid_email;//'Enter valid email.';
error_msg[3]	= js_qa_var.vaild_author;//'Enter valid author name.';
error_msg[4]	= js_qa_var.valid_comment_text;//'Enter valid comment text.';  

function askquestion()
{	
	jQuery(".rd_total_q_button Div#respond").removeClass("hide_questionsform");
	jQuery(".rd_total_q_button Div#respond").removeClass("hide_answerform");

	if (jQuery("#rd_ask_question").attr("data-value") == "rd_ask") {
		jQuery("#rd_ask_question").attr("data-value","rd_hide");
		jQuery(".comments-area #respond").removeClass("hide_questionsform");
		jQuery(".comments-area #respond").addClass("show_questionsform");
		jQuery("#rd_ask_question").text(text_hide);
	} else {
		jQuery("#rd_ask_question").attr("data-value","rd_ask")
		jQuery(".comments-area #respond").removeClass("show_questionsform");
		jQuery(".comments-area #respond").addClass("hide_questionsform");
		jQuery("#rd_ask_question").text(ask_a_question);
	} 

	/* Hide Answer form Start */
	var opendbox_id = jQuery("div.show_answerform").prop("id");
	if(jQuery("div").hasClass("show_answerform"))
	{
		jQuery("#"+opendbox_id).removeClass("show_answerform"); 	
		jQuery("#"+opendbox_id).addClass("hide_answerform"); 

		var id = opendbox_id.match(/\d+/);
		jQuery("#reply-"+id).attr("data-button", "rd_show_reply"); 
		jQuery("#reply-"+id).val(add_answer); 

	}
	/* Hide Answer form End */
}

function remove_and_active_tab()
{
	var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('#');
	var hashes_scroll = window.location.href.slice(window.location.href.indexOf('?') + 1).split('+');

	if(hashes[hashes.length-1].length>0)
	{
		var tab = hashes[hashes.length-1];
		if('tab-QA_tab'==tab)
		{
			jQuery('ul.tabs li').removeClass('active');
			jQuery('ul.tabs #tab-title-QA_tab').addClass('active');

			jQuery(".woocommerce-Tabs-panel").hide();
			jQuery("#"+tab).show();
		}
	}

	if(hashes_scroll[hashes_scroll.length-1].length>0)
	{
		var id = hashes_scroll[0].split('-');

		var tab_scroll = hashes_scroll[hashes_scroll.length-1];
		if('tab-QA_tab'==tab_scroll) {
			jQuery('ul.tabs li').removeClass('active');
			jQuery('ul.tabs #tab-title-QA_tab').addClass('active');

			jQuery(".woocommerce-Tabs-panel").hide();
			jQuery("#"+tab_scroll).show();

			jQuery('html, body').animate({ 
				scrollTop: jQuery('#div-comment-'+id[2]).offset().top
			}, 1000);
		}
	}
	jQuery("#tab-QA_tab nav.comment-navigation").hide();
}

jQuery(window).load(function(){
	remove_and_active_tab()
});

/*var validTag = /^<\/?[A-Za-z]+>$/;

var valid = function(tag){
	return validTag.test(tag);
}*/

function aw_pq_edit_comment(commentid) {

	jQuery('.comment-text').show();
	jQuery('.comment-editrespond').removeClass("show_editanswerform");
	jQuery('.comment-editrespond').addClass("hide_editanswerform"); 
	jQuery('.pq_ans_editbutton').text(edit_answer);
	jQuery('.pq_ques_editbutton').text(edit_question);

	jQuery(".comments-area #respond").removeClass("show_questionsform");
	jQuery(".comments-area #respond").addClass("hide_questionsform");
	jQuery("#rd_ask_question").text(ask_a_question);
	jQuery("#rd_ask_question").attr("data-value","rd_ask")

	var site_url = url+'/wp-admin/admin-ajax.php';
	jQuery.ajax({
		url: site_url,
		type: 'POST',
		data: {action:"aw_pq_check_enable_edit_comment", commentid:commentid, rd_qa_nonce_ajax: nonce_qa},
		success:function(data) {
			obj = JSON.parse(data);
			if(obj.result){
				jQuery('#error_msg-'+commentid).text("");
				if(jQuery("div").hasClass("show_editanswerform"))
				{	
					var opendbox_id = jQuery("div.show_editanswerform").prop("id");
					jQuery("#"+opendbox_id).removeClass("show_editanswerform"); 	
					jQuery("#"+opendbox_id).addClass("hide_editanswerform"); 
					var id = opendbox_id.match(/\d+/);
					jQuery("#edit-"+commentid).attr("data-button", "rd_show_reply"); 
					jQuery("#edit-"+commentid).text(edit_answer); 
					jQuery(".rdanswerbutton").show();
					jQuery('#comment-text-'+commentid).show();
				} else {
					var commentbox = 'editrespond-'+ commentid;
					if(jQuery('#edit-'+commentid).attr("data-button") === "rd_show_reply") {
						jQuery('#edit-'+commentid).attr("data-button", "rd_hide_reply");
						jQuery("#"+commentbox).removeClass("hide_editanswerform");
						jQuery("#"+commentbox).addClass("show_editanswerform");
						jQuery('#comment-text-'+commentid).hide();
						jQuery('#edit-'+commentid).text(text_hide);
					} else if(jQuery('#edit-'+commentid).attr("data-button") === "rd_show_edit_reply") {
						jQuery('#edit-'+commentid).attr("data-button", "rd_hide_edit_reply");
						jQuery("#"+commentbox).removeClass("hide_editanswerform");
						jQuery("#"+commentbox).addClass("show_editanswerform");
						jQuery('#comment-text-'+commentid).hide();
						jQuery('#edit-'+commentid).text(text_hide);						
					} else if(jQuery('#edit-'+commentid).attr("data-button") === "rd_hide_reply") {
						jQuery('#edit-'+commentid).attr("data-button", "rd_show_reply");
						jQuery("#"+commentbox).removeClass("show_editanswerform");
						jQuery("#"+commentbox).addClass("hide_editanswerform");
						jQuery('#comment-text-'+commentid).show();
						jQuery('#edit-'+commentid).text(edit_question);
					} else if(jQuery('#edit-'+commentid).attr("data-button") ==="rd_hide_edit_reply") {
						jQuery('#edit-'+commentid).attr("data-button", "rd_show_edit_reply");
						jQuery("#"+commentbox).removeClass("show_editanswerform");
						jQuery("#"+commentbox).addClass("hide_editanswerform");
						jQuery('#comment-text-'+commentid).show();
						jQuery('#edit-'+commentid).text(edit_answer);						
					} else {
						jQuery('#edit-'+commentid).attr("data-button", "rd_show_reply");
						jQuery("#"+commentbox).removeClass("show_editanswerform");
						jQuery("#"+commentbox).addClass("hide_editanswerform");
						jQuery('#comment-text-'+commentid).show();
						jQuery('#edit-'+commentid).text(edit_answer);
					}
				}
			} else {
				jQuery('.error_mesg').hide();
				jQuery('#error_msg-'+commentid).show();
				jQuery('#error_msg-'+commentid).text(obj.message);
			} 
		}, 
		error: function(errorThrown){
		console.log(errorThrown);
		}
	});
}

jQuery(document).ready(function(){
	remove_and_active_tab();
	jQuery(".rdanswerbutton").click(function(){
		/*jQuery('.comment-respond').removeClass("show_answerform");
		jQuery('.comment-respond').addClass("hide_answerform"); 
		jQuery('.rdanswerbutton').val('Add Answer');
		if(jQuery(this).attr("data-button") === "rd_hide_reply") {
			jQuery(this).attr("data-button", "rd_show_reply");
		}*/
		if(jQuery(this).attr("data-button") === "rd_show_reply") {
			jQuery('.rdanswerbutton').val(add_answer)
			jQuery('.comment-respond').removeClass("show_answerform");
			jQuery('.comment-respond').addClass("hide_answerform"); 
			jQuery('.rdanswerbutton').attr("data-button", "rd_show_reply");
		}

		/*Hide question Form Start */
		jQuery(".comments-area #respond").removeClass("show_questionsform");
		jQuery(".comments-area #respond").addClass("hide_questionsform");
		jQuery("#rd_ask_question").text(ask_a_question);
		jQuery("#rd_ask_question").attr("data-value","rd_ask")
		
		/*Hide question Form End */
		var commentid = jQuery(this).attr('data-value');
		if(jQuery("div").hasClass("show_answerform"))
		{	
			var opendbox_id = jQuery("div.show_answerform").prop("id");
			jQuery("#"+opendbox_id).removeClass("show_answerform"); 	
			jQuery("#"+opendbox_id).addClass("hide_answerform"); 
			var id = opendbox_id.match(/\d+/);
			jQuery("#reply-"+id).attr("data-button", "rd_show_reply"); 
			jQuery("#reply-"+id).val(add_answer); 
		} else {
			var commentbox = 'respond-'+ jQuery(this).attr('data-value');
			if(jQuery(this).attr("data-button") === "rd_show_reply") {
				jQuery(this).attr("data-button", "rd_hide_reply");
				jQuery("#"+commentbox).removeClass("hide_answerform");
				jQuery("#"+commentbox).addClass("show_answerform");
				//jQuery("#loginrequired"+commentid).removeClass("hide_answerform");
				//jQuery("#loginrequired"+commentid).addClass("show_answerform");
				jQuery(this).val(text_hide);
			} else {
				jQuery(this).attr("data-button", "rd_show_reply");
				jQuery("#"+commentbox).removeClass("show_answerform");
				jQuery("#"+commentbox).addClass("hide_answerform");
				jQuery(this).val(add_answer);
			}
		}
			
	});

	jQuery("#reviews .commentlist .q_and_a").remove();
	var tabcount = jQuery(".reviews_tab a").text();
	tabcount = tabcount.match(/\d+/);
	title = jQuery(".product_title").text();

	jQuery(".woocommerce-Reviews-title").html(tabcount+' reviews for <span>'+title+'<span>');
	jQuery(".woocommerce-product-rating a.woocommerce-review-link span").show();
	//jQuery(".woocommerce-product-rating a.woocommerce-review-link span").text(tabcount);

    /* #################  */
    jQuery(".btn_Q_submit").click(function(event){
    	$flag		= 0;
    	var id 		=jQuery(this).attr('data-value');
    	var author 	= jQuery(".author-"+id).val().trim(); 
		var email  	= jQuery(".email-"+id).val().trim();  
		var comment = jQuery(".comment-"+id).val().trim();  

		jQuery(".author-"+id).css( 'border','');
		jQuery(".email-"+id).css( 'border','');
		jQuery(".comment-"+id).css( 'border','');

		if ("" === author) {	
			jQuery(".author-"+id).css( 'border','1px solid red');
			event.preventDefault(); 		
			$flag = 1; 
		}  

		if("" === email ) {
			jQuery(".email-"+id).css( 'border','1px solid red');
			event.preventDefault(); 		
			$flag = 1; 
		} else {
		 	if (! emailReg.test( email )) {
		 		jQuery(".email-"+id).css( 'border','1px solid red');
				event.preventDefault(); 		
				$flag = 2; 	
			} else
			{
				jQuery(".email-"+id).css( 'border','');
			}
		 }

		if("" === comment)
		{
			jQuery(".comment-"+id).css( 'border','1px solid red');
			event.preventDefault(); 		
			$flag = 1; 
		}  

		if ($flag ) {
			alert(error_msg[$flag]); 
			return false;
		}
    });

	jQuery(".btn_QA_submit").click(function(event){
		$flag 		= 0;
		var id 		=jQuery(this).attr('data-value');
		var author 	= jQuery(".author-"+id).val().trim(); 
		var email  	= jQuery(".email-"+id).val().trim();  
		var comment = jQuery(".comment--"+id).val().trim();  

		jQuery(".author-"+id).css( 'border','');
		jQuery(".email-"+id).css( 'border','');
		jQuery(".comment--"+id).css( 'border','');

		if ("" === author) {	
			jQuery(".author-"+id).css( 'border','1px solid red');
			event.preventDefault(); 		
			$flag = 1; 
		}  

		if("" === email ) {
			jQuery(".email-"+id).css( 'border','1px solid red');
			event.preventDefault(); 		
			$flag = 1; 
		} else {
		 	if (! emailReg.test( email )) {
		 		jQuery(".email-"+id).css( 'border','1px solid red');
				event.preventDefault(); 		
				$flag = 2; 	
			} else {
				jQuery(".email-"+id).css( 'border','');
			}
		}
		if("" === comment)
		{
			jQuery(".comment--"+id).css( 'border','1px solid red');
			event.preventDefault(); 		
			$flag = 1; 
		}  
		if ($flag ) {
			alert(error_msg[$flag]); 
			return false;
		}
	});

	jQuery(".btn_Q_update_submit").click(function(event){
		$flag 		= 0;
		var id 		=jQuery(this).attr('data-value');
		var author 	= jQuery(".q_author-"+id).val().trim(); 
		var email  	= jQuery(".q_email-"+id).val().trim();  
		var comment = jQuery(".q_comment-"+id).val().trim(); 

		jQuery(".q_author-"+id).css( 'border','');
		jQuery(".q_email-"+id).css( 'border','');
		jQuery(".q_comment-"+id).css( 'border','');

		if ("" === author) {	
			jQuery(".q_author-"+id).css( 'border','1px solid red');
			event.preventDefault(); 		
			$flag = 1; 
		}  

		if("" === email ) {
			jQuery(".q_email-"+id).css( 'border','1px solid red');
			event.preventDefault(); 		
			$flag = 1; 
		} else {
		 	if (! emailReg.test( email )) {
		 		jQuery(".q_email-"+id).css( 'border','1px solid red');
				event.preventDefault(); 		
				$flag = 2; 	
			} else
			{
				jQuery(".q_email-"+id).css( 'border','');
			}
		 }

		if("" === comment)
		{
			jQuery(".q_comment-"+id).css( 'border','1px solid red');
			event.preventDefault(); 		
			$flag = 1; 
		}  

		if ($flag ) {
			alert(error_msg[$flag]); 
			return false;
		}
		
	});

    jQuery(".btn_A_update_submit").click(function(event){
    	$flag 		= 0;
    	var id 		=jQuery(this).attr('data-value');
		var author 	= jQuery(".a_author-"+id).val().trim(); 
		var email  	= jQuery(".a_email-"+id).val().trim();  
		var comment = jQuery(".a_comment-"+id).val().trim(); 

		jQuery(".a_author-"+id).css( 'border','');
		jQuery(".a_email-"+id).css( 'border','');
		jQuery(".a_comment-"+id).css( 'border','');

		if ("" === author) {	
			jQuery(".a_author-"+id).css( 'border','1px solid red');
			event.preventDefault(); 		
			$flag = 1; 
		}  

		if("" === email ) {
			jQuery(".a_email-"+id).css( 'border','1px solid red');
			event.preventDefault(); 		
			$flag = 1; 
		} else {
		 	if (! emailReg.test( email )) {
		 		jQuery(".a_email-"+id).css( 'border','1px solid red');
				event.preventDefault(); 		
				$flag = 2; 	
			} else
			{
				jQuery(".a_email-"+id).css( 'border','');
			}
		 }

		if("" === comment)
		{
			jQuery(".a_comment-"+id).css( 'border','1px solid red');
			event.preventDefault(); 		
			$flag = 1; 
		}  
	 
		if ($flag ) {
			alert(error_msg[$flag]); 
			return false;
		}
		
	});

	/* #################  */
		//jQuery("a .like_dislike_img").click(function(){
	jQuery(".like_dislike_img").click(function(){

		var site_url = url+'/wp-admin/admin-ajax.php';
		var comment_id = jQuery(this).data('comment-id');
		var trigger_type= jQuery(this).data('trigger-type');
		var user_id = jQuery(this).data('user-id');
		var image_id = jQuery(this).attr('id');

		if (!jQuery('.like_dislike_img').is('[disabled=disabled]')) {
			jQuery(this).parent('div').parent('div').addClass("rd_gif_loader");
			jQuery('.like_dislike_img').attr('disabled',true);
			jQuery.ajax({
				url: site_url,
				type: 'POST',
				data: {action:"aw_pq_product_question_like_dislike", comment_id:comment_id, trigger_type:trigger_type, user_id:user_id, rd_qa_nonce_ajax: nonce_qa},
				success:function(data) {
					if(data.length>0)
					{
						var obj = jQuery.parseJSON( data );
						jQuery('#'+image_id).attr('src',obj.changed_image);
						jQuery('#helpfulcount-'+comment_id).text(obj.rd_helpful);
						if (0 != obj.rd_not_helpful) {
							var nothelpful = "-"+obj.rd_not_helpful;
						} else {
							var nothelpful = obj.rd_not_helpful;
						}
						jQuery('#nothelpfulcount-'+comment_id).text(nothelpful);
						jQuery('#rd-helpful-'+comment_id).attr('src',obj.rd_helpful_image);
						jQuery('#rd-not-helpful-'+comment_id).attr('src',obj.rd_not_helpful_image);
						jQuery('.like_dislike_img').removeAttr('disabled');
						jQuery('.like_dislike_img').parent('div').parent('div').removeClass("rd_gif_loader");
					}
				},
				error: function(errorThrown){
				console.log(errorThrown);
				}
			});
		}
	});

	jQuery('.woocommerce-pagination ul li a.page-numbers').click(function() {
		remove_and_active_tab();
	});
		
})

