<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class AwPqFaqArticleAdmin {
	public static function aw_faq_article_activate() {
		// start global session for saving the referer url
		if (empty(session_id())) {
			session_start();
		}
		/** Create and Register Post Type article **/		
		$labels = array(
			'name'               => esc_html('Articles', 'your-plugin-textdomain' ),
			'singular_name'      => esc_html('Articles', 'your-plugin-textdomain'),
			'add_new'            => esc_html('Create New Article', 'pq_faq_plugin'),
			'add_new_item'       => esc_html('Add New Article', 'pq_faq_plugin'),
			'edit_item'          => esc_html('Edit Article', 'pq_faq_plugin'),
			'new_item'           => esc_html('New Article', 'pq_faq_plugin'),
			'all_items'          => esc_html('Articles', 'pq_faq_plugin'),
			'search_items'       => esc_html('Search article', 'pq_faq_plugin'),
			'not_found'          => esc_html('No article Found', 'pq_faq_plugin'),
			'not_found_in_trash' => esc_html('No article Found in Trash', 'pq_faq_plugin'),
			'parent_item_colon'  => '',
			'menu_name'          => esc_html('FAQ Articles', 'admin menu', 'pq_faq_plugin'),
			);

		$supports = array( 'title', 'editor');
		$args = array(
			'labels'        	=> $labels,
			'description'   	=> __( 'Description.', 'pq_faq_plugin' ),
			'capability_type' 	=> 'post',
			'public'        	=> true,
			'menu_position' 	=> null,
			'show_in_menu' 		=> 'edit.php?post_type=faq_article',
			'show_ui'           => true,
			'supports'      	=> $supports,
			'has_archive'   	=> false,
			'hierarchical'		=> false,
			'rewrite'			=> array('slug' => 'faq_article'),
			'publicly_queryable'=> true,
		);
		$post_type_exists = post_type_exists('faq_article');

		if (!$post_type_exists) {
			register_post_type('faq_article', $args );
			 $label = array(
			'name' => _x( 'FAQ Categories', 'taxonomy general name' ),
			'singular_name' => _x( 'FAQ Categories', 'taxonomy singular name' ),
		  ); 
			register_taxonomy('faq_cat', array('faq_article'), array(
			  'hierarchical' 		=> true,
			  'labels' 				=> $label,
			  'show_ui' 			=> false,
			  'show_admin_column' 	=> true,
			  'query_var' 			=> true,
			  'rewrite' 			=> array( 'slug' => 'faq_cat' ),
			));
		}
		
	}
	public static function aw_faq_article_table_head() {
		$columns = array(
		'cb' 					=> '&lt;input type="checkbox" />',
		'sno'					=> __('ID'),
		'title' 				=> __('Article Name'),
		//'name' 					=> __('Article Name'),
		'post_status'   		=> __('Status'),
		'faq_art_sort_order'   	=> __('Sort Order'),
		'faq_num_helpful_votes' => __('Number of helpful votes'),
		'faq_num_total_votes'   => __('Number of total votes'),
		'faq_helpful_rate'		=> __('Helpfulness Rate'),
		'post_modified' 		=> __('Published Date')
		);
		return $columns;

	}

	public static function aw_faq_article_table_columns( $column, $post_id) {
				/** Table data head and values **/
		global $post;

		switch ($column) {
			case 'sno':
				echo esc_html($post_id);
				break;	
			case 'title':
				$name = get_post($post_id);
				echo esc_html($name->post_title);
				break;

			case 'post_status':
				$status = get_post_status($post_id);
				if ( 'draft' == $status ) {
					$status = 'Disable';
				}
				if ('publish'  ==  $status) {
					$status = 'Enable';
				}
				update_post_meta($post_id, 'post_status', $status);
				echo wp_kses_post(get_post_meta($post_id, 'post_status', true));
				break;

			case 'faq_art_sort_order':
				$faq_art_sort_order = (int) get_post_meta($post_id, 'faq_art_sort_order', true);
				if (!empty( $faq_art_sort_order) || '' != $faq_art_sort_order) {
					echo esc_html($faq_art_sort_order);
				} else {
					update_post_meta($post_id, 'faq_art_sort_order', 0);
					echo (int) get_post_meta($post_id, 'faq_art_sort_order', true);
				}
				break;			

			case 'faq_num_helpful_votes':
				$faq_num_helpful_votes = (int) get_post_meta($post_id, 'faq_num_helpful_votes', true);
				if (!empty( $faq_num_helpful_votes ) || '' != $faq_num_helpful_votes) {
					echo esc_html($faq_num_helpful_votes);
				} else {
					update_post_meta($post_id, 'faq_num_helpful_votes', 0);
					echo (int) get_post_meta($post_id, 'faq_num_helpful_votes', true);
				}
				break;			

			case 'faq_num_total_votes':
				$faq_num_total_votes = (int) get_post_meta($post_id, 'faq_num_total_votes', true);

				if (( !empty($faq_num_total_votes) || 0 != $faq_num_total_votes )) {
					echo esc_html($faq_num_total_votes);
					
				} else {
					
					update_post_meta($post_id, 'faq_num_total_votes', 0);
					echo esc_html(get_post_meta($post_id, 'faq_num_total_votes', true));
				}
				break;

			case 'faq_helpful_rate':
				$num_total = (int) get_post_meta($post_id, 'faq_num_total_votes', true);
				$num_helpful = (int) get_post_meta($post_id, 'faq_num_helpful_votes', true);
				$helpful_rate = (int) get_post_meta($post_id, 'faq_helpful_rate', true);

				if (( !empty($helpful_rate) || 0 != $helpful_rate )) {
					echo esc_html(round(get_post_meta($post_id, 'faq_helpful_rate', true), 0, PHP_ROUND_HALF_UP) . '%');
				} else {
					update_post_meta($post_id, 'faq_helpful_rate', 0);
					echo esc_html(round(get_post_meta($post_id, 'faq_helpful_rate', true), 0, PHP_ROUND_HALF_UP) . '%');
				}

				break;	

			case 'post_modified':
				echo wp_kses_post(get_the_modified_date('F d, Y h:i:s A'));
				break;

			default:
				break;
		}
	}

	public static function aw_faq_article_column_register_sortable( $columns) {
		/** Sortable headers of listing grid **/
		$columns['sno'] 					= 'sno';
		$columns['name']                    ='name';
		$columns['post_status'] 			= 'post_status';	
		$columns['faq_art_sort_order']		= 'faq_art_sort_order';
		$columns['faq_num_helpful_votes']	= 'faq_num_helpful_votes';
		$columns['faq_num_total_votes']		= 'faq_num_total_votes';
		$columns['faq_helpful_rate'] 		= 'faq_helpful_rate';		
		$columns['post_modified'] 			= 'post_modified';
		return $columns;
	}

	public static function aw_faq_article_sorting_orderby( $query) {
		/** OrderBy headers of listing grid **/
		if (! is_admin()) {
			return;
		}
		global $wpdb;
		$orderby = $query->get('orderby');
		$order = $query->get('order');
		
		if ('sno' == $orderby) {
			$query->set('orderby', 'ID');
		}

		if ('post_status' == $orderby) {
			$query->set( 'meta_key', 'post_status' );
			$query->set( 'orderby', 'meta_value' );
		}

		if ('faq_art_sort_order' == $orderby) {
			$query->set( 'meta_key', 'faq_art_sort_order' );
			$query->set( 'orderby', 'meta_value_num' );
		}

		if ('faq_num_helpful_votes' == $orderby) {
			$query->set( 'meta_key', 'faq_num_helpful_votes' );
			$query->set( 'orderby', 'meta_value_num' );
		}

		if ('faq_num_total_votes' == $orderby) {
			$query->set( 'meta_key', 'faq_num_total_votes' );
			$query->set( 'orderby', 'meta_value_num' );
		}

		if ('faq_helpful_rate' == $orderby) {
			$query->set( 'meta_key', 'faq_helpful_rate');
			$query->set( 'orderby', 'meta_value_num');
		}

	}

	public static function aw_faq_article_register_my_bulk_actions( $bulk_actions) {
		/** Bulk Publish / draft Actions Renamed **/
		
		if ('faq_article' == get_post_type()) {
			$bulk_actions['publish']	= __('Enable', 'publish');
			$bulk_actions['draft'] 		= __('Disable', 'draft');
			return $bulk_actions;
		}
	}

	public static function aw_faq_article_my_bulk_action_handler( $redirect_to, $doaction, $post_ids) {
		/** Bulk Enable / Disable Action Handler **/
		foreach ($post_ids as $post_id) {
			$update_post = array(
			'post_type' 	=> 'faq_article',
			'ID' 			=> $post_id,
			'post_status' 	=> $doaction
			);

		$statusTest = wp_update_post($update_post);
		}
		$redirect_to='edit.php?post_type=faq_article';
		return $redirect_to;

	}

	public static function aw_faq_article_modified_views_post_status( $views) {
		/** Custom text for changing Draft To Disable FAQ **/
		if (isset($views['draft'])) {
			$views['draft'] = str_replace('Drafts', 'Disable', $views['draft']);
			$views['draft'] = str_replace('Draft', 'Disable', $views['draft']);
		}
		if (isset($views['publish'])) {
		   $views['publish'] = str_replace('Publisheds', 'Enable', $views['publish']);
		   $views['publish'] = str_replace('Published', 'Enable', $views['publish']);
		}
			return $views;		
	}
	public static function aw_faq_article_allnotice_messages( $messages ) { 
		/** Custom messages for various actions of articles **/
		if ('faq_article' == get_post_type()) {
			$post  		= get_post();
			$post_type 	= get_post_type($post);
			$post_type_object = get_post_type_object($post_type);


			$messages['faq_article'] = array(
				0  => '', // Unused. Messages start at index 1.
				1  => __( 'Article updated.', 'pq_faq_plugin' ),
				2  => __( 'Article field updated.', 'pq_faq_plugin' ),
				3  => __( 'Article field deleted.', 'pq_faq_plugin' ),
				4  => __( 'Article updated.', 'pq_faq_plugin' ),
				6  => __( 'Article published.', 'pq_faq_plugin' ),
				7  => __( 'Article saved.', 'pq_faq_plugin' ),
				8  => __( 'Article submitted.', 'pq_faq_plugin' ),
				10 => __( 'Article updated.', 'pq_faq_plugin'),
			);
			return $messages;
		}
	}

	public static function aw_faq_article_trashnotice() { 
		/** Custom messages for trash and restore actions of faq **/
		if ('faq_article' == get_post_type()) {
			if (isset($_REQUEST['post_type'])) {
				if ('faq_article' == $_REQUEST['post_type']) {
					$class = 'updated notice is-dismissible';
					if (isset($_REQUEST['trashed'])) {
						$message = __( wp_kses_post($_REQUEST['trashed']) . ' article moved to the Trash.', 'your-plugin-textdomain' );
						printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
					}
					if (isset($_REQUEST['untrashed'])) {
						$message = __( wp_kses_post($_REQUEST['untrashed']) . ' article restored from the Trash.', 'your-plugin-textdomain' );
						printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
					}

					echo '<style> #message { display: none; }</style>';
				}
			}
		}
	}
	public static function aw_faq_article_add_row_actions( $actions, $post_object) {
		if ('faq_article' == get_post_type()) {
			if ('publish' == get_post_status()) {
				$actions['disable'] = '<a href="edit.php?post_type=faq_article&disables&id=' . get_the_ID() . '">' . __('Disable') . '</a>';

			}
			if ('draft' == get_post_status()) {
				$actions['enable'] = '<a href="edit.php?post_type=faq_article&enables&id=' . get_the_ID() . '">' . __('Enable') . '</a>';
			}
			if (isset($_GET['enables'])) {
				if (isset($_GET['id'])) {
					$my_post = array();
					$my_post['ID'] = sanitize_text_field($_GET['id']);
					$my_post['post_status'] = 'publish';
					wp_update_post( $my_post );
					wp_redirect(admin_url() . 'edit.php?post_type=faq_article');
				}
			}
			if (isset($_GET['disables'])) {
				if (isset($_GET['id'])) {
					$my_post = array();
					$my_post['ID'] = sanitize_text_field($_GET['id']);
					$my_post['post_status'] = 'draft';
					wp_update_post( $my_post );
					wp_redirect(admin_url() . 'edit.php?post_type=faq_article');
				}
			}
			return $actions;
		}
	}
	public static function aw_faq_display_cat_filter_taxonomy() {
		global $typenow;
		$post_type = 'faq_article';
		$taxonomy  = 'faq_cat'; 
		if ($typenow == $post_type) {
			if (!empty($_GET[$taxonomy])) {
				$var          = sanitize_text_field($_GET[$taxonomy]);
			} else {
				$var = '';
			}
			$selected      = isset($var) ? $var : '';
			
			$info_taxonomy = get_taxonomy($taxonomy);
			wp_dropdown_categories(array(
				'show_option_all' => sprintf( 'All %s', $info_taxonomy->label ),
				'taxonomy'        => $taxonomy,
				'name'            => $taxonomy,
				'orderby'         => 'name',
				'selected'        => $selected,
				'hide_empty'      => true,
			));
		};
	}
	public static function aw_faq_process_cat_filter_query( $query) {
		global $pagenow;
		$post_type = 'faq_article';
		$taxonomy  = 'faq_cat'; 
		$q_vars    = &$query->query_vars;
		if ('edit.php' == $pagenow  && isset($q_vars['post_type']) && $post_type == $q_vars['post_type']  && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && 0 != $q_vars[$taxonomy]) {
			$term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
			$q_vars[$taxonomy] = $term->slug;
		}
	}

	public static function aw_article_meta_box() {
		$post_type = get_post_type();

		$multi_posts = array('faq_article');
		add_meta_box('Article', __('Article', 'faq_article'),
			array('AwPqFaqArticleAdmin', 'aw_article_meta_box_callback'),
			$multi_posts,
			'normal',
			'high'
		);
		if ('faq_article' == $post_type) {
			remove_meta_box('commentstatusdiv', 'faq_article', 'normal');
			remove_meta_box('commentsdiv', 'faq_article', 'normal');
		}
		
	}
	
	public static function aw_article_meta_box_callback( $post, $metabox) {
		global $wpdb;
		/** Show Meta Boxes / Fileds on admin Add New and Edit Article **/
		$select_category 			= get_post_meta($post->ID, 'select_category', true);
		$faq_art_desc 			    = get_post_meta($post->ID, 'faq_art_desc', true);
		$faq_art_sort_order			= get_post_meta($post->ID, 'faq_art_sort_order', true);
		$faq_art_meta_title 		= get_post_meta($post->ID, 'faq_art_meta_title', true);
		$faq_num_helpful_votes	    = get_post_meta($post->ID, 'faq_num_helpful_votes', true);
		$faq_num_total_votes	    = get_post_meta($post->ID, 'faq_num_total_votes', true);

		wp_nonce_field('article_nonce_action', 'article_nonce_name');
		?>
		<div class="article_container" id="article_container">
			<ul>
				<li>
					<label> Category</label>
					<div class="control">
						<?php
						$categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aw_pq_faq_category_list WHERE status = 1", ARRAY_A);
					
						//$categories = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aw_pq_faq_category_list WHERE status = %d ",1), ARRAY_N);
			
						if ( $categories ) {
							?>
						<select name="select_category" id="select_category" class="input-text required txt_required">

						<?php 
							foreach ($categories as $cat) {
								if ($cat['id'] == $select_category) { 
									?>
								<option value="<?php echo esc_html($cat['id']); ?>" selected><?php echo esc_html($cat['category_name']); ?></option>
								   <?php 
								} else {
									?>
								<option value="<?php echo esc_html($cat['id']); ?>"><?php echo esc_html($cat['category_name']); ?></option>
							<?php 
								}
							}
							?>
						</select>
						 <?php
						}
						?>
					</div>
				</li>
				<li>
					<label>Sort order</label>
					<div class="control">
						<input type="text" name="faq_art_sort_order" id="faq_art_sort_order" class="input-text txt_required" value="<?php echo esc_html($faq_art_sort_order); ?>" onkeypress="return checkIt(event)"><br/>
						<p>Articles with lower value will appear first. Note: Articles with the same value are sorted by number of helpful votes</p>
					</div>
				</li>				
				<li>
					<label>Meta title:</label>
					<div class="control">
						<input type="text" name="faq_art_meta_title" id="faq_art_meta_title" class="input-text" value="<?php echo esc_html($faq_art_meta_title); ?>">
					</div>
				</li>
				<li>
					<label>Meta Description:</label>
					<div class="control">
						<textarea name="faq_art_desc" id="faq_art_desc" class="txt_required"><?php echo esc_html($faq_art_desc); ?></textarea>
					</div>
				</li>
			</ul>
			<h3>
				<?php echo wp_kses('Statistics', wp_kses_allowed_html('post')); ?>
			</h3>
			<ul>
				<li>
					<label>Number of helpful votes</label>
					<div class="control">
						<input type="text" name="faq_num_helpful_votes" id="faq_num_helpful_votes" class="input-text txt_required" value="<?php echo esc_html($faq_num_helpful_votes); ?>" onkeypress="return checkIt(event)"><br/>
					</div>
				</li>
				<li>
					<label>Number of total votes</label>
					<div class="control">
						<input type="text" name="faq_num_total_votes" id="faq_num_total_votes" class="input-text txt_required" value="<?php echo esc_html($faq_num_total_votes); ?>" onkeypress="return checkIt(event)"><br/>
					</div>
				</li>											
			</ul> 			
		</div>
		<?php
	}
	public static function aw_article_save_data( $post_id) {
		/** Function to save or update article data **/
		if (isset($_POST['post_type']) && 'faq_article' == $_POST['post_type']) {
			global $post,$wpdb;

			// if we are doing an autosave then return
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
				return;
			}

			// if the nonce is not present there or we can not verify it.
			if (!isset($_POST['article_nonce_name']) || !wp_verify_nonce(sanitize_key($_POST['article_nonce_name']), 'article_nonce_action')) {
				return;
			}
			
			if (isset($_POST['select_category']) && ( '' != $_POST['select_category'] )) {
				update_post_meta($post_id, 'select_category', sanitize_text_field($_POST['select_category']));
			}

			if (isset($_POST['faq_art_sort_order']) && ( '' != $_POST['faq_art_sort_order'] )) {
				update_post_meta($post_id, 'faq_art_sort_order', sanitize_text_field($_POST['faq_art_sort_order']));
			} else {
				update_post_meta($post_id, 'faq_art_sort_order', 0);
			}

			if (isset($_POST['faq_art_meta_title']) && ( '' != $_POST['faq_art_meta_title'] )) {
				update_post_meta($post_id, 'faq_art_meta_title', sanitize_text_field($_POST['faq_art_meta_title']));
			} else {
				update_post_meta($post_id, 'faq_art_meta_title', '');
			}

			if (isset($_POST['faq_art_desc']) && ( '' != $_POST['faq_art_desc'] )) {
				update_post_meta($post_id, 'faq_art_desc', sanitize_text_field($_POST['faq_art_desc']));
			} else {
				update_post_meta($post_id, 'faq_art_desc', '');
			}

			if (isset($_POST['faq_num_helpful_votes']) && ( '' != $_POST['faq_num_helpful_votes'] )) {
				update_post_meta($post_id, 'faq_num_helpful_votes', sanitize_text_field($_POST['faq_num_helpful_votes']));
			} else {
				update_post_meta($post_id, 'faq_num_helpful_votes', 0);
			}

			if (isset($_POST['faq_num_total_votes']) && ( '' != $_POST['faq_num_total_votes'] )) {
				$total_votes = sanitize_text_field($_POST['faq_num_total_votes']);
				update_post_meta($post_id, 'faq_num_total_votes', $total_votes );
			} else {
				update_post_meta($post_id, 'faq_num_total_votes', 0);
			}

			if (isset($_POST['faq_num_helpful_votes']) && ( '' != $_POST['faq_num_helpful_votes'] ) && isset($_POST['faq_num_total_votes']) && ( '' != $_POST['faq_num_total_votes'] ) && ( 0 != $_POST['faq_num_total_votes'] )) {
				$num_total_votes = (int) get_post_meta($post_id, 'faq_num_total_votes', true);
				$num_helpful_votes = (int) get_post_meta($post_id, 'faq_num_helpful_votes', true);
				$num_not_helpful_votes = $num_total_votes - $num_helpful_votes;
				$rate = ( $num_helpful_votes * 100 );
				$rate = ( $rate / $num_total_votes );
				$per_rate = round($rate, 0, PHP_ROUND_HALF_UP);
				update_post_meta($post_id, 'faq_num_not_helpful_votes', $num_not_helpful_votes );
				update_post_meta($post_id, 'faq_helpful_rate', $per_rate);
				
			} else {
				update_post_meta($post_id, 'faq_num_not_helpful_votes', 0);
				update_post_meta($post_id, 'faq_helpful_rate', 0);
			}



			$post_table = $wpdb->prefix . 'posts';

			if (isset($_POST['select_category'])) {
				$cat_id = sanitize_text_field($_POST['select_category']);

				$cat_slug = $wpdb->get_var( $wpdb->prepare("SELECT category_slug FROM {$wpdb->prefix}aw_pq_faq_category_list WHERE status = %d AND id = %d ", 1, "{$cat_id}"));
				
				$term_id = $wpdb->get_var( $wpdb->prepare("SELECT term_id FROM {$wpdb->prefix }terms WHERE slug= %s", "{$cat_slug }"));				
				
				$term_taxonomy_id = $wpdb->get_var( $wpdb->prepare("SELECT term_taxonomy_id FROM {$wpdb->prefix }term_taxonomy WHERE term_id = %d ", "{$term_id}"));

				$count = $wpdb->get_var( $wpdb->prepare("SELECT count  FROM {$wpdb->prefix }term_taxonomy WHERE term_id = %d", "{$term_id}" ));
				
				if (0 == $count) {
					$count = ++$count;
					$wpdb->query( $wpdb->prepare("UPDATE {$wpdb->prefix }term_taxonomy SET count = %d  WHERE term_id = %d", "{$count}", "{$term_id}"));
				}
							
				$record_exits = $wpdb->get_var( $wpdb->prepare("SELECT object_id FROM {$wpdb->prefix }term_relationships WHERE object_id = %d ", "{$post_id}"));

				if (empty($record_exits)) {
					$db_table_3 = $wpdb->prefix . 'term_relationships';
					$post_array_3 = array(
							'object_id'				=> $post_id,
							'term_taxonomy_id'		=> $term_taxonomy_id,
							'term_order' 			=> '0'
							);
					$wpdb->insert($db_table_3, $post_array_3);
				} else {
					$db_table_3 = $wpdb->prefix . 'term_relationships';
					$post_array_3 = array(
					'term_taxonomy_id' => $term_taxonomy_id,
					'term_order' => '0'
					);
					$wpdb->update($db_table_3, $post_array_3, array('object_id'=>$post_id ));
				}
			}				
		}
	}
}
