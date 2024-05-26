<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class AwPqProductQuestionsAdmin {

	public static function aw_pq_self_deactivate_notice() {
		/** Display an error message when WooComemrce Plugin is missing **/
		?>
		<div class="notice notice-error">
			<p>Please install and activate WooComemrce plugin before activating Product Questions By Aheadworks Plugin.</p>
		</div>
		<?php
	}

	public static function aw_pq_product_question_add_flash_notice( $notice = '', $type = 'warning', $dismissible = true ) { 
		// Here we return the notices saved on our option, if there are not notices, then an empty array is returned
		$notices = get_option( 'product_question_flash_notices', array() );
		$dismissible_text = ( $dismissible ) ? 'is-dismissible' : '';

		$notices = array(
						'notice' => $notice, 
						'type' => $type, 
						'dismissible' => $dismissible_text
					); 	
		update_option('product_question_flash_notices', $notices );
	}

	public static function aw_pq_advanced_product_options() {
		global $product;

		$post_id 			= get_the_ID();
		$enable_chk 		= 'yes';
		$custom_attributes  = 'checked';
		$post_status 		= get_post_status($post_id);

		echo '<div class="options_group">';
		$enable_chk = get_post_meta( $post_id, 'enable_q_and_a', true );
		if ('auto-draft' === $post_status) {
			$custom_attributes  = 'checked';
			$enable_chk 		=  get_option('rd_setting_qa_enable');
		} else if ('' === $enable_chk) {
			add_post_meta($post_id , 'enable_q_and_a', 'yes' );
			$custom_attributes  = 'checked';
			$enable_chk 		= 'yes';
		} else if ('no' === $enable_chk) {
			$enable_chk 		= 'no';
			$custom_attributes  = '';
		}
		woocommerce_wp_checkbox( array(
				'id'     			=> 'enable_q_and_a',
				'value'   			=> $enable_chk,
				'custom_attributes' => $custom_attributes,
				'label'				=> 'Enable Q&A',
				'desc_tip'			=> true,
				'description'		=> 'Enable to display question & answer on product page.',
		));

		echo '</div>';
		wp_nonce_field('rd_batc_adv_opt_nonce_action', 'rd_batc_adv_opt_nonce_name');
	}

	public static function aw_pq_save_advanced_product_options( $post_id ) {
		global $wpdb;
		$product = wc_get_product( $post_id );

		if (isset($_POST['rd_batc_adv_opt_nonce_name'])) {
			$rd_batc_adv_opt_nonce_name = sanitize_text_field($_POST['rd_batc_adv_opt_nonce_name']);
		}

		if ( !wp_verify_nonce( $rd_batc_adv_opt_nonce_name, 'rd_batc_adv_opt_nonce_action')) {
			wp_die('Our Site is protected');
		}

		if (isset( $_POST['enable_q_and_a'] )) {
			$title = sanitize_text_field($_POST['enable_q_and_a']);
			$product->update_meta_data( 'enable_q_and_a', sanitize_text_field( $title ) );	
		} else {
			$product->update_meta_data( 'enable_q_and_a', sanitize_text_field('no'));	
		}
		$product->save();
	}

	public static function aw_pq_comment_meta_box_callback( $comment) {
		?>

			<div class="pinned-qa-detail">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
				  <tr>
					<td class="first">
						<input type="hidden" name="comment_ID" value="<?php echo wp_kses($comment->comment_ID, wp_kses_allowed_html('post')); ?>">
						<label for="rd-helpful">Helpful</label>
					</td>
					<td>
						<input name="rd-helpful" class="rd_text_helpful pq_txt_required" type="text" value="<?php echo wp_kses(get_comment_meta($comment->comment_ID, 'rd-helpful', true), wp_kses_allowed_html('post')); ?>" onkeypress="return checkIt(event,false)">
						<span class="rd_text_helpful_error"></span>
					</td>
				  </tr>
				  <tr>
					<td class="first">
						<label for="rd-not-helpful">Not helpful</label>
					</td>
					<td>
						<input name="rd-not-helpful" class="rd_text_nothelpful pq_txt_required" type="text" value="<?php echo wp_kses(get_comment_meta($comment->comment_ID, 'rd-not-helpful', true), wp_kses_allowed_html('post')); ?>" onkeypress="return checkIt(event,false)">
						<span class="rd_text_nothelpful_error"></span>
					</td>
				  </tr>
				  <tr>
					<td colspan="2" class="pinn-top">
						<?php
						if (0 == $comment->comment_parent) {
							$checkbox_value = get_comment_meta($comment->comment_ID, 'rd-pinned-to-top', true);
							if ('' == $checkbox_value) {
								?>
										<input name="rd-pinned-to-top" type="checkbox" value="true">
									<?php
							} else if ('true' == $checkbox_value) {
								?>
										  
										<input name="rd-pinned-to-top" type="checkbox" value="true" checked>
									<?php
							} else {
								?>
										<input name="rd-pinned-to-top" type="checkbox" value="false">
								<?php	 
							}
			
							?>
								<label for="rd-pinned-to-top">Pinned to Top</label>
							<?php 
						}
						?>
					</td>
				  </tr>
				</table>
				<?php wp_nonce_field('rd_pq_comment_nonce_action', 'rd_pq_comment_nonce_name'); ?>
			</div>
		<?php  
	}

	public static function aw_pq_save_comment_meta_box( $comment_content) {
		global $wpdb;
		$meta_box_helpful_value 		= '';
		$meta_box_notful_value 			= '';
		$meta_box_pinned_to_top_value	= '';
		
		$rd_pq_comment_nonce_name		= '';
		$comment_id 					= 0;
	
		if (isset($_POST['rd_pq_comment_nonce_name'])) {
				$rd_pq_comment_nonce_name = sanitize_text_field($_POST['rd_pq_comment_nonce_name']);
		}

		if ( !wp_verify_nonce( $rd_pq_comment_nonce_name, 'rd_pq_comment_nonce_action') && '' != $rd_pq_comment_nonce_name) {
			wp_die('Our Site is protected');
		}

		if (isset($_POST['comment_ID'])) {
			$comment_id = sanitize_text_field($_POST['comment_ID']);
		}  
		if (isset($_POST['rd-helpful'])) {
			$meta_box_helpful_value = sanitize_text_field($_POST['rd-helpful']);
		}  
		if (0 != $comment_id) {
			update_comment_meta($comment_id, 'rd-helpful', $meta_box_helpful_value);
		}

		if (isset($_POST['rd-not-helpful'])) {
			$meta_box_notful_value = sanitize_text_field($_POST['rd-not-helpful']);
		}   
		if (0 != $comment_id) {
			update_comment_meta($comment_id, 'rd-not-helpful', $meta_box_notful_value);
		}

		if (isset($_POST['rd-pinned-to-top'])) {
			$meta_box_pinned_to_top_value = sanitize_text_field($_POST['rd-pinned-to-top']);
		}   
		if (0 != $comment_id) {
			update_comment_meta($comment_id, 'rd-pinned-to-top', $meta_box_pinned_to_top_value);
		}

		return $comment_content;
	}

	public static function aw_pq_product_questions_save_setting_form() {
		$original_text = '';
		$modified_text = '';
		$url =  admin_url() . 'admin.php?page=product-questions';
		if (isset($_POST['rdproductquestion_admin_nonce'])) {
			$rdproductquestion_admin_nonce = sanitize_text_field($_POST['rdproductquestion_admin_nonce']);
		}

		if ( !wp_verify_nonce( $rdproductquestion_admin_nonce, 'save_product_question_setting' )) {
			wp_die('Our Site is protected');
		}

		if (isset($_POST['setting_qa_submit'])) {

			if (isset($_POST['rd_setting_qa_enable'])) {
				$get_rd_setting_qa_enable = sanitize_text_field($_POST['rd_setting_qa_enable']);
				update_option('rd_setting_qa_enable', $get_rd_setting_qa_enable);
			} else {
				update_option('rd_setting_qa_enable', '');
			}

			if (isset($_POST['rd_setting_helpful_enable'])) {
				$get_rd_setting_helpful_enable = sanitize_text_field($_POST['rd_setting_helpful_enable']);
				update_option('rd_setting_helpful_enable', $get_rd_setting_helpful_enable);
			} else {
				update_option('rd_setting_helpful_enable', '');
			}
			if (isset($_POST['rd_setting_cookie_days']) && '' != sanitize_text_field($_POST['rd_setting_cookie_days'])) {
				update_option('rd_setting_cookie_days', sanitize_text_field($_POST['rd_setting_cookie_days']));
			}
			if (isset($_POST['pq_allowtouser']) && '' != sanitize_text_field($_POST['pq_allowtouser'])) {
				update_option('pq_allowtouser', sanitize_text_field($_POST['pq_allowtouser']));
			}			
			if (isset($_POST['pq_editin_minutes'])) {
				update_option('pq_editin_minutes', sanitize_text_field($_POST['pq_editin_minutes']));
			}
			if (isset($_POST['pq_adminreply_color'])) {
				update_option('pq_adminreply_color', sanitize_text_field($_POST['pq_adminreply_color']));
			}
			if (isset($_POST['pq_number_color'])) {
				update_option('pq_number_color', sanitize_text_field($_POST['pq_number_color']));
			}

			self::aw_pq_product_question_add_flash_notice( __(aw_pq_translate_text('Product questions configuration setting updated')), 'success', true );

			if (!empty($_FILES['pq_langaugecsv']['name'])) {

				$aw_pq_file_name 			= sanitize_text_field($_FILES['pq_langaugecsv']['name']);

				if (!empty($_FILES['pq_langaugecsv']['tmp_name'])) {
					$aw_pq_file_name_temp 	= sanitize_text_field($_FILES['pq_langaugecsv']['tmp_name']);
				}

				// File extension
				$extension = pathinfo($aw_pq_file_name, PATHINFO_EXTENSION);
				// If file extension is 'csv'

				$file_type = mime_content_type($aw_pq_file_name_temp);

				if (!empty($aw_pq_file_name) && 'csv' == $extension && 'text/plain' == $file_type) {
					$totalInserted = 0;
					// Open file in read mode
					$csvFile = fopen($aw_pq_file_name_temp, 'r');
					// Read file
					while (( $csvData = fgetcsv($csvFile) ) !== false) {
						//$csvData = mb_convert_encoding($csvData, 'UTF-8');
						if (!empty($csvData)) {
							if (!empty($csvData[0]) ) {
								$original_text = trim($csvData[0]);	
							}
							if (!empty($csvData[1]) ) {
								$modified_text = trim($csvData[1]);
							}
							if (''!=$original_text) {
								$data = aw_pq_get_lang_translation_data($original_text);
								if (!empty($data)) {
									aw_pq_update_lang_translation_data($original_text, $modified_text);
								}
							}
						}
					}
					//$uploadedfile 		= $_FILES['pq_langaugecsv'];

					$upload_overrides 	= array( 'test_form' => false );
					$movefile 			= wp_handle_upload( $_FILES['pq_langaugecsv'], $upload_overrides );
					$new_filename 		= basename( $movefile['file'] );
					$uploaded_datetime  = gmdate( 'M d, Y ', strtotime(gmdate('d-m-Y h:i:s')));
					if (isset($movefile['url']) && !empty($movefile['url'])) {
						update_option('pq_langaugecsv', $movefile['url']);
						update_option('pq_csv_path', $movefile['file']);
						update_option('pq_csv_uploadtime', $uploaded_datetime);	
					}
					self::aw_pq_product_question_add_flash_notice( __(aw_pq_translate_text('Product questions configuration setting updated')), 'success', true );
				} else {
					self::aw_pq_product_question_add_flash_notice( __(aw_pq_translate_text('Invalid file type or Invalid content type')), 'warning', true );
				}
			}
		}
		wp_redirect($url);
	}

	public static function aw_pq_product_question_setting() {
		$rd_setting_helpful_enable = '';
		$rd_setting_qa_enable	= '';
		$pq_editin_minutes 			= '';
		$anyone 		 			= '';
		$loggedinuser				= '';
		if (get_option('rd_setting_qa_enable')) {
			$rd_setting_qa_enable = 'checked = checked';
		}
		if (get_option('rd_setting_helpful_enable')) {
			$rd_setting_helpful_enable = 'checked = checked';
		}
		if ('' != get_option('pq_editin_minutes')) {
			$pq_editin_minutes = get_option('pq_editin_minutes');
		}

		if (get_option('pq_allowtouser')) {
			if ('anyone' == get_option('pq_allowtouser')) {
				$anyone = 'selected = selected';
			}
			if ('loggedinuser' == get_option('pq_allowtouser')) {
				$loggedinuser = 'selected = selected';
			}
		}

		$notice = get_option( 'product_question_flash_notices', array() );
		if ( ! empty( $notice ) ) {
				printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
					wp_kses($notice['type'], wp_kses_allowed_html('post')),
					wp_kses($notice['dismissible'], wp_kses_allowed_html('post')),
					wp_kses($notice['notice'], wp_kses_allowed_html('post'))
				);
			delete_option( 'product_question_flash_notices', array() );
		}
		$email_template = aw_pq_get_email_template_setting_results();
		?>
		<div class="tab-grid-wrapper">
			<div class="spw-rw clearfix">
				<div class="panel-box product-questions-setting">
					<div class="page-title">
						<h1>
							<?php echo wp_kses(aw_pq_translate_text('Product Questions'), wp_kses_allowed_html('post')); ?>
						</h1>
					</div>
					<div class="panel-body">
						<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" id="rd_setting_form" enctype="multipart/form-data">
									<?php wp_nonce_field( 'save_product_question_setting', 'rdproductquestion_admin_nonce' ); ?>
									<input type="hidden" name="action" value="questions_save_setting_form">
							<div class="tab">
								<button class="tablinks active"   onclick="openTab(event, 'genral-setting-tab',this)"><?php echo wp_kses(aw_pq_translate_text('General Settings'), wp_kses_allowed_html('post')); ?></button>
								<button class="tablinks"  onclick="openTab(event, 'interface-setting-tab',this)"><?php echo wp_kses(aw_pq_translate_text('Interface Settings'), wp_kses_allowed_html('post')); ?></button>
								<button class="tablinks"  onclick="openTab(event, 'email-setting-tab',this)"><?php echo wp_kses(aw_pq_translate_text('Emails'), wp_kses_allowed_html('post')); ?></button>
							</div> 
							<!-- Tab Start -->
							<div class="tabcontent pq-general-set" id="genral-setting-tab" style="display:block;">
									<ul>
										<li>
											<label><?php echo wp_kses(aw_pq_translate_text('Enable Q&A'), wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<input type="checkbox" name="rd_setting_qa_enable" <?php echo wp_kses($rd_setting_qa_enable, wp_kses_allowed_html('post')); ?> value="yes"/>
											<p><span><?php echo wp_kses(aw_pq_translate_text('This option may be overridden for individual products'), wp_kses_allowed_html('post')); ?></span></p>
											</div>
										</li>
										<li>
											<label><?php echo wp_kses(aw_pq_translate_text('Enable helpfulness voting'), wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<input type="checkbox" name="rd_setting_helpful_enable" <?php echo wp_kses($rd_setting_helpful_enable, wp_kses_allowed_html('post')); ?> value="yes"/>
											</div>
										</li>
										<li>
											<label><?php echo wp_kses(aw_pq_translate_text('Helpfulness cookie lifetime days'), wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<input type="text" name="rd_setting_cookie_days" class="rd_setting_cookie_days" value="<?php echo wp_kses(get_option('rd_setting_cookie_days'), wp_kses_allowed_html('post')); ?>" onkeypress="return checkIt(event,false)"/>
											<span class="rd_setting_cookie_days_error"></span>
											</div>
										</li>
										<li>
											<label><?php echo wp_kses(aw_pq_translate_text('Who can ask questions from product page'), wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<select name="pq_allowtouser">
												<option value="anyone" <?php echo wp_kses($anyone, wp_kses_allowed_html('post')); ?>><?php echo wp_kses(aw_pq_translate_text('Anyone'), wp_kses_allowed_html('post')); ?></option>
												<option value="loggedinuser" <?php echo wp_kses($loggedinuser, wp_kses_allowed_html('post')); ?> ><?php echo wp_kses(aw_pq_translate_text('Logged In Users'), wp_kses_allowed_html('post')); ?></option>
											</select>
											<span class="pq_allowtouser_error"></span>
											</div>
										</li>
										<li>
											<label><?php echo wp_kses(aw_pq_translate_text('Post can be edited by customer within x minutes'), wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<input type="text" name="pq_editin_minutes" class="pq_editin_minutes" value="<?php echo wp_kses($pq_editin_minutes, wp_kses_allowed_html('post')); ?>" onkeypress="return aw_pq_checkIt(event,false)" minlength="0" maxlength="3" />
											<p><span><?php echo wp_kses(aw_pq_translate_text('If left empty or zero - no limit'), wp_kses_allowed_html('post')); ?></span></p>
											<span class="pq_editin_minutes_error"></span>
											</div>
										</li>
										<li>
											<label>
												<?php echo wp_kses(aw_pq_translate_text('Language Translation'), wp_kses_allowed_html('post')); ?> <br/>
												<a href="<?php echo wp_kses(plugin_dir_url( __FILE__ ), wp_kses_allowed_html('post')) . 'product-questions-by-aheadworks-en-us.csv'; ?>"><?php echo wp_kses(aw_pq_translate_text('Click here to download CSV file'), wp_kses_allowed_html('post')); ?></a>
											</label> 
											<div class="control">
												<input type="file" name="pq_langaugecsv" id="pq_langauge_csv">
												<p><span><?php echo wp_kses(aw_pq_translate_text('Download file and then uploaded once again after corrections to rename options or make their translation via a CSV file.'), wp_kses_allowed_html('post')); ?> <br/><?php echo  wp_kses(aw_pq_translate_text('Make changes in second column of CSV file, in text you want to replace.'), wp_kses_allowed_html('post')); ?></span></p>
											</div>
										</li>
										<?php 
										$uploadedcsv_url = wp_kses(get_option('pq_langaugecsv'), wp_kses_allowed_html('post'));
										$uploaded_time 	= wp_kses(get_option('pq_csv_uploadtime'), wp_kses_allowed_html('post'));
										if (!empty($uploadedcsv_url)) {
											?>
												<li>
													<label>
													   <?php echo wp_kses(aw_pq_translate_text('Language Translation you added'), wp_kses_allowed_html('post')); ?>
														<br>
														<a href="<?php echo wp_kses($uploadedcsv_url, wp_kses_allowed_html('post')); ?>">
													   <?php echo wp_kses(aw_pq_translate_text('Click here to download your CSV file'), wp_kses_allowed_html('post')); ?></a>
													</label>
													<div class="control">
														<span>
														   <?php echo wp_kses(aw_pq_translate_text('To apply default language'), wp_kses_allowed_html('post')) . ',' . wp_kses(aw_pq_translate_text('delete this file'), wp_kses_allowed_html('post')); ?>, 
															 <input type="button" style="background-color: none; border: none; color: white; color:red;font-weight: bold; font-size: 14px; cursor: pointer" onclick="aw_pq_defaultlang(this)" value="Click here">
															<!-- <a href="#" onclick="aw_pq_defaultlang(this)" style="color:red;font-weight: bold;">
															< ?php echo wp_kses(aw_pq_translate_text('Click here'), wp_kses_allowed_html('post')); ?>
															</a> -->
														</span>
														<p>
															<span><?php echo wp_kses(aw_pq_translate_text('Added On'), wp_kses_allowed_html('post')); ?>: <?php echo wp_kses($uploaded_time, wp_kses_allowed_html('post')); ?></span>
														</p>
													</div>
												</li>
											<?php	
										}
										?>
									</ul>   
									<div class="submit">
										<input type="submit" class="button button-primary" value="<?php echo wp_kses(aw_pq_translate_text('Save'), wp_kses_allowed_html('post')); ?>" name="setting_qa_submit"/>	
									</div>
							</div>
							<div class="tabcontent pq-interface-set" id="interface-setting-tab" style="display:none;">
									<ul>
										<li>
											<label><?php echo wp_kses(aw_pq_translate_text('Highlight admin reply'), wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<input type="text" name="adminreply_color" disabled="disabled" id="choose_adminreply_color" onchange="setTextColor(this.jscolor)" value="<?php echo wp_kses(get_option('pq_adminreply_color'), wp_kses_allowed_html('post')); ?>"  width: 6% >
											<button class="button jscolor {valueElement:'choose_adminreply_color',styleElement:'choose_adminreply_color',  onFineChange:'setTextColor(this)'}"><?php echo wp_kses(aw_pq_translate_text('Select color'), wp_kses_allowed_html('post')); ?></button>
											<button type="button" class="button pq-reset pq_replycolor_reset">Reset color</button>
											<input type="hidden" name="pq_adminreply_color" id="adminreply_color" value="">
											</div>
										</li>
										<li>
											<label><?php echo wp_kses(aw_pq_translate_text('Highlight Q & A number'), wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<input type="text" name="pqnumbercolor" disabled="disabled" id="choose_number_color" value="<?php echo wp_kses(get_option('pq_number_color'), wp_kses_allowed_html('post')); ?>"  width: 6% ><button class="button jscolor {valueElement:'choose_number_color',styleElement:'choose_number_color',  onFineChange:'setTextColor(this)'}"><?php echo wp_kses(aw_pq_translate_text('Select color'), wp_kses_allowed_html('post')); ?></button>
											<button type="button" class="button pq-reset pq_numbercolor_reset">Reset color</button>

											<input type="hidden" name="pq_number_color" id="pqnumbercolor" value="">

											<p><span><?php echo wp_kses(aw_pq_translate_text('If the number is greater than 0'), wp_kses_allowed_html('post')); ?></span></p>
											</div>
										</li>
									</ul>   
									<div class="submit">
										<input type="submit" class="button button-primary" value="<?php echo wp_kses(aw_pq_translate_text('Save'), wp_kses_allowed_html('post')); ?>" name="setting_qa_submit"/>	
									</div>
							</div>

							<div class="tabcontent pq-email-set" id="email-setting-tab" style="display:none;">
							<table class="form-table">
								<tbody>
									<tr valign="top">
									<td class="wc_emails_wrapper" colspan="2">
										<table class="wc_emails widefat" cellspacing="0">
											<thead>
												<tr>
													<th class="wc-email-settings-table-status"><?php echo wp_kses(aw_pq_translate_text('Status'), wp_kses_allowed_html('post')); ?></th><th class="wc-email-settings-table-name"><?php echo wp_kses(aw_pq_translate_text('Email'), wp_kses_allowed_html('post')); ?></th><th class="wc-email-settings-table-email_type"><?php echo wp_kses(aw_pq_translate_text('Content'), wp_kses_allowed_html('post')); ?></th><th class="wc-email-settings-table-recipient"><?php echo wp_kses(aw_pq_translate_text('Recipients'), wp_kses_allowed_html('post')); ?></th><th class="wc-email-settings-table-actions"><?php echo wp_kses(aw_pq_translate_text('Manage'), wp_kses_allowed_html('post')); ?></th>						
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
															<span class="tooltiptext"><?php echo wp_kses(aw_pq_translate_text($setting_status), wp_kses_allowed_html('post')); ?></span>
															</div>

														</td>
														<td class="wc-email-settings-table-name" data-colname="Email">
															<a href="<?php echo esc_url(admin_url('admin.php?page=product-questions-emails&ID=' . $template->id)) ; ?>"><?php echo wp_kses(aw_pq_translate_text($template->email), wp_kses_allowed_html('post')); ?></a>
															<span class="woocommerce-help-tip"></span>
														</td>
														<td class="wc-email-settings-table-email_type" data-colname="Content">
														<?php 
														echo wp_kses($template->email_type, wp_kses_allowed_html('post'));
														?>
														 </td>
														<td class="wc-email-settings-table-recipient" data-colname="Recipient">
														<?php 
														if ('1' == $template->id) {
															echo wp_kses(aw_pq_translate_text($template->recipients), wp_kses_allowed_html('post'));} else {
															echo wp_kses(aw_pq_translate_text('Customer'), wp_kses_allowed_html('post'));} 
															?>
														</td>
														<td class="wc-email-settings-table-actions">
															<a class="button" href="<?php echo esc_url(admin_url('admin.php?page=product-questions-emails&ID=' . $template->id)) ; ?>">
															<?php echo wp_kses(aw_pq_translate_text('Manage'), wp_kses_allowed_html('post')); ?></a>
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

	public static function aw_pq_save_email_templates_setting() {
		global $wpdb; 
		$url =  admin_url() . 'admin.php?page=product-questions&tab=emails';
		if (isset($_POST['rdproductquestion_admin_nonce'])) {
			$rdproductquestion_admin_nonce = sanitize_text_field($_POST['rdproductquestion_admin_nonce']);
		}

		if ( !wp_verify_nonce( $rdproductquestion_admin_nonce, 'save_product_question_email_setting' )) {
			wp_die('Our Site is protected');
		}

		if (isset($_POST['pq_email_setting_submit'])) {
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
				$recipients = '';
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
			$db_table = $wpdb->prefix . 'aw_pq_email_templates';
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
			self::aw_pq_product_question_add_flash_notice( __(aw_pq_translate_text('Email templates setting updated')), 'success', true );
			

			wp_redirect($url);
		}
	}

	public static function aw_pq_admin_email_setting() {
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
							'email'				=> 'Admin email',
							'subject'			=> 'New Question',
							'email_heading'		=> 'New Question',
							'additional_content'=> 'Hello,{admin}
Someone has posted a question {link to the question on the backend} for {product_name}.',
							'Formtitle'			=> 'A new question is submitted on the storefront and an email notification is sent to Admin',
						);

		$default_settings[2] = array(
							'email'				=> 'customer',
							'subject'			=> 'Your question is pending approval',
							'email_heading'		=> 'Your question is pending approval',
							'additional_content'=> 'Hello, {customer_name}
Your question about {product_name} has been accepted for moderation.
We will let you know about any updates.',
							'Formtitle'			=> 'When a new question is waiting for approval and an email notification is sent to the customer',

		);

		$default_settings[3] = array(
							'email'				=> 'customer',
							'subject'			=> 'A new reply to your question about {product_name}',
							'email_heading'		=> 'A new reply to your question about {product_name}',
							'additional_content'=> 'Hello, {customer_name}
Someone added a comment to your question thread: {A reply on the initial comment}
View it on the site:',
							'Formtitle'			=> 'When question answered by (admin/user) + link to the question (or product page) and an email notification is sent to the customer',
		);

		if (isset($_GET['ID']) && !empty($_GET['ID'])) {
			$id = sanitize_text_field($_GET['ID']);
			$default_email = $default_settings[$id]['email'];
			$default_subject = $default_settings[$id]['subject'];
			$default_email_heading = $default_settings[$id]['email_heading'];
			$default_additional_content = $default_settings[$id]['additional_content'];

			$data = aw_pq_get_email_template_setting_row($id);

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
			<?php echo wp_kses(aw_pq_translate_text($email), wp_kses_allowed_html('post')); ?>
			<small class="wc-admin-breadcrumb"><a href="<?php echo wp_kses(admin_url(), wp_kses_allowed_html('post')); ?>admin.php?page=product-questions&amp;tab=emails" aria-label="Return to emails">⤴</a></small>
		</h2>
		<p><?php echo wp_kses(aw_pq_translate_text($default_settings[$id]['Formtitle']), wp_kses_allowed_html('post')); ?></p>
		<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
			<?php wp_nonce_field( 'save_product_question_email_setting', 'rdproductquestion_admin_nonce' ); ?>
			<input type="hidden" name="action" value="pq_save_email_templates_setting">
			<input type="hidden" name="ID" value="<?php echo wp_kses($id, wp_kses_allowed_html('post')); ?>"><table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="woocommerce_new_order_enabled"><?php echo wp_kses(aw_pq_translate_text('Enable/Disable'), wp_kses_allowed_html('post')); ?></label>
						</th>
						<td class="forminp">
							<fieldset>
								<legend class="screen-reader-text"><span><?php echo wp_kses(aw_pq_translate_text('Enable/Disable'), wp_kses_allowed_html('post')); ?></span></legend>
								<label for="woocommerce_new_order_enabled">
								<input class="" type="checkbox" name="active" value="<?php echo wp_kses($active, wp_kses_allowed_html('post')); ?>" <?php echo wp_kses($checked, wp_kses_allowed_html('post')); ?>> <?php echo wp_kses(aw_pq_translate_text('Enable this email notification'), wp_kses_allowed_html('post')); ?></label><br>
							</fieldset>
						</td>
					</tr><?php if ('1' == $id ) { ?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="woocommerce_new_order_recipient"><?php echo wp_kses(aw_pq_translate_text('Recipient(s)'), wp_kses_allowed_html('post')); ?> <span class="woocommerce-help-tip"></span></label>
						</th>
						<td class="forminp">
							<fieldset>
								<legend class="screen-reader-text"><span><?php echo wp_kses(aw_pq_translate_text('Recipient(s)'), wp_kses_allowed_html('post')); ?></span></legend>
								<input class="input-text regular-input aw_pq_emaillist " type="text" name="recipients"  style="" value="<?php echo wp_kses($recipient, wp_kses_allowed_html('post')); ?>" placeholder="<?php echo wp_kses(aw_pq_translate_text($default_email), wp_kses_allowed_html('post')); ?>"><p><span class="error_msg"></span></p>
							</fieldset>
						</td>
					</tr>
				<?php } ?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="woocommerce_new_order_subject"><?php echo wp_kses(aw_pq_translate_text('Subject'), wp_kses_allowed_html('post')); ?> <span class="woocommerce-help-tip"></span></label>
						</th>
						<td class="forminp">
							<fieldset>
								<legend class="screen-reader-text"><span><?php echo wp_kses(aw_pq_translate_text('Subject'), wp_kses_allowed_html('post')); ?></span></legend>
								<input class="input-text regular-input " type="text" name="subject" value="<?php echo wp_kses(aw_pq_translate_text($subject), wp_kses_allowed_html('post')); ?>"placeholder="<?php echo wp_kses(aw_pq_translate_text($default_subject), wp_kses_allowed_html('post')); ?>">
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="woocommerce_new_order_heading"><?php echo wp_kses(aw_pq_translate_text('Email heading'), wp_kses_allowed_html('post')); ?> <span class="woocommerce-help-tip"></span></label>
						</th>
						<td class="forminp">
							<fieldset>
								<legend class="screen-reader-text"><span><?php echo wp_kses(aw_pq_translate_text('Email heading'), wp_kses_allowed_html('post')); ?></span></legend>
								<input class="input-text regular-input " type="text" name="email_heading" value="<?php echo wp_kses(aw_pq_translate_text($email_heading), wp_kses_allowed_html('post')); ?>"placeholder="<?php echo wp_kses(aw_pq_translate_text($default_email_heading), wp_kses_allowed_html('post')); ?>">
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="woocommerce_new_order_additional_content"><?php echo wp_kses(aw_pq_translate_text('Additional content'), wp_kses_allowed_html('post')); ?> <span class="woocommerce-help-tip"></span></label>
						</th>
						<td class="forminp">
							<fieldset>
								<legend class="screen-reader-text"><span><?php echo wp_kses(aw_pq_translate_text('Additional content'), wp_kses_allowed_html('post')); ?></span></legend>
								<textarea rows="3" cols="20" class="input-text wide-input " type="textarea" name="additional_content" style="width:400px; height: 75px;"placeholder="<?php echo wp_kses(aw_pq_translate_text($default_additional_content), wp_kses_allowed_html('post')); ?>"><?php echo wp_kses(aw_pq_translate_text($additional_content), wp_kses_allowed_html('post')) ; ?></textarea>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="woocommerce_new_order_email_type"><?php echo wp_kses(aw_pq_translate_text('Email type'), wp_kses_allowed_html('post')); ?> <span class="woocommerce-help-tip"></span></label>
						</th>
						<td class="forminp">
							<fieldset>
								<legend class="screen-reader-text"><span><?php echo wp_kses(aw_pq_translate_text('Email type'), wp_kses_allowed_html('post')); ?></span></legend>
								<select class="select email_type wc-enhanced-select select2-hidden-accessible enhanced" name="email_type" style="" tabindex="-1" aria-hidden="true">
									<option 
									<?php 
									if ('plain' === $email_type) {
echo 'selected="selected"';} 
									?>
									 value="text/plain"><?php echo wp_kses(aw_pq_translate_text('Plain text'), wp_kses_allowed_html('post')); ?></option>
									<option 
									<?php 
									if ('text/html' === $email_type) {
echo 'selected="selected"';} 
									?>
									 value="text/html" ><?php echo wp_kses(aw_pq_translate_text('HTML'), wp_kses_allowed_html('post')); ?></option>
								</select>
							</fieldset>
						</td>
					</tr>

				</tbody>
				<tfoot>
					<tr valign="top">
						<td>
							<button name="pq_email_setting_submit" class="button-primary woocommerce-save-button" type="submit" value="Save changes" onclick="return checkemaillist()"><?php echo wp_kses(aw_pq_translate_text('Save changes'), wp_kses_allowed_html('post')); ?></button>
						</td>
					</tr>
				</tfoot>
			</table>
		</form>	
		<?php 
	}

	public static function aw_pq_default_lang_setting() {
		global $wpdb;
		check_ajax_referer( 'rdproductquestion_admin_nonce', 'pq_nonce_admin_ajax' );
		$uploaded_csv_path = get_option('pq_csv_path');
		wp_delete_file( $uploaded_csv_path );
		delete_option('pq_langaugecsv');
		delete_option('pq_csv_uploadtime');
		$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}aw_pq_translations");
		aw_pq_insert_default_langauge_text();
		echo 'Default language applied';
		die;
	}
}
?>
