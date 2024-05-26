jQuery(document).ready(function(){
	var url = js_var.site_url;
	jQuery('#Popup_Pro_sub').removeClass('postbox');
	jQuery('#Popup_Pro_sub .handlediv').remove();
	jQuery('#Popup_Pro_sub .hndle').remove();
	jQuery('table.subscribers').removeClass('fixed');
	jQuery('#TB_ajaxContent').css('height',600);

	jQuery("#background_background-image").change(function() {
		var file = jQuery("#background_background-image")[0].files[0];
		var path = url+'/wp-content/plugins/popup-pro/admin/templates/images/'+file.name;
		var name = file.name;
		var filext = name.substring(name.lastIndexOf(".")+1);
		jQuery('#backgroundimage').val('');
		if(file)
		{
			if(filext == "jpeg" || filext == "jpg" || filext == "png" || filext == "bmp" || filext == "gif" || filext == "JPEG" || filext == "JPG" || filext == "PNG" || filext == "BMP" || filext == "GIF")
			{
				var reader = new FileReader();
				reader.readAsDataURL(file);
				reader.onload = function(e) {
					jQuery("#uploadedimage").text("");
					jQuery('#backgroundimage').attr('data-value',e.target.result);
					jQuery('#backgroundimage').val(path);
					jQuery('#background_display-image').attr('src',e.target.result);
					jQuery("#background_display-image").attr('image-name',name);
					jQuery("#closebackimage").show();
					jQuery("#background_display-image").show();
				};
			}
			else
			{
				alert("Invalid file type !");
				return false;
			}	
		}
	});

    /** Defult set template link for popup open */    
    var template= jQuery("#popup_pro_subscribe_template").find(':selected').val();

    var templateurl = jQuery("#pop-pro-sub-link").attr("href")+'&template='+template;
    jQuery("#pop-pro-sub-link").attr("href", templateurl).val();
    /**************/

    /** Defult set template link to popup product open */  
    var no_pro = jQuery("#popup_pro_maximum_product").val();
    var title = jQuery("#popup_pro_title").val();
    var type = jQuery("#popup_pro_type_display").children("option:selected").val();
    var templateurl  = jQuery("#hidden_popup_link").val()+'&no_pro='+no_pro+'&title='+title+'&type='+type;

    jQuery("#pop_pro_product_preview").attr("href",templateurl).val();

    jQuery(".txt_required").keyup(function(){
		var no_pro = jQuery("#popup_pro_maximum_product").val();
		var title = jQuery("#popup_pro_title").val();
		var type= jQuery("#popup_pro_type_display").children("option:selected").val();
		var templateurl  = jQuery("#hidden_popup_link").val()+'&no_pro='+no_pro+'&title='+title+'&type='+type;
		jQuery("#pop_pro_product_preview").attr("href",templateurl).val();
    });

    jQuery(".txt_required").change(function(){
		var no_pro = jQuery("#popup_pro_maximum_product").val();
		var title = jQuery("#popup_pro_title").val();
		var type= jQuery("#popup_pro_type_display").children("option:selected").val();
		var templateurl  = jQuery("#hidden_popup_link").val()+'&no_pro='+no_pro+'&title='+title+'&type='+type;
		jQuery("#pop_pro_product_preview").attr("href",templateurl).val();
    });
    /**************/

    jQuery('select option[value=draft]').text('Unpublished');
    jQuery('select option[value=pending]').remove();
    jQuery('.post-state').each(function(index,value){
		jQuery(".post-state").text('Unpublished');
    });

    if(jQuery.trim(jQuery("#post-status-display").text())=="Draft")
    {
       jQuery("#post-status-display").text('Unpublished');
    }

	jQuery("#wp-admin-bar-view").remove();
	jQuery("#wp-admin-bar-preview").remove();
	jQuery(".misc-pub-curtime").hide();
	jQuery(".inline-edit-date").hide();
	jQuery(".inline-edit-col-left .inline-edit-col .inline-edit-group").hide();
	jQuery("#misc-publishing-actions #visibility").hide();
	jQuery("#save-post").val('Save as Unpublished');

    var pageURL = window.location.href;
    var page = window.location.pathname.substr(window.location.pathname.lastIndexOf('/') + 1)
    URLsegment = pageURL.substr(pageURL.lastIndexOf('?') + 1)
    fragment = URLsegment.split('&');

	if(fragment.length>1 && page=='edit.php')
	{
		var fragment1 = URLsegment.split('&')[0];
		var fragment2 = URLsegment.split('&')[1];
		var fragment1 = fragment1.split('=');
		var fragment2 = fragment2.split('=');        
		if(fragment1[1]=='popup-pro' && fragment2[1]=='subscribers-list')
		{
			jQuery( ".bulkactions" ).after( '<div class="alignleft actions bulkactions"><select name="file_format" id="export_format"><option value="xls" value="0">XLS</option><option value="csv">CSV</option></select><input type="hidden" id="hidden_format" value=""><a href="'+url+'/wp-admin/admin.php?action=export_subscribers&amp;template=undefined" class="button action exportlink" id="pop-pro-sub-link">Export</a></div>' );
		}
		jQuery("#hidden_format").val(jQuery('.exportlink').attr('href'));
		var option =  jQuery("#export_format").children("option:selected").val();

		jQuery('.exportlink').attr('href','');
		var link = jQuery("#hidden_format").val()+'&format='+option;

		jQuery('.exportlink').attr('href',link);
		jQuery('#export_format').change(function(){
			var option =  jQuery("#export_format").children("option:selected").val();

			jQuery('.exportlink').attr('href','');
			var link = jQuery("#hidden_format").val()+'&format='+option;
			jQuery('.exportlink').attr('href',link);
		});
	}

	/* This is for New subscribe popup; set all defult value of new template */
	if( page=='post-new.php')
	{
		var template = jQuery("#popup_pro_subscribe_template option:nth-child(1)").val();
		set_bg_img_hint(template,'no','');
		jQuery("#closebackimage").hide();
	}
	else
	{
		var template = jQuery("#popup_pro_subscribe_template").find(':selected').val();
		set_bg_img_hint(template,'yes');
		jQuery("#closebackimage").show();
	}

	defaultfilepath = '';
	var defaultfilepath = jQuery('#backgroundimage').val();
	if(defaultfilepath)
	{
		convertImgToBase64URL(defaultfilepath, function(base64Img){
							jQuery('#backgroundimage').attr('data-value',base64Img);
						});
	}
	else
	{
		jQuery('#background_display-image').hide();
		jQuery("#closebackimage").hide();
	}
	var texts = jQuery('span.post-state').map(function(){
		var txt = this.previousSibling.nodeValue;
		this.previousSibling.nodeValue = txt.replace(txt, "");
	});

	jQuery("#closebackimage").click(function(){
		
		var url = js_var.site_url;
		var postid = jQuery(this).attr('post-id');
		request_Url = url+'/wp-admin/admin-ajax.php?action=aw_popup_background_image_delete&postid='+postid;
		jQuery.ajax({
			url: request_Url,
			type:'POST',
			success:function(data){
				if(data!=0)
				  alert(data);

				jQuery("#background_background-image").val("");
				jQuery("#uploadedimage").text("");
				jQuery("#backgroundimage").val('');
				jQuery("#backgroundimage").attr('data-value','');
				jQuery("#background_display-image").attr('src','');
				jQuery("#background_display-image").hide();
				jQuery("#closebackimage").hide();

			},
			error: function(errorThrown){
				console.log(errorThrown);
			}
		});

		jQuery("#background_display-image").attr('image-name','');
	});	

	jQuery('.jscolor').each(function(){
		colors = "#"+jQuery(this).val();
		fontcolor=pickTextColorBasedOnBgColorSimple(colors, '#FFFFFF', '#000000');
		jQuery(this).css({'background-color':'#'+jQuery(this).val(),'color':fontcolor}); 

	});
});

jQuery(window).load(function(){
	var url = js_var.site_url;
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
			jQuery('#save-post').val("Save as Unpublished");
		}
	});

	jQuery('#save-post').click(function(e){
		jQuery('#save-post').hide();
		jQuery('#save-post').val("Save as Unpublished");
	});

	jQuery('.cancel-post-status').click(function(e){
		jQuery('#save-post').val("Save as Unpublished");
	});

	jQuery('.save-post-status').click(function(e){
		jQuery('#save-post').val("Save as Unpublished");
	});
	
 	var template = jQuery("#popup_pro_subscribe_template").find(':selected').val();
	if(template==='aw-popup-template02')
	{
		document.getElementById('subtitle-tab').style.display = "block";
		tablinks = document.getElementsByClassName("tablinks");
		tablinks[1].className = 'tablinks active';
	}
	else
	{
		if(document.getElementById('title-tab'))
		{
			document.getElementById('title-tab').style.display = "block";
			tablinks = document.getElementsByClassName("tablinks");
			tablinks[0].className = 'tablinks active';
		}
	}
});

function openTab(evt, tabName)
{
	evt.preventDefault();
	var i, tabcontent, tablinks;

	document.getElementById('title-tab').style.display = "none";
	tablinks = document.getElementsByClassName("tablinks");
	tablinks[0].className =  tablinks[0].className.replace("active", "");

	tabcontent = document.getElementsByClassName("tabcontent");
	for (i = 0; i < tabcontent.length; i++)
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

	jQuery('.jscolor').each(function(){
      colors = "#"+jQuery(this).val();
      fontcolor=pickTextColorBasedOnBgColorSimple(colors, '#FFFFFF', '#000000');
      jQuery(this).css({'background-color':'#'+jQuery(this).val(),'color':fontcolor}); 

	});
}
function pickTextColorBasedOnBgColorSimple(bgColor, lightColor, darkColor) {
  var color = (bgColor.charAt(0) === '#') ? bgColor.substring(1, 7) : bgColor;
  var r = parseInt(color.substring(0, 2), 16); // hexToR
  var g = parseInt(color.substring(2, 4), 16); // hexToG
  var b = parseInt(color.substring(4, 6), 16); // hexToB
  var uicolors = [r / 255, g / 255, b / 255];
  var c = uicolors.map((col) => {
    if (col <= 0.03928) {
      return col / 12.92;
    }
    return Math.pow((col + 0.055) / 1.055, 2.4);
  });
  var L = (0.2126 * c[0]) + (0.7152 * c[1]) + (0.0722 * c[2]);
  return (L > 0.179) ? darkColor : lightColor;
}
function checkIt(evt)
{
	evt = (evt) ? evt : window.event;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	var charCode = (evt.which) ? evt.which : evt.keyCode;

	if (charCode > 31 && (charCode < 48 || charCode > 57))
	{
		status = "This field accepts numbers only.";
		return false;
	}

	status = "";

	return true;
}

function checkimage(img)
{
	var ext = jQuery('#backgroundimage').val().split('.').pop().toLowerCase();
	if(jQuery.inArray(ext, ['gif','png','jpg','jpeg']) == -1)
	{
		jQuery('#backgroundimage').css( 'border' , '1px solid red' );
	}
}

function selectweight(sel,url,section,weight='')
{
	if(sel!=null)
	{
		var str = jQuery("#"+section+"_font-family").closest('select').find(':selected').val();
		var url = js_var.site_url;
		request_Url = url+'/wp-admin/admin-ajax.php?action=aw_subscribefont_ajax_request&data='+ str+'&weight='+weight;
		jQuery.ajax({
		url: request_Url,
		type:'POST',
		success:function(data){
			if(data=="")
			{
				jQuery("#title_font-weight").empty();
				jQuery("#title_font-weight").prop('disabled', true);
				jQuery("#labelweight").css("color","#666");
			}
			else
			{
				jQuery("#title_font-weight").removeAttr("disabled");//prop('disabled', false);
				jQuery("#"+section+'_font-weight').children().remove();
				jQuery("#"+section+'_font-weight').append(data);
				jQuery("#labelweight").css("color","#444");
			}
		},
		error: function(errorThrown){
			console.log(errorThrown);
		}
		});
	}
}

function set_bg_img_hint(template, edit='',backimage='')
{
	 
	var url = js_var.site_url+'/wp-content/plugins/popup-pro/admin/templates/images/';
	switch(template)	 
	{
		case 'aw-popup-template01':
		
		var hint = "Resolution of background image - width 451px, height 352px";
		jQuery('#title_tab').show();
		jQuery('#design_title_tab').show();
		jQuery('#subtitle_tab').hide();			//hide full subtitle tab
		jQuery('#design_subtitle_tab').hide();
		jQuery('#popup_pro_subscribe_button').addClass('required');
		jQuery('#subscribebutton_tab').show();
		jQuery('#design_subscribebutton_tab').show();
		if(edit=='no')
		{
			var default_template01 = {'title':'FOLLOW','button':'Subscribe'};
			jQuery.each(default_template01, function( name, value ) {
				jQuery('#popup_pro_subscribe_'+name).val(value);
			});

			var design_template01 = {'title':{'font-family':'Montserrat','font-size':'18','font-weight':'500','color':'FAFAFA'},'emailform':{'font-family':'Lato','font-size':'14','font-weight':'400','color':'161616'},'subscribebutton':{'font-family':'Lato','font-size':'14','font-weight':'400','color':'FAFAFA','border-radius':'5','button-color':'161616'},'closebutton':{'background-color':'161616','buttonsize':'13'},'background':{'background-color':'FFFFFF','display-image':backimage}};
		
			jQuery.each(design_template01, function( parentname, value ) {
				jQuery.each(value, function( subname, value ) {
					if(subname==='font-family')
					{
						jQuery('#'+parentname+'_'+subname).val(value).attr('selected', 'selected');
					}
					else if(subname==='font-weight')
					{
						selectweight(' ',js_var.site_url,parentname,value);
						jQuery('#'+parentname+'_'+subname).val(value).attr('selected', 'selected');
					}
					else
					{
						if(subname==='display-image' && value!='')
						{
							file = value;
							jQuery('#backgroundimage').attr('data-value',value);
	 						if(file!='')
							{
								jQuery("#closebackimage").show();
								jQuery('#'+parentname+'_'+subname).attr("src",file);
							}
						}
						else
							jQuery('#'+parentname+'_'+subname).val(value);
					}
				});
			});
		}
		break;
		
		case 'aw-popup-template02':
		var hint = "Resolution of background image - width 713px, height 333px";
		jQuery('#title_tab').hide();
		jQuery('#design_title_tab').hide();
		jQuery('#subtitle_tab').show();
		jQuery('#popup_pro_subscribe_button').addClass('required');
		jQuery('#design_subtitle_tab').show();
		jQuery('#subscribebutton_tab').show();
		jQuery('#design_subscribebutton_tab').show();
		if(edit=='no')
		{
			var default_template02 = {'title':'Subscribe','subtitle':'Admin text popup one two admin lorem lorem fish text','button':'Subscribe'};
			jQuery.each(default_template02, function( name, value ) {
				jQuery('#popup_pro_subscribe_'+name).val(value);
			});
			var design_template02 = {'title':{'font-family':'Lato','font-size':'21','font-weight':'700','color':'FAFAFA'},'subtitle':{'font-family':'Lato','font-size':'14','font-weight':'400','color':'161616'},'emailform':{'font-family':'Lato','font-size':'12','font-weight':'400','color':'161616'},'subscribebutton':{'font-family':'Lato','font-size':'14','font-weight':'600','color':'F5F5F5','border-radius':'0','button-color':'3184FF'},'closebutton':{'background-color':'161616','buttonsize':'13'},'background':{'background-color':'FFFFFF','display-image':backimage}};
			jQuery.each(design_template02, function( parentname, value ) {
					jQuery.each(value, function( subname, value ) {
					if(subname==='font-family')
					{
						jQuery('#'+parentname+'_'+subname).val(value).attr('selected', 'selected');
					}
					else if(subname==='font-weight')
					{
						selectweight(' ',js_var.site_url,parentname,value);
						jQuery('#'+parentname+'_'+subname).val(value).attr('selected', 'selected');
					}
					else
					{
						if(subname==='display-image')
						{
							file = value;
							jQuery('#backgroundimage').val(value);
							if(file!='')
							{
								jQuery("#closebackimage").show();
								jQuery('#'+parentname+'_'+subname).attr("src",file);	
							}
							
						}
						else
							jQuery('#'+parentname+'_'+subname).val(value);
					}
				});
			});
		}
		break;

		case 'aw-popup-template03':
		var hint = "Resolution of background image - width 191px, height 350px";
		jQuery('#title_tab').show();	
		jQuery('#design_title_tab').show();	
		jQuery('#subtitle_tab').show();
		jQuery('#popup_pro_subscribe_button').addClass('required');
		jQuery('#design_subtitle_tab').show();
		jQuery('#subscribebutton_tab').show();
		jQuery('#design_subscribebutton_tab').show();
		if(edit=='no')
		{
			var default_template03 = {'title':'Subscribe!','subtitle':'Lorem ipsum dolor sit amet, consetetur sadipscing elitr','button':'Subscribe'};
			jQuery.each(default_template03, function( name, value ) {
				jQuery('#popup_pro_subscribe_'+name).val(value);
			});
			var design_template03= {'title':{'font-family':'Montserrat','font-size':'26','font-weight':'700','color':'161616'},'subtitle':{'font-family':'Lato','font-size':'14','font-weight':'400','color':'161616'},'emailform':{'font-family':'Lato','font-size':'14','font-weight':'400','color':'FB66AA'},'subscribebutton':{'font-family':'Lato','font-size':'14','font-weight':'600','color':'F5F5F5','border-radius':'0','button-color':'FE489C'},'closebutton':{'background-color':'FF53A3','buttonsize':'12'},'background':{'background-color':'FFFFFF','display-image':backimage}};
			jQuery.each(design_template03, function( parentname, value ) {
					jQuery.each(value, function( subname, value ) {
					if(subname==='font-family')
					{
						jQuery('#'+parentname+'_'+subname).val(value).attr('selected', 'selected');
					}
					else if(subname==='font-weight')
					{
						selectweight(' ',js_var.site_url,parentname,value);
						jQuery('#'+parentname+'_'+subname).val(value).attr('selected', 'selected');
					}
					else
					{

						if(subname==='display-image')
						{
							file = value;
							jQuery('#backgroundimage').attr('data-value',value);
							if(file!='')
							{
								jQuery("#closebackimage").show();	
								jQuery('#'+parentname+'_'+subname).attr("src",file);
							}
						}
						else
							jQuery('#'+parentname+'_'+subname).val(value);
					}
				});
			});
		}
		break;

		case 'aw-popup-template04':
		var hint = "Resolution of background image - width 452px, height 274px";
		jQuery('#title_tab').show();
		jQuery('#design_title_tab').show();
		jQuery('#subtitle_tab').show();
		jQuery('#popup_pro_subscribe_button').addClass('required');
		jQuery('#design_subtitle_tab').show();
		jQuery('#subscribebutton_tab').show();
		jQuery('#design_subscribebutton_tab').show();
		if(edit=='no')
		{
			var default_template04 = {'title':'Subscribe!','subtitle':'Admin text start popup subscribe ready','button':'Subscribe'};
			jQuery.each(default_template04, function( name, value ) {
				jQuery('#popup_pro_subscribe_'+name).val(value);
			});
			var design_template04 = {'title':{'font-family':'Montserrat','font-size':'26','font-weight':'700','color':'161616'},'subtitle':{'font-family':'Lato','font-size':'14','font-weight':'400','color':'5E5E5E'},'emailform':{'font-family':'Lato','font-size':'14','font-weight':'400','color':'161616'},'subscribebutton':{'font-family':'Lato','font-size':'14','font-weight':'600','color':'161616','border-radius':'0','button-color':'FFFFFF'},'closebutton':{'background-color':'161616','buttonsize':'14'},'background':{'background-color':'F1F1F1','display-image':backimage}};
			jQuery.each(design_template04, function( parentname, value ) {
					jQuery.each(value, function( subname, value ) {
					if(subname==='font-family')
					{
						jQuery('#'+parentname+'_'+subname).val(value).attr('selected', 'selected');
					}
					else if(subname==='font-weight')
					{
						selectweight(' ',js_var.site_url,parentname,value);
						jQuery('#'+parentname+'_'+subname).val(value).attr('selected', 'selected');
					}

					else
					{

						if(subname==='display-image')
						{
							file = value;
							jQuery('#backgroundimage').attr('data-value',value);
							if(file!='')
							{
								jQuery("#closebackimage").show();
								jQuery('#'+parentname+'_'+subname).attr("src",file);
							}
						}
						else
							jQuery('#'+parentname+'_'+subname).val(value);
					}
				});
			});
		}
		break;
		
		case 'aw-popup-template05':
		var hint = "Resolution of background image - width 350px, height 350px";
		jQuery('#title_tab').show();
		jQuery('#design_title_tab').show();
		jQuery('#subtitle_tab').show();
		jQuery('#design_subtitle_tab').show();
		jQuery('#subscribebutton_tab').hide();
		jQuery('#design_subscribebutton_tab').hide();
		if(edit=='no')
		{
			var default_template05 = {'title':'Subscribe','subtitle':'Text popup test','button':''};
			jQuery.each(default_template05, function( name, value ) {
				jQuery('#popup_pro_subscribe_'+name).val(value);
			});
			var design_template05= {'title':{'font-family':'Lato','font-size':'26','font-weight':'400','color':'307CFF'},'subtitle':{'font-family':'Lato','font-size':'14','font-weight':'400','color':'161616'},'emailform':{'font-family':'Lato','font-size':'14','font-weight':'400','color':'307CFF'},'subscribebutton':{'font-family':'Lato','font-size':'14','font-weight':'600','color':'161616','border-radius':'0','button-color':'FFFFFF'},'closebutton':{'background-color':'161616','buttonsize':'15'},'background':{'background-color':'FFFFFF','display-image':backimage}};
			jQuery.each(design_template05, function( parentname, value ) {
				jQuery.each(value, function( subname, value ) {
					if(subname==='font-family')
					{
						jQuery('#'+parentname+'_'+subname).val(value).attr('selected', 'selected');
					}
					else if(subname==='font-weight')
					{
						selectweight(' ',js_var.site_url,parentname,value);
						jQuery('#'+parentname+'_'+subname).val(value).attr('selected', 'selected');
					}
					else
					{

						if(subname==='display-image')
						{
							file = value;
							jQuery('#backgroundimage').attr('data-value',value);
							if(file!='')
							{
								jQuery("#closebackimage").show();
								jQuery('#'+parentname+'_'+subname).attr("src",file);
							}
						}
						else
							jQuery('#'+parentname+'_'+subname).val(value);
					}
				});
			});		
		}
		jQuery('#popup_pro_subscribe_button').removeClass('required');
		break;
		
		case 'aw-popup-template06':
		var hint = "Resolution of background image - width 451px, height 316px";
		jQuery('#title_tab').show();
		jQuery('#design_title_tab').show();
		jQuery('#subtitle_tab').show();
		jQuery('#popup_pro_subscribe_button').addClass('required');
		jQuery('#design_subtitle_tab').show();
		jQuery('#subscribebutton_tab').show();
		jQuery('#design_subscribebutton_tab').show();
		if(edit=='no')
		{
			var default_template06 = {'title':'Subscribe!','subtitle':'Text admin popup one one one lorem one lorem popups start and finish!','button':'Subscribe'};
			jQuery.each(default_template06, function( name, value ) {
				jQuery('#popup_pro_subscribe_'+name).val(value);
			});
			var design_template06= {'title':{'font-family':'Roboto','font-size':'28','font-weight':'700','color':'333333'},'subtitle':{'font-family':'Oxygen','font-size':'14','font-weight':'400','color':'161616'},'emailform':{'font-family':'Lato','font-size':'14','font-weight':'400','color':'161616'},'subscribebutton':{'font-family':'Ubuntu','font-size':'14','font-weight':'400','color':'F5F5F5','border-radius':'0','button-color':'161616'},'closebutton':{'background-color':'161616','buttonsize':'15'},'background':{'background-color':'FFFFFF','display-image':backimage}};
			jQuery.each(design_template06, function( parentname, value ) {
				jQuery.each(value, function( subname, value ) {
					if(subname==='font-family')
					{
						jQuery('#'+parentname+'_'+subname).val(value).attr('selected', 'selected');
					}
					else if(subname==='font-weight')
					{
						selectweight(' ',js_var.site_url,parentname,value);
						jQuery('#'+parentname+'_'+subname).val(value).attr('selected', 'selected');
					}
					else
					{
						if(subname==='display-image')
						{
							file = value;
							jQuery('#backgroundimage').attr('data-value',value);
							if(file!='')
							{
								jQuery("#closebackimage").show();
								jQuery('#'+parentname+'_'+subname).attr("src",file);
							}
						}
						else
							jQuery('#'+parentname+'_'+subname).val(value);
					}
				});
			});	
		}
		break;
		
		case 'aw-popup-template07':
		var hint = "Resolution of background image - width 404px, height 540px";
		jQuery('#title_tab').show();
		jQuery('#design_title_tab').show();
		jQuery('#subtitle_tab').show();
		jQuery('#popup_pro_subscribe_button').addClass('required');
		jQuery('#design_subtitle_tab').show();
		jQuery('#subscribebutton_tab').show();
		jQuery('#design_subscribebutton_tab').show();
		if(edit=='no')
		{
			var default_template07 = {'title':'Subscribe now!','subtitle':'Text admin one two..','button':'SUBSCRIBE AND GET COUPON'};
			jQuery.each(default_template07, function( name, value ) {
				jQuery('#popup_pro_subscribe_'+name).val(value);
			});
			var design_template07= {'title':{'font-family':'Roboto','font-size':'26','font-weight':'500','color':'333333'},'subtitle':{'font-family':'Oxygen','font-size':'14','font-weight':'400','color':'161616'},'emailform':{'font-family':'Lato','font-size':'14','font-weight':'400','color':'017DD4'},'subscribebutton':{'font-family':'Ubuntu','font-size':'14','font-weight':'400','color':'F5F5F5','border-radius':'0','button-color':'3FA8F2'},'closebutton':{'background-color':'161616','buttonsize':'13'},'background':{'background-color':'FFFFFF','display-image':backimage}};
			jQuery.each(design_template07, function( parentname, value ) {
				jQuery.each(value, function( subname, value ) {
					if(subname==='font-family')
					{
						jQuery('#'+parentname+'_'+subname).val(value).attr('selected', 'selected');
					}
					else if(subname==='font-weight')
					{
						selectweight(' ',js_var.site_url,parentname,value);
						jQuery('#'+parentname+'_'+subname).val(value).attr('selected', 'selected');
					}
					else
					{
						if(subname==='display-image')
						{
							file = value;
							jQuery('#backgroundimage').attr('data-value',value);
							if(file!='')
							{
								jQuery("#closebackimage").show();
								jQuery('#'+parentname+'_'+subname).attr("src",file);
							}
						}
						else
							jQuery('#'+parentname+'_'+subname).val(value);
					}
				});
			});	
		}
		break;
	}
	jQuery("#bg_img_hint").text(hint);
}

function imagechange(sel,url)
{
	var url_main = js_var.site_url;
    var imgname= jQuery("#popup_pro_subscribe_template").find(':selected').attr('data-value');
    jQuery("#imageplaceholder").attr('src','');
    jQuery("#imageplaceholder").attr('src',url+imgname);
    jQuery("#popup_pro_subscribe_image").val(imgname);

    var templateurl = jQuery('#hidden_popup_link').val()+'&template='+jQuery("#popup_pro_subscribe_template").val();
    jQuery("#pop-pro-sub-link").attr("href",'');
    jQuery("#pop-pro-sub-link").attr("href",templateurl);

	var template = jQuery("#popup_pro_subscribe_template").find(':selected').val();
	var backimage = jQuery("#backgroundimage").attr('data-value');
	set_bg_img_hint(template,'no',backimage);

	if(template==='aw-popup-template02')
	{
		document.getElementById('title-tab').style.display = "none";
		tablinks = document.getElementsByClassName("tablinks");
		tabcontent = document.getElementsByClassName("tabcontent");
		for (i = 0; i < tabcontent.length; i++)
		{
			tabcontent[i].style.display = "none";
		}
		for(i = 0; i < tablinks.length; i++)
		{
			tablinks[i].className = tablinks[i].className.replace("active", "");
		}
		document.getElementById('subtitle-tab').style.display = "block";
		tablinks[1].className = 'tablinks active';
	}
	else
	{
		document.getElementById('subtitle-tab').style.display = "none";
		tablinks = document.getElementsByClassName("tablinks");
		tabcontent = document.getElementsByClassName("tabcontent");
		for (i = 0; i < tabcontent.length; i++)
		{
			tabcontent[i].style.display = "none";
		}
		for(i = 0; i < tablinks.length; i++)
		{
			tablinks[i].className = tablinks[i].className.replace("active", "");
		}
		document.getElementById('title-tab').style.display = "block";
		tablinks[0].className = 'tablinks active';
	}
 	jQuery('.jscolor').each(function(){
		colors = "#"+jQuery(this).val();
		fontcolor=pickTextColorBasedOnBgColorSimple(colors, '#FFFFFF', '#000000');
		jQuery(this).css({'background-color':'#'+jQuery(this).val(),'color':fontcolor}); 

	});
}

function resize_tb(product_count)
{
	var win_wid = jQuery(window).width();

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
		var TB_HEIGHT = 300;
	}

	setTimeout(function() {
		var title = jQuery("#TB_ajaxWindowTitle").text();
		var title_len = title.length;

		jQuery("#TB_window").css({
			marginLeft: '-' + parseInt((TB_WIDTH / 2), 10) + 'px',
			width: TB_WIDTH + 'px',
			marginTop: '-' + parseInt((TB_HEIGHT / 2), 10) + 'px',
			height: '',
			top: ''
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
	}, 1);
}

function resize_subs_popup()
{
	jQuery('#TB_window').attr('style', 'background-color:transparent !important');
	var win = jQuery( window ).width();
	var high = jQuery( document ).height();

	setTimeout(function() {
		jQuery("#TB_ajaxContent").css('height', '');
		jQuery('#TB_title').remove();
		var wid = jQuery("#template_width").text();

		var TB_WIDTH = wid;
		var TB_HEIGHT = 530;

			jQuery("#TB_window").css({
				marginLeft: '-' + parseInt((TB_WIDTH / 2), 10) + 'px',
				width: TB_WIDTH + 'px',
				marginTop: '-' + parseInt((TB_HEIGHT / 2), 10) + 'px',
				top: ''
		});
		jQuery("#TB_ajaxContent").css({
			width: TB_WIDTH + 'px'
		});
		}, 1);
}

function convertImgToBase64URL(url, callback, outputFormat){
    var img = new Image();
    img.crossOrigin = 'Anonymous';
    img.onload = function(){
        var canvas = document.createElement('CANVAS'),
        ctx = canvas.getContext('2d'), dataURL;
        canvas.height = img.height;
        canvas.width = img.width;
        ctx.drawImage(img, 0, 0);
        dataURL = canvas.toDataURL(outputFormat);
        callback(dataURL);
        canvas = null; 
    };
    img.src = url;
}

function do_popup_pro_subscribe(this_btn)
{
	return false;
}