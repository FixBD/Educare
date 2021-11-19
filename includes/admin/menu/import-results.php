<div class="educare_post imoport">
	<h1>Import Results</h1>

<?php

// Create functions for import demo
function educare_import_demo() {
	$view_data = file_get_contents(EDUCARE_DIR."assets/files/import_demo.csv");
	
	// for update database
	$add_data = educare_get_data('import_demo', '', '');
	
	// for store (save) database status to a files
	$data_path = EDUCARE_DIR."assets/files/import_demo.csv";
	
	// check if data is already exist/same or not. if data not exist or old, then update data. otherwise ignore it.
	if (!($add_data == $view_data)) {
		// process to update data
		if ( !file_exists("data") );
		// update data if any changed found
		$update_data = fopen($data_path, 'w'); 
		fwrite($update_data, $add_data);
		fclose($update_data);
	}
}
// finally, call function for create || update import_demo.csv files
educare_import_demo();



// Begin import results function
global $wpdb;

// Table name, where to import the results
$table = $wpdb->prefix."Educare_results";

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
	
		fgetcsv($csvFile); // Skipping header row
	
		// Read file
		while(($csvData = fgetcsv($csvFile)) !== FALSE) {
			$csvData = array_map("utf8_encode", $csvData);
			
			$total ++;
			
			// CSV row column length (based on import files)
			$dataLen = count($csvData);
			// $table row column length (based on the users settings)
			$content_len = count(educare_get_data('all_content', '', ''));
			
			/* =====( Explain )=====
			* 19 means $table rows/headers ($table structure).
			# Currently $table row (default) length is 19.
			# Notes: 19 only for example. Sometimes, It's may grow (+19) and reduce (-19) based on the users settings
			
			* For example:
				If users add any content, like Subject or Extra field it's (19) grow up and reduce if users delete any contents.
				
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
				2. $data	 	=	data (*array)
				
				// Finally Insert data/value
				$wpdb->insert(
		            $table,	// 1. table
					$data, 	// 2. data (array)
				);
				
				# SQP sample,
					INSERT INTO '$table' ('Name', 'Roll_No', 'Reg_No', 'Class', 'Exam', 'Year') VALUES ('$Name', '$Roll_No', '$Reg_No', '$Class', '$Exam', '$Year')
			
			# Please note, here were assign data {$data} with a function for automatically adjust users settings and csv files {$dataLen == $content_len}. but it's same to work above details.
			*/
			// display error msg if length != 19|$content_len
			if( $dataLen != $content_len ) $error++;
			// process to import the results/data if everything ok
			if( !($dataLen == $content_len) ) continue;
			// Assign default value/field as a variables
			$Name = trim($csvData[0]);
			$Roll_No = trim($csvData[1]);
			$Regi_No = trim($csvData[2]);
			$Class = trim($csvData[3]);
			$Exam = trim($csvData[4]);
			$Year = trim($csvData[5]);
	
			// Check results already exists or not
			$search = "SELECT count(*) as count FROM {$table} where Class='$Class' AND Exam='$Exam' AND Roll_No='$Roll_No' AND Regi_No='$Regi_No' AND Year='$Year'";
			$results = $wpdb->get_results($search, OBJECT);
			
			// ignore old results if all ready exist
			if($results[0]->count==0) {
		
				// Check default data/field is empty or not
				if(!empty($Name) && !empty($Roll_No) && !empty($Regi_No) && !empty($Class) && !empty($Exam) && !empty($Year) ) {
					
					/*
					# For manually =>
					$wpdb->insert($table, array(
			            'Name' =>$Name,
						'Roll_No' =>$Roll_No,
			            'Reg_No' =>$Reg_No,
			            'Class' => $Class,
						'Exam' => $Exam,
						'Year' => $Year
			          ));
					*/
					// Assign data with automated value - educare_get_data();
					$data = educare_get_data('import', $csvData, '');
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
		echo "<div class='notice notice-success is-dismissible'><p>Total Results Inserted: <b style='color: green;'>".esc_html($totalInserted)."</b> results<br>Allredy Exist: <b>".esc_html($exist)."</b> results<br>Error to import: <b style='color: red;'>".esc_html($error)."</b> results<br>Successfully Import: ".esc_html($total)." of ".esc_html($totalInserted)."</p></div>";
		
		if ($error) {
			echo educare_guide_for('import_error', $error);
		}
	} else {
		// notify users if empty files or invalid extension
		echo "<div class='notice notice-error is-dismissible'><p>";
		if(empty($_FILES['import_file']['name'])) {
			echo "No file chosen! please choose a files";
		} else {
			echo "Invalid extension. Files must be an <b>.csv</b> extension for import the results. Please choose a .csv files";
		}
		echo "</p></div>";
	}
}

echo educare_guide_for('import');
?>

<!-- Import Form -->
<form  class="add_results" method="post" action="<?php esc_url($_SERVER['REQUEST_URI']); ?>" enctype="multipart/form-data">
	Files must be an <b>.csv</b> extension for import the results.
	<input type="file" name="import_file">
	<button class="educare_button" type="submit" name="educare_import_results"><i class="dashicons dashicons-database-import"></i> Import</button>
</form>
<br>

<div class='demo'>
	<p>Notes: Please carefully fill out all the details of your import (<b>.csv</b>) files. If you miss one, you may have problems to see the results. So, verify the student's admission form well and then give all the details in your import files. Required field are: <b><i>Name, Roll No, Regi No, Exam, Class and Year</i></b>. So, don't miss all of this required field!</p>
	
	<p>This is an example of import <b>demo.csv</b> files, based on your current settings (<i>Subject, Exam, Extra field...</i>).
	If problem to download the flies, you can manually get this file in dir: <?php echo esc_url(EDUCARE_DIR.'assets/files/import_demo.csv');?></p>
	
	<p><a class='educare_button' href="<?php echo esc_url(EDUCARE_URL.'assets/files/import_demo.csv');?>" title="Download Import Demo.csv"><i class='dashicons dashicons-download'></i> Download Demo</a></p>
</div>

</div>