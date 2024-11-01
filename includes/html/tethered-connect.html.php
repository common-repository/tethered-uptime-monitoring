<?php

if(!defined( 'ABSPATH' )){
	exit;
}

$this->load_part('head', "");

$user = wp_get_current_user();

if(!empty($_POST['tethered_add_connection'])) {
  
  if(wp_verify_nonce( sanitize_text_field( $_POST['tethered_add_connection_nonce'] ), 'tethered_add_connection_nonce' )){
    $config_type = sanitize_text_field( $_POST['config_type'] );
    $name = sanitize_text_field( $_POST['name'] );
    $link = sanitize_text_field( $_POST['link'] );
    $port = sanitize_text_field( $_POST['port'] );
    $keyword = sanitize_text_field( $_POST['keyword'] );
    $username = sanitize_text_field( $_POST['username'] );
    $password = sanitize_text_field( $_POST['password'] );
  
    $response = $this->add_site($config_type, $name, $link, $port, $keyword, $username, $password);
  }

  $this->refresh_page();
}

?>

<div id="tethered_body">
    
  <div class="tethered_get_started_stage" data-stage="1">
    <div class="tethered_get_started_content">
      <h2>Welcome, <?php echo esc_html($user->display_name); ?></h2>
      <h1>Ready to get upbeat about your <span class="tethered_hightlight_blue">uptime?</span></h1>
      <div class="tethered_get_started_image_container">
        <img src="<?php echo esc_url($this->image_dir_url . 'couldbe.png'); ?>" alt="This could be you!" class="tethered_get_started_image">
      </div>
      <div class="tethered_get_started_button_container">
        <?php if($this->logged_in) { ?>
          <a href="javascript:void(0);" class="tethered_button tethered_primary_button" id="tethered_connect_site">Monitor this site</a>
        <?php } else { ?>
          <a href="<?php echo esc_url($this->plugin_account_page_url . '&tethered_login_connect=1&tethered_onboarding=1'); ?>" class="tethered_button tethered_primary_button" id="tethered_auto_register_connect_site">Auto-Register and Continue</a>
          <a href="<?php echo esc_url($this->plugin_account_page_url . '&tethered_login_connect=1'); ?>" class="" id="tethered_login_connect_site">Create account and Continue</a>
        <?php } ?>
      </div>
    </div>
  </div>

  <div class="tethered_get_started_stage" data-stage="2" style="display: none;">
      <div class="tethered_get_started_content">
        <h1>Add Monitor</h1>

        <hr>

        <div class="tethered_connect_row" data-type="type">
          <div class="tethered_connect_row_description">
            <h3>Type</h3>
            <p>Select the type of connection you would like to monitor</p><br>
            <p>For more information on add-ons, please see here: <a href="https://tethered.app/app/add-ons" target="_BLANK">Add-ons</a></p>
          </div>
          <div class="tethered_connect_row_input">
            <ul class="tethered_config_type_list">
              <li class="tethered_config_type_list_item">
                <input type="radio" id="url" name="config_type" value="url" form="tethered_add_connection_form">
                <label for="url">
                  <img src="<?php echo esc_url($this->image_dir_url . 'icons/link-45deg.svg'); ?>" alt="" class="tethered_config_type_icon">
                  <span class="tethered_config_type_title">URL Check</span>
                  <span class="tethered_config_type_description">Link, API, Service</span>
                </label>
              </li>
              <li class="tethered_config_type_list_item">
                <input type="radio" id="port" name="config_type" value="port" form="tethered_add_connection_form">
                <label for="port">
                  <img src="<?php echo esc_url($this->image_dir_url . 'icons/ethernet.svg'); ?>" alt="" class="tethered_config_type_icon">
                  <span class="tethered_config_type_title">Port Check</span>
                  <span class="tethered_config_type_description">FTP, SMTP, POP3</span>
                </label>
              </li>
              <li class="tethered_config_type_list_item">
                <input type="radio" id="keyword" name="config_type" value="keyword" form="tethered_add_connection_form">
                <label for="keyword">
                  <img src="<?php echo esc_url($this->image_dir_url . 'icons/blockquote-left.svg'); ?>" alt="" class="tethered_config_type_icon">
                  <span class="tethered_config_type_title">Keyword Watcher</span>
                  <span class="tethered_config_type_description">Content changes</span>
                </label>
              </li>
              <li class="tethered_config_type_list_item tethered_add_on">
                <label for="ssl">
                  <img src="<?php echo esc_url($this->image_dir_url . 'icons/shield.svg'); ?>" alt="" class="tethered_config_type_icon">
                  <span class="tethered_config_type_title">SSL Certificate</span>
                  <span class="tethered_config_type_description">Monitor validity</span>
                </label>
              </li>
              <li class="tethered_config_type_list_item tethered_add_on">
                <label for="IPReputation">
                  <img src="<?php echo esc_url($this->image_dir_url . 'icons/flag.svg'); ?>" alt="" class="tethered_config_type_icon">
                  <span class="tethered_config_type_title">Spam Check</span>
                  <span class="tethered_config_type_description">IP Reputation</span>
                </label>
              </li>
              <li class="tethered_config_type_list_item tethered_add_on">
                <label for="domainThreats">
                  <img src="<?php echo esc_url($this->image_dir_url . 'icons/flag.svg'); ?>" alt="" class="tethered_config_type_icon">
                  <span class="tethered_config_type_title">Threat Detection</span>
                  <span class="tethered_config_type_description">Monitor integrity</span>
                </label>
              </li>
            </ul>
          </div>
        </div>

        <hr>

        <div class="tethered_connect_row" data-type="connection">
          <div class="tethered_connect_row_description">
            <h3>Monitor</h3>
            <p>Setup the monitor details</p>
          </div>
          <div class="tethered_connect_row_input">
            <div class="tethered_input_group" data-type="all">
              <label for="name" class="tethered_label">Name</label>
              <input type="text" name="name" id="name" class="tethered_input" placeholder="Monitor name, shown on status page..." form="tethered_add_connection_form" value="<?php echo esc_attr($this->site_title); ?>" required>
            </div>
            <div class="tethered_input_group" data-type="all">
              <label for="link" class="tethered_label">Link</label>
              <input type="text" name="link" id="link" class="tethered_input disabled" placeholder="Paste your link here..." form="tethered_add_connection_form" value="<?php echo esc_attr($this->site_url); ?>" required readonly title="This field is auto-filled">
            </div>
            <div class="tethered_input_group" data-type="port" style="display: none;">
              <label for="port" class="tethered_label">Port</label>
              <input type="text" name="port" id="port" class="tethered_input" placeholder="Your port that you would like monitored..." form="tethered_add_connection_form">
            </div>
            <div class="tethered_input_group" data-type="keyword" style="display: none;">
              <label for="keyword" class="tethered_label">Keyword</label>
              <input type="text" name="keyword" id="keyword" class="tethered_input" placeholder="Keyword/Phrase to monitor..." form="tethered_add_connection_form">
            </div>
          </div>
        </div>

        <hr>

        <div class="tethered_connect_row" data-type="advanced">
          <div class="tethered_connect_row_description">
            <h3>Advanced</h3>
            <p>Need to configure basic authentication? You can do that here, leave these empty if not required</p>
          </div>
          <div class="tethered_connect_row_input">
            <div class="tethered_input_group">
              <label for="username" class="tethered_label">Username</label>
              <input type="text" name="username" id="username" class="tethered_input" placeholder="Username..." form="tethered_add_connection_form">
            </div>
            <div class="tethered_input_group">
              <label for="password" class="tethered_label">Password</label>
              <input type="text" name="password" id="password" class="tethered_input" placeholder="Password..." form="tethered_add_connection_form">
            </div>
          </div>
        </div>

        <hr>
        
        <div class="tethered_connect_row">
          <form method="POST" id="tethered_add_connection_form">
            <input type="submit" name="tethered_add_connection" id="tethered_add_connection" class="tethered_button tethered_primary_button" value="Add Monitor">
            <input type="hidden" name="tethered_add_connection_nonce" id="tethered_add_connection_nonce" value="<?php echo esc_attr( wp_create_nonce( 'tethered_add_connection_nonce' ) ); ?>">
          </form>
        </div>

      </div>
    
  </div>

</div>

<?php

$this->load_part('foot');

?>