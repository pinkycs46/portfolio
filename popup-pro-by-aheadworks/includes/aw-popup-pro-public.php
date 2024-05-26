<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class AwPopupProPublic {

	public static function aw_popup_pro_add_to_cart( $product_id) {
		/** Prepare Upsell and Related Product Popups **/

		$product_count = '';
		$upsell_arr = array();
		$related_arr = array();
		$popup = array();
		$upsells_ids = array();

		if (is_array($product_id)) {
			$flag = $product_id[1];
			$product_id = $product_id[0];
			$html = array();
		} else {
			$flag = 0;
			if (isset($_REQUEST['add-to-cart'])) {
				$add_to_cart = sanitize_text_field($_REQUEST['add-to-cart']);
			} else {
				$add_to_cart = false;
			}
			$product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($add_to_cart));
		}

		if ( 0 == $product_id|| !class_exists( 'woocommerce' )) {
			return;
		}

		$product = wc_get_product($product_id);

		if ($product_id) {
			$upsells_ids = $product->get_upsell_ids();
		}

		if (count($upsells_ids) > 0) {
			$upsell_arr = array(
				'key'		=> 'popup_pro_type_display',
				'value'		=> 'upsells',
				'compare'	=> '='
			);
		}

		$related_ids = wc_get_related_products($product_id);
		if (count($related_ids) > 0) {
			$related_arr = array(
				'key'		=> 'popup_pro_type_display',
				'value'		=> 'relatedproducts',
				'compare'	=> '='
			);
		}

		if (( count($related_ids) < 1 ) && ( count($upsells_ids) < 1 )) {
			return;
		}

		$args = array(
			'post_type' 	=> 'popup-pro', 
			'posts_per_page'=> -1,
			'post_status' 	=> 'publish',
			'meta_key' 		=> 'popup_pro_priority',
			'orderby' 		=> 'meta_value',
			'order' 		=> 'ASC',
			'meta_query' 	=> 
			array(
				'relation' 	=> 'OR',
				$upsell_arr,
				$related_arr,
			)
		);
		$loop = new WP_Query($args);

		if (!$loop->have_posts()) {
			return;
		}

		while ($loop->have_posts()) :
			$loop->the_post();

			$cookie_name = 'popup_pro_cookie_' . get_the_ID();

			if (!isset($_COOKIE[$cookie_name])) {
				$popup[] = array(
				'id'				=> get_the_ID(),
				'title'				=> get_post_meta(get_the_ID(), 'popup_pro_title', true),
				'type'				=> get_post_meta(get_the_ID(), 'popup_pro_type_display', true),
				'maximum_product'	=> get_post_meta(get_the_ID(), 'popup_pro_maximum_product', true),
				'cookie_lifetime'	=> get_post_meta(get_the_ID(), 'popup_pro_cookie_lifetime', true),
				'priority'			=> get_post_meta(get_the_ID(), 'popup_pro_priority', true)
				);
			}

		endwhile;
		wp_reset_query();

		$num_popup = count($popup);
		$title = '';
		$show_popup = '';
		$scr = '';
		$i = 1;
		if ($num_popup > 0) {
			foreach ($popup as $popups) {
				$popup_type = $popups['type'];
				$post_id	= $popups['id'];
				$cookie_lifetime = $popups['cookie_lifetime'];

				aw_popup_pro_cookies($post_id, $cookie_lifetime);

				if ('upsells' == $popup_type) {
					$upsell_product = array();

					if (( 0 != $popups['maximum_product'] ) && ( count($upsells_ids) >= $popups['maximum_product'] )) {
						$random_upsells_ids = self::aw_getRandomArrayElement($upsells_ids, $popups['maximum_product']);
						$upsells_ids = $random_upsells_ids;
					}

					foreach ($upsells_ids as $get_upsell_product) {
						$get_upsell_product_details = wc_get_product($get_upsell_product);

						$upsell_product[] = array(
							'id'			=> $get_upsell_product,
							'name' 			=> $get_upsell_product_details->get_name(),
							'price' 		=> $get_upsell_product_details->get_price(),
							'sku'           => $get_upsell_product_details->get_sku(),
							'regular_price' => $get_upsell_product_details->get_regular_price(),
							'image'			=> wp_get_attachment_image_url($get_upsell_product_details->get_image_id()),
							'stock_status'	=> $get_upsell_product_details->get_stock_status(),
							'type'			=> $get_upsell_product_details->get_type(),
							'url' 			=> get_permalink($get_upsell_product)
						);
					}
					$product_count = count($upsell_product);

					$title = $popups['title'];

					if (0 == $flag) {
						add_action('wp_footer', function() use ( $upsell_product, $post_id, $popup_type, $num_popup, $flag) {
							AwPopupProPublic::aw_show_tb($upsell_product, $popup_type, $post_id, $num_popup, $flag);
						});
					} else {
						$html[] = self::aw_show_tb($upsell_product, $popup_type, $post_id, $num_popup, $flag) . '::' . $popup_type . '::' . $post_id . '::' . $title . '::' . $num_popup . '::' . $product_count;
					}
				}

				if ('relatedproducts' == $popup_type) {
					if ($popups['maximum_product']>0) {
						$related_ids = wc_get_related_products($product_id, $popups['maximum_product']);
					} else {
						$limit = $popups['maximum_product'];
						if (0==$limit) {
							$limit = 1000;
						}
						$related_ids = wc_get_related_products($product_id, $limit);
					}

					$related_product = array();

					foreach ($related_ids as $get_related_product) {
						$get_related_product_details = wc_get_product($get_related_product);

						$related_product[] = array(
							'id'			=> $get_related_product,
							'name' 			=> $get_related_product_details->get_name(),
							'price' 		=> $get_related_product_details->get_price(),
							'sku'           => $get_related_product_details->get_sku(),
							'regular_price' => $get_related_product_details->get_regular_price(),
							'image'			=> wp_get_attachment_image_url($get_related_product_details->get_image_id()),
							'stock_status'	=> $get_related_product_details->get_stock_status(),
							'type'			=> $get_related_product_details->get_type(),
							'url' 			=> get_permalink($get_related_product)
						);
					}
					$product_count = count($related_product);

					$title = $popups['title'];

					if (0 == $flag) {
						add_action('wp_footer', function() use ( $related_product, $post_id, $popup_type, $num_popup, $flag) {
							AwPopupProPublic::aw_show_tb($related_product, $popup_type, $post_id, $num_popup, $flag);
						});
					} else {
						$html[] = self::aw_show_tb($related_product, $popup_type, $post_id, $num_popup, $flag) . '::' . $popup_type . '::' . $post_id . '::' . $title . '::' . $num_popup . '::' . $product_count;
					}
				}

				$show_popup .= 'window.' . $popup_type . '_' . $post_id . '_' . $flag . ' = function(){';
				$show_popup .= "setTimeout(function()
								{
									tb_show('" . $title . "', '#TB_inline?inlineId=popup-pro-content-" . $popup_type . '_' . $post_id . '_' . $flag . "',null);
								}, 1000);";
				$show_popup .= '}';
				$show_popup .= "\n";

				$show_popup .= "\n";

				if ($num_popup > 1 && 1 == $flag) {
					if ( 2 == $i) {
						$scr .= "jQuery( 'body' ).on( 'thickbox:removed', function() {";
						$scr .= "\n";
					}
					$scr .= "\n";
					if ($i >= 2) {
						$scr .= "if(jQuery('#tb_unload_count_" . $post_id . '_' . $flag . "').val() == 'show')
							{
								" . $popup_type . '_' . $post_id . '_' . $flag . "();
								resize_tb('" . $product_count . "');
								jQuery('#tb_unload_count_" . $post_id . '_' . $flag . "').val('hide');
								return false;
							}";
					}
					$scr .= "\n";
					if ($i == $num_popup) {
						$scr .= '});';
						$scr .= "\n";
					}
				}
				$i++;
			}
		}
		if (0 == $flag) {
			add_action('wp_head', function() use ( $show_popup, $scr, $num_popup, $product_count) {
				AwPopupProPublic::aw_add_tb($show_popup, $scr, $num_popup, $product_count);
			});
		} else {
			return json_encode($html, JSON_FORCE_OBJECT);
		}
	}

	public static function aw_popup_pro_cart_checkout() {
		/** Prepare Crosssell Product Popups **/
		if (( is_cart() || Is_checkout() ) && ( class_exists( 'woocommerce' )  && !is_admin() )) {
			$popup = array();

			$args = array(
			'post_type' 	=> 'popup-pro', 
			'posts_per_page'=> -1,
			'post_status' 	=> 'publish',
			'meta_key' 		=> 'popup_pro_priority',
			'orderby' 		=> 'meta_value',
			'order' 		=> 'ASC',
			'meta_query' 	=> 
				array(
					'key'		=> 'popup_pro_type_display',
					'value'		=> 'crosssells',
					'compare'	=> '='
				)
			);

			$loop = new WP_Query($args);

			if (!$loop->have_posts()) {
				return;
			}

			while ($loop->have_posts()) :
				$loop->the_post();

				$cookie_name = 'popup_pro_cookie_' . get_the_ID();

				if (!isset($_COOKIE[$cookie_name])) {
					$popup[] = array(
					'id'				=> get_the_ID(),
					'title'				=> get_post_meta(get_the_ID(), 'popup_pro_title', true),
					'type'				=> get_post_meta(get_the_ID(), 'popup_pro_type_display', true),
					'maximum_product'	=> get_post_meta(get_the_ID(), 'popup_pro_maximum_product', true),
					'cookie_lifetime'	=> get_post_meta(get_the_ID(), 'popup_pro_cookie_lifetime', true),
					'priority'			=> get_post_meta(get_the_ID(), 'popup_pro_priority', true)
					);
				}
			endwhile;
			wp_reset_query();

			$num_popup = count($popup);
			$show_popup = '';
			$scr = '';
			$i = 1;
			$flag = 0;

			$crosssel_ids = array();	
			if (( count(WC()->cart->get_cart()) > 0 ) && ( $num_popup > 0 )) {
				foreach (WC()->cart->get_cart() as $cart_item) {
					$product_id = $cart_item['product_id'];
					if (count(get_post_meta($product_id, '_crosssell_ids')) > 0) {
						$crosssel_ids = array_unique(array_merge(get_post_meta($product_id, '_crosssell_ids', true), $crosssel_ids));
					}
				}
			}

			if (count($crosssel_ids) > 0) {
				foreach ($popup as $popups) {
					$popup_type = $popups['type'];
					$post_id	= $popups['id'];
					$cookie_lifetime = $popups['cookie_lifetime'];

					aw_popup_pro_cookies($post_id, $cookie_lifetime);

					$crosssell_product = array();

					if (( 0 != $popups['maximum_product'] ) && ( count($crosssel_ids) >= $popups['maximum_product'] )) { 
						$random_crosssell_ids = self::aw_getRandomArrayElement($crosssel_ids, $popups['maximum_product']);
						$crosssel_ids = $random_crosssell_ids; 
					}

					foreach ($crosssel_ids as $get_crosssell_product) {
						$get_crosssell_product_details = wc_get_product($get_crosssell_product);
						$crosssell_product[] = array(
							'id'			=> $get_crosssell_product,
							'name' 			=> $get_crosssell_product_details->get_name(),
							'price' 		=> $get_crosssell_product_details->get_price(),
							'regular_price' => $get_crosssell_product_details->get_regular_price(),
							'image'			=> wp_get_attachment_image_url($get_crosssell_product_details->get_image_id()),
							'stock_status'	=> $get_crosssell_product_details->get_stock_status(),
							'type'			=> $get_crosssell_product_details->get_type(),
							'url' 			=> get_permalink($get_crosssell_product)
						);
					}
					$title = $popups['title'];
					add_action('wp_footer', function() use ( $crosssell_product, $post_id, $popup_type, $num_popup, $flag) {
						AwPopupProPublic::aw_show_tb($crosssell_product, $popup_type, $post_id, $num_popup, $flag);
					});

					$product_count = count($crosssell_product);

					$show_popup .= 'window.' . $popup_type . '_' . $post_id . '_' . $flag . ' = function(){';
					$show_popup .= "setTimeout(function()
									{
										tb_show('" . $title . "', '#TB_inline?inlineId=popup-pro-content-" . $popup_type . '_' . $post_id . '_' . $flag . "',null);
									}, 1000);";
					$show_popup .= '}';
					$show_popup .= "\n";
	
					$show_popup .= "\n";

					if ($num_popup > 1 && 1 == $flag) {
						if (2 == $i) {
							$scr .= "jQuery( 'body' ).on( 'thickbox:removed', function() {";
							$scr .= "\n";
						}
						$scr .= "\n";
						if ($i >= 2) {
							$scr .= "if(jQuery('#tb_unload_count_" . $post_id . '_' . $flag . "').val() == 'show')
							{
								" . $popup_type . '_' . $post_id . '_' . $flag . "();
								resize_tb('" . $product_count . "');
								jQuery('#tb_unload_count_" . $post_id . '_' . $flag . "').val('hide');
								return false;
							}";
						}
						$scr .= "\n";
						if ($i == $num_popup) {
							$scr .= '});';
							$scr .= "\n";
						}
					}
					$i++;
				}
				add_action('wp_head', function() use ( $show_popup, $scr, $num_popup, $product_count) {
					AwPopupProPublic::aw_add_tb($show_popup, $scr, $num_popup, $product_count);
				});
			}
		}
	}

	public static function aw_add_tb( $show_popup, $scr, $num_popup, $product_count) {
		/** Add thickbox, wordpress way to open Linked Product Popup **/
		add_thickbox();
		?>
		<script language="javascript">
			jQuery(document).ready(function()
			{
				<?php echo wp_kses_post($show_popup . $scr); ?>
			});
		</script>
		<?php
	}

	public static function aw_show_tb( $product, $type, $post_id, $num_popup, $flag) {
		/** Display Linked Product Popup on page load **/
		$html = '';
		$html.= '<div class="popup-pro-show-tb" id="popup-pro-content-' . $type . '_' . $post_id . '_' . $flag . '" style="display:none;">';
		$html.= '<div class="popup-pro-main-dv" id="popup-pro-main-dv">';
		$popup_pro_views = get_post_meta($post_id, 'popup_pro_views', true);
		//$popup_pro_views = $popup_pro_views + 1;
		$popup_pro_views++;
		update_post_meta($post_id, 'popup_pro_views', $popup_pro_views);
		$wid = 'auto';

		$i = 1;
		foreach ($product as $products) {
			$html.= '<div class="popup-pro-show-tb_prod">
					<div class="popup-pro-show-tb_imgage"><img src="' . $products['image'] . '"/></div>
					<div class="popup-pro-show-tb_name">' . $products['name'] . '</div>
					<div class="popup-pro-show-tb_price">' . get_woocommerce_currency_symbol() . ' ' . $products['price'] . '</div>
					<div class="popup-pro-show-tb_cart">';
			if ('outofstock' == $products['stock_status']) {
				$html.= '<span>Out Of Stock</span>';
			} elseif ('variable' == $products['type']) {
				$html.= '<a href="' . $products['url'] . '" class="popup-pro-show-tb_btn">Select Options</a>';
			} else {
				$url = get_site_url();
				$product_id = $products['id'];
				$add_to_cart = do_shortcode('[add_to_cart_url id="' . $product_id . '"]');
				$html.='<a data-prevent="true" data-quantity="1" class="popup-pro-show-tb_btn add_to_cart_button ajax_add_to_cart" data-product_id="' . $product_id . '" data-popup-id="' . $post_id . '" href="' . $add_to_cart . '" >Add To Cart</a>';
			}
			$html.= '</div>
					</div>';
			$i++;
		}
		if (0 == $flag) {
			$html.=   '<input type="hidden" name="allpopup[]" class="current_popup_id" value="linked::' . $type . '_' . $post_id . '_' . $flag . '::' . count($product) . '::' . $post_id . '">';
		}
		$html.=   '<input type="hidden" name="link_tb_unload_count_' . $post_id . '" id="tb_unload_count_' . $post_id . '_' . $flag . '" value="show">
			</div></div>';
		if (0 == $flag) {
			echo wp_kses($html, wp_kses_allowed_html('post'));
		} else {
			return $html;
		}
	}

	public static function aw_getRandomArrayElement( $array, $limit) {
		/** Callback action for randomizing Upsell, Crosssell and Related Product Array **/
		if ($limit > 1 && count($array) > 1) {
			$randomIndex = array_rand($array, $limit);
			foreach ($randomIndex as $randomVal) {
				$randomElement[] = $array[$randomVal];
			}
		} else {
			$randomIndex = array_rand($array);
			$randomElement[] = $array[$randomIndex];
		}
		return $randomElement;
	}

	public static function aw_popup_div_for_append() { 
		/** Trigger to display Linked Product Popup on Ajax Add To Cart Button click **/
		add_thickbox();
		?>
		 
		<div id="popup-pro-show-tb_prod_jquery"></div>
		<div id="popup-pro-show-tb_prod_ajx"></div>
		
		<?php
	}

	public static function aw_popup_pro_product_add_to_cart_ajx( $product_id) {
		/** Prepare display of Linked Product Popup on Ajax Add To Cart Button click **/
		$product_id = array();
		$product_id[0]	= 0;
		if (isset($_GET['product_id'])) {
			$product_id[0] = wp_kses_post($_GET['product_id']);
		}

		$product_id[1]= 1;
		$html = self::aw_popup_pro_add_to_cart($product_id);
		echo esc_html($html);
		//echo wp_kses($html, wp_kses_allowed_html('post'));
		wp_die();
	}
}

function aw_popup_pro_product_add_to_cart() {
	/** Update Linked Product Popup Clicks **/
	if (isset($_GET['product_id']) && isset($_GET['popup_id'])) {
		$product_id = sanitize_text_field($_GET['product_id']);
		$popup_id 	= sanitize_text_field($_GET['popup_id']);
		$popup_pro_clicks = get_post_meta($popup_id, 'popup_pro_clicks', true);
		//$popup_pro_clicks = $popup_pro_clicks + 1;
		$popup_pro_clicks++;
		update_post_meta($popup_id, 'popup_pro_clicks', $popup_pro_clicks);
		echo 1;
	} else {
		echo 0;
	}
	wp_die();
}
?>
