var url    = js_batc_var.site_url;
var nonce_batc  = js_batc_var.aw_batc_nonce;
var PAGINATION_SIZE = 5;
var listchecked = [];

jQuery.urlParam = function(name){
		var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
		if(results!=null)
			return results[1] || 0;
		else
			return 0;
	}
jQuery(document).ready(function() {
  
    if(jQuery("h1.wp-heading-inline").text().trim() == 'Product Lists') {
        url = url+'/wp-admin/edit.php?post_type=aw_bulk_product_list&page=aw-batc-product-list-admin';
        jQuery("h1.wp-heading-inline").after('<a href="'+url+'" class="page-title-action">Add New</a>');
    }

	jQuery('table#batc tbody#the-list').sortable({
        'items': 'tr',
        'axis': 'y',
        'helper': fixHelper,
        'update': function (e, ui) {
            jQuery(".intemate_to_save").text('Press save button to changed order');
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
});

function aw_batc_checkIt(evt,allowed = '')
{
	evt = (evt) ? evt : window.event;

	var charCode = (evt.which) ? evt.which : evt.keyCode;
	var charCode = (evt.which) ? evt.which : evt.keyCode;

	if(charCode === 46 && allowed == true)
	{
		return true;	
	}
	if(charCode > 31 && (charCode < 49 || charCode > 57))
	{
		status = "This field accepts numbers only.";
		return false;
	}
	status = "";
	return true;
}

/* onkey press get row total */

jQuery(document).on('change',".quantity",function(e){
    
    var currentRow  = jQuery(this).closest("tr");
    var quantity    = parseInt(jQuery(this).val());
    var row_id      = jQuery(this).attr('id').split('tr').pop();
    row_id          = 'tr'+row_id;  
    var price       = parseFloat(jQuery(".price-"+row_id).val()); 

    row_total = quantity*price;
    qw_batc_calculate_row_total(row_total,currentRow);
});

jQuery(window).load(function(){
	var tabName = jQuery.urlParam('screen'); 
    var page    = 1;
    /**
        Show default tab  
        Show default tab content
    **/
	jQuery("#aw-batc-list-tabcontent-0").show();
	jQuery("#aw-batc-list-tab-0").addClass("active");

    /**** Close popup ***/
    /*jQuery(".batc-popup-close").click(function(){
         jQuery(".prod_modal").hide(); 
         jQuery(".batc_variation_modal").hide(); 
     });*/
	
	
	
    /** Validate Grid data after click on save button **/
    jQuery(".aw_validatesave_btn").click(function(event){
        var $flag = 0;
        var error_msg = [];
        error_msg[0] = '';
        error_msg[1] = 'Quantity is a required field';
        error_msg[2] = 'Numeric value is required';
        error_msg[3] = 'Quantity must be greater than 0';
        error_msg[4] = 'Quantity must be without decimal';
        error_msg[5] = 'Quantity must be numeric';
		error_msg[6] = 'Title is required field';
		
		jQuery('.required').each(function(index){
			var str     = this.value;
			if (str.length == 0) {
				event.preventDefault();
                jQuery(this).css({'border':'1px solid red'});
                jQuery('span.errormsg-title').text(error_msg[6]).css({'color':'red'});
                $flag = 1;
			}
			else {
                jQuery(this).css({'border':''});
				jQuery('span.errormsg-title').text('').css('');;
            }
		});
        jQuery('.quantity').each(function(index){
            var pattern =/^\d+$/;
            var str     = this.value;
            value       = parseInt(this.value);
            
            if (value.length == 0) {
                event.preventDefault();
                jQuery(this).css({'border':'1px solid red'});
                jQuery('span.errormsg-'+index).text(error_msg[1]).css({'color':'red'});
                $flag = 1;
            } 
            else if (value <=0) {
                event.preventDefault();
                jQuery(this).css({'border':'1px solid red'});
                jQuery('span.errormsg-'+index).text(error_msg[3]).css({'color':'red'});
                $flag = 1;
            } else if(!pattern.test(str)) 
            {
                event.preventDefault();
                jQuery(this).css({'border':'1px solid red'});
                jQuery('span.errormsg-'+index).text(error_msg[2]).css({'color':'red'});
                $flag = 4;
            } else if(!jQuery.isNumeric(this.value)) {
                event.preventDefault();
                jQuery(this).css({'border':'1px solid red'});
                jQuery('span.errormsg-'+index).text(error_msg[2]).css({'color':'red'});
                $flag = 1;
            } else {
                jQuery(this).css({'border':''});
                jQuery('span.errormsg-'+index).text(error_msg[0]).css('');
            }
        });
        if (1 == $flag ) {
               // return false;
        }
    });

    /** Search Record in data table using search option **/
    jQuery("#search-submit").click(function(){
        search_key = jQuery("#post-search-input").val();
        ajax_append_data_popup_grid('', '' , search_key, '', '', '', '', '', PAGINATION_SIZE, 1);
    });
    jQuery("#post-search-input").keypress(function(e){
         search_key = jQuery("#post-search-input").val();
         ajax_append_data_popup_grid('','' , search_key, '', '', '', '', '', PAGINATION_SIZE, 1);
    });
    /** ########################### **/

    /* Muliselect checkbox in data table*/
    jQuery(".aw-allselect_chk").click(function() {
        jQuery('#batc-list tr td input:checkbox').not(this).prop('checked', this.checked);
        jQuery(".aw-allselect_chk").not(this).prop('checked', this.checked);
    });

    jQuery(".aw_save_list_btn").click(function(){
        if(listchecked.length==0){

            var arr = jQuery('input:checkbox:checked').map(function() {
                        if(jQuery.isNumeric(this.value)) {
                            return this.value;
                        }
                      }).get(); 
            if ( arr.length == 0 ) {
                alert("please checked any product before to save.");
                return false;
            }
            listchecked = arr;
        }
        json_arr    = Object.assign({}, listchecked);
        var tab_id  = jQuery(this).attr('data-tab_id');
        jQuery("#listchecked-"+tab_id).val( listchecked );
        ajax_append_data_to_main_grid(json_arr,tab_id);
    });
  
/*    jQuery( ".prod_modal-content" ).scroll(function() {
        if(jQuery(this).scrollTop() + jQuery(this).innerHeight() >= jQuery(this)[0].scrollHeight-100) {
            page++;
            alert(page);
            ajax_append_data_popup_grid(tabId, '' , '' , '', '', '', '', '', 10, page);
        }        
    });*/
    var tab_id  = jQuery(".tablinks.active").attr('data-value');  

    jQuery("#current-page-selector").keypress(function(){
         var keycode = (event.keyCode ? event.keyCode : event.which);
         if(keycode == '13'){
            var current = parseInt(jQuery("#current-page-selector").val());
            var total   = parseInt(jQuery(".tablenav-paging-text .total-pages").text());
            if(current <= total){
                ajax_append_data_popup_grid(tab_id, 'all' , '' , '', '', '', '', '', PAGINATION_SIZE, current);    
            }
            
         }
    });
    overallsummation(tab_id); 
    jQuery('select option[value=draft]').text('Unpublished');
    jQuery('select option[value=pending]').remove();
    /*jQuery(".inline-edit-date").hide();*/
    jQuery(".inline-edit-col-left .inline-edit-col .inline-edit-group").hide();
    /*jQuery('.post-state').each(function(index,value){
        jQuery(".post-state").text('Unpublished');
    });*/
});

/**** Close popup ***/
jQuery(document).on("click",".batc-popup-close",function(){
     jQuery(".batc_variation_modal").hide();
	 jQuery(".prod_modal").hide();
});

/* Save variation pop up form value */
jQuery(document).on("click", ".save_variation_setting", function() {
   
    var allowed_variation   = [];
    var child_id        = [];
    var row_id = jQuery(this).attr('data-value');
    var product_id = jQuery(this).attr('data-productid');
    var select_id  = ''; 
    jQuery('.customer_can_edit').each(function(index) {
        label        = this.value.toLowerCase(); 
        //label        = label.charAt(0).toUpperCase()+ label.slice(1);
        option = jQuery('.attribute_dropdown-'+index).find(":selected").text();
        key = label.replace('pa_','');
        key = key.charAt(0).toUpperCase()+key.slice(1);
        allowed_variation[key] = option;
        if (this.checked) {
            allowed_variation['Customer_can_edit_'+label] = label;
        }
    });
    var variation       = Object.assign({}, allowed_variation); //JSON.stringify();
    aw_variation_model_save(product_id, variation, row_id);

});

function batc_popup_close() {
	jQuery(".prod_modal").hide();
	jQuery(".batc_variation_modal").hide();
}

function aw_selcted_variation_dropdown(sel, product_id, selected_val='') {

    var id              = sel.id
    var value           = sel.value;
    var attribute_name  = id.split('-');
    var key             = attribute_name[1];
    aw_replaceSelection('selected-'+key);
    var value           = jQuery('#selected-'+key+' option:selected').val();

    var selected_val = [];
    var object       = {};
    jQuery.each(jQuery("select.popup_attribute option:selected"), function(){   
            id = jQuery(this).parent().attr('id');        
            attribute_name  = id.split('-'); 
            selected_val[attribute_name[1]] = jQuery(this).val();
    }); 
    object = Object.assign({}, selected_val);
    var site_url = url+'/wp-admin/admin-ajax.php';
    jQuery.ajax({
        url: site_url,
        type: 'POST',
        data: {action: "aw_ajax_filterVariations",product_id:product_id, key:key, value:value, selected_val:object, aw_qa_nonce_ajax: nonce_batc},
        success:function(data) {
            if(data != 0){
             var data  = JSON.parse(data);
                if(data.appendto.length>0 && data.options.length>0) {
                    jQuery("#selected-"+data.appendto).empty();
                    jQuery("#selected-"+data.appendto).append(data.options);
                }   
             }
        }
    });
}

function aw_replaceSelection(id) {
  selectedVal = jQuery('#'+id).val();
  
  jQuery('#'+id+' > option').each(function(i, item) {
   if(jQuery(item).attr("selected") === "selected"){
        jQuery(item).removeAttr("selected");
   }
  })
  
  jQuery('#'+id+' > option').each(function(i, item) {
    if(item.value === selectedVal) {
      jQuery(item).attr("selected", "true");
      prevVal = jQuery(item).val();
    }
  })
}

/** Call on Edit link for variation changes **/

function edit_popup_open(variation_id, row_id, product_id, data_value) {
    var variation       = {};
	var variation_id    = variation_id;
    var row_id			= row_id;
	var product_id      = product_id;

    var tabid  = row_id.split('-');
    var tab_id = tabid[tabid.length-1]; 
    var i = 0;
    jQuery('.existvariation_'+row_id).each(function(index,val) {
        variation[i++] = jQuery(this).val().toLowerCase();    
    });
     
    var site_url = url+'/wp-admin/admin-ajax.php';
    jQuery.ajax({
        url: site_url,
        type: 'POST',
        data: {action: "aw_append_variation_to_popup", row_id:row_id, product_id:product_id , variation_id:variation_id, existing_variation:variation, aw_qa_nonce_ajax: nonce_batc},
        success:function(data) {
            jQuery('.batc_variation_modal .wrap').remove();            
            jQuery('.batc_modal-content').append(data);
            jQuery('.batc_variation_modal').show();
        }
    });
}	
 

/** Save variation setting of product **/
function aw_variation_model_save(product_id, variation, row_id) {

    var arr_data    = row_id.split('-');
    var tab_id      = arr_data[arr_data.length-1];
    var site_url    = url+'/wp-admin/admin-ajax.php';
    var quantity    = jQuery('#quantity-'+row_id).val();    
    jQuery.ajax({
        url: site_url,
        type: 'POST',
        data: {action:"aw_save_variation_setting_form_data", row_id:row_id, product_id:product_id , variation:variation, quantity:quantity, aw_qa_nonce_ajax: nonce_batc},
        success:function(data) {
            var data  = JSON.parse(data);
            if (!jQuery.isEmptyObject(data)) {
                if (data.image_tag.length>0) {
                    jQuery('.aw-prod-img-'+row_id).html('');
                    jQuery('.aw-prod-img-'+row_id).html(data.image_tag);  
                }
                if (data.variation.length>0 || data.variation == '') {
                    if (data.variation == '') {
                        jQuery('.aw_variation_modal_'+product_id).show();
                    }
                    jQuery('.variation-'+row_id+' span').text('');
                    jQuery('.variation-'+row_id+' span').text(data.variation); 
                }
                if (data.variation_id.length>0) {
                    jQuery('.aw_variation_modal_'+product_id).attr('data-variation_id',data.variation_id);
                }   
                if (!jQuery.isEmptyObject(data.variation_val)) {
                    var array   = [];
                    var myJSON  = JSON.stringify(data.variation_val); 
                    var array   = JSON.parse(myJSON);
                    
                    jQuery(".variation_txt_"+row_id).remove();
                    jQuery.each( array, function( index, value ) {
                      jQuery('<input>').attr({
                                        type : 'hidden',
                                        class: 'variation_txt_'+row_id,
                                        name : 'variations['+tab_id+']['+product_id+']['+index+']',
                                        value: value
                                    }).appendTo('td.variation-'+row_id);
                    });
                }
                if (data.price.length>0) {
                    jQuery(".price-"+row_id).val(data.price);
                }
                if(data.totalamount.length>0){
                    jQuery("#totalamount-"+row_id).html(data.totalamount);
                    jQuery("#totalamount-"+row_id).attr('data-rowtotal',quantity*data.price);
                    
                }
            }
			overallsummation(tab_id);
            jQuery('.batc_variation_modal').hide();
        }
    });
}
 
/* Pagination function */
function paginationclick(clicked)
{
    if(!jQuery(".pagination-links a."+clicked).hasClass('disabled')) 
    {   
        var product_type = '';
        var stock_status = '';
        var tab_id   = jQuery(".aw_save_list_btn").attr('data-tab_id');//jQuery(".tablinks.active").attr('data-value');
        var current  = parseInt(jQuery("#current-page-selector").val());
        var total    = parseInt(jQuery(".tablenav-paging-text .total-pages").text());
        product_type = jQuery('#dropdown_product_type').find(":selected").val();
        stock_status = jQuery('#dropdown_stock_status').find(":selected").val();
        switch(clicked)
        {
            case 'onlynext': 
                ajax_append_data_popup_grid(tab_id, 'all' , '' , '', product_type, stock_status, '', '', PAGINATION_SIZE, current+1);
                 break;
            case 'lastnext': 
                ajax_append_data_popup_grid(tab_id, 'all' , '' , '', product_type, stock_status, '', '', PAGINATION_SIZE, total);
                 break;
            case 'onlyprev': 
                ajax_append_data_popup_grid(tab_id, 'all' , '' , '', product_type, stock_status, '', '', PAGINATION_SIZE, current-1);
                 break;
            case 'firstprev': 
                ajax_append_data_popup_grid(tab_id, 'all' , '' , '', product_type, stock_status, '', '', PAGINATION_SIZE, 1);
                 break;                                                          
        }
    }
}

/* Total Summation of row total assign it to below table */
function overallsummation(tabId)
{
    var sum = 0;
    var current_sym = '';
    jQuery('.totalamount').each(function(index) {
        sum += Number(jQuery(this).attr('data-rowtotal'));
    });
    //current_sym = jQuery("span.symbol").text();
    var site_url = url+'/wp-admin/admin-ajax.php';
    jQuery.ajax({
        url: site_url,
        type: 'POST',
        data: {action: "aw_get_price_quantity_calculate",row_total:sum, aw_qa_nonce_ajax: nonce_batc, screen: "admin"},
        success:function(data) {
             if(data.length>0) {
                jQuery('.overallsummation').text(data);
             }
        }
    });
}

/* Function to conform before delete */
function batc_confirmdelete()
{
  var tab_id = jQuery(".tablinks.active").attr('data-value');
  var selected_option = jQuery('#bulk-action-selector-top option:selected').val();
  if(selected_option.length<3){
    alert('Please select action before apply')
    return false;
  }
  if(jQuery('input[type="checkbox"]:checked').length == 0){
    alert('Please check at least one record form list');
    return false;
  }

  var x = confirm("Are you sure you want to delete this item ?");
    if (x)
    {
        jQuery('input[type="checkbox"]:checked').each(function(index) {
           jQuery("."+this.value).remove();
           overallsummation(tab_id);
        });
    }
    else
    {
        return false;
    }
}

/*** Function called when click on Add New Product button ***/
function rdcallpopup(tab_id)
{
    var checkedlist = jQuery("#listchecked-"+tab_id).val();
    var value =jQuery("#addbeforesave-"+tab_id).val();
    if(value=="1"){
        ajax_append_data_popup_grid(tab_id, 'all' , '' , '', '', '', '', '', PAGINATION_SIZE, 1);    
    } else {
        alert("Please save before add new product");
    }
}

/** Function to call on click of delete button in data grid  **/
function rdclick_ondelete(elm) 
{
   var tab = '';
   var tab_id = '';
   var $class = elm.getAttribute("data-class");
   jQuery("."+$class).remove();
   tab = $class.split('-');
   tab_id = tab[tab.length-1];
   jQuery("#addbeforesave-"+tab_id).val(0);
   overallsummation(tab_id);
}
function rdbulk_ondelete() 
{   
    var arr = [];
}
 
/*** Function called when click on all, published, draft, trash ***/
function post_list_by_statuslist(elm)
{
    $status_type = jQuery(elm).attr('data-value');
    ajax_append_data_popup_grid('' , $status_type , '', '', '', '', '', '', PAGINATION_SIZE, 1);
}

/*** Function called when ascending and descending order clicked in data table ***/
function aw_sorting_table_data(classname,order_by)
{
    
    if(jQuery('.'+classname).hasClass('asc')) 
    {
        order = 'asc';
        jQuery('.'+classname).removeClass('asc');
        jQuery('.'+classname).addClass('desc');   
    } else {
        order = 'desc';
        jQuery('.'+classname).removeClass('desc');
        jQuery('.'+classname).addClass('asc');           
    }

    ajax_append_data_popup_grid('', 'all', '', '', '', '', order_by , order, PAGINATION_SIZE, 1);

}

/*** Function called when filter product by category, type, stock status ***/
function aw_filterproducts()
{
    var product_cat  = jQuery("#product_cat option:selected").val();
    var product_type = jQuery("#dropdown_product_type option:selected").val();
    var stock_status = jQuery("#dropdown_stock_status option:selected").val();
    ajax_append_data_popup_grid('', 'publish' , '', product_cat, product_type, stock_status, '', '', PAGINATION_SIZE, 1);
}

function ajax_append_data_popup_grid(tab_id='', status_type = 'all', search_key = '', product_cat='', product_type='', stock_status='', order_by='', order='', product_limit=10, paged=1)
{
    //var tab_id  = jQuery(".tablinks.active").attr('data-value'); 
    var checkedlist_str = jQuery("#listchecked-"+tab_id).val();
	jQuery("#batc-loader").addClass('batc-loader');
    var site_url = url+'/wp-admin/admin-ajax.php';
        jQuery.ajax({
            url: site_url,
            type: 'POST',
            data: {action:"aw_fetch_woo_product_list", tab_id:tab_id , product_limit:product_limit, paged:paged, status_type:status_type, search_key:search_key, product_cat:product_cat, product_type:product_type, stock_status:stock_status, checkedlist:checkedlist_str, order_by:order_by, order:order, aw_qa_nonce_ajax: nonce_batc},
            success:function(data) {
                var data  = JSON.parse(data);
                if (data.tbody.length>0) {
					jQuery("#batc-loader").removeClass('batc-loader');
                    jQuery("#batc-list").empty();
                    jQuery("#batc-list").append(data.tbody);
                } 
                if (data.subsubsub.length>0 && status_type != "") {
                    jQuery("#post_counts").empty();
                    jQuery("#post_counts").append(data.subsubsub);
                    jQuery("#post_counts li a").removeClass('current');
                    jQuery("#post_counts li."+status_type+" a").addClass("current");
                }
                if (data.items.length>0) {
                    jQuery(".post_display_num").empty();
                    jQuery(".post_display_num").text(data.items);
                }
                if (data.tab_id.length>0) {
                    jQuery(".aw_save_list_btn").attr('data-tab_id', data.tab_id);
                }
                if (data.totalrecord > product_limit) {
                    jQuery(".pagination-links").show();
                    var totalpagination = Math.ceil(data.totalrecord / product_limit);
                    jQuery("#current-page-selector").val(paged);
                    jQuery(".paging-input .total-pages").text(totalpagination);
                    if(totalpagination>1 && paged==1){
                       jQuery(".tablenav-pages .onlyprev").addClass('disabled');
                       jQuery(".tablenav-pages .firstprev").addClass('disabled'); 
                       jQuery(".tablenav-pages .onlynext").removeClass('disabled');
                       jQuery(".tablenav-pages .lastnext").removeClass('disabled');
                    }
                    if(totalpagination>1 && paged>1 && totalpagination!=paged){
                       jQuery(".tablenav-pages .onlyprev").removeClass('disabled');
                       jQuery(".tablenav-pages .firstprev").removeClass('disabled');
                       jQuery(".tablenav-pages .onlynext").removeClass('disabled');
                       jQuery(".tablenav-pages .lastnext").removeClass('disabled');
                    } 
                    if(totalpagination>1 && paged>1 && totalpagination==paged){                   
                       jQuery(".tablenav-pages .onlyprev").removeClass('disabled');
                       jQuery(".tablenav-pages .firstprev").removeClass('disabled');
                       jQuery(".tablenav-pages .onlynext").addClass('disabled');
                       jQuery(".tablenav-pages .lastnext").addClass('disabled');                        
                    }
                } else
                {
                    if(0 == data.totalrecord){
                        jQuery(".tablenav-pages").hide();
                    } else {
                        jQuery(".tablenav-pages .pagination-links").hide();
                    }
                    
                }
                jQuery(".prod_modal").show();
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });
}

function ajax_append_data_to_main_grid(product_ids, tab_id='')
{   
    var exist_product_id = [];
    jQuery(".main-list-"+tab_id).find("tr a.batc-remove-product").each(function( index ) {
       exist_product_id[index] = jQuery(this).attr('data-value');
    }).get();

    var product_ids_arr = [];
    jQuery.each( product_ids, function( key, value ) {
        product_ids_arr.push( value );    
    });

    product_ids_arr = aw_merge_array(product_ids_arr , exist_product_id);
    product_id_obj  = Object.assign({}, product_ids_arr);

    var site_url = url+'/wp-admin/admin-ajax.php';
        jQuery.ajax({
            url: site_url,
            type: 'POST',
            data: {action:"aw_get_product_from_woo_popup_grid", product_id:product_id_obj, tab_id:tab_id, aw_qa_nonce_ajax: nonce_batc},
            success:function(data){
                if(data.length>0){
                    totalsum = 0;
                    jQuery(".main-list-"+tab_id).empty();
                    jQuery(".main-list-"+tab_id).append(data);
                    totalsum = jQuery(".summation-"+tab_id).val();
                    //jQuery(".overallsummation").text(totalsum);
                    overallsummation();
                }
                jQuery(".prod_modal").hide();
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });
}
/* merge two array */
function aw_merge_array(array1, array2) {
    var result_array = [];
    var arr = array1.concat(array2);
    var len = arr.length;
    var assoc = {};

    while(len--) {
        var item = arr[len];

        if(!assoc[item]) 
        { 
            result_array.unshift(item);
            assoc[item] = true;
        }
    }
    return result_array;
}

/* 
    ** Replace All function replace occurance of specific tab id 
*/
String.prototype.replaceAll = function(searchStr, replaceStr) {
        var str = this;

        // no match exists in string?
        if(str.indexOf(searchStr) === -1) {
            // return string
            return str;
        }

        // replace and remove first match, and do another recursirve search/replace
        return (str.replace(searchStr, replaceStr)).replaceAll(searchStr, replaceStr);
    }

function cloneTab(evt, tabId, increment='')
{
    var today   = new Date();
    var date    = today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate();
    var time    = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
    var dateTime = date+' '+time;
    var unixtime = Date.parse(dateTime);
	var max = 0;
    var prev = 0
	jQuery("button.tablinks").each(function() {

	  var value = parseInt(jQuery(this).data('id'));
	  max = (value > max) ? value : max;
		jQuery("#aw-batc-list-tabcontent-"+value).hide(); // Hide all tab
		jQuery("#aw-batc-list-tab-"+value).removeClass("active") // Hide all tab content
	});
    prev = max;
	max++;

    var prev_tab_name   = jQuery("#aw-batc-list-tab-"+prev).text();
	var button_html 	= '<button class="tablinks" data-id="'+max+'" id="aw-batc-list-tab-'+max+'"  onclick="openTab(event,'+max+')">'+prev_tab_name+'</button>';
	jQuery("#aw-batc-list-tab-plus").before(button_html);
 
	var tabcontent_html = '<div id="aw-batc-list-tabcontent-'+max+'">';
	tabcontent_html    += jQuery("#aw-batc-list-tabcontent-"+(max-1)).html(); 
	tabcontent_html    += '</div>';

    /* Change tab id in following cloned conetent after + button press */
    tabcontent_html= tabcontent_html.replaceAll(tabId , unixtime);
    jQuery("#aw-batc-list-tab-plus").attr("onClick", "cloneTab('event',"+unixtime+",1)");
     
    jQuery("#aw-batc-list-tabcontent-"+prev).after(tabcontent_html);
    jQuery("#aw-batc-list-tab-"+prev).removeClass("active"); // deactive just previous tab
    jQuery("#aw-batc-list-tabcontent-"+prev).hide(); // hide just previous tab content
	jQuery("#aw-batc-list-tabcontent-"+max).show(); // Show only cloned tab
	jQuery("#aw-batc-list-tab-"+max).addClass("active") // Show only cloned tab content
}
function openTab(evt, tabId, screen='')
{
	jQuery("#aw-batc-list-tab-"+tabId).addClass("active");
	evt.preventDefault();
	var i, tabcontent, tablinks;
	jQuery("button.tablinks").each(function (index, val){
		if(tabId == index)
		{
			jQuery("#aw-batc-list-tabcontent-"+index).show();
		}
		else
		{
			jQuery("#aw-batc-list-tabcontent-"+index).hide();
			jQuery("#aw-batc-list-tab-"+index).removeClass("active")
		}

	});
}

function qw_batc_calculate_row_total(row_total,currentRow){
    var site_url = url+'/wp-admin/admin-ajax.php';
    jQuery.ajax({
        url: site_url,
        type: 'POST',
        data: {action: "aw_get_price_quantity_calculate",row_total:row_total, aw_qa_nonce_ajax: nonce_batc, screen: "admin"},
        success:function(data) {
             if(data.length>0) {
                currentRow.find("td span.totalamount").html(data);
                currentRow.find("td span.totalamount").attr('data-rowtotal',row_total);
                currentRow.find("td input.totalamount").val(row_total);
                overallsummation();
             }
        }
    });
}


/* 04-05-2020 */
/*jQuery('table.posts #the-list, table.pages #the-list').sortable({
        'items': 'tr',
        'axis': 'y',
        'helper': fixHelper,
        'update': function (e, ui) {
            jQuery.post(ajaxurl, {
                action: 'update-menu-order',
                order: jQuery('#the-list').sortable('serialize'),
            });
        }
    });
jQuery('table.tags #the-list').sortable({
        'items': 'tr',
        'axis': 'y',
        'helper': fixHelper,
        'update': function (e, ui) {
            jQuery.post(ajaxurl, {
                action: 'update-menu-order-tags',
                order: jQuery('#the-list').sortable('serialize'),
            });
        }
    });*/

/*(function ($) {
    
    
   

     
    jQuery(window).load(function () {

        // make the array for the sizes
        var td_array = new Array();
        var i = 0;
        jQuery('#the-list tr:first-child').find('td').each(function () {
            td_array[i] = $(this).outerWidth();
            jQuery(this).css('padding','8px 0px');
            i += 1;
        });

        jQuery('#the-list').find('tr').each(function () {
            var j = 0;
            jQuery(this).find('td').each(function () {
                jQuery(this).css('padding','8px 0px');
                j += 1;
            });
        });

        var y = 0;

        // check if there are no items in the table
        if(jQuery('#the-list > tr.no-items').length == 0){
            jQuery('#the-list').parent().find('thead').find('th').each(function () {
                jQuery(this).css('padding','8px 0px');
                y += 1;
            });

            jQuery('#the-list').parent().find('tfoot').find('th').each(function () {
                jQuery(this).css('padding','8px 0px');
                y += 1;
            });
        }

    });

     

})(jQuery)*/

/* 04-05-2020 */

jQuery(document).on("click",".aw_batc_listcheckbox", function(){
     var tab_id  = jQuery(".aw_save_list_btn").attr('data-tab_id');  
        if(this.checked) {
             listchecked.push(jQuery(this).val());
              jQuery("#listchecked-"+tab_id).val( listchecked );
        }
    });