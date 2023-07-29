<?php
/**
 * ### educare_custom_results($print);
 * function for default results card
 * 
 * How to customize educare results card ?
 * Folow this simple step =>
 * 1. Copy and paste this function with action in your active theme functions.php files
 
	=================( Custom Result Card )================

		// function for custom results card
		// @param object|array $print					For student data

		function educare_custom_results_card($print) {
			echo '<pre>';
			print_r($print);
			echo '</pre>';

			// Do something awesome!

		}

		// Hook/Apply specified function with 'educare_custom_results'
		add_action( 'educare_custom_results', 'educare_custom_results_card' );

	=================( End Custom Result Card )================

 * 2. allow/enable 'Custom Design Permission' in plugin settings (because we care of your security)
 * 3. Throw your logic [code] under 'specified' function for customize results card
 * 4. Done, now you can see your custom design!
 * 
 * Notes: When you add or hook custom functionality for customize the results card or search forms, you need to allow/enable the 'Custom Design Permission' options in the (educare) plugin settings. Otherwise, this function will be ignored. One more think, This function allow only one @param {$print}. So, you must pass this arg. But, you can rename this arg as your wise!
 * 
 * For more info (about custom result card): 
 * @link GitHub: https://github.com/FixBD/Educare/blob/educare/includes/support/educare-custom-results-card.php
 * @see Plugin Dir: educare/includes/support/educare-custom-results-card.php
 * 
 * How to customize educare results (searching) forms ?
 * Follow this files =>
 * @link GitHub: https://github.com/FixBD/Educare/blob/educare/includes/support/educare-custom-results-form.php
 * @see Plugin Dir: educare/includes/support/educare-custom-results-form.php
 *
 * @since 1.4.0
 * @last-update 1.4.0
 * 
 * @param object|array $print 	Students data
 * @return mixed
 */

// Prevent direct access to the file
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

function educare_default_results($print) {
	global $requred_data, $requred_title;

	$id = $print->id;
	$Subject = json_decode($print->Subject);
	$Details = json_decode($print->Details);

	?>
	<!-- Begin (Front-End) Results Body -->
	<div class="result_body">
		<?php
		if (educare_check_status('photos') == 'checked') {
			if ($Details->Photos == 'URL') {
				$Photos = EDUCARE_STUDENTS_PHOTOS;
			} else {
				$Photos = $Details->Photos;
			}
			echo "<div class='student_photos'>
			<img src='".esc_url($Photos)."' class='img' alt='".esc_attr($print->Name)."'/></center>
			</div>";
		}

		$info = educare_check_status('details');
		$name_class = '';
		
		if ($info != 'checked') {
			$name_class = 'students_name';
		}

		// Admin can hide (students) name from result card
		if (key_exists('Name', $requred_data)) {
			echo "<h2 class='".$name_class." ".esc_attr( $requred_title['Name'] )."'> ".esc_html($print->Name)."</h2>";
		}

		// Admin can hide students details from result card
		if ($info == 'checked') {
			echo '<div class="table_body">
			<table class="result_details">';
			
				echo '<tr>';
				if (key_exists('Roll_No', $requred_data) and key_exists('Regi_No', $requred_data)) {
					echo '<td>'.esc_html( $requred_title['Roll_No'] ).'</td>
						<td>'.esc_html($print->Roll_No).'</td>';

						if (key_exists('Name', $requred_data)) {
						echo '<td>'.esc_html( $requred_title['Name'] ).'</td>
						<td>'.esc_html($print->Name).'</td>';
					} else {
						echo '<td></td>
						<td></td>';
					}

					echo '</tr>';

					echo '<tr>
						<td>'.esc_html( $requred_title['Regi_No'] ).'</td>
						<td>'.esc_html($print->Regi_No).'</td>
						<td>'.esc_html( $requred_title['Class'] ).'</td>
						<td>'.esc_html($print->Class).'</td>
					</tr>';
				} else {
					echo '</tr>';
					if (key_exists('Roll_No', $requred_data)) {
						echo '<td>'.esc_html( $requred_title['Roll_No'] ).'</td>
							<td>'.esc_html($print->Roll_No).'</td>';
					}

					if (key_exists('Regi_No', $requred_data)) {
						echo '<tr>
						<td>'.esc_html( $requred_title['Regi_No'] ).'</td>
							<td>'.esc_html($print->Regi_No).'</td>';
					}

					if (key_exists('Name', $requred_data)) {
						echo '<td>'.esc_html( $requred_title['Name'] ).'</td>
							<td>'.esc_html($print->Name).'</td>';
					}
					echo '</tr>';
				}
				?>
			
				<!-- Extra field -->
				<?php echo educare_get_data_by_student($id, 'Details');?>
				
				<tr>
					<td>Result</td>
					<td><?php echo wp_kses_post(educare_results_status($Subject, $id));?></td>
					<td>Year</td>
					<td><?php echo esc_html($print->Year);?></td>
				</tr>
				
				<tr>
					<td>GPA</td>
					<td colspan='3'>
						<?php echo esc_html(educare_results_status($Subject, $id, true));?>
					</td>
				</tr>
			</table>
			</div>
			<?php
		}

		// Admin can hide grade sheet from result card
		if (educare_check_status('grade_sheet') == 'checked') {
			?>
			<h3>Grade Sheet</h3>
			
			<div class="table_body">			
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
						echo educare_get_data_by_student($id, 'Subject');
						?>
					</tbody>
				</table>
			</div>
			<div class="no_print">
				<button onClick="<?php echo esc_js('window.print()');?>" class="print_button"><i class="fa fa-print"></i> Print</button>
				<button id="educare-undo" class="undo-button" onClick="window.location.href = window.location.href;"><i class="fa fa-undo"></i> Undo</button>
			</div>
			<?php
		}
		?>
	</div> <!-- .result_body -->
	<?php
}

?>