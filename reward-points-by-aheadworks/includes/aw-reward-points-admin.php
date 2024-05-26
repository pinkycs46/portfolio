<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class AwRewardPointsAdmin {
	
	public static function aw_self_deactivate_notice() {
		/** Display an error message when WooComemrce Plugin is missing **/
		?>
		<div class="notice notice-error">
			<p>Please install and activate WooComemrce plugin before activating Rd Reward Points plugin.</p>
		</div>
		<?php
	}

	public static function aw_transaction_balance_html() {
		?>
		<div id="update_bal_Modal" class="bal_modal">

		  <!-- Modal content -->
		  <div class="bal_modal-content">
			<!-- <span class="bal_modal_close">&times;</span> -->
			<!--<span>Update Balance</span>-->
			<div class="rd-header"><h2>Update Balance</h2></div>
			<div class="rd-update-balance">
				<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
					<?php wp_nonce_field( 'balance_update_form', 'rdrewardpoints_admin_nonce' ); ?>
					<input type="hidden" name="action" value="balance_update_form">	
					<input type="hidden" value="" name="user_id" class="allids">
					<ul>
						<li>
							<label><strong>Points</strong></label>
							<div class="control">
								<input type="text" class="input-text" id="updatepoint_input" name="points" value="" onkeypress="return checkupdateval(event,true)">
							</div>
							<span id="updatepoint_txt"></span>
						</li>
						<li>
							<label><strong>Comments to Admin</strong></label>
							<div class="control">
								<textarea class="input-text" cols="25" rows="5" name="update_comments" id="updatepoint_txt"></textarea>
							</div>
						</li>
					</ul>
	 
					 
					<!-- <p>Some text in the Modal..</p> -->
					<div class="bal_modal_action_btns">
						<input type="submit" name="updatepoint" class="bal_modal_apply button" value="Apply" id="apply_button">
						<input type="button" name="" class="bal_modal_close inactive-btn " value="Cancel" id="close_button">
					</div>
				</form>
			</div>	
		  </div>
		</div>
		<div class="tab-grid-wrapper">
			<div class="spw-rw clearfix">
				<div class="panel-box temp-design">
					<div class="wrap">
						<div class="page-title">
							<h1 class="wp-heading-inline"><?php //_e('Customer Balance', 'customer balance') ?></h1>
						</div>
						<div class="panel-body">
							<div class="tab">
								<button class="tablinks" data-screen="balance-tab" id="design_earnpoints_tab"  onclick="openTab(event, 'balance-tab', 'balance')"><?php esc_html_e('Customer Balance', 'customer balance'); ?></button>
								<button class="tablinks" data-screen="transaction-tab" id="design_spendpoints_tab" onclick="openTab(event, 'transaction-tab', 'transaction')"><?php esc_html_e('Transaction History', 'transaction history'); ?></button>
							</div> 
							<div id="balance-tab" class="tabcontent">
								<?php
									global $wpdb;
									$tablename 			= $wpdb->prefix . 'reward_points_balances';
									$db_user_table 	   	= "{$wpdb->prefix}users";
									$db_usermeta_table 	= $wpdb->prefix . 'usermeta';
									
									$table_bal = new AwRewardTransactionBalance();
									$search = '';

								if (isset($_GET['s']) && isset($_GET['screen']) && 'balance-tab' == $_GET['screen']) {
									$search = sanitize_text_field($_GET['s']);
								}
							
								$count_all = $wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) FROM  {$wpdb->get_blog_prefix(1)}users WHERE 1=%d", 1));
								?>
								<ul class="subsubsub">
									<li class="all"><b style="color:#000000;">All</b> <span class="count">(<?php echo esc_html($count_all); ?>)</span></li>
								</ul>
								<form id="posts-filter" method="get">
									<p class="search-box">
										<input type="hidden" name="page" class="page" value="reward-transaction-balance">	
										<input type="hidden" name="screen" class="post_status_page" value="balance-tab">
										<input type="search" id="post-search-input" name="s" value="<?php echo esc_html($search); ?>">
										<input type="submit" id="search-submit" class="button" value="Search Customer">
									</p>
								</form>
								<form id="balance-table" method="GET">
									<?php
									if (isset($_GET['screen']) && 'balance-tab' === $_GET['screen']) {
										$table_bal->prepare_items('balance-tab', $search);
										echo esc_html($table_bal->display());
									} else if (!isset($_GET['screen'])) {
										$table_bal->prepare_items('balance-tab', $search);
										echo esc_html($table_bal->display());	
									}
									?>
								</form>
							</div>

							<div id="transaction-tab" class="tabcontent">
								<?php
									$tablename = 'reward_points_transaction_history';
									$table_trans = new AwRewardTransactionBalance();
									$search = '';
								if (isset($_GET['s']) && isset($_GET['screen']) && 'transaction-tab' == $_GET['screen']) {
									$search = sanitize_text_field($_GET['s']);
									if (isset($_GET['d'])) {
										$link_search = '';
									}
									$count_all = $table_trans->get_count($tablename);
								}
								?>
								<form id="posts-filter" method="get">
									<p class="search-box">
										<input type="hidden" name="page" class="page" value="reward-transaction-balance">	
										<input type="hidden" name="screen" class="post_status_page" value="transaction-tab">
										<input type="search" id="post-search-input" name="s" value="<?php echo isset($_GET['d']) ? esc_html($link_search) : esc_html($search); ?>">
										<input type="submit" id="search-submit" class="button" value="Search Customer" title="Search Customer by Customer Name or Customer Email or Order #">
									</p>
								</form>
								<form id="transaction-table" method="GET">
									<?php
									if (isset($_GET['screen']) && 'transaction-tab' === $_GET['screen']) {
										$table_trans->prepare_items('transaction-tab', $search);
										$table_trans->display();
									}
									?>
								</form>
							</div>
						</div>
					</div> <!-- clos of wrap -->
				</div>
			</div>
		</div>
		<?php
	}
}
?>
