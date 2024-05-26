<?php
/**
 * Plugin Name: Company Accounts By Aheadworks
 * Plugin URI: https://www.aheadworks.com/
 * Description: Manage corporate accounts from the admin panel and enable customers to create companies from the storefront.
 * Author: Aheadworks
 * Author URI: https://www.aheadworks.com/
 * Version: 1.0.0
 * Woo: 
 * Text Domain: company-accounts-by-aheadworks
 *
 * @package company-accounts-by-aheadworks
 *
 * Requires at least: 5.2.9
 * Tested up to: 5.6
 * WC requires at least: 3.8.0
 * WC tested up to: 4.8.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
/** Present plugin version **/
define( 'AW_COMPANY_ACCOUNTS_VERSION', '1.0.0' );

require_once(plugin_dir_path(__FILE__) . 'includes/aw-company-accounts-admin.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-ca-company-list-admin.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-ca-new-company-form.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-ca-role-permission-list-admin.php');
require_once(plugin_dir_path(__FILE__) . 'includes/aw-ca-user-myaccount.php');


$companyaccoutn = new AwCompanyAccounts();

class AwCompanyAccounts {
	public $GLOBALS;
	public function __construct() {
		add_filter('wp_kses_allowed_html', 'aw_ca_kses_filter_allowed_html', 10, 2);
		register_activation_hook(__FILE__ , array(get_called_class(),'aw_company_accounts_installer'));
		register_uninstall_hook(__FILE__, array(get_called_class(), 'aw_company_accounts_unistaller'));
		add_action('init', array(get_called_class(),'aw_add_capability_users'));
		 
		if (true === get_plugin_active_status()) {
			add_action('admin_menu', array('AwcompanyaccountsAdmin','aw_company_accounts_menu'));
		}
		
		add_action('admin_enqueue_scripts', array(get_called_class(),'aw_company_accounts_admin_addScript'));
		add_action('wp_enqueue_scripts', array(get_called_class(),'aw_company_accounts_public_addScript'));
		add_action('wp_ajax_aw_ca_get_company_domain_ajax', array(get_called_class(),'aw_ca_get_company_domain_ajax'));
		add_action('wp_ajax_aw_ca_delete_company_domain_ajax', array(get_called_class(),'aw_ca_delete_company_domain_ajax'));
		add_action('wp_ajax_aw_ca_check_company_name_ajax', array(get_called_class(),'aw_ca_check_company_name_ajax'));

		add_action( 'woocommerce_after_customer_login_form', array(get_called_class(),'aw_ca_new_company_from_link_page') );
		add_filter('bulk_actions-users', array(get_called_class(),'aw_ca_assigncompany_bulk_actions'));
		add_filter('handle_bulk_actions-users', array(get_called_class(),'aw_ca_userlist_bulk_action_handler'), 10, 3 );

		add_filter( 'woocommerce_login_redirect', array(get_called_class(),'aw_ca_company_admin_redirect'), 10, 2 );

		add_action( 'woocommerce_checkout_update_order_meta', array(get_called_class(),'aw_company_accouns_update_order'), 10, 2);
	}
	public static function aw_company_accounts_installer() {
		if (is_admin()) {
			$need = get_plugin_active_status();
			if (false === $need) {
				add_action('admin_notices', array('AwcompanyaccountsAdmin','aw_ca_self_deactivate_notice'));
				deactivate_plugins(plugin_basename(__FILE__));
				if (isset($_GET['activate'])) {
					unset($_GET['activate']);
				}
			} else {
				global $wpdb;
				$db_company_info_table 		= $wpdb->prefix . 'aw_ca_company_information'; 
				$db_company_domain_table	= $wpdb->prefix . 'aw_ca_company_domain'; 
				$db_role_permission_table	= $wpdb->prefix . 'aw_ca_role_permission'; 
				$db_aw_ca_email_table 		= $wpdb->prefix . 'aw_ca_email_templates'; 
				flush_rewrite_rules();
				wp_deregister_script( 'autosave' );

				$args = array(
				'role'    => array('store_admin','sales_representative'),
				'orderby' => 'first_name',
				'order'   => 'ASC'
				);
				$users = get_users( $args );

				//add_role('store_admin',__( 'Store Admin' ),array('read'=> false, 'edit_posts'  => false));
				add_role('sales_representative', __( 'Sales Representative' ), array('read'=> false, 'edit_posts'  => false));
				add_role('company_admin', __( 'Company Admin' ), array('read'=> true, 'edit_posts'  => true,'new_users'=>true,'list_users'=>true,'delete_users'=>true,'promote_users'=>true,'add_company'=>true,'company_right'=>true,'aw_ca_order_list'=>true,'edit_shop_orders'=>true,'edit_others_shop_orders'=>true,'create_users'=>true));

				//add_role('company_admin',__( 'Company Admin' ),array('read'=> true,'level_0' => true, 'edit_posts'  => true,'new_users'=>true,'list_users'=>true,'delete_users'=>true,'promote_users'=>true,'add_company'=>true,'company_right'=>true));
				/*$Role = get_role( 'company_admin' );
				$Role->add_cap( 'create_user' );
				$Role->add_cap( 'edit_users' );
				$Role->add_cap( 'list_users' );
				$Role->add_cap( 'delete_users' );
				$Role->add_cap( 'promote_user' );
				$Role->add_cap( 'add_company' );
				$Role->add_cap( 'read_shop_order');
				$Role->add_cap( 'edit_shop_orders');
				$Role->add_cap( 'new_users' );
				$Role->add_cap( 'add_company' );
				$Role->add_cap( 'company_right');*/
				/*add_role('company_admin',__( 'Company Admin' ),array('read'=> true, 'edit_posts'  => true));
				$Role = get_role( 'company_admin' );
				$Role->add_cap( 'new_users' );
				$Role->add_cap( 'list_users' );
				$Role->add_cap( 'delete_users' );
				$Role->add_cap( 'promote_users' );
				$Role->add_cap( 'add_company' );
				$Role->add_cap( 'company_right' );*/

				if ($wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ' , "{$wpdb->prefix}aw_ca_company_information")) != $db_company_info_table) {
					$sql = "CREATE TABLE {$wpdb->prefix}aw_ca_company_information (
								  `id` int(11) NOT NULL auto_increment,
								  `company_admin_id` bigint(20) NOT NULL,
								  `job_position` varchar(55) NOT NULL,
								  `company_name` varchar(55) NOT NULL,
								  `company_email` varchar(55) NOT NULL,
								  `customer_group` varchar(55) NOT NULL,
								  `company_legal_name` TEXT NOT NULL,
								  `tax_vat_id` TEXT NOT NULL,
								  `reseller_id` TEXT NOT NULL,
								  `sales_representative`  bigint(20) NOT NULL,
								  `company_street` TEXT NOT NULL,
								  `country` varchar(55) NOT NULL,
								  `city` varchar(55) NOT NULL,
								  `state` varchar(55) NOT NULL,
								  `company_phone` bigint(20) NOT NULL,
								  `zip` varchar(55) NOT NULL,
								  `poistion` varchar(55) NOT NULL,
								  `status` varchar(55) NOT NULL,
								  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
								  `last_updated` timestamp NOT NULL,
								  PRIMARY KEY (`id`)
								);"	;
					require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
					dbDelta($sql);
				}
			
				if ($wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ' , "{$wpdb->prefix}aw_ca_company_domain")) != $db_company_domain_table) {
					$sql = "CREATE TABLE {$wpdb->prefix}aw_ca_company_domain (
								`id` int(11) NOT NULL auto_increment,
								`company_id` bigint(20) NOT NULL,		
								`domain_name` TEXT NOT NULL,
								`status` varchar(55) NOT NULL,
								`last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
								  PRIMARY KEY (`id`)
								);"	;
					require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
					dbDelta($sql);
				}

				if ($wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ' , "{$wpdb->prefix}aw_ca_role_permission")) != $db_role_permission_table) {
					$sql = "CREATE TABLE {$wpdb->prefix}aw_ca_role_permission (
								`id` int(11) NOT NULL auto_increment,
								`company_id` bigint(20) NOT NULL,		
								`users` int(20) NOT NULL,	
								`role_name` varchar(55) NOT NULL,							
								`permissions` TEXT NOT NULL,
								`order_limit` bigint(55) NOT NULL,	
								`last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
								  PRIMARY KEY (`id`)
								);"	;
					require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
					dbDelta($sql);
				}

				$db_aw_cc_email_table	= $wpdb->prefix . 'aw_ca_email_templates';
				//Check to see if the table exists already, if not, then create it
				if ($wpdb->get_var($wpdb->prepare('SHOW TABLES like %s ' , "{$wpdb->prefix}aw_ca_email_templates")) != $db_aw_ca_email_table) {
					$sql = "CREATE TABLE {$wpdb->prefix}aw_ca_email_templates (
								`id` int(10) NOT NULL AUTO_INCREMENT,
								`email` text NOT NULL,
								`email_type` varchar(55) NOT NULL,
								`category` varchar(55) NOT NULL,
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
					$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}aw_ca_email_templates
					(email, email_type, category, recipients, active, subject, email_heading, additional_content)
					VALUES
					('New Company Submitted','text/html','configuremail',%s, 0 ,'New Company Submitted', 'New Company Submitted', '
					Dear {admin} 
					New Company Submitted.'),
					('New Company Approved','text/html','configuremail',%s, 0 ,'New Company Approved', 'New Company Approved', '
					Dear administrator 
					New Company Submitted.'),
					('New Company Declined','text/html','configuremail',%s, 0 ,'New Company Declined', 'New Company Declined', '
					Dear administrator 
					New Company Declined.'),
					('New Company Domain Created','text/html','configuremail',%s, 0 ,'New Company Domain Created', 'New Company Domain Created', '
					Hello Administrator,
					A new domain is assigned to Company Domains and has status Active.'),
					('New Company User Assigned','text/html','configuremail','', 0 ,'A customer is assigned to the Company', 'New Company Domain Created', '
					Hello Admin,
					A customer is assigned to the company.'),
					('Company Domain Approved','text/html','configuremail','', 0 ,'Company Domain Approved by Sales Representative', 'Company Domain Approved by Sales Representative', '
					Hello {customer},
					The Sales Representative approves the domain.'),
					('Domain Status Changed','text/html','configuremail','', 0 ,'Company Domain Status Changed by Sales Representative', 'Company Domain Status Changed by Sales Representative', '
					
					The Sales Representative changes the status of the domain.'),
					('Sales Representative Deleted Domain','text/html','configuremail','', 0 ,'Company Domain Status Deleted by Sales Representative', 'Company Domain Status Deleted by Sales Representative', '
					
					A domain is deleted by the Sales Representative.'),
					('Company admin change domain status','text/html','configuremail','', 0 ,'Company Domain Status Deleted by Sales Representative', 'Company Domain Status Deleted by Sales Representative', '
					
					A domain is deleted by the Sales Representative.'),
					('Company Domain Deleted','text/html','configuremail','', 0 ,'A domain is deleted', 'Company Domain Deleted by Company Admin', '
					Hello {company_admin},
					Company Domain Deleted by Company Admin'),
					('Sales Representative Group','text/html','salesrepresemail',%s,0,'Sales Representative Group', 'Sales Representative Group', '
					Dear administrator 
					Sales Representative Group.')", "{$admin_emails}", "{$admin_emails}", "{$admin_emails}", "{$admin_emails}", "{$admin_emails}"));
				}
			 

				update_option('aw_ca_companyform_endppoint', 'aw_ca_new_company');
				$post_id 	= -1;
				$author_id 	= get_current_user_id();
				$slug 		= 'aw_ca_new_company';//sanitize_title($_POST['aw_ca_companyform_endppoint']);
				$title 		= 'New Company';
				// Check if page exists, if not create it
				$existing_id = get_option('aw_ca_new_companypage_id');
				wp_delete_post($existing_id);
				$menu = wp_insert_term('AW-CA-NEWCOMPANYFROM', 'nav_menu', array('slug' => 'aw_ca_newcompanymenu'));
				$uploader_page = array(
								'comment_status'        => 'closed',
								'ping_status'           => 'closed',
								'post_author'           => $author_id,
								'post_name'				=> $slug,
								'post_title'            => $title,
								'post_status'           => 'publish',
								'post_type'             => 'page',
								'post_content'			=> '[aw_ca_newcompanyform]'
								);

				$post_id = wp_insert_post( $uploader_page );

				update_post_meta( $post_id, '_wp_page_template', 'default' );
				update_option('aw_ca_new_companypage_id', $post_id);
								
				/*$arr1 = array(
			   'Company_Information_Customization' => array
					   (
							  0 => array('label'=>'Company Name', 'name'=>'company_name', 'enable' => 1,'required' => 1, 'chkboxvisible'=>0),
						   1 => array('label'=>'Company Legal Name','name'=>'company_legal_name', 'enable' => 1,'required' => 0, 'chkboxvisible'=>1),
						   2 => array('label'=>'Company Email', 'name'=>'company_email' ,'enable' => 1,'required' => 1, 'chkboxvisible'=>0),
						   3 => array('label'=>'VAT/TAX ID', 'name'=>'tax_vat_id', 'enable' => 1,'required' => 0, 'chkboxvisible'=>1),
						   4 => array('label'=>'Reseller ID','name'=>'reseller_id', 'enable' => 1,'required' => 0, 'chkboxvisible'=>1)
					   ),
				'Legal_Address_Customization'=>   
						array
					   (
						   0 => array('label'=>'Street Address','name'=>'company_street', 'enable' => 1,'required' => 1, 'chkboxvisible'=>0),
						   1 => array('label'=>'City', 'name'=>'city', 'enable' => 1,'required' => 1, 'chkboxvisible'=>0),
						   2 => array('label'=>'Country', 'name'=>'country', 'enable' => 1,'required' => 1, 'chkboxvisible'=>0),
						   3 => array('label'=>'State/Province', 'name'=>'state', 'enable' => 1,'required' => 1, 'chkboxvisible'=>0),
						   4 => array('label'=>'ZIP/Postal Code', 'name'=>'zip', 'enable' => 1,'required' => 1, 'chkboxvisible'=>0),
						   5 => array('label'=>'Company Phone Number', 'name'=>'company_phone', 'enable' => 1,'required' => 0, 'chkboxvisible'=>1),

					   ),
				'Company_Administrator_Customization'=>   
						array
					   (
						   0 => array('label'=>'First Name','name'=>'first_name', 'enable' => 1,'required' => 1, 'chkboxvisible'=>0),
						   1 => array('label'=>'Last Name','name'=>'last_name', 'enable' => 1,'required' => 1, 'chkboxvisible'=>0),
						   2 => array('label'=>'Email','name'=>'user_email', 'enable' => 1,'required' => 1, 'chkboxvisible'=>0),
						   3 => array('label'=>'Job Position','name'=>'job_position', 'enable' => 1,'required' => 0, 'chkboxvisible'=>1),
						   4 => array('label'=>'Phone number','name'=>'user_phone', 'enable' => 1,'required' => 1, 'chkboxvisible'=>1)

					   ),           

				);
				error_log(print_r($arr1,1));*/
				

				$company_form_setting = 'a:3:{s:33:"Company_Information_Customization";a:5:{i:0;a:5:{s:5:"label";s:12:"Company Name";s:4:"name";s:12:"company_name";s:6:"enable";i:1;s:8:"required";i:1;s:13:"chkboxvisible";i:0;}i:1;a:5:{s:5:"label";s:18:"Company Legal Name";s:4:"name";s:18:"company_legal_name";s:6:"enable";i:1;s:8:"required";i:0;s:13:"chkboxvisible";i:1;}i:2;a:5:{s:5:"label";s:13:"Company Email";s:4:"name";s:13:"company_email";s:6:"enable";i:1;s:8:"required";i:1;s:13:"chkboxvisible";i:0;}i:3;a:5:{s:5:"label";s:10:"VAT/TAX ID";s:4:"name";s:10:"tax_vat_id";s:6:"enable";i:1;s:8:"required";i:0;s:13:"chkboxvisible";i:1;}i:4;a:5:{s:5:"label";s:11:"Reseller ID";s:4:"name";s:11:"reseller_id";s:6:"enable";i:1;s:8:"required";i:0;s:13:"chkboxvisible";i:1;}}s:27:"Legal_Address_Customization";a:6:{i:0;a:5:{s:5:"label";s:14:"Street Address";s:4:"name";s:14:"company_street";s:6:"enable";i:1;s:8:"required";i:1;s:13:"chkboxvisible";i:0;}i:1;a:5:{s:5:"label";s:4:"City";s:4:"name";s:4:"city";s:6:"enable";i:1;s:8:"required";i:1;s:13:"chkboxvisible";i:0;}i:2;a:5:{s:5:"label";s:7:"Country";s:4:"name";s:7:"country";s:6:"enable";i:1;s:8:"required";i:1;s:13:"chkboxvisible";i:0;}i:3;a:5:{s:5:"label";s:14:"State/Province";s:4:"name";s:5:"state";s:6:"enable";i:1;s:8:"required";i:1;s:13:"chkboxvisible";i:0;}i:4;a:5:{s:5:"label";s:15:"ZIP/Postal Code";s:4:"name";s:3:"zip";s:6:"enable";i:1;s:8:"required";i:1;s:13:"chkboxvisible";i:0;}i:5;a:5:{s:5:"label";s:20:"Company Phone Number";s:4:"name";s:13:"company_phone";s:6:"enable";i:1;s:8:"required";i:0;s:13:"chkboxvisible";i:1;}}s:35:"Company_Administrator_Customization";a:5:{i:0;a:5:{s:5:"label";s:10:"First Name";s:4:"name";s:10:"first_name";s:6:"enable";i:1;s:8:"required";i:1;s:13:"chkboxvisible";i:0;}i:1;a:5:{s:5:"label";s:9:"Last Name";s:4:"name";s:9:"last_name";s:6:"enable";i:1;s:8:"required";i:1;s:13:"chkboxvisible";i:0;}i:2;a:5:{s:5:"label";s:5:"Email";s:4:"name";s:10:"user_email";s:6:"enable";i:1;s:8:"required";i:1;s:13:"chkboxvisible";i:0;}i:3;a:5:{s:5:"label";s:12:"Job Position";s:4:"name";s:12:"job_position";s:6:"enable";i:1;s:8:"required";i:0;s:13:"chkboxvisible";i:1;}i:4;a:5:{s:5:"label";s:12:"Phone number";s:4:"name";s:10:"user_phone";s:6:"enable";i:1;s:8:"required";i:1;s:13:"chkboxvisible";i:1;}}}';

				update_option('aw_ca_default_form', $company_form_setting );				
				update_option('AW_COMPANY_ACCOUNTS_VERSION', AW_COMPANY_ACCOUNTS_VERSION );
				update_option( 'company_accounts_by_aheadwork', 'completed' );

			}
		}
	}

	public static function aw_company_accounts_unistaller() {
		/* Perform required operations at time of plugin uninstallation */
		global $wpdb;
		$db_company_info_table 		= $wpdb->prefix . 'aw_ca_company_information'; 
		$db_company_domain_table	= $wpdb->prefix . 'aw_ca_company_domain'; 
		$db_role_permission_table	= $wpdb->prefix . 'aw_ca_role_permission'; 
		$db_email_templates_table 	= $wpdb->prefix . 'aw_ca_email_templates'; 

		if ($GLOBALS['wp_roles']->is_role( 'store_admin' )) {
			remove_role('store_admin');	
		}
		if ($GLOBALS['wp_roles']->is_role( 'sales_representative' )) {
			remove_role('sales_representative');	
		}
		if ($GLOBALS['wp_roles']->is_role( 'company_admin' )) {
			remove_role('company_admin');	
		}
		
		if (is_multisite()) {
			$blogs_ids = get_sites();

			foreach ( $blogs_ids as $b ) {
				$wpdb->prefix  = $wpdb->get_blog_prefix($b->blog_id);
				$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aw_ca_company_information");
				$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aw_ca_company_domain");
				$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aw_ca_role_permission");
				$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aw_ca_email_templates");
				delete_network_option( $b->blog_id, 'company_accounts_by_aheadwork' );
				delete_network_option( $b->blog_id, 'AW_COMPANY_ACCOUNTS_VERSION' );
				delete_network_option( $b->blog_id, 'aw_ca_default_salesrep');
				delete_network_option( $b->blog_id, 'aw_ca_order_approvalenable');
				delete_network_option( $b->blog_id, 'aw_ca_default_form');
				delete_network_option( $b->blog_id, 'aw_ca_sales_representative');
				delete_network_option( $b->blog_id, 'aw_ca_groupsales_representative');
			}
		} else {

			$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aw_ca_company_information");
			$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aw_ca_company_domain");
			$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aw_ca_role_permission");
			$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aw_ca_email_templates");	
			delete_option('company_accounts_by_aheadwork');
			delete_option('AW_COMPANY_ACCOUNTS_VERSION');	
			delete_option('aw_ca_default_salesrep');
			delete_option('aw_ca_order_approvalenable');
			delete_option('aw_ca_default_form');
			delete_option('aw_ca_sales_representative');
			delete_option('aw_ca_groupsales_representative');
		}
	}

	public static function aw_company_accounts_admin_addScript() {
		$path 	= parse_url(get_option('siteurl'), PHP_URL_PATH);
		$host 	= parse_url(get_option('siteurl'), PHP_URL_HOST);
		$nonce 	= wp_create_nonce('companyaccounts_admin_nonce');
		$page 	= '';
		
		wp_register_style('companyaccountsadmincss', plugins_url('/admin/css/aw-company-accounts-admin.css', __FILE__ ), array(), '1.0' );
		wp_enqueue_style('companyaccountsadmincss');

		wp_enqueue_script('jquery-ui-sortable');
	 
		wp_register_script('companyaccountsadminjs', plugins_url('/admin/js/aw-company-accounts-admin.js', __FILE__ ), array(), '1.0' );

		$order_js_var = array('site_url' => get_option('siteurl'), 'ajax_url'=>admin_url('admin-ajax.php'), 'path' => $path, 'host' => $host, 'aw_ca_admin_nonce' => $nonce);
		wp_localize_script('companyaccountsadminjs', 'aw_ca_admin_js_var', $order_js_var);

		wp_register_script('companyaccountsadminjs', plugins_url('/admin/js/aw-company-accounts-admin.js', __FILE__ ), array(), '1.0' );
		wp_enqueue_script('companyaccountsadminjs'); 
		 
	}

	public static function aw_company_accounts_public_addScript() {
		//add_filter( 'comments_clauses', 'aw_pq_filter_comments_clauses', 10, 1 );
		$path = parse_url(get_option('siteurl'), PHP_URL_PATH);
		$host = parse_url(get_option('siteurl'), PHP_URL_HOST);
		$nonce 	= wp_create_nonce('companyaccounts_front_nonce');
		$page 	= '';

		/** Add Plugin CSS and JS files Public Side**/
		wp_register_style('companyaccountspubliccss', plugins_url('/public/css/aw-company-accounts-public.css', __FILE__ ), array(), '1.0' );
		wp_enqueue_style('companyaccountspubliccss');	
		wp_register_script('companyaccountspublicjs', plugins_url('/public/js/aw-company-accounts-public.js', __FILE__ ), array('jquery'), '1.0' );

		$js_var = array('site_url' => get_option('siteurl'),'ajax_url'=>admin_url('admin-ajax.php'), 'path' => $path, 'host' => $host,'aw_ca_front_nonce' => $nonce);
		wp_localize_script('companyaccountspublicjs', 'js_ca_var', $js_var);
		wp_register_script('companyaccountspublicjs', plugins_url('/public/js/aw-company-accounts-public.js', __FILE__ ), array('jquery'), '1.0' );
		wp_enqueue_script('companyaccountspublicjs');
	}

	public static function aw_add_capability_users() {
		$Role = get_role( 'company_admin' );
		$Role->add_cap( 'create_user' );
		$Role->add_cap( 'edit_users' );
		$Role->add_cap( 'list_users' );
		$Role->add_cap( 'delete_users' );
		$Role->add_cap( 'promote_user' );
		$Role->add_cap( 'add_company' );
		$Role->add_cap( 'read_shop_order');
		$Role->add_cap( 'edit_shop_order');
		$Role->add_cap( 'edit_shop_orders');
		$Role->add_cap( 'edit_private_shop_orders');
	}

	public static function aw_ca_get_company_domain_ajax() {
		global $wpdb;
		$msg = array();
		check_ajax_referer( 'companyaccounts_admin_nonce', 'nonce_ca_ajax' );

		if (!empty($_POST['domain_name'])) {
			$domain_name = sanitize_text_field($_POST['domain_name']);
		}

		if (!empty($_POST['domain_status'])) {
			$domain_status = sanitize_text_field($_POST['domain_status']);
		}

		$result = aw_ca_get_company_domain_row( $domain_name );
		if (!empty($result)) {
			$msg = 'This domain already exists';
		} else {
			$msg = '';
		} 
		echo esc_html($msg);
		wp_die();
	}

	public static function aw_ca_delete_company_domain_ajax() {
		$wpdb;
		global $wpdb;
		$msg = array();
		check_ajax_referer( 'companyaccounts_admin_nonce', 'nonce_ca_ajax' );
		$domain_id = '';
		if (!empty($_POST['domain_id'])) {
			$domain_id = sanitize_text_field($_POST['domain_id']);
		}
		$no_row = aw_ca_delete_company_domain( $domain_id );
		$admin_obj 	= get_users( array( 'role__in' => array( 'administrator' ) ) );
		foreach ( $admin_obj as $user ) {
				aw_ca_send_mail_notification( $user->ID, 'Company Domain Deleted'); 
		}
		echo esc_html($no_row);
		wp_die();
	}	

	public static function aw_ca_check_company_name_ajax() {
		$wpdb;
		global $wpdb;
		$msg = array();
		check_ajax_referer( 'companyaccounts_front_nonce', 'nonce_ca_ajax' );
		if (!empty($_POST['company_name'])) {
			$company_name = sanitize_text_field($_POST['company_name']);
		}

		$result = aw_ca_get_company_by_name( $company_name );
		if (!empty($result)) {
			$msg = 'This company name already exists';
		} else {
			$msg = '';
		} 
		echo esc_html($msg);
		wp_die();		
	}

	public static function aw_ca_new_company_from_link_page() {
		if ( ! is_user_logged_in() ) {
			$link = home_url( '/aw_ca_new_company' ); ?>
			<span>New Company Account</span>
			<hr/>
			<p>If you are a company representative, use this feature to be able to manage subaccounts and other B2B features.</p>
			<p><a href="<?php echo esc_url($link); ?>" class="woocommerce-button button woocommerce-form-login__submit">Create a Company Account<a/></p>
		<?php
		}
	}

	public static  function aw_ca_assigncompany_bulk_actions( $actions) {
		$companylist 	= aw_ca_get_approved_company_list();	
		$list_array 	= array();
		foreach ($companylist as $key => $list) {
			$list_array[$list->id]= $list->company_name;	
		}
		$actions['Assign_to_the_Company'] = $list_array;
		return $actions;
	}

	public static function aw_ca_userlist_bulk_action_handler( $redirect, $action, $object_ids ) {
		global $wpdb;
		$company_id = $action;
		$user_id 	= array();
		foreach ($object_ids as $key => $user_id) {
			update_user_meta($user_id, 'company_id', $company_id);
			$company = aw_ca_get_company_by_id($company_id);
			update_user_meta($user_id, 'company_name', $company->company_name);
			aw_ca_send_mail_notification( $user_id, 'New Company User Assigned');
		}
		return $redirect;
	}

	public static function aw_ca_company_admin_redirect( $redirect, $user ) {
		// Get the first of all the roles assigned to the user
		$role = $user->roles[0];
		$dashboard = admin_url();
		$myaccount = get_permalink( wc_get_page_id( 'myaccount' ) );
		if ( 'administrator' === $role  || 'company_admin' === $role ) {
			//Redirect administrators to the dashboard
			$redirect = $dashboard;
		} 
		return $redirect;
	}

	public static function aw_company_accouns_update_order( $order_id, $posted) {
		$user_id 	= get_current_user_id();
		if ($user_id) {
			$company_id = get_user_meta($user_id, 'company_id', true);
			if ($company_id) {
				update_post_meta( $order_id , 'company_id', $company_id);		
			}
		}
	}
}

function get_plugin_active_status() {
	$need = true;
	if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	}
	if ( is_multisite() ) {
		  // this plugin is network activated - Woo must be network activated 
		if ( is_plugin_active_for_network( plugin_basename(__FILE__) ) ) {
			$need = is_plugin_active_for_network('woocommerce/woocommerce.php') ? true : false ; 
		  // this plugin is locally activated - Woo can be network or locally activated 
		} else {
			$need = is_plugin_active( 'woocommerce/woocommerce.php')  ? true : false  ;   
		}
		// this plugin runs on a single site    
	} else {
		  $need =  is_plugin_active( 'woocommerce/woocommerce.php') ? true: false  ;     
	}
	return $need;
}

function aw_ca_get_email_template_setting_results( $category) {
	global $wpdb;
	$emails_template = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_ca_email_templates WHERE category = %s ", "{$category}" ) );
	return $emails_template;
}
function aw_ca_get_email_template_setting_row( $id) {
	global $wpdb;
	$emails_template = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_ca_email_templates WHERE id = %d ", "{$id}") );
	return $emails_template;
}
 
function aw_ca_register_user( $email, $first_name, $last_name, $role, $side ) {
	$errors = new WP_Error();
	 // Email address is used as both username and email. It is also the only
	// parameter we need to validate
	if ( username_exists( $email ) || email_exists( $email ) ) {
		$user = get_user_by( 'email', $email );
		$user_id = $user->ID;

	} else {
		// Generate the password so that the subscriber will have to check email...
		$password = wp_generate_password( 12, false );
		$user_data = array(
			'user_login'    => $email,
			'user_email'    => $email,
			'user_pass'     => $password,
			'first_name'    => $first_name,
			'last_name'     => $last_name,
			'nickname'      => $first_name,
		);
		$user_id 	= wp_insert_user( $user_data );
		wp_new_user_notification( $user_id, 'user');
		/*if ('backend' === $side) {
			//wp_new_user_notification( $user_id, 'user' );
			wp_new_user_notification( $user_id, 'user');
		} else {
			wp_new_user_notification( $user_id );
		}*/
		$user = new WP_User( $user_id );
		$user->set_role( $role );
	}
	return $user_id;
}

function aw_ca_get_company_details_row( $id ) {
	global $wpdb;
	$company_detail = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_ca_company_information WHERE id = %d ", "{$id}") );
	return $company_detail;
}

function aw_ac_get_user_details( $user_id) {
	$details = array();
	$com_admin_detail 		= get_userdata($user_id);
	if (!empty($com_admin_detail->data)) {
		$details['user_email']	= $com_admin_detail->data->user_email;
	}
	$details['user_id']			= $user_id;
	$details['first_name']		= get_user_meta($user_id, 'first_name', true);
	$details['last_name']		= get_user_meta($user_id, 'last_name', true);
	$details['job_position']	= get_user_meta($user_id, 'job_position', true);
	$details['phone_number']	= get_user_meta($user_id, 'phone_number', true);
	$details['company_id']		= get_user_meta($user_id, 'company_id', true);
	$details['customer_group']	= get_user_meta($user_id, 'customer_group', true);
	return $details;
}

function aw_ca_get_approved_company_list() {
	global $wpdb;
	$company_list = $wpdb->get_results($wpdb->prepare( "SELECT id, company_name FROM {$wpdb->prefix}aw_ca_company_information WHERE status = %s ", 'approved') );
	return $company_list;
}

function aw_ca_get_company_by_name( $company_name ) {
	global $wpdb;
	$company_row = $wpdb->get_row($wpdb->prepare( "SELECT * FROM {$wpdb->prefix}aw_ca_company_information WHERE company_name = %s ", "{$company_name}") );
	return $company_row;
}

function aw_ca_get_company_by_id( $record_id ) {
	global $wpdb;
	$company_row = $wpdb->get_row($wpdb->prepare( "SELECT * FROM {$wpdb->prefix}aw_ca_company_information WHERE id = %d ", "{$record_id}") );
	return $company_row;
}

function aw_ca_get_company_domains_list( $company_id ) {
	global $wpdb;
	$domain_list = $wpdb->get_results($wpdb->prepare( "SELECT * FROM {$wpdb->prefix}aw_ca_company_domain WHERE company_id = %d ", "{$company_id}") );
	return $domain_list;
}

function aw_ca_get_company_domain_row( $domain ) {
	global $wpdb;
	$domain_detail = $wpdb->get_row($wpdb->prepare( "SELECT * FROM {$wpdb->prefix}aw_ca_company_domain WHERE domain_name = %s ", "{$domain}") );
	return $domain_detail;
}

function aw_ca_get_company_id_by_domain( $domain ) {
	global $wpdb;
	$company_id = $wpdb->get_var($wpdb->prepare( "SELECT company_id FROM {$wpdb->prefix}aw_ca_company_domain WHERE domain_name  LIKE  %s AND status= %s ", "%{$domain}%", 'active') );
	return $company_id;
}

function aw_ca_get_company_domain_row_by_id( $domain_id ) {
	global $wpdb;
	$domain_detail = $wpdb->get_row($wpdb->prepare( "SELECT * FROM {$wpdb->prefix}aw_ca_company_domain WHERE id = %s ", "{$domain_id}") );
	return $domain_detail;
}

function aw_ca_delete_company_domain( $domain_id ) {
	global $wpdb;
	$no_row = $wpdb->query($wpdb->prepare( "DELETE FROM {$wpdb->prefix}aw_ca_company_domain WHERE id = %d ", "{$domain_id}") );
	return $no_row;
}

function aw_ca_get_allrole_permission( $company_id, $limit = '', $offset = '') {
	global $wpdb;

	if ( '' !== $offset && '' !== $limit ) {
		$result = $wpdb->get_results($wpdb->prepare( "SELECT * FROM {$wpdb->prefix}aw_ca_role_permission WHERE company_id = %d LIMIT %d OFFSET %d ", "{$company_id}", "{$limit}", "{$offset}" ) );
	} else {
		$result = $wpdb->get_results($wpdb->prepare( "SELECT * FROM {$wpdb->prefix}aw_ca_role_permission WHERE company_id = %d ", "{$company_id}" ) );
	}
	
	return $result;
}

function aw_ca_get_role_permission_row( $id ) {
	global $wpdb;
	$result = $wpdb->get_row($wpdb->prepare( "SELECT * FROM {$wpdb->prefix}aw_ca_role_permission WHERE id = %d ", "{$id}"), ARRAY_A );
	return $result;
}
function aw_ca_get_role_permission_by_company_row( $loggedin_user_id ) {
	global $wpdb;
	$user 			= wp_get_current_user();
	$role 			= $user->roles[0];
	$company_id 	= get_user_meta( $loggedin_user_id, 'company_id', true);
	$result 		= $wpdb->get_var($wpdb->prepare( "SELECT permissions FROM {$wpdb->prefix}aw_ca_role_permission WHERE company_id = %d  AND role_name = %s ", "{$company_id}", "{$role}" ));
	if (!empty($result)) {
		$result = maybe_unserialize($result);
	}
	return $result;
}

function aw_ca_get_post_status_by_user( $post_status) {
	global $wpdb;
	$company_admin_id = get_current_user_id();
	$company_id = get_user_meta( $company_admin_id, 'company_id', true);
	$args = array(
	   'post_type'		=>'shop_order',
	   'post_per_page' 	=> -1,
	   'meta_query' 	=> array(
		   array(
			   'key' 	=> 'company_id',
			   'value' 	=> $company_id,
		   )
	   ),
	   'fields' => 'ids'
	);
	if ('all'!=$post_status) {
		$args['post_status'] = $post_status;
	}
	$query = new WP_Query( $args );
	$total = $query->posts;
	return count($total);
}


function aw_ca_send_mail_notification( $user_id, $mail_template) {
	global $wpdb;
	$user_data 		= get_userdata($user_id);
	if (!empty($user_data)) {
		$role_name 	= $user_data->roles[0];	
		$user_email = $user_data->get('user_email');
		$user_name 	= $user_data->display_name;
	}

	$settings 		= $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_ca_email_templates WHERE email = %s", "{$mail_template}" )); 
	if (!empty($settings)) {
		foreach ($settings as $value) {
			$subject 			= $value->subject;
			$additional_content = $value->additional_content;
			$heading 			= $value->email_heading;
			$active 			= $value->active;
			$email_type 		= $value->email_type;
		}
		if (0 == $active) {
			return;
		}
	} else {
		$email_type = 'text/plain';
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
	$footer_text 			= aw_ca_placeholders_replace($footer_text);

	if (!empty($heading)) {
		$email_heading 	= $heading;
	}

	if (!empty($subject)) {
		$email_subject 	= $subject;
	}
	if (!empty($additional_content)) {
		$additional_text = $additional_content;
/*		$additional_text = preg_replace('/{admin}/', '<b>{admin}</b><br>', $additional_text);
		$additional_text = preg_replace('/{admin}/', $user_name, $additional_text);

		$additional_text = preg_replace('/{customer_name}/', '<b>{customer_name}</b><br>', $additional_text);
		$additional_text = preg_replace('/{customer_name}/', $user_name, $additional_text);*/


		if (!empty($transaction_array)) {
			$order_id = '';
			if (!empty($transaction_array['order_id'])) {
				$order_id = $transaction_array['order_id'];
			}
			$comment_to_customer = '';
			if (isset($transaction_array['comment_to_customer']) && !empty($transaction_array['comment_to_customer'])) {
				$comment_to_customer = $transaction_array['comment_to_customer'];
			}
			$transaction_text='';
/*			$transaction_text='<br><table border="1px" cellpadding="2px" cellspacing="2px">
			<tr> <th>Amount</th> <th>Credit Balance</th> <th>Available Credit</th> <th>Credit Limit</th> <th>Comment</th><tr> <td>' . $transaction_array['transaction_amount'] . '</td> <td>' . $transaction_array['credit_balance'] . '</td><td>' . $transaction_array['available_credit'] . '</td><td>' . $transaction_array['credit_limit'] . '</td> <td>' . $comment_to_customer . '</td></tr>	</table><br>';
			$additional_text .=  $transaction_text;*/
		}

/*		$myaccount_url 	 = wc_get_account_endpoint_url( 'aw-cc-mycredit' ) ;
		$additional_text .= 'Click <a href="' . $myaccount_url . '" target="_blank">here</a> to see more details.';

		$additional_text = $additional_text ; */
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
			$to_replace 	= array('{site_title}','{site_url}','{customer_name}','{order_number}','{order_date}');
			$by_replace 	= array($site_title,$site_url,$user_name,'','');
			$message 		= str_replace($to_replace, $by_replace, $message);
			$email_subject 	= str_replace($to_replace, $by_replace, $email_subject);
			ob_end_clean();
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			add_filter( 'wp_mail_from', 'aw_ca_mail_from' );
			add_filter( 'wp_mail_from_name', 'aw_ca_from_name' );
			
		if ('text/plain' == $email_type) {
			$message 	= preg_replace('/(<[^>]+) style=".*?"/i', '$1', $message);
			$to_replace = array('<b>','</b>','<h1>','</h1>','<strong>','</strong>');
			$message 	= str_replace($to_replace, '', $message);
			$message 	= preg_replace('/<b>/', '$1', $message);
		}
		wp_mail($user_email, $email_subject, $message, $headers);
		remove_filter( 'wp_mail_from', 'aw_ca_mail_from' );
		remove_filter( 'wp_mail_from_name', 'aw_ca_from_name' );
}
function aw_ca_mail_from( $email ) {
	$from_email = get_option('woocommerce_email_from_address');
	return $from_email;
}

function aw_ca_from_name( $name ) {
	$from_name = get_option('woocommerce_email_from_name');
	return $from_name;
}
function aw_ca_placeholders_replace( $string ) {
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
function aw_ca_kses_filter_allowed_html( $allowed, $context) {
	$allowed['style'] 				= array();
	$allowed['input']['type'] 		= array();
	$allowed['input']['name'] 		= array();
	$allowed['input']['checked'] 	= array();
	$allowed['input']['value'] 		= array();
	$allowed['input']['id'] 		= array();
	$allowed['input']['class'] 		= array();
	$allowed['input']['placeholder'] = array();
	$allowed['input']['style'] 		= array();
	$allowed['input']['onkeypress'] = array();
	$allowed['span']['onclick'] 	= array();
	$allowed['button']['onclick'] 	= array();

	return $allowed;
} 




  
