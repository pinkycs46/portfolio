<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class AwRewardAdminPayByPoints {

	public static function aw_admin_show_button( $order ) {
		
		$order_status 	= $order->get_status();
		$user_id 		= $order->get_user_id();
		$order_id 		= $order->get_id();

		$btn_html 		= '';	
		$btn_disabled 	= '';
		$style 			= '';

		if ('auto-draft' == $order_status) {
			$btn_disabled = 'disabled="disabled"';
		}

		$balance = '';
		$points_reduced = 'zero';

		if ('0' != $user_id) {
			$get_balance 	= aw_reward_points_get_customer_balance($user_id);
			$balance 		= 'Points: ' . $get_balance->balance;
		}

		if ('auto-draft' == $order_status || 'pending' == $order_status || 'on-hold' == $order_status) {
			global $wpdb;
			$apply_admin_point_cookie = $wpdb->prefix . 'woocommerce_admin_I3QIn9ctULD';
			$apply_admin_point_points = $wpdb->prefix . 'woocommerce_admin_points_I3QIn9ctULD';
			$btn_type 			= 'Apply Points';

			$order = wc_get_order($order_id);
			$point_items = $order->get_items(array('fee'));

			foreach ($point_items as $item_id => $item_obj) {
				if ('fee' == $item_obj->get_type() && ( 'Points' === substr($item_obj->get_name(), 0, 6) )) {
					$btn_disabled = 'disabled="disabled"';
					$style = 'style="color: #a0a5aa !important"';
					
					echo wp_kses('<style>
						#order_fee_line_items tr[data-order_item_id="' . $item_id . '"] .wc-order-edit-line-item-actions a.edit-order-item{
							display: none !important;
						}
						#order_fee_line_items tr[data-order_item_id="' . $item_id . '"] input.wc_input_price{
							display: none !important;
						}
					</style>', wp_kses_allowed_html('post'));
					
				}
			}	

			if (isset($_COOKIE[$apply_admin_point_cookie]) && '0' == $_COOKIE[$apply_admin_point_cookie]) {
				$btn_type 		= 'Apply Points';
				//$balance 		= 'Points: '.$get_balance->balance;
				$balance 		= $balance;
				$points_reduced = 'zero';
			}

			if (isset($_COOKIE[$apply_admin_point_cookie]) && '1' == $_COOKIE[$apply_admin_point_cookie]) {
				//$btn_type = 'Remove Points';
				$btn_type 	= 'Apply Points';
				$btn_disabled = 'disabled="disabled"';
			}

			if (isset($_COOKIE[$apply_admin_point_points]) && $wpdb->prefix != $_COOKIE[$apply_admin_point_points]) {
				$balance = 'Points: ' . sanitize_text_field($_COOKIE[$apply_admin_point_points]);
				$points_reduced = sanitize_text_field($_COOKIE[$apply_admin_point_points]);
				
				if (0 == $points_reduced) {
					$points_reduced = 'zero';
				}
			}

			$get_config = aw_reward_points_get_config();
			if (isset($get_config['spendrates'])) {
				$rates = unserialize($get_config['spendrates']);	
			}
			if (!empty($rates)) {
				$btn_html = '<div id="rd_admin_points_main_dv"><span id="rd_admin_points_main" class="rd_admin_points_main" style="float: left;">
								<button ' . $btn_disabled . ' class="button" type="button" id="rd_reward_admin_points" name="rd_reward_admin_points" value="Apply Points" onclick="return apply_rd_admin_points(this);">' . $btn_type . '</button> 
								<span id="rd_admin_points_txt" class="rd_admin_points_txt" ' . $style . '>' . $balance . '</span>
								<input type="hidden" name="rd_rp_user_id" id="rd_rp_user_id" value="">
								<input type="hidden" name="rd_rp_recalculate" id="rd_rp_recalculate" value="">
								<input type="hidden" name="rd_rp_points_reduced" id="rd_rp_points_reduced" value="' . $points_reduced . '">
							</span></div>';
				$btn_html .= wp_nonce_field('rd_rp_order_nonce_action', 'rd_rp_order_nonce_name');
				echo wp_kses($btn_html, wp_kses_allowed_html('post'));
			} else {
				$btn_html = wp_nonce_field('rd_rp_order_nonce_action', 'rd_rp_order_nonce_name');
				echo wp_kses($btn_html, wp_kses_allowed_html('post'));
				return;
			}
		} else {
			global $wpdb;
			$order = wc_get_order($order_id);
			$point_items = $order->get_items(array('fee'));

			foreach ($point_items as $item_id => $item_obj) {
				if ('fee' == $item_obj->get_type() && ( 'Points' === substr($item_obj->get_name(), 0, 6) )) {
					
					echo wp_kses('<style>
						#order_fee_line_items tr[data-order_item_id="' . $item_id . '"] input.wc_input_price{
							display: none !important;
						}
					</style>', wp_kses_allowed_html('post'));
				}
			}

			$btn_html = wp_nonce_field('rd_rp_order_nonce_action', 'rd_rp_order_nonce_name');
			echo wp_kses($btn_html, wp_kses_allowed_html('post'));
			return;
		}
	}

	public static function aw_admin_get_points() {
		global $wpdb;
		check_ajax_referer( 'rdrewardpoints_admin_order_nonce', 'nonce_odr_ajax' );

		if (!empty($_POST['user_id'])) {
			$user = sanitize_text_field($_POST['user_id']);
		}

		if (!empty($_POST['order_id'])) {
			$order_id = sanitize_text_field($_POST['order_id']);
		}

		if (!empty($_POST['user_name'])) {
			$name = sanitize_text_field($_POST['user_name']);
		}

		$apply_admin_point_cookie = $wpdb->prefix . 'woocommerce_admin_I3QIn9ctULD';
		$apply_admin_point_points = $wpdb->prefix . 'woocommerce_admin_points_I3QIn9ctULD';
		$apply_admin_point_user = $wpdb->prefix . 'woocommerce_admin_user_I3QIn9ctULD';

		$path = '/';
		$host = parse_url(get_option('siteurl'), PHP_URL_HOST);

		$remove_points = '0';

		if (!empty($_COOKIE[$apply_admin_point_user]) && $user != $_COOKIE[$apply_admin_point_user] || 'Guest' == $name) {
			setcookie($apply_admin_point_user, $user, 0, $path, $host);
			$remove_points = '1';
		}

		$point_exists 	= 0;
		$item_id 		= 0;

		if (0 < $order_id && ( 'Guest' == $name || '1' == $remove_points )) {
			$order = wc_get_order($order_id);
			$point_items = $order->get_items(array('fee'));

			foreach ($point_items as $item_id => $item_obj) {
				if ('fee' == $item_obj->get_type() && ( 'Points' === substr($item_obj->get_name(), 0, 6) )) {

					$fee_amt 	= wc_get_order_item_meta($item_id, '_fee_amount', true);
					$ln_ttl		= wc_get_order_item_meta($item_id, '_line_total', true);

					wc_update_order_item_meta($item_id, '_fee_amount', $fee_amt, true);
					wc_update_order_item_meta($item_id, '_line_total', $ln_ttl, true);

					wc_delete_order_item(absint($item_id));
					setcookie($apply_admin_point_cookie, '0', 0, $path, $host);
					setcookie($apply_admin_point_points, $wpdb->prefix, 0, $path, $host);

					$point_exists = 1;
				}
			}
		}

		$get_balance 				= aw_reward_points_get_customer_balance($user);
		$get_balance->point_exists 	= $point_exists;
		$get_balance->item_id 		= $item_id;
		
		$get_config = aw_reward_points_get_config();

		$config_lifetime_sale = array();
		$customer_lifetime_sale = $get_balance->lifetime_sale;

		if (isset($get_config['spendrates'])) {
			$spent_rates = unserialize($get_config['spendrates']);	
			foreach ($spent_rates as $key => $rate) {
				if ($customer_lifetime_sale >= $rate['lifetime_sale']) {
					$config_lifetime_sale[] = $rate['lifetime_sale'];
				}
			}
		}

		$customer_rates = aw_reward_points_getClosest($customer_lifetime_sale, $config_lifetime_sale);

		$get_balance->customer_rates = $customer_rates;

		echo json_encode($get_balance);
		wp_die();
	}

	public static function aw_admin_remove_points_after_order( $order_item_id) {
		$item_obj 	= new WC_Order_Item_Product($order_item_id);
		$order_id	= $item_obj->get_order_id(); 
		$points 	= intval(preg_replace('/[^0-9]+/', '', $item_obj->get_name()), 10);
		$order 		= wc_get_order( $order_id );
		$user_id 	= $order->get_user_id();
		self::aw_delete_transaction($user_id, $points, $order_id);
	}

	public static function aw_display_point_in_order_admin( $order) {
		
		$security 	= '';
		$action 	= '';
		
		if (isset($_POST['security'])) {
			$security = sanitize_text_field($_POST['security']);
		}
		
		if (isset($_POST['action'])) {
			$action = sanitize_text_field($_POST['action']);
		}

		if ('' != $security && '' != $action) {
			$act_check = '';
			switch ($action) {
				case 'woocommerce_add_order_item':
					$act_check = 'order-item';
					break;

				case 'woocommerce_remove_order_item':
					$act_check = 'order-item';
					break;

				case 'woocommerce_load_order_items':
					$act_check = 'order-item';
					break;

				case 'woocommerce_save_order_items':
					$act_check = 'order-item';
					break;
					
				case 'woocommerce_add_coupon_discount':
					$act_check = 'order-item';
					break;
				
				case 'woocommerce_remove_order_coupon':
					$act_check = 'order-item';
					break;

				case 'woocommerce_add_order_fee':
					$act_check = 'order-item';
					break;

				case 'woocommerce_add_order_shipping':
					$act_check = 'order-item';
					break;

				case 'woocommerce_add_order_tax':
					$act_check = 'order-item';
					break;

				case 'woocommerce_calc_line_taxes':
					$act_check = 'calc-totals';
					break;

				default:
					break;
			}

			if ( !wp_verify_nonce( $security, $act_check)) {
				wp_die('Our Site is protected 1');
			}

			global $wpdb;			
			
			$existing_order_id = 0;

			$get_odr_itm = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}woocommerce_order_items WHERE `order_item_id` = %d", "{$order}"));
			$existing_order_id = $get_odr_itm->order_id;

			if (0 != $existing_order_id) {
				$order = wc_get_order($existing_order_id);
				$point_items = $order->get_items(array('fee'));

				foreach ($point_items as $item_id => $item_obj) {
					if ('fee' == $item_obj->get_type() && ( 'Points' === substr($item_obj->get_name(), 0, 6) )) {
						echo wp_kses('<style>
								#order_fee_line_items tr[data-order_item_id="' . $item_id . '"] .wc-order-edit-line-item-actions a.edit-order-item{
									display: none !important;
								}
							</style>', wp_kses_allowed_html('post'));
					}
				}
			}

			$path = '/';
			$host = parse_url(get_option('siteurl'), PHP_URL_HOST);

			$apply_admin_point_cookie = $wpdb->prefix . 'woocommerce_admin_I3QIn9ctULD';
			$apply_admin_point_points = $wpdb->prefix . 'woocommerce_admin_points_I3QIn9ctULD';

			if (!empty($_POST['order_id'])) {
				$order_id = sanitize_text_field($_POST['order_id']);
				$order = wc_get_order($order_id);
				$point_items = $order->get_items(array('fee'));

				foreach ($point_items as $item_id => $item_obj) {
					if ('fee' == $item_obj->get_type() && ( 'Points' === substr($item_obj->get_name(), 0, 6) )) {
						?>
							<script type="text/javascript">
								jQuery('tr[data-order_item_id='+ <?php echo wp_kses($item_id, wp_kses_allowed_html('post')); ?> +'] td.wc-order-edit-line-item div.wc-order-edit-line-item-actions a.edit-order-item').remove();
							</script>
						<?php
					}
				}
			}

			if (isset($_POST['action']) && ( 'woocommerce_remove_order_item' == $_POST['action'] || 'woocommerce_save_order_items' == $_POST['action'] )) {

				if (0 < $order_id) {
					$order = wc_get_order($order_id);
					$point_items = $order->get_items(array('fee'));

					foreach ($point_items as $item_id => $item_obj) {
						if ('fee' == $item_obj->get_type() && ( 'Points' === substr($item_obj->get_name(), 0, 6) )) {
							$fee_amt 	= wc_get_order_item_meta($item_id, '_fee_amount', true);
							$ln_ttl		= wc_get_order_item_meta($item_id, '_line_total', true);

							wc_update_order_item_meta($item_id, '_fee_amount', $fee_amt, true);
							wc_update_order_item_meta($item_id, '_line_total', $ln_ttl, true);

							wc_delete_order_item(absint($item_id));

							?>
								<script type="text/javascript">
									jQuery('tr[data-order_item_id='+ <?php echo wp_kses($item_id, wp_kses_allowed_html('post')); ?> +']').remove();
								</script>
							<?php
						}
					}
					setcookie($apply_admin_point_cookie, '0', 0, $path, $host);
					setcookie($apply_admin_point_points, $wpdb->prefix, 0, $path, $host);
				}
				?>
					<script type="text/javascript">
						var chk_rec_val = jQuery('#rd_rp_points_reduced').val();
						if(chk_rec_val != "zero")
						{
							jQuery('#rd_rp_points_reduced').val("zero");
							jQuery('button.calculate-action').trigger('click');
						}	
					</script>
				<?php
			}

			if (isset($_POST['action']) && ( 'woocommerce_add_order_item' == $_POST['action'] || 'woocommerce_calc_line_taxes' == $_POST['action'] )) {
				?>
			<script type="text/javascript">
				get_rd_admin_points();
			</script>
				<?php
			}
		}	
	}

	public static function aw_recalculate_btn_clk() {
		global $wpdb;
		$apply_admin_point_points = $wpdb->prefix . 'woocommerce_admin_points_I3QIn9ctULD';
		$points_reduced = 'zero';

		if (isset($_COOKIE[$apply_admin_point_points]) && $wpdb->prefix != $_COOKIE[$apply_admin_point_points]) {
			$points_reduced = sanitize_text_field($_COOKIE[$apply_admin_point_points]);
		}
		?>
		<script type="text/javascript">
			jQuery("#rd_rp_recalculate").val("1");
			jQuery("#rd_rp_points_reduced").val("<?php echo wp_kses($points_reduced, wp_kses_allowed_html('post')); ?>");
		</script>
		<?php	
	}

	public static function aw_admin_apply_points() {
		global $wpdb;
		$apply_admin_point_cookie = $wpdb->prefix . 'woocommerce_admin_I3QIn9ctULD';
		$apply_admin_point_points = $wpdb->prefix . 'woocommerce_admin_points_I3QIn9ctULD';
		$path = '/';
		$host = parse_url(get_option('siteurl'), PHP_URL_HOST);

		$type = '';

		check_ajax_referer( 'rdrewardpoints_admin_order_nonce', 'nonce_odr_ajax' );

		if (!empty($_POST['opt_type'])) {
			$type = sanitize_text_field($_POST['opt_type']);
		}

		if (!empty($_POST['user_id'])) {
			$user = sanitize_text_field($_POST['user_id']);
		}

		if (!empty($_POST['order_id'])) {
			$order_id = sanitize_text_field($_POST['order_id']);
		}

		$total_amount = 0;
		$total_amount = get_post_meta( $order_id, '_order_total', true );

		$ret_type = array();

		if (0 < $user) {
			$get_balance 		= aw_reward_points_get_customer_balance($user);
			$get_config 		= aw_reward_points_get_config();
			$customer_balance 	= $get_balance->balance;

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
					setcookie($apply_admin_point_cookie, '0', 0, $path, $host);
					setcookie($apply_admin_point_points, $wpdb->prefix, 0, $path, $host);
					$ret_type['disp'] 				= '';
					$ret_type['pts_rdcd'] 			= '';
					$ret_type['fee_amt'] 			= '';
					$ret_type['ln_ttl'] 			= '';
					$ret_type['item_id'] 			= '';
					$ret_type['msg'] 				= 'No possibility to reward points discounts in the cart';
					$ret_type['type'] 				= 'Apply Points';
					$ret_type['customer_points'] 	= $customer_balance;

				} else {

					$points_reduced 	= ceil($points_reduced);
					$points_reduced_int = $points_reduced;
					$points_reduced 	= $points_reduced . ' Points';
					
					$item_array = array(
					'order_item_name' => 'Points: ' . $points_reduced,
					'order_item_type' => 'fee',
					'order_id'  => $order_id
					);

					$order = wc_get_order($order_id);
					$point_items = $order->get_items(array('fee'));
					$point_exists = 0;

					foreach ($point_items as $item_id => $item_obj) {
						if ('fee' == $item_obj->get_type() && ( 'Points' === substr($item_obj->get_name(), 0, 6) )) {
							$point_exists = 1;

							$fee_amt 	= wc_get_order_item_meta($item_id, '_fee_amount', true);
							$ln_ttl		= wc_get_order_item_meta($item_id, '_line_total', true);

							wc_update_order_item_meta($item_id, '_fee_amount', -$check_which_min_amt, $fee_amt);
							wc_update_order_item_meta($item_id, '_line_total', -$check_which_min_amt, $ln_ttl);

							wc_update_order_item($item_id, $item_array);

							$ret_type['disp'] 		= 'Existing';
							$ret_type['pts_rdcd'] 	= 'Points: ' . $points_reduced;
							$ret_type['fee_amt'] 	= $check_which_min_amt;
							$ret_type['ln_ttl'] 	= $check_which_min_amt;
							$ret_type['item_id'] 	= $item_id;
						}
					}

					if (1 != $point_exists) {
						wc_add_order_item($order_id, $item_array);

						$lastid = $wpdb->insert_id;
						wc_add_order_item_meta($lastid, '_fee_amount', -$check_which_min_amt);
						wc_add_order_item_meta($lastid, '_line_total', -$check_which_min_amt);

						$ret_type['disp'] 		= 'New';
						$ret_type['pts_rdcd'] 	= '';
						$ret_type['fee_amt'] 	= '';
						$ret_type['ln_ttl'] 	= '';
						$ret_type['item_id'] 	= '';
					}
					setcookie($apply_admin_point_cookie, '1', 0, $path, $host);
					setcookie($apply_admin_point_points, $customer_balance - $points_reduced_int, 0, $path, $host);

					$ret_type['msg'] 				= '';
					$ret_type['type'] 				= 'Apply Points';
					$ret_type['customer_points'] 	= $customer_balance - $points_reduced_int;
				}
			}
		}
		echo json_encode($ret_type);
		wp_die();
	}

	public static function aw_admin_remove_points( $order) {
		global $wpdb;
		
		$apply_admin_point_cookie = $wpdb->prefix . 'woocommerce_admin_I3QIn9ctULD';
		$apply_admin_point_points = $wpdb->prefix . 'woocommerce_admin_points_I3QIn9ctULD';
		$path = '/';
		$host = parse_url(get_option('siteurl'), PHP_URL_HOST);
		
		if (isset($_POST['security'])) {
			$security = sanitize_text_field($_POST['security']);
		}

		if ( !wp_verify_nonce( $security, 'order-item')) {
			wp_die('Our Site is protected');
		}
		
		if (!empty($_POST['order_id'])) {
			$order_id = sanitize_text_field($_POST['order_id']);
		} else {
			$order_id = 0;
		}

		if (0 < $order_id) {
			$order = wc_get_order($order_id);
			$point_items = $order->get_items(array('fee'));

			foreach ($point_items as $item_id => $item_obj) {
				if ('fee' == $item_obj->get_type() && ( 'Points' === substr($item_obj->get_name(), 0, 6) )) {

					$fee_amt 	= wc_get_order_item_meta($item_id, '_fee_amount', true);
					$ln_ttl		= wc_get_order_item_meta($item_id, '_line_total', true);

					wc_update_order_item_meta($item_id, '_fee_amount', $fee_amt, true);
					wc_update_order_item_meta($item_id, '_line_total', $ln_ttl, true);

					wc_delete_order_item(absint($item_id));
					
				
					setcookie($apply_admin_point_cookie, '0', 0, $path, $host);
					setcookie($apply_admin_point_points, $wpdb->prefix, 0, $path, $host);
				}
			}
		}
	}

	public static function aw_delete_transaction( $user_id, $points, $order_id) {
		global $wpdb; 	
		$db_balance_table 		= $wpdb->prefix . 'reward_points_balances';
		$db_transcation_table 	= $wpdb->prefix . 'reward_points_transaction_history';

		$transations = get_post_meta($order_id, '_rd_transcations', true);
		if (!empty($transations)) {

			if (in_array('Spent', $transations)) {
				$key = array_search('Spent', $transations);
				unset($transations[$key]);
				$array = array_values($transations);
				update_post_meta($order_id, '_rd_transcations', $array);
				$get_trans = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}reward_points_transaction_history WHERE `user_id` = %d AND `order_id` = %d AND `balance_change` = %d ORDER BY `transaction_id` DESC", "{$user_id}", "{$order_id}", "{$points}" ));
				if (!empty($get_trans)) {
					$get_balance 	= aw_reward_points_get_customer_balance($user_id);
					$currentbalance = $get_balance->balance;

					$balance_ary = array(
						'balance'		=> $currentbalance + $points,
						'spendpoints'	=> $get_balance->spendpoints - $points
					);
					$wpdb->update($db_balance_table, $balance_ary, array('user_id' => $user_id));
					$wpdb->query($wpdb->prepare("DELETE  FROM {$wpdb->prefix}reward_points_transaction_history WHERE `transaction_id` = %d", "{$get_trans->transaction_id}"));
				}	
			}
		}
	}
}
