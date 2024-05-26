<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
if (!class_exists('WP_List_Table')) {
	require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class AwRewardTransactionBalance extends WP_List_Table {

	public function __construct() {
		global $status, $page;
		parent::__construct(array(
			'singular' => __('Transaction', 'RD'),
			'plural' => __('Transactions', 'RD'),
			'ajax'   => true
		));
	}

	public function column_default( $item, $column_name) {
		if (isset($item['id']) && 'id' == $column_name) {
			return $item['id']; 
		}

		if (isset($item['user_nicename']) && 'user_nicename' == $column_name) {
			global $wpdb;
			$url = get_site_url() . '/wp-admin/admin.php?page=reward-transaction-balance';
			$table_name  = $wpdb->prefix . 'reward_points_transaction_history';
			//$total_items = $wpdb->get_var("SELECT COUNT(*) FROM ".$table_name." WHERE user_id=".$item['id']);
			$linkeddata = $item['user_nicename'];
			//if($total_items>0)
			//{
				$linkeddata = '<a href="' . $url . '&s=' . urlencode($item['user_nicename']) . '&screen=transaction-tab&d=">' . $item['user_nicename'] . '</a>';
			//}
			$item['user_nicename'] = $linkeddata;
			return $item['user_nicename'];
		}

		if (isset($item['user_email']) && 'user_email' == $column_name) {
			return $item['user_email']; 
		}

		if (isset($item['lifetime_sale']) && 'lifetime_sale' == $column_name) {
			//$sale = get_woocommerce_currency_symbol() . $item['lifetime_sale'];
			$sale = sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), $item['lifetime_sale']);
			$item['lifetime_sale'] = $sale;
			return $item['lifetime_sale'];
		}

		if (isset($item['balance']) && 'balance' == $column_name) {
			return $item['balance'];
		}

		if ('balance_change' == $column_name && isset($item['points_type'])) {
			switch ($item['points_type']) {
				case 'Earn':
				case 'Admin Earn':
				case 'Reverse Spent':   
					$color  = 'green';
										$symbol = '+';
					break;
				case 'Spent':
				case 'Admin Reverse Earn':
				case 'Reverse Earn':
				case 'Expired':    
					$color  = 'red';
										$symbol = '';         
					break;
			}
			$item[$column_name] = "<span style='color:$color;'> $symbol" . $item[$column_name] . '</span>';
			 return $item[$column_name]; 
		}

		if (isset($item['earnedpoints']) && 'earnedpoints' == $column_name) {
			return $item['earnedpoints']; 
		}

		if (isset($item['spendpoints']) && 'spendpoints' == $column_name) {
			return $item['spendpoints']; 
		}

		if (isset($item['expiration_date']) && 'expiration_date' == $column_name) {
			if (null != $item['expiration_date']) {
				$date = gmdate('d.m.Y', strtotime($item['expiration_date']));

			} else {
				$date = '';
			}
			$item['expiration_date'] = $date;
			return $item['expiration_date'];
		}

		if ('id_t' == $column_name && isset($item['transaction_id'])) {
			$item['id_t'] = $item['transaction_id'];
			return $item['id_t'];
		}

		if (isset($item['user_nicename_t']) && 'user_nicename_t' == $column_name) {
			return $item['user_nicename_t']; 
		}

		if (isset($item['user_email_t']) && 'user_email_t' == $column_name) {
			return $item['user_email_t']; 
		}

		if (isset($item['comments']) && 'comments' == $column_name) {
			return $item['comments']; 
		}
		
		if (isset($item['transaction_description']) && 'transaction_description' == $column_name) {
			return $item['transaction_description']; 
		}
		
		if (isset($item['balance_change']) && 'balance_change' == $column_name) {
			return $item['balance_change']; 
		} 

		if (isset($item['transaction_balance']) && 'transaction_balance' == $column_name) {
			return $item['transaction_balance']; 
		}  

		if (isset($item['transaction_date']) && 'transaction_date' == $column_name) {
			$show_date = strtotime($item['transaction_date']);
			$item['transaction_date'] = gmdate('M d, Y h:i:s A', $show_date);
			return $item['transaction_date'];
		}
		//return $item[$column_name];
	}

	public function column_name( $item) {
		if (isset($_REQUEST['page'])) {
			$page = sanitize_text_field($_REQUEST['page']);
		}
		
		if (isset($_GET['status'])) {
			$actions['untrash'] = sprintf('<a href="?page=%s&action=untrash&id=%s">%s</a>', $page, $item['id'], __('Restore', 'RD'));
			$actions['delete'] = sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $page, $item['id'], __('Delete Permanently', 'RD'));
		} else {
			$actions = array(
			  'Trash' => sprintf('<a href="?page=%s&action=trash&id=%s">%s</a>', $page, $item['id'], __('Trash', 'RD'))
			);
		}
		return sprintf('%s %s', $item['id'], $this->row_actions($actions));
	}

	public function column_cb( $item) {
		if (isset($item['id'])) {
			return sprintf(
			'<input type="checkbox" name="id[]" value="%s" />',
			$item['id']);
		}
	}

	public function get_columns( $screen = '') {
		$columns = array(
			'cb' 					=> '<input type="checkbox" />', //Render a checkbox instead of text
			'id' 					=> __('ID', 'RD'),
			'user_nicename' 		=> __('Customer Name', 'RD'),
			'user_email' 			=> __('Customer Email', 'RD'),
			'lifetime_sale' 		=> __('Lifetime Purchase', 'RD'),
			'balance' 				=> __('Points Balance', 'RD'),
			'earnedpoints' 			=> __('Earned Points', 'RD'),
			'spendpoints' 			=> __('Spent Points', 'RD'),
			'expiration_date' 		=> __('Expiration Date', 'RD'),

			'id_t' 					=> __('ID', 'RD'),
			'user_nicename_t' 		=> __('Customer Name', 'RD'),
			'user_email_t' 			=> __('Customer Email', 'RD'),
			'comments' 				=> __('Comment To Admin', 'RD'),
			'transaction_description' => __('Transaction Description', 'RD'),
			'balance_change' 		=> __('Balance Change', 'RD'),
			'transaction_balance' 	=> __('Balance', 'RD'),
			'transaction_date' 		=> __('Transaction Date', 'RD'),
			);
			return $columns;
	}

	public function get_sortable_columns( $screen = '') {
		$sortable_columns = array(
			'id' 					=> array('id', true),
			'user_nicename' 		=> array('user_nicename', true),
			'user_email' 			=> array('user_email', true),
			'id_t' 					=> array('transaction_id', true),
			'user_nicename_t' 		=> array('user_nicename_t', true),
			'user_email_t' 			=> array('user_email_t', true),
			'comments' 				=> array('comments', true),
			'transaction_description' => array('transaction_description', true),
			'lifetime_sale' 		=> array('lifetime_sale', true),
			'balance' 				=> array('balance', true),
			'earnedpoints' 			=> array('earnedpoints', true),
			'spendpoints' 			=> array('spendpoints', true),
			'expiration_date' 		=> array('expiration_date', true),
			'balance_change' 		=> array('balance_change', true),
			'transaction_balance' 	=> array('transaction_balance', true),
			'transaction_date' 		=> array('transaction_date', true)
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

	public function prepare_items( $screen = '', $search = '') {
		global $wpdb;
		$sql = '';
		//$db_balances_table 		= $wpdb->prefix . 'reward_points_balances';
		//$db_transcation_table 	= $wpdb->prefix . 'reward_points_transaction_history';
		//$db_user_table 			= $wpdb->prefix . 'users';
		//$db_user_meta_table		= $wpdb->prefix . 'usermeta';
		$db_balances_table 		= 'reward_points_balances';
		$db_transcation_table 	= 'reward_points_transaction_history';
		$db_user_table 			= 'users';
		$db_user_meta_table		= 'usermeta';

		$search = trim($search);
		if ('balance-tab' === $screen) {
			$table_name  = 'reward_points_balances';
			$id 		 = 'id';
			$total_items = self::get_count($table_name, $search, $screen);
		} else {
			$table_name  = 'reward_points_transaction_history';
			$id          = 'transaction_id';
			$total_items = self::get_count($table_name, $search, $screen);
		}

		//$users_table 			= $wpdb->prefix . 'users';
		$users_table 			= 'users';
		$per_page     			= $this->get_items_per_page( 'customers_per_page', 20 );
		$current_page 			= $this->get_pagenum();
		if (isset($_REQUEST['paged'])) {
			$current_page 		= sanitize_text_field($_REQUEST['paged']);
		}
		$sortable 				= $this->get_sortable_columns($screen);
		$hidden 				= get_hidden_columns($this->screen);
		$columns 				= $this->get_columns($screen);
		$this->_column_headers 	=  array($columns, $hidden, $sortable);
		$this->process_bulk_action();
		if (isset($_REQUEST['screen']) && trim($screen) != $_REQUEST['screen']) {
			$orderby        = $id;
		} else {
			$orderby        = ( isset($_REQUEST['orderby']) && in_array(sanitize_text_field($_REQUEST['orderby']), array_keys($this->get_sortable_columns())) ) ? sanitize_text_field($_REQUEST['orderby']) : $id;    
		}

		$order        = ( isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc')) ) ? sanitize_text_field($_REQUEST['order']) : 'asc';
		$offset = ( (int) $current_page - 1 ) * (int) $per_page;
		if (trim($screen) === 'balance-tab') {
			$this->items = $wpdb->get_results($wpdb->prepare("SELECT u.ID AS id, u.user_email AS user_email, IFNULL(b.lifetime_sale,0) AS lifetime_sale, 
					IFNULL(b.earnedpoints,0) AS earnedpoints, IFNULL(b.spendpoints,0) AS spendpoints, IFNULL(b.balance,0) AS balance, 
					b.expiration_date AS expiration_date, IFNULL(b.transaction_date,0) AS transaction_date, 
					IFNULL(b.last_updated,0) AS last_updated, 
						(SELECT 
						Case WHEN CONCAT(umf.meta_value , ' ' , uml.meta_value) = '' 
						THEN u.display_name 
						ELSE CONCAT(umf.meta_value , ' ' , uml.meta_value) END AS user_nicename
						FROM ({$wpdb->get_blog_prefix(1)}%5s umf, {$wpdb->get_blog_prefix(1)}%5s uml) where u.ID = uml.user_id AND umf.user_id = u.ID AND umf.meta_key= %s 
						AND uml.meta_key=%s
						) AS user_nicename FROM {$wpdb->get_blog_prefix(1)}%5s as u 
					LEFT JOIN {$wpdb->prefix}%5s as b ON b.user_id = u.ID ORDER BY %5s %5s LIMIT %d OFFSET %d", "{$db_user_meta_table}", "{$db_user_meta_table}", 'first_name', 'last_name', "{$db_user_table}", "{$table_name}", "{$orderby}", "{$order}", "{$per_page}", "{$offset}") , ARRAY_A);	//PHPCS: unprepared SQL OK
		}


		if (trim($screen) === 'transaction-tab') {
			$this->items = $wpdb->get_results($wpdb->prepare("SELECT u.ID as id_t,(SELECT 
                        Case WHEN CONCAT(umf.meta_value , ' ' , uml.meta_value) = '' 
                        THEN u.display_name 
                        ELSE CONCAT(umf.meta_value , ' ' , uml.meta_value) END AS user_nicename
                        FROM ({$wpdb->get_blog_prefix(1)}%5s umf, {$wpdb->get_blog_prefix(1)}%5s uml) where u.ID = uml.user_id AND umf.user_id = u.ID AND umf.meta_key= %s 
                        AND uml.meta_key= %s 
                        ) AS user_nicename_t, 
                    u.user_email AS user_email_t , b.transaction_id,b.user_id,b.order_id,b.points_type,b.transaction_balance,b.transaction_description,b.balance_log,b.order_status,b.transaction_date,b.comments,b.last_updated,
					CASE WHEN b.points_type = %s THEN b.balance_change 
					WHEN b.points_type = %s THEN b.balance_change
					WHEN b.points_type = %s 
					THEN b.balance_change ELSE -b.balance_change END AS balance_change FROM ({$wpdb->get_blog_prefix(1)}%5s umf, {$wpdb->get_blog_prefix(1)}%5s uml) 
                    INNER JOIN {$wpdb->prefix}%5s AS b ON umf.user_id = b.user_id 
                    INNER JOIN {$wpdb->get_blog_prefix(1)}%5s AS u ON b.user_id = u.id 
                    WHERE umf.user_id = uml.user_id AND umf.meta_key = %s AND uml.meta_key = %s ORDER BY %5s %5s LIMIT %d OFFSET %d", "{$db_user_meta_table}", "{$db_user_meta_table}", 'first_name', 'last_name', 'Earn', 'Admin Earn', 'Reverse Spent', "{$db_user_meta_table}", "{$db_user_meta_table}", "{$table_name}", "{$db_user_table}", 'first_name', 'last_name', "{$orderby}", "{$order}", "{$per_page}", "{$offset}") , ARRAY_A);
		}

		if ('' != $search) {
			if (trim($screen) === 'balance-tab') {
				$this->items = $wpdb->get_results($wpdb->prepare("SELECT u.ID AS id, u.user_email AS user_email, IFNULL(b.lifetime_sale,0) AS lifetime_sale, 
					IFNULL(b.earnedpoints,0) AS earnedpoints, IFNULL(b.spendpoints,0) AS spendpoints, IFNULL(b.balance,0) AS balance, 
					b.expiration_date AS expiration_date, IFNULL(b.transaction_date,0) AS transaction_date, 
					IFNULL(b.last_updated,0) AS last_updated, 
						(SELECT 
						Case WHEN CONCAT(umf.meta_value , ' ' , uml.meta_value) = '' 
						THEN u.display_name 
						ELSE CONCAT(umf.meta_value , ' ' , uml.meta_value) END AS user_nicename
						FROM ({$wpdb->get_blog_prefix(1)}%5s umf, {$wpdb->get_blog_prefix(1)}%5s uml) where u.ID = uml.user_id AND umf.user_id = u.ID AND umf.meta_key= %s 
						AND uml.meta_key=%s
						) AS user_nicename FROM {$wpdb->get_blog_prefix(1)}%5s as u 
					LEFT JOIN {$wpdb->prefix}%5s as b ON b.user_id = u.ID HAVING user_nicename LIKE %s OR u.user_email  LIKE %s ORDER BY %5s %5s LIMIT %d OFFSET %d", "{$db_user_meta_table}", "{$db_user_meta_table}", 'first_name', 'last_name', "{$db_user_table}", "{$table_name}", "%{$search}%", "%{$search}%", "{$orderby}", "{$order}", "{$per_page}", "{$offset}") , ARRAY_A);

			}
			if (trim($screen) === 'transaction-tab') {
				$this->items = $wpdb->get_results($wpdb->prepare("SELECT u.ID as id_t,(SELECT 
                        Case WHEN CONCAT(umf.meta_value , ' ' , uml.meta_value) = '' 
                        THEN u.display_name 
                        ELSE CONCAT(umf.meta_value , ' ' , uml.meta_value) END AS user_nicename
                        FROM ({$wpdb->get_blog_prefix(1)}%5s umf, {$wpdb->get_blog_prefix(1)}%5s uml) where u.ID = uml.user_id AND umf.user_id = u.ID AND umf.meta_key= %s 
                        AND uml.meta_key= %s 
                        ) AS user_nicename_t, 
                    u.user_email AS user_email_t , b.transaction_id,b.user_id,b.order_id,b.points_type,b.transaction_balance,b.transaction_description,b.balance_log,b.order_status,b.transaction_date,b.comments,b.last_updated,
					CASE WHEN b.points_type = %s THEN b.balance_change 
					WHEN b.points_type = %s THEN b.balance_change
					WHEN b.points_type = %s 
					THEN b.balance_change ELSE -b.balance_change END AS balance_change FROM ({$wpdb->get_blog_prefix(1)}%5s umf, {$wpdb->get_blog_prefix(1)}%5s uml) 
                    INNER JOIN {$wpdb->prefix}%5s AS b ON umf.user_id = b.user_id 
                    INNER JOIN {$wpdb->get_blog_prefix(1)}%5s AS u ON b.user_id = u.id 
                    WHERE umf.user_id = uml.user_id AND umf.meta_key = %s AND uml.meta_key = %s HAVING user_nicename_t LIKE %s OR user_email_t LIKE %s OR b.order_id = %s ORDER BY %5s %5s LIMIT %d OFFSET %d", "{$db_user_meta_table}", "{$db_user_meta_table}", 'first_name', 'last_name', 'Earn', 'Admin Earn', 'Reverse Spent', "{$db_user_meta_table}", "{$db_user_meta_table}", "{$table_name}", "{$db_user_table}", 'first_name', 'last_name', "%{$search}%", "%{$search}%", "{$search}", "{$orderby}", "{$order}", "{$per_page}", "{$offset}") , ARRAY_A);
			}
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
		if (trim($screen)=== 'balance-tab') {
			$join = 'LEFT JOIN';
		}

		/*$table_prefix = $wpdb->prefix;
		if (is_multisite()) {
			$table_prefix = $wpdb->get_blog_prefix(1);
		}*/
		if ('' != $search) {
			$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ({$wpdb->get_blog_prefix(1)}usermeta umf , {$wpdb->get_blog_prefix(1)}usermeta uml) %5s  {$wpdb->prefix}%5s AS b ON umf.user_id = b.user_id  WHERE umf.user_id = uml.user_id AND umf.meta_key = %s AND uml.meta_key =%s AND CONCAT(umf.meta_value , ' ' , uml.meta_value) LIKE %s", "{$join}", "{$table_name}", 'first_name', 'last_name', "%{$search}%" ));
		} else {
			$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ({$wpdb->get_blog_prefix(1)}usermeta umf , {$wpdb->get_blog_prefix(1)}usermeta uml) %5s  {$wpdb->prefix}%5s AS b ON umf.user_id = b.user_id  WHERE umf.user_id = uml.user_id AND umf.meta_key = %s AND uml.meta_key =%s", "{$join}", "{$table_name}", 'first_name', 'last_name' ));	
		}
		return $total_items;
	}
}
