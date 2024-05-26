<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

$AdvancedReviewAllReview = new AdvancedReviewAllReview();

class AdvancedReviewAllReview {

	public function __construct() {
		add_shortcode('aw_ar_reviewslist' , array('AdvancedReviewAllReview','aw_ar_shortcode_display_review_list'));
		add_action('wp_head', array('AdvancedReviewAllReview','aw_ar_add_meta_description'));
	}
	public static function aw_ar_shortcode_display_review_list() {
		$role 				= '';
		$reviews_per_page 	= get_option('comments_per_page');
		$page 				= ( get_query_var('paged') ) ? get_query_var('paged') : 1;
		$offset 			= ( $page - 1 ) * $reviews_per_page;		
	
		$all_approved_product_review_count = get_comments(array(
												'status'   => 'approve',
												'type' => 'review',
												'count' => true
											));
		$pages = ceil($all_approved_product_review_count/$reviews_per_page);

		$args = array(
					'status' 	=> 'approve',
					'type' 		=> 'review',
					'number' 	=> $reviews_per_page,
					'offset' 	=> $offset
				);
		// Pagination args
		if ($all_approved_product_review_count>0) { ?>
			<div class="aw_ar_avg_count-panel">
				<div class="aw_ar_avg_count">
					<?php                     
						$average_rate = self::aw_ar_average_rating_allreview();
						echo wp_kses($average_rate, wp_kses_allowed_html('post'));
					?>
				</div>
				<div class='star-rating-container'>
					<?php $averga_rating = 22 * (int) $average_rate ; ?>
					<div itemprop="reviewRating" class="star-rating" title="<?php echo  wp_kses($average_rate, wp_kses_allowed_html('post')); ?>">
						<span style="width:<?php echo wp_kses($averga_rating, wp_kses_allowed_html('post')) ; ?>px"> <?php esc_html_e('out of 5', 'woocommerce'); ?></span>
					</div>
				</div>
			</div>
			
			<?php 
			$progressbar 	= self::aw_ar_rating_progressbar_all_review();
			echo wp_kses($progressbar, wp_kses_allowed_html('post'));
			$sortingsection = self::aw_ar_filter_sorting_allreview_section();
			echo wp_kses($sortingsection, wp_kses_allowed_html('post'));
		}
			$comments_query = new WP_Comment_Query();
			$comments = $comments_query->query( $args );
		?>

		<div id="unfeaturedreview">
		<?php 
		if ( $comments ) { 
			?>
			<ul>
			<?php 	
			foreach ( $comments as $comment ) : 
				?>
			<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="meta waiting-approval-info">
					<em><?php esc_html_e( 'Thanks, your review is awaiting approval', 'woocommerce' ); ?></em>
				</p>
				<?php endif; ?>
				<li id="li-review-<?php echo  wp_kses($comment->comment_ID, wp_kses_allowed_html('post')); ?>">
					<div id="review-<?php echo wp_kses($comment->comment_ID, wp_kses_allowed_html('post')); ?>" class="review_container">
						<div class="review-avatar">
							<?php echo get_avatar( $comment->comment_author_email, $size = '50' ); ?>
						</div>
						<div class="review-author">
							<div class="review-author-name" itemprop="author">
								<?php 
									$author 		= $comment->comment_author;
									$setting_author = get_option('aw_ar_admin_caption');
									$user_id 		= $comment->user_id;
								if (0==$user_id) {
									$author 		= $comment->comment_author;
								} else {
									$user_info		= get_userdata( $user_id );
									$role 			= implode(', ', $user_info->roles);
									if ( 'administrator' === $role && '' != $setting_author ) {
										$author 	= $setting_author;
									}
								}
									echo wp_kses($author, wp_kses_allowed_html('post')); 
								?>
							</div>
							<?php 
								$recommed = get_comment_meta($comment->comment_ID, 'aw_ar_recommend', true);
							if ('yes' === $recommed) { 
								?>
									<div><span style="color:green"> I recommend this product </span></div>
								<?php 	
							} 
							if ('no' === $recommed) { 
								?>
									<div><span style="color:red" > I don't recommend this product </span></div>
								<?php 		
							}
							?>

							<div class='star-rating-container'>
								<div itemprop="reviewRating" class="star-rating" title="<?php echo esc_attr( get_comment_meta( $comment->comment_ID, 'rating', true ) ); ?>">
									<?php 
									$rate_value = get_comment_meta( $comment->comment_ID, 'rating', true );
									$existing_rating = 17 * (float) $rate_value  ;
									?>
									<span style="width:<?php echo esc_html_e($existing_rating) ; ?>px"><span itemprop="ratingValue"><?php echo esc_html_e($rate_value); ?></span> <?php esc_html_e('out of 5', 'woocommerce'); ?></span>
										<?php
											$timestamp = strtotime( $comment->comment_date ); //Changing comment time to timestamp
											$date = gmdate('F d, Y', $timestamp);
										?>
								</div>
								<em class="review-date">
									<time itemprop="datePublished" datetime="<?php esc_html($comment->comment_date); ?>"><?php echo esc_html($date); ?>
									</time>
								</em>
							</div>
						</div>
						<div class="clear"></div>
						<div class="review-text">
							<div itemprop="description" class="description">
								<?php echo wp_kses($comment->comment_content, wp_kses_allowed_html('post')); ?>
							</div>
							<div class="clear"></div>
						</div>

						<?php 
							$review_images	= maybe_unserialize(get_comment_meta($comment->comment_ID, 'aw-ar-reviewimage', true));
						if (!empty($review_images)) { 
							?>
								<ul class="aw_ar_gallery-front gallery-<?php echo wp_kses($comment->comment_ID, wp_kses_allowed_html('post')); ?>"> 
								<?php 
								foreach ($review_images as $review_image_id) {
									$img_src 		= wp_get_attachment_image_url($review_image_id, 'medium');
									$img_link 		= wp_get_attachment_image_url( $review_image_id, 'full');
									$path 			= wp_get_upload_dir();
									$imagepath 		= explode('uploads', $img_src) ;
									$fullpath  		= $path['basedir'] . $imagepath[1];
									if (file_exists($fullpath)) { 
										?>
										<li>
											<a href="<?php echo esc_url($img_link); ?>">
												<img src="<?php echo esc_url($img_src); ?>" alt="Image" onclick="aw_ar_show_lightbox(<?php echo wp_kses($comment->comment_ID , wp_kses_allowed_html('post')); ?>)" ></a></li>
										<?php 
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
									echo wp_kses(aw_ar_get_voting_abuse_section($comment->comment_ID, $comment->comment_post_ID, $comment->user_id, $comment->comment_approved, $comment->comment_type), wp_kses_allowed_html('post')); 
								?>
							</p>
						</div>
						
						<div>
						<div style="clear:both;"></div>
						<?php 
							/* Comment are displayed for review */
								aw_ar_commentlist_and_commentform_section( $comment->comment_ID, $comment->comment_post_ID, $comment->user_id, $comment->comment_approved, $comment->comment_type);
						?>
								
						</div>
					<div class="clear"></div>			
				</div>
			</li>
 
			<?php 
			endforeach; 
			?>
			</ul>

			<div style="clear:both;"></div>
			<nav class="woocommerce-pagination">
				<?php 	
				if ( $pages > 1 && get_option( 'page_comments' ) ) :
						 
					$paginate = array(
								'base' 			=> add_query_arg( 'paged', '%#%' ),
								'format' 		=> '',
								'total' 		=> $pages,
								'current' 		=> $page,
								'show_all'     	=> false,
								'end_size'     	=> 1,
								'mid_size'     	=> 1,
								'prev_next'    	=> true,
								'prev_text'    	=> __('« Previous'),
								'next_text'    	=> __('Next »'),
								'type'         	=> 'list',
									 
								);
					echo wp_kses(paginate_links($paginate), wp_kses_allowed_html('post'));
					endif;	
				?>
			</nav>
			<?php 
		} else { 
			?>
			<p class="woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'woocommerce' ); ?></p>
			<?php 
		}
		?>
		</div>
		<?php 
	}

	public static function aw_ar_average_rating_allreview() {
		global $wpdb;
		$ratings = $wpdb->get_results("

	        SELECT $wpdb->commentmeta.meta_value
	        FROM $wpdb->commentmeta
	        INNER JOIN $wpdb->comments on $wpdb->comments.comment_id=$wpdb->commentmeta.comment_id
	        WHERE $wpdb->commentmeta.meta_key='rating' 
	        AND $wpdb->comments.comment_approved =1

	        ");
		$counter = 0;
		$average_rating = 0;    
		if ($ratings) {
			foreach ($ratings as $rating) {
				$average_rating = $average_rating + $rating->meta_value;
				$counter++;
			} 
			//round the average to the nearast 1/2 point
			return ( round(( $average_rating/$counter )*2, 0)/2 );  
		} else {
			//no ratings
			return 'no rating';
		}
	}

	public static function aw_ar_total_and_count_star_point( $star) {
		
		global $wpdb;
		$total_ratings = $wpdb->get_row($wpdb->prepare(" SELECT SUM(CM.meta_value) AS total_rating , COUNT(CM.meta_value) AS star_total FROM {$wpdb->prefix}commentmeta AS CM INNER JOIN {$wpdb->prefix}comments AS CO on CO.comment_id=CM.comment_id WHERE CM.meta_key = %s AND CM.meta_value = %d AND CO.comment_approved = %d", 'rating', "{$star}", 1));
		return $total_ratings;
	}

	public static function aw_ar_rating_progressbar_all_review() {
		global $product;
		$progressbar 	= '';
		$total_rating 	= 0;

		$all_approved_review_count = get_comments(array(
											'status'   => 'approve',
											'type' => 'review',
											'count' => true
										));
		$total_rating 			= (float) $all_approved_review_count;

		$progressbar .= '<div class="aw-ar-row">';
		for ($i=5; $i>=1; $i--) {
			$star_total 		= 0;
			$star_average 		= 0;
			$total_and_count 	= self::aw_ar_total_and_count_star_point($i);

			$star_total 		= (float) $total_and_count->star_total;
			if ($star_total>0) {
				$star_average 	= round(( (float) $star_total/$total_rating )*100);	
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
			$progressbar 	.=	'</div>';

		echo wp_kses($progressbar, wp_kses_allowed_html('post')) ;
	}

	public static function aw_ar_filter_sorting_allreview_section() {
		?>
		<div class="clear"></div>
		<div class="aw_ar_filters_wrap mrg-top"> 
			<div class="aw_ar_form-field-wrap">
				<input type="checkbox" class="buttontohref" name="aw_ar_with_picture" value="aw-ar-reviewimage" onchange="aw_ar_apply_filter_ajax(this,false)"> 
				With Pictures
			</div>
			<div class="aw_ar_form-field-wrap">
				<input type="checkbox" class="buttontohref" name="aw_ar_verified_buyer" value="verified" onchange="aw_ar_apply_filter_ajax(this,false)"> 
				Verified Buyers
			</div>
			<div class="aw_ar_form-field-wrap">
				<select onchange="aw_ar_apply_filter_ajax(this,false)" class="aw_ar_rating_type">
					<option value="all"> All Customer Reviews </option>
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
		<div class="clear"></div>
		<?php 
	}

	public static function aw_ar_add_meta_description() {
		global $post;
		if (!empty($post)) {
			$post_slug 		= $post->post_name;
			$existing_id 	= get_option('aw_ar_allreviewpage_id');
			$existing_post 	= get_post($existing_id);

			if ($existing_post->post_name === $post_slug) {
				$meta_description = get_option('aw_ar_meta_description');
				echo wp_kses('<meta name="description" content="' . $meta_description . '" />' . "\n" , wp_kses_allowed_html('post'));
			}	
		}
	}

 
}
