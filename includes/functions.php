<?php

/* Include admin menu
	# View results
	# Add results
	# Import results
	# Update results
	# Settings
	# About us
*/
require_once(EDUCARE_ADMIN.'menu.php');


/*=====================( Functions Details )======================
	
	* Usage: educare_esc_str($string);
	
	* @param string $str	The string to be escaped.
	* @return The escaped string.
	
==================( function for escaped string, )==================*/

function educare_esc_str($str) {
	$str = preg_replace("/[^A-Za-z0-9 _.]/",'',$str);
	// One more protection with WP esc_attr()
	$str = esc_attr($str);
	return $str;
}



/*=====================( Functions Details )======================
	
	* Usage example: educare_check_status('confirmation');
	# For checking settings status, if specific settings is anable return{checked}. or disabled return{unchecked}.
	
	
	# Cunenty there are 6 settings status support
			
		Name			   			Default	  		Details
		1. confirmation 	 	checked			/ for delete confirmation
		2. guide			  	 	checked			/ for helps (guidelines) pop-up
		3. photos 	 			   checked			/ for students photos
		4. auto_results 	 	 checked			/ for auto results calculation
		5. delete_subject		checked			/ for delete subject with results
		6. clear_field 		 	 checked			/ for delete extra field with results
		
		
	for check current status =>
		1. educare_check_status('confirmation');
		2. educare_check_status('guide');
		3. educare_check_status('photos');
		4. educare_check_status('auto_results');
		5. educare_check_status('delete_subject');
		6. educare_check_status('clear_field');
	
	# Above callback function return current status => checked or unchecked
	# Notes: all default status => checked
	
	* @param string $target	Select specific key and get value
	
	* @return string
	
==================( function for check settings status, )==================*/

function educare_check_status($target) {
	
	global $wpdb;
	$table = $wpdb->prefix."Educare_settings";
   
	$search = $wpdb->get_results("SELECT * FROM $table WHERE list='Settings'");
	
	if ($search) {
		
		foreach ( $search as $print ) {
			$data = $print->data;
			$data = json_decode($data);
			// $id = $print->id;
		}
		
		// return $status = $data->$target;
		return $data->$target;
		
	}
}



/*=====================( Functions Details )======================
	
	* Usage example: educare_confirmation($list, $content);
	# Pop-up delete/remove confirmation if {confirmation} status is => checked.
	
	
	# for example, when users delete/remove a Subject, like - Science. this function pop-up (alart) the user like this - You want to remove 'Science' from the 'Subject' list. Are you sure?.
	
	# Simple but powerful!
	
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
			$guide = "If this student has an optional subject, then select optional subject. otherwise ignore it.<br><b>Note: It's important, when students will have a optional subjects</b>";
		}
		
		if ($guide == 'import') {
			$guide = "Notes: Please carefully fill out all the details of your import (<b>.csv</b>) files. If you miss one, you may have problems to import the results. So, verify the student's admission form well and then give all the details in your import files. Required field are: <b><i>Name, Roll No, Regi No, Exam, Class and Year</i></b>. So, don't miss all of this required field!<br><br>
If you don't know, how to create import files. Please download the demo files given below.";
		}
		
		if ($guide == 'import_error') {
			$guide = "<div class='notice notice-error is-dismissible'><p>It's not possible to import $details results while during this process. Maybe, that's results field or data is missing. Notes: If you keep any empty field - use comma (,). for example: Your csv files Head like this - <br><b>Name,Roll_No,Regi_No,Class,Exam,Year,Field1,Field2,Field3,Field4,Field5</b><br>You need to get empty (Field1, Field3 and Field4) For that our csv data will be look like - <br> (<font color='green'>Atik,123456,12345678,Class 8,Exam no 2,2022<font color='red'>,,</font>Field2<font color='red'>,,,</font>Field5</font>) not (<font color='red'>Atik,123456,12345678,Class 8,Exam no 2,2022,Field2,Field5</font>)</p></div>
			";
			return $guide;
		}
		
		return "<div class='notice notice-success is-dismissible'><p>".wp_kses_post($guide)."</p></div>";
	}
}



/*=====================( Functions Details )======================
	
	* Usage example: educare_value('Bangla', 1);
	# display result value.
	
	
	# Simple but super power!
	# Without this function result system is gone!!!!!
	
	* @param string $list	Select object array
	* @param int $id			Select specific database rows by id
	
	* @return string|int|float|bool / database value
	
==================( function for display result value )==================*/

function educare_value($list, $id) {
	global $wpdb;
    $table_name = $wpdb->prefix . 'Educare_results';
    
    $educare_results = $wpdb->get_results("SELECT * FROM $table_name WHERE id='$id'");
	
    if ($educare_results) {
        foreach($educare_results as $print) {
        	return $print->$list;
        }
	}
}



/*=====================( Functions Details )======================
	
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
	
	* @param string $list		Specific string
	* @param int|string $id	Specific var
	
	* @return string
	
	
	
==================( function for display content options/field )==================*/


function educare_get_options($list, $id) {
	
	global $wpdb;
	$table = $wpdb->prefix."Educare_settings";
	
	
	if ($list != 'optinal') {
		$results = $wpdb->get_results("SELECT * FROM $table WHERE list='$list'");
	} else {
		$results = $wpdb->get_results("SELECT * FROM $table WHERE list='Subject'");
	}
	
	if ($results) {
		foreach ( $results as $print ) {
			$results = $print->data;
			// $subject = ["Class", "Regi_No", "Roll_No", "Exam", "Name"];
			$results = json_decode($results);
			$results = str_replace(' ', '_', $results);
		}
	}
	
	$serial = 0;
	foreach ( $results as $print ) {
		$display = str_replace('_', ' ', $print);
		$name = $print;
		$type = $print;
		
		if ($list == 'Extra_field') {
			$display = substr(strstr($display, ' '), 1);
			$name = str_replace(' ', '_', $display);
			
			/*
			if (isset($_POST['educare_results'])) {
				$value = sanitize_text_field($_POST[$name]);
			} else {
				$value = sanitize_text_field(educare_value($name, $id));
			}
			*/
			
			if ($id == 'add') {
				$value = sanitize_text_field($_POST[$name]);
			} else {
				$value = sanitize_text_field(educare_value($name, $id));
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
			$value = sanitize_text_field(educare_value($name, $id));
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
			
			?>
			<tr>
				<td><?php echo esc_html($serial+=1);?></td>
				<td><?php echo esc_html($display);?></td>
				<td><label for="<?php echo esc_attr($name);?>" class="mylabels" id="<?php esc_attr($name);?>"></label>
	<input id="<?php echo esc_attr($name);?>" type="number" name="<?php echo esc_attr($name);?>" class="myfields" value="<?php echo esc_attr($value);?>" placeholder="<?php echo esc_attr("$value $placeholder");?>"></td>
				<td>Auto</td>
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
}



/*=====================( Functions Details )======================
	
	* Usage example: educare_get_data($get, $id, $result); || educare_get_data('all', '', '');
	
	#
	
	* @param string $get		 Select array key
	* @param array $id			Insert array
	* @param object $result	$print object
	
	* @return array
	
===================( function for getting data )===================*/


function educare_get_data($get, $id, $result) {
	
	global $wpdb;
	$table = $wpdb->prefix."Educare_settings";
   
	$subject = $wpdb->get_results("SELECT * FROM $table WHERE list='Subject'");
	
	$extra_field = $wpdb->get_results("SELECT * FROM $table WHERE list='Extra_field'");
	
	$default_data = array('Name', 'Roll_No', 'Regi_No', 'Class', 'Exam', 'Year');
	$default_end = array('Photos', 'Result', 'GPA');
	
	
	if ($subject) {
		foreach ( $subject as $print ) {
			$subject = $print->data;
			// $subject = ["Class", "Regi_No", "Roll_No", "Exam", "Name"];
			$subject = json_decode($subject);
			$subject = str_replace(' ', '_', $subject);
		}
	}
	
	if ($extra_field) {
		foreach ( $extra_field as $print ) {
			$extra_field = $print->data;
			$extra_field = json_decode($extra_field);
			$extra_field = str_replace(' ', '_', $extra_field);
		}
	}
	
	if ($get == 'all' or $get == 'import' or $get == 'all_content') {
		$all = $default_data;
		// add default data
		foreach ( $extra_field as $add ) {
			array_push($all, substr(strstr($add, '_'), 1));
		}
		// add default end data
		foreach ( $default_end as $add ) {
			array_push($all, $add);
		}
		// add subject data
		foreach ( $subject as $add ) {
			array_push($all, $add);
		}
	}
	
	if ($get == 'all_content' ) {
		$data = $all;
	}
	
	if ($get == 'all' ) {
		$value = array();
		foreach ($all as $val) {
			$value[]= sanitize_text_field($_POST[$val]);
			// for preview
			// $value[]= sanitize_text_field('$_POST["'.$val.'"]');
		}
		$data = array_combine($all, $value);
	}
	
	if ($get == 'import') {
		$value = array();
		$conut = 0;
		foreach ($all as $val) {
			$value[$val]= trim($id[$conut++]);
		}
		$data = $value;
	}
	
	if ($get == 'import_demo') {
		ob_start();
		$all = $default_data;
		$content = array('Atik', '123456', '12345678', 'Class 8', 'Exam no 2', '2022' );
		// add default data
		foreach ( $extra_field as $add ) {
			array_push($all, substr(strstr($add, '_'), 1));
			
			$text = substr(strstr(str_replace('_', ' ', $add), ' '), 1);
			$type = strtok($add, '_');
			if ($type == 'text') {
				$add = $text;
			}
			if ($type == 'number') {
				$add = '01760000000';
			}
			if ($type == 'date') {
				$add = '2022-08-15';
			}
			if ($type == 'email') {
				$add = 'students-mail@gmail.com';
			}
			
			array_push($content, $add);
		}
		
		// add default end data
		foreach ( $default_end as $add ) {
			array_push($all, $add);
			if ($add == 'Photos') {
				// $add = 'URL';
				$add = EDUCARE_URL.'assets/img/default.jpg';
			}
			if ($add == 'Result') {
				$add = 'Passed';
			}
			if ($add == 'GPA') {
				$add = '5.00';
			}
			array_push($content, $add);
		}
		
		// add subject data
		foreach ( $subject as $add ) {
			array_push($all, $add);
			$add = '80';
			array_push($content, $add);
		}
		
		// now print our csv files data
		
		ob_start();
		foreach ( $all as $head ) {
			echo esc_html("$head,");
		}
		$head = substr(ob_get_clean(),0,-1);
		
		ob_start();
		foreach ( $content as $cont ) {
			if(preg_match ('/http/', $cont)) {
				echo '"'.esc_url($cont).'",';
			} else {
				echo esc_html("$cont,");
			}
		}
		$cont = substr(ob_get_clean(),0,-1);
		
		$data = "$head\n$cont";
	}
	
	
	if ($get == 'Extra_field') {
		
		ob_start();
		$count = 1; // for add specific tags (div/tr/ul) in every 4 foreach loop
		
		foreach ($extra_field as $print) {
			$print = substr(strstr($print, '_'), 1);
			$display = str_replace('_', ' ', $print);
			$value = educare_value($print, $id);
			
			if ($count%2 == 1) {  
		         echo "<tr>\n";
		    }
				
				echo "<td>".esc_html($display)."</td>\n<td>".esc_html($value)."</td>\n"; 
				
			if ($count%2 == 0) {
		        echo "</tr>\n\n";
		    }
		
		    $count++;
		
		}
		
		$data = ob_get_clean();
	}
	
	if ($get == 'Subject') {
		
		ob_start();
		$serial = 1;
		$count = 1; // for add specific tags (div/tr/ul) in every 4 foreach loop
		
		foreach ($subject as $print) {
			$display = str_replace('_', ' ', $print);
			$value = educare_value($print, $id);
			
				echo "<tr><td>".esc_html($serial++)."</td><td>".esc_html($display)."</td><td>".educare_show_marks($result, $print)."</td><td>".educare_letter_grade($result, $print)."</td></tr>"; 
		
		    $count++;
		
		}
		
		$data = ob_get_clean();
	}
	
	return $data;
}



/*=====================( Functions Details )======================
	
	* Usage example: educare_files_selector($type, $print)
	# Access WP gallery for upload/import students photos
	
	
	# educare_files_selector('add_results', '');
	# educare_files_selector('add_results', '$print'); for update selected photos
	
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
			$( '#educare_attachment_preview' ).attr( 'src', '<?php echo esc_url($default_img);			
?>' );
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



/*=====================( Functions Details )======================
	
	* Usage example: educare_get_results_forms($print, 'add/update')
	# print students results forms for add/update/delete students results
	
	
	# it's only print forms field (Name, Class, Exam, Roll No, Regi No, Year...)
	# required educare_save_results() function for work properly
	# Actually, this function only for print forms under educare_save_results();
	
	* @param object $print		Getting object value
	* @param string $submit	Forms action type - Add/Update
	
	* @return null|HTML
	
===================( function for print results forms )===================*/


function educare_get_results_forms($print, $submit) {
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
			educare_files_selector('update', $print);
			echo "<input type='hidden' name='id' value='".esc_attr($id)."'/>";
		} else {
			educare_files_selector('add_results', '');
		}
		?>
        
        <h2>Students Details</h2>
        
        <?php echo educare_guide_for('add_class');?>
        
        <div class="select">
			<label for="Class" class="labels" id="class"></label>
			<label for="Exam" class="labels" id="exam"></label>
		</div>
								
    	<div class="select">
		<select id="Class" name="Class" class="form-control">
			<?php educare_get_options('Class', $Class);?>
		</select>
			
		<select id="Exam" name="Exam" class="fields">
			<?php educare_get_options('Exam', $Exam);?>
		</select>
		</div>
		
		Name:
		<label for="Name" class="labels" id="name"></label>
    	<input type="text" name="Name" value="<?php echo esc_attr($Name);?>" placeholder="Student Name">
		
		Roll No:
		<label for="Roll_No" class="labels" id="roll_no"></label>
		<input type="number" id="Roll_No" name="Roll_No" class="fields" value="<?php echo esc_attr($Roll_No);?>" placeholder="Enter Students Roll">
		
		Reg No:
		<label for="Regi_No" class="labels" id="regi_no"></label>
		<input type="number" id="Regi_No" name="Regi_No" class="fields" value="<?php echo esc_attr($Regi_No);?>" placeholder="Enter Students Registration no">
			
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
			?>
	    	<div id='disabled_field' class="select">
	    	<select id="disabled_Result" disabled>
	    		<option value="0">Results: Auto</option>
	    	</select><br>
	    	
	    	<input type="number" id="disabled_GPA" placeholder="GPA - Auto" disabled>
	    	</div>
	    
	    	<input type="hidden" name="Result" value="<?php echo esc_attr(educare_value('Result', $id));?>">
		    <input type="hidden" name="GPA" value="<?php echo esc_attr(educare_value('GPA', $id));?>">
	    	<?php
		} else {
			?>
			<div class="select">
	    	<label for="Result" class="labels" id="result"></label>
	    	<select name="Result" class="fields">
				<?php if (isset($_POST['Add'])) { echo '<option>Select Status</option>'; }?>
	    		<option value="Passed" <?php if (educare_value('Result', $id) == 'Passed') { echo 'Selected'; }?>>Passed</option>
	    		<option value="Failed" <?php if (educare_value('Result', $id) == 'Failed') { echo 'Selected'; }?>>Failed</option>
	    	</select><br>
	    	
	    	<label for="GPA" class="labels" id="gpa"></label>
	    	<input type="number" name="GPA" class="fields" value="<?php echo esc_attr(educare_value('GPA', $id));?>" placeholder="0.00">
	    	</div>
			<?php
		}
		?>
		
    	
    	<h4>Grade Sheet</h4>
    	<?php echo educare_guide_for('add_subject');?>
    	
		<table class="grade_sheet">
			
			<thead>
				<tr>
					<th>No</th>
					<th>Subject</th>
					<th>Point</th>
					<th>Grade</th>
				</tr>
			</thead>
			
			<tbody>
				<?php 
				if (isset($_POST['Add'])) {
					educare_get_options('Subject', 'add');
				} else {
					educare_get_options('Subject', $id);
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
				educare_get_options('optinal', 'add');
			} else {
				educare_get_options('optinal', $id);
			}
			
			?>
		</select>
		
		<input id="optional" type="text" hidden>
		
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



/*=====================( Functions Details )======================
	
	* Usage example: educare_get_search_forms();
	# Display forms for search students results
	
	
	# Search specific results for Edit/Delete/View
	# Search results by Class, Exam, Year, Roll & Regi No for Edit/Delete/View specific results.
	# Admin can Edit/Delete/View the results.
	# Users only view the results.
	
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
        <h2>Students Details</h2>
        <div class="select">
        	<label for="Class" class="labels" id="class"></label>
        	<label for="Exam" class="labels" id="exam"></label>
        </div>
        <div class="select">
	        <select id="Class" name="Class" class="form-control">
		        <?php
					/*
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
        
        Roll No:
        <label for="Roll_No" class="labels" id="roll_no"></label>
        <input type="number" id="Roll_No" name="Roll_No" class="fields" value="<?php echo esc_attr($Roll_No);?>" placeholder="Enter Students Roll">
        
        Reg No:
        <label for="Regi_No" class="labels" id="regi_no"></label>
        <input type="number" id="Regi_No" name="Regi_No" class="fields" value="<?php echo esc_attr($Regi_No);?>" placeholder="Enter Students Registration no">
        	
        Select Year:<br>
		<select id="Year" name="Year" class="fields">
			<?php educare_get_options('Year', $Year);?>
		</select>
         <br>
        <button id="edit_btn" name="edit" type="submit" class="educare_button"><i class="dashicons dashicons-search"></i> Search for edit</button>
        
    </form>
	<?php
}




/*=====================( Functions Details )======================
	
	* Usage example: educare_save_results();
	# Processing students results forms
	
	
	# Add/Edit/Delete results forms processor
	# Main function for modify (Add, Edit, Delete) students results
	
	* @return null|HTML
	
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
			<input type='submit' name='delete' class='educare_button' value='&#xf182' ".$confirm.">
		</form>";
		
		if ($x == 'added' or $x == 'updated') {
			$Name = sanitize_text_field($_POST['Name']);
			
			echo "
			<div class='notice notice-success is-dismissible'>\n
				<p>Successfully ".esc_html($x)." <b>".esc_html($Name)."</b> Result for his <b>".esc_html($Exam)."</b><br>\n
				Class: <b>".esc_html($Class)."</b><br>\n
				Roll No: <b>".esc_html($Roll_No)."</b><br>\n
				Reg No: <b>".esc_html($Regi_No)."</b>
				
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
			
			// notify if empty Roll No
			if (empty($Roll_No) ) {
        		echo '<b>Roll No</b>, ';
			}
			
			// notify if empty Reg No
			if (empty($Regi_No) ) {
        		echo '<b>Reg No</b>, ';
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
	$table_name = $wpdb->prefix . 'Educare_results';
	
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
			
			$select = "SELECT * FROM $table_name WHERE Class='$Class' AND Exam='$Exam' AND Roll_No='$Roll_No' AND Regi_No='$Regi_No' AND Year='$Year'";
			
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
					// for manually
					// include(INC.'database/data_Extra_field.php');
					$data = educare_get_data('all', '', '');
					
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
				notice('empty');
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
				
			/*
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
			/*
			$data = array (
				'Name' => sanitize_text_field($_POST['Name']),
				'Regi_No' => sanitize_text_field($_POST['Regi_No']),
		        'Roll_No' => sanitize_text_field($_POST['Roll_No']),
		        'Exam' => sanitize_text_field($_POST['Exam']),
		        'Class' => sanitize_text_field($_POST['Class'])
			)
			*/
			
			// Auto generated data
			$data = educare_get_data('all', '', '');
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


?>