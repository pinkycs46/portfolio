<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
if (!class_exists('WP_List_Table')) {
	require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class AwccCreditHistoryListAdmin extends WP_List_Table {

	public function __construct() {
		global $status, $page;
		parent::__construct(array(
			'singular' 	=> __('Transaction', 'CC'),
			'plural' 	=> __('Transactions', 'CC'),
			'ajax'   	=> true
		));
		add_filter('set-screen-option', array(get_called_class(),'aw_cc_set_screen'), 11, 3);
	}

	public function column_default( $item, $column_name) {
		
		if (isset($item['id']) && 'id' == $column_name) {
			return $item['id']; 
		}
		if ( isset($item['last_payment']) && 'last_payment' == $column_name) {
				$show_date = strtotime($item['last_payment']);
			if (!empty($show_date)) {
				$item['last_payment'] = gmdate('M d, Y h:i:s A', $show_date);	
			} 
			return $item['last_payment'];
		}	
		if ( isset($item['credit_balance']) && !empty($item['credit_balance']) && 'credit_balance' == $column_name ) {
			if ($item['credit_balance']<0) {
				return '-' . aw_cc_get_amount(abs($item['credit_balance']));
			}
			return aw_cc_get_amount($item['credit_balance']);//$item['edit_action'];
		}
		if ( isset($item['available_credit']) &&  !empty($item['available_credit']) && 'available_credit' == $column_name ) {
			return aw_cc_get_amount($item['available_credit']);
		}
		if ( isset($item['credit_limit']) && !empty($item['credit_limit']) && 'credit_limit' == $column_name ) {
			return aw_cc_get_amount($item['credit_limit']);
		}
		
		if ( isset($item['transaction_amount']) && !empty($item['transaction_amount']) && 'transaction_amount' == $column_name ) {
			$transaction_amount = '';
 
			if ('Purchased' === $item['transaction_status']) {
				$transaction_amount = aw_cc_get_amount(abs($item['transaction_amount']));	
				$transaction_amount = '<span style="color:red">-' . $transaction_amount . '</span>';
			}
			if ('Updated' === $item['transaction_status']) {
				$transaction_amount = $item['transaction_amount'];
				if ($transaction_amount<0) {

					$transaction_amount = aw_cc_get_amount(abs($item['transaction_amount']));
					$transaction_amount = '<span style="color:red">-' . $transaction_amount . '</span>';
				} else {
					$transaction_amount = '<span style="color:green">+' . aw_cc_get_amount($transaction_amount) . '</span>';
				}
			}
			if ('Assigned' === $item['transaction_status'] || 'Changed' === $item['transaction_status']) {
				$transaction_amount = '';
			}
			$item['transaction_amount'] = $transaction_amount;
			return $item['transaction_amount'];
		}

		if ( isset($item['comment_to_customer']) && 'comment_to_customer' == $column_name && ( 'Purchased' === $item['transaction_status'] || 'Refunded' === $item['transaction_status'] || 'Cancelled' === $item['transaction_status'] )) {
			$url = get_site_url() . '/wp-admin/post.php?post=' . $item['order_id'] . '&action=edit';
			$ordercommenttext = explode('#', $item['comment_to_customer']);
			if (!empty($ordercommenttext)) {
				 
				$item['comment_to_customer'] = $ordercommenttext[0] . '<a href="' . $url . '&id=' . urlencode($ordercommenttext[1]) . '" target="_blank"> #' . $ordercommenttext[1] . '</a>';
			}
			
			return $item['comment_to_customer'];
		}
		return $item[$column_name];
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

	public function column_cb( $item) {
		if (isset($item['id'])) {
			return sprintf('<input type="checkbox" name="id[]" value="%s" />', $item['id']);
		}
	}

	/*	
	public function column_edit_action($item) {
		if (isset($item['edit_action'])) {
			$page= 'customer-credit-history';
			return sprintf('<a href="?page=%s&action=untrash&id=%s">%s</a>', $page, base64_encode($item['id']), __('Edit', 'CC'));
		}
	}
	*/

	public function get_columns() {
		$columns = array(
						'last_payment'			=> __('Date', 'CC'),
						'transaction_status' 	=> __('Action', 'CC'),
						'transaction_amount'	=> __('Amount', 'CC'),
						'credit_balance' 		=> __('Credit Balance', 'CC'),
						'available_credit'		=> __('Available Credit', 'CC'),
						'credit_limit'			=> __('Credit Limit', 'CC'),
						'comment_to_admin'		=> __('Comment to Admin', 'CC'),
						'comment_to_customer'	=> __('Comment to Customer', 'CC'),
						
					);
		return $columns;
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
				'last_payment'			=> array('last_payment', true),
				'transaction_status' 	=> array('transaction_status', true),
				'transaction_amount'	=> array('transaction_amount', true),
				'credit_balance' 		=> array('credit_balance', true),
				'available_credit'		=> array('available_credit', true),
				'credit_limit'			=> array('credit_limit', true),
				'comment_to_admin'		=> array('comment_to_admin', true),			
				'comment_to_customer'	=> array('comment_to_customer', true),
				
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

	public function prepare_items( $search = '', $user_id = '') {
		global $wpdb;
		$sql = '';
		$db_transcation_table 	= 'aw_company_credit_history';
		$db_user_table 			= 'users';
		$db_user_meta_table		= 'usermeta';

		$search = trim($search);
		$total_items = self::get_count($user_id, $search);
		$users_table 			= 'users';
		$per_page     			= $this->get_items_per_page( 'aw_cc_history_per_page', 20 );
		$current_page 			= $this->get_pagenum();
		if (isset($_REQUEST['paged'])) {
			$current_page 		= sanitize_text_field($_REQUEST['paged']);
		}
		$sortable 				= $this->get_sortable_columns();
		$columns 				= $this->get_columns();
		$this->process_bulk_action();
		$date 			= 'last_payment';
		$orderby        = ( isset($_REQUEST['orderby']) && in_array(sanitize_text_field($_REQUEST['orderby']), array_keys($this->get_sortable_columns())) ) ? sanitize_text_field($_REQUEST['orderby']) : $date;  

		$order = ( isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc')) ) ? sanitize_text_field($_REQUEST['order']) : 'desc';
		$offset= ( (int) $current_page - 1 ) * (int) $per_page;
		if ('' != $search) {
			$this->items = 	$wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}%5s WHERE user_id=%d AND (`transaction_status` LIKE %s OR `transaction_amount` LIKE %s OR `credit_balance` LIKE %s OR `available_credit` LIKE %s OR `credit_limit` LIKE %s OR `comment_to_customer` LIKE %s OR `comment_to_admin` LIKE %s OR `last_payment` LIKE %s) ORDER BY %5s %5s LIMIT %d OFFSET %d", "{$db_transcation_table}", "{$user_id}", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%" , "{$orderby}", "{$order}", "{$per_page}", "{$offset}"), ARRAY_A);	
		} else {
			$this->items = 	$wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}%5s WHERE user_id=%d  ORDER BY %5s %5s LIMIT %d OFFSET %d", "{$db_transcation_table}", "{$user_id}", "{$orderby}", "{$order}", "{$per_page}", "{$offset}"), ARRAY_A);	
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
			$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}aw_company_credit_history WHERE user_id = %d AND ( `last_payment` OR `transaction_status` OR `transaction_amount` OR `credit_balance` OR `available_credit` OR `credit_limit` OR `comment_to_customer` OR `comment_to_admin`) LIKE %s", "{$user_id}" , "{$search}"));
			  
		} else {
			$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}aw_company_credit_history WHERE user_id = %d", "{$user_id} "));	
		}
		return $total_items;
	}
} 

