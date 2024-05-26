<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
$AwAdvancedReviewAdmin = new AwAdvancedReviewAdmin();

class AwAdvancedReviewAdmin {
	public function __construct() {

		add_filter('set-screen-option', array(get_called_class(), 'aw_ar_advanced_review_set_screen'), 10, 3);

		add_action('wp_ajax_aw_ar_review_image_delete', array(get_called_class(),'aw_ar_review_image_delete'));
		add_action('wp_ajax_nopriv_aw_ar_review_image_delete', array(get_called_class(),'aw_ar_review_image_delete'));

		/* Add Custome Metabox for Q&A Detail*/
		add_action('admin_head', array('AwAdvancedReviewAdmin','aw_ar_add_advanced_review_meta_box'));
		add_action('comment_save_pre', array('AwAdvancedReviewAdmin','aw_ar_save_comment_meta_box'));

		/* Add Custom menus admin side*/
		if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
			add_action('admin_menu', array(get_called_class(),'aw_ar_advanced_review_menu'));
			
			add_action('admin_post_advancedreview_save_setting_form', array(get_called_class(),'aw_ar_advanced_review_save_setting_form'));
			add_action('admin_post_aw_ar_save_email_templates_setting', array(get_called_class(),'aw_ar_save_email_templates_setting'));
		}

		add_filter( 'comment_status_links', array(get_called_class(),'aw_advanced_review_comment_status_link'));
		add_filter( 'manage_edit-comments_columns', array(get_called_class(),'aw_advanced_review_change_column_name' ));
		add_filter( 'comment_edit_pre', array(get_called_class(),'aw_advanced_review_append_starrating_comment_column' ));

		add_filter( 'comment_row_actions' , array(get_called_class(),'aw_ar_change_row_action_url_querysting' ));

		add_action('transition_comment_status', array(get_called_class(),'aw_ar_approve_comment_callback'), 10, 3);
	}
	
	public static function aw_ar_self_deactivate_notice() {
		/** Display an error message when WooComemrce Plugin is missing **/
		?>
		<div class="notice notice-error">
			<p>Please install and activate WooComemrce plugin before activating Advanced Review By Aheadworks Plugin.</p>
		</div>
		<?php
	}

	public static function aw_ar_advanced_review_add_flash_notice( $notice = '', $type = 'warning', $dismissible = true ) { 
		// Here we return the notices saved on our option, if there are not notices, then an empty array is returned
		$notices = get_option( 'advanced_review_flash_notices', array() );
		$dismissible_text = ( $dismissible ) ? 'is-dismissible' : '';

		$notices = array(
						'notice' => $notice, 
						'type' => $type, 
						'dismissible' => $dismissible_text
					); 	
		update_option('advanced_review_flash_notices', $notices );
	}

	public static function aw_ar_advanced_review_set_screen( $status, $option, $value) {
		if ('review_per_page' == $option) {
			return $value;
		}	
		return $status;
	}

	public static function aw_ar_advanced_review_menu() {
		global $submenu;
		add_menu_page('Advanced Review', 'Advanced Review', 'edit_posts', 'advanced-review', array('AwAdvancedReviewAdmin','aw_ar_advanced_review_list') , plugins_url('advanced-reviews-by-aheadworks/admin/images/aw_ar_icon.png'), 25);
		$hook = add_submenu_page('advanced-review', __('Reviews'), __('Reviews'), 'manage_options', 'edit-comments.php?comment_type=review' );
		add_submenu_page('advanced-review', __('Settings'), __('Settings'), 'manage_options', 'advanced-review-setting', array('AwAdvancedReviewAdmin','aw_ar_advanced_review_setting') );
		add_action( "load-$hook", array('AwAdvancedReviewAdmin','aw_ar_advanced_review_screen_option'));

		add_submenu_page('', __('Advanced Review Emails'), '', 'manage_options', 'advanced-review-emails', array('AwAdvancedReviewAdmin' , 'aw_ar_admin_email_setting'));
		unset($submenu['advanced-review'][0]);
		
	}

	public static function aw_ar_advanced_review_screen_option() {
		$option = 	'per_page';
		$args 	= 	array(
						'label' => 'Number of items per page:',
						'default' => 20,
						'option' => 'review_per_page'
					);
		add_screen_option( $option, $args );
		$table = new AwAdvancedReviewListAdmin();
	}

	public static function aw_ar_advanced_review_setting() {
		$aw_ar_setting_helpful_enable 	= '';
		$aw_ar_setting_review_enable	= '';
		$aw_ar_admin_caption			= '';
		$aw_ar_max_filesize				= '';
		$aw_ar_reviewpage_endppoint		= '';	
		$aw_ar_allowfile_extensions		= '';														
		$aw_ar_meta_description			= '';

		if (get_option('aw_ar_enable_pronandcons')) {
			$aw_ar_enable_pronandcons = get_option('aw_ar_enable_pronandcons');
			$noproncons = '';
			$yesproncons = '';
			if ('no' == $aw_ar_enable_pronandcons) {
				$noproncons 	= 'selected = selected';
			}
			if ('yes' == $aw_ar_enable_pronandcons) {
				$yesproncons 	= 'selected = selected';
			}
		}
		if (get_option('aw_ar_enable_termcondition')) {
			$aw_ar_enable_termcondition = get_option('aw_ar_enable_termcondition');
			$notermcondition 	= '';
			$yestermcondition 	= '';
			if ('no' == $aw_ar_enable_termcondition) {
				$notermcondition	= 'selected = selected';
				$enabletermclass	= 'aw_ar_li_none';
			}
			if ('yes' == $aw_ar_enable_termcondition) {
				$yestermcondition 	= 'selected = selected';
				$enabletermclass	= 'aw_ar_li_block';
			}
		}
		if (get_option('aw_ar_whoaccept')) {
			$aw_ar_whoaccept = get_option('aw_ar_whoaccept');
			$guestaccept 	= '';
			$everyoneaccept	= '';
			if ('guest' == $aw_ar_whoaccept) {
				$guestaccept = 'selected = selected';
			}
			if ('every' == $aw_ar_whoaccept) {
				$everyoneaccept = 'selected = selected';
			}			
		}
		if (get_option('aw_ar_admin_caption')) {
			$aw_ar_admin_caption = get_option('aw_ar_admin_caption');
		}
		if (get_option('aw_ar_isattach_file')) {
			$aw_ar_isattach_file = get_option('aw_ar_isattach_file');
			$noattachfile 	= '';
			$yesattachfile 	= '';
			if ('no' == $aw_ar_isattach_file) {
				$noattachfile = 'selected = selected';
				$displayclass	= 'aw_ar_li_none';
			}
			if ('yes' == $aw_ar_isattach_file) {
				$yesattachfile  = 'selected = selected';
				$displayclass	= 'aw_ar_li_block';
			}
		}
		if (get_option('aw_ar_max_filesize')) {
			$aw_ar_max_filesize = get_option('aw_ar_max_filesize');
		}
		if (get_option('aw_ar_reviewpage_endppoint')) {
			$aw_ar_reviewpage_endppoint = get_option('aw_ar_reviewpage_endppoint');
		}												
		if (get_option('aw_ar_allowfile_extensions')) {
			$aw_ar_allowfile_extensions = get_option('aw_ar_allowfile_extensions');
		}
		if (get_option('aw_ar_meta_description')) {
			$aw_ar_meta_description = get_option('aw_ar_meta_description');
		}


		$notice = get_option( 'advanced_review_flash_notices', array() );
		if ( ! empty( $notice ) ) {
				printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
					wp_kses($notice['type'], wp_kses_allowed_html('post')),
					wp_kses($notice['dismissible'], wp_kses_allowed_html('post')),
					wp_kses($notice['notice'], wp_kses_allowed_html('post'))
				);
			delete_option( 'advanced_review_flash_notices', array() );
		}
		$email_template = aw_ar_get_email_template_setting_results();
		?>
		<div class="tab-grid-wrapper">
			<div class="spw-rw clearfix">
				<div class="panel-box advanced-review-setting">
					<div class="page-title">
						<h1>
							<?php echo 'Advanced Review'; ?>
						</h1>
					</div>
					<div class="panel-body">
						<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" id="aw_ar_setting_form" enctype="multipart/form-data">
									<?php wp_nonce_field( 'save_advanced_review_setting', 'awaradvancedreview_admin_nonce' ); ?>
									<input type="hidden" name="action" value="advancedreview_save_setting_form">
							<div class="tab">
								<button class="tablinks active" onclick="openTab(event, 'aw_ar_general-setting-tab',this)"><?php echo 'General Settings'; ?></button>
								<button class="tablinks" onclick="openTab(event, 'aw_ar_email-setting-tab',this)"><?php echo 'Emails'; ?></button>
							</div>
							<!-- Tab Start -->
							<div class="tabcontent aw-ar-general-set" id="aw_ar_general-setting-tab" style="display:block;">
									<ul>
										<?php 
										/*
										<li>
											<label>Guests must specify their email to submit review </label>
											<div class="control">
											 <select name="aw_ar_guestreview">
												 <option value="no" <?php echo wp_kses($noguestview, wp_kses_allowed_html('post')); ?>>No</option>
												 <option value="optional" <?php echo wp_kses($optionalview, wp_kses_allowed_html('post')); ?>>Optional</option>
												 <option value="required" <?php echo wp_kses($requiredview, wp_kses_allowed_html('post')); ?>>Required</option>
											 </select>
											</div>
										</li>
										*/ 
										?>
										<li>
											<label>Enable Pros & Cons</label>
											<div class="control">
											 <select name="aw_ar_enable_pronandcons">
												 <option value="yes" <?php echo wp_kses($yesproncons, wp_kses_allowed_html('post')); ?>>Yes</option>
												 <option value="no" <?php echo wp_kses($noproncons, wp_kses_allowed_html('post')); ?>>No</option>
											 </select>
											 <p>Adds two extra fields: "Advantages" and "Disadvantages"</p>
											</div>
										</li>
										
											<li>
												<label>Enable Terms and Conditions </label>
												<div class="control">
												 <select name="aw_ar_enable_termcondition" onchange="handleSelectChange(this.value,'aw_ar_displaytermli')">
													 <option value="yes" <?php echo wp_kses($yestermcondition, wp_kses_allowed_html('post')); ?>>Yes</option>
													 <option value="no" <?php echo wp_kses($notermcondition, wp_kses_allowed_html('post')); ?>>No</option>
												 </select>
												</div>
												
											</li>
										
										<div id="aw_ar_displaytermli" class="<?php echo wp_kses($enabletermclass, wp_kses_allowed_html('post')); ?>">
										<li>
											<label>Who must accept Terms and Conditions </label>
											<div class="control">
											 <select name="aw_ar_whoaccept">
												 <option value="guest" <?php echo wp_kses($guestaccept, wp_kses_allowed_html('post')); ?>>Guest only</option>
												 <option value="everyone" <?php echo wp_kses($everyoneaccept, wp_kses_allowed_html('post')); ?>>Everyone</option>
											 </select>
											</div>
										</li>
										</div>
										<li>
											<label>Admin Comment Caption</label>
											<div class="control">
												<input type="text" name="aw_ar_admin_caption" class="aw_ar_admin_caption" value="<?php echo wp_kses($aw_ar_admin_caption, wp_kses_allowed_html('post')); ?>" />
											</div>
										</li>

										<li>
											<label>Request Path To Page With All Reviews</label>
											<div class="control">
												<input type="text" name="aw_ar_reviewpage_endppoint" class="aw_ar_reviewpage_endppoint" value="<?php echo wp_kses( sanitize_title($aw_ar_reviewpage_endppoint), wp_kses_allowed_html('post')); ?>" />
												<p>
													<span class="aw_ar_reviewpage_endppoint_msg"></span>
												</p>
												<p>For example, "reviews" makes the page accessible at domain.com/reviews/</p>

											</div>
										</li>
	
										<li>
											<label>Meta Description For All Reviews Page</label>
											<div class="control">
												<textarea  name="aw_ar_meta_description" cols="19" rows="2"><?php echo wp_kses($aw_ar_meta_description, wp_kses_allowed_html('post')); ?></textarea>
											</div>
										</li>
									</ul>
									<hr/>
									<p><b>File Attachments</b></p>
									<ul>
										<li>
											<label>Allow Customer to Attach Files</label>
											<div class="control">
												<select id="aw_ar_isattach_file" name="aw_ar_isattach_file" onchange="handleSelectChange(this.value,'aw_ar_displayimageli')">
													<option value="yes" <?php echo wp_kses($yesattachfile, wp_kses_allowed_html('post')); ?>>Yes</option>
													<option value="no" <?php echo wp_kses($noattachfile, wp_kses_allowed_html('post')); ?>>No</option>
												</select>	
											</div>
										</li>
										<div id="aw_ar_displayimageli" class="<?php echo wp_kses($displayclass, wp_kses_allowed_html('post')); ?>">
										<li>
											
												<label>Max Upload File Size (Mb)</label>
												<div class="control">
													<input type="text" name="aw_ar_max_filesize" class="aw_ar_max_filesize" value="<?php echo wp_kses($aw_ar_max_filesize, wp_kses_allowed_html('post')); ?>" onkeypress="return aw_ar_checkIt(event,false)"/>
													<p><span class="aw_ar_max_filesize_msg"></span></p>
												</div>
											
										</li>
										<li>
											<!-- <div class="< ?php echo $displayclass;?>"> -->
												<label>Allow File Extensions</label>
												<div class="control">
													<input type="text" name="aw_ar_allowfile_extensions" class="aw_ar_allowfile_extensions" value="<?php echo wp_kses($aw_ar_allowfile_extensions, wp_kses_allowed_html('post')); ?>"/>
														<p><span class="aw_ar_allowfile_extensions_msg"></span></p>

													<p>Example: jpg,png</p>
												</div>
											<!-- </div> -->
										</li>
										</div>
									</ul>
									<div class="submit">
										<input type="submit" class="button button-primary" value="Save" name="setting_ar_submit" onclick="return aw_ar_setting_submit(event)" />	
									</div>
							</div>

							 <div class="tabcontent ar-email-set" id="aw_ar_email-setting-tab" style="display:none;">
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
															<a href="<?php echo esc_url(admin_url('admin.php?page=advanced-review-emails&ID=' . $template->id)) ; ?>"><?php echo wp_kses($template->email, wp_kses_allowed_html('post')); ?></a>
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
															<a class="button" href="<?php echo esc_url(admin_url('admin.php?page=advanced-review-emails&ID=' . $template->id)) ; ?>">
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

	public static function aw_ar_advanced_review_save_setting_form() {
		$original_text = '';
		$modified_text = '';
		$url =  admin_url() . 'admin.php?page=advanced-review-setting';
		if (isset($_POST['awaradvancedreview_admin_nonce'])) {
			$awaradvancedreview_admin_nonce = sanitize_text_field($_POST['awaradvancedreview_admin_nonce']);
		}

		if ( !wp_verify_nonce( $awaradvancedreview_admin_nonce, 'save_advanced_review_setting' )) {
			wp_die('Our Site is protected');
		}

		if (isset($_POST['setting_ar_submit'])) {
			if (isset($_POST['aw_ar_enable_pronandcons'])) {
				$aw_ar_enable_pronandcons = sanitize_text_field($_POST['aw_ar_enable_pronandcons']);
				update_option('aw_ar_enable_pronandcons', $aw_ar_enable_pronandcons);
			}

			if (isset($_POST['aw_ar_enable_termcondition'])) {
				$aw_ar_enable_termcondition = sanitize_text_field($_POST['aw_ar_enable_termcondition']);
				update_option('aw_ar_enable_termcondition', $aw_ar_enable_termcondition);
			} else {
				update_option('aw_ar_enable_termcondition', '');
			}		 
		
			if (isset($_POST['aw_ar_whoaccept'])) {
				$aw_ar_whoaccept = sanitize_text_field($_POST['aw_ar_whoaccept']);
				update_option('aw_ar_whoaccept', $aw_ar_whoaccept);
			}			

			if (isset($_POST['aw_ar_admin_caption']) && '' != sanitize_text_field($_POST['aw_ar_admin_caption'])) {
				update_option('aw_ar_admin_caption', sanitize_text_field($_POST['aw_ar_admin_caption']));
			}
			if (isset($_POST['aw_ar_isattach_file']) && '' != sanitize_text_field($_POST['aw_ar_isattach_file'])) {
				update_option('aw_ar_isattach_file', sanitize_text_field($_POST['aw_ar_isattach_file']));
			}
			if (isset($_POST['aw_ar_max_filesize'])) {
				update_option('aw_ar_max_filesize', sanitize_text_field($_POST['aw_ar_max_filesize']));
			} 			
			if (isset($_POST['aw_ar_reviewpage_endppoint'])) {
				
				update_option('aw_ar_reviewpage_endppoint', sanitize_text_field($_POST['aw_ar_reviewpage_endppoint']));
				$post_id 	= -1;
				$author_id 	= get_current_user_id();
				$slug 		= sanitize_title($_POST['aw_ar_reviewpage_endppoint']);
				$title 		= 'Reviews';
				// Check if page exists, if not create it
				$existing_id = get_option('aw_ar_allreviewpage_id');
				wp_delete_post($existing_id);
				$menu = wp_insert_term('AW-AR-REVIEW', 'nav_menu', array('slug' => 'aw_ar_reviewmenu'));
				$uploader_page = array(
									'comment_status'        => 'closed',
									'ping_status'           => 'closed',
									'post_author'           => $author_id,
									'post_name'				=> $slug,
									'post_title'            => $title,
									'post_status'           => 'publish',
									'post_type'             => 'page',
									'post_content'			=> '[aw_ar_reviewslist]'
									);

				$post_id = wp_insert_post( $uploader_page );

				update_post_meta( $post_id, '_wp_page_template', 'default' );
				update_option('aw_ar_allreviewpage_id', $post_id);

				$nav_item = wp_insert_post(array('post_title' => 'Reviews',
								'post_content'	=> '',
								'post_status'	=> 'publish',
								'post_name'		=> $slug,
								'menu_order'	=> '1',
								'guid'			=> site_url() . '?p=' . $post_id,
								'post_type'		=> 'nav_menu_item')); 
				update_post_meta($nav_item, '_menu_item_type', 'post_type');
				update_post_meta($nav_item, '_menu_item_menu_item_parent', '0');
				update_post_meta($nav_item, '_menu_item_object_id', $post_id);
				update_post_meta($nav_item, '_menu_item_object', 'page');
				update_post_meta($nav_item, '_menu_item_target', '');
				update_post_meta($nav_item, '_menu_item_classes', 'a:1:{i:0;s:0:"";}');
				update_post_meta($nav_item, '_menu_item_xfn', '');
				update_post_meta($nav_item, '_menu_item_url', '');
				wp_set_object_terms($nav_item, 'AW AR REVIEW MENU', 'nav_menu');
 
			} else {
				update_option('aw_ar_reviewpage_endppoint', '');
			}

			if (isset($_POST['aw_ar_allowfile_extensions'])) {
				update_option('aw_ar_allowfile_extensions', sanitize_text_field($_POST['aw_ar_allowfile_extensions']));
			} else {
				update_option('aw_ar_allowfile_extensions', '');
			}

			if (isset($_POST['aw_ar_meta_description'])) {
				update_option('aw_ar_meta_description', sanitize_text_field($_POST['aw_ar_meta_description']));
			} else {
				update_option('aw_ar_meta_description', '');
			}
		}
		self::aw_ar_advanced_review_add_flash_notice( __('Advanced Review general settings updated'), 'success', true );
		wp_redirect($url);

	}


	public static function aw_ar_save_email_templates_setting() {
		global $wpdb; 
		$url =  admin_url() . 'admin.php?page=advanced-review-setting&tab=aw-ar-emails';
		if (isset($_POST['awaradvancedreview_admin_nonce'])) {
			$awaradvancedreview_admin_nonce = sanitize_text_field($_POST['awaradvancedreview_admin_nonce']);
		}

		if ( !wp_verify_nonce( $awaradvancedreview_admin_nonce, 'save_advanced_review_email_setting' )) {
			wp_die('Our Site is protected');
		}

		if (isset($_POST['aw_ar_email_setting_submit'])) {
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
			$db_table = $wpdb->prefix . 'aw_ar_email_templates';
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
			self::aw_ar_advanced_review_add_flash_notice( __('Email templates setting updated'), 'success', true );
			wp_redirect($url);
		}
	}

	public static function aw_ar_admin_email_setting() {
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
							'email'				=> 'Admin Email',
							'subject'			=> 'New review',
							'email_heading'		=> 'New review',
							'additional_content'=> 'Hello,{admin}
Someone has posted a review {link to the review on the backend} for {product_name}.',
							'Formtitle'			=> 'A new review is submitted on the storefront and an email notification is sent to Admin',
						);

		$default_settings[2] = array(
							'email'				=> 'Customer Review Email',
							'subject'			=> 'Your review is pending approval',
							'email_heading'		=> 'Your review is pending approval',
							'additional_content'=> 'Hello, {customer_name}
Your review about {product_name} has been accepted for moderation.
We will let you know about any updates.',
							'Formtitle'			=> 'When a new review is waiting for approval and an email notification is sent to the customer',

		);

		$default_settings[3] = array(
							'email'				=> 'Abuse Report Email',
							'subject'			=> 'Abuse report on review about {product_name}',
							'email_heading'		=> 'Abuse report on review about {product_name}',
							'additional_content'=> 'Hello, {admin}
Someone added a abusment to review about {product_name}',
							'Formtitle'			=> 'Abusement review notification sent to admin',
		);

		$default_settings[4] = array(
							'email'				=> 'Critical Report Email',
							'subject'			=> 'Critical review report about {product_name}',
							'email_heading'		=> 'Critical review report about {product_name}',
							'additional_content'=> 'Hello, {admin}
Critical report of review about {product_name}',
							'Formtitle'			=> 'Critical review report notification is sent to Admin',
		);

		$default_settings[5] = array(
							'email'				=> 'Review Reminder Email',
							'subject'			=> 'Reminder email of review on {product_name}',
							'email_heading'		=> 'Reminder email of review on {product_name}',
							'additional_content'=> 'Hello, {customer_name}
Reminder email of review about {product_name}',
							'Formtitle'			=> 'After purchase email notification is sent to the customer for just reminder.'
		);

		$default_settings[6] = array(
							'email'				=> 'Customer Review Approved Email',
							'subject'			=> 'Review approved',
							'email_heading'		=> 'Review approved of {product_name}',
							'additional_content'=> 'Hello, {customer_name}
your review about {product_name} have been approved.',
							'Formtitle'			=> ' Email notification to customer when a review approved'
		);	

		$default_settings[7] = array(
							'email'				=> 'Comment on Review Email',
							'subject'			=> 'Comment received on review of {product_name}',
							'email_heading'		=> 'Comment received on review of {product_name}',
							'additional_content'=> 'Hello, {customer_name}
comment received on your review about {product_name}',
							'Formtitle'			=> 'Email to customer about a new comment on customer review '
		);				


		if (isset($_GET['ID']) && !empty($_GET['ID'])) {
			$id = sanitize_text_field($_GET['ID']);
			$default_email = $default_settings[$id]['email'];
			$default_subject = $default_settings[$id]['subject'];
			$default_email_heading = $default_settings[$id]['email_heading'];
			$default_additional_content = $default_settings[$id]['additional_content'];

			$data = aw_ar_get_email_template_setting_row($id);

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
			<small class="wc-admin-breadcrumb"><a href="<?php echo wp_kses(admin_url(), wp_kses_allowed_html('post')); ?>admin.php?page=advanced-review-setting&amp;tab=aw-ar-emails" aria-label="Return to emails">⤴</a></small>
		</h2>
		<p><?php echo wp_kses($default_settings[$id]['Formtitle'], wp_kses_allowed_html('post')); ?></p>
		<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" onsubmit="return aw_ar_email_templvalidateForm()">
			<?php wp_nonce_field( 'save_advanced_review_email_setting', 'awaradvancedreview_admin_nonce' ); ?>
			<input type="hidden" name="action" value="aw_ar_save_email_templates_setting">
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
					<?php 
					if ('Admin Email' == $default_email || 'Abuse Report Email'== $default_email|| 'Critical Report Email' ==  $default_email) { 
						?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="woocommerce_new_order_sender">Recipient(s)<span class="woocommerce-help-tip"></span></label>
						</th>
						<td class="forminp">
							<fieldset>
								<legend class="screen-reader-text">
									<span>Recipient(s)</span>
								</legend>
								<input class="input-text regular-input aw_ar_emaillist" onkeypress="return ae_ar_not_allowed()" type="text" name="recipients"  style="" value="<?php echo wp_kses($recipient, wp_kses_allowed_html('post')); ?>" placeholder="<?php echo wp_kses($default_email, wp_kses_allowed_html('post')); ?>" >
								<p><span class="aw_ar_emaillist_msg"></span></p>
							</fieldset>
						</td>
					</tr>
				<?php } ?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="woocommerce_new_order_subject">Subject<span class="woocommerce-help-tip"></span></label>
						</th>
						<td class="forminp">
							<fieldset>
								<legend class="screen-reader-text"><span>Subject</span></legend>
								<input class="input-text regular-input aw_ar_mailsubject" type="text" name="subject" value="<?php echo wp_kses($subject, wp_kses_allowed_html('post')); ?>"placeholder="<?php echo wp_kses($default_subject, wp_kses_allowed_html('post')); ?>" maxlength="50">
								<p><span class="aw_ar_mailsubject_msg"></span></p>
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
								<input class="input-text regular-input required aw_ar_email_heading" type="text" name="email_heading" value="<?php echo wp_kses($email_heading, wp_kses_allowed_html('post')); ?>"placeholder="<?php echo wp_kses($default_email_heading, wp_kses_allowed_html('post')); ?>" maxlength="50">
								<p><span class="aw_ar_email_heading_msg"></span></p>
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
								<textarea rows="3" cols="20" class="input-text wide-input required aw_ar_additional_content" type="textarea"  name="additional_content" placeholder="<?php echo wp_kses($default_additional_content, wp_kses_allowed_html('post')); ?>"><?php echo wp_kses($additional_content, wp_kses_allowed_html('post')) ; ?></textarea>
								<p><span class="aw_ar_additional_content_msg"></span></p>
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
							<button name="aw_ar_email_setting_submit" class="button-primary woocommerce-save-button" type="submit" value="Save changes">Save changes</button>
						</td>
					</tr>
				</tfoot>
			</table>
		</form>	
		<?php 
	}

	public static function aw_ar_add_advanced_review_meta_box() {
		//global $comment;
		$multi_posts = array('comment');
		if (isset($_GET['c'])) {
			$comment_id = sanitize_text_field($_GET['c']);
			$comment 	= get_comment($comment_id);
			if ( ! empty( $comment )) {
				if ('review' === $comment->comment_type) {
					remove_meta_box('woocommerce-rating', 'comment', 'normal');
					//add_meta_box('rating-meta-box', __('Rating'), array('AwAdvancedReviewAdmin' , 'aw_ar_rating_meta_box_callback'), 'comment', 'normal');

					add_meta_box('helpful-pinned-meta-box', __('Advanced Review Details'), array('AwAdvancedReviewAdmin' , 'aw_ar_comment_meta_box_callback'), 'comment', 'normal');
				}
			}
		}
	}

/*	public static function aw_ar_rating_meta_box_callback(){
		global $comment;
		//echo $comment->comment_ID;
		$rating = '';
		$rating .= '<div><span class="star-rating">';
		$aw_ar_reviewrating = get_comment_meta($comment->comment_ID, 'rating', true);
		if( $aw_ar_reviewrating > 0 ):
			for ( $i = 1; $i < 6; $i++ ):
				$class 	= ( $i <= $aw_ar_reviewrating ) ? "filled": "empty";
				$rating .= '<span class="dashicons dashicons-star-'.$class.'"></span>';
			endfor;	
		endif;	
		$rating .= '</span></div>';
		$rating .= '<div class="comment-form-rating" id="aw-ar-comment-form-rating"><p class="stars"><span><a class="star-1" href="#">1</a><a class="star-2" href="#">2</a><a class="star-3" href="#">3</a><a class="star-4" href="#">4</a><a class="star-5" href="#">5</a></span></p></div>';

		//$rating .= '<div class="comment-form-rating" id="aw-ar-comment-form-rating"><p class="stars"><span><a class="star-1" href="#">1</a><a class="star-2" href="#">2</a><a class="star-3" href="#">3</a><a class="star-4" href="#">4</a><a class="star-5" href="#">5</a></span></p><select name="rating" id="rating" required="" style="display: none;"><option value="">Rate…</option><option value="5">Perfect</option><option value="4">Good</option><option value="3">Average</option><option value="2">Not that bad</option><option value="1">Very poor</option></select></div>';
		echo $rating;
	}*/

	public static function aw_ar_comment_meta_box_callback( $comment) {
		
		?>
		<div class="pinned-ar-detail">
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			  <tr>
				  <td class="first">
					<label for="aw-ar-advantage">Advantage</label>
				</td>
				<td >
					<?php
					$aw_ar_allowed_image_ext = get_option('aw_ar_allowfile_extensions');
					$aw_ar_allowed_image_size= get_option('aw_ar_max_filesize');

					if (0 == $comment->comment_parent) {
						$advantage_value = get_comment_meta($comment->comment_ID, 'aw_ar_advantage', true);
						if ('' == $advantage_value) {
							?>
									<textarea rows="5" class="widefat" cols="50" name="aw_ar_advantage"></textarea>
								<?php
						} else {
							?>
									<textarea rows="5" class="widefat" cols="50" name="aw_ar_advantage"><?php echo wp_kses($advantage_value, wp_kses_allowed_html('post')); ?></textarea>
							<?php	 
						}
					}
					?>
				</td>
			  </tr>	
			  <tr>
				  <td class="first">
					<label for="aw-ar-disadvantage">Disadvantage</label>
				</td>
				<td>
					<?php
					if (0 == $comment->comment_parent) {
						$disadvantage_value = get_comment_meta($comment->comment_ID, 'aw_ar_disadvantage', true);
						if ('' == $disadvantage_value) {
							?>
									<textarea rows="5" class="widefat" cols="50" name="aw_ar_disadvantage"></textarea>
								<?php
						} else {
							?>
									<textarea rows="5" class="widefat" cols="50" name="aw_ar_disadvantage"><?php echo wp_kses($disadvantage_value, wp_kses_allowed_html('post')); ?></textarea>
							<?php	 
						}
					}
					?>
				</td>
			  </tr>
			  <tr>
				<td class="first">
					<input type="hidden" name="comment_ID" value="<?php echo wp_kses($comment->comment_ID, wp_kses_allowed_html('post')); ?>">
					<label for="aw-ar-helpful">Helpful</label>
				</td>
				<td>
					<input name="aw_ar_helpful" class="aw_ar_text_helpful aw_ar_txt_required" type="text" value="<?php echo wp_kses(get_comment_meta($comment->comment_ID, 'aw_ar_helpful', true), wp_kses_allowed_html('post')); ?>" onkeypress="return checkIt(event,false)" pattern="^[0-9]*$">
					<span class="rd_text_helpful_error"></span>
				</td>
			  </tr>
			  <tr>
				<td class="first">
					<label for="aw-ar-not-helpful">Not helpful</label>
				</td>
				<td>
					<input name="aw_ar_not_helpful" class="aw_ar_text_nothelpful aw_ar_txt_required" type="text" value="<?php echo wp_kses(get_comment_meta($comment->comment_ID, 'aw_ar_not_helpful', true), wp_kses_allowed_html('post')); ?>" onkeypress="return checkIt(event,false)" pattern="^[0-9]*$" >
					<span class="aw_ar_text_nothelpful_error"></span>
				</td>
			  </tr>
			  <tr>
				<td class="first">
					<label for="aw-ar-recommend">Do you recommend this product?</label>
				</td>
				<td>
					<?php 
						$notspecified 	= '';
						$no_recommend 	= '';
						$yes_recommend 	= '';
						$recommended_value = get_comment_meta($comment->comment_ID, 'aw_ar_recommend', true);
					if ('not specified'== $recommended_value) {
						$notspecified = 'selected = selected';
					}
					if ('no'== $recommended_value) {
						$no_recommend = 'selected = selected';
					}
					if ('yes'== $recommended_value) {
						$yes_recommend = 'selected = selected';
					}												
					?>
					<select name="aw_ar_recommend" >
						<option value="not specified" <?php echo wp_kses($notspecified, wp_kses_allowed_html('post')); ?>>Not specified</option>
						<option value="no" <?php echo wp_kses( $no_recommend, wp_kses_allowed_html('post')); ?>>No</option>
						<option value="yes" <?php echo wp_kses( $yes_recommend, wp_kses_allowed_html('post')); ?>>Yes</option>
					</select>
					<span class="aw_ar_text_nothelpful_error"></span>
				</td>
			  </tr>		
			  <tr>
				<td colspan="2" class="ar-pinn-top">
					<?php
					if (0 == $comment->comment_parent) {
						$checkbox_value = get_comment_meta($comment->comment_ID, 'verified', true);
						if (0 == $checkbox_value) {
							?>
									<input name="verified" type="checkbox" value="false">
								<?php
						} else if (1 == $checkbox_value) {
							?>
									  
									<input name="verified" type="checkbox" value="true" checked>
								<?php
						}  
						?>
							<label for="aw-ar-verified">Verified</label>
						<?php 
					}
					?>
				</td>
			  </tr>	  
			  <tr>
				<td colspan="2" class="ar-pinn-top">
					<?php
					if (0 == $comment->comment_parent) {
						$checkbox_value = get_comment_meta($comment->comment_ID, 'aw_ar_featured', true);
						if ('' == $checkbox_value) {
							?>
							<input name="aw_ar_featured" type="checkbox" value="true">
							<?php
						} else if ('true' == $checkbox_value) {
							?>
							<input name="aw_ar_featured" type="checkbox" value="true" checked>
							<?php
						} else {
							?>
							<input name="aw_ar_featured" type="checkbox" value="false">
							<?php	 
						}
						?>
							<label for="aw-ar-featured">Featured</label>
						<?php 
					}
					?>
				</td>
			  </tr>
			  <tr>
				<td class="first">
					<?php
					if (0 == $comment->comment_parent) {
						?>
						<label for="aw-ar-not-helpful">Image</label>
						<?php 
					}
					?>
				</td>
				<td>
						<div id="preview"></div>
						<div id="aw_ar_filediv">
							<ul class="gallery-<?php echo wp_kses( $comment->comment_ID, wp_kses_allowed_html('post') ); ?>"> 
							<?php 
							 $review_attached_img = maybe_unserialize(get_comment_meta($comment->comment_ID, 'aw-ar-reviewimage', true));
							if (!empty($review_attached_img)) {
								foreach ($review_attached_img as $imageid) {
									$img_link 		= wp_get_attachment_image_url( $imageid, 'full');
									$img_src 		= wp_get_attachment_image_url( $imageid , 'medium');
									$path 			= wp_get_upload_dir();
									$imagepath 		= explode('uploads', $img_src) ;
									$fullpath  		= $path['basedir'] . $imagepath[1];
									if (file_exists($fullpath)) { 
										?>
										 <li>
											 <div id="ar_aw_preview<?php echo wp_kses($imageid, wp_kses_allowed_html('post')); ?>">
												 <a class="ar_aw_light" href="<?php echo esc_url($img_link); ?>">
													 <img class="portfolio" src="<?php echo wp_kses($img_src, wp_kses_allowed_html('post')); ?>" alt="Image" onclick="aw_ar_show_lightbox(<?php echo wp_kses($comment->comment_ID, wp_kses_allowed_html('post')); ?>)">
												 </a>
												 <a href="#" class="closeimg" onclick="awar_deleteimage(<?php echo wp_kses($imageid, wp_kses_allowed_html('post')); ?>,<?php echo wp_kses($comment->comment_ID, wp_kses_allowed_html('post')); ?>)">
													 <img src="<?php echo esc_url(plugins_url('/advanced-reviews-by-aheadworks/admin/images/x.png')); ?>" alt="delete">
												 </a>
											 </div>
										 </li>
									<?php
									}
								}
							}
							?>
							</ul>
						</div>
						<br/>
						<input name="aw_ar_file[]" type="file" id="aw_ar_file" multiple="multiple" />
						<input id="aw_ar_allowed_image_ext" type="hidden" value="<?php echo wp_kses($aw_ar_allowed_image_ext, wp_kses_allowed_html('post')); ?>" />
						<input id="aw_ar_allowed_image_size" type="hidden" value="<?php echo wp_kses($aw_ar_allowed_image_size, wp_kses_allowed_html('post')); ?>" />
						
				</td>
			  </tr>
			</table>
			<?php wp_nonce_field('aw_ar_comment_nonce_action', 'aw_ar_comment_nonce_name'); ?>
		</div>
		<?php  
	}

	public static function aw_ar_save_comment_meta_box( $comment_content) {
	 
		global $wpdb;
		$imagepath 						= array();
		$existing 						= array();
		$imageids 						= array();
		$newimageids 					= array();
		$meta_box_helpful_value 		= '';
		$meta_box_notful_value 			= '';
		$meta_box_pinned_to_top_value	= '';
		$meta_box_advantage_value 		= '';
		$meta_box_disadvantage_value 	= '';
		$meta_box_aw_ar_recommend 		= '';

		$rd_pq_comment_nonce_name		= '';
		$comment_id 					= 0;

		if (isset($_POST['aw_ar_comment_nonce_name'])) {
				$aw_ar_comment_nonce_name = sanitize_text_field($_POST['aw_ar_comment_nonce_name']);
		}

		if ( !wp_verify_nonce( $aw_ar_comment_nonce_name, 'aw_ar_comment_nonce_action') && '' != $aw_ar_comment_nonce_name) {
			wp_die('Our Site is protected');
		}
		if (isset($_POST['comment_ID'])) {
			$comment_id = sanitize_text_field($_POST['comment_ID']);
		}  

		if (isset($_POST['aw_ar_advantage'])) {
			$meta_box_advantage_value = trim(sanitize_text_field($_POST['aw_ar_advantage']));
		}
		if (0 != $comment_id) {
			update_comment_meta($comment_id, 'aw_ar_advantage', $meta_box_advantage_value);
		}

		if (isset($_POST['aw_ar_disadvantage'])) {
			$meta_box_disadvantage_value = trim(sanitize_text_field($_POST['aw_ar_disadvantage']));
		}
		if (0 != $comment_id) {
			update_comment_meta($comment_id, 'aw_ar_disadvantage', $meta_box_disadvantage_value);
		}

		if (isset($_POST['aw_ar_helpful'])) {
			$meta_box_helpful_value = (int) sanitize_text_field($_POST['aw_ar_helpful']);
		}  
		if (0 != $comment_id) {
			update_comment_meta($comment_id, 'aw_ar_helpful', $meta_box_helpful_value);
		}

		if (isset($_POST['aw_ar_not_helpful'])) {
			$meta_box_notful_value = (int) sanitize_text_field($_POST['aw_ar_not_helpful']);
		}   
		if (0 != $comment_id) {
			update_comment_meta($comment_id, 'aw_ar_not_helpful', $meta_box_notful_value);
		}
		
		if (isset($_POST['aw_ar_recommend'])) {
			$meta_box_aw_ar_recommend = sanitize_text_field($_POST['aw_ar_recommend']);
		}   
		if (0 != $comment_id) {
			update_comment_meta($comment_id, 'aw_ar_recommend', $meta_box_aw_ar_recommend);
		}
		 
		if (isset($_POST['verified'])) {
			$meta_box_aw_ar_verified = sanitize_text_field($_POST['verified']);
			if ($meta_box_aw_ar_verified) {
				update_comment_meta($comment_id, 'verified', 1);	
			}
		} else {
				update_comment_meta($comment_id, 'verified', 0);	
		}
		
		if (isset($_POST['aw_ar_featured'])) {
			$meta_box_pinned_to_top_value = sanitize_text_field($_POST['aw_ar_featured']);
		}   
		if (0 != $comment_id) {
			update_comment_meta($comment_id, 'aw_ar_featured', $meta_box_pinned_to_top_value);
		}

		$existing = maybe_unserialize(get_comment_meta($comment_id, 'aw-ar-reviewimage', true));
		if (!empty($_FILES['aw_ar_file']['name'])) {
			$image_data = json_encode($_FILES);
			$image_data = json_decode($image_data, true);
			$newimageids = aw_ar_multiimage_upload($image_data['aw_ar_file']);
			if (!empty($existing)) {
				$imageids	 = array_unique(array_merge ($newimageids, $existing));	
			} else {
				$imageids	 = $newimageids;
			}
			update_comment_meta($comment_id, 'aw-ar-reviewimage', maybe_serialize($imageids));
		}  
		return $comment_content;
	}

	public static function aw_ar_review_image_delete() {
		
		if (isset($_REQUEST['imageid'])) {
			$image_id 	= '';
			$review_id 	= '';
			if (isset($_REQUEST['imageid']) && !empty($_REQUEST['imageid'])) {
				$image_id 	= wp_kses_post($_REQUEST['imageid']);	
				$status 	= wp_delete_post($image_id);
			}
			if (isset($_REQUEST['reviewid']) && !empty($_REQUEST['reviewid'])) {

				$review_id 		= wp_kses_post($_REQUEST['reviewid']);	
				$review_images  = maybe_unserialize(get_comment_meta($review_id, 'aw-ar-reviewimage', true));
				$key = array_search($image_id, $review_images);
				if (false !== $key) {
					unset($review_images[$key]);
				}

				update_comment_meta($review_id, 'aw-ar-reviewimage', maybe_serialize($review_images));
			}
			if ($status) {
				echo 'Image deleted successfully';	
			}
		} else {
			echo 0;
		}
		die;
	}


	public static function aw_advanced_review_comment_status_link( $status_links ) { 
		$user_id = '';
		if (empty($_GET) || ( isset($_GET['comment_type']) && 'review' != $_GET['comment_type'] ) ) {
			return $status_links;
		} else {
			$users = get_users( array( 'role' => 'Administrator' ) );
			if ( ! empty( $users ) ) {
				$user_id = implode(',', wp_list_pluck( $users, 'ID' ));
			}

			$all_count = aw_ar_get_comment_type_count(); // <-- Adjust this count
			$status_links['all'] = sprintf(
			  "<a href=%s>%s <span class='count'>(<span class='all-count'>%d</span>)</span></a>",
			  esc_url( admin_url( 'edit-comments.php?comment_status=all&comment_type=review') ),
			  __( 'All' ),
			  $all_count
			);

			$mine_count = aw_ar_get_comment_type_count('mine', $user_id); // <-- Adjust this count
			$status_links['mine'] = sprintf(
			  "<a href=%s>%s <span class='count'>(<span class='mine-count'>%d</span>)</span></a>",
			  esc_url( admin_url( 'edit-comments.php?comment_status=mine&comment_type=review&user_id=' . $user_id) ),
			  __( 'Mine' ),
			  $mine_count
			);

			$pending_count = aw_ar_get_comment_type_count('0'); // <-- Adjust this count
			$status_links['moderated'] = sprintf(
			  "<a href=%s>%s <span class='count'>(<span class='unapproved-count'>%d</span>)</span></a>",
			  esc_url( admin_url( 'edit-comments.php?comment_status=moderated&comment_type=review') ),
			  __( 'Pending' ),
			  $pending_count
			);

			$approved_count = aw_ar_get_comment_type_count('1'); // <-- Adjust this count
			$status_links['approved'] = sprintf(
			  "<a href=%s>%s <span class='count'>(<span class='approved-count'>%d</span>)</span></a>",
			  esc_url( admin_url( 'edit-comments.php?comment_status=approved&comment_type=review') ),
			  __( 'Approved' ),
			  $approved_count
			);

			$spam_count = aw_ar_get_comment_type_count('spam'); // <-- Adjust this count
			$status_links['spam'] = sprintf(
			  "<a href=%s>%s <span class='count'>(<span class='spam-count'>%d</span>)</span></a>",
			  esc_url( admin_url( 'edit-comments.php?comment_status=spam&comment_type=review') ),
			  __( 'Spam' ),
			  $spam_count
			);

			$trash_count = aw_ar_get_comment_type_count('trash'); // <-- Adjust this count
			$status_links['trash'] = sprintf(
			  "<a href=%s>%s <span class='count'>(<span class='trash-count'>%d</span>)</span></a>",
			  esc_url( admin_url( 'edit-comments.php?comment_status=trash&comment_type=review') ),
			  __( 'Trash' ),
			  $trash_count
			);
		}
		remove_filter('comment_status_links', 'comment_status_links');
		return $status_links;
	}

	public static function aw_advanced_review_change_column_name( $cols ) {
		if (empty($_GET)) {
			return $cols;
		}
		if (isset($_GET['comment_type']) && 'review' != $_GET['comment_type']) {
			return $cols;	
		}
		$cols['author'] 	= 'Customer';
		$cols['response'] 	= 'Product';
		$cols['comment'] 	= 'Review';
		return $cols;
	}

	public static function aw_advanced_review_append_starrating_comment_column( $comment_content ) {
		global $comment;
		if (empty($_GET)) {
			return $comment_content;
		}
		if (( isset($_GET['comment_type']) && 'review' != $_GET['comment_type'] ) || ( isset($_GET['action']) && 'editcomment' == $_GET['action'] )) {
			return $comment_content;	
		}
		$rating = '<div><span class="star-rating">';
		$aw_ar_reviewrating = get_comment_meta($comment->comment_ID, 'rating', true);
		if ( $aw_ar_reviewrating > 0 ) :
			for ( $i = 1; $i < 6; $i++ ) :
				$class 	= ( $i <= $aw_ar_reviewrating ) ? 'filled': 'empty';
				$rating .= '<span class="dashicons dashicons-star-' . $class . '"></span>';
			endfor;	
		endif;	
		$rating .= '</span></div>';
		echo wp_kses($rating, wp_kses_allowed_html('post'));
		return $comment_content;
	}

	public static function aw_ar_change_row_action_url_querysting( $actions) {
		global $comment;
		if (empty($_GET)) {
			return $actions;
		}
		if (( isset($_GET['comment_type']) && 'review' != $_GET['comment_type'] ) || ( isset($_GET['action']) && 'editcomment' == $_GET['action'] )) {
			return $actions;	
		} 
		$actions['edit'] = '<a href="comment.php?action=editcomment&amp;c=' . $comment->comment_ID . '&amp;comment_type=review" aria-label="Edit this comment">Edit</a>';

		return $actions;
	}

	public static function aw_ar_approve_comment_callback( $new_status, $old_status, $comment ) {
		$is_user_enabled = 'no';
		if ( 'review' != $comment->comment_type) {
			return;
		}
		if ( $old_status != $new_status ) {
			if ( 'approved' == $new_status) {
				$product_id = $comment->comment_post_ID;
				$comment_id = $comment->comment_ID;
				$nickname 	= $comment->comment_author; 
				$get_mail_status = get_comment_meta($comment_id, 'approval_mail_sent', true);
				$is_admin_enabled = aw_ar_get_email_template_active_status('Review Approval Email');
				if (empty($get_mail_status)) {
					if ($is_admin_enabled) {
						$the_user = get_user_by('email', $comment->comment_author_email);
						if (!empty($the_user)) {
							$is_user_enabled = get_user_meta($the_user->ID, 'is_ar_reminder_email_enabled', true);	
							if ('yes'===$is_user_enabled) {
								aw_ar_send_mail($product_id, $comment_id, $nickname, '', 'Review Approval Email');
								update_comment_meta($comment_id, sanitize_title('approval_mail_sent'), 1);	
							}
						} else {
							aw_ar_send_mail($product_id, $comment_id, $nickname, '', 'Review Approval Email');
							update_comment_meta($comment_id, sanitize_title('approval_mail_sent'), 1);
						}
					}
				}	
			}
		}
	}

} // class close
