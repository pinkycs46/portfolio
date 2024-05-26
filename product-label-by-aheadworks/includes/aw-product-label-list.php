<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
if (!class_exists('WP_List_Table')) {
	require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class AwProductLableList extends WP_List_Table {

	public function __construct() {
		global $status, $page;
		parent::__construct(array(
			'singular' => __('Labels', 'RD'),
			'plural' => __('Labels', 'RD'),
			'ajax'   => true
		));
	}
	public static function aw_pl_label_add_screen_option() {
		$option = 'per_page';
			$args = array(
				'label' => 'Number of items per page:',
				'default' => 20,
				'option' => 'labels_per_page'
			);
			add_screen_option( $option, $args );

			$table_bal = new AwProductLableList();
	}

	public function column_default( $item, $column_name) {	
		return $item[$column_name]; 
		
	}
	public static function column_label_image( $item) {
		$style = '';
		$textstyle = '';
		$design = '';
		$labeltext = 'Sale';
		$replace_str = array('{attribute: code}','{save_percent}','{save_amount}','{price}','{special_price}','{stock}','{sku}','{spdl}','{sphl}');

		/*if ('large' == $item['label_size'] && !empty($item['frontend_label_text'])) {
			$labeltext = $item['frontend_label_text'];
		}*/

		if (''!= $item['label_container_css']) {
			$style = $item['label_container_css'];	
		}
		if (''!= $item['label_text_css']) {
			$textstyle = $item['label_text_css'];
		}

		if ('picture' == $item['type']) {
			if (''!= $item['label_image'] && !empty($item['label_image'])) {
				return sprintf('<div class="td-img-label"><img src="%s" width = "60px" height = "60px" /><p class = "overlay-label" style ="%s">%s</p></div>', $item['label_image'], $textstyle, $labeltext);
			}
		} else if ('shape' == $item['type']) {
			
			if (''!= $item['shape_type'] && !empty($item['shape_type'])) {
				//return sprintf('<div class= "%s" style ="%s">%s</div>', $item['shape_type'], $style, $labeltext);
				if ('rectangle_belevel_down' == $item['shape_type'] || 'rectangle_belevel_up' == $item['shape_type']) {
					return sprintf('<div id = "shapeposition" style = "display: block; position: absolute;"><div class= "%s" style ="%s"><p style ="%s">%s</p></div></div>', $item['shape_type'], $style, $textstyle, $labeltext);

				} else {
					return sprintf('<div class= "%s" style ="%s"><p style ="%s">%s</p></div>', $item['shape_type'], $style, $textstyle, $labeltext);
				}
			}
		} else if ('text' == $item['type']) {
			if (''!= $item['label_container_css']) {
				$design = $item['label_container_css']; 
			}
			if (''!= $item['label_text_css'] ) {
				$design = $item['label_text_css'] . $design;
			}	    	
			return sprintf('<span id ="%s" style ="%s">%s</span>', $labeltext, $design, $labeltext);
		   
		}
	}


	public function column_name( $item) {
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
				'Edit' => sprintf('<a href="?page=new-label&action=edit&id=%s">%s</a>', $item['id'], __('Edit', 'RD')),
				'Trash' => sprintf('<a href="?page=%s&action=trash&id=%s">%s</a>', $page, $item['id'], __('<span style="color:red">Trash</span>', 'RD'))
				);
			}
		} else {
				$actions = array(
			'Edit' => sprintf('<a href="?page=new-label&action=edit&id=%s">%s</a>', $item['id'], __('Edit', 'RD')),
			'Trash' => sprintf('<a href="?page=%s&action=trash&id=%s">%s</a>', $page, $item['id'], __('<span style="color:red">Trash</span>', 'RD'))
			);
		}	 
		return sprintf('%s %s', $item['name'], $this->row_actions($actions));
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
			'cb' 				=> '<input type="checkbox" />', //Render a checkbox instead of text
			'id' 				=> __('ID', 'RD'),
			'name' 				=> __('Name', 'RD'),
			'label_image' 		=> __('Label View', 'RD'),
			'type' 				=> __('Type', 'RD'),
			'position' 			=> __('Position', 'RD'),
			'rule_name' 		=> __('Rule Name', 'RD'),
			'last_updated' 		=> __('Last Updated Date', 'RD'),
			);
			return $columns;
	}
	public function get_sortable_columns() {
		$sortable_columns = array(
			'id' 				=> array('id', true),
			'name'  			=> array('name', true),
			'label_image'  		=> array('label_image', true),
			'type'  			=> array('type', true),
			'position' 			=> array('position', true),
			'rule_name' 		=> array('rule_name', true),
			'last_updated' 		=> array('last_updated', true)
			);
		return $sortable_columns;
	}

	public function get_bulk_actions() {
		$actions = array(
			'untrash'	=> 'Restore',
			'trash' 	=> 'Move to Trash',
			'delete'	=> 'Delete Permanently',
			
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
		$table_name = $wpdb->prefix . 'aw_pl_product_label'; // do not forget about tables prefix

		$allrule = $wpdb->get_results("SELECT label_id FROM {$wpdb->prefix}aw_pl_product_rule AS rule WHERE EXISTS(SELECT id FROM {$wpdb->prefix}aw_pl_product_label AS label WHERE label.id = rule.label_id) ", ARRAY_N);
		$allrule = aw_pl_convert_array_one_dimension($allrule);

		if ('trash' === $this->current_action()) {
			if (isset($_REQUEST['id'])) {
				$ids = json_encode($_REQUEST);
				$ids = wp_unslash($ids);
				$ids = json_decode($ids, true);
				$ids = $ids['id'];
			} else { 
				$ids = array();
			}
			
			/*if (!empty($ids)) {
				$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aw_pl_product_label SET status=0  WHERE id IN(%5s)" , "{$ids}"));
			}*/
			if (!empty($ids)) {
				
				if (is_array($ids)) {
					$id_present = !empty(array_intersect($allrule, $ids));					
					$ids = implode(',', $ids);
				} else {
					$id_present = in_array($ids, $allrule);
				}

				if (empty($id_present)) {
					$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aw_pl_product_label SET status=0  WHERE id IN(%5s)" , "{$ids}"));
					$_REQUEST['success'] = 'success';
				} else {
					$_REQUEST['success'] = '';
					$_REQUEST['labelid'] = $ids;
				}
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
				$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}aw_pl_product_label  WHERE id IN(%5s)" , $ids));
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
				$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aw_pl_product_label SET status=1 WHERE id IN(%5s)" , $ids));
			}
		}
	}
	public function prepare_items( $status = 1, $search = '') {
		global $wpdb;

		$search 		= trim($search);
		$per_page     	= $this->get_items_per_page( 'labels_per_page', 20 );
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
		/*$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM  {$wpdb->prefix}aw_pl_product_label  WHERE status=%d", 1));

		if (isset($_GET['status'])) { 
			$sanitize_status = sanitize_text_field($_GET['status']);
			$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM {$wpdb->prefix}aw_pl_product_label WHERE status =%s", "{$sanitize_status}"));
		}*/

		$orderby = ( isset($_REQUEST['orderby']) && in_array(sanitize_text_field($_REQUEST['orderby']), array_keys($this->get_sortable_columns())) ) ? sanitize_text_field($_REQUEST['orderby']) : 'id';
		$order = ( isset($_REQUEST['order']) && in_array(sanitize_text_field($_REQUEST['order']), array('asc', 'desc')) ) ? sanitize_text_field($_REQUEST['order']) : 'ASC';
		$offset = ( (int) $current_page - 1 ) * (int) $per_page;

		if ( 'rule_name' == $orderby) {
			$tab = 'rule';
		} else {
			$tab = 'label';
		}
		if ('label_image' == $orderby) {
			$orderby  = 'frontend_label_text';
			$tab = 'rule';
		}

		if ('' != $search) {
			$this->items = $wpdb->get_results($wpdb->prepare("SELECT  label.id,label.name,label.position,label.status,label.type,label.shape_type,label.label_image,label.label_size,label.label_test_text,label.label_container_css,label.label_text_css,label.last_updated,rule.frontend_label_text,rule.frontend_medium_text,rule.frontend_small_text,GROUP_CONCAT(DISTINCT rule.rule_name SEPARATOR ',') AS rule_name FROM {$wpdb->prefix}aw_pl_product_label AS label LEFT JOIN {$wpdb->prefix}aw_pl_product_rule AS rule ON label.id = rule.label_id WHERE label.status = %d   AND name LIKE %s  GROUP BY label.id ORDER BY %5s.%5s  %5s LIMIT %d OFFSET %d ", "{$status}", "{$search}%", "{$tab}", "{$orderby}", "{$order}", "{$per_page}", "{$offset}"), ARRAY_A);	
		} else {
			$this->items = $wpdb->get_results($wpdb->prepare("SELECT  label.id,label.name,label.position,label.status,label.type,label.shape_type,label.label_image,label.label_size,label.label_test_text,label.label_container_css,label.label_text_css,label.last_updated,rule.frontend_label_text,rule.frontend_medium_text,rule.frontend_small_text,GROUP_CONCAT(DISTINCT rule.rule_name SEPARATOR ',') AS rule_name FROM {$wpdb->prefix}aw_pl_product_label AS label LEFT JOIN {$wpdb->prefix}aw_pl_product_rule as rule ON label.id = rule.label_id WHERE label.status = %d  GROUP BY label.id ORDER BY %5s.%5s %5s LIMIT %d OFFSET %d", "{$status}", "{$tab}", "{$orderby}", "{$order}", "{$per_page}", "{$offset}"), ARRAY_A);		
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
			$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}aw_pl_product_label WHERE status=%d AND name LIKE %s" , "{$status}", "%{$search}%" ));
		} else {
			$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*)  FROM {$wpdb->prefix}aw_pl_product_label WHERE status=%d", "{$status}"));
		}		
		return $total_items;
	}	

}
