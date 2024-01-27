<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/** 
* Include Educare Admin Menu
*	- All Students
*	- All Results
* - Mark Sheed
*	- Performance
*	- Management
*	- Settings
*	- About us
*/
require_once(EDUCARE_ADMIN.'menu.php');
// add grading systems fuctionality
require_once(EDUCARE_INC.'support/grading-systems.php');
// function for default/custom results card
require_once(EDUCARE_INC.'support/educare-default-results-card.php');
// Define default students photos
define('EDUCARE_STUDENTS_PHOTOS', EDUCARE_URL.'assets/img/default.jpg');
// Define Educare prefix
define('EDUCARE_PREFIX', 'educare_');



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
 * ### For check settings status
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
			$data = $print->data;
			$data = json_decode($data);
			// $id = $print->id;

			if (empty($target)) {
				return $data;
			}
		}
		
		if ($display) {
			$status = 'unchecked';
			
			if (property_exists($data->display, $target)) {
				$name = $data->display->$target;
				$value = $name[0];
				$status = $name[1];
			}

			if ($status == 'checked') {
				return $value;
			} else {
				return false;
			}
		} else {
			if (property_exists($data, $target)) {
				return $data->$target;
			} else {
				return false;
			}
		}
	}
}



/**
 * Checks whether the current user has the necessary permissions to access a specific feature or functionality in WordPress.
 *
 * This function is used to restrict access to certain parts of the website to users with specific privileges.
 *
 * @since 1.4.7
 * @last-update 1.4.7
 * 
 * @param string $msg Optional. The message to display to the user if they do not have the required permissions. Default is 'Sorry! You are not allowed to access it.'
 */
function educare_check_access($msg = 'Sorry! You are not allowed to access it.') {
	// Check if the current user has the 'manage_options' capability (typically administrators).
	if ( ! current_user_can( 'manage_options' ) ) {
		// Display an error message to the user and terminate script execution.
		echo educare_show_msg(esc_html__($msg, 'educare'), false);
		die;
	}
}




/**
 * ### Educare settings data
 * 
 * @since 1.2.0
 * @last-update 1.2.4
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
			$data = $print->data;
			$data = json_decode($data);
			// $id = $print->id;

			if (empty($target)) {
				return $data;
			}
		}
		
		if ($target) {
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
 * ### Notify user if anythink wrong in educare (database)
 * 
 * @since 1.2.0
 * @last-update 1.2.4
 * 
 * @param bool $fix_form		to get database update form
 * @param string $db				for specific database
 * 
 * @return void|HTML
 */

function educare_database_error_notice($fix_form = null, $db = null) {
	echo '<div class="educare_post">';

	if ($fix_form) {
		echo '<div class="logo"><img src="'.esc_url(EDUCARE_URL."assets/img/educare.svg").'" alt="Educare"/></div>';

		if (isset($_POST['update_educre_database'])) {
			global $wpdb;
			$database = array (
				'educare_settings',
				'educare_results',
				'educare_students',
				'educare_marks',
			);

			if ($db == 'educare_settings') {
				foreach ($database as $edb) {
					$edb = sanitize_text_field( $edb );
					$remove = $wpdb->prefix . $edb;
					$wpdb->query( $wpdb->prepare( "DROP TABLE %s", $remove ) );
				}

				// new database
				educare_database_table();
				
			} else {
				$edb = sanitize_text_field( $db );
				$edb = $wpdb->prefix.$edb;
				$wpdb->query( $wpdb->prepare( "DROP TABLE %s", $edb ) );

				// new db (table)
				educare_database_table($db);
			}
			
			echo "<div class='notice notice-success is-dismissible'><p>Successfully updated (Educare) database click here to <a href='".esc_url($_SERVER['REQUEST_URI'])."'>Start</a></p></div>";
		} else {
			?>
			<form class="add_results" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
				<div class="content">
					<b>Database Update Required</b>
					<p>Your current (Educare) database is old or corrupt, you need to update database to run new version <b><?php echo esc_html( EDUCARE_VERSION );?></b> of educare, it will only update <strong>Educare related database</strong>. Click to update database</p>
					<p><strong>Please note:</strong> You should backup your (Educare) database before updating to this new version (only for v1.0.2 or earlier users).</p>
					<button class="button" name="update_educre_database">Update Educare Database</button>
				</div>
			</form>
			<?php
		}
	} else {
		echo "<div class='notice notice-error is-dismissible'><p>Something went wrong!. Please go to (Educare) settings or <a href='/wp-admin/admin.php?page=educare-settings'>click here to fix</a></p></div>";
	}
	echo '<div>';
}



/**
 * Function for educare smart guideline
 * 
 * @since 1.0.0
 * @last-update v1.2.2
 * 
 * @param string $guide	  Specific string/msgs
 * @param string $details	Specific var/string
 * @param bool $success A boolean flag indicating whether it's a success message (true) or an error message (false).
 * 
 * @return string The HTML markup for the admin notice.
 */

function educare_guide_for($guide, $details = null, $success = true) {
	if (educare_check_status('guide') == 'checked') {
		$url = '/wp-admin/admin.php?page=educare-management&';

		if ($guide == 'add_class') {
			$guide = "Do you want to add more <b>Class</b>, <b>Exam</b> or <b>Year</b>? click here to add <a href='".esc_url($url . 'Class')."' target='_blank'>Class</a>, <a href='".esc_url($url . 'Exam')."' target='_blank'>Exam</a> or <a href='".esc_url($url . 'Year')."' target='_blank'>Year</a>";
		}
		
		if ($guide == 'add_extra_field') {
			$guide = "Do you want to add more <b>Field</b> ? click here to <a href='".esc_url($url . 'Extra_field')."' target='_blank'>Add extra field</a>";
		}
		
		if ($guide == 'add_subject') {
			$guide = "Do you want to add more <b>Subject</b> ? click here to <a href='".esc_url($url . 'Subject')."' target='_blank'>Add Subject</a>";
		}
		
		if ($guide == 'optinal_subject') {
			$guide = "If this student has an optional subject, then select optional subject. otherwise ignore it.<br><b>Note: It's important, when students will have a optional subject</b>";
		}

		if ($guide == 'display_msgs') {
			$guide = "It is not possible to deactivate both (<b>Regi number or Roll number</b>). Because, it is difficult to find students without roll or regi number. So, you need to deactivate one of them (Regi or Roll Number). If your system has one of these, you can select it. Otherwise, it is better to have both selected (<b>Recommended</b>).";
		}

		if ($guide == 'db_error') {
			$guide = "Database connections error. Make sure to alnabled Educare <b>(AI) Problem Detection</b> options. Also, you can go to plugin (Educare) settings and press <b>Reset Settings</b> to fix this error. If you unable to fix it, you can contact your developers or share in Educare support forum.";
		}

		if ($success) {
			$success = 'success';
		} else {
			$success = 'error';
		}

		return "<div class='notice notice-".esc_attr( $success )." is-dismissible'><p>".wp_kses_post($guide)."</p></div>";
	}
}




/**
 * Generates HTML markup for displaying success or error messages as WordPress admin notices.
 *
 * @param string $msg The message to be displayed in the notice.
 * @param bool $success A boolean flag indicating whether it's a success message (true) or an error message (false).
 * @param bool $sticky A boolean flag indicating whether the notice should be sticky (true) or not (false).
 *
 * @return string The HTML markup for the admin notice.
 */
function educare_show_msg($msg, $success = true, $sticky = true) {
	// Determine the notice type (success or error) based on the $success flag
	if ($success) {
		$notice_type = 'success';
	} else {
		$notice_type = 'error';
	}

	// Generate HTML markup for the admin notice based on the $sticky flag
	if ($sticky) {
		// If the notice is sticky, wrap it with a div having class 'sticky_msg'
		return "<div class='sticky_msg'><div class='notice notice-" . esc_attr($notice_type) . " is-dismissible'><p>" . wp_kses_post($msg) . "</p><button class='notice-dismiss'></button></div></div>";
	} else {
		// If the notice is not sticky, just generate the notice HTML without the 'sticky_msg' wrapper
		return "<div class='notice notice-" . esc_attr($notice_type) . " is-dismissible'><p>" . wp_kses_post($msg) . "</p></div>";
	}
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
 * @last-update 1.4.2
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

			if ($list == 'Class' or $list == 'Group') {
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
								$all_subject =  json_decode($all_subject, true);
								
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
			
			if ($list == 'Class' or $list == 'Group' or $list == 'Exam' or $list == 'Year') {
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
			echo "<div class='notice notice-error is-dismissible'><p>Currently, You don't have added any ".esc_html(str_replace('_', ' ', $list))." Please, <a href='".esc_url("/wp-admin/admin.php?page=educare-management&$list")."' target='_blank'>Click Here</a> to add ".esc_html(str_replace('_', ' ', strtolower($list))).".</p></div>";
		}
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
				foreach ($data[$target] as $subject) {
					$selected = '';
					$check = "";
					if ($subject == $value) {
						$selected = 'selected';
						$check = '✓';
					}

					echo '<option value="'.esc_attr($subject).'" '.esc_attr($selected).'>'.esc_html($subject).''.esc_html($check).'</option>';
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
 * Display specific class subject
 * 
 * Usage example: educare_get_subject('class name', $id);
 * 
 * @since 1.2.0
 * @last-update 1.2.0
 * 
 * @param string $class			Select class for get subject
 * @param int $id						Select specific database rows by id
 * 
 * @return string
 */

function educare_get_subject($class, $group, $id, $add_students = null) {

	if (isset($_POST['Add'])) {
		$id = 'add';
	}

	if (isset($_POST['Group'])) {
		$group = sanitize_text_field($_POST['Group']);
	}

	?>
	<table class="grade_sheet list">
		<thead>
			<tr>
				<th>No</th>
				<th>Subject</th>
				<?php 
				if (!$add_students) {
					?>
					<th>Marks</th>
					<th>Grade</th>
					<?php
				}
				?>
			</tr>
		</thead>
		
		<tbody>
			<?php 
			educare_get_options('Subject', $id, $class, $add_students);
			?>

			<tbody id="Group_list"></tbody>

		</tbody>
	</table>

	<div id="sub_msgs"></div>

	<div id="add_to_button">
		<div id='edit_add_subject' class='educare_button'>
			<i class='dashicons dashicons-edit'></i>
		</div>
	</div>
	
	<h4>Optional Subject</h4>
	
	<?php echo educare_guide_for('optinal_subject');?>

	<div class="select">
		<div>
			<p>Select Group:</p>
			<?php educare_options_by("Group", $group);?>
		</div>

		<div>
			<p>Optional Subject:</p>
			<select id="optional_subject" class="fields">
				<?php 
				echo '<option>None</option>';
				educare_get_options('optinal', $id, $class, $add_students);
				?>
			</select>
		</div>
			
	</div>
	
	<input type="hidden" id="optional" type="text">
	<?php
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
	$query = $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id );
	$results = $wpdb->get_row($query);

	if ($results) {
		if ($data == 'Details') {
			$details = json_decode($results->Details, true);
			$count = 1; // for add specific tags (div/tr/ul) in every 4 foreach loop

			foreach ($details as $key => $value) {
				if ($key == 'Photos') {
					break;
				}
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
			
			foreach ($subject as $name => $marks) {
				$mark = educare_display_marks($marks);
				echo "<tr>
				<td>".esc_html($serial++)."</td>
				<td>".esc_html(str_replace('_', ' ', $name))."</td>
				<td>".esc_html($mark)."</td>
				<td>".wp_kses_post(educare_letter_grade($marks))."</td>
				</tr>";
			}
		}

	} else {
		echo '<div class="error_results"><div class="error_notice">Something went wrong!</div></div>';
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
 * @param object $print		Get old data when update
 * 
 * @return null|HTML
 */

function educare_files_selector($type, $print) {
	wp_enqueue_media();
	$educare_save_attachment = get_option( 'educare_files_selector', 0 );
	
	$display = 'none';
	$default_photos = wp_get_attachment_url( get_option( 'educare_files_selector' ) );
	$educare_attachment_id = get_option( 'educare_files_selector' );
	
	if ($default_photos == null) {
		$default_img = EDUCARE_URL.'assets/img/default.jpg';
    } else {
		$default_img = $default_photos;
	}
	
	if ($type == 'update') {
		$img = $print->Photos;
    $img_type = "Students Photos";
		$guide = "If you change students photos, Please upload or select  a custom photos from gallery that's you want!";
	} else {
		$img = $default_img;
		$img_type = "Default Photos";
		$guide = "Current students photos are default. Please upload or select  a custom photos from gallery that's you want!";
	}

	if ($img == 'URL' or $img == '') {
		$img = $default_img;
	}

	if ($type != 'update') {
		$default_img = EDUCARE_URL.'assets/img/default.jpg';
	}
	
	?>

	<!-- Default value -->
	<div class="educare_data_field">
		<div class="educareFileSelector_educare_attachment_id" data-value="<?php echo esc_url($educare_attachment_id);?>"></div>
		<div class="educareFileSelector_default_img" data-value="<?php echo esc_url($default_img);?>"></div>
		<div class="educareFileSelector_img" data-value="<?php echo esc_attr($img);?>"></div>
		<div class="educareFileSelector_img_type" data-value="<?php echo esc_html($img_type);?>"></div>
		<div class="educareFileSelector_guide" data-value="<?php echo esc_html($guide);?>"></div>
	</div>
	
	<div id='educare_files_selector_disabled'>
		<div id='educare_files_uploader' class='educare_upload add'>
			<div class='educare_files_selector'>
				<img id='educare_attachment_preview' class='educare_student_photos' src='<?php echo esc_url($img);?>'/>
				
				<h3 id='educare_img_type' class='title'><?php echo esc_html($img_type);?></h3>
			</div>
			
			<p id='educare_guide'><?php echo esc_html($guide);?></p>
			<div id='educare_default_help'></div>
				
			<input type="hidden" name='Photos' id='educare_attachment_url' value='<?php echo esc_attr(esc_url($img));?>'>
		
			<input type='button' id='educare_attachment_title' class="button full" value='Please Select a students photos' disabled>
			
			<input type='button' id='educare_attachment_clean' class='button educare_clean full' value='&#xf171 Undo' style='display: <?php echo esc_attr($display);?>'>

			<div class="select">
				<input id="educare_upload_button" type="button" class="button" value="<?php _e( 'Upload Students Photos' ); ?>"/>

				<?php
				if ($type == 'add_results') {
					echo "<input type='hidden' id='educare_attachment_default'>";
				} else {
					if ($img != $default_img) {
						echo "<input type='button' id='educare_attachment_default' class='button' onClick='".esc_js('javascript:;')."' value='Use Default photos'>";
					} else {
						echo "<input type='hidden' id='educare_attachment_default'>";
					}
				}
				?>
			</div>

			<input type="hidden" name='educare_attachment_id' id='educare_attachment_id' value='<?php echo esc_attr(get_option( 'educare_files_selector' )); ?>'>

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
 * sample array
$array = array(
  'Roll_No' => 1,
  'Regi_No' => 2,
  'Year' => 2022,
  'Class' => 'Class 6',
  'Exam' => ''
);
 */


/**
 * Requred form fields
 * 
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

  foreach ($default as $key => $val) {
    if ($all) {
      if ($value) {
        $requred[$key] = $val[0];
      } else {
        array_push($requred, $key);
      }
    } else {
      if ($val[1] == 'checked') {
        
        if ($value) {
          $requred[$key] = $val[0];
        } else {
          array_push($requred, $key);
        }
        
      }
    }
  }

  return $requred;
}





/**
 * Combine fields from two arrays into a new associative array while optionally ignoring specified keys.
 *
 * @param array $array1 The first array of fields to combine.
 * @param array|null $ignore An optional array of keys to ignore in the resulting array.
 * @param array|null $array2 An optional second array to combine with the first array. If not provided, it uses $_POST.
 * @param bool|null $normal An optional flag to indicate whether to use normal or required data for $array1.
 *
 * @return array The combined array containing values from $array2 with keys from $array1.
 * 
 * @since 1.3.0
 * @last-update 1.3.0
 * 
 */
function educare_combine_fields($array1, $ignore = null, $array2 = null, $normal = null) {
	// If $normal is not specified, use educare_requred_data function to get required data from $array1
	if (!$normal) {
		$array1 = educare_requred_data($array1);
	}

	// If $array2 is not specified, use $_POST as the second array
	if (!$array2) {
		$array2 = $_POST;
	}

	// Initialize an empty array to store the combined values
	$combine = array();

	// Iterate through the elements of $array1
	foreach ($array1 as $value) {
		// Check if the key exists in $array2
		if (key_exists($value, $array2)) {
			// Sanitize the text field value and add it to the combined array
			$combine[$value] = sanitize_text_field($array2[$value]);
		} else {
			// If the key does not exist in $array2, set its value to false in the combined array
			$combine[$value] = false;
		}
	}

	// If $ignore array is provided, remove the specified keys from the combined array
	if ($ignore) {
		foreach ($ignore as $remove) {
			unset($combine[$remove]);
		}
	}

	return $combine;
}




/**
 * Check if specific array key is empy or not
 * Same as array_keys($array, null);
 * 
 * @since 1.3.0
 * @last-update 1.4.0
 * 
 * @param array $array			for check empty
 * @param bool $normal 			for ignore educare settings status
 * @param bool $text_only		To return messege only (without <p> tag)
 * @return bool|string
 */

function educare_is_empty(array $array, $normal = null, $text_only = null) {
  $empty_key = array();

  // Loop to find empty elements 
  foreach($array as $key => $value) {
		if ($normal) {
			$val = $key;
			$val = str_replace('_', ' ', $val);
		} else {
			$val = educare_check_status($key, true);
		}

    if(empty($value)) {
      // return empty elements key
      array_push($empty_key, $val);
    }
  }

  // return $empty_key;
  if ($empty_key) {
		$msg = 'You must fill <b>' . implode(', ', $empty_key) . '</b>';

		if ($text_only) {
			return $msg;
		} else {
			$msgs = "<div class='notice notice-error is-dismissible'><p>";
			$msgs .= $msg;
			$msgs .= "</p></div>";
			return $msgs;
		}

  } else {
    return false;
  }
}



/** 
 * ### Auto create sql command
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
 * @param array $cond				for specific condition like AND, OR
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
 * Gennarete dynamic sql
 * 
 * @param string $roles           for select table
 * @param array $requred_fields   for generate sql
 * @param bool $crud              if data for CRUD process
 * 
 * @return string (sql)
 */

 function educare_get_dynamic_sql($roles, $requred_fields, $crud = false) {
	global $wpdb;
	// Define table name
	$table = $roles;
	$table_name = $wpdb->prefix.EDUCARE_PREFIX.$table;

	// Build the SELECT query
	if ($requred_fields) {
		$sql = "SELECT * FROM $table_name WHERE ";
	} else {
		$sql = "SELECT * FROM $table_name ";
	}
	
	$prepared_values = array();

	foreach ($requred_fields as $key => $value) {
		// We need to encrypt the plain text password using wp_hash_password() to match the stored encrypted password in the user_pass field. But  wp_hash_password() generates a different hash each time it's called, even for the same password. In that case, you won't be able to directly compare the encrypted password stored in the database with the hashed password generated by wp_hash_password(). To verify the password, you can use the wp_check_password() function instead.
		if ($key == 'user_pass') {
			continue;
			// $value = wp_check_password($value);
		}

		$sql .= "`$key`=%s AND ";

		$prepared_values[] = $value;
	}

	// Check to ignore specific ID
	if ($crud) {
		if (isset($_POST['id']) && !empty($_POST['id'])) {
			$id = sanitize_text_field( $_POST['id'] );
			$sql .= $wpdb->prepare('id <> %d AND ', $id);
		}
	}
	
	// Remove the last 'AND'
	$sql = rtrim($sql, 'AND ');
	$sql = $wpdb->prepare($sql, $prepared_values);
	return $sql;
}



/**
 *  Add/Edit/Delete students and results
 * Processing students and results forms
 * 
 * @since 1.3.0
 * @last-update 1.3.0
 * 
 * @param bool $add_students		if data for students
 * @param bool $import_data			if data for import system
 * @return mixed
 */

function educare_crud_data($add_students = null, $import_data = null) {
  global $wpdb, $update_data, $table_name, $requred_title, $requred_data, $requred_fields, $msg, $import_from;
	$table_name = $wpdb->prefix . 'educare_'.$add_students.'';
	$msg = $add_students;

	if ($add_students != 'results') {
    $ignore = array(
			'Name',
      'Exam'
    );
  } else {
		$ignore = array(
			'Name'
    );
  }
  
  $requred = educare_check_status('display');
	$requred_title = educare_requred_data($requred, true, true);
  $requred_fields = educare_combine_fields($requred, $ignore);
  $requred_data = educare_combine_fields($requred);

  // show error/success notice
  function notice($msgs, $print = null, $add_students = null) {
    global $requred_data, $requred_title, $msg;

    foreach ($requred_data as $key => $value) {
      $$key = sanitize_text_field($value);
    }

    foreach ($requred_title as $key => $value) {
      $var = strtolower($key);

			if (isset($requred_data[$key])) {
				if ($print) {
					if(property_exists($print, $key)) {
						$$var = "<br>$value: <b>".$print->$key."</b>";
					}
				} else {
					$$var = "<br>$value: <b>$requred_data[$key]</b>";
				}
			} else {
				$$var = '';
			}
    }
    
    if ((isset($_POST['id']))) {
      $id = sanitize_text_field($_POST['id']);
    } elseif ($print) {
      $id = $print->id;
    } else {
      $id = '';
    }

		$link = admin_url();
		$link .= 'admin.php?page=educare-all-'.$add_students.'';
		
		if ($add_students == 'results') {
			$profiles = '/'.educare_check_status("results_page");
		} else {
			$profiles = $link . '&profiles=' . $id;
		}

		// Security nonce for form requests.
		$nonce = wp_create_nonce( 'educare_form_nonce' );
		$crud_nonce = wp_create_nonce( 'educare_crud_data' );
      
    $forms = "<form method='post' action='' class='text_button'>
			<input type='hidden' name='nonce' value='".esc_attr($nonce)."'>
			<input type='hidden' name='delete_nonce' value='".esc_attr($crud_nonce)."'>
      <input name='id' value='".esc_attr($id)."' hidden>
      <input type='submit' name='educare_results_by_id' formaction='".esc_url($profiles)."' class='educare_button' value='&#xf177' formtarget='_blank'>
			<input type='submit' name='edit_by_id' formaction='".esc_url($link)."&update-data' class='educare_button' value='&#xf464'>
			<input type='submit' name='delete' formaction='".esc_url($_SERVER['REQUEST_URI'])."' class='educare_button' value='&#xf182' onClick='".esc_js( 'return educareConfirmation()' )."'>
    </form>";
    
    // create and show msgs
    if ($msgs == 'added' or $msgs == 'updated') {
      echo "<div class='notice notice-success is-dismissible'><p>";
        echo "Successfully ".esc_html($msgs)." ".esc_html($msg)."." . wp_kses_post($name . $class . $roll_no . $regi_no) . $forms;
      echo "</p></div>";
    }
    
    if ($msgs == 'exist') {
      echo "<div class='notice notice-error is-dismissible'><p>Sorry, ".esc_html($msg)." is allready exist." . wp_kses_post($name . $class) . $forms;
      echo "</p></div>";
    }
    
    if ($msgs == 'not_found') {
      echo "<div class='notice notice-error is-dismissible'><p>Result not found. Please try again</p></div>";
    }
  }

  function educare_insert_data($add_students = null) {
		// Check user capability to manage options
		if (!current_user_can('manage_options')) {
			exit;
		}
		
		// Verify the nonce to ensure the request originated from the expected source
		educare_verify_nonce('educare_crud_data');
		
    global $wpdb, $table_name, $requred_fields;
		
		if (educare_check_status('Name', true)) {
			$data['Name'] = sanitize_text_field($_POST['Name']);
		}

    foreach ($requred_fields as $key => $value) {
      $data[$key] = $value;
    }

		if (isset($_POST['Group'])) {
			$data['Group'] = sanitize_text_field($_POST['Group']);
		} else {
			$data['Group'] = '';
		}

    // unset($requred_fields['Year']);
    $Photos = sanitize_text_field($_POST['Photos']);
    $Details = educare_array_slice($_POST, 'Year', 'end_exatra_fields');

		if ($Photos == esc_url('URL')) {
			$Details['Photos'] = 'URL';
		} else {
			$Details['Photos'] = $Photos;
		}
    
    $Details = json_encode($Details);

    $data['Details'] = $Details;

    if ($add_students != 'results') {
      $Subject = educare_array_slice($_POST, 'end_exatra_fields', 'Group');
      $Subject = json_encode($Subject);
			$data['Subject'] = $Subject;
    } else {
			$Subject = educare_array_slice($_POST, 'GPA', 'Group');
      $Subject = json_encode($Subject);
      $Result = sanitize_text_field($_POST['Result']);
      $GPA = sanitize_text_field($_POST['GPA']);

      $data['Subject'] = $Subject;
      $data['Result'] = $Result;
      $data['GPA'] = $GPA;
		}
    
    if (isset($_POST['id'])) {
      $id = sanitize_text_field($_POST['id']);
      $wpdb->update($table_name, $data, array('ID' => $id));
    } else {
      $wpdb->insert($table_name, $data);
    }
    
    // Show success msgs
    if($wpdb->insert_id > 0) {
      // echo 'Added';
      $id = $wpdb->insert_id;
			$query = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id);
      $insert_data = $wpdb->get_row($query);
			notice('added', $insert_data, $add_students);
    } else {
      // echo 'Updated';
			$query = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id);
      $insert_data = $wpdb->get_row($query);
			notice('updated', $insert_data, $add_students);
    }
  }

	if (isset($_POST['id'])) {
		$id = sanitize_text_field($_POST['id']);
	} else {
		$id = false;
	}

  if (!educare_is_empty($requred_fields) or $id) {
    if (isset($_POST['id']) and !isset($_POST['update'])) {
      $sql = "id='$id'";
    } else {
      $sql = educare_get_sql($requred_fields);
    }
    
    $select = "SELECT * FROM $table_name WHERE $sql";
    $results = $wpdb->get_results($select);

    if ($results) {
      
      foreach ($results as $print) {
				
				if ($import_data) {
					return $print;
				}

        if (isset($_POST['edit']) or isset($_POST['edit_by_id'])) {
          // return $results;
          // echo 'edit forms';
          if ($add_students != 'results') {
            educare_get_results_forms($print, true);
          } else {
            educare_get_results_forms($print);
          }
        } elseif (isset($_POST['update'])) {

          if ($id == $print->id) {
            // ignore current data
            $update_data = true;
            // global $print;
            continue;
          } else {
            // duplicate data exist
            notice('exist', $print, $add_students);
            return;
          }
          
        } elseif (isset($_POST['delete'])) {
					// Check user capability to manage options
					if (!current_user_can('manage_options')) {
						exit;
					}

					// Verify the nonce to ensure the request originated from the expected source
					if (isset($_POST['delete_nonce'])) {
						educare_verify_nonce('educare_crud_data', 'delete_nonce');
					} else {
						educare_verify_nonce('educare_crud_data');
					}
					
					
					$query = $wpdb->prepare("DELETE FROM $table_name WHERE id = %d", $id);
          $wpdb->query($query);
          echo '<div class="notice notice-success is-dismissible"><p>Succesfully deleted '.esc_html($msg).'.</p></div>';
          return;
        } else {
          // if action for add but data already exist 
          // if (isset($_POST['Add']))
					notice('exist', $print, $add_students);
          return;
        }

      }

    } else {

      // if action for add data
      if (isset($_POST['Add'])) {

				educare_insert_data($add_students);

      } elseif (isset($_POST['update'])) {
        $update_data = true;
      } else {
        echo "<div class='sticky_msg'><div class='notice notice-error is-dismissible'><p>Sorry, ".esc_html($msg)." not found. Please try again</p></div></div>";

				if (!$import_from) {
					educare_get_search_forms();
				}
      }
      
    }

  } else {
    // Empty requred fields
    if ($_POST) {
      echo educare_is_empty($requred_fields, 'display');
    }
    
    if (isset($_POST['edit']) or isset($_POST['edit_by_id'])) {
			educare_get_search_forms();
    }

    return;
  }
  
  if ($update_data) {
		educare_insert_data($add_students);
  }

}



/**
 * Print students results forms for add/update/delete students results
 * 
 * Usage example: educare_get_results_forms($print, 'add/update')
 * 
 * it's only print forms field (Name, Class, Exam, Roll No, Regi No, Year...)
 * required educare_crud_data() function for work properly
 * Actually, this function only for print forms under educare_crud_data();
 * 
 * @since 1.0.0
 * @last-update 1.4.2
 * 
 * @param object $print				Getting object value
 * @param bool $add_students		if forms for add students (since 1.2.4)
 * 
 * @return null||HTML
 */

function educare_get_results_forms($print, $add_students = null) {
	global $requred_title, $requred_data, $requred_fields, $import_from;

	foreach ($requred_data as $key => $value) {
		if ($print) {
			$id = $print->id;
			$Group = $print->Group;

			if ($import_from) {
				$submit = 'Add';
			} else {
				$submit = 'update';
			}

			if (property_exists($print, $key)) {
				$$key = $print->$key;
			} else {
				$$key = $value;
			}
		} else {
			$id = $Group = '';
			$$key = sanitize_text_field($value);
			$submit = 'Add';
		}
	}

	?>
	
	<div id="educare-form">
		<form id="crud-forms" class="add_results" action="" method="post">
			<div class="content">
				
				<?php 
				// Security nonce for form requests.
				$nonce = wp_create_nonce( 'educare_crud_data' );
				echo '<input type="hidden" name="nonce" value="'.esc_attr($nonce).'">';
				
				if (isset($_POST['edit']) or isset($_POST['edit_by_id']) or $import_from) {
					$photos = $print->Details;
					$photos = json_decode($photos);
					
					educare_files_selector('update', $photos);

					if (!$import_from) {
						echo "<input type='hidden' id='id_no' name='id' value='".esc_attr($id)."'/>";
					}
				} else {
					// echo "<input type='hidden' id='id_no'>";
					educare_files_selector('add_results', '');
				}
				?> 
				<h2>Students Details</h2>
					
				<div class="select">
					<label for="Class" class="labels" id="class"></label>
					<label for="Exam" class="labels" id="exam"></label>
				</div>
				
				<?php
					$check_name = educare_check_status('Name', true);
					if ($check_name) {
						echo '<p>'.esc_html($check_name).':</p>
						<label for="Name" class="labels" id="name"></label>
						<input type="text" name="Name" value="'.esc_attr($Name).'" placeholder="Enter '.esc_html($check_name).'">
						';
					}

					if (key_exists('Roll_No', $requred_fields)) {
						echo '<p>'.esc_html($requred_title['Roll_No']).':</p>
						<label for="Roll_No" class="labels" id="roll_no"></label>
						<input type="number" name="Roll_No" value="'.esc_attr($Roll_No).'" placeholder="Enter '.esc_html($requred_title['Roll_No']).'">
						';
					}

					if (key_exists('Regi_No', $requred_fields)) {
						echo '<p>'.esc_html($requred_title['Regi_No']).':</p>
						<label for="Regi_No" class="labels" id="regi_no"></label>
						<input type="text" name="Regi_No" value="'.esc_attr($Regi_No).'" placeholder="Enter '.esc_html($requred_title['Regi_No']).'">
						';
					}
				?>
				
				<?php echo educare_guide_for('add_class');?>

				<div class="select">
					<select id="Class" name="Class" class="form-control">
						<?php educare_get_options('Class', $Class);?>
					</select>
				
				<?php 
				if (key_exists('Exam', $requred_fields) or $import_from) {
					?>
					<select id="Exam" name="Exam" class="fields">
						<?php educare_get_options('Exam', $Exam);?>
					</select>
					<?php
				}

				if (!$add_students) {
					echo '</div> <div class="select">';
				}

				if (key_exists('Year', $requred_fields)) {
					?>
					<select id="Year" name="Year" class="fields">
						<?php educare_get_options('Year', $Year);?>
					</select>
					<?php
				}

				if (!$add_students) {
					echo '<div id="data_from_students" title="Get data/details from specific student profiles. For this, you need to fill roll no, regi no, class and year."><div class="educare_button">Auto Fill</div></div>';
				}
				
				?>

				</div>
				
				<?php
				if ($add_students) {
					echo educare_guide_for('Premium version of educare supports user (Students, Teachers, Educare Admin) profiles/dashboard system.');
				}
				?>
					
				<!-- Extra field -->
				<h2>Others</h2>
				<?php
				echo educare_guide_for('add_extra_field');
				
				if (isset($_POST['Add'])) {
					educare_get_options('Extra_field', 'add');
				} else {

					if ($add_students) {
						educare_get_options('Extra_field', $id, '', true);
					} else {
						educare_get_options('Extra_field', $id);
					}
					
				}

				echo '<input type="hidden" name="end_exatra_fields" value="true">';

				if (!$add_students) {
					?>
					<h2>Students Results</h2>
					
					<?php
					if (educare_check_status('auto_results') == 'checked') {
						$disabled = 'disabled';
						
						echo educare_guide_for('Currently you can not modify (Result, GPA and Grade options. For this disable <b>Auto Result</b> system from educare (plugins) settings. Click here to <a href="/wp-admin/admin.php?page=educare-settings#settings" target="_blank">disable auto results</a>');

						echo '<input type="hidden" name="Result" value="'.esc_attr(educare_value('Result', $id)).'">';
						echo '<input type="hidden" name="GPA" value="'.esc_attr(educare_value('GPA', $id)).'">';
					} else {
						$disabled = '';
					}
					?>

					<div class="select">
						<p>Result:</p>
						<p>GPA:</p>
					</div>
					<div class="select">
						<select name="Result" class="form-control" <?php echo esc_attr( $disabled );?>>
						<?php if (isset($_POST['Add'])) { echo '<option>Select Status</option>'; }?>
							<option value="Passed" <?php if (educare_value('Result', $id) == 'Passed') { echo 'Selected'; }?>>Passed</option>
							<option value="Failed" <?php if (educare_value('Result', $id) == 'Failed') { echo 'Selected'; }?>>Failed</option>
						</select>
						
						<input type="number" name="GPA" class="fields" value="<?php echo esc_attr(educare_value('GPA', $id));?>" placeholder="0.00" step="any" <?php echo esc_attr( $disabled );?>>
					</div>
					<?php
				}
				?>

				<?php 
				echo educare_guide_for('add_subject');

				if (!$add_students) {
					echo educare_guide_for('With the premium version of Educare, you can add additional mark terms and fields. Exp: Practical Marks, Exam Marks, CA1, CA2, CA3... and more. Also, you can secure the result with password or PIN.');
				}
				?>
				<div id="result_msg">
					<?php educare_get_subject($Class, $Group, $id, $add_students) ?>
				</div>

				<br>
				<?php
				// if ($submit != 'Add') { 
				// 	echo educare_guide_for('If you want to update old class data ('.esc_html($requred_title['Name']).', '.esc_html($requred_title['Roll_No']).', '.esc_html($requred_title['Regi_No']).', Details) please check it otherwise uncheck.');

				// 	echo '<input type="checkbox" name="update_old_data" checked> Update old data <br>';
				// }

				if ($add_students) {
					$btn_value = 'Students';
				} else {
					$btn_value = 'Results';
				}
				?>

				<button type="submit" name="<?php echo esc_attr($submit);?>" class="educare_button educare_crud"><i class="dashicons dashicons-<?php if ($submit == 'Add') {echo 'plus-alt';}else{echo 'edit';}?>"></i> <?php echo esc_html($submit .' '. $btn_value);?></button>
						
				<?php
				// remove delete button when Add results
				if ($submit != 'Add') {
					?>
						<button type="submit" name="delete" class="educare_button" onClick="<?php echo esc_js( 'return educareConfirmation()' )?>"><i class="dashicons dashicons-trash"></i>Delete</button>
					<?php
				}
				?>
				
			</div>
		</form>
	</div>
	<?php
}



/**
 * Process form when click auto fill button
 * 
 * @since 1.4.0
 * @last-update 1.4.7
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

	$roll = sanitize_text_field($_POST['Roll_No']);
	$regi = sanitize_text_field($_POST['Regi_No']);
	$class = sanitize_text_field($_POST['Class']);
	$year = sanitize_text_field($_POST['Year']);

	$_POST = array (
		'Roll_No' => $roll,
		'Regi_No' => $regi,
		'Class' => $class,
		'Year' => $year,
	);

	global $import_from;
	$import_from = 1;

	$print = educare_crud_data('students', true);

	educare_get_results_forms($print, '');
	
	die;
}

// Add the 'educare_get_data_from_students' function as an AJAX action
add_action('wp_ajax_educare_get_data_from_students', 'educare_get_data_from_students');



/**
 * Display forms for search students results
 * 
 * Search specific results for Edit/Delete/View
 * Search results by Class, Exam, Year, Roll & Regi No for Edit/Delete/View specific results.
 * Admin can Edit/Delete/View the results.
 * Users only view the results.
 * 
 * @since 1.0.0
 * @last-update 1.4.1
 * 
 * @return null||HTML
 */

function educare_get_search_forms($front = null) {
	global $requred_fields, $requred_data, $requred_title;
	$custom_results = educare_check_status('custom_results');

	foreach ($requred_data as $key => $value) {
		$$key = sanitize_text_field($value);
		$title = strtolower($key);
		$$title = $requred_title[$key];
	}

	if ($custom_results == 'checked' and has_action('educare_custom_results_forms') and $front) {
		return do_action( 'educare_custom_results_forms');
	} else {
		?>
		<div class="results_form">
			<form class="add_results" action="" method="post" id="educare_search_forms">
				<div class="content">
					<?php
					// Security nonce for AJAX requests.
					$nonce = wp_create_nonce( 'educare_form_nonce' );
					echo '<input type="hidden" name="nonce" value="'.esc_attr($nonce).'">';

					echo '<div class="select add-subject">';
						if (key_exists('Class', $requred_fields)) {
							?>
							<div>
								<p><?php echo esc_html($class);?>:</p>
								<select id="Class" name="Class" class="fields">
									<?php educare_get_options('Class', $Class);?>
								</select>
							</div>
							<?php
						}

						if (key_exists('Exam', $requred_fields)) {
							?>
							<div>
								<p><?php echo esc_html($exam);?>:</p>
								<select id="Exam" name="Exam" class="fields">
									<?php educare_get_options('Exam', $Exam);?>
								</select>
							</div>
							<?php
						}
					echo '</div>';

					if (key_exists('Roll_No', $requred_fields)) {
						echo '<p>'.esc_html($roll_no).':</p>
						<label for="Roll_No" class="labels" id="roll_no"></label>
						<input type="number" name="Roll_No" value="'.esc_attr($Roll_No).'" placeholder="Enter '.esc_attr($roll_no).'">
						';
					}

					if (key_exists('Regi_No', $requred_fields)) {
						echo '<p>'.esc_html($regi_no).':</p>
						<label for="Regi_No" class="labels" id="regi_no"></label>
						<input type="text" name="Regi_No" value="'.esc_attr($Regi_No).'" placeholder="Enter '.esc_attr($regi_no).'">
						';
					}
					?>
					
					<div>
						<p>Select Year:</p>
						<select id="Year" name="Year" class="fields">
							<?php educare_get_options('Year', $Year);?>
						</select>
					</div>

					<?php
					if ($front) {

						if (educare_check_status('re_captcha') == 'checked') {
							$site_key = educare_check_status('site_key');

							if ( current_user_can( 'manage_options' ) and $site_key == '' ) {
								echo educare_guide_for('<small>The Google Recaptcha checkbox field is hidden. Please enter/paste your google recaptcha v2 site key at <br><a href="'.esc_url( admin_url() ).'/admin.php?page=educare-settings&menu=Security" target="_blank"><code>Educare > Settings > Security > Site Key</code></a><br><br><small>(Only admin can view these messages)</small>', '', false);
							}

							echo '<div class="g-recaptcha" data-sitekey="'.esc_attr($site_key).'"></div>';
						}

						echo '<button id="results_btn" class="results_button button" name="educare_results" type="submit">View Results </button>';
					} else {
						echo '<button id="edit_btn" name="edit" type="submit" class="educare_button"><i class="dashicons dashicons-search"></i> Search for edit</button>';
					}
					?>

				</div>
			</form>
		</div>
		<?php
	}
}



/** 
 * ### educare_get_data_management('results')
 * 
 * @since 1.4.0
 * @last-update 1.4.0
 * 
 * @param bool $add_students		if data for students
 * 
 * @return null|HTML
 */

function educare_get_data_management($students) {

	if ($students == 'students') {
		$icon = 'businessman';
	} elseif ($students == 'results') {
		$icon = 'id-alt';
	} else {
		$icon = 'businessperson';
	}

	?>
	<div class="container educare-page">

		<div class="tab students">
			<button class="tablinks active" id="default" title="View all <?php echo esc_attr($students)?>" data="all-data"><i class="dashicons dashicons-<?php echo esc_attr($icon)?>"></i><span>All</span></button>
			<button class="tablinks" title="Add new <?php echo esc_attr($students)?>" data="add-data"><i class="dashicons dashicons-plus-alt"></i><span>Add</span></button>
			<button class="tablinks" title="Update <?php echo esc_attr($students)?> Data" data="update-data"><i class="dashicons dashicons-update"></i><span>Edit</span></button>
			<button class="tablinks" title="Import <?php echo esc_attr($students)?>" data="import-data"><i class="dashicons dashicons-database-import"></i><span>Import</span></button>
		</div>
		
		<div class="educare_post">
			<div id="educare-data">
				<?php educare_data_management($students);?>
			</div>
		</div> <!-- / .educare Settings -->

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

function educare_tab_management($action_for = 'management', array $tab = null) {

	if (!$tab) {
		$tab = array (
			// tab name => icon
			'Class' => 'awards',
			'Group' => 'groups',
			'Exam' => 'welcome-write-blog',
			'Year' => 'calendar',
			'Extra_field' => 'welcome-add-page',
		);
	}

	echo '<div class="container educare-page">';
		echo '<div class="tab tab_management">';
			$activate = array_key_first($tab);
			$active_tab = '';

			foreach ($tab as $name => $icon) {
				$title = ucwords(str_replace('_', ' ', $name));

				if ($name == $activate) {
					$activate = 'active';
				} else {
					$activate = '';
				}

				echo '<button class="tablinks '.esc_attr($activate).'" id="'.esc_attr($name).'" title="Manage '.esc_html($title).'"><i class="dashicons dashicons-'.esc_attr($icon).'"></i><span>'.esc_html($title).'</span></button>';

				if ( isset($_GET[$name])) {
					$active_tab = sanitize_text_field( $name );
				}

			}

		echo '</div>';
		?>
		
		<div class="educare_post educare_settingss <?php echo esc_attr($action_for) ?>">
			<div id="educare-data">
				<?php educare_get_tab_management($action_for);?>
			</div>
		</div>

	</div>

	<?php
	$url = admin_url();
	$url .= 'admin.php?page=educare-' . esc_attr($action_for);

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
		<div class="educareTabManagement_menu" data-value="<?php echo esc_attr($menu);?>"></div>
		<div class="educareTabManagement_active_tab" data-value="<?php echo esc_attr($active_tab);?>"></div>
	</div>
	
	<?php
}




/**
 * AJAX callback function to process a specific tab in the educare tab area.
 *
 * The `educare_process_tab` function is an AJAX callback function that handles the request to process a specific tab in the educare management area.
 *
 * The function performs the following tasks:
 * - Retrieves the action for the tab from the AJAX request.
 * - If the 'tab' parameter is set in the AJAX request, it sets the corresponding GET parameter to true.
 * - Calls the `educare_get_tab_management` function to process the specified tab.
 * - Terminates the script execution and sends the response as JSON.
 *
 * Note: The `educare_get_tab_management` function, which is called within this AJAX callback, is not provided in the code snippet. It is assumed that this function exists and handles the processing of the specified tab.
 * 
 * @since 1.4.0
 * @last-update 1.4.7
 */
function educare_process_tab() {
	// Check if the current user has the access this request as 'manage_options' capability (typically administrators).
	educare_check_access();
	
	// Get the action for the tab from the AJAX request
	$action_for = $_POST['action_for'];

	// Set the 'tab' parameter in GET if it is set in the AJAX request
	if (isset($_POST['tab'])) {
		$_GET[$_POST['tab']] = true;
	}

	// Call the function to process the specified tab
	educare_get_tab_management($action_for);

	// Terminate the script execution and send the response as JSON
	die;
}

// Add the 'educare_process_tab' function as an AJAX action
add_action('wp_ajax_educare_process_tab', 'educare_process_tab');




/** 
 * Proccess ajax request from tab button and display data
 * 
 * @since 1.4.0
 * @last-update 1.4.2
 * 
 * @param string $action_for		$_GET request for ajax response
 * @return mixed
 */

function educare_get_tab_management($action_for) {
	
	if ($action_for == 'management') {
		if (isset($_GET['Group'])) {
			echo "<h1>Group List</h1>";
	
			// Group list
			echo '<div id="msg_for_Group">';
				educare_setting_subject("Group");
			echo '</div>';
			
			// Group forms
			educare_setting_subject("Group", true);
		} elseif (isset($_GET['Exam'])) {
			echo "<h1>Exam List</h1>";
			educare_get_all_content('Exam');
		} elseif (isset($_GET['Year'])) {
			echo "<h1>Year List</h1>";
			educare_get_all_content('Year');
		} elseif (isset($_GET['Extra_field'])) {
			echo "<h1>Extra Field</h1>";
			educare_get_all_content('Extra_field');
		} else {
			echo '<div class="cover"><img src="'.esc_url(EDUCARE_URL.'assets/img/cover.svg').'" alt="educare cover"/></div>';
			// Class list
			echo '<div id="msg_for_Class">';
				educare_setting_subject("Class");
			echo '</div>';
	
			// Class forms
			educare_setting_subject("Class", true);
		}
		
		return;
	} elseif ($action_for == 'mark-sheed') {
		if (isset($_GET['import_marks'])) {
			echo "<h1>Import Marks</h1>";

			echo '<div id="msgs" style="text-align:center;">';
			echo '<span style="font-size:100px">&#9785;</span><br><b>We are working on it!</b>';
			echo '</div>';

		} elseif (isset($_GET['attendance'])) {
			echo "<h1>Attendance</h1>";

			echo educare_guide_for('Premium version of Educare supports attendance system.');

			echo '<div class="center"><img src="'.esc_url(EDUCARE_URL . 'assets/img/cover.svg').'" alt="Educare" width="50%"/></div>';
			
		} else {
			echo '<div class="cover"><img src="'.esc_url(EDUCARE_URL.'assets/img/marks.svg').'" alt="Marks List" title="Add Marks"/></div>';
			echo "<h1>Add Marks</h1>";

			echo educare_guide_for("<p>Using this features admin (teachers) can add subject wise multiple students results at a same time. So, it's most usefull for (single) teacher. This is particularly advantageous for individual teachers handling their own subjects. And can print all student marks as a marksheet. Once the mark entry process concludes for all subjects, students can easily access and print their results once the administrator publishes them as results</p>
			
			<p><b>Notes:</b> With the premium version, administrators have the capability to add teachers and grant them access to specific subjects to input marks!</p>
			");
			
			$Class = $Group = $Exam = $Subject = $Year = '';

			if (isset($_POST['students_list'])) {
				$Class = sanitize_text_field($_POST['Class']);
				$Group = sanitize_text_field($_POST['Group']);
				$Exam = sanitize_text_field($_POST['Exam']);
				$Subject = sanitize_text_field($_POST['Subject']);
				$Year = sanitize_text_field($_POST['Year']);
			}
			?>
	
			<form method='post' action="" class="add_results educareProcessMarksCrud">
				<div class="content">
				<div class="select">
						<select id="Class" name="Class" class="form-control">
						<option value="">Select Class</option>
							<?php educare_get_options('Class', $Class);?>
						</select>

						<select id="Group" name="Group" class="form-control">
						<option value="">Select Group</option>
							<?php educare_get_options('Group', $Group);?>
						</select>
					</div>

					<div class="select">
						<select id="Exam" name="Exam" class="form-control">
							<?php educare_get_options('Exam', $Exam);?>
						</select>

						<select id="Subject" name="Subject" class="form-control">
							<option value="">Select Subject</option>
						</select>
					</div>

					<div class="select">
						<div>
						<p>Select Year:</p>
							<select id="Year" name="Year" class="form-control">
								<?php educare_get_options('Year', $Year);?>
							</select>
						</div>

						<div>
							<p>Students Per Page:</p>
							<input id="results_per_page" type="number" value="30">
						</div>
					</div>

					<?php
					$students_list_nonce = wp_create_nonce( 'students_list' );
					$get_Group_nonce = wp_create_nonce( 'get_Group' );
					$get_Class_nonce = wp_create_nonce( 'get_Class' );
					
					echo '<input type="hidden" name="students_list_nonce" value="'.esc_attr($students_list_nonce).'">';
					echo '<input type="hidden" name="get_Group_nonce" value="'.esc_attr($get_Group_nonce).'">';
					echo '<input type="hidden" name="get_Class_nonce" value="'.esc_attr($get_Class_nonce).'">';
					?>

					<input type="submit" name="students_list" id="process_marks" class="educare_button" value="Students List">
				</div>
			</form>

			<div id="msgs"></div>
			<?php
		}

		return;
	} elseif ($action_for == 'performance') {
		if (isset($_GET['attendance'])) {
			echo "<h1>Attendance</h1>";

			echo '<div id="msgs" style="text-align:center;">';
			echo '<span style="font-size:100px">&#9785;</span><br><b>We are working on it!</b>';
			echo '</div>';
			
		} else {
			echo '<div class="cover"><img src="'.esc_url(EDUCARE_URL.'assets/img/achivement.svg').'" alt="Achivement" title="Achivement"/></div>';
			echo "<h1>Promote</h1>";

			echo educare_guide_for('Here you can change multiple students class, year, group just one click! Most usefull when you need to promote students (one class to onother) or need to update mulltiple studens');
			
			echo '<div id="promote_msgs">';
			educare_promote_students();
			echo '</div>';
		}

	} elseif ($action_for == 'settings') {
		if (isset($_GET['default_photos'])) {
			// echo "<h1>Default Photos</h1>";
			if ( isset( $_POST['educare_default_photos'] ) && isset( $_POST['educare_attachment_id'] ) ) {
				// Check user capability to manage options
				if (!current_user_can('manage_options')) {
					exit;
				}

				// Verify the nonce to ensure the request originated from the expected source
				educare_verify_nonce('educare_default_photos');
				
				$attachment_id = sanitize_text_field($_POST['educare_attachment_id']);
				update_option( 'educare_files_selector', absint($attachment_id) );
			}

			?>
			<form method='post'>
				<?php 
				// Security nonce for form requests.
				$nonce = wp_create_nonce( 'educare_default_photos' );
				echo '<input type="hidden" name="nonce" value="'.esc_attr($nonce).'">';
				
				educare_files_selector('set_default', '');
				
				if ( isset( $_POST['educare_default_photos'] ) && isset( $_POST['educare_attachment_id'] ) ) {
					echo "<div class='notice notice-success is-dismissible'><p>Successfully update default students photos</p></div>";
				}
				?>
				
				<button id='educare_default_photos' type="submit" name="educare_default_photos" class="educare_button full"><i class="dashicons dashicons-yes-alt"></i> Save</button>
			</form>
			
			<?php
		} elseif (isset($_GET['grading_system'])) {
			echo "<h1>Grading System</h1>";
			?>
			<?php echo educare_guide_for('If you need to change default grading value, simply click edit button and enter your custom (Country) starndard rules. Allso, you can add your custom rules using code. For this please visit Educare support forum or carfully read plugin readme files');?>
			
			<p>Grading systems: <i id="help" title="How does it work? Click to view" class="dashicons dashicons-editor-help"></i></p>
			<div class="select">
				<select id="grading" name="grading" class="form-control">
					<option value="Default">Default</option>
					<option value="Custom" disabled>Custom</option>
				</select>
			</div>

			<div id="show_help" style="display: none;">
				<div class="notice notice-success educare-notice"><p>
					<h3>How it's work?</h3>
					<p>
					We are mentioning the process how to calculate CGPA (GPA) from Marks in HSC. To do this, add up the grade points for the six major subjects and divide with 6 (total subject). For example, your grade points for <b>six</b> main subjects are listed below:</p><br>

					<table>
						<thead>
							<tr>
							<th>Subject</th>
							<th>Mark</th>
							<th>Grade Points</th>
							<th>Letter grade</th>
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
								<td>Total</td>
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

					<p>
						<ul style="list-style-type:circle;">
							<li><strong>Step 1:</strong> Add the grade points i.e <code>5+4+3.5+3+5+5 = 25.5</code></li>
							<li><strong>Step 2:</strong> Divide the sum by (total subject) 6 i.e <code>25.5/6 = 4.25</code></li>
							<li>Thus, your GPA is <code>4.25</code></li>
							<li>And, Letter grade is <code>A</code></li>
						</ul>
					</p>

					<p>Basically, <strong>GPA = Total grade points/Total subject</strong></p>
					<br>
					<strong>How to define grade point and letter grade?</strong>
					<pre><code>if ($marks >= 80 and $marks <= 100) { $point = 5; }</code></pre>or<pre><code>if ($marks >= 80 and $marks <= 100) { $grade = 'A+'; }</code></pre>
					</p>
				</div>
			</div>

			<div id="result_msg">
				<p><b>Default Rules</b></p>
				<?php educare_show_grade_rule();?>
			</div>
			
			<div id="update_button" class="button-container">
				<button type="submit" name="save_grade_system" class="educare_button disabled"><i class="dashicons dashicons-update" disabled></i></button>
				<button id="edit_grade" type="submit" name="edit_grade_system" class="educare_button"><i class="dashicons dashicons-edit"></i></button>
			</div>

			<?php
		} else {
			echo "<h1>Settings</h1>";
			echo educare_guide_for('Currently you are using the free version. But, <b>Educare Premium Version</b> is even more functional and powerful.');

			echo '<div id="msg_for_settings">'.educare_settings_form().'</div>';
		}
	} else {
		echo '<div id="msgs" style="text-align:center;">';
		echo '<span style="font-size:100px">&#9785;</span><br>
		<b>Sorry your requested data is missing!</b>';
		echo '</div>';
	}
}



/**
 * Display data (students and results)
 * 
 * @since 1.0.0
 * @last-update 1.2.4
 * 
 * @param bool $add_students		if data for students
 * @param bool $on_load 				if (directly) show data when page is loaded
 * 
 * @return null || HTML
 */

function educare_all_view($students = null, $on_load = null) {
	global $wpdb;
	// Table name
	$tablename = $wpdb->prefix."educare_".$students."";
	$msgs = $students;

	if (!isset($_POST["educare_view_results"]) and !isset($_POST['remove'])) {
		if ($on_load) {
			$action = 'table=All&year=All&time=id&order=DESC&results_per_page='.sanitize_text_field($on_load).'&on_load';
			wp_parse_str($action, $_POST);
		}
	}
	
	// define empty variables for ignore error
	$table = $year = $data = $select_year = $order = $time = $sub_term = $sub = '';
	$results_per_page = 10;

	if (isset($_POST["educare_view_results"]) or isset($_POST['remove']) or isset($_POST['on_load'])) {
		$table = sanitize_text_field($_POST['table']);
		$year = sanitize_text_field($_POST['year']);

		// echo '<pre>';	
		// print_r($_POST);
		// echo '</pre>';

		if ($table != 'All') {
			$data = sanitize_text_field($_POST['data']);
			$sub_term = sanitize_text_field($_POST['sub_term']);
		}
		
		if ($year != 'All') {
			$select_year = sanitize_text_field($_POST['select_year']);
		}
		
		$order = sanitize_text_field($_POST['order']);
		$time = sanitize_text_field($_POST['time']);
		$results_per_page = sanitize_text_field($_POST['results_per_page']);
	}

	// remove records
	if (isset($_POST['remove'])) {
		echo "<div class='notice notice-success is-dismissible'><p>Successfully deletet your selectet ".esc_html( $msgs )."</p></div>";
	}
	
	?>

	<!-- Search Form -->
	<form class="add_results" action="" method="post">
		<?php
		// Security nonce for form requests.
		$nonce = wp_create_nonce( 'educare_view_results' );
		echo '<input type="hidden" name="nonce" value="'.esc_attr($nonce).'">';
		?>

		<div class="content">

			<div class="select add-subject">
				<div>
					<p>Results By:</p>
					<select id='select_table' name="table" onChange="<?php echo esc_js('select_Table()');?>">
						<option value='All' <?php if ($table == 'All') echo 'selected';?>>All</option>
						<option value='Class' <?php if ($table == 'Class') echo 'selected';?>>Class</option>
						<option value='Exam' <?php if ($table == 'Exam') echo 'selected';?>>Exam</option>
					</select>
				</div>
				
				<div class="select">
					<div>
						<p id='select_data_label'>Select One:</p>
						<select id='select_data' name="data">
							<option>All Results</options>
						</select>
					</div>

					<div>
						<p id='term_label'>All</p>
						<select id='term' name="sub_term">
							<option>All</options>
						</select>
					</div>
				</div>

			</div>

			<div class="select">
				<p>Select Year:</p>
				<p>Select One:</p>
			</div>

			<div class="select">

				<select id='year' name="year" onChange="<?php echo esc_js('select_Year()');?>">
					<option value='All' <?php if ($year == 'All') echo 'selected';?>>All</option>
					<option value='Year' <?php if ($year == 'Year') echo 'selected';?>>Select Year</option>
				</select>
				
				<select id='select_year' name="select_year">
					<option>All Years</options>
				</select>

			</div>
			
			<div class="select">
				<p>Order By:</p>
				<p>Asc/Desc</p>
			</div>

			<div class="select">

				<select id='select_time' name="time">
					<option value='id' <?php if ($time == 'id') echo 'selected';?>>Time</option>
					<option value='Name' <?php if ($time == 'Name') echo 'selected';?>>Name</option>
					<option value='Roll_No' <?php if ($time == 'Roll_No') echo 'selected';?>>Roll No</option>
					<option value='Regi_No' <?php if ($time == 'Regi_No') echo 'selected';?>>Regi No</option>
				</select>
				
				<select id='select_order' name="order">
					<option value='DESC' <?php if ($order == 'DESC') echo 'selected';?>>Desc</option>
					<option value='ASC' <?php if ($order == 'ASC') echo 'selected';?>>Asc</option>
				</select>

			</div>
			
			<p><?php echo esc_html( ucfirst($msgs) );?> Per Page:</p>
			<div class="select">
				<select id='results_per_page' name='results_per_page'>
					<?php
						for ( $a = 5; $a < 305; $a+=5 ) {
							ob_start();
							if ($a == $results_per_page) {
								echo 'selected';
							}
							$select = ob_get_clean();
							
							echo "<option value='".esc_attr($a)."' ".esc_attr($select).">".esc_html($a)."</option>";
						}
					?>
				</select>
					
				<button type="submit" name="educare_view_results" class="educare_button" style="margin: 0;"><i class="dashicons dashicons-visibility"></i> View</button>
			</div>

			<!-- Default value -->
			<div class="educare_data_field">
				<div class="educareAllView_select_class"><?php echo educare_get_options('Class', $data);?></div>
				<div class="educareAllView_select_exam"><?php echo educare_get_options('Exam', $data);?></div>
				<div class="educareAllView_select_year"><?php echo educare_get_options('Year', $select_year);?></div>

				<div class="educareAllView_sub_select_class"><?php echo educare_get_options('Class', $sub_term);?></div>
				<div class="educareAllView_sub_select_exam"><?php echo educare_get_options('Exam', $sub_term);?></div>
			</div>

		</div>
	</form>

	<?php
		// Record List
		if (isset($_POST["educare_view_results"]) or isset($_POST['remove']) or isset($_POST['remove_result']) or isset($_POST['on_load'])) {
			// Check user capability to manage options
			if (!current_user_can('manage_options')) {
				exit;
			}
			
			// Check request
			if (!isset($_POST['on_load'])) {
				// Verify the nonce to ensure the request originated from the expected source
				educare_verify_nonce('educare_view_results');
			}

			$table = sanitize_text_field($_POST['table']);

			if (isset($_POST['remove_result'])) {
				$id = sanitize_text_field($_POST['id']);
				if ($wpdb->delete( $tablename, array( 'id' => $id ))) {
					echo "<div class='notice notice-success is-dismissible'><p>Successfully deletet ".esc_html( $msgs )."</p></div>";
				} else {
					echo "<div class='notice notice-error is-dismissible'><p><span class='error'>Your selected ".esc_html( $msgs )." not found for delete.</span></p></div>";
				}
			}

			if ($table != 'All') {
				$data = sanitize_text_field($_POST['data']);
				$sub_term = sanitize_text_field($_POST['sub_term']);
			}

			if ($table == 'Class') {
				$sub = 'Exam';
			} else {
				$sub = 'Class';
			}
			
			$order = sanitize_text_field($_POST['order']);
			$time = sanitize_text_field($_POST['time']);

			// Fetch records
		?>
			
			<div class="wrap-input">
				<span class="input-for">Filter <?php echo esc_html( ucfirst($msgs) );?> For Specific <i>Students, Roll No, Regi No...</i></span>
				<label for="searchBox" class="labels"></label>
				<input type="search" id="searchBox" placeholder="Search <?php echo esc_attr( ucfirst($msgs) );?>" class="fields">
				<span class="focus-input"></span>
			</div>
				
			<table width='100%' border='1' style='border-collapse: collapse;' class='view_results all-results'>
				<thead>
				<tr>
				<th>No</th>

				<?php 
				$photos = educare_check_status('photos');
				$default_data = educare_check_status('display');
				$col = 0;

				if ($photos == 'checked') {
					$col++;
					echo '<th>Photos</th>';
				}

				foreach ($default_data as $key => $value) {
					if ($students != 'results') {
						if ($key == 'Exam') {
							continue;
						}
					}

					$default_check = educare_check_status($key, true);
					if ($default_check) {
						$col++;
						echo "<th>".esc_html($default_check)."</th>";
					}
				}
				?>

				<th>Action</th>
				</tr>
				</thead>

				<tbody>
				<?php
				// Make sure $order is either 'ASC' or 'DESC' to prevent SQL injection
				$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
				// Escaping the ORDER BY clause using esc_sql()
				$order_by = esc_sql($time) . ' ' . $order;
				

				if (!empty($select_year)) {
					if ($table == 'All' or empty($data)) {
						// echo 'year';
						$search = $wpdb->get_results(
							$wpdb->prepare(
								"SELECT * FROM {$tablename} WHERE Year = %d ORDER BY {$order_by}",
								$select_year
							)
						);
					} else {
						// echo 'turm';
						if ($sub_term != 'All') {
							$search = $wpdb->get_results(
								$wpdb->prepare(
									"SELECT * FROM {$tablename} WHERE {$table} = %s AND {$sub} = %s AND Year = %d ORDER BY $order_by",
									$data,
									$sub_term,
									$select_year
								)
							);
						} else  {
							$search = $wpdb->get_results(
								$wpdb->prepare(
									"SELECT * FROM {$tablename} WHERE {$table} = %s AND Year = %d ORDER BY $order_by",
									$data,
									$select_year
								)
							);			
						}
					}
				} else {
					if ($table == 'All' or empty($data)) {
						// echo 'time';
						$search = $wpdb->get_results("SELECT * FROM {$tablename} ORDER BY {$order_by}");
					} else {
						// echo 'turm'; Class and Exan/Exam or Class
						if ($sub_term != 'All') {
							// echo $sub_term;
							$search = $wpdb->get_results(
								$wpdb->prepare(
									"SELECT * FROM {$tablename} WHERE {$table}=%s AND {$sub}=%s ORDER BY $order_by",
									$data,
									$sub_term
								)
							);
						} else {
							$search = $wpdb->get_results(
								$wpdb->prepare(
									"SELECT * FROM {$tablename} WHERE {$table}=%s ORDER BY $order_by",
									$data
								)
							);
						}
					}
				}
				
				if(count($search) > 0) {
					
					$count = 0;
					foreach($search as $print) {
						$id = $print->id;
						if (isset($_POST['remove'])) {
							$wpdb->delete( $tablename, array( 'id' => $id ));
						} else {
							$Details = $print->Details;
							$Details = json_decode($Details);
							$Photos = $Details->Photos;

							echo '<tr>';
								echo "<td>".esc_html(++$count)."</td>";
								
								if ($photos == 'checked') {
									if ($Photos == 'URL') {
										$Photos = EDUCARE_STUDENTS_PHOTOS;
									}
									echo "<td><img src='".esc_url($Photos)."' class='student-img' alt='IMG'/></td>";
								}
									
								$results_button = '';
								$results_title = "View $msgs";
								$results_value = '&#xf177';

								foreach ($default_data as $key => $value) {
									if ($students != 'results') {
										if ($key == 'Exam') {
											continue;
										}
									}
									$default_check = educare_check_status($key, true);
									if ($default_check) {
										if ($print->$key) {
											echo "<td>".esc_html($print->$key)."</td>";
										} else {
											echo "<td class='error'>Empty</td>";
											$results_button = 'error';
											$results_value = '&#xf530';
											$results_title = 'This '.esc_html( $msgs ).' is not visible for users. Because, some required field are empty. Fill all the required field carefully. Otherwise, users getting arror notice when someone find this '.esc_html( $msgs ).'. Click pen (Edit) button for fix this issue.';
										}
									}
								}

								$link = admin_url();
								$link .= 'admin.php?page=educare-all-'.$students.'';
								
								if ($students == 'results') {
									$profiles = '/'.educare_check_status("results_page");
								} else {
									$profiles = $link . '&profiles=' . $id;
								}

								?>

								<td>
									<input name="id" value="<?php echo esc_attr($id);?>" hidden>
									
									<div class="action_menu">
										<input type="submit" class="button action_button" value="&#xf349">
										<menu class="action_link">
											<?php
											// Security nonce for form requests.
											$nonce = wp_create_nonce( 'educare_form_nonce' );
											$remove_nonce = wp_create_nonce( 'educare_view_results' );
											?>

											<form class="educare-modify" method="post" id="educare_results" target="_blank">
												<?php
												echo '<input type="hidden" name="nonce" value="'.esc_attr($nonce).'">';
												?>

												<input name="id" value="<?php echo esc_attr($id);?>" hidden>
												
												<input class="button" type="submit" <?php echo esc_attr($results_button);?> name="educare_results_by_id" value="<?php echo wp_check_invalid_utf8($results_value);?>" title="<?php echo esc_attr( ucfirst($results_title) );?>" formaction="<?php echo esc_url($profiles);?>">

												<input class="button" type="submit" name="edit_by_id" value="&#xf464" title="Edit <?php echo esc_attr( ucfirst($msgs) );?>" formaction="<?php echo esc_url($link);?>&update-data">
											</form>

											<form class="educare-modify" action="<?php echo esc_url($link); ?>" method="post">
												<?php
												echo '<input type="hidden" name="nonce" value="'.esc_attr($remove_nonce).'">';
												?>
												<input type='hidden' name='educare_view_results'>
												<input type='hidden' name='id' value='<?php echo esc_attr($id);?>'>
												<input type='hidden' name='table' value='<?php echo esc_attr($table);?>'>
												<input type='hidden' name='data' value='<?php echo esc_attr($data);?>'>
												<input type='hidden' name='sub_term' value='<?php echo esc_attr($sub_term);?>'>
												<input type='hidden' name='select_year' value='<?php echo esc_attr($select_year);?>'>
												<input type='hidden' name='year' value='<?php echo esc_attr($year);?>'>
												<input type='hidden' name='order' value='<?php echo esc_attr($order);?>'>
												<input type='hidden' name='time' value='<?php echo esc_attr($time);?>'>
												<input type='hidden' name='results_per_page' value='<?php echo esc_attr($results_per_page);?>'>
												
												<input class="button error" type="submit" name="remove_result" value="&#xf182" title="Remove <?php echo esc_attr( ucfirst($msgs) );?>" onClick="<?php echo esc_js( 'return educareConfirmation()' )?>">
											</form>
										</menu>
									</div>
								</td>
								<?php
							echo '</tr>';
						}
					}
				} else {
					echo "<tr><td colspan='".esc_attr($col+2)."'><span class='error'>".esc_html( ucfirst($msgs) )." not found</span></td></tr>";
				}
			}
			?>
			</tbody>
		</table>

		<br>

		<?php
		if (isset($_POST["educare_view_results"])) {
			$status = '';
			$count = count($search);
			
			if (empty($search)) {
				$msg = '';
			} else {
				$msg = "<p>Tolal ".esc_html($count)." ".esc_html( $msgs )." found. if you click <b>Delete</b> button, It will remove all (<b>".esc_html($count)."</b>)  ".esc_html( $msgs ).".</p>";
			}
			
			if (empty($search)) {
				$status = 'disabled';
			}
			
			echo wp_kses_post($msg);
			?>
			<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
				<?php 
				// Security nonce for form request.
				$nonce = wp_create_nonce( 'educare_view_results' );
				echo '<input type="hidden" name="nonce" value="'.esc_attr($nonce).'">';
				?>

				<input type='hidden' name='id' value='<?php echo esc_attr($id);?>'>
				<input type='hidden' name='table' value='<?php echo esc_attr($table);?>'>
				<input type='hidden' name='data' value='<?php echo esc_attr($data);?>'>
				<input type='hidden' name='sub_term' value='<?php echo esc_attr($sub_term);?>'>
				<input type='hidden' name='select_year' value='<?php echo esc_attr($select_year);?>'>
				<input type='hidden' name='year' value='<?php echo esc_attr($year);?>'>
				<input type='hidden' name='order' value='<?php echo esc_attr($order);?>'>
				<input type='hidden' name='time' value='<?php echo esc_attr($time);?>'>
				<input type='hidden' name='results_per_page' value='<?php echo esc_attr($results_per_page);?>'>
				
				<input type="submit" name="remove" class="educare_button" value="Delete <?php echo esc_attr( ucfirst($msgs) );?>" onClick="<?php echo esc_js( 'return educareConfirmation()' )?>">
			</form>
			<?php
		}
}



/** 
 * Slice part of array
 * 
 * Usage example: educare_array_slice($class, 'b', 'd');
 * 
	$class = array(
		'a' => 'aa',
		'b' => 'bb',
		'c' => 'cc',
		'd' => 'dd',
		'e' => 'ee',
	);
 *
 * Example:
 * 
	$new_array = educare_array_slice($class, 'b', 'd');
	echo '<pre>';	
	print_r($new_array);	
	echo '</pre>';
 *
 * @since 1.2.0
 * @last-update 1.2.0
 * 
 * @param array 			$array where to slice
 * @param str 				$offset slice start
 * @param str 				$length slice end
 * 
 * @return new array()
 */

function educare_array_slice($array, $offset, $length = null) {
  $offset = array_search($offset, array_keys($array));
  $slice_array = array_slice($array, $offset);

  $length = array_search($length, array_keys($slice_array));
  $length = $length - 1;

  $slice_array = array_slice($slice_array, 1, $length);

  return $slice_array;
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
 * For replace old key to new key. Also, change the value
 * 
 * Usage example: educare_replace_key_n_val($arr, $oldkey, $newkey);
 * 
 * @since 1.2.0
 * @last-update 1.2.0
 * 
 * @param array $arr   	where to replace key/value
 * @param string $oldkey  	old key to replace key/value
 * @param string $newkey 	 	replace key/value to new key
 * @param mixed $value 	replace specific key value
 * 
 * @return array
 */

function educare_replace_key_n_val($arr, $oldkey, $newkey, $value = null) {
	if(array_key_exists( $oldkey, $arr)) {

    if ($value) {
      $arr[$oldkey] = $value;
    }

		$keys = array_keys($arr);
    $keys[array_search($oldkey, $keys)] = $newkey;
    return array_combine($keys, $arr);	
	}
    return $arr;    
}



/**
 * remove specific value from array
 * 
 * Usage example: educare_remove_value($value, $array);
 * 
 * @since 1.2.0
 * @last-update 1.2.0
 * 
 * @param mixed $val 	remove specific value
 * @param array $arr   from array
 * 
 * @return array
 */

function educare_remove_value($val, $arr) {
	
	if (($key = array_search($val, $arr)) !== false) {
		unset($arr[$key]);
	}

	return array_values($arr);
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
 * AJAX callback function to retrieve and display subjects based on the selected class and group.
 *
 * The `educare_class` function is an AJAX callback function that handles the request to retrieve and display subjects based on the selected class and group.
 *
 * The function performs the following tasks:
 * - Checks the user's capability to manage options. If the user doesn't have the required capability, the function exits.
 * - Verifies the nonce to ensure the request is secure.
 * - Retrieves the selected class, group, ID, and additional data from the AJAX request.
 * - Calls the `educare_get_subject` function to get the subjects for the selected class and group.
 * - Sends the subjects data as a response to the AJAX request.
 *
 * Note: The `educare_get_subject` function, which is called within this AJAX callback, is not provided in the code snippet. It is assumed that this function exists and handles the retrieval of subjects based on the class and group parameters.
 * 
 * @since 1.2.0
 * @last-update 1.2.0
 */
function educare_class() {
	// Check user capability to manage options
	if (!current_user_can('manage_options')) {
		exit;
	}

	// Remove the backslash
	$_POST['form_data'] = stripslashes($_POST['form_data']);
	
	// Get data from the AJAX request
	$class = sanitize_text_field($_POST['class']);
	$add_students = sanitize_text_field($_POST['add_students']);
	$id = sanitize_text_field($_POST['id']);
	// parses query strings and sets the parsed values into the $_POST array.
	wp_parse_str($_POST['form_data'], $_POST);

	// Verify nonce to ensure the request is secure
	educare_verify_nonce('educare_crud_data');

	// Check if the 'Group' field exists in the POST data
	if (key_exists('Group', $_POST)) {
		$Group = sanitize_text_field($_POST['Group']);
	} else {
		$Group = '';
	}

	// Call the function to get subjects based on the selected class and group
	educare_get_subject($class, $Group, $id, $add_students);

	// Terminate the script execution and send the response as JSON
	die;
}

// Add the 'educare_class' function as an AJAX action
add_action('wp_ajax_educare_class', 'educare_class');





/**
 * Generates a demo CSV file based on the current settings for importing data (results or students) into the database.
 *
 * The `educare_demo` function generates a demo CSV file based on the current settings in the Educare theme or plugin.
 * The generated demo file can be used for importing data (results or students) into the database.
 *
 * The function performs the following tasks:
 * - Checks the selected class and retrieves the associated subjects.
 * - Creates default data for the CSV file based on the required fields and additional fields from the user's settings.
 * - Saves the generated data into a CSV file named "import_demo_results.csv" for results or "import_demo_students.csv" for students.
 * - Provides feedback to the user about the success of the file generation and instructions for downloading the file.
 *
 * The function also checks if the "copy_demo" setting is enabled in the Educare settings. If it is enabled, it displays the generated data that can be copied directly. Otherwise, it instructs the user to download the CSV file manually.
 *
 * Note: The generated demo file is based on the current settings, so if the user changes the settings, the demo file may not work, and a new one needs to be generated.
 *
 * @param bool|null $demo_key Whether to return the demo fields (array keys) or not. Default is null.
 *
 * @since 1.2.0
 * @last-update 1.2.2
 * 
 * @example
 * To generate a demo file for results, call the function as follows:
 * educare_demo('results');
 *
 * To generate a demo file for students, call the function as follows:
 * educare_demo('students');
 */
function educare_demo($demo_key = null) {
	// Check user capability to manage options
	if (!current_user_can('manage_options')) {
		exit;
	}
	
	// Verify the nonce to ensure the request originated from the expected source
	if (!$demo_key) {
		// because, this is for import proccess, we have allready define nonce there
		educare_verify_nonce('educare_demo_nonce');
	}
	

	$Class = educare_demo_data('Class');

	// If we can not check exam, php will show an error msg. Because, array_rand(): Argument #1 ($array) cannot be empty
	if (empty(educare_demo_data('Exam'))) {
    $Exam = 'Exam Name';
  } else {
    $Exam = array_rand(educare_demo_data('Exam'), 1);
    $Exam = educare_demo_data('Exam')[$Exam];
  }

	$selected_class = sanitize_text_field($_POST['Class']);
	$Subject = $Class->$selected_class;

	if (isset($_POST['data_for'])) {
		$data_for = sanitize_text_field($_POST['data_for']);
	} else {
		$data_for = '';
	}
	
	// Save data as a file (import_demo.csv)
	$download_files = "assets/files/import_demo_".$data_for.".csv";
	 
	if ($data_for == 'results') {
		$search = $Subject;
	} else {
		$search = $Class;
	}

	$files_name = EDUCARE_DIR.$download_files;

	if ($search) {
		$Name = $Roll_No = $Regi_No = '';
		$Class = $selected_class;
		$GPA = rand(2, 5);
		$Extra_field = educare_demo_data('Extra_field');
		// $Year = educare_demo_data('Year');
		$Year = date("Y");
		$Photos = 'URL';

		if ($data_for == 'results' or isset($_POST['results'])) {
			$ignore = array();
		} else {
			$ignore = array(
				'Exam'
			);
		}

		$requred = educare_check_status('display');
		$requred_fields = educare_combine_fields($requred, $ignore);

		foreach ($requred_fields as $key => $value) {
			$data[$key] = $$key;
		}

		foreach ($Extra_field as $value) {
			// get type
			$type = strtok($value, ' ');
			// remove field type
			$value = substr(strstr($value, ' '), 1);

			if ($type == 'number') {
				$data[$value] = rand(10000000, 90000000);
			}
			elseif ($type == 'date') {
				$data[$value] = date("Y-m-d");
			}
			elseif ($type == 'email') {
				$data[$value] = 'youremail@gmail.com';
			} else {
				$data[$value] = $value;
			}
		}

		if ($data_for == 'results' or isset($_POST['results'])) {
			$data['Result'] = 'Passed';
			$data['GPA'] = number_format((float)$GPA, 1, '.', '');
		}
		
		$data['Group'] = 'Group Name';

		foreach ($Subject as $value) {
			// remove field type
			$data[$value] = rand(33, 99);
		}
		
		$data['Photos'] = $Photos;

		if ($demo_key) {
			return array_keys($data);
		}
		
		// .csv (exel) head
		$head = implode(',',array_keys($data));
		
		// students data
		if (isset($_POST['total_demo'])) {
			$total_demo = sanitize_text_field($_POST['total_demo']);
		} else {
			$total_demo = 1;
		}

		ob_start();

		for ($i=0; $i < $total_demo; $i++) {

			foreach ($data as $field_name => $value) {
				if ($field_name == 'Name') {
					$data[$field_name] = 'Student name' . $i;
				}
				if ($field_name == 'Roll_No') {
					$data[$field_name] = rand(10000, 90000);
				}
				if ($field_name == 'Regi_No') {
					$data[$field_name] = rand(10000000, 90000000);
				}
			}

			echo "\n" . esc_html(implode(',', $data));
    }

		$content = ob_get_clean();
		$data = $head . $content;
		
		// Save functionality
		$demo_file = file_get_contents($files_name);
		// for store (save) database status to a files
		$file_dir = $files_name;
		
		// check if data is already exist/same or not. if data not exist or old, then update data. otherwise ignore it.
		if (!($data == $demo_file)) {
			// process to update data
			if ( !file_exists("data") );
			// update data if any changed found
			$update_data = fopen($file_dir, 'w'); 
			fwrite($update_data, $data);
			fclose($update_data);
		}

		echo "<br><div class='notice notice-success is-dismissible'><p>Successfully generated demo files for your selected class (<b>".esc_html( $selected_class )."</b>)</p></div>";

		$enable_copy = '';
		
		if(educare_check_status('copy_demo') == 'checked') {
			echo '<pre><textarea style="width: 100%; height: 100px;">';
			print_r($data);
			echo '</textarea></pre>';
		} else {
			$enable_copy = 'enable <a href="'.esc_url( admin_url() ).'/admin.php?page=educare-settings&menu=Others" target="_blank">Copy Demo Data</a> from educare settings or';
		}

		echo "<p><strong>Notes:</strong> This is an example of importing a demo.csv file, based on your current settings (Class, Subject, Additional fields...). If you make any changes to educare (plugin) settings, this demo file may not work. For this you need to create this file again! And if you get error or face any problem while downloading the file &#9785;, you can ".wp_kses_post( $enable_copy )." manually get this file in dir: <p>".esc_html( $file_dir )."</p><br>";

		echo "<p><a class='educare_button' href='".esc_url(EDUCARE_URL.$download_files)."' title='Download Import Demo'><i class='dashicons dashicons-download'></i> Download Demo</a></p>";
	} else {
		$file_dir = $files_name;
		
		$update_data = fopen($file_dir, 'w'); 
		fwrite($update_data, '');
		fclose($update_data);

		$url = admin_url().'/admin.php?page=educare-management&menu='.$selected_class;

		if (!$selected_class) {
			echo "<br><div class='notice notice-error is-dismissible'><p>Please select a valid class</p></div>";
		} else {
			echo "<br><div class='notice notice-error is-dismissible'><p>Currently, you don't have added any subject in this class (<b>".esc_html( $selected_class )."</b>). Please add some subject by <a href='".esc_url( $url)."' target='_blank'>Click Here</a>. Thanks </p></div>";
		}

		echo "<br><p><a class='educare_button disabled' title='Download Import Demo.csv Error'><i class='dashicons dashicons-download'></i> Download Demo</a></p>";
	}

	die;
}

// Hook the AJAX action to the 'educare_demo' function
add_action('wp_ajax_educare_demo', 'educare_demo');




/**
 * Imports data (results or students) from a CSV file into the Educare theme or plugin database.
 *
 * The `educare_import_result` function is responsible for importing data (results or students) from a CSV file
 * into the Educare theme or plugin database. The function handles the process of importing the data and performs
 * various checks to ensure the data is imported correctly.
 *
 * The function performs the following tasks:
 * - Reads the CSV file and extracts data row by row.
 * - Validates the data length based on the CSV header and the user's settings.
 * - Assigns default values to the data fields.
 * - Processes the data and combines it with other required fields.
 * - Checks if the results or students data already exists in the database and ignores duplicates.
 * - Imports the data into the respective database table using the WordPress `$wpdb` object.
 * - Provides feedback to the user about the import process, including the number of records inserted, existing records, and any errors.
 *
 * The function also checks for the file extension and validates that the uploaded file is a CSV file. If the file is not a CSV,
 * or if no file is chosen, it displays an error message to the user.
 *
 * @param string|null $data_for The type of data to import: 'results' or 'students'.
 *
 * @since 1.0.0
 * @last-update 1.3.0
 * 
 * @example
 * To import results data, call the function as follows:
 * educare_import_result('results');
 *
 * To import students data, call the function as follows:
 * educare_import_result('students');
 */
function educare_import_result($data_for = null) {
	// Display a guide with required fields and instructions for importing
	echo educare_guide_for("Notes: Please carefully fill out all the details of your import (<b>.csv</b>) files. If you miss one, you may have problems to import the data. So, verify the student's admission form well and then give all the details in your import files. So, don't miss all of this required field!<br><br>Notes: If you don't know, how to create a import files. Please download the demo files given below.");

	// Import CSV if the "educare_import_data" form is submitted
	if(isset($_POST['educare_import_data'])) {
		// Check user capability to manage options
		if (!current_user_can('manage_options')) {
			exit;
		}

		// Verify the nonce to ensure the request originated from the expected source
		educare_verify_nonce('educare_import_data');

		// Begin import results function
		global $wpdb;

		// Table name, where to import the results
		$table = $wpdb->prefix."educare_$data_for";
		
		if ($data_for == 'results') {
			$ignore = array();
			$ignore_key = array(
				'Name'
			);
		} else {
			$ignore = array(
				'Exam'
			);
			
			$ignore_key = array(
				'Name',
				'Exam'
			);
		}

		// Get the required fields based on the educare settings
		$requred = educare_check_status('display');
		$requred_fields = educare_requred_data($requred, true);

		
		// Proccess Import Data
		// File extension
		$extension = pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION);

		// If file extension is 'csv'
		if(!empty($_FILES['import_file']['name']) && $extension == 'csv') {

			$totalInserted = $total = $exist = $error = $empty_fields = 0;
			
			// Open file in read mode
			$csvFile = fopen($_FILES['import_file']['tmp_name'], 'r');
			$keys = educare_demo(true);

			// Skipping header row
			fgetcsv($csvFile);

			// Read file
			while(($csvData = fgetcsv($csvFile)) !== FALSE) {
				$csvData = array_map("utf8_encode", $csvData);
				// Count total data
				$total ++;
				// CSV row column length (based on import files)
				$dataLen = count($csvData);
				// $table row column length (based on the users settings)
				$content_len = count($keys);

				// display error msg if $dataLen & $content_len are not same

				if( $dataLen != $content_len ) $error++;
				// process to import the results/data if everything ok
				if( !($dataLen == $content_len) ) continue;
				// Assign default value/field as a variables
				$keys = str_replace(' ' , '_', array_values($keys));
				$data = array_combine($keys, $csvData);

  			$requred_fields = educare_combine_fields($requred, $ignore_key, $data);
				$sql = educare_get_sql($requred_fields);

				// Check results already exists or not
				$search = "SELECT count(*) as count FROM {$table} WHERE {$sql}";
				$results = $wpdb->get_results( $search );

				
				// ignore old results if all ready exist
				if($results[0]->count==0) {
			
					// Check default data/field is empty or not
					if(!educare_is_empty($requred_fields)) {
						$requred_fields = educare_combine_fields($requred, $ignore, $data);
					
						if ($data_for == 'students') {
							$Details = educare_array_slice($data, 'Year', '');
							$Photos = sanitize_text_field($data['Photos']);
							$Details['Photos'] = $Photos;
							$Details = json_encode($Details);
							$Subject = educare_array_slice($data, 'Group', 'Photos');
							$Subject = json_encode($Subject);
							$Group = sanitize_text_field($data['Group']);

							$data = $requred_fields;
							$data['Details'] = $Details;
							$data['Group'] = $Group;
							$data['Subject'] = $Subject;
						} else {
							$Photos = sanitize_text_field($data['Photos']);
							$Details = educare_array_slice($data, 'Year', 'Result');
							$Details['Photos'] = $Photos;
							$Details = json_encode($Details);

							$Subject = educare_array_slice($data, 'Group', 'Photos');
							$Subject = json_encode($Subject);

							$Result = sanitize_text_field($data['Result']);
							$GPA = sanitize_text_field($data['GPA']);
							$Group = sanitize_text_field($data['Group']);

							$data = $requred_fields;
							$data['Details'] = $Details;
							$data['Group'] = $Group;
							$data['Subject'] = $Subject;
							$data['Result'] = $Result;
							$data['GPA'] = $GPA;
						}

						// Insert data/results into database table
						$wpdb->insert($table, $data);
						// display how many data is imported
						if ($wpdb->insert_id > 0) {
							$totalInserted++;
						}
					} else {
						// requred fields are empty
						$empty_fields++;
						$error++;
					}
				} else {
					// display how many data is already exists
					$exist++;
				}
			}
			// print import process details
			echo "<div class='notice notice-success is-dismissible'><p>Total ".esc_html($data_for)." inserted: <b style='color: green;'>".esc_html($totalInserted)."</b> results<br>Allredy exist: <b>".esc_html($exist)."</b> ".esc_html($data_for)."<br>Error to import: <b style='color: red;'>".esc_html($error)."</b> ".esc_html($data_for)."<br>Successfully imported: ".esc_html($totalInserted)." of ".esc_html($total)."</p></div>";
			
			if ($error) {
				$missing = $error - $empty_fields;
				echo educare_guide_for("<b>Logs</b>: It's not possible to import <b style='color: red;'>".esc_html($error)."</b> ".esc_html($data_for)." while during this process. Maybe, some field or data is missing.<br>Missing fields: <b>".esc_html($missing)."</b><br>Empty requred value: <b>".esc_html($empty_fields)."</b><p>Notes: If you make any changes on educare (plugin) settings, sometimes this demo file may not work. For this you need to create this file again!</p>", '', false);
			}
		} else {
			// notify users if empty files or invalid extension
			echo "<div class='notice notice-error is-dismissible'><p>";
			if(empty($_FILES['import_file']['name'])) {
				echo "No file chosen! Please select a files";
			} else {
				echo "Invalid extension. Files must be an <b>.csv</b> extension for import the ".esc_html($data_for).". Please choose a .csv files";
			}
			echo "</p></div>";
		}
	}
	
	?>
	<!-- Import Form -->
	<form  class="add_results" method="post" action="<?php esc_url($_SERVER['REQUEST_URI']); ?>" enctype="multipart/form-data" id="upload_csv">
		<?php
		// Define educare nonce for secure request
		$nonce = wp_create_nonce( 'educare_import_data' );
		echo '<input type="hidden" name="nonce" value="'.esc_attr($nonce).'">';
		?>

		<div class="content">
			<p>Files must be an <b>.csv</b> extension for import the results.</p>
			<input type="hidden" name="<?php echo esc_attr( $data_for );?>" value="<?php echo esc_attr( $data_for );?>">
			<input type="file" name="import_file">
			<select name="Class" class="form-control">
				<?php educare_get_options('Class', '');?>
			</select><br>
			<button class="educare_button" type="submit" name="educare_import_data"><i class="dashicons dashicons-database-import"></i> Import</button>
		</div>
	</form>
	<br>
	<?php
}




/**
 * Fixes and updates settings for the Educare theme or plugin.
 *
 * The `educare_ai_fix` function is responsible for fixing and updating settings related to the Educare theme or plugin.
 * This function is part of the problem detection mechanism and is triggered to resolve issues that may arise during updates
 * or if the settings data is inconsistent with the default settings.
 *
 * The function performs the following tasks:
 * - Retrieves the current settings and default settings data using the `educare_check_status` and `educare_add_default_settings` functions.
 * - Compares the current settings with the default settings and adds any missing or new settings to the current settings.
 * - Checks if the `Group` setting exists and adds it if not present.
 * - Updates the database settings with the fixed data.
 *
 * The function also checks if the "AI Problem Detection" option is enabled in the settings. If it's enabled, the function performs the updates
 * and returns a success message. If the option is disabled, the function informs the user to enable the "AI Problem Detection" option
 * to fix the issues.
 *
 * @return string The function returns a success message if the issues are fixed, or an informational message if the "AI Problem Detection" option is disabled.
 *
 * @since 1.2.4
 * @last-update 1.3.0
 * 
 * @example
 * Trigger the AI fix mechanism to resolve issues and update settings.
 * $fix_status = educare_ai_fix();
 * echo $fix_status;
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

	$error_key = array_diff_key($current_data,$default_data);

	// remove unkhown key from data
	foreach ($error_key as $key => $value) {
		unset($current_data[$key]);
	}

	// insert educare new data in database settings
	foreach ($default_data as $key => $data) {
		// keep user old settings
		if (!key_exists($key, $current_data)) {
			$current_data[$key] = $data;
			$msgs = true;
			$update_current_data = true;
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
				$wpdb->query(
					$wpdb->prepare(
						"ALTER TABLE `%s` ADD `%s` VARCHAR(80) NOT NULL AFTER `%s`;",
						$results_table,
						'Group',
						'Details'
					)
				);

				$wpdb->query(
					$wpdb->prepare(
						"ALTER TABLE `%s` ADD `%s` VARCHAR(80) NOT NULL AFTER `%s`;",
						$students_table,
						'Group',
						'Details'
					)
				);

				$wpdb->query(
					$wpdb->prepare(
						"ALTER TABLE `%s` ADD `%s` mediumint(11) NOT NULL AFTER `%s`;",
						$students_table,
						'Student_ID',
						'Others'
					)
				);
			
			}
			
			$msgs = '<div class="educare_post">'.educare_guide_for("<strong>Educare (AI) Detection:</strong> Successfully complete update process and fixed all bugs and error").'</div>';
		} else {
			$msgs = educare_guide_for('There are some issues found and you will get an error while proccessing some options. Because, Your current settings are disabled educare AI Problem Detection options. Please, Go to educare <code>Settings > Advance Settings > <b>(AI) Problem Detection</b></code> enable it to fix (remove) this messege. Note: To show advanced settings you must enable advanced settings in Settings > Other > Advanced settings.', '', false);
		}
	}
	
	return $msgs;

}



/**
 * ### Add, Updata or Remove Data
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
	// Prepare the query with placeholders
	$query = $wpdb->prepare( "SELECT * FROM $table WHERE list = %s", $list );
	// Execute the prepared query and get the results
	$search = $wpdb->get_results( $query );
	
	if ($search) {
		foreach ( $search as $print ) {
			$data = $print->data;
			$id = $print->id;
		}
		
		$data = json_decode($data);
		
		/* Convert index to associative array (number to name)
		and ignore Settings data, because our settings data is not array, it's an object. so, its not possible to combination [with: array_combine() func] between array and object. so, ignore Settings {object} to combine array. Otherwise, it's will give an error!
		*/
		$display_data = '';
		if ($list != 'Settings') {
			$display_data = array_combine($data, $data);
		}
	
		// for add list items
		if (isset($_POST['educare_add_'.$list.''])) {
			$in_list = $list;
			// remove all _ characters from the list (normalize the $list)
			$list = str_replace('_', ' ', $in_list);
			
			$target = sanitize_text_field($_POST[$in_list]);
			// $target = str_replace('_', ' ', $target);
			
			if (empty($target)) {
				?>
				<div class="sticky_msg">
					<div class="notice notice-error is-dismissible"> 
						<p>You must fill the form for add the <b><?php echo esc_html($list);?></b>. thanks</p>
						<button class='notice-dismiss'></button>
					</div>
				</div>
				<?php
			
			} else {
				
				$y = array();
				if (isset($_POST["educare_add_Extra_field"])) {
					$unique_target = strtolower(substr(strstr($target, ' '), 1));

					for ($i = 0; $i < count($data); $i++) {
						$x = strtolower(substr(strstr($data[$i], ' '), 1));
						$y[] = $x;
					}

				} else {
					$unique_target = strtolower($target);

					for ($i = 0; $i < count($data); $i++) {
						$x = strtolower($data[$i]);
						$y[] = $x;
					}

				}

				$unique_data = $y;
				
				if (in_array($unique_target, $unique_data)) {
					echo '<div class="sticky_msg"><div class="notice notice-error is-dismissible"><p>'.esc_html($list).' <b>'.esc_html($target).'</b> is allready exist!</p><button class="notice-dismiss"></button></div></div>';
				} else {
					
					$data = array_unique($data);
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
						$target = substr(strstr($target, ' '), 1);
					}

					echo '<div class="sticky_msg"><div class="notice notice-success is-dismissible"><p>Successfully Added <b>'.esc_html($target).'</b> at the '.esc_html($list).' list<br>Total: <b>'.esc_html(count($data)).'</b> '.esc_html($list).' added</p><button class="notice-dismiss"></button></div></div>';
				}
			}
		}
		
		if (isset($_POST['educare_edit_'.$list.''])) {
			
			$in_list = $list;
			// remove all _ characters from the list (normalize the $list)
			$list = str_replace('_', ' ', $in_list);
			
			$target = sanitize_text_field($_POST[$in_list]);
			$target = str_replace('_', ' ', $target);
			
			$check = strtolower($target);
			
			if ($in_list == 'Extra_field') {
				$check = strtolower(substr(strstr($check, ' '), 1));
			}
			
			// Create nonce for update or remove forms
			$update_nonce = wp_create_nonce( 'educare_update_'.esc_attr($in_list) );
			$remove_nonce = wp_create_nonce( 'remove_'.esc_attr($in_list) );

			if ($in_list == 'Extra_field') {
				$data_type = strtok($target, ' ');
				$Target = substr(strstr($target, ' '), 1);
				
				?>
				<div class="sticky_msg">
					<div class="notice notice-success is-dismissible">
						<p>
						<center><h2>Edit <?php echo esc_html($list);?></h2></center>
						
						<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post" class="educare_update_data">

							<input type="hidden" name="remove" value="<?php echo esc_attr($target);?>"/>
							<input type="hidden" name="old_data" value="<?php echo esc_attr($target);?>"/>
							Edit - <b><?php echo esc_html($Target);?></b>:
							
							<div class="select add-subject">
								<div>
									<p>Name:</p>
									<input type="text" name="field" class="fields" value="<?php echo esc_attr($Target);?>" placeholder="<?php echo esc_attr($Target);?>">
								</div>

								<div>
									<p>Select type:</p>
									<select name="type">
										<option value="text" <?php if ( $data_type == "text") { echo "selected";}?>>Text</option>
										<option value="number" <?php if ( $data_type == "number") { echo "selected";}?>>Number</option>
										<option value="date" <?php if ( $data_type == "date") { echo "selected";}?>>Date</option>
										<option value="email" <?php if ( $data_type == "email") { echo "selected";}?>>Email</option>
									<select>
								</div>
							</div>
									
							<input type="hidden" name="<?php echo esc_attr($in_list);?>">
					
							<input type="submit" name="educare_update_<?php echo esc_attr($list);?>" class="educare_button update<?php echo esc_attr(str_replace(' ', '', $list));?>" onClick="<?php echo esc_js('add(this.form)');?>" value="&#xf464 Edit">
					
							<input type="submit" name="<?php echo esc_attr("remove_$list");?>" class="educare_button remove<?php echo esc_attr(str_replace(' ', '', $list));?>" value="&#xf182">

							<?php
							echo '<input type="hidden" name="educare_update_'.esc_attr($in_list).'_nonce" value="'.esc_attr($update_nonce).'">';
							echo '<input type="hidden" name="remove_'.esc_attr($in_list).'_nonce" value="'.esc_attr($remove_nonce).'">';
							?>

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
						<center><h2>Edit <?php echo esc_html($list);?></h2></center>

						<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">

							<input type="hidden" name="remove" value="<?php echo esc_attr($target);?>"/>
							
							<input type="hidden" name="old_data" value="<?php echo esc_attr($target);?>"/>

							Edit - <b><?php echo esc_html($target);?></b>:<br>
							<label for="Name" class="labels" id="name"></label>
							<input type="text" name="<?php echo esc_attr($list);?>" value="<?php echo esc_attr($target);?>" placeholder="<?php echo esc_attr($list);?> Name">
						
							<input type="submit" name="educare_update_<?php echo esc_attr($list);?>" class="educare_button update<?php echo esc_attr(str_replace(' ', '', $list));?>" value="&#xf464 Edit">
								
							<input type="submit" name="<?php echo esc_attr("remove_$list");?>" class="educare_button remove<?php echo esc_attr(str_replace(' ', '', $list));?>" value="&#xf182">

							<?php
							echo '<input type="hidden" name="educare_update_'.esc_attr($in_list).'_nonce" value="'.esc_attr($update_nonce).'">';
							echo '<input type="hidden" name="remove_'.esc_attr($in_list).'_nonce" value="'.esc_attr($remove_nonce).'">';
							?>
														
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
			
			$in_list = $list;
			// remove all _ characters from the list (normalize the $list)
			$list = str_replace('_', ' ', $in_list);
			
			$old_data = sanitize_text_field($_POST['old_data']);
			$target = sanitize_text_field($_POST[$in_list]);
			$target = str_replace('_', ' ', $target);
			
			if (empty($target)) {
				echo '<div class="notice notice-error is-dismissible"><p>Sorry, its not possible to update empty field. You must fill the form for update the <b>'.esc_html($list).'</b>. thanks</p><button class="notice-dismiss"></button></div>';
			} else {
				
				if (isset($_POST["educare_update_Extra_field"])) {
					$old_type = strtok($old_data, ' ');
					$old_content = strtolower(substr(strstr($old_data, ' '), 1));
					
					$target_type = strtok($target, ' ');
					$target_content = strtolower(substr(strstr($target, ' '), 1));
					
					$old = substr(strstr($old_data, ' '), 1);
					$new = substr(strstr($target, ' '), 1);
					
					$unique_target = strtolower(substr(strstr($target, ' '), 1));
					for ($i = 0; $i < count($data); $i++) {
						$x = strtolower(substr(strstr($data[$i], ' '), 1));
						$y[] = $x;
					}

				} else {

					$old_type = false;
					$old_content = strtolower($old_data);
					
					$target_type = false;
					$target_content = strtolower($target);
					
					$old = $old_data;
					$new = $target;
						
					$unique_target = strtolower($target);
					for ($i = 0; $i < count($data); $i++) {
						$x = strtolower($data[$i]);
						$y[] = $x;
					}

				}
				
				$unique_data = $y;
				
				/* for test
				echo "old_type : ".esc_html($old_type)."<br>old_content : ".esc_html($old_content)."<br>target_type : ".esc_html($target_type)."<br>target_content : ".esc_html($target_content)."<br>";
				*/
				
				$exist = "<div class='sticky_msg'><div class='notice notice-error is-dismissible'><p>Update failed. Because,  <b>".esc_html($new)."</b> is allready exist in your selected ".esc_html($list)." list. Please try a different <i>(unique)</i> one!</p><button class='notice-dismiss'></button></div></div>";
				
				
				// getting the key where we need to update data
				$update_key = array_search($old_data, $data);
				$data[$update_key] = $target;
				// make it unique
				$data = array_unique($data);
				
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
				
				$target_content = strtolower($target_content);
				
				if ( $old_type == $target_type or $old_content == $target_content) {
					$msg = "<div class='sticky_msg'><div class='notice notice-error is-dismissible'><p>There are no changes for updates</p><button class='notice-dismiss'></button></div></div>";
				}
				
				if ( $old_type != $target_type and $old_content == $target_content) {
					// $msgs = "Change $old_type to $target_type";
					$msgs = "<div class='notice notice-success is-dismissible'><p>Succesfully update ".esc_html($list)." ".esc_html($new)." type <b class='error'>".esc_html($old_type)."</b> to <b class='success'>".esc_html($target_type)."</b>.</p><button class='notice-dismiss'></button></div>";
					$msg = update_data($wpdb, $table, $old, $new, $data, $id, $msgs);
				}
				
				if ( $old_type == $target_type and $old_content != $target_content) {
					if (in_array($target_content, $unique_data)) {
						return $exist;
					} else {
						// $msgs = "Change $old_content to $target_content";
						$msgs = "<div class='notice notice-success is-dismissible'><p>Succesfully update ".esc_html($list)." <b class='error'>".esc_html($old)."</b> to <b class='success'>".esc_html($new)."</b>.</p><button class='notice-dismiss'></button></div>";
						$msg = update_data($wpdb, $table, $old, $new, $data, $id, $msgs);
					}
				}
				
				if ( $old_type != $target_type and $old_content != $target_content) {
					if (in_array($target_content, $unique_data)) {
						return $exist;
					} else {
						// $msgs = "Full Update: Change $old_content to $target_content and also Change old type $old_type to $target_type ";
						$msgs = "<div class='notice notice-success is-dismissible'><p>Succesfully update ".esc_html($list)." <b class='error'>".esc_html($old)."</b> to <b class='success'>".esc_html($new)."</b>. also changed type <b class='error'>".esc_html($old_type)."</b> to <b class='success'>".esc_html($target_type)."</b>.</p><button class='notice-dismiss'></button></div>";
						$msg = update_data($wpdb, $table, $old, $new, $data, $id, $msgs);
					}
				}
				
				return $msg;
			}
		}
		
		
		// for remove list items
		if (isset($_POST["remove_$list"])) {
			
			$in_list = $list;
			// remove all _ characters from the list (normalize the $list)
			$list = str_replace('_', ' ', $in_list);
			
			$target = sanitize_text_field($_POST["remove"]);
			$target = str_replace('_', ' ', $target);
			$check = strtolower(str_replace('_', ' ', $target));
			
			if (isset($_POST["remove_Extra_field"])) {
				$check = substr(strstr($check, ' '), 1);
			}
					
			if (in_array($target, $display_data)) {
					unset($display_data[$target]);
					$display_data = array_values($display_data);
				
				$wpdb->update(
					$table, 				//table
					array( 					// data
						"data" => json_encode($display_data)
					),
				
					array( 					//where
						'ID' => $id
					)
				);
				
				// for hide extra field type
				if (isset($_POST["remove_Extra_field"])) {
					$target = substr(strstr($target, ' '), 1);
					$status = educare_check_status('clear_field');
				}
				if (isset($_POST["remove_Subject"])) {
					$status = educare_check_status('delete_subject');
				}
				
				echo '<div class="sticky_msg"><div class="notice notice-success is-dismissible"><p>Successfully removed <b>'.esc_html($target).'</b> from the '.esc_html($list).' list.</p><button class="notice-dismiss"></button></div></div>';
			} else {
				echo '<div class="notice notice-error is-dismissible"><p>Sorry, '.esc_html($list).' <b>'.esc_html($target).'</b> is not found!</p><button class="notice-dismiss"></button></div>';
			}
		}
		
		if ($list == 'Settings') {
			if (isset($_POST['educare_reset_default_settings'])) {
				$wpdb->query(
					$wpdb->prepare(
						"DELETE FROM $table WHERE id = %d",
						$id
					)
				);
				
				educare_add_default_settings('Settings');
				
				echo "<div class='sticky_msg'><div class='notice notice-success is-dismissible'> <p>Successfully reset default <b>settings</b></p><button class='notice-dismiss'></button></div></div>";
			}
			
			if (isset($_POST['educare_update_settings_status'])) {
				echo "<div class='sticky_msg'><div class='notice notice-success is-dismissible'><p>Successfully updated Settings</p><button class='notice-dismiss'></button></div></div>";
			}
			
			if ( isset( $_POST['educare_default_photos'] ) && isset( $_POST['educare_attachment_id'] ) ) {
					echo "<div class='notice notice-success is-dismissible'><p>Successfully updated default students photos</p><button class='notice-dismiss'></button></div>";
			}
		}
	}
}


/**
 * ### Settings Status
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
 * @last-update 1.4.1
 * 
 * @param string $target				Select settings status
 * @param string $title					Display settings title
 * @param string $comments			Settings informations
 * @param bool $input						for input fields
 * 
 * @return void|HTML
 */

function educare_settings_status($target, $title, $comments, $input = null) {
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
	// Prepare the query with placeholders
	$query = $wpdb->prepare( "SELECT * FROM $table WHERE list = %s", 'Settings' );
	// Execute the prepared query and get the results
	$search = $wpdb->get_results( $query );
	
	if ($search) {

		foreach ( $search as $print ) {
			$data = $print->data;
			$data = json_decode($data);
			$id = $print->id;
		}

		// for update settings status
		if (isset($_POST['educare_update_settings_status'])) {
			$status = 'unchecked';
			if (property_exists($data, $target)) {
				$status = $data->$target;
			}

			if ($target != 'display') {
				$update_data = sanitize_text_field($_POST[$target]);
			}

			if ($target == 'display') {
				$no = 0;
				
				foreach ($status as $key => $value) {
					$status->$key[0] = array_map( 'sanitize_text_field', $_POST['display_input'] )[$no++];
					$status->$key[1] = sanitize_text_field($_POST[$key]);
				}
				
				$update_data = $status;
			}

			$data->$target = $update_data;
			// now update desired data
			$wpdb->update(
	      $table, 				//table
				array( 					// data - we need to encode our data for store array/object into database
					"data" => json_encode($data)
			  ),
	      array( 					//where
					'ID' => $id
				)
			);
		}
	
		$status = 'unchecked';
		if (property_exists($data, $target)) {
			$status = $data->$target;
		}
		// $clear_field = $data->clear_field;
		// for input field

		if ($input) {
			echo "<div class='educare-settings'>";
			echo "<div class='title'>
			<h3>".esc_html($title)."<h3>
			<p class='comments'>".wp_kses_post($comments)."</p>
			<input type='text' id='".esc_attr($target)."' name='".esc_attr($target)."' value='".esc_attr(educare_check_status($target))."' placeholder='".esc_attr(educare_check_status($target))."'>
			</div></div>";
		} else {
			if ($target == 'display') {

				$display = $status;

				foreach ($display as $key => $value) {
					$target = $key;
					$field_name = $value[0];
					$status = $value[1];
					if ($key == 'Class' or $key == 'Exam' or $key == 'Year') {
						$info = '<div class="action_menu"><i class="dashicons action_button dashicons-info"></i> <menu class="action_link info">';
						if ($key == 'Class') {
							$info .= 'If you want to disable the class from <b>View Results</b> and <b>Front-End</b> search form, you can disable it. But you need to fill in the class while adding or importing results.';
						}
						if ($key == 'Exam') {
							$info .= 'If you want to disable the exam from <b>View Results</b> and <b>Front-End</b> search form, you can disable it. But you need to fill in the exam while adding or importing results.';
						}
						if ($key == 'Year') {
							$info .= "<span class='error'>You can't disable year.</span> But, you can rename it. like - Passing Year, Exam Year...";
						}
						$info .= '</menu></div>';
					} else {
						$info = '';
					}

					?>
					<div class="educare-settings">
						<div class="title">
							<h3><?php echo esc_html(ucwords(str_replace('_', ' ', $target))) . ' ' . wp_kses_post( $info );?><h3>
							<p class="comments">
								<input type='text' id='<?php echo esc_attr($target);?>' name='display_input[]' value='<?php echo esc_attr($field_name);?>' placeholder='Type <?php echo esc_attr($field_name);?>'>
							</p>
						</div>
						
						<div class="status-button">
							<div class="switch-radio">
								<?php if ($key != 'Year') {
									?>
									<input type="radio" id="<?php echo esc_attr($target);?>_no" name="<?php echo esc_attr($target);?>" value="unchecked" <?php if ($status == 'unchecked') { echo 'checked';};?>/>
									<label for="<?php echo esc_attr($target);?>_no">No</label>
									<?php
								}?>
								
								<input type="radio" id="<?php echo esc_attr($target);?>_yes" name="<?php echo esc_attr($target);?>" value="checked" <?php if ($status == 'checked') { echo 'checked';};?>/>
								<label for="<?php echo esc_attr($target);?>_yes">Yes</label>
							</div>
						</div>
					</div>
					<?php
				}
			} else {
				// for radio button
				?>
				<div class="educare-settings">
					<div class="title">
						<h3><?php echo esc_html($title);?><h3>
						<p class="comments"><?php echo wp_kses_post($comments);?></p>
					</div>
					
					<div class="status-button">
						<div class="switch-radio">
							<input type="radio" id="<?php echo esc_attr($target);?>_no" name="<?php echo esc_attr($target);?>" value="unchecked" <?php if ($status == 'unchecked') { echo 'checked';};?>/>
							<label for="<?php echo esc_attr($target);?>_no">No</label>
							
							<input type="radio" id="<?php echo esc_attr($target);?>_yes" name="<?php echo esc_attr($target);?>" value="checked" <?php if ($status == 'checked') { echo 'checked';};?>/>
							<label for="<?php echo esc_attr($target);?>_yes">Yes</label>
						</div>
					</div>
				</div>
				<?php
			}
		}
		
	} else {
		echo educare_guide_for('db_error');
	}
}



/**
 * Displays the settings form for the Educare.
 *
 * The `educare_settings_form` function is responsible for displaying the settings form for the Educare plugin.
 * The function generates a comprehensive form with collapsible sections, allowing users to configure various settings.
 *
 * The function makes use of various HTML elements, CSS classes, and JavaScript to create collapsible sections and handle user interactions.
 * Each section represents a different category of settings, and the user can expand or collapse each section by clicking on the respective labels.
 *
 * The form allows users to configure settings related to page setup, default fields, results system, security, and other advanced settings.
 * Users can enable or disable specific options, enter required details (e.g., page slugs, reCaptcha keys), and view guidelines or help messages.
 *
 * The function also provides options to save the settings and reset them to their default values using corresponding buttons.
 *
 * @return void The function outputs the settings form for the Educare theme or plugin.
 *
 * @since 1.4.0
 * @last-update 1.4.2
 * 
 * @example
 * Display the settings form for the Educare plugin.
 * educare_settings_form();
 */
function educare_settings_form() {
	?>
		<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
			<?php
			ob_start();
			echo bloginfo( 'url' );
			$domain = ob_get_clean();

			$active_menu = '';
			if (isset($_POST['active_menu'])) {
				$active_menu = sanitize_text_field( $_POST['active_menu'] );
			}

			?>
			<div class="collapses">
				<div class="collapse">
					<input class="head" type="radio" name="settings_status_menu" id="Page_Setup_menu" checked>
					<label class="collapse-label" for="Page_Setup_menu"><div><i class="dashicons dashicons-edit-page"></i> Page Setup</div></label>
					<div class="collapse-content">
						<?php
						echo "<div style='padding: 1px 0;'>";
						echo educare_guide_for("Enter your Front-End page slug (where you use educare shortcode in WP editor, template or any shortcode-ready area for front end results system). Don't need to insert with domain - ".esc_url($domain)."/results. Only slug will be accepted, for exp: results or index.php/results.");
						echo '</div>';

						echo '<div class="educare-settings"><div class="title"><h3>Shortcode</h3><h3>
						<p class="comments">Copy and paste this <strong>`[educare_results]`</strong> shortcode in your editor, template or any shortcode-ready area for front end results system).</p>
						<input type="text" id="Shortcode" value="[educare_results]" placeholder="[educare_results]" disabled>
						</h3></div></div>';
						
						educare_settings_status('results_page', 'Results Page', "Enter your front end results page slug (where you use <strong>`[educare_results]`</strong> shortcode in your editor, template or any shortcode-ready area for front end results system).", true);
						?>

						<?php
						educare_settings_status('students_page', 'Students Page', "Enter your front end students page slug (where you use <strong>`[educare_students]`</strong> shortcode in your editor, template or any shortcode-ready area for front end students profiles system).<br> <b>Note:</b> This feature has not been launched yet. It can be used in the next update", true);
						?>
					</div>
				</div>

				<div class="collapse">
					<div style="background-color: inicial;">
					<input class="head" type="radio" name="settings_status_menu" id="Display_menu" <?php echo esc_attr(checked($active_menu, 'Display_menu'))?> />
					<label class="collapse-label" for="Display_menu"><div><i class="dashicons dashicons-editor-spellcheck"></i> Default Fields</div></label>
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
					<input class="head" type="radio" name="settings_status_menu" id="Results_System_menu" <?php echo esc_attr(checked($active_menu, 'Results_System_menu'))?> />
					<label class="collapse-label" for="Results_System_menu"><div><i class="dashicons dashicons-welcome-learn-more"></i> Results System</div></label>
					<div class="collapse-content">
						<?php
						educare_settings_status('institute', 'Institution', "Name of the institutions (Title)", true);
						
						educare_settings_status('optional_sybmbol', 'Optional Subject Selection', "Define optional subject identifier character/symbol. In this way educare define and identify optional subjects when you add or import results.", true);
		
						educare_settings_status('group_subject', 'Group Subject', "Define how many subject in each group. In this way educare define last (your defined) subject as a group wise subject when you add or import any results and students. For disable or unlimited set <code>0</code>", true);

						educare_settings_status('auto_results', 'Auto Results', "Automatically calculate students results status Passed/Failed and GPA");
		
						educare_settings_status('photos', 'Students Photos', "Show or Hide students photos");

						educare_settings_status('details', 'Students Details', "Show information/details of students on result card");

						educare_settings_status('grade_sheet', 'Grade Sheet', "Show the grade sheet on the result card");
		
						educare_settings_status('custom_results', 'Custom Design Permissions', "You need to permit/allow this options when you add custom functionality or customize educare results card or searching forms");
						?>
					</div>
				</div>

				<div class="collapse">
					<input class="head" type="radio" name="settings_status_menu" id="Security_menu" <?php echo esc_attr(checked($active_menu, 'Security_menu'))?> />
					<label class="collapse-label" for="Security_menu"><div><i class="dashicons dashicons-lock"></i> Security</div></label>
					<div class="collapse-content">
						<?php
						echo "<div style='padding: 1px 0;'>";
						echo educare_guide_for("<i>FAQ:</i> How to get my site or secret key?<br>Please go to <a href ='https://www.google.com/recaptcha' target='_blank'>google recaptcha</a>. Click Admin Console or Get Started with Enterprise at the top right corner. Enter a label for your ReCaptcha and select the V2 checkbox. Add the URL for your site in the Domain section. Accept the terms of service and click Submit. Copy the Site Key and Secret Key that Google generates.");
						echo '</div>';

						// Site Key: 
						educare_settings_status('re_captcha', 'Google Re-Captcha', "Enable google recaptcha to improve security. Here, You need to enter/paste your google re-captcha v2 site or secret key. (Currently it's only supports <b>ReCaptcha V2</b>)");

						// Sectet Key: 
						educare_settings_status('site_key', 'Site Key', "Paste your google re-captcha v2 site key:", true);

						educare_settings_status('secret_key', 'Secret Key', "Paste your google re-captcha v2 secret key:", true);
						?>
					</div>
				</div>

				<div class="collapse">
					<input class="head" type="radio" name="settings_status_menu" id="Others_menu" <?php echo esc_attr(checked($active_menu, 'Others_menu'))?> />
					<label class="collapse-label" for="Others_menu"><div><i class="dashicons dashicons-admin-tools"></i> Others</div></label>
					<div class="collapse-content">
						<?php
						educare_settings_status('guide', 'Guidelines', "Enable this options to receive smart guidance or help messages. These features guide you to - how to use educare (recommended for new users).");

						educare_settings_status('confirmation', 'Delete confirmation', "Enable these options to get a popup confirmation when you delete something.");

						educare_settings_status('copy_demo', 'Copy Demo Data', "<strong>Recommendation:</strong> Allow this option when your systems don't allow to download demo file. If you enable this options all demo data will be show in text box. You can copy and paste this data into csv files.");
						
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
							<label class="collapse-label" for="Advance_Settings_menux"><div><i class="dashicons dashicons-performance"></i> Advance Settings</div></label>
							<div class="collapse-content">
								<?php
								echo "<div style='padding: 1px 0;'>";
								educare_settings_status('problem_detection', '(AI) Problem Detection', "Automatically detect and fix educare relatet problems. Please, enable this options when update educare");
								echo '</div>';

								educare_settings_status('clear_data', 'Clear Data', "Clear all (educare) data from database when you uninstall or delete educare from plugin list?");
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

			echo '<input type="hidden" name="educare_update_settings_status_nonce" value="'.esc_attr($update_settings).'">';
			echo '<input type="hidden" name="educare_reset_default_settings_nonce" value="'.esc_attr($reset_settings).'">';
			?>
				
			<button type="submit" name="educare_update_settings_status" class="educare_button"><i class="dashicons dashicons-yes-alt"></i> Save</button>
			<button type="submit" name="educare_reset_default_settings" class="educare_button"><i class="dashicons dashicons-update"></i> Reset Settings</button>
				
		</form>
	<?php
}



/**
 * ### Class wise Jubject
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
					echo '<div class="notice notice-error is-dismissible"> <p><b> '.esc_html( $target ).'</b> is allready exist in '.esc_html( $list ).' list</p><button class="notice-dismiss"></button></div>';
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
					echo '<div class="sticky_msg"><div class="notice notice-error is-dismissible"> <p>You must fill the form for add the <b>Subject</b>. thanks</p><button class="notice-dismiss"></button></div></div>';
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
								$msg = '<div class="sticky_msg"><div class="notice notice-error is-dismissible"> <p>There are no changes for update</p><button class="notice-dismiss"></button></div></div>';
							} else {
								$msg = '<div class="sticky_msg"><div class="notice notice-error is-dismissible"> <p><b> '.esc_html( $target ).'</b> is allready exist in your selected '.esc_html( $list ).' ('.esc_html( $class ).')</p><button class="notice-dismiss"></button></div></div>';

								if (isset($_POST['update_class'])) {
									$msg = '<div class="notice notice-error is-dismissible"> <p><b> '.esc_html( $target ).'</b> is allready exist in '.esc_html( $list ).' list</p><button class="notice-dismiss"></button></div>';
								}
								
							}
						}
						elseif (isset($_POST['edit_subject'])) {
							?>
							<div class="sticky_msg">
								<div class="notice notice-success is-dismissible add_results"><p>
								<center><h2>Edit Subject</h2></center>

									<form action="" method="post">
										<input type="hidden" name="educare_process_<?php echo esc_attr($list);?>">
										<input type="hidden" name="old_subject" value="<?php echo esc_attr($target);?>">
										<input type="hidden" name="old_class" value="<?php echo esc_attr($class);?>">

										Edit - <b><?php echo esc_html($target);?></b>:

										<div class="select add-subject">
											<div>
												<p>Subject name:</p>
												<input type="text" name="subject" class="fields" value="<?php echo esc_attr($target);?>" placeholder="<?php echo esc_attr($target);?>" pattern="[A-Za-z0-9 ]+" title="Only Caretaker, Number and Space allowed. (A-Za-z0-9)">
											</div>

											<div>
												<p>Subject for <?php echo esc_html($list);?></p>
												<select name='class'>
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

										<input id="educare_results_btn" class="educare_button proccess_<?php echo esc_attr($list);?>" name="update_subject" type="submit" value="&#xf464 Edit">

										<input type="submit" name="remove_subject" class="educare_button proccess_<?php echo esc_attr($list);?>" value="&#xf182">

										<?php
										$update_subject_nonce = wp_create_nonce( 'update_subject' );
										$remove_subject_nonce = wp_create_nonce( 'remove_subject' );
										
										echo '<input type="hidden" name="update_subject_nonce" value="'.esc_attr($update_subject_nonce).'">';
										echo '<input type="hidden" name="remove_subject_nonce" value="'.esc_attr($remove_subject_nonce).'">';
										?>
										
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
								<div class="notice notice-success is-dismissible add_results"><p>
									<form action="" method="post">
										Edit <?php echo esc_attr($list);?>:
										<input type="hidden" name="educare_process_<?php echo esc_attr($list);?>">
										<input type="hidden" name="old_class" value="<?php echo esc_attr($class);?>">
										<input type="text" name="class" class="fields" value="<?php echo esc_attr($class);?>" placeholder="<?php echo esc_attr($class);?>" pattern="[A-Za-z0-9 ]+" title="Only Caretaker, Number and Space allowed. (A-Za-z0-9)">

										<br>
										
										<button id="educare_results_btn" class="educare_button proccess_<?php echo esc_attr($list);?>" name="update_class" type="submit"><i class="dashicons dashicons-edit"></i> Edit</button>

										<?php
										$update_class_nonce = wp_create_nonce( 'update_class' );
										
										echo '<input type="hidden" name="update_class_nonce" value="'.esc_attr($update_class_nonce).'">';
										?>

									</form>
									</p>
									<button class="notice-dismiss"></button>
								</div>
							</div>
							<?php
						}

						elseif (isset($_POST['remove_subject'])) {
							$msg = '<div class="sticky_msg"><div class="notice notice-success is-dismissible"><p>Successfully removed <b>'.esc_html($target).'</b> from the subject list.</p><button class="notice-dismiss"></button></div></div>';
							// convert indexed array to associative array. So, we can essily select your specific data/value by specific key. Otherwise, it's hard to detect specific data with indexed key.
							$subject_list = array_combine($subject_list, $subject_list);
							// remove data by specific key
							unset($subject_list[$target]);
							$subject_list = array_values($subject_list);

							$data[$class] = $subject_list;
						} else {
							echo '<div class="notice notice-error is-dismissible"> <p><b class="error">Somethink went wrong!</b> Maybe its a bug. Soon, we (Educare) will fix these issues after the next update</p><button class="notice-dismiss"></button></div>';
						}
					} else {
						if (isset($_POST['add_subject'])) {
							//  if add sobject
							$subject_list = array_unique($subject_list);
							array_push($subject_list, $target);
							$data[$class] = $subject_list;
							
							$msg = '<div class="sticky_msg"><div class="notice notice-success is-dismissible"> <p>Successfully Added <b>'.esc_html($target).'</b> at the '.esc_html( $list ).' list<br>'.esc_html( $list ).': <b>'.esc_html($class).'</b><br>Total: <b>'.esc_html(count($subject_list)).'</b> Subject added</p><button class="notice-dismiss"></button>
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

								$msg = '<div class="sticky_msg"><div class="notice notice-success is-dismissible"> <p>Successfully change subject <b class="error">'.esc_html($old_sub).'</b> to <b class="success">'.esc_html($target).'</b></p><button class="notice-dismiss"></button></div></div>';
							}
							elseif (strtolower($target) == $old_subject and $class != $old_class) {
								unset($data[$old_class][$get_key]);
								array_values($data[$old_class]);
								array_push($data[$class], $target);
								
								$msg = '<div class="sticky_msg"><div class="notice notice-success is-dismissible"> <p>Successfully change '.esc_html( $list ).' <b class="error">'.esc_html($old_class).'</b> to <b class="success">'.esc_html($class).'</b></p><button class="notice-dismiss"></button></div></div>';
							} else {
								// Add data
								$data[$old_class][$get_key] = $target;
								// Remove data
								unset($data[$old_class][$get_key]);
								array_values($data[$old_class]);
								array_push($data[$class], $target);

								$msg = "<div class='sticky_msg'><div class='notice notice-success is-dismissible'><p>Succesfully update subject <b class='error'>".esc_html($old_sub)."</b> to <b class='success'>".esc_html($target)."</b>. also changed ".esc_html( $list )." <b class='error'>".esc_html($old_class)."</b> to <b class='success'>".esc_html($class)."</b>.</p><button class='notice-dismiss'></button></div></div>";
							}
						}

						if (isset($_POST['update_class'])) {
							$old_class = sanitize_text_field( $_POST['old_class'] );
							$get_key = array_search($data[$old_class], $data);

							if(strtolower($old_class) == strtolower($target)) {
								$msg = "<div class='sticky_msg'><div class='notice notice-error is-dismissible'><p>There are no changes for updates</p><button class='notice-dismiss'></button></div></div>";
							} else {
								if (key_exists(strtolower($target), array_change_key_case($data))) {
									echo '<div class="notice notice-error is-dismissible"><p><b>'.esc_html($target).'</b> is allready exist in '.esc_html( $list ).' list</p><button class="notice-dismiss"></button></div>';
								} else {
									if ($target !== $old_class) {
										$data = educare_replace_key($data, $old_class, $target);
										$msg = '<div class="sticky_msg"><div class="notice notice-success is-dismissible"> <p>Successfully changed '.esc_html( $list ).' <b class="error">'.esc_html($old_class).'</b> to <b class="success">'.esc_html($target).'</b></p><button class="notice-dismiss"></button></div></div>';
									}
								}
							}
						}

						if (isset($_POST['remove_class'])) {
							$class = sanitize_text_field( $_POST['class'] );
							
							unset($data[$class]);
							
							$msg = '<div class="notice notice-success is-dismissible"> <p><b class="error">'.esc_html($class).'</b> has been successfully removed from the '.esc_html( $list ).' list</p><button class="notice-dismiss"></button></div>';
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
						echo '<div class="notice notice-error is-dismissible"><p><b>'.esc_html($class).'</b> is allready exist in '.esc_html( $list ).' list</p><button class="notice-dismiss"></button></div>';
					} else {
						if (empty($class)) {
							echo '<div class="sticky_msg"><div class="notice notice-error is-dismissible"> <p>You must fill the form for add the <b>'.esc_html( $list ).'</b>. thanks</p><button class="notice-dismiss"></button></div></div>';
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

							echo '<div class="sticky_msg"><div class="notice notice-success is-dismissible"> <p>Successfully Added <b>'.esc_html($target).'</b> at the '.esc_html( $list ).' list<br></p><button class="notice-dismiss"></button>
							</div></div>';
						}
					}
				} else {
					echo '<div class="notice notice-error is-dismissible">';

					if ($data) {
						?>
						<p>Sorry, <b><?php echo esc_html($class);?></b> not exist<b></b> at the <?php echo esc_html($list);?> list<br>
						If you need to add subject in this (<?php echo esc_html($class);?>) <?php echo esc_html($list);?>. First, You need to add this (<?php echo esc_html($class);?>) in the <?php echo esc_html($list);?> list. Then, You would allowed to add some subject. thanks
						<?php
					} else {
						?>
						<p>Sorry, you don't have added any <?php echo esc_html($list);?> yet. For add subject, you need to add a <?php echo esc_html($list);?> first. Then, you get to add a subject for this <?php echo esc_html($list);?>. thank you 
						<?php
					}
						
					echo '</p><button class="notice-dismiss"></button></div>';
				}
			}
		}
	} else {
		echo educare_guide_for('db_error', '', false);
	}

	// Add newly adde class or group in options (also selected) without realoding the page
	if (isset($_POST['educare_process_Class']) or isset($_POST['educare_process_Group'])) {
		$data_for = 'Group';
		$class = sanitize_text_field( $_POST['class'] );

		if (isset($_POST['educare_process_Class'])) {
			$data_for = 'Class';
		}
		
		?>
		<script>
			jQuery(document).ready(function($) {
				$('#add_<?php echo esc_js($data_for);?>').html('<?php echo esc_js(educare_get_options($data_for, $class)); ?>');
			});
		</script>
		<?php
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
	// Access the global $wpdb object for database queries
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
	// Prepare the query with placeholders
	$query = $wpdb->prepare( "SELECT * FROM $table WHERE list = %s", $list );
	// Execute the prepared query and get the results
	$search = $wpdb->get_results( $query );

	// Initialize variables to store data retrieved from the database
	$data = array();

	// If data is found, extract and decode it into an array
	if ($search) {
		foreach ( $search as $print ) {
			$data = $print->data;
			$id = $print->id;
		}
		
		$data = json_decode($data, true);
	} else {
		$data = array();
	}
	
	if (!$form) {
		$count = 1;
		// Checked first class content (Subjects)
		$first = array_key_first($data);

		if ($data) {
			// echo '<h3 id="'.esc_attr( $list ).'">'.esc_html( $list ).'</h3>';
			echo '<div class="collapses">';
			foreach ($data as $class => $val) {
				// here $val = total subject in this class
				?>
				<div class="collapse">
					<input class="head" type="radio" name="<?php echo esc_attr($list);?>" data="<?php echo esc_attr($class);?>" id="<?php echo esc_attr( $list . '_' . $class );?>" <?php if ($class == $first or isset($_POST['class']) and $_POST['class'] == $class) {echo 'checked';}?>>
					<label class="collapse-label" for="<?php echo esc_attr( $list . '_' . $class );?>">
						<?php echo esc_html( $count++ ) . '. ' . esc_html( $class );?>
						<span>
							<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">

								<input type="hidden" name="educare_process_<?php echo esc_attr($list);?>"><input type="hidden" name="class" value="<?php echo esc_attr( $class );?>">

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
						<table class='grade_sheet list'>
							<thead>
								<tr>
									<th>No</th>
									<th width='100%'>Subject</th>
									<th>Edit</th>
									<th>Delete</th>
								</tr>
							</thead>

							<tbody>
							<?php
							if ($val) {
								$no = 1;
								foreach ($val as $subject) {
									// echo '<li>';
									// echo $subject;
									// echo '</li>';
									?>
									<tr>
										<td><?php echo esc_html($no++);?></td>
										<td><?php echo esc_html($subject);?></td>
										<td colspan='2'>
											<form class="educare-modify" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">

												<input type="hidden" name="educare_process_<?php echo esc_attr($list);?>">

												<input type="hidden" name="class" value="<?php echo esc_attr($class);?>"/>

												<input type="hidden" name="subject" value="<?php echo esc_attr($subject);?>"/>
												
												<input type="submit" name="edit_subject" class="button success proccess_<?php echo esc_attr($list);?>" value="&#xf464">
												
												<input type="submit" name="<?php echo esc_attr("remove_subject");?>" class="button error proccess_<?php echo esc_attr($list);?>" value="&#xf182">

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

								echo "<tr><td colspan='4'><div class='notice notice-error is-dismissible'><p>Currently, you don't have added any subject in this ".esc_html( $list ).". Please add a subject for this ".esc_html( $list )." by using above forms. Thanks</p></div></td></tr>";
							}
							?>
							</tbody>
						</table>
					</div>
				</div>
				<?php
			}
			echo '</div>';
		} else {
			echo "<div class='notice notice-error is-dismissible'><p>Currently, you don't have added any ".esc_html( $list ).". Please add a ".esc_html( $list )." by clicking on the <b>Add ".esc_html( $list )."</b> tab. Thanks</p></div>";
		}
		
	}
	
	if ($form) {
		?>
		<div class="educare_tabs form_tab">
			<div class="tab_head">
				<button class="tablink educare_button" data="<?php echo esc_attr($list);?>_subject">Add Subject</button>
				<button class="tablink" data="<?php echo esc_attr($list);?>_class">Add <?php echo esc_html($list);?></button>
			</div>
			
			<div id="<?php echo esc_attr($list);?>_subject" class="section_name">
				<form class="add_results" action="" method="post" id="add_subject">
					<div class="content">
						<input type="hidden" name="educare_process_<?php echo esc_attr($list);?>">

						<div class="select add-subject">
							<div>
							<p>Subject:</p>
								<input type="text" name="subject" class="fields" placeholder="subject name" pattern="[A-Za-z0-9 ]+" title="Only characters, numbers and space allowed. (A-Za-z0-9)">
							</div>
						
							<div>
								<p>Subject For (<?php echo esc_attr($list);?>):</p>
								<select id='add_<?php echo esc_attr($list);?>' name='class'>
									<?php
									foreach ($data as $key => $value) {
										echo "<option value='".esc_attr($key)."'>".esc_html($key)."</option>";
									}
									?>
								</select>
							</div>
						</div>

						<?php
						$nonce = wp_create_nonce( 'add_subject' );
						echo '<input type="hidden" name="add_subject_nonce" value="'.esc_attr($nonce).'">';
						?>

						<button id="educare_results_btn" class="educare_button proccess_<?php echo esc_attr($list);?>" name="add_subject" type="submit"><i class="dashicons dashicons-plus-alt"></i> Add Subject</button>
					</div>
				</form>
			</div>

			<div id="<?php echo esc_attr($list);?>_class" class="section_name" style="display:none">
				<form class="add_results" action="" method="post" id="add_subject">
					<div class="content">
						<input type="hidden" name="educare_process_<?php echo esc_attr($list);?>">
						<div class="select add-subject">
							<div>
								<p><?php echo esc_html($list);?>:</p>
								<input type="text" name="class" class="fields" placeholder="<?php echo esc_attr($list);?> name" pattern="[A-Za-z0-9 ]+" title="Only characters, numbers and space allowed. (A-Za-z0-9)">
							</div>
						</div>
						
						<br>

						<?php
						$nonce = wp_create_nonce( 'add_class' );
						echo '<input type="hidden" name="add_class_nonce" value="'.esc_attr($nonce).'">';
						?>
						
						<button id="educare_results_btn" class="educare_button proccess_<?php echo esc_attr($list);?>" name="add_class" type="submit"><i class="dashicons dashicons-plus-alt"></i> Add <?php echo esc_html($list);?></button>
					</div>
				</form>
			</div>
		</div>
		
		<!-- Default value -->
		<div class="educare_data_field">
			<div class="educareSettingSubForm" data-value="<?php echo esc_attr($list);?>"></div>
		</div>

		<?php
	}
}



/**
 * ### Display Content
 * 
 * Usage example: educare_content('Exam');
 * 
 * @since 1.0.0
 * @last-update 1.0.0
 * 
 * @param string $list	Exam, Year, Extra field
 * @return void|HTML
 * 
 * Display Content - Subject, Exam, Class, Year Extra field...
 */

function educare_content($list, $form = null) {
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
				$content = $target[$i];
				// for ignore extra field type
				$Content = $target[$i];
				$type_th = '';
				$type_td = '';
				
				if ($list == 'Extra_field') {
					$get_type = strtok($content, ' ');
					$Content = substr(strstr($content, ' '), 1);
					$type_th = "<th>Type</th>";
					$type_td = "<td><span class='type ".esc_attr($get_type)."'></span></td>";
				}
				
				if ($list) {
				?>
					<tr>
						<td><?php echo esc_html(++$count);?></td>
						<td><b><?php echo esc_html($Content);?></b></td>
						<?php echo wp_kses_post($type_td);?>
						<td colspan='2'><form class="educare-modify" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
							
							<input type="hidden" name="remove" value="<?php echo esc_attr($content);?>"/>

							<input type="hidden" name="<?php echo esc_attr($list);?>" value="<?php echo esc_attr($content);?>"/>
							
							<input type="submit" name="educare_edit_<?php echo esc_attr($list);?>" class="button success edit<?php echo esc_attr(str_replace('_', '', $list));?>" value="&#xf464">
							
							<input type="submit" name="<?php echo esc_attr("remove_$list");?>" class="button error remove<?php echo esc_attr(str_replace('_', '', $list));?>" value="&#xf182">
							
							<?php
							$update_nonce = wp_create_nonce( 'educare_edit_'.esc_attr($list) );
							$remove_nonce = wp_create_nonce( 'remove_'.esc_attr($list) );
							
							echo '<input type="hidden" name="educare_edit_'.esc_attr($list).'_nonce" value="'.esc_attr($update_nonce).'">';
							echo '<input type="hidden" name="remove_'.esc_attr($list).'_nonce" value="'.esc_attr($remove_nonce).'">';
							?>
						    	
						</form></td>
					</tr>
				<?php
				
				} // end if ($list)
			}
			
			$target = ob_get_clean();
		}
		
		if (!$form) {
			if (!empty($target)) {
				?>
				<table class='grade_sheet list'>
					<thead>
						<tr>
							<th>No</th>
							<th width='100%'><?php echo esc_html($List);?></th>
							<?php echo wp_kses_post($type_th);?>
							<th>Edit</th>
							<th>Delete</th>
						</tr>
					</thead>
					<tbody>
						<?php echo wp_check_invalid_utf8(str_replace('_', ' ', $target));?>
					</tbody>
				</table>
				<?php
			} else {
				?>
				<div class="notice notice-error is-dismissible">
						<p>Currently, You don't have added any <b><?php echo esc_html($List);?></b>. Please add a <?php echo esc_html($List);?> by using this forms</p>
				</div>
				<?php
			}
		}

		if ($form) {
			// Create nonce for this form
			$nonce = wp_create_nonce( 'educare_add_'.esc_attr($list) );
			
			if ($list == 'Extra_field') {
				?>
				<form class="add_results" action="" method="post">
				<div class="content">
					<div class="select add-subject">
						<div>
							<p>Name:</p>
							<input type="text" name="field" class="fields" placeholder="<?php echo esc_attr($List);?> name" pattern="[A-Za-z0-9 ]+" title="Only Caretaker, Number and Space allowed. (A-Za-z0-9)">
						</div>
						
						<div>
							<p>Select type:</p>
							<select name="type">
								<option value="text">Text</option>
								<option value="number">Number</option>
								<option value="date">Date</option>
								<option value="email">Email</option>
							<select>
						</div>
					</div>
					
					<input type="text" name="<?php echo esc_attr($list);?>" hidden>

					<?php
					// Print nonce value
					echo '<input type="hidden" name="educare_add_'.esc_attr($list).'_nonce" value="'.esc_attr($nonce).'">';
					?>

					<button id="educare_add_<?php echo esc_attr($list);?>" class="educare_button" name="educare_add_<?php echo esc_attr($list);?>" type="submit" onClick="<?php echo esc_js('add(this.form)');?>"><i class="dashicons dashicons-plus-alt"></i> Add <?php echo esc_html($List);?></button>
				</div>
				</form>
				<br>
				<?php
				
			} else {
				?>
				<form class="add_results" action="" method="post">
					<div class="content">
						<?php echo esc_html($List);?>:
						<label for="<?php echo esc_attr($list);?>" class="labels" id="<?php echo esc_attr($list);?>"></label>
						<input type="text" name="<?php echo esc_attr($list);?>" class="fields" placeholder="<?php echo esc_attr($List);?> name" pattern="[A-Za-z0-9 ]+" title="Only Caretaker, Number and Space allowed. (A-Za-z0-9)">

						<?php
						// Print nonce value
						echo '<input type="hidden" name="educare_add_'.esc_attr($list).'_nonce" value="'.esc_attr($nonce).'">';
						?>
						
						<button id="educare_add_<?php echo esc_attr($list);?>" class="educare_button" name="educare_add_<?php echo esc_attr($list);?>" type="submit"><i class="dashicons dashicons-plus-alt"></i> Add <?php echo esc_html($List);?></button>
					</div>
				</form>
				<br>
				<?php
			}
		}
	} else {
		// database error
	}
}



/** 
 * ### Pack all in one
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
 * ### Responce all content
 * 
 * Ajax respnce for management menu/page
 * 
 * @since 1.4.0
 * @last-update 1.4.7
 * 
 * @return mixed
 */

function educare_process_content() {
	// Check user capability to manage options
	if (!current_user_can('manage_options')) {
		exit;
	}
	
	$action_for = sanitize_text_field($_POST['action_for']);
	// $currenTab = sanitize_text_field($_POST['currenTab']);
	
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
	
	// verify is request comming from valid sources
	educare_verify_nonce($action_for, $action_for.'_nonce');

	if (isset($_POST['educare_process_Class'])) {
		educare_process_class('Class');
		educare_setting_subject('Class');
	}
	elseif (isset($_POST['educare_process_Group'])) {
		educare_process_class('Group');
		educare_setting_subject('Group');
	}
	elseif (isset($_POST['educare_update_settings_status']) or isset($_POST['educare_reset_default_settings'])) {
		echo educare_process_settings('Settings');
		educare_settings_form();
	} 
	elseif (isset($_POST['educare_import_results'])) {
		educare_import_result();
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
	
	// Terminate script execution after processing content
	// to prevent further output
	die;
}

// Hook the AJAX action to the 'educare_process_content' function
add_action('wp_ajax_educare_process_content', 'educare_process_content');



/** 
 * ### Proccess add || update || delete [CRUD] students and results form
 * 
 * @since 1.4.0
 * @last-update 1.4.7
 * 
 * @return mixed
 */

function educare_process_forms() {
	// Check if the current user has the access this request as 'manage_options' capability (typically administrators).
	educare_check_access();
	
	$action_for = sanitize_text_field($_POST['action_for']);
	$data_for = sanitize_text_field($_POST['data_for']);
	// $currenTab = sanitize_text_field($_POST['currenTab']);
	wp_parse_str($_POST['form_data'], $_POST);
	$_POST[$action_for] = $action_for;
	$_POST['data_for'] = $data_for;

	if (isset($_POST['data_for']) and $_POST['data_for'] == 'students') {
		educare_crud_data(true);
	} else {
		educare_crud_data();
	}

	// Terminate script execution after processing form data
	// to prevent further output
	die;
}

// Hook the AJAX action to the 'educare_process_forms' function
add_action('wp_ajax_educare_process_forms', 'educare_process_forms');



/** 
 * ### Students and Results page tab management
 * 
 * Show element for add, update, import - results or students
 * 
 * @since 1.4.0
 * @last-update 1.4.2
 * 
 * @param string $students		for specific data - Students or Results
 * @return mixed
 */

function educare_data_management($students = null) {

	if ($students == 'results') {
		$status = false; 
	} else {
		$status = true;
	}

	// get the slug of the page we want to display 
	// then we include the page
	if (isset($_GET['add-data'])) {
		// include (EDUCARE_ADMIN."menu/view-results.php");
		echo '<h1>Add '.esc_html($students).'</h1>';

		echo educare_guide_for("Here you can add data and their details. Once, if you add and fill student details then you don't need to fill student details again while adding or publishing any result. If you miss something and need to update/edit, you can update a student's details from the <a href='admin.php?page=educare-all-".esc_html($students)."&update-data'>Update Menu</a>. Aslo, you can import unlimited students from <a href='admin.php?page=educare-all-".esc_html($students)."&import-data'>Import</a> tab.");
		
		// save forms data
		echo '<div id="msgs">';
		educare_crud_data($students);
		echo '</div>';
		
		// get results forms for add result
		echo '<div id="msgs_forms">';
		educare_get_results_forms('', $status);
		echo '</div>';
		
	} elseif (isset($_GET['update-data'])) {
		// include (EDUCARE_ADMIN."menu/view-results.php");
		echo '<h1>Update '.esc_html($students).'</h1>';

		echo educare_guide_for("Search student by roll, reg no, selecting class and year for update or remove specific data (All fields are requred)");

		// save forms data
    echo '<div id="msgs">';
		educare_crud_data($students);
		echo '</div>';
		// Search form for edit/delete results
		if (!isset($_POST['edit']) and !isset($_POST['edit_by_id'])) {
			educare_get_search_forms();
		}
		
	} elseif (isset($_GET['import-data'])) {
		// include (EDUCARE_ADMIN."menu/view-results.php");
		echo '<h1>Import '.esc_html($students).'</h1>';

		educare_import_result($students);
		?>

		<div class='demo'>
			<strong>Optional Subject Selection Guide</strong>
			<p>Educare add 1 before optional subject marks <code>1 [space] Marks</code>.</p>
			<li style="font-size: small;">Exp: <code>1 85</code></li>
			<li style="font-size: small;">Here <code>1</code> 	= Define optional subject</li> 
			<li style="font-size: small;">and <code>85</code> 	= Marks</li>
			<p>In this way educare define and identify optional subjects. So, when you add a result to the csv files - you need to add <code>1</code> symbol before the optional subject marks.</p>

			
			<div class="select add-subject">
				
				<div>
					<p>Total <?php echo esc_html($students)?>:</p>
					<select id="total_demo" name="total_demo" class="form-control">
						<?php 
						for ($i=5; $i < 105; $i+=5) {
							// if ($i == 0) {
							// 	echo '<option value="'.esc_attr( $i ).'">Head only</option>';
							// 	continue;
							// }

							echo '<option value="'.esc_attr( $i ).'">'.esc_html( $i ).'</option>';
						}
						?>
					</select>
				</div>

				<div>
					<p>Select class for demo files:</p>
					<select id="Class" name="educare-demo demoClass" class="form-control">
						<option value="">Select Class</option>
						<?php educare_get_options('Class', '');?>
					</select>
				</div>
				
			</div>
			

			<div id="result_msg"><br><p><a class='educare_button disabled' title='Download Import Demo.csv Error'><i class='dashicons dashicons-download'></i> Download Demo</a></p></div>

		</div>

		<!-- Default value -->
		<div class="educare_data_field">
			<div class="educareImportDemo_students" data-value="<?php echo esc_attr($students);?>"></div>
		</div>
		<?php
		
	} elseif (isset($_GET['profiles'])) {
    echo '<div id="msgs">';
		educare_show_student_profiles();
		echo '</div>';
	} else {
		echo '<h1>All '.esc_html($students).'</h1>';
		echo educare_guide_for("Here you can add, edit, update data and ".esc_html($students)." details. For this you have to select the options that you see here. Options details: firt to last (All, Add, Update, Import ".esc_html(ucfirst($students)).")");

		educare_all_view($students, 15);
	}
}



/**
 * AJAX action to process data for data management tasks.
 *
 * The `educare_process_data` function is an AJAX callback used to process data for data management tasks within the Educare theme or plugin.
 * It is triggered when the corresponding AJAX action is called.
 *
 * The function first sanitizes and parses the necessary data from the AJAX request, including 'action_for' and 'form_data'.
 * It then calls the `educare_data_management` function with the sanitized 'action_for' as an argument to perform data management tasks
 * based on the specific action requested through AJAX.
 *
 * The `educare_data_management` function is expected to handle different data management tasks depending on the provided 'action_for' value.
 * The details of these data management tasks are defined within the `educare_data_management` function.
 *
 * After processing the data management tasks, the function terminates script execution with `die()` to prevent any further output.
 *
 * @return void The function processes data for data management tasks and terminates script execution.
 *
 * @since 1.4.0
 * @last-update 1.4.7
 * 
 * @example
 * This AJAX action is hooked to the 'educare_process_data' action.
 * add_action('wp_ajax_educare_process_data', 'educare_process_data');
 *
 * The function is triggered via AJAX when the 'educare_process_data' action is called.
 * It processes data for data management tasks based on the specific AJAX request.
 */
function educare_process_data() {
	// Check if the current user has the access this request as 'manage_options' capability (typically administrators).
	educare_check_access();
	
	// Sanitize and parse necessary data from the AJAX request
	$action_for = sanitize_text_field($_GET['action_for']);
	wp_parse_str($_GET['form_data'], $_GET);

	// Call the educare_data_management function to handle data management tasks based on the provided action_for value
	educare_data_management($action_for);

	// Terminate script execution after processing data management tasks
	// to prevent further output
	die();
}

// Hook the AJAX action to the 'educare_process_data' function
add_action('wp_ajax_educare_process_data', 'educare_process_data');




/** 
 * ### Get students
 * Get student by specific class, year, subject
 * 
 * @since 1.4.0
 * @last-update 1.4.2
 * 
 * @param string $Class 		for spicific class students
 * @param string|int $Year	for specific year students
 * @return mixed
 */

function educare_get_students_list($Class = null, $Year = null) {
	global $wpdb;
	$educare_students = $wpdb->prefix."educare_students";

	if (isset($_POST['students_list'])) {
		$Class = sanitize_text_field($_POST['Class']);
		$Exam = sanitize_text_field($_POST['Exam']);
		$Subject = sanitize_text_field($_POST['Subject']);
		$Year = sanitize_text_field($_POST['Year']);
	
		if (empty($Class) or empty($Exam) or empty($Subject) or empty($Year)) {
			echo '<div class="notice notice-error is-dismissible"> 
				<p>You must fill ';

				// notify if empty Class
			if (empty($Class) ) {
				echo '<b>Class</b>, ';
			}
			// notify if empty Exam
			if (empty($Exam) ) {
				echo '<b>Exam</b>, ';
			}
			// notify if empty Year
			if (empty($Subject) ) {
				echo '<b>Subject</b>, ';
			}
			// notify if empty Year
			if (empty($Year) ) {
				echo '<b>Year</b>, ';
			}
			
			echo 'Please fill all required (<i>Name, Roll No, Regi No, Class, Exam</i>) fields carefully. thanks.</p><button class="notice-dismiss"></button></div>';
		} else {
			$search = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM $educare_students WHERE Class=%s AND Year=%d",
					$Class,
					$Year
				)
			);
		

			if (count($search) > 0) {
				?>
				<div class="wrap-input">
					<span class="input-for">Filter students for specific <i>Name, Roll No, Marks...</i></span>
					<label for="searchBox" class="labels"></label>
					<input type="search" id="searchBox" placeholder="Search Results" class="fields">
					<span class="focus-input"></span>
				</div>

				<form method='post' action="" class="educareProcessMarksCrud">
					<div class="educare_print">
						<?php
						echo "<div class='notice notice-success is-dismissible'><p>
							<b>Class:</b> ".esc_html($Class)."<br>
							<b>Exam:</b> ".esc_html($Exam)."<br>
							<b>Subject:</b> ".esc_html($Subject)."<br>
							<b>Exam Year:</b> ".esc_html($Year)."<br>
							<b>Total Students:</b> ".esc_html(count($search))."
						</p><button class='notice-dismiss'></button></div>";

						$requred = educare_check_status('display');
						$requred_title = educare_requred_data($requred, true);
						?>
						<table class="view_results">
							<thead>
								<tr>
									<th>No</th>
									<th>Photos</th>
									<?php
									foreach ($requred_title as $key => $value) {
										if ($key == 'Name' || $key == 'Roll_No' || $key == 'Regi_No') {
											echo '<th>'.esc_html($value).'</th>';
										}
									}
									?>
									<th>Marks</th>
								</tr>
							</thead>

							<?php
							$count = 1;
							$sub_in = 0;
							$find_sub = str_replace(' ', '_', $Subject);

							foreach($search as $print) {
								$id = $print->id;
								$Details = json_decode($print->Details);
								$sub = json_decode($print->Subject);

								if ($sub) {

									if (property_exists($sub, $find_sub)) {
										$sub_in++;
										echo '
										<input type="hidden" name="id[]" value="'.esc_attr( $id ).'">
										<input type="hidden" name="Class" value="'.esc_attr( $Class ).'">
										<input type="hidden" name="Exam" value="'.esc_attr( $Exam ).'">
										<input type="hidden" name="Subject" value="'.esc_attr( $Subject ).'">
										<input type="hidden" name="Year" value="'.esc_attr( $Year ).'">
										<tr>
											<td>'.esc_html( $count++ ).'</td>
											<td><img src="'.esc_url($Details->Photos).'" class="student-img" alt="IMG"/></td>';
											foreach ($requred_title as $key => $value) {
												if ($key == 'Name' || $key == 'Roll_No' || $key == 'Regi_No') {
													echo '<td>'.esc_html( $print->$key ).'</td>';
												}
											}

										echo '<td width="80px"><input type="number" name="marks[]" value="'.esc_attr( educare_get_marks_by_id($id) ).'" placeholder="'.esc_attr( educare_get_marks_by_id($id) ).'" class="full"></td>
										</tr>
										';
									}

								}

							}

							if (empty($sub_in)) {
								echo '<tr><td colspan="5">Sorry, no student found in this subject <b>('.esc_html( $Subject++ ).')</b></td></tr>';
							} else {
								echo "<div class='notice notice-success is-dismissible'><p># Total ".esc_html($sub_in)." students found in this subject</p><button class='notice-dismiss'></button></div>";
							}

							?>
						</table>
					</div>

					<?php 
					if ($sub_in) {
						?>
						<div class="button_container">
							<input type="submit" name="add_marks" class="educare_button" value="Save Marks">
							<input type="submit" name="publish_marks" class="educare_button" value="Publish">
							<input type="button" id="print" class="educare_button" value="&#xf193 Print">
							<div class="action_menu"><i class="dashicons action_button dashicons-info"></i> <menu class="action_link info"><strong>Mark not visible when print?</strong><br> Please, fill up students marks and save. Then, select <b>Students List</b> and print marksheet (Save then Print).</menu></div>
						</div>
						<?php

						$add_marks_nonce = wp_create_nonce( 'add_marks' );
						$publish_marks_nonce = wp_create_nonce( 'publish_marks' );
						
						echo '<input type="hidden" name="add_marks_nonce" value="'.esc_attr($add_marks_nonce).'">';
						echo '<input type="hidden" name="publish_marks_nonce" value="'.esc_attr($publish_marks_nonce).'">';
					}
					?>
					
				</form>

				<script>
					perPage = document.querySelector('#results_per_page').value; 
					educarePagination(perPage);
				</script>
				<?php
			} else {
				echo '<div class="notice notice-error is-dismissible"><p> No student found in this class <b>('.esc_html($Class).')</b>. <a href="/wp-admin/admin.php?page=educare-all-students&add-data" target="_blank">click add students</a></p><button class="notice-dismiss"></button></div>';
			}
		}
	}
}


/** 
 * ### Get students by id
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
 * ### Students profiles
 * 
 * @since 1.4.0
 * @last-update 1.4.0
 * 
 * @return mixed
 */

function educare_show_student_profiles() {

	if (isset($_POST['educare_results_by_id'])) {
		$id = sanitize_text_field($_POST['id']);
	} elseif (isset($_GET['profiles'])) {
		$id = sanitize_text_field($_GET['profiles']);
	} else {
		$id = false;
	}

	if ($id and educare_get_students($id)) {
		$students = educare_get_students($id);
		$Mobile = $DoB = '';
		if ($students) {
			foreach ($students as $students) {
				$Name = $students->Name;
				$Roll_No = $students->Roll_No;
				$Regi_No = $students->Regi_No;
				$Class = $students->Class;
				$Group = $students->Group;
				$Year = $students->Year;
				$Details = $students->Details;
				$Details = json_decode($Details);
				$Photos = $Details->Photos;
				$Subject = json_decode($students->Subject);
				$Student_ID = $students->Student_ID;

				if (!$Student_ID) {
					$Student_ID = $id;
				}

				if ($Photos == 'URL') {
					$Photos = EDUCARE_STUDENTS_PHOTOS;
				}

				if (property_exists($Details, 'Date_of_Birth')) {
					$DoB = $Details->Date_of_Birth;
				}
				if (property_exists($Details, 'Mobile_No')) {
					$Mobile = $Details->Mobile_No;
				}

				echo '
				<div class="educare-card">
					<div class="card-head">
						<h2><img src="'.esc_url( $Photos ).'">'.esc_html( educare_check_status('institute') ).'</h2>
						<!-- <span>Educare School Management Systems</span> -->
					</div>

					<div class="card-body">
						<div class="photos">
							<img src="'.esc_url( $Photos ).'" alt="'.esc_url( $Name ).'">
						</div>

						<div class="deatails">
							<li><b>Name</b> <span>'.esc_html( $Name ).'</span></li>
							<li><b>Roll No</b> <span>'.esc_html( $Roll_No ).'</span></li>
							<li><b>Reg No</b> <span>'.esc_html( $Regi_No ).'</span></li>
							<li><b>Class</b> <span>'.esc_html( $Class ).'</span></li>
							<li><b>Group</b> <span>'.esc_html( $Group ).'</span></li>
							<li><b>Birthday</b> <span>'.esc_html( $DoB ).'</span></li>
							<li><b>Mobile</b> <span>'.esc_html( $Mobile ).'</span></li>
						</div>

						<div class="id">
							<li><b>ID.</b> <span>'.esc_html( $id ).'</span></li>
						</div>

						<div class="sign">
							<small>Signathure</small>
						</div>
					</div>
				</div>

				'.educare_guide_for('<b>Card Title:</b> You can change card title from educare settings.<br><b>Analytics (Under Construction):</b> If you need these (Analytics and Print Card) features, please send your feedback on the Educare plugin forum. If we get recommendation, this feature will be added in the next update.').'
				';

				?>

				<div class="educare_tabs">
					<div class="tab_head form_tab">
						<button class="tablink educare_button" data="Alalytics">Alalytics</button>
						<button class="tablink" data="Details">Details</button>
						<button class="tablink" data="Subject">Subject</button>
						<button class="tablink" data="Old-Data">Old Data</button>
					</div>
					
					<div id="Alalytics" class="section_name" style="display: block;">
						<div class="add_results">
							<div class="content">
								<div class="analytics">
									<h3>Analytics <div class="action_menu"><i class="dashicons action_button dashicons-info"></i> <menu class="action_link info"><strong>Under Construction</strong><hr> If you need these features, please send your feedback on the Educare plugin forum. If we get 2 requests, this feature will be added in the next update.</menu></div></h3>

									<div class="select add-subject">
										<div>
											<b>Last Exam</b><br>
											<p for="file">Average: 82</p>
											<progress id="file" value="82" max="100"> 82% </progress><br>
											<p for="file">Position: 10/85</p>
											<progress class="position" id="file" value="10" max="85"> 10% </progress><hr>
										</div><div>
											<b>Curent Status</b><br>
											<p for="file">Average: 76</p>
											<progress id="file" value="76" max="100"> 76% </progress><br>
											<p for="file">Position: 15/85</p>
											<progress class="position" id="file" value="12" max="85"> 15 </progress><hr>
										</div>
									</div>

									<div class="select add-subject">
										<div>
											<b>Exam Details</b><br>
											<p for="file">Exam participation: 3/3</p>
											<progress id="file" value="3" max="3"> 100% </progress>
											<p for="file">Passed: 2/3</p>
											<progress id="file" value="2" max="3"> 66% </progress><br>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div id="Details" class="section_name" style="display: none;">
						<div class="add_results">
							<div class="content">
								<table>
									<?php
									$count = 1; // for add specific tags (div/tr/ul) in every 4 foreach loop

									foreach ($Details as $key => $value) {
										if ($key == 'Photos') {
											break;
										}
										if ($count%2 == 1) {  
											echo "<tr>";
										}
											
										echo "<td>".esc_html(str_replace('_', ' ', $key))."</td><td>".esc_html($value)."</td>";
										
										if ($count%2 == 0) {
											echo "</tr>";
										}

										$count++;

									}
									?>
								</table>
								<br>
								<ul>
									<li>Class ID: <?php echo esc_html($id)?></li>
									<li>Student ID: <?php echo esc_html($Student_ID)?></li>
								</ul>

							</div>
						</div>
					</div>

					<div id="Subject" class="section_name" style="display: none;">
						<div class="add_results">
							<div class="content">
								<table class="grade_sheet list">
									<thead>
										<tr>
											<th>No</th>
											<th>Subject</th>
										</tr>
									</thead>
									<tbody>
										<?php 
										$no = 1;

										if ($Subject) {
											foreach ($Subject as $sub => $optional) {
												if (strpos($optional, ' ')) {
													$optional_check = '✓';
												} else {
													$optional_check = '';
												}
												echo '<tr?><td>'.esc_html($no++).'</td><td>'.esc_html($sub).' '.esc_html($optional_check).'</td></tr>';
											}
										} else {
											echo '<tr><td colspan="2">Empty</td></tr>';
										}
										
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>

					<div id="Old-Data" class="section_name" style="display: none;">
						<div class="add_results">
							<div class="content">
								<table class="grade_sheet list">
									<thead>
										<tr>
											<th>No</th>
											<th>Class</th>
											<th>Roll No</th>
											<th>Regi No</th>
											<th>Year</th>
										</tr>
									</thead>
									<tbody>
										<?php 
										$no = 1;
										
										if ($Student_ID) {
											$s_id = $Student_ID;
										} else {
											$s_id = $id;
											$Student_ID = $id;
										}

										$sql = array (
											'id' => $s_id,
											'Student_ID' => $Student_ID
										);

										$old_class = educare_get_students($id, $sql);
										
										if ($old_class) {
											foreach ($old_class as $old_data) {
												$old_id = $old_data->id;
												$old_class = $old_data->Class;
												$old_year = $old_data->Year;
												$old_Roll_No = $old_data->Roll_No;
												$old_Regi_No = $old_data->Regi_No;
												$url = admin_url() . 'admin.php?page=educare-all-students&profiles=' . $old_id;
												
												if ($old_id != $id) {
													$old_class = '<a href="'.esc_url($url).'">'.esc_html($old_class).'</a>';
												}

												echo '<tr?><td>'.esc_html($no++).'</td><td>'.wp_kses_post($old_class).'</td><td class="center">'.esc_html($old_Roll_No).'</td><td class="center">'.esc_html($old_Regi_No).'</td><td class="center">'.esc_html($old_year).'</td></tr>';
											}
										} else {
											echo '<tr><td colspan="5">No more data</td></tr>';
										}
													
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
		}
	} else {
		// save forms data
    echo '<h1>Profiles</h1><div id="msgs" style="text-align:center;">';

		echo '<span style="font-size:100px">&#9785;</span><br>
		<b>Students Not Fount!</b>';

		echo '</div>';
	}
}


/** 
 * ### Save marks from marks forms
 * 
 * @since 1.4.0
 * @last-update 1.4.3
 * 
 * @param bool $publish to publish the results
 * @return mixed|void
 */

function educare_save_marks($publish = null) {
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

		$count = $count_students = 0;
		$students = array();
		foreach ($_POST['id'] as $value) {
			// $marks[$value]['Englis'] = $_POST['marks'][$count++];
			$details[$value][$Subject] = sanitize_text_field($_POST['marks'][$count++]);
			$students[$value] = educare_get_students($value);
		}

		$data = array (
			'Class' => $Class,
			'Exam' => $Exam,
			'Year' => $Year,
			'Marks' => json_encode($details),
			'Status' => 'pending'
		);

		if ($publish) { 
			$count = 1;
			$updated = $new = 0;

			// echo '<pre>';	
			// print_r($students);	
			// echo '</pre>';
			
			foreach ($details as $key => $value) {
				if (key_exists($key, $students)) {
					$print = $students[$key][0];

					// $results_id = $print->id;
					$Roll_No = $print->Roll_No;
					$Regi_No = $print->Regi_No;

					// remove id
					unset($print->id);
					unset($print->Others);
					unset($print->Student_ID);
					$print->Class = $Class;
					$print->Exam = $Exam;
					$print->Year = $Year;
					$print->Subject = json_encode($value);
					$print->Result = '';
					$print->GPA = '';

					$print = json_encode($print);
					$print = json_decode( $print, TRUE );

					$requred = educare_check_status('display');
					$requred_fields = educare_combine_fields($requred, array('Name'), $print);
					$requred_fields = educare_get_dynamic_sql('results', $requred_fields);

					// $search_results = $wpdb->get_results("SELECT * FROM ".$educare_results." WHERE Regi_No='$Regi_No' AND Class='$Class' AND Exam='$Exam' AND Year='$Year'");
					$search_results = $wpdb->get_results($requred_fields);

					$coun++;

					if ($search_results) {
						foreach ($search_results as $results) {
							$results_id = $results->id;
							$wpdb->update($educare_results, $print, array('ID' => $results_id));
							$updated++;
						}
						
					} else {
						$wpdb->insert($educare_results, $print);
						$new++;
					}
				}
			}

			$data['Status'] = 'published';

			if($coun == $updated) {
				$msgs = 'updated';
			} else {
				$msgs = 'publish';
			}

			$msgs = "<div class='notice notice-success is-dismissible'><p>
			Successfully ".esc_html( $msgs )." all (".esc_html( $coun ).") results. <br>
			<b>Total Students:</b> ".esc_html( $coun )." <br>
			<b>Updated Students:</b> ".esc_html( $updated )." <br>
			<b>New Students:</b> ".esc_html( $new )." <br>
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
 * Retrieve marks for a specific subject and student ID from the database.
 *
 * The `educare_get_marks_by_id` function is used to retrieve marks for a specific subject and student ID from the database
 * within the Educare theme or plugin. It takes the student ID as a parameter and queries the database to fetch marks based
 * on the provided student ID, class, exam, year, and subject.
 *
 * The function first sanitizes the class, exam, year, and subject values from the `$_POST` array. It then executes a database query
 * to fetch the marks from the table with the prefix 'educare_marks' (assuming it is the correct table name) based on the provided class,
 * exam, and year. The fetched data is stored in the `$marks` variable.
 *
 * If marks are found for the provided student ID and subject combination, the function extracts the marks for that specific subject
 * from the decoded 'Marks' data stored in the database. It returns the marks as a result.
 *
 * @param int $id The student ID for which marks need to be retrieved.
 *
 * @return mixed|null The marks for the specific subject and student ID, if available; otherwise, returns null.
 *
 * @since 1.4.0
 * @last-update 1.4.0
 * 
 * @example
 * To retrieve marks for a student with ID 123 for a specific subject (e.g., 'Math'):
 * $student_id = 123;
 * $subject = 'Math';
 * $marks = educare_get_marks_by_id($student_id);
 * $math_marks = $marks[$subject];
 *
 * The function will return the marks for the 'Math' subject for the student with ID 123, if available.
 */
function educare_get_marks_by_id($id) {
	global $wpdb;
	$educare_marks = $wpdb->prefix . "educare_marks";

	// Sanitize class, exam, year, and subject values from the $_POST array
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

	if (count($marks) > 0) {
		// Extract marks for the specific subject and student ID from the decoded 'Marks' data
		foreach ($marks as $print) {
			$details = $print->Marks;
			$details = json_decode($details, true);
		}

		// Check if marks are available for the provided student ID and subject
		if (isset($details[$id][$Subject])) {
			return $details[$id][$Subject];
		}
	}

	// If no marks found for the provided student ID and subject combination, return null
	return null;
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
 * @last-update 1.4.8
 * 
 * @example
 * This AJAX action is hooked to the 'educare_process_marks' action.
 * add_action('wp_ajax_educare_process_marks', 'educare_process_marks');
 *
 * The function is triggered via AJAX when the 'educare_process_marks' action is called.
 * It processes marks and performs actions based on the specific AJAX request.
 */
function educare_process_marks() {
	// Check user capability to manage options
	if (!current_user_can('manage_options')) {
		exit;
	}
	
	// Sanitize and parse necessary data from the AJAX request
	$action_for = sanitize_text_field($_POST['action_for']);
	$data_for = sanitize_text_field($_POST['data_for']);
	// Remove the backslash
	$_POST['form_data'] = stripslashes($_POST['form_data']);
	// parses query strings and sets the parsed values into the $_POST array.
	wp_parse_str($_POST['form_data'], $_POST);
	$_POST[$action_for] = $action_for;
	$_POST['data_for'] = $data_for;

	// Verify the nonce to ensure the request originated from the expected source
	educare_verify_nonce($action_for, $action_for.'_nonce');

	// Sanitize other data for marks processing
	$Class = sanitize_text_field($_POST['Class']);
	$Group = sanitize_text_field($_POST['Group']);
	$Subject = sanitize_text_field($_POST['Subject']);
	$Exam = sanitize_text_field($_POST['Exam']);
	$Year = sanitize_text_field($_POST['Year']);

	// Check the specific action requested through AJAX
	if (isset($_POST['get_Class'])) {
		// Retrieve options for the 'Class' field based on the selected subject
		educare_get_options_for_subject('Class', $Class, $Subject);
	} elseif (isset($_POST['get_Group'])) {
		// Retrieve options for the 'Group' field based on the selected subject
		educare_get_options_for_subject('Group', $Group, $Subject);
	} elseif (isset($_POST['publish_marks'])) {
		// Publish marks and get updated students' list
		educare_save_marks(true);
		educare_get_students_list();
	} else {
		// Save marks and get updated students' list
		educare_save_marks();
		educare_get_students_list();
	}

	// Terminate script execution after processing marks and retrieving the students' list
	// to prevent further output
	die();
}

// Hook the AJAX action to the 'educare_process_marks' function
add_action('wp_ajax_educare_process_marks', 'educare_process_marks');




/**
 * AJAX action to process options based on a target field and a specific subject.
 *
 * The `educare_process_options_by` function is an AJAX callback used to process options based on a target field and a specific subject
 * within the Educare theme or plugin. It is triggered when the corresponding AJAX action is called.
 *
 * If the AJAX request is initiated to add a new subject ('add_subject' parameter exists in the form data), the function parses the form data.
 * Otherwise, it retrieves the 'data_for' and 'subject' parameters from the AJAX request and calls the `educare_show_options` function to display
 * the available options for the specified 'data_for' (target field) and 'subject' combination, specifically for the 'Group' field.
 *
 * The function terminates script execution with `die()` after processing the options to prevent any further output.
 *
 * @return void The function processes options based on a target field and a specific subject and terminates script execution.
 *
 * @example
 * This AJAX action is hooked to the 'educare_process_options_by' action.
 * add_action('wp_ajax_educare_process_options_by', 'educare_process_options_by');
 *
 * The function is triggered via AJAX when the 'educare_process_options_by' action is called.
 * It processes options based on a target field and a specific subject.
 */
function educare_process_options_by() {
	// Check if the current user has the access this request as 'manage_options' capability (typically administrators).
	educare_check_access();

	// Check if the AJAX request is to add a new subject
	if (isset($_POST['add_subject'])) {
		// Parse the form data from the AJAX request
		wp_parse_str($_POST['form_data'], $_POST);
	} else {
		// Retrieve the 'data_for' and 'subject' parameters from the AJAX request
		$data_for = sanitize_text_field($_POST['data_for']);
		$subject = sanitize_text_field($_POST['subject']);

		// Call the function to display available options for the specified 'data_for' and 'subject' combination
		// Specifically, for the 'Group' field
		educare_show_options($data_for, $subject, 'Group');
	}

	// Terminate script execution after processing options to prevent further output
	die();
}

// Hook the AJAX action to the 'educare_process_options_by' function
add_action('wp_ajax_educare_process_options_by', 'educare_process_options_by');




/**
 * Generates HTML markup for select options based on a target field and a specific value.
 *
 * The `educare_options_by` function is used to generate HTML markup for select options in the Educare theme or plugin.
 * It takes a 'target' field and a specific 'val' value as parameters and generates a select dropdown with options.
 *
 * The function generates the select dropdown with options by calling the `educare_get_options` function, which retrieves
 * options for the specified target field. The dropdown includes a default 'None (Default)' option and additional options
 * obtained from the `educare_get_options` function.
 *
 * If the target is 'Class', the function also generates an additional select dropdown with ID '[target]_list' for selecting
 * subjects on the 'class/add marks' page. This additional dropdown includes an option 'Select Subject' by default.
 *
 * The function also creates a hidden input field with ID 'old-[target]' to store the original value of the target field.
 *
 * @param string $target The target field for which options are generated (e.g., 'Class', 'Year', 'Group', etc.).
 * @param string $val    The specific value to be selected in the generated dropdown.
 *
 * @return void The function outputs the generated HTML markup for the select dropdown and hidden input field.
 *
 * @since 1.4.0
 * @last-update 1.4.0
 * 
 * @example
 * To generate a select dropdown for the 'Class' field with the value '10A':
 * educare_options_by('Class', '10A');
 *
 * The function will generate the select dropdown and hidden input field for the 'Class' field.
 */
function educare_options_by($target, $val) {
	?>
	<div class="select">
		<select id="<?php echo esc_attr($target);?>" name="Group" class="form-control">
			<option value="">None (Default)</option>
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
 * Displays student results based on specific criteria and handles AJAX requests for result viewing.
 *
 * The `educare_view_results` function is responsible for displaying student results within the Educare theme or plugin.
 * It can be used for both standard result displays and custom result displays (if custom results are enabled and registered).
 *
 * If called through AJAX, the function first checks for reCAPTCHA validation (if enabled) to ensure the request is not from a robot.
 * It then retrieves the required fields for result display and the custom results status. After parsing the form data from the AJAX request,
 * the function generates an SQL query to fetch the matching student results from the database.
 *
 * If results are found, the function either executes the custom result action (if defined) or displays the default result view.
 * If custom results are enabled and an action hook 'educare_custom_results' is registered, it is executed for the custom result view.
 * Otherwise, the function displays the results using the default result view function `educare_default_results`.
 *
 * If no results are found or required fields are missing in the form data, appropriate error messages are displayed.
 * If the function is called through AJAX, it sends a JSON response containing the error message back to the client-side.
 * Otherwise, it displays the error message along with the student search form using `educare_get_search_forms`.
 *
 * @param bool|null $ajax (optional) Indicates if the function is called through AJAX. Default is null.
 *
 * @return void The function displays student results or appropriate error messages based on the form data.
 *
 * @since 1.4.0
 * @last-update 1.4.1
 * 
 * @example
 * To display standard student results:
 * educare_view_results();
 *
 * To handle AJAX requests for result viewing (called through AJAX with $ajax = true):
 * educare_view_results(true);
 *
 * The function is responsible for displaying student results based on specific criteria and handling AJAX requests.
 */
function educare_view_results($ajax = null) {
	global $wpdb, $requred_fields, $requred_data, $requred_title;
	$table_name = $wpdb->prefix . 'educare_results';

	$ignore = array(
		'Name'
	);

	$requred = educare_check_status('display');
	$requred_title = educare_requred_data($requred, true, true);
	$requred_fields = educare_combine_fields($requred, $ignore);
	$requred_data = educare_combine_fields($requred);
	$custom_results = educare_check_status('custom_results');

	

	if (isset($_POST['educare_results']) or isset($_POST['id'])) {
		// Verify the nonce to ensure the request originated from the expected source
		educare_verify_nonce();

		// check educare re_captcha status and execute
		if (educare_check_status('re_captcha') == 'checked') {
			if (isset($_POST['educare_results'])) {
				
				if (isset($_POST['g-recaptcha-response']) and $_POST['g-recaptcha-response'] != "") {
					$secret = educare_check_status('secret_key');
					$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $_POST['g-recaptcha-response']);
					$responseData = json_decode($verifyResponse);
					
					if (! $responseData->success) {
						$msgs = 'Invalid request!';

						if ($ajax) {
							$message = array ('message' => $msgs);
							return wp_send_json($message);
						} else {
							echo '<div class="results_form error_notice error">'.esc_html($msgs).'</div>';
							return educare_get_search_forms(true);
						}
					}
				} else {
					$msgs = 'Make sure you are not a robot!';

					if ( current_user_can( 'manage_options' ) and educare_check_status('site_key') == '' ) {
						$msgs .= '<br><br><p class="left-text"><small>Site key is missing! Currently, you have not entered or paste your google recaptcha site key at - <br><a href="'.esc_url( admin_url() ).'/admin.php?page=educare-settings&menu=Security" target="_blank"><code>Educare > Settings > Security > Site Key</code></a><br><br>To ignore these messages, please enter/paste google recaptcha key or disable Google Recaptcha options from educare settings<br><br>(Only admin can view these messages)</small></p>';
					}

					if ($ajax) {
						$message = array ('message' => $msgs);
						return wp_send_json($message);
					} else {
						echo '<div class="results_form error_notice error">'.wp_kses_post($msgs).'</div>';
						return educare_get_search_forms(true);
					}
				}
			}
		}

		// if everything is ok
		if (isset($_POST['id'])) {
			$id = sanitize_text_field($_POST['id']);

			// check if users is admin and can manage_options or not. Beacause, only admin can accsess results by ID
			if ( current_user_can( 'manage_options' ) ) {
				$sql = "id='$id'";
			} else {
				echo '<div class="results_form error_notice error"><p><h4>Sorry, you are not allowed to access this page.!</h4></p><br><p>Please reload or open this page and try again</p></div>';
				return;
			}
			
		} else {
			$sql = educare_get_sql($requred_fields);
			$id = '';
		}
		
		if (!educare_is_empty($requred_fields) or $id) {
			$select = "SELECT * FROM $table_name WHERE $sql";
			$results = $wpdb->get_results($select);

			if ($results) {
				foreach($results as $print) {

					if ($custom_results == 'checked' and has_action('educare_custom_results')) {
						return do_action( 'educare_custom_results', $print );
					} else {
						return educare_default_results($print);
					}
				}
			} else {
				$msgs = 'Result not found. Please try again';

				if ($ajax) {
					$message = array ('message' => $msgs);
				return wp_send_json($message);
				} else {
					echo '<div class="results_form error_notice error">'.esc_html($msgs).'</div>';
					return educare_get_search_forms(true);
				}
			}
		} else {
			$msgs = educare_is_empty($requred_fields, 'display', true);

			if ($ajax) {
				$message = array ('message' => $msgs);
				return wp_send_json($message);
			} else {
				echo '<div class="results_form error_notice error">'.wp_kses_post($msgs).'</div>';
				return educare_get_search_forms(true);
			}
		}
	} else {
		educare_get_search_forms(true);
	}
}




/**
 * AJAX action to process the viewing of student results.
 *
 * The `educare_proccess_view_results` function is an AJAX callback that handles the request to view student results
 * within the Educare theme or plugin. It is triggered when the corresponding AJAX action is called.
 *
 * The function first verifies the nonce to ensure the request originated from the expected source and to prevent CSRF attacks.
 * After nonce verification, it parses the form data from the AJAX request and sets the 'educare_results' flag to 'educare_results'.
 * Then, it calls the `educare_view_results` function to display the student results based on the provided data.
 *
 * The function terminates script execution with `die()` after calling the view results function to prevent any further output.
 *
 * @return void The function processes the viewing of student results and terminates script execution.
 * 
 * @since 1.4.0
 * @last-update 1.4.0
 *
 * @example
 * This AJAX action is hooked to the 'educare_proccess_view_results' action for both logged-in and non-logged-in users.
 * add_action('wp_ajax_nopriv_educare_proccess_view_results', 'educare_proccess_view_results');
 * add_action('wp_ajax_educare_proccess_view_results', 'educare_proccess_view_results');
 *
 * The function is triggered via AJAX when the 'educare_proccess_view_results' action is called.
 * It verifies the nonce, processes form data, and displays student results.
 */
function educare_proccess_view_results() {
	// Parse the form data from the AJAX request
	wp_parse_str($_POST['form_data'], $_POST);
	// Set the 'educare_results' flag to 'educare_results' to indicate viewing results
	$_POST['educare_results'] = 'educare_results';
	// Call the function to display the student results based on the provided data
	educare_view_results(true);
	
	// Terminate script execution after displaying student results to prevent further output
	die();
}

// Hook the AJAX action to the 'educare_proccess_view_results' function for both logged-in and non-logged-in users
add_action('wp_ajax_nopriv_educare_proccess_view_results', 'educare_proccess_view_results');
add_action('wp_ajax_educare_proccess_view_results', 'educare_proccess_view_results');




/**
 * AJAX action to process the promotion of students to a new class.
 *
 * The `educare_proccess_promote_students` function is an AJAX callback that handles form submissions for promoting students
 * to a new class within the Educare theme or plugin. It is triggered when the corresponding AJAX action is called.
 *
 * The function parses the form data from the AJAX request, sets the 'promote' flag to true, and then calls the main promotion
 * function `educare_promote_students()` to process the promotion based on the provided data.
 *
 * The function terminates script execution with `die()` after calling the promotion function to prevent any further output.
 *
 * 
 * @return void The function processes the promotion of students to a new class and terminates script execution.
 * 
 * @since 1.4.0
 * @last-update 1.4.8
 *
 * @example
 * This AJAX action is hooked to the 'educare_proccess_promote_students' action.
 * add_action('wp_ajax_educare_proccess_promote_students', 'educare_proccess_promote_students');
 *
 * The function is triggered via AJAX when the 'educare_proccess_promote_students' action is called.
 * It processes form data and promotes students to a new class.
 */
function educare_proccess_promote_students() {
	// Remove the backslash
	$_POST['form_data'] = stripslashes($_POST['form_data']);
	// parses query strings and sets the parsed values into the $_POST array.
	wp_parse_str($_POST['form_data'], $_POST);

	// Set the 'promote' flag to true to initiate the promotion process
	$_POST['promote'] = true;

	// Call the main promotion function to process the promotion
	educare_promote_students();

	// Terminate script execution after promotion to prevent further output
	die();
}

// Hook the AJAX action to the 'educare_proccess_promote_students' function
add_action('wp_ajax_educare_proccess_promote_students', 'educare_proccess_promote_students');





/**
 * Promotes students to a new class based on specific criteria and displays the promotion results.
 *
 * The `educare_promote_students` function handles the promotion of students to a new class.
 * It processes form submissions and promotes students based on provided criteria, such as the current class, year, and exam results.
 *
 * If the form is submitted with the 'promote' action, the function performs the following steps:
 * 1. Sanitizes and extracts the submitted data for processing.
 * 2. Checks for required fields, such as 'Class' and 'Year', and optionally, 'Group' and 'Promoted_Exam'.
 * 3. Retrieves the list of students that match the specified criteria.
 * 4. Evaluates exam results and checks if students are eligible for promotion based on their results (optional).
 * 5. Updates the student records with the new class, year, and group (if changed) if they meet the promotion criteria.
 * 6. Displays the promotion results with the number of students promoted, already existing students, and failed promotions.
 *
 * The function utilizes various utility functions like `educare_combine_fields`, `educare_check_status`, `educare_check_settings`,
 * `educare_get_sql`, and `educare_guide_for` for processing and displaying the promotion results.
 *
 * @since 1.4.0
 * @last-update 1.4.0
 * 
 * @return void The function processes form submissions, performs promotions, and displays the results accordingly.
 *
 * @example
 * educare_promote_students();
 *
 * The function will process form submissions and display the promotion results accordingly.
 */
function educare_promote_students() {
	global $wpdb;
	$students_table = $wpdb->prefix . 'educare_students';
	$results_table = $wpdb->prefix . 'educare_results';
	$status = '';

	if (isset($_POST['promote'])) {
		// Check user capability to manage options
		if (!current_user_can('manage_options')) {
			exit;
		}

		// Verify the nonce to ensure the request originated from the expected source
		educare_verify_nonce('educare_promote_nonce');

		$requred = array (
			'Class',
			'Year',
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
			
			$total = $promoted = $failed = $exist =  0;

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
					$id = $print->id;
					$Student_ID = $print->Student_ID;
					$Roll_No = $print->Roll_No;
					$Regi_No = $print->Regi_No;
					$_POST['Promoted_Roll_No'] = $Roll_No;
					$_POST['Promoted_Regi_No'] = $Regi_No;

					foreach ($requred as $key => $value) {
						$requred_fields[$key] = $_POST['Promoted_'.$key];
					}

					$sql = educare_get_sql($requred_fields);
					$select = "SELECT * FROM $students_table WHERE $sql";
					$students = $wpdb->get_results($select);
					
					if ($students) {
						$exist++;
					} else {

						if ($Exam) {
							$requred_fields['Exam'] = $Exam;
							$find_results = array (
								'Roll_No' => $Roll_No,
								'Regi_No' => $Regi_No,
								'Class' => $Class,
								'Exam' => $Exam,
								'Year' => $Year,
							);

							$requred = educare_check_status('display');
							$requred = educare_combine_fields($requred, array('Name'), $find_results);

							$sql = educare_get_sql($find_results);
							$select = "SELECT * FROM $results_table WHERE $sql";
							$results = $wpdb->get_results($select);

							if ($results) {

								if ($status == 'passed') {
									foreach ($results as $show) {
										$id = $show->id;
										$subject = json_decode($show->Subject, true);
										$promote =  educare_results_status($subject, $id, '', true);

										if (!$promote) {
											$failed++;
										}
									}
								} else {
									$promote = true;
								}

							} else {
								$promote = false;
								$failed++;
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
							$subject = json_decode($print->Subject, true);

							if (isset($_POST['Promoted_Group'])) {
								$group = sanitize_text_field($_POST['Promoted_Group']);
								$print->Group = $group;
							} else {
								$group = $print->Group;
							}

							$new_group = array();
							
							if ($group) {
								$group = educare_check_settings('Group', $group);

								foreach ($group as $sub) {
									if (key_exists($sub, $subject)) {
										array_push($new_group ,$sub);
									}
								}
							}

							$sub = educare_check_settings('Class', $new_class);

							if ($new_group) {
								$sub = array_merge($sub, $new_group);
							}
							
							$optional = false;
							if ($subject) {
								foreach ($subject as $new_sub => $op) {
									if (strpos($op, ' ')) {
										$optional = $new_sub;
									}
								}
							}
							
							$procces_sub = array();
							foreach ($sub as $key => $value) {
								if ($value == $optional) {
									$procces_sub[$value ] = '1 ';
								} else {
									$procces_sub[$value ] = '';
								}
							}
							
							$print->Subject = json_encode($procces_sub);

							// Insert data
							$print = json_decode(json_encode($print), TRUE);
							
							$old_data = sanitize_text_field($_POST['old_data']);

							if ($old_data) {
								$wpdb->insert($students_table, $print);
								$modify_msgs = 'promoted';
							} else {
								$wpdb->update($students_table, $print, array('ID' => $id));
								$modify_msgs = 'update';
							}
							
							// Count promoted data/students
							$promoted++;
						}

					}

				}

				if ($promoted) {
					$msgs = 'Successfully '.$modify_msgs.' ' . $promoted . ' students';
					$success = 'success';
				} else {
					$msgs = 'No students found for promote';
					$success = 'error';
				}

				if ($Exam) {
					$failed = $failed . ' students';
				} else {
					$failed = 'Not requred';
				}

				echo "<div class='notice notice-".esc_html($success)." is-dismissible'><p>
					".esc_html($msgs)." <br>
					<b>Total:</b> ".esc_html($total)." students<br>
					<b>Promote:</b> ".esc_html($promoted)." students<br>
					<b>Already exist:</b> ".esc_html($exist)." students<br>
					<b>Failed:</b> ".esc_html($failed)."
				</p><button class='notice-dismiss'></button></div>";
				
			} else {
				echo educare_guide_for('Students not found');
			} 
		} else {
			echo educare_is_empty($_POST, true);
		}
		
	}
	
	?>
		<div id="educare-form">
		<form class="add_results" action="" method="post">
			<div class="content">
				<?php
					$nonce = wp_create_nonce( 'educare_promote_nonce' );
					echo '<input type="hidden" name="nonce" value="'.esc_attr($nonce).'">';
				?>
				
				<div class="select">
					<div>
						<div>Promote From (Old)</div>
						<select id="Class" name="Class" class="form-control">
							<?php educare_get_options('Class', $Class);?>
						</select>

						<select id="Year" name="Year" class="fields">
							<?php educare_get_options('Year', $Year);?>
						</select>

						<select id="Group" name="Group" class="fields">
						<option value="">All Group</option>
							<?php educare_get_options('Group', $Group);?>
						</select>

						<select id="status" name="status" class="fields">
						<option value="passed" <?php if($status == 'passed') echo 'selected' ?>>Students have passed</option>
						<option value="participated" <?php if($status == 'participated') echo 'selected' ?>>Participated in the exam</option>
						</select>
					</div>

					<div>
					<div>To Selected Term (New)</div>
						<select id="Promoted_Class" name="Promoted_Class" class="form-control">
							<option value="">Select Class</option>
							<?php educare_get_options('Class', $Promoted_Class);?>
						</select>

						<select id="Promoted_Year" name="Promoted_Year" class="fields">
						<option value="">Select Year</option>
							<?php educare_get_options('Year', $Promoted_Year);?>
						</select>

						<select id="Promoted_Group" name="Promoted_Group" class="fields">
						<option value="">Select Group</option>
							<?php educare_get_options('Group', $Promoted_Group);?>
						</select>

						<select id="Promoted_Exam" name="Promoted_Exam" class="fields">
							<option value="">Not Requred</option>
							<option value="all" disabled>All Exam</option>
							<?php educare_get_options('Exam', $Promoted_Exam);?>
						</select>
					</div>
				</div>
				
				<br>
				<input type="checkbox" name="old_data" checked> Keep old (Class) data
				<br><br>

				<input type="submit" id="promote" name="promote" class="educare_button" value="&#xf118 Promote">

			</div>
		</form>
	</div>
	<?php
}


/**
 * Enqueues the AJAX script and sets up AJAX parameters for the Educare theme or plugin.
 *
 * The `educare_enqueue_ajax_script` function is responsible for loading the AJAX script required for handling
 * asynchronous requests in the Educare theme or plugin. It also sets up AJAX parameters, such as the URL for
 * the WordPress AJAX handler and a security nonce, which are used for secure communication between the client
 * and server during AJAX requests.
 *
 * The script is enqueued with the handle 'educare-ajax-script' and depends on jQuery, ensuring that jQuery is
 * loaded before this script to prevent compatibility issues.
 *
 * @return void The function enqueues the AJAX script and localizes it with the required AJAX parameters.
 *
 * @example
 * educare_enqueue_ajax_script();
 *
 * The script will be loaded with the following parameters available:
 * - educareAjax.url: The URL to the WordPress AJAX handler (admin-ajax.php) used for AJAX requests.
 * - educareAjax.nonce: A security nonce generated using 'educare_form_nonce', used for verifying the
 * authenticity of AJAX requests and preventing CSRF attacks.
 */
function educare_enqueue_ajax_script() {
	// Enqueue the AJAX script and specify its dependencies (jQuery) with version '1.0'.
	wp_enqueue_script( 'educare-wp', EDUCARE_URL.'assets/js/educare-wp.js', array( 'jquery' ), '1.0', true );

	// Localize the AJAX script with necessary parameters.
	wp_localize_script( 'educare-wp', 'educareAjax', array(
			'url'   => admin_url( 'admin-ajax.php' ), // URL to the WordPress AJAX handler.
			'nonce' => wp_create_nonce( 'educare_form_nonce' ), // Security nonce for AJAX requests.
	) );
}

add_action( 'admin_enqueue_scripts', 'educare_enqueue_ajax_script' );
add_action( 'wp_enqueue_scripts', 'educare_enqueue_ajax_script' );


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
function educare_verify_nonce($nonce = 'educare_form_nonce', $nonce_field = 'nonce') {
	$nonce = sanitize_text_field( $nonce );
	$nonce_field = sanitize_text_field( $nonce_field );

	// check_ajax_referer( 'educare_form_nonce', 'nonce' );
	if ( ! isset( $_POST[$nonce_field] ) || ! wp_verify_nonce( $_POST[$nonce_field], $nonce ) ) {
    // Nonce is not valid, handle error or unauthorized access
		echo educare_show_msg('Invalid Request', false);
		die;
	}
}


?>