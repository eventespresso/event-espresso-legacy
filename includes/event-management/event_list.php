<?php
function event_espresso_edit_list(){

	global $wpdb, $org_options;

   if ( $_POST[ 'delete_event' ] )
    {
        if ( is_array( $_POST[ 'checkbox' ] ) )
        {
            while ( list($key, $value) = each( $_POST[ 'checkbox' ] ) ):
                $del_id = $key;
				event_espresso_delete_event($del_id);
            endwhile;
        }
?>

        <div id="message" class="updated fade">
          <p><strong>
            <?php _e( 'Event(s) have been permanently deleted.', 'event_espresso' ); ?>
            </strong></p>
        </div>
<?php
	}

    $recurrence_icon = '';

    if (defined('EVENT_ESPRESSO_RECURRENCE_MODULE_ACTIVE'))

    {

        $recurrence_icon = '<img src="' . EVENT_ESPRESSO_RECURRENCE_FULL_URL . 'images/recurring_icon.png" alt="Recurring Event" class="re_fr" />';

    }
?>
<div class="box-mid-head">
  <h2 class="fugue f-wrench">
    <?php _e('Current Events','event_espresso'); ?>
  </h2>
</div>
<div class="box-mid-body" id="toggle2">
  <div class="padding">
   <div style="float:right; margin:10px 20px;">
      <a class="button-primary" href="admin.php?page=events&amp;action=csv_import"><?php _e('Import CSV','event_espresso'); ?></a>
	</div>
	<div style="float:right; margin:10px 20px;">
       <a class="button-primary" href="admin.php?page=events&amp;action=add_new_event"><?php _e('Add New Event','event_espresso'); ?></a>
	</div>
   <div id="tablewrapper">
		<div id="tableheader">
        	<div class="search">
                <select id="columns" onchange="sorter.search('query')"></select>
                <input type="text" id="query" onkeyup="sorter.search('query')" />
            </div>
            <span class="details">
				<div>Records <span id="startrecord"></span>-<span id="endrecord"></span> of <span id="totalrecords"></span></div>
        		<div><a href="javascript:sorter.reset()">reset</a></div>
        	</span>
        </div>
<form id="form1" name="form1" method="post" action="<?php echo $_SERVER[ "REQUEST_URI" ] ?>">
<table id="table" class="tinytable" width="100%"> 
	<thead>
		<tr>
          <th><h3><?php _e('ID','event_espresso'); ?></h3></th>
		  <th><h3><?php _e('Name','event_espresso'); ?></h3></th>
          <th><h3><?php _e('Event Start','event_espresso'); ?></h3></th>
          <th><h3><?php _e('Registration Start','event_espresso'); ?></h3></th>
         
		  <th><h3><?php _e('Status','event_espresso'); ?></h3></th>
		  <th><h3><?php _e('Attendees','event_espresso'); ?></h3></th>
          <th><h3><?php _e('Shortcode','event_espresso'); ?></h3></th>
		</tr>
	</thead>
    <tbody>
<?php 
		$curdate = date("Y-m-d");
		$wpdb->query("SELECT * FROM ". EVENTS_DETAIL_TABLE ." WHERE event_status != 'D'");
		if ($wpdb->num_rows > 0) {
			$sql = "SELECT e.id event_id, e.event_name, e.event_identifier, e.reg_limit, e.registration_start, ";
			$sql .= " e.start_date, e.is_active, e.recurrence_id FROM ". EVENTS_DETAIL_TABLE ." e ";
			$sql .= " WHERE event_status != 'D' GROUP BY e.id  ORDER BY start_date  ASC ";
			$results = $wpdb->get_results($sql);

				foreach ($results as $result){
					$event_id= $result->event_id;
					$event_name=stripslashes_deep($result->event_name);
					$event_identifier=stripslashes_deep($result->event_identifier);
					$reg_limit = $result->reg_limit;
					$registration_start = $result->registration_start;
					$start_date = $result->start_date;
					$end_date = $result->end_date;
					$is_active= $result->is_active;
					$status = array();
					$status = event_espresso_get_is_active($event_id);
					$recurrence_id = $result->recurrence_id;
					

?>
			<tr>
			  <td>
              <!--Delete Events-->
			  <?php echo '<input name="checkbox[' . $event_id . ']" type="checkbox"  title="Delete Event '. $event_name .'" />';?>
			  <?php echo $event_id?>
				<?php echo ($recurrence_id >0) ? $recurrence_icon :'' ; ?>
                          </td>

			  <td><a href="admin.php?page=events&action=edit&event_id=<?php echo $event_id?>"><?php echo $event_name?></a><br />

              <div class="row-actions"><span class='edit'><a href="admin.php?page=events&amp;action=edit&amp;event_id=<?php echo $event_id?>">Edit</a> | </span><span class='delete'><a onclick="return confirmDelete();" href='admin.php?page=events&amp;action=delete&amp;event_id=<?php echo $event_id?>'>Delete</a></span> | <span><a href="admin.php?page=admin_reports&amp;event_admin_reports=list_attendee_payments&amp;event_id=<?php echo $event_id?>">Attendees</a></span></div></td>

              <td><?php echo event_date_display($start_date, 'm-d-Y')?></td>

              <td><?php echo event_date_display($registration_start, 'm-d-Y')?></td>

              

              <td><?php echo $status['display'] ?></td>

			  <td><a href="admin.php?page=admin_reports&amp;event_admin_reports=list_attendee_payments&amp;event_id=<?php echo $event_id?>"><?php echo get_number_of_attendees_reg_limit($event_id);?></a></td>			  

              <td><a class="ev_reg-fancylink" href="#unique_id_info_<?php echo $event_id?>"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>/images/question-frame.png" width="16" height="16" /></a>

              <div id="unique_id_info_<?php echo $event_id?>" style="display:none">

  <?php _e('<h2>Event Identifier</h2>

      <p>The unique ID can be used in individual pages using:<br />

	  <span  class="updated fade">[SINGLEEVENT single_event_id="' .  $event_identifier . '"]</span></p>','event_espresso'); ?>
</div>
				</td>
			  </tr>
	<?php } 
		}else { ?>
  <tr>
    <td><?php _e('No Record Found!','event_espresso'); ?></td>
  </tr>
<?php	}?>
          </tbody>
          </table>
<?php /*?> <input type="checkbox" name="sAll" onclick="selectAll(this)" />
  <strong>
  <?php _e( 'Check All', 'event_espresso' ); ?>
  </strong><?php */?>
  <input name="delete_event" type="submit" class="button-secondary" id="delete_event" value="<?php _e( 'Delete Events(s)', 'event_espresso' ); ?>" style="margin:10px 0 0 10px;" onclick="return confirmDelete();" />  </form>
          <div id="tablefooter">
          <div id="tablenav">
            	<div>
                    <img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>images/first.gif" width="16" height="16" alt="First Page" onclick="sorter.move(-1,true)" />
                    <img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>images/previous.gif" width="16" height="16" alt="First Page" onclick="sorter.move(-1)" />
                    <img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>images/next.gif" width="16" height="16" alt="First Page" onclick="sorter.move(1)" />
                    <img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>images/last.gif" width="16" height="16" alt="Last Page" onclick="sorter.move(1,true)" />
                </div>
                <div>
                	<select id="pagedropdown"></select>
				</div>
                <div>
                	<a href="javascript:sorter.showall()">view all</a>
                </div>
            </div>
			<div id="tablelocation">
            	<div>
                    <select onchange="sorter.size(this.value)">

                    <option value="5">5</option>

                        <option value="10" selected="selected">10</option>

                        <option value="20">20</option>

                        <option value="50">50</option>

                        <option value="100">100</option>

                    </select>
                    <span>Entries Per Page</span>
                </div>
                <div class="page">Page <span id="currentpage"></span> of <span id="totalpages"></span></div>
            </div>
        </div>
    </div>
</div>
</div>
<div id="coupon_code_info" style="display:none">
<?php _e('<h2>Coupon/Promo Code</h2>
      <p>This is used to apply discounts to events.</p>
      <p>A coupon or promo code could can be anything you want. For example: Say you have an event that costs '. $org_options['currency_symbol'].'200. If you supplied a promo like "PROMO50" and entered 50.00 into the "Discount w/Promo Code" field your event will be discounted '.$org_options['currency_symbol'].'50.00, Bringing the cost of the event to '.$org_options['currency_symbol'].'150.</p>','event_espresso'); ?>
</div>
<div id="unique_id_info" style="display:none">

      <?php _e('<h2>Event Identifier</h2>

      <p>This should be a unique identifier for the event. Example: "Event1" (without qoutes.)</p>

      <p>The unique ID can also be used in individual pages using the [SINGLEEVENT single_event_id="Unique Event ID"] shortcode.</p>','event_espresso'); ?>

    </div>


  <script type="text/javascript">
	var sorter = new TINY.table.sorter('sorter','table',{
		headclass:'head',
		ascclass:'asc',
		descclass:'desc',
		evenclass:'evenrow',
		oddclass:'oddrow',
		evenselclass:'evenselected',
		oddselclass:'oddselected',
		paginate:true,
		size:30,
		colddid:'columns',
		currentid:'currentpage',
		totalid:'totalpages',
		startingrecid:'startrecord',
		endingrecid:'endrecord',
		totalrecid:'totalrecords',
		hoverid:'selectedrow',
		pageddid:'pagedropdown',
		navid:'tablenav',
		sortcolumn:2,
		sortdir:1,
		//sum:[8],
		//avg:[6,7,8,9],
		//columns:[{index:7, format:'%', decimals:1},{index:8, format:'$', decimals:0}],
		init:true
	});
  </script>
  <?php
  }