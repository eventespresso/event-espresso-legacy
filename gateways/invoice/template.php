<?php
define('FPDF_FONTPATH', EVENT_ESPRESSO_PLUGINFULLPATH . 'class/fpdf/font/');
unset($_SESSION['espresso_session_id']);
require_once EVENT_ESPRESSO_PLUGINFULLPATH . 'class/fpdf/fpdf.php';
global $espresso_premium; if ($espresso_premium != true) return;
global $wpdb, $org_options;
$invoice_payment_settings = get_option('event_espresso_invoice_payment_settings');

$attendees = $wpdb->get_results("SELECT * FROM ". EVENTS_ATTENDEE_TABLE ." WHERE registration_id ='" . $_REQUEST['registration_id'] . "' LIMIT 0,1");
	foreach ($attendees as $attendee){
		$attendee_id = $attendee->id;
		$attendee_last = $attendee->lname;
		$attendee_first = $attendee->fname;
		$attendee_address = $attendee->address;
		$attendee_city = $attendee->city;
		$attendee_state = $attendee->state;
		$attendee_zip = $attendee->zip;
		$attendee_email = $attendee->email;
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
	
	$payment_status= 'Pending';
	$txn_type = 'INV';
	$payment_date = date("m-d-Y");
	
	$sql = "UPDATE ". EVENTS_ATTENDEE_TABLE . " SET payment_status = '" . $payment_status . "', txn_type = '" . $txn_type . "', payment_date ='" . $payment_date . "'  WHERE registration_id ='" . espresso_registration_id($attendee_id) . "'";

	$wpdb->query($sql);

	//Query Database for event and get variable
	$events = $wpdb->get_results("SELECT * FROM " . EVENTS_DETAIL_TABLE . " WHERE id='" . $event_id . "'");
		foreach ($events as $event){
			//$event_id = $event->id;
			$event_name = html_entity_decode(stripslashes($event->event_name),ENT_QUOTES,"UTF-8");
			$event_desc = $event->event_desc;
			$event_description = $event->event_desc;
			$event_identifier = $event->event_identifier;
			$start_date = $event->start_date;
	}
	
	//This is an example of how to get custom questions for an attendee
	//Get the questions for the attendee
		/*$q_sql = "SELECT ea.answer, eq.question 
					FROM " . EVENTS_ANSWER_TABLE . " ea 
					LEFT JOIN " . EVENTS_QUESTION_TABLE . " eq ON eq.id = ea.question_id
					WHERE ea.registration_id = '".$registration_id."'";
		$q_sql .= " AND ea.question_id = '9' ";
		$q_sql .= " ORDER BY eq.sequence asc ";
		$wpdb->get_results($q_sql);
		
		$organization_name = $wpdb->last_result[0]->answer;//question_id = '9'*/
		
		
//Build the PDF			
class PDF extends FPDF
{
	//Page header
	function Header()
	{
		global $wpdb, $org_options;
		$invoice_payment_settings = get_option('event_espresso_invoice_payment_settings');
		//Logo
		if (trim($invoice_payment_settings['image_url']) !=''){
			$this->Image($invoice_payment_settings['image_url'],10,8,90);//Set the logo if it is available
		}else{
			$this->SetFont('Arial','B',15);
			$this->Cell(10,10,$org_options['organization'],0,0,'L');//If no logo, then display the organizatin name
		}
			
		//Arial bold 15
		$this->SetFont('Arial','B',15);
		//Move to the right
		$this->Cell(80);
		//Title
		$this->MultiCell(100,10,$invoice_payment_settings['pdf_title'],0,'R');//Set the right header
		//Line break
		$this->Ln(20);
	}
	
	function LoadData($file)
	{
		$lines=$file;
		$data=array();
		foreach($lines as $line)
			$data[]=explode(';',chop($line));
		return $data;
	}
	
	
	//Better table
	function ImprovedTable($header,$data)
	{
		//Column widths
		$w=array(100,35,40);
		//Header
		for($i=0;$i<count($header);$i++)
			$this->Cell($w[$i],7,$header[$i],1,0,'C');
		$this->Ln();
		
			$x = $this->GetX();
			$y = $this->GetY();
	
		//Data
		foreach($data as $row)
		{
				$y1 = $this->GetY();
	
			$this->MultiCell($w[0],6,$row[0],'LR');
	  
				$y2 = $this->GetY();
				$yH = $y2 - $y1;
							
				$this->SetXY($x + $w[0], $this->GetY() - $yH);
	  
			$this->Cell($w[1],$yH,$row[1],'LR',0,'C');
			$this->Cell($w[2],$yH,number_format($row[2],2, '.', ''),'LR',0,'R');
		  //  $this->Cell($w[3],6,number_format($row[3]),'LR',0,'R');
			$this->Ln();
		}
		//Closure line
		$this->Cell(array_sum($w),0,'','T');
	}
	
	//Page footer
	function Footer()
	{
		//Position at 1.5 cm from bottom
		$this->SetY(-15);
		//Arial italic 8
		$this->SetFont('Arial','I',8);
		//Page number
		$this->Cell(0,10, __('Page','event_espresso').$this->PageNo().'/{nb}',0,0,'C');
	}
}

//Create a payment link
$payment_link = home_url() . "/?page_id=" . $org_options['return_url'] . "&id=" . $attendee_id;

//Instanciation of inherited class
$pdf=new PDF();
$pdf->AliasNbPages();
$pdf->SetAuthor($org_options['organization']);
$pdf->SetTitle($event_name . ' - ' . $invoice_payment_settings['pdf_title']);

//$pdf->SetAutoPageBreak('auto');
$pdf->AddPage();
//Create the top right of invoice below header
$pdf->SetFont('Times','',12);
$pdf->Cell(180,0, __('Date: ','event_espresso'). date('m-d-Y'),0,1, 'R');//Set invoice date
$pdf->Cell(180,10,__('Attendee ID: ','event_espresso'). $attendee_id,0,0, 'R');//Set Invoice number
$pdf->Ln(0);

//Set the top left of invoice below header
$pdf->SetFont('Times','BI',14);
$pdf->MultiCell(0,10,$invoice_payment_settings['payable_to'],0,'L');//Set payable to
$pdf->SetFont('Times','',12);
$pdf->MultiCell(50,5,$invoice_payment_settings['payment_address'],0, 'L');//Set address
$pdf->Ln(5);

//Set the biiling information
$pdf->SetFont('Times','B',12);
$pdf->Cell(50,5,__('Bill To: ','event_espresso'),0,1,'L');//Set biil to
$pdf->SetFont('Times','',12);
$pdf->Cell(50,5,$attendee_first . ' ' . $attendee_last,0,1, 'L');//Set attendee name
$pdf->Cell(50,5,$attendee_email,0,1,'L');//Set attendee email
//Set attendee address
$attendee_address != '' ? $pdf->Cell(100,5,$attendee_address,0,1,'L') :'';
$pdf->Cell(100,5,($attendee_city != '' ? $attendee_city :''). ($attendee_state != '' ? ' ' . $attendee_state :''),0,1,'L');
$attendee_zip != '' ? $pdf->Cell(50,5,$attendee_zip,0,1,'L') :'';

$pdf->Ln(10);

//Build the table for the event details
//Column titles
$header=array(__('Event Name','event_espresso'),__('Event Date','event_espresso'),__('Amount Owed','event_espresso'));
//Event Data
$data = $pdf->LoadData (array($event_name . ';' . date('m-d-Y',strtotime($start_date)) . ';' . $org_options['currency_format'].$amount_pd));
$pdf->ImprovedTable($header, $data);
$pdf->Ln(10);

//Build the payment link and instructions
$pdf->MultiCell(100,5,$invoice_payment_settings['pdf_instructions'],0,'L');//Set instructions

$pdf->SetFont('Arial','BU',20);
//$pdf->Cell(200,20,'Pay Online',0,1,'C',0,$payment_link);//Set payment link

$pdf->Output($event_identifier.'.pdf','D');