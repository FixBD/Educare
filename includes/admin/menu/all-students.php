<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * ### Manage Educare Students
 * 
 * Here admin can add, edit, update students and their details. For this you have to select the options that you see here. Options details: firt to last (All, Add, Update, Import Students).
 * 
 *  @since 1.0.0
 *  @last-update 1.4.0
 */

educare_check_access();
educare_get_data_management('students');

?>


