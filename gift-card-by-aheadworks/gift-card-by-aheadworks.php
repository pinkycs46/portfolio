<?php
/**
 * Plugin Name: Gift Card By Aheadworks
 * Plugin URI: https://www.aheadworks.com/
 * Description: The Gift Card plugin enables Gift Card as a new type of product. With the help of it you can expand your business by increasing sales and attracting new customers via promoting brand awareness. Besides, you will offer your customers a nice gift option to their friends!
 * Author: Aheadworks
 * Author URI: https://www.aheadworks.com/
 * Version: 1.0.0
 * Woo: 7298470:384aea6f1cf6ce937af095cda958d684
 * Text Domain: gift-card-by-aheadworks
 *
 * @package gift-card-by-aheadworks
 *
 * Requires at least: 5.2.9
 * Tested up to: 5.6
 * WC requires at least: 3.8.0
 * WC tested up to: 4.8.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

require_once(plugin_dir_path(__FILE__) . 'includes/aw-gift-card-admin.php'); 
require_once(plugin_dir_path(__FILE__) . 'includes/aw-gift-card-configuration.php'); 
require_once(plugin_dir_path(__FILE__) . 'includes/aw-gift-card-cartpage.php'); 
require_once(plugin_dir_path(__FILE__) . 'includes/aw-gift-card-checkoutpage.php'); 
require_once(plugin_dir_path(__FILE__) . 'includes/aw-gift-code-list-admin.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-gift-card-my-gift-cards.php');

$awgiftcard = new AwGiftCard();

/** Present plugin version **/
define( 'AW_GIFT_CARD_VERSION', '1.0.0' );
define( 'ACTIVE', 'Activated' );
define( 'DEACTIVE', 'Deactivated' );
define( 'INACTIVE', 'Inactive' );
define( 'USED', 'Used' );
define( 'EXPIRED', 'Expired' );
define( 'REFUND', 'Refund' );
define( 'UPDATED', 'Updated' );

class AwGiftCard {

	public $GLOBALS;
	public static $aw_giftcard_settings;
	public function __construct() {

		self::$aw_giftcard_settings = maybe_unserialize(get_option('aw_wgc_configuration'));
		/* Constructor function, initialize and register hooks */
		add_action('admin_init', array(get_called_class(),'aw_gift_card_installer'));
		register_uninstall_hook(__FILE__, array(get_called_class(),'aw_gift_card_unistaller'));

		/* Admin Javascript files */
		add_action('admin_enqueue_scripts', array(get_called_class(),'aw_gift_card_admin_addScript'));
		add_action('wp_enqueue_scripts', array(get_called_class(),'aw_gift_card_public_addScript'), 20);

		add_action( 'wp_ajax_aw_gift_code_apply_ajax', 'aw_gift_code_apply_ajax');
		add_action( 'wp_ajax_nopriv_aw_gift_code_apply_ajax', 'aw_gift_code_apply_ajax');	
		add_action( 'wp_ajax_aw_gift_code_remove_ajax', 'aw_gift_code_remove_ajax');
		add_action( 'wp_ajax_nopriv_aw_gift_code_remove_ajax', 'aw_gift_code_remove_ajax');

		add_action( 'wp_ajax_aw_gift_code_check_coupon_and_gift', 'aw_gift_code_check_coupon_and_gift');
		add_action( 'wp_ajax_nopriv_aw_gift_code_check_coupon_and_gift', 'aw_gift_code_check_coupon_and_gift');

		add_filter( 'woocommerce_calculated_total', 'aw_giftcard_subtract_giftamount_in_carttotals', 10, 3 );

		add_action('woocommerce_cart_totals_before_order_total', 'aw_gift_code_apply_points_cart_total');

		add_action('woocommerce_thankyou', array(get_called_class(),'aw_gift_code_action_new_order_received'), 10);
		add_action( 'admin_menu', array('AwGiftCardList','aw_gc_register_card_detail_page' ) );

		add_filter('woocommerce_account_menu_items', array('AwGiftCardMyGiftCards', 'aw_gc_account_menu_items'));

		/****Woocommerce Hook - Add endpoint title.****/
		add_filter('woocommerce_get_query_vars', array('AwGiftCardMyGiftCards', 'aw_gc_account_menu_history_query_vars'), 0);
		add_filter('woocommerce_endpoint_my-gift-cards_title', array('AwGiftCardMyGiftCards', 'aw_gc_users_gift_card_endpoint_title'), 0);
		/****Woocommerce Hook - Add endpoint title.****/

		add_action('woocommerce_account_my-gift-cards_endpoint', array('AwGiftCardMyGiftCards', 'aw_gc_account_menu_items_endpoint_content'));
		add_action('wp_ajax_aw_gc_add_new_gift_card_ajax', array('AwGiftCardMyGiftCards','aw_gc_add_new_gift_card_ajax'));

		add_action( 'woocommerce_order_refunded', array(get_called_class(),'aw_gc_giftcard_reverse_by_button'));

		add_action('woocommerce_order_status_changed', array(get_called_class(),'aw_gc_giftcard_order_status_notifier'), 10, 4);

		add_action('in_admin_header', array(get_called_class(),'aw_gc_giftcard_create_admin_header'));

		add_action( 'woocommerce_admin_order_data_after_order_details', array(get_called_class(),'aw_gc_giftcard_admin_order_details'), 10, 1 );

		//add_filter( 'woocommerce_order_item_get_formatted_meta_data', array(get_called_class(),'aw_wgc_removehyperlink_order_item_metadata'), 10, 2);
	}

	public static function aw_gift_card_installer() {
		global $wpdb;	
		if (is_admin()) {
			if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
				/** If WooCommerce plugin is not activated show notice **/
				add_action('admin_notices', array('AwGiftCardAdmin','aw_gc_self_deactivate_notice'));
				/** Deactivate our plugin **/
				deactivate_plugins(plugin_basename(__FILE__));
				if (isset($_GET['activate'])) {
					unset($_GET['activate']);
				}
			} else {
				if ('completed' === get_option( 'aw-gift-card-aheadworks' )) {
					update_option('AW_GIFT_CARD_VERSION', AW_GIFT_CARD_VERSION );
					aw_gc_change_code_update_active_to_expired();
					return ;
				}
				add_rewrite_endpoint( 'my-gift-cards', EP_ROOT | EP_PAGES  );
				update_option( 'aw-gift-card-aheadworks', 'completed' );
				$option_array = array(
									'status' 				=> 'wc-completed',
									'expiration' 			=> null,
									'length' 				=> 12,
									'format' 				=> 'alphanumeric',
									'prefix' 				=> '',
									'suffix' 				=> '',
									'dash_position' 		=> '',
								);
				$settings = maybe_serialize($option_array);
				update_option('aw_wgc_configuration', $settings);
				WC_Tax::create_tax_class( 'taxfree', 'taxfree' );
				flush_rewrite_rules();

				wp_deregister_script( 'autosave' );

				global $wpdb;
				$db_aw_gc_codes_table			= $wpdb->prefix . 'aw_gc_codes';
				$db_aw_gc_transactions_table 	= $wpdb->prefix . 'aw_gc_transactions';
				$db_aw_gc_ugc_table 			= $wpdb->prefix . 'aw_gc_users_gift_card';
				
				$charset_collate = $wpdb->get_charset_collate();
				//Check to see if the table exists already, if not, then create it
				if ($wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ' , "{$wpdb->prefix}aw_gc_codes")) != $db_aw_gc_codes_table) {
					$sql = "CREATE TABLE {$wpdb->prefix}aw_gc_codes (
							  `id` bigint(20) NOT NULL auto_increment,
							  `product_id` bigint(20) NOT NULL,
							  `order_id` text NOT NULL,
							  `giftcard_code` text NOT NULL,
							  `giftcard_product_name` text NOT NULL,
							  `giftcard_amount` float NOT NULL,
							  `giftcard_used_amount` float NOT NULL,
							  `giftcard_balance` float NOT NULL,
							  `transaction_action` VARCHAR(200) NOT NULL,
							  `giftcard_trash_status` int(11) NOT NULL COMMENT '1=Active & 0=Trash',
							  `sender_name` varchar(55) NOT NULL,
							  `sender_email` varchar(55) NOT NULL,
							  `recipient_name` varchar(55) NOT NULL,
							  `recipient_email` varchar(55) NOT NULL,
							  `email_heading` text NOT NULL,
							  `gift_description` text NOT NULL,
							  `expiration_date` date DEFAULT NULL,
							  `created_date` datetime NOT NULL,
							  PRIMARY KEY (`id`)
							);"	;
					require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
					dbDelta($sql);
				}
				if ($wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ' , "{$wpdb->prefix}aw_gc_transactions")) != $db_aw_gc_transactions_table) {
					$sql = "CREATE TABLE {$wpdb->prefix}aw_gc_transactions (
							  `id` bigint(20) NOT NULL auto_increment,
							  `giftcard_id` bigint(20) NOT NULL,
							  `order_id` text NOT NULL,
							  `balance_change` float NOT NULL,
							  `balance` float NOT NULL,
							  `transaction_action` varchar(20) NOT NULL,
							  `used_by_name` varchar(55) NOT NULL,
							  `used_by_email` varchar(55) NOT NULL,
							  `transaction_description` text NOT NULL,
							  `transaction_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
							  PRIMARY KEY (`id`)
							);"	;
					require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
					dbDelta($sql);
				}
				if ($wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ' , "{$wpdb->prefix}aw_gc_users_gift_card")) != $db_aw_gc_ugc_table) {
					$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}aw_gc_users_gift_card (
							  `id` bigint(20) NOT NULL AUTO_INCREMENT,
							  `aw_gc_codes_id` bigint(20) NOT NULL COMMENT 'foreign ket to {id} of aw_gc_codes table',
							  `user_id` bigint(20) NOT NULL,
							  `created_date` datetime NOT NULL,
							  PRIMARY KEY (`id`)
							);"	;
					require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
					dbDelta($sql);
				}
			}
		}
	}

	public static function aw_gift_card_unistaller() {
	/*Perform required operations at time of plugin uninstallation*/
		global $wpdb;
		delete_option('aw-gift-card-aheadworks');
		delete_option('AW_GIFT_CARD_VERSION');
		delete_option('aw_wgc_configuration');
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aw_gc_codes");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aw_gc_transactions");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aw_gc_users_gift_card");
	}

	public static function aw_gift_card_admin_addScript() {
		$path 		= parse_url(get_option('siteurl'), PHP_URL_PATH);
		$host 	 	= parse_url(get_option('siteurl'), PHP_URL_HOST);
		$wgc_nonce	= wp_create_nonce('aw_giftcard_admin_nonce');

		$page 	= '';
		$post 	= '';

		wp_register_style('awgiftcardadmincss', plugins_url('/admin/css/aw-gift-card-admin.css', __FILE__ ), array(), '1.0' );
		wp_enqueue_style('awgiftcardadmincss');

		wp_register_script('awgiftcardadminjs', plugins_url('/admin/js/aw-gift-card-admin.js', __FILE__ ), array(), '1.0' );
		$js_wgc_var = array('site_url' => get_option('siteurl'), 'path' => $path, 'host' => $host, 'aw_wgc_nonce' => $wgc_nonce);
		wp_localize_script('awgiftcardadminjs', 'js_wgc_var', $js_wgc_var);
		wp_register_script('awgiftcardadminjs', plugins_url('/admin/js/aw-gift-card-admin.js', __FILE__ ), array(), '1.0' );
		wp_enqueue_script('awgiftcardadminjs');
	}

	public static function aw_gift_card_public_addScript() {
		$path = parse_url(get_option('siteurl'), PHP_URL_PATH);
		$host = parse_url(get_option('siteurl'), PHP_URL_HOST);
		$wgc_nonce = wp_create_nonce('aw_giftcard_public_nonce');
		/** Add Plugin CSS and JS files Public Side **/

		wp_register_style('awgiftcardpubliccss', plugins_url('/public/css/aw-gift-card-public.css', __FILE__ ), array(), '1.0' );
		wp_enqueue_style('awgiftcardpubliccss');

		wp_register_script('awgiftcardpublicjs', plugins_url('/public/js/aw-gift-card-public.js', __FILE__ ), array(), '1.0' );
		$js_wgc_var = array('site_url' => get_option('siteurl'), 'path' => $path, 'host' => $host, 'aw_wgc_front_nonce' => $wgc_nonce);
		wp_localize_script('awgiftcardpublicjs', 'js_wgc_var', $js_wgc_var);
		wp_register_script('awgiftcardpublicjs', plugins_url('/public/js/aw-gift-card-public.js', __FILE__ ), array(), '1.0' );
		wp_enqueue_script('awgiftcardpublicjs');
	}

	public static function aw_gc_check_order_action( $order_id ) {
		global $wpdb;
		$db_transa_table 	= $wpdb->prefix . 'aw_gc_transactions';
		$settings  = maybe_unserialize(get_option('aw_wgc_configuration'));
		$mail_send = false;
		$gift_card = array();
		$giftprice = array();
		$gift_name = array();
		$item_id   = array();
		$redeemed_gift = array();
		$order_meta_id = array();
		if ('completed' != get_option( 'aw-gift-card-aheadworks' )) {
			return;
		}
		//** If gift card applied on purchase of gift card **//
		$appliedgifts = maybe_unserialize(get_post_meta($order_id, 'gift_info', true));
		if (!empty($appliedgifts)) {
			$redeemed_gift = explode(',', get_post_meta($order_id, '_awgc_redeemed_cards', true));
		}
		//***//
		$order 		= wc_get_order($order_id);
		$ordertotal = $order->get_total();

		foreach ( $order->get_items() as $item_id => $item ) {
			$formatted_meta_data = $item->get_formatted_meta_data( '_', true );
			$product_id		= $item->get_variation_id();
			$order_status 	= get_post_status($order_id);
			$product_obj 	= wc_get_product($item->get_product_id());
			$quantity   	= $item->get_quantity();
			$price 			= get_post_meta($product_id, '_price', true);

			if (( empty($product_id) || 0 == $product_id ) && $product_obj->is_type('gift_card_virtual')) {
				$product = new WC_Product_Variable($item['product_id']);
				$variations = $product->get_children();
				$product_id = $variations[0];
				wc_update_order_item_meta( $item_id, '_variation_id', $product_id);
			}
			
			if ( $product_obj->is_type('gift_card_virtual') ) {
				$gift_info = get_post_meta($order_id, 'gift_info', true);
				if (!empty($gift_info)) {
					foreach ($gift_info as $info) {
						if ($product_id == $info['product_id']) {
							if (1 != $quantity) {
								$aw_wgc_amount = $price;
							} else {
								$aw_wgc_amount = aw_gift_code_convert_default_currency($item->get_subtotal());
							}
							$giftcard = array(
									'order_id'				=>	$order_id,	
									'product_id'			=>	$product_id,
									'giftcard_amount' 		=>	$aw_wgc_amount,
									'giftcard_balance' 		=>	$aw_wgc_amount,
									'giftcard_product_name'	=> 	$item->get_name(),
									'recipient_name' 		=>	trim($info['recipient_name']),
									'recipient_email' 		=> 	trim($info['recipient_email']),
									'sender_name' 			=>	trim($info['sender_name']),
									'sender_email' 			=>	trim($info['sender_email']),
									'email_heading'			=>	trim($info['email_heading']),
									'gift_description'		=>	trim($info['gift_description']),
									'transaction_action' 	=>	ACTIVE,
									'giftcard_trash_status'	=> 	1,
									'created_date'			=>	gmdate('Y-m-d H:i:s')
								);
							if ($settings['status'] === $order_status) {

								for ($x = $quantity; $x > 0; $x--) {
									$giftcode 	= self::aw_gift_card_save_giftcard($giftcard);
									$code_detail= (object) $giftcode;
									aw_gc_mail_giftcard_to_author($code_detail);
								}
							}

							$order_id   = $giftcard['order_id'];
							$product_id = $giftcard['product_id'];

							$result = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_codes WHERE `product_id` = %d AND `order_id` = %s ORDER BY `id` ASC ", "{$product_id}", "{$order_id}") );
							if ($result) {
								foreach ($result as $value) {
									$id = wc_add_order_item_meta( $item_id, 'Gift_Card', '<span class="aw_gc_hp_code" id="' . base64_encode($value->id) . '">' . $value->giftcard_code . '</span> (' . $value->recipient_email . ')');
									$order_meta_id[] = $id;
								}
								if (!empty($redeemed_gift)) {
									$aw_gc_redeemed_amt = aw_gift_code_convert_default_currency( get_post_meta($order_id, '_awgc_redeemed_amt', true));
									foreach ($redeemed_gift as $key=> $redeemed_code) {
										$giftrecord = check_gift_code_validate( trim($redeemed_code));
										if ( !empty($giftrecord)) {
											$gift_original_bal_amt =aw_gift_code_convert_default_currency($giftrecord->giftcard_balance);
											$gift_default_used_amt = $giftrecord->giftcard_used_amount;
											if ( !empty($giftrecord) && $gift_original_bal_amt <= $aw_gc_redeemed_amt) {
												$balance_change = $gift_original_bal_amt;
												$balance = 0;
												$aw_gc_redeemed_amt = $aw_gc_redeemed_amt - $gift_original_bal_amt;
												$status = USED;
											} else {
												if ( !empty($giftrecord)) {
													$aw_gc_redeemed_amt = abs($gift_original_bal_amt - $aw_gc_redeemed_amt);
													$balance 		= $aw_gc_redeemed_amt;
													$balance_change = $gift_original_bal_amt -$balance;
													$status = UPDATED;	
												}
													
											}
											$order_status = $status;

											$update_array = array(
														'transaction_action'	=> $order_status,
														'id'					=> $giftrecord->id,
														'giftcard_used_amount'	=> $balance_change+$gift_default_used_amt,
														'giftcard_balance'		=> $balance,
														'transaction_action'	=> $status
													);
											aw_gc_upate_giftcard($update_array);

											$trans_array = array(
																'giftcard_id' 	=> $giftrecord->id,
																'order_id' 		=> $order_id,
																'balance_change'=> -$balance_change,
																'balance'		=> $balance,
																'transaction_action' => $status,
																'transaction_description'=>'Applied to order #' . $order_id 
															);
											$result = $wpdb->insert($db_transa_table, $trans_array);
											unset($redeemed_gift[$key]);
										}
									}
								}
							}
						}
					}
				}
			}
		}
		update_post_meta($order_id, '_order_meta_id', maybe_serialize($order_meta_id));
	}

	public static function aw_gift_card_generate_code( $aw_giftcard_settings = '') {
		if (empty($aw_giftcard_settings)) {
			return false;
		}		 	
		$dash_position 	= $aw_giftcard_settings['dash_position'];
		$prefix 		= $aw_giftcard_settings['prefix'];
		$suffix			= $aw_giftcard_settings['suffix'];
		$length 		= $aw_giftcard_settings['length'];

		switch ($aw_giftcard_settings['format']) {
			case 'numeric': 
					$permitted_chars = '0123456789';
				break;
			case 'alphabetic': 
					$permitted_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
				break;
			case 'alphanumeric': 
					$permitted_chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
				break;
		}
		$charactersLength = strlen($permitted_chars);
		$randomString = $prefix;

		for ($i = 1; $i < $length+1; $i++) {
			$randomString .= $permitted_chars[rand(0, $charactersLength - 1)];

			if (''!=$dash_position && 0 == ( $i % $dash_position )) {
				$randomString .= '-';
			}
		}
		$randomString 	= rtrim($randomString, '-');
		$randomString	.= $suffix;
		return $randomString;
	}
	public static function aw_gift_card_save_giftcard( $order ) {
		global $wpdb;
		$gc_code_table 			= $wpdb->prefix . 'aw_gc_codes';
		$db_transcation_table	= $wpdb->prefix . 'aw_gc_transactions';
		if ( !empty($order) && isset($order['product_id']) ) {
				$aw_giftcard_settings = self::$aw_giftcard_settings;
				$code 	= self::aw_gift_card_generate_code($aw_giftcard_settings);
				$order['giftcard_code']		= $code;
			if ( null!=$aw_giftcard_settings['expiration'] ) {
				$Today	= gmdate('y-m-d');
				$days 	= '+' . $aw_giftcard_settings['expiration'] . ' days';
				$order['expiration_date'] 	= gmdate('y-m-d', strtotime($days));	
			} else {
				$order['expiration_date'] 	= null;
			}
				$wpdb->insert($gc_code_table, $order);
				$lastid = $wpdb->insert_id;
				$wpdb->insert( $db_transcation_table, array (
							'giftcard_id'      			=> $lastid,
							'order_id'      			=> $order['order_id'],
							'balance_change'       		=> 0,
							'balance'					=> $order['giftcard_balance'],
							'transaction_action'		=> ACTIVE,
							'transaction_description'	=> 'Purchased, Order #' . $order['order_id'] ,
						));
				return $order;
		}
	}

	public static function aw_gc_giftcard_forward( $order_id, $order_status = '') {
		global $wpdb;
		$settings 			= maybe_unserialize(get_option('aw_wgc_configuration'));
		$order 				= wc_get_order($order_id);
		$order_data 		= $order->get_data();
		$order_status 		= get_post_status($order_id);
		$order_grand_total 	= $order_data['total'];
		$status 			= '';
		$db_coupon_table 	= $wpdb->prefix . 'aw_gc_codes';
		$db_transa_table 	= $wpdb->prefix . 'aw_gc_transactions';
		$reverse_status 	= array('wc-cancelled','wc-refunded','wc-failed');
		$forward_status 	= array('wc-on-hold','wc-processing','wc-completed','wc-pending');

		$giftrecords = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_codes WHERE `order_id` = %s " , "{$order_id}"));

		if ( $settings['status'] === $order_status) {
			$status	= ACTIVE;
		} elseif (in_array($order_status, $reverse_status)) {
			$status	= DEACTIVE;
			$trans_desc 	= 'Reimbursed for refunded Order #' . $order_id;
		} else {
			if (!in_array($order_status, $forward_status)) {
				$status	= INACTIVE;
			}
		}
		//aw_gc_change_code_update_active_to_expired();
		if (!empty($giftrecords)) {
			foreach ($giftrecords as $giftrecord) {
				$balance_change = $giftrecord->giftcard_used_amount;
				$balance 	 	= $giftrecord->giftcard_balance;
				$gift_amount  	= $giftrecord->giftcard_amount;
				$giftcard_id 	= $giftrecord->id;	

				if (DEACTIVE != $giftrecord->transaction_action && '' != $status) {
						$code_array = array(
										'id'					=> $giftcard_id,
										'giftcard_used_amount'	=> $balance_change,
										'giftcard_balance'		=> $balance,
										'transaction_action'	=> $status
										); 
						aw_gc_upate_giftcard($code_array);
						$trans_array= array(
											'giftcard_id' 		=> $giftcard_id,
											'order_id' 			=> $order_id,
											'balance_change'	=> $balance_change,
											'balance'			=> $balance,
											'transaction_action'=> $status,
											'transaction_description'=> ucfirst($order->get_status()) . ', Order #' . $order_id
											);
						$result = $wpdb->insert($db_transa_table, $trans_array);
				}
			}
			
		} else {
			// This part for order after apply gift card and process 
			$trans_records = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT(giftcard_id) FROM {$wpdb->prefix}aw_gc_transactions WHERE `order_id` = %s AND `transaction_action` != %s  " , "{$order_id}" , 'Expired'));
			if (!empty($trans_records)) {
				foreach ($trans_records as $transaction) {

					$allowed 		= true;
					$giftrecord 	= $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_codes WHERE `id` = %d AND ( `transaction_action` != %s) " , "{$transaction->giftcard_id}", 'Expired'));

					$trans_record 	= $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_transactions WHERE `giftcard_id` = %d AND `order_id` = %s ORDER BY id DESC LIMIT %d"  , "{$giftrecord->id}" , "{$order_id}" , 1));

					$search = 'Applied';
					if (preg_match("/{$search}/", $trans_record->transaction_description)) {
						$allowed = false;
					}

					$balance 		= abs($trans_record->balance) - $trans_record->balance_change ;	
					$balance_change = abs($trans_record->balance_change);
					if ($trans_record->balance_change === $giftrecord->giftcard_amount) {
						$balance_change = abs($trans_record->balance_change);
						$balance 		= 0;
						$status = USED;
					} else {
						$status = UPDATED;
					}

					if ($allowed) {
						$update_array = array(
												'transaction_action'	=> $order_status,
												'id'					=> $giftrecord->id,
												'giftcard_used_amount'	=> $giftrecord->giftcard_amount-$balance,
												'giftcard_balance'		=> $balance,
												'transaction_action'	=> $status
											);
						aw_gc_upate_giftcard($update_array);

						$trans_array = array(
											'giftcard_id' 	=> $giftrecord->id,
											'order_id' 		=> $order_id,
											'balance_change'=> -$balance_change,
											'balance'		=> $balance,
											'transaction_action' => $status,
											'transaction_description'=>'Applied to order #' . $order_id 
										);
						$result = $wpdb->insert($db_transa_table, $trans_array);
					}
				}
			}

		}
	}	 
	

	public static function aw_gc_giftcard_reverse_by_button( $order_id, $order_status = '') {
		global $wpdb;
		$settings 			= maybe_unserialize(get_option('aw_wgc_configuration'));
		$order 				= wc_get_order($order_id);
		$order_data 		= $order->get_data();
		$order_grand_total 	= $order_data['total'];
		$order_status 		= get_post_status($order_id);
		$gc_code_table 		= $wpdb->prefix . 'aw_gc_codes';
		$db_transa_table 	= $wpdb->prefix . 'aw_gc_transactions';
		$reverse_status 	= array('wc-cancelled','wc-refunded','wc-failed');
		$forward_status 	= array('wc-on-hold','wc-processing','wc-completed','wc-pending');
		$item_qty_refunded 	= array();
		$item_id_refunded 	= array();
		$product_ids 		= array();
		$giftrecords 		= array();
		$data 				= array();
		$button_refunded 	= 0;
		$balance_change  	= 0;
		$balance 			= 0;
		$quantity 			= 0;

		if ( isset($_REQUEST['edit_order_detail_nonce'])) {
			$edit_order_detail_nonce = sanitize_text_field($_REQUEST['edit_order_detail_nonce']);

			if ( !wp_verify_nonce($edit_order_detail_nonce, 'aw_gc_edit_order_detail_action')) {
				wp_die('Our Site is protected');
			}
		}
		
		//aw_gc_change_code_update_active_to_expired();
		$coderesult = aw_get_ordered_gift_codes( $order_id );
		//if (in_array($order_status, $reverse_status) && !empty($coderesult)) {
		if (isset($_POST['line_item_qtys']) && !empty($_POST['line_item_qtys']) && !empty($coderesult)) {

			$json_response = stripslashes(sanitize_text_field($_POST['line_item_qtys']));
			$data = json_decode($json_response, true);
			$item_id_refunded 	= array_keys($data);
			$item_qty_refunded 	= array_values($data);
			$quantity 			= count($item_qty_refunded);				

			$order = new WC_Order( $order_id );
			$items = $order->get_items();
			$order_meta_ids = maybe_unserialize(get_post_meta( $order_id, '_order_meta_id', true));
			$count = 0;
			foreach ( $items as $order_item_id=>$item ) {
				$item_data = $item->get_data();
				if (0 != $item_data['variation_id'] && ( in_array($order_item_id, $item_id_refunded) )) {
					$product_ids 	= $item_data['variation_id'];
					if (isset($data[$order_item_id])) {
						$quantity 		= $data[$order_item_id];
					} else {
						$quantity 		= count($item_data);
					}
					
					$results = $wpdb->get_results($wpdb->prepare("SELECT `meta_id`,`meta_value` FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE `meta_key` = %s AND `order_item_id` = %d AND `meta_value` NOT LIKE %s  ORDER BY `meta_id` ASC LIMIT %d", 'Gift_Card', "{$order_item_id}" , '%<a style="color:red"%' , "{$quantity}"));
					if (!empty($results)) {
						foreach ($results as $value) {
							$text	= '';
							$text 	= preg_replace('/<a /', '<a style="color:red" ', $value->meta_value);
							$text = $text . '<span style="color:red"> -Refunded </span>';
							$wpdb->query($wpdb->prepare("UPDATE  {$wpdb->prefix}woocommerce_order_itemmeta SET  `meta_value` =%s WHERE `meta_key`=%s AND `meta_id` = %d", "{$text}" , 'Gift_Card', "{$value->meta_id}"));	
							
							unset($order_meta_ids[$count]);
							$gift = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_codes WHERE `order_id` = %s AND `transaction_action` != %s  AND ( `transaction_action` = %s OR `transaction_action` = %s OR `transaction_action` = %s) AND `product_id` = %d ORDER BY `id` ASC LIMIT %d" , "{$order_id}", 'Expired', 'Used', 'Activated', 'Updated', "{$product_ids}", 1));
							$button_refunded 	= 1;
							$order_status 		= get_post_status($order_id);
							$reverse_status 	= array($order_status);
							if ($settings['status'] === $order_status && 0 == $button_refunded ) {
								$status	= ACTIVE;
							} elseif (in_array($order_status, $reverse_status)) {
								$status	= DEACTIVE;
							} else {
								if (!in_array($order_status, $forward_status)) {
									$status	= INACTIVE;	
								}
							}
							if (!empty($gift)) {
								$balance_change = $gift->giftcard_used_amount;
								$balance 	 	= $gift->giftcard_balance;
								$gift_amount  	= $gift->giftcard_amount;
								$giftcard_id 	= $gift->id;	
								$code_array 	= array(
														'id'					=> $giftcard_id,
														'giftcard_used_amount'	=> $balance_change,
														'giftcard_balance'		=> $balance,
														'transaction_action'	=> $status
														); 

								if (INACTIVE == $gift->transaction_action || ACTIVE == $gift->transaction_action || USED == $gift->transaction_action || UPDATED == $gift->transaction_action || 1 == $button_refunded ) {
									aw_gc_upate_giftcard($code_array);
									$trans_array = array(
														'giftcard_id' 		=> $giftcard_id,
														'order_id' 			=> $order_id,
														'balance_change'	=> $balance_change,
														'balance'			=> $balance,
														'transaction_action'=> $status,
														'transaction_description'=>'Refund/Cancel/Failed, Order #' . $order_id 
														);
									$wpdb->insert($db_transa_table, $trans_array);
									$count++;
								}
							}	
						}
					}
				}
			}
			$order_meta_ids = array_values($order_meta_ids);
			update_post_meta($order_id, '_order_meta_id', maybe_serialize($order_meta_ids));

		} 
	}

	public static function aw_gc_giftcard_reverse( $order_id, $order_status = '') {
		
		global $wpdb;
		$settings 			= maybe_unserialize(get_option('aw_wgc_configuration'));
		$order 				= wc_get_order($order_id);
		$order_data 		= $order->get_data();
		$order_grand_total 	= $order_data['total'];
		$order_status 		= get_post_status($order_id);
		$gc_code_table 		= $wpdb->prefix . 'aw_gc_codes';
		$db_transa_table 	= $wpdb->prefix . 'aw_gc_transactions';
		$reverse_status 	= array('wc-cancelled','wc-refunded','wc-failed');
		$forward_status 	= array('wc-on-hold','wc-processing','wc-completed','wc-pending');
		$item_qty_refunded 	= array();
		$item_id_refunded 	= array();
		$product_ids 		= array();
		$giftrecords 		= array();
		$data 				= array();
		$button_refunded 	= 0;
		$balance_change  	= 0;
		$balance 			= 0;
		$quantity 			= 0;

		if ( isset($_REQUEST['edit_order_detail_nonce'])) {
			$edit_order_detail_nonce = sanitize_text_field($_REQUEST['edit_order_detail_nonce']);

			if ( !wp_verify_nonce($edit_order_detail_nonce, 'aw_gc_edit_order_detail_action')) {
				wp_die('Our Site is protected');
			}
		}
		if ( isset($_POST['line_item_qtys']) && !empty($_POST['line_item_qtys'])) {
			return;
		}
		//aw_gc_change_code_update_active_to_expired();
		$coderesult = aw_get_ordered_gift_codes( $order_id );
		if (in_array($order_status, $reverse_status) && !empty($coderesult)) {
			if (isset($_POST['line_item_qtys'])) {
				$json_response = stripslashes(sanitize_text_field($_POST['line_item_qtys']));
				$data = json_decode($json_response, true);
				$item_id_refunded 	= array_keys($data);
				$item_qty_refunded 	= array_values($data);
				$quantity 			= count($item_qty_refunded);				
			}
			$order = new WC_Order( $order_id );
			$items = $order->get_items();
			$order_meta_ids = maybe_unserialize(get_post_meta( $order_id, '_order_meta_id', true));
			$count = 0;
			foreach ( $items as $order_item_id=>$item ) {

				$item_data = $item->get_data();
				if (0 != $item_data['variation_id'] && in_array($order_status, $reverse_status) ||( in_array($order_item_id, $item_id_refunded) )) {
					$product_ids 	= $item_data['variation_id'];
					if (isset($data[$order_item_id])) {
						$quantity 		= $data[$order_item_id];
					} else {
						$quantity 		= count($item_data);
					}
					
					$results = $wpdb->get_results($wpdb->prepare("SELECT `meta_id`,`meta_value` FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE `meta_key` = %s AND `order_item_id` = %d AND `meta_value` NOT LIKE %s  ORDER BY `meta_id` ASC LIMIT %d", 'Gift_Card', "{$order_item_id}" , '%<a style="color:red"%' , "{$quantity}"));
					if (!empty($results)) {
						foreach ($results as $value) {
							$text	= '';
							$text 	= preg_replace('/<a /', '<a style="color:red" ', $value->meta_value);
							$text = $text . '<span style="color:red"> -Refunded </span>';
							$wpdb->query($wpdb->prepare("UPDATE  {$wpdb->prefix}woocommerce_order_itemmeta SET  `meta_value` =%s WHERE `meta_key`=%s AND `meta_id` = %d", "{$text}" , 'Gift_Card', "{$value->meta_id}"));	
							
							unset($order_meta_ids[$count]);
							$gift = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_codes WHERE `order_id` = %s AND `transaction_action` != %s  AND ( `transaction_action` = %s OR `transaction_action` = %s OR `transaction_action` = %s) AND `product_id` = %d ORDER BY `id` ASC LIMIT %d" , "{$order_id}", 'Expired', 'Used', 'Activated', 'Updated', "{$product_ids}", 1));
							$button_refunded 	= 1;
							$order_status 		= get_post_status($order_id);
							$reverse_status 	= array($order_status);
							if ($settings['status'] === $order_status && 0 == $button_refunded ) {
								$status	= ACTIVE;
							} elseif (in_array($order_status, $reverse_status)) {
								$status	= DEACTIVE;
							} else {
								if (!in_array($order_status, $forward_status)) {
									$status	= INACTIVE;	
								}
							}
							if (!empty($gift)) {
								$balance_change = $gift->giftcard_used_amount;
								$balance 	 	= $gift->giftcard_balance;
								$gift_amount  	= $gift->giftcard_amount;
								$giftcard_id 	= $gift->id;	
								$code_array 	= array(
														'id'					=> $giftcard_id,
														'giftcard_used_amount'	=> $balance_change,
														'giftcard_balance'		=> $balance,
														'transaction_action'	=> $status
														); 

								if (INACTIVE == $gift->transaction_action || ACTIVE == $gift->transaction_action || USED == $gift->transaction_action || UPDATED == $gift->transaction_action || 1 == $button_refunded ) {
									aw_gc_upate_giftcard($code_array);
									$trans_array = array(
														'giftcard_id' 		=> $giftcard_id,
														'order_id' 			=> $order_id,
														'balance_change'	=> $balance_change,
														'balance'			=> $balance,
														'transaction_action'=> $status,
														'transaction_description'=>'Refund/Cancel/Failed, Order #' . $order_id 
														);
									$wpdb->insert($db_transa_table, $trans_array);
									$count++;
								}
							}	
						}
					}
				}
			}
			$order_meta_ids = array_values($order_meta_ids);
			update_post_meta($order_id, '_order_meta_id', maybe_serialize($order_meta_ids));

		} else {
			$giftrecords = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_codes WHERE `order_id` = %s AND `transaction_action` != %s AND (`transaction_action` = %s OR `transaction_action` = %s OR `transaction_action` = %s)" , "{$order_id}", 'Expired', 'Used', 'Updated', 'Activated'));
		}
		if (!empty($giftrecords)) {
			if ($settings['status'] === $order_status && 0 == $button_refunded ) {
				$status	= ACTIVE;
			} elseif (in_array($order_status, $reverse_status)) {
				$status	= DEACTIVE;
			} else {
				if (!in_array($order_status, $forward_status)) {
					$status	= INACTIVE;	
				}
			}

			foreach ($giftrecords as $gift) {
				$balance_change = $gift->giftcard_used_amount;
				$balance 	 	= $gift->giftcard_balance;
				$gift_amount  	= $gift->giftcard_amount;
				$giftcard_id 	= $gift->id;	
				$code_array 	= array(
										'id'					=> $giftcard_id,
										'giftcard_used_amount'	=> $balance_change,
										'giftcard_balance'		=> $balance,
										'transaction_action'	=> $status
										); 
				 
				if (INACTIVE == $gift->transaction_action || ACTIVE == $gift->transaction_action || USED == $gift->transaction_action || UPDATED == $gift->transaction_action || 1 == $button_refunded ) {
					aw_gc_upate_giftcard($code_array);
					$trans_array = array(
										'giftcard_id' 		=> $giftcard_id,
										'order_id' 			=> $order_id,
										'balance_change'	=> $balance_change,
										'balance'			=> $balance,
										'transaction_action'=> $status,
										'transaction_description'=>'Refund/Cancel/Failed, Order #' . $order_id 
										);
					$result 	= $wpdb->insert($db_transa_table, $trans_array);
				}
			}
		} else {
			aw_gc_make_order_reverse($order, $order_id, $order_status , $reverse_status);
			echo '1' ;
		}
	}

	public static function aw_gift_code_action_new_order_received( $order_id, $code_detail = null) {
		global $wpdb;
		$trans_array				= array();
		$gift_code 					= array();
		$db_transcation_table 		= $wpdb->prefix . 'aw_gc_transactions';
		$db_coupon_table 			= $wpdb->prefix . 'aw_gc_codes';
		$apply_point_cookie 		= $wpdb->prefix . 'woocommerce_InD9QULI3ct';
		$giftcoupon_cookie 			= $wpdb->prefix . 'woocommerce_RD14QULDiAW';
		$remainingbalance 			= $wpdb->prefix . 'woocommerce_REMAING1iaW';
		$myremainingbalance 		= $wpdb->prefix . 'woocommerce_M1YiR5E7MBA';
		$settings 					= maybe_unserialize(get_option('aw_wgc_configuration'));

		$order 						= new WC_Order($order_id);
		$code_detail 				= aw_get_ordered_gift_code_detail( $order_id );
		$order_data 				= $order->get_data();
		$order_status 				= get_post_status($order_id);
		$user_id 					= $order->get_user_id();
		$order_sub_total 			= aw_gift_code_convert_default_currency((float) $order->get_subtotal() + $order->get_total_tax()+ $order->get_shipping_total());
		
		$order_billing_first_name 	= $order_data['billing']['first_name'];
		$order_billing_last_name 	= $order_data['billing']['last_name'];
		$used_by_name 				= $order_billing_first_name . ' ' . $order_billing_last_name;
		$used_by_email 				= $order_data['billing']['email'];
		$transaction_description	= 'Applied to order #' . $order_id;
		$transaction_action 		= '';

		$redeemed_gift = array();
		$appliedgifts = maybe_unserialize(get_post_meta($order_id, 'gift_info' , true));
		if (!empty($appliedgifts)) {
			$redeemed_gift = explode(',', get_post_meta($order_id, '_awgc_redeemed_cards', true));
			$aw_gc_redeemed_amt = get_post_meta($order_id, '_awgc_redeemed_amt', true);
		}
		if (isset($_COOKIE[$apply_point_cookie]) && !empty($_COOKIE[$apply_point_cookie]) &&  isset($_COOKIE[$giftcoupon_cookie]) && !empty($_COOKIE[$giftcoupon_cookie]) ) {
			$fee_total	= get_post_meta($order_id , '_awgc_redeemed_amt', true);
			$fee_total	= aw_gift_code_convert_default_currency($fee_total);

			$gift_code 	= maybe_unserialize(base64_decode(( sanitize_text_field($_COOKIE[$giftcoupon_cookie]) )));
			if (is_array($gift_code)) {
				$gift_balance 		= aw_gift_code_convert_default_currency(aw_gc_get_user_total_balance());
			} else {
				$gift_code 			= base64_decode(( sanitize_text_field($_COOKIE[$giftcoupon_cookie]) ));
				$gift_detail 		= check_gift_code_validate($gift_code);
				if (!empty($gift_detail)) {

					$giftcard_id 		= $gift_detail->id;
					$giftcard_amount 	= $gift_detail->giftcard_amount;
					$gift_balance 		= aw_gift_code_get_transaction( $giftcard_id  );

					if ($gift_balance<=$fee_total) {
						$action_and_balance['balance']	= 0;
						$action_and_balance['action'] 	= USED;
					} else {
						$action_and_balance['balance'] 	= $gift_balance-(float) $fee_total;
						$action_and_balance['action'] 	= UPDATED;
					}
				}
			}
			if ($gift_balance > 0 ) {
					$giftcard_cart_amt 	= $gift_balance;
				if ($order_sub_total <= $fee_total) {
					$subtracted_amt 	= $order_sub_total;
				} else {
					$subtracted_amt 	= $fee_total;
				}
			}
			if (is_array($gift_code) && empty($appliedgifts)) {
				foreach ($gift_code as $giftcode) {
					$gift_detail 	= check_gift_code_validate($giftcode);
					$giftcard_id 	= $gift_detail->id;
					if (0 == $subtracted_amt ) {
						break;
					}
					if (aw_gift_code_convert_default_currency($gift_detail->giftcard_balance) <= $subtracted_amt ) {
						$action_and_balance = aw_get_action_of_transaction($subtracted_amt, aw_gift_code_convert_default_currency($gift_detail->giftcard_balance));
						$subtracted_amt 	= $subtracted_amt - aw_gift_code_convert_default_currency($gift_detail->giftcard_balance);	
						
						$changed_balance	= aw_gift_code_convert_default_currency($gift_detail->giftcard_balance);
						$total_used_amount 	= $gift_detail->giftcard_used_amount+$changed_balance;
					
					} else {
						$changed_balance 	= $subtracted_amt;
						$total_used_amount 	= $subtracted_amt; 
						$action_and_balance	= aw_get_action_of_transaction($subtracted_amt, aw_gift_code_convert_default_currency($gift_detail->giftcard_balance));
						$subtracted_amt 	= aw_gift_code_convert_default_currency($gift_detail->giftcard_balance) - $subtracted_amt;	
						if (aw_gift_code_convert_default_currency($gift_detail->giftcard_balance) > $subtracted_amt) {
							$subtracted_amt = 0;
						}
					} 	
					$wpdb->insert($db_transcation_table, array(
						'giftcard_id'      			=> $giftcard_id,
						'order_id'      			=> $order_id,
						'balance_change'       		=> -$changed_balance,
						'balance'					=> $action_and_balance['balance'],
						'transaction_action'		=> $action_and_balance['action'],
						'used_by_name'				=> $used_by_name,
						'used_by_email' 			=> $used_by_email,
						'transaction_description'	=> $transaction_description,
					));
					$update_array = array(
										'id'					=> $giftcard_id,
										'giftcard_used_amount' 	=> $total_used_amount,
										'giftcard_balance'		=> $action_and_balance['balance'],
										'transaction_action' 	=> $action_and_balance['action']
									);
					aw_gc_upate_giftcard($update_array);
				}
			} else {
				if (empty($appliedgifts)) {
					$wpdb->insert($db_transcation_table, array(
						'giftcard_id'      			=> $giftcard_id,
						'order_id'      			=> $order_id,
						'balance_change'       		=> -(float) $fee_total,
						'balance'					=> $action_and_balance['balance'],
						'transaction_action'		=> $action_and_balance['action'],
						'used_by_name'				=> $used_by_name,
						'used_by_email' 			=> $used_by_email,
						'transaction_description'	=> $transaction_description,
					));
					$total_used_amount = $gift_detail->giftcard_used_amount+(float) $fee_total; 
					$update_array = array(
									'id'					=> $giftcard_id,
									'giftcard_used_amount' 	=> $total_used_amount,
									'giftcard_balance'		=> $action_and_balance['balance'],
									'transaction_action' 	=> $action_and_balance['action']
								);
					aw_gc_upate_giftcard($update_array);
				}
			}
		} else {
			// When order recieved and status matched with setting then below function create giftcard and transaction.
			$record = aw_get_ordered_gift_code_detail($order_id);
			if (empty($record)) {
				self::aw_gc_check_order_action( $order_id );		
			}
		} 

		$path = '/';
		$host = parse_url(get_option('siteurl'), PHP_URL_HOST);
		setcookie($apply_point_cookie, '', time()-3600, $path, $host);
		setcookie($giftcoupon_cookie, '', time()-3600, $path, $host);
		setcookie($remainingbalance, '', time()-3600, $path, $host);
		setcookie($myremainingbalance, '', time()-3600, $path, $host);
		setcookie('aw_gc_checked_balance', '', time()-3600, $path, $host);
		unset($_COOKIE[$apply_point_cookie]);
		unset($_COOKIE[$giftcoupon_cookie]);
		unset($_COOKIE[$remainingbalance]);
		unset($_COOKIE[$myremainingbalance]);
		unset($_COOKIE['aw_gc_checked_balance']);
	}

	public static function aw_gc_giftcard_order_status_notifier( $order_id, $checkout = null, $status_transition_to, $instance ) {
		global $woocommerce;
		$reverse = array('wc-refunded','wc-cancelled','wc-failed');
		$settings = maybe_unserialize(get_option('aw_wgc_configuration'));

		$instance_data = maybe_unserialize($instance);
		$post_id = $instance_data->get_data()['id'];
		aw_gc_change_code_update_active_to_expired();
		if ( isset($post_id) && $post_id == $order_id ) {

			$order_status 	= get_post_status($order_id);
			$record 		= aw_gift_code_get_all_transaction($order_id);
		
			if ('wc-expired' == $order_status) {
				return;
			}
			if ( $settings['status'] === $order_status) {
				if (empty($record)) {
					// Create first code and transaction
					self::aw_gc_check_order_action( $order_id );		
				} else {
					self::aw_gc_giftcard_forward( $order_id , $order_status);	
				}	
			} elseif (in_array($order_status, $reverse)) {

				self::aw_gc_giftcard_reverse( $order_id , $order_status);

			} elseif (!in_array($order_status, $reverse)) {

				self::aw_gc_giftcard_forward( $order_id , $order_status);

			}  
		} elseif (isset($_REQUEST['post_status']) && 'all' === $_REQUEST['post_status']) {

			if (isset($_REQUEST['post'])) {
				$get_order_id 	= json_encode($_REQUEST);
				$get_order_id 	= wp_unslash($get_order_id);
				$get_order_id 	= json_decode($get_order_id, true);
				$order_ids 		= serialize(array_values(array_filter($get_order_id['post'])));
				$order_ids 		= unserialize($order_ids);
				foreach ($order_ids as $order_id) {

					$order_status 	= get_post_status($order_id);
					$record 		= aw_gift_code_get_all_transaction($order_id);

					if ('wc-expired' === $order_status) {
						continue;
					}

					$order_status = str_replace('wc-', 'mark_', $order_status);
					if (isset($_REQUEST['action']) && $order_status === $_REQUEST['action']) {
						if (empty($record)) {
							self::aw_gc_check_order_action( $order_id );		
						} else {
							self::aw_gc_giftcard_forward( $order_id , $order_status);
						}
					} elseif (in_array($order_status, $reverse)) {
						self::aw_gc_giftcard_reverse( $order_id , $order_status);
					} elseif (!in_array($order_status, $reverse)) {
						self::aw_gc_giftcard_forward( $order_id , $order_status);
					}
				}
			}
		}
	}

	public static function aw_gc_giftcard_create_admin_header() {
		$page_name = get_admin_page_title();

		if ('Configuration' ==  $page_name || 'Gift Card Codes' == $page_name || 'Gift Card Detail' == $page_name) {

			$sep = ' / ';
			if ('Gift Card Detail' == $page_name) {
				echo '<h3><a href="' . wp_kses_post(admin_url('/admin.php?page=aw_gift_card_codes')) . '" >Gift Card Codes</a>' . wp_kses_post($sep) . '' . wp_kses_post(get_admin_page_title()) . ' </h3>';
			} /*else {
				echo '<h3><a href="' . wp_kses_post(admin_url('/admin.php?page=aw_gift_card_configration')) . '" >Gift Card</a>' . wp_kses_post($sep) . '' . wp_kses_post(get_admin_page_title()) . ' </h3>';
			}*/
		}
	}

	public static function aw_gc_giftcard_admin_order_details( $order ) {
		wp_nonce_field( 'aw_gc_edit_order_detail_action', 'edit_order_detail_nonce' );
	}

	public static function aw_wgc_removehyperlink_order_item_metadata( $formatted_meta, $item ) {
		if (!is_wc_endpoint_url() ) {
			return $formatted_meta;
		}
		if (!empty($formatted_meta)) {
			foreach ($formatted_meta as $key=> $metadata) {
				$formatted_meta[$key]->value = strip_tags($metadata->value);	
				$formatted_meta[$key]->display_value = '<p>' . strip_tags($metadata->display_value) . '</p>';	 	
			}
		}
		return $formatted_meta;
	}
}

function aw_gift_code_apply_ajax() {
	global $woocommerce, $wpdb , $cart;
	$remainigamount = 0;
	$mybalance 		= 0;
	$checked_balance= 0;
	$giftcode 		= array();
	$gift_expired 	= false;
	$gift_invalid 	= false;
	$usebalance_flag= false;
	$apply_point_cookie = $wpdb->prefix . 'woocommerce_InD9QULI3ct';
	$giftcoupon_cookie 	= $wpdb->prefix . 'woocommerce_RD14QULDiAW';
	$remainingbalance 	= $wpdb->prefix . 'woocommerce_REMAING1iaW';
	$myremainingbalance = $wpdb->prefix . 'woocommerce_M1YiR5E7MBA';	
	$path = '/';
	$host = parse_url(get_option('siteurl'), PHP_URL_HOST);
	check_ajax_referer( 'aw_giftcard_public_nonce', 'aw_gc_front_nonce_ajax' );
	if (isset($_POST['billing_email']) && !empty($_POST['billing_email'])) {
		$billing_email = sanitize_text_field($_POST['billing_email']);
	}

	if (isset($_POST['screen']) && !empty($_POST['screen'])) {
		$screen = sanitize_text_field($_POST['screen']);
	}

	if (isset($_POST['usebalance']) && !empty($_POST['usebalance'])) {
		$usebalance_flag = sanitize_text_field($_POST['usebalance']);
		if ('1' === $usebalance_flag) {
			setcookie('aw_gc_checked_balance', 1, 0, $path, $host);
		}
	}

	if (isset($_POST['gift_code']) && !empty($_POST['gift_code'])) {
		$gift_code 	= sanitize_text_field($_POST['gift_code']);
		$gifts 		= explode(',', $gift_code);
		if ( count($gifts)>1 ) {
			$giftcode 		= maybe_serialize($gifts);
			$gift_balance 	= aw_gc_get_user_total_balance();
			$gift_expired 	= false;
			$gift_invalid 	= false;

		} else {
			$giftcode 		= $gifts[0];
			$validate_wc_gc = check_gift_code_validate( $gift_code );
			if (!empty($validate_wc_gc)) {
				if (null != $validate_wc_gc->expiration_date && gmdate('Y-m-d') > gmdate('Y-m-d', strtotime($validate_wc_gc->expiration_date))) {
					$gift_expired =  true;
				} else {
					if (0 != aw_gc_get_user_total_balance()) {
						$is_user_gc = aw_gift_code_check_giftcard_touser( $validate_wc_gc->id );	
						$subtotal 	= WC()->cart->total;
						if (empty($is_user_gc)) {
							$giftcode 		= $validate_wc_gc->giftcard_code; 
							$gift_balance 	= $validate_wc_gc->giftcard_balance;
							$mybalance 		= aw_gc_get_user_total_balance();
							setcookie($myremainingbalance, $mybalance, 0, $path, $host);
						} else if (!empty($is_user_gc)) {
							$gift_balance 	= aw_gc_get_user_total_balance();
							if ($subtotal <= $gift_balance) {
								$mybalance 	= $gift_balance- $subtotal; 
							} else {
								$mybalance 	= $subtotal-$gift_balance;
							}
							setcookie($myremainingbalance, $mybalance, 0, $path, $host);
						}
					} elseif (!empty($validate_wc_gc)) {
						$gift_balance 	= $validate_wc_gc->giftcard_balance;
						setcookie($myremainingbalance, $gift_balance, 0, $path, $host);	
					}
				} 
			} else {
				$gift_invalid = true;
			} 
		}
	} else {
		$messages =  apply_filters( $screen, __( 'Please enter gift card', 'woocommerce' ) );
		if ('checkout' === $screen) {
			wc_print_notice( $messages, 'error' );
			$info_message = apply_filters( $screen, __( 'Have a gift card?', 'woocommerce' ) . ' <a href="#" class="showgift">' . __( 'Click here to enter your code', 'woocommerce' ) . '</a>' );
			wc_print_notice( $info_message, 'success' );	
		} else {
			wc_add_notice ( $messages, 'error' );
		}
		wp_die();
	}

	if (isset($_COOKIE[$apply_point_cookie]) && 2 == $_COOKIE[$apply_point_cookie]) {
		$messages =  apply_filters( $screen, __( 'Gift card already applied ', 'woocommerce' ) );
		if ('checkout' === $screen) {
			wc_print_notice ( $messages, 'error' );
		} else {
			wc_add_notice ( $messages, 'error' );
		}
		wp_die();
	} else {
		if ($gift_expired) {
			$messages =  apply_filters( $screen, __( "Expired gift card can't be applied ", 'woocommerce' ) );
			if ('checkout' === $screen) {
				wc_print_notice( $messages, 'error' );
				$info_message = apply_filters( $screen, __( 'Have a gift card?', 'woocommerce' ) . ' <a href="#" class="showgift">' . __( 'Click here to enter your code', 'woocommerce' ) . '</a>' );
				wc_print_notice( $info_message, 'success' );	
			} else {
				wc_add_notice ( $messages, 'error' );
			}
			wp_die();	
		} elseif ($gift_invalid) {
			$messages =  apply_filters( $screen, __( 'Gift card code is not valid', 'woocommerce' ) );
			if ('checkout' === $screen) {
				wc_print_notice( $messages, 'error' );
				/*$info_message = apply_filters( $screen, __( 'Have a gift card?', 'woocommerce' ) . ' <a href="#" class="showgift">' . __( 'Click here to enter your code', 'woocommerce' ) . '</a>' );
				wc_print_notice( $info_message, 'success' );*/	
			} else {
				wc_add_notice($messages, 'error');
			}
			wp_die();
		} elseif (0 == $gift_balance) {
			$messages =  apply_filters( $screen, __( '0 balance in gift card ', 'woocommerce' ) );
			if ('checkout' === $screen) {
				wc_print_notice( $messages, 'error' );
				$info_message = apply_filters( $screen, __( 'Have a gift card?', 'woocommerce' ) . ' <a href="#" class="showgift">' . __( 'Click here to enter your code', 'woocommerce' ) . '</a>' );
				wc_print_notice( $info_message, 'success' );	
			} else {
				wc_add_notice ( $messages, 'error' );
			}
			wp_die();
		} elseif (0 == (float) WC()->cart->total) {
			$messages =  apply_filters( $screen, __( 'Gift Card can not be applied on 0 total', 'woocommerce' ) );
			if ('checkout' === $screen) {
				wc_print_notice( $messages, 'error' );
				/*$info_message = apply_filters( $screen, __( 'Have a gift card?', 'woocommerce' ) . ' <a href="#" class="showgift">' . __( 'Click here to enter your code', 'woocommerce' ) . '</a>' );
				wc_print_notice( $info_message, 'success' );*/	
			} else {
				wc_add_notice ( $messages, 'error' );
			}
			wp_die();
		} else {
			setcookie($apply_point_cookie, '1', 0, $path, $host);	
			setcookie($giftcoupon_cookie, base64_encode($giftcode) , 0, $path, $host);
			$messages =  apply_filters( $screen, __( 'Gift card applied ', 'woocommerce' ) );
			if ('checkout' === $screen) {
				wc_print_notice ( $messages, 'success' );
				setcookie($apply_point_cookie, '2', 0, $path, $host);	
			} else {
				wc_add_notice ( $messages, 'success' );
				setcookie($apply_point_cookie, '2', 0, $path, $host);	
				if ($gift_balance > 0 ) {
					if (WC()->cart->total<$gift_balance) {
						$remainingamount =  abs($gift_balance-WC()->cart->total);	
					} else {
						$remainingamount =  0;	
					}
					if ($mybalance > 0) {
						if (0 == $remainingamount) {
							echo wp_kses_post(aw_gc_get_amount($remainingamount));		
						} else {
							echo wp_kses_post(aw_gc_get_amount($mybalance));	
						}
					} else {
						echo wp_kses_post(aw_gc_get_amount($remainingamount));	
					}
					setcookie($remainingbalance, $remainingamount, 0, $path, $host);	
				}
			}
			WC()->cart->calculate_totals();
			wp_die();
		}
	}
}

function aw_giftcard_subtract_giftamount_in_carttotals( $total, $cart ) {
	global $wpdb;
	$giftcoupon_cookie 	= $wpdb->prefix . 'woocommerce_RD14QULDiAW';	
	if (isset($_COOKIE[$giftcoupon_cookie]) && !empty($_COOKIE[$giftcoupon_cookie])) {
		$code_data = get_balance_and_applied_code();	
		if (!empty($code_data) && isset($code_data['total_before_gift']) && isset($code_data['giftamount'])) {
			$total = $code_data['total_before_gift'] - $code_data['giftamount'] ;	
		}
	}
	return $total;
}

function aw_gift_code_remove_ajax( $screen ) {

	global $woocommerce,$wpdb;
	$apply_point_cookie = $wpdb->prefix . 'woocommerce_InD9QULI3ct';
	$giftcoupon_cookie 	= $wpdb->prefix . 'woocommerce_RD14QULDiAW';
	$remainingbalance 	= $wpdb->prefix . 'woocommerce_REMAING1iaW';
	$myremainingbalance = $wpdb->prefix . 'woocommerce_M1YiR5E7MBA';	
	$path 	= '/';
	$host 	= parse_url(get_option('siteurl'), PHP_URL_HOST);
	$items 	= $woocommerce->cart->get_cart();
	check_ajax_referer( 'aw_giftcard_public_nonce', 'aw_gc_front_nonce_ajax' );
	if (isset($_POST['screen']) && 'cart' === $_POST['screen'] && isset($_COOKIE[$apply_point_cookie]) && 0 != $_COOKIE[$apply_point_cookie] ) {
		if (isset($_POST['message']) && !empty($_POST['message'])) {
			$message = sanitize_text_field($_POST['message']);
			$info_message = apply_filters( $screen, __($message), 'woocommerce' ) ;	
		} else {
			$info_message = apply_filters( $screen, __( 'Sorry, it seems the gift card code is invalid - it has now been removed from your order.', 'woocommerce' ) );	
		}
		if (isset($_POST['message_type'])) {
			wc_add_notice( $info_message, sanitize_text_field($_POST['message_type']) );
		} else {
			wc_add_notice( $info_message, 'error' );	
		}
		setcookie($apply_point_cookie, '', time()-3600, $path, $host);
		setcookie($giftcoupon_cookie, '', time()-3600, $path, $host);
		setcookie($remainingbalance, '', time()-3600, $path, $host);
		setcookie($myremainingbalance, '', time()-3600, $path, $host);
		setcookie('aw_gc_checked_balance', '', time()-3600, $path, $host);
		wp_die();

	} elseif (isset($_COOKIE[$apply_point_cookie]) && 0 != $_COOKIE[$apply_point_cookie] && 'cart' != $_POST['screen']) {
		$info_message = apply_filters( $screen, __( 'Have a gift card?', 'woocommerce' ) . ' <a href="#" class="showgift">' . __( 'Click here to enter your code', 'woocommerce' ) . '</a>' );
		wc_print_notice( $info_message, 'success' );
		setcookie($apply_point_cookie, '', time()-3600, $path, $host);
		setcookie($giftcoupon_cookie, '', time()-3600, $path, $host);
		setcookie($remainingbalance, '', time()-3600, $path, $host);
		setcookie($myremainingbalance, '', time()-3600, $path, $host);
		setcookie('aw_gc_checked_balance', '', time()-3600, $path, $host);
		wp_die();
	}
}

function aw_gift_code_apply_points_cart_total( $cart) {
	global $wpdb, $woocommerce;
	$path = '/';
	$host = parse_url(get_option('siteurl'), PHP_URL_HOST);
	$apply_point_cookie = $wpdb->prefix . 'woocommerce_InD9QULI3ct';
	$giftcoupon_cookie 	= $wpdb->prefix . 'woocommerce_RD14QULDiAW';
	$remainingbalance 	= $wpdb->prefix . 'woocommerce_REMAING1iaW';
	$myremainingbalance = $wpdb->prefix . 'woocommerce_M1YiR5E7MBA';	
	$aw_gc_name 		= '';
	$aw_gc_code			= '';
	$total 				= 0;
	$aw_gc_amount 		= 0;
	$available_balance 	= 0;
	$avail_bal_string 	= '';


	$cartsubtotal = (float) WC()->cart->get_subtotal();
	$totaldiscount = (float) WC()->cart->get_cart_discount_total();
	$taxes = (float) WC()->cart->get_taxes();
	$total = (float) $cartsubtotal - (float) $totaldiscount + (float) WC()->cart->get_shipping_total()+ (float) WC()->cart->get_total_tax();	

	if ( isset($_COOKIE[$apply_point_cookie]) && 0 != $_COOKIE[$apply_point_cookie] && isset($_COOKIE[$apply_point_cookie]) ) {

		$gift_data = get_balance_and_applied_code();
		if (!empty($gift_data) && isset($gift_data['giftcode']) && isset($gift_data['giftamount'])  ) {
			?>
			<tr class="fee">
				<th>
				<?php 
				esc_attr_e ( 'Gift Card: ' . $gift_data['giftcode'] . $gift_data['remaining_msg']);
				?>

				<input type="hidden" id="awgc_total_before_gc" value="<?php echo wp_kses_post($total); ?>">
				<input type="hidden" id="awgc_applied_gift" value="<?php echo wp_kses_post($gift_data['giftcode']); ?>"></th>
				<td data-title="<?php esc_attr_e( 'aw_gc_code', 'aw_gc_code' ); ?>">
					<?php echo wp_kses_post(aw_gc_get_amount( $gift_data['giftamount'] )); ?>
				</td>
			<tr>
			<?php
		}
	}  
}

function check_gift_code_validate( $gift_code) {
	global $wpdb;
	$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_codes Where `giftcard_code` = %s AND `giftcard_trash_status` = %d AND (`transaction_action` = %s OR `transaction_action` = %s)", "{$gift_code}", 1, 'Activated', 'Updated'));
	if (!empty($result)) {
		$result->giftcard_amount =  aw_gift_code_convert_currency( $result->giftcard_amount );
		$result->giftcard_balance =  aw_gift_code_convert_currency( $result->giftcard_balance );
		return $result;
	} else {
		$result = array();
		return $result;
	}
}
function check_gift_code_details( $gift_code) {
	global $wpdb;
	$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_codes Where `giftcard_code` = %s AND `giftcard_trash_status` = %d", "{$gift_code}", 1));
	if (!empty($result)) {
		return $result;
	} else {
		$result = array();
		return $result;
	}
}

function aw_gift_code_get_transaction( $giftcard_id ) {
	global $wpdb;
	$result = '';
	$table_name = $wpdb->prefix . 'aw_gc_transactions';
	$result 	= $wpdb->get_var($wpdb->prepare("SELECT `balance` FROM {$wpdb->prefix}aw_gc_transactions WHERE `giftcard_id` = %d ORDER BY id DESC LIMIT 0 , 1"  , "{$giftcard_id}"));
	return $result;
}

function aw_gift_code_get_all_transaction( $order_id ) {
	global $wpdb;
	$result = '';
	$table_name = $wpdb->prefix . 'aw_gc_transactions';
	$result 	= $wpdb->get_results($wpdb->prepare("SELECT giftcard_id , balance_change, balance, transaction_action FROM {$wpdb->prefix}aw_gc_transactions WHERE `order_id` = %s "  , "{$order_id}"));
	return $result;
}

function aw_gc_get_user_total_balance() {
	global $wpdb;

	$db_aw_gc_codes_table	= $wpdb->prefix . 'aw_gc_codes';
	$db_aw_gc_ugc_table 	= $wpdb->prefix . 'aw_gc_users_gift_card';

	$user_id = get_current_user_id();

	$result = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_users_gift_card Where `user_id` = %d", "{$user_id}"));

	if (!empty($result)) {
		$ids = '';

		foreach ($result as $results) {
			$ids .= $results->aw_gc_codes_id . ','; 
		}
		$ids = rtrim($ids, ',');

		$balance_result = $wpdb->get_row($wpdb->prepare("SELECT SUM(`giftcard_balance`) balance FROM {$wpdb->prefix}aw_gc_codes WHERE `id` IN (%5s) AND `giftcard_trash_status` = %d AND (`transaction_action` = %s OR `transaction_action` = %s)", "{$ids}", 1, 'Activated', 'Updated'));
		if (!empty($balance_result)) {
			$balance = aw_gift_code_convert_currency($balance_result->balance);
			
		} else {
			$balance = 0;
		}
	} else {
		$balance = 0;
	}
	return $balance;
}

function aw_gc_get_recipient_giftcode() {
	global $wpdb;
	$user_id = get_current_user_id();
	$result = $wpdb->get_results($wpdb->prepare("SELECT GC.giftcard_code, GC.giftcard_balance, IFNULL(ABS(DATEDIFF(GC.expiration_date, NOW())),1000) AS expiry FROM {$wpdb->prefix}aw_gc_codes AS GC INNER JOIN {$wpdb->prefix}aw_gc_users_gift_card AS UGC ON UGC.aw_gc_codes_id =  GC.id Where UGC.user_id =  %d AND (GC.transaction_action = %s || GC.transaction_action = %s ) ORDER BY expiry ASC , UGC.created_date ASC ", "{$user_id}", 'Activated', 'Updated'));
	return $result;
}

function aw_gc_getall_expired_giftcard() {
	global $wpdb;
	$result = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_codes WHERE DATEDIFF(expiration_date, NOW()) < %d AND (`transaction_action` = %s OR `transaction_action` = %s OR `transaction_action` = %s)", 0, 'Updated', 'Activated', 'Inactive' ));
	return $result;
}

function aw_get_ordered_gift_code_detail( $order_id ) {
	global $wpdb;
	$result 	= $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_codes WHERE `order_id` = %s"  , "{$order_id}"));
	return $result;
}
function aw_get_ordered_gift_codes( $order_id ) {
	global $wpdb;
	$result 	= $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_codes WHERE `order_id` = %s AND `transaction_action` != %s "  , "{$order_id}", 'Deactivated'));
	return $result;
}



function aw_gc_upate_giftcard( $update_array) {
	global $wpdb;
	$db_coupon_table = $wpdb->prefix . 'aw_gc_codes';
	$result = $wpdb->update($db_coupon_table, $update_array, array( 'id'=> $update_array['id'] ) );
	return $result;
}


function aw_get_action_of_transaction( $order_sub_total, $giftcard_amount) {
	
	if ( $order_sub_total >= $giftcard_amount ) {
		$action_balance['balance'] 	= 0 ; 
		$action_balance['action'] 	= USED;
	} elseif ($order_sub_total < $giftcard_amount) {
		$action_balance['balance'] 	= $giftcard_amount - $order_sub_total;
		$action_balance['action'] 	= UPDATED;
	} else {
		$action_balance['balance'] 	= 0;
		$action_balance['action'] 	= USED;
	}

	return $action_balance;	
}
function aw_gc_get_amount( $amount) {
	$decimalposition = get_option('woocommerce_price_num_decimals'); 
	$total_price = esc_html(sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), number_format($amount, $decimalposition) ));
	$total_price = strip_tags( $total_price );
	$total_price = html_entity_decode( $total_price );
	return $total_price;
}

/* Mail to send gift */
function aw_gc_mail_giftcard_to_author ( $code_details ) {

		$send_to_email 		= $code_details->recipient_email;
		$giftcard_code 		= $code_details->giftcard_code;
		$giftcard_amount 	= aw_gc_get_amount( aw_gift_code_convert_currency( (float) $code_details->giftcard_amount ));
		$sender_name		= $code_details->sender_name;
		$recipient_name		= $code_details->recipient_name;
		$additional_text	= $code_details->gift_description; 
		$email_subject 		= 'A Gift Card for you from ' . $sender_name;

	if (''!=$code_details->email_heading) {
		$email_subject 	= $code_details->email_heading;
	}

		$from_name 				= get_option('woocommerce_email_from_name');
		$from_email				= get_option('woocommerce_email_from_address');
		$header_image 			= get_option('woocommerce_email_header_image');
		$footer_text 			= get_option('woocommerce_email_footer_text'); 
		$basecolor 	 			= get_option('woocommerce_email_base_color'); 
		$backgroundcolor		= get_option('woocommerce_email_background_color'); 
		$body_backgroundcolor 	= get_option('woocommerce_email_body_background_color'); 	    
		$text_color	 			= get_option('woocommerce_email_text_color');  
		$footer_text 			= aw_gc_placeholders_replace($footer_text);
		ob_start();
	?>
			<!DOCTYPE html>
			 <html>
			 <body  marginwidth="0" topmargin="0" marginheight="0" offset="0" >
				 <div id="wrapper" style="background-color:<?php echo wp_kses($backgroundcolor, wp_kses_allowed_html('post')); ?> ">
					 <table border="0" cellpadding="0" cellspacing="0" height="50%" width="50%" align="center">
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
							 <table border="0" cellpadding="0" cellspacing="0" width="600" align="center" bgcolor="#FFFFFF" id="template_container" style="border-radius: 6px 6px 0 0;">
								 <tr>
								<td align="center" valign="top">
									<!-- Header -->
									<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_header" style="background-color: <?php echo wp_kses($basecolor, wp_kses_allowed_html('post')); ?> ; border-bottom: 0; font-weight: bold; line-height: 150%; vertical-align: middle;border-radius: 3px 3px 0 0;font-weight: bold; line-height: 100%; vertical-align: middle;">
										<tr>
											<td id="header_wrapper" style="padding: 50px 10px 25px 10px; display: block;">
												<h1 style="font-size: 20px; font-weight: 500; line-height: 180%; margin: 0; text-align: center;"><?php echo 'Hello ' . wp_kses_post($recipient_name); ?></h1>
												<p style="font-size: 15px; font-weight: 400; line-height: 100%; margin: 0; text-align: center;"><?php echo 'You have received a ' . wp_kses_post($giftcard_amount) . ' Gift Card from ' . wp_kses_post($sender_name); ?></p>
											</td>
										</tr>
										<tr>
											<td id="header_wrapper" style="padding: 0 10px 20px 10px;">
												<?php if ('' != $additional_text) { ?>
													<p style="font-size: 15px; font-weight: 400; line-height: normal; margin: 0; text-align: center;"><?php echo wp_kses_post($additional_text); ?></p>
												<?php } ?>
											</td>
										</tr>
									</table>
									<!-- End Header -->
								</td>
								</tr>
								<tr>
									<td align="center" valign="top">
										<!-- Body -->
										<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body" align="center">
											<tr>
												<td valign="top" id="body_content" style="background-color:<?php echo wp_kses($body_backgroundcolor, wp_kses_allowed_html('post')); ?>; border-top: 1px solid #eee; border-bottom: 1px solid #eee; padding: 15px 0;">
													<!-- Content -->
													<table width="340" align="center" bgcolor="#fdfdfd" cellspacing="0" cellpadding="1" border="0" style="border-radius: 8px; background-color:#fdfdfd; 
													box-shadow: 0px 0px 6px #0000000F; border: 1px solid #F2F2F2; margin: 0 auto;">
														<tr>
															<td valign="top" style="padding: 48px 48px 32px;">
																<div id="body_content_inner" style="color:<?php echo wp_kses($text_color, wp_kses_allowed_html('post')); ?>;font-size: 14px; line-height: 150%; text-align: left;">
																	<div style="clear:both;">
																		<span style="color: #6D6D6D; font-size:14px; display:block;">Gift card code:</span>
																		<strong style="font-size: 18px; color:#A27AB2;"><?php echo wp_kses_post($giftcard_code); ?></strong>
																	</div>
																	
																	<div style="clear:both; padding: 20px 0 0;">
																		<div style="display:inline-block; width:50%;">
																			<span style="color: #6D6D6D; font-size:14px; display:block;">Gift card amount:</span>
																			<?php echo wp_kses_post($giftcard_amount); ?>
																		</div>
																		<div style="display:inline-block; width:48%;">
																			<span style="color: #6D6D6D; font-size:14px; display:block;">Expiration date:</span>
																			<?php echo null != $code_details->expiration_date ? esc_html(gmdate(get_option('date_format'), strtotime( $code_details->expiration_date))) : '&mdash;'; ?>
																		</div>
																	</div>
																	
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
							<table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer" bgcolor="#FFFFFF" style="border-radius: 0 0 6px 6px;">
								<tr>
									<td valign="top">
										<table border="0" cellpadding="10" cellspacing="0" width="100%">
											<tr>
												<td colspan="2" valign="middle" id="credit" style="font-size: 12px; line-height: 150%; text-align: center; padding: 24px 0;">
													<a class="button" style="font: normal 16px Arial;text-decoration: none;background-color: #000; color: #fff; padding: 12px 25px; border: 1px solid #000;" href="<?php echo wp_kses_post(get_home_url()); ?>" target="_blank">REDEEM NOW</a>
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
			$message = ob_get_contents();
			ob_end_clean();

			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			add_filter( 'wp_mail_from', 'aw_gc_mail_from' );
			add_filter( 'wp_mail_from_name', 'aw_gc_from_name' );
			
			wp_mail($send_to_email, $email_subject, $message, $headers);
			remove_filter( 'wp_mail_from', 'aw_gc_mail_from' );
			remove_filter( 'wp_mail_from_name', 'aw_gc_from_name' );
}

function aw_gc_mail_from( $email ) {
	$from_email = get_option('woocommerce_email_from_address');
	return $from_email;
}

function aw_gc_from_name( $name ) {
	$from_name = get_option('woocommerce_email_from_name');
	return $from_name;
}


function aw_gc_placeholders_replace( $string ) {
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

function aw_gift_code_check_giftcard_touser( $giftcard_id ) {
	global $wpdb;
	$result = '';
	$table_name = $wpdb->prefix . 'aw_gc_transactions';
	$result 	= $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_users_gift_card WHERE `aw_gc_codes_id` = %d "  , "{$giftcard_id}"));
	return $result;
}

function aw_gift_code_convert_default_currency( $price ) {
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

function aw_gift_code_convert_currency( $price ) {
	// AW Gift Card Currency Switching 
	if ( isset( $GLOBALS['WOOCS'] ) && method_exists( $GLOBALS['WOOCS'], 'woocs_convert_price' ) ) {
		return $GLOBALS['WOOCS']->woocs_convert_price( $price );
	}
	return $price;
}

function aw_get_product_variation_id( $product_id) {
	$var_id = array(0);
	global $wpdb;
	$table1 = 'posts';
	$table2 = 'postmeta';
	$result = $wpdb->get_results($wpdb->prepare("SELECT `ID`, metatable.meta_value as price FROM {$wpdb->prefix}%5s AS posttable INNER JOIN {$wpdb->prefix}%5s AS metatable ON posttable.ID = metatable.post_id WHERE posttable.post_parent = %d AND metatable.meta_key = %s AND posttable.post_status = %s ORDER BY price + %d ASC ", "{$table1}" , "{$table2}", "{$product_id}", '_price', 'publish', 0));
	return $result;
}

function get_balance_and_applied_code() {
	global $wpdb;
	$is_user_gc 		= 0; 
	$aw_gc_code 		= '';
	$aw_gc_amount 		= 0;
	$available_balance 	= 0;	
	$avail_bal_string 	= ''; 
	$path 				= parse_url(get_option('siteurl'), PHP_URL_PATH);
	$host 	 			= parse_url(get_option('siteurl'), PHP_URL_HOST);
	$apply_point_cookie = $wpdb->prefix . 'woocommerce_InD9QULI3ct';
	$giftcoupon_cookie 	= $wpdb->prefix . 'woocommerce_RD14QULDiAW';
	$remainingbalance 	= $wpdb->prefix . 'woocommerce_REMAING1iaW';
	$myremainingbalance = $wpdb->prefix . 'woocommerce_M1YiR5E7MBA';

	if (isset($_COOKIE[$giftcoupon_cookie])) {
		$code 	= maybe_unserialize(base64_decode(sanitize_text_field($_COOKIE[$giftcoupon_cookie])));
		 
	}
	$result = array();
	$subtotal 		= WC()->cart->cart_contents_total;
	$shipping 		= WC()->cart->get_shipping_total();
	$shipping_tax 	= WC()->cart->get_shipping_tax();
	$tax 			= WC()->cart->tax_total;
	$subtotal 		= $subtotal + $shipping + $shipping_tax + $tax;

	if (is_array($code)) {
		$gift_balance 	= aw_gc_get_user_total_balance();
		if ($gift_balance) {
			if (isset($_COOKIE[$remainingbalance]) && $_COOKIE[$remainingbalance] >=0) { 
				$available_balance = sanitize_text_field($_COOKIE[$remainingbalance]);
			}
		}
		$result['total_before_gift'] = $subtotal;
		if ($gift_balance <= $subtotal) {
			$aw_gc_amount		= $gift_balance; 	
			$available_balance 	= 0;
		} else {
			$aw_gc_amount		= $subtotal; 
			$available_balance 	= $gift_balance-$subtotal;
		} 
		$result['myremaining_balance']	= $available_balance;
		$_COOKIE[$myremainingbalance]	= $available_balance;
		foreach ($code as $k => $gift_code) {
			$data = check_gift_code_validate($gift_code);
			if (!empty($data) ) {
				if ($subtotal > 0 ) {
					$aw_gc_code .= $data->giftcard_code . ', ';
				}
				$subtotal = $subtotal - $data->giftcard_balance;
			}
		}
		$aw_gc_code	= rtrim($aw_gc_code, ', ');
		$avail_bal_string = '   ( Available Balance: ' . aw_gc_get_amount($available_balance) . ' )';
	} else {
		$result['total_before_gift'] = $subtotal;
		$validate_wc_gc 	= check_gift_code_validate($code);
		$aw_gc_amount 		= $subtotal;
		if (!empty($validate_wc_gc)) {
			$giftbalance 	= $validate_wc_gc->giftcard_balance;
			$is_user_gc		= aw_gift_code_check_giftcard_touser( $validate_wc_gc->id );
			if (!empty($is_user_gc)) {
				$mybalance = aw_gc_get_user_total_balance();
				if ($giftbalance <= $subtotal) {
					$aw_gc_amount 		= $giftbalance;		
					$available_balance 	= 0;
					if ($mybalance >= $subtotal) {
						$_COOKIE[$myremainingbalance] 	= $mybalance - $subtotal;
						$result['myremaining_balance']	= $mybalance - $subtotal;
					} else {
						$_COOKIE[$myremainingbalance] 	= 0;
						$result['myremaining_balance']	= 0;
					}
				} else {
					$available_balance 	= $giftbalance - $subtotal;	
					$_COOKIE[$myremainingbalance] 	= $mybalance - $subtotal;
					$result['myremaining_balance']	= $mybalance - $subtotal;
				}
				$_COOKIE[$remainingbalance]		= $available_balance;
			} elseif (empty($is_user_gc)) {
				$_COOKIE[$myremainingbalance] = aw_gc_get_user_total_balance();
				$result['myremaining_balance']= aw_gc_get_user_total_balance();
				if ($giftbalance <= $subtotal) {
					$aw_gc_amount 		= $giftbalance;		
					$available_balance 	= 0;
				} else {
					$available_balance 	= $giftbalance - $subtotal;	
				}
				$_COOKIE[$remainingbalance]=$available_balance;
			} else {
				$giftbalance = aw_gc_get_user_total_balance();
				if ($giftbalance <= $subtotal) {
						$aw_gc_amount 		= $giftbalance;		
						$available_balance 	= 0;
				} else {
					$available_balance 	= $giftbalance - $subtotal;
					$aw_gc_amount 		= $available_balance;
				}

				$_COOKIE[$myremainingbalance] 	= $available_balance;
				$_COOKIE[$remainingbalance]		= $available_balance;
				$result['myremaining_balance']	= $available_balance;
			}
		} 
		if (!empty($validate_wc_gc)) {
			$aw_gc_code	= $validate_wc_gc->giftcard_code;
		}
		$avail_bal_string = '   ( Available Balance: ' . aw_gc_get_amount($available_balance) . ' )';
	}
	$result['remaining_msg']= $avail_bal_string;
	$result['giftcode'] 	= $aw_gc_code;
	$result['giftamount'] 	= $aw_gc_amount;
	$result['avail_balance']= $available_balance;
	return $result;
}

function aw_gc_change_code_update_active_to_expired() {
	global $wpdb;
	
	$db_transcation_table 	= $wpdb->prefix . 'aw_gc_transactions';
	$giftcodes 				= aw_gc_getall_expired_giftcard();
	if (!empty($giftcodes)) {
		foreach ($giftcodes as $key => $gift) {
			$update_array = array(
								'id' => $gift->id,
								'transaction_action' => 'Expired',
							);

			aw_gc_upate_giftcard($update_array);
			$gift->giftcard_used_amount = 0;
			$wpdb->insert( $db_transcation_table, array (
				'giftcard_id'      			=> $gift->id,
				'order_id'      			=> $gift->order_id,
				'balance_change'       		=> $gift->giftcard_used_amount,
				'balance'					=> $gift->giftcard_balance,
				'transaction_action'		=> EXPIRED,
				'transaction_description'	=> 'Expired',
			));
		}
	}
}

function aw_gc_make_order_reverse( $order, $order_id, $order_status, $reverse_status) {
	global $wpdb;
	$db_transa_table 	= $wpdb->prefix . 'aw_gc_transactions';
	$item_id_refunded 	= array();
	$product_ids 		= array();
	$giftrecords 		= array();
	$data 				= array();
	$button_refunded 	= 0;
	$balance_change  	= 0;
	$balance 			= 0;
	$quantity 			= 0;

	$status	= UPDATED;
	if (in_array($order_status, $reverse_status)) {
		$trans_desc 	= 'Reimbursed for ' . $order->get_status() . ' Order #' . $order_id;
	} else {
		$trans_desc 	= 'Reimbursed for refunded Order #' . $order_id;
	}  

	$trans_records = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT(giftcard_id) FROM {$wpdb->prefix}aw_gc_transactions WHERE `order_id` = %s  " , "{$order_id}" ));

	if (!empty($trans_records)) {
		foreach ($trans_records as $transaction) {
			$allowed 		= true;
			$current_date 	= gmdate('Y-m-d');
			$giftrecord = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_codes WHERE `id` = %d AND `transaction_action` != %s AND (`transaction_action` = %s OR `transaction_action` = %s OR `transaction_action` = %s)"   , "{$transaction->giftcard_id}", 'Expired', 'Updated', 'Used', 'Activated'));

			if (empty($giftrecord->expiration_date) || $giftrecord->expiration_date > $current_date) {
				if (!empty($giftrecord)) {
					$total_used_balance 	= $wpdb->get_row($wpdb->prepare("SELECT `balance_change`, `balance`, `transaction_description` FROM {$wpdb->prefix}aw_gc_transactions WHERE `giftcard_id` = %d AND `order_id` = %s AND (`transaction_action` = %s OR `transaction_action` = %s OR `transaction_action` = %s) ORDER BY `id` DESC LIMIT %d" , "{$giftrecord->id}", "{$order_id}", 'Updated' , 'Used', 'Activated', 1), ARRAY_A );
					$orderstatus = $order->get_status();
					$last_trans_desc = $total_used_balance['transaction_description'];
					if (preg_match("/\bcancelled\b/", $last_trans_desc ) ||preg_match("/\brefunded\b/", $last_trans_desc ) || preg_match("/\bfailed\b/", $last_trans_desc )) {
						$allowed = false;
					} 

					if (!empty($total_used_balance)) {
						$last_balance_change = $wpdb->get_var($wpdb->prepare("SELECT balance_change FROM {$wpdb->prefix}aw_gc_transactions WHERE `giftcard_id` = %d AND `transaction_action` != %s AND order_id = %d ORDER BY `id` DESC LIMIT %d " , "{$giftrecord->id}", 'Expired', "{$order_id}", 1)); 
						if (!empty($last_balance_change) && 0!=$last_balance_change) {
						$balance_change = abs($last_balance_change);
						$balance 		= abs($giftrecord->giftcard_balance)+abs($last_balance_change); 
						}  
					// already refunded or cancelled for below condition work
					}
					if (true === $allowed) {
					$update_array 		= array(
							   'transaction_action'	=> $status,
							   'id'					=> $giftrecord->id,
							   'giftcard_used_amount'=>$giftrecord->giftcard_amount-$balance,
							   'giftcard_balance'		=> $balance,
						   );
					aw_gc_upate_giftcard($update_array);

					$trans_array		= array(
							   'giftcard_id' 	=> $giftrecord->id,
							   'order_id' 		=> $order_id,
							   'balance_change'	=> $balance_change,
							   'balance'		=> $balance,
							   'transaction_action' => $status,
							   'transaction_description'=> $trans_desc
							);
					$wpdb->insert($db_transa_table, $trans_array);
					}
				}
			}
		}
	}
}

function aw_gift_code_check_coupon_and_gift() {
	global $woocommerce , $wpdb;
	check_ajax_referer( 'aw_giftcard_public_nonce', 'aw_gc_front_nonce_ajax' );
	if (isset($_POST['coupon_code']) && !empty($_POST['coupon_code'])) {
		$coupon_code 	= sanitize_text_field($_POST['coupon_code']);
		$coupon 		= new WC_Coupon($coupon_code);
		$cartsubtotal	= (float) WC()->cart->get_subtotal();
		$totaldiscount 	= WC()->cart->get_cart_discount_total();
		$taxes = WC()->cart->get_taxes(); 
		$total = $cartsubtotal - $totaldiscount + WC()->cart->get_shipping_total()+ WC()->cart->get_total_tax();
		$total = (float) $total;
		if (0 == $total) {
			echo 'remove';
			wp_die();
		} else {
			echo 'apply';
			wp_die();
		}
	}
}

function aw_gc_filter_woocommerce_order_again_cart_item_data( $array, $item, $order ) {
	if ( !empty( $item['order_id'] ) && 1 < $item['order_id']) {

		$ext_order_id 	= $item['order_id'];
		$ext_pro_id 	= $item['variation_id'];

		global $wpdb;
		$result 	= '';
		$result 	= $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_codes WHERE `order_id` = %s AND `product_id` = %d"  , "{$ext_order_id}", "{$ext_pro_id}"));

		if ($result) {

			$existingdata = WC()->session->get( 'cart_items' );

			foreach ($result as $value) {
					$existingdata[$ext_pro_id] = array(
					'product_id'		=> $value->product_id,
					'giftcard_amount'	=> $value->giftcard_amount,
					'recipient_name'	=> $value->recipient_name,
					'recipient_email'  	=> $value->recipient_email,
					'sender_name'		=> $value->sender_name,
					'sender_email'		=> $value->sender_email,
					'email_heading' 	=> $value->email_heading,
					'gift_description' 	=> $value->gift_description
				);
				WC()->session->set( 'cart_items', $existingdata );
			}
		} else {
			$order 		= wc_get_order($ext_order_id);
			foreach ( $order->get_items() as $id => $item ) {
				$varid = wc_get_order_item_meta($id, '_variation_id', true );
				$product = new WC_Product_Variable($item['product_id']);
				$variations = $product->get_children();

				if ( !empty( $variations )) {
					$ext_pro_id = $variations[0];
				}

				$gift_amount = get_post_meta($ext_pro_id, '_price', true);

				$result 	= $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_codes WHERE `order_id` = %s AND `product_id` = %d"  , "{$ext_order_id}", "{$varid}"));
				if ($result) {

					$existingdata = WC()->session->get( 'cart_items' );

					foreach ($result as $value) {
						$existingdata[$ext_pro_id] = array(
						'product_id' => $ext_pro_id,
						'giftcard_amount' => $gift_amount,
						'recipient_name' => $value->recipient_name,
						'recipient_email'   => $value->recipient_email,
						'sender_name' => $value->sender_name,
						'sender_email' => $value->sender_email,
						'email_heading' => $value->email_heading,
						'gift_description' => $value->gift_description
						);					
						WC()->session->set( 'cart_items', $existingdata );
					}
				}
			}
		}
	}
	return $array;
}

// add the filter
add_filter( 'woocommerce_order_again_cart_item_data', 'aw_gc_filter_woocommerce_order_again_cart_item_data', 10, 3 );
