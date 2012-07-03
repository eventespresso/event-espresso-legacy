<div style="display: none;">

  <?php
  	/**
  	 * Pre-existing Emails Help Box
  	 */
  ?>
  <div id="email_manager_info" class="pop-help" >
	<div class="TB-ee-frame">
	  <h2><?php _e('Pre-existing Emails', 'event_espresso'); ?></h2>
		<p><?php _e('These emails will override the custom email if a pre-existing email is selected. You must select "Yes" in the "Send custom confirmation emails for this event?" above.', 'event_espresso'); ?></p>

	</div>
  </div>

  <?php
  	/**
  	 * Coupon/Promo Code Help Box
  	 */
  ?>
  <div id="coupon_code_info" class="pop-help" >
	<div class="TB-ee-frame">
	  <h2><?php _e('Coupon/Promo Code', 'event_espresso'); ?></h2><p><?php _e('This is used to apply discounts to events.', 'event_espresso'); ?></p><p><?php _e('A coupon or promo code could can be anything you want. For example: Say you have an event that costs', 'event_espresso'); ?> <?php echo $org_options['currency_symbol'] ?>200. <?php _e('If you supplied a promo like "PROMO50" and entered 50.00 into the "Discount w/Promo Code" field your event will be discounted', 'event_espresso'); ?>  <?php echo $org_options['currency_symbol'] ?>50.00, <?php _e('Bringing the cost of the event to', 'event_espresso'); ?> <?php echo $org_options['currency_symbol'] ?>150.</p>

	</div>
  </div>

  <?php
  	/**
  	 * Event Identifier Help Box
  	 */
  ?>
  <div id="unique_id_info" class="pop-help" >
	<div class="TB-ee-frame">
	 <h2><?php _e('Event Identifier', 'event_espresso'); ?></h2><p><?php _e('This should be a unique identifier for the event. Example: "Event1" (without qoutes.)</p><p>The unique ID can also be used in individual pages using the', 'event_espresso'); ?> [SINGLEEVENT single_event_id="<?php _e('Unique Event ID', 'event_espresso'); ?>"] <?php _e('shortcode', 'event_espresso'); ?>.</p>
	</div>
  </div>

   <?php
   	/**
   	 * Waitlist Events Help Box
   	 */
   ?>
  <div id="secondary_info" class="pop-help" >
	<div class="TB-ee-frame">
	   <h2><?php _e('Waitlist Events', 'event_espresso'); ?></h2>
		<p><?php _e('These types of events can be used as a overflow or waiting list events.', 'event_espresso'); ?></p>
		<p><?php _e('If an event is set up as an "Waitlist Event," it can be set to not appear in your event listings template. You may need to customize your event_listing.php file to make this work. For more information, please', 'event_espresso'); ?> <a href="http://eventespresso.com/forums/?p=512" target="_blank"><?php _e('visit the forums', 'event_espresso'); ?></a>.
	</div>
  </div>

  <?php
  	/**
  	 * Off-site Registration URL Help Box
  	 */
  ?>
  <div id="external_URL_info" class="pop-help" >
	<div class="TB-ee-frame">
	 <h2><?php _e('Off-site Registration Page', 'event_espresso'); ?></h2>
		<p><?php _e('If an off-site registration page is entered, it will override your registration page and send attendees to the URL that is entered.', 'event_espresso'); ?></p>
	</div>
  </div>

  <?php
  	/**
  	 * Event Status Type Help Box
  	 */
  ?>
  <div id="status_types_info" class="pop-help" >
	<div class="TB-ee-frame">
	 <h2><?php _e('Event Status Types', 'event_espresso'); ?></h2>
		<ul>
			<li><strong><?php _e('Primary', 'event_espresso'); ?></strong><br /><?php _e('This type if event should always appear in the event lsiting. It is a live event (not deleted, ongoing or secondary.)', 'event_espresso'); ?></li>
			<li><strong><?php _e('Waitlist', 'event_espresso'); ?></strong><br /><?php _e('This type of event can be hidden and used as a waiting list for a primary event. Template customizations may be required. For more information, please', 'event_espresso'); ?> <a href="http://eventespresso.com/forums/?p=512" target="_blank"><?php _e('visit the forums', 'event_espresso'); ?></a></li>
			<li><strong><?php _e('Ongoing', 'event_espresso'); ?></strong><br /><?php _e('This type of an event can be set to appear in your event listings and display a registration page. Template customizations are required. For more information, please', 'event_espresso'); ?> <a href="http://eventespresso.com/forums/?p=518" target="_blank"><?php _e('visit the forums', 'event_espresso'); ?></a></li>
			<li><strong><?php _e('Deleted', 'event_espresso'); ?></strong><br /><?php _e('This is event type will not appear in the event listings and will not dispaly a registrations page. Deleted events can still be accessed in the', 'event_espresso'); ?> <a href="admin.php?page=events"><?php _e('Attendee Reports', 'event_espresso'); ?></a> <?php _e('page', 'event_espresso'); ?>.</li>
		</ul>
	</div>
  </div>

  <?php
  	/**
  	 * Registration Date/Time Help Box
  	 */
  ?>
  <div id="reg_date_info" class="pop-help" >
	<div class="TB-ee-frame">
	 <h2><?php _e('Registration Dates/Times', 'event_espresso'); ?></h2>
		<p><?php _e('The event will automatically turn the registration form on and off between these dates and times.', 'event_espresso'); ?></p>
		<p><strong><?php _e('Note:', 'event_espresso'); ?></strong> <?php _e('If the date of your event occurs before the regisration end date. Then the registation form will be displayed and also accept registrations.', 'event_espresso'); ?></p>
		<p><?php _e('All events require registration start/end dates and start/end times in order to display properly on your pages.', 'event_espresso'); ?></p>

	</div>
  </div>

 <?php
 	/**
 	 * Event Date Help Box
 	 */
 ?>
  <div id="event_date_info" class="pop-help" >
	<div class="TB-ee-frame">
	 <h2><?php _e('Event Date', 'event_espresso'); ?></h2>
		<p><?php _e('This is the date of the event.', 'event_espresso'); ?></p>
		<p><?php _e('All events require a start and end date in order to display properly on your pages.', 'event_espresso'); ?></p>
	</div>
  </div>

  <?php
  	/**
  	 * Event Time Help Box
  	 */
  ?>
  <div id="event_times_info" class="pop-help" >
	<div class="TB-ee-frame">
	 <h2><?php _e('Event Times', 'event_espresso'); ?></h2>
		<p><?php _e('Each event can have an unlimited amount of start/end times. This is useful for event/class organizers to manage several different sessions in their events.', 'event_espresso'); ?></p>
		<p><?php _e('All events require a start and end time in order to display properly on your pages.', 'event_espresso'); ?></p>
		<p><?php _e('Event times can be entered in the format of: 09:00/21:00  or 9am/9pm ', 'event_espresso') ?></p>
	</div>
  </div>

  <?php
  	/**
  	 * Current Time Help Box
  	 */
  ?>
  <div id="current_time_info" class="pop-help" >
	<div class="TB-ee-frame">
	 <h2><?php _e('Current Time', 'event_espresso'); ?></h2>
		<p><?php _e('This is the current time of your website. The timezone and date/time formats can be changed in your <a href="options-general.php" target="_blank">WordPress settings</a>.', 'event_espresso'); ?></p>
	</div>
  </div>



  <?php
  	/**
  	 * Custom Ticket Help Box
  	 */
  ?>
  <div id="custom_ticket_info" class="pop-help" >
	<div class="TB-ee-frame">
	 <h2><?php _e('Custom Ticket', 'event_espresso'); ?></h2>
		<p><?php _e('Choose a ticket template to be used for this event. If no template is selected, the default template will be used. Templates can be created on the Tickets page.', 'event_espresso'); ?></p>
	</div>
  </div>


	<?php
  	/**
  	 * A dummy example help box
  	 * use this to create new help boxes
  	 */
  ?>
  <div id="example_example_info" class="pop-help" >
	<div class="TB-ee-frame">
	 <h2><?php _e('Example Example', 'event_espresso'); ?></h2>
		<p><?php _e('Hey Mickey, you\'re so fine, you\'re so fine you blow my mind, hey Mickey', 'event_espresso'); ?></p>
	</div>
  </div>


</div><!--End <div style="display: none;"> -->
