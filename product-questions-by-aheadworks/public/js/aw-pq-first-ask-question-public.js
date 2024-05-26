var url 			= js_faq_var.site_url;
var post_type 		= js_faq_var.post_type;
var nonce_faq 		= js_faq_var.rd_faq_nonce;
jQuery(document).ready(function(){

	jQuery(".faq_like_dislike_img").click(function(){
		var site_url = url+'/wp-admin/admin-ajax.php';
		var post_id = jQuery(this).data('post-id');
		var trigger_type= jQuery(this).data('trigger-type');
		var user_id = jQuery(this).data('user-id');
		var image_id = jQuery(this).attr('id');
		var  val_rate = '';

		if (!jQuery('.faq_like_dislike_img').is('[disabled=disabled]')) {
			jQuery(this).parent('div').parent('div').addClass("rd_gif_loader");
			jQuery('.faq_like_dislike_img').attr('disabled',true);
			jQuery.ajax({
				url: site_url,
				type: 'POST',
				data: {action:"aw_faq_like_dislike", post_id:post_id, trigger_type:trigger_type, user_id:user_id, pq_faq_nonce_ajax: nonce_faq},
				success:function(data) {
					if(data.length>0)
					{
						var obj = jQuery.parseJSON( data );
						jQuery('#'+image_id).attr('src',obj.changed_image);
						jQuery('#faqhelpfulcount-'+post_id).text(obj.helpful_votes);
						if (0 != obj.not_helpful_votes) {
							var faqnothelpful = "-"+obj.not_helpful_votes;
						} else {
							var faqnothelpful = obj.not_helpful_votes;
						}
						jQuery('#faqhelpfulrate-'+post_id).text();
						if ('yes'+post_id == obj.faq_vote_user) {
							val_rate = "("+obj.helpful_rate+" %  of other people think it was helpful)";
						} 
						jQuery('#faqnothelpfulcount-'+post_id).text(faqnothelpful);
						jQuery('#faqhelpfulrate-'+post_id).html(val_rate);
						jQuery('#faq_num_helpful_votes-'+post_id).attr('src',obj.helpful_votes_image);
						jQuery('#faq_num_not_helpful_votes-'+post_id).attr('src',obj.not_helpful_votes_image);
						jQuery('.faq_like_dislike_img').removeAttr('disabled');
						jQuery('.faq_like_dislike_img').parent('div').parent('div').removeClass("rd_gif_loader");
					}
				},
				error: function(errorThrown){
				console.log(errorThrown);
				}
			});
		}
	});

	if ('faq_article' == post_type) {

		jQuery("#submit").click(function(event){
	    	$flag		= 0;
	    	var comment = jQuery("#comment").val();
	    	var pattern = new RegExp('^(https?:\\/\\/)?'+'((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+'((\\d{1,3}\\.){3}\\d{1,3}))'+'(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+'(\\?[;&a-z\\d%_.~+=-]*)?'+'(\\#[-a-z\\d_]*)?$','i');

			if("" === comment)
			{
				jQuery("#comment").css( 'border','1px solid red');
				event.preventDefault(); 		
				$flag = 1; 
			}  

			if ($flag ) {
				alert('Enter required field'); 
				return false;
			}
	    });
	}
})