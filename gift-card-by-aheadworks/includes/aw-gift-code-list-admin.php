<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
if (!class_exists('WP_List_Table')) {
	require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class AwGiftCardList extends WP_List_Table {

	public function __construct() {
		global $status, $page;

		parent::__construct(array(
			'singular' => 'giftcard',
			'plural' => 'giftcards',
		) );
		
	}
	public function column_default( $item, $column_name) {
		if ('expiration_date' == $column_name) {
			if (null != $item['expiration_date']) {
				$date = gmdate('d/m/Y', strtotime($item['expiration_date']));

			} else {
				$date = '&mdash;';
			}
			$item[$column_name] = $date;
		}
		
		if (isset($item['giftcard_amount']) && 'giftcard_amount' == $column_name) {
			$sale = aw_gc_get_amount($item[$column_name]);
			$item[$column_name] = $sale;
		}

		if (isset($item['giftcard_used_amount']) && 'giftcard_used_amount' == $column_name) {
			$sale = aw_gc_get_amount($item[$column_name]);
			$item[$column_name] = $sale;
		}
		if (isset($item['giftcard_product_name']) && 'giftcard_product_name' == $column_name) {
			$parent_id = wp_get_post_parent_id($item['product_id']);
			$link = '<a href="' . get_edit_post_link($parent_id) . '">' . $item[$column_name] . '</a>';			
			$item[$column_name] = $link;
		}
		if (isset($item['giftcard_code']) && 'giftcard_code' == $column_name) {
			$link = '<a href="' . menu_page_url('giftcard-detail-page', false) . '&giftcode_id=' . base64_encode($item['id']) . '">' . $item[$column_name] . '</a>';
			$item[$column_name] = $link;
		}
		return $item[$column_name];
	}

	public function column_cb( $item) {
		return sprintf(
			'<input type="checkbox" name="id[]" value="%s" />',
			$item['id']
		);
	}

	public function get_columns() {
		$columns = array(
			'cb' 					=> '<input type="checkbox" />', //Render a checkbox instead of text
			'id'					=> __('ID', 'aw_code_table'),
			'giftcard_code'			=> __('Code Number', 'aw_code_table'),
			'giftcard_product_name'	=> __('Gift Card Name', 'aw_code_table'),
			'giftcard_amount'		=> __('Purchased Amount', 'aw_code_table'),
			'giftcard_used_amount'	=> __('Used Amount', 'aw_code_table'),
			'expiration_date'		=> __('Expiration Date', 'aw_code_table'),
			'transaction_action'	=> __('Status', 'aw_code_table'),
			'sender_name'			=> __('Sender Name', 'aw_code_table'),
			'sender_email'			=> __('Sender Email', 'aw_code_table'),
			'recipient_name'		=> __('Recipient Name', 'aw_code_table'),
			'recipient_email'		=> __('Recipient Email', 'aw_code_table'),
		);
		return $columns;
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
			'id'					=> array('id',true),
			'giftcard_code'			=> array('giftcard_code',true),
			'giftcard_product_name'	=> array('giftcard_product_name',true),
			'giftcard_amount'		=> array('giftcard_amount',true),
			'giftcard_used_amount'	=> array('giftcard_used_amount',true),
			'expiration_date'		=> array('expiration_date',true),
			'transaction_action'	=> array('transaction_action',true),
			'sender_name'			=> array('sender_name',true),
			'sender_email'			=> array('sender_email',true),
			'recipient_name'		=> array('recipient_name',true),
			'recipient_email'		=> array('recipient_email',true),
		);
		return $sortable_columns;
	}

	public function get_bulk_actions() {
		$actions = array(
			'untrash'=>'Restore',
			'delete'=>'Delete Permanently',
			'trash' => 'Move to Trash',
		);
		if (!isset($_GET['status'])) {
			unset($actions['untrash']);
			unset($actions['delete']);
		} else {
			unset($actions['trash']);
		}
		return $actions;
	}

	public function process_bulk_action() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'aw_gc_codes'; // do not forget about tables prefix
		if ('trash' === $this->current_action()) {
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
				$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aw_gc_codes SET giftcard_trash_status=0  WHERE id IN(%5s)" , "{$ids}"));
			}
		}
		if ('delete' === $this->current_action()) {
			if (isset($_REQUEST['id'])) {
				$ids = json_encode($_REQUEST);
				$ids = wp_unslash($ids);
				$ids = json_decode($ids, true);
				$ids = $ids['id'];
			} else {
				$ids =  array();	
			}
			
			if (is_array($ids)) {
				$ids = implode(',', $ids);
			}

			if (!empty($ids)) {
				$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}aw_gc_codes WHERE id IN(%5s)" , $ids));
				$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}aw_gc_transactions WHERE giftcard_id IN(%5s)" , $ids));
				$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}aw_gc_users_gift_card WHERE aw_gc_codes_id IN(%5s)" , $ids));
			}
		}
		if ('untrash' === $this->current_action()) {
			if (isset($_REQUEST['id'])) {
				$ids = json_encode($_REQUEST);
				$ids = wp_unslash($ids);
				$ids = json_decode($ids, true);
				$ids = $ids['id'];
			} else {
				$ids =  array();	
			}
			if (is_array($ids)) {
				$ids = implode(',', $ids);
			}
			if (!empty($ids)) {
				$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aw_gc_codes SET giftcard_trash_status=1 WHERE id IN(%5s)" , $ids));
			}
		}
	}

	public function prepare_items( $status = 1) {
		global $wpdb;
		$user 		= get_current_user_id();
		$screen 	= get_current_screen();
		$option 	= $screen->get_option('per_page', 'option');

		$columns 	= $this->get_columns();
		$hidden 	= array();
		$sortable 	= $this->get_sortable_columns();
		$hidden 	= get_hidden_columns($this->screen);
		$columns 	= $this->get_columns($screen);

		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->process_bulk_action();

		$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM  {$wpdb->prefix}aw_gc_codes WHERE giftcard_trash_status=%d", "{$status}"));

		if (isset($_GET['status'])) { 
			$sanitize_status = sanitize_text_field($_GET['status']);
			$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM {$wpdb->prefix}aw_gc_codes WHERE giftcard_trash_status =%d", "{$sanitize_status}"));
		}

		$per_page = $this->get_items_per_page( 'aw_gc_codes_per_page', 20 );
		$paged = isset($_REQUEST['paged']) ? ( $per_page * max(0, intval($_REQUEST['paged']) - 1) ) : 0;
		$orderby = ( isset($_REQUEST['orderby']) && in_array(sanitize_text_field($_REQUEST['orderby']), array_keys($this->get_sortable_columns())) ) ? sanitize_text_field($_REQUEST['orderby']) : 'id';
		$order = ( isset($_REQUEST['order']) && in_array(sanitize_text_field($_REQUEST['order']), array('asc', 'desc')) ) ? sanitize_text_field($_REQUEST['order']) : 'DESC';

		if (isset($_GET['s'])) {
			$value = sanitize_text_field($_GET['s']);
			$this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_codes WHERE giftcard_trash_status = %d  AND ( giftcard_code LIKE %s OR giftcard_product_name LIKE %s) ORDER BY %5s %5s LIMIT %d OFFSET %d", "{$status}", "%{$value}%", "%{$value}%", "{$orderby}", "{$order}", "{$per_page}", "{$paged}"), ARRAY_A);
		} else {
			$this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_gc_codes 
			WHERE giftcard_trash_status = %d ORDER BY %5s %5s LIMIT %d OFFSET %d", "{$status}", "{$orderby}", "{$order}", "{$per_page}", "{$paged}"), ARRAY_A);
		}
		
		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page' => $per_page,
			'total_pages' => ceil($total_items / $per_page)
		));
	}

	public function get_count( $status) {
		global $wpdb;
		$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM {$wpdb->prefix}aw_gc_codes WHERE giftcard_trash_status =%d", "{$status}"));
		return $total_items;
	}

	public static function aw_gc_register_card_detail_page() {
		add_submenu_page('admin.php?page=giftcard-detail-page', 'Gift Card Detail', 'Gift Card Detail', 'manage_options' , 'giftcard-detail-page', array('AwGiftCardList', 'aw_gift_card_detail_page' ));

		remove_menu_page('admin.php?page=giftcard-detail-page', 'giftcard-detail-page');
	}

	public static function aw_gift_card_detail_page() {
		 require_once(plugin_dir_path(__FILE__) . 'aw-gift-card-code-details.php');
	}
}
