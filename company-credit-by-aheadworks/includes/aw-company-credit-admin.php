<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

$companycreditadmin 	= new AwCompanyCreditAdmin();

class AwCompanyCreditAdmin {
	public function __construct() {
		add_filter('set-screen-option', array(get_called_class(),'aw_cc_set_screen'), 11, 3);
		add_filter('set-screen-option', array(get_called_class(),'aw_cc_set_history_screen'), 11, 3);
		if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
		//if (class_exists( 'woocommerce' ) ) {
		//if(aw_cc_isWoocommerceActive()){

			add_action('admin_post_company_credit_save_setting_form', array(get_called_class(),'aw_company_credit_save_setting_form'));
			add_action('admin_post_aw_cc_save_email_templates_setting', array(get_called_class(),'aw_cc_save_email_templates_setting'));
			add_action('admin_post_customer_credit_save_setting_form', array(get_called_class(),'aw_customer_credit_save_setting_form'));
			
		}
	}

	public static function aw_company_credit_add_flash_notice( $notice = '', $type = 'warning', $dismissible = true ) { 
		// Here we return the notices saved on our option, if there are not notices, then an empty array is returned
		$notices = get_option( 'company_credit_flash_notices', array() );
		$dismissible_text = ( $dismissible ) ? 'is-dismissible' : '';

		$notices = array(
						'notice' => $notice, 
						'type' => $type, 
						'dismissible' => $dismissible_text
					); 	
		update_option( 'company_credit_flash_notices', $notices );
	}
	
	public static function aw_company_credit_menu() {

		
		add_menu_page(__('Company Credit', 'main_menu'), __('Company Credit', 'main_menu'), ' ', 'AwCompanyCredit', 'toplevel_page' , plugins_url('/company-credit-by-aheadworks/admin/images/aw_company_credit.png'), 25);
		// Add a submenu to the custom top-level menu:
		$hook =add_submenu_page('AwCompanyCredit', __('Customers', 'main_menu'), __('Customers', 'main_menu'), 'manage_options', 'customer-credit-balance', array(get_called_class(),'aw_credit_balance_html'));
		add_action( "load-$hook", array(get_called_class(),'aw_cc_credit_balance_screen_option'));

		add_submenu_page('AwCompanyCredit', __('Setting', 'main_menu'), __('Configurations', 'main_menu'), 'manage_options', 'credit-configuration', array(get_called_class(),'aw_creditconfiguration_html'));

		add_submenu_page('', __('Company Credit Emails'), '', 'manage_options', 'company-credit-emails', array(get_called_class() , 'aw_cc_admin_email_setting'));

		$hookhistory =add_submenu_page('', __('Company Credit History'), '', 'manage_options', 'customer-credit-history', array(get_called_class() , 'aw_cc_admin_credit_history_list'));
		add_action( "load-$hookhistory", array(get_called_class(),'aw_cc_customer_history_balance_screen_option'));
		//add_action( "load-$hook", array(get_called_class(),'aw_reward_add_screen_option'));
	}

	public static function aw_cc_credit_balance_screen_option() {
		$option = 'per_page';
		$args = array(
			'label' 	=> 'Number of items per page:',
			'default' 	=> 20,
			'option' 	=> 'aw_cc_customers_per_page'
		);
		add_screen_option( $option, $args );
		$table_bal_trans = new AwcccustomerListAdmin();
	}


	public static function aw_cc_customer_history_balance_screen_option() {
		$option = 'per_page';
		$args = array(
					'label' 	=> 'Number of items per page:',
					'default' 	=> 10,
					'option' 	=> 'aw_cc_history_per_page'
			);
		$data = add_screen_option( $option, $args );
		$history_list = new AwccCreditHistoryListAdmin();
	}

	public static function aw_cc_set_screen( $status, $option, $value) {

		if ('aw_cc_customers_per_page' == $option) {
			$user 	= get_current_user_id();	
			$screen = get_current_screen();
			update_user_meta($user, 'aw_cc_customers_per_page', $value);
			return $value;
		}		
		return $status;

	}
	public static function aw_cc_set_history_screen( $status, $option, $value) {
		if ('aw_cc_history_per_page' == $option) {
			$user 	= get_current_user_id();	
			$screen = get_current_screen();
			update_user_meta($user, 'aw_cc_history_per_page', $value);
			return $value;
		}  	
		return $status;
	}

	public static function aw_creditconfiguration_html() {
		$aw_cc_enable 			= ''; 
		$aw_cc_title 			= ''; 
		$aw_cc_neworderstatus 	= '';
		$aw_cc_credit_limit 	= ''; 
		$aw_cc_min_ordertotal 	= '';
		$aw_cc_max_ordertotal 	= '';
		$aw_cc_enable_no 		= '';
		$aw_cc_enable_yes 		= '';
		$order_status_wc 		= wc_get_order_statuses();
		$aw_cc_order_status 	= array_diff($order_status_wc, ['Failed', 'Cancelled', 'Refunded']);

		if (get_option('aw_cc_enable')) {
			$aw_cc_enable = get_option('aw_cc_enable');
			if ('yes'==$aw_cc_enable) {
				$aw_cc_enable_yes = 'selected="selected"';
			}
			if ('no'==$aw_cc_enable) {
				$aw_cc_enable_no = 'selected="selected"';
			}
		}
		if (get_option('aw_cc_title')) {
			$aw_cc_title = get_option('aw_cc_title');
		}
		if (get_option('aw_cc_credit_limit')) {
			$aw_cc_credit_limit = get_option('aw_cc_credit_limit');
		}  	
		if (get_option('aw_cc_min_ordertotal')) {
			$aw_cc_min_ordertotal = get_option('aw_cc_min_ordertotal');
		}
		if (get_option('aw_cc_max_ordertotal')) {
			$aw_cc_max_ordertotal = get_option('aw_cc_max_ordertotal');
		}				

		$notice = get_option( 'company_credit_flash_notices', array() );
		if ( ! empty( $notice ) ) {
				printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
					wp_kses($notice['type'], wp_kses_allowed_html('post')),
					wp_kses($notice['dismissible'], wp_kses_allowed_html('post')),
					wp_kses($notice['notice'], wp_kses_allowed_html('post'))
				);
			delete_option( 'company_credit_flash_notices', array() );
		}
		$email_template = aw_cc_get_email_template_setting_results();		 
		?>
		<div class="tab-grid-wrapper">
			<div class="spw-rw clearfix">
				<div class="panel-box company-credit-setting">
					<div class="page-title">
						<h1>
							<?php echo 'Configurations'; ?>
						</h1>
					</div>
					<div class="panel-body">
						<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" id="aw_cc_setting_form" enctype="multipart/form-data">
							<?php wp_nonce_field( 'save_company_credit_setting', 'awcompanycredit_admin_nonce' ); ?>
							<input type="hidden" name="action" value="company_credit_save_setting_form">
							<div class="tab">
								<button class="tablinks active" onclick="openTab(event, 'aw_cc_general-setting-tab',this)"><?php echo 'General Settings'; ?></button>
								<button class="tablinks" onclick="openTab(event, 'aw_cc_email-setting-tab',this)"><?php echo 'Emails'; ?></button>
							</div>
							<!-- Tab Start -->
							<div class="tabcontent aw-ar-general-set" id="aw_cc_general-setting-tab" style="display:block;">
									<ul>
										<li>
											<label>Credit Limit </label>
											<div class="control">
												<input type="text" name="aw_cc_credit_limit" class="aw_cc_credit_limit" onkeypress="return aw_cc_checkIt(event, '.')" value="<?php echo wp_kses($aw_cc_credit_limit, wp_kses_allowed_html('post')); ?>" />
											</div>
										</li> 
										<li>
											<label>Minimum Order Total</label>
											<div class="control">
												<input type="text" name="aw_cc_min_ordertotal" class="aw_cc_min_ordertotal" onkeypress="return aw_cc_checkIt(event, '.')" value="<?php echo wp_kses( $aw_cc_min_ordertotal, wp_kses_allowed_html('post')); ?>" />
											</div>
										</li>
										<li>
											<label>Maximum Order Total</label>
											<div class="control">
												<input type="text" name="aw_cc_max_ordertotal" class="aw_cc_max_ordertotal" onkeypress="return aw_cc_checkIt(event, '.')" value="<?php echo wp_kses( $aw_cc_max_ordertotal, wp_kses_allowed_html('post')); ?>" />
											</div>
										</li>
									</ul>
									<div class="submit">
										<input type="submit" class="button button-primary" value="Save" name="cc_setting_general_submit" onclick="return aw_ar_setting_submit(event)" />	
									</div>
							</div>

							<div class="tabcontent ar-email-set" id="aw_cc_email-setting-tab" style="display:none;">
							<table class="form-table">
								<tbody>
									<tr valign="top">
									<td class="wc_emails_wrapper" colspan="2">
										<table class="wc_emails widefat" cellspacing="0">
											<thead>
												<tr>
													<th class="wc-email-settings-table-status">Status</th><th class="wc-email-settings-table-name">Email</th><th class="wc-email-settings-table-email_type">Content</th><th class="wc-email-settings-table-recipient">Recipients</th><th class="wc-email-settings-table-actions">Manage</th>	
												</tr>
											</thead>
											<tbody>
												<?php 
												if (!empty($email_template)) { 
													foreach ($email_template as $template) { 
														?>
													<tr>
														<td class="wc-email-settings-table-status" data-colname="Status">
															<?php
															$status_class = 'status-disabled';
															$setting_status ='Disabled';
															if (1 == $template->active) {
															$status_class = 'status-enabled';
															$setting_status ='Enabled';
															}
															?>
															<div class="tooltip">
															<span class="<?php echo wp_kses($status_class, wp_kses_allowed_html('post')); ?> tips"><?php echo wp_kses($template->active, wp_kses_allowed_html('post')); ?>
															</span>
															<span class="tooltiptext"><?php echo wp_kses($setting_status, wp_kses_allowed_html('post')); ?></span>
															</div>

														</td>
														<td class="wc-email-settings-table-name column-primary" data-colname="Email">
															<a href="<?php echo esc_url(admin_url('admin.php?page=company-credit-emails&ID=' . $template->id)) ; ?>"><?php echo wp_kses($template->email, wp_kses_allowed_html('post')); ?></a>
															<span class="woocommerce-help-tip"></span>
														</td>
														<td class="wc-email-settings-table-email_type" data-colname="Content">
														<?php 
														echo wp_kses($template->email_type, wp_kses_allowed_html('post'));
														?>
														</td>
														<td class="wc-email-settings-table-recipient" data-colname="Recipient">
														<?php 
														if ('customer' != $template->recipients) {
															echo wp_kses($template->recipients, wp_kses_allowed_html('post'));} else {
															echo 'Customer'; } 
															?>
														</td>
														<td class="wc-email-settings-table-actions">
															<a class="button" href="<?php echo esc_url(admin_url('admin.php?page=company-credit-emails&ID=' . $template->id)) ; ?>">
															<?php echo 'Manage'; ?></a>
														</td>
													</tr>
													<?php 
													} 
												}
												?>
											</tbody>
										</table>
									</td>
									</tr>
								</tbody>
							</table>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public static function aw_company_credit_save_setting_form() {
		$original_text = '';
		$modified_text = '';
		$url =  admin_url() . 'admin.php?page=credit-configuration';
		if (isset($_POST['awcompanycredit_admin_nonce'])) {
			$awcompanycredit_admin_nonce = sanitize_text_field($_POST['awcompanycredit_admin_nonce']);
		}

		if ( !wp_verify_nonce( $awcompanycredit_admin_nonce, 'save_company_credit_setting' )) {
			wp_die('Our Site is protected');
		}

		if (isset($_POST['cc_setting_general_submit'])) {
			if (isset($_POST['aw_cc_enable'])) {
				$aw_cc_enable = sanitize_text_field($_POST['aw_cc_enable']);
				update_option('aw_cc_enable', $aw_cc_enable);
			} /*else {
				update_option('aw_cc_enable', '');
			}		*/ 
		
			if (isset($_POST['aw_cc_title'])) {
				$aw_cc_title = sanitize_text_field($_POST['aw_cc_title']);
				update_option('aw_cc_title', $aw_cc_title);
			}			

			if (isset($_POST['aw_cc_neworderstatus']) && '' != sanitize_text_field($_POST['aw_cc_neworderstatus'])) {
				update_option('aw_cc_neworderstatus', sanitize_text_field($_POST['aw_cc_neworderstatus']));
			}

			if (isset($_POST['aw_cc_credit_limit'])) {
				update_option('aw_cc_credit_limit', sanitize_text_field($_POST['aw_cc_credit_limit']));
			}

			if (isset($_POST['aw_cc_min_ordertotal'])) {
				update_option('aw_cc_min_ordertotal', sanitize_text_field($_POST['aw_cc_min_ordertotal']));
			} 			
			 
			if (isset($_POST['aw_cc_max_ordertotal'])) {
				update_option('aw_cc_max_ordertotal', sanitize_text_field($_POST['aw_cc_max_ordertotal']));
			} 

			if (isset($_POST['aw_cc_meta_description'])) {
				update_option('aw_cc_meta_description', sanitize_text_field($_POST['aw_cc_meta_description']));
			} else {
				update_option('aw_cc_meta_description', '');
			}
		}
		self::aw_company_credit_add_flash_notice( __('General settings updated'), 'success', true );
		wp_redirect($url);

	}
	public static function aw_cc_admin_email_setting() {
		$data 				= array();
		$id 				= '';
		$email 				= '';
		$recipient 			= '';
		$subject 			= '';
		$email_heading 		= '';
		$additional_content = '';
		$checked 			= '';
		$email_type 		= '';
		$active 			= '';

		$default_settings[1] = array(
							'email'				=> 'Credit LImit Update',
							'subject'			=> 'Your Credit Balance has been updated',
							'email_heading'		=> 'Your Credit Balance has been updated',
							'additional_content'=> 'Dear {customer_name} 
						Your Credit Balance has been updated.',
							'Formtitle'			=> 'Notification When Credit Balance is Updated',
						);
						

		if (isset($_GET['ID']) && !empty($_GET['ID'])) {
			$id = sanitize_text_field($_GET['ID']);
			$default_email = $default_settings[$id]['email'];
			$default_subject = $default_settings[$id]['subject'];
			$default_email_heading = $default_settings[$id]['email_heading'];
			$default_additional_content = $default_settings[$id]['additional_content'];

			$data = aw_cc_get_email_template_setting_row($id);

			if (!empty($data)) {
				$email 				= $data->email;	
				$active 			= $data->active;	
				$recipient 			= $data->recipients;	
				$subject 			= $data->subject;	 
				$email_heading 		= $data->email_heading;
				$additional_content = $data->additional_content; 
				$email_type 		= $data->email_type;  
				if ($active) {
					$checked = 'checked = checked';
				}
			}			
		}
		?>
		<h2>
			<?php echo wp_kses($email, wp_kses_allowed_html('post')); ?>
			<small class="wc-admin-breadcrumb"><a href="<?php echo wp_kses(admin_url(), wp_kses_allowed_html('post')); ?>admin.php?page=credit-configuration&amp;tab=aw-cc-emails" aria-label="Return to emails">⤴</a></small>
		</h2>
		<p><?php echo wp_kses($default_settings[$id]['Formtitle'], wp_kses_allowed_html('post')); ?></p>
		<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" onsubmit="return aw_cc_email_templvalidateForm()">
			<?php wp_nonce_field( 'save_company_credit_email_setting', 'awcompanycredit_admin_nonce' ); ?>
			<input type="hidden" name="action" value="aw_cc_save_email_templates_setting">
			<input type="hidden" class="emailformname" value="<?php echo wp_kses($email, wp_kses_allowed_html('post')); ?>">
			<input type="hidden" name="ID" value="<?php echo wp_kses($id, wp_kses_allowed_html('post')); ?>"><table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="woocommerce_new_order_enabled">Enable/Disable</label>
						</th>
						<td class="forminp">
							<fieldset>
								<legend class="screen-reader-text"><span>Enable/Disable</span></legend>
								<label for="woocommerce_new_order_enabled">
								<input class="" type="checkbox" name="active" value="<?php echo wp_kses($active, wp_kses_allowed_html('post')); ?>" <?php echo wp_kses($checked, wp_kses_allowed_html('post')); ?>>Enable this email notification</label><br>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="woocommerce_new_order_subject">Subject<span class="woocommerce-help-tip"></span></label>
						</th>
						<td class="forminp">
							<fieldset>
								<legend class="screen-reader-text"><span>Subject</span></legend>
								<input class="input-text regular-input aw_cc_mailsubject" type="text" name="subject" value="<?php echo wp_kses($subject, wp_kses_allowed_html('post')); ?>"placeholder="<?php echo wp_kses($default_subject, wp_kses_allowed_html('post')); ?>" maxlength="50">
								<p><span class="aw_cc_mailsubject_msg"></span></p>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="woocommerce_new_order_heading">Email heading<span class="woocommerce-help-tip"></span></label>
						</th>
						<td class="forminp">
							<fieldset>
								<legend class="screen-reader-text"><span>Email heading</span></legend>
								<input class="input-text regular-input required aw_cc_email_heading" type="text" name="email_heading" value="<?php echo wp_kses($email_heading, wp_kses_allowed_html('post')); ?>"placeholder="<?php echo wp_kses($default_email_heading, wp_kses_allowed_html('post')); ?>" maxlength="50">
								<p><span class="aw_cc_email_heading_msg"></span></p>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="woocommerce_new_order_additional_content">Additional content<span class="woocommerce-help-tip"></span></label>
						</th>
						<td class="forminp">
							<fieldset>
								<legend class="screen-reader-text"><span>Additional content; ?></span></legend>
								<textarea rows="3" cols="20" class="input-text wide-input required aw_cc_additional_content" type="textarea" name="additional_content" placeholder="<?php echo wp_kses($default_additional_content, wp_kses_allowed_html('post')); ?>"><?php echo wp_kses($additional_content, wp_kses_allowed_html('post')); ?></textarea>
								<p><span class="aw_cc_additional_content_msg"></span></p>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="woocommerce_new_order_email_type">Email type<span class="woocommerce-help-tip"></span></label>
						</th>
						<td class="forminp">
							<fieldset>
								<legend class="screen-reader-text"><span>Email type</span></legend>
								<select class="select email_type wc-enhanced-select select2-hidden-accessible enhanced" name="email_type" style="" tabindex="-1" aria-hidden="true">
									<option 
									<?php 
									if ('plain' === $email_type) {
										echo 'selected="selected"';
									} 
									?>
									 value="text/plain">Plain text</option>
									<option 
									<?php 
									if ('text/html' === $email_type) {
										echo 'selected="selected"';
									} 
									?>
									 value="text/html" >HTML</option>
								</select>
							</fieldset>
						</td>
					</tr>

				</tbody>
				<tfoot>
					<tr valign="top">
						<td>
							<button name="aw_cc_email_setting_submit" class="button-primary woocommerce-save-button" type="submit" value="Save changes">Save changes</button>
						</td>
					</tr>
				</tfoot>
			</table>
		</form>	
		<?php 
	}
	public static function aw_cc_save_email_templates_setting() {
		global $wpdb; 
		$url =  admin_url() . 'admin.php?page=credit-configuration&tab=aw-cc-emails';
		if (isset($_POST['awcompanycredit_admin_nonce'])) {
			$awcompanycredit_admin_nonce = sanitize_text_field($_POST['awcompanycredit_admin_nonce']);
		}

		if ( !wp_verify_nonce( $awcompanycredit_admin_nonce, 'save_company_credit_email_setting' )) {
			wp_die('Our Site is protected');
		}

		if (isset($_POST['aw_cc_email_setting_submit'])) {
			if (isset($_POST['ID']) && !empty($_POST['ID'])) {
				$ID = sanitize_text_field($_POST['ID']);
			} else {
				$ID = '';
			} 
			if (isset($_POST['active'])) {
				$active = '1';
			} else {
				$active = 0;
			}
			if (isset($_POST['recipients'])) {
				$recipients = sanitize_text_field($_POST['recipients']);
			} else {
				$recipients = 'Customer';
			}
			if (isset($_POST['subject'])) {
				$subject = sanitize_text_field($_POST['subject']);
			} else {
				$subject = '';
			}
			if (isset($_POST['email_heading'])) {
				$email_heading = sanitize_text_field($_POST['email_heading']);
			} else {
				$email_heading = '';
			}
			if (isset($_POST['additional_content'])) {
				$additional_content = sanitize_text_field($_POST['additional_content']);
			} else {
				$additional_content = '';
			}
			if (isset($_POST['email_type'])) {
				$email_type = sanitize_text_field($_POST['email_type']);
			} 
			$db_table = $wpdb->prefix . 'aw_cc_email_templates';
			$post_array = array(
								'active'=>$active,
								'recipients'=>$recipients,
								'subject'=>$subject,
								'email_heading'=>$email_heading,
								'additional_content'=>$additional_content,
								'email_type' =>$email_type	
							);
			if ('' != $ID ) {
				$result = $wpdb->update($db_table, $post_array, array('ID'=>$ID));	
			}
			self::aw_company_credit_add_flash_notice( __('Email templates setting updated'), 'success', true );
			wp_redirect($url);
		}
	}
 
	public static function aw_credit_balance_html() {
		// Update credit limit if new user found
		aw_cc_update_new_user_credit_limit();

		?>
			 <div class="page-title">
				<h1 class="wp-heading-inline"><?php echo 'Customers'; ?></h1>
			</div>
			<div class="panel-body">
				 
				<div class="tabcontent">
					<?php
						$tablename = 'aw_company_credit_balance';
						$bal_trans = new AwcccustomerListAdmin();
						$search = '';
					if (isset($_GET['s'])) {
						$search = sanitize_text_field($_GET['s']);
						if (isset($_GET['d'])) {
							$link_search = '';
						}
						$count_all = $bal_trans->get_count($tablename);
					}
					?>
					<form id="posts-filter" method="get">
						<p class="search-box">
							<input type="hidden" name="page" class="page" value="customer-credit-balance">	
							<input type="search" id="post-search-input" name="s" value="<?php echo isset($_GET['d']) ? esc_html($link_search) : esc_html($search); ?>">
							<input type="submit" id="search-submit" class="button" value="Search Customer" title="Search Customer by Customer Name or Customer Email">
						</p>
					</form>
					<form id="cc-userlist-table" method="GET">
						<?php
							$bal_trans->prepare_items($search);
							$bal_trans->display();
						?>
					</form>
				</div>
			</div>
		<?php
	}

	public static function aw_cc_admin_credit_history_list() {
		//require_once( plugin_dir_path(__FILE__) . 'aw-cc-customer-history-admin.php' );
		$id = 0;
		if (isset($_GET['id'])) {
			$id = sanitize_text_field($_GET['id']);
		}

		//$id = $id;//base64_decode($id);

		$code_information 		= 0;
		$transactions_history 	= 0;
		$credit_limit 			= '';
		$display_name 			= '';
		$user_email				= '';
		$user = get_user_by( 'id', $id );
		if (!empty($user)) {
			//if ($user->first_name && $user->last_name) {
			$customer 		= new WC_Customer( $id );
			$first_name   	= $customer->get_first_name();
			$last_name    	= $customer->get_last_name();
			$display_name 	= $customer->get_display_name(); 
			$user_email 	= $user->user_email;
		}

		$credit_detail 			= aw_cc_get_user_credit_detail($id);
		if (!empty($credit_detail)) {
			$credit_limit 		= $credit_detail->credit_limit;
			$credit_balance		= $credit_detail->credit_balance;
			$available_credit	= $credit_detail->available_credit;	
		}
		?>
		<div class="wrap">
			<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
			<h1 class="wp-heading-inline"><?php echo wp_kses_post($display_name); ?></h1>
			<hr class="wp-header-end">
			
				<?php 
				if (!empty(get_option( 'aw_cc_notification_add_msg' ))) { 
					?>
					<div class="notice notice-success is-dismissible">
						<p><?php echo esc_html(get_option('aw_cc_notification_add_msg')); ?></p>
					</div>
					<?php 
					delete_option( 'aw_cc_notification_add_msg' ); 
				} 

				$notice = get_option( 'company_credit_flash_notices', array() );
				if ( ! empty( $notice ) ) {
						printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
							wp_kses($notice['type'], wp_kses_allowed_html('post')),
							wp_kses($notice['dismissible'], wp_kses_allowed_html('post')),
							wp_kses($notice['notice'], wp_kses_allowed_html('post'))
						);
					delete_option( 'company_credit_flash_notices', array() );
				}
				?>
			<div class="cc-col-row">
				<div class="cc-col-two">
					<div class="card">
						<div class="card-body">
							<h3>Credit Information</h3> 
							<div class="aw_cc_credit_information">
								 
								<div class="cc-col-row">
									<div class="cc-col-three">
										<label>Credit Balance</label><br/>
										<strong>
										<?php
										if (!empty($credit_balance)) {
											if ($credit_balance<0) {
												echo '-' . wp_kses_post(aw_cc_get_amount(abs($credit_balance)));
											} else {
												echo wp_kses_post(aw_cc_get_amount($credit_balance)); 
											}
											
										}
										?>
										</strong>
									</div>
									<div class="cc-col-three">
										<label>Available Credit</label><br/>
										<strong>
										<?php 
										if (!empty($available_credit)) {
											echo wp_kses_post(aw_cc_get_amount($available_credit )); 
										}
										?>
										</strong>
									</div>
									<div class="cc-col-three">
										<label>Credit Limit</label><br/>
										<strong>
										<?php
										if (!empty($credit_limit)) {
											echo wp_kses_post(aw_cc_get_amount($credit_limit)); 
										}
										?>
										</strong>
									</div>
								</div>

							</div>

						</div>
					</div>
				</div>
				<div class="cc-col-two">
					<div class="card">
						<div class="card-body">
							<h3>Personal Information</h3>
							<div class="aw_cc_user_information">
								<div class="cc-col-row">
									<div class="cc-col-three">
										<label>Name</label><br/>
										<strong><?php echo wp_kses_post($display_name); ?></strong>
									</div>
									 
									<div class="cc-col-three">
										<label>Email</label><br/>
										<strong><?php echo wp_kses_post($user_email); ?></strong>
									</div>
								</div>
							</div>
						</div>
					</div>                    
				</div>
			</div>

			<div class="cc-col-row eql-clm">
				<div class="cc-col-two">
					<div class="card">
						<div class="card-body">
							<div class="cc-aw-bal-info clearfix">
							<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data">
							<?php wp_nonce_field( 'save_customer_credit_setting', 'awcustomercredit_admin_nonce' ); ?>
								<input type="hidden" name="action" value="customer_credit_save_setting_form">
								<input type="hidden" name="user_id" value="<?php echo wp_kses_post($id); ?>">
							<div class="ccc-aw-bal-info-col">
								<h3>Credit Limit</h3>
								<ul>
									<li>
										<label>Custom Credit Limit</label>
										<div class="control">
											<input type="input" name="credit_limit" value="<?php echo wp_kses($credit_limit , wp_kses_allowed_html('post')); ?>" onkeypress="return aw_cc_checkIt(event, '.')">
										</div>
									</li> 
									<li>
										<label>Comment <span>(visible to admin only)</span></label>
										<div class="control">
											<textarea name="credit_comment_to_admin" class="input-text wide-input"></textarea> 
										</div>
									</li>
								</ul>
								<div>
								 <button name="cc_customer_crdit_submit" class="button-primary woocommerce-save-button" type="submit" value="Save changes">Save Credit Limit</button>
							 </div>
							  </div>

							
							</form>
							</div>
						</div>
					</div>
				</div>
				<div class="cc-col-two">
					<div class="card">
						<div class="card-body">
							<div class="cc-aw-bal-info clearfix">
							<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data">
							<?php wp_nonce_field( 'save_customer_credit_setting', 'awcustomercredit_admin_nonce' ); ?>
								<input type="hidden" name="action" value="customer_credit_save_setting_form">
								<input type="hidden" name="user_id" value="<?php echo wp_kses_post($id); ?>">
							<div class="ccc-aw-bal-info-col update-bal">
								<h3>Update Balance</h3> 
								 <ul>
									<li>
										<label> Amount to Add </label>
										<div class="control">
											<input type="input" name="transaction_amount" value="" onkeypress="return aw_cc_checkIt_minus(event, '.','-')">
										</div>
									</li>
									<li>
										<label> Comment <span>(visible to customer only)</span></label>
										<div class="control">
											<textarea name="comment_to_customer" class="input-text wide-input"></textarea>  
										</div>
									</li>
									<li>
										<label> Comment <span>(visible to admin only)</span></label>
										<div class="control">
											<textarea name="comment_to_admin" class="input-text wide-input"></textarea>  
										</div>
									</li>
									 
								</ul>

								<div>
								 <button name="cc_customer_balance_submit" class="button-primary woocommerce-save-button" type="submit" value="Save changes">Update Balance</button>
								 </div>
							 </div>
							</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>	

		<div class="panel-body" id="aw_cc_hostory_grid">
			<h2>Balance History</h2>
			<div class="tabcontent">
				<?php
					$user_id 		= '';
					$tablename 		= 'aw_company_credit_history';
					$history_list 	= new AwccCreditHistoryListAdmin();
					$search = '';
				if (isset($_GET['s'])) {
					$search = sanitize_text_field($_GET['s']);
					/*if (isset($_GET['d'])) {
						$link_search = '';
					}*/
					$count_all = $history_list->get_count($tablename);
				} 
				if (isset($_GET['id'])) {
					$user_id = sanitize_text_field($_GET['id']);
				}
				
				?>
				<form id="posts-filter" method="get">
						<p class="search-box">
							<input type="hidden" name="page" class="page" value="customer-credit-history">	
							<input type="hidden" name="id" value="<?php echo wp_kses_post($user_id); ?>">
							<input type="search" id="post-search-input" name="s" value="<?php echo isset($_GET['d']) ? esc_html($link_search) : esc_html($search); ?>">
							<input type="submit" id="search-submit" class="button" value="Search" title="Search by Date, Action, Amount, Credit Balance, Available Balance, Credit Limit, Comment to Customer, Comment to Admin">
						</p>
				</form>
				<form id="aw-cc-history-table" method="GET">
					<input type="hidden" name="page" class="page" value="customer-credit-history">
					<input type="hidden" name="id" value="<?php echo wp_kses_post($user_id); ?>">
					<?php
						$history_list->prepare_items($search, $id);
						$history_list->display();
					?>
				</form>
			</div>
		</div>	
		<?php 
	}

	public static function aw_customer_credit_save_setting_form() {
		global $wpdb;
		$post_array 		= array();
		$trans_array 		= array();
		$url 				= admin_url() . 'admin.php?page=customer-credit-history&id=';
		$balance_table 		= $wpdb->prefix . 'aw_company_credit_balance';
		$history_table 		= $wpdb->prefix . 'aw_company_credit_history';
		$credit_limit 		= '';
		$status 			= '';
		$comment_to_admin 	= '';
		$comment_to_customer= '';
		$transaction_amount = 0;

		$previous_credit_limit = '';
		if (isset($_POST['awcustomercredit_admin_nonce'])) {
			$awcustomercredit_admin_nonce = sanitize_text_field($_POST['awcustomercredit_admin_nonce']);
		}

		if ( !wp_verify_nonce( $awcustomercredit_admin_nonce, 'save_customer_credit_setting' )) {
			wp_die('Our Site is protected');
		}

		if (isset($_POST['cc_customer_crdit_submit'])) {
			
			if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
				$ID = sanitize_text_field($_POST['user_id']);
				$the_user = get_user_by( 'id', $ID );
			} else {
				$ID = '';
			} 

			$credit_detail 			= aw_cc_get_user_credit_detail($ID);
			if (!empty($credit_detail)) {

				$previous_credit_limit 	= $credit_detail->credit_limit;
				$credit_limit 			= $credit_detail->credit_limit;	
			}
			
			if (isset($_POST['credit_comment_to_admin']) && !empty(sanitize_text_field($_POST['credit_comment_to_admin']))) {
				$comment_to_admin = sanitize_text_field($_POST['credit_comment_to_admin']);
			} 

			if (isset($_POST['credit_limit'])) {
				$credit_limit 	= (float) sanitize_text_field($_POST['credit_limit']);
				if ($credit_limit>0) {
					$post_array = array('credit_limit'=> $credit_limit,'last_payment'=>gmdate('Y-m-d H:i:s'));
					$balance_detail	= self::aw_cc_calculate_updated_balance($ID);
					if ($credit_limit != $previous_credit_limit ) {
						$post_array['available_credit']	= (float) $balance_detail['available_credit'] + (float) $credit_limit;
					}
					if ('' != $ID ) {
						$result = $wpdb->update($balance_table, $post_array, array('user_id'=>$ID));	
					}
					self::aw_company_credit_add_flash_notice( __('Credit detail updated successfully'), 'success', true );
				} else {
					self::aw_company_credit_add_flash_notice( __('Zero amount cannot be assigned as credit limit'), 'error', true );
				}
			}  
			 
			$balance_detail	= self::aw_cc_calculate_updated_balance($ID);	
			if ( !empty($balance_detail) && $credit_limit>0 ) {
				$credit_balance  = (float) $balance_detail['credit_balance'];
				$post_array['credit_balance'] = $credit_balance;
				$available_credit = (float) $balance_detail['available_credit'];

				if ( $credit_limit != $previous_credit_limit ) {
					$post_array['credit_balance'] 	= $credit_balance;
					$available_credit 				= $credit_balance+$credit_limit;
					
				}
				if ($previous_credit_limit > 0 && $available_credit>0) {
					$status = 'Changed';
				}

				$post_array['available_credit'] = $available_credit;	
				$post_array['last_payment']		= gmdate('Y-m-d H:i:s');
				$result = $wpdb->update( $balance_table, $post_array, array( 'user_id'=>$ID ));	
				if ( $credit_limit != $previous_credit_limit) {
					$transaction_array 	= array(
						'user_id' 				=> $ID,
						'transaction_amount'	=> $transaction_amount,
						'credit_balance' 		=> $credit_balance,
						'available_credit' 		=> $available_credit,
						'credit_limit' 			=> $credit_limit,
						'transaction_status'	=> $status,
						'comment_to_customer'	=> $comment_to_customer,
						'comment_to_admin'		=> $comment_to_admin,
					);
					
					$wpdb->insert($history_table, $transaction_array);

					unset($transaction_array['comment_to_admin']);
					unset($transaction_array['comment_to_customer']);
					unset($transaction_array['user_id']);
					unset($transaction_array['transaction_status']);
					$transaction_array 	= array_map('aw_cc_display_actual_amount', $transaction_array);
					$user_name 			= $the_user->display_name;
					$user_email 		= $the_user->user_email;
					$mail_template 		= 'Credit Balance Updated';
					aw_cc_send_mail_after_update_credit_balance( $user_name, $user_email, $transaction_array, $mail_template);
					self::aw_company_credit_add_flash_notice( __('Credit detail updated successfully'), 'success', true );
				}

			}
			$url.= $ID;
			wp_redirect($url);
		}

		if (isset($_POST['cc_customer_balance_submit'])) {

			$transaction_amount = 0;
			if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
				$ID = sanitize_text_field($_POST['user_id']);
				$the_user = get_user_by( 'id', $ID );
			} else {
				$ID = '';
			} 

			$credit_detail = aw_cc_get_user_credit_detail($ID);
			
			if (!empty($credit_detail)) {
				$previous_credit_limit 	= $credit_detail->credit_limit;
				$credit_limit 			= $credit_detail->credit_limit;	
			}
			
			if (isset($_POST['transaction_amount'])) {
				$transaction_amount = (float) sanitize_text_field($_POST['transaction_amount']);
				$status = 'Updated';
			} else {
				$transaction_amount = 0;
				
			}

			if (isset($_POST['comment_to_customer']) && !empty($_POST['comment_to_customer']) ) {
					$comment_to_customer = sanitize_text_field($_POST['comment_to_customer']);
			}

			if (isset($_POST['comment_to_admin']) && !empty(sanitize_text_field($_POST['comment_to_admin']))) {
				$comment_to_admin = sanitize_text_field($_POST['comment_to_admin']);
			} 
			
			if ( /*$credit_limit >= abs($transaction_amount)*/ $credit_limit > 0) {
				$balance_detail	= self::aw_cc_calculate_updated_balance($ID);	
				if ( !empty($balance_detail) ) {
					$credit_balance  = (float) $balance_detail['credit_balance'];
					$post_array['credit_balance'] = $credit_balance;
					$available_credit = (float) $balance_detail['available_credit'];

					//if (0 != $transaction_amount ) {
					$credit_balance 				= $credit_balance+$transaction_amount;
					$post_array['credit_balance'] 	= $credit_balance;
					$available_credit 				= $available_credit+$transaction_amount;
					//}
					
					$post_array['available_credit'] = $available_credit;	
					$post_array['last_payment']		= gmdate('Y-m-d H:i:s');	
					$result = $wpdb->update( $balance_table, $post_array, array( 'user_id'=>$ID ));	
					//if ( 0!=$transaction_amount) {
						$transaction_array 	= array(
							'user_id' 				=> $ID,
							'transaction_amount'	=> $transaction_amount,
							'credit_balance' 		=> $credit_balance,
							'available_credit' 		=> $available_credit,
							'credit_limit' 			=> $credit_limit,
							'transaction_status'	=> $status,
							'comment_to_customer'	=> $comment_to_customer,
							'comment_to_admin'		=> $comment_to_admin,
						);
						
						$wpdb->insert($history_table, $transaction_array);

						unset($transaction_array['comment_to_admin']);
						unset($transaction_array['comment_to_customer']);
						unset($transaction_array['user_id']);
						unset($transaction_array['transaction_status']);
						$transaction_array 	= array_map('aw_cc_display_actual_amount', $transaction_array);
						$transaction_array['comment_to_customer'] = $comment_to_customer;
						$user_name = $the_user->display_name;
						$user_email = $the_user->user_email;
						$mail_template = 'Credit Balance Updated';
						aw_cc_send_mail_after_update_credit_balance( $user_name, $user_email, $transaction_array, $mail_template);
						self::aw_company_credit_add_flash_notice( __('Credit balance updated successfully'), 'success', true );
						//wp_redirect($url);
					//}
				}
			} else {
				if (0 == $credit_limit || $transaction_amount <= 0) {
					self::aw_company_credit_add_flash_notice( __('Please specify Credit Limit for customer before updating amount'), 'error', true );	
				} else {
					self::aw_company_credit_add_flash_notice( __('Amount to add should be more than existing Credit Limit'), 'error', true );	
				}
			}
			$url.= $ID;
			wp_redirect($url);
		}
	}

	public static function aw_cc_calculate_updated_balance( $id) {
		global $wpdb;
		$transaction= array();
		$credit_detail = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_company_credit_balance WHERE user_id = %d ", "{$id}"), ARRAY_A );
		return $credit_detail;
	}
}
