<?php

function espresso_display_megasoft($data) {
	extract($data);
	global $org_options;
	wp_enqueue_script( 'megasoft' );
	$megasoft_settings = get_option('event_espresso_megasoft_settings');		
	?>
<div id="megasoft-payment-option-dv" class="payment-option-dv">

	<a id="megasoft-payment-option-lnk" class="payment-option-lnk display-the-hidden" rel="megasoft-payment-option-form" style="cursor:pointer;">
		<img alt="Pay using Megasoft" src="<?php echo $megasoft_settings['button_url']?>">
	</a>	

	<div id="megasoft-payment-option-form-dv" class="hide-if-js">
		<?php
		
		$use_sandbox = $megasoft_settings['use_sandbox'];
		if ($use_sandbox) {
			echo '<p>Test credit card # 4007000000027</p>';
			echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Debug Mode Is Turned On', 'event_espresso') . '</h3>';
		}
		if ($megasoft_settings['display_header']) {
?>
		<h3 class="payment_header"><?php echo $megasoft_settings['header']; ?></h3><?php } ?>

		<form id="megasoft_payment_form" name="megasoft_payment_form" method="post" action="<?php echo add_query_arg(array('r_id'=>$registration_id), get_permalink($org_options['return_url'])); ?>">
			<div class = "event_espresso_form_wrapper">

				<fieldset id="megasoft-billing-info-dv">
					<h4 class="section-title"><?php _e('Información de Facturación', 'event_espresso') ?></h4>
					<p>
						<label for="first_name"><?php _e('Nombre', 'event_espresso'); ?></label>
						<input name="first_name" type="text" id="megasoft_first_name" value="<?php echo $fname ?>" class="required" />
					</p>
					<p>
						<label for="last_name"><?php _e('Apellido', 'event_espresso'); ?></label>
						<input name="last_name" type="text" id="megasoft_last_name" value="<?php echo $lname ?>" class="required" />
					</p>
					<p>
					  <label for="cid_code"><?php _e('Número de Identificación', 'event_espresso'); ?></label>
					  <select id="cid_code" name ="cid_code" class="required">
							<option value='V'><?php _e('Venezolano','event_espresso'); ?></option>
							<option value='J'><?php _e('Juridico','event_espresso');?></option>
							<option value='E'><?php _e('Extranjero','event_espresso'); ?></option>
							<option value='G'><?php _e("Gubernamental",'event_espresso');?></option>
							<option value='P'><?php _e("Pasaporte",'event_espresso');?></option>
						</select>
					</p>
					<p>
						<label for="cid"><?php _e('Cédula o Pasaporte', 'event_espresso'); ?></label>
						<input name="cid" type="text" id="cid" value="" />
					</p>
				</fieldset>

				<fieldset id="megasoft-credit-card-info-dv">
					<h4 class="section-title"><?php _e('Información de la Tarjeta de Crédito', 'event_espresso'); ?></h4>
					<p>
						<label for="card_num"><?php _e('Número de la Tarjeta', 'event_espresso'); ?></label>
						<input type="text" name="card_num" id="megasoft_card_num"  class="required" autocomplete="off" />
					</p>
					<p>
						<?php 
							$currentMonth=date('m');
							$months=array();
							for($i=0;$i<12;$i++){
								$months[$i]['id']=sprintf("%02s",$i+1);
								$months[$i]['text']=$months[$i]['id'];
							}
							$currentYear=intval(date('Y'));
							$years=array();
							for($i=0;$i<20;$i++){
								$years[$i]['id']=substr($currentYear+$i,2);
								$years[$i]['text']=$currentYear+$i;
							}
						
						?>
						<label for="exp_date"><?php _e('Fecha de Caducidad', 'event_espresso'); ?></label>
						<?php echo select_input('exp_date_month',$months,$currentMonth, 'class="med"' );?>/
						<?php echo select_input('exp_date_year',$years,$currentYear, 'class="med"')?>
					</p>
					<p>
						<label for="ccv_code"><?php _e('Código CCV', 'event_espresso'); ?></label>
						<input type="text" name="ccv_code" id="megasoft_ccv_code"  class="small required" autocomplete="off" />
					</p>
				</fieldset>
				<input name="invoice_num" type="hidden" value="<?php echo $registration_id;//substr(event_espresso_session_id(),0,10); ?>" />
				<input name="megasoft" type="hidden" value="true" />
				<input name="cust_id" type="hidden" value="<?php echo $attendee_id ?>" />
				
				<p class="event_form_submit">
					<input name="megasoft_submit" id="megasoft_submit" class="submit-payment-btn allow-leave-page" type="submit" value="<?php _e('Completar Compra', 'event_espresso'); ?>" />
					<div class="clear"></div>
				</p>
			</div>
		</form>
		<br/>
		<p class="choose-diff-pay-option-pg">
			<a class="hide-the-displayed" rel="megasoft-payment-option-form" style="cursor:pointer;"><?php _e('Escoja otra opción de pago', 'event_espresso'); ?></a>
		</p>

	</div>
</div>
	<?php
}
add_action('action_hook_espresso_display_onsite_payment_gateway', 'espresso_display_megasoft');