<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

$aw_giftcard_code_details = new AwGiftCardCodeDetailsPage();

class AwGiftCardCodeDetailsPage {

	public function __construct() {
		self::aw_gift_card_display_giftcard_code_details();
		
	}

	public static function aw_gc_get_code_details_giftcode( $id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'aw_gc_codes';
		$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_codes WHERE `id` = %d ORDER BY id DESC LIMIT 0 , 1"  , "{$id}"));
		return $result;
	}

	public static function aw_gc_get_code_details_transaction( $id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'aw_gc_transactions';
		$result = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_transactions WHERE `giftcard_id` = %d ORDER BY id ASC"  , "{$id}"));
		return $result;
	}

	public static function aw_gift_card_display_giftcard_code_details() {

		if (isset($_GET['giftcode_id'])) {
			$id = sanitize_text_field($_GET['giftcode_id']);
		}

		$id = base64_decode($id);

		$code_information 		= self::aw_gc_get_code_details_giftcode($id);
		$transactions_history 	= self::aw_gc_get_code_details_transaction($id);
		$customer_id 			= get_post_meta($code_information->order_id, '_customer_user', true);
		$customer 			= new WC_Customer( $customer_id );
		$first_name   			= $customer->get_first_name();
		$last_name    			= $customer->get_last_name();
		$customer_name  		= $customer->get_display_name(); 
		?>
		<div class="wrap">
			<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
			<h1 class="wp-heading-inline"><?php esc_html_e('Gift Card Code Details', 'Gift Card Code Details'); ?></h1>
			<hr class="wp-header-end">
			
			<div class="col-row">
				<div class="col-two">
					<div class="card">
						<div class="card-body">
							<h3>Code Information</h3>
							<div class="aw_gc_code_information">
								<div class="aw_gc_code_number">
									<h4><?php echo wp_kses_post($code_information->giftcard_code); ?></h4>
									<span class="aw_gc_code_status"><?php echo wp_kses_post($code_information->transaction_action); ?></span>
								</div>
								<div class="col-row">
									<div class="col-three aw_gc_code_available_amount">
										<label>Available Amount</label><br/>
										<strong><?php echo wp_kses_post(aw_gc_get_amount($code_information->giftcard_balance)); ?></strong>
									</div>
									<div class="col-three aw_gc_code_expiration_date">
										<label>Expiration Date</label><br/>
										<strong><?php echo null != $code_information->expiration_date ? esc_html(gmdate('d/m/Y', strtotime($code_information->expiration_date))) : '&mdash;' ; ?></strong>
									</div>
									<div class="col-three aw_gc_code_created_date">
										<label>Created Date</label><br/>
										<strong><?php echo wp_kses_post(gmdate('d/m/Y', strtotime($code_information->created_date))); ?></strong>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
	
				<div class="col-two">
					<div class="card">
						<div class="card-body">
							<h3>Order Information</h3>
							<div class="aw_gc_order_information">
								<div class="aw_gc_code_number">
									<span>Product Name</span><br/>
									<?php
										$parent_id = wp_get_post_parent_id($code_information->product_id);
									?>
									<h4><a href="<?php echo wp_kses_post(get_edit_post_link($parent_id)); ?>" title="Click here to go to Product Detail Page" alt="Click here to go to Product Detail Page"><?php echo wp_kses_post($code_information->giftcard_product_name); ?></a></h4>
								</div>
								<div class="col-row">
									<div class="col-three aw_gc_code_available_amount">
										<label>Purchase Amount</label><br/>
										<strong><?php echo wp_kses_post(aw_gc_get_amount($code_information->giftcard_amount)); ?></strong>
									</div>
									<div class="col-three aw_gc_code_expiration_date">
										<label>Order</label><br/>
										<strong><a href="<?php echo wp_kses_post(get_edit_post_link($code_information->order_id)); ?>" title="Click here to go to Order Detail Page" alt="Click here to go to Order Detail Page">#<?php echo wp_kses_post($code_information->order_id); ?></a></strong>
									</div>
									<div class="col-three aw_gc_code_created_date">
										<label>Customer Name</label><br/>
										<strong><?php echo wp_kses_post($customer_name); ?></strong>
									</div>
								</div>
							</div>
						</div>
					</div>                    
				</div>
			</div>
			
			<div class="col-row">
				<div class="col-two">
					<div class="card">
						<div class="card-body">
							<h3>Sender Details</h3>
							<div class="aw_gc_detail_information">                                
								<div class="aw_info-name">
									<?php echo wp_kses_post($code_information->sender_name); ?>
								</div>
								<div class="aw_info_email">
									<?php echo wp_kses_post($code_information->sender_email); ?>
								</div>
							</div>
						 </div>
					  </div>
				 </div>
				 
				 <div class="col-two">
					<div class="card">
						<div class="card-body">    
							<h3>Recipient Details</h3>
							<div class="aw_gc_detail_information">                                
								<div class="aw_info-name">
									<?php echo wp_kses_post($code_information->recipient_name); ?>
								</div>
								<div class="aw_info_email">
									<?php echo wp_kses_post($code_information->recipient_email); ?>
								</div>
							</div>
						</div>
					 </div>
				 </div>         
			</div>
			
			<div class="col-row">
				<div class="col-one">
					<div class="card">
						<div class="card-body">
							<h3>History</h3>
							<div class="aw_gc_order_history">                            
								<table class="wp-list-table widefat fixed striped aw_gc_order_history">
									<thead>
										<tr>
											<td>Date</td>
											<td>Action</td>
											<td>Balance</td>
											<td>Balance Change</td>
											<td>Description</td>
										</tr>
									</thead>
									<tbody id="the-list" data-wp-lists="list:aw_gc_order_history">
										<?php
										if (empty($transactions_history)) {
											?>
											<tr>
											<td colspan="5">No result found</td>
											</tr>
										<?php
										} else {
											foreach ($transactions_history as $show_transaction_details) {
												if (ACTIVE === $show_transaction_details->transaction_action) {
													$show_transaction_details->transaction_action = '<span style="color:#008000!important">' . $show_transaction_details->transaction_action . '</span>';
												}

												?>
										<tr>
											<td class="column-id has-row-actions column-primary" type="button" data-colname="Date">
												<?php echo wp_kses_post(gmdate('d/m/Y', strtotime($show_transaction_details->transaction_date))); ?>
												<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
											</td>
											<td data-colname="Action"><?php echo wp_kses_post($show_transaction_details->transaction_action); ?></td>
											<td data-colname="Balance"><?php echo wp_kses_post(aw_gc_get_amount($show_transaction_details->balance)); ?></td>
											<td data-colname="Balance Change">
											<?php 
												if ($show_transaction_details->balance_change < 0) {
													echo wp_kses_post('<span style="color:#FF0000 !important">-' . aw_gc_get_amount(abs($show_transaction_details->balance_change)) . '</span>');	
												} else {
													echo wp_kses_post(aw_gc_get_amount($show_transaction_details->balance_change));	
												}
												?>
											</td>
											<td data-colname="Description"><?php echo wp_kses_post($show_transaction_details->transaction_description); ?></td>                                            
										</tr>
											<?php 
											}
										}
										?>
									</tbody>
								</table>	
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
		<?php 
	}
} //class end
