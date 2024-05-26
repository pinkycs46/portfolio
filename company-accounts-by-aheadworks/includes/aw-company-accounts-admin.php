<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
$companyaccountsadmin 	= new AwCompanyAccountsAdmin();

class AwCompanyAccountsAdmin {
	public $GLOBALS;
	public function __construct() {

		add_filter('set-screen-option', array(get_called_class(),'aw_ca_set_screen'), 11, 3);

		add_action('admin_post_company_accounts_save_setting_form', array(get_called_class(),'aw_company_accounts_save_setting_form'));
		add_action('admin_post_aw_ca_save_email_templates_setting', array(get_called_class(),'aw_ca_save_email_templates_setting'));
		add_action('admin_post_company_data_save_form', array(get_called_class(),'aw_ca_company_data_save_form'));
		add_action( 'user_register', array(get_called_class(),'aw_ca_company_user_updated_meta'));
		add_filter('pre_get_users', array(get_called_class(),'aw_ca_users_list_by_company'));
		add_filter('pre_get_posts', array(get_called_class(),'aw_ca_orders_list_by_company'));

		add_action('manage_users_columns', array(get_called_class(),'aw_ca_userstable_add_custom_column'));
		add_action('manage_users_custom_column', array(get_called_class(),'aw_ca_userstable_add_custom_column_view'), 10, 3);

		add_filter('manage_users_sortable_columns', array(get_called_class(),'aw_ca_register_sortable_column'));

		add_action('admin_post_ca_role_permission_save_form', array(get_called_class(),'ca_role_permission_save_form'));

		add_action('wp_ajax_aw_ca_get_domain_detail_ajax', array(get_called_class(),'aw_ca_get_domain_detail_ajax'));

		// Restrict Order status 
		add_filter( 'wc_order_statuses', array(get_called_class(),'aw_ca_restrict_order_statuses_update') );
		add_filter( 'bulk_actions-edit-shop_order', array(get_called_class(),'aw_ca_filter_dropdown_bulk_actions_shop_order'), 20, 1 );
		add_filter( 'manage_edit-shop_order_columns', array(get_called_class(),'aw_ca_remove_specific_orders_column') );

		// Remove role from drop down list
		add_action( 'editable_roles' , array(get_called_class(),'aw_ca_hide_editable_roles' ));
	}

	public static function aw_ca_self_deactivate_notice() {
		/** Display an error message when WooComemrce Plugin is missing **/
		?>
		<div class="notice notice-error">
			<p>Please install and activate WooComemrce plugin before activating Product Questions By Aheadworks Plugin.</p>
		</div>
		<?php
	}

	public static function aw_company_accounts_add_flash_notice( $notice = '', $type = 'warning', $dismissible = true ) { 
		// Here we return the notices saved on our option, if there are not notices, then an empty array is returned
		$notices = get_option( 'company_accounts_flash_notices', array() );
		$dismissible_text = ( $dismissible ) ? 'is-dismissible' : '';

		$notices = array(
						'notice' => $notice, 
						'type' => $type, 
						'dismissible' => $dismissible_text
					); 	
		update_option( 'company_accounts_flash_notices', $notices );
	}

	public static function aw_ca_set_screen( $status, $option, $value) {

		if ('aw_ca_companylist_per_page' == $option) {
			$user 	= get_current_user_id();	
			$screen = get_current_screen();
			update_user_meta($user, 'aw_ca_companylist_per_page', $value);
			return $value;
		}	
		if ('aw_ca_rolelist_per_page' == $option) {
			$role 	= get_current_user_id();	
			$screen = get_current_screen();
			update_user_meta($role, 'aw_ca_rolelist_per_page', $value);
			return $value;
		}
		return $status;
	}

	public static function aw_ca_company_list_screen_option() {
		$option = 'per_page';
		$args = array(
					'label' 	=> 'Number of items per page:',
					'default' 	=> 10,
					'option' 	=> 'aw_ca_companylist_per_page'
			);
		$data = add_screen_option( $option, $args );
		$company_list = new AwcaCompanyListAdmin();
	}

	public static function aw_ca_role_permission_screen_option() {
		$option = 'per_page';
		$args = array(
					'label' 	=> 'Number of items per page:',
					'default' 	=> 10,
					'option' 	=> 'aw_ca_rolelist_per_page'
			);
		$data = add_screen_option( $option, $args );
		$role_list = new AwcaRolePermissionAdmin();
	}

	public static function aw_company_accounts_menu() {
		global $current_user;
		$capabilities = 'manage_options';
		if ( current_user_can( 'company_right' ) ) {
			$capabilities = 'company_right';
		}
		add_menu_page(__('Company Accounts', 'main_menu'), __('Company Accounts', 'main_menu'), '', 'AwCompanyAccounts', 'toplevel_page' , plugins_url('/company-accounts-by-aheadworks/admin/images/aw_company_accounts.png'), 25);
		$user_roles = $current_user->roles[0];
		if ( 'company_admin' === $user_roles ) {
			$user_id 	= $current_user->ID;
			$company_id = get_user_meta($user_id, 'company_id', true);
			$company 	= aw_ca_get_company_by_id( $company_id );
			if ('approved' === $company->status) {
				add_submenu_page('AwCompanyAccounts', __('Company Information'), 'Company Information', $capabilities, 'new-company-form', array(get_called_class() , 'aw_ca_company_form_admin'));

				add_submenu_page('', __('Add New Role'), 'Add New Role', $capabilities, 'add-new-role', array(get_called_class() , 'aw_ca_add_new_role'));

				$rolepermission = add_menu_page( 'Role and Permission', 'Role and Permission', 'edit_posts', 'roles-and-permission', array(get_called_class() ,'aw_ca_role_permission'), 'dashicons-groups', 90 );
				add_action( "load-$rolepermission", array(get_called_class(),'aw_ca_role_permission_screen_option'));
			}
		} else {
			add_submenu_page('AwCompanyAccounts', __('Setting', 'main_menu'), __('Configurations', 'main_menu'), $capabilities, 'company-accounts-configuration', array(get_called_class(),'aw_companyaccountconfiguration_html'));
			add_submenu_page('AwCompanyAccounts', __('Sales Representative', 'main_menu'), __('Sales Representative', 'main_menu'), $capabilities, 'company-accounts-sales-representative', array(get_called_class(),'aw_salesrepresentativeconfiguration_html'));

			$hookhistory =add_submenu_page('AwCompanyAccounts', __('Companies', 'main_menu'), __('Companies', 'main_menu'), $capabilities, 'company-accounts-company-list', array(get_called_class(),'aw_companyaccount_companylist'));
			add_action( "load-$hookhistory", array(get_called_class(),'aw_ca_company_list_screen_option'));
			add_submenu_page('', __('Emails'), '', $capabilities, 'company-accounts-emails', array(get_called_class() , 'aw_ca_admin_email_setting'));
			add_submenu_page('', __('New Company'), '', $capabilities, 'new-company-form', array(get_called_class() , 'aw_ca_company_form_admin'));	
		}
		
		if ( !current_user_can('administrator') ) {
			remove_menu_page('edit.php'); // Posts
			remove_menu_page('upload.php'); // Media
			remove_menu_page('link-manager.php'); // Links
			remove_menu_page('edit-comments.php'); // Comments
			remove_menu_page('edit.php?post_type=page'); // Pages
			remove_menu_page('plugins.php'); // Plugins
			remove_menu_page('themes.php'); // Appearance
			remove_menu_page('tools.php'); // Tools
			remove_menu_page('options-general.php'); // Settings
		}
		/*$hookhistory =add_submenu_page('', __('Company Credit History'), '', 'manage_options', 'customer-credit-history', array(get_called_class() , 'aw_ca_admin_credit_history_list'));
		add_action( "load-$hookhistory", array(get_called_class(),'aw_ca_customer_history_balance_screen_option'));*/
		//add_action( "load-$hook", array(get_called_class(),'aw_reward_add_screen_option'));
	}

	public static function aw_companyaccountconfiguration_html() {
		$aw_ca_enable 			= ''; 
		$aw_ca_title 			= ''; 
		$aw_ca_neworderstatus 	= '';
		$aw_ca_credit_limit 	= ''; 
		$aw_ca_min_ordertotal 	= '';
		$aw_ca_max_ordertotal 	= '';
		$aw_ca_enable_no 		= '';
		$aw_ca_enable_yes 		= '';

		$salesrepr_obj 	= get_users( [ 'role__in' => [ 'sales_representative' ] ] ); 
		$email_template = aw_ca_get_email_template_setting_results('configuremail');	

		$notice = get_option( 'company_accounts_flash_notices', array() );
		if ( ! empty( $notice ) ) {
				printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
					wp_kses($notice['type'], wp_kses_allowed_html('post')),
					wp_kses($notice['dismissible'], wp_kses_allowed_html('post')),
					wp_kses($notice['notice'], wp_kses_allowed_html('post'))
				);
			delete_option( 'company_accounts_flash_notices', array() );
		}
		$searilized_form_setting 	= get_option('aw_ca_default_form', true);
		$company_form_setting 		= maybe_unserialize($searilized_form_setting);
		$approval = get_option('aw_ca_order_approvalenable');
		if ('yes' === $approval) {
			$aw_ca_enable_yes = 'selected="selected"';
		}
		if ('no' === $approval) {
			$aw_ca_enable_no = 'selected="selected"';
		}
		?>
		<div class="aw-ca-tab-grid-wrapper">
			<div class="spw-rw clearfix">
				<div class="panel-box company-accounts-setting">
					<div class="page-title">
						<h1>
							<?php echo 'Configurations'; ?>
						</h1>
					</div>
					<div class="panel-body">
							<div class="tab">
								<button class="tablinks active" onclick="ca_openTab(event, 'aw_ca_general-setting-tab',this)">General Settings</button>
								<button class="tablinks" id="company_form-setting-tab" onclick="ca_openTab(event, 'aw_ca_company_form-setting-tab',this)">New Company form</button>
								<button class="tablinks" onclick="ca_openTab(event, 'aw_ca_email-setting-tab',this)"><?php echo 'Emails'; ?></button>
							</div>
							<!-- Tab Start -->
							<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" id="aw_ca_company_form" enctype="multipart/form-data">
								<input type="hidden" name="action" value="company_accounts_save_setting_form">
								<?php wp_nonce_field( 'save_company_accounts_setting', 'awcompanyaccounts_admin_nonce' ); ?>
								<div class="tabcontent aw-ca-general-set" id="aw_ca_general-setting-tab" style="display:block;">
										<ul>
											<li>
												<label>Default Sales Representative</label>
												<div class="control">
												 <select name="aw_ca_default_salesrep">
													<?php 
													if (!empty($salesrepr_obj)) {
														foreach ($salesrepr_obj as $key => $sales_representative) {
															?>
															  <option value="<?php echo wp_kses($sales_representative->data->ID, wp_kses_allowed_html('post')); ?>"><?php echo wp_kses($sales_representative->data->display_name, wp_kses_allowed_html('post')); ?></option>
															<?php 
														} 
													} else { 
														?>
														<option value=""> No Sales Reprsentative</option>
													<?php } ?>
												 </select>
												</div>
											</li>
											<li>
												<label>Enable Order Approval by Company Admin</label>
												<div class="control">
												 <select name="aw_ca_order_approvalenable">
													 <option value="yes" <?php echo wp_kses($aw_ca_enable_yes, wp_kses_allowed_html('post')); ?>>Yes</option>
													 <option value="no" <?php echo wp_kses($aw_ca_enable_no, wp_kses_allowed_html('post')); ?>>No</option>
												 </select>
												</div>
											</li>
											 
										</ul>
										<div class="submit">
											<input type="submit" class="button button-primary" value="Save" name="ca_setting_general_submit"/>	
										</div>
								 </div>	
							</form>
							<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" id="aw_ca_company_form" enctype="multipart/form-data">
								<input type="hidden" name="action" value="company_accounts_save_setting_form">
								<?php wp_nonce_field( 'save_company_accounts_setting', 'awcompanyaccounts_admin_nonce' ); ?>
								<div class="tabcontent aw-ca-general-set" id="aw_ca_company_form-setting-tab" style="display:none;">
									<?php
									if (!empty($company_form_setting)) {
										foreach ($company_form_setting as $formkey=> $currentform) {
											?>
									 
									 <table width="100%" border="0" cellspacing="0" cellpadding="0" class="aw-form-list">
										  <tbody>
											  <tr>
												<td class="label">
													<lable><?php echo wp_kses(str_replace('_', ' ', $formkey), wp_kses_allowed_html('post')); ?></lable>
												</td>
												<td class="value">
													<div class="aw-ca-table-wrapper">
														<div class="wrap">
															<table id="companyinoform"  class="wp-list-table widefat striped" >
																<tbody id="companyino-list" class="ui-sortable main-list">
																	<?php 
																		$tbody = '';
																	foreach ($currentform as $key=>$value) {
																		$required = '';
																		$mandatory= '';
																		$display_checkbox = '<td>&nbsp;</td><td>&nbsp;</td>';
																		$checked_req 		= '';
																		if ($value['required']) {
																			$required = 'required';
																			$mandatory= '<span class="aw-ca-star">*</span>';
																			$checked_req = 'checked';
																		}
																		if ($value['chkboxvisible']) {
																			$enable_check 	= '';
																			$required_check = '';
			
																			if ($value['enable']) {
																				$enable_check = 'checked ="checked"';
																			}
																			if ($value['required']) {
																				$required_check = 'checked ="checked"';
																			}
																			$display_checkbox = '<td><input type="hidden" name="company_form[' . $formkey . '][' . $key . '][enable]" value="0"/><input type="checkbox" name="company_form[' . $formkey . '][' . $key . '][enable]" value="1" ' . $enable_check . '/>Enable</td>';  
																			$display_checkbox .= '<input type="hidden" name="company_form[' . $formkey . '][' . $key . '][required]" value="0"/><td><input type="checkbox" name="company_form[' . $formkey . '][' . $key . '][required]" value="1" ' . $required_check . '/>Required</td>';  
																			$display_checkbox .='<input type="hidden" name="company_form[' . $formkey . '][' . $key . '][chkboxvisible]"  value="' . $value['chkboxvisible'] . '" ' . $required_check . '/>';
			
																		} else {
																			$required = 'required';
																			$mandatory= '<span class="aw-ca-star">*</span>';
																			$checked_req = 'checked';
																			$display_checkbox .='<input type="hidden" name="company_form[' . $formkey . '][' . $key . '][enable]" value="1"/><input type="hidden" name="company_form[' . $formkey . '][' . $key . '][required]" value="1"/><input type="hidden" name="company_form[' . $formkey . '][' . $key . '][chkboxvisible]" value="0"/>';
																		}
																		$tbody .= '<tr class="tr-' . $key . '"><td><span class="colon-move">::</span></td><td class="aw-ca-cname">' . $mandatory . '<input type="hidden" name="company_form[' . $formkey . '][' . $key . '][label]" value="' . $value['label'] . '"/><input type="text" name="company_form[' . $formkey . '][' . $key . '][name]" value="' . $value['name'] . '" placeholder="' . $value['label'] . '" ' . $required . '/>
                                                                                    </td>' . $display_checkbox . '</tr>' ;
																	}
																		echo wp_kses($tbody, wp_kses_allowed_html('post'));
																	?>
																</tbody>
															</table>		
														</div>
													</div>
												</td>
											 </tr>
										  </tbody>
									</table>
										
											
										<?php 
										}	 
									}
									?>
									
									<div class="submit">
										<input type="submit" class="button button-primary" value="Save" name="ca_form_setting_submit"/>	
									</div>
								</div>
							</form>
							
							<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" id="aw_ca_company_form" enctype="multipart/form-data">
								<input type="hidden" name="action" value="company_accounts_save_setting_form">
								<div class="tabcontent ar-email-set" id="aw_ca_email-setting-tab" style="display:none;">
								<table class="form-table">
									<tbody>
										<tr valign="top">
										<td class="wc_emails_wrapper" colspan="2">
											<table class="wc_emails widefat" cellspacing="0">
												<thead>
													<tr>
														<th class="aw-ca-wc-email-settings-table-status">Status</th><th class="wc-email-settings-table-name">Email</th><th class="wc-email-settings-table-email_type">Content</th><th class="wc-email-settings-table-recipient">Recipients</th><th class="wc-email-settings-table-actions">Manage</th>	
													</tr>
												</thead>
												<tbody>
													<?php 
													if (!empty($email_template)) { 
														foreach ($email_template as $template) { 
															?>
														<tr>
															<td class="aw-ca-wc-email-settings-table-status" data-colname="Status">
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
																<a href="<?php echo esc_url(admin_url('admin.php?page=company-accounts-emails&category=configuremail&ID=' . $template->id)) ; ?>"><?php echo wp_kses($template->email, wp_kses_allowed_html('post')); ?></a>
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
																<a class="button" href="<?php echo esc_url(admin_url('admin.php?page=company-accounts-emails&category=configuremail&ID=' . $template->id)) ; ?>">
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
	public static function aw_salesrepresentativeconfiguration_html() {
		$aw_ca_enable 			= ''; 
		$aw_ca_title 			= ''; 
		$aw_ca_neworderstatus 	= '';
		$aw_ca_credit_limit 	= ''; 
		$aw_ca_min_ordertotal 	= '';
		$aw_ca_max_ordertotal 	= '';
		$aw_ca_enable_no 		= '';
		$aw_ca_enable_yes 		= '';
		$default_group_assigned = '';
		//$companyinfo_count 		= 0; 

		$salesrepr_obj 	= get_users( [ 'role__in' => [ 'sales_representative' ] ] ); 

		$email_template = aw_ca_get_email_template_setting_results('salesrepresemail');		
		
		$notice = get_option( 'company_accounts_flash_notices', array() );
		if ( ! empty( $notice ) ) {
				printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
					wp_kses($notice['type'], wp_kses_allowed_html('post')),
					wp_kses($notice['dismissible'], wp_kses_allowed_html('post')),
					wp_kses($notice['notice'], wp_kses_allowed_html('post'))
				);
			delete_option( 'company_accounts_flash_notices', array() );
		}
		$serialized_data = get_option('aw_ca_groupsales_representative');
		$default_group_assigned = maybe_unserialize($serialized_data);
		?>
		<div class="aw-ca-tab-grid-wrapper">
			<div class="spw-rw clearfix">
				<div class="panel-box company-accounts-setting">
					<div class="page-title">
						<h1>
							<?php echo 'Conditions'; ?>
						</h1>
					</div>
					<div class="panel-body">
							<div class="tab">
								<button class="tablinks active" onclick="ca_openTab(event, 'aw_ca_slaes_repsentative-setting-tab',this)"><?php echo 'Sales Representative'; ?></button>
								<button class="tablinks" onclick="ca_openTab(event, 'aw_ca_sales_representative_email-setting-tab',this)"><?php echo 'Emails'; ?></button>
							</div>
							<!-- Tab Start -->
							<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" id="aw_ca_sales_representative_form" enctype="multipart/form-data">
								<input type="hidden" name="action" value="company_accounts_save_setting_form">
								<?php wp_nonce_field( 'save_company_accounts_setting', 'awcompanyaccounts_admin_nonce' ); ?>
									<div class="tabcontent aw-ca-general-set" id="aw_ca_slaes_repsentative-setting-tab" style="display:block;">
									
										<table width="100%" border="0" cellspacing="0" cellpadding="0" class="aw-form-list">
										  <tr>
											<td class="label">
												<label>Assign Sales Representative to Customer Group</label>
											</td>
											<td class="value">
												<div class="aw-ca-table-wrapper">
													<table width="100%" border="0" cellspacing="0" cellpadding="0" id="sales_representative_table">
													<thead>
														<tr>
															<td><label>Customer Group</label></td>
															<td><label>Default Sales Representative</label></td>
															<td><label>Action</label></td>
														</tr>
													</thead>
													<tbody class="aw_ca_tbody">
														<?php 
														$tr_str ='';	
														if (!empty($default_group_assigned)) {
															$count = count($default_group_assigned);
															
															for ( $row=0; $row<$count; $row++) { 
																?>
																<tr><td> 
																<?php 	
																	$selected_logined = '';
																	$selected_notlogined = '';
																if ('notlogined' === $default_group_assigned[$row]['aw_ca_group_to_representative']) {
																	$selected_logined = 'selected=selected';
																} else {
																	$selected_notlogined = 'selected=selected';
																}
																?>
																	<select name="aw_ca_group_to_representative[]">
																		<option value="notlogined" <?php echo wp_kses($selected_logined, wp_kses_allowed_html('post')); ?> >Not Logged In</option>
																		<option value="logined" <?php echo wp_kses($selected_notlogined, wp_kses_allowed_html('post')); ?> >Logged In</option>
																	</select>
																	</td>
																	<td>
																		<select name="aw_ca_salesrepresentative[]">
																			<?php 
																			if (!empty($salesrepr_obj)) {
																				foreach ($salesrepr_obj as $key => $sales_representative) {
																					$selected_user = '';
																					$user_id = $default_group_assigned[$row]['aw_ca_salesrepresentative'];
																					if ( $sales_representative->data->ID === $user_id) {
																							$selected_user = 'selected=selected';
																					} 
		
																					?>
																					<option value="<?php echo wp_kses($sales_representative->data->ID, wp_kses_allowed_html('post')); ?>" <?php echo wp_kses($selected_user, wp_kses_allowed_html('post')); ?>><?php echo wp_kses($sales_representative->data->display_name, wp_kses_allowed_html('post')); ?></option>
																				 <?php 
																				}	
																			}
																			?>
																		</select>
																	</td>
																	<td>
																		<button onclick="return aw_ca_delete_sales_man(this)" class="button-delete"><span>Delete</span></button>
																	</td>
																</tr>		
																<?php 
															}
														} 
														?>
															
													</tbody>
													<tfoot>
														<tr>
															<td colspan="3">
																<button class="aw_ca_rowclone" onclick="
															return aw_ca_add_clone_row(event)" >Add</button>
															</td>
														</tr>
													</tfoot>
												</table>
												</div>
											</td>
										  </tr>
										</table>

										
										
										
										<div class="submit">
											<input type="submit" class="button button-primary" value="Save" name="ca_sales_repesentative_submit"/>	
										</div>
								 </div>	
							</form>
							 <table class="hidden_div">
								 <tr class="aw_ca_row">
									<td>
										<select name="aw_ca_group_to_representative[]">
											 <option value="notlogined">Not Logged In</option>
											 <option value="logined">Logged In</option>
										 </select>
									</td>
									<td>
										<select name="aw_ca_salesrepresentative[]">
											<?php 
											if (!empty($salesrepr_obj)) {
												foreach ($salesrepr_obj as $key => $sales_representative) {
													$customer 		= new WC_Customer( $sales_representative->data->ID);
													$display_name 	= $customer->get_display_name(); 
													?>
													  <option value="<?php echo wp_kses($sales_representative->data->ID, wp_kses_allowed_html('post')); ?>"> <?php echo wp_kses($display_name, wp_kses_allowed_html('post')); ?> 
													  </option>
												 <?php 
												}	
											} else { 
												?>
												<option value="">No Sales Representative</option>
											<?php 
											}
											?>
										 </select>
									</td>
									<td>
										<button onclick="return aw_ca_delete_sales_man(this)" class="button-delete"><span>Delete</span></button>
									</td>
								</tr>	
							 </table>
							<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" id="aw_ca_company_form" enctype="multipart/form-data">
								<input type="hidden" name="action" value="company_accounts_save_setting_form">

								<div class="tabcontent ar-email-set" id="aw_ca_sales_representative_email-setting-tab" style="display:none;">
								<table class="form-table">
									<tbody>
										<tr valign="top">
										<td class="wc_emails_wrapper" colspan="2">
											<table class="wc_emails widefat" cellspacing="0">
												<thead>
													<tr>
														<th class="aw-ca-wc-email-settings-table-status">Status</th><th class="wc-email-settings-table-name">Email</th><th class="wc-email-settings-table-email_type">Content</th><th class="wc-email-settings-table-recipient">Recipients</th><th class="wc-email-settings-table-actions">Manage</th>	
													</tr>
												</thead>
												<tbody>
													<?php 
													if (!empty($email_template)) { 
														foreach ($email_template as $template) { 
															?>
														<tr>
															<td class="aw-ca-wc-email-settings-table-status" data-colname="Status">
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
																<a href="<?php echo esc_url(admin_url('admin.php?page=company-accounts-emails&category=salesrepresemail&ID=' . $template->id)) ; ?>"><?php echo wp_kses($template->email, wp_kses_allowed_html('post')); ?></a>
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
																<a class="button" href="<?php echo esc_url(admin_url('admin.php?page=company-accounts-emails&category=salesrepresemail&ID=' . $template->id)) ; ?>">
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

	public static function aw_company_accounts_save_setting_form() {
		$post_formsetting = array();
		$url =  admin_url() . 'admin.php?page=company-accounts-configuration';
		if (isset($_POST['awcompanyaccounts_admin_nonce'])) {
			$awcompanycredit_admin_nonce = sanitize_text_field($_POST['awcompanyaccounts_admin_nonce']);
		}

		if ( !wp_verify_nonce( $awcompanycredit_admin_nonce, 'save_company_accounts_setting' )) {
			wp_die('Our Site is protected');
		}

		/* Submit general setting of configuration setting */ 
		if (isset($_POST['ca_setting_general_submit'])) {
			$sales_representative 	= '';
			$approvalenable 		= '';
			if (isset($_POST['aw_ca_default_salesrep'])) {
				$sales_representative = sanitize_text_field($_POST['aw_ca_default_salesrep']);
				update_option('aw_ca_default_salesrep', $sales_representative);
			} 
			if (isset($_POST['aw_ca_order_approvalenable'])) {
				$approvalenable = sanitize_text_field($_POST['aw_ca_order_approvalenable']);
				update_option('aw_ca_order_approvalenable', $approvalenable);
			} 
			self::aw_company_accounts_add_flash_notice( __('General settings updated'), 'success', true );
			wp_redirect($url);
		}

		/* Submit company form customization setting */
		if (isset($_POST['ca_form_setting_submit'])) {

			$url =  admin_url() . 'admin.php?page=company-accounts-configuration&tab=company_form-setting-tab';
			if (isset($_POST['awcompanyaccounts_admin_nonce'])) {
				$awcompanycredit_admin_nonce = sanitize_text_field($_POST['awcompanyaccounts_admin_nonce']);
			}
			if ( !wp_verify_nonce( $awcompanycredit_admin_nonce, 'save_company_accounts_setting' )) {
				wp_die('Our Site is protected');
			}

			if (isset($_POST['company_form'])) {
				$post 	= json_encode($_POST);
				$post 	= wp_unslash($post);
				$post 	= json_decode($post, true);
				$form1 	= array_values(array_filter($post['company_form']['Company_Information_Customization'])); 

				$form2 = array_values(array_filter($post['company_form']['Legal_Address_Customization']));
				$form3 = array_values(array_filter($post['company_form']['Company_Administrator_Customization']));
				if (!empty($form1) && !empty($form2) && !empty($form3)) {
					$post_formsetting['Company_Information_Customization'] = $form1;
					$post_formsetting['Legal_Address_Customization'] = $form2;
					$post_formsetting['Company_Administrator_Customization'] = $form3;
					$searlizeddata = maybe_serialize($post_formsetting);
					update_option('aw_ca_default_form', $searlizeddata);		
					self::aw_company_accounts_add_flash_notice( __('Company form settings updated'), 'success', true );
				}
				wp_redirect($url);
			}		
		}

		/* Submit configuration setting of email tab setting */ 
		if (isset($_POST['aw_ca_email_setting_submit'])) {
			$url =  admin_url() . 'admin.php?page=company-accounts-configuration&tab=aw-ca-emails';
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
			$db_table = $wpdb->prefix . 'aw_ca_email_templates';
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
			self::aw_company_accounts_add_flash_notice( __('Email templates setting updated'), 'success', true );
			wp_redirect($url);
		}

		/* Submit Sales Representative setting */
		if (isset($_POST['ca_sales_repesentative_submit'])) {
			$result = array();
			$url 	= admin_url() . 'admin.php?page=company-accounts-sales-representative&tab=aw_ca_slaes_repsentative-setting-tab';
			if (isset($_POST['awcompanyaccounts_admin_nonce'])) {
				$awcompanycredit_admin_nonce = sanitize_text_field($_POST['awcompanyaccounts_admin_nonce']);
			}
			if ( !wp_verify_nonce( $awcompanycredit_admin_nonce, 'save_company_accounts_setting' )) {
				wp_die('Our Site is protected');
			}

			unset($_POST['action']);
			unset($_POST['awcompanyaccounts_admin_nonce']);
			unset($_POST['_wp_http_referer']);
			unset($_POST['ca_sales_repesentative_submit']);
			
			if (!empty($_POST['aw_ca_group_to_representative']) && !empty($_POST['aw_ca_salesrepresentative'])) {
				
				$post 	= json_encode($_POST);
				$post 	= wp_unslash($post);
				$post 	= json_decode($post, true);
				$group_to_representative = array_values(array_filter($post['aw_ca_group_to_representative']));
				$sales_representative = array_values(array_filter($post['aw_ca_salesrepresentative']));
				 
				for ($i=0;$i<count($group_to_representative);$i++) {
					$result[$i] = array('aw_ca_group_to_representative'=>$group_to_representative[$i],'aw_ca_salesrepresentative'=>$sales_representative[$i]);
				}
			}
			update_option('aw_ca_groupsales_representative', maybe_serialize($result));
			self::aw_company_accounts_add_flash_notice( __('Sales Representative setting updated successfuly.'), 'success', true );
			wp_redirect($url);
		}
	}

	public static function aw_ca_admin_email_setting() {
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
		$category 			= '';

		$default_settings[1] = array(
							/*'email'				=> 'Credit LImit Update',
							'subject'			=> 'New review',
							'email_heading'		=> 'New review',
							'additional_content'=> 'Hello,{admin}
Someone has posted a review {link to the review on the backend} for {product_name}.',*/
							'Formtitle'			=> 'New Company Submitted',
						);
		$default_settings[2] = array(
							/*'email'				=> 'Credit LImit Update',
							'subject'			=> 'New review',
							'email_heading'		=> 'New review',
							'additional_content'=> 'Hello,{admin}
Someone has posted a review {link to the review on the backend} for {product_name}.',*/
							'Formtitle'			=> 'New Company Approved',
						);
		$default_settings[3] = array(
							/*'email'				=> 'Credit LImit Update',
							'subject'			=> 'New review',
							'email_heading'		=> 'New review',
							'additional_content'=> 'Hello,{admin}
Someone has posted a review {link to the review on the backend} for {product_name}.',*/
							'Formtitle'			=> 'New Company Declined',
						);
		$default_settings[4] = array(
							/*'email'				=> 'Credit LImit Update',
							'subject'			=> 'New review',
							'email_heading'		=> 'New review',
							'additional_content'=> 'Hello,{admin}
Someone has posted a review {link to the review on the backend} for {product_name}.',*/
							'Formtitle'			=> 'New Company Domain Created',
						);
		$default_settings[5] = array(
						/*'email'				=> 'Credit LImit Update',
						'subject'			=> 'New review',
						'email_heading'		=> 'New review',
						'additional_content'=> 'Hello,{admin}
Someone has posted a review {link to the review on the backend} for {product_name}.',*/
						'Formtitle'			=> 'New Company User Assigned',
					);
		$default_settings[6] = array(
						/*'email'				=> 'Credit LImit Update',
						'subject'			=> 'New review',
						'email_heading'		=> 'New review',
						'additional_content'=> 'Hello,{admin}
Someone has posted a review {link to the review on the backend} for {product_name}.',*/
						'Formtitle'			=> 'Company Domain Approved',
					);
		$default_settings[7] = array(
						/*'email'				=> 'Credit LImit Update',
						'subject'			=> 'New review',
						'email_heading'		=> 'New review',
						'additional_content'=> 'Hello,{admin}
Someone has posted a review {link to the review on the backend} for {product_name}.',*/
						'Formtitle'			=> 'Domain Status Changed',
					);
		$default_settings[8] = array(
						/*'email'				=> 'Credit LImit Update',
						'subject'			=> 'New review',
						'email_heading'		=> 'New review',
						'additional_content'=> 'Hello,{admin}
Someone has posted a review {link to the review on the backend} for {product_name}.',*/
						'Formtitle'			=> 'Company Domain Deleted',
					);
		$default_settings[9] = array(
						/*'email'				=> 'Credit LImit Update',
						'subject'			=> 'New review',
						'email_heading'		=> 'New review',
						'additional_content'=> 'Hello,{admin}
Someone has posted a review {link to the review on the backend} for {product_name}.',*/
						'Formtitle'			=> 'Company admin change domain status',
					);
		$default_settings[10] = array(
						/*'email'				=> 'Credit LImit Update',
						'subject'			=> 'New review',
						'email_heading'		=> 'New review',
						'additional_content'=> 'Hello,{admin}
Someone has posted a review {link to the review on the backend} for {product_name}.',*/
						'Formtitle'			=> 'Company Domain Deleted',
					);		
		$default_settings[11] = array(
						/*'email'				=> 'Credit LImit Update',
						'subject'			=> 'New review',
						'email_heading'		=> 'New review',
						'additional_content'=> 'Hello,{admin}
Someone has posted a review {link to the review on the backend} for {product_name}.',*/
						'Formtitle'			=> 'Sales Representative Group',
					);									

		if (isset($_GET['ID']) && !empty($_GET['ID'])) {
			$id = sanitize_text_field($_GET['ID']);
			$data = aw_ca_get_email_template_setting_row($id);
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
		if (isset($_GET['category']) && !empty($_GET['category'])) {
			$category = sanitize_text_field($_GET['category']);
		}
		?>
		<h2>
			<?php echo wp_kses($email, wp_kses_allowed_html('post')); ?>
			<small class="wc-admin-breadcrumb"><a href="<?php echo wp_kses(admin_url(), wp_kses_allowed_html('post')); ?>admin.php?page=company-accounts-configuration&amp;tab=aw-ca-emails" aria-label="Return to emails">⤴</a></small>
		</h2>
		<p>
		<?php 
		echo wp_kses($default_settings[$id]['Formtitle'], wp_kses_allowed_html('post')); 
		?>
		</p>
		<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" onsubmit="return aw_ca_email_templvalidateForm()">
			<?php wp_nonce_field( 'save_company_accounts_email_setting', 'awcompanyaccounts_admin_nonce' ); ?>
			<input type="hidden" name="action" value="aw_ca_save_email_templates_setting">
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
								<input class="input-text regular-input aw_ca_mailsubject" type="text" name="subject" value="<?php echo wp_kses($subject, wp_kses_allowed_html('post')); ?>"placeholder="<?php echo wp_kses($subject, wp_kses_allowed_html('post')); ?>" maxlength="50">
								<p><span class="aw_ca_mailsubject_msg"></span></p>
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
								<input class="input-text regular-input required aw_ca_email_heading" type="text" name="email_heading" value="<?php echo wp_kses($email_heading, wp_kses_allowed_html('post')); ?>"placeholder="<?php echo wp_kses($email_heading, wp_kses_allowed_html('post')); ?>" maxlength="50">
								<p><span class="aw_ca_email_heading_msg"></span></p>
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
								<textarea rows="3" cols="20" class="input-text wide-input required aw_ca_additional_content" type="textarea" name="additional_content" placeholder="<?php echo wp_kses($additional_content, wp_kses_allowed_html('post')); ?>"><?php echo wp_kses($additional_content, wp_kses_allowed_html('post')); ?></textarea>
								<p><span class="aw_ca_additional_content_msg"></span></p>
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
							<input type="hidden" value="<?php echo wp_kses($category, wp_kses_allowed_html('post')); ?>" name="category">
							<button name="aw_ca_email_setting_submit" class="button-primary woocommerce-save-button" type="submit" value="Save changes">Save changes</button>
						</td>
					</tr>
				</tfoot>
			</table>
		</form>	
		<?php 
	}

	public static function aw_ca_save_email_templates_setting() {
		global $wpdb; 
		$url =  admin_url() . 'admin.php?page=company-accounts-configuration&tab=aw-ca-emails';
		if (isset($_POST['awcompanyaccounts_admin_nonce'])) {
			$awcompanyaccounts_admin_nonce = sanitize_text_field($_POST['awcompanyaccounts_admin_nonce']);
		}

		if ( !wp_verify_nonce( $awcompanyaccounts_admin_nonce, 'save_company_accounts_email_setting' )) {
			wp_die('Our Site is protected');
		}

		if (isset($_POST['aw_ca_email_setting_submit'])) {

			if (isset($_POST['category']) && !empty($_POST['category'])) {
				$category = sanitize_text_field($_POST['category']);
				if ('configuremail'===$category) {
					$url =  admin_url() . 'admin.php?page=company-accounts-configuration&caetgory=configuremail&tab=aw-ca-emails';
				} else {
					$url =  admin_url() . 'admin.php?page=company-accounts-sales-representative&caetgory=salesrepresemail&tab=aw-ca-sales-emails';
				}
			}

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
			$db_table = $wpdb->prefix . 'aw_ca_email_templates';
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
			self::aw_company_accounts_add_flash_notice( __('Email templates setting updated'), 'success', true );
			wp_redirect($url);
		}
	}

	public static function aw_companyaccount_companylist() {
		?>
		<div class="wrap">
			<div class="page-title">
				<h1 class="wp-heading-inline">Companies</h1>
				<a href="<?php echo esc_url(admin_url('admin.php?page=new-company-form')); ?>" class="page-title-action">Add New Company</a>
				<hr class="wp-header-end">
			</div>

			<div class="panel-body">
				<div class="tabcontent">
					<?php
						$tablename 		= 'aw_ca_company_information';
						$company_obj 	= new AwcaCompanyListAdmin();
						$search 		= '';
					if (isset($_GET['s'])) {
						$search = sanitize_text_field($_GET['s']);
						if (isset($_GET['d'])) {
							$link_search = '';
						}
						$count_all = $company_obj->get_count($tablename);
					}
					?>

					<form id="posts-filter" method="get">
						<p class="search-box">
							<input type="hidden" name="page" class="page" value="company-accounts-company-list">	
							<input type="search" id="post-search-input" name="s" value="<?php echo isset($_GET['d']) ? esc_html($link_search) : esc_html($search); ?>">
							<input type="submit" id="search-submit" class="button" value="Search Company" title="Search Company by Name ">
						</p>
					</form>
					<form id="ca-companylist-table" method="GET">
						<input type="hidden" name="page" class="page" value="company-accounts-company-list">
						<?php
							$company_obj->prepare_items($search);
							$company_obj->display();
						?>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	public static function aw_ca_company_form_admin() {
		$company_id 		= '';
		$company_name 		= '';
		$status 			= '';
		$company_email 		= '';
		$exist_representative_name = '';
		$exist_representative_id = '';
		$company_legal_name = '';
		$tax_vat_id 		= '';
		$reseller_id 		= '';
		$company_street 	= '';
		$city 				= '';
		$country 			= '';
		$state 				= '';
		$zip 				= '';
		$company_phone 		= '';
		$company_admin_email= '';
		$company_admin_id  	= '';
		$first_name 		= '';
		$last_name 			= '';
		$job_position 		= '';
		$phone_number 		= '';
		$customer_group 	= '';
		$website 			= '';
		$send_mail_from 	= '';
		$pending_status  	= '';
		$approved_status 	= '';
		$declined_status 	= ''; 
		$blocked_status  	= '';

		$countries = array('Afghanistan', 'Albania', 'Algeria', 'American Samoa', 'Andorra', 'Angola', 'Anguilla', 'Antarctica', 'Antigua and Barbuda', 'Argentina', 'Armenia', 'Aruba', 'Australia', 'Austria', 'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bermuda', 'Bhutan', 'Bolivia', 'Bosnia and Herzegowina', 'Botswana', 'Bouvet Island', 'Brazil', 'British Indian Ocean Territory', 'Brunei Darussalam', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cambodia', 'Cameroon', 'Canada', 'Cape Verde', 'Cayman Islands', 'Central African Republic', 'Chad', 'Chile', 'China', 'Christmas Island', 'Cocos (Keeling) Islands', 'Colombia', 'Comoros', 'Congo', 'Congo, the Democratic Republic of the', 'Cook Islands', 'Costa Rica', "Cote d'Ivoire", 'Croatia (Hrvatska)', 'Cuba', 'Cyprus', 'Czech Republic', 'Denmark', 'Djibouti', 'Dominica', 'Dominican Republic', 'East Timor', 'Ecuador', 'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Ethiopia', 'Falkland Islands (Malvinas)', 'Faroe Islands', 'Fiji', 'Finland', 'France', 'France Metropolitan', 'French Guiana', 'French Polynesia', 'French Southern Territories', 'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Gibraltar', 'Greece', 'Greenland', 'Grenada', 'Guadeloupe', 'Guam', 'Guatemala', 'Guinea', 'Guinea-Bissau', 'Guyana', 'Haiti', 'Heard and Mc Donald Islands', 'Holy See (Vatican City State)', 'Honduras', 'Hong Kong', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran (Islamic Republic of)', 'Iraq', 'Ireland', 'Israel', 'Italy', 'Jamaica', 'Japan', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', "Korea, Democratic People's Republic of", 'Korea, Republic of', 'Kuwait', 'Kyrgyzstan', "Lao, People's Democratic Republic", 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libyan Arab Jamahiriya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macau', 'Macedonia, The Former Yugoslav Republic of', 'Madagascar', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Martinique', 'Mauritania', 'Mauritius', 'Mayotte', 'Mexico', 'Micronesia, Federated States of', 'Moldova, Republic of', 'Monaco', 'Mongolia', 'Montserrat', 'Morocco', 'Mozambique', 'Myanmar', 'Namibia', 'Nauru', 'Nepal', 'Netherlands', 'Netherlands Antilles', 'New Caledonia', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'Niue', 'Norfolk Island', 'Northern Mariana Islands', 'Norway', 'Oman', 'Pakistan', 'Palau', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Pitcairn', 'Poland', 'Portugal', 'Puerto Rico', 'Qatar', 'Reunion', 'Romania', 'Russian Federation', 'Rwanda', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Vincent and the Grenadines', 'Samoa', 'San Marino', 'Sao Tome and Principe', 'Saudi Arabia', 'Senegal', 'Seychelles', 'Sierra Leone', 'Singapore', 'Slovakia (Slovak Republic)', 'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa', 'South Georgia and the South Sandwich Islands', 'Spain', 'Sri Lanka', 'St. Helena', 'St. Pierre and Miquelon', 'Sudan', 'Suriname', 'Svalbard and Jan Mayen Islands', 'Swaziland', 'Sweden', 'Switzerland', 'Syrian Arab Republic', 'Taiwan, Province of China', 'Tajikistan', 'Tanzania, United Republic of', 'Thailand', 'Togo', 'Tokelau', 'Tonga', 'Trinidad and Tobago', 'Tunisia', 'Turkey', 'Turkmenistan', 'Turks and Caicos Islands', 'Tuvalu', 'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States', 'United States Minor Outlying Islands', 'Uruguay', 'Uzbekistan', 'Vanuatu', 'Venezuela', 'Vietnam', 'Virgin Islands (British)', 'Virgin Islands (U.S.)', 'Wallis and Futuna Islands', 'Western Sahara', 'Yemen', 'Yugoslavia', 'Zambia', 'Zimbabwe');

			$notice = get_option( 'company_accounts_flash_notices', array() );
		if ( ! empty( $notice ) ) {
				printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
					wp_kses($notice['type'], wp_kses_allowed_html('post')),
					wp_kses($notice['dismissible'], wp_kses_allowed_html('post')),
					wp_kses($notice['notice'], wp_kses_allowed_html('post'))
				);
			delete_option( 'company_accounts_flash_notices', array() );
		}
			$serialized_data 		= get_option('aw_ca_groupsales_representative');
			$salesrepresentative 	= maybe_unserialize($serialized_data);

			$user_id 	= get_current_user_id();
		if (isset($_GET['id'])) {
			$company_id = sanitize_text_field($_GET['id']);
		} 
		
		$user_meta = get_userdata($user_id);
		$user_roles = $user_meta->roles;
		if(!empty($user_roles) && 'company_admin' === $user_roles[0]) {
			$company_id = get_user_meta($user_id, 'company_id', true);
			error_log('-->>'.$company_id);
		} else {
			echo '<h1>New Company</h1>';
		}	

		if ('' !=$company_id ) {
			$company_detail = aw_ca_get_company_details_row( $company_id );
			if (!empty($company_detail)) {
				$company_name 	= $company_detail->company_name;
				$status 	 	= $company_detail->status;
				switch ($status) {
					case 'pending_approval':
						$pending_status 	= 'selected="selected"';
						break;
					case 'approved':
						$approved_status 	= 'selected="selected"';
						break;
					case 'declined':
						$declined_status 	= 'selected="selected"';
						break;	
					case 'blocked':
						$blocked_status 	= 'selected="selected"';
						break;		
				}
				$company_email 	= $company_detail->company_email;
				$representative_data = aw_ac_get_user_details($company_detail->sales_representative);
				if (!empty($representative_data)) {
					if (isset($representative_data['user_email'])) {
						$exist_representative_name 	= $representative_data['user_email'];	
					}
					if (isset($representative_data['user_id'])) {
						$exist_representative_id 	= $representative_data['user_id'];	
					}
				}
						
				$company_legal_name 		= $company_detail->company_legal_name;
				$tax_vat_id 				= $company_detail->tax_vat_id;
				$reseller_id 				= $company_detail->reseller_id;
				$company_street 			= $company_detail->company_street;
				$city 						= $company_detail->city;
				$country 					= $company_detail->country;
				$state 						= $company_detail->state;
				$zip 						= $company_detail->zip;
				$company_phone 				= $company_detail->company_phone;

				$admin_data 				= aw_ac_get_user_details($company_detail->company_admin_id);
				if (!empty($admin_data)) {
					$company_admin_email 	= $admin_data['user_email'];//$com_admin_detail->user_email;
					$company_admin_id 		= $admin_data['user_id'];
					$first_name = $admin_data['first_name']; 
					$last_name  = $admin_data['last_name']; 
					$job_position = $admin_data['job_position']; 
					$phone_number = $admin_data['phone_number']; 
					$customer_group = $admin_data['customer_group'];
				}
			}
		}
		?>
			
			<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data" onsubmit="return formvalidate(event)">
			<?php wp_nonce_field( 'save_company_form_data', 'awcompanyaccounts_admin_nonce' ); ?>
				<input type="hidden" name="action" value="company_data_save_form">
				
				<div class="aw-nca-section table-responsive">
				<h2>General</h2><hr>
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="woocommerce_new_order_subject">Company Name *<span class="woocommerce-help-tip"></span></label>
							</th>
							<td class="formcompany">
								<fieldset>
									<input class="input-text regular-input txt_required" id="company_name" type="text" name="formdata[company_name]" value="<?php echo  wp_kses($company_name, wp_kses_allowed_html('post')); ?>"  maxlength="50">
									<p><span class="error_company_name" id=""></span></p>
								</fieldset>
							</td>
						</tr>
							<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="woocommerce_new_order_email_type">Status<span class="woocommerce-help-tip"></span></label>
							</th>
							<td class="forminp">
								<fieldset>
									<legend class="screen-reader-text"><span>Status</span></legend>
									<select class="select" name="formdata[status]" style="" aria-hidden="true">
										<option <?php echo wp_kses($pending_status, wp_kses_allowed_html('post')) ; ?> value="pending">Pending Approval</option>
										<option <?php echo wp_kses($approved_status, wp_kses_allowed_html('post')); ?> value="approved">Approved</option>
										<option <?php echo wp_kses($blocked_status, wp_kses_allowed_html('post')); ?> value="blocked">Blocked</option>
										<option <?php echo wp_kses($declined_status, wp_kses_allowed_html('post')); ?> value="declined">Declined</option>
									</select>
								</fieldset>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label >Company Email</label>
							</th>
							<td class="formcompany">
								<fieldset>
									<input class="input-text regular-input validemail" type="text" id="company_email" name="formdata[company_email]" value="<?php echo wp_kses($company_email, wp_kses_allowed_html('post')); ?>" maxlength="50">
									 <p><span class="error_company_email" id=""></span></p>
								</fieldset>
							</td>	
						</tr>
						<tr valign="top">
							<th scope="row" class="formcompany">
								<label for="woocommerce_new_order_enabled">Sales Representative</label>
							</th>
							<td class="formcompany">
								<fieldset>
										<select class="select" name="formdata[sales_representative]">
											<option value="">Not Assigned</option>
											<?php 
											if (!empty($salesrepresentative)) {
												foreach ($salesrepresentative as $key => $sales_representative) {
													$selected_sales_rep = '';
													$sales_person_id = $sales_representative['aw_ca_salesrepresentative'];
													$customer = new WC_Customer( $sales_representative['aw_ca_salesrepresentative']);
													$display_name 	= $customer->get_display_name(); 
													if ($exist_representative_id === $sales_person_id) {
														$selected_sales_rep = 'selected="selected"';
													}
													?>
													  <option <?php echo wp_kses($selected_sales_rep, wp_kses_allowed_html('post')); ?> value="<?php echo wp_kses($sales_representative['aw_ca_salesrepresentative'], wp_kses_allowed_html('post')); ?>"> <?php echo wp_kses($display_name, wp_kses_allowed_html('post')); ?> </option>
												 <?php 
												}	
											}
											?>
										</select>
								</fieldset>
							</td>
						</tr>
					</tbody>
					</table>
					</div>
					
					<div class="aw-nca-section table-responsive">
					<h2>Account Information</h2><hr>
					<table class="form-table">
						<tbody>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for=""> Company Legal Name</label>
							</th>
							<td class="formcompany">
								<fieldset>
									<input class="input-text regular-input" type="text" name="formdata[company_legal_name]" value="<?php echo wp_kses($company_legal_name, wp_kses_allowed_html('post')); ?>" />
								</fieldset>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="">Tax ID / VAT ID</label>
							</th>
							<td class="formcompany">
								<fieldset>
									<input class="input-text regular-input" type="text" name="formdata[tax_vat_id]" value="<?php echo wp_kses($tax_vat_id, wp_kses_allowed_html('post')); ?>" />
								</fieldset>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="">Reseller ID</label>
							</th>
							<td class="formcompany">
								<fieldset>
									<input class="input-text regular-input" type="text" name="formdata[reseller_id]" value="<?php echo wp_kses($reseller_id, wp_kses_allowed_html('post')); ?>" />
								</fieldset>
							</td>
						</tr>
						</tbody>
					   </table>
					 </div>
					 
					 <div class="aw-nca-section table-responsive">
					 <h2>Legal Address</h2><hr>
					 <table class="form-table">
						<tbody>  
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="">Street Address *</label>
							</th>
							<td class="formcompany">
								<fieldset>
									<input class="input-text regular-input txt_required" id="company_street" type="text" name="formdata[company_street]" value="<?php echo wp_kses($company_street, wp_kses_allowed_html('post')); ?>" />
								</fieldset>
								<p><span class="aw_ca_company_street_msg error_company_street" id=""></span></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="">City *</label>
							</th>
							<td class="formcompany">
								<fieldset>
									<input class="input-text regular-input txt_required" id="city" type="text" name="formdata[city]" value="<?php echo wp_kses($city, wp_kses_allowed_html('post')); ?>" />
								</fieldset>
								<p><span class="aw_ca_city_msg error_city" id=""></span></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="">Country *</label>
								 
							</th>
							<td class="formcompany">
								<fieldset>
									<select class="select" name="formdata[country]" style="" aria-hidden="true">
										<?php 
										for ($i=0 ; $i<count($countries); $i++) {
											$selected_country = '';
											if ($countries[$i] === $country) {
												$selected_country = 'selected="selected"';
											}
											?>
											<option <?php echo wp_kses($selected_country, wp_kses_allowed_html('post')); ?> value="<?php echo wp_kses($countries[$i], wp_kses_allowed_html('post')); ?>"><?php echo wp_kses($countries[$i], wp_kses_allowed_html('post')); ?></option>
										<?php } ?>	
									</select>
								</fieldset>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for=""> State/Province</label>
							</th>
							<td class="formcompany">
								<fieldset>
									<input class="input-text regular-input" type="text" name="formdata[state]" value="<?php echo wp_kses($state, wp_kses_allowed_html('post')); ?>" />
								</fieldset>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for=""> ZIP/Postal Code *</label>
							</th>
							<td class="formcompany">
								<fieldset>
									<input class="input-text regular-input" id="zip" type="text" name="formdata[zip]" value="<?php echo wp_kses($zip, wp_kses_allowed_html('post')); ?>" />
								</fieldset>
								<p><span class="aw_ca_zip_msg error_zip" id=""></span></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for=""> Company Phone Number </label>
							</th>
							<td class="formcompany">
								<fieldset>
									<input class="input-text regular-input" type="text" name="formdata[company_phone]" value="<?php echo wp_kses($company_phone, wp_kses_allowed_html('post')); ?>" onkeypress="return awcacheckIt(event)"/>
								</fieldset>
							</td>
						</tr>
					</tbody>
				  </table>
					</div>
					
					<div class="aw-nca-section table-responsive">
					<h2>Company Admin Information</h2><hr>
					<table class="form-table">
						<tbody> 
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="">  Company Admin Email *</label>
							</th>
							<td class="formcompany">
								<fieldset>
									<input class="input-text regular-input txt_required validemail" id="company_admin_email" type="text" name="formdata[user_email]" value="<?php echo wp_kses($company_admin_email, wp_kses_allowed_html('post')); ?>" />
								</fieldset>
								<p><span class="error_company_admin_email"></span></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for=""> First Name *</label>
							</th>
							<td class="formcompany">
								<fieldset>
									<input class="input-text regular-input txt_required" id="first_name" type="text" name="formdata[first_name]" value="<?php echo wp_kses($first_name, wp_kses_allowed_html('post')); ?>" />
								</fieldset>
								<p><span class="aw_ca_admin_first_name_msg error_first_name" id=""></span></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for=""> Last Name  *</label>
							</th>
							<td class="formcompany">
								<fieldset>
									<input class="input-text regular-input txt_required" id="last_name" type="text" name="formdata[last_name]" value="<?php echo wp_kses($last_name, wp_kses_allowed_html('post')); ?>" />
								</fieldset>
								<p><span class="aw_ca_admin_last_name_msg error_last_name" id=""></span></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for=""> Job Position </label>
							</th>
							<td class="formcompany">
								<fieldset>
									<input class="input-text regular-input" type="text" name="formdata[job_position]" value="<?php echo wp_kses($job_position, wp_kses_allowed_html('post')); ?>" />
								</fieldset>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for=""> Phone Number </label>
							</th>
							<td class="formcompany">
								<fieldset>
									<input class="input-text regular-input" type="text" name="formdata[phone_number]" value="<?php echo wp_kses($phone_number, wp_kses_allowed_html('post')); ?>" onkeypress="return awcacheckIt(event)" />
								</fieldset>
								
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for=""> Customer Group For Company Users</label>
							</th>
							<td class="formcompany">
								<fieldset>
										<?php 
											$logined_status = '';
											$notlogined_status = '';
										if ('logined' === $customer_group) {
											$logined_status = 'selected="selected"';
										}
										if ('notlogined' === $customer_group) {
											$notlogined_status = 'selected="selected"';
										}
										?>
										<select class="select" name="formdata[customer_group]">
											<option value="notlogined" <?php echo wp_kses($notlogined_status, wp_kses_allowed_html('post')); ?> >Not Logged In</option>
											<option value="logined" <?php echo wp_kses($logined_status, wp_kses_allowed_html('post')); ?> >Logged In</option>
										</select>
								</fieldset>
								<p><span class="aw_ca_zip_msg"></span></p>
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr valign="top">
							<td>
								<?php
								if ('' != $company_id) { 
									?>
									<input type="hidden" name="company_id" value="<?php echo wp_kses($company_id, wp_kses_allowed_html('post')); ?>" />
									<input type="hidden" name="company_admin_id" value="<?php echo wp_kses($company_admin_id, wp_kses_allowed_html('post')); ?>" />
									<button name="aw_ca_update_company_by_admin" class="button-primary woocommerce-save-button" type="submit" value="Update">Update</button>
								<?php } else { ?>
									<button name="aw_ca_new_company_by_admin" class="button-primary woocommerce-save-button" type="submit" value="Save">Save</button>
								<?php } ?>
							</td>
						</tr>
					</tfoot>
				</table>
				</div>
			</form>

			<div id="update_domain_Modal" class="domain_modal">
				<!-- Modal content -->
				<div class="domain_modal-content">
				<!-- <span class="bal_modal_close">&times;</span> -->
				<!--<span>Update Balance</span>-->
					<div class="aw-domain-header"><h2>Configure Domain</h2></div>
					<div class="aw-domain-inner">
						<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
							<?php wp_nonce_field( 'save_company_form_data', 'awcompanyaccounts_admin_nonce' ); ?>
							<input type="hidden" name="action" value="company_data_save_form">
							<input type="hidden" name="company_id" value="<?php echo wp_kses($company_id, wp_kses_allowed_html('post')); ?>" />
							<input type="hidden" name="domain_id" id="domain_id" value="" />
							<ul>
								<li>
									<label><strong> Domain Name * </strong></label>
									<div class="control">
										<input type="text" class="input-text" id="aw_ca_domain_name" name="domain_name" value="">
									</div>
									<span id="updatepoint_txt"></span>
								</li>
								<?php
								if (isset($user_roles[0]) && 'administrator' === $user_roles[0]) {
									?>
								<li>
									<label><strong>Status</strong></label>
									<div class="control">
									 <select name="domain_status" class="domain_status" id="domain_status">
										  <option value="active">Active</option>
										  <option value="inactive">Inactive</option>
										  <option value="pending">Pending</option>
									 </select>
									</div>
								</li>
								<?php 
								} 
								?>
							</ul>
							<!-- <p>Some text in the Modal..</p> -->
							<div class="domain_modal_action_btns">
								<input type="submit" name="aw_ca_savedomain" class="domain_modal_save button" value="Save" id="domain_save_button" onclick="check_exist_domain(event)">
								<input type="button" name="" class="domain_modal_close inactive-btn " value="Cancel" id="close_button">
							</div>
						</form>
					</div>	
				</div>
			</div>

			<?php 
			if (''==$company_id) {
				return;
			}
			$domain_list =	aw_ca_get_company_domains_list( $company_id );
			?>
			
			<div class="aw-nca-domain-panel">
				<h2>Company Domains</h2><hr>
				<div class="action-rw">
					<button class="button-primary woocommerce-save-button add_company_domain">Add New Domain</button>
				</div>
				<table class="form-table">
					<thead>
						<tr>
							<th>Domain Name</th>
							<th>Status</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
						if (!empty($domain_list)) {
							foreach ($domain_list as $key => $domain) {
								$id = $domain->id;
								$domain_name = $domain->domain_name;
								$url = get_site_url();//.'/wp-admin/admin.php?page=new-company-form';

								//$domain_name = '<a href="'.$url.'" class="add_company_domain">'. wp_kses($domain->domain_name, wp_kses_allowed_html("post")).'</a>';
								?>
							<tr>
								<?php
								if (isset($user_roles[0]) && 'administrator' === $user_roles[0]) { 
									?>
									<td data-colname="Domain Name"><a href='javascript:void(0)' onclick="aw_ca_get_domain_detail(<?php echo wp_kses($id, wp_kses_allowed_html('post')); ?>)"><?php echo wp_kses($domain_name, wp_kses_allowed_html('post')); ?></a></td> 
									<?php 
								} else { 
									?>
									<td data-colname="Domain Name"><?php echo wp_kses($domain_name, wp_kses_allowed_html('post')); ?>
									</td>
								<?php } ?>
								
								<td data-colname="Status"><?php echo wp_kses(ucfirst($domain->status), wp_kses_allowed_html('post')); ?></td>
								<td data-colname="Action"><button onclick="return aw_ca_deletedomain(<?php echo wp_kses($id, wp_kses_allowed_html('post')); ?>)" >Delete</button></td>
							</tr>
							<?php 
							}
						} else {
							?>
							<tr><td colspan="3" align="center">No Domain found</td></tr>
							<?php 
						}
						?>
					</tbody>
				</table>
			</div>
		<?php 
	}

	public static function aw_ca_company_data_save_form() {
		global $wpdb;
		$url =  admin_url() . 'admin.php?page=new-company-form';
		$db_company_table = $wpdb->prefix . 'aw_ca_company_information'; 
		if (isset($_POST['awcompanyaccounts_admin_nonce'])) {
			$awcompanyaccounts_admin_nonce = sanitize_text_field($_POST['awcompanyaccounts_admin_nonce']);
		}

		if ( !wp_verify_nonce( $awcompanyaccounts_admin_nonce, 'save_company_form_data' )) {
			wp_die('Our Site is protected');
		}
		$post_array = array();
		$company_form_data 	= array('company_name','status','company_email','company_legal_name','tax_vat_id','reseller_id','sales_representative','company_street','city','country','state','zip','company_phone');
		//$company_admin_data = array('user_email','customer_group','first_name','last_name','job_position','phone_number','website','send_mail_from');
		if (!empty($_POST) && ( isset($_POST['aw_ca_new_company_by_admin']) || isset( $_POST['aw_ca_update_company_by_admin']) )) {
			foreach ($company_form_data as $value) {
				if (isset($_POST['formdata'][$value])) {
					$post_array[$value] = sanitize_text_field($_POST['formdata'][$value]);	
				}
			}
			if (isset($_POST['formdata']['first_name'])) {
				$first_name = sanitize_text_field($_POST['formdata']['first_name']);
			}
			if (isset($_POST['formdata']['last_name'])) {
				$last_name 	= sanitize_text_field($_POST['formdata']['last_name']);
			}
			if (isset($_POST['formdata']['user_email'])) {
				$email 		= sanitize_text_field($_POST['formdata']['user_email']);
			}
			if (isset($_POST['formdata']['job_position'])) {
				$admin_job_position = sanitize_text_field($_POST['formdata']['job_position']);
			}
			if (isset($_POST['formdata']['phone_number'])) {
				$phone_number 		= sanitize_text_field($_POST['formdata']['phone_number']);
			}
			if (isset($_POST['formdata']['website'])) {
				$website 			= sanitize_text_field($_POST['formdata']['website']);
			}
			if (isset($_POST['formdata']['customer_group'])) {
				$customer_group 	= sanitize_text_field($_POST['formdata']['customer_group']); 
			}
		}
		if (isset($_POST['aw_ca_new_company_by_admin'])) {
			if ( username_exists( $email ) || email_exists( $email ) ) {
				self::aw_company_accounts_add_flash_notice( __('Company admin already register with this email'), 'error', true );

			} else {
				$user_id 	= aw_ca_register_user( $email, $first_name, $last_name, 'company_admin', 'backend' ); 
				update_user_meta($user_id, 'job_position', $admin_job_position);
				update_user_meta($user_id, 'phone_number', $phone_number);
				update_user_meta($user_id, 'customer_group', $customer_group);

				$post_array['company_admin_id'] = $user_id;
				$wpdb->insert( $db_company_table , $post_array );
				$company_id = $wpdb->insert_id;
				update_user_meta($user_id, 'company_id', $company_id);
				$company 	= aw_ca_get_company_by_id($company_id);
				update_user_meta($user_id, 'company_name', $company->company_name);
				if ($company_id) {
					self::aw_company_accounts_add_flash_notice( __('New company saved successfully'), 'success', true );
				} 
			}
			wp_redirect($url);
		}

		if (isset($_POST['aw_ca_update_company_by_admin'])) {
			$company_admin_data = array('user_email','first_name','last_name');
			$update_array 		= array();

			if (isset($_POST['company_admin_id'])) {
				$user_id 			= (int) sanitize_text_field($_POST['company_admin_id']); 
			}
			if (isset($_POST['company_id'])) {
				if (isset($_POST['company_id'])) {
					$company_id 	= sanitize_text_field($_POST['company_id']); 	
				}
			}
			$user_meta  	= get_userdata($user_id);
			$exist_email 	= $user_meta->user_email;
			if ( $exist_email != $email && ( username_exists( $email ) || email_exists( $email ) ) ) {
				self::aw_company_accounts_add_flash_notice( __('Company admin already register with this email'), 'error', true );

			} else {
				$wpdb->update( $db_company_table , $post_array, array('id'=>$company_id));
				$user_array['ID'] 	= $user_id;
				foreach ($company_admin_data as $value) {
					if (isset($_POST['formdata'][$value])) {
						$user_array[$value] = sanitize_text_field($_POST['formdata'][$value]);
					}
				}
				wp_update_user($user_array);
				update_user_meta($user_id, 'job_position', $admin_job_position);
				update_user_meta($user_id, 'phone_number', $phone_number);
				update_user_meta($user_id, 'customer_group', $customer_group);

				if ($company_id) {
					self::aw_company_accounts_add_flash_notice( __('Company detail updated successfully'), 'success', true );
				} 
			}
			$url .= '&id=' . $company_id;
			wp_redirect($url);
		}

		if (isset($_POST['aw_ca_savedomain'])) {
			$domain_id = 0;
			$url =  admin_url() . 'admin.php?page=new-company-form';
			$db_domain_table = $wpdb->prefix . 'aw_ca_company_domain';
			if (isset($_POST['domain_id'])  && !empty($_POST['domain_id']) ) {
				$domain_id 		= sanitize_text_field($_POST['domain_id']); 
			}
			if (isset($_POST['company_id'])) {
				$post_array['company_id'] 		= sanitize_text_field($_POST['company_id']); 
			}
			if (isset($_POST['domain_name'])) {
				$post_array['domain_name'] 		= sanitize_text_field($_POST['domain_name']);  
			} 
			if (isset($_POST['domain_status'])) {
				$post_array['status']			= sanitize_text_field($_POST['domain_status']);
			} else {
				$post_array['status'] 		= 'pending';
			}

			if (isset($_POST['company_id'])) {
				$company_id = sanitize_text_field($_POST['company_id']); 
			}
			if (0 === $domain_id) {
				$wpdb->insert( $db_domain_table , $post_array );	
				 $mail_template = 'New Company Domain Created';
				 $admin_obj 	= get_users( array( 'role__in' => array( 'administrator' ) ) );
				foreach ( $admin_obj as $user ) {
					aw_ca_send_mail_notification( $user->ID, $mail_template); 
				}
				self::aw_company_accounts_add_flash_notice( __('New domain saved successfully'), 'success', true );
			} else {
				$company 	= aw_ca_get_company_by_id($company_id);
				$wpdb->update( $db_domain_table , $post_array, array('id'=>$domain_id) );
				$mail_template = 'Domain Status Changed';
				aw_ca_send_mail_notification( $company->company_admin_id, $mail_template);
				self::aw_company_accounts_add_flash_notice( __('Domain updated successfully'), 'success', true );
			}
			
			$url .= '&id=' . $company_id;
			wp_redirect($url);
		}
	}

	public static function aw_ca_users_list_by_company( $query) {
		global $pagenow, $wpdb;
		$company_admin_id = get_current_user_id();
		$user_meta = get_userdata($company_admin_id);
		$user_roles = $user_meta->roles;
	 
		if ( !empty($user_roles) && in_array( 'company_admin', $user_roles , true ) ) {
			if ('users.php' == $pagenow) {
				$company_id = get_user_meta( $company_admin_id, 'company_id', true);
				$meta_query = [['key' => 'company_id','value' => $company_id]];
				$query->set('meta_key', 'company_id');
				$query->set('meta_query', $meta_query);
			}
		}

		$orderby = $query->get('orderby');
		$order = $query->get('order');
		if ('company_name' == $orderby) {
			$query->set('meta_key', 'company_name');
			$query->set('orderby', 'meta_value');
		}
		if ('job_position' == $orderby) {
			$query->set('meta_key', 'job_position');
			$query->set('orderby', 'meta_value');
		}
		if ('phone_number' == $orderby) {
			$query->set('meta_key', 'phone_number');
			$query->set('orderby', 'meta_value');
		}
		if ('user_status' == $orderby) {
			$query->set('meta_key', 'user_status');
			$query->set('orderby', 'meta_value');
		}
	}

	public static function aw_ca_company_user_updated_meta( $user_id) {
		$company_admin_id = get_current_user_id();
		$user_meta = get_userdata($company_admin_id);
		$user_roles= array('company_admin');
		if (!empty($user_meta)) {
			$user_roles = $user_meta->roles;
		}
		if ( !empty($user_roles) && in_array( 'company_admin', $user_roles, true ) ) {
			$company_id = get_user_meta( $company_admin_id, 'company_id', true);
			update_user_meta( $user_id, 'company_id', $company_id);
			$company 	= aw_ca_get_company_by_id($company_id);
			if (!empty($company)) {
				update_user_meta($user_id, 'company_name', $company->company_name);	
			}
		} else {
			$user_meta = get_userdata($user_id);
			$user_email = $user_meta->user_email;
			$domaindata = explode('@', $user_email);
			$domain 	= $domaindata[1];
			$company_id = aw_ca_get_company_id_by_domain($domain);
			$company 	= aw_ca_get_company_by_id($company_id);
			if (!empty($company)) {
				update_user_meta($user_id, 'company_name', $company->company_name);			
			}
			update_user_meta($user_id, 'company_id', $company_id);
			update_user_meta($user_id, 'job_position', '');
			update_user_meta($user_id, 'billing_phone', '');
		}
		update_user_meta($user_id, 'user_status', 'active');
	}

	public static function aw_ca_orders_list_by_company( $query) {
		global $pagenow;
		$user_roles 		= array();
		$company_admin_id 	= get_current_user_id();
		$company_id 		= get_user_meta( $company_admin_id, 'company_id', true);
		$user_meta 			= get_userdata($company_admin_id);
				 
		if (!empty($user_meta)) {
			$user_roles = $user_meta->roles;	
		}

		if (isset($query->query_vars['post_type']) && 'shop_order' == $query->query_vars['post_type']  &&'edit.php' == $pagenow && !empty($user_roles) && in_array( 'company_admin', $user_roles, true )) {

				$meta_query = [['key' => 'company_id','value' => $company_id]];
				$query->set('meta_key', 'company_id');
				$query->set('meta_query', $meta_query);

				add_filter( 'views_edit-shop_order', function( $views ) {
					global $pagenow;
					global $wp;
					$url_to_redirect 	= home_url( $pagenow . '?post_type=shop_order&' );
					$all_count 			= aw_ca_get_post_status_by_user('all');
					$processing_count 	= aw_ca_get_post_status_by_user('wc-processing');
					$completed_count 	= aw_ca_get_post_status_by_user('wc-completed');
					$failed_count 		= aw_ca_get_post_status_by_user('wc-failed');
					$trash_count 		= aw_ca_get_post_status_by_user('wc-trash');
					$completed_count 	= aw_ca_get_post_status_by_user('wc-completed');
					$onhold_count 		= aw_ca_get_post_status_by_user('wc-on-hold');
					$trash_count 		= aw_ca_get_post_status_by_user('trash');
					$cancelled_count 	= aw_ca_get_post_status_by_user('wc-cancelled');
					 
					$views['all'] 			= sprintf("<a href='%s'>All (%d)", '' , $all_count );
					$views['wc-processing'] = sprintf("<a href='%s'>Processing (%d)", $url_to_redirect, $processing_count );
					$views['wc-completed'] 	= sprintf("<a href='%s'>Completed (%d)", '', $completed_count );
					$views['wc-failed'] 	= sprintf("<a href='%s'>Failed (%d)", '', $failed_count );
					$views['wc-on-hold'] 	= sprintf("<a href='%s'>On hold (%d)", '', $onhold_count );
					$views['trash'] 	= sprintf("<a href='%s'>On Trash (%d)", '', $trash_count );
					$views['wc-cancelled'] = sprintf("<a href='%s'>On Trash (%d)", '', $cancelled_count );
					return $views;
				});
		}
	}


	public static function aw_ca_userstable_add_custom_column( $columns) {
		$company_admin_id 	= get_current_user_id();
		$company_id 		= get_user_meta( $company_admin_id, 'company_id', true);
		$user_meta 			= get_userdata($company_admin_id);
		$user_roles 		= $user_meta->roles[0];
		$columns['company_name'] = 'Company';	

		if ('company_admin' === $user_roles ) {
			$columns['user_status'] = 'Status in Company';	
			$columns['billing_phone'] = 'Phone Number';	
			$columns['job_position'] = 'Job Position';	
		}
		return $columns;
	}

	public static function aw_ca_userstable_add_custom_column_view( $value, $column_name, $user_id) {
		$user_info 		= get_userdata( $user_id );
		$company_id 	= get_user_meta( $user_id, 'company_id', true);
		if ('company_name' === $column_name ) {
			$company_detail = aw_ca_get_company_by_id( $company_id );
			if (!empty($company_detail)) {
				return ucfirst(get_user_meta( $user_id, 'company_name', true));//ucfirst($company_detail->company_name);		
			}
		} 

		if ('user_status' === $column_name) {
			$user_meta 	= get_userdata($user_id);
			$user_roles =  $user_meta->roles;	
			if (!empty($user_roles)) {
				update_user_meta($user_id, 'user_status', 'active');
				return  'Active';	
			} else {
				update_user_meta($user_id, 'user_status', 'deactive');
				return  'Dactive';	
			}
		}
		if ('billing_phone' === $column_name) {
			$user_phone = get_user_meta( $user_id, 'billing_phone', true);
			return $user_phone;	
		}
		if ('job_position' === $column_name) {
			$job_position = get_user_meta( $user_id, 'job_position', true);
			return $job_position;	
		}
		return $value;
	}

	public static function aw_ca_register_sortable_column( $columns) {
		$columns['company_name'] 	= 'company_name';
		$columns['job_position'] 	= 'job_position';
		$columns['billing_phone']	= 'billing_phone';
		$columns['user_status']		= 'user_status';
		$columns['role']			= 'role';

		return $columns;
	}

	public static function aw_ca_role_permission() {
		?>
		<div class="wrap">
			<div class="page-title">
				<h1 class="wp-heading-inline">Roles</h1>
				<a href="<?php echo esc_url(admin_url('admin.php?page=add-new-role')); ?>" class="page-title-action">Add New Role</a>
				<hr class="wp-header-end">
			</div>

			<div class="panel-body">
				<div class="tabcontent">
					<?php
						$tablename 	= 'aw_ca_company_domain';
						$role_obj 	= new AwcaRolePermissionAdmin();
						$search 	= '';
					if (isset($_GET['s'])) {
						$search = sanitize_text_field($_GET['s']);
						if (isset($_GET['d'])) {
							$link_search = '';
						}
						$count_all 	= $role_obj->get_count($tablename);
					}
					?>
					<form id="ca-rolepermission-table" method="GET">
						<input type="hidden" name="page" class="page" value="roles-and-permission">
						<?php
							$role_obj->prepare_items($search);
							$role_obj->display();
						?>
					</form>
				</div>
			</div>
		</div>
		<?php
		 
	}

	public static function aw_ca_add_new_role() {
		$role_name 		= '';
		$order_limit 	= '';
		$role_name 		= '';
		$order_limit 	= '';
		$allpermissions = array('all'=>'','company_info'=>'','company_info_view'=>'' ,'company_info_edit'=>'','company_user'=>'','company_user_viewall'=>'','company_user_addnew'=>'','company_user_edit'=>'','company_user_userstatus'=>'','company_roles'=>'','company_roles_viewall'=>'','company_roles_addnew'=>'','company_roles_edit'=>'','orders'=>'','orders_viewall'=>'','credit_limit'=>'','credit_limit_view'=>'','transaction_viewall'=>'');
		if (isset($_GET['id'])) {
			$id			= sanitize_text_field($_GET['id']);
			$details 	= aw_ca_get_role_permission_row( $id );
			if (!empty($details)) {
				$role_name 		= $details['role_name'];
				$order_limit 	= $details['order_limit'];
				$permissions 	= maybe_unserialize($details['permissions']);	
				foreach ($allpermissions as $key => $value) {
					if (isset($permissions[$key])) {
						$allpermissions[$key] = 1;
					} else {
						$allpermissions[$key] = '';
					}
				}
			}
		}
		?>
		<h1>New Role</h1>
		<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data" onsubmit="return formvalidate(event)">
		<?php wp_nonce_field( 'save_role_permission_form_data', 'awcompanyaccounts_admin_nonce' ); ?>
			<input type="hidden" name="action" value="ca_role_permission_save_form">
			<table class="form-table">
				<tbody>
					<tr><h2>General Information</h2><hr></tr>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="woocommerce_new_order_subject">Role Name *<span class="woocommerce-help-tip"></span>
							</label>
						</th>
						<td class="">
							<fieldset>
								<input class="input-text regular-input txt_required" id="role_name" type="text" name="role_name" value="<?php echo wp_kses($role_name, wp_kses_allowed_html('post')); ?>" maxlength="50">
								<p><span class="error_role_name" id=""></span></p>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for=""> Order Limit per Role <span class="woocommerce-help-tip"></span>
							</label>
						</th>
						<td class="formcompany">
							<fieldset>
								<input class="input-text regular-input" id="order_limit" type="text" name="order_limit" value="<?php echo wp_kses($order_limit, wp_kses_allowed_html('post')); ?>" maxlength="50">
								<p><span class="error_company_name" id=""></span></p>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th>
							<label for=""> Permissions to the Role </label>
						</th>
						<td>
							<ul id="myUL" class="permission_checklist">
								<li><span class="caret"></span>
									<?php 
										 $checked = '';
									if ($allpermissions['company_info']) {
									   $checked = 'checked="checked"';
									}
									?>
									<input <?php echo wp_kses($checked, wp_kses_allowed_html('post')); ?> type="checkbox" name="checkbox[all]" value="1" />
									All
									<ul class="nested children">
										<li>
											<span class="caret"></span>
												<?php 
													 $checked = '';
												if ($allpermissions['company_info']) {
												   $checked = 'checked="checked"';
												}
												?>
												<input <?php echo wp_kses($checked, wp_kses_allowed_html('post')); ?> type="checkbox" name="checkbox[company_info]" value="<?php echo wp_kses($allpermissions['company_info'], wp_kses_allowed_html('post')); ?>" />
												Company Information
												<ul class="nested children">
													<li>
													<?php 
														 $checked = '';
													if ($allpermissions['company_info_view']) {
													   $checked = 'checked="checked"';
													}
													?>
													<input <?php echo wp_kses($checked, wp_kses_allowed_html('post')); ?> type="checkbox" name="checkbox[company_info_view]" value="<?php echo wp_kses($allpermissions['company_info_view'], wp_kses_allowed_html('post')); ?>" />View</li>
													<li>
													<?php 
														 $checked = '';
													if ($allpermissions['company_info_edit']) {
													   $checked = 'checked="checked"';
													}
													?>
													<input <?php echo wp_kses($checked, wp_kses_allowed_html('post')); ?> type="checkbox" name="checkbox[company_info_edit]" value="<?php echo wp_kses($allpermissions['company_info_edit'], wp_kses_allowed_html('post')); ?>" />edit</li>
												</ul>
										</li>

										<li>
											<span class="caret"></span>
												<?php 
													 $checked = '';
												if ($allpermissions['company_user']) {
												   $checked = 'checked="checked"';
												}
												?>
												<input <?php echo wp_kses($checked, wp_kses_allowed_html('post')); ?> class="company_user" type="checkbox" name="checkbox[company_user]" value="<?php echo wp_kses($allpermissions['company_user'], wp_kses_allowed_html('post')); ?>" />
												Company Users
											
												<ul class="nested children">
													<li>
														<?php 
															 $checked = '';
														if ($allpermissions['company_user_viewall']) {
														   $checked = 'checked="checked"';
														}
														?>
													<input <?php echo wp_kses($checked, wp_kses_allowed_html('post')); ?> type="checkbox" name="checkbox[company_user_viewall]" value="<?php echo wp_kses($allpermissions['company_user_viewall'], wp_kses_allowed_html('post')); ?>" />View All</li>
													<li>
														<?php 
															 $checked = '';
														if ($allpermissions['company_user_addnew']) {
														   $checked = 'checked="checked"';
														}
														?>
													<input <?php echo wp_kses($checked, wp_kses_allowed_html('post')); ?> type="checkbox" name="checkbox[company_user_addnew]" value="<?php echo wp_kses($allpermissions['company_user_addnew'], wp_kses_allowed_html('post')); ?>" />Add New</li>
													<li>
														<?php 
															 $checked = '';
														if ($allpermissions['company_user_edit']) {
														   $checked = 'checked="checked"';
														}
														?>
													<input <?php echo wp_kses($checked, wp_kses_allowed_html('post')); ?> type="checkbox" name="checkbox[company_user_edit]" value="<?php echo wp_kses($allpermissions['company_user_edit'], wp_kses_allowed_html('post')); ?>" />Edit</li>
													<li>
														<?php 
															 $checked = '';
														if ($allpermissions['company_user_userstatus']) {
														   $checked = 'checked="checked"';
														}
														?>
													<input <?php echo wp_kses($checked, wp_kses_allowed_html('post')); ?> type="checkbox" name="checkbox[company_user_userstatus]" value="<?php echo wp_kses($allpermissions['company_user_userstatus'], wp_kses_allowed_html('post')); ?>" />Change Status</li>
												</ul>
										</li>

										<li>
											<span class="caret"></span>
													<?php 
														 $checked = '';
													if ($allpermissions['company_roles']) {
													   $checked = 'checked="checked"';
													}
													?>
													<input <?php echo wp_kses($checked, wp_kses_allowed_html('post')); ?> class="company_roles" type="checkbox" name="checkbox[company_roles]" value="<?php echo wp_kses($allpermissions['company_roles'], wp_kses_allowed_html('post')); ?>" />
												Company Roles
											
												<ul class="nested children">
													<li>
														<?php 
															 $checked = '';
														if ($allpermissions['company_roles_viewall']) {
														   $checked = 'checked="checked"';
														}
														?>
													<input <?php echo wp_kses($checked, wp_kses_allowed_html('post')); ?> type="checkbox" name="checkbox[company_roles_viewall]" value="<?php echo wp_kses($allpermissions['company_roles_viewall'], wp_kses_allowed_html('post')); ?>" />View All</li>
													<li>
														<?php 
															 $checked = '';
														if ($allpermissions['company_roles_addnew']) {
														   $checked = 'checked="checked"';
														}
														?>
													<input <?php echo wp_kses($checked, wp_kses_allowed_html('post')); ?> type="checkbox" name="checkbox[company_roles_addnew]" value="<?php echo wp_kses($allpermissions['company_roles_addnew'], wp_kses_allowed_html('post')); ?>" />Add New</li>
													<li>
														<?php 
															 $checked = '';
														if ($allpermissions['company_roles_edit']) {
														   $checked = 'checked="checked"';
														}
														?>
													<input <?php echo wp_kses($checked, wp_kses_allowed_html('post')); ?> type="checkbox" name="checkbox[company_roles_edit]" value="<?php echo wp_kses($allpermissions['company_roles_edit'], wp_kses_allowed_html('post')); ?>" />Edit</li>
												</ul>
										</li>

										<li>
											<span class="caret"></span>
												<?php 
													 $checked = '';
												if ($allpermissions['orders']) {
												   $checked = 'checked="checked"';
												}
												?>
												<input <?php echo wp_kses($checked, wp_kses_allowed_html('post')); ?> class="company_orders" type="checkbox" name="checkbox[orders]" value="<?php echo wp_kses($allpermissions['orders'], wp_kses_allowed_html('post')); ?>" />
												Orders
											
												<ul class="nested children">
													<li>
													<?php 
														 $checked = '';
													if ($allpermissions['orders_viewall']) {
													   $checked = 'checked="checked"';
													}
													?>
													<input <?php echo wp_kses($checked, wp_kses_allowed_html('post')); ?> type="checkbox" name="checkbox[orders_viewall]" value="<?php echo wp_kses($allpermissions['orders_viewall'], wp_kses_allowed_html('post')); ?>" />View All</li>
												</ul>
										</li>
										<?php /*
										<li>
											<span class="caret"></span>
												<?php 
													 $checked = '';
												if ($allpermissions['credit_limit']) {
												   $checked = 'checked="checked"';
												}
												?>
												<input <?php echo wp_kses($checked, wp_kses_allowed_html('post')); ?> class="company_orders" type="checkbox" name="checkbox[credit_limit]" value="<?php echo wp_kses($allpermissions['credit_limit'], wp_kses_allowed_html('post')); ?>" />
												Company Credit Limit
											
												<ul class="nested children">
													<li>											
													<?php 
															 $checked = '';
													if ($allpermissions['credit_limit_view']) {
													   $checked = 'checked="checked"';
													}
													?>
														<input <?php echo wp_kses($checked, wp_kses_allowed_html('post')); ?> type="checkbox" name="checkbox[credit_limit_view]" value="<?php echo wp_kses($allpermissions['credit_limit_view'], wp_kses_allowed_html('post')); ?>" />View, and Use on Checkout
													<ul class="nested children">
														<li>
															<?php 
															 $checked = '';
															if ($allpermissions['transaction_viewall']) {
																$checked = 'checked="checked"';
															}
															?>
															<input <?php echo wp_kses($checked, wp_kses_allowed_html('post')); ?> type="checkbox" name="checkbox[transaction_viewall]" value="<?php echo wp_kses($allpermissions['transaction_viewall'], wp_kses_allowed_html('post')); ?>" />
															View All Transactions
														</li>
													</ul>	
													</li>							
												</ul>
										</li>	
										*/ ?>									
									</ul>
								</li>	
							</ul>
						</td>
					</tr>
					 
				</tbody>
				<tfoot>
					<tr valign="top">
						<td>

						</td>
					</tr>
				</tfoot>
			</table>
			<div class="submit">
				<?php 
				if (isset($_GET['id'])) { 
					?>
					<input type="hidden" value="<?php echo wp_kses($_GET['id'], wp_kses_allowed_html('post')); ?>" name="id"/>		
					<input type="submit" class="button button-primary" value="Update" name="ca_role_permission_update"/>		
				<?php } else { ?>
				<input type="submit" class="button button-primary" value="Save" name="ca_role_permission_submit"/>	
				<?php } ?>
			</div>
		</form>
		<?php 	
	}

	public static function ca_role_permission_save_form() {
		global $wpdb; 
		$db_role_permission_table = $wpdb->prefix . 'aw_ca_role_permission'; 
		$url 		=  admin_url() . 'admin.php?page=roles-and-permission';
		$post 		= array();
		$permission	= array();
		$user_id 	= get_current_user_id();
		$company_id = get_user_meta($user_id, 'company_id', true);

		if (isset($_POST['awcompanyaccounts_admin_nonce'])) {
			$awcompanyaccounts_admin_nonce = sanitize_text_field($_POST['awcompanyaccounts_admin_nonce']);
		}

		if ( !wp_verify_nonce( $awcompanyaccounts_admin_nonce, 'save_role_permission_form_data' )) {
			wp_die('Our Site is protected');
		}

		$rolepermissions = array('all','company_info','company_info_view','company_info_edit','company_user','company_user_viewall','company_user_addnew','company_user_edit','company_user_userstatus','company_roles','company_roles_viewall','company_roles_addnew','company_roles_edit','orders','orders_viewall','credit_limit','credit_limit_view','transaction_viewall');
		$post['permissions'] = '';
		if (isset($_POST['role_name'])) {
			$post['role_name'] = sanitize_text_field($_POST['role_name']);
		}
		if (isset($_POST['order_limit'])) {
			$post['order_limit'] = sanitize_text_field($_POST['order_limit']);
		}
		if (isset($_POST['checkbox'])) {
			foreach ($rolepermissions as $value) {
				if ( isset($_POST['checkbox']) && isset($_POST['checkbox'][$value]) ) {
					$permission[$value] = 1;
				}
			}
			$post['permissions'] 	= maybe_serialize($permission);
			$post['company_id']		= $company_id; 	
		}
		if (isset($_POST['ca_role_permission_submit'])) {
			$wpdb->insert( $db_role_permission_table , $post );
		}
		if (isset($_POST['ca_role_permission_update'])) {
			if (isset($_POST['id'])) {
				$id = sanitize_text_field($_POST['id']);	
			}
			$result = $wpdb->update($db_role_permission_table, $post, array('id'=>$id));	
		}
		wp_redirect($url);
	}

	public static function aw_ca_get_domain_detail_ajax() {
		global $wpdb;
		$domain = array();
		check_ajax_referer( 'companyaccounts_admin_nonce', 'nonce_ca_ajax' );
		if (!empty($_POST['domain_id'])) {
			$domain_id 		= sanitize_text_field($_POST['domain_id']);
			$doamin_detail 	= aw_ca_get_company_domain_row_by_id($domain_id);
			$domain['domain_id'] 	= $domain_id;
			$domain['domain_name'] 	= $doamin_detail->domain_name;
			$domain['status'] 		= $doamin_detail->status;
		}
		echo json_encode($domain);
		die;
	}

	public static function aw_ca_restrict_order_statuses_update( $wc_statuses_arr ) {
		global $page;
		$enabled = get_option('aw_ca_order_approvalenable');
		if (current_user_can('company_admin') && 'no'===$enabled) {
			 $wc_statuses_arr = array();
			 $wc_statuses_arr['nostatus'] = 'No status';
			 return $wc_statuses_arr;
		}
		return $wc_statuses_arr; 
	}
	public static function aw_ca_filter_dropdown_bulk_actions_shop_order( $actions ) {
		$enabled = get_option('aw_ca_order_approvalenable');	
		$new_actions = [];
		foreach ( $actions as $key => $option ) {
			if ( current_user_can('company_admin') && 'no' === $enabled) {
				unset($actions[$key]);
			}
		}
		if ( count($new_actions) > 0 ) {
			return $new_actions;
		}
		return $actions;
	}
	public static function aw_ca_remove_specific_orders_column( $columns ) {
		$enabled = get_option('aw_ca_order_approvalenable');
		if ( current_user_can('company_admin') && 'no' === $enabled) {
			unset($columns['cb']);
		}
		return $columns;
	}

	// Remove role from roles list in company account
	public static function aw_ca_hide_editable_roles( $roles ) {
		if ( current_user_can('company_admin') ) {
			
			if (isset($roles['customer'])) {
				unset( $roles['customer'] );
			}
			if (isset($roles['shop_manager'])) {
				unset( $roles['shop_manager'] );
			}
			if (isset($roles['subscriber'])) {
				unset( $roles['subscriber'] );
			}
			if (isset($roles['subscriber'])) {
				unset( $roles['subscriber'] );
			}
			if (isset($roles['contributor'])) {
				unset( $roles['contributor'] );
			}	
			if (isset($roles['author'])) {
				unset( $roles['author'] );
			}	
			if (isset($roles['editor'])) {
				unset( $roles['editor'] );
			}
		}
	 return $roles;
	}
} // class end


