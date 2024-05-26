<?php
/**
 * Plugin Name: Bulk Add To Cart By Aheadworks
 * Plugin URI: https://www.aheadworks.com/
 * Description: Bulk Add To Cart plugin for Woocommerce allows admin to create the lists of products with predefined options and quantities, which can be published either on the store or blog pages and which can be added to a cart in a few clicks. Unique values of the plugin are: Add the list to the blog post, User can reconfigure products in the list, Product list grid to manage products, Embed the list into a blog post or static page or anywhere else by using shortcodes.
 * Author: Aheadworks
 * Author URI: https://www.aheadworks.com/
 * Version: 1.0.0
 * Woo: 6689732:e687fffc44a91c748703507233f22d1b
 * Text Domain: bulk-add-to-cart-by-aheadworks 
 *
 * @package bulk-add-to-cart-by-aheadworks
 *
 * Requires at least: 5.2.7
 * Tested up to: 5.5.1
 * WC requires at least: 3.8.0
 * WC tested up to: 4.5.2
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

require_once(plugin_dir_path(__FILE__) . 'includes/aw-batc-product-list-admin.php'); 
require_once(plugin_dir_path(__FILE__) . 'includes/aw-batc-add-new-product-admin.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-batc-product-list-public.php');


$bulkaddtocart = new BulkAddToCart();

/** Present plugin version **/
define( 'AW_BULK_ADD_TO_CART_VERSION', '1.0.0' );

class BulkAddToCart {

	public $GLOBALS;
	public function __construct() {
		global $hook_suffix;
		$nowglobal = $GLOBALS['hook_suffix'];
		$nowglobal = null;

		/* Constructor function, initialize and register hooks */
		add_action('admin_init', array(get_called_class(),'aw_bulk_addto_cart_installer'));
		register_uninstall_hook(__FILE__, array(get_called_class(),'aw_bulk_addto_cart_unistaller'));

		/* Admin Javascript files */
		add_action('admin_enqueue_scripts', array(get_called_class(),'aw_bulk_addto_cart_admin_addScript'));
		add_action('wp_enqueue_scripts', array(get_called_class(),'aw_bulk_addto_cart_public_addScript'));

		

		add_action('init', array('AwbatcProductList' , 'bulk_addto_cart_activate' ));
		/* Add Custom menus admin side */
		if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
			add_action('admin_menu', array('AwbatcProductList','aw_bulk_addto_cart_adminmenu'));
		}

		/*
			Add the custom columns to the aw_bulk_product_list post type. 
			Add the data to the custom columns.
			Save Product List.
		*/

		add_filter('manage_edit-aw_bulk_product_list_columns', array('AwbatcProductList', 'aw_add_new_columns_product_grid'));
		add_action('manage_aw_bulk_product_list_posts_custom_column' , array('AwbatcProductList', 'aw_add_data_to_custom_product_grid_column' ), 10, 2);
		add_action('admin_post_save_product_list_form', array('AwbatcProductList','aw_batc_save_product_list_form'));

		/*
			Change row action edit url on custom post list in data table.
			Duplicate Post action 
		*/

		add_action( 'post_row_actions', array('AwbatcProductList', 'aw_modify_list_row_actions'), 10, 2);
		add_filter( 'get_edit_post_link', array('AwbatcProductList', 'aw_modify_list_row_title'), 10, 3);

		add_action( 'admin_action_aw_batc_duplicate_row_as_draft', array('AwbatcProductList','aw_batc_duplicate_row_as_draft'));

		/* Ajax call to add product on main list from popup grid. */

		add_action('wp_ajax_aw_get_product_from_woo_popup_grid', array('AwbatcProductList','aw_get_product_from_woo_popup_grid'));
		add_action('wp_ajax_nopriv_aw_get_product_from_woo_popup_grid', array('AwbatcProductList','aw_get_product_from_woo_popup_grid'));

		/*
			   To display popup use admin footer hooks
			   To fetch Woocommerce product list use plugin	
		*/

		add_action('admin_footer' , array('AwbatcAddNewProduct','aw_add_new_product_popup'));
		add_action('wp_ajax_aw_fetch_woo_product_list' , array('AwbatcAddNewProduct','aw_fetch_woo_product_list'));
		add_action('wp_ajax_nopriv_aw_fetch_woo_product_list', array('AwbatcAddNewProduct','aw_fetch_woo_product_list'));

		/* On permanent delete custom post aw_bulk_product_list; delete all attached products id's */
		add_action('before_delete_post', array('AwbatcProductList','aw_batc_delete_product_after_post'));	

		/*
			Ajax call to append variation popup html.
			Ajax to fetch variation drop down value on popup.
			Save selected variation form value.
		*/

		add_action('wp_ajax_aw_append_variation_to_popup', array('AwbatcProductList','aw_append_variation_to_popup'));
		add_action('wp_ajax_aw_ajax_filterVariations', array('AwbatcProductList','aw_ajax_filterVariations'));
		add_action('wp_ajax_aw_save_variation_setting_form_data', array('AwbatcProductList','aw_save_variation_setting_form_data'));

		add_shortcode('batc' , array('AwbatcProductShow','aw_batc_shortcode_display_product_list'));

		/* To make sortable column in wordpress grid */

		add_filter('manage_edit-aw_bulk_product_list_sortable_columns', array('AwbatcProductList','aw_batc_list_column_register_sortable'));
		add_action('pre_get_posts', array('AwbatcProductList','aw_batc_list_column_made_sortable'), 1);
		add_filter('bulk_actions-edit-aw_bulk_product_list', array('AwbatcProductList','aw_bulk_product_list_register_my_bulk_actions'));
		add_filter('handle_bulk_actions-edit-aw_bulk_product_list', array('AwbatcProductList','aw_bulk_product_list_bulk_action_handler'), 10, 3);

		add_filter('views_edit-aw_bulk_product_list' , array('AwbatcProductList','aw_bulk_product_list_modified_views_post_status'));

		add_action( 'wp_loaded', array('AwbatcProductShow','aw_add_multiple_products_to_cart'));

		add_action('wp_ajax_nopriv_aw_ajax_filterVariations_front', array('AwbatcProductShow','aw_ajax_filterVariations_front'));
		add_action('wp_ajax_aw_ajax_filterVariations_front', array('AwbatcProductShow','aw_ajax_filterVariations_front'));

		add_action('wp_ajax_nopriv_aw_get_price_quantity_calculate', 'aw_get_price_quantity_calculate');
		add_action('wp_ajax_aw_get_price_quantity_calculate', 'aw_get_price_quantity_calculate');

		add_action('save_post', array('AwbatcProductList', 'aw_bulk_addto_cart_changetitle'));
		add_action('quick_edit_custom_box', array('AwbatcProductList', 'aw_batc_quick_edit_add'), 10, 2);

		/* Display notices after successfully saved */		
		add_action( 'admin_notices', 'aw_batc_display_flash_notices', 12 );


		add_filter( 'wp_ajax_nopriv_aw_batc_mode_theme_update_mini_cart', 'aw_batc_mode_theme_update_mini_cart' );
		add_filter( 'wp_ajax_aw_batc_mode_theme_update_mini_cart', 'aw_batc_mode_theme_update_mini_cart' );

		add_filter('wp_kses_allowed_html', 'aw_batc_kses_filter_allowed_html', 10, 2);
	}

	public static function aw_bulk_addto_cart_installer() {

		if ('completed' === get_option( 'bulk-addto-cart-aheadworks')) {
				return ;
		}
		update_option( 'bulk-addto-cart-aheadworks', 'completed' );

		if (is_admin()) {
			if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
				/** If WooCommerce plugin is not activated show notice **/
				add_action('admin_notices', array('AwbatcProductList','self_deactivate_notice'));
				/** Deactivate our plugin **/
				deactivate_plugins(plugin_basename(__FILE__));
				if (isset($_GET['activate'])) {
					unset($_GET['activate']);
				}
			} else {

				flush_rewrite_rules();

				wp_deregister_script( 'autosave' );

				global $wpdb;

				$db_product_list = $wpdb->prefix . 'product_list';  

				$charset_collate = $wpdb->get_charset_collate();

				//Check to see if the table exists already, if not, then create it
				if ($wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ' , "{$wpdb->prefix}product_list")) != $db_product_list) {
					$sql = "CREATE TABLE {$wpdb->prefix}product_list (
							  `id` bigint(20)  NOT NULL auto_increment,
							  `post_id` int(11) NOT NULL,
							  `product_ids` text NOT NULL,
							  `tab_id` bigint(50) NOT NULL,
							  `tab_title` varchar(55) NOT NULL,
							  `quantity` text NOT NULL,
							  `price` text NOT NULL,
							  `variation` text NOT NULL,
							  `quantity_change` varchar(55) NOT NULL,
							  `added_date` datetime NOT NULL DEFAULT current_timestamp(),
							  `updated_date` datetime NOT NULL,
							  PRIMARY KEY (`id`)
							);" ;

					require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
					dbDelta($sql);
				}
			}
		}
	}

	public static function aw_bulk_addto_cart_unistaller() {
		/*Perform required operations at time of plugin uninstallation*/
		global $wpdb;
		delete_option('bulk-addto-cart-aheadworks');
		//$db_product_list_table 	= $wpdb->prefix . 'product_list';
		
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}product_list");

		$args = array('post_type' => array('aw_bulk_product_list'), 'posts_per_page'=> -1);
		$loop = new WP_Query($args);

		if (!$loop->have_posts()) {
			return;
		}

		while ($loop->have_posts()) :
			$loop->the_post();
			wp_delete_post(get_the_ID(), true);
		endwhile;
		
		wp_reset_query();
	}

	public static function aw_bulk_addto_cart_admin_addScript() {
		$path 		= parse_url(get_option('siteurl'), PHP_URL_PATH);
		$host 	 	= parse_url(get_option('siteurl'), PHP_URL_HOST);
		$batc_nonce	= wp_create_nonce('aw_batc_admin_nonce');

		$page 	= '';
		$post 	= '';
		if (isset($_GET['page']) ) {
			$page = sanitize_text_field($_GET['page']);
		}
		if (isset($_GET['post_type'])) {
			$post = sanitize_text_field($_GET['post_type']);
		}
		if ('aw-batc-product-list-admin' === $page || 'aw_bulk_product_list' === $post) {
			wp_register_style('bulkaddtocartadmincss', plugins_url('/admin/css/aw-bulk-addto-cart-admin.css', __FILE__ ), array(), '1.0' );
			wp_enqueue_style('bulkaddtocartadmincss');

			wp_enqueue_script('jquery-ui-sortable');
			
			wp_register_script('bulkaddtocartadminjs', plugins_url('/admin/js/aw-bulk-addto-cart-admin.js', __FILE__ ), array(), '1.0' );
			$js_batc_var = array('site_url' => get_option('siteurl'), 'path' => $path, 'host' => $host, 'aw_batc_nonce' => $batc_nonce);
			wp_localize_script('bulkaddtocartadminjs', 'js_batc_var', $js_batc_var);
			wp_register_script('bulkaddtocartadminjs', plugins_url('/admin/js/aw-bulk-addto-cart-admin.js', __FILE__ ), array(), '1.0' );
			wp_enqueue_script('bulkaddtocartadminjs');
		}
	}

	public static function aw_bulk_addto_cart_public_addScript() {
		$path = parse_url(get_option('siteurl'), PHP_URL_PATH);
		$host = parse_url(get_option('siteurl'), PHP_URL_HOST);
		$nonce = wp_create_nonce('awbatcproductsave_nonce');
		/** Add Plugin CSS and JS files Public Side**/

		wp_register_style('bulkaddtocartpubliccss', plugins_url('/public/css/aw-bulk-addto-cart-public.css', __FILE__ ), array(), '1.0' );
		wp_enqueue_style('bulkaddtocartpubliccss');
		wp_register_script('bulkaddtocartpublicjs', plugins_url('/public/js/aw-bulk-addto-cart-public.js', __FILE__ ), array('jquery'), '1.0' );

		$js_var = array('site_url' => get_option('siteurl'), 'ajax_url'=>admin_url('admin-ajax.php'), 'path' => $path, 'host' => $host, 'js_batc_nonce' => $nonce);
		wp_localize_script('bulkaddtocartpublicjs', 'js_batc_var', $js_var);

		wp_register_script('bulkaddtocartpublicjs', plugins_url('/public/js/aw-bulk-addto-cart-public.js', __FILE__ ), array('jquery'), '1.0' );
		wp_enqueue_script('bulkaddtocartpublicjs');
	}
}

function aw_get_product_image( $product_id) {
	$url = '';
	if (has_post_thumbnail( $product_id ) ) {
		$url = get_the_post_thumbnail_url($product_id, array(20,20));
	} else {
		$url = site_url() . '/wp-content/uploads/woocommerce-placeholder-150x150.png';
	}
	return $url;
}

function aw_get_alltab_product_list( $post_id) {
	global $wpdb;
	$result = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}product_list WHERE post_id = %d", "{$post_id}"));
	return $result;
}

function explodeX( $delimiters, $string ) {
	return explode( chr( 1 ), str_replace( $delimiters, chr( 1 ), $string ) );
}

function aw_get_post_id_by_post_title( $post_name, $product_id) {
	global $wpdb;
	$result = $wpdb->get_var( 
		$wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = %s AND post_status = %s AND post_parent = %d AND post_name LIKE %s", 'product_variation', 'publish', $product_id, $post_name) 
	);
	return $result;
}
function aw_get_post_id_by_post_excerpt( $post_excerpt, $product_id) {
	global $wpdb;
	$result = $wpdb->get_var( 
		$wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = %s AND post_status = %s AND post_parent = %d AND post_excerpt LIKE %s", 'product_variation', 'publish', $product_id, $post_excerpt) 
	);
	return $result;
}

/* Ajax call to get variation drop down value */
function aw_append_default_dropdown_attribute( $product_id = '', $key = '', $value = '', $selected, $screen) {
	if ('admin' == $screen) {
		check_ajax_referer( 'aw_batc_admin_nonce', 'aw_qa_nonce_ajax' );
	}
	global $wpdb;
	$option = '';

	if (isset($_POST['key']) && isset($_POST['value']) && isset($_POST['product_id'])) {
		$product_id 	= sanitize_text_field($_POST['product_id']);
		$key 			= sanitize_text_field($_POST['key']);
		$value 			= sanitize_text_field($_POST['value']);
	}  

	$key 			= 'attribute_' . $key;
	$product_obj 	= new WC_Product_Factory();
	$product 		= $product_obj->get_product($product_id);
	$attributes 	= $product->get_variation_attributes();
	$attribute_keys = array_keys($attributes);
	$attribute_keys = array_map('strtolower', $attribute_keys);
	$attribute_keys = array_map(function( $val) {
		return 'attribute_' . $val;
	} , $attribute_keys);
	wp_reset_query();
	$query = new WP_Query( array(
		'post_parent' => $product_id,
		'post_status' => 'publish',
		'post_type' => 'product_variation',
		'posts_per_page' => -1,
		'meta_query' => array(
			array(
				'key'   => $key, 
				'value' => $value 
			),
		),
	) );
	$result = array();
	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->next_post();
			$object = $query->post;
			foreach ($attribute_keys as $attribute_name) {
				if ($attribute_name != $key) {
					$indexkey = str_replace('attribute_', '', $attribute_name);
					$value = get_post_meta($object->ID, $attribute_name, true);
					if ('' != $value) {
						$active = '';
						if ($value == $selected) {
							$active = 'selected';
						}
						$option .= '<option ' . $active . ' value="' . strtolower($value) . '">' . ucfirst($value) . '</option>';
					}  
				}
			}
		}
		wp_reset_postdata();
	} 
	wp_reset_query();
	return $option;
	die;
}

function aw_get_individual_product_price( $product_id ) {
	$object = wc_get_product( $product_id );
	$price = 0;

	if (empty($object)) {
		return 0;
	}
	if ($object->is_type( 'variable' )) {
		$product 	= new WC_Product_Variable( $product_id );
		$variations = $product->get_available_variations();
		if (!empty($variations)) {
			$variation_id 	= $variations[0]['variation_id'];
			//$url 			= $variations[0]['image']['gallery_thumbnail_src'];
			$price 			= $variations[0]['display_price'];
			if (empty($price)) {
				$price = $variations[0]['display_regular_price'];
			}
		}
	} else {
		$price = $object->get_price();
		if (empty($price)) {
			$price = $object->get_regular_price();
		}
	}
	return $price;
}

function aw_get_price_quantity_calculate() {

	$rowtotal='';
	if (isset($_POST['screen']) && 'admin' == $_POST['screen']) {
		check_ajax_referer( 'aw_batc_admin_nonce', 'aw_qa_nonce_ajax' );
	}

	$decimalposition = get_option('woocommerce_price_num_decimals');
	if (isset($_POST['row_total'])) {

		$rowtotal = sanitize_text_field($_POST['row_total']);
		if (is_numeric($rowtotal)) {
			$rowtotal = esc_html(sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), number_format($rowtotal, $decimalposition) ));	
		} else {
			$rowtotal = esc_html(sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), number_format(0, $decimalposition) ));	
		} 
		
	}
	echo wp_kses(html_entity_decode($rowtotal), wp_kses_allowed_html('post'));
	die;
}

function aw_batc_display_flash_notices() {
	$notices = get_option( 'aw_batc_flash_notices', array() );

	// Iterate through our notices to be displayed and print them.
	foreach ( $notices as $notice ) {
		printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
			esc_html($notice['type']),
			esc_html($notice['dismissible']),
			esc_html($notice['notice'])
		);
	}
 
	// Now we reset our options to prevent notices being displayed forever.
	if ( ! empty( $notices ) ) {
		delete_option( 'aw_batc_flash_notices', array() );
	}
}

function aw_batc_mode_theme_update_mini_cart( $args = array() ) {
	$defaults = array(
	  'list_class' => '',
	);

	$args = wp_parse_args( $args, $defaults );

	echo wp_kses(wc_get_template( 'cart/mini-cart.php', $args ) . '~' . WC()->cart->get_cart_total() . '~' . WC()->cart->cart_contents_count, wp_kses_allowed_html('post'));
	die();
}

function aw_batc_kses_filter_allowed_html( $allowed, $context) {
	$allowed['style'] 				= array();
	$allowed['select'] 				= array();
	$allowed['select']['name'] 		= array();
	$allowed['select']['id'] 		= array();
	$allowed['select']['class'] 	= array();
	$allowed['select']['onchange'] 	= array();
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
	$allowed['input']['onkeypress'] = array();
	$allowed['input']['checked'] 	= array();
	$allowed['input']['data-productid'] = array();
	$allowed['input']['data-value'] = array();
	$allowed['span']['onclick'] 	= array();
	$allowed['button']['onclick'] 	= array();
	$allowed['a']['onclick'] 		= array();
	/*$allowed['a']['javascript'] 	= array();
	$allowed['a']['data-variation_id'] 	= array();
	$allowed['a']['data-row_id'] = array();
	$allowed['a']['data-product_id'] 	= array();
	*/
	return $allowed;
}


function find_matching_product_variation_id( $attributes, $product_id) {
	return ( new \WC_Product_Data_Store_CPT() )->find_matching_product_variation(
		new \WC_Product($product_id),
		$attributes
	);
}
