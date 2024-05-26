<?php

if ( ! class_exists( 'Aw_Pq_Walker_Comment' ) ) {

	class Aw_Pq_Walker_Comment extends Walker_Comment {

		protected function html5_comment( $comment, $depth, $args ) {
			$unapproved_id = 0;
			if ( isset($_GET['unapproved']) ) {
				$unapproved_id = sanitize_text_field($_GET['unapproved']);
			}
			$tag 	= ( 'div' === $args['style'] ) ? 'div' : 'li';
			$theme 	= get_option( 'current_theme' );

			$name 			= '';
			$email 			= '';
			$hide_email 	= '';
			$already_guest 	= '';
			$user_id 		= '';
			$readonly 		= '';
			$disabled_ans_btn 	= false;
			$enabled_edit_btn 	= false;
			$ip 			= aw_pq_get_the_user_ip();
			if ('::1' === $ip) {
				$ip = ip2long('127.0.0.1'); 
			} else {
				$ip = ip2long($ip); 
			}
			$user_id 		= $ip; 
			if (is_user_logged_in()) {
				$userdata	= wp_get_current_user();
				$name 		= $userdata->display_name;	
				$email 		= $userdata->user_email;
				$user_id 	= $userdata->ID;	
				$hide_email = 'hide_email_box';
				$already_guest = 'checked';
				$readonly 	= 'readonly';
				if ( get_current_user_id() == $comment->user_id ) {
					$disabled_ans_btn = true;
					$comment_date = date_create($comment->comment_date);
					$current_date = date_create(gmdate('Y-m-d H:i:s'));
					$diff = date_diff($current_date, $comment_date);
					if (get_option('pq_editin_minutes') > 0 ) {
						$x_minutes = get_option('pq_editin_minutes');
						if ($x_minutes>=$diff->i) {
							$enabled_edit_btn = true;
						}
					}
					if (''==get_option('pq_editin_minutes') || 0==get_option('pq_editin_minutes') ) {
						$enabled_edit_btn = true;
					}
				}
			} else {
				if (isset($_COOKIE['guest_author']) && isset($_COOKIE['guest_email'])) {
					$name 	= sanitize_text_field($_COOKIE['guest_author']);
					$email 	= sanitize_text_field($_COOKIE['guest_email']);
					$user_id= $ip;
					$already_guest = 'checked';

					if ( $email == $comment->comment_author_email ) {
						$disabled_ans_btn = true;
					}
				} else {
					$already_guest 	= '';
				}
			} 

			$comment_date = gmdate('F, Y ', strtotime($comment->comment_date));
			$comment_time = gmdate('H:i', strtotime($comment->comment_date));
			if (( 0 == $comment->comment_approved && $user_id == $comment->user_id ) || 1 == $comment->comment_approved || $unapproved_id == $comment->comment_ID) {
				?>

			<<?php echo wp_kses($tag, wp_kses_allowed_html('post')); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static output ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( $this->has_children ? 'parent' : '', $comment ); ?>>
				<div class="comment-body">
					<div class="comment-meta commentmetadata">
						<div class="comment-author vcard">
							<?php
							$user = get_userdata( $comment->user_id );
							if (!empty($user)) {
								$user_roles	= $user->roles;	
							}
							$get_admin_reply_color 	= get_option('pq_adminreply_color');

							if ( get_option( 'show_avatars' ) ) {
								$default_avatar		= '';
								$comment_author_url = get_comment_author_url( $comment );
								$default_avatar 	= get_option( 'avatar_default');
								$avatar             = get_avatar($comment->comment_author_email , $args['avatar_size'], $default_avatar);
								if ( 0 !== $args['avatar_size'] ) {
									if ( empty( $comment_author_url ) ) {
										echo wp_kses_post( $avatar );
									} else {
										echo wp_kses_post( $avatar );
									}
								}
							}
							$comment_author = get_comment_author( $comment );

							if (!empty($user_roles) && '' != $get_admin_reply_color && 'administrator' == $user_roles[0] && 0 != $comment->comment_parent) {
								$dv_bg_color = 'style = background-color:#' . $get_admin_reply_color . '';
								echo wp_kses('<span class="fn" style = "color:#' . $get_admin_reply_color . ';">' . $comment_author . '</span>', wp_kses_allowed_html('post'));
							} else {
								$dv_bg_color = '';
								echo wp_kses('<span class="fn">' . $comment_author . '</span>', wp_kses_allowed_html('post'));
							}
							?>

							<a href="<?php echo esc_url( get_comment_link( $comment, $args ) ); ?>">
								<?php
									// Translators: 1 = comment date, 2 = comment time 
									$comment_timestamp = sprintf( __( '%1$s at %2$s', 'twentynineteen' ), get_comment_date( '', $comment ), get_comment_time() );
								?>
								<time datetime="<?php comment_time( 'c' ); ?>" title="<?php echo wp_kses($comment_timestamp, wp_kses_allowed_html('post')); ?>">
									<?php echo wp_kses($comment_timestamp, wp_kses_allowed_html('post')); ?>
								</time>
							</a>
						</div><!-- .comment-author -->
					</div><!-- .comment-meta -->

					<div id="div-comment-<?php comment_ID(); ?>" class="comment-content">
						<div class="comment-text" id="comment-text-<?php comment_ID(); ?>" <?php echo wp_kses($dv_bg_color, wp_kses_allowed_html('post')); ?>>
							<?php
							if ( is_object( $comment ) && $comment->user_id > 0 ) {
								//$user = get_userdata( $comment->user_id );
								$post = get_post( $comment->comment_post_ID );
							}

							comment_text();

							echo wp_kses(self::aw_pq_get_voting_section($comment->comment_ID, $comment->user_id), wp_kses_allowed_html('post'));

							if ( ( $user_id == $comment->user_id || 0 != $unapproved_id ) && 0 == $comment->comment_approved ) {
								$disabled_ans_btn = true;
								?>
								<p class="meta">
									<em class="woocommerce-review__awaiting-approval">
										<?php 
										if (0 != $comment->comment_parent) {
											echo wp_kses(aw_pq_translate_text('Your answer is awaiting moderation.'), wp_kses_allowed_html('post'));
										} else {
											echo wp_kses(aw_pq_translate_text('Your question is waiting for the answer.'), wp_kses_allowed_html('post'));
										}
										?>
									</em>
								</p>
								<?php
							}
							?>
								
						</div>

						<div class="show-hide-answer">
						<?php 
						if (false == $disabled_ans_btn) { 
							?>
								<input name="answersubmit" type="button" data-belowelement="div-comment-<?php comment_ID(); ?>" data-commentid="<?php comment_ID(); ?>" data-postid="<?php comment_ID(); ?>" data-value="<?php comment_ID(); ?>" id="reply-<?php comment_ID(); ?>" data-button="rd_show_reply" class="submit rdanswerbutton" value="<?php echo wp_kses(aw_pq_translate_text('Add Answer'), wp_kses_allowed_html('post')); ?>">
							<?php
						} 
						if (true == $enabled_edit_btn && 0 != $comment->comment_parent) {
							?>
								<div class="woocommerce-error error_mesg" id="error_msg-<?php comment_ID(); ?>"></div>
								<button name="answeredit" data-belowelement="div-comment-<?php comment_ID(); ?>" data-commentid="<?php comment_ID(); ?>" data-postid="<?php comment_ID(); ?>" data-value="<?php comment_ID(); ?>" id="edit-<?php comment_ID(); ?>" data-button="rd_show_edit_reply" class="submit pq_ans_editbutton" onclick="return aw_pq_edit_comment(<?php comment_ID(); ?>)"><?php echo wp_kses(aw_pq_translate_text('Edit Answer'), wp_kses_allowed_html('post')); ?></button>
							<?php
						} elseif (true == $enabled_edit_btn && 0 == $comment->comment_parent) {
							?>
								<div class="woocommerce-error error_mesg" id="error_msg-<?php comment_ID(); ?>"></div>
								<button name="answeredit" data-belowelement="div-comment-<?php comment_ID(); ?>" data-commentid="<?php comment_ID(); ?>" data-postid="<?php comment_ID(); ?>" data-value="<?php comment_ID(); ?>" id="edit-<?php comment_ID(); ?>" data-button="rd_show_reply" class="submit pq_ques_editbutton" onclick="return aw_pq_edit_comment(<?php comment_ID(); ?>)"><?php echo wp_kses(aw_pq_translate_text('Edit Question'), wp_kses_allowed_html('post')); ?></button>
							<?php
						}  
						?>
						</div>	
						<?php 
						if ( ( '' == get_option('pq_allowtouser') || 'anyone' == get_option('pq_allowtouser') )  || ( 'loggedinuser' == get_option('pq_allowtouser') && is_user_logged_in() ) ) { 
							?>
							<div id="respond-<?php comment_ID(); ?>" class="comment-respond hide_answerform">
								<form action="<?php echo wp_kses(admin_url( 'admin-post.php' ), wp_kses_allowed_html('post')); ?>" method="post" id="commentform" class="comment-form " novalidate="">
									<input type="hidden" name="action" value="save_question_answer"/>

									<p class="comment-form-author">
										<label for="author">
											<?php echo wp_kses(aw_pq_translate_text('Name'), wp_kses_allowed_html('post')); ?>
											<span class="required">*</span>
										</label> 
										<input id="author" name="author" type="text" class="author-<?php comment_ID(); ?>" value="<?php echo wp_kses($name, wp_kses_allowed_html('post')); ?>" required="required" <?php echo wp_kses($readonly, wp_kses_allowed_html('post')); ?>>
									</p>

									<p class="comment-form-email <?php echo wp_kses($hide_email, wp_kses_allowed_html('post')); ?>">
										<label for="email">
											<?php echo wp_kses(aw_pq_translate_text('Email'), wp_kses_allowed_html('post')); ?> 
											<span class="required">*</span>
										</label>
										<input id="email" name="email" class="email-<?php comment_ID(); ?>" type="email" value="<?php echo wp_kses($email, wp_kses_allowed_html('post')); ?>"  aria-describedby="email-notes" required="required" <?php echo wp_kses($readonly, wp_kses_allowed_html('post')); ?>>
									</p>
										
									<p class="comment-form-comment">
										<textarea id="comment" name="comment" cols="45" rows="8" required="required" class="comment--<?php comment_ID(); ?>"></textarea>
									</p>
									<?php if (!is_user_logged_in()) { ?>
										<p class="comment-form-cookies-consent">
											<input <?php echo wp_kses($already_guest, wp_kses_allowed_html('post')); ?> id="wp-comment-cookies-consent" name="wp-question-cookies-consent" type="checkbox" value="yes">
											<label for="wp-comment-cookies-consent"><?php echo wp_kses(aw_pq_translate_text('Save my name, email in this browser for the next time I question.'), wp_kses_allowed_html('post')); ?> </label>
										</p>
									<?php } ?>
									<?php wp_nonce_field('rd_batc_question_save_nonce_action', 'rd_batc_question_save_nonce_name'); ?>

									<p class="form-submit">
										<input name="questionsubmit" type="submit" id="submit" class="submit btn_QA_submit" data-value="<?php comment_ID(); ?>" value="<?php echo wp_kses(aw_pq_translate_text('Send Answer'), wp_kses_allowed_html('post')); ?>">
										<input type="hidden" name="comment_post_ID" value="<?php echo wp_kses($comment->comment_post_ID, wp_kses_allowed_html('post')); ?>" id="comment_post_ID">
										<input type="hidden" name="comment_parent" id="comment_parent" value="<?php comment_ID(); ?>">
									</p>
								</form>	
							</div>
							<!-- Edit comment form -->
							<div id="editrespond-<?php comment_ID(); ?>" class="comment-respond comment-editrespond hide_editanswerform">
								<form action="<?php echo wp_kses(admin_url( 'admin-post.php' ), wp_kses_allowed_html('post')); ?>" method="post" id="commentform" class="comment-form" novalidate="">
									<input type="hidden" name="action" value="save_question_answer"/>
									<input type="hidden" name="edit_id" value="<?php comment_ID(); ?>"/>

									<p class="comment-form-author">
										<label for="author">
											<?php echo wp_kses(aw_pq_translate_text('Name'), wp_kses_allowed_html('post')); ?> 
											<span class="required">*</span>
										</label> 
										<input id="author" name="author" type="text" class="
										<?php 
										if (0 == $comment->comment_parent ) {
											echo 'q_author-';
										} else {
											echo 'a_author-';
										}
										echo wp_kses(comment_ID(), wp_kses_allowed_html('post'));
										?>
										" value="<?php echo wp_kses($name, wp_kses_allowed_html('post')); ?>" required="required" <?php echo wp_kses($readonly, wp_kses_allowed_html('post')); ?>>
									</p>

									<p class="comment-form-email <?php echo wp_kses($hide_email, wp_kses_allowed_html('post')); ?>">
										<label for="email">
											<?php echo wp_kses(aw_pq_translate_text('Email'), wp_kses_allowed_html('post')); ?>
											<span class="required">*</span>
										</label>
										<input id="email" name="email" class="
										<?php 
										if (0 == $comment->comment_parent ) {
											echo 'q_email-';
										} else {
											echo 'a_email-';
										} echo wp_kses(comment_ID(), wp_kses_allowed_html('post'));
										?>
										" type="email" value="<?php echo wp_kses($email, wp_kses_allowed_html('post')); ?>"  aria-describedby="email-notes" required="required" <?php echo wp_kses($readonly, wp_kses_allowed_html('post')); ?>>
									</p>

									<p class="comment-form-comment">
										<textarea id="comment" name="comment" cols="45" rows="8" required="required" class="
										<?php 
										if (0 == $comment->comment_parent ) {
											echo 'q_comment-';
										} else {
											echo 'a_comment-';
										} echo wp_kses(comment_ID(), wp_kses_allowed_html('post'));
										?>
										"><?php echo wp_kses(strip_tags(get_comment_text(get_comment_ID())), wp_kses_allowed_html('post')); ?></textarea>
									</p>
									<?php if (!is_user_logged_in()) { ?>
										<p class="comment-form-cookies-consent">
											<input <?php echo wp_kses($already_guest, wp_kses_allowed_html('post')); ?> id="wp-comment-cookies-consent" name="wp-question-cookies-consent" type="checkbox" value="yes">
											<label for="wp-comment-cookies-consent"><?php echo wp_kses(aw_pq_translate_text('Save my name, email in this browser for the next time I question.'), wp_kses_allowed_html('post')); ?></label>
										</p>
									<?php } ?>
									<?php wp_nonce_field('rd_batc_question_save_nonce_action', 'rd_batc_question_save_nonce_name'); ?>

									<p class="form-submit">
										<?php 
										if (0 == $comment->comment_parent ) {
											$btn_Q_update_submit = ' btn_Q_update_submit';
										} else {
											$btn_Q_update_submit = ' btn_A_update_submit';
										} 

										if (0 == $comment->comment_parent ) {
											$text_Q_update_submit= wp_kses(aw_pq_translate_text('Update Question'), wp_kses_allowed_html('post'));
										} else {
											$text_Q_update_submit= wp_kses(aw_pq_translate_text('Update Answer'), wp_kses_allowed_html('post'));
										} 
										?>
										<input name="questionsubmit" type="submit" id="submit" class="submit <?php echo wp_kses($btn_Q_update_submit, wp_kses_allowed_html('post')); ?>" data-value="<?php comment_ID(); ?>" value="<?php echo wp_kses($text_Q_update_submit, wp_kses_allowed_html('post')); ?>">
										<input type="hidden" name="comment_post_ID" value="<?php echo wp_kses($comment->comment_post_ID, wp_kses_allowed_html('post')); ?>" id="comment_post_ID">
										<input type="hidden" name="comment_parent" id="comment_parent" value="<?php comment_ID(); ?>">
									</p>
								</form>	
							</div>
						<?php 
						} else { 
							?>
							<div id="respond-<?php comment_ID(); ?>" class="comment-respond hide_answerform">
								<p><?php echo wp_kses(aw_pq_translate_text('Please'), wp_kses_allowed_html('post')); ?>, <a href="<?php echo wp_kses( get_permalink(wc_get_page_id( 'myaccount' )) . '/#tab-QA_tab' , wp_kses_allowed_html('post')); ?>"><?php echo wp_kses(aw_pq_translate_text('log in'), wp_kses_allowed_html('post')); ?></a>&nbsp;<?php echo wp_kses(aw_pq_translate_text('to post an answer'), wp_kses_allowed_html('post')); ?>
								</p>
							</div>
							<div id="editrespond-<?php comment_ID(); ?>" class="comment-respond hide_answerform">
								<p><?php echo wp_kses(aw_pq_translate_text('Please'), wp_kses_allowed_html('post')); ?>, <a href="<?php echo  wp_kses(get_permalink(wc_get_page_id( 'myaccount' )) . '/#tab-QA_tab', wp_kses_allowed_html('post')); ?>"><?php echo wp_kses(aw_pq_translate_text('log in'), wp_kses_allowed_html('post')); ?></a>&nbsp;<?php echo wp_kses(aw_pq_translate_text('to post an answer'), wp_kses_allowed_html('post')); ?>
								</p>
							</div>
						<?php
						}
						?>
					</div><!-- .comment-content -->
				</div><!-- .comment-body -->

				<?php
			}
		}

		public static function aw_pq_get_voting_section( $comment_id, $user_id) {
			global $wpdb;
			global $product;

			$like_dislike_count	= array();
			$image_url 			= array();
			$is_enable 			= get_option('rd_setting_helpful_enable');
			$html 				= '';
			if ('yes' === $is_enable) {
				$like_dislike_count['rd-helpful'] 		= 0;
				$like_dislike_count['rd-not-helpful'] 	= 0;
			
				$product_id = $product->get_id();

				$like_dislike_count['rd-helpful'] = get_comment_meta($comment_id, 'rd-helpful' , true) ? get_comment_meta($comment_id, 'rd-helpful' , true) : 0;
				if ($like_dislike_count['rd-helpful']>0) {
					$image_url['rd-helpful']		= plugins_url('/admin/images/Thumb-icon-rd-helpful.png', __DIR__);
				} else {
					$image_url['rd-helpful']		= plugins_url('/admin/images/Thumb-icon-default-rd-helpful.png', __DIR__);
				}

				$like_dislike_count['rd-not-helpful'] = get_comment_meta($comment_id, 'rd-not-helpful' , true) ? get_comment_meta($comment_id, 'rd-not-helpful' , true) : 0;
				if ($like_dislike_count['rd-not-helpful']>0) {
					$image_url['rd-not-helpful']	= plugins_url('/admin/images/Thumb-icon-rd-not-helpful.png', __DIR__);
				} else {
					$image_url['rd-not-helpful']	= plugins_url('/admin/images/Thumb-icon-default-rd-not-helpful.png', __DIR__);
				}	

				if (0 != $like_dislike_count['rd-not-helpful'] ) {
					$like_dislike_count['rd-not-helpful'] = '-' . $like_dislike_count['rd-not-helpful'];
				}		
			
				$comment_approved = $wpdb->get_row( $wpdb->prepare( "SELECT `comment_approved`, `comment_type`  FROM {$wpdb->prefix}comments WHERE `comment_ID` =%d ", "{$comment_id}" ) );

				if ('1' == $comment_approved->comment_approved && 'q_and_a' == $comment_approved->comment_type) {
					$html= '<div class="thumbs-rate">
							<div class="thumbs-images">
								<img src="' . $image_url['rd-helpful'] . '" id="rd-helpful-' . $comment_id . '" class="like_dislike_img" data-trigger-type="rd-helpful"  data-comment-id="' . $comment_id . '" data-user-id="' . $user_id . '" />
								<span id="helpfulcount-' . $comment_id . '">' . $like_dislike_count['rd-helpful'] . '</span>
							</div>
							<div class="thumbs-images">
								<img src="' . $image_url['rd-not-helpful'] . '" id="rd-not-helpful-' . $comment_id . '" class="like_dislike_img" data-trigger-type="rd-not-helpful" data-comment-id="' . $comment_id . '" data-product-id="' . $product_id . '"  data-user-id="' . $user_id . '"/>
								<span id="nothelpfulcount-' . $comment_id . '"> ' . $like_dislike_count['rd-not-helpful'] . '</span>
							</div>
						</div>';
				}		
			}			
			return $html;
		}	

		public static function aw_pq_ask_question_form( $class = 'hide_questionsform') {
			global $product;
			$product_id 	= $product->get_id();
			$title 			= $product->get_name();		
			$related_posts 	= array( $product_id );
			$html 			= '';
			
			// The comment Query
			$total_Q		= aw_pq_get_questions_count($product_id); 
			$name 			= '';
			$email 			= '';
			$hide_email 	= '';
			$already_guest 	= '';
			$user_id 		= '';
			$total_Q		= 0;
			$readonly 		= '';
			$ip 			= aw_pq_get_the_user_ip();
			if ('::1' === $ip) {
				$ip = ip2long('127.0.0.1'); 
			} else {
				$ip = ip2long($ip); 
			}
			$user_id 		= $ip;
			
			if ( $total_Q > 0) {
				$title	= $total_Q . ' ' . aw_pq_translate_text('Questions about') . ' ' . $title;
			} else {
				$title  = aw_pq_translate_text('Be the first to ask a question about') . ' ' . $title;
			}

			if (is_user_logged_in()) {

				$userdata	= wp_get_current_user();
				$name 		= $userdata->display_name;	
				$email 		= $userdata->user_email;
				$user_id 	= $userdata->ID;	
				$hide_email = 'hide_email_box';
				$already_guest = 'checked';
				$readonly 	= 'readonly';
				 
			} else {
				if (isset($_COOKIE['guest_author']) && isset($_COOKIE['guest_email'])) {
					$name 	= sanitize_text_field($_COOKIE['guest_author']);
					$email 	= sanitize_text_field($_COOKIE['guest_email']);
					$user_id= $ip;//strtotime(gmdate('Y/m/d H:i:s'));
					$already_guest = 'checked';
				}
			}
			if ( ( '' == get_option('pq_allowtouser') || 'anyone' == get_option('pq_allowtouser') )  || ( 'loggedinuser' == get_option('pq_allowtouser') && is_user_logged_in() ) ) {

				$html = '<div id="respond" class="comment-respond ' . $class . '" >
							<form action="' . admin_url( 'admin-post.php' ) . '" method="post" id="commentform1" class="comment-form" novalidate="">
								<input type="hidden" name="action" value="save_question_answer"/>
								<input type="hidden" name="action" value="save_question_answer"/>
								<p class="comment-form-author">
									<label for="author">' . aw_pq_translate_text('Name') . ' <span class="required">*</span></label>
									<input id="author"  name="author" type="text" value="' . $name . '" class="author-' . $product_id . '" size="30" maxlength="245" required="required" ' . $readonly . '>
								</p>
								<p class="comment-form-email ' . $hide_email . '">
									<label for="email">' . aw_pq_translate_text('Email') . '<span class="required">*</span></label>
									<input id="email"  name="email" type="email" value="' . $email . '" class="email-' . $product_id . '" size="30" maxlength="100" aria-describedby="email-notes" required="required" ' . $readonly . '>
								</p>
								<p>
									<textarea id="comment_extra" name="comment" cols="45" rows="8" aria-required="true" class="comment-' . $product_id . '"></textarea>
								</p>';
				if (!is_user_logged_in()) {
					$html .='<p class="comment-form-cookies-consent">
									<input  ' . $already_guest . ' id="wp-comment-cookies-consent" name="wp-question-cookies-consent" type="checkbox" value="yes">
									<label for="wp-comment-cookies-consent">' . aw_pq_translate_text('Save my name, email in this browser for the next time I question.') . '</label>
								</p>';
				}
						$html .='<p class="form-submit">
									<input name="questionsubmit" type="submit" id="submit" class="submit btn_Q_submit" data-value="' . $product_id . '" value="' . aw_pq_translate_text('Send Question') . '">
									<input type="hidden" name="comment_post_ID" value="' . $product_id . '" id="comment_post_ID">
									<input type="hidden" name="comment_parent" id="comment_parent" value="0">
								</p>';
							$html .= wp_nonce_field('rd_batc_question_save_nonce_action', 'rd_batc_question_save_nonce_name');
							$html .= '</form></div>';

			} else {
				$html = '<div id="respond" class="comment-respond ' . $class . '" ><p>' . aw_pq_translate_text('Please') . ', <a href="' . get_permalink(wc_get_page_id( 'myaccount' )) . '">' . aw_pq_translate_text('log in') . '</a>&nbsp;' . aw_pq_translate_text('to post a question') . '</p></div>';
			}
			echo wp_kses($html, wp_kses_allowed_html('post'));
		}
		public static function aw_pq_wp_list_comments( $args = array(), $comments = null ) {

			global $wp_query, $overridden_cpage;

			$in_comment_loop = true;

			$comment_thread_alt = 0;
			$comment_alt   = 0;
			$comment_depth = 1;


			$defaults = array(
			'walker'            => null,
			'max_depth'         => '',
			'style'             => 'ul',
			'callback'          => null,
			'end-callback'      => null,
			'type'              => 'all',
			'page'              => '',
			'per_page'          => '',
			'avatar_size'       => 32,
			'reverse_top_level' => '',
			'reverse_children'  => '',
			'format'            => current_theme_supports( 'html5', 'comment-list' ) ? 'html5' : 'xhtml',
			'short_ping'        => false,
			'echo'              => true,
			);
		 
			$r = wp_parse_args( $args, $defaults );

			$r = apply_filters( 'wp_list_comments_args', $r );
		 
			// Figure out what comments we'll be looping through ($_comments)
			if ( null !== $comments ) {
				$comments = (array) $comments;
				if ( empty( $comments ) ) {
					return;
				}
				if ( 'all' != $r['type'] ) {
						$comments_by_type = separate_comments( $comments );
					if ( empty( $comments_by_type[ $r['type'] ] ) ) {
						return;
					}
						$_comments = $comments_by_type[ $r['type'] ];
				} else {
					$_comments = $comments;
				}
			} else {
				/*
				* If 'page' or 'per_page' has been passed, and does not match what's in $wp_query,
				* perform a separate comment query and allow Walker_Comment to paginate.
				*/
				if ( $r['page'] || $r['per_page'] ) {
					$current_cpage = get_query_var( 'cpage' );
					if ( ! $current_cpage ) {
						$current_cpage = 'newest' === get_option( 'default_comments_page' ) ? 1 : $wp_query->max_num_comment_pages;
					}

					$current_per_page = get_query_var( 'comments_per_page' );
					if ( $r['page'] != $current_cpage || $r['per_page'] != $current_per_page ) {
						$comment_args = array(
											'post_id' => get_the_ID(),
											//'orderby' => 'comment_date_gmt',
											//'order'   => 'ASC',
											//'status'  => 'approve',
											);

						if ( is_user_logged_in() ) {
							$comment_args['include_unapproved'] = get_current_user_id();
						} else {
							$unapproved_email = wp_get_unapproved_comment_author_email();

							if ( $unapproved_email ) {
								$comment_args['include_unapproved'] = array( $unapproved_email );
							}
						}

						$comments = get_comments( $comment_args );

						if ( 'all' != $r['type'] ) {
							$comments_by_type = separate_comments( $comments );
							if ( empty( $comments_by_type[ $r['type'] ] ) ) {
								return;
							}
								$_comments = $comments_by_type[ $r['type'] ];
						} else {
								$_comments = $comments;
						}
					}
		 
					// Otherwise, fall back on the comments from `$wp_query->comments`.
				} else {
					if ( empty( $wp_query->comments ) ) {
						return;
					}
					if ( 'all' != $r['type'] ) {
						if ( empty( $wp_query->comments_by_type ) ) {
							$wp_query->comments_by_type = separate_comments( $wp_query->comments );
						}
						if ( empty( $wp_query->comments_by_type[ $r['type'] ] ) ) {
							return;
						}
						$_comments = $wp_query->comments_by_type[ $r['type'] ];
					} else {
						$_comments = $wp_query->comments;
					}
		 
					if ( $wp_query->max_num_comment_pages ) {
						$default_comments_page = get_option( 'default_comments_page' );
						$cpage                 = get_query_var( 'cpage' );
						if ( 'newest' === $default_comments_page ) {
							$r['cpage'] = $cpage;
		 
							/*
							* When first page shows oldest comments, post permalink is the same as
							* the comment permalink.
							*/
						} elseif ( 1 == $cpage ) {
							$r['cpage'] = '';
						} else {
							$r['cpage'] = $cpage;
						}
		 
						$r['page']     = 0;
						$r['per_page'] = 0;
					}
				}
			}
	 
			if ( '' === $r['per_page'] && get_option( 'page_comments' ) ) {
				$r['per_page'] = get_query_var( 'comments_per_page' );
			}
		 
			if ( empty( $r['per_page'] ) ) {
				$r['per_page'] = 0;
				$r['page']     = 0;
			}
	 
			if ( '' === $r['max_depth'] ) {
				if ( get_option( 'thread_comments' ) ) {
					$r['max_depth'] = get_option( 'thread_comments_depth' );
				} else {
					$r['max_depth'] = -1;
				}
			}
	 
			if ( '' === $r['page'] ) {
				if ( empty( $overridden_cpage ) ) {
					$r['page'] = get_query_var( 'cpage' );
				} else {
					$threaded  = ( -1 != $r['max_depth'] );
					$r['page'] = ( 'newest' == get_option( 'default_comments_page' ) ) ? get_comment_pages_count( $_comments, $r['per_page'], $threaded ) : 1;
					set_query_var( 'cpage', $r['page'] );
				}
			}
			// Validation check
			$r['page'] = intval( $r['page'] );
			if ( 0 == $r['page'] && 0 != $r['per_page'] ) {
				$r['page'] = 1;
			}
		 
			if ( null === $r['reverse_top_level'] ) {
				$r['reverse_top_level'] = ( 'desc' == get_option( 'comment_order' ) );
			}
		 
			wp_queue_comments_for_comment_meta_lazyload( $_comments );
		 
			if ( empty( $r['walker'] ) ) {
				$walker = new Walker_Comment();
			} else {
				$walker = $r['walker'];
			}
		 
			$output = $walker->paged_walk( $_comments, $r['max_depth'], $r['page'], $r['per_page'], $r );
		 
			$in_comment_loop = false;
		 
			if ( $r['echo'] ) {
				echo wp_kses($output, wp_kses_allowed_html('post'));
			} else {
				return $output;
			}
		}

		public function aw_pq_paginate_comments_links( $args = array(), $unapproved_id, $user_id, $querystring ) {
			global $wp_rewrite;
	 
			if ( ! is_singular() ) {
				return;
			}
	 
			$page = get_query_var( 'cpage' );
			if ( ! $page ) {
				$page = 1;
			}
			//$max_page = get_comment_pages_count();
			global $product;
			$product_id = $product->get_id();
			$max_page = aw_pq_get_all_questions_count($product_id, $unapproved_id, $user_id);
			$per_page = get_option('comments_per_page');
			$max_page = ceil($max_page/$per_page);
			$defaults = array(
							'base'         => add_query_arg( 'cpage', '%#%' ),
							'format'       => '',
							'total'        => $max_page,
							'current'      => $page,
							'echo'         => true,
							'type'         => 'plain',
							'add_fragment' => '#' . $querystring,//#tab-QA_tab',
						);
			if ( $wp_rewrite->using_permalinks() ) {
				$defaults['base'] = user_trailingslashit( trailingslashit( get_permalink() ) . $wp_rewrite->comments_pagination_base . '-%#%', 'commentpaged' );
			}
			$args       = wp_parse_args( $args, $defaults );
			$page_links = paginate_links( $args );
			
			if ( $args['echo'] && 'array' !== $args['type'] ) {
				echo wp_kses($page_links, wp_kses_allowed_html('post'));
			} else {
				return $page_links;
			}
		}
	}//Close of Main if
}
