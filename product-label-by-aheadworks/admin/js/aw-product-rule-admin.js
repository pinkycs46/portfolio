var nonce_batc  = js_rule_var.aw_batc_nonce;
var url    = js_rule_var.site_url;
var PAGINATION_SIZE = 20;
var listchecked = [];
var valueToPush = [];
var allValue =[];
var i = '';
var arr = new Array(2);

jQuery(window).load(function() {
    /** Search Record in data table using search option **/
    jQuery("#search-submit").click(function(){
        search_key = jQuery("#post-search-input").val();
        ajax_append_pl_data_popup_grid('','' , '', '' , search_key, '', '', '', '', '', PAGINATION_SIZE, 1);
    });
    jQuery("#post-search-input").keypress(function(e){
         search_key = jQuery("#post-search-input").val();
         ajax_append_pl_data_popup_grid('','' , '','' , search_key, '', '', '', '', '', PAGINATION_SIZE, 1);
    });

    jQuery(".aw_save_list_btn").click(function() {
        if(listchecked.length==0){
            var arr = jQuery('input:checkbox:checked').map(function() {
                        if(jQuery.isNumeric(this.value)) {
                            return this.value;
                        }
                      }).get(); 
            if ( arr.length == 0 ) {
                alert("Please select any products.");
                return false;
            }
            listchecked = arr;
        }
        jQuery(".prod_modal").hide();
    });

});

jQuery(document).ready(function() {
    var test;
    jQuery(function() {
       jQuery( "#start_date" ).datepicker({
       dateFormat: 'yy-mm-dd',
       minDate: new Date()
       });         
   });

    jQuery(function() {
       jQuery( "#end_date" ).datepicker({
       dateFormat: 'yy-mm-dd',      
       });         
   });

    jQuery("#start_date").change(function() {
       test = jQuery(this).datepicker('getDate');
       testm = new Date(test.getTime());
       testm.setDate(testm.getDate());

       jQuery("#end_date").datepicker("option", "minDate",testm);
    });

    jQuery("#l").click(function () {
        var size = jQuery('#s option').size();
        if (size != jQuery("#s").prop('size')) {
            jQuery("#s").prop('size', size);
        } else {
            jQuery("#s").prop('size', 1);
        }
    });
});

function plcallpopup(tab_id,parentid,childid)
{
    listid = tab_id;
    var checkedlist = jQuery("#listchecked-"+parentid+'-'+childid+'-'+tab_id).val();
    ajax_append_pl_data_popup_grid(tab_id,parentid,childid, 'all' , '' , '', '', '', '', '', PAGINATION_SIZE, 1);    
    return false;
}

function ajax_append_pl_data_popup_grid(tab_id='',parentid,childid, status_type = 'all', search_key = '', product_cat='', product_type='', stock_status='', order_by='', order='', product_limit=10, paged=1)
{

    var checkedlist_str = jQuery("#listchecked-"+parentid+'-'+childid+'-'+tab_id).val();
 
	jQuery("#batc-loader").addClass('batc-loader');
    var site_url = url+'/wp-admin/admin-ajax.php';
        jQuery.ajax({
            url: site_url,
            type: 'POST',
            data: {action:"aw_pl_fetch_woo_product_list", tab_id:tab_id, parentid:parentid, childid:childid, product_limit:product_limit, paged:paged, status_type:status_type, search_key:search_key, product_cat:product_cat, product_type:product_type, stock_status:stock_status, checkedlist:checkedlist_str, order_by:order_by, order:order, aw_qa_nonce_ajax: nonce_batc},
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

/**** Close popup ***/
jQuery(document).on("click",".batc-popup-close",function(){
     jQuery(".batc_variation_modal").hide();
	 jQuery(".prod_modal").hide();
});

/* Pagination function */
function plpaginationclick(clicked)
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
                ajax_append_pl_data_popup_grid(tab_id, '' , '', 'all' , '' , '', product_type, stock_status, '', '', PAGINATION_SIZE, current+1);
                 break;
            case 'lastnext': 
                ajax_append_pl_data_popup_grid(tab_id,  '' , '','all' , '' , '', product_type, stock_status, '', '', PAGINATION_SIZE, total);
                 break;
            case 'onlyprev': 
                ajax_append_pl_data_popup_grid(tab_id,  '' , '','all' , '' , '', product_type, stock_status, '', '', PAGINATION_SIZE, current-1);
                 break;
            case 'firstprev': 
                ajax_append_pl_data_popup_grid(tab_id,  '' , '','all' , '' , '', product_type, stock_status, '', '', PAGINATION_SIZE, 1);
                 break;                                                          
        }
    }
}

/*** Function called when ascending and descending order clicked in data table ***/
function aw_pl_sorting_table_data(classname,order_by)
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

    ajax_append_pl_data_popup_grid('','' , '', 'all', '', '', '', '', order_by , order, PAGINATION_SIZE, 1);

}
/*** Function called when filter product by category, type, stock status ***/
function aw_pl_filterproducts()
{
    var product_cat  = jQuery("#product_cat option:selected").val();
    var product_type = jQuery("#dropdown_product_type option:selected").val();
    var stock_status = jQuery("#dropdown_stock_status option:selected").val();
    ajax_append_pl_data_popup_grid('','' , '', 'publish' , '', product_cat, product_type, stock_status, '', '', PAGINATION_SIZE, 1);
}

/*** Function called when click on all, published, draft, trash ***/
function pl_post_list_by_statuslist(elm)
{
    $status_type = jQuery(elm).attr('data-value');
    ajax_append_pl_data_popup_grid('' ,'' , '', $status_type , '', '', '', '', '', '', PAGINATION_SIZE, 1);
}



function aw_pl_listcheckbox(cb,parentid,childid) {
    var tab_id  = jQuery(".aw_save_list_btn").attr('data-tab_id');  
    /*if (cb.checked) {
         listchecked.push(cb.value);
          jQuery("#listchecked-"+parentid+'-'+childid+'-'+tab_id).val( listchecked );
    }
    jQuery("#sku_input_id-"+parentid+'-'+childid).val(listchecked);*/

    listchecked = jQuery(":checkbox[name='post"+parentid+'-'+childid+"[]']:checked").map(function() {
        return jQuery(this).val();
    })
    .get();
    jQuery("#sku_input_id-"+parentid+'-'+childid).val(listchecked);
}


function condition_remove(event,me,parentid,childid)
{
    event.preventDefault();   
    var button_id = jQuery(me).attr("id");
    jQuery('.ul_li_'+parentid+'_'+childid).remove();
}
function btn_remove(event,me,parentid,childid) {
    event.preventDefault();   
    var button_id = jQuery(me).attr("id");
    jQuery('#li-'+button_id).remove();
}

function add_rule(event,parentid,childid) {
    event.preventDefault();
    jQuery("#addbutton-"+parentid+" button.plus").css("display","none");
    var newchildid  = childid; 
    if(parentid == 0 && childid == 0)
    {
        newchildid = jQuery('ul.ul_li_0_child_0 > li:visible').length;
        if(0 == newchildid) {
           newchildid++;
        }
        else
        {
        newchildid  = childid;
        }
    }  

    var select_option_html = jQuery('#initial_of_select').html();
    select_option_html = select_option_html.replace('id="myDIV"','id="myDIV-'+parentid+'"');
    select_option_html = select_option_html.replace('data-value="0"','data-value="'+parentid+'"');
    select_option_html = select_option_html.replace('selectoptionval(this,0,0)','selectoptionval(this,'+parentid+','+newchildid+')');

    jQuery('.static-select-'+parentid).html(select_option_html);
    var x = document.getElementById("myDIV-"+parentid);

    if (x.style.display === "none") {
        x.style.display = "block";
    } else {
        x.style.display = "none";
    }
}

function selectoptionval(me,parentid,childid){
    var levelnode = 'child_';
    if('conditions_combination'===me.value)
    {
        levelnode = '';
    }
    var option_val = jQuery("#myDIV-"+parentid).val();
    jQuery("#level-"+parentid+"-"+childid+" button.plus").css("display","inline-block");   
     
    switch(option_val){

        case 'conditions_combination' : 
                
                jQuery('#myDIV-'+parentid).hide();
                parentid++;
                childid =0;
                conditions_combination_ol_text = '<ol class="orderedlist ul_li_'+parentid+'_'+childid+'" id="level-'+childid+'"><li><p>If <span class="rule-param"><a href="javascript:void(0)" id="label_all-'+parentid+'-'+childid+'" onclick="return label_all('+parentid+','+childid+')">ALL</a><span class="element"><select  name = "rule[conditions]['+parentid+']['+childid+'][aggregator]" id="select_label_all-'+parentid+'-'+childid+'" onchange="getval(this,'+parentid+','+childid+');" style="display: none;"><option selected="selected" value="ALL">ALL</option><option value="ANY">ANY</option></select></span></span>&nbsp;of these conditions are <span class="rule-param"><a href="javascript:void(0)" id ="label_true-'+parentid+'-'+childid+'" onclick="return label_true('+parentid+','+childid+')">TRUE</a><span class="element"><select name = "rule[conditions]['+parentid+']['+childid+'][value]" id="select_label_true-'+parentid+'-'+childid+'" onchange="getval1(this,'+parentid+','+childid+');" style="display: none;"><option selected="selected" value="TRUE">TRUE</option><option value="FALSE">FALSE</option></select></span>&nbsp;</span><button id="close-parent-'+parentid+'" onclick="return condition_remove(event,this,'+parentid+','+childid+')";  class="close-tb" style="display:inline-block;">&#10006;</button></p></li><li><ul class="ul_li_'+parentid+'_child_'+childid+'"></ul></li><button id="addbutton-'+ parentid +'" class="plus" onclick="return add_rule(event,'+parentid+','+childid+')">+</button><div class="static-select-'+parentid+'"></div></ol>';

                /*if(parentid == 1 && childid == 0)
                {
                    var position = 'first';
                }
                else {
                    var position = 'last'; 
                }*/
                jQuery('.ul_li_'+(parentid-1)+'_'+childid+ ' li:last').append(conditions_combination_ol_text);
        break;

        case 'sale' :
                
                jQuery('#myDIV-'+parentid).hide();

                
                if(0 == parentid && 1 == childid)
                {

                    var sale_html = jQuery('#li-close-sale-0-1').html();
                    jQuery('.ul_li_0_child_0').append('<li id="li-close-sale-'+parentid+'-'+childid+'">'+sale_html+'</li>');
                }
                else
                {   
                    var newchildid= jQuery('.ul_li_'+parentid+'_'+levelnode+childid+ ' > li:visible').length + 1;                
                    var sale_html = jQuery('#li-close-sale-0-0').html();
                    
                    sale_html = sale_html.replace('id="sale-0-0"','id="sale-'+parentid+'-'+newchildid+'"');
                    sale_html = sale_html.replace('id="sale_is-0-0"','id="sale_is-'+parentid+'-'+newchildid+'"');
                    sale_html = sale_html.replace('sale_is(0,0)','sale_is('+parentid+','+newchildid+')');
                    sale_html = sale_html.replace('name="rule[conditions][0][0][sale-operator]"','name="rule[conditions]['+parentid+']['+newchildid+'][sale-operator]"');
                    sale_html = sale_html.replace('id="select_sale_is-0-0"','id="select_sale_is-'+parentid+'-'+newchildid+'"');
                    sale_html = sale_html.replace('getval2(this,0,0)','getval2(this,'+parentid+','+newchildid+')');
                    sale_html = sale_html.replace('id="sale_yes-0-0"','id="sale_yes-'+parentid+'-'+newchildid+'"');
                    sale_html = sale_html.replace('sale_yes(0,0)','sale_yes('+parentid+','+newchildid+')');
                    sale_html = sale_html.replace('name="rule[conditions][0][0][sale-value]"','name="rule[conditions]['+parentid+']['+newchildid+'][sale-value]"');
                    sale_html = sale_html.replace('id="select_sale_yes-0-0"','id="select_sale_yes-'+parentid+'-'+newchildid+'"');
                    sale_html = sale_html.replace('getval3(this,0,0)','getval3(this,'+parentid+','+newchildid+')');
                    sale_html = sale_html.replace('id="close-sale-0-0"','id="close-sale-'+parentid+'-'+newchildid+'"');
                    sale_html = sale_html.replace('btn_remove(event,this,0,0)','btn_remove(event,this,'+parentid+','+newchildid+')');

                    jQuery('.ul_li_'+parentid+'_'+levelnode+childid).append('<li id="li-close-sale-'+parentid+'-'+newchildid+'">'+sale_html+'</li>');                    
                }
        break;

        case 'weight' :
                
                jQuery('#myDIV-'+parentid).hide();

                
                if(0 == parentid && 1 == childid)
                {
                    var weight_html = jQuery('#li-close-weight-0-1').html();
                    jQuery('.ul_li_0_child_0').append('<li id="li-close-weight-'+parentid+'-'+childid+'">'+weight_html+'</li>');
                }
                else
                {
                    var newchildid= jQuery('.ul_li_'+parentid+'_'+levelnode+childid+ ' > li:visible').length + 1;                
                    var weight_html = jQuery('#li-close-weight-0-0').html();
                    
                    weight_html = weight_html.replace('id="weight-0-0"','id="weight-'+parentid+'-'+newchildid+'"');
                    weight_html = weight_html.replace('id="weight_is-0-0"','id="weight_is-'+parentid+'-'+newchildid+'"');
                    weight_html = weight_html.replace('weight_is(0,0)','weight_is('+parentid+','+newchildid+')');
                    weight_html = weight_html.replace('name="rule[conditions][0][0][weight-operator]"','name="rule[conditions]['+parentid+']['+newchildid+'][weight-operator]"');
                    weight_html = weight_html.replace('id="select_weight_is-0-0"','id="select_weight_is-'+parentid+'-'+newchildid+'"');
                    weight_html = weight_html.replace('getval12(this,0,0)','getval12(this,'+parentid+','+newchildid+')');
                    weight_html = weight_html.replace('id="weight_yes-0-0"','id="weight_yes-'+parentid+'-'+newchildid+'"');
                    weight_html = weight_html.replace('weight_yes(0,0)','weight_yes('+parentid+','+newchildid+')');
                    weight_html = weight_html.replace('name="rule[conditions][0][0][weight-value]"','name="rule[conditions]['+parentid+']['+newchildid+'][weight-value]"');
                    weight_html = weight_html.replace('id="weight_input_id-0-0"','id="weight_input_id-'+parentid+'-'+newchildid+'"');
                    weight_html = weight_html.replace('id="apply_weight-0-0"','id="apply_weight-'+parentid+'-'+newchildid+'"');
                    weight_html = weight_html.replace('apply_weight(0,0)','apply_weight('+parentid+','+newchildid+')');
                    weight_html = weight_html.replace('id="close-weight-0-0"','id="close-weight-'+parentid+'-'+newchildid+'"');   
                    weight_html = weight_html.replace('btn_remove(event,this,0,0)','btn_remove(event,this,'+parentid+','+newchildid+')');
                    jQuery('.ul_li_'+parentid+'_'+levelnode+childid).append('<li  id="li-close-weight-'+parentid+'-'+newchildid+'">'+weight_html+'</li>');
                                 
                }
        break; 

        case 'dimensions':
                jQuery('#myDIV-'+parentid).hide();
                if(0 == parentid && childid == 1 )
                {
                    var dimensions_html = jQuery('#li-close-dimensions-0-1').html();
                    jQuery('.ul_li_0_child_0').append('<li id="li-close-dimensions-'+parentid+'-'+childid+'">'+dimensions_html+'</li>');
                }
                else
                {
                    var newchildid= jQuery('.ul_li_'+parentid+'_'+levelnode+childid+ ' > li:visible').length + 1;
                    var dimensions_html = jQuery('#li-close-dimensions-0-0').html();
                    
                    dimensions_html = dimensions_html.replace('id="dimensions_is-0-0"','id="dimensions_is-'+parentid+'-'+newchildid+'"');
                    dimensions_html = dimensions_html.replace('dimensions_is(0,0)','dimensions_is('+parentid+','+newchildid+')');
                    dimensions_html = dimensions_html.replace('name="rule[conditions][0][0][dimensions-operator]"','name="rule[conditions]['+parentid+']['+newchildid+'][dimensions-operator]"');
                    dimensions_html = dimensions_html.replace('id="select_dimensions_is-0-0"','id="select_dimensions_is-'+parentid+'-'+newchildid+'"');
                    dimensions_html = dimensions_html.replace('getval14(this,0,0)','getval14(this,'+parentid+','+newchildid+')');


                    dimensions_html = dimensions_html.replace('id="dimensions_length-0-0"','id="dimensions_length-'+parentid+'-'+newchildid+'"');
                    dimensions_html = dimensions_html.replace('name="rule[conditions][0][0][length-value]"','name="rule[conditions]['+parentid+']['+newchildid+'][length-value]"');
                    dimensions_html = dimensions_html.replace('dimensions_length(0,0)','dimensions_length('+parentid+','+newchildid+')');
                    dimensions_html = dimensions_html.replace('id="dimlength_input_id-0-0"','id="dimlength_input_id-'+parentid+'-'+newchildid+'"');


                    dimensions_html = dimensions_html.replace('id="dimensions_width-0-0"','id="dimensions_width-'+parentid+'-'+newchildid+'"');
                    dimensions_html = dimensions_html.replace('name="rule[conditions][0][0][width-value]"','name="rule[conditions]['+parentid+']['+newchildid+'][width-value]"');
                    dimensions_html = dimensions_html.replace('dimensions_width(0,0)','dimensions_width('+parentid+','+newchildid+')');
                    dimensions_html = dimensions_html.replace('id="dimwidth_input_id-0-0"','id="dimwidth_input_id-'+parentid+'-'+newchildid+'"');

                    dimensions_html = dimensions_html.replace('id="dimensions_height-0-0"','id="dimensions_height-'+parentid+'-'+newchildid+'"');
                    dimensions_html = dimensions_html.replace('name="rule[conditions][0][0][height-value]"','name="rule[conditions]['+parentid+']['+newchildid+'][height-value]"');
                    dimensions_html = dimensions_html.replace('dimensions_height(0,0)','dimensions_height('+parentid+','+newchildid+')');
                    dimensions_html = dimensions_html.replace('id="dimheight_input_id-0-0"','id="dimheight_input_id-'+parentid+'-'+newchildid+'"');

                    dimensions_html = dimensions_html.replace('id="apply_length-0-0"','id="apply_length-'+parentid+'-'+newchildid+'"');
                    dimensions_html = dimensions_html.replace('apply_length(0,0)','apply_length('+parentid+','+newchildid+')');

                    dimensions_html = dimensions_html.replace('id="apply_width-0-0"','id="apply_width-'+parentid+'-'+newchildid+'"');
                    dimensions_html = dimensions_html.replace('apply_width(0,0)','apply_width('+parentid+','+newchildid+')');

                    dimensions_html = dimensions_html.replace('id="apply_height-0-0"','id="apply_height-'+parentid+'-'+newchildid+'"');
                    dimensions_html = dimensions_html.replace('apply_height(0,0)','apply_height('+parentid+','+newchildid+')');

                    dimensions_html = dimensions_html.replace('id="close-dimensions-0-0"','id="close-dimensions-'+parentid+'-'+newchildid+'"'); 
                    dimensions_html = dimensions_html.replace('btn_remove(event,this,0,0)','btn_remove(event,this,'+parentid+','+newchildid+')');

                    jQuery('.ul_li_'+parentid+'_'+levelnode+childid).append('<li id="li-close-dimensions-'+parentid+'-'+newchildid+'">'+dimensions_html+'</li>');
                    
                }
        break;

        case 'special_price' :
                jQuery('#myDIV-'+parentid).hide();

                
                if(0 == parentid && childid == 1 )
                {

                    var special_price_html = jQuery('#li-close_price-0-1').html();
                    jQuery('.ul_li_0_child_0').append('<li id="li-close_price-'+parentid+'-'+childid+'">'+special_price_html+'</li>');

                }
                else
                {
                    var newchildid= jQuery('.ul_li_'+parentid+'_'+levelnode+childid+ ' > li:visible').length + 1;
                    var special_price_html = jQuery('#li-close_price-0-0').html();
                    
                    special_price_html = special_price_html.replace('id="special_price-0-0"','id="special_price-'+parentid+'-'+newchildid+'"');
                    special_price_html = special_price_html.replace('id="special_price_is-0-0"','id="special_price_is-'+parentid+'-'+newchildid+'"');
                    special_price_html = special_price_html.replace('special_price_is(0,0)','special_price_is('+parentid+','+newchildid+')');
                    special_price_html = special_price_html.replace('name="rule[conditions][0][0][special_price-operator]"','name="rule[conditions]['+parentid+']['+newchildid+'][special_price-operator]"');
                    special_price_html = special_price_html.replace('id="select_special_price_is-0-0"','id="select_special_price_is-'+parentid+'-'+newchildid+'"');
                    special_price_html = special_price_html.replace('getval16(this,0,0)','getval16(this,'+parentid+','+newchildid+')');
                    special_price_html = special_price_html.replace('id="special_price_yes-0-0"','id="special_price_yes-'+parentid+'-'+newchildid+'"');
                    special_price_html = special_price_html.replace('special_price_yes(0,0)','special_price_yes('+parentid+','+newchildid+')');
                    special_price_html = special_price_html.replace('name="rule[conditions][0][0][special_price-value]"','name="rule[conditions]['+parentid+']['+newchildid+'][special_price-value]"');
                    special_price_html = special_price_html.replace('id="price_input_id-0-0"','id="price_input_id-'+parentid+'-'+newchildid+'"');
                    special_price_html = special_price_html.replace('id="apply_price-0-0"','id="apply_price-'+parentid+'-'+newchildid+'"');
                    special_price_html = special_price_html.replace('apply_price(0,0)','apply_price('+parentid+','+newchildid+')');
                    special_price_html = special_price_html.replace('id="close_price-0-0"','id="close_price-'+parentid+'-'+newchildid+'"');
                    special_price_html = special_price_html.replace('btn_remove(event,this,0,0)','btn_remove(event,this,'+parentid+','+newchildid+')');

                    jQuery('.ul_li_'+parentid+'_'+levelnode+childid).append('<li  id="li-close_price-'+parentid+'-'+newchildid+'">'+special_price_html+'</li>');
                    
                }
        break;

        case 'stock_range' :
                jQuery('#myDIV-'+parentid).hide();

                
                if(0 == parentid && childid == 1 )
                {

                    var stock_range_html = jQuery('#li-close-range-0-1').html();
                    jQuery('.ul_li_0_child_0').append('<li id="li-close-range-'+parentid+'-'+childid+'">'+stock_range_html+'</li>');
                }
                else
                {   
                    var newchildid= jQuery('.ul_li_'+parentid+'_'+levelnode+childid+ ' > li:visible').length + 1;
                    var stock_range_html = jQuery('#li-close-range-0-0').html();
                    
                    stock_range_html = stock_range_html.replace('id="stock_range-0-0"','id="stock_range-'+parentid+'-'+newchildid+'"');
                    stock_range_html = stock_range_html.replace('id="stock_range_is-0-0"','id="stock_range_is-'+parentid+'-'+newchildid+'"');
                    stock_range_html = stock_range_html.replace('stock_range_is(0,0)','stock_range_is('+parentid+','+newchildid+')');
                    stock_range_html = stock_range_html.replace('name="rule[conditions][0][0][stock_range-operator]"','name="rule[conditions]['+parentid+']['+newchildid+'][stock_range-operator]"');
                    stock_range_html = stock_range_html.replace('id="select_stock_range_is-0-0"','id="select_stock_range_is-'+parentid+'-'+newchildid+'"');
                    stock_range_html = stock_range_html.replace('getval18(this,0,0)','getval18(this,'+parentid+','+newchildid+')');

                    stock_range_html = stock_range_html.replace('id="stock_range_yes-0-0"','id="stock_range_yes-'+parentid+'-'+newchildid+'"');
                    stock_range_html = stock_range_html.replace('stock_range_yes(0,0)','stock_range_yes('+parentid+','+newchildid+')');
                    stock_range_html = stock_range_html.replace('name="rule[conditions][0][0][stock_range-value]"','name="rule[conditions]['+parentid+']['+newchildid+'][stock_range-value]"');
                    stock_range_html = stock_range_html.replace('id="range_input_id-0-0"','id="range_input_id-'+parentid+'-'+newchildid+'"');
                    stock_range_html = stock_range_html.replace('id="apply_range-0-0"','id="apply_range-'+parentid+'-'+newchildid+'"');
                    stock_range_html = stock_range_html.replace('apply_range(0,0)','apply_range('+parentid+','+newchildid+')');
                    stock_range_html = stock_range_html.replace('id="close-range-0-0"','id="close-range-'+parentid+'-'+newchildid+'"');
                    stock_range_html = stock_range_html.replace('btn_remove(event,this,0,0)','btn_remove(event,this,'+parentid+','+newchildid+')');

                    jQuery('.ul_li_'+parentid+'_'+levelnode+childid).append('<li id="li-close-range-'+parentid+'-'+newchildid+'">'+stock_range_html+'</li>');
                    
                }
        break;

        case 'sku' :
                
                jQuery('#myDIV-'+parentid).hide();
                
                if(0 == parentid && childid == 1 )
                {

                    var sku_html = jQuery('#li-close-sku-0-1').html();
                    jQuery('.ul_li_0_child_0').append('<li id="li-close-sku-'+parentid+'-'+childid+'">'+sku_html+'</li>');
                }
                else
                {
                    var newchildid= jQuery('.ul_li_'+parentid+'_'+levelnode+childid+ ' > li:visible').length + 1;
                    var sku_html = jQuery('#li-close-sku-0-0').html();
                    
                    sku_html = sku_html.replace('id="sku-0-0"','id="sku-'+parentid+'-'+newchildid+'"');
                    sku_html = sku_html.replace('id="sku_is-0-0"','id="sku_is-'+parentid+'-'+newchildid+'"');
                    sku_html = sku_html.replace('sku_is(0,0)','sku_is('+parentid+','+newchildid+')');
                    sku_html = sku_html.replace('name="rule[conditions][0][0][sku-operator]"','name="rule[conditions]['+parentid+']['+newchildid+'][sku-operator]"');
                    sku_html = sku_html.replace('id="select_sku_is-0-0"','id="select_sku_is-'+parentid+'-'+newchildid+'"');
                    sku_html = sku_html.replace('getval10(this,0,0)','getval10(this,'+parentid+','+newchildid+')');
                    sku_html = sku_html.replace('id="sku_yes-0-0"','id="sku_yes-'+parentid+'-'+newchildid+'"');
                    sku_html = sku_html.replace('sku_yes(0,0)','sku_yes('+parentid+','+newchildid+')');
                    sku_html = sku_html.replace('name="rule[conditions][0][0][sku-value]"','name="rule[conditions]['+parentid+']['+newchildid+'][sku-value]"');
                    sku_html = sku_html.replace('id="sku_input_id-0-0"','id="sku_input_id-'+parentid+'-'+newchildid+'"');
                    sku_html = sku_html.replace('id="sku_div-0-0"','id="sku_div-'+parentid+'-'+newchildid+'"');
                    sku_html = sku_html.replace('plcallpopup(16573,0,0)','plcallpopup(16573,'+parentid+','+newchildid+')');
                    sku_html = sku_html.replace('id="listchecked-0-0-16573"','id="listchecked-'+parentid+'-'+newchildid+'-16573"');
                    sku_html = sku_html.replace('id="apply_sku-0-0"','id="apply_sku-'+parentid+'-'+newchildid+'"');
                    sku_html = sku_html.replace('apply_sku(0,0)','apply_sku('+parentid+','+newchildid+')');
                    sku_html = sku_html.replace('id="close-sku-0-0"','id="close-sku-'+parentid+'-'+newchildid+'"');
                    sku_html = sku_html.replace('btn_remove(event,this,0,0)','btn_remove(event,this,'+parentid+','+newchildid+')');

                    jQuery('.ul_li_'+parentid+'_'+levelnode+childid).append('<li id="li-close-sku-'+parentid+'-'+newchildid+'">'+sku_html+'</li>');
                    
                }
        break;

        case 'category' :
                jQuery('#myDIV-'+parentid).hide();
                if(0 == parentid && childid == 1 )
                {

                    var category_html = jQuery('#li-close-category-0-1').html();
                    jQuery('.ul_li_0_child_0').append('<li id="li-close-category-'+parentid+'-'+childid+'">'+category_html+'</li>');
                }
                else
                {   
                    var newchildid= jQuery('.ul_li_'+parentid+'_'+levelnode+childid+ ' > li:visible').length + 1;
                    var category_html = jQuery('#li-close-category-0-0').html();
                    
                    category_html = category_html.replace('id="category-0-0"','id="category-'+parentid+'-'+newchildid+'"');
                    category_html = category_html.replace('id="category_is-0-0"','id="category_is-'+parentid+'-'+newchildid+'"');
                    category_html = category_html.replace('category_is(0,0)','category_is('+parentid+','+newchildid+')');
                    category_html = category_html.replace('name="rule[conditions][0][0][category-operator]"','name="rule[conditions]['+parentid+']['+newchildid+'][category-operator]"');
                    category_html = category_html.replace('id="select_category_is-0-0"','id="select_category_is-'+parentid+'-'+newchildid+'"');
                    category_html = category_html.replace('getval6(this,0,0)','getval6(this,'+parentid+','+newchildid+')');
                    category_html = category_html.replace('id="category_yes-0-0"','id="category_yes-'+parentid+'-'+newchildid+'"');
                    category_html = category_html.replace('category_yes(0,0)','category_yes('+parentid+','+newchildid+')');
                    category_html = category_html.replace('name="rule[conditions][0][0][category-value]"','name="rule[conditions]['+parentid+']['+newchildid+'][category-value]"');
                    category_html = category_html.replace('id="cat_input_id-0-0"','id="cat_input_id-'+parentid+'-'+newchildid+'"');
                    category_html = category_html.replace('id="select_category-0-0"','id="select_category-'+parentid+'-'+newchildid+'"');
                    category_html = category_html.replace('showCheckboxes(0,0)','showCheckboxes('+parentid+','+newchildid+')');
                    category_html = category_html.replace('id="checkboxes-0-0"','id="checkboxes-'+parentid+'-'+newchildid+'"');
                    category_html = category_html.replace('checkboxes(0,0)','checkboxes('+parentid+','+newchildid+')');
                    //category_html = category_html.replace('name="category-1-1"','name="category-'+parentid+'-'+childid+'"');
                    category_html = category_html.replace('id="cat_div-0-0"','id="cat_div-'+parentid+'-'+newchildid+'"');
                    category_html = category_html.replace('category_div(0,0)','category_div('+parentid+','+newchildid+')');
                    category_html = category_html.replace('id="apply_category-0-0"','id="apply_category-'+parentid+'-'+newchildid+'"');
                    category_html = category_html.replace('apply_category(0,0)','apply_category('+parentid+','+newchildid+')');
                    category_html = category_html.replace('id="close-category-0-0"','id="close-category-'+parentid+'-'+newchildid+'"');
                    category_html = category_html.replace('btn_remove(event,this,0,0)','btn_remove(event,this,'+parentid+','+newchildid+')');

                    jQuery('.ul_li_'+parentid+'_'+levelnode+childid).append('<li id="li-close-category-'+parentid+'-'+newchildid+'">'+category_html+'</li>');
                    
                }
        break;
               
        default:

            jQuery('#myDIV-'+parentid).hide();

                
            if(0 == parentid && childid == 1 )
            {
                jQuery(".plus").css("display","block");
                jQuery(".ul").css("display","block");
                jQuery("."+option_val).css("display","block");
                jQuery(".attribute-0-1").css("display","block");
                jQuery(".attribute-0-1").append("<br>"+option_val +" <a href='javascript:void(0)' id='"+option_val+"_is-0-1' onclick='return attribute_operator("+parentid+","+childid+",\""+ option_val+ "\")'>is</a><select name = 'rule[conditions]["+parentid+"]["+childid+"]["+option_val +"-operator]' id='select_"+option_val +"_is-0-1' onchange='getvalIs(this,0,1,\""+ option_val+ "\");'  style='display: none;'><option value='is' selected='selected'>is</option><option value='is not'>is not</option></select>");
                jQuery(".attribute-0-1").append('<span class="rule-param"><a href="javascript:void(0)" id="'+option_val+'_yes-0-1"  onclick="return attribute_value(0,1,\''+ option_val+ '\')">....</a><select name = "rule[conditions]['+parentid+']['+childid+']['+option_val +'-value]" id="select_'+option_val+'-0-1" onchange="getvalDynamic(this,0,1,\''+ option_val+ '\');" style="display: none;" ></select>');
                jQuery(".attribute-0-1").append('<button id="attribute-0-1" style="display:inline-block;" onclick="return btn_remove(event,this,0,1);" class="close-tb">&#10006;</button>');
                
            }
            else
            {
                jQuery(".plus").css("display","block");
                jQuery(".ul").css("display","block");

                var newchildid= jQuery('.ul_li_'+parentid+'_'+levelnode+childid+ ' > li:visible').length + 1;
                var attribute_html = jQuery('#li-attribute-0-1').html();
                attribute_html = option_val +' <a href="javascript:void(0)" id="'+option_val+'_is-'+parentid+"-"+newchildid+'" onclick="return attribute_operator('+parentid+','+newchildid+',\''+ option_val+ '\')">is</a><select name = "rule[conditions]['+parentid+"]["+newchildid+']['+option_val +'-operator]" id="select_'+option_val +'_is-'+parentid+"-"+newchildid+'" onchange="getvalIs(this,'+parentid+','+newchildid+',\''+ option_val+ '\');"  style="display: none;"><option value="is" selected="selected">is</option><option value="is not">is not</option></select><span class="rule-param"><a href="javascript:void(0)" id="'+option_val+'_yes-'+parentid+'-'+newchildid+'"  onclick="return attribute_value('+parentid+','+newchildid+',\''+ option_val+ '\')">....</a><select name = "rule[conditions]['+parentid+']['+newchildid+']['+option_val +'-value]" id="select_'+option_val+'-'+parentid+'-'+newchildid+'" onchange="getvalDynamic(this,'+parentid+','+newchildid+',\''+ option_val+ '\');" style="display: none;" ></select><button id="attribute-'+parentid+'-'+newchildid+'" style="display:inline-block;" onclick="return btn_remove(event,this,'+parentid+','+newchildid+');" class="close-tb">&#10006;</button>';
                jQuery('.ul_li_'+parentid+'_'+levelnode+childid).append('<li id="li-attribute-'+parentid+'-'+newchildid+'">'+attribute_html+'</li>');
                
            }
                
    }

}

function attribute_operator(parentid,childid,v)
{
   	//var v = jQuery("#myDIV-"+parentid).val();
	var x = document.getElementById("select_"+v+"_is-"+parentid+'-'+childid);
	if (x.style.display === "none") {
		x.style.display = "inline-block";
		jQuery("select_"+v+"_is-"+parentid+'-'+childid).removeAttr("style");
		jQuery("#"+v+"_is-"+parentid+'-'+childid).css("display","none");
	} else {
		x.style.display = "none";
	}
    
   
    return false;
}
function attribute_value(parentid,childid,v) {

    var tmt = '<option>Select attribute</option>';
	//var v = jQuery("#myDIV-"+parentid).val();
	var x = document.getElementById("select_"+v+'-'+parentid+'-'+childid);
	if (x.style.display === "none") 
	{
		x.style.display = "inline-block";
		jQuery("#close-"+v+'-'+parentid+'-'+childid).css("margin-left","300px");
		jQuery("select_"+v+'-'+parentid+'-'+childid).removeAttr("style");
		jQuery("#"+v+"_yes-"+parentid+'-'+childid).css("display","none");
	} 
	else {
		x.style.display = "none";
	}
	ajaxUrl = postdata.ajax_url,
	nonceValue = postdata.ajax_nonce;

	var request = jQuery.post(ajaxUrl,{action: 'dynamic_attribute_ajax',security: nonceValue,color :v,},function( parentid ){});
	request.done( function ( response ) {
		response.data.data.forEach(function(color) {
	   		tmt +=  '<option value ="'+color+'">'+color+'</option>';
		});
		jQuery("#select_"+v+'-'+parentid+'-'+childid).html(tmt);
	});
    
    
	return false;
}

function getvalIs(is,parentid,childid,v)
{   
    //var v = jQuery("#myDIV-"+parentid).val();
    jQuery("#"+v+"_is-"+parentid+'-'+childid).text(is.value);
    jQuery("#"+v+"_is-"+parentid+'-'+childid).css("display","inline-block");
    jQuery("#select_"+v+"_is-"+parentid+'-'+childid).css("display","none");
}
function getvalDynamic(sel,parentid,childid,v)
{   
    //var v = jQuery("#myDIV-"+parentid).val();
    jQuery("#"+v+"_yes-"+parentid+'-'+childid).text(sel.value);
    jQuery("#"+v+"_yes-"+parentid+'-'+childid).css("display","inline-block");
    jQuery("#select_"+v+'-'+parentid+'-'+childid).css("display","none");
}

function label_all(parentid,childid)
{    
    var x = document.getElementById("select_label_all-"+parentid+'-'+childid);
    if (x.style.display === "none") {
        x.style.display = "inline-block";
        jQuery("#label_all-"+parentid+'-'+childid).css("display","none");
    } else {
        x.style.display = "none";
    }
    jQuery("#label_all-"+parentid+'-'+childid).css("display","none");
  
    return false;
}

function label_true(parentid,childid)
{
   
    var x = document.getElementById("select_label_true-"+parentid+'-'+childid);
    if (x.style.display === "none") {
        x.style.display = "inline-block";
        jQuery("#label_true-"+parentid+'-'+childid).css("display","none");
    } else {
        x.style.display = "none";
    }
    jQuery("#label_true-"+parentid+'-'+childid).css("display","none");

   
    return false;
}

function getval(sel,parentid,childid)
{
    var ve = "label_all-"+parentid+'-'+childid+"'";
    jQuery("#label_all-"+parentid+'-'+childid).text(sel.value);

    jQuery("#label_all-"+parentid+'-'+childid).css("display","inline-block");
    jQuery("#select_label_all-"+parentid+'-'+childid).css("display","none"); 

    jQuery("#rule_true-"+parentid+'-'+childid).val(sel.value);
}

function getval1(sel,parentid,childid)
{  
    jQuery("#label_true-"+parentid+'-'+childid).text(sel.value);
    jQuery("#label_true-"+parentid+'-'+childid).css("display","inline-block");
    jQuery("#select_label_true-"+parentid+'-'+childid).css("display","none");

    jQuery("#rule_true-"+parentid+'-'+childid).val(sel.value);
}
function sale_is(parentid,childid)
{
    var x = document.getElementById("select_sale_is-"+parentid+'-'+childid);
    if (x.style.display === "none") {
        x.style.display = "inline-block";
        jQuery("#sale_is-"+parentid+'-'+childid).css("display","none");
    } else {
        x.style.display = "none";
    }
 
    return false;       
}

function sale_yes(parentid,childid)
{
    
    var x = document.getElementById("select_sale_yes-"+parentid+'-'+childid);
    if (x.style.display === "none") {
        x.style.display = "inline-block";
        jQuery("#sale_yes-"+parentid+'-'+childid).css("display","none");
    } else {
        x.style.display = "none";
    }
    
    return false;   
}

function getval2(sel,parentid,childid)
{
    
    jQuery("#sale_is-"+parentid+'-'+childid).text(sel.value);
    jQuery("#sale_is-"+parentid+'-'+childid).css("display","inline-block");
    jQuery("#select_sale_is-"+parentid+'-'+childid).css("display","none");

}

function getval3(sel,parentid,childid)
{
    
    jQuery("#sale_yes-"+parentid+'-'+childid).text(sel.value);
    jQuery("#sale_yes-"+parentid+'-'+childid).css("display","inline-block");
    jQuery("#select_sale_yes-"+parentid+'-'+childid).css("display","none");
   
}

function weight_is(parentid,childid)
{
    
    var x = document.getElementById("select_weight_is-"+parentid+'-'+childid);
    if (x.style.display === "none") {
        x.style.display = "inline-block";
        jQuery("#weight_is-"+parentid+'-'+childid).css("display","none");
    } else {
        x.style.display = "none";
    }
  
    return false;
    
}

function weight_yes(parentid,childid)
{ 
    
    var x = document.getElementById("weight_input_id-"+parentid+'-'+childid);
    var apply = document.getElementById("apply_weight-"+parentid+'-'+childid);
    if (x.style.display === "none") {
        x.style.display = "inline-block";

        document.getElementById("weight_input_id-"+parentid+'-'+childid).oninput = () => {
            const input = document.getElementById('weight_input_id-'+parentid+'-'+childid);
            const output = document.getElementById('weight_yes-'+parentid+'-'+childid);

            output.value = input.value;
            jQuery("#weight_yes-"+parentid+'-'+childid).text(output.value);
        };

        jQuery("#weight_yes-"+parentid+'-'+childid).css("display","none");
    } else {
        x.style.display = "none";
    }

    if (apply.style.display === "none") {
        apply.style.display = "inline-block";
        jQuery("#weight_yes-"+parentid+'-'+childid).css("display","none");
    } 
  
    return false;
}

function getval12(sel,parentid,childid)
{
    
    jQuery("#weight_is-"+parentid+'-'+childid).text(sel.value);
    jQuery("#weight_is-"+parentid+'-'+childid).css("display","inline-block");
    jQuery("#select_weight_is-"+parentid+'-'+childid).css("display","none");
   
}



function apply_weight(parentid,childid)
{

    var a = document.getElementById("weight_input_id-"+parentid+'-'+childid);
    var z = document.getElementById("apply_weight-"+parentid+'-'+childid);

    if (a.style.display === "inline-block") {
        a.style.display = "none";
    }

    if (z.style.display === "inline-block") {
        z.style.display = "none";
    }

    jQuery("#weight_yes-"+parentid+'-'+childid).css("display","inline-block");
   

    return false;
}

function dimensions_is(parentid,childid)
{
    
    var x = document.getElementById("select_dimensions_is-"+parentid+'-'+childid);
    if (x.style.display === "none") {
        x.style.display = "inline-block";
        jQuery("#dimensions_is-"+parentid+'-'+childid).css("display","none");
    } else {
        x.style.display = "none";
    }
    
    return false;
}

function getval14(sel,parentid,childid)
{
    jQuery("#dimensions_is-"+parentid+'-'+childid).text(sel.value);
    jQuery("#dimensions_is-"+parentid+'-'+childid).css("display","inline-block");
    jQuery("#select_dimensions_is-"+parentid+'-'+childid).css("display","none");
    
  
}

function dimensions_length(parentid,childid)
{
    
    var x = document.getElementById("dimlength_input_id-"+parentid+'-'+childid);
    var apply = document.getElementById("apply_length-"+parentid+'-'+childid);
    if (x.style.display === "none") {
        x.style.display = "inline-block";
        jQuery("#dimensions_length-"+parentid+'-'+childid).css("display","none");
        document.getElementById("dimlength_input_id-"+parentid+'-'+childid).oninput = () => {
            const input = document.getElementById('dimlength_input_id-'+parentid+'-'+childid);
            const output = document.getElementById('dimensions_length-'+parentid+'-'+childid);

            output.value = input.value;
            jQuery("#dimensions_length-"+parentid+'-'+childid).text(output.value);
        };
    } else {
        x.style.display = "none";
    }   

    if (apply.style.display === "none") {
        apply.style.display = "inline-block";
        jQuery("#dimensions_length-"+parentid+'-'+childid).css("display","none");
    } 
   
    
    return false;   
}

function apply_length(parentid,childid)
{
    
    var a = document.getElementById("dimlength_input_id-"+parentid+'-'+childid);
    var z = document.getElementById("apply_length-"+parentid+'-'+childid);

    if (a.style.display === "inline-block") {
        a.style.display = "none";
    }

    if (z.style.display === "inline-block") {
        z.style.display = "none";
    }

    jQuery("#dimensions_length-"+parentid+'-'+childid).css("display","inline-block");

    return false;
}
    /*-------------------------------------------------------*/

function dimensions_width(parentid,childid)
{
    
    var x = document.getElementById("dimwidth_input_id-"+parentid+'-'+childid);
    var apply = document.getElementById("apply_width-"+parentid+'-'+childid);
    if (x.style.display === "none") {
        x.style.display = "inline-block";
        jQuery("#dimensions_width-"+parentid+'-'+childid).css("display","none");
    } else {
        x.style.display = "none";
    }   

    if (apply.style.display === "none") {
        apply.style.display = "inline-block";
        jQuery("#dimensions_width-"+parentid+'-'+childid).css("display","none");

        document.getElementById("dimwidth_input_id-"+parentid+'-'+childid).oninput = () => {
            const input = document.getElementById('dimwidth_input_id-'+parentid+'-'+childid);
            const output = document.getElementById('dimensions_width-'+parentid+'-'+childid);

            output.value = input.value;
            jQuery("#dimensions_width-"+parentid+'-'+childid).text(output.value);
        };
    }    
    return false;   
}

function apply_width(parentid,childid)
{
   
    var a = document.getElementById("dimwidth_input_id-"+parentid+'-'+childid);
    var z = document.getElementById("apply_width-"+parentid+'-'+childid);

    if (a.style.display === "inline-block") {
        a.style.display = "none";
    }

    if (z.style.display === "inline-block") {
        z.style.display = "none";
    }

    jQuery("#dimensions_width-"+parentid+'-'+childid).css("display","inline-block");

    return false;
}
    /*-------------------------------------------------------*/

function dimensions_height(parentid,childid)
{

    var x = document.getElementById("dimheight_input_id-"+parentid+'-'+childid);
    var apply = document.getElementById("apply_height-"+parentid+'-'+childid);
    if (x.style.display === "none") {
        x.style.display = "inline-block";
        jQuery("#dimensions_height-"+parentid+'-'+childid).css("display","none");
        document.getElementById("dimheight_input_id-"+parentid+'-'+childid).oninput = () => {
            const input = document.getElementById('dimheight_input_id-'+parentid+'-'+childid);
            const output = document.getElementById('dimensions_height-'+parentid+'-'+childid);

            output.value = input.value;
            jQuery("#dimensions_height-"+parentid+'-'+childid).text(output.value);
        };
    } else {
        x.style.display = "none";
    }   

    if (apply.style.display === "none") {
        apply.style.display = "inline-block";
        jQuery("#dimensions_height-"+parentid+'-'+childid).css("display","none");
    } 
    

    return false;   
}


function apply_height(parentid,childid)
{
    
    var a = document.getElementById("dimheight_input_id-"+parentid+'-'+childid);
    var z = document.getElementById("apply_height-"+parentid+'-'+childid);

    if (a.style.display === "inline-block") {
        a.style.display = "none";
    }

    if (z.style.display === "inline-block") {
        z.style.display = "none";
    }

    jQuery("#dimensions_height-"+parentid+'-'+childid).css("display","inline-block");


    return false;
}

function special_price_is(parentid,childid)
{
    
    var x = document.getElementById("select_special_price_is-"+parentid+'-'+childid);
    if (x.style.display === "none") {
        x.style.display = "inline-block";
        jQuery("#special_price_is-"+parentid+'-'+childid).css("display","none");
       
    } else {
        x.style.display = "none";
    }
        
    return false;
}


function special_price_yes(parentid,childid)
{
      
    var x = document.getElementById("price_input_id-"+parentid+'-'+childid);
    var apply = document.getElementById("apply_price-"+parentid+'-'+childid);
    if (x.style.display === "none") {
        x.style.display = "inline-block";
        jQuery("#special_price_yes-"+parentid+'-'+childid).css("display","none");
        document.getElementById("price_input_id-"+parentid+'-'+childid).oninput = () => {
            const input = document.getElementById('price_input_id-'+parentid+'-'+childid);
            const output = document.getElementById('special_price_yes-'+parentid+'-'+childid);

            output.value = input.value;
            jQuery("#special_price_yes-"+parentid+'-'+childid).text(output.value);
        };
    } else {
        x.style.display = "none";
    }   

    if (apply.style.display === "none") {
        apply.style.display = "inline-block";
        jQuery("#special_price_yes-"+parentid+'-'+childid).css("display","none");
    } 
        
    return false;   
}


function apply_price(parentid,childid)
{                                    
   var a = document.getElementById("price_input_id-"+parentid+'-'+childid);
    var z = document.getElementById("apply_price-"+parentid+'-'+childid);

    if (a.style.display === "inline-block") {
        a.style.display = "none";
    }

    if (z.style.display === "inline-block") {
        z.style.display = "none";
    }

    jQuery("#special_price_yes-"+parentid+'-'+childid).css("display","inline-block");


    return false;
}


function stock_range_is(parentid,childid)
{
    
    var x = document.getElementById("select_stock_range_is-"+parentid+'-'+childid);
    if (x.style.display === "none") {
        x.style.display = "inline-block";
        jQuery("#stock_range_is-"+parentid+'-'+childid).css("display","none");
    } else {
        x.style.display = "none";
    }
        
    return false;
}


function stock_range_yes(parentid,childid)
{
 
    var x = document.getElementById("range_input_id-"+parentid+'-'+childid);
    var apply = document.getElementById("apply_range-"+parentid+'-'+childid);
    if (x.style.display === "none") {
        x.style.display = "inline-block";
        jQuery("#stock_range_yes-"+parentid+'-'+childid).css("display","none");

        document.getElementById("range_input_id-"+parentid+'-'+childid).oninput = () => {
            const input = document.getElementById('range_input_id-'+parentid+'-'+childid);
            const output = document.getElementById('stock_range_yes-'+parentid+'-'+childid);
            output.value = input.value;
            jQuery("#stock_range_yes-"+parentid+'-'+childid).text(output.value);
        };
    } else {
        x.style.display = "none";
    }

    if (apply.style.display === "none") {
        apply.style.display = "inline-block";
        jQuery("#stock_range_yes-"+parentid+'-'+childid).css("display","none");
    }      

    return false;
}


function apply_range(parentid,childid)
{
    
    var a = document.getElementById("range_input_id-"+parentid+'-'+childid);
    var z = document.getElementById("apply_range-"+parentid+'-'+childid);

    if (a.style.display === "inline-block") {
        a.style.display = "none";
    }

    if (z.style.display === "inline-block") {
        z.style.display = "none";
    }

    jQuery("#stock_range_yes-"+parentid+'-'+childid).css("display","inline-block");
    

    return false;
}

function sku_is(parentid,childid)
{   
    var x = document.getElementById("select_sku_is-"+parentid+'-'+childid);
    if (x.style.display === "none") {
        x.style.display = "inline-block";
        jQuery("#sku_is-"+parentid+'-'+childid).css("display","none");
    } else {
        x.style.display = "none";
    }
        
    return false;
}

function sku_yes(parentid,childid)
{  
    var x = document.getElementById("sku_input_id-"+parentid+'-'+childid);
    if (x.style.display === "none") {
        x.style.display = "inline-block";
        document.getElementById("sku_input_id-"+parentid+'-'+childid).oninput = () => {
            const input = document.getElementById('sku_input_id-'+parentid+'-'+childid);
            const output = document.getElementById('sku_yes-'+parentid+'-'+childid);
            output.value = input.value;
            jQuery("#sku_yes-"+parentid+'-'+childid).text(output.value);
        };

        jQuery("#sku_yes-"+parentid+'-'+childid).css("display","none");
    } else {
        x.style.display = "none";
    }
    var x = document.getElementById("sku_div-"+parentid+'-'+childid);
    if (x.style.display === "none") {
        x.style.display = "inline-block";
    } else {
        x.style.display = "none";
    }

    var z = document.getElementById("apply_sku-"+parentid+'-'+childid);
    if (z.style.display === "none") {
        z.style.display = "inline-block";
    } else {
        z.style.display = "none";
    }
 
    return false;
        
}

function apply_sku(parentid,childid)
{
    var a = document.getElementById("sku_input_id-"+parentid+'-'+childid);
    var x = document.getElementById("add_new_prod_modal");
    var y = document.getElementById("close-sku-"+parentid+'-'+childid);
    var z = document.getElementById("apply_sku-"+parentid+'-'+childid);
    var sku_div = document.getElementById("sku_div-"+parentid+'-'+childid);

    var checked = new Array();

    if (a.style.display === "inline-block") {
        a.style.display = "none";
    }

    if (x.style.display === "inline-block") {
        x.style.display = "none";
    }

    if (z.style.display === "inline-block") {
        z.style.display = "none";
    }

    if (sku_div.style.display === "inline-block") {
        sku_div.style.display = "none";
    }

    jQuery("input[name='post"+parentid+'-'+childid+"[]']:checked").each(function() {
        checked.push(jQuery(this).val());
    });
    if(checked != '') {
      jQuery("#sku_yes-"+parentid+'-'+childid).text(checked);  
    }
    
    jQuery("#sku_yes-"+parentid+'-'+childid).css("display","inline-block");

    return false;
}

function category_is(parentid,childid)
{    
    var x = document.getElementById("select_category_is-"+parentid+'-'+childid);
    if (x.style.display === "none") {
        x.style.display = "inline-block";
        jQuery("#category_is-"+parentid+'-'+childid).css("display","none");
    } else {
        x.style.display = "none";
    }

    return false;
}

function category_yes(parentid,childid)
{
    var x = document.getElementById("cat_input_id-"+parentid+'-'+childid);
    if (x.style.display === "none") {
        x.style.display = "inline-block";

        document.getElementById("cat_input_id-"+parentid+'-'+childid).oninput = () => {
            const input = document.getElementById('cat_input_id-'+parentid+'-'+childid);
            const output = document.getElementById('category_yes-'+parentid+'-'+childid);

            output.value = input.value;
            jQuery("#category_yes-"+parentid+'-'+childid).text(output.value);
        };

        jQuery("#category_yes-"+parentid+'-'+childid).css("display","none");
    } else {
        x.style.display = "none";
    }
    var x = document.getElementById("cat_div-"+parentid+'-'+childid);
    if (x.style.display === "none") {
        x.style.display = "inline-block";
    } else {
        x.style.display = "none";
    }

    var z = document.getElementById("apply_category-"+parentid+'-'+childid);
    if (z.style.display === "none") {
        z.style.display = "inline-block";
    } else {
        z.style.display = "none";
    }
    
 
    return false;
}

var test = new Array();
function checkboxes(parentid,childid)
{   
    if (jQuery(this).is(":checked")) 
    {
        var checked_val = jQuery(this).val();
        test.push(jQuery(this).val());
        
    }
    jQuery("#cat_input_id-"+parentid+'-'+childid).val(test);

}


function category_div(parentid,childid)
{   
    var x = document.getElementById("select_category-"+parentid+'-'+childid);
    if (x.style.display === "none") {
        x.style.display = "block";
        var test = new Array();
        jQuery("#checkboxes-"+parentid+'-'+childid+" :checkbox").change(function(e) {    
            var assignedTo = jQuery(':checkbox[name=category]:checked').map(function() {
                return jQuery(this).val();
            })
            .get();
            
            jQuery("#cat_input_id-"+parentid+'-'+childid).val(assignedTo);
        });
    } else {
        x.style.display = "none";
    }

    return false;
}

function apply_category(parentid,childid)
{
   
    var a = document.getElementById("cat_input_id-"+parentid+'-'+childid);
    var x = document.getElementById("select_category-"+parentid+'-'+childid);
    var z = document.getElementById("apply_category-"+parentid+'-'+childid);
    var cat_div = document.getElementById("cat_div-"+parentid+'-'+childid);

    var checked = new Array();

    if (a.style.display === "inline-block") {
        a.style.display = "none";
    }

    if (x.style.display === "block") {
        x.style.display = "none";
    }
    if (z.style.display === "inline-block") {
        z.style.display = "none";
    }

    if (cat_div.style.display === "inline-block") {
        cat_div.style.display = "none";
    }
    jQuery("input[name='category']:checked").each(function() {
        checked.push(jQuery(this).val());
    });
    if(checked != ''){
     jQuery("#category_yes-"+parentid+'-'+childid).text(checked);   
    }
    
    jQuery("#category_yes-"+parentid+'-'+childid).css("display","inline-block");

    return false;
}

var expanded = false;

function showCheckboxes(parentid,childid) {
  var checkboxes = document.getElementById("checkboxes-"+parentid+'-'+childid);
  if (!expanded) {
    checkboxes.style.display = "inline-block";
    expanded = true;
  } else {
    checkboxes.style.display = "none";
    expanded = false;
  }
}


function getval6(sel,parentid,childid)
{
    
    jQuery("#category_is-"+parentid+'-'+childid).text(sel.value);
    jQuery("#category_is-"+parentid+'-'+childid).css("display","inline-block");
    jQuery("#select_category_is-"+parentid+'-'+childid).css("display","none");

}

function getval10(sel,parentid,childid)
{
    jQuery("#sku_is-"+parentid+'-'+childid).text(sel.value);
    jQuery("#sku_is-"+parentid+'-'+childid).css("display","inline-block");
    jQuery("#select_sku_is-"+parentid+'-'+childid).css("display","none");
    
}

function getval16(sel,parentid,childid)
{
    
    jQuery("#special_price_is-"+parentid+'-'+childid).text(sel.value);
    jQuery("#special_price_is-"+parentid+'-'+childid).css("display","inline-block");
    jQuery("#select_special_price_is-"+parentid+'-'+childid).css("display","none");
   
}

function getval18(sel,parentid,childid)
{
    
    jQuery("#stock_range_is-"+parentid+'-'+childid).text(sel.value);
    jQuery("#stock_range_is-"+parentid+'-'+childid).css("display","inline-block");
    jQuery("#select_stock_range_is-"+parentid+'-'+childid).css("display","none");
    

}

function checkItInput(evt,allowed = '')
{
	evt = (evt) ? evt : window.event;

	var charCode = (evt.which) ? evt.which : evt.keyCode;

	if(charCode === 46 && allowed == '0' || charCode === 44)
	{
		return true;	
	}

	if(charCode > 31 && (charCode < 48 || charCode > 57))
	{
		status = "This field accepts numbers and comma(,)only.";
		return false;
	}
	status = "";
	return true;
}


function checkIt(evt,allowed = '')
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

function checkNum(evt,allowed = '')
{
	 evt = (evt) ? evt : window.event;
	 
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if (charCode === 45 || charCode === 44 || (charCode > 64 && charCode < 91) || (charCode > 96 && charCode < 123) || charCode == 8)
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

function genral_setting_save() 
{
	var check = true;
	var priority = jQuery('.priority').val();
	var next_priority = jQuery('.next_priority').val();
	var label_distance = jQuery('.aw_pl_setting_label_distance').val();
	
	if(label_distance.trim()==""){
		jQuery('span.aw_pl_setting_label_distance_error').text('Field is required').css({'color':'red'});
		check= false;
	}
	else
	{
		jQuery('span.aw_pl_setting_label_distance_error').text('Field  is required').css({'display':'none'});
	}

	if(priority.trim()=="")
	{
		jQuery('span.priority_error').text('Field is required').css({'color':'red'});
		check= false;
	}
	else
	{
		jQuery('span.priority_error').text('Field  is required').css({'display':'none'});
	}

	if(next_priority.trim()==""){
		jQuery('span.next_priority_error').text('Field is required').css({'color':'red'});
		check= false;
	}
	else
	{
		jQuery('span.next_priority_error').text('Field  is required').css({'display':'none'});
	}
	if(check == false)
	{
		return false;
	}
}


function rule_saved() 
{
  	var check = true;
  	var rule_name = jQuery('.rule_name').val();  
  	var priority = jQuery('.priority').val();
    var start_date = jQuery('.start_date').val();
    var end_date = jQuery('.end_date').val();
    var labelname = jQuery("#select_label_name option:selected").val();

  	var select_label = document.getElementsByName('select_label_name');

  	if(rule_name.trim()==""){
    	jQuery('span.rule_name_error').text('Field is required').css({'color':'red'});
    	check = false;
  	}
    else
    {
        jQuery('span.rule_name_error').text('Field  is required').css({'display':'none'});
    }

  	if(priority.trim()==""){
    	jQuery('span.priority_error').text('Field is required').css({'color':'red'});
    	check = false;
  	}
    else
    {
        jQuery('span.priority_error').text('Field  is required').css({'display':'none'});
    }

    if(start_date.trim()==""){
        jQuery('span.start_date_error').text('Field is required').css({'color':'red'});
        check = false;
    }
    else
    {
        jQuery('span.start_date_error').text('Field  is required').css({'display':'none'});
    }

    if(end_date.trim()==""){
        jQuery('span.end_date_error').text('Field is required').css({'color':'red'});
        check = false;
    }
    else
    {
        jQuery('span.end_date_error').text('Field  is required').css({'display':'none'});
    }

    if ( labelname == "Select label" )
    {
        jQuery('span.select_label_name_error').text('Field is required').css({'color':'red'});
        check = false;
    }
    else
    {
        jQuery('span.select_label_name_error').text('Field  is required').css({'display':'none'});
    }
 
	if(check == false)
	{
		return false;
	}
}


