<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

$awgiftcart 	= new AwGiftCardCartPage();

class AwGiftCardCartPage {
	public static $aw_giftcard_settings;
	public function __construct() {

		self::$aw_giftcard_settings = maybe_unserialize(get_option('aw_wgc_configuration'));
		
		add_action( 'woocommerce_get_price_html', array('AwGiftCardCartPage','aw_giftcard_change_product_html'), 1, 2 ); 

		/* Change button text from 'Read more' to 'Select options' */
		add_filter( 'woocommerce_product_add_to_cart_text' , array('AwGiftCardCartPage', 'aw_giftcard_change_readmore_button_text') );
		
		add_action( 'woocommerce_single_product_summary', array('AwGiftCardCartPage', 'aw_giftcard_add_custom_fields'), 25 );
 
		add_action( 'woocommerce_add_to_cart', array('AwGiftCardCartPage', 'aw_giftcard_add_to_cart_handler'), 10, 2 );

		add_action( 'woocommerce_after_cart_table', array('AwGiftCardCartPage','aw_giftcard_after_cart_contents'), 10, 0 );  

		add_action( 'woocommerce_after_checkout_validation', array('AwGiftCardCartPage', 'aw_giftcard_validate_gift_information'), 10, 2);

		add_action( 'woocommerce_before_calculate_totals', array('AwGiftCardCartPage', 'aw_gifcard_exclude_tax_shipping_for_giftcard'));

		add_action( 'woocommerce_before_cart_table', array('AwGiftCardCartPage', 'aw_gift_code_display_cart_error_message'), 100 );

		add_filter( 'woocommerce_get_order_item_totals', array( 'AwGiftCardCartPage', 'aw_gift_card_get_order_item_totals' ), 10, 3 );
	}

	public static function aw_giftcard_change_product_html( $price_html, $product) {
		$length = 0;
		$var_price = array();
		if ('gift_card_virtual' == $product->get_type() ) {
			$product_id = $product->get_id();
			$children = aw_get_product_variation_id( $product_id);
			if (!empty($children)) {
				$length = count($children);
				$variation_min = new WC_Product_Variation($children[0]);
				$variation_max = new WC_Product_Variation($children[$length-1]);

				$min_price = wc_get_price_to_display( $variation_min);
				$max_price = wc_get_price_to_display( $variation_max); 
				if ($min_price != $max_price) {
					$price_html = sprintf( '%s &ndash; %s', wc_price($min_price), wc_price($max_price) );
				} else {
					$price_html = wc_price($min_price);
				}
			}
		}
		return  $price_html;
	}
	/* On catalog page change buton text of gift card virtual product from read more to Select options */
	public static function aw_giftcard_change_readmore_button_text( $text ) {
		global $product;
		$product_type = $product->get_type();
		if ('gift_card_virtual' === $product_type) {
			return __( 'Select options', 'woocommerce' );
		}  
		return $text ;
	}

	public static function aw_get_product_price( $product_id) {
		$price = array(0);
		$searilize_price= get_post_meta( $product_id, '_price');
		if (!empty($searilize_price)) {
			$price	= maybe_unserialize($searilize_price);
			sort($price, 1);
		}
		return $price;
	}

	/** Display Giftcard fields on singal Product page */	
	public static function aw_giftcard_add_custom_fields() {
		global $product;
		$price 		= array();
		$default_id = 0; 
		if ('gift_card_virtual' == $product->get_type() ) {
			$current_user = wp_get_current_user();
			$price = aw_get_product_variation_id($product->get_id());
			if (!empty($price)) {
				$default_id	= $price[0]->ID;
			}
			$aw_user_email 	=  $current_user->user_email;
			$firstname 		= $current_user->user_firstname;
			$lastname 		= $current_user->user_lastname;
			$aw_user_name 	= $firstname . ' ' . $lastname;
 
			$Date  = gmdate('Y-m-d');

			if (!empty(self::$aw_giftcard_settings)) {
				$days 		= self::$aw_giftcard_settings['expiration'];
				$expiry_date= gmdate('Y-m-d', strtotime($Date . ' + ' . $days . ' day'));
			}

			$woo_comm_show_out_of_stock_prod = '';
			if ('yes' === get_option( 'woocommerce_hide_out_of_stock_items')) {
				$woo_comm_show_out_of_stock_prod = 'yes';
			}

			$stock_status 	= $product->get_stock_status();
			$post_status	= get_post_status();

			if ('yes' != $woo_comm_show_out_of_stock_prod || 'outofstock' != $stock_status) {
				$display_form = 'Yes';
			}

			if ('onbackorder' == $stock_status) {
				?>
				<p class="stock available-on-backorder">Available on backorder</p>
			<?php
			}

			if ('outofstock' == $stock_status) {
				?>
				
				<p class="stock out-of-stock">Out of stock</p>
			<?php
			}
			if ('Yes' == $display_form) {
				?>
			 
			<form class="gift_card_virtual" method="post" enctype='multipart/form-data'>
		
				<?php wp_nonce_field('aw_gift_card_action', 'aw_gift_card_nonce_fields'); ?>

				<input type="hidden" id="aw_wgc_product_id" name="aw_wgc_product_id" value="<?php echo esc_attr($default_id) ; ?>">
				<input type="hidden" id="aw_wgc_amount" name="aw_wgc_amount" value="">
				<input type="hidden" id="aw_wgc_form" name="aw_wgc_form" value="<?php echo esc_attr($aw_user_name) ; ?>">
				<input type="hidden" id="aw_wgc_email_from" name="aw_wgc_email_from" value="<?php echo esc_attr($aw_user_email) ; ?>">
				<input type="hidden" id="aw_wgc_expiry_date" name="phoen_gift_card_expiry_date" value="<?php echo esc_attr($expiry_date) ; ?>">

				<table class="variations" cellspacing="1" cellspadding="1">
					<tbody>
						<tr>
							<td><label class="aw-steps one">Choose the amount</label></td>
							<td class="value"> 
								<select id="aw_wgc_amount_option" onchange="aw_gift_cart_price(this)">
									<?php 
									if (!empty($price)) {
										foreach ($price as $price) {
											$variation = new WC_Product_Variation($price->ID);
											echo '<option value="' . wp_kses_post(wc_get_price_to_display($variation)) . '" data-value ="' . wp_kses_post($price->ID) . '" >' . wp_kses_post(wc_price(wc_get_price_to_display($variation))) . '</option>';	
										}
									}
									?>
								</select>
							</td>
						</tr>

						<tr>
							<td><label class="aw-steps two">Compose your email</label></td>
							<td>To</td>
							<td class="value"> 
								<input type="text"  id="aw_wgc_to" name="aw_wgc_to" value="" required placeholder="<?php echo 'Recipient Name'; ?>">
							</td>
							<td class="value"> 								 
								<input type="email"  id="aw_wgc_email_to" name="aw_wgc_email_to" value="" required placeholder="<?php echo 'Email'; ?>"> 
							</td>
						</tr>

						<tr>
							<td>From</td>
							<td class="value"> 
								<input type="text"  id="aw_wgc_sender_name" name="aw_wgc_sender_name" value="<?php echo wp_kses_post($aw_user_name); ?>" required placeholder="<?php echo 'Sender Name'; ?>">
							</td>
							<td class="value"> 								 
								<input type="email"  id="aw_wgc_sender_email" name="aw_wgc_sender_email" value="<?php echo wp_kses_post($aw_user_email); ?>" required placeholder="<?php echo 'Sender Email'; ?>"> 
							</td>
						</tr>

						<tr>
							<td>Email Subject</td>
							<td class="value"> 
								<input type="text"  id="aw_wgc_email_heading" name="aw_wgc_email_heading" maxlength="100" value="Congratulations! Here is your gift card" placeholder="Congratulations! Here is your gift card">
								<p id="aw_wgc_text_counter" class="aw_hint"></p>
							</td>
						</tr>

						<tr>
							<td>Additional Text</td>
							<td class="value">
								<textarea cols="15" maxlength="500" rows="2" id="aw_wgc_additional_text" name="aw_wgc_additional_text" placeholder="Dear friend, wishing you all the best!">Dear friend, wishing you all the best!</textarea>
								<span id="aw_wgc_textarea_counter" class="aw_hint"></span>
							</td>
						</tr>

						<tr>
							<td>
								<?php
									$sold_individually = $product->get_sold_individually();
								if ('1' != $sold_individually) {
									?>
										<input type="number" step="1" min="1" name="quantity" value="1" title="Qty" class="input-text qty text"/>
									<?php
								} else { 
									?>
										<input type="hidden" name="quantity" value="1" title="Qty" class="input-text qty text"/>
									<?php
								}
								?>
								<button type="submit" name="add-to-cart" id="add-to-cart"value="<?php echo esc_attr($default_id); ?>" class="cart_validation single_add_to_cart_button1 button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
							</td>
						</tr> 
					</tbody>
				</table>
			</form>
			<?php
			}
		}
	}

	public static function aw_giftcard_add_to_cart_handler ( $cart_item_key, $product_id ) {
		global $wp; 
		$custom_data 	= array();
		$setting 		= self::$aw_giftcard_settings;
		$product_obj 	= new WC_Product_Factory();
		$product 		= $product_obj->get_product($product_id);
		$index 			= 0;
		$existingdata 	= array();

		if ( 'gift_card_virtual' == $product->get_type() ) {
			if ( isset($_REQUEST['aw_gift_card_nonce_fields'])) {

				$aw_gift_card_nonce_fields = sanitize_text_field($_REQUEST['aw_gift_card_nonce_fields']);

				if ( !wp_verify_nonce($aw_gift_card_nonce_fields, 'aw_gift_card_action')) {
					wp_die('Our Site is protected');
				}
			}

			$aw_wgc_product_id		= isset($_POST['aw_wgc_product_id']) ? sanitize_text_field($_POST['aw_wgc_product_id']) : '';
			$aw_wgc_form 			= isset($_POST['aw_wgc_form']) ? strip_tags(sanitize_text_field($_POST['aw_wgc_form'])) : '';
			$aw_wgc_email_from 		= isset($_POST['aw_wgc_email_from']) ? strip_tags(sanitize_text_field($_POST['aw_wgc_email_from'])) : '';
			$aw_wgc_expiry_date 	= isset($_POST['aw_wgc_expiry_date']) ? sanitize_text_field($_POST['aw_wgc_expiry_date']) : '';
			$aw_wgc_to 				= isset($_POST['aw_wgc_to']) ? strip_tags(sanitize_text_field($_POST['aw_wgc_to'])) : '';
			$aw_wgc_email_to 		= isset($_POST['aw_wgc_email_to']) ? strip_tags(sanitize_text_field($_POST['aw_wgc_email_to'])) : '';
			$aw_wgc_sender_name 	= isset($_POST['aw_wgc_sender_name']) ? strip_tags(sanitize_text_field($_POST['aw_wgc_sender_name'])) : '';
			$aw_wgc_sender_email	= isset($_POST['aw_wgc_sender_email']) ? strip_tags(sanitize_text_field($_POST['aw_wgc_sender_email'])) : '';
			$aw_wgc_email_heading 	= isset($_POST['aw_wgc_email_heading']) ? strip_tags(sanitize_text_field($_POST['aw_wgc_email_heading'])) : '';	
			$quantity 				= isset($_POST['quantity']) ? sanitize_text_field($_POST['quantity']) : '';
			$aw_wgc_additional_text = isset($_POST['aw_wgc_additional_text']) ? strip_tags(sanitize_text_field($_POST['aw_wgc_additional_text'])) : ''; 
			$aw_wgc_amount 			= isset($_POST['aw_wgc_amount']) ? sanitize_text_field($_POST['aw_wgc_amount']) : '';
			if (isset($_POST['aw_wgc_product_id'])) {
				$existingdata  = WC()->session->get( 'cart_items' );
				$existingdata[$aw_wgc_product_id] = array(
									'product_id'		=> $aw_wgc_product_id,
									'giftcard_amount'	=> $aw_wgc_amount,
									'recipient_name'	=> $aw_wgc_to,
									'recipient_email'  	=> $aw_wgc_email_to,
									'sender_name'		=> $aw_wgc_sender_name,
									'sender_email'		=> $aw_wgc_sender_email,
									'email_heading' 	=> $aw_wgc_email_heading,
									'gift_description' 	=> $aw_wgc_additional_text
								);
				WC()->session->set( 'cart_items', $existingdata );
			}
		}
	}

	public static function aw_giftcard_after_cart_contents() {
		global $woocommerce, $wpdb;
		$virtual_gift 		= false;
		$setting 			= self::$aw_giftcard_settings;
		$remainingbalance 	= $wpdb->prefix . 'woocommerce_REMAING1iaW';
		$myremainingbalance = $wpdb->prefix . 'woocommerce_M1YiR5E7MBA';
		$gift_code 			= 0;
		$giftcard_balance 	= 0;
		$gift_codes 		= array();
		$code 				= array();
		$check_enabled 		= 'disabled';
		$user 				= wp_get_current_user();
		$cart_total 		= $woocommerce->cart->cart_contents_total;

		if ($user->ID) {
			$giftcard_balance 	= aw_gc_get_user_total_balance();
			if ($giftcard_balance) {
				if (isset($_COOKIE[$remainingbalance]) && $_COOKIE[$remainingbalance] > 0) { 
					$giftcard_balance = sanitize_text_field($_COOKIE[$remainingbalance]);
				}
			}
			if ($giftcard_balance) {
				$check_enabled 	= 'enabled';
				$gift_codes 	= aw_gc_get_recipient_giftcode();
				if (!empty($gift_codes)) {
					foreach ($gift_codes as $key => $value) {
						$code[] = $value->giftcard_code;
					}	
					$gift_code 	= implode(',', $code);
				}
			}
		}

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = $cart_item['data'];
			$product_id = $cart_item['product_id'];
			$_product 	= wc_get_product( $product_id );
			if ( $_product->is_type( 'gift_card_virtual' ) ) {
				$virtual_gift = true;
			}
		}

		if (( !array_key_exists('giftcard_restriction', $setting) && 1 == $virtual_gift ) || 0 == $virtual_gift) { 
			?>

			<table class="aw-tbl-coupon shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
				<tr>
					<td class="actions">
						<div class="coupon">
							
							<div class="coupon-col">
								<input type="text" name="aw_gift_code" class="input-text" id="aw_gift_code" value="" placeholder="Enter gift card code">
							</div>
							
							<div class="coupon-col">
								<div class="inp-checkbox">
									<?php  
									if ( $gift_code ) {
										?>
										<input type="hidden" name="" id="aw_balance_gift_code" value="<?php echo wp_kses_post($gift_code); ?>">
									<?php 
									} 
									if ( $giftcard_balance > 0) {
										$checked = '';
										if (isset($_COOKIE['aw_gc_checked_balance']) && 1 == $_COOKIE['aw_gc_checked_balance']) {
											$checked = 'checked = checked';
										}
										?>
										<input id="aw_use_balance_chk" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" type="checkbox" name="aw_use_balance_check" <?php echo wp_kses_post($check_enabled); ?>  value="" <?php echo wp_kses_post($checked); ?>>                             
									<?php 
									}
									?>
								</div>
								<div class="inp-label">
									<?php  
									if ( $giftcard_balance > 0) {
										?>
									<label> Use gift card balance </label>
									<?php 
										if (isset($_COOKIE[$myremainingbalance])) {
											$giftcard_balance = sanitize_text_field($_COOKIE[$myremainingbalance]);	
										}
										?>
									<label id="label_aw_gc_cardbalance">(Available Balance: <span id="aw_gc_cardbalance"><?php echo wp_kses_post(aw_gc_get_amount($giftcard_balance)); ?></span>)</label>
									<?php 
									}
									?>
								</div>
								
							</div>
						
							<div class="coupon-col action-btn">						 
								<button type="button" class="button" name="aw_apply_gift"  value="Apply gift card" onclick="return apply_aw_gift(this,'cart',<?php echo wp_kses_post($cart_total); ?>,'<?php echo wp_kses_post($gift_code); ?>');">Apply</button>
							</div>
						</div>
					</td>
				</tr>
			</table>
		<?php 
		}
	}

	public static function aw_giftcard_validate_gift_information( $fields, $errors ) {
		$virtual_gift = false;

		if (!isset($_POST['aw_gc_checkout_nonce_name']) || !wp_verify_nonce(sanitize_key($_POST['aw_gc_checkout_nonce_name']), 'aw_gc_checkout_nonce_action')) {
			WC()->session->set( 'refresh_totals', true );
			throw new Exception( __( 'We were unable to process your order, please try again.', 'woocommerce' ) );
		}

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

			$product 	= $cart_item['data'];
			$product_id = $cart_item['product_id'];
			$_product 	= wc_get_product( $product_id );

			if ( $_product->is_type( 'gift_card_virtual' ) ) {
				$virtual_gift = true;
			}
		}
		if (false == $virtual_gift) {
			return ;
		}
		if (isset($_POST['recipient_name']) && empty(trim(sanitize_text_field($_POST['recipient_name']))) ) {
			 $errors->add( 'validation', ' <strong>Recipient Name </strong>  is a required field. ' );
		}
		if (isset($_POST['recipient_email']) && empty(trim(sanitize_text_field($_POST['recipient_email'])))) {
			 $errors->add( 'validation', ' <strong>Recipient Email </strong> is a required field. ' );
		} elseif (isset($_POST['recipient_email']) && !filter_var(trim(sanitize_text_field($_POST['recipient_email'])), FILTER_VALIDATE_EMAIL)) {
			 $errors->add( 'validation', 'Invalid Gift to email address. ' );
		}
		if (isset($_POST['sender_name']) && empty(trim(sanitize_text_field($_POST['sender_name'])))) {
			 $errors->add( 'validation', ' <strong>Sender Name </strong> is a required field. ' );
		}
		if (isset($_POST['sender_email']) && empty(trim(sanitize_text_field($_POST['sender_email'])))) {
			 $errors->add( 'validation', ' <strong>Sender Email</strong>  is a required field. ' );
		} elseif (isset($_POST['sender_email']) && !filter_var(trim(sanitize_text_field($_POST['sender_email'])), FILTER_VALIDATE_EMAIL)) {
			 $errors->add( 'validation', 'Invalid sender email address. ' );
		}
	}

	
	public static function aw_gifcard_exclude_tax_shipping_for_giftcard( $cart ) {
		if ( is_cart() || is_checkout()) {
			$gift 			= false;
			$nongift 		= false;
			$variation_id 	= array();

			foreach ( $cart->cart_contents as $cart_item_id=>$cart_item ) {
				$product_id 	= $cart_item['product_id'];
				$_product 		= wc_get_product( $product_id );
				if ( $_product->is_type( 'gift_card_virtual' ) ) {
					if (empty($cart_item['variation_id']) || 0 == $cart_item['variation_id']) {
						$product = new WC_Product_Variable($product_id);
						$variations = $product->get_children();
						$variation_id[] = $variations[0];				
					} else {
						$variation_id[] = $cart_item['variation_id'];
					}
					$gift 	= true;
				} else {
					$nongift= true;
				} 
			}

			if (true == $gift && false == $nongift) {
				add_filter( 'wc_tax_enabled', '__return_false' );
				add_filter( 'wc_shipping_enabled', '__return_false' );				
							
			} else {
				add_filter( 'wc_shipping_enabled', '__return_true' );
				add_filter( 'wc_tax_enabled', '__return_true' );
			}

			$gift_info = WC()->session->get('cart_items');
			if (!empty($gift_info)) {
				foreach ($gift_info as $key => $info) {
					if (!in_array($key, $variation_id)) {
						unset($gift_info[$key]);
						WC()->session->set('cart_items', $gift_info);
					}
				}
			}
		}
	}
	public static function aw_gift_code_display_cart_error_message() {
		global $wpdb;
		$setting 			= self::$aw_giftcard_settings;
		$apply_point_cookie = $wpdb->prefix . 'woocommerce_InD9QULI3ct';
		$giftcoupon_cookie 	= $wpdb->prefix . 'woocommerce_RD14QULDiAW';
		$remainingbalance 	= $wpdb->prefix . 'woocommerce_REMAING1iaW';
		$myremainingbalance = $wpdb->prefix . 'woocommerce_M1YiR5E7MBA';
		$carttotal 			= (float) WC()->cart->total;
		$cartsubtotal		= (float) WC()->cart->get_subtotal();
		$totaldiscount 		= WC()->cart->get_cart_discount_total();
		$path = '/';
		$host = parse_url(get_option('siteurl'), PHP_URL_HOST);
		$gift = false;
		
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = $cart_item['data'];
			$product_id = $cart_item['product_id'];
			$_product 	= wc_get_product( $product_id );

			if (array_key_exists('giftcard_restriction', $setting) && 'gift_card_virtual' == $_product->get_type() && isset($_COOKIE[$apply_point_cookie]) ) {
				 $gift = true;
				?>
				 <script>
					 remove_aw_gift('cart');
				 </script>
				<?php 
				WC()->cart->calculate_totals();
			}
		}

		if ($gift && isset($_COOKIE[$apply_point_cookie]) && 0 != $_COOKIE[$apply_point_cookie]) {
			$info_message = apply_filters( 'cart', __( 'Sorry, it seems the gift card code is invalid - it has now been removed from your order.', 'woocommerce' ) );
			wc_print_notice( $info_message, 'error' );
		}
	}

	public static function aw_gift_card_get_order_item_totals( $total_rows, $order, $tax_display) {
		$order_id = $order->get_id();
		$gift_cards = get_post_meta($order_id, '_awgc_redeemed_cards', true);
		$gift_amount= get_post_meta($order_id, '_awgc_redeemed_amt', true);
		$gift_bal 	= get_post_meta($order_id, '_awgc_cards_balance', true);
		
		if (empty($gift_bal)) {
			$gift_bal = 0 ;
		}
		if ($gift_amount) {
			$validate_wc_gc = check_gift_code_details($gift_cards);
			$available_bal = aw_gc_get_user_total_balance();
			if (!empty($validate_wc_gc)) {

				$is_user_gc = aw_gift_code_check_giftcard_touser( $validate_wc_gc->id );
				if (empty($is_user_gc) || 0 == $available_bal) {
					$balancemsg = '';
				} else {
					$balancemsg = ' (Available Balance: ' . $gift_bal . ' )';
				}		
			} else {
				$balancemsg = ' (Available Balance: ' . $gift_bal . ' )';
			} 

			$order_total = $total_rows['order_total'];
			unset( $total_rows['order_total']);
			$total_rows['gift']['label']= 'Gift card: ' . $gift_cards . $balancemsg;
			$total_rows['gift']['value']= '-' . aw_gc_get_amount($gift_amount);
			$total_rows['order_total'] 	= $order_total;	
		}
		return $total_rows;
	}	
} //class end 
?>
