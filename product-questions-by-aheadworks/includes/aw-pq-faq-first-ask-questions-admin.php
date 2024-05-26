<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class AwPqFaqFirstAskQuestionsAdmin {
	public static function aw_pq_faq_add_flash_notice( $notice = '', $type = 'warning', $dismissible = true ) { 
		// Here we return the notices saved on our option, if there are not notices, then an empty array is returned
		$notices = get_option( 'faq_flash_notices', array() );
		$dismissible_text = ( $dismissible ) ? 'is-dismissible' : '';

		$notices = array(
						'notice' => $notice, 
						'type' => $type, 
						'dismissible' => $dismissible_text
					); 	
		update_option('faq_flash_notices', $notices );
	}
	public static function aw_pq_faq_cat_add_flash_notice( $notice = '', $type = 'warning', $dismissible = true ) { 
		// Here we return the notices saved on our option, if there are not notices, then an empty array is returned
		$notices = get_option('faq_cat_flash_notices', array() );
		$dismissible_text = ( $dismissible ) ? 'is-dismissible' : '';

		$notices = array(
						'notice' => $notice, 
						'type' => $type, 
						'dismissible' => $dismissible_text
					); 	
		update_option('faq_cat_flash_notices', $notices );
	}

	public static function aw_pq_first_ask_question_setting() {	
		$faq_setting_enable_search_articles	= '';
		$faq_setting_ask_question_form 		= '';

		$faq_one 							= '';
		$faq_two 							= '';
		$faq_three 							= '';

		$faq_anyone 		 				= '';
		$faq_loggedinuser					= '';

		$faq_anyone_helpful 				= '';
		$faq_loggedinuser_helpful			= '';

		$faq_before_vote_yes 				= '';
		$faq_before_vote_no 				= '';

		$faq_after_vote_yes 				= '';
		$faq_after_vote_no 					= '';

		$faq_setting_redirect_url_yes 		= '';
		$faq_setting_redirect_url_no 		= '';

		$faq_categories_meta_tag_yes 		= '';
		$faq_categories_meta_tag_no 		= '';

		$faq_articles_meta_tag_yes 			= '';
		$faq_articles_meta_tag_no 			= '';


		if (get_option('faq_setting_enable_search_articles')) {
			$faq_setting_enable_search_articles = 'checked = checked';
		}
		if (get_option('faq_setting_ask_question_form')) {
			$faq_setting_ask_question_form = 'checked = checked';
		}

		if (get_option('faq_setting_default_columns_main_page')) {
			if ('1' == get_option('faq_setting_default_columns_main_page')) {
				$faq_one = 'selected = selected';
			}
			if ('2' == get_option('faq_setting_default_columns_main_page')) {
				$faq_two = 'selected = selected';
			}
			if ('3' == get_option('faq_setting_default_columns_main_page')) {
				$faq_three = 'selected = selected';
			}
		}
		
		if (get_option('faq_setting_view_content')) {
			if ('anyone' == get_option('faq_setting_view_content')) {
				$faq_anyone = 'selected = selected';
			}
			if ('loggedinuser' == get_option('faq_setting_view_content')) {
				$faq_loggedinuser = 'selected = selected';
			}
		}

		if (get_option('faq_setting_view_helpfulness')) {
			if ('anyone' == get_option('faq_setting_view_helpfulness')) {
				$faq_anyone_helpful = 'selected = selected';
			}
			if ('loggedinuser' == get_option('faq_setting_view_helpfulness')) {
				$faq_loggedinuser_helpful = 'selected = selected';
			}
		}

		if (get_option('faq_setting_helpfulness_rate_before_voting')) {
			if ('yes' == get_option('faq_setting_helpfulness_rate_before_voting')) {
				$faq_before_vote_yes = 'selected = selected';
			}
			if ('no' == get_option('faq_setting_helpfulness_rate_before_voting')) {
				$faq_before_vote_no = 'selected = selected';
			}
		}

		if (get_option('faq_setting_helpfulness_rate_after_voting')) {
			if ('yes' == get_option('faq_setting_helpfulness_rate_after_voting')) {
				$faq_after_vote_yes = 'selected = selected';
			}
			if ('no' == get_option('faq_setting_helpfulness_rate_after_voting')) {
				$faq_after_vote_no = 'selected = selected';
			}
		}

		if (get_option('faq_setting_redirect_url')) {
			if ('yes' == get_option('faq_setting_redirect_url')) {
				$faq_setting_redirect_url_yes = 'selected = selected';
			}
			if ('no' == get_option('faq_setting_redirect_url')) {
				$faq_setting_redirect_url_no = 'selected = selected';
			}
		}

		if (get_option('faq_setting_meta_tag_categories_link')) {
			if ('yes' == get_option('faq_setting_meta_tag_categories_link')) {
				$faq_categories_meta_tag_yes = 'selected = selected';
			}
			if ('no' == get_option('faq_setting_meta_tag_categories_link')) {
				$faq_categories_meta_tag_no = 'selected = selected';
			}
		}

		if (get_option('faq_setting_meta_tag_articles_link')) {
			if ('yes' == get_option('faq_setting_meta_tag_articles_link')) {
				$faq_articles_meta_tag_yes = 'selected = selected';
			}
			if ('no' == get_option('faq_setting_meta_tag_articles_link')) {
				$faq_articles_meta_tag_no = 'selected = selected';
			}
		}

		$notice = maybe_unserialize(get_option( 'faq_flash_notices'));
		if ( ! empty( $notice ) ) {
				printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
					wp_kses($notice['type'], wp_kses_allowed_html('post')),
					wp_kses($notice['dismissible'], wp_kses_allowed_html('post')),
					wp_kses($notice['notice'], wp_kses_allowed_html('post'))
				);
			delete_option( 'faq_flash_notices');
		}

		?>
		<div class="tab-grid-wrapper">
			<div class="spw-rw clearfix">
				<div class="panel-box faq-setting">
					<div class="page-title">
						<h1>
							<?php echo wp_kses('FAQ Settings', wp_kses_allowed_html('post')); ?>
						</h1>
					</div>
					<div class="panel-body">
						<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" id="rd_faq_setting_form">
									<?php wp_nonce_field( 'save_faq_setting_form', 'rdfaq_admin_nonce' ); ?>
									<input type="hidden" name="action" value="faq_save_setting_form">
							<div class="tab">
								<button class="tablinks active"   onclick="openTab(event, 'genral-setting-tab',this)"><?php echo wp_kses('General Settings', wp_kses_allowed_html('post')); ?></button>
								<button class="tablinks"  onclick="openTab(event, 'articles-helpfulness-setting-tab',this)"><?php echo wp_kses('Articles Helpfulness Settings', wp_kses_allowed_html('post')); ?></button>
								<button class="tablinks"  onclick="openTab(event, 'search-engine-optimization-setting-tab',this)"><?php echo wp_kses('Search Engine Optimization Settings', wp_kses_allowed_html('post')); ?></button>
							</div> 
							<!-- Tab Start -->
							<div class="tabcontent faq-general-set" id="genral-setting-tab" style="display:block;">
									<ul>
										<li>
											<label><?php echo wp_kses('FAQ name', wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<input type="text" name="faq_setting_name" class="faq_setting_name" value="<?php echo wp_kses(get_option('faq_setting_name'), wp_kses_allowed_html('post')); ?>" onkeypress="return checkSpecialchar(event)"/>
											<span class="faq_setting_name_error"></span>
											</div>
										</li>
										<li>
											<label><?php echo wp_kses('FAQ slug', wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<input type="text" name="faq_setting_slug" class="
											faq_setting_slug" value="<?php echo wp_kses(get_option('faq_setting_slug'), wp_kses_allowed_html('post')); ?>"  />
											<p><span><?php echo wp_kses('E.g.: "faq" will make the FAQ accessible from http://mydomain.com/faq', wp_kses_allowed_html('post')); ?></span></p>
											<span class="faq_setting_slug_error"></span>
											</div>
										</li>
										<li>
											<label><?php echo wp_kses('FAQ main page meta title', wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<input type="text" name="faq_setting_page_meta_tilte" class="
											faq_setting_page_meta_tilte" value="<?php echo wp_kses(get_option('faq_setting_page_meta_tilte'), wp_kses_allowed_html('post')); ?>" />
											<span class="faq_setting_page_meta_tilte_error"></span>
											</div>
										</li>
										<li>
											<label><?php echo wp_kses('FAQ main page meta description', wp_kses_allowed_html('post')); ?></label>
											<div class="control">
												<textarea rows="3" cols="20" class="faq_setting_main_page_meta_description" type="textarea" name="faq_setting_main_page_meta_description" value="<?php echo wp_kses(get_option('faq_setting_main_page_meta_description'), wp_kses_allowed_html('post')); ?>"><?php echo wp_kses(get_option('faq_setting_main_page_meta_description'), wp_kses_allowed_html('post')) ; ?></textarea>
												<span class="faq_setting_main_page_meta_description_error"></span>
											</div>
										</li>
										<li>
											<label><?php echo wp_kses('Default number of columns on FAQ main page', wp_kses_allowed_html('post')); ?></label>
											<div class="control">
												<select class="select faq_setting_default_columns_main_page wc-enhanced-select select2-hidden-accessible enhanced" name="faq_setting_default_columns_main_page" style="" tabindex="-1" aria-hidden="true">
												<option value="1" <?php echo wp_kses($faq_one, wp_kses_allowed_html('post')); ?> ><?php echo wp_kses('1', wp_kses_allowed_html('post')); ?></option>
												<option value="2" <?php echo wp_kses($faq_two, wp_kses_allowed_html('post')); ?> ><?php echo wp_kses('2', wp_kses_allowed_html('post')); ?></option>
												<option value="3" <?php echo wp_kses($faq_three, wp_kses_allowed_html('post')); ?> ><?php echo wp_kses('3', wp_kses_allowed_html('post')); ?></option>
												</select>
											</div>
										</li>
										<li>
											<label><?php echo wp_kses('Enable search in articles', wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<input type="checkbox" name="faq_setting_enable_search_articles" <?php echo wp_kses($faq_setting_enable_search_articles, wp_kses_allowed_html('post')); ?> value="yes"/>
											</div>
										</li>
										<li class="inline-menu-link">
											<label><?php echo wp_kses('Display link to FAQ in', wp_kses_allowed_html('post')); ?></label>								
												<?php
												if ( current_theme_supports( 'menus' ) ) {
													$locations      = get_registered_nav_menus();
													$menu_locations = get_nav_menu_locations();
													$nav_menu_selected_id = isset( $_REQUEST['menu'] ) ? (int) $_REQUEST['menu'] : 0;
													$theme_name = wp_get_theme();
													$theme_name = str_replace('-', '', $theme_name);
													$theme_domain_name = preg_replace('/\s*/', '', $theme_name);
													$theme_domain_name = strtolower($theme_domain_name);
													$arr=get_option('theme_mods_' . $theme_domain_name . '');
													?>
																								
														<div class="control">
															<?php foreach ( $locations as $location => $description ) : ?>
																	<div class="menu-settings-input checkbox-input">
																		<input type="checkbox"<?php checked( isset( $menu_locations[ $location ] ) && $menu_locations[ $location ] == $nav_menu_selected_id ); ?> name="menu-locations[<?php echo esc_attr( $location ); ?>]" id="locations-<?php echo esc_attr( $location ); ?>" value="<?php echo esc_attr( $nav_menu_selected_id ); ?>"
																											 <?php 
																												if (isset($arr['nav_menu_locations'])) {
																													foreach ($arr['nav_menu_locations'] as $paramName => $paramValue) {
																														if ($paramName == $location) {
																															?>
																		checked="checked" <?php }}} ?>/>
																		<label for="locations-<?php echo esc_attr( $location ); ?>"><?php echo wp_kses($description, wp_kses_allowed_html('post')); ?></label>
																		<?php if ( ! empty( $menu_locations[ $location ] ) && $menu_locations[ $location ] != $nav_menu_selected_id ) : ?>
																		<?php endif; ?>
																	</div>
																<?php endforeach; ?>
															<p><span>Your active theme supports <?php echo count($locations); ?> menus.<br/>Select menu locations in which FAQ should appear.</span></p>
														</div>
														<?php
												} 
												?>
										</li>
										<li>
											<label><?php echo wp_kses('Enable "Ask a question" form on the article pages', wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<input type="checkbox" name="faq_setting_ask_question_form" <?php echo wp_kses($faq_setting_ask_question_form, wp_kses_allowed_html('post')); ?> value="yes"/>
											</div>
										</li>
										<li>
											<label><?php echo wp_kses('Email address for the questions from article pages', wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<input type="text" name="faq_setting_email_address" class="
											faq_setting_email_address" value="<?php echo wp_kses(get_option('faq_setting_email_address'), wp_kses_allowed_html('post')); ?>"/>
											<span class="faq_setting_email_address_error"></span>
											</div>
										</li>
										<li>
											<label><?php echo wp_kses('Who can view FAQ content', wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<select name="faq_setting_view_content">
												<option value="anyone" <?php echo wp_kses($faq_anyone, wp_kses_allowed_html('post')); ?>><?php echo wp_kses('Anyone', wp_kses_allowed_html('post')); ?></option>
												<option value="loggedinuser" <?php echo wp_kses($faq_loggedinuser, wp_kses_allowed_html('post')); ?> ><?php echo wp_kses('Logged In Users', wp_kses_allowed_html('post')); ?></option>
											</select>
											<span class="faq_setting_view_content_error"></span>
											</div>
										</li>
									</ul>   
									<div class="submit">
										<input type="submit" class="button button-primary" value="<?php echo wp_kses('Save', wp_kses_allowed_html('post')); ?>" onclick="return faqcheckform()" name="setting_faq_submit"/>	
									</div>
							</div>
							<div class="tabcontent faq-articles-set" id="articles-helpfulness-setting-tab" style="display:none;">
									<ul>
										<li>
											<label><?php echo wp_kses('Who can view helpfulness', wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<select name="faq_setting_view_helpfulness">
												<option value="anyone" <?php echo wp_kses($faq_anyone_helpful, wp_kses_allowed_html('post')); ?>><?php echo wp_kses('Anyone', wp_kses_allowed_html('post')); ?></option>
												<option value="loggedinuser" <?php echo wp_kses($faq_loggedinuser_helpful, wp_kses_allowed_html('post')); ?> ><?php echo wp_kses('Logged In Users', wp_kses_allowed_html('post')); ?></option>
											</select>
											<span class="faq_setting_view_content_error"></span>
											</div>
										</li>
										<li>
											<label><?php echo wp_kses('Display helpfulness rate before voting', wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<select name="faq_setting_helpfulness_rate_before_voting">
												<option value="yes" <?php echo wp_kses($faq_before_vote_yes, wp_kses_allowed_html('post')); ?>><?php echo wp_kses('Yes', wp_kses_allowed_html('post')); ?></option>
												<option value="no" <?php echo wp_kses($faq_before_vote_no, wp_kses_allowed_html('post')); ?> ><?php echo wp_kses('No', wp_kses_allowed_html('post')); ?></option>
											</select>
											<span class="faq_setting_helpfulness_rate_before_voting_error"></span>
											</div>
										</li>
										<li>
											<label><?php echo wp_kses('Display helpfulness rate after voting', wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<select name="faq_setting_helpfulness_rate_after_voting">
												<option value="yes" <?php echo wp_kses($faq_after_vote_yes, wp_kses_allowed_html('post')); ?>><?php echo wp_kses('Yes', wp_kses_allowed_html('post')); ?></option>
												<option value="no" <?php echo wp_kses($faq_after_vote_no, wp_kses_allowed_html('post')); ?> ><?php echo wp_kses('No', wp_kses_allowed_html('post')); ?></option>
											</select>
											<span class="faq_setting_helpfulness_rate_before_voting_error"></span>
											</div>
										</li>
									</ul>   
									<div class="submit">
										<input type="submit" class="button button-primary" value="<?php echo wp_kses('Save', wp_kses_allowed_html('post')); ?>" name="setting_faq_submit"/>	
									</div>
							</div>
							<div class="tabcontent faq-seo-set" id="search-engine-optimization-setting-tab" style="display:none;">
							<ul>
								<li>
									<label><?php echo wp_kses('Article URL suffix', wp_kses_allowed_html('post')); ?></label>
									<div class="control">
									<input type="text" name="faq_setting_article_url_suffix" class="faq_setting_article_url_suffix" value="<?php echo wp_kses(get_option('faq_setting_article_url_suffix'), wp_kses_allowed_html('post')); ?>" onkeypress="return checkSpace(event)"/>
									<span class="faq_setting_article_url_suffix_error"></span>
									</div>
								</li>
								<li>
									<label><?php echo wp_kses('Category URL Suffix', wp_kses_allowed_html('post')); ?></label>
									<div class="control">
									<input type="text" name="faq_setting_category_url_suffix" class="faq_setting_category_url_suffix" value="<?php echo wp_kses(get_option('faq_setting_category_url_suffix'), wp_kses_allowed_html('post')); ?>" onkeypress="return checkSpace(event)"/>
									<span class="faq_setting_category_url_suffix_error"></span>
									</div>
								</li>
								<li>
									<label><?php echo wp_kses('Create Permanent Redirect for URLs if URL Key Changed', wp_kses_allowed_html('post')); ?></label>
									<div class="control">
									<select name="faq_setting_redirect_url">
										<option value="no" <?php echo wp_kses($faq_setting_redirect_url_no, wp_kses_allowed_html('post')); ?> ><?php echo wp_kses('No', wp_kses_allowed_html('post')); ?></option>
										<option value="yes" <?php echo wp_kses($faq_setting_redirect_url_yes, wp_kses_allowed_html('post')); ?>><?php echo wp_kses('Yes', wp_kses_allowed_html('post')); ?></option>
									</select>
								<span class="faq_setting_redirect_url_error"></span>
								</div>
								</li>
								<li>
									<label><?php echo wp_kses('Page Title Separator', wp_kses_allowed_html('post')); ?></label>
									<div class="control">
									<input type="text" name="faq_setting_page_seprator" class="faq_setting_page_seprator" value="<?php echo wp_kses(get_option('faq_setting_page_seprator'), wp_kses_allowed_html('post')); ?>"/>
									<span class="faq_setting_page_seprator_error"></span>
									</div>
								</li>
								<li>
									<label><?php echo wp_kses('Use Canonical Link Meta Tag For Categories', wp_kses_allowed_html('post')); ?></label>
									<div class="control">
									<select name="faq_setting_meta_tag_categories_link">
										<option value="no" <?php echo wp_kses($faq_categories_meta_tag_no, wp_kses_allowed_html('post')); ?> ><?php echo wp_kses('No', wp_kses_allowed_html('post')); ?></option>
										<option value="yes" <?php echo wp_kses($faq_categories_meta_tag_yes, wp_kses_allowed_html('post')); ?>><?php echo wp_kses('Yes', wp_kses_allowed_html('post')); ?></option>
									</select>
									<span class="faq_setting_meta_tag_categories_link_error"></span>
								</div>
								</li>
								<li>
									<label><?php echo wp_kses('Use Canonical Link Meta Tag For Articles', wp_kses_allowed_html('post')); ?></label>
									<div class="control">
									<select name="faq_setting_meta_tag_articles_link">
										<option value="no" <?php echo wp_kses($faq_articles_meta_tag_no, wp_kses_allowed_html('post')); ?> ><?php echo wp_kses('No', wp_kses_allowed_html('post')); ?></option>
										<option value="yes" <?php echo wp_kses($faq_articles_meta_tag_yes, wp_kses_allowed_html('post')); ?>><?php echo wp_kses('Yes', wp_kses_allowed_html('post')); ?></option>
									</select>
									<span class="faq_setting_meta_tag_articles_link_error"></span>
								</div>
								</li>
							</ul>
									<div class="submit">
										<input type="submit" class="button button-primary" value="<?php echo wp_kses('Save', wp_kses_allowed_html('post')); ?>" name="setting_faq_submit" onclick="return aw_faq_setting_submit(event)"/>	
									</div>
							</div>
						</form>
					</div>
				</div>	
			</div>
		</div>	
		<?php

	}

	public static function aw_pq_faq_save_setting_form() {
		global $wpdb;
		$url =  admin_url() . 'admin.php?page=first_ask_question';
		if (isset($_POST['rdfaq_admin_nonce'])) {
			$rdfaq_admin_nonce = sanitize_text_field($_POST['rdfaq_admin_nonce']);
		}

		if ( !wp_verify_nonce( $rdfaq_admin_nonce, 'save_faq_setting_form' )) {
			wp_die('Our Site is protected');
		}

		$art_old_suffix	 = get_option('faq_setting_article_url_suffix');
		$cat_old_suffix	 = get_option('faq_setting_category_url_suffix');
		update_option('faq_setting_article_url_suffix', ' ');
		update_option('faq_setting_category_url_suffix', ' ');

		if (isset($_POST['setting_faq_submit'])) {
			if (isset($_POST['faq_setting_article_url_suffix'])) {
				$article_url_suffix = sanitize_text_field($_POST['faq_setting_article_url_suffix']);
				if ( '/' != $article_url_suffix) {
					if (get_option('faq_setting_article_url_suffix') != $article_url_suffix) {
						$check = $wpdb->get_results( $wpdb->prepare("SELECT ID,post_name FROM {$wpdb->prefix}posts WHERE post_type = %s ", 'faq_article' ));

						foreach ($check as $key => $value) {
							$c 			= $check[$key];
							$name 		= $c->post_name;
							$name 		= chop($name, $art_old_suffix);
							$suffix_art = $name . $article_url_suffix;
							$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}posts SET post_name = %s  WHERE ID = %d " , "{$suffix_art}", "{$c->ID}"));

						}
					}
				}
			}

			if (isset($_POST['faq_setting_category_url_suffix'])) {
				$category_url_suffix = sanitize_text_field($_POST['faq_setting_category_url_suffix']);
				if ('/' != $category_url_suffix ) {
					if (get_option('faq_setting_category_url_suffix') != $category_url_suffix) {
						$check1 = $wpdb->get_results( $wpdb->prepare("SELECT term_id FROM {$wpdb->prefix}term_taxonomy WHERE taxonomy = %s ", 'faq_cat' ));

						foreach ($check1 as $key => $value) {
							$c 		= 	$check1[$key];
							$name 	= $wpdb->get_var( $wpdb->prepare("SELECT slug FROM {$wpdb->prefix}terms WHERE term_id= %d ", "{$c->term_id}" ));
							$old_name=$name;
							$name = chop($name, $cat_old_suffix);
							$suffix_cat = $name . $category_url_suffix;

							$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}terms SET slug = %s  WHERE term_id = %d " , "{$suffix_cat}", "{$c->term_id}"));
							

							$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aw_pq_faq_category_list SET category_slug = %s  WHERE category_slug = %s " , "{$suffix_cat}", "{$old_name}"));

						}
					}
				}
			}	

			update_option('faq_setting_category_url_suffix', $category_url_suffix);
			update_option('faq_setting_article_url_suffix', $article_url_suffix);
			$menu_locations_value 	= $wpdb->get_var( $wpdb->prepare("SELECT term_id FROM {$wpdb->prefix}terms WHERE slug = %s ", 'faq-menu'));	

			if (!empty($_POST['menu-locations'])) {
				//$post_menu_loc = sanitize_post($_POST['menu-locations']);
				$loc = json_encode($_POST);
				$loc = json_decode($loc, true);
				foreach ($loc['menu-locations'] as $key=>$value) {
				$theme_name 				= wp_get_theme();
				$theme_domain_name 			= preg_replace('/\s*/', '', $theme_name);
				$theme_domain_name 			= strtolower($theme_domain_name);
				$location_array 			= get_option('theme_mods_' . $theme_domain_name . '');
				$new_location_array[$key] 	= $menu_locations_value;
				$location_array['nav_menu_locations'] = $new_location_array;
				update_option('theme_mods_' . $theme_domain_name . '', $location_array);
				}
			} else {
				$theme_name = wp_get_theme();
				$theme_domain_name = preg_replace('/\s*/', '', $theme_name);
				$theme_domain_name = strtolower($theme_domain_name);
				$location_array = get_option('theme_mods_' . $theme_domain_name . '');
				unset($location_array['nav_menu_locations']);
				update_option('theme_mods_' . $theme_domain_name . '', $location_array);
			}


			if (isset($_POST['faq_setting_name']) && '' != sanitize_text_field($_POST['faq_setting_name'])) {
				$faq_setting_name = sanitize_text_field($_POST['faq_setting_name']);
				update_option('faq_setting_name', $faq_setting_name );

				$post_name ='' . $faq_setting_name . '' ;
				$post = $wpdb->get_var( $wpdb->prepare("SELECT post_name FROM {$wpdb->prefix}posts WHERE post_title = %s ", "{$post_name}" ));
				
				$page_key='_faq_page_template';
				$page_id= $wpdb->get_var( $wpdb->prepare("SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = %s LIMIT %d " , "{$page_key}", 1) );
				$menu_page_key='_faq_menu_page_template';
				$menu_page_id = $wpdb->get_var( $wpdb->prepare("SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = %s LIMIT %d " , "{$menu_page_key}", 1) );

				if (isset($_POST['faq_setting_page_meta_tilte'])) {
					$faq_setting_meta_tilte = sanitize_text_field($_POST['faq_setting_page_meta_tilte']);
				}
				if (isset($_POST['faq_setting_main_page_meta_description'])) {
					$faq_setting_meta_desc = sanitize_text_field($_POST['faq_setting_main_page_meta_description']);
				}
				if (isset($_POST['faq_setting_slug'])) {
					$faq_setting_slug = sanitize_text_field($_POST['faq_setting_slug']);
				}
				if (!$post) {
					if (!empty($page_id) && !empty($menu_page_id)) {
						$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}posts WHERE ID = %d ", "{$page_id}"));
						$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}posts WHERE ID = %d ", "{$menu_page_id}"));
						$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}postmeta WHERE post_id = %d ", "{$page_id}"));
						$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}postmeta WHERE post_id = %d ", "{$menu_page_id}"));
					}
					$menu = wp_insert_term('FAQ MENU', 'nav_menu', array('slug' => 'faq-menu'));
					$page = wp_insert_post(array('post_title' => '' . $faq_setting_name . '',
					'post_content' => '[aw_pq_faq_page]',
					'post_status' => 'publish',
					'post_name'  =>'' . $faq_setting_slug . '',
					'menu_order' => '1',
					'post_type' => 'page',
				   ));
					update_post_meta( $page, '_faq_page_template', 'default' );
					$post_id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = %s AND menu_order = %d AND post_name= %s ", 'page', 1, "{$faq_setting_slug}"));


					$nav_item = wp_insert_post(array('post_title' => '' . $faq_setting_name . '',
					'post_content' => '',
					'post_status' => 'publish',
					'post_name' =>'' . $faq_setting_name . '',
					'menu_order' => '1',
					'guid' 	  => site_url() . '?p=' . $page,
					'post_type' => 'nav_menu_item'));
					
					add_post_meta($post_id, '_faq_meta_title', '' . $faq_setting_meta_tilte . '');
					add_post_meta($post_id, '_faq_meta_desc', '' . $faq_setting_meta_desc . '');
					
					update_post_meta($nav_item, '_faq_menu_page_template', 'default' );
					update_post_meta($nav_item, '_menu_item_type', 'post_type');
					update_post_meta($nav_item, '_menu_item_menu_item_parent', '0');
					update_post_meta($nav_item, '_menu_item_object_id', $page);
					update_post_meta($nav_item, '_menu_item_object', 'page');
					update_post_meta($nav_item, '_menu_item_target', '');
					update_post_meta($nav_item, '_menu_item_classes', 'a:1:{i:0;s:0:"";}');
					update_post_meta($nav_item, '_menu_item_xfn', '');
					update_post_meta($nav_item, '_menu_item_url', '');
					wp_set_object_terms($nav_item, 'FAQ MENU', 'nav_menu');
				} else {
					$faq_setting_slug = strtolower(trim($faq_setting_slug));
					$faq_setting_slug = str_replace(' ', '-', $faq_setting_slug);
					$id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = %s ", "{$post_name}" ));
					$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}posts SET post_name = %s  WHERE ID = %d " , "{$faq_setting_slug}", "{$id}"));
					$post_id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = %d  AND  menu_order = %d AND post_name= %s ", 'page', 1, "{$faq_setting_slug}"));
					update_post_meta($post_id, '_faq_meta_title', '' . $faq_setting_meta_tilte . '');
					update_post_meta($post_id, '_faq_meta_desc', '' . $faq_setting_meta_desc . '');
				}
			}		
			if (isset($_POST['faq_setting_slug'])) {
				update_option('faq_setting_slug', sanitize_text_field($_POST['faq_setting_slug']));
			}
			if (isset($_POST['faq_setting_page_meta_tilte'])) {
				update_option('faq_setting_page_meta_tilte', sanitize_text_field($_POST['faq_setting_page_meta_tilte']));
			}
			if (isset($_POST['faq_setting_main_page_meta_description'])) {
				update_option('faq_setting_main_page_meta_description', sanitize_text_field($_POST['faq_setting_main_page_meta_description']));
			}
			if (isset($_POST['faq_setting_default_columns_main_page']) && '' != sanitize_text_field($_POST['faq_setting_default_columns_main_page'])) {
				update_option('faq_setting_default_columns_main_page', sanitize_text_field($_POST['faq_setting_default_columns_main_page']));
			}
			if (isset($_POST['faq_setting_enable_search_articles'])) {
				update_option('faq_setting_enable_search_articles', sanitize_text_field($_POST['faq_setting_enable_search_articles']));
			} else {
				update_option('faq_setting_enable_search_articles', '');
			}

			if (isset($_POST['faq_setting_ask_question_form'])) {
				update_option('faq_setting_ask_question_form', sanitize_text_field($_POST['faq_setting_ask_question_form']));
			} else {
				update_option('faq_setting_ask_question_form', '');
			}
			if (isset($_POST['faq_setting_email_address']) && '' != sanitize_text_field($_POST['faq_setting_email_address'])) {
				update_option('faq_setting_email_address', sanitize_text_field($_POST['faq_setting_email_address']));
			}
			if (isset($_POST['faq_setting_view_content']) && '' != sanitize_text_field($_POST['faq_setting_view_content'])) {
				update_option('faq_setting_view_content', sanitize_text_field($_POST['faq_setting_view_content']));
			}
			if (isset($_POST['faq_setting_view_helpfulness']) && '' != sanitize_text_field($_POST['faq_setting_view_helpfulness'])) {
				update_option('faq_setting_view_helpfulness', sanitize_text_field($_POST['faq_setting_view_helpfulness']));
			}	
			if (isset($_POST['faq_setting_helpfulness_rate_before_voting']) && '' != sanitize_text_field($_POST['faq_setting_helpfulness_rate_before_voting'])) {
				update_option('faq_setting_helpfulness_rate_before_voting', sanitize_text_field($_POST['faq_setting_helpfulness_rate_before_voting']));
			}
			if (isset($_POST['faq_setting_helpfulness_rate_after_voting']) && '' != sanitize_text_field($_POST['faq_setting_helpfulness_rate_after_voting'])) {
				update_option('faq_setting_helpfulness_rate_after_voting', sanitize_text_field($_POST['faq_setting_helpfulness_rate_after_voting']));
			}

			if (isset($_POST['faq_setting_article_url_suffix']) && '' != sanitize_text_field($_POST['faq_setting_article_url_suffix'])) {
				update_option('faq_setting_article_url_suffix', sanitize_text_field($_POST['faq_setting_article_url_suffix']));
			}
			if (isset($_POST['faq_setting_category_url_suffix']) && '' != sanitize_text_field($_POST['faq_setting_category_url_suffix'])) {
				update_option('faq_setting_category_url_suffix', sanitize_text_field($_POST['faq_setting_category_url_suffix']));
			}
			if (isset($_POST['faq_setting_redirect_url']) && '' != sanitize_text_field($_POST['faq_setting_redirect_url'])) {
				update_option('faq_setting_redirect_url', sanitize_text_field($_POST['faq_setting_redirect_url']));
			}
			if (isset($_POST['faq_setting_page_seprator'])) {
				update_option('faq_setting_page_seprator', sanitize_text_field($_POST['faq_setting_page_seprator']));
			}
			if (isset($_POST['faq_setting_meta_tag_categories_link']) && '' != sanitize_text_field($_POST['faq_setting_meta_tag_categories_link'])) {
				update_option('faq_setting_meta_tag_categories_link', sanitize_text_field($_POST['faq_setting_meta_tag_categories_link']));
			}
			if (isset($_POST['faq_setting_meta_tag_articles_link']) && '' != sanitize_text_field($_POST['faq_setting_meta_tag_articles_link'])) {
				update_option('faq_setting_meta_tag_articles_link', sanitize_text_field($_POST['faq_setting_meta_tag_articles_link']));
			}

			self::aw_pq_faq_add_flash_notice( __('FAQ configuration setting updated'), 'success', true );
		}
		wp_redirect($url);
	}

	public static function aw_pq_faq_categories() {
		global $wpdb;

		$table = new AwPqFaqCategoryList();
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

		$count_one = $table->get_count(1);
		$count_two = $table->get_count(2);
		$count_all = $count_one + $count_two;
		$count_trashed = $table->get_count(0);
		if (isset($_REQUEST['id'])&&is_array($_REQUEST['id'])) {
			$count = count($_REQUEST['id']);
		} else {
			$count = 1;
		}
		$message = '';
		if ('trash' === $table->current_action() && isset($_REQUEST['id'])) {
			/* translators: number count  */
			$message = '<div class="updated below-h2 cutommessage"  ><p>' . sprintf(__('%d Categories moved to the Trash', 'Category List'), intval($count)) . '</p></div>';
		}
		if ('delete' === $table->current_action() && isset($_REQUEST['id'])) {
			/* translators: number count  */
			$message = '<div class="updated below-h2 cutommessage"  ><p>' . sprintf(__('%d Category permanently deleted.', 'Category List'), intval($count)) . '</p></div>';
		}
		if ('untrash' === $table->current_action() && isset($_REQUEST['id'])) {
			/* translators: number count  */
			$message = '<div class="updated below-h2 cutommessage"  ><p>' . sprintf(__('%d Category restore.', 'Category List'), intval($count)) . '</p></div>';
		}
		$notice = maybe_unserialize(get_option( 'faq_cat_flash_notices'));
		if ( ! empty( $notice ) ) {
			printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
			wp_kses($notice['type'], wp_kses_allowed_html('post')),
			wp_kses($notice['dismissible'], wp_kses_allowed_html('post')),
			wp_kses($notice['notice'], wp_kses_allowed_html('post'))
			);
			delete_option( 'faq_cat_flash_notices');
		}

		?>
									
		<div class="wrap">
			<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
			<h1 class="wp-heading-inline"><?php esc_html_e('Category', 'Category List'); ?></h1>
			<a href="admin.php?page=faq_category_page" class="page-title-action">Add New</a>
			<?php echo wp_kses($message, 'post'); ?>
			<hr class="wp-header-end">
			<ul class="subsubsub">
				<li class="all"><a href="admin.php?page=faq_categories" class="current" aria-current="page">All <span class="count">(<?php echo intval($count_all); ?>)</span></a> |</li>
				<li class="trash"><a href="admin.php?status=0&page=faq_categories">Trash <span class="count">(<?php echo intval($count_trashed); ?>)</span></a></li>
			</ul>
			<form id="posts-filter" method="get">
				<p class="search-box">
					<input type="hidden" name="page" class="page" value="faq_categories">	
					<input type="hidden" name="status" class="post_status_page" value="
					<?php 
					if (isset($_GET['status']) && 0  == $_GET['status'] ) {
						echo 0;
					} else {
						echo 1;} 
					?>
					">
					<input type="search" id="post-search-input" name="s" value="<?php echo esc_html($search); ?>">
					<input type="submit" id="search-submit" class="button" value="Search Category">
				</p>
			</form>
		
			<form id="category-table" method="GET">
				<input type="hidden" name="page" value="<?php echo esc_html('faq_categories'); ?>"/>
				<input type="hidden" name="page" value="<?php echo isset($_REQUEST['page']) ? wp_kses($_REQUEST['page'], 'post') : '' ; ?>"/>
				<?php $table->display(); ?>
			</form>
		</div>
		<?php

	}

	public static function aw_pq_faq_new_category_page() {
		$id 							= '';
		$heading 						= 'New categories';
		$faq_enable_no 		 			= '';
		$faq_enable_yes					= '';
		$faq_category_name 				= '';
		$faq_category_slug 				= '';
		$faq_sort_order 				= '';
		$faq_num_articles_page 			= '';
		$faq_category_meta_title 		= '';
		$faq_category_meta_description 	= '';
		$faq_category_icon_file 		= '';
		$faq_article_icon_file 			= '';

		if (isset($_GET['id']) && !empty($_GET['id'])) {
			$heading = 'Edit categories';
			$id = sanitize_text_field($_GET['id']);
			$cat_data = aw_pq_faq_category_row($id);
			if (!empty($cat_data)) {
				$faq_category_name 				= $cat_data->category_name;
				$faq_category_slug 				= $cat_data->category_slug;		
				$faq_enable_category 			= $cat_data->status;	
				$faq_sort_order 				= $cat_data->sort_order;	
				$faq_num_articles_page 			= $cat_data->category_num_articles_page; 
				$faq_category_meta_title 		= $cat_data->category_meta_title;
				$faq_category_meta_description 	= $cat_data->category_meta_description;
				$faq_category_icon_file  		= $cat_data->category_icon_file;
				$faq_article_icon_file  		= $cat_data->articles_list_icon_file;

				if ($faq_enable_category) {
					if ('1' == $faq_enable_category) {
					$faq_enable_yes = 'selected = selected';
					}
					if ('2' == $faq_enable_category) {
					$faq_enable_no = 'selected = selected';
					}
				}	
			}

		}
		 
		$notice = maybe_unserialize(get_option( 'faq_cat_flash_notices'));
		if ( ! empty( $notice ) ) {
				printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
					wp_kses($notice['type'], wp_kses_allowed_html('post')),
					wp_kses($notice['dismissible'], wp_kses_allowed_html('post')),
					wp_kses($notice['notice'], wp_kses_allowed_html('post'))
				);
			delete_option( 'faq_cat_flash_notices');
		}
		?>
		<div class="tab-grid-wrapper">
			<div class="spw-rw clearfix">
				<div class="panel-box product-questions-setting">
					<div class="page-title">
						<h2>
							<?php echo wp_kses($heading, wp_kses_allowed_html('post')); ?>
							<small class="wc-admin-breadcrumb"><a href="<?php echo wp_kses(admin_url(), wp_kses_allowed_html('post')); ?>admin.php?page=faq_categories" aria-label="Return to emails">⤴</a></small>
						</h2>
						<div class="panel-body">
							<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" id="rd_faq_category_form" enctype="multipart/form-data">
								<?php wp_nonce_field( 'save_faq_category_form', 'rdfaq_new_category__nonce' ); ?>
								<input type="hidden" name="action" value="faq_save_category_form">

									<div class="tabcontent faq-general-set" id="genral-setting-tab" style="display:block;">
									<ul>
										<li>
											<label><?php echo wp_kses('Enable category', wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<select name="faq_enable_category">
												<option value="1" <?php echo wp_kses($faq_enable_yes, wp_kses_allowed_html('post')); ?>><?php echo wp_kses('Yes', wp_kses_allowed_html('post')); ?></option>
												<option value="2" <?php echo wp_kses($faq_enable_no, wp_kses_allowed_html('post')); ?> ><?php echo wp_kses('No', wp_kses_allowed_html('post')); ?></option>
											</select>
											<span class="faq_enable_category_error"></span>
											</div>
										</li>
										<li>
											<label><?php echo wp_kses('Category name', wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<input type="text" name="faq_category_name" class="faq_category_name" value="<?php echo wp_kses($faq_category_name, wp_kses_allowed_html('post')); ?>" />
											<span class="faq_category_name_error"></span>
											</div>
										</li>
											<li>
											<?php 
											global $wpdb;
$term_slug = $wpdb->get_var($wpdb->prepare("SELECT * FROM {$wpdb->prefix}terms WHERE slug = %s ", "{$faq_category_slug}") ); 
											?>
											<label><?php echo wp_kses('Category slug', wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<input type="text" name="faq_category_slug" class="
											faq_category_slug" value="<?php echo wp_kses($faq_category_slug, wp_kses_allowed_html('post')); ?>"/>
											<input type="hidden" name="term_slug" class="
											term_slug" value="<?php echo wp_kses($term_slug, wp_kses_allowed_html('post')); ?>"/>
											<span class="faq_category_slug_error"></span>
											</div>
										</li>
										<li>
											<label><?php echo wp_kses('Sort order', wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<input type="text" name="faq_sort_order" class="faq_sort_order" value="<?php echo wp_kses($faq_sort_order, wp_kses_allowed_html('post')); ?>" autocomplete="off" maxlength="5" onkeypress="return aw_gc_checkItExp(event)"/>
											<p><span><?php echo wp_kses('Categories with lower value will appear first', wp_kses_allowed_html('post')); ?></span></p>
											<span class="faq_sort_order_error"></span>
											</div>
										</li>
										<li>
											<label><?php echo wp_kses('Number of articles to display on FAQ main page', wp_kses_allowed_html('post')); ?></label>
											<div class="control">
											<input type="text" name="faq_num_articles_page" class="faq_num_articles_page" value="<?php echo wp_kses($faq_num_articles_page, wp_kses_allowed_html('post')); ?>" autocomplete="off" maxlength="5" onkeypress="return aw_gc_checkItExp(event)"/>
											<p><span><?php echo wp_kses('0 or empty disables limitation', wp_kses_allowed_html('post')); ?></span></p>
											<span class="faq_num_articles_page_error"></span>
											</div>
										</li>
										<li>
											<label><?php echo wp_kses(' Category icon', wp_kses_allowed_html('post')); ?></label>
										  <div class="control">
											  <div class="faq-upload-panel">
												<?php
												$src = '';
												$imagname = array();
												if (isset($faq_category_icon_file) && '' != $faq_category_icon_file) {
													$path = wp_get_upload_dir();
													$imagepath = explode('uploads', $faq_category_icon_file) ;
													$fullpath  = $path['basedir'] . $imagepath[1];
													if (file_exists($fullpath)) {
														$src = $faq_category_icon_file; 
														$imagname = explode('/', $src);
														?>
														<input type="file" name="faq_category_icon" id="faq_category_icon" style="display:none">
														<a id="closebackimage" class="faq_close_upload" href="javascript:void(0)" post-id="<?php echo wp_kses_post($id); ?>">X</a>
														<input type="hidden" data-value="" value="<?php echo esc_url($src); ?>" name="category_icon_file" id="faq_category_image">
														<img id="faq_category_display-image" src="<?php echo esc_url($faq_category_icon_file); ?>">
	
														<?php
													}
												} else {
													?>
												<input type="file" name="faq_category_icon" id="faq_category_icon">
												<img width="20%" height="20%" id="faq_category_display-image" src="
												<?php 
													if (!empty($src)) {
	echo esc_url($src);
													} else {
													echo ' ';} 
													?>
												" alt="">
												<a id="closebackimage" class="faq_close_upload" href="javascript:void(0)" post-id="<?php echo wp_kses_post($id); ?>">X</a>
												<input type="hidden" data-value="" name="faq_category_image" value="<?php echo esc_url($src); ?>" id="faq_category_image">
												<?php
												}
												?>
												<br>
												<span align="center" id="uploadedimage" style="color: #000000;font-style: normal;"><?php echo wp_kses_post(end($imagname)); ?></span>
												</div>
											</div>
										</li>
										<li>
											<label><?php echo wp_kses(' Article list icon', wp_kses_allowed_html('post')); ?></label>
											<div class="control">
												<div class="faq-upload-panel">
													<?php
													$src = '';
													$imagname = array();
													if (isset($faq_article_icon_file) && '' != $faq_article_icon_file) {
														$path = wp_get_upload_dir();
														$imagepath = explode('uploads', $faq_article_icon_file) ;
														$fullpath  = $path['basedir'] . $imagepath[1];
														if (file_exists($fullpath)) {
															$src = $faq_article_icon_file; 
															$imagname = explode('/', $src);
															?>
															<input type="file" name="faq_article_icon" id="faq_article_icon" style="display:none;">
															<a id="artclosebackimage" class="faq_close_upload" href="javascript:void(0)" post-id="<?php echo wp_kses_post($id); ?>">X</a>
															<input type="hidden" data-value="" value="
															<?php 
															if (!empty($src)) {
echo esc_url($src);
															} else {
															echo ' ';} 
															?>
															" name="articles_list_icon_file" id="faq_art_image">
															<img id="faq_art_display-image" src="<?php echo esc_url($faq_article_icon_file); ?>">		
															<?php
														}
													} else {
														?>
													<input type="file" name="faq_article_icon" id="faq_article_icon">
													<img width="20%" height="20%" id="faq_art_display-image" src="
													<?php 
														if (!empty($src)) {
	echo esc_url($src);
														} else {
														echo ' ';} 
														?>
													" alt="">
													<a id="artclosebackimage" class="faq_close_upload" href="javascript:void(0)" post-id="<?php echo wp_kses_post($id); ?>">X</a>
	
													<input type="hidden" data-value="" name="faq_art_image" value="<?php echo esc_url($src); ?>" id="faq_art_image">
													<?php
													}
													?>
												<br>
												<span align="center" id="artuploadedimage" style="color: #000000;font-style: normal;"><?php echo wp_kses_post(end($imagname)); ?></span>
												</div>
											</div>
										</li>
									</ul>
									<h3>
									<?php echo wp_kses('SEO', wp_kses_allowed_html('post')); ?>
									</h3>
									<div class="tabcontent faq-seo" id="faq-seo" style="display:block;">
										<ul>
											<li>
												<label><?php echo wp_kses('Meta title', wp_kses_allowed_html('post')); ?></label>
												<div class="control">
												<input type="text" name="faq_category_meta_title" class="
												faq_category_meta_title" value="<?php echo wp_kses($faq_category_meta_title, wp_kses_allowed_html('post')); ?>"/>
												<span class="faq_category_meta_title_error"></span>
												</div>
											</li>
											<li>
												<label><?php echo wp_kses('  Meta description', wp_kses_allowed_html('post')); ?></label>
												<div class="control">
													<textarea rows="3" cols="20" class="faq_category_meta_description" type="textarea" name="faq_category_meta_description" style="width:400px; height: 75px;" value="<?php echo wp_kses($faq_category_meta_description, wp_kses_allowed_html('post')); ?>"><?php echo wp_kses($faq_category_meta_description, wp_kses_allowed_html('post')) ; ?></textarea>
													<span class="faq_category_meta_description_error"></span>
												</div>
											</li>											
										</ul> 
									</div>  
									<div class="submit">
									<?php 
									if (isset($_GET['id']) && !empty($_GET['id'])) {
										$id = sanitize_text_field($_GET['id']);
										?>
										<input name="category_id" type="hidden" id= "<?php echo wp_kses($id, wp_kses_allowed_html('post')); ?>" value="<?php echo wp_kses($id, wp_kses_allowed_html('post')); ?>"><input type="submit" class="button button-primary" value="<?php echo wp_kses('Update', wp_kses_allowed_html('post')); ?>" name="faq_category_submit" onclick="return faqcheckcategory()"/>
										<?php
									} else {
;
										?>
				
										<input type="submit" class="button button-primary" value="<?php echo wp_kses('Save', wp_kses_allowed_html('post')); ?>" name="faq_category_submit" onclick="return faqcheckcategory()"/><?php } ?>	
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
	public static function aw_pq_faq_save_category_form() {
		global $wpdb; 
		$faq_category_icon_file = '';
		$faq_article_icon_file = '';
		$term_slug = '';
		$id = '';
		$flag = 0;
		$url =  admin_url() . 'admin.php?page=faq_categories';
		if (isset($_POST['category_id']) && !empty($_POST['category_id'])) {
			$id = sanitize_text_field($_POST['category_id']);
			$url =  admin_url() . 'admin.php?page=faq_category_page&action=edit&id=' . $id;
		}

		
		if (isset($_POST['rdfaq_new_category__nonce'])) {
			$rdfaq_new_category__nonce = sanitize_text_field($_POST['rdfaq_new_category__nonce']);
		}

		if ( !wp_verify_nonce( $rdfaq_new_category__nonce, 'save_faq_category_form' )) {
			wp_die('Our Site is protected');
		}

		if (isset($_POST['faq_category_submit'])) {
			if (isset($_POST['faq_enable_category']) && !empty($_POST['faq_enable_category'])) {
				if (1 == $_POST['faq_enable_category']) {
					$faq_enable_category = 1;
				}
				if (2 == $_POST['faq_enable_category']) {
					$faq_enable_category = 2;
				}
			}  


			if (isset($_POST['faq_category_name'])) {
				$faq_category_name = sanitize_text_field($_POST['faq_category_name']);
			} else {
				$faq_category_name = '';
			}
			if (isset($_POST['faq_category_slug'])) {
				$faq_category_slug = sanitize_text_field($_POST['faq_category_slug']);
			} else {
				$faq_category_slug = '';
			}
			if (isset($_POST['term_slug']) && !empty($_POST['term_slug'])) {
				$term_slug = sanitize_text_field($_POST['term_slug']);
			}

			if (isset($_POST['faq_sort_order'])) {
				$faq_sort_order = sanitize_text_field($_POST['faq_sort_order']);
			} else {
				$faq_sort_order = '';
			}
			if (isset($_POST['faq_num_articles_page'])) {
				$faq_num_articles_page = sanitize_text_field($_POST['faq_num_articles_page']);
			} else {
				$faq_num_articles_page = '';
			}
			
			if (isset($_POST['faq_category_meta_title'])) {
				$faq_category_meta_title = sanitize_text_field($_POST['faq_category_meta_title']);
			} else {
				$faq_category_meta_title = '';
			}
			if (isset($_POST['faq_category_meta_description'])) {
				$faq_category_meta_description = sanitize_text_field($_POST['faq_category_meta_description']);
			} else {
				$faq_category_meta_description = '';
			}
			$last_modified = 		gmdate('Y-m-d H:i:s');					
			
			/*insert image code*/
			$Save_FILES=$_FILES;
			if (isset($_POST['category_icon_file'])&& !empty($_POST['category_icon_file'])) {
				
				$wordpress_upload_dir = wp_upload_dir();    
				if (isset($_FILES['faq_category_icon']) && !empty($_FILES['faq_category_icon'])) {
					$image = json_encode($_FILES);
					$image = json_decode($image, true);
					$image = array_filter($image['faq_category_icon']);		
										
					if (!empty($image['tmp_name'] )) {
						$extension = pathinfo($image['name'], PATHINFO_EXTENSION);
						$allowed = array('jpg','jpeg','png');
						$new_file_path = $wordpress_upload_dir['path'] . '/' . $image['name'];
						$new_file_mime = mime_content_type( $image['tmp_name'] );
					}
					$uploaded_datetime  = gmdate('M d, Y ', strtotime(gmdate('d-m-Y h:i:s')));
				}

				if (!empty($image['name']) && in_array($extension, $allowed)  && in_array( $new_file_mime, get_allowed_mime_types())) {

					$_FILES = array('upload_file' => $image);
					$attachment_id = media_handle_upload('upload_file', 0);
					$faq_category_icon_file = wp_get_attachment_url( $attachment_id );
				} else {
					self::aw_pq_faq_cat_add_flash_notice( __('Invalid image type or Invalid content type'), 'error', true );
				}
				if (empty($faq_category_icon_file)) {
					$faq_category_icon_file = sanitize_text_field($_POST['category_icon_file']);
				}
			} else {
				if (!empty($_FILES['faq_category_icon']['name'])) {
					if (isset($_FILES['faq_category_icon']) && !empty($_FILES['faq_category_icon'])) {
						$wordpress_upload_dir = wp_upload_dir();             
						$image = json_encode($_FILES);
						$image = json_decode($image, true);
						$image = array_filter($image['faq_category_icon']);

						$extension = pathinfo($image['name'], PATHINFO_EXTENSION);
						$allowed = array('jpg','jpeg','png');
						$new_file_path = $wordpress_upload_dir['path'] . '/' . $image['name'];
						$new_file_mime = mime_content_type( $image['tmp_name'] );
						$uploaded_datetime  = gmdate('M d, Y ', strtotime(gmdate('d-m-Y h:i:s')));
					}

					if (!empty($image['name']) && in_array($extension, $allowed)  && in_array( $new_file_mime, get_allowed_mime_types())) {
						$_FILES = array('upload_file' => $image);
						$attachment_id = media_handle_upload('upload_file', 0);
						$faq_category_icon_file = wp_get_attachment_url( $attachment_id );	            	
					} else {
						self::aw_pq_faq_cat_add_flash_notice( __('Invalid image type or Invalid content type'), 'error', true );
					}

				}
			}
			$_FILES=$Save_FILES;
			if (isset($_POST['articles_list_icon_file'])&& !empty($_POST['articles_list_icon_file'])) {
				
				$wordpress_upload_dir = wp_upload_dir(); 
				if (isset($_FILES['faq_article_icon'])&& !empty($_FILES['faq_article_icon'])) {   
					$image_art_list = json_encode($_FILES);					
					$image_art_list = json_decode($image_art_list, true);
					$image_art_list = array_filter($image_art_list['faq_article_icon']);

					
					if (!empty($image_art_list['tmp_name'] )) {
						$extension = pathinfo($image_art_list['name'], PATHINFO_EXTENSION);
						$allowed = array('jpg','jpeg','png');
						$new_file_path = $wordpress_upload_dir['path'] . '/' . $image_art_list['name'];
						$new_file_mime = mime_content_type( $image_art_list['tmp_name'] );
					}
					$uploaded_datetime  = gmdate('M d, Y ', strtotime(gmdate('d-m-Y h:i:s')));
				}

				if (!empty($image_art_list['name']) && in_array($extension, $allowed)  && in_array( $new_file_mime, get_allowed_mime_types())) {

					$_FILES = array('upload_file' => $image_art_list);
					$attachment_id = media_handle_upload('upload_file', 0);
					$faq_article_icon_file = wp_get_attachment_url( $attachment_id );
				} else {
					self::aw_pq_faq_cat_add_flash_notice( __('Invalid image type or Invalid content type'), 'error', true );
				}
				if (empty($faq_article_icon_file )) {
					$faq_article_icon_file = sanitize_text_field($_POST['articles_list_icon_file']);
				}
			} else {
				if (!empty($_FILES['faq_article_icon']['name'])) {

					$wordpress_upload_dir = wp_upload_dir();     
					if (isset($_FILES['faq_article_icon'])&& !empty($_FILES['faq_article_icon'])) {        
						$image_art_list = json_encode($_FILES);
						$image_art_list = json_decode($image_art_list, true);
						$image_art_list = array_filter($image_art_list['faq_article_icon']);

						$extension = pathinfo($image_art_list['name'], PATHINFO_EXTENSION);
						$allowed = array('jpg','jpeg','png');
						$new_file_path = $wordpress_upload_dir['path'] . '/' . $image_art_list['name'];
						$new_file_mime = mime_content_type( $image_art_list['tmp_name'] );
						$uploaded_datetime  = gmdate('M d, Y ', strtotime(gmdate('d-m-Y h:i:s')));
					}

					if (!empty($image_art_list['name']) && in_array($extension, $allowed)  && in_array( $new_file_mime, get_allowed_mime_types())) {

						$_FILES = array('upload_file' =>$image_art_list);
						$attachment_id = media_handle_upload('upload_file', 0);
						$faq_article_icon_file = wp_get_attachment_url( $attachment_id );	 

					} else {
						self::aw_pq_faq_cat_add_flash_notice( __('Invalid image type or Invalid content type'), 'error', true );
					}
				}
			}
			
			if ( '' == $faq_category_slug) {
				$faq_category_slug = strtolower(trim($faq_category_name));
				$faq_category_slug = str_replace(' ', '-', $faq_category_slug);
			} else {
				$faq_category_slug = strtolower(trim($faq_category_slug));
				$faq_category_slug = str_replace(' ', '-', $faq_category_slug);

			}
			$faq_category_slug_update = $faq_category_slug;
			$data_exits = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aw_pq_faq_category_list");
	
			$flags      = 0;

			foreach ($data_exits as $date) {
				$category_name = $date->category_name;
				$category_slug = $date->category_slug;
				if ($category_name ==  $faq_category_name) {
					$flag = 1;
					self::aw_pq_faq_cat_add_flash_notice( __('category exist already'), 'error', true );
					$url =  admin_url() . 'admin.php?page=faq_category_page';
				}
				
				if ($category_slug == $faq_category_slug) {
					$flags++;
				}
				if ($category_slug == $faq_category_slug . '-' . +$flags) {
					$flags++;
				}

			}
			if (0 == $flags ) {
				$faq_category_slug = $faq_category_slug;
			} else {
				$faq_category_slug = $faq_category_slug . '-' . +$flags;
			}

			$db_table = $wpdb->prefix . 'aw_pq_faq_category_list';
			$post_array = array(
				'category_name' 				=> $faq_category_name,
				'category_slug' 				=> $faq_category_slug,
				'status' 						=> $faq_enable_category,
				'sort_order' 					=> $faq_sort_order,
				'date'							=> $last_modified,
				'category_num_articles_page'	=> $faq_num_articles_page,
				'category_icon_file'			=> $faq_category_icon_file,
				'articles_list_icon_file'		=> $faq_article_icon_file,
				'category_meta_title'			=> $faq_category_meta_title,
				'category_meta_description' 	=> $faq_category_meta_description	
					);
			$post_array_update = array(
				'category_name' 				=> $faq_category_name,
				'category_slug' 				=> $faq_category_slug_update,
				'status' 						=> $faq_enable_category,
				'sort_order' 					=> $faq_sort_order,
				'date'							=> $last_modified,
				'category_num_articles_page'	=> $faq_num_articles_page,
				'category_icon_file'			=> $faq_category_icon_file,
				'articles_list_icon_file'		=> $faq_article_icon_file,
				'category_meta_title'			=> $faq_category_meta_title,
				'category_meta_description' 	=> $faq_category_meta_description	
					);
			
			if ('' != $id ) {
				$result = $wpdb->update($db_table, $post_array_update, array('id'=>$id));
				if ('' != $term_slug) {
					$term_table = $wpdb->prefix . 'terms';
					$term_array = array(
						'name' => $faq_category_name,
						'slug' => $faq_category_slug_update,
						'term_group' => '0',
						);
					$wpdb->update($term_table, $term_array, array('term_id'=>$term_slug));
				}

				self::aw_pq_faq_cat_add_flash_notice( __('Category updated succesfully'), 'success', true );
				$url =  admin_url() . 'admin.php?page=faq_category_page&action=edit&id=' . $id;		 	
			} else {
				if (1 != $flag) {
					$result = $wpdb->insert($db_table, $post_array);
					$record_exist = $wpdb->get_var( $wpdb->prepare("SELECT term_id FROM {$wpdb->prefix }terms WHERE name = %s AND slug = %s ", "{$faq_category_name}", "{$faq_category_slug}"));

					if (empty($record_exist)) {
						$db_table_1 = $wpdb->prefix . 'terms';
						$post_array_1 = array(
							'name' => $faq_category_name,
							'slug' => $faq_category_slug,
							'term_group' => '0',
							);
						$wpdb->insert($db_table_1, $post_array_1);					

						$term_id = $wpdb->get_var( $wpdb->prepare("SELECT term_id FROM {$wpdb->prefix }terms WHERE slug= %s", "{$faq_category_slug}"));	

						$db_table_2 = $wpdb->prefix . 'term_taxonomy';
						$post_array_2 = array(
							'term_id' => $term_id,
							'taxonomy' => 'faq_cat',
							'parent' => '0',
							'count' =>'0'
							);
						$wpdb->insert($db_table_2, $post_array_2);
					}
					self::aw_pq_faq_cat_add_flash_notice( __('Category inserted succesfully'), 'success', true );
				}				
			}
		}
		wp_redirect($url);		
	}		
}
