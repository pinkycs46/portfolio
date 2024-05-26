<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
class AwSubscribePopupProPublic {
	public static function aw_subscribe_popup_pro() {
		/** Prepare which Subscribers Popups should be displayed **/
		$is_woocommerce_page = self::aw_is_realy_woocommerce_page();
		if (is_user_logged_in()) {
			$current_user = wp_get_current_user();
			global $wpdb;
			$table_name = $wpdb->prefix . 'popup_pro_subscribes';
			$email = $current_user->data->user_email;

			$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}popup_pro_subscribes WHERE email = %s", "{$email}"));

			if (count($results) != 0) {
				return;
			}
		}
		if (!is_admin() && !$is_woocommerce_page && !aw_popup_pro_is_subscribed()) {
			$args = array('post_type' => 'popup-pro-subscribe', 'posts_per_page' => - 1, 'post_status' => 'publish', 'orderby' => 'ID', 'order' => 'ASC');
			$loop = new WP_Query($args);
			if (!$loop->have_posts()) {
				return;
			}
			$popup = array();
			$popup_viewed_pages = sanitize_text_field('popup_pro_subscribers_cookie_viewed_pages');
			$popup_minutes_visit = sanitize_text_field('popup_pro_subscribers_cookie_minutes_visit');

			if (is_multisite()) {
				$current_site = get_current_blog_id();
				$popup_viewed_pages .= '_' . $current_site;
				$popup_minutes_visit.= '_' . $current_site;
			}

			if (isset($_COOKIE[$popup_minutes_visit])) {
				$time_diff = time() - sanitize_text_field($_COOKIE[$popup_minutes_visit]);
			} else {
				$time_diff = 0;
			}
			$min = floor($time_diff / 60);
			$viewed_pages = isset($_COOKIE[$popup_viewed_pages]) ? sanitize_text_field($_COOKIE[$popup_viewed_pages]) + 1 : 0;
			while ($loop->have_posts()) :
				$loop->the_post();
				$cookie_name = sanitize_text_field('popup_pro_subscriber_cookie_') . get_the_ID();
				/*
				1 - After X viewed pages
				2 - X minutes after the visit
				*/
				$type = get_post_meta(get_the_ID(), 'popup_pro_subscribe_when_display', true);
				$x_equalto = get_post_meta(get_the_ID(), 'popup_pro_subscribe_x_equalto', true);
				if (( 1 == $type && $viewed_pages >= $x_equalto ) || ( 2 == $type && $min >= $x_equalto )) {
					if (!isset($_COOKIE[$cookie_name])) {
						$popup[] = array('id' => get_the_ID(), 'title' => get_post_meta(get_the_ID(), 'popup_pro_subscribe_title', true), 'subtitle' => get_post_meta(get_the_ID(), 'popup_pro_subscribe_subtitle', true), 'button_text' => get_post_meta(get_the_ID(), 'popup_pro_subscribe_button', true), 'design' => get_post_meta(get_the_ID(), 'popup_pro_subscribe_design', true), 'type' => get_post_meta(get_the_ID(), 'popup_pro_subscribe_when_display', true), 'cookie_lifetime' => get_post_meta(get_the_ID(), 'popup_pro_subscribe_cookie_lifetime', true), 'x_equalto' => get_post_meta(get_the_ID(), 'popup_pro_subscribe_x_equalto', true), 'template' => get_post_meta(get_the_ID(), 'popup_pro_subscribe_template', true), 'template_width' => get_post_meta(get_the_ID(), 'popup_pro_subscribe_template_width', true));
					}
				}
			endwhile;
			wp_reset_query();
			$num_popup = count($popup);
			$show_popup = '';
			$scr = '';
			$i = 1;
			foreach ($popup as $popups) {
				$popup_type = $popups['type'];
				$post_id = $popups['id'];
				$title = $popups['title'];
				$width = $popups['template_width'];
				$type = $popups['type'];
				$x_equalto = $popups['x_equalto'];
				$cookie_lifetime = $popups['cookie_lifetime'];
				aw_popup_pro_subscribers_cookies($post_id, $type, $x_equalto, $cookie_lifetime);
				add_action('wp_footer', function () use ( $post_id, $popups) {
					//echo self::aw_show_tb($post_id, $popups);
					echo wp_kses(self::aw_show_tb($post_id, $popups), wp_kses_allowed_html('post'));
				});
				$font_family_url = '';
				$elements = array('title', 'subtitle', 'emailform', 'subscribebutton');
				$font_family = '';
				$designs = get_post_meta($post_id, 'popup_pro_subscribe_design', true);
				foreach ($elements as $element) {
					if (isset($designs[$element]['fontweight'])) {
						$font_family.= $designs[$element]['fontname'] . ':' . $designs[$element]['fontweight'] . '|';
						$font_family_url = 'https://fonts.googleapis.com/css?family=' . $font_family . '&display=swap';	
					}
				}
				wp_register_style('popupprosubspubcss_' . $post_id, esc_url($font_family_url), array(), '1.0' );
				wp_enqueue_style('popupprosubspubcss_' . $post_id);

				$tb_height = 530;
				$margin_top = ( ( $tb_height / 2 ) );
				$show_popup.= 'window.pop_' . $post_id . ' = function(){';
				$show_popup.= 'jQuery("html").addClass("noscroll");';
				$show_popup.= "setTimeout(function()
								{
									tb_show('" . $title . "', '#TB_inline?width=" . $width . '&inlineId=popup-pro-subscribe-' . $post_id . "',null);
									jQuery('#TB_title').remove();
									jQuery('#TB_ajaxContent').removeAttr('style').attr('style','width:" . $width . "');
									jQuery('#TB_window').css({width: " . $width . " + 'px', marginTop: -" . $margin_top . " + 'px', 'background-color': 'transparent'});
								}, 1000);";
				$show_popup.= '}';
				$show_popup.= "\n";
				$show_popup.= "\n";
				$i++;
			}
			add_action('wp_head', function () use ( $show_popup, $scr) {
				self::aw_add_tb($show_popup, $scr);
			});
		}
	}
	public static function aw_add_tb( $show_popup, $scr) {
		/** Add thickbox, wordpress way to open Subscribers Popup **/
		add_thickbox();
		?>
		<script language="javascript">
			jQuery(document).ready(function()
			{
				<?php echo wp_kses_post($show_popup . $scr); ?>
			});
		</script>
		<?php
	}
	public static function aw_show_tb( $post_id, $popup) {
		/** Display Subscriber Popup on page load **/
		$patterns = array();
		$replacements = array();

		$popup_pro_subscribe_views = (int) get_post_meta($post_id, 'popup_pro_subscribe_views', true);

		$popup_pro_subscribe_views++;
		update_post_meta($post_id, 'popup_pro_subscribe_views', $popup_pro_subscribe_views);
		require_once (plugin_dir_path(__FILE__) . 'aw-subscribe-popup-pro-templates.php');
		$template = $popup['template'];
		$aw_get_template = new AwSubscribePopupProTemplates();
		$aw_get_template_data = $aw_get_template->aw_get_templates_data($template);
		$data = $aw_get_template_data;
		$readonly = '';
		$name = '';
		$email = '';
		if (is_user_logged_in()) {
			$readonly = 'readonly';
			$current_user = wp_get_current_user();
			$email = $current_user->user_email;
			$first_name = get_user_meta($current_user->ID, 'first_name', true);
			$last_name = get_user_meta($current_user->ID, 'last_name', true);
			if ('' != $first_name && '' != $last_name ) {
				$name = $first_name . ' ' . $last_name;
			} else {
				$name = $current_user->user_nicename;
			}
		}
		?>
		<script language="javascript">
		function change_placeholder_color(target_class, color_choice) {
			jQuery("body").append("<style>" + target_class + "::placeholder{color:" +  color_choice + "};" 
			+ target_class + "::-webkit-input-placeholder{color:" +  color_choice + "};"
			+ target_class + "::-moz-placeholder{color:" +  color_choice + "};"
			+ target_class + ":-ms-input-placeholder{color:" +  color_choice + "};"
			+ target_class + ":-moz-placeholder{color:" +  color_choice + "};</style>");
		}
		</script>
		<?php
		$designs = get_post_meta($post_id, 'popup_pro_subscribe_design', true);
		$bg_clr = $designs['background']['backgroundcolor'];
		$img_src = '';
		$img_path = plugin_dir_url(__DIR__) . 'admin/templates/images/';
		$img_src = $designs['background']['backgroundimage'];
		$path = wp_get_upload_dir();
		$imagepath = explode('uploads', $designs['background']['backgroundimage']);
		$fullpath = '';
		if (isset($imagepath[1])) {
			$fullpath = $path['basedir'] . $imagepath[1];
		}
		$elements = array('title', 'subtitle', 'emailform', 'subscribebutton', 'closebutton', 'background');
		
		foreach ($elements as $element) {
			$weight = '';
			$font_style = 'normal';
			if (isset($designs[$element]['fontweight'])) {
				$font_weight = mb_substr($designs[$element]['fontweight'], 0, 3);
				$weight = 'font-weight:' . $font_weight;
				if (strlen($designs[$element]['fontweight']) > 3) {
					$font_style = 'italic';
				}
			}
			switch ($element) {
				case 'title':
					$title = get_post_meta($post_id, 'popup_pro_subscribe_title', true);
					$patterns[0] 	= '#<span class="popup">(.*?)</span>#';
					$replacements[0] = '<span class="popup">' . $title . '</span>';
					$patterns[1] 	= '/<span class="popup">/';
					$replacements[1] = '<span class="popup" style="font-size:' . $designs[$element]['fontsize'] . 'px;font-family:' . $designs[$element]['fontname'] . ';' . $weight . ';font-style:' . $font_style . ';color:#' . $designs[$element]['fontcolor'] . '">';
					if ('aw-popup-template01' == $template) {
						if ('' == $img_src && 'FFFFFF' == $bg_clr) {
							$replacements[1] = '<span class="popup" 	style="font-size:' . $designs[$element]['fontsize'] . 'px;font-family:' . $designs[$element]['fontname'] . ';' . $weight . ';font-style:' . $font_style . ';color:#' . $designs[$element]['fontcolor'] . ';background-color:#48CBDF;">';
						}
					}
					if ( 'aw-popup-template06' == $template) {
						if ('' != $img_src || 'FFFFFF' != $bg_clr ) {
							$replacements[1] = '<span class="popup" style="font-size:' . $designs[$element]['fontsize'] . 'px ;font-family:' . $designs[$element]['fontname'] . ';' . $weight . ';font-style:' . $font_style . ';color:#' . $designs[$element]['fontcolor'] . ';background: transparent;">';
						}
					}
					$data = preg_replace($patterns, $replacements, $data);
					break;
				case 'subtitle':
					$subtitle = get_post_meta($post_id, 'popup_pro_subscribe_subtitle', true);
					$patterns[0] = '#<p class="popup">(.*?)</p>#';
					$replacements[0] = '<p class="popup">' . $subtitle . '</p>';
					$patterns[1] = '/<p class="popup">/';
					$replacements[1] = '<p class="popup" style="font-size:' . $designs[$element]['fontsize'] . 'px;font-family:' . $designs[$element]['fontname'] . ';' . $weight . ';font-style:' . $font_style . ';color:#' . $designs[$element]['fontcolor'] . '">';
					if ('aw-popup-template06' == $template ) {
						if ('' != $img_src || 'FFFFFF' != $bg_clr) {
							$replacements[1] = '<p class="popup" style="font-size:' . $designs[$element]['fontsize'] . 'px;font-family:' . $designs[$element]['fontname'] . ';' . $weight . ';font-style:' . $font_style . ';color:#' . $designs[$element]['fontcolor'] . ';background: transparent;">';
							
						}
					}
					$data = preg_replace($patterns, $replacements, $data);
					break;
				case 'emailform':
					$patterns[0]='/<input type="text" class="input-txt1" placeholder="Name" name="popup-pro-subscribe-name" id="popup-pro-subscribe-name" value="">/';
					$replacements[0]='<input type="text" class="input-txt1" placeholder="Name" name="popup-pro-subscribe-name" id="popup-pro-subscribe-name" style="font-size:' . $designs[$element]['fontsize'] . 'px;font-family:' . $designs[$element]['fontname'] . ';' . $weight . ';font-style:' . $font_style . ';color:#' . $designs[$element]['fontcolor'] . '" value="' . $name . '" ' . $readonly . '>';
					$patterns[1] ='/<input type="text" class="input-txt1" placeholder="Email" name="popup-pro-subscribe-email" id="popup-pro-subscribe-email" value="">/';
					$replacements[1] = '<input type="text" class="input-txt1" placeholder="Email" name="popup-pro-subscribe-email" id="popup-pro-subscribe-email"  style="font-size:' . $designs[$element]['fontsize'] . 'px;font-family:' . $designs[$element]['fontname'] . ';' . $weight . ';font-style:' . $font_style . ';color:#' . $designs[$element]['fontcolor'] . '" value="' . $email . '" ' . $readonly . '>';
					$data = preg_replace($patterns, $replacements, $data);
					?>
				<script language="javascript">
				var font_color = '<?php echo esc_html($designs[$element]['fontcolor']); ?>';
				change_placeholder_color('.input-txt1', '#'+font_color);
				</script>
					<?php
					break;
				case 'subscribebutton':
					$patterns[0] = '/class="submit-btn"/';
					$replacements[0] = 'class="submit-btn" style="font-size:' . $designs[$element]['fontsize'] . 'px;font-family:' . $designs[$element]['fontname'] . ';' . $weight . ';font-style:' . $font_style . ';color:#' . $designs[$element]['fontcolor'] . ';border-radius:' . $designs[$element]['borderradius'] . 'px;background-color:#' . $designs[$element]['buttoncolor'] . '"';
					$patterns[1] = '/class="btn-arw"/';
					$replacements[1] = 'class="btn-arw" style="background-color:#' . $designs[$element]['buttoncolor'] . '"';
					$data = preg_replace($patterns, $replacements, $data);
					$btn_txt = get_post_meta($post_id, 'popup_pro_subscribe_button', true);
					$data = str_replace('<span id="popup_pro_subscribe">Subscribe</span>', '<span id="popup_pro_subscribe">' . $btn_txt . '</span>', $data);
					break;
				case 'closebutton':
					$patterns[0] = '/<span class="popup_cls">/';
					$replacements[0] = '<span class="popup_cls" style="width:' . $designs[$element]['buttonsize'] . 'px; fill:#' . $designs[$element]['buttoncolor'] . '; color:#' . $designs[$element]['buttoncolor'] . '; display: inline-block;" onclick="return close_tb(this);">';
					$data = preg_replace($patterns, $replacements, $data);
					break;
				case 'background':
					switch ($template) {
						case 'aw-popup-template01':
							$bg_active = 'bg_active';
							if (isset($img_src) && '' != $img_src  && file_exists($fullpath)) {
								$data = str_replace($img_path . 'aw_circle_blue.png', $img_src, $data);
								$bg_active = '';
							}
							if ('FFFFFF' != $bg_clr) {
								$patterns[0] = '/<div class="popup-main subscribe-one">/';
								$replacements[0] = '<div class="popup-main subscribe-one ' . $bg_active . '" style="background-repeat:no-repeat;background-color:#' . $bg_clr . ';">';
								$data = preg_replace($patterns, $replacements, $data);
							}
							if ( 'FFFFFF' == $bg_clr) {
								$patterns[0] = '/<div class="popup-main subscribe-one">/';
								$replacements[0] = '<div class="popup-main subscribe-one" style="background-repeat:no-repeat;background-color:#FFFFFF;">';
								$data = preg_replace($patterns, $replacements, $data);
							}
							break;
						case 'aw-popup-template02':
							if ('FFFFFF' != $bg_clr || isset($img_src) &&  '' != $img_src && file_exists($fullpath)) {
								$data = str_replace('<img src="' . $img_path . 'aw-subscribe-txt.png" id="subscrib_imag_t">', '', $data);
								$patterns[0] = '/<div class="popup-main subscribe-two clearfix" id="popupfullbackground">/';
								$replacements[0] = '<div class="popup-main subscribe-two clearfix" id="popupfullbackground" style="background-image:url(' . $img_src . ');background-repeat:no-repeat;background-color:#' . $bg_clr . ';">';
								$data = preg_replace($patterns, $replacements, $data);
							}
							break;
						case 'aw-popup-template03':
							$bg_active = 'bg_active';
							if (isset($img_src) && '' != $img_src && file_exists($fullpath)) {
								$data = str_replace($img_path . 'aw_template_3.png', $img_src, $data);
								$bg_active = '';
							}
							if ( 'FFFFFF' != $bg_clr ) {
								$patterns[0] = '/<div class="popup-main subscribe-three">/';
								$replacements[0] = '<div class="popup-main subscribe-three ' . $bg_active . '" style="background-color:#' . $bg_clr . ';">';
								$data = preg_replace($patterns, $replacements, $data);
								$patterns[1] = '/<div class="close-pop1">/';
								$replacements[1] = '<div class="close-pop1" style="background-color:#' . $bg_clr . ';">';
								$data = preg_replace($patterns, $replacements, $data);
							}
							break;
						case 'aw-popup-template04':
							$bg_active = 'bg_active';
							if (isset($img_src) && '' != $img_src && file_exists($fullpath)) {
								$data = str_replace($img_path . 'aw-up-slide.png', $img_src, $data);
								$bg_active = '';
							}
							if ('F1F1F1' != $bg_clr) {
								$patterns[0] = '/<div class="popup-main subscribe-four">/';
								$replacements[0] = '<div class="popup-main subscribe-four ' . $bg_active . '" style="background-color:#' . $bg_clr . ';">';
								$data = preg_replace($patterns, $replacements, $data);
							}
							break;
						case 'aw-popup-template05':
							if ('FFFFFF' != $bg_clr) {
								$patterns[0] = '/<div class="popup-main subscribe-five" id="popupfullbackground">/';
								$replacements[0] = '<div class="popup-main subscribe-five" id="popupfullbackground" style="background-color:#' . $bg_clr . ';">';
								$data = preg_replace($patterns, $replacements, $data);
							}
							if (isset($img_src) && '' != $img_src && file_exists($fullpath)) {
								if ('FFFFFF' != $bg_clr) {
									$patterns[0] = '/<div class="popup-main subscribe-five" id="popupfullbackground" style="background-color:#' . $bg_clr . ';">/';
									$replacements[0] = '<div class="popup-main subscribe-five" id="popupfullbackground" style="background-color:#' . $bg_clr . ';background-image:url(' . $img_src . ');background-repeat:no-repeat;">';
									$data = preg_replace($patterns, $replacements, $data);
								}
								$patterns[1] = '/<div class="popup-main subscribe-five" id="popupfullbackground">/';
								$replacements[1] = '<div class="popup-main subscribe-five" id="popupfullbackground" style="background-image:url(' . $img_src . ');background-repeat:no-repeat;">';
								$data = preg_replace($patterns, $replacements, $data);
							}
							break;
						case 'aw-popup-template06':
							$bg_active = 'bg_active';
							if ('FFFFFF' != $bg_clr || isset($img_src) && '' != $img_src && file_exists($fullpath)) {
								$patterns[0] = '/<div class="popup-main subscribe-six">/';
								$replacements[0] = '<div class="popup-main subscribe-six ' . $bg_active . '" style="background-repeat:no-repeat;background-color:#' . $bg_clr . ';">';
								$data = preg_replace($patterns, $replacements, $data);
							}
							if (isset($img_src) && '' != $img_src && file_exists($fullpath)) {
								$patterns[0] = '/<div class="header-title" id="popupfullbackground">/';
								$replacements[0] = '<div class="header-title" id="popupfullbackground" style="background-image:url(' . $img_src . ');background-repeat:no-repeat;">';
								$data = preg_replace($patterns, $replacements, $data);
							}
							break;
						case 'aw-popup-template07':
							$bg_active = 'bg_active';
							if (isset($img_src) && '' != $img_src && file_exists($fullpath)) {
								$data = str_replace($img_path . 'aw-subscribe-img-seven.jpg', $img_src, $data);
								$bg_active = '';
							}
							if ('FFFFFF' != $bg_clr) {
								$patterns[0] = '/<div class="popup-main subscribe-seven">/';
								$replacements[0] = '<div class="popup-main subscribe-seven ' . $bg_active . '" style="background-color:#' . $bg_clr . ';">';
								$data = preg_replace($patterns, $replacements, $data);
							}
							break;
					}
					break;
			}
		}

		$html = '';
		$html.= '<div class="popup-pro-show-tb" id="popup-pro-subscribe-' . $post_id . '" style="display:none;">';
		$html.= '<div>';
		$html.= $data;
		$html.= '</div>';
		$html.= '<input type="hidden" name="tb_unload_count_' . $post_id . '" id="tb_unload_count_' . $post_id . '" value="show">';
		$html.= '<input type="hidden" name="popup-pro-subscribe-post-id" id="popup-pro-subscribe-post-id" value="' . $post_id . '"><input type="hidden" name="allpopup[]" class="current_popup_id" value="subscribe::pop_' . $post_id . ':: 0 ::' . $post_id . '">
		</div>';
		//echo $html;
		return $html;
	}
	public static function aw_popup_pro_add_subscriber() {
		/** Add Subscriber to Poup Pro Plugin and Mailchimp (if Mailchimp is installed, active and configured correctly) **/
		$msg = '';
		global $wpdb;
		$table_name = $wpdb->prefix . 'popup_pro_subscribes';
		//$path = parse_url(get_option('siteurl'), PHP_URL_PATH);
		$path = '/';
		$host = parse_url(get_option('siteurl'), PHP_URL_HOST);
		if (isset($_GET['email'])) {
			//$query = 'SELECT * FROM `' . $table_name . '` WHERE `email` = "' .  . '"';
			$sanitize_email = sanitize_email($_GET['email']);
			$results 		= $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}popup_pro_subscribes WHERE email = %s", "{$sanitize_email}"));
		}
		//$results = $wpdb->get_results($query);
		
		$emailid	= '';
		$postid 	= '';
		$name 		= '';	 
		if (isset($_GET['email'])) {
			$emailid = sanitize_email($_GET['email']);
		}
		if (isset($_GET['post_id'])) {
			$postid = sanitize_text_field($_GET['post_id']);
		}
		if (isset($_GET['name'])) {
			$name = sanitize_text_field($_GET['name']);
		}
		if (count($results) == 0) {
			$result_check = $wpdb->insert($table_name, array('name' => sanitize_text_field($_GET['name']), 'email' => $emailid, 'post_id' => $postid, 'date' => gmdate('Y-m-d h:y:s')));
			if ($result_check) {
				$popup_pro_subscribe_clicks = get_post_meta($postid, 'popup_pro_subscribe_clicks', true);
				//$popup_pro_subscribe_clicks = $popup_pro_subscribe_clicks + 1;
				$popup_pro_subscribe_clicks++;
				update_post_meta($postid, 'popup_pro_subscribe_clicks', $popup_pro_subscribe_clicks);
				$msg = 'Thank you.';
				$is_subscribed = sanitize_text_field('aw_popup_pro_is_subscribed');
				if (!isset($_COOKIE[$is_subscribed])) {
					setcookie($is_subscribed, 'Yes', time() + ( 6 * 30 * 24 * 3600 ), $path, $host);
				}
				if (get_option('mailchimp-woocommerce-cached-api-lists') && get_option('mailchimp-woocommerce')) {
					$mailchimpkey = get_option('mailchimp-woocommerce');
					$mailchimplistid = get_option('mailchimp-woocommerce-cached-api-lists');
					$apikey = $mailchimpkey['mailchimp_api_key'];
					$listid = unserialize($mailchimplistid);
					$list_id = array_keys($listid['value']);
					$result = self::aw_mailchimp_subscriber_status($emailid, 'subscribed', $list_id[0], $apikey, array('FNAME' => $name));
				}
			} else {
				$msg = 'Something went wrong. Try again later';
			}
		}
		//echo $msg;
		echo wp_kses($msg, wp_kses_allowed_html('post'));
		wp_die();
	}
	public static function aw_mailchimp_subscriber_status( $email, $status, $list_id, $api_key, $merge_fields = array('FNAME' => 'John', 'LNAME' => 'Doe')) {
		/** Mailchimp API Call, to add Subscriber on Mailchimp Account **/
		$data = array('apikey' => $api_key, 'email_address' => $email, 'status' => $status, 'merge_fields' => $merge_fields);

		$mch_api = curl_init(); // initialize cURL connection
		curl_setopt($mch_api, CURLOPT_URL, 'https://' . substr($api_key, strpos($api_key, '-')+1) . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . md5(strtolower($data['email_address'])));
		curl_setopt($mch_api, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . base64_encode('user:' . $api_key)));
		curl_setopt($mch_api, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
		curl_setopt($mch_api, CURLOPT_RETURNTRANSFER, true); // return the API response
		curl_setopt($mch_api, CURLOPT_CUSTOMREQUEST, 'PUT'); // method PUT
		curl_setopt($mch_api, CURLOPT_TIMEOUT, 10);
		curl_setopt($mch_api, CURLOPT_POST, true);
		curl_setopt($mch_api, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($mch_api, CURLOPT_POSTFIELDS, json_encode($data)); // send data in json
		$result = curl_exec($mch_api);
		return $result;
	}
	public static function aw_is_realy_woocommerce_page() {
		return false;
		/** Function to check if visitor is on any kind of WooCommerce Page **/
		if (function_exists('is_woocommerce') && is_woocommerce()) {
			return true;
		}
		$woocommerce_keys = array('woocommerce_shop_page_id', 'woocommerce_cart_page_id', 'woocommerce_checkout_page_id',);
		foreach ($woocommerce_keys as $wc_page_id) {
			if (get_the_ID() == get_option($wc_page_id, 0)) {
				return true;
			}
		}
		return false;
	}
}
?>
