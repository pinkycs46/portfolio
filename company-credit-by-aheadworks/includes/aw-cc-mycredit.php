<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

$CCMyCredit = new CCMyCredit();

class CCMyCredit {
	public function __construct() {

		add_filter('woocommerce_account_menu_items', array('CCMyCredit', 'aw_cc_account_menu_items'));
		/****Woocommerce Hook - Add endpoint title.****/
		add_filter('woocommerce_get_query_vars', array('CCMyCredit', 'aw_cc_mycredit_menu_history_query_vars'));
		add_filter('woocommerce_endpoint_aw-cc-mycredit_title', array('CCMyCredit', 'aw_cc_mycredit_endpoint_title'), 0);
		add_action('woocommerce_account_aw-cc-mycredit_endpoint', array('CCMyCredit', 'aw_cc_account_menu_items_endpoint_content'));
	}

	public static function aw_cc_account_menu_items( $items) {
		$cc_menu_item = array('aw-cc-mycredit' => __('Credit Limit', 'aw_cc_mycredit_limit'));
		$cc_menu_item = array_slice($items, 0, 3, true) + $cc_menu_item + array_slice($items, 1, count($items), true);
		return $cc_menu_item;

	}

	public static function aw_cc_mycredit_menu_history_query_vars( $endpoints) {
		$endpoints['aw-cc-mycredit'] = 'aw-cc-mycredit';
		return $endpoints;
	}

	public static function aw_cc_mycredit_endpoint_title( $title) {
		global $wp;
		$parts = explode('/', $wp->request );
		
		$title = __( 'Credit Limit', 'woocommerce' );
		
		if (!empty($parts)) {
			if (is_numeric(end($parts))) {
				$current_page = end($parts);
				/* translators: current page number  */
				$title = sprintf( __( 'Credit Limit (page %d)', 'woocommerce' ), intval( $current_page ) );
			} else {
				if (isset($_GET['paged'])) {
				$current_page = (int) $_GET['paged'];
				/* translators: current page number  */
				$title = sprintf( __( 'Credit Limit (page %d)', 'woocommerce' ), intval( $current_page ) );
				return $title;
				}
			}
		} else {
				$title = __( 'Credit Limit', 'woocommerce' );
				return $title;
		}
		return $title;
	}

	public static function aw_cc_account_menu_items_endpoint_content() {

		$reminder_email 	= '';
		$approved_email 	= '';
		$new_comment_email 	= '';
		$history_per_page 	= get_option('posts_per_page');
		//$current_page 		= ( get_query_var('paged') ) ? get_query_var('paged') : 1;
		$user_id			= get_current_user_id();
		$total_history	= count(aw_cc_get_user_credit_history( $user_id ));

		$structure = get_option( 'permalink_structure' );
		$history_per_page 	= get_option('posts_per_page');
		$pages 		= ceil($total_history/$history_per_page);
		$nexturl = '';
		$current_page = 1;
		if (isset($_GET['paged'])) {
			$current_page = (int) $_GET['paged'];
		}
		switch ($structure) {
			case '/%year%/%monthnum%/%day%/%postname%/':
			case '/%year%/%monthnum%/%postname%/':
			case '/archives/%post_id%':
			case '/%postname%/':
				global $wp;
				$parts = explode('/', $wp->request );
				if (is_numeric(end($parts))) {
					$current_page = end($parts);
				}
				$nexturl = esc_url( wc_get_endpoint_url( 'aw-cc-mycredit', $current_page + 1 ) );
				break;
			default:
				$nexturl =  esc_url(add_query_arg('paged', $current_page + 1));	
		}

		$offset 			= ( $current_page - 1 ) * $history_per_page;	
		$credit_detail 		= aw_cc_get_user_credit_detail($user_id);
		
		if (!empty($credit_detail)) {
			$credit_limit 		= aw_cc_convert_currency($credit_detail->credit_limit);
			$credit_balance		= aw_cc_convert_currency($credit_detail->credit_balance);
			$available_credit	= aw_cc_convert_currency($credit_detail->available_credit);	
		
			?>
		<div class="cc-col-row">
				<div class="cc-col-two">
					<div class="card">
						<div class="card-body">
							<h3>Credit Information</h3> 
							<div class="aw_cc_credit_information">
								 
								<div class="cc-col-row">
									<div class="cc-col-three">
										<label>Credit Balance</label>
										<strong>
										<?php 
										if (!empty($credit_balance) ) {
											if ($credit_balance<0) {
												echo '-' . wp_kses_post(aw_cc_get_amount(abs($credit_balance))); 
											} else {
												echo wp_kses_post(aw_cc_get_amount($credit_balance)); 
											}
										}
										?>
										</strong>
									</div>
									<div class="cc-col-three">
										<label>Available Credit</label>
										<strong>
										<?php 
										if (!empty($available_credit)) {
											echo wp_kses_post(aw_cc_get_amount( $available_credit )); 	
										}
										?>
										</strong>
									</div>
									<div class="cc-col-three">
										<label>Credit Limit</label>
										<strong>
										<?php 
										if (!empty($credit_limit)) {
											echo wp_kses_post(aw_cc_get_amount($credit_limit));	
										}
										?>
											</strong>
									</div>
								</div>

							</div>

						</div>
					</div>
				</div>
				 
		</div>
		<?php 	
		}
		$history  		= self::aw_cc_get_credit_history_user_perpage( $user_id , $history_per_page , $offset );
		$user_id 		= get_current_user_id();

		if (empty($history)) {  
			?>
					<div class="woocommerce-notices-wrapper"></div>
					<p class="woocommerce-noreviews"><?php esc_html_e( 'There is no Credit History yet.', 'woocommerce' ); ?></p>
				<?php 	
				return ;
		}
		?>
			<div class="shop_table_responsive_grid">		
			<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
				<thead>
					<tr>
						<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><span class="nobr">Date</span></th>
						<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-date"><span class="nobr">Action</span></th>
						<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-status"><span class="nobr">Amount</span></th>
						<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-total"><span class="nobr">Available Credit</span></th>
						<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-balance"><span class="nobr">Credit Balance</span></th>
						<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-limit"><span class="nobr">Credit Limit</span></th>
						<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-customercomment"><span class="nobr">Comment</span></th>
					</tr>
				</thead>
					<tbody>
							<?php 
							if (!empty($history)) {
								foreach ($history as $record) { 
									?>
								<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-on-hold order">
									<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" data-title="Order">
									<?php 
										echo esc_html(gmdate(get_option('date_format'), strtotime( $record->last_payment))) ;
									?>
									</td>

									<td data-title="Action">
									<?php 
										echo esc_html($record->transaction_status);
									?>
									</td>

									<td data-title="Amount">
									<?php 
									if (0 != $record->transaction_amount) {
										if ($record->transaction_amount<0) {
											echo '<span class="negative_val">-' . wp_kses_post(aw_cc_get_amount(aw_cc_convert_currency(abs($record->transaction_amount)))) . '</span>';	
										} else {
											echo  '<span class="positive_val">+' . wp_kses_post(aw_cc_get_amount(aw_cc_convert_currency(abs($record->transaction_amount)))) . '</span>';	
										}
									}
									?>
									</td>

									<td data-title="Available Credit">
									<?php
									if ($record->credit_balance<0) {
										echo '-' . wp_kses_post(aw_cc_get_amount(aw_cc_convert_currency(abs($record->credit_balance))));
									} else {
										echo wp_kses_post(aw_cc_get_amount(aw_cc_convert_currency($record->credit_balance)));	
									}
									?>
									</td>

									<td data-title="Credit Balance">
									<?php 
										echo wp_kses_post(aw_cc_get_amount(aw_cc_convert_currency($record->available_credit)));
									?>
									</td>

									<td data-title="Credit Limit">
									<?php 
										echo wp_kses_post(aw_cc_get_amount(aw_cc_convert_currency($record->credit_limit)));
									?>
									</td>
									<td data-title="Comment">
									<?php 
									if (''!=$record->comment_to_customer) {
											
										if ('Purchased'===$record->transaction_status || 'Refunded'===$record->transaction_status || 'Cancelled'===$record->transaction_status ) {
												
											$order 		= wc_get_order($record->order_id);
											$order_url 	= $order->get_view_order_url();
											echo 'Order <a href="' . esc_url($order_url) . '" alt="order" target="_blank">' . esc_html($record->comment_to_customer) . '</a>';	
										} else {
											echo esc_html($record->comment_to_customer);	
										}
									}
											
									?>
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
		
		if ( $total_history > 1  && get_option( 'posts_per_page' ) ) : 
			 
			?>
		<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
			<?php 
			
			if ( 1 !== intval($current_page) ) :
				$current_page_next = $current_page - 1;
				if (isset($_GET['paged'])) {
					$premalink 	= esc_url(add_query_arg('paged', $current_page_next));
				} else {
					$premalink 	= esc_url( wc_get_endpoint_url( 'aw-cc-mycredit', $current_page_next));
				}

				?>
				<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url($premalink);//echo esc_url( wc_get_endpoint_url( 'aw-cc-mycredit', '', $premalink  ) ); ?>"><?php esc_html_e( 'Previous', 'woocommerce' ); ?></a>
			<?php 
			endif; 

			if ( intval($pages ) !== intval($current_page) ) :
				?>
				<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url($nexturl); ?>"><?php esc_html_e( 'Next', 'woocommerce' ); ?></a>
			<?php endif; ?>
		</div>
		<?php endif; 
	}

 

	public static function aw_cc_get_credit_history_user_perpage( $user_id, $history_per_page, $offset ) {
		global $wpdb;
		$credit_history = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_company_credit_history WHERE user_id = %d ORDER BY `last_payment` DESC LIMIT %d OFFSET %d ", "{$user_id}", "{$history_per_page}", "{$offset}") );

		return $credit_history;
	}
}
?>
