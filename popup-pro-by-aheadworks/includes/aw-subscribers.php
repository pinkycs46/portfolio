<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if (!class_exists('WP_List_Table')) {
	require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class AwSubscribers extends WP_List_Table {

	public function __construct() {
		global $status, $page;
		parent::__construct(array(
			'singular' => 'subscriber',
			'plural' => 'subscribers',
		) );
	}

	public function column_default( $item, $column_name) {
		if ('date' == $column_name) {
			$show_date = strtotime($item[$column_name]);
			$item[$column_name] = gmdate('F d, Y h:y:s A', $show_date);
		}
		return $item[$column_name];
	}

 

	public function column_name( $item) {
		if (isset($_REQUEST['page'])) {
			$page = sanitize_text_field($_REQUEST['page']);
		}
		
		if (isset($_GET['status'])) {
			$actions['untrash']=sprintf('<a href="?post_type=popup-pro&page=%s&action=untrash&id=%s">%s</a>', $page, $item['id'], __('Restore', 'custom_table_example'));
			$actions['delete']=sprintf('<a href="?post_type=popup-pro&page=%s&action=delete&id=%s">%s</a>', $page , $item['id'], __('Delete Permanently', 'custom_table_example'));
		} else {
			$actions = array(
			  'Trash' => sprintf('<a href="?post_type=popup-pro&page=%s&action=trash&id=%s">%s</a>', $page , $item['id'], __('Trash', 'custom_table_example'))
			);
		}
		return sprintf('%s %s', $item['name'], $this->row_actions($actions));
	}

	public function column_cb( $item) {
		return sprintf(
			'<input type="checkbox" name="id[]" value="%s" />',
			$item['id']
		);
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
			'name' => __('Name', 'custom_table_example'),
			'email' => __('E-Mail', 'custom_table_example'),
			'date' => __('Subscribed On', 'custom_table_example'),
			'post_id' => __('Popup ID', 'custom_table_example'),
		);
		return $columns;
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
			'name' => array('name', true),
			'email'   => array('email', true),
			'date'    =>  array('date', true),
			'post_id' => array('post_id', true),
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

		$table_name = $wpdb->prefix . 'popup_pro_subscribes'; // do not forget about tables prefix
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
				$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}popup_pro_subscribes SET status=0  WHERE id IN(%5s)" , "{$ids}"));
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
				$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}popup_pro_subscribes  WHERE id IN(%5s)" , $ids));
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
				$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}popup_pro_subscribes SET status=1 WHERE id IN(%5s)" , $ids));
			}
		}
	}

	public function prepare_items( $status = 1) {
		global $wpdb;
		$per_page = 20;

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->process_bulk_action();

		$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM  {$wpdb->prefix}popup_pro_subscribes  WHERE status=%d", 1));
		if (isset($_GET['status'])) { 
			$sanitize_status = sanitize_text_field($_GET['status']);
			$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM {$wpdb->prefix}popup_pro_subscribes WHERE status =%s", "{$sanitize_status}"));
		}

		$paged = isset($_REQUEST['paged']) ? ( $per_page * max(0, intval($_REQUEST['paged']) - 1) ) : 0;
		$orderby = ( isset($_REQUEST['orderby']) && in_array(sanitize_text_field($_REQUEST['orderby']), array_keys($this->get_sortable_columns())) ) ? sanitize_text_field($_REQUEST['orderby']) : 'id';
		$order = ( isset($_REQUEST['order']) && in_array(sanitize_text_field($_REQUEST['order']), array('asc', 'desc')) ) ? sanitize_text_field($_REQUEST['order']) : 'DESC';

		if (isset($_GET['s'])) {
			$value = sanitize_text_field($_GET['s']);
			$this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}popup_pro_subscribes WHERE status = %d  AND ( name LIKE %s OR email LIKE %s OR post_id LIKE %d) ORDER BY %5s  %5s LIMIT %d OFFSET %d", "{$status}", "{$value}%", "{$value}%", "{$value}", "{$orderby}", "{$order}", "{$per_page}", "{$paged}"), ARRAY_A);

		} else {

			$this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}popup_pro_subscribes 
			WHERE status = %d ORDER BY %5s %5s LIMIT %d OFFSET %d", "{$status}", "{$orderby}", "{$order}", "{$per_page}", "{$paged}"), ARRAY_A);
		}

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page' => $per_page,
			'total_pages' => ceil($total_items / $per_page)
		));
	}

	public function get_count( $status) {
		global $wpdb;
		$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM {$wpdb->prefix}popup_pro_subscribes WHERE status=%d", "{$status}"));
		return $total_items;
	}
}
