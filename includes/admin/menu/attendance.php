<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * ### Educare Attendance Systems
 * 
 * Using this features admin (teacher) can add subject wise multiple student attendance at a same time.
 * 
 * need to add action in => educare_get_tab_management();
 * 
 * @since 1.2.4
 * @last-update 1.4.2
 */

// Create tab
$action = 'attendance';
$tab = array (
	// Tab name => Icon
  'all-data' => 'text-page',
  'add-data' => 'plus-alt',
  'update-data' => 'update',
  // 'import-data' => 'database-import'
);

educare_check_access();
educare_tab_management($action, $tab);

?>

