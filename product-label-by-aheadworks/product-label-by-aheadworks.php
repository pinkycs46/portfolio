<?php
/**
 * Plugin Name: Product Labels By Aheadworks
 * Plugin URI: https://www.aheadworks.com/
 * Description: Product Labels By Aheadworks for WooCommerce. Show promotions with product labels.
 * Author: Aheadworks
 * Author URI: https://www.aheadworks.com/
 * Version: 1.0.0
 * Text Domain: aw_product_label-by-aheadworks
 *
 * @package aw_product_label-by-aheadworks
 *
 * Requires at least: 5.4.7
 * Tested up to: 5.8.1
 * WC requires at least: 4.3.3
 * WC tested up to: 5.6.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

require_once(plugin_dir_path(__FILE__) . 'includes/aw-product-label-admin.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-product-rule-admin.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-product-label-list.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-product-label-rule-list.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-product-label-public.php');

$productlabel = new AwProductLabel();

/** Present plugin version **/
define( 'AW_PRODUCT_LABEL_VERSION', '1.0.0' );

class AwProductLabel {
	public function __construct() {
		/** Constructor function, initialize and register hooks **/
		add_action('admin_init', array(get_called_class(),'aw_product_label_installer'));
		register_uninstall_hook(__FILE__, array(get_called_class(), 'aw_product_label_unistaller'));
		if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
			add_action('admin_menu', array(get_called_class(),'aw_product_label_admin_menu'));
		}
		add_filter('set-screen-option', array(get_called_class(),'aw_pl_set_screen'), 10, 3);

		add_action('admin_enqueue_scripts', array(get_called_class(),'aw_pl_label_admin_addScript'));
		add_action('wp_enqueue_scripts', array(get_called_class(),'aw_pl_label_public_addScript'));

		add_action('admin_post_aw_pl_save_label_form', array('AwProductLabelAdmin','aw_pl_save_label_form'));
		add_action('wp_ajax_aw_label_image_delete', 'aw_label_image_delete');
		add_action('wp_ajax_nopriv_aw_label_image_delete', 'aw_label_image_delete');

		add_action('admin_post_aw_pl_save_rule_form', array('AwProductRuleAdmin','aw_pl_save_rule_form'));
		add_action('admin_post_aw_pl_save_setting_form', array('AwProductLabelAdmin','aw_pl_save_setting_form'));
		
		add_action( 'wp_ajax_dynamic_attribute_ajax', array(get_called_class(),'aw_pl_dynamic_attribute_ajax' ));
		add_action('wp_ajax_nopriv_dynamic_attribute_ajax', array(get_called_class(), 'aw_pl_dynamic_attribute_ajax'));

		add_action('admin_footer' , array('AwProductRuleAdmin','aw_pl_add_new_product_popup'));
		add_action('wp_ajax_aw_pl_fetch_woo_product_list' , array('AwProductRuleAdmin','aw_pl_fetch_woo_product_list'));
		add_action('wp_ajax_nopriv_aw_pl_fetch_woo_product_list', array('AwProductRuleAdmin','aw_pl_fetch_woo_product_list'));


		if (! is_admin()) {
			add_filter( 'woocommerce_product_get_image', array('AwProductLabelPublic','aw_pl_label_shop_page'), 100000, 2 );
		}

		add_action( 'woocommerce_after_shop_loop_item', array('AwProductLabelPublic','aw_pl_label_next_price_shop_page'), 10, 0 ); 

		add_filter( 'woocommerce_single_product_image_thumbnail_html', array('AwProductLabelPublic','aw_pl_label_single_product_page'), 10, 2 );

		add_action('woocommerce_before_add_to_cart_form', array('AwProductLabelPublic','aw_pl_label_next_price_single_product_page'), 10, 0 );

		add_filter('wp_kses_allowed_html', 'aw_pl_kses_filter_allowed_html', 10, 2);
		add_filter( 'safe_style_css', function( $style) {
			$style[] = 'display';
			return $style;
		} );
		add_filter( 'woocommerce_cart_item_price', array('AwProductLabelPublic','aw_pl_label_cart_next_price'), 10, 3);

		//add_filter( 'woocommerce_cart_item_thumbnail', array('AwProductLabelPublic','aw_pl_label_cart_item_thumbnail'),10,3);
	}
	public static function aw_pl_label_admin_addScript() {
		$path 		= parse_url(get_option('siteurl'), PHP_URL_PATH);
		$host 	 	= parse_url(get_option('siteurl'), PHP_URL_HOST);
		$batc_nonce	= wp_create_nonce('aw_batc_admin_nonce');

		wp_register_style('awproductlabeladmincss', plugins_url('/admin/css/aw-product-label-admin.css', __FILE__ ), array(), '1.0' );
		wp_enqueue_style('awproductlabeladmincss');


		wp_register_style('awproductruleadmincss', plugins_url('/admin/css/aw-product-rule-admin.css', __FILE__ ), array(), '1.0' );
		wp_enqueue_style('awproductruleadmincss');


		wp_register_script('awproductlabeladminjs', plugins_url('/admin/js/aw-product-label-admin.js' , __FILE__ ), array(), '1.0' );
		$js_var = array('site_url' => get_option('siteurl'));
		wp_localize_script('awproductlabeladminjs', 'js_var', $js_var);
		wp_register_script('awproductlabeladminjs', plugins_url('/admin/js/aw-product-label-admin.js' , __FILE__ ), array(), '1.0' );
		wp_enqueue_script('awproductlabeladminjs');


		wp_register_script('awproductruleadminjs', plugins_url('/admin/js/aw-product-rule-admin.js' , __FILE__ ), array(), '1.0' );
		$js_var_rule = array('site_url' => get_option('siteurl'), 'path' => $path, 'host' => $host, 'aw_batc_nonce' => $batc_nonce);
		wp_localize_script('awproductruleadminjs', 'js_rule_var', $js_var_rule);
		wp_register_script('awproductruleadminjs', plugins_url('/admin/js/aw-product-rule-admin.js' , __FILE__ ), array(), '1.0' );
		wp_enqueue_script('awproductruleadminjs');
		wp_localize_script( 'awproductruleadminjs', 'postdata', array('ajax_url' => admin_url( 'admin-ajax.php' ), 'ajax_nonce' => wp_create_nonce( 'ihs_nonce_action_name' ),) );

		// Load the datepicker script (pre-registered in WordPress).
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css', array(), '1.0' );
		wp_enqueue_style( 'jquery-ui' );
	   
		wp_register_style('awproductlabelladmincss', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), '1.0' );
		wp_enqueue_style('awproductlabelladmincss');		

		wp_register_script('pl_color_palates_adminjs', plugins_url('/admin/js/aw-product-label-jscolor.js' , __FILE__ ), array(), '1.0' );
		wp_enqueue_script('pl_color_palates_adminjs');
	}
	public static function aw_pl_label_public_addScript() {
		$path 		= parse_url(get_option('siteurl'), PHP_URL_PATH);
		$host 	 	= parse_url(get_option('siteurl'), PHP_URL_HOST);
		$plnonce = wp_create_nonce('product_label_nonce');

		/** Add pl CSS and JS files Public Side**/
		wp_register_style('awproductlabelpubliccss', plugins_url('/public/css/aw-product-label-public.css', __FILE__ ), array(), '1.0' );
		wp_enqueue_style('awproductlabelpubliccss');

		/*wp_register_script('awproductlabelpublicjs', plugins_url('/public/js/aw-product-label-public.js', __FILE__ ), array('jquery'), '1.0' );

		$js_var = array('site_url' => get_option('siteurl'), 'path' => $path, 'host' => $host, 'aw_pl_nonce' => $plnonce);
		wp_localize_script('awproductlabelpublicjs', 'js_pl_var', $js_var);
		wp_register_script('awproductlabelpublicjs', plugins_url('/public/js/aw-product-label-public.js', __FILE__ ), array('jquery'), '1.0' );
		wp_enqueue_script('awproductlabelpublicjs');*/

	}
	public static function aw_product_label_installer() {
		/** Check WooCommerce plugin activated ? **/
		if (is_admin()) {
			if (!is_plugin_active( 'woocommerce/woocommerce.php')) {
				/** If WooCommerce plugin is not activated show notice **/
				add_action('admin_notices', array('AwProductLabelAdmin','aw_pl_self_deactivate_notice'));

				/** Deactivate our plugin **/
				deactivate_plugins(plugin_basename(__FILE__));

				if (isset($_GET['activate'])) {
					unset($_GET['activate']);
				}
			} else {
				wp_deregister_script( 'autosave' );

				global $wpdb;
				$db_label_table_name = $wpdb->prefix . 'aw_pl_product_label'; 
				$db_rule_table_name = $wpdb->prefix . 'aw_pl_product_rule'; 
				//$db_product_condition_table_name = $wpdb->prefix . 'aw_pl_product_condition';  

				$charset_collate = $wpdb->get_charset_collate();

				//Check to see if the table exists already, if not, then create it
				if ($wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ' , "{$wpdb->prefix}aw_pl_product_label")) != $db_label_table_name) {
					$sql = "CREATE TABLE {$wpdb->prefix}aw_pl_product_label (
							 `id` int(11) NOT NULL AUTO_INCREMENT,
							 `name` varchar(55) NOT NULL,
							 `position` varchar(55) NOT NULL,
							 `status` int(2) NOT NULL,
							 `type` varchar(55) NOT NULL,
							 `shape_type` varchar(55) DEFAULT NULL,
							 `label_image` text DEFAULT NULL,
							 `label_size` varchar(55) NOT NULL,
							 `label_test_text` varchar(55) NOT NULL,
							 `label_container_css` text NOT NULL,
							 `medium_label_container_css` text NOT NULL,
							 `small_label_container_css` text NOT NULL,
							 `label_text_css` text NOT NULL,
							 `medium_label_text_css` text NOT NULL,
							 `small_label_text_css` text NOT NULL,
							 `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
							 PRIMARY KEY (`id`)
							);";
					require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
					dbDelta($sql);
				}
				if ($wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ' , "{$wpdb->prefix}aw_pl_product_rule")) != $db_rule_table_name) {
					$sql = "CREATE TABLE {$wpdb->prefix}aw_pl_product_rule (
						`rule_id` int(11) NOT NULL AUTO_INCREMENT,
						`rule_name` varchar(55) NOT NULL,
						`label_name` varchar(55) NOT NULL,
						`priority` int(11) NOT NULL,
						`rule_status` int(2) NOT NULL,
						`rule_allow_to_user` varchar(55) NOT NULL,
						`start_date` date NOT NULL,
 						`end_date` date NOT NULL,
						`frontend_label_text` varchar(55) CHARACTER SET utf8 COLLATE utf8_unicode_520_ci NOT NULL,
 						`frontend_medium_text` varchar(55) CHARACTER SET utf8 COLLATE utf8_unicode_520_ci NOT NULL,
 						`frontend_small_text` varchar(55) CHARACTER SET utf8 COLLATE utf8_unicode_520_ci NOT NULL,
						`product_condition` text NOT NULL,
						`product_id` longtext NOT NULL,
						`label_id` int(11) NOT NULL,
						`rule_last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
						 PRIMARY KEY (`rule_id`),KEY `foreign_key` (`label_id`),
						CONSTRAINT `{$wpdb->prefix}aw_pl_product_rule` FOREIGN KEY (`label_id`)
						REFERENCES `{$wpdb->prefix}aw_pl_product_label`(`id`)
						)";
					require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
					dbDelta($sql);
				}
			}
		}
	}
	public static function aw_product_label_unistaller() {
		/*Perform required operations at time of plugin uninstallation*/
		global $wpdb;
		$db_label_table_name = $wpdb->prefix . 'aw_pl_product_label'; 
		$db_rule_table_name = $wpdb->prefix . 'aw_pl_product_rule'; 

		if (is_multisite()) {
			$blogs_ids = get_sites(); 
			foreach ( $blogs_ids as $b ) { 
				$wpdb->prefix = $wpdb->get_blog_prefix($b->blog_id);	
				$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aw_pl_product_rule");		 
				$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aw_pl_product_label");
				
			} 
		} else {
			$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aw_pl_product_rule");
			$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aw_pl_product_label");
		}	
		delete_option('aw_pl_setting_label_distance');
		delete_option('number_of_labels_over_product_image');
		delete_option('number_of_labels_next_to_price');
		delete_option('aw_pl_label_select');
	}
	public static function aw_product_label_admin_menu() {
		global $submenu;
		if (!empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['REQUEST_URI'])) {
			$host = sanitize_text_field($_SERVER['HTTP_HOST']);
			$request = sanitize_text_field($_SERVER['REQUEST_URI']);
			$current_url='http://' . $host . $request;	
		}
		
		add_menu_page(__('Product Labels', 'main_menu'), __('Product Labels', 'main_menu'), '', 'product-label', '', plugin_dir_url(__FILE__) . '/admin/images/product_label_new.png', 25);
		add_submenu_page('product-label', __('General Settings'), __('General Settings'), 'manage_options', 'aw_pl_setting_page', array('AwProductLabelAdmin' , 'aw_pl_setting_page'));
		$hook = add_submenu_page('product-label', __('Product Labels'), __('Product Labels'), 'manage_options', 'aw_pl_label_page', array('AwProductLabelAdmin' , 'aw_pl_label_page'));
		if (get_admin_url() . 'admin.php?page=new-label'== $current_url) {
			add_submenu_page('admin.php?page=new-label', __('New Label'), '', 'manage_options', 'new-label', array('AwProductLabelAdmin' , 'aw_pl_label_new_page'));
			remove_menu_page('admin.php?page=new-label', 'new-label');
			
		} else {
			add_submenu_page('admin.php?page=new-label', __('Edit Label'), '', 'manage_options', 'new-label', array('AwProductLabelAdmin' , 'aw_pl_label_new_page'));
			remove_menu_page('admin.php?page=new-label', 'new-label');
		}
	
		$hooks = add_submenu_page('product-label', __('Product Rules'), __('Product Rules'), 'manage_options', 'aw_pl_rule_page', array('AwProductRuleAdmin' , 'aw_pl_rule_page') );
		if (get_admin_url() . 'admin.php?page=new-rule'== $current_url) {
			add_submenu_page('admin.php?page=new-rule', __('New Rule'), '', 'manage_options', 'new-rule', array('AwProductRuleAdmin' , 'aw_pl_rule_new_page'));
		remove_menu_page('admin.php?page=new-rule', 'new-rule');
			
		} else {
			add_submenu_page('admin.php?page=new-rule', __('Edit Rule'), '', 'manage_options', 'new-rule', array('AwProductRuleAdmin' , 'aw_pl_rule_new_page'));
		remove_menu_page('admin.php?page=new-rule', 'new-rule');
		}
		

		add_action( "load-$hook", array('AwProductLableList','aw_pl_label_add_screen_option'));
		add_action( "load-$hooks", array('AwProductRuleList','aw_pl_rule_add_screen_option'));
	
	}
	public static function aw_pl_set_screen( $status, $option, $value) { 
		if ('labels_per_page' == $option) {
			return $value;
		}	
		if ('rule_per_page' == $option) {
			return $value;
		}
		return $status;
	}

	public static function aw_pl_dynamic_attribute_ajax() {
		global $wpdb;
		// If nonce verification fails die.
		
		if (isset($_POST['security'])) {
			$security = sanitize_text_field($_POST['security']);
			if ( ! wp_verify_nonce($security, 'ihs_nonce_action_name' ) ) {
				wp_die();
			}
		}
		
		$arr=array();
		if (isset($_POST['color'])) {
			$attribute = sanitize_text_field($_POST['color']);
			$terms = get_terms('pa_' . $attribute . '');	
		}
		
		foreach ( $terms as $term ) {
			$arr[]=$term->name;
		}
		wp_send_json_success( array(
		 'data'               => $arr,
		 'data_recieved_from_js' => $_POST,
		 ) );
	}
}

function aw_label_image_delete() {
	global $wpdb;
	if (isset($_REQUEST['postid'])) {
		$post_id 	= wp_kses_post($_REQUEST['postid']);
		$label_data = aw_pl_label_row($post_id);		
		$label_image  	= $label_data->label_image;
		if (isset($label_image)) {
			$path = wp_get_upload_dir();
			$imagepath = explode('uploads', $label_image) ;
			if (isset($path['basedir']) && isset($imagepath[1])) {
				$fullpath  = $path['basedir'] . $imagepath[1];	
				if (file_exists($fullpath)) {
					if (unlink($fullpath)) {
						$label_image = '';
						//update_post_meta($post_id, 'popup_pro_subscribe_design', array_filter($cat_data));
						$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aw_pl_product_label SET label_image =''  WHERE id = %s" , "{$post_id}"));
						echo 'Image deleted successfully';
					}	
				} else {
					echo 'No image available !';
				}
			}
		} else {
			echo 'Image deleted successfully';
		}
	} else {
		echo 0;
	}
die;
}

function aw_pl_kses_filter_allowed_html( $allowed, $context) {
	$allowed['style'] 				= array();
	$allowed['select'] 				= array();
	$allowed['select']['name'] 		= array();
	$allowed['select']['id'] 		= array();
	$allowed['select']['class'] 	= array();
	$allowed['select']['onchange'] 	= array();
	$allowed['select']['style']		= array();
	$allowed['option'] 				= array();
	$allowed['option']['class'] 	= array();
	$allowed['option']['value']		= array();
	$allowed['option']['selected']	= array();
	$allowed['input']['type'] 		= array();
	$allowed['input']['name'] 		= array();
	$allowed['input']['value'] 		= array();
	$allowed['input']['id'] 		= array();
	$allowed['input']['class'] 		= array();
	$allowed['input']['placeholder'] = array();
	$allowed['input']['style'] 		= array();
	$allowed['input']['step']		= array();
	$allowed['input']['min'] 		= array();
	$allowed['input']['max'] 		= array();
	$allowed['input']['size'] 		= array();
	$allowed['input']['inputmode'] 	= array();
	$allowed['input']['onpaste'] 	= array();
	$allowed['input']['onclick'] 	= array();
	$allowed['input']['onkeypress'] = array();
	$allowed['input']['checked'] 	= array();
	$allowed['input']['data-productid'] = array();
	$allowed['input']['data-value'] = array();
	$allowed['span']['onclick'] 	= array();
	$allowed['button']['onclick'] 	= array();
	$allowed['div']['onclick'] 	= array();
	$allowed['a']['onclick'] 		= array();
	return $allowed;
}

	
