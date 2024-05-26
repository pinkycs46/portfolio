<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
class AwbatcProductList {

	public function __construct() {
		 
	}

	public static function self_deactivate_notice() {
		/** Display an error message when WooComemrce Plugin is missing **/
		?>
		<div class="notice notice-error">
			<p>Please install and activate WooComemrce plugin before activating Bulk AddTo Cart plugin.</p>
		</div>
		<?php
	}

	public static function bulk_addto_cart_activate() {
		/** Create and Register Post Type bulk-product-list **/
		if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
			$labels = array(
				'name'               => __( 'Product Lists', 'aw-bulktocart-plugin' ),
				'singular_name'      => __('Bulk Add To Cart', 'aw-bulktocart-plugin'),
				//'add_new'            => __('New Product List', 'aw-bulktocart-plugin'),
				'add_new_item'       => __('Add', 'aw-bulktocart-plugin'),
				'edit_item'          => __('Edit List', 'aw-bulktocart-plugin'),
				//'new_item'           => __('Bulk Add to Cart', 'aw-bulktocart-plugin'),
				'all_items'          => __('Product Lists', 'aw-bulktocart-plugin'),
				//'view_item'          => __('', 'popup_pro_plugin'), 
				'search_items'       => __('Search Lists', 'aw-bulktocart-plugin'),
				'not_found'          => __('No Lists Found', 'aw-bulktocart-plugin'),
				'not_found_in_trash' => __('No Lists Found in Trash', 'aw-bulktocart-plugin'),
				'parent_item_colon'  => '',
				'menu_name'          => 'Bulk Add to Cart'
				);

			$args = array(
				'labels'        	=> $labels,
				'description'   	=> __( 'Description.' , 'aw-bulktocart-plugin' ),
				'capability_type' 	=> 'post',
				'capabilities' 		=> array('create_posts' => false),
				'public'        	=> true,
				'menu_position' 	=> 25,
				'supports'      	=> array('title'),
				'has_archive'   	=> false,
				'hierarchical'		=> false,
				//'rewrite'			=> array('slug' => 'aw_bulk_product_list/%popup_category%','with_front' => false),
				'publicly_queryable'=> false,
				'menu_icon'         => 'dashicons-cart',
				'map_meta_cap'		=> true
				);

			$post_type_exists = post_type_exists('aw_bulk_product_list');

			if (!$post_type_exists) {
				register_post_type( 'aw_bulk_product_list', $args );
			}
		}
	}

	public static function aw_bulk_addto_cart_adminmenu() {
		$page = add_submenu_page('edit.php?post_type=aw_bulk_product_list', 'Product List', 'New Product List', 'manage_options', 'aw-batc-product-list-admin', array(get_called_class(),'aw_batc_admin_main_product_list'));
	}

	// Add the custom columns to the aw_bulk_product_list post type:
	public static function aw_add_new_columns_product_grid( $new_columns) {
		unset( $new_columns['date'] );
		$new_columns['post_status'] = __('Status', 'aw-bulktocart-plugin');
		$new_columns['date'] = __( 'Date', 'aw-bulktocart-plugin' );
		$new_columns['price'] = __( 'Total Price', 'aw-bulktocart-plugin' );
		$new_columns['shortcode'] = __( 'Shortcode', 'aw-bulktocart-plugin' );
		
		return $new_columns;
	}

	// Add the data to the custom columns for the aw_bulk_product_list post type:
	public static function aw_add_data_to_custom_product_grid_column( $column, $post_id ) {

		switch ( $column ) {

			case 'post_status':
					$status = get_post_status($post_id);
				if ( 'draft' == $status ) {
					$status = 'Unpublished';
				}
				if ('publish'  ==  $status) {
					$status = 'Published';
				}
				update_post_meta($post_id, 'post_status', $status);
				echo wp_kses_post($status);
				break;

			case 'price':
					$total = self::aw_get_tab_product_total_price($post_id);
					echo wp_kses_post($total);
				break;

			case 'shortcode':
				echo wp_kses_post('[batc id=' . $post_id . ']');
				break; 
		}
	}

	public static function aw_bulk_product_list_bulk_action_handler( $redirect_to, $doaction, $post_ids) {
		/** Bulk Publish / Unpublish Action Handler **/
		foreach ($post_ids as $post_id) {
			$update_post = array(
				'post_type' 	=> 'aw_bulk_product_list',
				'ID' 			=> $post_id,
				'post_status' 	=> $doaction
			);
			$statusTest = wp_update_post($update_post);
		}
		return $redirect_to;
	}

	public static function aw_batc_quick_edit_add( $column_name, $post_type ) {
		if ( 'aw_bulk_product_list' == $post_type ) {
			wp_nonce_field( 'aw_batc_save_inline_edit_form', 'aw_batc_admin_edit_inline_nonce' );
		}
	}

	public static function aw_bulk_addto_cart_changetitle ( $post_id) {
		
		if (isset($_POST['screen']) && 'edit-aw_bulk_product_list' == $_POST['screen'] && isset($_POST['post_type']) && 'aw_bulk_product_list' == $_POST['post_type']) {
			
			if (isset($_POST['aw_batc_admin_edit_inline_nonce'])) {
				$security = sanitize_text_field($_POST['aw_batc_admin_edit_inline_nonce']);
			}

			if ( !wp_verify_nonce( $security, 'aw_batc_save_inline_edit_form')) {
				wp_die('Our Site is protected');
			}

			global $wpdb;

			$db_product_list_table 	= $wpdb->prefix . 'product_list';

			if ( isset($_POST['post_ID']) ) {
				$post_id 				= sanitize_text_field($_POST['post_ID']);
			}

			if ( isset($_POST['post_title']) ) {
				$post_title				= sanitize_text_field($_POST['post_title']);
			}

			$ary = array('tab_title' => stripslashes($post_title));
			$wpdb->update($db_product_list_table, $ary, array('post_id' => $post_id));
		}
	}

	/* Get Total Price Coumn Value of table grid */
	public static function aw_get_tab_product_total_price( $post_id) {
		global  $woocommerce;
		$total_price = 0;
		$price 		= 0;
		$data = self::aw_get_tabs_product_row($post_id);

		if (!empty($data)) {

			$tab_id		= $data['tab_id'];	 
			$quantity	= maybe_unserialize($data['quantity']);
			$variation 	= maybe_unserialize($data['variation']); 
			$product_ids= maybe_unserialize($data['product_ids']);
			if (!empty($variation)) {
				$variation = $variation[$tab_id];
			}
			if (!empty($product_ids)) {
				foreach ($product_ids as $index=>$product_id) {
					if (!empty($variation[$product_id]['variation_id'])) {
						$product_id = $variation[$product_id]['variation_id'];
					} 
					if ('product' === get_post_type( $product_id ) || 'product_variation' === get_post_type( $product_id )) {
						$product = wc_get_product( $product_id );
						$price = 0;
						 
						if ($product->get_price()) {
							$price = $quantity[$index] * $product->get_price();	
						}
						$total_price= $total_price+$price;
					}
				}	
			}
			$decimalposition = get_option('woocommerce_price_num_decimals'); 
			$total_price = esc_html(sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), number_format($total_price, $decimalposition) ));
		}
		return $total_price;
	}

	/*
	 ** Get product from popup-up product list into main product list using AJAX 
	*/
	public static function aw_get_product_from_woo_popup_grid() {
		$tbody	= '';

		check_ajax_referer( 'aw_batc_admin_nonce', 'aw_qa_nonce_ajax' );

		if (isset($_POST['product_id']) && !empty($_POST['product_id']) && isset($_POST['tab_id']) && !empty($_POST['tab_id'])) {

			$product_ids = json_encode($_POST);
			$product_ids = wp_unslash($product_ids);
			$product_ids = json_decode($product_ids, true);
			$product_ids = array_values(array_filter($product_ids['product_id']));

			$tab_id 	 = sanitize_text_field($_POST['tab_id']);
			$tbody 		 = self::aw_show_product_list_of_tab($product_ids , $tab_id );
		}
		echo wp_kses($tbody, wp_kses_allowed_html('post'));
		die;
	}

	/*
	  Render tab product list
	*/
	public static function aw_show_product_list_of_tab ( $product_ids, $tab_id, $quantity = array(), $variation_id = array()) {
		global  $woocommerce;
		global 	$product;   

		$tbody 	= '';
		$qty 	= 1;
		$attribute_arr 		= array();
		$variation_images 	= array();
		$summation			= 0;
		$stop 				= 1;
		$variations_input 	= '';
		$variation_id 		= array();

		if (!empty($product_ids) && !empty($tab_id)) {

			$records = self::aw_get_singletab_product_list($tab_id);
			$existing_variation = array();
			if (!empty($records->variation)) {
				$existing_variation = maybe_unserialize($records->variation);
			}
			if (!empty($records->quantity)) {
				$existing_qty 		= maybe_unserialize($records->quantity);
			}
			if (!empty($records->product_ids)) {
				$existing_product_ids= maybe_unserialize($records->product_ids);
			}

			foreach ($product_ids as $key => $product_id) {
				$product_obj = new WC_Product_Factory();
				if ('product' === get_post_type( $product_id )) {
					$product = $product_obj->get_product($product_id);
					if ($product->is_type( 'grouped' ) ) {
						$children = $product->get_children();
						unset($product_ids[$key]);	
						$product_ids = array_merge($product_ids, $children);
					}		
				} else {
					unset($product_ids[$key]);
				} 
			}
			$product_ids = array_unique($product_ids);
			foreach ($product_ids as $key => $product_id) {
				$variations 	= '';
				$edit_link 		= '';
				$variations_txt = '';
				$v_id 			= '';
				$row_id  		= $key . '-' . $tab_id;
				$product_obj 	= new WC_Product_Factory();
				$product 		= $product_obj->get_product($product_id);
				$rowtotal 		= 0;
				$hidden_can_edit= '';
				$price 			= 0;
				if ('' != aw_get_individual_product_price($product_id)) {
					$price = aw_get_individual_product_price($product_id);
				}

				if (isset($existing_qty[$key]) && !empty($existing_qty[$key]) && in_array($product_id, $existing_product_ids)) {
					$qty 		= $existing_qty[$key];	
				}

				if ($product->is_type( 'variable' )) {

					$args = array (
									'post_type'     => 'product_variation',
									'post_status'   => array( 'private', 'publish' ),
									'numberposts'   => -1,
									'orderby'       => 'menu_order',
									'order'         => 'asc',
									'post_parent'   => $product_id 
								);
					$variations = get_posts( $args );

					if (!empty($variations)) {
						foreach ( $product->get_attributes() as $attribute_name => $options ) {
							if (isset($existing_variation[$tab_id][$product_id]['variation_id'] ) && !empty($existing_variation[$tab_id][$product_id]['variation_id']) ) {

								$v_id = $existing_variation[$tab_id][$product_id]['variation_id'];
								$price = 0;
								if ('' != aw_get_individual_product_price($v_id)) {
									$price = aw_get_individual_product_price($v_id);
								}

								$name 	= ucfirst(str_replace('pa_', '', $attribute_name));
								$value 	= ucfirst(get_post_meta($v_id, 'attribute_' . $attribute_name, true));

								if (''==$value && isset($existing_variation[$tab_id][$product_id][$name])) {
									$value 	= $existing_variation[$tab_id][$product_id][$name];
								}
									$variations_txt .= $name . ': ' . $value . ', ';
									$variations_input .= '<input type="hidden" value="' . strtolower(trim($value)) . '" class="variation_txt_tr-' . $row_id . '" name="variations[' . $tab_id . '][' . $product_id . '][' . ucfirst(trim($name)) . ']"><input type="hidden" value="' . $value . '" class="existvariation_tr-' . $row_id . '">';

									/* Check Customer can edit */
									$getall_keys = array_keys($existing_variation[$tab_id][$product_id]);
									$datakey = 'Customer_can_edit_' . $attribute_name;
									
								if ( in_array($datakey, $getall_keys ) ) {
									$hidden_can_edit .= $existing_variation[$tab_id][$product_id][$datakey];
									$variations_input .= '<input type="hidden" value="' . strtolower(trim($attribute_name)) . '" class="variation_txt_tr-' . $row_id . '" name="variations[' . $tab_id . '][' . $product_id . '][' . ucfirst(trim($datakey)) . ']">';
								}
							} else {

								$v_id 	= $variations[0]->ID;
								$price 	= 0; 
								if ('' != aw_get_individual_product_price($v_id)) {
									$price = aw_get_individual_product_price($v_id);
								}
								$label 	= wc_attribute_label( $attribute_name );
								$value 	= ucfirst(get_post_meta($v_id, 'attribute_' . $attribute_name, true));
								if ('' == $value) {
									$term_taxonomy 	= wc_get_product_terms($product_id, $attribute_name, 'names');
									if (!empty($term_taxonomy)) {
										$value= $term_taxonomy[0]->name;	
									} else {
										$any_variation = explodeX(array(',', '|'), $product->get_attribute($attribute_name));
										if (!empty($any_variation)) {
											$value= $any_variation[0];
										}
									}
								}
								$variations_txt .= ucfirst($label) . ': ' . $value . ', ';
								$variations_input .= '<input type="hidden" value="' . strtolower(trim($value)) . '" class="variation_txt_tr-' . $row_id . '" name="variations[' . $tab_id . '][' . $product_id . '][' . ucfirst(trim($label)) . ']">';
							}
						}

						$variations_txt = rtrim($variations_txt, ', ');	
					}

					$product_variation = new WC_Product_Variation( $v_id );
					$image_tag 	= $product_variation->get_image(array(30,30));
					$tr 		= "'tr-" . $row_id . "'";
					$data_value = "'modal_variation_" . $product_id . "'";
					//$edit_link 	= '<br/><a href="javascript:void(0)" class="edit_popup_open aw_variation_modal_' . $product_id . '" data-variation_id="' . $v_id . '" data-row_id="' . $tr . '" data-product_id="' . $product_id . '"  data-value="modal_variation_' . $product_id . '">Edit</a>';
					
					$edit_link 	= '<br/><a href="#" class="edit_popup_open aw_variation_modal_' . $product_id . '" onclick="return edit_popup_open(' . $v_id . ',' . $tr . ',' . $product_id . ',' . $data_value . ');">Edit</a>';

				} else {

					$image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'single-post-thumbnail' );

					if (empty($image_url)) {
						$string = wc_placeholder_img_src( 30 );
						$image_tag = '<img width="30" height="30" src="' . $string . '" class="attachment-30x30 size-30x30" alt="">';
					} else {
						$image_tag = '<img width="30" height="30" src="' . $image_url[0] . '" class="attachment-30x30 size-30x30" alt="">';
					}
				}

				$product_description 	= wp_trim_words($product->get_description(), 10, ' …' );	
				$responsiveclass = '';
				if (0 == $key) {
					$responsiveclass = 'is-expanded';
				}
				$tbody	.= '<tr class="tr-' . $row_id . ' ' . $responsiveclass . '">';
				$tbody	.= '<td  >::</td>';
				$tbody	.= '<td class="aw-checkbox"><input type="checkbox" name="chk_box_' . $product_id . '" value="tr-' . $row_id . '"><input type="hidden" name="product_id[' . $tab_id . '][]" value="' . $product_id . '"></td>';
				$tbody	.= '<td class="aw-prod-img-tr-' . $row_id . '">' . $image_tag . '</td>';
				$tbody	.= '<td class="column-primary" data-colname="Name">' . $product->get_name() . '<button type="button" class="toggle-row"><span class="screen-reader-text">show details</span></button></td>';
				$tbody	.= '<td class="variation-tr-' . $row_id . '" data-colname="Variation">
								<span>' . $variations_txt . '</span>
								' . $edit_link . '
								<input type="hidden"  value="' . $v_id . '" class="variation_txt_tr-' . $row_id . '" name="variations[' . $tab_id . '][' . $product_id . '][variation_id]">
								' . $variations_input . '
							</td>';
				$tbody	.= '<td data-colname="Description">' . $product_description . '</td>';
				$tbody	.= '<td class="price" data-colname="Price"><input type="hidden" class="price-tr-' . $row_id . '" value="' . $price . '">' . $product->get_price_html() . '</td>';
				//$tbody	.= '<td class="price">' . esc_html(sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), round($price, 2) )) . '</td>';
				$tbody	.= '<td data-colname="Quantity"><input type="number" step="1" min="1" max="" size="2" style="width: 60px;" inputmode="numeric" onpaste="return false;" name="qty[' . $tab_id . '][]" class="quantity" id="quantity-tr-' . $row_id . '"  value="' . $qty . '" size="1" onkeypress="return aw_batc_checkIt(event,false)"/></td>';

				if (is_numeric($qty)) {
					$rowtotal = $price * $qty;	
				}
				$summation= $summation + $rowtotal;
				$decimalposition = get_option('woocommerce_price_num_decimals'); 
				$tbody	.= '<td class="rowtotal" data-colname="Total"><input type="hidden"  class="row_totalamount" value="' . $rowtotal . '" name="totalamount[' . $tab_id . '][]"><span id="totalamount-tr-' . $row_id . '" data-rowtotal="' . $rowtotal . '"  class="totalamount">' . esc_html(sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), number_format($rowtotal, $decimalposition) )) . '</span></td>';
				
				if ( $product->is_on_backorder() ) {
					$stock_html = '<mark class="onbackorder">' . __( 'On backorder', 'woocommerce' ) . '</mark>';
				} elseif ( $product->is_in_stock() ) {
					$stock_html = '<mark class="instock">' . __( 'In stock', 'woocommerce' ) . '</mark>';
				} else {
					$stock_html = '<mark class="outofstock">' . __( 'Out of stock', 'woocommerce' ) . '</mark>';
				}

				$tbody	.= '<td data-colname="Status">' . $stock_html . '</td>';
				//$tbody	.= '<td class="column-is_in_stock">'.$product->get_stock_status().'</td>';
				$tbody	.= '<td data-colname="Remove"><a href="#" data-class="tr-' . $key . '-' . $tab_id . '" data-value="' . $product_id . '" alt="f158" class="dashicons dashicons-no batc-remove-product" onclick="rdclick_ondelete(this)"></a></td>';

				$tbody	.= '</tr>';

			} /* For Loop end */
			$tbody.= '<input type="hidden" value="' . $summation . '" class="summation-' . $tab_id . '">';
		} else {
			$tbody	.= '<tr><td colspan="11">No Products</td></tr>';
		}
		return $tbody;
	}  

	/*
		 Save tabbed Product list after add product
	*/
	public static function aw_batc_save_product_list_form() {
		global $wpdb;
		$table_name 		= $wpdb->prefix . 'product_list'; 
		$url 				= '';
		$post_id 			= '';
		$sear_product_id 	= '';
		$quantity 			= '';
		$variation 			= '';
		$product_ids 		= array();

		if (isset($_POST['aw_batc_admin_nonce'])) {
			$aw_batc_admin_nonce = sanitize_text_field( $_POST['aw_batc_admin_nonce'] );
		}

		if ( !wp_verify_nonce( $aw_batc_admin_nonce, 'save_product_list_form' )) {
			wp_die('Our Site is protected');
		}

		if ( isset($_POST['batc_save_list'])) {
			$tabs = array();

			if (!empty($_POST['post_id'])) {
				$post_id 	= sanitize_text_field($_POST['post_id']);
			}

			if (!empty($_POST['product_id'])) {
				$product_ids = json_encode($_POST);
				$product_ids = wp_unslash($product_ids);
				$product_ids = json_decode($product_ids, true);
				$product_ids = array_filter($product_ids['product_id']);
			}

			if (!empty($_POST['qty'])) {
				$qty = json_encode($_POST);
				$qty = wp_unslash($qty);
				$qty = json_decode($qty, true);
				$qty = array_filter($qty['qty']);
			}

			if (!empty($_POST['tab_id'])) {
				$tab_id = json_encode($_POST);
				$tab_id = wp_unslash($tab_id);
				$tab_id = json_decode($tab_id, true);
				$tab_id = array_filter($tab_id['tab_id']);
			}

			if (!empty($_POST['tabtitle'])) {
				$tabtitle = sanitize_text_field($_POST['tabtitle']);
				/*$tabtitle = json_encode($_POST);
				$tabtitle = wp_unslash($tabtitle);
				$tabtitle = json_decode($tabtitle, true);
				$tabtitle = array_filter($tabtitle['tabtitle']);*/
			}
			$tabtitle = stripslashes($tabtitle);

			foreach ($tab_id as $key => $tab_id) {
				//$title 		= $tabtitle[$key];
				$title 		= $tabtitle;
				if (0 === $key && false === get_post_status( $post_id )) {
					$post_id	= 	wp_insert_post( array(
										'post_status' => 'publish',
										'post_type' => 'aw_bulk_product_list',
										'post_title' => $title,
									) );
				} else {
					wp_update_post( array(
					'ID' => $post_id,
									'post_status' => 'publish',
									'post_type' => 'aw_bulk_product_list',
									'post_title' => $title,
								) );
				}

				if (!empty($product_ids)) {
					$product_id 	 	= $product_ids[$tab_id];
					$sear_product_id 	= maybe_serialize($product_ids[$tab_id]);
					$quantity			= maybe_serialize($qty[$tab_id]);
				}

				if (isset($_POST['variations'])) {
					$variation = json_encode($_POST);
					$variation = wp_unslash($variation);
					$variation = json_decode($variation, true);
					$variation = array_filter($variation['variations']);
					$variation = maybe_serialize($variation); 	
				}
				
				$data_array		= array(
											'product_ids'	=> $sear_product_id, 
											'tab_id'	 	=> $tab_id,
											'tab_title' 	=> $title,
											'quantity'	 	=> $quantity,
											'variation'		=> $variation,
											'updated_date' 	=> gmdate('Y-m-d H:i:s')
										);	

				$existing_record 	= self::aw_get_singletab_product_list($tab_id);
				if (empty($existing_record)) {
					$data_array['post_id'] = $post_id;
					$result_success = $wpdb->insert($table_name, $data_array);
				} else {
					$result_success = $wpdb->update($table_name, $data_array, array('post_id' => $post_id));
				}

				//$url =  admin_url() . 'edit.php?post_type=aw_bulk_product_list&page=aw-batc-product-list-admin&post=' . $post_id . '&action=edit';
				$notices = get_option( 'aw_batc_flash_notices', array() );
				array_push( $notices, array( 
						'notice' => 'Changes are applied successfully', 
						'type' => 'success', 
						'dismissible' => true
				) );
				update_option('aw_batc_flash_notices', $notices );

				$url =  admin_url() . 'edit.php?post_type=aw_bulk_product_list';
			}
		}
		wp_redirect($url);
	} 		

	public static function aw_batc_admin_main_product_list( $post) {

		global  $woocommerce; 
		$results 		= array();
		$tab_ids 		= array();
		$tab_title 		= array();
		$variation_id 	= array();
		$tab_id 		= '';
		$tbody 			= '';
		$title			= 'New Product List';

		if (isset($_GET['post'])) {
			$get_post = sanitize_text_field($_GET['post']);
			if ('' != trim($get_post)) {
				$post_id  = trim($get_post);
				$results  = aw_get_alltab_product_list($post_id);
				$title	  = 'Edit Product List';
			}
		}
		if (count($results)>0) {	
			foreach ($results as $result) {
				$tab_ids[] 		= $result->tab_id;
				$tab_title[]	= $result->tab_title;	
				$product_ids[] 	= maybe_unserialize($result->product_ids);
				$quantity[] 	= maybe_unserialize($result->quantity);	
				$variation_id   = maybe_unserialize($result->variation);
			}
		} else {
			$tab_id = strtotime(gmdate('Y-m-d H:i:s'));
			$tab_title[] = 'No Name';
		}  

		?>
		<div class="tab-grid-wrapper aw-batc">
			<div class="spw-rw clearfix">
				<div class="panel-box temp-design">
					<div class="page-title">
						<h1><?php echo wp_kses_post($title); ?></h1>
					</div>
					<div class="panel-body" id="alltabcontent">
						<div class="tab" id="allmaintab">
						<?php  
						if (count($results)>0) {
							foreach ($tab_ids as $key => $tab_id) {
								$tab_id 	= $result->tab_id;	
								?>
								<button class="tablinks" data-id="<?php echo wp_kses_post($key); ?>" id="aw-batc-list-tab-<?php echo wp_kses_post($key); ?>"  onclick="openTab(event, '<?php echo wp_kses_post($key); ?>')"><?php echo wp_kses_post($tab_title[$key]); ?></button>
								<?php
							}
						} else {
							$tab_ids[0] = $tab_id;
							?>
							<button class="tablinks" data-id="0" data-value="<?php echo wp_kses_post($tab_id); ?>" id="aw-batc-list-tab-0"  onclick="openTab(event, '0')">No Name</button>
							<?php 
						}  
						?>
						<!-- <button class="tablinks" id="aw-batc-list-tab-plus" onclick="cloneTab(event, '<?php //echo $tab_id; ?>', 1)">+</button> -->
						<button class="tablinks" id="aw-batc-list-tab-plus" onclick="">+</button>
						</div> 

						<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">

							<?php wp_nonce_field( 'save_product_list_form', 'aw_batc_admin_nonce' ); ?>
								<input type="hidden" name="action" value="save_product_list_form">
								<?php
								if (!isset($post_id)) {
									$post_id = '';
								}
								?>
								<input type="hidden" name="post_id" value="<?php echo wp_kses_post($post_id); ?>">
							<?php 
							foreach ($tab_ids as $key=> $tab_id) {
								?>
							<div id="aw-batc-list-tabcontent-<?php echo wp_kses_post($key); ?>" class="tabcontent">
								<input type="hidden" name="tab_id[]" class="hidden-tabid" value="<?php echo wp_kses_post($tab_id); ?>">	
								<div class="batc-tab-grid">
									<ul>
										<li>
											<lable>Title</lable>
											<br class="clear">
											<input type="text" id="batc_tab_input-<?php echo wp_kses_post($tab_id); ?>" name="tabtitle" class="required" value="<?php echo esc_attr($tab_title[$key]); ?>"> 
											<span class="errormsg-title"></span>
										</li>
											<div class="tablenav top">
											   <div class="alignleft actions bulkactions">
												  <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
												  <select name="" id="bulk-action-selector-top">
													 <option value="-1">Bulk Actions</option>
													 <option value="trash">Remove</option>
												  </select>
												  <input type="button" id="batc-removeaction" onclick="return batc_confirmdelete();" class="button action" value="Apply">

												  <input type="button" id="batc_addnew_buton-<?php echo wp_kses_post($tab_id); ?>" class=" button btn_addnewproduct" onclick="rdcallpopup(<?php echo wp_kses_post($tab_id); ?>)" data-value="<?php echo wp_kses_post($tab_id); ?>" name="" value="Add New Product">
												  <input type="hidden" id="addbeforesave-<?php echo wp_kses_post($tab_id); ?>" class=" button addbeforesave_buton"  name="" value="1">
												  <input type="hidden" id="listchecked-<?php echo wp_kses_post($tab_id); ?>" value="">

											   </div>
											   <br class="clear">
											</div>
										<li>
										</li>
									</ul>
								</div>

								<!-- ############ -->
								<div class="wrap">
										<table id="batc"  class="wp-list-table widefat striped" >
											 <thead>
												<tr>
													<th> :: </th>
													<th class="column-primary"> Select </th>
													<th> Image </th>
													<th > Product Name </th>
													<th> Variation </th>
													<th> Description </th>
													<th> Price </th>
													<th> Qty </th>
													<th> Row Total </th>
													<th> Stock </th>
													<th> Remove </th>
												</tr>
											</thead>
											<tbody id="the-list" class="ui-sortable main-list-<?php echo wp_kses_post($tab_id); ?>">
												<?php 
												if (count($results)==0) {
													?>
												<tr class="no_product" ><td colspan="11"> No Products</td></tr>
													<?php 
												} else {
													$tbody = self::aw_show_product_list_of_tab($product_ids[$key] , $tab_id , $quantity[$key], $variation_id);
													echo wp_kses($tbody, wp_kses_allowed_html('post'));
												}
												?>
											 
											</tbody>
										</table>
								</div>
								<!-- ############ -->
							</div> <!-- close of individual tab -->
								<?php 
							}
							?>
							<div class="aw-batc-list-action-bottom">
								<div class="aw-total-summary">
									<label>Total:&nbsp;&nbsp;</label><span class="overallsummation"></span>
								</div>
								<div class="aw-action-buttons">
									<input type="submit" class="button button-primary aw_validatesave_btn" value="Save" name="batc_save_list">
									<span style="color: green; font-weight: bold;" class="intemate_to_save"></span>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>	
 
		<?php 
	}

	public static function aw_get_singletab_product_list( $tab_id) {

		global $wpdb;
		$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}product_list WHERE tab_id = %d", "{$tab_id}"));
		return $result;
	}
	public static function aw_get_tabs_product_row( $post_id) {

		global $wpdb;
		$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}product_list WHERE post_id = %d", "{$post_id}"), ARRAY_A);
		return $result;
	}

	/*
	** Change Title and Edit Row url of Data tabel grid
	*/

	public static function aw_modify_list_row_title( $link, $post_id, $context) {

		require_once(ABSPATH . 'wp-admin/includes/screen.php');
		$scr = get_current_screen();

		if (!empty($scr)) {

			$url = admin_url( 'edit.php?post_type=aw_bulk_product_list&page=aw-batc-product-list-admin&post=' . $post_id );
			$edit_link 	= add_query_arg( array( 'action' => 'edit' ), $url );

			if ('edit-aw_bulk_product_list' == $scr->id && 'aw_bulk_product_list' == $scr->post_type && 'display' == $context) {
				return $url;
			} else {
				return $link;
			}
		}
	}

	public static function aw_modify_list_row_actions( $actions, $post) {
		$trash		= '';
		$quick_edit = '';
		if ('aw_bulk_product_list' === $post->post_type) {

			$url = admin_url( 'edit.php?post_type=aw_bulk_product_list&page=aw-batc-product-list-admin&post=' . $post->ID );
			$edit_link 	= add_query_arg( array( 'action' => 'edit' ), $url );

			if (isset($actions['trash'])) {
				$trash 	= $actions['trash'];	
			}
			if (isset($actions['inline hide-if-no-js'])) {
				$quick_edit= $actions['inline hide-if-no-js']; 
			}

			if (isset($actions['inline hide-if-no-js'])) {
				$actions = array(
					'edit' => sprintf( '<a href="%1$s">%2$s</a>',
					esc_url( $edit_link ),
					esc_html( __( 'Edit', '' ) ) )
				);
			}

			if ( '' != $trash) {
				 $actions['trash'] = $trash;	
			}
			if ( '' != $quick_edit) {
				$actions['inline hide-if-no-js'] = $quick_edit;
			}

			// get all post type which is register
			$posttypes = get_post_types(array('public' => true), 'names', 'and');
			foreach ($posttypes as $post_type) {
				$posttype[] = $post_type;
			}
			//check current post type is in "$posttype"  array, if it's in array then display duplicate link.
			if (in_array($post->post_type, $posttype)) {
				$actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=aw_batc_duplicate_row_as_draft&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce' ) . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
			}	        
		}	
		return $actions;	
	}

	/*
	** Create duplicate row of table data grid
	*/

	public static function aw_batc_duplicate_row_as_draft() {

		global $wpdb;
		if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'aw_batc_duplicate_post_as_draft' == $_REQUEST['action'] ) ) ) {
			wp_die('No post to duplicate has been supplied!');
		}

		/*
		 * Nonce verification
		*/
		if (isset($_GET['duplicate_nonce'])) {
			$duplicate_nonce = sanitize_text_field($_GET['duplicate_nonce']);
		}
		
		if ( !isset( $duplicate_nonce ) || !wp_verify_nonce( $duplicate_nonce, basename( __FILE__ ) ) ) {
			return;
		}	

		/*
		 * get the original post id
		 */

		$post_id = ( isset($_GET['post']) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );
		/*
		 * and all the original post data then
		 */
		$post = get_post( $post_id );
	 
		/*
		 * if you don't want current user to be the new post author,
		 * then change next couple of lines to this: $new_post_author = $post->post_author;
		 */
		$current_user = wp_get_current_user();
		$new_post_author = $current_user->ID;
	 
		/*
		 * if post data exists, create the post duplicate
		 */
		if (isset( $post ) && null != $post) {
	 
			/*
			 * new post data array
			*/
			$args = array(
						'comment_status' => $post->comment_status,
						'ping_status'    => $post->ping_status,
						'post_author'    => $new_post_author,
						'post_content'   => $post->post_content,
						'post_excerpt'   => $post->post_excerpt,
						'post_name'      => $post->post_name,
						'post_parent'    => $post->post_parent,
						'post_password'  => $post->post_password,
						'post_status'    => 'draft',
						'post_title'     => $post->post_title,
						'post_type'      => $post->post_type,
						'to_ping'        => $post->to_ping,
						'menu_order'     => $post->menu_order
					);
	 
			/*
			 * insert the post by wp_insert_post() function
			 */
			$new_post_id = wp_insert_post( $args );
	 
			/*
			 * get all current post terms ad set them to the new post draft
			 */
			$taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
			foreach ($taxonomies as $taxonomy) {
				$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
				wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
			}
	 
			/*
			* duplicate all post meta just in two SQL queries
			*/

			$post_meta_infos = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d", "{$post_id}"));
			if (count($post_meta_infos)!=0) {
				foreach ($post_meta_infos as $meta_info) {
					$meta_key = $meta_info->meta_key;
					if ( '_' . $wpdb->prefix . 'old_slug' == $meta_key ) {
						continue;
					}
					$meta_value = addslashes($meta_info->meta_value);
					$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}postmeta SET post_id= %d, meta_key= %s, meta_value= %s ", $new_post_id, "{$meta_key}", "{$meta_value}"));
				}
			}
		
			$data_array = self::aw_get_tabs_product_row($post_id);
			if (!empty($data_array)) {
				$table_name	= $wpdb->prefix . 'product_list';
				unset($data_array['id']);
				$data_array['post_id'] 	= $new_post_id;
				$data_array['tab_id'] 	= strtotime(gmdate('Y-m-d H:i:s'));
				$tab_id 				= $data_array['tab_id'];
				if (isset($data_array['variation']) && !empty($data_array['variation'])) {
					$variation = maybe_unserialize($data_array['variation']);
					foreach ($variation as $key => $value) {
						unset($variation[$key]);
						$variation[$tab_id] = $value;
					}
					$data_array['variation'] = maybe_serialize( $variation );
				}
				$result_success  = 	$wpdb->insert($table_name, $data_array);
			}
			wp_redirect( admin_url( 'edit.php?post_type=aw_bulk_product_list'));
			exit;
		} else {
			wp_die('Post creation failed, could not find original post: ' . esc_html($post_id));
		}
	}

	/*
	** Delete all product tab list after delete custom post type
	*/

	public static function aw_batc_delete_product_after_post( $post_id) {

		global $wpdb;
		$chk_tbl = $wpdb->query("SHOW TABLES LIKE '{$wpdb->prefix}product_list'");
		
		if (1==$chk_tbl) {
			$wpdb->query($wpdb->prepare("DELETE  FROM {$wpdb->prefix}product_list WHERE `post_id` = %d", "{$post_id}"));
		}
	}

	/*
	 ** Ajax call to append data into variation popup
	 */
	public static function aw_append_variation_to_popup() {
		global $wpdb; 
		check_ajax_referer( 'aw_batc_admin_nonce', 'aw_qa_nonce_ajax' );
		$html 	= '';
		$index	= 0;
		$existing_variation_id 	= '';
		if (isset($_POST['variation_id']) && !empty($_POST['variation_id'])) {
			$existing_variation_id = sanitize_text_field($_POST['variation_id']);
		}
		if (isset($_POST['existing_variation']) && !empty($_POST['existing_variation'])) {
			$existing_variation = sanitize_text_field($_POST['existing_variation']);
		}
		
		if (isset($_POST['product_id']) && isset($_POST['row_id'])) {

			$product_id = sanitize_text_field($_POST['product_id']);
			$product 	= wc_get_product($product_id);
			$row_id 	= sanitize_text_field($_POST['row_id']);	
			$tab_id_arr = explode('-', $row_id);
			$tab_id 	= end($tab_id_arr);

			$product_obj = new WC_Product_Factory();
			$product 	 = $product_obj->get_product($product_id);
			$post_meta 	 = get_post_meta($product_id, '_product_attributes', true);

			$existing_variation = $wpdb->get_var($wpdb->prepare("SELECT `variation` FROM  {$wpdb->prefix}product_list WHERE `tab_id` = %d", "{$tab_id}"));

			if (!empty($existing_variation)) {
				$existing_product = maybe_unserialize($existing_variation);
			} 
			$product_name 	= $product->get_name();
			/* Get variation of product */
			if ($product->is_type( 'variable' )) {
				$html = '<div class="wrap">
								<div class="aw-header">
									<h2>' . $product_name . '</h2> 
									<a href="#" alt="f158" onclick="batc_popup_close()" class="dashicons dashicons-no batc-popup-close"></a>
								</div>
								<div class="tablenav top popuptable">
									<div class="table-responsive">';

					$html .= '<table>';
					$first_push_array	= array();
					$iteration 	= 0 ;
					$prevoption = '';
					$option_selected = '';
					
				$attributes 	= $product->get_variation_attributes();
				$variations_id = array();
				$prevoption_val ='';
				foreach ($attributes as $attribute_name=> $variation) {
					$option = '';
					
					$label 		= wc_attribute_label( $attribute_name );
					$selected_val = '';
					$selected_val 	= ucfirst(get_post_meta($existing_variation_id, 'attribute_' . $attribute_name, true));
					if ('' != $selected_val) {
						$prevoption = $attribute_name;
						foreach ($variation as $vari) {
							$selected = '';
							$attribute_key = str_replace('pa_', '', $attribute_name);
							$attribute_key = ucfirst($attribute_key);

							if (isset($existing_product[$tab_id][$product_id][$attribute_key]) && 0 == strcasecmp($vari, $existing_product[$tab_id][$product_id][$attribute_key])) {
								$selected = 'selected="selected"';	
								$option_selected = ucfirst($vari);
								$prevoption_val  = $option_selected;
								$prevoption_val  = "'" . $prevoption_val . "'";
							}
							$option .= '<option ' . $selected . ' value="' . $vari . '">' . ucfirst($vari) . '</option>';
						}	
					} else {
						$selected_val = '';
						$attribute_key = str_replace('pa_', '', $attribute_name);
						$attribute_key = ucfirst($attribute_key);

						if (isset($existing_product[$tab_id][$product_id][$attribute_key])) {
							$selected_val 	=  $existing_product[$tab_id][$product_id][$attribute_key];

						}
						$option .=  aw_append_default_dropdown_attribute($product_id, $prevoption, $option_selected , $selected_val, 'admin');	
						if ('' == $option) {
							$variation = $attributes[$attribute_name];
							foreach ($variation as $vari) {
								$selected = '';
								$attribute_key = str_replace('pa_', '', $attribute_name);
								$attribute_key = ucfirst($attribute_key);
								if (isset($existing_product[$tab_id][$product_id][$attribute_key]) &&  0 == strcasecmp($vari, $existing_product[$tab_id][$product_id][$attribute_key])) { 
									$selected = 'selected="selected"';	
									$option_selected = ucfirst($vari);
								}
								$option .= '<option ' . $selected . ' value="' . $vari . '">' . ucfirst($vari) . '</option>';
							}
						}
					}
					
					$box_checked	= '';
					$existing_variation = $wpdb->get_var($wpdb->prepare("SELECT `variation` FROM  {$wpdb->prefix}product_list WHERE `tab_id` = %d", "{$tab_id}"));
					if (!empty($existing_variation)) {
						$existing_product = maybe_unserialize($existing_variation);
					}

					if ( isset($existing_product[$tab_id][$product_id]['Customer_can_edit_' . strtolower($attribute_name)]) && !strcmp(strtolower($attribute_name) , $existing_product[$tab_id][$product_id]['Customer_can_edit_' . strtolower($attribute_name)]) ) {
						$box_checked 	= 'checked=checked';
					}

						$html .=	'<tr class="variation_popup_tr">
										 	<td class="label"><label>' . ucfirst($label) . ':</label></td>
										 	<td>
										 		<select name="' . $attribute_name . '" class="popup_attribute attribute_dropdown-' . $index . '" id="selected-' . strtolower($attribute_name) . '" onchange="aw_selcted_variation_dropdown(this,' . $product_id . ',' . $prevoption_val . ')" >' . ucfirst($option) . '</select>
										 	</td>
										 	<td class="chk_can_edit"><input type="checkbox"  class="customer_can_edit" name="Customer_can_edit_' . strtolower($attribute_name) . '" value="' . strtolower($attribute_name) . '"  ' . $box_checked . '>
										 		<span>Can be edited by customer</span>
										 	</td>
										</tr>';
						$index++;				
						
				}

				$html .=			'</table>									
									</div>
									<div class="action-bottom">
										<input type="button" name="cancel_variation" class="button button-primary button-large batc-popup-close" value="Cancel">
										<input type="button" name="save_variation_setting" class="button button-primary button-large save_variation_setting" value="Save" data-productid=' . $product_id . ' data-value="' . $row_id . '" >
									</div>
								</div>	
					 </div>';
			}
		}
		echo wp_kses($html, wp_kses_allowed_html('post'));
		die;
	}

	/*
	 ** Ajax call tp append data into variation popup
	 */
	public static function aw_save_variation_setting_form_data() {

		global $wpdb; 
		check_ajax_referer( 'aw_batc_admin_nonce', 'aw_qa_nonce_ajax' );
		$product_list_table = $wpdb->prefix . 'product_list';
		$row 			= 0;
		$response 		= array();
		$response_value = array();
		$response_txt 	= '';
		$excerpt_txt 	= '';
		$variation 		= array();
		$variation_id   = ''; 
		$excerpt 		= '';
		$attributes 	= array();

		if (isset($_POST['action'])) {
			if (!empty($_POST['product_id']) && !empty($_POST['variation']) && !empty($_POST['row_id']) && !empty($_POST['quantity']) ) {

				$product_id 	= sanitize_text_field($_POST['product_id']);
				$quantity 		= sanitize_text_field($_POST['quantity']);
				$row_id 		= explode('-', sanitize_text_field($_POST['row_id']));
				$tab_id 		= end($row_id);
				$product_name 	= strtolower(str_replace(' ', '-', get_the_title($product_id)));

				$variation = json_encode($_POST);
				$variation = wp_unslash($variation);
				$variation = json_decode($variation, true);
				$variation = array_filter($variation['variation']);

				$product = new WC_Product_Variable( $product_id );
				$variations = $product->get_available_variations();
				$default_variation =  array_keys($product->get_attributes());

				$response_value = $variation;
				foreach ($default_variation as $key => $value) {
					$k = ucfirst(str_replace('pa_', '', $value));
					$attributes['attribute_' . $value] = strtolower($variation[$k]);

					$product_name 	.= '-' . strtolower($variation[$k]);
					$excerpt 		.= $k . ': ' . $variation[$k] . ', ';
					$response_txt 	.= $k . ' : ' . $variation[$k] . ' ';

					$variation_id = find_matching_product_variation_id($attributes , $product_id);
					if (0 == $variation_id) {
						$excerpt_val = rtrim($excerpt, ', ');
						$variation_id = aw_get_post_id_by_post_excerpt($excerpt_val , $product_id );
					} 

					$response_value['variation_id'] = $variation_id;
					$response['variation_id'] 		= $variation_id;
					
				}
		
				/* To check record exist fetch data from table */
				$existing_variation = $wpdb->get_var($wpdb->prepare("SELECT `variation` FROM  {$wpdb->prefix}product_list WHERE `tab_id` = %d", "{$tab_id}"));
				 

				if (!empty($existing_variation)) {
					$variation = maybe_unserialize($existing_variation);
					$variation[$tab_id][$product_id] = $response_value;
				} else {
					$variation[$tab_id][$product_id] = $response_value;
				}
				
				if (!empty($variation_id)) { 

					$decimalposition = get_option('woocommerce_price_num_decimals');

					$var_product= new WC_Product_Variation($variation_id);
					$image_tag 	= $var_product->get_image(array(30,30));
					$response['variation'] 		= $response_txt;
					$response['image_tag'] 		= $image_tag;
					$response['variation_val'] 	= $response_value;
					$response['price']			= aw_get_individual_product_price($variation_id );
					$total 						= $quantity*$response['price'];
					$response['totalamount'] 	= esc_html(sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), number_format($total, $decimalposition) ));
					$variation[$tab_id][$product_id] = $response_value;
				}
				 
				$update_arr = array('variation' => serialize($variation));
				$row = $wpdb->update($wpdb->prefix . 'product_list', $update_arr, array('tab_id'=>$tab_id));
			}
		} 
		echo json_encode($response);
		die;
	}

	/* Ajax call to get variation drop down value */
	public static function aw_ajax_filterVariations( $product_id = '', $key = '', $value = '') {
			global $wpdb;
			check_ajax_referer( 'aw_batc_admin_nonce', 'aw_qa_nonce_ajax' );
			$str = ''; 
			$selected_val = '';
		if (isset($_POST['key']) && isset($_POST['value']) && isset($_POST['product_id'])) {
			$product_id 	= sanitize_text_field($_POST['product_id']);
			$key 			= sanitize_text_field($_POST['key']);
			$selectedvalue 	= sanitize_text_field($_POST['value']);
			 
		}  
		if (isset($_POST['selected_val'])) {

			$selected_val = json_encode($_POST);
			$selected_val = wp_unslash($selected_val);
			$selected_val = json_decode($selected_val, true);
			$selected_val = array_filter($selected_val['selected_val']);
		}
			$key 			= 'attribute_' . $key;
			
			$product_obj 	= new WC_Product_Factory();
			$product 		= $product_obj->get_product($product_id);
			$attributes 	= $product->get_variation_attributes();
			$attribute_keys = array_keys($attributes);
			$attribute_keys = array_map('strtolower', $attribute_keys);
			$attribute_keys = array_map(function( $val) {
				return 'attribute_' . $val;
			} , $attribute_keys);

			$query = new WP_Query( array(
				'post_parent' => $product_id,
				'post_status' => 'publish',
				'post_type' => 'product_variation',
				'posts_per_page' => -1,
				'meta_query' => array(
					array(
						'key'   => $key, 
						'value' => $selectedvalue 
					),
				),
			) );
			$result = array();
		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->next_post();
				$object = $query->post;
				foreach ($attribute_keys as $attribute_name) {
					
					
					if ($attribute_name != $key) {

						$indexkey = str_replace('attribute_', '', $attribute_name);
						$value = get_post_meta($object->ID, $attribute_name, true);
						
						if ('' != $value) {
							$selected = '';
							if (0==strcasecmp($selected_val[$indexkey], $value)) {
								$selected = 'selected';
							}
							$str .= '<option ' . $selected . ' value="' . strtolower($value) . '">' . ucfirst($value) . '</option>';
						} else {
							$product_obj 	= new WC_Product_Factory();
							$product 		= $product_obj->get_product($product_id);
							$attribut_value = $product->get_attribute( $indexkey );
							$attribute 		= explodeX(array(',', '|'), $attribut_value);
							$array = array_map(function( $val) {
								return '<option data-value="any" value="' . strtolower(trim($val)) . '">' . trim($val) . '</option>';
							} , $attribute);

							$str .=  implode('', $array);
						}
						$result['options'] = $str;
						$result['appendto'] =$indexkey;
					}
				}
			}
			wp_reset_postdata();
		} 
		wp_reset_query();
		if (!empty($result)) {
			echo json_encode($result);	
		} else {
			echo '0';
		}
		
		die;
	}

	public static function aw_batc_list_column_register_sortable( $columns) {
		$columns['post_status'] 		= 'post_status';
		return $columns;
	}
	
	public static function aw_batc_list_column_made_sortable( $query) {
		/** OrderBy headers of listing grid **/
		if (! is_admin()) {
			return;
		}
		global $wpdb;

		$orderby = $query->get('orderby');
		$order = $query->get('order');

		if ('post_status' == $orderby) {
			$query->set( 'meta_key', 'post_status' );
			$query->set( 'orderby', 'meta_value' );
		}
	}
	
	public static function aw_bulk_product_list_register_my_bulk_actions( $bulk_actions) {
		$bulk_actions['publish']	= __('Published', 'publish');
		$bulk_actions['draft'] 		= __('Unpublished', 'draft');
		return $bulk_actions;
	}

	public static function aw_bulk_product_list_modified_views_post_status( $views) {
		/** Custom text for changing Draft To Unpublished Popup Pro Plugin **/
		if (isset($views['draft'])) {
			$views['draft'] = str_replace('Drafts', 'Unpublished', $views['draft']);
			$views['draft'] = str_replace('Draft', 'Unpublished', $views['draft']);
		}
		return $views;
	}
	
}
?>
