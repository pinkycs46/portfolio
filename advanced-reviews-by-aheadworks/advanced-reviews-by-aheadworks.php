<?php
/**
 * Plugin Name: Advanced Reviews By Aheadworks
 * Plugin URI: https://www.aheadworks.com/
 * Description: Advanced Reviews for WooCommerce encourage customers to share their purchase experience by making the review submission process clear and simple.
 * Author: Aheadworks
 * Author URI: https://www.aheadworks.com/
 * Version: 1.0.0
 * Woo: 7772111:9338fcf688b649b4db1d81a888bedcd7
 * Text Domain: advanced-reviews-by-aheadworks
 *
 * @package advanced-reviews-by-aheadworks
 *
 * Requires at least: 5.3.6
 * Tested up to: 5.7.0
 * WC requires at least: 4.3.3
 * WC tested up to: 5.1.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

require_once(plugin_dir_path(__FILE__) . 'includes/aw-ar-advanced-review-admin.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-ar-advanced-review-tab.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-ar-advanced-review-myreview.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-ar-advanced-review-allreview.php');

$awadvancedreview = new AwAdvancedReview();

define('ADMIN_EMAIL', 'Admin Email');
define('CUSTOMER_REVIEW_EMAIL', 'Customer Review Email');
define('ABUSE_REPORT_EMAIL', 'Abuse Report Email');
define('CRITICAL_REPORT_EMAIL', 'Critical Report Email');
define('REVIEW_REMINDER_EMAIL', 'Review Reminder Email');
define('REVIEW_APPROVAL_EMAIL', 'Review Approval Email');
define('NEW_COMMENT_ON_CUST_REVIEW_EMAIL', 'New Comment on Customer Review Email');

/** Present plugin version **/
define( 'AW_AR_ADVANCED_REVIEW_VERSION', '1.0.0' );

class AwAdvancedReview {
	public function __construct() {
		/** Constructor function, initialize and register hooks **/
		add_action('admin_init', array(get_called_class(),'aw_ar_advanced_review_installer'));
		register_uninstall_hook(__FILE__, array(get_called_class(), 'aw_ar_advanced_review_unistaller'));
		//register_deactivation_hook( __FILE__ , array(get_called_class(),'aw_ar_advanced_review_deactivated'));

		add_filter('wp_kses_allowed_html', 'aw_ar_kses_filter_allowed_html', 10, 2);

		/* Admin Javascript files */
		add_action('admin_enqueue_scripts', array(get_called_class(),'aw_ar_advanced_review_admin_addScript'));
		add_action('wp_enqueue_scripts', array(get_called_class(),'aw_ar_advanced_review_public_addScript'));
		add_action( 'woocommerce_payment_completed', array(get_called_class(),'aw_ar_order_status_completed') );

	} // constructor close

	public static function aw_ar_advanced_review_admin_addScript() {
		$path 	= parse_url(get_option('siteurl'), PHP_URL_PATH);
		$host 	= parse_url(get_option('siteurl'), PHP_URL_HOST);
		$nonce 	= wp_create_nonce('rdadvancedreview_admin_nonce');
		$page 	= '';

		if (isset($_GET['page'])) {
			$page = sanitize_text_field($_GET['page']);
		}

		wp_register_style('awadvancedreviewadmincss', plugins_url('/admin/css/aw-ar-advanced-review.css', __FILE__ ), array(), '1.0' );
		wp_enqueue_style('awadvancedreviewadmincss');

		wp_register_style('lightboxadmincss', plugins_url('/admin/css/jquery.lightbox.css', __FILE__ ), array(), '1.0' );
		wp_enqueue_style('lightboxadmincss');

		wp_register_script('awadvancedreviewadminjs', plugins_url('/admin/js/aw-ar-advanced-review-admin.js', __FILE__ ), array(), '1.0' );

		$order_js_var = array('site_url' => get_option('siteurl'), 'ajax_url'=>admin_url('admin-ajax.php'), 'path' => $path, 'host' => $host, 'aw_ar_admin_nonce' => $nonce );
		wp_localize_script('awadvancedreviewadminjs', 'rd_admin_js_var', $order_js_var);

		wp_register_script('awadvancedreviewadminjs', plugins_url('/admin/js/aw-ar-advanced-review-admin.js', __FILE__ ), array(), '1.0' );
		wp_enqueue_script('awadvancedreviewadminjs');

		wp_register_script('lightboxadminjs', plugins_url('/admin/js/jquery.lightbox.js', __FILE__ ), array('jquery'), '1.0' , 100);
		wp_enqueue_script('lightboxadminjs');
	}

	public static function aw_ar_advanced_review_public_addScript() {

		$path 	= parse_url(get_option('siteurl'), PHP_URL_PATH);
		$host 	= parse_url(get_option('siteurl'), PHP_URL_HOST);
		$arnonce= wp_create_nonce('rdadvancedrewiew_nonce');

		/** Add Plugin CSS and JS files Public Side**/
		wp_register_style('advancedrewiewpubliccss', plugins_url('/public/css/aw-ar-advanced-review.css', __FILE__ ), array(), '1.0' );
		wp_enqueue_style('advancedrewiewpubliccss');

		wp_register_style('lightboxpubliccss', plugins_url('/public/css/jquery.lightbox.css', __FILE__ ), array(), '1.0' );
		wp_enqueue_style('lightboxpubliccss');

		wp_register_script('advancedrewiewpublicjs', plugins_url('/public/js/aw-ar-advanced-review-public.js', __FILE__ ), array('jquery'), '1.0' );

		$aw_ar_js_var = array('site_url' => get_option('siteurl'), 'path' => $path, 'host' => $host, 'aw_ar_front_nonce' => $arnonce);
		wp_localize_script('advancedrewiewpublicjs', 'js_aw_ar_var', $aw_ar_js_var);
		wp_register_script('advancedrewiewpublicjs', plugins_url('/public/js/aw-ar-advanced-review-public.js', __FILE__ ), array('jquery'), '1.0' );
		wp_enqueue_script('advancedrewiewpublicjs');
		
		wp_register_script('lightboxpublicjs', plugins_url('/public/js/jquery.lightbox.js', __FILE__ ), array('jquery'), '1.0' , 10);
		wp_enqueue_script('lightboxpublicjs');
	}

	/*public static function aw_ar_advanced_review_deactivated() {
		global $wpdb;
		$db_comment_table = $wpdb->prefix . 'comments';
		$products = $wpdb->get_results($wpdb->prepare("SELECT comment_ID, comment_approved , comment_type  FROM {$wpdb->prefix}comments WHERE comment_type= %s ", 'advanced_review'));
		if (!empty($products)) {
			foreach ($products as $product) {
				$array = array('comment_approved' => $product->comment_approved . '_' . $product->comment_type);
				$wpdb->update($db_comment_table, $array, array('comment_ID'=>$product->comment_ID));
			}
		}
	}*/

	public static function aw_ar_advanced_review_installer() {

		if (is_admin()) {
			if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
				/** If WooCommerce plugin is not activated show notice **/
				add_action('admin_notices', array('AwAdvancedReviewAdmin','aw_ar_self_deactivate_notice'));
				/** Deactivate our plugin **/
				deactivate_plugins(plugin_basename(__FILE__));
				if (isset($_GET['activate'])) {
					unset($_GET['activate']);
				}
			} else {
				global $wpdb;
				add_rewrite_endpoint( 'aw-ar-myreview', EP_ROOT | EP_PAGES  );
				add_rewrite_endpoint( 'aw-ar-allreview', EP_ROOT | EP_PAGES  );
				$db_comment_table = $wpdb->prefix . 'comments'; 
				$type_arr 		= array();
				$typr_string 	= implode(',', $type_arr);

				$products = $wpdb->get_results($wpdb->prepare("SELECT comment_ID, comment_approved , comment_type  FROM {$wpdb->prefix}comments WHERE comment_approved IN ( %s, %s, %s, %s ) ", '1_q_and_a' , '0_q_and_a' , 'spam_q_and_a', 'trash_q_and_a'));
				if (!empty($products)) {
					foreach ($products as $product) {

						$replace = str_replace('_' . $product->comment_type , '' , $product->comment_approved);
						$array = array(
										'comment_approved' => $replace
									 );
						$wpdb->update($db_comment_table, $array, array('comment_ID'=>$product->comment_ID));
					}
				}

				flush_rewrite_rules();
				wp_deregister_script( 'autosave' );

				$exist = get_option( 'advanced_review_by_aheadwork');
				if (!$exist) {
					add_option('aw_ar_admin_caption', 'Store Manager');
					add_option('aw_ar_isattach_file', 'yes');
					add_option('aw_ar_whoaccept', 'guest');
					add_option('aw_ar_enable_pronandcons', 'yes');
					add_option('aw_ar_meta_description', 'Read product reviews by real customers');
					add_option('aw_ar_allowfile_extensions', 'jpg,jpeg');
					add_option('aw_ar_reviewpage_endppoint', 'reviews');
					add_option('aw_ar_max_filesize', '2');
					add_option('aw_ar_enable_termcondition', 'yes');
					//add_option('aw_ar_guestreview', 'required');
					add_option('aw_ar_setting_cookie_days', 30);
					update_option('AW_AR_ADVANCED_REVIEW_VERSION', AW_AR_ADVANCED_REVIEW_VERSION );

					$charset_collate 		= $wpdb->get_charset_collate();
					$db_aw_pq_codes_table	= $wpdb->prefix . 'aw_ar_email_templates';
					//Check to see if the table exists already, if not, then create it
					if ($wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ' , "{$wpdb->prefix}aw_ar_email_templates")) != $db_aw_pq_codes_table) {
						$sql = "CREATE TABLE {$wpdb->prefix}aw_ar_email_templates (
									`id` int(10) NOT NULL AUTO_INCREMENT,
									`email` text NOT NULL,
									`email_type` varchar(55) NOT NULL,
									`recipients` text NOT NULL,
									`active` int(2) NOT NULL,
									`subject` text NOT NULL,
									`email_heading` text NOT NULL,
									`additional_content` text NOT NULL,
									PRIMARY KEY (`id`)
								);"	;
						require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
						dbDelta($sql);
						$users = get_users( array( 'role' => 'Administrator' ) );
						if ( ! empty( $users ) ) {
							$admin_emails = implode(',', wp_list_pluck( $users, 'user_email' ));
						}
						$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}aw_ar_email_templates
						(email, email_type, recipients, active, subject, email_heading, additional_content)
						VALUES

						('Admin Email', 'text/html', %s , 1, 'New Review','New Review', 'Hello,{admin}
 Someone has posted a review {link to the review on the backend} for {product_name}.'),

						('Customer Review Email', 'text/html', 'customer', 1, 'Your review is pending approval', 'Your review is pending approval', 'Hello, {customer_name}
						Your review about {product_name} has been accepted for moderation.
We will let you know about any updates.'),

						('Abuse Report Email', 'text/html',  %s, 1 ,'Abuse report on review about {product_name}', 'Abusement report review about {product_name}', 'Hello, {admin} 
						Someone added a abusment to review about {product_name}'),

						('Critical Report Email', 'text/html',  %s, 1 ,'Critical report on review about {product_name}', 'Critical review report about {product_name}', 'Hello, {admin}
Critical report of review about {product_name}'),

						('Review Reminder Email', 'text/html',  'customer', 1 ,'Reminder email of review on {product_name}', 'Reminder email of review on {product_name}', 'Hello, {customer_name}
Reminder email to write review on {product_name}'),

						('Review Approval Email', 'text/html',  'customer', 1 ,'Review approved on {product_name}', 'Review approved on {product_name}', 'Hello, {customer_name}
your review about {product_name} have been approved'),

						('New Comment on Customer Review Email', 'text/html',  'customer', 1 ,'New comment of review on {product_name}', 'New comment of review on {product_name}', 'Hello, {customer_name}
new comment received on your review about {product_name}')", "{$admin_emails}", "{$admin_emails}", "{$admin_emails}"));

					}
					update_option( 'advanced_review_by_aheadwork', 'completed' );
				}
			}
		}
	}

	public static function aw_ar_advanced_review_unistaller() {
		/* Perform required operations at time of plugin uninstallation */
		global $wpdb;

		$pluginDefinedOptions = array('aw_ar_setting_review_enable', 'aw_ar_setting_helpful_enable', 'aw_ar_setting_cookie_days'); // etc
		foreach ($pluginDefinedOptions as $optionName) {
			delete_option($optionName);
		}

		$reviewpage_id = update_option('aw_ar_allreviewpage_id', $post_id);
		wp_delete_post($reviewpage_id);

		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aw_ar_email_templates");

		delete_option('aw_ar_admin_caption');
		delete_option('aw_ar_isattach_file');
		delete_option('aw_ar_whoaccept');
		delete_option('aw_ar_enable_pronandcons');
		delete_option('aw_ar_meta_description');
		delete_option('aw_ar_allowfile_extensions');
		delete_option('aw_ar_reviewpage_endppoint');
		delete_option('aw_ar_max_filesize');
		delete_option('aw_ar_enable_termcondition');
		delete_option('aw_ar_setting_cookie_days');
		delete_option('advanced_review_by_aheadwork');
		delete_option('AW_AR_ADVANCED_REVIEW_VERSION');
		delete_option('aw_ar_allreviewpage_id');
	}

	public static function aw_ar_order_status_completed( $order_id) {
		$product_id = 0;
		$order 		= wc_get_order($order_id);
		$user_id   	= $order->get_user_id();
		foreach ($order->get_items() as $order_key => $order_value) {
			$product_id = $order_value->get_product_id();
		  
		}
		if ( 0 == $user_id) {
			$first_name = get_post_meta($order_id, '_billing_first_name', true);
			$last_name 	= get_post_meta($order_id, '_billing_last_name', true);
			$user_name 	= $first_name . ' ' . $last_name;
			$user_email = get_post_meta($order_id, '_billing_email', true);
		}
		$is_mail_sent = get_post_meta($order_id, 'aw_ar-reminder-mail-sent', true);
		if (!$is_mail_sent) {
			$is_admin_enabled = aw_ar_get_email_template_active_status('Customer Review Email');
			if ($is_admin_enabled ) {
				$user_id = get_current_user_id();
				if ($user_id) {
					$is_user_enabled = get_user_meta($user_id, 'is_ar_reminder_email_enabled', true);	
					if ('yes'===$is_user_enabled) {
						aw_ar_send_mail_after_product_order($order_id, $product_id, $user_id, $user_name, $user_email, 'Review Reminder Email');		
					}
				}
			}		
		}
	}
	
} //class close

function aw_ar_get_settings_options() {

	$aw_ar_get_settings_options = array();

	$aw_ar_get_settings_options['aw_ar_admin_caption'] 			= get_option('aw_ar_admin_caption');
	$aw_ar_get_settings_options['aw_ar_isattach_file'] 			= get_option('aw_ar_isattach_file');
	$aw_ar_get_settings_options['aw_ar_whoaccept'] 				= get_option('aw_ar_whoaccept');
	$aw_ar_get_settings_options['aw_ar_enable_pronandcons'] 	= get_option('aw_ar_enable_pronandcons');
	$aw_ar_get_settings_options['aw_ar_meta_description'] 		= get_option('aw_ar_meta_description');
	$aw_ar_get_settings_options['aw_ar_allowfile_extensions'] 	= get_option('aw_ar_allowfile_extensions');
	$aw_ar_get_settings_options['aw_ar_reviewpage_endppoint'] 	= get_option('aw_ar_reviewpage_endppoint');
	$aw_ar_get_settings_options['aw_ar_max_filesize'] 			= get_option('aw_ar_max_filesize');
	$aw_ar_get_settings_options['aw_ar_enable_termcondition'] 	= get_option('aw_ar_enable_termcondition');
	//$aw_ar_get_settings_options['aw_ar_guestreview'] 			= get_option('aw_ar_guestreview');
	$aw_ar_get_settings_options['aw_ar_setting_cookie_days'] 	= get_option('aw_ar_setting_cookie_days');

	return json_decode(json_encode($aw_ar_get_settings_options), false);
}

function aw_ar_get_product_review_enable() {
	$product_id = get_the_ID();
	return comments_open($product_id);
}

function aw_ar_get_comment_type_count( $status = '', $user_id = '') {
	global $wpdb;
	if ('' != $status) {
		if ('mine'== $status) {
			$total = $wpdb->get_var($wpdb->prepare("SELECT COUNT(comment_ID) FROM {$wpdb->prefix}comments WHERE comment_type = %s AND comment_approved != %s AND  comment_approved != %s AND user_id = %d", 'review' , 'trash', 'spam', $user_id ));
		} else {
			$total = $wpdb->get_var($wpdb->prepare("SELECT COUNT(comment_ID) FROM {$wpdb->prefix}comments WHERE comment_type = %s AND comment_approved = %s", 'review' , "{$status}" ));	
		}
		
	} else {
		$total = $wpdb->get_var($wpdb->prepare("SELECT COUNT(comment_ID) FROM {$wpdb->prefix}comments WHERE comment_type = %s AND comment_approved != %s AND comment_approved != %s", 'review', 'trash', 'spam'));
	}
	return $total;
}
 
function aw_ar_get_email_template_setting_results() {
	global $wpdb;
	$emails_template = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_ar_email_templates WHERE 1 = %d ", 1 ) );
	return $emails_template;
}

function aw_ar_get_email_template_setting_row( $id) {
	global $wpdb;
	$emails_template = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_ar_email_templates WHERE id = %d ", "{$id}") );
	return $emails_template;
}

function aw_ar_count_recommend_review() {
	global $wpdb;
	$emails_template = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_ar_comments   " ) );
	return $emails_template;
}

function aw_ar_multiimage_upload( $files, $final_files = '' ) {
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );
	$attached_images = array();
	$final_attach_img= array();
	if (!empty($files)) {
		$aw_ar_max_filesize = get_option('aw_ar_max_filesize');
		foreach ($files['name'] as $key => $value) {
			if ($files['name'][$key]) {
				$file = array(
					'name' => $files['name'][$key],
					'type' => $files['type'][$key],
					'tmp_name' => $files['tmp_name'][$key],
					'error' => $files['error'][$key],
					'size' => $files['size'][$key]
				);
				if ('' != $final_files) {
					$final_attach_img = explode(',', $final_files);	
					if (in_array($file['name'], $final_attach_img) && $file['size'] > 0) {
							$_FILES = array('aw_ar_file' => $file);
							$attachment_id = media_handle_upload('aw_ar_file', 0);
							$attached_images[] = $attachment_id;
						if (is_wp_error($attachment_id)) {
							echo 'Error adding file';
						}
					}
				} else {
					$_FILES = array('upload_file' => $file);
					$attachment_id = media_handle_upload('upload_file', 0);
					$attached_images[] = $attachment_id;
					if (is_wp_error($attachment_id)) {
						echo 'Error adding file';
					}
				}
				
			}
		}
	}
	return $attached_images;
}


function aw_ar_get_voting_abuse_section( $comment_id, $product_id, $user_id = '', $comment_approved = '', $comment_type = '') {
	global $wpdb;
	global $product;

	$like_dislike_count	= array();
	$image_url 			= array();
	$html 				= '';
	$abuse_image_url 	= '';
	$meta_key			= 'aw_ar_abusement-' . $comment_id;
	if (wc_review_ratings_enabled()) {
		
		$like_dislike_count['aw_ar_helpful'] 		= 0;
		$like_dislike_count['aw_ar_not_helpful'] 	= 0;
		$like_dislike_count['aw_ar_helpful'] = get_comment_meta($comment_id, 'aw_ar_helpful' , true) ? get_comment_meta($comment_id, 'aw_ar_helpful' , true) : 0;
		if ($like_dislike_count['aw_ar_helpful']>0) {
			$image_url['aw_ar_helpful']		= plugins_url('/advanced-reviews-by-aheadworks/admin/images/Thumb-icon-aw_ar_helpful.png', __DIR__);
		} else {
			$image_url['aw_ar_helpful']		= plugins_url('/advanced-reviews-by-aheadworks/admin/images/Thumb-icon-default-aw_ar_helpful.png', __DIR__);
		}

		$like_dislike_count['aw_ar_not_helpful'] = get_comment_meta($comment_id, 'aw_ar_not_helpful' , true) ? get_comment_meta($comment_id, 'aw_ar_not_helpful' , true) : 0;
		if ($like_dislike_count['aw_ar_not_helpful']>0) {
			$image_url['aw_ar_not_helpful']	= plugins_url('/advanced-reviews-by-aheadworks/admin/images/Thumb-icon-aw_ar_not_helpful.png', __DIR__);
		} else {
			$image_url['aw_ar_not_helpful']	= plugins_url('/advanced-reviews-by-aheadworks/admin/images/Thumb-icon-default-aw_ar_not_helpful.png', __DIR__);
		}	

		if (0 != $like_dislike_count['aw_ar_not_helpful'] ) {
			//$like_dislike_count['aw_ar_not_helpful'] = '-' . $like_dislike_count['aw_ar_not_helpful'];
			$like_dislike_count['aw_ar_not_helpful'] =  $like_dislike_count['aw_ar_not_helpful'];
		}		

		if ('1' == $comment_approved && 'review' == $comment_type) {
			$html= '<div class="thumbs-rate" id="loadersection-' . $comment_id . '">
					<div class="thumbs-images">
						<img src="' . $image_url['aw_ar_helpful'] . '" id="aw_ar_helpful-' . $comment_id . '" class="aw_ar_like_dislike_img" data-trigger-type="aw_ar_helpful"  data-review-id="' . $comment_id . '" data-user-id="' . $user_id . '" />
						<span id="helpfulcount-' . $comment_id . '">' . $like_dislike_count['aw_ar_helpful'] . '</span>
					</div>
					<div class="thumbs-images">
						<img src="' . $image_url['aw_ar_not_helpful'] . '" id="aw_ar_not_helpful-' . $comment_id . '" class="aw_ar_like_dislike_img" data-trigger-type="aw_ar_not_helpful" data-review-id="' . $comment_id . '" data-product-id="' . $product_id . '"  data-user-id="' . $user_id . '"/>
						<span id="nothelpfulcount-' . $comment_id . '"> ' . $like_dislike_count['aw_ar_not_helpful'] . '</span>
					</div>';
			if (!isset($_COOKIE[$meta_key])  && !empty($comment_id)) {
				$abuse_image_url = plugins_url('/advanced-reviews-by-aheadworks/admin/images/abuse_default.png', __DIR__);
				$html.= '<div>
							<img src="' . $abuse_image_url . '" class="aw_ar_abuse_img aw_ar_abuseimge-' . $comment_id . '" id="' . $comment_id . '" alt="abusement" />	
						</div>';
				aw_ar_abusement_popup( $comment_id, $product_id);
			}		
			$html.=	'</div>';
			
		}		
	}			
	return $html;
}

function aw_ar_abusement_popup( $comment_id, $product_id, $user_id = '', $comment_approved = '', $comment_type = '') {
	if (''==$user_id) {
		$user_id = 0;
	}
	?>
	<div id="aw_ar_abuse_Modal-<?php echo wp_kses($comment_id, wp_kses_allowed_html('post')); ?>" class="aw_ar_abuse_modal">

	  <!-- Modal content -->
	  <div class="modal-content" id="aw_ar_popup_area">
		<span class="close" onclick="aw_ar_close_modal()">&times;</span>
		
		<div id="aw_ar_button_section">
				
				<p><h2>Please confirm that you want to report abuse.</h2></p>
				<div class="aw_ar_modal_action_btns">
					<input type="button" name="updatepoint" class="aw_ar_modal_apply button" value="I Confirm" id="aw_ar_apply_button" onclick="aw_ar_absument(<?php echo wp_kses($comment_id, wp_kses_allowed_html('post')); ?>,<?php echo wp_kses($product_id, wp_kses_allowed_html('post')); ?>,<?php echo wp_kses($user_id, wp_kses_allowed_html('post')) ; ?>)">
					<input type="button" name="" class="aw_ar_modal_close inactive-btn " value="Cancel" id="aw_ar_close_button" onclick="aw_ar_close_modal()">
				</div>
		</div>	
	  </div>
	</div>
	<?php
}

function aw_ar_commentlist_and_commentform_section( $comment_id, $product_id, $user_id, $comment_approved, $comment_type) {

	$nickname 	= '';
	$meta_key	= 'aw_ar_abusement';

	$childcomments = get_comments(array(
									'type'  => 'review',
									'status'=> 'approve',
									'order' => 'DESC',
									'parent'=> $comment_id,
								));

	if (!empty($childcomments)) {
		foreach ($childcomments as $childcomment) {
			$abused = get_comment_meta($childcomment->comment_ID, 'aw_ar_abusement', true);
		
			?>
		<div class="aw_ar_comments-block">
			<div class="aw_ar_comments_author"><?php echo wp_kses($childcomment->comment_author , wp_kses_allowed_html('post')); ?></div>
			<div class="aw_ar_comments_content"><?php echo wp_kses($childcomment->comment_content , wp_kses_allowed_html('post')); ?></div>
			<?php 
			//if(empty($abused) && !empty($childcomment)){
			if (!isset($_COOKIE[$meta_key]) && !empty($childcomment)) {
				?>
			<div class="aw_ar_comments_abuse"><?php $abuse_image_url = plugins_url('/advanced-reviews-by-aheadworks/admin/images/abuse_default.png', __DIR__); ?>
					<div>
						<img src="<?php echo esc_url($abuse_image_url); ?>" class="aw_ar_abuse_img" id="aw_ar_abuseimg-<?php echo wp_kses($childcomment->comment_ID , wp_kses_allowed_html('post')); ?>" alt="abuse" />	
					</div>
					<?php aw_ar_abusement_popup($childcomment->comment_ID, $product_id); ?> 
				</div>
			<?php 
			} 
			?>
		</div>
		<?php
		}
	}
	
	if (is_user_logged_in()) {
		$user_id 	= get_current_user_id();
		$user_info 	= get_userdata( $user_id );		
		$nickname 	= $user_info->user_nicename;
		$role 		= implode(', ', $user_info->roles);
	} 
	?>
	<div style="clear:both;"></div>
	<p>
		<button class="aw_ar_addcomment_btn" onclick="aw_ar_toggle_comment_form(<?php echo wp_kses($comment_id, wp_kses_allowed_html('post')); ?>)"> Add Comment </button>
	<p>
	<p>
		<div id="awarcommentform-<?php echo wp_kses($comment_id, wp_kses_allowed_html('post')); ?>" style="display:none">
			<form action="" method="post" class="aw_ar_comment-form ">
				<div style="clear:both;"></div>  
				<p>
					<label for="nickname"><?php echo wp_kses('Nickname', wp_kses_allowed_html('post')); ?> <span class="required">*</span></label>
					<input type="text" id="nickname_text-<?php echo wp_kses($comment_id, wp_kses_allowed_html('post')); ?>" name="aw_ar_nickname" required="" value="<?php echo wp_kses($nickname, wp_kses_allowed_html('post')); ?>"/>
					<span class='error_nickname-<?php echo wp_kses($comment_id, wp_kses_allowed_html('post')); ?>'></span>
				</p>
				<div style="clear:both;"></div>
				<p>
					<label for="comment"><?php echo wp_kses('Comment', wp_kses_allowed_html('post')); ?> <span class="required">*</span></label>
					<textarea id="comment_text-<?php echo wp_kses($comment_id, wp_kses_allowed_html('post')); ?>" name="aw_ar_comment" cols="45" rows="3" required=""></textarea>
					<span class='error_comment-<?php echo wp_kses($comment_id, wp_kses_allowed_html('post')); ?>'></span>
				</p>
				<div style="clear:both;"></div>  
				<p class="form-submit arcommntform-<?php echo wp_kses($comment_id, wp_kses_allowed_html('post')); ?>">
					<button class="aw_ar_commentfrm_btn" name="aw_ar_reviewcomment" onclick="aw_ar_submit_comment_on_review(event,<?php echo wp_kses($comment_id, wp_kses_allowed_html('post')); ?>,<?php echo wp_kses($product_id, wp_kses_allowed_html('post')); ?>)" >Submit</button>
					<button class="aw_ar_commentfrm_btn" name="aw_ar_reviewcomment" onclick="aw_ar_toggle_comment_form(<?php echo wp_kses($comment_id, wp_kses_allowed_html('post')); ?>)" >Cancel</button>
				</p>
			</form>
		</div>
	</p>					
	<?php 
}
function aw_ar_kses_filter_allowed_html( $allowed, $context) {
	$allowed['style'] 				= array();
	$allowed['input']['type'] 		= array();
	$allowed['input']['name'] 		= array();
	$allowed['input']['value'] 		= array();
	$allowed['input']['id'] 		= array();
	$allowed['input']['class'] 		= array();
	$allowed['input']['placeholder'] = array();
	$allowed['input']['style'] 		= array();
	$allowed['input']['data-value'] = array();
	$allowed['input']['data-button'] = array();
	$allowed['input']['size'] 		= array();
	$allowed['input']['maxlength'] 	= array();
	$allowed['input']['required'] 	= array();
	$allowed['input']['aria-describedby'] = array();
	$allowed['input']['data-belowelement'] = array();
	$allowed['input']['data-commentid'] = array();
	$allowed['input']['data-postid'] = array();
	$allowed['input']['checked'] 	= array();
	$allowed['textarea']['id']		= array();
	$allowed['textarea']['name']	= array();
	$allowed['textarea']['cols']	= array();
	$allowed['textarea']['rows']	= array();
	$allowed['textarea']['class']	= array();
	$allowed['textarea']['maxlength'] = array();
	$allowed['textarea']['required'] = array();
	$allowed['textarea']['aria-required'] = array();
	$allowed['time']				= array();
	$allowed['time']['datetime']	= array();
	$allowed['time']['title']		= array();
	$allowed['span']['onclick'] 	= array();
	$allowed['span']['aria-current'] = array();
	$allowed['button']['onclick'] 	= array();
	$allowed['form']['id'] 			= array();
	$allowed['form']['class'] 		= array();
	$allowed['form']['novalidate'] 	= array();
	$allowed['form']['action'] 		= array();
	$allowed['form']['method'] 		= array();
	
	return $allowed;
}

/* Send Mail to author */
function aw_ar_send_mail( $product_id = '', $review_id = '', $sender_nickname = '', $comment_text = '', $mail_template) {

	$admin_mail_id 	= '';
	$goformail 		= 0;
	$review 		= get_comment($review_id );
	$heading 		= '';
	$goformail 		= 1;
	if ( 1 == $goformail) {
		global $wpdb;
		$settings = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_ar_email_templates WHERE email = %s", "{$mail_template}" )); 
		if (!empty($settings)) {
			foreach ($settings as $value) {
				$subject 			= $value->subject;
				$additional_content = $value->additional_content;
				$heading 			= $value->email_heading;
				$active 			= $value->active;
				$email_type 		= $value->email_type;
			}
		}
		if (0 == $active) {
			return;
		}

		$product_id 			= $review->comment_post_ID;
		$product_name 			= get_the_title( $product_id );
		$comment_author_email	= $review->comment_author_email;
		$comment_author_name 	= $review->comment_author;
		$comment_parent_id 		= $review->comment_ID;

		$url_part		= ''; //'+tab-QA_tab';
		$url 			= '<a href="' . get_permalink($product_id) . '#div-comment-' . $comment_parent_id . $url_part . '" target="_blank" rel="nofollow">' . get_permalink($product_id) . '#div-comment-' . $comment_parent_id . $url_part . '</a>';

		switch ($mail_template) {
			case 'Admin Email':
			case 'Abuse Report Email':
			case 'Critical Report Email':
				$users = get_users( array( 'role' => 'Administrator' ) );
				foreach ( $users as $user ) {
					$user_name = get_user_by('email', $user->data->user_email);
					if (!empty($user_name) && !empty($userdata)) {
						$name = ucfirst($userdata->first_name) . ' ' . ucfirst($userdata->last_name);
					} else {
						$name = 'Admin';
					}
					$user_data[] = array('user_email'=>$user->data->user_email,'user_name' => $name);
				} 

				$url_part		= admin_url('comment.php?action=editcomment&c=' . $review_id);
				$url 			=  '&nbsp;<a href="' . $url_part . '" target="_blank" rel="nofollow">' . $url_part . '</a>&nbsp;';
				break;
			default:
				 $user_data[] = array('user_email'=>$review->comment_author_email,'user_name'=>$review->comment_author);
				break;
		}

		foreach ( $user_data as $user ) { 
			$user_name 		= $user['user_name'];
			$user_email 	= $user['user_email'];
			$from_name 		= get_option('woocommerce_email_from_name');
			$from_email		= get_option('woocommerce_email_from_address');
			$header_image 	= get_option('woocommerce_email_header_image');
			$footer_text 	= get_option('woocommerce_email_footer_text'); 
			$basecolor 	 	= get_option('woocommerce_email_base_color'); 
			$backgroundcolor= get_option('woocommerce_email_background_color'); 
			$body_backgroundcolor 	= get_option('woocommerce_email_body_background_color'); 	    
			$text_color	 			= get_option('woocommerce_email_text_color');  
			$footer_text 			= aw_ar_placeholders_replace($footer_text);

			if (!empty($heading)) {
				$email_heading 	= $heading;
			}

			if (!empty($subject)) {
				$email_subject 	= $subject;
			}
			if (!empty($additional_content)) {
				$additional_text = preg_replace('/{admin}/', '<b>{admin}</b><br>', $additional_content);
				$additional_text = preg_replace('/{admin}/', $user_name, $additional_content);

				$additional_text = preg_replace('/{customer_name}/', '<b>{customer_name}</b><br>', $additional_content);
				$additional_text = preg_replace('/{customer_name}/', $user_name, $additional_text);
			
				$additional_text = preg_replace('/{link to the review on the backend}/', $url, $additional_text);
				$additional_text = preg_replace('/{A reply on the initial comment}/', '{A reply on the initial comment}<br>', $additional_text);
				if (''!=$comment_text) {
					$additional_text = preg_replace('/{A reply on the initial comment}/', '"' . $comment_text . '"', $additional_text);	
				}
				$additional_text = $additional_text . $url;
			}
			ob_start();
			?>
			<!DOCTYPE html>
				 <html>
				 <body  marginwidth="0" topmargin="0" marginheight="0" offset="0" >
					 <div id="wrapper" style="background-color:<?php echo wp_kses($backgroundcolor, wp_kses_allowed_html('post')); ?> ">
						 <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
						 <tr>
							 <td align="center" valign="top">
								 <div id="template_header_image">
								<?php
								$img = get_option( 'woocommerce_email_header_image' );
								if ('' != $img) {
									$out_o = '<p style="margin-top:0;"><img src="' . esc_url( $img ) . '" alt="' . get_bloginfo( 'name', 'display' ) . '" /></p>';
									echo wp_kses($out_o, wp_kses_allowed_html('post'));
								}
								?>
								 </div>
								 <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container" style="border: 1px solid #dedede; box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1); border-radius: 3px;">
									 <tr>
									<td align="center" valign="top">
										<!-- Header -->
										<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_header" style="background-color: <?php echo wp_kses($basecolor, wp_kses_allowed_html('post')); ?> ; border-bottom: 0; font-weight: bold; line-height: 100%; vertical-align: middle;border-radius: 3px 3px 0 0;font-weight: bold; line-height: 100%; vertical-align: middle;">
											<tr>
												<td id="header_wrapper" style="padding: 36px 48px; display: block;">
													<h1 style="font-size: 30px; font-weight: 300; line-height: 150%; margin: 0; text-align: left; text-shadow: 0 1px 0 #ab79a1;"><?php echo wp_kses($email_heading, wp_kses_allowed_html('post'));//$email_heading; ?></h1>
												</td>
											</tr>
										</table>
										<!-- End Header -->
									</td>
									</tr>
									 <tr>
										<td align="center" valign="top">
											<!-- Body -->
											<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body">
												<tr>
													<td valign="top" id="body_content" style="background-color:<?php echo wp_kses($body_backgroundcolor, wp_kses_allowed_html('post')); ?>;">
														<!-- Content -->
														<table border="0" cellpadding="20" cellspacing="0" width="100%">
															<tr>
																<td valign="top" style="padding: 48px 48px 32px;">
																	<div id="body_content_inner" style="color:<?php echo wp_kses($text_color, wp_kses_allowed_html('post')); ?>;font-size: 14px; line-height: 150%; text-align: left;">
																		<p><?php echo wp_kses($additional_text, wp_kses_allowed_html('post')); ?></p>
																		
																	</div>
																</td>
															</tr>
														</table>
														<!-- End Content -->
													</td>
												</tr>
											</table>
											<!-- End Body -->
										</td>
									</tr>					
								 </table>
							 </td>
						 </tr>	

						<tr>
							<td align="center" valign="top">
								<!-- Footer -->
								<table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer">
									<tr>
										<td valign="top">
											<table border="0" cellpadding="10" cellspacing="0" width="100%">
												<tr>
													<td colspan="2" valign="middle" id="credit" style="font-size: 12px; line-height: 150%; text-align: center; padding: 24px 0;">
													<?php echo wp_kses($footer_text, wp_kses_allowed_html('post')); ?>
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
								<!-- End Footer -->
							</td>
						</tr>
				</table>
			</div>
			</body>
			</html>
			<?php
				$message 		= ob_get_contents();
				$site_title 	= get_bloginfo( 'name', 'display' );
				$site_url 		= home_url();
				$comment_author = $review->comment_author;
				$users 			= get_users( array( 'role' => 'Administrator' ) );
			foreach ( $users as $user ) {
				$USERDATA[]= $user->data->display_name;
			} 
			if (!empty($USERDATA)) {
				$admin_name 	= implode(',', $USERDATA);
			} else {
				$admin_name 	= $user_name;
			}

			$to_replace 	= array('{site_title}','{site_url}','{product_name}','{admin}','{customer_name}','{A reply on the initial comment}','{order_number}','{order_date}');
			$by_replace 	=array($site_title,$site_url,$product_name,$admin_name,$comment_author,'','','');
			$message 		= str_replace($to_replace, $by_replace, $message);
			$email_subject 	= str_replace($to_replace, $by_replace, $email_subject);
			ob_end_clean();
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			add_filter( 'wp_mail_from', 'aw_ar_mail_from' );
			add_filter( 'wp_mail_from_name', 'aw_ar_from_name' );
				
			if ('text/plain' == $email_type) {
				$message= preg_replace('/(<[^>]+) style=".*?"/i', '$1', $message);
				$to_replace = array('<b>','</b>','<h1>','</h1>','<strong>','</strong>');
				$message = str_replace($to_replace, '', $message);
				$message= preg_replace('/<b>/', '$1', $message);
			}
			wp_mail($user_email, $email_subject, $message, $headers);
			update_comment_meta( $review_id, sanitize_title($mail_template), '1');
			remove_filter( 'wp_mail_from', 'aw_ar_mail_from' );
			remove_filter( 'wp_mail_from_name', 'aw_ar_from_name' );
		}
	}
}

function aw_ar_send_mail_after_product_order( $order_id, $product_id, $user_id, $user_name, $user_email, $mail_template) {
		global $wpdb;
		$settings = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_ar_email_templates WHERE email = %s", "{$mail_template}" )); 
	if (!empty($settings)) {
		foreach ($settings as $value) {
			$subject 			= $value->subject;
			$additional_content = $value->additional_content;
			$heading 			= $value->email_heading;
			$active 			= $value->active;
			$email_type 		= $value->email_type;
		}
	}
	if (0 == $active) {
		return;
	}

		$product_name 			= get_the_title( $product_id );
		
		$url_part		= ''; //'+tab-QA_tab';
		$url 			= '<a href="' . get_permalink($product_id) . '#div-comment-' . $url_part . '" target="_blank" rel="nofollow">' . get_permalink($product_id) . '#div-comment-' . $url_part . '</a>';

		$from_name 		= get_option('woocommerce_email_from_name');
		$from_email		= get_option('woocommerce_email_from_address');
		$header_image 	= get_option('woocommerce_email_header_image');
		$footer_text 	= get_option('woocommerce_email_footer_text'); 
		$basecolor 	 	= get_option('woocommerce_email_base_color'); 
		$backgroundcolor= get_option('woocommerce_email_background_color'); 
		$body_backgroundcolor 	= get_option('woocommerce_email_body_background_color'); 	    
		$text_color	 			= get_option('woocommerce_email_text_color');  
		$footer_text 			= aw_ar_placeholders_replace($footer_text);

	if (!empty($heading)) {
		$email_heading 	= $heading;
	}

	if (!empty($subject)) {
		$email_subject 	= $subject;
	}
	if (!empty($additional_content)) {
		$additional_text = preg_replace('/{admin}/', '<b>{admin}</b><br>', $additional_content);
		$additional_text = preg_replace('/{admin}/', $user_name, $additional_content);

		$additional_text = preg_replace('/{customer_name}/', '<b>{customer_name}</b><br>', $additional_content);
		$additional_text = preg_replace('/{customer_name}/', $user_name, $additional_text);
		
		$additional_text = preg_replace('/{link to the review on the backend}/', $url, $additional_text);
		$additional_text = preg_replace('/{A reply on the initial comment}/', '{A reply on the initial comment}<br>', $additional_text);
		$additional_text = $additional_text . $url;
	}
		ob_start();
	?>
		<!DOCTYPE html>
			 <html>
			 <body  marginwidth="0" topmargin="0" marginheight="0" offset="0" >
				 <div id="wrapper" style="background-color:<?php echo wp_kses($backgroundcolor, wp_kses_allowed_html('post')); ?> ">
					 <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
					 <tr>
						 <td align="center" valign="top">
							 <div id="template_header_image">
							<?php
							$img = get_option( 'woocommerce_email_header_image' );
							if ('' != $img) {
								$out_o = '<p style="margin-top:0;"><img src="' . esc_url( $img ) . '" alt="' . get_bloginfo( 'name', 'display' ) . '" /></p>';
								echo wp_kses($out_o, wp_kses_allowed_html('post'));
							}
							?>
							 </div>
							 <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container" style="border: 1px solid #dedede; box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1); border-radius: 3px;">
								 <tr>
								<td align="center" valign="top">
									<!-- Header -->
									<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_header" style="background-color: <?php echo wp_kses($basecolor, wp_kses_allowed_html('post')); ?> ; border-bottom: 0; font-weight: bold; line-height: 100%; vertical-align: middle;border-radius: 3px 3px 0 0;font-weight: bold; line-height: 100%; vertical-align: middle;">
										<tr>
											<td id="header_wrapper" style="padding: 36px 48px; display: block;">
												<h1 style="font-size: 30px; font-weight: 300; line-height: 150%; margin: 0; text-align: left; text-shadow: 0 1px 0 #ab79a1;"><?php echo wp_kses($email_heading, wp_kses_allowed_html('post'));//$email_heading; ?></h1>
											</td>
										</tr>
									</table>
									<!-- End Header -->
								</td>
								</tr>
								 <tr>
									<td align="center" valign="top">
										<!-- Body -->
										<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body">
											<tr>
												<td valign="top" id="body_content" style="background-color:<?php echo wp_kses($body_backgroundcolor, wp_kses_allowed_html('post')); ?>;">
													<!-- Content -->
													<table border="0" cellpadding="20" cellspacing="0" width="100%">
														<tr>
															<td valign="top" style="padding: 48px 48px 32px;">
																<div id="body_content_inner" style="color:<?php echo wp_kses($text_color, wp_kses_allowed_html('post')); ?>;font-size: 14px; line-height: 150%; text-align: left;">
																	<p><?php echo wp_kses($additional_text, wp_kses_allowed_html('post')); ?></p>
																	
																</div>
															</td>
														</tr>
													</table>
													<!-- End Content -->
												</td>
											</tr>
										</table>
										<!-- End Body -->
									</td>
								</tr>					
							 </table>
						 </td>
					 </tr>	

					<tr>
						<td align="center" valign="top">
							<!-- Footer -->
							<table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer">
								<tr>
									<td valign="top">
										<table border="0" cellpadding="10" cellspacing="0" width="100%">
											<tr>
												<td colspan="2" valign="middle" id="credit" style="font-size: 12px; line-height: 150%; text-align: center; padding: 24px 0;">
												<?php echo wp_kses($footer_text, wp_kses_allowed_html('post')); ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
							<!-- End Footer -->
						</td>
					</tr>
			</table>
		</div>
		</body>
		</html>
		<?php
			$message 		= ob_get_contents();
			$site_title 	= get_bloginfo( 'name', 'display' );
			$site_url 		= home_url();
			$to_replace 	= array('{site_title}','{site_url}','{product_name}','{customer_name}','{A reply on the initial comment}','{order_number}','{order_date}');
			$by_replace 	= array($site_title,$site_url,$product_name,$user_name,'','','');
			$message 		= str_replace($to_replace, $by_replace, $message);
			$email_subject 	= str_replace($to_replace, $by_replace, $email_subject);
			ob_end_clean();
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			add_filter( 'wp_mail_from', 'aw_ar_mail_from' );
			add_filter( 'wp_mail_from_name', 'aw_ar_from_name' );
			
		if ('text/plain' == $email_type) {
			$message= preg_replace('/(<[^>]+) style=".*?"/i', '$1', $message);
			$to_replace = array('<b>','</b>','<h1>','</h1>','<strong>','</strong>');
			$message = str_replace($to_replace, '', $message);
			$message= preg_replace('/<b>/', '$1', $message);
		}
			wp_mail($user_email, $email_subject, $message, $headers);
			update_post_meta( $order_id, 'aw_ar-reminder-mail-sent', '1');
			remove_filter( 'wp_mail_from', 'aw_ar_mail_from' );
			remove_filter( 'wp_mail_from_name', 'aw_ar_from_name' );
}

function aw_ar_get_email_template_active_status( $template) {
	global $wpdb;
	$emails_template = $wpdb->get_var($wpdb->prepare("SELECT active FROM {$wpdb->prefix}aw_ar_email_templates WHERE email LIKE %s ", "%{$template}%") );
	return $emails_template;
}

function aw_ar_mail_from( $email ) {
	$from_email = get_option('woocommerce_email_from_address');
	return $from_email;
}

function aw_ar_from_name( $name ) {
	$from_name = get_option('woocommerce_email_from_name');
	return $from_name;
}
function aw_ar_placeholders_replace( $string ) {
	$domain = wp_parse_url( home_url(), PHP_URL_HOST );

	return str_replace(
	   array(
		   '{site_title}',
		   '{site_address}',
		   '{woocommerce}',
		   '{WooCommerce}',
	   ),
	   array(
		   wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ),
		   $domain,
		   '<a href="https://woocommerce.com">WooCommerce</a>',
		   '<a href="https://woocommerce.com">WooCommerce</a>',
	   ),
	   $string
);
}

function aw_ar_get_the_user_ip() {
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ip = sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ip = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
	} else {
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
		}	
	}
	return $ip;
}


 
