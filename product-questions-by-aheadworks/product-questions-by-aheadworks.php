<?php
/**
 * Plugin Name:  Product Questions And FAQ By Aheadworks
 * Plugin URI: https://www.aheadworks.com/
 * Description: Product Questions And FAQ plugin for WooCommerce. Adds Question & Answer area to product pages. FAQ enable customers to find necessary information in a matter of a few clicks.
 * Author: Aheadworks
 * Author URI: https://www.aheadworks.com/
 * Version: 2.0.0
 * Woo: 6331065:bca00d1d7cc732d823cbe1034881a576
 * Text Domain: product-questions-by-aheadworks
 *
 * @package product-questions-by-aheadworks
 *
 * Requires at least: 5.3.6
 * Tested up to: 5.7
 * WC requires at least: 4.3.3
 * WC tested up to: 5.1
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

require_once(plugin_dir_path(__FILE__) . 'includes/aw-pq-product-questions-qa-tab.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-pq-product-questions-admin.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-pq-product-questions-walker.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-pq-faq-first-ask-questions-admin.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-pq-faq-category-list.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-pq-faq-article-admin.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-pq-faq-first-ask-question-public.php');


$rdproductquestions = new AwPqProductQuestions();

/** Present plugin version **/
define( 'AW_PQ_PRODUCT_QUESTION_VERSION', '1.0.0' );

class AwPqProductQuestions {
	public function __construct() {
		/** Constructor function, initialize and register hooks **/
		//register_activation_hook( __FILE__ , array(get_called_class(),'aw_pq_product_questions_installer'));
		//add_action('init', array('AwPqProductQuestions','aw_pq_start_session'), 1);
		add_action('init', array('AwPqFaqArticleAdmin','aw_faq_article_activate'));
		add_action('admin_init', array(get_called_class(),'aw_pq_product_questions_installer'));
		add_filter('set-screen-option', array('AwPqProductQuestions','aw_pq_faq_set_screen'), 10, 3);
		register_uninstall_hook(__FILE__, array(get_called_class(), 'aw_pq_product_questions_unistaller'));
		register_deactivation_hook( __FILE__ , array(get_called_class(),'aw_pq_product_questions_deactivated'));

		add_filter('woocommerce_product_tabs', array('AwPqProductQuestionsQAtab', 'aw_pq_product_questions_new_tab'), 100, 1);
		if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
			add_action('wp_head', 'aw_pq_product_question_update_product_post_meta');
		}
		/* Save Question and answer */
		add_action('admin_post_nopriv_save_question_answer', array('AwPqProductQuestionsQAtab', 'aw_pq_product_questions_save_data'));
		add_action('admin_post_save_question_answer', array('AwPqProductQuestionsQAtab', 'aw_pq_product_questions_save_data'));
		/* Save Question and answer */

		/* Edit & Update Question and answer */
		add_action('admin_post_nopriv_update_question_answer', array('AwPqProductQuestionsQAtab', 'aw_pq_product_questions_update_data'));
		add_action('admin_post_update_question_answer', array('AwPqProductQuestionsQAtab', 'aw_pq_product_questions_update_data'));
		/* Edit & Update Question and answer */		

		/* Admin Javascript files*/
		add_action('admin_enqueue_scripts', array(get_called_class(),'aw_pq_product_questions_admin_addScript'));
		add_action('wp_enqueue_scripts', array(get_called_class(),'aw_pq_product_questions_public_addScript'));

		/* Add option in advanced tab of product */
		add_action( 'woocommerce_product_options_advanced', array('AwPqProductQuestionsAdmin','aw_pq_advanced_product_options'));
		add_action( 'woocommerce_process_product_meta', array('AwPqProductQuestionsAdmin', 'aw_pq_save_advanced_product_options'), 10, 1);

		/* Add Custome Metabox for Q&A Detail*/
		add_action('admin_head', array(get_called_class(),'aw_pq_add_product_questions_meta_box'));
		add_action('comment_save_pre', array('AwPqProductQuestionsAdmin','aw_pq_save_comment_meta_box'));

		/* Add Custom menus admin side*/
		add_action('admin_menu', array(get_called_class(),'aw_pq_product_questions_menu'));

		if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
			add_action('admin_post_questions_save_setting_form', array('AwPqProductQuestionsAdmin','aw_pq_product_questions_save_setting_form'));
			add_action('admin_post_pq_save_email_templates_setting', array('AwPqProductQuestionsAdmin','aw_pq_save_email_templates_setting'));
			
		}		

		add_action('wp_ajax_aw_pq_product_question_like_dislike' , array('AwPqProductQuestionsQAtab','aw_pq_product_question_like_dislike'));
		add_action('wp_ajax_nopriv_aw_pq_product_question_like_dislike', array('AwPqProductQuestionsQAtab','aw_pq_product_question_like_dislike'));

		add_action('wp_ajax_aw_pq_mail_to_question_author' , 'aw_pq_mail_to_question_author');
		add_action('wp_ajax_nopriv_aw_pq_mail_to_question_author' , 'aw_pq_mail_to_question_author');

		add_action('comment_post', 'aw_pq_batc_admin_rply', 10, 3);

		add_action('transition_comment_status', 'aw_pq_batc_approve_comment_mail', 99, 3);

		add_filter('wp_kses_allowed_html', 'aw_pq_kses_filter_allowed_html', 10, 2);


		add_action('wp_ajax_aw_pq_check_enable_edit_comment', array('AwPqProductQuestionsQAtab','aw_pq_check_enable_edit_comment'));
		add_action('wp_ajax_nopriv_aw_pq_check_enable_edit_comment', array('AwPqProductQuestionsQAtab','aw_pq_check_enable_edit_comment'));

		if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
			add_action( 'wp_footer', array('AwPqProductQuestionsQAtab','aw_pq_active_QA_tab_product_page' ));
		}
		add_action('wp_ajax_aw_pq_default_lang_setting', array('AwPqProductQuestionsAdmin','aw_pq_default_lang_setting'));
		add_action('wp_ajax_nopriv_aw_pq_default_lang_setting', array('AwPqProductQuestionsAdmin','aw_pq_default_lang_setting'));

		add_action('template_redirect', array('AwPqProductQuestions','aw_pq_redirect_url'));
		add_filter('woocommerce_login_redirect', array('AwPqProductQuestions','aw_pq_login_redirect'), 90, 2);

		/*FAQ action and filters*/
		add_action('admin_post_faq_save_setting_form', array('AwPqFaqFirstAskQuestionsAdmin','aw_pq_faq_save_setting_form'));
		add_action('admin_post_faq_save_category_form', array('AwPqFaqFirstAskQuestionsAdmin','aw_pq_faq_save_category_form'));

		add_action('wp_ajax_aw_faq_category_image_delete', 'aw_faq_category_image_delete');
		add_action('wp_ajax_nopriv_aw_faq_category_image_delete', 'aw_faq_category_image_delete');

		add_action('wp_ajax_aw_faq_art_image_delete', 'aw_faq_art_image_delete');
		add_action('wp_ajax_nopriv_aw_faq_art_image_delete', 'aw_faq_art_image_delete');


		add_filter('wp_head', array('AwPqFaqFirstAskQuestionsPublic','aw_faq_custom_menu_item'));
		add_filter('wp_loaded', array('AwPqFaqFirstAskQuestionsPublic','aw_faq_enable_ask_form'));
		add_shortcode('aw_pq_faq_page', array('AwPqFaqFirstAskQuestionsPublic','aw_paq_faq_frontend_page')); 
		add_shortcode('aw_pq_faq_search_page', array('AwPqFaqFirstAskQuestionsPublic','aw_pq_faq_frontend_search_page')); 
		add_filter('get_the_archive_title', array('AwPqFaqFirstAskQuestionsPublic','aw_faq_custom_search'));
		add_filter('the_content', array('AwPqFaqFirstAskQuestionsPublic','aw_faq_like_and_dislike_html'));
		  
		add_filter( 'get_canonical_url', array('AwPqFaqFirstAskQuestionsPublic','aw_faq_canonical_url'), 10, 2 );
		add_action('wp_ajax_aw_faq_like_dislike' , array('AwPqFaqFirstAskQuestionsPublic','aw_faq_like_dislike'));
		add_action('wp_ajax_nopriv_aw_faq_like_dislike', array('AwPqFaqFirstAskQuestionsPublic','aw_faq_like_dislike'));

		/*custom post type article*/
		add_action('add_meta_boxes', array('AwPqFaqArticleAdmin','aw_article_meta_box'));
		add_filter('manage_faq_article_posts_columns', array('AwPqFaqArticleAdmin','aw_faq_article_table_head'));
		add_action('manage_faq_article_posts_custom_column', array('AwPqFaqArticleAdmin','aw_faq_article_table_columns'), 10, 2);
		add_filter('manage_edit-faq_article_sortable_columns', array('AwPqFaqArticleAdmin','aw_faq_article_column_register_sortable'));
		add_action('pre_get_posts', array('AwPqFaqArticleAdmin','aw_faq_article_sorting_orderby'));
		add_filter('bulk_actions-edit-faq_article', array('AwPqFaqArticleAdmin','aw_faq_article_register_my_bulk_actions'));
		add_filter('handle_bulk_actions-edit-faq_article', array('AwPqFaqArticleAdmin','aw_faq_article_my_bulk_action_handler'), 10, 3);

		if (isset($_GET['post_type'])) {
			if ('faq_article' == $_GET['post_type']) {
				add_filter('post_row_actions', array('AwPqFaqArticleAdmin','aw_faq_article_add_row_actions'), 10, 2);
				add_action('restrict_manage_posts', array('AwPqFaqArticleAdmin','aw_faq_display_cat_filter_taxonomy'));
				add_filter('parse_query', array('AwPqFaqArticleAdmin','aw_faq_process_cat_filter_query'));

			}
			
		}
		add_filter( 'comment_form_defaults', array('AwPqFaqFirstAskQuestionsPublic','aw_faq_comment_form_default'), 10, 1);
		add_filter('views_edit-faq_article', array('AwPqFaqArticleAdmin','aw_faq_article_modified_views_post_status'));
		add_filter('post_updated_messages', array('AwPqFaqArticleAdmin','aw_faq_article_allnotice_messages'));	
		add_action('admin_notices', array('AwPqFaqArticleAdmin','aw_faq_article_trashnotice'));	
		add_action('save_post', array('AwPqFaqArticleAdmin', 'aw_article_save_data'));
		add_filter('preprocess_comment', array('AwPqFaqFirstAskQuestionsPublic', 'aw_faq_save_comment_type_handler'));
		add_filter('init', array('AwPqFaqFirstAskQuestionsPublic','aw_faq_permanent_redirect'));
		/*FAQ action and filters******************/
	}

	public static function aw_pq_product_questions_installer() {

		if (is_admin()) {
			global $wpdb;
			$charset_collate 		= $wpdb->get_charset_collate();
			$db_aw_pq_cat_table	= $wpdb->prefix . 'aw_pq_faq_category_list';
			//Check to see if the table exists already, if not, then create it
			if ($wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ' , "{$wpdb->prefix}aw_pq_faq_category_list")) != $db_aw_pq_cat_table) {
				$sql = "CREATE TABLE {$wpdb->prefix}aw_pq_faq_category_list (
							`id` int(10) NOT NULL AUTO_INCREMENT,
							`category_name` text NOT NULL,
							`category_slug` text NOT NULL,
							`status` int(2) NOT NULL,
							`sort_order` int(11) NOT NULL,
							`date` datetime NOT NULL,
							`category_num_articles_page` int(11) NOT NULL,
							`category_icon_file` varchar(255) NOT NULL,
							`articles_list_icon_file` varchar(255) NOT NULL,
							`category_meta_title` text NOT NULL,
							`category_meta_description` text NOT NULL,
							PRIMARY KEY (`id`)
						);"	;
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta($sql);						
			}

			if (is_plugin_active( 'woocommerce/woocommerce.php')) {
				global $wpdb;
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

				$exist = get_option( 'product_question_by_aheadwork');
				if (!$exist) {
					add_option('rd_setting_qa_enable', 'yes');
					add_option('rd_setting_helpful_enable', 'yes');
					add_option('rd_setting_cookie_days', 30);
					update_option('AW_PQ_PRODUCT_QUESTION_VERSION', AW_PQ_PRODUCT_QUESTION_VERSION );
					/* Enable Individual product advanced Q&A setting */
					$post = array(
						'post_type' => 'product',
						'post_status' => 'publish',
						'posts_per_page' => -1,
					);
					$product_post = get_posts($post);
					foreach ($product_post as $prod) {
						update_post_meta($prod->ID, 'enable_q_and_a', 'yes');
					}

					$charset_collate 		= $wpdb->get_charset_collate();
					$db_aw_pq_codes_table	= $wpdb->prefix . 'aw_pq_email_templates';
					//Check to see if the table exists already, if not, then create it
					if ($wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ' , "{$wpdb->prefix}aw_pq_email_templates")) != $db_aw_pq_codes_table) {
						$sql = "CREATE TABLE {$wpdb->prefix}aw_pq_email_templates (
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
						$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}aw_pq_email_templates
						(email, email_type, recipients, active, subject, email_heading, additional_content)
						VALUES
						('Admin Email', 'text/html', %s , 1, 'New Question','New Question', 'Hello,{admin}
Someone has posted a question {link to the question on the backend} for {product_name}.'),
						('Customer Question Email', 'text/html', 'customer', 1, 'Your question is pending approval', 'Your question is pending approval', 'Hello, {customer_name}
Your question about {product_name} has been accepted for moderation.
We will let you know about any updates.'),
						('Customer Answer Email', 'text/html', 'customer', 1 ,'A new reply to your question about {product_name}', 'A new reply to your question about {product_name}', 'Hello, {customer_name}
Someone added a comment to your question thread: {A reply on the initial comment}
View it on the site:')", "{$admin_emails}"));
					}

					$charset_collate 			= $wpdb->get_charset_collate();
					$db_aw_pq_translation_table	= $wpdb->prefix . 'aw_pq_translations';
					if ($wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ' , "{$wpdb->prefix}aw_pq_translations")) != $db_aw_pq_translation_table) {
						$sql = "CREATE TABLE {$wpdb->prefix}aw_pq_translations (
									`id` int(10) NOT NULL AUTO_INCREMENT,
									`text` text CHARACTER SET utf8 COLLATE utf8_unicode_520_ci NOT NULL,
									`translation` text CHARACTER SET utf8 COLLATE utf8_unicode_520_ci NOT NULL,
									PRIMARY KEY (`id`)
								);"	;
						require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
						dbDelta($sql);
						aw_pq_insert_default_langauge_text();
					}
				update_option( 'product_question_by_aheadwork', 'completed' );
				}
			}
		}
	}

	public static function aw_pq_product_questions_deactivated() {
		global $wpdb;
		$db_comment_table = $wpdb->prefix . 'comments';
		$products = $wpdb->get_results($wpdb->prepare("SELECT comment_ID, comment_approved , comment_type  FROM {$wpdb->prefix}comments WHERE comment_type= %s ", 'q_and_a'));
		if (!empty($products)) {
			foreach ($products as $product) {
				$array = array('comment_approved' => $product->comment_approved . '_' . $product->comment_type);
				$wpdb->update($db_comment_table, $array, array('comment_ID'=>$product->comment_ID));
			}
		}
	}

	public static function aw_pq_product_questions_unistaller() {
		/* Perform required operations at time of plugin uninstallation */
		global $wpdb;

		$pluginDefinedOptions = array('rd_setting_qa_enable', 'rd_setting_helpful_enable', 'rd_setting_cookie_days', 'pq_allowtouser', 'pq_editin_minutes', 'pq_adminreply_color', 'pq_number_color'); // etc
		foreach ($pluginDefinedOptions as $optionName) {
			delete_option($optionName);
		}

		$comment_table = $wpdb->prefix . 'comments';
		$wpdb->query($wpdb->prepare("DELETE  FROM {$wpdb->prefix}comments WHERE `comment_type` = %s", 'q_and_a'));
		wp_reset_query();
		/* Remove all Individual product advanced Q&A enable setting */
		$post = array(
						'post_type' => 'product',
						'post_status' => 'publish',
						'posts_per_page' => -1,
					);
		$product_post = get_posts($post);
		foreach ($product_post as $prod) {
			delete_post_meta($prod->ID, 'enable_q_and_a');
		}

		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}commentmeta  WHERE `meta_key` = %s" , 'rd-not-helpful'));
		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}commentmeta  WHERE `meta_key` = %s" , 'rd-helpful'));
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aw_pq_email_templates");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aw_pq_translations");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aw_pq_faq_category_list");
		delete_option('product_question_by_aheadwork');
		delete_option('AW_PQ_PRODUCT_QUESTION_VERSION');
		delete_option('faq_after_search_page');
		delete_option('pq_csv_uploadtime');
		delete_option('pq_langaugecsv');
	}

	public static function aw_pq_product_questions_admin_addScript() {
		$path 	= parse_url(get_option('siteurl'), PHP_URL_PATH);
		$host 	= parse_url(get_option('siteurl'), PHP_URL_HOST);
		$nonce 	= wp_create_nonce('rdproductquestion_admin_nonce');
		$page 	= '';

		
		if (isset($_GET['page'])) {
			$page = sanitize_text_field($_GET['page']);
		}
		if (is_plugin_active( 'woocommerce/woocommerce.php')) {
			wp_register_style('rdproductquestionadmincss', plugins_url('/admin/css/aw-pq-product-questions.css', __FILE__ ), array(), '1.0' );
			wp_enqueue_style('rdproductquestionadmincss');

			wp_register_script('productquestionsadminjs', plugins_url('/admin/js/aw-pq-product-questions-admin.js', __FILE__ ), array(), '1.0' );

			$order_js_var = array('site_url' => get_option('siteurl'), 'ajax_url'=>admin_url('admin-ajax.php'), 'path' => $path, 'host' => $host, 'rd_pq_admin_nonce' => $nonce,'validemail_text'=>aw_pq_translate_text('Please, enter a valid email or email separated by comma.'),'required_email'=>aw_pq_translate_text('Email id is required'),'invalid_file_type'=>aw_pq_translate_text('Invalid file type !'),'default_language_applied'=>aw_pq_translate_text('Default language applied'));
			wp_localize_script('productquestionsadminjs', 'rd_admin_js_var', $order_js_var);

			wp_register_script('productquestionsadminjs', plugins_url('/admin/js/aw-pq-product-questions-admin.js', __FILE__ ), array(), '1.0' );
			wp_enqueue_script('productquestionsadminjs');

			wp_register_script('pq_color_palates_adminjs', plugins_url('/admin/js/aw-pq-jscolor.js' , __FILE__ ), array(), '1.0' );
			wp_enqueue_script('pq_color_palates_adminjs');
		}


		/*FAQ css and js*/
		wp_register_style('rdfirstaskquestionadmincss', plugins_url('/admin/css/aw-pq-faq-first-ask-questions.css', __FILE__ ), array(), '1.0' );
		wp_enqueue_style('rdfirstaskquestionadmincss');

		wp_register_script('firstaskquestionsadminjs', plugins_url('/admin/js/aw-pq-first-ask-questions-admin.js', __FILE__ ), array(), '1.0' );

		$post_type = get_post_type();
		$js_var = array('site_url' => get_option('siteurl'),'post_type' => $post_type);
		wp_localize_script('firstaskquestionsadminjs', 'js_var', $js_var);
		
		wp_register_script('firstaskquestionsadminjs', plugins_url('/admin/js/aw-pq-first-ask-questions-admin.js', __FILE__ ), array(), '1.0' );

		wp_enqueue_script('firstaskquestionsadminjs');
	}

	public static function aw_pq_product_questions_public_addScript() {
		add_filter( 'comments_clauses', 'aw_pq_filter_comments_clauses', 10, 1 );
		$path = parse_url(get_option('siteurl'), PHP_URL_PATH);
		$host = parse_url(get_option('siteurl'), PHP_URL_HOST);
		$nonce = wp_create_nonce('rdproductquestion_nonce');

		/** Add Plugin CSS and JS files Public Side**/
		wp_register_style('productquestionspubliccss', plugins_url('/public/css/aw-pq-product-questions-public.css', __FILE__ ), array(), '1.0' );
		wp_enqueue_style('productquestionspubliccss');	
		wp_register_script('productquestionspublicjs', plugins_url('/public/js/aw-pq-product-questions-public.js', __FILE__ ), array('jquery'), '1.0' );

		$js_var = array('site_url' => get_option('siteurl'), 'path' => $path, 'host' => $host, 'rd_qa_nonce' => $nonce,'ask_a_question' => aw_pq_translate_text('Ask a Question'), 'text_hide'=> aw_pq_translate_text('Hide'),'add_answer'=> aw_pq_translate_text('Add Answer'),'edit_answer'=> aw_pq_translate_text('Edit Answer'),'edit_question'=>aw_pq_translate_text('Edit Question'),'required_field'=>aw_pq_translate_text('Enter required field'),'valid_email'=>aw_pq_translate_text('Enter valid email.'),'vaild_author'=>aw_pq_translate_text('Enter valid author name.'),'valid_comment_text'=>aw_pq_translate_text('Enter valid comment text.'));
		wp_localize_script('productquestionspublicjs', 'js_qa_var', $js_var);
		wp_register_script('productquestionspublicjs', plugins_url('/public/js/aw-pq-product-questions-public.js', __FILE__ ), array('jquery'), '1.0' );
		wp_enqueue_script('productquestionspublicjs');



		/** Add faq CSS and JS files Public Side**/
		wp_register_style('firstaskquestionpubliccss', plugins_url('/public/css/aw-pq-faq-first-ask-question-public.css', __FILE__ ), array(), '1.0' );
		wp_enqueue_style('firstaskquestionpubliccss');

		wp_register_script('firstaskquestionpublicjs', plugins_url('/public/js/aw-pq-first-ask-question-public.js', __FILE__ ), array('jquery'), '1.0' );

		$post_type = get_post_type();
		$faqnonce = wp_create_nonce('firstaskquestion_nonce');
		$faq_js_var = array('site_url' => get_option('siteurl'),'post_type' => $post_type ,'rd_faq_nonce' => $faqnonce);

		wp_localize_script('firstaskquestionpublicjs', 'js_faq_var', $faq_js_var);
		wp_register_script('firstaskquestionpublicjs', plugins_url('/public/js/aw-pq-first-ask-question-public.js', __FILE__ ), array('jquery'), '1.0' );
		wp_enqueue_script('firstaskquestionpublicjs');
	}

	public static function aw_pq_add_product_questions_meta_box() {
		$multi_posts = array('comment');
		if (isset($_GET['c'])) {
			$comment_id = sanitize_text_field($_GET['c']);
			$comment 	= get_comment($comment_id);
			if ( ! empty( $comment )) {
				if ('q_and_a' === $comment->comment_type) {
					add_meta_box('helpful-pinned-meta-box', __('Q&A Details'), array('AwPqProductQuestionsAdmin' , 'aw_pq_comment_meta_box_callback'), 'comment', 'normal');
				}
			}
		}
	}

	public static function aw_pq_product_questions_menu() {

		add_menu_page(__('Product Questions And FAQ', 'main_menu'), __('Product Questions And FAQ', 'main_menu'), '', 'product-questions-and-faq', '', plugin_dir_url(__FILE__) . '/admin/images/questions.png', 75);
		if (is_plugin_active( 'woocommerce/woocommerce.php')) {
			add_submenu_page('product-questions-and-faq', 'Product Question Settings', 'Product Question Settings', 'manage_options', 'product-questions', array('AwPqProductQuestionsAdmin','aw_pq_product_question_setting'));
			add_submenu_page('', __('Product Question Emails'), '', 'manage_options', 'product-questions-emails', array('AwPqProductQuestionsAdmin' , 'aw_pq_admin_email_setting'));
		}
		add_submenu_page('product-questions-and-faq', 'FAQ Settings', 'FAQ Settings', 'manage_options', 'first_ask_question', array('AwPqFaqFirstAskQuestionsAdmin','aw_pq_first_ask_question_setting'));
		$hook = add_submenu_page('product-questions-and-faq', 'FAQ Categories', 'FAQ Categories', 'manage_options', 'faq_categories', array('AwPqFaqFirstAskQuestionsAdmin','aw_pq_faq_categories'));
		add_action( "load-$hook", array('AwPqProductQuestions','aw_pq_faq_add_screen_option'));
		add_submenu_page('', __('Faq category page'), '', 'manage_options', 'faq_category_page', array('AwPqFaqFirstAskQuestionsAdmin' , 'aw_pq_faq_new_category_page'));

		add_submenu_page( 'product-questions-and-faq', 'Custom Post Type Admin', 'FAQ Articles', 'manage_options', 'edit.php?post_type=faq_article');		
	}
		// start global session for saving the referer url
	/*public static function aw_pq_start_session() {
		if (empty(session_id())) {
			session_start();
		}
	}*/

	public static function aw_pq_redirect_url() {
		if (! is_user_logged_in()) {
			$_SESSION['referer_url'] = wp_get_referer() . '#tab-QA_tab';
		} else {
			session_destroy();
		}

	}

	//login redirect 
	public static function aw_pq_login_redirect( $redirect_to) {
		global $wpdb;
		$query_string = '';
		if (isset($_SESSION['referer_url'])) {
			$query_str_arr = explode('#', $_SESSION['referer_url']);
			if (!empty($query_str_arr)) {

				$url = rtrim($query_str_arr[0], '/');
				$path = parse_url($url, PHP_URL_PATH);
				$pathFragments = explode('/', $path);
				$end = end($pathFragments);				
				
				$post_id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE post_name = %s ", "{$end}" ));
				$post = get_page($post_id);
				if (!empty($post) && 'page'=== $post->post_type ) {
					$replace_to = '/my-account/';  
					$replace_by = $post->post_name;
					$redirect_to= preg_replace($replace_to, $replace_by, $redirect_to);

				} else {
					$query_string = $query_str_arr[1];
				}
			} else {
				wp_redirect($_SESSION['referer_url']);
			}
		}

		if (isset($_SESSION['referer_url']) && 'tab-QA_tab' == $query_string) {
			if (isset($_SESSION['referer_url'])) {
				wp_redirect($_SESSION['referer_url']);
			} else {
				wp_redirect(home_url());
			}
		} else {
			return $redirect_to;
		}
	}


	public static function aw_pq_faq_add_screen_option() {
	$option = 'per_page';
		$args = array(
			'label' => 'Number of items per page:',
			'default' => 20,
			'option' => 'customers_per_page'
		);
		add_screen_option( $option, $args );

		$table_bal = new AwPqFaqCategoryList();
	}

	public static function aw_pq_faq_set_screen( $status, $option, $value) { 
		if ('customers_per_page' == $option) {
			return $value;
		}	
		return $status;
	}
}



function aw_pq_get_questions_count( $product_id) {
	global $wpdb;
	$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}comments WHERE comment_post_ID = %d AND comment_parent = %d AND comment_type = %s AND comment_approved = %d ", "{$product_id}", 0, 'q_and_a', 1)); 
	return $total_items;
}
 
function aw_pq_get_replyed_answer( $product_id, $question_id = '') {
	global $wpdb;
	$total_questions = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}comments WHERE comment_post_ID = %5d  AND comment_type = %s AND comment_parent = %5d ", "{$product_id}", 'q_and_a', "{$question_id}" ) );
	return $total_questions;
}	
function aw_pq_set_guest_cookies( $author, $email) {
		global $wpdb;
		$set_guest_author  = 'guest_author';
		$set_guest_email   = 'guest_email';
		$path = '/';
		$host = parse_url(get_option('siteurl'), PHP_URL_HOST);
		setcookie($set_guest_author, $author, 0, $path, $host);
		setcookie($set_guest_email, $email, 0, $path, $host);
}
function aw_pq_delete_guest_cookies( $author, $email) {
		global $wpdb;
		$set_guest_author  = 'guest_author';
		$set_guest_email   = 'guest_email';
		$path = '/';
		$host = parse_url(get_option('siteurl'), PHP_URL_HOST);
		setcookie($set_guest_author, '', time() - 3600, $path, $host);
		setcookie($set_guest_email, '', time() - 3600, $path, $host);
}
function aw_pq_get_question_reply_detail_row( $product_id, $comment_id) {
	global $wpdb;
	$comment_detail = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}comments WHERE comment_post_ID = %5d  AND comment_type = %s AND comment_ID = %5d ", "{$product_id}", 'q_and_a', "{$comment_id}" ) );
	return $comment_detail;
}
function aw_pq_get_total_reviews_count() {
	return get_comments(array(
		'status'   => 'approve',
		'post_status' => 'publish',
		'post_type'   => 'product',
		'count' => true
	));
}

function aw_pq_get_question_author_detail( $comment_id) {
	global $wpdb;
	$comment_row = $wpdb->get_row( $wpdb->prepare( "SELECT comment_ID, comment_parent , comment_author_email, comment_author FROM {$wpdb->prefix}comments WHERE comment_ID = %5d AND comment_approved = %d AND comment_type = %s ", "{$comment_id}", 1 , 'q_and_a' ), ARRAY_A );
	if (!empty($comment_row)) {
		if (0 == $comment_row['comment_parent']) {
			return json_encode($comment_row);
		} else {
			return aw_pq_get_question_author_detail($comment_row['comment_parent']);
		}	
	}
}

function aw_pq_get_count_reply_depth_of_question( $comment_id, $count = 0) {
	global $wpdb;
	$comment_row = $wpdb->get_row( $wpdb->prepare( "SELECT comment_ID, comment_parent , comment_author_email, comment_author FROM {$wpdb->prefix}comments WHERE comment_parent = %5d AND comment_approved = %d AND comment_type = %s ", "{$comment_id}", 1 , 'q_and_a' ), ARRAY_A );
	if (!empty($comment_row)) {
		$count++;
		return aw_pq_get_count_reply_depth_of_question($comment_row['comment_ID'], $count); 	
	} else {
		return $count;
	}
}

/* Mail to question author that he get reply of question */
function aw_pq_mail_to_question_author( $product_id = '', $comment_parent_id = '', $comment_text = '', $email = '', $comment_id = '') {
	if ( '0' == $comment_parent_id ) {
		return;
	}
	$admin_mail_id = '';
	$goformail = 0;

	$admin_mail_id = get_option('admin_email');

	$question_detail = json_decode(aw_pq_get_question_author_detail($comment_parent_id));

	if (empty($question_detail->comment_author_email) || empty($question_detail->comment_author)) {
		return;	
	}

	if ($email != $question_detail->comment_author_email) {
		$goformail = 1;
	} elseif ($admin_mail_id == $question_detail->comment_author_email) {
		$goformail = 1;
	}

	if ( 1 == $goformail) {
		global $wpdb;
		$settings = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pq_email_templates WHERE email = %s", 'Customer Answer Email')); 

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

		$product      	= wc_get_product( $product_id );
		$product_name 	= $product->get_title();
		$url_part		= '+tab-QA_tab';
		$url 			= '<a href="' . get_permalink($product_id) . '#div-comment-' . $comment_parent_id . $url_part . '" target="_blank" rel="nofollow">' . get_permalink($product_id) . '#div-comment-' . $comment_parent_id . $url_part . '</a>';

		$user_email 	= $question_detail->comment_author_email;

		$email_subject 	= 'A new reply to your question about ' . $product_name;
		$email_heading 	= 'A new reply to your question about ' . $product_name;
		$additional_text = 'Hello,<strong>' . $question_detail->comment_author . '</strong><br/>Someone added a comment to your question thread: "' . $comment_text . '"<br/>View it on the site:' . $url;
		$from_name 		= get_option('woocommerce_email_from_name');
		$from_email		= get_option('woocommerce_email_from_address');
		$header_image 	= get_option('woocommerce_email_header_image');
		$footer_text 	= get_option('woocommerce_email_footer_text'); 
		$basecolor 	 	= get_option('woocommerce_email_base_color'); 
		$backgroundcolor= get_option('woocommerce_email_background_color'); 
		$body_backgroundcolor 	= get_option('woocommerce_email_body_background_color'); 	    
		$text_color	 			= get_option('woocommerce_email_text_color');  
		$footer_text 			= aw_pq_placeholders_replace($footer_text);

		if (!empty($heading)) {
			$email_heading 	= $heading;
		}

		if (!empty($subject)) {
			$email_subject 	= $subject;
		}
		
		if (!empty($additional_content)) {
			$additional_text = preg_replace('/{customer_name}/', '<b>{customer_name}</b> <br>', $additional_content);
			$additional_text = preg_replace('/{customer_name}/', $question_detail->comment_author, $additional_text);

			$additional_text = preg_replace('/{A reply on the initial comment}/', '{A reply on the initial comment}<br>', $additional_text);
			$additional_text = preg_replace('/{A reply on the initial comment}/', '"' . $comment_text . '"', $additional_text);
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
			$comment_author = $question_detail->comment_author;
			$users 			= get_users( array( 'role' => 'Administrator' ) );
			foreach ( $users as $user ) {
				$user_data[]= $user->data->user_login;
			} 
			$admin_name 	= implode(',', $user_data);
			$to_replace 	= array('{site_title}','{site_url}','{product_name}','{admin}','{customer_name}','{A reply on the initial comment}','{order_number}','{order_date}');
			$by_replace 	=array($site_title,$site_url,$product_name,$admin_name,$comment_author,'','','');
			$message 		= str_replace($to_replace, $by_replace, $message);
			$email_subject 	= str_replace($to_replace, $by_replace, $email_subject);
			ob_end_clean();
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			add_filter( 'wp_mail_from', 'aw_pq_mail_from' );
			add_filter( 'wp_mail_from_name', 'aw_pq_from_name' );
			
			if ('text/plain' == $email_type) {
				$message= preg_replace('/(<[^>]+) style=".*?"/i', '$1', $message);
				$to_replace = array('<b>','</b>','<h1>','</h1>','<strong>','</strong>');
				$message = str_replace($to_replace, '', $message);
				$message= preg_replace('/<b>/', '$1', $message);
			}
			wp_mail($user_email, $email_subject, $message, $headers);
			update_comment_meta($comment_id, 'rd-mail-sent', '1');
			remove_filter( 'wp_mail_from', 'aw_pq_mail_from' );
			remove_filter( 'wp_mail_from_name', 'aw_pq_from_name' );
	}
}

function aw_pq_mail_to_customer( $product_id = '', $author = '', $comment_text = '', $email = '', $comment_id = '') {

	if (!empty($email) && !empty($author)) {
		global $wpdb;
		$settings = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pq_email_templates WHERE email = %s", 'Customer Question Email')); 

		if (!empty($settings)) {
			foreach ($settings as $value) {
				$subject 			= $value->subject;
				$additional_content = $value->additional_content;
				$email_heading 		= $value->email_heading;
				$active 			= $value->active;
				$email_type 		= $value->email_type;
			}
		}		
		if (0 == $active) {
			return;
		}

		$product      	= wc_get_product( $product_id );
		$product_name 	= $product->get_title();
		$user_email 	= $email;
		$email_subject 	= 'Your question is pending approval';
		$additional_text = 'Hello,<strong>' . $author . '</strong><br/>Your question about <strong>' . $product_name . '</strong>&nbsp; has been accepted for moderation.<br/>We will let you know about any updates.';

		$from_name 		= get_option('woocommerce_email_from_name');
		$from_email		= get_option('woocommerce_email_from_address');
		$header_image 	= get_option('woocommerce_email_header_image');
		$footer_text 	= get_option('woocommerce_email_footer_text'); 
		$basecolor 	 	= get_option('woocommerce_email_base_color'); 
		$backgroundcolor= get_option('woocommerce_email_background_color'); 
		$body_backgroundcolor 	= get_option('woocommerce_email_body_background_color'); 	    
		$text_color	 			= get_option('woocommerce_email_text_color');  
		$footer_text 			= aw_pq_placeholders_replace($footer_text);

		if (empty($email_heading)) {
			$email_heading = 'Your question is pending approval';
		}
		if (!empty($subject)) {	
			$email_subject 		= $subject;
		}
		if (!empty($additional_content)) {
			$additional_text = preg_replace('/{customer_name}/', '<b>{customer_name}</b> <br>', $additional_content);
			$additional_text = preg_replace('/{customer_name}/', $author, $additional_text);

			$additional_text = preg_replace('/{product_name}/', '<b>{product_name}</b>', $additional_text);
			$additional_text = preg_replace('/{product_name}/', $product_name, $additional_text);
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
			$message = ob_get_contents();
			$site_title 	= get_bloginfo( 'name', 'display' );
			$site_url 	= home_url();
			$comment_author = $author;
			$users = get_users( array( 'role' => 'Administrator' ) );
			foreach ( $users as $user ) {
				$user_data[] = $user->data->user_login;
			} 
			$admin_name = implode(',', $user_data);
			$to_replace = array('{site_title}','{site_url}','{product_name}','{admin}','{customer_name}','{A reply on the initial comment}','{order_number}','{order_date}');
			$by_replace =array($site_title,$site_url,$product_name,$admin_name,$comment_author,'','','');
			$message = str_replace($to_replace, $by_replace, $message);
			$email_subject = str_replace($to_replace, $by_replace, $email_subject);
			ob_end_clean();
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			add_filter( 'wp_mail_from', 'aw_pq_mail_from' );
			add_filter( 'wp_mail_from_name', 'aw_pq_from_name' );
			if ('text/plain' == $email_type) {
				$message= preg_replace('/(<[^>]+) style=".*?"/i', '$1', $message);
				$to_replace = array('<b>','</b>','<h1>','</h1>','<strong>','</strong>');
				$message = str_replace($to_replace, '', $message);
				$message= preg_replace('/<b>/', '$1', $message);
			}
			wp_mail($user_email, $email_subject, $message, $headers);
			update_comment_meta($comment_id, 'rd-mail-sent', '1');
			remove_filter( 'wp_mail_from', 'aw_pq_mail_from' );
			remove_filter( 'wp_mail_from_name', 'aw_pq_from_name' );
	}
}

function aw_pq_mail_to_admin( $product_id = '', $comment_parent_id = '', $comment_text = '', $email = '', $comment_id = '') {
	global $wpdb;
	$settings = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pq_email_templates WHERE email = %s", 'Admin Email')); 

	if (!empty($settings)) {
		foreach ($settings as $value) {
			$email_recipients 	= $value->recipients;
			$subject 			= $value->subject;
			$email_heading 		= $value->email_heading;
			$additional_content = $value->additional_content;
			$active 			= $value->active;
			$email_type 		= $value->email_type;	
		}
	}
	if (0 == $active) {
		return;
	}

		$product      	= wc_get_product( $product_id );
		$product_name 	= $product->get_title();
		$url_part		= admin_url('comment.php?action=editcomment&c=' . $comment_id);
		$url 			=  '&nbsp;<a href="' . $url_part . '" target="_blank" rel="nofollow">' . $url_part . '</a>&nbsp;';
		$email_subject 	= 'New Question - ' . $product_name;
		

	if (empty($email_heading)) {
		$email_heading = 'New Question';
	}
	if (!empty($subject)) {
		$email_subject 	= preg_replace('/New Question /', $subject, $email_subject);	
	}

	if (!empty($email_recipients)) {
		$user_data[0]['admin_email'] 	= $email_recipients;
		$users = explode(',', $email_recipients);
		if (!empty($users)) {
			$user_data = array();
			foreach ( $users as $email ) {
				$userdata = get_user_by('email', $email);
				if (!empty($userdata)) {
					$name = ucfirst($userdata->first_name) . ' ' . ucfirst($userdata->last_name);
				} else {
					$name = 'Admin';
				}
				$user_data[] = array('admin_email'=>$email,'admin_name' => $name);
			}
		} else {
			$userdata = get_user_by('email', $email);
			if (!empty($userdata)) {
				$user_data[0]['admin_name'] = ucfirst($userdata->first_name) . ' ' . ucfirst($userdata->last_name);
			} else {
				$user_data[0]['admin_name'] = 'Admin';
			}
			$admin_name = $user_data[0]['admin_name'];
		}
	} else {
		$users = get_users( array( 'role' => 'Administrator' ) );
		foreach ( $users as $user ) {
			$user_name = get_user_by('email', $user->data->user_email);
			if (!empty($user_name) && !empty($userdata)) {
				$name = ucfirst($userdata->first_name) . ' ' . ucfirst($userdata->last_name);
			} else {
				$name = 'Admin';
			}
			$user_data[] = array('admin_email'=>$user->data->user_email,'admin_name' => $name);
		} 
	}
	foreach ( $user_data as $user ) { 
		 $admin_name 	= $user['admin_name'];
		 $email 		= $user['admin_email'];
		 $additional_text = 'Hello, <strong>' . $admin_name . '</strong> <br /> Someone has posted a question' . $url . 'for &nbsp;<strong>' . $product_name . '.</strong>';
		if (!empty($additional_content)) {
			$additional_text = preg_replace('/{admin}/', '<b>{admin}</b> <br>', $additional_content);
			$additional_text = preg_replace('/{admin}/', $admin_name, $additional_text);

			$additional_text = preg_replace('/{link to the question on the backend}/', $url, $additional_text);
			$additional_text = preg_replace('/{product_name}/', '<b>{product_name}</b>', $additional_text);
			$additional_text = preg_replace('/{product_name}/', $product_name, $additional_text);
		}

		$from_name 		= get_option('woocommerce_email_from_name');
		$from_email		= get_option('woocommerce_email_from_address');
		$header_image 	= get_option('woocommerce_email_header_image');
		$footer_text 	= get_option('woocommerce_email_footer_text'); 
		$basecolor 	 	= get_option('woocommerce_email_base_color'); 
		$backgroundcolor= get_option('woocommerce_email_background_color'); 
		$body_backgroundcolor 	= get_option('woocommerce_email_body_background_color'); 	    
		$text_color	 			= get_option('woocommerce_email_text_color');  
		$footer_text 			= aw_pq_placeholders_replace($footer_text);
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
			$comment_author = get_comment_author($comment_id);

			$to_replace = array('{site_title}','{site_url}','{product_name}','{admin}','{customer_name}','{A reply on the initial comment}','{order_number}','{order_date}');
			$by_replace =array($site_title,$site_url,$product_name,$admin_name,$comment_author,'','','');
			$message = str_replace($to_replace, $by_replace, $message);
			$email_subject = str_replace($to_replace, $by_replace, $email_subject);
			ob_end_clean();
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			add_filter( 'wp_mail_from', 'aw_pq_mail_from' );
			add_filter( 'wp_mail_from_name', 'aw_pq_from_name' );
			if ('text/plain' == $email_type) {
				$message= preg_replace('/(<[^>]+) style=".*?"/i', '$1', $message);
				$to_replace = array('<b>','</b>','<h1>','</h1>','<strong>','</strong>');
				$message = str_replace($to_replace, '', $message);
				$message= preg_replace('/<b>/', '$1', $message);
			}			 
			wp_mail($email, $email_subject, $message, $headers);
			update_comment_meta($comment_id, 'rd-mail-sent', '1');
			remove_filter( 'wp_mail_from', 'aw_pq_mail_from' );
			remove_filter( 'wp_mail_from_name', 'aw_pq_from_name' );
	}
}

function aw_pq_mail_from( $email ) {
	$from_email = get_option('woocommerce_email_from_address');
	return $from_email;
}

function aw_pq_from_name( $name ) {
	$from_name = get_option('woocommerce_email_from_name');
	return $from_name;
}

function aw_pq_placeholders_replace( $string ) {
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

function aw_pq_kses_filter_allowed_html( $allowed, $context) {
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
	$allowed['input']['readonly']	= array();
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

function aw_pq_get_the_user_ip() {
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

function aw_pq_batc_admin_rply( $new_comment, $comment_approved, $commentdata ) {

	if (isset($_POST['_ajax_nonce-replyto-comment'])) {
		$ajax_nonce_replyto_comment = sanitize_text_field($_POST['_ajax_nonce-replyto-comment']);
	} else {
		return;
	}

	if ( !wp_verify_nonce( $ajax_nonce_replyto_comment, 'replyto-comment')) {
		wp_die('Our Site is protected - admin reply form');
	}

	$action 		= '';
	$comment_type 	= '';

	if ( isset($_POST['action']) ) {
		$action = sanitize_text_field($_POST['action']);
	}

	if ( isset($_POST['comment_type']) ) {
		$comment_type = sanitize_text_field($_POST['comment_type']);
	}

	if ( isset($_POST['comment_post_ID']) ) {
		$comment_post_ID = sanitize_text_field($_POST['comment_post_ID']);
	}

	if ( isset($_POST['comment_ID']) ) {
		$comment_parent_id = sanitize_text_field($_POST['comment_ID']);
	}

	if ( isset($_POST['content']) ) {
		$content = sanitize_text_field($_POST['content']);
	}

	$comment_id = $new_comment;

	if ( 'replyto-comment' == $action && 'q_and_a' == $comment_type && '' != $new_comment ) {
		add_comment_meta($new_comment, 'rd-not-helpful', 0);
		add_comment_meta($new_comment, 'rd-helpful', 0);
		$replier_email = get_comment_author_email( $new_comment );
		aw_pq_mail_to_question_author($comment_post_ID, $comment_parent_id, $content, $replier_email, $comment_id);
	}
}

function aw_pq_batc_approve_comment_mail( $new_status, $old_status, $comment ) {

	if ($old_status != $new_status) {

		if ('approved' == $new_status) {

			$comment_post_ID 	= $comment->comment_post_ID;
			$comment_parent_id 	= $comment->comment_parent;
			$content 			= $comment->comment_content;
			$replier_email		= $comment->comment_author_email;
			$comment_id			= $comment->comment_ID;

			$get_mail_status 	= get_comment_meta($comment_id, 'rd-mail-sent', true);

			if ('0' == $get_mail_status) {
				aw_pq_mail_to_question_author($comment_post_ID, $comment_parent_id, $content, $replier_email, $comment_id);
			}
		}
	}
}

function aw_pq_get_all_questions_count( $product_id, $unapproved_id, $user_id) {
	
	global $wpdb;
	$comment_in = array();
	$key 		= -1;

	$ids = $wpdb->get_results($wpdb->prepare("SELECT comment_ID FROM {$wpdb->prefix}comments WHERE comment_post_ID = %d AND comment_parent = %d AND comment_type = %s AND comment_approved = %d AND comment_approved != %s AND comment_approved != %s", "{$product_id}", 0, 'q_and_a', 1, 'trash', 'spam')); 

	if (!empty($ids)) {
		foreach ($ids as $key=> $id) {
			$comment_in[] = $id->comment_ID;
		}	
	}
	$userdata = get_user_by('id', $user_id);
	 
	if (empty($userdata) && 0 != $unapproved_id ) {
		$parent = aw_pq_is_question($product_id, $unapproved_id);
		if (0 == $parent) {
			$comment_in[$key+1] = $unapproved_id;	
		}
	} else {
		$ids = $wpdb->get_results($wpdb->prepare("SELECT comment_ID FROM {$wpdb->prefix}comments WHERE comment_post_ID = %d AND comment_parent = %d AND comment_type = %s AND (comment_approved != %s AND comment_approved != %s ) AND user_id = %d ", "{$product_id}", 0, 'q_and_a', 'trash', 'spam', "{$user_id}" ));
		$unapprove_arr = array(); 
		if (!empty($ids)) {
			foreach ($ids as $key=> $id) {
				$unapprove_arr[] = $id->comment_ID;
			}	
			$comment_in = array_merge($comment_in , $unapprove_arr) ;
		}
	}
	$comment_in = array_unique($comment_in);
	return count($comment_in); 
}

function aw_pq_filter_comments_clauses( $array ) {
	$array['where'] .= ( $array['where'] ? ' AND ' : '' ) . " comment_type != 'q_and_a' ";
	return $array;
};

function aw_pq_get_all_unapproved_question( $product_id) {
	global $wpdb;
	$question_id = $wpdb->get_results($wpdb->prepare("SELECT comment_ID , user_id FROM {$wpdb->prefix}comments WHERE comment_post_ID = %d AND comment_type = %s AND comment_approved = %d ", "{$product_id}", 'q_and_a', 0));
	return $question_id;
}

function aw_pq_is_question( $product_id, $question_id ) {
	global $wpdb;
	$parent_id = $wpdb->get_var($wpdb->prepare("SELECT comment_parent FROM {$wpdb->prefix}comments WHERE comment_post_ID = %d AND comment_type = %s AND comment_ID = %d ", "{$product_id}", 'q_and_a' , "{$question_id}")); 
	return $parent_id;
}

function aw_pq_product_question_update_product_post_meta() {
	global $wpdb;
	$id = get_the_ID();

	if (is_product()) {
		$product_id = $id;

		$products = $wpdb->get_results($wpdb->prepare("SELECT comment_post_ID , COUNT(*) AS review_count FROM {$wpdb->prefix}comments WHERE comment_type= %s AND comment_approved = %d AND comment_post_ID = %d GROUP BY comment_post_ID  ", 'review', 1, $product_id));

		if (!empty($products)) {
			foreach ($products as $product) {
				update_post_meta($product->comment_post_ID , '_wc_review_count', $product->review_count);
			}
		} else {
			update_post_meta($product_id , '_wc_review_count', 0);		
		}
	}
}

function aw_pq_product_question_get_trashed_comment_child( $product_id) {
	global $wpdb;
	$childrens = array();

	$trashed_id = $wpdb->get_results( $wpdb->prepare( "SELECT comment_ID  FROM {$wpdb->prefix}comments WHERE comment_post_ID = %5d AND comment_approved = %s OR comment_approved = %s  OR comment_approved = %s AND comment_type = %s ", "{$product_id}", 'trash', 'spam', '0', 'q_and_a' ), ARRAY_A );

	if (!empty($trashed_id)) {

		foreach ($trashed_id as $id ) {
			$args = array(
			  'parent' => $id,
			'hierarchical' => true,
			);
			$replies = get_comments($args);
			if (!empty($replies)) {
				foreach ( $replies as $reply ) {
					$childrens[] =   $reply->comment_ID;
				}
			}
		}
	}
	return $childrens;
}

function aw_pq_whitelist_question_reply( $email) {
	global $wpdb;
	$approved = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}comments WHERE comment_author_email = %s AND comment_type = %s AND comment_approved = %d ", "{$email}", 'q_and_a' , 1));
	return $approved;
}

function aw_pq_get_email_template_setting_results() {
	global $wpdb;
	$emails_template = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pq_email_templates WHERE 1 = %d ", 1 ) );
	return $emails_template;
}

function aw_pq_get_email_template_setting_row( $id) {
	global $wpdb;
	$emails_template = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pq_email_templates WHERE id = %d ", "{$id}") );
	return $emails_template;
}

function aw_pq_login_redirect( $redirect_to, $request, $user ) {
	return $redirect_to;
}
add_filter( 'login_redirect', 'aw_pq_login_redirect', 10, 3 );

function aw_pq_translate_text( $text) {
	global $wpdb;
	$translated_text = $wpdb->get_var($wpdb->prepare("SELECT `translation` FROM {$wpdb->prefix}aw_pq_translations WHERE text = %s ", "{$text}"));

	$translated_text = trim($translated_text);

	if (!empty($translated_text) || '' != $translated_text) {
		return wp_kses_post($translated_text);
	} else {
		return wp_kses_post($text);
	}
}

function aw_pq_get_lang_translation_data( $original_text) {
	global $wpdb;
	$data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pq_translations WHERE `text` = %s", "{$original_text}" ) );
	return $data;
}

function aw_pq_update_lang_translation_data( $original_text, $modified_text) {
	global $wpdb;
	//$table_name = $wpdb->prefix . 'aw_pq_translations';
	$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aw_pq_translations SET `translation`=%s WHERE `text` = %s", "{$modified_text}", "{$original_text}"));
}

function aw_pq_faq_category_row( $id) {
	global $wpdb;
	$single_category = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pq_faq_category_list WHERE id = %d ", "{$id}") );
	return $single_category;
}

function aw_faq_category_image_delete() {
	global $wpdb;
	if (isset($_REQUEST['postid'])) {
		$post_id 	= wp_kses_post($_REQUEST['postid']);
		$cat_data = aw_pq_faq_category_row($post_id);		
		$faq_category_icon_file  	= $cat_data->category_icon_file;
		//$data 		= get_post_meta($post_id, 'popup_pro_subscribe_design', true);
		if (isset($faq_category_icon_file)) {
			$path = wp_get_upload_dir();
			$imagepath = explode('uploads', $faq_category_icon_file) ;
			if (isset($path['basedir']) && isset($imagepath[1])) {
				$fullpath  = $path['basedir'] . $imagepath[1];	
				if (file_exists($fullpath)) {
					if (unlink($fullpath)) {
						$faq_category_icon_file = '';
						//update_post_meta($post_id, 'popup_pro_subscribe_design', array_filter($cat_data));
						$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aw_pq_faq_category_list SET 	category_icon_file =''  WHERE id = %s" , "{$post_id}"));
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
function aw_faq_art_image_delete() {
	global $wpdb;

	if (isset($_REQUEST['postid'])) {
		$post_id 	= wp_kses_post($_REQUEST['postid']);
		$cat_data = aw_pq_faq_category_row($post_id);		
		$faq_article_icon_file  	= $cat_data->articles_list_icon_file;
		//$data 		= get_post_meta($post_id, 'popup_pro_subscribe_design', true);
		if (isset($faq_article_icon_file)) {
			$path = wp_get_upload_dir();
			$imagepath = explode('uploads', $faq_article_icon_file) ;
			if (isset($path['basedir']) && isset($imagepath[1])) {
				$fullpath  = $path['basedir'] . $imagepath[1];	
				if (file_exists($fullpath)) {
					if (unlink($fullpath)) {
						$faq_article_icon_file = '';
						//update_post_meta($post_id, 'popup_pro_subscribe_design', array_filter($cat_data));
						$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aw_pq_faq_category_list SET 	articles_list_icon_file =''  WHERE id = %s" , "{$post_id}"));
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
function aw_pq_insert_default_langauge_text() {
	global $wpdb;
	$wpdb->query("INSERT INTO {$wpdb->prefix}aw_pq_translations ( `text`, `translation`) VALUES
		('Product questions configuration setting updated', 'Product questions configuration setting updated'),
		('Invalid file type or Invalid content type','Invalid file type or Invalid content type'),
		('Product Questions','Product Questions'),
		('General Settings', 'General Settings'),
		('Enable Q&A', 'Enable Q&A'),
		('This option may be overridden for individual products', 'This option may be overridden for individual products'),
		('Enable helpfulness voting', 'Enable helpfulness voting'),
		('Helpfulness cookie lifetime days', 'Helpfulness cookie lifetime days'),
		('Who can ask questions from product page', 'Who can ask questions from product page'),
		('Anyone', 'Anyone'),
		('Logged In Users', 'Logged In Users'),
		('Post can be edited by customer within x minutes', 'Post can be edited by customer within x minutes'),
		('If left empty or zero - no limit', 'If left empty or zero - no limit'),
		('Language Translation','Language Translation'),
		('Click here to download CSV file','Click here to download CSV file'),
		('Download file and then uploaded once again after corrections to rename options or make their translation via a CSV file.','Download file and then uploaded once again after corrections to rename options or make their translation via a CSV file.'),
		('Make changes in second column of CSV file, in text you want to replace.','Make changes in second column of CSV file, in text you want to replace.'),
		('Language Translation you added','Language Translation you added'),
		('Click here to download your CSV file','Click here to download your CSV file'),
		('To apply default language','To apply default language'),
		('delete this file','delete this file'),
		('Click here','Click here'),
		('Added On','Added On'),
		('Default language applied','Default language applied'),
		('Invalid file type !','Invalid file type !'),
		('Save', 'Save'),
		('Interface Settings', 'Interface Settings'),
		('Highlight admin reply', 'Highlight admin reply'),
		('Highlight Q & A number', 'Highlight Q & A number'),
		('If the number is greater than 0', 'If the number is greater than 0'),
		('Select color', 'Select color'),
		('Emails', 'Emails'),
		('Status', 'Status'),
		('Email', 'Email'),
		('Content', 'Content'),
		('Recipients', 'Recipients'),
		('Manage', 'Manage'),
		('Add Answer', 'Add Answer'),
		('Admin Email', 'Admin Email'),
		('Email templates setting updated', 'Email templates setting updated'),
		('Customer Question Email', 'Customer Question Email'),
		('Customer Answer Email', 'Customer Answer Email'),
		('Customer', 'Customer'),
		('A new question is submitted on the storefront and an email notification is sent to Admin', 'A new question is submitted on the storefront and an email notification is sent to Admin'),
		('Enable/Disable', 'Enable/Disable'),
		('Enabled','Enabled'),
		('Disabled', 'Disabled'),
		('Enable this email notification', 'Enable this email notification'),
		('Recipient(s)', 'Recipient(s)'),
		('Subject', 'Subject'),
		('Email heading', 'Email heading'),
		('Additional content', 'Additional content'),
		('When a new question is waiting for approval and an email notification is sent to the customer', 'When a new question is waiting for approval and an email notification is sent to the customer'),
		('When question answered by (admin/user) + link to the question (or product page) and an email notification is sent to the customer','When question answered by (admin/user) + link to the question (or product page) and an email notification is sent to the customer'),
		('Email type', 'Email type'),
		('Save changes', 'Save changes'),
		('Q&A Details', 'Q&A Details'),
		('Helpful', 'Helpful'),
		('Not helpful', 'Not helpful'),
		('Pinned to Top', 'Pinned to Top'),
		('Enable to display question & answer on product page.', 'Enable to display question & answer on product page.'),
		('HTML','HTML'),
		('Plain text','Plain text'),
		('Q&A', 'Q&A'),
		('Be the first to ask a question about', 'Be the first to ask a question about'),
		('Ask a Question', 'Ask a Question'),
		('Hide', 'Hide'),
		('Name', 'Name'),
		('Save my name, email in this browser for the next time I question.', 'Save my name, email in this browser for the next time I question.'),
		('Send Question', 'Send Question'),
		('Questions about', 'Questions about'),
		('Edit Question', 'Edit Question'),
		('Update Question', 'Update Question'),
		('Your question is waiting for the answer.', 'Your question is waiting for the answer.'),
		('Send Answer', 'Send Answer'),
		('Your answer is awaiting moderation.', 'Your answer is awaiting moderation.'),
		('Edit Answer', 'Edit Answer'),
		('Update Answer', 'Update Answer'),
		('Please','Please'),
		('log in','log in'),
		('to post an answer','to post an answer'),
		('to post a question','to post a question'),
		('Enter required field','Enter required field'),
		('Enter valid email.','Enter valid email.'),
		('Enter valid author name.','Enter valid author name.'),
		('Enter valid comment text.','Enter valid comment text.'),
		('Time period in which your question can be edited is expired.','Time period in which your question can be edited is expired.'),
		('Time period in which your answer can be edited is expired.','Time period in which your answer can be edited is expired.'),
		('Please, enter a valid email or email separated by comma.', 'Please, enter a valid email or email separated by comma.'),
		('Email id is required', 'Email id is required')");
}
  
