<?php
namespace StrategicPlugins\TargetedActions;

if ( !defined( 'WPINC' ) ) {
    die;
}

class SaveEditRule {

  function __construct() {

    if(!isset($_SESSION)) {
      session_start();
    }
    
  	add_action( 'admin_post_save_rule_new', [$this, "saveNewRule"]);
    add_action( 'admin_post_save_rule_edit', [$this, "editExistingRule"]);
    add_action( 'admin_post_enable_disable_rule', [$this, "enableDisableRule"]);
    add_action( 'admin_post_delete_rule', [$this, "deleteRule"]);

  }

  public function saveNewRule() {

  	$response = [];
  	$response["status"] = "failure";
  	$response["errors"] = [];
  	$response["invalid_fields"] = [];
  	$response["message_error"] = "The following errors occurred while saving your rule:";
  	$response["message_success"] = 'Your rule has been saved successfully.  <a href="'. get_admin_url(null, 'admin.php?page=tua-admin') .'">Click here</a> to view your rules.';

  	/* User is Admin, or at least has admin permissions */

    if(is_user_logged_in() AND current_user_can( 'manage_options' )) {

  		/* Verify the nonce and die if it fails */
      $nonce_status = check_ajax_referer( 'save_rule' . (is_numeric(Form::getArg("ruleID")) ? '_edit' : '_new'), '_wpnonce', true);

      /* Get all of the values of the form and perform validation */
  		$results = self::getAndValidateData();

      if($results["is_valid"] !== true) {
        $response["errors"] = $results["response"]["errors"];
        $response["invalid_fields"] = $results["response"]["invalid_fields"];
      }
      
      $values = $results["values"];

  		// No errors, so perform our success action

  		if(count($response["errors"]) === 0 AND count($response["invalid_fields"]) === 0 AND $results["is_valid"] === true) {

        // Save the new rule to the database
        
        global $wpdb;

        $rules_table_name = $wpdb->prefix . 'tua_rules';
        $hits_table_name = $wpdb->prefix . 'tua_hits';

        $rule = json_encode($values);

        $db_result = $wpdb->insert($rules_table_name, [
          'hit_rule_json' => $rule
        ]);

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

  public function editExistingRule() {

    $response = [];
    $response["status"] = "failure";
    $response["errors"] = [];
    $response["invalid_fields"] = [];
    $response["message_error"] = "The following errors occurred while saving your rule:";
    $response["message_success"] = 'Your rule has been saved successfully.  <a href="'. get_admin_url(null, 'admin.php?page=tua-admin') .'">Click here</a> to view your rules.';

    /* User is Admin, or at least has admin permissions */

    if(is_user_logged_in() AND current_user_can( 'manage_options' )) {

      /* Verify the nonce and die if it fails */
      $nonce_status = check_ajax_referer( 'save_rule' . (is_numeric(Form::getArg("ruleID")) ? '_edit' : '_new'), '_wpnonce', true);

      /* Get all of the values of the form and perform validation */
      $results = self::getAndValidateData(true);

      if($results["is_valid"] !== true) {
        $response["errors"] = $results["response"]["errors"];
        $response["invalid_fields"] = $results["response"]["invalid_fields"];
      }
      
      $values = $results["values"];

      // No errors, so perform our success action

      if(count($response["errors"]) === 0 AND count($response["invalid_fields"]) === 0 AND $results["is_valid"] === true) {

        // Save the edited rule to the database
        
        global $wpdb;

        $rules_table_name = $wpdb->prefix . 'tua_rules';
        $hits_table_name = $wpdb->prefix . 'tua_hits';

        $rule = json_encode($values);

        $db_result = $wpdb->update(
          $rules_table_name, 
          ["hit_rule_json" => $rule], 
          ["id" => $values["rule_id"]],
          array("%s"), 
          array("%d")
        );

        if($db_result !== false and $db_result === 1) {
          $response["status"] = "success";
        }
        else {
          $response["errors"][] = "Something went wrong updating this rule in the database.";
        }
        
      }

      // Return the values
      $response["values"] = $values;
    }
    else {
      $response["errors"][] = "You do not have permission to edit this setting.";
    }

    wp_send_json($response);

  }

  public static function getAndValidateData($update = false) {

    $is_valid = true;
    $response = [];

    $values = [];

    // Basic Settings
    $values["rule_id"] = Form::getArg("ruleID");
    $values["rule_name"] = Form::getArg("rule_name");

    // Triggers - Page URL
    $values["page_url_matches"] = Form::getArg("page_url_matches");
    $values["required_page_url"] = Form::getArg("required_page_url");

    // Triggers - Page View Count
    $values["page_view_count-equals"] = Form::getArg("page_view_count-equals");
    $values["required_view_count"] = Form::getArg("required_view_count");
    $values["required_view_type_match"] = Form::getArg("required_view_type_match");

    // Triggers - User Logged In
    $values["user_logged_in"] = Form::getArg("user_logged_in");

    // Triggers - User NOT Logged In
    $values["user_not_logged_in"] = Form::getArg("user_not_logged_in");

    // Triggers - Visit Count
    $values["visit_count_equals"] = Form::getArg("visit_count_equals");
    $values["match_type"] = Form::getArg("match_type");
    $values["required_visit_count"] = Form::getArg("required_visit_count");

    // Action Selection
    $values["rule_action"] = Form::getArg("rule_action");

    // Action - Modal
    $values["modal_rule_action"] = Form::getArg("modal_rule_action");
    $values["modal_header"] = Form::getArg("modal_header");
    $values["modal_content"] = Form::getArg("modal_content");
    $values["modal_cta_text"] = Form::getArg("modal_cta_text");
    $values["modal_cta_url"] = Form::getArg("modal_cta_url");
    $values["modal_bg_color"] = Form::getArg("modal_bg_color");
    $values["modal_html"] = Form::getPostHTML("modal_html");
    $values["modal_bg_img_url"] = Form::getArg("modal_bg_img_url");
    $values["modal_text_color"] = Form::getArg("modal_text_color");
    $values["modal_header_size"] = Form::getArg("modal_header_size");
    $values["modal_body_size"] = Form::getArg("modal_body_size");
    $values["modal_button_size"] = Form::getArg("modal_button_size");
    $values["modal_button_text_color"] = Form::getArg("modal_button_text_color");
    $values["modal_button_border_color"] = Form::getArg("modal_button_border_color");
    $values["modal_button_background_color"] = Form::getArg("modal_button_background_color");

    // Action - Cookie
    $values["cookie_name"] = Form::getArg("cookie_name");
    $values["cookie_value"] = Form::getArg("cookie_value");
    $values["cookie_expiry_time"] = Form::getArg("cookie_expiry_time");
    $values["cookie_https_only"] = Form::getArg("cookie_https_only");
    $values["cookie_php_only"] = Form::getArg("cookie_php_only");
    $values["cookie_no_overwrite"] = Form::getArg("cookie_no_overwrite");

    // Get any custom values for any custom rules created using hooks / outside plugins
    $values = apply_filters( 'tua_save_rule_values', $values);
    
    // Define values that are required for all rules
    $required_values = ["rule_name", "rule_action"];

    // Allow adding values that are required for all rules
    // If you're creating a custom plugin using hooks, this usually isn't the place to validate your custom rules
    $required_values = apply_filters( 'tua_define_global_required_values', $required_values);

    if($update === true) {
      $required_values[] = "rule_id";
    }

    foreach ($values as $key => $value) {
      if(in_array($key, $required_values) && $value == "") {

        $response["invalid_fields"] [] = $key;
        $response["errors"][] = "Required field <em>" . $key . "</em> is empty!";

        if($key == "rule_id" AND $value != "" AND !is_numeric($value)) {
          $response["invalid_fields"] [] = $key;
          $response["errors"][] = "Rule ID must be a numeric value.";
        }

      }
      
    }

    // Validation for the Triggers
    if(boolval($values["page_url_matches"]) === true AND $values["required_page_url"] == "") {
      $response["invalid_fields"][] = "required_page_url";
      $response["errors"][] = "You must specify a page URL for the <em>Page URL must match a specific page</em> trigger.";
    }

    if(boolval($values["page_view_count-equals"]) === true AND (!is_numeric($values["required_view_count"]) OR $values["required_view_count"] < 1)) {
      $response["invalid_fields"][] = "required_view_count";
      $response["errors"][] = "The <em>Required Number of Views</em> field value must be a number greater than zero.";
    }

    if(boolval($values["page_view_count-equals"]) === true AND $values["required_view_type_match"] == "") {
      $response["invalid_fields"][] = "required_view_type_match";
      $response["errors"][] = "You must specify a value for the <em>Match Type</em> field for the <em>user must have viewed the current page a certain number of times</em> rule.";
    }

    if(boolval($values["visit_count_equals"]) === true) {

      if($values["match_type"] == "") {
        $response["invalid_fields"][] = "match_type";
        $response["errors"][] = "The <em>Match Type</em> field value is invalid.";
      }

      if(!is_numeric($values["required_visit_count"]) OR $values["required_visit_count"] < 1) {
        $response["invalid_fields"][] = "required_visit_count";
        $response["errors"][] = "The <em>Required Number of Visits</em> field value must be a number greater than zero.";
      }

    }

    // Validate that at least one trigger is checked
    
    $trigger_values = array(boolval($values["page_url_matches"]), boolval($values["page_view_count-equals"]), boolval($values["visit_count_equals"]), boolval($values["user_logged_in"]), boolval($values["user_not_logged_in"]));

    $at_least_one_trigger_checked = false;

    foreach($trigger_values as $tval) {
      if($tval === true) {
        $at_least_one_trigger_checked = true;
        break;
      }
    } 
    
    if($at_least_one_trigger_checked !== true) {

        $response["errors"][] = "You must specify at least one trigger so that this rule will fire.";

    }

    // Validate that both user logged in and not logged in are checked at the same time
    
    if(boolval($values["user_logged_in"]) === true AND boolval($values["user_not_logged_in"]) === true) {
      $response["errors"][] = "You cannot create a rule requiring users to be logged in and not logged in.  Please disable one of these triggers.";
    }

    // Validation for the Modal rule
    if($values["rule_action"] == "show_modal") {

      if($values["modal_rule_action"] == "modal_custom_html") {
        if($values["modal_html"] == "") {
          $response["invalid_fields"][] = "modal_html";
          $response["errors"][] = "You must specify some HTML to use for the modal window.";
        }
      }
      elseif($values["modal_rule_action"] == "modal_modal_builder") {
        // User is using the friendly builder
        // Let's not require all the fields
      
        if($values["modal_content"] == "") {
          $response["invalid_fields"][] = "modal_content";
          $response["errors"][] = "You must specify some content for your modal / pop-up window.";
        }
      }
      else {

        $response["invalid_fields"][] = "modal_rule_action";
        $response["errors"][] = "Please choose whether you want to use the modal builder or custom HTML for your modal.";

      }
    }

    if($values["rule_action"] == "set_cookie") {

      if($values["cookie_name"] == "") {
        $response["invalid_fields"][] = "cookie_name";
        $response["errors"][] = "You must specify a name for your cookie.";
      }

      if(strpos($values["cookie_name"], ' ') !== false) {
        $response["invalid_fields"][] = "cookie_name";
        $response["errors"][] = "Your cookie name appears to contain a space, which is not allowed in cookie names.";
      }

      if($values["cookie_expiry_time"] == "" OR !is_numeric($values["cookie_expiry_time"])) {
        $response["invalid_fields"][] = "cookie_expiry_time";
        $response["errors"][] = "You must specify an expiry time for your cookie.";
      }

    }

    // If you are creating a custom trigger or action, use this hook to validate any required fields are present
    $response = apply_filters( 'tua_validate_and_respond', $response, $values);

    if(isset($response["errors"]) AND count($response["errors"]) > 0) {
      $is_valid = false;
    }

    $result = [
      'is_valid' => $is_valid,
      'values' => $values,
      'response' => $response
    ];

    return $result;

  }

  public function enableDisableRule() {

    if(is_user_logged_in() AND current_user_can('manage_options')) {

      global $wpdb;

      $rules_table_name = $wpdb->prefix . 'tua_rules';
      $hits_table_name = $wpdb->prefix . 'tua_hits';

      $response = [];
      $response["status"] = "failure";

      $nonce_status = check_ajax_referer( 'tua_manage_rule', '_wpnonce', true);

      $rule_id = Form::getArg("ruleID");
      $rule_action = Form::getArg("rule_action");

      if(is_numeric($rule_id)) {

        if($rule_action == "enable") {
          $query = $wpdb->prepare("UPDATE " . $rules_table_name . " SET rule_enabled = '1' WHERE id = %d", $rule_id);
        }
        else {
          $query = $wpdb->prepare("UPDATE " . $rules_table_name . " SET rule_enabled = '0' WHERE id = %d", $rule_id);
        }

        $result = $wpdb->query($query);

        if($result > 0) {
          $response["status"] = "success";
        }
        else {
          $response["error"] = "No results were updated by the query.";
        }
        
      }

      wp_send_json($response);

    }
  }

  public function deleteRule() {

    if(is_user_logged_in() AND current_user_can('manage_options')) {

      global $wpdb;

      $rules_table_name = $wpdb->prefix . 'tua_rules';
      $hits_table_name = $wpdb->prefix . 'tua_hits';

      $response = [];
      $response["status"] = "failure";

      $nonce_status = check_ajax_referer( 'tua_manage_rule', '_wpnonce', true);

      $rule_id = Form::getArg("ruleID");

      if(is_numeric($rule_id)) {

        $query = $wpdb->prepare("DELETE FROM " . $rules_table_name . " WHERE id = %d", $rule_id);
        $result = $wpdb->query($query);

        if($result > 0) {
          $response["status"] = "success";
        }
        else {
          $response["error"] = "The delete query failed!";
        }

      }

      wp_send_json($response);

    }
  }
}