<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
if (!class_exists('WP_List_Table')) {
	require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class AwProductRuleList extends WP_List_Table {
	public function __construct() {
		global $status, $page;
		parent::__construct(array(
			'singular' => __('Rule', 'RD'),
			'plural' => __('Rule', 'RD'),
			'ajax'   => true
		));
	}
	public static function aw_pl_rule_add_screen_option() {
		$option = 'per_page';
		$args = array(
		'label' => 'Number of items per page:',
		'default' => 20,
		'option' => 'rule_per_page'
		);
	add_screen_option( $option, $args );

		$table_bal = new AwProductRuleList();
	}

	public function column_default( $item, $column_name) {
		return $item[$column_name]; 
	}

	public static function column_label_image( $item) {
		$style = '';
		$textstyle = '';
		$design = '';
		$labeltext = '';
		$replace_str = array('{attribute: code}','{save_percent}','{save_amount}','{price}','{special_price}','{stock}','{sku}','{spdl}','{sphl}',);

		if (!empty($item['frontend_label_text'])) {
			$labeltext = $item['frontend_label_text'];
		} else if (!empty($item['frontend_medium_text'])) {
			$labeltext = $item['frontend_medium_text'];
		} else if (!empty($item['frontend_small_text'])) {
			$labeltext = $item['frontend_small_text'];
		}

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
			if ('rectangle_belevel_down' == $item['shape_type'] || 'rectangle_belevel_up' == $item['shape_type']) {
				return sprintf('<div id = "shapeposition" style = "display: block; position: absolute;"><div class= "%s" style ="%s"><p style ="%s">%s</p></div></div>', $item['shape_type'], $style, $textstyle, $labeltext);

			} else {
				return sprintf('<div class= "%s" style ="%s"><p style ="%s">%s</p></div>', $item['shape_type'], $style, $textstyle, $labeltext);
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

	public function column_rule_name( $item) {
		if (isset($_REQUEST['page'])) {
			$page = sanitize_text_field($_REQUEST['page']);
		}
		 
		if (isset($_GET['status'])) {
			$val = sanitize_text_field($_GET['status']);
			if (1 != $val) {
				$actions['untrash'] = sprintf('<a href="?page=%s&action=untrash&id=%s">%s</a>', $page, $item['rule_id'], __('Restore', 'RD'));
				$actions['delete'] = sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $page, $item['rule_id'], __('Delete Permanently', 'RD'));
			} else {
					$actions = array(
				'Edit' => sprintf('<a href="?page=new-rule&action=edit&id=%s">%s</a>', $item['rule_id'], __('Edit', 'RD')),
				'Enable' => sprintf('<a href="?page=%s&action=enable&id=%s">%s</a>', $page, $item['rule_id'], __('Enable', 'RD')),
				'Disable' => sprintf('<a href="?page=%s&action=disable&id=%s">%s</a>', $page, $item['rule_id'], __('<span style="color:red">Disable</span>', 'RD')),
				'Trash' => sprintf('<a href="?page=%s&action=trash&id=%s">%s</a>', $page, $item['rule_id'], __('<span style="color:red">Trash</span>', 'RD'))
				);
					if (1 == $item['rule_status']) {
						unset($actions['Enable']);
					}
					if (2 == $item['rule_status']) {
						unset($actions['Disable']);
					}
			}
		} else {

			$actions = array(
				'Edit' => sprintf('<a href="?page=new-rule&action=edit&id=%s">%s</a>', $item['rule_id'], __('Edit', 'RD')),
				'Enable' => sprintf('<a href="?page=%s&action=enable&id=%s">%s</a>', $page, $item['rule_id'], __('Enable', 'RD')),
				'Disable' => sprintf('<a href="?page=%s&action=disable&id=%s">%s</a>', $page, $item['rule_id'], __('<span style="color:red">Disable</span>', 'RD')),
				'Trash' => sprintf('<a href="?page=%s&action=trash&id=%s">%s</a>', $page, $item['rule_id'], __('<span style="color:red">Trash</span>', 'RD'))
			);
			if (1 == $item['rule_status']) {
				unset($actions['Enable']);
			}
			if (2 == $item['rule_status']) {
				unset($actions['Disable']);
			}
		}
		return sprintf('%s %s', $item['rule_name'], $this->row_actions($actions));

	}

	public function column_rule_status( $item) {
		if (1 == $item['rule_status']) {
			$item['rule_status'] = '<span style="color:green">Enable</span>';
		} else {
			$item['rule_status'] = '<span style="color:red">Disable</span>';
		}
		return $item['rule_status'];
	}

	public function column_cb( $item) {
		if (isset($item['rule_id'])) {
			return sprintf(
			'<input type="checkbox" name="id[]" value="%s" />',
			$item['rule_id']);
		}
	}
	public function get_columns() {
		$columns    = array(
			'cb' 			 	=> '<input type="checkbox" />',//Render a checkbox instead of text
			'rule_id'           =>  __( 'ID', 'RD' ) ,
			'rule_name'      	=>  __( 'Rule Name', 'RD' ),
			'priority'       	=>  __( 'Priority', 'RD' ),
			'label_image'    	=>  __( 'Label View', 'RD' ),
			'rule_status'       =>  __( 'Status', 'RD' ),
			'label_name'     	=>  __( 'Label Name', 'RD' ),
			'rule_last_updated'	=>  __( 'Last Updated Date', 'RD' )
			);
		return $columns;
	}
	public function get_sortable_columns() {
		$sortable_columns = array(
			'rule_id'            	=>  array('rule_id', true),
			'rule_name'      		=>  array('rule_name', true),
			'priority'       		=>  array('priority', true),
			'label_image'    		=>  array('label_image', true),
			'rule_status'           =>  array('rule_status', true),
			'label_name'     		=>  array('label_name', true),
			'rule_last_updated'   	=>  array('rule_last_updated', true),
			);
		return $sortable_columns;
	}
	public function get_bulk_actions() {
		global $wpdb;
		$label_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pl_product_label WHERE status=%d", 1), ARRAY_A);

		$actions = array(
			'enable'	=>'Enable',
			'disable'	=>'Disable',
			'trash' 	=> 'Move to Trash',
			'untrash'	=> 'Restore',
			'delete'	=> 'Delete Permanently',
			'' 			=> 'Replace Label ',			
		);
		foreach ($label_data as $data) {
			$val 	=	$data['name'];			
			$actions[$val] = '→' . $val;	
		}
		
		if (!isset($_GET['status']) ) {
			unset($actions['untrash']);
			unset($actions['delete']);
		} 
		if (isset($_GET['status'])) {
			$val = sanitize_text_field($_GET['status']);
			if (1 != $val) {
				unset($actions['enable']);
				unset($actions['disable']);
				unset($actions['trash']);
			} else {
				unset($actions['untrash']);
				unset($actions['delete']);
			}
		}

		return $actions;
	}

	public function bulk_actions( $which = '' ) {
		if ( is_null( $this->_actions ) ) {
			$this->_actions = $this->get_bulk_actions();
	 
			$this->_actions = apply_filters( "bulk_actions-{$this->screen->id}", $this->_actions ); 
			$two = '';
		} else {
			$two = '2';
		}
	 
		if ( empty( $this->_actions ) ) {
			return;
		}
	 
		echo '<label for="bulk-action-selector-' . esc_attr( $which ) . '" class="screen-reader-text">' . ( 'Select bulk action' ) . '</label>';
		echo '<select name="action' . esc_attr($two ) . '" id="bulk-action-selector-' . esc_attr( $which ) . "\">\n";
		echo '<option value="-1">' . ( 'Bulk actions' ) . "</option>\n";
	 
		foreach ( $this->_actions as $key => $value ) {
			if ( is_array( $value ) ) {
				echo "\t" . '<optgroup label="' . esc_attr( $key ) . '">' . "\n";
	 
				foreach ( $value as $name => $title ) {
					$class = ( 'edit' === $name ) ? ' class="hide-if-no-js"' : '';
	 
					echo "\t\t" . '<option value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . "</option>\n";
				}
				echo "\t</optgroup>\n";
			} else {
				$class = ( 'edit' === $key ) ? ' class="hide-if-no-js"' : '';
				if ('' == $key) {
					echo "\t<optgroup label='Replace Label'></optgroup>";
				} else {
					echo '\t<option value="' . esc_attr( $key ) . '"' . esc_attr( $class ) . '>' . esc_attr( $value ) . "</option>\n";
				}				
			}
		}
	 
		echo "</select>\n";
	 
		submit_button( __( 'Apply' ), 'action', '', false, array( 'id' => "doaction$two" ) );
		echo "\n";
	}

	public function process_bulk_action() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'aw_pl_product_rule'; // do not forget about tables prefix
		$label_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pl_product_label WHERE %d", 1), ARRAY_A);


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
				$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aw_pl_product_rule SET rule_status=0  WHERE rule_id IN(%5s)" , "{$ids}"));
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
				$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}aw_pl_product_rule  WHERE rule_id IN(%5s)" , $ids));
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
				$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aw_pl_product_rule SET rule_status=1 WHERE rule_id IN(%5s)" , $ids));
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
				$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aw_pl_product_rule SET rule_status=1 WHERE rule_id IN(%5s)" , $ids));
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
				$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aw_pl_product_rule SET rule_status=2 WHERE rule_id IN(%5s)" , $ids));
			}
		}
		foreach ($label_data as $data) {
			$label_name = $data['name'];
			$label_id = $data['id'];
			if ($label_name === $this->current_action()) {
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
					$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aw_pl_product_rule SET label_name = %s,label_id = %s WHERE rule_id IN(%5s)" , "{$label_name}", "{$label_id}", $ids));
				}
			}
		}
	}

	public function prepare_items( $status = 1, $search = '') {
		global $wpdb;
		$search 		= trim($search);
		$per_page     	= $this->get_items_per_page( 'rule_per_page', 20 );
		//$total_items 	= self::get_count($status, $search);
		$current_page 	= $this->get_pagenum();
		if (isset($_REQUEST['paged'])) {
			$current_page = sanitize_text_field($_REQUEST['paged']);
		}

		$sortable 	= $this->get_sortable_columns();
		$columns 	= $this->get_columns();
		$hidden 	= get_hidden_columns($this->screen);
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->process_bulk_action();

		if ('' != $search) {
			$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*)  FROM {$wpdb->prefix}aw_pl_product_rule WHERE rule_status IN(%d ,%d) AND rule_name LIKE %s", 1, 2, "%{$search}%"));
		} else if (0 != $status) {
			$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*)  FROM {$wpdb->prefix}aw_pl_product_rule WHERE rule_status IN(%d ,%d)", 1, 2));	
		} else {
			$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*)  FROM {$wpdb->prefix}aw_pl_product_rule WHERE rule_status =%d", 0));
		}

		$orderby = ( isset($_REQUEST['orderby']) && in_array(sanitize_text_field($_REQUEST['orderby']), array_keys($this->get_sortable_columns())) ) ? sanitize_text_field($_REQUEST['orderby']) : 'rule_id';
		$order = ( isset($_REQUEST['order']) && in_array(sanitize_text_field($_REQUEST['order']), array('asc', 'desc')) ) ? sanitize_text_field($_REQUEST['order']) : 'ASC';
		$offset = ( (int) $current_page - 1 ) * (int) $per_page;

		if ('label_image' == $orderby) {
			$orderby  = 'frontend_label_text';
		}

		if ('' != $search) {
			$this->items = $wpdb->get_results($wpdb->prepare("SELECT  *  FROM {$wpdb->prefix}aw_pl_product_rule AS rule INNER JOIN {$wpdb->prefix}aw_pl_product_label AS label ON rule.label_id = label.id WHERE rule_status IN(%d ,%d) AND rule_name LIKE %s  ORDER BY rule.%5s  %5s LIMIT %d OFFSET %d", 1, 2, "{$search}%", "{$orderby}", "{$order}", "{$per_page}", "{$offset}"), ARRAY_A); 
		} else {
			if (0 !=$status) {
			   $status='1,2';
			   $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pl_product_rule AS rule INNER JOIN {$wpdb->prefix}aw_pl_product_label AS label ON rule.label_id = label.id  WHERE rule.rule_status IN(%d ,%d) ORDER BY rule.%5s %5s LIMIT %d OFFSET %d", 1, 2, "{$orderby}", "{$order}", "{$per_page}", "{$offset}"), ARRAY_A);

			} else {
			  $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pl_product_rule AS rule INNER JOIN {$wpdb->prefix}aw_pl_product_label AS label ON rule.label_id = label.id  WHERE rule.rule_status = %d ORDER BY rule.%5s %5s LIMIT %d OFFSET %d", "{$status}", "{$orderby}", "{$order}", "{$per_page}", "{$offset}"), ARRAY_A);
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
			$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}aw_pl_product_rule WHERE rule_status=%d AND rule_name LIKE %s" , "{$status}", "%{$search}%" ));
		} else {
			$total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*)  FROM {$wpdb->prefix}aw_pl_product_rule WHERE rule_status=%d", "{$status}"));
		}		
		return $total_items;
	}
}
