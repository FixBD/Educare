<?php

/**
 * ### educare_custom_results($results);
 * function for customize educare results card
 *
 * usage => 
 * 1. Copy and paste this function in your themes functions.php files
 * 2. allow/enable 'Custom Design Permission' in plugin settings (because we care of your security)
 * 3. Throw your logic [code] under 'educare_custom_results' function for customize results card
 * 4. Done, now you can see your custom design!
 *
 * Notes: When you add (this) custom functionality or customize the results card or search forms you need to allow/enable the 'Custom Design Permission' options in the (educare) plugin settings. Otherwise this function will be ignored. This function allow only one ($results) arguments. So, you must pass this arg. But, you can rename this arg as you wise
 *
 * @since 1.2.2
 * @param object|array $results 	Students data
 * @return mixed
 * 
 */

function educare_custom_results($results) {
	$id = $results->id;
	// Students details
	$details = json_decode($results->Details);
	// Subject and marks
	$subject = json_decode($results->Subject);

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

?>