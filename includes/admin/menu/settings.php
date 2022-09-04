<?php

/**
	============================================================
		=======================( Test Section )=======================
		=======================( Backup Test )=======================
						It will help you and other developers. Especially, beginners
	=============================================================


	## String to object sample
	## Educare using this method to configure settings
	## Main function: json_encode() and json_decode()

	* @return object
	* exp: =>

	$settings = ["delete_subject"=>"checked", "clear_field"=>"unchecked"];
	$data = json_encode($settings);
	$status = json_decode($data);

	// Preview return
	print_r($status);
	echo esc_html($status->clear_field);

	************************************( Next )***********************************

	## Search and remove array content
	## Educare using this method to Remove the content
	## Main function:
		* 1. array_search() for search array content
		* 2. unset() for remove content
		* 3. array_values() for reindex array content/key

	* @return array
	* exp: =>

	$data = array('English', 'Mathematics', 'Chemistry', 'Biology', 'ICT');
	$target = 'Mathematics';
	if (($target = array_search($target, $data)) != false) {
			unset($data[$target]);
		// currently $data index/key is => 0, 2, 3, 4
		// reindex $data
			$data = array_values($data);
		// Now $data index/key is => 0, 1, 2, 3
	}

	// Preview return
	print_r($data);

	************************************( Next )***********************************

	## discover function cleanData() for cleaning array data for specific characters
	## Educare using this method to configure Extra fields content or Optional Subject
	## Main function: preg_match()

	* @return array
	* exp: =>

	$data = Array (
		0 => "text 1",
		1 => "x text 2",
		2 => "text 3",
		3 => "x text 4",
		4 => "text 5",
	);

	// create function for clean data
	function cleanData($data){ 
			$clean = array();
			foreach($data as $val){ 
				// for global match: /[x 2]/
					preg_match ('/x /', $val, $matches); 
					if(count($matches) == 0) $clean[] = $val;
			}
			return $clean;
	}

	// Preview return
	print_r(cleanData($data));
	// output: Array ( [0] => text 1 [1] => text 3 [2] => text 5 )

	************************************( Next )***********************************

	## Similar to above

	$data = array(1,3,4,1,3,1,5,8,9,10);
	$clean = array();

	foreach($data as $value) {
		if($value=='3') {
					continue;
			} else {
					$clean[]=$value;
			}     
	}

	print_r($clean);

	************************************( Next )***********************************

	## Getting first and last word
	## Educare using this method to detect (forms) field type
	* @return string
	* exp: =>

	$target = "text Fathers Name";
						1. type	2. name
	// $target = "number Mobile Number";

	// sclice/remove first world
	// for replace all white space
	// $name = str_replace(' ', '_', $name);
	Get Last Word
	// $name = substr(strstr($target, ' '), 1);

	// getting first world
	$type = strtok($target, ' ');

	echo "
		input name: ".esc_html($name)."<br>
		input type: ".esc_html($type)."<br>
	";

	// more example:
	echo "<input type='".esc_attr($type)."' name='".esc_attr($name)."' placeholder='Type students ".esc_attr($name)."'>";

	## Notes: Change $target (1) value number to text, email, file, date...


######################( End Buckup )######################
*/



/** ====================( Functions Details )=====================
	
	### Notify Users
	* Usage example: educare_default_notice('update', $old_data, $target);

	* @since 1.0.0
	* @last-update 1.0.0
	
	* @param string $notice			Detect notice type
	* @param string $default	  Default data
	* @param string $where			Where {Subject, Exam, Class, Year, Extra field}
	
	* @return null|HTML

	* Notify (warning) user when force to add/edit/remove default content.
	('id', 'subject', 'class', 'exam', 'name', 'gpa', 'status', 'regi no', 'roll no');
	
	* for example, when users force to add/edit/remove any default content like - Name. this function notify the users like this - You can't Add/Edit/Remove (Name). So, It's not possible to add/edit/remove Name.
	
==================( function for notify users )================== **/


function educare_default_notice($notice,$default,$where) {
	
	$default_data = array('id', 'name', 'regi no', 'roll no', 'class', 'exam', 'gpa', 'result', 'photos', 'year');
	
	ob_start();
	for ($i = 0; $i < count($default_data); $i++) {
		echo "".strtoupper(esc_html($default_data[$i])).", ";
	}
	$data = ob_get_clean();
	
	$list = 'list';
	
	if ($notice == 'add') {
		$in = 'in';
	}
	if ($notice == 'update') {
		$in = 'to';
		$list = '';
	}
	if ($notice == 'remove') {
		$in = 'from';
	}
	$notice = "".esc_html($notice)." <b>".esc_html($default)."</b> ".esc_html($in)." <b>".strtoupper(esc_html($where))."</b> ".esc_html($list)."";
	
	echo "<div class='notice notice-error is-dismissible'><p>Notes: You can't Add/Edit/Remove (<i>".esc_html($data)."</i>). So, It's not possible to ".wp_kses_post($notice).". Please visit <a href='#plugin_forum'>Plugin Forum</a> for more information. Thanks</p></div>";
}



/** ====================( Functions Details )=====================
	
	### Add, Updata or Remove Data
	* Usage example: educare_settings('Settings');

	* @since 1.0.0
	* @last-update 1.2.0
	 
	* @param string $list	Select database
	* @return null|HTML 

	* Add / Update / Remove - Subject, Exam, Class, Year, Extra field... and settings status.
	
	* this is a main function for update all above (Settings) content. it's decide which content need to Add / Update / Remove and where to store Data into database.
	*
	* this function also provide - Error / Success notification when users Add / Update / Remove any Data.
	
	* it's make temporary history data for notify the users. 
	
	* for example, when users update a Subject like - Science to Biology. this function notify the users like this - Successfully update Subject (Science) to (Biology).
	
==================( function for Add, Updata or Remove Data )================== **/

function educare_settings($list) {
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
   
	$search = $wpdb->get_results("SELECT * FROM $table WHERE list='$list'");
	
	if ($search) {
		foreach ( $search as $print ) {
			$data = $print->data;
			$id = $print->id;
		}
		
		$data = json_decode($data);
		
		/* Convert index to associative array (number to name)
		and ignore Settings data, because our settings data is not array, it's an object. so, its not possible to combination [with: array_combine() func] between array and object. so, ignore Settings {object} to combine array. Otherwise, it's will give an error!
		*/
		$display_data = '';
		if ($list != 'Settings') {
			$display_data = array_combine($data, $data);
		}
	
		// for add list items
		if (isset($_POST['educare_add_'.$list.''])) {
			
			$in_list = $list;
			// remove all _ characters from the list (normalize the $list)
			$list = str_replace('_', ' ', $in_list);
			
			$target = sanitize_text_field($_POST[$in_list]);
			// $target = str_replace('_', ' ', $target);
			
			if (empty($target)) {
				?>
					<div class="notice notice-error is-dismissible"> 
						<p>You must fill the form for add the <b><?php echo esc_html($list);?></b>. thanks</p>
					</div>
				<?php
			
			} else {
				
				$y = array();
				if (isset($_POST["educare_add_Extra_field"])) {
					$unique_target = strtolower(substr(strstr($target, ' '), 1));

					for ($i = 0; $i < count($data); $i++) {
						$x = strtolower(substr(strstr($data[$i], ' '), 1));
						$y[] = $x;
					}

				} else {
					$unique_target = strtolower($target);

					for ($i = 0; $i < count($data); $i++) {
						$x = strtolower($data[$i]);
						$y[] = $x;
					}

				}

				$unique_data = $y;
				
				if (in_array($unique_target, $unique_data)) {
					echo '<div class="notice notice-error is-dismissible"><p>'.esc_html($list).' <b>'.esc_html($unique_target).'</b> is allready exist!</p></div>';
				} else {
					
					$data = array_unique($data);
					array_push($data, $target);
					
					$wpdb->update(
						$table, 			//table
						array( 				// data
							"data" => json_encode($data)
						),
					
						array( 				//where
							'ID' => $id
						)
					);
					
					// for hide extra field type
					if (isset($_POST["educare_add_Extra_field"])) {
						// $type = strtok($target, ' ');
						$target = substr(strstr($target, ' '), 1);
					}

					echo '<div class="notice notice-success is-dismissible"><p>Successfully Added <b>'.esc_html($target).'</b> at the '.esc_html($list).' list<br>Total: <b>'.esc_html(count($data)).'</b> '.esc_html($list).' added</p></div>';
				}
			}
		}
		
		if (isset($_POST['educare_edit_'.$list.''])) {
			
			$in_list = $list;
			// remove all _ characters from the list (normalize the $list)
			$list = str_replace('_', ' ', $in_list);
			
			$target = sanitize_text_field($_POST[$in_list]);
			$target = str_replace('_', ' ', $target);
			
			$check = strtolower($target);
			
			if ($in_list == 'Extra_field') {
				$check = strtolower(substr(strstr($check, ' '), 1));
			}
						
			if ($in_list == 'Extra_field') {
				$data_type = strtok($target, ' ');
				$Target = substr(strstr($target, ' '), 1);
				
				?>
				<div class="notice notice-success is-dismissible">
					<p>
					<center><h2>Edit <?php echo esc_html($list);?><h2></center>
					
					<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post" class="educare_update_data">

						<input type="hidden" name="remove" value="<?php echo esc_attr($target);?>"/>
						<input type="hidden" name="old_data" value="<?php echo esc_attr($target);?>"/>
						Edit - <b><?php echo esc_html($Target);?></b>:
						
						<div class="select add-subject">
							<div>
								<p>Name:</p>
								<input type="text" name="field" class="fields" value="<?php echo esc_attr($Target);?>" placeholder="<?php echo esc_attr($Target);?>">
							</div>

							<div>
								<p>Select type::</p>
								<select name="type">
									<option value="text" <?php if ( $data_type == "text") { echo "selected";}?>>Text</option>
									<option value="number" <?php if ( $data_type == "number") { echo "selected";}?>>Number</option>
									<option value="date" <?php if ( $data_type == "date") { echo "selected";}?>>Date</option>
									<option value="email" <?php if ( $data_type == "email") { echo "selected";}?>>Email</option>
								<select>
							</div>
						</div>
								
						<input type="text" name="<?php echo esc_attr($in_list);?>" hidden>
						<script>
							function add(form) {
								$type = form.type.value;
								$field = form.field.value
								if (!$field == 0) {
									form.Extra_field.value = $type+ " " +$field;
								}
							}
						</script>
				
						<input type="submit" name="educare_update_<?php echo esc_attr($list);?>" class="educare_button" onClick="<?php echo esc_js('add(this.form)');?>" value="&#xf464 Edit">
				
						<input type="submit" name="<?php echo esc_attr("remove_$list");?>" class="educare_button" value="&#xf182" <?php educare_confirmation($list, $Target);?>>

					</form>
					</p>
				</div>	
				<?php
			} else {
				?>
				<div class="notice notice-success is-dismissible">
					<p>
					<center><h2>Edit <?php echo esc_html($list);?><h2></center>
					<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
						<input type="hidden" name="remove" value="<?php echo esc_attr($target);?>"/>
						<input type="hidden" name="old_data" value="<?php echo esc_attr($target);?>"/>
						Edit - <b><?php echo esc_html($target);?></b>:<br>
						<label for="Name" class="labels" id="name"></label>
							<input type="text" name="<?php echo esc_attr($list);?>" value="<?php echo esc_attr($target);?>" placeholder="<?php echo esc_attr($list);?> Name">
					
						<input type="submit" name="educare_update_<?php echo esc_attr($list);?>" class="educare_button" value="&#xf464 Edit">
							
						<input type="submit" name="<?php echo esc_attr("remove_$list");?>" class="educare_button" value="&#xf182" <?php educare_confirmation($list, $target);?>>
													
					</form>
					</p>
				</div>	
			<?php
			}
		}
		
		// for update list content
		if (isset($_POST['educare_update_'.$list.''])) {
			
			$in_list = $list;
			// remove all _ characters from the list (normalize the $list)
			$list = str_replace('_', ' ', $in_list);
			
			$old_data = sanitize_text_field($_POST['old_data']);
			$target = sanitize_text_field($_POST[$in_list]);
			$target = str_replace('_', ' ', $target);
			
			if (empty($target)) {
				echo '<div class="notice notice-error is-dismissible"><p>Sorry, its not possible to update empty field. You must fill the form for update the <b>'.esc_html($list).'</b>. thanks</p></div>';
			} else {
				
				if (isset($_POST["educare_update_Extra_field"])) {
					$old_type = strtok($old_data, ' ');
					$old_content = strtolower(substr(strstr($old_data, ' '), 1));
					
					$target_type = strtok($target, ' ');
					$target_content = strtolower(substr(strstr($target, ' '), 1));
					
					$old = substr(strstr($old_data, ' '), 1);
					$new = substr(strstr($target, ' '), 1);
					
					$unique_target = strtolower(substr(strstr($target, ' '), 1));
					for ($i = 0; $i < count($data); $i++) {
						$x = strtolower(substr(strstr($data[$i], ' '), 1));
						$y[] = $x;
					}

				} else {

					$old_type = false;
					$old_content = strtolower($old_data);
					
					$target_type = false;
					$target_content = strtolower($target);
					
					$old = $old_data;
					$new = $target;
						
					$unique_target = strtolower($target);
					for ($i = 0; $i < count($data); $i++) {
						$x = strtolower($data[$i]);
						$y[] = $x;
					}

				}
				
				$unique_data = $y;
				
				/* for test
				echo "old_type : ".esc_html($old_type)."<br>old_content : ".esc_html($old_content)."<br>target_type : ".esc_html($target_type)."<br>target_content : ".esc_html($target_content)."<br>";
				*/
				
				$exist = "<div class='notice notice-error is-dismissible'><p>Update failed. Because,  <b>".esc_html($new)."</b> is allready exist in ".esc_html($list)." list. Please try a different <i>(unique)</i> one!</p></div>";
				
				
				// getting the key where we need to update data
				$update_key = array_search($old_data, $data);
				$data[$update_key] = $target;
				// make it unique
				$data = array_unique($data);
				
				function update_data($wpdb, $table, $old, $new, $data, $id, $msgs) {
					echo wp_kses_post($msgs);
					
					$wpdb->update(
			      $table,
						array( 
							"data" => json_encode($data)
					  ),
					
			      array(
							'ID' => $id
						)
					);
				}
				
				$target_content = strtolower($target_content);
				
				if ( $old_type == $target_type or $old_content == $target_content) {
					$msg = "<div class='notice notice-error is-dismissible'><p>There are no changes for updates!</p></div>";
				}
				
				if ( $old_type != $target_type and $old_content == $target_content) {
					// $msgs = "Change $old_type to $target_type";
					$msgs = "<div class='notice notice-success is-dismissible'><p>Succesfully update ".esc_html($list)." ".esc_html($new)." type <b class='error'>".esc_html($old_type)."</b> to <b class='success'>".esc_html($target_type)."</b>.</p></div>";
					$msg = update_data($wpdb, $table, $old, $new, $data, $id, $msgs);
				}
				
				if ( $old_type == $target_type and $old_content != $target_content) {
					if (in_array($target_content, $unique_data)) {
						return $exist;
					} else {
						// $msgs = "Change $old_content to $target_content";
						$msgs = "<div class='notice notice-success is-dismissible'><p>Succesfully update ".esc_html($list)." <b class='error'>".esc_html($old)."</b> to <b class='success'>".esc_html($new)."</b>.</p></div>";
						$msg = update_data($wpdb, $table, $old, $new, $data, $id, $msgs);
					}
				}
				
				if ( $old_type != $target_type and $old_content != $target_content) {
					if (in_array($target_content, $unique_data)) {
						return $exist;
					} else {
						// $msgs = "Full Update: Change $old_content to $target_content and also Change old type $old_type to $target_type ";
						$msgs = "<div class='notice notice-success is-dismissible'><p>Succesfully update ".esc_html($list)." <b class='error'>".esc_html($old)."</b> to <b class='success'>".esc_html($new)."</b>. also changed type <b class='error'>".esc_html($old_type)."</b> to <b class='success'>".esc_html($target_type)."</b>.</p></div>";
						$msg = update_data($wpdb, $table, $old, $new, $data, $id, $msgs);
					}
				}
				
				return $msg;
			}
		}
		
		
		// for remove list items
		if (isset($_POST["remove_$list"])) {
			
			$in_list = $list;
			// remove all _ characters from the list (normalize the $list)
			$list = str_replace('_', ' ', $in_list);
			
			$target = sanitize_text_field($_POST["remove"]);
			$target = str_replace('_', ' ', $target);
			$check = strtolower(str_replace('_', ' ', $target));
			
			if (isset($_POST["remove_Extra_field"])) {
				$check = substr(strstr($check, ' '), 1);
			}
					
			if (in_array($target, $display_data)) {
					unset($display_data[$target]);
					$display_data = array_values($display_data);
				
				$wpdb->update(
					$table, 				//table
					array( 					// data
						"data" => json_encode($display_data)
					),
				
					array( 					//where
						'ID' => $id
					)
				);
				
				// for hide extra field type
				if (isset($_POST["remove_Extra_field"])) {
					$target = substr(strstr($target, ' '), 1);
					$status = educare_check_status('clear_field');
				}
				if (isset($_POST["remove_Subject"])) {
					$status = educare_check_status('delete_subject');
				}
				
				echo '<div class="notice notice-success is-dismissible"><p>Successfully removed <b>'.esc_html($target).'</b> from the '.esc_html($list).' list.</p></div>';
			} else {
				echo '<div class="notice notice-error is-dismissible"><p>Sorry, '.esc_html($list).' <b>'.esc_html($target).'</b> is not found!</p></div>';
			}
		}
		
		if ($list == 'Settings') {
			if (isset($_POST['educare_reset_default_settings'])) {
				$wpdb->query("DELETE FROM $table WHERE id = $id");
				
				educare_default_settings('');
				
				echo "<div class='notice notice-success is-dismissible'> <p>Successfully reset default <b>settings</b></p></div>";
			}
			
			if (isset($_POST['educare_update_settings_status'])) {
				echo "<div class='notice notice-success is-dismissible'><p>Successfully updated Settings</p></div>";
			}
			
			if ( isset( $_POST['educare_default_photos'] ) && isset( $_POST['educare_attachment_id'] ) ) {
					echo "<div class='notice notice-success is-dismissible'><p>Successfully updated default students photos</p></div>";
			}
		}
	}
}

// Pack all in one
function educare_get_settings() {
	echo educare_settings('Settings');
	echo educare_settings('Exam');
	echo educare_settings('Year');
	echo educare_settings('Extra_field');
}



/** ====================( Functions Details )======================
	
	### Settings Status

	* Usage example: educare_settings_status($target, $title, $comments);

	* @since 1.0.0
	* @last-update 1.2.0
	
	* @param string $target					Select settings status
	* @param string $title					Display settings title
	* @param string $comments				Settings informations
	
	* @return void|HTML

	* One more exp: educare_settings_status('confirmation', 'Delete confirmation', "Anable and disable delete/remove confirmation");
	
	* Anable or Disable Settings status
	* Display toggle switch to update status
	
	* it's return radio or input. so, always call function under form tags. Exp: 
	<form class="educare-update-settings" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
	<?php
		
	educare_settings_status('delete_subject', 'Automatically Delete Subject', "Automatically Delete Subject from Results Table When You Delete Subject From Subject List?");
	
	educare_settings_status('clear_field', 'Delete and Clear field data', "Tips: If you set <b>No</b> that's mean only field will be delete. And, if you set <b>Yes</b> - clear field data when you delete any (current) field. Delete and Clear field data?");
	
	educare_settings_status('confirmation', 'Delete confirmation', "Anable and disable delete/remove confirmation");
	
	educare_settings_status('guide', 'Guidelines', "Anable and disable guide/help messages");
	
	?>
	<input type="submit" name="educare_update_settings_status" class="educare_button" value="&#x464 Update">
	</form>
	
==============( Settings Status Display & Update )================ **/

function educare_settings_status($target, $title, $comments) {
	
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
   
	$search = $wpdb->get_results("SELECT * FROM $table WHERE list='Settings'");
	
	if ($search) {
		
		foreach ( $search as $print ) {
			$data = $print->data;
			$data = json_decode($data);
			$id = $print->id;
		}
		// for update settings status
		if (isset($_POST['educare_update_settings_status'])) {
			$status = 'unchecked';
			if (property_exists($data, $target)) {
				$status = $data->$target;
			}

			if ($target != 'display') {
				$update_data = sanitize_text_field($_POST[$target]);
			}

			if ($target == 'display') {
				$no = 0;
				
				foreach ($status as $key => $value) {
					$status->$key[0] = array_map( 'sanitize_text_field', $_POST['display_input'] )[$no++];
					$status->$key[1] = sanitize_text_field($_POST[$key]);
				}
				
				$update_data = $status;
			}

			$data->$target = $update_data;
			// now update desired data
			$wpdb->update(
	      $table, 				//table
				array( 					// data - we need to encode our data for store array/object into database
					"data" => json_encode($data)
			  ),
	      array( 					//where
					'ID' => $id
				)
			);
		}
	
		$status = 'unchecked';
		if (property_exists($data, $target)) {
			$status = $data->$target;
		}
		// $clear_field = $data->clear_field;
		// for input field
		if ( $target == 'results_page' or $target == 'optional_sybmbol' ) {
			echo "<div class='educare-settings'>";
			echo "<div class='title'>
			<h3>".esc_html($title)."<h3>
			<p class='comments'>".wp_kses_post($comments)."</p>
			<input type='text' id='".esc_attr($target)."' name='".esc_attr($target)."' value='".esc_attr(educare_check_status($target))."' placeholder='".esc_attr(educare_check_status($target))."'>
			</div></div>";
		}
		elseif ($target == 'display') {

			$display = $status;

			foreach ($display as $key => $value) {
				$target = $key;
				$field_name = $value[0];
				$status = $value[1];
				if ($key == 'Class' or $key == 'Exam' or $key == 'Year') {
					$info = '<div class="action_menu"><i class="dashicons action_button dashicons-info"></i> <menu class="action_link info">';
					if ($key == 'Class') {
						$info .= 'If you want to disable the class from <b>View Results</b> and <b>Front-End</b> search form, you can disable it. But you need to fill in the class while adding or importing results.';
					}
					if ($key == 'Exam') {
						$info .= 'If you want to disable the exam from <b>View Results</b> and <b>Front-End</b> search form, you can disable it. But you need to fill in the exam while adding or importing results.';
					}
					if ($key == 'Year') {
						$info .= "<span class='error'>You can't disable year.</span> But, you can rename it. like - Passing Year, Exam Year...";
					}
					$info .= '</menu></div>';
				} else {
					$info = '';
				}

				?>
				<div class="educare-settings">
					<div class="title">
						<h3><?php echo esc_html(ucwords(str_replace('_', ' ', $target))) . ' ' . wp_kses_post( $info );?><h3>
						<p class="comments">
							<input type='text' id='<?php echo esc_attr($target);?>' name='display_input[]' value='<?php echo esc_attr($field_name);?>' placeholder='Type <?php echo esc_attr($field_name);?>'>
						</p>
					</div>
					
					<div class="status-button">
						<div class="switch-radio">
							<?php if ($key != 'Year') {
								?>
								<input type="radio" id="<?php echo esc_attr($target);?>_no" name="<?php echo esc_attr($target);?>" value="unchecked" <?php if ($status == 'unchecked') { echo 'checked';};?>/>
								<label for="<?php echo esc_attr($target);?>_no">No</label>
								<?php
							}?>
							
							<input type="radio" id="<?php echo esc_attr($target);?>_yes" name="<?php echo esc_attr($target);?>" value="checked" <?php if ($status == 'checked') { echo 'checked';};?>/>
							<label for="<?php echo esc_attr($target);?>_yes">Yes</label>
						</div>
					</div>
				</div>

				<script>
					$(document).ready(function(){
						$("input[name='Roll_No']").click(function() {
							// alert($(this).val());
							if ($(this).val() == 'checked') {
								$('#Regi_No_no').attr("disabled",false);
								// alert('checked!');
							}
							else {
								// $('#Regi_No_no').attr("disabled",true);
								$("input[name='Regi_No']").prop("checked", true);
							}
						});

						$("input[name='Regi_No']").click(function() {
							// alert($(this).val());
							if ($(this).val() == 'checked') {
								$('#Roll_No_no').attr("disabled",false);
								// alert('checked!');
							}
							else {
								// $('#Roll_No_no').attr("disabled",true);
								$("input[name='Roll_No']").prop("checked", true);
							}
						});
					});
				</script>
				<?php
			}
		} else {
			// for radio button
			?>
			<div class="educare-settings">
				<div class="title">
					<h3><?php echo esc_html($title);?><h3>
					<p class="comments"><?php echo wp_kses_post($comments);?></p>
				</div>
				
				<div class="status-button">
					<div class="switch-radio">
						<input type="radio" id="<?php echo esc_attr($target);?>_no" name="<?php echo esc_attr($target);?>" value="unchecked" <?php if ($status == 'unchecked') { echo 'checked';};?>/>
						<label for="<?php echo esc_attr($target);?>_no">No</label>
						
						<input type="radio" id="<?php echo esc_attr($target);?>_yes" name="<?php echo esc_attr($target);?>" value="checked" <?php if ($status == 'checked') { echo 'checked';};?>/>
						<label for="<?php echo esc_attr($target);?>_yes">Yes</label>
					</div>
				</div>
			</div>
			<?php
		}
	} else {
		echo educare_guide_for('db_error');
	}
}



/** ====================( Functions Details )======================
	
	### Display Content
	* Usage example: educare_content('Exam');
	
	* @since 1.0.0
	* @last-update 1.0.0
	
	* @param string $list	Exam, Year, Extra field
	* @return void|HTML

	* Display Content - Subject, Exam, Class, Year Extra field...
	
=================( function for Display Content  )================ **/

function educare_content($list) {
	
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
	// remove all _ characters from the list (normalize the $list)
	$List = str_replace('_', ' ', $list);
	// section head
	echo '<h3 id ="'.esc_attr($list).'">'.esc_html($List).' List</h3>';
   
	$search = $wpdb->get_results("SELECT * FROM $table WHERE list='$list'");
	
	if ($search) {
		
		foreach ( $search as $print ) {
			$target = $print->data;
			$target = json_decode($target);
		}
		
		if ($target) {
			
			ob_start();
			$count = 0;
			for ($i = 0; $i < count($target); $i++) {
				$content = $target[$i];
				// for ignore extra field type
				$Content = $target[$i];
				$type_th = '';
				$type_td = '';
				
				if ($list == 'Extra_field') {
					$get_type = strtok($content, ' ');
					$Content = substr(strstr($content, ' '), 1);
					$type_th = "<th>Type</th>";
					$type_td = "<td><span class='type ".esc_attr($get_type)."'></span></td>";
				}
				
				if ($list) {
				?>
					<tr>
						<td><?php echo esc_html(++$count);?></td>
						<td><b><?php echo esc_html($Content);?></b></td>
						<?php echo wp_kses_post($type_td);?>
						<td colspan='2'><form class="educare-modify" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
							
						<input type="hidden" name="remove" value="<?php echo esc_attr($content);?>"/>

						<input type="hidden" name="<?php echo esc_attr($list);?>" value="<?php echo esc_attr($content);?>"/>
							
							<input type="submit" name="educare_edit_<?php echo esc_attr($list);?>" class="button success" value="&#xf464">
							
							<input type="submit" name="<?php echo esc_attr("remove_$list");?>" class="button error" value="&#xf182" <?php educare_confirmation($list, $content);?>>
						    	
						</form></td>
					</tr>
				<?php
				
				} // end if ($list)
			}
			
			$target = ob_get_clean();
		}
		
		
		if (!empty($target)) {
		?>
		<table class='educare_add_content'>
			<thead>
				<tr>
					<th>No</th>
					<th width='100%'><?php echo esc_html($List);?></th>
					<?php echo wp_kses_post($type_th);?>
					<th>Edit</th>
					<th>Delete</th>
				</tr>
			</thead>
			<tbody>
				<?php echo wp_check_invalid_utf8(str_replace('_', ' ', $target));?>
			</tbody>
		</table>
		<?php
		} else {
		?>
 		 <div class="notice notice-error is-dismissible"> 
				<p>You don't have added any <b><?php echo esc_html($List);?></b> yet. Please add a <?php echo esc_html($List);?> by using this forms</p>
		</div>
		<?php
		}
		
		if ($list == 'Extra_field') {
			?>
			<form class="add_results" action="" method="post" id="educare_add_<?php echo esc_attr($list);?>">
			<div class="content">
				<div class="select add-subject">
					<div>
						<p>Name:</p>
						<input type="text" name="field" class="fields" placeholder="<?php echo esc_attr($List);?> name" pattern="[A-Za-z0-9 ]+" title="Only Caretaker, Number and Space allowed. (A-Za-z0-9)">
					</div>
					
					<div>
						<p>Select type:</p>
						<select name="type">
							<option value="text">Text</option>
							<option value="number">Number</option>
							<option value="date">Date</option>
							<option value="email">Email</option>
						<select>
					</div>
				</div>
				
				<input type="text" name="<?php echo esc_attr($list);?>" hidden>
				<script>
					function add(form) {
						$type = form.type.value;
						$field = form.field.value
						if (!$field == 0) {
							form.Extra_field.value = $type+ " " +$field;
						}
					}
				</script>
		    	
					
				<button id="educare_results_btn" class="educare_button" name="educare_add_<?php echo esc_attr($list);?>" type="submit" onClick="<?php echo esc_js('add(this.form)');?>"><i class="dashicons dashicons-plus-alt"></i> Add <?php echo esc_html($List);?></button>
			</div>
			</form>
			<br>
			<?php
			
		} else {
			
			?>
			<form class="add_results" action="" method="post" id="educare_add_<?php echo esc_attr($list);?>">
				<div class="content">
		    	<?php echo esc_html($List);?>:
		    	<label for="<?php echo esc_attr($list);?>" class="labels" id="<?php echo esc_attr($list);?>"></label>
		    	<input type="text" name="<?php echo esc_attr($list);?>" class="fields" placeholder="<?php echo esc_attr($List);?> name" pattern="[A-Za-z0-9 ]+" title="Only Caretaker, Number and Space allowed. (A-Za-z0-9)">
					
					<button id="educare_results_btn" class="educare_button" name="educare_add_<?php echo esc_attr($list);?>" type="submit"><i class="dashicons dashicons-plus-alt"></i> Add <?php echo esc_html($List);?></button>
				</div>
			</form>
			<br>
			<?php
		}
		
	}
}

// Pack all in one
function educare_get_content() {
	educare_content('Exam');
	educare_content('Year');
	educare_content('Extra_field');
}



/** ====================( Functions Details )======================
	
	### Replace Specific Array Key
	* Usage example: $educare_replace_key = replace_key($array, 'b', 'e');
	
	* @since 1.0.0
	* @last-update 1.0.0
	
	* @param array $array						Where to replace key
	* @param string|int $old_key 		key to replace
	* @param string|int $new_key 		peplace old key to new key

	* @return array
	
=================( function for Replace Key  )================ **/

function educare_replace_key($array, $old_key, $new_key) {
	$keys = array_keys($array);
	if (false === $index = array_search($old_key, $keys, true)) {
			throw new Exception(sprintf('Key "%s" does not exist', $old_key));
	}
	$keys[$index] = $new_key;
	return array_combine($keys, array_values($array));
}



/** ====================( Functions Details )======================
	
	### Class wise Jubject
	* Usage example: educare_setting_subject('Subject');
	
	* @since 1.2.0
	* @last-update 1.2.0
	
	* @param string|mixed $list			select specific data
	
	* @return void

	* This is a most important function of educare. Because, additing this function its possible to add different grading rules. here some necessity of this function given below:
		1. Sossible to add class wise subject
		2. Sossible to add different grading systems
		3. Possible to manage or modify grading systems
		4. Macking Educare_results database unique
		5. Make database clean
		and much more...............
	
=================( function for Class wise Jubject  )================ **/

function educare_setting_subject($list) {
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";
	
	// add subject/extra field to (database) results table
	$Educare_results = $wpdb->prefix . 'educare_results';
   
	$search = $wpdb->get_results("SELECT * FROM $table WHERE list='Class'");
	
	if ($search) {
		foreach ( $search as $print ) {
			$data = $print->data;
			$id = $print->id;
		}
		
		$data = json_decode($data, true);
		
		// for add list items (Subject)
		if (isset($_POST['educare_process_class'])) {
			// geting form data and store as a var
			if (isset($_POST['edit_class']) or isset($_POST['update_class']) or isset($_POST['remove_class']) or isset($_POST['add_class'])) {
				$target = sanitize_text_field($_POST['class']);
			} else {
				$target = sanitize_text_field($_POST['subject']);
			}

			if (isset($_POST['update_class'])) {
				$class = sanitize_text_field($_POST['old_class']);
			} else {
				$class = sanitize_text_field($_POST['class']);
			}

			// Check if selected class exist or not, if exist then apply this logic
			if (key_exists($class, $data)) {
				// Choice selected grouo

				if (isset($_POST['add_class'])) {
					echo '<div class="notice notice-error is-dismissible"> <p><b> '.esc_html( $target ).'</b> is allready exist in class list</p></div>';
				}

				if (isset($_POST['edit_class'])) {
					$subject_list = array();
					foreach ($data as $key => $value) {
						$subject_list[$key] = $key;
					}
				} else {
					$subject_list = $data[$class];
				}
				
				// check if subject field is empty or not
				if (empty($target)) {
					echo '<div class="notice notice-error is-dismissible"> <p>You must fill the form for add the <b>'.esc_html($list).'</b>. thanks</p></div>';
				} else {
					$search_terget = in_array(strtolower($target), array_map('strtolower', $subject_list));
					
					if (isset($_POST['update_class']) or isset($_POST['add_class'])) {
						$search_terget = key_exists(strtolower($target), $data);
					}

					$process = true;
					$msg = '';

					// check if subject exist or not
					if ($search_terget) {
						if (isset($_POST['add_subject']) or isset($_POST['update_subject']) or isset($_POST['add_class']) or isset($_POST['update_class'])) {
							// if add_Subject
							$process = false;
							$update_subject = false;
							if (isset($_POST['update_subject'])) {
								$old_subject = strtolower(sanitize_text_field( $_POST['old_subject'] ));
								$old_class = strtolower(sanitize_text_field( $_POST['old_class'] ));

								if (strtolower($target) == $old_subject and strtolower($class) == $old_class) {
									$update_subject = true;
								}
							}

							if (isset($_POST['update_class'])) {
								$old_class = strtolower(sanitize_text_field( $_POST['old_class'] ));

								if (strtolower($target) !== $old_class) {
									$update_subject = true;
								}
							}

							if ($update_subject) {
								$msg = '<div class="notice notice-error is-dismissible"> <p>There are no change for update</p></div>';
							} else {
								$msg = '<div class="notice notice-error is-dismissible"> <p><b> '.esc_html( $target ).'</b> is allready exist in class '.esc_html( $class ).'</p></div>';

								if (isset($_POST['update_class'])) {
									$msg = '<div class="notice notice-error is-dismissible"> <p><b> '.esc_html( $target ).'</b> is allready exist in class list</p></div>';
								}
								
							}
						}
						elseif (isset($_POST['edit_subject'])) {
							?>
							<form class="notice notice-success is-dismissible add_results" action="" method="post">
	
								Edit <?php echo esc_html($list);?>:
								<input type="hidden" name="educare_process_class">
								<input type="hidden" name="old_subject" value="<?php echo esc_attr($target);?>">
								<input type="hidden" name="old_class" value="<?php echo esc_attr($class);?>">
								<input type="text" name="subject" class="fields" value="<?php echo esc_attr($target);?>" placeholder="<?php echo esc_attr($target);?>" pattern="[A-Za-z0-9 ]+" title="Only Caretaker, Number and Space allowed. (A-Za-z0-9)">

								<select name='class'>
								<?php
								foreach ($data as $key => $value) {
									if ($key == $class) {
										$selected = 'selected';
									} else {
										$selected = '';
									}
									echo "<option value='".esc_attr( $key )."' ".esc_attr( $selected ).">".esc_html( $key )."</option>";
								}
								?>
								</select>
								<br>

								<button id="educare_results_btn" class="educare_button" name="update_<?php echo esc_attr($list);?>" type="submit"><i class="dashicons dashicons-edit"></i> Edit <?php echo esc_html($list);?></button>

							</form>
							<?php
						}
						elseif (isset($_POST['edit_class'])) {
							?>
							<form class="notice notice-success is-dismissible add_results" action="" method="post">
								Edit Class:
								<input type="hidden" name="educare_process_class">
								<input type="hidden" name="old_class" value="<?php echo esc_attr($class);?>">
								<input type="text" name="class" class="fields" value="<?php echo esc_attr($class);?>" placeholder="<?php echo esc_attr($class);?>" pattern="[A-Za-z0-9 ]+" title="Only Caretaker, Number and Space allowed. (A-Za-z0-9)">

								<br>
								
								<button id="educare_results_btn" class="educare_button" name="update_class" type="submit"><i class="dashicons dashicons-edit"></i> Edit Class</button>

							</form>
							<?php
						}

						elseif (isset($_POST['remove_subject'])) {
							$msg = '<div class="notice notice-success is-dismissible"><p>Successfully removed <b>'.esc_html($target).'</b> from the '.esc_html($list).' list.</p></div>';
							// convert indexed array to associative array. So, we can essily select your specific data/value by specific key. Otherwise, it's hard to detect specific data with indexed key.
							$subject_list = array_combine($subject_list, $subject_list);
							// remove data by specific key
							unset($subject_list[$target]);
							$subject_list = array_values($subject_list);

							$data[$class] = $subject_list;
						} else {
							echo '<div class="notice notice-error is-dismissible"> <p><b class="error">Somethink went wrong!</b> Maybe its a bug. Soon, we (Educare) will fix these issues after the next update</p></div>';
						}
					} else {
						if (isset($_POST['add_subject'])) {
							//  if add sobject
							$subject_list = array_unique($subject_list);
							array_push($subject_list, $target);
							$data[$class] = $subject_list;
							
							$msg = '<div class="notice notice-success is-dismissible"> <p>Successfully Added <b>'.esc_html($target).'</b> at the subject list<br>Class: <b>'.esc_html($class).'</b><br>Total: <b>'.esc_html(count($subject_list)).'</b> Subject added</p>
							</div>';
						}

						if (isset($_POST['update_subject'])) {
							$old_sub = sanitize_text_field( $_POST['old_subject'] );
							$old_subject = strtolower($old_sub);

							$old_class = sanitize_text_field( $_POST['old_class'] );
							$class = $class;

							// echo "Old Subject: $old_subject <br>Old Class: $old_class <br>";
							// echo "New Subject: $target <br>New Class: $class <br>";
							$get_key = array_search($old_sub, $data[$old_class]);

							if (strtolower($target) != $old_subject and $class == $old_class) {
								$data[$old_class][$get_key] = $target;

								$msg = '<div class="notice notice-success is-dismissible"> <p>Successfully change subject <b class="error">'.esc_html($old_sub).'</b> to <b class="success">'.esc_html($target).'</b></p></div>';
							}
							elseif (strtolower($target) == $old_subject and $class != $old_class) {
								unset($data[$old_class][$get_key]);
								array_values($data[$old_class]);
								array_push($data[$class], $target);
								
								$msg = '<div class="notice notice-success is-dismissible"> <p>Successfully change class <b class="error">'.esc_html($old_class).'</b> to <b class="success">'.esc_html($class).'</b></p></div>';
							} else {
								// Add data
								$data[$old_class][$get_key] = $target;
								// Remove data
								unset($data[$old_class][$get_key]);
								array_values($data[$old_class]);
								array_push($data[$class], $target);

								$msg = "<div class='notice notice-success is-dismissible'><p>Succesfully update subject <b class='error'>".esc_html($old_sub)."</b> to <b class='success'>".esc_html($target)."</b>. also changed class <b class='error'>".esc_html($old_class)."</b> to <b class='success'>".esc_html($class)."</b>.</p></div>";
							}
						}

						if (isset($_POST['update_class'])) {
							$old_class = sanitize_text_field( $_POST['old_class'] );
							$get_key = array_search($data[$old_class], $data);
							
							// $new_class = $data[$old_class];

							if(strtolower($old_class) == strtolower($target)) {
								$msg = "<div class='notice notice-error is-dismissible'><p>There are no changes for updates!</p></div>";
							} else {
								if (key_exists(strtolower($target), array_change_key_case($data))) {
									echo '<div class="notice notice-error is-dismissible"><p><b>'.esc_html($target).'</b> is allready exist in class list</p></div>';
								} else {
									if ($target !== $old_class) {
										$data = educare_replace_key($data, $old_class, $target);
										$msg = '<div class="notice notice-success is-dismissible"> <p>Successfully changed class <b class="error">'.esc_html($old_class).'</b> to <b class="success">'.esc_html($target).'</b></p></div>';
									}
								}
							}
						}

						if (isset($_POST['remove_class'])) {
							$class = sanitize_text_field( $_POST['class'] );
							
							unset($data[$class]);
							
							$msg = '<div class="notice notice-success is-dismissible"> <p><b class="error">'.esc_html($class).'</b> has been successfully removed from the class list</p></div>';
						}
						
					} // unique data

					if ($process) {
						$wpdb->update(
							$table, 			//table
							array( 				// data
								"data" => json_encode($data)
							),
						
							array( 				//where
								'ID' => $id
							)
						);
						
					}
					
					echo $msg;
				}

			} else {

				if (isset($_POST['add_class'])) {
					if (key_exists(strtolower($class), array_change_key_case($data))) {
						echo '<div class="notice notice-error is-dismissible"><p><b>'.esc_html($class).'</b> is allready exist in class list</p></div>';
					} else {
						$data[$class] = array();
						
						$wpdb->update(
							$table, //table
							array( // data
								"data" => json_encode($data)
							),
						
							array( //where
								'ID' => $id
							)
						);

						echo '<div class="notice notice-success is-dismissible"> <p>Successfully Added <b>'.esc_html($target).'</b> at the class list<br></p>
						</div>';
					}
				} else {
					?>
					<div class="notice notice-error is-dismissible"> 
						<p>Sorry, <b><?php echo esc_html($class);?></b> not exist<b></b> at the class list<br>
						If you need to add subject in this (<?php echo esc_html($class);?>) class. First, You need to add this (<?php echo esc_html($class);?>) in the class list. Then, You would allowed to add some subject. thanks
						</p>
					</div>
					<?php
				}
			}
		}
	}
	
	echo '<div class="collapses">';
	$count = 1;
	// Checked first class content (Subjects)
	$first = array_key_first($data);

	foreach ($data as $class => $val) {
		// here $val = total subject in this class
		?>
		<div class="collapse">
			<input class="head" type="radio" name="subject" id="<?php echo esc_attr( $class );?>" <?php if ($class == $first) {echo 'checked';}?>>
			<label class="collapse-label" for="<?php echo esc_attr( $class );?>"><?php echo esc_html( $count++ ) . '. ' . esc_html( $class );?><span><form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post"><input type="hidden" name="educare_process_class"><input type="hidden" name="class" value="<?php echo esc_attr( $class );?>"><input type="submit" name="edit_class" value="&#xf464"><input type="submit" name="remove_class" value="&#xf182" <?php educare_confirmation('class', esc_attr( $class ));?>></form></span></label>
			<div class="collapse-content bg-white">
			
				<table class='educare_add_content'>
					<thead>
						<tr>
							<th>No</th>
							<th width='100%'>Subject</th>
							<th>Edit</th>
							<th>Delete</th>
						</tr>
					</thead>
					<tbody>
					<?php
					if ($val) {
						$no = 1;
						foreach ($val as $subject) {
							// echo '<li>';
							// echo $subject;
							// echo '</li>';
							?>
							<tr>
								<td><?php echo esc_html($no++);?></td>
								<td><?php echo esc_html($subject);?></td>
								<td colspan='2'>
									<form class="educare-modify" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
										<input type="hidden" name="educare_process_class">

										<input type="hidden" name="class" value="<?php echo esc_attr($class);?>"/>

										<input type="hidden" name="subject" value="<?php echo esc_attr($subject);?>"/>
										
										<input type="submit" name="edit_<?php echo esc_attr($list);?>" class="button success" value="&#xf464">
										
										<input type="submit" name="<?php echo esc_attr("remove_$list");?>" class="button error" value="&#xf182" <?php educare_confirmation($list, esc_attr($subject));?>>
											
									</form>
								</td>
							</tr>
							<?php
						}
					} else {
						?>
						<tr>
							<td colspan="4"><div class="notice notice-error is-dismissible"><p> Currently you don't have added any subject in this class. Pleas add a subject under this class by using above forms. Thanks</p></div></td>
						</tr>
						<?php
					}
					
					?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}

	echo '</div>';

	?>
	
  <div class="tab_head">
    <button class="tablink educare_button" onclick="openTab(event,'x_subject')">Add Subject</button>
    <button class="tablink" onclick="openTab(event,'class')">Add Class</button>
  </div>
  
  <div id="x_subject" class="section_name">
    <form class="add_results" action="" method="post" id="add_<?php echo esc_attr($list);?>">
			<div class="content">
				<input type="hidden" name="educare_process_class">

				<div class="select add-subject">
					<div>
					<p>Subject:</p>
						<input type="text" name="<?php echo esc_attr($list);?>" class="fields" placeholder="<?php echo esc_attr($list);?> name" pattern="[A-Za-z0-9 ]+" title="Only Caretaker, Number and Space allowed. (A-Za-z0-9)">
					</div>
				
					<div>
						<p>Subject For:</p>
						<select name='class'>
							<?php
							foreach ($data as $key => $value) {
								echo "<option value='".esc_attr($key)."'>".esc_html($key)."</option>";
							}
							?>
						</select>
					</div>
				</div>

				<button id="educare_results_btn" class="educare_button" name="add_<?php echo esc_attr($list);?>" type="submit"><i class="dashicons dashicons-plus-alt"></i> Add Subject</button>
			</div>
		</form>
  </div>

  <div id="class" class="section_name" style="display:none">
    <form class="add_results" action="" method="post" id="add_<?php echo esc_attr($list);?>">
			<div class="content">
				<p>Class:</p>
				<input type="hidden" name="educare_process_class">
				<label for="<?php echo esc_attr($list);?>" class="labels" id="<?php echo esc_attr($list);?>"></label>
				<input type="text" name="class" class="fields" placeholder="Class name" pattern="[A-Za-z0-9 ]+" title="Only Caretaker, Number and Space allowed. (A-Za-z0-9)">
				<br>
				
				<button id="educare_results_btn" class="educare_button" name="add_class" type="submit"><i class="dashicons dashicons-plus-alt"></i> Add Class</button>
			</div>
		</form>
  </div>

	<script>
	function openTab(evt, myData) {
		var i, x, tablinks;
		x = document.getElementsByClassName("section_name");
		for (i = 0; i < x.length; i++) {
			x[i].style.display = "none";
		}
		tablinks = document.getElementsByClassName("tablink");
		for (i = 0; i < x.length; i++) {
			tablinks[i].className = tablinks[i].className.replace(" educare_button", "");
		}
		document.getElementById(myData).style.display = "block";
		evt.currentTarget.className += " educare_button";
	}
	</script>

	<?php
}




/*=====================( END SETTINGS FUNCTIONS )=======================
===================================================================
						NOW PRINT OUT OUR DATA IN SETTINGS PAGE
===================================================================
===================( BEGIN SETTINGS PAGE )===================*/

?>


<!-- Tab Head -->
<div class="container">
	<div class="tab">
	  <button class="tablinks" onclick="openTabs(event, 'add_content')" id="default"><i class="dashicons dashicons-plus-alt"></i><span>Add Content</span></button>
	  <button class="tablinks" onclick="openTabs(event, 'settings')"><i class="dashicons dashicons-admin-generic"></i><span>Settings</span></button>
	</div>
		
	<div class="educare_post educare_settings">
		<?php educare_get_settings();?>
		
		<!-- Tab add_content -->
		<div id="add_content" class="tab_content">
			
			<div class="cover">
				<img src="<?php echo esc_url(EDUCARE_URL.'assets/img/cover.svg'); ?>" alt="educare cover"/>
			</div>
				
			<!-- <h1>Add Content</h1> -->
			
			<?php 
			echo educare_setting_subject("subject");
			educare_get_content();
			?>
					
		</div> <!-- End Tab add_content -->
		
		<!-- Tab settings -->
		<div id="settings" class="tab_content">
			<?php
				if ( isset( $_POST['educare_default_photos'] ) && isset( $_POST['educare_attachment_id'] ) ) :
					update_option( 'educare_files_selector', absint( sanitize_text_field($_POST['educare_attachment_id'] )) );
				endif;
				
				wp_enqueue_media();
				$educare_save_attachment = get_option( 'educare_files_selector', 0 );
			
				$default_photos = wp_get_attachment_url( get_option( 'educare_files_selector' ) );
				if ($default_photos == null) {
					$visibility = 'none';
					$img = EDUCARE_URL.'assets/img/default.svg';
					$img_type = "<h3 id='educare_img_type' class='title'>Default Photos</h3>";
					$guide = "<p id='educare_guide'>Current students photos are default. Please upload or select  a custom photos from gallery that's you want!</p>";
				} else {
					$visibility = 'block';
					$img = wp_get_attachment_url( get_option( 'educare_files_selector' ) );
					$img_type = "<h3 id='educare_img_type' class='title'>Custom Photos</h3>";
					$guide = "<p id='educare_guide'></p>";
				}
			?>
						
			<form method='post'>
				<div id='educare_files_selector_disabled'>
					<div id='educare_files_uploader' class='educare_upload'>
						<div class='educare_files_selector'>
							<img id='educare_attachment_preview' class='educare_student_photos' src='<?php echo esc_url($img);?>'>
							<?php echo wp_kses_post($img_type);?>
						</div>
						
						<?php echo wp_kses_post($guide);?>
						<div id="photos_help"></div>
							<div class="select">
								<input type="hidden" name='educare_attachment_id' id='educare_attachment_id' value='<?php echo esc_attr(get_option( 'educare_files_selector' )); ?>'>
									
								<input type='hidden' name='educare_attachment_url' id='educare_attachment_url' value='<?php echo esc_url(get_option( 'educare_files_selector' )); ?>'>
								
								<input id="educare_upload_button" type="button" class="button" value="<?php _e( 'Upload Students Photos' ); ?>"/>
								
								<input type='button' id='educare_attachment_title' class="button" value='Pleace Select a students photos' disabled>
								
								<a id='educare_attachment_clean' class='dashicons dashicons-no educare_clean' style='width: auto; display: <?php echo esc_attr($visibility);?>' href='<?php echo esc_js('javascript:;');?>'></a>
							</div>
							<button id='educare_default_photos' type="submit" name="educare_default_photos" class="educare_button full"><i class="dashicons dashicons-yes-alt"></i> Save</button>
					</div>
				</div>
			</form>
			
			<h1>Settings</h1>
			
			<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
				<?php
				ob_start();
				echo esc_url( bloginfo( 'url' ) );
				$domain = ob_get_clean();

				?>
				<div class="collapses">
					<div class="collapse">
						<input class="head" type="radio" name="settings_status_menu" id="Page_Setup_menu" checked>
						<label class="collapse-label" for="Page_Setup_menu"><div><i class="dashicons dashicons-edit-page"></i> Page Setup</div></label>
						<div class="collapse-content">
							<?php
							educare_settings_status('results_page', 'Set Results Page', "Inter your front end results page slug (Where you use `[educare_results]` shortcode in your editor, template or any shortcode-ready area for front end results system). Don't need to insert with domain - ".esc_url($domain)."/results. Only slug will be accepted, for exp: results or index.php/results.");
							?>
						</div>
					</div>

					<div class="collapse">
						<div style="background-color: inicial;">
						<input class="head" type="radio" name="settings_status_menu" id="Display_menu">
						<label class="collapse-label" for="Display_menu"><div><i class="dashicons dashicons-admin-appearance"></i> Customize</div></label>
						<div class="collapse-content">
							<?php
							echo "<div style='padding: 1px 0;'>";
							echo educare_guide_for('display_msgs');
							echo '</div>';
				
							educare_settings_status('display', 'Delete confirmation', "Anable and disable delete/remove confirmation");
							?>
					</div>
						
						</div>
					</div>

					<div class="collapse">
						<input class="head" type="radio" name="settings_status_menu" id="Results_System_menu">
						<label class="collapse-label" for="Results_System_menu"><div><i class="dashicons dashicons-welcome-learn-more"></i> Results System</div></label>
						<div class="collapse-content">
							<?php
							educare_settings_status('optional_sybmbol', 'Optional Subject Selection', "Define optional subject identifier character/symbol. In this way educare define and identify optional subjects when you add or import results.");
				
							educare_settings_status('auto_results', 'Auto Results', "Automatically calculate students results status Passed/Failed and GPA");
			
							educare_settings_status('photos', 'Students Photos', "Show or Hide students photos");
			
							educare_settings_status('custom_results', 'Custom Design Permissions', "You need to permit/allow this options when you add custom functionality or customize results card or searching forms");
							?>
						</div>
					</div>

					<div class="collapse">
						<input class="head" type="radio" name="settings_status_menu" id="Others_menu">
						<label class="collapse-label" for="Others_menu"><div><i class="dashicons dashicons-admin-tools"></i> Others</div></label>
						<div class="collapse-content">
							<?php
							educare_settings_status('guide', 'Guidelines', "Anable and disable guide/help messages");

							educare_settings_status('confirmation', 'Delete confirmation', "Anable and disable delete/remove confirmation");
							
							educare_settings_status('advance', 'Advance Settings', "Anable and disable Advance/Developers menu. Note: it's only for developers or advance users");
							
							echo '<div id="advance_settings">';
								
							educare_settings_status('delete_subject', 'Automatically Delete Subject', "Automatically Delete Subject from Results Table When You Delete Subject From Subject List?");
							
							educare_settings_status('clear_field', 'Delete and Clear field data', "Tips: If you set <b>No</b> that's mean only field will be delete. And, if you set <b>Yes</b> - clear field data when you delete any (current) field. Delete and Clear field data?");
							
							echo '</div>';
							?>
						</div>
					</div>

				</div>
				<?php
				
				?>
				<script type='text/javascript'>
					jQuery( document ).ready( function( $ ) {
						var advance = '<?php echo educare_esc_str(educare_check_status('advance'));?>';
						if (advance == 'unchecked') {
							$( '#advance_settings' ).css( 'display', "none" );
						}
					});
				</script>
					
				<button type="submit" name="educare_update_settings_status" class="educare_button"><i class="dashicons dashicons-yes-alt"></i> Save</button>
				<button type="submit" name="educare_reset_default_settings" class="educare_button" onclick='return confirm("Are you sure to reset default settings? This will not effect your content, its only reset your current settings status.")'><i class="dashicons dashicons-update"></i> Reset Settings</button>
					
			</form>
		</div>

	</div> <!-- / .educare Settings -->
</div> <!-- / .Container -->


<script type='text/javascript'>
	jQuery( document ).ready( function( $ ) {
		// Uploading files
		var file_frame;
		var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
		var educare_media_post_id = <?php echo esc_attr($educare_save_attachment); ?>; // Set this
		jQuery('#educare_upload_button').on('click', function( event ){
				event.preventDefault();
				// If the media frame already exists, reopen it.
				if ( file_frame ) {
						// Set the post ID to what we want
						file_frame.uploader.uploader.param( 'post_id', educare_media_post_id );
						// Open frame
						file_frame.open();
						return;
				} else {
						// Set the wp.media post id so the uploader grabs the ID we want when initialised
						wp.media.model.settings.post.id = educare_media_post_id;
				}
				// Create the media frame.
				file_frame = wp.media.frames.file_frame = wp.media({
						title: 'Select Students Photos',
						button: {
								text: 'Use this image',
						},
						multiple: false // Set to true to allow multiple files to be selected
				});
				// When an image is selected, run a callback.
				file_frame.on( 'select', function() {
						// We set multiple to false so only get one image from the uploader
						attachment = file_frame.state().get('selection').first().toJSON();
						// Do something with attachment.id and/or attachment.url here
						// $( '#educare_attachment_preview' ).attr( 'src', attachment.url ).css( 'width', '100px' );
						$( '#educare_attachment_preview' ).attr( 'src', attachment.url );
						$( '#educare_upload_button' ).val( 'Edit Photos' );
						$( '#educare_attachment_clean' ).css( 'display', 'block' );
						$("#educare_img_type").html('Custom photos');
						$( '#educare_attachment_id' ).val( attachment.id );
						$( '#educare_attachment_url' ).val( attachment.url );
						$( '#educare_attachment_title' ).val( attachment.title ).attr( 'value', this(val) );
						// Restore the main post ID
						wp.media.model.settings.post.id = wp_media_post_id;
				});
						// Finally, open the modal
						file_frame.open();
		});
		// Restore the main ID when the add media button is pressed
		jQuery( 'a.add_media' ).on( 'click', function() {
				wp.media.model.settings.post.id = wp_media_post_id;
		});
		// clean files
		$("a.educare_clean").on("click", function() { 
				$("#educare_attachment_url").val("");
				$("#educare_attachment_id").val("");
				$( '#educare_attachment_preview' ).attr( 'src', "<?php echo esc_url(EDUCARE_URL.'assets/img/default.svg');?>" );
				$("a.educare_clean").css('display', 'none');
				$( '#educare_attachment_title' ).val('Cleaned! please select onother one');
				$( '#educare_upload_button' ).val( 'Upload photos again' );
				$("#educare_img_type").html('Default photos');
				$("#educare_guide").html("Current students photos are default. Please upload or select  a custom photos from gallery that's you want!");
		});
	});
    
    
  <?php
	if (educare_check_status('photos') == 'unchecked') {
		$photos = 'disabled';
	} else {
		$photos = '';
	}
	?>
	
	var photos = '<?php echo educare_esc_str($photos);?>';
	if (photos == 'disabled') {
		document.getElementById('educare_upload_button').setAttribute('disabled', 'disabled');
		document.getElementById('educare_default_photos').setAttribute('disabled', 'disabled');
		document.getElementById('educare_attachment_clean').style.display= 'none';
		document.getElementById('educare_files_selector_disabled').className = 'educare_files_selector_disabled';
		document.getElementById('photos_help').innerHTML = 'Currently students photos are disabled. If you upload or display student photos, first check/anable students photos under the settings sections';
	}
</script>


<script type="text/javascript">
	function openTabs(evt, tabName) {
		// Declare all variables
		var i, tab_content, tablinks;

		// Get all elements with class="tab_content" and hide them
		tab_content = document.getElementsByClassName("tab_content");
		for (i = 0; i < tab_content.length; i++) {
			tab_content[i].style.display = "none";
		}

		// Get all elements with class="tablinks" and remove the class "active"
		tablinks = document.getElementsByClassName("tablinks");
		for (i = 0; i < tablinks.length; i++) {
			tablinks[i].className = tablinks[i].className.replace(" active", "");
		}

		// Show the current tab, and add an "active" class to the button that opened the tab
		document.getElementById(tabName).style.display = "block";
		evt.currentTarget.className += " active";
	}

	// Get the element with id="defaultOpen" and click on it
	document.getElementById("default").click();
</script>
	
	