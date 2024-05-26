<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

$awgiftadmin 	= new AwGiftCardAdmin();

class AwGiftCardAdmin {

	public function __construct() {
		add_filter('set-screen-option', array('AwGiftCardAdmin','aw_gc_set_screen'), 11, 3);

		if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
			add_action('admin_menu', array('AwGiftCardAdmin','aw_gift_card_adminmenu'));
		}

		add_filter( 'product_type_selector', array( 'AwGiftCardAdmin' , 'aw_gift_card_add_custom_product_type' ));

		add_action( 'init', 'aw_gift_card_create_custom_product_type');

		add_filter( 'woocommerce_product_class' , array( 'AwGiftCardAdmin' , 'aw_gift_card_woocommerce_product_class' ), 10, 2 );

		add_action( 'admin_footer' , array( 'AwGiftCardAdmin' , 'aw_gift_card_custom_product_admin_custom_js' ));

		// add the settings under ‘General’ sub-menu
		add_action( 'woocommerce_product_options_general_product_data', array( 'AwGiftCardAdmin' , 'aw_gift_card_add_html_to_genraltab' ));

		/* Ajax to add Price */
		add_action( 'wp_ajax_aw_gift_price_display_on_product', array( 'AwGiftCardAdmin', 'aw_gift_price_display_on_product' ));
		add_action( 'wp_ajax_nopriv_aw_gift_price_display_on_product', array( 'AwGiftCardAdmin', 'aw_gift_price_display_on_product' ));

		 /* Save Configuration form setting */
		 add_action('admin_post_aw_wgc_save_configuration_form', array('AwGiftCardConfiguration','aw_save_gifcard_configuration_form'));

		 add_filter( 'woocommerce_admin_order_totals_after_tax', array( 'AwGiftCardAdmin', 'aw_gift_order_totals_after_tax' ) );

		 // To update variable product post meta _product_attribute
		 add_action( 'woocommerce_update_product', array( 'AwGiftCardAdmin','aw_gift_update_postmeta_value_on_product_save'));

		 add_action( 'woocommerce_update_order', array( 'AwGiftCardAdmin','aw_gift_woocommerce_update_order_total'));

		 add_action( 'admin_footer-post.php', array( 'AwGiftCardAdmin','aw_gift_woocommerce_add_coupon_link'));
	}

	public static function aw_gc_self_deactivate_notice() {
	/** Display an error message when WooComemrce Plugin is missing **/
		?>
		<div class="notice notice-error">
			<p>Please install and activate WooComemrce plugin before activating Gift Card plugin.</p>
		</div>
		<?php
	}

	public static function aw_gift_card_adminmenu() {

		add_menu_page(__('Gift Card'), __('Gift Card'), 'edit_themes', 'aw_gift_card_configration', array('AwGiftCardConfiguration' , 'aw_giftcard_configuration_html'), plugins_url( '/gift-card-by-aheadworks/admin/images/giftcard.png' ), 7); 
		$page = add_submenu_page('aw_gift_card_configration', __('Configuration'), __('Configuration'), 'manage_options', 'aw_gift_card_configration', array('AwGiftCardConfiguration' , 'aw_giftcard_configuration_html')); 
		$hook = add_submenu_page('aw_gift_card_configration', __('Gift Card Codes'), __('Gift Card Codes'), 'manage_options', 'aw_gift_card_codes', array('AwGiftCardAdmin' , 'aw_gift_card_codes_list'));

		add_action( "load-$hook", array('AwGiftCardAdmin','aw_gc_add_screen_option'));
		
	}
	
	public static function aw_gc_add_screen_option() {
		$option = 'per_page';
		$args = array(
			'label' => 'Number of items per page:',
			'default' => 15,
			'option' => 'aw_gc_codes_per_page'
		);
		add_screen_option( $option, $args );
		$table = new AwGiftCardList();
	}
	public static function aw_gc_set_screen( $status, $option, $value) { 
		if ('aw_gc_codes_per_page' == $option) {
			$user 	= get_current_user_id();	
			$screen = get_current_screen();
			update_user_meta($user, 'aw_gc_codes_per_page', $value);
			return $value;
		}	
		return $status;
	}
	
	public static function aw_gift_card_codes_list() {
		global $wpdb;
		$table 			= new AwGiftCardList();
		$all 			= '';
		$trash 			= '';
		if (isset($_GET['status'])) {
			$status= sanitize_text_field($_GET['status']);
			$table->prepare_items($status);
			$trash 		= 'current';
		} else {
			$table->prepare_items();
			$all 		= 'current';
		}

		$count_all 		= $table->get_count(1);
		$count_trashed 	= $table->get_count(0);
		
		?>
		<div class="wrap">
			<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
			<h1 class="wp-heading-inline"><?php esc_html_e('Gift Card Codes', 'Gift Card Codes'); ?></h1>
			<hr class="wp-header-end">
			<ul class="subsubsub">
				<li class="all"><a href="admin.php?page=aw_gift_card_codes" class="<?php echo wp_kses_post($all); ?>" aria-current="page">All <span class="count">(<?php echo intval($count_all); ?>)</span></a> |</li>
				<li class="trash"><a href="admin.php?page=aw_gift_card_codes&status=0" class="<?php echo wp_kses_post($trash); ?>">Trash <span class="count">(<?php echo intval($count_trashed); ?>)</span></a></li>
			</ul>
			<form id="posts-filter" method="get">
				<p class="search-box">
					<!--<input type="hidden" name="post_type" class="post_type_page" value="aw_search_gift_card">-->
					<input type="hidden" name="page" class="page" value="aw_gift_card_codes">	
					<input type="hidden" name="status" class="post_status_page" value="
					<?php
					if (isset($_GET['status']) && 0  == $_GET['status'] ) {
						echo 0;
					} else {
						echo 1;} 
					?>
						 ">
					<?php 
					$search_input = '';
					if (isset($_GET['s'])) {
						$search_input = sanitize_text_field($_GET['s']);} 
					?>
					<input type="search" id="post-search-input" name="s" value="<?php echo esc_html($search_input); ?>">
					<input type="submit" id="search-submit" class="button" value="Search Gift Card">
				</p>
			</form>

			<form id="aw-gift-card-table" method="GET">
				<input type="hidden" name="post_type" value="<?php echo esc_html('aw_search_gift_card'); ?>"/>
				<input type="hidden" name="page" value="<?php echo isset($_REQUEST['page']) ? wp_kses($_REQUEST['page'], 'post') : '' ; ?>"/>
				<?php $table->display(); ?>
			</form>
		</div>
		<?php 
	}

	public static function aw_gift_card_add_custom_product_type( $types ) {
		$types[ 'gift_card_virtual' ] 	= 'Gift Card';
		return $types;
	}

	public static function aw_gift_card_woocommerce_product_class( $classname, $product_type ) {
		if ( 'gift_card_virtual' == $product_type ) { 
			$classname = 'WC_Product_Custom';
		}
		return $classname;
	}

	public static function aw_gift_card_custom_product_admin_custom_js() { 
		global $product;
		$product_price 	= '';

		if ('product' != get_post_type()) :
			return;
		endif;

		$product_id 	= get_the_ID();
		$product_price 	= get_post_meta($product_id, '_product_giftcard_prices', true);
		if (!empty($product_price)) {
			$product_price 	= json_encode($product_price, JSON_FORCE_OBJECT);	
		}
		?>
		<script type='text/javascript'>
			jQuery(document).ready(function () {
				var type = jQuery('#product-type :selected').val();
				if ('gift_card_virtual' == type) {
					tab_with_panel_display();
				} 
				if ('simple' == type) {
					default_tab_panel_display();
				}
				jQuery('#product-type').change(function ()
					{	
						var type = jQuery('#product-type :selected').val();
						if ('gift_card_virtual' == type) {
							tab_with_panel_display();
						} if ('simple' == type) {
							default_tab_panel_display();
						 }
					});
						
			});
			function default_tab_panel_display()
			{
				jQuery('.panel').hide();
				jQuery('#general_product_data').show();
				jQuery('.general_options').addClass('hide_if_grouped  active').show();
				jQuery('.inventory_options').removeClass('active');
				jQuery('.shipping_options').addClass('show_if_virtual').show();
				jQuery('.aw_wgc_tab_options').addClass('show_if_aw_gift').hide(); 
			}
			function tab_with_panel_display()
			{
				jQuery('.shipping_options').addClass('show_if_virtual').hide();
				jQuery('.linked_product_options').removeClass('active');
				jQuery('.shipping_options').removeClass('active');
				
				jQuery('.panel').hide();
				
				jQuery('.options_group.show_if_downloadable').hide();

				jQuery('.options_group.pricing').addClass('show_if_virtual active').show();
				jQuery('.aw_wgc_tab_options').addClass('show_if_aw_gift').show();
				jQuery('#general_product_data').show();
				jQuery('._regular_price_field').addClass('hide_if_virtual').hide();
				jQuery('._sale_price_field').addClass('hide_if_virtual').hide();

				jQuery('.general_options').addClass('show_if_virtual active').show();
				jQuery('.inventory_options').addClass('show_if_virtual').show();
				jQuery('#inventory_product_data ._manage_stock_field').addClass('show_if_virtual').show();
				jQuery('#inventory_product_data ._sold_individually_field').parent().addClass('show_if_virtual').show();
				jQuery('#inventory_product_data ._sold_individually_field').addClass('show_if_virtual').show();
			}
		</script>
		<?php
	}
	 
	public static function aw_gift_card_add_html_to_genraltab() {
		global $woocommerce, $post,$product;
		$prices = maybe_unserialize(get_post_meta($post->ID, 'aw_wgc_price', true));
		$allowed = "'.'";
		echo '<div class="options_group aw_wgc_tab_options" style="display: none;">';
		echo '<p class="form-field aw_virtual_gift_field show_if_aw_gift">
	    		<label for="aw_virtual_gift">Fixed price</label>
	    		<input type="text" id="aw_wgc_price_input" name="aw_wgc_price" value="" onkeypress="return aw_gc_checkIts(event, ' . wp_kses_post($allowed) . ')">
	    		<input type="button" class="button button-primary" id="aw_wgc_price_btn" name="aw_wgc_price" value="Add Price">
	    		<span class="error_msg"></span>
    		</p>';

		echo '<p class="form-field _price_field ">
				<label for="_price"><abbr title="Stock Keeping Unit">Price</abbr></label>';
		if (!empty($prices)) {
			asort($prices);	
			foreach ($prices as $key=> $price) {
			echo '<span class="aw_wgc_prices"><span class="aw_added_price">' . wp_kses_post(aw_gc_get_amount($price)) . '</span><span class="aw_price_close_hover" id="aw_price_close_hover-' . wp_kses_post($key) . '" onclick="aw_remove_price(' . wp_kses_post($key) . ')">&times;</span><input type="hidden" id="hidden_input_wgc_price" value="' . wp_kses_post($price) . '" name="aw_wgc_price[]"></span>';	
			}
		}		
		echo '</p>';
		echo '</div>';
	}

	/* Ajax call to update price in virtual gift card product using add price button */
	public static function aw_gift_price_display_on_product() {
		global $wpdb;

		check_ajax_referer( 'aw_giftcard_admin_nonce', 'aw_gc_nonce_ajax' );
		$str 	= '';
		$result = false;
		$product_id	= '';
		$prices 	= array();
		$existing 	= array();
		/* Below Condition work at add more price */  
		if (isset($_POST['product_id']) && !empty($_POST['price']) && empty($_POST['removeid'])) {

			$product_id	= sanitize_text_field($_POST['product_id']);
			$newprice 	= sanitize_text_field($_POST['price']);
			$existing 	= get_post_meta($product_id, '_price');
			if (in_array($newprice, $existing)) {
				echo json_encode( 'Already added this price', JSON_FORCE_OBJECT );
				die;
			} else {
				if (!empty($newprice)) {
					self::aw_gift_card_add_product_price($product_id, $newprice );		
				}
			}
			$children 	= aw_get_product_variation_id( $product_id);
			if (!empty($children)) {
				foreach ($children as $child) {
					$prices[$child->ID] = $child->price;
				}	
			}
			$result = update_post_meta($product_id, 'aw_wgc_price', $prices);
		} 
		/* Below Condition work at delete price */  
		if (isset($_POST['product_id']) && empty($_POST['price']) && !empty($_POST['removeid'])) {
			$product_id	= sanitize_text_field($_POST['product_id']);
			$remove_id	= sanitize_text_field($_POST['removeid']);
			$price 		= get_post_meta($remove_id, '_price', true);
			unset($prices[$remove_id]);
			delete_post_meta($product_id, '_price', $price);	
			wp_delete_post($remove_id, true);
			$children 	= aw_get_product_variation_id( $product_id);
			if (!empty($children)) {
				foreach ($children as $child) {
					$prices[$child->ID] = $child->price;
				}	
			}
			update_post_meta($product_id, 'aw_wgc_price', $prices);
		}
		
		if (!empty($prices)) {
			asort($prices);
			$prices 	= array_map(function( $val) {
							return aw_gc_get_amount($val);
			} , $prices);
			$prices = json_encode( $prices, JSON_FORCE_OBJECT );
			echo wp_kses_post($prices);
		} else {
			echo '';
		}
		wp_die();
	}

	public static function 	aw_gift_card_add_product_price( $post_id, $price) {
		global $wpdb;
		$parentID = $post_id;
		 
		wp_set_object_terms($post_id, 'gift_card_virtual', 'product_type');
		add_post_meta($post_id, '_price', $price, false);
		$post_name = get_the_title($post_id) . '-' . $price;
		$args = array(
					'ID'		   	=> $post_id, // get parent post-ID
					'post_type'    	=> 'product',
					'post_title'	=>	$post_name,
					'post_name'		=>	$post_name,
					'post_parent'	=>  0,
					'post_status'  	=> array('auto-draft','private', 'publish' ),
				);

		$postexist = get_posts( $args );
		$variation_post = 	array(
								'post_title'	=> $post_name,
								'post_name' 	=> $post_name,
								'post_status'	=> 'publish',
								'post_parent' 	=> $parentID,
								'menu_order' 	=>  get_count_meta_key($post_id)-1,
								'post_type' 	=> 'product_variation',
								'guid' 			=> home_url() . '/?product_variation=' . $post_name
							);
		if (empty($postexist)) {
			unset($args['ID']);	
			$args['post_status']=	'publish';
			$args['guid']		=	home_url() . '/?product_variation=' . $post_names;
			$parentID = wp_insert_post($args);
			$variation_post['post_parent'] = 0;
			$variation_post['menu_order'] = 0;

		} else {
			$variation_post['post_parent'] 	= $parentID;
		}  
		$attID = wp_insert_post($variation_post);
		update_post_meta($attID, 'attribute_giftprice', aw_gc_get_amount($price));
		update_post_meta($attID, '_stock', null);
		update_post_meta($attID, '_download_expiry', -1);
		update_post_meta($attID, '_download_limit', -1);
		update_post_meta($attID, '_downloadable', 'no');
		update_post_meta($attID, '_virtual', 'yes');
		update_post_meta($attID, '_sold_individually', 'no');
		update_post_meta($attID, '_backorders', 'no');
		update_post_meta($attID, '_manage_stock', 'no');
		update_post_meta($attID, '_tax_status', 'taxable');
		update_post_meta($attID, '_tax_class', 'taxfree');
		update_post_meta($attID, 'total_sales', 0);
		update_post_meta($attID, '_wc_average_rating', 0);
		update_post_meta($attID, '_wc_review_count', 0);
		update_post_meta($attID, '_stock_status', 'instock');
		update_post_meta($attID, '_regular_price', $price);
		update_post_meta($attID, '_sku', 'woo-' . get_the_title($post_id));
		update_post_meta($attID, '_price', $price);
	}

	public static function aw_gift_order_totals_after_tax( $order_id  ) {

		$gift_amount= get_post_meta($order_id, '_awgc_redeemed_amt', true);
		$gift_cards = get_post_meta($order_id, '_awgc_redeemed_cards', true);
		$gift_bal 	= get_post_meta($order_id, '_awgc_cards_balance', true);

		if (empty($gift_bal)) {
			$gift_bal 	= 0;
		}

		if ($gift_amount) {
			$total_order_pro = maybe_unserialize(get_post_meta($order_id, 'gift_info', true));
			//$ordered_product_qty 	= count($total_order_pro);
			$validate_wc_gc 		= check_gift_code_details($gift_cards);
			$available_bal 			= aw_gc_get_user_total_balance();
			if (!empty($validate_wc_gc)) {
				$is_user_gc = aw_gift_code_check_giftcard_touser( $validate_wc_gc->id );
				if (empty($is_user_gc)) {
					$balancemsg = '';
				} else {
					$balancemsg = ' (Available Balance: ' . $gift_bal . ' )';
				}		
			} else {
				$balancemsg = ' (Available Balance: ' . $gift_bal . ' )';
			}
			?>
			<tr class="fee">
				<td class="label">
					<?php esc_attr_e( 'Gift Card: ' . $gift_cards . $balancemsg , 'aw-gift-card' ); ?>:
				</td>
				<td width="1%"></td>
				<td class="total">
					<span class="woocommerce-Price-amount amount">
						<bdi><?php echo '-' . wp_kses_post(aw_gc_get_amount($gift_amount)); ?></bdi>
						<input type="hidden" value="<?php echo wp_kses_post($gift_amount); ?>" class="aw_gc_hidden_gift_price">
						<input type="hidden" value="<?php //echo $ordered_product_qty; ?>" class="aw_gc_hidden_total_ordered_qty">
					</span>
				</td>
			</tr>
			<?php
		}
	}

	public static function aw_gift_update_postmeta_value_on_product_save( $product_id) {

		global $wpdb;
		$price_arr 			= array();
		$product_attributes = array();
		$product 			= wc_get_product($product_id);

		if ( $product->is_type('gift_card_virtual') ) {
			$parent_title 	= get_the_title($product_id);
			$args = array(
						'post_status' 	=> 'publish',
						'post_type' 	=> 'product_variation',
						'post_parent'	=> $product_id,
					);
			$children = get_posts( $args );
			if (!empty($children)) {
				foreach ( $children as $children_id => $child ) {
					$child_id = $child->ID;
					$price = get_post_meta($child_id, '_price', true);
					$child->post_title 	= $parent_title . '-' . $price;
					$child->post_name 	= $parent_title . '-' . $price;
					wp_update_post($child);
				}	
			}

		}
	}

	public static function aw_gift_woocommerce_update_order_total( $order_id ) {
		remove_action( 'woocommerce_update_order', array( 'AwGiftCardAdmin','aw_gift_woocommerce_update_order_total' ) );
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}
		$applied_gift 	= get_post_meta($order_id, '_awgc_redeemed_cards', true);
		$reedeem_amt 	= get_post_meta($order_id, '_awgc_redeemed_amt', true);
		if (!empty($reedeem_amt)) {
			$order_sub_total= $order->get_subtotal() + $order->get_total_tax()+ $order->get_shipping_total();
			$new_total 		= $order_sub_total - (float) $reedeem_amt-$order->get_total_discount();
			$order->set_total( $new_total );
			$order->save();
		}
		
		add_action( 'woocommerce_update_order', array( 'AwGiftCardAdmin','aw_gift_woocommerce_update_order_total' ) );
	}
	
	public static function aw_gift_woocommerce_add_coupon_link() {
		$screen = get_current_screen();
		if ('shop_order' == $screen->post_type) {
			?>
			<script language="javascript">
				jQuery(window).ready(function() {
					if (jQuery('span.aw_gc_hp_code').length) {
						var gc_count = jQuery('span.aw_gc_hp_code').length;
					
						jQuery('.aw_gc_hp_code').each(function(i, obj) {
							var gc_code_id = obj.id;
							var gc_code = obj.innerText;
							var gc_code_url = '<a href="admin.php?page=giftcard-detail-page&giftcode_id='+gc_code_id+'">'+gc_code+'</a>';
							jQuery(this).html(gc_code_url);
						});
					}
				});
				</script>
			<?php
		}
	}
} //class close

function aw_gift_card_create_custom_product_type() {
	if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

		class WC_Product_Custom extends WC_Product {
			public function get_type() {
				return 'gift_card_virtual';
			}
		}

	}
}
function get_count_meta_key( $id) {
	global $wpdb;
	$post_count = 0;
	$post_count = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*) FROM $wpdb->postmeta
                WHERE post_id = %d AND meta_key= %s", "{$id}", '_price'));
	return $post_count;
}
?>
