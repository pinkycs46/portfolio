<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
if (!class_exists('WP_List_Table')) {
	require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class AwcccustomerListAdmin extends WP_List_Table {

	public function __construct() {
		global $status, $page;
		parent::__construct(array(
			'singular' => __('Transaction', 'CC'),
			'plural' => __('Transactions', 'CC'),
			'ajax'   => true
		));
	}

	public function column_default( $item, $column_name) {
		if (isset($item['id']) && 'id' == $column_name) {
			return $item['id']; 
		}

		if (isset($item['user_nicename']) && 'user_nicename' == $column_name) {
			global $wpdb;
			$url = get_site_url() . '/wp-admin/admin.php?page=customer-credit-history';
			$table_name  = $wpdb->prefix . 'aw_company_credit_balance';
			$linkeddata = $item['user_nicename'];
			$linkeddata = '<a href="' . $url . '&id=' . urlencode($item['id']) . '">' . $item['user_nicename'] . '</a>';
			$item['user_nicename'] = $linkeddata;
			return $item['user_nicename'];
		}

		if (isset($item['user_email']) && 'user_email' == $column_name) {
			return $item['user_email']; 
		}

		if ( isset($item['credit_limit']) && !empty($item['credit_limit']) && 'credit_limit' == $column_name) {
			return aw_cc_get_amount($item['credit_limit']);
		}

		if ( isset($item['credit_balance']) && !empty($item['credit_balance']) && 'credit_balance' == $column_name) {
			return aw_cc_get_amount($item['credit_balance']);
		}
		if ( isset($item['available_credit']) && !empty($item['available_credit']) && 'available_credit' == $column_name) {
			return aw_cc_get_amount($item['available_credit']);
		}
		if ( isset($item['last_payment']) && 'last_payment' == $column_name) {
				$show_date = strtotime($item['last_payment']);
			if (!empty($show_date)) {
				$item['last_payment'] = gmdate('M d, Y h:i:s A', $show_date);	
			} 
			return $item['last_payment'];
		}	
	}

	public function column_name( $item) {
		if (isset($_REQUEST['page'])) {
			$page = sanitize_text_field($_REQUEST['page']);
		}
		
		if (isset($_GET['status'])) {
			$actions['untrash'] = sprintf('<a href="?page=%s&action=untrash&id=%s">%s</a>', $page, $item['id'], __('Restore', 'CC'));
			$actions['delete'] = sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $page, $item['id'], __('Delete Permanently', 'CC'));
		} else {
			$actions = array(
			  'Trash' => sprintf('<a href="?page=%s&action=trash&id=%s">%s</a>', $page, $item['id'], __('Trash', 'CC'))
			);
		}
		return sprintf('%s %s', $item['id'], $this->row_actions($actions));
	}

	public function get_columns( $screen = '') {

		$columns = array(
			//'cb' 					=> '<input type="checkbox" />', //Render a checkbox instead of text
			'id' 					=> __('ID', 'CC'),
			'user_nicename' 		=> __('Customer Name', 'CC'),
			'user_email' 			=> __('Customer Email', 'CC'),
			'credit_limit' 			=> __('Credit Limit', 'CC'),
			'credit_balance'		=> __('Credit Balance', 'CC'),
			'available_credit'		=> __('Available Credit', 'CC'),
			'last_payment' 			=> __('Last Payment', 'CC'),
			);
			return $columns;
	}

	public function get_sortable_columns( $screen = '') {
		$sortable_columns = array(
				'id' 					=> array('ID', true),
				'user_nicename' 		=> array('user_nicename', true),
				'user_email' 			=> array('user_email', true),
				'credit_limit' 			=> array('credit_limit', true),
				'credit_balance'		=> array('credit_balance', true),
				'available_credit'		=> array('available_credit', true),
				'last_payment' 			=> array('last_payment', true),
			);
		return $sortable_columns;
	}

	public function get_bulk_actions() {
		$actions = array('update_cust_bal' => 'Update Balance');
		return $actions;
	}

	public function process_bulk_action() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'reward_points_transaction_history'; // do not forget about tables prefix
	}

	public function prepare_items( $search = '') {
		global $wpdb;
		$sql = '';
		$db_balances_table 		= 'aw_company_credit_balance';
		$db_transcation_table 	= 'aw_company_credit_history';
		$db_user_table 			= 'users';
		$db_user_meta_table		= 'usermeta';

		$search = trim($search);

		$total_items 			= self::get_count($db_balances_table, $search, '');
		$id 		 			= 'id';
		$total_items 			= self::get_count($db_balances_table, $search, '');
		$per_page     			= $this->get_items_per_page( 'aw_cc_customers_per_page', 20 );
		$current_page 			= $this->get_pagenum();
		if (isset($_REQUEST['paged'])) {
			$current_page 		= sanitize_text_field($_REQUEST['paged']);
		}
		$columns 				= $this->get_columns();
		//$this->process_bulk_action();
		$orderby        = ( isset($_REQUEST['orderby']) && in_array(sanitize_text_field($_REQUEST['orderby']), array_keys($this->get_sortable_columns())) ) ? sanitize_text_field($_REQUEST['orderby']) : $id;  
		$order        = ( isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc')) ) ? sanitize_text_field($_REQUEST['order']) : 'asc';
		$offset = ( (int) $current_page - 1 ) * (int) $per_page;
		
		if ('' != $search) {
			$this->items = $wpdb->get_results($wpdb->prepare("SELECT u.ID AS id, u.user_email AS user_email, b.credit_balance AS credit_balance, 
				b.available_credit AS available_credit, b.credit_limit AS credit_limit, IFNULL(b.last_payment,NULL) AS last_payment, 
				IFNULL(b.last_updated,'') AS last_updated, 
					(SELECT 
					Case WHEN CONCAT(umf.meta_value , ' ' , uml.meta_value) = '' 
					THEN u.display_name 
					ELSE CONCAT(umf.meta_value , ' ' , uml.meta_value) END AS user_nicename
					FROM ({$wpdb->prefix}%5s umf, {$wpdb->prefix}%5s uml) where u.ID = uml.user_id AND umf.user_id = u.ID AND umf.meta_key= %s 
					AND uml.meta_key=%s
					) AS user_nicename FROM {$wpdb->prefix}%5s as u 
				INNER JOIN {$wpdb->prefix}%5s as b ON b.user_id = u.ID HAVING user_nicename LIKE %s OR u.user_email  LIKE %s  ORDER BY %5s %5s LIMIT %d OFFSET %d", "{$db_user_meta_table}", "{$db_user_meta_table}", 'first_name', 'last_name', "{$db_user_table}", "{$db_balances_table}", "%{$search}%", "%{$search}%", "{$orderby}", "{$order}", "{$per_page}", "{$offset}") , ARRAY_A);

		} else {

			$this->items = $wpdb->get_results($wpdb->prepare("SELECT u.ID AS id, u.user_email AS user_email, b.credit_balance AS credit_balance, 
					b.available_credit AS available_credit, b.credit_limit AS credit_limit, IFNULL(b.last_payment,NULL) AS last_payment, 
					b.last_updated AS last_updated, 
						(SELECT 
						Case WHEN CONCAT(umf.meta_value , ' ' , uml.meta_value) = '' 
						THEN u.display_name 
						ELSE CONCAT(umf.meta_value , ' ' , uml.meta_value) END AS user_nicename
						FROM ({$wpdb->prefix}%5s umf, {$wpdb->prefix}%5s uml) where u.ID = uml.user_id AND umf.user_id = u.ID AND umf.meta_key= %s 
						AND uml.meta_key=%s
						) AS user_nicename FROM {$wpdb->prefix}%5s as u 
					INNER JOIN {$wpdb->prefix}%5s as b ON b.user_id = u.ID ORDER BY %5s %5s LIMIT %d OFFSET %d", "{$db_user_meta_table}", "{$db_user_meta_table}", 'first_name', 'last_name', "{$db_user_table}", "{$db_balances_table}", "{$orderby}", "{$order}", "{$per_page}", "{$offset}") , ARRAY_A);
		}
			 
		$this->set_pagination_args(array(
			'total_items'   => $total_items,
			'per_page'      => $per_page,
			'total_pages'   => ceil($total_items / $per_page),
		));
		return $this->items;
	}
	 

	public function get_count( $table_name = '', $search = '', $screen = '') {
		global $wpdb;
		$join = 'INNER JOIN';
		if ('' != $search) {
			$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ({$wpdb->prefix}usermeta umf , {$wpdb->prefix}usermeta uml) %5s  {$wpdb->prefix}%5s AS b ON umf.user_id = b.user_id  WHERE umf.user_id = uml.user_id AND umf.meta_key = %s AND uml.meta_key =%s AND CONCAT(umf.meta_value , ' ' , uml.meta_value) LIKE %s", "{$join}", "{$table_name}", 'first_name', 'last_name', "%{$search}%" ));
		} else {
			$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ({$wpdb->prefix}usermeta umf , {$wpdb->prefix}usermeta uml) %5s  {$wpdb->prefix}%5s AS b ON umf.user_id = b.user_id  WHERE umf.user_id = uml.user_id AND umf.meta_key = %s AND uml.meta_key =%s", "{$join}", "{$table_name}", 'first_name', 'last_name' ));	
			
		}
		return $total_items;
	}
} 

