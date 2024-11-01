<?php
namespace StrategicPlugins\TargetedActions;

if ( !defined( 'WPINC' ) ) {
    die;
}

class ActivateDeactivate {

	private $version = null;

	function __construct($version, $folder_name) {

	$this->version = $version;

	register_activation_hook($folder_name . "/TargetedUserActions.php", [$this, "install"] );
	register_uninstall_hook($folder_name . "/TargetedUserActions.php", 'StrategicPlugins\TargetedActions\ActivateDeactivate::uninstall');

	}

	function install() {

		$version = $this->version;
		$previous_version = \get_option("tua_version", false);

		if($previous_version == false OR $previous_version < $version) {

			// Update our database and add the tables
			
			if($previous_version == false) {

			// We are doing a fresh install
				
				global $wpdb;
				$charset_collate = $wpdb->get_charset_collate();
				$rules_table_name = $wpdb->prefix . 'tua_rules';
				$hits_table_name = $wpdb->prefix . 'tua_hits';

				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

				$sql = "CREATE TABLE IF NOT EXISTS $hits_table_name  (
				  id int NOT NULL AUTO_INCREMENT,
				  hit_url varchar(1000) NOT NULL,
				  hit_parameters varchar(500) NULL DEFAULT NULL,
				  hit_ip varchar(255) NULL DEFAULT NULL,
				  hit_user_id int NULL DEFAULT 0,
				  hit_time datetime NOT NULL,
				  hit_session_id varchar(100) NULL DEFAULT NULL,
				  hit_permanent_id varchar(100) NULL DEFAULT NULL,
				  PRIMARY KEY (id) USING BTREE,
				  KEY hit_url(hit_url(250)) USING BTREE,
				  KEY hit_user_id(hit_user_id) USING BTREE,
				  KEY hit_ip(hit_ip(250)) USING BTREE,
				  KEY hit_session_id(hit_session_id) USING BTREE,
				  KEY hit_permanent_id(hit_permanent_id) USING BTREE,
				  KEY hit_time(hit_time) USING BTREE,
				  KEY hit_parameters(hit_parameters) USING BTREE
				) $charset_collate;";

				dbDelta( $sql );

				$sql = "CREATE TABLE IF NOT EXISTS $rules_table_name  (
				  id int NOT NULL AUTO_INCREMENT,
				  hit_rule_json text NULL,
				  rule_enabled int NOT NULL DEFAULT 1,
				  PRIMARY KEY (id) USING BTREE
				) $charset_collate;";

				dbDelta( $sql );

			}
			else {

				// We are doing an upgrade
				// Figure this out down the road as we make DB updates

			}

		}

  }

  static function uninstall() {

  	global $wpdb;		
	$rules_table_name = $wpdb->prefix . 'tua_rules';
	$hits_table_name = $wpdb->prefix . 'tua_hits';

	// Drop Custom Tables

	$wpdb->query("DROP TABLE IF EXISTS " . $rules_table_name);
	$wpdb->query("DROP TABLE IF EXISTS " . $hits_table_name);

	// Delete Options
	delete_option("tua_require_cookie");
	delete_option("tua_required_cookie_name");

  }

}