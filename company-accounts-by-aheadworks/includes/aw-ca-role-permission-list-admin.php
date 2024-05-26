<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
if (!class_exists('WP_List_Table')) {
	require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
class AwcaRolePermissionAdmin extends WP_List_Table {
	public function __construct() {
		global $status, $page;
		parent::__construct(array(
			'singular' 	=> __('Companylist', 'CA'),
			'plural' 	=> __('Companylists', 'CA'),
			'ajax'   	=> true
		));
	}

	public function column_default( $item, $column_name) {

		if (isset($item['id']) && 'id' == $column_name) {
			return $item['id']; 
		}

		if (isset($item['role_name']) && 'role_name' == $column_name) {
			global $wpdb;
			$url = get_site_url() . '/wp-admin/admin.php?page=add-new-role';
			$table_name  = $wpdb->prefix . 'aw_ca_company_information';
			;
			$linkeddata = $item['role_name'];
			$linkeddata = '<a href="' . $url . '&s=' . urlencode($item['role_name']) . '&id=' . $item['id'] . '">' . ucfirst(str_replace( '_', ' ', $item['role_name'])) . '</a>';
			$item['role_name'] = $linkeddata;
			return $item['role_name'];
		} 
		if (isset($item['users']) && 'users' == $column_name) {
			$role = str_replace( ' ', '_', $item['role_name']);
			$company_admin_id = get_current_user_id();
			$company_id = get_user_meta( $company_admin_id, 'company_id', true);
			$item['users'] = count( get_users( array( 'role' => $role,'meta_query'   => array( array('key'=>'company_id','value'=>$company_id) ) ) ) );
		}
		return $item[$column_name];
	}

	public function column_name( $item) {
		if (isset($_REQUEST['page'])) {
			$page = sanitize_text_field($_REQUEST['page']);
		}
		return sprintf('%s %s', $item['id'], $this->row_actions($actions));
	}

	public function get_columns() {
		$columns = array(
							'id'		=> __('ID', 'CA'),
							'role_name'	=> __('Name', 'CA'),
							'users' 	=> __('Users', 'CA'),
						);
		return $columns;
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
								'id'		=> array('id', true),
								'role_name' => array('role_name', true),
								'users'		=> array('total_users', true),
								//'action' 	=> array('action', true),
			);
		return $sortable_columns;
	}

	public function prepare_items( $search = '', $user_id = '') {
		
		global $wpdb;
		$sql = '';
		$db_ca_role_permission	= 'aw_ca_role_permission';
		$db_user_table 			= 'users';
		$db_user_meta_table		= 'usermeta';

		$total_items 			= self::get_count($user_id, $search);
		$users_table 			= 'users';
		$per_page     			= $this->get_items_per_page( 'aw_ca_rolelist_per_page', 20 );
		$current_page 			= $this->get_pagenum();
		if (isset($_REQUEST['paged'])) {
			$current_page 		= sanitize_text_field($_REQUEST['paged']);
		}

		$columns 	= $this->get_columns();
		$hidden 	= array();
		$sortable 	= $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->process_bulk_action();
		$date 			= 'last_updated';
		$orderby        = ( isset($_REQUEST['orderby']) && in_array(sanitize_text_field($_REQUEST['orderby']), array_keys($this->get_sortable_columns())) ) ? sanitize_text_field($_REQUEST['orderby']) : $date;  
		$order 	= ( isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc')) ) ? sanitize_text_field($_REQUEST['order']) : 'desc';
		$offset = ( (int) $current_page - 1 ) * (int) $per_page;

		$this->items = 	$wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}%5s  ORDER BY %5s %5s LIMIT %d OFFSET %d " , "{$db_ca_role_permission}", "{$orderby}", "{$order}", "{$per_page}", "{$offset}"), ARRAY_A);
		
		$this->set_pagination_args(array(
			'total_items'   => $total_items,
			'per_page'      => $per_page,
			'total_pages'   => ceil($total_items / $per_page),
		));
		
		return $this->items;
	}

	public function get_count( $user_id = '', $search = '') {
		global $wpdb;
		 
		if ('' != $search) {

			$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}aw_ca_company_information WHERE (`status` LIKE %s OR `company_name` LIKE %s OR `country` LIKE %s OR `city` LIKE %s OR company_admin LIKE %s OR `admin_email` LIKE %s OR `sales_representative` LIKE %s OR `customer_group` LIKE %s)", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"));

			//$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}aw_company_credit_history WHERE user_id = %d AND ( `last_payment` OR `transaction_status` OR `transaction_amount` OR `credit_balance` OR `available_credit` OR `credit_limit` OR `comment_to_customer` OR `comment_to_admin`) LIKE %s", "{$user_id}" , "{$search}"));
			  
		} else {
			$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}aw_ca_company_information WHERE 1= %d", 1));
			//error_log($total_items);
			//$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}aw_company_credit_history WHERE user_id = %d", "{$user_id} "));	
		}

		return $total_items;
	}

}
