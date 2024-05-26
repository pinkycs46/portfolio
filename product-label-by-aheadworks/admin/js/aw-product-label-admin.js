var url_admin = js_var.site_url;
var design1 = '';
var design2 = '';
var design3 = '';


jQuery(document).ready(function(){

  if (window.location.href.indexOf('new-label') > -1) {
    document.getElementById("myDropdown").addEventListener('click',function(event){
      event.stopPropagation();
    });
    document.getElementById("myDropdowntext").addEventListener('click',function(event){
      event.stopPropagation();
    });
    document.getElementById("con_css_area").addEventListener('click',function(event){
      event.stopPropagation();
    });
    document.getElementById("text_css_area").addEventListener('click',function(event){
      event.stopPropagation();
    });

    document.getElementById("myDropdown_medium").addEventListener('click',function(event){
      event.stopPropagation();
    });

    document.getElementById("myDropdown_small").addEventListener('click',function(event){
      event.stopPropagation();
    });

    document.getElementById("myDropdowntext_medium").addEventListener('click',function(event){
      event.stopPropagation();
    });
    document.getElementById("myDropdowntext_small").addEventListener('click',function(event){
      event.stopPropagation();
    });

    document.getElementById("con_css_area_medium").addEventListener('click',function(event){
      event.stopPropagation();
    });
    document.getElementById("text_css_area_medium").addEventListener('click',function(event){
      event.stopPropagation();
    });

    document.getElementById("con_css_area_small").addEventListener('click',function(event){
      event.stopPropagation();
    });
    document.getElementById("text_css_area_small").addEventListener('click',function(event){
      event.stopPropagation();
    });

    jQuery('.caret-icon').on('click', function () {
        if (jQuery(this).hasClass('fa-caret-down')) {
            jQuery(this).removeClass('fa-caret-down').addClass('fa-caret-up');
            jQuery(".dropdown-content").css({'display':'block'});
        }else{
            jQuery(this).removeClass('fa-caret-up').addClass('fa-caret-down');
            jQuery(".dropdown-content").css({'display':'none'});
        }
    });

    jQuery('.caret-iconn').on('click', function () {
        if (jQuery(this).hasClass('fa-caret-down')) {
            jQuery(this).removeClass('fa-caret-down').addClass('fa-caret-up');
            jQuery(".dropdown-text-content").css({'display':'block'});
        }else{
            jQuery(this).removeClass('fa-caret-up').addClass('fa-caret-down');
            jQuery(".dropdown-text-content").css({'display':'none'});
        }
    });
    

    var shapename     = jQuery("#label_shape_type").find(':selected').attr('data-value');
    
    if(document.getElementById('label_large').checked == true)
    {
      if(document.getElementById('next_price').checked == true) 
      {
        if(document.getElementById('label_shape').checked == true)
        {
          var str = jQuery("#shapenextlabeltext1").text();
        }
        else if(document.getElementById('label_text').checked == true)
        {
          var str = jQuery("#nextlabeltext1").text();
        } 
        else if(document.getElementById('label_picture').checked == true)
        {
          var str = jQuery("#nextpictext1").text();
        }       
      }
      else
      {
        if(document.getElementById('label_shape').checked == true)
        {
          var str = jQuery("#shapelabeltext1").text();
        }
        else if(document.getElementById('label_text').checked == true) 
        {
          var str = jQuery("#labeltext1").text();
        }
        else if(document.getElementById('label_picture').checked == true) 
        {
          var str = jQuery("#pictext1").text();
        }
        
      }
      
      jQuery("#source_text").val(str);

      jQuery("#source_text").on("input", function()
      {      
        if(document.getElementById('next_price').checked == true) 
        {
          if(document.getElementById('label_shape').checked == true)
          {
            jQuery("#shapenextlabeltext2").text('Sale');
            jQuery("#shapenextlabeltext3").text('Sale');
            jQuery("#shapenextlabeltext1").text(jQuery(this).val()); 
          }
          else if(document.getElementById('label_text').checked == true) 
          {
            jQuery("#nextlabeltext2").text('Sale');
            jQuery("#nextlabeltext3").text('Sale');
            jQuery("#nextlabeltext1").text(jQuery(this).val());                  
          }
          else if(document.getElementById('label_picture').checked == true) 
          {
            jQuery("#nextpictext2").text('Sale');
            jQuery("#nextpictext3").text('Sale');
            jQuery("#nextpictext1").text(jQuery(this).val());                  
          }
        }
        else
        {
           if(document.getElementById('label_shape').checked == true)
          {
            jQuery("#shapelabeltext2").text('Sale');
            jQuery("#shapelabeltext3").text('Sale');
            jQuery("#shapelabeltext1").text(jQuery(this).val()); 
          }
          else if(document.getElementById('label_text').checked == true)
          {
            jQuery("#labeltext2").text('Sale');
            jQuery("#labeltext3").text('Sale');
            jQuery("#labeltext1").text(jQuery(this).val());
          }
          else if(document.getElementById('label_picture').checked == true)
          {
            jQuery("#pictext2").text('Sale');
            jQuery("#pictext3").text('Sale');
            jQuery("#pictext1").text(jQuery(this).val());
          }
        }
      });
    }
    else if(document.getElementById('label_medium').checked == true)
    {
      if(document.getElementById('next_price').checked == true) 
      {
        if(document.getElementById('label_shape').checked == true)
        {
          var str = jQuery("#shapenextlabeltext2").text();
        }
        else if(document.getElementById('label_text').checked == true)
        {
          var str = jQuery("#nextlabeltext2").text();
        }
        else if(document.getElementById('label_picture').checked == true)
        {
          var str = jQuery("#nextpictext2").text();
        }
      }
      else
      {
        if(document.getElementById('label_shape').checked == true)
        {
          var str = jQuery("#shapelabeltext2").text();
        }
        else if(document.getElementById('label_text').checked == true) 
        {
          var str = jQuery("#labeltext2").text();
        }
        else if(document.getElementById('label_picture').checked == true) 
        {
          var str = jQuery("#pictext2").text();
        }
      }
      jQuery("#source_text").val(str);

      jQuery("#source_text").on("input", function()
      {
       
        if(document.getElementById('next_price').checked == true) 
        {
          if(document.getElementById('label_shape').checked == true)
          {
            jQuery("#shapenextlabeltext1").text('Sale');
            jQuery("#shapenextlabeltext3").text('Sale');
            jQuery("#shapenextlabeltext2").text(jQuery(this).val());
          }
          else if(document.getElementById('label_text').checked == true) 
          {
            jQuery("#nextlabeltext1").text('Sale');
            jQuery("#nextlabeltext3").text('Sale');
            jQuery("#nextlabeltext2").text(jQuery(this).val());
          }
          else if(document.getElementById('label_picture').checked == true) 
          {
            jQuery("#nextpictext1").text('Sale');
            jQuery("#nextpictext3").text('Sale');
            jQuery("#nextpictext2").text(jQuery(this).val());
          }

        }
        else
        {
          if(document.getElementById('label_shape').checked == true)
          {
            jQuery("#shapelabeltext1").text('Sale');
            jQuery("#shapelabeltext3").text('Sale');
            jQuery("#shapelabeltext2").text(jQuery(this).val());
          }
          else if(document.getElementById('label_text').checked == true)
          {
            jQuery("#labeltext1").text('Sale');
            jQuery("#labeltext3").text('Sale');
            jQuery("#labeltext2").text(jQuery(this).val());
          }
          else if(document.getElementById('label_picture').checked == true)
          {
            jQuery("#pictext1").text('Sale');
            jQuery("#pictext3").text('Sale');
            jQuery("#pictext2").text(jQuery(this).val());
          }
        }
      });
    }
    else if(document.getElementById('label_small').checked == true)
    {
      if(document.getElementById('next_price').checked == true) 
      {
        if(document.getElementById('label_shape').checked == true)
        {
          var str = jQuery("#shapenextlabeltext3").text();
        }
        else if(document.getElementById('label_text').checked == true)
        {
          var str = jQuery("#nextlabeltext3").text();
        }
        else if(document.getElementById('label_picture').checked == true)
        {
          var str = jQuery("#nextpictext3").text();
        }
      }
      else
      {
        if(document.getElementById('label_shape').checked == true)
        {
          var str = jQuery("#shapelabeltext3").text();
        }
        else if(document.getElementById('label_text').checked == true) 
        {
          var str = jQuery("#labeltext3").text();
        }
        else if(document.getElementById('label_picture').checked == true) 
        {
          var str = jQuery("#pictext3").text();
        }
      }
      jQuery("#source_text").val(str);

      jQuery("#source_text").on("input", function()
      {
        if(document.getElementById('next_price').checked == true) 
        {
          if(document.getElementById('label_shape').checked == true)
          {
            jQuery("#shapenextlabeltext1").text('Sale');
            jQuery("#shapenextlabeltext2").text('Sale');
            jQuery("#shapenextlabeltext3").text(jQuery(this).val());
          }
          else if(document.getElementById('label_text').checked == true) 
          {
            jQuery("#nextlabeltext1").text('Sale');
            jQuery("#nextlabeltext2").text('Sale');
            jQuery("#nextlabeltext3").text(jQuery(this).val());
          }
          else if(document.getElementById('label_picture').checked == true) 
          {
            jQuery("#nextpictext1").text('Sale');
            jQuery("#nextpictext2").text('Sale');
            jQuery("#nextpictext3").text(jQuery(this).val());
          }
        }
        else
        {        
          if(document.getElementById('label_shape').checked == true)
          {
            jQuery("#shapelabeltext1").text('Sale');
            jQuery("#shapelabeltext2").text('Sale');
            jQuery("#shapelabeltext3").text(jQuery(this).val());
          }
          else if(document.getElementById('label_text').checked == true) 
          {
            jQuery("#labeltext1").text('Sale');
            jQuery("#labeltext2").text('Sale');
            jQuery("#labeltext3").text(jQuery(this).val());
          }
          else if(document.getElementById('label_picture').checked == true) 
          {
            jQuery("#pictext1").text('Sale');
            jQuery("#pictext2").text('Sale');
            jQuery("#pictext3").text(jQuery(this).val());
          }
        }        
      });
    }
 
    if(document.getElementById('next_price').checked == true) 
    {
      jQuery("#shapeplaceholder1").attr('class','');
      jQuery("#shapeplaceholder2").attr('class','');
      jQuery("#shapeplaceholder3").attr('class','');

      jQuery("#shapeposition1").css("display","none");
      jQuery("#shapeposition2").css("display","none");
      jQuery("#shapeposition3").css("display","none");

      jQuery("#display_picture1").css("display","none"); 
      jQuery("#display_picture2").css("display","none"); 
      jQuery("#display_picture3").css("display","none");

      jQuery("#labeltext1").css({'display':'none'});
      jQuery("#labeltext2").css({'display':'none'}); 
      jQuery("#labeltext3").css({'display':'none'});

      jQuery("#shapelabeltext1").css({'display':'none'});
      jQuery("#shapelabeltext2").css({'display':'none'}); 
      jQuery("#shapelabeltext3").css({'display':'none'});

      jQuery("#nextlabeltext1").css({'display':'none'});
      jQuery("#nextlabeltext2").css({'display':'none'}); 
      jQuery("#nextlabeltext3").css({'display':'none'});

      if(document.getElementById('label_shape').checked == true) 
      {
        var shapename = jQuery("#label_shape_type").find(':selected').attr('data-value'); 

        jQuery("#image_tab").css("display","none");
        jQuery("#shape_tab").css("display","block");

        jQuery("#next_display_picture1").css("display","none"); 
        jQuery("#next_display_picture2").css("display","none"); 
        jQuery("#next_display_picture3").css("display","none"); 

        jQuery("#shapenextlabeltext1").css({'display':'block'});
        jQuery("#shapenextlabeltext2").css({'display':'block'}); 
        jQuery("#shapenextlabeltext3").css({'display':'block'});

        jQuery("#shapenextposition1").css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapenextposition1").css({'display':'block','position':'absolute','left':'0px','top':'0px'});

        jQuery("#shapenextposition2").css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapenextposition2").css({'display':'block','position':'absolute','left':'0px','top':'0px'});

        jQuery("#shapenextposition3").css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapenextposition3").css({'display':'block','position':'absolute','left':'0px','top':'0px'});

        jQuery("#shapenextprice1").attr('class','');
        jQuery("#shapenextprice1").attr('class',shapename);

        jQuery("#shapenextprice2").attr('class','');
        jQuery("#shapenextprice2").attr('class',shapename);

        jQuery("#shapenextprice3").attr('class','');
        jQuery("#shapenextprice3").attr('class',shapename);
      }
      else if(document.getElementById('label_picture').checked == true) 
      {
        jQuery("#image_tab").css("display","block");
        jQuery("#shape_tab").css("display","none");

        jQuery("#shapenextprice1").attr('class','');
        jQuery("#shapenextprice2").attr('class','');
        jQuery("#shapenextprice3").attr('class','');

        jQuery("#shapenextlabeltext1").css("display","none"); 
        jQuery("#shapenextlabeltext2").css("display","none"); 
        jQuery("#shapenextlabeltext3").css("display","none");

        jQuery("#next_display_picture1").css("display","inline-block"); 
        jQuery("#next_display_picture2").css("display","inline-block"); 
        jQuery("#next_display_picture3").css("display","inline-block"); 

        jQuery("#nextpictext1").css({'display':'block'});
        jQuery("#nextpictext2").css({'display':'block'}); 
        jQuery("#nextpictext3").css({'display':'block'});

      }
      else if(document.getElementById('label_text').checked == true) 
      {

        jQuery("#image_tab").css("display","none");
        jQuery("#shape_tab").css("display","none");

        jQuery("#shapenextprice1").attr('class','');
        jQuery("#shapenextprice2").attr('class','');
        jQuery("#shapenextprice3").attr('class','');

        jQuery("#shapenextposition1").css("display","none"); 
        jQuery("#shapenextposition2").css("display","none"); 
        jQuery("#shapenextposition3").css("display","none");

        jQuery("#shapenextlabeltext1").css("display","none"); 
        jQuery("#shapenextlabeltext2").css("display","none"); 
        jQuery("#shapenextlabeltext3").css("display","none");

        jQuery("#next_display_picture1").css("display","none"); 
        jQuery("#next_display_picture2").css("display","none"); 
        jQuery("#next_display_picture3").css("display","none"); 

        jQuery("#nextlabeltext1").css({'display':'block'});
        jQuery("#nextlabeltext2").css({'display':'block'}); 
        jQuery("#nextlabeltext3").css({'display':'block'});
      }
    }
    else 
    {
      var shapename = jQuery("#label_shape_type").find(':selected').attr('data-value');
      
      jQuery("#shapenextposition1").css("display","none");
      jQuery("#shapenextposition2").css("display","none");
      jQuery("#shapenextposition3").css("display","none");

      jQuery("#next_display_picture1").css("display","none"); 
      jQuery("#next_display_picture2").css("display","none"); 
      jQuery("#next_display_picture3").css("display","none"); 

      jQuery("#nextlabeltext1").css({'display':'none'});
      jQuery("#nextlabeltext2").css({'display':'none'}); 
      jQuery("#nextlabeltext3").css({'display':'none'});

      jQuery("#shapenextlabeltext1").css("display","none"); 
      jQuery("#shapenextlabeltext2").css("display","none"); 
      jQuery("#shapenextlabeltext3").css("display","none");

      if(document.getElementById('upper_right').checked == true)  
      {
        
        if(document.getElementById('label_shape').checked == true) 
        {      
          jQuery("#image_tab").css("display","none");
          jQuery("#shape_tab").css("display","block");

          jQuery("#display_picture1").css("display","none"); 
          jQuery("#display_picture2").css("display","none"); 
          jQuery("#display_picture3").css("display","none");

          jQuery("#labeltext1").css("display","none"); 
          jQuery("#labeltext2").css("display","none"); 
          jQuery("#labeltext3").css("display","none");

          jQuery("#shapeposition1").css({'left' : '', 'bottom' : '' ,'top':''});
          jQuery("#shapeposition1").css({'display':'block','position':'absolute','right':'0px','top':'0px'});

          jQuery("#shapeposition2").css({'left' : '', 'bottom' : '' ,'top':''});
          jQuery("#shapeposition2").css({'display':'block','position':'absolute','right':'0px','top':'0px'});

          jQuery("#shapeposition3").css({'left' : '', 'bottom' : '' ,'top':''});
          jQuery("#shapeposition3").css({'display':'block','position':'absolute','right':'0px','top':'0px'});

          jQuery("#shapelabeltext1").css({'display':'block'});
          jQuery("#shapelabeltext2").css({'display':'block'});
          jQuery("#shapelabeltext3").css({'display':'block'});

          jQuery("#shapeplaceholder1").attr('class','');
          jQuery("#shapeplaceholder1").attr('class',shapename);

          jQuery("#shapeplaceholder2").attr('class','');
          jQuery("#shapeplaceholder2").attr('class',shapename);

          jQuery("#shapeplaceholder3").attr('class','');
          jQuery("#shapeplaceholder3").attr('class',shapename);

          if('rectangle_belevel_down' == shapename)
          {
            jQuery('.rectangle_belevel_down').addClass('down_right');
          }
          if('rectangle_belevel_up' == shapename)
          {
            jQuery('.rectangle_belevel_up').addClass('up_right');
          } 
        }
        else  if(document.getElementById('label_picture').checked == true) 
        {
          
          jQuery("#image_tab").css("display","block");
          jQuery("#shape_tab").css("display","none");

          jQuery("#shapeplaceholder1").attr('class','');
          jQuery("#shapeplaceholder2").attr('class','');
          jQuery("#shapeplaceholder3").attr('class',''); 

          jQuery("#shapelabeltext1").css("display","none"); 
          jQuery("#shapelabeltext2").css("display","none"); 
          jQuery("#shapelabeltext3").css("display","none");

          jQuery("#display_picture1").css("display","block"); 
          jQuery("#display_picture2").css("display","block"); 
          jQuery("#display_picture3").css("display","block");

          jQuery("#labeltext1").css("display","none"); 
          jQuery("#labeltext2").css("display","none"); 
          jQuery("#labeltext3").css("display","none");

          jQuery('#display_picture1 ').css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
          jQuery('#display_picture1 ').css({'position': 'absolute','right':'0px','top':'0px'});

          jQuery('#display_picture2 ').css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
          jQuery('#display_picture2').css({'position': 'absolute','right':'0px','top':'0px'});

          jQuery('#display_picture3').css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
          jQuery('#display_picture3').css({'position': 'absolute','right':'0px','top':'0px'});

          jQuery("#pictext1").css({ 'right' : '','left' : '', 'bottom' : '' ,'top':''});
          jQuery("#pictext1").css({'display':'block'});

          jQuery("#pictext2").css({'right' : '', 'left' : '', 'bottom' : '' ,'top':''});
          jQuery("#pictext2").css({'display':'block'});

          jQuery("#pictext3").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
          jQuery("#pictext3").css({'display':'block'});

        }
        else  if(document.getElementById('label_text').checked == true) 
        {
          jQuery("#image_tab").css("display","none");
          jQuery("#shape_tab").css("display","none");

          jQuery("#shapeplaceholder1").attr('class','');
          jQuery("#shapeplaceholder2").attr('class','');
          jQuery("#shapeplaceholder3").attr('class','');

          jQuery("#shapelabeltext1").css("display","none"); 
          jQuery("#shapelabeltext2").css("display","none"); 
          jQuery("#shapelabeltext3").css("display","none");

          jQuery("#display_picture1").css("display","none"); 
          jQuery("#display_picture2").css("display","none"); 
          jQuery("#display_picture3").css("display","none");

          jQuery("#labeltext1").css({ 'right' : '','left' : '', 'bottom' : '' ,'top':''});
          jQuery("#labeltext1").css({'position': 'absolute','right':'0px', 'top':'0px'});

          jQuery("#labeltext2").css({'right' : '', 'left' : '', 'bottom' : '' ,'top':''});
          jQuery("#labeltext2").css({'position': 'absolute','right':'0px', 'top':'0px'});

          jQuery("#labeltext3").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
          jQuery("#labeltext3").css({'position': 'absolute','right':'0px', 'top':'0px'});
        }
      }
      else if(document.getElementById('upper_left').checked == true) 
      {
        if(document.getElementById('label_shape').checked == true) 
        {
          jQuery("#image_tab").css("display","none");
          jQuery("#shape_tab").css("display","block");

          jQuery("#display_picture1").css("display","none"); 
          jQuery("#display_picture2").css("display","none"); 
          jQuery("#display_picture3").css("display","none");

          jQuery("#labeltext1").css("display","none"); 
          jQuery("#labeltext2").css("display","none"); 
          jQuery("#labeltext3").css("display","none");

          jQuery("#shapeposition1").css({'right' : '', 'bottom' : '' ,'top':''});
          jQuery("#shapeposition1").css({'display':'block','position':'absolute','left':'0px','top':'0px'});

          jQuery("#shapeposition2").css({'right' : '', 'bottom' : '' ,'top':''});
          jQuery("#shapeposition2").css({'display':'block','position':'absolute','left':'0px','top':'0px'});

          jQuery("#shapeposition3").css({'right' : '', 'bottom' : '' ,'top':''});
          jQuery("#shapeposition3").css({'display':'block','position':'absolute','left':'0px','top':'0px'});
          

          jQuery("#shapelabeltext1").css({'display':'block'});
          jQuery("#shapelabeltext2").css({'display':'block'});
          jQuery("#shapelabeltext3").css({'display':'block'});

          jQuery("#shapeplaceholder1").attr('class','');
          jQuery("#shapeplaceholder1").attr('class',shapename);

          jQuery("#shapeplaceholder2").attr('class','');
          jQuery("#shapeplaceholder2").attr('class',shapename);

          jQuery("#shapeplaceholder3").attr('class','');
          jQuery("#shapeplaceholder3").attr('class',shapename);

        }
        else  if(document.getElementById('label_picture').checked == true) 
        {

          jQuery("#image_tab").css("display","block");
          jQuery("#shape_tab").css("display","none");

          jQuery("#shapeplaceholder1").attr('class','');
          jQuery("#shapeposition1").css("display","none");
          
          jQuery("#shapeplaceholder2").attr('class','');
          jQuery("#shapeposition2").css("display","none");

          jQuery("#shapeplaceholder3").attr('class',''); 
          jQuery("#shapeposition3").css("display","none");

          jQuery("#labeltext1").css("display","none"); 
          jQuery("#labeltext2").css("display","none"); 
          jQuery("#labeltext3").css("display","none");

          jQuery("#display_picture1").css("display","block"); 
          jQuery("#display_picture2").css("display","block"); 
          jQuery("#display_picture3").css("display","block");

          jQuery('#display_picture1 ').css({'right' : '', 'bottom' : '' ,'top':''});
          jQuery('#display_picture1 ').css({'position': 'absolute','left':'0px','top':'0px'});

          jQuery('#display_picture2 ').css({'right' : '', 'bottom' : '' ,'top':''});
          jQuery('#display_picture2 ').css({'position': 'absolute','left':'0px','top':'0px'});

          jQuery('#display_picture3 ').css({'right' : '', 'bottom' : '' ,'top':''});
          jQuery('#display_picture3 ').css({'position': 'absolute','left':'0px','top':'0px'});

          jQuery("#pictext1").css({ 'right' : '','left' : '', 'bottom' : '' ,'top':''});
          jQuery("#pictext1").css({'display':'block'});

          jQuery("#pictext2").css({'right' : '', 'left' : '', 'bottom' : '' ,'top':''});
          jQuery("#pictext2").css({'display':'block'});

          jQuery("#pictext3").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
          jQuery("#pictext3").css({'display':'block'});

        }
        else if(document.getElementById('label_text').checked == true) 
        {
          jQuery("#image_tab").css("display","none");
          jQuery("#shape_tab").css("display","none");

          jQuery("#shapeplaceholder1").attr('class','');
          jQuery("#shapeplaceholder2").attr('class','');
          jQuery("#shapeplaceholder3").attr('class',''); 

          jQuery("#display_picture1").css("display","none"); 
          jQuery("#display_picture2").css("display","none"); 
          jQuery("#display_picture3").css("display","none");

          jQuery("#shapelabeltext1").css("display","none"); 
          jQuery("#shapelabeltext2").css("display","none"); 
          jQuery("#shapelabeltext3").css("display","none");

          jQuery("#labeltext1").css({ 'right' : '','left' : '', 'bottom' : '' ,'top':''});
          jQuery("#labeltext1").css({'display':'block','position': 'absolute','left':'0px', 'top':'0px'});

          jQuery("#labeltext2").css({'right' : '', 'left' : '', 'bottom' : '' ,'top':''});
          jQuery("#labeltext2").css({'display':'block','position': 'absolute','left':'0px', 'top':'0px'});

          jQuery("#labeltext3").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
          jQuery("#labeltext3").css({'display':'block','position': 'absolute','left':'0px', 'top':'0px'});
        }
      }
      else if(document.getElementById('lower_left').checked == true)  
      {      
        if(document.getElementById('label_shape').checked == true) 
        {
          jQuery("#image_tab").css("display","none");
          jQuery("#shape_tab").css("display","block");

          jQuery("#display_picture1").css("display","none"); 
          jQuery("#display_picture2").css("display","none"); 
          jQuery("#display_picture3").css("display","none");

          jQuery("#labeltext1").css("display","none"); 
          jQuery("#labeltext2").css("display","none"); 
          jQuery("#labeltext3").css("display","none");

          jQuery("#shapeposition1").css({'right' : '', 'bottom' : '' ,'top':''});
          jQuery("#shapeposition1").css({'display':'block','position':'absolute','left':'0px','bottom':'0px'});

          jQuery("#shapeposition2").css({'right' : '', 'bottom' : '' ,'top':''});
          jQuery("#shapeposition2").css({'display':'block','position':'absolute','left':'0px','bottom':'0px'});

          jQuery("#shapeposition3").css({'right' : '', 'bottom' : '' ,'top':''});
          jQuery("#shapeposition3").css({'display':'block','position':'absolute','left':'0px','bottom':'0px'});
          

          jQuery("#shapelabeltext1").css({'display':'block'});
          jQuery("#shapelabeltext2").css({'display':'block'});
          jQuery("#shapelabeltext3").css({'display':'block'});

          jQuery("#shapeplaceholder1").attr('class','');
          jQuery("#shapeplaceholder1").attr('class',shapename);

          jQuery("#shapeplaceholder2").attr('class','');
          jQuery("#shapeplaceholder2").attr('class',shapename);

          jQuery("#shapeplaceholder3").attr('class','');
          jQuery("#shapeplaceholder3").attr('class',shapename);
    
        } 
        else if(document.getElementById('label_picture').checked == true) 
        {
          jQuery("#image_tab").css("display","block");
          jQuery("#shape_tab").css("display","none");

          jQuery("#shapeplaceholder1").attr('class','');
          jQuery("#shapeplaceholder2").attr('class','');
          jQuery("#shapeplaceholder3").attr('class','');

          jQuery("#shapelabeltext1").css("display","none"); 
          jQuery("#shapelabeltext2").css("display","none"); 
          jQuery("#shapelabeltext3").css("display","none");

          jQuery("#labeltext1").css("display","none"); 
          jQuery("#labeltext2").css("display","none"); 
          jQuery("#labeltext3").css("display","none");

          jQuery("#display_picture1").css("display","block"); 
          jQuery("#display_picture2").css("display","block"); 
          jQuery("#display_picture3").css("display","block");

          jQuery('#display_picture1 ').css({'right' : '', 'bottom' : '' ,'top':''});
          jQuery('#display_picture1 ').css({'position': 'absolute','left':'0px', 'bottom':'0px'});

          jQuery('#display_picture2 ').css({'right' : '', 'bottom' : '' ,'top':''});
          jQuery('#display_picture2 ').css({'position': 'absolute','left':'0px', 'bottom':'0px'});

          jQuery('#display_picture3 ').css({'right' : '', 'bottom' : '' ,'top':''});
          jQuery('#display_picture3 ').css({'position': 'absolute','left':'0px', 'bottom':'0px'});


          jQuery("#pictext1").css({ 'right' : '','left' : '', 'bottom' : '' ,'top':''});
          jQuery("#pictext1").css({'display':'block'});

          jQuery("#pictext2").css({'right' : '', 'left' : '', 'bottom' : '' ,'top':''});
          jQuery("#pictext2").css({'display':'block'});

          jQuery("#pictext3").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
          jQuery("#pictext3").css({'display':'block'});
        } 
        else if(document.getElementById('label_text').checked == true)
        {
          jQuery("#image_tab").css("display","none");
          jQuery("#shape_tab").css("display","none");

          jQuery("#shapeplaceholder1").attr('class','');
          jQuery("#shapeplaceholder2").attr('class','');
          jQuery("#shapeplaceholder3").attr('class','');

          jQuery("#display_picture1").css("display","none"); 
          jQuery("#display_picture2").css("display","none"); 
          jQuery("#display_picture3").css("display","none");

          jQuery("#shapelabeltext1").css("display","none"); 
          jQuery("#shapelabeltext2").css("display","none"); 
          jQuery("#shapelabeltext3").css("display","none");

          jQuery("#labeltext1").css({ 'right' : '','left' : '', 'bottom' : '' ,'top':''});
          jQuery("#labeltext1").css({'display':'block','position': 'absolute','left':'0px', 'bottom':'0px'});

          jQuery("#labeltext2").css({'right' : '', 'left' : '', 'bottom' : '' ,'top':''});
          jQuery("#labeltext2").css({'display':'block','position': 'absolute','left':'0px', 'bottom':'0px'});

          jQuery("#labeltext3").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
          jQuery("#labeltext3").css({'display':'block','position': 'absolute','left':'0px', 'bottom':'0px'});
        }
      }
      else if(document.getElementById('lower_right').checked == true) 
      {
        
        if(document.getElementById('label_shape').checked == true) 
        {
          jQuery("#image_tab").css("display","none");
          jQuery("#shape_tab").css("display","block");

          jQuery("#display_picture1").css("display","none"); 
          jQuery("#display_picture2").css("display","none"); 
          jQuery("#display_picture3").css("display","none");

          jQuery("#labeltext1").css("display","none"); 
          jQuery("#labeltext2").css("display","none"); 
          jQuery("#labeltext3").css("display","none");

          jQuery("#shapeposition1").css({'left' : '', 'bottom' : '' ,'top':''});
          jQuery("#shapeposition1").css({'display':'block','position':'absolute','right':'0px','bottom':'0px'});

          jQuery("#shapeposition2").css({'left' : '', 'bottom' : '' ,'top':''});
          jQuery("#shapeposition2").css({'display':'block','position':'absolute','right':'0px','bottom':'0px'});

          jQuery("#shapeposition3").css({'left' : '', 'bottom' : '' ,'top':''});
          jQuery("#shapeposition3").css({'display':'block','position':'absolute','right':'0px','bottom':'0px'});
          

          jQuery("#shapelabeltext1").css({'display':'block'});
          jQuery("#shapelabeltext2").css({'display':'block'});
          jQuery("#shapelabeltext3").css({'display':'block'});

          jQuery("#shapeplaceholder1").attr('class','');
          jQuery("#shapeplaceholder1").attr('class',shapename);

          jQuery("#shapeplaceholder2").attr('class','');
          jQuery("#shapeplaceholder2").attr('class',shapename);

          jQuery("#shapeplaceholder3").attr('class','');
          jQuery("#shapeplaceholder3").attr('class',shapename);

          if('rectangle_belevel_down' == shapename)
          {
            jQuery('.rectangle_belevel_down').addClass('down_right');
          }
          if('rectangle_belevel_up' == shapename)
          {
            jQuery('.rectangle_belevel_up').addClass('up_right');
          }
        }
        else if(document.getElementById('label_picture').checked == true) 
        {
          jQuery("#image_tab").css("display","block");
          jQuery("#shape_tab").css("display","none");
          
          jQuery("#shapeplaceholder1").attr('class','');
          jQuery("#shapeplaceholder2").attr('class','');
          jQuery("#shapeplaceholder3").attr('class','');

          jQuery("#shapelabeltext1").css("display","none"); 
          jQuery("#shapelabeltext2").css("display","none"); 
          jQuery("#shapelabeltext3").css("display","none");

          jQuery("#labeltext1").css("display","none"); 
          jQuery("#labeltext2").css("display","none"); 
          jQuery("#labeltext3").css("display","none");

          jQuery("#display_picture1").css("display","block"); 
          jQuery("#display_picture2").css("display","block"); 
          jQuery("#display_picture3").css("display","block");

          jQuery('#display_picture1').css({'left' : '', 'bottom' : '' ,'top':''});
          jQuery('#display_picture1 ').css({'position': 'absolute','right':'0px', 'bottom':'0px'});

          jQuery('#display_picture2 ').css({'left' : '', 'bottom' : '' ,'top':''});
          jQuery('#display_picture2 ').css({'position': 'absolute','right':'0px', 'bottom':'0px'});

          jQuery('#display_picture3 ').css({'left' : '', 'bottom' : '' ,'top':''});
          jQuery('#display_picture3 ').css({'position': 'absolute','right':'0px', 'bottom':'0px'});

          jQuery("#pictext1").css({ 'right' : '','left' : '', 'bottom' : '' ,'top':''});
          jQuery("#pictext1").css({'display':'block'});

          jQuery("#pictext2").css({'right' : '', 'left' : '', 'bottom' : '' ,'top':''});
          jQuery("#pictext2").css({'display':'block'});

          jQuery("#pictext3").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
          jQuery("#pictext3").css({'display':'block'});

        }
        else if(document.getElementById('label_text').checked == true) 
        {
          jQuery("#image_tab").css("display","none");
          jQuery("#shape_tab").css("display","none");

          jQuery("#shapeplaceholder1").attr('class','');
          jQuery("#shapeplaceholder2").attr('class','');
          jQuery("#shapeplaceholder3").attr('class','');

          jQuery("#display_picture1").css("display","none"); 
          jQuery("#display_picture2").css("display","none"); 
          jQuery("#display_picture3").css("display","none");

          jQuery("#shapelabeltext1").css("display","none"); 
          jQuery("#shapelabeltext2").css("display","none"); 
          jQuery("#shapelabeltext3").css("display","none");

          jQuery("#labeltext1").css({ 'right' : '','left' : '', 'bottom' : '' ,'top':''});
          jQuery("#labeltext1").css({'display':'block','position': 'absolute','right':'0px', 'bottom':'0px'});

          jQuery("#labeltext2").css({'right' : '', 'left' : '', 'bottom' : '' ,'top':''});
          jQuery("#labeltext2").css({'display':'block','position': 'absolute','right':'0px', 'bottom':'0px'});

          jQuery("#labeltext3").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
          jQuery("#labeltext3").css({'display':'block','position': 'absolute','right':'0px', 'bottom':'0px'});
        }
      }
    }

  }
  jQuery("#closebackimage").css("display","none");
  var one = jQuery("#pl_label_image").val();
  if(one !== "") {
    jQuery("#closebackimage").css("display","block");
  }

  jQuery("#pl_label_icon").change(function() {
    var file = jQuery("#pl_label_icon")[0].files[0];
    var path = url_admin+'/wp-content/plugins/product-label-by-aheadworks/admin/language/'+file.name;
    var name = file.name;
    var filext = name.substring(name.lastIndexOf(".")+1);
    jQuery('#pl_label_image').val('');
    if(file)
    {
      if(filext == "jpeg" || filext == "jpg" || filext == "png" || filext == "bmp" || filext == "gif" || filext == "JPEG" || filext == "JPG" || filext == "PNG" || filext == "BMP" || filext == "GIF")
      {

        var reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = function(e) {
          jQuery("#uploadedimage").text("");
          jQuery('#pl_label_image').attr('data-value',e.target.result);
          jQuery('#pl_label_image').val(path);
          jQuery('#pl_label_display-image').attr('src',e.target.result);
          jQuery("#pl_label_display-image").attr('image-name',name);
          jQuery("#closebackimage").show();
          jQuery("#pl_label_display-image").show();

          if('' != e.target.result) {
            jQuery(".img-label-wrap").css({'height' : '','width' : ''});
            var div = document.getElementsByClassName('img-label-wrap');
            div[0].classList.remove("hw-auto-tleft");
            div[0].classList.remove("hw-auto-tright");
            div[0].classList.remove("hw-auto-bleft");
            div[0].classList.remove("hw-auto-bright");

            div[1].classList.remove("hw-auto-tleft");
            div[1].classList.remove("hw-auto-tright");
            div[1].classList.remove("hw-auto-bleft");
            div[1].classList.remove("hw-auto-bright");

            div[2].classList.remove("hw-auto-tleft");
            div[2].classList.remove("hw-auto-tright");
            div[2].classList.remove("hw-auto-bleft");
            div[2].classList.remove("hw-auto-bright");

            div[3].classList.remove("hw-auto-nleft");
            div[4].classList.remove("hw-auto-nleft");
            div[5].classList.remove("hw-auto-nleft");
          }
          jQuery('.display_images').attr('src',e.target.result);
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
    request_Url = url+'/wp-admin/admin-ajax.php?action=aw_label_image_delete&postid='+postid;
    jQuery.ajax({
      url: request_Url,
      type:'POST',
      success:function(data){
        if(data!=0)
        alert(data);
        jQuery("#pl_label_icon").css("display","block");
        jQuery("#pl_label_icon").val("");
        jQuery("#uploadedimage").text("");
        jQuery("#pl_label_image").val('');
        jQuery("#pl_label_image").attr('data-value','');
        jQuery("#pl_label_display-image").attr('src','');
        jQuery("#pl_label_display-image").hide();
        jQuery("#closebackimage").hide();

        jQuery('.display_images').attr('src','');
        jQuery('.show_image').attr('src','');
        jQuery(".show_image").hide();
        jQuery(".img-label-wrap").css({'height' : 'auto','width' : 'auto'});

        var div = document.getElementsByClassName('img-label-wrap');

        if(document.getElementById('upper_right').checked == true){
          div[0].classList.add("hw-auto-tright");
          div[1].classList.add("hw-auto-tright");
          div[2].classList.add("hw-auto-tright");
        }
        if(document.getElementById('upper_left').checked == true){
          div[0].classList.add("hw-auto-tleft");
          div[1].classList.add("hw-auto-tleft");
          div[2].classList.add("hw-auto-tleft");
        }
        if(document.getElementById('lower_left').checked == true){
          div[0].classList.add("hw-auto-bleft");
          div[1].classList.add("hw-auto-bleft");
          div[2].classList.add("hw-auto-bleft");
        }
        if(document.getElementById('lower_right').checked == true){
          div[0].classList.add("hw-auto-bright");
          div[1].classList.add("hw-auto-bright");
          div[2].classList.add("hw-auto-bright");
        }
        if(document.getElementById('next_price').checked == true){
          div[3].classList.add("hw-auto-nleft");
          div[4].classList.add("hw-auto-nleft");
          div[5].classList.add("hw-auto-nleft");
        }

      },
      error: function(errorThrown){
        console.log(errorThrown);
      }
    });

    jQuery("#pl_label_display-image").attr('image-name','');
  });
});

function positionClick(position) 
{
  var textstyle_l = '';
  var textstyle_m = '';
  var textstyle_s = '';

  var final_textstr = '';
  var sec_textstr = '';
  var text_col = '';
  var final_textstr_m = '';
  var sec_textstr_m = '';
  var text_col_m = '';
  var final_textstr_s = '';
  var sec_textstr_s = '';
  var text_col_s = '';


  var final_str_label = '';
  var sec_str_label = '';
  var col = '';
  var final_str_label_m = '';
  var sec_str_label_m = '';
  var col_m = '';
  var final_str_label_s = '';
  var sec_str_label_s = '';
  var col_s = '';

  textdesign1 = jQuery('#label_text_css').text();
  textdesign2 = jQuery('#medium_label_text_css').text();
  textdesign3 = jQuery('#small_label_text_css').text();

  design1 = jQuery('#label_container_css').text();
  design2 = jQuery('#medium_label_container_css').text();
  design3 = jQuery('#small_label_container_css').text();

  if(''!= design1){
    var arr_label =  design1.split(" ");

    for (i = 0; i < arr_label.length; i++) {
      var subrr_label =  arr_label[i].split(":");
      first_str_label = '"'+subrr_label[0]+'":';
      sec_str_label  = '"'+subrr_label[1].substr(0, subrr_label[1].length - 1)+'" ';
      final_str_label = final_str_label+first_str_label +sec_str_label;
    }

    if(''!= final_str_label) {
      for(i=0; i < final_str_label.length; i++) {
       final_str_label = final_str_label.replace(" ", ","); 
      }
      final_str_label  = final_str_label.substr(0, final_str_label.length - 1);
    }
    textstyle_l = final_str_label;
    var data = '{'+final_str_label+'}';
    col  = JSON.parse(data);
  }
  

  if(''!= textdesign1){
    var arr_textstr =  textdesign1.split(" ");

    for (i = 0; i < arr_textstr.length; i++) {
      var subrr_textstr =  arr_textstr[i].split(":");
      first_textstr = '"'+subrr_textstr[0]+'":';
      sec_textstr  = '"'+subrr_textstr[1].substr(0, subrr_textstr[1].length - 1)+'" ';
      final_textstr = final_textstr+first_textstr +sec_textstr;
    }

    if(''!= final_textstr) {
      for(i=0; i < final_textstr.length; i++) {
       final_textstr = final_textstr.replace(" ", ","); 
      }
      final_textstr  = final_textstr.substr(0, final_textstr.length - 1);
    }
    var textdata = '{'+final_textstr+'}';
    text_col  = JSON.parse(textdata);
  }

  if('' != textstyle_l && '' != final_textstr){
    textstyle_l = textstyle_l+','+final_textstr;
    textstyle_l = '{'+textstyle_l+'}';
    textstyle_l  = JSON.parse(textstyle_l);
  } else if('' != final_textstr){
    textstyle_l = final_textstr;
    textstyle_l = '{'+textstyle_l+'}';
    textstyle_l  = JSON.parse(textstyle_l);
  } else if('' != textstyle_l){
    textstyle_l = '{'+textstyle_l+'}';
    textstyle_l  = JSON.parse(textstyle_l);
  }

  if(''!= design2){
    var arr_label_m =  design2.split(" ");

    for (i = 0; i < arr_label_m.length; i++) {
      var subrr_label_m =  arr_label_m[i].split(":");
      first_str_label_m = '"'+subrr_label_m[0]+'":';
      sec_str_label_m  = '"'+subrr_label_m[1].substr(0, subrr_label_m[1].length - 1)+'" ';
      final_str_label_m = final_str_label_m+first_str_label_m +sec_str_label_m;
    }

    if(''!= final_str_label_m) {
      for(i=0; i < final_str_label_m.length; i++) {
       final_str_label_m = final_str_label_m.replace(" ", ","); 
      }
      final_str_label_m  = final_str_label_m.substr(0, final_str_label_m.length - 1);
    }
    textstyle_m = final_str_label_m;
    var data_m = '{'+final_str_label_m+'}';
    col_m = JSON.parse(data_m);
  }

  if(''!= textdesign2){
    var arr_textstr_m =  textdesign2.split(" ");

    for (i = 0; i < arr_textstr_m.length; i++) {
      var subrr_textstr_m =  arr_textstr_m[i].split(":");
      first_textstr_m = '"'+subrr_textstr_m[0]+'":';
      sec_textstr_m  = '"'+subrr_textstr_m[1].substr(0, subrr_textstr_m[1].length - 1)+'" ';
      final_textstr_m = final_textstr_m+first_textstr_m +sec_textstr_m;
    }

    if(''!= final_textstr_m) {
      for(i=0; i < final_textstr_m.length; i++) {
       final_textstr_m = final_textstr_m.replace(" ", ","); 
      }
      final_textstr_m  = final_textstr_m.substr(0, final_textstr_m.length - 1);
    }
    var textdata_m = '{'+final_textstr_m+'}';
    text_col_m = JSON.parse(textdata_m);
  }

  if('' != textstyle_m && '' != final_textstr_m){
    textstyle_m = textstyle_m+','+final_textstr_m;
    textstyle_m = '{'+textstyle_m+'}';
    textstyle_m  = JSON.parse(textstyle_m);
  } else if('' != final_textstr_m){
    textstyle_m = final_textstr_m;
    textstyle_m = '{'+textstyle_m+'}';
    textstyle_m  = JSON.parse(textstyle_m);
  } else if('' != textstyle_m){
    textstyle_m = '{'+textstyle_m+'}';
    textstyle_m  = JSON.parse(textstyle_m);
  }
  
  if(''!= design3){
    var arr_label_s =  design3.split(" ");

    for (i = 0; i < arr_label_s.length; i++) {
      var subrr_label_s =  arr_label_s[i].split(":");
      first_str_label_s = '"'+subrr_label_s[0]+'":';
      sec_str_label_s  = '"'+subrr_label_s[1].substr(0, subrr_label_s[1].length - 1)+'" ';
      final_str_label_s = final_str_label_s+first_str_label_s +sec_str_label_s;
    }

    if(''!= final_str_label_s) {
      for(i=0; i < final_str_label_s.length; i++) {
       final_str_label_s = final_str_label_s.replace(" ", ","); 
      }
      final_str_label_s  = final_str_label_s.substr(0, final_str_label_s.length - 1);
    }
    textstyle_s = final_str_label_s;
    var data_s = '{'+final_str_label_s+'}';
    col_s = JSON.parse(data_s);
  }

  if(''!= textdesign3){
    var arr_textstr_s =  textdesign3.split(" ");

    for (i = 0; i < arr_textstr_s.length; i++) {
      var subrr_textstr_s =  arr_textstr_s[i].split(":");
      first_textstr_s = '"'+subrr_textstr_s[0]+'":';
      sec_textstr_s  = '"'+subrr_textstr_s[1].substr(0, subrr_textstr_s[1].length - 1)+'" ';
      final_textstr_s = final_textstr_s+first_textstr_s +sec_textstr_s;
    }

    if(''!= final_textstr_s) {
      for(i=0; i < final_textstr_s.length; i++) {
       final_textstr_s = final_textstr_s.replace(" ", ","); 
      }
      final_textstr_s  = final_textstr_s.substr(0, final_textstr_s.length - 1);
    }
    var textdata_s = '{'+final_textstr_s+'}';
    text_col_s = JSON.parse(textdata_s);
  }

  if('' != textstyle_s && '' != final_textstr_s){
    textstyle_s = textstyle_s+','+final_textstr_s;
    textstyle_s = '{'+textstyle_s+'}';
    textstyle_s  = JSON.parse(textstyle_s);
  } else if('' != final_textstr_s){
    textstyle_s = final_textstr_s;
    textstyle_s = '{'+textstyle_s+'}';
    textstyle_s  = JSON.parse(textstyle_s);
  } else if('' != textstyle_s){
    textstyle_s = '{'+textstyle_s+'}';
    textstyle_s  = JSON.parse(textstyle_s);
  }


  var div = document.getElementsByClassName('img-label-wrap');
  var elem = document.getElementById('display-images');

  if ('next_price' == position.value)   {
    jQuery("#shapeplaceholder1").attr('class','');
    jQuery("#shapeplaceholder2").attr('class','');
    jQuery("#shapeplaceholder3").attr('class','');

    jQuery("#shapeposition1").css("display","none");
    jQuery("#shapeposition2").css("display","none");
    jQuery("#shapeposition3").css("display","none");

    jQuery("#display_picture1").css("display","none"); 
    jQuery("#display_picture2").css("display","none"); 
    jQuery("#display_picture3").css("display","none");

    jQuery("#labeltext1").css({'display':'none'});
    jQuery("#labeltext2").css({'display':'none'}); 
    jQuery("#labeltext3").css({'display':'none'});

    jQuery("#shapelabeltext1").css({'display':'none'});
    jQuery("#shapelabeltext2").css({'display':'none'}); 
    jQuery("#shapelabeltext3").css({'display':'none'});

    jQuery("#nextlabeltext1").css({'display':'none'});
    jQuery("#nextlabeltext2").css({'display':'none'}); 
    jQuery("#nextlabeltext3").css({'display':'none'});

    if(document.getElementById('label_shape').checked == true)  {
      var shapename = jQuery("#label_shape_type").find(':selected').attr('data-value'); 
      jQuery("#image_tab").css("display","none");
      jQuery("#shape_tab").css("display","block");

      jQuery("#next_display_picture1").css("display","none"); 
      jQuery("#next_display_picture2").css("display","none"); 
      jQuery("#next_display_picture3").css("display","none"); 

      jQuery("#shapenextlabeltext1").css("display","block");
      jQuery("#shapenextlabeltext2").css("display","block"); 
      jQuery("#shapenextlabeltext3").css("display","block");

      jQuery("#shapenextposition1").css({'right' : '', 'bottom' : '' ,'top':''});
      jQuery("#shapenextposition1").css({'display':'block','position':'absolute','left':'0px','top':'0px'});

      jQuery("#shapenextposition2").css({'right' : '', 'bottom' : '' ,'top':''});
      jQuery("#shapenextposition2").css({'display':'block','position':'absolute','left':'0px','top':'0px'});

      jQuery("#shapenextposition3").css({'right' : '', 'bottom' : '' ,'top':''});
      jQuery("#shapenextposition3").css({'display':'block','position':'absolute','left':'0px','top':'0px'});

      jQuery("#shapenextprice1").attr('class','');
      jQuery("#shapenextprice1").attr('class',shapename);

      jQuery("#shapenextprice2").attr('class','');
      jQuery("#shapenextprice2").attr('class',shapename);

      jQuery("#shapenextprice3").attr('class','');
      jQuery("#shapenextprice3").attr('class',shapename);

      if('' != textstyle_l) {
        jQuery("#shapenextprice1").css(textstyle_l);
      }
      if('' != textstyle_m) {
        jQuery("#shapenextprice2").css(textstyle_m);
      }
      if('' != textstyle_s) {
        jQuery("#shapenextprice3").css(textstyle_s);
      }
    }
    else if(document.getElementById('label_picture').checked == true)  {
      if(elem !== null && elem !== '') {
        if(elem.getAttribute('src') == "") {
          div[0].classList.remove("hw-auto-tleft");
          div[0].classList.remove("hw-auto-tright");
          div[0].classList.remove("hw-auto-bleft");
          div[0].classList.remove("hw-auto-bright");

          div[1].classList.remove("hw-auto-tleft");
          div[1].classList.remove("hw-auto-tright");
          div[1].classList.remove("hw-auto-bleft");
          div[1].classList.remove("hw-auto-bright");

          div[2].classList.remove("hw-auto-tleft");
          div[2].classList.remove("hw-auto-tright");
          div[2].classList.remove("hw-auto-bleft");
          div[2].classList.remove("hw-auto-bright");

          div[3].classList.add("hw-auto-nleft");
          div[4].classList.add("hw-auto-nleft");
          div[5].classList.add("hw-auto-nleft");
        }
      }
      jQuery("#shapenextprice1").attr('class','');
      jQuery("#shapenextprice2").attr('class','');
      jQuery("#shapenextprice3").attr('class','');

      jQuery("#shapenextposition1").css("display","none"); 
      jQuery("#shapenextposition2").css("display","none"); 
      jQuery("#shapenextposition3").css("display","none"); 

      jQuery("#next_display_picture1").css("display","inline-block"); 
      jQuery("#next_display_picture2").css("display","inline-block"); 
      jQuery("#next_display_picture3").css("display","inline-block"); 

      if('' != col){
        jQuery('.next_display_picture1 ').css(col);
      }
      if('' != col_m){
        jQuery('.next_display_picture2 ').css(col_m);
      }
      if('' != col_s){
         jQuery('.next_display_picture2 ').css(col_s);
      }

      jQuery("#nextpictext1").css({'display':'block'});
      jQuery("#nextpictext2").css({'display':'block'}); 
      jQuery("#nextpictext3").css({'display':'block'});

      if('' != text_col){
        jQuery("#nextpictext1").css(text_col);
      }
      if('' != text_col_m){
        jQuery("#nextpictext2").css(text_col_m);
      }
      if('' != text_col_s){
        jQuery("#nextpictext3").css(text_col_s);
      }                
    }
    else if(document.getElementById('label_text').checked == true) {
      jQuery("#shapenextprice1").attr('class','');
      jQuery("#shapenextprice2").attr('class','');
      jQuery("#shapenextprice3").attr('class','');

      jQuery("#shapenextposition1").css("display","none"); 
      jQuery("#shapenextposition2").css("display","none"); 
      jQuery("#shapenextposition3").css("display","none"); 

      jQuery("#next_display_picture1").css("display","none"); 
      jQuery("#next_display_picture2").css("display","none"); 
      jQuery("#next_display_picture3").css("display","none"); 

      jQuery("#nextlabeltext1").css("display","block");
      jQuery("#nextlabeltext2").css("display","block"); 
      jQuery("#nextlabeltext3").css("display","block");

      if('' != textstyle_l){
        jQuery("#nextlabeltext1").css(textstyle_l);
      }
      if('' != textstyle_m){
        jQuery("#nextlabeltext2").css(textstyle_m);
      } 
      if('' != textstyle_s){
        jQuery("#nextlabeltext3").css(textstyle_s);
      }
    }
  }
  else 
  {
    var shapename = jQuery("#label_shape_type").find(':selected').attr('data-value');

    jQuery("#shapenextposition1").css("display","none");
    jQuery("#shapenextposition2").css("display","none");
    jQuery("#shapenextposition3").css("display","none");

    jQuery("#next_display_picture1").css("display","none"); 
    jQuery("#next_display_picture2").css("display","none"); 
    jQuery("#next_display_picture3").css("display","none"); 

    jQuery("#nextlabeltext1").css({'display':'none'});
    jQuery("#nextlabeltext2").css({'display':'none'}); 
    jQuery("#nextlabeltext3").css({'display':'none'});

    jQuery("#shapenextlabeltext1").css("display","none"); 
    jQuery("#shapenextlabeltext2").css("display","none"); 
    jQuery("#shapenextlabeltext3").css("display","none");


    if ('upper_right' == position.value) {     
      if(document.getElementById('label_shape').checked == true) {      
        jQuery("#image_tab").css("display","none");
        jQuery("#shape_tab").css("display","block");

        jQuery("#display_picture1").css("display","none"); 
        jQuery("#display_picture2").css("display","none"); 
        jQuery("#display_picture3").css("display","none");

        jQuery("#labeltext1").css("display","none"); 
        jQuery("#labeltext2").css("display","none"); 
        jQuery("#labeltext3").css("display","none");

        jQuery("#shapeposition1").css({'left' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition1").css({'display':'block','position':'absolute','right':'0px','top':'0px'});

        jQuery("#shapeposition2").css({'left' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition2").css({'display':'block','position':'absolute','right':'0px','top':'0px'});

        jQuery("#shapeposition3").css({'left' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition3").css({'display':'block','position':'absolute','right':'0px','top':'0px'});
        

        jQuery("#shapeplaceholder1").attr('class','');
        jQuery("#shapeplaceholder1").attr('class',shapename);

        jQuery("#shapeplaceholder2").attr('class','');
        jQuery("#shapeplaceholder2").attr('class',shapename);

        jQuery("#shapeplaceholder3").attr('class','');
        jQuery("#shapeplaceholder3").attr('class',shapename);

        if('' != textstyle_l) {
         jQuery("#shapeplaceholder1").css(textstyle_l); 
        }
        if('' != textstyle_m) {
         jQuery("#shapeplaceholder2").css(textstyle_m); 
        }
        if('' != textstyle_s) {
         jQuery("#shapeplaceholder3").css(textstyle_s); 
        }

        if('rectangle_belevel_down' == shapename)
        {
          jQuery('.rectangle_belevel_down').addClass('down_right');
        }
        if('rectangle_belevel_up' == shapename)
        {
          jQuery('.rectangle_belevel_up').addClass('up_right');
        }     
      }
      else  if(document.getElementById('label_picture').checked == true) 
      {
        if(elem !== null && elem !== '') {
          if(elem.getAttribute('src') == "") {
            div[0].classList.add("hw-auto-tright");
            div[1].classList.add("hw-auto-tright");
            div[2].classList.add("hw-auto-tright");

            div[0].classList.remove("hw-auto-tleft");
            div[0].classList.remove("hw-auto-bleft");
            div[0].classList.remove("hw-auto-bright");

            div[1].classList.remove("hw-auto-tleft");
            div[1].classList.remove("hw-auto-bleft");
            div[1].classList.remove("hw-auto-bright");

            div[2].classList.remove("hw-auto-tleft");
            div[2].classList.remove("hw-auto-bleft");
            div[2].classList.remove("hw-auto-bright");

            div[3].classList.remove("hw-auto-nleft");
            div[4].classList.remove("hw-auto-nleft");
            div[5].classList.remove("hw-auto-nleft");
          }
        }
        
        jQuery("#shapeplaceholder1").attr('class','');
        jQuery("#shapeplaceholder2").attr('class','');
        jQuery("#shapeplaceholder3").attr('class',''); 

        jQuery("#labeltext1").css("display","none"); 
        jQuery("#labeltext2").css("display","none"); 
        jQuery("#labeltext3").css("display","none");

        jQuery('#display_picture1 ').css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture1 ').css({'display':'block','position': 'absolute','right':'0px','top':'0px'});

        jQuery('#display_picture2 ').css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture2 ').css({'display':'block','position': 'absolute','right':'0px','top':'0px'});

        jQuery('#display_picture3 ').css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture3 ').css({'display':'block','position': 'absolute','right':'0px','top':'0px'});

        if('' != col){
          jQuery('.display_picture1 ').css(col);
        }
        if('' != col_m){
          jQuery('.display_picture2 ').css(col_m);
        }
        if('' != col_s){
          jQuery('.display_picture3 ').css(col_s);
        } 

        jQuery("#pictext1").css({ 'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#pictext1").css({'display':'block'});

        jQuery("#pictext2").css({'right' : '', 'left' : '', 'bottom' : '' ,'top':''});
        jQuery("#pictext2").css({'display':'block'});

        jQuery("#pictext3").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#pictext3").css({'display':'block'});

        if('' != text_col){
          jQuery("#pictext1").css(text_col);
        }
        if('' != text_col_m){
          jQuery("#pictext2").css(text_col_m);
        }
        if('' != text_col_s){
          jQuery("#pictext3").css(text_col_s);
        }

      }
      else  if(document.getElementById('label_text').checked == true) 
      {
        jQuery("#shapeplaceholder1").attr('class','');
        jQuery("#shapeplaceholder2").attr('class','');
        jQuery("#shapeplaceholder3").attr('class','');

        jQuery("#display_picture1").css("display","none"); 
        jQuery("#display_picture2").css("display","none"); 
        jQuery("#display_picture3").css("display","none");

        jQuery("#labeltext1").css({ 'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext1").css({'display':'block','position': 'absolute','right':'0px', 'top':'0px'});

        jQuery("#labeltext2").css({'right' : '', 'left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext2").css({'display':'block','position': 'absolute','right':'0px', 'top':'0px'});

        jQuery("#labeltext3").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext3").css({'display':'block','position': 'absolute','right':'0px', 'top':'0px'});

        if('' != textstyle_l){
          jQuery("#labeltext1").css(textstyle_l);
        }
        if('' != textstyle_m){
          jQuery("#labeltext2").css(textstyle_m);
        } 
        if('' != textstyle_s){
          jQuery("#labeltext3").css(textstyle_s);
        }
      }
    }
    else if ('upper_left' == position.value)
    {
      if(document.getElementById('label_shape').checked == true) 
      {
        jQuery("#image_tab").css("display","none");
        jQuery("#shape_tab").css("display","block");

        jQuery("#display_picture1").css("display","none"); 
        jQuery("#display_picture2").css("display","none"); 
        jQuery("#display_picture3").css("display","none");

        jQuery("#labeltext1").css("display","none"); 
        jQuery("#labeltext2").css("display","none"); 
        jQuery("#labeltext3").css("display","none");

        jQuery("#shapeposition1").css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition1").css({'display':'block','position':'absolute','left':'0px','top':'0px'});

        jQuery("#shapeposition2").css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition2").css({'display':'block','position':'absolute','left':'0px','top':'0px'});

        jQuery("#shapeposition3").css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition3").css({'display':'block','position':'absolute','left':'0px','top':'0px'});
        

        jQuery("#shapelabeltext1").css({'display':'block'});
        jQuery("#shapelabeltext2").css({'display':'block'});
        jQuery("#shapelabeltext3").css({'display':'block'});

        jQuery("#shapeplaceholder1").attr('class','');
        jQuery("#shapeplaceholder1").attr('class',shapename);

        jQuery("#shapeplaceholder2").attr('class','');
        jQuery("#shapeplaceholder2").attr('class',shapename);

        jQuery("#shapeplaceholder3").attr('class','');
        jQuery("#shapeplaceholder3").attr('class',shapename);

        if('' != textstyle_l){
          jQuery("#shapeplaceholder1").css(textstyle_l);
        }
        if('' != textstyle_m){
          jQuery("#shapeplaceholder2").css(textstyle_m);
        }
        if('' != textstyle_s){
          jQuery("#shapeplaceholder3").css(textstyle_s);
        }

      }
      else  if(document.getElementById('label_picture').checked == true) 
      {
        if(elem !== null && elem !== '') {
          if(elem.getAttribute('src') == "") {
            div[0].classList.add("hw-auto-tleft");
            div[1].classList.add("hw-auto-tleft");
            div[2].classList.add("hw-auto-tleft");

            div[0].classList.remove("hw-auto-tright");
            div[0].classList.remove("hw-auto-bleft");
            div[0].classList.remove("hw-auto-bright");

            div[1].classList.remove("hw-auto-tright");
            div[1].classList.remove("hw-auto-bleft");
            div[1].classList.remove("hw-auto-bright");

            div[2].classList.remove("hw-auto-tright");
            div[2].classList.remove("hw-auto-bleft");
            div[2].classList.remove("hw-auto-bright");

            div[3].classList.remove("hw-auto-nleft");
            div[4].classList.remove("hw-auto-nleft");
            div[5].classList.remove("hw-auto-nleft");
          }
        }

        jQuery("#shapeplaceholder1").attr('class','');
        jQuery("#shapeposition1").css("display","none");
        
        jQuery("#shapeplaceholder2").attr('class','');
        jQuery("#shapeposition2").css("display","none");

        jQuery("#shapeplaceholder3").attr('class',''); 
        jQuery("#shapeposition3").css("display","none");

        jQuery("#labeltext1").css("display","none"); 
        jQuery("#labeltext2").css("display","none"); 
        jQuery("#labeltext3").css("display","none");

        jQuery('#display_picture1 ').css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture1 ').css({'display':'block','position': 'absolute','left':'0px','top':'0px'});

        jQuery('#display_picture2 ').css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture2 ').css({'display':'block','position': 'absolute','left':'0px','top':'0px'});

        jQuery('#display_picture3 ').css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture3 ').css({'display':'block','position': 'absolute','left':'0px','top':'0px'});

        if('' != col){
          jQuery('.display_picture1 ').css(col);
        }
        if('' != col_m){
          jQuery('.display_picture2 ').css(col_m);
        }
        if('' != col_s){
           jQuery('.display_picture3 ').css(col_s);
        }

        jQuery("#pictext1").css({ 'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#pictext1").css({'display':'block'});

        jQuery("#pictext2").css({'right' : '', 'left' : '', 'bottom' : '' ,'top':''});
        jQuery("#pictext2").css({'display':'block'});

        jQuery("#pictext3").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#pictext3").css({'display':'block'});

        if('' != text_col){
          jQuery("#pictext1").css(text_col);
        }
        if('' != text_col_m){
          jQuery("#pictext2").css(text_col_m);
        }
        if('' != text_col_s){
          jQuery("#pictext3").css(text_col_s);
        }

      }
      else if(document.getElementById('label_text').checked == true) 
      {

        jQuery("#shapeplaceholder1").attr('class','');
        jQuery("#shapeplaceholder2").attr('class','');
        jQuery("#shapeplaceholder3").attr('class',''); 

        jQuery("#display_picture1").css("display","none"); 
        jQuery("#display_picture2").css("display","none"); 
        jQuery("#display_picture3").css("display","none");

        jQuery("#labeltext1").css({ 'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext1").css({'display':'block','position': 'absolute','left':'0px', 'top':'0px'});

        jQuery("#labeltext2").css({'right' : '', 'left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext2").css({'display':'block','position': 'absolute','left':'0px', 'top':'0px'});

        jQuery("#labeltext3").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext3").css({'display':'block','position': 'absolute','left':'0px', 'top':'0px'});
      
        if('' != textstyle_l){
          jQuery("#labeltext1").css(textstyle_l);
        }
        if('' != textstyle_m){
          jQuery("#labeltext2").css(textstyle_m);
        } 
        if('' != textstyle_s){
          jQuery("#labeltext3").css(textstyle_s);
        }
      }
    }
    else if ('lower_left' == position.value) 
    {      
      if(document.getElementById('label_shape').checked == true) 
      {
        jQuery("#image_tab").css("display","none");
        jQuery("#shape_tab").css("display","block");

        jQuery("#display_picture1").css("display","none"); 
        jQuery("#display_picture2").css("display","none"); 
        jQuery("#display_picture3").css("display","none");

        jQuery("#labeltext1").css("display","none"); 
        jQuery("#labeltext2").css("display","none"); 
        jQuery("#labeltext3").css("display","none");

        jQuery("#shapeposition1").css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition1").css({'display':'block','position':'absolute','left':'0px','bottom':'0px'});

        jQuery("#shapeposition2").css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition2").css({'display':'block','position':'absolute','left':'0px','bottom':'0px'});

        jQuery("#shapeposition3").css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition3").css({'display':'block','position':'absolute','left':'0px','bottom':'0px'});
        

        jQuery("#shapelabeltext1").css({'display':'block'});
        jQuery("#shapelabeltext2").css({'display':'block'});
        jQuery("#shapelabeltext3").css({'display':'block'});

        jQuery("#shapeplaceholder1").attr('class','');
        jQuery("#shapeplaceholder1").attr('class',shapename);

        jQuery("#shapeplaceholder2").attr('class','');
        jQuery("#shapeplaceholder2").attr('class',shapename);

        jQuery("#shapeplaceholder3").attr('class','');
        jQuery("#shapeplaceholder3").attr('class',shapename);

        if('' != textstyle_l){
          jQuery("#shapeplaceholder1").css(textstyle_l);
        }
        if('' != textstyle_m){
          jQuery("#shapeplaceholder2").css(textstyle_m);
        }

        if('' != textstyle_s){
          jQuery("#shapeplaceholder3").css(textstyle_s);
        }
      } 
      else if(document.getElementById('label_picture').checked == true) 
      {
        if(elem !== null && elem !== '') {
          if(elem.getAttribute('src') == "") {
            div[0].classList.add("hw-auto-bleft");
            div[1].classList.add("hw-auto-bleft");
            div[2].classList.add("hw-auto-bleft");

            div[0].classList.remove("hw-auto-tleft");
            div[0].classList.remove("hw-auto-tright");
            div[0].classList.remove("hw-auto-bright");

            div[1].classList.remove("hw-auto-tleft");
            div[1].classList.remove("hw-auto-tright");
            div[1].classList.remove("hw-auto-bright");

            div[2].classList.remove("hw-auto-tleft");
            div[2].classList.remove("hw-auto-tright");
            div[2].classList.remove("hw-auto-bright");

            div[3].classList.remove("hw-auto-nleft");
            div[4].classList.remove("hw-auto-nleft");
            div[5].classList.remove("hw-auto-nleft");
          }
        }

        jQuery("#shapeplaceholder1").attr('class','');
        jQuery("#shapeplaceholder2").attr('class','');
        jQuery("#shapeplaceholder3").attr('class','');

        jQuery("#labeltext1").css("display","none"); 
        jQuery("#labeltext2").css("display","none"); 
        jQuery("#labeltext3").css("display","none");

        jQuery('#display_picture1 ').css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture1 ').css({'display':'block','position': 'absolute','left':'0px', 'bottom':'0px'});

        jQuery('#display_picture2 ').css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture2 ').css({'display':'block','position': 'absolute','left':'0px', 'bottom':'0px'});

        jQuery('#display_picture3 ').css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture3 ').css({'display':'block','position': 'absolute','left':'0px', 'bottom':'0px'});

        if('' != col){
          jQuery('.display_picture1 ').css(col);
        }
        if('' != col_m){
          jQuery('.display_picture2 ').css(col_m);
        }
        if('' != col_s){
           jQuery('.display_picture3 ').css(col_s);
        }

        jQuery("#pictext1").css({ 'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#pictext1").css({'display':'block'});

        jQuery("#pictext2").css({'right' : '', 'left' : '', 'bottom' : '' ,'top':''});
        jQuery("#pictext2").css({'display':'block'});

        jQuery("#pictext3").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#pictext3").css({'display':'block'});

        if('' != text_col){
          jQuery("#pictext1").css(text_col);
        }
        if('' != text_col_m){
          jQuery("#pictext2").css(text_col_m);
        }
        if('' != text_col_s){
          jQuery("#pictext3").css(text_col_s);
        }
      } 
      else if(document.getElementById('label_text').checked == true)
      {

        jQuery("#shapeplaceholder1").attr('class','');
        jQuery("#shapeplaceholder2").attr('class','');
        jQuery("#shapeplaceholder3").attr('class','');

        jQuery("#display_picture1").css("display","none"); 
        jQuery("#display_picture2").css("display","none"); 
        jQuery("#display_picture3").css("display","none");

        jQuery("#labeltext1").css({ 'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext1").css({'display':'block','position': 'absolute','left':'0px', 'bottom':'0px'});

        jQuery("#labeltext2").css({'right' : '', 'left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext2").css({'display':'block','position': 'absolute','left':'0px', 'bottom':'0px'});

        jQuery("#labeltext3").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext3").css({'display':'block','position': 'absolute','left':'0px', 'bottom':'0px'});
      
        if('' != textstyle_l){
          jQuery("#labeltext1").css(textstyle_l);
        }
        if('' != textstyle_m){
          jQuery("#labeltext2").css(textstyle_m);
        }
        if('' != textstyle_s){ 
          jQuery("#labeltext3").css(textstyle_s);
        }
      }
    }
    else if('lower_right' == position.value) 
    {
      
      if(document.getElementById('label_shape').checked == true) 
      {
        jQuery("#image_tab").css("display","none");
        jQuery("#shape_tab").css("display","block");

        jQuery("#display_picture1").css("display","none"); 
        jQuery("#display_picture2").css("display","none"); 
        jQuery("#display_picture3").css("display","none");

        jQuery("#labeltext1").css("display","none"); 
        jQuery("#labeltext2").css("display","none"); 
        jQuery("#labeltext3").css("display","none");

        jQuery("#shapeposition1").css({'left' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition1").css({'display':'block','position':'absolute','right':'0px','bottom':'0px'});

        jQuery("#shapeposition2").css({'left' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition2").css({'display':'block','position':'absolute','right':'0px','bottom':'0px'});

        jQuery("#shapeposition3").css({'left' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition3").css({'display':'block','position':'absolute','right':'0px','bottom':'0px'});

        jQuery("#shapelabeltext1").css({'display':'block'});
        jQuery("#shapelabeltext2").css({'display':'block'});
        jQuery("#shapelabeltext3").css({'display':'block'});

        jQuery("#shapeplaceholder1").attr('class','');
        jQuery("#shapeplaceholder1").attr('class',shapename);

        jQuery("#shapeplaceholder2").attr('class','');
        jQuery("#shapeplaceholder2").attr('class',shapename);

        jQuery("#shapeplaceholder3").attr('class','');
        jQuery("#shapeplaceholder3").attr('class',shapename);

        if('' != textstyle_l){
          jQuery("#shapeplaceholder1").css(textstyle_l);
        }
        if('' != textstyle_m){
         jQuery("#shapeplaceholder2").css(textstyle_m); 
        }
        if('' != textstyle_s){
          jQuery("#shapeplaceholder3").css(textstyle_s);
        }

        if('rectangle_belevel_down' == shapename)
        {
          jQuery('.rectangle_belevel_down').addClass('down_right');
        }
        if('rectangle_belevel_up' == shapename)
        {
          jQuery('.rectangle_belevel_up').addClass('up_right');
        }
      }
      else if(document.getElementById('label_picture').checked == true) 
      {
        if(elem !== null && elem !== '') {
          if(elem.getAttribute('src') == "") {
            div[0].classList.add("hw-auto-bright");
            div[1].classList.add("hw-auto-bright");
            div[2].classList.add("hw-auto-bright");

            div[0].classList.remove("hw-auto-tleft");
            div[0].classList.remove("hw-auto-tright");
            div[0].classList.remove("hw-auto-bleft");

            div[1].classList.remove("hw-auto-tleft");
            div[1].classList.remove("hw-auto-tright");
            div[1].classList.remove("hw-auto-bleft");

            div[2].classList.remove("hw-auto-tleft");
            div[2].classList.remove("hw-auto-tright");
            div[2].classList.remove("hw-auto-bleft");

            div[3].classList.remove("hw-auto-nleft");
            div[4].classList.remove("hw-auto-nleft");
            div[5].classList.remove("hw-auto-nleft");
          }
        }
        jQuery("#shapeplaceholder1").attr('class','');
        jQuery("#shapeplaceholder2").attr('class','');
        jQuery("#shapeplaceholder3").attr('class','');

        jQuery("#labeltext1").css("display","none"); 
        jQuery("#labeltext2").css("display","none"); 
        jQuery("#labeltext3").css("display","none");

        jQuery('#display_picture1 ').css({'left' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture1').css({'display':'block','position': 'absolute','right':'0px', 'bottom':'0px'});

        jQuery('#display_picture2 ').css({'left' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture2 ').css({'display':'block','position': 'absolute','right':'0px', 'bottom':'0px'});

        jQuery('#display_picture3 ').css({'left' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture3 ').css({'display':'block','position': 'absolute','right':'0px', 'bottom':'0px'});

        jQuery("#pictext1").css({ 'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#pictext1").css({'display':'block'});

        jQuery("#pictext2").css({'right' : '', 'left' : '', 'bottom' : '' ,'top':''});
        jQuery("#pictext2").css({'display':'block'});

        jQuery("#pictext3").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#pictext3").css({'display':'block'});

        if('' != text_col){
          jQuery("#pictext1").css(text_col);
        }
        if('' != text_col_m){
          jQuery("#pictext2").css(text_col_m);
        }
        if('' != text_col_s){
          jQuery("#pictext3").css(text_col_s);
        }

      }
      else if(document.getElementById('label_text').checked == true) 
      {

        jQuery("#shapeplaceholder1").attr('class','');
        jQuery("#shapeplaceholder2").attr('class','');
        jQuery("#shapeplaceholder3").attr('class','');

        jQuery("#display_picture1").css("display","none"); 
        jQuery("#display_picture2").css("display","none"); 
        jQuery("#display_picture3").css("display","none");

        jQuery("#labeltext1").css({ 'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext1").css({'display':'block','position': 'absolute','right':'0px', 'bottom':'0px'});

        jQuery("#labeltext2").css({'right' : '', 'left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext2").css({'display':'block','position': 'absolute','right':'0px', 'bottom':'0px'});

        jQuery("#labeltext3").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext3").css({'display':'block','position': 'absolute','right':'0px', 'bottom':'0px'});

        if('' != textstyle_l){
          jQuery("#labeltext1").css(textstyle_l);
        }
        if('' != textstyle_m){
          jQuery("#labeltext2").css(textstyle_m);
        } 
        if('' != textstyle_s){
          jQuery("#labeltext3").css(textstyle_s);
        }
      }
    }
  }
}

function shapechange()
{
  const cont_element = document.querySelector('#choose_container_color')
  const cont_style = getComputedStyle(cont_element)
  var col = cont_style.backgroundColor;

  if('rgba(255, 255, 255, 0.5)' == col)
  {
    col = 'rgb(191,27,26)';
  }

  const cont_element_m = document.querySelector('#choose_container_color_medium')
  const cont_style_m = getComputedStyle(cont_element_m)
  var col_m = cont_style_m.backgroundColor;

  if('rgba(255, 255, 255, 0.5)' == col_m)
  {
    col_m = 'rgb(191,27,26)';
  }

  const cont_element_s = document.querySelector('#choose_container_color_small')
  const cont_style_s = getComputedStyle(cont_element_s)
  var col_s = cont_style_s.backgroundColor;

  if('rgba(255, 255, 255, 0.5)' == col_s)
  {
    col_s = 'rgb(191,27,26)';
  }
  

  var url_main = js_var.site_url;
  var shapename= jQuery("#label_shape_type").find(':selected').attr('data-value');
  if(document.getElementById('next_price').checked == true) 
  {
    jQuery("#shapeplaceholder1").attr('class','');
    jQuery("#shapeplaceholder2").attr('class','');
    jQuery("#shapeplaceholder3").attr('class','');
  
    jQuery("#shapeposition1").css("display","none");
    jQuery("#shapeposition2").css("display","none");
    jQuery("#shapeposition3").css("display","none");   

    jQuery("#shapenextprice1").attr('class','');
    jQuery("#shapenextprice1").attr('class',shapename);

    jQuery("#shapenextprice2").attr('class','');
    jQuery("#shapenextprice2").attr('class',shapename);

    jQuery("#shapenextprice3").attr('class','');
    jQuery("#shapenextprice3").attr('class',shapename);

    jQuery("#shapenextposition1").css({'right' : '', 'bottom' : '' ,'top':''});
    jQuery("#shapenextposition1").css({'display':'block','position':'absolute','left':'0px','top':'0px'});

    jQuery("#shapenextposition2").css({'right' : '', 'bottom' : '' ,'top':''});
    jQuery("#shapenextposition2").css({'display':'block','position':'absolute','left':'0px','top':'0px'});

    jQuery("#shapenextposition3").css({'right' : '', 'bottom' : '' ,'top':''});
    jQuery("#shapenextposition3").css({'display':'block','position':'absolute','left':'0px','top':'0px'});

    jQuery("#shapenextprice1").css({'background-color':col});
    jQuery("#shapenextprice2").css({'background-color':col_m});
    jQuery("#shapenextprice3").css({'background-color':col_s});
  } 
  else 
  { 
    jQuery("#shapenextprice1").attr('class','');
    jQuery("#shapenextprice2").attr('class','');
    jQuery("#shapenextprice3").attr('class','');

    jQuery("#shapenextposition1").css("display","none"); 
    jQuery("#shapenextposition2").css("display","none"); 
    jQuery("#shapenextposition3").css("display","none");

    jQuery("#shapeplaceholder1").attr('class','');
    jQuery("#shapeplaceholder1").attr('class',shapename);

    jQuery("#shapeplaceholder2").attr('class','');
    jQuery("#shapeplaceholder2").attr('class',shapename);

    jQuery("#shapeplaceholder3").attr('class','');
    jQuery("#shapeplaceholder3").attr('class',shapename);

    jQuery("#shapeplaceholder1").css({'background-color':col});
    jQuery("#shapeplaceholder2").css({'background-color':col_m});
    jQuery("#shapeplaceholder3").css({'background-color':col_s});

    if('rectangle_belevel_down' == shapename){
      if(document.getElementById('upper_right').checked == true || document.getElementById('lower_right').checked == true )  {
        jQuery('.rectangle_belevel_down').addClass('down_right');
      }
    }
    if('rectangle_belevel_up' == shapename) {
      if(document.getElementById('upper_right').checked == true || document.getElementById('lower_right').checked == true )  {
        jQuery('.rectangle_belevel_up').addClass('up_right');
      }
    }
  }
}

function typeClick(type) 
{
  var textstyle_l = '';
  var textstyle_m = '';
  var textstyle_s = '';

  var final_textstr = '';
  var sec_textstr = '';
  var text_col = '';
  var final_textstr_m = '';
  var sec_textstr_m = '';
  var text_col_m = '';
  var final_textstr_s = '';
  var sec_textstr_s = '';
  var text_col_s = '';


  var final_str_label = '';
  var sec_str_label = '';
  var col = '';
  var final_str_label_m = '';
  var sec_str_label_m = '';
  var col_m = '';
  var final_str_label_s = '';
  var sec_str_label_s = '';
  var col_s = '';

  textdesign1 = jQuery('#label_text_css').text();
  textdesign2 = jQuery('#medium_label_text_css').text();
  textdesign3 = jQuery('#small_label_text_css').text();

  design1 = jQuery('#label_container_css').text();
  design2 = jQuery('#medium_label_container_css').text();
  design3 = jQuery('#small_label_container_css').text();

  if(''!= design1){
    var arr_label =  design1.split(" ");

    for (i = 0; i < arr_label.length; i++) {
      var subrr_label =  arr_label[i].split(":");
      first_str_label = '"'+subrr_label[0]+'":';
      sec_str_label  = '"'+subrr_label[1].substr(0, subrr_label[1].length - 1)+'" ';
      final_str_label = final_str_label+first_str_label +sec_str_label;
    }

    if(''!= final_str_label) {
      for(i=0; i < final_str_label.length; i++) {
       final_str_label = final_str_label.replace(" ", ","); 
      }
      final_str_label  = final_str_label.substr(0, final_str_label.length - 1);
    }
    textstyle_l = final_str_label;
    var data = '{'+final_str_label+'}';
    col  = JSON.parse(data);
  }
  

  if(''!= textdesign1){
    var arr_textstr =  textdesign1.split(" ");

    for (i = 0; i < arr_textstr.length; i++) {
      var subrr_textstr =  arr_textstr[i].split(":");
      first_textstr = '"'+subrr_textstr[0]+'":';
      sec_textstr  = '"'+subrr_textstr[1].substr(0, subrr_textstr[1].length - 1)+'" ';
      final_textstr = final_textstr+first_textstr +sec_textstr;
    }

    if(''!= final_textstr) {
      for(i=0; i < final_textstr.length; i++) {
       final_textstr = final_textstr.replace(" ", ","); 
      }
      final_textstr  = final_textstr.substr(0, final_textstr.length - 1);
    }
    var textdata = '{'+final_textstr+'}';
    text_col  = JSON.parse(textdata);
  }

  if('' != textstyle_l && '' != final_textstr){
    textstyle_l = textstyle_l+','+final_textstr;
    textstyle_l = '{'+textstyle_l+'}';
    textstyle_l  = JSON.parse(textstyle_l);
  } else if('' != final_textstr){
    textstyle_l = final_textstr;
    textstyle_l = '{'+textstyle_l+'}';
    textstyle_l  = JSON.parse(textstyle_l);
  } else if('' != textstyle_l){
    textstyle_l = '{'+textstyle_l+'}';
    textstyle_l  = JSON.parse(textstyle_l);
  }

  if(''!= design2){
    var arr_label_m =  design2.split(" ");

    for (i = 0; i < arr_label_m.length; i++) {
      var subrr_label_m =  arr_label_m[i].split(":");
      first_str_label_m = '"'+subrr_label_m[0]+'":';
      sec_str_label_m  = '"'+subrr_label_m[1].substr(0, subrr_label_m[1].length - 1)+'" ';
      final_str_label_m = final_str_label_m+first_str_label_m +sec_str_label_m;
    }

    if(''!= final_str_label_m) {
      for(i=0; i < final_str_label_m.length; i++) {
       final_str_label_m = final_str_label_m.replace(" ", ","); 
      }
      final_str_label_m  = final_str_label_m.substr(0, final_str_label_m.length - 1);
    }
    textstyle_m = final_str_label_m;
    var data_m = '{'+final_str_label_m+'}';
    col_m = JSON.parse(data_m);
  }

  if(''!= textdesign2){
    var arr_textstr_m =  textdesign2.split(" ");

    for (i = 0; i < arr_textstr_m.length; i++) {
      var subrr_textstr_m =  arr_textstr_m[i].split(":");
      first_textstr_m = '"'+subrr_textstr_m[0]+'":';
      sec_textstr_m  = '"'+subrr_textstr_m[1].substr(0, subrr_textstr_m[1].length - 1)+'" ';
      final_textstr_m = final_textstr_m+first_textstr_m +sec_textstr_m;
    }

    if(''!= final_textstr_m) {
      for(i=0; i < final_textstr_m.length; i++) {
       final_textstr_m = final_textstr_m.replace(" ", ","); 
      }
      final_textstr_m  = final_textstr_m.substr(0, final_textstr_m.length - 1);
    }
    var textdata_m = '{'+final_textstr_m+'}';
    text_col_m = JSON.parse(textdata_m);
  }

  if('' != textstyle_m && '' != final_textstr_m){
    textstyle_m = textstyle_m+','+final_textstr_m;
    textstyle_m = '{'+textstyle_m+'}';
    textstyle_m  = JSON.parse(textstyle_m);
  } else if('' != final_textstr_m){
    textstyle_m = final_textstr_m;
    textstyle_m = '{'+textstyle_m+'}';
    textstyle_m  = JSON.parse(textstyle_m);
  } else if('' != textstyle_m){
    textstyle_m = '{'+textstyle_m+'}';
    textstyle_m  = JSON.parse(textstyle_m);
  }
  
  if(''!= design3){
    var arr_label_s =  design3.split(" ");

    for (i = 0; i < arr_label_s.length; i++) {
      var subrr_label_s =  arr_label_s[i].split(":");
      first_str_label_s = '"'+subrr_label_s[0]+'":';
      sec_str_label_s  = '"'+subrr_label_s[1].substr(0, subrr_label_s[1].length - 1)+'" ';
      final_str_label_s = final_str_label_s+first_str_label_s +sec_str_label_s;
    }

    if(''!= final_str_label_s) {
      for(i=0; i < final_str_label_s.length; i++) {
       final_str_label_s = final_str_label_s.replace(" ", ","); 
      }
      final_str_label_s  = final_str_label_s.substr(0, final_str_label_s.length - 1);
    }
    textstyle_s = final_str_label_s;
    var data_s = '{'+final_str_label_s+'}';
    col_s = JSON.parse(data_s);
  }

  if(''!= textdesign3){
    var arr_textstr_s =  textdesign3.split(" ");

    for (i = 0; i < arr_textstr_s.length; i++) {
      var subrr_textstr_s =  arr_textstr_s[i].split(":");
      first_textstr_s = '"'+subrr_textstr_s[0]+'":';
      sec_textstr_s  = '"'+subrr_textstr_s[1].substr(0, subrr_textstr_s[1].length - 1)+'" ';
      final_textstr_s = final_textstr_s+first_textstr_s +sec_textstr_s;
    }

    if(''!= final_textstr_s) {
      for(i=0; i < final_textstr_s.length; i++) {
       final_textstr_s = final_textstr_s.replace(" ", ","); 
      }
      final_textstr_s  = final_textstr_s.substr(0, final_textstr_s.length - 1);
    }
    var textdata_s = '{'+final_textstr_s+'}';
    text_col_s = JSON.parse(textdata_s);
  }

  if('' != textstyle_s && '' != final_textstr_s){
    textstyle_s = textstyle_s+','+final_textstr_s;
    textstyle_s = '{'+textstyle_s+'}';
    textstyle_s  = JSON.parse(textstyle_s);
  } else if('' != final_textstr_s){
    textstyle_s = final_textstr_s;
    textstyle_s = '{'+textstyle_s+'}';
    textstyle_s  = JSON.parse(textstyle_s);
  } else if('' != textstyle_s){
    textstyle_s = '{'+textstyle_s+'}';
    textstyle_s  = JSON.parse(textstyle_s);
  }
 
  if ('shape' == type.value) {
    var shapename = jQuery("#label_shape_type").find(':selected').attr('data-value'); 

    jQuery("#image_tab").css("display","none");
    jQuery("#shape_tab").css("display","block");

    jQuery("#display_picture1").css("display","none"); 
    jQuery("#display_picture2").css("display","none"); 
    jQuery("#display_picture3").css("display","none");

    jQuery("#next_display_picture1").css("display","none"); 
    jQuery("#next_display_picture2").css("display","none"); 
    jQuery("#next_display_picture3").css("display","none");

    jQuery("#labeltext1").css("display","none"); 
    jQuery("#labeltext2").css("display","none"); 
    jQuery("#labeltext3").css("display","none");

    if(document.getElementById('next_price').checked == true) {
      jQuery("#nextlabeltext1").css("display","none"); 
      jQuery("#nextlabeltext2").css("display","none"); 
      jQuery("#nextlabeltext3").css("display","none"); 

      jQuery("#shapenextposition1").css({'right' : '', 'bottom' : '' ,'top':''});
      jQuery("#shapenextposition1").css({'display':'block','position':'absolute','left':'0px','top':'0px'});

      jQuery("#shapenextposition2").css({'right' : '', 'bottom' : '' ,'top':''});
      jQuery("#shapenextposition2").css({'display':'block','position':'absolute','left':'0px','top':'0px'});

      jQuery("#shapenextposition3").css({'right' : '', 'bottom' : '' ,'top':''});
      jQuery("#shapenextposition3").css({'display':'block','position':'absolute','left':'0px','top':'0px'});

      jQuery("#shapenextprice1").attr('class','');
      jQuery("#shapenextprice1").attr('class',shapename);

      jQuery("#shapenextprice2").attr('class','');
      jQuery("#shapenextprice2").attr('class',shapename);

      jQuery("#shapenextprice3").attr('class','');
      jQuery("#shapenextprice3").attr('class',shapename);

      jQuery("#shapenextlabeltext1").css({'display':'block'});
      jQuery("#shapenextlabeltext2").css({'display':'block'});
      jQuery("#shapenextlabeltext3").css({'display':'block'});

      if('' != textstyle_l){
        jQuery("#shapenextprice1").css(textstyle_l);
      }
      if('' != textstyle_m){
        jQuery("#shapenextprice2").css(textstyle_m);
      }
      if('' != textstyle_s){
        jQuery("#shapenextprice3").css(textstyle_s); 
      }     
    }
    else
    {
      jQuery("#shapenextprice1").attr('class','');
      jQuery("#shapenextprice2").attr('class','');
      jQuery("#shapenextprice3").attr('class','');

      jQuery("#shapenextposition1").css({'display':'none'});
      jQuery("#shapenextposition2").css({'display':'none'});
      jQuery("#shapenextposition3").css({'display':'none'});

      jQuery("#nextlabeltext1").css({'display':'none'});
      jQuery("#nextlabeltext2").css({'display':'none'});
      jQuery("#nextlabeltext3").css({'display':'none'});

      
      jQuery("#shapeplaceholder1").attr('class','');
      jQuery("#shapeplaceholder1").attr('class',shapename);

      jQuery("#shapeplaceholder2").attr('class','');
      jQuery("#shapeplaceholder2").attr('class',shapename);

      jQuery("#shapeplaceholder3").attr('class','');
      jQuery("#shapeplaceholder3").attr('class',shapename);

      if('' != textstyle_l){
        jQuery("#shapeplaceholder1").css(textstyle_l); 
      }
      if('' != textstyle_m){
        jQuery("#shapeplaceholder2").css(textstyle_m);
      }
      if('' != textstyle_s){
        jQuery("#shapeplaceholder3").css(textstyle_s);
      }
 
      jQuery("#shapelabeltext1").css({'display':'block'}); 
      jQuery("#shapelabeltext2").css({'display':'block'}); 
      jQuery("#shapelabeltext3").css({'display':'block'});

      if(document.getElementById('upper_right').checked == true) 
      {
        jQuery("#shapeposition1").css({'left' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition1").css({'display':'block','position':'absolute','right':'0px','top':'0px'});

        jQuery("#shapeposition2").css({'left' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition2").css({'display':'block','position':'absolute','right':'0px','top':'0px'});

        jQuery("#shapeposition3").css({'left' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition3").css({'display':'block','position':'absolute','right':'0px','top':'0px'});

        if('rectangle_belevel_down' == shapename)
        {
          jQuery('.rectangle_belevel_down').addClass('down_right');
        }
        if('rectangle_belevel_up' == shapename)
        {
          jQuery('.rectangle_belevel_up').addClass('up_right');
        }
      }

      if(document.getElementById('upper_left').checked == true) 
      {
        jQuery("#shapeposition1").css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition1").css({'display':'block','position':'absolute','left':'0px','top':'0px'});

        jQuery("#shapeposition2").css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition2").css({'display':'block','position':'absolute','left':'0px','top':'0px'});

        jQuery("#shapeposition3").css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition3").css({'display':'block','position':'absolute','left':'0px','top':'0px'});
        
      }

      if(document.getElementById('lower_left').checked == true)  
      {
        jQuery("#shapeposition1").css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition1").css({'display':'block','position':'absolute','left':'0px','bottom':'0px'});

        jQuery("#shapeposition2").css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition2").css({'display':'block','position':'absolute','left':'0px','bottom':'0px'});

        jQuery("#shapeposition3").css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition3").css({'display':'block','position':'absolute','left':'0px','bottom':'0px'});
      }
      if(document.getElementById('lower_right').checked == true)  
      {
        jQuery("#shapeposition1").css({'left' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition1").css({'display':'block','position':'absolute','right':'0px','bottom':'0px'});

        jQuery("#shapeposition2").css({'left' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition2").css({'display':'block','position':'absolute','right':'0px','bottom':'0px'});

        jQuery("#shapeposition3").css({'left' : '', 'bottom' : '' ,'top':''});
        jQuery("#shapeposition3").css({'display':'block','position':'absolute','right':'0px','bottom':'0px'});


        if('rectangle_belevel_down' == shapename)
        {
          jQuery('.rectangle_belevel_down').addClass('down_right');
        }
        if('rectangle_belevel_up' == shapename)
        {
          jQuery('.rectangle_belevel_up').addClass('up_right');
        }
      }
    }

  } 
  if('picture' == type.value) {
    var elem = document.getElementById('display-images');
    var div = document.getElementsByClassName('img-label-wrap');
    if(elem !== null && elem !== '') {
      if(elem.getAttribute('src') == "")
      {
        jQuery(".img-label-wrap").css({'height' : 'auto','width' : 'auto'});
      }
    }
    jQuery("#shape_tab").css("display","none");
    jQuery("#image_tab").css("display","block"); 

    jQuery("#shapeplaceholder1").attr('class','');
    jQuery("#shapeplaceholder2").attr('class','');
    jQuery("#shapeplaceholder3").attr('class','');

    jQuery("#shapeposition1").css("display","none"); 
    jQuery("#shapeposition2").css("display","none"); 
    jQuery("#shapeposition3").css("display","none"); 

    jQuery("#shapenextprice1").attr('class','');
    jQuery("#shapenextprice2").attr('class','');
    jQuery("#shapenextprice3").attr('class','');

    jQuery("#shapenextposition1").css("display","none"); 
    jQuery("#shapenextposition2").css("display","none"); 
    jQuery("#shapenextposition3").css("display","none");

    jQuery("#labeltext1").css("display","none"); 
    jQuery("#labeltext2").css("display","none"); 
    jQuery("#labeltext3").css("display","none");

    jQuery("#nextlabeltext1").css("display","none"); 
    jQuery("#nextlabeltext2").css("display","none"); 
    jQuery("#nextlabeltext3").css("display","none"); 

    if(document.getElementById('next_price').checked == true)
    {
      if(elem !== null && elem !== '') {
        if(elem.getAttribute('src') == "")
        {
          div[3].classList.add("hw-auto-nleft");
          div[4].classList.add("hw-auto-nleft");
          div[5].classList.add("hw-auto-nleft");
        }
      }

      jQuery("#display_picture1").css("display","none"); 
      jQuery("#display_picture2").css("display","none"); 
      jQuery("#display_picture3").css("display","none");

      jQuery("#shapenextlabeltext1").css("display","none"); 
      jQuery("#shapenextlabeltext2").css("display","none"); 
      jQuery("#shapenextlabeltext3").css("display","none");

      jQuery("#next_display_picture1").css("display","inline-block"); 
      jQuery("#next_display_picture2").css("display","inline-block"); 
      jQuery("#next_display_picture3").css("display","inline-block");

      if('' != col){
        jQuery('.next_display_picture1 ').css(col);
      }
      if('' != col_m){
        jQuery('.next_display_picture2 ').css(col_m);
      }
      if('' != col_s){
         jQuery('.next_display_picture2 ').css(col_s);
      }

      jQuery("#nextpictext1").css({'display':'block'});
      jQuery("#nextpictext2").css({'display':'block'}); 
      jQuery("#nextpictext3").css({'display':'block'});

      if('' != text_col){
        jQuery("#nextpictext1").css(text_col);
      }
      if('' != text_col_m){
        jQuery("#nextpictext2").css(text_col_m); 
      }
      if('' != text_col_s){
        jQuery("#nextpictext3").css(text_col_s);
      }
    } 
    else
    {
      jQuery("#next_display_picture1").css("display","none"); 
      jQuery("#next_display_picture2").css("display","none"); 
      jQuery("#next_display_picture3").css("display","none");

      jQuery("#shapelabeltext1").css("display","none"); 
      jQuery("#shapelabeltext2").css("display","none"); 
      jQuery("#shapelabeltext3").css("display","none");

      jQuery("#display_picture1").css("display","block"); 
      jQuery("#display_picture2").css("display","block"); 
      jQuery("#display_picture3").css("display","block");

      if('' != col){
        jQuery('.display_picture1 ').css(col);
      }
      if('' != col_m){
        jQuery('.display_picture2 ').css(col_m);
      }
      if('' != col_s){
        jQuery('.display_picture3 ').css(col_s);
      }   

      if('' != text_col){
        jQuery("#pictext1").css(text_col);
      }
      if('' != text_col_m){
        jQuery("#pictext2").css(text_col_m);
      }
      if('' != text_col_s){
        jQuery("#pictext3").css(text_col_s);
      }

      if(document.getElementById('upper_right').checked == true)
      {
        if(elem !== null && elem !== '') {
          if(elem.getAttribute('src') == "")
          {
            div[0].classList.add("hw-auto-tright");
            div[1].classList.add("hw-auto-tright");
            div[2].classList.add("hw-auto-tright");
          }
        }
        jQuery('#display_picture1 ').css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture1 ').css({'position': 'absolute','right':'0px','top':'0px'});

        jQuery('#display_picture2 ').css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture2 ').css({'position': 'absolute','right':'0px','top':'0px'});

        jQuery('#display_picture3 ').css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture3 ').css({'position': 'absolute','right':'0px','top':'0px'});

        jQuery('#pictext1').css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery('#pictext1').css({'display':'block'});

        jQuery('#pictext2').css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery('#pictext2').css({'display':'block'});

        jQuery('#pictext3').css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery('#pictext3').css({'display':'block'});

      }
      if(document.getElementById('upper_left').checked == true) 
      {
        if(elem !== null && elem !== '') {
          if(elem.getAttribute('src') == "")
          {
            div[0].classList.add("hw-auto-tleft");
            div[1].classList.add("hw-auto-tleft");
            div[2].classList.add("hw-auto-tleft");
          }
        }
        jQuery('#display_picture1 ').css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture1 ').css({'position': 'absolute','left':'0px','top':'0px'});

        jQuery('#display_picture2 ').css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture2 ').css({'position': 'absolute','left':'0px','top':'0px'});

        jQuery('#display_picture3 ').css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture3 ').css({'position': 'absolute','left':'0px','top':'0px'});

        jQuery('#pictext1').css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery('#pictext1').css({'display':'block'});

        jQuery('#pictext2').css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery('#pictext2').css({'display':'block'});

        jQuery('#pictext3').css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery('#pictext3').css({'display':'block'});

      }
      if(document.getElementById('lower_left').checked == true)
      {
        if(elem !== null && elem !== '') {
          if(elem.getAttribute('src') == "")
          {
            div[0].classList.add("hw-auto-bleft");
            div[1].classList.add("hw-auto-bleft");
            div[2].classList.add("hw-auto-bleft");
          }
        }
        jQuery('#display_picture1 ').css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture1 ').css({'position': 'absolute','left':'0px', 'bottom':'0px'});

        jQuery('#display_picture2 ').css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture2 ').css({'position': 'absolute','left':'0px', 'bottom':'0px'});

        jQuery('#display_picture3 ').css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture3 ').css({'position': 'absolute','left':'0px', 'bottom':'0px'});

        jQuery('#pictext1').css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery('#pictext1').css({'display':'block'});

        jQuery('#pictext2').css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery('#pictext2').css({'display':'block'});

        jQuery('#pictext3').css({'right' : '', 'bottom' : '' ,'top':''});
        jQuery('#pictext3').css({'display':'block'});
      }
      if(document.getElementById('lower_right').checked == true)
      {
        if(elem !== null && elem !== '') {
          if(elem.getAttribute('src') == "")
          {
            div[0].classList.add("hw-auto-bright");
            div[1].classList.add("hw-auto-bright");
            div[2].classList.add("hw-auto-bright");
          }
        }
        jQuery('#display_picture1 ').css({'left' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture1 ').css({'position': 'absolute','right':'0px', 'bottom':'0px'});

        jQuery('#display_picture2 ').css({'left' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture2 ').css({'position': 'absolute','right':'0px', 'bottom':'0px'});

        jQuery('#display_picture3 ').css({'left' : '', 'bottom' : '' ,'top':''});
        jQuery('#display_picture3 ').css({'position': 'absolute','right':'0px', 'bottom':'0px'});

        jQuery('#pictext1').css({'left' : '', 'bottom' : '' ,'top':''});
        jQuery('#pictext1').css({'display':'block'});

        jQuery('#pictext2').css({'left' : '', 'bottom' : '' ,'top':''});
        jQuery('#pictext2').css({'display':'block'});

        jQuery('#pictext3').css({'left' : '', 'bottom' : '' ,'top':''});
        jQuery('#pictext3').css({'display':'block'});
      }
    }

  }
  if('text' == type.value) {
    jQuery("#image_tab").css("display","none");
    jQuery("#shape_tab").css("display","none");

    jQuery("#display_picture1").css("display","none"); 
    jQuery("#display_picture2").css("display","none"); 
    jQuery("#display_picture3").css("display","none");

    jQuery("#next_display_picture1").css("display","none"); 
    jQuery("#next_display_picture2").css("display","none"); 
    jQuery("#next_display_picture3").css("display","none");

    jQuery("#shapenextlabeltext1").css("display","none"); 
    jQuery("#shapenextlabeltext2").css("display","none"); 
    jQuery("#shapenextlabeltext3").css("display","none");

    jQuery("#shapeplaceholder1").attr('class','');
    jQuery("#shapeplaceholder2").attr('class','');
    jQuery("#shapeplaceholder3").attr('class','');

    jQuery("#shapeposition1").css("display","none");
    jQuery("#shapeposition2").css("display","none");
    jQuery("#shapeposition3").css("display","none");

    jQuery("#shapelabeltext1").css("display","none"); 
    jQuery("#shapelabeltext2").css("display","none"); 
    jQuery("#shapelabeltext3").css("display","none");

    jQuery("#shapenextprice1").attr('class','');
    jQuery("#shapenextprice2").attr('class','');
    jQuery("#shapenextprice3").attr('class','');

    jQuery("#shapenextposition1").css("display","none");
    jQuery("#shapenextposition2").css("display","none");
    jQuery("#shapenextposition3").css("display","none");

    if(document.getElementById('next_price').checked == true)
    {
      jQuery("#nextlabeltext1").css("display","block");
      jQuery("#nextlabeltext2").css("display","block"); 
      jQuery("#nextlabeltext3").css("display","block");

      if('' != textstyle_l){
        jQuery("#nextlabeltext1").css(textstyle_l);
      }
      if('' != textstyle_l){
        jQuery("#nextlabeltext2").css(textstyle_m); 
      }
      if('' != textstyle_l){
        jQuery("#nextlabeltext3").css(textstyle_s);
      }
    }
    else
    {
        jQuery("#image_tab").css("display","none");
        jQuery("#shape_tab").css("display","none");

        if('' != textstyle_l){
          jQuery("#labeltext1").css(textstyle_l);
        }
        if('' != textstyle_m){
          jQuery("#labeltext2").css(textstyle_m); 
        }
        if('' != textstyle_s){
          jQuery("#labeltext3").css(textstyle_s);
        }

      if(document.getElementById('upper_right').checked == true)
      {
        jQuery("#labeltext1").css({ 'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext1").css({'display':'block','position': 'absolute','right':'0px', 'top':'0px'});

        jQuery("#labeltext2").css({'right' : '', 'left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext2").css({'display':'block','position': 'absolute','right':'0px', 'top':'0px'});

        jQuery("#labeltext3").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext3").css({'display':'block','position': 'absolute','right':'0px', 'top':'0px'});
      } 
      if(document.getElementById('upper_left').checked == true)
      {
        jQuery("#labeltext1").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext1").css({'display':'block','position': 'absolute','left':'0px', 'top':'0px'});

        jQuery("#labeltext2").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext2").css({'display':'block','position': 'absolute','left':'0px', 'top':'0px'});

        jQuery("#labeltext3").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext3").css({'display':'block','position': 'absolute','left':'0px', 'top':'0px'});
      }
      if(document.getElementById('lower_right').checked == true)
      {
        jQuery("#labeltext1").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext1").css({'display':'block','position': 'absolute','right':'0px', 'bottom':'0px'});

        jQuery("#labeltext2").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext2").css({'display':'block','position': 'absolute','right':'0px', 'bottom':'0px'});

        jQuery("#labeltext3").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext3").css({'display':'block','position': 'absolute','right':'0px', 'bottom':'0px'});
      }
      if(document.getElementById('lower_left').checked == true)
      {
        jQuery("#labeltext1").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext1").css({'display':'block','position': 'absolute','left':'0px', 'bottom':'0px'});

        jQuery("#labeltext2").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext2").css({'display':'block','position': 'absolute','left':'0px', 'bottom':'0px'});

        jQuery("#labeltext3").css({'right' : '','left' : '', 'bottom' : '' ,'top':''});
        jQuery("#labeltext3").css({'display':'block','position': 'absolute','left':'0px', 'bottom':'0px'});
      }
    }
  } 
}

function sizeClick(size) {
  if ('large' == size.value) 
  {
    if(document.getElementById('next_price').checked == true) 
    {
      if(document.getElementById('label_shape').checked == true)
      {
        var str = jQuery("#shapenextlabeltext1").text();
      }
      else if(document.getElementById('label_text').checked == true)
      {
        var str = jQuery("#nextlabeltext1").text();
      }
       else if(document.getElementById('label_picture').checked == true)
      {
        var str = jQuery("#nextpictext1").text();
      }
      
    }
    else
    {
      if(document.getElementById('label_shape').checked == true)
      {
        var str = jQuery("#shapelabeltext1").text();
      }
      else if(document.getElementById('label_text').checked == true) 
      {
        var str = jQuery("#labeltext1").text();
      }
      else if(document.getElementById('label_picture').checked == true)
      {
        var str = jQuery("#pictext1").text();
      }
      
    }
    
    jQuery("#source_text").val(str);

    jQuery("#source_text").on("input", function()
    {      
      if(document.getElementById('next_price').checked == true) 
      {
        if(document.getElementById('label_shape').checked == true)
        {
          jQuery("#shapenextlabeltext2").text('Sale');
          jQuery("#shapenextlabeltext3").text('Sale');
          jQuery("#shapenextlabeltext1").text(jQuery(this).val()); 
        }
        else if(document.getElementById('label_text').checked == true)
        {
          jQuery("#nextlabeltext2").text('Sale');
          jQuery("#nextlabeltext3").text('Sale');
          jQuery("#nextlabeltext1").text(jQuery(this).val());                  
        }
        else if(document.getElementById('label_picture').checked == true)
        {
          jQuery("#nextpictext2").text('Sale');
          jQuery("#nextpictext3").text('Sale');
          jQuery("#nextpictext1").text(jQuery(this).val());
        }
      }
      else
      {
         if(document.getElementById('label_shape').checked == true)
        {
          jQuery("#shapelabeltext2").text('Sale');
          jQuery("#shapelabeltext3").text('Sale');
          jQuery("#shapelabeltext1").text(jQuery(this).val()); 
        }
        else if(document.getElementById('label_text').checked == true)
        {
          jQuery("#labeltext2").text('Sale');
          jQuery("#labeltext3").text('Sale');
          jQuery("#labeltext1").text(jQuery(this).val());
        }
        else if(document.getElementById('label_picture').checked == true)
        {
          jQuery("#pictext2").text('Sale');
          jQuery("#pictext3").text('Sale');
          jQuery("#pictext1").text(jQuery(this).val());
        }
      }
    });
  } 
  else if('medium' == size.value) 
  {
    if(document.getElementById('next_price').checked == true) 
    {
      if(document.getElementById('label_shape').checked == true)
      {
        var str = jQuery("#shapenextlabeltext2").text();
      }
      else  if(document.getElementById('label_text').checked == true)
      {
        var str = jQuery("#nextlabeltext2").text();
      }
      else if(document.getElementById('label_picture').checked == true)
      {
        var str = jQuery("#nextpictext2").text();
      }
    }
    else
    {
      if(document.getElementById('label_shape').checked == true)
      {
        var str = jQuery("#shapelabeltext2").text();
      }
      else if(document.getElementById('label_text').checked == true)
      {
        var str = jQuery("#labeltext2").text();
      }
      else if(document.getElementById('label_picture').checked == true)
      {
        var str = jQuery("#pictext2").text();
      }
    }
    jQuery("#source_text").val(str);

    jQuery("#source_text").on("input", function()
    {
     
      if(document.getElementById('next_price').checked == true) 
      {
        if(document.getElementById('label_shape').checked == true)
        {
          jQuery("#shapenextlabeltext1").text('Sale');
          jQuery("#shapenextlabeltext3").text('Sale');
          jQuery("#shapenextlabeltext2").text(jQuery(this).val());
        }
        else if(document.getElementById('label_text').checked == true)
        {
          jQuery("#nextlabeltext1").text('Sale');
          jQuery("#nextlabeltext3").text('Sale');
          jQuery("#nextlabeltext2").text(jQuery(this).val());
        }
        else if(document.getElementById('label_picture').checked == true)
        {
          jQuery("#nextpictext1").text('Sale');
          jQuery("#nextpictext3").text('Sale');
          jQuery("#nextpictext2").text(jQuery(this).val());
        }

      }
      else
      {
        if(document.getElementById('label_shape').checked == true)
        {
          jQuery("#shapelabeltext1").text('Sale');
          jQuery("#shapelabeltext3").text('Sale');
          jQuery("#shapelabeltext2").text(jQuery(this).val());
        }
        else if(document.getElementById('label_text').checked == true)
        {
          jQuery("#labeltext1").text('Sale');
          jQuery("#labeltext3").text('Sale');
          jQuery("#labeltext2").text(jQuery(this).val());
        }
        else if(document.getElementById('label_picture').checked == true)
        {
          jQuery("#pictext1").text('Sale');
          jQuery("#pictext3").text('Sale');
          jQuery("#pictext2").text(jQuery(this).val());
        }

      }
    });
  }
  else if('small' == size.value) 
  {
    if(document.getElementById('next_price').checked == true) 
    {
      if(document.getElementById('label_shape').checked == true)
      {
        var str = jQuery("#shapenextlabeltext3").text();
      }
      else if(document.getElementById('label_text').checked == true)
      {
        var str = jQuery("#nextlabeltext3").text();
      }
      else if(document.getElementById('label_picture').checked == true)
      {
        var str = jQuery("#nextpictext3").text();
      }
    }
    else
    {
      if(document.getElementById('label_shape').checked == true)
      {
        var str = jQuery("#shapelabeltext3").text();
      }
      else if(document.getElementById('label_text').checked == true)
      {
        var str = jQuery("#labeltext3").text();
      }
      else if(document.getElementById('label_picture').checked == true) 
      {
        var str = jQuery("#pictext3").text();
      }
    }
    jQuery("#source_text").val(str);

    jQuery("#source_text").on("input", function()
    {
      if(document.getElementById('next_price').checked == true) 
      {
        if(document.getElementById('label_shape').checked == true)
        {
          jQuery("#shapenextlabeltext1").text('Sale');
          jQuery("#shapenextlabeltext2").text('Sale');
          jQuery("#shapenextlabeltext3").text(jQuery(this).val());
        }
        else if(document.getElementById('label_text').checked == true)
        {
          jQuery("#nextlabeltext1").text('Sale');
          jQuery("#nextlabeltext2").text('Sale');
          jQuery("#nextlabeltext3").text(jQuery(this).val());
        }
        else if(document.getElementById('label_picture').checked == true)
        {
          jQuery("#nextpictext1").text('Sale');
          jQuery("#nextpictext2").text('Sale');
          jQuery("#nextpictext3").text(jQuery(this).val());
        }
      }
      else
      {        
        if(document.getElementById('label_shape').checked == true)
        {
          jQuery("#shapelabeltext1").text('Sale');
          jQuery("#shapelabeltext2").text('Sale');
          jQuery("#shapelabeltext3").text(jQuery(this).val());
        }
        else if(document.getElementById('label_text').checked == true)
        {
          jQuery("#labeltext1").text('Sale');
          jQuery("#labeltext2").text('Sale');
          jQuery("#labeltext3").text(jQuery(this).val());
        }
        else if(document.getElementById('label_picture').checked == true)
        {
          jQuery("#pictext1").text('Sale');
          jQuery("#pictext2").text('Sale');
          jQuery("#pictext3").text(jQuery(this).val());
        }
      }      
    });
  }
}
function changeContainerCss() {
  var shapename = jQuery("#label_shape_type").find(':selected').attr('data-value');
  var background_color = '';
  var con_width     = '';
  var con_height    = '';
  var widthpx       = '';
  var heightpx      = '';
  const element = document.querySelector('#choose_container_color')
  const style = getComputedStyle(element)
  var rgb_col = style.backgroundColor;
  var colorname = jQuery('#container_color').val();
  
  if('' != colorname){
    background_color = 'background-color:#'+colorname + ';';
    jQuery('#label_container_css').text(background_color);
  }
 
  var width = jQuery('.label_container_width').val();
  if('' != width) {
    widthpx = width+'px';
    con_width = background_color+'\n width:'+width + 'px;';
    jQuery('#label_container_css').text(con_width);
  }  

  var height = jQuery('.label_container_height').val();
  if('' != height) {
    heightpx = height+'px';
    con_height = con_width+'\n height:'+height + 'px;';
    jQuery('#label_container_css').text(con_height);
  }

/*--------------------------------------------------------------*/
  var m_background_color = '';
  var m_con_width     = '';
  var m_con_height    = '';
  var m_widthpx       = '';
  var m_heightpx      = '';
  const m_element = document.querySelector('#choose_container_color_medium')
  const m_style = getComputedStyle(m_element)
  var m_rgb_col = m_style.backgroundColor;
  var m_colorname = jQuery('#container_color_medium').val();
  
  if('' != m_colorname){
    m_background_color = 'background-color:#'+m_colorname + ';';
    jQuery('#medium_label_container_css').text(m_background_color);
  }
 
  var m_width = jQuery('.label_container_width_medium').val();
  if('' != m_width) {
    m_widthpx = m_width+'px';
    m_con_width = m_background_color+'\n width:'+m_width + 'px;';
    jQuery('#medium_label_container_css').text(m_con_width);
  }  

  var m_height = jQuery('.label_container_height_medium').val();
  if('' != m_height) {
    m_heightpx = m_height+'px';
    m_con_height = m_con_width+'\n height:'+m_height + 'px;';
    jQuery('#medium_label_container_css').text(m_con_height);
  }
  /*--------------------------------------------------------------*/
  var s_background_color = '';
  var s_con_width     = '';
  var s_con_height    = '';
  var s_widthpx       = '';
  var s_heightpx      = '';
  const s_element = document.querySelector('#choose_container_color_small')
  const s_style = getComputedStyle(s_element)
  var s_rgb_col = s_style.backgroundColor;
  var s_colorname = jQuery('#container_color_small').val();

  if('' != s_colorname){
    s_background_color = 'background-color:#'+s_colorname + ';';
    jQuery('#small_label_container_css').text(s_background_color);
  }
 
  var s_width = jQuery('.label_container_width_small').val();
  if('' != s_width) {
    s_widthpx = s_width+'px';
    s_con_width = s_background_color+'\n width:'+s_width + 'px;';
    jQuery('#small_label_container_css').text(s_con_width);
  }  

  var s_height = jQuery('.label_container_height_small').val();
  if('' != s_height) {
    s_heightpx = s_height+'px';
    s_con_height = s_con_width+'\n height:'+s_height + 'px;';
    jQuery('#small_label_container_css').text(s_con_height);
  }
  /*--------------------------------------------------------------*/

  if(document.getElementById('next_price').checked == true)
  {
    if(document.getElementById('label_large').checked == true)
    {    
      if(document.getElementById('label_shape').checked == true) {
        if('square' == shapename)
        {
          jQuery('#child1 .square').css({'width':'','height':'','background-color':''});
          jQuery('#child1 .square').css({'width':widthpx,'height':heightpx,'background-color':rgb_col});
        }
        if('rectangle' == shapename)
        {
          jQuery('#child1 .rectangle').css({'width':'','height':'','background-color':''});
          jQuery('#child1 .rectangle').css({'width':widthpx,'height':heightpx,'background-color':rgb_col});
        }
        if('rectangle_belevel_down' == shapename)
        {
          jQuery('#child1 .rectangle_belevel_down').css({'width':'','height':'','background-color':''});
          jQuery('#child1 .rectangle_belevel_down').css({'width':widthpx,'height':heightpx,'background-color':rgb_col});
        }
        if('rectangle_belevel_up' == shapename)
        {
          jQuery('#child1 .rectangle_belevel_up').css({'width':'','height':'','background-color':''});
          jQuery('#child1 .rectangle_belevel_up').css({'width':widthpx,'height':heightpx,'background-color':rgb_col});
        }
        if('circle' == shapename)
        {
          jQuery('#child1 .circle').css({'width':'','height':'','background-color':''});
          jQuery('#child1 .circle').css({'width':widthpx,'height':heightpx,'background-color':rgb_col});
        }
        if('flag' == shapename)
        {
          jQuery('#child1 .flag').css({'width':'','height':'','background-color':''});
          jQuery('#child1 .flag').css({'width':widthpx,'height':heightpx,'background-color':rgb_col});
        }
        if('point_burst' == shapename)
        {
          jQuery('#child1 .point_burst').css({'width':'','height':'','background-color':''});
          jQuery('#child1 .point_burst').css({'width':widthpx,'height':heightpx,'background-color':rgb_col});
        }
      }
      else if(document.getElementById('label_text').checked == true) {
        jQuery('#child1 #nextlabeltext1').css({'width':'','height':'','background-color':''});
        jQuery('#child1 #nextlabeltext1').css({'width':widthpx,'height':heightpx,'background-color':rgb_col});
      }
      else if(document.getElementById('label_picture').checked == true) {
        jQuery('#child1 .next_display_picture1').css({'width':'','height':'','background-color':''});
        jQuery('#child1 .next_display_picture1').css({'width':widthpx,'height':heightpx,'background-color':rgb_col});
      }

    }
    else if(document.getElementById('label_medium').checked == true)
    {
      if(document.getElementById('label_shape').checked == true) {
        if('square' == shapename)
        {
          jQuery('#child2 .square').css({'width':'','height':'','background-color':''});
          jQuery('#child2 .square').css({'width':m_widthpx,'height':m_heightpx,'background-color':m_rgb_col});

        }
        if('rectangle' == shapename)
        {
          jQuery('#child2 .rectangle').css({'width':'','height':'','background-color':''});
          jQuery('#child2 .rectangle').css({'width':m_widthpx,'height':m_heightpx,'background-color':m_rgb_col});

        }
        if('rectangle_belevel_down' == shapename)
        {
          jQuery('#child2 .rectangle_belevel_down').css({'width':'','height':'','background-color':''});
          jQuery('#child2 .rectangle_belevel_down').css({'width':m_widthpx,'height':m_heightpx,'background-color':m_rgb_col});

        }
        if('rectangle_belevel_up' == shapename)
        {
          jQuery('#child2 .rectangle_belevel_up').css({'width':'','height':'','background-color':''});
          jQuery('#child2 .rectangle_belevel_up').css({'width':m_widthpx,'height':m_heightpx,'background-color':m_rgb_col});
        }
        if('circle' == shapename)
        {
          jQuery('#child2 .circle').css({'width':'','height':'','background-color':''});
          jQuery('#child2 .circle').css({'width':m_widthpx,'height':m_heightpx,'background-color':m_rgb_col});
        }
        if('flag' == shapename)
        {
          jQuery('#child2 .flag').css({'width':'','height':'','background-color':''});
          jQuery('#child2 .flag').css({'width':m_widthpx,'height':m_heightpx,'background-color':m_rgb_col});
        }
         if('point_burst' == shapename)
        {
          jQuery('#child2 .point_burst').css({'width':'','height':'','background-color':''});
          jQuery('#child2 .point_burst').css({'width':m_widthpx,'height':m_heightpx,'background-color':m_rgb_col});
        }
      }
      else if(document.getElementById('label_text').checked == true) {
        jQuery('#child2 #nextlabeltext2').css({'width':'','height':'','background-color':''});
        jQuery('#child2 #nextlabeltext2').css({'width':m_widthpx,'height':m_heightpx,'background-color':m_rgb_col});
      }
      else if(document.getElementById('label_picture').checked == true) {
        jQuery('#child2 .next_display_picture2').css({'width':'','height':'','background-color':''});
        jQuery('#child2 .next_display_picture2').css({'width':m_widthpx,'height':m_heightpx,'background-color':m_rgb_col});
      }
    }
    else if(document.getElementById('label_small').checked == true)
    {
      if(document.getElementById('label_shape').checked == true) {
        if('square' == shapename)
        {
          jQuery('#child3 .square').css({'width':'','height':'','background-color':''});
          jQuery('#child3 .square').css({'width':s_widthpx,'height':s_heightpx,'background-color':s_rgb_col});

        }
        if('rectangle' == shapename)
        {
          jQuery('#child3 .rectangle').css({'width':'','height':'','background-color':''});
          jQuery('#child3 .rectangle').css({'width':s_widthpx,'height':s_heightpx,'background-color':s_rgb_col});

        }
        if('rectangle_belevel_down' == shapename)
        {
          jQuery('#child3 .rectangle_belevel_down').css({'width':'','height':'','background-color':''});
          jQuery('#child3 .rectangle_belevel_down').css({'width':s_widthpx,'height':s_heightpx,'background-color':s_rgb_col});

        }
        if('rectangle_belevel_up' == shapename)
        {
          jQuery('#child3 .rectangle_belevel_up').css({'width':'','height':'','background-color':''});
          jQuery('#child3 .rectangle_belevel_up').css({'width':s_widthpx,'height':s_heightpx,'background-color':s_rgb_col});
        }
        if('circle' == shapename)
        {
          jQuery('#child3 .circle').css({'width':'','height':'','background-color':''});
          jQuery('#child3 .circle').css({'width':s_widthpx,'height':s_heightpx,'background-color':s_rgb_col});
        }
        if('flag' == shapename)
        {
          jQuery('#child3 .flag').css({'width':'','height':'','background-color':''});
          jQuery('#child3 .flag').css({'width':s_widthpx,'height':s_heightpx,'background-color':s_rgb_col});
        }
        if('point_burst' == shapename)
        {
          jQuery('#child3 .point_burst').css({'width':'','height':'','background-color':''});
          jQuery('#child3 .point_burst').css({'width':s_widthpx,'height':s_heightpx,'background-color':s_rgb_col});
        }
      }
      else if(document.getElementById('label_text').checked == true) {
        jQuery('#child3 #nextlabeltext3').css({'width':'','height':'','background-color':''});
        jQuery('#child3 #nextlabeltext3').css({'width':s_widthpx,'height':s_heightpx,'background-color':s_rgb_col});
      }
      else if(document.getElementById('label_picture').checked == true) {
        jQuery('#child3 .next_display_picture3').css({'width':'','height':'','background-color':''});
        jQuery('#child3 .next_display_picture3').css({'width':s_widthpx,'height':s_heightpx,'background-color':s_rgb_col});
      }
    }
  }
  else
  {
    if(document.getElementById('label_large').checked == true)
    {    
      if(document.getElementById('label_shape').checked == true) {
        if('square' == shapename)
        {
          jQuery('#parent1 .square').css({'width':'','height':'','background-color':''});
          jQuery('#parent1 .square').css({'width':widthpx,'height':heightpx,'background-color':rgb_col});

        }
        if('rectangle' == shapename)
        {
          jQuery('#parent1 .rectangle').css({'width':'','height':'','background-color':''});
          jQuery('#parent1 .rectangle').css({'width':widthpx,'height':heightpx,'background-color':rgb_col});

        }
        if('rectangle_belevel_down' == shapename)
        {
          jQuery('#parent1 .rectangle_belevel_down').css({'width':'','height':'','background-color':''});
          jQuery('#parent1 .rectangle_belevel_down').css({'width':widthpx,'height':heightpx,'background-color':rgb_col});

        }
        if('rectangle_belevel_up' == shapename)
        {
          jQuery('#parent1 .rectangle_belevel_up').css({'width':'','height':'','background-color':''});
          jQuery('#parent1 .rectangle_belevel_up').css({'width':widthpx,'height':heightpx,'background-color':rgb_col});
        }
        if('circle' == shapename)
        {
          jQuery('#parent1 .circle').css({'width':'','height':'','background-color':''});
          jQuery('#parent1 .circle').css({'width':widthpx,'height':heightpx,'background-color':rgb_col});
        }
        if('flag' == shapename)
        {
          jQuery('#parent1 .flag').css({'width':'','height':'','background-color':''});
          jQuery('#parent1 .flag').css({'width':widthpx,'height':heightpx,'background-color':rgb_col});
        }
         if('point_burst' == shapename)
        {
          jQuery('#parent1 .point_burst').css({'width':'','height':'','background-color':''});
          jQuery('#parent1 .point_burst').css({'width':widthpx,'height':heightpx,'background-color':rgb_col});
        }
      }
      else if(document.getElementById('label_text').checked == true) {
        jQuery('#parent1 #labeltext1').css({'width':'','height':'','background-color':''});
        jQuery('#parent1 #labeltext1').css({'width':widthpx,'height':heightpx,'background-color':rgb_col});
      }
      else if(document.getElementById('label_picture').checked == true) {
        jQuery('#parent1 .display_picture1').css({'width':'','height':'','background-color':''});
        jQuery('#parent1 .display_picture1').css({'width':widthpx,'height':heightpx,'background-color':rgb_col});
      }
    }
    else if(document.getElementById('label_medium').checked == true)
    {
      if(document.getElementById('label_shape').checked == true) {
        if('square' == shapename)
        {
          jQuery('#parent2 .square').css({'width':'','height':'','background-color':''});
          jQuery('#parent2 .square').css({'width':m_widthpx,'height':m_heightpx,'background-color':m_rgb_col});

        }
        if('rectangle' == shapename)
        {
          jQuery('#parent2 .rectangle').css({'width':'','height':'','background-color':''});
          jQuery('#parent2 .rectangle').css({'width':m_widthpx,'height':m_heightpx,'background-color':m_rgb_col});

        }
        if('rectangle_belevel_down' == shapename)
        {
          jQuery('#parent2 .rectangle_belevel_down').css({'width':'','height':'','background-color':''});
          jQuery('#parent2 .rectangle_belevel_down').css({'width':m_widthpx,'height':m_heightpx,'background-color':m_rgb_col});

        }
        if('rectangle_belevel_up' == shapename)
        {
          jQuery('#parent2 .rectangle_belevel_up').css({'width':'','height':'','background-color':''});
          jQuery('#parent2 .rectangle_belevel_up').css({'width':m_widthpx,'height':m_heightpx,'background-color':m_rgb_col});
        }
        if('circle' == shapename)
        {
          jQuery('#parent2 .circle').css({'width':'','height':'','background-color':''});
          jQuery('#parent2 .circle').css({'width':m_widthpx,'height':m_heightpx,'background-color':m_rgb_col});
        }
        if('flag' == shapename)
        {
          jQuery('#parent2 .flag').css({'width':'','height':'','background-color':''});
          jQuery('#parent2 .flag').css({'width':m_widthpx,'height':m_heightpx,'background-color':m_rgb_col});
        }
        if('point_burst' == shapename)
        {
          jQuery('#parent2 .point_burst').css({'width':'','height':'','background-color':''});
          jQuery('#parent2 .point_burst').css({'width':m_widthpx,'height':m_heightpx,'background-color':m_rgb_col});
        }
      }
      else if(document.getElementById('label_text').checked == true) {
        jQuery('#parent2 #labeltext2').css({'width':'','height':'','background-color':''});
        jQuery('#parent2 #labeltext2').css({'width':m_widthpx,'height':m_heightpx,'background-color':m_rgb_col});
      }
      else if(document.getElementById('label_picture').checked == true) {
        jQuery('#parent2 .display_picture2').css({'width':'','height':'','background-color':''});
        jQuery('#parent2 .display_picture2').css({'width':m_widthpx,'height':m_heightpx,'background-color':m_rgb_col});
      }
    }
    else if(document.getElementById('label_small').checked == true)
    {
      if(document.getElementById('label_shape').checked == true) {
        if('square' == shapename)
        {
          jQuery('#parent3 .square').css({'width':'','height':'','background-color':''});
          jQuery('#parent3 .square').css({'width':s_widthpx,'height':s_heightpx,'background-color':s_rgb_col});

        }
        if('rectangle' == shapename)
        {
          jQuery('#parent3 .rectangle').css({'width':'','height':'','background-color':''});
          jQuery('#parent3 .rectangle').css({'width':s_widthpx,'height':s_heightpx,'background-color':s_rgb_col});

        }
        if('rectangle_belevel_down' == shapename)
        {
          jQuery('#parent3 .rectangle_belevel_down').css({'width':'','height':'','background-color':''});
          jQuery('#parent3 .rectangle_belevel_down').css({'width':s_widthpx,'height':s_heightpx,'background-color':s_rgb_col});

        }
        if('rectangle_belevel_up' == shapename)
        {
          jQuery('#parent3 .rectangle_belevel_up').css({'width':'','height':'','background-color':''});
          jQuery('#parent3 .rectangle_belevel_up').css({'width':s_widthpx,'height':s_heightpx,'background-color':s_rgb_col});
        }
        if('circle' == shapename)
        {
          jQuery('#parent3 .circle').css({'width':'','height':'','background-color':''});
          jQuery('#parent3 .circle').css({'width':s_widthpx,'height':s_heightpx,'background-color':s_rgb_col});
        }
        if('flag' == shapename)
        {
          jQuery('#parent3 .flag').css({'width':'','height':'','background-color':''});
          jQuery('#parent3 .flag').css({'width':s_widthpx,'height':s_heightpx,'background-color':s_rgb_col});
        }
         if('point_burst' == shapename)
        {
          jQuery('#parent3 .point_burst').css({'width':'','height':'','background-color':''});
          jQuery('#parent3 .point_burst').css({'width':s_widthpx,'height':s_heightpx,'background-color':s_rgb_col});
        }
      }
      else if(document.getElementById('label_text').checked == true) {
        jQuery('#parent3 #labeltext3').css({'width':'','height':'','background-color':''});
        jQuery('#parent3 #labeltext3').css({'width':s_widthpx,'height':s_heightpx,'background-color':s_rgb_col});
      }
      else if(document.getElementById('label_picture').checked == true) {
        jQuery('#parent3 .display_picture3').css({'width':'','height':'','background-color':''});
        jQuery('#parent3 .display_picture3').css({'width':s_widthpx,'height':s_heightpx,'background-color':s_rgb_col});
      }
    }
  }
   

}

function changeLabelTextCss() {
  var text_color = '';
  var text_size  = '';
  var sizepx     = '';
  const element = document.querySelector('#choose_labeltext_color')
  const style = getComputedStyle(element)
  var text_rgb_col = style.backgroundColor;
  var colorname = jQuery('#adminlabeltext_color').val();

  if('' != colorname){
    text_color = 'color:#'+colorname + ';';
    jQuery('#label_text_css').text(text_color);
  }
 
  var size = jQuery('.label_text_size').val();

  if('' != size) {
    sizepx = size+'px';
    text_size = text_color+'\n font-size:'+size + 'px;';
    jQuery('#label_text_css').text(text_size);
  }
  /*------------------------------------------------------------------------------*/
  var m_text_color = '';
  var m_text_size  = '';
  var m_sizepx     = '';
  const m_element = document.querySelector('#choose_labeltext_color_medium')
  const m_style = getComputedStyle(m_element)
  var m_text_rgb_col = m_style.backgroundColor;
  var m_colorname = jQuery('#adminlabeltext_color_medium').val();

  if('' != m_colorname){
    m_text_color = 'color:#'+m_colorname + ';';
    jQuery('#medium_label_text_css').text(m_text_color);
  }
 
  var m_size = jQuery('.label_text_size_medium').val();

  if('' != m_size) {
    m_sizepx = size+'px';
    m_text_size = text_color+'\n font-size:'+m_size + 'px;';
    jQuery('#medium_label_text_css').text(m_text_size);
  }
  /*------------------------------------------------------------------------------*/
  var s_text_color = '';
  var s_text_size  = '';
  var s_sizepx     = '';
  const s_element = document.querySelector('#choose_labeltext_color_small')
  const s_style = getComputedStyle(s_element)
  var s_text_rgb_col = s_style.backgroundColor;
  var s_colorname = jQuery('#adminlabeltext_color_small').val();

  if('' != s_colorname){
    s_text_color = 'color:#'+s_colorname + ';';
    jQuery('#small_label_text_css').text(s_text_color);
  }
 
  var s_size = jQuery('.label_text_size_small').val();

  if('' != s_size) {
    s_sizepx = s_size+'px';
    s_text_size = s_text_color+'\n font-size:'+s_size + 'px;';
    jQuery('#small_label_text_css').text(s_text_size);
  }
  /*------------------------------------------------------------------------------*/

  if(document.getElementById('next_price').checked == true)
  {
    if(document.getElementById('label_shape').checked == true)
    {
      if(document.getElementById('label_large').checked == true)
      {
        jQuery('#shapenextlabeltext1').css({'font-size':'','color':''});
        jQuery('#shapenextlabeltext1').css({'font-size':sizepx,'color':text_rgb_col});
      }
      else if(document.getElementById('label_medium').checked == true)
      {
        jQuery('#shapenextlabeltext2').css({'font-size':'','color':''});
        jQuery('#shapenextlabeltext2').css({'font-size':m_sizepx,'color':m_text_rgb_col});
      }
      else if(document.getElementById('label_small').checked == true)
      {
        jQuery('#shapenextlabeltext3').css({'font-size':'','color':''});
        jQuery('#shapenextlabeltext3').css({'font-size':s_sizepx,'color':s_text_rgb_col});
      }
    }
   
    else if(document.getElementById('label_text').checked == true)
    {
      if(document.getElementById('label_large').checked == true)
      {
        jQuery('#nextlabeltext1').css({'font-size':'','color':''});
        jQuery('#nextlabeltext1').css({'font-size':sizepx,'color':text_rgb_col});
      }
      else if(document.getElementById('label_medium').checked == true)
      {
        jQuery('#nextlabeltext2').css({'font-size':'','color':''});
        jQuery('#nextlabeltext2').css({'font-size':m_sizepx,'color':m_text_rgb_col});
      }
      else if(document.getElementById('label_small').checked == true)
      {
        jQuery('#nextlabeltext3').css({'font-size':'','color':''});
        jQuery('#nextlabeltext3').css({'font-size':s_sizepx,'color':s_text_rgb_col});
      }
    }
    
    if(document.getElementById('label_picture').checked == true)
    {
      if(document.getElementById('label_large').checked == true)
      {
        jQuery('#nextpictext1').css({'font-size':'','color':''});
        jQuery('#nextpictext1').css({'font-size':sizepx,'color':text_rgb_col});
      }
      else if(document.getElementById('label_medium').checked == true)
      {
        jQuery('#nextpictext2').css({'font-size':'','color':''});
        jQuery('#nextpictext2').css({'font-size':m_sizepx,'color':m_text_rgb_col});
      }
      else if(document.getElementById('label_small').checked == true)
      {
        jQuery('#nextpictext3').css({'font-size':'','color':''});
        jQuery('#nextpictext3').css({'font-size':s_sizepx,'color':s_text_rgb_col});
      }      
    }
  }
  else
  {
    if(document.getElementById('label_shape').checked == true)
    {
      if(document.getElementById('label_large').checked == true)
      {
        jQuery('#shapelabeltext1').css({'font-size':'','color':''});
        jQuery('#shapelabeltext1').css({'font-size':sizepx,'color':text_rgb_col});
      }
      else if(document.getElementById('label_medium').checked == true)
      {
        jQuery('#shapelabeltext2').css({'font-size':'','color':''});
        jQuery('#shapelabeltext2').css({'font-size':m_sizepx,'color':m_text_rgb_col});
      }
      else if(document.getElementById('label_small').checked == true)
      {
        jQuery('#shapelabeltext3').css({'font-size':'','color':''});
        jQuery('#shapelabeltext3').css({'font-size':s_sizepx,'color':s_text_rgb_col});
      }
    }
    else if(document.getElementById('label_text').checked == true)
    {
      if(document.getElementById('label_large').checked == true)
      {
        jQuery('#labeltext1').css({'font-size':'','color':''});
        jQuery('#labeltext1').css({'font-size':sizepx,'color':text_rgb_col});
      }
      else if(document.getElementById('label_medium').checked == true)
      {
        jQuery('#labeltext2').css({'font-size':'','color':''});
        jQuery('#labeltext2').css({'font-size':m_sizepx,'color':m_text_rgb_col});
      }
      else if(document.getElementById('label_small').checked == true)
      {
        jQuery('#labeltext3').css({'font-size':'','color':''});
        jQuery('#labeltext3').css({'font-size':s_sizepx,'color':s_text_rgb_col});
      }
    }
    else if(document.getElementById('label_picture').checked == true)
    {
      if(document.getElementById('label_large').checked == true)
      {
        jQuery('#pictext1').css({'font-size':'','color':''});
        jQuery('#pictext1').css({'font-size':sizepx,'color':text_rgb_col});
      }
      else if(document.getElementById('label_medium').checked == true)
      {
        jQuery('#pictext2').css({'font-size':'','color':''});
        jQuery('#pictext2').css({'font-size':m_sizepx,'color':m_text_rgb_col});
      }
      else if(document.getElementById('label_small').checked == true)
      {
        jQuery('#pictext3').css({'font-size':'','color':''});
        jQuery('#pictext3').css({'font-size':s_sizepx,'color':s_text_rgb_col});
      }
    }
  }

}

function myFunction() {
  /*jQuery(".dropdown-content").toggle();
  jQuery("#myDropdowntext").css({'display':'none'});
  jQuery("#text_css_area").css({'display':'none'});
  jQuery("#con_css_area").css({'display':'none'});*/

  jQuery("#myDropdowntext").css({'display':'none'});
  jQuery("#text_css_area").css({'display':'none'});
  jQuery("#con_css_area").css({'display':'none'});

  jQuery("#myDropdowntext_medium").css({'display':'none'});
  jQuery("#text_css_area_medium").css({'display':'none'});
  jQuery("#con_css_area_medium").css({'display':'none'});

  jQuery("#myDropdowntext_small").css({'display':'none'});
  jQuery("#text_css_area_small").css({'display':'none'});
  jQuery("#con_css_area_small").css({'display':'none'});

  if(document.getElementById('label_large').checked == true) {
    jQuery(".dropdown-content").toggle();
  }
  if(document.getElementById('label_medium').checked == true) {
    jQuery(".dropdown-content_medium").toggle();
    
  }
  if(document.getElementById('label_small').checked == true){
    jQuery(".dropdown-content_small").toggle();
  }

  return false;
}

function textLabelFunction() {
  /*jQuery(".dropdown-text-content").toggle();
  jQuery("#myDropdown").css({'display':'none'});
  jQuery("#text_css_area").css({'display':'none'});
  jQuery("#con_css_area").css({'display':'none'});*/

  jQuery("#myDropdown").css({'display':'none'});
  jQuery("#myDropdown_medium").css({'display':'none'});
  jQuery("#myDropdown_small").css({'display':'none'});

  jQuery("#text_css_area").css({'display':'none'});
  jQuery("#con_css_area").css({'display':'none'});

  jQuery("#text_css_area_medium").css({'display':'none'});
  jQuery("#con_css_area_medium").css({'display':'none'});

  jQuery("#text_css_area_small").css({'display':'none'});
  jQuery("#con_css_area_small").css({'display':'none'});

  if(document.getElementById('label_large').checked == true) {
    jQuery(".dropdown-text-content").toggle();   
  }
  if(document.getElementById('label_medium').checked == true) {
    jQuery(".dropdown-text-content_medium").toggle();    
  }
  if(document.getElementById('label_small').checked == true) {
    jQuery(".dropdown-text-content_small").toggle();    
  }
  return false;
}

window.onclick = function(event) {

  if (!event.target.matches('.con_adv_dropbtn') && !event.target.matches('.dropbtn-text') && !event.target.matches('.label_adv_dropbtn') && !event.target.matches('.dropbtn') && !event.target.matches('.caret-icon') && !event.target.matches('.caret-iconn')) {
    /*jQuery("#text_css_area").css({'display':'none'});
    jQuery("#con_css_area").css({'display':'none'});
    jQuery("#myDropdowntext").css({'display':'none'});
    jQuery("#myDropdown").css({'display':'none'});*/

    jQuery("#text_css_area").css({'display':'none'});
    jQuery("#con_css_area").css({'display':'none'});
    jQuery("#myDropdowntext").css({'display':'none'});
    jQuery("#myDropdown").css({'display':'none'});

    jQuery("#text_css_area_medium").css({'display':'none'});
    jQuery("#con_css_area_medium").css({'display':'none'});
    jQuery("#myDropdowntext_medium").css({'display':'none'});
    jQuery("#myDropdown_medium").css({'display':'none'});

    jQuery("#text_css_area_small").css({'display':'none'});
    jQuery("#con_css_area_small").css({'display':'none'});
    jQuery("#myDropdowntext_small").css({'display':'none'});
    jQuery("#myDropdown_small").css({'display':'none'});
  }
}

function containerFun() {
  /*jQuery("#con_css_area").toggle();
  jQuery("#text_css_area").css({'display':'none'});*/

  jQuery("#text_css_area").css({'display':'none'});
  jQuery("#text_css_area_medium").css({'display':'none'});
  jQuery("#text_css_area_small").css({'display':'none'});

  if(document.getElementById('label_large').checked == true) {
    jQuery("#con_css_area").toggle();   
  }
  if(document.getElementById('label_medium').checked == true) {
    jQuery("#con_css_area_medium").toggle();    
  }
  if(document.getElementById('label_small').checked == true) {
    jQuery("#con_css_area_small").toggle();   
  }
  return false;
}

function labelFun() {
  /*jQuery("#text_css_area").toggle();
  jQuery("#con_css_area").css({'display':'none'});*/

  jQuery("#con_css_area").css({'display':'none'});
  jQuery("#con_css_area_medium").css({'display':'none'});
  jQuery("#con_css_area_small").css({'display':'none'});

  if(document.getElementById('label_large').checked == true) {
    jQuery("#text_css_area").toggle();   
  }
  if(document.getElementById('label_medium').checked == true) {
    jQuery("#text_css_area_medium").toggle();    
  }
  if(document.getElementById('label_small').checked == true) {
    jQuery("#text_css_area_small").toggle();  
  }
  return false;
}

function label_saved() 
{
  var check = true;
  var label_name = jQuery('.label_name').val();
  var option = document.getElementsByName('label_position');
  var type = document.getElementsByName('label_type');

  if(label_name.trim()==""){
    jQuery('span.label_name_error').text('Field is required').css({'color':'red'});
    check = false;
  }

  if(document.getElementById('label_picture').checked == true) {
    var image = jQuery('.display_image').attr('src');
    if (image.trim() == "") {
        jQuery('span.image_require_error').text('Image is required').css({'color':'red'});
        check = false;
    }
  }

  if (!(option[0].checked || option[1].checked || option[2].checked || option[3].checked || option[4].checked)) {
      jQuery('span.label_position_error').text('Option is required').css({'color':'red'});
      check = false;
  }

  if (!(type[0].checked || type[1].checked || type[2].checked)) {
      jQuery('span.label_type_error').text('Type is required').css({'color':'red'});
      check = false;
  }
 
  if(check == false){
    return false;
  }
}

