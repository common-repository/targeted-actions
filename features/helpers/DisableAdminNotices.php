<?php
namespace StrategicPlugins\TargetedActions;

if ( !defined( 'WPINC' ) ) {
    die;
}

class DisableAdminNotices {

	function __construct() {
  		add_action('in_admin_header', [$this, "disableNotices"], 1000);
  	}

  	/**
  	 * Ensure that no admin notices from other plugins appear within our plugin's admin settings pages
  	 * @return null
  	 */
  	public function disableNotices() {

		if(stristr(Form::getArg("page"), "tua-") !== false) {

			remove_all_actions('admin_notices');
			remove_all_actions('all_admin_notices');

		}
  	}
}