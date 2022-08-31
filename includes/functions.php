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
		7. educare_check_status('name', true); // true because, this is an array
	
	# Above callback function return current status => checked or unchecked
	# Notes: all default status => checked
	
	* @since 1.0.0
	* @last-update 1.2.0
	
	* @param string $target	Select specific key and get value
	* @param bull $display	Select specific key with array
	
	* @return string
	
==================( function for check settings status, )==================*/

function educare_check_status($target, $display = null) {
	
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
   
	$search = $wpdb->get_results("SELECT * FROM $table WHERE list='Settings'");
	
	if ($search) {
		
		foreach ( $search as $print ) {
			$data = $print->data;
			$data = json_decode($data);
			// $id = $print->id;
		}
		
		if ($display) {
			$name = $data->display->$target;
			$value = $name[0];
			$status = $name[1];

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
	* @last-update 1.2.0

	* @return void|HTML
	
===================( function for database error notice )=================== **/

function educare_database_error_notice($fix_form = null) {
	echo '<div class="educare_post">';

	if ($fix_form) {
		echo '<div class="logo"><img src="'.esc_url(EDUCARE_URL."assets/img/educare.svg").'" alt="Educare"/></div>';

		if (isset($_POST['update_educre_database'])) {
			global $wpdb;
			$settings = $wpdb->prefix.'educare_settings';
			$results = $wpdb->prefix.'educare_results';

			$wpdb->query( "DROP TABLE $settings, $results" );
			educare_database_table();
			
			echo "<div class='notice notice-success is-dismissible'><p>Successfully Updated (Educare) Database click here to <a href='".esc_url($_SERVER['REQUEST_URI'])."'>Start</a></p></div>";
		} else {
			?>
			<form class="add_results" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
				<b>Database Update Required</b>
				<p>Your current (Educare) database is old or corrupt, you need to update database to run new version of educare, it will only update <strong>Educare related database</strong>. Click to update database</p>
				<p><strong>Please note:</strong> You should backup your (Educare) database before updating to this new version (only for v1.0.2 or earlier users).</p>
				<button class="button" name="update_educre_database">Update Educare Database</button>
			</form>
			<?php
		}
	} else {
		echo "<div class='notice notice-error is-dismissible'><p>Something went wrong!. Please go to (Educare) settings or <a href='/wp-admin/admin.php?page=educare-settings'>Click here to fix</a></p></div>";
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
	
	* @param string $list			Specific keys value: Subject/Class/Exam/Year/Extra Field...
	* @param string $content	Specific keys value
	* @param string|int $year	Specific keys value

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

/** similar function
	* @param string $guide	  Specific string
	* @param string $details	Specific var/string

	*@return string
*/

function educare_guide_for($guide, $details = null) {
	if (educare_check_status('guide') == 'checked') {
		
		if ($guide == 'add_class') {
			$guide = "Add more <b>Class</b> or <b>Exam</b>. Do you want to add more <b>Class</b> or <b>Exam</b>? Click Here To <a href='".esc_url('/wp-admin/admin.php?page=educare-settings#Class')."' target='_blank'>Add Class</a> or <a href='".esc_url('/wp-admin/admin.php?page=educare-settings#Exam')."' target='_blank'>Add Exam</a>";
		}
		
		if ($guide == 'add_extra_field') {
			$guide = "Do you want to add more <b>Field</b> ? Click Here To <a href='".esc_url('/wp-admin/admin.php?page=educare-settings#Extra_field')."' target='_blank'>Add extra field</a>";
		}
		
		if ($guide == 'add_subject') {
			$guide = "Do you want to add more <b>Subject</b> ? Click Here To <a href='".esc_url('/wp-admin/admin.php?page=educare-settings#Subject')."' target='_blank'>Add Subject</a>";
		}
		
		if ($guide == 'optinal_subject') {
			$guide = "If this student has an optional subject, then select optional subject. otherwise ignore it.<br><b>Note: It's important, when students will have a optional subject</b>";
		}
		
		if ($guide == 'import') {
			$guide = "Notes: Please carefully fill out all the details of your import (<b>.csv</b>) files. If you miss one, you may have problems to import the results. So, verify the student's admission form well and then give all the details in your import files. Required field are: <b><i>Name, Roll No, Regi No, Exam, Class and Year</i></b>. So, don't miss all of this required field!<br><br>If you don't know, how to create import files. Please download the demo files given below.";
		}
		
		if ($guide == 'import_error') {
			$guide = "<div class='notice notice-error is-dismissible'><p>It's not possible to import $details results while during this process. Maybe, that's results field or data is missing. Notes: If you keep any empty field - use comma (,). for example: Your csv files Head like this - <br><b>Name,Roll_No,Regi_No,Class,Exam,Year,Field1,Field2,Field3,Field4,Field5</b><br>You need to get empty (Field1, Field3 and Field4) For that our csv data will be look like - <br> (<font color='green'>Atik,123456,12345678,Class 8,Exam no 2,2022<font color='red'>,,</font>Field2<font color='red'>,,,</font>Field5</font>) not (<font color='red'>Atik,123456,12345678,Class 8,Exam no 2,2022,Field2,Field5</font>)</p></div>
			";
			return $guide;
		}

		if ($guide == 'display_msgs') {
			$guide = "It is not possible to deactivate both (<b>Regi number or Roll number</b>). Because, it is difficult to find students without roll or regi number. So, you need to deactivate one of them (Regi or Roll Number). If your system has one of these, you can select it. Otherwise, it is better to have both selected (<b>Recommended</b>).";
		}

		if ($guide == 'db_error') {
			$guide = "Something went wrong! in your settings. Please fix it. Otherwise some of our plugin settings will be not work. also it's effect your site. So, please contact to your developer for solve this issue. or go to plugin (Educare) settings and press <b>Reset setings</b>. Hope you understand.";
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
	* @last-update 1.2.0
	
	* @param string $list	Select object array
	* @param int $id			Select specific database rows by id
	
	* @return string|int|float|bool / database value
	
==================( function for display result value )==================*/

function educare_value($list, $id, $array = null) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'educare_results';
	
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
	
	* @param string $list		Specific string
	* @param int|string $id	Specific var
	
	* @return string
	
	
	
==================( function for display content options/field )==================*/

function educare_get_options($list, $id, $selected_class = null) {
	
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

				$results = $results[$selected_class];
				
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
					$value = sanitize_text_field(educare_value('Details', $id, $name));
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
					<p>Currently you don't have added any subject in this class (<?php echo esc_html($selected_class);?>). Please add some subject by <?php echo "<a href='".esc_url('/wp-admin/admin.php?page=educare-settings#Subject')."' target='_blank'>Click Here</a>.";?> Thanks </p>
				</div></td>
			</tr>
			<?php
		} else {
			echo "<div class='notice notice-error is-dismissible'><p>You Dont have Added Any ".esc_html($list)."</p></div>";
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
	
	* @param int $id			 	database row id
	* @param object $result	$print object
	
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
					echo "<tr>\n";
				}
					
				echo "<td>".esc_html(str_replace('_', ' ', $key))."</td>\n<td>".esc_html($value)."</td>\n"; 
					
				if ($count%2 == 0) {
					echo "</tr>\n\n";
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
		$default_img = EDUCARE_URL.'assets/img/default.jpg';
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
			$img = EDUCARE_URL.'assets/img/default.jpg';
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
	* @last-update 1.2.0
	
	* @param object $print		Getting object value
	* @param string $submit	Forms action type - Add/Update
	
	* @return null|HTML
	
===================( function for print results forms )===================*/

function educare_get_results_forms($print, $submit) {
	$Class = '';
	if (isset($_POST['edit']) or isset($_POST['edit_by_id'])) {
		$Class = $print->Class;
		$Exam = $print->Exam;
		$Year = $print->Year;
		$id = $print->id;
		$Name = $print->Name;
		$Roll_No = $print->Roll_No;
		$Regi_No = $print->Regi_No;
	} else {
		$selected_class = $selected_exam = $selected_year = $id = $Name = $Roll_No = $Regi_No = '';
		
		if (isset($_POST['Add'])) {
			$Name = sanitize_text_field($_POST['Name']);
			$Class = sanitize_text_field($_POST['Class']);
			$Exam = sanitize_text_field($_POST['Exam']);
			$Roll_No = sanitize_text_field($_POST['Roll_No']);
			$Regi_No = sanitize_text_field($_POST['Regi_No']);
			$Year = sanitize_text_field($_POST['Year']);
		}
	}
	?>
	
	<form class="add_results" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
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
			$chek_name = educare_check_status('name', true);
			$chek_roll = educare_check_status('roll_no', true);
			$chek_regi = educare_check_status('regi_no', true);

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
				
			<select id="Exam" name="Exam" class="fields">
				<?php educare_get_options('Exam', $Exam);?>
			</select>
		</div>
			
		<!-- Extra field -->
		<h2>Others</h2>
		<?php
		echo educare_guide_for('add_extra_field');
		
		if (isset($_POST['Add'])) {
			educare_get_options('Extra_field', 'add');
		} else {
			educare_get_options('Extra_field', $id);
		}
		?>
			
		Select Year:<br>
		<select id="Year" name="Year" class="fields">
			<?php educare_get_options('Year', $Year);?>
		</select>
    	
    
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
	# Display forms for search students results
	
	
	# Search specific results for Edit/Delete/View
	# Search results by Class, Exam, Year, Roll & Regi No for Edit/Delete/View specific results.
	# Admin can Edit/Delete/View the results.
	# Users only view the results.

	* @since 1.0.0
	* @last-update 1.2.0
	
	* @return null|HTML
	
===================( function for search specific results )===================*/

function educare_get_search_forms() {
	$Class = $Exam = $Roll_No = $Regi_No = '';

	if (isset($_POST['edit'])) {
		$Class = sanitize_text_field($_POST['Class']);
		$Exam = sanitize_text_field($_POST['Exam']);
		$Roll_No = sanitize_text_field($_POST['Roll_No']);
		$Regi_No = sanitize_text_field($_POST['Regi_No']);
		$Year = sanitize_text_field($_POST['Year']);
	}
	
	?>
	<form class="add_results" action="" method="post" id="edit">
	<div class="content">
		<?php 
			$chek_roll = educare_check_status('roll_no', true);
			$chek_regi = educare_check_status('regi_no', true);

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
		?>
		
		<div class="select">
			<label for="Class" class="labels" id="class"></label>
			<label for="Exam" class="labels" id="exam"></label>
		</div>
		<div class="select">

			<select id="Class" name="Class" class="form-control">
				<?php
				/** 
				echo '<option value="0">Select Class</option>';
				
				$options = array(
				'Class 6' => 'Class 6',
				'Class 7' => 'Class 7',
				'Class 8' => 'Class 8',
				'Class 9' => 'Class 9',
				'Class 10' => 'Class 10'
				// ....
				);
				
				foreach ( $options as $class_list) {
					echo '<option value="'.esc_attr($class_list).'" >'.esc_html($class_list).'</option>';
				}
				*/
			
				educare_get_options('Class', $Class);
				?>
			</select>

			<select id="Exam" name="Exam" class="fields">
				<?php educare_get_options('Exam', $Exam);?>
			</select>

		</div>
		
		<p>Select Year:</p>
		<select id="Year" name="Year" class="fields">
			<?php educare_get_options('Year', $Year);?>
		</select>

		<br>
		
		<button id="edit_btn" name="edit" type="submit" class="educare_button"><i class="dashicons dashicons-search"></i> Search for edit</button>
	</div>
	</form>
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
	* @last-update 1.2.0
	
	* @return mixed
	
===================( function for Saving forms data )===================*/

function educare_save_results() {
	
	// print error/success notice
	function notice($x, $print = null ) {
		$Class = sanitize_text_field($_POST['Class']);
		$Exam = sanitize_text_field($_POST['Exam']);
		$Roll_No = sanitize_text_field($_POST['Roll_No']);
		$Regi_No = sanitize_text_field($_POST['Regi_No']);
		$Year = sanitize_text_field($_POST['Year']);
		$id = '';
		
		ob_start();
		educare_confirmation('Result', 'this result');
		$confirm = ob_get_clean();
		$chek_name = educare_check_status('name', true);
		$chek_roll = educare_check_status('roll_no', true);
		$chek_regi = educare_check_status('regi_no', true);
		
		if ($x == 'updated') {
			$id = sanitize_text_field($_POST['id']);
		}
		if ($x == 'exist' or $x == 'added') {
			$id = $print->id;
		}
			
		$forms = "<form method='post' action='/".educare_check_status('results_page')."' class='text_button' target='_blank'>
			<input name='id' value='".esc_attr($id)."' hidden>
			<input  type='submit' name='educare_results_by_id' class='educare_button' value='&#xf177'>
		</form>
		
		<form method='post' action='/wp-admin/admin.php?page=educare-update-results' class='text_button'>
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
			
			echo "
			<div class='notice notice-success is-dismissible'>\n
				<p>Successfully ".esc_html($x)." <b>".esc_html($Name)."</b> Result for his <b>".esc_html($Exam)."</b><br>\n
				Class: <b>".esc_html($Class)."</b>".wp_kses_post($roll)."".wp_kses_post($regi)."
				
				\n\n
				
				$forms
				</p>
			</div>
			";
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

			echo "<div class='notice notice-error is-dismissible'><p>Sorry, Results is allready exist. You are trying to add duplicate results. It's not possible to add duplicate results. Because, ".esc_html($Name)."'s ".esc_html($Exam)." Result is already added in this Roll (".esc_html($Roll_No).") & Regi No (".esc_html($Regi_No)."). Please update or delete old results then add a new one.</p>
			
			\n\n
			$forms
			\n
			</div>";
		}
		
		if ($x == 'not_found') {
			echo "<div class='notice notice-error is-dismissible'><p>Sorry, results not found. Please try again</p></div>";
		}
	}
	
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'educare_results';
	
	$chek_roll = educare_check_status('roll_no', true);
	$chek_regi = educare_check_status('regi_no', true);

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
			$Class = sanitize_text_field($_POST['Class']);
			$Exam = sanitize_text_field($_POST['Exam']);
			$Roll_No = sanitize_text_field($_POST['Roll_No']);
			$Regi_No = sanitize_text_field($_POST['Regi_No']);
			$Year = sanitize_text_field($_POST['Year']);

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
			
			$select = "SELECT * FROM $table_name WHERE Class='$Class' AND Exam='$Exam' $roll $regi AND Year='$Year'";
			
			$results = $wpdb->get_results($select);
		}
		
		if ($results) {
			foreach($results as $print) {
				if (isset($_POST['Add'])) {
					notice('exist', $print);
				}
				
				// if results exist display update forms when call edit/edit_by_id
				if (isset($_POST['edit']) or isset($_POST['edit_by_id'])) {
					educare_get_results_forms($print, 'update');
		    }
			}
	
		} else { //if ($results)
			
			if(!empty($Class) && !empty($Exam) && !empty($Roll_No) && !empty($Regi_No) && !empty($Year) ) {
				
				if (isset($_POST['edit']) or isset($_POST['edit_by_id'])) {
					notice('not_found');
					educare_get_search_forms();
				}
				
				if (isset($_POST['Add'])) {
					$Name = sanitize_text_field($_POST['Name']);
					$Result = sanitize_text_field($_POST['Result']);
					$GPA = sanitize_text_field($_POST['GPA']);
					$Photos = sanitize_text_field($_POST['Photos']);

					$Details = educare_array_slice($_POST, 'Exam', 'Year');
					$Details['Photos'] = $Photos;
					$Details = json_encode($Details);
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

					// echo '<pre>';	
					// print_r($data);	
					// echo '</pre>';
					
					$wpdb->insert($table_name, $data);
				    
					if($wpdb->insert_id > 0) {
						$results = $wpdb->get_results($select);
						
						if (isset($_POST['Add'])) {
							foreach($results as $print) {
								notice('added', $print);
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
					educare_get_search_forms();
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
		if(!empty($Class) && !empty($Exam) && !empty($Roll_No) && !empty($Regi_No) && !empty($Year) ) {
			
			// Compare new to old results
			$class = educare_value('Class', $id);
			$exam = educare_value('Exam', $id);
			$roll_no = educare_value('Roll_No', $id);
			$regi_no = educare_value('Regi_No', $id);
			$year = educare_value('Year', $id);

			$Name = sanitize_text_field($_POST['Name']);
			$Result = sanitize_text_field($_POST['Result']);
			$GPA = sanitize_text_field($_POST['GPA']);
			$Photos = sanitize_text_field($_POST['Photos']);

			// $Roll_No = sanitize_text_field($_POST['Roll_No']);
			// $Regi_No = sanitize_text_field($_POST['Regi_No']);

			$Details = educare_array_slice($_POST, 'Exam', 'Year');
			$Details['Photos'] = $Photos;
			$Details = json_encode($Details);
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
				notice('updated');
	
			} else {
				if (!$results) {
					$wpdb->update($table_name, $data, array('ID' => $id));
					notice('updated');
				} else {
					
					foreach($results as $print) {
						notice('exist', $print);
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
	* @last-update 1.2.0

	* @return mised/create a files
	
===================( function for import demo )===================*/

add_action('wp_ajax_educare_demo', 'educare_demo');

function educare_demo() {
	$Class = educare_demo_data('Class');
	$Exam = array_rand(educare_demo_data('Exam'), 1);
  $Exam = educare_demo_data('Exam')[$Exam];
	// $Year = educare_demo_data('Year');
	$Year = date("Y");
	$Extra_field = educare_demo_data('Extra_field');

	$selected_class = sanitize_text_field($_POST['class']);
	$Subject = $Class->$selected_class;

	if ($Subject) {
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

		$chek_roll = educare_check_status('roll_no', true);
		$chek_regi = educare_check_status('regi_no', true);
		$chek_name = educare_check_status('name', true);

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

		$data['Result'] = 'Passed';
		$data['GPA'] = number_format((float)$GPA, 1, '.', '');

		foreach ($Subject as $value) {
			// remove field type
			$data[$value] = rand(33, 99);
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
		// Save data as a file (import_demo.csv)

		$demo_file = file_get_contents(EDUCARE_DIR."assets/files/import_demo.csv");
		// for store (save) database status to a files
		$file_dir = EDUCARE_DIR."assets/files/import_demo.csv";
		
		// check if data is already exist/same or not. if data not exist or old, then update data. otherwise ignore it.
		if (!($data == $demo_file)) {
			// process to update data
			if ( !file_exists("data") );
			// update data if any changed found
			$update_data = fopen($file_dir, 'w'); 
			fwrite($update_data, $data);
			fclose($update_data);
		}

		echo "<div class='notice notice-success is-dismissible'><p>Successfully generated demo files for your selected class (<b>".esc_html( $selected_class )."</b>)</p></div>";

		echo "<p><strong>Notes:</strong> This is an example of importing a demo.csv file, based on your current settings (Class, Subject, Additional fields...). If you make any changes to educare (plugin) settings, this demo file may not work. For this you need to create this file again! And if you get error or face any problem while downloading the file, you can manually get this file in dir: <p>".esc_html( $file_dir )."</p><br>";
	
		echo "<p><a class='educare_button' href='".esc_url(EDUCARE_URL.'assets/files/import_demo.csv')."' title='Download Import Demo.csv'><i class='dashicons dashicons-download'></i> Download Demo</a></p>";

	} else {
		$file_dir = EDUCARE_DIR."assets/files/import_demo.csv";

		$update_data = fopen($file_dir, 'w'); 
		fwrite($update_data, '');
		fclose($update_data);

		$url = esc_url('/wp-admin/admin.php?page=educare-settings');

		if (!$selected_class) {
			echo "<div class='notice notice-error is-dismissible'><p>Pleace select a valid class</p></div>";
		} else {
			echo "<div class='notice notice-error is-dismissible'><p>Currently you don't have added any subject in this class (<b>".esc_html( $selected_class )."</b>). Please add some subject by <a href='".esc_url( $url.'#'.$selected_class )."' target='_blank'>Click Here</a>. Thanks </p></div>";
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
	
===================( function for import demo )===================*/

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
	<div class="notice notice-success"><p>
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
	</p></div>

  <script type="text/javascript"><?php echo esc_js( 'cloneField()' );?></script>
	<?php

	die;
}

?>