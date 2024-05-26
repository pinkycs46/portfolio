<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class AwGiftCardMyGiftCards {

	public static function aw_gc_account_add_endpoint() {
		 add_rewrite_endpoint( 'my-gift-cards', EP_ROOT | EP_PAGES );
	}

	public static function aw_gc_account_menu_items( $items) {
		$aw_gcmenu_item = array('my-gift-cards' => __('My Gift Cards', 'aw_gc_my_gift_cards'));
		$aw_gcmenu_item = array_slice($items, 0, 3, true) + $aw_gcmenu_item + array_slice($items, 1, count($items), true);
		return $aw_gcmenu_item;
	}

	public static function aw_gc_account_menu_history_query_vars( $endpoints) {
		$endpoints['my-gift-cards'] = 'my-gift-cards';
		return $endpoints;
	}

	public static function aw_gc_users_gift_card_endpoint_title( $title) {
		return __( 'My Gift Cards', 'woocommerce' );
	}

	public static function aw_gc_add_new_gift_card_ajax() {

		check_ajax_referer( 'aw_giftcard_public_nonce', 'aw_gc_front_nonce_ajax' );

		if (isset($_POST['new_gc_code'])) {
			$new_gc_code = sanitize_text_field($_POST['new_gc_code']);
		}

		$check_new_gift_code 	= check_gift_code_validate($new_gc_code);

		if (!empty($check_new_gift_code)) {

			global $wpdb;

			$db_aw_gc_ugc_table = $wpdb->prefix . 'aw_gc_users_gift_card';
			$aw_gc_codes_id 	= $check_new_gift_code->id;
			$user_id 			= get_current_user_id();

			$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_users_gift_card Where `aw_gc_codes_id` = %d AND `user_id` = %d", "{$aw_gc_codes_id}", "{$user_id}"));

			if (empty($result)) {
				$wpdb->insert($db_aw_gc_ugc_table, array(
					'aw_gc_codes_id' 	=> $aw_gc_codes_id,
					'user_id'       	=> $user_id,
					'created_date'		=> gmdate('Y-m-d H:i:s'),
				));
				echo 'Code added successfully';
				update_option('aw_gc_code_add_msg_' . $user_id, 'Code added successfully');
			} else {
				echo 'Specified code already exist';
			}
		} else {
			echo '0';
		}
		wp_die();
	}

	public static function aw_gc_get_codes_add_new_gift_card() {
		global $wpdb;

		$data		= array();
		$user_id 	= get_current_user_id();
		$result 	= $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_users_gift_card Where `user_id` = %d", "{$user_id}"));

		if (!empty($result)) {

			$ids = '';

			foreach ($result as $results) {
				$ids .= $results->aw_gc_codes_id . ','; 
			}
			$ids = rtrim($ids, ',');

			$get_gc_info = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_codes WHERE `id` IN (%5s) AND `giftcard_trash_status` = %d AND (`transaction_action` = %s OR `transaction_action` = %s)", "{$ids}", 1, 'Activated', 'Updated'));
			if (!empty($get_gc_info)) {
				return $get_gc_info;
			} else {
				return $data;
			}
		} else {
			return $data;
		}
	}

	public static function aw_gc_account_menu_items_endpoint_content() {
		?>
			<div class="aw_gc_mgc_main">
				<table id="aw_gc_mgc_gcb" class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
					<thead>
						<?php
						$user_id 	= get_current_user_id();
						if ( get_option('aw_gc_code_add_msg_' . $user_id) ) {
							?>
						<tr>
							<div class="woocommerce">
								<div class="woocommerce-message" role="alert">Gift Card Code added successfully</div>
							</div>
						</tr>
						<?php
							delete_option('aw_gc_code_add_msg_' . $user_id);
						}
						?>
						<tr>
							<th class="woocommerce-orders-table__header woocommerce-orders-table__header-gcb" colspan="2">
								<span class="nobr"><h3>Gift Card Balance</h3></span>
							</th>
						</tr>
						<tr>
							<th class="woocommerce-orders-table__header woocommerce-orders-table__header-gcbalance">
								<span class="nobr"><?php echo wp_kses_post(aw_gc_get_amount(aw_gc_get_user_total_balance())); ?></span>
							</th>
							<th class="woocommerce-orders-table__header woocommerce-orders-table__header-gcaddnew">
								<div class="aw-gift-balance">
									<div class="field">
										<input type="button" onclick="return open_a_gift_card()" class="button alt" id="open_a_gift_card" value="Add a gift card">
									</div>
									
									<!-- Popup Balance -->
									<div id="aw_gc_users_gift_card_Modal" class="aw_gc_users_gift_card_modal">
										<div class="aw_gc_users_gift_card-content">
											<div class="aw_gc_mod-header"><h2>New gift card</h2></div>
											<div class="aw_gc_users_gift_cards">
												<?php wp_nonce_field( 'aw_gc_users_gift_card', 'aw_gc_users_gift_nonce' ); ?>
													<input type="hidden" name="action" value="aw_gc_users_gift_card">	
													<!--<input type="hidden" value="" name="user_id" class="allids">-->
													<ul>
														<li>
															<div class="control">
																<input type="text" class="input-text" id="new_gift_card_input" name="new_gift_card" value="" placeholder="Enter code">
															</div>
															<span id="aw_gc_new_gift_card_txt"></span>
														</li>
													</ul>
													<div class="aw_gc_users_gift_card_modal_action_btns">
														<input type="submit" name="new_gift_card_add" class="new_gift_card_add_btn button alt" value="Add" id="new_gift_card_add" onclick="return add_new_gift_card()">
														<input type="button" name="" class="aw_gc_users_gift_card_modal_close button aw_gc_users_gift_card_inactive-btn " value="Cancel" id="new_gift_card_close_button" onclick="return close_modal_gift_card()">
														<input type="button" name="" class="aw_gc_users_gift_card_modal_close aw_gc_users_gift_card_inactive-btn " value="Close" id="new_gift_card_fclose_button" onclick="return fclose_modal_gift_card()" style="display:none;">
													</div>
											</div>
										</div>
									</div>
								</div>
								<!-- Popup Balance -->
							</th>
						</tr>
					</thead>
				</table>	
				
				<table id="aw_gc_mgc_gci" class="wp-list-table widefat fixed woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
					<thead>
						<tr>
							<th class="woocommerce-orders-table__header woocommerce-orders-table__header-gci" colspan="4" style="border-bottom: 2px solid #fff;">
								<span class="nobr">Gift Card Information</span>
							</th>
						</tr>
						<tr class="aw_gc-mob-hide">
							<th class="woocommerce-orders-table__header woocommerce-orders-table__header-date">
								<span class="nobr">Date</span>
							</th>
							<th class="woocommerce-orders-table__header woocommerce-orders-table__header-code-number">
								<span class="nobr">Code number</span>
							</th>
							<th class="woocommerce-orders-table__header woocommerce-orders-table__header-available-amount">
								<span class="nobr">Available amount</span>
							</th>
							<th class="woocommerce-orders-table__header woocommerce-orders-table__header-expiration-date">
								<span class="nobr">Expiration date</span>
							</th>
						</tr>
					</thead>

					<tbody>
						<?php
							$data = self::aw_gc_get_codes_add_new_gift_card();
						if (empty($data)) {
							?>
								<tr class="woocommerce-orders-table__row woocommerce-orders-table__row">
									<td colspan="4">No active gift cards exist</td>
								</tr>
							<?php
						} else {
							foreach ($data as $show_info) {
								?>
									<tr class="woocommerce-orders-table__row woocommerce-orders-table__row">
										<td data-colname="Created Date" class="column-id has-row-actions column-primary" type="button">
										<?php echo null != $show_info->created_date ? esc_html(gmdate(get_option('date_format'), strtotime( $show_info->created_date))) : '' ; ?>
										</td>
										<td data-colname="Code"><?php echo wp_kses_post($show_info->giftcard_code); ?></td>
										<td data-colname="Amount"><?php echo wp_kses_post(aw_gc_get_amount(aw_gift_code_convert_currency($show_info->giftcard_balance))); ?></td>
										<td data-colname="Exp Date">
										<?php echo null != $show_info->expiration_date ? esc_html(gmdate(get_option('date_format'), strtotime( $show_info->expiration_date))) : '&mdash;' ; ?>
										</td>
									</tr>
									<?php
							}
						}
						?>
					</tbody>
				</table>
			</div>
		<?php
	}
} //class end
