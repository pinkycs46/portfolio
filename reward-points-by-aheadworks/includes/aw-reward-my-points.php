<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class AwRewardMyPoints {

	public static function aw_account_add_endpoint() {
		 add_rewrite_endpoint( 'rd-my-points', EP_ROOT | EP_PAGES );
	}

	public static function aw_account_menu_items( $items) {
		$rd_menu_item = array('rd-my-points' => __('My Points', 'rd_reward_points'));
		$rd_menu_item = array_slice($items, 0, 3, true) + $rd_menu_item + array_slice($items, 1, count($items), true);
		return $rd_menu_item;
	}

	public static function aw_account_menu_history_query_vars( $endpoints) {
		$endpoints['rd-my-points'] = 'rd-my-points';
		return $endpoints;
	}

	public static function aw_mypoint_endpoint_title( $title) {
		return __( 'My Points', 'woocommerce' );
	}

	public static function aw_account_menu_items_endpoint_content() {
		$user = aw_reward_points_get_user();
		if ($user) {
			$get_balance = aw_reward_points_get_customer_balance($user);
			$get_config = aw_reward_points_get_config();

			$customer_can_pay_min 	= 0;
			$customer_balance = $get_balance->balance;

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
			}
			?>
				<div class="rd_reward_points_main">
					<div class="rd_reward_points_content">
						<span class="rd_reward_points_txt">Your current Points Balance:</span>
						<span class="rd_reward_points_data"><b><?php echo esc_html($customer_balance); ?> points = <?php echo esc_html(sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), round($customer_can_pay_min, 2) )); ?></b></span>
					</div>
					<div class="rd_reward_points_content">
						<span class="rd_reward_points_txt"><b>Expiration Date</b></span>
						<span class="rd_reward_points_data"><?php echo null != $get_balance->expiration_date ? esc_html(gmdate(get_option('date_format'), strtotime( $get_balance->expiration_date))) : '' ; ?></span>
					</div>
					<div class="rd_reward_points_content">
						<span class="rd_reward_points_txt"><b>Earned Points</span>
						<span class="rd_reward_points_data"><?php echo esc_html($get_balance->earnedpoints); ?></span>
					</div>
					<div class="rd_reward_points_content">
						<span class="rd_reward_points_txt"><b>Spent Points</b></span>
						<span class="rd_reward_points_data">
						<?php
							$minus_sign = '-';
						if ($get_balance->spendpoints < 1) {
							$minus_sign = '';
						}
							echo esc_html($minus_sign . $get_balance->spendpoints);
						?>
						</span>
					</div>
				</div>	
			<?php
		} else {
			return;
		}
	}
}
?>
