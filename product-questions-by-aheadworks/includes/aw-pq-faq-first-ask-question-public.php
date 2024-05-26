<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class AwPqFaqFirstAskQuestionsPublic {

	public static function aw_faq_enable_ask_form() {
		global $wpdb;
		$db_aw_pq_cat_table	= $wpdb->prefix . 'aw_pq_faq_category_list';
			//Check to see if the table exists or not
		if ($wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ' , "{$wpdb->prefix}aw_pq_faq_category_list")) == $db_aw_pq_cat_table) {
			$categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aw_pq_faq_category_list WHERE status = 1 ORDER BY sort_order", ARRAY_A);
			foreach ($categories as $cat ) {
				$meta_value = $cat['id'];
				$meta_key = 'select_category';
				$table1 = 'postmeta';
				$article = $wpdb->get_results($wpdb->prepare("SELECT p1.post_id AS postid FROM {$wpdb->prefix}%5s p1 , {$wpdb->prefix}%5s p2 , {$wpdb->prefix}%5s p3 WHERE p1.meta_value = %d AND p1.meta_key = %s AND p1.post_id = p2.post_id AND p2.post_id = p3.post_id AND p2.meta_key = %s AND p3.meta_key = %s ORDER BY CASE WHEN p2.meta_value = p3.meta_value THEN p3.meta_value ELSE p2.meta_value END ASC, CASE WHEN p2.meta_value = p3.meta_value THEN p2.meta_value ELSE p3.meta_value END DESC", "{$table1}" , "{$table1}", "{$table1}", "{$meta_value}", "{$meta_key}", 'faq_art_sort_order', 'faq_num_helpful_votes'), ARRAY_N);
				if (!empty($article)) {
					foreach ($article as $art => $value) {
						$c 					= count($article);
						$postid 			= $value[0];
						$status 			= get_post_status($postid);
						$enable_ques_form 	= get_option('faq_setting_ask_question_form');
						$post_table = $wpdb->prefix . 'posts';
						if ('yes' === $enable_ques_form) {
							$update_post_array = array(
								'post_type' 		=> 'faq_article',
								'comment_status'	=> 'open'
							);
				
							$wpdb->update($post_table, $update_post_array, array('ID'=>$postid));
										
						} else {
							$update_post_array = array(
								'post_type' 		=> 'faq_article',
								'comment_status'	=> 'closed'
							);
							$wpdb->update($post_table, $update_post_array, array('ID'=>$postid));
						}
					}	

				}
			}
		}
	}

	public static function aw_faq_comment_form_default( $defaults ) {
		$post_type = get_post_type();
		if ('faq_article' == $post_type) {
			if (!empty($defaults) && isset($defaults) ) {
				if ( isset( $defaults[ 'fields' ] ) ) {
					$commenter = wp_get_current_commenter(); 
					$consent = empty( $commenter['comment_author_email'] ) ? '' : ' checked="checked"';
					$defaults['fields']['cookies'] = '<p class="comment-form-cookies-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"' . $consent . ' /><label for="wp-comment-cookies-consent">Save my name, email, and website in this browser for the next time I ask a question.</label></p>';
				}
				if ( isset( $defaults[ 'comment_field' ] ) ) {
					$defaults[ 'comment_field' ] = ' <p class="comment-form-comment"><label for="comment"></label> <textarea id="comment" name="comment" cols="45" rows="8" maxlength="65525" required="required"></textarea></p>';
				}
				if ( isset( $defaults[ 'title_reply' ] ) ) {
					$defaults[ 'title_reply' ] = 'Ask a question';
				}
				if ( isset( $defaults[ 'label_submit' ] ) ) {
					$defaults[ 'label_submit' ] = 'Submit';
				}
			}
		}
		return $defaults;
	}
	
	public static function aw_faq_permanent_redirect() {
		if (isset($_SERVER['REQUEST_URI'])) {
			$url = sanitize_text_field($_SERVER['REQUEST_URI']);
			$key = 'faq_cat';
			$check1 = strpos($url, $key);
			if ('yes' == get_option('faq_setting_redirect_url')) {
				if ( !empty($check1)) {
					$path = '/';
					$host = parse_url(get_option('siteurl'), PHP_URL_HOST);
					setcookie('redirect', 1, time() + ( 60 * 1 ), $path, $host);
					$cat_suffix=get_option('faq_setting_category_url_suffix');
					$term = get_queried_object();
					$str = $url;
					$arr=explode('/', $str);
					$a=count($arr);
					global $wpdb;
					//$check1 = $wpdb->get_results( 'SELECT term_id FROM ' . $wpdb->prefix . "term_taxonomy WHERE taxonomy= 'faq_cat'" );
					$check1 = $wpdb->get_results( $wpdb->prepare("SELECT term_id FROM {$wpdb->prefix}term_taxonomy WHERE taxonomy= %s ", 'faq_cat' ), ARRAY_N);
					foreach ($check1 as $key => $value) {
						$c 			= $check1[$key];
						$term_id 	= $c[0];
						$name = $wpdb->get_var( $wpdb->prepare("SELECT slug FROM {$wpdb->prefix}terms WHERE term_id= %d ", "{$term_id}"));
						$redirect= strpos($name, $arr[$a-2]);
						if (  ' ' == $redirect) {
							$strr=str_replace($arr[$a-2], $name, $str);
							if (isset($_SERVER['HTTPS']) &&  'on' === $_SERVER['HTTPS']) {
								$link = 'https';
							} else {
								$link = 'http';
							}
							$link .= '://';
							if (isset($_SERVER['HTTP_HOST'])) {
								$link .= sanitize_text_field($_SERVER['HTTP_HOST']);
							}
							$newurl = $link . $strr;
							if (!isset($_COOKIE['redirect'])) {
								if (!empty($redirect) ||  0 == $redirect) {
									wp_redirect($newurl);
									die;
								}
							}
						}
					}
				}
			}
		}
	}

	public static function aw_faq_canonical_url( $canonical_url, $post ) {
		if ('yes' == get_option('faq_setting_meta_tag_articles_link')) {
			$art_suffix=get_option('faq_setting_article_url_suffix');
			$canonical_url=$canonical_url . $art_suffix;
		}
		return $canonical_url;
	}

	public static function aw_faq_custom_menu_item() {
		global $wpdb;
		$id   = get_the_ID();
		$term = get_queried_object();
		if (isset($term->slug) && !empty($term->slug)) {

			/*$meta_title = $wpdb->get_var( 'SELECT category_meta_title FROM ' . $wpdb->prefix . "aw_pq_faq_category_list WHERE category_slug = '" . $term->slug . "'" );
			$meta_desc = $wpdb->get_var( 'SELECT category_meta_description FROM ' . $wpdb->prefix . "aw_pq_faq_category_list WHERE category_slug = '" . $term->slug . "'" );*/

			$meta_title = $wpdb->get_var($wpdb->prepare("SELECT category_meta_title FROM {$wpdb->prefix}aw_pq_faq_category_list WHERE category_slug = %s ", "{$term->slug}"));
			$meta_desc = $wpdb->get_var($wpdb->prepare("SELECT category_meta_description FROM {$wpdb->prefix}aw_pq_faq_category_list WHERE category_slug = %s ", "{$term->slug}"));

			$separator=get_option('faq_setting_page_seprator');
			$meta_title = str_replace(' ', $separator, $meta_title);
			echo '<meta name="title" content="' . esc_html($meta_title) . '" />';
			echo '<meta name="description" content="' . esc_html($meta_desc) . '" />';
			if ('yes' == get_option('faq_setting_meta_tag_categories_link')) {
					$cat_suffix=get_option('faq_setting_category_url_suffix');
				if (isset($_SERVER['HTTPS']) &&  'on' === $_SERVER['HTTPS']) {
					$link = 'https'; 
				} else {
					$link = 'http'; 
				}
					$link .= '://'; 
				if (isset($_SERVER['HTTP_HOST'])) {
					$server_host = sanitize_text_field($_SERVER['HTTP_HOST']);
					$link .= $server_host; 
				}
				if (isset($_SERVER['REQUEST_URI'])) {
					$server_request = sanitize_text_field($_SERVER['REQUEST_URI']);
					$link .= $server_request; 
				}					
					$url = $link . $cat_suffix;
					echo '<link rel="canonical" href="' . esc_html($url) . '" />';
			}
		} else {
			$arr=get_post_meta($id);
			if (isset($arr['_faq_meta_title'])) {
				$separator=get_option('faq_setting_page_seprator');
				$arr['_faq_meta_title'] = str_replace(' ', $separator, $arr['_faq_meta_title']);
				foreach ($arr['_faq_meta_title'] as $key => $value) {
					echo '<meta name="title" content="' . esc_html($value) . '" />';
				} 
			}
			if (isset($arr['_faq_meta_desc'])) {
				foreach ($arr['_faq_meta_desc'] as $key => $value) {
					echo '<meta name="description" content="' . esc_html($value) . '" />';
				} 
			}
			if (!empty($arr['faq_art_meta_title'])) {
				$separator=get_option('faq_setting_page_seprator');
				$arr['faq_art_meta_title'] 	= str_replace(' ', $separator, $arr['faq_art_meta_title']);
				foreach ($arr['faq_art_meta_title'] as $key => $value) {
					echo '<meta name="title" content="' . esc_html($value) . '" />';
				} 
			}
			if (!empty($arr['faq_art_desc'])) {
				foreach ($arr['faq_art_desc'] as $key => $value) {
					echo '<meta name="description" content="' . esc_html($value) . '" />';
				} 
			}
			$slug = $wpdb->get_var( 'SELECT slug FROM ' . $wpdb->prefix . "terms WHERE slug = 'faq-menu'" );
			if (empty($slug)) {
				$menu = wp_insert_term('FAQ MENU', 'nav_menu', array('slug' => 'faq-menu'));
			}
		}
	}

	public static function aw_faq_custom_search() {
		if (isset($_SERVER['REQUEST_URI'])) {
			$url 			= sanitize_text_field($_SERVER['REQUEST_URI']);
			$key 			= 'faq_cat';
			$check 			= strpos($url, $key);
			$term 			= get_queried_object();
			$enable_search 	= get_option('faq_setting_enable_search_articles');
			
			if (!empty($check)) {
				echo '<h1 class="page-title">FAQ Category: <span>' . esc_html($term->slug) . '</span></h1><br><div class="widget-content">
				<form role="search" method="get" class="search-article" action="' . esc_url(site_url()) . '">
				<label for="search-article-2">
				<span class="screen-reader-text">Search for:</span>
				<input type="search" id="search-article-2" class="search-field-art" placeholder="Type here to search FAQ articles" value="" name="s" style="height:50px;" height="500px">
				</label>
				<input type="submit" class="search-submit" value="Search">
				<input type="hidden" name="post_type" value="faq_article">
				</form>
				</div><br>';
			}
			
		}				
	}
	public static function aw_paq_faq_frontend_page() {
		global $wpdb;
		$html 			= '';
		$enable_search 	= get_option('faq_setting_enable_search_articles');
		$view_faq_page 	= get_option('faq_setting_view_content');
		$default_columns_main_page 	= get_option('faq_setting_default_columns_main_page');

		if ($default_columns_main_page) {
			if ('1' == $default_columns_main_page) {
				$faq_column = 'faq_column_one';
			}
			if ('2' == $default_columns_main_page) {
				$faq_column = 'faq_column_two';
			}
			if ('3' == $default_columns_main_page) {
				$faq_column = 'faq_column_three';
			}
		}

		if ( ( '' == $view_faq_page || 'anyone' == $view_faq_page )  || ( 'loggedinuser' == $view_faq_page && is_user_logged_in() ) ) {

			

			$html .= '<div class="widget-content">
						<form role="search" method="get" class="search-article" action="' . site_url() . '">
							<label for="search-article-2">
								<span class="screen-reader-text">Search for:</span>
								<input type="search" id="search-article-2" class="search-field-art" placeholder="Type here to search FAQ articles" value="" name="s">
							</label>
							<input type="submit" class="search-submit" value="Search">
							<input type="hidden" name="post_type" value="faq_article">	
						</form>
					</div>';
		
			$html .= '<div class ="faq-table"><ul class = "aw-pq" id = "' . $faq_column . '">';

			$categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aw_pq_faq_category_list WHERE status = 1 ORDER BY sort_order", ARRAY_A);
			
			$art_suffix=get_option('faq_setting_article_url_suffix');
			$cat_suffix=get_option('faq_setting_category_url_suffix');
			foreach ($categories as $cat ) {
				$meta_value = $cat['id'];
				$num_art = $cat['category_num_articles_page'];
				$meta_key = 'select_category';
				if (!empty($cat['category_icon_file'])) {
					$html .= "<li class='span'><img src='" . $cat['category_icon_file'] . "' width='50px' height='50px'><h3><a href='" . site_url() . '/index.php/faq_cat/' . $cat['category_slug'] . "/'>" . $cat['category_name'] . "</a></h3><ul style='list-style-type: none;'>";
				} else {
					$html .= "<li class='span'><a href='" . site_url() . '/index.php/faq_cat/' . $cat['category_slug'] . "/'>" . $cat['category_name'] . "</a><ul style='list-style-type: none;'>";
				}
				$table1 = 'postmeta';
		
				$article = $wpdb->get_results($wpdb->prepare("SELECT p1.post_id AS postid FROM {$wpdb->prefix}%5s p1 , {$wpdb->prefix}%5s p2 , {$wpdb->prefix}%5s p3 WHERE p1.meta_value = %d AND p1.meta_key = %s AND p1.post_id = p2.post_id AND p2.post_id = p3.post_id AND p2.meta_key = %s AND p3.meta_key = %s ORDER BY CASE WHEN p2.meta_value = p3.meta_value THEN p3.meta_value ELSE p2.meta_value END ASC, CASE WHEN p2.meta_value = p3.meta_value THEN p2.meta_value ELSE p3.meta_value END DESC", "{$table1}" , "{$table1}", "{$table1}", "{$meta_value}", "{$meta_key}", 'faq_art_sort_order', 'faq_num_helpful_votes'), ARRAY_N);

				if (!empty($article)) {
					$i = 1;
					foreach ($article as $art => $value) {
						$c 					= count($article);
						$postid 			= $value[0];
						$status 			= get_post_status($postid);
						if ('publish' == $status) {
							$post = get_post($postid);
							$art_name = $post->post_title;
							$guid = $post->guid;

							if (!empty($cat['articles_list_icon_file'])) {
								$html .= "<li style = 'list-style-type: none;'><img src='" . $cat['articles_list_icon_file'] . "' width='20px' height='20px'><a href='" . $guid . "'>" . $art_name . '</a></li>';
							} else {
								$html .= "<li><a href='" . $guid . "'>" . $art_name . '</a></li>';
							}
						}
						if ($i++ == $num_art && $c != $num_art) {
							$html .= "<li style = 'list-style-type: none;'><a href='" . site_url() . '/index.php/faq_cat/' . $cat['category_slug'] . "'><p style='color:green;'>Read more article.....</p></a></li>";
							break;
						}
					}
				}	
				$html .= '</ul>';
				$html .= '</li>';
			}
			$html .= '</ul></div>';
		} else {
				$html = '<p>Please&nbsp;<a href="' . get_permalink(wc_get_page_id( 'myaccount' )) . '">log in</a>&nbsp;to see the articles</p>';
		}
		return $html;
	}

	public static function aw_faq_save_comment_type_handler( $commentdata ) {
		
		$post_id 	= $commentdata['comment_post_ID'];
		$post_type 	= get_post_type($post_id);
		if ('faq_article' == $post_type ) {
			$commentdata['comment_type'] = 'faq_comment';
		}
		return $commentdata;
	}

	public static function aw_faq_like_and_dislike_html( $content) {
		global $wpdb,$post;

		if (isset($_SERVER['REQUEST_URI'])) {			
			$url 				= sanitize_text_field($_SERVER['REQUEST_URI']);
			$key 				= 'faq_cat';
			$check 				= strpos($url, $key);
			$term 				= get_queried_object();
		}
		
		$user_id 			= get_current_user_id();
		$post_id 			= $post->ID;
		$post_type 			= get_post_type($post_id);
		$view_helpfuness  	= get_option('faq_setting_view_helpfulness');
		$before_vote 		= get_option('faq_setting_helpfulness_rate_before_voting');
		$after_vote  		= get_option('faq_setting_helpfulness_rate_after_voting');
		$enable_search 		= get_option('faq_setting_enable_search_articles');

		
		if ('faq_article' == $post_type  && empty($check)) {
			$helpful_rate = round(get_post_meta($post_id, 'faq_helpful_rate', true), 2);

			$like_dislike_count	= array();
			$image_url 			= array();
			$before_content 	= '';
			$after_content 		= '';
			
			$like_dislike_count['faq_num_helpful_votes'] 		= 0;
			$like_dislike_count['faq_num_not_helpful_votes'] 	= 0;
			$like_dislike_count['faq_helpful_rate'] 			= 0;


			$like_dislike_count['faq_num_helpful_votes'] = get_post_meta($post_id, 'faq_num_helpful_votes' , true) ? get_post_meta($post_id, 'faq_num_helpful_votes' , true) : 0;
			if ($like_dislike_count['faq_num_helpful_votes']>0) {
				$image_url['faq_num_helpful_votes']		= plugins_url('/public/images/Thumb-icon-faq_num_helpful_votes.png', __DIR__);
			} else {
				$image_url['faq_num_helpful_votes']		= plugins_url('/public/images/Thumb-icon-default-faq_num_helpful_votes.png', __DIR__);
			}

			$like_dislike_count['faq_num_not_helpful_votes'] = get_post_meta($post_id, 'faq_num_not_helpful_votes' , true) ? get_post_meta($post_id, 'faq_num_not_helpful_votes' , true) : 0;
			if ($like_dislike_count['faq_num_not_helpful_votes']>0) {
				$image_url['faq_num_not_helpful_votes']	= plugins_url('/public/images/Thumb-icon-faq_num_not_helpful_votes.png', __DIR__);
			} else {
				$image_url['faq_num_not_helpful_votes']	= plugins_url('/public/images/Thumb-icon-default-faq_num_not_helpful_votes.png', __DIR__);
			}	

			if (0 != $like_dislike_count['faq_num_not_helpful_votes'] ) {
				$like_dislike_count['faq_num_not_helpful_votes'] = '-' . $like_dislike_count['faq_num_not_helpful_votes'];
			}	

			 $per_helpful_vote = get_post_meta($post_id, 'faq_helpful_rate' , true) ? get_post_meta($post_id, 'faq_helpful_rate' , true) : 0;

			 $like_dislike_count['faq_helpful_rate']  = round($per_helpful_vote, 0, PHP_ROUND_HALF_UP);

			if ('yes' == $enable_search) {
				$before_content = '<div class="widget-content">
										<form role="search" method="get" class="search-article" action="' . site_url() . '">
										<label for="search-article-2">
								 			<span class="screen-reader-text">Search for:</span>
								 			<input type="search" id="search-article-2" class="search-field-art" placeholder="Type here to search FAQ articles" value="" name="s">
							 			</label>
							 			<input type="submit" class="search-submit" value="Search">
										<input type="hidden" name="post_type" value="faq_article">
							 			</form>
						 			</div>';	
			}

			if ( ( '' == $view_helpfuness || 'anyone' == $view_helpfuness )  || ( 'loggedinuser' == $view_helpfuness && is_user_logged_in() ) ) {
				$after_content = '<div class="thumbs-rate">
								<h3>Was this helpful?</h3>
								<div class="thumbs-images">
									<img src="' . $image_url['faq_num_helpful_votes'] . '" id="faq_num_helpful_votes-' . $post_id . '" class="faq_like_dislike_img" data-trigger-type="faq_num_helpful_votes"  data-post-id="' . $post_id . '" data-user-id="' . $user_id . '" />
									<span id="faqhelpfulcount-' . $post_id . '">' . $like_dislike_count['faq_num_helpful_votes'] . '</span>
								</div>
								<div class="thumbs-images">
									<img src="' . $image_url['faq_num_not_helpful_votes'] . '" id="faq_num_not_helpful_votes-' . $post_id . '" class="faq_like_dislike_img" data-trigger-type="faq_num_not_helpful_votes" data-post-id="' . $post_id . '" data-user-id="' . $user_id . '"/>
									<span id="faqnothelpfulcount-' . $post_id . '"> ' . $like_dislike_count['faq_num_not_helpful_votes'] . '</span>&nbsp;&nbsp;';

				if ('yes' == $before_vote && 'no' == $after_vote) {
					$cookie_voted_user = '';
					if (isset($_COOKIE['faq_vote_user'])  ) {
						$cookie_voted_user = sanitize_text_field($_COOKIE['faq_vote_user']);
					}
					//if ( !isset($_COOKIE['faq_vote_user'])  ){
					if ( 'yes' . $post_id != $cookie_voted_user ) {
						$after_content.='<span id="faqhelpfulrate-' . $post_id . '">(' . $like_dislike_count['faq_helpful_rate'] . '% of other people think it was helpful</span>';
					} else {
						$after_content.='<span id="faqhelpfulrate-' . $post_id . '"></span>';
					}
																				
				} else if ('no' == $before_vote && 'yes' == $after_vote) {
					$likedislikecount = '';
					$cookie_voted = '';
					if (isset($_COOKIE['faq_vote_user'])  ) {
						$cookie_voted = sanitize_text_field($_COOKIE['faq_vote_user']);
					}
					// if ( isset($_COOKIE['faq_vote_user']) ) {
					if ( 'yes' . $post_id == $cookie_voted) {
						$likedislikecount = '(' . $like_dislike_count['faq_helpful_rate'] . ')% of other people think it was helpful';
					}
					$after_content .='<span id="faqhelpfulrate-' . $post_id . '">' . $likedislikecount . '</span>';
										
				} else if ('yes' == $before_vote && 'yes' == $after_vote) {
					$after_content .='<span id="faqhelpfulrate-' . $post_id . '">(' . $like_dislike_count['faq_helpful_rate'] . '%  of other people think it was helpful)</span>';										
				} else if ('no' == $before_vote && 'no' == $after_vote) {
					$after_content .='';	
				} else {										
					$after_content .='<span id="faqhelpfulrate-' . $post_id . '"></span>';	
				}									

								$after_content.='</div>';																							
			$after_content .='</div>';
			} else {
				$after_content = '<p>Please&nbsp;<a href="' . get_permalink(wc_get_page_id( 'myaccount' )) . '">log in</a>&nbsp;to rate of post</p>';
			}
			
			
			$content = $before_content . $content . $after_content;
		}
		return $content;

	}


	/* Ajax call for FAQ like and dislike */

	public static function aw_faq_like_dislike() {
		$before_vote 		= get_option('faq_setting_helpfulness_rate_before_voting');
		$after_vote  		= get_option('faq_setting_helpfulness_rate_after_voting');
		check_ajax_referer( 'firstaskquestion_nonce', 'pq_faq_nonce_ajax' );

		$count 		= 0;
		$meta_key	= '';
		$users		= array();
		$vote_data	= array();
		$data 		= array();
		$path 		= '/';
		$host 		= parse_url(get_option('siteurl'), PHP_URL_HOST);
		$days 		= 1;
		$cookie		= array();
		 

		if (isset($_POST['post_id'])) {
				$post_id = sanitize_text_field($_POST['post_id']);
		}
		$users['changed_image'] = '';
		$users['default_image'] = '';

		if ( isset($_POST['trigger_type']) && isset($_POST['user_id']) ) {
			$skip = false;
			$trigger_type 	= sanitize_text_field($_POST['trigger_type']);
			$meta_key 		= $trigger_type . '_count' ; 

			 
			setcookie('faq_vote_user', 'yes' . $post_id, time() + ( 86400 * $days ), $path, $host);
			 
			

			$vote = get_post_meta($post_id, $trigger_type, true);

			if ( 'faq_num_helpful_votes' === $trigger_type ) {
				$opposite_trigger = 'faq_num_not_helpful_votes';
				$vote++;
			}
			if ( 'faq_num_not_helpful_votes' === $trigger_type ) {
				$opposite_trigger = 'faq_num_helpful_votes';
				$vote++;
			}
			$opposite_meta_key = $opposite_trigger . '_count';

			if ( isset($_COOKIE[$meta_key]) ) {

				$cookie = json_decode(stripslashes(sanitize_text_field($_COOKIE[$meta_key])), true);
				if (in_array($post_id, $cookie)) {
					$vote = get_post_meta($post_id, $trigger_type, true);
					if (0 < $vote) {
						$vote--;	
					}
					update_post_meta($post_id, $trigger_type, $vote);
					$key = array_search($post_id, $cookie);
					unset($cookie[$key]);
					$value = json_encode($cookie);
					setcookie($meta_key, $value, time() + ( 86400 * $days ), $path, $host);
					$skip = true; 
				} else {
					self::aw_faq_update_vote_value($post_id, $vote, $trigger_type, $opposite_trigger);		        	 
				} 
			} else {
					self::aw_faq_update_vote_value($post_id, $vote, $trigger_type, $opposite_trigger);		
			}

			
			if ( false === $skip ) {
				array_push($cookie, $post_id);
				$value = json_encode($cookie);
				setcookie($meta_key, $value, time() + ( 86400 * $days ), $path, $host); 
			}

			if ( isset($_COOKIE[$opposite_meta_key]) ) {
				$opposite_cookie = json_decode(stripslashes(sanitize_text_field($_COOKIE[$opposite_meta_key])), true);

				if (!empty($opposite_cookie) && in_array($post_id, $opposite_cookie)) {
					$key = array_search($post_id, $opposite_cookie);	
					unset($opposite_cookie[$key]);
					$value = json_encode($opposite_cookie);
					setcookie($opposite_meta_key, $value, time() + ( 86400 * $days ), $path, $host); 
				}	
			}

			$helpful_votes 		= (int) get_post_meta($post_id, 'faq_num_helpful_votes', true);
			$not_helpful_votes 	= (int) get_post_meta($post_id, 'faq_num_not_helpful_votes', true);
			$total 				= $helpful_votes  + $not_helpful_votes;
			update_post_meta($post_id, 'faq_num_total_votes', $total);

			if (( !empty($total) || 0 != $total ) && ( !empty($helpful_votes) || 0 != $helpful_votes )) {
					$rate = ( $helpful_votes * 100 );
					$rate = ( $rate / $total );
					$per_rate = round($rate, 0, PHP_ROUND_HALF_UP);
					update_post_meta($post_id, 'faq_helpful_rate', $per_rate);
			} else {
				update_post_meta($post_id, 'faq_helpful_rate', 0);
			}

			//86400 for 1 day
			$users['helpful_votes'] 	= get_post_meta($post_id, 'faq_num_helpful_votes' , true); 
			$users['not_helpful_votes'] = get_post_meta($post_id, 'faq_num_not_helpful_votes' , true);
			$users['changed_image'] 	= plugins_url('/public/images/Thumb-icon-' . $trigger_type . '.png', __DIR__);
			$users['helpful_rate'] 		= get_post_meta($post_id, 'faq_helpful_rate' , true);
			$users['before_vote']		= get_option('faq_setting_helpfulness_rate_before_voting');
			$users['after_vote'] 		= get_option('faq_setting_helpfulness_rate_after_voting');
			$users['faq_vote_user'] = '';
			
			if ('no' == $before_vote && 'yes' == $after_vote) {
				$users['faq_vote_user'] = 'yes' . $post_id;			
			}
			if ('yes' == $before_vote && 'yes' == $after_vote) {	
				$users['faq_vote_user'] = 'yes' . $post_id;			
			}		
			
			if ($users['helpful_votes']>0) {
				$users['helpful_votes_image'] = plugins_url('/public/images/Thumb-icon-faq_num_helpful_votes.png', __DIR__);
			} else {
				$users['helpful_votes_image'] = plugins_url('/public/images/Thumb-icon-default-faq_num_helpful_votes.png', __DIR__);
			}

			if ($users['not_helpful_votes']>0) {
				$users['not_helpful_votes_image'] = plugins_url('/public/images/Thumb-icon-faq_num_not_helpful_votes.png', __DIR__);
			} else {
				$users['not_helpful_votes_image'] = plugins_url('/public/images/Thumb-icon-default-faq_num_not_helpful_votes.png', __DIR__);
			}
		}
		echo json_encode($users);
		wp_die();

	}

	public static function aw_faq_update_vote_value( $post_id, $vote, $trigger_type, $opposite_trigger) {
		$cookie 		= array();
		$opp_meta_key	= $opposite_trigger . '_count';	

		if (isset($_COOKIE[$opp_meta_key])) {
			
			$cookie = json_decode(stripslashes(sanitize_text_field($_COOKIE[$opp_meta_key])), true);

			if (in_array($post_id, $cookie)) {
				update_post_meta($post_id, $trigger_type, $vote);
				$vote = get_post_meta($post_id, $opposite_trigger, true);
				if (0 < $vote) {
					$vote--;	
				}
				update_post_meta($post_id, $opposite_trigger, $vote);

			} else {
				update_post_meta($post_id, $trigger_type, $vote);
			}

		} else {
			update_post_meta($post_id, $trigger_type, $vote);		
		}

		$helpful_votes 		= (int) get_post_meta($post_id, 'faq_num_helpful_votes', true);
		$not_helpful_votes 	= (int) get_post_meta($post_id, 'faq_num_not_helpful_votes', true);
		$total 				= $helpful_votes  + $not_helpful_votes;
		update_post_meta($post_id, 'faq_num_total_votes', $total);
		if (( !empty($total) || 0 != $total ) && ( !empty($helpful_votes) || 0 != $helpful_votes )) {
			$rate = ( $helpful_votes * 100 );
			$rate = ( $rate / $total );
			$per_rate = round($rate, 0, PHP_ROUND_HALF_UP);
			update_post_meta($post_id, 'faq_helpful_rate', $per_rate);
		} else {
			update_post_meta($post_id, 'faq_helpful_rate', 0);
		}	
	}
}
