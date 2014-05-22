<?php

//Added by Imon
if (isset($_SESSION['espresso_session']['id'])) {
	unset($_SESSION['espresso_session']['id']);
}

define('FPDF_FONTPATH', EVENT_ESPRESSO_PLUGINFULLPATH . 'class/fpdf/font/');
require_once EVENT_ESPRESSO_PLUGINFULLPATH . 'class/fpdf/fpdf.php';

require_once(dirname(__FILE__) . '/function.pdf.php'); //Added by Imon

global $espresso_premium;
if ($espresso_premium != true)
	return;
global $wpdb, $org_options;

$invoice_payment_settings = get_option('event_espresso_invoice_payment_settings');

//Added by Imon
$multi_reg = false;
$registration_id = espresso_return_reg_id();
$admin = isset($_REQUEST['admin']) ? $_REQUEST['admin'] : false;
$registration_ids = array();
$c_sql = "select * from " . EVENTS_MULTI_EVENT_REGISTRATION_ID_GROUP_TABLE . " where registration_id = '$registration_id' ";
//echo $c_sql;
$check = $wpdb->get_row($c_sql);
if ($check !== NULL) {
	$registration_id = $check->primary_registration_id;
	$registration_ids = $wpdb->get_results("select registration_id from " . EVENTS_MULTI_EVENT_REGISTRATION_ID_GROUP_TABLE . " where primary_registration_id = '$registration_id' ", ARRAY_A);
	$multi_reg = true;
} else {
	$registration_ids[] = array("registration_id" => $registration_id);
}
$attendees = $wpdb->get_results("SELECT a.*, e.event_name FROM " . EVENTS_ATTENDEE_TABLE . " a JOIN " . EVENTS_DETAIL_TABLE . " e ON e.id=a.event_id WHERE a.registration_id ='" . $registration_id . "' order by a.id LIMIT 0,1 ");

foreach ($attendees as $attendee) {
	$attendee_id = $attendee->id;
	$attendee_last = html_entity_decode(stripslashes($attendee->lname), ENT_QUOTES, "UTF-8");
	$attendee_first = html_entity_decode(stripslashes($attendee->fname), ENT_QUOTES, "UTF-8");
	$attendee_address = html_entity_decode(stripslashes($attendee->address), ENT_QUOTES, "UTF-8");
	$attendee_address .= isset($attendee->address2) ? "\n" . html_entity_decode(stripslashes($attendee->address2), ENT_QUOTES, "UTF-8") : '';
	$attendee_city = html_entity_decode(stripslashes($attendee->city), ENT_QUOTES, "UTF-8");
	$attendee_state = html_entity_decode(stripslashes($attendee->state), ENT_QUOTES, "UTF-8");
	$attendee_zip = $attendee->zip;
	$attendee_email = $attendee->email;
	//$attendee_organization_name = $attendee->organization_name;
	//$attendee_country = $attendee->country_id;
	$phone = $attendee->phone;
	$date = $attendee->date;
	$num_people = $attendee->quantity;
	$payment_status = $attendee->payment_status;
	$txn_type = $attendee->txn_type;
	$amount_pd = $attendee->amount_pd;
	$payment_date = $attendee->payment_date;
	$event_id = $attendee->event_id;
	$event_name = html_entity_decode(stripslashes($attendee->event_name), ENT_QUOTES, "UTF-8");
	//$attendee_session = $attendee->attendee_session;
	//$registration_id=$attendee->registration_id;
}

#$num_people = isset($num_people) && $num_people > 0 ? $num_people : espresso_count_attendees_for_registration($attendee_id);
#$event_meta = event_espresso_get_event_meta($event_id);
//	$event_data['additional_attendee_reg_info']
//if ($payment_status != 'Completed') {
//	$payment_status = 'Pending';
//	$txn_type = 'INV';
//	$payment_date = date('Y-m-d-H:i:s');

//Added by Imon
//	if (count($registration_ids) > 0 && $admin == false) {
//		foreach ($registration_ids as $reg_id) {
//			$sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET payment_status = '" . $payment_status . "', txn_type = '" . $txn_type . "', payment_date ='" . $payment_date . "'  WHERE registration_id ='" . $reg_id['registration_id'] . "' AND txn_type ='' ";
//			$wpdb->query($sql);
//		}
//	}
//}
//Query Database for event and get variable
/* 	$events = $wpdb->get_results("SELECT * FROM " . EVENTS_DETAIL_TABLE . " WHERE id='" . $event_id . "'");
  foreach ($events as $event){
  //$event_id = $event->id;
  $event_name = html_entity_decode(stripslashes($event->event_name),ENT_QUOTES,"UTF-8");
  $event_desc = $event->event_desc;
  $event_description = $event->event_desc;
  $event_identifier = $event->event_identifier;
  $start_date = $event->start_date;
  } */
//This is an example of how to get custom questions for an attendee
//Get the questions for the attendee
/* $q_sql = "SELECT ea.answer, eq.question
  FROM " . EVENTS_ANSWER_TABLE . " ea
  LEFT JOIN " . EVENTS_QUESTION_TABLE . " eq ON eq.id = ea.question_id
  WHERE ea.registration_id = '".$registration_id."'";
  $q_sql .= " AND ea.question_id = '9' ";
  $q_sql .= " ORDER BY eq.sequence asc ";
  $wpdb->get_results($q_sql);

  $organization_name = $wpdb->last_result[0]->answer;//question_id = '9' */


//Instanciation of inherited class
$pdf = new Espresso_PDF();
$pdf->AliasNbPages();
$pdf->SetAuthor(pdftext($org_options['organization']));
if (isset($invoice_payment_settings['pdf_title'])) {
	$pdf->SetTitle(pdftext($event_name . ' - ' . $invoice_payment_settings['pdf_title']));
} else {
	$pdf->SetTitle(pdftext($event_name));
}

//$pdf->SetAutoPageBreak('auto');
$pdf->AddPage();
//Create the top right of invoice below header
$pdf->SetFont('Times', '', 12);
$pdf->Cell(180, 0, pdftext(__('Date: ', 'event_espresso') . date(get_option('date_format'))), 0, 1, 'R'); //Set invoice date
$pdf->Cell(180, 10, pdftext(__('Primary Attendee ID: ', 'event_espresso') . $attendee_id), 0, 0, 'R'); //Set Invoice number
$pdf->Ln(0);

//Set the top left of invoice below header
$pdf->SetFont('Times', 'BI', 14);
if (isset($invoice_payment_settings['payable_to'])) {
	$pdf->MultiCell(0, 10, pdftext($invoice_payment_settings['payable_to']), 0, 'L'); //Set payable to
} else {
	$pdf->MultiCell(0, 10, pdftext(''), 0, 'L'); //Set payable to
}
$pdf->SetFont('Times', '', 12);
if (isset($invoice_payment_settings['payment_address'])) {
	$pdf->MultiCell(50, 5, pdftext($invoice_payment_settings['payment_address']), 0, 'L'); //Set address
} else {
	$pdf->MultiCell(50, 5, pdftext(''), 0, 'L'); //Set address
}
$pdf->Ln(5);

//Set the biiling information
$pdf->SetFont('Times', 'B', 12);
$pdf->Cell(50, 5, pdftext(__('Bill To: ', 'event_espresso')), 0, 1, 'L'); //Set biil to
$pdf->SetFont('Times', '', 12);
$pdf->Cell(50, 5, pdftext($attendee_first . ' ' . $attendee_last), 0, 1, 'L'); //Set attendee name
$pdf->Cell(50, 5, pdftext($attendee_email), 0, 1, 'L'); //Set attendee email
//Set attendee address
$attendee_address != '' ? $pdf->Cell(100, 5, pdftext($attendee_address), 0, 1, 'L') : '';
$pdf->Cell(100, 5, (pdftext($attendee_city != '' ? $attendee_city : '') . ($attendee_state != '' ? ' ' . $attendee_state : '')), 0, 1, 'L');
$attendee_zip != '' ? $pdf->Cell(50, 5, pdftext($attendee_zip), 0, 1, 'L') : '';

$pdf->Ln(10);

//Added by Imon
$attendees = array();
$total_cost = 0.00;
$total_orig_cost = 0.00;
$total_amount_pd = 0.00;
foreach ($registration_ids as $reg_id) {
	$sql = "select ea.registration_id, ed.event_name, ed.start_date, ed.event_identifier, ea.fname, ea.lname, ea.quantity, ea.orig_price, ea.final_price, ea.amount_pd from " . EVENTS_ATTENDEE_TABLE . " ea ";
	//$sql .= " inner join " . EVENTS_ATTENDEE_COST_TABLE . " eac on ea.id = eac.attendee_id ";
	$sql .= " inner join " . EVENTS_DETAIL_TABLE . " ed on ea.event_id = ed.id ";
	$sql .= " where ea.registration_id = '" . $reg_id['registration_id'] . "' order by ed.event_name ";

	$tmp_attendees = $wpdb->get_results($sql, ARRAY_A);

	foreach ($tmp_attendees as $tmp_attendee) {
		$sub_total = $tmp_attendee["final_price"] * $tmp_attendee["quantity"];
		$orig_total = $tmp_attendee["orig_price"] * $tmp_attendee["quantity"];
		$attendees[] = $pdf->LoadData(array(
			pdftext($tmp_attendee["event_name"] . "[" . date('m-d-Y', strtotime($tmp_attendee['start_date'])) . "]") . ' >> '
			. pdftext(html_entity_decode($tmp_attendee["fname"], ENT_QUOTES, "UTF-8") . " " . html_entity_decode($tmp_attendee["lname"], ENT_QUOTES, "UTF-8")) . ';'
			. pdftext($tmp_attendee["quantity"]) . ';'
			. doubleval($tmp_attendee["final_price"]) . ';'
			. doubleval($sub_total)
				)
		);
		$total_cost += $sub_total;
		$total_orig_cost += $orig_total;
		$total_amount_pd += $tmp_attendee["amount_pd"];
		$event_identifier = $tmp_attendee["event_identifier"];
	}
}
$header = array(pdftext(__('Event & Attendee', 'event_espresso')), pdftext(__('Quantity', 'event_espresso')), pdftext(__('Per Unit', 'event_espresso')), pdftext(__('Sub total', 'event_espresso')));
$w = array(100, 25, 30, 30);
$alling = array('L', 'L', 'C', 'C', 'C');
$left = 100 + 25 + 30;
$right = 30;

$pdf->ImprovedTable($header, $attendees, $w, $alling);

$pdf->Ln();
//if ( $total_amount_pd != $total_cost ) {
$pdf->InvoiceTotals(pdftext(__('Total:', 'event_espresso')), $total_cost, $left, $right);
$text = pdftext(__('Amount Paid:', 'event_espresso'));
$pdf->InvoiceTotals($text, $total_amount_pd, $left, $right);
//	$discount = $total_orig_cost - $total_cost;
//	if ($discount > 0) {
//		$text = __('Discount:', 'event_espresso');
//	} else {
//		$text = __('Discount:', 'event_espresso');
//		$pdf->InvoiceTotals($text, $discount, $left, $right);
//	}
//}
$total_owing = $total_cost - $total_amount_pd;
$text = pdftext(__("Total due:", 'event_espresso'));
$pdf->InvoiceTotals($text, $total_owing, $left, $right);
$pdf->Ln(10);

//Build the payment link and instructions
if (isset($invoice_payment_settings['pdf_instructions'])) {
	$pdf->MultiCell(100, 5, pdftext($invoice_payment_settings['pdf_instructions']), 0, 'L'); //Set instructions
} else {
	$pdf->MultiCell(100, 5, pdftext(''), 0, 'L'); //Set instructions
}

//Create a payment link
$payment_link = home_url() . "/?page_id=" . $org_options['return_url'] . "&r_id=" . $registration_id;

$pdf->SetFont('Arial', 'BU', 20);
add_action( 'action_hook_espresso_invoice_payment_link', 'espresso_invoice_payment_link', 10, 2 );
function espresso_invoice_payment_link( $pdf, $payment_link ){
	$pdf->Cell(200, 20, pdftext(__('Pay Online', 'event_espresso')), 0, 1, 'C', 0, $payment_link); //Set payment link
}
do_action( 'action_hook_espresso_invoice_payment_link', $pdf, $payment_link );

$pdf->Output('Invoice_' . $attendee_id . '_' . $event_identifier . '.pdf', 'D');
exit;
