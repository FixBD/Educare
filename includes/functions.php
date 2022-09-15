<?php

/** 
* Include admin menu
	# View results
	# Add results
	# Import results
	# Update results
	# Grading Systems
	# Settings
	# About us
*/
require_once(EDUCARE_ADMIN.'menu.php');

define('EDUCARE_STUDENTS_PHOTOS', EDUCARE_URL.'assets/img/default.svg');


/** =====================( Functions Details )======================
	
	* Usage: educare_esc_str($string);
	
	* @since 1.0.0
	* @last-update 1.0.0
	
	* @param string $str	The string to be escaped.
	* @return The escaped string.
	
==================( function for escaped string, )==================*/

function educare_esc_str($str) {
	$str = preg_replace("/[^A-Za-z0-9 _.]/",'',$str);
	// One more protection with WP esc_attr()
	$str = esc_attr($str);
	return $str;
}



/** =====================( Functions Details )======================
	
	* Usage example: educare_check_status('confirmation');
	# For checking settings status, if specific settings is anable return{checked}. or disabled return{unchecked}.
	
	# Cunenty there are 8 settings status support
	
		Name	============ 	Default	===	Details =============================
		1. confirmation 	 	checked			/ for delete confirmation
		2. guide			  	 	checked			/ for helps (guidelines) pop-up
		3. photos 	 			  checked			/ for students photos
		4. auto_results 	 	checked			/ for auto results calculation
		5. delete_subject		checked			/ for delete subject with results
		6. clear_field 		 	checked			/ for delete extra field with results
		7. display 		 	 		array()			/ for modify Name, Roll and Regi number (@since 1.2.0)
		8. grade_system 		array()			/ for grading systems or custom rules (@since 1.2.0)
	
	for check current status =>
		1. educare_check_status('confirmation');
		2. educare_check_status('guide');
		3. educare_check_status('photos');
		4. educare_check_status('auto_results');
		5. educare_check_status('delete_subject');
		6. educare_check_status('clear_field');
		7. educare_check_status('Name', true); // true because, this is an array
	
	# Above callback function return current status => checked or unchecked
	# Notes: all default status => checked
	
	* @since 1.0.0
	* @last-update 1.2.0
	
	* @param string $target	Select specific key and get value
	* @param bull $display	Select specific key with array
	
	* @return string
	
==================( function for check settings status, )==================*/

function educare_check_status($target = null, $display = null) {
	
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
   
	$search = $wpdb->get_results("SELECT * FROM $table WHERE list='Settings'");
	
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



/** =====================( Functions Details )======================
	
	# Notify user if anythink wrong in educare (database)

	* @since 1.2.0
	* @last-update 1.2.4

	* @param bool $fix_form		to get database update form
	* @param string $db				for specific database

	* @return void|HTML
	
===================( function for database error notice )=================== **/

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
					$remove = $wpdb->prefix.$edb;
					$wpdb->query( "DROP TABLE $remove" );
				}

				// new database
				educare_database_table();
				
			} else {
				$edb = sanitize_text_field( $db );
				$edb = $wpdb->prefix.$edb;
				$wpdb->query( "DROP TABLE $edb" );
				educare_database_table($db);
			}
			
			echo "<div class='notice notice-success is-dismissible'><p>Successfully Updated (Educare) Database click here to <a href='".esc_url($_SERVER['REQUEST_URI'])."'>Start</a></p></div>";
		} else {
			?>
			<form class="add_results" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
				<b>Database Update Required</b>
				<p>Your current (Educare) database is old or corrupt, you need to update database to run new version <b><?php echo esc_html( EDUCARE_VERSION );?></b> of educare, it will only update <strong>Educare related database</strong>. Click to update database</p>
				<p><strong>Please note:</strong> You should backup your (Educare) database before updating to this new version (only for v1.0.2 or earlier users).</p>
				<button class="button" name="update_educre_database">Update Educare Database</button>
			</form>
			<?php
		}
	} else {
		echo "<div class='notice notice-error is-dismissible'><p>Something went wrong!. Please go to (Educare) settings or <a href='/wp-admin/admin.php?page=educare-settings'>click here to fix</a></p></div>";
	}
	echo '<div>';
}



/** =====================( Functions Details )======================
	
	* Usage example: educare_confirmation($list, $content);
	# Pop-up delete/remove confirmation if {confirmation} status is => checked.
	
	*	For example, when users delete/remove a Subject, like - Science. this function pop-up (alart) the user like this - You want to remove 'Science' from the 'Subject' list. Are you sure?.
	
	* Simple but powerful!
	
	* @since 1.0.0
	* @last-update 1.0.0
	
	* @param string $list				Specific keys value: Subject/Class/Exam/Year/Extra Field...
	* @param string $content		Specific keys value
	* @param string|int $year		Specific keys value

	*@return string
	
==================( function for  delete/remove confirmation )==================*/

function educare_confirmation($list, $content, $year = null) {
	if (educare_check_status('confirmation') == 'checked') {
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
	}
}


/** =====================( Functions Details )======================
	* Function for Educare smart guidelines

	* @since 1.0.0
	*	@last-update v1.2.2

	* @param string $guide	  Specific string/msgs
	* @param string $details	Specific var/string

	*	@return string|html
	
==================( function for  guidelines )==================*/

function educare_guide_for($guide, $details = null) {
	if (educare_check_status('guide') == 'checked') {
		
		if ($guide == 'add_class') {
			$guide = "Add more <b>Class</b> or <b>Exam</b>. Do you want to add more <b>Class</b> or <b>Exam</b>? click here to <a href='".esc_url('/wp-admin/admin.php?page=educare-settings#Class')."' target='_blank'>Add Class</a> or <a href='".esc_url('/wp-admin/admin.php?page=educare-settings#Exam')."' target='_blank'>Add Exam</a>";
		}
		
		if ($guide == 'add_extra_field') {
			$guide = "Do you want to add more <b>Field</b> ? click here to <a href='".esc_url('/wp-admin/admin.php?page=educare-settings#Extra_field')."' target='_blank'>Add extra field</a>";
		}
		
		if ($guide == 'add_subject') {
			$guide = "Do you want to add more <b>Subject</b> ? click here to <a href='".esc_url('/wp-admin/admin.php?page=educare-settings#Subject')."' target='_blank'>Add Subject</a>";
		}
		
		if ($guide == 'optinal_subject') {
			$guide = "If this student has an optional subject, then select optional subject. otherwise ignore it.<br><b>Note: It's important, when students will have a optional subject</b>";
		}
		
		if ($guide == 'import') {
			$guide = "Notes: Please carefully fill out all the details of your import (<b>.csv</b>) files. If you miss one, you may have problems to import the results. So, verify the student's admission form well and then give all the details in your import files. Deafault required field are: <b><i>Name, Roll No, Regi No, Exam, Class and Year</i></b>. So, don't miss all of this required field!<br><br>Notes: If you don't know, how to create a import files. Please download the demo files given below.";
		}
		
		if ($guide == 'import_error') {
			$guide = "<div class='notice notice-error is-dismissible'><p>It's not possible to import ".esc_html( $details )." results while during this process. Maybe, that's results field or data is missing. Notes: If you keep any empty field - use comma (,). for example: Your csv files Head like this - <br><b>Name,Roll_No,Regi_No,Class,Exam,Year,Field1,Field2,Field3,Field4,Field5</b><br>You need to get empty (Field1, Field3 and Field4) For that our csv data will be look like - <br> (<font color='green'>Atik,123456,12345678,Class 8,Exam no 2,2022<font color='red'>,,</font>Field2<font color='red'>,,,</font>Field5</font>) not (<font color='red'>Atik,123456,12345678,Class 8,Exam no 2,2022,Field2,Field5</font>)</p></div>
			";
			return $guide;
		}

		if ($guide == 'display_msgs') {
			$guide = "It is not possible to deactivate both (<b>Regi number or Roll number</b>). Because, it is difficult to find students without roll or regi number. So, you need to deactivate one of them (Regi or Roll Number). If your system has one of these, you can select it. Otherwise, it is better to have both selected (<b>Recommended</b>).";
		}

		if ($guide == 'db_error') {
			$guide = "Something went wrong! in your settings. Please fix it. Otherwise some of our plugin settings will be not work. also it's effect your site. So, please contact to your developer for solve this issue. Or go to plugin (Educare) settings and press <b>Reset Settings</b>. Hope you understand.";
		}
		
		return "<div class='notice notice-success is-dismissible'><p>".wp_kses_post($guide)."</p></div>";
	}
}



/** =====================( Functions Details )======================
	
	* Usage example: educare_value('Bangla', 1);
	# display result value.
	
	
	# Simple but super power!
	# Without this function result system is gone!!!!!
	
	* @since 1.0.0
	* @last-update 1.2.4
	
	* @param string $list	Select object array
	* @param int $id			Select specific database rows by id
	* @param int $array		Select select array|object
	* @param true|false		if data for students
	
	* @return string|int|float|bool / database value
	
==================( function for display result value )==================*/

function educare_value($list, $id, $array = null, $add_students = null) {
	global $wpdb;
	
	if ($add_students) {
		$table_name = $wpdb->prefix . 'educare_students';
	} else {
		$table_name = $wpdb->prefix . 'educare_results';
	}

	$educare_results = $wpdb->get_results("SELECT * FROM $table_name WHERE id='$id'");
	
	if ($educare_results) {
		$value = '';
		
		foreach($educare_results as $print) {
			$value = $print->$list;
		}
		
		if ($array) {
			$value = json_decode($value, true);
			// Chek if key exist or not. Otherwise its show an error
			if (key_exists($array, $value)) {
				return $value[$array];
			}
		} else {
			return $value;
		}
	}
}



/** =====================( Functions Details )======================
	
	* Usage example: educare_get_options('Class', $Class);
	# function for display content options
	
	
	# it's only return <option>...</option>. soo, when calling this function you have must add <select>...</select> (parent) tags before and after.
	
	# Example:
	
		echo '<select id="Class" name="Class" class="fields">';
			echo '<option value="0">Select Class</option>';
			educare_get_options('Class', $Class)
		echo '</select>';
		
		echo '<select id="Class" name="Exam" class="fields">';
			echo '<option value="0">Select Class</option>';
			educare_get_options('Exam', $Exam)
		echo '</select>';

	* @since 1.0.0
	* @last-update 1.2.0
	
	* @param string $list			Specific string
	* @param int|string $id		Specific var
	
	* @return string
	
==================( function for display content options/field )==================*/

function educare_get_options($list, $id, $selected_class = null, $add_students = null) {
	
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
	
	if ($list == 'Subject' or $list == 'optinal') {
		$results = $wpdb->get_results("SELECT * FROM $table WHERE list='Class'");
	} else {
		$results = $wpdb->get_results("SELECT * FROM $table WHERE list='$list'");
	}
	
	if ($results) {
		foreach ( $results as $print ) {
			$results = $print->data;
			// $subject = ["Class", "Regi_No", "Roll_No", "Exam", "Name"];

			if ($list == 'Class') {
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
					$placeholder = "Inter Students ".str_replace('_', ' ', $display)."";
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
					$value = sanitize_text_field(educare_value('Subject', $id, $name));
				} else {
					$value = sanitize_text_field(educare_value('Details', $id, $name));
				}
			}
			
			if ($list == 'Subject') {
				
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
					<td><label for="<?php echo esc_attr($name);?>" class="mylabels" id="<?php esc_attr($name);?>"></label>
					<input id="<?php echo esc_attr($name);?>" type="number" name="<?php echo esc_attr($name);?>" class="myfields" value="<?php echo esc_attr($value);?>" placeholder="<?php echo esc_attr("$value $placeholder");?>"></td>
					
					<td><input type="number" name="grade[]" class="myfields" value="<?php echo esc_attr($value);?>" placeholder="auto" <?php echo esc_attr($disabled);?>></td>
				</tr>
				<?php
			}
			
			if ($list == 'optinal') {
				
				$optinal = strtok($value, ' ');
				$selected = '';
				$checked = '';
				if ($optinal == 1) { $selected = 'selected'; $checked = '✓'; }
					
				echo '<option value="'.esc_attr($display).'" '.esc_attr($selected).'>'.esc_html($display).' '.esc_html($checked).'</option>';
				
			}
			
			if ($list == 'Class' or $list == 'Exam' or $list == 'Year') {
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
					<p>Currently, you don't have added any subject in this class (<?php echo esc_html($selected_class);?>). Please add some subject by <?php echo "<a href='".esc_url('/wp-admin/admin.php?page=educare-settings#Subject')."' target='_blank'>Click Here</a>.";?> Thanks </p>
				</div></td>
			</tr>
			<?php
		} else {
			echo "<div class='notice notice-error is-dismissible'><p>Currently, You don't have added any ".esc_html(str_replace('_', ' ', $list))." Please, <a href='".esc_url("/wp-admin/admin.php?page=educare-settings#$list")."' target='_blank'>Click Here</a> to add ".esc_html(str_replace('_', ' ', strtolower($list))).".</p></div>";
		}
	}
	
}




/** =====================( Functions Details )======================
	
	* Usage example: educare_get_options_for_subject('Class 6', $Subject);
	# function for specific class subject
	
	
	# it's only return <option>...</option>. soo, when calling this function you have must add <select>...</select> (parent) tags before and after.
	
	# Example:
	
		echo '<select id="Subject" name="Subject" Subject="fields">';
			echo '<option value="0">Select Subject</option>';
			educare_get_options('Subject', $Subject)
		echo '</select>';

	* @since 1.2.4
	* @last-update 1.2.4
	
	* @param string $class				For specific class wise subject
	* @param string $value				Specific variable to make fields selected
	
	* @return string|html
	
==================( function for class wise subject )==================*/


function educare_get_options_for_subject($class, $value = null) {
	
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
	$results = $wpdb->get_results("SELECT * FROM $table WHERE list='Class'");
	
	if ($results) {
		foreach ( $results as $print ) {
			$data = $print->data;
			$data = json_decode($data, true);

			if (key_exists($class, $data)) {
				foreach ($data[$class] as $subject) {
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





/** =====================( Functions Details )======================
	
	* Usage example: educare_get_subject('class name', $id);
	# display specific class subject.
	
	* @since 1.2.0
	* @last-update 1.2.0
	
	* @param string $class	Select class for get subject
	* @param int $id				Select specific database rows by id
	
	* @return string
	
==================( function for get subject )==================*/

function educare_get_subject($class, $id) {
	?>
	<table class="grade_sheet">
		<thead>
			<tr>
				<th>No</th>
				<th>Subject</th>
				<th>Marks</th>
				<th>Grade</th>
			</tr>
		</thead>
		
		<tbody>
			<?php 
			if (isset($_POST['Add'])) {
				educare_get_options('Subject', 'add', $class);
			} else {
				educare_get_options('Subject', $id, $class);
			}
			?>
			
		</tbody>
		
	</table>
	
	<h4>Optional Subject</h4>
	
	<?php echo educare_guide_for('optinal_subject');?>
	
	<select id="optional_subject" class="fields">
		<?php 
		
		// echo '<option disabled selected>Select optional subject</option>';
		echo '<option>None</option>';
		
		if (isset($_POST['Add'])) {
			educare_get_options('optinal', 'add', $class);
		} else {
			educare_get_options('optinal', $id, $class);
		}
		
		?>
	</select>
	
	<input id="optional" type="text" hidden>
	<?php
}



/** =====================( Functions Details )======================
	
	* Usage example: educare_get_data_by_student($id, $data);

	* @since 1.2.0
	* @last-update 1.2.0
	
	* @param int $id			 			database row id
	* @param object $data				$data object
	
	* @return mixed
	
===================( function for getting student data )===================*/

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



/** =====================( Functions Details )======================
	
	* Usage example: educare_files_selector($type, $print)
	# Access WP gallery for upload/import students photos
	
	
	# educare_files_selector('add_results', '');
	# educare_files_selector('add_results', '$print'); for update selected photos

	* @since 1.0.0
	* @last-update 1.0.0
	
	* @param string $list		Getting file selector for Add/Update/Default
	* @param object $print	Get old data when update
	
	* @return null|HTML
	
======================( educare files selector )=====================*/

function educare_files_selector($type, $print) {
		
	if ( isset( $_POST['educare_default_photos'] ) && isset( $_POST['Photos'] ) ) :
	        update_option( 'educare_files_selector', absint( sanitize_text_field($_POST['Photos'] )) );
	endif;
	    
	wp_enqueue_media();
	$educare_save_attachment = get_option( 'educare_files_selector', 0 );
	
	$display = 'none';
	$default_set = "<input type='hidden' id='educare_attachment_default'>";
	$default_photos = wp_get_attachment_url( get_option( 'educare_files_selector' ) );
	
	if ($default_photos == null) {
		$default_img = EDUCARE_URL.'assets/img/default.svg';
    } else {
		$default_img = wp_get_attachment_url( get_option( 'educare_files_selector' ) );
	}
	
	if ($type == 'update') {
		$img = $print->Photos;
		
    $img_type = "Students Photos";
		$guide = "If you change students photos, Please upload or select  a custom photos from gallery that's you want!";
		$default_set = "<input type='button' id='educare_attachment_default' class='button' onClick='".esc_js('javascript:;')."' value='Use Default photos'>";

	} else {
		
		$img_type = "Default Photos";
		$guide = "Current students photos are default. Please upload or select  a custom photos from gallery that's you want!";

    	if ($default_photos == null) {
			$img = EDUCARE_URL.'assets/img/default.svg';
	    } else {
    		$img = wp_get_attachment_url( get_option( 'educare_files_selector' ) );
    	}

	}


	if (educare_check_status('photos') == 'unchecked') {
			$photos = 'disabled';
		} else {
			$photos = '';
		}
	?>
		
	<div id='educare_files_selector_disabled'>
		<div id='educare_files_uploader' class='educare_upload add'>
		<div class='educare_files_selector'>
	        <img id='educare_attachment_preview' class='educare_student_photos' src='<?php echo esc_url($img);?>'/>
			
	        <h3 id='educare_img_type' class='title'><?php echo esc_html($img_type);?></h3>
		</div>
		
		<p id='educare_guide'><?php echo esc_html($guide);?></p>
		<div id='educare_default_help'></div>
	    
	    <input type="hidden" name='Photos' id='educare_attachment_url' value='<?php echo esc_attr(esc_url($img));?>'>
		
		<input type='button' id='educare_attachment_title' class="button" value='Pleace Select a students photos' disabled>
	    
	    <input id="educare_upload_button" type="button" class="button" value="<?php _e( 'Upload Students Photos' ); ?>"/>
	
		<?php echo wp_kses_post($default_set);?>
		
		<input type='button' id='educare_attachment_clean' class='button educare_clean' value='&#xf158 Clean' style='display: <?php echo esc_attr($display);?>'>
	    
	    </div>
	
	</div>
	
	
	<script type='text/javascript'>
    jQuery( document ).ready( function( $ ) {
        // Uploading files
        var file_frame;
        var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
        var educare_media_post_id = <?php echo esc_attr($educare_save_attachment); ?>; // Set this
        jQuery('#educare_upload_button').on('click', function( event ){
            event.preventDefault();
            // If the media frame already exists, reopen it.
            if ( file_frame ) {
                // Set the post ID to what we want
                file_frame.uploader.uploader.param( 'post_id', educare_media_post_id );
                // Open frame
                file_frame.open();
                return;
            } else {
                // Set the wp.media post id so the uploader grabs the ID we want when initialised
                wp.media.model.settings.post.id = educare_media_post_id;
            }
            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: 'Select Students Photos',
                button: {
                    text: 'Use this image',
                },
                multiple: false // Set to true to allow multiple files to be selected
            });
            // When an image is selected, run a callback.
            file_frame.on( 'select', function() {
                // We set multiple to false so only get one image from the uploader
                attachment = file_frame.state().get('selection').first().toJSON();
                // Do something with attachment.id and/or attachment.url here
                // $( '#educare_attachment_preview' ).attr( 'src', attachment.url ).css( 'width', '100px' );
                $( '#educare_attachment_preview' ).attr( 'src', attachment.url );
                $( '#educare_upload_button' ).val( 'Edit Photos' );
                $( '#educare_attachment_clean' ).css( 'display', 'block' );
                $("#educare_img_type").html('Custom photos');
                $("#educare_guide").html('Please click edit button for change carently selected photos or click close/clean button for default photos');
                $( '#educare_attachment_id' ).val( attachment.id );
                $( '#educare_attachment_url' ).val( attachment.url );
                $( '#educare_attachment_title' ).val( attachment.title ).attr( 'value', this(val) );
                // Restore the main post ID
                wp.media.model.settings.post.id = wp_media_post_id;
            });
                // Finally, open the modal
                file_frame.open();
        });
        // Restore the main ID when the add media button is pressed
        jQuery( 'a.add_media' ).on( 'click', function() {
            wp.media.model.settings.post.id = wp_media_post_id;
        });

        // clean files/photos
        $("input.educare_clean").on("click", function() { 
            $("#educare_attachment_url").val("<?php echo esc_url($img);?>");
            $("#educare_attachment_id").val("");
            $( '#educare_attachment_preview' ).attr( 'src', '<?php echo esc_url($img);?>' );
            $("input.educare_clean").css('display', 'none');
            $( '#educare_attachment_title' ).val('Cleaned! please select onother one');
            $( '#educare_upload_button' ).val( 'Upload photos again' );
            $("#educare_img_type").html('<?php echo esc_html($img_type);?>');
            $("#educare_guide").html("<?php echo esc_html($guide);?>");
			$( '#educare_attachment_default' ).css( 'display', 'block' );
        });
		
		// set default photos
		$("#educare_attachment_default").on("click", function() { 
            $('#educare_attachment_url').val('<?php echo esc_url($default_img);?>');
			$( '#educare_attachment_preview' ).attr( 'src', '<?php echo esc_url($default_img);?>' );
			$( '#educare_attachment_clean' ).css( 'display', 'block' );
			$( this ).css( 'display', 'none' );
			$( '#educare_attachment_title' ).val('Successfully set default photos!');
        });
	
    });
    
    // disabled photos
		var photos = '<?php echo educare_esc_str($photos);?>';
		if (photos == 'disabled') {
			document.getElementById('educare_default_help').innerHTML = 'Currently students photos are disabled. If you upload or display student photos, first check/anable students photos under the settings sections';
			document.getElementById('educare_upload_button').setAttribute('disabled', 'disabled');
			document.getElementById('educare_attachment_default').setAttribute('disabled', 'disabled');
			document.getElementById('educare_files_selector_disabled').className = 'educare_files_selector_disabled';
			document.getElementById('educare_upload_button').setAttribute('disabled', 'disabled');
			document.getElementById('educare_default_photos').setAttribute('disabled', 'disabled');
			document.getElementById('educare_attachment_clean').style.display= 'none';
		}
	</script>

	<?php

}



/** =====================( Functions Details )======================
	
	* Usage example: educare_get_results_forms($print, 'add/update')
	# print students results forms for add/update/delete students results
	
	
	# it's only print forms field (Name, Class, Exam, Roll No, Regi No, Year...)
	# required educare_save_results() function for work properly
	# Actually, this function only for print forms under educare_save_results();

	* @since 1.0.0
	* @last-update 1.2.4
	
	* @param object $print				Getting object value
	* @param string $submit				Forms action type - Add/Update
	* @param bool $add_students		if forms for add students (since 1.2.4)
	
	* @return null|HTML
	
===================( function for print results forms )===================*/

function educare_get_results_forms($print, $submit, $add_students = null) {
	$Class = '';
	if (isset($_POST['edit']) or isset($_POST['edit_by_id'])) {
		
		if (!$add_students) {
			$Exam = sanitize_text_field($print->Exam);
		}

		$id = sanitize_text_field($print->id);
		$Class = sanitize_text_field($print->Class);
		$Year = sanitize_text_field($print->Year);
		$Name = sanitize_text_field($print->Name);
		$Roll_No = sanitize_text_field($print->Roll_No);
		$Regi_No = sanitize_text_field($print->Regi_No);
	} else {
		$id = $Name = $Roll_No = $Regi_No = $Class = $Exam = $Year = false;

		if (isset($_POST['Add'])) {
			if (key_exists('Class', $_POST)) {
				$Class = sanitize_text_field($_POST['Class']);
			}
			if (key_exists('Exam', $_POST)) {
				$Exam = sanitize_text_field($_POST['Exam']);
			}
			if (key_exists('Year', $_POST)) {
				$Year = sanitize_text_field($_POST['Year']);
			}

			$Name = sanitize_text_field($_POST['Name']);
			$Roll_No = sanitize_text_field($_POST['Roll_No']);
			$Regi_No = sanitize_text_field($_POST['Regi_No']);
		}
	}
	?>
	
	<form class="add_results" action="" method="post">
		<div class="content">
			
			<?php 
			if (isset($_POST['edit']) or isset($_POST['edit_by_id'])) {
				$photos = $print->Details;
				$photos = json_decode($photos);
				
				educare_files_selector('update', $photos);
				echo "<input type='hidden' id='id_no' name='id' value='".esc_attr($id)."'/>";
			} else {
				echo "<input type='hidden' id='id_no'>";
				educare_files_selector('add_results', '');
			}
			?> 
			<h2>Students Details</h2>
				
				<div class="select">
				<label for="Class" class="labels" id="class"></label>
				<label for="Exam" class="labels" id="exam"></label>
			</div>
			
			<?php 
				$chek_name = educare_check_status('Name', true);
				$chek_roll = educare_check_status('Roll_No', true);
				$chek_regi = educare_check_status('Regi_No', true);

				if ($chek_name) {
					echo '<p>'.esc_html($chek_name).':</p>
					<label for="Name" class="labels" id="name"></label>
					<input type="text" name="Name" value="'.esc_attr($Name).'" placeholder="Inter '.esc_html($chek_name).'">
					';
				} else {
					echo '<input type="hidden" name="Name" value="'.esc_attr($Name).'">';
				}

				if ($chek_roll) {
					echo '<p>'.esc_html($chek_roll).':</p>
					<label for="Roll_No" class="labels" id="roll_no"></label>
					<input type="number" name="Roll_No" value="'.esc_attr($Roll_No).'" placeholder="Inter '.esc_html($chek_roll).'">
					';
				} else {
					echo '<input type="hidden" name="Roll_No" value="Null">';
				}

				if ($chek_regi) {
					echo '<p>'.esc_html($chek_regi).':</p>
					<label for="Regi_No" class="labels" id="regi_no"></label>
					<input type="text" name="Regi_No" value="'.esc_attr($Regi_No).'" placeholder="Inter '.esc_html($chek_regi).'">
					';
				} else {
					echo '<input type="hidden" name="Regi_No" value="Null">';
				}
			?>
			
			<?php echo educare_guide_for('add_class');?>

			<div class="select">
				<select id="Class" name="Class" class="form-control">
					<?php educare_get_options('Class', $Class);?>
				</select>
			
			<?php 
			if ($add_students) {
				echo '<input type="hidden" name="Exam" value="none">';
			} else {
				?>
					<select id="Exam" name="Exam" class="fields">
						<?php educare_get_options('Exam', $Exam);?>
					</select>
				<?php
			}
			?>
			</div>
				
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

			if ($add_students) {
				educare_guide_for('Please selecet current year. its help you to find different year students.');
			}
			?>
			
			Select Year:<br>
			<select id="Year" name="Year" class="fields">
				<?php educare_get_options('Year', $Year);?>
			</select>
			<?php

			if (!$add_students) {
				?>
				<h2>Students Results</h2>
				
				<?php
				if (educare_check_status('auto_results') == 'checked') {
					$disabled = 'disabled';
					
					echo educare_guide_for('You can not modify (Result, GPA and Grade options. If You need to manually set this options, first disable <b>Auto Result</b> system frome educare (plugins) settings. Click here to <a href="/wp-admin/admin.php?page=educare-settings#settings" target="_blank">Disable Auto Results</a>');

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
					
					<input type="number" name="GPA" class="fields" value="<?php echo esc_attr(educare_value('GPA', $id));?>" placeholder="0.00" <?php echo esc_attr( $disabled );?>>
				</div>
				
				<?php echo educare_guide_for('add_subject');?>
				<div id="result_msg">
					<?php educare_get_subject($Class, $id) ?>
				</div>
				
				<script>
				$(document).on("change", "#Class", function() {
					$(this).attr('disabled', true);
					var class_name = $('#Class').val();
					var id_no = $('#id_no').val();
					$.ajax({
							url: "<?php echo esc_url(admin_url('admin-ajax.php'))?>",
							data: {
							action: 'educare_class',
							class: class_name,
							id: id_no,
						},
							type: 'POST',
							success: function(data) {
								$('#result_msg').html(data);
								$('#Class').attr('disabled', false);
							},
							error: function(data) {
								alert(data);
							},
					});
				});
				</script>

				<script>
				function myFunction() {
					var x = document.getElementById("optional_subject").value;
					var y = document.getElementById(x).value;
				
					document.getElementById("optional").value = "1 " + y;
					document.getElementById("optional").setAttribute("name", x);
				}
				</script>
				<?php
			}
			?>
			
			<br>
			<button type="submit" name="<?php echo esc_attr($submit);?>" class="educare_button" onClick="<?php echo esc_js('myFunction()');?>"><i class="dashicons dashicons-<?php if ($submit == 'Add') {echo 'plus-alt';}else{echo 'edit';}?>"></i> <?php echo esc_html($submit);?> Results</button>
					
			<?php
			// remove delete button when Add results
			if ($submit != 'Add') {
				?>
					<button type="submit" name="delete" class="educare_button" <?php educare_confirmation('Result', 'this result');?>><i class="dashicons dashicons-trash"></i>Delete</button>
				<?php
			}
			?>
			
		</div>
  </form>

	<?php
}



/** =====================( Functions Details )======================
	
	* Usage example: educare_get_search_forms();
	### Display forms for search students results
	
	
	* Search specific results for Edit/Delete/View
	* Search results by Class, Exam, Year, Roll & Regi No for Edit/Delete/View specific results.
	* Admin can Edit/Delete/View the results.
	* Users only view the results.

	* @since 1.0.0
	* @last-update 1.2.4

	* @param bool $add_students		if forms for students
	
	* @return null|HTML
	
===================( function for search specific results )===================*/

function educare_get_search_forms($add_students = null) {
	$Roll_No = $Regi_No = $Class = $Exam = $Year = false;

	if (isset($_POST['edit'])) {
		if (key_exists('Class', $_POST)) {
			$Class = sanitize_text_field($_POST['Class']);
		}
		if (key_exists('Exam', $_POST)) {
			$Exam = sanitize_text_field($_POST['Exam']);
		}
		if (key_exists('Year', $_POST)) {
			$Year = sanitize_text_field($_POST['Year']);
		}

		$Roll_No = sanitize_text_field($_POST['Roll_No']);
		$Regi_No = sanitize_text_field($_POST['Regi_No']);
	}
	
	?>
	<form class="add_results" action="" method="post" id="edit">
		<div class="content">
		<?php 
			$chek_roll = educare_check_status('Roll_No', true);
			$chek_regi = educare_check_status('Regi_No', true);
			$chek_class = educare_check_status('Class', true);
			$chek_exam = educare_check_status('Exam', true);

			if ($chek_roll) {
				echo '<p>'.esc_html($chek_roll).':</p>
				<label for="Roll_No" class="labels" id="roll_no"></label>
				<input type="number" name="Roll_No" value="'.esc_attr($Roll_No).'" placeholder="Inter '.esc_attr($chek_roll).'">
				';
			} else {
				echo '<input type="hidden" name="Roll_No" value="Null">';
			}

			if ($chek_regi) {
				echo '<p>'.esc_html($chek_regi).':</p>
				<label for="Regi_No" class="labels" id="regi_no"></label>
				<input type="text" name="Regi_No" value="'.esc_attr($Regi_No).'" placeholder="Inter '.esc_attr($chek_regi).'">
				';
			} else {
				echo '<input type="hidden" name="Regi_No" value="Null">';
			}

			echo '<div class="select">';
				if ($chek_class) {
					?>
					<div>
						<p><?php echo esc_html($chek_class);?>:</p>
						<select id="Class" name="Class" class="form-control">
							<?php educare_get_options('Class', $Class);?>
						</select>
					</div>
					<?php
				} else {
					echo '<input type="hidden" name="Class" value="Null">';
				}

				if ($add_students) {
					echo '<input type="hidden" name="Exam" value="none">';
				} else {
					if ($chek_exam) {
						?>
						<div>
							<p><?php echo esc_html($chek_exam);?>:</p>
							<select id="Exam" name="Exam" class="fields">
								<?php educare_get_options('Exam', $Exam);?>
							</select>
						</div>
						<?php
					} else {
						echo '<input type="hidden" name="Exam" value="Null">';
					}
				}
				?>

				<div>
					<p>Select Year:</p>
					<select id="Year" name="Year" class="fields">
						<?php educare_get_options('Year', $Year);?>
					</select>
				</div>
				
			</div>

			<button id="edit_btn" name="edit" type="submit" class="educare_button"><i class="dashicons dashicons-search"></i> Search for edit</button>

		</div>
	</form>
	<?php
}




/** =====================( Functions Details )======================
	
	* Usage example: educare_all_view();
	### Display data (students and results)

	* @since 1.0.0
	* @last-update 1.2.4

	* @param bool $add_students		if data for students
	* @param bool $on_load 				if (directly) show data when page is loaded
	
	* @return null|HTML
	
===================( function for search specific results )===================*/

function educare_all_view($students = null, $on_load = null) {
	global $wpdb;
	// Table name
	if ($students) {
		$tablename = $wpdb->prefix."educare_students";
		$msgs = 'students';
	} else {
		$tablename = $wpdb->prefix."educare_results";
		$msgs = 'results';
	}

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
						for ( $a = 5; $a < 55; $a+=5 ) {
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
			
			<script>
				function select_Table() {
					var x = document.getElementById("select_table").value;
					var term = document.getElementById("term");
					var term_label = document.getElementById("term_label");

					var select_class = '<?php educare_get_options('Class', $data);?>';
					var select_exam = '<?php educare_get_options('Exam', $data);?>';
					var sub_select_class = '<?php educare_get_options('Class', $sub_term);?>';
					var sub_select_exam = '<?php educare_get_options('Exam', $sub_term);?>';
					var all = '<option>All</options>';

					if (x == 'All') {
						select_data.disabled = 'disabled';
						term.disabled = 'disabled';
						term_label.innerHTML = 'All:';
					}

					if (x == 'Class') {
						select_data.disabled = '';
						term.disabled = '';
						select_data.innerHTML = select_class;
						term.innerHTML = all + sub_select_exam;
						term_label.innerHTML = 'Select Exam:';
					}

					if (x == 'Exam') {
						select_data.disabled = '';
						term.disabled = '';
						select_data.innerHTML = select_exam;
						term.innerHTML = all + sub_select_class;
						term_label.innerHTML = 'Select Class:';
					}

				}
				
				function select_Year() {
					var x = document.getElementById("year").value;
					var year = document.getElementById("select_year");
					
					if (x == 'All') {
						year.disabled = 'disabled';
					}
					if (x == 'Year') {
						year.disabled = '';
						year.innerHTML = '<?php educare_get_options('Year', $select_year);?>';
					}
				}
				
				// keep selected
				select_Table();
				select_Year();

			</script>

		</div>
	</form>

	<?php
		// Record List
		if (isset($_POST["educare_view_results"]) or isset($_POST['remove']) or isset($_POST['remove_result']) or isset($_POST['on_load'])) {
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
					if ($students) {
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

				if (!empty($select_year)) {
					if ($table == 'All' or empty($data)) {
						// echo 'year';
						$search = $wpdb->get_results("SELECT * FROM ".$tablename." WHERE Year='$select_year' ORDER BY $time $order");
					} else {
						// echo 'turm';
						if ($sub_term != 'All') {
							$search = $wpdb->get_results("SELECT * FROM ".$tablename." WHERE $table='$data' AND $sub='$sub_term' AND Year='$select_year' ORDER BY $time $order");
						} else  {
							$search = $wpdb->get_results("SELECT * FROM ".$tablename." WHERE $table='$data' AND Year='$select_year' ORDER BY $time $order");
						}
					}
				} else {
					if ($table == 'All' or empty($data)) {
						// echo 'time';
						$search = $wpdb->get_results("SELECT * FROM ".$tablename." ORDER BY $time $order");
					} else {
						// echo 'turm'; Class and Exan/Exam or Class
						if ($sub_term != 'All') {
							// echo $sub_term;
							$search = $wpdb->get_results("SELECT * FROM ".$tablename." WHERE $table='$data' AND $sub='$sub_term' ORDER BY $time $order");
						} else {
							$search = $wpdb->get_results("SELECT * FROM ".$tablename." WHERE $table='$data' ORDER BY $time $order");
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
									if ($students) {
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
								$link .= 'admin.php?page=educare-';

								if ($students) {
									$remove_link = $link.'all-students';
									$profiles = $remove_link.'&profiles';
									$link .= 'all-students&update-students';
								} else {
									$remove_link = $link.'view-results';
									$profiles = '/'.educare_check_status("results_page");
									$link .= 'update-results';
								}

								?>

								<td>
									<input name="id" value="<?php echo esc_attr($id);?>" hidden>
									
									<div class="action_menu">
										<input type="submit" class="button action_button" value="&#xf349">
										<menu class="action_link">
											<form class="educare-modify" action="<?php echo esc_url($profiles);?>" method="post" id="educare_results" target="_blank">
												<input name="id" value="<?php echo esc_attr($id);?>" hidden>
												
												<input class="button" type="submit" <?php echo esc_attr($results_button);?>" name="educare_results_by_id" value="<?php echo wp_check_invalid_utf8($results_value);?>" title="<?php echo esc_attr( ucfirst($results_title) );?>">
											</form>
											
											<form class="educare-modify" action="<?php echo esc_url($link); ?>" method="post" id="educare_results_by_id" target="_blank">
												<input name="id" value="<?php echo esc_attr($id); ?>" hidden>
												<input class="button" type="submit" name="edit_by_id" value="&#xf464" title="Edit <?php echo esc_attr( ucfirst($msgs) );?>">
											</form>

											<form class="educare-modify" action="<?php echo esc_url($remove_link); ?>" method="post">
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
												
												<input class="button error" type="submit" name="remove_result" value="&#xf182" title="Remove <?php echo esc_attr( ucfirst($msgs) );?>">
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
				<input type='hidden' name='id' value='<?php echo esc_attr($id);?>'>
				<input type='hidden' name='table' value='<?php echo esc_attr($table);?>'>
				<input type='hidden' name='data' value='<?php echo esc_attr($data);?>'>
				<input type='hidden' name='sub_term' value='<?php echo esc_attr($sub_term);?>'>
				<input type='hidden' name='select_year' value='<?php echo esc_attr($select_year);?>'>
				<input type='hidden' name='year' value='<?php echo esc_attr($year);?>'>
				<input type='hidden' name='order' value='<?php echo esc_attr($order);?>'>
				<input type='hidden' name='time' value='<?php echo esc_attr($time);?>'>
				<input type='hidden' name='results_per_page' value='<?php echo esc_attr($results_per_page);?>'>
				
				<input type="submit" name="remove" class="educare_button" value="Delete <?php echo esc_attr( ucfirst($msgs) );?>" <?php educare_confirmation('remove_results', $data, $select_year); echo esc_attr($status);?>>
			</form>
			<?php

		}
		?>
		<script>
			$(document).on("click", ".action_button", function() {
				// alert('Atik');
				$(this).parent('div').find('menu').toggle();
			});

			let options = {
				// How many content per page
				numberPerPage:<?php echo esc_attr($results_per_page);?>,
				// anable or disable go button
				goBar:true,
				// count page based on numberPerPage
				pageCounter:true,
			};

			let filterOptions = {
				// filter or search specific content
				el:'#searchBox'
			};

			paginate.init('.view_results',options,filterOptions);
		</script>
	<?php
}



/** =====================( Functions Details )======================
	
	* Usage example: educare_array_slice($class, 'b', 'd');

	$class = array(
		'a' => 'aa',
		'b' => 'bb',
		'c' => 'cc',
		'd' => 'dd',
		'e' => 'ee',
	);

	* Example:
	$new_array = educare_array_slice($class, 'b', 'd');
	echo '<pre>';	
	print_r($new_array);	
	echo '</pre>';

	* @since 1.2.0
	* @last-update 1.2.0

	* @param array 			$array where to slice
	* @param str 				$offset slice start
	* @param str 				$length slice end
	
	* @return new array()
	
===================( function for slice array data )===================*/

function educare_array_slice($array, $offset, $length = null) {
  $offset = array_search($offset, array_keys($array));
  $slice_array = array_slice($array, $offset);

  $length = array_search($length, array_keys($slice_array));
  $length = $length - 1;

  $slice_array = array_slice($slice_array, 1, $length);

  return $slice_array;
}



/** =====================( Functions Details )======================
	
	* Usage example: educare_save_results();
	# Processing students results forms
	
	
	# Add/Edit/Delete results forms processor
	# Main function for modify (Add, Edit, Delete) students results

	* @since 1.0.0
	* @last-update 1.2.4

	* @param bool $add_students		if save data for students
	
	* @return mixed
	
===================( function for Saving forms data )===================*/

function educare_save_results($add_students = null) {
	
	// print error/success notice
	function notice($x, $print = null, $add_students = null) {
		$Class = $Exam = $Year = false;

		if (key_exists('Class', $_POST)) {
			$Class = sanitize_text_field($_POST['Class']);
		}
		if (key_exists('Exam', $_POST)) {
			$Exam = sanitize_text_field($_POST['Exam']);
		}
		if (key_exists('Year', $_POST)) {
			$Year = sanitize_text_field($_POST['Year']);
		}

		$Roll_No = sanitize_text_field($_POST['Roll_No']);
		$Regi_No = sanitize_text_field($_POST['Regi_No']);
		$id = '';
		
		ob_start();
		educare_confirmation('Result', 'this result');
		$confirm = ob_get_clean();
		$chek_name = educare_check_status('Name', true);
		$chek_roll = educare_check_status('Roll_No', true);
		$chek_regi = educare_check_status('Regi_No', true);
		
		if ($x == 'updated') {
			$id = sanitize_text_field($_POST['id']);
		}
		if ($x == 'exist' or $x == 'added') {
			$id = $print->id;
		}

		// if ($add_students) {
		// 	$link = 'all-students&update-students';
		// } else {
		// 	$link = 'update-results';
		// }

		$link = admin_url();
		$link .= 'admin.php?page=educare-';

		if ($add_students) {
			$remove_link = $link.'all-students';
			$profiles = $remove_link.'&profiles';
			$link .= 'all-students&update-students';
		} else {
			$remove_link = $link.'view-results';
			$profiles = '/'.educare_check_status("results_page");
			$link .= 'update-results';
		}
			
		$forms = "<form method='post' action='".esc_url($profiles)."' class='text_button' target='_blank'>
			<input name='id' value='".esc_attr($id)."' hidden>
			<input type='submit' name='educare_results_by_id' class='educare_button' value='&#xf177'>
		</form>
		
		<form method='post' action='".esc_url($link)."' class='text_button'>
			<input name='id' value='".esc_attr($id)."' hidden>
			<input type='submit' name='edit_by_id' class='educare_button' value='&#xf464'>
		</form>

		<form method='post' action='".esc_url($_SERVER['REQUEST_URI'])."' class='text_button'>
			<input name='id' value='".esc_attr($id)."' hidden>
			<input type='submit' name='delete' class='educare_button' value='&#xf182' ".esc_attr($confirm).">
		</form>";
		
		if ($x == 'added' or $x == 'updated') {
			$Name = sanitize_text_field($_POST['Name']);
			if ($chek_roll) {
				$roll = "<br>$chek_roll: <b>$Roll_No</b>";
			} else {
				$roll = '';
			}

			if ($chek_regi) {
				$regi = "<br>$chek_regi: <b>$Regi_No</b>";
			} else {
				$regi = '';
			}

			echo "<div class='notice notice-success is-dismissible'><p>";

			if ($add_students) {
				echo "Successfully ".esc_html($x)." <b>".esc_html($Name)."</b> as a students.<br>
				Class: <b>".esc_html($Class)."</b>".wp_kses_post($roll)."".wp_kses_post($regi)."
				$forms";
			} else {
				echo "Successfully ".esc_html($x)." <b>".esc_html($Name)."</b> Result for his <b>".esc_html($Exam)."</b><br>
				Class: <b>".esc_html($Class)."</b>".wp_kses_post($roll)."".wp_kses_post($regi)."
				$forms";
			}

			echo "</p></div>";
			
		}
		
		if ($x == 'empty') {
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
			
			if ($chek_roll) {
				// notify if empty Roll No
				if (empty($Roll_No) ) {
					echo '<b>Roll No</b>, ';
				}
			}

			if ($chek_regi) {
				// notify if empty Reg No
				if (empty($Regi_No) ) {
					echo '<b>Reg No</b>, ';
				}
			}
			
			// notify if empty Year
			if (empty($Year) ) {
        echo '<b>Year</b>, ';
			}
			
			echo 'Please fill all required (<i>Name, Roll No, Regi No, Class, Exam</i>) fields carefully. thanks.</p></div>';
		}
		
		if ($x == 'exist') {
			// $Name = sanitize_text_field($_POST['Name']);
			$Name = $print->Name;
			$Roll_No = sanitize_text_field($_POST['Roll_No']);
			$Regi_No = sanitize_text_field($_POST['Regi_No']);

			if ($add_students) {
				echo "<div class='notice notice-error is-dismissible'><p>Sorry, Student is allready exist. You are trying to add duplicate students. It's not possible to add duplicate students. Because, ".esc_html($Name)."'s ".esc_html($Class)." student is already added in this Roll (".esc_html($Roll_No).") & Regi No (".esc_html($Regi_No)."). Please update or delete old student then add a new one.</p>$forms</div>";
			} else {
				echo "<div class='notice notice-error is-dismissible'><p>Sorry, Results is allready exist. You are trying to add duplicate results. It's not possible to add duplicate results. Because, ".esc_html($Name)."'s ".esc_html($Exam)." Result is already added in this Roll (".esc_html($Roll_No).") & Regi No (".esc_html($Regi_No)."). Please update or delete old results then add a new one.</p>$forms</div>";
			}

		}
		
		if ($x == 'not_found') {
			echo "<div class='notice notice-error is-dismissible'><p>Sorry, results not found. Please try again</p></div>";
		}
	}
	
	// insert logic
	global $wpdb;
	if ($add_students) {
		$table_name = $wpdb->prefix . 'educare_students';
	} else {
		$table_name = $wpdb->prefix . 'educare_results';
	}
	
	
	$chek_roll = educare_check_status('Roll_No', true);
	$chek_regi = educare_check_status('Regi_No', true);
	$chek_class = educare_check_status('Class', true);
	$chek_exam = educare_check_status('Exam', true);

	if ( isset($_POST['Add']) or isset($_POST['edit']) or isset($_POST['edit_by_id']) or isset($_POST['update']) ) {
		
		if ( isset($_POST['edit_by_id']) or isset($_POST['update']) ) {
			$id = sanitize_text_field($_POST['id']);
	    } else {
	    	$id = '';
	    }
	   // Search Results by only [id]
	   if (isset($_POST['edit_by_id'])) {
	        $results = $wpdb->get_results("SELECT * FROM $table_name WHERE id='$id'");
		} else {
			$Class = $Exam = $Year = false;

			if (key_exists('Class', $_POST)) {
				$Class = sanitize_text_field($_POST['Class']);
			}
			if (key_exists('Exam', $_POST)) {
				$Exam = sanitize_text_field($_POST['Exam']);
			}
			if (key_exists('Year', $_POST)) {
				$Year = sanitize_text_field($_POST['Year']);
			}
			
			$Roll_No = sanitize_text_field($_POST['Roll_No']);
			$Regi_No = sanitize_text_field($_POST['Regi_No']);

			if ($chek_roll) {
				$roll =  " AND Roll_No='$Roll_No'";
			} else {
				$roll = '';
			}
			if ($chek_regi) {
				$regi = " AND Regi_No='$Regi_No'";
			} else {
				$regi = '';
			}
			if ($chek_class) {
				$class = " AND Class='$Class'";
			} else {
				$class = '';
			}
			if ($chek_exam) {
				$exam = " AND Exam='$Exam'";
			} else {
				$exam = '';
			}
			
			if ($add_students) {
				$select = "SELECT * FROM $table_name WHERE Year='$Year' $class $roll $regi";
			} else {
				$select = "SELECT * FROM $table_name WHERE Year='$Year' $class $exam $roll $regi";
			}
			
			$results = $wpdb->get_results($select);
		}
		
		if ($results) {
			foreach($results as $print) {
				if (isset($_POST['Add'])) {
					if ($add_students) {
						notice('exist', $print, true);
					} else {
						notice('exist', $print);
					}
				}
				
				// if results exist display update forms when call edit/edit_by_id
				if (isset($_POST['edit']) or isset($_POST['edit_by_id'])) {
					if ($add_students) {
						educare_get_results_forms($print, 'update', true);
					} else {
						educare_get_results_forms($print, 'update');
					}
		    }
			}
	
		} else { //if ($results)
			
			if(!empty($Class) && !empty($Exam) && !empty($Roll_No) && !empty($Regi_No) && !empty($Year) ) {
				
				if (isset($_POST['edit']) or isset($_POST['edit_by_id'])) {
					notice('not_found');
					if ($add_students) {
						educare_get_search_forms(true);
					} else {
						educare_get_search_forms();
					}
				}
				
				if (isset($_POST['Add'])) {
					$Name = sanitize_text_field($_POST['Name']);
					$Photos = sanitize_text_field($_POST['Photos']);
					
					$Details = educare_array_slice($_POST, 'Exam', 'Year');
					$Details['Photos'] = $Photos;
					$Details = json_encode($Details);

					if ($add_students) {
						$data = array (
							'Name' => $Name,
							'Roll_No' => $Roll_No,
							'Regi_No' => $Regi_No,
							'Class' => $Class,
							'Year' => $Year,
							'Details' => $Details
						);
					} else {
						$Result = sanitize_text_field($_POST['Result']);
						$GPA = sanitize_text_field($_POST['GPA']);

						$Subject = educare_array_slice($_POST, 'GPA', 'Add');
						$Subject = json_encode($Subject);
						
						$data = array (
							'Name' => $Name,
							'Roll_No' => $Roll_No,
							'Regi_No' => $Regi_No,
							'Class' => $Class,
							'Exam' => $Exam,
							'Year' => $Year,
							'Details' => $Details,
							'Subject' => $Subject,
							'Result' => $Result,
							'GPA' => $GPA
						);
					}

					// echo '<pre>';	
					// print_r($data);	
					// echo '</pre>';
					
					$wpdb->insert($table_name, $data);
				    
					if($wpdb->insert_id > 0) {
						$results = $wpdb->get_results($select);
						
						if (isset($_POST['Add'])) {
							foreach($results as $print) {
								if ($add_students) {
									notice('added', $print, true);
								} else {
									notice('added', $print);
								}
							}
						}
					}
				}
			} else { //empty check
				if (isset($_POST['Add'])) {
					notice('empty');
				} else {
					notice('empty');
				}
				
				if (isset($_POST['edit']) or isset($_POST['edit_by_id'])) {
					if ($add_students) {
						educare_get_search_forms(true);
					} else {
						educare_get_search_forms();
					}
				}
			}
		}
	}
	
	// delete results
	if (isset($_POST['delete'])) {
		$id = sanitize_text_field($_POST['id']);

		$select = "SELECT * FROM $table_name WHERE id='$id'";
		$results = $wpdb->get_results($select);
		// check if results already deleted or not
		if ($results) {
			$wpdb->query("DELETE FROM $table_name WHERE id = $id");
			echo '<div class="notice notice-success is-dismissible"><p>Succesfully Delete Results.</p></div>';
		} else {
			echo '<div class="notice notice-error is-dismissible"><p>Results not fount for delete. Maybe, You are allredy delete this result!</p></div>';
		}
	}
	
	// update results
	if (isset($_POST['update'])) {
		if ($add_students) {
			$Exam = 'none';
		}
		
		if(!empty($Class) && !empty($Exam) && !empty($Roll_No) && !empty($Regi_No) && !empty($Year) ) {

			$Name = sanitize_text_field($_POST['Name']);
			$Photos = sanitize_text_field($_POST['Photos']);
			
			$Details = educare_array_slice($_POST, 'Exam', 'Year');
			$Details['Photos'] = $Photos;
			$Details = json_encode($Details);

			if ($add_students) {
				// Compare new to old results
				$class = educare_value('Class', $id, '', true);
				$roll_no = educare_value('Roll_No', $id, '', true);
				$regi_no = educare_value('Regi_No', $id, '', true);
				$year = educare_value('Year', $id, '', true);
				$Exam = $exam = 'none';

				$data = array (
					'Name' => $Name,
					'Roll_No' => $Roll_No,
					'Regi_No' => $Regi_No,
					'Class' => $Class,
					'Year' => $Year,
					'Details' => $Details
				);
			} else {
				// Compare new to old results
				$class = educare_value('Class', $id);
				$roll_no = educare_value('Roll_No', $id);
				$regi_no = educare_value('Regi_No', $id);
				$exam = educare_value('Exam', $id);
				$year = educare_value('Year', $id);
				
				$Result = sanitize_text_field($_POST['Result']);
				$GPA = sanitize_text_field($_POST['GPA']);

				$Subject = educare_array_slice($_POST, 'GPA', 'Add');
				$Subject = json_encode($Subject);
				
				$data = array (
					'Name' => $Name,
					'Roll_No' => $Roll_No,
					'Regi_No' => $Regi_No,
					'Class' => $Class,
					'Exam' => $Exam,
					'Year' => $Year,
					'Details' => $Details,
					'Subject' => $Subject,
					'Result' => $Result,
					'GPA' => $GPA
				);
			}
			
			// echo '<pre>';	
			// print_r($data);	
			// echo '</pre>';
				
			/** 
			1. $table_name = table
			2. $data = data
			3. $id = where
			
							1,		2,	3
			update(table, data, id)
			
			Pro tips: you can also use array -
			
			$wpdb->update(
	      $table_name, // 1. table
				array( // 2. data
					'Name' => sanitize_text_field($_POST['Name']),
					'Regi_No' => sanitize_text_field($_POST['Regi_No']),
					'Roll_No' => sanitize_text_field($_POST['Roll_No']),
					'Exam' => sanitize_text_field($_POST['Exam']),
					'Class' => sanitize_text_field($_POST['Class'])
			  ),
			
	      array( // 3. where
					'ID' => sanitize_text_field($_POST["id"])
				)
			);
			
			*/
			
			// for manually
			/** 
			$data = array (
				'Name' => sanitize_text_field($_POST['Name']),
				'Regi_No' => sanitize_text_field($_POST['Regi_No']),
				'Roll_No' => sanitize_text_field($_POST['Roll_No']),
				'Exam' => sanitize_text_field($_POST['Exam']),
				'Class' => sanitize_text_field($_POST['Class'])
			)
			*/
			
			// $wpdb->update($table_name, $data, array('ID' => $id));
			
			if ($class == $Class and $exam == $Exam and $roll_no == $Roll_No and $regi_no == $Regi_No and $year == $Year ) {
				
				$wpdb->update($table_name, $data, array('ID' => $id));
				if ($add_students) {
					notice('updated', '', true);
				} else {
					notice('updated');
				}
	
			} else {
				if (!$results) {
					$wpdb->update($table_name, $data, array('ID' => $id));
					if ($add_students) {
						notice('updated', '', true);
					} else {
						notice('updated');
					}
				} else {
					
					foreach($results as $print) {
						if ($add_students) {
							notice('exist', $print, true);
						} else {
							notice('exist', $print);
						}
					}
					
				}
			}
		} else {
			notice('empty');
		}
	}
}



/** =====================( Functions Details )======================
	
	* Usage example: educare_demo_data('Extra_field');
	# For import demo or specific field data

	* @since 1.2.0
	* @last-update 1.2.0

	* @param string $list 	for specific data (class, exam, year, extra fields)
	
	* @return mixed
	
===================( function for demo data )===================*/

function educare_demo_data($list) {
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
	
	$search = $wpdb->get_results("SELECT * FROM $table WHERE list='$list'");
	$data = '';

	foreach ( $search as $print ) {
		$data = $print->data;
	}

	$data = json_decode($data);
	return $data;
}



/** =====================( Functions Details )======================
	
	* Usage example: educare_replace_key_n_val($arr, $oldkey, $newkey);
	# For replace old key to new key. Also, change the value

	* @since 1.2.0
	* @last-update 1.2.0

	* @param array $arr   	where to replace key/value
	* @param str $oldkey  	old key to replace key/value
	* @param str $newkey 	 	replace key/value to new key
	* @param mixed $value 	replace specific key value

	* @return arr
	
===================( function for replace key and value )===================*/

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



/** =====================( Functions Details )======================
	
	* Usage example: educare_remove_value($value, $array);
	# remove specific value from array

	* @since 1.2.0
	* @last-update 1.2.0

	* @param mixed $val 	remove specific value
	* @param array $arr   from array

	* @return arr
	
===================( function for value from array )===================*/

function educare_remove_value($val, $arr) {
	
	if (($key = array_search($val, $arr)) !== false) {
		unset($arr[$key]);
	}

	return array_values($arr);
}




/** ====================( Functions Details )======================
	
	### Replace Specific Array Key
	* Usage example: $educare_replace_key = replace_key($array, 'b', 'e');
	
	* @since 1.0.0
	* @last-update 1.0.0
	
	* @param array $array						Where to replace key
	* @param string|int $old_key 		key to replace
	* @param string|int $new_key 		peplace old key to new key

	* @return array
	
=================( function for Replace Key  )================ **/

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


/** =====================( Functions Details )======================
	
	# Get/show specific class subject wehen user select any subject

	* @since 1.2.0
	* @last-update 1.2.0

	* @return mised/HTML
	
===================( function for show Subject )===================*/

add_action('wp_ajax_educare_class', 'educare_class');

function educare_class() {
	$class = sanitize_text_field($_POST['class']);
	$id = sanitize_text_field($_POST['id']);
	educare_get_subject($class, $id);
	die;
}



/** =====================( Functions Details )======================
	
	# Create demo files (import_demo.csv) for specific class

	* @since 1.2.0
	* @last-update 1.2.2

	* @return mised/create a files
	
=====================( function for import demo )===================*/

add_action('wp_ajax_educare_demo', 'educare_demo');

function educare_demo() {
	$Class = educare_demo_data('Class');

	// If we can not check exam, php will show an error msg. Because, array_rand(): Argument #1 ($array) cannot be empty
	if(empty(educare_demo_data('Exam'))) {
    $Exam = 'Exam Name';
  } else {
    $Exam = array_rand(educare_demo_data('Exam'), 1);
    $Exam = educare_demo_data('Exam')[$Exam];
  }

	// $Year = educare_demo_data('Year');
	$Year = date("Y");
	$Extra_field = educare_demo_data('Extra_field');

	$selected_class = sanitize_text_field($_POST['class']);
	$Subject = $Class->$selected_class;

	if (isset($_POST['students'])) {
		// Save data as a file (import_demo.csv)
		$download_files = "assets/files/import_demo_students.csv";
		$search = $Class;
	} else {
		// Save data as a file (import_demo.csv)
		$download_files = "assets/files/import_demo_results.csv";
		$search = $Subject;
	}

	$files_name = EDUCARE_DIR.$download_files;

	if ($search) {
		$Name = 'Student name';
		$Roll_No = rand(10000, 90000);
		$Regi_No = rand(10000000, 90000000);
		$GPA = rand(2, 5);
		$Photos = 'URL';

		$data = array (
			'Name' => $Name,
			'Roll_No' => $Roll_No,
			'Regi_No' => $Regi_No,
			'Class' => $selected_class,
			'Exam' => $Exam,
			'Year' => $Year,
			// 'Details' => $Details,
			// 'Subject' => $Subject,
			// 'Result' => $Result,
			// 'GPA' => $GPA
		);

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

		if (isset($_POST['students'])) {
			unset($data['Exam']);
		} else {
			$chek_roll = educare_check_status('Roll_No', true);
			$chek_regi = educare_check_status('Regi_No', true);
			$chek_name = educare_check_status('Name', true);

			if ($chek_roll) {
				$data = educare_replace_key_n_val($data, 'Roll_No', $chek_roll);
			} else {
				unset($data['Roll_No']);
			}

			if ($chek_regi) {
				$data = educare_replace_key_n_val($data, 'Regi_No', $chek_regi);
			} else {
				unset($data['Regi_No']);
			}

			if ($chek_name) {
				$data = educare_replace_key_n_val($data, 'Name', $chek_name);
			} else {
				unset($data['Name']);
			}

			// add more

			$data['Result'] = 'Passed';
			$data['GPA'] = number_format((float)$GPA, 1, '.', '');

			foreach ($Subject as $value) {
				// remove field type
				$data[$value] = rand(33, 99);
			}
		}
		
		$data['Photos'] = $Photos;
		
		ob_start();
		foreach ($data as $key => $value) {
			echo esc_html("$key,");
		}
		$head = substr(ob_get_clean(),0,-1);

		ob_start();
		foreach ($data as $value) {
			echo esc_html("$value,");
		}
		$content = substr(ob_get_clean(),0,-1);

		$data = "$head\n$content";
		
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

		$anable_copy = '';
		
		if(educare_check_status('copy_demo') == 'checked') {
			echo '<pre><textarea style="width: 100%; height: 100px;">';
			print_r($data);
			echo '</textarea></pre>';
		} else {
			$anable_copy = 'anable <strong>Copy Demo Data</strong> from educare settings or';
		}

		echo "<p><strong>Notes:</strong> This is an example of importing a demo.csv file, based on your current settings (Class, Subject, Additional fields...). If you make any changes to educare (plugin) settings, this demo file may not work. For this you need to create this file again! And if you get error or face any problem while downloading the file &#9785;, you can ".wp_kses_post( $anable_copy )." manually get this file in dir: <p>".esc_html( $file_dir )."</p><br>";

		echo "<p><a class='educare_button' href='".esc_url(EDUCARE_URL.$download_files)."' title='Download Import Demo'><i class='dashicons dashicons-download'></i> Download Demo</a></p>";
	} else {
		$file_dir = $files_name;
		
		$update_data = fopen($file_dir, 'w'); 
		fwrite($update_data, '');
		fclose($update_data);

		$url = esc_url('/wp-admin/admin.php?page=educare-settings');

		if (!$selected_class) {
			echo "<br><div class='notice notice-error is-dismissible'><p>Pleace select a valid class</p></div>";
		} else {
			echo "<br><div class='notice notice-error is-dismissible'><p>Currently, you don't have added any subject in this class (<b>".esc_html( $selected_class )."</b>). Please add some subject by <a href='".esc_url( $url.'#'.$selected_class )."' target='_blank'>Click Here</a>. Thanks </p></div>";
		}

		echo "<br><p><a class='educare_button disabled' title='Download Import Demo.csv Error'><i class='dashicons dashicons-download'></i> Download Demo</a></p>";
	}

	die;
}



/** =====================( Functions Details )======================
	
	# Modify or update grading systems

	* @since 1.2.0
	* @last-update 1.2.0

	* @return proceess data
	
===================( function for grading systems )===================*/

add_action('wp_ajax_educare_proccess_grade_system', 'educare_proccess_grade_system');

function educare_proccess_grade_system() {
	$rules = sanitize_text_field($_POST['class']);

	function educare_add_grade_system($rules = null, $point = null, $grade = null) {
    // get first rules (less den) number to compare
    $rules1 = strtok($rules, '-');
    // get second rules (greater den) number to compare
    $rules2 =substr(strstr($rules, '-'), 1);

		if (!$rules1) {
			$rules1 = 0;
		}
		if (!$rules2) {
			$rules2 = 0;
		}
		if (!$point) {
			$point = 0;
		}
    ?>
		<tr class="cloneField">
			<td><input type="number" name="rules1[]" value="<?php echo esc_attr($rules1)?>" placeholder="<?php echo esc_attr($rules1)?>"/></td>
			<td><input type="number" name="rules2[]" value="<?php echo esc_attr($rules2)?>" placeholder="<?php echo esc_attr($rules2)?>"/></td>
			<td><input type="number" name="point[]" value="<?php echo esc_attr($point)?>" placeholder="<?php echo esc_attr($point)?>"/></td>
			<td><input class="bold" type="text" name="grade[]" value="<?php echo esc_attr($grade)?>" placeholder="<?php echo esc_attr($grade)?>"/></td>
			<td><a href="<?php echo esc_js( 'javascript:void(0);' );?>" class="remove_button"><i class="dashicons dashicons-no"></i></a></td>
		</tr>
    <?php
  }

	/** 
	$grade_system = array(
    'current' => 'Default',
    'rules' => [
      'Default' => [
        '80-100' => 'A+',
        '70-79'  => 'A',
        '60-69'  => 'A-',
        '50-59'  => 'B',
        '40-49'  => 'C',
        '33-39'  => 'D',
        '0-32'  => 'F'
      ]
    ]
  );
	*/

	$grade_system = educare_check_status('grade_system');
	$grade_system = $grade_system->rules->$rules;

  ?>
	<div class="notice notice-success is-dismissible"><p>
		<form id='addForm' action="" method="post">
			<div class='fixbd_cloneField'>
				<h2>Edit Rules</h2>
				<p id='status' class='warning sticky'></p>
				
				Rules Name: <br>
				<input type="text" name="rules" value="<?php echo esc_attr($rules)?>" placeholder=""/ disabled>
				<input type="hidden" name="rules" value="<?php echo esc_attr($rules)?>">
				
				<table id='cloneBody'>
					<thead>
						<tr>
							<th>Less Mark</th>
							<th>Greater Mark</th>
							<th>Grade point</th>
							<th>Letter grade</th>
							<th>Close</th>
						</tr>
					</thead>
					<tbody id='cloneBody'>
						<?php
						// $count1 = $count2 = 0;
						foreach ( $grade_system as $rules => $grade ) {
							educare_add_grade_system($rules, $grade[0], $grade[1]);
						}
						?>
					</tbody>
				</table>
				
				<div class="button-container">
				<a href='<?php echo esc_js( 'javascript:void(0);' );?>' class='addButton educare_button' title='Add more field'><i class='dashicons dashicons-plus-alt'></i></a>
				<button id='save_addForm' class="educare_button" name="update_grade_rules"><i class='dashicons dashicons-yes'></i></button>
				</div>
				
			</div>
		</form>
		
		
		<div id='cloneWrapper' style='display: none;'>
			<?php educare_add_grade_system();?>
		</div>
	</p><button class="notice-dismiss"></button></div>

  <script type="text/javascript"><?php echo esc_js( 'cloneField()' );?></script>
	<?php

	die;
}




/** =====================( Functions Details )======================
  
	### Educare Import Results

  * @since 1.0.0
	* @last-update 1.2.3

  * @return void
	
===================( function for import results )=================== **/

function educare_import_result() {
	// Begin import results function
	global $wpdb;

	// Table name, where to import the results
	$table = $wpdb->prefix."educare_results";

	// Import CSV
	if(isset($_POST['educare_import_results'])) {

		// File extension
		$extension = pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION);

		// If file extension is 'csv'
		if(!empty($_FILES['import_file']['name']) && $extension == 'csv') {

			$totalInserted = 0;
			$total = 0;
			$exist = 0;
			$error = 0;
		
			// Open file in read mode
			$csvFile = fopen($_FILES['import_file']['tmp_name'], 'r');
			$keys = fopen($_FILES['import_file']['tmp_name'], 'r');
			$keys = fgetcsv($keys);
			$keys = array_map("utf8_encode", $keys);

			$data = array (
				'Name',
				'Roll_No',
				'Regi_No',
				'Class',
				'Exam',
				'Year',
				'GPA',
				'Result',
				'Photos'
			);

			$chek_roll = educare_check_status('Roll_No', true);
			$chek_regi = educare_check_status('Regi_No', true);
			$chek_name = educare_check_status('Name', true);

			if (!$chek_roll) {
				$data = educare_remove_value('Roll_No', $data);
			}
			if (!$chek_regi) {
				$data = educare_remove_value('Regi_No', $data);
			}
			if (!$chek_name) {
				$data = educare_remove_value('Name', $data);
			}

			$Extra_field = educare_demo_data('Extra_field');
			$Class = educare_demo_data('Class');
			$selected_class = sanitize_text_field($_POST['Class']);
			$Subject = $Class->$selected_class;

			$value_len = count($data) + count($Extra_field) + count($Subject);
			// Skipping header row
			fgetcsv($csvFile);
		
			// Read file
			while(($csvData = fgetcsv($csvFile)) !== FALSE) {
				$csvData = array_map("utf8_encode", $csvData);

				// echo '<pre>';	
				// print_r($csvData);	
				// echo '</pre>';
				
				$total ++;
				
				// CSV row column length (based on import files)
				$dataLen = count($csvData);
				// $table row column length (based on the users settings)
				$content_len = $value_len;
				
				/* =====( Explain )=====
				* Example for default Class 6 -> subject = 3, extra field = 7 and default requred field = 9 (Name, Roll No, Regi No, Class, Exam, Year, Passed, GPA, Photos)
				# So, default $table row length is (3+7+9) = 19.
				# Notes: 19 only for example. Sometimes, It's may grow (+19) and reduce (-19) based on the users settings
				
				* For example:
					If users add any content, like Subject or Extra field it's (19+1) = 20 grow up and reduce if users delete any contents.
					
					# Getting csv data value and assign it's a var
					$Name = trim($csvData[0]);
					$Roll_No = trim($csvData[1]);
					$Regi_No = trim($csvData[2]);
					$Class = trim($csvData[3]);
					$Exam = trim($csvData[4]);
					$Year = trim($csvData[5]);
					
					# Were use wpdb for add/insert csv assigned value into database.  for this, we need two types of @param.
					
								1,		2,
					insert(table, data)
					
					First, table name {$table} where to insert our csv data/value and Second is data {$data}, that's means values. Data must be an array, to assign where to insert the data/value.
					
					# For example: 
						$data = array (
							// where => value
							'Name' =>$Name,
							'Roll_No' =>$Roll_No,
										'Reg_No' =>$Reg_No,
										'Class' => $Class,
							'Exam' => $Exam,
							'Year' => $Year
						);
					
					# Now, we can insert our data/value into database
					1. $table		=	table,
					2. $data	 	=	data (all data in one array)
					
					// Finally Insert data/value
					$wpdb->insert(
						$table,	// 1. table
						$data, 	// 2. data
					);
					
					# SQP sample,
						INSERT INTO '$table' ('Name', 'Roll_No', 'Reg_No', 'Class', 'Exam', 'Year') VALUES ('$Name', '$Roll_No', '$Reg_No', '$Class', '$Exam', '$Year')
				
				# Please note, here were assign data {$value_len} with a function 'educare_demo_data()' for automatically adjust users settings and csv files {$dataLen == $value_len}. but it's same to work above details.
				*/

				$error_found = false; 

				if ($chek_name and !in_array($chek_name, $keys) or $chek_roll and !in_array($chek_roll, $keys) or $chek_regi and !in_array($chek_regi, $keys)) $error_found = true;

				// display error msg if length != 19|$content_len
				if( $dataLen != $content_len or $error_found ) $error++;
				if( $error_found ) continue;
				// process to import the results/data if everything ok
				if( !($dataLen == $content_len) ) continue;
				// Assign default value/field as a variables
				$data = array_combine($keys, $csvData);
				
				if ($chek_name) {
					$Name = sanitize_text_field($data[$chek_name]);
				} else {
					$Name = 'Null';
				}
				if ($chek_roll) {
					$Roll_No = sanitize_text_field($data[$chek_roll]);
					$search_roll = " AND Roll_No='$Roll_No'";
				} else {
					$Roll_No = 'Null';
					$search_roll = "";
				}
				if ($chek_regi) {
					$Regi_No = sanitize_text_field($data[$chek_regi]);
					$search_regi = " AND Regi_No='$Regi_No'";
				} else {
					$Regi_No = 'Null';
					$search_regi = "";
				}

				// $Name = trim($data['Name']);
				// $Roll_No = trim($data['Roll_No']);
				// $Regi_No = trim($data['Regi_No']);

				$Class = sanitize_text_field($data['Class']);
				$Exam = sanitize_text_field($data['Exam']);
				$Year = sanitize_text_field($data['Year']);

				$Result = sanitize_text_field($data['Result']);
				$GPA = sanitize_text_field($data['GPA']);
				$Photos = sanitize_text_field($data['Photos']);
		
				// Check results already exists or not
				$search = "SELECT count(*) as count FROM {$table} where Class='$Class' AND Exam='$Exam' $search_roll $search_regi AND Year='$Year'";
				$results = $wpdb->get_results($search, OBJECT);
				
				// ignore old results if all ready exist
				if($results[0]->count==0) {
			
					// Check default data/field is empty or not
					if(!empty($Name) && !empty($Roll_No) && !empty($Regi_No) && !empty($Class) && !empty($Exam) && !empty($Year) ) {
						$Details = educare_array_slice($data, 'Year', 'Result');
						$Details['Photos'] = $Photos;
						$Details = json_encode($Details);
						$Subject = educare_array_slice($data, 'GPA', 'Photos');
						$Subject = json_encode($Subject);

						$data = array (
							'Name' => $Name,
							'Roll_No' => $Roll_No,
							'Regi_No' => $Regi_No,
							'Class' => $Class,
							'Exam' => $Exam,
							'Year' => $Year,
							'Details' => $Details,
							'Subject' => $Subject,
							'Result' => $Result,
							'GPA' => $GPA
						);

						// echo '<pre>';	
						// print_r($data);	
						// echo '</pre>';

						// Insert data/results into database table
						$wpdb->insert($table, $data);
						// display how many data is imported
						if ($wpdb->insert_id > 0) {
							$totalInserted++;
						}
					}
				} else {
					// display how many data is already exists
					$exist++;
				}
			}
			// print import process details
			echo "<div class='notice notice-success is-dismissible'><p>Total Results Inserted: <b style='color: green;'>".esc_html($totalInserted)."</b> results<br>Allredy Exist: <b>".esc_html($exist)."</b> results<br>Error to import: <b style='color: red;'>".esc_html($error)."</b> results<br>Successfully Imported: ".esc_html($totalInserted)." of ".esc_html($total)."</p></div>";
			
			if ($error) {
				echo educare_guide_for('import_error', $error);
			}
		} else {
			// notify users if empty files or invalid extension
			echo "<div class='notice notice-error is-dismissible'><p>";
			if(empty($_FILES['import_file']['name'])) {
				echo "No file chosen! Please select a files";
			} else {
				echo "Invalid extension. Files must be an <b>.csv</b> extension for import the results. Please choose a .csv files";
			}
			echo "</p></div>";
		}
	}
}





/** =====================( Functions Details )======================
  
	### Educare Import Students

  * @since 1.2.7
	* @last-update 1.2.7

  * @return void
	
===================( function for import Students )=================== **/

function educare_import_students() {
	// Begin import students function
	global $wpdb;

	// Table name, where to import the students
	$table = $wpdb->prefix."educare_students";

	// Import CSV
	if(isset($_POST['educare_import_students'])) {

		// File extension
		$extension = pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION);

		// If file extension is 'csv'
		if(!empty($_FILES['import_file']['name']) && $extension == 'csv') {

			$totalInserted = 0;
			$total = 0;
			$exist = 0;
			$error = 0;
		
			// Open file in read mode
			$csvFile = fopen($_FILES['import_file']['tmp_name'], 'r');
			$keys = fopen($_FILES['import_file']['tmp_name'], 'r');
			$keys = fgetcsv($keys);
			$keys = array_map("utf8_encode", $keys);

			$data = array (
				'Name',
				'Roll_No',
				'Regi_No',
				'Class',
				'Year',
				'Photos'
			);

			$Extra_field = educare_demo_data('Extra_field');

			$value_len = count($data) + count($Extra_field);

			// Skipping header row
			fgetcsv($csvFile);
		
			// Read file
			while(($csvData = fgetcsv($csvFile)) !== FALSE) {
				$csvData = array_map("utf8_encode", $csvData);

				// echo '<pre>';	
				// print_r($csvData);	
				// echo '</pre>';
				
				$total ++;
				
				// CSV row column length (based on import files)
				$dataLen = count($csvData);
				
				// $table row column length (based on the users settings)
				$content_len = $value_len;

				// display error msg if length != 19|$content_len
				if( $dataLen != $content_len ) $error++;
				// process to import the results/data if everything ok
				if( !($dataLen == $content_len) ) continue;
				// Assign default value/field as a variables
				$keys = str_replace(' ' , '_', array_values($keys));
				$data = array_combine($keys, $csvData);

				$Name = trim($data['Name']);
				$Roll_No = trim($data['Roll_No']);
				$Regi_No = trim($data['Regi_No']);

				$Class = sanitize_text_field($data['Class']);
				$Year = sanitize_text_field($data['Year']);
				$Photos = sanitize_text_field($data['Photos']);

				// Check results already exists or not
				$search = "SELECT count(*) as count FROM {$table} where Class='$Class' AND Roll_No='$Roll_No' AND Regi_No='$Regi_No' AND Year='$Year'";

				$results = $wpdb->get_results($search, OBJECT);
				
				// ignore old results if all ready exist
				if($results[0]->count==0) {
			
					// Check default data/field is empty or not
					if(!empty($Name) && !empty($Roll_No) && !empty($Regi_No) && !empty($Class) && !empty($Year) ) {
						$Details = educare_array_slice($data, 'Year', 'Photos');

						$Details['Photos'] = $Photos;
						$Details = json_encode($Details);

						$data = array (
							'Name' => $Name,
							'Roll_No' => $Roll_No,
							'Regi_No' => $Regi_No,
							'Class' => $Class,
							'Year' => $Year,
							'Details' => $Details,
						);

						// Insert data/results into database table
						$wpdb->insert($table, $data);
						// display how many data is imported
						if ($wpdb->insert_id > 0) {
							$totalInserted++;
						}
					}
				} else {
					// display how many data is already exists
					$exist++;
				}
			}
			// print import process details
			echo "<div class='notice notice-success is-dismissible'><p>Total students Inserted: <b style='color: green;'>".esc_html($totalInserted)."</b> students<br>Allredy exist: <b>".esc_html($exist)."</b> students<br>Error to import: <b style='color: red;'>".esc_html($error)."</b> students<br>Successfully Imported: ".esc_html($totalInserted)." of ".esc_html($total)."</p></div>";
			
			if ($error) {
				echo educare_guide_for('import_error', $error);
			}
		} else {
			// notify users if empty files or invalid extension
			echo "<div class='notice notice-error is-dismissible'><p>";
			if(empty($_FILES['import_file']['name'])) {
				echo "No file chosen! Please select a files";
			} else {
				echo "Invalid extension. Files must be an <b>.csv</b> extension for import the results. Please choose a .csv files";
			}
			echo "</p></div>";
		}
	}
}




/** ====================( Functions Details )=====================
	
	### Check educare default settings

	* @since 1.2.4
	* @last-update 1.2.4

==================( function for Check educare default settings )================== **/

function educare_ai_fix() {
	$current_settings = educare_check_status();
	$current_data = $current_settings->display;
	$current_data = json_decode(json_encode($current_data), TRUE);
	$settings_data = educare_add_default_settings('Settings', true);
	$default_data = $settings_data['display'];

	$error_key = array_diff_key($current_data,$default_data);

	// remove unkhown key from data
	foreach ($error_key as $key => $value) {
		unset($current_data[$key]);
	}

	$msgs = '';
	// insert educare new data in database settings
	foreach ($default_data as $key => $data) {
		// keep user old settings
		if (!key_exists($key, $current_data)) {
			$current_data[$key] = $data;
			$msgs = '<div class="educare_post">'.educare_guide_for("Educare (AI) Problem Detection: Successfully fixed all bugs and errors").'</div>';
		}
	}
	
	foreach ($current_data as $key => $value) {
		$default_data[$key] = $value;
	}
	
	if ($msgs) {
		$current_settings->display = $default_data;
		educare_add_default_settings('Settings', false, $current_settings);
	}
	
	return $msgs;

}



/** ====================( Functions Details )=====================
	
	### Add, Updata or Remove Data
	* Usage example: educare_settings('Settings');

	* @since 1.2.4
	* @last-update 1.2.4
	 
	* @param string $list	Select database
	* @return null|HTML 

	* Add / Update / Remove - Subject, Exam, Class, Year, Extra field... and settings status.
	
	* this is a main function for update all above (Settings) content. it's decide which content need to Add / Update / Remove and where to store Data into database.
	*
	* this function also provide - Error / Success notification when users Add / Update / Remove any Data.
	
	* it's make temporary history data for notify the users. 
	
	* for example, when users update a Subject like - Science to Biology. this function notify the users like this - Successfully update Subject (Science) to (Biology).
	
==================( function for Add, Updata or Remove Data )================== **/

function educare_process_settings($list) {
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
   
	$search = $wpdb->get_results("SELECT * FROM $table WHERE list='$list'");
	
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
					<div class="notice notice-error is-dismissible"> 
						<p>You must fill the form for add the <b><?php echo esc_html($list);?></b>. thanks</p>
						<button class='notice-dismiss'></button>
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
					echo '<div class="notice notice-error is-dismissible"><p>'.esc_html($list).' <b>'.esc_html($target).'</b> is allready exist!</p><button class="notice-dismiss"></button></div>';
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

					echo '<div class="notice notice-success is-dismissible"><p>Successfully Added <b>'.esc_html($target).'</b> at the '.esc_html($list).' list<br>Total: <b>'.esc_html(count($data)).'</b> '.esc_html($list).' added</p><button class="notice-dismiss"></button></div>';
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
						
			if ($in_list == 'Extra_field') {
				$data_type = strtok($target, ' ');
				$Target = substr(strstr($target, ' '), 1);
				
				?>
				<div class="notice notice-success is-dismissible">
					<p>
					<center><h2>Edit <?php echo esc_html($list);?><h2></center>
					
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
								<p>Select type::</p>
								<select name="type">
									<option value="text" <?php if ( $data_type == "text") { echo "selected";}?>>Text</option>
									<option value="number" <?php if ( $data_type == "number") { echo "selected";}?>>Number</option>
									<option value="date" <?php if ( $data_type == "date") { echo "selected";}?>>Date</option>
									<option value="email" <?php if ( $data_type == "email") { echo "selected";}?>>Email</option>
								<select>
							</div>
						</div>
								
						<input type="text" name="<?php echo esc_attr($in_list);?>" hidden>
						<script>
							function add(form) {
								$type = form.type.value;
								$field = form.field.value
								if (!$field == 0) {
									form.Extra_field.value = $type+ " " +$field;
								}
							}
						</script>
				
						<input type="submit" name="educare_update_<?php echo esc_attr($list);?>" class="educare_button update<?php echo esc_attr(str_replace(' ', '', $list));?>" onClick="<?php echo esc_js('add(this.form)');?>" value="&#xf464 Edit">
				
						<input type="submit" name="<?php echo esc_attr("remove_$list");?>" class="educare_button remove<?php echo esc_attr(str_replace(' ', '', $list));?>" value="&#xf182">

					</form>
					</p>
					<button class="notice-dismiss"></button>
				</div>	
				<?php
			} else {
				?>
				<div class="notice notice-success is-dismissible">
					<p>
					<center><h2>Edit <?php echo esc_html($list);?><h2></center>
					<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
						<input type="hidden" name="remove" value="<?php echo esc_attr($target);?>"/>
						<input type="hidden" name="old_data" value="<?php echo esc_attr($target);?>"/>
						Edit - <b><?php echo esc_html($target);?></b>:<br>
						<label for="Name" class="labels" id="name"></label>
							<input type="text" name="<?php echo esc_attr($list);?>" value="<?php echo esc_attr($target);?>" placeholder="<?php echo esc_attr($list);?> Name">
					
						<input type="submit" name="educare_update_<?php echo esc_attr($list);?>" class="educare_button update<?php echo esc_attr(str_replace(' ', '', $list));?>" value="&#xf464 Edit">
							
						<input type="submit" name="<?php echo esc_attr("remove_$list");?>" class="educare_button remove<?php echo esc_attr(str_replace(' ', '', $list));?>" value="&#xf182">
													
					</form>
					</p>
					<button class="notice-dismiss"></button>
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
				
				$exist = "<div class='notice notice-error is-dismissible'><p>Update failed. Because,  <b>".esc_html($new)."</b> is allready exist in ".esc_html($list)." list. Please try a different <i>(unique)</i> one!</p><button class='notice-dismiss'></button></div>";
				
				
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
					$msg = "<div class='notice notice-error is-dismissible'><p>There are no changes for updates!</p><button class='notice-dismiss'></button></div>";
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
				
				echo '<div class="notice notice-success is-dismissible"><p>Successfully removed <b>'.esc_html($target).'</b> from the '.esc_html($list).' list.</p><button class="notice-dismiss"></button></div>';
			} else {
				echo '<div class="notice notice-error is-dismissible"><p>Sorry, '.esc_html($list).' <b>'.esc_html($target).'</b> is not found!</p><button class="notice-dismiss"></button></div>';
			}
		}
		
		if ($list == 'Settings') {
			if (isset($_POST['educare_reset_default_settings'])) {
				$wpdb->query("DELETE FROM $table WHERE id = $id");
				
				educare_add_default_settings('Settings');
				
				echo "<div class='notice notice-success is-dismissible'> <p>Successfully reset default <b>settings</b></p><button class='notice-dismiss'></button></div>";
			}
			
			if (isset($_POST['educare_update_settings_status'])) {
				echo "<div class='notice notice-success is-dismissible'><p>Successfully updated Settings</p><button class='notice-dismiss'></button></div>";
			}
			
			if ( isset( $_POST['educare_default_photos'] ) && isset( $_POST['educare_attachment_id'] ) ) {
					echo "<div class='notice notice-success is-dismissible'><p>Successfully updated default students photos</p><button class='notice-dismiss'></button></div>";
			}
		}
	}
}


/** ====================( Functions Details )======================
	
	### Settings Status

	* Usage example: educare_settings_status($target, $title, $comments);

	* @since 1.0.0
	* @last-update 1.2.0
	
	* @param string $target					Select settings status
	* @param string $title					Display settings title
	* @param string $comments				Settings informations
	
	* @return void|HTML

	* One more exp: educare_settings_status('confirmation', 'Delete confirmation', "Anable and disable delete/remove confirmation");
	
	* Anable or Disable Settings status
	* Display toggle switch to update status
	
	* it's return radio or input. so, always call function under form tags. Exp: 
	<form class="educare-update-settings" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
	<?php
		
	educare_settings_status('delete_subject', 'Automatically Delete Subject', "Automatically Delete Subject from Results Table When You Delete Subject From Subject List?");
	
	educare_settings_status('clear_field', 'Delete and Clear field data', "Tips: If you set <b>No</b> that's mean only field will be delete. And, if you set <b>Yes</b> - clear field data when you delete any (current) field. Delete and Clear field data?");
	
	educare_settings_status('confirmation', 'Delete confirmation', "Anable and disable delete/remove confirmation");
	
	educare_settings_status('guide', 'Guidelines', "Anable and disable guide/help messages");
	
	?>
	<input type="submit" name="educare_update_settings_status" class="educare_button" value="&#x464 Update">
	</form>
	
==============( Settings Status Update )================ **/

function educare_settings_status($target, $title, $comments) {
	
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
   
	$search = $wpdb->get_results("SELECT * FROM $table WHERE list='Settings'");
	
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
		if ( $target == 'results_page' or  $target == 'students_page' or $target == 'optional_sybmbol' or  $target == 'institute') {
			echo "<div class='educare-settings'>";
			echo "<div class='title'>
			<h3>".esc_html($title)."<h3>
			<p class='comments'>".wp_kses_post($comments)."</p>
			<input type='text' id='".esc_attr($target)."' name='".esc_attr($target)."' value='".esc_attr(educare_check_status($target))."' placeholder='".esc_attr(educare_check_status($target))."'>
			</div></div>";
		}
		elseif ($target == 'display') {

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

				<script>
					$(document).ready(function(){
						$("input[name='Roll_No']").click(function() {
							// alert($(this).val());
							if ($(this).val() == 'checked') {
								$('#Regi_No_no').attr("disabled",false);
								// alert('checked!');
							}
							else {
								// $('#Regi_No_no').attr("disabled",true);
								$("input[name='Regi_No']").prop("checked", true);
							}
						});

						$("input[name='Regi_No']").click(function() {
							// alert($(this).val());
							if ($(this).val() == 'checked') {
								$('#Roll_No_no').attr("disabled",false);
								// alert('checked!');
							}
							else {
								// $('#Roll_No_no').attr("disabled",true);
								$("input[name='Roll_No']").prop("checked", true);
							}
						});
					});
				</script>
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
	} else {
		echo educare_guide_for('db_error');
	}
}


function educare_settings_form() {
	?>
		<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
			<?php
			ob_start();
			echo esc_url( bloginfo( 'url' ) );
			$domain = ob_get_clean();

			?>
			<div class="collapses">
				<div class="collapse">
					<input class="head" type="radio" name="settings_status_menu" id="Page_Setup_menu" checked>
					<label class="collapse-label" for="Page_Setup_menu"><div><i class="dashicons dashicons-edit-page"></i> Page Setup</div></label>
					<div class="collapse-content">
						<?php
						echo "<div style='padding: 1px 0;'>";
						echo educare_guide_for("Inter your front end all page slug (Where you use educare shortcode in your editor, template or any shortcode-ready area for front end results system). Don't need to insert with domain - ".esc_url($domain)."/results. Only slug will be accepted, for exp: results or index.php/results.");
						echo '</div>';
						
						educare_settings_status('results_page', 'Results Page', "Inter your front end results page slug (Where you use <strong>`[educare_results]`</strong> shortcode in your editor, template or any shortcode-ready area for front end results system).");
						?>

						<?php
						educare_settings_status('students_page', 'Students Page', "Inter your front end students page slug (Where you use <strong>`[educare_students]`</strong> shortcode in your editor, template or any shortcode-ready area for front end students profiles system).<br> <b>Note:</b> This feature has not been launched yet. It can be used in the next update");
						?>
					</div>
				</div>

				<div class="collapse">
					<div style="background-color: inicial;">
					<input class="head" type="radio" name="settings_status_menu" id="Display_menu">
					<label class="collapse-label" for="Display_menu"><div><i class="dashicons dashicons-admin-appearance"></i> Customize</div></label>
					<div class="collapse-content">
						<?php
						echo "<div style='padding: 1px 0;'>";
						echo educare_guide_for('display_msgs');
						echo '</div>';
			
						educare_settings_status('display', 'Delete confirmation', "Anable and disable delete/remove confirmation");
						?>
				</div>
					
					</div>
				</div>

				<div class="collapse">
					<input class="head" type="radio" name="settings_status_menu" id="Results_System_menu">
					<label class="collapse-label" for="Results_System_menu"><div><i class="dashicons dashicons-welcome-learn-more"></i> Results System</div></label>
					<div class="collapse-content">
						<?php
						educare_settings_status('institute', 'Institution', "Name of the institutions (Title)");
						
						educare_settings_status('optional_sybmbol', 'Optional Subject Selection', "Define optional subject identifier character/symbol. In this way educare define and identify optional subjects when you add or import results.");
			
						educare_settings_status('auto_results', 'Auto Results', "Automatically calculate students results status Passed/Failed and GPA");
		
						educare_settings_status('photos', 'Students Photos', "Show or Hide students photos");
		
						educare_settings_status('custom_results', 'Custom Design Permissions', "You need to permit/allow this options when you add custom functionality or customize results card or searching forms");
						?>
					</div>
				</div>

				<div class="collapse">
					<input class="head" type="radio" name="settings_status_menu" id="Others_menu">
					<label class="collapse-label" for="Others_menu"><div><i class="dashicons dashicons-admin-tools"></i> Others</div></label>
					<div class="collapse-content">
						<?php
						educare_settings_status('guide', 'Guidelines', "Anable and disable guide/help messages");

						educare_settings_status('confirmation', 'Delete confirmation', "Anable and disable delete/remove confirmation");

						educare_settings_status('copy_demo', 'Copy Demo Data', "<strong>Recommendation:</strong> Allow this option when your systems don't allow to download demo file. If you enable this options all demo data will be show in text box. You can copy and past this data into csv files.");
						
						educare_settings_status('advance', 'Advance Settings', "Anable and disable Advance/Developers menu. Note: it's only for developers or advance users");
						
						echo '<div id="advance_settings">';
							
						educare_settings_status('problem_detection', '(AI) Problem Detection', "Automatically detect and fix educare relatet problems");
						
						educare_settings_status('clear_data', 'Clear Data', "Clear all (Educare) data from database when you uninstall or delete educare from plugin list?");
						
						echo '</div>';
						?>
					</div>
				</div>

			</div>
			
			<?php
			
			?>
			<script type='text/javascript'>
				jQuery( document ).ready( function( $ ) {
					var advance = '<?php echo educare_esc_str(educare_check_status('advance'));?>';
					if (advance == 'unchecked') {
						$( '#advance_settings' ).css( 'display', "none" );
					}
				});
			</script>
				
			<button type="submit" name="educare_update_settings_status" class="educare_button"><i class="dashicons dashicons-yes-alt"></i> Save</button>
			<button type="submit" name="educare_reset_default_settings" class="educare_button"><i class="dashicons dashicons-update"></i> Reset Settings</button>
				
		</form>
	<?php
}





/** ====================( Functions Details )======================
	
	### Class wise Jubject
	* Usage example: educare_setting_subject('Subject');
	
	* @since 1.2.0
	* @last-update 1.2.4
	
	* @param string|mixed $list			select specific data
	
	* @return void

	* This is a most important function of educare. Because, additing this function its possible to add different grading rules. here some necessity of this function given below:
		1. Sossible to add class wise subject
		2. Sossible to add different grading systems
		3. Possible to manage or modify grading systems
		4. Macking Educare_results database unique
		5. Make database clean
		and much more...............
	
=================( function for Class wise Jubject  )================ **/

function educare_process_class($list) {
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
   
	$search = $wpdb->get_results("SELECT * FROM $table WHERE list='Class'");

	if ($search) {
		foreach ( $search as $print ) {
			$data = $print->data;
			$id = $print->id;
		}
		
		$data = json_decode($data, true);

		// for add list items (Subject)
		if (isset($_POST['educare_process_class'])) {
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
					echo '<div class="notice notice-error is-dismissible"> <p><b> '.esc_html( $target ).'</b> is allready exist in class list</p><button class="notice-dismiss"></button></div>';
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
					echo '<div class="notice notice-error is-dismissible"> <p>You must fill the form for add the <b>'.esc_html($list).'</b>. thanks</p><button class="notice-dismiss"></button></div>';
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
								$msg = '<div class="notice notice-error is-dismissible"> <p>There are no change for update</p><button class="notice-dismiss"></button></div>';
							} else {
								$msg = '<div class="notice notice-error is-dismissible"> <p><b> '.esc_html( $target ).'</b> is allready exist in class '.esc_html( $class ).'</p><button class="notice-dismiss"></button></div>';

								if (isset($_POST['update_class'])) {
									$msg = '<div class="notice notice-error is-dismissible"> <p><b> '.esc_html( $target ).'</b> is allready exist in class list</p><button class="notice-dismiss"></button></div>';
								}
								
							}
						}
						elseif (isset($_POST['edit_subject'])) {
							?>
							<div class="notice notice-success is-dismissible add_results"><p>
							<form action="" method="post">
								Edit <?php echo esc_html($list);?>:
								<input type="hidden" name="educare_process_class">
								<input type="hidden" name="old_subject" value="<?php echo esc_attr($target);?>">
								<input type="hidden" name="old_class" value="<?php echo esc_attr($class);?>">
								<input type="text" name="subject" class="fields" value="<?php echo esc_attr($target);?>" placeholder="<?php echo esc_attr($target);?>" pattern="[A-Za-z0-9 ]+" title="Only Caretaker, Number and Space allowed. (A-Za-z0-9)">

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
								<br>

								<button id="educare_results_btn" class="educare_button" name="update_<?php echo esc_attr($list);?>" type="submit"><i class="dashicons dashicons-edit"></i> Edit <?php echo esc_html($list);?></button>

								
							</form>
							</p>
							<button class="notice-dismiss"></button>
							</div>
							<?php
						}
						elseif (isset($_POST['edit_class'])) {
							?>
							<div class="notice notice-success is-dismissible add_results"><p>
							<form action="" method="post">
								Edit Class:
								<input type="hidden" name="educare_process_class">
								<input type="hidden" name="old_class" value="<?php echo esc_attr($class);?>">
								<input type="text" name="class" class="fields" value="<?php echo esc_attr($class);?>" placeholder="<?php echo esc_attr($class);?>" pattern="[A-Za-z0-9 ]+" title="Only Caretaker, Number and Space allowed. (A-Za-z0-9)">

								<br>
								
								<button id="educare_results_btn" class="educare_button" name="update_class" type="submit"><i class="dashicons dashicons-edit"></i> Edit Class</button>

							</form>
							</p>
							<button class="notice-dismiss"></button>
							</div>
							<?php
						}

						elseif (isset($_POST['remove_subject'])) {
							$msg = '<div class="notice notice-success is-dismissible"><p>Successfully removed <b>'.esc_html($target).'</b> from the '.esc_html($list).' list.</p><button class="notice-dismiss"></button></div>';
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
							
							$msg = '<div class="notice notice-success is-dismissible"> <p>Successfully Added <b>'.esc_html($target).'</b> at the subject list<br>Class: <b>'.esc_html($class).'</b><br>Total: <b>'.esc_html(count($subject_list)).'</b> Subject added</p><button class="notice-dismiss"></button>
							</div>';
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

								$msg = '<div class="notice notice-success is-dismissible"> <p>Successfully change subject <b class="error">'.esc_html($old_sub).'</b> to <b class="success">'.esc_html($target).'</b></p><button class="notice-dismiss"></button></div>';
							}
							elseif (strtolower($target) == $old_subject and $class != $old_class) {
								unset($data[$old_class][$get_key]);
								array_values($data[$old_class]);
								array_push($data[$class], $target);
								
								$msg = '<div class="notice notice-success is-dismissible"> <p>Successfully change class <b class="error">'.esc_html($old_class).'</b> to <b class="success">'.esc_html($class).'</b></p><button class="notice-dismiss"></button></div>';
							} else {
								// Add data
								$data[$old_class][$get_key] = $target;
								// Remove data
								unset($data[$old_class][$get_key]);
								array_values($data[$old_class]);
								array_push($data[$class], $target);

								$msg = "<div class='notice notice-success is-dismissible'><p>Succesfully update subject <b class='error'>".esc_html($old_sub)."</b> to <b class='success'>".esc_html($target)."</b>. also changed class <b class='error'>".esc_html($old_class)."</b> to <b class='success'>".esc_html($class)."</b>.</p><button class='notice-dismiss'></button></div>";
							}
						}

						if (isset($_POST['update_class'])) {
							$old_class = sanitize_text_field( $_POST['old_class'] );
							$get_key = array_search($data[$old_class], $data);
							
							// $new_class = $data[$old_class];

							if(strtolower($old_class) == strtolower($target)) {
								$msg = "<div class='notice notice-error is-dismissible'><p>There are no changes for updates!</p><button class='notice-dismiss'></button></div>";
							} else {
								if (key_exists(strtolower($target), array_change_key_case($data))) {
									echo '<div class="notice notice-error is-dismissible"><p><b>'.esc_html($target).'</b> is allready exist in class list</p><button class="notice-dismiss"></button></div>';
								} else {
									if ($target !== $old_class) {
										$data = educare_replace_key($data, $old_class, $target);
										$msg = '<div class="notice notice-success is-dismissible"> <p>Successfully changed class <b class="error">'.esc_html($old_class).'</b> to <b class="success">'.esc_html($target).'</b></p><button class="notice-dismiss"></button></div>';
									}
								}
							}
						}

						if (isset($_POST['remove_class'])) {
							$class = sanitize_text_field( $_POST['class'] );
							
							unset($data[$class]);
							
							$msg = '<div class="notice notice-success is-dismissible"> <p><b class="error">'.esc_html($class).'</b> has been successfully removed from the class list</p><button class="notice-dismiss"></button></div>';
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
				// if (isset($_POST['add_class'])) {
				// 	$target = sanitize_text_field($_POST['class']);
				// }

				
				if (isset($_POST['add_class'])) {
					if (key_exists(strtolower($class), array_change_key_case($data))) {
						echo '<div class="notice notice-error is-dismissible"><p><b>'.esc_html($class).'</b> is allready exist in class list</p><button class="notice-dismiss"></button></div>';
					} else {
						if (empty($class)) {
							echo '<div class="notice notice-error is-dismissible"> <p>You must fill the form for add the <b>Class</b>. thanks</p><button class="notice-dismiss"></button></div>';
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

							echo '<div class="notice notice-success is-dismissible"> <p>Successfully Added <b>'.esc_html($target).'</b> at the class list<br></p><button class="notice-dismiss"></button>
							</div>';
						}
					}
				} else {
					echo '<div class="notice notice-error is-dismissible">';

					if ($data) {
						?>
						<p>Sorry, <b><?php echo esc_html($class);?></b> not exist<b></b> at the class list<br>
						If you need to add subject in this (<?php echo esc_html($class);?>) class. First, You need to add this (<?php echo esc_html($class);?>) in the class list. Then, You would allowed to add some subject. thanks
						<?php
					} else {
						?>
						<p>Sorry, you don't have added any class yet. For add subject, you need to add a class first. Then, you get to add a subject for this class. thank you 
						<?php
					}
						
					echo '</p><button class="notice-dismiss"></button></div>';
				}
			}
		}
	}
}

function educare_setting_subject($list, $form = null) {
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
	
	// add subject/extra field to (database) results table
	// $Educare_results = $wpdb->prefix . 'educare_results';
   
	$search = $wpdb->get_results("SELECT * FROM $table WHERE list='Class'");

	if ($search) {
		foreach ( $search as $print ) {
			$data = $print->data;
			$id = $print->id;
		}
		
		$data = json_decode($data, true);
	}
	
	if (!$form) {
		$count = 1;
		// Checked first class content (Subjects)
		$first = array_key_first($data);

		if ($data) {
			echo '<div class="collapses">';
			foreach ($data as $class => $val) {
				// here $val = total subject in this class
				?>
				<div class="collapse">
					<input class="head" type="radio" name="subject" id="<?php echo esc_attr( $class );?>" <?php if ($class == $first or isset($_POST['class']) and $_POST['class'] == $class) {echo 'checked';}?>>
					<label class="collapse-label" for="<?php echo esc_attr( $class );?>">
						<?php echo esc_html( $count++ ) . '. ' . esc_html( $class );?>
						<span>
							<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
							<input type="hidden" name="educare_process_class"><input type="hidden" name="class" value="<?php echo esc_attr( $class );?>">
							<input type="submit" name="edit_class" value="&#xf464"><input type="submit" name="remove_class" value="&#xf182"></form>
						</span>
					</label>

					<div class="collapse-content bg-white">
						<table class='educare_add_content'>
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
												<input type="hidden" name="educare_process_class">

												<input type="hidden" name="class" value="<?php echo esc_attr($class);?>"/>

												<input type="hidden" name="subject" value="<?php echo esc_attr($subject);?>"/>
												
												<input type="submit" name="edit_<?php echo esc_attr($list);?>" class="button success" value="&#xf464">
												
												<input type="submit" name="<?php echo esc_attr("remove_$list");?>" class="button error" value="&#xf182">
													
											</form>
										</td>
									</tr>
									<?php
								}

							} else {

								echo "<tr><td colspan='4'><div class='notice notice-error is-dismissible'><p>Currently, you don't have added any subject in this class. Pleas add a subject under this class by using above forms. Thanks</p></div></td></tr>";
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
			echo "<div class='notice notice-error is-dismissible'><p>Currently, you don't have added any classe. Please add a class by clicking on the <b>Add Class</b> tab. Thanks</p></div>";
		}
		
	}
	
	if ($form) {
		?>
		<div class="tab_head">
			<button class="tablink educare_button" onclick="openTab(event,'x_subject')">Add Subject</button>
			<button class="tablink" onclick="openTab(event,'class')">Add Class</button>
		</div>
		
		<div id="x_subject" class="section_name">
			<form class="add_results" action="" method="post" id="add_<?php echo esc_attr($list);?>">
				<div class="content">
					<input type="hidden" name="educare_process_class">

					<div class="select add-subject">
						<div>
						<p>Subject:</p>
							<input type="text" name="<?php echo esc_attr($list);?>" class="fields" placeholder="<?php echo esc_attr($list);?> name" pattern="[A-Za-z0-9 ]+" title="Only Caretaker, Number and Space allowed. (A-Za-z0-9)">
						</div>
					
						<div>
							<p>Subject For:</p>
							<select name='class'>
								<?php
								foreach ($data as $key => $value) {
									echo "<option value='".esc_attr($key)."'>".esc_html($key)."</option>";
								}
								?>
							</select>
						</div>
					</div>

					<button id="educare_results_btn" class="educare_button" name="add_<?php echo esc_attr($list);?>" type="submit"><i class="dashicons dashicons-plus-alt"></i> Add Subject</button>
				</div>
			</form>
		</div>

		<div id="class" class="section_name" style="display:none">
			<form class="add_results" action="" method="post" id="add_<?php echo esc_attr($list);?>">
				<div class="content">
					<p>Class:</p>
					<input type="hidden" name="educare_process_class">
					<label for="<?php echo esc_attr($list);?>" class="labels" id="<?php echo esc_attr($list);?>"></label>
					<input type="text" name="class" class="fields" placeholder="Class name" pattern="[A-Za-z0-9 ]+" title="Only Caretaker, Number and Space allowed. (A-Za-z0-9)">
					<br>
					
					<button id="educare_results_btn" class="educare_button" name="add_class" type="submit"><i class="dashicons dashicons-plus-alt"></i> Add Class</button>
				</div>
			</form>
		</div>
		<?php
	}

	?>
	<script>
		function openTab(evt, myData) {
			var i, x, tablinks;
			x = document.getElementsByClassName("section_name");
			for (i = 0; i < x.length; i++) {
				x[i].style.display = "none";
			}
			tablinks = document.getElementsByClassName("tablink");
			for (i = 0; i < x.length; i++) {
				tablinks[i].className = tablinks[i].className.replace(" educare_button", "");
			}
			document.getElementById(myData).style.display = "block";
			evt.currentTarget.className += " educare_button";
		}
		
	</script>

	<?php
}


/** ====================( Functions Details )======================
	
	### Display Content
	* Usage example: educare_content('Exam');
	
	* @since 1.0.0
	* @last-update 1.0.0
	
	* @param string $list	Exam, Year, Extra field
	* @return void|HTML

	* Display Content - Subject, Exam, Class, Year Extra field...
	
=================( function for Display Content  )================ **/

function educare_content($list, $form = null) {
	
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
	// remove all _ characters from the list (normalize the $list)
	$List = str_replace('_', ' ', $list);
	// section head
	// echo '<h3 id ="'.esc_attr($list).'">'.esc_html($List).' List</h3>';

	// echo '<div id="msg_for_'.esc_attr($list).'"></div>';
   
	$search = $wpdb->get_results("SELECT * FROM $table WHERE list='$list'");
	
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
						    	
						</form></td>
					</tr>
				<?php
				
				} // end if ($list)
			}
			
			$target = ob_get_clean();
		}
		
		if (!$form) {
			// echo '<h3 id ="'.esc_attr($list).'">'.esc_html($List).' List</h3>';
			// echo '<div id="msg_for_'.esc_attr($list).'"></div>';

			if (!empty($target)) {
				?>
				<table class='educare_add_content'>
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
					<script>
						function add(form) {
							$type = form.type.value;
							$field = form.field.value
							if (!$field == 0) {
								form.Extra_field.value = $type+ " " +$field;
							}
						}
					</script>
						
						
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

// Pack all in one
function educare_get_all_content($list) {
	echo '<h3 id ="'.esc_attr($list).'">'.esc_html(ucwords(str_replace('_', ' ', $list))).' List</h3>';
	ob_start();
	educare_content($list);
	$data = ob_get_clean();
	echo '<div id="msg_for_'.esc_attr($list).'">'.wp_check_invalid_utf8(str_replace('_', ' ', $data)).'</div>';
	
	educare_content($list, true);
	educare_ajax_content($list);
}

function educare_get_content() {
	educare_get_all_content('Exam');
	educare_get_all_content('Year');
	educare_get_all_content('Extra_field');
}


function educare_ajax_content($list) {
	?>
	<script>
		$(document).on("click", "#educare_add_<?php echo esc_attr($list);?>", function(event) {
			event.preventDefault();
			$(this).attr('disabled', true);
			var form_data = $(this).parents('form').serialize();
			var action_for = "educare_add_<?php echo esc_attr($list);?>";
			$.ajax({
				url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
				data: {
					action: 'educare_process_content',
					form_data: form_data,
					action_for
				},
				type: 'POST',
				success: function(data) {
					$('#msg_for_<?php echo esc_attr($list);?>').html(data);
					$("#educare_add_<?php echo esc_attr($list);?>").attr('disabled', false);
				},
				error: function(data) {
					$('#msg_for_<?php echo esc_attr($list);?>').html("<?php echo educare_guide_for('db_error')?>");
				},
				complete: function() {
					// event.remove();
				},
			});
			
		});

		$(document).on("click", "input.remove<?php echo esc_attr(str_replace('_', '', $list));?>", function(event) {
			// $(this).attr('disabled', true);
			event.preventDefault();
			var form_data = $(this).parents('form').serialize();
			var target = $(this).prevAll("[name='remove']").val();
			var action_for = "remove_<?php echo esc_attr($list);?>";
			$.ajax({
				url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
				data: {
					action: 'educare_process_content',
					form_data: form_data,
					action_for
				},
				type: 'POST',
				beforeSend:function() {
					<?php 
					if (educare_check_status('confirmation') == 'checked') {
						echo 'return confirm("Are you sure to remove (" + target + ") from this '.esc_js(ucwords(str_replace('_', ' ', $list))).' list?")';
					}
					?>
      	},
				success: function(data) {
					$('#msg_for_<?php echo esc_attr($list);?>').html(data);
				},
				error: function(data) {
					$('#msg_for_<?php echo esc_attr($list);?>').html("<?php echo educare_guide_for('db_error')?>");
				},
			});
		});

		
		$(document).on("click", "input.edit<?php echo esc_attr(str_replace('_', '', $list));?>", function(event) {
			// $(this).attr('disabled', true);
			event.preventDefault();
			var form_data = $(this).parents('form').serialize();
			var action_for = "educare_edit_<?php echo esc_attr($list);?>";
			$.ajax({
				url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
				data: {
					action: 'educare_process_content',
					form_data: form_data,
					action_for
				},
				type: 'POST',
				success: function(data) {
					$('#msg_for_<?php echo esc_attr($list);?>').html(data);
				},
				error: function(data) {
					$('#msg_for_<?php echo esc_attr($list);?>').html("<?php echo educare_guide_for('db_error')?>");
				},
			});
		});


		$(document).on("click", "input.update<?php echo esc_attr(str_replace('_', '', $list));?>", function(event) {
			// $(this).attr('disabled', true);
			event.preventDefault();
			var form_data = $(this).parents('form').serialize();
			var action_for = "educare_update_<?php echo esc_attr($list);?>";
			$.ajax({
				url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
				data: {
					action: 'educare_process_content',
					form_data: form_data,
					action_for
				},
				type: 'POST',
				success: function(data) {
					$('#msg_for_<?php echo esc_attr($list);?>').html(data);
				},
				error: function(data) {
					$('#msg_for_<?php echo esc_attr($list);?>').html("<?php echo educare_guide_for('db_error')?>");
				},
			});
		});

		$(document).on("click", ".notice-dismiss", function(event) {
			$(this).parent('div').fadeOut();
		});

	</script>
	<?php
}


add_action('wp_ajax_educare_process_content', 'educare_process_content');

function educare_process_content() {
	$action_for = sanitize_text_field($_POST['action_for']);
	// $currenTab = sanitize_text_field($_POST['currenTab']);
	wp_parse_str($_POST['form_data'], $_POST);
	$_POST[$action_for] = $action_for;

	if (isset($_POST['educare_process_class'])) {
		educare_process_class("subject");
		educare_setting_subject("subject");
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

	die;
}



// Under constructions 
function educare_settings_page_management() {
	// get the slug of the page we want to display 
	// then we include the page
	if (isset($_GET['add-content'])) {
		echo 'Settings';
	} else {
		echo 'add';
	}
}


// Under constructions 
add_action('wp_ajax_educare_process_settings_page', 'educare_process_settings_page');

function educare_process_settings_page() {
	$action_for = sanitize_text_field($_GET['action_for']);
	// $currenTab = sanitize_text_field($_POST['currenTab']);
	wp_parse_str($_GET['form_data'], $_GET);
	$_GET[$action_for] = $action_for;

	educare_settings_page_management();

	die;
}




add_action('wp_ajax_educare_process_forms', 'educare_process_forms');

function educare_process_forms() {
	$action_for = sanitize_text_field($_POST['action_for']);
	$data_for = sanitize_text_field($_POST['data_for']);
	// $currenTab = sanitize_text_field($_POST['currenTab']);
	wp_parse_str($_POST['form_data'], $_POST);
	$_POST[$action_for] = $action_for;
	$_POST['data_for'] = $data_for;

	if (isset($_POST['data_for']) and $_POST['data_for'] == 'students') {
		educare_save_results(true);
	} else {
		educare_save_results();
	}

	die;
}


function educare_show_student_profiles() {
	if (isset($_POST['educare_results_by_id'])) {
		$id = sanitize_text_field($_POST['id']);
		$students = educare_get_students($id);
		$Mobile = $DoB = '';
		if ($students) {
			$Name = $students->Name;
			$Class = $students->Class;
			$Roll_No = $students->Roll_No;
			$Regi_No = $students->Regi_No;
			$Year = $students->Year;
			$Details = $students->Details;
			$Details = json_decode($Details);
			$Photos = $Details->Photos;

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
						<li><b>Clas</b> <span>'.esc_html( $Class ).'</span></li>
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

			'.educare_guide_for('<b>Card Title:</b> You can change card title from educare settings.<br><b>Analytics (Under Construction):</b> If you need these (Analytics and Print Card) features, please send your feedback on the Educare plugin forum. If we get 2 requests, this feature will be added in the next update.').'

			<div class="add_results analytics">
				<div class="content">
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
			';
		}
	} else {
		// save forms data
    echo '<h1>Profiles</h1><div id="msgs" style="text-align:center;">';
		?>
		<span style='font-size:100px;'>&#9785;</span><br>
		<b>Students Not Fount!</b>
		<?php
		echo '</div>';
	}
}


function educare_students_management() {
	// get the slug of the page we want to display 
	// then we include the page
	if (isset($_GET['add-students'])) {
		// include (EDUCARE_ADMIN."menu/view-results.php");
		echo '<h1>Add Students</h1>';

		echo educare_guide_for("Here you can add students and their details. Once, if you add and fill student details then you don't need to fill student details again while adding or publishing any result. If you miss something and need to update/edit, you can update a student's details from the <a href='admin.php?page=educare-all-students&update-students'>Update Menu</a>.");
		
		// save forms data
		echo '<div id="msgs">';
		educare_save_results(true);
		echo '</div>';
		
		// get results forms for add result
		echo '<div id="msgs_forms">';
		educare_get_results_forms('', 'Add', true);
		echo '</div>';
	}

	elseif (isset($_GET['update-students'])) {
		// include (EDUCARE_ADMIN."menu/view-results.php");
		echo '<h1>Update Students</h1>';

		echo educare_guide_for("Search student by roll, reg no, selecting class and year for update or remove specific students (All fields are requred)");

		// save forms data
    echo '<div id="msgs">';
		educare_save_results(true);
		echo '</div>';
		// Search form for edit/delete results
		if (!isset($_POST['edit']) and !isset($_POST['edit_by_id'])) {
			educare_get_search_forms(true);
		}
		
	}
	elseif (isset($_GET['import-students'])) {
		// include (EDUCARE_ADMIN."menu/view-results.php");
		echo '<h1>Import Students</h1>';

		educare_import_students();

		echo educare_guide_for("<strong>Note:</strong> Result and student import files are different. So, when you import students you need to create the import file differently. If you don't know how to create import file for students, Please download the student demo files given below. (All class .csv files head will be same)");
		?>

		<!-- Import Form -->
		<form  class="add_results" method="post" action="<?php esc_url($_SERVER['REQUEST_URI']); ?>" enctype="multipart/form-data" id="upload_csv">
			<div class="content">
				<p>Files must be an <b>.csv</b> extension for import the results.</p>
				<input type="file" name="import_file">
				<select name="Class" class="form-control">
					<?php educare_get_options('Class', '');?>
				</select><br>
				<button class="educare_button" type="submit" name="educare_import_students"><i class="dashicons dashicons-database-import"></i> Import</button>
			</div>
		</form>
		<br>

		<div class='demo'>
			<p>Select class for demo files:</p>
			
			<select id="Class" name="Class" class="form-control">
				<option value="">Select Class</option>
				<?php educare_get_options('Class', $Class);?>
			</select>

			<div id="result_msg"><br><p><a class='educare_button disabled' title='Download Import Demo.csv Error'><i class='dashicons dashicons-download'></i> Download Demo</a></p></div>

			<script>
			$(document).on("change", "#Class", function() {
				$(this).attr('disabled', true);
				var class_name = $('#Class').val();
				// var id_no = $('#id_no').val();
				$.ajax({
						url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
						data: {
						action: 'educare_demo',
						class: class_name,
						students: 'students',
					},
						type: 'POST',
						success: function(data) {
							$('#result_msg').html(data);
							$('#Class').attr('disabled', false);
						},
						error: function(data) {
							$('#result_msg').html("<?php echo educare_guide_for('db_error')?>");
						},
				});
			});
			</script>
		</div>
		<?php
		
	}
	elseif (isset($_GET['profiles'])) {
		// include (EDUCARE_ADMIN."menu/view-results.php");
		// echo '<h1>Students Profiles</h1>';
		
		// save forms data
    echo '<div id="msgs">';
		educare_show_student_profiles();
		echo '</div>';
		
	} else {
		define('EDUCARE_ALL_STUDENTS', 'admin.php?page=educare-all-students&');
		$add = "<a href='".EDUCARE_ALL_STUDENTS."add-students'>Add Students</a>";
		$update = "<a href='".EDUCARE_ALL_STUDENTS."update-students'>Update Students</a>";

		echo '<h1>All Students</h1>';
		echo educare_guide_for("Here you can add, edit, update students and their details. For this you have to select the options that you see here. Options details: firt to last (All, Add, Update, Import Students)");

		educare_all_view(true, 15);
	}
}


add_action('wp_ajax_educare_process_students', 'educare_process_students');

function educare_process_students() {
	$action_for = sanitize_text_field($_GET['action_for']);
	// $currenTab = sanitize_text_field($_POST['currenTab']);
	wp_parse_str($_GET['form_data'], $_GET);
	$_GET[$action_for] = $action_for;

	// echo '<pre>';	
	// print_r($_GET);
	// echo '</pre>';

	educare_students_management();

	die;
}


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
			$search = $wpdb->get_results("SELECT * FROM ".$educare_students." WHERE Class='$Class' AND Year='$Year'");

			if (count($search) > 0) {

				?>
				<div class="wrap-input">
					<span class="input-for">Filter students for specific <i>Name, Roll No, Marks...</i></span>
					<label for="searchBox" class="labels"></label>
					<input type="search" id="searchBox" placeholder="Search Results" class="fields">
					<span class="focus-input"></span>
				</div>

				<form method='post' action="">
					<div class="educare_print">
						<?php
						echo "<div class='notice notice-success is-dismissible'><p>
							<b>Class:</b> ".esc_html($Class)."<br>
							<b>Exam:</b> ".esc_html($Exam)."<br>
							<b>Subject:</b> ".esc_html($Subject)."<br>
							<b>Exam Year:</b> ".esc_html($Year)."<br>
							<b>Total Students:</b> ".esc_html(count($search))."
						</p><button class='notice-dismiss'></button></div>";
						?>
						<table class="view_results">
							<thead>
								<tr>
									<th>No</th>
									<th>Photos</th>
									<th>Name</th>
									<th>Roll No</th>
									<th>Marks</th>
								</tr>
							</thead>

							<?php
							$count = 1;

							foreach($search as $print) {
								$id = $print->id;
								$name = $print->Name;
								$roll_no = $print->Roll_No;
								$Details = json_decode($print->Details);

								echo '
								<input type="hidden" name="id[]" value="'.esc_attr( $id ).'">
								<input type="hidden" name="Class" value="'.esc_attr( $Class ).'">
								<input type="hidden" name="Exam" value="'.esc_attr( $Exam ).'">
								<input type="hidden" name="Subject" value="'.esc_attr( $Subject ).'">
								<input type="hidden" name="Year" value="'.esc_attr( $Year ).'">
								<tr>
									<td>'.esc_html( $count++ ).'</td>
									<td><img src="'.esc_url($Details->Photos).'" class="student-img" alt="IMG"/></td>
									<td>'.esc_html( $name ).'</td>
									<td>'.esc_html( $roll_no ).'</td>
									<td><input type="number" name="marks[]" value="'.esc_attr( educare_get_marks_by_id($id) ).'" placeholder="'.esc_attr( educare_get_marks_by_id($id) ).'" class="full"></td>
								</tr>
								';
							}
							?>

						</table>
					</div>

					<div class="button_container">
						<input type="submit" name="add_marks" class="educare_button" value="Save Marks">
						<input type="submit" name="publish_marks" class="educare_button" value="Publish">
						<input type="button" id="print" class="educare_button" value="&#xf193 Print">
						<div class="action_menu"><i class="dashicons action_button dashicons-info"></i> <menu class="action_link info"><strong>Mark not visible when print?</strong><br> Please, fill up students marks and save. Then, select <b>Students List</b> and print marksheet (Save then Print).</menu></div>
					</div>
					
				</form>

				<script>
					var perPage = $('#student_per_page').val();
					let options = {
						// How many content per page
						numberPerPage:perPage,
						// anable or disable go button
						goBar:true,
						// count page based on numberPerPage
						pageCounter:true,
					};

					let filterOptions = {
						// filter or search specific content
						el:'#searchBox'
					};

					paginate.init('.view_results',options,filterOptions);
				</script>
				<?php
			} else {
				echo '<div class="notice notice-error is-dismissible"><p> No students found in this class <b>('.esc_html($Class).')</b></p><button class="notice-dismiss"></button></div>';
			}
		}
	}
}


function educare_get_students($id) {
	global $wpdb;
	// Table name
	$educare_students = $wpdb->prefix."educare_students";
	$search = $wpdb->get_row("SELECT * FROM ".$educare_students." WHERE id='$id'");

	if ($search) {
		return $search;
	}
}


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

		$search = $wpdb->get_results("SELECT * FROM ".$educare_marks." WHERE Class='$Class' AND Exam='$Exam' AND Year='$Year'");

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
			$students[$count_students++] = educare_get_students($value);
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
			
			foreach ($details as $key => $value) {
				foreach ($students as $print) {
					if ($print->id == $key ) {
						// $results_id = $print->id;
						$Roll_No = $print->Roll_No;
						$Regi_No = $print->Regi_No;

						// remove id
						unset($print->id);
						unset($print->Others);
						$print->Class = $Class;
						$print->Exam = $Exam;
						$print->Year = $Year;
						$print->Subject = json_encode($value);
						$print->Result = '';
						$print->GPA = '';

						$print = json_encode($print);
						$print = json_decode( $print, TRUE );

						$search_results = $wpdb->get_results("SELECT * FROM ".$educare_results." WHERE Roll_NO='$Roll_No' AND Regi_No='$Regi_No' AND Class='$Class' AND Exam='$Exam' AND Year='$Year'");

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


function educare_get_marks_by_id($id) {
	global $wpdb;
	$educare_marks = $wpdb->prefix."educare_marks";

	$Class = sanitize_text_field($_POST['Class']);
	$Exam = sanitize_text_field($_POST['Exam']);
	$Year = sanitize_text_field($_POST['Year']);
	$Subject = sanitize_text_field($_POST['Subject']);

	$marks = $wpdb->get_results("SELECT * FROM ".$educare_marks." WHERE Class='$Class' AND Exam='$Exam' AND Year='$Year'");

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


add_action('wp_ajax_educare_process_marks', 'educare_process_marks');

function educare_process_marks() {
	$action_for = sanitize_text_field($_POST['action_for']);
	$data_for = sanitize_text_field($_POST['data_for']);
	wp_parse_str($_POST['form_data'], $_POST);
	$_POST[$action_for] = $action_for;
	$_POST['data_for'] = $data_for;

	$Class = sanitize_text_field($_POST['Class']);
	$Exam = sanitize_text_field($_POST['Exam']);
	$Subject = sanitize_text_field($_POST['Subject']);
	$Year = sanitize_text_field($_POST['Year']);

	if (isset($_POST['get_subject'])) {
		educare_get_options_for_subject($Class, $Subject);
	}
	elseif (isset($_POST['publish_marks'])) {
		educare_save_marks(true);
		educare_get_students_list();

		// echo '<pre>';	
		// print_r($_POST);	
		// echo '</pre>';
	} else {
		educare_save_marks();
		educare_get_students_list();
	}

	die;
}




?>