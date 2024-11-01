<?php
namespace StrategicPlugins\TargetedActions;

if ( !defined( 'WPINC' ) ) {
    die;
}

class TUAAdmin {

  function __construct() {

    add_action('admin_menu', [$this, "registerTUAAdmin"]);

    $this->allowedHTML = TargetedActions::allowedHTML();

    add_filter('safe_style_css', function( $styles ) {
        
        if(!in_array('display', $styles)) {
          $styles[] = 'display';
        }

        return $styles;
    } );

  }

  /**
   * Registers the menu items
   * @return null
   */
  public function registerTUAAdmin() {

    // Ensure the user has permission to view the admin pages we want to register
    // This is the earliest in the execution process we can do this check

    if(\is_user_logged_in() AND \current_user_can( 'manage_options' )) {

      add_menu_page("Targeted User Actions", "Targeted User Actions", "manage_options", "tua-admin", [$this, "displayTUAAdmin"], "dashicons-clipboard");

      add_submenu_page( "tua-admin", "Plugin Settings", "Plugin Settings", "manage_options", "tua-settings", [$this, "displayTUASettings"], 2);

      add_submenu_page( "tua-admin", "Create Rule", "Create Rule", "manage_options", "tua-create-rule", [$this, "displayTUACreateRule"], 1);

      add_submenu_page( "tua-admin", "Help and Support", "Help and Support", "manage_options", "tua-support", [$this, "displayTUASupport"], 3);

      add_submenu_page( "tua-admin", "License", "License", "manage_options", "tua-license", [$this, "displayTUALicense"], 4);

    }
  }

  /**
   * Displays the HTML of the main menu page
   * @return [string] [HTML Contents of the page]
   */
  public function displayTUAAdmin() {

    global $wpdb;

    $rules_table_name = $wpdb->prefix . 'tua_rules';
    $hits_table_name = $wpdb->prefix . 'tua_hits';

    $rules = $wpdb->get_results("SELECT id, hit_rule_json, rule_enabled FROM " . $rules_table_name);

    $rules_table_html = '';

    if(count($rules) > 0) {

      $rules_table_html = $rules_table_html . '

        <div class="js-rule-nonce" style="display: none;" data-nonce="'. wp_create_nonce('tua_manage_rule') .'" data-action="' . esc_url( admin_url('admin-post.php') ) . '"></div>

        <div class="tua-rule-row tua-rule-row-header">

          <div class="rule-name">Rule Name</div>
          <div class="rule-type">Rule Type</div>
          <div class="rule-enabled">Rule Enabled</div>
          <div class="rule-actions">Actions</div>

        </div>

      ';

      foreach ($rules as $key => $rule) {

        $rule_contents = json_decode($rule->hit_rule_json, true);

        $rule_type = "";

        if($rule_contents["rule_action"] == "show_modal") {
          $rule_type = "Show a Modal / Pop-Up";
        }
        elseif($rule_contents["rule_action"] == "set_cookie") {
          $rule_type = "Set a Cookie";
        }
        
        $rules_table_html = $rules_table_html . '

          <div class="tua-rule-row">

            <div class="rule-name"><a href="'. get_admin_url(null, 'admin.php?page=tua-create-rule&ruleID=' . $rule->id) .'">'. $rule_contents['rule_name'] .'</a></div>
            <div class="rule-type">'. $rule_type .'</div>
            <div class="rule-enabled">' . (boolval($rule->rule_enabled) === true ? 'Enabled' : 'Disabled') . '</div>
            <div class="rule-actions">

              <button class="tua-button js-enable-disable-rule '. (boolval($rule->rule_enabled) === true ? 'js-disable-rule' : 'js-enable-rule') .'" data-id="'. $rule->id .'">' . (boolval($rule->rule_enabled) === true ? 'Disable' : 'Enable') . '</button>

              <a href="'. get_admin_url(null, 'admin.php?page=tua-create-rule&ruleID=' . $rule->id) .'" class="tua-button">Edit Rule</a>

              <button class="tua-button delete-rule js-delete-rule" data-id="' . $rule->id . '">Delete Rule</button>

              <div class="js-confirm-delete confirm-delete" style="display: none;">

                <p>Are you sure you want to delete this rule?</p>

                <button class="js-confirm-delete-btn tua-button btn-confirm-delete" data-id="' . $rule->id . '">Yes, Delete Rule</button>
                <button class="js-cancel-delete-btn tua-button btn-cancel-delete">No</button>

              </div>

            </div>

          </div>

        ';

      }

    }
    else {
      $rules_table_html = '<p class="no-rules-defined">You do not have any rules configured.  Use the button above to create your first rule.</p>';
    }

    $html = '
      <div class="plugin-wrapper">
        <div class="tua-welcome">
          <h2>Targeted User Actions</h2>
          <p>Welcome to the Targeted User Actions plugin, a plugin that allows you to target users of your website based on criteria that you set, and then perform custom actions when a matching user is found, including showing the user a pop-up message or setting a cookie for the user.</p>

          <div class="tua-all-rules padded-element">

            <div class="create-new-rule">
              <a href="'. get_admin_url(null, 'admin.php?page=tua-create-rule') .'" class="tua-button">Create New Rule</a>
            </div>

            '. $rules_table_html .'
          </div>

        </div>
      </div>';

    echo wp_kses($html, $this->allowedHTML);

  }

  public function displayTUASupport() {

    $html = '

      <h2>Help and Support for Targeted User Actions plugin</h2>

      <p>The Targeted User Actions Plugin is made available by <a href="https://www.strategicplugins.com/" target="_blank">StrategicPlugins.com</a> in the hopes that you will find it useful.  For support with this plugin, please visit our <a href="https://www.strategicplugins.com/" target="_blank">website</a> or email us at <a href="mailto:support@strategicplugins.com" target="_blank">support@strategicplugins.com</a>.</p>

      <p>Please note that while we currently offer free support for this plugin via our website, we are under no obligation to provide free support and may cease providing free support in the future at our discretion.</p>

    ';

    echo wp_kses($html, $this->allowedHTML);

  }

  public function displayTUALicense() {

    $html = '

      <h2>Software License</h2>

      <p>By using this software you agree to be bound by the terms of the following software license:</p>

      <hr/>

      <p>Copyright 2021 StrategicPlugins.com</p>

      <p>Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:</p>

      <p>The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.</p>

      <p>THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.</p>

    ';

    echo wp_kses($html, $this->allowedHTML);

  }

  public function displayTUASettings() {

    $html = '
      <div class="plugin-wrapper">
        <h2>Targeted User Actions Plugin Settings</h2>

        '. Form::displayFormStatuses() .'

        <form class="js-ajax-form custom-form" action="' . esc_url( admin_url('admin-post.php') ) . '" method="post">
          <input type="hidden" name="action" value="update_tua_settings">
          '. wp_nonce_field( 'update_tua_settings', '_wpnonce', false, false ) .'

          <div class="form-row settings-row">

            <div class="col">
              ' . Form::checkbox("require_cookie", "yes", [
                  'label' => 'Require that the following cookie be set before tracking users:',
                  'required' => false,
                  'checked' => boolval(get_option("tua_require_cookie"))
                ]) . '

              <div class="mt-15 constrained">
                ' . Form::input("required_cookie", [
                  'label' => 'Required Cookie Name',
                  'value' => esc_textarea(get_option('tua_required_cookie_name'))
                ]) . '
              </div>

              <p>If you are not allowed to track user actions before a user gives consent and you use a cookie notice plugin to obtain consent, check the box above and enter the name of the cookie your cookie notice plugin saves in the box below.  We won\'t track any user actions for users who don\'t have this cookie present.
            </div>
          </div>

          

          <input class="js-submit" type="submit" value="Save Settings" data-loading="Loading..." data-default="Save Settings"/>

        </form>
      </div>

    ';

    echo wp_kses($html, $this->allowedHTML);

  }

  public function displayTUACreateRule() {

    global $wpdb;

    $rules_table_name = $wpdb->prefix . 'tua_rules';
    $hits_table_name = $wpdb->prefix . 'tua_hits';

    $header_text = '
      <h1>Create New Rule</h1>
      <p>Use the form below to create a new targeted action rule.</p>
    ';

    $rule_to_edit = "";
    $update_rule = "";
    $rule_data = "";

    if(Form::getArg("ruleID") != "" AND is_numeric(Form::getArg("ruleID"))) {
      $rule_to_edit = Form::getArg("ruleID");

      $header_text = '
        <h1>Edit Rule</h1>
        <p>Use the form below to edit your targeted action rule.</p>
      ';

      $query = $wpdb->prepare("SELECT id, hit_rule_json, rule_enabled FROM " . $rules_table_name . " WHERE id = %d LIMIT 1", $rule_to_edit);
      $result = $wpdb->get_row($query);

      if(isset($result->hit_rule_json)) {
        $rule_data = json_decode($result->hit_rule_json, true);
      }
    }

    $modal_area_style = ' style="display: none;"';
    $cookie_area_style = ' style="display: none;"';

    if(isset($rule_data) AND is_array($rule_data)) {
      if($rule_data["rule_action"] == "show_modal") {
        $modal_area_style = '';
      }

      if($rule_data["rule_action"] == "set_cookie") {
        $cookie_area_style = '';
      }
    } 

    $modal_builder_style = "display: none;";
    $modal_html_style = "display: none;";

    if(isset($rule_data["modal_rule_action"]) AND $rule_data["modal_rule_action"] == "modal_modal_builder") {
      $modal_builder_style = "";
    }

    if(isset($rule_data["modal_rule_action"]) AND $rule_data["modal_rule_action"] == "modal_custom_html") {
      $modal_html_style = "";
    }

    $html = '
      <div class="plugin-wrapper">
        ' . $header_text . '

        '. Form::displayFormStatuses() .'

        <form class="js-ajax-form custom-form" action="' . esc_url( admin_url('admin-post.php') ) . '" method="post">
          <input type="hidden" name="action" value="save_rule' . (is_numeric($rule_to_edit) ? '_edit' : '_new') .'">
          <input type="hidden" name="ruleID" value="'. $rule_to_edit .'" />
          '. wp_nonce_field( 'save_rule' . (is_numeric($rule_to_edit) ? '_edit' : '_new'), '_wpnonce', false, false ) .'

          '. RuleFields::ruleName((isset($rule_data["rule_name"]) ? $rule_data["rule_name"] : '')) .'

          <div class="padded-element">

            <h2>Rule Triggers</h2>
            <p>Select which trigger conditions must be true in order to fire the rule.  Checking the checkbox will enable the trigger condition and all selected trigger conditions must be present for the rule to fire.</p>

            '. RuleFields::rulePage(
                (isset($rule_data["page_url_matches"]) ? boolval($rule_data["page_url_matches"]) : false), 
                (isset($rule_data["required_page_url"]) ? $rule_data["required_page_url"] : '/')
              ) .'
            '. RuleFields::ruleNumPageViews(
                (isset($rule_data["page_view_count-equals"]) ? boolval($rule_data["page_view_count-equals"]) : false), 
                (isset($rule_data["required_view_count"]) ? $rule_data["required_view_count"] : ''), 
                (isset($rule_data["required_view_type_match"]) ? $rule_data["required_view_type_match"] : '')
              ) . '
            '. RuleFields::ruleUserLoggedIn(
                (isset($rule_data["user_logged_in"]) ? boolval($rule_data["user_logged_in"]) : false)
              ) . '
            '. RuleFields::ruleUserNotLoggedIn(
                (isset($rule_data["user_not_logged_in"]) ? boolval($rule_data["user_not_logged_in"]) : false)
              ) . '
            '. RuleFields::ruleVisitCount(
                (isset($rule_data["visit_count_equals"]) ? boolval($rule_data["visit_count_equals"]) : false),
                (isset($rule_data["match_type"]) ? $rule_data["match_type"] : ''),
                (isset($rule_data["required_visit_count"]) ? $rule_data["required_visit_count"] : '')
              ) . '

            <!-- TUA Rule Triggers -->
            
          </div>

          <div class="padded-element">

            <h2>Rule Action</h2>
            <p>Select the action that will be performed if the trigger conditions for this rule is met.</p>

            ' . RuleFields::ruleType(
                  (isset($rule_data["rule_action"]) ? $rule_data["rule_action"] : '')
                ) . '

            <div class="the-rules mt-20">

              <div class="rule" data-rule="show_modal"'. $modal_area_style .'>

                <h2>Show a Modal</h2>
                <p>Configure the options for the modal that is shown to the user.</p>

                <div class="form-row settings-row">
                  <div class="col">

                    <div class="form-rule-select">
                        ' . Form::radio("modal_rule_action", 
                          [
                            'modal_modal_builder' => 'Use the Modal Builder to specify text and button links',
                            'modal_custom_html' => 'Use the HTML Editor to insert custom HTML',
                          ],
                          [
                          'label' => 'How do you want to create your modal?',
                          'required' => false,
                          'value' => (isset($rule_data['modal_rule_action']) ? $rule_data['modal_rule_action'] : ''),
                          'field_classes' => 'js-modal-choice-selection'
                        ]) . '
                      </div>

                  </div>
                </div>

                <div class="modal-choice" data-choice="modal_modal_builder" style="'. $modal_builder_style .'">

                  <div class="form-row settings-row">
                    <div class="col">

                      <div class="constrained">
                        ' . Form::input("modal_header", [
                          'label' => 'Modal Header',
                          'value' => (isset($rule_data["modal_header"]) ? $rule_data["modal_header"] : ''),
                          'required' => false
                        ]) . '
                      </div>

                      <p>Specify the header text for your pop-up / modal window.</p>
                    </div>
              
                    <div class="col">
                    
                      <div class="constrained">
                        ' . Form::textarea("modal_content", [
                          'label' => 'Modal Content',
                          'value' => (isset($rule_data["modal_content"]) ? $rule_data["modal_content"] : ''),
                          'required' => false
                        ]) . '
                      </div>

                      <p>Add some paragraph content for your modal window.</p>
                    </div>

                    <div class="col">
                    
                      <div class="constrained">
                        ' . Form::input("modal_cta_text", [
                          'label' => 'Modal Call to Action Text',
                          'value' => (isset($rule_data["modal_cta_text"]) ? $rule_data["modal_cta_text"] : ''),
                          'required' => false
                        ]) . '
                      </div>

                      <p>Specify the text of the call to action (CTA) button.  This button will be the action you want the user to take, such as "Buy Now".</p>
                    </div>

                    <div class="col">
                    
                      <div class="constrained">
                        ' . Form::input("modal_cta_url", [
                          'label' => 'Modal Call to Action URL',
                          'value' => (isset($rule_data["modal_cta_url"]) ? $rule_data["modal_cta_url"] : ''),
                          'required' => false
                        ]) . '
                      </div>

                      <p>Specify the URL that the user will be taken to when they click the call to action button in your modal.</p>
                    </div>

                    <div class="col">
                    
                      <div class="constrained">
                        ' . Form::colorPicker("modal_bg_color", [
                          'label' => 'Modal Background Color',
                          'value' => (isset($rule_data["modal_bg_color"]) ? $rule_data["modal_bg_color"] : '#ffffff'),
                          'required' => false
                        ]) . '
                      </div>

                      <p>Specify the background color of the modal window.</p>
                    </div>

                    <div class="col">
                    
                      <div class="constrained">
                        ' . Form::input("modal_bg_img_url", [
                          'label' => 'Modal Background Image URL',
                          'value' => (isset($rule_data["modal_bg_img_url"]) ? $rule_data["modal_bg_img_url"] : ''),
                          'required' => false
                        ]) . '
                      </div>

                      <p>Specify a background image to use for the modal window.  Paste the URL of the background image in this box.</p>
                    </div>

                    <div class="col">
                    
                      <div class="constrained">
                        ' . Form::colorPicker("modal_text_color", [
                          'label' => 'Modal Text Color',
                          'value' => (isset($rule_data["modal_text_color"]) ? $rule_data["modal_text_color"] : '#000000'),
                          'required' => false
                        ]) . '
                      </div>

                      <p>Specify the text color of the text in the modal window.</p>
                    </div>

                    <div class="col">
                    
                      <div class="constrained">
                        ' . Form::input("modal_header_size", [
                          'label' => 'Modal Header Font Size',
                          'value' => (isset($rule_data["modal_header_size"]) ? $rule_data["modal_header_size"] : ''),
                          'required' => false
                        ]) . '
                      </div>

                      <p>Specify a font size for the modal header.  You must also specify units, such as <em>16px</em> or <em>1rem</em>.  Leave blank to use the default font size set in your theme.</p>
                    </div>

                    <div class="col">
                    
                      <div class="constrained">
                        ' . Form::input("modal_body_size", [
                          'label' => 'Modal Body Font Size',
                          'value' => (isset($rule_data["modal_body_size"]) ? $rule_data["modal_body_size"] : ''),
                          'required' => false
                        ]) . '
                      </div>

                      <p>Specify a font size for the modal body.  You must also specify units, such as <em>16px</em> or <em>1rem</em>.  Leave blank to use the default font size set in your theme.</p>
                    </div>

                    <div class="col">
                    
                      <div class="constrained">
                        ' . Form::input("modal_button_size", [
                          'label' => 'Modal Button Font Size',
                          'value' => (isset($rule_data["modal_button_size"]) ? $rule_data["modal_button_size"] : ''),
                          'required' => false
                        ]) . '
                      </div>

                      <p>Specify a font size for the modal call to action button.  You must also specify units, such as <em>16px</em> or <em>1rem</em>.  Leave blank to use the default font size set in your theme.</p>
                    </div>

                    <div class="col">
                    
                      <div class="constrained">
                        ' . Form::colorPicker("modal_button_text_color", [
                          'label' => 'Modal Button Text Color',
                          'value' => (isset($rule_data["modal_button_text_color"]) ? $rule_data["modal_button_text_color"] : '#000000'),
                          'required' => false
                        ]) . '
                      </div>

                      <p>Specify the text color of the text in the modal call to action button.</p>
                    </div>

                    <div class="col">
                    
                      <div class="constrained">
                        ' . Form::colorPicker("modal_button_border_color", [
                          'label' => 'Modal Button Border Color',
                          'value' => (isset($rule_data["modal_button_border_color"]) ? $rule_data["modal_button_border_color"] : '#000000'),
                          'required' => false
                        ]) . '
                      </div>

                      <p>Specify the color of the modal call to action button border.</p>
                    </div>

                    <div class="col">
                    
                      <div class="constrained">
                        ' . Form::colorPicker("modal_button_background_color", [
                          'label' => 'Modal Button Background Color',
                          'value' => (isset($rule_data["modal_button_background_color"]) ? $rule_data["modal_button_background_color"] : '#ffffff'),
                          'required' => false
                        ]) . '
                      </div>

                      <p>Specify the color of the modal call to action button background.</p>
                    </div>

                  </div>

                </div>

                <div class="modal-choice" data-choice="modal_custom_html" style="'. $modal_html_style .'">
                  <div class="form-row settings-row">
                    <div class="col">
                      '. Form::textarea("modal_html", [
                          'label' => 'Modal HTML',
                          'id' => 'modal-html-codemirror',
                          'field_class' => 'js-tua-codemirror',
                          'value' => (isset($rule_data["modal_html"]) ? html_entity_decode($rule_data["modal_html"]) : '')
                        ]) .'

                      <p>Use the editor above to add custom HTML to the modal.  As a part of this plugin, your HTML will be inserted into a modal window that pops up when the associated rule is triggered.</p>
                    </div>
                  </div>
                </div>

              </div>

              <div class="rule" data-rule="set_cookie"'. $cookie_area_style .'>

                <h2>Set a Cookie</h2>
                <p>Configure the options for setting a cookie.</p>

                <div class="form-row settings-row">
                  <div class="col">
                    
                    <div class="constrained">
                      ' . Form::input("cookie_name", [
                        'label' => 'Cookie Name',
                        'value' => (isset($rule_data["cookie_name"]) ? $rule_data['cookie_name'] : ''),
                        'required' => false
                      ]) . '
                    </div>

                    <p>Specify the name of the cookie that will be saved in the browser.  Valid cookie names can contain letter, numbers, dashes and underscores, but should never contain spaces or other <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie#attributes" target="_blank">prohibited characters</a>.</p>
                  </div>

                  <div class="col">
                    
                    <div class="constrained">
                      ' . Form::input("cookie_value", [
                        'label' => 'Cookie Value',
                        'value' => (isset($rule_data["cookie_value"]) ? $rule_data['cookie_value'] : ''),
                        'required' => false
                      ]) . '
                    </div>

                    <p>Specify the value that will be stored in the cookie.</p>
                  </div>

                  <div class="col">

                    <div class="constrained">
                      ' . Form::select("cookie_expiry_time", 
                        [
                          '1800'      => 'After 30 Minutes',
                          '3600'      => 'After 1 Hour',
                          '86400'     => 'After 24 Hours',
                          '172800'    => 'After 48 Hours',
                          '259200'    => 'After 72 Hours',
                          '604800'    => 'After 7 Days',
                          '2592000'   => 'After 30 Days',
                          '3888000'   => 'After 45 Days',
                          '5184000'   => 'After 60 Days',
                          '11664000'  => 'After 90 Days',
                          '23328000'  => 'After 6 Months',
                          '46656000'  => 'After 1 Year',
                          '-10000'    => 'Now (Expire Existing Cookie)'
                        ],
                        [
                        'label' => 'Choose when the cookie expires',
                        'required' => false,
                        'value' => (isset($rule_data["cookie_expiry_time"]) ? $rule_data['cookie_expiry_time'] : ''),
                      ]) . '
                    </div>

                    <p>Choose how long the cookie will exist on the user\'s computer before the cookie expires.</p>

                  </div>

                  <div class="col">

                    ' . Form::checkbox("cookie_no_overwrite", "1", [
                      'label' => 'Do not overwrite existing cookie',
                      'required' => false,
                      'checked' => (isset($rule_data["cookie_no_overwrite"]) ? boolval($rule_data['cookie_no_overwrite']) : false)
                    ]) . '

                    <p>If checked, the cookie will not be overwritten if it already exists.  If this box is not checked and the cookie already exists, the cookie will have its value and expiration time updated.</p>

                  </div>

                  <div class="col">

                    ' . Form::checkbox("cookie_https_only", "1", [
                      'label' => 'Only allow the cookie to be transmitted over HTTPS',
                      'required' => false,
                      'checked' => (isset($rule_data["cookie_https_only"]) ? boolval($rule_data['cookie_https_only']) : false)
                    ]) . '

                    <p>If checked, the cookie will only be transmitted over secure connections.</p>

                  </div>

                  <div class="col">

                    ' . Form::checkbox("cookie_php_only", "1", [
                      'label' => 'Only allow the cookie to be read server-side',
                      'required' => false,
                      'checked' => (isset($rule_data["cookie_php_only"]) ? boolval($rule_data['cookie_php_only']) : false)
                    ]) . '

                    <p>Marks the cookie as <em>httponly</em>, which may prevent Javascript from reading the cookie.  See the <a href="https://www.php.net/manual/en/function.setcookie.php" target="_blank">PHP Cookie Documentaion</a> for further info.</p>

                  </div>

                </div>

              </div>

              <!-- TUA Add Rule Settings -->

            </div>

          </div>

          <div class="submit-container">
            <input class="js-submit" type="submit" value="Save Targeted Rule" data-loading="Loading..." data-default="Save Targeted Rule"/>
          </div>

        </form>

      </div>

    ';

    $html = apply_filters( 'tua_update_rule_html', $html, $rule_data);

    echo wp_kses($html, $this->allowedHTML);

  }

}