<?php
function add_educare_dashboard_widgets() {
  remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' ); // Removes the Activity widget

  if (current_user_can( 'administrator', 'educare_admin' )) {
    wp_add_dashboard_widget(
      'educare_widget',
      'Educare',
      'educare_dashboard_widget'
    );
  }
}

add_action( 'wp_dashboard_setup', 'add_educare_dashboard_widgets' );



function educare_dashboard_widget() {
  echo '<div class="educare-widget">';
    echo '<div class="logo text-center">
      <a href="'.admin_url( 'admin.php?page=educare-all-students' ).'">
        <img src="'.esc_url(EDUCARE_URL . 'assets/img/educare.svg').'" alt="Educare" /><br>
        '.esc_html('v' . EDUCARE_VERSION).'
      </a>
    </div>';

    echo '<div class="d-flex">';
      echo '<a class="all-data" href="'.admin_url( 'admin.php?page=educare-all-students' ).'">';
        echo __('All Students', 'educare');
      echo '</a>';

      echo '<a class="all-data" href="'.admin_url( 'admin.php?page=educare-all-teachers' ).'">';
        echo __('All Staff', 'educare');
      echo '</a>';

      echo '<a class="all-data" href="'.admin_url( 'admin.php?page=educare-all-results' ).'">';
        echo __('All Results', 'educare');
      echo '</a>';
    echo '</div>';

    if (current_user_can( 'administrator' )) {
      echo '<div class="d-flex my-2">';
        echo '<a href="'.admin_url( 'admin.php?page=educare-attendance' ).'">';
          echo __('Attendance', 'educare');
        echo '</a>';

        echo '<a href="'.admin_url( 'admin.php?page=educare-management' ).'">';
          echo __('Management', 'educare');
        echo '</a>';

        echo '<a href="'.admin_url( 'admin.php?page=educare-settings' ).'">';
          echo __('Settings', 'educare');
        echo '</a>';
      echo '</div>';
    }
  echo '</div>';
}


function educare_get_unlock_banner($dismissible = false) {
  $msg = '<div class="row mb-1 d-flex align-items-center text-start">
    <div class="col-md-1">
      <div class="d-md-flex justify-content-center educare-status-icon">
        <span class="dashicons dashicons-lock text-warning"></span>
      </div>
    </div>
    <div class="col-md-11">
      <h5 class="m-0">'.__('Unlock Premium', 'educare').'</h5>
    </div>
  </div>

  <div class="row text-start">
    <div class="col-md-1"></div>
      <div class="col-md-11">
        '.__('You are currently using the free version of Educare. Upgrade to Educare Premium for enhanced functionality and more powerful features.', 'educare').'

        <p class="mt-3 mb-1">
          <span class="dashicons dashicons-info text-black-50"></span> 
          '.sprintf(
            __('%1$s is free and always will be. However, the premium version unlocks advanced features to further enhance your experience. Discover the %2$s and explore it\'s full capabilities.', 'educare'),
            'Educare',
            '<a href="https://fixbd.com/plugins/educare">' . __('premium features', 'educare') . '</a>'
          ).'
        </p>
    </div>
  </div>';

  $feedback = '<div class="row mb-1 d-flex align-items-center text-start">
    <div class="col-md-1">
      <div class="d-md-flex justify-content-center educare-status-icon">
        <span class="dashicons dashicons-star-filled text-warning"></span>
      </div>
    </div>
    <div class="col-md-11">
      <h5 class="m-0">'.__('We Value Your Feedback!', 'educare').'</h5>
    </div>
  </div>

  <div class="row text-start">
    <div class="col-md-1"></div>
      <div class="col-md-11">
        '.__('Got 2 minutes? We\'d love to hear your thoughts on Educare! Your insights help us innovate and bring you even more powerful features.', 'educare').'

        <p class="mt-3 mb-1">
          <span class="dashicons dashicons-thumbs-up text-black-50"></span>
          '.sprintf(
            __('Your %1$s means the world to us! It helps us improve %2$s and bring you even more powerful features.', 'educare'),
            '<a href="https://wordpress.org/support/plugin/educare/reviews/?filter=5">' . __('feedback', 'educare') . '</a>',
            '<b>Educare</b>'
          ).'
        </p>
    </div>
  </div>';

  if ($dismissible) {
    if (educare_guide_for($msg, 'warning')) {
      return educare_guide_for($msg, 'warning');
    } else {
      if (educare_guide_for($feedback, 'success')) {
        return educare_guide_for($feedback, 'success');
      }
    }
  } else {
    return educare_show_msg($msg, 'warning', false);
  }

  return false;
}


function educare_get_advance_banner($msg = '', $dismissible = true) {
  $output = '<div class="row mb-1 d-flex align-items-center text-start">
    <div class="col-md-1">
      <div class="d-md-flex justify-content-center educare-status-icon">
        <span class="dashicons dashicons-lock text-warning"></span>
      </div>
    </div>
    <div class="col-md-11">
      <h5 class="m-0">'.__('Unlock Premium', 'educare').'</h5>
    </div>
  </div>

  <div class="row text-start">
    <div class="col-md-1"></div>
      <div class="col-md-11">
        '.esc_html__($msg, 'educare').'

        <p class="mt-3 mb-1">
          <span class="dashicons dashicons-info text-black-50"></span> 
          '.sprintf(
            __('%1$s is free and always will be. However, the premium version unlocks advanced features to further enhance your experience. Discover the %2$s and explore it\'s full capabilities.', 'educare'),
            'Educare',
            '<a href="https://fixbd.com/plugins/educare">' . __('premium features', 'educare') . '</a>'
          ).'
        </p>
    </div>
  </div>';

  if ($dismissible) {
    if (educare_guide_for($output, 'warning')) {
      return educare_guide_for($output, 'warning');
    }
  } else {
    return educare_show_msg($output, 'warning', false);
  }
  
  return false;
}


?>