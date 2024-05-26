var url_admin			= js_ca_var.site_url;
var ajax_url			= js_ca_var.ajax_url;
var path				= js_ca_var.path;
var host				= js_ca_var.host;
var aw_ca_front_nonce	= js_ca_var.aw_ca_front_nonce;


jQuery.urlParam = function(name){
	var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
	if(results!=null)
		return results[1] || 0;
	else
		return 0;
}
jQuery(document).ready(function() {
	var toggler = document.getElementsByClassName("caret");
	var i;
	for (i = 0; i < toggler.length; i++) {
	  toggler[i].addEventListener("click", function() {
	    this.parentElement.querySelector(".nested").classList.toggle("activelist");
	    this.classList.toggle("caret-down");
	  });
	} 

	jQuery("ul.permission_checklist input[type=checkbox]").on("change", function() {
		var checkboxValue = jQuery(this).prop("checked");
		decideParentsValue(jQuery(this));
        jQuery(this).closest("li").find(".children input[type=checkbox]").prop("checked", checkboxValue);
	});

	function decideParentsValue(me) {
		var shouldTraverseUp = false;
        var checkedCount = 0;
        var myValue = me.prop("checked");
        jQuery.each(jQuery(me).closest(".children").children('li'), function() {
          var checkbox = jQuery(this).children("input[type=checkbox]");
          if (jQuery(checkbox).prop("checked")) {
            checkedCount = checkedCount + 1;
          }
        });

        //if I am checked and my siblings are also checked do nothing
        //OR
        //if I am unchecked and my any sibling is checked do nothing
        if ((myValue == true && checkedCount == 1) || (myValue == false && checkedCount == 0)) {
          shouldTraverseUp = true;
        }
        if (shouldTraverseUp == true) {
          var inputCheckBox = jQuery(me).closest(".children").siblings("input[type=checkbox]");
          inputCheckBox.prop("checked", me.prop("checked"));
          decideParentsValue(inputCheckBox);
        }
	}
});


function formvalidate(event){
	
	var error_msg = [];
	error_msg[0] = ' ';
	error_msg[1] = 'This is a required field';
	error_msg[2] = 'Valid email id is required';
	$flag 	= 0 ;
	
	jQuery('.txt_required').each(function(index){
		var id = jQuery(this).attr('id');
		 if (jQuery(this).val().trim() === ""){
		 	//if(jQuery(this).val == '') {
		 	$flag = 1;
			jQuery(this).css( 'border','1px solid red')	
			jQuery(".error_"+id).text(error_msg[$flag]).css('color','red');
		} else {
			jQuery(this).css( 'border','1px solid lightgrey');	
			jQuery(".error_"+id).text('');
		}
	});

	jQuery('.validemail').each(function(index) {
		var id = jQuery(this).attr('id');
		var result = aw_ca_validate_email(jQuery(this).val());
		if(result==false) {
			$flag = 2;
			jQuery(this).css( 'border','1px solid red')	
			jQuery(".error_"+id).text(error_msg[$flag]).css('color','red');
		} else {
			jQuery(this).css( 'border','1px solid lightgrey');	
			jQuery(".error_"+id).text('');
		}
	});
	if($flag!=0) {
		event.preventDefault();
		return false;
	}

/*
	$company_name = jQuery('#company_name').val().trim();

	jQuery.ajax({
		url: ajax_url,
		type: 'POST',
		async: false,
		data: {action: "aw_ca_check_company_name_ajax", company_name: $company_name, nonce_ca_ajax: aw_ca_front_nonce},
		success:function(data) { 
			if(data){
				jQuery('#company_name').css( 'border','1px solid red');
				jQuery('.error_company_name').text(data).css({'color':'red'});
				event.preventDefault();
			}else{
				jQuery('#company_name').css( 'border','');
				jQuery('.error_company_name').text('');
			}
		},
		error: function(errorThrown){
			console.log(errorThrown);
		}
	}); */

}

 
function aw_ca_validate_email(isEmail) {

	var filter = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
	if (filter.test(isEmail))
	{
		return true;
	}
	else
	{
		return false;
	}
} 

function awcacheckIt(evt,allowed = '')
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