<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class AwProductLabelAdmin {
	public static function aw_pl_self_deactivate_notice() {
		/** Display an error message when WooComemrce Plugin is missing **/
		?>
		<div class="notice notice-error">
			<p>Please install and activate WooCommerce plugin before activating Product Labels plugin.</p>
		</div>
		<?php
	}

	public static function aw_pl_save_flash_notice( $notice = '', $type = 'warning', $dismissible = true ) { 
		// Here we return the notices saved on our option, if there are not notices, then an empty array is returned
		$notices = get_option( 'label_flash_notices', array() );
		$dismissible_text = ( $dismissible ) ? 'is-dismissible' : '';

		$notices = array(
						'notice' => $notice, 
						'type' => $type, 
						'dismissible' => $dismissible_text
					); 	
		update_option('label_flash_notices', $notices );
	}
	public static function aw_pl_setting_page() {
		$aw_pl_vertical = '';
		$aw_pl_horizontal = '';
		$notice = maybe_unserialize(get_option( 'label_flash_notices'));
		if ( ! empty( $notice ) ) {
			printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
			wp_kses($notice['type'], wp_kses_allowed_html('post')),
			wp_kses($notice['dismissible'], wp_kses_allowed_html('post')),
			wp_kses($notice['notice'], wp_kses_allowed_html('post'))
			);
			delete_option( 'label_flash_notices');
		}
		if (get_option('aw_pl_label_select')) {
			if ('vertical' == get_option('aw_pl_label_select')) {
				$aw_pl_vertical = 'selected = selected';
			}
			if ('horizontal' == get_option('aw_pl_label_select')) {
				$aw_pl_horizontal = 'selected = selected';
			}
		}
		$heading='General Settings';
		?>
		<div class="tab-grid-wrapper">
			<div class="spw-rw clearfix">
				<div class="panel-box product-label-genral-setting">
					<div class="page-title">
						<h2>
						   <?php echo wp_kses($heading, wp_kses_allowed_html('post')); ?>
						</h2>
						<div class="panel-body">
						   <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" id="rdpl_save_setting_form" enctype="multipart/form-data">
								<?php wp_nonce_field( 'aw_pl_save_setting_form', 'rdpl_save_setting_nonce' ); ?>
							  <input type="hidden" name="action" value="aw_pl_save_setting_form">

								<div class="tabcontent rule-general-set" id="genral-setting-tab" style="display: block;">
									<ul>
										<li>
											<label>Distance from Label with Higher Priority, px</label>
											<div class="control">
												<input type="text" name="aw_pl_setting_label_distance" class="aw_pl_setting_label_distance" value="<?php echo wp_kses(get_option('aw_pl_setting_label_distance'), wp_kses_allowed_html('post')); ?>" onkeypress="return checkIt(event,false)"/>
												<span class="aw_pl_setting_label_distance_error"></span>
												<p><span>Margin in pixels between two labels 
													<br>that overlap each other in some way</span></p>
											</div>
										</li>
										<li>
											<label>Label Alignment</label>
											<div class="control">
											   <select name="aw_pl_label_select" class="label_alignment">
													   <option value="vertical" <?php echo wp_kses($aw_pl_vertical, wp_kses_allowed_html('post')); ?>><?php echo wp_kses('Vertical', wp_kses_allowed_html('post')); ?></option>
													   <option value="horizontal" <?php echo wp_kses($aw_pl_horizontal, wp_kses_allowed_html('post')); ?>><?php echo wp_kses('Horizontal', wp_kses_allowed_html('post')); ?></option>
												</select>
												<span class="label_name_error"></span>
												<p><span>How several labels that overlap <br>
												each other should line up</span></p>
											</div>
										</li>
										<li>
											<label>Max. Number of Labels Over Product Image</label>
											<div class="control">
												<input type="text" name="number_of_labels_over_product_image" class="next_priority" value="<?php echo wp_kses(get_option('number_of_labels_over_product_image'), wp_kses_allowed_html('post')); ?>" autocomplete="off" maxlength="5" onkeypress="return checkIt(event,false)" />
												
												<span class="next_priority_error"></span>
											</div>
										</li>
										<li>
											<label>Max. Number of Labels Next to Price	</label>
											  <div class="control">
												<input type="text" name="number_of_labels_next_to_price" class="priority" value="<?php echo wp_kses(get_option('number_of_labels_next_to_price'), wp_kses_allowed_html('post')); ?>" autocomplete="off" maxlength="5"onkeypress="return checkIt(event,false)" />
											
												<span class="priority_error"></span>
											</div>
										</li>
									</ul>
									<div class="submit">
										<input type="submit" class="button button-primary" value="Save" name="pl_label_submit" onclick="//return genral_setting_save()" />
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php
	}
	public static function aw_pl_save_setting_form() {
		$url =  admin_url() . 'admin.php?page=aw_pl_setting_page';
		if (isset($_POST['rdpl_save_setting_nonce'])) {
			$aw_pl_new_rule_nonce = sanitize_text_field($_POST['rdpl_save_setting_nonce']);
		}
		if ( !wp_verify_nonce( $aw_pl_new_rule_nonce, 'aw_pl_save_setting_form' )) {
			wp_die('Our Site is protected');
		}
		if (isset($_POST['aw_pl_setting_label_distance'])) {
				update_option('aw_pl_setting_label_distance', sanitize_text_field($_POST['aw_pl_setting_label_distance']));
		}
		if (isset($_POST['number_of_labels_over_product_image'])) {
				update_option('number_of_labels_over_product_image', sanitize_text_field($_POST['number_of_labels_over_product_image']));
		}
		if (isset($_POST['number_of_labels_next_to_price'])) {
				update_option('number_of_labels_next_to_price', sanitize_text_field($_POST['number_of_labels_next_to_price']));
		}
		if (isset($_POST['aw_pl_label_select'])) {
				update_option('aw_pl_label_select', sanitize_text_field($_POST['aw_pl_label_select']));
		}
		self::aw_pl_save_flash_notice( __('General setting is updated'), 'success', true );
		wp_redirect($url);		

	}
	public static function aw_pl_label_page() {
		global $wpdb;

		$table = new AwProductLableList();
		$search = '';

		if (isset($_GET['s'])) {
			$search = sanitize_text_field($_GET['s']);
		}
		
		if (isset($_GET['status'])) {
			$status= sanitize_text_field($_GET['status']);
			$table->prepare_items($status, $search);
		} else {
			$table->prepare_items();
		}

		$count_all = $table->get_count(1);
		$count_trashed = $table->get_count(0);
		if (isset($_REQUEST['id'])&&is_array($_REQUEST['id'])) {
			$count = count($_REQUEST['id']);
		} else {
			$count = 1;
		}
		$message = '';
		if ('trash' === $table->current_action() && isset($_REQUEST['id']) && !empty($_REQUEST['success'])) {
			/* translators: number count  */
			$message = '<div class="updated below-h2 cutommessage is-dismissible"  ><p>' . sprintf(__('%d Label moved to the Trash', 'Label List'), intval($count)) . '</p></div>';
		}

		if ('trash' === $table->current_action() && empty($_REQUEST['success'])) {
			/* translators: number count  */
			$message = '<div class="error notice is-dismissible"  ><p>' . sprintf(__('%d Label(-s) cannot be deleted so as they relate to rule(-s)', 'Label List'), intval($count)) . '</p></div>';
		}

		if ('delete' === $table->current_action() && isset($_REQUEST['id'])) {
			/* translators: number count  */
			$message = '<div class="updated below-h2 cutommessage is-dismissible"  ><p>' . sprintf(__('%d Label permanently deleted.', 'Label List'), intval($count)) . '</p></div>';
		}
		if ('untrash' === $table->current_action() && isset($_REQUEST['id'])) {
			/* translators: number count  */
			$message = '<div class="updated below-h2 cutommessage is-dismissible"  ><p>' . sprintf(__('%d Label  restore.', 'Label List'), intval($count)) . '</p></div>';
		}
		$notice = maybe_unserialize(get_option( 'label_flash_notices'));
		if ( ! empty( $notice ) ) {
			printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
			wp_kses($notice['type'], wp_kses_allowed_html('post')),
			wp_kses($notice['dismissible'], wp_kses_allowed_html('post')),
			wp_kses($notice['notice'], wp_kses_allowed_html('post'))
			);
			delete_option( 'label_flash_notices');
		}

		?>
									
		<div class="wrap">
			<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
			<h1 class="wp-heading-inline"><?php esc_html_e('Product Labels', 'Label List'); ?></h1>
			<a href="admin.php?page=new-label" class="page-title-action">New Label</a>
			<?php echo wp_kses($message, 'post'); ?>
			<hr class="wp-header-end">
			<ul class="subsubsub">
				<li class="all"><a href="admin.php?page=aw_pl_label_page" class="current" aria-current="page">All <span class="count">(<?php echo intval($count_all); ?>)</span></a> |</li>
				<li class="trash"><a href="admin.php?status=0&page=aw_pl_label_page">Trash <span class="count">(<?php echo intval($count_trashed); ?>)</span></a></li>
			</ul>
			<form id="posts-filter" method="get">
				<p class="search-box">
					<input type="hidden" name="page" class="page" value="aw_pl_label_page">	
					<input type="hidden" name="status" class="post_status_page" value="
					<?php 
					if (isset($_GET['status']) && 0  == $_GET['status'] ) {
						echo 0;
					} else {
						echo 1;} 
					?>
					">
					<input type="search" id="post-search-input" name="s" value="<?php echo esc_html($search); ?>">
					<input type="submit" id="search-submit" class="button" value="Search Label">
				</p>
			</form>
		
			<form id="label-table" method="GET">
				<input type="hidden" name="page" value="<?php echo esc_html('aw_pl_label_page'); ?>"/>
				<input type="hidden" name="page" value="<?php echo isset($_REQUEST['page']) ? wp_kses($_REQUEST['page'], 'post') : '' ; ?>"/>
				<?php $table->display(); ?>
			</form>
		</div>
		<?php

	}
	public static function aw_pl_label_new_page() {
		$id 					= '';
		$heading 				= 'New Label';
		$label_name 			= '';
		$label_test_text 		= '';
		$label_position 		= '';
		$label_type 			= '';
		$label_size 			= '';
		$product_label_shape 	= '';
		$label_image 			= '';
		$pl_label_icon_file 	= '';
		$pl_container_color 	= '';
		$pl_adminlabeltext_color = '';
		$label_container_width 	= '';
		$label_container_height = '';
		$label_text_size 		= '';
		$label_container_css 	= '';
		$label_text_css 		= '';

		$pl_adminlabeltext_color_medium = '';
		$pl_adminlabeltext_color_small = '';

		$pl_container_color_medium 	= '';
		$label_container_width_medium 	= '';
		$label_container_height_medium = '';

		$pl_container_color_small	= '';
		$label_container_width_small 	= '';
		$label_container_height_small = '';

		$medium_label_container_css 	= '';
		$small_label_container_css 	= '';

		$label_text_size_medium 		= '';
		$label_text_size_small 		= '';

		$medium_label_text_css 		= '';
		$small_label_text_css 		= '';
		
		$textdesign1 			= '';
		$textdesign2 			= '';
		$textdesign3 			= '';
		$textdesign4 			= '';
		$textdesign5 			= '';
		$textdesign6 			= '';
		$shapedesign1 			= '';
		$shapedesign2 			= '';
		$shapedesign3 			= '';
		$shapedesign4 			= '';
		$shapedesign5 			= '';
		$shapedesign6 			= '';
		$picdesign1 			= '';
		$picdesign2 			= '';
		$picdesign3 			= '';
		$picdesign4 			= '';
		$picdesign5				= '';
		$picdesign6 			= '';
		$text_css 				= '';
		$m_text_css				= '';
		$s_text_css				= '';


		if (isset($_GET['id']) && !empty($_GET['id'])) {
			$heading = 'Edit Label';
			$id = sanitize_text_field($_GET['id']);
			$label_data = aw_pl_label_row($id);
			if (!empty($label_data)) {
				$label_name 				= $label_data->name;
				$label_position 			= $label_data->position;
				$label_type	 				= $label_data->type;	
				$product_label_shape 		= $label_data->shape_type;	
				$pl_label_icon_file 		= $label_data->label_image;
				$label_size 				= $label_data->label_size;
				$label_container_css  		= $label_data->label_container_css;
				$medium_label_container_css = $label_data->medium_label_container_css;
				$small_label_container_css  = $label_data->small_label_container_css;
				$label_text_css  			= $label_data->label_text_css;	
				$medium_label_text_css		= $label_data->medium_label_text_css;
				$small_label_text_css		= $label_data->small_label_text_css;	
			}
			
			if ($label_position) {
				$label_position = sanitize_text_field($label_position);
				if ('next_price' == $label_position) {
					$shapenextprice = sanitize_text_field($product_label_shape);
				} else {
					$shapeplaceholder = sanitize_text_field($product_label_shape);
				}
			}

			if ($product_label_shape) {
				$product_label_shape = sanitize_text_field($product_label_shape);
			}

			if ($label_type) {
				$label_type = sanitize_text_field($label_type);
			}
			if ($label_size) {
				$label_size = sanitize_text_field($label_size);
			}

			if ($label_text_css && ''!= $label_text_css) {
				$value = sanitize_text_field($label_text_css);
				$text_css = $value;
				$label_text_css = $value;

				$arr = explode(';', $value);
				foreach ($arr as $index => $string) {
					if (strpos($string, 'color') !== false) {
						$data = $arr[$index];
						$pl_adminlabeltext_color = substr($data, strpos($data, ':') + 1);  
					}
					if (strpos($string, 'font-size') !== false) {
						$ser = $arr[$index];
						$f = (int) filter_var($ser, FILTER_SANITIZE_NUMBER_INT);
						$label_text_size  = ltrim($f, '-');

					}
				}
			}

			if ($medium_label_text_css && ''!= $medium_label_text_css) {
				$value = sanitize_text_field($medium_label_text_css);
				$m_text_css = $value;
				$medium_label_text_css = $value;

				$arr = explode(';', $value);
				foreach ($arr as $index => $string) {
					if (strpos($string, 'color') !== false) {
						$data = $arr[$index];
						$pl_adminlabeltext_color_medium = substr($data, strpos($data, ':') + 1);  
					}
					if (strpos($string, 'font-size') !== false) {
						$ser = $arr[$index];
						$f = (int) filter_var($ser, FILTER_SANITIZE_NUMBER_INT);
						$label_text_size_medium  = ltrim($f, '-');

					}
				}
			}

			if ($small_label_text_css && ''!= $small_label_text_css) {
				$value = sanitize_text_field($small_label_text_css);
				$s_text_css = $value;
				$small_label_text_css = $value;

				$arr = explode(';', $value);
				foreach ($arr as $index => $string) {
					if (strpos($string, 'color') !== false) {
						$data = $arr[$index];
						$pl_adminlabeltext_color_small = substr($data, strpos($data, ':') + 1);  
					}
					if (strpos($string, 'font-size') !== false) {
						$ser = $arr[$index];
						$f = (int) filter_var($ser, FILTER_SANITIZE_NUMBER_INT);
						$label_text_size_small  = ltrim($f, '-');

					}
				}
			}

			if ($label_container_css && ''!= $label_container_css ) {
				$value = sanitize_text_field($label_container_css);	    		
				$label_container_css = $value;
				
				$arr = explode(';', $value);
				foreach ($arr as $index => $string) {
					if (strpos($string, 'background-color') !== false) {
						$data = $arr[$index];
						$pl_container_color = substr($data, strpos($data, ':') + 1);   
					}
					if (strpos($string, 'width') !== false) {
						$ser = $arr[$index];
						$label_container_width = (int) filter_var($ser, FILTER_SANITIZE_NUMBER_INT);
					}
					if (strpos($string, 'height') !== false) {
						$ser = $arr[$index];
						$label_container_height = (int) filter_var($ser, FILTER_SANITIZE_NUMBER_INT);
					}
				}
			}

			if ($medium_label_container_css && ''!= $medium_label_container_css ) {
				$value = sanitize_text_field($medium_label_container_css);	    		
				$medium_label_container_css = $value;
				
				$arr = explode(';', $value);
				foreach ($arr as $index => $string) {
					if (strpos($string, 'background-color') !== false) {
						$data = $arr[$index];
						$pl_container_color_medium = substr($data, strpos($data, ':') + 1);   
					}
					if (strpos($string, 'width') !== false) {
						$ser = $arr[$index];
						$label_container_width_medium = (int) filter_var($ser, FILTER_SANITIZE_NUMBER_INT);
					}
					if (strpos($string, 'height') !== false) {
						$ser = $arr[$index];
						$label_container_height_medium = (int) filter_var($ser, FILTER_SANITIZE_NUMBER_INT);
					}
				}
			}

			if ($small_label_container_css && ''!= $small_label_container_css ) {
				$value = sanitize_text_field($small_label_container_css);	    		
				$small_label_container_css = $value;
				
				$arr = explode(';', $value);
				foreach ($arr as $index => $string) {
					if (strpos($string, 'background-color') !== false) {
						$data = $arr[$index];
						$pl_container_color_small = substr($data, strpos($data, ':') + 1);   
					}
					if (strpos($string, 'width') !== false) {
						$ser = $arr[$index];
						$label_container_width_small = (int) filter_var($ser, FILTER_SANITIZE_NUMBER_INT);
					}
					if (strpos($string, 'height') !== false) {
						$ser = $arr[$index];
						$label_container_height_small = (int) filter_var($ser, FILTER_SANITIZE_NUMBER_INT);
					}
				}
			}

			$label_design_l =  $label_container_css . $text_css;
			$label_design_m =  $medium_label_container_css . $m_text_css;
			$label_design_s =  $small_label_container_css . $s_text_css;

			if ('next_price' == $label_position) {

				if ('shape'== $label_type) {
					$shapedesign4 = $label_design_l;
					$shapedesign5 = $label_design_m;
					$shapedesign6 = $label_design_s;
				} else if ('picture'== $label_type) {
					$picdesign4 = $label_design_l;
					$picdesign5 = $label_design_m;
					$picdesign6 = $label_design_s;

					$textdesign4 = $text_css;
					$textdesign5 = $m_text_css;
					$textdesign6 = $s_text_css;

				} else if ('text'== $label_type) {
					$textdesign4 = $label_design_l;
					$textdesign5 = $label_design_m;
					$textdesign6 = $label_design_s;
				}
									
			} else {
				if ('shape'== $label_type) {
					$shapedesign1 = $label_design_l;
					$shapedesign2 = $label_design_m;
					$shapedesign3 = $label_design_s;

				} else if ('picture'== $label_type) {
					$picdesign1 = $label_design_l;
					$picdesign2 = $label_design_m;
					$picdesign3 = $label_design_s;
					
					$textdesign1 = $text_css;
					$textdesign2 = $m_text_css;
					$textdesign3 = $s_text_css;

				} else if ('text'== $label_type) {
					$textdesign1 = $label_design_l;
					$textdesign2 = $label_design_m;
					$textdesign3 = $label_design_s;
				}
			}
		}
		$notice = maybe_unserialize(get_option( 'label_flash_notices'));
		if ( ! empty( $notice ) ) {
			printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
			wp_kses($notice['type'], wp_kses_allowed_html('post')),
			wp_kses($notice['dismissible'], wp_kses_allowed_html('post')),
			wp_kses($notice['notice'], wp_kses_allowed_html('post'))
			);
			delete_option( 'label_flash_notices');
		}
		?>
		<div class="tab-grid-wrapper">
			<div class="spw-rw clearfix">
				<div class="panel-box label-setting">
					<div class="page-title">
						<h2>
							<?php echo wp_kses($heading, wp_kses_allowed_html('post')); ?>
							<small class="wc-admin-breadcrumb"><a href="<?php echo wp_kses(admin_url(), wp_kses_allowed_html('post')); ?>admin.php?page=aw_pl_label_page" aria-label="Return to label list">⤴</a></small>
						</h2>
						<div class="panel-body">
							 <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" id="rdpl_new_label_form" enctype="multipart/form-data">
							<?php wp_nonce_field( 'aw_pl_save_label_form', 'rdpl_new_label_nonce' ); ?>
							 <input type="hidden" name="action" value="aw_pl_save_label_form">
								<div class="tabcontent pl-label-set" id="label-setting-tab" style="display:block;">
									<ul>
										<li>
											<label><?php echo wp_kses('Name', wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<input type="text" name="label_name" class="label_name" value="<?php echo wp_kses($label_name, wp_kses_allowed_html('post')); ?>" />
											<span class="label_name_error"></span>
											</div>
										</li>
										<li>
											<div class="position-settings">
												<label><?php echo wp_kses('Position', wp_kses_allowed_html('post')); ?></label>
												<div class="control">
													<ul class="item-position">
														<li><input type="radio" id="upper_left" name="label_position" value="upper_left" onclick="return positionClick(this);"
														<?php 
														if (!empty($label_position)) {
															checked($label_position, 'upper_left');} else {
															echo 'checked';
															}
															?>
													>
													<span>Upper Left</span></li>
														<li><input type="radio" id="upper_right" name="label_position" value="upper_right" onclick="return positionClick(this);"
														<?php 
														if (!empty($label_position)) {
															checked($label_position, 'upper_right');} 
														?>
													>
													<span">Upper Right</span></li>
														<li><input type="radio" id="lower_right" name="label_position" value="lower_right" onclick="return positionClick(this);" 
														<?php 
														if (!empty($label_position)) {
															checked($label_position, 'lower_right');} 
														?>
													>
													<span>Lower Right</span></li>
														<li><input type="radio" id="lower_left" name="label_position" value="lower_left" onclick="return positionClick(this);"
														<?php 
														if (!empty($label_position)) {
															checked($label_position, 'lower_left');} 
														?>
													>
													<span>Lower Left</span></li>
														<li><input type="radio" id="next_price" name="label_position" value="next_price" onclick="return positionClick(this);"
														<?php 
														if (!empty($label_position)) {
															checked($label_position, 'next_price');} 
														?>
													>
													<span>Next To Price</span></li>
													</ul>
													
													<span class="label_position_error"></span>
												</div>
											</div>
										</li>
										<li>
											<div class="type-settings">
												<label><?php echo wp_kses('Type', wp_kses_allowed_html('post')); ?></label>
												<div class="control">	
													
													<ul class="item-type-sett">
														<li><input type="radio" id="label_shape" name="label_type" onclick="return typeClick(this);" value="shape"
														<?php 
														if (!empty($label_type)) {
															checked($label_type, 'shape');} else {
																echo 'checked';
															} 
															?>
												>
													<span>Shape</span></li>
														<li><input type="radio" id="label_picture" name="label_type" onclick="return typeClick(this);" value="picture" 
														<?php 
														if (!empty($label_type)) {
															checked($label_type, 'picture');} 
														?>
												>
													<span>Picture</span></li>
														<li><input type="radio" id="label_text" name="label_type" onclick="return typeClick(this);" value="text" 
														<?php 
														if (!empty($label_type)) {
															checked($label_type, 'text');} 
														?>
												>
													<span>Text</span></li>
													</ul>
													<span class="label_type_error"></span>
												</div>
											</div>
										</li>
										<li id="shape_tab">
											<label><?php echo wp_kses('Shape Type', wp_kses_allowed_html('post')); ?></label>
											
											<div class="control">
											<select name="label_shape_type"  id="label_shape_type" onchange="shapechange()">
												<option value="rectangle" data-value="rectangle"
												<?php 
												if (!empty($product_label_shape)) {
													selected($product_label_shape, 'rectangle');} 
												?>
												>Rectangle
												</option>
												<option value="rectangle_belevel_up" data-value="rectangle_belevel_up"
												<?php 
												if (!empty($product_label_shape)) {
													selected($product_label_shape, 'rectangle_belevel_up');} 
												?>
												>Rectangle Belevel Up
												</option>
												<option value="rectangle_belevel_down" data-value="rectangle_belevel_down"
												<?php 
												if (!empty($product_label_shape)) {
													selected($product_label_shape, 'rectangle_belevel_down');} 
												?>
												>Rectangle Belevel Down
												</option>
												<option value="square"  data-value="square"
												<?php 
												if (!empty($product_label_shape)) {
													selected($product_label_shape, 'square');} 
												?>
												>Square</option>
												<option value="circle" data-value="circle"
												<?php 
												if (!empty($product_label_shape)) {
													selected($product_label_shape, 'circle');} 
												?>
												>Circle</option>
												<option value="flag" data-value="flag"
												<?php 
												if (!empty($product_label_shape)) {
													selected($product_label_shape, 'flag');} 
												?>
												>Flag</option>
												<option value="point_burst" data-value="point_burst"
												<?php 
												if (!empty($product_label_shape)) {
													selected($product_label_shape, 'point_burst');} 
												?>
												>Point burst</option>
											</select>
											</div>
										</li>
										<li id="image_tab">
											<label style="margin-top: 20px;" ><?php echo wp_kses('Image', wp_kses_allowed_html('post')); ?></label>
											  <div class="control">
											  <div class="pl-upload-panel" style=" margin-top: 20px;">
												<?php
												$src = '';
												$imagname = array();
												if (isset($pl_label_icon_file) && '' != $pl_label_icon_file) {
													$path = wp_get_upload_dir();
													$imagepath = explode('uploads', $pl_label_icon_file) ;
													$fullpath  = $path['basedir'] . $imagepath[1];
													if (file_exists($fullpath)) {
														$src = $pl_label_icon_file; 
														$imagecpy = $pl_label_icon_file;
														$imagname = explode('/', $src);
														?>
														<input type="file" name="pl_label_icon" id="pl_label_icon" style="display:none">
														<a id="closebackimage" class="pl_close_upload" href="javascript:void(0)" post-id="<?php echo wp_kses_post($id); ?>">X</a>
														<input type="hidden" data-value="" value="<?php echo esc_url($src); ?>" name="category_icon_file" id="pl_label_image">
														<img class= "display_image" id="pl_label_display-image" src="<?php echo esc_url($pl_label_icon_file); ?>">
														<?php
													}
												} else {
													?>
												<input type="file" name="pl_label_icon" id="pl_label_icon">
												<img class= "display_image" width="20%" height="20%" id="pl_label_display-image" src="
												<?php 
													if (!empty($src)) {
													echo esc_url($src);
													} else {
													echo ' ';} 
													?>
												" alt="">
												<a id="closebackimage" class="pl_close_upload" href="javascript:void(0)" post-id="<?php echo wp_kses_post($id); ?>">X</a>
												<input type="hidden" data-value="" name="pl_label_image" value="<?php echo esc_url($src); ?>" id="pl_label_image">
												<?php
												}
												?>
												<br>
												<span align="center" id="uploadedimage" style="color: #000000;font-style: normal;"><?php echo wp_kses_post(end($imagname)); ?></span>
												<span class="image_require_error"></span>
												</div>
											</div>
										</li>
									</ul>
										<div class="product-image-area">
											<div class="parent" id="parent1" style="margin-top: 20px;" >
												<div id = "shapeposition1">
													<div  id = "shapeplaceholder1" class="
													<?php 
													if (!empty($shapeplaceholder)) {
														echo wp_kses($shapeplaceholder, wp_kses_allowed_html('post'));
													} else {
														echo wp_kses('rectangle', wp_kses_allowed_html('post'));
													} 
													?>
														"style = "<?php echo wp_kses($shapedesign1, wp_kses_allowed_html('post')); ?>"><p id="shapelabeltext1">Sale</p>
													</div>
												</div>
												<div id="display_picture1">
													<div  Class="img-label-wrap">
														<?php if (!empty($pl_label_icon_file)) { ?>
														<img class = "display_picture1 show_image" id="pl_label_display-image" src="<?php echo esc_url($pl_label_icon_file); ?>" style = "<?php echo wp_kses($picdesign1, wp_kses_allowed_html('post')); ?>">
														<?php } else { ?>
														<img class= "display_images" id="display-images" src="" alt=""><?php } ?>
														<span class = "overlay-label" id="pictext1" style = "<?php echo wp_kses($textdesign1, wp_kses_allowed_html('post')); ?>">Sale</span>
													</div>
												</div>
												<span id="labeltext1" style = "<?php echo wp_kses($textdesign1, wp_kses_allowed_html('post')); ?>">Sale</span>
											</div>
											<div class="parent"  id="parent2">
												<div id = "shapeposition2">
													<div id = "shapeplaceholder2" class="
													<?php 
													if (!empty($shapeplaceholder)) {
echo wp_kses($shapeplaceholder, wp_kses_allowed_html('post'));
													} else {
													echo wp_kses('rectangle', wp_kses_allowed_html('post'));} 
													?>
													" style = "<?php echo wp_kses($shapedesign2, wp_kses_allowed_html('post')); ?>"><p id="shapelabeltext2">Sale</p>
													</div>
												</div>
												<div id="display_picture2">
													<div  Class="img-label-wrap">
														<?php if (!empty($pl_label_icon_file)) { ?>
														<img class = "display_picture2 show_image" id="pl_label_display-image" src="<?php echo esc_url($pl_label_icon_file); ?>" style = "<?php echo wp_kses($picdesign2, wp_kses_allowed_html('post')); ?>"><?php } else { ?>
														<img class= "display_images" id="display-images" src="" alt=""><?php } ?>
														<span class = "overlay-label" id="pictext2" style = "<?php echo wp_kses($textdesign2, wp_kses_allowed_html('post')) ; ?>">Sale</span>
													</div>
												</div>
												<span id="labeltext2" style = "<?php echo wp_kses($textdesign2, wp_kses_allowed_html('post')); ?>">Sale</span>
											</div>
											<div class="parent"  id="parent3">
												<div id = "shapeposition3">
													<div id = "shapeplaceholder3" class="
													<?php 
													if (!empty($shapeplaceholder)) {
														echo wp_kses($shapeplaceholder, wp_kses_allowed_html('post'));
													} else {
														echo wp_kses('rectangle', wp_kses_allowed_html('post'));
													} 
													?>
														" style = "<?php echo wp_kses($shapedesign3, wp_kses_allowed_html('post')); ?>"><p id="shapelabeltext3">Sale</p>
													</div>
												</div>
												<div id="display_picture3">
													<div  Class="img-label-wrap">
														<?php if (!empty($pl_label_icon_file)) { ?>
														<img class = "display_picture3 show_image" id="pl_label_display-image" src="<?php echo esc_url($pl_label_icon_file); ?>" style = "<?php echo wp_kses($picdesign3, wp_kses_allowed_html('post')); ?>"><?php } else { ?>
														<img class= "display_images" id="display-images" src="" alt=""><?php } ?>
														<span class = "overlay-label" id="pictext3" style = "<?php echo wp_kses($textdesign3, wp_kses_allowed_html('post')); ?>">Sale</span>
													</div>
												</div>
												<span id="labeltext3" style = "<?php echo wp_kses($textdesign3, wp_kses_allowed_html('post')); ?>">Sale</span>
											</div>
										</div>
										<div class="product-image-area-next-price">
											<div class="child" id="child1" style="margin-top: 20px;">
												<div id="shapenextposition1">
													<div id = "shapenextprice1" class="
													<?php 
													if (!empty($shapenextprice)) {
echo  wp_kses($shapenextprice, wp_kses_allowed_html('post'));} 
													?>
													" style = "<?php echo wp_kses($shapedesign4, wp_kses_allowed_html('post')); ?>"><p id="shapenextlabeltext1">Sale</p>
													</div>
												</div>
												<div id="next_display_picture1">
													<div  Class="img-label-wrap">
														<?php if (!empty($pl_label_icon_file)) { ?>
														<img class = "next_display_picture1 show_image" id="pl_label_display-image" src="<?php echo esc_url($pl_label_icon_file); ?>" style = "<?php echo wp_kses($picdesign4, wp_kses_allowed_html('post')); ?>">
														<?php } else { ?>
														<img class= "display_images" id="display-images" src="" alt=""><?php } ?>
														<span class = "overlay-label" id="nextpictext1" style = "<?php echo wp_kses($textdesign4, wp_kses_allowed_html('post')); ?>">Sale</span>
													</div>
												</div>
												<span id="nextlabeltext1" style = "<?php echo wp_kses($textdesign4, wp_kses_allowed_html('post')); ?>">Sale</span>
											</div>
											<div class="child"  id="child2">
												<div id="shapenextposition2">
													<div id = "shapenextprice2" class="
													<?php 
													if (!empty($shapenextprice)) {
echo  wp_kses($shapenextprice, wp_kses_allowed_html('post'));} 
													?>
													" style = "<?php echo wp_kses($shapedesign5, wp_kses_allowed_html('post')); ?>"><p id="shapenextlabeltext2">Sale</p>
													</div>
												</div>
												<div id="next_display_picture2">
													<div  Class="img-label-wrap">
														<?php if (!empty($pl_label_icon_file)) { ?>
														<img  class = "next_display_picture2 show_image" id="pl_label_display-image" src="<?php echo esc_url($pl_label_icon_file); ?>"style = "<?php echo wp_kses($picdesign5, wp_kses_allowed_html('post')); ?>">
														<?php } else { ?>
														<img class= "display_images" id="display-images" src="" alt=""><?php } ?>
														<span class = "overlay-label" id="nextpictext2" style = "<?php echo wp_kses($textdesign5, wp_kses_allowed_html('post')); ?>">Sale</span>
													</div>
												</div>
												<span id="nextlabeltext2" style = "<?php echo wp_kses($textdesign5, wp_kses_allowed_html('post')); ?>">Sale</span>
											</div>
											<div class="child"  id="child3">
												<div id="shapenextposition3">
													<div id = "shapenextprice3" class="
													<?php 
													if (!empty($shapenextprice)) {
echo wp_kses($shapenextprice, wp_kses_allowed_html('post'));} 
													?>
													" style = "<?php echo wp_kses($shapedesign6, wp_kses_allowed_html('post')); ?>"><p id="shapenextlabeltext3">Sale</p>
													</div>
												</div>
												<div id="next_display_picture3">
													<div  Class="img-label-wrap">
														<?php if (!empty($pl_label_icon_file)) { ?>
														<img  class = "next_display_picture3 show_image" id="pl_label_display-image" src="<?php echo esc_url($pl_label_icon_file); ?>" style = "<?php echo wp_kses($picdesign6, wp_kses_allowed_html('post')); ?>">
														<?php } else { ?>
														<img class= "display_images" id="display-images" src="" alt=""><?php } ?>
														<span class = "overlay-label" id="nextpictext3" style = "<?php echo wp_kses($textdesign6, wp_kses_allowed_html('post')); ?>">Sale</span>
													</div>
												</div>
												<span id="nextlabeltext3" style = "<?php echo wp_kses($textdesign6, wp_kses_allowed_html('post')); ?>">Sale</span>
											</div>
										</div>
									<ul>
										<li>
											<div class="size-settings">
												<label style="margin-top: 20px;"></label>
												<div class="size-text-label ">							
													<input type="radio"  id="label_large" name="label_size" value="large" onclick="return sizeClick(this);" 
													<?php 
													if (!empty($label_size)) {
														checked($label_size, 'large');} else {
														echo 'checked';
														} 
														?>
													>
													<span>Large &nbsp;&nbsp;</span>
													
													<input type="radio" id="label_medium" name="label_size" value="medium" onclick="return sizeClick(this);"
													<?php 
													if (!empty($label_size)) {
														checked($label_size, 'medium');} 
													?>
													>
													<span>Medium &nbsp;&nbsp;</span>
													
													<input type="radio" id="label_small" name="label_size" value="small" onclick="return sizeClick(this);" 
													<?php 
													if (!empty($label_size)) {
														checked($label_size, 'small');} 
													?>
													>
													<span>Small</span>
													
												</div>
											</div>
										</li>
										<li>
											<label style="margin-top: 20px;"><?php echo wp_kses('Test Text', wp_kses_allowed_html('post')); ?></label>
											<div class="control_label_test_text">
												<input type="text" name="label_test_text" id = "source_text" class="label_test_text" value ="Sale"/>
												<div class="tooltip">
													<span class="label_test_text_tooltip tips">
													</span>
													<span class="tooltiptext">Variables you can use in test text:
													{br} - new line</span>
												</div>
												<p><span>This field is only used to test the label display, text input doesn't get saved. Set up actual label texts in rules.</span></p>
											</div>
											
										</li>
										<li>
											<label style="margin-top: 20px;"><?php echo wp_kses('Customize Container', wp_kses_allowed_html('post')); ?></label>
											<div class="control" style="margin-top: 20px;">
												<div class="dropdown">
													<button class="dropbtn" onclick="return myFunction()">General&nbsp;&nbsp;<i class="caret-icon fa fa-caret-up"></i></button>												
													<div class="dropdown-content" id="myDropdown">
														<span>Color</span>
														<input type="text" name="container_color" disabled="disabled" id="choose_container_color" class ="shape_color" onchange="changeContainerCss(this.jscolor)" value="<?php echo wp_kses($pl_container_color, wp_kses_allowed_html('post')); ?>"  width: 6% style="width: 80px;">
														<button class="button jscolor {valueElement:'choose_container_color',styleElement:'choose_container_color'}">Select color</button>
														<input type="hidden" name="pl_container_color" id="container_color" value=""><br>
														<span>Width</span><input type="text" id = "container-input" name="label_container_width" class="label_container_width container-input" onchange="changeContainerCss()"value="<?php echo wp_kses($label_container_width, wp_kses_allowed_html('post')); ?>" placeholder = "px" onkeypress="return checkIt(event)"/><br>
														<span>Height</span><input type="text" id = "container-input"name="label_container_height" class="label_container_height container-input" onchange="changeContainerCss()"value="<?php echo wp_kses($label_container_height, wp_kses_allowed_html('post')); ?>"placeholder = "px" onkeypress="return checkIt(event)"/>
														
													</div>
													<div class="dropdown-content_medium" id="myDropdown_medium">
														<span>Color</span>
														<input type="text" name="container_color_medium" disabled="disabled" id="choose_container_color_medium" class ="shape_color_medium" onchange="changeContainerCss(this.jscolor)" value="<?php echo wp_kses($pl_container_color_medium, wp_kses_allowed_html('post')); ?>"  width: 6% style="width: 80px;">
														<button class="button jscolor {valueElement:'choose_container_color_medium',styleElement:'choose_container_color_medium'}">Select color</button>
														<input type="hidden" name="pl_container_color_medium" id="container_color_medium" value=""><br>
														<span>Width</span><input type="text" id = "container-input" name="label_container_width_medium" class="label_container_width_medium container-input" onchange="changeContainerCss()"value="<?php echo wp_kses($label_container_width_medium, wp_kses_allowed_html('post')); ?>" placeholder = "px" onkeypress="return checkIt(event)"/><br>
														<span>Height</span><input type="text" id = "container-input"name="label_container_height_medium" class="label_container_height_medium container-input" onchange="changeContainerCss()"value="<?php echo wp_kses($label_container_height_medium, wp_kses_allowed_html('post')); ?>"placeholder = "px" onkeypress="return checkIt(event)"/>
														
													</div>
													<div class="dropdown-content_small" id="myDropdown_small">
														<span>Color</span>
														<input type="text" name="container_color_small" disabled="disabled" id="choose_container_color_small" class ="shape_color_small" onchange="changeContainerCss(this.jscolor)" value="<?php echo wp_kses($pl_container_color_small, wp_kses_allowed_html('post')); ?>"  width: 6% style="width: 80px;">
														<button class="button jscolor {valueElement:'choose_container_color_small',styleElement:'choose_container_color_small'}">Select color</button>
														<input type="hidden" name="pl_container_color_small" id="container_color_small" value=""><br>
														<span>Width</span><input type="text" id = "container-input" name="label_container_width_small" class="label_container_width_small container-input" onchange="changeContainerCss()"value="<?php echo wp_kses($label_container_width_small, wp_kses_allowed_html('post')); ?>" placeholder = "px" onkeypress="return checkIt(event)"/><br>
														<span>Height</span><input type="text" id = "container-input"name="label_container_height_small" class="label_container_height_small container-input" onchange="changeContainerCss()"value="<?php echo wp_kses($label_container_height_small, wp_kses_allowed_html('post')); ?>"placeholder = "px" onkeypress="return checkIt(event)"/>
														
													</div>
												</div>
												<div class="con_adv_setting_css" style="margin-top: 20px;">
													<button class="con_adv_dropbtn"  onclick="return containerFun()"><i class="fa fa-chevron-circle-down"></i>&nbsp;&nbsp;Advanced Settings / CSS</button>
													<div class="tooltip">
														<span class="con_adv_dropbtn_tooltip tips">
														</span>
														<span class="tooltiptext">CSS parameters applied to the label container. Click the question mark to see more details in user guide.</span>
													</div>
													<div class="con_adv_dropbtn-content" id="con_css_area" style="display:none;">
														<textarea rows="2" cols="15" class="input-text wide-input " type="textarea" name="label_container_css" id="label_container_css"><?php echo wp_kses($label_container_css, wp_kses_allowed_html('post')); ?></textarea>
													</div>
													<div class="con_adv_dropbtn-content_medium" id="con_css_area_medium" style="display:none;">
														<textarea rows="2" cols="15" class="input-text wide-input " type="textarea" name="medium_label_container_css" id="medium_label_container_css"><?php echo wp_kses($medium_label_container_css, wp_kses_allowed_html('post')); ?></textarea>
													</div>
													<div class="con_adv_dropbtn-content_small" id="con_css_area_small" style="display:none;">
														<textarea rows="2" cols="15" class="input-text wide-input " type="textarea" name="small_label_container_css" id="small_label_container_css"><?php echo wp_kses($small_label_container_css, wp_kses_allowed_html('post')); ?></textarea>
													</div>
												</div>
											</div>
											
										</li>
										<li>
											<label style="margin-top: 20px;"><?php echo wp_kses('Customize Label', wp_kses_allowed_html('post')); ?></label>
											<div class="control" style="margin-top: 20px;">
												<div class="dropdown">
													<button class="dropbtn-text" onclick="return textLabelFunction()">Text&nbsp;&nbsp;<i class="caret-iconn fa fa-caret-up"></i></button>												
													<div class="dropdown-text-content" id="myDropdowntext" style="display: none;">
														<span>Color</span>
														<input type="text" name="adminlabeltext_color" disabled="disabled" id="choose_labeltext_color" onchange="changeLabelTextCss(this.jscolor)" value="<?php echo wp_kses($pl_adminlabeltext_color, wp_kses_allowed_html('post')); ?>"  width: 6% style="width: 80px;">
														<button class="button jscolor {valueElement:'choose_labeltext_color',styleElement:'choose_labeltext_color'}">Select color</button>
														<input type="hidden" name="pl_adminlabeltext_color" id="adminlabeltext_color" value=""><br>
														<span><?php echo wp_kses('Size', wp_kses_allowed_html('post')); ?></span><input type="text" id = "container-input"name="label_text_size" class="label_text_size container-input" onchange = "changeLabelTextCss()" value="<?php echo wp_kses($label_text_size, wp_kses_allowed_html('post')); ?>"placeholder = "px" onkeypress="return checkIt(event)"/>
														
													</div>
													<div class="dropdown-text-content_medium" id="myDropdowntext_medium" style="display: none;">
														<span>Color</span>
														<input type="text" name="adminlabeltext_color_medium" disabled="disabled" id="choose_labeltext_color_medium" onchange="changeLabelTextCss(this.jscolor)" value="<?php echo wp_kses($pl_adminlabeltext_color_medium, wp_kses_allowed_html('post')); ?>"  width: 6% style="width: 80px;">
														<button class="button jscolor {valueElement:'choose_labeltext_color_medium',styleElement:'choose_labeltext_color_medium'}">Select color</button>
														<input type="hidden" name="pl_adminlabeltext_color_medium" id="adminlabeltext_color_medium" value=""><br>
														<span><?php echo wp_kses('Size', wp_kses_allowed_html('post')); ?></span><input type="text" id = "container-input"name="label_text_size_medium" class="label_text_size_medium container-input" onchange = "changeLabelTextCss()" value="<?php echo wp_kses($label_text_size_medium, wp_kses_allowed_html('post')); ?>"placeholder = "px" onkeypress="return checkIt(event)"/>
														
													</div>
													<div class="dropdown-text-content_small" id="myDropdowntext_small" style="display: none;">
														<span>Color</span>
														<input type="text" name="adminlabeltext_color_small" disabled="disabled" id="choose_labeltext_color_small" onchange="changeLabelTextCss(this.jscolor)" value="<?php echo wp_kses($pl_adminlabeltext_color_small, wp_kses_allowed_html('post')); ?>"  width: 6% style="width: 80px;">
														<button class="button jscolor {valueElement:'choose_labeltext_color_small',styleElement:'choose_labeltext_color_small'}">Select color</button>
														<input type="hidden" name="pl_adminlabeltext_color_small" id="adminlabeltext_color_small" value=""><br>
														<span><?php echo wp_kses('Size', wp_kses_allowed_html('post')); ?></span><input type="text" id = "container-input"name="label_text_size_small" class="label_text_size_small container-input" onchange = "changeLabelTextCss()" value="<?php echo wp_kses($label_text_size_small, wp_kses_allowed_html('post')); ?>"placeholder = "px" onkeypress="return checkIt(event)"/>
														
													</div>
												</div>
												<div class="con_adv_setting_css" style="margin-top: 20px;">
													<button class="label_adv_dropbtn" onclick="return labelFun()"><i class="fa fa-chevron-circle-down"></i>&nbsp;&nbsp;Advanced Settings / CSS</button>
													<div class="tooltip">
														<span class="label_adv_dropbtn_tooltip tips">
														</span>
														<span class="tooltiptext">Other CSS parameters. Click the question mark to see more details in user guide.</span>
													</div>
													<div class="text_adv_dropbtn-content" id="text_css_area" style="display:none;">
														<textarea rows="2" cols="15" class="input-text wide-input " type="textarea" name="label_text_css" id ="label_text_css"><?php echo wp_kses($label_text_css, wp_kses_allowed_html('post')); ?></textarea>
													</div>
													<div class="text_adv_dropbtn-content_medium" id="text_css_area_medium" style="display:none;">
														<textarea rows="2" cols="15" class="input-text wide-input " type="textarea" name="medium_label_text_css" id ="medium_label_text_css"><?php echo wp_kses($medium_label_text_css, wp_kses_allowed_html('post')); ?></textarea>
													</div>
													<div class="text_adv_dropbtn-content_small" id="text_css_area_small" style="display:none;">
														<textarea rows="2" cols="15" class="input-text wide-input " type="textarea" name="small_label_text_css" id ="small_label_text_css"><?php echo wp_kses($small_label_text_css, wp_kses_allowed_html('post')); ?></textarea>
													</div>
												</div>
											</div>											
										</li>
									</ul>
									<div class="submit">
										<?php 
										if (isset($_GET['id']) && !empty($_GET['id'])) {
											$id = sanitize_text_field($_GET['id']);
											?>
											<input name="label_id" type="hidden" id= "<?php echo wp_kses($id, wp_kses_allowed_html('post')); ?>" value="<?php echo wp_kses($id, wp_kses_allowed_html('post')); ?>"><input type="submit" class="button button-primary" value="<?php echo wp_kses('Update', wp_kses_allowed_html('post')); ?>" name="setting_label_submit" onclick="return label_saved(event)"/>
											<?php
										} else {
;
											?>
															
											<input type="submit" class="button button-primary" value="<?php echo wp_kses('Save', wp_kses_allowed_html('post')); ?>" name="setting_label_submit" onclick="return label_saved(event)"/><?php } ?>	
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>		
		<?php
	}	
	public static function aw_pl_save_label_form() {
		global $wpdb;
		$id = '';
		$label_shape_type = '';
		$pl_label_icon_file = '';
		$url =  admin_url() . 'admin.php?page=new-label';
		if (isset($_POST['label_id']) && !empty($_POST['label_id'])) {
			$id = sanitize_text_field($_POST['label_id']);
		}
		if (isset($_POST['rdpl_new_label_nonce'])) {
			$aw_pl_new_label_nonce = sanitize_text_field($_POST['rdpl_new_label_nonce']);
		}

		if ( !wp_verify_nonce( $aw_pl_new_label_nonce, 'aw_pl_save_label_form' )) {
			wp_die('Our Site is protected');
		}
		if (isset($_POST['setting_label_submit'])) {
			if (isset($_POST['label_name'])) {
				$label_name = sanitize_text_field($_POST['label_name']);
			} else {
				$label_name = '';
			}
			
			if (isset($_POST['label_position'])) {
				$position = sanitize_text_field($_POST['label_position']);
				if ('upper_left' == $position) {
					$label_position = 'upper_left';
				} else if ('upper_right' == $position) {
					$label_position = 'upper_right';
				} else if ('lower_left' == $position) {
					$label_position = 'lower_left';
				} else if ('lower_right' == $position) {
					$label_position = 'lower_right';
				} else if ('next_price' == $position) {
					$label_position = 'next_price';
				}
			} else {
				$label_position = 'upper_left';
			}

			if (isset($_POST['label_type'])) {
				$type = sanitize_text_field($_POST['label_type']);
				if ('shape' == $type) {
					$label_type = 'shape';
					if (isset($_POST['label_shape_type']) && '' != sanitize_text_field($_POST['label_shape_type'])) {
						$label_shape_type = sanitize_text_field($_POST['label_shape_type']);
					}
				} else if ('picture' == $type) {
					$label_type = 'picture';
				} else if ('text' == $type) {
					$label_type = 'text';
				}
			} else {
				$label_type = 'shape';
			}

			if (isset($_POST['label_size'])) {
				$size = sanitize_text_field($_POST['label_size']);
				if ('large' == $size) {
					$label_size = 'large';
				} else if ('medium' == $size) {
					$label_size = 'medium';
				} else if ('small' == $size) {
					$label_size = 'small';
				}
			} else {
				$label_size = 'large';
			}

			if (isset($_POST['label_container_css'])) {
				$value = sanitize_text_field($_POST['label_container_css']);
				$label_container_css = $value; 
			}

			if (isset($_POST['label_text_css'])) {
				$value = sanitize_text_field($_POST['label_text_css']);
				$label_text_css =  $value;
			}

			if (isset($_POST['medium_label_container_css'])) {
				$medium_label_container_css = sanitize_text_field($_POST['medium_label_container_css']);
			}

			if (isset($_POST['medium_label_text_css'])) {
				$medium_label_text_css =  sanitize_text_field($_POST['medium_label_text_css']);
			}

			if (isset($_POST['small_label_container_css'])) {
				$small_label_container_css = sanitize_text_field($_POST['small_label_container_css']);
			}

			if (isset($_POST['small_label_text_css'])) {
				$small_label_text_css =  sanitize_text_field($_POST['small_label_text_css']);
			}

			/*insert image code*/
			$Save_FILES=$_FILES;
			if (isset($_POST['category_icon_file'])&& !empty($_POST['category_icon_file'])) {
				
				$wordpress_upload_dir = wp_upload_dir();    
				if (isset($_FILES['pl_label_icon']) && !empty($_FILES['pl_label_icon'])) {
					$image = json_encode($_FILES);
					$image = json_decode($image, true);
					$image = array_filter($image['pl_label_icon']);		
										
					if (!empty($image['tmp_name'] )) {
						$extension = pathinfo($image['name'], PATHINFO_EXTENSION);
						$allowed = array('jpg','jpeg','JPG','JPEG','png','PNG','bmp','BMP','gif','GIF');
						$new_file_path = $wordpress_upload_dir['path'] . '/' . $image['name'];
						$new_file_mime = mime_content_type( $image['tmp_name'] );
					}
				}

				if (!empty($image['name']) && in_array($extension, $allowed)  && in_array( $new_file_mime, get_allowed_mime_types())) {

					$_FILES = array('upload_file' => $image);
					$attachment_id = media_handle_upload('upload_file', 0);
					$pl_label_icon_file = wp_get_attachment_url( $attachment_id );
				} else {
					self::aw_pl_save_flash_notice( __('Invalid image type or Invalid content type'), 'error', true );
				}
				if (empty($pl_label_icon_file)) {
					$pl_label_icon_file = sanitize_text_field($_POST['category_icon_file']);
				}
			} else {
				if (!empty($_FILES['pl_label_icon']['name'])) {
					if (isset($_FILES['pl_label_icon']) && !empty($_FILES['pl_label_icon'])) {
						$wordpress_upload_dir = wp_upload_dir();             
						$image = json_encode($_FILES);
						$image = json_decode($image, true);
						$image = array_filter($image['pl_label_icon']);

						$extension = pathinfo($image['name'], PATHINFO_EXTENSION);
						$allowed = array('jpg','jpeg','JPG','JPEG','png','PNG','bmp','BMP','gif','GIF');
						$new_file_path = $wordpress_upload_dir['path'] . '/' . $image['name'];
						$new_file_mime = mime_content_type( $image['tmp_name'] );
					}

					if (!empty($image['name']) && in_array($extension, $allowed)  && in_array( $new_file_mime, get_allowed_mime_types())) {
						$_FILES = array('upload_file' => $image);
						$attachment_id = media_handle_upload('upload_file', 0);
						$pl_label_icon_file = wp_get_attachment_url( $attachment_id );	            	
					} else {
						self::aw_pl_save_flash_notice( __('Invalid image type or Invalid content type'), 'error', true );
					}
				}
			}
			$last_modified = 		gmdate('Y-m-d H:i:s');	
			$db_table = $wpdb->prefix . 'aw_pl_product_label';
			$post_array = array(
				'name' 							=> $label_name,
				'position' 						=> $label_position,
				'status' 						=> 1,
				'type' 							=> $label_type,
				'shape_type'					=> $label_shape_type,
				'label_size'					=> $label_size,
				'label_image'					=> $pl_label_icon_file,
				'label_container_css'			=> $label_container_css,
				'medium_label_container_css'	=> $medium_label_container_css,
				'small_label_container_css'		=> $small_label_container_css,
				'label_text_css'				=> $label_text_css,
				'medium_label_text_css'			=> $medium_label_text_css,
				'small_label_text_css'			=> $small_label_text_css,
				'last_updated' 					=> $last_modified	
					);

			if ('' != $id ) {
				$result = $wpdb->update($db_table, $post_array, array('id'=>$id));
				self::aw_pl_save_flash_notice( __('Label updated successfully'), 'success', true );
				$url =  admin_url() . 'admin.php?page=new-label&action=edit&id=' . $id;		 	
			} else {
				$result = $wpdb->insert($db_table, $post_array);
				self::aw_pl_save_flash_notice( __('Label inserted successfully'), 'success', true );
				$url =  admin_url() . 'admin.php?page=aw_pl_label_page';
			}
		}	
		wp_redirect($url);
	}
}

function aw_pl_label_row( $id) {
	global $wpdb;
	$single_label = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pl_product_label WHERE id = %d ", "{$id}") );
	return $single_label;
}	
