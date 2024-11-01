<?php
namespace StrategicPlugins\TargetedActions;

if ( !defined( 'WPINC' ) ) {
    die;
}

class RuleFields {

	public static function ruleName($value = "") {
		$html = '

		<div class="form-row settings-row">
	        <div class="col">
	        
	          <div class="constrained">
	            ' . Form::input("rule_name", [
	              'label' => 'Rule Name',
	              'value' => $value,
	              'required' => true
	            ]) . '
	          </div>

	          <p>Specify a name for your rule.</p>
	        </div>

	      </div>
        ';

		return $html;
	}

	public static function rulePage($enabled = false, $page_path = "/") {

		$html = '
		<div class="form-row settings-row">
          <div class="col">
            ' . Form::checkbox("page_url_matches", "1", [
                'label' => 'Page URL must match a specific page',
                'required' => false,
                'checked' => $enabled,
                'field_classes' => 'js-show-on-checked'
              ]) . '

            <div class="js-show-after-check" style="'. ($enabled === true ? '' : 'display: none;') .'">

              <div class="mt-15 constrained">
                ' . Form::input("required_page_url", [
                  'label' => 'Required Page URL',
                  'value' => $page_path
                ]) . '
              </div>

              <p>Enable this trigger to require that the user\'s current page match a specific page URL before firing the rule.  Paste your page\'s URL in this box to trigger the rule.  The page URL should begin with a slash and should not include your site\'s domain name.</p>

              <p>This setting will not work if the <em>plain</em> option is selected for your site\'s permalinks.  Parameters are not counted as a part of the page URL and you should not add additional parameters to the value entered in the box above.</p>
            </div>

          </div>
        </div>
		';

		return $html;

	}

	public static function ruleNumPageViews($enabled = false, $view_count = "", $compare_method = "") {

		$html = '
			<div class="form-row settings-row">
              <div class="col">
                ' . Form::checkbox("page_view_count-equals", "1", [
                    'label' => 'The user must have viewed the current page a certain number of times',
                    'required' => false,
                    'checked' => $enabled,
                    'field_classes' => 'js-show-on-checked'
                  ]) . '

                <div class="js-show-after-check" style="'. ($enabled === true ? '' : 'display: none;') .'">

                  <div class="mt-15 constrained">
                    ' . Form::select("required_view_type_match", 
                      [
                        'equal' => 'View Count Equals',
                        'less_equals' => 'View Count Less Than or Equal',
                        'greater_equals' => 'View Count Greater Than or Equal'
                      ],
                      [
                      'label' => 'Match Type',
                      'value' => $compare_method
                    ]) . '
                  </div>

                  <div class="mt-15 constrained">
                    ' . Form::input("required_view_count", [
                      'label' => 'Required Number of Views',
                      'value' => $view_count,
                      'type' => 'number'
                    ]) . '
                  </div>

                  <p>Enable this trigger to require that the user has viewed the current page a certain number of times.  The first time the user views the page, this will be counted as view #1.  If a user refreshes the current page, that will count as view #2.  Views will be tracked across sessions as long as users do not clear their cookies.</p>
                </div>

              </div>
            </div>
		';

		return $html;

	}

	public static function ruleUserLoggedIn($enabled = false) {

		$html = '
			<div class="form-row settings-row">
              <div class="col">
                ' . Form::checkbox("user_logged_in", "1", [
                    'label' => 'The user must be logged in to Wordpress',
                    'required' => false,
                    'checked' => $enabled,
                    'field_classes' => 'js-show-on-checked'
                  ]) . '

                <div class="js-show-after-check" style="'. ($enabled === true ? '' : 'display: none;') .'">

                  <p>Enable this trigger to require that the user is logged into an account on your Wordpress website.</p>
                </div>

              </div>
            </div>
		';

		return $html;

	}

  public static function ruleUserNotLoggedIn($enabled = false) {

    $html = '
      <div class="form-row settings-row">
              <div class="col">
                ' . Form::checkbox("user_not_logged_in", "1", [
                    'label' => 'The user must not be logged in to Wordpress',
                    'required' => false,
                    'checked' => $enabled,
                    'field_classes' => 'js-show-on-checked'
                  ]) . '

                <div class="js-show-after-check" style="'. ($enabled === true ? '' : 'display: none;') .'">

                  <p>Enable this trigger to require that the user is not logged into Wordpress.</p>
                </div>

              </div>
            </div>
    ';

    return $html;

  }

	public static function ruleVisitCount($enabled = false, $compare_method = "", $visits = "") {

    $rule_styles = "display: none;";

    if($enabled === true) {
      $rule_styles = "";
    }

		$html = '
			<div class="form-row settings-row">
              <div class="col">
                ' . Form::checkbox("visit_count_equals", "1", [
                    'label' => 'The user must have visited your website a specific number of times',
                    'required' => false,
                    'checked' => $enabled,
                    'field_classes' => 'js-show-on-checked'
                  ]) . '

                <div class="js-show-after-check" style="'. $rule_styles .'">

                  <div class="mt-15 constrained">
                    ' . Form::select("match_type", 
                      [
                        'equal' => 'View Count Equals',
                        'less_equals' => 'View Count Less Than or Equal',
                        'greater_equals' => 'View Count Greater Than or Equal'
                      ],
                      [
                      'label' => 'Match Type',
                      'value' => $compare_method
                    ]) . '
                  </div>

                  <div class="mt-15 constrained">
                    ' . Form::input("required_visit_count", [
                      'label' => 'Required Number of Visits',
                      'value' => $visits,
                      'type' => 'number'
                    ]) . '
                  </div>

                  <p>Enable this trigger to require that the user has visited your website a certain number of times.  A visit is determined by the creation of a temporary session value for a user\'s session.  If a user comes to your website, views 10 pages, then closes their browser and comes back the next day, this will count as two visits.  A user can load as many pages as they want within a session and it still counts as the same visit.  The first time a user views your website, this counts as the first visit.</p>
                </div>

              </div>
            </div>
		';

		return $html;

	}

	public static function ruleType($value = "") {

    $available_actions = [];
    $available_actions["show_modal"] = 'Show a Modal / Popup Window';
    $available_actions["set_cookie"] = 'Set a cookie on the user\'s browser';

    // Use the tua_define_actions hook to add a new option
    $available_actions = apply_filters( 'tua_define_actions', $available_actions);

		$html = '
			<div class="form-row settings-row">
              <div class="col">

                <div class="form-rule-select">
                    ' . Form::radio("rule_action", 
                      $available_actions,
                      [
                      'label' => 'Select Rule Action',
                      'required' => true,
                      'value' => $value,
                      'field_classes' => 'js-rule-selection'
                    ]) . '
                  </div>

              </div>
            </div>
		';

		return $html;

	}

}