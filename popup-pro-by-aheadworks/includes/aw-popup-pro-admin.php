<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

require_once(plugin_dir_path(__FILE__) . '/aw-subscribers.php');
require_once(plugin_dir_path(__FILE__) . '/aw-subscribe-popup-pro-fonts.php');
class AwPopupProAdmin {

	public static function aw_self_deactivate_notice() {
		/** Display an error message when WooComemrce Plugin is missing **/
		?>
		<div class="notice notice-error">
			<p>Please install and activate WooComemrce plugin before activating Popup Pro plugin.</p>
		</div>
		<?php
	}

	public static function aw_popup_pro_meta_box_callback( $post, $metabox) {
		/** Show Meta Boxes / Fileds on admin Add New and Edit Linked Product Popup **/
		$popup_pro_title 			= get_post_meta($post->ID, 'popup_pro_title', true);
		$popup_pro_type_display 	= get_post_meta($post->ID, 'popup_pro_type_display', true);
		$popup_pro_maximum_product 	= get_post_meta($post->ID, 'popup_pro_maximum_product', true);
		$popup_pro_cookie_lifetime 	= get_post_meta($post->ID, 'popup_pro_cookie_lifetime', true);
		$popup_pro_priority 		= get_post_meta($post->ID, 'popup_pro_priority', true);
		$popup_pro_subscribe_button	= get_post_meta($post->ID, 'popup_pro_subscribe_button', true);

		wp_nonce_field('popup_pro_nonce_action', 'popup_pro_nonce_name');

		if ('' == $popup_pro_maximum_product ) {
			$popup_pro_maximum_product = 0;
		}
		?>
		<div class="popup_pro_container" id="popup_pro_container">
			<ul>
				<li>
					<label>Popup title:</label>
					<div class="control">
						<textarea name="popup_pro_title" id="popup_pro_title" class="txt_required"><?php echo esc_html($popup_pro_title); ?></textarea>
						<p>The title is displayed on the top of the popup</p>
					</div>
				</li>
				<li>
					<label>What to display:</label>
					<div class="control">
						<select name="popup_pro_type_display" id="popup_pro_type_display" class="input-text required txt_required">
							<option value="">Select</option>
							<option value="upsells" <?php selected($popup_pro_type_display, 'upsells'); ?>>Up-Sells</option>
							<option value="crosssells" <?php selected($popup_pro_type_display, 'crosssells'); ?>>Cross-Sells</option>
							<option value="relatedproducts" <?php selected($popup_pro_type_display, 'relatedproducts'); ?>>Related Products</option>
						</select>
					</div>
				</li>
				<li>
					<label>Maximum number of products to display:</label>
					<div class="control">
						<input type="text" name="popup_pro_maximum_product" id="popup_pro_maximum_product" class="input-text required txt_required" value="<?php echo esc_html($popup_pro_maximum_product); ?>" onkeypress="return checkIt(event)"><br/>
						<p>Set to Zero to display all linked products</p>
					</div>
				</li>
				<li>
					<label>Cookie Lifetime, minutes:</label>
					<div class="control">
						<input type="text" name="popup_pro_cookie_lifetime" id="popup_pro_cookie_lifetime" class="input-text required txt_required" value="<?php echo esc_html($popup_pro_cookie_lifetime); ?>" onkeypress="return checkIt(event)"><br/>
						<p>Once popup is shown to the customer, it will not be shown to them again within the next X minutes</p>
					</div>
				</li>
				<li>
					<label>Priority:</label>
					<div class="control">
						<input type="text" name="popup_pro_priority" id="popup_pro_priority" class="input-text required txt_required" value="<?php echo esc_html($popup_pro_priority); ?>" onkeypress="return checkIt(event)">
						<p>0 - the highest priority</p>
					</div>
				</li>
			</ul>
		</div>
		<?php
		add_thickbox();
		$url = add_query_arg( array('action' => 'popup_pro_preview_modal_box',), admin_url( 'admin.php' ));
		echo '<div class="action-rw clearfix"><a href="' . esc_url($url) . '" class="button button-primary thickbox" id="pop_pro_product_preview">Preview</a></div>';	
		?>
			<input type="hidden" value="<?php echo esc_html($url); ?>" name="hidden_popup-link" id="hidden_popup_link"> 
		<?php
	}

	public static function aw_popup_pro_admin_sub_page() {
		/** Show Images and Links on Create New Popup Page **/
		?>
		<style>
			a:focus {
	box-shadow: none;
	}
		</style>
		<div class="wrap">
		<h1 class="wp-heading-inline">Create new popup</h1>
		<div>
			<a href="<?php echo esc_html(site_url()); ?>/wp-admin/post-new.php?post_type=popup-pro"><img src="<?php echo esc_html(plugins_url('/admin/templates/images/aw_linked.png', dirname(__FILE__) )); ?>"></a>
			<svg style="vertical-align:top;margin-top:35px;" aria-label="[title]" role="img" focusable="false" class="dashicon dashicons-info-outline" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><title>Click button on the left and create a popup with Upsells, Cross-Sells or Related products</title><path d="M9 15h2V9H9v6zm1-10c-.5 0-1 .5-1 1s.5 1 1 1 1-.5 1-1-.5-1-1-1zm0-4c-5 0-9 4-9 9s4 9 9 9 9-4 9-9-4-9-9-9zm0 16c-3.9 0-7-3.1-7-7s3.1-7 7-7 7 3.1 7 7-3.1 7-7 7z"></path></svg>
		</div>
		<br/>
		<div>
			<a href="<?php echo esc_html(site_url()); ?>/wp-admin/post-new.php?post_type=popup-pro-subscribe"><img src="<?php echo esc_html(plugins_url('/admin/templates/images/aw-subscribe.png', dirname(__FILE__) )); ?>"></a>
			<svg style="vertical-align:top;margin-top:35px;" aria-label="[title]" role="img" focusable="false" class="dashicon dashicons-info-outline" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><title>Click button on the left and create a popup with a subscription form</title><path d="M9 15h2V9H9v6zm1-10c-.5 0-1 .5-1 1s.5 1 1 1 1-.5 1-1-.5-1-1-1zm0-4c-5 0-9 4-9 9s4 9 9 9 9-4 9-9-4-9-9-9zm0 16c-3.9 0-7-3.1-7-7s3.1-7 7-7 7 3.1 7 7-3.1 7-7 7z"></path></svg>
		</div>
		<div>&nbsp;</div>
		</div>
		<?php 				
	}

	public static function aw_subscribers_list_include() {
		/** Display Subscribers Listing Page **/
		global $wpdb;

		$table = new AwSubscribers();

		if (isset($_GET['status'])) {
			$status= sanitize_text_field($_GET['status']);
			$table->prepare_items($status);
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
		if ('trash' === $table->current_action() && isset($_REQUEST['id'])) {
			/* translators: number count  */
			$message = '<div class="updated below-h2 cutommessage"  ><p>' . sprintf(__('%d Subscriber moved to the Trash', 'Subscriber List'), intval($count)) . '</p></div>';
		}
		if ('delete' === $table->current_action() && isset($_REQUEST['id'])) {
			/* translators: number count  */
			$message = '<div class="updated below-h2 cutommessage"  ><p>' . sprintf(__('%d Subscriber permanently deleted.', 'Subscriber List'), intval($count)) . '</p></div>';
		}
		if ('untrash' === $table->current_action() && isset($_REQUEST['id'])) {
			/* translators: number count  */
			$message = '<div class="updated below-h2 cutommessage"  ><p>' . sprintf(__('%d Subscriber restore.', 'Subscriber List'), intval($count)) . '</p></div>';
		}
		?>
		<div class="wrap">
			<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
			<h1 class="wp-heading-inline"><?php esc_html_e('Subscribers', 'Subscriber List'); ?></h1>
			<?php echo wp_kses($message, 'post'); ?>
			<hr class="wp-header-end">
			<ul class="subsubsub">
				<li class="all"><a href="edit.php?post_type=popup-pro&page=subscribers-list" class="current" aria-current="page">All <span class="count">(<?php echo intval($count_all); ?>)</span></a> |</li>
				<li class="trash"><a href="edit.php?status=0&post_type=popup-pro&page=subscribers-list">Trash <span class="count">(<?php echo intval($count_trashed); ?>)</span></a></li>
			</ul>
			<form id="posts-filter" method="get">
				<p class="search-box">
					<input type="hidden" name="post_type" class="post_type_page" value="popup-pro">	 
					<input type="hidden" name="page" class="page" value="subscribers-list">	
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
					<input type="submit" id="search-submit" class="button" value="Search Subscribers">
				</p>
			</form>

			<form id="subscriber-table" method="GET">
				<input type="hidden" name="post_type" value="<?php echo esc_html('popup-pro'); ?>"/>
				<input type="hidden" name="page" value="<?php echo isset($_REQUEST['page']) ? wp_kses($_REQUEST['page'], 'post') : '' ; ?>"/>
				<?php $table->display(); ?>
			</form>
		</div>
		<?php
	}

	public static function aw_popup_pro_subscribe_meta_box_callback( $post, $metabox) {
		/** Show Meta Boxes / Fileds on admin Add New and Edit Subscriber Popup **/

		if (isset($_GET['post'])) {
			$get_post_id = sanitize_text_field($_GET['post']);
		} else {
			$get_post_id = 0;
		}

		$popup_pro_subscribe_title 			= get_post_meta($post->ID, 'popup_pro_subscribe_title', true);
		$popup_pro_subscribe_subtitle 		= get_post_meta($post->ID, 'popup_pro_subscribe_subtitle', true);
		$popup_pro_subscribe_button 		= get_post_meta($post->ID, 'popup_pro_subscribe_button', true);
		$popup_pro_subscribe_design 		= get_post_meta($post->ID, 'popup_pro_subscribe_design', true);
		$popup_pro_subscribe_when_display 	= get_post_meta($post->ID, 'popup_pro_subscribe_when_display', true);
		$popup_pro_subscribe_cookie_lifetime = get_post_meta($post->ID, 'popup_pro_subscribe_cookie_lifetime', true);
		$popup_pro_subscribe_x_equalto 		= get_post_meta($post->ID, 'popup_pro_subscribe_x_equalto', true);
		$popup_pro_subscribe_template		= get_post_meta($post->ID, 'popup_pro_subscribe_template', true);
		$popup_pro_subscribe_image			= get_post_meta($post->ID, 'popup_pro_subscribe_image', true);

		if ( '' == $popup_pro_subscribe_design) {
			$popup_pro_subscribe_design = array();
		}

		wp_nonce_field('popup_pro_nonce_action', 'popup_pro_nonce_name');
		?>

		<div class="popup_pro_container" id="popup_pro_container">
			<div class="subscribe-popup-wrapper">
				<div class="spw-rw clearfix">
					<div class="panel-box temp-layout">
						<h3>Template</h3>
						<div class="panel-body">
						<div class="temp-lay-select">
							<label>Select Template</label>
							<select name="popup_pro_subscribe_template" id="popup_pro_subscribe_template" class="required" onchange="imagechange(this,'<?php echo esc_url(plugins_url('/admin/templates/images/', dirname(__FILE__))); ?>')">
								<option value="aw-popup-template01" data-value="aw-popup-template01.jpg" 
								<?php 
								if (!empty($popup_pro_subscribe_template)) {
									selected($popup_pro_subscribe_template, 'aw-popup-template01');} 
								?>
								>Template1</option>
								<option value="aw-popup-template02" data-value="aw-popup-template02.jpg" 
								<?php 
								if (!empty($popup_pro_subscribe_template)) {
									selected($popup_pro_subscribe_template, 'aw-popup-template02');} 
								?>
								>Template2</option>
								<option value="aw-popup-template03" data-value="aw-popup-template03.jpg" 
								<?php 
								if (isset($popup_pro_subscribe_template)) {
									selected($popup_pro_subscribe_template, 'aw-popup-template03');} 
								?>
								>Template3</option>
								<option value="aw-popup-template04" data-value="aw-popup-template04.jpg" 
								<?php 
								if (isset($popup_pro_subscribe_template)) {
									selected($popup_pro_subscribe_template, 'aw-popup-template04');} 
								?>
								>Template4</option>
								<option value="aw-popup-template05" data-value="aw-popup-template05.jpg" 
								<?php 
								if (isset($popup_pro_subscribe_template)) {
									selected($popup_pro_subscribe_template, 'aw-popup-template05');} 
								?>
								>Template5</option>
								<option value="aw-popup-template06" data-value="aw-popup-template06.jpg" 
								<?php 
								if (isset($popup_pro_subscribe_template)) {
									selected($popup_pro_subscribe_template, 'aw-popup-template06');} 
								?>
								>Template6</option>
								<option value="aw-popup-template07" data-value="aw-popup-template07.jpg" 
								<?php 
								if (isset($popup_pro_subscribe_template)) {
									selected($popup_pro_subscribe_template, 'aw-popup-template07');} 
								?>
								>Template7</option>
							</select>
						</div>
						<div class="temp-lay-selected">
							<img style="border:1px solid" id="imageplaceholder" src="
							 <?php 
								if (!empty($popup_pro_subscribe_image)) {
									echo  esc_url(plugins_url('/admin/templates/images/' . $popup_pro_subscribe_image, dirname(__FILE__))) ;
								} else {
									echo esc_url(plugins_url('/admin/templates/images/aw-popup-template01.jpg', dirname(__FILE__))) ;} 
								?>
								"  alt="">
							 <input type="hidden" id="popup_pro_subscribe_image" value="
							 <?php 
								if (!empty($popup_pro_subscribe_image)) {
									echo wp_kses($popup_pro_subscribe_image, 'post'); } 
								?>
								" name="popup_pro_subscribe_image">
						</div>				
					</div>
					</div>

					<div class="panel-box temp-content padd20">
						<h3>Content</h3>
						<div class="panel-body">
						<ul>
							<li id="title_tab">
								<label>Popup Title</label>
								<div class="control">
									<textarea name="popup_pro_subscribe_title" id="popup_pro_subscribe_title" class="tarea small" placeholder=""><?php echo wp_kses($popup_pro_subscribe_title, 'post'); ?></textarea>
								</div>
							</li>
							<li id="subtitle_tab">
								<label>Popup Subtitle</label>
								<div class="control">
									<textarea name="popup_pro_subscribe_subtitle" id="popup_pro_subscribe_subtitle" class="tarea" placeholder=""><?php echo wp_kses($popup_pro_subscribe_subtitle, 'post'); ?></textarea>
								</div>
							</li>
							<li id="subscribebutton_tab">
								<label>Subscribe button</label>
								<div class="control">
									<input type="text" name="popup_pro_subscribe_button" id="popup_pro_subscribe_button" class="input-txt required" value="<?php echo wp_kses($popup_pro_subscribe_button, 'post'); ?>" >
								</div>
							</li>
						</ul>
					</div>
					</div>
				</div>

				<div class="spw-rw clearfix">
					<div class="panel-box temp-design">
						<h3>Design</h3>
						<div class="panel-body">
							<div class="tab">
								<button class="tablinks" id="design_title_tab"  onclick="openTab(event, 'title-tab')">Title</button>
								<button class="tablinks" id="design_subtitle_tab" onclick="openTab(event, 'subtitle-tab')">Subtitle</button>
								<button class="tablinks" onclick="openTab(event, 'emailform-tab')">Email Form</button>
								<button class="tablinks" id="design_subscribebutton_tab" onclick="openTab(event, 'subscribebutton-tab')">Subscribe button</button>
								<button class="tablinks" onclick="openTab(event, 'closebutton-tab')">Close button</button>
								<button class="tablinks" onclick="openTab(event, 'background-tab')">Background</button>
							</div> 

						<?php
							$designs = array('title','subtitle','emailform','subscribebutton','closebutton','background');
							$get_fonts = new AwSubscribePopupProFonts();
							$fonts = $get_fonts->fonts;
						foreach ($designs as $design) :
							?>
						<div id="<?php echo wp_kses($design, 'post') . '-tab'; ?>" class="tabcontent">
						<ul>
							<?php 
							if ('title'==$design || 'subtitle'==$design || 'emailform'==$design || 'subscribebutton'==$design) {
								?>
							<li>
								<label>Font</label>
								<select name="design[<?php echo wp_kses_post($design); ?>][fontname]"  onchange="return selectweight(this,'<?php echo esc_url(site_url()); ?>','<?php echo esc_attr($design); ?>');" id="<?php echo esc_attr($design) . '_font-family'; ?>" >
								<?php 
								$i=0;
								foreach ($fonts as $font) {
									$optionval = $font;
									$selected = '';
									if (isset($popup_pro_subscribe_design[$design]['fontname']) && $optionval == $popup_pro_subscribe_design[$design]['fontname']) {
										$selected = 'selected=selected';
									}	
									?>
									<option <?php echo wp_kses_post($selected); ?> value="<?php echo wp_kses_post($optionval); ?>"><?php echo wp_kses_post($optionval); ?></option>
									<?php 
									$i++;
								}
								?>
								</select>
							</li>
							<li>
								<label>Font size, px</label>
								<?php
								if (isset($popup_pro_subscribe_design[$design]['fontsize'])) {
									$fontsize_input = trim($popup_pro_subscribe_design[$design]['fontsize']);
								} else {
									$fontsize_input = '';
								}
								?>
								<input type="text" name="design[<?php echo wp_kses_post($design); ?>][fontsize]" id="<?php echo wp_kses_post($design) . '_font-size'; ?>" value="<?php echo wp_kses_post(trim($fontsize_input)); ?>" onkeypress="return checkIt(event)" >	
							</li>
							<li>
								<label id="labelweight">Font weight</label>
								<select name="design[<?php echo wp_kses_post($design); ?>][fontweight]" id="<?php echo wp_kses_post($design) . '_font-weight'; ?>">
								<?php
								if (0 != $get_post_id) {
									$fonts_weight = $get_fonts->aw_get_font_weight($popup_pro_subscribe_design[$design]['fontname']);

									foreach ($fonts_weight as $font_weight => $font_weight_name) {
										$optionval_weight = $font_weight;
										$selected_weight = '';
										$a = 1;
										if (isset($popup_pro_subscribe_design[$design]['fontweight'])) {
											$a = strcmp($optionval_weight, $popup_pro_subscribe_design[$design]['fontweight']);
										}	
										if (0 == $a ) {
											$selected_weight = 'selected=selected';
										}
										echo '<option ' . wp_kses_post($selected_weight) . ' value="' . wp_kses_post($font_weight) . '">' . wp_kses_post($font_weight_name) . '</option>';
									}
								}
								?>
								</select>
							</li>
							<li>
								<label>Font color</label>
								<input type="text" class="jscolor" value="
								<?php 
								if (isset($popup_pro_subscribe_design[$design]['fontcolor'])) {
									echo wp_kses_post($popup_pro_subscribe_design[$design]['fontcolor']); } 
								?>
								" name="design[<?php echo wp_kses_post($design); ?>][fontcolor]" id="<?php echo wp_kses_post($design) . '_color'; ?>">
							</li>
								<?php
							}
							if ('subscribebutton' == $design) {
								$border_input = '';
								if (isset($popup_pro_subscribe_design[$design]['borderradius'])) {
									$border_input = $popup_pro_subscribe_design[$design]['borderradius'];
								}
								?>
								<li>
									<label>Border radius,px</label> 
									<input type="text" name="design[<?php echo wp_kses_post($design); ?>][borderradius]" value="<?php echo wp_kses_post(trim($border_input)); ?>" onkeypress="return checkIt(event)" id="<?php echo wp_kses_post($design) . '_border-radius'; ?>">
								</li>
								<li>
									<label>Button color</label>
									<?php
									$btncolor_input = '';
									if (isset($popup_pro_subscribe_design[$design]['buttoncolor'])) {
										   $btncolor_input = $popup_pro_subscribe_design[$design]['buttoncolor'];
									}
									?>
									<input type="text" class="jscolor" name="design[<?php echo wp_kses_post($design); ?>][buttoncolor]" value="<?php echo wp_kses_post($btncolor_input); ?>" onkeypress="return checkIt(event)" id="<?php echo wp_kses_post($design) . '_button-color'; ?>">
								</li>	
								<?php 
							}
							if ('closebutton' == $design) {
								?>
								<li>
									<label>Button color</label> 
									<input type="text" class="jscolor" value="
									<?php 
									if (isset($popup_pro_subscribe_design[$design]['buttoncolor'])) {
										echo wp_kses_post($popup_pro_subscribe_design[$design]['buttoncolor']);
									} else {
										echo ''; } 
									?>
									" name="design[<?php echo wp_kses_post($design); ?>][buttoncolor]" id="<?php echo wp_kses_post($design) . '_background-color'; ?>" id="<?php echo wp_kses_post($design) . '_background-color'; ?>">
								</li>   
								<li>
									<label>Button Size</label>
								<?php 
								if (isset($popup_pro_subscribe_design[$design]['buttonsize'])) {
									$button_input = wp_kses_post($popup_pro_subscribe_design[$design]['buttonsize']);
								} else {
									$button_input = '';
								}
								?>
								 
								<input type="text" value="<?php echo wp_kses_post(trim($button_input)); ?>" name="design[<?php echo wp_kses_post($design); ?>][buttonsize]" onkeypress="return checkIt(event)" id="<?php echo wp_kses_post($design) . '_buttonsize'; ?>">
								</li>			         
								<?php
							}
							if ('background' == $design) {
								?>
								<li>
									<label class="lrg">Background color</label> 
									<input type="text" class="jscolor" value="
									<?php 
									if (isset($popup_pro_subscribe_design[$design]['backgroundcolor'])) {
										echo wp_kses_post($popup_pro_subscribe_design[$design]['backgroundcolor']);
									} else {
										echo ''; } 
									?>
									" name="design[<?php echo wp_kses_post($design); ?>][backgroundcolor]"  id="<?php echo wp_kses_post($design) . '_background-color'; ?>">
								</li>
								<li>
									<label class="lrg">Background image</label> 
									<div class="control">
									<input type="file" name="backgroundimage" id="<?php echo wp_kses_post($design) . '_background-image'; ?>"  >
								<?php
								$src = '';
								$imagname = array();
								if (isset($popup_pro_subscribe_design[$design]['backgroundimage']) && '' != $popup_pro_subscribe_design[$design]['backgroundimage']) {
									$path = wp_get_upload_dir();
									$imagepath = explode('uploads', $popup_pro_subscribe_design[$design]['backgroundimage']) ;
									$fullpath  = $path['basedir'] . $imagepath[1];
									if (file_exists($fullpath)) {
										$src = $popup_pro_subscribe_design[$design]['backgroundimage']; 
										$imagname = explode('/', $src);
										?>

											<input type="hidden" data-value="" value="<?php echo esc_url($src); ?>" name="backgroundimage" id="backgroundimage">
											<img width="50%" height="50%" id="<?php echo wp_kses_post($design) . '_display-image'; ?>" src="<?php echo esc_url($src); ?>">

											<a id="closebackimage" href="javascript:void(0)" post-id="<?php echo wp_kses_post($post->ID); ?>">X</a>		
											<?php
									}
								} else {
									?>
										<img width="50%" height="50%" id="<?php echo wp_kses_post($design) . '_display-image'; ?>" src="<?php echo esc_url($src); ?>" alt="">
											<a id="closebackimage" href="javascript:void(0)" post-id="<?php echo wp_kses_post($post->ID); ?>">X</a>
											
										<input type="hidden" data-value="" name="backgroundimage" value="<?php echo esc_url($src); ?>" id="backgroundimage">
									<?php
								}
								?>
									</div>
									<p align="center" id="uploadedimage" style="color: #000000;font-style: normal;"><?php echo wp_kses_post(end($imagname)); ?></p>
									<p class="info">Supported file types: jpeg, jpg, png, bmp, gif</p>
									<p class="info" id="bg_img_hint"></p>
								</li>
								<?php
							}
							?>
						</ul>
						</div> <!-- close of individual tab -->
							<?php
						endforeach;
						?>
				   </div>
					</div>
					<div class="panel-box temp-setting padd20">
						<h3>Settings</h3>
						<div class="panel-body">
						<ul>
							<li>
								<label>When to display</label>
								<div class="control">
									<select name="popup_pro_subscribe_when_display" id="popup_pro_subscribe_when_display" class="required">
										<option value="1" 
										<?php 
										if (isset($popup_pro_subscribe_when_display)) {
											selected($popup_pro_subscribe_when_display, '1');} 
										?>
										>After X viewed pages</option>
										<option value="2" 
										<?php 
										if (isset($popup_pro_subscribe_when_display)) {
											selected($popup_pro_subscribe_when_display, '2');} 
										?>
										>X minutes after the visit</option>
									</select>
								</div>
							</li>
							<li>
								<label>X equals to</label>
								<div class="control">
									<input type="text" name="popup_pro_subscribe_x_equalto" id="popup_pro_subscribe_x_equalto" class="input-txt required" value="<?php echo wp_kses_post($popup_pro_subscribe_x_equalto); ?>" onkeypress="return checkIt(event)">
								</div>
							</li>
							<li>
								<label>Cookie Lifetime, minutes</label>
								<div class="control">
									<input type="text" name="popup_pro_subscribe_cookie_lifetime" id="popup_pro_subscribe_cookie_lifetime" class="input-txt required" value="<?php echo wp_kses_post($popup_pro_subscribe_cookie_lifetime); ?>" onkeypress="return checkIt(event)">
									<p class="info">Once popup is shown to the customer, it will not be shown to them again within the next X minutes</p>
								</div>
							</li>
						</ul>
						</div>
						</div>
				</div>	
			</div> 
		</div>
		<?php
		add_thickbox();
		 $url = add_query_arg( array(
			'action'    => 'subscribe_preview_modal_box',
			'width'     => '600',
			'height'    => '580',
		 ), admin_url( 'admin.php' ));

		echo '<div class="action-rw clearfix"><a href="' . esc_url($url) . '" class="button button-primary thickbox" id="pop-pro-sub-link"> Preview </a></div>';	
		?>
		<input type="hidden" value="<?php echo esc_url($url); ?>" name="hidden_popup-link" id="hidden_popup_link"> 
		<?php
	}
	public static function aw_export_subscribers_action_page() {
		/** Export Subscriber Data in CSV / XLS / XSLS **/
		global $wpdb;
		$format='';
		$flag = false;
		if (isset($_REQUEST['format'])) {
			$format = wp_kses_post($_REQUEST['format']);
		}

		$table_name = $wpdb->prefix . 'popup_pro_subscribes'; 
		//$sql= 'SELECT * FROM `' . $table_name . '`';
		$items = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}popup_pro_subscribes WHERE 1 = %d", '1'), ARRAY_A);
		$i=1;

		foreach ($items as $key=> $item) {
			$date = gmdate( 'F d Y H:i A', strtotime($item['date']));
			if (0 == $item['status'] ) {
				$status = 'Trash';
			} else {
				$status = 'Active';
			}
			
			$data[] = array('Name'=>$item['name'],'Email'=>$item['email'],'Subscribed On'=>$date,'Popup ID'=>$item['post_id'],'Status'=>$status);
		}

		$fileName = 'subscribers-' . gmdate('Ymd') . '.' . $format;
		if ('csv' == $format) {
			header('Content-Type: text/csv');	
		} 
		header("Content-Disposition: attachment; filename=\"$fileName\"");
		if ('xls' == $format||'xlsx' == $format) {
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		}
		foreach ($data as $row) {
			if (!$flag) {
				if ('csv' == $format) {
					echo wp_kses_post(implode(',', array_keys($row))) . "\r\n";
				} elseif ('xls' == $format || 'xlsx' == $format) {
					echo wp_kses_post(implode("\t", array_keys($row))) . "\r\n";
				}
				$flag = true;
			}
			//array_walk($row, __NAMESPACE__ . '\filterData');
			//array_walk($row, array('self', 'setUserRequestStatus'));
			if ('csv' == $format) {
				echo wp_kses_post(implode(',', array_values($row))) . "\r\n";
			}
			if ('xls' == $format || 'xlsx' == $format) {
				echo wp_kses_post(implode("\t", array_values($row))) . "\r\n";
			}
		}
		die;
	}
}

function aw_filterData( &$str) {
	$str = preg_replace("/\t/", "\\t", $str);
	$str = preg_replace("/\r?\n/", "\\n", $str);
	if (strstr($str, '"')) {
		$str = '"' . str_replace('"', '""', $str) . '"';
	}
}
?>
