<?php
namespace StrategicPlugins\TargetedActions;

if ( !defined( 'WPINC' ) ) {
    die;
}

class Rules {

  	private $assets_path;

	function __construct($assets_path) {

		$this->assets_path = $assets_path;

		if(!isset($_SESSION)) {
			session_start();
		}

		add_action( 'init', [$this, "executeRule"]);

	}

	/**
	 * Logs the user's hit, then executes any associated rules
	 */
	public function executeRule() {

		global $wpdb;
		$rules_table_name = $wpdb->prefix . 'tua_rules';
		$hits_table_name = $wpdb->prefix . 'tua_hits';

		if(!is_admin() AND !is_customize_preview()) {

			if(boolval(get_option("tua_require_cookie")) === false OR (boolval(get_option("tua_require_cookie")) === true AND $this->verifyCookie() === true)) {

				// Gather information and then log the current hit
				$page_url = sanitize_text_field($_SERVER['REQUEST_URI']);
				$parameters = "";

				$bad_page_urls = [
					"/favicon.ico",
					".php",
					"?p="
				];

				foreach ($bad_page_urls as $bad_page) {
					if(stristr($page_url, $bad_page) !== false) {
						return;
					}
				}

				if(stristr($page_url, "?")) {

					if(get_option('permalink_structure')) {

						$parts = explode("?", $page_url);
						$page_url = rtrim($parts[0], "/") . "/";
						$parameters = "?" . $parts[1];

					}
					else {

						// Site is not using pretty permalinks
						$page_id = Form::getArg("page_id");
						$parameters = str_replace("page_id=" . $page_id, "", $page_url);
						$page_url = $page_id;

						if($parameters == "/?" OR $parameters == "/&") {
							$parameters = "";
						}

						$parameters = ltrim($parameters, "/");
						$parameters = rtrim($parameters, "&");
						$parameters = str_replace("?&", "?", $parameters);

						if($page_url == "") {
							$page_url = "/";
						}
					}

				}
				else {
					$page_url = rtrim($page_url, "/") . "/";
				}

				// Other parameters we want to log

				$ip 			= $this->getIP();
				$user_id 		= get_current_user_id();
				$session_info	= $this->getSessionInfo();
				$permanent_id   = $this->getPermanentID();
				$hit_time 		= current_time('mysql', 1);

				// Log the hit
				$wpdb->insert($hits_table_name, 
					[
						"hit_url" => $page_url,
						"hit_parameters" => $parameters,
						"hit_ip" => $ip,
						"hit_user_id" => $user_id,
						"hit_time" => $hit_time,
						"hit_session_id" => $session_info["id"],
						"hit_permanent_id" => $permanent_id
					],
					["%s", "%s", "%s", "%d", "%s"]
				);

				// Fetch the rules from the database
					
				$rules = [];

				$query_results = $wpdb->get_results("SELECT hit_rule_json FROM " . $rules_table_name . " WHERE rule_enabled = '1'", ARRAY_A);

				if(is_array($query_results) AND count($query_results) > 0) {
					
					foreach($query_results as $result) {
						$rules[] = json_decode($result["hit_rule_json"], true);
					}

				}

				// Process the Rules
				
				$rule_trigger_conditions = [];	// Information about the user's state
				$rule_trigger_conditions["page_url_to_match"] = $page_url;
				$rule_trigger_conditions["viewed_current_page_times"] = -1;
				$rule_trigger_conditions["user_logged_in"] = is_user_logged_in();
				$rule_trigger_conditions["number_of_website_visits"] = $session_info["visit_count"];

				$rule_trigger_conditions = apply_filters( 'tua_current_conditions', $rule_trigger_conditions);

				// Ensure that only one modal rule triggers per iteration
				$modal_rule_will_fire = false;

				if(isset($rules) AND is_array($rules) AND count($rules) > 0) {
				
					foreach($rules as $rule) {

						$conditions_met = [];
						$conditions_met["page_url_matches"] = false;
						$conditions_met["viewed_current_page_times"] = false;
						$conditions_met["user_logged_in"] = false;
						$conditions_met["user_not_logged_in"] = false;
						$conditions_met["number_of_website_visits"] = false;

						// Page URL Match Rule

						if(boolval($rule["page_url_matches"]) === true) {

							if(get_option('permalink_structure')) {

								$page_url_to_match = rtrim($rule["required_page_url"], "/") . "/";

								if(strtolower($page_url_to_match) === strtolower($rule_trigger_conditions["page_url_to_match"])) {
									$conditions_met["page_url_matches"] = true;
								}

							}
							else {

								// Have to figure out what to do if not using pretty permalinks
								// This will likely come in a future release

							}

						}
						else {

							// No matching rule, so evaluate to true
							$conditions_met["page_url_matches"] = true;

						}

						// Page View Count Equals

						if(boolval($rule["page_view_count-equals"]) === true) {

							// We need to get the current number of times the user has viewed the current page
							// We get this here and not up top to save on a database query unless it's needed
							
							if($rule_trigger_conditions["viewed_current_page_times"] < 0) {

								$query = $wpdb->prepare("SELECT count('hit_url') AS the_count FROM " . $hits_table_name . " WHERE hit_permanent_id = %s AND hit_url = %s LIMIT 1", $permanent_id, $rule_trigger_conditions["page_url_to_match"]);

								$result = $wpdb->get_row($query);

								if(isset($result->the_count) AND is_numeric($result->the_count)) {

									$number_of_views = $result->the_count;

								}
								
							}
							else {
								$number_of_views = $rule["page_view_count-equals"];
							}

							// Check that the page count meets the requirement
							
							if($rule["required_view_type_match"] == "equal" AND $number_of_views == $rule["required_view_count"]) {
								$conditions_met["viewed_current_page_times"] = true;
							}
							elseif($rule["required_view_type_match"] == "less_equals" AND $number_of_views <= $rule["required_view_count"]) {
								$conditions_met["viewed_current_page_times"] = true;
							}
							elseif($rule["required_view_type_match"] == "greater_equals" AND $number_of_views >= $rule["required_view_count"]) {
								$conditions_met["viewed_current_page_times"] = true;
							}

						}
						else {
							$conditions_met["viewed_current_page_times"] = true;
						}

						// User Logged In Is True

						if(boolval($rule["user_logged_in"]) === true) {
							$conditions_met["user_logged_in"] = $rule_trigger_conditions["user_logged_in"];
						}
						else {
							$conditions_met["user_logged_in"] = true;
						}

						// User NOT logged in is True
						
						if(boolval($rule["user_not_logged_in"]) === true) {

							// Verify that user is not logged in
							
							if($rule_trigger_conditions["user_logged_in"] !== true) {
								$conditions_met["user_not_logged_in"] = true;
							}

						}
						else {
							$conditions_met["user_not_logged_in"] = true;
						}

						// Total websites visit equals
						
						if(boolval($rule["visit_count_equals"]) === true) {

							if($rule["match_type"] == "equal" AND $rule_trigger_conditions["number_of_website_visits"] == $rule["required_visit_count"]) {
								$conditions_met["number_of_website_visits"] = true;
							}
							elseif($rule["match_type"] == "less_equals" AND $rule_trigger_conditions["number_of_website_visits"] <= $rule["required_visit_count"]) {
								$conditions_met["number_of_website_visits"] = true;
							}
							elseif($rule["match_type"] == "greater_equals" AND $rule_trigger_conditions["number_of_website_visits"] >= $rule["required_visit_count"]) {
								$conditions_met["number_of_website_visits"] = true;
							}

						}
						else {
							$conditions_met["number_of_website_visits"] = true;
						}

						// echo "<p>Evaluating rule: " . $rule["rule_name"] . "</p>";
						
						// Hook for validating that custom trigger conditions are met
						$conditions_met = apply_filters( 'tua_conditions_met', $conditions_met, $rule, $rule_trigger_conditions);
						
						$all_conditions_met = true;

						foreach($conditions_met as $condition) {
							if($condition !== true) {
								$all_conditions_met = false;
								break;
							}
						}

						if($all_conditions_met === true) {

							// All of the criteria required for this rule are true
							// Execute the appropriate action based on the rule
							
							// echo "<p>Rule Name <em>" . $rule["rule_name"] . "</em> matches all conditions and will execute!</p>";

							if($rule["rule_action"] == "show_modal" AND $modal_rule_will_fire === false) {
								$this->executeShowModal($rule);
								$modal_rule_will_fire = true; // Only one modal per page
							}
							elseif($rule["rule_action"] == "set_cookie") {
								$this->executeSetCookie($rule);
							}

							// Custom action for executing custom rules
							do_action("tua_execute_actions", $rule["rule_action"], $rule, $modal_rule_will_fire);

						}

						/*
						else {
							if($conditions_met["page_url_matches"] !== true) {
								echo "<p>Rule failed on page URL!</p>";
							}
							if($conditions_met["viewed_current_page_times"] !== true) {
								echo "<p>Rule failed on current page views!</p>";
							}
							if($conditions_met["user_logged_in"] !== true) {
								echo "<p>Rule failed on user logged in!</p>";
							}
							if($conditions_met["number_of_website_visits"] !== true) {
								echo "<p>Rule failed on number of visits!</p>";
							}
						}
						*/

					}

				}
				
			}
		}
	}

	/**
	 * Verifies that the tracking allowed cookie is present if a cookie is required prior to tracking users
	 * @return [bool] [Whether or not cookie exists]
	 */
	public function verifyCookie() {

		if(isset($_COOKIE[sanitize_text_field(get_option("tua_required_cookie_name"))])) {
			return true;
		}

		return false;

	}

	public function getIP() {

		$ip = "";

		if(isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
			// We are behind Cloudflare
			$ip = sanitize_text_field($_SERVER["HTTP_CF_CONNECTING_IP"]);
		}
		elseif(isset( $_SERVER['REMOTE_ADDR'])) {
			$ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
		}

		return $ip;
	}

	public function getSessionInfo() {

		$session_data = [];
		$session_data["id"] = "anonymous";
		$session_data["visit_count"] = 1;

		if(isset($_SESSION["unique_id"])) {
			$session_data["id"] = sanitize_text_field($_SESSION["unique_id"]);

			if(isset($_COOKIE["tua_visit_count"]) AND is_numeric($_COOKIE["tua_visit_count"])) {
				$_SESSION["visit_count"] 		= sanitize_text_field($_COOKIE["tua_visit_count"]);
				$session_data["visit_count"] 	= sanitize_text_field($_COOKIE["tua_visit_count"]);
			}
		}
		else {

			// User has clean session, generate unique ID and determine visit count
			$unique_id = base64_encode(uniqid() . rand(1, 1000));
			$session_data["id"] = $unique_id;

			$previous_session_id = "";

			if(isset($_COOKIE["tua_previous_id"])) {
				$previous_session_id = sanitize_text_field($_COOKIE["tua_previous_id"]);
			}

			if(isset($_COOKIE["tua_visit_count"])) {
				$visit_count = sanitize_text_field($_COOKIE["tua_visit_count"]);

				if(is_numeric($visit_count)) {

					if($previous_session_id == "" OR $previous_session_id != $session_data["id"]) {

						$visit_count = $visit_count + 1;

						// Update the visit count that is returned
						$session_data["visit_count"] = $visit_count;

						setcookie("tua_previous_id", sanitize_text_field($session_data["id"]), time()+60*60*24*365, "/", sanitize_text_field($_SERVER['HTTP_HOST']));

						// Set the new visit count in a cookie
						setcookie("tua_visit_count", sanitize_text_field($session_data["visit_count"]), time()+60*60*24*365, "/", sanitize_text_field($_SERVER['HTTP_HOST']));

						// Set the new session ID
						$_SESSION["unique_id"] = $session_data["id"];

					}
					else {
						$session_data["visit_count"] = $visit_count;
						$_SESSION["visit_count"] = $visit_count;

						if($_SESSION["unique_id"] == "anonymous") {
							$_SESSION["unique_id"] = $session_data["id"];
						}
					}

				}
			}
			else {

				// New user, so store the current session ID as the previous ID
				setcookie("tua_previous_id", sanitize_text_field($session_data["id"]), time()+60*60*24*365, "/", sanitize_text_field($_SERVER['HTTP_HOST']));

				// Set the new visit count in a cookie
				setcookie("tua_visit_count", sanitize_text_field($session_data["visit_count"]), time()+60*60*24*365, "/", sanitize_text_field($_SERVER['HTTP_HOST']));

				$_SESSION["unique_id"] = $session_data["id"];

			}
		}

		return $session_data;

	}

	public static function getPermanentID() {

		if(isset($_COOKIE["tua_permanent_id"]) AND $_COOKIE["tua_permanent_id"] != "") {
			return sanitize_text_field($_COOKIE["tua_permanent_id"]);
		}

		$permanent_id = base64_encode(uniqid() . rand(1, 10000));

		// Set the permanent ID that will allow us to track a user across sessions
		setcookie("tua_permanent_id", $permanent_id, time()+60*60*24*365, "/", sanitize_text_field($_SERVER['HTTP_HOST']));

		return $permanent_id;

	}

	/**
	 * Displays the modal triggered by the rule
	 * @param  array $rule - The Rule Array	
	 * @return null
	 */
	public function executeShowModal($rule) {

		wp_enqueue_style('targeted-actions-modal',  $this->assets_path . 'css/modal.css');
		wp_enqueue_script('targeted-actions-modal.js', $this->assets_path . 'js/modal.js', ["jquery"]);

		$modal_background_color = "";
		$modal_background_image = "";
		$modal_content = "";
		$modal_text_color = "";
		$modal_header_size = "";
		$modal_body_size = "";
		$modal_button_size = "";
		$modal_inline_styles = '';

		if($rule["modal_rule_action"] == "modal_custom_html") {

			// The user is using the advanced code option

			$modal_content = html_entity_decode($rule["modal_html"]);
		}
		else {

			// The user used the friendly modal builder
			$modal_inline_styles = 'background-color: ' . $rule["modal_bg_color"] . ';';

			if($rule["modal_bg_img_url"] != "") {
				$modal_inline_styles = $modal_inline_styles . ' background-image: url('. $rule["modal_bg_img_url"] .');';
			}

			$modal_header = '';
			$modal_cta = '';
			$header_styles = '';

			if($rule["modal_header"] != "") {

				if($rule["modal_header_size"] != "") {
					$header_styles = $header_styles . 'font-size: ' . $rule["modal_header_size"] . '; ';
				}

				if($rule["modal_text_color"] != "") {
					$header_styles = $header_styles . 'color: ' . $rule['modal_text_color'] . '; ';
				}

				$modal_header = '<h2 class="tua-modal-header" style="'. $header_styles .'">' . $rule["modal_header"] . '</h2>';
			}

			if($rule["modal_cta_url"] != "") {

				$button_styles = '';

				if($rule['modal_button_size'] != '') {
					$button_styles = $button_styles . 'font-size: ' . $rule['modal_button_size'] . '; ';
				}

				if($rule["modal_button_text_color"] != '') {
					$button_styles = $button_styles . 'color: ' . $rule['modal_button_text_color'] . '; ';
				}

				if($rule["modal_button_border_color"] != '') {
					$button_styles = $button_styles . 'border-color: ' . $rule['modal_button_border_color'] . '; ';
				}

				if($rule["modal_button_background_color"] != '') {
					$button_styles = $button_styles . 'background-color: ' . $rule['modal_button_background_color'] . '; ';
				}

				$modal_cta = '<div class="tua-modal-cta"><a style="'.$button_styles.'" href="'. $rule["modal_cta_url"] .'">'. $rule['modal_cta_text'] .'</a></div>';
			}

			$modal_content_styles = '';

			if($rule['modal_body_size'] != "") {
				$modal_content_styles = $modal_content_styles . 'font-size: ' . $rule['modal_body_size'] . '; ';
			}

			if($rule["modal_text_color"] != "") {
				$modal_content_styles = $modal_content_styles . 'color: ' . $rule['modal_text_color'] . '; ';
			}

			$modal_content = '

				<div class="builder-modal-content">
					'. $modal_header .'
					<div class="rule-content">
						<p style="'.$modal_content_styles.'">'. $rule['modal_content'] .'</p>
					</div>
					'. $modal_cta .'
				</div>

			';

		}

		$modal_html = '

			<div class="tua-modal-backdrop js-tua-modal-close"><!-- This is just to black out the background --></div>
			<div class="tua-modal" style="'. $modal_inline_styles .'">

				<button class="js-tua-modal-close tua-modal-close" aria-label="Close Modal">
					<span class="tua-close-button" aria-hidden="true"></span>
				</button>

				<div class="tua-modal-content">

					' . $modal_content . '

				</div>

			</div>

		';

		add_action('wp_footer', function() use ($modal_html) {

			echo wp_kses('<div class="js-tua-modal-container tua-modal-main-container" style="display: none;">' . $modal_html .  '</div>', TargetedActions::allowedHTML());

		});

	}

	/**
	 * Sets a cookie based on the rule criteria
	 * @param  array $rule - The config for the rule
	 * @return null
	 */
	public static function executeSetCookie($rule) {

		$https_only = boolval($rule["cookie_https_only"]);
		$php_only = boolval($rule["cookie_php_only"]);

		// If user does not allow overwriting existing cookie, do nothing

		if(isset($rule["cookie_no_overwrite"]) AND boolval($rule["cookie_no_overwrite"]) === true AND isset($_COOKIE[$rule["cookie_name"]])) {
			return;
		}

		setcookie(sanitize_text_field($rule["cookie_name"]), sanitize_text_field($rule["cookie_value"]), time() + sanitize_text_field($rule["cookie_expiry_time"]), "/", sanitize_text_field($_SERVER['HTTP_HOST']), $https_only, $php_only);

	}
}