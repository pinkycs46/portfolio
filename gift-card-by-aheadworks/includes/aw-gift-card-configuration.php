<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
 
class AwGiftCardConfiguration {

	public static function aw_giftcard_configuration_html() {
		$nonce = wp_create_nonce( 'awgiftcard_admin_nonce' );
		$settings = get_option('aw_wgc_configuration');

		$setting 	= maybe_unserialize($settings);
		$status 	= '';
		$coupon_restriction 	= '';
		$giftcard_restriction 	= '';
		$expiration 		= '';
		$length 			= '';
		$format 			= '';
		$coupon_checked 	= '';
		$giftcard_checked 	= '';
		$completed 			= '';
		$pending 			= '';
		$processing			= '';
		$alphanumeric 		= '';
		$alphabetic			= '';
		$numeric 			= '';
		$prefix 			= '';
		$suffix 			= '';
		$dash_position 		= '';
		if (!empty($setting)) {
			if (isset($setting['status'])) {
				$status = $setting['status'];
			}
			if (isset($setting['coupon_restriction'])) {
				$coupon_restriction = $setting['coupon_restriction'];
				$coupon_checked 	= 'checked="checked"';
			}
			if (isset($setting['giftcard_restriction']) && '' != $setting['giftcard_restriction'] ) {
				$giftcard_restriction 	= $setting['giftcard_restriction'];
				$giftcard_checked 		= 'checked="checked"';
			}
			if (isset($setting['expiration'])) {
				$expiration = $setting['expiration'];
			}
			if (isset($setting['length'])) {
				$length 	= $setting['length'];
			}
			if (isset($setting['format'])) {
				$format 	= $setting['format'];
				if ('alphanumeric' == $format) {
					$alphanumeric = 'selected="selected"';
				}
				if ('alphabetic' == $format) {
					$alphabetic = 'selected="selected"';
				}
				if ('numeric' == $format) {
					$numeric = 'selected="selected"';
				}
			}
			if (isset($setting['prefix'])) {
				$prefix 	= $setting['prefix'];
			}
			if (isset($setting['suffix'])) {
				$suffix 	= $setting['suffix'];
			}
			if (isset($setting['dash_position'])) {
				$dash_position = $setting['dash_position'];
			}	
		}
		?>
		<div class="wrap">
			<div class="spw-rw clearfix">
				<div class="panel-box temp-design">
					<div class="page-title">
						<h1>Configuration</h1>
					</div>
					<?php if (!empty(get_option('aw_wgc_congfig_saved'))) { ?>
					<div id="message" class="updated">
						<p><strong><?php echo esc_html(get_option('aw_wgc_congfig_saved')); ?></strong></p>
					</div>
					<?php delete_option( 'aw_wgc_congfig_saved');} ?>
					
					<form id="aw_wgc_config_form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
						<?php wp_nonce_field( 'aw_wgc_save_configuration_form', 'awgiftcard_config_admin_nonce' ); ?>
						<input type="hidden" name="action" value="aw_wgc_save_configuration_form">	

						<table class="form-table" role="presentation">
							<tbody>
								<tr>
									<td colspan="2"><h2>Gift Card Order Settings</h2></td>
								</tr>
								<tr class="form-field form-required">
									<th scope="row">
										<label for="order_status">Order status when Gift Card should be sent to a recipient
											<?php
												$order_status_wc 	= wc_get_order_statuses();
												$aw_gc_order_status = array_diff($order_status_wc, ['Failed', 'Cancelled', 'Refunded']);
											?>
										</label>
									</th>
									<td>
										<select name="status">
											<?php
											foreach ($aw_gc_order_status as $key => $select_status) {
												$status_selected = '';
												if ($key == $status) {
													$status_selected = 'selected="selected"';
												}
												?>
												<option value="<?php echo wp_kses_post($key); ?>" <?php echo wp_kses_post($status_selected); ?>><?php echo wp_kses_post($select_status); ?></option>
											<?php
											}
											?>
										</select>
										<p class="description">Gift Card Code will be generated accordingly</p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for=restrict_gift_card">Restrict Gift Card application to Gift Card product</label>
									</th>
									<td>
										<input type="checkbox" name="giftcard_restriction" id="" value="1" <?php echo wp_kses_post($giftcard_checked); ?>>
										<p class="description">Check this box if the gift card shouldn't be applied when the Cart contains Gift Card product</p>
									</td>
								</tr>
								<tr class="form-field form-required">
									<th scope="row">
										<label for="aw_wgc_expiration">Gift Card Expiration, in days</label>
									</th>
									<td>
										<input name="expiration" id = "expiration" class="aw_wgc_expiration" type="text"  value="<?php echo wp_kses_post($expiration); ?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60" onkeypress="return aw_gc_checkItExp(event)">
										<p class="description aw_wgc_expiration_error" id="tagline-description">Enter a value greater than 0 or leave empty</p>
									</td>
								</tr>
								<tr align="left">
									<td colspan="2"><h2>Gift card Code Pattern</h2></td>
								</tr>
								<tr class="form-field form-required">
									<th scope="row">
										<label for="aw_wgc_length">Code Length</label>
									</th>
									<td>
										<input name="length" type="text" id="aw_wgc_length" value="<?php echo wp_kses_post($length); ?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60" onkeypress="return aw_gc_checkItLen(event)">
										<p class="aw_wgc_length_error"></p>
									</td>
								</tr>
								<tr class="form-field form-required">
									<th scope="row">
										<label for="aw_wgc_format">Code Format</label>
									</th>
									<td>
										<select name="format">
											<option <?php echo wp_kses_post($alphanumeric); ?> value="alphanumeric">Alphanumeric</option>
											<option <?php echo wp_kses_post($alphabetic); ?> value="alphabetic">Alphabetic</option>
											<option <?php echo wp_kses_post($numeric); ?> value="numeric">Numeric</option>
										</select>
									</td>
								</tr>	
								<tr class="form-field form-required">
									<th scope="row">
										<label for="aw_wgc_prefix">Code Prefix</label>
									</th>
									<td>
										<input name="prefix" type="text" id="aw_wgc_prefix" value="<?php echo wp_kses_post($prefix); ?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60">
									</td>
								</tr>
								<tr class="form-field form-required">
									<th scope="row">
										<label for="aw_wgc_suffix">Code Suffix</label>
									</th>
									<td>
										<input name="suffix" type="text" id="aw_wgc_suffix" value="<?php echo wp_kses_post($suffix); ?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60">
									</td>
								</tr>								
								<tr class="form-field form-required">
									<th scope="row">
										<label for="aw_wgc_dash_position">Dash Every X Characters</label>
									</th>
									<td>
										<input name="dash_position" type="text" id="aw_wgc_dash_position" value="<?php echo wp_kses_post($dash_position); ?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60" onkeypress="return aw_gc_checkItPos(event)">
										<p class="description aw_wgc_dash_position_error"> If empty - no separation</p>
									</td>
								</tr>
							</tbody>
						</table>
						<p class="submit">
							<input type="submit" name="aw_wgc_save_conifg" id="aw_wgc_save_conifg" class="button button-primary" value="Save">
						</p>
					</form>	
				</div>
			</div>
		</div>
		<?php 
	}

	public static function aw_save_gifcard_configuration_form() {

		global $wpdb;
		$url =  admin_url() . 'admin.php?page=aw_gift_card_configration';

		if (isset($_POST['awgiftcard_config_admin_nonce'])) {
			$awgiftcard_config_admin_nonce = sanitize_text_field($_POST['awgiftcard_config_admin_nonce']);
		}
		if ( !wp_verify_nonce( $awgiftcard_config_admin_nonce, 'aw_wgc_save_configuration_form' )) {
			wp_die('Our Site is protected');
		}

		if (isset($_POST['aw_wgc_save_conifg'])) {
			
			$post_array = array();
			if (isset($_POST['status'])) {
				$post_array['status'] = sanitize_text_field($_POST['status']);
			}
			if (isset($_POST['giftcard_restriction'])) {
				$post_array['giftcard_restriction'] = sanitize_text_field($_POST['giftcard_restriction']);
			}
			if (isset($_POST['expiration'])) {
				$post_array['expiration'] = sanitize_text_field($_POST['expiration']);
			}
			if (isset($_POST['length'])) {
				$post_array['length'] = sanitize_text_field($_POST['length']);
			}
			if (isset($_POST['format'])) {
				$post_array['format'] = sanitize_text_field($_POST['format']);
			}
			if (isset($_POST['prefix'])) {
				$post_array['prefix'] = sanitize_text_field($_POST['prefix']);
			}
			if (isset($_POST['suffix'])) {
				$post_array['suffix'] = sanitize_text_field($_POST['suffix']);
			}
			if (isset($_POST['dash_position'])) {
				$post_array['dash_position'] = sanitize_text_field($_POST['dash_position']);
			}
			$settings = maybe_serialize($post_array);
			update_option('aw_wgc_configuration', $settings);
			update_option('aw_wgc_congfig_saved', 'Your settings have been saved');
			wp_redirect($url);
		}
	}
}
?>
