<?php
global $espresso_premium;
if ($espresso_premium != true) {
	return;
}





/**
 * 	espresso_retrieve_filter_query_args
 * 	generates a list of current query args and removes empty elements
 *
 *  @access public
 *  array $var
 *  string
 */
function espresso_retrieve_filter_query_args() {
	// for deez filtaz ta be da shiltaz, dey haz ta function in conjunction wit da othaz
	// so let's grab the existing query string
	$query_string = isset( $_SERVER['QUERY_STRING'] ) ? wp_strip_all_tags( $_SERVER['QUERY_STRING'] ) : array();
	// and convert it's parameters into an array of query args
	parse_str( $query_string, $query_args );
	// and now let's remove any empty parameters
	$query_args = array_filter( $query_args );
	return $query_args;
}





/**
 * 	espresso_remove_filter_from_query_args
 * 	removes event list filter elements from list of query args
 *
 *  @access public
 *  @param array $query_args 
 *  @return array
 */
function espresso_remove_filter_from_query_args( $filter_params = array(), $query_args = FALSE ) {	
	// no query args ? well then let's get some
	$query_args = $query_args ? $query_args : espresso_retrieve_filter_query_args();
	// check that $filter_params is an array
	$filter_params = is_array( $filter_params ) ? $filter_params : array( $filter_params );
	// then loop thru the $query_args to remove them
	foreach ( $filter_params as $filter_param ) {
		if ( isset( $query_args[ $filter_param ] )) {
			unset( $query_args[ $filter_param ] );
		}
	}
	return $query_args;
}





/**
 * 	espresso_display_month_category_status_filters
 * 	generates links for filtering the espresso admin event/attendee list
 *
 *  @access public
 *  @return string
 */
//function espresso_display_month_category_status_filters() {
function espresso_display_admin_reports_filters( $total = 0 ) {

	$event_admin_reports = isset($_REQUEST['event_admin_reports']) ? wp_strip_all_tags( $_REQUEST['event_admin_reports'] ) : FALSE;
	$event_id = isset( $_REQUEST['event_id'] ) && $_REQUEST['event_id'] != '' ? absint( $_REQUEST['event_id'] ) : FALSE;
	$category_id =  isset($_REQUEST['category_id']) ? absint( $_REQUEST['category_id'] ) : FALSE;
	$payment_status = isset($_REQUEST['payment_status']) ? wp_strip_all_tags( $_REQUEST['payment_status'] ) : '';
	$event_status = isset($_REQUEST['event_status']) ? wp_strip_all_tags( $_REQUEST['event_status'] ) : '';
	$month_range = isset($_REQUEST['month_range']) ? wp_strip_all_tags( $_REQUEST['month_range'] ) : '';

	// then figure out the current "view"
	$view = isset( $_REQUEST['all'] ) && $_REQUEST['all'] == 'true' ? 'all' : '';
	$view = isset($_REQUEST['this_month']) && $_REQUEST['this_month'] == 'true' ? 'this_month' : $view;
	$view = isset($_REQUEST['today']) && $_REQUEST['today'] == 'true' ? 'today' : $view;
	$view = isset( $_REQUEST['all_a'] ) && $_REQUEST['all_a'] == 'true' ? 'all_a' : $view;
	$view = isset($_REQUEST['this_month_a']) && $_REQUEST['this_month_a'] == 'true' ? 'this_month_a' : $view;
	$view = isset($_REQUEST['today_a']) && $_REQUEST['today_a'] == 'true' ? 'today_a' : $view;
	
	
	// query params we need to remove
	$remove = array( $view, 'event_admin_reports', 'event_id' );
	// if a specific date period has been clicked
	if ( ! empty( $view )) {
		$remove[] = 'month_range';
		$month_range = '';
	}
		
	// then remove the current view from the query args so that the view can be changed
	$query_args = espresso_remove_filter_from_query_args( $remove );
	//printr( $query_args, '$query_args  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );

	// EVT_ADMIN_URL
	?> 
	  <h3 style="margin-bottom: -8px;">
		  <?php echo __('Filters', 'event_espresso'); ?>
	  </h3>
	 
	  <ul class="subsubsub">
	  	<li>
		  	<strong>
	  			<?php _e('Events', 'event_espresso'); ?> :
	  		</strong>
	  	</li>
	  	<li>	  		
		  	<a <?php echo $view == 'all' ? ' class="current" ' : '' ?> href="<?php echo add_query_arg( array_merge( array( 'all' => 'true', 'max_rows' => 100000 ), $query_args ), EVT_ADMIN_URL ); ?>">
	  			<?php _e('All Events', 'event_espresso'); ?> <span class="count">(<?php echo espresso_total_events(); ?>)</span>
	  		</a> |
	  	</li>
	  	<li>
		  	<a <?php echo $view == 'this_month' ? ' class="current" ' : '' ?>  href="<?php echo add_query_arg( array_merge( array( 'this_month' => 'true' ), $query_args ), EVT_ADMIN_URL ); ?>">
	  			<?php _e('This Month', 'event_espresso'); ?> <span class="count">(<?php echo espresso_total_events_this_month(); ?>)</span>
	  		</a>
	  	</li>
	  	<li>| 
		  	<a <?php echo $view == 'today' ? ' class="current" ' : '' ?> href="<?php echo add_query_arg( array_merge( array( 'today' => 'true' ), $query_args ), EVT_ADMIN_URL ); ?>">
	  			<?php _e('Today', 'event_espresso'); ?> <span class="count">(<?php echo espresso_total_events_today(); ?>)</span>
	  		</a>
	  	</li>
	  </ul>
	 
	  <ul class="subsubsub">
	  	<li>
		  	<strong>
	  			<?php _e('Attendees', 'event_espresso'); ?> :
	  		</strong>
	  	</li>
	  	<li>
		  	<a <?php echo $view == 'all_a' ? ' class="current" ' : '' ?> href="<?php echo add_query_arg( array_merge( array( 'event_admin_reports' => 'event_list_attendees', 'all_a' => 'true', 'max_rows' => 100000 ), $query_args ), EVT_ADMIN_URL ); ?>">
	  			<?php _e('All Attendees', 'event_espresso'); ?>
	  			<span class="count">
		  			(<?php echo espresso_total_all_attendees(); ?>)
	  			</span>
	  		</a> |
	  	</li>
	  	<li>
		  	<a <?php echo $view == 'this_month_a' ? ' class="current" ' : '' ?>  href="<?php echo add_query_arg( array_merge( array( 'event_admin_reports' => 'event_list_attendees', 'this_month_a' => 'true' ), $query_args ), EVT_ADMIN_URL ); ?>">
	  			<?php _e('This Month', 'event_espresso'); ?>
	  			<span class="count">
		  			(<?php echo espresso_total_attendees_this_month(); ?>)
	  			</span>
	  		</a>
	  	</li>
	  	<li> | 
		  	<a <?php echo $view == 'today_a' ? ' class="current" ' : '' ?> href="<?php echo add_query_arg( array_merge( array( 'event_admin_reports' => 'event_list_attendees', 'today_a' => 'true' ), $query_args ), EVT_ADMIN_URL ); ?>">
	  			<?php _e('Today', 'event_espresso'); ?>
	  			<span class="count">
		  			(<?php echo espresso_total_attendees_today(); ?>)
	  			</span>
	  		</a>
	  	</li>
	  	<?php
	  	if ( $event_id && ( ! $event_admin_reports || $event_admin_reports != 'charts')) {
		  	?>
	  		<li>
		  		|
	  			<a href="<?php echo add_query_arg( array_merge( array( 'event_admin_reports' => 'charts', 'event_id' => $event_id ), $query_args ), EVT_ADMIN_URL ); ?>">
	  				<?php _e('View Report', 'event_espresso'); ?>
	  			</a>
	  		</li>
	  		<?php
	  	} ?>
	  </ul>
	 
	<div style="clear:both"></div>

	<div class="ee_tablenav tablenav">
		<div class="actions">
			<form id="ee_tablenav" name="ee_tablenav" method="get" action="<?php echo EVT_ADMIN_URL; ?>">	
	<?php

	
	// what page are we doing filtering for ?
	if ( $event_admin_reports ) {
		
		// add event_admin_reports back into query args
		$query_args['event_admin_reports'] = $event_admin_reports;
		
		// Filter Month
		espresso_attendees_by_month_dropdown( $month_range ); ?>
		<input type="submit" class="button-secondary" value="Filter Month" id="post-query-month"/> &nbsp; &nbsp; 
		
		<?php 
		// Filter Category
		// when viewing the attendee list for a specific event we no longer need or would want the category filter	
		if ( ! $event_id ) {
			if( espresso_category_dropdown( $category_id ) ){ ?>
			<input type="submit" class="button-secondary" value="Filter Category" id="post-query-category"/> &nbsp; &nbsp; 			
			<?php }
		}
		
		// Filter Payment Status
		$status = array(
			array('id' => '', 'text' => __('Show All Completed/Incomplete', 'event_espresso')), 
			array('id' => 'Completed', 'text' => __('Completed', 'event_espresso')), 
			array('id' => 'Pending', 'text' => __('Pending', 'event_espresso')), 
			array('id' => 'Incomplete', 'text' => __('Incomplete', 'event_espresso')), 
			array('id' => 'Payment Declined', 'text' => __('Payment Declined', 'event_espresso')), 
			array('id' => 'Cancelled','text' => __('Cancelled','event_espresso')),
			array('id' => 'Refund', 'text' => __('Refund', 'event_espresso'))
		);
		echo select_input( 'payment_status', $status, $payment_status );
		?>
		<input type="submit" class="button-secondary" value="Filter Status" id="post-query-payment"/>
		
		<a class="button-secondary" href="admin.php?page=events&event_admin_reports=event_list_attendees">
			<?php _e('Reset Filters', 'event_espresso'); ?>
		</a>
		<?php

 	} else { 

		espresso_event_months_dropdown( $month_range ); 
		?>
		<input type="submit" class="button-secondary" value="Filter Month" id="post-query-submit-month"/>
		<?php
		if ( espresso_category_dropdown( $category_id )){
		?>
			<input type="submit" class="button-secondary" value="Filter Category" id="post-query-submit-category"/>
		<?php }?>
		<?php
		$status = array(
			array('id' => 'A', 'text' 	=> __('Active / Ongoing', 'event_espresso')), 
			array('id' => 'L', 'text'	=> __('ALL ( Active / Inactive )', 'event_espresso')), 
			array('id' => 'IA', 'text' 	=> __('Inactive', 'event_espresso')), 
			array('id' => 'P', 'text' 	=> __('Pending', 'event_espresso')), 
			array('id' => 'R', 'text' 	=> __('Draft', 'event_espresso')), 
			array('id' => 'S', 'text' 	=> __('Waitlist', 'event_espresso')), 
			array('id' => 'O', 'text' 	=> __('Ongoing', 'event_espresso')), 
			array('id' => 'X', 'text' 	=> __('Denied', 'event_espresso')), 
			array('id' => 'D', 'text' 	=> __('Deleted', 'event_espresso'))
		);

		echo select_input( 'event_status', $status, $event_status );
		?>
		<input type="submit" class="button-secondary" value="Filter Status" id="post-query-submit-event-status"/>
		
		<a class="button-secondary" href="admin.php?page=events">
		<?php _e('Reset Filters', 'event_espresso'); ?>
		</a>
		<?php

	}

	// query params we need to remove now
	$remove = array( 'month_range', 'category_id', 'event_status', 'payment_status' );
	// then remove the current view from the query args so that the view can be changed
	$query_args = espresso_remove_filter_from_query_args( $remove );

	// add view  back into query args
	if ( ! empty( $view )) {
		$query_args[ $view ] = 'true';
	}
	
	//because we're doing a get request, we'll need to explicitly preserve the old GET querystring
	foreach ( $query_args as $query_arg => $value ) {
		echo espresso_hidden_form_input( $query_arg, $value );
	}
	

	$max_rows = isset( $_REQUEST['max_rows'] ) ? absint( $_REQUEST['max_rows'] ) : 50;
	$max_rows = min( $max_rows, 100000 );
	$start_rec = isset( $_REQUEST['start_rec'] ) && ! empty( $_REQUEST['start_rec'] ) ? absint( $_REQUEST['start_rec'] ) : 0;
	$rows = array( 5=>5, 50 => 50, 100 => 100, 250 => 250, 500 => 500, 100000 => 'all' );
	$prev_start_rec = $start_rec - $max_rows;
	$next_start_rec = $start_rec + $max_rows;
	?>

				<div id='ee_table_pagination'>
					<button class='button-secondary'><?php _e("Retrieve",'event_espresso')?></button>		
					<select name="max_rows" size="1">
						<?php foreach ( $rows as $key => $value ) { ?>
							<?php $selected = $key == $max_rows ? ' selected="selected"' : ''; ?>
							<option value="<?php echo $key ?>"<?php echo $selected ?>><?php echo $value ?>&nbsp;&nbsp;</option>
					<?php } ?>
					</select>		
					<?php _e('rows from the Database at a time', 'event_espresso'); ?>
					
					<input name="start_rec" id='event-list-start-rec' value="<?php echo $start_rec ?>" class="textfield" type="hidden">
					<?php
					
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

			</form>
		</div>
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
		jQuery(document).ready(function( $ ){
			$('#event-admin-load-prev-rows-btn').click(function(e){
				e.preventDefault();
				event_set_start_rec_and_send_form(<?php echo $prev_start_rec ?>);
			});
			$('#event-admin-load-next-rows-btn').click(function(e){
				e.preventDefault();
				event_set_start_rec_and_send_form(<?php echo $next_start_rec ?>);
			});
			$('#ee_tablenav input').click(function(){
				event_reset_start_rec();
			});			
			$('#ee_table_pagination button').click(function(){
				event_reset_start_rec();
			});
			
			function event_set_start_rec_and_send_form(value){ 
				event_reset_view();
				$('#event-list-start-rec').val(value);
				$('#ee_tablenav').submit();
			}
			
			function event_reset_start_rec(){
				event_reset_view();
				$('#event-list-start-rec').val(0);
			}
			
			function event_reset_view(){
				var month_range = $('select[name="month_range"]').val();
				if( month_range != '' ) {
					$('input[name="<?php echo $view ?>"]').val('');
					$('input[name="<?php echo $view ?>"]').remove();
				}
			}
			
		});
	</script>
	<?php	
}




function espresso_hidden_form_input( $name = FALSE, $value = '' ) {
	return '<input type="hidden" name="'.$name.'" value="'.$value.'">';
}