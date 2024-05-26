<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

$awadvancedreviewtab = new AwAdvancedReviewTab();

class AwAdvancedReviewTab {

	public function __construct() {

		add_filter('woocommerce_product_tabs', array('AwAdvancedReviewTab', 'aw_ar_advanced_review_tab'), 100, 1);
		add_filter('comment_form_fields', array('AwAdvancedReviewTab', 'aw_ar_reorder_comment_form_fields'), 10 );

		add_action('comment_post', array('AwAdvancedReviewTab', 'aw_ar_save_review_form_data'));

		add_action( 'wp_ajax_aw_ar_get_filtered_review_ajax', array('AwAdvancedReviewTab','aw_ar_get_filtered_review_ajax'));
		add_action( 'wp_ajax_nopriv_aw_ar_get_filtered_review_ajax', array('AwAdvancedReviewTab','aw_ar_get_filtered_review_ajax'));

		add_action( 'woocommerce_review_after_comment_text', array('AwAdvancedReviewTab','aw_ar_meta_value_after_review_text'));	

		add_action('wp_ajax_aw_advanced_review_like_dislike' , array('AwAdvancedReviewTab','aw_advanced_review_like_dislike'));
		add_action('wp_ajax_nopriv_aw_advanced_review_like_dislike', array('AwAdvancedReviewTab','aw_advanced_review_like_dislike'));

		add_action('wp_ajax_aw_ar_abuse_on_review_ajax' , array('AwAdvancedReviewTab','aw_ar_abuse_on_review_ajax'));
		add_action('wp_ajax_nopriv_aw_ar_abuse_on_review_ajax', array('AwAdvancedReviewTab','aw_ar_abuse_on_review_ajax'));

		add_action('wp_ajax_nopriv_save_comment_on_review_ajax', array('AwAdvancedReviewTab', 'aw_ar_save_comment_on_review_ajax'));
		add_action('wp_ajax_save_comment_on_review_ajax', array('AwAdvancedReviewTab', 'aw_ar_save_comment_on_review_ajax'));

		add_filter('woocommerce_review_before_comment_meta', array('AwAdvancedReviewTab','aw_ar_show_recomended_message'), 40 );
		
	}

	public static function aw_ar_advanced_review_tab( $tabs ) {
		
		if (true === aw_ar_get_product_review_enable()) {

			if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
				return;
			}

			// change the callback for the reviews tab
			$tabs['reviews']['callback'] = array(self::class, 'aw_ar_advanced_review_template');
			add_action('wp_footer', array('AwAdvancedReviewTab', 'aw_ar_review_form_validation'));
		}
		return $tabs;
	}

	public static function aw_ar_reorder_comment_form_fields( $fields ) {

		if ( function_exists('is_product') && is_product() ) {
			$fields_order=[
				'author' => 1,
				'email' => 2,
				//'rating' => 3,
				'comment'=> 3,
				//'advantage'=>4,
				'cookies' => 4,
			];
			$fields = array_replace_recursive($fields_order, $fields);
		}
		return $fields;
	}

	public static function aw_ar_review_form_validation() {
		?>
		<script language="javascript">
			jQuery(document).ready(function() {
				if (jQuery('#commentform').hasClass('comment-form')) {
					jQuery('#commentform').addClass('aw_ar_review_frm');
					jQuery('#submit').addClass('aw_ar_submitreview_frm');
				}
				if (jQuery('#commentform').hasClass('aw_ar_review_frm')) {
					jQuery('#commentform').attr( "enctype", "multipart/form-data");
					jQuery('#submit').removeClass('aw_ar_submitreview_frm');
				}
			});

			jQuery('#submit').click(function () {
				if (jQuery.trim(jQuery('#comment').val()).length <= 0) {
					alert('This is required field');
					jQuery([document.documentElement, document.body]).animate({
							scrollTop: jQuery('#aw-ar-comment-form-comment').offset().top
					}, 1000);
					jQuery('#comment').focus();
					return false;
				}
			});
		</script>
		<?php
	}

	public static function aw_ar_save_review_form_data( $comment_id ) {

		if (isset($_POST['aw_ar_postreview_nonce_name'])) {
				$aw_ar_postreview_nonce_name = sanitize_text_field($_POST['aw_ar_postreview_nonce_name']);
		}

		if ( !wp_verify_nonce( $aw_ar_postreview_nonce_name, 'aw_ar_postreview_nonce_action') && '' != $aw_ar_postreview_nonce_name) {
			wp_die('Our Site is protected');
		}

		if (isset($_POST['aw_ar_from_data'])) {
			$aw_ar_from_data = sanitize_text_field($_POST['aw_ar_from_data']);
		}

		if ('aw_ar_from_data' == $aw_ar_from_data) {

			if (isset($_POST['aw_ar_advantage'])) {
				$aw_ar_advantage = sanitize_text_field($_POST['aw_ar_advantage']);
				update_comment_meta($comment_id, 'aw_ar_advantage', $aw_ar_advantage);
			}
			if (isset($_POST['aw_ar_disadvantage'])) {
				$aw_ar_disadvantage = sanitize_text_field($_POST['aw_ar_disadvantage']);
				update_comment_meta($comment_id, 'aw_ar_disadvantage', $aw_ar_disadvantage);
			}
			if (isset($_POST['aw_ar_recommend'])) {
				$aw_ar_recommend = sanitize_text_field($_POST['aw_ar_recommend']);
				update_comment_meta($comment_id, 'aw_ar_recommend', $aw_ar_recommend);
			}
			$existing = maybe_unserialize(get_comment_meta($comment_id, 'aw-ar-reviewimage', true));
			update_comment_meta($comment_id, 'aw_ar_featured', '');
			if (!empty($_FILES['aw_ar_file']['name'])) {
				$image_data = json_encode($_FILES);
				$image_data = json_decode($image_data, true);
				if (!empty($_POST['aw_ar_total_files'])) {
					$final_files= sanitize_text_field($_POST['aw_ar_total_files']);	
					$newimageids= aw_ar_multiimage_upload($image_data['aw_ar_file'], $final_files);
					if (!empty($existing)) {
						$imageids	 = array_unique(array_merge ($newimageids, $existing));	
					} else {
						$imageids	 = $newimageids;
					}
					update_comment_meta($comment_id, 'aw-ar-reviewimage', maybe_serialize($imageids));
				}
			} 

			$comment = get_comment($comment_id);
			if (0 == $comment->comment_approved) {

				$product_id = $comment->comment_post_ID;
				$nickname   = $comment->comment_author;
				$comment_text = $comment->comment_content;
				$is_enabled = aw_ar_get_email_template_active_status('Customer Review Email');
				if ($is_enabled) {
					aw_ar_send_mail( $product_id, $comment_id, $nickname, $comment_text, 'Customer Review Email');
				}
				$is_adminemailenabled = aw_ar_get_email_template_active_status('Admin Email');
				if ($is_adminemailenabled) {
					aw_ar_send_mail($product_id, $comment_id, '', $comment_text , 'Admin Email');
				}
			}
		}
	}

	public static function aw_ar_advanced_review_template() {

		global $product;
		$product_id 			= $product->get_id();
		$loggedin_user_nickname = '';
		$loggedin_user_email 	= '';
		$aw_ar_max_filesize 	= get_option('aw_ar_max_filesize');
		if ( ! comments_open() ) {
			return;
		}
		?>
		<div id="reviews" class="woocommerce-Reviews">
			<div id="comments">
				<h2 class="woocommerce-Reviews-title">
					<?php
					$count = $product->get_review_count();
					if ( $count && wc_review_ratings_enabled() ) {
						/* translators: 1: reviews count 2: product name */
						$reviews_title = sprintf( esc_html( _n( '%1$s review for %2$s', '%1$s reviews for %2$s', $count, 'woocommerce' ) ), esc_html( $count ), '<span>' . get_the_title() . '</span>' );
						echo wp_kses(apply_filters( 'woocommerce_reviews_title', $reviews_title, $count, $product ), wp_kses_allowed_html('post')); // WPCS: XSS ok.
						$display_form = 'none';
					} else {
						esc_html_e( 'Reviews', 'woocommerce' );
						$display_form = 'block';
					}
					?>
				</h2>

				<?php if ( $count && wc_review_ratings_enabled()) : ?>
					<div>
						<div class="aw_ar_avg_count-panel">
							<div class="aw_ar_avg_count">
								<?php 
								global $product;
								$rating_count = $product->get_rating_count();
								$review_count = $product->get_review_count();
								$average      = $product->get_average_rating();
								echo wp_kses($average, wp_kses_allowed_html('post')); 
								?>
							</div>
						
							<div class="woocommerce-product-rating">
	
								<?php 
								$rating =  wc_get_rating_html( $average, $rating_count ); // WPCS: XSS ok. 
									echo wp_kses($rating, wp_kses_allowed_html('post'));
								?>
								<?php if ( comments_open() ) : ?>
									<a href="#reviews" class="woocommerce-review-link" rel="nofollow"><?php sprintf(  '%s Reviews', '%s reviews', wp_kses($review_count, wp_kses_allowed_html('post')), 'woocommerce' , '<span class="count">' . wp_kses( $review_count, wp_kses_allowed_html('post')) . '</span>' ); ?></a>
								<?php endif ?>
							</div>
						</div>
						
						<?php 
							echo esc_html_e(self::aw_ar_rating_progressbar_single_product()); 
							echo esc_html_e(self::aw_ar_get_recommended_comment_average());
						?>
					</div>
					<div class="aw_ar_actions">
						<button class="submit" id="aw_ar_write_review" onclick="aw_ar_askreview('aw_ar_write_review_top')">Write a Review</button>
					</div>	
				<?php endif; ?>
			</div>

			<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>
				<div id="aw_ar_review_form_wrapper">
					<div id="review_form" style="display:<?php echo wp_kses($display_form, wp_kses_allowed_html('post')); ?>">
						<?php
						$commenter    = wp_get_current_commenter();
						$comment_form = array(
							/* translators: %s is product title */
							'title_reply'         => have_comments() ? esc_html__( 'Add a review', 'woocommerce' ) : sprintf( esc_html__( 'Be the first to review &ldquo;%s&rdquo;', 'woocommerce' ), get_the_title() ),
							/* translators: %s is product title */
							'title_reply_to'      => esc_html__( 'Leave a Reply to %s', 'woocommerce' ),
							'title_reply_before'  => '<span id="reply-title" class="comment-reply-title">',
							'title_reply_after'   => '</span>',
							'comment_notes_after' => '',
							'label_submit'        => esc_html__( 'Submit', 'woocommerce' ),
							'logged_in_as'        => '',
							'comment_field'       => '',
							'advanced_field'      => '',
						);

						$name_email_required = (bool) get_option( 'require_name_email', 1 );
						$fields              = array(
							'author' => array(
								'label'    => __( 'Name', 'woocommerce' ),
								'type'     => 'text',
								'value'    => $commenter['comment_author'],
								'required' => $name_email_required,
							),
							'email' => array(
								'label'    => __( 'Email', 'woocommerce' ),
								'type'     => 'email',
								'value'    => $commenter['comment_author_email'],
								'required' => $name_email_required,
							),
						);

						$comment_form['fields'] = array();
						
						foreach ( $fields as $key => $field ) {
							$field_html  = '<p class="comment-form-' . esc_attr( $key ) . '">';
							$field_html .= '<label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] );

							if ( $field['required'] ) {
								$field_html .= '&nbsp;<span class="required">*</span>';
							}

							$field_html .= '</label><input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="' . esc_attr( $field['type'] ) . '" value="' . esc_attr( $field['value'] ) . '" size="30" ' . ( $field['required'] ? 'required' : '' ) . ' /></p>';

							$comment_form['fields'][ $key ] = $field_html;
						}
						
						$account_page_url = wc_get_page_permalink( 'myaccount' );
						if ( $account_page_url ) {
							/* translators: %s opening and closing link tags respectively */
							$comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'woocommerce' ), '<a href="' . esc_url( $account_page_url ) . '">', '</a>' ) . '</p>';
						}
 
						/*$comment_form['comment_field'] .=	'<h2 class="woocommerce-Reviews-title">' . $product->get_name() . '</h1><p class="comment-notes"><span id="email-notes">Your emailaddress will not be published.</span> Required fields are marked <span class="required">*</span></p><p class="comment-notes"></p>'; */
 
						if ( wc_review_ratings_enabled() ) {
							$comment_form['comment_field'] .= '<div class="comment-form-rating" id="aw-ar-comment-form-rating"><label for="rating">' . esc_html__( 'Your rating', 'woocommerce' ) . '</label><select name="rating" id="rating" required>
								<option value="">Rate&hellip</option>
								<option value="5">Perfect</option>
								<option value="4">Good</option>
								<option value="3">Average</option>
								<option value="2">Not that bad</option>
								<option value="1">Very poor</option>
							</select><span class="required">*</span></div>';
						}

						$aw_ar_get_settings_options = aw_ar_get_settings_options();

						if (!empty($aw_ar_get_settings_options) && 'yes' == $aw_ar_get_settings_options->aw_ar_enable_pronandcons) {

							$comment_form['comment_field'] .= ' <p class="comment-form-advantage">
																<label for="advantage">' . esc_html__( 'Advantage', 'woocommerce' ) . '</label>
																	<textarea id="advantage" name="aw_ar_advantage" cols="45" rows="3" required></textarea>
															</p>
															<div style="clear:both;"></div>
															<p class="comment-form-disadvantage">
																<label for="disadvantage">' . esc_html__( 'Disadvantage', 'woocommerce' ) . '</label>
																	<textarea id="disadvantage" name="aw_ar_disadvantage" cols="45" rows="3" required></textarea>
															</p>
															<div style="clear:both;"></div> ';
						}
							$comment_form['comment_field'] .= ' <p class="comment-form-comment" id="aw-ar-comment-form-comment">
															<label for="comment">' . esc_html__( 'Your review', 'woocommerce' ) . '&nbsp;<span class="required">*</span></label>
																<textarea id="comment" name="comment" cols="45" rows="8" required></textarea>
																<input type="hidden" name="aw_ar_from_data" value="aw_ar_from_data" id="aw_ar_from_data">
															</p>
															<div style="clear:both;"></div>
															<p class="comment-form-recommend">
																	<label for="recommend">' . esc_html__( 'Do you recommend this product?', 'woocommerce' ) . '&nbsp;</label>
																	<select name="aw_ar_recommend" >
																		<option value="not specified">Not specified</option>
																		<option value="no">No</option>
																		<option value="yes">Yes</option>
																	</select>
															</p>
															<div style="clear:both;"><p>' . wp_nonce_field('aw_ar_postreview_nonce_action', 'aw_ar_postreview_nonce_name') . '</p></div>';
															
															
						if (!empty($aw_ar_get_settings_options) && 'yes' == $aw_ar_get_settings_options->aw_ar_isattach_file) {							
							$comment_form['comment_field'] .= '<div class="aw-upload-file"><p class="comment-form-reviewimage">
																	<label for="recommend">' . esc_html__( 'Images', 'woocommerce' ) . '&nbsp;</label>
																	 <span class="btn btn-default btn-file"> Browse
																	<input  id="aw_ar_file" name="aw_ar_file[]" type="file" multiple="true" ></span><input type="hidden" id="aw_ar_max_filesize" value="' . $aw_ar_max_filesize . '"> 
																	<input name ="aw_ar_total_files" type="hidden" id="aw_ar_total_files" value="" >
																</p>
																<div id="preview"></div>
																<div style="clear:both;"></div></div>';
						}
						$comment_form['comment_field'] .= '<p style="display:none"><input type="hidden" id="is_user_logged_in" value="' . is_user_logged_in() . '"/></p>';
						if (!empty($aw_ar_get_settings_options) && 'yes' == $aw_ar_get_settings_options->aw_ar_enable_termcondition) {
							$flag_to_display = false;
							if ('guest' == $aw_ar_get_settings_options->aw_ar_whoaccept) {
								if (!is_user_logged_in()) {
									$flag_to_display = true;	
									
								}
							} elseif ('everyone' == $aw_ar_get_settings_options->aw_ar_whoaccept) {
								$flag_to_display = true;
							} else {
								$flag_to_display = false;
							}
							if ($flag_to_display) {
								$comment_form['comment_field'] .= '<p class="comment-form-termcondition"><span class="aw_ar_termcondition" onclick="aw_ar_display_termcondi()">Term and Conditions</span></p>';
								$terms_condi = get_option('woocommerce_registration_privacy_policy_text');
								self::aw_ar_message_popup( $terms_condi );
							}
							
						}
						comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
						?>

					</div>
				</div>
			<?php else : ?>
				<p class="woocommerce-verification-required"><?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'woocommerce' ); ?></p>
			<?php endif; ?>
			<div class="clear"></div>
			<?php
			if ($count && wc_review_ratings_enabled()) {
				self::aw_ar_display_featured_review_section($product_id);
				?>
			<hr>
			<div class="clear"></div>
			<?php 
				self::aw_ar_display_filter_sorting_section($product_id);			
			} 
			?>
			<div class="clear"></div>
			<div class="clear"></div>
			<?php 
			self::aw_ar_display_nonfeatured_review_section($product_id); 
			?>
		</div>
	<?php
	}
	public static function aw_ar_display_featured_review_section( $product_id) {
		$args = array(
							'post_id'		=> $product_id,
							'type'			=> 'review',
							'meta_query' 	=> array(
								  array(
									'key'   => 'aw_ar_featured',
									'value' => 'true'
								  )
								),      
						);
		$featured_comments = get_comments($args);
		wp_reset_query();
		if ( $featured_comments ) :
			?>
			<h3>Featured review</h3>
			<ul class="commentlist">
				<?php 
				foreach ( $featured_comments as $comment ) :  
					?>
					<li class="review byuser comment-author-admin bypostauthor even thread-even depth-1" id="li-comment-<?php echo  wp_kses($comment->comment_ID, wp_kses_allowed_html('post')); ?>">
						<div id="comment-<?php echo wp_kses($comment->comment_ID, wp_kses_allowed_html('post')); ?>" class="comment_container">
							<div class="review-avatar">
							<?php echo get_avatar( $comment->comment_author_email, $size = '60' ); ?>
							</div>
							<div class="comment-text">
								<div class="star-rating">
									<span style="width:'<?php echo ( ( 3 / 5 ) * 100 ); ?>%">
										<strong itemprop="ratingValue" class="rating"><?php echo '3'; ?>
										</strong><?php __( 'out of 5', 'woocommerce' ); ?>
									</span>
								</div> 
								<?php echo esc_html_e(self::aw_ar_show_recomended_message($comment )); ?>
								<p class="meta">

									<strong class="woocommerce-review__author">
										<?php echo wp_kses($comment->comment_author, wp_kses_allowed_html('post')); ?> 
									</strong>
									
									 <?php
										$verified = wc_review_is_from_verified_owner( $comment->comment_ID );
										if ( 'yes' === get_option( 'woocommerce_review_rating_verification_label' ) && $verified ) { 
											?>
										<em class='woocommerce-review__verified verified'>
											<?php 
											echo '(' . esc_attr__( 'verified owner', 'woocommerce' ) . ')' ;
										}
										?>
										</em>
									<span class="woocommerce-review__dash">–</span> 	
									<time class="woocommerce-review__published-date"><?php echo esc_html(get_comment_date( '', $comment )); ?></time>
								</p>
								<div class="description">
									<p>
									<?php 
									if (!empty($comment->comment_content)) {
										echo esc_html($comment->comment_content); 	
									}
									?>
									</p>
								</div>
								<?php self::aw_ar_meta_value_after_review_text($comment); ?>
							</div>
						</div>
					</li>
				<?php	
				endforeach; 	
				?>
			</ul>

			<?php
		endif;
	}

	public static function aw_ar_display_filter_sorting_section( $product_id) {
		?>
		<div class="aw_ar_filters_wrap"> 
			<input type="hidden" value="<?php echo wp_kses($product_id, wp_kses_allowed_html('post')); ?>" class="aw_ar_product_id" name ="aw_ar_product_id">
			<div class="aw_ar_form-field-wrap">
				<input type="checkbox" class="buttontohref" name="aw_ar_with_picture" value="aw-ar-reviewimage" onchange="aw_ar_apply_filter_ajax(this,false,'')"> 
				With Pictures
			</div>
			<div class="aw_ar_form-field-wrap">
				<input type="checkbox" class="buttontohref" name="aw_ar_verified_buyer" value="verified" onchange="aw_ar_apply_filter_ajax(this,false,'')"> 
				Verified Buyers 
			</div>
			<div class="aw_ar_form-field-wrap">
				<select onchange="aw_ar_apply_filter_ajax(this,false,'')" class="aw_ar_rating_type">
					<option value="all"> All Review </option>
					<option value="positive"> Positive Reviews </option>
					<option value="critical"> Critical Reviews </option>
					<option value="5">5 stars</option>
					<option value="4">4 stars</option>
					<option value="3">3 stars</option>
					<option value="2">2 stars</option>
					<option value="1">1 stars</option>
				</select>
			</div>
		</div>
		<div class="aw_ar_review-sortby"> 
			<label>Sort By</label>
			<ul class="aw_ar_ulsort">
				<li class="aw_ar_desc"><span onclick="aw_ar_apply_filter_ajax(this,1,'rating')">Rating</span></li>
				<li class="aw_ar_desc active _ascend"><span onclick="aw_ar_apply_filter_ajax(this,2,'newest')">Newest</span></li>
				<li class="aw_ar_desc"><span onclick="aw_ar_apply_filter_ajax(this,3,'helpfulness')">Helpfulness</span></li>
			</ul>
		</div>
		<?php 
	}

	public static function aw_ar_display_nonfeatured_review_section( $product_id) {
		global $wpdb;
		?>
		<div id="unfeaturedreview">
				<?php 
					$reviews_per_page = get_option('comments_per_page');
					$page 	= ( get_query_var('cpage') ) ? get_query_var('cpage') : 1;
					$offset = ( $page - 1 ) * $reviews_per_page;
					$args 	= array(
									'post_id'	=> $product_id,
									'type'		=> 'review',
									'status'	=> 'approve',
									'meta_query'=> array(
														  array(
															'key'   => 'aw_ar_featured',
															'value' => null
														  )
													), 
								);

					$args['count'] 	= true;
					$total_review 	= get_comments($args);
					unset($args['count']);	
					wp_reset_query();
					$comments  		= get_comments($args);
					$total_review	= count($comments);
					wp_reset_query();
					if ( $comments ) :
						?>
						<ol class="commentlist">
							<?php 
							wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', array( 'callback' => 'woocommerce_comments' ) ), $comments );
							?>
						</ol>

						<?php
						if ( $total_review > 1 && get_option( 'page_comments' ) ) :
							$pages = ceil($total_review/$reviews_per_page);
							
							echo '<nav class="woocommerce-pagination">';
							paginate_comments_links(
								apply_filters(
									'woocommerce_comment_pagination_args',
									array(
										'prev_next'    	=> true,
										'prev_text' => __('« Previous'),
										'next_text' => __('Next »'),
										'type'      => 'list',
										'total' 		=> $pages,
										'show_all'     	=> false,
										'add_fragment' => '#tab-reviews'
										/*'current' 		=> $page,
										'add_fragment' => '#tab-reviews',*/
									)
								)
							);
							echo '</nav>';
						endif;
						?>
						<div class="clear"></div>
						<div class="aw_ar_actions">
							<button class="submit" id="aw_ar_write_review" onclick="return aw_ar_askreview('aw_ar_write_review_bottom')">Write a Review</button>  
						</div>	
				<?php endif; ?>
			</div>
			<?php 
	}

	public static function aw_ar_rating_progressbar_single_product() {
		global $product;
		$progressbar 	= '';
		$total_rating 	= 0;


		for ($i=5;$i>=1;$i--) {
			$total_rating += $product->get_rating_count($i);
		}

		$progressbar .= '<div class="aw-ar-row">';
		for ($i=5; $i>=1; $i--) {
			$star_average	= 0;
			$star_total 	= 0;
			$star_total 	= $product->get_rating_count($i);
			if ($total_rating>0) {
				$star_average 	= ( round($star_total/$total_rating) )*100;	
			}
			
			$progressbar 	.= '<div class="aw-ar-side">
									<div>' . $i . ' star</div>
								</div>
								<div class="aw-ar-middle">
									<div class="aw-ar-bar-container">
										<div class="aw-ar-bar-' . $i . '"></div>
									</div>
								</div>
								<div class="aw-ar-side aw-ar-right">
									<div>' . $star_total . ' (' . $star_average . '%)</div>
								</div>';
		}
		$progressbar 		.=	'</div>';
		echo wp_kses($progressbar, wp_kses_allowed_html('post'));
	}

	public static function aw_ar_get_recommended_comment_average() {
		$average 	= 0;
		$args 		= array(
							'comment_type' => 'review',
							'comment_approved' => 1,
							'meta_query' => array(
												  array(
													'key' => 'aw_ar_recommend',
												  )
												),      
							'count' => true
						);

		$my_comment_query = new WP_Comment_Query();
		$total_recommended = $my_comment_query->query( $args );

		$args = array(
						'comment_type' => 'review',
						'comment_approved' => 1,
						'meta_query' => array(
												array(
													'key'   => 'aw_ar_recommend',
													'value' => 'yes'
												)
											),      
						'count' => true
					);
		$my_comment_query = new WP_Comment_Query();
		$yes_recommended  = $my_comment_query->query( $args );
		if ($yes_recommended>0) {
			$average =  round(( $yes_recommended / $total_recommended )*100);	
		}
		return $average . '% of customers recommend this product';

	}

	public static function aw_ar_get_filtered_review_ajax() {
		global $wpdb;
		$args 		= array();
		$meta_array = array();
		$withimage 	= '';
		$verified 	= '';
		$sorty_by 	= '';
		$order 	 	= '';
		$product_id = 0;

		check_ajax_referer( 'rdadvancedrewiew_nonce', 'aw_ar_nonce_ajax' );
		if (isset($_POST['product_id']) && !empty($product_id)) {
			$product_id = sanitize_text_field($_POST['product_id']);
		}
		if (isset($_POST['withimage']) && !empty($_POST['withimage'])) {
			$withimage = sanitize_text_field($_POST['withimage']);
			$meta_array[0]['key'] 		= $withimage;
			$meta_array[0]['value'] 	= '';
			$meta_array[0]['compare'] 	= '!=';
		}
		if (isset($_POST['verified']) && !empty($_POST['verified'])) {
			$verified = sanitize_text_field($_POST['verified']);
			$meta_array[1]['key'] 		= $verified;
			$meta_array[1]['value'] 	= 0;
			$meta_array[1]['compare'] 	= '!=';
		}
		if (isset($_POST['starrate']) && !empty($_POST['starrate'])) {
			$starrate = sanitize_text_field($_POST['starrate']);
			$meta_array[0]['key'] 		= 'rating';
			$meta_array[0]['value'] 	= sanitize_text_field($_POST['starrate']);
			$meta_array[0]['compare'] 	= '=';
			if ('positive' == $starrate) {
				$meta_array[0]['value'] 	= 0;
				$meta_array[0]['compare'] 	= '>';
			}
			if ('critical' == $starrate) {
				$meta_array[0]['value'] 	= 3;
				$meta_array[0]['compare'] 	= '==';
			}
		}
		
		if (isset($_POST['order'])) {
			$order = sanitize_text_field($_POST['order']);
		}
		if (isset($_POST['sortyby'])) {
			$sorty_by = sanitize_text_field($_POST['sortyby']);
			switch ($sorty_by) {
				case 'rating':
						$args['meta_key'] = 'rating';
						$args['orderby'] = 'meta_value';
						$args['order'] =   $order;
					break;	
				case 'newest':
						$args['orderby'] 	= 'comment_ID';
						$args['order'] 		= $order;
					break;	
				case 'helpfulness':
						$args['meta_key'] = 'aw_ar_helpful';
						$args['orderby'] = 'meta_value';
						$args['order'] =   $order;
					break;	
			}
		}
		if ($product_id>0) {
			$args['post_id']	= $product_id;	
		}
		
		$args['type']		= 'review';
		$args['meta_query'] = $meta_array;
		if (empty($meta_array)) {
			$meta_array['key'] 	= 'aw_ar_advantage';
			$args['meta_query'] = $meta_array;
		}
		
		$comments_query = new WP_Comment_Query();
		$comments  		= $comments_query->query( $args );
		if ('' != $withimage) {
			foreach ($comments as $key=>$comment) {
				$image_data = maybe_unserialize(get_comment_meta($comment->comment_ID, 'aw-ar-reviewimage', true));
				if (empty($image_data)) {
					unset($comments[$key]);
				}
			}	
		}

		if ( $comments ) :
			?>
			<ol class="commentlist">
				<?php 
					wp_list_comments(array('callback' => 'woocommerce_comments'), $comments);
				?>
			</ol>

			<?php
			/*if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
				echo '<nav class="woocommerce-pagination">';
				paginate_comments_links(
					apply_filters(
						'woocommerce_comment_pagination_args',
						array(
							'prev_text' => '&larr;',
							'next_text' => '&rarr;',
							'type'      => 'list',
						)
					)
				);
				echo '</nav>';
			endif;*/
		else : 
			?>
			<p class="woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'woocommerce' ); ?></p>
			<?php 
		endif;

		wp_die();
	}



	public static function aw_ar_meta_value_after_review_text( $comment  ) { 
		
		$product_id 		= $comment->comment_post_ID;
		$comment_id 		= $comment->comment_ID;
		$user_id 			= get_current_user_id();//$comment->user_id;
		$comment_approved 	= $comment->comment_approved;
		$comment_type 		= $comment->comment_type;
		$advantage 			= get_comment_meta($comment_id, 'aw_ar_advantage', true);
		$disadvantage 		= get_comment_meta($comment_id, 'aw_ar_disadvantage', true);
		$meta_text			= '';
		$review_images 		= array();

		if (!empty($advantage)) {
			?>
			<div class="aw_ar_pro-cons">
				<h3>Advantages</h3>
				<div class="aw_ar_pro-cons-content">
					<span><?php echo esc_html($advantage); ?></span>
				</div>
			</div>
			<div style="clear:both;"></div>
		<?php 
		}
		if (!empty($disadvantage)) {
			?>
			<div class="aw_ar_pro-cons">
				<h3>Disadvantages</h3>
				<div class="aw_ar_pro-cons-content">
					<span><?php echo esc_html($disadvantage); ?></span>
				</div>
			</div>
			<div style="clear:both;"></div>
		<?php 
		}

		/* Display Images */
		$review_images	= maybe_unserialize(get_comment_meta($comment_id, 'aw-ar-reviewimage', true));
		if (!empty($review_images)) { 
			?>
			<ul class="aw_ar_gallery-front gallery-<?php echo wp_kses($comment_id, wp_kses_allowed_html('post')); ?>"> 
			<?php 	
			foreach ($review_images as $review_image_id) {
				if (wp_get_attachment_image_url($review_image_id, 'thumbnail')) {
					$img_src 		= wp_get_attachment_image_url($review_image_id, 'thumbnail');
					$img_link 		= wp_get_attachment_image_url( $review_image_id, 'full');
					$path 			= wp_get_upload_dir();
					$imagepath 		= explode('uploads', $img_src) ;
					$fullpath  		= $path['basedir'] . $imagepath[1];
					if (file_exists($fullpath)) {
						?>
						<li>
							<div id="ar_aw_preview<?php echo wp_kses($review_image_id, wp_kses_allowed_html('post')); ?>">
								<a class="ar_aw_light" href="<?php echo wp_kses($img_link , wp_kses_allowed_html('post')); ?>">
									<img class="portfolio" src="<?php echo wp_kses($img_src , wp_kses_allowed_html('post')); ?>" alt="Image" onclick="aw_ar_show_lightbox(<?php echo wp_kses($comment_id, wp_kses_allowed_html('post')); ?>)">
								</a>
							</div>
						</li>
						<?php
					}
				}
			} 
			?>
			</ul>
		<?php
		}
		?>
		<div style="clear:both;"></div>
		<div class="aw_ar_review-helpful">
			<p>Was this review helpful?
				<?php 
				echo wp_kses(aw_ar_get_voting_abuse_section($comment_id, $product_id, $user_id, $comment_approved , $comment_type), wp_kses_allowed_html('post')); 
				?>
			</p>
		</div>

		<div style="clear:both;"></div>

		<?php 
		/* Comment are displayed for review */
		aw_ar_commentlist_and_commentform_section( $comment_id, $product_id, $user_id, $comment_approved , $comment_type);
	}

	public static function aw_ar_message_popup( $text ) {
		?>
		<div id="aw_ar_message_Modal" class="aw_ar_msg_modal">
		  <!-- Modal content -->
		  <div class="modal-content" id="aw_ar_popup_area">
			  <span class="close" onclick="aw_ar_close_modal()">&times;</span>
			  <div id="aw_ar_msg_section">
			
				 <p><?php echo wp_kses($text, wp_kses_allowed_html('post')) ; ?></p>
			</div>
		 </div>
		</div>
		<?php
	}

	/* Ajax call for like and dislike */
	public static function aw_advanced_review_like_dislike() {
		global $product;
		$product_id = get_the_ID();
		check_ajax_referer( 'rdadvancedrewiew_nonce', 'aw_ar_nonce_ajax' );
		$count 		= 0;
		$meta_key	= '';
		$users		= array();
		$vote_data	= array();
		$path 		= '/';
		$host 		= parse_url(get_option('siteurl'), PHP_URL_HOST);
		$days 		= 1;//get_option('rd_setting_cookie_days');
		$cookie		= array();
		if (isset($_POST['comment_id'])) {
				$comment_id = sanitize_text_field($_POST['comment_id']);
		}
		$users['changed_image'] = '';
		$users['default_image'] = '';
		if ( isset($_POST['trigger_type']) && isset($_POST['user_id']) ) {
			$skip = false;
			$trigger_type 	= sanitize_text_field($_POST['trigger_type']);
			$meta_key 		= $trigger_type . '_count' ; 

			$vote = get_comment_meta($comment_id, $trigger_type, true);

			if ( 'aw_ar_helpful' === $trigger_type ) {
				$opposite_trigger = 'aw_ar_not_helpful';
				$vote++;
			}
			if ( 'aw_ar_not_helpful' === $trigger_type ) {
				$opposite_trigger = 'aw_ar_helpful';
				$vote++;
			}
			$opposite_meta_key = $opposite_trigger . '_count';
			if ( isset($_COOKIE[$meta_key]) ) {

				$cookie = json_decode(stripslashes(sanitize_text_field($_COOKIE[$meta_key])), true);
				if (in_array($comment_id, $cookie)) {
					$vote = get_comment_meta($comment_id, $trigger_type, true);
					if (0 < $vote) {
						$vote--;	
					}
					update_comment_meta($comment_id, $trigger_type, $vote);
					$key = array_search($comment_id, $cookie);
					unset($cookie[$key]);
					$value = json_encode($cookie);
					setcookie($meta_key, $value, time() + ( 86400 * $days ), $path, $host);
					$skip = true; 
					if (0 < $vote && 3 >=$vote) {
						$is_enabled = aw_ar_get_email_template_active_status('Critical Report Email');
						if ($is_enabled) {
							aw_ar_send_mail($product_id, $comment_id, '', '', 'Critical Report Email');			
						}
						
					}	
				} else {
					self::aw_ar_update_vote_value($comment_id, $vote, $trigger_type, $opposite_trigger, $opposite_meta_key);		        	 
				} 
			} else {

					self::aw_ar_update_vote_value($comment_id, $vote, $trigger_type, $opposite_trigger, $opposite_meta_key);
					
						
			}
			//self::aw_pq_update_vote_value($comment_id, $vote, $trigger_type, $opposite_trigger);	

			
			if ( false === $skip ) {
				array_push($cookie, $comment_id);
				$value = json_encode($cookie);
				setcookie($meta_key, $value, time() + ( 86400 * $days ), $path, $host); 
			}

			if ( isset($_COOKIE[$opposite_meta_key]) ) {
				$opposite_cookie = json_decode(stripslashes(sanitize_text_field($_COOKIE[$opposite_meta_key])), true);

				if (!empty($opposite_cookie) && in_array($comment_id, $opposite_cookie)) {
					$key = array_search($comment_id, $opposite_cookie);	
					unset($opposite_cookie[$key]);
					$value = json_encode($opposite_cookie);
					setcookie($opposite_meta_key, $value, time() + ( 86400 * $days ), $path, $host); 
				}	
			}
			//86400 for 1 day
			//$data = get_comment_meta($comment_id, 'rd_vote_users' , true);
			$users['rd_helpful']= get_comment_meta($comment_id, 'aw_ar_helpful' , true); 
			$users['rd_not_helpful'] = get_comment_meta($comment_id, 'aw_ar_not_helpful' , true);
			$users['changed_image']	 = plugins_url('/admin/images/Thumb-icon-' . $trigger_type . '.png', __DIR__);

			if ($users['rd_helpful']>0) {
				$users['rd_helpful_image'] = plugins_url('/admin/images/Thumb-icon-aw_ar_helpful.png', __DIR__);
			} else {
				$users['rd_helpful_image'] = plugins_url('/admin/images/Thumb-icon-default-aw_ar_helpful.png', __DIR__);
			}

			if ($users['rd_not_helpful']>0) {
				$users['rd_not_helpful_image'] = plugins_url('/admin/images/Thumb-icon-aw_ar_not_helpful.png', __DIR__);
			} else {
				$users['rd_not_helpful_image'] = plugins_url('/admin/images/Thumb-icon-default-aw_ar_not_helpful.png', __DIR__);
			}
		}
		echo json_encode($users);
		die;
	}
	public static function aw_ar_update_vote_value( $comment_id, $vote, $trigger_type, $opposite_trigger, $opp_meta_key) {
		if (isset($_COOKIE[$opp_meta_key])) {
			
			$cookie = json_decode(stripslashes(sanitize_text_field($_COOKIE[$opp_meta_key])), true);

			if (in_array($comment_id, $cookie)) {
				update_comment_meta($comment_id, $trigger_type, $vote);
				$vote = get_comment_meta($comment_id, $opposite_trigger, true);
				if (0 < $vote) {
					$vote--;	
				}
				update_comment_meta($comment_id, $opposite_trigger, $vote);
			} else {
				update_comment_meta($comment_id, $trigger_type, $vote);
			}

		} else {
			update_comment_meta($comment_id, $trigger_type, $vote);		
		}
	}

	public static function aw_ar_abuse_on_review_ajax() {
		$path 		= '/';
		$host 		= parse_url(get_option('siteurl'), PHP_URL_HOST);
		$days 		= 1;//get_option('rd_setting_cookie_days');
			
		check_ajax_referer( 'rdadvancedrewiew_nonce', 'aw_ar_nonce_ajax' );
		 
		if (isset($_POST['comment_id'])) {
			$comment_id = sanitize_text_field($_POST['comment_id']);
			$meta_key	= 'aw_ar_abusement-' . $comment_id;
			$abused = get_comment_meta($comment_id, 'aw_ar_abusement', true);
			if ($abused) {
				$abused++;
			} else {
				$abused = 1;
			}
			update_comment_meta($comment_id, 'aw_ar_abusement', $abused);
			setcookie($meta_key, 1, time() + ( 86400 * $days ), $path, $host);

			$comment 	= get_comment($comment_id);
			$product_id = $comment->comment_post_ID;
		}
		if (get_comment_meta($comment_id, 'aw_ar_abusement', true)) {
			echo 'Thank you for your report. We will check it as soon as possible.';
			$is_enabled = aw_ar_get_email_template_active_status('Abuse Report Email');
			if ($is_enabled) {
				aw_ar_send_mail( $product_id, $comment_id, '', '', 'Abuse Report Email');	
			}
			
		} else {
			echo 'Close Something went wrong.';
		}
		 
		wp_die();
	}

	public static function aw_ar_save_comment_on_review_ajax() {
		global $wpdb;
		$wp_version = get_bloginfo( 'version' );
		$whitelist 	= '';
		$phrases 	= '';
		$comment  	= '';
		$nickname  	= '';
		if (version_compare($wp_version, '5.5.0', '>=')) {
			$whitelist 	= get_option( 'comment_previously_approved' );
			$phrases 	= get_option( 'disallowed_keys');
		} else {
			$whitelist 	= get_option( 'comment_whitelist' );
			$phrases 	= get_option( 'blacklist_keys');
		}

		check_ajax_referer( 'rdadvancedrewiew_nonce', 'aw_ar_nonce_ajax' );

		if (isset($_POST['comment_id']) && !empty($_POST['comment_id'])) {
			$comment_id = sanitize_text_field($_POST['comment_id']);
		}
		if (isset($_POST['product_id'])) {
			$product_id = sanitize_text_field($_POST['product_id']);
		}
		if (isset($_POST['nickname']) && !empty($_POST['nickname'])) {
			$nickname 	= strip_tags(sanitize_text_field($_POST['nickname']));
			$author 	= $nickname;
		}
		if (isset($_POST['comment']) && !empty($_POST['comment'])) {
			$comment 	= strip_tags(sanitize_text_field($_POST['comment']));
		} 

		$user_id 	= 0;///get_current_user_id();
		$email 		= '';
		if (is_user_logged_in()) {
			$user_id 	= get_current_user_id();
			$email 		= get_the_author_meta( 'email', $user_id );	
		}
		$time   	= current_time('mysql');
		$gmdate 	= get_gmt_from_date($time);
		$post_type 	= 'review';

		/*if(in_array('administrator',  wp_get_current_user()->roles)){
			$comment_approved = 1;
		} else {
			$comment_approved = 0;
		}*/
		$comment_approved 	= 0;
		$akis_first_check 	= false;
		$akis_second_check 	= false;
		$moderation 		= get_option( 'comment_moderation');
		//echo get_option( 'comment_whitelist' );die;
		if ('' != $whitelist ) {
			$comment_whitelist 	= $whitelist;	
		} else {
			$comment_whitelist 	= 0;
		}
		
		 
		if (1 != $moderation && 1 != $comment_whitelist) {
			 $comment_approved = 1;
		} 

		/*if (1 == $comment_whitelist) {
			$whitelist_approve = aw_pq_whitelist_question_reply( $email);
			if ($whitelist_approve>0) {
				$comment_approved = 1;
			}
		}*/

		$max_links = get_option( 'comment_max_links' );
		if ( $max_links ) {
			$reg_exUrl = "/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\"]))/";
			$num_links = preg_match_all( $reg_exUrl, $comment, $out );
			if ( $num_links >= $max_links ) {
				$comment_approved = 0;
			}
		}

		$data = array(
					'comment_post_ID' 		=> $product_id,
					'comment_author'  		=> $author,
					'comment_author_email'	=> $email,
					'comment_content' 		=> $comment,
					'comment_date' 			=> $time,
					'comment_date_gmt' 		=> $gmdate,
					'comment_approved' 		=> $comment_approved,
					'comment_agent'			=> '',
					'comment_type' 			=> $post_type,
					'user_id'				=> $user_id,
					'comment_parent'		=> $comment_id
			   );

		if (function_exists( 'akismet_http_post' ) ) {
			$usr_agt = '';
			$htp_ref = '';

			if (isset($_SERVER['HTTP_USER_AGENT'])) {
				$usr_agt = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
			}

			if (isset($_SERVER['HTTP_REFERER'])) {
				$htp_ref = sanitize_text_field($_SERVER['HTTP_REFERER']);
			}

			$akismet_data = array(
							'blog' 					=> site_url(),
							'user_ip' 				=> aw_ar_get_the_user_ip(),
							'user_agent' 			=> $usr_agt,
							'referrer' 				=> $htp_ref,
							'permalink' 			=> get_the_permalink($postid),
							'comment_type' 			=> 'review',
							//'comment_author' 		=> $author,
							//'comment_author_email' => $email,
							'comment_author_url' 	=> '',
							'comment_content' 		=> $comment
							);
			$akismet_key = akismet_get_key();

			//$arr =akismet_check_key_status($akismet);
			$verify = akismet_verify_key( $akismet_key );
			if ('valid' === $verify) {
				$akis_first_check = self::aw_ar_akismet_comment_check( $akismet_key, $akismet_data);
				$akis_second_check = self::aw_ar_akismet_submit_spam( $akismet_key, $akismet_data);

				if ( true === $akis_first_check) {// && true === $akis_second_check) {// ||s
					$data['comment_approved'] = 'spam';
				}
			}
		}

		/* check phrase in Comment Blacklist for comment and reviews */
		//$phrases = get_option( 'blacklist_keys');
		if (!empty($phrases)) {
			$exploded 	= self::aw_ar_explodeX(array(',', '|',' '), $phrases);
			if (!empty($exploded)) {
				foreach ($exploded as $keyword) {
					$keyword = trim($keyword);
					if ('' != $keyword) {
						if (stristr($comment, $keyword )) {
							$data['comment_approved'] = 'trash';
							break;
						}	
					}
				}	
			}
		}
 
		/* check phrase in Comment Moderation for comment and reviews */
		$moderation_phrases = get_option( 'moderation_keys');
		if (!empty($moderation_phrases)) {
			$exploded 	= self::aw_ar_explodeX(array(',', '|',' '), $moderation_phrases);
			if (!empty($exploded)) {
				foreach ($exploded as $keyword) {
					$keyword = trim($keyword);
					if ('' != $keyword) {
						if (stristr($comment, $keyword )) {
							$data['comment_approved'] = 0;
							break;
						}	
					}
				}	
			}
		}
		if (wp_insert_comment($data)) {
			echo 'You submitted comments on review for moderation.';
			$is_admin_enabled = aw_ar_get_email_template_active_status('New Comment on Customer Review Email');
			if ($is_admin_enabled) {
				$the_user = get_user_by('email', $email);
				if (!empty($the_user)) {
					$is_user_enabled = get_user_meta($the_user->ID, 'is_ar_new_comment_email_enabled', true);	
					if ('yes'===$is_user_enabled) {
						aw_ar_send_mail($product_id, $comment_id, $nickname, $comment, 'New Comment on Customer Review Email');
					}
				} else {
					aw_ar_send_mail($product_id, $comment_id, $nickname, $comment, 'New Comment on Customer Review Email');
				}
			}
		} else {
			echo 0;
		}
		wp_die();
	}

	public static function aw_ar_explodeX( $delimiters, $string ) {
		return explode( chr( 1 ), str_replace( $delimiters, chr( 1 ), $string ) );
	}
	// Passes back true (it's spam) or false (it's ham)
	public static function aw_ar_akismet_comment_check( $key, $data ) {
		$request = 'blog=' . urlencode($data['blog']) .
				   '&user_ip=' . urlencode($data['user_ip']) .
				   '&user_agent=' . urlencode($data['user_agent']) .
				   '&referrer=' . urlencode($data['referrer']) .
				   '&permalink=' . urlencode($data['permalink']) .
				   '&comment_type=' . urlencode($data['comment_type']) .
				   //'&comment_author='. urlencode($data['comment_author']) .
				   //'&comment_author_email='. urlencode($data['comment_author_email']) .
				   '&comment_author_url=' . urlencode($data['comment_author_url']) .
				   '&comment_content=' . urlencode($data['comment_content']);
		$host = $key . '.rest.akismet.com';
		$http_host = $key . '.rest.akismet.com';
		$path = '/1.1/comment-check';
		$port = 443;
		$akismet_ua = 'WordPress/4.4.1 | Akismet/3.1.7';
		$content_length = strlen( $request );
		$http_request  = "POST $path HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$http_request .= "Content-Length: {$content_length}\r\n";
		$http_request .= "User-Agent: {$akismet_ua}\r\n";
		$http_request .= "\r\n";
		$http_request .= $request;
		$response = '';
		$fs = @fsockopen( 'ssl://' . $http_host, $port, $errno, $errstr, 10 );
		if ( false != $fs ) {

			fwrite( $fs, $http_request );
			while ( !feof( $fs ) ) {
				$response .= fgets( $fs, 1160 ); // One TCP-IP packet
			}
			fclose( $fs );
			$response = explode( "\r\n\r\n", $response, 2 );
		}
		if ( 'true' == $response[1] ) {
			return true;
		} else {
			return false;
		}
	}

	public static function aw_ar_akismet_submit_spam( $key, $data ) {
		$request = 'blog=' . urlencode($data['blog']) .
				   '&user_ip=' . urlencode($data['user_ip']) .
				   '&user_agent=' . urlencode($data['user_agent']) .
				   '&referrer=' . urlencode($data['referrer']) .
				   '&permalink=' . urlencode($data['permalink']) .
				   '&comment_type=' . urlencode($data['comment_type']) .
				   //'&comment_author='. urlencode($data['comment_author']) .
				   //'&comment_author_email='. urlencode($data['comment_author_email']) .
				   '&comment_author_url=' . urlencode($data['comment_author_url']) .
				   '&comment_content=' . urlencode($data['comment_content']);
		$host = $key . '.rest.akismet.com';
		$http_host = $key . '.rest.akismet.com';
		$path = '/1.1/submit-spam';
		$port = 443;
		$akismet_ua = 'WordPress/4.4.1 | Akismet/3.1.7';
		$content_length = strlen( $request );
		$http_request  = "POST $path HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$http_request .= "Content-Length: {$content_length}\r\n";
		$http_request .= "User-Agent: {$akismet_ua}\r\n";
		$http_request .= "\r\n";
		$http_request .= $request;
		$response = '';
		$fs = @fsockopen( 'ssl://' . $http_host, $port, $errno, $errstr, 10 );
		if ( false != $fs ) {

			fwrite( $fs, $http_request );

			while ( !feof( $fs ) ) {
				$response .= fgets( $fs, 1160 ); // One TCP-IP packet
			}
			fclose( $fs );

			$response = explode( "\r\n\r\n", $response, 2 );
		}

		if ( 'Thanks for making the web a better place.' == $response[1] ) {
			return true;
		} else {
			return false;
		}
	}

	public static function aw_ar_show_recomended_message( $comment ) {

		$comment_id = $comment->comment_ID;
		$recommed = get_comment_meta($comment_id, 'aw_ar_recommend', true);
		if ('yes' === $recommed) {
			echo '<div><span style="color:green"> I recommend this product </span></div>';	
		} 
		if ('no' === $recommed) {
			echo '<div><span style="color:red" > I don\'t recommend this product </span></div>';	
		}
	}

 
} // class close

