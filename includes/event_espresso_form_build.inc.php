<?php

if (!function_exists('event_form_build')) {
	function event_form_build(&$question, $answer = "") {
		$required = '';
             /**
                 * Temporary client side email validation solution by Abel, will be replaced
                 * in the next version with a full validation suite.
                 */
        $email_validate = $question->system_name == 'email'?'email':'';

		if ($question->required == "Y") {
			$required = ' title="' .$question->required_text . '" class="required ' . $email_validate . '"';
		}
			$field_name = (!is_null($question->system_name))?$question->system_name:$question->question_type . '_' . $question->id;
			$label = '<label for="' . $field_name . '">' . $question->question . '</label><br>';
			switch ($question->question_type) {
			case "TEXT" :
				echo  '<p class="event_form_field">' . $label;
				echo '<input type="text" '. $required . ' id="' . $field_name . '"  name="' . $field_name . '" size="40" value="' . $answer . '" /></p>';
				break;
			case "TEXTAREA" :
				echo  '<p class="event_form_field">' . $label;
				echo '<textarea id="TEXTAREA_' . $question->id . '"' . $required . ' name="TEXTAREA_' . $question->id .'"  cols="30" rows="5">' . $answer . '</textarea></p>';
				break;
			case "SINGLE" :
				$values = explode ( ",", $question->response );
				$answers = explode ( ",", $answer );
				echo  '<p class="event_form_field">' . $label;
				echo '</p>';
				echo '<ul class="event_form_field">';
				foreach ( $values as $key => $value ) {
					$checked = in_array ( $value, $answers ) ? ' checked="checked"' : "";
					echo '<li><label><input title="' . $question->required_text .'" id="SINGLE_' . $question->id . '_' . $key . '" ' . $required .' name="SINGLE_' . $question->id . '"  type="radio" value="' . $value . '" ' . $checked . ' />' . $value . '</label></li>';
				}
				echo '</ul>';
				break;
			case "MULTIPLE" :
				$values = explode ( ",", $question->response );
				$answers = explode ( ",", $answer );
				echo $label;
				echo '<ul class="event_form_field">';
				foreach ( $values as $key => $value ) {
					$checked = in_array ( $value, $answers ) ? ' checked="checked"' : "";
				/*	echo "<label><input type=\"checkbox\"$required id=\"MULTIPLE_$question->id_$key\" name=\"MULTIPLE_$question->id_$key\"  value=\"$value\"$checked /> $value</label><br/>\n"; */
				echo '<li><label><input id="' . $value . '" ' . $required . 'name="MULTIPLE_' . $question->id . '[]"  type="checkbox" value="' . $value . '" ' . $checked . '/> ' . $value . '</label></li>';
				}
				echo '</ul>';
				break;
			case "DROPDOWN" :
				$values = explode ( ",", $question->response );
				$answers = explode ( ",", $answer );
				echo '<p class="event_form_field">' . $label;
				echo '<select name="DROPDOWN_' . $question->id . '" ' . $required . ' id="DROPDOWN_' . $question->id . '"  />';
				echo "<option value=''>Select One </option><br/>";
				foreach ( $values as $key => $value ) {
					$checked = in_array ( $value, $answers ) ? ' selected=" selected"' : "";
					echo '<option value="' . $value . '" /> ' . $value . '</option></p>';
				}
				echo "</select>";
				break;
			default :
				break;
		}
	}
}

function event_form_build_edit ($question, $edits) {
	$required = '';
	if ($question->required == "Y") {
		$required = ' class="required"';
	}
	$field_name = (!is_null($question->system_name))?$question->system_name:'TEXT_' . $question->id;
	 echo '<label for="' . $field_name . '">' . $question->question . '</label><br>';
	switch ($question->question_type) {
		case "TEXT" :
			echo '<input type="text" ' . $required . ' id="' . $field_name .'"  name="' . $field_name .'" size="40"  value="' . $edits . '" />';
			break;
		case "TEXTAREA" :
			echo '<textarea id="TEXTAREA_' . $question->id . '" ' . $required . ' name="TEXTAREA_' . $question->id . '"  cols="30" rows="5">' . $edits . '</textarea>';
			break;
		case "SINGLE" :
			$values = explode ( ",", $question->response );
			$answers = explode ( ",", $edits );
			echo '<ul>';
			foreach ( $values as $key => $value ) {
				$checked = in_array ( $value, $answers ) ? ' checked="checked"' : "";
				echo '<li><input id="SINGLE_' . $question->id . '_' . $key . '" ' . $required . ' name="SINGLE_' . $question->id . '"  type="radio" value="' . $value . '" ' . $checked . '/> ' . $value . '</li>';
			}
			echo "</ul>";
			break;
		case "MULTIPLE" :
			$values = explode ( ",", $question->response );
			$answers = explode ( ",", $edits );
			echo '<ul>';
			foreach ( $values as $key => $value ) {
				$checked = in_array ( $value, $answers ) ? " checked=\"checked\"" : "";
			/*	echo "<label><input type=\"checkbox\"$required id=\"MULTIPLE_$question->id_$key\" name=\"MULTIPLE_$question->id_$key\"  value=\"$value\"$checked /> $value</label><br/>\n"; */
			echo '<li><input id="' . $value . '" ' . $required . ' name="MULTIPLE_' . $question->id . '[]"  type="checkbox" value="' . $value . '" ' . $checked . '/> ' . $value . '</li>';
			}
			echo "</ul>";
			break;
		case "DROPDOWN" :
			$values = explode ( ",", $question->response );
			//$answers = explode ( ",", $edits );
			echo '<select name="DROPDOWN_' . $question->id .'" ' . $required . ' id="DROPDOWN_' . $question->id . '"  />';
			echo '<option value="' . $edits . '">' . $edits . '</option>';
			foreach ( $values as $key => $value ) {
				//$checked = in_array ( $value, $answers ) ? " selected =\" selected\"" : "";
					echo '<option value="' . $value . '" /> ' . $value . '</option>';
			}
			echo "</select>";
			break;
		default :
			break;
	}
}
?>