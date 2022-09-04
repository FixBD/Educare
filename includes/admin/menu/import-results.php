<div class="educare_post imoport">
	<h1>Import Results</h1>

<?php

/** =====================( Functions Details )======================
  
	### Educare Import Results

  * @since 1.0.0
	* @last-update 1.2.3

  * @return void
	
===================( function for import results )=================== **/

function educare_import_result() {
	// Begin import results function
	global $wpdb;

	// Table name, where to import the results
	$table = $wpdb->prefix."educare_results";

	// Import CSV
	if(isset($_POST['educare_import_results'])) {

		// File extension
		$extension = pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION);

		// If file extension is 'csv'
		if(!empty($_FILES['import_file']['name']) && $extension == 'csv') {

			$totalInserted = 0;
			$total = 0;
			$exist = 0;
			$error = 0;
		
			// Open file in read mode
			$csvFile = fopen($_FILES['import_file']['tmp_name'], 'r');
			$keys = fopen($_FILES['import_file']['tmp_name'], 'r');
			$keys = fgetcsv($keys);
			$keys = array_map("utf8_encode", $keys);

			$data = array (
				'Name',
				'Roll_No',
				'Regi_No',
				'Class',
				'Exam',
				'Year',
				'GPA',
				'Result',
				'Photos'
			);

			$chek_roll = educare_check_status('Roll_No', true);
			$chek_regi = educare_check_status('Regi_No', true);
			$chek_name = educare_check_status('Name', true);

			if (!$chek_roll) {
				$data = educare_remove_value('Roll_No', $data);
			}
			if (!$chek_regi) {
				$data = educare_remove_value('Regi_No', $data);
			}
			if (!$chek_name) {
				$data = educare_remove_value('Name', $data);
			}

			$Extra_field = educare_demo_data('Extra_field');
			$Class = educare_demo_data('Class');
			$selected_class = sanitize_text_field($_POST['Class']);
			$Subject = $Class->$selected_class;

			$value_len = count($data) + count($Extra_field) + count($Subject);
			// Skipping header row
			fgetcsv($csvFile);
		
			// Read file
			while(($csvData = fgetcsv($csvFile)) !== FALSE) {
				$csvData = array_map("utf8_encode", $csvData);

				// echo '<pre>';	
				// print_r($csvData);	
				// echo '</pre>';
				
				$total ++;
				
				// CSV row column length (based on import files)
				$dataLen = count($csvData);
				// $table row column length (based on the users settings)
				$content_len = $value_len;
				
				/* =====( Explain )=====
				* Example for default Class 6 -> subject = 3, extra field = 7 and default requred field = 9 (Name, Roll No, Regi No, Class, Exam, Year, Passed, GPA, Photos)
				# So, default $table row length is (3+7+9) = 19.
				# Notes: 19 only for example. Sometimes, It's may grow (+19) and reduce (-19) based on the users settings
				
				* For example:
					If users add any content, like Subject or Extra field it's (19+1) = 20 grow up and reduce if users delete any contents.
					
					# Getting csv data value and assign it's a var
					$Name = trim($csvData[0]);
					$Roll_No = trim($csvData[1]);
					$Regi_No = trim($csvData[2]);
					$Class = trim($csvData[3]);
					$Exam = trim($csvData[4]);
					$Year = trim($csvData[5]);
					
					# Were use wpdb for add/insert csv assigned value into database.  for this, we need two types of @param.
					
								1,		2,
					insert(table, data)
					
					First, table name {$table} where to insert our csv data/value and Second is data {$data}, that's means values. Data must be an array, to assign where to insert the data/value.
					
					# For example: 
						$data = array (
							// where => value
							'Name' =>$Name,
							'Roll_No' =>$Roll_No,
										'Reg_No' =>$Reg_No,
										'Class' => $Class,
							'Exam' => $Exam,
							'Year' => $Year
						);
					
					# Now, we can insert our data/value into database
					1. $table		=	table,
					2. $data	 	=	data (all data in one array)
					
					// Finally Insert data/value
					$wpdb->insert(
						$table,	// 1. table
						$data, 	// 2. data
					);
					
					# SQP sample,
						INSERT INTO '$table' ('Name', 'Roll_No', 'Reg_No', 'Class', 'Exam', 'Year') VALUES ('$Name', '$Roll_No', '$Reg_No', '$Class', '$Exam', '$Year')
				
				# Please note, here were assign data {$value_len} with a function 'educare_demo_data()' for automatically adjust users settings and csv files {$dataLen == $value_len}. but it's same to work above details.
				*/

				$error_found = false; 

				if ($chek_name and !in_array($chek_name, $keys) or $chek_roll and !in_array($chek_roll, $keys) or $chek_regi and !in_array($chek_regi, $keys)) $error_found = true;

				// display error msg if length != 19|$content_len
				if( $dataLen != $content_len or $error_found ) $error++;
				if( $error_found ) continue;
				// process to import the results/data if everything ok
				if( !($dataLen == $content_len) ) continue;
				// Assign default value/field as a variables
				$data = array_combine($keys, $csvData);
				
				if ($chek_name) {
					$Name = sanitize_text_field($data[$chek_name]);
				} else {
					$Name = 'Null';
				}
				if ($chek_roll) {
					$Roll_No = sanitize_text_field($data[$chek_roll]);
					$search_roll = " AND Roll_No='$Roll_No'";
				} else {
					$Roll_No = 'Null';
					$search_roll = "";
				}
				if ($chek_regi) {
					$Regi_No = sanitize_text_field($data[$chek_regi]);
					$search_regi = " AND Regi_No='$Regi_No'";
				} else {
					$Regi_No = 'Null';
					$search_regi = "";
				}

				// $Name = trim($data['Name']);
				// $Roll_No = trim($data['Roll_No']);
				// $Regi_No = trim($data['Regi_No']);

				$Class = sanitize_text_field($data['Class']);
				$Exam = sanitize_text_field($data['Exam']);
				$Year = sanitize_text_field($data['Year']);

				$Result = sanitize_text_field($data['Result']);
				$GPA = sanitize_text_field($data['GPA']);
				$Photos = sanitize_text_field($data['Photos']);
		
				// Check results already exists or not
				$search = "SELECT count(*) as count FROM {$table} where Class='$Class' AND Exam='$Exam' $search_roll $search_regi AND Year='$Year'";
				$results = $wpdb->get_results($search, OBJECT);
				
				// ignore old results if all ready exist
				if($results[0]->count==0) {
			
					// Check default data/field is empty or not
					if(!empty($Name) && !empty($Roll_No) && !empty($Regi_No) && !empty($Class) && !empty($Exam) && !empty($Year) ) {
						$Details = educare_array_slice($data, 'Year', 'Result');
						$Details['Photos'] = $Photos;
						$Details = json_encode($Details);
						$Subject = educare_array_slice($data, 'GPA', 'Photos');
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

						// Insert data/results into database table
						$wpdb->insert($table, $data);
						// display how many data is imported
						if ($wpdb->insert_id > 0) {
							$totalInserted++;
						}
					}
				} else {
					// display how many data is already exists
					$exist++;
				}
			}
			// print import process details
			echo "<div class='notice notice-success is-dismissible'><p>Total Results Inserted: <b style='color: green;'>".esc_html($totalInserted)."</b> results<br>Allredy Exist: <b>".esc_html($exist)."</b> results<br>Error to import: <b style='color: red;'>".esc_html($error)."</b> results<br>Successfully Imported: ".esc_html($totalInserted)." of ".esc_html($total)."</p></div>";
			
			if ($error) {
				echo educare_guide_for('import_error', $error);
			}
		} else {
			// notify users if empty files or invalid extension
			echo "<div class='notice notice-error is-dismissible'><p>";
			if(empty($_FILES['import_file']['name'])) {
				echo "No file chosen! Please select a files";
			} else {
				echo "Invalid extension. Files must be an <b>.csv</b> extension for import the results. Please choose a .csv files";
			}
			echo "</p></div>";
		}
	}
}

educare_import_result();
echo educare_guide_for('import');
?>

<!-- Import Form -->
<form  class="add_results" method="post" action="<?php esc_url($_SERVER['REQUEST_URI']); ?>" enctype="multipart/form-data">
<div class="content">
	<p>Files must be an <b>.csv</b> extension for import the results.</p>
	<input type="file" name="import_file">
	<select name="Class" class="form-control">
		<?php educare_get_options('Class', '');?>
	</select><br>
	<button class="educare_button" type="submit" name="educare_import_results"><i class="dashicons dashicons-database-import"></i> Import</button>
</div>
</form>
<br>

<div class='demo'>
	<strong>Optional Subject Selection Guide</strong>
	<p>Educare add 1 before optional subject marks <code>1 [space] Marks</code>.</p>
	<li style="font-size: small;">Exp: <code>1 85</code></li>
	<li style="font-size: small;">Here <code>1</code> 	= Define optional subject</li> 
	<li style="font-size: small;">and <code>85</code> 	= Marks</li>
	<p>In this way educare define and identify optional subjects. So, when you add a result to the csv files - you need to add <code>1</code> symbol before the optional subject marks.</p>
	
	<p>Select class for demo files:</p>
	
	<select id="Class" name="Class" class="form-control">
		<option value="">Select Class</option>
		<?php educare_get_options('Class', $Class);?>
	</select>

	<div id="result_msg"><br><p><a class='educare_button disabled' title='Download Import Demo.csv Error'><i class='dashicons dashicons-download'></i> Download Demo</a></p></div>

	<script>
	$(document).on("change", "#Class", function() {
		$(this).attr('disabled', true);
		var class_name = $('#Class').val();
		var id_no = $('#id_no').val();
		$.ajax({
				url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
				data: {
				action: 'educare_demo',
				class: class_name,
				id: id_no,
			},
				type: 'POST',
				success: function(data) {
					$('#result_msg').html(data);
					$('#Class').attr('disabled', false);
				},
				error: function(data) {
					$('#result_msg').html("<?php echo educare_guide_for('db_error')?>");
				},
		});
	});
	</script>
</div>
</div>

