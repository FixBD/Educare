
<div class="educare_post imoport">
	<h1>View Results</h1>
	<blockquote>There are lots of options to find students results. You can view class, exam and year wise result. You can also view specific results sorting by Asc or Decs. For that, you need to change those options. Also, you can see all the results at the same time. To see all the results, keep the full forms as default and click the View Results button. That's it!</blockquote>

<?php
global $wpdb;
// Table name
$tablename = $wpdb->prefix."Educare_results";
	
// define empty variables for ignore error
$table = $year = $data = $select_year = $order = $time = "";
$results_per_page = 10;

if (isset($_POST["educare_view_results"]) or isset($_POST['remove'])) {
	$table = sanitize_text_field($_POST['table']);
	$year = sanitize_text_field($_POST['year']);

	if ($table != 'All') {
		$data = sanitize_text_field($_POST['data']);
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
	/*
	$table = sanitize_text_field($_POST['table']);
	$data = sanitize_text_field($_POST['data']);
	$select_year = sanitize_text_field($_POST['select_year']);
	*/
	echo "<div class='notice notice-success is-dismissible'> <p>";
	if (empty($select_year)) {
		$wpdb->delete( $tablename, array( $table => $data ));
		echo "All results of the ".esc_html($data)." have been successfully deleted";
	} else {
		$wpdb->delete( $tablename, array( "$table" => "$data", "Year" => "$select_year" ));
		echo "Results of the ".esc_html($data)." in ".esc_html($select_year)." have been successfully deleted";
	}
	echo "</p></div>";
}
?>


<!-- Search Form -->
<form class="add_results" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
<div class="content">
	<div class="select">
		<p>Results By:</p>
		<p>Select One:</p>
	</div>
	<div class="select">
		<select id='select_table' name="table" onChange="<?php echo esc_js('select_Table()');?>">
			<option value='All' <?php if ($table == 'All') echo 'selected';?>>All</option>
			<option value='Class' <?php if ($table == 'Class') echo 'selected';?>>Class</option>
			<option value='Exam' <?php if ($table == 'Exam') echo 'selected';?>>Exam</option>
		</select>
		
		<select id='select_data' name="data">
			<option>All Results</options>
		</select>
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
	
	<p>Results Per Page:</p>
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
		
	<button type="submit" name="educare_view_results" class="educare_button"><i class="dashicons dashicons-visibility"></i> View</button>
	
	<script>
		function select_Table() {
			var x = document.getElementById("select_table").value;
			
			if (x == 'All') {
				document.getElementById("select_data").disabled = 'disabled';
			}

			if (x == 'Class') {
				document.getElementById("select_data").disabled = '';
				document.getElementById("select_data").innerHTML = '<?php educare_get_options('Class', $data);?>';
			}

			if (x == 'Exam') {
				document.getElementById("select_data").disabled = '';
				document.getElementById("select_data").innerHTML = '<?php educare_get_options('Exam', $data);?>';
			}
			
		}
		
		function select_Year() {
			var x = document.getElementById("year").value;
			
			if (x == 'All') {
				document.getElementById("select_year").disabled = 'disabled';
			}
			if (x == 'Year') {
				document.getElementById("select_year").disabled = '';
				document.getElementById("select_year").innerHTML = '<?php educare_get_options('Year', $select_year);?>';
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
	if (isset($_POST["educare_view_results"])) {
		$table = sanitize_text_field($_POST['table']);

		if ($table != 'All') {
			$data = sanitize_text_field($_POST['data']);
		}

		$order = sanitize_text_field($_POST['order']);
		$time = sanitize_text_field($_POST['time']);
		
		$chek_roll = educare_check_status('roll_no', true);
		$chek_regi = educare_check_status('regi_no', true);

		// Fetch records
		?>
		
		<div class="wrap-input">
			<span class="input-for">Filter Results For Specific <i>Students, Roll No, Regi No...</i></span>
			<label for="searchBox" class="labels"></label>
			<input type="search" id="searchBox" placeholder="Search Results" class="fields">
			<span class="focus-input"></span>
		</div>
				
					
		<table width='100%' border='1' style='border-collapse: collapse;' class='view_results all-results'>
			<thead>
			<tr>
			<th>No</th>
			<th>Photos</th>
			<th>Name</th>
			<th>Class</th>
			<th>Exam</th>

			<?php 

			if ($chek_roll) {
				echo '<th>'.esc_html($chek_roll).'</th>';
			}

			if ($chek_regi) {
				echo '<th>'.esc_html($chek_regi).'</th>';
			}

			?>

			<th>Result</th>
			<th>Edit</th>
			</tr>
			</thead>
			<tbody>
			<?php
			
			if (!empty($select_year)) {
				if ($table == 'All' or empty($data)) {
					$search = $wpdb->get_results("SELECT * FROM ".$tablename." WHERE Year='$select_year' ORDER BY $time $order");
				} else {
					$search = $wpdb->get_results("SELECT * FROM ".$tablename." WHERE $table='$data' AND Year='$select_year' ORDER BY $time $order");
				}
			} else {
				if ($table == 'All' or empty($data)) {
					$search = $wpdb->get_results("SELECT * FROM ".$tablename." ORDER BY $time $order");
				} else {
					$search = $wpdb->get_results("SELECT * FROM ".$tablename." WHERE $table='$data' ORDER BY $time $order");
				}
			}
			
			if(count($search) > 0){
				$count = 0;
				foreach($search as $print){
					$id = $print->id;
					$Details = $print->Details;
					$Details = json_decode($Details);
					$Photos = $Details->Photos;
					$Name = $print->Name;
					$Roll_No = $print->Roll_No;
					$Regi_No = $print->Regi_No;
					$Class = $print->Class;
					$Exam = $print->Exam;
				
					?>
					<tr>
					<?php
					// CSS .empty
					$empty = 'empty';
					echo "
							<td>".esc_html(++$count)."</td>
							<td><img src='".esc_url($Photos)."' class='student-img' alt='IMG'/></td>
							<td>".esc_html($Name)."</td>";
						
						if (empty($Class) || empty($Exam) || empty($Roll_No) || empty($Regi_No)) {
							$results_button = 'error';
							$results_value = '&#xf530';
							$results_title = 'This results is not visible for users. Because, some required field are empty. Fill all the required field carefully. Otherwise, users getting arror notice when someone find this results. Click pen (Edit} button for fix this issue.';
						} else {
							$results_button = '';
							$results_value = '&#xf177';
							$results_title = '';
						}
						$Empty = "<td class='".esc_attr($empty)."'>Empty</td>";
						if (empty($Class)) {
									$Class = $Empty;
						} else {
							$Class = "<td>".esc_html($Class)."</td>";
						}
						if (empty($Exam)) {
							$Exam = $Empty;
						} else {
							$Exam = "<td>".esc_html($Exam)."</td>";
						}
						// check if roll is checked or not
						if ($chek_roll) {
							if (empty($Roll_No)) {
								$Roll_No = $Empty;
							} else {
								$Roll_No = "<td>".esc_html($Roll_No)."</td>";
							}
						} else {
							$Roll_No = '';
						}
						// check if regi is checked or not
						if ($chek_regi) {
							if (empty($Regi_No)) {
								$Regi_No = $Empty;
							} else {
								$Regi_No = "<td>".esc_html($Regi_No)."</td>";
							}
						} else {
							$Regi_No = '';
						}
						
						echo "
						".wp_kses_post($Exam)."
						".wp_kses_post($Exam)."
						".wp_kses_post($Roll_No)."
						".wp_kses_post($Regi_No)."
						";
					?>
					<td>
						<form class="educare-modify" action="/<?php echo esc_attr(educare_check_status("results_page"));?>" method="post" id="educare_results" target="_blank">
							<input name="id" value="<?php echo esc_attr($id);?>" hidden>
							
							<input type="submit" class="button <?php echo esc_attr($results_button);?>" name="educare_results_by_id" value="<?php echo wp_check_invalid_utf8($results_value);?>" title="<?php echo esc_attr($results_title);?>">
						</form>
					</td>
					
					<td>
					<form class="educare-modify" action="/wp-admin/admin.php?page=educare-update-results" method="post" id="educare_results_by_id" target="_blank">
						<input name="id" value="<?php echo esc_attr($id); ?>" hidden>
						<input type="submit" class="button" name="edit_by_id" value="&#xf464">
					</form>
				</td>
			</tr>
			
			<?php
			}
		} else {
			echo "<tr><td colspan='9'>Results not found</td></tr>";
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
			$msg = "<p>Tolal ".esc_html($count)." results found. if you click <b>Delete</b> button, It will remove your selected ".esc_html($select_year)." year results.</p>";
		}
		
		if ($table == 'All' or empty($data) or empty($search)) {
			$status = 'disabled';
		}
		
		echo wp_kses_post($msg);
		?>
		<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
			<input type='hidden' name='table' value='<?php echo esc_attr($table);?>'>
			<input type='hidden' name='data' value='<?php echo esc_attr($data);?>'>
			<input type='hidden' name='select_year' value='<?php echo esc_attr($select_year);?>'>
			<input type='hidden' name='year' value='<?php echo esc_attr($year);?>'>
			<input type='hidden' name='order' value='<?php echo esc_attr($order);?>'>
			<input type='hidden' name='time' value='<?php echo esc_attr($time);?>'>
			<input type='hidden' name='results_per_page' value='<?php echo esc_attr($results_per_page);?>'>
				
			<input type="submit" name="remove" class="educare_button" value="Delete Results" <?php educare_confirmation('remove_results', $data, $select_year); echo esc_attr($status);?>>
		</form>
		<?php
		
	}
	?>
	
	<script>
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

</div>

