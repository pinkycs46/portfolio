<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class AwbatcProductShow {

	public function __construct() {
		 
	}

	public static function aw_batc_shortcode_display_product_list( $atts) {
		global $wpdb;
		$default = array(
						 'id' => '',
						);
		$array = shortcode_atts($default, $atts);

		$html = '';
		$decimalposition = get_option('woocommerce_price_num_decimals');

		if (!empty($array['id']) && 'publish' == get_post_status($array['id'])) {
			
			$post_id 	= $array['id'];
			$tabs 		= aw_get_alltab_product_list($post_id);

			$html = '<form action="' . wc_get_cart_url() . '" method="post" autocomplete="off">';
			$html .= '	<input type="hidden" value="addtocart_product" name="action">';
			if (!empty($tabs)) {
				$html .= '<div class="panel-body" id="alltabcontent">
							<div class="aw-cart-wrapper">
	    					<div class="tab" id="allmaintab">';
				foreach ($tabs as $key => $tab) {
					$html .= '<span class="tablinks active button" data-id="' . $key . '" id="aw-batc-list-tab-' . $key . '">' . $tab->tab_title . '</span>';

					if (empty($tab->quantity)) {
						$html = '';
						return $html;
					}
				}

				$html .= 	 '</div>';

				foreach ($tabs as $key => $tab) {

					if (0 == $key) {
						$html .= 	'<div id="aw-batc-list-tabcontent-<?php echo $key ?>" class="tabcontent">';
						$html .= 	'<table class="aw-batc-tbl">
		    							<thead>
			    							<tr>
					    						<th>
					    							<input type="checkbox" checked class="aw-allselect_chk" name="aw-allselect_chk" value="">
					    						</th>
					    						<th>Image</th>
					    						<th>Product Name</th>
					    						<th>Description</th>
					    						<th>Variations</th>
					    						<th>Quantity</th>
					    						<th>Price</th>
				    						</tr>
			    						</thead>';	

						$html .= 		'<tbody id="batc-list">';				
					}
					$productids = maybe_unserialize($tab->product_ids);
					$quantity	= maybe_unserialize($tab->quantity);
					$tab_id 	= $tab->tab_id;
					$variations	= maybe_unserialize($tab->variation);
					
					$image_tag 	= '';

					foreach ($productids as $k => $product_id) {

						if ('product' === get_post_type( $product_id )) {

							$product 			= wc_get_product($product_id);
							$product_name 		= get_the_title( $product_id );
							$description 		= $product->get_description();
							$stock 				= $product->get_stock_quantity();
							$description 		= wp_trim_words($description, 8, ' …' );
							$currency_symbol 	= get_woocommerce_currency_symbol();	
							$product_price		= 0;
							if ( '' != aw_get_individual_product_price($product_id)) {
								$product_price	= aw_get_individual_product_price($product_id);	
							}

							if ($product->is_type( 'variable')) {
								$row_id  	 = 'tr-' . $k . '-' . $tab_id;
								$variation_id 		= $variations[$tab_id][$product_id]['variation_id'];
								$var_product= new WC_Product_Variation($variation_id);
								$image_tag 	= $var_product->get_image(array(100,100));

								$index 			= 0 ;
								$iteration 		= 0;	
								$attributes 	= $product->get_variation_attributes();
		
								$i = 0;
								$prevoption = '';
								$option_selected= '';
								$td_variation 	= '';
								foreach ( $attributes as $attribute_name => $attr_variation ) {
									/* Below attached_value denoted actual value attribute value are attached  with product*/
									$variation_val = '';
									if (isset($attributes[$attribute_name])) {
										$attached_value = $attributes[$attribute_name];	
									}
									
									$allowed_key 	= 'Customer_can_edit_' . strtolower($attribute_name);
									$meta_key 		= 'attribute_' . $attribute_name;

									if ( isset($variations[$tab_id][$product_id][$allowed_key]) && strtolower($attribute_name) == $variations[$tab_id][$product_id][$allowed_key]) {									
										$selected 		= '';
										$label 			= wc_attribute_label( $attribute_name );
										$option 		= '';
										$selected_val 	= get_post_meta($variation_id, 'attribute_' . strtolower($attribute_name), true);
										
										$variation_id = $variations[$tab_id][$product_id]['variation_id'];

										$rowid = "'" . $row_id . "'";
										$att 	= "'" . strtolower($attribute_name) . "'";

										if ('' != $selected_val && '' == $prevoption) {

											$td_variation .= '<select id="selected-' . strtolower($attribute_name ) . '-tr-' . $k . '-' . $tab_id . '" onchange="change_variation_product(this, ' . $att . ', ' . $product_id . ', ' . $rowid . ')">';
											$selected_val =  trim($variations[$tab_id][$product_id][ucfirst($label)]);
											
											foreach ($attr_variation as $vari) {
												$selected = '';
												if (0 == strcasecmp($vari, $selected_val)) {
													$selected = 'selected';
													$option_selected 	= $selected_val;
													$prevoption			= $attribute_name;
												}
												$product_price		= 0;
												if ( '' != aw_get_individual_product_price($variation_id)) {
													$product_price	= aw_get_individual_product_price($variation_id);	
												}
												$td_variation .= '<option ' . $selected . ' value="' . $vari . '">' . $vari . '</option>'; 
											}	
											//$variation_val .= '<input type="hidden" id="any_variation_' . $row_id . '"  value="' . $selected_val . '" data-value="' . $attribute_name . '" class="any_variation_val" name="products[' . $k . '][' . $attribute_name . ']">';
											 
										} else {
											
											if ('' === get_post_meta($variation_id, 'attribute_' . strtolower($attribute_name), true)) {
												$prevoption ='';	
											}
											$option =  aw_append_default_dropdown_attribute($product_id, $prevoption, $option_selected , $selected_val, 'site');

											if ('' == $option) {
												$td_variation .= '<select class="any_variation_name" onchange="seelct_any_variation(this,' . $rowid . ');">';
												$selected_val 	=  $variations[$tab_id][$product_id][$label];
												$variation 		= $attributes[$attribute_name];
												foreach ($variation as $vari) {
													$selected = '';
													if (0 == strcasecmp($vari, $selected_val)) {
														$selected = 'selected="selected"';
													}
													$option .= '<option ' . $selected . ' value="' . $vari . '">' . $vari . '</option>';
												}
											} else {

												$td_variation .= '<select id="selected-' . strtolower($attribute_name) . '-tr-' . $k . '-' . $tab_id . '" onchange="change_variation_product(this, ' . $att . ', ' . $product_id . ', ' . $rowid . ')">';
											}
											
											$td_variation .= $option ;
										}
										$prevoption = '';
										$td_variation .= '</select>';
										$variation_val .= '<input type="hidden" id="any_variation_' . $row_id . '"  value="' . $selected_val . '" data-value="' . $attribute_name . '" class="any_variation_val" name="products[' . $k . '][' . $attribute_name . ']">';

									} else {

										$variation_val 		= '';
										$meta_value 		= ucfirst(get_post_meta($variation_id, $meta_key, true));
										$product_price		= 0;
										if ( '' != aw_get_individual_product_price($variation_id)) {
											$product_price	= aw_get_individual_product_price($variation_id);	
										}
										$prevoption 		= $attribute_name;
										$option_selected 	= $meta_value;
										$label 				= ucfirst( str_replace('pa_', '', $attribute_name) );

										if (''!=$meta_value) {
											$td_variation .= '<div class="aw-variation">' . $label . ': ' . ucfirst($meta_value) . '</div> '; 	
										} else {

											if (isset($variations[$tab_id][$product_id][$label])) {
												$meta_value = $variations[$tab_id][$product_id][$label];
												$td_variation .= $label . ': ' . ucfirst($meta_value) . ',<br/> ';
												$variation_val .= '<input type="hidden" id="any_variation_' . $row_id . '"  value="' . $meta_value . '" data-value="' . $attribute_name . '" class="any_variation_val" name="products[' . $k . '][' . $attribute_name . ']">';
											}
											
										}
									}
								}
							} else {
								$variation_id 	= '';
								$variation_val	= '';
								$td_variation 	= '';
								$image_url 		= wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'single-post-thumbnail' );

								if (empty($image_url)) {
									$string = wc_placeholder_img_src( 30 );
									$image_tag = '<img width="100" height="100" src="' . $string . '" class="attachment-100x100 size-100x100" alt="">';
								} else {
									$image_tag = '<img width="100" height="100" src="' . $image_url[0] . '" class="attachment-100x100 size-100x100" alt="">';
								}
							}

							$total_price 	= $product_price * $quantity[$k];

							$row_id  	 = 'tr-' . $k . '-' . $tab_id;
							
							$sold_individually = $product->get_sold_individually();
							
							if ('1' != $sold_individually) {
								//$sold_individually_html = '<input type="text" class="batcquantity" id="quantity-' . $row_id . '" value="' . $quantity[$k] . '" style="width: 40px;"  name="products[' . $k . '][quantity]"><span class="errormsg errormsg-' . $k . '"><span>';
								$sold_individually_html = '<input type="number" step="1" min="1" max="' . $stock . '" size="2" style="width: 60px;" inputmode="numeric" onpaste="return false;" class="batcquantity" id="quantity-' . $row_id . '" value="' . $quantity[$k] . '" name="products[' . $k . '][quantity]" onkeypress="return aw_batc_public_checkIt(event,false)">';
							} else {
								
								$sold_individually_html = '1<input type="hidden" class="batcquantity" id="quantity-' . $row_id . '" value="1" name="products[' . $k . '][quantity]"><span class="errormsg errormsg-' . $k . '"><span>';
							}

							$woo_comm_show_out_of_stock_prod = '';

							if ('yes' === get_option( 'woocommerce_hide_out_of_stock_items')) {
								$woo_comm_show_out_of_stock_prod = 'yes';
							}

							$stock_status 	= $product->get_stock_status();

							$html_of_box	= '';
							$html_of_stock	= '';
							$msg_of_stock	= '';

							$post_status	= get_post_status($product_id);

							if ('hidden' == $product->get_catalog_visibility() || ( 'private' == $post_status && !current_user_can('administrator') ) ) {
								$html .= '';
							} else {
								if ('yes' != $woo_comm_show_out_of_stock_prod || 'outofstock' != $stock_status) {
								   
									$html .= '<tr class="' . $row_id . '">';
									if ('outofstock' == $stock_status) {
										$html .= '<td class="aw-batc-prod-checkbox">&nbsp;</td>';
									} else {
										$html .= '
									<td class="aw-batc-prod-checkbox"><input type="checkbox" class="prod_checkbox" checked id="cb-select-' . $product_id . '" name="checked_product[' . $k . '][product_id]" value="' . $product_id . '"></td>';
									}
									$html .= '
									<td class="aw-batc-prod-img product-image-' . $row_id . '"  data-colname="Image" >' . $image_tag . '</td>
									<td class="aw-batc-prod-name" data-colname="Product Name">' . $product_name . '</td>
									<td class="aw-batc-prod-dsp" data-colname="Product Description">' . $description . '</td>';
									if ('outofstock' == $stock_status) {
										$html .= '<td class="aw-batc-prod-oos" data-colname="Info" colspan="3"><p class="stock out-of-stock">Out of stock</p></td>';
									} else {
										$html .= '
									<td class="aw-batc-prod-attr" data-colname="Variation">' . $td_variation . '<input type="hidden"  value="' . $product_id . '"  class="product_id" name="products[' . $k . '][product_id]"><input type="hidden"  value="' . $variation_id . '"  class="variation_id" id="variation_id_' . $row_id . '" name="products[' . $k . '][variation_id]">' . $variation_val . '</td>
									<td class="aw-batc-prod-qty" data-colname="Quantity">' . $sold_individually_html . '</td>
									<td class="aw-batc-prod-price price" data-colname="Price"><span class="totalamount" data-rowtotal="' . $total_price . '" id="rowtotalamt-' . $row_id . '">' . esc_html(sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), number_format($total_price, $decimalposition) )) . '</span><input type="hidden" id="product_price-' . $row_id . '" class="product_price" value="' . $product_price . '">
									<input type="hidden" name="price" class="totalamount" id="row_total-' . $row_id . '"  value="' . $total_price . '" /><br/>
									</td>';
									}
									$html .= '</tr>';
								}
							}	
					
						}	
					}
				}
				$html .= 			'</tbody>';	
				$html .=		'</table>	
		    					</div>';
				
				$html .= '<div class="aw-cart-actions">';  
				$html .= '<div class="aw-cart-actions-total">Total for selected items : <span class="overallsummation"></span></div>';	
				$html .= '<div class="aw-cart-actions-button">';

				if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
					$current_url = esc_url_raw( add_query_arg( 'add-to-cart=', '' , wc_get_cart_url() ) );  
					$html .= '<input type="hidden" class="redirect_to_cart" value="yes" name="redirect_to_cart">
					<input type="hidden" class="cart_page_url" value="' . wc_get_cart_url() . '" name="cart_page_url">
					<button class="added_products_ids button" type="submit">Add All Selected To Cart</button>';	
				} else {
					$html .= '<input type="hidden" class="redirect_to_cart" value="no" name="redirect_to_cart"><button type="submit" name="add-to-cart" value="woocommerce_cart_no_redirect_after_add" class="button product_type_simple add_to_cart_button ajax_add_to_cart batc_add_to_cart_button ">Add All Selected To Cart</button>&nbsp;<a href="' . wc_get_cart_url() . '"  class="added_to_cart wc-forward hidecartbutton" title="View cart">View cart</a> </div>';
				}
				$html .= '</div>
					</div>';
				$html .= '</div>'; //</div> // PANEL BODY END
			}
		}
		$html .= '</form>';
		return $html ;
	}


 
	public static function aw_add_multiple_products_to_cart() {

		$redirect_to_cart = '';
		if (isset($_POST['redirect_to_cart'])) {
			$redirect_to_cart = sanitize_text_field($_POST['redirect_to_cart']);
		}
		if ('no' == $redirect_to_cart) {
			check_ajax_referer( 'awbatcproductsave_nonce', 'nonce_batc_ajax' );
		}
		if (isset($_POST['products'])) {
			$product_detail = json_encode($_POST);
			$product_detail = wp_unslash($product_detail);
			$product_detail = json_decode($product_detail, true);
			$product_detail = array_filter($product_detail['products']);
		}

		if (!empty($product_detail)) {

			$variation = array();
			$cart_variation = array();
			foreach ( $product_detail as $key=> $detail ) {
			
				$variation_id 	= null;
				$quantity 		= 0;
				$cart_variation = null;
				if (!empty($detail['product_id'])) {
					$product_id = $detail['product_id'];

				}
				if (!empty($detail['quantity'])) {
					$quantity = $detail['quantity'];
				}	
				$product = wc_get_product($product_id);			
				if (!empty($detail['variation_id']) && $product->is_type( 'variable' )) {
				 
					$variation_id = $detail['variation_id'];
					$variation = wc_get_product($variation_id);
					$variation_attributes = $variation->get_variation_attributes();
					unset($detail['variation_id']);
					unset($detail['quantity']);
					unset($detail['product_id']);

					foreach ($variation_attributes as $key=> $attribute) {

						if (empty($attribute)) {

							$key 	= str_replace('attribute_', '', $key);
							$label 	= wc_attribute_label($key);
							
							$key = array_keys($detail);
							if (!empty($key)) {
								if (isset($detail[$key[0]])) {
									$label 	= wc_attribute_label($key[0]);
									$cart_variation[$label] = $detail[$key[0]];
								}
								
							}
						}
					}
				}
				if ($product->is_type( 'grouped' )) {
					$children_id = get_post_meta($product_id, '_children' , true);
					if (!empty($children_id)) {
						foreach ($children_id as $child_id) {
							self::aw_add_to_cart ( $child_id, $quantity );
						}	
					}
				} else {
					self::aw_add_to_cart ( $product_id, $quantity, $variation_id, $cart_variation );	
				}
			}
		}

		if (isset($_POST['cart_page_url']) && 'yes' == $redirect_to_cart) { 
			$cart_url = sanitize_text_field($_POST['cart_page_url']);
			wp_redirect( $cart_url );
		}
		if ('no' == $redirect_to_cart) { 
			echo esc_html($redirect_to_cart);
			die;
		}
	}

	public static function aw_add_to_cart ( $product_id, $quantity, $variation_id = null, $cart_variation = null) {

		$cart = WC()->cart->get_cart();
		$product_cart_id = WC()->cart->generate_cart_id( $product_id );
		if ( ! WC()->cart->find_product_in_cart( $product_cart_id ) ) {
			WC()->cart->add_to_cart( $product_id, $quantity, $variation_id , $cart_variation);
		} else {
			WC()->cart->add_to_cart( $product_id, $quantity, $variation_id , $cart_variation);
		}
	} 

	/* Ajax call to get variation drop down value */
	public static function aw_ajax_filterVariations_front( $product_id = '', $key = '', $value = '') {
		global $wpdb;
		$decimalposition = get_option('woocommerce_price_num_decimals');
		check_ajax_referer( 'awbatcproductsave_nonce', 'nonce_batc_ajax' );
		$str = ''; 
		if (isset($_POST['key']) && isset($_POST['value']) && isset($_POST['product_id']) && isset($_POST['quantity']) && isset($_POST['exist_vid'])) {
			$product_id 	= sanitize_text_field($_POST['product_id']);
			$key 			= sanitize_text_field($_POST['key']);
			$value 			= sanitize_text_field($_POST['value']);
			$quantity		= sanitize_text_field($_POST['quantity']);
			$exist_vid 		= sanitize_text_field($_POST['exist_vid']);	
		}  

		$vkey 			= 'attribute_' . $key;
		$product_obj 	= new WC_Product_Factory();
		$product 		= $product_obj->get_product($product_id);
		$attributes 	= $product->get_variation_attributes();
		$attribute_keys = array_keys($attributes);
		$attribute_keys = array_map('strtolower', $attribute_keys);
		$attribute_keys = array_map(function( $val) {
			return 'attribute_' . $val;
		} , $attribute_keys);

		$variation_arr =  array(
						'key'   => $vkey, 
						'value' => $value 
					);
		wp_reset_postdata();
		wp_reset_query();
		$query = new WP_Query( array(
		'post_parent' => $product_id,
		'post_status' => 'publish',
		'post_type' => 'product_variation',
		'posts_per_page' => -1,
		'meta_query' => array(
							$variation_arr
						),
		) );

		$result = array();
		if ($query->have_posts()) {
			$k = 0;
			while ($query->have_posts()) {
				$query->next_post();
				$object = $query->post;
				$selected = '';
				foreach ($attribute_keys as  $attribute_name) {
					$value 		= get_post_meta($object->ID, $attribute_name, true);
					$atrr[$attribute_name] = $value;
					$vid = find_matching_product_variation_id( $atrr, $product_id);
				}
				if (count($attribute_keys)>1) {
					foreach ($attribute_keys as  $attribute_name) {
						if ($attribute_name != $vkey) {
							$indexkey 	= str_replace('attribute_', '', $attribute_name);
							$value 		= get_post_meta($object->ID, $attribute_name, true);
							if ('' != $value) {
								$selected = '';
								if (0 == $k) {
									//$selected 	= 'selected';
									$value 		= get_post_meta($object->ID, $attribute_name, true);
									$product_variation = new WC_Product_Variation( $object->ID );
									$image_tag 	= $product_variation->get_image(array(100,100));
									$price 		= aw_get_individual_product_price($object->ID);
									$result['variation_id'] = $object->ID;
								}
								$exitvalue 		= get_post_meta($exist_vid, $attribute_name, true);
								if ($exitvalue == $value) {
									$selected 	= 'selected';
									$atrr[$attribute_name] = $exitvalue;
									$vid = find_matching_product_variation_id( $atrr, $product_id);
									$result['variation_id']= $vid;

									$product_variation = new WC_Product_Variation( $object->ID );
									$image_tag = $product_variation->get_image(array(100,100));
									$price 			= aw_get_individual_product_price($object->ID);
									
								}
								if ($vid == $object->ID ) {
									$str .= '<option ' . $selected . ' value="' . strtolower($value) . '">' . $value . '</option>';
								}

							} else {

								$product_variation = new WC_Product_Variation( $object->ID );
								$image_tag = $product_variation->get_image(array(100,100));
								$price 			= aw_get_individual_product_price($object->ID);
								$product_obj 	= new WC_Product_Factory();
								$product 		= $product_obj->get_product($product_id);
								$attribut_value = $product->get_attribute( $indexkey );
								$attribute 		= explodeX(array(',', '|'), $attribut_value);
								$array 			= array_map(function( $val) {
													return '<option data-value="any" value="' . strtolower(trim($val)) . '">' . trim($val) . '</option>';
								} , $attribute);

								$str .=  implode('', $array);
								$result['variation_id'] = $object->ID;

							}
							$result['options'] 		= $str;
							$result['appendto'] 	= $indexkey;
							$result['image_tag'] 	= $image_tag;
						
							$result['price'] 		= $price;
							$result['rowtotal'] 	= html_entity_decode(sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), number_format($price*$quantity, $decimalposition) ));
							$k++;
						}
					}
				} else {
					
					$indexkey 		= str_replace('attribute_', '', $vkey);
					$selected_val 	= get_post_meta($object->ID, $vkey, true);
					$selected = '';
					if (isset($attributes[$key])) {
						$data = $attributes[$key];
					} else {
						$data = $attributes[ucfirst($key)];
					}
					foreach ($data as  $value) {
						if ($value == $selected_val) {
							$selected 	= 'selected';
						}
						$str .= '<option ' . $selected . ' value="' . strtolower($value) . '">' . $value . '</option>';
						$selected  ='';
					}

					$product_variation 		= new WC_Product_Variation( $object->ID );
					$image_tag 				= $product_variation->get_image(array(100,100));
					$price 					= aw_get_individual_product_price($object->ID);
					$result['price'] 		= $price;
					$result['options'] 		= $str;
					$result['appendto'] 	= $indexkey;
					$result['image_tag'] 	= $image_tag;
					$result['rowtotal'] 	= html_entity_decode(sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), number_format($price*$quantity, $decimalposition) ));
					$result['variation_id'] = $object->ID;
				}
			}
			wp_reset_postdata();
		} 
		wp_reset_query();

		echo json_encode($result);
		die;
	}
} 
