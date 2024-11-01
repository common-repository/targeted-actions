<?php
namespace StrategicPlugins\TargetedActions;

/*
Plugin Name: Targeted User Actions
description: A plugin that allows you to target users of your website and perform custom actions, including showing a pop-up or setting a cookie, only for matching users.
Version: 1.1
Author: StrategicPlugins.com
Author URI: https://strategicplugins.com
License: Expat
*/

/*

Copyright 2021 StrategicPlugins.com

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/

if ( !defined( 'WPINC' ) ) {
    die;
}

class TargetedActions {

	private $plugin_dir;
	private $plugin_dir_foldername;
	private $assets_path;
	private $version = "1.0";

	function __construct() {
		$this->plugin_dir_foldername = basename(__DIR__);
		$this->plugin_dir = WP_PLUGIN_DIR . '/' . $this->plugin_dir_foldername;
		$this->assets_path = plugin_dir_url(__FILE__) . "assets/";

		// All of our functionality is neatly broken out into separate files

		// Any helpers that we need
		require_once($this->plugin_dir . "/features/helpers/Form.php");
		new Form();

		require_once($this->plugin_dir . "/features/helpers/OptionsHelper.php");
		new OptionsHelper();

		require_once($this->plugin_dir . "/features/helpers/DisableAdminNotices.php");
		new DisableAdminNotices();

		require_once($this->plugin_dir . "/features/helpers/CSSandJSHelper.php");
		new CSSandJSHelper($this->assets_path);

		// Custom Post Types

		// Shortcodes
		
		// Activation Hooks
		require_once($this->plugin_dir . "/features/activation/ActivateDeactivate.php");
		new ActivateDeactivate($this->getVersion(), $this->plugin_dir_foldername);

		// Admin Pages

		require_once($this->plugin_dir . "/features/admin/TUAAdmin.php");
		new TUAAdmin();

		require_once($this->plugin_dir . "/features/admin/RuleFields.php");
		new RuleFields();

		// Form Handlers
		require_once($this->plugin_dir . "/features/formhandlers/UpdateTUASettings.php");
		new UpdateTUASettings();

		require_once($this->plugin_dir . "/features/formhandlers/SaveEditRule.php");
		new SaveEditRule();

		// The meat and potatoes
		require_once($this->plugin_dir . "/features/rules/Rules.php");
		new Rules($this->assets_path);
	}

	public function getVersion() {
		return $this->version;
	}

	public static function allowedHTML() {
		return array(
	      'a' => array(
	        'href' => array(),
	        'title' => array(),
	        'class' => array(),
	        'style' => array(),
	        'target' => array(),
	      ),
	      'br' => array(),
	      'em' => array(),
	      'strong' => array(),
	      'span' => array(
	        'class' => array(),
	        'style' => array(),
	        'id' => array()
	      ),
	      'p' => array(
	        'class' => array(),
	        'style' => array()
	      ),
	      'h1' => array(
	        'class' => array(),
	        'style' => array()
	      ),
	      'h2' => array(
	        'class' => array(),
	        'style' => array()
	      ),
	      'h3' => array(
	        'class' => array(),
	        'style' => array()
	      ),
	      'h4' => array(
	        'class' => array(),
	        'style' => array()
	      ),
	      'h5' => array(
	        'class' => array(),
	        'style' => array()
	      ),
	      'div' => array(
	        'class' => array(),
	        'style' => array(),
	        'id' => array(),
	        'data' => array(),
	        'data-rule' => array(),
	        'data-nonce' => array(),
	        'data-action' => array(),
	        'data-id' => array(),
	        'data-loading' => array(),
	        'data-default' => array(),
	        'data-choice' => array(),
	      ),
	      'button' => array(
	        'class' => array(),
	        'style' => array(),
	        'id' => array(),
	        'data-id' => array()
	      ),
	      'form' => array(
	        'class' => array(),
	        'style' => array(),
	        'id' => array(),
	        'data' => array(),
	        'action' => array(),
	        'method' => array(),
	      ),
	      'select' => array(
	        'class' => array(),
	        'style' => array(),
	        'id' => array(),
	        'data' => array(),
	        'name' => array(),
	        'value' => array(),
	      ),
	      'option' => array(
	        'id' => array(),
	        'data' => array(),
	        'name' => array(),
	        'value' => array(),
	        'selected' => array()
	      ),
	      'input' => array(
	        'class' => array(),
	        'style' => array(),
	        'id' => array(),
	        'data' => array(),
	        'type' => array(),
	        'name' => array(),
	        'value' => array(),
	        'checked' => array()
	      ),
	      'textarea' => array(
	        'class' => array(),
	        'style' => array(),
	        'id' => array(),
	        'data' => array(),
	        'name' => array(),
	        'value' => array(),
	        'type' => array()
	      ),
	      'label' => array(
	        'class' => array(),
	        'style' => array(),
	        'id' => array(),
	        'data' => array(),
	        'for' => array()
	      ),
	      'img' => array(
	        'src' => array(),
	        'class' => array(),
	        'id' => array(),
	        'alt' => array()
	      ),
	    );
	}

}

new TargetedActions();
