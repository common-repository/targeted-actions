<?php
namespace StrategicPlugins\TargetedActions;

if ( !defined( 'WPINC' ) ) {
    die;
}

class OptionsHelper {

  /**
   * Saves a new Wordpress option, unless option already exists, in which case overwrite the existing option
   * @param  string   $name   Name of the option in the wp_options table.
   * @param  value    $value  Value of the option to save. 
   * @return null
   */
  public static function saveOption($name, $value) {

    if(get_option($name, false) === false) {
      add_option(sanitize_key($name), sanitize_text_field($value)); // Option does not exist
    }
    else {
      update_option(sanitize_key($name), sanitize_text_field($value));
    }

  }
}