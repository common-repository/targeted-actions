<?php
namespace StrategicPlugins\TargetedActions;

if ( !defined( 'WPINC' ) ) {
    die;
}

class UpdateTUASettings {

  function __construct() {
    
  	add_action( 'admin_post_update_tua_settings', [$this, "UpdateTUASettings"]);

  }

  public function UpdateTUASettings() {

  	$response = [];
  	$response["status"] = "failure";
  	$response["errors"] = [];
  	$response["invalid_fields"] = [];
  	$response["message_error"] = "The following errors occurred while saving the plugin settings:";
  	$response["message_success"] = "The plugin settings have been saved successfully.";

  	/* User is Admin, or at least has admin permissions */

    if(is_user_logged_in() AND current_user_can( 'manage_options' )) {

  		/* Verify the nonce */
      $nonce_status = check_ajax_referer( 'update_tua_settings', '_wpnonce', false );

      if($nonce_status === false) {
        $response["errors"][] = "Invalid nonce.  Please log out of Wordpress and log in again.";
      }

      /* Get all of the values of the form and perform validation */
  		$values = [];
      $values["require_cookie"] = Form::getArg("require_cookie");
      $values["required_cookie"] = Form::getArg("required_cookie");

  		$required_values = [];

  		foreach ($values as $key => $value) {
  			if(in_array($key, $required_values) && $value == "") {

  				$response["invalid_fields"] [] = $key;
  				$response["errors"][] = "Required field <em>" . $key . "</em> is empty!";
  			}
  			
  		}

      // Field Specific Validation
      if($values["require_cookie"] == "yes" AND $values["required_cookie"] == "") {
        $response["invalid_fields"][] = "required_cookie";
        $response["errors"][] = "You must specify a required cookie name if requiring that a cookie be present before enabling tracking.";
      }

      if($values["require_cookie"] == "yes" AND strpos($values["required_cookie"], ' ') !== false) {
        $response["invalid_fields"][] = "required_cookie";
        $response["errors"][] = "The cookie name cannot contain a space.";
      }

  		// No errors, so perform our success action

  		if(count($response["errors"]) === 0 AND count($response["invalid_fields"]) === 0) {

        // Update the WP Options with the saved plugin settings
        OptionsHelper::saveOption("tua_require_cookie", ($values["require_cookie"] == "yes" ? true : false));
        OptionsHelper::saveOption("tua_required_cookie_name", $values["required_cookie"]);

  			$response["status"] = "success";
  		}

  		// Return the values
  		$response["values"] = $values;
  	}
  	else {
  		$response["errors"][] = "You do not have permission to edit this setting.";
  	}

  	wp_send_json($response);

  }
}