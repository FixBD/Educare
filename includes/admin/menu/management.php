<?php 
// Prevent direct access to the file
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * ### Educare Management
 * 
 *  Here admin can add, edit, update class, group, subject, exam, year, extra fields.
 * Refferange functions:
 * educare_setting_subject()
 * educare_get_all_content()
 * @since 1.4.0
 * @last-update 1.4.0
 */

educare_check_access();
educare_tab_management();

?>

