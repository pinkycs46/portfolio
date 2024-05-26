<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
class AwPqProductQuestionsQAtab {

	public static $sibling_array 	= array();

	public static function aw_pq_product_questions_new_tab( $tabs) {
		global $wpdb;
		$enabled_q_and_a = get_post_meta(get_the_ID(), 'enable_q_and_a', true);

		if ('yes' === $enabled_q_and_a) { 
			global $product;
			$product_id = $product->get_id();
			$total_Q	= 0;

			if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
				return;
			}

			$tabs['QA_tab'] = array(
								'title' => __( 'Q&A', 'woocommerce' ),
								'priority' => 25,
								'callback' => array(self::class, 'aw_pq_product_questions_tab_content')
								);

			$total_Q	= aw_pq_get_questions_count($product_id); 
			
			if (0 < $total_Q) {
				$get_highlight_qa_num_col = get_option('pq_number_color');

				if ('' != $get_highlight_qa_num_col) {
					$tabs['QA_tab']['title'] = 'Q&A (<span style = "color:#' . $get_highlight_qa_num_col . ';">' . $total_Q . '</span>)';
				} else {
					$tabs['QA_tab']['title'] = 'Q&A (' . $total_Q . ')';	
				}
			} else {
				$tabs['QA_tab']['title'] = 'Q&A (' . $total_Q . ')';
			}
			wp_reset_query();
		}
		return $tabs;
	}

	public static function aw_pq_product_questions_tab_content() {
		global $product, $wpdb;
		$product_id 	= $product->get_id();
		$title 			= $product->get_name();		
		$related_posts 	= array( $product_id );
		$parent_trash 	= array();

		$unapproved_id = 0;
		if ( isset($_GET['unapproved']) ) {
			$unapproved_id = sanitize_text_field($_GET['unapproved']);
		}
		// The comment Query
		$total_Q		= aw_pq_get_questions_count($product_id); 
		$obj	 		= null;
		$name 			= '';
		$email 			= '';
		$hide_email 	= '';
		$already_guest 	= '';
		$user_id 		= '';
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
		} else {
			if (isset($_COOKIE['guest_author']) && isset($_COOKIE['guest_email'])) {
				$name 	= sanitize_text_field($_COOKIE['guest_author']);
				$email 	= sanitize_text_field($_COOKIE['guest_email']);
				$user_id= $ip;//strtotime(gmdate('Y/m/d H:i:s'));
				$already_guest = 'checked';
			}
		}

		remove_filter( 'comments_clauses', 'aw_pq_filter_comments_clauses', 10, 1 );

		$userdata = get_user_by('id', $user_id);
		$ids = aw_pq_get_all_unapproved_question($product_id);

		$comment_not_in = array();

		if (!empty($ids)) {
			foreach ($ids as $key=> $id) {
				if ($user_id != $id->user_id) {
					$comment_not_in[] = $id->comment_ID;
				}
			}
		}

		if (empty($userdata) && 0 != $unapproved_id ) {
			if (in_array( $unapproved_id, $comment_not_in)) {
				$pos = array_search( $unapproved_id, $comment_not_in);	
				unset($comment_not_in[$pos]);
				$comment_not_in = array_values($comment_not_in);
			}
		}

		$parent_trash 		= aw_pq_product_question_get_trashed_comment_child($product_id);
		$comment_not_in 	= array_merge($comment_not_in, $parent_trash);

		$args = array(
			'post_id' 	 	 => $product_id,
			'type'			 => 'q_and_a',
			//'comment__in'	 => $comment_in,   
			'comment__not_in'=> $comment_not_in,   			           
			'meta_query' => array(
								'relation' => 'OR',
								'rd-pinned-to-top'=> array(
									'key' => 'rd-pinned-to-top',
									'value' => 'EXISTS',
								),
								'rd-helpful'=>array(
									'key' => 'rd-helpful',
									'compare' => 'EXISTS',
									'type' => 'numeric',
								), 
							),
			'orderby'	=> array(
								'rd-pinned-to-top' => 'DESC',
								'rd-helpful' => 'DESC',
							),				
		);
		
		$comments_query = new WP_Comment_Query();
		$comments       = $comments_query->query( $args );
		wp_reset_query();
		if ( class_exists( 'Aw_Pq_Walker_Comment' ) ) {
			$obj = new Aw_Pq_Walker_Comment();
		}
		if ( $comments ) {	

			$themeobject = wp_get_theme();						
			$theme_name = $themeobject->get( 'Name' );
		
			switch ($theme_name) {
				case 'Storefront': 
					$theme_id = 'aw_pq_commentsto';
					break;
				default: 
					$theme_id = 'aw_pq_comments';
			}
			?>

			<div id="<?php echo wp_kses($theme_id, wp_kses_allowed_html('post')) ; ?>" class="comments-area" style="padding-top: 0px">
				 <div class="rd_total_q_button">
				<?php if ( count($comments) ) : ?>
					<h2 class="comments-title">
						<?php
							echo wp_kses($title, wp_kses_allowed_html('post'));
						?>
					</h2>
					<?php 
					if ( count($comments)  || 0 != $unapproved_id ) {
						?>
						 <button class="submit" id="rd_ask_question" data-value="rd_ask" onclick="return askquestion()"><?php echo wp_kses(aw_pq_translate_text('Ask a Question'), wp_kses_allowed_html('post')); ?></button>
						<?php 
						if (null != $obj) {
							$obj->aw_pq_ask_question_form('hide_questionsform');	
						}
						
					} else { 
						if (null != $obj) {
							$obj->aw_pq_ask_question_form('show_questionsform');
						}
					}
					?>
				 </div>
					 
					<ol class="comment-list">
						<?php

						if (null != $obj) {
							/*$obj->aw_pq_wp_list_comments( array(
								'walker'			=> $obj,
								'style'     	 	=> 'ol',
								'reverse_top_level'	=> true,
								'avatar_size'		=> 50,
								//'reverse_children' => true,

							), $comments);*/
							$obj->aw_pq_wp_list_comments( array(
								'walker'			=> $obj,
								'style'     	 	=> 'ol',
								//'reverse_top_level'	=> true,
								'avatar_size'		=> 50,
								//'reverse_children' => true,

							), $comments);
						}
						?>
					</ol><!-- .comment-list -->
					<?php
					if ( aw_pq_get_all_questions_count($product_id, $unapproved_id, $user_id) > 1 && get_option( 'page_comments' ) ) :
						$querystring = 'tab-QA_tab';
						echo '<nav class="woocommerce-pagination">';
						$obj->aw_pq_paginate_comments_links(
							apply_filters(
								'woocommerce_comment_pagination_args',
								array(
									'prev_text' => '&larr;',
									'next_text' => '&rarr;',
									'type'      => 'list',
								)
							), $unapproved_id, $user_id, $querystring
						);
						echo '</nav>';
					endif;
			 
					if ( ! comments_open() && get_comments_number() ) : 
						?>
					<p class=""><?php //echo wp_kses('Comments are closed.', wp_kses_allowed_html('post')); ?></p>
					<?php endif; ?>
			 
				<?php else : ?>

					<div id="aw_pq_comments" class="comments-area comments">
						 <h2 class="comments-title">
							<?php
								echo wp_kses($title, wp_kses_allowed_html('post'));
							?>
						</h2>
					<?php 
					if (null != $obj) {
							$obj->aw_pq_ask_question_form('show_questionsform');
					} 
					?>
					</div>	
					<?php	
				  endif; // have_comments() 
				?>
			</div><!-- #comments -->
			<?php
		   
		} else { 
			?>
				<div id="aw_pq_comments" class="comments-area comments">
						 <h2 class="comments-title">
							<?php
								echo wp_kses($title, wp_kses_allowed_html('post'));
							?>
						</h2>
					<?php 
					if (null != $obj) {
							$obj->aw_pq_ask_question_form('show_questionsform');
					} 
					?>
				   </div>	
			<?php
		}
	}

	public static function aw_pq_product_questions_save_data( $post_id) {
		global $wpdb;
		$wp_version = get_bloginfo( 'version' );
		$whitelist 	= '';
		$phrases 	= '';
		$lastid 	= 0;
		if (version_compare($wp_version, '5.5.0', '>=')) {
			$whitelist 	= get_option( 'comment_previously_approved' );
			$phrases 	= get_option( 'disallowed_keys');
		} else {
			$whitelist 	= get_option( 'comment_whitelist' );
			$phrases 	= get_option( 'blacklist_keys');
		}

		if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
			return;
		}

		$enabled_q_and_a = get_post_meta(get_the_ID(), 'enable_q_and_a', true);
		if ('yes' === $enabled_q_and_a)	{
			return;
		}

		if (isset($_POST['rd_batc_question_save_nonce_name'])) {
			$rd_batc_question_save_nonce_name = sanitize_text_field($_POST['rd_batc_question_save_nonce_name']);
		}

		if ( !wp_verify_nonce( $rd_batc_question_save_nonce_name, 'rd_batc_question_save_nonce_action')) {
			wp_die('Our Site is protected - question and answer form');
		}
		if (isset($_POST['questionsubmit'])) {
			$author  			= '';
			$email 	 			= '';
			$user_id 			= 0;
			$comment_edit_id 	= 0;
			if (get_current_user_id()) {
				$user_id = get_current_user_id();	
			}

			if (isset($_POST['author']) && '' != $_POST['author'] && isset($_POST['email']) && '' != $_POST['email']) {
				$author 			= strip_tags(sanitize_text_field($_POST['author']));
				$email 				= sanitize_text_field($_POST['email']);
			
				if (isset($_POST['comment'])) {
					$comment 		= strip_tags(sanitize_text_field($_POST['comment']));
				}
				if (isset($_POST['comment_post_ID'])) {
					$postid 		= sanitize_text_field($_POST['comment_post_ID']);
				}
				if (isset($_POST['comment_parent'])) {
					$comment_parent_id 	= sanitize_text_field($_POST['comment_parent']);
				}
				if (isset($_POST['edit_id'])) {
					$comment_edit_id 	= sanitize_text_field($_POST['edit_id']);
				}

				$time   			= current_time('mysql');
				$gmdate 			= get_gmt_from_date($time);
				$post_type  		= 'q_and_a';
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
				
				 
				if (0 != $comment_parent_id && 1 != $moderation && 1 != $comment_whitelist) {
					 $comment_approved = 1;
				} 

				if (0 != $comment_parent_id && 1 == $comment_whitelist) {
					$whitelist_approve = aw_pq_whitelist_question_reply( $email);
					if ($whitelist_approve>0) {
						$comment_approved = 1;
					}
				}

				$max_links = get_option( 'comment_max_links' );
				if ( $max_links ) {
					$reg_exUrl = "/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\"]))/";
					$num_links = preg_match_all( $reg_exUrl, $comment, $out );
					if ( $num_links >= $max_links ) {
						$comment_approved = 0;
					}
				}

				$data 			= array(
										'comment_post_ID' 		=> $postid,
										'comment_author'  		=> $author,
										'comment_author_email'	=> $email,
										'comment_content' 		=> $comment,
										'comment_date' 			=> $time,
										'comment_date_gmt' 		=> $gmdate,
										'comment_approved' 		=> $comment_approved,
										'comment_agent'			=> '',
										'comment_type' 			=> $post_type,
										'user_id'				=> $user_id,
										'comment_parent'		=> $comment_parent_id
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
									'user_ip' 				=> aw_pq_get_the_user_ip(),
									'user_agent' 			=> $usr_agt,
									'referrer' 				=> $htp_ref,
									'permalink' 			=> get_the_permalink($postid),
									'comment_type' 			=> 'q_and_a',
									//'comment_author' 		=> $author,
									//'comment_author_email' => $email,
									'comment_author_url' 	=> '',
									'comment_content' 		=> $comment
									);
					$akismet_key = akismet_get_key();

					//$arr =akismet_check_key_status($akismet);
					$verify = akismet_verify_key( $akismet_key );
					if ('valid' === $verify) {
						$akis_first_check = self::aw_pq_akismet_comment_check( $akismet_key, $akismet_data);
						$akis_second_check = self::aw_pq_akismet_submit_spam( $akismet_key, $akismet_data);

						if ( true === $akis_first_check) {// && true === $akis_second_check) {// ||s
							$data['comment_approved'] = 'spam';
						}
					}
				}

				/* check phrase in Comment Blacklist for comment and reviews */
				//$phrases = get_option( 'blacklist_keys');
				if (!empty($phrases)) {
					$exploded 	= self::aw_pq_explodeX(array(',', '|',' '), $phrases);
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
					$exploded 	= self::aw_pq_explodeX(array(',', '|',' '), $moderation_phrases);
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
				if (0 == $comment_edit_id) {
					wp_insert_comment($data);	
					$lastid = $wpdb->insert_id;
					add_comment_meta($lastid, 'rd-pinned-to-top', '');
					add_comment_meta($lastid, 'rd-not-helpful', 0);
					add_comment_meta($lastid, 'rd-helpful', 0);
					add_comment_meta($lastid, 'rd-mail-sent', 0);

					if (isset($_POST['wp-question-cookies-consent']) && !empty($_POST['wp-question-cookies-consent'])) {
						aw_pq_set_guest_cookies($author, $email);
					} else {
						aw_pq_delete_guest_cookies($author, $email);
					}

					/*Send mail to Question Author*/
					if (0 != $comment_parent_id ) {
						$comment_approved 	= $wpdb->get_row( $wpdb->prepare( "SELECT `comment_approved`, `comment_type`  FROM {$wpdb->prefix}comments WHERE `comment_ID` =%d ", "{$lastid}" ) );
						$comment_id			= $lastid;
						if ('1' == $comment_approved->comment_approved && 'q_and_a' == $comment_approved->comment_type) {
							aw_pq_mail_to_question_author($postid, $comment_parent_id, $comment, $email, $comment_id);
						} elseif ('0' == $comment_approved->comment_approved && 'q_and_a' == $comment_approved->comment_type) {
							aw_pq_mail_to_question_author($postid, $comment_parent_id, $comment, $email, $comment_id);
						}
					}
					
					if (0 == $comment_parent_id) {
						$comment_id		= $lastid;
						$admin_mail_id 	= get_option('admin_email');
						/*Send mail to  Admin to someone ask a question*/
						aw_pq_mail_to_admin($postid, $comment_parent_id, $comment, $admin_mail_id, $comment_id);
						 /*Send  mail to customer for pending status*/
						aw_pq_mail_to_customer($postid, $author, $comment, $email, $comment_id);
					}
					
				} else {
					$data['comment_ID'] = $comment_edit_id;
					$comment = get_comment( $comment_edit_id );
					$comment_parent_id = $comment->comment_parent;
					$data['comment_approved'] 	= $comment->comment_approved;
					$data['comment_parent'] 	= $comment_parent_id;
					wp_update_comment($data);
				}	

				$link 	= get_permalink($postid);
				if (0 == $comment_parent_id && !is_user_logged_in() && !isset($_COOKIE['guest_author'])) {
					$link = add_query_arg( 'unapproved', $lastid, get_permalink($postid) ) ;
				} else if (1 == $moderation) {
					$link =  add_query_arg( 'unapproved', $lastid, get_permalink($postid) ) ; 
				} else if ( '' == $moderation && 0 == $data['comment_approved'] && $max_links > 0) {
					$link =  add_query_arg( 'unapproved', $lastid, get_permalink($postid) ) ;
				}
				wp_redirect($link);
			}
		}
	}

	/* Ajax call for like and dislike */
	public static function aw_pq_product_question_like_dislike() {
		check_ajax_referer( 'rdproductquestion_nonce', 'rd_qa_nonce_ajax' );

		$count 		= 0;
		$meta_key	= '';
		$users		= array();
		$vote_data	= array();
		$data 		= array();
		$path 		= '/';
		$host 		= parse_url(get_option('siteurl'), PHP_URL_HOST);
		$days 		= get_option('rd_setting_cookie_days');
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

			$data = get_comment_meta($comment_id, 'rd_vote_users' , true) ? get_comment_meta($comment_id, 'rd_vote_users' , true) : array();
			$vote = get_comment_meta($comment_id, $trigger_type, true);

			if ( 'rd-helpful' === $trigger_type ) {
				$opposite_trigger = 'rd-not-helpful';
				$vote++;
			}
			if ( 'rd-not-helpful' === $trigger_type ) {
				$opposite_trigger = 'rd-helpful';
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
				} else {
					self::aw_pq_update_vote_value($comment_id, $vote, $trigger_type, $opposite_trigger);		        	 
				} 
			} else {
					self::aw_pq_update_vote_value($comment_id, $vote, $trigger_type, $opposite_trigger);		
			}

			
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
			$data = get_comment_meta($comment_id, 'rd_vote_users' , true);
			$users['rd_helpful'] 	= get_comment_meta($comment_id, 'rd-helpful' , true); 
			$users['rd_not_helpful'] = get_comment_meta($comment_id, 'rd-not-helpful' , true);
			$users['changed_image'] = plugins_url('/admin/images/Thumb-icon-' . $trigger_type . '.png', __DIR__);

			if ($users['rd_helpful']>0) {
				$users['rd_helpful_image'] = plugins_url('/admin/images/Thumb-icon-rd-helpful.png', __DIR__);
			} else {
				$users['rd_helpful_image'] = plugins_url('/admin/images/Thumb-icon-default-rd-helpful.png', __DIR__);
			}

			if ($users['rd_not_helpful']>0) {
				$users['rd_not_helpful_image'] = plugins_url('/admin/images/Thumb-icon-rd-not-helpful.png', __DIR__);
			} else {
				$users['rd_not_helpful_image'] = plugins_url('/admin/images/Thumb-icon-default-rd-not-helpful.png', __DIR__);
			}
		}
		echo json_encode($users);
		die;
	}


	public static function aw_pq_update_vote_value( $comment_id, $vote, $trigger_type, $opposite_trigger) {
		$cookie 		= array();
		$opp_meta_key	= $opposite_trigger . '_count';	

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

	// Passes back true (it's spam) or false (it's ham)
	public static function aw_pq_akismet_comment_check( $key, $data ) {
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

	public static function aw_pq_akismet_submit_spam( $key, $data ) {
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

	public static function aw_pq_explodeX( $delimiters, $string ) {
		return explode( chr( 1 ), str_replace( $delimiters, chr( 1 ), $string ) );
	}

	/* Ajax call for like and dislike */
	public static function aw_pq_check_enable_edit_comment() {
		check_ajax_referer( 'rdproductquestion_nonce', 'rd_qa_nonce_ajax' );
		//$enabled_edit_btn = false;
		$data = array();		
		$data['result'] = false;

		if (isset($_POST['commentid'])) {
			$comment_id 	= sanitize_text_field($_POST['commentid']);
			$comment 		= get_comment($comment_id);
			$comment_date 	= date_create($comment->comment_date);
			$current_date 	= date_create(gmdate('Y-m-d H:i:s'));
			$diff 			= date_diff($current_date, $comment_date);
			if (get_option('pq_editin_minutes') > 0) {
				$x_minutes = get_option('pq_editin_minutes');
				if ($x_minutes>=$diff->i) {
					//$enabled_edit_btn = true;
					$data['result'] = true;
				}
			}
			if ('' == get_option('pq_editin_minutes') ||  0 == get_option('pq_editin_minutes')) {
				$data['result'] = true;
			}
			if (0 == $comment->comment_parent) {
				$data['message'] = aw_pq_translate_text('Time period in which your question can be edited is expired.');
			} else {
				$data['message'] = aw_pq_translate_text('Time period in which your answer can be edited is expired.');
			}
		}

		echo json_encode($data);
		wp_die();
	}

	public static function aw_pq_active_QA_tab_product_page() {
	 // Only in single product pages and a specific url (using GET method) 
		if (is_product()) :
			?>
			<script type="text/javascript">
			   var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('#');
			(function($){
				if(hashes[hashes.length-1].length>0)
				{
					var tab = hashes[hashes.length-1];
					if('tab-QA_tab'==tab)
					{	
						$('div#tab-description').hide();
						$('div#tab-reviews').hide();
						setTimeout(function() {

								$('ul.tabs.wc-tabs > li.active').removeClass("active");
								$('div#tab-description').hide();
								$('div#tab-reviews').hide();
								$('ul.tabs.wc-tabs > li.QA_tab_tab').addClass("active");
								$('div#tab-QA_tab').show();

						}, 500);
					}
				}
			})(jQuery);	
			</script>
			<?php
		endif;
	}
}
