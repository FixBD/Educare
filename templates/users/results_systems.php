<?php

/*=====================( Functions Details )======================

	# Front end educare results system

	# Usage:
		* in WordPress editor = [educare_results]
		* in PHP files = <?php echo do_shortcode( '[educare_results]' ); ?>

	# Futures:
		1. viewers can find result by Name, Registration number, Roll Number, Exam, Passing year.
		2. Results Table
		3. field validation
		4. Error Notice
		5. Profiles photos
		6. Print Results
		etc...
		
		* @since 1.0.0

==================( function for Results Shortcode )==================*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Create shortcode fo Educare results
add_shortcode('educare_results', 'educare_get_results' );

function educare_get_results() {    
	ob_start();
	global $wpdb;
	$table_name = $wpdb->prefix . 'Educare_results';
	$educare_results = "";
	echo '<div class="educare_results">';
	
	// Results Functions
	$not_found = $errmsg = ''; // only for results forms
	$Class = $Exam = $Roll_No = $Regi_No = $Year = '';
	if (isset($_POST['educare_results']) or isset($_POST['educare_results_by_id'])) {
	
	if (isset($_POST['educare_results'])) {
		$Class = sanitize_text_field($_POST['Class']);
		$Exam = sanitize_text_field($_POST['Exam']);
		$Roll_No = sanitize_text_field($_POST['Roll_No']);
		$Regi_No = sanitize_text_field($_POST['Regi_No']);
		$Year = sanitize_text_field($_POST['Year']);
		
		$educare_results = $wpdb->get_results("SELECT * FROM $table_name WHERE Class='$Class' AND Exam='$Exam' AND Roll_No='$Roll_No' AND Regi_No='$Regi_No' AND Year='$Year'");
	}
	
	/*
	Take one more step for,
	# Sometimes students/hacker can (force) access others results by database id.
	Notes: when we (admin) view results by id we don't need Class,  Exam, Year Roll & Regi No, just directly select your desired results by the id. if we don't take this step, anyone/hacker can access results by id.
	*/
	// get results by id (only for admin)
	if (isset($_POST['educare_results_by_id'])) {
		// check if users is admin or not
		if ('is_admin') {
			// if user is admin, then access results by id
			$id = sanitize_text_field($_POST['id']);
			$educare_results = $wpdb->get_results("SELECT * FROM $table_name WHERE id='$id'");
		} else {
			// if users is hacker/someone else
			$educare_results = '';
		}
	}
	
	$serial = 0;
	foreach($educare_results as $print) {
		
		$id = $print->id;
		
		/*
		* Create function {educare_letter_grade} for letter grade = A+, A, B, C, D and F (failed).
		
		* @since 1.0.0
		
		* @param object $print		Select specific subject
		* @param string $subject	Select specific name
		
		* @return string
		*/
		function educare_letter_grade($print, $subject){
			$marks = $print->$subject;
			$optional_marks = strtok($marks, ' ');
			
			if ($marks >= 80 and $marks <= 100) {
			    $marks = 'A+';
			}
			elseif ($marks >= 70 and $marks <= 79) {
			    $marks = 'A';
			}
			elseif ($marks >= 60 and $marks <= 69) {
			    $marks = 'A-';
			}
			elseif ($marks >= 50 and $marks <= 59) {
			    $marks = 'B';
			}
			elseif ($marks >= 40 and $marks <= 49) {
			    $marks = 'C';
			}
			elseif ($marks >= 33 and $marks <= 39) {
			    $marks = 'D';
			}
			
			// Getting optional marks
			elseif ($optional_marks = true) {
				
				$optional = substr(strstr($marks, ' '), 1);
				
				if ($optional >= 80 and $optional <= 100) {
			    	$marks = 'A+';
				}
				elseif ($optional >= 70 and $optional <= 79) {
				    $marks = 'A';
				}
				elseif ($optional >= 60 and $optional <= 69) {
				    $marks = 'A-';
				}
				elseif ($optional >= 50 and $optional <= 59) {
				    $marks = 'B';
				}
				elseif ($optional >= 40 and $optional <= 49) {
				    $marks = 'C';
				}
				elseif ($optional >= 33 and $optional <= 39) {
				    $marks = 'D';
				}
				else {
					$marks = 'F';
				}
				
			} else {
				$marks = 'F';
			}
			
			// print success or failed
			if ($marks == 'F') {
				$marks = '<div class="failed">'.esc_html($marks).'</div>'; // direct call
			}
			else {
				$marks = '<div class="success">'.esc_html($marks).'</div>'; // direct call
			}
			return $marks;
			
		}
		
		
		
		/*
		# create function {educare_point_grade} for set points value = 5, 4, 3.5, 2, 1 and 0.
		here,
		5 = A+,
		4 = A Grade,
		3.5 = A-,
		3 = B Grade,
		2 = C Grade,
		1 = D Grade,
		0 = F (Failed)
		
		* @since 1.0.0
		
		* @param object $print	Print specific subject value
		* @param float $id			Specific subject (database) id
		
		* @return int|float
		
		*/
		
		function educare_point_grade($print, $id){
			$marks = educare_value($print, $id);
			$optional_marks = strtok($marks, ' ');
			
			if ($marks >= 80 and $marks <= 100) {
			    $marks = '5';
			}
			elseif ($marks >= 70 and $marks <= 79) {
			    $marks = '4';
			}
			elseif ($marks >= 60 and $marks <= 69) {
			    $marks = '3.5';
			}
			elseif ($marks >= 50 and $marks <= 59) {
			    $marks = '3';
			}
			elseif ($marks >= 40 and $marks <= 49) {
			    $marks = '2';
			}
			elseif ($marks >= 33 and $marks <= 39) {
			    $marks = '1';
			}
			
			// Getting optional marks
			elseif ($optional_marks == 1) {
				
				$optional = substr(strstr($marks, ' '), 1);;
				
				if ($optional >= 80 and $optional <= 100) {
			    	$marks = '1 5';
				}
				elseif ($optional >= 70 and $optional <= 79) {
				    $marks = '1 4';
				}
				elseif ($optional >= 60 and $optional <= 69) {
				    $marks = '1 3.5';
				}
				elseif ($optional >= 50 and $optional <= 59) {
				    $marks = '1 3';
				}
				elseif ($optional >= 40 and $optional <= 49) {
				    $marks = '1 2';
				}
				elseif ($optional >= 33 and $optional <= 39) {
				    $marks = '1 1';
				}
				else {
					$marks = '1 0';
				}
				
			} // Optional marks
			
			else {
				$marks = '0';
			}
			return $marks; // return the value. #Notes: don't call directly 'echo'.
		}
		
		
		
		/* 
		# usage: educare_get_marks($print);
		
		* @since 1.0.0
		
		* @param object $print	Print specific subject value
		
		* @return int
		*/
		function educare_get_marks($print) {
			
			global $wpdb;
			$table_name = $wpdb->prefix . 'Educare_settings';
			
			$id = $print->id;
			
			// Fetch records
			$results = $wpdb->get_results("SELECT * FROM $table_name WHERE list='Subject'");
			
			if ($results) {
				foreach ( $results as $print ) {
					$results = $print->data;
					// $subject = ["Class", "Regi_No", "Roll_No", "Exam", "Name"];
					$results = json_decode($results);
					$results = str_replace(' ', '_', $results);
				}
			}
			
			$total_subject = 0;
			
			$points = array();
			foreach($results as $grade) {
				$target = $grade;
				if ($target) {
					$total_subject++;
					$points[] = educare_point_grade($target, $id);
				}
			}
			
			
			$add_optinal_mark = 0;
			$all_sub = $total_subject - 1;
			
			for ($x = 0; $x <= $all_sub; $x++) {
				
				$optinal = strtok($points[$x], ' ');
				$optinal = strtok($optinal);
				
				if ($optinal) {
					
					if ($optinal > 2) {
						$add_optinal_mark = $optinal - 2;
					} else {
						$add_optinal_mark = 0;
					}
				}
			}
			
			$main_subjects = array();
		    foreach($points as $marks){ 
		        preg_match ('/1 /', $marks, $optional); 
		        if(count($optional) == 0) $main_subjects[] = $marks;
		    }
			
			// $test = Array ( 0 => 3, 1=> 5, 2=> 4, 3=> 2 );
			
			/*
			* You know, php array start in 0.
			* So, if your subject is 4 and we call $total_subject directly in loop expirations it's return 5 digit.
			* For example: 0, 1, 2, 3, 4. if you see loop start at 0 then 1, 2, 3..., our subject is 4 but it's looping 5 times. for solve this matter we need to decrease (4 - 1). here $total_subject = 4.
			# Notes: don't call $total_subject / --$total_subject directly in loop expirations. it's complicated the code!. first assign this a var. Let's do it-
			*/
			$count = count($main_subjects) - 1;
			
			$pass = true;
			for ($x = 0; $x <= $count; $x++) {
				$sub_points = $main_subjects[$x];
				if (empty ($sub_points)) {
					$pass = false;
				}
			}
			
			if ($pass == false) {
				$gpa = 0;
			} else {
				$gpa = array_sum($main_subjects);
				$gpa += $add_optinal_mark;
				$gpa /= count($main_subjects);
				$gpa = number_format((float)$gpa, 2, '.', ''); // ignore unnecessary digits!
			}
			if ($gpa > 5) {
				$gpa = "5.00";
			}
			return $gpa;
		}
		
		
		/*
		# Target optional subject with sign characters {✓}
		* Call back roles: educare_show_marks($print, 'English');
		* This function call back only in function.php files not here!
		* @sincce 1.0.0
		
		* @param object $print		Select specific subject
		* @param string $subject	Specific subject name
		
		* @return string
		*/
		function educare_show_marks($print, $subject) {
			$marks = $print->$subject;
			$optinal = strtok($marks, ' ');
			if ($optinal == 1) {
				$marks = "".substr(strstr($marks, ' '), 1)." ✓";
			}
			return $marks;
		}
		
		
		/*
		# Create function for students result status
		
		* @sincce 1.0.0
		
		* @param object $print 	Select specific subject
		* @param int $id				Specific subject id
		
		* @return string|HTML
		*/
		function educare_results_status($print, $id) {
			$passed = "<div class='success results_passed'>Passed</div>";
			$failed = "<div class='failed results_failed'>Failed</div>";
			
			$geting_mark = educare_get_marks($print);
			if ( !empty ($geting_mark)) {
				$status = $passed;
			} else {
				$status = $failed;
			}
			
			// Get auto results, if auto result is checked
			if (educare_check_status('auto_results') == 'checked') {
				$status = $status;
			} else {
				$status = strtolower(educare_value('Result', $id));
				if ($status == 'passed') {
					$status = $passed;
				} else {
					$status = $failed;
				}
			}
			return $status;
		}
		
		// end results function
		?>
		
		<!-- Begin (frond end) Results Body -->
		<div class="result_body">
			<?php
			if (educare_check_status('photos') == 'checked') {
				echo "<div class='student_photos'>
				<img src='".esc_url($print->Photos)."' class='img' alt='".esc_attr($print->Name)."'/></center>
				</div>";
			}
			?>
			
			<h2><?php echo esc_html($print->Name);?></h2>
			<div class="table_body">
			<table class="result_details">
				<tr>
					<td>Roll No</td>
					<td><?php echo esc_html($print->Roll_No);?></td>
					<td>Name</td>
					<td><?php echo esc_html($print->Name);?></td>
				</tr>
					
				<tr>
					<td>Reg No</td>
					<td><?php echo esc_html($print->Regi_No);?></td>
					<td>Class</td>
					<td><?php echo esc_html($print->Class);?></td>
				</tr>
				
				<!-- Extra field -->
				<?php echo wp_kses_post(educare_get_data('Extra_field', $id, $print));?>
				
				<tr>
					<td>Result</td>
					<td><?php echo wp_kses_post(educare_results_status($print, $id));?></td>
					<td>Year</td>
					<td><?php echo esc_html($print->Year);?></td>
				</tr>
				
				<tr>
					<td>GPA</td>
					<td colspan='3'>
						<?php
						if (educare_check_status('auto_results') == 'checked') {
							echo esc_html(educare_get_marks($print));
						} else {
							$getting_value = educare_value('GPA', $id);
							if (empty($getting_value)) {
								echo '0';
							} else {
								echo esc_html(educare_value('GPA', $id));
							}
						}
						?>
					</td>
				</tr>
			</table>
			</div>
			
			<h3>Grade Sheet</h3>
			
			<div class="table_body">			
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
					// include(INC.'database/data_Subject.php');
					echo wp_kses_post(educare_get_data('Subject', $id, $print));
					
					/* ====== Above function return like this ======
					<tr>
					<td><?php echo esc_html($serial+=1);?></td>
					<td>Bangla</td>
					<td><?php echo esc_html($print->Bangla);?></td>
					<td>
						<?php esc_html(educare_letter_grade($print, 'Bangla'));?>
					</td>
					</tr>
					
					<tr>
						<td><?php echo esc_html($serial+=1);?></td>
						<td>English</td>
						<td><?php echo esc_html($print->English);?></td>
						<td>
							<?php educare_letter_grade($print, 'English');?>
						</td>
					</tr>
					
					<tr>
						<td><?php echo esc_html($serial+=1);?></td>
						<td>ICT</td>
						<td><?php echo esc_html($print->ICT);?></td>
						<td>
							<?php educare_letter_grade($print, 'ICT');?>
						</td>
					</tr>
					...............….................................…..................
					...............….................................…..................
					*/
					?>
					
				</tbody>
			</table>
			</div>
		<div class="no_print">
			<button onClick="<?php echo esc_js('window.print()');?>" class="print_button"><i class="fa fa-print"></i> Print</button>
			<button onClick="<?php echo esc_js('history.back()');?>" class="back_button"><i class="fa fa-backward"></i> Back</button>
		</div>
		</div> <!-- .result_body -->
		
		<?php
		}
		
		// let's do something when results not found
		if (!$educare_results) {
			if(!empty($Class) && !empty($Exam) && !empty($Roll_No) && !empty($Regi_No) && !empty($Year) ) {
				$not_found = '<div class="results_not_found"><h2 class="error">Results Not Found</h2><p>Please try again!</p></div>';
			} else {
				ob_start();
				echo '<div class="errnotice"> 
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
				
				echo 'Please fill all the required fields carefully. thanks.</p></div>';
				$errmsg = ob_get_clean();
			}
		}
	} 
	
	
	// Show results search forms when results not found
	if (!$educare_results) {
		echo '<div class="results_form">';
		// echo '<h1>Results</h1>';
		echo wp_kses_post($not_found);
		echo wp_kses_post($errmsg);
		?>
		
		<form class="educare_results_form forms" action="" method="post" id="educare_results">

			<div class="select">
				<p>Select Class:
					<select id="Class" name="Class" class="form-control">
						<?php 
						echo '<option value="0">Select Class</option>';
						educare_get_options('Class', $Class);
						?>
					</select>
				</p>
			
				<p>Select Exam:
					<label for="Exam" class="labels" id="exam"></label>
					<select id="Exam" name="Exam" class="form-control">
						<?php 
						echo '<option value="0">Select Exam</option>';
						educare_get_options('Exam', $Exam);
						?>
					</select>
				</p>
			</div>

			Inter Roll No:
			<label for="Roll_No" class="labels" id="roll_no"></label>
			<input type="number" id="Roll_No" name="Roll_No" class="fields" value='<?php echo esc_attr($Roll_No);?>' placeholder="Enter Your Roll">

			Inter Regi No:
			<label for="Regi_No" class="labels" id="regi_no"></label>
     	 <input type="number" id="Regi_No" name="Regi_No" class="fields" value='<?php echo esc_attr($Regi_No);?>' placeholder="Enter Your Reg no">

			Select Year:
			<label for="Year" class="labels" id="year"></label>
			<select id="Year" name="Year" class="form-control">
				<?php 
				// echo '<option value="0">Select Year</option>';
				educare_get_options('Year', $Year);
				?>
			</select>
			
			<button id="results_btn" class="results_button button" name="educare_results" type="submit">View Results </button>
			
		</form>
		</div>
		<?php
	}
	
	echo "</div>"; // .educare_result
	// don't remove this line !!!
	return ob_get_clean();
			
}



?>