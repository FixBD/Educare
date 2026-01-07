<?php
function educare_update_database($get_status = false) {
  global $wpdb;
  $educare_settings = $wpdb->prefix."educare_settings";
  $educare_results = $wpdb->prefix."educare_results";
  $status = false;

  if ($get_status) {
   if (educare_add_updated_column($get_status)) {
      return true;
   }
  }

  $status = educare_add_updated_column($get_status);

  // update extra field
  $data = educare_check_settings('Extra_field');
  $new_data = array();

  if ($data) {
    $i = 0;
    foreach ($data as $value) {
      if (is_string($value)) {
        // get title of field
        $title = substr(strstr($value, ' '), 1);
        // get type of field
        $type = strtok($value, " ");

        $new_data[$i]['title'] = $title;
        $new_data[$i]['type'] = $type;
        $new_data[$i]['for'] = array();

        $i++;
      }
    }

    if ($new_data) {
      if ($get_status) {
        return true;
      }

      // Ensure safe handling of JSON data
      $data_json = json_encode($new_data);
      // Run a manual query to update based on 'list'
      $sql = $wpdb->prepare(
        "UPDATE $educare_settings SET data = %s WHERE `list` = %s",
        $data_json, 
        'Extra_field'
      );

      $result = $wpdb->query($sql);

      // Check for success
      if ($result === false) {
        // $status = false;
        // error_log("Database update failed: " . $wpdb->last_error);
      } else {
        // echo "Update successful!";
        $status = true;
      }
    }
  }

  
  // update settings
  $settings = educare_check_settings('Settings');
  $new_display = array();

  if ($settings) {
    $display = isset($settings->display) ? $settings->display : '';

    if ($display) {
      foreach ($display as $key => $value) {
        if (is_array($value)) {
          $title = isset($value['0']) ? sanitize_text_field($value['0']) : '';

          if ($title) {
            $new_display[$key]['title'] = sanitize_text_field(str_replace('_', ' ', $title));
            $new_display[$key]['status'] = sanitize_text_field($value[1]);
            $new_display[$key]['type'] = 'text';
          }
        }
      }
    }

    if ($new_display) {
      if ($get_status) {
        return true;
      }

      $settings->display = json_decode(json_encode($new_display));

      // Ensure safe handling of JSON settings
      $settings_json = json_encode($settings);
      // Run a manual query to update based on 'list'
      $sql = $wpdb->prepare(
        "UPDATE $educare_settings SET data = %s WHERE `list` = %s",
        $settings_json, 
        'Settings'
      );

      $result = $wpdb->query($sql);

      // Check for success
      if ($result === false) {
        // $status = false;
        // error_log("Database update failed: " . $wpdb->last_error);
      } else {
        // echo "Update successful!";
        $status = true;
      }
    }
  }


  // update students subject
  $educare_students = educare_update_subject_db('educare_students', $get_status);

  if ($educare_students) {
    if ($get_status) {
      return true;
    }

    // update the database
    $status = educare_update_subject_db('educare_students');
  }

  // update results subject
  $educare_results = educare_update_subject_db('educare_results', $get_status);

  if ($educare_results) {
    if ($get_status) {
      return true;
    }

    // update the database
    $status = educare_update_subject_db('educare_results');
  }

  return $status;
}


function educare_update_subject_db($db = 'educare_results', $get_status = false) {
  global $wpdb;
  $subject_db = $wpdb->prefix . $db;
  $status = false;
  // Fetch all records as an array of objects
  $results = $wpdb->get_results("SELECT * FROM $subject_db");

  if (!empty($results)) {
    foreach ($results as $row) {
      $details = json_decode($row->Details, true);
      $subject = json_decode($row->Subject, true);
      $new_subject = array();

      if ($subject) {
        foreach ($subject as $subject_name => $subject_data) {
          if (!is_string($subject_data)) {
            continue;
          }

          $optional = substr(strstr($subject_data, ' '), 1);
          $marks = $subject_data;
          
          if (strpos($marks, ' ') !== false) {
            // Find the last space and get the substring after it
            $marks = substr($marks, strrpos($marks, ' ') + 1);
          }

          // marks field only available in 'educare_results' table
          if ($db =='educare_results') {
            $new_subject[$subject_name]['marks'] = $marks;
          }

          $new_subject[$subject_name]['optional'] = $optional ? 'yes' : '';
        }


        if ($new_subject) {
          if ($get_status) {
            return true;
          }

          $row->Subject = json_encode($new_subject);
  
          $status = true;
        }
      }

      if ($details) {
        if (isset($details['Photos'])) {
          if ($get_status) {
            return true;
          }

          $photos = isset($details['Photos']) ? $details['Photos'] : '';
          unset($details['Photos']);

          $new_details = array();

          foreach ($details as $key => $value) {
            $key = str_replace('_', ' ', $key);
            $new_details[$key] = $value;
          }

          $others = array(
            'Photos' => $photos
          );

          $row->Details = json_encode($new_details);
          $row->Others = json_encode($others);
          $row->Status = 'publish';

          $status = true;
        }
      }

      if ($status) {
        $data_id = $row->id;
        unset($row->id);
        
        // request to processing update
        $process = $wpdb->update($subject_db, json_decode(json_encode($row), TRUE), array('id' => $data_id));
  
        if ( $process === false ) {
          $status = false;
        } else {
          // echo "Update successful!";
          $status = true;
        }
      }
    }
  }

  return $status;
}


function educare_add_column($args = array(), $get_status = false) {
  global $wpdb;

  // Set default values for missing keys
  $defaults = array(
    'table' => '',
    'name' => '',
    'type' => '',
    'reference' => '',
    'position' => 'AFTER',
  );
  
  $args = wp_parse_args($args, $defaults);

  // Sanitize table and column names
  $table_name = sanitize_key($wpdb->prefix . $args['table']);
  $column = sanitize_text_field($args['name']);
  $column_type = sanitize_text_field($args['type']);
  $reference_column = sanitize_text_field($args['reference']);
  $position = strtoupper(trim($args['position']));

  // Validate table, column, and type
  if (empty($table_name) || empty($column) || empty($column_type)) {
    // return "Error: Missing required arguments (table, name, or type).";
    return false;
  }

  // Ensure position is only AFTER or BEFORE
  if (!in_array($position, ['AFTER', 'BEFORE'], true)) {
    $position = 'AFTER';
  }

  // Check if the column already exists
  $column_exists = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
      WHERE TABLE_NAME = %s AND COLUMN_NAME = %s 
      AND TABLE_SCHEMA = DATABASE()",
    $table_name, $column
  ));

  if ($column_exists == 0) {
    if ($get_status) {
      return true;
    }

    // Check if the reference column exists
    $reference_exists = $wpdb->get_var($wpdb->prepare(
      "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = %s AND COLUMN_NAME = %s 
        AND TABLE_SCHEMA = DATABASE()",
      $table_name, $reference_column
    ));

    $position_sql = (!empty($reference_column) && $reference_exists) ? "$position `$reference_column`" : "";

    // Construct and execute the ALTER TABLE query
    $sql = "ALTER TABLE `$table_name` ADD COLUMN `$column` $column_type $position_sql;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $wpdb->query($sql);
    
    return true;
  }

  return false;
}


function educare_add_updated_column($get_status = false) {
  $updated_db = array(
    // results database
    'educare_results' => array(
      array(
        'name' => 'Others',
        'type' => 'longtext NOT NULL',
        'reference' => 'GPA',
        'position' => 'AFTER'
      ),
      array(
        'name' => 'Status',
        'type' => 'VARCHAR(80) NOT NULL',
        'reference' => 'Others',
        'position' => 'AFTER'
      )
    ),

    // students database
    'educare_students' => array(
      array(
        'name' => 'Status',
        'type' => 'VARCHAR(80) NOT NULL',
        'reference' => 'Others',
        'position' => 'AFTER'
      )
    ),
  
    // marks database
    'educare_marks' => array(
      array(
        'name' => 'Group',
        'type' => 'longtext NOT NULL',
        'reference' => 'Class',
        'position' => 'AFTER'
      ),
      array(
        'name' => 'Others',
        'type' => 'longtext NOT NULL',
        'reference' => 'Details',
        'position' => 'AFTER'
      )
    )
  );

  $status = false;

  foreach ($updated_db as $table => $table_data) {
    foreach ($table_data as $column) {
      $column_name = $column['name'];
      $column_type = $column['type'];
      $column_reference = $column['reference'];
      $column_position = $column['position'];

      $status = educare_add_column(array(
        'table' => $table,
        'name' => $column_name,
        'type' => $column_type,
        'reference' => $column_reference,
        'position' => $column_position
      ), $get_status);
    }

    if ($status) {
      if ($get_status) {
        return true;
      }
    }
  }

  return $status;
}
