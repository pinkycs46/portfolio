<?php 

class WC_CC_Payment_Method extends WC_Payment_Gateway {

	private $order_status;

	public function __construct() {
		$this->id = 'companycredit_payment';
		$this->method_title = __('Company Credit', 'woocommerce-company-credit-payment-gateway');
		$this->method_description = __( 'Have your customers pay with credit limit.', 'woocommerce' );
		$this->title = __('Company Credit Payment', 'woocommerce-company-credit-payment-gateway');
		$this->has_fields = true;
		$this->init_form_fields();
		$this->init_settings();
		$this->enabled = $this->get_option('enabled');
		$this->title = $this->get_option('title');
		$this->description = $this->get_option('description');
		$this->hide_text_box = $this->get_option('hide_text_box');
		$this->text_box_required = $this->get_option('text_box_required');
		$this->order_status = $this->get_option('order_status');

		add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
		add_filter( 'woocommerce_available_payment_gateways', array($this,'aw_cc_payment_gateway_disable_credit_limit') );
	}

	public function init_form_fields() {
			$option_array = wc_get_order_statuses();
			unset($option_array['wc-cancelled']);
			unset($option_array['wc-refunded']);
			unset($option_array['wc-failed']);
				$this->form_fields = array(
						'enabled' => array(
						'title' 		=> __( 'Enable/Disable', 'woocommerce-company-credit-payment-gateway' ),
						'type' 			=> 'checkbox',
						'label' 		=> __( 'Enable Custom Payment', 'woocommerce-company-credit-payment-gateway' ),
						'default' 		=> 'yes'
					),

					'title' => array(
						'title' 		=> __( 'Method Title', 'woocommerce-credit-limit-payment' ),
						'type' 			=> 'text',
						'description' 	=> __( 'Payment method description that the customer will see on your checkout', 'woocommerce-credit-limit-payment' ),
						'default'		=> __( 'Credit Limit', 'woocommerce-credit-limit-payment' ),
						'desc_tip'		=> true,
					),
					'description' => array(
						'title' 		=> __( 'Description', 'woocommerce-company-credit-payment-gateway' ),
						'type' 			=> 'textarea',
						'css' 			=> 'width:500px;',
						'default' 		=> 'Available Credit: ',
						'description' 	=> __( 'The message which you want it to appear to the customer in the checkout page.', 'woocommerce-company-credit-payment-gateway' ),
						'desc_tip'		=> true,
					),
					'order_status' => array(
						'title' 		=> __( 'Order Status After The Checkout', 'woocommerce-company-credit-payment-gateway' ),
						'type' 			=> 'select',
						'options' 		=> $option_array,
						'default' 		=> 'wc-on-hold',
						'description' 	=> __( 'The default order status if this gateway used in payment.', 'woocommerce-company-credit-payment-gateway' ),
						'desc_tip'		=> true,
					),
			 );
	}
	/**
	 * Admin Panel Options
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_options() {
		?>
		<h3><?php echo 'Credit Limit Payment Setting'; ?></h3>
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">
					<table class="form-table">
						<?php $this->generate_settings_html(); ?>
					</table> 
				</div>
				 
			</div>
		</div>
		<div class="clear"></div>
		<?php
	}

	public function validate_fields() {
		$cart_total 			= (float) WC()->cart->total;
		$cart_total 			= aw_cc_convert_default_currency($cart_total);
		$aw_cc_available_credit = ( isset($_POST['aw_cc_available_credit']) ) ? sanitize_text_field($_POST['aw_cc_available_credit']): '';
		$aw_cc_credit_limit		= ( isset($_POST['aw_cc_credit_limit']) )? sanitize_text_field($_POST['aw_cc_credit_limit']): '';
		$aw_cc_min_ordertotal 	= (float) get_option('aw_cc_min_ordertotal');
		$aw_cc_max_ordertotal 	= (float) get_option('aw_cc_max_ordertotal');
		if ( isset($_POST['aw_cc_nonce_fields'])) {
			$aw_cc_nonce_fields = sanitize_text_field($_POST['aw_cc_nonce_fields']);
			if ( !wp_verify_nonce($aw_cc_nonce_fields, 'aw_cc_action')) {
				wp_die('Our Site is protected');
			}
		}

		$flag = false;
		/*
		if ($aw_cc_credit_limit < $cart_total) {
			wc_add_notice( __('Maximum Credit limit is ' . wp_kses(aw_cc_get_amount(aw_cc_convert_currency($aw_cc_credit_limit)), wp_kses_allowed_html('post')) . ' Credit Limit can not be applied.' ), 'error');
			return false;
		}*/

		if ($aw_cc_available_credit < $cart_total) {
			wc_add_notice( __('Insufficient Available Credit Funds. Credit Limit can not be applied.'), 'error');
			return false;
		} 

		if (!empty($aw_cc_min_ordertotal)) {
			if ($cart_total < $aw_cc_min_ordertotal) {
				$message = 'Order total must be greater or equal to Minimum Order Total. ' . wp_kses(aw_cc_get_amount(aw_cc_convert_currency($aw_cc_min_ordertotal)), wp_kses_allowed_html('post'));
				wc_add_notice( __($message), 'error');
				return false;
			}	
		}
		if (!empty($aw_cc_max_ordertotal)) {
			if ($aw_cc_max_ordertotal < $cart_total) {
				$message = 'Order total must be less or equal to Maximum Order Total. ' . wp_kses(aw_cc_get_amount(aw_cc_convert_currency($aw_cc_min_ordertotal)), wp_kses_allowed_html('post'));
				wc_add_notice( __($message), 'error');
				return false;
			}
		}
		
		$applied_amount = $cart_total;
		WC()->session->set('aw_cc_transaction_amount', $cart_total);
		//return true;
	}

	public function process_payment( $order_id ) {
		global $woocommerce;
		$order = new WC_Order( $order_id );
		$order->update_status($this->order_status, __( 'Awaiting payment', 'woocommerce-credit-limit-payment' ));
		wc_reduce_stock_levels( $order_id );
		// Remove cart
		$woocommerce->cart->empty_cart();
		// Return thankyou redirect
		return array(
			'result' => 'success',
			'redirect' => $this->get_return_url( $order )
		);	
	}

	public function payment_fields() {
		$cart_total = WC()->cart->total;
		if (is_user_logged_in()) {
			$user_id = get_current_user_id();
			$credit_detail = aw_cc_get_user_credit_detail($user_id);
			wp_nonce_field('aw_cc_action', 'aw_cc_nonce_fields');

			if (!empty($credit_detail)) {
				$details = get_option('woocommerce_credit_limit_settings');
				?>
			<fieldset>
				<p class="form-row form-row-wide">
					<label for="<?php echo wp_kses($this->id, wp_kses_allowed_html('post') ); ?>-order-num"><?php echo wp_kses( $this->description, wp_kses_allowed_html('post') ); ?>&nbsp; <span><?php echo wp_kses(aw_cc_get_amount(aw_cc_convert_currency($credit_detail->available_credit)), wp_kses_allowed_html('post')); ?></span></label>
					<input type="hidden" value="<?php echo wp_kses($credit_detail->available_credit, wp_kses_allowed_html('post') ); ?>" name="aw_cc_available_credit" />
					 <input type="hidden" value="<?php echo wp_kses($credit_detail->credit_limit, wp_kses_allowed_html('post') ); ?>" name="aw_cc_credit_limit" />
					 <input type="hidden" value="<?php echo wp_kses($cart_total, wp_kses_allowed_html('post') ); ?>" name="aw_cc_cart_total" />
				</p>
					
			</fieldset>
			<?php
			}
		}
	}

	public function aw_cc_payment_gateway_disable_credit_limit( $available_gateways ) {
		global $woocommerce;
		$exist = get_option( 'company_credit_by_aheadwork' );

		if (!$exist) {
			return false;
		}
		if (empty(WC()->cart->total)) {  
			return false;
		}
		$cart_total 	= (float) WC()->cart->total;
		$cart_total 	= aw_cc_convert_default_currency($cart_total);
		$user_id 		= get_current_user_id();
		$credit_detail 	= aw_cc_get_user_credit_detail($user_id);
		$aw_cc_min_ordertotal 	= (float) get_option('aw_cc_min_ordertotal');
		$aw_cc_max_ordertotal 	= (float) get_option('aw_cc_max_ordertotal');
		if (empty($credit_detail->credit_limit)|| $credit_detail->credit_limit<=0 || ( abs($credit_detail->available_credit)< abs($cart_total) )) {
			unset($available_gateways['companycredit_payment']);
		}
		if (!empty($aw_cc_min_ordertotal)) {
			if ($cart_total < $aw_cc_min_ordertotal) {
				unset($available_gateways['companycredit_payment']);
			}	
		}
		if (!empty($aw_cc_max_ordertotal)) {
			if ($aw_cc_max_ordertotal < $cart_total) {
				unset($available_gateways['companycredit_payment']);
			}
		}
		return $available_gateways;
	}
}

