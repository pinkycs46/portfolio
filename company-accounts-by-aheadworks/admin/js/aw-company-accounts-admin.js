var url_admin			= aw_ca_admin_js_var.site_url;
var ajax_url			= aw_ca_admin_js_var.ajax_url;
var path				= aw_ca_admin_js_var.path;
var host				= aw_ca_admin_js_var.host;
var aw_ca_admin_nonce	= aw_ca_admin_js_var.aw_ca_admin_nonce;

jQuery.urlParam = function(name) {
	var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
	if(results!=null)
		return results[1] || 0;
	else
		return 0;
}

jQuery(window).load(function() {
	var tab 	= jQuery.urlParam('tab');
	var page 	= jQuery.urlParam('page'); 
	if(page == 'company-accounts-sales-representative' && tab != 'aw-ca-emails') {
		var i, tabcontent, tablinks;
		tabcontent = document.getElementsByClassName("tabcontent");
		for(i = 0; i < tabcontent.length; i++)
		{
			tabcontent[i].style.display = "none";
		}
		document.getElementById('aw_ca_slaes_repsentative-setting-tab').style.display = "block";
	}
	if(tab == 'aw-ca-emails') {
		var i, tabcontent, tablinks;
		tabcontent = document.getElementsByClassName("tabcontent");
		for(i = 0; i < tabcontent.length; i++)
		{
			tabcontent[i].style.display = "none";
		}
		jQuery( ".tablinks" ).removeClass( "active" );
		jQuery( ".tab button" ).last().addClass( "active" );
		document.getElementById('aw_ca_email-setting-tab').style.display = "block";
	}
	if(tab == 'aw-ca-sales-emails') {
		var i, tabcontent, tablinks;
		tabcontent = document.getElementsByClassName("tabcontent");
		for(i = 0; i < tabcontent.length; i++)
		{
			tabcontent[i].style.display = "none";
		}
		jQuery( ".tablinks" ).removeClass( "active" );
		jQuery( ".tab button" ).last().addClass( "active" );
		document.getElementById('aw_ca_sales_representative_email-setting-tab').style.display = "block";
	}

	if(tab =='company_form-setting-tab') {
		var i, tabcontent, tablinks;
		tabcontent = document.getElementsByClassName("tabcontent");
		for(i = 0; i < tabcontent.length; i++)
		{
			tabcontent[i].style.display = "none";
		}
		jQuery( ".tablinks" ).removeClass( "active" );
		jQuery( ".tab button#"+tab ).last().addClass( "active" );
		document.getElementById('aw_ca_'+tab).style.display = "block";
	}

});

jQuery(document).ready(function() {
 	jQuery('table#companyinoform tbody#companyino-list').sortable({
        'items': 'tr',
        'axis': 'y',
        'helper': fixHelper,
        'update': function (e, ui) {
            //jQuery(".intemate_to_save").text('Press save button to changed order');
           /* jQuery.post(ajaxurl, {
                action: 'update-menu-order',
                order: jQuery('#the-list').sortable('serialize'),
            });*/
        }
    });

    var fixHelper = function (e, ui) {
        ui.children().children().each(function () {
            jQuery(this).width(jQuery(this).width());
        });
        return ui;
    };

    /*var pageURL = window.location.href;
    var page = window.location.pathname.substr(window.location.pathname.lastIndexOf('/') + 1)
    URLsegment = pageURL.substr(pageURL.lastIndexOf('?') + 1)
    fragment = URLsegment.split('&');

	if(fragment.length>1 && page=='edit.php')
	{
		var fragment1 = URLsegment.split('&')[0];
		var fragment2 = URLsegment.split('&')[1];
		var fragment1 = fragment1.split('=');
		var fragment2 = fragment2.split('=');        
		console.log(fragment1);
		console.log(fragment2);
	}*/

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

		//call the recursive function for the first time
		decideParentsValue(jQuery(this));

		//Compulsorily apply check value Down in DOM
        jQuery(this).closest("li").find(".children input[type=checkbox]").prop("checked", checkboxValue);
	});

	function decideParentsValue(me) {
		var shouldTraverseUp = false;
        var checkedCount = 0;
        var myValue = me.prop("checked");

        //inspect my siblings to decide parents value
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
jQuery(document).on('click','.add_company_domain',function(e){	
	jQuery("#update_domain_Modal").show();
});
jQuery(document).on('click','.domain_modal_close',function(e){	
	jQuery("#update_domain_Modal").hide();
});

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

	jQuery('.validemail').each(function(index){
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


	$company_name = jQuery('#company_name').val().trim();

	jQuery.ajax({
		url: ajax_url,
		type: 'POST',
		async: false,
		data: {action: "aw_ca_check_company_name_ajax", company_name: $company_name, nonce_ca_ajax: aw_ca_admin_nonce},
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
	});

	return true;
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
function aw_ca_validate_domain(domain) {
	//var filter = /^[\w\-\.\+]+\.[a-zA-z0-9]{2,4}$/;
	var filter = /^(?!:\/\/)([a-zA-Z0-9-]+\.){0,5}[a-zA-Z0-9-][a-zA-Z0-9-]+\.[a-zA-Z]{2,64}?$/gi;
	if (filter.test(domain))
	{
		return true;
	}
	else
	{
		return false;
	}
}

function ca_openTab(evt, tabName, me)
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

function aw_ca_email_templvalidateForm() {
	var admin_template 	= ['Admin Email','Abuse Report Email','Critical Report Email'];
	var flag 			= false;
	//var recipient 		= jQuery.trim(jQuery('.aw_cc_recipient').val());
	//var emails 			= emailList.replace(/\s/g,'').split(",");
	var emailsubject	= jQuery.trim(jQuery('.aw_ca_mailsubject').val());
	var email_heading 	= jQuery.trim(jQuery('.aw_ca_email_heading').val());
	var additional_content = jQuery.trim(jQuery('.aw_ca_additional_content').val());
	var emailformname 	= jQuery.trim(jQuery('.emailformname').val());
	var regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	
	if(emailsubject == ""){
		jQuery('span.aw_ca_mailsubject').css( 'border','1px solid red');
		jQuery('span.aw_ca_mailsubject_msg').text('Subject is required').css({'color':'red'});
	    flag = true;
	} else {
		jQuery('span.aw_ca_mailsubject').css( 'border','');
		jQuery('span.aw_ca_mailsubject_msg').text('');	
	}

	if(email_heading == ""){
		jQuery('span.aw_ca_email_heading').css( 'border','1px solid red');
		jQuery('span.aw_ca_email_heading_msg').text('Email heading is required').css({'color':'red'});
	    flag = true;
	} else {
		jQuery('span.aw_ca_email_heading').css( 'border','');
		jQuery('span.aw_ca_email_heading_msg').text('');
	}
	
	if(additional_content == ""){
		jQuery('span.aw_ca_additional_content').css( 'border','1px solid red');
		jQuery('span.aw_ca_additional_content_msg').text('Additional content is required').css({'color':'red'});
	    flag = true;
	} else {
		jQuery('span.aw_ca_additional_content').css( 'border','');
		jQuery('span.aw_ca_additional_content_msg').text('');
	}

	if(flag == true){
		return false;
	}
	return true;
}

function aw_ca_add_clone_row(event){
	event.preventDefault();
	var clonehtml = jQuery('table tr.aw_ca_row').html();
	jQuery('#sales_representative_table > tbody').append( "<tr>"+clonehtml+"</tr>" );
}

function aw_ca_delete_sales_man(){
	var td = event.target.parentNode; 
    var tr = td.parentNode; // the row to be removed
    tr.parentNode.removeChild(tr);
}

function check_exist_domain(event){
	var domainname = jQuery('#aw_ca_domain_name').val();
	var domainstatus = jQuery('#domain_status').val();

	if(!aw_ca_validate_domain(domainname)) {
		alert('This domain address is incorrect');
		event.preventDefault();
		return false;
	}
	
	jQuery.ajax({
		url: ajax_url,
		type: 'POST',
		async: false,
		data: {action: "aw_ca_get_company_domain_ajax", domain_name: domainname, domain_status:domainstatus, nonce_ca_ajax: aw_ca_admin_nonce},
		success:function(data) { 
			if(data){
				alert(data)
				event.preventDefault();
			} 
		},
		error: function(errorThrown){
			console.log(errorThrown);
		}
	});
	//event.preventDefault();
}

function aw_ca_deletedomain(id) {

	if (confirm("Are you sure to delete it ?")) {
		jQuery.ajax({
			url: ajax_url,
			type: 'POST',
			async: false,
			data: {action: "aw_ca_delete_company_domain_ajax", domain_id: id, nonce_ca_ajax: aw_ca_admin_nonce},
			success:function(data) {
				if(data) {
					alert('hello');
					location.reload();	
				} 
			},
			error: function(errorThrown) {
				console.log(errorThrown);
			}
		});
	} 
}	

function aw_ca_get_domain_detail(domainid){

	jQuery.ajax({
			url: ajax_url,
			type: 'POST',
			async: false,
			data: {action: "aw_ca_get_domain_detail_ajax", domain_id: domainid, nonce_ca_ajax: aw_ca_admin_nonce},
			success:function(data) {
				data = JSON.parse(data);
				if(data.domain_name != null && data.status != null) {
					jQuery("#aw_ca_domain_name").val(data.domain_name);
					var status = jQuery("#domain_status").val(data.status);
					jQuery("#domain_id").val(data.domain_id);
					jQuery("#domain_save_button").prop('onclick', null);;
					jQuery("#domain_status option").each(function () {
					        if (jQuery(this).html() == status) {
					            jQuery(this).attr("selected", "selected");
					        }
					});
					jQuery("#update_domain_Modal").show();

				}
			},
			error: function(errorThrown) {
				console.log(errorThrown);
			}
		});
}


 
