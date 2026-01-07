<?php
educare_check_access();
?>

<div id="educare-loading">
  <div class="educare-spinner"></div>
</div>

<div class="educare_post" style="margin: 3%;">
  <div class="bg-white m-5 p-5 rounded mx-auto" style="max-width: 900px;">
    <?php
    echo '<form action="" id="educareActivation">';
      do_settings_sections('educare-license-activation-form');
    echo '</form>';
    ?>
  </div>
</div>