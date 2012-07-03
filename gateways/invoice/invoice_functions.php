<?php
//Retunrs an array of available template files
function espresso_invoice_template_files() {
	// read our template dir and build an array of files
	if (file_exists(EVENT_ESPRESSO_UPLOAD_DIR . "invoice/css/index.php")) {
		$dhandle = opendir(EVENT_ESPRESSO_UPLOAD_DIR . 'invoice/css/');//If the template files have been moved to the uplaods folder
	} else {
		$dhandle = opendir(EVENT_ESPRESSO_PLUGINFULLPATH . 'gateways/invoice/templates/css/');
	}

	$files = array();

	if ($dhandle) { //if we managed to open the directory
		// loop through all of the files
		while (false !== ($fname = readdir($dhandle))) {
			// if the file is not this file, and does not start with a '.' or '..',
			// then store it for later display
			if ( $fname != '.' ){
				if ( $fname != 'index.php' ){
					if ( $fname != '..' ){
						if ( $fname != '.svn' ){
							if ( $fname != basename($_SERVER['PHP_SELF']) ){
								if ( $fname != '.DS_Store' ) {
									if ( $fname != 'images' ) {
										if ( $fname != 'print' ) {
											// store the filename
											$files[] = $fname;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		// close the directory
		closedir($dhandle);
	}

	return $files;
}

//Checks to see if the invoice is selected
function espresso_invoice_is_selected($name, $selected='') {
	$input_item = $name;
	$option_selections = array($selected);
	if (!in_array( $input_item, $option_selections )  ){
		return false;
	}else{
		echo  'selected="selected"';
	   return;
	}
}