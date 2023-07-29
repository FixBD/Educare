<?php
/**
 * ### educare_custom_results_card($print);
 * 
 * function for customize educare results card
 *
 * usage => 
 * 1. Copy and paste this function with action (hook) in your active theme functions.php files
 * 2. allow/enable 'Custom Design Permission' in plugin settings (because we care of your security)
 * 3. Throw your logic [code] under 'educare_custom_results_card' function for customize results card
 * 4. Done, now you can see your custom design!
 *
 * Notes: When you add or hook custom functionality for customize the results card or search forms, you need to allow/enable the 'Custom Design Permission' options in the (educare) plugin settings. Otherwise, this function will be ignored. One more think, This function allow only one @param {$print}. So, you must pass this arg. But, you can rename this arg as your wise!
 * 
 * Educare Default Results Card (table): 
 * @link GitHub: https://github.com/FixBD/Educare/blob/educare/includes/support/educare-default-results-card.php
 * @see Plugin Dir: educare/includes/support/educare-default-results-card.php
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

function educare_custom_results_card($print) {
	$id = $print->id;
	// Students details
	$details = json_decode($print->Details);
	// Subject and marks
	$subject = json_decode($print->Subject);

	/**
	*
	* Students details
		echo '<pre>';
		print_r($details);
		echo '</pre>';
	*
	* Students subject and marks
		echo '<pre>';
		print_r($subject);
		echo '</pre>';
	*
	* Show/Display passed or failed status
	* 
		echo wp_kses_post(educare_results_status($subject, $id));
	*
	* Show/Display GPA
	* 
		echo esc_html(educare_results_status($subject, $id, true));
	*
	* You can easily customize or change anything in the result card using the above variables and functions
	* Here is an example given bellow. That will be help you to clearify defined variables and functions
	* 
	*/

	// Students photos
	echo '<img src="'.esc_url($details->Photos).'">';

	// Students Details
	echo '<h2>Details</h2>';
	echo '<table style="width: 100%;">';
	// For add specific tags (div/tr/ul) in every 4 loops
	$count = 1;

	foreach ($details as $fields => $value) {

		// ignore photos
		if ($fields == 'Photos') {
			break;
		}

		if ($count%2 == 1) {
			echo "<tr>";
		}
		
		echo "<td>".esc_html(str_replace('_', ' ', $fields))."</td>
		<td>".esc_html($value)."</td>";
		
		if ($count%2 == 0) {
			echo "</tr>";
		}
	
		$count++;
	}

	echo '<tr>
	<td>Results</td>
	<td>'.wp_kses_post(educare_results_status($subject, $id)).'</td>
	<td>GPA</td>
	<td>'.esc_html(educare_results_status($subject, $id, true)).'</td>
	</tr>';

	echo '</table>';

	// Students Grade Sheet
	?>
	<h2>Grade Sheet</h2>
	<table style="width: 100%;">
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
			$serial = 1;

			foreach ($subject as $name => $marks) {
				$mark = educare_display_marks($marks);

				echo "<tr>
				<td>".esc_html($serial++)."</td>
				<td>".esc_html(str_replace('_', ' ', $name))."</td>
				<td>".esc_html($mark)."</td>
				<td>".wp_kses_post(educare_letter_grade($marks))."</td>
				</tr>";
			}

			// Do something awesome!
			
		echo '</tbody>
	</table>';

}

// Hook/Apply specified function with 'educare_custom_results'
add_action( 'educare_custom_results', 'educare_custom_results_card' );

?>