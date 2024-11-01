<?php

if(!defined( 'ABSPATH' )){
	exit;
}

$this->load_part('head', "");

$user = wp_get_current_user();

?>

<div id="tethered_body" class="tethered_flex_center">
    
  <div class="tethered_get_started_stage" data-stage="1">
    <div class="tethered_get_started_content tethered_onboarding_content">
      <h1 class="tethered_onboarding_title">Get Started</h1>
      <div class="tethered_onboarding_card_container">
        <div class="tethered_onboarding_card">
          <div class="tethered_onboarding_card_inner">
            <div class="tethered_onboarding_card_head">
              <h1>Instant Account</h1>
            </div>
            <div class="tethered_onboarding_card_body">
              <div>
                <p>Monitor</p>
                <p class="tethered_underline"><?php echo esc_html(preg_replace('/https:\/\/|http:\/\//','',$this->site_url)); ?></p>
                <p>for <span class="tethered_underline">Free</span></p>
              </div>
              <a href="<?php echo esc_url($this->plugin_account_page_url . '&tethered_login_connect=1&tethered_onboarding=1'); ?>" class="tethered_button tethered_primary_button" id="tethered_auto_register_connect_site">One-Click Sign Up*</a>

            </div>
          </div>
        </div>
        <div class="tethered_onboarding_card">
          <div class="tethered_onboarding_card_inner">
            <div class="tethered_onboarding_card_head">
              <h1>Manual</h1>
            </div>
            <div class="tethered_onboarding_card_body">
              <div class="tethered_flex_center">
                <p>Let me create my Tethered account manually</p>
              </div>
              <a href="<?php echo esc_url(TETHERED::TETHERED_APP_REGISTER_LINK); ?>" target="_BLANK" class="tethered_button tethered_primary_button" id="tethered_login_connect_site">Create Account</a>
            </div>
          </div>
        </div>
      </div>
      <div>
        <p class="tethered_onboarding_note">*We will automatically create an account for you using <strong><?php echo esc_html($user->data->user_email); ?></strong></p>
      </div>
      <a href="<?php echo esc_url($this->plugin_account_page_url . '&tethered_login_connect=1&tethered_onboarding=1&tethered_manual=1'); ?>" class="tethered_underline tethered_onboarding_login_link">I already have an account, log me in</a>
    </div>
  </div>

</div>

<?php

$this->load_part('foot');

?>