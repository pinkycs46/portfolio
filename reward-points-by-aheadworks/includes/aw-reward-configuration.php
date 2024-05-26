<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
 
class AwRewardConfiguration {

	public function __construct() {
		$configuration->startfun();
	}
	public static function aw_configuration_html() {
		$nonce = wp_create_nonce( 'rdrewardpoints_admin_nonce' );
		$result = aw_reward_points_get_config();
		$img_path = plugin_dir_url( dirname( __FILE__ ) ) . 'admin/images/aw_trash-icon.png';
		?>
		
		<div class="tab-grid-wrapper">
			<div class="spw-rw clearfix">
				<div class="panel-box temp-design">
					<div class="page-title">
						<h1>Configuration</h1>
					</div>
					<div class="panel-body">
						<div class="tab">
							<button class="tablinks" id="design_earnpoints_tab"  onclick="openTab(event, 'earnpoints-tab')">Earn Points</button>
							<button class="tablinks" id="design_spendpoints_tab" onclick="openTab(event, 'spendpoints-tab')">Spend Points</button>
							<button class="tablinks" id="design_storefront_tab" onclick="openTab(event, 'storefront-tab')">Storefront</button>
						</div> 
						<div id="earnpoints-tab" class="tabcontent">
						<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
							<?php wp_nonce_field( 'save_configuration_form', 'rdrewardpoints_admin_nonce' ); ?>
							<input type="hidden" name="action" value="save_configuration_form">	
							<div class="earn-point-calculation">
							<ul>
								<li>
									<label>Points earning calculation</label>
									<div class="control">
										<select name="beforafter_calculation" id="reward_calculation_before_after" class="required">
										<?php 
										$btselected='';
										$atselected='';
										if (!empty($result)) {
											if ('BT'===$result['beforafter_calculation']) {
												$btselected='selected = selected';
											}
											if ('AT'===$result['beforafter_calculation']) {
												$atselected='selected = selected';
											}
										}
										?>
											<option value="BT" <?php echo esc_html($btselected); ?>>Before Tax</option>
											<option value="AT" <?php echo esc_html($atselected); ?>>After Tax</option>
										</select>
									</div>
								</li>
								<li>
									<label>Points expiration period, in days</label>
									<div class="control">
										<input type="text" name="expiration_day" id="reward_expiration_day" class="input-txt required" value="<?php echo  empty($result)? '' : esc_html($result['expiration_day']); ?>" onkeypress="return checkIt(event)">
										<p class="info">Set 0 or leave empty if the expiration period is unlimited</p>
									</div>
								</li>
							</ul>
							</div>
							<div class="earn-point-rate">
								<h3>Earn Rates</h3>
								<div class="table-responsive">
								 <table id="earn_table" class="data-table">
									 <thead>
									 <tr>
										 <th>Customer Lifetime Purchases >= </th>
										 <th>Base Currency</th>
										 <th>Points</th>
										 <th>Action</th> 
									 </tr>
									</thead>
									 <?php 
										 $i=0;
										?>
									 <tbody>
										<?php 
										 $i = 0;
										 //$numrow = 1;
										 $numrow = 0;
										 $key=0;
										if (!empty($result)) {
											$rates = unserialize($result['earnrates']);
											if (!empty($rates)) {
												foreach ($rates as $key => $rate) {  
													$numrow = $key+1;
													?>
													 <tr id="earn_row_<?php echo esc_html($key); ?>">
														 <td>
															 <input type="text" name="earnrates[<?php echo esc_html($key); ?>][lifetime_sale]" class="txt_required lfsale" value="<?php echo esc_html($rate['lifetime_sale']); ?>" data-allowed="true" data-value="earn_error_<?php echo esc_html($key); ?>_0"  onkeypress="return checkIt(event,true)">
															 <span id="earn_error_<?php echo esc_html($key); ?>_0"></span>
														 </td>
														 <td>
															 <input type="text" name="earnrates[<?php echo esc_html($key); ?>][base_currency]" class="txt_required" value="<?php echo esc_html($rate['base_currency']); ?>" data-allowed="false" data-value="earn_error_<?php echo esc_html($key); ?>_1" onkeypress="return checkIt(event,false)">
															 <br>
															 <span id="earn_error_<?php echo esc_html($key); ?>_1"></span>
														 </td>
														 <td>
															 <input type="text" class="txt_required" name="earnrates[<?php echo esc_html($key); ?>][points]" value="<?php echo esc_html($rate['points']); ?>" data-allowed="false" data-value="earn_error_<?php echo esc_html($key); ?>_2" onkeypress="return checkIt(event,false)">
															 <br>
															 <span id="earn_error_<?php echo esc_html($key); ?>_2"></span>
														 </td>
														 <td>
															 <a href="javascript:void(0)" onclick="return deleterate('earn','<?php echo esc_html($key); ?>')"><img src="<?php echo esc_html($img_path); ?>"></a><input type="hidden" class="current_row" value="1">
														 </td>
													</tr>
													<?php 
												}
											}
										}
										?>
									</tbody>
									<tfoot>
									 <tr>
										 <td colspan="4"> <input type="button" data-row="<?php echo esc_html($numrow); ?>" onclick="return addmorerate('earn')" class="button button-primary" id="addmoreearn" value="Add Earn Rate"></td>
									 </tr>
									</tfoot>
								 </table>
								 </div>
							</div>

							<div class="action-rw clearfix">
							<input type="submit" name="earn_configuration_submit" class="button button-primary configuration_submit" value="Save Changes" data-value="earn" >
							</div>
						</form>
						</div> <!-- close of individual tab -->
						<div id="spendpoints-tab" class="tabcontent">
						<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
							<?php wp_nonce_field( 'save_configuration_form', 'rdrewardpoints_admin_nonce' ); ?>
							<input type="hidden" name="action" value="save_configuration_form">	
							<div class="earn-point-calculation allow-pur-point">
								<h3>Allow to cover <input class="cover_per_txt" type="text" name="cover_percentage" value="<?php echo empty($result) ? '' : esc_html($result['cover_percentage']); ?>" onkeypress="return checkIt(event)"> % of purchase by points</h3>
								<span id="cover_per_span"></span>
							</div>

							<div class="earn-point-rate">
								<h3>Spend Rates</h3>
								<div class="table-responsive">
								 <table id="spend_table" class="data-table">
								 <thead>
									 <tr>
										 <th>Customer Lifetime Purchases >= </th>
										 <th>Base Currency</th>
										 <th>Points</th>
										 <th>Action</th>
									 </tr>
								 </thead>
								 <?php 
									 $i=0;
									?>
								 <tbody>
									<?php 
									 $i = 0;
									 $numrow = 0;
									  $key=0;
									if (!empty($result)) {
										$rates = unserialize($result['spendrates']);
										if (!empty($rates)) {
											foreach ($rates as $key => $rate) {  
												$numrow = $key+1;
												?>
												 <tr id="spend_row_<?php echo esc_html($key); ?>">
													 <td>
														 <input type="text" name="spendrates[<?php echo esc_html($key); ?>][lifetime_sale]" class="txt_required lfsale" value="<?php echo esc_html($rate['lifetime_sale']); ?>" data-allowed="true" data-value="spend_error_<?php echo esc_html($key); ?>_0" onkeypress="return checkIt(event,true)">
														 <span id="spend_error_<?php echo esc_html($key); ?>_0"></span>
													 </td>
													 <td><input type="text" name="spendrates[<?php echo esc_html($key); ?>][base_currency]" class="txt_required" value="<?php echo esc_html($rate['base_currency']); ?>" data-allowed="false"data-value="spend_error_<?php echo esc_html($key); ?>_1" onkeypress="return checkIt(event,false)">
														 <br>
														 <span id="spend_error_<?php echo esc_html($key); ?>_1"></span>
													 </td>
													 <td><input type="text" name="spendrates[<?php echo esc_html($key); ?>][points]" class="txt_required" value="<?php echo esc_html($rate['points']); ?>" data-allowed="false" data-value="spend_error_<?php echo esc_html($key); ?>_2" onkeypress="return checkIt(event,false)">
														 <br>
														 <span id="spend_error_<?php echo esc_html($key); ?>_2"></span>
													 </td>
													 <td><a href="javascript:void(0)" onclick="return deleterate('spend','<?php echo esc_html($key); ?>')"><img src="<?php echo esc_html($img_path); ?>"></a><input type="hidden" class="current_row" value="1"></td>
												</tr>
												<?php 
											}
										}
									}
									?>
								</tbody>
								<tfoot>
								 <tr>
									 <td colspan="4"> <input type="button" data-row="<?php echo esc_html($numrow); ?>" onclick="return addmorerate('spend')" class="button button-primary" id="addmorespend" value="Add Spend Rate"></td>
								 </tr>
								</tfoot>
							 </table>
							 </div>
							</div>
							<div class="action-rw clearfix">
								<input type="submit" name="spend_configuration_submit" class="button button-primary configuration_submit" value="Save Changes" data-value="spend"  >
							</div>
						</form>
						</div>
						<div id="storefront-tab" class="tabcontent">
						<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
							<?php wp_nonce_field( 'save_configuration_form', 'rdrewardpoints_admin_nonce' ); ?>
							<input type="hidden" name="action" value="save_configuration_form">	
							<div class="storefront-setting">
							<ul>
								<li>
									<label>Display points earnings</label>
									<div class="control">
										<select name="display_earn" id="reward_display_earn" class="required" onchange="displayearn(this)">
										<?php 
										$displayearnyes='';
										$displayearnno='';
										
										if (!empty($result)) {
											if ('YES'===$result['display_earn']) {
												$displayearnyes='selected = selected';

											}
											if ('NO'===$result['display_earn']) {
												$displayearnno='selected = selected';
											}
										} else {
												$displayearnno='selected = selected';
										}
										?>
											<option value="YES" <?php echo esc_html($displayearnyes); ?>>YES</option>
											<option value="NO" <?php echo esc_html($displayearnno); ?>>NO</option>
										</select>
										<p id="displayearnyes_info">On the Cart and Checkout page</p>
									</div>
								</li>
								<li id="hide_nodisplay">
									<label>Promo text</label>
									<div class="control">
										<input type="text" name="promotext" id="storefront_prmotext" class="input-txt required txt_required" value="<?php echo  empty($result['promotext']) ? 'Complete this purchase and get <total amount> points' : esc_html($result['promotext']) ; ?>">
										<span id="error_storefront"></span>
									</div>
									<p class="info">"&lt;total amount&gt;" is required in promotext to display point.</p>
								</li>
							</ul>
							</div>
							 

							<div class="action-rw clearfix">
							<input type="submit" name="storefront_configuration_submit" class="button button-primary configuration_submit" value="Save Changes" data-value="storefront" >
							</div>
						</form>
						</div> <!-- close of individual tab -->
					   </div>
				</div>
			</div>
		</div>	
		<form>		
		<?php 
	}
}
?>
