<?php

if(!defined( 'ABSPATH' )){
	exit;
}

$this->load_part('head', "Account");

if(!empty($_POST['tethered_account_logout'])){
  if(wp_verify_nonce( sanitize_text_field( $_POST['tethered_logout_nonce'] ), 'tethered_logout_nonce' )) {
    $this->logout();
  }
  
  $this->refresh_page();
}

?>

<div id="tethered_body">

  <?php $this->load_part("loader"); ?>

  <div class="tethered_account_container" style="display: none;">

    <div class="tethered_account_content">

      <div class="tethered_account_block">

        <div class="tethered_account_image_container">
          <img src="<?php echo esc_url($this->image_dir_url . '/icons/person.svg'); ?>" id="tethered_account_image" alt="Account Image">
        </div>

        <div class="tethered_account_details">
          <div class="tethered_account_detail_row">
            <h3 id="tethered_account_username"></h3>
          </div>
        </div>

        <?php if(!empty($this->onboarded) && !empty($this->onboarded_email) && !empty($this->onboarded_password)){ ?>
        <div class="tethered_onboarding_details">
          <h3 class="tethered_onboarding_details_title">Onboarding Details</h3>
          <div class="tethered_onboarding_detail_row tethered_view_onboarding_details">
            <button class="tethered_button tethered_primary_button" id="tethered_onboarding_view_details">Show details</button>
          </div>
          <div class="tethered_onboarding_detail_row" style="display: none;">
            <p id="tethered_account_email"><strong>Email: </strong> <?php echo esc_html( $this->onboarded_email ); ?></p>
            <p id="tethered_account_password"><strong>Password: </strong> <?php echo esc_html( $this->onboarded_password ); ?></p>
          </div>
        </div>
        <?php } ?>

        <?php if(!empty($this->connected_site_details) && !empty($this->connected_site_details->id)){ ?>
        <div class="tethered_site_details">
          <h3 class="tethered_site_details_title">Monitor Details</h3>
          <div class="tethered_site_detail_row">
            <p id="tethered_site_url"></p>
          </div>
          <div class="tethered_site_detail_row">
            <p id="tethered_site_type"></p>
          </div>
          <div class="tethered_site_detail_row">
            <p id="tethered_site_last">
              <span id="tethered_site_last_label"></span>
              <span id="tethered_site_last_timestamp"></span>
            </p>
          </div>
          <div class="tethered_site_detail_row tethered_view_monitor">
            <button class="tethered_button tethered_primary_button" id="tethered_site_view_monitor">View Monitor</button>
          </div>
        </div>
        <?php } ?>
        
      </div>

    </div>

  </div>

</div>

<?php

$this->load_part('foot');

?>