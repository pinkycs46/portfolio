<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

$CAMyAccount = new CAMyAccount();

class CAMyAccount {
	public function __construct() {

		add_filter('woocommerce_account_menu_items', array('CAMyAccount', 'aw_ca_account_menu_items'));
		/****Woocommerce Hook - Add endpoint title.****/
		add_filter('woocommerce_get_query_vars', array('CAMyAccount', 'aw_ca_mycredit_menu_history_query_vars'));
		add_filter('woocommerce_endpoint_aw-ca-mycompany_title', array('CAMyAccount', 'aw_ca_mycompany_endpoint_title'), 0);
		add_action('woocommerce_account_aw-ca-mycompany_endpoint', array('CAMyAccount', 'aw_ca_company_information'));

		add_filter('woocommerce_endpoint_aw-ca-users_title', array('CAMyAccount', 'aw_ca_users_endpoint_title'), 0);
		add_action('woocommerce_account_aw-ca-users_endpoint', array('CAMyAccount', 'aw_ca_users_list'));

		add_filter('woocommerce_endpoint_aw-ca-adduser_title', array('CAMyAccount', 'aw_ca_adduser_endpoint_title'), 0);
		add_action('woocommerce_account_aw-ca-adduser_endpoint', array('CAMyAccount', 'aw_ca_add_new_user'));

		add_filter('woocommerce_endpoint_aw-ca-rolespermissions_title', array('CAMyAccount', 'aw_ca_rolespermissions_endpoint_title'), 0);
		add_action('woocommerce_account_aw-ca-rolespermissions_endpoint', array('CAMyAccount', 'aw_ca_rolespermissions_list'));

		add_filter('woocommerce_endpoint_aw-ca-addrolespermissions_title', array('CAMyAccount', 'aw_ca_addrolespermissions_endpoint_title'), 0);
		add_action('woocommerce_account_aw-ca-addrolespermissions_endpoint', array('CAMyAccount', 'aw_ca_add_new_rolespermissions'));
		//Roles and Permissions
	}

	public static function aw_ca_front_add_flash_notice( $notice = '', $type = 'warning', $dismissible = true ) { 
		// Here we return the notices saved on our option, if there are not notices, then an empty array is returned
		$notices = get_option( 'ca_front_flash_notices', array() );
		$dismissible_text = ( $dismissible ) ? 'is-dismissible' : '';

		$notices = array(
						'notice' => $notice, 
						'type' => $type, 
						'dismissible' => $dismissible_text
					); 	
		update_option( 'ca_front_flash_notices', $notices );
	}

	public static function aw_ca_account_menu_items( $items) {
		$cc_menu_item = array();
		$loggedin_user_id 	= get_current_user_id();
		$permission 		= aw_ca_get_role_permission_by_company_row($loggedin_user_id);
		if (empty($permission)) {
			//unset($items['orders']);
			return $items;
		}	
		if (isset($permission['company_info_view'])) {
			$cc_menu_item['aw-ca-mycompany'] = __('Company Information', 'aw_ca_mycredit_limit');
		}
		if (isset($permission['company_user_viewall'])) {
			$cc_menu_item['aw-ca-users'] = __('Company Users', 'aw_ca_users_list');	
		}
		if (isset($permission['company_roles_viewall'])) {
			$cc_menu_item['aw-ca-rolespermissions'] = __('Roles Permissions', 'aw_ca_rolespermissions_list');	
		}
		if (!isset($permission['orders_viewall'])) {
			unset($items['orders']);
		}
		$cc_menu_item = array_slice($items, 0, 3, true) + $cc_menu_item + array_slice($items, 1, count($items), true);
		return $cc_menu_item;
	}

	public static function aw_ca_mycredit_menu_history_query_vars( $endpoints) {
		$endpoints['aw-ca-mycompany'] 	= 'aw-ca-mycompany';
		$endpoints['aw-ca-users'] 		= 'aw-ca-users';
		$endpoints['aw-ca-adduser'] 	= 'aw-ca-adduser';
		$endpoints['aw-ca-addrolespermissions'] = 'aw-ca-addrolespermissions';
		$endpoints['aw-ca-rolespermissions'] 	= 'aw-ca-rolespermissions';
		return $endpoints;
	}

	public static function aw_ca_mycompany_endpoint_title( $title) {
		global $wp;
		$parts = explode('/', $wp->request );
		
		$title = __( 'Company Information', 'woocommerce' );
		
		if (!empty($parts)) {
			if (is_numeric(end($parts))) {
				$current_page = end($parts);
				/* translators: current page number  */
				$title = sprintf( __( 'Credit Limit (page %d)', 'woocommerce' ), intval( $current_page ) );
			} else {
				if (isset($_GET['paged'])) {
				$current_page = (int) $_GET['paged'];
				/* translators: current page number  */
				$title = sprintf( __( 'Company Information (page %d)', 'woocommerce' ), intval( $current_page ) );
				return $title;
				}
			}
		} else {
				$title = __( 'Company Information', 'woocommerce' );
				return $title;
		}
		return $title;
	}

	public static function aw_ca_users_endpoint_title( $title) {
		global $wp;
		$parts = explode('/', $wp->request );

		$title = __( 'Company Users', 'woocommerce' );

		if (!empty($parts)) {
			if (is_numeric(end($parts))) {
				$current_page = end($parts);
				/* translators: current page number  */
				$title = sprintf( __( 'Company Users (page %d)', 'woocommerce' ), intval( $current_page ) );
			} else {
				if (isset($_GET['paged'])) {
				$current_page = (int) $_GET['paged'];
				/* translators: current page number  */
				$title = sprintf( __( 'Company Users (page %d)', 'woocommerce' ), intval( $current_page ) );
				return $title;
				}
			}
		} else {
				$title = __( 'Company Users', 'woocommerce' );
				return $title;
		}
		return $title;
	}

	public static function aw_ca_adduser_endpoint_title() {
		global $wp;
		$parts = explode('/', $wp->request );
		
		$title = __( 'New User', 'woocommerce' );
		
		if (!empty($parts)) {
			if (is_numeric(end($parts))) {
				$current_page = end($parts);
				/* translators: current page number  */
				$title = sprintf( __( 'New User (page %d)', 'woocommerce' ), intval( $current_page ) );
			} else {
				if (isset($_GET['paged'])) {
				$current_page = (int) $_GET['paged'];
				/* translators: current page number  */
				$title = sprintf( __( 'New User (page %d)', 'woocommerce' ), intval( $current_page ) );
				return $title;
				}
			}
		} else {
				$title = __( 'New User', 'woocommerce' );
				return $title;
		}
		return $title;
	}

	public static function aw_ca_company_information() {
		global $wpdb; 
		$loggedin_user_id 		= get_current_user_id();
		$searilized_form_setting= get_option('aw_ca_default_form', true);
		$company_form_setting 	= maybe_unserialize($searilized_form_setting);
		$existing_value 		= '';
		$permission 			= aw_ca_get_role_permission_by_company_row($loggedin_user_id);
		if (!isset($permission['company_info_view'])) {
			echo 'Not allowed to view this page';
			return;
		}
		if (isset($_POST['aw_ca_new_company_submit'])) {
			global $wpdb, $wp; 
			$db_company_table = $wpdb->prefix . 'aw_ca_company_information'; 
			if (isset($_POST['aw_ca_frontend_nonce'])) {
				$aw_ca_frontend_nonce = sanitize_text_field($_POST['aw_ca_frontend_nonce']);
			}

			if ( !wp_verify_nonce( $aw_ca_frontend_nonce, 'save_new_company_details' )) {
				wp_die('Our Site is protected');
			}
	
			$post_array = array();
			$company_form_data 	= array('company_name','company_email','company_legal_name','tax_vat_id','reseller_id','company_street','city','country','state','zip','company_phone');

			foreach ($company_form_data as $value) {
				if (isset($_POST['formdata'][$value])) {
					$post_array[$value] = sanitize_text_field($_POST['formdata'][$value]);	
				}
			}
			
			$post_array['status'] = 'pending';
			if (isset($_POST['formdata']['first_name'])) {
				$first_name = sanitize_text_field($_POST['formdata']['first_name']);
			}
			if (isset($_POST['formdata']['last_name'])) {
				$last_name 	= sanitize_text_field($_POST['formdata']['last_name']);
			}
			if (isset($_POST['formdata']['user_email'])) {
				$email 		= sanitize_text_field($_POST['formdata']['user_email']);
			}
			if (isset($_POST['company_id'])) {
				$company_id = sanitize_text_field($_POST['company_id']);
			}
			if (isset($_POST['formdata']['job_position'])) {
				$admin_job_position = sanitize_text_field($_POST['formdata']['job_position']);
			}
			if (isset($_POST['formdata']['user_phone'])) {
				$phone_number 		= sanitize_text_field($_POST['formdata']['user_phone']);
			}
			 

			$user_id = aw_ca_register_user( $email, $first_name, $last_name, 'company_admin', 'frontend' ); 
			update_user_meta($user_id, 'job_position', $admin_job_position);
			update_user_meta($user_id, 'user_phone', $phone_number);

			$post_array['company_admin_id'] = $user_id;
			$wpdb->update( $db_company_table , $post_array, array( 'id'=>$company_id ));
			update_user_meta($user_id, 'company_id', $company_id);
			if (isset($_POST['formdata']['company_name'])) {
				$company_name = sanitize_text_field($_POST['formdata']['company_name']);
			}
			update_user_meta( $user_id, 'company_name', $company_name );
		}

		//error_log('$loggedin_user_id->'.$loggedin_user_id);

		$company_id 		= get_user_meta( $loggedin_user_id, 'company_id', true );
		$company_detail 	= (array) aw_ca_get_company_by_id( $company_id );
		echo '<div class="wrap"><form method="post" id="aw_ca_company_form" enctype="multipart/form-data" onsubmit="return formvalidate(event)"><input type="hidden" name="action" value="aw_ca_save_new_company_details">';
		wp_nonce_field( 'save_new_company_details', 'aw_ca_frontend_nonce' );
		if (!empty($company_form_setting)) {
			
			foreach ($company_form_setting as $formkey=> $currentform) {
				$tbody = '';
				$formkey	= str_replace( 'Customization', '' , $formkey );
				$formkey 	= str_replace( '_', ' ', $formkey );
				echo '<h3>' . wp_kses( $formkey, wp_kses_allowed_html('post') ) . '</h3>';
				echo '<table id="companyinoform"  class="widefat striped" ><tbody id="companyino-list" class="main-list">';
				foreach ($currentform as $key=>$value) {
					$required 			= '';
					$mandatory			= '';
					$class 				= '';
					$extracheck 		= '';
					if ($value['required']) {
						$required 		= 'required';
						$mandatory 		= '<span class="req_field">*</span>';
						 
						$class 			= 'txt_required';
						if ('user_email'=== trim($value['name']) || 'company_email'===trim($value['name'])) {
							$class 		.= ' validemail';
						}

					}
					if ('user_phone'=== trim($value['name']) || 'company_phone' === trim($value['name'])) {
						$extracheck	.= 'maxlength=10 onkeypress="return awcacheckIt(event)"';
					}

					if (isset($company_detail[$value['name']])) {
						$existing_value = $company_detail[$value['name']] ;	
					} else {
						if ('user_email' ===  $value['name'] ) {
							$user_info = get_userdata($company_detail['company_admin_id']);
							$existing_value = $user_info->user_email;
						}
						if ('first_name' === $value['name'] || 'last_name' === $value['name']  || 'user_phone' === $value['name']  || 'job_position' === $value['name'] ) {
							$existing_value = get_user_meta( $company_detail['company_admin_id'], $value['name'], true );
						}
					}
					$tbody .= '<tr class="tr-' . $key . '">
									<td><label>' . $value['label'] . '</label>' . $mandatory . '
										<input type="text" id="' . trim($value['name']) . '" name="formdata[' . trim($value['name']) . ']" value="' . $existing_value . '"  class="' . $class . '" ' . $extracheck . '/>';
					if (''!=$required) {
					$tbody .= 		'<p><span class="error_' . $value['name'] . '"></span></p>';				}
					$tbody .= 		'</td>
								</tr>' ;
				}
				echo wp_kses($tbody, wp_kses_allowed_html('post'));
				echo '</tbody></table>';
			}
		}
		
		if (!empty($permission) && isset($permission['company_info_edit'])) {
			echo '<input type="hidden" value="' . wp_kses($company_id, wp_kses_allowed_html('post')) . '" name="company_id" />';
			echo '<div class="submit">
					<input type="submit" class="button button-primary" value="Submit" name="aw_ca_new_company_submit"/>	
				</div>';
		}		
		echo '</form></div>';
	}

	public static function aw_cc_get_credit_history_user_perpage( $user_id, $history_per_page, $offset ) {
		global $wpdb;
		$credit_history = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_company_credit_history WHERE user_id = %d ORDER BY `last_payment` DESC LIMIT %d OFFSET %d ", "{$user_id}", "{$history_per_page}", "{$offset}") );

		return $credit_history;
	}

	public static function aw_ca_add_new_user() {
		$id 				= '';
		$loggedin_user_id 	= get_current_user_id();
		$company_id 		= get_user_meta( $loggedin_user_id, 'company_id', true);
		$permission 		= aw_ca_get_role_permission_by_company_row($loggedin_user_id);
		if (!isset($permission['company_user_viewall'])) {
			echo 'Not allowed to view this page';
			return;
		}
		if (!isset($permission['company_user_addnew'])) {
			echo 'Not allowed to view this page';
			return;	
		}
		if (!isset($permission['company_user_edit'])) {
			echo 'Not allowed to view this page';
			return;
		}
		if (isset($_GET['id'])) {
			$id = (int) sanitize_text_field($_GET['id']);
		}
		
		$loggedin_user_id 	= get_current_user_id();
		$user_data 			= get_userdata($loggedin_user_id);
		if (!empty($user_data)) {
			$role_name 	= $user_data->roles[0];	
			$email 		= $user_data->get('user_email');
			$first_name = $user_data->first_name;
			$last_name 	= $user_data->last_name;
		}
		$job_position 		= get_user_meta( $loggedin_user_id, 'job_position', true);
		$user_phone 		= get_user_meta( $loggedin_user_id, 'user_phone', true);

		$post_array = array();
		$company_form_data 	= array('job_position','user_role','first_name','user_email','user_phone');
		if (isset($_POST['job_position'])) {
			 $job_position 	= sanitize_text_field($_POST['job_position']);
		}
		if (isset($_POST['user_role'])) {
			 $user_role 	= sanitize_text_field($_POST['user_role']);
		}	
		if (isset($_POST['first_name'])) {
			 $first_name 	= sanitize_text_field($_POST['first_name']);
		}
		if (isset($_POST['last_name'])) {
			 $last_name 	= sanitize_text_field($_POST['last_name']);
		}
		if (isset($_POST['user_email'])) {
			 $email 		= sanitize_text_field($_POST['user_email']);
		}	
		if (isset($_POST['user_phone'])) {
			 $user_phone 	= sanitize_text_field($_POST['user_phone']);
		}					
		if (isset($_POST['aw_ca_save_new_user'])) {
			if (isset($_POST['aw_ca_frontend_nonce'])) {
				$aw_ca_frontend_nonce = sanitize_text_field($_POST['aw_ca_frontend_nonce']);
			}
			if ( !wp_verify_nonce( $aw_ca_frontend_nonce, 'save_new_user_details' )) {
				wp_die('Our Site is protected');
			}
			if ( username_exists( $email ) || email_exists( $email ) ) {
				self::aw_ca_front_add_flash_notice( __('User email already exist'), 'error', true );	
			} else {
				$user_id = aw_ca_register_user( $email, $first_name, $last_name, $user_role, 'frontend'); 
				update_user_meta( $user_id, 'job_position', $job_position);
				update_user_meta( $user_id, 'phone_number', $user_phone);
				update_user_meta( $user_id, 'company_id', $company_id);
				$company = aw_ca_get_company_by_id($company_id);
				update_user_meta($user_id, 'company_name', $company->company_name);
				self::aw_ca_front_add_flash_notice( __('User created successfully'), 'message', true );		
			}
		}
		if (isset($_POST['aw_ca_update_new_user'])) {
			if (isset($_POST['aw_ca_frontend_nonce'])) {
				$aw_ca_frontend_nonce = sanitize_text_field($_POST['aw_ca_frontend_nonce']);
			}
			if ( !wp_verify_nonce( $aw_ca_frontend_nonce, 'save_new_user_details' )) {
				wp_die('Our Site is protected');
			}
			if ( username_exists( $email ) || email_exists( $email ) ) {
				self::aw_ca_front_add_flash_notice( __('User email already exist'), 'error', true );	
			} else {
				$user_data = array ('ID' => $id, 'user_email' => esc_attr( $email ),'first_name' => $first_name, 'last_name' => $last_name );
				$user_id = wp_update_user($user_data);
				update_user_meta( $user_id, 'job_position', $job_position);
				update_user_meta( $user_id, 'phone_number', $user_phone);
				update_user_meta( $user_id, 'company_id', $company_id);
				$company = aw_ca_get_company_by_id($company_id);
				update_user_meta($user_id, 'company_name', $company->company_name);
				self::aw_ca_front_add_flash_notice( __('User updated successfully'), 'message', true );	
			}
		}
		 
		$get_roles = aw_ca_get_allrole_permission($company_id);

		$notice = get_option( 'ca_front_flash_notices', array() );
		if ( ! empty( $notice ) ) {
				printf('<div class="woocommerce-%1$s %2$s"><p>%3$s</p></div>',
					wp_kses($notice['type'], wp_kses_allowed_html('post')),
					wp_kses($notice['dismissible'], wp_kses_allowed_html('post')),
					wp_kses($notice['notice'], wp_kses_allowed_html('post'))
				);
			delete_option( 'ca_front_flash_notices', array() );
		} 
		?>
		<div class="woocommerce-notices-wrapper"></div>
		<form class="woocommerce-EditAccountForm edit-account" action="" method="post" onsubmit="return formvalidate(event)">
			<?php 
				 wp_nonce_field( 'save_new_user_details', 'aw_ca_frontend_nonce' );
			?>
			<fieldset>
				<legend>New User</legend>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="password_current">Job Position</label>
					<input type="text" class="woocommerce-Input input-text" name="job_position" id="job_position" autocomplete="off">
				</p>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="password_1">User Role <span class="req_field">*</span></label>
					<select name="user_role" id="user_role" class="woocommerce-Select">
						<?php 
						if (!empty($get_roles)) {
							foreach ($get_roles as $key => $role) { 
								?>
								<option value="<?php echo wp_kses($role->role_name, wp_kses_allowed_html('post')); ?>">
									<?php echo wp_kses(ucfirst( str_replace('_', ' ', $role->role_name)), wp_kses_allowed_html('post')); ?>
								</option>
						<?php 
							}
						}
						?>
					</select>
				</p>
				
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="first_name">First Name <span class="req_field">*</span></label>
					<input type="text" class="woocommerce-Input input-text txt_required" name="first_name" value="<?php echo wp_kses($first_name, wp_kses_allowed_html('post')); ?>" id="first_name" autocomplete="off"> 
					<span class="error_first_name"></span>
				</p>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="last_name">Last Name <span class="req_field">*</span></label>
					<input type="text" class="woocommerce-Input input-text txt_required" name="last_name" value="<?php echo wp_kses($last_name, wp_kses_allowed_html('post')); ?>" id="last_name" autocomplete="off">
					<span class="error_last_name"></span>
				</p>
				
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="user_email">Email <span class="req_field">*</span></label>
					<input type="text" class="woocommerce-Input input-text validemail txt_required" name="user_email" value="<?php echo wp_kses($email, wp_kses_allowed_html('post')); ?>" id="user_email" autocomplete="off"> 
					<span class="error_user_email"></span>
				</p>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="user_email">Phone Number</label>
					<input type="text" class="woocommerce-Input input-text" name="user_phone" maxlength="10" id="user_phone" value="<?php echo wp_kses($user_phone, wp_kses_allowed_html('post')); ?>" autocomplete="off" onkeypress="return awcacheckIt(event)"> 
				</p>
			</fieldset>
			<div class="clear"></div>
			<p>
				<?php
				if ('' != $id) { 
					?>
						<input type="hidden" name="id" value="<?php echo wp_kses($id, wp_kses_allowed_html('post')); ?>">		
						<button type="submit" class="woocommerce-Button button" name="aw_ca_update_new_user" value="Update">Update</button>
				<?php } else { ?>
						<button type="submit" class="woocommerce-Button button" name="aw_ca_save_new_user" value="Save">Save</button>
				<?php 	
				}	
				?>

				<input type="hidden" name="action" value="aw_ca_save_new_user">
			</p>
		</form>
			 
		<?php
	}
	public static function aw_ca_users_list() {
		$structure 		= get_option( 'permalink_structure' );
		$record_per_page= get_option('posts_per_page');
		$user_id		= get_current_user_id();
		$company_id 	= get_user_meta( $user_id, 'company_id', true);
		$userlist  		= get_users(array( 'meta_query'   => array( array('key'=>'company_id','value'=>$company_id) ) ) );
		$total_records  = count($userlist);
		$nexturl 		= '';
		$current_page 	= 1;
		if (isset($_GET['paged'])) {
			$current_page = (int) $_GET['paged'];
		}
		
		$permission 	= aw_ca_get_role_permission_by_company_row($user_id);
		if (!isset($permission['company_user_viewall'])) {
			echo 'Not allowed to view this page';
			return;
		}
		switch ($structure) {
			case '/%year%/%monthnum%/%day%/%postname%/':
			case '/%year%/%monthnum%/%postname%/':
			case '/archives/%post_id%':
			case '/%postname%/':
				global $wp;
				$parts = explode('/', $wp->request );
				if (is_numeric(end($parts))) {
					$current_page = end($parts);
				}
				$nexturl = esc_url( wc_get_endpoint_url( 'aw-ca-users', $current_page + 1 ) );
				break;
			default:
				$nexturl =  esc_url(add_query_arg('paged', $current_page + 1));	
		}

		$offset 	= ( $current_page - 1 ) * $record_per_page;	
		$userlist 	= get_users( 	
									array( 
										'number' => $record_per_page,
										'offset' => $offset,
										'meta_query'   => array( array('key'=>'company_id','value'=>$company_id) ) 
										)
								);
		 
		$pages 		= ceil($total_records/$record_per_page);
		$premalink 	= esc_url( wc_get_endpoint_url( 'aw-ca-adduser'));
		?>
		<h3>Company Users</h3>
		<?php 

		if (isset($permission['company_user_addnew'])) { 
			?>
			<div class="aw-ca-btn-action"><a href="<?php echo wp_kses($premalink, wp_kses_allowed_html('post')); ?>" class="woocommerce-button button view">Add New User</a></div>
		<?php 
		}
		if (empty($userlist)) {  
			?>
				<div class="woocommerce-notices-wrapper"></div>
				<p class="woocommerce-noreviews"><?php esc_html_e( 'There is no user yet.', 'woocommerce' ); ?></p>
				<?php 	
				return ;
		}  
		?>
		<div class="aw-ca-table-responsive-grid">		
			<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
				<thead>
					<tr>
						<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><span class="nobr">ID</span></th>
						<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-date"><span class="nobr">Name</span></th>
						<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-status"><span class="nobr">Email</span></th>
						<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-total"><span class="nobr">Phone Number</span></th>
						<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-balance"><span class="nobr">Role</span></th>
						<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-limit"><span class="nobr">Job Position</span></th>
						<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-customercomment"><span class="nobr">Status</span></th>
					</tr>
				</thead>
					<tbody>
							<?php 
							if (!empty($userlist)) {
								foreach ($userlist as $record) { 
									?>
								<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-on-hold order">
									<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" data-title="ID">
									<?php 
										echo esc_html($record->ID) ;
									?>
									</td>

									<td data-title="Name">
									<?php 
									if (isset($permission['company_user_edit'])) {
										$url = esc_url( add_query_arg( array('id'=>$record->ID), wc_get_endpoint_url( 'aw-ca-adduser')));
										echo '<a href="' . wp_kses($url, wp_kses_allowed_html('post')) . '">' . esc_html($record->data->display_name) . '</a>';
									} else {
										echo esc_html($record->data->display_name);
									}
									?>
									</td>

									<td data-title="Email">
									<?php 
										echo esc_html($record->data->user_email);
									?>
									</td>

									<td data-title="Phone">
									<?php
										echo esc_html(get_user_meta($record->ID, 'billing_phone', true), wp_kses_allowed_html('post'));
									?>
									</td>

									<td data-title="User Role">
									<?php 
										$user_roles = '';
										$user_meta 	= get_userdata($record->ID);
									if (!empty($user_meta)) {
										$user_roles = $user_meta->roles[0];	
									}
										echo esc_html(ucfirst(str_replace('_', ' ', $user_roles)), wp_kses_allowed_html('post'));
									?>
									</td>

									<td data-title="Job Position">
									<?php 
										echo esc_html(get_user_meta($record->ID, 'job_position', true), wp_kses_allowed_html('post'));
									?>
									</td>
									<td data-title="Status">
									<?php 
										echo esc_html(get_user_meta($user_id, 'user_status', true), wp_kses_allowed_html('post'));
									?>
									</td>
								</tr>
								<?php	
								}
							}
							?>
					</tbody>
			</table>
		</div>
		<?php 
		if ( $userlist > 1  && get_option( 'posts_per_page' ) ) : 
			?>
				<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
				<?php 
					
				if ( 1 !== intval($current_page) ) :
					$current_page_next = $current_page - 1;
					if (isset($_GET['paged'])) {
						$premalink 	= esc_url(add_query_arg('paged', $current_page_next));
					} else {
						$premalink 	= esc_url( wc_get_endpoint_url( 'aw-ca-users', $current_page_next));
					}

					?>
						<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url($premalink);//echo esc_url( wc_get_endpoint_url( 'aw-cc-mycredit', '', $premalink  ) ); ?>"><?php esc_html_e( 'Previous', 'woocommerce' ); ?></a>
					<?php 
					endif; 

				if ( intval($pages ) !== intval($current_page) ) :
					?>
						<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url($nexturl); ?>"><?php esc_html_e( 'Next', 'woocommerce' ); ?></a>
					<?php endif; ?>
				</div>
			<?php 
			endif; 
	}

	public static function aw_ca_rolespermissions_endpoint_title() {
		global $wp;
		$parts = explode('/', $wp->request );
		$title = __( 'Roles and Permissions', 'woocommerce' );
		
		if (!empty($parts)) {
			if (is_numeric(end($parts))) {
				$current_page = end($parts);
				/* translators: current page number  */
				$title = sprintf( __( 'Roles and Permissions (page %d)', 'woocommerce' ), intval( $current_page ) );
			} else {
				if (isset($_GET['paged'])) {
				$current_page = (int) $_GET['paged'];
				/* translators: current page number  */
				$title = sprintf( __( 'Roles and Permissions (page %d)', 'woocommerce' ), intval( $current_page ) );
				return $title;
				}
			}
		} else {
				$title = __( 'Roles and Permissions', 'woocommerce' );
				return $title;
		}
		return $title;
	}

	public static function aw_ca_rolespermissions_list() {
		$structure 			= get_option( 'permalink_structure' );
		$loggedin_user_id 	= get_current_user_id();
		$permission 		= aw_ca_get_role_permission_by_company_row($loggedin_user_id);
		$company_id 		= get_user_meta( $loggedin_user_id, 'company_id', true);
		$rolespermissions 	= aw_ca_get_allrole_permission($company_id);
		$total_records  	= count($rolespermissions);
		$record_per_page	= get_option('posts_per_page');
		$pages 				= ceil($total_records/$record_per_page);
		$nexturl 			= '';
		$current_page 		= 1;
		if (isset($_GET['paged'])) {
			$current_page = (int) $_GET['paged'];
		}
		if (!isset($permission['company_roles_viewall'])) {
			echo 'Not allowed to view this page';
			return;
		}
		switch ($structure) {
			case '/%year%/%monthnum%/%day%/%postname%/':
			case '/%year%/%monthnum%/%postname%/':
			case '/archives/%post_id%':
			case '/%postname%/':
				global $wp;
				$parts = explode('/', $wp->request );
				if (is_numeric(end($parts))) {
					$current_page = end($parts);
				}
				$nexturl = esc_url( wc_get_endpoint_url( 'aw-ca-rolespermissions', $current_page + 1 ) );
				break;
			default:
				$nexturl =  esc_url(add_query_arg('paged', $current_page + 1));	
		}

		$offset 			= ( $current_page - 1 ) * $record_per_page;	
		$rolespermissions 	= aw_ca_get_allrole_permission($company_id, $record_per_page, $offset);
		$premalink 			= esc_url( wc_get_endpoint_url( 'aw-ca-addrolespermissions'));
		?>
			<div class="wrap">
				<div class="page-title">
					<h1 class="wp-heading-inline">Roles</h1>
					<?php if (isset($permission['company_roles_addnew'])) { ?>
						<div class="aw-ca-btn-action"><a href="<?php echo wp_kses($premalink, wp_kses_allowed_html('post')); ?>" class="woocommerce-button button view">Add New Role</a></div>
					<?php } ?>
				</div>
				<div class="aw-ca-table-responsive-grid">		
					<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
						<thead>
							<tr>
								<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number">
									<span class="nobr">ID</span>
								</th>
								<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-date">
									<span class="nobr">Name</span></th>
								<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-status">
									<span class="nobr">Users</span>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php 
							if (!empty($rolespermissions)) {
								foreach ($rolespermissions as $record) { 
									?>
								<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-on-hold order">

									<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" data-title="ID">
										<?php echo esc_html($record->id); ?>
									</td>

									<td data-title="Name">
									<?php 
									if (isset($permission['company_roles_edit'])) {
										$premalink = esc_url( add_query_arg( array('id'=>$record->id), wc_get_endpoint_url( 'aw-ca-addrolespermissions')));
										$text = esc_html(str_replace('_', ' ', $record->role_name)); 
										echo "<a href='" . wp_kses($premalink, wp_kses_allowed_html('post')) . "'>" . wp_kses($text, wp_kses_allowed_html('post')) . '</a>';	
									} else {
										echo esc_html(str_replace('_', ' ', $record->role_name)); 
									}
									?>
									</td>

									<td data-title="Email">
										<?php 
											echo count( get_users( array( 'role' => $record->role_name,'meta_query'   => array( array('key'=>'company_id','value'=>$record->company_id) ) ) ) );
										?>
									</td>
								</tr>
								<?php	
								}
							} else { 
								?>
								<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-on-hold order">
									<td colspan="3">No record Fond</td>
								</tr>
								<?php 
							}
							?>
						</tbody>
					</table>
				</div>

			<?php 
			if ( $pages > 1  && get_option( 'posts_per_page' ) ) : 
				?>
				<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
					<?php 
					if ( 1 !== intval($current_page) ) :
						$current_page_next = $current_page - 1;
						if (isset($_GET['paged'])) {
							$premalink 	= esc_url(add_query_arg('paged', $current_page_next));
						} else {
							$premalink 	= esc_url( wc_get_endpoint_url( 'aw-ca-rolespermissions', $current_page_next));
						}

						?>
						<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url($premalink);//echo esc_url( wc_get_endpoint_url( 'aw-cc-mycredit', '', $premalink  ) ); ?>"><?php esc_html_e( 'Previous', 'woocommerce' ); ?></a>
					<?php 
					endif; 

					if ( intval($pages ) !== intval($current_page) ) :
						?>
						<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url($nexturl); ?>"><?php esc_html_e( 'Next', 'woocommerce' ); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			</div>
		 <?php
	}

	public static function aw_ca_addrolespermissions_endpoint_title() {
		global $wp;
		$parts = explode('/', $wp->request );
		$title = __( 'Roles and Permissions', 'woocommerce' );
		
		if (!empty($parts)) {
			if (is_numeric(end($parts))) {
				$current_page = end($parts);
				/* translators: current page number  */
				$title = sprintf( __( 'Roles and Permissions (page %d)', 'woocommerce' ), intval( $current_page ) );
			} else {
				if (isset($_GET['paged'])) {
				$current_page = (int) $_GET['paged'];
				/* translators: current page number  */
				$title = sprintf( __( 'Roles and Permissions (page %d)', 'woocommerce' ), intval( $current_page ) );
				return $title;
				}
			}
		} else {
				$title = __( 'Roles and Permissions', 'woocommerce' );
				return $title;
		}
		return $title;
	}

	public static function aw_ca_add_new_rolespermissions() {
		global $wpdb; 
		$db_role_permission_table = $wpdb->prefix . 'aw_ca_role_permission';
		$loggedin_user_id 	= get_current_user_id();
		$company_id 		= get_user_meta( $loggedin_user_id, 'company_id', true );
		$permission 		= aw_ca_get_role_permission_by_company_row($loggedin_user_id);		
		$role_name 			= '';
		$order_limit 		= '';
		$role_name 			= '';
		$order_limit 		= '';
		$id 				= '';
		$post 				= array();
		$rolepermission		= array();
		$allpermissions 	= array('all'=>'','company_info'=>'','company_info_view'=>'' ,'company_info_edit'=>'','company_user'=>'','company_user_viewall'=>'','company_user_addnew'=>'','company_user_edit'=>'','company_user_userstatus'=>'','company_roles'=>'','company_roles_viewall'=>'','company_roles_addnew'=>'','company_roles_edit'=>'','orders'=>'','orders_viewall'=>'','credit_limit'=>'','credit_limit_view'=>'','transaction_viewall'=>'');

		$rolepermissions = array('all','company_info','company_info_view','company_info_edit','company_user','company_user_viewall','company_user_addnew','company_user_edit','company_user_userstatus','company_roles','company_roles_viewall','company_roles_addnew','company_roles_edit','orders','orders_viewall','credit_limit','credit_limit_view','transaction_viewall');
		$post['permissions'] = '';

		if (!isset($permission['company_roles_viewall'])) {
			echo 'Not allowed to view this page';
			return;
		}
		if (!isset($permission['company_roles_addnew'])) {
			echo 'Not allowed to view this page';
			return;
		}
		if (!isset($permission['company_roles_edit'])) {
			echo 'Not allowed to view this page';
			return;
		}

		if (isset($_POST['role_name'])) {
			$post['role_name'] = sanitize_text_field($_POST['role_name']);
			$post['company_id']= $company_id;
		}
		if (isset($_POST['checkbox'])) {
			foreach ($rolepermissions as $value) {
				if ( isset($_POST['checkbox']) && isset($_POST['checkbox'][$value]) ) {
					$rolepermission[$value] = sanitize_text_field($_POST['checkbox'][$value]);
				}
			}
			$post['permissions'] = maybe_serialize($rolepermission);
		}
		if (isset($_POST['ca_role_permission_submit'])) {
			if (isset($_POST['aw_ca_frontend_nonce'])) {
				$aw_ca_frontend_nonce = sanitize_text_field($_POST['aw_ca_frontend_nonce']);
			}

			if ( !wp_verify_nonce( $aw_ca_frontend_nonce, 'save_role_permission_form_data' )) {
				wp_die('Our Site is protected');
			}
			$wpdb->insert( $db_role_permission_table , $post );
			self::aw_ca_front_add_flash_notice( __('Role permission created successfully'), 'message', true );	
		}
		
		if (isset($_POST['ca_role_permission_update'])) {
			if (isset($_POST['aw_ca_frontend_nonce'])) {
				$aw_ca_frontend_nonce = sanitize_text_field($_POST['aw_ca_frontend_nonce']);
			}

			if ( !wp_verify_nonce( $aw_ca_frontend_nonce, 'save_role_permission_form_data' )) {
				wp_die('Our Site is protected');
			}

			if (isset($_POST['id'])) {
				$id = sanitize_text_field($_POST['id']);	
			}
			$result = $wpdb->update($db_role_permission_table, $post, array('id'=>$id));

			self::aw_ca_front_add_flash_notice( __('Role permission updated successfully'), 'message', true );	
		}

		if (isset($_GET['id'])) {
			$id			= sanitize_text_field($_GET['id']);
			$details 	= aw_ca_get_role_permission_row( $id );
			if (!empty($details)) {
				$role_name 		= $details['role_name'];
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
		 
		$notice = get_option( 'ca_front_flash_notices', array() );
		if ( ! empty( $notice ) ) {
				printf('<div class="woocommerce-%1$s %2$s"><p>%3$s</p></div>',
					wp_kses($notice['type'], wp_kses_allowed_html('post')),
					wp_kses($notice['dismissible'], wp_kses_allowed_html('post')),
					wp_kses($notice['notice'], wp_kses_allowed_html('post'))
				);
			delete_option( 'ca_front_flash_notices', array() );
		} 

		?>
		<form action="" method="post" onsubmit="return formvalidate(event)" class="aw-ca-role-permission-new">
			<?php wp_nonce_field( 'save_role_permission_form_data', 'aw_ca_frontend_nonce' ); ?>
			<fieldset>
				<legend>New Role</legend>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="password_current">Role Name *</label>
					<input type="text" class="woocommerce-Input input-text txt_required" name="role_name" id="role_name" value="<?php echo wp_kses($role_name, wp_kses_allowed_html('post')); ?>" autocomplete="off">
					<span class="error_role_name"></span>
				</p>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="user_email"> Permissions to the Role </label>
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
							</ul>
						</li>	
					</ul>
				</p>
			</fieldset>
			<div class="clear"></div>
		<p>
			<?php 

			if ( '' != $id) { 
				?>
				<input type="hidden" name="id" value="
				<?php 
				echo wp_kses($id, wp_kses_allowed_html('post'));
				?>
				">
				<button type="submit" class="woocommerce-Button button" name="ca_role_permission_update" value="Update">Update</button>					
			<?php } else { ?>
				<button type="submit" class="woocommerce-Button button" name="ca_role_permission_submit" value="Save">Save</button>					
			<?php } ?>
		</p>
		</form>		
		<?php 
	}


}
?>
