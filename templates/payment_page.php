<div id="espresso_confirmation_display" class="event-display-boxes">
  <?php
    //Confirmation Page Template
    if ($event_cost == '0.00') {
        unset($_SESSION['espresso_session_id']);
        ?>
  <h2><?php echo $fname ?>,</h2>
  <p class="instruct">
    <?php _e('Thank you! Your registration is confirmed for', 'event_espresso'); ?>
    <b><?php echo stripslashes_deep($event_name) ?></b> </p>
  <p> <span class="section-title">
    <?php _e('Your Registration ID: ', 'event_espresso'); ?>
    </span> <?php echo $registration_id ?> </p>
  <p class="instruct">
    <?php _e('A confirmation email has been sent with additional details of your registration.', 'event_espresso'); ?>
  </p>
  <?php
} else {
    ?>
  <h2><?php echo $fname ?>,</h2>
  <p class="instruct">
    <?php _e('Your registration is not complete until payment is received.', 'event_espresso'); ?>
  </p>
  <p> <span class="event_espresso_name section-title">
    <?php _e('Amount due: ', 'event_espresso'); ?>
    </span> <span class="event_espresso_value"><?php echo isset($org_options['currency_symbol'])?$org_options['currency_symbol']:''; ?><?php echo $event_cost; ?></span> </p>
  <p> <span class="section-title">
    <?php _e('Your Registration ID: ', 'event_espresso'); ?>
    </span><?php echo $registration_id ?> </p>
  <p> <?php echo $org_options['email_before_payment'] == 'Y' ? __('A confirmation email has been sent with additional details of your registration.', 'event_espresso') : ''; ?> </p>
  <?php
}
?>
</div>