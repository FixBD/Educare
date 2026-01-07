<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/** 
 * ### Educare Settings
 * 
 * Features for manage educare settings.
 * 
 * @since 1.0.0
 * @last-update 1.4.0
 */


if (current_user_can( 'administrator' )) {
	// Create tab
	$action = 'settings';
	$tab = array (
		// Tab name => Icon
		'settings' => 'admin-generic',
		'default_photos' => 'format-image',
		'grading_system' => 'welcome-learn-more',
	);
	
	educare_tab_management($action, $tab);
}


?>
