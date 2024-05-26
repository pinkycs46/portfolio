<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class AwProductRuleAdmin {
	public static function aw_pl_rule_save_flash_notice( $notice = '', $type = 'warning', $dismissible = true ) { 
		$notices = get_option( 'rule_flash_notices', array() );
		$dismissible_text = ( $dismissible ) ? 'is-dismissible' : '';

		$notices = array(
						'notice' => $notice, 
						'type' => $type, 
						'dismissible' => $dismissible_text
					); 	
		update_option('rule_flash_notices', $notices );
	}
	public static function aw_pl_fetch_woo_product_list() {
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
		$product_sku = '_sku';

		if (isset($_POST['tab_id'])) {
			$tab_id = sanitize_text_field($_POST['tab_id']);
		}
		if (isset($_POST['parentid'])) {
			$parentid = sanitize_text_field($_POST['parentid']);
		}
		if (isset($_POST['childid'])) {
			$childid = sanitize_text_field($_POST['childid']);
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

			$index++;
			$post['meta_query'][$index] = array(
											'key'		=> '_stock_status',
											'value'		=> sanitize_text_field($_POST['stock_status']),
											'compare' 	=> '='
										);
																			
		}
		if ($product_sku) {
			$index++;
			$post['meta_query'][$index] = array(
											'key'		=> '_sku',
											'value'		=> '',
											'compare' 	=> '!='
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

				$url = aw_pl_get_product_image( $prod->ID );
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
				if (in_array($_product->get_sku(), $chckedlist)) {
					$checked = 'checked';
				}
				$tbody	.= '<tr class="' . $responsiveclass . '"">
								<td class="aw-checkbox"><input  class="aw_batc_listcheckbox" ' . $checked . ' id="cb-select-' . $prod->ID . '" type="checkbox" name="post' . $parentid . '-' . $childid . '[]" onclick="aw_pl_listcheckbox(this,' . $parentid . ',' . $childid . ')" value="' . $_product->get_sku() . '"/> </td>
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
			$subsubsub 	.= '<li class="all"  ><a href="javascript:void(0)"  data-value="all" class="current" onclick="pl_post_list_by_statuslist(this)" >All <span class="count">(' . $all . ')</span></a> |</li>'; 
		}
		if ($published>0) { 
			$subsubsub 	.= '<li class="published"><a href="javascript:void(0)" class="" aria-current="page" data-value="published" onclick="pl_post_list_by_statuslist(this)">Published <span class="count">(' . $published . ')</span></a> |</li>'; 
		}
		if ($draft>0) { 
			$subsubsub 	.= '<li class="draft"><a href="javascript:void(0)" class="" aria-current="page" data-value="draft" onclick="pl_post_list_by_statuslist(this)">Draft<span class="count">(' . $draft . ')</span></a> |</li>';
		}
		if ($trash>0) { 
			$subsubsub 	.= '<li class="trash"><a href="javascript:void(0)" class="" aria-current="page" data-value="trash" onclick="pl_post_list_by_statuslist(this)">Trash<span class="count">(' . $trash . ')</span></a> |</li>'; 
		}
		
		wp_reset_query();
 
		$data['tbody']		= $tbody;
		$data['subsubsub'] 	= $subsubsub;
		$data['tab_id']		= $tab_id;
		$data['parentid']	= $parentid;
		$data['childid']	= $childid;
		echo json_encode($data);
		die;
	}
	public static function aw_pl_add_new_product_popup() {
		$page 	= '';
		if (isset($_GET['page']) ) {
			$page = sanitize_text_field($_GET['page']);
		}

		if ('new-rule' === $page) { 
			
			?>
			<div id="add_new_prod_modal" class="prod_modal" style="display: none">

			  <!-- Modal content -->
			  <div class="prod_modal-content">
				
					<div class="aw-header">
						<h2>Add New Product</h2> 
						<a href="javascript:void(0)" alt="f158" class="dashicons dashicons-no batc-popup-close"></a>
					</div>

					<ul class="subsubsub" id="post_counts">
					</ul>

					<p class="search-box">
						<label class="screen-reader-text" for="post-search-input">Search Lists:</label>
						<input type="search" id="post-search-input" name="s" value="">
						<input type="submit" id="search-submit" class="button" value="Search products">
					</p>

					<div class="tablenav top popuptable">

						<div class="alignleft actions">							
							<?php 
								echo wp_kses(self::aw_pl_category_subcategory_dropdown(), wp_kses_allowed_html('post'));

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
							<input type="button" name="filter_action" id="post-query-submit" onclick="aw_pl_filterproducts();" class="button" value="Filter">	
						</div>
						
						<div class="tablenav-pages">
							<span class="displaying-num post_display_num"></span>
							<span class="pagination-links"><a class="tablenav-pages-navspan button disabled firstprev" aria-hidden="true" onclick="plpaginationclick('firstprev')">«</a>
							<a href="javascript:void(0)" class="tablenav-pages-navspan button disabled onlyprev" aria-hidden="true" onclick="plpaginationclick('onlyprev')">‹</a>
							<span class="paging-input">
								<label for="current-page-selector" class="screen-reader-text">Current Page</label>
								<input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging">
								<span class="tablenav-paging-text"> of <span class="total-pages"> </span>
								</span>
							</span>
							<a href="javascript:void(0)" class="tablenav-pages-navspan button disabled onlynext" aria-hidden="true" onclick="plpaginationclick('onlynext')">›</a>
							<a href="javascript:void(0)" class="tablenav-pages-navspan button disabled lastnext" aria-hidden="true" onclick="plpaginationclick('lastnext')">»</a> </span>
						</div>
							<br class="clear">
					</div>
					<div id="batc-loader"></div>
					<table class="wp-list-table widefat striped batc-new-prod-popup">
						<thead>
							<tr>
								<td class="column-primary"><input id="cb-select-all-1" class="aw-allselect_chk" type="checkbox" /></td>	
								<th>Image</th>	
								<th class="column-name sorted asc"><a href="javascript:void(0)" onclick="aw_pl_sorting_table_data('column-name','title')"><span>Name<span> <span class="sorting-indicator"></span></a></th>	
								<th class="column-sku sorted asc"><a href="javascript:void(0)" onclick="aw_pl_sorting_table_data('column-sku','_sku')"><span>SKU<span> <span class="sorting-indicator"></span></a></th>	
								<th>Stock</th>	
								<th class="column-price sorted asc"><a href="javascript:void(0)" onclick="aw_pl_sorting_table_data('column-price','_price')"><span>Price<span> <span class="sorting-indicator"></span></a></th>	
								<th>Category</th>
								<th>Tags</th>	
								<th><span alt="f155" class="dashicons dashicons-star-filled"></span></th>
								<th class="manage-column column-date sorted asc"><a href="javascript:void(0)" onclick="aw_pl_sorting_table_data('column-date','date')"><span>Date<span> <span class="sorting-indicator"></span></a></th>
							</tr>	
						</thead>
						<tbody id="batc-list">
							<!-- Here Data is adding from Ajax call -->		
						</tbody>

						<tfoot>
							<tr>
								<td class="column-primary"><input id="cb-select-all-1" class="aw-allselect_chk manage-column column-cb check-column" type="checkbox" /></td>	
								<th id="thumb" class="manage-column column-thumb">Image</th>	
								<th scope="col" id="name" class="sorted asc"><a href="javascript:void(0)" onclick="aw_pl_sorting_table_data('column-name','title')"><span>Name<span> <span class="sorting-indicator"></span></a></th>	
								<th scope="col" id="sku"  class="column-sku sorted asc"><a href="javascript:void(0)" onclick="aw_pl_sorting_table_data('column-sku','_sku')"><span>SKU<span> <span class="sorting-indicator"></span></a></th>	
								<th scope="col" id="is_in_stock" class="column-is_in_stock">Stock</th>	
								<th class="column-price sorted asc"><a href="javascript:void(0)" onclick="aw_pl_sorting_table_data('column-price','_price')"><span>Price<span> <span class="sorting-indicator"></span></a></th>	
								<th scope="col" id="product_cat" class="column-product_cat">Category</th>
								<th scope="col" id="product_tag" class="column-product_tag">Tags</th>	
								<th scope="col" id="featured" class="column-featured"><span alt="f155" class="dashicons dashicons-star-filled"></span></th>
								<th scope="col" id="date" class="column-date sorted asc"><a href="javascript:void(0)" onclick="aw_pl_sorting_table_data('column-date','date')"><span>Date<span> <span class="sorting-indicator"></span></a></th>
							</tr>	
						</tfoot>
					</table>	

					<div class="tablenav bottom">
						<div class="tablenav-pages one-page">
							<span class="displaying-num post_display_num"></span>
						</div>
						<div class="alignleft actions">
							<input type="submit" name="savelist" id="" data-tab_id="" class="button aw_save_list_btn" value="Save">
						</div>
						<br class="clear">
					</div>
				 <!-- close wrap class -->	
			  </div>
			</div>	
			<?php
		}
	}

	public static function aw_pl_category_subcategory_dropdown() { 
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
		$total_parent_cat_pro = self::aw_pl_get_count_parent_category_product();
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

	public static function aw_pl_get_count_parent_category_product() {
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

	public static function aw_pl_rule_page() {
		$table = new AwProductRuleList();
		$search = '';

		if (isset($_GET['s'])) {
			$search = sanitize_text_field($_GET['s']);
		}

		if (isset($_GET['status'])) {
			$status= sanitize_text_field($_GET['status']);
			$table->prepare_items($status, $search);
		} else {
			$table->prepare_items();
		}
		$count_one = $table->get_count(1);
		$count_two = $table->get_count(2);
		$count_all = $count_one + $count_two;
		$count_trashed = $table->get_count(0);
		if (isset($_REQUEST['id'])&&is_array($_REQUEST['id'])) {
			$count = count($_REQUEST['id']);
		} else {
			$count = 1;
		}
		$message = '';
		if ('trash' === $table->current_action() && isset($_REQUEST['id'])) {
		/* translators: number count  */
			$message = '<div class="updated below-h2 cutommessage"  ><p>' . sprintf(__('%d Rule moved to the Trash', 'Rule List'), intval($count)) . '</p></div>';
		}
		if ('delete' === $table->current_action() && isset($_REQUEST['id'])) {
		/* translators: number count  */
			$message = '<div class="updated below-h2 cutommessage"  ><p>' . sprintf(__('%d Rule permanently deleted.', 'Rule List'), intval($count)) . '</p></div>';
		}
		if ('untrash' === $table->current_action() && isset($_REQUEST['id'])) {
		/* translators: number count  */
			$message = '<div class="updated below-h2 cutommessage"  ><p>' . sprintf(__('%d Rule restore.', 'Rule List'), intval($count)) . '</p></div>';
		}
		$notice = maybe_unserialize(get_option( 'rule_flash_notices'));
		if ( ! empty( $notice ) ) {
			printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
			wp_kses($notice['type'], wp_kses_allowed_html('post')),
			wp_kses($notice['dismissible'], wp_kses_allowed_html('post')),
			wp_kses($notice['notice'], wp_kses_allowed_html('post'))
			);
			delete_option( 'rule_flash_notices');
		}

		?>

		<div class="wrap">
			<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
			<h1 class="wp-heading-inline"><?php esc_html_e('Product Rules', 'Rule List'); ?></h1>
			<a href="admin.php?page=new-rule" class="page-title-action">New Rule</a>
			<?php echo wp_kses($message, 'post'); ?>
			<hr class="wp-header-end">
			<ul class="subsubsub">
				<li class="all"><a href="admin.php?page=aw_pl_rule_page" class="current" aria-current="page">All <span class="count">(
				<?php
				echo intval($count_all); 
				?>
				)</span></a> |</li>
				<li class="trash"><a href="admin.php?status=0&page=aw_pl_rule_page">Trash <span class="count">(<?php echo intval($count_trashed); ?>)</span></a></li>
			</ul>
			<form id="posts-filter" method="get">
				<p class="search-box">
					<input type="hidden" name="page" class="page" value="aw_pl_rule_page">	
					<input type="hidden" name="status" class="post_status_page" value="
					<?php 
					if (isset($_GET['status']) && 0  == $_GET['status'] ) {
						echo 0;
					} else {
						echo 1;} 
					?>
					">
					<input type="search" id="post-search-input" name="s" value="<?php echo esc_html($search); ?>">
					<input type="submit" id="search-submit" class="button" value="Search Rule">
				</p>
			</form>
			<form id="rule-table" method="GET">
				<input type="hidden" name="page" value="<?php echo esc_html('aw_pl_rule_page'); ?>"/>
				<input type="hidden" name="page" value="<?php echo isset($_REQUEST['page']) ? wp_kses($_REQUEST['page'], 'post') : '' ; ?>"/>
				<?php $table->display(); ?>
			</form>
		</div>
		<?php
		//$table->display();
	}
	public static function aw_pl_rule_new_page() {
		global $wpdb;
		$heading = 'New Rule';
		$anyone 		 				= '';
		$loggedinuser					= '';
		$enable_yes 					= '';
		$enable_no 						= '';
		$select_label_name				= '';
		$rule_name 						= '';
		$priority 						= '';
		$rule_status 					= '';
		$rule_allow_to_user 			= '';
		$start_date 					= '';
		$end_date 						= '';
		$frontend_label_text 			= '';
		$frontend_medium_text 			= '';
		$frontend_small_text 			= '';
		$product_id 					= '';
		//$tab_id 		= '';
		$tab_id = strtotime(gmdate('Y-m-d H:i:s'));
		$label_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pl_product_label WHERE status=%d", 1), ARRAY_A);

		if (isset($_GET['id']) && !empty($_GET['id'])) {
			$heading = 'Edit Rule';
			$id = sanitize_text_field($_GET['id']);
			$rule_data = aw_pl_rule_row($id);


			if (!empty($rule_data)) {
				$rule_name 					= $rule_data->rule_name;
				$select_label_name 			= $rule_data->label_id;
				$priority 					= $rule_data->priority;
				$rule_status 				= $rule_data->rule_status;
				$rule_allow_to_user 		= $rule_data->rule_allow_to_user;
				$start_date					= $rule_data->start_date;
				$end_date					= $rule_data->end_date;
				$frontend_label_text 		= $rule_data->frontend_label_text;
				$frontend_medium_text 		= $rule_data->frontend_medium_text;
				$frontend_small_text 		= $rule_data->frontend_small_text;
				$product_id 				= $rule_data->product_id;
				$product_condition 			= $rule_data->product_condition;
			}
			if ($rule_name) {
				$rule_name = sanitize_text_field($rule_name);
			}
			if ($select_label_name) {
				$select_label_name = sanitize_text_field($select_label_name);
			}
			if ($priority) {
				$priority = sanitize_text_field($priority);
			}

			if ($rule_status) {
				$rule_status = sanitize_text_field($rule_status);
				if ('1' == $rule_status) {
					$enable_yes = 'selected = selected';
				}
				if ('2' == $rule_status) {
					$enable_no = 'selected = selected';
				}

			}
			if ($rule_allow_to_user) {
				$rule_allow_to_user = sanitize_text_field($rule_allow_to_user);
				if ('loggedinuser' == $rule_allow_to_user) {
					$loggedinuser = 'selected = selected';
				}  
				if ('anyone' == $rule_allow_to_user) {
					$anyone = 'selected = selected';
				}
			}

			if ($start_date) {
				$start_date = sanitize_text_field($start_date);
			}
			if ($end_date) {
				$end_date = sanitize_text_field($end_date);
			}

			if ($frontend_label_text) {
				$frontend_label_text = sanitize_text_field($frontend_label_text);
			}
			if ($frontend_medium_text) {
				$frontend_medium_text = sanitize_text_field($frontend_medium_text);
			}
			if ($frontend_small_text) {
				$frontend_small_text = sanitize_text_field($frontend_small_text);
			}
			if ($product_id) {
				$product_id = sanitize_text_field($product_id);
			}
			if ($select_label_name) {
				$label_id = sanitize_text_field($select_label_name);
			}
			if ($product_condition) {
				$pro_condition = unserialize($product_condition);
			}

		}
		$notice = maybe_unserialize(get_option( 'rule_flash_notices'));
		if ( ! empty( $notice ) ) {
			printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
			wp_kses($notice['type'], wp_kses_allowed_html('post')),
			wp_kses($notice['dismissible'], wp_kses_allowed_html('post')),
			wp_kses($notice['notice'], wp_kses_allowed_html('post'))
			);
			delete_option( 'rule_flash_notices');
			
		}

		$args = array('post_type'  => 'product' ,'limit'  => -1,
		'status' => 'publish',);
		$all_product = wc_get_products($args);
		foreach ( $all_product as $product ) {
			$sale_price = $product->get_sale_price();				
		}
		?>
		<div class="tab-grid-wrapper">
			<div class="spw-rw clearfix">
				<div class="panel-box product-label-setting">
					<div class="page-title">
						<h2>
						   <?php echo wp_kses($heading, wp_kses_allowed_html('post')); ?>
							<small class="wc-admin-breadcrumb"><a href="<?php echo wp_kses(admin_url(), wp_kses_allowed_html('post')); ?>admin.php?page=aw_pl_rule_page" aria-label="Return to emails">⤴</a></small>
						</h2>
						<div class="panel-body">
							<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" id="rdpl_new_rule_form" enctype="multipart/form-data">
								<?php wp_nonce_field( 'aw_pl_save_rule_form', 'rdpl_new_rule_nonce' ); ?>
							  <input type="hidden" name="action" value="aw_pl_save_rule_form">
								<div class="tabcontent rule-general-set" id="genral-setting-tab" style="display: block;">
									<ul>
										<li>
											<label>Enable Rule</label>
											<div class="control">
												<select name="enable_rule">
													<option value="1" <?php echo wp_kses($enable_yes, wp_kses_allowed_html('post')); ?>>Yes</option>
													<option value="2" <?php echo wp_kses($enable_no, wp_kses_allowed_html('post')); ?>>No</option>
												</select>
												<span class="enable_rule_error"></span>
											</div>
										</li>
										<li>
											<label>Rule name</label>
											<div class="control">
												<input type="text" name="rule_name" class="rule_name" value="<?php echo wp_kses($rule_name, wp_kses_allowed_html('post')); ?>" />
												<span class="rule_name_error"></span>
											</div>
										</li>
										<li>
											<label>Label Name</label>
											<div class="control">
											   <select name="select_label_name" class="select_label_name" id="select_label_name">
													<option>Select label</option>	
													<?php foreach ($label_data as $data) { ?>
													<option value="<?php echo wp_kses($data['id'], wp_kses_allowed_html('post')); ?>" data-value="<?php echo wp_kses($data['label_size'], wp_kses_allowed_html('post')); ?>"
																			  <?php 
																				if (!empty($select_label_name) && $label_id == $data['id']) {
																					?>
  selected = "selected"<?php } ?>><?php echo wp_kses($data['name'], wp_kses_allowed_html('post')); ?></option><?php } ?>
												</select>
												<span class="select_label_name_error"></span>
											</div>
										</li>
										<li>
											<label>Priority</label>
											<div class="control">
												<input type="text" name="priority" class="priority" value="<?php echo wp_kses($priority, wp_kses_allowed_html('post')); ?>" autocomplete="off" maxlength="5" onkeypress="return checkIt(event)" />
												<span class="priority_error"></span>
												<p><span>Rule with lower value will appear first</span></p>
												
											</div>
										</li>
										<li>
											<label>Rule Allow to user</label>
											<div class="control">
												<select name="rule_setting_view_content" class="allow_user">
												<option value="anyone" <?php echo wp_kses($anyone, wp_kses_allowed_html('post')) ; ?>>Anyone</option>
												<option value="loggedinuser" <?php echo wp_kses($loggedinuser, wp_kses_allowed_html('post')); ?>>Logged In Users</option>
												</select>
												<span class="rule_setting_view_content_error"></span>
											</div>
										</li>
										<li>
											<label>From</label>
											<div class="control">
												<div class="from_date">
													  <input type="text" id="start_date" name="start_date" class="start_date" value="<?php echo wp_kses($start_date, wp_kses_allowed_html('post')); ?>">
													  <span class="start_date_error"></span>
												</div>
											</div>
										</li>
										<li>
											<label>To</label>
											<div class="control">
												<div class="to_date">
													  <input type="text" id="end_date" name="end_date" class ="end_date" value="<?php echo wp_kses($end_date, wp_kses_allowed_html('post')); ?>">
													  <span class="end_date_error"></span>
												</div>
											</div>
										</li>
									</ul>
									<h3>Frontend Label Text</h3>
									<h4>Hover over the question mark to find variable options. Click it to get more details in user guide.&nbsp;
										<div class="tooltip">
											<span class="label_adv_dropbtn_tooltip tips">
											</span>
											<span class="tooltiptext"><div class="admin__field-tooltip-content">
												<div class=" tooltipbeforeDescription"></div>
													<p><strong>{attribute:code}</strong> - attribute value
													</p>
													<p><strong>{save_percent}</strong> -discount percentage</p>
													<p><strong>{save_amount}</strong> - discount amount</p>
													<p><strong>{price}</strong> -regular price</p>
													<p><strong>{special_price}</strong> - special price</p>
													<p><strong>{stock}</strong> -stock amount</p>
													<p><strong>{br}</strong> -new line</p>
													<p><strong>{sku}</strong> - product SKU</p>
													<p><strong>{spdl}</strong> -X days left for special price</p>
													<p><strong>{sphl}</strong> -X hours left for special price</p>
												</div>
											</span>
										</div>
									</h4>
									<div class="tabcontent rule-seo" id="rule-seo" style="display: block;">
										<table style=" border: 1px solid black; background-color: #fff;" width="100%" cellspacing="20px">
												<tr >
													<td>
														<label>Large Label Text</label>
														<input type="text" name="large_label_text" class= "large_label_text" value="<?php echo wp_kses($frontend_label_text, wp_kses_allowed_html('post')); ?>" /><br />
														<span class ="large_label_text_error"></span>
													</td>
													<td>
														<label>Medium Label Text</label>
													   <input type="text" name="medium_label_text" class= "medium_label_text" value="<?php echo wp_kses($frontend_medium_text, wp_kses_allowed_html('post')); ?>"/><br />
														<span class="medium_label_text_error"></span>
													</td>
													<td>
														<label>Small Label Text</label>
													   <input type="text" name="small_label_text" class= "small_label_text" value="<?php echo wp_kses($frontend_small_text, wp_kses_allowed_html('post')); ?>"/><br />
														<span class="small_label_text_error"></span>
													</td>
												</tr>
										</table>
									</div>
									<h3>Product Conditions</h3>
									<div class="tabcontent rule-seo" id="rule-seo" style="display: block;">
										<div class="updated below-h2 cutommessage" style="border-left-color: #007bdb;background: #fffbbb;margin:0px 0px 0px;"><p style="margin:1em 0;">Please specify products where the rule should be applied</p></div>
										<div class="rule-tree-wrapper" id="rule-tree-wrapper">
											<?php
											if (isset($_GET['id']) && !empty($_GET['id']) && !empty($pro_condition)) {
												$valueall = '';
												$valuetrue = '';
												$all = '';
												$any = '';
												$true = '';
												$false = '';

												$sale_is = '';
												$sale_is_not = '';
												$sale_operator = '';
												$sale_value = '';

												$sale_no = '';
												$sale_yes = '';

												$weight_is = '';
												$weight_is_not = '';
												$weight_operator = '';
												$weight_equal_greater = '';
												$weight_equal_less = '';
												$weight_greater_than = '';
												$weight_less_than = '';

												$price_is = '';
												$price_is_not = '';
												$price_equal_greater = '';
												$price_equal_less = '';
												$price_greater_than = '';
												$price_less_than = '';
												$price_contains = '';
												$price_not_contains = '';
												$price_one = '';
												$price_not_one = '';

												$range_operator = '';
												$range_equal = '';
												$range_is_not = '';
												$range_equal_greater = '';
												$range_more_than = '';
												$range_less_than = '';

												$dimensions_operator = '';
												$dimensions_is = '';
												$dimensions_is_not = '';
												$dimensions_equal_greater = '';
												$dimensions_equal_less = '';
												$dimensions_greater_than = '';
												$dimensions_less_than = '';

												$sku_is = '';
												$sku_is_not = '';
												$sku_contains = '';
												$sku_not_contains = '';
												$sku_one = '';
												$sku_not_one = '';

												$category_is = '';
												$category_is_not = '';		
												$category_contains = '';
												$category_not_contains = '';
												$category_one = '';
												$category_not_one = '';

												$attr_is = '';
												$attr_is_not = '';
												$attr_is_undefined = '';
												$attr_operator = '';
												$attr_value = '';

												$attr_no = '';
												$attr_yes = '';

												$str ='';
												$str.= '<ol class=" ul_li_0_0" id="level-0-0"><li>';
												foreach ($pro_condition[0] as $parentkey=> $innerarray) {
													foreach ($innerarray as $childkey=> $array) {
														if (0 == $parentkey  && 0 == $childkey) {
															$startul = '';
															$endul = '';
														} else {
															$startul = '<ul class="orderedlist  ul_li_' . $parentkey . '_' . $childkey . '">';
															$endul = '</ul>';
														}
														foreach ($array as $key=> $value) {
															if ('ALL' == $value) {
																$any = '';
																$valueall = $value;
																$all = 'selected = selected';
															} else if ('ANY' == $value) {
																$all = '';
																$valueall = $value;
																$any = 'selected = selected';
															}

															if ('TRUE' == $value) {
																$valuetrue = $value;
																$true = 'selected = selected';
															} else if ('FALSE' == $value) {
																$valuetrue = $value;
																$false = 'selected = selected';
															}

															if ('aggregator' === $key ) {
																$str.= $endul . '</ul>';

																$str.= $startul . '<p>If <span class="rule-param"><a href="javascript:void(0)" id="label_all-' . $parentkey . '-' . $childkey . '" onclick="return label_all(' . $parentkey . ',' . $childkey . ')">' . $valueall . '</a><span class="element"><select  name = "rule[conditions][' . $parentkey . '][' . $childkey . '][aggregator]" id="select_label_all-' . $parentkey . '-' . $childkey . '" onchange="getval(this,' . $parentkey . ',' . $childkey . ');" style="display: none;"><option value="ALL" ' . $all . '>ALL</option><option  value="ANY" ' . $any . '>ANY</option></select></span></span>&nbsp;of these conditions are';
															} else if ( 'value' === $key) {
																$str.= ' <span class="rule-param"><a href="javascript:void(0)" id ="label_true-' . $parentkey . '-' . $childkey . '" onclick="return label_true(' . $parentkey . ',' . $childkey . ')">' . $valuetrue . '</a><span class="element"><select name = "rule[conditions][' . $parentkey . '][' . $childkey . '][value]" id="select_label_true-' . $parentkey . '-' . $childkey . '" onchange="getval1(this,' . $parentkey . ',' . $childkey . ');" style="display: none;"><option ' . $true . ' value="TRUE">TRUE</option><option ' . $false . ' value="FALSE">FALSE</option></select></span>&nbsp;</span><button id="close-parent-' . $parentkey . '" onclick="return condition_remove(event,this,' . $parentkey . ',' . $childkey . ')" class="close-tb" style="display:inline-block;">&#10006;</button></p><button id="addbutton-' . $parentkey . '" class="plus" onclick="return add_rule(event,' . $parentkey . ',' . $childkey . ')">+</button><div class="static-select-' . $parentkey . '"></div>';
															$str.= '<ul class="ul_li_' . $parentkey . '_child_' . $childkey . '">';
															} else if ('value' != $key) {
																if ('sale-operator' === $key) {

																	if ('is' == $value) {
																		$sale_operator = $value;
																		$sale_is = 'selected = selected';
																	} else if ('is not' == $value) {
																		$sale_operator = $value;
																		$sale_is_not = 'selected = selected';
																	}
																	$str.='<li id = "li-close-sale-' . $parentkey . '-' . $childkey . '" class="sale" ><label class="conditions">
																		<p>Sale <span class="rule-param"><a href="javascript:void(0)" id="sale_is-' . $parentkey . '-' . $childkey . '" onclick="return sale_is(' . $parentkey . ',' . $childkey . ')">' . $sale_operator . '</a>
																			<span class="element">
																				<select name = "rule[conditions][' . $parentkey . '][' . $childkey . '][sale-operator]" id="select_sale_is-' . $parentkey . '-' . $childkey . '"  onchange="getval2(this,' . $parentkey . ',' . $childkey . ');" style="display: none;">
																					<option ' . $sale_is . ' value="is">is</option>
																					<option ' . $sale_is_not . ' value="is not">is not</option>
																				</select>
																			</span></span>';
																}  
																if ('sale-value' === $key ) {

																	if ('No' == $value) {
																		$sale_value = $value;
																		$sale_no = 'selected = selected';
																	} else if ('Yes' == $value) {
																		$sale_value = $value;
																		$sale_yes = 'selected = selected';
																	}

																	$str.='<span class="rule-param"><a href="javascript:void(0)" id="sale_yes-' . $parentkey . '-' . $childkey . '" onclick="return sale_yes(' . $parentkey . ',' . $childkey . ')">' . $sale_value . '</a>
																				<span class="element">
																					<select name ="rule[conditions][' . $parentkey . '][' . $childkey . '][sale-value]" id="select_sale_yes-' . $parentkey . '-' . $childkey . '"  onchange="getval3(this,' . $parentkey . ',' . $childkey . ');" style="display: none;">
																						<option ' . $sale_no . ' value="No">No</option>
																						<option ' . $sale_yes . ' value="Yes">Yes</option>
																					</select>
																				</span></span> 
																			</p>
														                </label>
														                <div class="control">
														                	<button class="Sale close-tb" id="close-sale-' . $parentkey . '-' . $childkey . '" onclick="return btn_remove(event,this,' . $parentkey . ',' . $childkey . ');" style="display: inline-block;">&#10006;</button>
														                </div>
														        	</li>';

																}
																if ('weight-operator' === $key) {
																	if ('is' == $value) {
																		$weight_operator = $value;
																		$weight_is = 'selected = selected';
																	} else if ('is not' == $value) {
																		$weight_operator = $value;
																		$weight_is_not = 'selected = selected';
																	} else if ('equals or greater than' == $value) {
																		$weight_operator = $value;
																		$weight_equal_greater = 'selected = selected';
																	} else if ('equals or less than' == $value) {
																		$weight_operator = $value;
																		$weight_equal_less = 'selected = selected';
																	} else if ('greater than' == $value) {
																		$weight_operator = $value;
																		$weight_greater_than = 'selected = selected';
																	} else if ('less than' == $value) {
																		$weight_operator = $value;
																		$weight_less_than = 'selected = selected';
																	}
																	$str.='<li  id = "li-close-weight-' . $parentkey . '-' . $childkey . '" class="weight" >
																		<label class="conditions">
																		<p>Weight <span class="rule-param"><a href="javascript:void(0)" id="weight_is-' . $parentkey . '-' . $childkey . '" onclick="return weight_is(' . $parentkey . ',' . $childkey . ')">' . $weight_operator . '</a>
																		<span class="element">
																		<select name="rule[conditions][' . $parentkey . '][' . $childkey . '][weight-operator]" id="select_weight_is-' . $parentkey . '-' . $childkey . '" onchange="getval12(this,' . $parentkey . ',' . $childkey . ');" style="display: none;">
																			<option ' . $weight_is . ' value="is">is</option>
																			<option ' . $weight_is_not . ' value="is not">is not</option>
																			<option ' . $weight_equal_greater . ' value="equals or greater than">equals or greater than</option>
																			<option ' . $weight_equal_less . ' value="equals or less than">equals or less than</option>
																			<option ' . $weight_greater_than . ' value="greater than">greater than</option>
																			<option ' . $weight_less_than . ' value="less than">less than</option>
																							</select>
																						</span>
																					</span>';
																}
																if ('weight-value' === $key) {
																	$str.='<span class="rule-param"><a href="javascript:void(0)" id="weight_yes-' . $parentkey . '-' . $childkey . '" onclick="return weight_yes(' . $parentkey . ',' . $childkey . ')">' . $value . '</a>
																			<input type="text" name="rule[conditions][' . $parentkey . '][' . $childkey . '][weight-value]" id="weight_input_id-' . $parentkey . '-' . $childkey . '" value="' . $value . '" onkeypress="return checkItInput(event,false)" style="display: none;">
																				</span> 
																			</p>
										                                </label>
										                                <div class="control">
										                                	<button class="apply-tb" id="apply_weight-' . $parentkey . '-' . $childkey . '" onclick="return apply_weight(' . $parentkey . ',' . $childkey . ')" style="display:none;">&#10004;</button>
										                                	<button id="close-weight-' . $parentkey . '-' . $childkey . '" onclick="return btn_remove(event,this,' . $parentkey . ',' . $childkey . ');" class="close-tb" style="display: inline-block;">&#10006;</button>
										                                </div>
										                        	</li>';

																}
																if ('special_price-operator' === $key) {
																	if ('is' == $value) {
																		$price_operator = $value;
																		$price_is = 'selected = selected';
																	} else if ('is not' == $value) {
																		$price_operator = $value;
																		$price_is_not = 'selected = selected';
																	} else if ('equals or greater than' == $value) {
																		$price_operator = $value;
																		$price_equal_greater = 'selected = selected';
																	} else if ('equals or less than' == $value) {
																		$price_operator = $value;
																		$price_equal_less = 'selected = selected';
																	} else if ('greater than' == $value) {
																		$price_operator = $value;
																		$price_greater_than = 'selected = selected';
																	} else if ('less than' == $value) {
																		$price_operator = $value;
																		$price_less_than = 'selected = selected';
																	}
																	$str.='<li id = "li-close_price-' . $parentkey . '-' . $childkey . '" class="special_price">
																	    <label class="conditions">
																			<p>Special Price <span class="rule-param"><a href="javascript:void(0)" id="special_price_is-' . $parentkey . '-' . $childkey . '" onclick="return special_price_is(' . $parentkey . ',' . $childkey . ')">' . $price_operator . '</a>
																					<span class="element">
																						<select name = "rule[conditions][' . $parentkey . '][' . $childkey . '][special_price-operator]" id="select_special_price_is-' . $parentkey . '-' . $childkey . '" onchange="getval16(this,' . $parentkey . ',' . $childkey . ');" style="display: none;">
																							<option ' . $price_is . ' value="is">is</option>
																							<option ' . $price_is_not . ' value="is not">is not</option>
																							<option ' . $price_equal_greater . ' value="equals or greater than">equals or greater than</option>
																							<option ' . $price_equal_less . ' value="equals or less than">equals or less than</option>
																							<option ' . $price_greater_than . ' value="greater than">greater than</option>
																							<option ' . $price_less_than . ' value="less than">less than</option>
																						</select>
																					</span>
																				</span>';
																}
																if ('special_price-value' === $key) {

																	$str.='<span class="rule-param"><a href="javascript:void(0)" id="special_price_yes-' . $parentkey . '-' . $childkey . '" onclick="return special_price_yes(' . $parentkey . ',' . $childkey . ')">' . $value . '</a>
																				<input type="text" name="rule[conditions][' . $parentkey . '][' . $childkey . '][special_price-value]" id="price_input_id-' . $parentkey . '-' . $childkey . '" value="' . $value . '" onkeypress="return checkIt(event,false)" style="display: none;" >
																				</span> 
																			</p>
										                                </label>
										                                <div class="control">
										                                	<button class="apply-tb" id="apply_price-' . $parentkey . '-' . $childkey . '" onclick="return apply_price(' . $parentkey . ',' . $childkey . ')" style="display:none;">&#10004;</button>
										                                	<button id="close_price-' . $parentkey . '-' . $childkey . '" onclick="return btn_remove(event,this,' . $parentkey . ',' . $childkey . ');" class="close-tb" style="display: inline-block;">&#10006;</button>
										                                </div>
										                        	</li>';
																}
																if ('stock_range-operator' === $key) {
																	if ('equal to' == $value) {
																		$range_operator = $value;
																		$range_equal = 'selected = selected';
																	} else if ('is not' == $value) {
																		$range_operator = $value;
																		$range_is_not = 'selected = selected';
																	} else if ('more than' == $value) {
																		$range_operator = $value;
																		$range_equal_greater = 'selected = selected';
																	} else if ('equal or greater than' == $value) {
																		$range_operator = $value;
																		$range_more_than = 'selected = selected';
																	} else if ('less than' == $value) {
																		$range_operator = $value;
																		$range_less_than = 'selected = selected';
																	}
																	$str.= '<li id = "li-close-range-' . $parentkey . '-' . $childkey . '" class="stock_range">
										                                <label class="conditions">
																			<p>Stock Range is <span class="rule-param"><a href="javascript:void(0)" id="stock_range_is-' . $parentkey . '-' . $childkey . '" onclick="return stock_range_is(' . $parentkey . ',' . $childkey . ')">' . $range_operator . '</a>
																					<select name ="rule[conditions][' . $parentkey . '][' . $childkey . '][stock_range-operator]" id="select_stock_range_is-' . $parentkey . '-' . $childkey . '" onchange="getval18(this,' . $parentkey . ',' . $childkey . ');" style="display: none;">
																						<option ' . $range_equal . ' value="equal to">equal to</option>
																						<option ' . $range_is_not . ' value="is not">is not</option>
																						<option ' . $range_equal_greater . ' value="more than">more than</option>
																						<option ' . $range_more_than . ' value="equal or greater than">equal or greater than</option>
																						<option ' . $range_less_than . ' value="less than">less than</option>
																					</select>
																				</span>';
																}
																if ('stock_range-value' === $key) {
																	$str.= '<span class="rule-param"><a href="javascript:void(0)" id="stock_range_yes-' . $parentkey . '-' . $childkey . '" onclick="return stock_range_yes(' . $parentkey . ',' . $childkey . ')">' . $value . '</a>
																			<input type="text" name="rule[conditions][' . $parentkey . '][' . $childkey . '][stock_range-value]" id="range_input_id-' . $parentkey . '-' . $childkey . '" value="' . $value . '" onkeypress="return checkIt(event,false)" style="display: none;">
																		</span> 
																		</p>
										                                </label>
										                                <div class="control">
										                                	<button class="apply-tb" id="apply_range-' . $parentkey . '-' . $childkey . '" onclick="return apply_range(' . $parentkey . ',' . $childkey . ')"  style="display:none;">&#10004;</button>
										                                	<button id="close-range-' . $parentkey . '-' . $childkey . '" onclick="return btn_remove(event,this,' . $parentkey . ',' . $childkey . ');" class="close-tb" style="display: inline-block;">&#10006;</button>
										                                </div>
										                        	</li>';
																}
																if ('dimensions-operator' === $key) {
																	if ('is' == $value) {
																		$dimensions_operator = $value;
																		$dimensions_is = 'selected = selected';
																	} else if ('is not' == $value) {
																		$dimensions_operator = $value;
																		$dimensions_is_not = 'selected = selected';
																	} else if ('equals or greater than' == $value) {
																		$dimensions_operator = $value;
																		$dimensions_equal_greater = 'selected = selected';
																	} else if ('equals or less than' == $value) {
																		$dimensions_operator = $value;
																		$dimensions_equal_less = 'selected = selected';
																	} else if ('greater than' == $value) {
																		$dimensions_operator = $value;
																		$dimensions_greater_than = 'selected = selected';
																	} else if ('less than' == $value) {
																		$dimensions_operator = $value;
																		$dimensions_less_than = 'selected = selected';
																	}
																	$str.='<li id = "li-close-dimensions-' . $parentkey . '-' . $childkey . '" class="dimensions">
										                            	<label class="conditions">
																			<p>Dimensions <span class="rule-param"><a href="javascript:void(0)" id="dimensions_is-' . $parentkey . '-' . $childkey . '" onclick="return dimensions_is(' . $parentkey . ',' . $childkey . ')">' . $dimensions_operator . '</a>
																		<select name = "rule[conditions][' . $parentkey . '][' . $childkey . '][dimensions-operator]" id="select_dimensions_is-' . $parentkey . '-' . $childkey . '" onchange="getval14(this,' . $parentkey . ',' . $childkey . ');" style="display: none;">
																			<option ' . $dimensions_is . ' value="is">is</option>
																			<option ' . $dimensions_is_not . ' value="is not">is not</option>
																			<option ' . $dimensions_equal_greater . ' value="equals or greater than">equals or greater than</option>
																			<option ' . $dimensions_equal_less . ' value="equals or less than">equals or less than</option>
																			<option ' . $dimensions_greater_than . ' value="greater than">greater than</option>
																			<option ' . $dimensions_less_than . ' value="less than">less than</option>
																		</select>	
																	</span>';
																}
																if ('length-value'  === $key) {
																	$str.='length is<span class="rule-param"><a href="javascript:void(0)" id="dimensions_length-' . $parentkey . '-' . $childkey . '" onclick="return dimensions_length(' . $parentkey . ',' . $childkey . ')">' . $value . '</a>
																		<input type="text" name="rule[conditions][' . $parentkey . '][' . $childkey . '][length-value]" id="dimlength_input_id-' . $parentkey . '-' . $childkey . '" value="' . $value . '" onkeypress="return checkIt(event,false)" style="display: none;">
																	</span>';
																}
																if ('width-value'  === $key) {  
																	$str.='width is<span class="rule-param"><a href="javascript:void(0)" id="dimensions_width-' . $parentkey . '-' . $childkey . '" onclick="return dimensions_width(' . $parentkey . ',' . $childkey . ')">' . $value . '</a>
																		<input type="text" name="rule[conditions][' . $parentkey . '][' . $childkey . '][width-value]" id="dimwidth_input_id-' . $parentkey . '-' . $childkey . '" value="' . $value . '" onkeypress="return checkIt(event,false)" style="display: none;">				
																	</span>';
																}
																if ('height-value'  === $key) {  
																	$str.='height is<span class="rule-param"><a href="javascript:void(0)" id="dimensions_height-' . $parentkey . '-' . $childkey . '" onclick="return dimensions_height(' . $parentkey . ',' . $childkey . ')">' . $value . '</a>
																		<input type="text" name="rule[conditions][' . $parentkey . '][' . $childkey . '][height-value]" id="dimheight_input_id-' . $parentkey . '-' . $childkey . '" value="' . $value . '" onkeypress="return checkIt(event,false)" style="display: none;">
																	</span> 
																	</p>
											                        <div class="control">
											                        	<button class="apply-tb" id="apply_length-' . $parentkey . '-' . $childkey . '"  onclick="return apply_length(' . $parentkey . ',' . $childkey . ')" style="display:none;">&#10004;</button>
											                        	<button class="apply-tb" id="apply_width-' . $parentkey . '-' . $childkey . '" onclick="return apply_width(' . $parentkey . ',' . $childkey . ')" style="display:none;">&#10004;</button>
											                        	<button class="apply-tb" id="apply_height-' . $parentkey . '-' . $childkey . '" onclick="return apply_height(' . $parentkey . ',' . $childkey . ')" style="display:none;">&#10004;</button>
											                        	<button id="close-dimensions-' . $parentkey . '-' . $childkey . '" onclick="return btn_remove(event,this,' . $parentkey . ',' . $childkey . ');" class="close-tb" style="display: inline-block;">&#10006;</button>
											                        </div>
											                	</li>';
																}
																if ('sku-operator' === $key) {
																	if ('is' == $value) {
																		$sku_operator = $value;
																		$sku_is = 'selected = selected';
																	} else if ('is not' == $value) {
																		$sku_operator = $value;
																		$sku_is_not = 'selected = selected';
																	} else if ('contains' == $value) {
																		$sku_operator = $value;
																		$sku_contains = 'selected = selected';
																	} else if ('does not contain' == $value) {
																		$sku_operator = $value;
																		$sku_not_contains = 'selected = selected';
																	} else if ('is one of' == $value) {
																		$sku_operator = $value;
																		$sku_one = 'selected = selected';
																	} else if ('is not one of' == $value) {
																		$sku_operator = $value;
																		$sku_not_one = 'selected = selected';
																	}
																	$str.='<li id = "li-close-sku-' . $parentkey . '-' . $childkey . '" class="sku">
										                                <label class="conditions">
																		<p>SKU <span class="rule-param"><a href="javascript:void(0)" id="sku_is-' . $parentkey . '-' . $childkey . '" onclick="return sku_is(' . $parentkey . ',' . $childkey . ')">' . $sku_operator . '</a>
																				<select name ="rule[conditions][' . $parentkey . '][' . $childkey . '][sku-operator]" id="select_sku_is-' . $parentkey . '-' . $childkey . '" onchange="getval10(this,' . $parentkey . ',' . $childkey . ');" style="display: none;">
																					<option ' . $sku_is . ' value="is">is</option>
																					<option ' . $sku_is_not . ' value="is not">is not</option>
																					<option ' . $sku_contains . ' value="contains">contains</option>
																					<option ' . $sku_not_contains . ' value="does not contain">does not contain</option>
																					<option ' . $sku_one . ' value="is one of">is one of</option>
																					<option ' . $sku_not_one . ' value="is not one of">is not one of</option>
																				</select>
																			</span>';
																}
																if ('sku-value'  === $key) { 
																	$str.='<span class="rule-param"><a href="javascript:void(0)" id="sku_yes-' . $parentkey . '-' . $childkey . '" onclick="return sku_yes(' . $parentkey . ',' . $childkey . ')">' . $value . '</a>
																			<input type="text" name="rule[conditions][' . $parentkey . '][' . $childkey . '][sku-value]" id="sku_input_id-' . $parentkey . '-' . $childkey . '" value="' . $value . '" onkeypress="return checkNum(event,false)" style="display: none;">
																			<input type="button" id="sku_div-' . $parentkey . '-' . $childkey . '" class=" button btn_addnewproduct" onclick="plcallpopup(16573,' . $parentkey . ',' . $childkey . ')" data-value="16573,' . $parentkey . ',' . $childkey . '" name="" value="Add New Product" style="display:none;">
																			<input type="hidden" id="listchecked-' . $parentkey . '-' . $childkey . '-16573" value="' . $value . '">
																		</span> 
																		</p>
									                                	</label>
										                                <div class="control">
										                                	<button  class="apply-tb" id="apply_sku-' . $parentkey . '-' . $childkey . '" onclick="return apply_sku(' . $parentkey . ',' . $childkey . ')" style="display:none;">&#10004;</button>
										                                	<button  id="close-sku-' . $parentkey . '-' . $childkey . '" style="display:inline-block;"  onclick="return btn_remove(event,this,' . $parentkey . ',' . $childkey . ');" class="close-tb" style="display: inline-block;">&#10006;</button>
										                                </div>
										                        	</li>';
																}
																if ('category-operator' === $key) {
																	if ('is' == $value) {
																		$category_operator = $value;
																		$category_is = 'selected = selected';
																	} else if ('is not' == $value) {
																		$category_operator = $value;
																		$category_is_not = 'selected = selected';
																	} else if ('contains' == $value) {
																		$category_operator = $value;
																		$category_contains = 'selected = selected';
																	} else if ('does not contain' == $value) {
																		$category_operator = $value;
																		$category_not_contains = 'selected = selected';
																	} else if ('is one of' == $value) {
																		$category_operator = $value;
																		$category_one = 'selected = selected';
																	} else if ('is not one of' == $value) {
																		$category_operator = $value;
																		$category_not_one = 'selected = selected';
																	}
																	$str.='<li id = "li-close-category-' . $parentkey . '-' . $childkey . '" class="category">
									                                <label class="conditions">
																		<p>Category <span class="rule-param"><a href="javascript:void(0)" id="category_is-' . $parentkey . '-' . $childkey . '" onclick="return category_is(' . $parentkey . ',' . $childkey . ')" >' . $category_operator . '</a>
																			<span class="element">
																				<select name= "rule[conditions][' . $parentkey . '][' . $childkey . '][category-operator]" id="select_category_is-' . $parentkey . '-' . $childkey . '" onchange="getval6(this,' . $parentkey . ',' . $childkey . ');" style="display: none;">
																					<option ' . $category_is . ' value="is">is</option>
																					<option ' . $category_is_not . ' value="is not">is not</option>
																					<option ' . $category_contains . ' value="contains">contains</option>
																					<option ' . $category_not_contains . ' value="does not contain">does not contain</option>
																					<option ' . $category_one . ' value="is one of">is one of</option>
																					<option ' . $category_not_one . ' value="is not one of">is not one of</option>
																				</select>
																			</span>
																		</span>';
																}
																if ('category-value' === $key) {
																	$chckedvalue = array();
																	if (!empty($value)) {
																		$chckedvalue = sanitize_text_field($value);
																		$chckedvalue = explode(',', $chckedvalue);
																	}
																	$str.='<span class="rule-param"><a href="javascript:void(0)" id="category_yes-' . $parentkey . '-' . $childkey . '" onclick="return category_yes(' . $parentkey . ',' . $childkey . ')">' . $value . '</a>
																	<input type="text" name="rule[conditions][' . $parentkey . '][' . $childkey . '][category-value]" id="cat_input_id-' . $parentkey . '-' . $childkey . '" value="' . $value . '" onkeypress="return checkItInput(event,false)" style="display: none;">
																	<div class="multiselect" id="select_category-' . $parentkey . '-' . $childkey . '" style="display: none;">
																		<div class="selectBox" onclick="showCheckboxes(' . $parentkey . ',' . $childkey . ')">
																			<select>
																				<option>Select Category</option>
																			</select>
																			<div class="overSelect"></div>
																		</div>
																		<div class = "checkboxes" id="checkboxes-' . $parentkey . '-' . $childkey . '" onclick="checkboxes(' . $parentkey . ',' . $childkey . ')">';
																			
																			$taxonomy     = 'product_cat';
																			$orderby      = 'name';  
																			$show_count   = 0;
																			$pad_counts   = 0;
																			$hierarchical = 1;  
																			$title        = '';  
																			$empty        = 0;
																			
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
																			foreach ($all_categories as $cat) {
																				$checkedid = '';
																				if (0 == $cat->category_parent ) {
																					$category_id = $cat->term_id;  
																					$category_name = $cat->name; 

																					if (in_array($category_id, $chckedvalue)) {
																						$checkedid = 'checked';
																					}

																					$str.="<ul><label for='" . $category_id . "'><input type='checkbox' " . $checkedid . " value='" . $category_id . "' name ='category' />" . $category_name . '</label>';
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
																					$sub_category = get_categories( $args2 );
																					if ($sub_category) {
																						$subcheckedid = '';
																						foreach ($sub_category as $sub_cats) {
																							$child_id = $sub_cats->term_id; 
																							$child_cat = $sub_cats->name ; 

																							if (in_array($child_id, $chckedvalue)) {
																								$subcheckedid = 'checked';
																							} 
																							
																							$str.="<li><label for='" . $child_id . "'><input type='checkbox' " . $subcheckedid . " value='" . $child_id . "' name ='category' />→" . $child_cat . '</label></li>';
																						}   
																					}
																					$str.='</ul>';
																				}       
																			}';	';
																}
																if ('category-value' === $key) {
																	$str.='</div>
																	</div>
																	</span> 
																	</p>
															    	</label><div class="control">
															        	<button id="cat_div-' . $parentkey . '-' . $childkey . '" onclick="return category_div(' . $parentkey . ',' . $childkey . ')" style="display:none;">[ ]</button>
															        	<button class="apply-tb" id="apply_category-' . $parentkey . '-' . $childkey . '" onclick="return apply_category(' . $parentkey . ',' . $childkey . ')" style="display:none;">&#10004;</button>
															        	<button  id="close-category-' . $parentkey . '-' . $childkey . '" style="display:inline-block;" onclick="return btn_remove(event,this,' . $parentkey . ',' . $childkey . ');" class="close-tb">&#10006;</button>
															        </div>
																	</li>';
																}
																if ('sale-operator' != $key && 'sale-value'!= $key  && 'weight-operator' != $key && 'weight-value' != $key  && 'special_price-operator' != $key &&  'special_price-value' != $key &&  'stock_range-operator' !=  $key && 'stock_range-value' != $key && 'dimensions-operator' != $key &&  'length-value' != $key && 'width-value' != $key && 'height-value'!= $key && 'sku-operator'!= $key && 'sku-value' != $key && 'category-operator' != $key && 'category-value' != $key) {
																	
																	if ('is' == $value) {
																		$attr_operator = $value;
																		$attr_is = 'selected = selected';
																	} else if ('is not' == $value) {
																		$attr_operator = $value;
																		$attr_is_not = 'selected = selected';
																	}
																	$subkey = substr($key, 0, strpos($key, '-'));
																	if ($key === $subkey . '-operator') {
																		$str.='<li id = "li-attribute-' . $parentkey . '-' . $childkey . '" class="' . $subkey . '" ><label class="conditions">
																			<p>' . $subkey . ' <span class="rule-param"><a href="javascript:void(0)" id="' . $subkey . '_is-' . $parentkey . '-' . $childkey . '" onclick="return attribute_operator(' . $parentkey . ',' . $childkey . ',\'' . $subkey . '\')">' . $attr_operator . '</a>
																				<span class="element">
																					<select name = "rule[conditions][' . $parentkey . '][' . $childkey . '][' . $subkey . '-operator]" id="select_' . $subkey . '_is-' . $parentkey . '-' . $childkey . '" onchange="getvalIs(this,' . $parentkey . ',' . $childkey . ',\'' . $subkey . '\');" style="display: none;">
																						<option ' . $attr_is . ' value="is">is</option>
																						<option ' . $attr_is_not . ' value="is not">is not</option>
																					</select>
																				</span></span>';
																	}
																	if ($key === $subkey . '-value') {
																		$str.='<span class="rule-param"><a href="javascript:void(0)" id="' . $subkey . '_yes-' . $parentkey . '-' . $childkey . '"  onclick="return attribute_value(' . $parentkey . ',' . $childkey . ',\'' . $subkey . '\')">' . $value . '</a><select name = "rule[conditions][' . $parentkey . '][' . $childkey . '][' . $subkey . '-value]" id="select_' . $subkey . '-' . $parentkey . '-' . $childkey . '" onchange="getvalDynamic(this,' . $parentkey . ',' . $childkey . ',\'' . $subkey . '\');" style="display: none;" ><option value="' . $value . '">' . $value . '</option></select><button id="attribute-' . $parentkey . '-' . $childkey . '" style="display:inline-block;" onclick="return btn_remove(event,this,' . $parentkey . ',' . $childkey . ');" class="close-tb">&#10006;</button>';
																	}
																}
															//$str.= '</ul>';
															}
															if (end($innerarray) == $value) {
																$str.= '</ul>';
															}

														}
													}
												}
												$str.= '</li></ol>';
												echo wp_kses($str, wp_kses_allowed_html('post'));
												//echo $str;
											} else {
												?>
												<ol class="ul_li_0_0" id="level-0-0">
													<li>
														<p>If <span class="rule-param"><a href="javascript:void(0)" id="label_all-0-0" onclick="return label_all(0,0)">ALL</a>
															<span class="element">
																<select  name = "rule[conditions][0][0][aggregator]" id="select_label_all-0-0" onchange="getval(this,0,0);" style="display: none;">
																	<option selected="selected" value="ALL">ALL</option>
																	<option value="ANY">ANY</option>
																</select>
															</span>
														</span>&nbsp;of these conditions are <span class="rule-param"><a href="javascript:void(0)" id ="label_true-0-0" onclick="return label_true(0,0)">TRUE</a>
															<span class="element">
																<select name = "rule[conditions][0][0][value]" id="select_label_true-0-0" onchange="getval1(this,0,0);" style="display: none;">
																	<option selected="selected" value="TRUE">TRUE</option>
																	<option value="FALSE">FALSE</option>
																</select>
															</span>&nbsp;:
														</span>
														
														<ul class="ul_li_0_child_0" id ="level-0-child-0">
															<li id = "li-attribute-0-1" class="attribute-0-1" style="display:none;">
															   </li>
														</ul>
													</li>
													
													<button class="plus" onclick="return add_rule(event,0,0)">+</button>
												</ol>
											<?php } ?>

										</div>
										<div id="initial_of_select">
											<select name="attribute_taxonomy" class="attribute_taxonomy" id="myDIV" style="display: none;" data-value="0" onchange="selectoptionval(this,0,0);">
												<option value="" selected="selected">Please choose a condition to add.</option>
												<option value="conditions_combination" data-value="parent">Conditions Combination</option>
												<optgroup label="Product Attributes">
													<?php
													global $wc_product_attributes;
													$attribute_taxonomies = wc_get_attribute_taxonomies();
													if ( ! empty( $attribute_taxonomies ) ) {
														foreach ( $attribute_taxonomies as $tax ) {
															$attribute_taxonomy_name = wc_attribute_taxonomy_name( $tax->attribute_name );
															$label                   = $tax->attribute_label ? $tax->attribute_label : $tax->attribute_name;
															$attr_value =  strtolower($label);
															echo '<option value="' . esc_attr( $attr_value) . '" data-value="child">' . esc_html( $label ) . '</option>';
														}
													}
													?>
													<option value="category" data-value="child">Category</option>
													<option value="sku" data-value="child">SKU</option>
													<option value="sale" data-value="child">Sale</option>
													<option value="special_price" data-value="child">Special Price</option>
													<option value="weight" data-value="child">Weight (kg)</option>
													<option value="dimensions" data-value="child">Dimensions</option>
												</optgroup>
												<optgroup label="Product Special">
													<option value="stock_range" data-value="child">Stock Range</option>
												</optgroup>
											</select>
										</div>
										<div class="static-select-0">
										</div>

									</div>
									<div class="submit">
										<?php 
										if (isset($_GET['id']) && !empty($_GET['id'])) {
											$id = sanitize_text_field($_GET['id']);
											?>
											<input name="rule_id" type="hidden" id= "<?php echo wp_kses($id, wp_kses_allowed_html('post')); ?>" value="<?php echo wp_kses($id, wp_kses_allowed_html('post')); ?>"><input type="submit" class="button button-primary" value="<?php echo wp_kses('Update', wp_kses_allowed_html('post')); ?>" name="setting_rule_submit" onclick="return rule_saved(event)"/>
											<?php
										} else {
;
											?>
															
											<input type="submit" class="button button-primary" value="<?php echo wp_kses('Save', wp_kses_allowed_html('post')); ?>" name="setting_rule_submit" onclick="return rule_saved(event)"/><?php } ?>	
									</div>
								</div>
							</form>
							<!-- pppppp -->
							
							<li id="li-close-sale-0-1" class="sale" style="display: none;">
								<label class="conditions">
									<p>Sale <span class="rule-param"><a href="javascript:void(0)" id="sale_is-0-1" onclick="return sale_is(0,1)">is</a>
										<span class="element">
											<select name = "rule[conditions][0][1][sale-operator]" id="select_sale_is-0-1" onchange="getval2(this,0,1);" style="display: none;">
												<option value="is" selected="selected">is</option>
												<option value="is not">is not</option>
											</select>
										</span></span>
										<span class="rule-param"><a href="javascript:void(0)" id="sale_yes-0-1" onclick="return sale_yes(0,1)">....</a>
										<span class="element">
											<select name ="rule[conditions][0][1][sale-value]" id="select_sale_yes-0-1"  onchange="getval3(this,0,1);" style="display: none;">
												<option selected="selected" value="No">No</option>
												<option value="Yes">Yes</option>
											</select>
										</span></span> 
									</p>
									
								</label>
								<div class="control">
									<button  id="close-sale-0-1"  onclick="return btn_remove(event,this,0,1);" class="close-tb" style="display: inline-block;">&#10006;</button>
								</div>
							</li>
							<li  id = "li-close-weight-0-1" class="weight" style="display: none;">
								<label class="conditions">
									<p>Weight <span class="rule-param"><a href="javascript:void(0)" id="weight_is-0-1" onclick="return weight_is(0,1)">is</a>
											<span class="element">
												<select name="rule[conditions][0][1][weight-operator]" id="select_weight_is-0-1" onchange="getval12(this,0,1);" style="display: none;">
													<option value="is" selected="selected">is</option>
													<option value="is not">is not</option>
													<option value="equals or greater than">equals or greater than</option>
													<option value="equals or less than">equals or less than</option>
													<option value="greater than">greater than</option>
													<option value="less than">less than</option>
												</select>
											</span>
										</span>
										<span class="rule-param"><a href="javascript:void(0)" id="weight_yes-0-1" onclick="return weight_yes(0,1)">....</a>
											<input type="text" name="rule[conditions][0][1][weight-value]" id="weight_input_id-0-1" value="" onkeypress="return checkItInput(event,false)" style="display: none;" >
										</span> 
									</p>
								</label>
								<div class="control">
									<button class="apply-tb" id="apply_weight-0-1" onclick="return apply_weight(0,1)" style="display:none;">&#10004;</button>
									<button id="close-weight-0-1" onclick="return btn_remove(event,this,0,1);" class="close-tb" style="display: inline-block;">&#10006;</button>
								</div>
							</li>
							<li id = "li-close_price-0-1" class="special_price" style="display: none;">
								<label class="conditions">
									<p>Special Price <span class="rule-param"><a href="javascript:void(0)" id="special_price_is-0-1" onclick="return special_price_is(0,1)">is</a>
											<span class="element">
												<select name = "rule[conditions][0][1][special_price-operator]" id="select_special_price_is-0-1" onchange="getval16(this,0,1);" style="display: none;">
													<option value="is" selected="selected">is</option>
													<option value="is not">is not</option>
													<option value="equals or greater than">equals or greater than</option>
													<option value="equals or less than">equals or less than</option>
													<option value="greater than">greater than</option>
													<option value="less than">less than</option>
												</select>
											</span>
										</span>
										<span class="rule-param"><a href="javascript:void(0)" id="special_price_yes-0-1" onclick="return special_price_yes(0,1)">....</a>
											<input type="text" name="rule[conditions][0][1][special_price-value]" id="price_input_id-0-1" value="" onkeypress="return checkIt(event,false)" style="display: none;" >
										</span> 
									</p>
								</label>
								<div class="control">
									<button class="apply-tb" id="apply_price-0-1" onclick="return apply_price(0,1)" style="display:none;">&#10004;</button>
									<button id="close_price-0-1"  onclick="return btn_remove(event,this,0,1);" class="close-tb" style="display: inline-block;">&#10006;</button>
								</div>
							</li>
							<li id = "li-close-range-0-1" class="stock_range" style="display: none;">
								<label class="conditions">
									<p>Stock Range is <span class="rule-param"><a href="javascript:void(0)" id="stock_range_is-0-1" onclick="return stock_range_is(0,1)">equal to</a>
											<select name ="rule[conditions][0][1][stock_range-operator]" id="select_stock_range_is-0-1" onchange="getval18(this,0,1); " style="display: none;">
												<option value="equal to" selected="selected">equal to</option>
												<option value="is not">is not</option>
												<option value="more than">more than</option>
												<option value="equal or greater than">equal or greater than</option>
												<option value="less than">less than</option>
											</select>
										</span>
										<span class="rule-param"><a href="javascript:void(0)" id="stock_range_yes-0-1" onclick="return stock_range_yes(0,1)">....</a>
											<input type="text" name="rule[conditions][0][1][stock_range-value]" id="range_input_id-0-1" value="" onkeypress="return checkIt(event,false)" style="display: none;">
										</span> 
									</p>
								</label>
								<div class="control">
									<button class="apply-tb" id="apply_range-0-1" onclick="return apply_range(0,1)"  style="display:none;">&#10004;</button>
									<button id="close-range-0-1" onclick="return btn_remove(event,this,0,1);" class="close-tb" style="display: inline-block;">&#10006;</button>
								</div>
							</li>
							<li id = "li-close-dimensions-0-1" class="dimensions" style="display: none;">
								<label class="conditions">
									<p>Dimensions <span class="rule-param"><a href="javascript:void(0)" id="dimensions_is-0-1" onclick="return dimensions_is(0,1)">is</a>
											<select name = "rule[conditions][0][1][dimensions-operator]" id="select_dimensions_is-0-1" onchange="getval14(this,0,1); "style="display: none;">
												<option value="is" selected="selected">is</option>
												<option value="is not">is not</option>
												<option value="equals or greater than">equals or greater than</option>
												<option value="equals or less than">equals or less than</option>
												<option value="greater than">greater than</option>
												<option value="less than">less than</option>
											</select>	
										</span>length is
										<span class="rule-param"><a href="javascript:void(0)" id="dimensions_length-0-1" onclick="return dimensions_length(0,1)">.....</a>
											<input type="text" name="rule[conditions][0][1][length-value]" id="dimlength_input_id-0-1" value="" onkeypress="return checkIt(event,false)" style="display: none;">
										</span> width is 
										<span class="rule-param"><a href="javascript:void(0)" id="dimensions_width-0-1" onclick="return dimensions_width(0,1)">....</a>
											<input type="text" name="rule[conditions][0][1][width-value]" id="dimwidth_input_id-0-1" value="" onkeypress="return checkIt(event,false)" style="display: none;">				
										</span> height is 
										<span class="rule-param"><a href="javascript:void(0)" id="dimensions_height-0-1" onclick="return dimensions_height(0,1)">....</a>
											<input type="text" name="rule[conditions][0][1][height-value]" id="dimheight_input_id-0-1" value="" onkeypress="return checkIt(event,false)"  style="display: none;">
										</span> 
									</p>
								
								<div class="control">
									<button class="apply-tb" id="apply_length-0-1"  onclick="return apply_length(0,1)"style="display:none;">&#10004;</button>
									<button class="apply-tb" id="apply_width-0-1" onclick="return apply_width(0,1)" style="display:none;">&#10004;</button>
									<button class="apply-tb" id="apply_height-0-1" onclick="return apply_height(0,1)" style="display:none;">&#10004;</button>
									<button id="close-dimensions-0-1" onclick="return btn_remove(event,this,0,1);" class="close-tb" style="display: inline-block;">&#10006;</button>
								</div>
							</li>
							<li id = "li-close-sku-0-1" class="sku" style="display: none;">
								<label class="conditions">
									<p>SKU <span class="rule-param"><a href="javascript:void(0)" id="sku_is-0-1" onclick="return sku_is(0,1)">is</a>
											<select name ="rule[conditions][0][1][sku-operator]" id="select_sku_is-0-1" onchange="getval10(this,0,1);" style="display: none;">
												<option value="is" selected="selected">is</option>
												<option value="is not">is not</option>
												<option value="contains">contains</option>
												<option value="does not contain">does not contain</option>
												<option value="is one of">is one of</option>
												<option value="is not one of">is not one of</option>
											</select>
										</span>
										<span class="rule-param"><a href="javascript:void(0)" id="sku_yes-0-1" onclick="return sku_yes(0,1)">....</a>
											<input type="text" name="rule[conditions][0][1][sku-value]" id="sku_input_id-0-1" value="" onkeypress="return checkNum(event,false)" style="display: none;">
											<input type="button" id="sku_div-0-1" class=" button btn_addnewproduct" onclick="plcallpopup(16573,0,1)" data-value="16573,0,1" name="" value="Add New Product" style="display:none;">
											<input type="hidden" id="listchecked-0-1-16573" value="">
										</span> 
									</p>
								</label>
								<div class="control">
									<button class="apply-tb" id="apply_sku-0-1" onclick="return apply_sku(0,1)" style="display:none;">&#10004;</button>
									<button  id="close-sku-0-1" style="display:inline-block;" onclick="return btn_remove(event,this,0,1);" class="close-tb" style="display: inline-block;">&#10006;</button>
								</div>
							</li>
							<li id = "li-close-category-0-1" class="category" style="display: none;">
								<label class="conditions">
									<p>Category <span class="rule-param"><a href="javascript:void(0)" id="category_is-0-1" onclick="return category_is(0,1)" >is</a>
											<span class="element">
												<select name= "rule[conditions][0][1][category-operator]" id="select_category_is-0-1" onchange="getval6(this,0,1); "style="display: none;">
													<option value="is" selected="selected">is</option>
													<option value="is not">is not</option>
													<option value="contains">contains</option>
													<option value="does not contain">does not contain</option>
													<option value="is one of">is one of</option>
													<option value="is not one of">is not one of</option>
												</select>
											</span>
										</span>
										<span class="rule-param"><a href="javascript:void(0)" id="category_yes-0-1" onclick="return category_yes(0,1)">....</a>
											<input type="text" name="rule[conditions][0][1][category-value]" id="cat_input_id-0-1" value="" onkeypress="return checkItInput(event,false)" style="display: none;">
											<div class="multiselect" id="select_category-0-1" style="display: none;">
												<div class="selectBox" onclick="showCheckboxes(0,1)">
													<select>
														<option>Select Category</option>
													</select>
													<div class="overSelect"></div>
												</div>
												<div class = "checkboxes" id="checkboxes-0-1" onclick="checkboxes(0,1)">
													<?php
													$taxonomy     = 'product_cat';
													$orderby      = 'name';  
													$show_count   = 0;
													$pad_counts   = 0;
													$hierarchical = 1;  
													$title        = '';  
													$empty        = 0;

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
													foreach ($all_categories as $cat) {
														if (0 == $cat->category_parent) {
															$category_id = $cat->term_id;  
															$category_name = $cat->name;     
															echo wp_kses("<ul><label for='" . $category_id . "'><input type='checkbox' value='" . $category_id . "' name ='category' />" . $category_name . '</label>', wp_kses_allowed_html('post'));
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
															$sub_category = get_categories( $args2 );
															if ($sub_category) {
																foreach ($sub_category as $sub_cats) {
																	$child_id = $sub_cats->term_id; 
																	$child_cat = $sub_cats->name ;  
																	
																	echo  wp_kses("<li><label for='" . $child_id . "'><input type='checkbox' value='" . $child_id . "' name ='category' />→" . $child_cat . '</label></li>', wp_kses_allowed_html('post'));
																}   
															}
															echo'</ul>';
														}       
													}
													?>

												</div>
											</div>
										</span> 
									</p>
								</label>
								<div class="control">
									<button id="cat_div-0-1" onclick="return category_div(0,1)" style="display:none;">[ ]</button>
									<button class="apply-tb" id="apply_category-0-1" onclick="return apply_category(0,1)" style="display:none;">&#10004;</button>
									<button  id="close-category-0-1" style="display:inline-block;" onclick="return btn_remove(event,this,0,1);" class="close-tb" style="display: inline-block;">&#10006;</button>
								</div>
							</li>
							<!-- -------------------------------------------- -->
							<li id="li-close-sale-0-0" class="sale" style="display: none;">
								<label class="conditions">
									<p>Sale <span class="rule-param"><a href="javascript:void(0)" id="sale_is-0-0" onclick="return sale_is(0,0)">is</a>
										<span class="element">
											<select name = "rule[conditions][0][0][sale-operator]" id="select_sale_is-0-0" onchange="getval2(this,0,0);" style="display: none;">
												<option value="is" selected="selected">is</option>
												<option value="is not">is not</option>
											</select>
										</span></span>
										<span class="rule-param"><a href="javascript:void(0)" id="sale_yes-0-0" onclick="return sale_yes(0,0)">....</a>
										<span class="element">
											<select name ="rule[conditions][0][0][sale-value]" id="select_sale_yes-0-0"  onchange="getval3(this,0,0);" style="display: none;">
												<option selected="selected" value="No">No</option>
												<option value="Yes">Yes</option>
											</select>
										</span></span> 
									</p>
									
								</label>
								<div class="control">
									<button  id="close-sale-0-0"  onclick="return btn_remove(event,this,0,0);" class="close-tb" style="display: inline-block;">&#10006;</button>
								</div>
							</li>
							<li  id = "li-close-weight-0-0" class="weight" style="display: none;">
								<label class="conditions">
									<p>Weight <span class="rule-param"><a href="javascript:void(0)" id="weight_is-0-0" onclick="return weight_is(0,0)">is</a>
											<span class="element">
												<select name="rule[conditions][0][0][weight-operator]" id="select_weight_is-0-0" onchange="getval12(this,0,0);" style="display: none;">
													<option value="is" selected="selected">is</option>
													<option value="is not">is not</option>
													<option value="equals or greater than">equals or greater than</option>
													<option value="equals or less than">equals or less than</option>
													<option value="greater than">greater than</option>
													<option value="less than">less than</option>
												</select>
											</span>
										</span>
										<span class="rule-param"><a href="javascript:void(0)" id="weight_yes-0-0" onclick="return weight_yes(0,0)">....</a>
											<input type="text" name="rule[conditions][0][0][weight-value]" id="weight_input_id-0-0" value="" onkeypress="return checkItInput(event,false)" style="display: none;" >
										</span> 
									</p>
								</label>
								<div class="control">
									<button class="apply-tb" id="apply_weight-0-0" onclick="return apply_weight(0,0)" style="display:none;">&#10004;</button>
									<button id="close-weight-0-0" onclick="return btn_remove(event,this,0,0);" class="close-tb" style="display: inline-block;">&#10006;</button>
								</div>
							</li>
							<li id = "li-close_price-0-0" class="special_price" style="display: none;">
								<label class="conditions">
									<p>Special Price <span class="rule-param"><a href="javascript:void(0)" id="special_price_is-0-0" onclick="return special_price_is(0,0)">is</a>
											<span class="element">
												<select name = "rule[conditions][0][0][special_price-operator]" id="select_special_price_is-0-0" onchange="getval16(this,0,0);" style="display: none;">
													<option value="is" selected="selected">is</option>
													<option value="is not">is not</option>
													<option value="equals or greater than">equals or greater than</option>
													<option value="equals or less than">equals or less than</option>
													<option value="greater than">greater than</option>
													<option value="less than">less than</option>
												</select>
											</span>
										</span>
										<span class="rule-param"><a href="javascript:void(0)" id="special_price_yes-0-0" onclick="return special_price_yes(0,0)">....</a>
											<input type="text" name="rule[conditions][0][0][special_price-value]" id="price_input_id-0-0" value="" onkeypress="return checkIt(event,false)" style="display: none;" >
										</span> 
									</p>
								</label>
								<div class="control">
									<button class="apply-tb" id="apply_price-0-0" onclick="return apply_price(0,0)" style="display:none;">&#10004;</button>
									<button id="close_price-0-0"  onclick="return btn_remove(event,this,0,0);" class="close-tb" style="display: inline-block;">&#10006;</button>
								</div>
							</li>
							<li id = "li-close-range-0-0" class="stock_range" style="display: none;">
								<label class="conditions">
									<p>Stock Range is <span class="rule-param"><a href="javascript:void(0)" id="stock_range_is-0-0" onclick="return stock_range_is(0,0)">equal to</a>
											<select name ="rule[conditions][0][0][stock_range-operator]" id="select_stock_range_is-0-0" onchange="getval18(this,0,0); " style="display: none;">
												<option value="equal to" selected="selected">equal to</option>
												<option value="is not">is not</option>
												<option value="more than">more than</option>
												<option value="equal or greater than">equal or greater than</option>
												<option value="less than">less than</option>
											</select>
										</span>
										<span class="rule-param"><a href="javascript:void(0)" id="stock_range_yes-0-0" onclick="return stock_range_yes(0,0)">....</a>
											<input type="text" name="rule[conditions][0][0][stock_range-value]" id="range_input_id-0-0" value="" onkeypress="return checkIt(event,false)" style="display: none;">
										</span> 
									</p>
								</label>
								<div class="control">
									<button class="apply-tb" id="apply_range-0-0" onclick="return apply_range(0,0)"  style="display:none;">&#10004;</button>
									<button id="close-range-0-0" onclick="return btn_remove(event,this,0,0);" class="close-tb" style="display: inline-block;">&#10006;</button>
								</div>
							</li>
							<li id = "li-close-dimensions-0-0" class="dimensions" style="display: none;">
								<label class="conditions">
									<p>Dimensions <span class="rule-param"><a href="javascript:void(0)" id="dimensions_is-0-0" onclick="return dimensions_is(0,0)">is</a>
											<select name = "rule[conditions][0][0][dimensions-operator]" id="select_dimensions_is-0-0" onchange="getval14(this,0,0); "style="display: none;">
												<option value="is" selected="selected">is</option>
												<option value="is not">is not</option>
												<option value="equals or greater than">equals or greater than</option>
												<option value="equals or less than">equals or less than</option>
												<option value="greater than">greater than</option>
												<option value="less than">less than</option>
											</select>	
										</span>length is
										<span class="rule-param"><a href="javascript:void(0)" id="dimensions_length-0-0" onclick="return dimensions_length(0,0)">.....</a>
											<input type="text" name="rule[conditions][0][0][length-value]" id="dimlength_input_id-0-0" value="" onkeypress="return checkIt(event,false)" style="display: none;">
										</span> width is 
										<span class="rule-param"><a href="javascript:void(0)" id="dimensions_width-0-0" onclick="return dimensions_width(0,0)">....</a>
											<input type="text" name="rule[conditions][0][0][width-value]" id="dimwidth_input_id-0-0" value="" onkeypress="return checkIt(event,false)" style="display: none;">				
										</span> height is 
										<span class="rule-param"><a href="javascript:void(0)" id="dimensions_height-0-0" onclick="return dimensions_height(0,0)">....</a>
											<input type="text" name="rule[conditions][0][0][height-value]" id="dimheight_input_id-0-0" value="" onkeypress="return checkIt(event,false)"  style="display: none;">
										</span> 
									</p>
								
								<div class="control">
									<button class="apply-tb" id="apply_length-0-0"  onclick="return apply_length(0,0)"style="display:none;">&#10004;</button>
									<button class="apply-tb" id="apply_width-0-0" onclick="return apply_width(0,0)" style="display:none;">&#10004;</button>
									<button class="apply-tb" id="apply_height-0-0" onclick="return apply_height(0,0)" style="display:none;">&#10004;</button>
									<button id="close-dimensions-0-0" onclick="return btn_remove(event,this,0,0);" class="close-tb" style="display: inline-block;">&#10006;</button>
								</div>
							</li>
							<li id = "li-close-sku-0-0" class="sku" style="display: none;">
								<label class="conditions">
									<p>SKU <span class="rule-param"><a href="javascript:void(0)" id="sku_is-0-1" onclick="return sku_is(0,0)">is</a>
											<select name ="rule[conditions][0][0][sku-operator]" id="select_sku_is-0-0" onchange="getval10(this,0,0);" style="display: none;">
												<option value="is" selected="selected">is</option>
												<option value="is not">is not</option>
												<option value="contains">contains</option>
												<option value="does not contain">does not contain</option>
												<option value="is one of">is one of</option>
												<option value="is not one of">is not one of</option>
											</select>
										</span>
										<span class="rule-param"><a href="javascript:void(0)" id="sku_yes-0-0" onclick="return sku_yes(0,0)">....</a>
											<input type="text" name="rule[conditions][0][0][sku-value]" id="sku_input_id-0-0" value="" onkeypress="return checkNum(event,false)" style="display: none;">
											<input type="button" id="sku_div-0-0" class=" button btn_addnewproduct" onclick="plcallpopup(16573,0,0)" data-value="16573,0,0" name="" value="Add New Product" style="display:none;">
											<input type="hidden" id="listchecked-0-0-16573" value="">
										</span> 
									</p>
								</label>
								<div class="control">
									<button class="apply-tb" id="apply_sku-0-0" onclick="return apply_sku(0,0)" style="display:none;">&#10004;</button>
									<button  id="close-sku-0-0" style="display:inline-block;" onclick="return btn_remove(event,this,0,0);" class="close-tb" style="display: inline-block;">&#10006;</button>
								</div>
							</li>
							<li id = "li-close-category-0-0" class="category" style="display: none;">
								<label class="conditions">
									<p>Category <span class="rule-param"><a href="javascript:void(0)" id="category_is-0-0" onclick="return category_is(0,0)" >is</a>
											<span class="element">
												<select name= "rule[conditions][0][0][category-operator]" id="select_category_is-0-0" onchange="getval6(this,0,0); "style="display: none;">
													<option value="is" selected="selected">is</option>
													<option value="is not">is not</option>
													<option value="contains">contains</option>
													<option value="does not contain">does not contain</option>
													<option value="is one of">is one of</option>
													<option value="is not one of">is not one of</option>
												</select>
											</span>
										</span>
										<span class="rule-param"><a href="javascript:void(0)" id="category_yes-0-0" onclick="return category_yes(0,0)">....</a>
											<input type="text" name="rule[conditions][0][0][category-value]" id="cat_input_id-0-0" value="" onkeypress="return checkItInput(event,false)" style="display: none;">
											<div class="multiselect" id="select_category-0-0" style="display: none;">
												<div class="selectBox" onclick="showCheckboxes(0,0)">
													<select>
														<option>Select Category</option>
													</select>
													<div class="overSelect"></div>
												</div>
												<div class = "checkboxes" id="checkboxes-0-0" onclick="checkboxes(0,0)">
													<?php
													$taxonomy     = 'product_cat';
													$orderby      = 'name';  
													$show_count   = 0;
													$pad_counts   = 0;
													$hierarchical = 1;  
													$title        = '';  
													$empty        = 0;

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
													foreach ($all_categories as $cat) {
														if (0 == $cat->category_parent) {
															$category_id = $cat->term_id;  
															$category_name = $cat->name;     
															echo wp_kses("<ul><label for='" . $category_id . "'><input type='checkbox' value='" . $category_id . "' name ='category' />" . $category_name . '</label>', wp_kses_allowed_html('post'));
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
															$sub_category = get_categories( $args2 );
															if ($sub_category) {
																foreach ($sub_category as $sub_cats) {
																	$child_id = $sub_cats->term_id; 
																	$child_cat = $sub_cats->name ;  
																	
																	echo  wp_kses("<li><label for='" . $child_id . "'><input type='checkbox' value='" . $child_id . "' name ='category' />→" . $child_cat . '</label></li>', wp_kses_allowed_html('post'));
																}   
															}
															echo'</ul>';
														}       
													}
													?>

												</div>
											</div>
										</span> 
									</p>
								</label>
								<div class="control">
									<button id="cat_div-0-0" onclick="return category_div(0,0)" style="display:none;">[ ]</button>
									<button class="apply-tb" id="apply_category-0-0" onclick="return apply_category(0,0)" style="display:none;">&#10004;</button>
									<button  id="close-category-0-0" style="display:inline-block;" onclick="return btn_remove(event,this,0,0);" class="close-tb" style="display: inline-block;">&#10006;</button>
								</div>
							</li>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	public static function aw_pl_save_rule_form() {
		global $wpdb;
		$id 					= '';
		$rule_name 				= '';
		$priority 				= '';
		$rule_status 			= '';		
		$start_date 			= '';
		$end_date 				= '';
		$rule_allow_to_user 	= '';
		$frontend_label_text 	= '';
		$frontend_medium_text 	= '';
		$frontend_small_text 	= '';
		$product_id 			= '';
		$label_id 				= '';

		$select_label_all 		= '';
		$select_label_true 		= '';
		$select_sale_is 		= '';
		$select_sale_yes 		= '';
		$select_category_is 	= '';
		$cat_input_id 			= '';
		$weight_is 				= '';
		$weight_input_id 		= '';
		$select_dimensions_is 	= '';
		$dimlength_input_id 	= '';
		$dimwidth_input_id 		= '';
		$dimheight_input_id 	= '';
		$select_special_price_is= '';
		$select_stock_range_is 	= '';
		$range_input_id 		= '';
		$select_sku_is 			= '';
		$sku_input_id 			= '';
		$attribute_dynamic 		= '';
		$serialize_product_condition_array = '';
		$str = '';

		$url =  admin_url() . 'admin.php?page=new-rule';
		if (isset($_POST['rule_id']) && !empty($_POST['rule_id'])) {
			$id = sanitize_text_field($_POST['rule_id']);
			$url =  admin_url() . 'admin.php?page=new-rule&action=edit&id=' . $id;
		}

		if (isset($_POST['rdpl_new_rule_nonce'])) {
			$aw_pl_new_rule_nonce = sanitize_text_field($_POST['rdpl_new_rule_nonce']);
		}

		if ( !wp_verify_nonce( $aw_pl_new_rule_nonce, 'aw_pl_save_rule_form' )) {
			wp_die('Our Site is protected');
		}
		if (isset($_POST['setting_rule_submit'])) {

			if (isset($_POST['rule_name'])) {
				$rule_name = sanitize_text_field($_POST['rule_name']);
			} 

			if (isset($_POST['priority'])) {
				$priority = sanitize_text_field($_POST['priority']);
			} 
			if (isset($_POST['enable_rule'])) {
				$rule_status = sanitize_text_field($_POST['enable_rule']);
			} 

			if (isset($_POST['rule_setting_view_content'])) {
				$rule_allow_to_user = sanitize_text_field($_POST['rule_setting_view_content']);
			} 

			if (isset($_POST['start_date'])) {
				$start_date = sanitize_text_field($_POST['start_date']);
			} 

			if (isset($_POST['end_date'])) {
				$end_date = sanitize_text_field($_POST['end_date']);
			} 

			if (isset($_POST['large_label_text'])) {
				$frontend_label_text = sanitize_text_field($_POST['large_label_text']);
			} 

			if (isset($_POST['medium_label_text'])) {
				$frontend_medium_text = sanitize_text_field($_POST['medium_label_text']);
			} 

			if (isset($_POST['small_label_text'])) {
				$frontend_small_text = sanitize_text_field($_POST['small_label_text']);
			} 

			if (isset($_POST['rule'])) {
				$post_rule = json_encode($_POST);
				$post_rule = wp_unslash($post_rule);
				$post_rule = json_decode($post_rule, true);
				$product_condition_array = array_values(array_filter($post_rule['rule']));
				$serialize_product_condition_array = serialize($product_condition_array);

				$ids = array();			
				$ids = filterVariationsAttribute($product_condition_array);

				if (!empty($ids)) {
					$product_list = implode(', ', $ids);
				}
			
				if (!empty($product_list)) {
					$product_id = sanitize_text_field($product_list);
				}
			}

			if (isset($_POST['select_label_name'])) {
				$label_id = sanitize_text_field($_POST['select_label_name']);
				$label_name =  $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}aw_pl_product_label WHERE id = %d ", "{$label_id}") );				
			} 

			$rule_last_updated = gmdate('Y-m-d H:i:s');	
			
			$db_table = $wpdb->prefix . 'aw_pl_product_rule';
			$post_array = array(
				'rule_name' 			=> $rule_name,
				'label_name' 			=> $label_name,
				'priority' 				=> $priority,
				'rule_status' 			=> $rule_status,
				'rule_allow_to_user' 	=> $rule_allow_to_user,
				'start_date'			=> $start_date,
				'end_date'				=> $end_date,
				'frontend_label_text'	=> $frontend_label_text,
				'frontend_medium_text'	=> $frontend_medium_text,
				'frontend_small_text'	=> $frontend_small_text,
				'product_condition' 	=> $serialize_product_condition_array,
				'product_id' 			=> $product_id,
				'label_id'				=> $label_id,
				'rule_last_updated' 	=> $rule_last_updated					
			);

			if ('' != $id ) {
				$result = $wpdb->update($db_table, $post_array, array('rule_id'=>$id));
				self::aw_pl_rule_save_flash_notice( __('Rule updated successfully'), 'success', true );
				$url =  admin_url() . 'admin.php?page=new-rule&action=edit&id=' . $id;					 	
			} else {
				$result = $wpdb->insert($db_table, $post_array);
				self::aw_pl_rule_save_flash_notice( __('Rule inserted successfully'), 'success', true );
				$url =  admin_url() . 'admin.php?page=aw_pl_rule_page';
			}
		}
		wp_redirect($url);		
	}
}

function filterVariationsAttribute( $pro_condition) {
	if (!empty($pro_condition)) {
		global $wpdb;
		$not_val = '';
		$con = '';
		$weight_operator = '';
		$price_operator = '';
		$range_operator = '';
		$sku_operator = '';
		$category_operator = '';
		$dimensions_operator = '';
		$sale_operator = '';
		$sale_value = '';
		$attr_operator = '';
		$sku_value = '';
		$result = array();
		$pro_id = array();
		$meta_condition = array();
		$subpart_array = array();
		$final_array = array();
		$taxnomy_array = array();
		$sku_array = array();
		$count = 0;

		foreach ($pro_condition[0] as $parentkey=> $innerarray) {
			
			foreach ($innerarray as $childkey=>$array) {
				$allkey = array_keys($innerarray[$childkey]);
				$keys = array_keys($array);
				
				if (isset($innerarray[$childkey]['aggregator']) && in_array('aggregator', $allkey)) {
					$con = $innerarray[$childkey]['aggregator'];
				}  
				if (isset($innerarray[$childkey]['value']) && in_array('value', $allkey)) {
					$con = $con . '-' . $innerarray[$childkey]['value'];
					if ('ALL-TRUE' == $con) {
						$relation = 'AND';
					} else if ('ANY-TRUE' == $con) {
						$relation = 'OR';
					} else if ('ALL-FALSE' == $con) {
						$relation = 'NOT';
					} else if ('ANY-FALSE' == $con) {
						$relation = 'OR';
					}
					$meta_condition[] = array('relation'=>$relation);
				} else {
					if (isset($innerarray[$childkey]['weight-operator']) && in_array('weight-operator', $allkey)) {
						$oper = $innerarray[$childkey]['weight-operator'];
						if ( 'is' ==  $oper) {
							$weight_operator = '=';
						} else if ('is not' == $oper) {
							$weight_operator = '!=';
						} else if ('equals or greater than' == $oper) {
							$weight_operator = '>=';
						} else if ('equals or less than' == $oper) {
							$weight_operator = '<=';
						} else if ('greater than' == $oper) {
							$weight_operator = '>';
						} else if ('less than' == $oper) {
							$weight_operator = '<';
						}
					}
					if (isset($innerarray[$childkey]['weight-value']) && in_array('weight-value', $allkey)) {
						
						$subpart_array[] = array(
									   'key'   => '_weight',
									   'value' => $innerarray[$childkey]['weight-value'],
									   'compare' => $weight_operator
								   );
					}
					if (isset($innerarray[$childkey]['stock_range-operator']) && in_array('stock_range-operator', $allkey)) {
						$oper = $innerarray[$childkey]['stock_range-operator'];

						if ('equal to' == $oper) {
							$range_operator = '=';
						} else if ('is not' == $oper) {
							$range_operator = '!=';
						} else if ('more than' == $oper) {
							$range_operator = '>';
						} else if ('less than' == $oper) {
							$range_operator = '<';
						} else if ('equal or greater than' == $oper) {
							$range_operator = '>=';
						} 
					}
					if (isset($innerarray[$childkey]['stock_range-value']) && in_array('stock_range-value', $allkey)) {
						$subpart_array[]= array(
									   'key'   => '_stock',
									   'value' => $innerarray[$childkey]['stock_range-value'],
									   'compare' => $range_operator
								   );

					}
					if (isset($innerarray[$childkey]['special_price-operator']) && in_array('special_price-operator', $allkey)) {
						$oper = $innerarray[$childkey]['special_price-operator'];
						
						if ('is' == $oper) {
							$price_operator = '=';
						} else if ('is not' == $oper) {
							$price_operator = '!=';
						} else if ('equals or greater than' == $oper) {
							$price_operator = '>=';
						} else if ('equals or less than' == $oper) {
							$price_operator = '<=';
						} else if ('greater than' == $oper) {
							$price_operator = '>';
						} else if ('less than' == $oper) {
							$price_operator = '<';
						} 
					}
					if (isset($innerarray[$childkey]['special_price-value']) && in_array('special_price-value', $allkey)) {
						$subpart_array[]= array(
									   'key'   => '_sale_price',
									   'value' => $innerarray[$childkey]['special_price-value'],
									   'compare' => $price_operator
								   );
					
					}
					if (isset($innerarray[$childkey]['sku-operator']) && in_array('sku-operator', $allkey)) {
						$oper = $innerarray[$childkey]['sku-operator'];
						if ('is' == $oper) {
							$sku_operator = 'IN';
						} else if ('is not' == $oper) {
							$sku_operator = 'NOT IN';
						} else if ('contains' == $oper) {
							$sku_operator = 'IN';
						} else if ('does not contain' == $oper) {
							$sku_operator = 'NOT IN';
						} else if ('is one of' == $oper) {
							$sku_operator = 'IN';
						} else if ('is not one of' == $oper) {
							$sku_operator = 'NOT IN';
						}
					}
					if (isset($innerarray[$childkey]['sku-value']) && in_array('sku-value', $allkey)) {
						$sku_array = $innerarray[$childkey]['sku-value'];
						$sku_value = explode(',', $sku_array);
						/*if(is_array($sku_array)){
							$sku_value = explode(",",$sku_array);
						} else{
							$sku_value = $sku_array;
						}*/
						$subpart_array[]= array(
									   'key'   => '_sku',
									   'value' => $sku_value,
									   'compare' => $sku_operator
								   ); 
					}
					
					if (isset($innerarray[$childkey]['dimensions-operator']) && in_array('dimensions-operator', $allkey)) {
						$oper = $innerarray[$childkey]['dimensions-operator'];
						if ('is' == $oper) {
							$dimensions_operator = '=';
						} else if ('is not' == $oper) {
							$dimensions_operator = '!=';
						} else if ('equals or greater than' == $oper) {
							$dimensions_operator = '>=';
						} else if ('equals or less than' == $oper) {
							$dimensions_operator = '<=';
						} else if ('greater than' == $oper) {
							$dimensions_operator = '>';
						} else if ('less than' == $oper) {
							$dimensions_operator = '<';
						}
					}
					if (isset($innerarray[$childkey]['length-value']) && in_array('length-value', $allkey)) {
						$subpart_array[]= array(
									   'key'   => '_length',
									   'value' => $innerarray[$childkey]['length-value'],
									   'compare' => $dimensions_operator
								   );
					}
					if (isset($innerarray[$childkey]['width-value']) && in_array('width-value', $allkey)) {
						$subpart_array[] = array(
									   'key'   => '_width',
									   'value' => $innerarray[$childkey]['width-value'],
									   'compare' => $dimensions_operator
								   );
					}
					if (isset($innerarray[$childkey]['height-value']) && in_array('height-value', $allkey)) {
						$subpart_array[] = array(
									   'key'   => '_height',
									   'value' => $innerarray[$childkey]['height-value'],
									   'compare' => $dimensions_operator
								   );
					}
					if (isset($innerarray[$childkey]['sale-operator']) && in_array('sale-operator', $allkey)) {
						$oper = $innerarray[$childkey]['sale-operator'];
						if ('is' == $oper) {
							$sale_operator = '>=';
						} else if ('is not' == $oper) {
							$sale_operator = '=';
						}
					}
					if (isset($innerarray[$childkey]['sale-value']) && in_array('sale-value', $allkey)) {
						
						$val = $innerarray[$childkey]['sale-value'];
						if ('Yes' == $val) {
							$subpart_array[] = array(
									   'key'   => '_sale_price',
									   'compare' => 'EXISTS'

								   );
						} else if ('No' == $val) {
							$subpart_array[] = array(
									   'key'   => '_sale_price',
									   'compare' => 'NOT EXISTS'

								   );
						}

					}
					if (isset($innerarray[$childkey]['category-operator']) && in_array('category-operator', $allkey)) {
						$oper = $innerarray[$childkey]['category-operator'];
						if ('is' == $oper) {
							$category_operator = 'IN';
						} else if ('is not' == $oper) {
							$category_operator = 'NOT IN';
						} else if ('contains' == $oper) {
							$category_operator = 'IN';
						} else if ('does not contain' == $oper) {
							$category_operator = 'NOT IN';
						} else if ('is one of' == $oper) {
							$category_operator = 'IN';
						} else if ('is not one of' == $oper) {
							$category_operator = 'NOT IN';
						}
					}
					if (isset($innerarray[$childkey]['category-value']) && in_array('category-value', $allkey)) {
						$cat_id = array($innerarray[$childkey]['category-value']);
						$taxnomy_array[] = array(
										'taxonomy'      => 'product_cat',
										'field'			=> 'term_id', 
										'terms'         => $cat_id,
										'operator'      => $category_operator
									);
						
					}
					if ('sale-operator' != $keys[0] && 'sale-value' != $keys[0] && 'weight-operator' != $keys[0] && 'weight-value' != $keys[0] && 'special_price-operator' != $keys[0] && 'special_price-value' != $keys[0] && 'stock_range-operator' != $keys[0] && 'stock_range-value' != $keys[0] && 'dimensions-operator' != $keys[0] && 'length-value' != $keys[0] && 'width-value' != $keys[0] && 'height-value' != $keys[0] && 'sku-operator' != $keys[0] && 'sku-value' != $keys[0] && 'category-operator' != $keys[0] && 'category-value' != $keys[0]) {

						$oper = $innerarray[$childkey][$keys[0]];

						$subkey = substr($keys[0], 0, strpos($keys[0], '-'));

						if ($keys[0] == $subkey . '-operator') {
							if ('is' == $oper) {
								$attr_operator = '=';
							} else if ('is not' == $oper) {
								$attr_operator = '!=';
							}
						}
						if ($keys[1] == $subkey . '-value') {
							$subpart_array[]= array(
									   'key'   => 'attribute_pa_' . $subkey,
									   'value' => $innerarray[$childkey][$keys[1]],
									   'compare' => $attr_operator
								   );
						}

					}
				}
				if (count($innerarray)-1  ==  $childkey ) {
					$f_array = array_merge($meta_condition[$parentkey], $subpart_array);
					if (count($final_array)>0) {
						array_push($final_array[0], $f_array);
					}
					if (count($final_array)==0) {
						$final_array[] = $f_array;
					}
					$subpart_array = array();
				}
			}
		}
		if (!empty($final_array) || !empty($taxnomy_array) ) {
			$args = array(
				'post_type' => array('product', 'product_variation'),
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'meta_query' => $final_array,
				'tax_query'  => $taxnomy_array,				
			);

			$query = new WP_Query($args);
			if ($query->have_posts()) {
				while ($query->have_posts()) {
					$query->next_post();
					$result[] = $query->post;
				}
			   wp_reset_postdata();
			}
			wp_reset_query();
			foreach ($result as $keyid => $valid) {
				$pro_id[] = $valid->ID;  	
			}
			if ('NOT' == $relation && !empty($pro_id)) {
				if (!empty($pro_id)) {
					$pid = implode(', ', $pro_id);
				}

				//$not_val = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status = 'publish' AND post_type IN ('product','product_variation') AND ID NOT IN(" . $pid . ')', ARRAY_N);

				$not_val = $wpdb->get_results($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status = %s AND (post_type = %s OR post_type = %s) AND ID NOT IN(%5s)", 'publish', 'product', 'product_variation', "{$pid}"), ARRAY_N);

				$pro_id = aw_pl_convert_array_one_dimension($not_val);
			}
			return $pro_id;
		}
	}
}

function aw_pl_rule_row( $id) {
	global $wpdb;
	$single_rule = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pl_product_rule WHERE rule_id = %d ", "{$id}") );
	return $single_rule;
}

function aw_pl_get_product_image( $product_id) {
	$url = '';
	if (has_post_thumbnail( $product_id ) ) {
		$url = get_the_post_thumbnail_url($product_id, array(20,20));
	} else {
		$url = site_url() . '/wp-content/uploads/woocommerce-placeholder-150x150.png';
	}
	return $url;
}
function aw_pl_convert_array_one_dimension( $array) { 
	if (!is_array($array)) { 
	  return false; 
	} 
  $result = array(); 
	foreach ($array as $key => $value) { 
		if (is_array($value)) { 
		  $result = array_merge($result, aw_pl_convert_array_one_dimension($value)); 
		} else { 
		  $result[$key] = $value; 
		} 
	} 
  return $result; 
} 


