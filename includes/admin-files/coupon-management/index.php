<?php

function event_espresso_discount_config_mnu() {
	global $wpdb;
	require_once("search.php");
	?>
	<div class="wrap">
		<div id="icon-options-event" class="icon32"> </div>
		<h2><?php echo _e('Manage Event Promotional Codes', 'event_espresso') ?>
			<?php
			if (!isset($_REQUEST['action']) || ($_REQUEST['action'] != 'edit' && $_REQUEST['action'] != 'new')) {
				echo '<a href="admin.php?page=discounts&amp;action=new" class="button add-new-h2" style="margin-left: 20px;">' . __('Add New Code', 'event_espresso') . '</a>';
			}
			?>
		</h2>

		<?php
		ob_start();
		$button_style = 'button-primary';
		if (isset($_REQUEST['action'])) {
			switch ($_REQUEST['action']) {
				case 'add':
					require_once("add_discount.php");
					add_discount_to_db(); //Add the discount to the DB
					break;
				case 'new':
					require_once("new_discount.php");
					add_new_event_discount(); //Add new discount form
					$button_style = 'button-secondary';
					break;
				case 'edit':
					require_once("edit_discount.php");
					edit_event_discount(); //Edit discount form
					$button_style = 'button-secondary';
					break;
				case 'update':
					require_once("update_discount.php");
					update_event_discount(); //Update discount in DB
					break;
				case 'delete_discount':
					require_once("delete_discount.php");
					delete_event_discount(); //Delete discount in DB
					break;
			}
		}
		if (!empty($_REQUEST['delete_discount'])) {//This is for the delete checkboxes
			require_once("delete_discount.php");
			delete_event_discount();
		}
		?>

		<form id="form1" name="form1" method="post" action="<?php echo $_SERVER["REQUEST_URI"] ?>">
			<table id="table" class="widefat manage-discounts">
				<thead>
					<tr>
						<th class="manage-column column-cb check-column" id="cb" scope="col" style="width:2.5%;"><input type="checkbox"></th>
						<th class="manage-column column-comments num" id="id" style="padding-top:7px; width:2.5%;" scope="col" title="Click to Sort"><?php _e('ID', 'event_espresso'); ?></th>
						<th class="manage-column column-title" id="name" scope="col" title="Click to Sort" style="width:20%;"><?php _e('Name', 'event_espresso'); ?></th>
						<?php if (function_exists('espresso_is_admin') && espresso_is_admin() == true) { ?>
							<th class="manage-column column-creator" id="creator" scope="col" title="Click to Sort" style="width:10%;"><?php _e('Creator', 'event_espresso'); ?></th>
	<?php } ?>
						<th class="manage-column column-author" id="start" scope="col" title="Click to Sort" style="width:20%;"><?php _e('Amount', 'event_espresso'); ?></th>
						<th class="manage-column column-date" id="begins" scope="col" title="Click to Sort" style="width:20%;"><?php _e('Percentage', 'event_espresso'); ?></th>
						<th class="manage-column column-date" id="begins" scope="col" title="Click to Sort" style="width:20%;"><?php _e('Global', 'event_espresso'); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan=6 class='dataTables_empty' style='text-align:center'>
							 <?php	_e("Loading...", "event_espresso")?>
						</td>
					</tr>
					
				<!--	<?php /*foreach(espresso_promocodes_initial_jquery_datatables_data() as $row){?>
					<tr>
						<?php foreach($row as $column){ ?>
						<td><?php echo $column?></td>
						<?php } ?>
					</tr>
					<?php }  */ ?> -->
				</tbody>
				</thead>
				
			</table>
			<div style="clear:both">
				<p><input type="checkbox" name="sAll" onclick="selectAll(this)" />
					<strong>
	<?php _e('Check All', 'event_espresso'); ?>
					</strong>
					<input name="delete_discount" type="submit" class="button-secondary" id="delete_discount" value="<?php _e('Delete Promotional Code', 'event_espresso'); ?>" style="margin:10 0 0 10px;" onclick="return confirmDelete();">

					<a  style="margin-left:5px"class="<?php echo $button_style; ?>" href="admin.php?page=discounts&amp;action=new"><?php _e('Add New Promotional Code', 'event_espresso'); ?></a></p>
			</div>
		</form>
		<?php
		$main_post_content = ob_get_clean();
		espresso_choose_layout($main_post_content, event_espresso_display_right_column());
		?>
	</div>

	<script type="text/javascript">
		jQuery(document).ready(function($) {

			/* show the table data */
			var mytable = $('#table').dataTable( {
					
				
				"aoColumns": [
					{ "bSortable": false },
					null,
					null,
					null,
					null,
	<?php echo function_exists('espresso_is_admin') && espresso_is_admin() == true ? 'null,' : ''; ?>
							null

						],
				'bProcessing': true, 
				'bServerSide': true, 
//				"bDeferRender": true,
				'sAjaxSource': ajaxurl+'?action=event_espresso_get_discount_codes_for_jquery_datatables',// - See more at: http://www.koolkatwebdesigns.com/using-jquery-datatables-with-wordpress-and-ajax/#sthash.H0zsZy6z.dpuf
				"iDeferLoading": <?php echo espresso_promocodes_count_total() ?>,
				"bStateSave": true,
				"sPaginationType": "full_numbers",

				"oLanguage": {	
					"sSearch": "<strong><?php _e('Live Search Filter', 'event_espresso'); ?>:</strong>",
					"sZeroRecords": "<?php _e('No Records Found!', 'event_espresso'); ?>",
					"sProcessing": "<img src='<?php echo EVENT_ESPRESSO_PLUGINFULLURL . "images/ajax-loader.gif" ?>'>"}
				

					} );

				} );
				// Add new promo code form validation
				jQuery(function(){
					jQuery("#new-promo-code").validate( {
						rules: {
							coupon_code: "required"
						},
						messages: {
							coupon_code: "Please add your promotional code"
						}
					});
		
				});
	</script>
	<?php
}
