<?php

/**
 * ### educare_custom_search_form($not_found, $errmsg);
 * function for customize educare search forms
 *
 * usage => 
 * 1. Copy and paste this function in your themes functions.php files
 * 2. allow/enable 'Custom Design Permission' in plugin settings (because we care of your security)
 * 3. Throw your logic [code] under 'educare_custom_search_form' function for customize search forms
 * 4. Done, now you can see your custom design!
 *
 * Notes: When you add (this) custom functionality or customize the results card or search forms you need to allow/enable the 'Custom Design Permission' options in the (educare) plugin settings. Otherwise this function will be ignored. This function allow two ($not_found, $errmsg) arguments. So, you must pass this arg. But, you can rename this arg as you wise
 *
 * You need to keep all forms [name] as default. like -
 * 1. <select name="Class">
 * 2. <select name="Exam">
 * 3. <select name="Year">
 * 4. <input name="Roll_No">
 * 5. <input name="Regi_No">
 * 6. <button name="educare_results">
 *
 * @since 1.2.2
 * @param mixed $not_found 	when results is not found/result not found msgs
 * @param mixed $errmsg 	field validation msg
 * @return mixed
 * 
 */

function educare_custom_search_form($not_found, $errmsg) {
	// show msg when results not found in the database
	echo wp_kses_post($not_found);
	// show field validations error msgs
	echo wp_kses_post($errmsg);

	?>
	<form method="post" action="" >
		<p>Select Class:</p>
		<select name="Class" class="form-control">
			<?php 
			echo '<option value="0">Select Class</option>';
			educare_get_options('Class', $Class);
			?>
		</select>

		<p>Select Exam:</p>
		<select name="Exam">
			<?php 
			echo '<option value="0">Select Exam</option>';
			educare_get_options('Exam', $Exam);
			?>
		</select>

		<p>Roll No:</p>
		<input type="number" name="Roll_No" value="" placeholder="Enter Roll_No">
		<p>Reg No:</p>
		<input type="number" name="Regi_No" value="" placeholder="Enter Regi_No">

		<p>Select Year:</p>
		<select name="Year">
			<?php 
			// echo '<option value="0">Select Year</option>';
			educare_get_options('Year', $Year);
			?>
		</select>
		
		<button name="educare_results" type="submit">View Results </button>
	</form>
	<?php
}

?>