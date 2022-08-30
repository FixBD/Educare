<?php

 /** =====================( Functions Details )======================
  
	# Educare Grading Systems
  * usage => echo educare_grade_system("85");

  * @since 1.2.0
	* @last-update 1.2.0
		
  * @param $marks int/str  for grading system
  * @return str
	
===================( function for create menu )=================== **/

function educare_grade_system($marks) {
  /* default grading system is
  $grade_system = array(
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
  */

  $grade_system = educare_check_status('grade_system');
  $current = $grade_system->current;
  $grade_system = $grade_system->rules->$current;

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



 /** =====================( Functions Details )======================
  
	# Save Grading System
  * usage => echo educare_save_results_system();

  * @since 1.2.0
	* @last-update 1.2.0
		
  * @return void
	
===================( function for proccess grading rules )=================== **/

function educare_save_results_system() {
  global $wpdb;
  $table = $wpdb->prefix . "educare_settings";

  $search = $wpdb->get_row("SELECT * FROM $table WHERE list='Settings'");

  if ($search) {
    if (isset($_POST['update_grade_rules'])) {
      $id = $search->id;
      $data = $search->data;
      $data = json_decode($data);
      
      $rules_name = sanitize_text_field($_POST['rules']);
      $rules1 = array_map( 'sanitize_text_field', $_POST['rules1'] );
      $rules2 = array_map( 'sanitize_text_field', $_POST['rules2'] );
      $grade = array_map( 'sanitize_text_field', $_POST['grade'] );
      $point = array_map( 'sanitize_text_field', $_POST['point'] );

      $count1 = $count2 = $count3 = 0;
      $rules = array();
      foreach ($grade as $value) {
        $key = $rules1[$count1++] . '-' . $rules2[$count2++];
        $rules[$key][0] = $point[$count3++];
        $rules[$key][1] = $value;
      }

      $grade_system = educare_check_status('grade_system');
      $grade_system->rules->$rules_name = $rules;
      $data->grade_system = $grade_system;

      // now update desired data
      $wpdb->update(
        $table, //table
        array(  // data
                // we need to encode our data for store array/object into databases
          "data" => json_encode($data)
        ),
        
        array( //where
          'ID' => $id
        )
        
      );

      echo "<div class='notice notice-success is-dismissible'><p>Successfully updated " . wp_kses_post($rules_name) . " grading systems</p></div>";
    }
  } else {
    echo educare_guide_for('db_error');
  }
}



 /** =====================( Functions Details )======================
  
	### Showing Grading System
  * usage => echo educare_save_results_system();

  * @since 1.2.0
	* @last-update 1.2.0
		
  * @return void
	
===================( function for Showing Grading System )=================== **/

function educare_show_grade_rule() {
  $grade_system = educare_check_status('grade_system');
  $current = $grade_system->current;
  $grade_system = $grade_system->rules->$current;

  // echo '<pre>';	
  // print_r($grade_system);	
  // echo '</pre>';

  echo '<table class="grading-system">
  <thead>
    <tr>
      <th>Class interval</th>
      <th>Grade point</th>
      <th>Letter grade</th>
    </tr>
    </thead>';

  foreach ($grade_system as $marks => $value) {
  echo '<tr>
  <td>'. esc_html($marks) .'</td>
  <td>'. esc_html($value[0]) .'</td>
  <td>'. esc_html($value[1]) .'</td>
  </tr>';
  }

  echo '</table>';
}

?>


<div class="educare_post">
  <h1>Results System</h1>
  <blockquote>Here you can change your custom results system. Also, you can change or add your country's result rules by code, Educare provides some powerful functions to manage or add custom result rules based on your demand. If you don't khow, How to add custom results rules? Visit the Educare support forum to add your custom rules.</blockquote>

  <?php 
  educare_save_results_system();

  echo educare_guide_for('If you need to change default grading value, simply click edit button and inter your custom (Country) starndard rules. Allso, you can add your custom results rules using code. For this please visit Educare suppor forum or carfully read plugin readme files');
  ?>
  
  <p>Grading systems: <i id="help" title="How does it work? Click to view" class="dashicons dashicons-editor-help"></i></p>
  <div class="select">
    <select id="Class" name="Class" class="form-control">
      <option value="Default">Default</option>
      <option value="Custom" disabled>Custom</option>
    </select>
  </div>

  <div id="show_help" style="display: none;">
    <div class="notice notice-success"><p>
      <h3>How it's work?</h3>
      <p>
      We are mentioning the process how to calculate CGPA (GPA) from Marks in HSC. To do this, add up the grade points for the six major subjects and divide with 6 (total subject). For example, your grade points for <b>six</b> main subjects are listed below:</p><br>

      <table>
        <thead>
          <tr>
          <th>Subject</th>
          <th>Mark</th>
          <th>Grade Points</th>
          <th>Letter grade</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Subject 1</td>
            <td>85</td>
            <td>5</td>
            <td>A+</td>
          </tr>
          <tr>
            <td>Subject 2</td>
            <td>70</td>
            <td>4</td>
            <td>A</td>
          </tr>
          <tr>
            <td>Subject 3</td>
            <td>68</td>
            <td>3.5</td>
            <td>A-</td>
          </tr>
          <tr>
            <td>Subject 4</td>
            <td>55</td>
            <td>3</td>
            <td>B</td>
          </tr>
          <tr>
            <td>Subject 5</td>
            <td>95</td>
            <td>5</td>
            <td>A+</td>
          </tr>
          <tr>
            <td>Subject 6</td>
            <td>80</td>
            <td>5</td>
            <td>A+</td>
          </tr>
          <tr>
            <td>Total</td>
            <td></td>
            <td>21</td>
            <td></td>
          </tr>
          <tr>
            <td><strong>GPA</strong></td>
            <td></td>
            <td><strong>25.5/6 = 4.25</strong></td>
            <td>A</td>
          </tr>
        </tbody>
      </table>

      <p>
        <ul style="list-style-type:circle;">
          <li><strong>Step 1:</strong> Add the grade points i.e <code>5+4+3.5+3+5+5 = 25.5</code></li>
          <li><strong>Step 2:</strong> Divide the sum by (total subject) 6 i.e <code>25.5/6 = 4.25</code></li>
          <li>Thus, your GPA is <code>4.25</code></li>
          <li>And, Letter grade is <code>A</code></li>
        </ul>
      </p>

      <p>Basically, <strong>GPA = Total grade points/Total subject</strong></p>
      <br>
      <strong>How to define grade point and letter grade?</strong>
      <pre><code>if ($marks >= 80 and $marks <= 100) { $point = 5; }</code></pre>or<pre><code>if ($marks >= 80 and $marks <= 100) { $grade = 'A+'; }</code></pre>
      </p>
    </div>
  </div>

  <div id="result_msg">
    <p><b>Default Rules</b></p>
    <?php educare_show_grade_rule();?>

    <div class="button-container">
      <button type="submit" name="save_grade_system" class="educare_button disabled"><i class="dashicons dashicons-update" disabled></i></button>
      <button id="edit_grade" type="submit" name="edit_grade_system" class="educare_button"><i class="dashicons dashicons-edit"></i></button>
    </div>
  </div>

</div>

<script>
  $(document).on("click", "#edit_grade", function() {
    $(this).attr('disabled', true);
    var class_name = $('#Class').val();
    $.ajax({
      url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
      data: {
        action: 'educare_proccess_grade_system',
        class: class_name
      },
      type: 'POST',
      success: function(data) {
        $('#result_msg').html(data);
        $('#edit_grade').attr('disabled', false);
      },
      error: function(data) {
        $('#result_msg').html("<?php echo educare_guide_for('db_error')?>");
      },
    });
  });
  
  $("#help").click(function() {
    $(this).css('color', 'green');
    $("#show_help").slideToggle();
  });

</script>

