<?php
function espresso_ticket($attendee_id=0){
define('FPDF_FONTPATH', EVENT_ESPRESSO_PLUGINFULLPATH . 'class/fpdf/font/');
require_once EVENT_ESPRESSO_PLUGINFULLPATH . 'class/fpdf/fpdf.php';
global $wpdb, $org_options;

$attendees = $wpdb->get_results("SELECT * FROM ". EVENTS_ATTENDEE_TABLE ." WHERE id ='" .$attendee_id. "' LIMIT 0,1");
	foreach ($attendees as $attendee){
		$attendee_id = $attendee->id;
		$attendee_last = $attendee->lname;
		$attendee_first = $attendee->fname;
		$attendee_address = $attendee->address;
		$attendee_city = $attendee->city;
		$attendee_state = $attendee->state;
		$attendee_zip = $attendee->zip;
		$attendee_email = $attendee->email;
		$ticket_type = $attendee->price_option;
		//$attendee_organization_name = $attendee->organization_name;
		//$attendee_country = $attendee->country_id;
		$phone = $attendee->phone;
		$date = $attendee->date;
		//$num_people = $attendee->quantity;
		$payment_status = $attendee->payment_status;
		$txn_type = $attendee->txn_type;
		$amount_pd = $attendee->amount_pd;
		$payment_date = $attendee->payment_date;
		$event_id = $attendee->event_id;
		$registration_id=$attendee->registration_id;
	}

	//Query Database for event and get variable
	$events = $wpdb->get_results("SELECT * FROM " . EVENTS_DETAIL_TABLE . " WHERE id='" . $event_id . "'");
		foreach ($events as $event){
			//$event_id = $event->id;
			$event_name = html_entity_decode(stripslashes($event->event_name),ENT_QUOTES);
			$event_desc = $event->event_desc;
			$event_description = $event->event_desc;
			$event_identifier = $event->event_identifier;
			$start_date = $event->start_date;
	}
//Create a payment link
$payment_link = urlencode(get_option('siteurl') . "/?page_id=" . $org_options['return_url'] . "&registration_id=" . $registration_id);

/**
 * Get either a Gravatar URL or complete image tag for a specified email address.
 *
 * @param string $email The email address
 * @param string $s Size in pixels, defaults to 80px [ 1 - 512 ]
 * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
 * @param boole $img True to return a complete IMG tag False for just the URL
 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
 * @return String containing either just a URL or a complete image tag
 * @source http://gravatar.com/site/implement/images/php/
 */
 if (!function_exists('espresso_get_gravatar')) {

function espresso_get_gravatar( $email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array() ) {
	$url = 'http://www.gravatar.com/avatar/';
	$url .= md5( strtolower( trim( $email ) ) );
	$url .= "?s=$s&d=$d&r=$r";
	if ( $img ) {
		$url = '<img src="' . $url . '"';
		foreach ( $atts as $key => $val )
			$url .= ' ' . $key . '="' . $val . '"';
		$url .= ' />';
	}
	return $url;
}
 }

//Build the PDF			
$pdf=new FPDF();
$pdf->AliasNbPages();
$pdf->SetAuthor($org_options['organization']);
$pdf->SetTitle($event_name);

//$pdf->SetAutoPageBreak('auto');
$pdf->AddPage();
//Create the top right of invoice below header
$pdf->SetFont('Times','',12);

//Build the border
$pdf->SetLineWidth(.5);
$pdf->Rect(20, 20, 170, 54);//Rect(float x, float y, float w, float h [, string style]) 

//Build the attendee info

/* Attendee Name */
$pdf->SetXY(95,8);
//Set font
$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(123,123,123);
//Move to 1 cm to the right
$pdf->Cell(10);
//Centered text in a framed 20*10 mm cell and line break
$pdf->Cell(75,40,__('Name:','event_espresso'),0,'0','L');//Cell(float w [, float h [, string txt [, mixed border [, int ln [, string align [, boolean fill [, mixed link]]]]]]])
$pdf->SetXY(95,13);
//Set font
$pdf->SetFont('Arial','',12);
$pdf->SetTextColor(0,0,0);
//Move to 1 cm to the right
$pdf->Cell(10);
//Centered text in a framed 20*10 mm cell and line break
$pdf->Cell(75,40,$attendee_first.' '.$attendee_last ,0,'0','L');//Cell(float w [, float h [, string txt [, mixed border [, int ln [, string align [, boolean fill [, mixed link]]]]]]])

/* Attendee Email */
$pdf->SetXY(95,18);
//Set font
$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(123,123,123);
//Move to 1 cm to the right
$pdf->Cell(10);
//Centered text in a framed 20*10 mm cell and line break
$pdf->Cell(75,40,__('Email:','event_espresso'),0,'0','L');//Cell(float w [, float h [, string txt [, mixed border [, int ln [, string align [, boolean fill [, mixed link]]]]]]])
$pdf->SetXY(95,23);
//Set font
$pdf->SetFont('Arial','',12);
$pdf->SetTextColor(0,0,0);
//Move to 1 cm to the right
$pdf->Cell(10);
//Centered text in a framed 20*10 mm cell and line break
$pdf->Cell(75,40,$attendee_email ,0,'0','L');//Cell(float w [, float h [, string txt [, mixed border [, int ln [, string align [, boolean fill [, mixed link]]]]]]])

/* Event Name */
$pdf->SetXY(95,28);
//Set font
$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(123,123,123);
//Move to 1 cm to the right
$pdf->Cell(10);
//Centered text in a framed 20*10 mm cell and line break
$pdf->Cell(75,40,__('Event:','event_espresso'),0,'0','L');//Cell(float w [, float h [, string txt [, mixed border [, int ln [, string align [, boolean fill [, mixed link]]]]]]])

$pdf->SetXY(95,33);
//Set font
$pdf->SetFont('Arial','',12);
$pdf->SetTextColor(0,0,0);
//Move to 1 cm to the right
$pdf->Cell(10);
//Centered text in a framed 20*10 mm cell and line break
$pdf->Cell(75,40,$event_name,0,'0','L');//Cell(float w [, float h [, string txt [, mixed border [, int ln [, string align [, boolean fill [, mixed link]]]]]]])  

if ($ticket_type!=''){
	/* Ticket Type */
	$pdf->SetXY(95,38);
	//Set font
	$pdf->SetFont('Arial','',10);
	$pdf->SetTextColor(123,123,123);
	//Move to 1 cm to the right
	$pdf->Cell(10);
	//Centered text in a framed 20*10 mm cell and line break
	$pdf->Cell(75,40,__('Ticket type:','event_espresso'),0,'0','L');//Cell(float w [, float h [, string txt [, mixed border [, int ln [, string align [, boolean fill [, mixed link]]]]]]])
	
	$pdf->SetXY(95,43);
	//Set font
	$pdf->SetFont('Arial','',12);
	$pdf->SetTextColor(0,0,0);
	//Move to 1 cm to the right
	$pdf->Cell(10);
	//Centered text in a framed 20*10 mm cell and line break
	$pdf->Cell(75,40,$ticket_type,0,'0','L');//Cell(float w [, float h [, string txt [, mixed border [, int ln [, string align [, boolean fill [, mixed link]]]]]]])  
}

//Build the QR Code
$qr_data = 'http://chart.apis.google.com/chart?cht=qr&chld=H&chs=100x100&chl='.$event_name.'%0A'.$attendee_id.'+-+'.$attendee_first . '+' . $attendee_last.'%0A'.$attendee_email.'%0A'.$registration_id.'%0A'.$payment_link;
$pdf->Image($qr_data ,'21','22',50,50,'PNG');//Image(string file [, float x [, float y [, float w [, float h [, string type [, mixed link]]]]]])

//Build the Gravatar
$gravatar = espresso_get_gravatar( $attendee_email, $size = '100', $default = 'http://www.gravatar.com/avatar/' );
$pdf->Image($gravatar,65,28,0,0,'JPG'); //Image(string file [, float x [, float y [, float w [, float h [, string type [, mixed link]]]]]])

$pdf->Output($attendee_id.'-'.$event_identifier.'.pdf','D');
}