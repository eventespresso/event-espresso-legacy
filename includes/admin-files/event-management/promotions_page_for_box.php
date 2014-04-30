<?php
/*
 * gets a list of promotions and outputs them.
 * Should be included in AJAX requests from includes/admin-files/event-management/promotions_box.php
 * where the ajax has the action parameter 'event_espresso_get_discount_codes'
 */
global $wpdb;
$start = isset($_REQUEST['start']) ? sanitize_text_field($_REQUEST['start']) : 1;
$count = isset($_REQUEST['count']) ? sanitize_text_field($_REQUEST['count']) : 11;
$event_id = isset($_REQUEST['event_id']) ? sanitize_text_field($_REQUEST['event_id']) : 0;
$excludes = array();
if (isset($_REQUEST['excludes']) && is_array($_REQUEST['excludes'])) {
	foreach ($_REQUEST['excludes'] as $promocode_id_to_exclude) {
		$excludes[] = $wpdb->prepare("%d", $promocode_id_to_exclude);
	}
}
if ($excludes) {
	$exclude_where_sql = "id NOT IN (" . implode(",", $excludes) . ") AND ";
} else {
	$exclude_where_sql = '';
}
$sql_in_common = " FROM " . EVENTS_DISCOUNT_CODES_TABLE . " WHERE  $exclude_where_sql apply_to_all=0";
if (function_exists('espresso_member_data') && !empty($event_id)) {
	$wpdb->get_results("SELECT wp_user FROM " . EVENTS_DETAIL_TABLE . " WHERE id = '" . $event_id . "'");
	$wp_user = $wpdb->last_result[0]->wp_user != '' ? $wpdb->last_result[0]->wp_user : espresso_member_data('id');
	$sql_in_common .= " AND ";
	if ($wp_user == 0 || $wp_user == 1) {
		$sql_in_common .= " (wp_user = '0' OR wp_user = '1') ";
	} else {
		$sql_in_common .= " wp_user = '" . $wp_user . "' ";
	}
}
$count_sql = "SELECT count(id) " . $sql_in_common;
$select_sql = "SELECT id,coupon_code " . $sql_in_common . " LIMIT $start,$count";

$event_discounts = $wpdb->get_results($select_sql);
$count_discounts = $wpdb->get_var($count_sql);
?>

<?php if (!$count_discounts) { ?>
	<p><?php _e("No more promocodes available for selection", "event_espresso") ?></p>
	<?php
} else {
	foreach ($event_discounts as $event_discount) {
		if (!empty($event_id)) {
			$in_event_discounts = $wpdb->get_col("SELECT discount_id FROM " . EVENTS_DISCOUNT_REL_TABLE . " WHERE event_id='" . $event_id . "' AND discount_id='" . $event_discount->id . "'");
		} else
			$in_event_discounts = array();

		echo '<p class="event-disc-code" id="event-discount-' . $event_discount->id . '"><label for="in-event-discount-' . $event_discount->id . '" class="selectit add-this-disc-code"><input value="' . $event_discount->id . '" type="checkbox" name="event_discount[]" id="in-event-discount-' . $event_discount->id . '"/> ' . $event_discount->coupon_code . "</label></p>";
	}
	?>
	<div class='promocodenav'>
	<?php if ($start != 0) { ?>
			<a href='' onclick='return espresso_disc_codes_paginate(<?php echo max(0, $start - $count) ?>,<?php echo $count ?>)'> &laquo; </a>
		<?php } ?>
		<?PHP printf(__("%s-%s of %s", "event_espresso"), $start, min($count_discounts, $start + $count), $count_discounts); ?>
		<?php if ($start + $count < $count_discounts) { ?>
			<a href='' onclick='return espresso_disc_codes_paginate(<?php echo $start + $count ?>,<?php echo $count ?>)'> &raquo;</a>
		<?php } ?>
	</div>
	<?php } ?>
<input type='hidden' name='espresso_ignore_promocode_page_start' value=<?php echo $start ?>>