<?php
namespace StrategicPlugins\TargetedActions;

if ( !defined( 'WPINC' ) ) {
    die;
}

class CSSandJSHelper {

  private $assets_path;

	function __construct($assets_path) {

      $this->assets_path = $assets_path;

      add_action( 'admin_enqueue_scripts', [$this, "addAssets"]);
  	}

  	public function addAssets($hook) {

      /* Only add our styles on pages we control, which always start with our prefix */

      if(stristr($hook, "toplevel_page_tua-admin") !== false || stristr($hook, "targeted-user-actions_page_tua-") !== false) {
        wp_enqueue_style('targeted-actions-css',  $this->assets_path . 'css/index.css');

        // CodeMirror
        $cm_settings['codeEditor'] = wp_enqueue_code_editor(array(
          'type' => 'text/html',
          'codemirror' => array(
            'autoRefresh' => true
          )
        ));
        
        wp_localize_script('jquery', 'cm_settings', $cm_settings);

        wp_enqueue_script('wp-theme-plugin-editor');
        wp_enqueue_style('wp-codemirror');

        // Admin Scripts
        wp_enqueue_script('admin.js', $this->assets_path . 'js/admin.js', ["jquery"]);
        wp_enqueue_script('form.js', $this->assets_path . 'js/form.js', ["jquery"]);
      }
		
  	}
}