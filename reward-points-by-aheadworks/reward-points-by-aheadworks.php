<?php
/**
 * Plugin Name: Reward Points By Aheadworks
 * Plugin URI: https://www.aheadworks.com/
 * Description: Reward Points plugin By Aheadworks for Woocommerce helps reward customers for shopping at your store with points that can be used towards purchase, spending rate and other conditions configured.
 * Author: Aheadworks
 * Author URI: https://www.aheadworks.com/
 * Version: 1.1.0
 * Woo: 5834808:4ffe242e0d42c234a3a25ff9a241df12
 * Text Domain: reward-points-by-aheadworks
 *
 * @package reward-points-by-aheadworks
 *
 * Requires at least: 5.2.7
 * Tested up to: 5.5.3
 * WC requires at least: 3.8.0
 * WC tested up to: 4.6.1
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

require_once(plugin_dir_path(__FILE__) . 'includes/aw-reward-points-admin.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-reward-transaction-balance.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-reward-configuration.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-reward-pay-by-points.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-reward-my-points.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-reward-admin-pay-by-points.php');

$rdrewardpoints = new AwRewardPoints();

/** Present plugin version **/
define( 'AW_REWARD_POINTS_VERSION', '1.1.0' );

class AwRewardPoints {

	public $GLOBALS;
	public function __construct() {
		global $hook_suffix;
		$nowglobal = $GLOBALS['hook_suffix'];
		$nowglobal = null;

		/** Constructor function, initialize and register hooks **/
		add_action('admin_init', array(get_called_class(),'aw_reward_points_installer'));
		add_filter('set-screen-option', array('AwRewardPoints','aw_reward_points_set_screen'), 10, 3);
		register_uninstall_hook(__FILE__, array(get_called_class(),'aw_reward_points_unistaller'));

		add_action('init', array('AwRewardPoints', 'aw_reset_balance_after_expiry'));

		/* Add Custom menus admin side*/
		add_action('admin_menu', array(get_called_class(),'aw_reward_points_menu'));

		/* Admin Javascript files*/
		add_action('admin_enqueue_scripts', array(get_called_class(),'aw_reward_points_admin_addScript'));
		add_action('admin_post_save_configuration_form', array(get_called_class(),'aw_reward_points_save_configuration_form'));

		/*Public Javascript files*/
		add_action('wp_enqueue_scripts', array(get_called_class(),'aw_reward_points_public_addScript'));

		// We add our display_flash_notices function to the admin_notices
		add_action( 'admin_notices', array(get_called_class(),'aw_reward_points_display_flash_notices'), 12 );

						/****Earn Points, Subtract Points, Spend Points Functions****/
		add_action('woocommerce_order_status_processing', array(get_called_class(),'aw_reward_points_purchase_points'));
		add_action('woocommerce_order_status_completed', array(get_called_class(),'aw_reward_points_purchase_points'));
		add_action('untrash_post', array(get_called_class(),'aw_reward_points_purchase_points'));

		add_action('woocommerce_order_status_cancelled', array(get_called_class(),'aw_reward_points_subtract_points'));
		add_action('woocommerce_order_status_refunded', array(get_called_class(),'aw_reward_points_subtract_points'));
		add_action('woocommerce_order_status_on-hold', array(get_called_class(),'aw_reward_points_subtract_points'));
		add_action('woocommerce_order_status_failed', array(get_called_class(),
				'aw_reward_points_subtract_points'));
		add_action('woocommerce_order_status_pending', array(get_called_class(),'aw_reward_points_subtract_points'));
		add_action('wp_trash_post', array(get_called_class(),'aw_reward_points_subtract_points'));
						/****Earn Points, Subtract Points, Spend Points Functions****/

		add_action('wp_ajax_aw_reward_points_tabcontent', array(get_called_class(),'aw_reward_points_tabcontent'));
		add_action('admin_post_balance_update_form', array(get_called_class(),'aw_reward_points_balance_update_form'));

		add_action('after_setup_theme', array(get_called_class(),'aw_reward_points_cookies'));

		add_action('woocommerce_before_cart_totals', array('AwRewardPayByPoints','aw_checkout_points'));
		add_action('wp_ajax_aw_apply_points', array('AwRewardPayByPoints','aw_apply_points'));

		add_action('woocommerce_cart_calculate_fees', array('AwRewardPayByPoints','aw_apply_points_cart_total'));

		add_filter('woocommerce_account_menu_items', array('AwRewardMyPoints', 'aw_account_menu_items'));

						/****Woocommerce Hook - Add endpoint title.****/
		add_filter('woocommerce_get_query_vars', array('AwRewardMyPoints', 'aw_account_menu_history_query_vars'), 0);
		add_filter('woocommerce_endpoint_rd-my-points_title', array('AwRewardMyPoints', 'aw_mypoint_endpoint_title'), 0);
						/****Woocommerce Hook - Add endpoint title.****/

		add_action('woocommerce_account_rd-my-points_endpoint', array('AwRewardMyPoints', 'aw_account_menu_items_endpoint_content'));
		add_action('woocommerce_checkout_order_processed', array('AwRewardPayByPoints', 'aw_action_new_order_recevied'), 10);

		add_action('wp_logout', array(get_called_class(),'aw_reward_points_logout'));

						/****Admin Side Reward Points Actions****/
		add_action('admin_footer', array(get_called_class(),'aw_reward_points_admin_cookies'));
		add_action('woocommerce_order_item_add_action_buttons', array('AwRewardAdminPayByPoints', 'aw_admin_show_button'), 10, 1);
		add_action('wp_ajax_aw_admin_get_points', array('AwRewardAdminPayByPoints','aw_admin_get_points'));
		add_action('woocommerce_before_order_itemmeta', array('AwRewardAdminPayByPoints', 'aw_display_point_in_order_admin'), 10, 1);
		add_action('wp_ajax_woocommerce_calc_line_taxes', array('AwRewardAdminPayByPoints','aw_recalculate_btn_clk'));
		add_action('wp_ajax_aw_admin_apply_points', array('AwRewardAdminPayByPoints','aw_admin_apply_points'));
		add_action('wp_ajax_woocommerce_add_order_item', array('AwRewardAdminPayByPoints','aw_admin_remove_points'), 10, 1);
		add_action('woocommerce_before_delete_order_item', array('AwRewardAdminPayByPoints','aw_admin_remove_points_after_order'), 10, 1);
		add_action('save_post', array(get_called_class(), 'aw_reward_points_admin_save_new_order'));
		
		/****Admin Side Reward Points Actions****/
		add_filter('wp_kses_allowed_html', 'aw_kses_filter_allowed_html', 10, 2);

		add_action('woocommerce_before_cart', array(get_called_class(), 'aw_reward_points_pre_notice_earn'));
		add_action('woocommerce_before_checkout_form', array(get_called_class(), 'aw_reward_points_pre_notice_earn'), 20);
	}

	public static function aw_reward_points_installer() {
		if (is_admin()) {
			if (!is_plugin_active( 'woocommerce/woocommerce.php')) {

				/** If WooCommerce plugin is not activated show notice **/
				add_action('admin_notices', array('AwRewardPointsAdmin','aw_self_deactivate_notice'));
				/** Deactivate our plugin **/
				deactivate_plugins(plugin_basename(__FILE__));
				if (isset($_GET['activate'])) {
					unset($_GET['activate']);
				}
			} else {
				update_option('AW_REWARD_POINTS_VERSION', AW_REWARD_POINTS_VERSION );
				add_rewrite_endpoint( 'rd-my-points', EP_ROOT | EP_PAGES  );
				flush_rewrite_rules();

				wp_deregister_script( 'autosave' );

				global $wpdb;

				$db_reward_table 		= $wpdb->prefix . 'reward_points_config';  
				$db_balances_table 		= $wpdb->prefix . 'reward_points_balances';
				$db_transcation_table 	= $wpdb->prefix . 'reward_points_transaction_history';

				$charset_collate = $wpdb->get_charset_collate();

				//Check to see if the table exists already, if not, then create it
				if ($wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ' , "{$wpdb->prefix}reward_points_config")) != $db_reward_table) {
					$sql = "CREATE TABLE {$wpdb->prefix}reward_points_config (
							  `id` int(11) NOT NULL auto_increment,
							  `beforafter_calculation` varchar(55) NOT NULL,
							  `expiration_day` int(11) DEFAULT NULL,
							  `earnrates` longtext NOT NULL,
							  `spendrates` longtext NOT NULL,
							  `cover_percentage` int(11) DEFAULT NULL,
							  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							  `display_earn` CHAR(20) NOT NULL DEFAULT 'NO',
							  `promotext` TEXT NOT NULL,
							  PRIMARY KEY (`id`)
							);"	;
					require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
					dbDelta($sql);
				} else {
					$installed_ver = get_option('AW_REWARD_POINTS_VERSION');
					$column1 = $wpdb->get_results($wpdb->prepare('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = %s AND column_name = %s', $db_reward_table, 'display_earn'));
					$column2 = $wpdb->get_results($wpdb->prepare('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = %s AND column_name = %s', $db_reward_table, 'promotext'));
					if (empty($column1) && empty($column2) ) {
						$wpdb->query( "ALTER TABLE {$wpdb->prefix}reward_points_config ADD `display_earn` CHAR(20) NOT NULL DEFAULT 'NO', ADD `promotext` TEXT NOT NULL;" );
					}
				}

				if ($wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ', "{$wpdb->prefix}reward_points_balances")) != $db_balances_table) {
					$sql = "CREATE TABLE {$wpdb->prefix}reward_points_balances (
							  `id` bigint(20) NOT NULL auto_increment,
							  `user_id` bigint(20) NOT NULL,
							  `comments` text NOT NULL,
							  `lifetime_sale` decimal(10,2) NOT NULL,
							  `earnedpoints` bigint(20) NOT NULL DEFAULT '0',
							  `spendpoints` bigint(20) NOT NULL DEFAULT '0',
							  `balance` bigint(20) NOT NULL,
							  `expiration_date` date DEFAULT NULL,
							  `reset` int(2) DEFAULT 0,
							  `transaction_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							  `last_updated` timestamp NULL DEFAULT NULL,
							  PRIMARY KEY (`id`)
							)";
					require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
					dbDelta($sql);
				}
				if ($wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ', "{$wpdb->prefix}reward_points_transaction_history")) != $db_transcation_table) {
					$sql = "CREATE TABLE {$wpdb->prefix}reward_points_transaction_history (
							  `transaction_id` bigint(20) NOT NULL auto_increment,
							  `user_id` bigint(20) NOT NULL,
							  `order_id` text NOT NULL,
							  `points_type` varchar(255) DEFAULT NULL COMMENT 'Earn Or Spent Or Subtract',
							  `balance_change` bigint(20) DEFAULT NULL,
							  `transaction_balance` bigint(20) DEFAULT NULL,
							  `transaction_description` text NOT NULL COMMENT 'The reason of balance update',
							  `balance_log` text NOT NULL COMMENT 'Used to save config value at time of point generation',
							  `order_status` varchar(255) NOT NULL COMMENT 'Order status from WooConmerce',
							  `transaction_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							  `comments` text NOT NULL,
							  `last_updated` timestamp NULL DEFAULT NULL,
							  PRIMARY KEY (`transaction_id`)
							)";
					require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
					dbDelta($sql);
				}
				/*For 1st Time, only once, check Balance Table and Sync Lifetime Sale (from WooCommerce) of Customer with Reward Points Extension*/
				$db_user_table = $wpdb->get_blog_prefix(1) . 'users';
				$rowcount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(`id`) FROM {$wpdb->prefix}reward_points_balances WHERE  1 = %d", 1));
				if (0 == $rowcount) {
					$existing_users = $wpdb->get_results($wpdb->prepare("SELECT `ID` FROM {$wpdb->get_blog_prefix(1)}users WHERE 1 = %d", 1));

					if (count($existing_users) > 0) {
						foreach ($existing_users as $get_users) {
							$customer_orders = get_posts(array(
								'numberposts' => - 1,
								'meta_key'    => '_customer_user',
								'meta_value'  => $get_users->ID,
								'post_type'   => array('shop_order'),
								'post_status' => array('wc-completed', 'wc-processing')
								));

							$orders_total = 0;
							foreach ($customer_orders as $customer_order) {
								$order = wc_get_order( $customer_order );
								$orders_total += $order->get_total();						
							}
							$wpdb->insert($db_balances_table, array(
								'user_id' => $get_users->ID,
								'comments' => '',
								'lifetime_sale' => $orders_total,
								'earnedpoints' => 0,
								'spendpoints' => 0,
								'balance' => 0,
								'expiration_date' => null,
								'last_updated' => null
							));
						}
					}
				} else {
					$existing_users = $wpdb->get_results($wpdb->prepare("SELECT `ID` FROM {$wpdb->get_blog_prefix(1)}users WHERE 1 = %d", 1));

					if (count($existing_users) > 0) {
						foreach ($existing_users as $get_users) {
							$upd_array = array();
							$customer_orders = get_posts(array(
								'numberposts' => - 1,
								'meta_key'    => '_customer_user',
								'meta_value'  => $get_users->ID,
								'post_type'   => array('shop_order'),
								'post_status' => array('wc-completed', 'wc-processing')
								));

							$orders_total = 0;
							foreach ($customer_orders as $customer_order) {
								$order = wc_get_order( $customer_order );
								$orders_total += $order->get_total();						
							}

							$upd_array['lifetime_sale'] = $orders_total;

							$ext_user_bal = aw_reward_points_get_customer_balance($get_users->ID);

							if (empty($ext_user_bal)) {
								$wpdb->insert($db_balances_table, array(
								'user_id' => $get_users->ID,
								'comments' => '',
								'lifetime_sale' => $orders_total,
								'earnedpoints' => 0,
								'spendpoints' => 0,
								'balance' => 0,
								'expiration_date' => null,
								'last_updated' => null
								));
							} else {
								$wpdb->update($db_balances_table, $upd_array, array('user_id'=>$get_users->ID));
							}
						}
					}
				}
				/*For 1st Time, only once, check Balance Table and Sync Lifetime Sale (from WooCommerce) of Customer with Reward Points Extension*/
			}
		}
	}

	public static function aw_reward_points_unistaller() {
		/*Perform required operations at time of plugin uninstallation*/
		global $wpdb;
		$db_config_table 		= $wpdb->prefix . 'reward_points_config';
		$db_balances_table 		= $wpdb->prefix . 'reward_points_balances';
		$db_transcation_table 	= $wpdb->prefix . 'reward_points_transaction_history';

		if (is_multisite()) {
			$blogs_ids = get_sites(); 
			foreach ( $blogs_ids as $b ) { 
				$wpdb->prefix  = $wpdb->get_blog_prefix($b->blog_id);
				$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}reward_points_config");
				$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}reward_points_balances");
				$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}reward_points_transaction_history");
			} 
		} else {		
			$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}reward_points_config");
			$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}reward_points_balances");
			$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}reward_points_transaction_history");
		}
		wp_reset_query();
	}

	public static function aw_reward_points_menu() {
		if (is_plugin_active( 'woocommerce/woocommerce.php')) {
			if (is_multisite() ) {
				$parent_slug = 'reward-configuration';
				$parent_function ='';
			} else {
				$parent_slug = 'RdRewardPoint';
				$parent_function ='toplevel_page';
			}

			add_menu_page(__('Reward Points', 'main_menu'), __('Reward Points', 'main_menu'), ' ', $parent_slug, $parent_function, plugin_dir_url(__FILE__) . '/admin/images/aw_points_ico.png', 25);

			// Add a submenu to the custom top-level menu:
			add_submenu_page($parent_slug, __('Configuration of Reward', 'main_menu'), __('Configuration', 'main_menu'), 'manage_options', 'reward-configuration', array('AwRewardConfiguration','aw_configuration_html'));
			$hook = add_submenu_page($parent_slug, __('Customer Balance', 'main_menu'), __('Customer Balance', 'main_menu'), 'manage_options', 'reward-transaction-balance', array('AwRewardPointsAdmin','aw_transaction_balance_html'));
			add_action( "load-$hook", array('AwRewardPoints','aw_reward_add_screen_option'));
		}
	}

	public static function aw_reward_add_screen_option() {
		$option = 'per_page';
		$args = array(
			'label' => 'Number of items per page:',
			'default' => 20,
			'option' => 'customers_per_page'
		);
		add_screen_option( $option, $args );

		$table_bal = new AwRewardTransactionBalance();
		$table_trans = new AwRewardTransactionBalance();
	}

	public static function aw_reward_points_set_screen( $status, $option, $value) { 
		if ('customers_per_page' == $option) {
			return $value;
		}	
		return $status;
	}

	public static function aw_reward_points_admin_addScript() {
		$page = '';

		if (isset($_GET['page'])) {
			$page = sanitize_text_field($_GET['page']);
		}

		if ('reward-configuration' == $page || 'reward-transaction-balance' == $page) {
			wp_register_style('rewardpointsadmincss', plugins_url('/admin/css/aw-reward-points-admin.css', __FILE__ ), array(), '1.0' );
			wp_enqueue_style('rewardpointsadmincss');

			wp_register_script('rewardpointsadminjs', plugins_url('/admin/js/aw-reward-points-admin.js', __FILE__ ), array(), '1.0' );
			$js_var = array('site_url' => get_option('siteurl'),'ajax_url'=>admin_url( 'admin-ajax.php'));
			wp_localize_script('rewardpointsadminjs', 'js_var', $js_var);
			wp_register_script('rewardpointsadminjs', plugins_url('/admin/js/aw-reward-points-admin.js', __FILE__ ), array(), '1.0' );
			wp_enqueue_script('rewardpointsadminjs');
		}

		wp_register_style('rewardpointsorderadmincss', plugins_url('/admin/css/aw-reward-points-admin-order.css', __FILE__ ), array(), '1.0' );
		wp_enqueue_style('rewardpointsorderadmincss');

		$path = parse_url(get_option('siteurl'), PHP_URL_PATH);
		$host = parse_url(get_option('siteurl'), PHP_URL_HOST);
		$nonce = wp_create_nonce('rdrewardpoints_admin_order_nonce');

		wp_register_script('rewardpointsorderadminjs', plugins_url('/admin/js/aw-reward-points-order-admin.js', __FILE__ ), array(), '1.0' );
		$order_js_var = array('site_url' => get_option('siteurl'), 'ajax_url'=>admin_url('admin-ajax.php'), 'path' => $path, 'host' => $host, 'rd_order_nonce' => $nonce);
		wp_localize_script('rewardpointsorderadminjs', 'order_js_var', $order_js_var);
		wp_register_script('rewardpointsorderadminjs', plugins_url('/admin/js/aw-reward-points-order-admin.js', __FILE__ ), array(), '1.0' );
		wp_enqueue_script('rewardpointsorderadminjs');
	}

	public static function aw_reward_points_public_addScript() {
		/** Add Plugin CSS and JS files Public Side**/
		$path = parse_url(get_option('siteurl'), PHP_URL_PATH);
		$host = parse_url(get_option('siteurl'), PHP_URL_HOST);
		$nonce = wp_create_nonce('rdrewardpoints_nonce');
		//if(isset($_GET['rd-my-points']))
		//{
			wp_register_style('rdrewardpointspubliccss', plugins_url('/public/css/aw-reward-points-public.css', __FILE__ ), array(), '1.0' );
			wp_enqueue_style('rdrewardpointspubliccss');
		//}
		wp_register_script('rdrewardpointspublicjs', plugins_url('/public/js/aw-reward-points-public.js', __FILE__ ), array('jquery'), '1.0', true);

		$js_var = array('site_url' => get_option('siteurl'), 'path' => $path, 'host' => $host, 'rd_nonce' => $nonce);
		wp_localize_script('rdrewardpointspublicjs', 'js_var', $js_var);

		wp_register_script('rdrewardpointspublicjs', plugins_url('/public/js/aw-reward-points-public.js', __FILE__ ), array('jquery'), '1.0');
		wp_enqueue_script('rdrewardpointspublicjs');
	}

	public static function aw_reward_points_save_configuration_form() {
		global $wpdb;
		$db_reward_table = $wpdb->prefix . 'reward_points_config'; 
		$url =  admin_url() . 'admin.php?page=reward-configuration';

		if (isset($_POST['rdrewardpoints_admin_nonce'])) {
			$rdrewardpoints_admin_nonce = sanitize_text_field($_POST['rdrewardpoints_admin_nonce']);
		}

		if ( !wp_verify_nonce( $rdrewardpoints_admin_nonce, 'save_configuration_form' )) {
			wp_die('Our Site is protected');
		}

		if (self::aw_reward_points_check_validate($_POST)) {
			if (isset($_POST['earn_configuration_submit'])) {
				if (isset($_POST['beforafter_calculation'])) {
					$array['beforafter_calculation'] = sanitize_text_field($_POST['beforafter_calculation']);
				}
				if (isset($_POST['expiration_day']) && '' != $_POST['expiration_day']) {
					$array['expiration_day'] = sanitize_text_field($_POST['expiration_day']);
				} else {
					$array['expiration_day'] = null;
				}
				if (isset($_POST['earnrates'])) {
					$var_earnrates = json_encode($_POST);
					$var_earnrates = wp_unslash($var_earnrates);
					$var_earnrates = json_decode($var_earnrates, true);
					$array['earnrates']  = serialize(array_values(array_filter($var_earnrates['earnrates'])));
				} else {
					$array['earnrates']  = '';
				}
			}
			if (isset($_POST['spend_configuration_submit'])) {
				if (isset($_POST['cover_percentage']) && '' != $_POST['cover_percentage']) {
					$array['cover_percentage'] = sanitize_text_field($_POST['cover_percentage']);
				} else {
					$array['cover_percentage'] = null;
				}
				if (isset($_POST['spendrates'])) {
					$var_spendrates = json_encode($_POST);
					$var_spendrates = wp_unslash($var_spendrates);
					$var_spendrates = json_decode($var_spendrates, true);
					$array['spendrates'] = serialize(array_values(array_filter($var_spendrates['spendrates'])));
				} else {
					$array['spendrates'] = '';
				}
			}

			if (isset($_POST['storefront_configuration_submit']) && isset($_POST['display_earn'])) {
				if (isset($_POST['promotext']) && '' != $_POST['promotext'] && isset($_POST['display_earn']) && 'YES' == $_POST['display_earn']) {
					
					$promotext = json_encode($_POST);
					$promotext = wp_unslash($promotext);
					$promotext = json_decode($promotext, true);
					$promotext = array_values(array_filter($promotext));

					$array['promotext'] = esc_attr($promotext[4]) ;
					$array['display_earn'] 	= sanitize_text_field($_POST['display_earn']);

				} else {
					$array['display_earn'] 	= sanitize_text_field($_POST['display_earn']);
				}
			}

			$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}reward_points_config where 1 = %d", 1), ARRAY_A);
			if (empty($result)) {
				$array['id'] = 1;
				$array['last_updated'] = gmdate('Y-m-d H:i:s');
				$wpdb->insert($db_reward_table, $array);
				self::aw_reward_points_add_flash_notice( __('Reward points configuration setting saved'), 'success', false );
			} else {
				$array['last_updated'] = gmdate('Y-m-d H:i:s');
				$wpdb->update($db_reward_table, $array, array('id'=>1));
				self::aw_reward_points_add_flash_notice( __('Reward points configuration setting updated'), 'success', false );
			}
		}
		wp_redirect($url);
	}

	public static function aw_reward_points_check_validate( $array) {
		$flag = true;
		$existing = array();
		foreach ($array as $key => $val) {
			if (is_array($val)) {
				$screen ='';
				if ('earnrates' === $key) {
					$screen = 'earn points';
				} else {
					$screen = 'spend points';
				}
				foreach ($val as $keyvalue) {
					if ('' === $keyvalue['lifetime_sale']) {
						self::aw_reward_points_add_flash_notice( __('Lifetime sales is required field'), 'error', false );
						$flag = false;
					} else if ($keyvalue['lifetime_sale']<0) {
						self::aw_reward_points_add_flash_notice( __('Enter a number greater than or equal to 0'), 'error', false );
						$flag = false;
					} else {
						if (in_array($keyvalue['lifetime_sale'], $existing)) {
							self::aw_reward_points_add_flash_notice( __("Lifetime sales values can't be the same "), 'error', false );
							$flag = false;	
						} else {
							$existing[] = $keyvalue['lifetime_sale'];
						}						
					}

					if ('' === $keyvalue['base_currency']) {
						self::aw_reward_points_add_flash_notice( __('Base currency is required field'), 'error', false );
						$flag = false;
					} else if ($keyvalue['base_currency']<=0) {
						self::aw_reward_points_add_flash_notice( __('Enter a number greater than 0' . $screen . ' section'), 'error', false );
						$flag = false;
					} else if (fmod($keyvalue['base_currency'], 1) !== 0.00) {
						self::aw_reward_points_add_flash_notice( __('Base currency values cannot be decimal'), 'error', false);
						$flag = false;						
					}

					if ('' === $keyvalue['points']) {
						self::aw_reward_points_add_flash_notice( __('Points is required field'), 'error', false );
						$flag = false;
					} else if ($keyvalue['points']<=0) {
						self::aw_reward_points_add_flash_notice( __('Enter a number greater than 0'), 'error', false );
						$flag = false;
					} else if (fmod($keyvalue['points'], 1) !== 0.00) {
						self::aw_reward_points_add_flash_notice(__('Points values cannot be decimal'), 'error', false);
						$flag = false;						
					}
				}
			} /*else {
				/*if($val==="")
				{
					$key = str_replace('_',' ', $key);
					self::aw_reward_points_add_flash_notice( __(ucfirst($key)." is required field"), "error", false );
					$flag = false;
				}
			}*/
		}
		return $flag;
	}

	public static function aw_reward_points_add_flash_notice( $notice = '', $type = 'warning', $dismissible = true ) { 
		// Here we return the notices saved on our option, if there are not notices, then an empty array is returned
		$notices = get_option( 'reward_flash_notices', array() );

		$dismissible_text = ( $dismissible ) ? 'is-dismissible' : '';

		// We add our new notice.
		array_push( $notices, array( 
				'notice' => $notice, 
				'type' => $type, 
				'dismissible' => $dismissible_text
			) );

		// Then we update the option with our notices array
		update_option('reward_flash_notices', $notices );
	}

	/**
	 * Function executed when the 'admin_notices' action is called, here we check if there are notices on
	 * our database and display them, after that, we remove the option to prevent notices being displayed forever.
	 *
	 * @return void
	*/

	public static function aw_reward_points_display_flash_notices() { 
		$notices = get_option('reward_flash_notices', array());

		// Iterate through our notices to be displayed and print them.
		if (!empty($notices)) {
			foreach ($notices as $notice) {
				printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
					esc_html($notice['type']),
					esc_html($notice['dismissible']),
					esc_html($notice['notice'])
				);
			}	
		} 

		// Now we reset our options to prevent notices being displayed forever.
		if (! empty($notices)) {
			delete_option('reward_flash_notices', array());
		}
	}

	public static function aw_reward_points_purchase_points( $order_id ) {
		/*
		Points = Subtotal including Discounts / Coupons, but excluded Shipping (Taxes are incl. or excl. - according to the settings)
		*/

		global $wpdb;
		$post = get_post( $order_id );
		if ( 'shop_order' !== $post->post_type ) {
			return false;
		}
		$twice_insert_check		= 0;
		$key_val				= 0;
		$key					= 0;
		$balance_ary 			= array();
		$fee_name	 			= '';
		$spendpoints 			= 0;
		$db_balances_table 		= $wpdb->prefix . 'reward_points_balances';
		$db_transcation_table 	= $wpdb->prefix . 'reward_points_transaction_history';

		$order 					= wc_get_order($order_id);
		$order_data 			= $order->get_data();
		$order_status 			= $order_data['status'];
		$user_id 				= $order_data['customer_id'];
		$order_grand_total 		= $order_data['total'];

		/*#62 Fix*/

		$get_balance = aw_reward_points_get_customer_balance($user_id);
		$get_config = aw_reward_points_get_config();
		
		$config_lifetime_sale = array();
		$customer_lifetime_sale = $get_balance->lifetime_sale;
		
		if (!empty($get_config['earnrates'])) {
			$earn_rates = unserialize($get_config['earnrates']);
			foreach ($earn_rates as $key => $rate) {
				if ($customer_lifetime_sale >= $rate['lifetime_sale']) {
					$config_lifetime_sale[] = $rate['lifetime_sale'];
				}
			}
		}

		$customer_rates = aw_reward_points_getClosest($customer_lifetime_sale, $config_lifetime_sale);

		if (null == $customer_rates) {
			return;
		}

		/*#62 Fix*/

		/*Only one order can be inserted in transaction_history*/
		/*$transaction_type 		= $wpdb->get_var($wpdb->prepare("SELECT `points_type` FROM {$wpdb->prefix}reward_points_transaction_history WHERE `order_id` = %d ORDER BY `transaction_id` DESC LIMIT %d ", "{$order_id}", 1));*/
		$transaction_type 		= get_post_meta($order_id , '_rd_transcations', true) ? get_post_meta($order_id , '_rd_transcations', true) : array();
		$transaction_type_count = count($transaction_type);

		//if ('Earn' == $transaction_type && 'Spent' != $transaction_type) {
		if (in_array('Earn', $transaction_type) && !in_array('Spent', $transaction_type)) {
			$transaction_type = 'Earn';

			$get_awarded_points = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}reward_points_transaction_history WHERE `user_id` = %d AND `order_id` = %d AND (`points_type` = %s || `points_type` = %s ) ORDER BY `transaction_id` DESC LIMIT %d ", "{$user_id}", "{$order_id}", 'Earn', 'Spent', "{$transaction_type_count}"));
			if (!empty($get_awarded_points)) {

				foreach ($get_awarded_points as $awardedpoints) {
					$trans_ary = array(
						'order_status'				=> $order_status
					);
					$wpdb->update($db_transcation_table, $trans_ary, array('transaction_id' => $awardedpoints->transaction_id));
				}
				//aw_get_update_post_meta($order_id, $transaction_type, 0);
			}
			return;
		}
		$prev_status = get_post_meta($post->ID , '_wp_trash_meta_status', true);
		$status= str_replace('wc-', '', $prev_status);
		$untrash_from = array('wc-processing','wc-completed');
		if ('trash' == $post->post_status && !in_array($prev_status, $untrash_from) && 1 == $transaction_type_count ) {

			if (in_array('Reverse Spent', $transaction_type) ) {
				if ('wc-on-hold' == $prev_status || 'wc-pending' == $prev_status) {
					$object = new AwRewardPayByPoints();
					$object->aw_action_new_order_recevied( $order_id);
					$points_type = 'Spent';	
					$key = 0;
					aw_get_update_post_meta($order_id, $points_type, $key);
				} 

				if ('wc-refunded' == $prev_status || 'wc-cancelled' == $prev_status || 'wc-failed' == $prev_status || 'wc-pending' == $prev_status) {
					$trans_ary = array(
						'order_status'				=> $status,
						'transaction_description'	=> 'Order #' . $order_id . ' is ' . $status
					);

					$transaction_id =  $wpdb->get_var($wpdb->prepare("SELECT `transaction_id` FROM {$wpdb->prefix}reward_points_transaction_history WHERE `user_id` = %d AND `order_id` = %d AND `points_type` = %s ORDER BY `transaction_id` DESC LIMIT %d ", "{$user_id}", "{$order_id}", 'Reverse Spent', 1));

					$wpdb->update($db_transcation_table, $trans_ary, array('transaction_id' => $transaction_id));
				} 
				
			} elseif (in_array('Reverse Earn', $transaction_type) ) {
				if ('wc-refunded' == $prev_status || 'wc-cancelled' == $prev_status || 'wc-failed' == $prev_status || 'wc-pending' == $prev_status) {

					$trans_ary = array(
						'order_status'				=> $status,
						'transaction_description'	=> 'Order #' . $order_id . ' is ' . $status
					);

					$transaction_id =  $wpdb->get_var($wpdb->prepare("SELECT `transaction_id` FROM {$wpdb->prefix}reward_points_transaction_history WHERE `user_id` = %d AND `order_id` = %d AND `points_type` = %s ORDER BY `transaction_id` DESC LIMIT %d ", "{$user_id}", "{$order_id}", 'Reverse Earn', 1));

					$wpdb->update($db_transcation_table, $trans_ary, array('transaction_id' => $transaction_id));
				} 
			}


			return false;
		} 
		$untrash_from = array('wc-refunded','wc-cancelled','wc-failed','wc-on-hold');
		
		if ('trash' == $post->post_status && in_array($prev_status, $untrash_from) && 2 == $transaction_type_count ) {
			
			$trans_ary = array(
						'order_status'				=> $status,
						'transaction_description'	=> 'Order #' . $order_id . ' is ' . $status
					);

			$transaction_ids =  $wpdb->get_results($wpdb->prepare("SELECT `transaction_id` FROM {$wpdb->prefix}reward_points_transaction_history WHERE `user_id` = %d AND `order_id` = %d AND (`points_type` = %s OR `points_type` = %s)ORDER BY `transaction_id` DESC LIMIT %d ", "{$user_id}", "{$order_id}", 'Reverse Earn', 'Reverse Spent', 2));
			foreach ($transaction_ids as $id) {
				$wpdb->update($db_transcation_table, $trans_ary, array('transaction_id' =>$id->transaction_id));	
			}
			
			return false;
		}
		//Issue #48
		if (in_array('Spent', $transaction_type)) {
				
			$transaction_type = 'Spent';
			//$get_awarded_points = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}reward_points_transaction_history WHERE `user_id` = %d AND `order_id` = %d AND `points_type` = %s ORDER BY `transaction_id` DESC LIMIT %d ", "{$user_id}", "{$order_id}", 'Spent', 1));
			$get_awarded_points = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}reward_points_transaction_history WHERE `user_id` = %d AND `order_id` = %d AND (`points_type` = %s || `points_type` = %s ) ORDER BY `transaction_id` DESC LIMIT %d ", "{$user_id}", "{$order_id}", 'Earn', 'Spent', "{$transaction_type_count}"));
			if (!empty($get_awarded_points)) {

				foreach ($get_awarded_points as $key => $awardedpoints) {
					$trans_ary = array(
						'order_status'				=> $order_status
					);
					if ('Earn' == $awardedpoints->points_type) {
						$wpdb->update($db_transcation_table, $trans_ary, array('transaction_id' => $awardedpoints->transaction_id));
						$twice_insert_check = 1;
					}
					aw_get_update_post_meta($order_id, $awardedpoints->points_type, $key);
				}
			}
			$key_val++;
		}
		$points_type = 'Earn';
		if (0 != $user_id) {
			$get_config = aw_reward_points_get_config();

			$order_item_total = 0;
			foreach ($order->get_items() as $item_id => $item ) {
				$order_item_total+= $item->get_total();
			}

			$cart_tax = 0;
			$total = $order_item_total;

			/* Points are calculated according to Earn rate in the following way: Points = Subtotal including Discounts / Coupons, but excluded Shipping (Taxes are incl. or excl. - according to the settings) https://aheadworks.atlassian.net/projects/WOOPOINTS/issues/WOOPOINTS-9 */

			if ('AT' === $get_config['beforafter_calculation']) {
				//$cart_tax = $order_data['cart_tax'];
				$cart_tax = $order_data['total_tax'];
				$total = $order_item_total + $cart_tax;
			}

			foreach ( $order->get_items('fee') as $item_id => $item_fee ) {
				$fee_name = $item_fee->get_name();
			}

			if ('' != $fee_name && ( 'Points' === substr($fee_name, 0, 6) )) {
				$total = $total + $item_fee->get_total();
			}

			/* Points are calculated according to the rate of lifetime sales before placed order. https://aheadworks.atlassian.net/projects/WOOPOINTS/issues/WOOPOINTS-9 */

			$customer_lifetime_sale = self::aw_reward_points_get_customer_total_order($user_id, $order_grand_total);

			$rates 			= unserialize($get_config['earnrates']);
			$spend_rates 	= unserialize($get_config['spendrates']);

			if (!empty($rates) && 0 == $twice_insert_check) {
				$config_lifetime_sale = array();

				foreach ($rates as $key => $rate) {
					if ($customer_lifetime_sale['calculation'] >= $rate['lifetime_sale']) {
						$config_lifetime_sale[] = $rate['lifetime_sale'];
					}
				}
				$earn_rates = aw_reward_points_getClosest($customer_lifetime_sale['calculation'], $config_lifetime_sale);
				$key = array_search($earn_rates, array_column($rates, 'lifetime_sale'));

				/*
				Balance Change (amount of transaction; can be a positive or negative value)
				Balance (Balance value after it' has been changed)
				*/

				$base_currency 			= $rates[$key]['base_currency'];
				$points 				= $rates[$key]['points'];

				if (0 < $total) {
					$total = $total;
				} else {
					$total = 0;
				}

				//$balance_change 		= $earnedpoints = ((int)($total / $base_currency) * $points);

				/* Changed Round Off Thing After Discussion on Call and Chat - 13Jan2020 https://aheadworks.atlassian.net/projects/WOOPOINTS/issues/WOOPOINTS-9 */
				$earnedpoints			= ( ( $total / $base_currency ) * $points );
				$balance_change			= (int) $earnedpoints;
				/* Changed Round Off Thing After Discussion on Call and Chat - 13Jan2020 https://aheadworks.atlassian.net/projects/WOOPOINTS/issues/WOOPOINTS-9 */
 
				$transaction_balance 	= 0;

				$get_transaction_balance = $wpdb->get_var($wpdb->prepare("SELECT `transaction_balance` FROM {$wpdb->prefix}reward_points_transaction_history WHERE `user_id` = %d ORDER BY transaction_id DESC LIMIT %d", "{$user_id}", 1));


				if (is_null($get_transaction_balance)) {
					$transaction_balance = $balance_change;
				} else {
					$transaction_balance = $get_transaction_balance + $balance_change;
				}

				//$balance_log 			= 'Customer Lifetime Sales >= ' . $rates[$key]['lifetime_sale'] . '::' . 'Base Currency = ' . $rates[$key]['base_currency'] . '::' . 'Points = ' . $rates[$key]['points'];
				$balance_log			= 'Customer Lifetime Sales >= ' . $rates[$key]['lifetime_sale'] . '::';
				$balance_log			.= 'Base Currency = ' . $rates[$key]['base_currency'] . '::';
				$balance_log			.= 'Points = ' . $rates[$key]['points'];
				$order_status 			= $order_data['status'];
				$transaction_date 		= gmdate('Y-m-d H:i:s');
				$comments 				= '';
				$last_updated 			= gmdate('Y-m-d H:i:s');

				$wpdb->insert($db_transcation_table, array(
					'user_id'          		=> $user_id,
					'order_id'       		=> $order_id,
					'points_type'			=> $points_type,
					'balance_change'		=> $balance_change,
					'transaction_balance'	=> $transaction_balance,
					'transaction_description' => 'Points are earned for order #' . $order_id,
					'balance_log'			=> $balance_log,
					'order_status'			=> $order_status,
					'transaction_date'		=> $transaction_date,
					'comments'				=> $comments,
					'last_updated'			=> $last_updated
				));
				aw_get_update_post_meta($order_id, $points_type, $key_val);

				/* this actin called when admin change status*/
				$get_balance 		= aw_reward_points_get_customer_balance($user_id);
				$expiration_day 	= (int) $get_config['expiration_day'];
				if (0 != $expiration_day) {
					$expiration_date = gmdate('Y-m-d', strtotime('+ ' . $get_config['expiration_day'] . ' day'));
				} else {
					$expiration_date = null;
				}

				$rd_ep = $get_balance->earnedpoints + $balance_change;

				$balance_ary['user_id'] 			= $user_id;
				$balance_ary['comments'] 			= $comments;
				$balance_ary['lifetime_sale'] 		= $customer_lifetime_sale['actual'];
				$balance_ary['earnedpoints'] 		= $rd_ep;
				$balance_ary['balance'] 			= ( $transaction_balance - $spendpoints );
				$balance_ary['expiration_date'] 	= $expiration_date;
				$balance_ary['transaction_date'] 	= $transaction_date;
				$balance_ary['last_updated'] 		= $last_updated;

				$user_exists = $wpdb->get_var($wpdb->prepare("SELECT count(`user_id`) FROM {$wpdb->prefix}reward_points_balances WHERE `user_id` = %d", "{$user_id}"));

				if (0 == $user_exists) {
					$balance_ary['spendpoints']	= 0;
					$wpdb->insert($db_balances_table, $balance_ary);
				} else {
					$wpdb->update($db_balances_table, $balance_ary, array('user_id' => $user_id));

					if (is_admin()) {
						if ('Spent' != $transaction_type) {
							aw_reward_points_on_hold_spent_point($order_id);
						}
					}
				}
			} else {
				$user_exists = $wpdb->get_var($wpdb->prepare("SELECT count(`user_id`) FROM {$wpdb->prefix}reward_points_balances WHERE `user_id` = %d", "{$user_id}"));
				$last_updated 					= gmdate('Y-m-d H:i:s');

				$balance_ary 					= array();
				$balance_ary['user_id'] 		= $user_id;
				$balance_ary['lifetime_sale'] 	= $customer_lifetime_sale['actual'];
				$balance_ary['last_updated']  	= $last_updated;

				if (0 == $user_exists) {
					$balance_ary['spendpoints']	= 0;
					$wpdb->insert($db_balances_table, $balance_ary);
				} else {
					$wpdb->update($db_balances_table, $balance_ary, array('user_id' => $user_id));
				}
			}
		} else {
			return;
		}
		return;
	}

	public static function aw_reward_points_subtract_points( $order_id) {

		global $wpdb;
		$post = get_post( $order_id );
		$transaction_type_chk = array();

		if ( 'shop_order' !== $post->post_type ) {
			return false;
		}

		$db_balances_table 		= $wpdb->prefix . 'reward_points_balances';
		$db_transcation_table 	= $wpdb->prefix . 'reward_points_transaction_history';		
		$twice_insert_check 	= 0;

		/*Check if Order exists for which points needed to be Subtracted*/
		$check_order = $wpdb->get_var($wpdb->prepare("SELECT COUNT(`order_id`) FROM {$wpdb->prefix}reward_points_transaction_history WHERE `order_id` = %d", "{$order_id}"));
		
		if ($check_order > 0) {
			$order 				= wc_get_order($order_id);
			$order_data 		= $order->get_data();
			$order_grand_total 	= $order_data['total'];
			$order_status		= $order_data['status'];
			$user_id 			= $order_data['customer_id'];
			//$on_hold_del		= 0;
			$transaction_description = '';

			$action = '';
			if (isset($_GET['action']) && 'trash' == $_GET['action']) {
				$action = sanitize_text_field($_GET['action']);
			}
			if ( 'trash' == $action ) {
				$transaction_description = 'Order #' . $order_id . ' is deleted';
			} else {
				$transaction_description = 'Order #' . $order_id . ' is ' . $order_status;
			}
			/*$transaction_type = $wpdb->get_var($wpdb->prepare("SELECT `points_type` FROM {$wpdb->prefix}reward_points_transaction_history WHERE `user_id` = %d AND `order_id` = %d ORDER BY `transaction_id` DESC LIMIT %d", "{$user_id}", "{$order_id}", 1));*/

			$transaction_type 		= get_post_meta($order_id , '_rd_transcations', true);
			$transaction_type_total = count($transaction_type);

			if (0 != $user_id && ( in_array('Earn', $transaction_type) || in_array('Spent', $transaction_type) ) ) {

				$get_awarded_points = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}reward_points_transaction_history WHERE `user_id` = %d AND `order_id` = %d AND (`points_type` = %s || `points_type` = %s ) ORDER BY `transaction_id` DESC LIMIT %d ", "{$user_id}", "{$order_id}", 'Earn', 'Spent', "{$transaction_type_total}"));
				$customer_lifetime_sale = self::aw_reward_points_get_customer_total_order($user_id, $order_grand_total);

				$balance_ary['lifetime_sale'] 	= $customer_lifetime_sale['actual'];
				$last_updated 			= gmdate('Y-m-d H:i:s');
				$transaction_date 		= gmdate('Y-m-d H:i:s');
				$comments 				= '';
				$points_type 			= '';
				$balance_ary 			= array();

				if (!empty($get_awarded_points)) {  
					$check_earn = 1;
					foreach ($get_awarded_points as $key=> $awardedpoints) {
						$get_balance = aw_reward_points_get_customer_balance($user_id);
						if ('Spent' === $awardedpoints->points_type) {
							$get_awarded_pointsc = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}reward_points_transaction_history WHERE `user_id` = %d AND `order_id` = %d AND `points_type` = %s ORDER BY `transaction_id` DESC LIMIT %d ", "{$user_id}", "{$order_id}", 'Spent', 1));

							//if (!empty($get_awarded_pointsc) && $transaction_type_total < 2 && $more_condition ) {
							$prev_status = get_post_meta($post->ID , '_wp_trash_meta_status', true);
							if (!empty($get_awarded_pointsc) && $transaction_type_total < 2 && 'failed' != $order_status && 'refunded' != $order_status && 'cancelled' != $order_status && 'trash'!=$action) {//} ) {
								foreach ($get_awarded_pointsc as $awardedpointsc) {
									$trans_ary = array(
										'order_status'	=> $order_status,
									);
									$wpdb->update($db_transcation_table, $trans_ary, array('transaction_id' => $awardedpointsc->transaction_id));
								}
								$twice_insert_check = 1;
							} else {
								$points_type			= 'Reverse Spent';
								$balance_change 		= $awardedpoints->balance_change;
								//$transaction_balance 	= $awardedpoints->balance_change + $awardedpoints->transaction_balance;
								$transaction_balance 	= $awardedpoints->balance_change + $get_balance->balance;
								$balance_log			= $awardedpoints->balance_log;

								$wpdb->insert($db_transcation_table, array(
								'user_id'          		=> $user_id,
								'order_id'       		=> $order_id,
								'points_type'			=> $points_type,
								'balance_change'		=> $balance_change,
								'transaction_balance'	=> $transaction_balance,
								'transaction_description' => $transaction_description,
								'balance_log'			=> $balance_log,
								'order_status'			=> $order_status,
								'transaction_date'		=> $transaction_date,
								'comments'				=> $comments,
								'last_updated'			=> $last_updated
								));
								aw_get_update_post_meta($order_id, $points_type, $key);
								$bal_spend_points 			 = $get_balance->spendpoints - $awardedpoints->balance_change;
								$balance_ary['spendpoints']  = $get_balance->spendpoints - $awardedpoints->balance_change;
								$balance_ary['balance']		 = $get_balance->balance + $awardedpoints->balance_change;

								$balance_ary['last_updated'] = $last_updated;

								$wpdb->update($db_balances_table, $balance_ary, array('user_id' => $user_id));
							}
						}

						if ('Earn' === $awardedpoints->points_type) {// && 1 == $check_earn) {

							$points_type			= 'Reverse Earn';
							$balance_change			= $awardedpoints->balance_change;
							//$transaction_balance 	= $awardedpoints->transaction_balance - $awardedpoints->balance_change;
							$transaction_balance 	= $get_balance->balance - $awardedpoints->balance_change;
							$balance_log			= $awardedpoints->balance_log;

							$wpdb->insert($db_transcation_table, array(
								'user_id'          		=> $user_id,
								'order_id'       		=> $order_id,
								'points_type'			=> $points_type,
								'balance_change'		=> $balance_change,
								'transaction_balance'	=> $transaction_balance,
								'transaction_description' => $transaction_description,
								'balance_log'			=> $balance_log,
								'order_status'			=> $order_status,
								'transaction_date'		=> $transaction_date,
								'comments'				=> $comments,
								'last_updated'			=> $last_updated
							));
							aw_get_update_post_meta($order_id, $points_type, $key);
							$bal_earn_points 				= $get_balance->earnedpoints - $awardedpoints->balance_change;
							$balance_ary['earnedpoints']	= $get_balance->earnedpoints - $awardedpoints->balance_change;
							$balance_ary['balance'] 		= $get_balance->balance - $awardedpoints->balance_change;
							$balance_ary['lifetime_sale']	= $get_balance->lifetime_sale - $order_grand_total;
							$balance_ary['last_updated'] 	= $last_updated;

							$wpdb->update($db_balances_table, $balance_ary, array('user_id' => $user_id));
							$check_earn++;
						}
					}
				}
			} else {

				//$get_awarded_points = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}reward_points_transaction_history WHERE `user_id` = %d AND `order_id` = %d AND (`points_type` = %s || `points_type` = %s ) ORDER BY `transaction_id` DESC LIMIT %d ", "{$user_id}", "{$order_id}", 'Reverse Earn', 'Reverse Spent', 2));
				$get_awarded_points = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}reward_points_transaction_history WHERE `user_id` = %d AND `order_id` = %d ORDER BY `transaction_id` DESC LIMIT %d ", "{$user_id}", "{$order_id}", 2));
				if (!empty($get_awarded_points)) {
					foreach ($get_awarded_points as $key=> $awardedpoints) {
						$trans_ary = array(
							'transaction_description' 	=> $transaction_description,
							'order_status'				=> $order_status
						);
						if ('Reverse Earn' == $awardedpoints->points_type || 'Reverse Spent' == $awardedpoints->points_type) {
							$wpdb->update($db_transcation_table, $trans_ary, array('transaction_id' => $awardedpoints->transaction_id));
						}
					}
				}
				return;
			}
		} else {
			return;
		}
		return;
	}

	public static function aw_reward_points_admin_save_new_order( $post_id) {

		$is_front_order = get_post_meta($post_id, '_rd_front_order', true);
		/*if (!empty($is_front_order)) {
			return;
		}

		if (isset($_POST['post_status']) && 'draft' != $_POST['post_status']) {
			return;
		}*/

		if (!empty($is_front_order) && isset($_POST['post_status']) && 'draft' != $_POST['post_status'] && isset($_POST['original_post_status']) && isset($_POST['order_status']) && $_POST['original_post_status'] != $_POST['order_status']) {
			return;
		}

		if (isset($_POST['post_type']) && 'shop_order' == $_POST['post_type']) {

			if (isset($_POST['rd_rp_order_nonce_name'])) {
				$rd_rp_order_nonce_name = sanitize_text_field($_POST['rd_rp_order_nonce_name']);
			}

			if ( !wp_verify_nonce( $rd_rp_order_nonce_name, 'rd_rp_order_nonce_action')) {
				wp_die('Our Site is protected');
			}

			global $post;
			global $wpdb;
			$order_id = $post->ID;

			$fee_name 			= '';
			$fee_total 			= 0;
			$points 			= 0;
			$db_balance_table 		= $wpdb->prefix . 'reward_points_balances';
			$db_transcation_table 	= $wpdb->prefix . 'reward_points_transaction_history';

			$order = new WC_Order($order_id);
			$order_data = $order->get_data();
			$order_status = $order->get_status();
			$user_id = $order->get_user_id();
			$order_grand_total = $order_data['total'];

			//$transaction_type = $wpdb->get_var($wpdb->prepare("SELECT `points_type` FROM {$wpdb->prefix}reward_points_transaction_history WHERE `user_id` = %d AND `order_id` = %d ORDER BY `transaction_id` DESC LIMIT %d", "{$user_id}", "{$order_id}", 1));

			$transaction_type 		= get_post_meta($order_id , '_rd_transcations', true) ? get_post_meta($order_id , '_rd_transcations', true) : array();

			//if ('Spent' == $transaction_type) {
			if (in_array('Spent', $transaction_type)) {
				$get_awarded_points = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}reward_points_transaction_history WHERE `user_id` = %d AND `order_id` = %d AND (`points_type` = %s || `points_type` = %s ) ORDER BY `transaction_id` DESC LIMIT %d ", "{$user_id}", "{$order_id}", 'Earn', 'Spent', 2));
				if (!empty($get_awarded_points)) {

					foreach ($get_awarded_points as $awardedpoints) {
						$trans_ary = array(
							'order_status'				=> $order_status
						);
						$wpdb->update($db_transcation_table, $trans_ary, array('transaction_id' => $awardedpoints->transaction_id));
					}
				}
				return;
			}

			foreach ( $order->get_items('fee') as $item_id => $item_fee ) {
				$fee_name = $item_fee->get_name();
			}

			$get_config 			= aw_reward_points_get_config();
			$customer_lifetime_sale = aw_reward_points_total_orders($user_id, $order_grand_total);

			$rates 	= unserialize($get_config['spendrates']);
			//if ('' != $fee_name && ( 'Points' === substr($fee_name, 0, 6) ) && !empty($rates) && ( 'pending' == $order_status || 'on-hold' == $order_status )) {
			if ('' != $fee_name && ( 'Points' === substr($fee_name, 0, 6) ) && !empty($rates) && 'wc-failed' != $_POST['order_status'] && 'wc-refunded' != $_POST['order_status'] && 'wc-cancelled' != $_POST['order_status'] && ( 'pending' == $order_status || 'on-hold' == $order_status || 'completed' == $order_status || 'processing' == $order_status )) {
				$config_lifetime_sale = array();
				foreach ($rates as $key => $rate) {
					if ($customer_lifetime_sale['calculation'] >= $rate['lifetime_sale']) {
						$config_lifetime_sale[] = $rate['lifetime_sale'];
					}
				}
				$spend_rates	= aw_reward_points_getClosest($customer_lifetime_sale['calculation'], $config_lifetime_sale);
				$key 			= array_search($spend_rates, array_column($rates, 'lifetime_sale'));

				$balance_log	= 'Customer Lifetime Sales >= ' . $rates[$key]['lifetime_sale'] . '::';
				$balance_log	.= 'Base Currency = ' . $rates[$key]['base_currency'] . '::';
				$balance_log	.= 'Points = ' . $rates[$key]['points'];
				$points 		= intval(preg_replace('/[^0-9]+/', '', $fee_name), 10);
				$get_balance 	= aw_reward_points_get_customer_balance($user_id);
				$currentbalance = $get_balance->balance;
				$balance 		= $currentbalance - $points;

				$expiration_day = (int) $get_config['expiration_day'];

				if (0 != $expiration_day) {
					$expiration_date = gmdate('Y-m-d', strtotime('+ ' . $get_config['expiration_day'] . ' day'));
				} else {
					$expiration_date = null;
				}

				$array['last_updated'] 		= gmdate('Y-m-d H:i:s');
				$array['balance'] 			= abs($balance);
				$array['spendpoints'] 		= $get_balance->spendpoints + abs($points);
				$array['expiration_date'] 	= $expiration_date;
				$array['reset'] 			= 0;

				$wpdb->update($db_balance_table, $array, array('user_id'=>$user_id));
				$order_status 			= $order_data['status'];
				$transaction_date 		= gmdate('Y-m-d H:i:s');
				$last_updated 			= gmdate('Y-m-d H:i:s');
				$points_type			= 'Spent';
				$comments 				= '';
				$wpdb->insert($db_transcation_table, array(
					'user_id'          		=> $user_id,
					'order_id'       		=> $order_id,
					'points_type'			=> $points_type,
					'balance_change'		=> abs($points),
					'transaction_balance'	=> $balance,
					'transaction_description' => 'Points are spent on order #' . $order_id,
					'balance_log'			=> $balance_log,
					'order_status'			=> $order_status,
					'transaction_date'		=> $transaction_date,
					'comments'				=> $comments,
					'last_updated'			=> $last_updated
				));
				aw_get_update_post_meta($order_id, $points_type, 0);
			}
		} else {
			return;
		}
		return;
	}

	public static function aw_reward_points_get_customer_total_order( $user_id, $order_grand_total) {
		$customer_orders = get_posts( array(
		'numberposts' => - 1,
		'meta_key'    => '_customer_user',
		'meta_value'  => $user_id,
		'post_type'   => array('shop_order'),
		'post_status' => array('wc-completed', 'wc-processing', 'trash', 'untrash')
		));

		$total = array('actual' => 0, 'calculation' => 0);
		$orders_total = 0;

		foreach ($customer_orders as $customer_order) {
			$order = wc_get_order( $customer_order );
			$orders_total += $order->get_total();
		}

		$total['actual'] = $orders_total;
		$total['calculation'] = $orders_total - $order_grand_total;
		if ($total['calculation'] < 0) {
			$total['calculation'] = 0;
		}
		return $total;
	}

	/* Ajax Call for tab content on change of tab*/
	public static function aw_reward_points_tabcontent() {
		require_once(plugin_dir_path(__FILE__) . 'includes/aw-reward-transaction-balance.php');
		global $wpdb;
		$seach_key = '';
		$tabName   = '';
		if (isset($_REQUEST['tabName'])) {
			if (isset($_REQUEST['tabName'])) {
				$tabName = sanitize_text_field($_REQUEST['tabName']);
			}
			if (isset($_REQUEST['s'])) {
				$seach_key = sanitize_text_field($_REQUEST['s']);
			}

			$table_trans = new AwRewardTransactionBalance();
			$table_trans->prepare_items($tabName, $seach_key);

			if ('balance-tab' === $tabName) {
				$tablename = 'reward_points_balances';
				$count_all = $table_trans->get_count($tablename, '', $tabName);
				echo wp_kses('<ul class="subsubsub">
					<li class="all"><b style="color:#000000;">All</b> <span class="count">(' . $count_all . ')</span></li>
					</ul>', wp_kses_allowed_html('post'));

				echo '<form id="balance-table" method="GET">';
			}
			if ('transaction-tab' === $tabName) {
				echo '<form id="transaction-table" method="GET">';
			}
			echo wp_kses($table_trans->display(), wp_kses_allowed_html('post'));
			if ('transaction-tab' === $tabName) {
				echo '</form>';
			}
			die;
		}
	}

	public static function aw_reward_points_balance_update_form() {
		global $wpdb;
		$url =  admin_url() . 'admin.php?page=reward-transaction-balance';
		$db_balance_table = $wpdb->prefix . 'reward_points_balances'; 
		$db_transcation_table = $wpdb->prefix . 'reward_points_transaction_history'; 
		$points 	= 0;

		$count 		= 0;
		$comments 	= '';

		if (isset($_POST['rdrewardpoints_admin_nonce'])) {
			$rdrewardpoints_admin_nonce = sanitize_text_field($_POST['rdrewardpoints_admin_nonce']);
		}

		if ( !wp_verify_nonce( $rdrewardpoints_admin_nonce, 'balance_update_form' )) {
			wp_die('Our Site is protected');
		}

		if (!empty($_POST)) {
			if (isset($_POST['points'])) {
				$points = sanitize_text_field($_POST['points']);
			}
			if (isset($_POST['update_comments'])) {
				$comments = sanitize_text_field($_POST['update_comments']);
			}
			if (isset($_POST['user_id'])) {
				$userids = explode(',', sanitize_text_field($_POST['user_id']));
				$sql = '';
				$expiration_day = 0 ;
				$get_config 		= aw_reward_points_get_config();
				if (isset($get_config['expiration_day'])) {
					$expiration_day 	= (int) $get_config['expiration_day'];	
				}
				
				if (0 != $expiration_day) {
					$expiration_date = gmdate('Y-m-d', strtotime('+ ' . $get_config['expiration_day'] . ' day'));
				} else {
					$expiration_date = null;
				}

				foreach ($userids as $user_id) {

					$flag = 1;
					$exist_user_id = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM {$wpdb->prefix}reward_points_balances WHERE user_id = %d", "{$user_id}"));
					if (empty($exist_user_id)) {
						$balance_ary['user_id'] 		= $user_id;
						$balance_ary['lifetime_sale']	= '0.00';
						$balance_ary['earnedpoints'] 	= '0.00';
						$balance_ary['spendpoints'] 	= '0.00';
						$balance_ary['balance'] 		= '0.00';

						$wpdb->insert($db_balance_table, $balance_ary);
					}

					$currentbalance = $wpdb->get_row($wpdb->prepare("SELECT balance , earnedpoints, spendpoints FROM {$wpdb->prefix}reward_points_balances WHERE user_id = %d", "{$user_id}"));
					if (!empty($currentbalance)) {
						$balance 	 = $points + $currentbalance->balance;
					}
					if (0 == $points) {
						$count++;
						$flag = 0;
					} else if ($points < 0) {
						$points_type = 'Admin Reverse Earn';
						$spendpoints = $points + $currentbalance->balance;
						if (0 == $currentbalance->spendpoints) {
							if ($currentbalance->balance < abs($points)) {
									$count++;
									$flag = 0;
							} else {
								$array['spendpoints']	= abs($points);	
							}
						} else if ($currentbalance->balance < abs($points)) { 
								$count++;
								$flag = 0;
						} else {
							$array['spendpoints']  = $currentbalance->spendpoints+abs($points);
						} 
						 
					} else {
						$points_type = 'Admin Earn';
						$earnedpoints				= $points + $currentbalance->earnedpoints;
						$array['earnedpoints']		= abs($earnedpoints);
						$array['expiration_date']	= $expiration_date;
						$array['reset'] 			= 0;
					}
					if ($balance < 0) {
						$balance = 0;
					}

					$array['last_updated'] 	= gmdate('Y-m-d H:i:s');
					$array['balance'] 		= abs($balance);

					if ($flag) {
						$wpdb->update($db_balance_table, $array, array('user_id'=>$user_id));

						$transaction_date 		= gmdate('Y-m-d H:i:s');
						$last_updated 			= gmdate('Y-m-d H:i:s');

						$wpdb->insert($db_transcation_table, array(
							'user_id'          		=> $user_id,
							'order_id'       		=> 0,
							'points_type'			=> $points_type,
							'balance_change'		=> abs($points),
							'transaction_balance'	=> $balance,
							'transaction_description' => 'Balance is updated by Admin',
							'balance_log'			=> '',
							'order_status'			=> '',
							'transaction_date'		=> $transaction_date,
							'comments'				=> $comments,
							'last_updated'			=> $last_updated
						));
					}
				}
				if ($count>0) {
					self::aw_reward_points_add_flash_notice( __('Balance can\'t be updated for ' . $count . ' users'), 'error', false );
				} else {
					self::aw_reward_points_add_flash_notice( __('Points balance updated'), 'success', false );
				}
			}
		}
		wp_redirect($url);
	}

	public static function aw_reward_points_cookies() {
		global $wpdb;
		$apply_point_cookie = $wpdb->prefix . 'woocommerce_I3QIn9ctULD';
		$path = '/';
		$host = parse_url(get_option('siteurl'), PHP_URL_HOST);

		if (!isset($_COOKIE[$apply_point_cookie])) {
			setcookie($apply_point_cookie, '0', 0, $path, $host);
		}
	}

	public static function aw_reward_points_admin_cookies() {
		global $wpdb,$pagenow;
		if ('update.php' != $pagenow) {
			$apply_admin_point_cookie = $wpdb->prefix . 'woocommerce_admin_I3QIn9ctULD';
			$apply_admin_point_points = $wpdb->prefix . 'woocommerce_admin_points_I3QIn9ctULD';
			$apply_admin_point_user = $wpdb->prefix . 'woocommerce_admin_user_I3QIn9ctULD';

			$path = '/';
			$host = parse_url(get_option('siteurl'), PHP_URL_HOST);
			setcookie($apply_admin_point_cookie, '0', 0, $path, $host);
			setcookie($apply_admin_point_points, $wpdb->prefix, 0, $path, $host);
			setcookie($apply_admin_point_user, '0', 0, $path, $host);
		}

	}

	public static function aw_reward_points_logout() {
		global $wpdb;
		$apply_point_cookie = $wpdb->prefix . 'woocommerce_I3QIn9ctULD';
		$path = '/';
		$host = parse_url(get_option('siteurl'), PHP_URL_HOST);

		if (isset($_COOKIE[$apply_point_cookie])) {
			setcookie($apply_point_cookie, '0', 0, $path, $host);
		}
	}

	public static function aw_reset_balance_after_expiry() {
		global $wpdb;
		$todaydate = strtotime(gmdate('Y-m-d'));
		$db_balance_table 		= $wpdb->prefix . 'reward_points_balances';
		$db_transcation_table 	= $wpdb->prefix . 'reward_points_transaction_history';
		
		if (( $wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ' , "{$wpdb->prefix}reward_points_balances")) == $db_balance_table ) && ( $wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ' , "{$wpdb->prefix}reward_points_transaction_history")) == $db_transcation_table )) {
			$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}reward_points_balances WHERE `reset` != %d AND `expiration_date` != %s" , 1 , ''));

			if (!empty($results)) {	
				foreach ($results as $key => $result) {
					if (strtotime($result->expiration_date) < $todaydate) {
						$get_balance 	= aw_reward_points_get_customer_balance( $result->user_id) ;
						$balance 		= $get_balance->balance;
						$user_id 		= $get_balance->user_id;

						$last_updated 		= gmdate('Y-m-d H:i:s');
						$transaction_date 	= gmdate('Y-m-d H:i:s');
						$balance_array	= array(
											'user_id'			=> $user_id,
											'balance' 			=> 0,
											'spendpoints' 		=> 0,
											'earnedpoints' 		=> 0,
											'reset'				=> 1,
											'last_updated'		=> $last_updated,
											'transaction_date'	=> $transaction_date
											);
						if (0 != $balance && 1 != $result->reset ) {
							$wpdb->update($db_balance_table, $balance_array, array('user_id'=>$user_id));

							$order_status 	= 'Expired';
							$points_type	= 'Expired';
							$wpdb->insert($db_transcation_table, array(
							'user_id'          			=> $user_id,
							'order_id'       			=> 0,
							'points_type'				=> $points_type,
							'balance_change'			=> $balance,
							'transaction_balance'		=> 0,
							'transaction_description' 	=>'Balance is expired',
							'order_status'				=> $order_status,
							'transaction_date'			=> $transaction_date,
							'last_updated'				=> $last_updated
							));
						}
					}
				}
			}
		}
	}

	public static function aw_reward_points_pre_notice_earn() {
		$points = aw_reward_points_display_earn_notice();
		$get_config = aw_reward_points_get_config();

		if ( $points && !empty($get_config['display_earn']) && 'YES' == $get_config['display_earn']) {

			if (is_page( 'cart' ) || is_cart() ) {
				 wc_print_notice( __( '<div id="msg_earn_point">' . str_replace('&lt;total amount&gt;', $points, $get_config['promotext'] ) . '</div>', 'woocommerce ttt' ), 'success' );
			} else {
				$info_message = apply_filters( 'woocommerce_checkout_coupon_message', __( str_replace('&lt;total amount&gt;', $points, $get_config['promotext'] ), 'woocommerce' ));
				wc_print_notice( $info_message, 'success' );
			}
		}
	}
}

function aw_reward_points_on_hold_spent_point( $order_id, $currentbalance = 0) { 
		global $wpdb;
		$fee_name 			= '';
		$fee_total 			= 0;
		$points 			= 0;
		$db_balance_table 		= $wpdb->prefix . 'reward_points_balances';
		$db_transcation_table 	= $wpdb->prefix . 'reward_points_transaction_history';

		$order = new WC_Order($order_id);
		$order_data = $order->get_data();
		$order_status = $order->get_status();
		$user_id = $order->get_user_id();
		$order_grand_total = $order_data['total'];

	foreach ( $order->get_items('fee') as $item_id => $item_fee ) {
		$fee_name = $item_fee->get_name();
	}

		$get_config 			= aw_reward_points_get_config();
		$customer_lifetime_sale = aw_reward_points_total_orders($user_id, $order_grand_total);

		$rates 	= unserialize($get_config['spendrates']);

	if ('' != $fee_name && ( 'Points' === substr($fee_name, 0, 6) ) && !empty($rates)) {
		$config_lifetime_sale = array();
		foreach ($rates as $key => $rate) {
			if ($customer_lifetime_sale['calculation'] >= $rate['lifetime_sale']) {
				$config_lifetime_sale[] = $rate['lifetime_sale'];
			}
		}
		$spend_rates	= aw_reward_points_getClosest($customer_lifetime_sale['calculation'], $config_lifetime_sale);
		$key 			= array_search($spend_rates, array_column($rates, 'lifetime_sale'));
		$balance_log	= 'Customer Lifetime Sales >= ' . $rates[$key]['lifetime_sale'] . '::';
		$balance_log	.= 'Base Currency = ' . $rates[$key]['base_currency'] . '::';
		$balance_log	.= 'Points = ' . $rates[$key]['points'];

		$points 		= intval(preg_replace('/[^0-9]+/', '', $fee_name), 10);
		$get_balance 	= aw_reward_points_get_customer_balance($user_id);
		$get_transaction_balance = aw_reward_points_last_transaction_balance($user_id);

		$currentbalance = $get_transaction_balance->transaction_balance;

		$balance 		=   $currentbalance - $points;
		$expiration_day = (int) $get_config['expiration_day'];

		if (0 != $expiration_day) {
			$expiration_date = gmdate('Y-m-d', strtotime('+ ' . $get_config['expiration_day'] . ' day'));
		} else {
			$expiration_date = null;
		}

		$array['last_updated'] 		= gmdate('Y-m-d H:i:s');
		$array['balance']           = abs($balance); 
		$array['spendpoints']       = $get_balance->spendpoints + abs($points);
		$array['expiration_date']   = $expiration_date;

		$wpdb->update($db_balance_table, $array, array('user_id'=>$user_id));

		$order_status 			= $order_data['status'];
		$transaction_date 		= gmdate('Y-m-d H:i:s');
		$last_updated 			= gmdate('Y-m-d H:i:s');
		$points_type			= 'Spent';
		$comments 				= '';
		$wpdb->insert($db_transcation_table, array(
		'user_id'          		=> $user_id,
		'order_id'       		=> $order_id,
		'points_type'			=> $points_type,
		'balance_change'		=> abs($points),
		'transaction_balance'	=> $balance,
		'transaction_description' => 'Points are spent on order #' . $order_id,
		'balance_log'			=> $balance_log,
		'order_status'			=> $order_status,
		'transaction_date'		=> $transaction_date,
		'comments'				=> $comments,
		'last_updated'			=> $last_updated
		));
		aw_get_update_post_meta($order_id, $points_type, 1);
		return abs($points);
	}
}

function aw_reward_points_total_orders( $user_id, $order_grand_total) {
		$customer_orders = get_posts( array(
		'numberposts' => - 1,
		'meta_key'    => '_customer_user',
		'meta_value'  => $user_id,
		'post_type'   => array('shop_order'),
		'post_status' => array('wc-completed', 'wc-processing')
		));
		$total = array('actual' => 0, 'calculation' => 0);
		$orders_total = 0;

	foreach ($customer_orders as $customer_order) {
		$order = wc_get_order( $customer_order );
		$orders_total += $order->get_total();
	}

		$total['actual'] = $orders_total;
		$total['calculation'] = $orders_total - $order_grand_total;
	if ($total['calculation'] < 0) {
		$total['calculation'] = 0;			
	}
		return $total;
}

function aw_reward_points_getClosest( $search, $arr) {
	$closest = null;
	foreach ($arr as $item) {
		if (null === $closest || abs($search - $closest) > abs($item - $search)) {
			$closest = $item;
		}
	}
	return $closest;
}

function aw_reward_points_get_user() {
	if (is_user_logged_in()) {
		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
		return $user_id;
	} else {
		return false;
	}
}

function aw_reward_points_get_config() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'reward_points_config';
	$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}reward_points_config WHERE 1 = %d", '1'), ARRAY_A);
	return $result;
}

function aw_reward_points_get_customer_balance( $user_id) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'reward_points_balances';
	$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}reward_points_balances WHERE `user_id` = %d", "{$user_id}"));
	return $result;
}
function aw_reward_points_last_transaction_balance( $user_id) {
	global $wpdb;
	$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}reward_points_transaction_history WHERE `user_id` = %d  ORDER BY `transaction_id` DESC LIMIT %d", "{$user_id}", 1));
	return $result;
}
function aw_kses_filter_allowed_html( $allowed, $context) {
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

function aw_get_update_post_meta( $post_id, $operation, $flag = 1) {
	$post_meta_array = array();
	$post_meta_array = get_post_meta($post_id , '_rd_transcations', true);
	if ( 0 === $flag) {
		update_post_meta($post_id, '_rd_transcations', '');
		$post_meta_array = array();
	} 
	if (!empty($post_meta_array)) {
		array_push($post_meta_array, $operation);	
	} else {
		$post_meta_array = array($operation);
	}
	update_post_meta($post_id, '_rd_transcations', $post_meta_array);
}


/*add_action('woocommerce_before_cart','testing');*/
function aw_reward_points_display_earn_notice() {

	$get_config = aw_reward_points_get_config();
	if ( is_user_logged_in() && !empty($get_config['display_earn']) && 'YES' == $get_config['display_earn']) {
		$user_id 		= get_current_user_id();
		$get_balance 	= aw_reward_points_get_customer_balance($user_id);
		$get_config 	= aw_reward_points_get_config();
		 
		$config_lifetime_sale = array();
		$customer_lifetime_sale = $get_balance->lifetime_sale;

		if (!empty($get_config['earnrates'])) {
			$earn_rates = unserialize($get_config['earnrates']);
			foreach ($earn_rates as $key => $rate) {
				if ($customer_lifetime_sale >= $rate['lifetime_sale']) {
					$config_lifetime_sale[] = $rate['lifetime_sale'];
				}
			}
		}
		$customer_rates = aw_reward_points_getClosest($customer_lifetime_sale, $config_lifetime_sale);
		if (null == $customer_rates) {
			return;
		}
	
		$order_item_total 	= WC()->cart->total;
		$cart_tax 			= WC()->cart->tax_total;

		$total 				= $order_item_total-$cart_tax;
		$order_grand_total 	= $total;
		/* Points are calculated according to Earn rate in the following way: Points = Subtotal including Discounts / Coupons, but excluded Shipping (Taxes are incl. or excl. - according to the settings) https://aheadworks.atlassian.net/projects/WOOPOINTS/issues/WOOPOINTS-9 */
		if ('AT' === $get_config['beforafter_calculation']) {
			if (!empty($cart_tax)) {
				$total = $total + $cart_tax;	
			}
		}

		//echo $customer_lifetime_sale;
		$customer_orders = get_posts( array(
		'numberposts' => - 1,
		'meta_key'    => '_customer_user',
		'meta_value'  => $user_id,
		'post_type'   => array('shop_order'),
		'post_status' => array('wc-completed', 'wc-processing', 'trash', 'untrash')
		));

		$customer_lifetime_sale = array('actual' => 0, 'calculation' => 0);
		$orders_total = 0;

		foreach ($customer_orders as $customer_order) {
			$order = wc_get_order( $customer_order );
			$orders_total += $order->get_total();
		}
		 
		$customer_lifetime_sale['calculation'] = $orders_total;
		$rates 			= unserialize($get_config['earnrates']);

		if (!empty($rates) ) {
			$config_lifetime_sale = array();
			foreach ($rates as $key => $rate) {
				if ($customer_lifetime_sale['calculation'] >= $rate['lifetime_sale']) {
					$config_lifetime_sale[] = $rate['lifetime_sale'];
				}
			}
			//print_r($customer_lifetime_sale);
			$earn_rates = aw_reward_points_getClosest($customer_lifetime_sale['calculation'], $config_lifetime_sale);
			$key = array_search($earn_rates, array_column($rates, 'lifetime_sale'));

			/*
				Balance Change (amount of transaction; can be a positive or negative value)
				Balance (Balance value after it' has been changed)
			*/
			$base_currency 			= $rates[$key]['base_currency'];
			$points 				= $rates[$key]['points'];
 
			if ($total>0) {
				$total = $total;
			} else {
				$total = 0;
			}

			/* Changed Round Off Thing After Discussion on Call and Chat - 13Jan2020 https://aheadworks.atlassian.net/projects/WOOPOINTS/issues/WOOPOINTS-9 */
			$earnedpoints			=  ( $total / $base_currency ) * $points ;
			$balance_change			= (int) $earnedpoints;
			return $balance_change;
		} else {
			return false;
		}

	} else {
		return false;
	}

}

 
