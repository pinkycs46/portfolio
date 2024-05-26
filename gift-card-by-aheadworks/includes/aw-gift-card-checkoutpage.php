<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

$awgiftchecout 	= new AwGiftCardCheckoutPage();

class AwGiftCardCheckoutPage {

	public static $aw_giftcard_settings;
	public function __construct() {

		global $wp_session;
		self::$aw_giftcard_settings = maybe_unserialize(get_option('aw_wgc_configuration'));
		/* Display Gift car*/
		add_action('woocommerce_before_checkout_form', array('AwGiftCardCheckoutPage', 'aw_gift_card_display_giftapply_option'), 20);

		add_action('woocommerce_after_order_notes', array('AwGiftCardCheckoutPage','aw_after_checkout_shipping_form'));

		add_action('woocommerce_checkout_update_order_meta', array('AwGiftCardCheckoutPage','aw_gc_update_meta_after_order'), 10, 2);

		add_action( 'woocommerce_review_order_before_order_total', array('AwGiftCardCheckoutPage','aw_giftcard_subtract_giftamount_in_revieworder') );
	}

	public static function aw_gift_card_display_giftapply_option() {
		global $wpdb;
		$virtual_gift 	= false;
		$setting 		= self::$aw_giftcard_settings;
		$apply_point_cookie = $wpdb->prefix . 'woocommerce_InD9QULI3ct';
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product 	= $cart_item['data'];
			$product_id = $cart_item['product_id'];
			$_product 	= wc_get_product( $product_id );
			if ( $_product->is_type( 'gift_card_virtual' ) ) {
				$virtual_gift = true;
			}
		}

		if (( !array_key_exists('giftcard_restriction', $setting) && 1 == $virtual_gift ) || 0 == $virtual_gift) { ?>
			<div class="woocommerce-message appendeddiv"  role="alert">Have a gift card? <a href="#" class="showgift">Click here to enter your code</a>
			</div>
			<form class="checkout_gift" method="post" style="display:none">

				<p class="form-row form-row-first">
					<input type="text" name="gift_code" class="input-text" placeholder="<?php esc_attr_e( 'Gift code', 'woocommerce' ); ?>" id="gift_code" value="" />
				</p>

				<p class="form-row form-row-last">
					<button type="submit" class="button" name="apply_gift"  id="awgift_apply_btn" data-value="checkout" value="<?php esc_attr_e( 'Apply Gift', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply gift card', 'woocommerce' ); ?></button>
				</p>

				<div class="clear"></div>
			</form>	
			<?php 
		}
		
		if (array_key_exists('giftcard_restriction', $setting) && 'gift_card_virtual' == $_product->get_type() && $virtual_gift && isset($_COOKIE[$apply_point_cookie]) && 0 != $_COOKIE[$apply_point_cookie] ) {

			?>
				<script>
					remove_aw_gift('cart');
				</script>
			<?php

			$info_message = apply_filters( 'checkout', __( 'Sorry, it seems the gift card code is invalid - it has now been removed from your order.', 'woocommerce' ) );
			wc_print_notice( $info_message, 'error' );
		}
	}
	 
	public static function aw_after_checkout_shipping_form() {
		wp_nonce_field('aw_gc_checkout_nonce_action', 'aw_gc_checkout_nonce_name');
		$giftcard_in_cart 	= false;
		$recipient_name		= '';
		$recipient_email	= '';
		$sender_name 		= '';
		$sender_email 		= '';	
		$email_heading 		= '';
		$gift_description 	= '';	
		$count 				= 0;
		foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product_id 	= $cart_item['product_id'];
			$_product 		= wc_get_product( $product_id );

			if (( empty($cart_item['variation_id']) || 0 == $cart_item['variation_id'] ) && $_product->is_type( 'gift_card_virtual' )) {
				$product = new WC_Product_Variable($cart_item['product_id']);
				$variations = $product->get_children();
				$variation_id = $variations[0];

			} else {
				if ($_product->is_type( 'gift_card_virtual' )) {
					$variation_id 	= $cart_item['variation_id'];	
				}
			}
			
			if ( $_product->is_type( 'gift_card_virtual' ) ) {
				$giftcard_in_cart = true;
				$count++;
			}
		}
		if (false == $giftcard_in_cart || $count >=2) {
			$cartitem = WC()->session->get( 'cart_items');
			return ;
		}
		$cartitems = WC()->session->get( 'cart_items');
		if (!empty($cartitems)) {
			foreach ($cartitems as $cartitem) {

				if (isset($cartitem['recipient_name'])) {
					$recipient_name 	= $cartitem['recipient_name'];
				}
				if (isset($cartitem['recipient_email'])) {
					$recipient_email 	= $cartitem['recipient_email'];
				}
				if (isset($cartitem['sender_name'])) {
					$sender_name 		= $cartitem['sender_name'];
				}
				if (isset($cartitem['sender_email'])) {
					$sender_email 		= $cartitem['sender_email'];
				}
				if (isset($cartitem['email_heading'])) {
					$email_heading 		= $cartitem['email_heading'];
				}
				if (isset($cartitem['gift_description'])) {
					$gift_description 	= $cartitem['gift_description'];
				}
			}
		}
		?>

		<div id="customise_checkout_field">
			<h3> Gift card information</h3>
			<table class="variations" cellspacing="0" cellspadding="0">
				<tbody>
					 
					<tr>
						<td>To</td>
						<td class="value"> 
							<input type="hidden" name="product_id" value="<?php echo wp_kses_post($variation_id); ?>">
							<input type="text"  id="aw_wgc_to" name="recipient_name"  required value="<?php echo wp_kses_post($recipient_name); ?>">
						</td>
					</tr>
					<tr>
						<td>To Email</td>
						<td class="value"> 								 
							<input type="email"  id="aw_wgc_email_to" name="recipient_email"  value="<?php echo wp_kses_post($recipient_email); ?>"> 
						</td>
					</tr>
					<tr>
						<td>From</td>
						<td class="value"> 
							<input type="text"  id="aw_wgc_sender_name" name="sender_name" value="<?php echo wp_kses_post($sender_name); ?>" required placeholder="<?php echo 'Sender Name'; ?>">
						</td>
					</tr>
					<tr>
						<td>From Email</td>
						<td class="value"> 								 
							<input type="email"  id="aw_wgc_sender_email" name="sender_email" value="<?php echo wp_kses_post($sender_email); ?>" required placeholder="<?php echo 'Sender Email'; ?>"> 
						</td>
					</tr>
					<tr>
						<td>Email Subject</td>
						<td class="value">
							<?php
							if ( '' != $email_heading) {
								$email_heading = $email_heading;
							} else {
								$email_heading = 'Congratulations! Here is your gift card';
							}
							?>
							<input type="text"  id="aw_wgc_email_heading" name="email_heading" maxlength="100" value="<?php echo wp_kses_post($email_heading); ?>" placeholder="Congratulations! Here is your gift card">
						</td>
					</tr>
					<tr>
						<td>Additional Text</td>
						<td class="value">
							<textarea cols="15" maxlength="500" rows="2" id="aw_wgc_additional_text" name="gift_description" placeholder="Dear friend, wishing you all the best!"><?php echo '' != $gift_description ? wp_kses_post($gift_description) : 'Dear friend, wishing you all the best!'; ?></textarea>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	public static function aw_gc_update_meta_after_order( $order_id, $posted ) {
		if (!isset($_POST['aw_gc_checkout_nonce_name']) || !wp_verify_nonce(sanitize_key($_POST['aw_gc_checkout_nonce_name']), 'aw_gc_checkout_nonce_action')) {
			WC()->session->set( 'refresh_totals', true );
			throw new Exception( __( 'We were unable to process your order, please try again.', 'woocommerce' ) );
		}
		if (isset($_POST['recipient_name']) && isset($_POST['recipient_email']) && isset($_POST['sender_name']) && isset($_POST['sender_email']) && isset($_POST['email_heading']) && isset($_POST['gift_description']) && isset($_POST['product_id']) && 0 != $_POST['product_id']) {
			$gift_info[0] = $_POST;
		} else {
			$gift_info = WC()->session->get( 'cart_items' );
		}
		WC()->session->set( 'cart_items', null );
		if (!empty($gift_info)) {
			update_post_meta($order_id, 'gift_info', $gift_info);
		}
		if (isset($_POST['aw_gc_redeemed_amt']) && !empty($_POST['aw_gc_redeemed_amt']) && isset($_POST['aw_gc_redeemed_cards']) && !empty($_POST['aw_gc_redeemed_cards'])) {
			$amount = sanitize_text_field($_POST['aw_gc_redeemed_amt']);
			$cards 	= sanitize_text_field($_POST['aw_gc_redeemed_cards']);
			if (isset($_POST['aw_gc_cards_balance'])) {
				$balance= (float) $_POST['aw_gc_cards_balance'];
			}	
			update_post_meta($order_id, '_awgc_redeemed_cards', $cards);
			update_post_meta($order_id, '_awgc_redeemed_amt', $amount);
			update_post_meta($order_id, '_awgc_cards_balance', aw_gc_get_amount($balance));
		}
	}

	public static function aw_giftcard_subtract_giftamount_in_revieworder() {
		global $wpdb;
		$available_balance 	= 0;	
		$avail_bal_string 	= ''; 
		$apply_point_cookie = $wpdb->prefix . 'woocommerce_InD9QULI3ct';
		$giftcoupon_cookie 	= $wpdb->prefix . 'woocommerce_RD14QULDiAW';
		$remainingbalance 	= $wpdb->prefix . 'woocommerce_REMAING1iaW';	
		$myremainingbalance = $wpdb->prefix . 'woocommerce_M1YiR5E7MBA';

		if (isset($_COOKIE[$giftcoupon_cookie]) && !empty($_COOKIE[$giftcoupon_cookie])) {
			$shiping_total 	= (float) WC()->cart->get_shipping_total();
			$cartsubtotal	= (float) WC()->cart->get_subtotal();
			$totaldiscount 	= (float) WC()->cart->get_cart_discount_total();
			$total = $cartsubtotal - $totaldiscount + WC()->cart->get_shipping_total()+ WC()->cart->get_total_tax();
			if (0 == $total) {
				?>
				<script type="text/javascript">
					remove_aw_gift('cart','Gift Card can not be applied on 0 total');
				</script>
				<?php 
			}
			$code_data = get_balance_and_applied_code();
			if (!empty($code_data) && isset($code_data['remaining_msg']) && isset($code_data['giftcode']) && isset($code_data['giftamount'])) {
				$bal = 0;
				if (isset($_COOKIE['aw_gc_checked_balance'])) {
					if (!empty($_COOKIE[$myremainingbalance])) {
						$bal = sanitize_text_field($_COOKIE[$myremainingbalance]);
					}
				} elseif (isset($_COOKIE[$myremainingbalance])) {
					if (!empty($_COOKIE[$remainingbalance])) {
						$bal = sanitize_text_field($_COOKIE[$remainingbalance]);
					}
				}
				?>
				<tr class="fee">
					<th>
					<?php 
					esc_attr_e ( 'Gift Card: ' . $code_data['giftcode'] . $code_data['remaining_msg']);
					?>
					</th>
					<td>
						<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol"><?php echo wp_kses_post(aw_gc_get_amount($code_data['giftamount'])); ?>
						<input type="hidden" name="aw_gc_redeemed_amt" value="<?php echo wp_kses_post($code_data['giftamount']); ?>">
						<input type="hidden" name="aw_gc_redeemed_cards" value="<?php echo wp_kses_post($code_data['giftcode']); ?>">
						<input type="hidden" name="aw_gc_cards_balance" value="<?php echo wp_kses_post($bal); ?>">
					</td>
				</tr>
				<?php
			}
		}
	}
} //class end
?>
