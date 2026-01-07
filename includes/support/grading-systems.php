<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/** 
 * ### Educare Grading Systems
 * 
 * usage => echo educare_grade_system("85");
 * Default grading system is
 * 
 * $grade_system = array(
    'current' => 'Default',
    'rules' => [
      'Default' => [
        '80-100' => [5, 'A+'],
        '70-79'  => [4, 'A'],
        '60-69'  => [3.5, 'A-'],
        '50-59'  => [3, 'B'],
        '40-49'  => [2, 'C'],
        '33-39'  => [1, 'D'],
        '0-32'  => [0, 'F']
      ]
    ]
  );
 * 
 * @since 1.2.0
 * @last-update 1.2.0
 * 
 * @param $marks int/str  for grading system
 * @return str
 */

function educare_grade_system($marks) {
  $grade_system = educare_grading_system();

  // check optional marks
  $optional_marks = substr(strstr($marks, ' '), 1);
  if ($optional_marks) {
    $marks = $optional_marks;
  }

  foreach ($grade_system as $rules => $grade) {
    if ($rules == 'failed' or $rules == 'success') break;
    // get first rules number to compare
    $rules1 = strtok($rules, '-');
    // get second rules number to compare
    $rules2 = substr(strstr($rules, '-'), 1);

    if ($marks >= $rules1 and $marks <= $rules2) {
      $marks = $grade;
    }
  }

  return $marks;
}



function educare_grading_system() {
  $grade_system = educare_check_status('grade_system');
	$current = $grade_system->current;
	$grade_system = $grade_system->rules->$current;
	// $grade_system = json_decode(json_encode($grade_system), true);

	// Filter grade system.
	$grade_system = apply_filters( 'educare_filter_grading_system', $grade_system );

  return $grade_system;
}



/**
 * ### Save Grading System
 * 
 * usage => echo educare_save_results_system();
 * 
 * @since 1.2.0
 * @last-update 1.2.0
 * 
 * @return void
 */

function educare_save_results_system() {
  global $wpdb;
  $table = $wpdb->prefix . "educare_settings";

  $search = $wpdb->get_row("SELECT * FROM $table WHERE list='Settings'");

  if ($search) {
    $id = $search->id;
    $data = $search->data;
    // $data = json_decode($data);
    $data = educare_check_status();
    
    $rules_name = sanitize_text_field($_POST['rules']);
    $rules1 = array_map( 'sanitize_text_field', $_POST['rules1'] );
    $rules2 = array_map( 'sanitize_text_field', $_POST['rules2'] );
    $grade = array_map( 'sanitize_text_field', $_POST['grade'] );
    $point = array_map( 'sanitize_text_field', $_POST['point'] );

    $no = 0;
    $rules = array();
    
    foreach ($grade as $value) {
      $key = $rules1[$no] . '-' . $rules2[$no];
      $rules[$key][0] = $point[$no];
      $rules[$key][1] = $value;

      $no++;
    }

    $grade_system = educare_check_status('grade_system');
    $grade_system->rules->$rules_name = $rules;
    $data->grade_system = $grade_system;

    // now update desired data
    $wpdb->update(
      $table, //table
      array(  // data
              // we need to encode our data for store array/object into databases
        "data" => educare_encrypt_data($data)
      ),
      
      array( //where
        'ID' => $id
      )
      
    );

    echo "<div class='notice notice-success is-dismissible'><p>Successfully updated " . wp_kses_post($rules_name) . " grading systems</p></div>";
  } else {
    echo educare_guide_for('db_error');
  }
}



/**
 * ### Showing Grading System
 * 
 * usage => echo educare_show_grade_rule();
 * 
 * @since 1.2.0
 * @last-update 1.2.0
 * 
 * @return void
 */

function educare_show_grade_rule() {
  $grade_system = educare_grading_system();

  echo '<div class="table_container"><table class="grade_sheet grading-system bg-white">
  <thead>
    <tr>
      <th>'.__('Class interval', 'educare').'</th>
      <th style="text-align: center">'.__('Grade Points', 'educare').'</th>
      <th>'.__('Letter grade', 'educare').'</th>
      <th>'.__('Comments', 'educare').'</th>
    </tr>
    </thead>';

  foreach ($grade_system as $marks => $value) {
    $point = isset($value[0]) ? $value[0] : '';
    $grade = isset($value[1]) ? $value[1] : '';

    echo '<tr>
      <td>'. esc_html($marks) .'</td>
      <td>'. esc_html($point) .'</td>
      <td>'. esc_html($grade) .'</td>
      <td>'. esc_html__('Unlock Premium', 'educare') .'</td>
    </tr>';
  }
  
  echo '</table></div>';
}



/**
 * ### Modify or update grading systems
 * 
 * @since 1.2.0
 * @last-update 1.2.0
 * 
 * @return proceess data
 */

add_action('wp_ajax_educare_proccess_grade_system', 'educare_proccess_grade_system');

function educare_proccess_grade_system() {
  educare_verify_nonce('edit_grade_system');
  
	$rules = sanitize_text_field($_POST['class']);

	function educare_add_grade_system($rules = null, $point = null, $grade = null, $comments = null) {
    // get first rules (less den) number to compare
    $rules1 = strtok($rules ?? '', '-');
    // get second rules (greater den) number to compare
    $rules2 =substr(strstr($rules ?? '', '-'), 1);

		if (!$rules1) {
			$rules1 = 0;
		}
		if (!$rules2) {
			$rules2 = 0;
		}
		if (!$point) {
			$point = 0;
		}
    ?>
		<tr class="cloneField">
			<td><input type="number" name="rules1[]" value="<?php echo esc_attr($rules1)?>" placeholder="<?php echo esc_attr($rules1)?>" step="any"></td>
			<td><input type="number" name="rules2[]" value="<?php echo esc_attr($rules2)?>" placeholder="<?php echo esc_attr($rules2)?>" step="any"></td>
			<td><input type="number" name="point[]" value="<?php echo esc_attr($point)?>" placeholder="<?php echo esc_attr($point)?>" step="any"></td>
			<td><input class="bold" type="text" name="grade[]" value="<?php echo esc_attr($grade)?>" placeholder="<?php echo esc_attr($grade)?>"/></td>
      <td><input type="text" placeholder="<?php echo esc_html__('Unlock Premium', 'educare');?>" disabled/></td>
			<td><a href="<?php echo esc_js( 'javascript:void(0);' );?>" class="remove_button"><i class="dashicons dashicons-no"></i></a></td>
		</tr>
    <?php
  }
  
	$grade_system = educare_check_status('grade_system');
	$grade_system = $grade_system->rules->$rules;

  ?>
	<div class="notice notice-success is-dismissible"><p>
		<form id='addForm' action="" method="post">
			<div class='fixbd_cloneField'>
				<h5 class="text-center mb-3"><?php _e('Edit Rules', 'educare');?></h5>
				<p id='status' class='warning sticky rounded p-2 px-3'></p>
				
				<p><?php _e('Rules Name', 'educare');?></p>
				<input type="text" name="rules" value="<?php echo esc_attr($rules)?>" placeholder=""/ disabled>
        <br>
				<input type="hidden" name="rules" value="<?php echo esc_attr($rules)?>">
				
        <div class="table_container">
        <table class="grade_sheet" id='cloneBody'>
            <thead>
              <tr>
                <th>Less Mark</th>
                <th>Greater Mark</th>
                <th>Grade point</th>
                <th>Letter grade</th>
                <th>Comments</th>
                <th>Close</th>
              </tr>
            </thead>
            <tbody id='cloneBody'>
              <?php
              // $count1 = $count2 = 0;
              foreach ( $grade_system as $rules => $value ) {
                $point = isset($value[0]) ? $value[0] : '';
                $grade = isset($value[1]) ? $value[1] : '';

                educare_add_grade_system($rules, $point, $grade);
              }
              ?>
            </tbody>
          </table>
        </div>

        <?php
        // Security nonce for this form.
        $nonce = wp_create_nonce( 'update_grade_rules' );
        echo '<input type="hidden" name="nonce" value="'.esc_attr($nonce).'">';
        ?>
				
				<div class="button-container">
				<a href='<?php echo esc_js( 'javascript:void(0);' );?>' class='addButton educare_button' title='Add more field'><i class='dashicons dashicons-plus-alt'></i></a>
				<button id='save_addForm' class="educare_button" name="update_grade_rules"><i class='dashicons dashicons-yes'></i></button>
				</div>
				
			</div>
		</form>
		
		<div id='cloneWrapper' style='display: none;'>
			<?php educare_add_grade_system();?>
		</div>
	</p><button class="notice-dismiss"></button></div>

  <script type="text/javascript"><?php echo esc_js( 'cloneField()' );?></script>
	<?php

	die;
}



/**
 * ### Save grading fields data
 * 
 * 
 * @since 1.2.0
 * @last-update 1.2.0
 * 
 * @return void
 */

add_action('wp_ajax_educare_save_grade_system', 'educare_save_grade_system');

function educare_save_grade_system() {
  // Remove the backslash
	$_POST['form_data'] = stripslashes($_POST['form_data']);
  // parses query strings and sets the parsed values into the $_POST array.
  wp_parse_str($_POST['form_data'], $_POST);

  // Verify the nonce to ensure the request originated from the expected source
  educare_verify_nonce('update_grade_rules');

  // Save data
  educare_save_results_system();
  // Show updated data
  educare_show_grade_rule();

  // Ignore (0) and stop ajax response
  die;
}


// Dont't close

