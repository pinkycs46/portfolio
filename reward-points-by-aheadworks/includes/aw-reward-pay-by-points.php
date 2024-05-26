<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class AwRewardPayByPoints {

	public static function aw_checkout_points() {
		$user = aw_reward_points_get_user();
		if ($user) {
			global $wpdb;
			$apply_point_cookie = $wpdb->prefix . 'woocommerce_I3QIn9ctULD';
			$btn_type 			= 'Apply Points';
			$display_points 	= 0;

			if (isset($_COOKIE[$apply_point_cookie]) && '0' == $_COOKIE[$apply_point_cookie]) {
				$btn_type = 'Apply Points';
			}
			if (isset($_COOKIE[$apply_point_cookie]) && '1' == $_COOKIE[$apply_point_cookie]) {
				$btn_type = 'Remove Points';
			}

			$get_balance = aw_reward_points_get_customer_balance($user);
			$get_config = aw_reward_points_get_config();

			if (!empty($get_balance)) {
				$display_points = $get_balance->balance;
				global $woocommerce;
				$used_points = array();

				foreach ($woocommerce->cart->get_fees() as $cart_fee) {
					$fee_name = $cart_fee->name;
					if ('' != $fee_name && ( 'Points' === substr($fee_name, 0, 6) )) {
						$temp = explode(':', $fee_name);
						$used_points = explode(' ', $temp[1]);
					}
				}

				if (!empty($used_points)) {
					$display_points = $get_balance->balance - $used_points[1];
				}
				if (isset($get_config['spendrates'])) {
					$rates = unserialize($get_config['spendrates']);	
				}

				if (!empty($rates)) {
					$disp_btn = 0;
					if (null == $get_balance->expiration_date || gmdate('Y-m-d') <= $get_balance->expiration_date) {
						$disp_btn = 1;
					}
					//wp_kses(, wp_kses_allowed_html('post')

					$earn_rates = unserialize($get_config['earnrates']);

					$config_lifetime_sale = array();
					$customer_lifetime_sale = $get_balance->lifetime_sale;

					foreach ($rates as $key => $rate) {
						if ($customer_lifetime_sale >= $rate['lifetime_sale']) {
							$config_lifetime_sale[] = $rate['lifetime_sale'];
						}
					}

					$customer_rates = aw_reward_points_getClosest($customer_lifetime_sale, $config_lifetime_sale);

					if (1 == $disp_btn && 0 < $get_balance->balance && null != $customer_rates) {
						echo wp_kses('<span style="float:left;" class="rd_customer_points">Points: ' . $display_points . '</span>', wp_kses_allowed_html('post'));
						echo wp_kses('<button style="float:right;" class="button" type="button" id="rd_reward_points" name="rd_reward_points" value="' . $btn_type . '" onclick="return apply_rd_points(this);">' . $btn_type . '</button>', wp_kses_allowed_html('post'));
					}
				}
			}
		} else {
			return;
		}
	}

	public static function aw_apply_points() {
		global $wpdb;
		$apply_point_cookie = $wpdb->prefix . 'woocommerce_I3QIn9ctULD';
		$type = '';
		
		check_ajax_referer( 'rdrewardpoints_nonce', 'nonce_ajax' );
		
		if (!empty($_POST['opt_type'])) {
			$type = sanitize_text_field($_POST['opt_type']);
		}

		$path = '/';
		$host = parse_url(get_option('siteurl'), PHP_URL_HOST);
		$user = aw_reward_points_get_user();
		if ($user) {
			global $woocommerce;
			$get_balance 		= aw_reward_points_get_customer_balance($user);
			$get_config 		= aw_reward_points_get_config();
			$customer_balance 	= $get_balance->balance;

			if (isset($_COOKIE[$apply_point_cookie]) && 'apply' == $type) {
				$taxes = $woocommerce->cart->get_taxes();
				$total_tax = 0;
				$total_amount = 0;

				foreach ($taxes as $tax) {
					$total_tax += $tax;
				}
				$total_amount = $woocommerce->cart->cart_contents_total + $woocommerce->cart->shipping_total + $total_tax;

				$cover_percentage = $get_config['cover_percentage'];
				if (null === $cover_percentage) {
					$cover_percentage = 100;
				}

				/*Maximum amount can pe paid by customer based on % setting from Spend Points Config*/
				$customer_can_pay_max = ( $total_amount/100 ) * $cover_percentage;
				$customer_can_pay_max = number_format($customer_can_pay_max, 2, '.', '');
				/*Maximum amount can pe paid by customer based on % setting from Spend Points Config*/

				$rates = unserialize($get_config['spendrates']);

				if (!empty($rates)) {
					$config_lifetime_sale = array();
					$customer_lifetime_sale = $get_balance->lifetime_sale;

					foreach ($rates as $key => $rate) {
						if ($customer_lifetime_sale >= $rate['lifetime_sale']) {
							$config_lifetime_sale[] = $rate['lifetime_sale'];
						}
					}

					$spent_rates = aw_reward_points_getClosest($customer_lifetime_sale, $config_lifetime_sale);
					$key = array_search($spent_rates, array_column($rates, 'lifetime_sale'));

					$base_currency 			= $rates[$key]['base_currency'];
					$points 				= $rates[$key]['points'];

					/*Based on Lifetime Sale and Point Balance of Customer, Amount customer has authority to pay*/
					$customer_can_pay_min = ( $base_currency/$points )*$customer_balance;
					/*Based on Lifetime Sale and Point Balance of Customer, Amount customer has authority to pay*/

					/*Check based on configuration, what is maximum amount customer can pay*/
					if ($customer_can_pay_min > $customer_can_pay_max) {
						$check_which_min_amt = $customer_can_pay_max;
					} else {
						$check_which_min_amt = $customer_can_pay_min;
					}
					/*Check based on configuration, what is maximum amount customer can pay*/

					/*Points to be reduced from customer points balance*/
					$one_point = ( $base_currency/$points );
					$points_reduced = ( $check_which_min_amt/$one_point );

					if ($points_reduced < 1) {
						$check_which_min_amt = 0;
						//$notices = WC()->session->get('wc_notices');
						wc_add_notice('No possibility to reward points discounts in the cart', 'error');
						setcookie($apply_point_cookie, '0', 0, $path, $host);
						$ret_type['type'] 				= 'Apply Points';
						$ret_type['customer_points'] 	= $customer_balance;
					} else {
						setcookie($apply_point_cookie, '1', 0, $path, $host);

						$points_reduced 				= ceil($points_reduced);
						$ret_type['type'] 				= 'Remove Points';
						$ret_type['customer_points'] 	= $customer_balance - $points_reduced;
					}
				}
			}

			if (isset($_COOKIE[$apply_point_cookie]) && 'remove' == $type) {
				setcookie($apply_point_cookie, '2', 0, $path, $host);
				$ret_type['type']				= 'Apply Points';
				$ret_type['customer_points'] 	= $customer_balance;
			}

			$ret_type['earnpoint']				= aw_reward_points_display_earn_notice();
			echo json_encode($ret_type);
			wp_die();
		}
	}

	public static function aw_apply_points_cart_total( $cart) {
		global $wpdb;
		$apply_point_cookie = $wpdb->prefix . 'woocommerce_I3QIn9ctULD';

		$path = '/';
		$host = parse_url(get_option('siteurl'), PHP_URL_HOST);

		if (isset($_COOKIE[$apply_point_cookie])) {
			$user = aw_reward_points_get_user();
			if ($user) {
				global $woocommerce;
				$get_balance = aw_reward_points_get_customer_balance($user);
				$get_config = aw_reward_points_get_config();

				$taxes = $cart->get_taxes();
				$total_tax = 0;
				$total_amount = 0;

				foreach ($taxes as $tax) {
					$total_tax += $tax;
				}	
				$total_amount = $cart->cart_contents_total + $cart->shipping_total + $total_tax;
				$cover_percentage = null;
				if (isset($get_config['cover_percentage'])) {
					$cover_percentage = $get_config['cover_percentage'];	
				}
				
				if (null === $cover_percentage) {
					$cover_percentage = 100;
				}

				/*Maximum amount can pe paid by customer based on % setting from Spend Points Config*/
				$customer_can_pay_max = ( $total_amount/100 ) * $cover_percentage;
				$customer_can_pay_max = number_format($customer_can_pay_max, 2, '.', '');
				/*Maximum amount can pe paid by customer based on % setting from Spend Points Config*/
				if (isset($get_config['spendrates'])) {
					$rates = unserialize($get_config['spendrates']);	
				}

				if (!empty($rates) && !empty($get_balance)) {
					$config_lifetime_sale = array();
					$customer_lifetime_sale = $get_balance->lifetime_sale;
					$customer_balance = $get_balance->balance;

					foreach ($rates as $key => $rate) {
						if ($customer_lifetime_sale >= $rate['lifetime_sale']) {
							$config_lifetime_sale[] = $rate['lifetime_sale'];
						}
					}

					$spent_rates = aw_reward_points_getClosest($customer_lifetime_sale, $config_lifetime_sale);
					$key = array_search($spent_rates, array_column($rates, 'lifetime_sale'));

					$base_currency 			= $rates[$key]['base_currency'];
					$points 				= $rates[$key]['points'];

					/*Based on Lifetime Sale and Point Balance of Customer, Amount customer has authority to pay*/

					$customer_can_pay_min = ( $base_currency/$points )*$customer_balance;
					/*Based on Lifetime Sale and Point Balance of Customer, Amount customer has authority to pay*/

					/*Check based on configuration, what is maximum amount customer can pay*/
					if ($customer_can_pay_min > $customer_can_pay_max) {
						$check_which_min_amt = $customer_can_pay_max;
					} else {
						$check_which_min_amt = $customer_can_pay_min;
					}

					/*Check based on configuration, what is maximum amount customer can pay*/

					/*Points to be reduced from customer points balance*/
					$one_point = ( $base_currency/$points );
					$points_reduced = ( $check_which_min_amt/$one_point );

					/*Points to be reduced from customer points balance*/

					if ('1' == $_COOKIE[$apply_point_cookie] && 1 <= $points_reduced) {
						$points_reduced = ceil($points_reduced);
						$points_reduced = $points_reduced . ' Points';
						$cart->add_fee( __( 'Points: ' . $points_reduced, 'rd-reward-points' ), -$check_which_min_amt, false);
					}
					if ('2' == $_COOKIE[$apply_point_cookie]) {
						wc_clear_notices();
					}
				} else {
					return;
				}
			}

		}
	}

	public static function aw_action_new_order_recevied( $order_id) {
		global $wpdb;

		$apply_point_cookie = $wpdb->prefix . 'woocommerce_I3QIn9ctULD';
		$path 				= '/';
		$host 				= parse_url(get_option('siteurl'), PHP_URL_HOST);

		$fee_name 			= '';
		$fee_total 			= 0;
		$points 			= 0;
		$db_balance_table 		= $wpdb->prefix . 'reward_points_balances';
		$db_transcation_table 	= $wpdb->prefix . 'reward_points_transaction_history';

		$order = new WC_Order($order_id);
		$order_data = $order->get_data();
		$order_status = $order->get_status();
		$user_id = $order->get_user_id();
		$order_grand_total = $order_data['total'];

		foreach ( $order->get_items('fee') as $item_id => $item_fee ) {
			$fee_name = $item_fee->get_name();
		}

		$get_config 			= aw_reward_points_get_config();
		$customer_lifetime_sale = self::aw_reward_points_total_order($user_id, $order_grand_total);

		if (isset($get_config['spendrates'])) {
			$rates 	= unserialize($get_config['spendrates']);	
		}
		//if ('' != $fee_name && ( 'Points' === substr($fee_name, 0, 6) ) && !empty($rates) && 'on-hold' !== $order_status) {
		//if ('' != $fee_name && ( 'Points' === substr($fee_name, 0, 6) ) && !empty($rates) && ( 'processing' == $order_status || 'completed' == $order_status )) {
		if ('' != $fee_name && ( 'Points' === substr($fee_name, 0, 6) ) && !empty($rates)) {
			$config_lifetime_sale = array();
			foreach ($rates as $key => $rate) {
				if ($customer_lifetime_sale['calculation'] >= $rate['lifetime_sale']) {
					$config_lifetime_sale[] = $rate['lifetime_sale'];
				}
			}
			$spend_rates	= aw_reward_points_getClosest($customer_lifetime_sale['calculation'], $config_lifetime_sale);
			$key 			= array_search($spend_rates, array_column($rates, 'lifetime_sale'));

			$balance_log	= 'Customer Lifetime Sales >= ' . $rates[$key]['lifetime_sale'] . '::';
			$balance_log	.= 'Base Currency = ' . $rates[$key]['base_currency'] . '::';
			$balance_log	.= 'Points = ' . $rates[$key]['points'];
			$points 		= intval(preg_replace('/[^0-9]+/', '', $fee_name), 10);
			$get_balance 	= aw_reward_points_get_customer_balance($user_id);
			$currentbalance = $get_balance->balance;
			$balance 		= $currentbalance - $points;

			$expiration_day = (int) $get_config['expiration_day'];

			if (0 != $expiration_day) {
				$expiration_date = gmdate('Y-m-d', strtotime('+ ' . $get_config['expiration_day'] . ' day'));
			} else {
				$expiration_date = null;
			}

			$array['last_updated'] 		= gmdate('Y-m-d H:i:s');
			$array['balance'] 			= abs($balance);
			$array['spendpoints'] 		= $get_balance->spendpoints + abs($points);
			$array['expiration_date'] 	= $expiration_date;
			$array['reset'] 			= 0;

			$wpdb->update($db_balance_table, $array, array('user_id'=>$user_id));
			$order_status 			= $order_data['status'];
			$transaction_date 		= gmdate('Y-m-d H:i:s');
			$last_updated 			= gmdate('Y-m-d H:i:s');
			$points_type			= 'Spent';
			$comments 				= '';
			$wpdb->insert($db_transcation_table, array(
				'user_id'          		=> $user_id,
				'order_id'       		=> $order_id,
				'points_type'			=> $points_type,
				'balance_change'		=> abs($points),
				'transaction_balance'	=> $balance,
				'transaction_description' => 'Points are spent on order #' . $order_id,
				'balance_log'			=> $balance_log,
				'order_status'			=> $order_status,
				'transaction_date'		=> $transaction_date,
				'comments'				=> $comments,
				'last_updated'			=> $last_updated
			));
			aw_get_update_post_meta($order_id, $points_type, 1);
		}

		update_post_meta($order_id, '_rd_front_order', 'yes');

		if (isset($_COOKIE[$apply_point_cookie]) && 1 == $_COOKIE[$apply_point_cookie]) {
			setcookie($apply_point_cookie, '0', 0, $path, $host);
		}
	}

	public static function aw_reward_points_total_order( $user_id, $order_grand_total) {
		$customer_orders = get_posts( array(
		'numberposts' => - 1,
		'meta_key'    => '_customer_user',
		'meta_value'  => $user_id,
		'post_type'   => array('shop_order'),
		'post_status' => array('wc-completed', 'wc-processing')
		));
		$total = array('actual' => 0, 'calculation' => 0);
		$orders_total = 0;

		foreach ($customer_orders as $customer_order) {
			$order = wc_get_order( $customer_order );
			$orders_total += $order->get_total();
		}

		$total['actual'] = $orders_total;
		$total['calculation'] = $orders_total - $order_grand_total;
		if ($total['calculation'] < 0) {
			$total['calculation'] = 0;			
		}
		return $total;
	}
}

