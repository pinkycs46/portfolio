<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
if (!class_exists('WP_List_Table')) {
	require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
class AwcaOrderListByUser extends WP_List_Table {
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
		/*if ('user_id' == $column_name) {
			$customer 		= new WC_Customer( $item[$column_name] );
			$first_name   	= $customer->get_first_name();
			$last_name    	= $customer->get_last_name();
			$display_name 	= $customer->get_display_name(); 
			$item[$column_name]= $display_name;
		}*/
		if (isset($item['status']) && 'status' == $column_name) {
			$item['status'] = ucfirst($item['status']);
		}
		if (isset($item['company_name']) && 'company_name' == $column_name) {
			global $wpdb;
			$url = get_site_url() . '/wp-admin/admin.php?page=new-company-form';
			$table_name  = $wpdb->prefix . 'aw_ca_company_information';
			;
			$linkeddata = $item['company_name'];
			$linkeddata = '<a href="' . $url . '&s=' . urlencode($item['company_name']) . '&id=' . $item['id'] . '">' . $item['company_name'] . '</a>';
			$item['company_name'] = $linkeddata;
			return $item['company_name'];
		} 
		return $item[$column_name];
	}

	public function column_name( $item) {
		if (isset($_REQUEST['page'])) {
			$page = sanitize_text_field($_REQUEST['page']);
		}
		if (isset($_GET['status'])) {
			$actions['approve'] = sprintf('<a href="?page=%s&action=approve&id=%s">%s</a>', $page, $item['id'], __('Approved', 'CA'));
			$actions['block'] = sprintf('<a href="?page=%s&action=block&id=%s">%s</a>', $page, $item['id'], __('Blocked', 'CA'));
			$actions['decline'] = sprintf('<a href="?page=%s&action=decline&id=%s">%s</a>', $page, $item['id'], __('Declined', 'CA'));
		} else {
			$actions = array(
			  'Trash' => sprintf('<a href="?page=%s&action=trash&id=%s">%s</a>', $page, $item['id'], __('Trash', 'CA'))
			);
		}
		return sprintf('%s %s', $item['id'], $this->row_actions($actions));
	}

	public function column_cb( $item) {
		if (isset($item['id'])) {
			return sprintf('<input type="checkbox" name="id[]" value="%s" />', $item['id']);
		}
	}

	public function get_bulk_actions() {
		$actions = array('approved' => 'Approve','blocked' => 'Block','declined' => 'Decline');
		return $actions;
	}

	public function process_bulk_action() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'aw_ca_company_information'; // do not forget about tables prefix
		$action = $this->current_action();
		 
		if ('approved' === $action || 'blocked' === $action || 'declined' === $action) {

			if (isset($_REQUEST['id'])) {
				$ids = json_encode($_REQUEST);
				$ids = wp_unslash($ids);
				$ids = json_decode($ids, true);
				$ids = $ids['id'];
			} else { 
				$ids = array();
			}
			if (is_array($ids)) {
				$ids = implode(',', $ids);
			}
			if (!empty($ids)) {
				$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aw_ca_company_information SET status= %s  WHERE id IN(%5s)" , "{$action}" , "{$ids}"));
			}
		}
	}


	public function get_columns() {
		$columns = array(
						'cb' 			=> '<input type="checkbox" />', //Render a checkbox instead of text
						'id'			=> __('ID', 'CA'),
						'status' 		=> __('Status', 'CA'),
						'company_name'	=> __('Company Name', 'CA'),
						'country' 		=> __('Country', 'CA'),
						'city'			=> __('City', 'CA'),
						'customer_group'=> __('Customer Group', 'CA'),
						'company_admin'	=> __('Company Admin', 'CA'),
						'admin_email'	=> __('Admin Email', 'CA'),
						'sales_representative'	=> __('Sales Representative', 'CA'),
						//'comment_to_customer'	=> __('Action', 'CA'),
						
					);
		return $columns;
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
				'id'			=> array('id', true),
				'status' 		=> array('status', true),
				'company_name'	=> array('company_name', true),
				'country' 		=> array('country', true),
				'city'			=> array('city', true),
				'customer_group'=> array('customer_group', true),
				'company_admin'	=> array('company_admin', true),			
				'admin_email'	=> array('admin_email', true),
				'sales_representative'	=> array('sales_representative', true),
			);
		return $sortable_columns;
	}

	public function prepare_items( $search = '', $user_id = '') {
		
		global $wpdb;
		$sql = '';
		$db_company_table 		= 'aw_ca_company_information';
		$db_user_table 			= 'users';
		$db_user_meta_table		= 'usermeta';

		$total_items 			= self::get_count($user_id, $search);
		$users_table 			= 'users';
		$per_page     			= $this->get_items_per_page( 'aw_ca_companylist_per_page', 20 );
		$current_page 			= $this->get_pagenum();
		if (isset($_REQUEST['paged'])) {
			$current_page 		= sanitize_text_field($_REQUEST['paged']);
		}

		$columns 	= $this->get_columns();
		$hidden 	= array();
		$sortable 	= $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->process_bulk_action();
		$date 			= 'created_date';
		$orderby        = ( isset($_REQUEST['orderby']) && in_array(sanitize_text_field($_REQUEST['orderby']), array_keys($this->get_sortable_columns())) ) ? sanitize_text_field($_REQUEST['orderby']) : $date;  
		$order = ( isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc')) ) ? sanitize_text_field($_REQUEST['order']) : 'desc';
		$offset= ( (int) $current_page - 1 ) * (int) $per_page;
		if ('' != $search) {
			$this->items = 	$wpdb->get_results($wpdb->prepare("SELECT * , IFNULL((select U.user_email from  {$wpdb->prefix}%5s AS U WHERE U.ID= CI.company_admin_id ),'') AS admin_email, IFNULL((SELECT U.display_name  from {$wpdb->get_blog_prefix(1)}%5s AS U WHERE U.ID= CI.company_admin_id ),'') AS company_admin , IFNULL((SELECT U.display_name from {$wpdb->get_blog_prefix(1)}%5s AS U WHERE U.ID= CI.sales_representative ),'') AS sales_representative FROM {$wpdb->prefix}%5s AS CI WHERE CI.company_admin_id <> 0 AND (`status` LIKE %s OR `company_name` LIKE %s OR `country` LIKE %s OR `city` LIKE %s OR company_admin LIKE %s OR `admin_email` LIKE %s OR `sales_representative` LIKE %s OR `customer_group` LIKE %s) ORDER BY %5s %5s LIMIT %d OFFSET %d" , "{$db_user_table}", "{$db_user_table}", "{$db_user_table}", "{$db_company_table}", "{$user_id}", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "{$orderby}", "{$order}", "{$per_page}", "{$offset}"), ARRAY_A);
			/*$this->items = 	$wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}%5s WHERE user_id=%d AND (`transaction_status` LIKE %s OR `transaction_amount` LIKE %s OR `credit_balance` LIKE %s OR `available_credit` LIKE %s OR `credit_limit` LIKE %s OR `comment_to_customer` LIKE %s OR `comment_to_admin` LIKE %s OR `last_payment` LIKE %s) ORDER BY %5s %5s LIMIT %d OFFSET %d", "{$db_transcation_table}", "{$user_id}", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%" , "{$orderby}", "{$order}", "{$per_page}", "{$offset}"), ARRAY_A);	*/
		} else {
			if (is_multisite()) {
				$this->items = 	$wpdb->get_results($wpdb->prepare("SELECT * , IFNULL((select U.user_email from $wpdb->get_blog_prefix(1)}%5s AS U WHERE U.ID= CI.company_admin_id ),'') AS admin_email, IFNULL((SELECT U.display_name  from {$wpdb->get_blog_prefix(1)}%5s AS U WHERE U.ID= CI.company_admin_id ),'') AS company_admin , IFNULL((SELECT U.display_name from {$wpdb->get_blog_prefix(1)}%5s AS U WHERE U.ID= CI.sales_representative ),'') AS sales_representative FROM {$wpdb->prefix}%5s AS CI WHERE CI.company_admin_id <> 0 ORDER BY %5s %5s LIMIT %d OFFSET %d" , "{$db_user_table}", "{$db_user_table}", "{$db_user_table}", "{$db_company_table}", "{$orderby}", "{$order}", "{$per_page}", "{$offset}"), ARRAY_A);	
			} else {
				$this->items = 	$wpdb->get_results($wpdb->prepare("SELECT * , IFNULL((select U.user_email from  {$wpdb->prefix}%5s AS U WHERE U.ID= CI.company_admin_id ),'') AS admin_email, IFNULL((SELECT U.display_name  from {$wpdb->get_blog_prefix(1)}%5s AS U WHERE U.ID= CI.company_admin_id ),'') AS company_admin , IFNULL((SELECT U.display_name from {$wpdb->get_blog_prefix(1)}%5s AS U WHERE U.ID= CI.sales_representative ),'') AS sales_representative FROM {$wpdb->prefix}%5s AS CI WHERE CI.company_admin_id <> 0 ORDER BY %5s %5s LIMIT %d OFFSET %d" , "{$db_user_table}", "{$db_user_table}", "{$db_user_table}", "{$db_company_table}", "{$orderby}", "{$order}", "{$per_page}", "{$offset}"), ARRAY_A);

			}
		}

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
