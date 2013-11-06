<?php

if ( !function_exists( 'espresso_user_has_venue_permission' ) ) {
    function espresso_user_has_venue_permission ( $venue_id ) {
        
        global $espresso_premium, $wpdb, $espresso_manager, $current_user;
        $result = false;
        
        // If not premium then permission is not necessary, return true
        if ( true != $espresso_premium ) return true;
        
        // If permission addon doesn't exists then return true
        if(  !function_exists('espresso_member_data') ) return true; 
        
        // If user is administrator or master admin then return true
        if( 'espresso_event_admin'  == espresso_member_data('role') || current_user_can('administrator') ) return true;
        
        if ( 'espresso_group_admin' == espresso_member_data('role') || 'espresso_event_manager' == espresso_member_data( 'role' ) ) {
            $sql = " SELECT * FROM " . EVENTS_VENUE_TABLE .  " v WHERE wp_user = '" . espresso_member_data( 'id' ) . "' AND v.id = " . $venue_id ;
            $rs = $wpdb->get_results( $sql );
            if ( NULL !== $rs && count( $rs ) > 0 ) {
                $result = true;
            } elseif ( 'espresso_group_admin' == espresso_member_data( 'role' ) && 
                     ( isset( $espresso_manager[ 'event_manager_venue' ] ) && "y" == strtolower( $espresso_manager[ 'event_manager_venue' ] ) ) 
                ) {
                $group = get_user_meta( espresso_member_data( 'id' ), "espresso_group", true );
                if (is_array( $group ) && count( $group ) > 0 ) {
                    $sql = " SELECT * FROM " . EVENTS_VENUE_TABLE . " v LEFT JOIN " . EVENTS_LOCALE_REL_TABLE . " lr ON v.id = lr.venue_id WHERE v.id = '" . $venue_id . "' AND lr.locale_id IN (" . implode( ',', $group ) . ") ";
                    $rs = $wpdb->get_results( $sql );
                    if ( NULL !== $rs && count( $rs ) > 0 ) $result = true;
                }
            }
        }
        
        return $result;
    }
} 

if ( !function_exists( 'espresso_venue_dd' ) ){
	function espresso_venue_dd($current_value=0){
		global $espresso_premium; if ($espresso_premium != true) return;
		global $wpdb, $espresso_manager, $current_user;

		$WHERE = " WHERE ";
		$sql = "SELECT ev.*, el.name AS locale FROM " . EVENTS_VENUE_TABLE . " ev ";
		$sql .= " LEFT JOIN " . EVENTS_LOCALE_REL_TABLE . " lr ON lr.venue_id = ev.id ";
		$sql .= " LEFT JOIN " . EVENTS_LOCALE_TABLE . " el ON el.id = lr.locale_id ";

		if(  function_exists('espresso_member_data') && ( espresso_member_data('role')=='espresso_group_admin' ) ){
			if(	$espresso_manager['event_manager_venue'] == "Y" ){
				//show only venues inside their assigned locales.
				$group = get_user_meta(espresso_member_data('id'), "espresso_group", true);
				$sql .= " $WHERE lr.locale_id IN (" . implode(",", $group) . ")";
				$sql .= " OR ev.wp_user = ".$current_user->ID ;
				$WHERE = " AND ";
			}
		}
		$sql .= " GROUP BY ev.id ORDER by name";

		//echo $sql;
		$venues = $wpdb->get_results($sql);
		$num_rows = $wpdb->num_rows;


#		return "<pre>".print_r( $venues,true )."</pre>";
/*
            [id] => 3
            [name] => Home
            [identifier] =>
            [address] => 101-1414 Government Street
            [address2] =>
            [city] => Penticton
            [state] => BC
            [zip] => V2A 4W1
            [country] => Canada
            [meta] => a:6:{s:7:"contact";s:0:"";s:5:"phone";s:0:"";s:7:"twitter";s:0:"";s:5:"image";s:0:"";s:7:"website";s:0:"";s:11:"description";s:0:"";}
            [locale] =>
            [wp_user] => 0
*/
		//echo $current_value;
		if ($num_rows > 0) {
			$field = '<label>' . __('Select from Venue Manager list', 'event_espresso') . '</label>';
			$field .= '<select name="venue_id[]" id="venue_id">\n';
			$field .= '<option value="0">'.__('Select a Venue', 'event_espresso').'</option>';
			$div = "";
			$help_div = "";
			$i = 0;
			foreach ($venues as $venue){

				$i++;
				$selected = $venue->id == $current_value ? 'selected="selected"' : '';
                if ($venue->locale != '') {
                    $field .= '<option rel="'.$i.'" '. $selected .' value="' . $venue->id .'">' . stripslashes_deep($venue->name) . ' (' . stripslashes_deep($venue->locale) . ') </option>\n';
                } else if ($venue->city != '' && $venue->state != '') {
                    $field .= '<option rel="'.$i.'" '. $selected .' value="' . $venue->id .'">' . stripslashes_deep($venue->name) . ' (' . stripslashes_deep($venue->city). ', ' . stripslashes_deep($venue->state) . ') </option>\n';
                } else if ($venue->state != '') {
                    $field .= '<option rel="'.$i.'" '. $selected .' value="' . $venue->id .'">' . stripslashes_deep($venue->name) . ' (' . stripslashes_deep($venue->state) . ') </option>\n';
                } else {
                    $field .= '<option rel="'.$i.'" '. $selected .' value="' . $venue->id .'">' . stripslashes_deep($venue->name) . ' </option>\n';
                }

				$hidden = "display:none;";
				if( $selected ) $hidden = '';
				$div .= "<fieldset id='eebox_".$i."' class='eebox' style='".$hidden."'>";
				$div .= "<ul class='address-view'><li><p><span>Address:</span> ".stripslashes_deep($venue->address)."</p>";
				$div .= "<p><span>Address 2:</span> ".stripslashes_deep($venue->address2)."</p>";
				$div .= "<p><span>City:</span> ".stripslashes_deep($venue->city)."</p>";
				$div .= "<p><span>State:</span> ".stripslashes_deep($venue->state)."</p>";
				$div .= "<p><span>Zip:</span> ".stripslashes_deep($venue->zip)."</p>";
				$div .= "<p><span>Country:</span> ".stripslashes_deep($venue->country)."</p>";
				$div .= "<p><span>Venue ID:</span> ".$venue->id."</p>";
				$div .= '<p><a href="admin.php?page=event_venues&action=edit&id='.$venue->id.'" target="_blank">'.__('Edit this venue', 'event_espresso').'</a> | <a class="thickbox link" href="#TB_inline?height=300&width=400&inlineId=venue_info">Shortcode</a></p></li></ul>';
				$div .= "</fieldset>";
			}
			$field .= "</select>";
			$help_div .= '<div id="venue_info" style="display:none">';
			$help_div .= '<h2>'.__('Venue Shortcode', 'event_espresso').'</h2>';
			$help_div .= '<p>'.__('Add the following shortcode into the description to show the venue for this event.', 'event_espresso').'</p>';
			$help_div .= '<p>[ESPRESSO_VENUE]</p>';
			$help_div .= '<p>'.__('To use this venue in a page or post. Use the following shortcode.', 'event_espresso').'</p>';
			$help_div .= '<p>[ESPRESSO_VENUE id="selected_venue_id"]</p>';
			$help_div .= '<p>Example with Optional Parameters:<br />[ESPRESSO_VENUE outside_wrapper="div" outside_wrapper_class="event_venue"]</p>';
			$help_div .= '<p><strong><a href="http://eventespresso.com/wiki/shortcodes-template-variables/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=venue+shortcode+examples+ee_version_'.EVENT_ESPRESSO_VERSION.'&utm_campaign=event_editor_venue_section#venue_shortcode" target="_blank">More Examples</a></strong></p>';
			$help_div .= '</div>';
			ob_start();

?>
				<script>
					jQuery("#venue_id").change( function(){
						var selected = jQuery("#venue_id option:selected");
						var rel = selected.attr("rel");
						jQuery(".eebox").hide();
						jQuery("#eebox_"+rel).show();
					});
				</script>
<?php
			$js = ob_get_contents();
			ob_end_clean();
			$html = '<table><tr><td>' . $field .'</td></tr><tr><td>'.$div.'</td></tr></table>'.$help_div.$js;
			return $html;
		}
	}
}

if ( !function_exists( 'espresso_personnel_cb' ) ){
	function espresso_personnel_cb($event_id = 0, $recurrence_id = 0){
		global $espresso_premium; if ($espresso_premium != true) return;
		global $wpdb;
		$where = apply_filters('filter_hook_espresso_personal_cb_where', '', $event_id);
		$sql = "SELECT id, name, role, meta FROM " . EVENTS_PERSONNEL_TABLE . $where;
		$event_personnel = $wpdb->get_results($sql);
		$num_rows = $wpdb->num_rows;
		if ($num_rows > 0){
			$html= '';
			foreach ($event_personnel as $person){
				$person_id = $person->id;
				$person_name = $person->name;
				$person_role = $person->role;

				$meta = unserialize($person->meta);
				$person_organization = (isset($meta['organization']) && $meta['organization'] !='') ? $meta['organization'] :'';
				//$person_title = $meta['title']!=''? $meta['title']:'';
				$person_info = (isset($person_role) && $person_role !='') ?' ['. $person_role . ']':'';

				$in_event_personnel = $wpdb->get_results("SELECT * FROM " . EVENTS_PERSONNEL_REL_TABLE . " WHERE event_id='".$event_id."' AND person_id='".$person_id."'");
				$in_event_person = '';
				foreach ($in_event_personnel as $in_person){
					$in_event_person = $in_person->person_id;
				}

				$html .= '<p id="event-person-' . $person_id . '" class="event-staff-list"><label for="in-event-person-' . $person_id . '" class="selectit"><input value="' . $person_id . '" type="checkbox" name="event_person[]" id="in-event-person-' . $person_id . '"' . ($in_event_person == $person_id ? ' checked="checked"' : "" ) . '/> <a href="admin.php?page=event_staff&amp;action=edit&amp;id='.$person_id.'"  target="_blank" title="'.$person_organization.'">' . $person_name .'</a> '. $person_info.'</label></p>';

			}
			
			$top_div ='';
			$bottom_div ='';

			if ($num_rows > 10){
				$top_div = '<div style="height:250px;overflow:auto;">';
				$bottom_div = '</div>';
			}
			
			?>
			<div id="event-staff" class="postbox">
			<div class="handlediv" title="Click to toggle"><br></div>
			<h3 class="hndle"> <span><?php _e('Event Staff / Speakers', 'event_espresso'); ?></span> </h3>
				<div class="inside">  
				<?php 
				echo $top_div.$html.$bottom_div; 
				if (defined('EVENT_ESPRESSO_RECURRENCE_TABLE') && $recurrence_id > 0) {
					echo '<hr /><input name="rem_apply_to_all_staff" type="checkbox" value="1" /> '.__('Apply to all events in this series', 'event_espresso');
				}
				?>
			<p><a href="admin.php?page=event_staff" target="_blank"><?php _e('Manage Staff Members', 'event_espresso')?></a> | <a class="thickbox link" href="#TB_inline?height=300&width=400&inlineId=staff_info">Shortcode</a> </p>
			<div id="staff_info" style="display:none">
				<h2><?php _e('Staff Shortcode', 'event_espresso');?></h2>
				<p><?php _e('Add the following shortcode into the description to show the staff for this event.', 'event_espresso')?></p>
				<p>[ESPRESSO_STAFF]</p>
				<p><?php _e('Example with Optional Parameters:', 'event_espresso'); ?><br />
					[ESPRESSO_STAFF outside_wrapper="div" outside_wrapper_class="event_staff" inside_wrapper="p" inside_wrapper_class="event_person"]</p>
				<p><strong><a href="http://eventespresso.com/wiki/shortcodes-template-variables/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Staff+Shortcode+examples<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=event_editor_staff_section#staff_shortcode" target="_blank"><?php _e('More Examples', 'event_espresso'); ?></a></strong></p>
			</div>
		</div>
	</div>
<?php
		}else{
			return '<a href="admin.php?page=event_staff&amp;action=add_new_person">'.__('Please add at least one person.', 'event_espresso').'</a>';
		}
	}
}
add_action('action_hook_espresso_staff_cb', 'espresso_personnel_cb', 10, 2);

if ( !function_exists( 'espresso_personnel_dd' ) ){
	function espresso_personnel_dd(){
		global $espresso_premium; if ($espresso_premium != true) return;
		global $wpdb;
			$sql = "SELECT name, title FROM EVENTS_PERSONNEL_TABLE ";//. EVENTS_DETAIL_TABLE;
			$sql .= " WHERE name != '' GROUP BY name ";

			$people = $wpdb->get_results($sql);
			$num_rows = $wpdb->num_rows;
			//return print_r( $events );
			if ($num_rows > 0) {
				$field = '<select name="event_primary_person id="event_primary_person">\n';
				$field .= '<option value="0">'.__('Select a Person', 'event_espresso').'</option>';

				foreach ($people as $person){
					$selected = $event->name == $current_value ? 'selected="selected"' : '';
					$meta = unserialize($person->meta);
					$title = $meta['title']!=''? ' (' . $meta['title'] . ')':'';
					$field .= '<option '. $selected .' value="' . $person->id .'">' . $person->name .  $title . '</option>\n';
				}
				$field .= "</select>";
				$html = '<p>' .__('Primary','event_espresso') . ': ' . $field .'</p>';
				return $html;
			}
	}
}
if (!function_exists('espresso_chart_display')){
	function espresso_chart_display($event_id, $type){
		global $wpdb, $org_options;
		$retVAl = array();
		switch ($type){
			case 'total_reg':
				//Total Registrations/Transactions
				$title = __('Total Registrations/Transactions', 'event_espresso');
				$sql = "SELECT SUM(a.total_cost) amount, SUM(a.quantity) quantity, DATE_FORMAT(a.date,'%b %d') date FROM ".EVENTS_ATTENDEE_TABLE." a WHERE event_id =".$event_id." GROUP BY DATE_FORMAT(a.date,'%m-%d-%Y')";
			break;

			case 'total_completed':
				//Completed Registrations/Transactions
				$title = __('Completed Registrations/Transactions', 'event_espresso');
				$sql = "SELECT SUM(a.total_cost) amount, SUM(a.quantity) quantity, DATE_FORMAT(a.date,'%b %d') date FROM ".EVENTS_ATTENDEE_TABLE." a WHERE event_id =".$event_id." AND (payment_status='Completed' OR payment_status='Refund') GROUP BY DATE_FORMAT(a.date,'%m-%d-%Y')";
			break;

			case 'total_pending':
				//Pending Registrations/Transactions
				$title = __('Pending Registrations/Transactions', 'event_espresso');
				$sql = "SELECT SUM(a.total_cost) amount, SUM(a.quantity) quantity, DATE_FORMAT(a.date,'%b %d') date FROM ".EVENTS_ATTENDEE_TABLE." a WHERE event_id =".$event_id." AND payment_status='Pending' GROUP BY DATE_FORMAT(a.date,'%m-%d-%Y')";
			break;

			case 'total_incomplete':
				//Incomplete Registrations/Transactions
				$title = __('Incomplete Registrations/Transactions', 'event_espresso');
				$sql = "SELECT SUM(a.total_cost) amount, SUM(a.quantity) quantity, DATE_FORMAT(a.date,'%b %d') date FROM ".EVENTS_ATTENDEE_TABLE." a WHERE event_id =".$event_id." AND (payment_status='Incomplete' OR payment_status='Payment Declined') GROUP BY DATE_FORMAT(a.date,'%m-%d-%Y')";
			break;
		}

		$results = $wpdb->get_results($sql);
		if ($wpdb->num_rows > 0) {

		foreach ($results as $row) {
			$retVal[] = $row;
		}

		$attendees = '';
		$amount ='';
		$date = '';
		foreach($retVal as $rec ){
			$amount .= $rec->amount.',';
			$date .= "'".$rec->date."', ";
			$attendees .= $rec->quantity.", ";
		}
	 //echo "<pre>".print_r($retVal,true)."</pre>";
	  ?>
		<script>
		 jQuery(document).ready(function() {
				var line1 = [<?php echo $attendees ?>];//bottom column
				var line2 = [<?php echo $amount ?>];
				var ticks = [<?php echo $date ?>];

				plot1 = jQuery.jqplot('<?php echo $type; ?>', [line1, line2], {
					//stackSeries: true,
					title: '<?php echo $title; ?>',
					seriesDefaults:{
						renderer:jQuery.jqplot.BarRenderer,
						pointLabels: { show: true },
					},
					axes: {
						xaxis: {
							renderer: jQuery.jqplot.CategoryAxisRenderer,
							ticks: ticks
						}
					},
					series: [{
						label: '# Attendees'
					},
					{
						label: '<?php echo $org_options['currency_symbol'] ?> <?php _e('Amount', 'event_espresso'); ?>',
						pointLabels: { formatString:'<?php echo utf8_encode(html_entity_decode($org_options['currency_symbol'])); ?>%.2f' },
					}],
					legend: {
						show: true,
						location: 'ne',     // compass direction, nw, n, ne, e, se, s, sw, w.
						placement: 'outsideGrid'

					},

				});

			});
		  </script>
           <!-- <?php echo $title; ?> -->
		<div id="<?php echo $type; ?>" style="margin-top:20px; margin-left:20px; width:600px; height:200px; float:left;"></div>
      	<?php
		}
	}
}

if (!function_exists('ee_default_event_meta')){
	function ee_default_event_meta(){
		return array("event_hashtag"=>"","event_format"=>"","event_livestreamed"=>"");
	}
}

if (!function_exists('event_espresso_meta_edit')){
	function event_espresso_meta_edit($event_meta='') {
		
		global $wpdb, $org_options, $espresso_premium;
		if ( $espresso_premium != TRUE ) {
			return;
		}
			
		$good_meta = array();
		$hiddenmeta = array("", "venue_id", "additional_attendee_reg_info", "add_attendee_question_groups", "date_submitted", "event_host_terms", "default_payment_status","event_thumbnail_url");
		$hiddenmeta = apply_filters('filter_hook_espresso_hidden_meta',$hiddenmeta);
		
		$meta_counter = 1;

		$event_meta = ! empty( $event_meta ) ? $event_meta : ee_default_event_meta();
		$good_meta = $event_meta;
//		printr( $event_meta, '$event_meta  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
?>
		<p>
			<?php _e('Using Event Meta boxes', 'event_espresso'); ?>
   			<a class="thickbox"  href="#TB_inline?height=400&width=500&inlineId=event-meta-boxes" target="_blank">
				<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/question-frame.png" width="16" height="16" />
			</a>
		</p>
		
		<ul id="dynamicMetaInput">
<?php
	if ( ! empty( $event_meta )) {
		foreach ( $event_meta as $k => $v ) {

			if ( in_array( $k, $hiddenmeta )) {
				//				echo "<input type='hidden' name='emeta[]' value='{$v}' />";
				unset($good_meta[$k]);
			} else {
		?>
			<li>
				<label><?php _e('Key: ', 'event_espresso'); ?></label> 
				<select id="emeta[]" name="emeta[]">
					<?php foreach ($good_meta as $k2 => $v2) { ?>
					<option value="<?php echo htmlentities( stripslashes( $k2 ), ENT_QUOTES, 'UTF-8' ); ?>" <?php echo ( $k2 == $k ? ' selected="selected"' : '' ); ?>>
						<?php echo htmlentities( stripslashes( $k2 ), ENT_QUOTES, 'UTF-8' ); ?>						
					</option>
					<?php } ?>
				</select>
				<label for="meta-value"><?php _e('Value: ', 'event_espresso'); ?></label> 
				<input  size="20" type="text" value="<?php echo htmlentities( stripslashes( $v ), ENT_QUOTES, 'UTF-8' ); ?>" name="emetad[]" id="emetad[]" />
				<img class="remove-item" title="<?php _e('Remove this meta box', 'event_espresso');?>" onclick="this.parentNode.parentNode.removeChild(this.parentNode);" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL;?>images/icons/remove.gif" alt="<?php _e('Remove Meta', 'event_espresso');?>" />
			</li>
		<?php
			}
		 } 
	} 
?>
			<li>
				<label for="emeta[]"><?php _e('Key: ', 'event_espresso');?></label> 
				<input size="20" type="text" value="" name="emeta[]" id="emeta[]"> 
				<label for="emetad[]"><?php _e('Value: ', 'event_espresso'); ?> </label>
				<input size="20" type="text" value="" name="emetad[]" id="emetad[]">
				<img class="remove-item" title="<?php _e('Remove this meta box', 'event_espresso');?>" onclick="this.parentNode.parentNode.removeChild(this.parentNode);" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL;?>images/icons/remove.gif" alt="<?php _e('Remove Meta', 'event_espresso');?>" />
			</li>
		</ul>

		<p><input type="button" class="button" value="<?php _e('Add A Meta Box', 'event_espresso'); ?>" onClick="addMetaInput('dynamicMetaInput');"></p>

		<script type="text/javascript">
			//Dynamic form fields
			var meta_counter = <?php echo $meta_counter > 1 ? $meta_counter - 1 : $meta_counter++; ?>;
			function addMetaInput(divName){
				var next_counter = counter_staticm(meta_counter);
				var newdiv = document.createElement('li');
				newdiv.innerHTML = "<label><?php _e('Key: ', 'event_espresso'); ?></label><input size='20' type='text' value='' name='emeta[]' id='emeta[]'><label><?php _e(' Value: ', 'event_espresso'); ?></label><input size='20' type='text' value='' name='emetad[]' id='emetad[]'><?php echo ' <img class=\"remove-item\" title=\"' . __('Remove this meta box', 'event_espresso') . '\" onclick=\"this.parentNode.parentNode.removeChild(this.parentNode);\" src=\"' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/remove.gif\" alt=\"' . __('Remove Meta', 'event_espresso') . '\" />'; ?>";
				document.getElementById(divName).appendChild(newdiv);
				counter++;
			}

			function counter_staticm(meta_counter) {
				if ( typeof counter_static.counter == 'undefined' ) {

					counter_static.counter = meta_counter;
				}
				return ++counter_static.counter;
			}
		</script>
		<?php
	}
}