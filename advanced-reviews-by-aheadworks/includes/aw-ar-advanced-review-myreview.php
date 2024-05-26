<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

$AdvancedReviewMyReview = new AdvancedReviewMyReview();

class AdvancedReviewMyReview {

	public function __construct() {

		add_filter('woocommerce_account_menu_items', array('AdvancedReviewMyReview', 'aw_ar_account_menu_items'));
		/****Woocommerce Hook - Add endpoint title.****/
		add_filter('woocommerce_get_query_vars', array('AdvancedReviewMyReview', 'aw_ar_myreview_menu_history_query_vars'), 0);
		add_filter('woocommerce_endpoint_aw-ar-myreview_title', array('AdvancedReviewMyReview', 'aw_ar_myreview_endpoint_title'), 0);
		add_action('woocommerce_account_aw-ar-myreview_endpoint', array('AdvancedReviewMyReview', 'aw_ar_account_menu_items_endpoint_content'));

		add_filter( 'template_redirect', array('AdvancedReviewMyReview','aw_ar_redirect_url'));
		add_action( 'admin_post_nopriv_save_notification_setting', array('AdvancedReviewMyReview','aw_ar_save_notification_setting'));
		add_action( 'admin_post_save_notification_setting', array('AdvancedReviewMyReview', 'aw_ar_save_notification_setting'));

	}
	public static function aw_ar_account_menu_items( $items) {
		$rd_menu_item = array('aw-ar-myreview' => __('My Product Reviews', 'aw_ar_myreview_points'));
		$rd_menu_item = array_slice($items, 0, 3, true) + $rd_menu_item + array_slice($items, 1, count($items), true);
		return $rd_menu_item;
	}

	public static function aw_ar_myreview_menu_history_query_vars( $endpoints) {
		$endpoints['aw-ar-myreview'] = 'aw-ar-myreview';
		return $endpoints;
	}

	public static function aw_ar_myreview_endpoint_title( $title) {
		return __( 'My Product Reviews', 'woocommerce' );
	}

	public static function aw_ar_account_menu_items_endpoint_content() {
		$reminder_email 	= '';
		$approved_email 	= '';
		$new_comment_email 	= '';
		$reviews_per_page 	= get_option('posts_per_page');
		$current_page 		= ( get_query_var('paged') ) ? get_query_var('paged') : 1;
		$offset 			= ( $current_page - 1 ) * $reviews_per_page;		

		$args_review		= array(
									'status'	=> 'approve',
									'type' 		=> 'review',
									'user_id'	=> get_current_user_id(),
									'count'	 	=>	true

								);
		$total_review		= get_comments($args_review);
		$args 				= array(
									'status'	=> 'approve',
									'type' 		=> 'review',
									'user_id'	=> get_current_user_id(),
									'number' 	=> $reviews_per_page,
									'offset' 	=> $offset
								);
		$comments_query = new WP_Comment_Query();
		$comments  		= $comments_query->query( $args );
		$user_id 		= get_current_user_id();
		$reminder_email_enabled 	= get_user_meta($user_id, 'is_ar_reminder_email_enabled', true);
		$approved_email_enabled 	= get_user_meta($user_id, 'is_ar_approved_email_enabled', true);
		$new_comment_email_enabled 	= get_user_meta($user_id, 'is_ar_new_comment_email_enabled', true);
		if ('yes' === $reminder_email_enabled) {
			$reminder_email 	= 'checked';
		}
		if ('yes' === $approved_email_enabled) {
			$approved_email 	= 'checked';
		}
		if ('yes' === $new_comment_email_enabled) {
			$new_comment_email 	= 'checked';
		}
		 
		if (!empty(get_option('aw_ar_notification_add_msg' . $user_id))) { ?>
			 <p class="woocommerce-awar-success"><?php echo esc_html(get_option('aw_ar_notification_add_msg' . $user_id)); ?></p>

				<!-- <p><strong>< ?php echo esc_html(get_option('aw_ar_notification_add_msg'.$user_id)); ?></strong></p> -->
			 
		<?php delete_option( 'aw_ar_notification_add_msg' . $user_id); } ?>
		<div class="aw_ar_notifications">
			<p>Notifications</p>
			<form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" > 	<?php wp_nonce_field( 'aw_ar_save_notification_form', 'aw_ar_save_notification_form_nonce' ); ?>
				<input type="hidden" value="save_notification_setting" name="action" />
				 <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					 <input type="checkbox" value="yes" name="is_ar_reminder_email_enabled" <?php echo wp_kses($reminder_email, wp_kses_allowed_html('post')); ?>>
					 <label>Remind me to write a review after a purchase</label>
				 </p>
				 <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					 <input type="checkbox" value="yes" name="is_ar_approved_email_enabled" <?php echo wp_kses($approved_email, wp_kses_allowed_html('post')); ?>><label> Notify me when a review was approved </label>
				 </p>
				 <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">	
					 <input type="checkbox" value="yes" name="is_ar_new_comment_email_enabled" <?php echo wp_kses($new_comment_email, wp_kses_allowed_html('post')); ?>><label>Notify me about a new comment on my review</label>
				  </p>
				<P>
					<input type="submit" name="aw_ar_submit" value="Save">
				</P>
			</form>
		</div>	
			<?php 
			if (empty($comments)) {  
				?>
					<div class="woocommerce-notices-wrapper"></div>
					<p class="woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'woocommerce' ); ?></p>
					<?php 	
					return ;
			}
			?>
						
			<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
				<thead>
					<tr>
						<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><span class="nobr">Created</span></th>
						<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-date"><span class="nobr">Product Name</span></th>
						<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-status"><span class="nobr">Status</span></th>
						<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-total"><span class="nobr">Rating</span></th>
						<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-actions"><span class="nobr">Summary</span></th>
					</tr>
				</thead>
					<tbody>
						
							<?php 
							if (!empty($comments)) {
								foreach ($comments as $comment) { 
									?>
								<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-on-hold order">
									<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" data-title="Order">
									<?php 
										echo esc_html(gmdate(get_option('date_format'), strtotime( $comment->comment_date ))) ;
									?>
											
									</td>
									<td>
									<?php 
										echo esc_html(get_the_title($comment->comment_post_ID));
									?>
										
									</td>
									<td>
									<?php 
									if (1 == $comment->comment_approved) {
										echo 'Approved';	
									} else {
										echo 'Unapproved';	
									}
									?>
										
									</td>

									<td>
									<?php
										$rate = (int) get_comment_meta($comment->comment_ID, 'rating', true); 
									?>
										<div class='star-rating-container'>
											<div itemprop="reviewRating" class="star-rating" title="<?php echo wp_kses($rate, wp_kses_allowed_html('post')); ?>">
												<span style="width:<?php echo esc_html(15 * $rate); ?>px"> <?php esc_html('out of 5', 'woocommerce'); ?></span>
											</div>
										</div>
									</td>
									<td>
									<?php 
										echo '' != esc_html($comment->comment_content)  ? esc_html($comment->comment_content) : '';
									?>
										
									</td>
								</tr>

								<?php	
								}
							}
							?>
					</tbody>
			</table>
		 
	<?php 
		if ( 1 < $total_review  && get_option( 'page_comments' ) ) : 
			?>
		<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
			<?php 
			$reviews_per_page 	= get_option('comments_per_page');
			$pages 		= ceil($total_review/$reviews_per_page);
			if (isset($_GET['paged'])) {
				$current_page = (int) $_GET['paged'];
			}
			if ( 1 !== intval($current_page) ) :
				$current_page_next = $current_page - 1;
					$premalink = add_query_arg('paged', $current_page_next, get_permalink());
				?>
				<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url( wc_get_endpoint_url( 'aw-ar-myreview', '', $premalink  ) ); ?>"><?php esc_html_e( 'Previous', 'woocommerce' ); ?></a>
			<?php endif; ?>

				<?php 
				if ( intval($total_review ) !== intval($current_page) ) :
					$current_page_prev = $current_page + 1;
					$premalink = add_query_arg('paged', $current_page_prev, get_permalink());
					?>
				<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( wc_get_endpoint_url( 'aw-ar-myreview', '' , $premalink ) ); ?>"><?php esc_html_e( 'Next', 'woocommerce' ); ?></a>
				<?php endif; ?>
		</div>
		<?php endif; 
	}

	public static function aw_ar_save_notification_setting() {
		
		if (isset($_POST['aw_ar_save_notification_form_nonce'])) {
			$aw_ar_save_notification_form_nonce = sanitize_text_field($_POST['aw_ar_save_notification_form_nonce']);
		}
		if ( !wp_verify_nonce( $aw_ar_save_notification_form_nonce, 'aw_ar_save_notification_form' )) {
			wp_die('Our Site is protected');
		}
		if (isset($_POST['aw_ar_submit'])) {
			$user_id = get_current_user_id();
			if (isset($_POST['is_ar_reminder_email_enabled'])) {
				update_user_meta($user_id , 'is_ar_reminder_email_enabled', sanitize_text_field($_POST['is_ar_reminder_email_enabled']));
			} else {
				update_user_meta($user_id , 'is_ar_reminder_email_enabled', 'no');
			} 

			if (isset($_POST['is_ar_approved_email_enabled'])) {
				update_user_meta($user_id , 'is_ar_approved_email_enabled', sanitize_text_field($_POST['is_ar_approved_email_enabled']));
			} else {
				update_user_meta($user_id , 'is_ar_approved_email_enabled', 'no');
			} 

			if (isset($_POST['is_ar_new_comment_email_enabled'])) {
				update_user_meta($user_id , 'is_ar_new_comment_email_enabled', sanitize_text_field($_POST['is_ar_new_comment_email_enabled']));	
			} else {
				update_user_meta($user_id , 'is_ar_new_comment_email_enabled', 'no');
			}
			update_option('aw_ar_notification_add_msg' . $user_id, 'Notification saved successfully');
		}
		wp_redirect(wp_get_referer());
	}

	public static function aw_ar_redirect_url() {
		if (is_user_logged_in()) {
			$user_id = get_current_user_id();
			$reminder_email_enabled 	= get_user_meta($user_id, 'is_ar_reminder_email_enabled', true);
			$approved_email_enabled 	= get_user_meta($user_id, 'is_ar_approved_email_enabled', true);
			$new_comment_email_enabled 	= get_user_meta($user_id, 'is_ar_new_comment_email_enabled', true);
			if (empty($reminder_email_enabled)) {
				update_user_meta($user_id , 'is_ar_reminder_email_enabled', 'yes');
			}
			if (empty($approved_email_enabled)) {
				update_user_meta($user_id , 'is_ar_approved_email_enabled', 'yes');
			}
			if (empty($new_comment_email_enabled)) {
				update_user_meta($user_id , 'is_ar_new_comment_email_enabled', 'yes');
			}
		}
	}
}
?>
