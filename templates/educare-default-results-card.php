<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * Processes and calculates various student result details using the default result system.
 *
 * This function takes a student data object and performs calculations for result-related details such as combined marks,
 * class average, highest and lowest marks in the class, position out of all students, and overall GPA. It utilizes the
 * predefined `StudentResult` class to perform these calculations. The function also updates subject data based on a
 * provided template, including auto-generated fields like GPA and grade. The modified student data object is returned
 * after processing.
 *
 * @param object $print The student data object containing details like marks, subjects, and remarks.
 * @return object The modified student data object with calculated result details and updated subject data.
 */
function educare_process_default_result_system($print) {
	$print->Details = json_decode($print->Details);
	$print->Others = json_decode($print->Others);
	$Subject = json_decode($print->Subject);

	if ($Subject) {
		// We use Educare pre build class StudentResult(). So we just provide student data ($print) into StudentResult() for getting combineMarks, classAverage, highestInClass, lowestInClass, positionOutofAllStudents. also getAllStudent to get all students based on Class, Exam, Year
		$student = new StudentResult($print);
		// Calculate combined marks and class average
		$combineMarks = $student->combineMarks(null, true);
		// Set 'Result' and 'GPA' properties based on student's status
		$print->Result = $student->getStatus('Result', $combineMarks);
		$print->GPA = $student->getStatus('GPA', $combineMarks);

		// Fetch terms and fields based on a template
		$terms = educare_get_template_data('fields', false);

		// Process and update subject data based on the template
		foreach ($Subject as $subject => $fields) {
			foreach ($terms as $term => $termInfo) {
				if (property_exists($fields, $term)) {
					$value = sanitize_text_field($fields->$term);
				} else {
					$value = '';
				}

				// Handle auto-generated fields (e.g., GPA, grade, combine, etc.)
				if ($termInfo['auto'] == 'on') {
					if ($term == 'gpa' or $term == 'grade') {
						$value = sanitize_text_field($fields->marks);
						
						if ($term == 'gpa') {
							$value = educare_letter_grade($value, true);
						} else {
							$value = educare_letter_grade($value);
						}
					}
				}

				// Update the subject's term with the calculated value
				$Subject->$subject->$term = $value;
			}
		}

		// Update the $print object with the modified subject data
		$print->Subject = $Subject;
	} else {
		$print->Subject = $Subject;
	}

	// Return the modified student data object
	return $print;
}



/**
 * Generates a table displaying student's marks
 *
 * This function constructs a table layout to display a student's marks for different terms and subjects. It fetches
 * template data for terms fields and displays the default grades and terms. Additionally, if the 'rattings_system'
 * is enabled, it displays remarks and rattings in separate tables. The function provides a comprehensive view of the
 * student's performance and optional remarks through a visually organized table.
 *
 * @param object $student_data The object containing student's data including subjects, marks, and remarks.
 * @return void
 */
function educare_get_marks_terms($student_data) {
	// Extract subject data from the student_data object
	$subject = $student_data->Subject;
	
	// Fetch template data for terms fields
	$terms = educare_get_template_data('fields', false);
	
	// Fetch results_card data and checked grade_sheet data
	$results_card = educare_check_status('results_card');
	$default_terms = educare_checked_data($results_card->grade_sheet, false);
	$default_terms = json_decode(json_encode($default_terms), true);

	// Display the title and subtitle if 'grade_sheet' is defined in template data
	if (key_exists('grade_sheet', $default_terms)) {
		$subtitle = '';

		if (key_exists('subtitle', $default_terms['grade_sheet'])) {
			$subtitle = $default_terms['grade_sheet']['subtitle'];
			if ($subtitle) {
				$subtitle = '<small clss="subtitle">' . esc_html__($subtitle, 'educare') . '</small>';
			}
		}

		echo '<h2 class="head">' . esc_html__($default_terms['grade_sheet']['title'], 'educare') . wp_kses_post($subtitle) . '</h2>';
	}

	// Start HTML output for the main grade sheet table
	?>
	<div class="table-responsive">
		<table class="grade_sheet list">
			<thead>
				<tr>
					<?php
					// Generate table headers for default_terms and terms
					educare_grade_sheet_th($default_terms);
					educare_grade_sheet_th($terms);
					?>
				</tr>
			</thead>

			<tbody>
				<?php
				if ($subject) {
					// Initialize serial number for subjects
					$serialNo = 1;

					// Loop through each subject and its fields
					foreach ($subject as $sub_name => $fields) {
						$optional = $value = '';
						
						// Check if optional field is defined for the subject
						if (property_exists($fields, 'optional')) {
							$optional = educare_check_status('optional_sybmbol');
							$optional = sanitize_text_field($fields->optional) ? ' ' . $optional : '';
						}

						// Start a new row for each subject
						echo '<tr>';

						// Loop through default_terms and display the corresponding data
						foreach ($default_terms as $term => $termInfo) {
							if ($term == 'grade_sheet') continue;
							if ($term == 'no') echo '<td>' . esc_html($serialNo++) . '</td>';
							if ($term == 'subject') echo '<td>' . esc_html($sub_name . $optional) . '</td>';

							if (property_exists($fields, $term)) {
								echo '<td>' . esc_html__($fields->$term, 'educare') . '</td>';
							}
						}

						// Loop through terms and display corresponding data
						foreach ($terms as $term => $termInfo) {
							if (property_exists($fields, $term)) {
								if ($term == 'gpa' or $term == 'grade') {
									$value = wp_kses_post($fields->$term);
								} else {
									$value = sanitize_text_field($fields->$term);
								}
							}

							echo '<td>' . wp_kses_post($value) . '</td>';
						}

						// End row for the subject
						echo '</tr>';
					}
				} else {
					// Subject not found
					$span = count($default_terms) + count($terms);
					echo '<tr><td colspan="' . esc_attr($span) . '">'.__('No Data', 'educare').'</td></tr>';
				}
				?>
			</tbody>
		</table>
	</div>
	<?php
}


/**
 * function for default results card
 * 
 * How to customize educare results card?
 * For more info about custom results card (PREMIUM):
 * @link FixBD: https://fixbd.com/docs/educare/How-to-customize-educare-results-card
 * @see Plugin Dir: educare/includes/support/educare-custom-results-card.php
 *
 * @since 1.4.2
 * @last-update 1.4.2
 * 
 * @param object|array $print 							Students data
 * @param object|array $template_details 		Template details
 * @return mixed
 */

/**
 * Generates the default results card layout and displays student result details using the default results card template.
 *
 * This function is responsible for generating and displaying the results card layout for student result details. It takes a
 * student data object and template details as parameters. If template details are provided, it returns the default template
 * information. If a student data object is provided, it processes the data, calculates various result details, and displays
 * the results card layout with the student's information, marks, GPA, grades, and remarks. The function also handles the printing
 * of the results card. The generated layout is based on the default results card template and can be customized using the
 * provided template information.
 * 
 * How to customize educare results card?
 * For more info about custom results card (PREMIUM):
 * @link FixBD: https://fixbd.com/docs/educare/How-to-customize-educare-results-card
 * @see Plugin Dir: educare/includes/support/educare-custom-results-card.php
 *
 * @since 1.4.2
 * @last-update 1.4.2
 *
 * @param object|null $print The student data object containing details like marks, subjects, and remarks.
 * @param bool $template_details Optional. If true, returns the default template information. Default is false.
 * @param bool $sttings Optional. Not used in this function.
 * @return array|null The default template information if $template_details is true, otherwise null.
 */
function educare_default_results_card($print = null, $template_details = false, $sttings = false) {
	// Set template information (title or thumbnail)
	if (!$print && $template_details) {
		$template_info = array (
			'title' => 'Default Template',
			'thumbnail' => EDUCARE_URL . 'assets/img/templates-preview/results-card-templates/default-results-template.jpg', // for default use EDUCARE_TEMPLATE_THUMBNAIL
			'fields' => array(),
			'import' => true,
		);

		// return info
		return $template_info;
	}

	$student_data = educare_process_default_result_system($print);

	// For certificate template
	if ($print && $template_details) {
		return $student_data;
	}

	// Check if custom results card is available. If available, use it. Otherwise, use the default results card.
	if (has_action('educare_custom_results')) {
		do_action('educare_custom_results', $student_data);
		return;
	}
	
	$info = educare_check_status('details');
	// Check requred fields data
	$requred = educare_check_status('display');
	// Getting all requered field key and title
	$requred_title = educare_requred_data($requred, true);

	$details = $student_data->Details;
	$others = $student_data->Others;

	?>
	<!-- Begin (Front-End) Results Body -->
	<div class="result_body default-template">
		<div class="results-container border">
			<?php
			if (educare_check_status('show_banner') == 'checked') {?>
				<div class="fixbd-flex banner">
					<div>
						<img src="<?php echo esc_url(educare_get_attachment(0, 'logo1'))?>">
					</div>
					<div class="title">
						<h1>Institutions Name Or Title</h1>
						<p class="sub-title">Rangpur, Dhaka, Bangladesh</p>
						<p class="sub-title">Founded in <?php echo esc_html(date('Y'))?></p>
					</div>
					<div>
						<img src="<?php echo esc_url(educare_get_attachment(0, 'logo2'))?>">
					</div>
				</div>
				<?php
			}

			if (educare_check_status('student_info') == 'checked') {
				?>
				<div class="fixbd-flex student-photos border m-3 p-2">
					<div class="student-details">
						<table style="display: block;">
							<?php
							$field = array (
								'Name',
								'Class',
								'Roll_No',
								'Regi_No',
								'Exam'
							);

							foreach ($field as $field_name) {
								if (key_exists($field_name, $requred_title)) {
									echo '
									<tr>
									<td>'.esc_html__( $requred_title[$field_name], 'educare' ).'</td>
									<td>:</td>
									<td>'.esc_html($print->$field_name).'</td>
									<tr>
									';
								}
							}
							?>
						</table>
					</div>

					<?php
					// Admin can hide (students) name from result card
					if (key_exists('Name', $requred_title)) {
						echo "<h2 class='".esc_attr( $requred_title['Name'] )."'> ".esc_html($print->Name)."</h2>";
					}

					if (educare_check_status('photos') == 'checked') {
						echo '<div class="s-photos">';
						$Photos = isset($others->Photos) ? educare_get_attachment($others->Photos) : educare_get_attachment();
						
						echo "<div class='img'>";

						if ($Photos) {
							echo "<img src='".esc_url($Photos)."' alt='".esc_attr($print->Name)."' width='100%'/>";
						}

						echo "</div>";
						echo '</div>';
					}
					?>
				</div>
				<?php
			}

			$results_card = educare_get_card_data();
			$results_card = educare_requred_data($results_card, true);
			$info = educare_check_status('details');

			// Getting checked data
			$results_card = educare_check_status('results_card');
			// getting results_card checked data
			$card_details = educare_checked_data($results_card->details, false);
			$card_details = json_decode(json_encode($card_details), true);

			// Admin can hide students details from result card
			if ($info == 'checked') {
				// Admin can hide (students) name from result card
				if (key_exists('details', $card_details)) {
					$subtitle = '';
					
					if (key_exists('subtitle', $card_details['details'])) {
						$subtitle = $card_details['details']['subtitle'];
						if ($subtitle) {
							$subtitle = '<small clss="subtitle">'.esc_html__($subtitle, 'educare').'</small>';
						}
					}
			
					echo '<h2 class="details">'. esc_html__($card_details['details']['title'], 'educare') . wp_kses_post( $subtitle ).'</h2>';
				}
				
				echo '<div class="y-3 border-top"></div>';

				echo '<div class="table_body m-3">
					<table class="result_details">';
						echo '<tr>';
						if (key_exists('Roll_No', $requred_title) and key_exists('Regi_No', $requred_title)) {
							echo '<td>'.esc_html__( $requred_title['Roll_No'], 'educare' ).'</td>
							<td>'.esc_html($student_data->Roll_No).'</td>';

							if (key_exists('Name', $requred_title)) {
								echo '<td>'.esc_html__( $requred_title['Name'], 'educare' ).'</td>
								<td>'.esc_html($student_data->Name).'</td>';
							} else {
								echo '<td></td>
								<td></td>';
							}

							echo '</tr>';

							echo '<tr>
								<td>'.esc_html__( $requred_title['Regi_No'], 'educare' ).'</td>
								<td>'.esc_html($student_data->Regi_No).'</td>
								<td>'.esc_html__( $requred_title['Class'], 'educare' ).'</td>
								<td>'.esc_html($student_data->Class).'</td>
							</tr>';
						} else {
							echo '</tr>';
							
							if (key_exists('Roll_No', $requred_title)) {
								echo '<td>'.esc_html__( $requred_title['Roll_No'], 'educare' ).'</td>
								<td>'.esc_html($student_data->Roll_No).'</td>';
							}
							if (key_exists('Regi_No', $requred_title)) {
								echo '<tr>
								<td>'.esc_html__( $requred_title['Regi_No'], 'educare' ).'</td>
								<td>'.esc_html($student_data->Regi_No).'</td>';
							}
							if (key_exists('Name', $requred_title)) {
								echo '<td>'.esc_html__( $requred_title['Name'], 'educare' ).'</td>
								<td>'.esc_html($student_data->Name).'</td>';
							}

							echo '</tr>';
						}
						
						// Extra field
						$count = 1; // for add specific tags (div/tr/ul) in every 4 foreach loop

						if ($details) {
							foreach ($details as $key => $value) {
								if ($count%2 == 1) {  
									echo "<tr>";
								}
									
								echo "<td>".esc_html__(str_replace('_', ' ', $key), 'educare')."</td><td>".esc_html($value)."</td>"; 
								
								if ($count%2 == 0) {
									echo "</tr>";
								}
							
								$count++;
							}
						}
						
						$count = 1;
						$field = array (
							'result',
							'year',
							'gpa',
						);
			
						foreach ($field as $value) {
							if (key_exists($value, $card_details)) {
								$key = $value == 'gpa' ? strtoupper($value) : ucfirst($value);

								if ($key == 'Result') {
									$key = $student_data->Result;
								}

								if ($key == 'Year') {
									$key = $student_data->Year;
								}

								if ($key == 'GPA') {
									$key = $student_data->GPA;
								}

								if ($count%2 == 1) {  
									echo "<tr>";
								}

								$key = $key ?: false;

								echo '<td>'.esc_html__( $card_details[$value]['title'], 'educare' ).'</td><td>'.wp_kses_post($key).'</td>';

								if ($count%2 == 0) {
									echo "</tr>";
								}

								$count++;
							}
						}
						?>
						
					</table>
				</div>
				<?php
			}

			echo '<div class="m-3">';
				educare_get_marks_terms($student_data);
			echo '</div>';

		echo '</div>';

		?>
		<div class="no_print">
			<button onClick="<?php echo esc_js('window.print()');?>" class="print_button"><i class="fa fa-print"></i> <?php _e('Print', 'educare');?></button>
			<button id="educare-undo" class="undo-button" onClick="window.location.href = window.location.href;"><i class="fa fa-undo"></i> <?php _e('Search Again', 'educare');?></button>
		</div>
		<?php
	echo '</div>';
}

// Hook the function to the educare_results_card_template card
add_action( 'educare_results_card_template', 'educare_default_results_card' );


?>