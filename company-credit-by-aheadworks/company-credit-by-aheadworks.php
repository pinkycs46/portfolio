<?php
/**
 * Plugin Name: Company Credit By Aheadworks
 * Plugin URI: https://www.aheadworks.com/
 * Description: Offer credit to encourage customers to speed up and increase the amount of their spending.
 * Author: Aheadworks
 * Author URI: https://www.aheadworks.com/
 * Version: 1.0.2
 * Woo: 8300442:b59b0f4f7fc320ffb3c9b75640d6aec1
 * Text Domain: company-credit-by-aheadworks
 *
 * @package company-credit-by-aheadworks
 *
 * Requires at least: 5.9.3
 * Tested up to: 6.1.1
 * WC requires at least: 6.2.2
 * WC tested up to: 7.3.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
/** Present plugin version **/
define( 'AW_COMPANY_CREDIT_VERSION', '1.0.2' );

require_once(plugin_dir_path(__FILE__) . 'includes/aw-company-credit-admin.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-cc-customer-list-admin.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-cc-credit-history-list-admin.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-cc-mycredit.php');

$companycredit = new AwCompanyCredit();

class AwCompanyCredit {
	public $GLOBALS;
	public function __construct() {
 
		//add_action('admin_init', array(get_called_class(),'aw_company_credit_installer'));
		register_activation_hook(__FILE__ , array(get_called_class(),'aw_company_credit_installer'));
		register_uninstall_hook(__FILE__, array(get_called_class(), 'aw_company_credit_unistaller'));
		/* Add Custom menus admin side*/
		if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
		//if (class_exists( 'woocommerce' ) ) {
		//if (isWoocommerceActive()) {
			add_action('admin_menu', array('AwCompanyCreditAdmin','aw_company_credit_menu'));
		}
		
		add_action('admin_enqueue_scripts', array(get_called_class(),'aw_company_credit_admin_addScript'));
		add_action('wp_enqueue_scripts', array(get_called_class(),'aw_company_credit_public_addScript'));
		//add_action('woocommerce_checkout_update_order_meta', array(get_called_class(),'aw_company_credit_update_meta_after_order'), 10, 2);
		add_action('woocommerce_thankyou', array(get_called_class(),'aw_company_credit_update_meta_after_order'), 10, 2);

		add_action( 'woocommerce_new_order', array(get_called_class(),'aw_compony_credit_get_admin_order'), 10, 2);
	
		add_action('woocommerce_order_status_changed', array(get_called_class(),'aw_cc_order_status_changed_notifier'), 10, 4);
		add_action( 'woocommerce_order_refunded', array(get_called_class(),'aw_company_credit_admin_refunde_by_button'));
		add_action( 'woocommerce_process_shop_order_meta', array(get_called_class(),'aw_company_credit_admin_update_order'), 10, 2);

		/***** Admin Order *****/
		add_action('woocommerce_order_item_add_action_buttons', array(get_called_class(), 'aw_cc_order_item_add_hidden_input'), 10, 1);
		add_action('wp_ajax_aw_cc_admin_get_order_detail', array(get_called_class(),'aw_cc_admin_get_order_detail'));
		add_action('woocommerce_before_order_itemmeta', array(get_called_class(), 'aw_cc_display_point_in_order_admin'), 10, 1);
		add_action('wp_ajax_woocommerce_calc_line_taxes', array(get_called_class(),'aw_cc_recalculate_btn_clk'));
		add_filter( 'woocommerce_admin_order_totals_after_tax', array(get_called_class(),'aw_cc_order_totals_after_tax')) ;
		/*************** Admin Order ****************/
		add_filter('wp_kses_allowed_html', 'aw_cc_kses_filter_allowed_html', 10, 2);

		add_action('user_register', array(get_called_class(),'aw_cc_new_user_transaction'));
	}

	public static function aw_company_credit_installer() {
		if (is_admin()) {

			if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
			//if (class_exists( 'woocommerce' ) ) {	
				/** If WooCommerce plugin is not activated show notice **/
				add_action('admin_notices', array('AwcompanycreditAdmin','aw_pq_self_deactivate_notice'));
				/** Deactivate our plugin **/
				deactivate_plugins(plugin_basename(__FILE__));
				if (isset($_GET['activate'])) {
					unset($_GET['activate']);
				}
			} else {
				global $wpdb;
				$db_credit_bal_table 	= $wpdb->prefix . 'aw_company_credit_balance'; 
				$db_credit_history_table= $wpdb->prefix . 'aw_company_credit_history'; 
				flush_rewrite_rules();
				wp_deregister_script( 'autosave' );
				if ($wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ' , "{$wpdb->prefix}aw_company_credit_balance")) != $db_credit_bal_table) {
						$sql = "CREATE TABLE {$wpdb->prefix}aw_company_credit_balance (
								  `id` int(11) NOT NULL auto_increment,
								  `user_id` bigint(20) NOT NULL,
								  `credit_limit` DECIMAL(10,2) NULL,
								  `credit_balance` DECIMAL(10,2) NOT NULL,
								  `available_credit`  DECIMAL(10,2) NOT NULL,
								  `last_payment` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
								  `last_updated` timestamp NOT NULL,
								  PRIMARY KEY (`id`)
								);"	;
						require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
						dbDelta($sql);
				}

				if ($wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ' , "{$wpdb->prefix}aw_company_credit_history")) != $db_credit_history_table) {
						$sql = "CREATE TABLE {$wpdb->prefix}aw_company_credit_history (
								  `transaction_id` int(11) NOT NULL auto_increment,
								  `user_id` bigint(20) NOT NULL,
								  `transaction_amount` DECIMAL(10,2) NOT NULL,
								  `credit_balance` DECIMAL(10,2) NOT NULL,
								  `available_credit` DECIMAL(10,2) NOT NULL,
								  `credit_limit` DECIMAL(10,2) NOT NULL,
								  `transaction_status` varchar(255) NOT NULL COMMENT 'Purchased, Updated',
								  `order_id`  text NOT NULL ,
								  `comment_to_customer` TEXT NOT NULL,
								  `comment_to_admin` TEXT NOT NULL,
								  `last_payment` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
								  PRIMARY KEY (`transaction_id`)
								);"	;
					require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
					dbDelta($sql);
				}
				$exist = get_option( 'company_credit_by_aheadwork' );
				if (!$exist) {
					aw_cc_update_new_user_credit_limit();
					//Check to see if the table exists already, if not, then create it
					update_option('AW_COMPANY_CREDIT_VERSION', AW_COMPANY_CREDIT_VERSION );
					update_option( 'company_credit_by_aheadwork', 'completed' );

					$charset_collate 		= $wpdb->get_charset_collate();
					$db_aw_cc_email_table	= $wpdb->prefix . 'aw_cc_email_templates';
					//Check to see if the table exists already, if not, then create it
					if ($wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ' , "{$wpdb->prefix}aw_cc_email_templates")) != $db_aw_cc_email_table) {
						$sql = "CREATE TABLE {$wpdb->prefix}aw_cc_email_templates (
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
						$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}aw_cc_email_templates
						(email, email_type, recipients, active, subject, email_heading, additional_content)
						VALUES
						('Credit Balance Updated','text/html',%s,0,'Your Credit Balance has been updated', 'Your Credit Balance has been updated', '
						Dear {customer_name} 
						Your Credit Balance has been updated.')", 'customer'));
					}
				}
			}
		}
	}
	public static function aw_company_credit_unistaller() {
		/* Perform required operations at time of plugin uninstallation */
		global $wpdb;
		$db_credit_bal_table 	= $wpdb->prefix . 'aw_company_credit_balance'; 
		$db_credit_history_table= $wpdb->prefix . 'aw_company_credit_history'; 
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aw_company_credit_balance");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aw_company_credit_history");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aw_cc_email_templates");
		delete_option('company_credit_by_aheadwork');
		delete_option('AW_COMPANY_CREDIT_VERSION');
		delete_option('aw_cc_credit_limit');
		delete_option('aw_cc_min_ordertotal');
		delete_option('aw_cc_max_ordertotal');
		delete_option('woocommerce_companycredit_payment_settings');
	}

	public static function aw_company_credit_admin_addScript() {
		$path 	= parse_url(get_option('siteurl'), PHP_URL_PATH);
		$host 	= parse_url(get_option('siteurl'), PHP_URL_HOST);
		$nonce 	= wp_create_nonce('companycredit_admin_nonce');
		$page 	= '';
		
		 
		wp_register_style('companycreditadmincss', plugins_url('/admin/css/aw-company-credit-admin.css', __FILE__ ), array(), '1.0' );
		wp_enqueue_style('companycreditadmincss');
 
		wp_register_script('companycreditadminjs', plugins_url('/admin/js/aw-company-credit-admin.js', __FILE__ ), array(), '1.0' );

		$order_js_var = array('site_url' => get_option('siteurl'), 'ajax_url'=>admin_url('admin-ajax.php'), 'path' => $path, 'host' => $host, 'aw_cc_admin_nonce' => $nonce);
		wp_localize_script('companycreditadminjs', 'aw_cc_admin_js_var', $order_js_var);

		wp_register_script('companycreditadminjs', plugins_url('/admin/js/aw-company-credit-admin.js', __FILE__ ), array(), '1.0' );
		wp_enqueue_script('companycreditadminjs'); 
		 
	}

	public static function aw_company_credit_public_addScript() {
		//add_filter( 'comments_clauses', 'aw_pq_filter_comments_clauses', 10, 1 );
		$path = parse_url(get_option('siteurl'), PHP_URL_PATH);
		$host = parse_url(get_option('siteurl'), PHP_URL_HOST);

		/** Add Plugin CSS and JS files Public Side**/
		wp_register_style('companycreditpubliccss', plugins_url('/public/css/aw-company-credit-public.css', __FILE__ ), array(), '1.0' );
		wp_enqueue_style('companycreditpubliccss');	
		wp_register_script('companycreditpublicjs', plugins_url('/public/js/aw-company-credit-public.js', __FILE__ ), array('jquery'), '1.0' );

		$js_var = array('site_url' => get_option('siteurl'), 'path' => $path, 'host' => $host);
		wp_localize_script('companycreditpublicjs', 'js_qa_var', $js_var);
		wp_register_script('companycreditpublicjs', plugins_url('/public/js/aw-company-credit-public.js', __FILE__ ), array('jquery'), '1.0' );
		wp_enqueue_script('companycreditpublicjs');

	}

	public static function aw_compony_credit_get_admin_order( $order_id) {
		if ( ! is_admin() ) {
			return;
		}
		global $wpdb;
		$db_balance_table 	= $wpdb->prefix . 'aw_company_credit_balance';
		$db_history_table	= $wpdb->prefix . 'aw_company_credit_history';
		$exist 				= get_option( 'company_credit_by_aheadwork' );
		if (!$exist) {
			return false;
		}

		$order = new WC_Order( $order_id );

		if (!empty($order)) {
			$order_data 		= $order->get_data();
			if ('companycredit_payment' != $order_data['payment_method']) {
				return false;
			}
			$transaction_amount = $order_data['total'];
			$user_id 			= $order_data['customer_id'];
			$credit_detail 		= aw_cc_get_user_credit_detail($user_id);

			$aw_cc_min_ordertotal 	= (float) get_option('aw_cc_min_ordertotal');
			$aw_cc_max_ordertotal 	= (float) get_option('aw_cc_max_ordertotal');

			if ('' == $credit_detail->credit_limit || $credit_detail->credit_limit < $transaction_amount) {
				return ;
			}
			if ($credit_detail->available_credit < $transaction_amount) {
				return ;
			}
			if (!empty($aw_cc_min_ordertotal)) {
				if ($transaction_amount < $aw_cc_min_ordertotal) {
					return ;
				}
			}	
			if (!empty($aw_cc_max_ordertotal)) {
				if ($aw_cc_max_ordertotal < $transaction_amount ) {
					return ;
				}
			}

			$balance_arr = array(
								'user_id' 			=> $user_id,
								'credit_limit' 		=> $credit_detail->credit_limit,
								'credit_balance' 	=> $credit_detail->credit_balance - $transaction_amount,
								'available_credit' 	=> $credit_detail->available_credit - $transaction_amount,
								'last_payment'		=> gmdate('Y-m-d H:i:s')
							);

			$wpdb->update( $db_balance_table , $balance_arr , array( 'user_id'=> $user_id ) );
			$transaction = array(
								'user_id' 				=> $user_id,
								'transaction_amount' 	=> -1* $transaction_amount,
								'credit_balance' 		=> $credit_detail->credit_balance - $transaction_amount,
								'available_credit' 		=> $credit_detail->available_credit - $transaction_amount,
								'credit_limit' 			=> $credit_detail->credit_limit,
								'transaction_status' 	=> 'Purchased',
								'order_id'				=> $order_id,
								'comment_to_customer' 	=> '#' . $order_id,
							);
			$wpdb->insert( $db_history_table , $transaction );



			unset($transaction['user_id']);
			unset($transaction['order_id']);
			unset($transaction['comment_to_customer']);
			unset($transaction['transaction_status']);

			$transaction_array 	= array_map('aw_cc_display_default_ordered_currency_amount', $transaction);
			$transaction_array['comment_to_customer'] 	= 'Order #' . $order_id;
			$transaction_array['order_id'] 				= $order_id;

			$the_user 			= get_user_by( 'id', $user_id );
			$user_name 			= $the_user->display_name;
			$user_email 		= $the_user->user_email;
			$mail_template 		= 'Credit Balance Updated';
			aw_cc_send_mail_after_update_credit_balance( $user_name, $user_email, $transaction_array, $mail_template);
		}
	}

	public static function aw_company_credit_update_meta_after_order( $order_id, $posted = '' ) {
		global $wpdb;
		$db_balance_table 	= $wpdb->prefix . 'aw_company_credit_balance';
		$db_history_table	= $wpdb->prefix . 'aw_company_credit_history';
		$path 		= '/';
		$host 		= parse_url(get_option('siteurl'), PHP_URL_HOST);
		$user_id 	= get_current_user_id();

		$credit_detail 		= aw_cc_get_user_credit_detail($user_id);

		$actual_amount 		= WC()->session->get('aw_cc_transaction_amount');
		$transaction_amount = WC()->session->get('aw_cc_transaction_amount');

		$order = new WC_Order( $order_id );
		if (!empty($order)) {
			$order_data = $order->get_data();
			if ('companycredit_payment' != $order_data['payment_method']) {
				return false;
			}
		}
		if (!empty($transaction_amount) && !empty($credit_detail) ) {
			WC()->session->set( 'aw_cc_transaction_amount', null );	
			$balance_arr = array(
								'user_id' 			=> $user_id,
								'credit_limit' 		=> $credit_detail->credit_limit,
								'credit_balance' 	=> $credit_detail->credit_balance - $transaction_amount,
								'available_credit' 	=> $credit_detail->available_credit - $transaction_amount,
								'last_payment'		=> gmdate('Y-m-d H:i:s')
							);

			$wpdb->update( $db_balance_table , $balance_arr , array( 'user_id'=> $user_id ) );
			$transaction = array(
								'user_id' 				=> $user_id,
								'transaction_amount' 	=> -1* $transaction_amount,
								'credit_balance' 		=> $credit_detail->credit_balance - $transaction_amount,
								'available_credit' 		=> $credit_detail->available_credit - $transaction_amount,
								'credit_limit' 			=> $credit_detail->credit_limit,
								'transaction_status' 	=> 'Purchased',
								'order_id'				=> $order_id,
								'comment_to_customer' 	=> '#' . $order_id,
							);
			$wpdb->insert( $db_history_table , $transaction );
			unset($transaction['user_id']);
			unset($transaction['transaction_amount']);
			unset($transaction['order_id']);
			unset($transaction['comment_to_customer']);
			unset($transaction['transaction_status']);
			$transaction_array 	= array_map('aw_cc_display_actual_amount', $transaction);
			$transaction_array['transaction_amount'] = '-' . aw_cc_get_amount($actual_amount);
			$transaction_array['comment_to_customer'] = 'Order #' . $order_id;
			$transaction_array['order_id'] 			= $order_id;

			$the_user 			= get_user_by( 'id', $user_id );
			$user_name 			= $the_user->display_name;
			$user_email 		= $the_user->user_email;
			$mail_template 		= 'Credit Balance Updated';
			aw_cc_send_mail_after_update_credit_balance( $user_name, $user_email, $transaction_array, $mail_template);
		} 
	}

	public static function aw_cc_order_status_changed_notifier( $order_id, $checkout = null, $status_transition_to, $instance ) {
		global $wpdb; 
		$db_balance_table 	= $wpdb->prefix . 'aw_company_credit_balance';
		$db_history_table	= $wpdb->prefix . 'aw_company_credit_history';
		$reverse 			= array( 'Refunded', 'Cancelled','Failed');
		$forward 			= array('On-hold','Processing','Completed','Pending');
		$exist 				= get_option( 'company_credit_by_aheadwork' );
		$statustext 		= '';	
		if (!$exist) {
			return false;
		}
		$history = aw_cc_get_history_by_orderid( $order_id );
		if (empty($history)) {
			return;
		}
		$balance_detail = aw_cc_get_user_credit_detail($history->user_id);

		$already_done_status = aw_cc_get_refund_cancel_status_order( $order_id, ucfirst($status_transition_to) );
		if ($already_done_status) {
			return;
		}
		 
		if (in_array(ucfirst($status_transition_to), $reverse)) {
			if ('Cancelled'===ucfirst($status_transition_to)) {
				$statustext = 'Cancel';
			}
			if ('Refunded'===ucfirst($status_transition_to)) {
				$statustext = 'Refund';
			}
			if ('Failed'===ucfirst($status_transition_to)) {
				$statustext = 'Failed';
			}
			$credit_detail 	= aw_cc_get_user_credit_detail($history->user_id);
			$transaction 	= array(
									'user_id' 				=> $history->user_id,
									'transaction_amount' 	=> (float) abs($history->transaction_amount),
									'credit_balance' 		=> (float) $balance_detail->credit_balance-$history->transaction_amount,
									'available_credit' 		=> (float) $balance_detail->available_credit-$history->transaction_amount,
									'credit_limit' 			=> $credit_detail->credit_limit,
									'transaction_status' 	=> ucfirst($status_transition_to),
									'order_id'				=> $order_id,
									'comment_to_customer' 	=> $statustext . ' created for order #' . $order_id
								);

			$wpdb->insert( $db_history_table , $transaction );
			$balance_arr 	= array(
									'user_id' 			=> $credit_detail->user_id,
									'credit_limit' 		=> $credit_detail->credit_limit,
									'credit_balance' 	=> $balance_detail->credit_balance-$history->transaction_amount,
									'available_credit' 	=> $balance_detail->available_credit-$history->transaction_amount,
									'last_payment'		=> gmdate('Y-m-d H:i:s'),
								);

			$wpdb->update( $db_balance_table , $balance_arr , array( 'user_id'=> $credit_detail->user_id ) );


			unset($transaction['user_id']);
			unset($transaction['order_id']);
			unset($transaction['transaction_status']);
			unset($transaction['comment_to_customer']);
			$transaction_array 	= array_map('aw_cc_display_actual_amount', $transaction);
			$transaction_array['comment_to_customer'] = $statustext . ' created for order #' . $order_id;
			$transaction_array['order_id'] 			= $order_id;
			$the_user 			= get_user_by( 'id', $history->user_id );
			$user_name 			= $the_user->display_name;
			$user_email 		= $the_user->user_email;
			$mail_template 		= 'Credit Balance Updated';
			aw_cc_send_mail_after_update_credit_balance( $user_name, $user_email, $transaction_array, $mail_template);
		} else if (in_array(ucfirst($status_transition_to), $forward)) {
				$history = aw_cc_get_history_by_orderid( $order_id );
				$order = new WC_Order( $order_id );

			if (!empty($order) && empty($history)) {
				$order_data 		= $order->get_data();
				$transaction_amount = -1* (float) $order_data['total'];
				if ('companycredit_payment' == $order_data['payment_method'] && $transaction_amount == $history->transaction_amount) {
					self::aw_compony_credit_get_admin_order( $order_id);
				}
			}
		}
	}

	public static function aw_company_credit_admin_update_order( $order_id, $posted) {
		global $wpdb; 
		$db_balance_table 	= $wpdb->prefix . 'aw_company_credit_balance';
		$db_history_table	= $wpdb->prefix . 'aw_company_credit_history';
		$reverse 			= array( 'Refunded', 'Cancelled', 'Failed');
		$exist 				= get_option( 'company_credit_by_aheadwork' );
		$statustext 		= '';	
		if (!$exist) {
			return false;
		}
		if (!is_admin()) {
			return ;
		}
		$history = aw_cc_get_history_by_orderid( $order_id );
		if (empty($history) || 'wc-failed'===$posted->post_status || 'wc-cancelled'===$posted->post_status|| 'wc-refunded'===$posted->post_status) {
			return;
		}
		$order = new WC_Order( $order_id );
		if (!empty($order)) {
			$order_data 		= $order->get_data();
			$transaction_amount = get_post_meta($order_id, '_order_total', true);
			$user_id = $order_data['customer_id'];
			if ('companycredit_payment' == $order_data['payment_method'] && abs($transaction_amount) !== abs($history->transaction_amount)) {
				$history_transaction = -1 * $history->transaction_amount;
				$balance_arr = array(
									'credit_balance'=> $history->credit_balance+ $history_transaction,
									'available_credit'=>$history->available_credit+$history_transaction
								 );
				$wpdb->update( $db_balance_table , $balance_arr , array( 'user_id'=> $history->user_id ) );

				$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}aw_company_credit_history WHERE transaction_id = %d" , "{$history->transaction_id}"));
				self::aw_compony_credit_get_admin_order( $order_id);
			}
		}
	}

	public static function aw_company_credit_admin_refunde_by_button( $order_id, $order_status = '') {
		global $wpdb; 
		$db_balance_table 	= $wpdb->prefix . 'aw_company_credit_balance';
		$db_history_table	= $wpdb->prefix . 'aw_company_credit_history';
		$reverse 			= array( 'Refunded', 'Cancelled');
		$exist 				= get_option( 'company_credit_by_aheadwork' );
		$statustext			= ''; 
		if (!$exist) {
			return false;
		}
		$history = aw_cc_get_history_by_orderid( $order_id );
		$already_done_status = aw_cc_get_refund_cancel_status_order( $order_id, ucfirst($order_status) );
		if ($already_done_status) {
			return;
		}
		$balance_detail = aw_cc_get_user_credit_detail($history->user_id);
		if (in_array(ucfirst($order_status), $reverse)) {
			if ('Cancelled'=== ucfirst($order_status)) {
				$statustext = 'Cancel';
			}
			if ('Refunded'===ucfirst($order_status)) {
				$statustext = 'Refund';
			}
			$credit_detail 		= aw_cc_get_user_credit_detail($history->user_id);
			$transaction = array(
									'user_id' 				=> $history->user_id,
									'transaction_amount' 	=> (float) abs($history->transaction_amount),
									'credit_balance' 		=> (float) $balance_detail->credit_balance-$history->transaction_amount,
									'available_credit' 		=> (float) $balance_detail->available_credit-$history->transaction_amount,
									'credit_limit' 			=> $credit_detail->credit_limit,
									'transaction_status' 	=> ucfirst($order_status),
									'order_id'				=> $order_id,
									'comment_to_customer' 	=> $statustext . ' created for order #' . $order_id
								);

			$wpdb->insert( $db_history_table , $transaction );
			$balance_arr = array(
									'user_id' 			=> $credit_detail->user_id,
									'credit_limit' 		=> $credit_detail->credit_limit,
									'credit_balance' 	=> $balance_detail->credit_balance-$history->transaction_amount,
									'available_credit' 	=> $balance_detail->available_credit-$history->transaction_amount,
									'last_payment'		=> gmdate('Y-m-d H:i:s'),
								);

			$wpdb->update( $db_balance_table , $balance_arr , array( 'user_id'=> $credit_detail->user_id ) );


			unset($transaction['user_id']);
			unset($transaction['order_id']);
			unset($transaction['transaction_status']);
			unset($transaction['comment_to_customer']);
			$transaction_array 	= array_map('aw_cc_display_actual_amount', $transaction);
			$transaction_array['comment_to_customer'] = 'Order #' . $order_id;
			$transaction_array['order_id'] 			= $order_id;
			$the_user 			= get_user_by( 'id', $history->user_id );
			$user_name 			= $the_user->display_name;
			$user_email 		= $the_user->user_email;
			$mail_template 		= 'Credit Balance Updated';
			aw_cc_send_mail_after_update_credit_balance( $user_name, $user_email, $transaction_array, $mail_template);

		}
	}

	

	public static function aw_cc_order_item_add_hidden_input( $order) {
		$btn_html = '<div id="aw_cc_admin_points_main_dv"><span id="aw_cc_admin_points_main" class="aw_cc_admin_points_main" style="float: left;">
					<input type="hidden" name="aw_cc_user_id" id="aw_cc_user_id" value=""> 
					<input type="hidden" name="aw_cc_recalculate" id="aw_cc_recalculate" value="">
				</span></div>';
		$btn_html .= wp_nonce_field('aw_cc_order_nonce_action', 'aw_cc_order_nonce_name');
		//echo $btn_html;
		echo wp_kses($btn_html, wp_kses_allowed_html('post'));
	}

	public static function aw_cc_display_point_in_order_admin( $order) {
		$security 	= '';
		$action 	= '';
		
		if (isset($_POST['security'])) {
			$security = sanitize_text_field($_POST['security']);
		}
		
		if (isset($_POST['action'])) {
			$action = sanitize_text_field($_POST['action']);
		}
		if ('' != $security && '' != $action) {
			$act_check = '';
			switch ($action) {
				case 'woocommerce_add_order_item':
					$act_check = 'order-item';
					break;

				case 'woocommerce_remove_order_item':
					$act_check = 'order-item';
					break;

				case 'woocommerce_load_order_items':
					$act_check = 'order-item';
					break;

				case 'woocommerce_save_order_items':
					$act_check = 'order-item';
					break;
					
				case 'woocommerce_add_coupon_discount':
					$act_check = 'order-item';
					break;
				
				case 'woocommerce_remove_order_coupon':
					$act_check = 'order-item';
					break;

				case 'woocommerce_add_order_fee':
					$act_check = 'order-item';
					break;

				case 'woocommerce_add_order_shipping':
					$act_check = 'order-item';
					break;

				case 'woocommerce_add_order_tax':
					$act_check = 'order-item';
					break;

				case 'woocommerce_calc_line_taxes':
					$act_check = 'calc-totals';
					break;

				default:
					break;
			}
			if ( !wp_verify_nonce( $security, $act_check)) {
				wp_die('Our Site is protected 1');
			}

			global $wpdb;			
			$existing_order_id = 0;
			$get_oder_itmm = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}woocommerce_order_items WHERE `order_item_id` = %d", "{$order}"));
			$existing_order_id = $get_oder_itmm->order_id;
			
			if (0 != $existing_order_id) {
				$order = wc_get_order($existing_order_id);
			}
		}
		
		if (isset($_POST['action']) && ( 'woocommerce_add_order_item' == $_POST['action'] )) {
			$order = wc_get_order($existing_order_id);
			?>
				<script type="text/javascript">
					msgcounterflag = 0 ;
					jQuery("#aw_cc_recalculate").val("0");
					aw_cc_admin_get_order_details('addnewitem');
					
				</script>
			<?php
		}

		if (isset($_POST['action']) && ( 'woocommerce_calc_line_taxes' == $_POST['action']  || 'woocommerce_save_order_items' == $_POST['action'] )) {
			?>
				<script type="text/javascript">
					msgcounterflag = 1;
					jQuery("#aw_cc_recalculate").val("1");
					aw_cc_admin_get_order_details('recalculate');
				</script>
			<?php
		}
	}

	public static function aw_cc_recalculate_btn_clk() {
		?>
		<script type="text/javascript">
			 msgcounterflag =0;
		</script>
		<?php	
	}

	public static function aw_cc_admin_get_order_detail() {
		global $wpdb;
		check_ajax_referer( 'companycredit_admin_nonce', 'nonce_cc_odr_ajax' );

		if (!empty($_POST['user_id'])) {
			$user = sanitize_text_field($_POST['user_id']);
		}

		if (!empty($_POST['order_id'])) {
			$order_id = sanitize_text_field($_POST['order_id']);
		}

		if (!empty($_POST['user_name'])) {
			$name = sanitize_text_field($_POST['user_name']);
		}

		if (!empty($_POST['order_total'])) {
			$order_total = sanitize_text_field($_POST['order_total']);
		}

		$point_exists 	= 0;
		$item_id 		= 0;

		$order 		= wc_get_order($order_id);
		$point_items = $order->get_items();

		if (( 0 < $order_id ) && ( 'Guest' != $name )) {
			$order = wc_get_order($order_id);
			$point_items = $order->get_items();
			foreach ($point_items as $item_id => $item_obj) {
					$point_exists = 1;
			}
		}

		$order_total 	= get_post_meta($order_id, '_order_total', true);
		if (empty($order_total)) {
			$order_total = 0;
		}
		$credit_detail 	= aw_cc_get_user_credit_detail($user);

		$aw_cc_min_ordertotal 	= (float) get_option('aw_cc_min_ordertotal');
		$aw_cc_max_ordertotal 	= (float) get_option('aw_cc_max_ordertotal');

		$credit_detail->error_msg = '';
		if ('' == $credit_detail->credit_limit /*|| $credit_detail->credit_limit < $order_total*/) {
			$credit_detail->error_msg = 'Maximum Credit limit is ' . aw_cc_get_amount($credit_detail->credit_limit) . ' Credit Limit can not be applied.' ;

		}
		if ($credit_detail->available_credit < $order_total && $credit_detail->credit_limit<$order_total) {
			$credit_detail->error_msg = 'Insufficient Available Credit Funds. Credit Limit can not be applied.';
		}
		if (!empty($aw_cc_min_ordertotal)) {
			if ($order_total < $aw_cc_min_ordertotal) {
				$credit_detail->error_msg = 'Order total must be greater or equal to Minimum Order Total. ' . aw_cc_get_amount($aw_cc_min_ordertotal) ;
			}	
		}
		if (!empty($aw_cc_max_ordertotal)) {
			if ($aw_cc_max_ordertotal < $order_total) {
				$credit_detail->error_msg = 'Order total must be less or equal to Maximum Order Total. ' . aw_cc_get_amount($aw_cc_max_ordertotal) ;
			}
		}
		 
		$credit_detail->order_total = $order_total;

		if (!empty($credit_detail)) {
			$credit_detail->item_id = $item_id;
			echo json_encode($credit_detail);	
		} else {
			echo 0;
		}
		wp_die();
	}

	public static function aw_cc_order_totals_after_tax( $order_id) {
		$order_total 	= get_post_meta($order_id, '_order_total', true);
		if (!empty($order_total)) {
			?>
		<script>
			if(msgcounterflag == '1'){
				jQuery('#aw_cc_recalculate').val('1');
				aw_cc_admin_get_order_details('recalculate');
			}
		</script>
		<?php 
		}
	}

	public static function aw_cc_new_user_transaction( $user_id) {
		global $wpdb;
		$balance_table = $wpdb->prefix . 'aw_company_credit_balance';
		$transac_table = $wpdb->prefix . 'aw_company_credit_history';
		$results = $wpdb->get_results($wpdb->prepare("SELECT U.ID FROM {$wpdb->prefix}aw_company_credit_balance AS CCB RIGHT JOIN {$wpdb->prefix}users AS U  ON  U.ID = CCB.user_id WHERE CCB.user_id =%d", "{$user_id}"));
				
		if (empty($results)) {
				$pusharrar = array();
				$aw_cc_credit_limit = get_option('aw_cc_credit_limit');
			if (!empty($aw_cc_credit_limit)) {
				$pusharrar['credit_limit'] 		= $aw_cc_credit_limit;
				$pusharrar['available_credit'] 	= $aw_cc_credit_limit;
				$pusharrar['credit_balance'] 	= 0;

				$transaction_array 	= array(
						'user_id' 				=> $user_id,
						'transaction_amount'	=> '',
						'credit_balance' 		=> 0,
						'available_credit' 		=> $aw_cc_credit_limit,
						'credit_limit' 			=> $aw_cc_credit_limit,
						'transaction_status'	=> 'Assigned',
					);
				$wpdb->insert($transac_table, $transaction_array);
			} else {
				$aw_cc_credit_limit = 0;
				$pusharrar['available_credit'] 	= $aw_cc_credit_limit;
				$pusharrar['credit_balance'] 	= $aw_cc_credit_limit;
				$pusharrar['credit_limit'] 	= 0;
			}
				$pusharrar['user_id'] = $user_id;
				$wpdb->insert($balance_table, $pusharrar);
			 
		}
	}
}

function aw_cc_get_email_template_setting_results() {
	global $wpdb;
	$emails_template = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_cc_email_templates WHERE 1 = %d ", 1 ) );
	return $emails_template;
}

function aw_cc_get_email_template_setting_row( $id) {
	global $wpdb;
	$emails_template = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_cc_email_templates WHERE id = %d ", "{$id}") );
	return $emails_template;
}

function aw_cc_get_user_available_credit( $user_id ) {
	global $wpdb;
	$available_credit = $wpdb->get_var($wpdb->prepare("SELECT available_credit FROM {$wpdb->prefix}aw_company_credit_balance WHERE user_id = %d ", "{$user_id}") );
	return $available_credit;
}

function aw_cc_get_user_credit_detail( $user_id ) {
	global $wpdb;
	$credit_detail = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_company_credit_balance WHERE user_id = %d ", "{$user_id}") );
	return $credit_detail;
}
function aw_cc_get_user_credit_history( $user_id ) {
	global $wpdb;
	$credit_history = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_company_credit_history WHERE user_id = %d ", "{$user_id}") );
	return $credit_history;
}

function aw_cc_get_amount( $amount) {
	if (!empty($amount)) {
		$decimalposition = get_option('woocommerce_price_num_decimals'); 
		$total_price = esc_html(sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), number_format($amount, $decimalposition) ));
		$total_price = strip_tags( $total_price );
		$total_price = html_entity_decode( $total_price );
		return $total_price;	
	}
}

function aw_cc_convert_default_currency( $price ) {
	if ( isset( $GLOBALS['WOOCS'] ) && method_exists( $GLOBALS['WOOCS'], 'get_currencies' ) && method_exists( $GLOBALS['WOOCS'], 'back_convert' ) ) {
		$currency_switcher = $GLOBALS['WOOCS'];
		$default_currency  = false;
		$currencies = $currency_switcher->get_currencies();

		foreach ( $currencies as $currency ) {
			if ( 1 === $currency['is_etalon']) {
				$default_currency = $currency;
				break;
			}
		}

		if ( $default_currency ) {
			if ( $currency_switcher->current_currency != $default_currency['name'] ) {
				return (float) $currency_switcher->back_convert( $price, $currencies[ $currency_switcher->current_currency ]['rate'] );
			}
		}
	}
	return $price;
}

function aw_cc_convert_currency( $price ) {
	// AW Gift Card Currency Switching 
	if ( isset( $GLOBALS['WOOCS'] ) && method_exists( $GLOBALS['WOOCS'], 'woocs_convert_price' ) ) {
		return $GLOBALS['WOOCS']->woocs_convert_price( $price );
	}
	return $price;
}


function aw_cc_get_history_by_orderid( $order_id ) {
	global $wpdb;
	$history = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_company_credit_history WHERE `order_id` = %d  AND `transaction_status`= %s ", "{$order_id}", 'Purchased' ) );
	return $history;
}

function aw_cc_get_refund_cancel_status_order( $order_id, $transaction_to ) {
	global $wpdb;
	$reverse 			= array( 'Refunded', 'Cancelled', 'Failed');
	$forward 			= array('On-hold','Processing','Completed','Pending');
	$result = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_company_credit_history WHERE `order_id` = %d  ORDER BY transaction_id DESC LIMIT %d", "{$order_id}", 1) );
	if (!empty($result)) {
		if (in_array($transaction_to, $reverse) && in_array($result->transaction_status, $reverse)) {
			return true;
		} else {
			return false;	
		}
	} else {
		return false;	
	} 
	
	//SELECT * FROM `rprprp_aw_company_credit_history` where order_id = 520 AND (transaction_status = 'Refunded' OR transaction_status = 'Cancelled' ) ORDER BY transaction_id DESC LIMIT 1
}

$active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
if (aw_cc_check_active_status_of_plugins()) {
//if (aw_cc_isWoocommerceActive()){
	add_filter('woocommerce_payment_gateways', 'aw_company_credit_limit_payment_method');
	function aw_company_credit_limit_payment_method( $gateways ) {
		$gateways[] = 'WC_CC_Payment_Method';
		return $gateways; 
	}

	add_action( 'plugins_loaded', 'aw_company_credit_init_credit_limit_payment_method' );
	function aw_company_credit_init_credit_limit_payment_method() {
		require_once(plugin_dir_path(__FILE__) . 'includes/class-woocommerce-credit-limit-payment.php');
	}
}


function aw_cc_check_active_status_of_plugins() {
	$active_plugins = (array) get_option('active_plugins', array());
	return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
	/*
	$plugins_active = (array) get_option('active_plugins', array());
		///return in_array('woocommerce/woocommerce.php', $plugins_active) || array_key_exists('woocommerce/woocommerce.php', $plugins_active);
	if (is_multisite()) {
		$plugins_active = array_merge($plugins_active, get_site_option('active_sitewide_plugins', array()));
	}
	if (class_exists( 'woocommerce' )) {
		return $plugins_active;
	} */
		//return false;
		//return class_exists( 'woocommerce' ) || array_key_exists('woocommerce/woocommerce.php', $plugins_active);
}


function aw_cc_update_new_user_credit_limit() {
	global $wpdb;
	$balance_table = $wpdb->prefix . 'aw_company_credit_balance';
	$transac_table = $wpdb->prefix . 'aw_company_credit_history';
	$results = $wpdb->get_results($wpdb->prepare("SELECT U.ID FROM {$wpdb->prefix}aw_company_credit_balance AS CCB RIGHT JOIN {$wpdb->prefix}users AS U  ON  U.ID = CCB.user_id WHERE CCB.user_id IS NULL AND 1=%d", 1));	
	
	
	if (!empty($results)) {
		foreach ( $results as $result) {
			$pusharrar = array();
			$aw_cc_credit_limit = get_option('aw_cc_credit_limit');
			if (!empty($aw_cc_credit_limit)) {
				$pusharrar['credit_limit'] 		= $aw_cc_credit_limit;
				$pusharrar['available_credit'] 	= $aw_cc_credit_limit;
				$pusharrar['credit_balance'] 	= 0;

				$transaction_array 	= array(
						'user_id' 				=> $result->ID,
						'transaction_amount'	=> '',
						'credit_balance' 		=> 0,
						'available_credit' 		=> $aw_cc_credit_limit,
						'credit_limit' 			=> $aw_cc_credit_limit,
						'transaction_status'	=> 'Assigned',
					);
				$wpdb->insert($transac_table, $transaction_array);
			} else {
				$aw_cc_credit_limit = 0;
				$pusharrar['available_credit'] 	= $aw_cc_credit_limit;
				$pusharrar['credit_balance'] 	= $aw_cc_credit_limit;
				$pusharrar['credit_limit'] 	= 0;
			}
			$pusharrar['user_id'] = $result->ID;
			$wpdb->insert($balance_table, $pusharrar);
		}
	}
}


function aw_cc_send_mail_after_update_credit_balance( $user_name, $user_email, $transaction_array, $mail_template) {
	global $wpdb;
	$settings = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_cc_email_templates WHERE email = %s", "{$mail_template}" )); 
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

	$url_part		= ''; //'+tab-QA_tab';
	//$url 			= '<a href="' . get_permalink($product_id) . '#div-comment-' . $url_part . '" target="_blank" rel="nofollow">' . get_permalink($product_id) . '#div-comment-' . $url_part . '</a>';

	$from_name 				= get_option('woocommerce_email_from_name');
	$from_email				= get_option('woocommerce_email_from_address');
	$header_image 			= get_option('woocommerce_email_header_image');
	$footer_text 			= get_option('woocommerce_email_footer_text'); 
	$basecolor 	 			= get_option('woocommerce_email_base_color'); 
	$backgroundcolor 		= get_option('woocommerce_email_background_color'); 
	$body_backgroundcolor 	= get_option('woocommerce_email_body_background_color'); 	    
	$text_color	 			= get_option('woocommerce_email_text_color');  
	$footer_text 			= aw_cc_placeholders_replace($footer_text);

	if (!empty($heading)) {
		$email_heading 	= $heading;
	}

	if (!empty($subject)) {
		$email_subject 	= $subject;
	}
	if (!empty($additional_content)) {
		$additional_text = $additional_content;
		$additional_text = preg_replace('/{admin}/', '<b>{admin}</b><br>', $additional_text);
		$additional_text = preg_replace('/{admin}/', $user_name, $additional_text);

		$additional_text = preg_replace('/{customer_name}/', '<b>{customer_name}</b><br>', $additional_text);
		$additional_text = preg_replace('/{customer_name}/', $user_name, $additional_text);


		if (!empty($transaction_array)) {
			$order_id = '';
			if (!empty($transaction_array['order_id'])) {
				$order_id = $transaction_array['order_id'];
			}
			$comment_to_customer = '';
			if (isset($transaction_array['comment_to_customer']) && !empty($transaction_array['comment_to_customer'])) {
				$comment_to_customer = $transaction_array['comment_to_customer'];

			}
			$transaction_text='<br><table border="1px" cellpadding="2px" cellspacing="2px">
			<tr> <th>Amount</th> <th>Credit Balance</th> <th>Available Credit</th> <th>Credit Limit</th> <th>Comment</th><tr> <td>' . $transaction_array['transaction_amount'] . '</td> <td>' . $transaction_array['credit_balance'] . '</td><td>' . $transaction_array['available_credit'] . '</td><td>' . $transaction_array['credit_limit'] . '</td> <td>' . $comment_to_customer . '</td></tr>	</table><br>';
			 
			$additional_text .=  $transaction_text;

		}

		$myaccount_url 	 = wc_get_account_endpoint_url( 'aw-cc-mycredit' ) ;
		$additional_text .= 'Click <a href="' . $myaccount_url . '" target="_blank">here</a> to see more details.';

		$additional_text = $additional_text ;//. $url;
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
			$to_replace 	= array('{site_title}','{site_url}','{customer_name},{transaction}','{order_number}','{order_date}');
			$by_replace 	= array($site_title,$site_url,$user_name,$transaction_text,'','');
			$message 		= str_replace($to_replace, $by_replace, $message);
			$email_subject 	= str_replace($to_replace, $by_replace, $email_subject);
			ob_end_clean();
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			add_filter( 'wp_mail_from', 'aw_cc_mail_from' );
			add_filter( 'wp_mail_from_name', 'aw_cc_from_name' );
			
		if ('text/plain' == $email_type) {
			$message= preg_replace('/(<[^>]+) style=".*?"/i', '$1', $message);
			$to_replace = array('<b>','</b>','<h1>','</h1>','<strong>','</strong>');
			$message = str_replace($to_replace, '', $message);
			$message= preg_replace('/<b>/', '$1', $message);
		}
			wp_mail($user_email, $email_subject, $message, $headers);
			remove_filter( 'wp_mail_from', 'aw_cc_mail_from' );
			remove_filter( 'wp_mail_from_name', 'aw_cc_from_name' );
}


function aw_cc_mail_from( $email ) {
	$from_email = get_option('woocommerce_email_from_address');
	return $from_email;
}

function aw_cc_from_name( $name ) {
	$from_name = get_option('woocommerce_email_from_name');
	return $from_name;
}

function aw_cc_placeholders_replace( $string ) {
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

function aw_cc_display_actual_amount( $amount) {
	if ($amount<0) {
		return '-' . wp_kses_post(aw_cc_get_amount(aw_cc_convert_currency(abs($amount)))); 
	} else {
		return wp_kses_post(aw_cc_get_amount(aw_cc_convert_currency($amount))); 
	}
}

function aw_cc_display_default_ordered_currency_amount( $amount ) {
	if ($amount<0) {
		return '-' . wp_kses_post(aw_cc_get_amount(abs($amount))); 
	} else {
		return wp_kses_post(aw_cc_get_amount($amount)); 
	}
}

function aw_cc_kses_filter_allowed_html( $allowed, $context) {
	$allowed['style'] 				= array();
	$allowed['input']['type'] 		= array();
	$allowed['input']['name'] 		= array();
	$allowed['input']['value'] 		= array();
	$allowed['input']['id'] 		= array();
	$allowed['input']['class'] 		= array();
	$allowed['input']['placeholder'] = array();
	$allowed['input']['style'] 		= array();
	$allowed['span']['onclick'] 	= array();
	$allowed['button']['onclick'] 	= array();
	return $allowed;
} 



