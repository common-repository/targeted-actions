<?php
namespace StrategicPlugins\TargetedActions;

if ( !defined( 'WPINC' ) ) {
    die;
}

class Form {

	function __construct() {
		if(!isset($_SESSION)) {
			session_start();
		}
	}

	public static function getArg($name) {

		if(isset($_POST[$name])) {
			return sanitize_text_field($_POST[$name]);
		}
		elseif(isset($_GET[$name])) {
			return sanitize_text_field($_GET[$name]);
		}

		return "";
	}

	/**
	 * Gets POSTed HTML and uses wp_kses to remove everything not on the safelist
	 * 
	 * @param  string $name [The form field name]
	 * @return string       [Sanitized HTML]
	 */
	public static function getPostHTML($name) {

		if(current_user_can("manage_options")) {

			/* HTML functionality for site admins only */

			if(isset($_POST[$name])) {
				return addslashes( wp_kses( stripslashes( $_POST[$name] ), TargetedActions::allowedHTML() ) );
			}
		}

		return "";

	}

	/**
	 * Builds a form input box
	 * @return [string] HTML for the input box
	 */
	public static function input($field_name, $args = []) {

		/* 
			Allowed arguments:

			id [string]: 				The id of the form field
			label [string]: 			The label of the form field
			wrapper_class [string]: 	A wrapper class for the form field
			field_class [string]: 		A class for the form field
			type [string]: 				Type of input, defaults to text
			required [bool]: 			Whether the form field is required
			value [string]:				The field value

		*/

		// Form Field ID
	
		$id = $field_name;

		if(isset($args["id"]) AND $args["id"] != "") {
			$id = $args["id"];
		}
		 
		$label = '';
		$required = '';

		// Form field label

		if(isset($args["label"]) AND $args["label"] != "") {
			$label = '<label for="'.$id.'" class="form-field-label">' . $args['label'] . (isset($args['required']) && $args['required'] === true ? '<span class="required">*</span>' : '') . '</label>';
		}

		// Form field required

		if(isset($args['required']) && $args['required'] === true) {
			$required = ' required="required"';
		}

		$field_html = '
			<div class="custom-form-field input' . (isset($args['wrapper_class']) && $args['wrapper_class'] != "" ? ' ' . $args['wrapper_class'] : '') . '">
				'.$label.'
				<input name="'. $field_name .'" id="' . $id . '" class="field field-input' . (isset($args['field_class']) ? ' ' . $args['field_class'] : '') . '" type="' . (isset($args['type']) && $args['type'] != "" ? $args['type'] : 'text') . '" value="'. (isset($args['value']) ? $args['value'] : '') .'" '.$required.'/>
			</div>
		';

		return $field_html;

	}


	/**
	 * Builds a form textarea
	 * @return [string] HTML for the textarea
	 */
	public static function textarea($field_name, $args = []) {

		/* 
			Allowed arguments:

			id [string]: 				The id of the form field
			label [string]: 			The label of the form field
			wrapper_class [string]: 	A wrapper class for the form field
			field_class [string]: 		A class for the form field
			required [bool]: 			Whether the form field is required
			value [string]:				The value of the form field

		*/

		// Form Field ID
	
		$id = $field_name;

		if(isset($args["id"]) AND $args["id"] != "") {
			$id = $args["id"];
		} 

		$label = '';
		$required = '';

		// Form field label

		if(isset($args["label"]) AND $args["label"] != "") {
			$label = '<label for="'.$id.'" class="form-field-label">' . $args['label'] . (isset($args['required']) && $args['required'] === true ? '<span class="required">*</span>' : '') . '</label>';
		}

		// Form field required

		if(isset($args['required']) && $args['required'] === true) {
			$required = ' required="required"';
		}

		$field_html = '
			<div class="custom-form-field textarea' . (isset($args['wrapper_class']) && $args['wrapper_class'] != "" ? ' ' . $args['wrapper_class'] : '') . '">
				'.$label.'
				<textarea name="'. $field_name .'" id="' . $id . '" class="field field-input' . (isset($args['field_class']) ? ' ' . $args['field_class'] : '') . '" type="' . (isset($args['type']) && $args['type'] != "" ? $args['type'] : 'text') . '" '.$required.'/>'.(isset($args['value']) ? $args['value'] : '').'</textarea>
			</div>
		';

		return $field_html;

	}

	/**
	 * Builds an HTML select box
	 * @param  [string] $field_name [The name of the field element]
	 * @param  [array] $choices    	[A key / value array of choices]
	 * @param  [array] $args       	[Additional field args]
	 * @return [string]             [The field HTML]
	 */
	public static function select($field_name, $choices, $args = []) {

		/* 
			Allowed arguments:

			id [string]: 				The id of the form field
			label [string]: 			The label of the form field
			wrapper_class [string]: 	A wrapper class for the form field
			field_class [string]: 		A class for the form field
			required [bool]: 			Whether the form field is required
			value [string]:				The key of the selected value
			default_option[string]:		The default option shown when no choice selected

		*/
	
		// Form Field ID
	
		$id = $field_name;

		if(isset($args["id"]) AND $args["id"] != "") {
			$id = $args["id"];
		}

		// Form field required
		
		$required = false;

		if(isset($args['required']) && $args['required'] === true) {
			$required = ' required="required"';
		}


		// Field label
		
		$label = '';

		if(isset($args["label"]) AND $args["label"] != "") {
			$label = '<label for="'.$id.'" class="form-field-label">' . $args['label'] . (isset($args['required']) && $args['required'] === true ? '<span class="required">*</span>' : '') . '</label>';
		}

		$default_option = '<option value="">Select...</option>';

		if(isset($args["default_option"]) && $args['default_option'] == "") {
			$default_option = '';
		}

		if(isset($args["default_option"]) && $args['default_option'] != "") {
			$default_option = '<option value="">' . $args['default_option'] .  '</option>';
		}

		$choices_html = '';

		if(is_array($choices) && count($choices) > 0) {
			foreach ($choices as $key => $choice) {
				if(isset($args['value']) && $args['value'] == $key) {
					$choices_html = $choices_html . '<option value="' . $key . '" selected="selected">' . $choice .  '</option>';
				}
				else {
					$choices_html = $choices_html . '<option value="' . $key . '">' . $choice .  '</option>';
				}
			}
		}

		$field_html = '
			<div class="custom-form-field select'. (isset($args['wrapper_class']) && $args['wrapper_class'] != "" ? ' ' . $args['wrapper_class'] : '') .'">
				' . $label . '
				<select name="'. $field_name .'" id="' . $id .  '" class="select-field' . (isset($args['field_class']) ? ' ' . $args['field_class'] : '') . $required . '">
						' . $default_option . $choices_html . '
				</select>
			</div>
		';

		return $field_html;

	}

	public static function radio($field_name, $choices, $args = []) {

		/* 
			Allowed arguments:

			id [string]: 				The id of the form field
			label [string]: 			The label of the form field
			wrapper_class [string]: 	A wrapper class for the form field
			required [bool]: 			Whether the form field is required
			value [string]:				The key of the selected value
			field_classes [string]		Classes for all of the radio buttons

		*/

		// Form field required
		
		$required = '';

		if(isset($args['required']) && $args['required'] === true) {
			$required = ' required="required"';
		}


		// Field label

		if(isset($args["label"]) AND $args["label"] != "") {
			$label = '<label class="form-field-label">' . $args['label'] . (isset($args['required']) && $args['required'] === true ? '<span class="required">*</span>' : '') . '</label>';
		}

		// Field Class
		$field_classes = '';

		if(isset($args['field_classes']) && $args['field_classes'] != "") {
			$field_classes = ' ' . $args['field_classes'];
		}

		$choices_html = '';

		if(is_array($choices) && count($choices) > 0) {
			foreach ($choices as $key => $choice) {
				if(isset($args['value']) && $args['value'] == $key) {
					$choices_html = $choices_html . '<div class="radio-button-wrapper"><label class="radio-label"><input class="'.$field_classes.'" type="radio" name="'. $field_name .'" value="'. $key .'" checked="checked"'. $required .'> ' . $choice . '</label></div>';
				}
				else {
					$choices_html = $choices_html . '<div class="radio-button-wrapper"><label class="radio-label"><input class="'.$field_classes.'" type="radio" name="'. $field_name .'" value="'. $key .'"'. $required .'> ' . $choice . '</label></div>';
				}
			}
		}

		$field_html = '
			<div class="custom-form-field radio'. (isset($args['wrapper_class']) && $args['wrapper_class'] != "" ? ' ' . $args['wrapper_class'] : '') .'">
				' . $label . '
				<div class="radio-buttons">' . $choices_html . '</div>
			</div>
		';

		return $field_html;

	}

	/**
	 * Generates an HTML checkbox
	 * @param  string 	$field_name [Name of the HTML field]
	 * @param  string 	$value      [Value of the box when checked]
	 * @param  array  	$args       [Arguments for the form field]
	 * @return string             	[The field HTML]
	 */
	public static function checkbox($field_name, $value, $args = []) {

		/* 
			Allowed arguments:

			id [string]: 				The id of the form field
			label [string]: 			The label of the form field
			wrapper_class [string]: 	A wrapper class for the form field
			field_classes [string]:		A class for the checkbox
			required [bool]: 			Whether the form field is required
			checked [bool]:				Whether the checkbox is checked

		*/

		// Field ID
		
		$id = $field_name;

		if(isset($args["id"]) AND $args["id"] != "") {
			$id = $args["id"];
		}

		// Form field required

		if(isset($args['required']) && $args['required'] === true) {
			$required = ' required="required"';
		}

		$checked = '';

		if(isset($args['checked']) && $args['checked'] === true) {
			$checked = ' checked="checked"';
		}

		$label = '';

		if(isset($args['label']) && $args['label'] != '') {
			$label = '<label for="'. $id .'" class="checkbox-label">'. $args['label'] .'</label>';
		}

		$field_classes = '';

		if(isset($args['field_classes']) && $args['field_classes'] != "") {
			$field_classes = ' ' . $args['field_classes'];
		}

		$field_html = '
			<div class="custom-form-field checkbox'. (isset($args['wrapper_class']) && $args['wrapper_class'] != "" ? ' ' . $args['wrapper_class'] : '') .'">
				<input type="checkbox" value="' . $value . '" id="' . $id . '" name="' . $field_name . '" class="checkbox-input'. $field_classes .'" '.$checked.'>
				' . $label . '
			</div>
		';

		return $field_html;

	}

	public static function colorPicker($field_name, $args = []) {

		// Field ID
		
		$id = $field_name;

		if(isset($args["id"]) AND $args["id"] != "") {
			$id = $args["id"];
		}

		// Form field required
		
		$required = '';

		if(isset($args['required']) && $args['required'] === true) {
			$required = ' required="required"';
		}

		// Field Classes

		$field_classes = '';

		if(isset($args['field_classes']) && $args['field_classes'] != "") {
			$field_classes = ' ' . $args['field_classes'];
		}

		// Field Value
		
		$value = '';

		if(isset($args['value'])) {
			$value = $args['value'];
		}

		// Field label
		
		$label = '';

		if(isset($args["label"]) AND $args["label"] != "") {
			$label = '<label for="'.$id.'" class="form-field-label">' . $args['label'] . (isset($args['required']) && $args['required'] === true ? '<span class="required">*</span>' : '') . '</label>';
		}

		$field_html = '
			<div class="custom-form-field input colorpicker'. (isset($args['wrapper_class']) && $args['wrapper_class'] != "" ? ' ' . $args['wrapper_class'] : '') .'">
				
				'. $label .'

				<input class="js-color-picker'.$field_classes.'" type="color" id="'.$id.'" name="'.$field_name.'" value="' . $value . ''.$required.'">

				<span class="picker-instructions">Click the colored area to select color</span>

			</div>
		';

		return $field_html;

	}

	/**
	 * @param  string $success_message 
	 * @param  string $error_message
	 * @return string 
	 */
	public static function displayFormStatuses(
		$success_message = "Your form has been submitted successfully.", 
		$error_message = "An error occurred while submitting your form."
	) {

		$html = '

			<div class="form-status-messaging">

				<div class="js-form-success form-message form-success" style="display: none;">' . $success_message . '</div>
				<div class="js-form-failure form-message form-failure" style="display: none;">' . $error_message . '</div>

			</div>

		';

		return $html;

	}

}