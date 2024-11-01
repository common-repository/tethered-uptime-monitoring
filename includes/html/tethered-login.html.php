<?php

if(!defined( 'ABSPATH' )){
	exit;
}

$this->load_part('head', "Login");

$tetheredErrorMessage = false;
$tetheredOnboarded = false;

$tetheredConnectRedirect = false;
if(!empty($_GET['tethered_login_connect'])){
  $tetheredConnectRedirect = true;
}

if(!empty($_POST['tethered_account_submit'])){
  if(wp_verify_nonce( sanitize_text_field( $_POST['tethered_account_nonce'] ), 'tethered_account_nonce' )){
    $action = sanitize_text_field( $_POST['tethered_action'] );
  
    $availableActions = array('sign_in', 'sign_up');
  
    if(!empty($action) && in_array($action, $availableActions)){
      $name = sanitize_text_field( $_POST['tethered_name'] );
      $email = sanitize_text_field( $_POST['tethered_email'] );
      $password = sanitize_text_field( $_POST['tethered_password'] );
    
      switch($action){
        case 'sign_in':
  
          $loggedIn = $this->login($email, $password);
          
          if(!empty($loggedIn)){
            
            if($tetheredConnectRedirect){
              $this->redirect_to_page($this->plugin_home_page_url);
            } else {
              $this->refresh_page();
            }
  
          } else {
            $tetheredErrorMessage = "Something went wrong whilst logging in. Please ensure that you have entered the correct email address and password.";
          }
          
          break;
  
        case 'sign_up':
          $registered = $this->register($name, $email, $password);
          
          if(!empty($registered)){
            
            if($tetheredConnectRedirect){
              $this->redirect_to_page($this->plugin_home_page_url);
            } else {
              $this->refresh_page();
            }
  
          } else {
            $tetheredErrorMessage = "Something went wrong whilst registering your account. Please try again.";
          }
          break;
      }
    } else {
      $tetheredErrorMessage = "Oops, something went wrong... Please try again.";
    }
  } else {
    $tetheredErrorMessage = "Nonce verification failed... Please try again.";
  }
} else {
  /* Onboarding Improvement */
  if(!empty($_GET['tethered_onboarding']) && empty($_GET['tethered_manual'])){
    $tetheredOnboarded = $this->autoOnboard();
    
    if(empty($tetheredOnboarded->success)){
      if(!empty($tetheredOnboarded->error)){
        $tetheredErrorMessage = $tetheredOnboarded->error . '<br> Please sign in with your Tethered account above.';
      }
    } else {
      /* Successfully onboarded */
      /* Lets check if there site was added succesfully */
      if(!empty($tetheredOnboarded->site_added)){
        $this->redirect_to_page($this->plugin_home_page_url);
      }
    }
  }
}

?>

<div id="tethered_body">

  <div class="tethered_account_form_container">
    <h1 class="tethered_form_title">Sign <span id="tethered_form_title_action">in</span> to <span class="tethered_highlight_bold">Tethered</span></h1>
    <form class="tethered_form" id="tethered_account_form" method="POST">
      <div class="tethered_input_group" data-type="name" style="display: none">
        <input type="text" name="tethered_name" id="tethered_name" class="tethered_input" placeholder="Your Name">
      </div>
      <div class="tethered_input_group" data-type="email">
        <input type="text" name="tethered_email" id="tethered_email" class="tethered_input" placeholder="Email Address" value="<?php echo !empty($tetheredOnboarded && !empty($tetheredOnboarded->existing)) ? esc_attr($tetheredOnboarded->existing) : '' ;?>" required>
      </div>
      <div class="tethered_input_group" data-type="password">
        <input type="password" name="tethered_password" id="tethered_password" class="tethered_input" placeholder="Password" required>
      </div>
      <div class="tethered_input_group" data-type="submit">
        <input type="submit" value="Sign In" name="tethered_account_submit" id="tethered_account_submit" class="tethered_input tethered_button tethered_primary_button">
        <input type="hidden" name="tethered_action" id="tethered_action" value="sign_in">
        <input type="hidden" name="tethered_account_nonce" id="tethered_account_nonce" value="<?php echo esc_attr( wp_create_nonce('tethered_account_nonce') ); ?>">
      </div>
      <?php if(!empty($tetheredErrorMessage)){ ?>
        <div class="tethered_account_error_message"><p><?php echo wp_kses($tetheredErrorMessage, ['div' => [], 'p' => [], 'strong' => [], 'br' => []]); ?></p></div>
      <?php } ?>
    </form>
    <div class="tethered_form_links">
      <p id="tethered_sign_up_links">Don't have an account? <span class="tethered_form_link" id="tethered_signup_link">Sign up</span></p>
      <p id="tethered_sign_in_links" style="display: none;">Already have an account? <span class="tethered_form_link" id="tethered_signin_link">Sign in</span></p>
    </div>
  </div>

</div>

<?php

$this->load_part('foot');

?>