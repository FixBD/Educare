
<div class="educare_post imoport">
	<h1>View Results</h1>
	<blockquote>There are lots of options to find students results. You can view class, exam and year wise result. You can also view specific results sorting by Asc or Decs. For that, you need to change those options. Also, you can see all the results at the same time. To see all the results, keep the full forms as default and click the View Results button. That's it!</blockquote>

<?php
global $wpdb;
// Table name
$tablename = $wpdb->prefix."educare_results";
	
// define empty variables for ignore error
$table = $year = $data = $select_year = $order = $time = $sub_term = $sub = '';
$results_per_page = 10;

if (isset($_POST["educare_view_results"]) or isset($_POST['remove'])) {
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
	echo "<div class='notice notice-success is-dismissible'><p>Successfully Deletet Your Selectet Results</p></div>";
}
if (isset($_POST['remove'])) {
	/*
	$table = sanitize_text_field($_POST['table']);
	$data = sanitize_text_field($_POST['data']);
	$select_year = sanitize_text_field($_POST['select_year']);
	*/
	/*
	echo "<div class='notice notice-success is-dismissible'> <p>";
	if ($table == 'All' and $year == 'All') {
		$wpdb->delete( $tablename );
		echo "Successfully deleted all results!";
	}
	elseif ($table == 'All' and $year == 'Year') {
		$wpdb->delete( $tablename, array( 'Year' => $select_year ) );
		echo "Successfully deleted all results of the year $select_year";
	}
	elseif (empty($select_year)) {
		$wpdb->delete( $tablename, array( $table => $data ));
		echo "All results of the ".esc_html($data)." have been successfully deleted";
	} else {
		$wpdb->delete( $tablename, array( "$table" => "$data", "Year" => "$select_year" ));
		echo "Results of the ".esc_html($data)." in ".esc_html($select_year)." have been successfully deleted";
	}
	echo "</p></div>";

	*/
}
?>


<!-- Search Form -->
<form class="add_results" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
<div class="content">

	<div class="select">
		
		<div class="select">
			
			
		</div>
	</div>

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
	
	<p>Results Per Page:</p>
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
	if (isset($_POST["educare_view_results"]) or isset($_POST['remove']) or isset($_POST['remove_result'])) {
		$table = sanitize_text_field($_POST['table']);

		if (isset($_POST['remove_result'])) {
			$id = sanitize_text_field($_POST['id']);
			if ($wpdb->delete( $tablename, array( 'id' => $id ))) {
				echo "<div class='notice notice-success is-dismissible'><p>Successfully Deletet Results</p></div>";
			} else {
				echo "<div class='notice notice-error is-dismissible'><p><span class='error'>Results not found for delete.</span></p></div>";
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
			<span class="input-for">Filter Results For Specific <i>Students, Roll No, Regi No...</i></span>
			<label for="searchBox" class="labels"></label>
			<input type="search" id="searchBox" placeholder="Search Results" class="fields">
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
								echo "<td><img src='".esc_url($Photos)."' class='student-img' alt='IMG'/></td>";
							}
								
							$results_title = $results_button = '';
							$results_value = '&#xf177';

							foreach ($default_data as $key => $value) {
								$default_check = educare_check_status($key, true);
								if ($default_check) {
									if ($print->$key) {
										echo "<td>".esc_html($print->$key)."</td>";
									} else {
										echo "<td class='error'>Empty</td>";
										$results_button = 'error';
										$results_value = '&#xf530';
										$results_title = 'This results is not visible for users. Because, some required field are empty. Fill all the required field carefully. Otherwise, users getting arror notice when someone find this results. Click pen (Edit) button for fix this issue.';
									}
								}
							}

							?>

							<td>
								<input name="id" value="<?php echo esc_attr($id);?>" hidden>
								
								<div class="action_menu">
									<input type="submit" class="button action_button" value="&#xf349">
									<menu class="action_link">
										<form class="educare-modify" action="/<?php echo esc_attr(educare_check_status("results_page"));?>" method="post" id="educare_results" target="_blank">
											<input name="id" value="<?php echo esc_attr($id);?>" hidden>
											
											<input class="button" type="submit" <?php echo esc_attr($results_button);?>" name="educare_results_by_id" value="<?php echo wp_check_invalid_utf8($results_value);?>" title="<?php echo esc_attr($results_title);?>">
										</form>

										<form class="educare-modify" action="/wp-admin/admin.php?page=educare-update-results" method="post" id="educare_results_by_id" target="_blank">
											<input name="id" value="<?php echo esc_attr($id); ?>" hidden>
											<input class="button" type="submit" name="edit_by_id" value="&#xf464">
										</form>

										<form class="educare-modify" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
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
											
											<input class="button error" type="submit" name="remove_result" value="&#xf182">
										</form>

										

									</menu>
								</div>
							</td>
							<?php
						echo '</tr>';
					}
				}

			} else {
				echo "<tr><td colspan='".esc_attr($col+2)."'><span class='error'>Results not found</span></td></tr>";
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
			
			<input type="submit" name="remove" class="educare_button" value="Delete Results" <?php educare_confirmation('remove_results', $data, $select_year); echo esc_attr($status);?>>
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

</div>

