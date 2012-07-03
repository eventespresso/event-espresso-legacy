<?php
//--------Do not use. This file is deprecated.-------//

function event_form_build(&$question, $answer = "") {

	$required = '';
	if ($question->required == "Y") {
		$required = ' class="r"';
	}
	switch ($question->question_type) {
		case "TEXT" :
			echo "<input type=\"text\"$required id=\"TEXT_$question->id\"  name=\"TEXT_$question->id\" size=\"40\" title=\"$question->question\" value=\"$answer\" />\n";
			break;
		
		case "TEXTAREA" :
			echo "<textarea id=\"TEXTAREA_$question->id\"$required name=\"TEXTAREA_$question->id\" title=\"$question->question\" cols=\"30\" rows=\"5\">$answer</textarea>\n";
			break;
		
		case "SINGLE" :
			$values = explode ( ",", $question->response );
			$answers = explode ( ",", $answer );
			
			foreach ( $values as $key => $value ) {
				$checked = in_array ( $value, $answers ) ? " checked=\"checked\"" : "";
				echo "<label><input id=\"SINGLE_$question->id_$key\"$required name=\"SINGLE_$question->id\" title=\"$question->question\" type=\"radio\" value=\"$value\"$checked /> $value</label><br/>\n";
			}
			break;
		
		case "MULTIPLE" :
			$values = explode ( ",", $question->response );
			$answers = explode ( ",", $answer );
			foreach ( $values as $key => $value ) {
				$checked = in_array ( $value, $answers ) ? " checked=\"checked\"" : "";
			/*	echo "<label><input type=\"checkbox\"$required id=\"MULTIPLE_$question->id_$key\" name=\"MULTIPLE_$question->id_$key\" title=\"$question->question\" value=\"$value\"$checked /> $value</label><br/>\n"; */
			echo "<label><input id=\"$value\"$required name=\"MULTIPLE_$question->id[]\" title=\"$question->question\" type=\"checkbox\" value=\"$value\"$checked /> $value</label><br/>\n";
			}
			break;
		
		case "DROPDOWN" :
			$values = explode ( ",", $question->response );
			$answers = explode ( ",", $answer );
			echo "<select name=\"DROPDOWN_$question->id\"$required id=\"DROPDOWN_$question->id\" title=\"$question->question\" />".BR;
			echo "<option value=''>Select One </option><br/>";
			foreach ( $values as $key => $value ) {
				$checked = in_array ( $value, $answers ) ? " selected =\" selected\"" : "";
				echo "<option value=\"$value\" /> $value</option><br/>\n";
			}
			echo "</select>";
			break;
		
		default :
			break;
	}
}


function event_form_build_edit ($question, $edits) {
	$required = '';
	if ($question->required == "Y") {
		$required = ' class="r"';
	}
	switch ($question->question_type) {
		case "TEXT" :
			echo "<input type=\"text\"$required id=\"TEXT_$question->id\"  name=\"TEXT_$question->id\" size=\"40\" title=\"$question->question\" value=\"$edits\" />\n";
			break;
		
		case "TEXTAREA" :
			echo "<textarea id=\"TEXTAREA_$question->id\"$required name=\"TEXTAREA_$question->id\" title=\"$question->question\" cols=\"30\" rows=\"5\">".$edits."</textarea>\n";
			break;
		
		case "SINGLE" :
			$values = explode ( ",", $question->response );
			$answers = explode ( ",", $edits );
			foreach ( $values as $key => $value ) {
				$checked = in_array ( $value, $answers ) ? " checked=\"checked\"" : "";
				echo " <label><input id=\"SINGLE_$question->id_$key\"$required name=\"SINGLE_$question->id\" title=\"$question->question\" type=\"radio\" value=\"$value\"$checked /> $value</label>  ";
			}
			echo "</br>\n";
			break;
		
		case "MULTIPLE" :
			$values = explode ( ",", $question->response );
			$answers = explode ( ",", $edits );
			foreach ( $values as $key => $value ) {
				$checked = in_array ( $value, $answers ) ? " checked=\"checked\"" : "";
			/*	echo "<label><input type=\"checkbox\"$required id=\"MULTIPLE_$question->id_$key\" name=\"MULTIPLE_$question->id_$key\" title=\"$question->question\" value=\"$value\"$checked /> $value</label><br/>\n"; */
			echo " <label><input id=\"$value\"$required name=\"MULTIPLE_$question->id[]\" title=\"$question->question\" type=\"checkbox\" value=\"$value\"$checked /> $value</label>  ";
			}
			echo "</br>\n";
			break;
		
		case "DROPDOWN" :
			$values = explode ( ",", $question->response );
			//$answers = explode ( ",", $edits );
			echo "<select name=\"DROPDOWN_$question->id\"$required id=\"DROPDOWN_$question->id\" title=\"$question->question\" />".BR;
			echo "<option value=\"$edits\">$edits</option><br/>";
			foreach ( $values as $key => $value ) {
				//$checked = in_array ( $value, $answers ) ? " selected =\" selected\"" : "";
					echo "<option value=\"$value\" /> $value</option><br/>\n";
			}
			echo "</select>";
			break;
		
		default :
			break;
	}
}
?>