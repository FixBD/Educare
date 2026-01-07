<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

// Educare default settings
require_once(EDUCARE_INC.'database/default-settings.php');

/**
 * ### Check educare database version
 * 
 * @since 1.2.0
 * @last-update 1.6.0
 * 
 * @return void
 */

function educare_database_check($db) {
	global $wpdb;
	$table = $wpdb->prefix.$db;

	// if database not exists
	if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
		// database not exists
	 return true;
	} else {
		// if database exists, and check (educare) database version
		$info = educare_check_status('educare_info');
		// if database version exists, that's mean our database version 1.2.0+ otherwise our database is old. (educare) old databse not support educare_info key.

		if ($info) {
			if ($db == 'educare_settings') {
				$current_db = EDUCARE_SETTINGS_VERSION;
				$istaled_db = $info->$db;

				// if database db_version exists. check if our current (educare) database version 1.0+ or not. if is less then 1.0 then return TRUE. and create/insert our latest database
				if (!($current_db <= $istaled_db)) {
					return true;
				}
				
			}
		} else {
			// old (educare) database
			return true;
		}
		
	}
}



/**
 * ### Create educare database
 * 
 * Create educare database for store results, students and settings data
 * - educare_settings
 * - educare_results
 * - educare_students
 * - educare_marks
 * 
 * @since 1.0.0
 * @last-update 1.2.8
 * 
 * @return void
 */

function educare_database_table($db = null) {
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();

	// Create table for educare (plugins) settings
  $settings = $wpdb->prefix."educare_settings";
  $educare_settings = "CREATE TABLE $settings (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`list` varchar(80) NOT NULL,
		`data` longtext NOT NULL,
		PRIMARY KEY (id),
		UNIQUE KEY list (list)
	) $charset_collate;";

	// Create table for educare results system
	$results = $wpdb->prefix."educare_results";
	$educare_results = "CREATE TABLE $results (
		`id` mediumint(11) NOT NULL AUTO_INCREMENT,
		`Name` varchar(80) NOT NULL,
		`Roll_No` varchar(80) NOT NULL,
		`Regi_No` varchar(80) NOT NULL,
		`Class` varchar(80) NOT NULL,
		`Exam` varchar(80) NOT NULL,
		`Year` varchar(80) NOT NULL,
		`Group` varchar(80) NOT NULL,
		`Details` longtext NOT NULL,
		`Subject` longtext NOT NULL,
		`Result` varchar(80),
		`GPA` varchar(80),
		`Others` longtext NOT NULL,
		`Status` varchar(80),
		PRIMARY KEY (id)
  ) $charset_collate;";

	// Create table for educare (plugins) settings
  $students = $wpdb->prefix."educare_students";
  $educare_students = "CREATE TABLE $students (
		`id` mediumint(11) NOT NULL AUTO_INCREMENT,
		`Name` varchar(80) NOT NULL,
		`Roll_No` varchar(80) NOT NULL,
		`Regi_No` varchar(80) NOT NULL,
		`Class` varchar(80) NOT NULL,
		`Year` varchar(80) NOT NULL,
		`Group` varchar(80) NOT NULL,
		`Details` longtext NOT NULL,
		`Subject` longtext NOT NULL,
		`Student_ID` mediumint(11) NOT NULL,
		`Others` longtext NOT NULL,
		`Status` varchar(80) NOT NULL,
		PRIMARY KEY (id)
  ) $charset_collate;";

	// Create table for educare (plugins) settings
  $marks = $wpdb->prefix."educare_marks";
  $educare_marks = "CREATE TABLE $marks (
		`id` mediumint(11) NOT NULL AUTO_INCREMENT,
		`Class` varchar(80) NOT NULL,
		`Group` varchar(80) NOT NULL,
		`Exam` varchar(80) NOT NULL,
		`Year` varchar(80) NOT NULL,
		`Marks` longtext NOT NULL,
		`Details` longtext NOT NULL,
		`Others` longtext NOT NULL,
		`Status` varchar(80) NOT NULL,
		PRIMARY KEY (id)
  ) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	if ($db == 'educare_settings') {
		dbDelta($educare_settings);
	} elseif ($db == 'educare_results') {
		dbDelta($educare_results);
	} elseif ($db == 'educare_students') {
		dbDelta($educare_students);
	} elseif ($db == 'educare_marks') {
		dbDelta($educare_marks);
	} else {
		if (educare_database_check('educare_settings')) {
			dbDelta($educare_settings);
			dbDelta($educare_results);
			dbDelta($educare_students);
			dbDelta($educare_marks);
		}
	}

	if (educare_database_check('educare_settings')) {
		// Set educare default settings
		educare_default_settings();
	}
}


/**
 * ### Clean Educare DB
 * 
 * Clean all (educare) data from database, when user remove/delete/uninstall educare from plugin list. If user uncheck Clear Data at educare settings, this action will be ignored.
 * 
 * @since 1.4.0
 * @last-update 1.4.0
 * 
 * @return void
 */

function educare_uninstall_action($action = null) {
	if (educare_check_status('clear_data') == 'checked' || $action) {
		global $wpdb;

		// Educare database
		$educare_db = array (
			'educare_settings',
			'educare_results',
			'educare_students',
			'educare_marks',
			'educare_teachers',
			'educare_payment',
			'educare_attendance'
		);

		// Drop/remove table from database
		foreach ($educare_db as $table_name) {
			$table = $wpdb->prefix.$table_name;
			$wpdb->query( "DROP TABLE IF EXISTS $table" );
		}

		delete_option('educare_db_sys_tem');
		// remove educare default students photos
		delete_option('educare_files_selector');
		// clear dismissed notices data
		educare_clear_dismissed_notices();
	}

	delete_option('educare_svr_data');
	// All clean!!!
}

