<?php

$pageheadTitle = "Tethered";

if(!empty($pageTitle) && trim($pageTitle) != ''){
  $pageheadTitle .= ' ' . $pageTitle;
}

?>

<div id="tethered_wrapper"> <!-- OPENING #TETHERED_WRAPPER -->

  <div id="tethered_top_section"> <!-- OPENING $TETHERED_TOP_SECTION -->

    <div id="tethered_head">
      <div class="tethered_head_left">
        <div><img src="<?php echo esc_url($this->logo);?>" id="tethered_head_logo"></div>
        <h1 id="tethered_head_title"><?php echo esc_html($pageheadTitle); ?></h1>
      </div>
      <div class="tethered_head_right">
        <?php if($this->logged_in && $this->current_page === 'tethered_account') { ?>
          <form method="POST" id="tethered_logout_form">
            <input type="submit" name="tethered_account_logout" id="tethered_account_logout" value="Log out" class="tethered_button tethered_secondary_button">
            <input type="hidden" name="tethered_logout_nonce" id="tethered_logout_nonce" value="<?php echo esc_attr( wp_create_nonce( 'tethered_logout_nonce' ) ); ?>">
          </form>
        <?php } ?>
        <a href="<?php echo esc_url($this->web_app_link); ?>" target="_BLANK" class="tethered_button tethered_primary_button">Go to Tethered.app</a>
      </div>
    </div>