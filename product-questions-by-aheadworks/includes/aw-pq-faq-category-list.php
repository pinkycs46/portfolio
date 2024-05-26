<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
if (!class_exists('WP_List_Table')) {
	require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class AwPqFaqCategoryList extends WP_List_Table {

	public function __construct() {
		global $status, $page;
		parent::__construct(array(
			'singular' => __('Categories', 'RD'),
			'plural' => __('Categories', 'RD'),
			'ajax'   => true
		));
	}

	public function column_default( $item, $column_name) {
		return $item[$column_name]; 
	}

	public function column_category_name( $item) {
		if (isset($_REQUEST['page'])) {
			$page = sanitize_text_field($_REQUEST['page']);
		}
		 
		if (isset($_GET['status'])) {
			$val = sanitize_text_field($_GET['status']);
			if (1 != $val) {
				$actions['untrash'] = sprintf('<a href="?page=%s&action=untrash&id=%s">%s</a>', $page, $item['id'], __('Restore', 'RD'));
				$actions['delete'] = sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $page, $item['id'], __('Delete Permanently', 'RD'));
			} else {
					$actions = array(
				'Edit' => sprintf('<a href="?page=faq_category_page&action=edit&id=%s">%s</a>', $item['id'], __('Edit', 'RD')),
				'Enable' => sprintf('<a href="?page=%s&action=enable&id=%s">%s</a>', $page, $item['id'], __('Enable', 'RD')),
				'Disable' => sprintf('<a href="?page=%s&action=disable&id=%s">%s</a>', $page, $item['id'], __('<span style="color:red">Disable</span>', 'RD')),
				'Trash' => sprintf('<a href="?page=%s&action=trash&id=%s">%s</a>', $page, $item['id'], __('<span style="color:red">Trash</span>', 'RD'))
				);
					if (1 == $item['status']) {
						unset($actions['Enable']);
					}
					if (2 == $item['status']) {
						unset($actions['Disable']);
					}
			}
		} else {

			$actions = array(
				'Edit' => sprintf('<a href="?page=faq_category_page&action=edit&id=%s">%s</a>', $item['id'], __('Edit', 'RD')),
				'Enable' => sprintf('<a href="?page=%s&action=enable&id=%s">%s</a>', $page, $item['id'], __('Enable', 'RD')),
				'Disable' => sprintf('<a href="?page=%s&action=disable&id=%s">%s</a>', $page, $item['id'], __('<span style="color:red">Disable</span>', 'RD')),
				'Trash' => sprintf('<a href="?page=%s&action=trash&id=%s">%s</a>', $page, $item['id'], __('<span style="color:red">Trash</span>', 'RD'))
			);
			if (1 == $item['status']) {
				unset($actions['Enable']);
			}
			if (2 == $item['status']) {
				unset($actions['Disable']);
			}
		}
		return sprintf('%s %s', $item['category_name'], $this->row_actions($actions));
	}
	public function column_status( $item) {

		if (1 == $item['status']) {
			$item['status'] = '<span style="color:green">Enable</span>';
		} else {
			$item['status'] = '<span style="color:red">Disable</span>';
		}
		return $item['status'];
	}
	public function column_cb( $item) {
		if (isset($item['id'])) {
			return sprintf(
			'<input type="checkbox" name="id[]" value="%s" />',
			$item['id']);
		}
	}
	public function get_columns() {
		$columns = array(
			'cb' 					=> '<input type="checkbox" />', //Render a checkbox instead of text
			'id' 					=> __('ID', 'RD'),
			'category_name' 		=> __('Category Name', 'RD'),
			'category_slug' 		=> __('Slug', 'RD'),
			'status' 				=> __('Status', 'RD'),
			'sort_order' 			=> __('Sort Order', 'RD'),
			'date' 					=> __('Date', 'RD'),
			);
			return $columns;
	}
	public function get_sortable_columns() {
		$sortable_columns = array(
			'id' 					=> array('id', true),
			'category_name' 		=> array('category_name', true),
			'category_slug' 		=> array('category_slug', true),
			'status' 				=> array('status', true),
			'sort_order' 			=> array('sort_order', true),
			'date' 					=> array('date', true)
			);
		return $sortable_columns;
	}

	public function get_bulk_actions() {
		$actions = array(
			'enable' 	=> 'Enable',
			'disable' 	=> 'Disable',
			'untrash'	=> 'Restore',
			'trash' 	=> 'Move to Trash',
			'delete'	=> 'Delete Permanently',
			
		);
		
		if (!isset($_GET['status']) ) {
			unset($actions['untrash']);
			unset($actions['delete']);
		} 
		if (isset($_GET['status'])) {
			$val = sanitize_text_field($_GET['status']);
			if (1 != $val) {
				unset($actions['trash']);
				unset($actions['enable']);
				unset($actions['disable']);
			} else {
				unset($actions['untrash']);
				unset($actions['delete']);
			}
		}
		return $actions;
	}

	public function process_bulk_action() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'aw_pq_faq_category_list'; // do not forget about tables prefix

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
				$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aw_pq_faq_category_list SET status=0  WHERE id IN(%5s)" , "{$ids}"));
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
				$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}aw_pq_faq_category_list  WHERE id IN(%5s)" , $ids));
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
				$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aw_pq_faq_category_list SET status=1 WHERE id IN(%5s)" , $ids));
			}
		}

		if ('enable' === $this->current_action()) {
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
				$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aw_pq_faq_category_list SET status=1 WHERE id IN(%5s)" , $ids));
			}
		}
		if ('disable' === $this->current_action()) {
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
				$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aw_pq_faq_category_list SET status=2 WHERE id IN(%5s)" , $ids));
			}
		}
	}
	public function prepare_items( $status = '', $search = '') {
		global $wpdb;

		$search 		= trim($search);
		$per_page     	= $this->get_items_per_page( 'categories_per_page', 20 );
		$total_items 	= self::get_count($status, $search);
		$current_page 	= $this->get_pagenum();
		if (isset($_REQUEST['paged'])) {
			$current_page 		= sanitize_text_field($_REQUEST['paged']);
		}

		$sortable = $this->get_sortable_columns();
		$columns = $this->get_columns();
		$hidden =  get_hidden_columns($this->screen);
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->process_bulk_action();
		$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM  {$wpdb->prefix}aw_pq_faq_category_list  WHERE status=%d", 1));

		if (isset($_GET['status'])) { 
			$sanitize_status = sanitize_text_field($_GET['status']);
			$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM {$wpdb->prefix}aw_pq_faq_category_list WHERE status =%s", "{$sanitize_status}"));
		}

		$orderby = ( isset($_REQUEST['orderby']) && in_array(sanitize_text_field($_REQUEST['orderby']), array_keys($this->get_sortable_columns())) ) ? sanitize_text_field($_REQUEST['orderby']) : 'id';
		$order = ( isset($_REQUEST['order']) && in_array(sanitize_text_field($_REQUEST['order']), array('asc', 'desc')) ) ? sanitize_text_field($_REQUEST['order']) : 'ASC';
		$offset = ( (int) $current_page - 1 ) * (int) $per_page;

		if ('' != $search) {
			$this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pq_faq_category_list WHERE status IN(%d ,%d)  AND ( category_name LIKE %s OR category_slug LIKE %s ) ORDER BY %5s  %5s LIMIT %d OFFSET %d", 1, 2, "{$search}%", "{$search}%", "{$orderby}", "{$order}", "{$per_page}", "{$offset}"), ARRAY_A);			

		} else {
			if (''==$status) {
				$status='1,2';
				$this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pq_faq_category_list WHERE status IN(%d ,%d) ORDER BY %5s %5s LIMIT %d OFFSET %d", 1, 2, "{$orderby}", "{$order}", "{$per_page}", "{$offset}"), ARRAY_A);
			} else {
				$this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pq_faq_category_list WHERE status = %d ORDER BY %5s %5s LIMIT %d OFFSET %d", "{$status}", "{$orderby}", "{$order}", "{$per_page}", "{$offset}"), ARRAY_A);
			}
			
		}
		$this->set_pagination_args(array(
			'total_items'   => $total_items,
			'per_page'      => $per_page,
			'total_pages'   => ceil($total_items / $per_page),
			));
	}

	public function get_count( $status, $search = '') {
		global $wpdb;
		if ('' != $search) {
			$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}aw_pq_faq_category_list WHERE status=%d LIKE %s" , "{$status}", "%{$search}%" ));
		} else {
			$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*)  FROM {$wpdb->prefix}aw_pq_faq_category_list WHERE status=%d", "{$status}"));
		}		
		return $total_items;
	}	

}
