<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}


/**
 * Imports student data from a CSV file.
 * 
 * @since 1.0.0
 * @last-update 1.6.0
 *
 * @param string $roles The roles associated with the imported data (e.g., student roles).
 *
 * @return void The function imports and processes student data from the uploaded CSV file.
 */
function educare_default_import_data($roles = 'results') {
  educare_show_school_banner();
  
  echo '<div class="educare-header-container"></div>';
  
  if (isset($_POST['import_data'])) {
    if ($roles === 'teachers') {
      echo educare_get_advance_banner('The features you are requesting are supported in the Educare Premium version. It includes advanced functionalities like teachers or staffer management.', false);
    } else {
      educare_verify_nonce('educare_import_data');

      if ($_FILES['csv_file']['name']) {
        $extension = pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION);

        if ($extension == 'csv') {
          if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $tmpFilePath = $_FILES['csv_file']['tmp_name'];

            // Load the CSV file from the temporary uploaded file
            $file = fopen($tmpFilePath, 'r');
            $headers = fgetcsv($file);

            $csv_data = array();
            while ($row = fgetcsv($file)) {
              $csv_data[] = array_combine($headers, $row);
            }
            fclose($file);

            $highestRow = count($csv_data);

            global $wpdb;
            $marks_db = 'marks';
            $educare_marks = $wpdb->prefix.EDUCARE_PREFIX.esc_sql($marks_db);
            $students_db = 'students';
            $educare_students = $wpdb->prefix.EDUCARE_PREFIX.esc_sql($students_db);

            $requred = educare_check_status('display');
            $requred_data = educare_requred_data($requred);
            $slice_default = end($requred_data);

            $row = 0;
            $data = array();

            // Loop through the rows using a while loop
            while ($row < $highestRow) {
              $rowData = $csv_data[$row];
              
              $default = educare_array_slice($rowData, 'Name', $slice_default);
              $get_default = $default;

              $default['Status'] = 'publish';

              if ($roles == 'results') {
                $subject = educare_array_slice($rowData, $slice_default, null, 1);
                $default_data = $default;
                $details = array();

                if ($get_default) {
                  $student = new StudentResult((object)$default_data);
                  $getStudents = $student->getStudents();

                  if (isset($getStudents[0]->Details)) {
                    $details = json_decode($getStudents[0]->Details);
                  }
                }
              } else {
                $subject = isset($rowData['Class']) ? educare_get_data('Class', $rowData['Class']) : array();
                $subject = array_flip($subject);
                $details = educare_array_slice($rowData, $slice_default, null, 1);
              }

              $subject_data = array();

              if ($subject) {
                foreach ($subject as $sub_name => $value) {
                  if ($roles != 'results') {
                    $value = '';
                  }

                  $subject_data[$sub_name]['marks'] = $value;
                  $subject_data[$sub_name]['optional'] = '';
                }
              }

              $importData = array();
              $importData['default'] = $default;
              $importData['Details'] = $details;
              $importData['Subject'][0] = $subject_data;
              
              $data[] = $importData;

              $row++;
            }

            if ($data) {
              $status = array (
                'total' => count($data),
                'success' => 0,
                'exist' => 0,
                'error' => 0,
                'empty' => 0,
                'error_creating_user' => 0,
              );

              $marks_data = array();

              $import_function = function_exists('educare_crud_data_new_csv') ? 'educare_crud_data_new_csv' : 'educare_crud_data_new';

              foreach ($data as $data_key => $import) {
                $_POST = array();
                $_POST['roles'] = $roles;

                foreach ($import as $key => $value) {
                  $_POST[$key] = $value;
                  unset($import[$key]);
                }

                $_POST['Others']['Photos'] = '';
                $_POST['crud'] = 'crud';

                $import_data = $import_function(true);
                
                $import_status = is_array($import_data) ? sanitize_text_field($import_data['status']) : $import_data;

                if ($roles == 'results' && $import_status == 'success') {
                  $search_marks = array(
                    'Class' => 'Class 6',
                    'Exam' => 'Exam no 1',
                    'Year' => '2023',
                    'Group' => ''
                  );

                  $default = $_POST['default'];

                  foreach ($default as $key => $value) {
                    if (key_exists($key, $search_marks)) {
                      $search_marks[$key] = sanitize_text_field($value);
                    }
                  }

                  $get_marks = educare_dynamic_sql($search_marks, $marks_db);
                  $marks = $wpdb->get_row("SELECT * FROM $educare_marks $get_marks");

                  $search_students = array(
                    'Roll_No' => '',
                    'Regi_No' => '',
                    'Class' => '',
                    'Year' => '',
                    'Group' => ''
                  );
                  
                  foreach ($default as $key => $value) {
                    if (key_exists($key, $search_students)) {
                      $search_students[$key] = sanitize_text_field($value);
                    }
                  }
                  
                  $get_student = educare_dynamic_sql($search_students, $students_db);
                  $student_data = $wpdb->get_row("SELECT * FROM $educare_students $get_student");

                  $student_id = $student_data ? $student_data->id : 0;

                  if (isset($marks->id)) {
                    $marks_id = $marks->id;
                    $old_marks = json_decode($marks->Marks, true);

                    if (empty($marks_data)) {
                      $marks_data[$marks_id] = $old_marks;
                    }

                    if ($student_id) {
                      $sub_data = $_POST['Subject'][0];
                      $marks_data[$marks_id][$student_id] = (array)$sub_data;
                    }

                  } else {
                    if ($student_id) {
                      $new_marks = $search_marks;

                      $sub_data = $_POST['Subject'][0];
                      $new_marks['Marks'][$student_id] = (array)$sub_data;
                      $new_marks['Marks'] = json_encode($new_marks['Marks']);

                      $insert = $wpdb->insert($educare_marks, (array)$new_marks);

                      if ($insert !== false) {
                        $insert_id = $wpdb->insert_id;
                        $marks_data[$insert_id][$student_id] = (array)$sub_data;
                      }
                    }
                  }
                }
                
                if (key_exists($import_status, $status)) {
                  $status[$import_status]++;
                }
                
                unset($data[$data_key]);
              }

              if ($roles == 'results' && $marks_data) {
                foreach ($marks_data as $marks_id => $mark_data) {
                  $mark_data = array('Marks' => json_encode($mark_data));
                  $wpdb->update($educare_marks, (array) $mark_data, array('ID' => $marks_id));
                }
              }

              $user_can_login = educare_check_status('user_profiles');
              
              if ($roles == 'results' || $user_can_login != 'checked') {
                unset($status['error_creating_user']);
              }

              $msgs = '';

              foreach ($status as $key => $value) {
                $class = $key == 'total' ? 'total' : ($key == 'success' ? 'success' : ($value ? 'error' : ''));
                $msgs .= '<p class="'.esc_attr($class).'">'.esc_html(str_replace('_', ' ', ucfirst($key))).': <b>'.esc_html($value).'</b></p>';
              }
              
              echo educare_show_msg($msgs);

              if (isset($status['empty']) && $status['empty'] > 0 ) {
                $required_fields = educare_required_fields($roles);

                $empty_msgs = '<b>Error to import '. esc_html($status['empty']) . ' ' . $roles . ':</b> Because, one or more of their required fields ('.esc_html(implode(', ', $required_fields)).') are empty';

                if (key_exists('error_creating_user', $status)) {
                  $empty_msgs .= ' Currently Educare settings allow user login (profiles) system. And you are tyring to import ' . $roles . ' without - User login (name), Email and Password. Please note, when you try to import students and teachers from the demo file, you will need to input these fields manually. Otherwise, you can disable <a href="/wp-admin/admin.php?page=educare-settings&menu=Security" target="_blank">User Profiles</a> from Educare settings to resolve this issue.';
                }
                
                echo educare_show_msg($empty_msgs, false);
              }
            }
          } else {
            echo educare_show_msg('File not upload yet');
          }
        } else {
          echo educare_show_msg('The file extension is not valid. To import the data, you need to use CSV files with the <b>.csv</b> extension. Please select a file with this extension.', false);
        }
      } else {
        echo educare_show_msg('No file chosen! Please select a file', false);
      }
    }
  }

  ?>
  <form class="add_results" method="post" action="<?php esc_url($_SERVER['REQUEST_URI']); ?>" enctype="multipart/form-data">
    <div class="content">
      <?php
      if (function_exists('educare_get_page_title')) {
        echo educare_get_page_title($roles, 'import');
      }

      if ($roles === 'results') {
        echo educare_guide_for("Note: Carefully fill out all data in your import file. Missing any required fields may cause issues during the import process. While importing results, student details are automatically retrieved from the student list, so ensure all students are added correctly.");
      } else {
        echo educare_guide_for("Note: Please ensure all details in your import file are filled out accurately. Missing any required fields may cause issues during the import process. Verify your data carefully before proceeding. Required or default fields must not be omitted. (Fields may vary based on Educare settings.)");
      }
      ?>

      <label for="file_selector_box" class="drop-container" id="dropcontainer">
        <span class="drop-title">Drop files here</span>
        <p>or</p>
        <input type="file" id="file_selector_box" name="csv_file" accept=".csv">
        <p>Files must be CSV files with the extension <b>.csv</b></p>
      </label>

      <button class="btn btn-success d-flex gap-2 align-items-center justify-content-center mt-4" type="submit" name="import_data"><i class="dashicons dashicons-database-import"></i> <?php echo sprintf(__('Import %s', 'educare'), esc_html($roles));?></button>

      <?php
      $nonce = wp_create_nonce('educare_import_data');
      echo '<input type="hidden" name="nonce" value="'.esc_attr($nonce).'">';
      ?>
    </div>
  </form>

  <div class="demo mt-5 p-5 bg-white rounded">
  <?php 
    echo educare_guide_for("If you are unsure how to create an import file, don't worry. Educare will assist you. Click the <b>Generate Demo File</b> button to generate and download a demo file.");

    echo '<div class="d-none">';
    educare_get_forms($roles);
    echo '</div>';

    $demo_nonce = wp_create_nonce('educare_demo_data');
    ?>

    <div class="educare_data_field">
      <div class="educareImportDemo_demo_nonce" data-value="<?php echo esc_attr($demo_nonce);?>"></div>
    </div>
    
    <div class="select add-subject">
      <div>
        <label for="total_demo" class="form-label">Total <?php echo esc_html($roles)?>:</label>
        <select id="total_demo" name="total_demo" class="form-control">
          <?php 
          for ($i=0; $i < 105; $i+=5) {
            if ($i == 0) {
              continue;
            }

            echo '<option value="'.esc_attr($i).'">'.esc_html($i).'</option>';
          }
          ?>
        </select>
      </div>
    </div>
    
    <div id="demo_data"></div>

    <button id="download_demo" class="btn btn-outline-secondary d-flex gap-2 align-items-center justify-content-center mt-3"><i class="dashicons dashicons-printer"></i> <?php _e('Generate Demo File', 'educare');?></button>
    
  </div>
  <?php
}



/**
 * Exports student data into a CSV file.
 *
 * @since 1.0.0
 * @last-update 1.6.0
 * 
 * @param array $data  An array containing student, teachers or results data organized by categories.
 * @param string $roles The roles associated with the exported data (e.g., student, teachers, results).
 *
 * @return void The function generates and saves a CSV file with the provided student data.
 */
function educare_generate_export_files($data, $roles) {
  if ($data) {
    $filename = EDUCARE_DIR.'assets/demo-files/demo-file-for-'.esc_attr($roles).'.csv';
    $file = fopen($filename, 'w');

    // Extract headers from the first row of data
    $headers = array_keys($data[0]);
    fputcsv($file, $headers);

    // Add data rows
    foreach ($data as $data_row) {
      fputcsv($file, $data_row);
    }

    fclose($file);
  }
}


/**
 * Generates and serves a demo CSV file for importing student data.
 *
 * @since 1.0.0
 * @last-update 1.6.0
 * 
 * @return void Generates a demo CSV file and provides a download link for it.
 */
function educare_process_demo_file() {
  educare_verify_nonce('educare_demo_data');

  $total_demo = sanitize_text_field($_POST['total_demo']);

  wp_parse_str($_POST['form_data'], $_POST);
  $roles = sanitize_text_field($_POST['roles']);
  unset($_POST['roles'], $_POST['educare_attachment_url']);

  if ($roles === 'teachers') {
    echo educare_get_advance_banner('The features you are requesting are supported in the Educare Premium version. It includes advanced functionalities like teachers or staffer management.', false);
    die;
  }

  $data = $default = array();
  
  if (isset($_POST['default'])) {
    $default = $_POST['default'];
    unset($default['Status']);

    $data = array_merge($data, $default);
  }

  if ($roles == 'results') {
    if (isset($_POST['Subject'])) {
      $subject = educare_get_data('Class', $data['Class']);

      if ($subject) {
        $subject = array_flip($subject);
        $data = array_merge($data, $subject);
      }
    }
  } else {
    if (isset($_POST['Details'])) {
      $data = array_merge($data, $_POST['Details']);
    }
  }

  $demo_data = array();
  $count = 1;

  for ($i = 0; $i < $total_demo; $i++) {
    foreach ($default as $key => $value) {
      if (!$value) {
        if ($key == 'Name') {
          $value = 'Name '. $count;
        }
        if ($key == 'Roll_No' || $key == 'Regi_No') {
          $value = $count;
        }
      }

      $data[$key] = $value;
    }
    
    $demo_data[$i] = $data;
    $count++;
  }

  educare_generate_export_files($demo_data, $roles);

  echo educare_show_msg('Successfully generated demo file for ' . $roles . '. Total ' . $total_demo . ' ' . $roles . ' have been added to this file. You can modify or follow this file structure to create your own import files.<br><strong>Notes:</strong> This file is created based on your current settings (class templates, additional fields...). If you make any changes to the Educare (plugin) settings, this demo file may not work. For this, you need to modify or re-generate this file.');

  $demoFileURL = EDUCARE_URL . 'assets/demo-files/demo-file-for-' . esc_attr($roles) . '.csv';

  echo '<a class="d-inline-flex gap-1 align-items-center btn btn-success" href="' . esc_url($demoFileURL) . '" download><i class="dashicons dashicons-download"></i> '.__('Download File', 'educare').'</a>';
  die;
}

add_action('wp_ajax_educare_process_demo_file', 'educare_process_demo_file');

/**
 * Slice part of array
 * 
 * @since 1.2.0
 * @last-update 1.6.0
 * 
 * @param array $array where to slice
 * @param str $offset slice start
 * @param str $length slice end
 * 
 * @return new array()
 */
function educare_array_slice($array, $offset, $length = null, $start = 0) {
  $offset = array_search($offset, array_keys($array));
  $slice_array = array_slice($array, $offset);

  if ($length) {
    $length = array_search($length, array_keys($slice_array));
    $length++;
  } else {
    $length = count($array);
  }

  $slice_array = array_slice($slice_array, $start, $length);

  return $slice_array;
}
