<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

$Aw_ca_newcompanyform = new AW_CA_NewCompanyForm();

class AW_CA_NewCompanyForm {
	public function __construct() {
		add_shortcode('aw_ca_newcompanyform' , array('AW_CA_NewCompanyForm','aw_ca_new_company_form'));
		//add_action('wp_ajax_aw_ca_save_new_company_details', array(get_called_class(),'aw_ca_save_new_company_details'));
	}

 
	public static function aw_ca_new_company_form() {
		global $wpdb; 
		$class 				= '';
		$message 			= '';
		$default_sales_rep 	= '';
		$searilized_form_setting 	= get_option('aw_ca_default_form', true);
		$company_form_setting 		= maybe_unserialize($searilized_form_setting);
		$sales_representative 		= get_option('aw_ca_groupsales_representative');
		
		
		

		if (isset($_POST['aw_ca_new_company_submit'])) {
			global $wpdb, $wp; 
			$db_company_table = $wpdb->prefix . 'aw_ca_company_information'; 
			//$url =  home_url($wp->request).'/aw_ca_new_company';//admin_url() . 'admin.php?page=company-accounts-configuration&tab=aw-ca-emails';

			if (isset($_POST['aw_ca_frontend_nonce'])) {
				$aw_ca_frontend_nonce = sanitize_text_field($_POST['aw_ca_frontend_nonce']);
			}

			if ( !wp_verify_nonce( $aw_ca_frontend_nonce, 'save_new_company_details' )) {
				wp_die('Our Site is protected');
			}
		
			$post_array = array();
			$company_form_data 	= array('company_name','company_email','company_legal_name','tax_vat_id','reseller_id','company_street','city','country','state','zip','company_phone');
				 
			if (!empty($sales_representative)) {
				$default_record			= maybe_unserialize($sales_representative);	
				if (count($default_record)>0) {
					$default_sales_rep 	= $default_record[0]['aw_ca_salesrepresentative'];	
					$post_array['sales_representative'] = $default_sales_rep;
				}
			}	 
			foreach ($company_form_data as $value) {
				if (isset($_POST['formdata'][$value])) {
					$post_array[$value] = sanitize_text_field($_POST['formdata'][$value]);	
				}
			}
				//error_log(print_r($_POST,1));die;
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

			if (isset($_POST['formdata']['job_position'])) {
				$admin_job_position = sanitize_text_field($_POST['formdata']['job_position']);
			}
			if (isset($_POST['formdata']['user_phone'])) {
				$phone_number 		= sanitize_text_field($_POST['formdata']['user_phone']);
			}
				 
			if ( !empty( $email) && !empty( $first_name) && !empty( $last_name) ) {
					
				if ( username_exists( $email ) || email_exists( $email ) ) {
					update_option('CA_flash_notices', 1);
					$post_array['first_name'] 			= $first_name;
					$post_array['last_name'] 			= $last_name;
					$post_array['user_email']			= $email;
					$post_array['job_position']			= $admin_job_position;
					$post_array['user_phone'] 			= $phone_number;	
					
					$class 		= 'woocommerce-error';
					$message 	= 'Company admin already register with this email';				 
				} else {
					$user_id = aw_ca_register_user( $email, $first_name, $last_name, 'company_admin', 'frontend' ); 
					update_user_meta($user_id, 'job_position', $admin_job_position);
					update_user_meta($user_id, 'phone_number', $phone_number);
					$post_array['company_admin_id'] = $user_id;
					$wpdb->insert( $db_company_table , $post_array );
					$company_id = $wpdb->insert_id;
					update_user_meta($user_id, 'company_id', $company_id);
					$company = aw_ca_get_company_by_id($company_id);
					update_user_meta($user_id, 'company_name', $company->company_name);	
					$post_array = array();
					$class 		= 'woocommerce-message';
					$message 	= 'New company added successfully';	
					update_option('CA_flash_notices', 1);
				}
			}
		}

		$notice = get_option( 'CA_flash_notices');
		if ( ! empty( $notice ) ) {
			echo '<p class="' . wp_kses($class , wp_kses_allowed_html('post')) . '">' . wp_kses($message, wp_kses_allowed_html('post')) . '</p>';
			delete_option( 'CA_flash_notices');
		}
		echo '<div class="wrap"><form method="post" id="aw_ca_company_form" enctype="multipart/form-data" onsubmit="return formvalidate(event)"><input type="hidden" name="action" value="aw_ca_save_new_company_details">';
		wp_nonce_field( 'save_new_company_details', 'aw_ca_frontend_nonce' );
		if (!empty($company_form_setting)) {
			foreach ($company_form_setting as $formkey=> $currentform) {
				$tbody 		= '';
				$formkey	= str_replace('Customization', '' , $formkey );
				$formkey 	= str_replace('_', ' ', $formkey);
				echo '<h3>' . wp_kses($formkey, wp_kses_allowed_html('post')) . '</h3>';
				echo '<table id="companyinoform"  class="widefat striped" ><tbody id="companyino-list" class="main-list">';
				foreach ($currentform as $key=>$value) {
					$exist_value 		= '';
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
					$id = str_replace(' ', '_' , str_replace('/', '-', $value['name']));
					if (isset($post_array[$value['name']])) {
						$exist_value = $post_array[$value['name']];
					}
					if ($value['enable']) {
						$tbody .= '<tr class="tr-' . $key . '">
									<td><label>' . $value['label'] . '</label>' . $mandatory . '
										<input type="text" id="' . $id . '" name="formdata[' . str_replace(' ', '_', trim($value['name'])) . ']" value="' . $exist_value . '"  class="' . $class . '" ' . $extracheck . '/>';
						$exist_value= '';
					}					
					$tbody .= 		'<p><span class="error_' . $id . '"></span></p>';	
					$tbody .= 		'</td>
								</tr>' ;
				}
				echo wp_kses($tbody, wp_kses_allowed_html('post'));
				echo '</tbody></table>';
			}
		}
		echo '<div class="submit">
					<input type="submit" class="button button-primary" value="Submit" name="aw_ca_new_company_submit"/>	
				</div>';
		echo '</form></div>';
	}

	/*public static function aw_ca_save_new_company_details() {
		global $wpdb, $wp; 
		$db_company_table = $wpdb->prefix . 'aw_ca_company_information'; 
		$url =  home_url($wp->request) . '/aw_ca_new_company';//admin_url() . 'admin.php?page=company-accounts-configuration&tab=aw-ca-emails';
		 
		check_ajax_referer( 'ajax_securiy_nonce', 'security' );
		 
		$post_array = array();
		$company_form_data 	= array('company_name','company_email','company_legal_name','tax_vat_id','reseller_id','company_street','city','country','state','zip','company_phone');
		 
		if (!empty($_POST)) {
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
			if (isset($_POST['formdata']['job_position'])) {
				$admin_job_position = sanitize_text_field($_POST['formdata']['job_position']);
			}
			if (isset($_POST['formdata']['job_position'])) {
				$phone_number 		= sanitize_text_field($_POST['formdata']['job_position']);
			}
			//$website 			= sanitize_text_field($_POST['formdata']['website']);
			//$send_mail_from		= sanitize_text_field($_POST['formdata']['send_mail_from']);
			//$customer_group 	= sanitize_text_field($_POST['formdata']['customer_group']); 

		}

		if (isset($_POST['aw_ca_new_company_submit'])) {
			error_log(print_r($_POST,1));die;
			$user_id = aw_ca_register_user( $email, $first_name, $last_name, 'company_admin' ); 
			$user_meta 	= get_userdata($user_id);
			$user_roles = $user_meta->roles;
			error_log('=>'.print_r($user_roles));
			if(!empty($user_roles) && 'administrator' === $user_roles[0]) {
				?>
					<script>
						alert('not allowed');
						return false;
					</script>
				<?php 

			} else {
				update_user_meta($user_id, 'job_position', $admin_job_position);
				update_user_meta($user_id, 'phone_number', $phone_number);
				update_user_meta($user_id,	'customer_group','');


				$post_array['company_admin_id'] = $user_id;

				$wpdb->insert( $db_company_table , $post_array );
				$company_id = $wpdb->insert_id;
				update_user_meta($user_id, 'company_id', $company_id);
				$company 	= aw_ca_get_company_by_id($company_id);
				update_user_meta($user_id, 'company_name', $company->company_name);
				wp_redirect($url);
			}
			
			//if($company_id) {
				//self::aw_company_accounts_add_flash_notice( __('New company saved successfully'), 'success', true );
			//} 
			
		}
		wp_redirect($url);
	}
*/}


