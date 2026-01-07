<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

// Define Educare prefix
define('EDUCARE_PREFIX', 'educare_');
// Define default students photos
define('EDUCARE_STUDENTS_PHOTOS', EDUCARE_URL.'assets/img/default.jpg');
// Define default template thumbnail
define('EDUCARE_TEMPLATE_THUMBNAIL', EDUCARE_URL.'assets/img/template.svg');
define('EDUCARE_SVR', 'https://fixbd.com');

/** 
* == Include Educare Admin Menu ==
*	- All Students
*	- All Results
*	- All Teachers
*	- Attendance
* - Mark Sheet
*	- Performance
*	- Management
*	- Settings
*	- About us
*/
require_once(EDUCARE_ADMIN.'menu.php');
// Educare supported script for front-end
require_once(EDUCARE_INC.'support/educare-themes.php');
// Educare grading systems fuctionality
require_once(EDUCARE_INC.'support/grading-systems.php');
// Educare results system (front view)
require_once(EDUCARE_INC.'support/results-systems.php');
require_once(EDUCARE_ADMIN.'svr.php');
require_once(EDUCARE_ADMIN.'updater.php');
require_once(EDUCARE_ADMIN.'db-updater.php');
// Educare default import system
require_once(EDUCARE_INC.'support/educare-default-import-system.php');
// Educare results and certificate shortcode functionality
require_once(EDUCARE_INC.'support/shortcode/educare-results.php');
// Educare template list. Custom results card and this functinality
require_once(EDUCARE_TEMP.'template-functions.php');
// Educare dashbord widget functinality
require_once(EDUCARE_INC.'support/dashbord-widget.php');



/**
 * Sanitize a string by removing any characters that are not alphanumeric, underscores, or dots,
 * and then escaping it using `esc_attr()` to ensure it is safe for use in HTML attributes.
 *
 * @since 1.0.0
 * @last-update 1.0.0
 * 
 * @param string $str The string to be sanitized.
 * @return string The sanitized and escaped string safe for use in HTML attributes.
 */
function educare_esc_str($str) {
	// Ensure $str is a string and is not empty
	if (!is_string($str) || empty($str)) {
		return '';
	}

	// Remove any characters that are not alphanumeric, underscores, or dots
	$str = preg_replace("/[^A-Za-z0-9 _.]/", '', $str);

	// One more protection with WP esc_attr()
	$str = esc_attr($str);
	return $str;
}



/** =====================( Functions Details )======================
 * For check settings status
 * 
 * * Usage example: educare_check_status('confirmation');
 * For checking settings status, if specific settings is enable return{checked}. or disabled return{unchecked}.
 * 
 * Cunenty there are 18 settings status support
 * @see educare_add_default_settings()
 * @link https://github.com/FixBD/Educare/blob/FixBD/includes/database/default-settings.php
 * 
 * Name	============= 	Default	 ===	Details =================
 * 1. confirmation 	 		checked				for delete confirmation
 * 2. guide			  	 		checked				for helps (guidelines) pop-up
 * 3. photos 	 			  	checked				for students photos
 * 4. auto_results 	 		checked				for auto results calculation
 * 5. delete_subject		checked				for delete subject with results
 * 6. clear_field 		 	checked				for delete extra field with results
 * 7. display 		 	 		array()				for modify Name, Roll and Regi number (@since 1.2.0)
 * 8. grade_system 			array()				for grading systems or custom rules (@since 1.2.0)
 * and more..
 * 
 * for check current status =>
 * 1. educare_check_status('confirmation');
 * 2. educare_check_status('guide');
 * 3. educare_check_status('photos');
 * 4. educare_check_status('auto_results');
 * 5. educare_check_status('delete_subject');
 * 6. educare_check_status('clear_field');
 * 7. educare_check_status('Name', true); // true because, this is an array
 * 
 * Above callback function return current status => checked or unchecked

 * @since 1.0.0
 * @last-update 1.2.0
 * 
 * @param string $target	Select specific key and get value
 * @param bull $display	Select specific key with array
 * 
 * @return string
 */
function educare_check_status($target = null, $display = null) {
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
   
	$search = $wpdb->get_results(
    $wpdb->prepare("SELECT * FROM $table WHERE list = %s", 'Settings')
	);
	
	if ($search) {
		foreach ( $search as $print ) {
			$data = educare_decrypt_data($print->data);

			if (empty($target)) {
				return $data;
			}
		}
		
		if ($display) {
			$status = 'unchecked';
			
			if (isset($data->display)) {
				if (property_exists($data->display, $target)) {
					$name = $data->display->$target;
					$value = $name->title;
					$status = $name->status;
				}
			}

			if ($status == 'checked') {
				return $value;
			} else {
				return false;
			}
		} else {
			if ($data) {
				if (property_exists($data, $target)) {
					return $data->$target;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}
}



/**
 * Educare settings data
 * 
 * @since 1.2.0
 * @last-update 1.6.0
 * 
 * @param string $list					Class, Group, Setting, Exam, Year, Extra_field
 * @param string $target				for specific data
 * 
 * @return array|bool
 */
function educare_check_settings($list, $target = null) {
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
   
	$search = $wpdb->get_results(
    $wpdb->prepare("SELECT * FROM $table WHERE list = %s", $list)
	);
	
	if ($search) {
		
		foreach ( $search as $print ) {
			if ($list === 'Settings') {
				$data = educare_decrypt_data($print->data);
			} else {
				$data = json_decode($print->data);
			}

			if (empty($target)) {
				return $data;
			}
		}
		
		if ($target) {
			if (!is_object($data)) {
				return false;
			}
			
			if (property_exists($data, $target)) {
				return $data->$target;
			} else {
				return false;
			}
		}
	} else {
		return false;
	}
}



/**
 * Notify user if anythink wrong in educare (database)
 * 
 * @since 1.2.0
 * @last-update 1.6.0
 * 
 * @param bool $fix_form		to get database update form
 * @param string $db				for specific database
 * 
 * @return void|HTML
 */
function educare_database_error_notice($fix_form = null, $db = null) {
	if ($fix_form) {
		echo '<div class="m-4 text-center"><img src="'.esc_url(EDUCARE_URL."assets/img/educare.svg").'" alt="Educare"/ width="50%" style="max-width: 300px;"></div>';

		if (isset($_POST['update_educre_database'])) {
			global $wpdb;
			// check if user access this request
			educare_check_access('settings');

			if ($db == 'educare_settings') {
				$database = array (
					'educare_settings',
					'educare_results',
					'educare_students',
					'educare_marks',
					'educare_teachers',
					'educare_payment',
					'educare_attendance'
				);

				foreach ($database as $edb) {
					$edb = sanitize_text_field( $edb );
					$remove = $wpdb->prefix.$edb;
					$wpdb->query( "DROP TABLE $remove" );
				}

				// new database
				educare_database_table();
				
			} elseif ($db = 'general_update') {
				// update all database according to the latest version
				educare_update_database();
			} else {
				$edb = sanitize_text_field( $db );
				$edb = $wpdb->prefix.$edb;
				$wpdb->query( "DROP TABLE $edb" );
				educare_database_table($db);
			}
			
			echo '<div class="notice notice-success is-dismissible m-5"><p>'.
				sprintf(
					__(
						'Successfully updated (Educare) database. Click here to <a href="%s">Start</a>.',
						'educare'
					),
					esc_url($_SERVER["REQUEST_URI"])
				)
			.'</p></div>';
		} else {
			?>
			<form class="alert alert-primary p-5 m-5 rounded" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
				<div class="content">
					<?php
					echo sprintf(
						__(
							'<h5>Database Update Required</h5><p>Your current Educare database is outdated or may be corrupted. To run the latest version (<b>%d</b>) of Educare, an update to the database is required. This process will only update Educare-related data, leaving all other WordPress data unaffected. Please click to proceed with the database update.</p><p><strong>Please note:</strong> It is recommended to back up your Educare database before proceeding with the update.</p>',
							'educare'
						),
						esc_html( EDUCARE_VERSION )
					)
					?>
					<button class="btn btn-outline-danger" name="update_educre_database"><?php _e('Update Database', 'educare');?></button>
				</div>
			</form>
			<?php
		}
	} else {
		echo '<div class="notice notice-error is-dismissible m-5"><p>'.sprintf(__(
			'Oops! Something went wrong. Please visit (Educare) settings or <a href="%s">click here to fix</a>.',
			'educare'
		), esc_url(admin_url('admin.php?page=educare-settings'))).'</p></div>';
	}
}


/**
 * Delete confirmation
 * 
 * Pop-up delete/remove confirmation if {confirmation} status is => checked.
 * 
 * For example, when users delete/remove a Subject, like - Science. this function pop-up (alart) the user like this - You want to remove 'Science' from the 'Subject' list. Are you sure?.
 * 
 * Simple but powerful!
 * 
 * @since 1.0.0
 * @last-update 1.0.0
 * 
 * @param string $list				Specific keys value: Subject/Class/Exam/Year/Extra Field...
 * @param string $content			Specific keys value
 * @param string|int $year		Specific keys value
 * 
 * @return string
 */
function educare_confirmation($list, $content = null, $year = null) {
	if (educare_check_status('confirmation') == 'checked') {
		if ($content) {
			if ($list == 'remove_results') {
				if (empty($year)) {
					$message = "Are you sure to delete all results of the ".esc_html($content)."? It will delete all session results.";
					echo "onclick='return confirm(".' " '.esc_js( $message ).' " '.")' ";
				} else {
					$message = "Are you sure to delete all results of the ".esc_html($content)." in ".esc_html($year)." ? It will delete only your selected year (".esc_html($year).") results.";
					echo "onclick='return confirm(".' " '.esc_js( $message ).' " '.")' ";
				}
			} else {
				$message = "You want to remove ".esc_html($content)." from the ".esc_html($list)." list. Are you sure?";
				echo "onclick='return confirm(".' " '.esc_js( $message ).' " '.")' ";
			}
		} else {
			return "onclick='return confirm(".' " '.esc_js( $list ).' " '.")' ";
		}
	}
}



/**
 * Function for educare smart guideline
 * 
 * @since 1.0.0
 * @last-update 1.6.0
 * 
 * @param string $guide	  Specific string/msgs
 * @param string $success	messege type (success, info, warning or error)
 * 
 *	@return string|html
 */
function educare_guide_for($guide, $success = 'info') {
	if (educare_check_status('guide') == 'checked') {
		$notice_id = sanitize_text_field($guide);
		$user_id = get_current_user_id();
		// Retrieve the current array of dismissed notices
		$dismissed_notices = get_user_meta($user_id, 'educare_dismissed_notices', true);

		if ($dismissed_notices) {
			if (in_array($notice_id, $dismissed_notices)) return;
		}

		$url = '/wp-admin/admin.php?page=educare-management&';

		if ($guide == 'add_class') {
			$guide = sprintf(
				__(
					"Do you want to add more <b>Class</b>, <b>Exam</b> or <b>Year</b>? Click here to add <a href='%s' target='_blank'>Class</a>, <a href='%s' target='_blank'>Exam</a> or <a href='%s' target='_blank'>Year</a>.",
					'educare'
				),
				esc_url($url . 'Class'),
				esc_url($url . 'Exam'),
				esc_url($url . 'Year')
			);
		}
		
		if ($guide == 'add_extra_field') {
			$guide = sprintf(
				__(
					"Click here to <a href='%s' target='_blank'>Add More Fields</a>.",
					'educare'
				),
				esc_url($url . 'Extra_field')
			);
		}
		
		if ($guide == 'add_subject') {
			$guide = sprintf(
				__(
					"Click here to <a href='%s' target='_blank'>Add More Subject</a>. You can also disable, hide and control one or more of these marks term from <a href='%s' target='_blank'>Card Settings</a>.",
					'educare'
				),
				esc_url($url . 'Subject'),
				esc_url('/wp-admin/admin.php?page=educare-settings&menu=Results_card')
			);
		}
		
		if ($guide == 'optinal_subject') {
			$guide = __("If this student has an optional subject, then select optional subject. otherwise ignore it.<br><b>Note: It's important, when students will have a optional subject</b>.", 'educare');
		}

		if ($guide == 'display_msgs') {
			$guide = __("It is not possible to deactivate both (<b>Regi number or Roll number</b>). Because, it is difficult to find students without roll or regi number. So, you need to deactivate one of these (Regi or Roll Number). If your system has one of these, you can select it. Otherwise, it is better to have both selected (<b>Recommended</b>).", 'educare');
		}

		if ($guide == 'db_error') {
			$guide = __("Something went wrong! Please fix it, Otherwise some of our plugin settings will be not work properly. So, please contact to your developer for solve this issue. Make sure to alabled educare <b>(AI) Problem Detection</b> options. Also, you can go to plugin (Educare) settings and press <b>Reset Settings</b> to fix this error. Hope you understand.", 'educare');
		}

		if (!$success) {
			$success = 'error';
		}

		return "<div class='notice notice-".esc_attr($success)." is-dismissible educare-notice' data-id='".esc_attr($notice_id)."'><p>".wp_kses_post($guide)."</p></div>";
	}
}


/**
 * Function for notice
 * 
 * @since 1.6.0
 * @last-update 1.6.0
 * 
 *	@return string|html
 */
function educare_dismiss_notice() {
	if (!isset($_POST['noticeKey'])) {
    wp_send_json_error();
  }

	$notice_key = sanitize_text_field($_POST['noticeKey']);
	// Example: Store notice dismissal in user meta (as an array)
	$user_id = get_current_user_id();

	if ($user_id) {
		// Retrieve the current array of dismissed notices
		$dismissed_notices = get_user_meta($user_id, 'educare_dismissed_notices', true);

		// Ensure the value is an array
		if (!is_array($dismissed_notices)) {
			$dismissed_notices = array();
		}

		// Add the new notice key to the array if not already present
		if (!in_array($notice_key, $dismissed_notices)) {
			$dismissed_notices[] = $notice_key;
		}

		// Update user meta with the updated array
		update_user_meta($user_id, 'educare_dismissed_notices', $dismissed_notices);
		wp_send_json_success();
	} else {
		wp_send_json_error();
	}
}

add_action('wp_ajax_educare_dismiss_notice', 'educare_dismiss_notice');


/**
 * Function for dismiss notice
 * 
 * @since 1.6.0
 * @last-update 1.6.0
 * 
 *	@return string|html
 */
function educare_clear_dismissed_notices() {
	wp_clear_scheduled_hook('educare_svr_event');

	// Define the roles to target
	$roles_to_target = array('administrator', 'educare_admin', 'educare_teachers', 'educare_class_admin');

	// Get users who have one of the specified roles
	$users = get_users(array(
		'role__in' => $roles_to_target // Fetch users with any of the roles
	));

	// Loop through users and delete the meta key
	foreach ($users as $user) {
		// Delete the 'educare_dismissed_notices' meta key for each user
		delete_user_meta($user->ID, 'educare_dismissed_notices');
	}
}



/**
 * Display a message notice to the user.
 *
 * This function displays a message notice to the user in the WordPress admin area.
 *
 * @param string $msg The message content to display.
 * @param bool $success Whether the message is a success message (default: true).
 * @param bool $sticky Whether the notice should be sticky (default: true).
 * @return string The HTML markup for the notice.
 */
function educare_show_msg($msg, $success = true, $sticky = true) {
	if (educare_check_status('guide') !== 'checked') {
		return ''; // Explicit return to avoid undefined behavior
	}

	// Restrict allowed notice types
	$allowed_notice_types = array('success', 'error', 'warning', 'info');

	// Determine the notice type
	if ($success === true) {
		$notice_type = 'success';
	} elseif ($success === false) {
		$notice_type = 'error';
	} elseif (in_array($success, $allowed_notice_types, true)) {
		$notice_type = $success;
	} else {
		$notice_type = 'info'; // Default to 'info' if invalid value is provided
	}

	// Construct the message
	$message = '<div class="notice notice-' . esc_attr($notice_type);

	if ($sticky) {
		$message .= ' is-dismissible">';
		$message .= '<p>' . wp_kses_post($msg) . '</p>';
		$message .= '<button class="notice-dismiss"></button></div>';
		return '<div class="sticky_msg">' . $message . '</div>';
	}

	$message .= '"><p>' . wp_kses_post($msg) . '</p></div>';

	return $message;
}






/**
 * display result value
 * 
 * Usage example: educare_value('Bangla', 1);
 * Simple but super power!
 * Without this function result system is gone!!!!!
 * 
 * @since 1.0.0
 * @last-update 1.4.0
 * 
 * @param string $list					Select object array
 * @param int $id								Select specific database rows by id
 * @param int $arr							If selected data is arr|object
 * @param bool $add_students		if data for students
 * 
 * @return string|int|float|bool / database value
 */
function educare_value($list, $id, $arr = null, $add_students = null) {
	global $wpdb, $import_from;
	
	if ($add_students or $import_from) {
		$table_name = $wpdb->prefix . 'educare_students';
	} else {
		$table_name = $wpdb->prefix . 'educare_results';
	}

	$query = $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id );
	$educare_results = $wpdb->get_results($query);
	
	if ($educare_results) {
		$value = '';
		
		foreach($educare_results as $print) {
			if (property_exists($print, $list)) {
				$value = $print->$list;
			}
		}
		
		if ($arr) {
			$value = json_decode($value, true);

			// Chek if key exist or not. Otherwise its show an error
			if (is_array($value)) {
				if (key_exists($arr, $value)) {
					return $value[$arr];
				}
			}

		} else {
			return $value;
		}
	}
}



/**
 * Display content options
 * Usage example: educare_get_options('Class', $Class);
 * 
 * it's only return <option>...</option>. soo, when calling this function you have must add <select>...</select> (parent) tags before and after.
 * 
 * Example:
		echo '<select id="Class" name="Class" class="fields">';
			echo '<option value="0">Select Class</option>';
			educare_get_options('Class', $Class)
		echo '</select>';
		
		echo '<select id="Class" name="Exam" class="fields">';
			echo '<option value="0">Select Class</option>';
			educare_get_options('Exam', $Exam)
		echo '</select>';
 * 
 * @since 1.0.0
 * @last-update 1.2.0
 * 
 * @param string $list			Specific string
 * @param int|string $id		Specific var
 * 
 * @return string
 */
function educare_get_options($list, $id, $selected_class = null, $add_students = null) {
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
	
	if ($list == 'Subject' or $list == 'optinal') {
		$query = $wpdb->prepare( "SELECT * FROM $table WHERE list = %s", 'Class' );
	} else {
		$query = $wpdb->prepare( "SELECT * FROM $table WHERE list = %s", $list );
	}

	$results = $wpdb->get_results($query);
	
	if ($results) {
		
		foreach ( $results as $print ) {
			$results = $print->data;
			// $subject = ["Class", "Regi_No", "Roll_No", "Exam", "Name"];

			if ($list == 'Class' or $list == 'Group' or $list == 'Rattings') {
				$results = json_decode($results, true);
				$cls = array();
				foreach ( $results as $class => $sub ) {
					$cls[] = $class;
				}
				$results = json_encode($cls);
			}
			
			if ($list == 'Subject' or $list == 'optinal') {
				$results = json_decode($results, true);

				if (!$selected_class) {
					// Auto select first class if not selecet
					// Getting the first class name
					foreach ( $results as $class => $sub) {
						// Get the class
						$selected_class = $class;
						// break to loops!
						break;
					}
				}

				if (key_exists($selected_class, $results)) {
					$results = $results[$selected_class];
				}
				
				if (key_exists('select_subject', $_POST)) {
					$results = array_merge($results, $_POST['select_subject']);
				} else {
					$all_subject = educare_value('Subject', $id, '', $add_students);
					$all_subject =  json_decode($all_subject, true);

					if (isset($_POST['Group'])) {
						$Group = sanitize_text_field($_POST['Group']);
					} else {
						$Group = educare_value('Group', $id, '', $add_students);
					}
					
					if ($Group) {
						$all_group = educare_demo_data('Group');

						if (property_exists($all_group, $Group)) {
							$Group = $all_group->$Group;
							
							if ($all_subject) {
								foreach ($Group as $sub) {
									if (key_exists($sub, $all_subject)) {
										array_push($results, $sub);
									}
								}
							}
						}
						
					}
					
				}
				
				$cls = array();

				if ($results) {
					foreach ( $results as $class) {
						$cls[] = $class;
					}
				}

				$results = json_encode($cls);

			}

			$results = json_decode($results);
			$results = str_replace(' ', '_', $results);
			
		}
	}
	
	$serial = 0;
	
	if ($results) {
		// Sorts an array in descending order

		if ($list == 'Year') {
			rsort($results);
		}
		
		foreach ( $results as $print ) {
			$display = str_replace('_', ' ', $print);
			$name = $print;
			$type = $print;
			
			if ($list == 'Extra_field') {
				$display = substr(strstr($display, ' '), 1);
				$name = str_replace(' ', '_', $display);
				
				if ($id == 'add') {
					$value = sanitize_text_field($_POST[$name]);
				} else {
					if ($add_students) {
						$value = sanitize_text_field(educare_value('Details', $id, $name, true));
					} else {
						$value = sanitize_text_field(educare_value('Details', $id, $name));
					}
				}
				
				$type = strtok($print, '_');
				
				if (empty($value)) {
					$placeholder = "Enter Students ".str_replace('_', ' ', $display)."";
				}
				if (!empty($value)) {
					$placeholder = '';
				}
			
				?>
				<div class="wrap-input">
					<span class="input-for"><?php echo esc_html($display);?>:</span>
					<label for="<?php echo esc_attr($name);?>" class="labels" id="<?php echo esc_attr($name);?>"></label>
					<input type="<?php echo esc_attr($type);?>" name="<?php echo esc_attr($name);?>" class="fields" value="<?php echo esc_attr($value);?>" placeholder="<?php echo esc_attr("$value$placeholder");?>">
					<span class="focus-input"></span>
				</div>
				<?php
			}
			
			
			if ($id == 'add') {
				$value = sanitize_text_field($_POST[$name]);
			} else {

				if ($list == 'Subject' or $list == 'optinal') {
					$value = sanitize_text_field(educare_value('Subject', $id, $name, $add_students));
				} else {
					$value = sanitize_text_field(educare_value('Details', $id, $name, $add_students));
				}
				
			}
			
			if ($list == 'Subject') {

				if (isset($_POST[$name])) {
					$value = sanitize_text_field($_POST[$name]);
				}
				
				$optinal = substr(strstr($value, ' '), 1);
						
				if ($optinal != false) {
					$value = $optinal;
				}
				
				if (empty($value)) {
					$placeholder = "0.0";
				}
				if (!empty($value)) {
					$placeholder = '';
				}

				if (educare_check_status('auto_results') == 'checked') {
					$disabled = 'disabled';
				} else {
					$disabled = 'disabled';
				}
				
				?>
				<tr>
					<td><?php echo esc_html($serial+=1);?></td>
					<td><?php echo esc_html($display);?></td>
					
					<?php 
					if ($add_students) {
						echo '<input type="hidden" name="'.esc_attr($name).'">';
					} else {
						?>
						<td><label for="<?php echo esc_attr($name);?>" class="mylabels" id="<?php esc_attr($name);?>"></label>
						<input id="<?php echo esc_attr($name);?>" type="number" name="<?php echo esc_attr($name);?>" class="myfields" value="<?php echo esc_attr($value);?>" placeholder="<?php echo esc_attr("$value $placeholder");?>"></td>
						
						<td><input type="number" name="grade[]" class="myfields" value="<?php echo esc_attr(educare_letter_grade($value, true));?>" placeholder="auto" <?php echo esc_attr($disabled);?>></td>
						<?php
					}
					?>
					
				</tr>
				<?php
			}
			
			if ($list == 'optinal') {

				if (isset($_POST[$name])) {
					$value = sanitize_text_field($_POST[$name]);
				}

				if (strpos($value, ' ')) {
					$selected = 'selected';
					$checked = '✓';
				} else {
					$selected = $checked = '';
				}
					
				echo '<option value="'.esc_attr($display).'" '.esc_attr($selected).'>'.esc_html($display).' '.esc_html($checked).'</option>';
				
			}
			
			if ($list == 'Class' or $list == 'Group' or $list == 'Exam' or $list == 'Year'  or $list == 'Term'  or $list == 'School') {
				$selected = '';
				$check = "";
				if ($id == $display) {
					$selected = 'selected';
					$check = '✓';
				}
				echo '<option value="'.esc_attr($display).'" '.esc_attr($selected).'>'.esc_html($display).''.esc_html($check).'</option>';
			}
			
		}
	} else {
		if ($list == 'Subject') {
			?>
			<tr>
				<td colspan='4'><div class='notice notice-error is-dismissible'>
					<p>Currently, you don't have added any subject in this class (<?php echo esc_html($selected_class);?>). <?php echo "<a href='".esc_url('/wp-admin/admin.php?page=educare-management&Subject')."' target='_blank'>Click here</a>";?> to add subject or <a href='#Class'>Change Class</a></p>
				</div></td>
			</tr>
			<?php
		} else {
			echo "<option value=''><div class='notice notice-error is-dismissible'><p>".__('Empty', 'educare')."</p></div><option>";
		}
	}
	
}



/**
 * function for all subject
 * usage => for html option
 * echo '<select name="">';
 * echo educare_get_all_subject(true);
 * echo '</select>';
 * 
 * usage => for retrn array
 * educare_get_all_subject();
 * 
 * @param bool $options 			for return subject with html option structure
 * @return mixed
 * @since 1.4.2
 * @last-update 1.4.2
 * 
 */
 function educare_get_all_subject($options = false, $selected_sub = array()) {
	$class = educare_get_data('Class');
	$group = educare_get_data('Group');

	$subject = array();

	// get class wise subject
	if ($class) {
		foreach ($class as $class_name) {
			$sub = educare_get_data('Class', $class_name);
			if ($sub) {
				$subject = array_unique(array_merge($subject, $sub));
			}
		}
	}

	// get group wise subject
	if ($group) {
		foreach ($group as $group_name) {
			$sub = educare_get_data('Group', $group_name);
			if ($sub) {
				$subject = array_unique(array_merge($subject, $sub));
			}
		}
	}

	if ($options) {
		if ($subject) {
			$options = '';

			// $selected_sub = array();
			if (isset($_POST['Subject'][0]['all'])) {
				$selected_sub = $_POST['Subject'][0]['all'];
			}

			foreach ($subject as $value) {
				// keep selected subject
				$is_selected = in_array($value, $selected_sub);
				$options .= '<option value="' . esc_attr($value) . '" ' . selected($is_selected, true, false) . '>' . esc_html($value) . '</option>';
			}

			return $options;
		}
	} else {
		return $subject;
	}
}



/**
 * Get specific class subject
 * 
 * Usage example: educare_get_options_for_subject('Class 6', $Subject);
 * it's only return <option>...</option>. soo, when calling this function you have must add <select>...</select> (parent) tags before and after.
 * Example:
 * 
		echo '<select id="Subject" name="Subject" Subject="fields">';
			echo '<option value="0">Select Subject</option>';
			educare_get_options('Subject', $Subject)
		echo '</select>';

 * @since 1.2.4
 * @last-update 1.2.4
 * @param string $class				For specific class wise subject
 * @param string $value				Specific variable to make fields selected
 * 
 * @return string|html
 */
function educare_get_options_for_subject($data_for, $target, $value = null) {
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
	$query = $wpdb->prepare( "SELECT * FROM $table WHERE list = %s", $data_for );
	$results = $wpdb->get_results($query);
	
	if ($results) {
		foreach ( $results as $print ) {
			$data = $print->data;
			$data = json_decode($data, true);

			if (key_exists($target, $data)) {
				if ($data[$target]) {
					echo '<option value="">All Subject</option>';
					
					foreach ($data[$target] as $subject) {
						$selected = '';
						$check = "";
						if ($subject == $value) {
							$selected = 'selected';
							$check = '✓';
						}

						echo '<option value="'.esc_attr($subject).'" '.esc_attr($selected).'>'.esc_html($subject).''.esc_html($check).'</option>';
					}
				} else {
					echo '<option value="">All Subject</option><option value="" disabled>Subject Not Found</option>';
				}
			}
		}
	}
}



/**
 * Option for class or group
 * 
 * @since 1.2.0
 * @last-update 1.2.4
 * 
 * @param string $target				for specific data
 * @param string $current				selected data
 * @param string $option_for		option for Class or Group
 * 
 * @return mixed
 */
function educare_show_options($target, $current = null, $option_for = 'Class') {
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
	$query = $wpdb->prepare( "SELECT * FROM $table WHERE list = %s", $option_for );
	$results = $wpdb->get_results($query);
	
	if ($results) {
		foreach ( $results as $print ) {
			$data = $print->data;
			$data = json_decode($data, true);

			if (key_exists($target, $data)) {
				foreach ($data[$target] as $subject) {
					$selected = '';
					$check = "";
					if ($subject == $current) {
						$selected = 'selected';
						$check = '✓';
					}

					if ($option_for == 'Group') {
						echo '<tr><td><input type="checkbox" name="select_subject[]" value="'.esc_attr($subject).'"></td><td>
						<label for="select_subject">'.esc_attr($subject).'</label></td></tr>';
					} else {
						echo '<option value="'.esc_attr($subject).'" '.esc_attr($selected).'>'.esc_html($subject).''.esc_html($check).'</option>';
					}
					
				}

			}
		}
	}
}


/**
 * Specific students data
 * Usage example: educare_get_data_by_student($id, $data);
 * 
 * @since 1.2.0
 * @last-update 1.2.0
 * 
 * @param int $id			 				database row id
 * @param object $data				$data object
 * 
 * @return mixed
 */
function educare_get_data_by_student($id, $data) {
	global $wpdb;
	$table = $wpdb->prefix."educare_results";
	$id = sanitize_text_field($id);
	$results = $wpdb->get_row("SELECT * FROM $table WHERE id='$id'");

	if ($results) {
		if ($data == 'Details') {
			$details = json_decode($results->Details, true);
			$count = 1; // for add specific tags (div/tr/ul) in every 4 foreach loop

			foreach ($details as $key => $value) {
				if ($count%2 == 1) {  
					echo "<tr>";
				}
					
				echo "<td>".esc_html(str_replace('_', ' ', $key))."</td><td>".esc_html($value)."</td>"; 
				
				if ($count%2 == 0) {
					echo "</tr>";
				}
			
				$count++;
			
			}
		}

		if ($data == 'Subject') {
			$subject = json_decode($results->Subject, true);
			$serial = 1;
			$count = 1; // for add specific tags (div/tr/ul) in every 4 foreach loop
			$results_card = educare_get_card_data();
			$results_card = educare_requred_data($results_card, true);

			?>
			<div class="table_body">
				<table class="grade_sheet">
					<thead>
						<tr>
							<?php
							$field = array (
								'no',
								'subject',
								'marks',
								'grade'
							);
	
							foreach ($field as $field_name) {
								if (key_exists($field_name, $results_card)) {
									echo '<th>'.esc_html( $results_card[$field_name] ).'</th>';
								}
							}
							?>
						</tr>
					</thead>
					
					<tbody>
						<?php
						
						foreach ($subject as $name => $marks) {
							$mark = educare_display_marks($marks);
							echo "<tr>";
								if (key_exists('no', $results_card)) echo "<td>".esc_html($serial++)."</td>";
								if (key_exists('subject', $results_card)) echo "<td>".esc_html(str_replace('_', ' ', $name))."</td>";
								if (key_exists('marks', $results_card)) echo "<td>".esc_html($mark)."</td>";
								if (key_exists('grade', $results_card)) echo "<td>".wp_kses_post(educare_letter_grade($marks))."</td>";
							echo "</tr>";
						}
						?>
					</tbody>
				</table>
			</div>
			<?php
		}

	} else {
		echo '<div class="error_results"><div class="error_notice">Something went wrong!</div></div>';
	}

}



/**
 * Get the URL of a WordPress attachment.
 *
 * This function retrieves the URL of a WordPress attachment based on the provided attachment ID.
 *
 * @since 1.6.0
 * @last-update 1.6.0
 * 
 * @param int|null $id The attachment ID (default: null).
 * @param bool $blank Whether to return a blank URL if the attachment is not found (default: false).
 * @return string The URL of the attachment.
 */
function educare_get_attachment($id = null, $blank = false) {
	// Get the attachment URL based on the provided ID
	$attachment = wp_get_attachment_url($id);

	if ($attachment) {
		// If attachment URL is found, return it
		return $attachment;
	} else {
		if ($blank) {
			if ($blank === 'logo1' && $id == 0) {
				return EDUCARE_URL . 'assets/img/card-logo1.svg';
			}

			if ($blank === 'logo2' && $id == 0) {
				return EDUCARE_URL . 'assets/img/card-logo2.svg';
			}

			// Return a blank URL if requested
			return false;
		} else {
			// Get the default attachment URL from settings
			$default_attachment = wp_get_attachment_url(get_option('educare_files_selector', 0));

			if ($default_attachment) {
				// Return the default attachment URL if available
				return $default_attachment;
			} else {
				// Return a predefined default URL
				return EDUCARE_STUDENTS_PHOTOS;
			}
		}
	}
}



/**
 * Access WP gallery for upload/import students photos
 * Usage example:
 * educare_files_selector('add_results', '');
 * for update selected photos
 * educare_files_selector('add_results', '$print');
 * 
 * @since 1.0.0
 * @last-update 1.0.0
 * 
 * @param string $list		Getting file selector for Add/Update/Default
 * @param object $print	Get old data when update
 * 
 * @return null|HTML
 */
function educare_files_selector($print) {
	wp_enqueue_media();
	
	$attachment_id = get_option( 'educare_files_selector' );
	$default_img = educare_get_attachment($attachment_id);

	if (isset($_POST['Others']['Photos'])) {
		$img = sanitize_text_field($_POST['Others']['Photos']);
		$img_src = wp_get_attachment_url( $img );
    $img_type = __("Custom Photos", 'educare');
		$guide = __("If you change photos, Please upload or select  a custom photos from gallery that's you want!", 'educare');
	} else {
		$img = '';
		$img_src = $default_img;
		$img_type = __("Default Photos", 'educare');
		$guide = __("Current photos are default. Please upload or select  a custom photos from gallery that's you want!", 'educare');
	}

	if ($img == 'URL' or $img == '') {
		$img_src = $default_img;
	}
	?>

	<!-- Default value -->
	<div class="educare_data_field">
		<div class="educareFileSelector_img_src" data-value="<?php echo esc_url($img_src);?>"></div>
		<div class="educareFileSelector_default_img" data-value="<?php echo esc_url($default_img);?>"></div>
		<div class="educareFileSelector_img" data-value="<?php echo esc_url($img);?>"></div>
		<div class="educareFileSelector_img_type" data-value="<?php echo esc_html($img_type);?>"></div>
		<div class="educareFileSelector_guide" data-value="<?php echo esc_html($guide);?>"></div>
	</div>
	
	<div id='educare_files_selector_disabled'>
		<div id='educare_files_uploader' class='educare_upload add'>
			<div class='educare_files_selector'>
				<img id='educare_attachment_preview' class='educare_student_photos' src='<?php echo esc_url($img_src);?>'/>
				<h3 id='educare_img_type' class='title'><?php echo esc_html($img_type);?></h3>
			</div>
			
			<p id='educare_guide'><?php echo esc_html($guide);?></p>
			<div id='educare_default_help'></div>
				
			<input type="hidden" name='educare_attachment_url' id='educare_attachment_url' value='<?php echo esc_attr($img_src);?>'>
			<input type="hidden" name='Others[Photos]' id='educare_attachment_id' value='<?php echo esc_attr($img);?>'>
		
			<input type='button' id='educare_attachment_title' class="btn btn-sm my-1 full" value='<?php _e('Please select a photos', 'educare');?>' disabled>
			
			<input type='button' id='educare_attachment_clean' class='btn btn-sm btn-danger my-1 educare_clean full' value='&#xf171 Undo' style='display: none'>

			<div class="select">
				<input id="educare_upload_button" type="button" class="btn btn-sm btn-success my-1" value="<?php _e('Upload Photos', 'educare');?>">

				<?php
				if ($img_src != $default_img) {
					echo "<input type='button' id='educare_attachment_default' class='button' onClick='".esc_js('javascript:;')."' value='Use Default photos'>";
				} else {
					echo "<input type='hidden' id='educare_attachment_default'>";
				}
				?>
			</div>
		</div>
	</div>

	<?php
}



/** ====================( Functions Details )=======================
===================================================================
						      Educare CRUD and Support Functions
===================================================================
====================( BEGIN CRUD FUNCTIONALITY )===================*/

/**
 * Requred form fields
 * 
 * sample array
	$array = array(
		'Roll_No' => 1,
		'Regi_No' => 2,
		'Year' => 2022,
		'Class' => 'Class 6',
		'Exam' => ''
	);
 * Usage example: educare_requred_data(educare_check_status('display');
 * 
 * @since 1.3.0
 * @last-update 1.3.0
 * 
 * @param array $array		select all and retun only requred (checked) field
 * @param array $value		retun all rewured fields with key value
 * @param array $all		  retun all fields key (checked or unchecked)
 * 
 * @return array
 */
function educare_requred_data($array, $value = null, $all = null) {
  $default = $array;
  $requred = array();

	if ($default) {
		foreach ($default as $key => $val) {
			if ($all) {
				if ($value) {
					$requred[$key] = $val->title;
				} else {
					array_push($requred, $key);
				}
			} else {
				if ($val->status == 'checked') {
					
					if ($value) {
						$requred[$key] = $val->title;
					} else {
						array_push($requred, $key);
					}
					
				}
			}
		}
	}

  return $requred;
}



/**
 * Combine requred data with forms field ($_POST)
 * 
 * Usage example: educare_combine_fields($array);
 * 
 * @since 1.3.0
 * @last-update 1.3.0
 * 
 * @param array $array		for combine with $_POST
 * @param array $ignore	for remove specific key from array
 * @return array
 */
function educare_combine_fields($array1, $ignore = null, $array2 = null, $normal = null) {
	if (!$normal) {
		$array1 = educare_requred_data($array1);
	}
  
	if (!$array2) {
		$array2 = $_POST;
	}
  
  $combine = array();

  foreach ($array1 as $value) {
    if (key_exists($value, $array2)) {
      $combine[$value] = sanitize_text_field( $array2[$value] );
    } else {
      $combine[$value] = false;
    }
  }

  if ($ignore) {
    foreach ($ignore as $remove) {
      unset($combine[$remove]);
    }
  }
  
  return $combine;
}



/**
 * Combine keys and properties from a stdClass object based on provided keys.
 *
 * This function combines keys and properties from a stdClass object based on the provided keys.
 *
 * @param array $keys An array of keys to filter the properties.
 * @param stdClass $object The stdClass object containing properties.
 * @return array An associative array with filtered keys and their corresponding values.
 */
function educare_combineKeysAndProperties(array $keys, stdClass $object): array {
	$result = array();

	// Loop through each property of the stdClass object
	foreach ($object as $property => $value) {
		// Check if the property is in the provided keys array
		if (in_array($property, $keys)) {
			// Add the property and its value to the result array
			$result[$property] = $value;
		}
	}

	return $result;
}



/**
 * Check if specific array key is empy or not
 * 
 * Same as array_keys($array, null);
 * 
 * @since 1.3.0
 * @last-update 1.4.0
 * 
 * @param array $array		for check empty
 * @param bool $normal 		for ignore educare settings status
 * @param bool $text_only	return without extra html
 * @return bool|string
 */
function educare_is_empty(array $array, $normal = null, $text_only = null, $data_only = null) {
  $empty_key = array();

  // Loop to find empty elements 
  foreach($array as $key => $value) {
		if ($normal) {
			$val = $key;
			$val = str_replace('_', ' ', $val);
		} else {
			if ($key == 'user_login') {
				$val = 'User Name';
			}
			elseif ($key == 'user_email') {
				$val = 'User Email';
			}
			elseif ($key == 'user_pass') {
				$val = 'User Password';
			} else {
				$val = educare_check_status($key, true);

				if (!$val) {
					$val = str_replace('_', ' ', $key);
				}
			}
			
		}

    if(empty($value)) {
      // return empty elements key
      array_push($empty_key, $val);
    }
  }

  // return $empty_key;
  if ($empty_key) {
		if ($data_only) {
			return $empty_key;
		}

		$msg = sprintf(__('You mast fill <b>%s</b>', 'educare'), implode(', ', $empty_key));

		if ($text_only) {
			return $msg;
		} else {
			return educare_show_msg($msg, false);
		}

  } else {
    return false;
  }
}



/** 
 * Auto create sql command
 * 
 * Usage example: educare_get_sql($requred);
 * array to sql command
 * here array $key = database structure
 * and $value = data
 * 
 * @since 1.3.0
 * @last-update 1.3.0
 * 
 * @param array $requred		for create sql
 * @return string
 */
function educare_get_sql($requred, $cond = 'AND') {
  ob_start();

		$end_sql = end($requred);
		$end_sql = key($requred);

    foreach ($requred as $key => $value) {
      $key = sanitize_text_field( $key );
      $value = sanitize_text_field( $value );
			$cond = esc_sql( $cond );

      $and = "$cond ";

      if ($key == $end_sql) {
        $and = '';
      }

      echo '`' . esc_sql($key) . "`='" . esc_sql($value) . "'" . " " . esc_sql($and);
    }

  $sql = ob_get_clean();
  return $sql;
}



/**
 * Process form when click auto fill button
 * 
 * @since 1.4.0
 * @last-update 1.4.0
 * 
 * @return mixed
 */
function educare_get_data_from_students() {
	// Check if the current user has the access this request as 'manage_options' capability (typically administrators).
	educare_check_access();

	// Remove the backslash
	$_POST['form_data'] = stripslashes($_POST['form_data']);
	// parses query strings and sets the parsed values into the $_POST array.
	wp_parse_str($_POST['form_data'], $_POST);

	// Verify the nonce to ensure the request originated from the expected source
	educare_verify_nonce('educare_crud_data', 'crud_data_nonce');

	if (isset($_POST['auto_fill'])) {
		$_POST['roles'] = 'students';
		echo educare_crud_data_new();

		$_POST['roles'] = 'results';
		unset($_POST['default']['id']);
		educare_get_forms();
	} else {
		echo educare_crud_data_new();

		if(!isset($_POST['search'])) {
			educare_get_forms();
		}
	}
	
	die;
}

add_action('wp_ajax_educare_get_data_from_students', 'educare_get_data_from_students');


/**
 * Process single student results data
 * 
 * @since 1.6.0
 * @last-update 1.6.0
 * 
 * @return mixed
 */
function educare_crud_results() {
	echo educare_get_advance_banner('The features you are requesting are supported in the Educare Premium version. It includes advanced functionalities like add specific student resutls via all students list.', false);
	
	die;
}

add_action('wp_ajax_educare_crud_results', 'educare_crud_results');



/** 
 * educare_get_data_management('results')
 * 
 * @since 1.4.0
 * @last-update 1.4.0
 * 
 * @param bool $add_students		if data for students
 * 
 * @return null|HTML
 */
function educare_get_data_management($students, $front = false) {
	if ($students == 'students') {
		$icon = 'businessman';
	} elseif ($students == 'results') {
		$icon = 'id-alt';
	} else {
		$icon = 'businessperson';
	}

	?>
	<div class="educare-container">
		<div class="tab students">
			<button class="tablinks active" id="default" title="<?php echo sprintf(__('All %s', 'educare'), esc_html__($students, 'educare'))?>" data="all-data"><i class="dashicons dashicons-<?php echo esc_attr($icon)?>"></i><span><?php _e('All', 'educare')?></span></button>
			<button class="tablinks" title="<?php echo sprintf(__('Add %s', 'educare'), esc_html__($students, 'educare'))?>" data="add-data"><i class="dashicons dashicons-plus-alt"></i><span><?php _e('Add', 'educare')?></span></button>
			<button class="tablinks" title="<?php echo sprintf(__('Edit %s', 'educare'), esc_html__($students, 'educare'))?>" data="update-data"><i class="dashicons dashicons-update"></i><span><?php _e('Edit', 'educare')?></span></button>
			<button class="tablinks position-relative" title="<?php echo sprintf(__('Import %s', 'educare'), esc_html__($students, 'educare'))?>" data="import-data"><i class="dashicons dashicons-database-import"></i><span><?php _e('Import', 'educare')?></span></button>
		</div>
		
		<div class="educare_post">
			<div id="educare-data">
				<?php educare_data_management($students, $front);?>
			</div>
		</div>
	</div>

	<?php
	$students_data = '';
	if ($students == 'students') {
		$students_data = true;
	}
	
	$url = admin_url();
	$url .= 'admin.php?page=educare-all-'.$students.'';

	// Keep active tab
	if ( isset($_GET['add-data'])) {
		$tab = 'add-data';
	}
	elseif ( isset($_GET['update-data'])) {
		$tab = 'update-data';
	}
	elseif ( isset($_GET['import-data'])) {
		$tab = 'import-data';
	} else {
		$tab = 'all-data';
	}
	?>

	<!-- Default value -->
	<div class="educare_data_field">
		<div class="educareDataManagement_url" data-value="<?php echo esc_url($url);?>"></div>
		<div class="educareDataManagement_students" data-value="<?php echo esc_js($students);?>"></div>
		<div class="educareDataManagement_tab" data-value="<?php echo esc_attr($tab);?>"></div>
		<div class="educareDataManagement_students_data" data-value="<?php echo esc_attr($students_data);?>"></div>
	</div>

	<?php
}



/**
 * Creat tab in admin page
 * 
 * @since 1.4.0
 * @last-update 1.4.0
 * 
 * @param string $action_for		$_GET request for ajax call
 * @param array $tab						All tab
 * @return mixed
 */
 function educare_tab_management($action_for = 'management', $tab = null, $front = false) {
	if (!$tab) {
		$tab = array (
			// tab name => icon
			'Class' => 'awards',
			'Group' => 'groups',
			'Rattings' => 'star-half',
			'School' => 'admin-multisite',
			'Exam' => 'welcome-write-blog',
			'Year' => 'calendar',
			'Term' => 'image-filter',
			'Staff_position' => 'businessman',
			'Extra_field' => 'welcome-add-page',
		);
	}

	if (has_filter('educare_tab_management')) {
		$tab = apply_filters( 'educare_tab_management', $action_for, $tab, $front );
	}

	echo '<div class="educare-container">';
		echo '<div class="tab tab_management">';
			$activate = array_key_first($tab);
			$active_tab = '';

			$advance = array(
				'Rattings',
				'School',
				'Term',
				'Staff_position'
			);

			foreach ($tab as $name => $icon) {
				$title = ucwords(str_replace('-', ' ', $name));

				if ($name == $activate) {
					$activate = 'active';
				} else {
					$activate = '';
				}

				$badge = $advance_fields = '';

				if (in_array($name, $advance)) {
					$advance_fields = educare_advance_fields();
					$badge = 'lock';
				}

				echo '<button class="tablinks position-relative '.esc_attr($activate).' '.esc_attr($advance_fields).'" id="'.esc_attr($name).'" title="Manage '.esc_html(str_replace('_', ' ', $title)).'">
					<i class="dashicons dashicons-'.esc_attr($icon).'"></i>
					<span>'.esc_html(str_replace('_', ' ', $title)).'</span>
					<div class="position-absolute top-50 start-50 translate-middle educare-badge '.esc_attr($badge).' p-1">
						'.educare_advance_fields_badge().'
					</div>
				</button>';

				if ( isset($_GET[$name])) {
					$active_tab = sanitize_text_field( $name );
				}

			}

		echo '</div>';
		?>
		
		<div class="educare_post educare_settingss <?php echo esc_attr($action_for) ?>">
			<div id="educare-data">
				<?php educare_get_tab_management($action_for, $front);?>
			</div>
		</div>

	</div>

	<?php
	if ($front) {
		$url = '/'.educare_check_status("profiles_page").'/?'.educare_check_status("front_dashboard").'=';
	} else {
		$url = admin_url().'admin.php?page=';
	}
	
	$url .= 'educare-' . esc_attr($action_for);

	// Keep slected specific menu
	if (isset($_GET['menu'])) {
		$menu = sanitize_text_field( $_GET['menu'] );
	} else {
		$menu = '';
	}
	?>

	<!-- Default value -->
	<div class="educare_data_field">
		<div class="educareTabManagement_url" data-value="<?php echo esc_url($url);?>"></div>
		<div class="educareTabManagement_action_for" data-value="<?php echo esc_attr($action_for);?>"></div>
		<div class="educareTabManagement_front" data-value="<?php echo esc_attr($front);?>"></div>
		<div class="educareTabManagement_menu" data-value="<?php echo esc_attr($menu);?>"></div>
		<div class="educareTabManagement_active_tab" data-value="<?php echo esc_attr($active_tab);?>"></div>
	</div>
	
	<?php
}



/** 
 * Response ajax request from tab button
 * 
 * @since 1.4.0
 * @last-update 1.4.0
 * 
 * @return null|HTML
 */
function educare_process_tab() {
	// Check if the current user has the access this request as 'manage_options' capability (typically administrators).
	educare_check_access();

	$action_for = $_POST['action_for'];
	$front = $_POST['front'];
	unset($_POST['front']);

	if (isset($_POST['tab'])) {
		$_GET[$_POST['tab']] = '';
	}

	educare_get_tab_management($action_for, $front);
	die;
}

add_action('wp_ajax_educare_process_tab', 'educare_process_tab');



/** 
 * Proccess ajax request from tab button and display data
 * 
 * @since 1.4.0
 * @last-update 1.4.0
 * 
 * @param string $action_for		$_GET request for ajax response
 * @param bool $front  if display tab in front end dashboard
 * 
 * @return mixed
 */
 function educare_get_tab_management($action_for, $front = false) {
	if ($action_for == 'management') {
		if (isset($_GET['Group'])) {
			echo "<h1>Group List</h1>";
	
			// Group list
			echo '<div id="msg_for_Group">';
				educare_setting_subject("Group");
			echo '</div>';
			
			// Group forms
			educare_setting_subject("Group", true);
		} elseif (isset($_GET['Rattings'])) {
			echo "<h1>Rattings List</h1>";
			echo wp_kses_post(educare_get_unlock_banner());

			// Group list
			echo '<div id="msg_for_Rattings">';
				educare_setting_subject("Rattings");
			echo '</div>';
			
			// Rattings forms
			educare_setting_subject("Rattings", true);
		} elseif (isset($_GET['Exam'])) {
			echo "<h1>Exam List</h1>";
			educare_get_all_content('Exam');
		} elseif (isset($_GET['Year'])) {
			echo "<h1>Year List</h1>";
			educare_get_all_content('Year');
		} elseif (isset($_GET['School'])) {
			echo "<h1>School List</h1>";
			echo wp_kses_post(educare_get_unlock_banner());
			educare_get_all_content('School');
		} elseif (isset($_GET['Term'])) {
			echo "<h1>Term</h1>";
			echo wp_kses_post(educare_get_unlock_banner());
			educare_get_all_content('Term');
		} elseif (isset($_GET['Staff_position'])) {
			echo "<h1>Staff Position</h1>";
			echo wp_kses_post(educare_get_unlock_banner());
			educare_get_all_content('Staff_position');
		} elseif (isset($_GET['Extra_field'])) {
			echo "<h1>Extra Field</h1>";
			educare_get_all_content('Extra_field');
		} else {
			if (educare_check_status('show_cover_photos') === 'checked') {
				echo '<div class="cover"><img src="'.esc_url(EDUCARE_URL.'assets/img/cover.svg').'" alt="educare cover"/></div>';
			}
			
			// Class list
			echo '<div id="msg_for_Class">';
				educare_setting_subject("Class");
			echo '</div>';
	
			// Class forms
			educare_setting_subject("Class", true);
		}
		
		return;
	} elseif ($action_for == 'mark-sheet') {
		$_GET['page'] = 'educare-mark-sheet';
		
		if (isset($_GET['import-data'])) {
			echo '<h1>'.sprintf(__('Import %s', 'educare'), __('Marks', 'educare')).'</h1>';

			echo '<div id="msgs" style="text-align:center;">';
			echo '<span style="font-size:100px">&#9785;</span><br><b>'.__('We are working on it!', 'educare').'</b>';
			echo '</div>';

		} elseif  (isset($_GET['update-data'])) {
			educare_add_marks('marks', null, $front);
		} elseif  (isset($_GET['add-data'])) {
			educare_add_marks('marks', null, $front);
		} else {
			educare_display_data('marks', null, $front);
		}

		return;
	} elseif ($action_for == 'attendance') {
		$_GET['page'] = 'educare-attendance';

		if ($front) {
			$url = '/'.educare_check_status("profiles_page").'/?'.educare_check_status("front_dashboard").'=';
		} else {
			$url = admin_url().'admin.php?page=';
		}
		
		if (isset($_GET['import-data'])) {
			echo '<h1>'.sprintf(__('Import %s', 'educare'), __('Attendance', 'educare')).'</h1>';

			echo '<div id="msgs" style="text-align:center;">';
			echo '<span style="font-size:100px">&#9785;</span><br><b>'.__('Attendance import is temporarily unavailable.', 'educare').'</b>';
			echo '</div>';

		} elseif  (isset($_GET['update-data'])) {
			// form to find or show students
			if (educare_get_unlock_banner()) {
				// Show banner
				educare_show_school_banner();
				echo wp_kses_post(educare_get_unlock_banner());
				return;
			}

			educare_add_marks('attendance', null, $front);
		} elseif  (isset($_GET['add-data'])) {
			if (educare_get_unlock_banner()) {
				// Show banner
				educare_show_school_banner();
				echo wp_kses_post(educare_get_unlock_banner());
				return;
			}

			// form to find or show students
			educare_add_marks('attendance', null, $front);
		} else {
			// Show banner
			educare_show_school_banner();
			
			echo '<div class="add_results">';
				echo '<div class="content">';
					echo '<h4 class="mb-4 p-2 text-center">'.__('Attendance', 'educare').'</h4>';

					if (educare_check_status('show_cover_photos') === 'checked') {
						echo '<div class="cover mb-4"><img src="'.esc_url(EDUCARE_URL.'assets/img/marks.svg').'" alt="Attendance List" title="Attendance"/></div>';
					}
					
					?>
					<div class="flex">
						<a href="<?php echo $url;?>educare-attendance&add-data" class="flex-content bg-light py-5 px-3 rounded-4 text-decoration-none">
							<div class="box-content">
								<h3 class="text-center"><?php _e('Add New', 'educare');?></h3>
							</div>
						</a>
						<a href="<?php echo $url;?>educare-attendance&add-data" class="flex-content bg-light py-5 px-3 rounded-4 text-decoration-none">
							<div class="box-content">
								<h3 class="text-center"><?php _e('View Attendance', 'educare');?></h3>
							</div>
						</a>
					</div>
				</div>
			</div>
			<?php
		}

		return;
	} elseif ($action_for == 'performance') {
		// check if user access this request
		// educare_check_access('performance');

		echo '<div id="promote_msgs">';
		educare_promote_students();
		echo '</div>';
	} elseif ($action_for == 'settings') {
		// check if user access this request
		educare_check_access('settings');

		if (isset($_GET['default_photos'])) {
			if ( isset( $_POST['educare_default_photos'] ) && isset( $_POST['Others']['Photos'] ) ) {
				// Verify the nonce to ensure the request originated from the expected source
				educare_verify_nonce('educare_default_photos');

				// check if user access this request
				educare_check_access('settings');
				
				$attachment_id = sanitize_text_field($_POST['Others']['Photos']);
				update_option( 'educare_files_selector', absint($attachment_id) );
			}

			?>
			<form method='post'>
				<?php 
				// Security nonce for this form.
				$nonce = wp_create_nonce( 'educare_default_photos' );
				echo '<input type="hidden" name="nonce" value="'.esc_attr($nonce).'">';
				
				educare_files_selector('');
				?>
				
				<?php 
				echo '<div class="educare_upload">';

					if ( isset( $_POST['educare_default_photos'] ) && isset( $_POST['Others']['Photos'] ) ) {
						echo "<div class='notice notice-success is-dismissible'><p>".__('Successfully update default students photos', 'educare')."</p></div>";
					}
					?>

					<button id='educare_default_photos' type="submit" name="educare_default_photos" class="educare_button full"><i class="dashicons dashicons-yes-alt"></i> <?php _e('Save', 'educare');?></button>
				</div>
				
			</form>
			
			<?php
		} elseif (isset($_GET['grading_system'])) {
			echo '<h1>'.__('Grading System', 'educare').'</h1>';
			?>
			<?php echo educare_guide_for(__('If you need to change default grading value, simply click edit button and enter your custom (Country) starndard rules. Allso, you can add your custom rules using code. For this please visit Educare support forum or carfully read plugin readme files', 'educare'));?>
			
			<p><?php _e('Grading Systems', 'educare');?>: <i id="help" title="<?php _e('How does it work? Click to view', 'educare');?>" class="dashicons dashicons-editor-help"></i></p>
			<div class="select">
				<select id="grading" name="grading" class="form-control">
					<option value="Default"><?php _e('Default', 'educare');?></option>
				</select>
			</div>

			<div id="show_help" style="display: none;">
				<div class="notice notice-info educare-notice"><p>
					<h5><?php _e("How it's work?", 'educare');?></h5>
					<p>
					<?php _e('We are mentioning the process how to calculate CGPA (GPA) from Marks in HSC. To do this, add up the grade points for the six major subjects and divide with 6 (total subject). For example, your grade points for <b>six</b> main subjects are listed below:', 'educare');?></p><br>
					<div class="table_container">
						<table class="grade_sheet bg-light">
							<thead>
								<tr>
								<th><?php _e('Subject', 'educare');?></th>
								<th><?php _e('Mark', 'educare');?></th>
								<th><?php _e('Grade Points', 'educare');?></th>
								<th><?php _e('Letter grade', 'educare');?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>Subject 1</td>
									<td>85</td>
									<td>5</td>
									<td>A+</td>
								</tr>
								<tr>
									<td>Subject 2</td>
									<td>70</td>
									<td>4</td>
									<td>A</td>
								</tr>
								<tr>
									<td>Subject 3</td>
									<td>68</td>
									<td>3.5</td>
									<td>A-</td>
								</tr>
								<tr>
									<td>Subject 4</td>
									<td>55</td>
									<td>3</td>
									<td>B</td>
								</tr>
								<tr>
									<td>Subject 5</td>
									<td>95</td>
									<td>5</td>
									<td>A+</td>
								</tr>
								<tr>
									<td>Subject 6</td>
									<td>80</td>
									<td>5</td>
									<td>A+</td>
								</tr>
								<tr>
									<td><?php _e('Total', 'educare');?></td>
									<td></td>
									<td>21</td>
									<td></td>
								</tr>
								<tr>
									<td><strong>GPA</strong></td>
									<td></td>
									<td><strong>25.5/6 = 4.25</strong></td>
									<td>A</td>
								</tr>
							</tbody>
						</table>
					</div>

					<br>
					
					<ul style="list-style-type:circle;">
						<li><b>Step 1:</b> Add the grade points i.e <code>5+4+3.5+3+5+5 = 25.5</code></li>
						<li><b>Step 2:</b> Divide the sum by (total subject) 6 i.e <code>25.5/6 = 4.25</code></li>
						<li>Thus, your GPA is <code>4.25</code></li>
						<li>And, Letter grade is <code>A</code></li>
					</ul>

					<p>Basically, GPA = Total grade points/Total subject</p>
					<p><b>How to define grade point and letter grade (Programmally)?</b></p>
					<p><b>Grade point:</b></p>
					</p><pre><code>if ($marks >= 80 and $marks <= 100) { $point = 5; }</code></pre>
					</p>

					<p><b><?php _e('Letter grade', 'educare');?>:</b><br>
					<pre><code>if ($marks >= 80 and $marks <= 100) { $grade = 'A+'; }</code></pre>
					</p>

				</div>
			</div>

			<div id="result_msg" class="grading_system">
				<div class="my-3 text-center"><?php _e('Default Rules', 'educare');?></div>
				<?php educare_show_grade_rule();?>
			</div>
			
			<div id="update_button" class="button-container">
				<button type="submit" name="save_grade_system" class="educare_button disabled"><i class="dashicons dashicons-update" disabled></i></button>
				<button id="edit_grade" type="submit" name="edit_grade_system" class="educare_button"><i class="dashicons dashicons-edit"></i></button>
			</div>

			<?php
		} else {
			echo '<h1>'.__('Settings', 'educare').'</h1>';

			echo wp_kses_post(educare_get_unlock_banner(true));
			
			echo '<div id="msg_for_settings">'.educare_settings_form().'</div>';
		}
	} else {
		echo '<div id="msgs" class="text-center">';
		echo '<span style="fs-1">&#9785;</span><br>
		<b>'.__('Sorry your requested data is missing!', 'educare').'</b>';
		echo '</div>';
	}
}



/**
 * Educare add marks
 * 
 * usage: educare_add_marks('marks')
 * 
 * @since 1.4.2
 * @last-update 1.6.0
 * 
 * @param string $db			for specific users or data (Students, Teachers, Results, Marks...)
 * @param int $per_page 		Data per (one) page. Default is 15 (baseed on educare settings)
 * 
 * @return void|HTML
 */
function educare_add_marks($db = 'marks', $per_page = null, $front = false) {
	global $wpdb;
	// Define table name to access data
	$roles = 'students';
	// $table = $wpdb->prefix.EDUCARE_PREFIX.$roles;
	
	// Define all empty fields to ignore php error
  $Year = $Class = $Group = $Exam = $Subject = $search = $order_by = $order = '';

	// default data per page when page load
	if (!$per_page) {
		$per_page = educare_check_status('data_per_page');
	}
	// default page when page load
	$page_no = 1;
	// Center pagination
	$center = 2;

	$requred = educare_check_status('display');
	// Getting all requered field key and title
	$requred_title = educare_requred_data($requred, true);

	$get_data = array();

	// allow edit when click edit button
	if (isset($_POST['students_list'])) {
		$get_data = array(
			'Class' => '',
			'Exam' => '',
			'Year' => '',
			'Group' => '',
			'Subject' => '',
			'order_by' => 'id',
			'order' => 'DESC',
			'students_list_nonce' => '',
			'students_list' => true,
			'add-data' => true
		);

		foreach ($get_data as $get_data_key => $get_data_value) {
			if (isset($_POST[$get_data_key])) {
				$get_data[$get_data_key] = sanitize_text_field($_POST[$get_data_key]);
			}
		}

		foreach ($get_data as $datkey => $datavalue) {
			$_GET[$datkey] = $datavalue;
		}
	}
	
	if (isset($_GET['page-no']) && $_GET['page-no']!='' && $_GET['page-no']!='0') {
		$page_no = $_GET['page-no'];
	}
	if (isset($_GET['per-page']) && $_GET['per-page']!=''&& $_GET['per-page']!='0') {
		$per_page = $_GET['per-page'];
	}

	$url = admin_url().'admin.php?';

	if ($_GET) {
		$pageURL = $url;
		foreach ($_GET as $key => $value) {
			if (!$value) {
				if ($key !== 'add-data' && $key !== 'students_list') {
					continue;
				}
			}

			$value = sanitize_text_field( $value );
			// Define dynamic variable
			$$key = $value;
			// Define page url
			$pageURL .= $key.'='.$value.'&';
		}
	} else {
		// Default page url
		$pageURL = admin_url().'admin.php?page=educare-all-students&';
	}

	// ignore index page -1, and increase $per_page (by default 5) in each page
  $offset = ($page_no-1) * $per_page;
	// current page - 1
	$previous = $page_no - 1;
	// current page + 1
	$next = $page_no + 1;
	// Requred fields to add marks
	$requred_fields = array (
		'Class',
		'Exam',
		'Year'
	);

	// Others fields to related with marks, for sort, search, group or subject wise student.
	$default_fields = array (
		'Group',
		'Subject',
		'order_by',
		'order'
	);

	// 1. for process marks fuctionality
	$default_fields = array_merge($requred_fields, $default_fields);
	$default_fields = educare_combine_fields($default_fields, '', $_GET, true);

	// 2. for save marks as specific Class, Exam and Year
	$requred_fields = educare_combine_fields($requred_fields, '', $default_fields, true);

	// 3. for get students list
	$search_students = $default_fields;
	if ($search) {
		$search_students['search'] = $search;
	}
	if (!$Group) {
		unset($search_students['Group']);
	}

	unset($search_students['Subject'], $search_students['Exam']);

	// 4. for pagination
	$offset_students = $search_students;
	$offset_students['offset'] = $offset;
	$offset_students['per-page'] = $per_page;

	// 5. for get marks data
	$search_marks = $requred_fields;

	// finaly creat dynamic sql
	$search_students = educare_dynamic_sql($search_students, $roles);
	$offset_students = educare_dynamic_sql($offset_students, $roles);
	$search_marks = educare_dynamic_sql($search_marks, $db);

	// Forms
	// Check requred fields data
	$requred = educare_check_status('display');
	// Getting all requered field key and title
	$requred_title = educare_requred_data($requred, true, false);

	$group_title = isset($requred_title['Group']) ? $requred_title['Group'] : '';
	$class_title = isset($requred_title['Class']) ? $requred_title['Class'] : '';
	$exam_title = isset($requred_title['Exam']) ? $requred_title['Exam'] : '';

	// Show banner
	educare_show_school_banner();

	?>
	<form method="get" id="filter_data" class="add_results add_marks">
		<div class="content">
			<?php
			// Show page title
			if (isset($_GET['page']) && $_GET['page'] === 'educare-mark-sheet') {
				$form_title = 'marks';
			} else {
				$form_title = 'attendance';
			}
			if (isset($_GET['add-data'])) {
				$form_type = 'add';
			} else	{
				$form_type = 'update';
			}

			echo educare_get_page_title(esc_html($form_title), esc_html($form_type));

			if ($form_title == 'marks') {
				if (educare_guide_for(__("<p>This feature allows teachers to enter subject-wise results for multiple students at once, making it especially useful for individual teachers. Since different teachers handle different subjects, each teacher can add marks for their assigned subject and generate a mark sheet for all students.</p><p>Once marks for all subjects are entered, students can view and print their results after the admin publishes them. Additionally, teachers can publish results for individual subjects when needed.</p><p>(We call this the <b>GOLDEN FEATURE</b> for teachers!)</p>", 'educare'))) {
					echo '<div class="mb-4">';
					echo educare_guide_for(__("<p>This feature allows teachers to enter subject-wise results for multiple students at once, making it especially useful for individual teachers. Since different teachers handle different subjects, each teacher can add marks for their assigned subject and generate a mark sheet for all students.</p><p>Once marks for all subjects are entered, students can view and print their results after the admin publishes them. Additionally, teachers can publish results for individual subjects when needed.</p><p>(We call this the <b>GOLDEN FEATURE</b> for teachers!)</p>", 'educare'));
					echo '</div>';
				}
			}
			
			foreach ($_GET as $key => $value) {
				if ($key == 'page-no') {
					continue;
				}
				
				echo '<input type="hidden" name="'.esc_attr( $key ).'" value="'.esc_attr( $value ).'">';
			}

			if ($front) {
				$db_for = $db;

				if ($db_for == 'marks') {
					$db_for = 'mark-sheet';
				}

				echo '<input type="hidden" name="'.esc_attr( educare_check_status("front_dashboard") ).'" value="educare-'.esc_attr( $db_for ).'">';
			}
			?>

			<div class="row gy-3">
				<?php
				if ($class_title) {
					?>
					<div class="col-md-6">
						<label for="Class" class="form-label"><?php _e(sprintf(__('Select %s', 'educare'), __(esc_html($requred_title['Class']), 'educare'))); ?></label>
						<select id="Class" name="Class" class="form-control">
							<option value=""><?php _e(sprintf(__('Select %s', 'educare'), __(esc_html($requred_title['Class']), 'educare'))); ?></option>
							<?php educare_get_options('Class', $Class);?>
						</select>
					</div>
					<?php
				} else {
					echo '<input type="hidden" name="Class" value="">';
				}
				?>
				
				<?php
				if ($group_title) {
					?>
					<div class="col-md-6">
						<label for="Group" class="form-label"><?php _e(sprintf(__('Select %s', 'educare'), __(esc_html($requred_title['Group']), 'educare'))); ?></label>
						<select id="Group" name="Group" class="form-control">
							<option value=""><?php _e(sprintf(__('Select %s', 'educare'), __(esc_html($requred_title['Group']), 'educare'))); ?></option>
							<?php educare_get_options('Group', $Group);?>
						</select>
					</div>
					<?php
				} else {
					echo '<input type="hidden" name="Group" value="">';
				}
				?>

				<div class="col-md-6">
					<label for="Year" class="form-label"><?php _e(sprintf(__('Select %s', 'educare'), __(esc_html($requred_title['Year']), 'educare'))); ?></label>
					<select id="Year" name="Year" class="form-control">
						<option value=""><?php _e(sprintf(__('Select %s', 'educare'), __(esc_html($requred_title['Year']), 'educare'))); ?></option>
						<?php educare_get_options('Year', $Year);?>
					</select>
				</div>

				<div class="col-md-6">
					<label for="Subject" class="form-label"><?php _e(sprintf(__('Select %s', 'educare'), __('Subject', 'educare'))); ?></label>
					<select id="Subject" name="Subject" class="form-control">
						<option value=""><?php _e(__('All', 'educare'));?></option>
						<?php 
						if ($Subject) {
							if ($Group) {
								educare_get_options_for_subject('Class', $Class, $Subject);
								educare_get_options_for_subject('Group', $Group, $Subject);
							} else {
								educare_get_options_for_subject('Class', $Class, $Subject);
							}
						} else {
							educare_get_options_for_subject('Class', $Class, $Subject);
						}
						?>
					</select>
				</div>

				<?php
				if ($db == 'marks') {
					?>
					<div class="col-md-6">
						<label for="Exam" class="form-label"><?php _e(sprintf(__('Select %s', 'educare'), __(esc_html($exam_title), 'educare'))); ?></label>
						
						<select id="Exam" name="Exam" class="form-control">
							<?php educare_get_options('Exam', $Exam);?>
						</select>
					</div>

					<?php
				}
				?>

				<div class="col-md-6">
					<label for="order_by" class="form-label"><?php _e(__('Order By', 'educare')); ?></label>
					
					<select id='order_by' name="order_by">
						<option value='id' <?php esc_attr(selected( 'id', $order_by )) ;?>><?php _e(__('Time', 'educare')); ?></option>
						
						<?php
						if (isset($requred_title['Name'])) {
							echo '<option value="Name" '.esc_attr(selected( "Name", $order_by )).'>'.__(__(esc_html($requred_title["Name"]), 'educare')).'</option>';
						}

						if (isset($requred_title['Roll_No'])) {
							echo '<option value="Roll_No" '.esc_attr(selected( "Roll_No", $order_by )).'>'.__(__(esc_html($requred_title["Roll_No"]), 'educare')).'</option>';
						}

						if (isset($requred_title['Regi_No'])) {
							echo '<option value="Regi_No" '.esc_attr(selected( "Regi_No", $order_by )).'>'.__(__(esc_html($requred_title["Regi_No"]), 'educare')).'</option>';
						}
						?>
					</select>
				</div>
				
				<div class="col-md-6">
					<label for="select_order" class="form-label"><?php _e(__('Asc', 'educare')); ?>/<?php _e(__('Desc', 'educare')); ?></label>
					
					<select id='select_order' name="order">
						<option value='DESC' <?php esc_attr(selected( 'DESC', $order )) ;?>><?php _e(__('Desc', 'educare')); ?></option>
						<option value='ASC' <?php esc_attr(selected( 'ASC', $order )) ;?>><?php _e(__('Asc', 'educare')); ?></option>
					</select>
				</div>
			</div>

			<input class="my-3" type="text" name="search" value="<?php echo $search?>" placeholder="<?php _e('Type here for search', 'educare');?>">

			<?php
			$students_list_nonce = wp_create_nonce( 'students_list' );
			$get_Group_nonce = wp_create_nonce( 'get_Group' );
			$get_Class_nonce = wp_create_nonce( 'get_Class' );
			
			echo '<input type="hidden" name="students_list_nonce" value="'.esc_attr($students_list_nonce).'">';
			echo '<input type="hidden" name="get_Group_nonce" value="'.esc_attr($get_Group_nonce).'">';
			echo '<input type="hidden" name="get_Class_nonce" value="'.esc_attr($get_Class_nonce).'">';
			?>

			<div class="button-container flex">
				<button id="students_list" type="submit" name="students_list" class="educare_button w-100" style="margin: 0;"><i class="dashicons dashicons-groups"></i> Show Students List</button>
			</div>

		</div>
	</form>

	<div id="msgs"></div>
	<?php
	if (isset($_GET['students_list'])) {
		educare_check_access('manage_options');
		
		// Verify the nonce to ensure the request originated from the expected source
		educare_verify_nonce('students_list', 'students_list_nonce', true);

		if (educare_is_empty($requred_fields)) {
			echo educare_is_empty($requred_fields);
		} else {
			// Display data
			// Proccess query to face data
			// Count total data
			// Define educare students table
			$educare_students = $wpdb->prefix.EDUCARE_PREFIX.esc_sql($roles);
			// Define educare marks table
			$educare_marks = $wpdb->prefix.EDUCARE_PREFIX.esc_sql($db);

			if ($Subject) {
				$over_all = 0;
				$opset_data = $wpdb->get_results("SELECT * FROM $educare_students $search_students");

				if ($opset_data) {
					$over_all = count($opset_data);

					foreach ($opset_data as $key => $student) {
						$sub = json_decode($student->Subject);

						if ($sub) {
							if (!property_exists($sub, $Subject)) {
								unset($opset_data[$key]);
							}
						} else {
							unset($opset_data[$key]);
						}
					}
				}
				
				// Count total data
				$total_data = count($opset_data);
				// Pagination data || Offset data
				$opset_data = array_slice($opset_data, $offset, $per_page);
			} else {
				// count total data
				$total_data = $wpdb->get_var("SELECT COUNT(*) FROM  $educare_students $search_students");
				// Face || get main data baset on pagination
				$opset_data = $wpdb->get_results("SELECT * FROM $educare_students $offset_students");
			}

			$total_pages = ceil($total_data / $per_page);
			$second_last = $total_pages - 1;

			if ($search) {
				if ($total_data) {
					$alert = 'success';
				} else {
					$alert = 'danger';
				}

				echo '<div class="text-center p-4 my-4 alert alert-'.esc_attr($alert).' rounded-5">'.sprintf(__('Search results for: %s', 'educare'), '<b>'. esc_html($search) . '</b>').'</div>';
			}

			if ($total_data) {
				// Show total data
				echo '<b class="text-center d-block p-4 my-4 bg-white rounded-5">'.sprintf(__('Total %1$s %2$s found', 'educare'), esc_html($total_data), esc_html__($roles, 'educare')).'</b>';

				if ($opset_data) {
					// getting previousely saved data
					$marks = new stdClass();
					$marks_data = array();
					
					if ($search_marks) {
						$marks = $wpdb->get_row("SELECT * FROM $educare_marks $search_marks");
					} else {
						$marks = 0;
					}

					// Store marks as $marks_data
					if ($marks) {
						$marks_data = $marks->Marks;
						$marks_data = json_decode($marks_data, true);
					} else {
						$marks = new stdClass();
						$marks_data = array();
						$marks->Class = sanitize_text_field( $Class );
						$marks->Group = sanitize_text_field( $Group );
						$marks->Exam = sanitize_text_field( $Exam );
						$marks->Year = sanitize_text_field( $Year );
					}

					$access_to_publish_marks = true;

					if (isset($_POST['crud_marks']) && isset($_POST[$db])) {
						$new = $updated = $success = $error = 0;
						$process = '';

						if (educare_table_exists($educare_marks)) {
							// We use the array_replace_recursive() function to combine the two arrays while preserving our nested structure.
							// Now resulting $marks_data will contain the combined values from database $marks_data and $_POST['marks'], with the values from $_POST['marks'] overwriting any matching keys in previeus $marks_data. Let's do it!
							
							$marks_data = array_replace_recursive($marks_data, $_POST[$db]);
							$marks->Marks = json_encode($marks_data);
							
							// Save marks
							// Check is data || marks already esxist or not
							if (isset($marks->id)) {
								$marks_id = sanitize_text_field( $marks->id );
								// Data alreay esxist, update data
								unset($marks->id);
								$process = $wpdb->update($educare_marks, (array) $marks, array('ID' => $marks_id));
								$status = 'update';
								// $updated++;
							} else {
								// Data not esxist, insert data
								$process = $wpdb->insert($educare_marks, (array) $marks);
								$status = 'insert';
							}

							if (isset($_POST['publish_marks'])) {
								if ($access_to_publish_marks) {
									// Check requred fields
									$results_sql = educare_requred_data($requred);
									$ignore = array ('Name', 'Group', 'Exam', 'user_pin');
									$results_sql = array_diff($results_sql, $ignore);

									if ($marks_data) {
										foreach ($marks_data as $student_id => $subject) {
											$student_data = educare_get_users_data($student_id);

											if ($student_data) {
												$educare_results = $wpdb->prefix.EDUCARE_PREFIX.'results';
												$student_results = educare_combineKeysAndProperties($results_sql, $student_data);
												$student_results['Exam'] = sanitize_text_field( $Exam );

												$student_results = educare_get_sql_new('results', $student_results);
												$student_results = $wpdb->get_row("$student_results");

												// Check if results already exist or not
												if ($student_results) {
													
													$old_subject = $student_results->Subject;
													$new_subject = json_encode($subject);
													
													if (strcmp($old_subject, $new_subject) !== 0) {
														// echo "The JSON strings are not identical or equal.";
														$updated++;
														// Add new marks data
														$student_results->Subject = $new_subject;

														// Get results id where to update
														$where = array (
															// replace with the ID of the row to update
															'id' => $student_results->id,
														);
														// Processing update
														$process = $wpdb->update($educare_results, (array) $student_results, $where);
														$status = 'update';
													}

												} else {
													$new++;
													// Results not exist, insert data
													$student_data->Subject = json_encode($subject);
													$student_data->Exam = sanitize_text_field( $Exam );

													$insert_results = (array) $student_data;
													$ignore_keys = array (
														'id',
														'user_id',
														'user_pass',
														'user_login',
														'user_email',
														'user_pin',
														'Student_ID'
													);

													foreach ($ignore_keys as $key) {
														if (isset($insert_results[$key])) {
															unset($insert_results[$key]);
														}
													}

													$process = $wpdb->insert($educare_results, $insert_results);
													$status = 'insert';
												}

												// Check if the update was successful
												if ( $process === false ) {
													// handle error
													// Error to porocess request
													$error++;
												} elseif ( $process == 0 ) {
													// no rows were updated, handle accordingly
													// echo educare_show_msg(__('No changes were found in this request.', 'educare'), 'info');
												} else {
													// update was successful, handle accordingly
													$success++;
												}
											}
										}
									}
								}
							}
						} else {
							$process = false;
						}

						// Check if the update was successful
						if ( $process === false ) {
							// handle error
							// Error to porocess request
							echo educare_show_msg('There was an issue processing your request', false);
						} elseif ( $process == 0 && $updated == 0 ) {
							// no rows were updated, handle accordingly
							echo educare_show_msg(__('No changes were found in this request.', 'educare'), 'info');
						} else {
							// update was successful, handle accordingly
							// Perform the update
							if (isset($_POST['delete'])) {
								// Execute the DELETE query
								// $_POST = array();
								if ($success) {
									echo educare_show_msg('Successfully deleted data');
									// clean data
									$_POST = array();
								} else {
									echo educare_show_msg('No data found for delete');
								}
							} else {
								if (isset($status) && $status === 'insert') {
									// The operation was a successful insert
									// Show insert message
									echo educare_show_msg('Data insert was successful');
								} else {
									// The operation was a successful update
									// Show update message
									echo educare_show_msg('Data update was successful');
								}
							}

							// Show status
							if ($access_to_publish_marks) {
								if (isset($_POST['publish_marks'])) {
									echo educare_show_msg(
										'Success: <span class="success">' . esc_html($success) . '</span><br>' .
										'Updated: ' . esc_html($updated) . '<br>' .
										'New: ' . esc_html($new) . '<br>' .
										'Error: <span class="error">' .esc_html($error) . '</span>', 
										true, false
									);
								}
							}
						}
					}

					$display_group = $Group ? $Group : 'All ' . $group_title;
					$display_sub = $Subject ? $Subject : 'All Subject';
					$display_class = $Group ? $group_title : $class_title;
					
					if ($Subject) {
						$over_all = 'Total '.$over_all.' students in this '.$display_class.'. '.$total_data.' students were found in this subject';
					} else {
						$over_all = 'Total '.$total_data.' students in this ' .$display_class;
					}

					// Build a form for save marks data
					echo'<form id="add-marks" method="post" action="">';
						// Define necessary field
						echo '<input type="hidden" name="crud_marks" value="true">';

						if ($get_data) {
							foreach ($get_data as $key => $value) {
								echo '<input type="hidden" name="'.esc_attr( $key ).'" value="'.esc_attr( $value ).'">';
							}
						}

						// for print marks data
						echo '<div class="educare_print" data-value="'.esc_attr($db).'">';
							echo '<div class="notice notice-success is-dismissible">
								<p>';
								
									if (isset($requred_title['Class'])) {
										echo '<b>'.esc_html__($class_title, 'educare').':</b> '.esc_html__($Class, 'educare').'<br>';
									}
									if (isset($requred_title['Group'])) {
										echo '<b>'.esc_html__($group_title, 'educare').':</b> '.esc_html__($display_group, 'educare').'<br>';
									}

									if (isset($requred_title['Exam'])) {
										if ($db == 'marks') {
											echo '<b>'.esc_html__($exam_title, 'educare').':</b> '.esc_html__($Exam, 'educare').'<br>';
										}
									}
									
									echo '<b>Subject:</b> '.esc_html__($display_sub, 'educare').'<br>
									<b>'.esc_html__($requred_title['Year'], 'educare').':</b> '.esc_html__($Year, 'educare').'<br>
									<b>Students:</b> '.esc_html__($over_all, 'educare').'
								</p>
								<button class="notice-dismiss"></button>
							</div>';

							// Getting all requered field key and title
							$requred_title = educare_requred_data($requred, true);
							$student_photos = educare_check_status('photos');

							// Serialize data
							$count = $offset + 1;
							// Show data || marks fields for single oe specific subject
							if ($Subject) {
								$terms = educare_get_template_data('fields', false);

								?>
								<div class="table_container">
									<table class="grade_sheet view_results bg-white my-3">
										<thead>
											<tr>
												<th><?php _e('No.', 'educare')?></th>
												<?php

												if ($student_photos == 'checked') {
													echo '<th>'.__('Photos', 'educare').'</th>';
												}

												foreach ($requred_title as $field_key => $field_value) {
													if ($field_key == 'Class' || $field_key == 'Exam' || $field_key == 'Year' || $field_key == 'user_pin' || $field_key == 'Staff_position') {
														continue;
													}

													echo '<th>'.esc_html($field_value).'</th>';
												}

												// thead for marks
												foreach ($terms as $term => $termInfo) {
													if (key_exists('hide', $termInfo)) {
														if ($termInfo['hide'] == 'on') {
															continue;
														}
													}
													
													$title = $subtitle = '';

													if (key_exists('title', $termInfo)) {
														$title = $termInfo['title'];
													}
													
													if (key_exists('subtitle', $termInfo)) {
														$subtitle = $termInfo['subtitle'];
														if ($subtitle) {
															$subtitle = '<br><small>'.esc_html($subtitle).'</small>';
														}
													}
													
													echo '<th>'. esc_html($title) . $subtitle.'</th>';
												}
												?>

												<th class="optional_sub">
													<div class="action_menu"><i class="dashicons action_button dashicons-info"></i> <menu class="action_link msg text-black"><b>Optional Subject</b><hr>If the student or your result system includes an optional subject, please indicate it. Otherwise, you may disregard this field.</menu></div>
												</th>
												<?php
												?>
											</tr>
										</thead>
										
										<tbody>
											<?php

											foreach($opset_data as $student) {
												$id = $student->id;
												if (!key_exists($id, $marks_data)) {
													// based students data
													$marks_data[$id] = json_decode($student->Subject, true);
												}
											}

											foreach($opset_data as $student) {
												$id = $student->id;
												$others = json_decode($student->Others);
												
												echo '<tr>';
													echo '<td>'.esc_html( $count ).'</td>';

													if ($student_photos == 'checked') {
														$photos = isset($others->Photos) ? $others->Photos : '';
														echo '<td><img src="'.esc_url(educare_get_attachment($photos)).'" class="user-img" alt="IMG"/></td>';
													}

													foreach ($requred_title as $field_key => $field_value) {
														if ($field_key == 'Class' || $field_key == 'Exam' || $field_key == 'Year' || $field_key == 'user_pin' || $field_key == 'Staff_position') {
															continue;
														}

														echo '<td>'.esc_html($student->$field_key).'</td>';
													}

													// tbody for marks
													$_POST['marks'] = $marks_data;
													educare_get_marks_input($terms, 'marks', $id, $Subject);

												echo '</tr>';

												$count++;
											}
											?>
										</tbody>
									</table>
								</div>
								<?php
							} else {
								echo educare_get_advance_banner('The features you are requesting are supported in the Educare Premium version. It includes advanced functionalities like batch entry of multiple students and multiple subject marks at once. Please select a specific subject instead.', false);
								die;
							}

						echo '</div>';

						echo educare_show_msg("Don't forget to save data before exiting, go to new page or reload the page.", 'info');
							
						echo '<div class="d-flex gap-3 my-4">';
							echo '<button type="submit" name="add_marks" class="btn btn-secondary py-2 w-100">'.sprintf(__('Save %s', 'educare'), esc_attr($db)).'</button>';

							if ($db == 'attendance') {
								echo "<button type='submit' name='delete' class='btn btn-danger text-center w-100' value='Delete'>".__('Delete', 'educare')."</button>";
							} else {
								if ($access_to_publish_marks) {
									echo '<input type="submit" name="publish_marks" class="btn btn-success d-flex align-items-center w-100" value="Publish">';
								}
							}
							
							?>
							<div class="btn-group d-block d-sm-flex align-items-center justify-content-end text-center">
								<button type="button" class="btn d-flex align-items-center justify-content-center mx-auto d-block text-center rounded-pill" data-bs-toggle="dropdown" aria-expanded="false">
									<i class="dashicons dashicons-menu" title="<?php echo __('Menu', 'educare');?>"></i>
								</button>

								<ul class="dropdown-menu p-0 overflow-hidden" style="width: 200px;">
									<li class="m-0 border-bottom"><button type="reset" class="dropdown-item d-flex align-items-center py-2 px-2"><i class="dashicons dashicons-undo me-2"></i><?php echo __('Undo', 'educare');?></button></li>

									<li class="m-0 border-bottom"><span class="dropdown-item d-flex align-items-center py-2 px-2" onclick="<?php echo esc_js('clearFormData("add-marks")');?>"><i class="dashicons dashicons-editor-removeformatting me-2"></i><?php echo __('Clear All', 'educare');?></span></li>

									<li class="m-0 border-bottom"><span class="dropdown-item d-flex align-items-center py-2 px-2" data-bs-toggle="modal" data-bs-target="#jumpPage"><i class="dashicons dashicons-admin-page me-2"></i><?php echo __('Jump page', 'educare');?></span></li>

									<li class="m-0 border-bottom "><span id="print" class="dropdown-item d-flex align-items-center py-2 px-2"><i class="dashicons dashicons-printer me-2"></i><?php echo __('Print Data', 'educare');?></span></li>
								</ul>
							</div>
							<?php

						echo '</div>';

					echo '</form>';
				} else {
					echo educare_show_msg('No more students found in this page <b>('.esc_html($offset).')</b>');
				}
			} else {
				echo educare_show_msg('No student found in this class <b>('.esc_html($Class).')</b>. <a href="/wp-admin/admin.php?page=educare-all-students&add-data" target="_blank">click add students</a>');
			}

			// Pagination
			if ($total_data) {
				?>
				<div class='page_status center'>
					<small>
						<?php _e(sprintf(__('Page %d Of %d', 'educare'), esc_html($page_no), esc_html($total_pages))) ?>
					</small>
				</div>

				<ul class="pagination">
					<li <?php if ($page_no <= 1) { echo "class='disabled'"; } ?>>
					<a <?php echo "data-id='".esc_attr($previous)."'";?> <?php if($page_no > 1) { echo "href='". esc_url($pageURL) ."page-no=".esc_attr($previous)."'"; } ?>>&laquo;</a>
					</li>
							
					<?php
					// Display first and second page
					$first_page = "<li><a data-id='1' href='". esc_url($pageURL) ."page-no=1'>1</a></li>
					<li><a data-id='2' href='". esc_url($pageURL) ."page-no=2'>2</a></li>
					<li><span>...</span></li>";
					// Display last and second-last page
					$last_page = "<li><span>...</span></li>
					<li><a data-id='".esc_attr($second_last)."' href='". esc_url($pageURL) ."page-no=".esc_attr($second_last)."'>".esc_html($second_last)."</a></li>
					<li><a data-id='".esc_attr($total_pages)."' href='". esc_url($pageURL) ."page-no=".esc_attr($total_pages)."'>". esc_html($total_pages) ."</a></li>";
					
					function fixbd_get_page($counter, $page_no, $url = '?') {
						if ($counter == $page_no) {
							echo "<li><a class='current'>".esc_html($counter)."</a></li>";
						} else {
							echo "<li><a data-id='".esc_attr($counter)."' href='". esc_url($url) ."page-no=".esc_attr($counter)."'>".esc_html($counter)."</a></li>";
						}
					}
					
					if ($total_pages <= 10) {
						/**
						* Display all (10) page if total page qual and less then 10
						* Exp structure: 
						
						1.2.3.4.5.6.7.8.9.10
						
						*
						*/
						for ($counter = 1; $counter <= $total_pages; $counter++) {
							fixbd_get_page($counter, $page_no, $pageURL);
						}
					}
					
					elseif ($total_pages > 10) {
						if ($page_no <= 4) {
							/**
							* Display last 2 page and first 8 page (with ...)
							* Exp structure: 
							
														($last_page)
							1.2.3.4.5.6.7 ... 11.12
							
							*
							*/
							for ($counter = 1; $counter < 8; $counter++) {		 
								fixbd_get_page($counter, $page_no, $pageURL);
							}
							
							// Display last 2 page
							echo wp_kses_post($last_page);
						}
					
						elseif ($page_no > 4 && $page_no < $total_pages - 4) {
							/**
							* let's do -
							* Begin display first 2 page
							* Then center pagination with 2 page before and after
							* Then display last 2 page
							* Exp structure:
							
																		($center)
							($first_page) 1.2 ... 3.4.5.6.7 ... 11.12 ($last_page)
							
							*
							*/
							// Display first 2 page
							echo wp_kses_post($first_page);
							
							/**
							* Display center with 2 page before and after
							* Exp structure: 
							
											center
							3 < 4 < =(5)= > 6 > 7
							
							*
							*/

							for ($counter = $page_no - $center; $counter <= $page_no + $center; $counter++) {			
								fixbd_get_page($counter, $page_no, $pageURL);
							}
							
							// Display last 2 page
							echo wp_kses_post($last_page);
						} else {
							/**
							* Display first 2 page and last 8 page (with ...)
							* Exp structure: 
							
							($first_page)
							1.2 ... 6.7.8.9.10.11.12
							
							*
							*/
								echo wp_kses_post($first_page);
							
							for ($counter = $total_pages - 6; $counter <= $total_pages; $counter++) {
								fixbd_get_page($counter, $page_no, $pageURL);      
							}
						}
					}
					?>
					
					<li <?php if($page_no >= $total_pages){ echo "class='disabled'"; } ?>>
					<a <?php echo "data-id='".esc_attr($next)."'";?> <?php if($page_no < $total_pages) { echo "href='". esc_url($pageURL) ."page-no=".esc_attr($next)."'"; } ?>>&raquo;</a>
					</li>

				</ul>

				<!-- Vertically centered scrollable modal -->
				<div class="modal fade" id="jumpPage" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="jumpPageLabel" aria-hidden="true">
					<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
						<div class="modal-content bg-white">
							<form>
								<div class="modal-header">
									<h5 class="modal-title d-flex align-items-center" id="jumpPageLabel"><i class="dashicons dashicons-admin-page me-2"></i><?php echo __('Jump Page', 'educare');?></h5>
									<button type="button" class="btn-close me-1" data-bs-dismiss="modal" aria-label="Close"></button>
								</div>
								<div class="modal-body">
									<div class="educare-form">
										<div class="content">
											<?php
											foreach ($_GET as $key => $value) {
												echo '<input type="hidden" name="'.esc_attr( $key ).'" value="'.esc_attr( $value ).'">';
											}
											?>

											<div class="row mb-3">
												<label class="col-md-2 col-form-label d-flex align-items-center" for="page-no"><?php _e('Page No', 'educare');?></label>
												<div class="col-md-10">
													<input class="form-control" type="number" id="page-no" name="page-no" value="<?php echo esc_attr($page_no); ?>" placeholder="<?php echo esc_attr($page_no); ?>">
												</div>
											</div>
											
											<div class="row">
												<label class="col-md-2 col-form-label d-flex align-items-center" for="per-page"><?php _e('Per Page', 'educare');?>:</label>
												<div class="col-md-10">
													<input class="form-control" type="number" id="per-page" name="per-page" value="<?php echo esc_attr($per_page); ?>" placeholder="<?php echo esc_attr($per_page); ?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="modal-footer">
									<button type="submit" name="filter" class="btn btn-success"><?php _e('Go', 'educare');?></button>
								</div>
							</form>
						</div>
					</div>
				</div>
				<?php
			} else {
				?>
				<div class="modal fade" id="jumpPage" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="jumpPageLabel" aria-hidden="true">
					<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
						<div class="modal-content bg-white">
							<div class="modal-header">
								<h5 class="modal-title d-flex align-items-center" id="jumpPageLabel"><i class="dashicons dashicons-admin-page me-2"></i><?php echo __('Jump Page', 'educare');?></h5>
								<button type="button" class="btn-close me-1" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body">
								<?php echo educare_show_msg( 'No page available to navigate.', false )?>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?php echo __('Ok', 'educare');?></button>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
		}
	}
}



/**
 * Get specific field data
 * 
 * For import demo or specific field data
 * Usage example: educare_demo_data('Extra_field');
 * 
 * @since 1.2.0
 * @last-update 1.2.0
 * 
 * @param string $list 	for specific data (class, exam, year, extra fields)
 * 
 * @return mixed
 */
function educare_demo_data($list) {
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
	// Prepare the query with placeholders
	$query = $wpdb->prepare( "SELECT * FROM $table WHERE list = %s", $list );
	// Execute the prepared query and get the results
	$search = $wpdb->get_results( $query );

	$data = '';

	foreach ( $search as $print ) {
		$data = $print->data;
	}

	$data = json_decode($data);
	return $data;
}



/**
 * Replace Specific Array Key
 * 
 * Usage example: $educare_replace_key = replace_key($array, 'b', 'e');
 * 
 * @since 1.0.0
 * @last-update 1.0.0
 * 
 * @param array $array						Where to replace key
 * @param string|int $old_key 		key to replace
 * @param string|int $new_key 		peplace old key to new key
 * 
 * @return array
 */
function educare_replace_key($array, $old_key, $new_key) {
	$keys = array_keys($array);

	if (false === $index = array_search($old_key, $keys, true)) {
			throw new Exception(sprintf('Key "%s" does not exist', $old_key));
	}

	$keys[$index] = $new_key;
	return array_combine($keys, array_values($array));
}



/** =====================( Functions Details )=======================
===================================================================
						HERE SOME OF AJAX FUNCTIONS THAT USE EDUCARE
===================================================================
===================( BEGIN AJAX FUNCTIONALITY )===================*/


/**
 * Get/show specific class subject wehen user select any subject
 * 
 * @since 1.2.0
 * @last-update 1.2.0
 * 
 * @return mised/HTML
 */
function educare_class() {
	// Remove the backslash
	$_POST['form_data'] = stripslashes($_POST['form_data']);
	// parses query strings and sets the parsed values into the $_POST array.
	wp_parse_str($_POST['form_data'], $_POST);

	// Verify the nonce to ensure the request originated from the expected source
	educare_verify_nonce('educare_crud_data', 'crud_data_nonce');
	
	unset($_POST['default']['id']);
	educare_get_subject_field();
	die;
}

add_action('wp_ajax_educare_class', 'educare_class');



/**
 * Check educare default settings
 * 
 * @since 1.2.4
 * @last-update 1.3.0
 * 
 * @return void
 */
function educare_ai_fix() {
	$current_settings = educare_check_status();
	$current_data = $current_settings->display;
	$current_data = json_decode(json_encode($current_data), TRUE);
	$settings_data = educare_add_default_settings('Settings', true);
	$default_data = $settings_data['display'];
	// @since 1.4.0
	$group = educare_check_settings('Group');
	// $group_list = educare_check_settings('Group_list');
	$msgs = $update_settings = $update_current_data = $update_group = false;

	foreach ($settings_data as $key => $data) {
		// keep user old settings
		if (!property_exists($current_settings, $key)) {
			$current_settings->$key = $data;
			$msgs = true;
			$update_settings = true;
		}
	}

	// $error_key = array_diff_key($current_data, $default_data);
	$error_key = array();
	if (is_array($current_data) && is_array($default_data)) {
    $error_key = array_diff_key($current_data, $default_data);
	}

	// remove unkhown key from data

	if ($error_key) {
		foreach ($error_key as $key => $value) {
			unset($current_data[$key]);
		}
	}

	// insert educare new data in database settings
	foreach ($default_data as $key => $data) {
		if (is_array($current_data)) {
			// keep user old settings
			if (!key_exists($key, $current_data)) {
				$current_data[$key] = $data;
				$msgs = true;
				$update_current_data = true;
			}
		} else {
			return educare_show_msg('There are some issues found and you will get an error while proccessing some options. Go to Educare <code>Settings > Reset Settings > <b>Restore Default Settings</b></code> and click on it to fix (remove) this messege.', false, false);
		}
	}

	if ($group === false) $msgs = true;
	
	if ($msgs) {
		if (educare_check_status('problem_detection') == 'checked') {

			if ($update_settings) {
				educare_add_default_settings('Settings', false, $current_settings);
			}

			if ($update_current_data) {
				foreach ($current_data as $key => $value) {
					$default_data[$key] = $value;
				}

				$current_settings->display = $default_data;
				educare_add_default_settings('Settings', false, $current_settings);
			}

			if ($group === false) {
				global $wpdb;
				$results_table = $wpdb->prefix.'educare_results';
				$students_table = $wpdb->prefix.'educare_students';
				// Add Group list
				educare_add_default_settings('Group');
				// Add group head/structure in table
				$wpdb->query("ALTER TABLE `$results_table` ADD `Group` VARCHAR(80) NOT NULL AFTER `Details`;");

				$wpdb->query("ALTER TABLE `$students_table` ADD `Group` VARCHAR(80) NOT NULL AFTER `Details`;");

				$wpdb->query("ALTER TABLE `$students_table` ADD `Student_ID` mediumint(11) NOT NULL AFTER `Others`;");
			}
			
			$msgs = '<div class="educare-container"><div class="educare_post">'.educare_show_msg("<strong>Educare (AI) Detection:</strong> Successfully complete update process.", 'info', false).'</div></div>';
		} else {
			$msgs = educare_show_msg('There are some issues found and you will get an error while proccessing some options. Because, Your current settings are disabled educare AI Problem Detection options. Please, Go to educare <code>Settings > Advance Settings > <b>(AI) Problem Detection</b></code> enable it to fix (remove) this messege. Note: To show advanced settings you must enable advanced settings in Settings > Other > Advanced settings.', false, false);
		}
	}
	
	return $msgs;

}


function educare_sanitize_array($data) {
	// Before using array_map on $_POST['for'], you should ensure that $_POST['for'] is set to avoid potential warnings or errors. 
	if (isset($data) && is_array($data)) {
		$sanitize_data = array_filter($data, 'sanitize_text_field');
	} else {
		$sanitize_data = sanitize_text_field($data);
	}

	return $sanitize_data;
}



/**
 * Add, Updata or Remove Data
 * 
 * Usage example: educare_settings('Settings');
 * 
 * Add / Update / Remove - Subject, Exam, Class, Year, Extra field... and settings status.
 * 
 * this is a main function for update all above (Settings) content. it's decide which content need to Add / Update / Remove and where to store Data into database.
 *
 * this function also provide - Error / Success notification when users Add / Update / Remove any Data.
 * 
 * it's make temporary history data for notify the users. 
 * 
 * for example, when users update a Subject like - Science to Biology. this function notify the users like this - Successfully update Subject (Science) to (Biology).
 * 
 * @since 1.2.4
 * @last-update 1.2.4
 * 
 * @param string $list	Select database
 * @return null|HTML 
 */
function educare_process_settings($list) {
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
	$list_column = $list;

	if ($list == 'Extra_field') {
		$list_column = 'Details';
	}
   
	// Prepare the query with placeholders
	$query = $wpdb->prepare( "SELECT * FROM $table WHERE list = %s", $list );
	// Execute the prepared query and get the results
	$search = $wpdb->get_results( $query );
	
	if ($search) {
		foreach ( $search as $print ) {
			$data = $print->data;
			$id = $print->id;
		}
		
		$data = json_decode($data, true);
		
		/* Convert index to associative array (number to name)
		and ignore Settings data, because our settings data is not array, it's an object. so, its not possible to combination [with: array_combine() func] between array and object. so, ignore Settings {object} to combine array. Otherwise, it's will give an error!
		*/
	
		// for add list items
		if (isset($_POST['educare_add_'.$list.''])) {
			// check if user access this request
			educare_check_access('management');
			
			$in_list = $list;
			// remove all _ characters from the list (normalize the $list)
			$list = str_replace('_', ' ', $in_list);
			
			if (isset($_POST["educare_add_Extra_field"])) {
				$target = sanitize_text_field($_POST['field']);
			} else {
				$target = sanitize_text_field($_POST[$in_list]);
			}
			
			if (empty($target)) {
				?>
				<div class="sticky_msg">
					<div class="notice notice-error is-dismissible"> 
						<p><?php
							echo sprintf(
								__(
									'You must fill the form for add the <b>%s</b>.',
									'educare'
								),
								esc_html__($list, 'educare')
							)
							?></p>
						<button class='notice-dismiss'></button>
					</div>
				</div>
				<?php
			
			} else {
				
				$unique_target = strtolower($target);
				$y = array();

				if (isset($_POST["educare_add_Extra_field"])) {
					$unique_target = strtolower(sanitize_text_field($_POST['field']));

					if ($data) {
						// log_it($data);
						foreach ($data as $value) {
							$y[] = strtolower($value['title']);
						}
					}

				} else {
					for ($i = 0; $i < count($data); $i++) {
						$x = strtolower($data[$i]);
						$y[] = $x;
					}
				}

				$unique_data = $y;
				
				if (in_array($unique_target, $unique_data)) {
					echo '<div class="sticky_msg"><div class="notice notice-error is-dismissible"><p>'.
					sprintf(
						__(
							'<b>%1$s</b> is allready exist!',
							'educare'
						),
						// esc_html__($new, 'educare'),
						esc_html__($target, 'educare')
					)
					.'</p><button class="notice-dismiss"></button></div></div>';
				} else {
					if (!$data) {
						$data = array();
					}
					
					if (isset($_POST['Extra_field'])) {
						$target = array(
							'title' => sanitize_text_field($_POST['field']),
							'type' => sanitize_text_field($_POST['type']),
							'for' => isset($_POST['for']) ? educare_sanitize_array(($_POST['for'])) : array()
						);
					}
					
					// Add new data
					array_push($data, $target);
					
					$wpdb->update(
						$table, 			//table
						array( 				// data
							"data" => json_encode($data)
						),
					
						array( 				//where
							'ID' => $id
						)
					);
					
					// for hide extra field type
					if (isset($_POST["educare_add_Extra_field"])) {
						// $type = strtok($target, ' ');
						$target = $target['title'];
					}

					echo '<div class="sticky_msg"><div class="notice notice-success is-dismissible"><p>'.
					sprintf(
						__(
							'Successfully Added <b>%1$s</b> at the %2$s list <br>Total: <b>%3$d</b> %2$s added.',
							'educare'
						),
						esc_html__($target, 'educare'),
						esc_html__($list, 'educare'),
						esc_html__(count($data), 'educare'),
					)
					.'</p><button class="notice-dismiss"></button></div></div>';
				}
			}
		}
		
		if (isset($_POST['educare_edit_'.$list.''])) {
			// check if user access this request
			educare_check_access('management');
			
			$in_list = $list;
			// remove all _ characters from the list (normalize the $list)
			$list = str_replace('_', ' ', $in_list);
			
			$target = sanitize_text_field($_POST[$in_list]);
			$target = str_replace('_', ' ', $target);
			
			$check = strtolower($target);
			
			// Create nonce for update or remove forms
			$update_nonce = wp_create_nonce( 'educare_update_'.esc_attr($in_list) );
			$remove_nonce = wp_create_nonce( 'remove_'.esc_attr($in_list) );
						
			if ($in_list == 'Extra_field') {
				$data_type = $Target = '';

				if ($data) {
					foreach ($data as $value) {
						if ($value['title'] == $target) {
							$data_type = $value['type'];
							$data_for = $value['for'];
							// $Target = $value['title'];
						}
					}
				}
				
				?>
				<div class="sticky_msg">
					<div class="notice notice-success is-dismissible">
						<p>
							<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post" class="py-0 px-3 add_results bg-transparent shadow-none educare_update_data">
								<div class="d-block fw-bold mb-3"><?php echo __('Edit:', 'educare') .' '. esc_html__($target, 'educare');?></div>

								<?php
								echo '<input type="hidden" name="educare_update_'.esc_attr($in_list).'_nonce" value="'.esc_attr($update_nonce).'">';
								echo '<input type="hidden" name="remove_'.esc_attr($in_list).'_nonce" value="'.esc_attr($remove_nonce).'">';
								echo '<input type="hidden" name="'.esc_attr($in_list).'">';
								?>

								<input type="hidden" name="old_data" value="<?php echo esc_attr($target);?>"/>
								<input type="hidden" name="old_type" value="<?php echo esc_attr($data_type);?>"/>

								<?php
								// Before using array_map on $_POST['for'], you should ensure that $_POST['for'] is set to avoid potential warnings or errors. 
								if (isset($data_for) && is_array($data_for)) {
									foreach ($data_for as $value) {
										echo '<input type="hidden" name="old_for[]" value="'.esc_attr($value).'"/>';
									}
								} else {
									echo '<input type="hidden" name="old_for[]" value="'.esc_attr($data_for).'"/>';
								}
								?>

								<div class="mb-3 row">
									<label for="editFieldName" class="col-sm-2 col-form-label"><?php _e('Field Name', 'educare');?></label>
									<div class="col-sm-10">
										<input id="editFieldName" type="text" name="field" class="form-control-plaintext" value="<?php echo esc_attr($target);?>" placeholder="<?php echo esc_attr($target);?>">
									</div>
								</div>

								<div class="mb-3 row">
									<label for="editFielType" class="col-sm-2 col-form-label"><?php _e('Select type', 'educare');?></label>
									<div class="col-sm-10">
										<select id="editFielType" class="m-0" name="type">
											<option value="text" <?php esc_attr(selected( 'text', $data_type ));?>><?php _e('Text', 'educare');?></option>
											<option value="number" <?php esc_attr(selected( 'number', $data_type ));?>><?php _e('Number', 'educare');?></option>
											<option value="date" <?php esc_attr(selected( 'date', $data_type ));?>><?php _e('Date', 'educare');?></option>
											<option value="email" <?php esc_attr(selected( 'email', $data_type ));?>><?php _e('Email', 'educare');?></option>
										</select>
									</div>
								</div>

								<div class="mb-3 row">
									<label for="editFielFor" class="col-sm-2 col-form-label"><?php _e('Field For', 'educare');?></label>
									<div class="col-sm-10">
										<select id="editFielFor" class="m-0" name="for[]" multiple>
											<option value="students" <?php esc_attr( selected( is_array($data_for) ? in_array('students', $data_for) : ('students' === $data_for), true ) ); ?>><?php _e('Students', 'educare');?></option>
											
											<option value="teachers" <?php esc_attr( selected( is_array($data_for) ? in_array('teachers', $data_for) : ('teachers' === $data_for), true ) ); ?>><?php _e('Teachers/Staff', 'educare');?></option>

											<option value="results" <?php esc_attr( selected( is_array($data_for) ? in_array('results', $data_for) : ('results' === $data_for), true ) ); ?>><?php _e('Results', 'educare');?></option>
										<select>
									</div>
								</div>

								<?php educare_update_anywhere_options();?>

								<div class="mb-3 row">
									<div class="col-sm-2"></div>
									<div class="col-sm-10">
										<input type="hidden" name="remove" value="<?php echo esc_attr($target);?>"/>

										<div class="d-flex gap-2">
											<button type="submit" name="educare_update_<?php echo esc_attr($list);?>" class="d-inline-flex gap-1 align-items-center justify-content-center btn btn-success p-2 pe-3 update<?php echo esc_attr(str_replace(' ', '', $list));?>" onClick="<?php echo esc_js('add(this.form)');?>"><i class="dashicons dashicons-update"></i> <?php echo __('Update', 'educare');?></button>

											<button type="submit" name="<?php echo esc_attr("remove_$list");?>" class="d-inline-flex gap-1 align-items-center justify-content-center btn btn-danger p-2 pe-3 remove<?php echo esc_attr(str_replace(' ', '', $list));?>"><i class="dashicons dashicons-trash"></i> Delete</button>
										</div>
									</div>
								</div>
							</form>
						</p>

						<button class="notice-dismiss"></button>
					</div>
				</div>
				<?php
			} else {
				?>
				<div class="sticky_msg">
					<div class="notice notice-success is-dismissible">
						<p>
							<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post" class="py-0 px-3 add_results bg-transparent shadow-none">
								<div class="d-block fw-bold mb-3"><?php echo __('Edit', 'educare') .': '. esc_html__($target, 'educare');?></div>

								<?php
								echo '<input type="hidden" name="educare_update_'.esc_attr($in_list).'_nonce" value="'.esc_attr($update_nonce).'">';
								echo '<input type="hidden" name="remove_'.esc_attr($in_list).'_nonce" value="'.esc_attr($remove_nonce).'">';
								?>

								<input type="hidden" name="old_data" value="<?php echo esc_attr($target);?>"/>

								<div class="mb-3 row">
									<label for="fieldName" class="col-sm-2 col-form-label"><?php esc_html_e($list, 'educare');?></label>
									<div class="col-sm-10">
										<input id="fieldName" type="text" name="<?php echo esc_attr($list);?>" value="<?php echo esc_attr($target);?>" placeholder="<?php echo esc_attr($target);?>" class="form-control-plaintext">
									</div>
								</div>

								<?php educare_update_anywhere_options();?>
								
								<div class="mb-3 row">
									<div class="col-sm-2 col-form-label"></div>
									<div class="col-sm-10">
										<input type="hidden" name="remove" value="<?php echo esc_attr($target);?>"/>

										<div class="d-flex gap-2">
											<button type="submit" name="educare_update_<?php echo esc_attr($list);?>" class="d-inline-flex gap-1 align-items-center justify-content-center btn btn-success p-2 pe-3 update<?php echo esc_attr(str_replace(' ', '', $list));?>" onClick="<?php echo esc_js('add(this.form)');?>"><i class="dashicons dashicons-update"></i> <?php echo __('Update', 'educare');?></button>

											<button type="submit" name="<?php echo esc_attr("remove_$list");?>" class="d-inline-flex gap-1 align-items-center justify-content-center btn btn-danger p-2 pe-3 remove<?php echo esc_attr(str_replace(' ', '', $list));?>"><i class="dashicons dashicons-trash"></i> Delete</button>
										</div>
									</div>
								</div>			
							</form>
						</p>

						<button class="notice-dismiss"></button>
					</div>
				</div>
			<?php
			}
		}
		
		// for update list content
		if (isset($_POST['educare_update_'.$list.''])) {
			// check if user access this request
			educare_check_access('management');
			
			$in_list = $list;
			// remove all _ characters from the list (normalize the $list)
			$list = str_replace('_', ' ', $in_list);
			
			$old_data = sanitize_text_field($_POST['old_data']);
			
			if (isset($_POST["educare_update_Extra_field"])) {
				$target = sanitize_text_field($_POST['field']);
			} else {
				$target = sanitize_text_field($_POST[$in_list]);
			}

			$target = str_replace('_', ' ', $target);
			
			if (empty($target)) {
				echo '<div class="sticky_msg"><div class="notice notice-error is-dismissible"><p>'.
				sprintf(
					__(
						'Sorry, its not possible to update empty field. You must fill the form for update the <b>%s</b>.',
						'educare'
					),
					esc_html__($list, 'educare')
				)
				.'</p><button class="notice-dismiss"></button></div></div>';
			} else {
				$unique_target = strtolower($target);

				if (isset($_POST["educare_update_Extra_field"])) {
					$old_content = strtolower($old_data);
					$old_type = sanitize_text_field($_POST['old_type']);
					$old_for = isset($_POST['old_for']) ? educare_sanitize_array(($_POST['old_for'])) : array();
					
					$target_type = sanitize_text_field($_POST['type']);
					$target_for = isset($_POST['for']) ? educare_sanitize_array(($_POST['for'])) : array();
					
					$old = $old_data;
					$new = $target;

					if ($data) {
						foreach ($data as $value) {
							$y[] = strtolower($value['title']);
						}
					}
				} else {
					$old_type = $old_for = $target_for = false;
					$old_content = strtolower($old_data);
					$target_type = false;
					
					$old = $old_data;
					$new = $target;

					for ($i = 0; $i < count($data); $i++) {
						$x = strtolower($data[$i]);
						$y[] = $x;
					}

				}
				
				$unique_data = $y;
				$exist = '<div class="sticky_msg"><div class="notice notice-error is-dismissible"><p>'.
				sprintf(
					__(
						'Update failed. Because, <b>%1$s</b> is already exist in your selected %2$s list. Please try a different <i>(unique)</i> one!',
						'educare'
					),
					esc_html__($new, 'educare'),
					esc_html__($list, 'educare')
				)
				.'</p><button class="notice-dismiss"></button></div></div>';
				

				if (isset($_POST["educare_update_Extra_field"])) {
					if ($data) {
						foreach ($data as $key => $value) {
							if ( strtolower($value['title']) == $old_content) {
								$data[$key] = array(
									'title' => $target,
									'type' => $target_type,
									'for' => $target_for
								);
							}
						}
					}
				} else {
					// getting the key where we need to update data
					$update_key = array_search($old_data, $data);
					$data[$update_key] = $target;
				}
				
				// make it unique
				// $data = array_unique($data);
				
				function update_data($wpdb, $table, $old, $new, $data, $id, $msgs) {
					echo wp_kses_post($msgs);
					
					$wpdb->update(
			      $table,
						array( 
							"data" => json_encode($data)
					  ),
					
			      array(
							'ID' => $id
						)
					);
				}
				
				$target_content = $unique_target;
				
				if ( $old_type == $target_type and $old_for == $target_for and $old_content == $target_content) {
					$msg = educare_show_msg(__('No changes were found in this request.', 'educare'), 'info');
				}
				
				if ( $old_type != $target_type and $old_content == $target_content) {
					$msgs = '<div class="sticky_msg"><div class="notice notice-success is-dismissible"><p>'.
					sprintf(
						__(
							'Successfully update %1$s %2$s type <b class="error">%3$s</b> to <b class="success">%4$s</b>.',
							'educare'
						),
						esc_html__($list, 'educare'),
						esc_html__($new, 'educare'),
						esc_html__($old_type, 'educare'),
						esc_html__($target_type, 'educare')
					)
					.'</p><button class="notice-dismiss"></button></div></div>';
					$msg = update_data($wpdb, $table, $old, $new, $data, $id, $msgs);
					educare_update_anywhere($list_column, $old, $new);
				}

				if ( $old_for != $target_for and $old_content == $target_content) {
					$msgs = '<div class="sticky_msg"><div class="notice notice-success is-dismissible"><p>'.
					sprintf(
						__(
							'Successfully update %1$s %2$s data for <b class="error">%3$s</b> to <b class="success">%4$s</b>.',
							'educare'
						),
						esc_html__($list, 'educare'),
						esc_html__($new, 'educare'),
						esc_html__(implode(', ', $old_for), 'educare'),
						esc_html__(implode(', ', $target_for), 'educare')
					)
					.'</p><button class="notice-dismiss"></button></div></div>';
					$msg = update_data($wpdb, $table, $old, $new, $data, $id, $msgs);
					educare_update_anywhere($list_column, $old, $new);
				}

				if ( $old_type == $target_type and $old_content != $target_content) {
					if (in_array($target_content, $unique_data)) {
						return $exist;
					} else {
						$msgs = '<div class="sticky_msg"><div class="notice notice-success is-dismissible"><p>'.
						sprintf(
							__(
								'Successfully update %1$s <b class="error">%2$s</b> to <b class="success">%3$s</b>.',
								'educare'
							),
							esc_html__($list, 'educare'),
							esc_html__($old, 'educare'),
							esc_html__($new, 'educare'),
						)
						.'</p><button class="notice-dismiss"></button></div></div>';

						$msg = update_data($wpdb, $table, $old, $new, $data, $id, $msgs);
						educare_update_anywhere($list_column, $old, $new);
					}
				}
				
				if ( $old_type != $target_type and $old_content != $target_content) {
					if (in_array($target_content, $unique_data)) {
						return $exist;
					} else {
						$msgs = '<div class="sticky_msg"><div class="notice notice-success is-dismissible"><p>'.
						sprintf(
							__(
								'Successfully update %1$s <b class="error">%2$s</b> to <b class="success">%3$s</b>. also changed type <b class="error">%4$s</b> to <b class="success">%5$s</b>.',
								'educare'
							),
							esc_html__($list, 'educare'),
							esc_html__($old, 'educare'),
							esc_html__($new, 'educare'),
							esc_html__($old_type, 'educare'),
							esc_html__($target_type, 'educare')
						)
						.'</p><button class="notice-dismiss"></button></div></div>';

						$msg = update_data($wpdb, $table, $old, $new, $data, $id, $msgs);
						educare_update_anywhere($list_column, $old, $new);
					}
				}
				
				return $msg;
			}
		}
		
		// for remove list items
		if (isset($_POST["remove_$list"])) {
			// check if user access this request
			educare_check_access('management');
			
			$in_list = $list;
			// remove all _ characters from the list (normalize the $list)
			$list = str_replace('_', ' ', $in_list);
			$target = sanitize_text_field($_POST["remove"]);

			$target = str_replace('_', ' ', $target);
			
			if (isset($_POST["remove_Extra_field"])) {
				$remove_data = educare_removeElementsByKeyValue($data, $target, 'title');
			} else {
				$remove_data = educare_removeElementsByKeyValue($data, $target);
			}
			

			// Check if the key was found
			if ($remove_data !== false) {
				$wpdb->update(
					$table, 				//table
					array( 					// data
						"data" => json_encode($remove_data)
					),
				
					array( 					//where
						'ID' => $id
					)
				);
				
				// for hide extra field type
				if (isset($_POST["remove_Extra_field"])) {
					$status = educare_check_status('clear_field');
				}
				if (isset($_POST["remove_Subject"])) {
					$status = educare_check_status('delete_subject');
				}
				
				echo '<div class="sticky_msg"><div class="notice notice-success is-dismissible"><p>'.
				sprintf(
					__(
						'Successfully removed <b>%1$s</b> from the %2$s list.',
						'educare'
					),
					esc_html__($target, 'educare'),
					esc_html__($list, 'educare')
				)
				.'</p><button class="notice-dismiss"></button></div></div>';
			} else {
				echo '<div class="sticky_msg"><div class="notice notice-error is-dismissible"><p>'.
				sprintf(
					__(
						'Sorry, %1$s <b>%2$s</b> is not found!',
						'educare'
					),
					esc_html__($list, 'educare'),
					esc_html__($target, 'educare')
				)
				.'</p><button class="notice-dismiss"></button></div></div>';
			}
		}
		
		if ($list == 'Settings') {
			// check if user access this request
			educare_check_access('settings');

			if (isset($_POST['educare_reset_default_settings'])) {
				$wpdb->query("DELETE FROM $table WHERE id = $id");
				educare_add_default_settings('Settings');
				educare_clear_dismissed_notices();
				
				echo "<div class='sticky_msg'><div class='notice notice-success is-dismissible'> <p>".__('Default settings restore successfully', 'educare')."</p><button class='notice-dismiss'></button></div></div>";
			}

			if (isset($_POST['educare_reset_everything'])) {
				// Reset everything!
				educare_uninstall_action(true);
				// Get default settings and data
				educare_database_table();
				
				echo "<div class='sticky_msg'><div class='notice notice-success is-dismissible'> <p>".__('Reset everything successfully', 'educare')."</p><button class='notice-dismiss'></button></div></div>";
			}
			
			if (isset($_POST['educare_update_settings_status'])) {
				echo "<div class='sticky_msg'><div class='notice notice-success is-dismissible'><p>".__('Settings update was successful', 'educare')."</p><button class='notice-dismiss'></button></div></div>";
			}
			
			if ( isset( $_POST['educare_default_photos'] ) && isset( $_POST['educare_attachment_id'] ) ) {
				echo "<div class='notice notice-success is-dismissible'><p>".__('Successfully updated default student photos', 'educare')."</p><button class='notice-dismiss'></button></div>";
			}
		}
	}
}



function educare_advance_fields() {
	return 'educare-advance-fields';
}


function educare_advance_fields_badge($bg = true) {
	if ($bg) {
		$bg = sanitize_text_field($bg);
	} else {
		$bg = '';
	}

	return '<div class="educare-badge lock p-1 rounded-circle '.esc_attr($bg).'" title="'.__('Unlock To Premium', 'educare').'"><i class="dashicons dashicons-lock"></i></div>';
}



function educare_update_anywhere($column, $old, $new) {
	$update_anywhere = educare_check_status('update_anywhere');
	if ($update_anywhere == 'unchecked') {
		if (!isset($_POST['update_anywhere']) && $_POST['update_anywhere'] != 'on') {
			return;
		}
	}

	global $wpdb;

	$all_table = array(
		'students',
		'results',
		'teachers',
		'marks',
		'attendance'
	);

	foreach ($all_table as $table) {
		$table = $wpdb->prefix."educare_".$table;

		// Query to check column existence
		$column_exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM information_schema.columns WHERE table_name = %s AND column_name = %s",
				$table,
				$column
			)
		);

		if ($column_exists) {
			if ($column == 'Details' or $column == 'Subject') {
				
				// Construct the UPDATE query to replace a specific part in the 'Name' column
				$update_query = $wpdb->prepare(
					"UPDATE $table SET `$column` = REPLACE(`$column`, %s, %s) WHERE `$column` LIKE %s",
					'"'.$old.'"',
					'"'.$new.'"',
					'%"'.$old.'"%'
				);

			} else {
				// Construct the UPDATE query
				$update_query = $wpdb->prepare(
					"UPDATE $table SET `$column` = %s WHERE `$column` = %s",
					$new,
					$old
				);
			}

			// Execute the UPDATE query
			$wpdb->query($update_query);
		}
		
	}
}



function educare_update_anywhere_options() {
	$update_anywhere = educare_check_status('update_anywhere');
	if ($update_anywhere == 'unchecked') {
		?>
		<div class="mb-3 row">
			<label class="col-sm-2 form-check-label" for="update_anywhere"><?php _e('Update Anywhere', 'educare')?> <div class="action_menu"><i class="dashicons action_button dashicons-info"></i> <menu class="action_link info"><?php _e('Make sure any existing data, like student records, results, and marks, stays up-to-date.', 'educare')?></menu></div></label>
			<div class="col-sm-10">
				<input type="checkbox" id="update_anywhere" name="update_anywhere" checked>
			</div>
		</div>
		<?php
	}
}



/**
 * Settings Status
 * 
 * Usage example: educare_settings_status($target, $title, $comments);
 * 
 * One more exp: educare_settings_status('confirmation', 'Delete confirmation', "Enable and disable delete/remove confirmation");
 * 
 * Enable or Disable Settings status
 * Display toggle switch to update status
 * 
 * it's return radio or input. so, always call function under form tags. Exp: 
	<form class="educare-update-settings" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
	
	<?php
	educare_settings_status('confirmation', 'Delete confirmation', "Enable and disable delete/remove confirmation");
	
	educare_settings_status('guide', 'Guidelines', "Enable and disable guide/help messages");
	?>

	<input type="submit" name="educare_update_settings_status" class="educare_button" value="&#x464 Update">
	</form>
 *
 * @since 1.0.0
 * @last-update 1.6.0
 * 
 * @param string $target				Select settings status
 * @param string $title					Display settings title
 * @param string $comments			Settings informations
 * @param bool $input						for input fields
 * @param bool $advance 				premium features badge
 * 
 * @return void|HTML
 */
function educare_settings_status($target, $title, $comments, $input = null, $advance = false) {
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
	// Prepare the query with placeholders
	$query = $wpdb->prepare( "SELECT * FROM $table WHERE list = %s", 'Settings' );
	// Execute the prepared query and get the results
	$search = $wpdb->get_results( $query );
	
	if ($search) {

		foreach ( $search as $print ) {
			$id = $print->id;
			$data = educare_decrypt_data($print->data);
		}
	
		// Get updated data
		$status = 'unchecked';
		
		if (property_exists($data, $target)) {
			$status = $data->$target;
		}

		$advance_feature = $advance_badge = '';
		$target_name = $target;
		
		if ($advance) {
			$advance_feature = educare_advance_fields();
			$advance_badge = educare_advance_fields_badge();
			$target_name = '';
		}

		if ($input) {
			if ($input === 'files') {
				$attachmentPreview = educare_get_attachment(educare_check_status($target), true);

				echo "<div class='educare-settings ".esc_attr($advance_feature)."'>";
				echo "<div class='title'>
				<h3>".esc_html__($title, 'educare')." ".wp_kses_post($advance_badge)."</h3>
				<p class='comments mt-1 mb-2'>".wp_kses_post(__($comments, 'educare'))."</p>";

				echo '<div class="getAttachment users-attachment w-100 max-width-100 text-center">';
					echo '<div class="attachmentPreview attachmentInput w-100 bg-white">';
						if ($attachmentPreview) {
							echo '<div class="attachmentImg"><img class="educare-attachment attachment-id-'.esc_attr(educare_check_status($target)).'" src="'.esc_url($attachmentPreview).'"></div>';
						}
					echo '</div>';

					if ($attachmentPreview) {
						echo '<span class="btn btn-danger d-inline-flex align-items-center gap-1 attachmentControl attachmentRemove"><span class="dashicons dashicons-remove"></span> '.__('Remove', 'educare').'</span>';
					} else {
						echo '<span class="btn btn-success d-inline-flex align-items-center gap-1 attachmentControl attachmentInput"><span class="dashicons dashicons-cloud-upload"></span> '.__('Upload Now', 'educare').'</span>';
					}

					echo '<input type="hidden" id="'.esc_attr($target).'" name="'.esc_attr($target_name).'" value="'.esc_attr(educare_check_status($target)).'" placeholder="'.esc_attr(educare_check_status($target)).'">';
				echo '</div>';

				echo "</div></div>";
			} else {
				echo "<div class='educare-settings ".esc_attr($advance_feature)."'>";
				echo "<div class='title'>
				<h3>".esc_html__($title, 'educare')." ".wp_kses_post($advance_badge)."</h3>
				<p class='comments mt-1 mb-2'>".wp_kses_post(__($comments, 'educare'))."</p>
				<input type='text' id='".esc_attr($target)."' name='".esc_attr($target_name)."' value='".esc_attr(educare_check_status($target))."' placeholder='".esc_attr(educare_check_status($target))."'>
				</div></div>";
			}
		} else {
			if ($target == 'display') {
				$display = $status;

				if ($display) {
					$advance_fields = array(
						'Term',
						'user_pin',
						'Staff_position',
						'School'
					);

					foreach ($display as $key => $value) {
						// $target = $key;
						$field_name = isset($value->title) ? sanitize_text_field($value->title) : '';
						$status = isset($value->status) ? sanitize_text_field($value->status) : 'unchecked';

						if ($key == 'Class' or $key == 'Exam' or $key == 'Year' or $key == 'user_pin' or $key == 'School') {
							$info = '<div class="action_menu"><i class="dashicons action_button dashicons-info"></i> <menu class="action_link info">';
							
							if ($key == 'Class') {
								$info .= 'If you want to disable the class from <b>View Results</b> and <b>Front-End</b> search form, you can disable it. But you need to fill in the class while adding or importing results.';
							}
							if ($key == 'Exam') {
								$info .= 'If you want to disable the exam from <b>View Results</b> and <b>Front-End</b> search form, you can disable it. But you need to fill in the exam while adding or importing results.';
							}
							if ($key == 'Year') {
								$info .= "The <b>Year</b> field cannot be disabled as it is essential for accurately specifying data. However, you can rename it to suit your needs, such as Passing Year or Exam Year.";
							}
							if ($key == 'user_pin') {
								$info .= "<b>Protected Results</b><hr>Enable students to access their results securely using a password or PIN. This feature allows you to restrict result access for specific students, such as those with outstanding payments or other conditions.";
							}
							if ($key == 'School') {
								$info .= "<b>Manage Multiple School</b><hr>Use this feature to manage multiple schools or departments. This feature requires the Educare Multiple School add-on.";
							}

							$info .= '</menu></div>';
						} else {
							$info = '';
						}

						$advance_feature = $advance_badge = '';
						$target_name = $key;

						if (in_array($key, $advance_fields)) {
							$advance_feature = educare_advance_fields();
							$advance_badge = educare_advance_fields_badge();
							$target_name = '';
						}

						?>
						<div class="educare-settings <?php echo esc_attr($advance_feature);?>">
							<div class="title">
								<div class="row">
									<div class="col-md-3 d-flex align-items-center">
										<h3 class="my-2"><?php echo esc_html(ucwords(str_replace('_', ' ', __($key, 'educare')))) . ' ' . wp_kses_post( __($info, 'educare') );?> <?php echo wp_kses_post($advance_badge);?></h3>
									</div>
									
									<div class="col-md-9 d-flex align-items-center comments">
										<input type='text' id='<?php echo esc_attr($key);?>' name='<?php echo esc_attr( $target . '[' . $key . ']');?>[title]' value='<?php echo esc_attr($field_name);?>' placeholder='<?php echo sprintf(esc_attr__('Type here to modify (%s)'), __($field_name, 'educare'));?>'>
									</div>
								</div>
							</div>
							
							<div class="status-button">
								<div class="switch-radio">
									<?php if ($key != 'Year') {
										?>
										<input type="radio" id="<?php echo esc_attr($target.'_'.$key);?>_no" name="<?php echo esc_attr( $target . '[' . $key . ']');?>[status]" value="unchecked" <?php checked( esc_attr($status), 'unchecked' );?>>
										<label for="<?php echo esc_attr($target.'_'.$key);?>_no"><?php _e('Off', 'educare')?></label>
										<?php
									}
									
									if ($target_name) {
										?>
										<input type="radio" id="<?php echo esc_attr($target.'_'.$key);?>_yes" name="<?php echo esc_attr( $target . '[' . $key . ']');?>[status]" value="checked" <?php checked( esc_attr($status), 'checked' );?>>
										<label for="<?php echo esc_attr($target.'_'.$key);?>_yes"><?php _e('On', 'educare')?></label>
										<?php
									} else {
										echo '<input type="hidden" name="'.esc_attr( $target . '[' . $key . ']').'[title]" value="'.esc_attr($field_name).'">';
										echo "<input type='hidden' name='".esc_attr( $target . '[' . $key . ']')."[status]' value='unchecked'>";
									}
									?>
								</div>
							</div>
						</div>
						<?php
					}
				} else {
					echo educare_show_msg(__('There has been a critical issues on this website. Please scroll down and click <b>Reset Settings</b> button.', 'educare'), false);
				}
			} elseif ($target == 'results_card') {
				$display = $status;
				$current_template = educare_check_status('results_card_template');
				
				foreach ($display as $card_key => $card_value) {
					if ($card_key == 'details' or $card_key == 'grade_sheet') {
						$results_card = educare_requred_data($card_value, true, true);

						foreach ($card_value as $key => $value) {

							$info = '';
							$field_name = $value->title;
							$status = $value->status;

							if ($key == 'details') {
								$info = '<div class="action_menu"><i class="dashicons action_button dashicons-info"></i> <menu class="action_link info">'.
								sprintf(
									__(
										'Hide or show %1$s header (h2) title from the results. If you want to disable the entire %1$s section from the card, please scroll down and uncheck <b>Students %1$s</b>',
										'educare'
									),
									esc_html($field_name)
								)
								.'</menu></div>';
								?>
								
								<!-- Details -->
								<div class="educare-settings d-block border-0">
									<h2 class="educare-title bg-white rounded mt-2"><?php esc_html_e($field_name, 'educare');?></h2>

									<div class="table-responsive">
										<table class="table table-bordered rounded-0">
											<tbody>
												<tr>
													<td><?php esc_html_e($results_card['result'], 'educare');?></td>
													<td><div class="success results_passed"><?php esc_html_e(educare_check_status('passed'), 'educare');?></div></td>
													<td><?php esc_html_e($results_card['year'], 'educare');?></td>
													<td><?php esc_html_e(date('Y'), 'educare');?></td>
												</tr>
												<tr>
													<td><?php esc_html_e($results_card['gpa'], 'educare');?></td>
													<td colspan="3">00</td>					
												</tr>
											</tbody>
										</table>
									</div>
								</div>
								<?php
							}
							
							if ($key == 'grade_sheet') {
								?>
								<h2 class="educare-title bg-white rounded <?php echo esc_attr($status);?>"><?php esc_html_e($field_name, 'educare');?></h2>
								<div class="table_container">
									<table class="grade_sheet demo-table">
										<tbody>
											<tr>
												<th class="<?php echo esc_attr($card_value->no->status);?>"><?php esc_html_e($results_card['no'], 'educare');?></th>
												<th class="<?php echo esc_attr($card_value->subject->status);?>"><?php esc_html_e($results_card['subject'], 'educare');?></th>
												<?php
												// check currrent template
												if (isset($display->template->$current_template)) {
													// loop all fields and get title, subtitle and status
													foreach ($display->template->$current_template as $card_field) {
														// define field titile
														$termTitle = $card_field->title;
														// define subtitle
														$subtitle = $card_field->subtitle;
														// add some html to beautify!
														if ($subtitle) {
															$subtitle = '<small>'.esc_html__($subtitle, 'educare').'</small>';
														}
														// show fields
														echo '<th class="'.esc_attr($card_field->status).'">'. esc_html__($termTitle, 'educare') . wp_kses_post( $subtitle ).'</th>';
													}
												}
												?>
											</tr>
										</tbody>
									</table>
								</div>
								<?php
								$info = '<div class="action_menu"><i class="dashicons action_button dashicons-info"></i> <menu class="action_link info">'.
								sprintf(
									__(
										'Hide or show %1$s header (h2) title from the results. If you want to disable the entire %1$s section from the card, please scroll down and uncheck <b>Students %1$s</b>',
										'educare'
									),
									esc_html($field_name)
								)
								.'</menu></div>';
							}

							?>
							<div class="educare-settings">
								<div class="title">
									<h3 class="educare-menu-container my-2"><?php esc_html_e(ucwords(str_replace('_', ' ', $key)), 'educare') . ' ' . wp_kses_post( $info );?></h3>
									<p class="comments m-0">
										<input type='text' id='<?php echo esc_attr($key);?>' name='<?php echo esc_attr( $target . '[' . $card_key . ']' . '[' . $key . '][title]');?>' value='<?php echo esc_attr($field_name);?>' placeholder='<?php echo sprintf(esc_attr__('Type here to modify (%s)'), __($field_name, 'educare'));?>'>
									</p>
								</div>
								
								<div class="status-button">
									<div class="switch-radio">
										<?php if ($key != 'Year') {
											?>
											<input type="radio" id="<?php echo esc_attr($target.'_'.$key);?>_no" name="<?php echo esc_attr( $target . '[' . $card_key . ']' . '[' . $key . '][status]');?>" value="unchecked" <?php checked( esc_attr($status), 'unchecked' );?>>
											<label for="<?php echo esc_attr($target.'_'.$key);?>_no"><?php _e('Off', 'educare')?></label>
											<?php
										}?>
										
										<input type="radio" id="<?php echo esc_attr($target.'_'.$key);?>_yes" name="<?php echo esc_attr( $target . '[' . $card_key . ']' . '[' . $key . '][status]');?>" value="checked" <?php checked( esc_attr($status), 'checked' );?>>
										<label for="<?php echo esc_attr($target.'_'.$key);?>_yes"><?php _e('On', 'educare')?></label>
									</div>
								</div>

								<div class="submenu p-0">
									<div class="submenu-content p-3">
										<p class="mt-1 mb-2"><?php _e('Sub Titile', 'educare')?>:</p>
										<input type="text" name="<?php echo esc_attr( $target . '[' . $card_key . ']' . '[' . $key . '][subtitle]');?>" value="<?php echo esc_attr($value->subtitle);?>" palaceholder="<?php _e('Sub Titile', 'educare')?>">
									</div>
								</div>
							</div>
							<?php
						}
					}

					if ($card_key == 'template') {
						if (property_exists($card_value, $current_template)) {
							$fields = array (
								'title' => 'Marks',
								'subtitle' => 'Marks',
								'status' => 'unchecked',
								'default_value' => '80',
								'placeholder' => '00',
								'type' => 'number',
								'auto' => 'off',
								'hide' => 'off'
							);

							foreach ($card_value->$current_template as $key => $value) {
								foreach ($fields as $field_key => $field_value) {
									$$field_key = sanitize_text_field( $field_value );
									
									if (property_exists($value, $field_key)) {
										$$field_key = sanitize_text_field( $value->$field_key );
									}
								}

								?>
								<div class="educare-settings">
									<div class="title">
										<h3 class="educare-menu-container my-2"><?php esc_html_e( $title, 'educare' );?></h3>
										<p class="comments m-0">
											<input type="text" id="marks" name="<?php echo esc_attr( $target . '[' .$card_key. '][' .$current_template. '][' .$key. '][title]');?>" value="<?php echo esc_attr( $title );?>" placeholder="<?php echo sprintf(esc_attr__('Type here to modify (%s)'), __($title, 'educare'));?>">
										</p>
									</div>

									<div class="status-button">
										<div class="switch-radio">
											<input type="radio" id="<?php echo esc_attr($title.'_'.$key);?>_no" name="<?php echo esc_attr( $target . '[' .$card_key. '][' .$current_template. '][' .$key. '][status]');?>" value="unchecked"  <?php esc_attr(checked( 'unchecked', $status ));?>>
											<label for="<?php echo esc_attr($title.'_'.$key);?>_no"><?php _e('Off', 'educare')?></label>
											
											<input type="radio" id="<?php echo esc_attr($title.'_'.$key);?>_yes" name="<?php echo esc_attr( $target . '[' .$card_key. '][' .$current_template. '][' .$key. '][status]');?>" value="checked"  <?php esc_attr(checked( 'checked', $status ));?>>
											<label for="<?php echo esc_attr($title.'_'.$key);?>_yes"><?php _e('On', 'educare')?></label>
										</div>
									</div>

									<div class="submenu p-0">
										<div class="submenu-content p-3">
											<p class="mt-1 mb-2"><?php _e('Sub Titile', 'educare')?>:</p>

											<input class="mb-2" type="text" name="<?php echo esc_attr( $target . '[' .$card_key. '][' .$current_template. '][' .$key. '][subtitle]');?>" value="<?php echo esc_attr($subtitle);?>" palaceholder="<?php _e('Sub Titile', 'educare')?>">

											<p class="mt-1 mb-2"><?php _e('Default value', 'educare')?>:</p>
											<input class="mb-2" type="text" name="<?php echo esc_attr( $target . '[' .$card_key. '][' .$current_template. '][' .$key. '][default_value]');?>" value="<?php echo esc_attr($default_value);?>" palaceholder="><?php _e('Default value', 'educare')?>">

											<p class="mt-1 mb-2"><?php _e('Placeholder', 'educare')?>:</p>
											<input class="mb-2" type="text" name="<?php echo esc_attr( $target . '[' .$card_key. '][' .$current_template. '][' .$key. '][placeholder]');?>" value="<?php echo esc_attr($placeholder);?>" palaceholder="<?php _e('Placeholder', 'educare')?>">
											
											<div class="select">
												<div>
													<p class="mt-1 mb-2"><?php _e('Select Type', 'educare');?>:</p>
													<select name="<?php echo esc_attr( $target . '[' .$card_key. '][' .$current_template. '][' .$key. '][type]');?>">
														<option value="text" <?php esc_attr(selected( $type, 'text' ));?>><?php _e('Text', 'educare')?></option>
														<option value="number" <?php esc_attr(selected( $type, 'number' ));?>><?php _e('Number', 'educare')?></option>
														<option value="date" <?php esc_attr(selected( $type, 'date' ));?>><?php _e('Date', 'educare')?></option>
														<option value="select" <?php esc_attr(selected( $type, 'select' ));?>><?php _e('Options', 'educare')?></option>
													</select>
												</div>

												<div>
													<p class="mt-1 mb-2"><?php _e('Auto Results', 'educare')?>:</p>
													<select name="<?php echo esc_attr( $target . '[' .$card_key. '][' .$current_template. '][' .$key. '][auto]');?>">
														<option value="on" <?php esc_attr(selected( $auto, 'on' ));?>><?php _e('Yes', 'educare')?></option>
														<option value="off" <?php esc_attr(selected( $auto, 'off' ));?>><?php _e('No', 'educare')?></option>
													</select>
												</div>

												<div>
													<p class="mt-1 mb-2"><?php _e('Hide Fields', 'educare')?>:</p>
													<select name="<?php echo esc_attr( $target . '[' .$card_key. '][' .$current_template. '][' .$key. '][hide]');?>">
														<option value="on" <?php esc_attr(selected( $hide, 'on' ));?>><?php _e('Yes', 'educare')?></option>
														<option value="off" <?php esc_attr(selected( $hide, 'off' ));?>><?php _e('No', 'educare')?></option>
													</select>
												</div>
											</div>

											<?php
											if	($type == 'select') {
												echo '<p>'.__('Options Value', 'educare').':</p>';

												if	(property_exists($value, 'value')) {
													if ($value->value) {
														if (is_array($value->value)) {
															foreach ($value->value as $options_key => $options) {
																echo '<input name="'.esc_attr( $target . '[' .$card_key. '][' .$current_template. '][' .$key. '][value][]').'" type="text" value="'.esc_attr($options).'">';
															}
														}
													} else {
														echo '<input name="'.esc_attr( $target . '[' .$card_key. '][' .$current_template. '][' .$key. '][value][]').'" type="text" value="Options1">';
														echo '<input name="'.esc_attr( $target . '[' .$card_key. '][' .$current_template. '][' .$key. '][value][]').'" type="text" value="Options2">';
													}
												}
											}
											?>
										</div>
									</div>
								</div>
								<?php
							}
						}
					}
				}
			} elseif ($target == 'banner_settings') {
				$logo1 = educare_get_attachment(0, 'logo1');
				$logo2 = educare_get_attachment(0, 'logo2');
				?>

				<div class="position-relative <?php echo educare_advance_fields();?>">
					<div class="position-absolute top-50 start-50">
						<?php echo educare_advance_fields_badge();?>
					</div>

					<div class="template-info-container fixbd-flex bg-light text-center banner rounded">
						<div class="banner-logo">
							<div class="getAttachment">
								<div class="attachmentPreview attachmentInput bg-white <?php echo educare_advance_fields();?>">
									<div class="attachmentImg">
										<?php
										echo educare_advance_fields_badge();

										if ($logo1) {
											echo '<img class="educare-attachment attachment-id-0" src="'.esc_url($logo1).'">';
										}
										?>
									</div>
								</div>

								<?php
								if ($logo1) {
									echo '<span class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1 attachmentControl attachmentRemove"><span class="dashicons dashicons-remove"></span> '.__('Remove', 'educare').'</span>';
								} else {
									echo '<span class="btn btn-sm btn-success d-inline-flex align-items-center gap-1 attachmentControl attachmentInput"><span class="dashicons dashicons-cloud-upload"></span> '.__('Upload Now', 'educare').'</span>';
								}
								?>
								
								<input type="hidden" name="banner[logo1]" value="">
							</div>
						</div>

						<div class="title">
							<div>
								<h1><input class="text-center" type="text" name="banner[title]" value="Institutions Name Or Title" placeholder="<?php _e('Instutions name or slogan', 'educare');?>"></h1>

								<p class="sub-title"><input class="text-center" type="text" name="banner[subtitle1]" value="Rangpur, Dhaka, Bangladesh" placeholder="<?php _e('Address or something like this', 'educare');?>"></p>
								
								<p class="sub-title"><input class="text-center" type="text" name="banner[subtitle2]" value="Founded in <?php echo esc_html(date('Y'))?>" placeholder="<?php _e('Year or something like this', 'educare');?>"></p>
							</div>
						</div>

						<div class="banner-logo">
						<div class="getAttachment">
								<div class="attachmentPreview attachmentInput bg-white <?php echo educare_advance_fields();?>">
									<div class="attachmentImg">
										<?php
										echo educare_advance_fields_badge();
										
										if ($logo2) {
											echo '<img class="educare-attachment attachment-id-0" src="'.esc_url($logo2).'">';
										}
										?>
									</div>
								</div>

								<?php
								if ($logo2) {
									echo '<span class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1 attachmentControl attachmentRemove"><span class="dashicons dashicons-remove"></span> '.__('Remove', 'educare').'</span>';
								} else {
									echo '<span class="btn btn-sm btn-success d-inline-flex align-items-center gap-1 attachmentControl attachmentInput"><span class="dashicons dashicons-cloud-upload"></span> '.__('Upload Now', 'educare').'</span>';
								}
								?>
								
								<input type="hidden" name="banner[logo2]" value="">
							</div>
						</div>
					</div>
				</div>
				<?php
			} else {
				// for radio button
				if ($target == 'results_card_template' or $target == 'search_form_template' or $target == 'certificate_template' or $target == 'profiles_template') {
					// Show all template
					educare_show_template('educare_'.$target);

					if ($target == 'search_form_template' or $target == 'certificate_template' or $target == 'profiles_template') {
						$target_template = educare_check_status($target);
						$template_data = educare_get_template_details($target, 'fields');

						if ($template_data) {
							echo '<div class="center mt-4">
								<b>'.__('Template Settings', 'educare').'</b>
							</div><hr>';

							foreach ($template_data as $field => $value) {
								echo "<div class='educare-settings'>";
								echo "<div class='title'>
								<h3>".esc_html__($value['title'], 'educare')."</h3>
								<p class='comments mt-1 mb-2'>".esc_html__($value['subtitle'], 'educare')."</p>

								<input type='text' name='template_settings".esc_attr('['.$target.']['.$target_template.']['.$field.'][value]')."' value='".esc_attr($value['value'])."' placeholder='".esc_attr($value['value'])."'>
								</div></div>";
							}
						}
					}
				} else {
					?>
					<div class="educare-settings <?php echo esc_attr($advance_feature);?>">
						<div class="title">
							<h3><?php esc_html_e($title, 'educare');?> <?php echo wp_kses_post($advance_badge);?></h3>
							<p class="comments m-0 mt-1"><?php echo wp_kses_post(__($comments, 'educare'));?></p>
						</div>
						
						<div class="status-button">
							<div class="switch-radio">
								<input type="radio" id="<?php echo esc_attr($target);?>_no" name="<?php echo esc_attr($target);?>" value="unchecked" <?php checked( esc_attr($status), 'unchecked' );?>>
								<label for="<?php echo esc_attr($target);?>_no"><?php _e('Off', 'educare')?></label>
								
								<?php
								if ($advance) {
									echo "<input type='hidden' name='".esc_attr( $target )."' value='unchecked'>";
								} else	{
									?>
									<input type="radio" id="<?php echo esc_attr($target);?>_yes" name="<?php echo esc_attr($target);?>" value="checked" <?php checked( esc_attr($status), 'checked' );?>>
									<label for="<?php echo esc_attr($target);?>_yes"><?php _e('On', 'educare')?></label>
									<?php
								}
								?>
							</div>
						</div>
					</div>
					<?php
				}
			}
		}
		
	} else {
		echo educare_guide_for('db_error');
	}
}



/**
 * Get details of a specific template.
 *
 * Retrieves details of a specified template using a provided template identifier and optional field name.
 *
 * @param string $template The identifier for the template to retrieve details for.
 * @param string $field    Optional. The specific field within the template details to retrieve. Default is 'fields'.
 *
 * @return mixed|null|array Returns the specified field from the template details if found, or the entire template details.
 *                         Returns null if the template or field does not exist, and false if the template exists but the field does not.
 */
function educare_get_template_details($template, $field = 'fields') {
	$target_template = educare_check_status($template);
	
	if (function_exists($target_template)) {
		$template_data = call_user_func($target_template, null, true);

		if ($template_data) {
			if ($field) {
				if (key_exists($field, $template_data)) {
					return $template_data[$field];
				} else {
					return false;
				}
			}
			
			return $template_data;
		}

	}

	return null;
}



/**
 * Get template settings with modified fields based on specified criteria.
 *
 * Retrieves template settings for a given template and modifies specified fields.
 *
 * @param string $template_for The identifier for the template settings to retrieve.
 * @param array  $fields       An array of fields to modify within the template settings.
 *
 * @return array Modified template settings with specified fields updated, or the original fields if conditions are not met.
 */
function educare_get_template_settings(array $fields, $template_name, $template_for = 'search_form_template') {
	$template_settings = educare_check_status('template_settings');

	if ($template_settings) {
		if (property_exists($template_settings, $template_for)) {
			$saved_fields = $template_settings->$template_for;

			if ($template_name) {
				if (property_exists($saved_fields, $template_name)) {
					$current_template = $saved_fields->$template_name;

					if ($current_template) {
						foreach ($fields as $key => $value) {
							if (isset($current_template->$key->value)) {
								$fields[$key]['value'] = sanitize_text_field( $current_template->$key->value );
							}
						}

						// return saved fields
						return $fields;
					}
				} else {
					return $fields;
				}
			}
		}
	}

	// return original fields
	return $fields;
}



/** 
 * Educare settings forms
 * 
 * @since 1.4.0
 * @last-update 1.4.1
 * 
 * @return mixed
 */
function educare_settings_form() {
	?>
		<form class="educareUpdateSettings" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
			<?php
			ob_start();
			echo bloginfo('url');
			$domain = ob_get_clean();

			$active_menu = '';
			if (isset($_POST['active_menu'])) {
				$active_menu = sanitize_text_field( $_POST['active_menu'] );
			}

			?>
			<div class="collapses">
				<div class="collapse">
					<input class="head" type="radio" name="settings_status_menu" id="Shortcode_menu" checked>
					<label class="collapse-label" for="Shortcode_menu"><div class="collapse-label-title"><i class="dashicons dashicons-shortcode"></i> <?php _e('Shortcode', 'educare');?></div></label>
					
					<div class="collapse-content">
						<?php
						$certificate_code = '[educare_results certificate="true" results="false"]';
						$both_code = '[educare_results certificate="true"]';
						
						// echo "<div style='padding: 1px 0;'></div>";
						echo '
						<div class="educare-settings pt-2"><div class="title"><h3>'.__('Results Shortcode', 'educare').'</h3><h3>
						<p class="comments mt-1 mb-2">'.sprintf(__('Copy and paste this <strong>`%1$s`</strong> shortcode in your editor, template or any shortcode-ready area for front end (%2$s).', 'educare'), '[educare_results]', __('results system', 'educare')).'</p>
						
						<div class="educare-copy-button">
							<input type="text" id="educare_results" value="[educare_results]" disabled>
							<span class="copy-tooltip-text">Copied!</span>
						</div>

						</h3></div></div>

						<div class="educare-settings '.esc_attr(educare_advance_fields()).'"><div class="title"><h3>'.__('Certificate Shortcode', 'educare').' '.educare_advance_fields_badge().'</h3><h3>
						<p class="comments mt-1 mb-2">'.sprintf(__('Copy and paste this <strong>`%1$s`</strong> shortcode in your editor, template or any shortcode-ready area for front end (%2$s).', 'educare'), esc_html($certificate_code), __('certificate system', 'educare')).'</p>

						<div class="educare-copy-button">
							<input type="text" id="certificate_code" value="'.esc_attr($certificate_code).'" disabled>
							<span class="copy-tooltip-text">Copied!</span>
						</div>

						</h3></div></div>

						<div class="educare-settings '.esc_attr(educare_advance_fields()).'"><div class="title"><h3>'.__('Both (Results and Certificate)', 'educare').' '.educare_advance_fields_badge().'</h3><h3>
						<p class="comments mt-1 mb-2">'.sprintf(__('Copy and paste this <strong>`%1$s`</strong> shortcode in your editor, template or any shortcode-ready area for front end (%2$s).', 'educare'), esc_html($both_code), __('results and certificate system', 'educare')).'</p>
						
						<div class="educare-copy-button">
							<input type="text" id="both_code_hortcode" value="'.esc_attr($both_code).'" disabled>
							<span class="copy-tooltip-text">Copied!</span>
						</div>

						</h3></div></div>

						<div class="educare-settings '.esc_attr(educare_advance_fields()).'"><div class="title"><h3>'.__('Profiles Shortcode', 'educare').' '.educare_advance_fields_badge().'</h3><h3>
						<p class="comments mt-1 mb-2">'.sprintf(__('Copy and paste this <strong>`%1$s`</strong> shortcode in your editor, template or any shortcode-ready area for front end (%2$s).', 'educare'), '[educare_profiles]', __('profiles system', 'educare')).'</p>

						<div class="educare-copy-button">
							<input type="text" id="profiles_hortcode" value="[educare_profiles]" disabled>
							<span class="copy-tooltip-text">Copied!</span>
						</div>
						
						</h3></div></div>
						
						<div class="educare-settings '.esc_attr(educare_advance_fields()).'"><div class="title"><h3>'.__('Edit Profiles Shortcode', 'educare').' '.educare_advance_fields_badge().'</h3><h3>
						<p class="comments mt-1 mb-2">'.sprintf(__('Copy and paste this <strong>`%1$s`</strong> shortcode in your editor, template or any shortcode-ready area for front end (%2$s).', 'educare'), '[educare_edit_profiles]', __('edit profile page', 'educare')).'</p>

						<div class="educare-copy-button">
							<input type="text" id="profiles_hortcode" value="[educare_edit_profiles]" disabled>
							<span class="copy-tooltip-text">Copied!</span>
						</div>
						
						</h3></div></div>';

						
						echo '<h6 class="educare-settings mt-4 d-flex gap-2"><span class="dashicons dashicons-admin-page"></span>Page Setup</h6>';

						echo '<div class="py-0 px-1">';
						echo educare_guide_for(sprintf(__('Enter your Front-End page slug (where you use educare shortcode in WP editor, template or any shortcode-ready area for front end results system). Don\'t need to insert with domain - <strike class="disable">%s/</strike><code class="text-success">results</code>. Only slug will be accepted, for exp: <code class="text-success">results</code> or <code class="text-success">index.php/results</code>.', 'educare'), esc_url($domain)));
						echo '</div>';
						

						educare_settings_status('results_page', 'Results Page', sprintf(__('Enter your front-end page slug, where you use <strong>`%1$s`</strong> shortcode in your editor, template or any shortcode-ready area for front end (%2$s).', 'educare'), '[educare_results]', __('results system', 'educare')), true);
						
						educare_settings_status('profiles_page', 'Profiles Page', sprintf(__('Enter your front-end page slug, where you use <strong>`%1$s`</strong> shortcode in your editor, template or any shortcode-ready area for front end (%2$s).', 'educare'), '[educare_profiles]', __('profiles system', 'educare')), true, true);

						educare_settings_status('edit_profiles_page', 'Edit Profiles Page', sprintf(__('Enter your front-end page slug, where you use <strong>`%1$s`</strong> shortcode in your editor, template or any shortcode-ready area for front end (%2$s).', 'educare'), '[educare_edit_profiles]', __('profiles system', 'educare')), true, true);

						educare_settings_status('front_dashboard', 'Front-End Dashboard Tab', sprintf(__('Enter your front-end dashboard tab slug, where you use <strong>`%1$s`</strong> shortcode in your editor, template or any shortcode-ready area for front end (%2$s).', 'educare'), '[educare_profiles]', __('profiles system', 'educare')), true, true);
						?>
					</div>
				</div>

				<div class="collapse">
					<div style="background-color: inicial;">
					<input class="head" type="radio" name="settings_status_menu" id="Display_menu" <?php echo esc_attr(checked($active_menu, 'Display_menu'))?> />
					<label class="collapse-label" for="Display_menu"><div class="collapse-label-title"><i class="dashicons dashicons-editor-spellcheck"></i><?php _e('Default Fields', 'educare');?></div></label>
					<div class="collapse-content">
						<?php
						echo "<div style='padding: 1px 0;'>";
						echo educare_guide_for('display_msgs');
						echo '</div>';
			
						educare_settings_status('display', 'Delete confirmation', "Enable and disable delete/remove confirmation");
						?>
					</div>
					
					</div>
				</div>

				<div class="collapse">
					<div style="background-color: inicial;">
					<input class="head" type="radio" name="settings_status_menu" id="Logo_And_Branding_menu" <?php echo esc_attr(checked($active_menu, 'Logo_And_Branding_menu'))?> />
					<label class="collapse-label" for="Logo_And_Branding_menu"><div class="collapse-label-title"><i class="dashicons dashicons-admin-customizer"></i><?php _e('Logo & Branding', 'educare')?> <?php echo educare_advance_fields_badge(false);?></div></label>
					<div class="collapse-content">
						<div style='padding: 1px 0;'></div>

							<?php
							educare_settings_status('banner_settings', '', "");

							educare_settings_status('show_banner', 'Show Banner', "Enable or disable the display of the banner, including the title, subtitle, and logo, on the results card or search form", null, true);

							educare_settings_status('admin_area_banner', 'Admin Area Banner', "Show banner (Title, Subtitle and Logo) in admin area. If you don't need to show banner in admin area, just uncheck it.", null, true);
							?>

						</div>
					
					</div>
				</div>

				<div class="collapse">
					<div style="background-color: inicial;">
					<input class="head" type="radio" name="settings_status_menu" id="Search_Form_menu" <?php echo esc_attr(checked($active_menu, 'Search_Form_menu'))?> />
					<label class="collapse-label" for="Search_Form_menu"><div class="collapse-label-title"><i class="dashicons dashicons-search"></i><?php _e('Search Form', 'educare')?></div></label>
					<div class="collapse-content">
					<div style='padding: 1px 0;'></div>

						<?php
						educare_settings_status('search_form_template', 'Search Form Template', "Select one for front-end result card layout");
						?>

					</div>
					
					</div>
				</div>

				<div class="collapse">
					<div style="background-color: inicial;">
					<input class="head" type="radio" name="settings_status_menu" id="dashboard_templates_menu" <?php echo esc_attr(checked($active_menu, 'dashboard_templates_menu'))?> />
					<label class="collapse-label" for="dashboard_templates_menu"><div class="collapse-label-title"><i class="dashicons dashicons-dashboard"></i><?php _e('Dashboard Templates', 'educare')?> <?php echo educare_advance_fields_badge(false);?></div></label>
					<div class="collapse-content">
					<div style='padding: 1px 0;'></div>

						<?php
						educare_settings_status('profiles_template', 'Profiles', "Select one for front-end result card layout");
						?>

					</div>
					
					</div>
				</div>

				<div class="collapse">
					<div style="background-color: inicial;">
					<input class="head" type="radio" name="settings_status_menu" id="Certificate_Templates_menu" <?php echo esc_attr(checked($active_menu, 'Certificate_Templates_menu'))?> />
					<label class="collapse-label" for="Certificate_Templates_menu"><div class="collapse-label-title"><i class="dashicons dashicons-awards"></i><?php _e('Certificate Templates', 'educare') . ' '; echo educare_advance_fields_badge(false);?></div></label>
					<div class="collapse-content">

						<div style='padding: 1px 0;'></div>
							<?php
							educare_settings_status('certificate_template', 'Certificate Template', "Select one for front-end certificate layout");

							educare_settings_status('custom_certificate_bg', 'Custom Background', "Select custom background for certificate template. If you want to change your selected template background, you can easily select your custom certificate template from here.", 'files', true);
							?>
						</div>

					</div>
				</div>

				<div class="collapse">
					<div style="background-color: inicial;">
					<input class="head" type="radio" name="settings_status_menu" id="Results_card_menu" <?php echo esc_attr(checked($active_menu, 'Results_card_menu'))?> />
					<label class="collapse-label" for="Results_card_menu"><div class="collapse-label-title"><i class="dashicons dashicons-id"></i><?php _e('Results Card', 'educare')?></div></label>
					<div class="collapse-content">
					<div style='padding: 1px 0;'></div>

						<?php
						educare_settings_status('results_card_template', 'Result Card Template', "Select one for front-end result card layout");

						echo '<div class="center mt-4">
							<b>Template Settings</b>
						</div><hr>';

						educare_settings_status('results_card', '', "");

						echo educare_guide_for(sprintf(
							__(
								"All options are performed based on the active template. So make sure, your active template supports these options. Also, you can customize card/academy logo, title subtitle from <a href='%s'>Logo & Branding</a> section",
								'educare'
							),
							esc_url('/wp-admin/admin.php?page=educare-settings&menu=Logo_And_Branding')
						));

						educare_settings_status('student_info', 'Student Info', "Show information of students on result card");
						
						educare_settings_status('details', 'Students Details', "Show extra-field/details of students on result card");

						educare_settings_status('grade_sheet', 'Grade Sheet', "Show the grade sheet on the result card");

						educare_settings_status('quick_overview', 'Quick Overview', "Disable this option when don't need to quick overview section on results card", null, true);
						?>

					</div>
					
					</div>
				</div>

				<div class="collapse">
					<input class="head" type="radio" name="settings_status_menu" id="Results_System_menu" <?php echo esc_attr(checked($active_menu, 'Results_System_menu'))?> />
					<label class="collapse-label" for="Results_System_menu"><div class="collapse-label-title"><i class="dashicons dashicons-welcome-learn-more"></i><?php _e('Results System', 'educare')?></div></label>
					<div class="collapse-content">
						<?php
						educare_settings_status('passed', 'Passed Status', "Define text when results is passed", true);
						educare_settings_status('failed', 'Failed Status', "Define text when results is failed", true);
						
						educare_settings_status('optional_sybmbol', 'Optional Subject Detection', "Define optional subject identifier character/symbol. In this way educare define and identify optional subjects when you add or import results.", true);
		
						educare_settings_status('group_subject', 'Group Based Subject', "Define how many subject in each group. For disable or unlimited set <code>0</code>", true);

						educare_settings_status('auto_results', 'Auto Results', "Automatically calculate students results status Passed/Failed and GPA");

						educare_settings_status('auto_regi_no', 'Auto Registration Number', "Automatically genarate student registration (Regi No) number.", null, true);

						educare_settings_status('position_filtering', 'Position Filtering', "Filter position based on passed or failed status (Every single subject). Example: S1 fails in 2 subjects and S2 in 1 subject. In this case, S1 will be in first and other person will be in second position. Because, S2 failed in less subjects than S1.", null, true);

						educare_settings_status('certificate_system', 'Certificate System', "This features allow students to view and download certificate", null, true);

						educare_settings_status('rattings_system', 'Rattings System', "This features allow to remarks student with rattings skale", null, true);

						educare_settings_status('remarks', 'Remarks System', "If you don't need to remarks section, judt uncheck it", null, true);

						educare_settings_status('print_only_remarks', 'Print Only Remarks', "Hide remarks section from front-end result card and show only when print results. Checked this when you want to hide remarks from fornt end and Unchecked when need show remarks section both (front-end and also print).", null, true);
						?>
					</div>
				</div>

				<div class="collapse">
					<input class="head" type="radio" name="settings_status_menu" id="Users_menu" <?php echo esc_attr(checked($active_menu, 'Users_menu'))?> />
					<label class="collapse-label" for="Users_menu"><div class="collapse-label-title"><i class="dashicons dashicons-admin-users"></i><?php _e('Users Settings', 'educare') . ' '; echo educare_advance_fields_badge(false);?></div></label>
					<div class="collapse-content">
						<?php
						educare_settings_status('photos', 'Users Photos', "Show or hide students, staffer photos");

						educare_settings_status('user_documents', 'Documents', "Upload verification documents such as an identity card (Documents), ID, or any other required documents to verify users.", null, educare_advance_fields());

						educare_settings_status('user_signature', 'Signature', "Upload signature verify users", null, educare_advance_fields());

						educare_settings_status('staff_profiles', 'Staff Profiles', "Teachers or staff can login their profiles and access roles based data. If you don't need this just disable it.", null, educare_advance_fields());

						educare_settings_status('user_profiles', 'Student Profiles', "Students can login their profiles and access roles based data. If you don't need this just disable it.", null, educare_advance_fields());

						educare_settings_status('connect_wp_users', 'Connect WP Users', "Connect Educare users with WordPress users (with email or username) that alredy added or registared.", null, educare_advance_fields());

						educare_settings_status('publish_user_by_admin', 'Allow the School Admin to Publish Users.', "Allow school administrators to publish user (students or staffer) without requiring approval from the main admin. Enable this option when verification is not needed. This feature is available exclusively with the Educare Multiple School Module.", null, educare_advance_fields());

						educare_settings_status('publish_results_by_admin', 'Allow the School Admin to Publish results.', "Allow school administrators to publish results data without requiring approval from the main admin. Enable this option when verification is not needed. This feature is available exclusively with the Educare Multiple School Module.", null, educare_advance_fields());

						educare_settings_status('publish_results_by_teachers', 'Allow Teachers To Publish Results', "Allow teachers to publish results while adding marks", null, true);
						?>
					</div>
				</div>

				<div class="collapse">
					<input class="head" type="radio" name="settings_status_menu" id="Security_menu" <?php echo esc_attr(checked($active_menu, 'Security_menu'))?> />
					<label class="collapse-label" for="Security_menu"><div class="collapse-label-title"><i class="dashicons dashicons-lock"></i><?php _e('Security', 'educare')?></div></label>
					<div class="collapse-content">
						<?php
						echo "<div style='padding: 1px 0;'>";

						// Google reCAPTCHA
						echo educare_guide_for("<i>FAQ:</i> How to get my site or secret key?<br>Please go to <a href ='https://www.google.com/recaptcha' target='_blank'>google recaptcha</a>. Click Admin Console or Get Started with Enterprise at the top right corner. Enter a label for your ReCaptcha and select the V2 checkbox. Add the URL for your site in the Domain section. Accept the terms of service and click Submit. Copy the Site Key and Secret Key that Google generates.");
						echo '</div>';

						// Site Key: 
						educare_settings_status('re_captcha', 'Google Re-Captcha', "Enable google recaptcha to improve security. Here, You need to enter/paste your google re-captcha v2 site or secret key. (Currently it's only supports <b>ReCaptcha V2</b>)");

						// Sectet Key: 
						educare_settings_status('site_key', 'Site Key', "Paste your google re-captcha v2 site key:", true);

						educare_settings_status('secret_key', 'Secret Key', "Paste your google re-captcha v2 secret key:", true);

						educare_settings_status('always_re_captcha', 'Always Re-Captcha', "Show Re-Captcha always (for logged or non-logged in users). If you want to disable Captcha for logged in users, then turn off this options.");

						educare_settings_status('secure_results', 'Login To Access Results', "Show results for login (specific) users only", null, true);
						?>
					</div>
				</div>

				<div class="collapse">
					<input class="head" type="radio" name="settings_status_menu" id="Others_menu" <?php echo esc_attr(checked($active_menu, 'Others_menu'))?> />
					<label class="collapse-label" for="Others_menu"><div class="collapse-label-title"><i class="dashicons dashicons-admin-tools"></i><?php _e('Others', 'educare')?></div></label>
					<div class="collapse-content">
						<?php
						educare_settings_status('data_per_page', 'Data Per Page', "Define how many data display on one page. This is for default paginaton. You can change it anytime", true);

						educare_settings_status('guide', 'Guidelines', "Enable this option to receive smart guidance and helpful messages from Educare. These features provide step-by-step assistance on using Educare, making them especially useful for new users.");

						educare_settings_status('show_cover_photos', 'Show Cover Photos', "Enable or disable cover images in the admin area for sections such as Management, Attendance, and Performance headers.");

						educare_settings_status('confirmation', 'Delete confirmation', "Enable these options to get a popup confirmation when you delete something.");

						educare_settings_status('update_anywhere', 'Update Anywhere', "Enable this options to make sure any existing data, like student records, results, and marks, stays up-to-date when update anythink in management.");
						
						educare_settings_status('advance', 'Advance Settings', "Enable these options to access or view the Advanced/Developer menu. (This is only for developers or advanced users).");
						?>
					</div>
				</div>

			</div>
			
			<?php
			if (educare_check_status('advance') == 'checked') {
				?>
				<div id="advance_settings">
					<br>
					<div class="collapses">
						<div class="collapse">
							<input class="head" type="radio" name="advance_settings_status" id="Advance_Settings_menu" checked>
							<label class="collapse-label" for="Advance_Settings_menux"><div class="collapse-label-title"><i class="dashicons dashicons-performance"></i><?php _e('Advance Settings', 'educare')?></div></label>
							<div class="collapse-content">
								<?php
								echo "<div style='padding: 1px 0;'>";
								educare_settings_status('problem_detection', '(AI) Problem Detection', "Automatically detect and fix Educare related problems. Recommended to enable this options when update Educare.");
								echo '</div>';

								educare_settings_status('clear_data', 'Clear Data', "Clear all Educare data from the database when you uninstall or delete Educare from the plugin list. Ensure it is disabled during updates.");
								?>
							</div>
						</div>
					</div>
				</div>
				<?php
			} else {
				echo '<input type="hidden" name="problem_detection" value="'.esc_attr(educare_check_status('problem_detection')).'">';
				echo '<input type="hidden" name="clear_data" value="'.esc_attr(educare_check_status('clear_data')).'">';
			}

			$update_settings = wp_create_nonce( 'educare_update_settings_status' );
			$reset_settings = wp_create_nonce( 'educare_reset_default_settings' );
			$reset_everything = wp_create_nonce( 'educare_reset_everything' );

			echo '<input type="hidden" name="educare_update_settings_status_nonce" value="'.esc_attr($update_settings).'">';
			echo '<input type="hidden" name="educare_reset_default_settings_nonce" value="'.esc_attr($reset_settings).'">';
			echo '<input type="hidden" name="educare_reset_everything_nonce" value="'.esc_attr($reset_everything).'">';
			?>

			<!-- Reset -->
			<div id="reset_settings">
				<br>
				<div class="collapses">
					<div class="collapse">
						<input class="head" type="radio" name="reset_settings_status" id="Reset_Settings_menu" checked="">
						<label class="collapse-label" for="Reset_Settings_menu">
							<div class="collapse-label-title"><i class="dashicons dashicons-update-alt"></i>Reset Settings</div>
						</label>
						<div class="collapse-content">
							<div style="padding: 1px 0;">
								<div class="educare-settings">
									<div class="title">
										<h3>Restore Default Settings</h3>
										<h3>
											<p class="comments">Are you sure to restore default settings? This will not effect your content (Class, Subject, Exam, Year, Extra Field and others), It's only reset your current settings status and value.</p>
										</h3>
									</div>

									<div class="status-button">
										<div class="switch-radio">
											<button type="submit" name="educare_reset_default_settings" class="reset-button" title="Restore Default Settings"><i class="dashicons dashicons-update-alt"></i></button>
										</div>
									</div>
								</div>

								<div class="educare-settings reset-everything">
									<div class="title">
										<h3>Reset Everything</h3>
										<h3>
											<p class="comments">Are you sure you want to reset all settings? This action will restore all Educare-related data to it's default state. Please back up any important data before proceeding.</p>
										</h3>
									</div>

									<div class="status-button">
										<div class="switch-radio">
											<button type="submit" name="educare_reset_everything" class="reset-button" title="Reset Everything"><i class="dashicons dashicons-trash"></i></button>
										</div>
									</div>
								</div>
							</div>					
						</div>
					</div>
				</div>
			</div>
			
			<div class="button-container">
				<button type="submit" name="educare_update_settings_status" class="btn btn-success d-flex gap-2 align-items-center justify-content-center mt-3 py-2"><i class="dashicons dashicons-yes-alt"></i> <?php _e('Save Settings', 'educare')?></button>
			</div>
				
		</form>
	<?php
}



/**
 * Class wise Jubject
 * Usage example: educare_setting_subject('Subject');
 * 
 * @since 1.2.0
 * @last-update 1.2.4
 * 
 * @param string|mixed $list			select specific data
 * 
 * @return void
 * 
 * This is a most important function of educare. Because, additing this function its possible to add different grading rules. here some necessity of this function given below:
 * 
 * 1. Sossible to add class wise subject
 * 2. Sossible to add different grading systems
 * 3. Possible to manage or modify grading systems
 * 4. Macking Educare_results database unique
 * 5. Make database clean
 * and much more...............
 */
 function educare_process_class($list) {
	if ($list === 'Rattings') {
		echo educare_show_msg('Unlock Premium', false);
	} else {
		global $wpdb;
		$table = $wpdb->prefix."educare_settings";
		// Prepare the query with placeholders
		$query = $wpdb->prepare( "SELECT * FROM $table WHERE list = %s", $list );
		// Execute the prepared query and get the results
		$search = $wpdb->get_results( $query );

		if ($search) {
			foreach ( $search as $print ) {
				$data = $print->data;
				$id = $print->id;
			}
			
			$data = json_decode($data, true);

			if ($list == 'Rattings') {
				$msgs = 'remark';
			} else {
				$msgs = 'subject';
			}

			// check if user access this request
			educare_check_access('management');

			// for add list items (Subject)
			if (isset($_POST["educare_process_$list"])) {
				// geting form data and store as a var
				if (isset($_POST['edit_class']) or isset($_POST['update_class']) or isset($_POST['remove_class']) or isset($_POST['add_class'])) {
					$target = sanitize_text_field($_POST['class']);
				} else {
					$target = sanitize_text_field($_POST['subject']);
				}

				if (isset($_POST['update_class'])) {
					$class = sanitize_text_field($_POST['old_class']);
				} else {
					$class = sanitize_text_field($_POST['class']);
				}

				// Check if selected class exist or not, if exist then apply this logic
				if (key_exists($class, $data)) {
					// Choice selected grouo

					if (isset($_POST['add_class'])) {
						echo '<div class="sticky_msg"><div class="notice notice-error is-dismissible"><p>'.
						sprintf(
							__(
								'<b>%1$s</b> is already exist in %2$s list',
								'educare'
							),
							esc_html__($target, 'educare'),
							esc_html__($list, 'educare')
						)
						.'</p><button class="notice-dismiss"></button></div></div>';
					}

					if (isset($_POST['edit_class'])) {
						$subject_list = array();
						foreach ($data as $key => $value) {
							$subject_list[$key] = $key;
						}
					} else {
						$subject_list = $data[$class];
					}
					
					// check if subject field is empty or not
					if (empty($target)) {
						echo '<div class="sticky_msg"><div class="notice notice-error is-dismissible"><p>'.sprintf(
							__(
								'You must fill the form for add the <b>%s</b>.',
								'educare'
							),
							esc_html__($msgs, 'educare')
						).'</p><button class="notice-dismiss"></button></div></div>';
					} else {
						$search_terget = in_array(strtolower($target), array_map('strtolower', $subject_list));
						
						if (isset($_POST['update_class']) or isset($_POST['add_class'])) {
							$search_terget = key_exists(strtolower($target), $data);
						}

						$process = true;
						$msg = '';

						// check if subject exist or not
						if ($search_terget) {
							if (isset($_POST['add_subject']) or isset($_POST['update_subject']) or isset($_POST['add_class']) or isset($_POST['update_class'])) {
								// if add_Subject
								$process = false;
								$update_subject = false;
								if (isset($_POST['update_subject'])) {
									$old_subject = strtolower(sanitize_text_field( $_POST['old_subject'] ));
									$old_class = strtolower(sanitize_text_field( $_POST['old_class'] ));

									if (strtolower($target) == $old_subject and strtolower($class) == $old_class) {
										$update_subject = true;
									}
								}

								if (isset($_POST['update_class'])) {
									$old_class = strtolower(sanitize_text_field( $_POST['old_class'] ));

									if (strtolower($target) !== $old_class) {
										$update_subject = true;
									}
								}

								if ($update_subject) {
									$msg = educare_show_msg(__('No changes were found in this request.', 'educare'), 'info');
								} else {
									$msg = '<div class="sticky_msg"><div class="notice notice-error is-dismissible"> <p>'.
									sprintf(
										__(
											'<b>%1$s</b> is already exist in your selected %2$s (%3$s)',
											'educare'
										),
										esc_html__($target, 'educare'),
										esc_html__($list, 'educare'),
										esc_html__($class, 'educare')
									)
									.'</p><button class="notice-dismiss"></button></div></div>';

									if (isset($_POST['update_class'])) {
										$msg = '<div class="sticky_msg"><div class="notice notice-error is-dismissible"> <p>'.
										sprintf(
											__(
												'<b>%1$s</b> is already exist in %2$s list',
												'educare'
											),
											esc_html__($target, 'educare'),
											esc_html__($list, 'educare')
										)
										.'</p><button class="notice-dismiss"></button></div></div>';
									}
									
								}
							}
							elseif (isset($_POST['edit_subject'])) {
								?>
								<div class="sticky_msg">
									<div class="notice notice-success is-dismissible add_results">
										<p>

											<form action="" method="post" class="py-0 px-3 add_results bg-transparent shadow-none">
												<div class="d-block fw-bold mb-3"><?php echo __('Edit:', 'educare') .' '. esc_html__($target, 'educare');?></div>
												<?php
												$update_subject_nonce = wp_create_nonce( 'update_subject' );
												$remove_subject_nonce = wp_create_nonce( 'remove_subject' );
												
												echo '<input type="hidden" name="update_subject_nonce" value="'.esc_attr($update_subject_nonce).'">';
												echo '<input type="hidden" name="remove_subject_nonce" value="'.esc_attr($remove_subject_nonce).'">';
												?>

												<input type="hidden" name="educare_process_<?php echo esc_attr($list);?>">
												<input type="hidden" name="old_subject" value="<?php echo esc_attr($target);?>">
												<input type="hidden" name="old_class" value="<?php echo esc_attr($class);?>">
												
												<div class="mb-3 row">
													<label for="editFieldSubject" class="col-sm-2 col-form-label"><?php esc_html_e(ucfirst($msgs), 'educare');?></label>
													<div class="col-sm-10">
														<input id="editFieldSubject" type="text" name="subject" class="form-control-plaintext" value="<?php echo esc_attr($target);?>" placeholder="<?php echo esc_attr($target);?>" pattern="[A-Za-z0-9 ]+" title="<?php _e('Only characters, numbers and space allowed. (A-Za-z0-9)', 'educare')?>">
													</div>
												</div>

												<div class="mb-3 row">
													<label for="editFieldClass" class="col-sm-2 col-form-label"><?php esc_html_e(ucfirst($list), 'educare');?></label>
													<div class="col-sm-10">
														<select class="m-0" name="class" id="editFieldClass">
															<?php
															foreach ($data as $key => $value) {
																if ($key == $class) {
																	$selected = 'selected';
																} else {
																	$selected = '';
																}
																echo "<option value='".esc_attr( $key )."' ".esc_attr( $selected ).">".esc_html( $key )."</option>";
															}
															?>
														</select>
													</div>
												</div>

												<?php educare_update_anywhere_options();?>

												<div class="row">
													<div class="col-sm-2"></div>
													<div class="col-sm-10">
														<div class="d-flex gap-2">
															<button type="submit" name="update_subject" id="educare_results_btn" class="d-inline-flex gap-1 align-items-center justify-content-center btn btn-success p-2 pe-3 proccess_<?php echo esc_attr($list);?>"><i class="dashicons dashicons-update"></i> <?php echo __('Update', 'educare');?></button>

															<button type="submit" name="remove_subject" class="d-inline-flex gap-1 align-items-center justify-content-center btn btn-danger p-2 pe-3 proccess_<?php echo esc_attr($list);?>" data-class="<?php echo esc_attr($class);?>" data-subject="<?php echo esc_attr($target);?>"><i class="dashicons dashicons-trash"></i> Delete</button>
														</div>
													</div>
												</div>
											</form>
										</p>

										<button class="notice-dismiss"></button>
									</div>
								</div>
								<?php
							}
							elseif (isset($_POST['edit_class'])) {
								?>
								<div class="sticky_msg">
									<div class="notice notice-success is-dismissible add_results">
										<p>
											<form action="" method="post" class="py-0 px-3 add_results bg-transparent shadow-none">
												<div class="d-block fw-bold mb-3"><?php echo __('Edit', 'educare') .' '. esc_html__($class, 'educare');?></div>

												<?php
												$update_class_nonce = wp_create_nonce( 'update_class' );
												echo '<input type="hidden" name="update_class_nonce" value="'.esc_attr($update_class_nonce).'">';
												echo '<input type="hidden" name="educare_process_'.esc_attr($list).'">';
												echo '<input type="hidden" name="old_class" value="'.esc_attr($class).'">';
												?>

												<div class="mb-3 row">
													<label for="editFieldClass" class="col-sm-2 col-form-label"><?php echo esc_html(ucfirst($list));?> Name</label>
													<div class="col-sm-10">
														<input id="editFieldClass" type="text" name="class" class="fields" value="<?php echo esc_attr($class);?>" placeholder="<?php echo esc_attr($class);?>" pattern="[A-Za-z0-9 ]+" title="<?php _e('Only characters, numbers and space allowed. (A-Za-z0-9)', 'educare')?>">
													</div>
												</div>

												<?php educare_update_anywhere_options();?>

												<div class="row">
													<div class="col-sm-2"></div>
													<div class="col-sm-10">
														<button type="submit" name="update_class" id="educare_results_btn" class="d-inline-flex gap-1 align-items-center justify-content-center btn btn-success p-2 pe-3 proccess_<?php echo esc_attr($list);?>"><i class="dashicons dashicons-update"></i> <?php _e('Update', 'educare')?></button>
													</div>
												</div>

											</form>
										</p>
										
										<button class="notice-dismiss"></button>
									</div>
								</div>
								<?php
							}

							elseif (isset($_POST['remove_subject'])) {
								$msg = '<div class="sticky_msg"><div class="notice notice-success is-dismissible"><p>'.
								sprintf(
									__(
										'Successfully removed <b>%1$s</b> from the %2$s list.',
										'educare'
									),
									esc_html__($target, 'educare'),
									esc_html__($list, 'educare')
								)
								.'</p><button class="notice-dismiss"></button></div></div>';
								
								// convert indexed array to associative array. So, we can essily select your specific data/value by specific key. Otherwise, it's hard to detect specific data with indexed key.
								$subject_list = array_combine($subject_list, $subject_list);
								// remove data by specific key
								unset($subject_list[$target]);
								$subject_list = array_values($subject_list);

								$data[$class] = $subject_list;
							} else {
								echo '<div class="notice notice-error is-dismissible"> <p><b class="error">'.__('Somethink went wrong!</b> Maybe its a bug. Soon, we (Educare) will fix these issues after the next update', 'educare').'</p><button class="notice-dismiss"></button></div>';
							}
						} else {
							if (isset($_POST['add_subject'])) {
								//  if add sobject
								$subject_list = array_unique($subject_list);
								array_push($subject_list, $target);
								$data[$class] = $subject_list;
								
								$msg = '<div class="sticky_msg"><div class="notice notice-success is-dismissible"> <p>'.
								sprintf(
									__(
										'Successfully Added <b>%1$s</b> at the %2$s list<br>%3$s: <b>%4$s</b><br>Total: <b>%5$s</b> %2$s added.',
										'educare'
									),
									esc_html__($target, 'educare'),
									esc_html__($msgs, 'educare'),
									esc_html__($list, 'educare'),
									esc_html__($class, 'educare'),
									esc_html(count($subject_list))
								)
								.'</p><button class="notice-dismiss"></button>
								</div></div>';
							}

							if (isset($_POST['update_subject'])) {
								$old_sub = sanitize_text_field( $_POST['old_subject'] );
								$old_subject = strtolower($old_sub);

								$old_class = sanitize_text_field( $_POST['old_class'] );
								$class = $class;

								// echo "Old Subject: $old_subject <br>Old Class: $old_class <br>";
								// echo "New Subject: $target <br>New Class: $class <br>";
								$get_key = array_search($old_sub, $data[$old_class]);

								if (strtolower($target) != $old_subject and $class == $old_class) {
									$data[$old_class][$get_key] = $target;

									$msg = '<div class="sticky_msg"><div class="notice notice-success is-dismissible"> <p>'.
									sprintf(
										__(
											'Successfully change %1$s <b class="error">%2$s</b> to <b class="success">%3$s</b>',
											'educare'
										),
										esc_html__($msgs, 'educare'),
										esc_html__($old_sub, 'educare'),
										esc_html__($target, 'educare')
									)
									.'</p><button class="notice-dismiss"></button></div></div>';

									educare_update_anywhere(ucfirst($msgs), $old_sub, $target);
								}
								elseif (strtolower($target) == $old_subject and $class != $old_class) {
									unset($data[$old_class][$get_key]);
									array_values($data[$old_class]);
									array_push($data[$class], $target);
									
									$msg = '<div class="sticky_msg"><div class="notice notice-success is-dismissible"> <p>'.
									sprintf(
										__(
											'Successfully change %1$s <b class="error">%2$s</b> to <b class="success">%3$s</b>',
											'educare'
										),
										esc_html__($list, 'educare'),
										esc_html__($old_class, 'educare'),
										esc_html__($class, 'educare')
									)
									.'</p><button class="notice-dismiss"></button></div></div>';
								} else {
									// Add data
									$data[$old_class][$get_key] = $target;
									// Remove data
									unset($data[$old_class][$get_key]);
									array_values($data[$old_class]);
									array_push($data[$class], $target);

									$msg = '<div class="sticky_msg"><div class="notice notice-success is-dismissible"><p>'.
									sprintf(
										__(
											'Successfully update %1$s <b class="error">%2$s</b> to <b class="success">%3$s</b>. also changed %4$s <b class="error">%5$s</b> to <b class="success">%6$s</b>.',
											'educare'
										),
										esc_html__($msgs, 'educare'),
										esc_html__($old_sub, 'educare'),
										esc_html__($target, 'educare'),
										esc_html__($list, 'educare'),
										esc_html__($old_class, 'educare'),
										esc_html__($class, 'educare')
									)
									.'</p><button class="notice-dismiss"></button></div></div>';

									educare_update_anywhere(ucfirst($msgs), $old_sub, $target);
								}
							}

							if (isset($_POST['update_class'])) {
								$old_class = sanitize_text_field( $_POST['old_class'] );
								$get_key = array_search($data[$old_class], $data);

								if(strtolower($old_class) == strtolower($target)) {
									$msg = educare_show_msg(__('No changes were found in this request.', 'educare'), 'info');
								} else {
									if (key_exists(strtolower($target), array_change_key_case($data))) {
										echo '<div class="sticky_msg"><div class="notice notice-error is-dismissible"> <p>'.
										sprintf(
											__(
												'<b>%1$s</b> is already exist in %2$s list',
												'educare'
											),
											esc_html__($target, 'educare'),
											esc_html__($list, 'educare')
										)
										.'</p><button class="notice-dismiss"></button></div></div>';
									} else {
										if ($target !== $old_class) {
											$data = educare_replace_key($data, $old_class, $target);
											$msg = '<div class="sticky_msg"><div class="notice notice-success is-dismissible"> <p>'.
											sprintf(
												__(
													'Successfully change %1$s <b class="error">%2$s</b> to <b class="success">%3$s</b>',
													'educare'
												),
												esc_html__($list, 'educare'),
												esc_html__($old_class, 'educare'),
												esc_html__($target, 'educare')
											)
											.'</p><button class="notice-dismiss"></button></div></div>';

											educare_update_anywhere($list, $old_class, $target);
										}
									}
								}
							}

							if (isset($_POST['remove_class'])) {
								$class = sanitize_text_field( $_POST['class'] );
								
								unset($data[$class]);
								
								$msg = '<div class="sticky_msg"><div class="notice notice-success is-dismissible"><p>'.
								sprintf(
									__(
										'Successfully removed <b>%1$s</b> from the %2$s list.',
										'educare'
									),
									esc_html__($class, 'educare'),
									esc_html__($list, 'educare')
								)
								.'</p><button class="notice-dismiss"></button></div></div>';
							}
							
						} // unique data

						if ($process) {
							$wpdb->update(
								$table, 			//table
								array( 				// data
									"data" => json_encode($data)
								),
							
								array( 				//where
									'ID' => $id
								)
							);
							
						}
						
						echo $msg;
					}

				} else {

					if (isset($_POST['add_class'])) {
						if (key_exists(strtolower($class), array_change_key_case($data))) {
							echo '<div class="sticky_msg"><div class="notice notice-error is-dismissible"> <p>'.
							sprintf(
								__(
									'<b>%1$s</b> is already exist in %2$s list',
									'educare'
								),
								esc_html__($class, 'educare'),
								esc_html__($list, 'educare')
							)
							.'</p><button class="notice-dismiss"></button></div></div>';
						} else {
							if (empty($class)) {
								echo '<div class="sticky_msg"><div class="notice notice-error is-dismissible"> <p>'.sprintf(
									__(
										'You must fill the form for add the <b>%s</b>.',
										'educare'
									),
									esc_html__($list, 'educare')
								).'</p><button class="notice-dismiss"></button></div></div>';
							} else {
								$data[$class] = array();
								
								$wpdb->update(
									$table, //table
									array( // data
										"data" => json_encode($data)
									),
								
									array( //where
										'ID' => $id
									)
								);

								echo '<div class="sticky_msg"><div class="notice notice-success is-dismissible"> <p>'.
								sprintf(
									__(
										'Successfully Added <b>%1$s</b> at the %2$s list.',
										'educare'
									),
									esc_html__($target, 'educare'),
									esc_html__($list, 'educare')
								)
								.'</p><button class="notice-dismiss"></button>
								</div></div>';
							}
						}
					} else {
						echo '<div class="sticky_msg"><div class="notice notice-error is-dismissible"><p>';

						if ($data) {
							echo sprintf(
								__(
									'Sorry, <b>%1$s</b> not exist<b></b> at the %2$s list<br>If you need to add subject in this (%1$s) %2$s. First, You need to add this (%1$s) in the %2$s list. Then, You would be allowed to add some subject.',
									'educare'
								),
								esc_html__($class, 'educare'),
								esc_html__($list, 'educare')
							);
						} else {
							echo sprintf(
								__(
									'Sorry, you don\'t have added any %1$s yet. For adding a subject, you need to add a %1$s first. Then, you can add a subject for this %1$s.',
									'educare'
								),
								esc_html__($list, 'educare')
							);
						}
							
						echo '</p><button class="notice-dismiss"></button></div></div>';
					}
				}
			}
		} else {
			echo educare_guide_for('db_error', false);
		}

		// Add newly adde class or group in options without realoding the page
		if (isset($_POST['educare_process_Class']) or isset($_POST['educare_process_Group'])) {
			$class = sanitize_text_field( $_POST['class'] );

			if (isset($_POST['educare_process_Group'])) {
				$data_for = 'Group';
			} elseif (isset($_POST['educare_process_Rattings'])) {
				$data_for = 'Rattings';
			} else {
				$data_for = 'Class';
			}
			
			?>
			<script>
				jQuery(document).ready(function($) {
					$('#add_<?php echo esc_js($data_for);?>').html('<?php esc_js(educare_get_options($data_for, $class))?>');
				});
			</script>
			<?php
		}
	}
}



/**
 * Displays the setting options for subjects or classes in the Educare theme or plugin.
 *
 * The `educare_setting_subject` function is responsible for displaying the setting options for subjects or classes in the Educare theme or plugin.
 * The function takes two parameters: `$list` and `$form`. The `$list` parameter specifies whether it's for subjects or classes, while the `$form` parameter
 * is optional and determines whether the form for adding subjects or classes should be displayed.
 *
 * The function retrieves data from the database for the specified `$list` (subjects or classes) using the `$wpdb` global object.
 * If `$form` is not specified (or set to `null`), the function displays the existing subjects or classes in collapsible sections, allowing the user to edit or remove them.
 * If `$form` is set to `true`, the function displays a form that allows the user to add a new subject or class.
 *
 * If the `$form` parameter is set to `true`, the function outputs the form for adding subjects or classes. The form includes text inputs for subject or class names,
 * and a select dropdown to specify the subject's associated class (if applicable). The user can then submit the form to add a new subject or class to the database.
 *
 * If `$form` is `null` or not specified, the function outputs the existing subjects or classes in collapsible sections. Each section displays the subjects or classes associated
 * with a specific class (for subjects) or lists the available classes (for classes). The collapsible sections allow the user to view and edit subjects or classes,
 * and options to edit or remove them are provided.
 *
 * The function makes use of various HTML elements, CSS classes, and JavaScript to create the collapsible sections and handle user interactions.
 *
 * @param string $list The type of setting to display, either "subjects" or "classes".
 * @param bool|null $form Optional. Specifies whether to display the form for adding a new subject or class. Default is null.
 *
 * @return void The function outputs the setting options for subjects or classes in the Educare theme or plugin.
 *
 * @since 1.4.0
 * @last-update 1.4.0
 * 
 * @example
 * Display existing subjects in the Educare theme or plugin without the form for adding a new subject.
 * educare_setting_subject('subjects');
 *
 * Display existing classes in the Educare theme or plugin without the form for adding a new class.
 * educare_setting_subject('classes');
 *
 * Display the form for adding a new subject in the Educare theme or plugin.
 * educare_setting_subject('subjects', true);
 *
 * Display the form for adding a new class in the Educare theme or plugin.
 * educare_setting_subject('classes', true);
 */
 function educare_setting_subject($list, $form = null) {
	global $wpdb;

	$table = $wpdb->prefix."educare_settings";
	// Prepare the query with placeholders
	$query = $wpdb->prepare( "SELECT * FROM $table WHERE list = %s", $list );
	// Execute the prepared query and get the results
	$search = $wpdb->get_results( $query );

	if ($search) {
		foreach ( $search as $print ) {
			$data = $print->data;
			$id = $print->id;
		}
		
		$data = json_decode($data, true);
	} else {
		$data = array();
	}

	if ($list == 'Rattings') {
		$msgs = 'remarks';
	} else {
		$msgs = 'subject';
	}
	
	if (!$form) {
		$count = 1;
		// Checked first class content (Subjects)
		$first = array_key_first($data);

		if ($data) {
			echo '<div class="collapses">';
			foreach ($data as $class => $val) {
				?>
				<div class="collapse">
					<input class="head" type="radio" name="<?php echo esc_attr($list);?>" data="<?php echo esc_attr($class);?>" id="<?php echo esc_attr( $list . '_' . $class );?>" <?php if ($class == $first or isset($_POST['class']) and $_POST['class'] == $class) {echo 'checked';}?>>
					<label class="collapse-label" for="<?php echo esc_attr( $list . '_' . $class );?>">
						<?php echo esc_html__( $count++ , 'educare') . '. ' . esc_html__( $class, 'educare' );?>
						<span>
							<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">

								<input type="hidden" name="educare_process_<?php echo esc_attr($list);?>">
								<input type="hidden" name="class" value="<?php echo esc_attr( $class );?>">
								<input type="submit" class="proccess_<?php echo esc_attr($list);?>" name="edit_class" value="&#xf464">
								<input type="submit" class="proccess_<?php echo esc_attr($list);?>" name="remove_class" value="&#xf182">

								<?php
								$edit_class_nonce = wp_create_nonce( 'edit_class' );
								$remove_class_nonce = wp_create_nonce( 'remove_class' );

								echo '<input type="hidden" name="edit_class_nonce" value="'.esc_attr($edit_class_nonce).'">';
								echo '<input type="hidden" name="remove_class_nonce" value="'.esc_attr($remove_class_nonce).'">';
								?>

							</form>
						</span>
					</label>

					<div class="collapse-content bg-white">
						<div class="table-responsivex">
							<table class='table overflow-autox align-middle data-list bg-white list'>
								<thead>
									<tr>
										<th><?php _e('No.', 'educare');?></th>
										<th width='100%'><?php echo esc_html__(ucfirst($msgs), 'educare');?></th>
										<th><?php _e('Edit', 'educare');?></th>
										<th><?php _e('Delete', 'educare');?></th>
									</tr>
								</thead>

								<tbody>
								<?php
								if ($val) {
									$no = 1;
									
									foreach ($val as $subject) {
										?>
										<tr>
											<td><?php echo esc_html__($no++, 'educare');?></td>
											<td><?php echo esc_html__($subject, 'educare');?></td>
											<td colspan='2'>
												<form class="d-flex gap-2 my-1" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
													<input type="hidden" name="educare_process_<?php echo esc_attr($list);?>">

													<input type="hidden" name="class" value="<?php echo esc_attr($class);?>"/>

													<input type="hidden" name="subject" value="<?php echo esc_attr($subject);?>"/>

													<div class="d-flex gap-1">
														<button type="submit" name="edit_subject" class="d-inline-flex gap-1 align-items-center justify-content-center btn btn-outline-success proccess_<?php echo esc_attr($list);?>"><i class="dashicons dashicons-edit"></i></button>

														<button type="submit" name="<?php echo esc_attr("remove_subject");?>" class="d-inline-flex gap-1 align-items-center justify-content-center btn btn-outline-danger proccess_<?php echo esc_attr($list);?>"><i class="dashicons dashicons-trash"></i></button>
													</div>

													<?php
													$edit_subject_nonce = wp_create_nonce( 'edit_subject' );
													$remove_subject_nonce = wp_create_nonce( 'remove_subject' );
													
													echo '<input type="hidden" name="edit_subject_nonce" value="'.esc_attr($edit_subject_nonce).'">';
													echo '<input type="hidden" name="remove_subject_nonce" value="'.esc_attr($remove_subject_nonce).'">';
													?>
														
												</form>
											</td>
										</tr>
										<?php
									}

								} else {

									echo '<tr><td colspan="4"><div class="notice notice-error is-dismissible"><p>
										'.sprintf(
											__(
												'Currently, you have not added any %1$s to this %2$s. Please add a %1$s for this %2$s using the form given below.',
												'educare'
											),
											esc_html__($msgs, 'educare'),
											esc_html__($list, 'educare')
										).'
									</p></div></td></tr>';
								}
								?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<?php
			}
			echo '</div>';
		} else {
			echo '<div class="notice notice-error is-dismissible"><p>
			'.sprintf(
				__(
					'Currently, you have not added any %1$s. Please add a %1$s using the form given below.',
					'educare'
				),
				esc_html__($list, 'educare')
			).'
			</p></div>';
		}
		
	}
	
	if ($form) {
		?>
		<div class="educare_tabs form_tab">
			<div class="tab_head">
				<button class="tablink educare_button" data="<?php echo esc_attr($list);?>_subject"><?php echo sprintf(__('Add %s', 'educare'), esc_html__(ucfirst($msgs), 'educare'));?></button>
				<button class="tablink" data="<?php echo esc_attr($list);?>_class"><?php echo sprintf(__('Add %s', 'educare'), esc_html__(ucfirst($list), 'educare'));?></button>
			</div>
			
			<div id="<?php echo esc_attr($list);?>_subject" class="section_name">
				<form class="add_results" action="" method="post" id="add_subject">
					<div class="content pt-4">
						<?php
						$nonce = wp_create_nonce( 'add_subject' );
						echo '<input type="hidden" name="add_subject_nonce" value="'.esc_attr($nonce).'">';
						echo '<input type="hidden" name="educare_process_'.esc_attr($list).'">';
						?>

						<div class="mb-3 row">
							<label for="fieldSubject" class="col-sm-2 col-form-label"><?php echo esc_html__(ucfirst($msgs), 'educare');?></label>
							<div class="col-sm-10">
								<input id="fieldSubject" type="text" name="subject" class="form-control-plaintext" placeholder="<?php echo esc_html__(ucfirst($msgs), 'educare');?>" pattern="[A-Za-z0-9 ]+" title="<?php _e('Only characters, numbers and space allowed. (A-Za-z0-9)', 'educare')?>">
							</div>
						</div>
						
						<div class="mb-3 row">
							<label for="add_<?php echo esc_attr($list);?>" class="col-sm-2 col-form-label"><?php esc_html_e($list, 'educare')?></label>
							<div class="col-sm-10">
								<select class="form-select" id='add_<?php echo esc_attr($list);?>' name='class'>
									<?php
									foreach ($data as $key => $value) {
										echo "<option value='".esc_attr($key)."'>".esc_html__($key, 'educare')."</option>";
									}
									?>
								</select>
							</div>
						</div>

						<div class="mb-3 row">
							<div class="col-sm-2 col-form-label"></div>
							<div class="col-sm-10">
								<button id="educare_results_btn" class="educare_button m-0 proccess_<?php echo esc_attr($list);?>" name="add_subject" type="submit"><i class="dashicons dashicons-plus-alt"></i> <?php _e('Add', 'educare')?></button>
							</div>
						</div>
					</div>
				</form>
			</div>

			<div id="<?php echo esc_attr($list);?>_class" class="section_name" style="display:none">
				<form class="add_results" action="" method="post" id="add_subject">
					<div class="content pt-4">
						<?php
						$nonce = wp_create_nonce( 'add_class' );
						echo '<input type="hidden" name="add_class_nonce" value="'.esc_attr($nonce).'">';
						echo '<input type="hidden" name="educare_process_'.esc_attr($list).'">';
						?>

						<div class="mb-3 row">
							<label for="fieldClass" class="col-sm-2 col-form-label"><?php esc_html_e($list, 'educare')?></label>
							<div class="col-sm-10">
								<input id="fieldClass" type="text" name="class" class="form-control-plaintext" placeholder="<?php esc_html_e($list, 'educare')?>" pattern="[A-Za-z0-9 ]+" title="<?php _e('Only characters, numbers and space allowed. (A-Za-z0-9)', 'educare')?>">
							</div>
						</div>

						<div class="mb-3 row">
							<div class="col-sm-2 col-form-label"></div>
							<div class="col-sm-10">
								<button id="educare_results_btn" class="educare_button m-0 proccess_<?php echo esc_attr($list);?>" name="add_class" type="submit"><i class="dashicons dashicons-plus-alt"></i> <?php _e('Add', 'educare')?></button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
		<?php
	}
}



function educare_removeElementsByKeyValue(array &$array, $searchValue, $searchKey = null) {
	$removed_array = array();

	if ($searchKey) {
		foreach ($array as $key => $item) {
			if (isset($item[$searchKey]) && $item[$searchKey] === $searchValue) {
				unset($array[$key]);
				$removed_array = array_values($array);
			}
		}
	} else {
		$remove_key = array_search($searchValue, $array);
		if ($remove_key !== false) {
			// Unset or remove the key from the array
			unset($array[$remove_key]);
			$removed_array = array_values($array);
		}
	}

	return $removed_array;
}



/**
 * Display Content
 * Usage example: educare_content('Exam');
 * 
 * @since 1.0.0
 * @last-update 1.0.0
 * 
 * @param string $list	Exam, Year, Extra field
 * @param bool $form return form for add data
 * @return void|HTML
 * 
 * Display Content - Subject, Exam, Class, Year Extra field...
 */
function educare_content($list, $form = null) {
	$advance = array(
		'School',
		'Term',
		'Staff_position'
	);

	if (in_array($list, $advance)) {
	} else {
		global $wpdb;
		$table = $wpdb->prefix."educare_settings";
		// remove all _ characters from the list (normalize the $list)
		$List = str_replace('_', ' ', $list);
		
		// Prepare the query with placeholders
		$query = $wpdb->prepare( "SELECT * FROM $table WHERE list = %s", $list );
		// Execute the prepared query and get the results
		$search = $wpdb->get_results( $query );
		
		if ($search) {
			foreach ( $search as $print ) {
				$target = $print->data;
				$target = json_decode($target);
			}
			
			if ($target) {
				ob_start();
				$count = 0;
				for ($i = 0; $i < count($target); $i++) {
					$Content = $target[$i];
					$type_th = '';
					$type_td = '';
					
					if ($list == 'Extra_field') {
						$get_type = $Content->type;
						$Content = $Content->title;
						
						$type_th = '<th>'.__('Type', 'educare').'</th>';
						$type_td = "<td><span class='type ".esc_attr($get_type)."'></span></td>";
					}
					
					if ($list) {
					?>
						<tr>
							<td><?php esc_html_e(++$count, 'educare');?></td>
							<td><b><?php esc_html_e($Content, 'educare');?></b></td>
							<?php echo wp_kses_post($type_td);?>
							<td colspan='2'>
								<form class="d-flex gap-2 my-1" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
									<?php
									$update_nonce = wp_create_nonce( 'educare_edit_'.esc_attr($list) );
									$remove_nonce = wp_create_nonce( 'remove_'.esc_attr($list) );
									
									echo '<input type="hidden" name="educare_edit_'.esc_attr($list).'_nonce" value="'.esc_attr($update_nonce).'">';
									echo '<input type="hidden" name="remove_'.esc_attr($list).'_nonce" value="'.esc_attr($remove_nonce).'">';
									?>

									<input type="hidden" name="remove" value="<?php echo esc_attr($Content);?>"/>
									<input type="hidden" name="<?php echo esc_attr($list);?>" value="<?php echo esc_attr($Content);?>"/>

									<div class="d-flex gap-1">
										<button type="submit" name="educare_edit_<?php echo esc_attr($list);?>" class="d-inline-flex gap-1 align-items-center justify-content-center btn btn-outline-success edit<?php echo esc_attr(str_replace('_', '', $list));?>"><i class="dashicons dashicons-edit"></i></button>

										<button type="submit" name="<?php echo esc_attr("remove_$list");?>" class="d-inline-flex gap-1 align-items-center justify-content-center btn btn-outline-danger remove<?php echo esc_attr(str_replace('_', '', $list));?>"><i class="dashicons dashicons-trash"></i></button>
									</div>
								</form>
							</td>
						</tr>
					<?php
					
					} // end if ($list)
				}
				
				$target = ob_get_clean();
			}
			
			if (!$form) {
				if (!empty($target)) {
					?>
					<table class='table align-middle data-list bg-white list'>
						<thead>
							<tr>
								<th><?php _e('No.', 'educare');?></th>
								<th width='100%'><?php echo esc_html__($List, 'educare');?></th>
								<?php echo wp_kses_post($type_th);?>
								<th><?php _e('Edit', 'educare');?></th>
								<th><?php _e('Delete', 'educare');?></th>
							</tr>
						</thead>
						<tbody>
							<?php echo wp_check_invalid_utf8(str_replace('_', ' ', $target));?>
						</tbody>
					</table>
					<?php
				} else {
					echo '<div class="notice notice-error is-dismissible"><p>
					'.sprintf(
						__(
							'Currently, you have not added any %1$s. Please add a %1$s using the form given below.',
							'educare'
						),
						esc_html__($List, 'educare')
					).'
					</p></div>';
				}
			}

			if ($form) {
				// Create nonce for this form
				$nonce = wp_create_nonce( 'educare_add_'.esc_attr($list) );

				if ($list == 'Extra_field') {
					?>
					<form class="add_results" action="" method="post">
						<div class="content">
							<?php
							// Print nonce value
							echo '<input type="hidden" name="educare_add_'.esc_attr($list).'_nonce" value="'.esc_attr($nonce).'">';
							echo '<input type="hidden" name="'.esc_attr($list).'">';
							?>

							<div class="mb-3 row">
								<label for="fieldName" class="col-sm-2 col-form-label"><?php _e('Field Name', 'educare');?></label>
								<div class="col-sm-10">
									<input id="fieldName" type="text" name="field" class="fields" placeholder="<?php _e('Field Name', 'educare');?>" pattern="[A-Za-z0-9 ]+" title="<?php _e('Only characters, numbers and space allowed. (A-Za-z0-9)', 'educare')?>">
								</div>
							</div>

							<div class="mb-3 row">
								<label for="fieldType" class="col-sm-2 col-form-label"><?php _e('Select type', 'educare');?></label>
								<div class="col-sm-10">
									<select id="fieldType" class="m-0" name="type">
										<option value="text"><?php _e('Text', 'educare');?></option>
										<option value="number"><?php _e('Number', 'educare');?></option>
										<option value="date"><?php _e('Date', 'educare');?></option>
										<option value="email"><?php _e('Email', 'educare');?></option>
									<select>
								</div>
							</div>

							<div class="mb-3 row">
								<label for="fieldFor" class="col-sm-2 col-form-label"><?php _e('Field For', 'educare');?></label>
								<div class="col-sm-10">
									<select id="fieldFor" class="m-0" name="for[]" multiple>
										<option value="students"><?php _e('Students', 'educare');?></option>
										<option value="teachers"><?php _e('Teachers/Staff', 'educare');?></option>
										<option value="results"><?php _e('Results', 'educare');?></option>
									<select>
								</div>
							</div>

							<div class="mb-3 row">
								<div class="col-sm-2 col-form-label"></div>
								<div class="col-sm-10">
									<button id="educare_add_<?php echo esc_attr($list);?>" class="educare_button m-0" name="educare_add_<?php echo esc_attr($list);?>" type="submit" onClick="<?php echo esc_js('add(this.form)');?>"><i class="dashicons dashicons-plus-alt"></i> <?php _e('Add', 'educare');?></button>
								</div>
							</div>
						</div>
					</form>
					<br>
					<?php
					
				} else {
					?>
					<form class="add_results" action="" method="post">
						<div class="content">
							<?php
							// Print nonce value
							echo '<input type="hidden" name="educare_add_'.esc_attr($list).'_nonce" value="'.esc_attr($nonce).'">';
							?>

							<div class="mb-3 row">
								<label for="field<?php echo esc_attr($list);?>" class="col-sm-2 col-form-label"><?php esc_html_e($List, 'educare');?></label>
								<div class="col-sm-10">
									<input id="field<?php echo esc_attr($list);?>" type="text" name="<?php echo esc_attr($list);?>" class="form-control-plaintext" placeholder="<?php echo esc_attr($List);?> name" pattern="[A-Za-z0-9 ]+" title="<?php _e('Only characters, numbers and space allowed. (A-Za-z0-9)', 'educare')?>">
								</div>
							</div>

							<div class="mb-3 row">
								<div class="col-sm-2"></div>
								<div class="col-sm-10">
									<button id="educare_add_<?php echo esc_attr($list);?>" class="educare_button m-0" name="educare_add_<?php echo esc_attr($list);?>" type="submit"><i class="dashicons dashicons-plus-alt"></i> <?php _e('Add', 'educare');?></button>
								</div>
							</div>
						</div>
					</form>
					<br>
					<?php
				}
			}
		} else {
			// database error
			// echo educare_guide_for('db_error', false);
		}
	}
}



/** 
 * Pack all in one
 * 
 * @since 1.4.0
 * @last-update 1.4.0
 * 
 * @param string $list		for specific data - Class or Group
 * @return mixed
 */
function educare_get_all_content($list) {
	// content list
	ob_start();
	educare_content($list);
	$data = ob_get_clean();

	echo '<div id="msg_for_'.esc_attr($list).'">'.wp_check_invalid_utf8(str_replace('_', ' ', $data)).'</div>';
	
	// Content forms
	educare_content($list, true);
}



/** 
 * Responce all content
 * 
 * Ajax respnce for management menu/page
 * 
 * @since 1.4.0
 * @last-update 1.4.0
 * 
 * @return mixed
 */
 function educare_process_content() {
	$action_for = sanitize_text_field($_POST['action_for']);

	if (isset($_POST['active_menu'])) {
		$active_menu = sanitize_text_field($_POST['active_menu']);
	} else {
		$active_menu = '';
	}

	// Remove the backslash
	$_POST['form_data'] = stripslashes($_POST['form_data']);
	// parses query strings and sets the parsed values into the $_POST array.
	wp_parse_str($_POST['form_data'], $_POST);
	$_POST[$action_for] = $action_for;
	$_POST['active_menu'] = $active_menu;

	// Verify the nonce to ensure the request originated from the expected source
	educare_verify_nonce($action_for, $action_for.'_nonce');

	if (isset($_POST['educare_process_Class'])) {
		educare_process_class('Class');
		educare_setting_subject('Class');
	}
	elseif (isset($_POST['educare_process_Group'])) {
		educare_process_class('Group');
		educare_setting_subject('Group');
	}
	elseif (isset($_POST['educare_process_Rattings'])) {
		educare_process_class('Rattings');
		educare_setting_subject('Rattings');
	}
	elseif (isset($_POST['educare_update_settings_status']) or isset($_POST['educare_reset_default_settings']) or isset($_POST['educare_reset_everything'])) {
		echo '<h1>'.__('Settings', 'educare').'</h1>';

		// for update settings status
		if (!empty($_POST)) {
			global $wpdb;
			$table = $wpdb->prefix."educare_settings";
			// Prepare the query with placeholders
			$query = $wpdb->prepare( "SELECT * FROM $table WHERE list = %s", 'Settings' );
			// Execute the prepared query and get the results
			$search = $wpdb->get_row( $query );
			
			if ($search) {
				// Get active template name securely
				$active_template = isset($_POST['results_card_template']) ? sanitize_text_field($_POST['results_card_template']) : '';
				// Getting current template field
				$template_fields = educare_get_template_data('fields', true, $active_template);

				$request = array(
					'data' => $search,
					'request_data' => $_POST,
					'card_template' => array(
						'active_template' => $active_template,
						'template_fields' => $template_fields
					)
				);
				
				function educare_save_settings($request) {
					$id = sanitize_text_field($request['data']->id);
					$data = educare_decrypt_data($request['data']->data);
					
					$request_data = $request['request_data'];
					$active_template = sanitize_text_field($request['card_template']['active_template']);
					$template_fields = $request['card_template']['template_fields'];
					$advance_fields = true;

					foreach ($request_data as $target => $value) {
						$status = 'unchecked';

						if (property_exists($data, $target)) {
							$status = $data->$target;
						}

						if ($target != 'display' or $target != 'results_card') {
							$update_data = sanitize_text_field($value);
						}

						if ($target == 'display') {
							$update_data = json_decode(json_encode($value));
						}

						if ($target == 'results_card') {
							// Escape all data and Convert array to object
							$data->banner = json_decode(json_encode($request_data['banner']));
							$update_data = json_decode(json_encode($value));

							if ($advance_fields) {
								if ($data->banner->logo1) {
									$data->banner->logo1 = 0;
								}
								if ($data->banner->logo2) {
									$data->banner->logo2 = 0;
								}

								if ($data->banner->title) {
									$data->banner->title = 'Institutions Name Or Title';
								}
								if ($data->banner->subtitle1) {
									$data->banner->subtitle1 = 'Rangpur, Dhaka, Bangladesh';
								}
								if ($data->banner->subtitle2) {
									$data->banner->subtitle2 = 'Founded in 2025';
								}
							}
							
							$data->$target->details = $update_data->details;
							$data->$target->grade_sheet = $update_data->grade_sheet;

							if (!property_exists($data->$target->template, $active_template)) {
								$data->$target->template->$active_template = json_decode(json_encode($template_fields));
							}

							foreach ($data->$target->template as $template_name => $template_data) {
								if (isset($update_data->template)) {
									if (property_exists($update_data->template, $template_name)) {
										$data->$target->template->$template_name = $update_data->template->$template_name;
									}
								}
							}

						} else {
							$data->$target = $update_data;
						}

						if ($target == 'template_settings') {
							$template_settings = json_decode(json_encode($value));
							$data->template_settings = $template_settings;
						}
					}

					$process_data = array(
						'id' => $id,
						'data' => $data
					);

					return $process_data;
				}

				$process_data = educare_save_settings($request);

				$id = sanitize_text_field($process_data['id']);
				// $data = json_encode($process_data['data']);
				$data = educare_encrypt_data($process_data['data']);

				if ($id) {
					// now update desired data
					$wpdb->update(
						$table, 				//table
						array( 					// data - we need to encode our data for store array/object into database
							"data" => $data
						),
						array( 					//where
							'ID' => $id
						)
					);
				}
				
			}
		}

		echo educare_process_settings('Settings');
		educare_settings_form();
	} elseif (isset($_POST['educare_import_results'])) {
		if (function_exists('educare_import_data')) {
			educare_import_data();
		} else {
			educare_default_import_data($action_for);
		}

	} else {
		echo educare_process_settings('Exam');
		echo educare_process_settings('Year');
		echo educare_process_settings('Extra_field');

		if (isset($_POST['Exam'])) {
			educare_content('Exam');
		}
		elseif (isset($_POST['Year'])) {
			educare_content('Year');
		} else {
			educare_content('Extra_field');
		}
	}

	die;
}

add_action('wp_ajax_educare_process_content', 'educare_process_content');



/** 
 * Students and Results page tab management
 * 
 * Show element for add, update, import - results or students
 * 
 * @since 1.4.0
 * @last-update 1.4.0
 * 
 * @param string $roles		for specific data - Students or Results
 * @return mixed
 */
function educare_data_management($roles = null, $front = false) {
	echo '<div id="educare-form">';
	// Escape roles
	$roles = sanitize_text_field( $roles );
	$display_roles = $roles;

	if ($display_roles == 'teachers') {
		$display_roles = 'Staff';
	}

	// get the slug of the page we want to display 
	// then we include the page
	// Add WP page value for control Ajax request
	$_GET['page'] = 'educare-all-'.esc_attr($roles);

	// Display data for specific page || tabe
	if (isset($_GET['add-data'])) {
		echo educare_crud_data_new();
		educare_get_forms($roles);
	} elseif (isset($_GET['update-data'])) {
		echo educare_crud_data_new();
		if(!isset($_POST['default']['id'])) {
			educare_get_search_form($roles);
		}
	} elseif (isset($_GET['import-data'])) {
		if (function_exists('educare_import_data')) {
			educare_import_data($roles);
		} else {
			educare_default_import_data($roles);
		}
	} else {
		educare_display_data($roles, null, $front);
	}

	echo '</div>';
}



/** 
 * Ajax responce for students and results page
 * 
 * @since 1.4.0
 * @last-update 1.4.0
 * 
 * @return mixed
 */
function educare_process_data() {
	// Check if the current user has the access this request as 'manage_options' capability (typically administrators).
	educare_check_access();

	$action_for = sanitize_text_field($_GET['action_for']);
	// $currenTab = sanitize_text_field($_POST['currenTab']);
	wp_parse_str($_GET['form_data'], $_GET);

	educare_data_management($action_for);

	die;
}

add_action('wp_ajax_educare_process_data', 'educare_process_data');



/** 
 * Get students
 * Get student by specific class, year, subject
 * 
 * @since 1.4.0
 * @last-update 1.4.0
 * 
 * @param string $Class 		for spicific class students
 * @param string|int $Year	for specific year students
 * @return mixed
 */
function educare_get_students_list($Class = null, $Year = null) {
	global $wpdb;
	$educare_students = $wpdb->prefix."educare_students";
	$educare_marks = $wpdb->prefix."educare_marks";

	if (isset($_POST['students_list'])) {
		// Requred fields to add marks
		$requred_fields = array (
			'Class',
			'Exam',
			'Year'
		);

		// Others fields to related with marks, for sort, search, group or subject wise student.
		$default_fields = array (
			'Group',
			'Subject',
			'order_by',
			'order'
		);

		// Merge all in one
		$default_fields = array_merge($requred_fields, $default_fields);
		$default_fields = educare_combine_fields($default_fields, '', $_POST, true);
		$requred_fields = educare_combine_fields($requred_fields, '', $default_fields, true);

		foreach ($default_fields as $fields => $field_value) {
			$$fields = sanitize_text_field($field_value);
		}

		// Check requred fields valid || filled or not
		if (educare_is_empty($requred_fields)) {
			echo educare_is_empty($requred_fields);
		} else {
			$search_students = $default_fields;
			// remove group if Empty || All Group
			if (!$Group) {
				unset($search_students['Group']);
			}

			// Also remove Subject and Exam
			unset($search_students['Subject'], $search_students['Exam']);

			$search_marks = $search_students;
			unset($search_marks['Group']);
			$search_marks = educare_dynamic_sql($search_marks, 'marks');
			$search_students = educare_dynamic_sql($search_students, 'students');
			$search_students = $wpdb->get_results("SELECT * FROM $educare_students $search_students");

			if (count($search_students) > 0) {
				$terms = educare_get_template_data('fields');
				$marks = $wpdb->get_results("SELECT * FROM $educare_marks $search_marks");
				$marks_data = array();

				if(count($marks) > 0) {
					foreach($marks as $print) {
						$marks_data = $print->Marks;
						$marks_data = json_decode($marks_data, true);
					}
				}
				
				?>
				<form method='post' action="">
					<div class="educare_print">
						<?php
						echo '
						<input type="hidden" name="Subject" value="'.esc_attr( $Subject ).'">
						<input type="hidden" name="Class" value="'.esc_attr( $Class ).'">
						<input type="hidden" name="Group" value="'.esc_attr( $Group ).'">
						<input type="hidden" name="Exam" value="'.esc_attr( $Exam ).'">
						<input type="hidden" name="Year" value="'.esc_attr( $Year ).'">
						';

						echo "<div class='notice notice-success is-dismissible'><p>
							<b>".esc_html__('Class', 'educare').":</b> ".esc_html__($Class, 'educare')."<br>
							<b>".esc_html__('Group', 'educare')."Group:</b> ".esc_html__($Group, 'educare')."<br>
							<b>".esc_html__('Exam', 'educare')."Exam:</b> ".esc_html__($Exam, 'educare')."<br>
							<b>".esc_html__('Subject', 'educare')."Subject:</b> ".esc_html__($Subject, 'educare')."<br>
							<b>".esc_html__('Year', 'educare').":</b> ".esc_html__($Year, 'educare')."<br>
							<b>".esc_html__('Total Students', 'educare').":</b> ".esc_html(count($search_students))."
						</p><button class='notice-dismiss'></button></div>";

						echo '<input type="text" name="search" value="" placeholder="'._e('Type here for search', 'educare').'" width="100%">';

						if ($Subject) {
							?>
							<div class="table_container">
								<table class="grade_sheet list">
									<thead>
										<tr>
											<th><?php _e('No.', 'educare')?></th>
											<th><?php _e('Photos', 'educare')?></th>
											<th><?php _e('Name', 'educare')?></th>
											<th><?php _e('Roll No', 'educare')?></th>
											<th><?php _e('Regi No', 'educare')?></th>
											<?php
											foreach ($terms as $term => $termInfo) {
												$title = $subtitle = '';

												if (key_exists('title', $termInfo)) {
													$title = $termInfo['title'];
												}
												
												if (key_exists('subtitle', $termInfo)) {
													$subtitle = $termInfo['subtitle'];
													if ($subtitle) {
														$subtitle = '<br><small>('.esc_html($subtitle).')</small>';
													}
												}
												
												echo '<th>'. esc_html($title) . $subtitle.'</th>';
											}
											?>

											<th class="optional_sub">
												<div class="action_menu"><i class="dashicons action_button dashicons-info"></i> <menu class="action_link msg text-black"><b><?php _e('Optional Subject</b><hr>If the student or your result system includes an optional subject, please indicate it. Otherwise, you may disregard this field.', 'educare')?></menu></div>
											</th>
										</tr>
									</thead>
								<?php
						}

						$serialNo = 1;
						$sub_in = 0;

						// Student data
						foreach($search_students as $student) {
							$id = $student->id;
							$name = $student->Name;
							$roll_no = $student->Roll_No;
							$regi_no = $student->Regi_No;
							$details = json_decode($student->Details);
							$others = json_decode($student->Others);
							$sub = json_decode($student->Subject);

							if ($Subject) {
								if ($sub) {
									if (property_exists($sub, $Subject)) {
										$sub_in++;
										echo '
										<tr>
											<td>'.esc_html( $serialNo++ ).'</td>
											<td><img src="'.esc_url(educare_get_attachment($others->Photos)).'" class="user-img" alt="IMG"/></td>
											<td>'.esc_html( $name ).'</td>
											<td>'.esc_html( $roll_no ).'</td>
											<td>'.esc_html( $regi_no ).'</td>
											';

											foreach ($terms as $term => $termInfo) {
												$value = '';
												if (isset($marks_data[$id][$Subject][$term])) {
													$value = $marks_data[$id][$Subject][$term];
												}

												echo '<td>';

												// Select field
												if ($termInfo['type'] == 'select') {
													echo '<select name="marks['.esc_attr($term).'][]">';
													foreach ($termInfo['value'] as $val) {
														echo '<option value="'.esc_attr($val).'">' . esc_html($val) . '</option>';
													}
													echo '</select>';
												} else {
													// Input field
													echo '<input type="' . esc_attr($termInfo['type']) . '" name="marks['.esc_attr($id).']['.esc_attr($Subject).']['.esc_attr($term).']" value="'.esc_attr($value).'">';
												}

												echo '</td>';
						
											}

											$value = '';
											if (isset($marks_data[$id][$Subject]['optional'])) {
												$value = $marks_data[$id][$Subject]['optional'];
											}

											echo '<td>
											<input type="hidden" name="marks['.esc_attr($id).']['.esc_attr($Subject).'][optional]">
											<input type="checkbox" value="yes" name="marks['.esc_attr($id).']['.esc_attr($Subject).'][optional]" '.checked($value, 'yes', false).'>
											</td>';
										echo '</tr>';
									}
								}

							} else {
								?>
								<div class="educare-marks">
									<div class="table_container">
										<table class="grade_sheet list view_results">
											<thead>
												<tr>
													<th><?php _e('No.', 'educare')?></th>
													<th><?php _e('Photos', 'educare')?></th>
													<th><?php _e('Name', 'educare')?></th>
													<th><?php _e('Roll No', 'educare')?></th>
													<th><?php _e('Regi No', 'educare')?></th>
													<th><?php _e('Group', 'educare')?></th>
												</tr>
											</thead>

											<tbody>
												<tr>
													<?php
													echo'
													<td class="student-no">'.esc_html( $serialNo++ ).'</td>
													<td><img src="'.esc_url(educare_get_attachment($others->Photos)).'" class="user-img" alt="IMG"/></td>
													<td>'.esc_html( $name ).'</td>
													<td>'.esc_html( $roll_no ).'</td>
													<td>'.esc_html( $regi_no ).'</td>
													<td>'.esc_html( $group ).'</td>
													';
													?>
												</tr>
											</tbody>
										</table>
									</div>
									<?php

									if (key_exists($id, $marks_data)) {
										$_POST['marks'][$id] = $marks_data[$id];
									}
									
									// In the above, the $sub is an object. We use get_object_vars() to convert the object into an associative array, and then we apply array_keys() to retrieve the keys from the array. The resulting keys are stored in the $student_sub
									$student_sub = array_keys(get_object_vars($sub));
									educare_get_marks_fields('results', $student_sub, $id, 'marks');

								echo '</div>';
							}
						}

						if ($Subject) {
							if (empty($sub_in)) {
								echo '<tr><td colspan="100">Sorry, no student found in this subject <b>('.esc_html( $Subject++ ).')</b></td></tr>';
							} else {
								echo "<div class='notice notice-success is-dismissible'><p># Total ".esc_html($sub_in)." students found in this subject</p><button class='notice-dismiss'></button></div>";
							}

							echo '</table>
							</div>';
						}
						?>
					</div>
					
					<div class="button-container">
						<input type="submit" name="add_marks" class="educare_button" value="Save Marks">
						<input type="submit" name="publish_marks" class="educare_button" value="Publish">
						<input type="button" id="print" class="educare_button" value="&#xf02f; Print">
						<div class="action_menu"><i class="dashicons action_button dashicons-info"></i> <menu class="action_link info"><?php _e('<strong>Mark not visible when print?</strong><br> Please, fill up students marks and save. Then, select <b>Students List</b> again and print marksheet (Save then Print).', 'educare')?></menu></div>
					</div>
					
				</form>
				<?php
			} else {
				echo '<div class="notice notice-error is-dismissible"><p> No student found in this class <b>('.esc_html($Class).')</b>. <a href="/wp-admin/admin.php?page=educare-all-students&add-data" target="_blank">click add students</a></p><button class="notice-dismiss"></button></div>';
			}
		}
	}
}


/** 
 * Get students by id
 * Get student by specific id
 * 
 * @since 1.4.0
 * @last-update 1.4.0
 * 
 * @param int $id	for 	specific students by id
 * @param array $sql		for auto sql
 * @return object|array
 */
function educare_get_students($id, $sql = null) {
	global $wpdb;
	// Table name
	$educare_students = $wpdb->prefix."educare_students";

	if ($sql) {
		$sql = educare_get_sql($sql, 'OR');
	} else {
		$sql = "id='$id'";
	}

	$search = $wpdb->get_results("SELECT * FROM ".$educare_students." WHERE $sql");

	if ($search) {
		return $search;
	}
}



/**
 * Get user data based on ID and role.
 *
 * This function retrieves data for a specific user based on their ID and role.
 * It constructs a database query to fetch the user data from the specified table
 * corresponding to the given role.
 *
 * @param int $id The ID of the user whose data is being retrieved.
 * @param string $roles The role of the user (default: 'students').
 * @return object|null The retrieved user data as an object, or null if not found.
 */
function educare_get_users_data($id, $roles = 'students') {
	$select = array (
		'table' => $roles,     			// SELECT * FROM $select
		'row' => 'id',           		// WHERE $row (need to compare with value)
		'value' => $id,       			// WHERE $row equal to $value ($row='$value')
		'get_results' => 'get_row',
	);

	return educare_get_results($select);
}



/**
 * Save or publish student marks for a specific class, exam, subject, and year.
 *
 * This function handles the process of saving or publishing student marks based on the provided
 * input data. It checks for access permissions, processes and updates marks data, and provides
 * appropriate success/error messages. If the $publish parameter is set to true, the function will
 * publish the marks and update the corresponding results table. If $publish is false, it will only
 * save the marks to the marks table.
 *
 * @since 1.4.0
 * @last-update 1.4.0
 * @param bool|null $publish Whether to publish the marks and update results table (true) or save marks only (false).
 * @return void
 */
function educare_save_marks($publish = null) {
	// check if user access this request
	educare_check_access('mark_sheet');

	global $wpdb;
	// Table name
	$educare_marks = $wpdb->prefix."educare_marks";
	$educare_results = $wpdb->prefix."educare_results";

	if (isset($_POST['add_marks']) or isset($_POST['publish_marks'])) {
		$Class = sanitize_text_field($_POST['Class']);
		$Exam = sanitize_text_field($_POST['Exam']);
		$Subject = sanitize_text_field($_POST['Subject']);
		$Year = sanitize_text_field($_POST['Year']);

		$search = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $educare_marks WHERE Class=%s AND Exam=%s AND Year=%d",
				$Class,
				$Exam,
				$Year
			)
		);

		if(count($search) > 0) {
			foreach($search as $print) {
				$id = $print->id;
				$Class = $print->Class;
				$Exam = $print->Exam;
				$Year = $print->Year;
				
				$details = $print->Marks;
				$details = json_decode($details, TRUE);
			}
		}

		foreach ($_POST['marks'] as $student_id => $marks) {
			// for single subject
			if ($Subject) {
				$details[$student_id][$Subject] = $_POST['marks'][$student_id][$Subject];
			} else {
				// for multiple sublect
				$details[$student_id] = $_POST['marks'][$student_id];
			}
		}

		$data = array (
			'Class' => $Class,
			'Exam' => $Exam,
			'Year' => $Year,
			'Marks' => json_encode($details),
			'Status' => 'pending'
		);

		if ($publish) {
			$count = $updated = $new = $error = 0;
			$unset = array (
				'id',
				'Student_ID',
				'Others'
			);

			foreach ($details as $student_id => $subject) {
				$students = educare_get_users_data($student_id);
				if ($students) {
					$Roll_No = $students->Roll_No;
					$Regi_No = $students->Regi_No;
					
					foreach ($unset as $key) {
						if (property_exists($students, $key)) {
							unset($students->$key);
						}
					}
					
					$students->Exam = $Exam;
					$students->Subject = json_encode($subject);
					$students = json_decode(json_encode($students), TRUE);

					$requred = educare_check_status('display');
					$requred_fields = educare_combine_fields($requred, array('Name'), $students);
					$requred_fields = educare_get_dynamic_sql('results', $requred_fields);

					$find_results = $wpdb->get_results($requred_fields);

					$count++;

					if ($find_results) {
						foreach ($find_results as $results_data) {
							// Define the where condition
							$where = array(
								// replace with the ID of the row to update
								'id' => $results_data->id,
							);
							// request to processing update
							$process = $wpdb->update($educare_results, $students, $where);
							// Check if the update was successful
							if ( $process === false ) {
								$error++;
								// handle error
							} elseif ( $process == 0 ) {
								// no rows were updated, handle accordingly (No channges for your update)
							} else {
								$updated++;
								// update was successful, handle accordingly
							}
						}
					} else {
						$insert = $wpdb->insert($educare_results, $students);
						if ($insert === false) {
							$error++;
							// Error occurred, handle it
						} else {
							$new++;
							// Data inserted successfully
						}
					}
				}

			}

			$data['Status'] = 'published';

			if($count == $updated) {
				$msgs = 'updated';
			} else {
				$msgs = 'publish';
			}

			$msgs = "<div class='notice notice-success is-dismissible'><p>
			Successfully ".esc_html( $msgs )." all (".esc_html( $count ).") results. <br>
			<b>Total Students:</b> ".esc_html( $count )." <br>
			<b>Updated Results:</b> ".esc_html( $updated )." <br>
			<b>New Results:</b> ".esc_html( $new )." <br>
			<b>Error:</b> <span class='error'>".esc_html( $error )."</span> <br>
			</p><button class='notice-dismiss'></button></div>";

		}

		if ($search) {
			$wpdb->update($educare_marks, $data, array('ID' => $id));
		} else {
			$wpdb->insert($educare_marks, $data);
		}
		
		if (!$publish) { 
			if ($wpdb->insert_id > 0) {
				$msgs = "<div class='notice notice-success is-dismissible'><p>Successfully added your sellected subject (<b>".esc_html($Subject)."</b>) marks for all students of class <b>".esc_html($Class)."</b></p><button class='notice-dismiss'></button></div>";
			} else {
				$msgs = "<div class='notice notice-success is-dismissible'><p>Successfully updated your sellected subject (<b>".esc_html($Subject)."</b>) marks for all students of class <b>".esc_html($Class)."</b></p><button class='notice-dismiss'></button></div>";
			}
		}

		echo $msgs;

	}
}



/** 
 * Get mark for specific student
 * 
 * @since 1.4.0
 * @last-update 1.4.0
 * @param int $id				select specific students
 * @return mixed|void
 */
function educare_get_marks_by_id($id) {
	global $wpdb;
	$educare_marks = $wpdb->prefix."educare_marks";

	$Class = sanitize_text_field($_POST['Class']);
	$Exam = sanitize_text_field($_POST['Exam']);
	$Year = sanitize_text_field($_POST['Year']);
	$Subject = sanitize_text_field($_POST['Subject']);

	// Execute database query to fetch marks for the provided class, exam, and year
	$marks = $wpdb->get_results(
    $wpdb->prepare(
			"SELECT * FROM $educare_marks WHERE Class=%s AND Exam=%s AND Year=%d",
			$Class,
			$Exam,
			$Year
    )
	);

	if(count($marks) > 0) {
		foreach($marks as $print) {
			$details = $print->Marks;
			$details = json_decode($details, true);
		}
		
		if (isset($details[$id][$Subject])) {
			return $details[$id][$Subject];
		}
	}
}

/**
 * AJAX action to process marks for a specific class, group, subject, exam, and year combination.
 *
 * The `educare_process_marks` function is an AJAX callback used to process marks for a specific class, group, subject, exam, and year
 * within the Educare theme or plugin. It is triggered when the corresponding AJAX action is called.
 *
 * The function first sanitizes and parses the necessary data from the AJAX request, including 'action_for', 'data_for', and 'form_data'.
 * It then sets the corresponding 'action_for' and 'data_for' values in the `$_POST` array for further processing.
 *
 * Depending on the specific action requested through AJAX, the function takes different actions:
 *
 * - If the AJAX request is to retrieve options for the 'Class' field based on the selected subject, it calls the `educare_get_options_for_subject`
 *   function with the 'Class' field as the target and the selected 'Class' and 'Subject' values.
 *
 * - If the AJAX request is to retrieve options for the 'Group' field based on the selected subject, it calls the `educare_get_options_for_subject`
 *   function with the 'Group' field as the target and the selected 'Group' and 'Subject' values.
 *
 * - If the AJAX request is to publish marks, it calls the `educare_save_marks` function with the 'publish_marks' parameter set to true. This saves
 *   the marks and publishes them, then calls the `educare_get_students_list` function to retrieve the updated students' list.
 *
 * - For any other action or if the AJAX request is not one of the above, the function calls the `educare_save_marks` function to save the marks,
 *   and then calls the `educare_get_students_list` function to retrieve the updated students' list.
 *
 * The function terminates script execution with `die()` after processing the marks and retrieving the students' list to prevent any further output.
 *
 * @return void The function processes marks for a specific class, group, subject, exam, and year combination and terminates script execution.
 *
 * @since 1.4.0
 * @last-update 1.4.0
 * 
 * @example
 * This AJAX action is hooked to the 'educare_process_marks' action.
 * add_action('wp_ajax_educare_process_marks', 'educare_process_marks');
 *
 * The function is triggered via AJAX when the 'educare_process_marks' action is called.
 * It processes marks and performs actions based on the specific AJAX request.
 */
function educare_process_marks() {
	// Sanitize and parse necessary data from the AJAX request
	$action_for = sanitize_text_field($_POST['action_for']);
	$data_for = isset($_POST['data_for']) ? sanitize_text_field($_POST['data_for']) : '';
	// Remove the backslash
	$_POST['form_data'] = stripslashes($_POST['form_data']);
	// parses query strings and sets the parsed values into the $_POST array.
	wp_parse_str($_POST['form_data'], $_POST);
	$_POST[$action_for] = $action_for;
	$_POST['data_for'] = $data_for;

	// Verify the nonce to ensure the request originated from the expected source
	educare_verify_nonce($action_for, $action_for.'_nonce');

	$Class   = isset($_POST['Class']) ? sanitize_text_field($_POST['Class']) : '';
	$Group   = isset($_POST['Group']) ? sanitize_text_field($_POST['Group']) : '';
	$Subject = isset($_POST['Subject']) ? sanitize_text_field($_POST['Subject']) : '';

	if (isset($_POST['get_Class'])) {
		educare_get_options_for_subject('Class', $Class, $Subject);
	} elseif (isset($_POST['get_Group'])) {
		educare_get_options_for_subject('Group', $Group, $Subject);
	} elseif (isset($_POST['publish_marks'])) {
		educare_save_marks(true);
		educare_get_students_list();
	} else {
		educare_save_marks();
		educare_get_students_list();
	}

	die;
}

add_action('wp_ajax_educare_process_marks', 'educare_process_marks');



/** 
 * Ajax responce when request for class or group options
 * 
 * @since 1.4.0
 * @last-update 1.4.0
 * @return mixed
 */
function educare_process_options_by() {
	// Check if the current user has the access this request as 'manage_options' capability (typically administrators).
	educare_check_access();

	// Verify the nonce to ensure the request originated from the expected source
	educare_verify_nonce('educare_crud_data');
	
	$data_for = isset($_POST['data_for']) ? sanitize_text_field($_POST['data_for']) : '';
	$subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '';
	
	educare_show_options($data_for, $subject, 'Group');
	
	die;
}

add_action('wp_ajax_educare_process_options_by', 'educare_process_options_by');



/**
 * Display options for a given target based on a selected value.
 *
 * This function generates HTML markup to display a select dropdown element containing
 * options for a specific target, based on a selected value. It is commonly used for generating
 * dropdowns for classes and subjects, and includes an option to select "None (Default)".
 * Additionally, it includes an input field to store the old value for comparison.
 *
 * @since 1.4.0
 * @last-update 1.4.0
 * @param string $target The target for which options are being generated (e.g., "Class", "Group").
 * @param string $val The selected value to determine options based on.
 * @return void
 */
function educare_options_by($target, $val) {
	?>
	<div class="select">
		<select id="<?php echo esc_attr($target);?>" name="Group" class="form-control">
			<option value=""><?php _e('None (Default)', 'educare')?></option>
			<?php educare_get_options($target, $val);?>
		</select>

		<?php
		// data for class/add marks page
		if ($target == 'Class') {
			echo '<select id="'.esc_attr($target).'_list" name="'.esc_attr($target).'_list" class="form-control"><option value="">Select Subject</option></select>';
		}
		?>
	</div>

	<input type="hidden" id="old-<?php echo esc_attr($target)?>" type="text" value="<?php echo esc_attr($val)?>">
	<?php
}



/**
 * Promote students to a new class/year/group based on specified criteria.
 *
 * This function handles the process of promoting students to a new class, year, and/or group
 * based on the provided criteria. It verifies user access, retrieves necessary data, performs
 * checks, and updates student records as needed.
 * 
 * @since 1.4.0
 * @last-update 1.6.0
 * @return void
 */
function educare_promote_students() {
	// check if user access this request
	educare_check_access('performance');
	// Show banner
	educare_show_school_banner();

	$status = $Class = $Group = $Exam = $Year = $Promoted_Class = $Promoted_Group = $Promoted_Exam = $Promoted_Year = '';

	if (isset($_POST['promote'])) {
		// Verify the nonce to ensure the request originated from the expected source
		educare_verify_nonce('educare_promote_nonce');
		
		global $wpdb;
		$students_table = $wpdb->prefix . 'educare_students';
		$results_table = $wpdb->prefix . 'educare_results';

		$requred = array (
			'Class',
			'Year'
		);
		
		foreach ($_POST as $key => $val) {
			$$key = sanitize_text_field($val);
		}

		unset($_POST['status'], $_POST['promote']);
		
		if ($Group) {
			array_push($requred, 'Group');
		} else {
			unset($_POST['Group']);
		}

		if (!$Promoted_Exam) {
			unset($_POST['Promoted_Exam']);
		}
		if (!$Promoted_Group) {
			unset($_POST['Promoted_Group']);
		}
		
		$requred_fields = educare_combine_fields($requred, '', '', true);

		if (!educare_is_empty($_POST)) {
			$sql = educare_get_sql($requred_fields);
			
			$select = "SELECT * FROM $students_table WHERE $sql";
			$students = $wpdb->get_results($select);
			
			$total = $promoted = $failed = $no_results_data = $exist =  0;

			if ($students) {
				$total = count($students);

				$ignore = array(
					'Name',
					'Exam'
				);

				if (isset($_POST['Promoted_Exam'])) {
					$Exam = sanitize_text_field($_POST['Promoted_Exam']);
				} else {
					$Exam = false;
				}
				
				$requred = educare_check_status('display');
				$requred = educare_combine_fields($requred, $ignore);
				$requred_fields = array();

				foreach ($students as $print) {
					$promote = false;
					$students_id = $print->id;
					$id = $print->id;
					$Student_ID = $print->Student_ID;
					$Roll_No = $print->Roll_No;
					$Regi_No = $print->Regi_No;
					$Group = $print->Group;
					$School = $print->School;

					$_POST['Promoted_Roll_No'] = $Roll_No;
					$_POST['Promoted_Regi_No'] = $Regi_No;

					foreach ($requred as $key => $value) {
						if (key_exists('Promoted_'.$key, $_POST)) {
							$requred_fields[$key] = $_POST['Promoted_'.$key];
						} else {
							if (property_exists($print, $key)) {
								$requred_fields[$key] = sanitize_text_field($print->$key);
							} else {
								$requred_fields[$key] = '';
							}
						}
					}

					$sql = educare_get_sql($requred_fields);
					$select = "SELECT * FROM $students_table WHERE $sql";
					$students = $wpdb->get_results($select);
					
					if ($students) {
						$exist++;
					} else {
						if ($Exam) {
							$find_results = array (
								'Roll_No' => $Roll_No,
								'Regi_No' => $Regi_No,
								'Class' => $Class,
								'Exam' => $Exam,
								'Year' => $Year
							);

							$requred_results_fields = educare_check_status('display');
							$requred_results_fields = educare_combine_fields($requred_results_fields, array('Name'), $find_results);

							$sql = educare_get_sql($find_results);
							$select = "SELECT * FROM $results_table WHERE $sql";
							$results = $wpdb->get_results($select);

							if ($results) {
								if ($status == 'passed') {
									foreach ($results as $show) {
										$id = $show->id;
										// $subject = json_decode($show->Subject, true);
										$results_status = educare_results_status($show);
										$results_status = $results_status->GPA;

										if ($results_status) {
											$promote = true;
										} else {
											$failed++;
										}
									}
								} else {
									$promote = true;
								}
								
							} else {
								$promote = false;
								$no_results_data++;
							}

						} else {
							$promote = true;
						}

						if ($promote) {
							unset($print->id);

							if ($Student_ID) {
								$print->Student_ID = $Student_ID;
							} else {
								$print->Student_ID = $id;
							}

							$print->Year = sanitize_text_field($_POST['Promoted_Year']);
							$new_class = sanitize_text_field($_POST['Promoted_Class']);
							$print->Class = $new_class;

							// if request to change group
							$subject = json_decode($print->Subject);

							if (isset($_POST['Promoted_Group'])) {
								$group = sanitize_text_field($_POST['Promoted_Group']);
								$print->Group = $group;
							} else {
								$group = $print->Group;
							}

							$new_sub = educare_check_settings('Class', $new_class);
							$add_new_sub = array();

							// add new class subject
							if ($new_sub) {
								foreach ($new_sub as $sub) {
									if (isset($subject->$sub->optional)) {
										$add_new_sub[$sub]['optional'] = 'yes';
									} else {
										$add_new_sub[$sub]['optional'] = '';
									}
								}
							}

							// add new group subject
							if ($group) {
								$new_group = educare_check_settings('Group', $group);
								
								if ($new_group) {
									foreach ($new_group as $sub) {
										if (isset($subject->$sub->optional)) {
											$add_new_sub[$sub]['optional'] = 'yes';
										} else {
											$add_new_sub[$sub]['optional'] = '';
										}
									}
								}
							}
							
							// convert object to json for insert data
							$print->Subject = json_encode($add_new_sub);
							// Insert data
							$print = json_decode(json_encode($print), TRUE);

							if (isset($_POST['old_data']) && $_POST['old_data'] == 'on') {
								$modify_msgs = false;
							} else {
								// Clean student data
								$insert_data = $wpdb->update($students_table, $print, array('ID' => $students_id));
								$modify_msgs = 'update';
							}
							
							if ($insert_data) {
								// Count promoted data/students
								$promoted++;
							}
							
						}

					}

				}

				if ($promoted) {
					$msgs = sprintf(__('Successfully %s %d students', 'educare'), esc_html($modify_msgs), esc_html($promoted));
					$success = 'success';
				} else {
					$msgs = __('No students were found for promotion.', 'educare');
					$success = 'error';
				}

				if ($Exam) {
					$failed = $failed . ' students';
				} else {
					$failed = 'Not required';
				}
				
				$info = esc_html($msgs) . '<br>';
				$info .= sprintf(__('Total: %d students', 'educare'), esc_html($total))."<br>
				".sprintf(__('Promote: %d students', 'educare'), esc_html($promoted));

				if ($exist) {
					$info .= '<br>'.sprintf(__('Already exist: %d students', 'educare'), esc_html($exist));
				}
				if ($Exam) {
					$info .= '<br>'.sprintf(__('Not Participate: %s', 'educare'), esc_html($no_results_data));
					$info .= '<br>'.sprintf(__('Failed: %s', 'educare'), esc_html($failed));
				}

				echo educare_show_msg($info);
				
			} else {
				echo educare_show_msg(sprintf(__('%s not found', 'educare'), __('Students', 'educare')));
			} 
		} else {
			echo educare_is_empty($_POST, true);
		}
		
	}
	
	$old_data = 'on';
	$required = educare_check_status('display');
	$required_title = educare_requred_data($required, true);
	
	?>
	<div id="educare-form">
		<form class="add_results" action="" method="post">
			<div class="content">
				<?php
				// Show page title
				echo educare_get_page_title('Students', 'promote');

				if (educare_check_status('show_cover_photos') === 'checked') {
					echo '<div class="cover"><img src="'.esc_url(EDUCARE_URL.'assets/img/achivement.svg').'" alt="Achivement" title="Achivement"/></div>';
				}

				echo educare_guide_for(__('Here, you can update multiple students\' class, year, group or school with a single click! This feature is especially useful for promoting students from one class to another or making bulk updates.', 'educare'));
				?>

				<div class="row gy-3 mt-3">
					<div class="col-5 mb-2">
						<div class="text-center">From</div>
					</div>
					<div class="col-2 mb-2">
						<div class="text-center">
							<span class="dashicons dashicons-arrow-left-alt"></span>
							<span class="dashicons dashicons-arrow-right-alt"></span>
						</div>
					</div>
					<div class="col-5 mb-2">
						<div class="text-center">To</div>
					</div>

					<!-- Class -->
					<div class="col-6">
						<label for="Class" class="form-label"><?php echo sprintf(__('Select %s', 'educare'), __('Class', 'educare'));?></label>
						<select id="Class" name="Class" class="form-control">
							<?php educare_get_options('Class', $Class);?>
						</select>
					</div>
					<div class="col-6">
						<label for="Promoted_Class" class="form-label"><?php echo sprintf(__('New %s', 'educare'), __('Class', 'educare'));?></label>
						<select id="Promoted_Class" name="Promoted_Class" class="form-control">
							<?php educare_get_options('Class', $Promoted_Class);?>
						</select>
					</div>

					<!-- Year -->
					<div class="col-6">
						<label for="Year" class="form-label"><?php echo sprintf(__('Select %s', 'educare'), __('Year', 'educare'));?></label>
						<select id="Year" name="Year" class="form-control">
							<?php educare_get_options('Year', $Year);?>
						</select>
					</div>
					<div class="col-6">
						<label for="Promoted_Year" class="form-label"><?php echo sprintf(__('New %s', 'educare'), __('Year', 'educare'));?></label>
						<select id="Promoted_Year" name="Promoted_Year" class="form-control">
							<?php educare_get_options('Year', $Promoted_Year);?>
						</select>
					</div>

					<!-- Group -->
					<?php
					if (key_exists('Group', $required_title)) {
					?>
						<div class="col-6">
							<label for="Group" class="form-label"><?php echo sprintf(__('Select %s', 'educare'), __('Group', 'educare'));?></label>
							<select id="Group" name="Group" class="form-control">
								<option value=""><?php echo sprintf(__('All %s', 'educare'), __('Group', 'educare'));?></option>
								<?php educare_get_options('Group', $Group);?>
							</select>
						</div>
						<div class="col-6">
							<label for="Promoted_Group" class="form-label"><?php echo sprintf(__('New %s', 'educare'), __('Group', 'educare'));?></label>
							<select id="Promoted_Group" name="Promoted_Group" class="form-control">
								<option value=""><?php echo sprintf(__('All %s', 'educare'), __('Group', 'educare'));?></option>
								<?php educare_get_options('Group', $Promoted_Group);?>
							</select>
						</div>
						<?php
					}
					?>

					<!-- Condition -->
					<div class="col-6">
						<label for="status" class="form-label"><?php echo sprintf(__('Select %s', 'educare'), __('Condition', 'educare'));?></label>
						<select id="status" name="status" class="fields">
							<option value="passed" <?php selected( $status, 'passed' ); ?>><?php _e('Students have passed', 'educare')?></option>
							<option value="participated" <?php selected( $status, 'participated' ); ?>><?php _e('Participated in the exam', 'educare')?></option>
						</select>
					</div>
					<div class="col-6">
						<label for="Promoted_Exam" class="form-label"><?php _e('Requred condition', 'educare');?></label>
						<select id="Promoted_Exam" name="Promoted_Exam" class="form-control">
							<option value=""><?php _e('Not required', 'educare');?></option>
							<option value="all" disabled><?php echo sprintf(__('All %s', 'educare'), __('Exam', 'educare'));?></option>
							<?php educare_get_options('Exam', $Promoted_Exam);?>
						</select>
					</div>
				</div>
				
				<div class="p-2 my-3 rounded <?php echo educare_advance_fields();?>">
					<input type="hidden" name="old_data" value="off">
					<input id="old_data" type="checkbox" name="old_data" value="on" <?php checked($old_data, 'on' );?> disabled>
					<label for="old_data"><?php _e('Keep old data', 'educare');?></label>
					<?php echo educare_advance_fields_badge(false);?>

					<div class="action_menu">
						<i class="dashicons action_button dashicons-info"></i> <menu class="action_link info"><b>Archive Data</b><hr>Enable this option to archive old data, allowing students and authorities to access past records. Otherwise, the current students data will be removed permanently.</menu>
					</div>
				</div>

				<?php
				// Nonce for this form
				$nonce = wp_create_nonce( 'educare_promote_nonce' );
				echo '<input type="hidden" name="nonce" value="'.esc_attr($nonce).'">';
				?>
				
				<button type="submit" name="promote" id="promote" class="btn btn-success"><span class="dashicons dashicons-star-half"></span> <?php echo __('Promote', 'educare');?></button>

			</div>
		</form>
	</div>
	<?php
}



/** 
 * Ajax response for promote students form
 * 
 * @since 1.4.0
 * @last-update 1.4.0
 * 
 * @return mixed
 */
 function educare_proccess_promote_students() {
	// Remove the backslash
	$_POST['form_data'] = stripslashes($_POST['form_data']);
	// parses query strings and sets the parsed values into the $_POST array.
	wp_parse_str($_POST['form_data'], $_POST);
	$_POST['promote'] = true;
	educare_promote_students();
	die;
}

add_action('wp_ajax_educare_proccess_promote_students', 'educare_proccess_promote_students');



/**
 * Check user access based on capabilities.
 *
 * This function checks if the current logged-in user has the required capabilities to access a specific feature or page.
 * It takes an array of capabilities as a parameter and verifies whether the user has the necessary permissions.
 * If the user does not have the required capabilities, an error message is displayed, and the script execution is terminated.
 *
 * @param array $capabilities An array of capabilities required to access the feature or page.
 * @return void
 */
function educare_check_access($capabilities = '') {
	if ($capabilities) {
		$capabilities = 'access_educare_'.$capabilities;
	} else {
		$capabilities = 'manage_options';
	}

	// Check if user is admin then no need to check capability
	if (current_user_can('manage_options')) {
		return;
	}

	if (!current_user_can( $capabilities )) {
		echo educare_guide_for('Sorry, you are not allowed to access this. You need a higher-level permission.', false);
		die;
	}
}



/**
 * Retrieve all functions hooked to a specific action.
 *
 * Usage
 * $action_name = 'educare_results_card_template';
 * $hooked_functions = educare_get_hooked_functions($action_name);
 * 
 * Display hooked functions
 * echo '<pre>';
 * print_r($hooked_functions);
 * echo '</pre>';

 * This function retrieves all functions hooked to a specific WordPress action. It accesses the global
 * `$wp_filter` variable to get the list of hooked functions for the provided action name. The function
 * returns an array of hooked functions or, if the `$only_function` parameter is set to `true`, it returns
 * an array containing only the names of the hooked functions without any additional information.
 *
 * @param string  $action_name   The name of the WordPress action to retrieve hooked functions for.
 * @param boolean $only_function Optional. Whether to return only the names of the hooked functions. Default is `false`.
 * @return array An array of hooked functions or their names.
 */
function educare_get_hooked_functions($action_name, $only_function = false) {
  global $wp_filter;
  
  $hooked_functions = array();
  
  // Check if the action has hooks
  if (isset($wp_filter[$action_name]) && !empty($wp_filter[$action_name])) {
    $hooked_functions = $wp_filter[$action_name];
  }

	if ($only_function) {
		$hooked_functions_all = array();
		$i = 0;

		foreach ($hooked_functions as $priority => $callbacks) {
			foreach ($callbacks as $callback) {
				$function_name = '';
				$is_class_method = is_array($callback['function']) && is_object($callback['function'][0]);
				
				// Get the function name or class method name
				if ($is_class_method) {
					$function_name = get_class($callback['function'][0]) . '->' . $callback['function'][1];
				} else {
					$function_name = $callback['function'];
				}

				$hooked_functions_all[$i++] = $function_name;

			}
		}

		return $hooked_functions_all;
	}
  
  return $hooked_functions;
}



/**
 * Displays a list of available template options for a specific template type.
 *
 * usage:
 * educare_show_template('educare_results_card_template');
 * 
 * This function generates and displays a list of available template options for a given template type. It is typically used
 * in the admin panel to allow users to select a template for a specific purpose. The function retrieves hooked functions for
 * the provided template type, retrieves template information such as title and thumbnail from these functions, and generates
 * image radio buttons for each template option. Users can then select the desired template option from the list.
 *
 * @param string $template The template type to display options for.
 * @return void
 */
function educare_show_template($template = 'educare_results_card_template') {
	$template_for = sanitize_text_field( str_replace('educare_', '', $template) );

	echo '<div class="template_list">
		<div class="m-4 text-center">
			<b>'.esc_html__(ucwords(str_replace('_', ' ', $template_for)), 'educare').'</b>
			<small class="d-block">'.sprintf(__('You can find more template from <a href="%s" target="_blank">Here</a>', 'educare'), esc_url(EDUCARE_SVR.'/plugins/educare/templates')).'</small>
		</div>
		
		<div class="radio-container">';

			// Replace with the action name you want to retrieve hooked functions for
			$hooked_functions = educare_get_hooked_functions($template);
			// Loop through hooked functions and generate image radio buttons
			// Check active template
			$active_template = educare_check_status($template_for);
			
			foreach ($hooked_functions as $priority => $callbacks) {
				foreach ($callbacks as $callback) {
					$function_name = '';
					$is_class_method = is_array($callback['function']) && is_object($callback['function'][0]);
					
					// Get the function name or class method name
					if ($is_class_method) {
						$function_name = get_class($callback['function'][0]) . '->' . $callback['function'][1];
					} elseif ($callback['function'] instanceof Closure) {
						// $function_name = 'Closure';
					} else {
						$function_name = $callback['function'];
					}

					// Retrieve template details (call the function)
					$template_info = $template_callback = null;

					if ($callback['function'] instanceof Closure) {
						// If it's a closure, execute it directly
						$template_info = $callback['function']('', true);
					} elseif ($is_class_method) {
						// If it's a class method, use call_user_func
						$template_info = call_user_func($callback['function'], '', true);
					} elseif (is_string($callback['function']) && function_exists($callback['function'])) {
						// If it's a regular function, call it normally
						$template_callback = sanitize_text_field($callback['function']);
						$template_info = $template_callback('', true);
					}

					// Now $template_info holds the template details

					if (is_array($template_info)) {
						// get title from hoocked function
						$advance = key_exists('advance', $template_info) ? $template_info['advance'] : false;
						$template_title = key_exists('title', $template_info) ? $template_info['title'] : $function_name;
						// get thumbnail from hoocked function
						$template_thumbnail = key_exists('thumbnail', $template_info) ? $template_info['thumbnail'] : EDUCARE_TEMPLATE_THUMBNAIL;
					} else {
						// get title from hoocked function
						$template_title = $function_name;
						
						// get thumbnail from hoocked function
						$template_thumbnail = EDUCARE_TEMPLATE_THUMBNAIL;
					}

					$advance_fields = $advance_badge = '';
					$template_id = $function_name;

					$template_callback = isset($template_info['callback']) ? sanitize_text_field($template_info['callback']) : $template_callback;

					if (function_exists($template_callback)) {
						$INSTALLED = 'Activate';
						$template_status = 'activate';
					} else {
						$INSTALLED = 'Install';
						$template_status = 'install';
					}

					if ($template_id == $active_template) {
						$INSTALLED = 'Active';
						$template_status = 'active';
					}
					
					if ($advance) {
						$advance_fields = educare_advance_fields();
						$advance_badge = educare_advance_fields_badge();
						$INSTALLED = 'Unlock To Premium';
						$template_status = 'advance-template';
						$template_id = '';
					}
					
					// Output the image radio button with the function name as the value
					echo '<input type="radio" id="' . esc_attr($template_id) . '" name="' . esc_attr($template_for) . '" value="' . esc_attr($template_id) . '" '.checked($template_id, $active_template, false).'>';
					// echo wp_kses_post($advance_badge);
					echo '<label class="template-info-container" for="' . esc_attr($template_id) . '">
						<div class="template-item '.esc_attr($advance_fields).'">
							<img class="template-img" src="'.esc_url($template_thumbnail).'" alt="' . esc_attr($template_title) . '">
							<div class="template-details">
								'.wp_kses_post($advance_badge).'
								<div class="template-info">
									<b>'.esc_html__( str_replace('_',  ' ', $template_title), 'educare' ).'</b>
									<p class="pt-3"><span class="template-status py-2 px-3 rounded '.esc_attr($template_status).'">'.esc_html__($INSTALLED, 'educare').'</span></p>
								</div>
							</div>
						</div>
					</label>';
				}
			}
		echo '</div>';
	echo '</div>';
}



/**
 * Change event thought Ajax request
 */
function educare_change_event() {
	// parses query strings and sets the parsed values into the $_GET array.
	wp_parse_str($_POST['form_data'], $_GET);
	educare_show_attendance(13);
	die;
}

add_action('wp_ajax_nopriv_educare_change_event', 'educare_change_event');
add_action('wp_ajax_educare_change_event', 'educare_change_event');



/**
 *  Function to sort array by key
* @since 1.4.2
 * @last-update 1.4.2
 * 
 * @param $array array for sort
 * @param $sort_key specific key for sort
 * @return str
 */
function educare_sort_array_by_key($array, $sort_key) {
  // Find all unique keys across all sub-arrays
  $allKeys = array();
  foreach ($array as $item) {
    $allKeys = array_merge($allKeys, array_keys($item));
  }
	
  $allKeys = array_unique($allKeys);

  // Loop through each sub-array and add missing keys with empty values
  foreach ($array as &$item) {
    foreach ($allKeys as $key) {
      if (!isset($item[$key])) {
        $item[$key] = 0;
      }
    }
  }

  $key_array = array_column($array, $sort_key);
  // or SORT_ASC or SORT_DESC
  array_multisort($key_array, SORT_DESC, $array); 
  return $array;
}




/**
 * Verifies the nonce associated with a specific action before processing sensitive form submissions.
 *
 * The `educare_verify_nonce` function checks whether the submitted nonce is valid for the specified action
 * to prevent Cross-Site Request Forgery (CSRF) attacks. It is typically used in WordPress themes or plugins
 * when handling form submissions that require an additional layer of security.
 *
 * @param string $nonce (optional) A unique string representing the action or context for which the nonce was generated.
 * Default is 'educare_form_nonce' if not provided.
 * @param string $nonce_field for specific nonce field
 *
 * @return void The function displays an error message if the nonce is missing or invalid. Execution terminates
 * immediately after displaying the error, preventing further processing of the form submission.
 *
 * @example
 * Assuming you have defined your action (nonce) name as 'my_custom_action'
 * educare_verify_nonce('my_custom_action');
 *
 * Your form submission processing code comes here
 * ...
 */
function educare_verify_nonce($nonce = 'educare_form_nonce', $nonce_field = 'nonce', $get_method = false) {
	$nonce = sanitize_text_field( $nonce );
	$nonce_field = sanitize_text_field( $nonce_field );

	$method = $_POST;

	if ($get_method) {
		$method = $_GET;
	}

	// check_ajax_referer( 'educare_form_nonce', 'nonce' );
	if ( ! isset( $method[$nonce_field] ) || ! wp_verify_nonce( $method[$nonce_field], $nonce ) ) {
    // Nonce is not valid, handle error or unauthorized access
		echo educare_show_msg('Invalid Request', false);
		die;
	}
}



function educare_get_page_title($roles, $page = null) {
	$display_roles = $roles;

	if ($display_roles == 'teachers') {
		$display_roles = 'Staff';
	}

	$display_roles = sprintf(__(ucwords($page).' %s', 'educare'), esc_html__($display_roles, 'educare'));
	
	return '<h4 class="mb-4 p-2 text-center">'.esc_html($display_roles).'</h4>';
}


?>