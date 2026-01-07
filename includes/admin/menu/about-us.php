<?php
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * ### About Educare
 * 
 * Educare is a powerful online School/College students & results management system dev by FixBD. This plugin allows you to manage and publish students results. This is a School/College students & results management plugin that was created to make WordPress a more powerful CMS.
 * 
 * @since 1.0.0
 * @last-update 1.6.0
 */
?>

<div class="educare-container">
  <div class="educare_post">
    <div class="educare_post_content about">

      <div class="logo mt-5 mb-4">
        <img src="<?php echo esc_url(EDUCARE_URL . 'assets/img/educare.svg'); ?>" alt="Educare" /><br>
        <?php echo esc_html('v' . EDUCARE_VERSION); ?>
      </div>

      <h4 style="font-size: 22px; line-height: 1.4"><?php echo sprintf(__('Educare is a powerful online School, College, students & results management system dev by %s. This plugin allows you to manage and publish students results. This is a school, college, students & results management plugin that was created to easily manage institute, academy or student results at online.', 'educare'), '<a href="https://fixbd.com"><img src="' . esc_url(EDUCARE_URL . 'assets/img/fixbd.svg') . '" width="50px" alt="fixbd" /></a>') ?></h4>

      <p><?php _e('Educare help you to easily control over your institute students at online. You can easily Add/Edit/Delete Teachers, Students, Results, Class, Group, Exam, Rating Scale, Year, Extra Field, Custom Result Rules, Auto result calculations and much more… Also you can add marks, promote or import & export unlimited students and results just one click!', 'educare'); ?></p>

      <hr class="my-5">

      <div class="row g-5">
        <div class="col-12 col-xxl-6">
          <div class="select">
            <div class="logo mt-0">
              <img src="<?php echo esc_url(EDUCARE_URL . 'assets/img/marks.svg'); ?>" alt="Vision" />
            </div>

            <div>
              <h4><?php _e('Our Vision', 'educare'); ?></h4>
              <p><?php _e('We are committed to aligning global result systems with Educare. We believe in freedom and recognize the value of your project. Connect with us and let’s work together to bring your vision to life!', 'educare'); ?></p>
            </div>
          </div>
        </div>
        <div class="col-12 col-xxl-6">
          <div class="select">
            <div class="logo mt-0">
              <img src="<?php echo esc_url(EDUCARE_URL . 'assets/img/achivement.svg'); ?>" alt="Vision" />
            </div>

            <div>
              <h4><?php _e('Our Mission', 'educare'); ?></h4>
              <p><?php _e('Our mission is to develop powerful tools that drive effective growth. Our future goal is to transform Educare into a comprehensive school management system.', 'educare'); ?></p>
            </div>
          </div>
        </div>
      </div>

      <?php do_action('educare_collaborator'); ?>

      <hr class="my-5">

      <p>
        <?php 
        echo '<b>Name:</b> Educare';
        echo '<br><b>Version:</b> ' . esc_html(EDUCARE_VERSION) . ' (Free)<br>'; 
        echo '<b>Autor:</b> <a href="https://fixbd.com" target="_blank">FixBD</a><br>';
        echo '<b>License:</b> <a href="https://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GPLv2 or later</a><br>';
        ?>
        
        <b>Settings Version:</b> <?php echo esc_html(EDUCARE_SETTINGS_VERSION); ?> <br>
        <b>Results Version:</b> <?php echo esc_html(EDUCARE_RESULTS_VERSION); ?> <br>
        <b>Changelog:</b> The change log is located in the <strong>changelog.md</strong> file in the plugin folder. You may also <a href="https://github.com/fixbd/educare/blob/educare/changelog.md" target="_blank">view the change logs</a> at online.
      </p>

      <p>The Educare plugin is a comprehensive project with a substantial codebase that requires continuous maintenance. Major updates can take weeks or even months of dedicated development.</p>

      <p>We do not generate any revenue from users of the free version. However, we are pleased to offer many <a href="https://fixbd.com/plugins/educare" target="_blank">Educare Premium</a> features completely free of charge—no payment is required to install or update the free version of Educare.</p>

      <p>Your <a href="https://wordpress.org/plugins/educare/#reviews" target="_blank">feedback</a> is invaluable in helping us enhance Educare. Please share your experience to contribute to its continuous improvement.</p>

      <p>If you're a theme developer, plugin author, or coding enthusiast, you can explore our <a href="http://github.com/fixbd/educare" target="_blank">DEVELOPMENT GUIDE</a> on GitHub. For detailed documentation, visit <a href="https://fixbd.com/docs/educare" target="_blank">Educare Docs</a>.</p>

      <p>If you encounter any issues and need our support (completely free!), feel free to contact us at: <a href="mailto:fixbd.org@gmail.com">fixbd.org@gmail.com</a>
      </p>
      
      <div class="mb-2">
        <strong class="d-block">Share Your Feedback:</strong>
        <a href="https://wordpress.org/plugins/educare/#reviews" target="_blank">https://wordpress.org/plugins/educare/#reviews</a>
      </div>

      <div class="mb-2">
        <strong class="d-block">Educare Support Forum:</strong>
        <a href="https://wordpress.org/support/plugin/educare" target="_blank">https://wordpress.org/support/plugin/educare</a>
      </div>

      <div class="mb-4">
        <strong class="d-block">Get Professional Support:</strong>
        <a href="https://fixbd.com/support" target="_blank">https://fixbd.com/support</a>
      </div>

      <p>
        <a href="https://fixbd.com"><img src="<?php echo esc_url(EDUCARE_URL . 'assets/img/fixbd.svg'); ?>" width="100px" alt="FixBD" /></a>

        <?php do_action('educare_collaborator_logo'); ?>
      </p>
    </div>
  </div>
</div>

