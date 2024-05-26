<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class AwProductLabelPublic {
	public static function aw_pl_label_shop_page ( $productThumb, $product) {
		$all_rule 				= aw_pl_all_rule();
		$gen_settings 			= aw_pl_genral_setting();
		$no_of_labels 			= $gen_settings['no_of_labels_single_product'];
		$checkarr 				= array();
		$product_array 			= array();
		$same_pos 				= array();
		$same_value 			= '';
		$product_count			= 0;
		$main_class 			= '<div class="aw_product_label">';
		$today_date 			= gmdate('Y-m-d');
		$currency_symbol 		= get_woocommerce_currency_symbol();
		$regular_price  		= $product->get_regular_price(); 
		$sale_price     		= $product->get_sale_price();
		$sku 					= $product->get_sku();
		$quantity 				= $product->get_stock_quantity();
		$from_date 				= $product->get_date_on_sale_from();
		$to_date 				= $product->get_date_on_sale_to();

		foreach ($all_rule as $rule => $value) {
			$style 							= '';
			$textstyle 						= '';
			$small_style 					= '';
			$small_textstyle 				= '';
			$labeltext 						= '';
			$smalllabeltext 				= '';
			$label_style 					= '';
			$label_id 						= $value->label_id;
			$priority 						= $value->priority;
			$view_rule			 			= $value->rule_allow_to_user;
			$start_date 					= $value->start_date;
			$end_date 						= $value->end_date;
			$product_id 					= $value->product_id;
			$frontend_label_text 			= $value->frontend_label_text;
			$frontend_medium_text 			= $value->frontend_medium_text;
			$frontend_small_text 			= $value->frontend_small_text;
			$label_name 					= $value->name;
			$label_position 				= $value->position;
			$label_type	 					= $value->type;
			$shape_type						= $value->shape_type;	
			$label_image 					= $value->label_image;	
			$label_size 					= $value->label_size;
			$medium_label_container_css  	= $value->medium_label_container_css;
			$medium_label_text_css  		= $value->medium_label_text_css;
			$small_label_container_css  	= $value->small_label_container_css;
			$small_label_text_css  			= $value->small_label_text_css;
			$product_arr 					= explode(',', $product_id);

			if ( $start_date <= $today_date && $today_date <= $end_date ) {
				if ( ( '' == $view_rule || 'anyone' == $view_rule )  || ( 'loggedinuser' == $view_rule && is_user_logged_in() ) ) {

					if (!empty($frontend_medium_text)) {
						$labeltext = $frontend_medium_text;
						$labeltext = preg_replace('/{price}/', $regular_price, $labeltext);
						$labeltext = preg_replace('/{sku}/', $sku, $labeltext);
						$labeltext = preg_replace('/{special_price}/', $sale_price, $labeltext);
						$labeltext = preg_replace('/{stock}/', $quantity, $labeltext);
						$labeltext = preg_replace('/{br}/', '<br/>', $labeltext);

						if ( !empty($sale_price) ) {
							$amount_saved = $regular_price - $sale_price;
							$percentage = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
							$labeltext = preg_replace('/{save_amount}/', $currency_symbol . $amount_saved, $labeltext);	
							$labeltext = preg_replace('/{save_percent}/', number_format($percentage, 0, '', '') . '%', $labeltext);
						}
						
						if (!empty($to_date) ) {
							$datetime1 = new DateTime($from_date);
							$datetime2 = new DateTime($to_date);

							$difference = $datetime1->diff($datetime2);
							$left_days 	= $difference->d;
							$left_hours = $left_days*24;

							$labeltext = preg_replace('/{spdl}/', $left_days, $labeltext);
							$labeltext = preg_replace('/{sphl}/', $left_hours, $labeltext);
						}																
						
						$color =  wc_get_product_terms($product->get_id(), 'pa_color', array( 'fields' => 'names' ) );
						if (!empty($color)) {
							$labeltext = preg_replace('/{attribute: code}/', $color[0], $labeltext);	
						}
					} 

					if (!empty($frontend_small_text)) {
						$smalllabeltext = $frontend_small_text;
						$smalllabeltext = preg_replace('/{price}/', $regular_price, $smalllabeltext);
						$smalllabeltext = preg_replace('/{sku}/', $sku, $smalllabeltext);
						$smalllabeltext = preg_replace('/{special_price}/', $sale_price, $smalllabeltext);
						$smalllabeltext = preg_replace('/{stock}/', $quantity, $smalllabeltext);
						$smalllabeltext = preg_replace('/{br}/', '<br/>', $smalllabeltext);

						if ( !empty($sale_price) ) {
							$amount_saved = $regular_price - $sale_price;
							$percentage = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
							$smalllabeltext = preg_replace('/{save_amount}/', $currency_symbol . $amount_saved, $smalllabeltext);	
							$smalllabeltext = preg_replace('/{save_percent}/', number_format($percentage, 0, '', '') . '%', $smalllabeltext);
						}
								
						if ( ! empty($to_date) ) {
							$datetime1 = new DateTime($from_date);
							$datetime2 = new DateTime($to_date);

							$difference = $datetime1->diff($datetime2);
							$left_days 	= $difference->d;
							$left_hours = $left_days*24;

							$smalllabeltext = preg_replace('/{spdl}/', $left_days, $smalllabeltext);
							$smalllabeltext = preg_replace('/{sphl}/', $left_hours, $smalllabeltext);
						}																
						
						$color =  wc_get_product_terms($product->get_id(), 'pa_color', array( 'fields' => 'names' ) );
						if (!empty($color)) {
							$smalllabeltext = preg_replace('/{attribute: code}/', $color[0], $smalllabeltext);	
						}
					}

					if (''!= $medium_label_container_css) {
						$style = $medium_label_container_css;
					}

					if (''!= $medium_label_text_css) {
						$textstyle = $medium_label_text_css;
					}

					if (''!= $small_label_container_css) {
						$small_style = $small_label_container_css;
					}
					if (''!= $small_label_text_css) {
						$small_textstyle = $small_label_text_css;
					}

					foreach ($product_arr as $pro_id) {
						if ($product->get_id() == $pro_id) {
							if ($product_count < $no_of_labels  && !empty($no_of_labels) ) {
								$same_pos[$label_id][] = $label_position;
								$product_array[trim($pro_id)][$label_id] = array(
									'label_id'			=> $label_id,
									'labeltext'			=> $labeltext,
									'smalllabeltext' 	=> $smalllabeltext,
									'label_type'		=> $label_type,
									'shape_type'		=> $shape_type,
									'label_position'	=> $label_position,
									'label_image'		=> $label_image,
									'style'				=> $style,
									'textstyle' 		=> $textstyle,
									'small_style'		=> $small_style,
									'small_textstyle' 	=> $small_textstyle,
								);
								$product_count = count($product_array[trim($pro_id)]);			
							}
							if (empty($no_of_labels)) {
								$same_pos[$label_id][] = $label_position;
								$product_array[trim($pro_id)][$label_id] = array(
									'label_id'			=> $label_id,
									'labeltext'			=> $labeltext,
									'smalllabeltext' 	=> $smalllabeltext,
									'label_type'		=> $label_type,
									'shape_type'		=> $shape_type,
									'label_position'	=> $label_position,
									'label_image'		=> $label_image,
									'style'				=> $style,
									'textstyle' 		=> $textstyle,
									'small_style'		=> $small_style,
									'small_textstyle' 	=> $small_textstyle,
								);
							}
						}
					}
				}
			}
		}
		$same_value = array_count_values(array_column($same_pos, 0));
		if (!empty($product_array )) {
			$productThumb = aw_display_label($product_array[$product->get_id()], $productThumb, $same_value);
		}
		return $main_class . $productThumb . '</div>';
	}

	public static function aw_pl_label_single_product_page( $sprintf, $post_id ) {
		global $product,$wpdb;
		$nextposition 			= 'next_price';
		$gen_settings 			= aw_pl_genral_setting();
		$no_of_labels 			= $gen_settings['no_of_labels_single_product'];
		$product_array 			= array();
		$same_pos 				= array();
		$same_value 			= '';
		$product_count 			= 0;
		$id 					= wp_get_post_parent_id($post_id);
		$gen_settings 			= aw_pl_genral_setting();
		$label_distance 		= $gen_settings['label_distance'];
		$label_alignment		= $gen_settings['label_alignment'];
		$no_of_labels 			= $gen_settings['no_of_labels_single_product'];
		$main_class 			= '<div class="aw_product_label">';
		$currency_symbol 		= get_woocommerce_currency_symbol();
		$regular_price  		= $product->get_regular_price(); 
		$sale_price     		= $product->get_sale_price();
		$sku 					= $product->get_sku();
		$quantity 				= $product->get_stock_quantity();
		$from_date 				= $product->get_date_on_sale_from();
		$to_date 				= $product->get_date_on_sale_to();

		$all_rule = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pl_product_rule AS rule INNER JOIN {$wpdb->prefix}aw_pl_product_label AS label ON rule.label_id = label.id  WHERE rule.rule_status = %d AND label.position != %s ORDER BY rule.priority ASC,rule.rule_last_updated ASC", 1, "{$nextposition}"));
		
		foreach ($all_rule as $rule => $value) {
			$style 							= '';
			$textstyle 						= '';
			$small_style 					= '';
			$small_textstyle 				= '';
			$labeltext 						= '';
			$smalllabeltext 				= '';
			$label_id 						= $value->label_id;
			$priority 						= $value->priority;
			$view_rule			 			= $value->rule_allow_to_user;
			$start_date 					= $value->start_date;
			$end_date 						= $value->end_date;
			$product_id 					= $value->product_id;
			$frontend_label_text 			= $value->frontend_label_text;
			$frontend_medium_text 			= $value->frontend_medium_text;
			$frontend_small_text 			= $value->frontend_small_text;
			$label_name 					= $value->name;
			$label_position 				= $value->position;
			$label_type	 					= $value->type;
			$shape_type						= $value->shape_type;	
			$label_image 					= $value->label_image;	
			$label_size 					= $value->label_size;
			$label_container_css  			= $value->label_container_css;
			$label_text_css  				= $value->label_text_css;
			$small_label_container_css  	= $value->small_label_container_css;
			$small_label_text_css  			= $value->small_label_text_css;
			$product_arr 					= explode(',', $product_id);
			$today_date 					= gmdate('Y-m-d');
			if ( $start_date <= $today_date && $today_date <= $end_date ) {
				if ( ( '' == $view_rule || 'anyone' == $view_rule )  || ( 'loggedinuser' == $view_rule && is_user_logged_in() )) {

					if (!empty($frontend_label_text)) {
						$labeltext = $frontend_label_text;
						$labeltext 	= preg_replace('/{price}/', $regular_price, $labeltext);
						$labeltext 	= preg_replace('/{sku}/', $sku, $labeltext);
						$labeltext = preg_replace('/{special_price}/', $sale_price, $labeltext);
						$labeltext = preg_replace('/{stock}/', $quantity, $labeltext);
						$labeltext = preg_replace('/{br}/', '<br/>', $labeltext);
						if ( !empty($sale_price) ) {
							$amount_saved = $regular_price - $sale_price;
							$percentage = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
							$labeltext = preg_replace('/{save_amount}/', $currency_symbol . $amount_saved, $labeltext);	
							$labeltext = preg_replace('/{save_percent}/', number_format($percentage, 0, '', '') . '%', $labeltext);
						}
								
						if ( ! empty($to_date) ) {
							$datetime1 = new DateTime($from_date);
							$datetime2 = new DateTime($to_date);

							$difference = $datetime1->diff($datetime2);
							$left_days 	= $difference->d;
							$left_hours = $left_days*24;

							$labeltext = preg_replace('/{spdl}/', $left_days, $labeltext);
							$labeltext = preg_replace('/{sphl}/', $left_hours, $labeltext);
						}																
						
						$color =  wc_get_product_terms($id, 'pa_color', array( 'fields' => 'names' ) );
						if (!empty($color)) {
							$labeltext = preg_replace('/{attribute: code}/', $color[0], $labeltext);	
						}
					}

					if (!empty($frontend_small_text)) {
						$smalllabeltext = $frontend_small_text;
						$smalllabeltext = preg_replace('/{price}/', $regular_price, $smalllabeltext);
						$smalllabeltext = preg_replace('/{sku}/', $sku, $smalllabeltext);
						$smalllabeltext = preg_replace('/{special_price}/', $sale_price, $smalllabeltext);
						$smalllabeltext = preg_replace('/{stock}/', $quantity, $smalllabeltext);
						$smalllabeltext = preg_replace('/{br}/', '<br/>', $smalllabeltext);
						if ( !empty($sale_price) ) {
							$amount_saved = $regular_price - $sale_price;
							$percentage = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
							$smalllabeltext = preg_replace('/{save_amount}/', $currency_symbol . $amount_saved, $smalllabeltext);	
							$smalllabeltext = preg_replace('/{save_percent}/', number_format($percentage, 0, '', '') . '%', $smalllabeltext);
						}
								
						if ( ! empty($to_date) ) {
							$datetime1 = new DateTime($from_date);
							$datetime2 = new DateTime($to_date);

							$difference = $datetime1->diff($datetime2);
							$left_days 	= $difference->d;
							$left_hours = $left_days*24;

							$smalllabeltext = preg_replace('/{spdl}/', $left_days, $smalllabeltext);
							$smalllabeltext = preg_replace('/{sphl}/', $left_hours, $smalllabeltext);
						}																
						
						$color =  wc_get_product_terms($id, 'pa_color', array( 'fields' => 'names' ) );
						if (!empty($color)) {
							$smalllabeltext = preg_replace('/{attribute: code}/', $color[0], $smalllabeltext);	
						}
					} 

					if (''!= $label_container_css) {
						$style = $label_container_css;
					}

					if (''!= $label_text_css) {
						$textstyle = $label_text_css;
					}

					if (''!= $small_label_container_css) {
						$small_style = $small_label_container_css;
					}
					
					if (''!= $small_label_text_css) {
						$small_textstyle = $small_label_text_css;
					}

					foreach ($product_arr as $pro_id) {
						if ($id == $pro_id) {
							if ($product_count < $no_of_labels && !empty($no_of_labels) ) {
								$product_array[trim($pro_id)][$label_id] = array(
									'label_id'			=> $label_id,
									'labeltext'			=> $labeltext,
									'smalllabeltext' 	=> $smalllabeltext,
									'label_type'		=> $label_type,
									'shape_type'		=> $shape_type,
									'label_position'	=> $label_position,
									'label_image'		=> $label_image,
									'style'				=> $style,
									'textstyle' 		=> $textstyle,
									'small_style'		=> $small_style,
									'small_textstyle' 	=> $small_textstyle,
								);
								$product_count = count($product_array[trim($pro_id)]);
								$same_pos[$label_id][] = $label_position;	
							}
							if (empty($no_of_labels)) {
								$same_pos[$label_id][] = $label_position;
								$product_array[trim($pro_id)][$label_id] = array(
									'label_id'			=> $label_id,
									'labeltext'			=> $labeltext,
									'smalllabeltext' 	=> $smalllabeltext,
									'label_type'		=> $label_type,
									'shape_type'		=> $shape_type,
									'label_position'	=> $label_position,
									'label_image'		=> $label_image,
									'style'				=> $style,
									'textstyle' 		=> $textstyle,
									'small_style'		=> $small_style,
									'small_textstyle' 	=> $small_textstyle,
								);	
							}
						}
					}
				}
			}
		}
		$same_value = array_count_values(array_column($same_pos, 0));
		if (!empty($product_array )) {
			$sprintf = aw_display_label($product_array[$id], $sprintf, $same_value);
		}
		return $main_class . $sprintf . '</div>';
	}

	public static function aw_pl_label_next_price_shop_page() {
		global $product,$wpdb;
		$productThumb 				= '';
		$Thumb 						= '';
		$product_count 				= 0;
		$nextposition 				= 'next_price';
		$product_array 				= array();
		$num_label_same_product 	= array();
		$gen_settings 				= aw_pl_genral_setting();
		$label_distance 			= $gen_settings['label_distance'];
		$label_alignment			= $gen_settings['label_alignment'];
		$no_of_labels_next_price 	= $gen_settings['no_of_labels_next_price'];
		$currency_symbol 			= get_woocommerce_currency_symbol();
		$regular_price  			= $product->get_regular_price(); 
		$sale_price     			= $product->get_sale_price();
		$sku 						= $product->get_sku();
		$quantity 					= $product->get_stock_quantity();
		$from_date 					= $product->get_date_on_sale_from();
		$to_date 					= $product->get_date_on_sale_to();
		

		$all_rule = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pl_product_rule AS rule INNER JOIN {$wpdb->prefix}aw_pl_product_label AS label ON rule.label_id = label.id  WHERE rule.rule_status = %d AND label.position = %s ORDER BY rule.priority ASC,rule.rule_last_updated ASC", 1, "{$nextposition}"));

		foreach ($all_rule as $rule => $value) {
			$style 						= '';
			$textstyle 					= '';
			$labeltext 					= '';
			$label_style 				= '';
			$position 					= '';
			$margin 					= '';

			$label_id 					= $value->label_id;
			$priority 					= $value->priority;
			$view_rule			 		= $value->rule_allow_to_user;
			$start_date 				= $value->start_date;
			$end_date 					= $value->end_date;
			$product_id 				= $value->product_id;
			$frontend_label_text 		= $value->frontend_label_text;
			$frontend_medium_text 		= $value->frontend_medium_text;
			$frontend_small_text 		= $value->frontend_small_text;
			$label_name 				= $value->name;
			$label_position 			= $value->position;
			$label_type	 				= $value->type;
			$shape_type					= $value->shape_type;	
			$label_image 				= $value->label_image;	
			$label_size 				= $value->label_size;
			$medium_label_container_css = $value->medium_label_container_css;
			$medium_label_text_css  	= $value->medium_label_text_css;
			$product_arr 				= explode(',', $product_id);
			$today_date 				= gmdate('Y-m-d');

			if ( $start_date <= $today_date && $today_date <= $end_date ) {
				if ( ( '' == $view_rule || 'anyone' == $view_rule )  || ( 'loggedinuser' == $view_rule && is_user_logged_in() ) ) {

					if (!empty($frontend_medium_text)) {						
						$labeltext = $frontend_medium_text;
						$labeltext 	= preg_replace('/{price}/', $regular_price, $labeltext);
						$labeltext 	= preg_replace('/{sku}/', $sku, $labeltext);
						$labeltext = preg_replace('/{special_price}/', $sale_price, $labeltext);
						$labeltext = preg_replace('/{stock}/', $quantity, $labeltext);
						$labeltext = preg_replace('/{br}/', '<br/>', $labeltext);

						if ( !empty($sale_price) ) {
							$amount_saved = $regular_price - $sale_price;
							$percentage = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
							$labeltext = preg_replace('/{save_amount}/', $currency_symbol . $amount_saved, $labeltext);	
							$labeltext = preg_replace('/{save_percent}/', number_format($percentage, 0, '', '') . '%', $labeltext);
						}
						
						if ( ! empty($to_date) ) {
							$datetime1 = new DateTime($from_date);

							$datetime2 = new DateTime($to_date);

							$difference = $datetime1->diff($datetime2);
							$left_days 	= $difference->d;
							$left_hours = $left_days*24;

							$labeltext = preg_replace('/{spdl}/', $left_days, $labeltext);
							$labeltext = preg_replace('/{sphl}/', $left_hours, $labeltext);
						}																
						
						$color =  wc_get_product_terms($product->get_id(), 'pa_color', array( 'fields' => 'names' ) );
						if (!empty($color)) {
							$labeltext = preg_replace('/{attribute: code}/', $color[0], $labeltext);	
						}								
					} 

					if (''!= $medium_label_container_css) {
							$style = $medium_label_container_css;
					}
					if (''!= $medium_label_text_css) {
						$textstyle = $medium_label_text_css;
					}

					foreach ($product_arr as $pro_id) {
						if ($product->get_id() == $pro_id) {
							if ($product_count < $no_of_labels_next_price && !empty($no_of_labels_next_price)) {
								$product_array[trim($pro_id)][$label_id] = array(
									'label_id'			=> $label_id,
									'labeltext'			=> $labeltext,
									'label_type'		=> $label_type,
									'shape_type'		=> $shape_type,
									'label_position'	=> $label_position,
									'label_image'		=> $label_image,
									'style'				=> $style,
									'textstyle' 		=> $textstyle,
								);
								$product_count = count($product_array[trim($pro_id)]);
							}
							if (empty($no_of_labels_next_price)) {
								$product_array[trim($pro_id)][$label_id] = array(
									'label_id'			=> $label_id,
									'labeltext'			=> $labeltext,
									'label_type'		=> $label_type,
									'shape_type'		=> $shape_type,
									'label_position'	=> $label_position,
									'label_image'		=> $label_image,
									'style'				=> $style,
									'textstyle' 		=> $textstyle,
								);	
							}
						}
					}
				}
			}
		}
		if (!empty($product_array )) {
			$Thumb = aw_shop_next_price_display_label($product_array[$product->get_id()], $productThumb);
		}
		echo wp_kses($Thumb, wp_kses_allowed_html('post'));		
	}
	
	public static function aw_pl_label_next_price_single_product_page() {
		global $product,$wpdb;
		$productThumb 				= '';
		$Thumb 						= '';
		$product_count 				= 0;
		$nextposition 				= 'next_price';
		$product_array 				= array();
		$num_label_same_product 	= array();
		$gen_settings 				= aw_pl_genral_setting();
		$label_distance 			= $gen_settings['label_distance'];
		$label_alignment			= $gen_settings['label_alignment'];
		$no_of_labels_next_price 	= $gen_settings['no_of_labels_next_price'];
		$currency_symbol 			= get_woocommerce_currency_symbol();
		$regular_price 			 	= $product->get_regular_price(); 
		$sale_price     			= $product->get_sale_price();
		$sku 						= $product->get_sku();
		$quantity 					= $product->get_stock_quantity();
		$from_date 					= $product->get_date_on_sale_from();
		$to_date 					= $product->get_date_on_sale_to();
		

		$all_rule = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pl_product_rule AS rule INNER JOIN {$wpdb->prefix}aw_pl_product_label AS label ON rule.label_id = label.id  WHERE rule.rule_status = %d AND label.position = %s ORDER BY rule.priority ASC,rule.rule_last_updated ASC", 1, "{$nextposition}"));

		foreach ($all_rule as $rule => $value) {
			$position 				= '';
			$style 					= '';
			$textstyle 				= '';
			$labeltext 				= '';
			$label_style 			= '';
			$margin 				= '';

			$label_id 				= $value->label_id;
			$priority 				= $value->priority;
			$view_rule			 	= $value->rule_allow_to_user;
			$start_date 			= $value->start_date;
			$end_date 				= $value->end_date;
			$product_id 			= $value->product_id;
			$frontend_label_text 	= $value->frontend_label_text;
			$frontend_medium_text 	= $value->frontend_medium_text;
			$frontend_small_text 	= $value->frontend_small_text;
			$label_name 			= $value->name;
			$label_position 		= $value->position;
			$label_type	 			= $value->type;
			$shape_type				= $value->shape_type;	
			$label_image 			= $value->label_image;	
			$label_size 			= $value->label_size;
			$label_container_css  	= $value->label_container_css;
			$label_text_css  		= $value->label_text_css;
			$product_str 			= $product_id;
			$product_arr 			= explode(',', $product_str);
			$today_date 			= gmdate('Y-m-d');

			if ( $start_date <= $today_date && $today_date <= $end_date ) {
				if ( ( '' == $view_rule || 'anyone' == $view_rule )  || ( 'loggedinuser' == $view_rule && is_user_logged_in() ) ) {

					if (!empty($frontend_label_text)) {
						$labeltext = $frontend_label_text;
						$labeltext = preg_replace('/{price}/', $regular_price, $labeltext);
						$labeltext = preg_replace('/{sku}/', $sku, $labeltext);
						$labeltext = preg_replace('/{special_price}/', $sale_price, $labeltext);
						$labeltext = preg_replace('/{stock}/', $quantity, $labeltext);
						$labeltext = preg_replace('/{br}/', '<br/>', $labeltext);

						if ( !empty($sale_price) ) {
							$amount_saved = $regular_price - $sale_price;
							$percentage = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
							$labeltext = preg_replace('/{save_amount}/', $currency_symbol . $amount_saved, $labeltext);	
							$labeltext = preg_replace('/{save_percent}/', number_format($percentage, 0, '', '') . '%', $labeltext);
						}
						
						if ( ! empty($to_date) ) {
							$datetime1 = new DateTime($from_date);

							$datetime2 = new DateTime($to_date);

							$difference = $datetime1->diff($datetime2);
							$left_days 	= $difference->d;
							$left_hours = $left_days*24;

							$labeltext = preg_replace('/{spdl}/', $left_days, $labeltext);
							$labeltext = preg_replace('/{sphl}/', $left_hours, $labeltext);
						}																
						
						$color =  wc_get_product_terms($product->get_id(), 'pa_color', array( 'fields' => 'names' ) );
						if (!empty($color)) {
							$labeltext = preg_replace('/{attribute: code}/', $color[0], $labeltext);	
						}		
					} 

					if (''!= $label_container_css) {
							$style = $label_container_css;
					}
					if (''!= $label_text_css) {
						$textstyle = $label_text_css;
					}
					
					foreach ($product_arr as $pro_id) {
						if ($product->get_id() == $pro_id) {
							if ($product_count < $no_of_labels_next_price && !empty($no_of_labels_next_price)) {
								$product_array[trim($pro_id)][$label_id] = array(
									'label_id'			=> $label_id,
									'labeltext'			=> $labeltext,
									'label_type'		=> $label_type,
									'shape_type'		=> $shape_type,
									'label_position'	=> $label_position,
									'label_image'		=> $label_image,
									'style'				=> $style,
									'textstyle' 		=> $textstyle,
								);
								$product_count = count($product_array[trim($pro_id)]);
							}
							if (empty($no_of_labels_next_price)) {
								$product_array[trim($pro_id)][$label_id] = array(
									'label_id'			=> $label_id,
									'labeltext'			=> $labeltext,
									'label_type'		=> $label_type,
									'shape_type'		=> $shape_type,
									'label_position'	=> $label_position,
									'label_image'		=> $label_image,
									'style'				=> $style,
									'textstyle' 		=> $textstyle,
								);
							}
						}
					}
				}
			}
		}
		if (!empty($product_array )) {
			$Thumb = aw_product_next_price_display_label($product_array[$product->get_id()], $productThumb);
		}
		echo wp_kses($Thumb, wp_kses_allowed_html('post'));		
	}

	public static function aw_pl_label_cart_next_price( $price_html, $cart_item, $cart_item_key) {
		global $wpdb;
		$productThumb 				= '';
		$Thumb 						= '';
		$product_count 				= 0;
		$nextposition 				= 'next_price';
		$product_array 				= array();
		$num_label_same_product 	= array();
		$product 					= $cart_item['data'];
		$gen_settings 				= aw_pl_genral_setting();
		$label_distance 			= $gen_settings['label_distance'];
		$label_alignment			= $gen_settings['label_alignment'];
		$no_of_labels_next_price 	= $gen_settings['no_of_labels_next_price'];
		

		$all_rule = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pl_product_rule AS rule INNER JOIN {$wpdb->prefix}aw_pl_product_label AS label ON rule.label_id = label.id  WHERE rule.rule_status = %d AND label.position = %s ORDER BY rule.priority ASC,rule.rule_last_updated ASC", 1, "{$nextposition}"));

		foreach ($all_rule as $rule => $value) {
			$position 					= '';
			$style 						= '';
			$textstyle 					= '';
			$small_style 				= '';
			$small_textstyle 			= '';
			$labeltext 					= '';
			$smalllabeltext 			= '';
			$label_style 				= '';
			$margin 					= '';

			$label_id 					= $value->label_id;
			$priority 					= $value->priority;
			$view_rule			 		= $value->rule_allow_to_user;
			$start_date 				= $value->start_date;
			$end_date 					= $value->end_date;
			$product_id 				= $value->product_id;
			$frontend_label_text 		= $value->frontend_label_text;
			$frontend_medium_text 		= $value->frontend_medium_text;
			$frontend_small_text 		= $value->frontend_small_text;
			$label_name 				= $value->name;
			$label_position 			= $value->position;
			$label_type	 				= $value->type;
			$shape_type					= $value->shape_type;	
			$label_image 				= $value->label_image;	
			$label_size 				= $value->label_size;
			$medium_label_container_css  	= $value->medium_label_container_css;
			$medium_label_text_css  		= $value->medium_label_text_css;
			$small_label_container_css  = $value->small_label_container_css;
			$small_label_text_css  		= $value->small_label_text_css;
			$product_str 				= $product_id;
			$product_arr 				= explode(',', $product_str);
			$currency_symbol 			= get_woocommerce_currency_symbol();
			$regular_price  			= $product->get_regular_price(); 
			$sale_price     			= $product->get_sale_price();
			$sku 						= $product->get_sku();
			$quantity 					= $product->get_stock_quantity();
			$from_date 					= $product->get_date_on_sale_from();
			$to_date 					= $product->get_date_on_sale_to();
			$today_date 				= gmdate('Y-m-d');
			
			if ( $start_date <= $today_date && $today_date <= $end_date ) {
				if ( ( '' == $view_rule || 'anyone' == $view_rule )  || ( 'loggedinuser' == $view_rule && is_user_logged_in() ) ) {

					if (!empty($frontend_medium_text)) {
						$labeltext = $frontend_medium_text;							
						$labeltext = preg_replace('/{price}/', $regular_price, $labeltext);
						$labeltext = preg_replace('/{sku}/', $sku, $labeltext);
						$labeltext = preg_replace('/{special_price}/', $sale_price, $labeltext);
						$labeltext = preg_replace('/{stock}/', $quantity, $labeltext);
						$labeltext = preg_replace('/{br}/', '<br/>', $labeltext);

						if ( !empty($sale_price) ) {
							$amount_saved = $regular_price - $sale_price;
							$percentage = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
							$labeltext = preg_replace('/{save_amount}/', $currency_symbol . $amount_saved, $labeltext);	
							$labeltext = preg_replace('/{save_percent}/', number_format($percentage, 0, '', '') . '%', $labeltext);
						}
						
						if ( ! empty($to_date) ) {
							$datetime1 = new DateTime($from_date);

							$datetime2 = new DateTime($to_date);

							$difference = $datetime1->diff($datetime2);
							$left_days 	= $difference->d;
							$left_hours = $left_days*24;

							$labeltext = preg_replace('/{spdl}/', $left_days, $labeltext);
							$labeltext = preg_replace('/{sphl}/', $left_hours, $labeltext);
						}																
						
						$color =  wc_get_product_terms($cart_item['product_id'], 'pa_color', array( 'fields' => 'names' ) );
						if (!empty($color)) {
							$labeltext = preg_replace('/{attribute: code}/', $color[0], $labeltext);	
						}
					} 
					if (!empty($frontend_small_text)) {
						$smalllabeltext = $frontend_small_text;
						$smalllabeltext = preg_replace('/{price}/', $regular_price, $smalllabeltext);
						$smalllabeltext = preg_replace('/{sku}/', $sku, $smalllabeltext);
						$smalllabeltext = preg_replace('/{special_price}/', $sale_price, $smalllabeltext);
						$smalllabeltext = preg_replace('/{stock}/', $quantity, $smalllabeltext);
						$smalllabeltext = preg_replace('/{br}/', '<br/>', $smalllabeltext);

						if ( !empty($sale_price) ) {
							$amount_saved = $regular_price - $sale_price;
							$percentage = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
							$smalllabeltext = preg_replace('/{save_amount}/', $currency_symbol . $amount_saved, $smalllabeltext);	
							$smalllabeltext = preg_replace('/{save_percent}/', number_format($percentage, 0, '', '') . '%', $smalllabeltext);
						}
						
						if ( ! empty($to_date) ) {
							$datetime1 = new DateTime($from_date);

							$datetime2 = new DateTime($to_date);

							$difference = $datetime1->diff($datetime2);
							$left_days 	= $difference->d;
							$left_hours = $left_days*24;

							$smalllabeltext = preg_replace('/{spdl}/', $left_days, $smalllabeltext);
							$smalllabeltext = preg_replace('/{sphl}/', $left_hours, $smalllabeltext);
						}																
						
						$color =  wc_get_product_terms($cart_item['product_id'], 'pa_color', array( 'fields' => 'names' ) );
						if (!empty($color)) {
							$smalllabeltext = preg_replace('/{attribute: code}/', $color[0], $smalllabeltext);	
						}
					}

					if (''!= $medium_label_container_css) {
						$style = $medium_label_container_css;
					}
					if (''!= $medium_label_text_css) {
						$textstyle = $medium_label_text_css;
					}

					if (''!= $small_label_container_css) {
						$small_style = $small_label_container_css;
					}
					
					if (''!= $small_label_text_css) {
						$small_textstyle = $small_label_text_css;
					}
					
					foreach ($product_arr as $pro_id) {
						if ($cart_item['product_id'] == $pro_id) {
							if ($product_count < $no_of_labels_next_price && !empty($no_of_labels_next_price)) {
								$product_array[trim($pro_id)][$label_id] = array(
									'label_id'			=> $label_id,
									'labeltext'			=> $labeltext,
									'smalllabeltext' 	=> $smalllabeltext,
									'label_type'		=> $label_type,
									'shape_type'		=> $shape_type,
									'label_position'	=> $label_position,
									'label_image'		=> $label_image,
									'style'				=> $style,
									'textstyle' 		=> $textstyle,
									'small_style'		=> $small_style,
									'small_textstyle' 	=> $small_textstyle,
								);
								$product_count = count($product_array[trim($pro_id)]);
							}
							if (empty($no_of_labels_next_price)) {
								$product_array[trim($pro_id)][$label_id] = array(
									'label_id'			=> $label_id,
									'labeltext'			=> $labeltext,
									'smalllabeltext' 	=> $smalllabeltext,
									'label_type'		=> $label_type,
									'shape_type'		=> $shape_type,
									'label_position'	=> $label_position,
									'label_image'		=> $label_image,
									'style'				=> $style,
									'textstyle' 		=> $textstyle,
									'small_style'		=> $small_style,
									'small_textstyle' 	=> $small_textstyle,
								);
								$product_count = count($product_array[trim($pro_id)]);
							}
						}
					}
				}
			}
		}
		if (!empty($product_array )) {
			$productThumb = aw_cart_next_price_display_label($product_array[$cart_item['product_id']], $productThumb);
		}
		return $price_html . '<br>' . $productThumb;
	}
}
function aw_shop_next_price_display_label( $product_array, $productThumb) {
	$main_class 		= '';
	$Thumb 				= '';
	$count 				= 0;
	$gen_settings 		= aw_pl_genral_setting();
	$label_distance 	= $gen_settings['label_distance'];
	$label_alignment	= $gen_settings['label_alignment'];

	if ('horizontal' == $label_alignment) {
		$main_class 	= '<div class="aw_product_label_shop_next_price pl-hrz-pst">';
	} else {
		$main_class 	= '<div class="aw_product_label_shop_next_price">';
	}
	foreach ($product_array as $key => $val) {
		$count++;
		$label_id 		= $val['label_id'];
		$labeltext 		= $val['labeltext'];
		//$smalllabeltext = $val['smalllabeltext'];
		$label_type 	= $val['label_type'];
		$shape_type 	= $val['shape_type'];
		$label_position = $val['label_position'];
		$label_image 	= $val['label_image'];
		$style 			= $val['style'];
		$textstyle 		= $val['textstyle'];
		$position 		= '';
		$margin 		= '';

		if ('next_price' == $label_position ) {
			if ($count>1) {
				if ('vertical' == $label_alignment) {
					$margin = 'margin-top: ' . $label_distance . 'px';
				} else {
					$margin = 'margin-left: ' . $label_distance . 'px';
				}	
			}
			$position = 'pl_labelclass_shop_next_price_' . $label_id;
		}
								
		if (!empty($position)) {
			if ('picture' == $label_type) {
				if (''!= $label_image && !empty($label_image)) {
					$badge = '<div class="' . $position . ' overlay-lbl" style= "' . $margin . '"><div class="img-label-wrap"><img src="' . $label_image . '" width="50px" height="50px" ><span style = "' . $textstyle . '" >' . $labeltext . '</span></div></div>';
					$productThumb .= $badge;
				}
				
			} else if ('shape' == $label_type) {
				if (''!= $shape_type && !empty($shape_type)) {
					$badge = '<div class="' . $position . '" style= "' . $margin . '"><div class= "' . $shape_type . '" style = "' . $style . '" ><span style = "' . $textstyle . '" >' . $labeltext . '</span></div></div>';
					$productThumb .= $badge;
				}
			} else if ('text' == $label_type) { 
				$badge = '<div class="' . $position . '" style= "' . $margin . '"><div class="labeltext" style="' . $style . $textstyle . '">' . $labeltext . '</div></div>';
				$productThumb .= $badge;
			}
		}
	}
	$Thumb = $main_class . $productThumb . '</div>';
	return $Thumb;
}
function aw_product_next_price_display_label( $product_array, $productThumb) {
	$main_class 		= '';
	$Thumb 				= '';
	$count 				= 0;
	$gen_settings 		= aw_pl_genral_setting();
	$label_distance 	= $gen_settings['label_distance'];
	$label_alignment	= $gen_settings['label_alignment'];

	if ('horizontal' == $label_alignment) {
		$main_class 	= '<div class="aw_product_label_next_price pl-hrz-pst">';
	} else {
		$main_class 	= '<div class="aw_product_label_next_price">';
	}
	foreach ($product_array as $key => $val) {
		$count++;
		$label_id 		= $val['label_id'];
		$labeltext 		= $val['labeltext'];
		//$smalllabeltext = $val['smalllabeltext'];
		$label_type 	= $val['label_type'];
		$shape_type 	= $val['shape_type'];
		$label_position = $val['label_position'];
		$label_image 	= $val['label_image'];
		$style 			= $val['style'];
		$textstyle 		= $val['textstyle'];
		$position 		= '';
		$margin 		= '';

		if ('next_price' == $label_position ) {
			if ($count>1) {
				if ('vertical' == $label_alignment) {
					$margin = 'margin-top: ' . $label_distance . 'px';
				} else {
					$margin = 'margin-left: ' . $label_distance . 'px';
				}
			}
			$position = 'pl_labelclass_next_price_' . $label_id;
		}

		if (!empty($position)) {
			if ('picture' == $label_type) {
				if (''!= $label_image && !empty($label_image)) {
					$badge = '<div class="' . $position . ' overlay-lbl" style= "' . $margin . '"><div class="img-label-wrap"><img src="' . $label_image . '" width="50px" height="50px" style= "' . $style . '"><span style = "' . $textstyle . '" >' . $labeltext . '</span></div></div>';
					$productThumb .= $badge;
				}
				
			} else if ('shape' == $label_type) {
				if (''!= $shape_type && !empty($shape_type)) {
					$badge = '<div class="' . $position . '" style= "' . $margin . '"><div class= "' . $shape_type . '" style = "' . $style . '" ><span style = "' . $textstyle . '" >' . $labeltext . '</span></div></div>';
					$productThumb .= $badge;
				}
			} else if ('text' == $label_type) { 
				$badge = '<div class="' . $position . '" style= "' . $margin . '"><div class="labeltext" style="' . $style . $textstyle . '">' . $labeltext . '</div></div>';
				$productThumb .= $badge;
			}
		}
	}
	$Thumb = $main_class . $productThumb . '</div>';
	return $Thumb;
}
function aw_cart_next_price_display_label( $product_array, $productThumb) {
	$main_class 		= '';
	$Thumb 				= '';
	$count 				= 0;
	$gen_settings 		= aw_pl_genral_setting();
	$label_distance 	= $gen_settings['label_distance'];
	$label_alignment	= $gen_settings['label_alignment'];
	$small_textstyle 	= '';
	$small_style 		= '';

	if ('horizontal' == $label_alignment) {
		$main_class 	= '<div class="aw_product_label_next_price pl-hrz-pst">';
	} else {
		$main_class 	= '<div class="aw_product_label_next_price">';
	}

	foreach ($product_array as $key => $val) {
		$count++;
		$label_id 			= $val['label_id'];
		$labeltext 			= $val['labeltext'];
		$smalllabeltext 	= $val['smalllabeltext'];
		$label_type 		= $val['label_type'];
		$shape_type 		= $val['shape_type'];
		$label_position 	= $val['label_position'];
		$label_image 		= $val['label_image'];
		$style 				= $val['style'];
		$textstyle 			= $val['textstyle'];
		$small_style 		= $val['small_style'];
		$small_textstyle 	= $val['small_textstyle'];
		$position 			= '';
		$margin 			= '';
		$small_design 		= ''; 
		$small_textdesign 	= '';

		if ('next_price' == $label_position ) {
			if ($count>1) {
				if ('vertical' == $label_alignment) {
					$margin = 'margin-top: ' . $label_distance . 'px';
				} else {
					$margin = 'margin-left: ' . $label_distance . 'px';
				}
			}
			$position = 'pl_labelclass_next_price_' . $label_id;
		}
		if (''!= $small_style && !empty($small_style)) {
			$arr_label = explode(' ', $small_style);
			$final_str_label = '';
			foreach ($arr_label as $index => $string_label) {
				$subrr_label = explode(':', $string_label);
				$first_str_label = '"' . $subrr_label[0] . '":';
				$sec_str_label  = '"' . substr($subrr_label[1], 0, -1) . '" ';
				$final_str_label = $final_str_label . $first_str_label . $sec_str_label;
			}
			$small_design = preg_replace('/[ ]+/', ',', trim($final_str_label)); 
		} 

		if (''!= $small_textstyle && !empty($small_textstyle)) {
			$arr_text = explode(' ', $small_textstyle);
			$final_str_text = '';
			foreach ($arr_text as $index => $string_text) {
				$subrr_text = explode(':', $string_text);
				$first_str_text = '"' . $subrr_text[0] . '":';
				$sec_str_text  = '"' . substr($subrr_text[1], 0, -1) . '" ';
				$final_str_text = $final_str_text . $first_str_text . $sec_str_text;
			}
			$small_textdesign = preg_replace('/[ ]+/', ',', trim($final_str_text)); 
		}
		if (!empty($position)) {
			if ('picture' == $label_type) {
				if (''!= $label_image && !empty($label_image)) {
					$badge = '<div class="' . $position . ' overlay-lbl" style= "' . $margin . '"><div class="img-label-wrap"><img src="' . $label_image . '" width="50px" height="50px" style= "' . $style . '"><span class="nextl1" style = "' . $textstyle . '" >' . $labeltext . '</span></div></div>';
					$productThumb .= $badge;
					?>
					<script>
					jQuery(window).load(function() {
						var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
						var label_pos 	= '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
						var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
						var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';

						if (jQuery('.product_list_widget ')[0]) {   
							jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' .nextl1').text(smalllabeltext);

							jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' .nextl1').removeAttr("style");
							jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' .nextl1').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});   
						} 
					});
					jQuery( document ).ajaxComplete(function( event, xhr, settings ) {
						var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
						var label_pos = '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
						var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
						var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';
						if (jQuery('.product_list_widget ')[0]) {  
							jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' .nextl1').text(smalllabeltext);
							
							jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' .nextl1').removeAttr("style");
							jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' .nextl1').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});   
						} 
					});
					</script>
					<?php
				}
				
			} else if ('shape' == $label_type) {
				if (''!= $shape_type && !empty($shape_type)) {
					$badge = '<div class="' . $position . '" style= "' . $margin . '"><div class= "' . $shape_type . '" style = "' . $style . '" ><span class= "nextl2" style = "' . $textstyle . '" >' . $labeltext . '</span></div></div>';
					$productThumb .= $badge;
					?>
					<script>
					jQuery(window).load(function() {
						var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
						var label_pos 	= '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
						var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
						var shape = '<?php echo wp_kses($shape_type, wp_kses_allowed_html('post')); ?>';
						var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';

						if (jQuery('.product_list_widget ')[0]) {
							jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' .'+shape+' .nextl2').text(smalllabeltext);

							jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' .'+shape+' .nextl2').removeAttr("style");
							jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' .'+shape+' .nextl2').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>}); 

							jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' .'+shape).removeAttr("style");
							jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' .'+shape).css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});
						} 
					});
					jQuery( document ).ajaxComplete(function( event, xhr, settings ) {
						var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
						var label_pos 	= '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
						var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
						var shape = '<?php echo wp_kses($shape_type, wp_kses_allowed_html('post')); ?>';
						var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';

						if (jQuery('.product_list_widget ')[0]) {
							jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' .'+shape+' .nextl2').text(smalllabeltext);

							jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' .'+shape+' .nextl2').removeAttr("style");
							jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' .'+shape+' .nextl2').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>}); 

							jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' .'+shape).removeAttr("style");
							jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' .'+shape).css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});
						} 
					});
					</script>
					<?php
				}
			} else if ('text' == $label_type) { 
				$badge = '<div class="' . $position . '" style= "' . $margin . '"><div class="labeltext nextl3" style="' . $style . $textstyle . '">' . $labeltext . '</div></div>';
				$productThumb .= $badge;
				?>
				<script>
				jQuery(window).load(function() {
					var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
					var label_pos 	= '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
					var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
					var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';

					if (jQuery('.product_list_widget ')[0]) {	
						jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' .nextl3').text(smalllabeltext); 

						jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' .nextl3').removeAttr("style");
						jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' .nextl3').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>}); 

						jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' div').removeAttr("style");
						jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' div').css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});  
					} 
				});
				jQuery( document ).ajaxComplete(function( event, xhr, settings ) {
					var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
					var label_pos = '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
					var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
					var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';
					if (jQuery('.product_list_widget ')[0]) {	
						jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' .nextl3').text(smalllabeltext); 

						jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' .nextl3').removeAttr("style");
						jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' .nextl3').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>}); 

						jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' div').removeAttr("style");
						jQuery('.product_list_widget  li .quantity .aw_product_label_next_price .' +pos+' div').css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});     
					} 
				});
				</script>
				<?php
			}
		}
	}
	$Thumb = $main_class . $productThumb . '</div>';
	return $Thumb;
}
function aw_display_label( $product_array, $productThumb, $same_value) {
	$checkarr 			= array();
	$gen_settings 		= aw_pl_genral_setting();
	$label_distance 	= $gen_settings['label_distance'];
	$label_alignment	= $gen_settings['label_alignment'];
	$count 				= 0;
	$cul 		= 0;
	$cur 		= 0;
	$cll 		= 0;
	$clr 		= 0;
	$Thumbul 	= '';
	$Thumbur 	= '';
	$Thumbll 	= '';
	$Thumblr 	= '';
	$ulcount 	= '' ;
	$urcount 	= '' ;
	$llcount 	= '' ;
	$lrcount 	= '' ;
	$small_textstyle 	= '';
	foreach ($same_value as $key => $value) {
		if ('upper_left' == $key) {
			$ulcount = $value;
		}
		if ('upper_right' == $key) {
			$urcount = $value;
		}
		if ('lower_left' == $key) {
			$llcount = $value;
		}
		if ('lower_right' == $key) {
			$lrcount = $value;
		}
	}

	if ('horizontal' == $label_alignment) {
		$upper_left 		= '<div class = "pl_upper_left pl-hrz-pst">';
		$upper_right 		= '<div class = "pl_upper_right pl-hrz-pst">';
		$lower_left 		= '<div class = "pl_lower_left pl-hrz-pst">';
		$lower_right 		= '<div class = "pl_lower_right pl-hrz-pst">';
	} else {
		$upper_left 		= '<div class = "pl_upper_left">';
		$upper_right 		= '<div class = "pl_upper_right">';
		$lower_left 		= '<div class = "pl_lower_left">';
		$lower_right 		= '<div class = "pl_lower_right">';
	}
	foreach ($product_array as $key => $val) {
		$count++;
		$label_id 			= $val['label_id'];
		$labeltext 			= $val['labeltext'];
		$smalllabeltext 	= $val['smalllabeltext'];
		$label_type 		= $val['label_type'];
		$shape_type 		= $val['shape_type'];
		$label_position 	= $val['label_position'];
		$label_image 		= $val['label_image'];
		$style 				= $val['style'];
		$textstyle 			= $val['textstyle'];
		$small_style 		= $val['small_style'];
		$small_textstyle 	= $val['small_textstyle'];
		$position 			= '';
		$margin 			= '';
		$labelarr 			= $product_array[$label_id];
		$small_design 		= ''; 
		$small_textdesign 	= '';

		if (in_array($labelarr['label_position'], $checkarr)) {
			if ('upper_left' == $label_position ) {
				if ($count>1) {
					if ('vertical' == $label_alignment) {
						$margin = 'margin-top: ' . $label_distance . 'px';
					} else {
						$margin = 'margin-left: ' . $label_distance . 'px';
					}
				}
				$position = 'pl_labelclass_upper_left_' . $label_id;
			} else if ('upper_right' == $label_position ) {
				if ($count>1) {
					if ('vertical' == $label_alignment) {
						$margin = 'margin-top: ' . $label_distance . 'px';
					} else {
						$margin = 'margin-right: ' . $label_distance . 'px';
					}
				}
				$position = 'pl_labelclass_upper_right_' . $label_id;
			} else if ('lower_right' == $label_position ) {
				if ($count>1) {
					if ('vertical' == $label_alignment) {
						$margin = 'margin-bottom: ' . $label_distance . 'px';
					} else {
						$margin = 'margin-right: ' . $label_distance . 'px';
					}
				}
				$position = 'pl_labelclass_lower_right_' . $label_id;
			} else if ('lower_left' == $label_position ) {
				if ($count>1) {
					if ('vertical' == $label_alignment) {
						$margin = 'margin-bottom: ' . $label_distance . 'px';
					} else {
						$margin = 'margin-left: ' . $label_distance . 'px';
					}
				}
				$position = 'pl_labelclass_lower_left_' . $label_id;
			}
		} else {
			$checkarr[] = $labelarr['label_position'];
			if ('upper_left' == $label_position ) {
				$position = 'pl_labelclass_upper_left_' . $label_id;
			} else if ('upper_right' == $label_position ) {
				$position = 'pl_labelclass_upper_right_' . $label_id;
			} else if ('lower_right' == $label_position ) {
				$position = 'pl_labelclass_lower_right_' . $label_id;
			} else if ('lower_left' == $label_position ) {
				$position = 'pl_labelclass_lower_left_' . $label_id;
			}
		}

		if (''!= $small_style && !empty($small_style)) {
			$arr_label = explode(' ', $small_style);
			$final_str_label = '';
			foreach ($arr_label as $index => $string_label) {
				$subrr_label = explode(':', $string_label);
				$first_str_label = '"' . $subrr_label[0] . '":';
				$sec_str_label  = '"' . substr($subrr_label[1], 0, -1) . '" ';
				$final_str_label = $final_str_label . $first_str_label . $sec_str_label;
			}
			$small_design = preg_replace('/[ ]+/', ',', trim($final_str_label)); 
		} 

		if (''!= $small_textstyle && !empty($small_textstyle)) {
			$arr_text = explode(' ', $small_textstyle);
			$final_str_text = '';
			foreach ($arr_text as $index => $string_text) {
				$subrr_text = explode(':', $string_text);
				$first_str_text = '"' . $subrr_text[0] . '":';
				$sec_str_text  = '"' . substr($subrr_text[1], 0, -1) . '" ';
				$final_str_text = $final_str_text . $first_str_text . $sec_str_text;
			}
			$small_textdesign = preg_replace('/[ ]+/', ',', trim($final_str_text)); 
		}
		if (!empty($position)) {
			if ('upper_left' == $label_position) {					
				if ('picture' == $label_type) {
					if (''!= $label_image && !empty($label_image)) {
						$cul++;
						if (1==$cul) {
							$Thumbul.= $upper_left . $Thumbul;
						}
						$badge = '<div class="' . $position . ' overlay-lbl " style= "' . $margin . '"><div class="img-label-wrap"><img src="' . $label_image . '" width="50px" height="50px" style= "' . $style . '"><span class="l1" style = "' . $textstyle . '" >' . $labeltext . '</span></div></div>';
						$Thumbul.= $badge;
						?>
						<script>
						jQuery(window).load(function() {
							var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
							var label_pos 	= '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
							var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
							var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';

							if (jQuery('.product_list_widget ')[0]) {								    
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l1').text(smalllabeltext);

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l1').removeAttr("style");
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l1').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' img').removeAttr("style"); 
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' img').css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});  
							} 
						});
						jQuery( document ).ajaxComplete(function( event, xhr, settings ) {
							var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
							var label_pos = '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
							var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
							var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';
							if (jQuery('.product_list_widget ')[0]) {								    
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l1').text(smalllabeltext); 

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l1').removeAttr("style");
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l1').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' img').removeAttr("style"); 
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' img').css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});  
							} 
						});
						</script>
						<?php
					}
					
				} else if ('shape' == $label_type) {						
					if (''!= $shape_type && !empty($shape_type)) {
						$cul++;
						if (1==$cul) {
							$Thumbul.= $upper_left . $Thumbul;
						}
						$badge = '<div class="' . $position . '" style= "' . $margin . '"><div class= "' . $shape_type . '" style = "' . $style . '" ><span class="l2" style = "' . $textstyle . '" >' . $labeltext . '</span></div></div>';
						$Thumbul.= $badge;
						?>
						<script>
						jQuery(window).load(function() {
							var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
							var label_pos 	= '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
							var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
							var shape = '<?php echo wp_kses($shape_type, wp_kses_allowed_html('post')); ?>';
							var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';

							if (jQuery('.product_list_widget ')[0]) {								    
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l2').text(smalllabeltext);

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l2').removeAttr("style"); 
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l2').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape).removeAttr("style"); 
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape).css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});        
							} 
						});
						jQuery( document ).ajaxComplete(function( event, xhr, settings ) {
							var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
							var label_pos = '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
							var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
							var shape = '<?php echo wp_kses($shape_type, wp_kses_allowed_html('post')); ?>';
							var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';
							if (jQuery('.product_list_widget ')[0]) {								    
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l2').text(smalllabeltext); 

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l2').removeAttr("style"); 
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l2').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape).removeAttr("style"); 
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape).css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});   
							} 
						});
						</script>
						<?php
					}

				} else if ('text' == $label_type) {
					$cul++;
					if (1==$cul) {
							$Thumbul.= $upper_left . $Thumbul;
					}
					
					if (strpos($textstyle, 'color') !== false) {
						$ex = 'no';
					} else {
						$textstyle = 'color:black;' . $textstyle;
					}
					$badge = '<div class="' . $position . '" style= "' . $margin . '"><div class= "' . $shape_type . '" style = "' . $style . '" ><span class="l3" style = "' . $textstyle . '" >' . $labeltext . '</span></div></div>';
					$Thumbul.= $badge;
					?>
					<script>
					jQuery(window).load(function() {
						var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
						var label_pos 	= '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
						var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
						var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';

						if (jQuery('.product_list_widget ')[0]) {								    
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l3').text(smalllabeltext);   

							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l3').removeAttr("style");   
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l3').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>}); 

							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div').removeAttr("style"); 
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div').css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});  
						} 
					});
					jQuery( document ).ajaxComplete(function( event, xhr, settings ) {
						var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
						var label_pos = '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
						var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
						var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';
						if (jQuery('.product_list_widget ')[0]) {								    
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l3').text(smalllabeltext);    

							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l3').removeAttr("style");   
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l3').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>}); 

							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div').removeAttr("style"); 
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div').css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});   
						} 
					});
					</script>
					<?php
				}					
				if ($cul == $ulcount) {
					$Thumbul.= '</div>';
				}
			}
			if ('upper_right' == $label_position) {
				if ('picture' == $label_type) {
					if (''!= $label_image && !empty($label_image)) {
						$cur++;
						if (1==$cur) {
							$Thumbur.= $upper_right . $Thumbur;
						}
						$badge = '<div class="' . $position . ' overlay-lbl " style= "' . $margin . '"><div class="img-label-wrap"><img src="' . $label_image . '" width="50px" height="50px" style= "' . $style . '"><span class="l4" style = "' . $textstyle . '" >' . $labeltext . '</span></div></div>';
						$Thumbur.= $badge;
						?>
						<script>
						jQuery(window).load(function() {
							var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
							var label_pos 	= '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
							var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
							var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';

							if (jQuery('.product_list_widget ')[0]) {								    
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l4').text(smalllabeltext); 

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l4').removeAttr("style");
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l4').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' img').removeAttr("style"); 
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' img').css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});   
							} 
						});
						jQuery( document ).ajaxComplete(function( event, xhr, settings ) {
							var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
							var label_pos = '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
							var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
							var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';
							if (jQuery('.product_list_widget ')[0]) {								    
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l4').text(smalllabeltext);

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l4').removeAttr("style");
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l4').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' img').removeAttr("style"); 
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' img').css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});    
							} 
						});
						</script>
						<?php
					}
					
				} else if ('shape' == $label_type) {						
					if (''!= $shape_type && !empty($shape_type)) {
						$cur++;
						if (1==$cur) {
							$Thumbur.= $upper_right . $Thumbur;
						}
						if ('rectangle_belevel_up' == $shape_type) {
							$shape_type = $shape_type . ' up_right';
						}
						if ('rectangle_belevel_down' == $shape_type) {
							$shape_type = $shape_type . ' down_right';
						}

						$badge = '<div class="' . $position . '" style= "' . $margin . '"><div class= "' . $shape_type . '" style = "' . $style . '" ><span class="l5" style = "' . $textstyle . '" >' . $labeltext . '</span></div></div>';
						$Thumbur.= $badge;
						?>
						<script>
						jQuery(window).load(function() {
							var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
							var label_pos 	= '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
							var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
							var shape = '<?php echo wp_kses($shape_type, wp_kses_allowed_html('post')); ?>';
							var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';
							console.log(smalllabeltext);
							if (jQuery('.product_list_widget ')[0]) {								    
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l5').text(smalllabeltext);

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l5').removeAttr("style");
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l5').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape).removeAttr("style"); 
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape).css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});     
							} 
						});
						jQuery( document ).ajaxComplete(function( event, xhr, settings ) {
							var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
							var label_pos = '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
							var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
							var shape = '<?php echo wp_kses($shape_type, wp_kses_allowed_html('post')); ?>';
							var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';
							if (jQuery('.product_list_widget ')[0]) {								    
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l5').text(smalllabeltext); 

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l5').removeAttr("style");
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l5').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape).removeAttr("style"); 
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape).css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});    
							} 
						});
						</script>
						<?php
					}

				} else if ('text' == $label_type) {
					$cur++;
					if (1==$cur) {
						$Thumbur.= $upper_right . $Thumbur;
					}
					$badge = '<div class="' . $position . '" style= "' . $margin . '"><div class= "' . $shape_type . '" style = "' . $style . '" ><span class="l6" style = "' . $textstyle . '" >' . $labeltext . '</span></div></div>';
					$Thumbur.= $badge;
					?>
					<script>
					jQuery(window).load(function() {
						var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
						var label_pos 	= '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
						var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
						var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';

						if (jQuery('.product_list_widget ')[0]) {								    
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l6').text(smalllabeltext); 

							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l6').removeAttr("style");
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l6').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});

							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div').removeAttr("style"); 
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div').css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});    
						} 
					});
					jQuery( document ).ajaxComplete(function( event, xhr, settings ) {
						var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
						var label_pos = '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
						var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
						var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';
						if (jQuery('.product_list_widget ')[0]) {								    
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l6').text(smalllabeltext);

							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l6').removeAttr("style");
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l6').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});

							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div').removeAttr("style"); 
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div').css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});    
						} 
					});
					</script>
					<?php
				}
				if ($cur == $urcount) {
					$Thumbur.= '</div>';
				}
			}
			if ('lower_left' == $label_position) {
				if ('picture' == $label_type) {
					if (''!= $label_image && !empty($label_image)) {
						$cll++;
						if (1==$cll) {
							$Thumbll.= $lower_left . $Thumbll;
						}
						$badge = '<div class="' . $position . ' overlay-lbl " style= "' . $margin . '"><div class="img-label-wrap"><img src="' . $label_image . '" width="50px" height="50px" style= "' . $style . '"><span class="l7" style = "' . $textstyle . '" >' . $labeltext . '</span></div></div>';
						$Thumbll.= $badge;
						?>
						<script>
						jQuery(window).load(function() {
							var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
							var label_pos 	= '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
							var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
							var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';

							if (jQuery('.product_list_widget ')[0]) {								    
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l7').text(smalllabeltext); 

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l7').removeAttr("style");
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l7').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' img').removeAttr("style"); 
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' img').css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});  
							} 
						});
						jQuery( document ).ajaxComplete(function( event, xhr, settings ) {
							var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
							var label_pos = '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
							var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
							var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';
							if (jQuery('.product_list_widget ')[0]) {								    
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l7').text(smalllabeltext);

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l7').removeAttr("style");
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l7').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' img').removeAttr("style"); 
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' img').css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});    
							} 
						});
						</script>
						<?php
					}
					
				} else if ('shape' == $label_type) {						
					if (''!= $shape_type && !empty($shape_type)) {
						$cll++;
						if (1==$cll) {
							$Thumbll.= $lower_left . $Thumbll;
						}
						$badge = '<div class="' . $position . '" style= "' . $margin . '"><div class= "' . $shape_type . '" style = "' . $style . '" ><span class="l8" style = "' . $textstyle . '" >' . $labeltext . '</span></div></div>';
						$Thumbll.= $badge;
						?>
						<script>
						jQuery(window).load(function() {
							var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
							var label_pos 	= '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
							var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
							var shape = '<?php echo wp_kses($shape_type, wp_kses_allowed_html('post')); ?>';
							var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';

							if (jQuery('.product_list_widget ')[0]) {								    
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l8').text(smalllabeltext);

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l8').removeAttr("style"); 
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l8').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape).removeAttr("style"); 
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape).css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});     
							} 
						});
						jQuery( document ).ajaxComplete(function( event, xhr, settings ) {
							var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
							var label_pos = '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
							var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
							var shape = '<?php echo wp_kses($shape_type, wp_kses_allowed_html('post')); ?>';
							var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';
							if (jQuery('.product_list_widget ')[0]) {								    
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l8').text(smalllabeltext);  

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l8').removeAttr("style"); 
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l8').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape).removeAttr("style"); 
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape).css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});   
							} 
						});
						</script>
						<?php
					}

				} else if ('text' == $label_type) {
					$cll++;
					if (1==$cll) {
						$Thumbll.= $lower_left . $Thumbll;
					}
					$badge = '<div class="' . $position . '" style= "' . $margin . '"><div class= "' . $shape_type . '" style = "' . $style . '" ><span class="l9" style = "' . $textstyle . '" >' . $labeltext . '</span></div></div>';
					$Thumbll.= $badge;
					?>
					<script>
					jQuery(window).load(function() {
						var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
						var label_pos 	= '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
						var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
						var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';

						if (jQuery('.product_list_widget ')[0]) {								    
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l9').text(smalllabeltext);

							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l9').removeAttr("style");
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l9').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});

							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div').removeAttr("style"); 
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div').css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});    
						} 
					});
					jQuery( document ).ajaxComplete(function( event, xhr, settings ) {
						var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
						var label_pos = '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
						var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
						var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';
						if (jQuery('.product_list_widget ')[0]) {								    
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l9').text(smalllabeltext);

							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l9').removeAttr("style");
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l9').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});

							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div').removeAttr("style"); 
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div').css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});     
						} 
					});
					</script>
					<?php
				}
				if ($cll == $llcount) {
					$Thumbll.= '</div>';
				}
			}
			if ('lower_right' == $label_position) {
				if ('picture' == $label_type) {
					if (''!= $label_image && !empty($label_image)) {
						$clr++;
						if (1==$clr) {
							$Thumblr.= $lower_right . $Thumblr;
						}
						$badge = '<div class="' . $position . ' overlay-lbl " style= "' . $margin . '"><div class="img-label-wrap"><img src="' . $label_image . '" width="50px" height="50px" style= "' . $style . '"><span class="l10" style = "' . $textstyle . '" >' . $labeltext . '</span></div></div>';
						$Thumblr.= $badge;
						?>
						<script>
						jQuery(window).load(function() {
							var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
							var label_pos 	= '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
							var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
							var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';

							if (jQuery('.product_list_widget ')[0]) {								    
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l10').text(smalllabeltext); 

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l10').removeAttr("style");
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l10').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' img').removeAttr("style"); 
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' img').css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});     
							} 
						});
						jQuery( document ).ajaxComplete(function( event, xhr, settings ) {
							var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
							var label_pos = '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
							var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
							var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';
							if (jQuery('.product_list_widget ')[0]) {								    
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l10').text(smalllabeltext);

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l10').removeAttr("style");
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .l10').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' img').removeAttr("style"); 
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' img').css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});     
							} 
						});
						</script>
						<?php
					}
					
				} else if ('shape' == $label_type) {						
					if (''!= $shape_type && !empty($shape_type)) {
						$clr++;
						if (1==$clr) {
							$Thumblr.= $lower_right . $Thumblr;
						}
						if ('rectangle_belevel_up' == $shape_type) {
							$shape_type = $shape_type . ' up_right';
						}
						if ('rectangle_belevel_down' == $shape_type) {
							$shape_type = $shape_type . ' down_right';
						}
						
						$badge = '<div class="' . $position . '" style= "' . $margin . '"><div class= "' . $shape_type . '" style = "' . $style . '" ><span class="l11" style = "' . $textstyle . '" >' . $labeltext . '</span></div></div>';
						$Thumblr.= $badge;
						?>
						<script>
						jQuery(window).load(function() {
							var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
							var label_pos 	= '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
							var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
							var shape = '<?php echo wp_kses($shape_type, wp_kses_allowed_html('post')); ?>';
							var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';

							if (jQuery('.product_list_widget ')[0]) {								    
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l11').text(smalllabeltext);

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l11').removeAttr("style"); 
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l11').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape).removeAttr("style"); 
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape).css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});     
							} 
						});
						jQuery( document ).ajaxComplete(function( event, xhr, settings ) {
							var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
							var label_pos = '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
							var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
							var shape = '<?php echo wp_kses($shape_type, wp_kses_allowed_html('post')); ?>';
							var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';
							if (jQuery('.product_list_widget ')[0]) {								    
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l11').text(smalllabeltext);

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l11').removeAttr("style"); 
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape+' .l11').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});

								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape).removeAttr("style"); 
								jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' .'+shape).css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});     
							} 
						});
						</script>
						<?php
					}

				} else if ('text' == $label_type) {
					$clr++;
					if (1==$clr) {
						$Thumblr.= $lower_right . $Thumblr;
					}
					$badge = '<div class="' . $position . '" style= "' . $margin . '"><div class= "' . $shape_type . '" style = "' . $style . '" ><span class="l12" style = "' . $textstyle . '" >' . $labeltext . '</span></div></div>';
					$Thumblr.= $badge;
					?>
					<script>
					jQuery(window).load(function() {
						var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
						var label_pos 	= '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
						var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
						var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';

						if (jQuery('.product_list_widget ')[0]) {								    
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l12').text(smalllabeltext); 

							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l12').removeAttr("style"); 
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l12').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});

							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div').removeAttr("style"); 
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div').css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});    
						} 
					});
					jQuery( document ).ajaxComplete(function( event, xhr, settings ) {
						var id = jQuery('.remove_from_cart_button ').attr('data-product_id');
						var label_pos = '<?php echo wp_kses($label_position, wp_kses_allowed_html('post')); ?>';
						var pos = '<?php echo wp_kses($position, wp_kses_allowed_html('post')); ?>';
						var smalllabeltext = '<?php echo wp_kses($smalllabeltext, wp_kses_allowed_html('post')); ?>';
						if (jQuery('.product_list_widget ')[0]) {								    
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l12').text(smalllabeltext); 

							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l12').removeAttr("style"); 
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div .l12').css({<?php echo wp_kses($small_textdesign, wp_kses_allowed_html('post')); ?>});

							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div').removeAttr("style"); 
							jQuery('.product_list_widget  li  a .aw_product_label .pl_'+label_pos+' .' +pos+' div').css({<?php echo wp_kses($small_design, wp_kses_allowed_html('post')); ?>});    
						} 
					});
					</script>
					<?php
				}
				if ($clr == $lrcount) {
					$Thumblr.= '</div>';
				}
			}
		}
	}	
	return $Thumbul . $Thumbur . $Thumbll . $Thumblr . $productThumb;
}

function aw_pl_all_rule() {
	global $wpdb;
	$position = 'next_price';

	$allrule = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pl_product_rule AS rule INNER JOIN {$wpdb->prefix}aw_pl_product_label AS label ON rule.label_id = label.id  WHERE rule.rule_status = %d AND label.position != %s ORDER BY rule.priority ASC,rule.rule_last_updated ASC", 1, "{$position}"));	
	
	return $allrule;
}

function aw_pl_genral_setting() {
	$genral_setting = array();
	$label_distance = '';
	$label_alignment = '';
	$no_of_labels_single_product = '';
	$no_of_labels_next_price = '';

	if (get_option('aw_pl_setting_label_distance')) {
		$label_distance = get_option('aw_pl_setting_label_distance');
	}
	if (get_option('aw_pl_label_select')) {
		$label_alignment = get_option('aw_pl_label_select');
	}

	if (get_option('number_of_labels_over_product_image')) {
		$no_of_labels_single_product = get_option('number_of_labels_over_product_image');
	}
	if (get_option('number_of_labels_next_to_price')) {
		$no_of_labels_next_price = get_option('number_of_labels_next_to_price');
	}

	$genral_setting = array(
						'label_distance'				=> $label_distance,
						'label_alignment'				=> $label_alignment,
						'no_of_labels_single_product'	=> $no_of_labels_single_product,
						'no_of_labels_next_price'		=> $no_of_labels_next_price
					);

	return $genral_setting;
}

