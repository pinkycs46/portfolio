<?php
/**
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * Plugin Name: Popup Pro By Aheadworks
 * Plugin URI: https://www.aheadworks.com/
 * Description: Popup pro By Aheadworks for WooCommerce. Show promotions with popups.
 * Author: Aheadworks
 * Author URI: https://www.aheadworks.com/
 * Version: 1.0.1
 * Woo: 5581976:ad0bfd8e049ae16222d60615abecf175
 * Text Domain: popup-pro-by-aheadworks
 *
 * @package popup-pro-by-aheadworks
 *
 * Requires at least: 5.2.7
 * Tested up to: 5.5.1
 * WC requires at least: 3.8.0
 * WC tested up to: 4.5.2
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
require_once(plugin_dir_path(__FILE__) . 'includes/aw-popup-pro-admin.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-popup-pro-public.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-subscribe-popup-pro-public.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-subscribe-popup-pro-fonts.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-subscribe-popup-pro-templates.php');

$popuppro = new AwPopupPro();

/** Present plugin version **/
define( 'POPUP_PRO_VERSION', '1.0.0' );

class AwPopupPro {
	public function __construct() {
		/** Constructor function, initialize and register hooks **/
		add_action('admin_init', array(get_called_class(),'aw_popup_pro_installer'));
		add_action('init', array(get_called_class(),'aw_popup_pro_activate'));

		register_uninstall_hook(__FILE__, array(get_called_class(),'aw_popup_pro_unistaller'));

		add_action('add_meta_boxes', array(get_called_class(), 'aw_popup_pro_meta_box'));
		add_action('admin_enqueue_scripts', array(get_called_class(),'aw_popup_pro_admin_addScript'));
		add_action('wp_enqueue_scripts', array(get_called_class(),'aw_popup_pro_public_addScript'));
		add_action('save_post', array(get_called_class(), 'aw_popup_pro_save_data'));
		add_action('manage_popup-pro_posts_custom_column', array(get_called_class(),'aw_popup_pro_table_columns'), 10, 2); // Display custom value in column
		add_action('manage_popup-pro-subscribe_posts_custom_column', array(get_called_class(),'aw_popup_pro_subscribe_table_columns'), 10, 2); // Display custom value in column

		add_action('woocommerce_add_to_cart', array('AwPopupProPublic','aw_popup_pro_add_to_cart'), 0);

		if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
			add_action('wp', array('AwPopupProPublic','aw_popup_pro_cart_checkout'));
		}

		if (!aw_popup_pro_is_subscribed() && !is_admin() && empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
			$post_id = 0;
			$type = 0;
			$x_equalto = 0;
			$cookie_lifetime = 0;

			add_action('wp', function() use ( $post_id, $type, $x_equalto, $cookie_lifetime) {
				aw_popup_pro_subscribers_cookies($post_id, $type, $x_equalto, $cookie_lifetime);
			});
		}

		add_action('wp', array('AwSubscribePopupProPublic','aw_subscribe_popup_pro'), 20);
		add_action('wp_head', array('AwPopupProPublic','aw_popup_div_for_append'));

		add_action('wp_ajax_aw_popup_pro_product_add_to_cart_ajx', array('AwPopupProPublic','aw_popup_pro_product_add_to_cart_ajx'));
		add_action('wp_ajax_nopriv_aw_popup_pro_product_add_to_cart_ajx', array('AwPopupProPublic','aw_popup_pro_product_add_to_cart_ajx'));

		add_action('wp_ajax_aw_popup_pro_add_subscriber', array('AwSubscribePopupProPublic','aw_popup_pro_add_subscriber'));
		add_action('wp_ajax_nopriv_aw_popup_pro_add_subscriber', array('AwSubscribePopupProPublic','aw_popup_pro_add_subscriber'));

		add_action('wp_ajax_aw_popup_pro_product_add_to_cart', 'aw_popup_pro_product_add_to_cart');
		add_action('wp_ajax_nopriv_aw_popup_pro_product_add_to_cart', 'aw_popup_pro_product_add_to_cart');

		add_filter('bulk_actions-edit-popup-pro', array(get_called_class(),'aw_popup_pro_register_my_bulk_actions'));
		add_filter('bulk_actions-edit-popup-pro-subscribe', array(get_called_class(),'aw_popup_pro_subscribe_register_my_bulk_actions'));
		add_filter('handle_bulk_actions-edit-popup-pro', array(get_called_class(),'aw_popup_pro_my_bulk_action_handler'), 10, 3);
		add_filter('handle_bulk_actions-edit-popup-pro-subscribe', array(get_called_class(),'aw_popup_pro_subscribe_my_bulk_action_handler'), 10, 3);		
		add_filter('manage_edit-popup-pro_sortable_columns', array(get_called_class(),'aw_popup_pro_column_register_sortable'));
		add_action('pre_get_posts', array(get_called_class(),'aw_columndata_sorting_orderby'));

		add_filter('manage_edit-popup-pro-subscribe_sortable_columns', array(get_called_class(),'aw_popup_pro_subscribe_column_register_sortable'));
		add_filter('manage_popup-pro_posts_columns', array(get_called_class(),'aw_popup_pro_table_head')); // create Head of custom column
		add_filter('manage_popup-pro-subscribe_posts_columns', array(get_called_class(),'aw_popup_pro_subscribe_table_head')); // create Head of custom column
		add_action('admin_menu', array(get_called_class(),'aw_popup_pro_admin_menu'));
		add_action('save_post', array(get_called_class(), 'aw_popup_pro_subscribe_save_data'));

		add_action('post_edit_form_tag', array(get_called_class(), 'aw_update_edit_form'));
		add_action('wp_ajax_aw_subscribefont_ajax_request', 'aw_subscribefont_ajax_request');
		add_action('wp_ajax_nopriv_aw_subscribefont_ajax_request', 'aw_subscribefont_ajax_request');

		add_action('admin_action_popup_pro_preview_modal_box', array(get_called_class(), 'aw_subscribe_popup_pro_preview_action_page'));
		add_action('admin_action_subscribe_preview_modal_box', array(get_called_class(), 'aw_subscribe_popup_preview_action_page'));
		add_filter('post_updated_messages', array(get_called_class(),'aw_pop_pro_allnotice_messages'));
		add_action('admin_notices', array(get_called_class(),'aw_popup_pro_trashnotice'));

		foreach (array('popup-pro', 'popup-pro-subscribe') as $hook) {
			add_filter('views_edit-' . $hook , array(get_called_class(),'aw_modified_views_post_status'));
		}
		add_action('admin_action_export_subscribers', array('AwPopupProAdmin','aw_export_subscribers_action_page'));

		add_action('wp_ajax_aw_popup_background_image_delete', 'aw_popup_background_image_delete');
		add_action('wp_ajax_nopriv_aw_popup_background_image_delete', 'aw_popup_background_image_delete');

		add_filter('wp_kses_allowed_html', 'aw_pop_kses_filter_allowed_html', 10, 2);
		add_filter( 'safe_style_css', function( $styles ) {
			$styles[] = 'display';
			$styles[] = 'background-repeat';
			$styles[] = 'fill';
			$styles[] = 'border-radius';
			$styles[] = 'enable-background';
			return $styles;
		} );
	}

	public static function aw_popup_pro_installer() {
		/** Check WooCommerce plugin activated ? **/
		if (is_admin()) {
			if (!is_plugin_active( 'woocommerce/woocommerce.php')) {
				/** If WooCommerce plugin is not activated show notice **/
				add_action('admin_notices', array('AwPopupProAdmin','aw_self_deactivate_notice'));

				/** Deactivate our plugin **/
				deactivate_plugins(plugin_basename(__FILE__));

				if (isset($_GET['activate'])) {
					unset($_GET['activate']);
				}
			} else {
				wp_deregister_script( 'autosave' );

				global $wpdb;
				$db_table_name = $wpdb->prefix . 'popup_pro_subscribes';  
				$charset_collate = $wpdb->get_charset_collate();

				//Check to see if the table exists already, if not, then create it
				if ($wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ' , "{$wpdb->prefix}popup_pro_subscribes")) != $db_table_name) {
					$sql = "CREATE TABLE {$wpdb->prefix}popup_pro_subscribes (
							`id` int(11) NOT NULL auto_increment,
							`name` text NOT NULL,
							`email` text NOT NULL,
							`date` datetime NOT NULL,
							`post_id` int(11) NOT NULL,
							`status` int(2) NOT NULL DEFAULT '1',
							PRIMARY KEY(`id`)
							);";
					require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
					dbDelta($sql);
				}
			}
		}
	}

	public static function aw_popup_pro_unistaller() {
		/*Perform required operations at time of plugin uninstallation*/
		global $wpdb;
		$db_table_name = $wpdb->prefix . 'popup_pro_subscribes';

		if (is_multisite()) {
			$blogs_ids = get_sites(); 
			foreach ( $blogs_ids as $b ) {
				$wpdb->prefix  = $wpdb->get_blog_prefix($b->blog_id);
				$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}popup_pro_subscribes");
			}
		} else {
			$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}popup_pro_subscribes");
		}

		$args = array('post_type' => array('popup-pro','popup-pro-subscribe'), 'posts_per_page'=> -1);
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

	public static function aw_popup_pro_admin_addScript() {
		/** Add Plugin CSS and JS files Admin Side**/
		if (isset($_GET['post_type'])) {
			$post_type = sanitize_text_field($_GET['post_type']);
		} else {
			$post_type = get_post_type();
		}
		if ('popup-pro' == $post_type || 'popup-pro-subscribe' == $post_type) {
			wp_register_style('popupproadmincss', plugins_url('/admin/css/aw-popup-pro-admin.css', __FILE__ ), array(), '1.0' );
			wp_enqueue_style('popupproadmincss');

			$get_fonts = new AwSubscribePopupProFonts();
			$fonts = $get_fonts->fonts;

			$google_fonts = array();
			$font_family = '';
			$font_family_url = '';

			foreach ($fonts as $font) {
				$google_fonts[$font] = $get_fonts->aw_get_font_weight($font);
				$aw_get_font_weight = array_flip($get_fonts->aw_get_font_weight($font));
				
				$font_weight = '';
				foreach ($aw_get_font_weight as $aw_get_font_weights) {
					$font_weight.= $aw_get_font_weights . ',';
				}
				$font_weight = rtrim($font_weight, ',') . '<br/>';
				$font_family.= $font . ':' . $font_weight . '|';
				$font_family_url = 'https://fonts.googleapis.com/css?family=' . $font_family . '&display=swap';
			}

			wp_register_style('popupprogoogleadmincss', esc_url($font_family_url), array(), '1.0' );
			wp_enqueue_style('popupprogoogleadmincss');

			wp_register_script('popupproadminjs', plugins_url('/admin/js/aw-popup-pro-admin.js', __FILE__ ), array(), '1.0'  );

			$js_var = array('site_url' => get_option('siteurl'));
			wp_localize_script('popupproadminjs', 'js_var', $js_var);

			wp_register_script('popupproadminjs', plugins_url('/admin/js/aw-popup-pro-admin.js', __FILE__ ), array(), '1.0' );
			wp_enqueue_script('popupproadminjs');

			wp_register_script('popupproadminjs1', plugins_url('/admin/js/aw-jscolor.js' , __FILE__ ), array(), '1.0' );
			wp_enqueue_script('popupproadminjs1');
		}
	}

	public static function aw_popup_pro_public_addScript() {
		/** Add Plugin CSS and JS files Public Side**/
		$path = parse_url(get_option('siteurl'), PHP_URL_PATH);
		$host = parse_url(get_option('siteurl'), PHP_URL_HOST);

		wp_register_style('popupproapubliccss', plugins_url('/public/css/aw-popup-pro-public.css', __FILE__ ), array(), '1.0' );
		wp_enqueue_style('popupproapubliccss');

		wp_register_script('popuppropublicjs', plugins_url('/public/js/aw-popup-pro-public.js', __FILE__ ), array('jquery'), '1.0', true);

		$js_var = array('site_url' => get_option('siteurl'), 'path' => $path, 'host' => $host);
		wp_localize_script('popuppropublicjs', 'js_var', $js_var);

		wp_register_script('popuppropublicjs', plugins_url('/public/js/aw-popup-pro-public.js', __FILE__ ), array('jquery'), '1.0' );
		wp_enqueue_script('popuppropublicjs');
	}

	public static function aw_popup_pro_activate() {
		/** Create and Register Post Type popup-pro **/
		if (class_exists( 'woocommerce' ) ) {
			$labels = array(
				//'name'               => __('Linked products popups', 'post type general name', 'your-plugin-textdomain'),
				'name'               => __( 'Linked products popups', 'your-plugin-textdomain' ),
				'singular_name'      => __('Linked products popups', 'your-plugin-textdomain'),
				'add_new'            => __('Create New Popup', 'popup_pro_plugin'),
				'add_new_item'       => __('Add New Popup', 'popup_pro_plugin'),
				'edit_item'          => __('Edit Popup', 'popup_pro_plugin'),
				'new_item'           => __('New Popup', 'popup_pro_plugin'),
				'all_items'          => __('Linked products popups', 'popup_pro_plugin'),
				//'view_item'          => __('', 'popup_pro_plugin'), 
				'search_items'       => __('Search Popup', 'popup_pro_plugin'),
				'not_found'          => __('No Popup Found', 'popup_pro_plugin'),
				'not_found_in_trash' => __('No Popup Found in Trash', 'popup_pro_plugin'),
				'parent_item_colon'  => '',
				'menu_name'          => 'Popup Pro'
				);

			$args = array(
				'labels'        	=> $labels,
				'description'   	=> __( 'Description.', 'popup_pro_plugin' ),
				'capability_type' 	=> 'post',
				'public'        	=> true,
				'menu_position' 	=> 25,
				'supports'      	=> array('title'),
				'has_archive'   	=> false,
				'hierarchical'		=> false,
				'rewrite'			=> array('slug' => 'popup-pro/%popup_category%','with_front' => false),
				'publicly_queryable'=> false
				);

			$post_type_exists = post_type_exists('popup-pro');

			if (!$post_type_exists) {
				register_post_type( 'popup-pro', $args );
			}

			/** Create and Register Post Type popup-pro-subscribe **/
			$labels = array(
				'name'               => __('Subscribe popups', 'post type general name'),
				'singular_name'      => __('Subscribe popups', 'our-plugin-textdomain'),
				'add_new_item'       => __('Add New Popup'),
				'edit_item'          => __('Edit Subscribe'),
				'new_item'           => __('New Subscribe'),
				'all_items'          => __('All Subscribe'),
				//'view_item'          => __(''), 
				'search_items'       => __('Search Popup'),
				'not_found'          => __('No Subscribe Found'),
				'not_found_in_trash' => __('No Subscribe Found in Trash'),
				'parent_item_colon'  => '',
				'menu_name'          => 'Subscribe'
				);
			$args = array(
				'labels'        	=> $labels,
				'description'   	=> 'Subscribe For WooCommerce Post',
				'capability_type' 	=> 'post',
				'public'        	=> true,
				'supports'      	=> array('title'),
				'has_archive'   	=> false,
				'hierarchical'		=> false,
				'publicly_queryable' => false,
				);

			$post_type_exists = post_type_exists('popup-pro-subscribe');

			if (!$post_type_exists) {
				register_post_type( 'popup-pro-subscribe', $args );
			}
		}
	}

	public static function aw_popup_pro_meta_box() {
		/** Add Meta Boxes / Fields for plugin data on Admin Side Popup-Pro**/
		$multi_posts = array('popup-pro');
		add_meta_box('Linked products popups', __('Linked products popups', 'popup-pro'),
			array('AwPopupProAdmin', 'aw_popup_pro_meta_box_callback'),
			$multi_posts,
			'normal',
			'high'
		);

		/** Add Meta Boxes / Fields for plugin data on Admin Side popup-pro-subscribe**/
		$multi_posts = array('popup-pro-subscribe');
		add_meta_box('Popup_Pro_sub', __('popup-pro-subscribe', 'popup-pro-subscribe'),
			array('AwPopupProAdmin', 'aw_popup_pro_subscribe_meta_box_callback'),
			$multi_posts,
			'normal',
			'high'
		);
	}

	public static function aw_popup_pro_save_data( $post_id) {
		/** Function to save or update popup-pro data **/
		if (isset($_POST['post_type']) && 'popup-pro' == $_POST['post_type']) {
			global $post;

			// if we are doing an autosave then return
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
				return;
			}

			// if the nonce is not present there or we can not verify it.
			if (!isset($_POST['popup_pro_nonce_name']) || !wp_verify_nonce(sanitize_key($_POST['popup_pro_nonce_name']), 'popup_pro_nonce_action')) {
				return;
			}

			$input_title = '';
			if (isset($_POST['popup_pro_title'])) {
				$input_title = sanitize_text_field($_POST['popup_pro_title']);
			}
			update_post_meta($post_id, 'popup_pro_title', $input_title) ;

			if (isset($_POST['popup_pro_type_display']) && ( '' != $_POST['popup_pro_type_display'] )) {
				update_post_meta($post_id, 'popup_pro_type_display', sanitize_text_field($_POST['popup_pro_type_display']));
			}

			if (isset($_POST['popup_pro_maximum_product'] ) && ( '' != $_POST['popup_pro_maximum_product'] ) ) {
				update_post_meta($post_id, 'popup_pro_maximum_product', sanitize_text_field($_POST['popup_pro_maximum_product']));
			}

			if (isset($_POST['popup_pro_cookie_lifetime']) && ( '' != $_POST['popup_pro_cookie_lifetime'] )) {
				update_post_meta($post_id, 'popup_pro_cookie_lifetime', sanitize_text_field($_POST['popup_pro_cookie_lifetime']));
			}

			if (isset($_POST['popup_pro_priority']) && ( '' != $_POST['popup_pro_priority'] )) {
				update_post_meta($post_id, 'popup_pro_priority', sanitize_text_field($_POST['popup_pro_priority']));
			}
		}
	}

	public static function aw_popup_pro_subscribe_save_data( $post_id) {
		if (isset($_POST['post_type']) && 'popup-pro-subscribe' == $_POST['post_type']) {
			/** Function to save or update popup-pro-subscribe data **/
			global $post; 
			// if we are doing an autosave then return
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
				return;
			}
			// if the nonce is not present there or we can not verify it.
			if (!isset($_POST['popup_pro_nonce_name']) || !wp_verify_nonce(sanitize_key($_POST['popup_pro_nonce_name']), 'popup_pro_nonce_action')) {
				return;
			}
			$title_input = '';
			if (isset($_POST['popup_pro_subscribe_title'])) {
				$title_input = sanitize_text_field($_POST['popup_pro_subscribe_title']);
			}
			update_post_meta($post_id, 'popup_pro_subscribe_title', $title_input);

			$subtitle_input = '';
			if (isset($_POST['popup_pro_subscribe_subtitle'])) {
				$subtitle_input = sanitize_text_field($_POST['popup_pro_subscribe_subtitle']);
			}
			update_post_meta($post_id, 'popup_pro_subscribe_subtitle', $subtitle_input);

			if (isset($_POST['popup_pro_subscribe_button']) && ( '' != $_POST['popup_pro_subscribe_button'] )) {
				update_post_meta($post_id, 'popup_pro_subscribe_button', sanitize_text_field($_POST['popup_pro_subscribe_button']));
			}

			if (isset($_POST['design']) && ( '' != $_POST['design'] )) {
				if (!empty($_FILES['backgroundimage']['name'])) {
					$supported_types = array('image/png','image/jpeg','image/jpg','image/bmp','image/gif');
					
					$image_full_name =sanitize_text_field($_FILES['backgroundimage']['name']);
					$arr_file_type = wp_check_filetype(basename($image_full_name));
					$uploaded_type = $arr_file_type['type'];

					// Check if image file type is supported. If not, throw an error.
					if (in_array($uploaded_type, $supported_types)) {
						// Use the WordPress API to upload the file
						$uploadspath = wp_upload_dir();
						if (!empty($image_full_name) && isset($_FILES['backgroundimage']['tmp_name'])) {
							//echo ;die;
							$upload = wp_upload_bits($image_full_name, null, file_get_contents(sanitize_text_field($_FILES['backgroundimage']['tmp_name'])));
						}
						if (isset($upload['error']) && 0 != $upload['error']) {
							add_action('admin_notices', array('AwPopupProAdmin','aw_self_deactivate_notice')); 
						} else {
							$_POST['design']['background']['backgroundimage'] = $upload['url']; 
						}
					} else {
						add_action('admin_notices', array('AwPopupProAdmin','aw_self_deactivate_notice')); 
					}
				} else {
					if (isset($_POST['backgroundimage'])) {
						$_POST['design']['background']['backgroundimage'] = sanitize_text_field($_POST['backgroundimage']);		
					}
				}

				$design = json_encode($_POST);
				$design = wp_unslash($design);
				$design = json_decode($design, true);

				update_post_meta($post_id, 'popup_pro_subscribe_design', $design['design']);
			}

			if (isset($_POST['popup_pro_subscribe_when_display']) && ( '' != $_POST['popup_pro_subscribe_when_display'] )) {
				update_post_meta($post_id, 'popup_pro_subscribe_when_display', sanitize_text_field($_POST['popup_pro_subscribe_when_display']));
			}

			if (isset($_POST['popup_pro_subscribe_cookie_lifetime']) && ( '' != $_POST['popup_pro_subscribe_cookie_lifetime'] )) {
				update_post_meta($post_id, 'popup_pro_subscribe_cookie_lifetime', sanitize_text_field($_POST['popup_pro_subscribe_cookie_lifetime']));
			}

			if (isset($_POST['popup_pro_subscribe_x_equalto']) && ( '' != $_POST['popup_pro_subscribe_x_equalto'] )) {
				update_post_meta($post_id, 'popup_pro_subscribe_x_equalto', sanitize_text_field($_POST['popup_pro_subscribe_x_equalto']));
			}

			if (isset($_POST['popup_pro_subscribe_template']) && ( '' != $_POST['popup_pro_subscribe_template'] )) {
				$aw_get_template = new AwSubscribePopupProTemplates();
				$aw_get_template_data = $aw_get_template->aw_get_templates_data(sanitize_text_field($_POST['popup_pro_subscribe_template']));

				$dochtml = new DOMDocument();
				libxml_use_internal_errors(true);
				$dochtml->loadHTML($aw_get_template_data);
				libxml_clear_errors();

				$elm = $dochtml->getElementById('template_width');
				$width = $elm->nodeValue;

				update_post_meta($post_id, 'popup_pro_subscribe_template_width', $width);
				update_post_meta($post_id, 'popup_pro_subscribe_template', sanitize_text_field($_POST['popup_pro_subscribe_template']));
			}

			if (isset($_POST['popup_pro_subscribe_image']) && ( '' != $_POST['popup_pro_subscribe_image'] )) {
				update_post_meta($post_id, 'popup_pro_subscribe_image', sanitize_text_field($_POST['popup_pro_subscribe_image']));
			}
		}
	}

	public static function aw_popup_pro_table_head( $columns) {
		/** Add Column in data grid listing **/
		$columns = array(
		'cb' 				=> '&lt;input type="checkbox" />',
		'sno'				=> __('ID'),
		'title' 			=> __('Name'),
		'popup_pro_priority'=> __('Priority'),
		'post_status'   	=> __('Status'),
		'post_modified' 	=> __('Published Date'),
		'popup_pro_views'	=> __('Views'),
		'popup_pro_clicks'	=> __('Clicks'),
		'popup_pro_ctr'		=> __('CTR')
		);
		return $columns;
	}

	public static function aw_popup_pro_subscribe_table_head( $columns) {
		/** Add Column Names in data grid listing **/
		$columns = array(
		'cb' 							=> '&lt;input type="checkbox" />',
		'sno'							=> __('ID'),
		'title' 						=> __('Name'),
		'post_status'   				=> __('Status'),
		'post_modified' 				=> __('Published Date'),
		'popup_pro_subscribe_views'		=> __('Views'),
		'popup_pro_subscribe_clicks'	=> __('Clicks'),
		'popup_pro_subscribe_ctr'		=> __('CTR')
		);
		return $columns;
	}

	public static function aw_popup_pro_table_columns( $column, $post_id) {
		/** Table data head and values **/
		global $post;

		switch ($column) {
			case 'sno':
				echo esc_html($post_id);
				break;

			case 'popup_title':
				/* Get the post meta. */
				$popup_title = get_post_meta( $post_id, 'popup_title', true );

				/* If no popup title is found, output a default message. */
				if (empty($popup_title)) { 
					echo  'Unknown' ; 
				} else { 
					echo wp_kses_post($popup_title) ; 
				} 
				break;

			case 'popup_pro_priority':
				$popup_priority = (int) get_post_meta($post_id, 'popup_pro_priority', true);
				echo wp_kses_post($popup_priority); 
				break;	

			case 'post_status':
				$status = get_post_status($post_id);
				if ( 'draft' == $status ) {
					$status = 'Unpublished';
				}
				if ('publish'  ==  $status) {
					$status = 'Published';
				}
				update_post_meta($post_id, 'post_status', $status);
				echo wp_kses_post(get_post_meta($post_id, 'post_status', true));
				break;

			case 'post_modified':
				echo wp_kses_post(get_the_modified_date('F d, Y h:i:s A'));
				break;

			case 'popup_pro_views':
				$popup_pro_views = (int) get_post_meta($post_id, 'popup_pro_views', true);
				if (!empty( $popup_pro_views ) || '' != $popup_pro_views) {
					echo esc_html($popup_pro_views);
				} else {
					update_post_meta($post_id, 'popup_pro_views', 0);
					echo (int) get_post_meta($post_id, 'popup_pro_views', true);
				}
				break;

			case 'popup_pro_clicks':
				$popup_pro_clicks = (int) get_post_meta($post_id, 'popup_pro_clicks', true);
				if (!empty( $popup_pro_clicks ) || '' != $popup_pro_clicks) {
					echo esc_html($popup_pro_clicks);
				} else {
					update_post_meta($post_id, 'popup_pro_clicks', 0);
					echo (int) get_post_meta($post_id, 'popup_pro_clicks', true);
				}
				break;

			case 'popup_pro_ctr':
				$popup_pro_views = (int) get_post_meta($post_id, 'popup_pro_views', true);
				$popup_pro_clicks = (int) get_post_meta($post_id, 'popup_pro_clicks', true);

				if (( !empty($popup_pro_views) || 0 != $popup_pro_views ) && ( !empty($popup_pro_clicks) || 0 != $popup_pro_clicks )) {
					$ctr = ( $popup_pro_clicks / $popup_pro_views );
					$ctr = ( $ctr * 100 );
					update_post_meta($post_id, 'popup_pro_ctr', $ctr);
				} else {
					update_post_meta($post_id, 'popup_pro_ctr', 0);
				}
				echo esc_html(round(get_post_meta($post_id, 'popup_pro_ctr', true), 2) . '%');
				break;

			default:
				break;
		}
	}

	public static function aw_popup_pro_subscribe_table_columns( $column, $post_id) {
		/** Table data head and values **/
		global $post;

		switch ($column) {
			case 'sno':
				echo esc_html($post_id);
				break;

			case 'popup_title':
				/* Get the post meta. */
				$popup_title = get_post_meta( $post_id, 'popup_title', true );

				/* If no popup title is found, output a default message. */
				if (empty($popup_title)) { 
					echo  'Unknown' ; 
				} else {
					echo wp_kses_post($popup_title) ;
				} 
				break;

			case 'post_status':
				$status = get_post_status($post_id);
				if ('draft' == $status ) {
					$status = 'Unpublished';
				}
				if ('publish' == $status ) {
					$status = 'Published';
				}
				update_post_meta($post_id, 'post_status', $status);
				echo wp_kses_post(get_post_meta($post_id, 'post_status', true));	
				break;

			case 'post_modified':
				echo wp_kses_post(get_the_modified_date('F d, Y h:i:s A', $post_id));
				break;

			case 'popup_pro_subscribe_views':
				$popup_pro_views = (int) get_post_meta($post_id, 'popup_pro_subscribe_views', true);
				if (!empty( $popup_pro_views ) || '' != $popup_pro_views) {
					echo wp_kses_post($popup_pro_views);
				} else {
					update_post_meta($post_id, 'popup_pro_subscribe_views', 0);
					echo (int) get_post_meta($post_id, 'popup_pro_subscribe_views', true);
				}
				break;

			case 'popup_pro_subscribe_clicks':
				$popup_pro_clicks = (int) get_post_meta($post_id, 'popup_pro_subscribe_clicks', true);
				if (!empty( $popup_pro_clicks ) || '' != $popup_pro_clicks) {
					echo esc_html($popup_pro_clicks);
				} else {
					update_post_meta($post_id, 'popup_pro_subscribe_clicks', 0);
					echo (int) get_post_meta($post_id, 'popup_pro_subscribe_clicks', true);
				}
				break;

			case 'popup_pro_subscribe_ctr':
				$popup_pro_views 	= (int) get_post_meta($post_id, 'popup_pro_subscribe_views', true);
				$popup_pro_clicks 	= (int) get_post_meta($post_id, 'popup_pro_subscribe_clicks', true);

				if (( !empty($popup_pro_views) || 0 != $popup_pro_views ) && ( !empty($popup_pro_clicks) || 0 != $popup_pro_clicks )) {
					$ctr = ( $popup_pro_clicks / $popup_pro_views );
					$ctr = ( $ctr * 100 );
					update_post_meta($post_id, 'popup_pro_subscribe_ctr', $ctr);
				} else {
					update_post_meta($post_id, 'popup_pro_subscribe_ctr', 0);
				}
				echo esc_html(round(get_post_meta($post_id, 'popup_pro_subscribe_ctr', true), 2) . '%');
				break;

			default:
				break;
		}
	}

	public static function aw_popup_pro_column_register_sortable( $columns) {
		/** Sortable headers of listing grid **/

		$columns['post_status'] 		= 'post_status';
		$columns['post_modified'] 		= 'post_modified';
		$columns['popup_pro_priority'] 	= 'popup_pro_priority';
		$columns['popup_pro_views'] 	= 'popup_pro_views';
		$columns['popup_pro_clicks']	= 'popup_pro_clicks';
		$columns['popup_pro_ctr'] 		= 'popup_pro_ctr';
		$columns['sno'] 				= 'sno';
		return $columns;
	}

	public static function aw_columndata_sorting_orderby( $query) {
		/** OrderBy headers of listing grid **/
		if (! is_admin()) {
			return;
		}
		global $wpdb;
		$orderby = $query->get('orderby');
		$order = $query->get('order');
		
		if ('sno' == $orderby) {
			$query->set('orderby', 'ID');
		}
		
		if ('popup_pro_priority' == $orderby) {
			$query->set('meta_key', 'popup_pro_priority');
			$query->set('orderby', 'meta_value_num');
		}

		if ('post_status' == $orderby) {
			$query->set( 'meta_key', 'post_status' );
			$query->set( 'orderby', 'meta_value' );
		}

		if ('popup_pro_views' == $orderby) {
			$query->set( 'meta_key', 'popup_pro_views' );
			$query->set( 'orderby', 'meta_value_num' );
		}

		if ('popup_pro_clicks' == $orderby) {
			$query->set( 'meta_key', 'popup_pro_clicks' );
			$query->set( 'orderby', 'meta_value_num' );
		}

		if ('popup_pro_ctr' == $orderby) {
			$query->set( 'meta_key', 'popup_pro_ctr' );
			$query->set( 'orderby', 'meta_value_num' );
		}

		if ('popup_pro_subscribe_clicks' == $orderby) {
			$query->set( 'meta_key', 'popup_pro_subscribe_clicks');
			$query->set( 'orderby', 'meta_value_num');
		}

		if ('popup_pro_subscribe_views' == $orderby) {
			$query->set( 'meta_key', 'popup_pro_subscribe_views');
			$query->set( 'orderby', 'meta_value_num');
		}

		if ('popup_pro_subscribe_ctr' == $orderby) {
			$query->set( 'meta_key', 'popup_pro_subscribe_ctr');
			$query->set( 'orderby', 'meta_value_num');
		}
	}

	public static function aw_popup_pro_subscribe_column_register_sortable( $columns) {
		/** Sortable headers of listing grid **/

		$columns['post_status'] 				= 'post_status';
		$columns['post_modified'] 				= 'post_modified';
		$columns['popup_pro_subscribe_views'] 	= 'popup_pro_subscribe_views';
		$columns['popup_pro_subscribe_clicks']	= 'popup_pro_subscribe_clicks';
		$columns['popup_pro_subscribe_ctr'] 	= 'popup_pro_subscribe_ctr';
		$columns['sno'] 						= 'sno';
		return $columns;
	}

	public static function aw_popup_pro_register_my_bulk_actions( $bulk_actions) {
		/** Bulk Publish / Unpublish Actions Renamed **/

		$bulk_actions['publish']	= __('Published', 'publish');
		$bulk_actions['draft'] 		= __('Unpublished', 'draft');
		return $bulk_actions;
	}

	public static function aw_popup_pro_subscribe_register_my_bulk_actions( $bulk_actions) {
		/** Bulk Publish / Unpublish Actions Renamed **/

		$bulk_actions['publish'] 	= __('Published', 'publish');
		$bulk_actions['draft'] 		= __('Unpublished', 'draft');
		return $bulk_actions;
	}

	public static function aw_popup_pro_my_bulk_action_handler( $redirect_to, $doaction, $post_ids) {
		/** Bulk Publish / Unpublish Action Handler **/

		foreach ($post_ids as $post_id) {
			$update_post = array(
				'post_type' 	=> 'popup-pro',
				'ID' 			=> $post_id,
				'post_status' 	=> $doaction
			);
			$statusTest = wp_update_post($update_post);
		}
		return $redirect_to;
	}

	public static function aw_popup_pro_subscribe_my_bulk_action_handler( $redirect_to, $doaction, $post_ids) {
		/** Bulk Publish / Unpublish Action Handler **/

		foreach ($post_ids as $post_id) {
			$update_post = array(
				'post_type' 	=> 'popup-pro-subscribe',
				'ID' 			=> $post_id,
				'post_status' 	=> $doaction
			);
			$statusTest = wp_update_post($update_post);
		}
		return $redirect_to;
	}

	public static function aw_popup_pro_admin_menu() {
		/** Add Popup Pro Menu options and remove create new popup from menulist and edit page **/

		$page = add_submenu_page('edit.php?post_type=popup-pro', 'Select popup type', 'Create New Popup', 'manage_options', 'popup-pro/includes/popup-pro-by-aheadworks.php', array('AwPopupProAdmin','aw_popup_pro_admin_sub_page')); 
		$page = add_submenu_page('edit.php?post_type=popup-pro', 'Select popup type', 'Subscribe popups', 'manage_options', 'edit.php?post_type=popup-pro-subscribe');
		$page = add_submenu_page('edit.php?post_type=popup-pro', 'Subscribers', 'Subscribers', 'manage_options', 'subscribers-list', array('AwPopupProAdmin','aw_subscribers_list_include')); 

		global $submenu;
		if (isset($submenu['edit.php?post_type=popup-pro'])) {
			unset($submenu['edit.php?post_type=popup-pro'][10]);
		}
		
		if (isset($submenu['edit.php?post_type=popup-pro-subscribe'])) {
			unset($submenu['edit.php?post_type=popup-pro-subscribe'][9]);
		}
		
		if (isset($submenu['edit.php?post_type=popup-pro'])) {
			$keys = array_column($submenu['edit.php?post_type=popup-pro'], '0');
			array_multisort($keys, SORT_ASC, $submenu['edit.php?post_type=popup-pro']);
		}
		remove_menu_page('edit.php?post_type=popup-pro-subscribe'); 
	}

	public static function aw_update_edit_form() { 
		/** Added multipart/form-data for Background Image Uploading **/
		echo ' enctype="multipart/form-data"';
	}

	public static function aw_subscribe_popup_pro_preview_action_page() {
		if (isset($_GET['no_pro'])) {
			$no_pro = sanitize_key($_GET['no_pro']);	
		}
		if (isset($_GET['title'])) {
			$title 	= sanitize_key($_GET['title']);
		}

		if (0 == $no_pro) {
			$no_pro = -1;	
		}

		/** Admin side preview of Linked Products Popup **/
		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => $no_pro,
		);

		$loop = new WP_Query($args);
		$i = 0;
		echo '<div class="popup-pro-main-dv linked-main">';
		while ($loop->have_posts()) :
			$loop->the_post();
			global $product;

			echo wp_kses_post('<div class="popup-pro-show-tb_prod">
						<div class="popup-pro-show-tb_imgage">' . woocommerce_get_product_thumbnail(array(150,150)) . '</div> 
						<div class="popup-pro-show-tb_name">' . wp_kses_post(get_the_title()) . '</div>
						<div class="popup-pro-show-tb_price">' . get_woocommerce_currency_symbol() . ' ' . $product->get_price() . '</div>
						<div class="popup-pro-show-tb_cart"><a id="popup-pro-show-tb_btn_42" class="popup-pro-show-tb_btn" href="#">Add To Cart</a></div>
					</div>');
			$i++;			
		endwhile;
		echo '</div>';
		wp_reset_query(); 
		?>
			<script language="javascript">
				resize_tb('<?php echo wp_kses_post($i); ?>');
				jQuery("#TB_ajaxWindowTitle").html('<?php echo wp_kses_post($title); ?>');
			</script>
		<?php
	}

	public static function aw_subscribe_popup_preview_action_page() {
		/** Admin side preview of Subscriber Popup **/
		if (isset($_REQUEST['template']) && sanitize_key($_REQUEST['template'])) {
			$template = sanitize_key($_REQUEST['template']);	
		}

		if (isset($template)) {
			$aw_get_template = new AwSubscribePopupProTemplates();
			$aw_get_template_data = $aw_get_template->aw_get_templates_data($template);

			$data = htmlentities($aw_get_template_data);
			$designs = array('title','subtitle','emailform','subscribebutton','closebutton','background');
			$style = array();
			?>
			<script language="javascript">
			function change_placeholder_color(target_class, color_choice)
			{
				jQuery("body").append("<style>" + target_class + "::placeholder{color:" +  color_choice + "};" 
				+ target_class + "::-webkit-input-placeholder{color:" +  color_choice + "};"
				+ target_class + "::-moz-placeholder{color:" +  color_choice + "};"
				+ target_class + ":-ms-input-placeholder{color:" +  color_choice + "};"
				+ target_class + ":-moz-placeholder{color:" +  color_choice + "};</style>");
			}
			</script>
			<?php
			foreach ($designs as $design) :
				?>
				<script language="javascript">
				main_title = jQuery("#popup_pro_subscribe_title").val();
				sub_title = jQuery("#popup_pro_subscribe_subtitle").val();
				jQuery(".header-title span").text(main_title);
				jQuery(".header-title p").text(sub_title);
				btn_text = jQuery("#popup_pro_subscribe_button").val();
				jQuery(".submit-btn span").text(btn_text);
				jQuery("#popup_pro_subscribe").removeAttr("onclick");
				var template = '<?php echo esc_html($template); ?>';

				switch("<?php echo wp_kses_post($design); ?>")
				{
					case 'title':
					var font_family= jQuery('#'+"<?php echo wp_kses_post($design) . '_font-family'; ?>").val();
					var fontweight= jQuery('#'+"<?php echo wp_kses_post($design) . '_font-weight'; ?>").val();
					var font_style = "normal";
					if(null !== fontweight )
					{
						var font_weight = fontweight.match(/.{1,3}/g);
						if(fontweight.length > 3)
						{
							font_style = "italic";
						}
					}
					var font_size= jQuery('#'+"<?php echo wp_kses_post($design) . '_font-size'; ?>").val();
					var font_color= jQuery('#'+"<?php echo wp_kses_post($design) . '_color'; ?>").val();

					var str = font_family; 
					var res = str.replace(" ", "+");

					jQuery(".header-title span").css({"font-family":font_family,"font-size":font_size+'px',"font-weight":font_weight[0], "font-style":font_style, "color":'#'+font_color});
					break;

					case 'subtitle':
					var font_family= jQuery('#'+"<?php echo wp_kses_post($design) . '_font-family'; ?>").val();
					var fontweight= jQuery('#'+"<?php echo wp_kses_post($design) . '_font-weight'; ?>").val();
					var font_style = "normal";
					if( null  !== fontweight)
					{
						var font_weight = fontweight.match(/.{1,3}/g);
						if(fontweight.length > 3)
						{
							font_style = "italic";
						}
					}
					var font_size= jQuery('#'+"<?php echo wp_kses_post($design) . '_font-size'; ?>").val();
					var font_color= jQuery('#'+"<?php echo wp_kses_post($design) . '_color'; ?>").val();

					var str = font_family;
					var res = str.replace(" ", "+");

					jQuery(".header-title p").css({"font-family":font_family,"font-size":font_size+'px',"font-weight":font_weight[0], "font-style":font_style,"color":'#'+font_color});
					break;

					case 'emailform':
					var font_family= jQuery('#'+"<?php echo wp_kses_post($design) . '_font-family'; ?>").val();
					var fontweight= jQuery('#'+"<?php echo wp_kses_post($design) . '_font-weight'; ?>").val();
					var font_style = "normal";
					if(null !== fontweight )
					{
						var font_weight = fontweight.match(/.{1,3}/g);
						if(fontweight.length > 3)
						{
							font_style = "italic";
						}
					}
					var font_size= jQuery('#'+"<?php echo wp_kses_post($design) . '_font-size'; ?>").val();
					var font_color= jQuery('#'+"<?php echo wp_kses_post($design) . '_color'; ?>").val();

					var str = font_family;
					var res = str.replace(" ", "+");

					jQuery(".input-txt1").css({"font-family":font_family,"font-size":font_size+'px',"font-weight":font_weight[0], "font-style":font_style,"color":'#'+font_color});
					change_placeholder_color('.input-txt1', '#'+font_color);
					break;

					case 'subscribebutton':
					var font_family= jQuery('#'+"<?php echo wp_kses_post($design) . '_font-family'; ?>").val();
					var fontweight= jQuery('#'+"<?php echo wp_kses_post($design) . '_font-weight'; ?>").val();
					var font_style = "normal";
					if(null !== fontweight)
					{
						var font_weight = fontweight.match(/.{1,3}/g);
						if(fontweight.length > 3)
						{
							font_style = "italic";
						}
					}
					var font_size= jQuery('#'+"<?php echo wp_kses_post($design) . '_font-size'; ?>").val();
					var font_color= jQuery('#'+"<?php echo wp_kses_post($design) . '_color'; ?>").val();
					var border_radius= jQuery('#'+"<?php echo wp_kses_post($design) . '_border-radius'; ?>").val();
					var button_color= jQuery('#'+"<?php echo wp_kses_post($design) . '_button-color'; ?>").val();

					var str = font_family; 
					var res = str.replace(" ", "+");

					jQuery(".submit-btn").css({"font-family":font_family,"font-size":font_size+'px',"font-weight":font_weight[0], "font-style":font_style,"color":'#'+font_color,"border-radius":border_radius+'px',"background-color":'#'+button_color});
					jQuery(".btn-arw").css({"background-color":'#'+button_color});
					break;

					case 'closebutton':
					var background_color= jQuery('#'+"<?php echo wp_kses_post($design) . '_background-color'; ?>").val();
					var buttonsize= jQuery('#'+"<?php echo wp_kses_post($design) . '_buttonsize'; ?>").val();
					jQuery(".close-pop1 a").css({"width":buttonsize+'px',"fill":'#'+background_color});
					break;

					case 'background':
					var background_color= jQuery('#'+"<?php echo wp_kses_post($design) . '_background-color'; ?>").val();
					var background_image= jQuery('#backgroundimage').attr('data-value');
						switch(template)
						{
							case 'aw-popup-template01':
								if("FFFFFF" == background_color )
								{
									jQuery(".header-title span").css({"background-color":'#48CBDF'});
								}
								if( "FFFFFF" != background_color && "" == background_image)
								{
									jQuery(".header-title span").css({"background-color":'#'+background_color});
									jQuery(".subscribe-one").addClass("bg_active");
								}
								if( "" != background_image)
								{
									jQuery(".header-title span").css({"background-color":'transparent'});
								}
							break;
							
							case 'aw-popup-template02':
								if( "FFFFFF" != background_color || '' != background_image)
								{
									jQuery("#subscrib_imag_t").remove();
								}
							break;
							
							case 'aw-popup-template03':
								if("FFFFFF" != background_color && "" == background_image )
								{
									var background_color_bg = jQuery('#'+"<?php echo wp_kses_post($design) . '_background-color'; ?>").val();
									var cls_btn_clr = jQuery('#closebutton_background-color').val();
									jQuery(".close-pop1 a").css({"width":buttonsize+'px',"fill":'#'+cls_btn_clr,"background-color":'#'+background_color_bg});
									jQuery(".close-pop1").css({"background-color":'#'+background_color_bg});
									jQuery(".subscribe-three").addClass("bg_active");
								}
							break;
							
							case 'aw-popup-template04':
								if( "F1F1F1" != background_color &&  "" == background_image)
								{
									jQuery(".subscribe-four").addClass("bg_active");
								}
							break;

							case 'aw-popup-template06':
								if("FFFFFF" != background_color || '' != background_image)
								{
									jQuery(".header-title span").css({"background":'transparent'});
									jQuery(".header-title p").css({"background":'transparent'});
									jQuery(".subscribe-six").addClass("bg_active");
								}
							break;

							case 'aw-popup-template07':
								if("FFFFFF"  !=  background_color && "" == background_image)
								{
									jQuery(".subscribe-seven").addClass("bg_active");
								}
							break;
						}

						jQuery(".popup-main").css({"background-color":'#'+background_color});
						if('' != background_image)
						{
							jQuery('#subscrib_imag_o').attr('src',background_image);
							jQuery('#popupfullbackground').css({'background-image': 'url(' + background_image + ')', 'background-repeat': 'no-repeat'});	
						}
					break;
				}
				</script>
				<?php
			endforeach;
			?>
			<script language="javascript">
			resize_subs_popup();

			function close_tb()
			{
				tb_remove();
			}
			</script>
			<?php
		} else {
			$data= 'No Template selected';
		}
		//echo html_entity_decode(sanitize_text_field($data));
		echo wp_kses(html_entity_decode(sanitize_text_field($data)), wp_kses_allowed_html('post'));
		die;
	}

	public static function aw_pop_pro_allnotice_messages( $messages ) { 
		/** Custom messages for various actions of Popup Pro Plugin **/
		$post  		= get_post();
		$post_type 	= get_post_type($post);
		$post_type_object = get_post_type_object($post_type);

		$messages['popup-pro'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => __( 'Popup updated.', 'popup_pro_plugin' ),
			2 => __( 'Popup field updated.', 'popup_pro_plugin' ),
			3 => __( 'Popup field deleted.', 'popup_pro_plugin' ),
			4 => __( 'Popup updated.', 'popup_pro_plugin' ),
			/*5 => isset( $_GET['revision'] ) ? sprintf( __( 'Popup restored to revision from %s', 'popup_pro_plugin' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,*/
			6 => __( 'Popup published.', 'popup_pro_plugin' ),
			7 => __( 'Popup saved.', 'popup_pro_plugin' ),
			8 => __( 'Popup submitted.', 'popup_pro_plugin' ),
			/*9 => sprintf(
			__( 'Popup scheduled for: <strong>%1$s</strong>.', 'popup_pro_plugin' ),
			date_i18n( __( 'M j, Y @ G:i', 'popup_pro_plugin' ), strtotime( $post->post_date ) )
			),*/
			10 => __( 'Popup updated.', 'popup_pro_plugin'),
		);

		$messages['popup-pro-subscribe'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => __( 'Subscribe popup updated.', 'popup_pro_plugin' ),
			2 => __( 'Subscribe popup field updated.', 'popup_pro_plugin' ),
			3 => __( 'Subscribe popup field deleted.', 'popup_pro_plugin' ),
			4 => __( 'Subscribe popup updated.', 'popup_pro_plugin' ),
			/*5 => isset( $_GET['revision'] ) ? sprintf( __( 'Subscriber popup restored to revision from %s', 'popup_pro_plugin' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,*/
			6 => __( 'Subscribe popup published.', 'popup_pro_plugin' ),
			7 => __( 'Subscribe popup saved.', 'popup_pro_plugin' ),
			8 => __( 'Subscribe popup submitted.', 'popup_pro_plugin' ),
			/*9 => sprintf(
			__( 'Subscribe popup scheduled for: <strong>%1$s</strong>.', 'popup_pro_plugin' ),
			date_i18n( __( 'M j, Y @ G:i', 'popup_pro_plugin' ), strtotime( $post->post_date ) )
			),*/
			10 => __( 'Subscribe popup updated.', 'popup_pro_plugin'),
		);

		return $messages;
	}

	public static function aw_popup_pro_trashnotice() { 
		/** Custom messages for trash and restore actions of Popup Pro Plugin **/
		if (isset($_REQUEST['post_type'])) {
			if ('popup-pro' == $_REQUEST['post_type']) {
				$class = 'updated notice is-dismissible';
				if (isset($_REQUEST['trashed'])) {
					$message = __( wp_kses_post($_REQUEST['trashed']) . ' popup moved to the Trash.', 'your-plugin-textdomain' );
					printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
				}
				if (isset($_REQUEST['untrashed'])) {
					$message = __( wp_kses_post($_REQUEST['untrashed']) . ' popup restored from the Trash.', 'your-plugin-textdomain' );
					printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
				}

				echo '<style> #message { display: none; }</style>';
			}

			if ('popup-pro-subscribe' == $_REQUEST['post_type']) {
				$class = 'updated notice is-dismissible';
				if (isset($_REQUEST['trashed'])) {
					$message = __( wp_kses_post($_REQUEST['trashed']) . ' subscriber popup moved to the Trash.', 'your-plugin-textdomain' );
					printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
				}
				if (isset($_REQUEST['untrashed'])) {
					$message = __( wp_kses_post($_REQUEST['untrashed']) . ' subscriber popup restored from the Trash.', 'your-plugin-textdomain' );
					printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
				}
				echo '<style> #message { display: none; }</style>';
			}
		}
	}

	public static function aw_modified_views_post_status( $views) {
		/** Custom text for changing Draft To Unpublished Popup Pro Plugin **/
		if (isset($views['draft'])) {
			$views['draft'] = str_replace('Drafts', 'Unpublished', $views['draft']);
			$views['draft'] = str_replace('Draft', 'Unpublished', $views['draft']);
		}
		return $views;
	}
}

function get_string_between( $string, $start, $end) {
	/** Callback action for function aw_subscribefont_ajax_request() **/
	$string = ' ' . $string;
	$ini = strpos($string, $start);
	if ( 0 == $ini) {
		return '';
	}
	$ini += strlen($start);
	$len = strpos($string, $end, $ini) - $ini;
	return substr($string, $ini, $len);
}

function aw_subscribefont_ajax_request() { 
	$option = '';
	if (isset($_GET['data'])) {
		$font_family = wp_kses_post($_GET['data']);
		$get_fonts = new AwSubscribePopupProFonts();
		$fonts_weight = $get_fonts->aw_get_font_weight($font_family);

		if (count($fonts_weight) > 0) {
			foreach ($fonts_weight as $font_weight => $font_weight_name) {
				$selected = '';
				if (isset($_GET['weight']) && $_GET['weight']==$font_weight) {
					$selected='selected=selected';
				}
				$option .= '<option ' . $selected . ' value="' . $font_weight . '">' . $font_weight_name . '</option>';
			}
		}
	}
	$allowed_html = array(
							'option' => array(
							'value'      => array(),
							)
						); 
	echo wp_kses($option, $allowed_html);
	wp_die();
}

function aw_popup_pro_cookies( $post_id, $cookie_lifetime) {
	/** Set and Get Cookies for Linked Product Popup **/
	$path = '/';
	$host = parse_url(get_option('siteurl'), PHP_URL_HOST);

	$cookie_name = 'popup_pro_cookie_' . $post_id;
	if (!isset($_COOKIE[$cookie_name])) {
		setcookie($cookie_name, $post_id, time() + ( 60 * $cookie_lifetime ), $path, $host);
	}
}

function aw_popup_pro_subscribers_cookies( $post_id = null, $type = null, $x_equalto = null, $cookie_lifetime = null) {
	$status = 0;

	$popup_viewed_pages = sanitize_text_field('popup_pro_subscribers_cookie_viewed_pages');
	$popup_minutes_visit = sanitize_text_field('popup_pro_subscribers_cookie_minutes_visit');
	$page_view_count = 1;
	$checkoutpage = 0;

	$path = '/';
	$host = parse_url(get_option('siteurl'), PHP_URL_HOST);

	global $woocommerce,$pagenow;
	$woocommerce_keys = array('woocommerce_checkout_page_id');
	foreach ($woocommerce_keys as $wc_page_id) {
		if (get_the_ID() == get_option($wc_page_id, 0)) {
			$checkoutpage = 1;
		}
	}

	if (is_multisite()) {
		$current_site = get_current_blog_id();
		$popup_viewed_pages .= '_' . $current_site;
		$popup_minutes_visit .= '_' . $current_site;
	}

	if (empty($woocommerce->cart->cart_contents) && 1 == $checkoutpage ) {

		//$popup_viewed_pages = sanitize_text_field('popup_pro_subscribers_cookie_viewed_pages');

		if (isset($_COOKIE[$popup_viewed_pages])) {
			setcookie($popup_viewed_pages, wp_kses_post($_COOKIE[$popup_viewed_pages]), time() + ( 60 * 180 ), $path, $host);
		}

		if (!isset($_COOKIE[$popup_viewed_pages])) {
			$page_view_count = '';
			setcookie($popup_viewed_pages, $page_view_count, time() + ( 60 * 180 ), $path, $host);
		}
	} else if (is_wc_endpoint_url('customer-logout')) {
		//$popup_viewed_pages = sanitize_text_field('popup_pro_subscribers_cookie_viewed_pages');

		if (isset($_COOKIE[$popup_viewed_pages])) {
			setcookie($popup_viewed_pages, wp_kses_post($_COOKIE[$popup_viewed_pages]), time() + ( 60 * 180 ), $path, $host);
		}

		if (!isset($_COOKIE[$popup_viewed_pages])) {
			$page_view_count = '';
			setcookie($popup_viewed_pages, $page_view_count, time() + ( 60 * 180 ), $path, $host);
		}
	} else {
	
		if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {

			/** Set and Get Cookies for Subscriber Popup **/

			if (!isset($_COOKIE[$popup_viewed_pages])) {
				setcookie($popup_viewed_pages, $page_view_count, time() + ( 60 * 180 ), $path, $host);
			} else {
				setcookie($popup_viewed_pages, wp_kses_post($_COOKIE[$popup_viewed_pages])+1, time() + ( 60 * 180 ), $path, $host);
			}

			if (!isset($_COOKIE[$popup_minutes_visit])) {
				setcookie($popup_minutes_visit, time(), time() + ( 60 * 180 ), $path, $host);
			}

			if ( 0 != $post_id) {
				$cookie_name = 'popup_pro_subscriber_cookie_' . $post_id;
				if (!isset($_COOKIE[$cookie_name])) {
					setcookie($cookie_name, $post_id, time() + ( 60 * $cookie_lifetime ), $path, $host);
				}
			}
		}
	}
}

function aw_popup_pro_is_subscribed() {
	/** Check if a visitor is already subscribed for Popup Pro Plugin **/
	$is_subscribed = 'aw_popup_pro_is_subscribed';
	if (is_multisite()) {
		$current_site 	= get_current_blog_id();	
		$is_subscribed .= '_' . $current_site;
	}
	if (isset($_COOKIE[$is_subscribed]) && 'Yes' == $_COOKIE[$is_subscribed] ) {
		return true;
	}
	return false;
}

function aw_popup_background_image_delete() {
	if (isset($_REQUEST['postid'])) {
		$post_id = wp_kses_post($_REQUEST['postid']);
		$data=get_post_meta($post_id, 'popup_pro_subscribe_design', true);
		if (isset($data['background']['backgroundimage'])) {
			$path = wp_get_upload_dir();
			$imagepath = explode('uploads', $data['background']['backgroundimage']) ;
			if (isset($path['basedir']) && isset($imagepath[1])) {
				$fullpath  = $path['basedir'] . $imagepath[1];	
				if (file_exists($fullpath)) {
					if (unlink($fullpath)) {
						$data['background']['backgroundimage']='';
						update_post_meta($post_id, 'popup_pro_subscribe_design', array_filter($data));
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

function aw_pop_kses_filter_allowed_html( $allowed, $context) {
	$allowed['style'] 				= array();
	$allowed['svg'] 				= array(
			'class'				=> true,
			'aria-hidden' 		=> true,
			'aria-labelledby'	=> true,
			'role'				=> true,
			'xmlns'				=> true,
			'width'				=> true,
			'height'			=> true,
			'viewbox'			=> true,
			'version'			=> true, 
			'id'				=> true, 
			'xmlns:xlink'		=> true, 
			'x'					=> true, 
			'y'					=> true, 
			'style'				=> true, 
			'xml:space'			=> true, 
	);
	$allowed['g']					= array( 'fill' => true );
	$allowed['title']				= array( 'title' => true );
	$allowed['path']				= array( 'id' => true, 'd' => true, 'fill' => true );
	$allowed['input']['type'] 		= array();
	$allowed['input']['name'] 		= array();
	$allowed['input']['value'] 		= array();
	$allowed['input']['id'] 		= array();
	$allowed['input']['class'] 		= array();
	$allowed['input']['placeholder'] = array();
	$allowed['input']['style'] 		= array();
	/*$allowed['div'] 				= array();
	$allowed['div']['class']		= array();
	$allowed['div']['style']		= array();
	$allowed['a']['data-quantity'] 	= array();
	$allowed['a']['add-to-cart'] 	= array();
	$allowed['a']['data-product_id'] = array();
	$allowed['a']['data-popup_id'] 	= array();*/
	$allowed['a']['onclick'] 		= array();
	$allowed['span']['onclick'] 	= array();
	$allowed['button']['onclick'] 	= array();
	return $allowed;
}
?>
