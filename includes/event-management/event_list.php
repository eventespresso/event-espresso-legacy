<?php
if (!defined('EVENT_ESPRESSO_VERSION'))
	exit('No direct script access allowed');

function event_espresso_edit_list() {

	global $wpdb, $org_options;

	define( 'EVT_ADMIN_URL', admin_url( 'admin.php?page=events' ));

	$max_rows = isset( $_REQUEST['max_rows'] ) & ! empty( $_REQUEST['max_rows'] ) ? absint( $_REQUEST['max_rows'] ) : 50;
	$start_rec = isset( $_REQUEST['start_rec'] ) && ! empty($_REQUEST['start_rec']) ? absint( $_REQUEST['start_rec'] ) : 0;
	$records_to_show = " LIMIT $max_rows OFFSET $start_rec ";

	//Dates
	$curdate = date('Y-m-d');
	$this_year_r = date('Y');
	$this_month_r = date('m');
	$days_this_month = date('t', strtotime($curdate));
	
	$month_range = isset( $_REQUEST['month_range'] ) && ! empty( $_REQUEST['month_range'] ) ? sanitize_text_field( $_REQUEST['month_range'] ) : FALSE;
	$category_id = isset( $_REQUEST['category_id'] ) && ! empty( $_REQUEST['category_id'] ) ? sanitize_text_field( $_REQUEST['category_id'] ) : FALSE;
	$today_filter = isset( $_REQUEST['today'] ) && $_REQUEST['today'] == 'true' ? TRUE : FALSE;
	$this_month_filter = isset( $_REQUEST['this_month'] ) && $_REQUEST['this_month'] == 'true' ? TRUE : FALSE;
	$event_status = isset($_POST['event_status']) && ! empty( $_POST['event_status'] )  ? sanitize_text_field( $_REQUEST['event_status'] ) : FALSE;

	if (isset($_POST['delete_event'])) {
		if (is_array($_POST['checkbox'])) {
			while (list($key, $value) = each($_POST['checkbox'])):
				$del_id = $key;
				event_espresso_delete_event($del_id);
			endwhile;
		}
		?>
		<div id="message" class="updated fade">
			<p><strong>
					<?php _e('Event(s) have been permanently deleted.', 'event_espresso'); ?>
				</strong></p>
		</div>
		<?php
	}
	if (isset($_POST['perm_delete_event'])) {
		if (is_array($_POST['checkbox'])) {
			while (list($key, $value) = each($_POST['checkbox'])):
				$del_id = $key;
				event_espresso_empty_event_trash($del_id);
			endwhile;
		}
		?>

		<div id="message" class="updated fade">
			<p><strong>
					<?php _e('Event(s) have been permanently deleted.', 'event_espresso'); ?>
				</strong></p>
		</div>
		<?php
	}

	$recurrence_icon = '';
	if (defined('EVENT_ESPRESSO_RECURRENCE_MODULE_ACTIVE')) {
		$recurrence_icon = '<img src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/arrow_rotate_clockwise.png" alt="Recurring Event" title="Recurring Event" class="re_fr" />';
	}

	require_once('queries.php');

	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/admin_reports_filters.php')) {
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/admin_reports_filters.php');
	} else {
		echo '<p><strong>' . __('Advanced filters are now available in the premium versions.', 'event_espresso') . '</strong> <a href="http://eventespresso.com/download/" target="_blank">' . __('Upgrade Now!', 'event_espresso') . '</a></p>';
		//$total_events = espresso_total_events();
	}


	if ( $month_range !== FALSE ) {
		$pieces = explode('-', $month_range, 3);
		$year_r = $pieces[0];
		$month_r = $pieces[1];
	}
	
	$group = '';
	$sql = '';
	
	//Check if the venue manager is turned on
	$use_venue_manager = isset( $org_options['use_venue_manager'] ) && $org_options['use_venue_manager'] == 'Y' ? TRUE : FALSE;
	$is_regional_manager = FALSE;
	
	//This checks to see if the user is a regional manager and creates a union to join the events that are in the users region based on the venue/locale combination
	if (function_exists('espresso_member_data') && espresso_member_data('role') == 'espresso_group_admin') {
	
		$is_regional_manager = TRUE;
		
		$group = get_user_meta(espresso_member_data('id'), "espresso_group", TRUE);
		if ( $group != '0' && !empty($group) ){
			
			$sql = "(SELECT e.id event_id, e.event_name, e.event_identifier, e.reg_limit, e.registration_start, ";
			$sql .= " e.start_date, e.is_active, e.recurrence_id, e.registration_startT, e.wp_user ";
			
			//Get the venue information
			if ( $use_venue_manager ) {
				$sql .= ", v.name AS venue_title, v.address AS venue_address, v.address2 AS venue_address2, v.city AS venue_city, v.state AS venue_state, v.zip AS venue_zip, v.country AS venue_country ";
			} else {
				$sql .= ", e.venue_title, e.phone, e.address, e.address2, e.city, e.state, e.zip, e.country ";
			}
			
			//Get the locale fields
			if ( $use_venue_manager ) {
				$sql .= ", lc.name AS locale_name, e.wp_user ";
			}
			
			$sql .= " FROM " . EVENTS_DETAIL_TABLE . " e ";
			
			//Join the categories
			if ( $today_filter ) {
				$sql .= " JOIN " . EVENTS_CATEGORY_REL_TABLE . " cr ON cr.event_id = e.id ";
				$sql .= " JOIN " . EVENTS_CATEGORY_TABLE . " c ON  c.id = cr.cat_id ";
			}
			
			//Join the venues and locales
			if ( ! empty( $group ) && $use_venue_manager ) {
				$sql .= " LEFT JOIN " . EVENTS_VENUE_REL_TABLE . " vr ON vr.event_id = e.id ";
				$sql .= " LEFT JOIN " . EVENTS_VENUE_TABLE . " v ON v.id = vr.venue_id ";
				$sql .= " LEFT JOIN " . EVENTS_LOCALE_REL_TABLE . " l ON  l.venue_id = vr.venue_id ";
				$sql .= " LEFT JOIN " . EVENTS_LOCALE_TABLE . " lc ON lc.id = l.locale_id ";
			}
			
			//Event status filter
			$sql .= $event_status !== FALSE && $event_status != 'IA' ? " WHERE e.event_status = '" . $event_status . "' " : " WHERE e.event_status != 'D' ";
			
			//Category filter
			$sql .= $category_id !== FALSE ? " AND c.id = '" . $category_id . "' " : '';
			
			//Find events in the locale
			$sql .= !empty($group) && $use_venue_manager == true ? " AND l.locale_id IN (" . implode(",", $group) . ") " : '';
			
			//Month filter
			if ( $month_range !== FALSE ) {
				$sql .= " AND e.start_date BETWEEN '" . date('Y-m-d', strtotime($year_r . '-' . $month_r . '-01')) . "' AND '" . date('Y-m-d', strtotime($year_r . '-' . $month_r . '-31')) . "' ";
			}
			
			//Todays events filter
			if ( $today_filter ) {
				$sql .= " AND e.start_date = '" . $curdate . "' ";
			}
			
			//This months events filter
			if ( $this_month_filter ) {
				$sql .= " AND e.start_date BETWEEN '" . date('Y-m-d', strtotime($this_year_r . '-' . $this_month_r . '-01')) . "' AND '" . date('Y-m-d', strtotime($this_year_r . '-' . $this_month_r . '-' . $days_this_month)) . "' ";
			}
			$sql .= ") UNION ";
		}
	}
	
		
	
	//This is the standard query to retrieve the events
	$sql .= "(SELECT e.id event_id, e.event_name, e.event_identifier, e.reg_limit, e.registration_start, ";
	$sql .= " e.start_date, e.is_active, e.recurrence_id, e.registration_startT, e.wp_user ";

	//Get the venue information
	if ( $use_venue_manager ) {
		//If using the venue manager, we need to get those fields
		$sql .= ", v.name AS venue_title, v.address AS venue_address, v.address2 AS venue_address2, v.city AS venue_city, v.state AS venue_state, v.zip AS venue_zip, v.country AS venue_country ";
	} else {
		//Otherwise we need to get the address fields from the individual events
		$sql .= ", e.venue_title, e.phone, e.address, e.address2, e.city, e.state, e.zip, e.country ";
	}
	
	//get the locale fields
	if ( $is_regional_manager && $use_venue_manager ) {
		$sql .= ", lc.name AS locale_name, e.wp_user ";
	}
	
	$sql .= " FROM " . EVENTS_DETAIL_TABLE . " e ";
	
	//Join the categories
	if ( $category_id != FALSE ) {
		$sql .= " JOIN " . EVENTS_CATEGORY_REL_TABLE . " cr ON cr.event_id = e.id ";
		$sql .= " JOIN " . EVENTS_CATEGORY_TABLE . " c ON  c.id = cr.cat_id ";
	}

	//Join the venues
	if ($use_venue_manager == true) {
		$sql .= " LEFT JOIN " . EVENTS_VENUE_REL_TABLE . " vr ON vr.event_id = e.id ";
		$sql .= " LEFT JOIN " . EVENTS_VENUE_TABLE . " v ON v.id = vr.venue_id ";
	}
	
	//Join the locales
	if (isset($is_regional_manager) && $is_regional_manager == true && $use_venue_manager == true) {
		$sql .= " LEFT JOIN " . EVENTS_LOCALE_REL_TABLE . " l ON  l.venue_id = vr.venue_id ";
		$sql .= " LEFT JOIN " . EVENTS_LOCALE_TABLE . " lc ON lc.id = l.locale_id ";
	}
	
	//Event status filter
	$sql .= ( isset($_POST['event_status']) && ($_POST['event_status'] != '' && $_POST['event_status'] != 'IA')) ? " WHERE e.event_status = '" . $_POST['event_status'] . "' " : " WHERE e.event_status != 'D' ";
	
	//Category filter
	$sql .= $category_id !== FALSE ? " AND c.id = '" . $category_id . "' " : '';
	
	//Month filter
	if (isset($_POST['month_range']) && !empty($_POST['month_range']) ? $_POST['month_range'] : '') {
		$sql .= " AND e.start_date BETWEEN '" . date('Y-m-d', strtotime($year_r . '-' . $month_r . '-01')) . "' AND '" . date('Y-m-d', strtotime($year_r . '-' . $month_r . '-31')) . "' ";
	}
	
	//Todays events filter
	if (isset($_REQUEST['today']) && $_REQUEST['today'] == 'true') {
		$sql .= " AND e.start_date = '" . $curdate . "' ";
	}
	
	//This months events filter
	if (isset($_REQUEST['this_month']) && $_REQUEST['this_month'] == 'true') {
		$sql .= " AND e.start_date BETWEEN '" . date('Y-m-d', strtotime($this_year_r . '-' . $this_month_r . '-01')) . "' AND '" . date('Y-m-d', strtotime($this_year_r . '-' . $this_month_r . '-' . $days_this_month)) . "' ";
	}
	
	//If user is an event manager, then show only their events
	if (function_exists('espresso_member_data') && ( espresso_member_data('role') == 'espresso_event_manager' || espresso_member_data('role') == 'espresso_group_admin')) {
		$sql .= " AND e.wp_user = '" . espresso_member_data('id') . "' ";
	}
	
	$sql .= ") ORDER BY start_date DESC ";
    $sql .= $records_to_show;
	
	$events = $wpdb->get_results($sql);
	$total_events = $wpdb->num_rows;
	
?>

	<form id="event-admin-list-page-select-frm" name="event_admin_list_page_select_frm" method="post" action="<?php echo EVT_ADMIN_URL; ?>">
		<div id="event-admin-list-page-select-dv" class="admin-list-page-select-dv">
			<input name="navig" value="<?php _e('Retrieve', 'event_espresso'); ?>" type="submit" class="button-secondary">
			<?php $rows = array( 50 => 50, 100 => 100, 250 => 250, 500 => 500, 100000 => 'all' ); ?>
			<select name="max_rows" size="1">
				<?php foreach ( $rows as $key => $value ) { ?>
				<?php $selected = $key == $max_rows ? ' selected="selected"' : ''; ?>
				<option value="<?php echo $key ?>"<?php echo $selected ?>><?php echo $value ?>&nbsp;&nbsp;</option>
				<?php } ?>
			</select>		
			<?php _e('rows from the db at a time', 'event_espresso'); ?>
			<input name="start_rec" value="<?php echo $start_rec ?>" class="textfield" type="hidden">
			<?php
				if ( $start_rec > 0 && $max_rows < 100000 ) {
					$prev_rows = $start_rec > $max_rows ? ( $start_rec - $max_rows ) : 0;
					$prev_rows_url = add_query_arg( array( 'max_rows' => $max_rows, 'start_rec' => $prev_rows ), EVT_ADMIN_URL ); 
			?>
			<a id="event-admin-load-prev-rows-btn" href="<?php echo $prev_rows_url; ?>" title="load prev rows" class="button-secondary">
				<?php echo __('Previous', 'event_espresso') . ' ' . $max_rows  . ' ' .  __('rows', 'event_espresso'); ?>
			</a>
			<?php } ?>
			<?php 			
				if ( $total_events >= $max_rows && $max_rows < 100000 ) {
					$next_rows = $start_rec + $max_rows;
					$next_rows_url = add_query_arg( array( 'max_rows' => $max_rows, 'start_rec' => $next_rows ), EVT_ADMIN_URL ); 
			?>
			<a id="event-admin-load-next-rows-btn" href="<?php echo $next_rows_url; ?>" title="load next rows" class="button-secondary">
			<?php echo __('Next', 'event_espresso') . ' ' . $max_rows  . ' ' .  __('rows', 'event_espresso'); ?>
			</a> 
			<?php } ?>
		</div>
	</form>

	<form id="form1" name="form1" method="post" action="admin.php?page=events<?php //echo $_SERVER["REQUEST_URI"] ?>">
		<table id="table" class="widefat event-list" width="100%">
			<thead>
				<tr>
					<th class="manage-column column-cb check-column" id="cb" scope="col" style="width:28px;"><input type="checkbox"></th>

					<th class="manage-column column-comments num" id="id" style="padding-top:7px; width:3%;" scope="col" title="Click to Sort">
						<span><?php _e('ID', 'event_espresso'); ?></span>
						<span class="sorting-indicator"></span>
					</th>

					<th class="manage-column column-title" id="name" scope="col" title="Click to Sort" style="width:26%;">
						<span><?php _e('Name', 'event_espresso'); ?></span>
						<span class="sorting-indicator"></span>
					</th>

					<th class="manage-column column-title" id="name" scope="col" title="Click to Sort" style="width:12%;">
						<span><?php _e('Venue', 'event_espresso'); ?></span>
						<span class="sorting-indicator"></span>
					</th>

					<th class="manage-column column-author" id="start" scope="col" title="Click to Sort" style="width:12%;">
						<span><?php _e('Start Date', 'event_espresso'); ?></span>
						<span class="sorting-indicator"></span>
					</th>

					<th class="manage-column column-author" id="start" scope="col" title="Click to Sort" style="width:10%;">
						<span><?php _e('Start Time', 'event_espresso'); ?></span>
						<span class="sorting-indicator"></span>
					</th>

					<th class="manage-column column-date" id="dow" scope="col" title="Click to Sort" style="width:6%;";>
							<span><?php _e('DoW', 'event_espresso'); ?></span>
						<span class="sorting-indicator"></span>
					</th>

					<th class="manage-column column-date" id="begins" scope="col" title="Click to Sort" style="width:12%;">
						<span><?php _e('Reg Begins', 'event_espresso'); ?></span>
						<span class="sorting-indicator"></span>
					</th>

					<th class="manage-column column-date" id="status" scope="col" title="Click to Sort" style="width:10%;">
						<span><?php _e('Status', 'event_espresso'); ?></span>
						<span class="sorting-indicator"></span>
					</th>
					<?php if (function_exists('espresso_is_admin') && espresso_is_admin() == true && $espresso_premium == true) { ?>
						<th class="manage-column column-date" id="status" scope="col" title="Click to Sort" style="width:10%;">
							<span><?php _e('Creator', 'event_espresso'); ?></span>
							<span class="sorting-indicator"></span>
						</th>
					<?php } ?>
					<th class="manage-column column-date" id="attendees" scope="col" title="Click to Sort" style="width:9%;">
						<span><?php _e('Attendees', 'event_espresso'); ?></span>
						<span class="sorting-indicator"></span>
					</th>
					<th class="manage-column column-author" id="actions" scope="col" style="width:25%;">
						<?php _e('Actions', 'event_espresso'); ?>
					</th>

				</tr>
			</thead>

			<tbody>
				<?php
				if ( $total_events > 0 ) {
					foreach ($events as $event) {
						//print_r ($event);
						$event_id = $event->event_id;
						$event_name = stripslashes_deep($event->event_name);
						$event_identifier = stripslashes_deep($event->event_identifier);
						$reg_limit = isset($event->reg_limit) ? $event->reg_limit : '';
						$registration_start = isset($event->registration_start) ? $event->registration_start : '';
						$start_date = isset($event->start_date) ? $event->start_date : '';
						$end_date = isset($event->end_date) ? $event->end_date : '';
						$is_active = isset($event->is_active) ? $event->is_active : '';
						$status = array();
						$status = event_espresso_get_is_active($event_id);
						$recurrence_id = isset($event->recurrence_id) ? $event->recurrence_id : '';
						$registration_startT = isset($event->registration_startT) ? $event->registration_startT : '';

						$event_address = isset($event->address) ? $event->address : '';
						$event_address2 = isset($event->address2) ? $event->address2 : '';
						$event_city = isset($event->city) ? $event->city : '';
						$event_state = isset($event->state) ? $event->state : '';
						$event_zip = isset($event->zip) ? $event->zip : '';
						$event_country = isset($event->country) ? $event->country : '';
						//added new
						$venue_title = isset($event->venue_title) ? $event->venue_title : '';
						$venue_locale = isset($event->locale_name) ? $event->locale_name : '';
						$wp_user = isset($event->wp_user) ? $event->wp_user : '';


						$location = (!empty($event_address) ? $event_address : '') . (!empty($event_address2) ? '<br />' . $event_address2 : '') . (!empty($event_city) ? '<br />' . $event_city : '') . (!empty($event_state) ? ', ' . $event_state : '') . (!empty($event_zip) ? '<br />' . $event_zip : '') . (!empty($event_country) ? '<br />' . $event_country : '');
						$dow = date("D", strtotime($start_date));
						ob_start();
						?>
						<tr>
							<td class="check-column" style="padding:7px 0 22px 7px; vertical-align:top;"><!--Delete Events-->
								<?php echo '<input name="checkbox[' . $event_id . ']" type="checkbox"  title="Delete Event ' . $event_name . '" />'; ?></td>

							<td class="column-comments" style="padding-top:3px;"><?php echo $event_id ?></td>

							<td class="post-title page-title"><strong><a class="row-title" href="admin.php?page=events&action=edit&event_id=<?php echo $event_id ?>"><?php echo $event_name ?></a> <?php echo ($recurrence_id > 0) ? $recurrence_icon : ''; ?> </strong>
								<div class="row-actions"><span><a href="<?php echo espresso_reg_url($event_id); ?>" target="_blank"><?php _e('View', 'event_espresso'); ?></a> | </span><span class='edit'><a href="admin.php?page=events&amp;action=edit&amp;event_id=<?php echo $event_id ?>"><?php _e('Edit', 'event_espresso'); ?></a> | </span><span class='delete'><a onclick="return confirmDelete();" href='admin.php?page=events&amp;action=delete&amp;event_id=<?php echo $event_id ?>'><?php _e('Delete', 'event_espresso'); ?></a></span> | <span><a href="admin.php?page=events&amp;event_admin_reports=list_attendee_payments&amp;event_id=<?php echo $event_id ?>"><?php _e('Attendees', 'event_espresso'); ?></a> | </span><span><a href="#" onclick="window.location='<?php echo get_bloginfo('wpurl') . "/wp-admin/admin.php?event_espresso&amp;event_id=" . $event_id . "&amp;export=report&action=payment&amp;type=excel"; ?>'" title="<?php _e('Export to Excel', 'event_espresso'); ?>"><?php _e('Export', 'event_espresso'); ?></a></span></div></td>

							<td class="author"><?php echo $venue_title != '' ? $venue_title : '';
					echo $venue_locale != '' ? '<br />[' . $venue_locale . ']' : ''; ?></td>

							<td class="author"><?php echo event_date_display($start_date, get_option('date_format')) ?></td>

							<td class="author"><?php echo event_espresso_get_time($event_id, 'start_time') ?></td>

							<td class="date"><?php echo $dow ?></td>

							<td class="date"><?php echo event_date_display($registration_start, get_option('date_format')); ?> <br />
								<?php echo $registration_startT ?></td>

							<td class="date"><?php echo $status['display'] ?></td>

							<?php
							if (function_exists('espresso_is_admin') && espresso_is_admin() == true && $espresso_premium == true) {
								$user_company = espresso_user_meta($wp_user, 'company') != '' ? espresso_user_meta($wp_user, 'company') : '';
								$user_organization = espresso_user_meta($wp_user, 'organization') != '' ? espresso_user_meta($wp_user, 'organization') : '';
								$user_co_org = $user_company != '' ? $user_company : $user_organization;
								?>
								<td class="date"><?php echo espresso_user_meta($wp_user, 'user_firstname') != '' ? espresso_user_meta($wp_user, 'user_firstname') . ' ' . espresso_user_meta($wp_user, 'user_lastname') . ' (<a href="user-edit.php?user_id='.$wp_user.'">' . espresso_user_meta($wp_user, 'user_nicename'). '</a>)' : espresso_user_meta($wp_user, 'display_name')  . ' (<a href="user-edit.php?user_id='.$wp_user.'">' . espresso_user_meta($wp_user, 'user_nicename'). '</a>)'; ?>
									<?php echo $user_co_org != '' ? '<br />[' . espresso_user_meta($wp_user, 'company') . ']' : ''; ?>
								</td>
							<?php } ?>

							<td class="author"><a href="admin.php?page=events&amp;event_admin_reports=list_attendee_payments&amp;event_id=<?php echo $event_id ?>"><?php echo get_number_of_attendees_reg_limit($event_id, 'num_attendees_slash_reg_limit'); ?></a></td>
							<td class="date"><div style="width:180px;"><a href="<?php echo espresso_reg_url($event_id); ?>" title="<?php _e('View Event', 'event_espresso'); ?>" target="_blank"><div class="view_btn"></div></a>

									<a href="admin.php?page=events&amp;action=edit&amp;event_id=<?php echo $event_id ?>" title="<?php _e('Edit Event', 'event_espresso'); ?>"><div class="edit_btn"></div></a>

									<a href="admin.php?page=events&amp;event_id=<?php echo $event_id ?>&amp;event_admin_reports=list_attendee_payments" title="<?php _e('View Attendees', 'event_espresso'); ?>"><div class="complete_btn"></div></a>
									<a href="admin.php?page=events&event_admin_reports=charts&event_id=<?php echo $event_id ?>" title="<?php _e('View Report', 'event_espresso'); ?>"><div class="reports_btn"></div></a>


									<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=unique_id_info_<?php echo $event_id ?>" title="<?php _e('Get Short URL/Shortcode', 'event_espresso'); ?>"><div class="shortcode_btn"></div></a>

									<a href="#" onclick="window.location='<?php echo get_bloginfo('wpurl') . "/wp-admin/admin.php?event_espresso&amp;event_id=" . $event_id . "&amp;export=report&action=payment&amp;type=excel"; ?>'" title="<?php _e('Export to Excel', 'event_espresso'); ?>"><div class="excel_exp_btn"></div></a>

									<a href="#" onclick="window.location='<?php echo get_bloginfo('wpurl') . "/wp-admin/admin.php?event_espresso&event_id=" . $event_id . "&export=report&action=payment&type=csv"; ?>'" title="<?php _e('Export to CSV', 'event_espresso'); ?>"><div class="csv_exp_btn"></div></a>

									<a href="admin.php?page=events&amp;event_admin_reports=event_newsletter&amp;event_id=<?php echo $event_id ?>" title="<?php _e('Email Attendees', 'event_espresso'); ?>"><div class="newsletter_btn"></div></a></div>

								<div id="unique_id_info_<?php echo $event_id ?>" style="display:none">
									<?php _e('<h2>Short URL/Shortcode</h2><p>This is the short URL to this event:</p><p><span  class="updated fade">' . espresso_reg_url($event_id) . '</span></p><p>This will show the registration form for this event just about anywhere. Copy and paste the following shortcode into any page or post.</p><p><span  class="updated fade">[SINGLEEVENT single_event_id="' . $event_identifier . '"]</span></p> <p class="red_text"> Do not use in place of the main events page that is set in the Organization Settings page.', 'event_espresso'); ?>
								</div></td>
						</tr>
						<?php
						//echo $_REQUEST['event_status'];
						if (!empty($_REQUEST['event_status'])) {
							$content = ob_get_contents();
							ob_end_clean();
							switch ($_REQUEST['event_status']) {
								case 'A':
									switch (event_espresso_get_status($event_id, empty($event_meta) ? '' : $event_meta)) {
										case 'NOT_ACTIVE':
											//Don't show the event if any of the above are true
											break;

										default:
											echo $content;
											break;
									}
									break;
								case 'IA':
									switch (event_espresso_get_status($event_id)) {
										case 'NOT_ACTIVE':
											echo $content;
											break;

										default:
											//Don't show the event if any of the above are true
											break;
									}
									break;
								default:
									echo $content;
									break;
							}
						}
					}//End foreach ($events as $event){
					
				}
?>

			</tbody>
		</table>
		<div style="clear:both; margin-bottom:30px;">
			<input type="checkbox" name="sAll" onclick="selectAll(this)" />
			<strong>
				<?php _e('Check All', 'event_espresso'); ?>
			</strong><?php if (isset($_POST['event_status']) && $_POST['event_status'] == 'D') { ?>
				<input name="perm_delete_event" type="submit" class="button-secondary" id="perm_delete_event" value="<?php _e('Permanently Delete Events(s)', 'event_espresso'); ?>" style="margin:10px 0 0 10px;" onclick="return confirmDelete();" />
			<?php } else { ?>
				<input name="delete_event" type="submit" class="button-secondary" id="delete_event" value="<?php _e('Delete Events(s)', 'event_espresso'); ?>" style="margin:10px 0 0 10px;" onclick="return confirmDelete();" />

				<a  style="margin-left:5px"class="button-secondary" href="admin.php?page=events&amp;action=csv_import"><?php _e('Import Events', 'event_espresso'); ?></a>
				<?php if (function_exists('espresso_attendee_import') && $espresso_premium == true) {?><a style="margin-left:5px" class="button-secondary" href="admin.php?page=espresso_attendee_import"><?php _e('Import Attendees', 'event_espresso'); ?></a><?php } ?>

				<a style="margin-left:5px" class="button-secondary" href="#" onclick="window.location='<?php echo get_bloginfo('wpurl') . "/wp-admin/admin.php?event_espresso&amp;id=" . $event_id . "&amp;export=report&action=payment&amp;type=excel&all_events=true"; ?>'" title="<?php _e('Export to Excel', 'event_espresso'); ?>"><?php _e('Export All Attendee Data', 'event_espresso'); ?></a>
				<a class="button-secondary" href="#" onclick="window.location='<?php echo get_bloginfo('wpurl') . "/wp-admin/admin.php?event_espresso&amp;id=" . $event_id . "&amp;export=report&action=event&amp;type=excel&all_events=true"; ?>'" title="<?php _e('Export to Excel', 'event_espresso'); ?>"><?php _e('Export All Event Data', 'event_espresso'); ?></a>
				<a style="margin-left:5px" class="button-primary" href="admin.php?page=events&amp;action=add_new_event"><?php _e('Add New Event', 'event_espresso'); ?></a>
			<?php } ?>  </div>
	</form>
	<h4 style="clear:both"><?php _e('Legend', 'event_espresso'); ?></h4>
	<dl style="float:left; margin-left:10px; width:200px">
		<?php
		echo defined('EVENT_ESPRESSO_RECURRENCE_MODULE_ACTIVE') ?
						'<dt><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/arrow_rotate_clockwise.png" alt="Recurring Event" title="Recurring Event"  /> - ' . __('Recurring Event', 'event_espresso') . '</dt>' : '';
		?>
		<dt><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/magnifier.png" width="16" height="16" alt="<?php _e('View Event', 'event_espresso'); ?>" /> - <?php _e('View Event', 'event_espresso'); ?></dt>

		<dt><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/calendar_edit.png" width="16" height="16" alt="<?php _e('Edit Event', 'event_espresso'); ?>" /> - <?php _e('Edit Event', 'event_espresso'); ?></dt>

		<dt><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/group.png" width="16" height="16" alt="<?php _e('Event Attendees', 'event_espresso'); ?>" /> - <?php _e('Event Attendees', 'event_espresso'); ?></dt>
		<dt><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/chart_bar.png" width="16" height="16" alt="<?php _e('Send Event Email', 'event_espresso'); ?>" /> - <?php _e('View Report', 'event_espresso'); ?></dt>


	</dl>

	<dl style="float:left; margin-left:10px;">
		<dt><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/tag.png" width="16" height="16" alt="<?php _e('Short Code', 'event_espresso'); ?>" /> - <?php _e('Short Code', 'event_espresso'); ?></dt>
		<dt><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/excel_icon.png" width="16" height="16" alt="<?php _e('Excel Spreadsheet', 'event_espresso'); ?>" /> - <?php _e('Excel Export', 'event_espresso'); ?></dt>

		<dt><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/csv_icon_sm.gif" width="16" height="16" alt="<?php _e('CSV Spreadsheet', 'event_espresso'); ?>" /> - <?php _e('CSV Export', 'event_espresso'); ?></dt>

		<dt><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/email_go.png" width="16" height="16" alt="<?php _e('View Report', 'event_espresso'); ?>" /> - <?php _e('Event Newsletter', 'event_espresso'); ?></dt>
	</dl>

	<script>
		jQuery(document).ready(function($) {
			/* show the table data */
			var mytable = $('#table').dataTable( {
				"sDom": 'Clfrtip',
				"aoColumns": [
					{ "bSortable": false },
					null,
					null,
					null,
					null,
					null,
					null,
					null,
					null,
	<?php echo function_exists('espresso_is_admin') && espresso_is_admin() == true ? 'null,' : ''; ?>
					null,
					{ "bSortable": false }
				],
				"aoColumnDefs": [
					{ "bVisible": false, "aTargets": [ <?php echo $org_options['use_venue_manager'] == 'Y' ? '' : '3,' ?> 6 ] }
				],
				"oColVis": {
					"aiExclude": [ 0, 1, 2 ],
					"buttonText": "Filter: Show / Hide Columns",
					"bRestore": true
				},
				"bAutoWidth": false,
				"bStateSave": true,
				"sPaginationType": "full_numbers",
				"oLanguage": {	"sSearch": "<strong><?php _e('Live Search Filter', 'event_espresso'); ?>:</strong>",
					"sZeroRecords": "<?php _e('No Records Found!', 'event_espresso'); ?>" }

			});
		});
	</script>

	<div id="coupon_code_info" style="display:none">
		<h2><?php _e('Coupon/Promo Code', 'event_espresso'); ?></h2><p><?php _e('This is used to apply discounts to events.', 'event_espresso'); ?></p><p><?php _e('A coupon or promo code could can be anything you want. For example: Say you have an event that costs', 'event_espresso'); ?> <?php echo $org_options['currency_symbol'] ?>200. <?php _e('If you supplied a promo like "PROMO50" and entered 50.00 into the "Discount w/Promo Code" field your event will be discounted', 'event_espresso'); ?>  <?php echo $org_options['currency_symbol'] ?>50.00, <?php _e('Bringing the cost of the event to', 'event_espresso'); ?> <?php echo $org_options['currency_symbol'] ?>150.</p>
	</div>
	<div id="unique_id_info" style="display:none">
		<h2><?php _e('Event Identifier', 'event_espresso'); ?></h2><p><?php _e('This should be a unique identifier for the event. Example: "Event1" (without qoutes.)</p><p>The unique ID can also be used in individual pages using the', 'event_espresso'); ?> [SINGLEEVENT single_event_id="<?php _e('Unique Event ID', 'event_espresso'); ?>"] <?php _e('shortcode', 'event_espresso'); ?>.</p>
	</div>
	<?php
	echo event_espresso_custom_email_info();
}
