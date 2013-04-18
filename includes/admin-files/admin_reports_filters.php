<?php
global $espresso_premium;
if ($espresso_premium != true) {
	return;
}

function espresso_display_month_category_status_filters() {
	$total_events = espresso_total_events();
	?>
	<h3 style="margin-bottom: -8px;"><?php echo __('Filters', 'event_espresso'); ?></h3>
	<ul class="subsubsub">
		<li><strong>
				<?php _e('Events', 'event_espresso'); ?>
				: </strong> </li>
		<li><a <?php echo (isset($_REQUEST['all']) && $_REQUEST['all'] == 'true') ? ' class="current" ' : '' ?> href="admin.php?page=events&all=true&max_rows=100000">
				<?php _e('All Events', 'event_espresso'); ?>
				<span class="count">(<?php echo $total_events; ?>)</span></a> |</li>
		<li><a <?php echo (isset($_REQUEST['today']) && $_REQUEST['today'] == 'true') ? ' class="current" ' : '' ?> href="admin.php?page=events&today=true&max_rows=100000">
				<?php _e('Today', 'event_espresso'); ?>
				<span class="count">(<?php echo espresso_total_events_today(); ?>)</span></a> |</li>
		<li><a <?php echo (isset($_REQUEST['this_month']) && $_REQUEST['this_month'] == 'true') ? ' class="current" ' : '' ?>  href="admin.php?page=events&this_month=true&max_rows=100000">
				<?php _e('This Month', 'event_espresso'); ?>
				<span class="count">(<?php echo espresso_total_events_this_month(); ?>)</span></a></li>
	</ul>
	<ul class="subsubsub">
		<li><strong>
				<?php _e('Attendees', 'event_espresso'); ?>
				: </strong> </li>
		<li><a <?php echo (isset($_REQUEST['all_a']) && $_REQUEST['all_a'] == 'true') ? ' class="current" ' : '' ?> href="admin.php?page=events&event_admin_reports=event_list_attendees&all_a=true">
				<?php _e('All Attendees', 'event_espresso'); ?>
				<span class="count">(<?php echo espresso_total_all_attendees(); ?>)</span></a> | </li>
		<li><a <?php echo (isset($_REQUEST['today_a']) && $_REQUEST['today_a'] == 'true') ? ' class="current" ' : '' ?> href="admin.php?page=events&event_admin_reports=event_list_attendees&today_a=true">
				<?php _e('Today', 'event_espresso'); ?>
				<span class="count">(<?php echo espresso_total_attendees_today(); ?>)</span></a> |</li>
		<li><a <?php echo (isset($_REQUEST['this_month_a']) && $_REQUEST['this_month_a'] == 'true') ? ' class="current" ' : '' ?>  href="admin.php?page=events&event_admin_reports=event_list_attendees&this_month_a=true">
				<?php _e('This Month', 'event_espresso'); ?>
				<span class="count">(<?php echo espresso_total_attendees_this_month(); ?>)</span></a> </li>
		<?php if (!empty($_REQUEST['event_id']) && (empty($_REQUEST['event_admin_reports']) || $_REQUEST['event_admin_reports'] != 'charts')) { ?>
			<li> | <a href="admin.php?page=events&event_admin_reports=charts&event_id=<?php echo $_REQUEST['event_id'] ?>">
					<?php _e('View Report', 'event_espresso'); ?>
				</a></li>
		<?php } ?>
	</ul>
	<div style="clear:both"></div>
	<?php
}





function espresso_ee_tablenav_form_open () {
	?>
	<div class="ee_tablenav tablenav">
		<div class="actions">
			<form id="ee_tablenav" name="ee_tablenav" method="get" action="<?php echo $_SERVER["REQUEST_URI"] ?>">	
	<?php
}




function espresso_ee_tablenav_form_close () {
	?>
			</form>
		</div>
	</div>	
	<?php	
}





function espresso_display_ee_tablenav () {

	//because we're doing a get request, we'll need to explicitly preserve the old GET querystring
	if (isset($_GET['page'])) { 
		echo espresso_hidden_form_input( 'page', $_GET['page'] );
	}
	if (isset($_GET['event_admin_reports'])) { 
		echo espresso_hidden_form_input( 'event_admin_reports', $_GET['event_admin_reports'] );
	}
	if (isset($_GET['event_id'])) { 
		echo espresso_hidden_form_input( 'event_id', $_GET['event_id'] );
	}
	
	// what page are we doing filtering for ?
	$_REQUEST['event_admin_reports'] = isset($_REQUEST['event_admin_reports']) ? $_REQUEST['event_admin_reports'] : '';
	switch ($_REQUEST['event_admin_reports']) {
		
		case'event_list_attendees':
		case'edit_attendee_record':
		case'resend_email':
		case'enter_attendee_payments':
		case'list_attendee_payments':
		case'add_new_attendee':
		case'charts':
		
			 espresso_attendees_by_month_dropdown(isset($_REQUEST['month_range']) ? $_REQUEST['month_range'] : ''); //echo $_POST[ 'month_range' ];  ?>
			<input type="submit" class="button-secondary" value="Filter Month" id="post-query-month">
			<?php if( espresso_category_dropdown(isset($_REQUEST['category_id']) ? $_REQUEST['category_id'] : '')){ ?>
				<input type="submit" class="button-secondary" value="Filter Category" id="post-query-category">
			<?php }
			//Payment status drop down
			$status = array(
				array('id' => '', 'text' => __('Show All Completed/Incomplete', 'event_espresso')), 
				array('id' => 'Completed', 'text' => __('Completed', 'event_espresso')), 
				array('id' => 'Pending', 'text' => __('Pending', 'event_espresso')), 
				array('id' => 'Incomplete', 'text' => __('Incomplete', 'event_espresso')), 
				array('id' => 'Payment Declined', 'text' => __('Payment Declined', 'event_espresso')), 
				array('id' => 'Refund', 'text' => __('Refund', 'event_espresso'))
			);

			echo select_input('payment_status', $status, isset($_REQUEST['payment_status']) ? $_REQUEST['payment_status'] : '');
			?>
			<input type="submit" class="button-secondary" value="Filter Status" id="post-query-payment">
			<a class="button-secondary" href="admin.php?page=events&event_admin_reports=event_list_attendees" style=" width:40px; display:inline">
				<?php _e('Reset Filters', 'event_espresso'); ?>
			</a>
			<?php
			break;

		default:
			?>
			<?php
			$_REQUEST['month_range'] = isset($_REQUEST['month_range']) ? $_REQUEST['month_range'] : '';
			espresso_event_months_dropdown($_REQUEST['month_range']); //echo $_POST[ 'month_range' ];  
			?>
			<input type="submit" class="button-secondary" value="Filter Month" id="post-query-submit">
			<?php
			$_REQUEST['category_id'] = isset($_REQUEST['category_id']) ? $_REQUEST['category_id'] : '';
			if ( espresso_category_dropdown($_REQUEST['category_id'])){
			?>
				<input type="submit" class="button-secondary" value="Filter Category" id="post-query-submit">
			<?php }?>
			<?php
			$status = array(
				array('id' => 'A', 'text' 	=> __('Active', 'event_espresso')), 
				array('id' => 'L', 'text'		=> __('Show Active/Inactive', 'event_espresso')), 
				array('id' => 'IA', 'text' 	=> __('Inactive', 'event_espresso')), 
				array('id' => 'P', 'text' 	=> __('Pending', 'event_espresso')), 
				array('id' => 'R', 'text' 	=> __('Draft', 'event_espresso')), 
				array('id' => 'S', 'text' 	=> __('Waitlist', 'event_espresso')), 
				array('id' => 'O', 'text' 	=> __('Ongoing', 'event_espresso')), 
				array('id' => 'X', 'text' 	=> __('Denied', 'event_espresso')), 
				array('id' => 'D', 'text' 	=> __('Deleted', 'event_espresso'))
			);

			if (empty($_REQUEST['event_status'])) {
				$_REQUEST['event_status'] = '';
			}

			echo select_input('event_status', $status, $_REQUEST['event_status']);
			?>
			<input type="submit" class="button-secondary" value="Filter Status" id="post-query-submit">
			<a class="button-secondary" href="admin.php?page=events" style=" width:40px; display:inline">
			<?php _e('Reset Filters', 'event_espresso'); ?>
			</a>
			<?php
			break;
	}
	
}	






function espresso_display_ee_pagination ( $total = 0 ) {	
	
	$max_rows = isset($_REQUEST['max_rows']) ? $_REQUEST['max_rows'] : 50;
	$start_rec = isset($_REQUEST['start_rec']) && !empty($_REQUEST['start_rec']) ? absint($_REQUEST['start_rec']) : 0;
	?>

	<div id='ee_table_pagination'>
		<button class='button-secondary'><?php _e("Retrieve",'event_espresso')?></button>
			<?php $rows = array(5=>5, 50 => 50, 100 => 100, 250 => 250, 500 => 500, 100000 => 'all'); ?>
		<select name="max_rows" size="1">
			<?php foreach ($rows as $key => $value) { ?>
				<?php $selected = $key == $max_rows ? ' selected="selected"' : ''; ?>
				<option value="<?php echo $key ?>"<?php echo $selected ?>><?php echo $value ?>&nbsp;&nbsp;</option>
		<?php } ?>
		</select>		
		<?php _e('rows from the Database at a time', 'event_espresso'); ?>
		<input name="start_rec" id='event-list-start-rec' value="<?php echo $start_rec ?>" class="textfield" type="hidden">
		<?php
		$prev_start_rec = $start_rec - $max_rows;
		$next_start_rec = $start_rec + $max_rows;
		
		//show db-level pagination buttons
		if ( $prev_start_rec >= 0 ) {
			?>
			<a id="event-admin-load-prev-rows-btn" title="load prev rows" class="event-pagination-button button-secondary">
				<?php echo __('Previous', 'event_espresso') . ' ' . $max_rows . ' ' . __('rows', 'event_espresso'); ?>
			</a>
		<?php
		} 

		if ( $total >= $max_rows) {
		?>	
			<a  id="event-admin-load-next-rows-btn"  title="load next rows" class="event-pagination-button button-secondary">
				<?php echo __('Next', 'event_espresso') . ' ' . $max_rows . ' ' . __('rows', 'event_espresso'); ?>				
			</a> 
		<?php } ?>
		
	</div>
	
	<?php
//	echo '<h4>$max_rows : ' . $max_rows . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//	echo '<h4>$total : ' . $total . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
	//setup jquery for previous and next buttons, 
	//but don't output them here.
	//we actually don't know if we want them both yet anyways
	//because don't know how many records are in the curren tquery, because
	//the query hasn't been run yet. (Hence why its nice to setup all your variables FIRST and then start outputting
	//although sometimes it requires some work and discipline
	//anyways, to output the previous/next buttons, just have some HTML like
	//<a id='event-admin-load-prev-rows-btn'>Previous</a>
	//<a id='event-admin-load-next-rows-btn'>Previous</a>
	//and it can be placed anywher ein the page
	?>
	<script>
		jQuery(document).ready(function(){
			jQuery('#event-admin-load-prev-rows-btn').click(function(e){
				e.preventDefault();
				event_set_start_rec_and_send_form(<?php echo $prev_start_rec ?>);
			});
			jQuery('#event-admin-load-next-rows-btn').click(function(e){
				e.preventDefault();
				event_set_start_rec_and_send_form(<?php echo $next_start_rec ?>);
			});
			jQuery('#ee_tablenav input').click(function(){
				event_reset_start_rec();
			});			
			jQuery('#ee_table_pagination button').click(function(){
				event_reset_start_rec();
			});
			
		});
		function event_set_start_rec_and_send_form(value){
			jQuery('#event-list-start-rec').val(value);
			jQuery('#ee_tablenav').submit();
		}
		function event_reset_start_rec(){
			jQuery('#event-list-start-rec').val(0);
		}
	</script>
	<?php	
}




function espresso_hidden_form_input( $name = FALSE, $value = '' ) {
	return '<input type="hidden" name="'.$name.'" value="'.$value.'">';
}