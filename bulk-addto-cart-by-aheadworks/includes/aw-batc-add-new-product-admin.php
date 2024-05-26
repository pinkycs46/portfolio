<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class AwbatcAddNewProduct {
 
	public function __construct() {
		 
	}

	/* To fetch Woocommerce product list use plugin and send it to popup useing ajax call */

	public static function aw_fetch_woo_product_list() {
		global $wpdb; 
		if ( !function_exists( 'wc_get_product_types' ) ) { 
			require_once '/includes/wc-product-functions.php'; 
		} 
		check_ajax_referer( 'aw_batc_admin_nonce', 'aw_qa_nonce_ajax' );
		 
		$data		= array();
		$tbody		= '';
		$subsubsub 	= '';	
		$all 		= 0;
		$published 	= 0;
		$trash 		= 0;
		$draft 		= 0;
		$tab_id		= 0;
		$index 		= 0;
		$chckedlist = array();

		if (isset($_POST['tab_id'])) {
			$tab_id = sanitize_text_field($_POST['tab_id']);
		}

		if (isset($_POST['product_limit'])) {
			$limit 	= sanitize_text_field($_POST['product_limit']);	
		}

		if (isset($_POST['paged'])) {
			$paged 	= sanitize_text_field($_POST['paged']);	
		}
		if (isset($_POST['checkedlist']) && !empty($_POST['checkedlist'])) {

			$chckedlist	= sanitize_text_field($_POST['checkedlist']);
			$chckedlist	= explode(',', $chckedlist);
		}

		$post = array(
					'post_type'		=> 'product',
					'posts_per_page'=> -1,
				);

		$post['tax_query'] 	= array(array(
									'taxonomy' 	=> 'product_type',
									'field'    	=> 'slug',
									'terms'    	=> array('external'),
									'operator' 	=> 'NOT IN', 
									));

		$post['post_status']= array('publish', 'draft');
		$all = count(get_posts($post));

		$post['post_status']= 'trash';
		$trash 				= count(get_posts($post));

		$post['post_status']= 'draft';
		$draft 				= count(get_posts($post));

		//$all = $all + $draft;
		$post['post_status']= 'publish';
		$published 			= count(get_posts($post));		

		if (isset($_POST['status_type'])) {
			switch ($_POST['status_type']) {
				case 'all': 
					unset($post['post_status']); 
					break;
				case 'published': 
					$post['post_status']= 'publish';
					break;
				case 'trash': 
					$post['post_status']= 'trash';
					break;
				case 'draft': 
					$post['post_status']= 'draft';
					break;				 					
			}
		}
		/* Search record in table data */
		if (isset($_POST['search_key']) && !empty($_POST['search_key'])) {
			$post['s']	=	sanitize_text_field($_POST['search_key']);	
		}

		/* get product of specific category in table data */
		if (isset($_POST['product_cat']) && !empty($_POST['product_cat'])) {
			$post['product_cat']	=	sanitize_text_field($_POST['product_cat']);	
		}

			/* get product of specific stock status in table data */
		$post['meta_query'] = array( array(
									 'key' => '_price',
									 'compare' => 'EXISTS'
									));

		/* get product of specific type(variable, single, downloadable) in table data */
		if (isset($_POST['product_type']) && !empty($_POST['product_type'])) {

			if ('virtual' == $_POST['product_type'] || 'downloadable' == $_POST['product_type'] ) {
				$index++;
				$post['meta_query'][$index] =	array(
													'key'		=> '_' . sanitize_text_field($_POST['product_type']),
													'value'		=> 'yes',
													'compare' 	=> '='
												);
			} else {
				$post['post_status']= 	array('publish','draft');
				$post['tax_query'] 			=	array(	array(
													'taxonomy' 	=> 'product_type',
													'field'    	=> 'slug',
													'terms'    	=> array(sanitize_text_field($_POST['product_type'])),
													'operator' 	=> 'IN', 
												));
			}						
		}	
	
		if (isset($_POST['stock_status']) && !empty($_POST['stock_status'])) {

			/*$post['meta_query'] =	 array(
											array(
												'key'		=> '_stock_status',
												'value'		=> $_POST['stock_status'],
												'compare' 	=> '='
											),
											
										);*/
			$index++;
			$post['meta_query'][$index] =	 
											array(
												'key'		=> '_stock_status',
												'value'		=> sanitize_text_field($_POST['stock_status']),
												'compare' 	=> '='
											);

													
											
																				
		}		
	

		/* Get product by ascending and descending order */
		if (isset($_POST['order_by']) && !empty($_POST['order_by']) && isset($_POST['order']) && !empty($_POST['order'])) {

			switch ($_POST['order_by']) {
				case 'title':
				case 'date': 
								$post['orderby'] 	= sanitize_text_field($_POST['order_by']);
					break;		
				case '_price':				
								$post['orderby'] 	= 'meta_value_num';
								$post['meta_key']   = sanitize_text_field($_POST['order_by']);	
					break;
				case '_sku':				
								$post['orderby'] 	= 'meta_value';
								$post['meta_key']   = sanitize_text_field($_POST['order_by']);	
					break;
			}
			$post['order'] = sanitize_text_field($_POST['order']);
		}

		$data['totalrecord'] 	=  count(get_posts($post));

		$post['posts_per_page'] = $limit;
		$post['paged'] 			= $paged;
		$product_post 			= get_posts($post);
		if (!empty($product_post)) {
			$data['items']		= $data['totalrecord'] . ' items';
			foreach ($product_post as $key=> $prod) {
				$tagoutput 	= array();
				$tags		= '';
				$_product 	= wc_get_product($prod->ID);
				$status 	= '';
				$url 		= '';
				$checked 	= '';
				$featured 	= '<span alt="f154" class="dashicons dashicons-star-empty">';
				
				$terms 		= wp_get_post_terms( $prod->ID, 'product_tag' );
				if ( count($terms) > 0 ) {
					foreach ($terms as $term) {
						$term_name = $term->name; // Product tag Name
						$term_slug = $term->slug; // Product tag slug
						$term_link = get_term_link( $term, 'product_tag' ); // Product tag link
						$tagoutput[]= '<a href="' . $term_link . '">' . $term_name . '</a>';
					}
					$tags 	= implode( ', ', $tagoutput );
				}

				$url = aw_get_product_image( $prod->ID );
				//$price = aw_get_individual_product_price($prod->ID);
				
				if ('publish' === $_product->get_status()) {
					$status = 'Published';
				}
				if ($_product->is_featured()) {
					$featured = '<span alt="f155" class="dashicons dashicons-star-filled">';
				}

				if ( $_product->is_on_backorder() ) {
					$stock_html = '<mark class="onbackorder">' . __( 'On backorder', 'woocommerce' ) . '</mark>';
				} elseif ( $_product->is_in_stock() ) {
					$stock_html = '<mark class="instock">' . __( 'In stock', 'woocommerce' ) . '</mark>';
				} else {
					$stock_html = '<mark class="outofstock">' . __( 'Out of stock', 'woocommerce' ) . '</mark>';
				}
				$responsiveclass = '';
				if (0 == $key) {
					$responsiveclass = 'is-expanded';
				}
				if (in_array($prod->ID, $chckedlist)) {
					$checked = 'checked';
				}
				$tbody	.= '<tr class="' . $responsiveclass . '"">
								<td class="aw-checkbox"><input class="aw_batc_listcheckbox" ' . $checked . ' id="cb-select-' . $prod->ID . '" type="checkbox" name="post[]" value="' . $prod->ID . '"/> </td>
								<td class="aw-prod-img"><img width="40%" src="' . $url . '"></td>
								<td class="column-primary" data-colname="Name">' . $_product->get_name() . ' <button type="button" class="toggle-row">
										                <span class="screen-reader-text">show details</span>
										            </button></td>
								<td class="sku column-sku" data-colname="SKU">' . $_product->get_sku() . '</td>
								<td data-colname="Stock">' . $stock_html . '</td>
								<td class="price" data-colname="Price">' . $_product->get_price_html() . '</td>
								<td data-colname="ID">' . wc_get_product_category_list($prod->ID) . '</td>
								<td data-colname="Tags">' . $tags . '</td>
								<td data-colname="Featured">' . $featured . '</td>
								<td data-colname="Status">' . $status . '<br/>' . $_product->get_date_created()->date('Y/m/d') . '</td>
							</tr>';
			}
		} else {
				$data['items'] = '';
				$tbody	.= '<tr><td colspan="10">No products found</td></tr>';
		}
		 
		if ($all>0) { 
			$subsubsub 	.= '<li class="all"  ><a href="javascript:void(0)"  data-value="all" class="current" onclick="post_list_by_statuslist(this)" >All <span class="count">(' . $all . ')</span></a> |</li>'; 
		}
		if ($published>0) { 
			$subsubsub 	.= '<li class="published"><a href="javascript:void(0)" class="" aria-current="page" data-value="published" onclick="post_list_by_statuslist(this)">Published <span class="count">(' . $published . ')</span></a> |</li>'; 
		}
		if ($draft>0) { 
			$subsubsub 	.= '<li class="draft"><a href="javascript:void(0)" class="" aria-current="page" data-value="draft" onclick="post_list_by_statuslist(this)">Draft<span class="count">(' . $draft . ')</span></a> |</li>';
		}
		if ($trash>0) { 
			$subsubsub 	.= '<li class="trash"><a href="javascript:void(0)" class="" aria-current="page" data-value="trash" onclick="post_list_by_statuslist(this)">Trash<span class="count">(' . $trash . ')</span></a> |</li>'; 
		}
		
		wp_reset_query();
 
		$data['tbody']		= $tbody;
		$data['subsubsub'] 	= $subsubsub;
		$data['tab_id']		= $tab_id;
		echo json_encode($data);
		die;
	}

	/*
		** To Display popup on admin footer hooks
	*/
	public static function aw_add_new_product_popup() {
		$page 	= '';
		if (isset($_GET['page']) ) {
			$page = sanitize_text_field($_GET['page']);
		}

		if ('aw-batc-product-list-admin' === $page) { 
			
			?>
			<div id="add_new_prod_modal" class="prod_modal">

			  <!-- Modal content -->
			  <div class="prod_modal-content">
				<!-- <span class="bal_modal_close">&times;</span> -->
				<!--<span>Update Balance</span>-->
				<div class="wrap">

					<div class="aw-header">
						<h2>Add New Product</h2> 
						<a href="javascript:void(0)" alt="f158" class="dashicons dashicons-no batc-popup-close"></a>
					</div>

					<ul class="subsubsub" id="post_counts">
						<!-- <li class="all"><a href="edit.php?post_type=aw_bulk_product_list" class="current" aria-current="page">All <span class="count">(3)</span></a> |</li>
						<li class="publish"><a href="edit.php?post_status=publish&amp;post_type=aw_bulk_product_list">Published <span class="count">(3)</span></a></li> -->
					</ul>

					<p class="search-box">
						<label class="screen-reader-text" for="post-search-input">Search Lists:</label>
						<input type="search" id="post-search-input" name="s" value="">
						<input type="submit" id="search-submit" class="button" value="Search products">
					</p>

					<div class="tablenav top popuptable">

						<div class="alignleft actions">
							
							<?php 
								echo wp_kses(self::category_subcategory_dropdown(), wp_kses_allowed_html('post'));

								$product_all_type = wc_get_product_types();
							?>
							<select name="product_type" id="dropdown_product_type">
								<option value="">Filter by product type</option>
								<?php	
								foreach ($product_all_type as $type=> $alltype) {
									if ('external' != $type) {
										?>
									<option value="<?php echo esc_html($type); ?>"><?php echo esc_html($alltype); ?></option>
										<?php 
										if ('simple'===$type) { 
											?>
											<option value="downloadable"> → Downloadable</option>
											<option value="virtual"> → Virtual</option>
											<?php 
										}
									}  
								} 
								?>
							</select>
							<select name="stock_status" id="dropdown_stock_status">
								<option value="">Filter by stock status</option><option value="instock">In stock</option>
								<option value="outofstock">Out of stock</option><option value="onbackorder">On backorder</option>
							</select>
							<input type="button" name="filter_action" id="post-query-submit" onclick="aw_filterproducts();" class="button" value="Filter">	
						</div>
						
						<div class="tablenav-pages">
							<span class="displaying-num post_display_num"></span>
							<span class="pagination-links"><a class="tablenav-pages-navspan button disabled firstprev" aria-hidden="true" onclick="paginationclick('firstprev')">«</a>
							<a href="javascript:void(0)" class="tablenav-pages-navspan button disabled onlyprev" aria-hidden="true" onclick="paginationclick('onlyprev')">‹</a>
							<span class="paging-input">
								<label for="current-page-selector" class="screen-reader-text">Current Page</label>
								<input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging">
								<span class="tablenav-paging-text"> of <span class="total-pages"> </span>
								</span>
							</span>
							<a href="javascript:void(0)" class="tablenav-pages-navspan button disabled onlynext" aria-hidden="true" onclick="paginationclick('onlynext')">›</a>
							<a href="javascript:void(0)" class="tablenav-pages-navspan button disabled lastnext" aria-hidden="true" onclick="paginationclick('lastnext')">»</a> </span>
						</div>
							<br class="clear">
					</div>
					<div id="batc-loader"></div>
					<table class="wp-list-table widefat striped batc-new-prod-popup">
						<thead>
							<tr>
								<td class="column-primary"><input id="cb-select-all-1" class="aw-allselect_chk" type="checkbox" /></td>	
								<th>Image</th>	
								<th class="column-name sorted asc"><a href="javascript:void(0)" onclick="aw_sorting_table_data('column-name','title')"><span>Name<span> <span class="sorting-indicator"></span></a></th>	
								<th class="column-sku sorted asc"><a href="javascript:void(0)" onclick="aw_sorting_table_data('column-sku','_sku')"><span>SKU<span> <span class="sorting-indicator"></span></a></th>	
								<th>Stock</th>	
								<th class="column-price sorted asc"><a href="javascript:void(0)" onclick="aw_sorting_table_data('column-price','_price')"><span>Price<span> <span class="sorting-indicator"></span></a></th>	
								<th>Category</th>
								<th>Tags</th>	
								<th><span alt="f155" class="dashicons dashicons-star-filled"></span></th>
								<th class="manage-column column-date sorted asc"><a href="javascript:void(0)" onclick="aw_sorting_table_data('column-date','date')"><span>Date<span> <span class="sorting-indicator"></span></a></th>
							</tr>	
						</thead>
						<tbody id="batc-list">
							<!-- Here Data is adding from Ajax call -->		
						</tbody>

						<tfoot>
							<tr>
								<td class="column-primary"><input id="cb-select-all-1" class="aw-allselect_chk manage-column column-cb check-column" type="checkbox" /></td>	
								<th id="thumb" class="manage-column column-thumb">Image</th>	
								<th scope="col" id="name" class="sorted asc"><a href="javascript:void(0)" onclick="aw_sorting_table_data('column-name','title')"><span>Name<span> <span class="sorting-indicator"></span></a></th>	
								<th scope="col" id="sku"  class="column-sku sorted asc"><a href="javascript:void(0)" onclick="aw_sorting_table_data('column-sku','_sku')"><span>SKU<span> <span class="sorting-indicator"></span></a></th>	
								<th scope="col" id="is_in_stock" class="column-is_in_stock">Stock</th>	
								<th class="column-price sorted asc"><a href="javascript:void(0)" onclick="aw_sorting_table_data('column-price','_price')"><span>Price<span> <span class="sorting-indicator"></span></a></th>	
								<th scope="col" id="product_cat" class="column-product_cat">Category</th>
								<th scope="col" id="product_tag" class="column-product_tag">Tags</th>	
								<th scope="col" id="featured" class="column-featured"><span alt="f155" class="dashicons dashicons-star-filled"></span></th>
								<th scope="col" id="date" class="column-date sorted asc"><a href="javascript:void(0)" onclick="aw_sorting_table_data('column-date','date')"><span>Date<span> <span class="sorting-indicator"></span></a></th>
							</tr>	
						</tfoot>
					</table>	

					<div class="tablenav bottom">
						<div class="tablenav-pages one-page">
							<span class="displaying-num post_display_num"></span>
							<!--<span class="pagination-links">
									<span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>
									<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
									<span class="screen-reader-text">Current Page</span>
									<span id="table-paging" class="paging-input">
										<span class="tablenav-paging-text">1 of <span class="total-pages">1</span></span>
									</span>
									<span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
									<span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>
								</span> -->
						</div>
						<div class="alignleft actions">
							<input type="submit" name="savelist" id="" data-tab_id="" class="button aw_save_list_btn" value="Save">
						</div>
						<br class="clear">
					</div>

				</div> <!-- close wrap class -->	
			  </div>
			</div>

			<div id="batc_variation_modal" class="batc_variation_modal">
			  <!-- Modal content -->
				<div class="batc_modal-content">
				<!-- <span class="bal_modal_close">&times;</span> -->
				<!--<span>Update Balance</span>-->
					<!-- <div class="wrap">

						<div class="aw-header">
							<h2>Add New Product</h2> 
							<a href="javascript:void(0)" alt="f158" class="dashicons dashicons-no batc-popup-close"></a>
						</div>
					</div> -->
				</div>	
			</div>	
			<?php
		}
	}

	public static function category_subcategory_dropdown() { 
		$term_ids		= array();
		$taxonomy     	= 'product_cat';
		$orderby      	= 'name';  
		$show_count   	= 1;      // 1 for yes, 0 for no
		$pad_counts   	= 0;      // 1 for yes, 0 for no
		$hierarchical 	= 1;      // 1 for yes, 0 for no  
		$title        	= '';  
		$empty        	= 0;
		$options 		= '';	
		$count 			= 0;	

		$args = array(
				 'taxonomy'     => $taxonomy,
				 'orderby'      => $orderby,
				 'show_count'   => $show_count,
				 'pad_counts'   => $pad_counts,
				 'hierarchical' => $hierarchical,
				 'title_li'     => $title,
				 'hide_empty'   => $empty
				);
		$all_categories = get_categories( $args );
		$options .= '<select name="product_cat" id="product_cat" class="dropdown_product_cat">
						<option value="">Select a category</option>';
		
		$total_parent_cat_pro = array();
		$total_parent_cat_pro = self::aw_get_count_parent_category_product();
		$term_ids = array_keys($total_parent_cat_pro);
		foreach ($all_categories as $cat) {
			if (0 == $cat->category_parent) {
				$category_id = $cat->term_id;  
				if ( in_array( $cat->term_id , $term_ids ) ) {
					$count = $total_parent_cat_pro[$cat->term_id];
				} else {
					$count = $cat->count;
				}
				
				$options .= '<option class="level-0" value="' . $cat->slug . '">' . $cat->name . '&nbsp;&nbsp;(' . $count . ')</option>';

				$args2 = array(
						'taxonomy'     => $taxonomy,
						'child_of'     => 0,
						'parent'       => $category_id,
						'orderby'      => $orderby,
						'show_count'   => $show_count,
						'pad_counts'   => $pad_counts,
						'hierarchical' => $hierarchical,
						'title_li'     => $title,
						'hide_empty'   => $empty
				);
				$sub_cats = get_categories( $args2 );
				if ($sub_cats) {
					foreach ($sub_cats as $sub_category) {
						$options .= '<option  class="level-1" value="' . $sub_category->slug . '">&nbsp;&nbsp;&nbsp;&nbsp;' . $sub_category->name . '&nbsp;&nbsp;(' . $sub_category->count . ')</option>';
					}   
				}
			}       
		}
		$options .= '</select>';
		return $options;
	}

	public static function aw_get_count_parent_category_product() {
		$product_categories = get_terms( 'product_cat' );
		$categories_count = array();
		foreach ( $product_categories as $key => $value ) {
			$category_term_id = $value->term_id;

			if ( $value->parent > 0 ) {
				   $category_parent_term_id = $value->parent;
				if ( !isset( $categories_count[$category_parent_term_id] ) ) {
						$categories_count[$category_parent_term_id] = $value->count;
				} else {
					$categories_count[$category_parent_term_id] = $categories_count[$category_parent_term_id] + $value->count;
				}
			} else {
				if ( !isset( $categories_count[$category_term_id] ) ) {
						$categories_count[$category_term_id] = $value->count;
				} else {
					$categories_count[$category_term_id] = $categories_count[$category_term_id] + $value->count;       
				}
			}
		}
		return  $categories_count;
	}
}
?>
