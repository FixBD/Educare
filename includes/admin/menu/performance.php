<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * ### Educare Performance
 * 
 * Here admin can change multiple students class, year, group just one click! Most usefull when need to promote students (one class to onother) or need to update mulltiple studens.
 * 
 * @since 1.4.0
 * @last-update 1.4.0
 */

// Create tab
$action = 'performance';
$tab = array (
  // Tab name => Icon
  'promote_students' => 'welcome-learn-more'
);

educare_check_access();
educare_tab_management($action, $tab);

?>

