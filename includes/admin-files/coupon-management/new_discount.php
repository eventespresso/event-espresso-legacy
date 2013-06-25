<?php
function add_new_event_discount(){
	?>
<div class="metabox-holder">
  <div class="postbox">
    <h3>
      <?php _e('Add a New Promotional Code','event_espresso'); ?>
    </h3>
<div class="inside">
    <form id="new-promo-code" method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>">
      <input type="hidden" name="action" value="add">
      <ul>
        <li>
          <label for="coupon_code">
            <?php _e('Promotional Code','event_espresso'); ?><em title="<?php _e('This field is required', 'event_espresso') ?>"> *</em>
          </label>
          <input  id="coupon_code" type="text" name="coupon_code" size="25" />
        </li>
        <li>
          <label>
            <?php _e('Price Discount','event_espresso'); ?>
          </label>
          <input type="text" name="coupon_code_price" />
        </li>
        <li>
          <?php _e('Is this a percentage discount?','event_espresso'); ?>
          <input type="radio" name="use_percentage" value="Y">
          <?php _e('Yes','event_espresso'); ?>
          <input type="radio" name="use_percentage" checked="checked" value="N">
          <?php _e('No','event_espresso'); ?>
        </li> 
		<li>
          <?php _e('Global ? (Apply to all events by default)','event_espresso'); ?>
          <input type="radio" name="apply_to_all" value=1>
          <?php _e('Yes','event_espresso'); ?>
          <input type="radio" name="apply_to_all" checked="checked" value=0>
          <?php _e('No','event_espresso'); ?>
        </li>
        <li>
          <?php _e('Promotional Code Description','event_espresso'); ?>
          <br />
          <textarea rows="5" cols="300" name="coupon_code_description" id="coupon_code_description_new"  class="my_ed"></textarea>
        </li>
        <li>
          <p>
            <input class="button-primary" type="submit" name="add_new_discount" value="<?php _e('Submit','event_espresso'); ?>" id="add_new_discount" />
          </p>
        </li>
      </ul>
    </form>
</div>
  </div>
</div>
<?php 
}