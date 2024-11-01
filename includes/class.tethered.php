<?php

class Tethered {

  private const ENDPOINT_SITE = "https://tethered.app/app/api/v1/site/";
  private const ENDPOINT_SITES = "https://tethered.app/app/api/v1/sites/";

  private const ENDPOINT_REPORT = "https://tethered.app/app/api/v1/site/report/";

  private const ENDPOINT_LOGIN = "https://tethered.app/app/api/v1/auth/login/";
  private const ENDPOINT_REGISTER = "https://tethered.app/app/api/v1/auth/register/";

  private const ENDPOINT_PROFILE = "https://tethered.app/app/api/v1/profile/me/";

  private const ENDPOINT_AUTH_OTL = "https://tethered.app/app/api/v1/auth/otl/";

  private const TETHERED_APP_FULL_REPORT_LINK = "https://tethered.app/app/site/report/";

  private const TETHERED_APP_LINK = "https://tethered.app/app/";

  private const TETHERED_APP_REGISTER_LINK = "https://tethered.app/app/register";
  
  private const STATUS_BAD_REQUEST_BOUNDARY = 400;
  
  protected $version;
  protected $plugin_dir;
  protected $plugin_dir_url;
  protected $plugin_name;
  protected $page_dir;
  protected $page_part_dir;

  protected $image_dir;
  protected $image_dir_url;

  protected $plugin_styles;
  protected $plugin_scripts;
  protected $plugin_scripts_vendor;

  protected $logo;
  protected $logo_full;
  protected $logo_full_dark;
  protected $logo_full_dark_small;

  protected $site_url;
  protected $site_title;
  protected $plugin_onboard_page_url;
  protected $plugin_home_page_url;
  protected $plugin_account_page_url;

  protected $logged_in;
  protected $logged_in_user;
  protected $web_app_link;
  protected $otl_auth_link;

  protected $current_page;

  protected $connected_site;
  protected $connected_site_details;

  protected $onboarding;
  protected $onboarded;
  protected $onboarded_email;
  protected $onboarded_password;

  protected $rest_route_report;
  protected $rest_route_profile;
  protected $rest_route_remove;


  public function __construct(){

    $this->logged_in = false;
    
    if(defined( 'TETHERED_VERSION' )){
      $this->version = TETHERED_VERSION;
    } else {
      $this->version = '1.0.3';
    }

    if(defined( 'TETHERED_PLUGIN_DIR' )){
      $this->plugin_dir = TETHERED_PLUGIN_DIR;
    } else {
      $this->plugin_dir = "";
    }

    if(defined( 'TETHERED_PLUGIN_DIR_URL' )){
      $this->plugin_dir_url = TETHERED_PLUGIN_DIR_URL;
    } else {
      $this->plugin_dir_url = "";
    }
    
    $this->plugin_name = "tethered";

    $this->page_dir = $this->plugin_dir . "includes/html/";

    $this->page_part_dir = $this->page_dir . "/parts/";

    $this->image_dir = $this->plugin_dir . "img/";
    $this->image_dir_url = $this->plugin_dir_url . "img/";

    $this->plugin_styles = $this->plugin_dir_url . "css/";
    $this->plugin_scripts = $this->plugin_dir_url . "js/";
    $this->plugin_scripts_vendor = $this->plugin_dir_url . "js/vendor/";

    $this->logo = $this->plugin_dir_url . "img/logo.svg";
    $this->logo_full = $this->plugin_dir_url . "img/logo_full.png";
    $this->logo_full_dark = $this->plugin_dir_url . "img/logo_full_dark.png";
    $this->logo_full_dark_small = $this->plugin_dir_url . "img/logo-dark-small.png";

    $this->site_url = get_site_url();
    $this->site_title = get_bloginfo( 'name' );
    $this->plugin_onboard_page_url = get_admin_url() . "admin.php?page=tethered_onboard";
    $this->plugin_home_page_url = get_admin_url() . "admin.php?page=tethered_monitor";
    $this->plugin_account_page_url = get_admin_url() . "admin.php?page=tethered_account";
    $this->web_app_link = false;

    $this->rest_route_report = $this->site_url . "/wp-json/tethered/v1/report/";
    $this->rest_route_profile = $this->site_url . "/wp-json/tethered/v1/profile/";
    $this->rest_route_remove = $this->site_url . "/wp-json/tethered/v1/remove/";

    $this->onboarding = false;

    $this->init();
    
  }

  private function init(){
    $this->set_current_page();

    $this->logged_in = $this->hasLoggedIn();
    if($this->logged_in){
      $this->otl_auth_link = $this->get_otl_auth_link();
      $this->web_app_link = $this->otl_auth_link;
    }

    $this->onboarded_email = false;
    $this->onboarded_password = false;
    $this->onboarded = $this->hasOnboarded();

    if(empty($this->web_app_link)){
      $this->web_app_link = "https://tethered.app/app";
    }
    
    $this->connected_site = $this->hasConnectedSite();
  }

  /**
   * Adds Tethered to the menu
   * 
   * @return void
   */
  public function load_admin_menu(){
    add_menu_page(
      "Monitor",
      'Monitor',
      'manage_options',
      'tethered_monitor',
      array( $this, 'load_page' ),
      TETHERED_PLUGIN_DIR_URL . "img/tethered_menu_icon.png"
    );

    add_submenu_page(
      "tethered_monitor",
      "Account",
      "Account",
      'manage_options',
      "tethered_account",
      array( $this, 'load_page' )
    );
  }

  public function register_rest_routes(){  
    
    if($this->logged_in){
      register_rest_route( 'tethered/v1', '/profile', array(
        'methods' => 'GET',
        'callback' => array($this, 'getProfileDetails'),
        'show_in_index' => false
      ));
    }
    
    if(!empty($this->connected_site)){
      register_rest_route( 'tethered/v1', '/report/(?P<site_id>\d+)', array(
        'methods' => 'GET',
        'callback' => array($this, 'getSiteReport'),
        'show_in_index' => false
      ));

      register_rest_route( 'tethered/v1', '/remove/(?P<site_id>\d+)', array(
        'methods' => 'GET',
        'callback' => array($this, 'removeSite'),
        'show_in_index' => false
      ));
    }

  }

  /**
   * Sets the current_page variable in the class
   * 
   * @return string
   */
  public function set_current_page(){
    if(!empty($_GET['page'])){
      $this->current_page = sanitize_title( $_GET['page'] );
    } else {
      $this->current_page = false;
    }

    return $this->current_page;
  }

  /**
   * Loads the respective page based on page slug
   * 
   * @param string $url
   * 
   * @return void
   */
  public function load_page($url = false){
    if(!empty($url)){
      include_once($url);
      return true;
    }
    
    $page = sanitize_title($_GET['page']);

    if(!empty($page)){
      $page_url = $this->get_page_url($page);
      include_once($page_url);
      return true;
    } else {
      return false;
    }
  }

  /**
   * Gets the URL for the respective page slug
   *
   * @param string $slug
   * 
   * @return string $page_url 
   */
  protected function get_page_url($slug){
    if(!empty($slug)){
      switch($slug) {
        case 'tethered_monitor':          
          if(empty($this->connected_site)){
            if(!$this->logged_in){
              $page_url = $this->page_dir . "tethered-onboard.html.php";
            } else {
              $page_url = $this->page_dir . "tethered-connect.html.php";
            }
          } else {
            $page_url = $this->page_dir . "tethered-monitor.html.php";
          }
          break;

        case 'tethered_account':
          if(!empty($_GET['tethered_onboarding'])){
            $this->onboarding = true;

            if(!empty($_GET['tethered_manual'])){
              $page_url = $this->page_dir . "tethered-login.html.php";
              return $page_url;
            }
          }
          if(!$this->logged_in && !$this->onboarding){
            $page_url = $this->page_dir . "tethered-onboard.html.php";
          } else if(!$this->logged_in && $this->onboarding) {
            $page_url = $this->page_dir . "tethered-login.html.php";
          } else {
            $page_url = $this->page_dir . "tethered-account.html.php";
          }
          break;

        default:
          $page_url = $this->page_dir . "tethered-monitor.html.php";
      }

      return $page_url;
    }

    return $this->page_dir . "tethered-monitor.html.php";
  }

  /**
   * Loads the respective page part based on part name
   * 
   * @return void
   */
  public function load_part($part, $pageTitle = ''){
    if(!empty($part)){
      $part_url = '';
      
      switch($part){
        case 'head':
          $part_url = $this->page_part_dir . "tethered-head.html.php";
          break;

        case 'foot':
          $part_url = $this->page_part_dir . "tethered-foot.html.php";
          break;
        
        case 'loader':
          $part_url = $this->page_part_dir . "tethered-loader.html.php";
          break;

        default:
          return false;
      }

      include_once($part_url);
    } else {
      return false;
    }
  }

  /**
   * Registers and enqueues Admin styles
   * 
   * @return void
   */
  public function load_admin_styles(){
    wp_register_style('tethered_theme_styles', $this->plugin_styles . "tethered-theme.css", array(), $this->version );
    
    wp_register_style('tethered_admin_styles', $this->plugin_styles . "tethered-admin.css", array(), $this->version );

    wp_enqueue_style( 'tethered_theme_styles' );
    wp_enqueue_style( 'tethered_admin_styles' );
  }

  /**
   * Enquesue Admin scripts
   * 
   * @return void
   */
  public function load_admin_scripts(){
    wp_register_script( 'tethered_admin_account_login', $this->plugin_scripts . "tethered-account-login.js", array('jquery'), $this->version, false );
    wp_register_script( 'tethered_admin_account', $this->plugin_scripts . "tethered-account.js", array('jquery'), $this->version, false );
    
    wp_register_script( 'tethered_admin_monitor', $this->plugin_scripts . "tethered-monitor.js", array('jquery'), $this->version, false );

    wp_register_script( 'tethered_apexcharts_library', $this->plugin_scripts_vendor . "apexcharts/dist/apexcharts.min.js", array('jquery'), $this->version, false );
    wp_register_script( 'tethered_charts_class', $this->plugin_scripts . "tethered-charts.js", array('jquery'), $this->version, false );

    wp_register_script( 'tethered_admin_connect', $this->plugin_scripts . "tethered-connect.js", array('jquery'), $this->version, false );
    
    // wp_enqueue_script( 'tethered_admin_monitor' );

    $reportHelpers = array(
      'operational_success_svg_code' => wp_remote_retrieve_body(wp_remote_get($this->image_dir_url . 'icons/check-circle-fill.svg')),
      'operational_failure_svg_code' => wp_remote_retrieve_body(wp_remote_get($this->image_dir_url . 'icons/x-circle-fill.svg')),
      'operational_pending_svg_code' => wp_remote_retrieve_body(wp_remote_get($this->image_dir_url . 'icons/clock-fill.svg')),
      'status_bad_request_boundary' => TETHERED::STATUS_BAD_REQUEST_BOUNDARY,
    );

    $connectedSiteId = 0;
    if(!empty($this->connected_site_details) && !empty($this->connected_site_details->id)){
      $connectedSiteId = $this->connected_site_details->id;
    }
    
    if($this->current_page == 'tethered_account'){
      if(!$this->logged_in){
        wp_enqueue_script( 'tethered_admin_account_login' );
      } else {
        wp_enqueue_script( 'tethered_admin_account' );
        wp_localize_script( 'tethered_admin_account', 'tethered_account_helpers', array(
          'rest_route_profile' => $this->rest_route_profile,
          'image_dir_url' => $this->image_dir_url,
          'rest_route_report' => $this->rest_route_report,
          'rest_route_remove' => $this->rest_route_remove,
          'site_id' => $connectedSiteId,
          'site_monitor_link' => $this->plugin_home_page_url,
        ) );
        wp_localize_script( 'tethered_admin_account', 'tethered_report_helpers', $reportHelpers );
      }
    }

    if($this->current_page == 'tethered_monitor'){
      if($this->logged_in && !empty($this->connected_site)){
        wp_enqueue_script( 'tethered_admin_monitor' );
        wp_localize_script( 'tethered_admin_monitor', 'tethered_monitor_helpers', array(
          'rest_route_report' => $this->rest_route_report,
          'rest_route_remove' => $this->rest_route_remove,
          'image_dir_url' => $this->image_dir_url,
          'site_id' => $connectedSiteId,
          'app_full_report_link' => Tethered::TETHERED_APP_FULL_REPORT_LINK . $connectedSiteId,
        ) );
        wp_localize_script( 'tethered_admin_monitor', 'tethered_report_helpers', $reportHelpers );
          
        wp_enqueue_script( 'tethered_apexcharts_library' );
        wp_enqueue_script( 'tethered_charts_class' );
      } else {
        wp_enqueue_script( 'tethered_admin_connect' );
      }
    }

    if($this->current_page == 'tethered_onboard'){
      if($this->logged_in){
        $this->redirect_to_page($this->plugin_home_page_url);
      }
    }
  }

  /**
   * Trick to refresh page
   * 
   * @return void
   */
  public function refresh_page(){
    echo wp_kses("<meta http-equiv='refresh' content='0'>", ['meta' => ['http-equiv' => true, 'content' => true]]);
    exit;
  }

  /**
   * Redirects to the specified url
   * 
   * @return void
   */
  public function redirect_to_page($url){
    echo wp_kses("<script>window.location.href = '" . esc_url($url) . "' </script>", ['script' => []]);
    exit;
  }

  /**
   * Checks if the user has logged in by checking for saved account details
   * 
   * @return boolean
   */
  private function hasLoggedIn(){
    $accountDetails = $this->getAccountDetails();

    if(!empty($accountDetails)){
      $apikey = $accountDetails->apikey;

      if(!empty($apikey)){
        $this->setLoggedInUser($apikey);
        return true;
      }
    }

    return false;
  }

  private function hasOnboarded(){
    if(!empty(get_option( 'tethered_onboarded' ))){
      $this->onboarded = true;
      $this->onboarded_email = get_option( 'tethered_onboarded_email' );
      $this->onboarded_password = get_option( 'tethered_onboarded_password' );
      return true;
    }

    return false;
  }

  /**
   * Sets the logged_in_user object variable
   * 
   * @return boolean
   */
  private function setLoggedInUser($apikey){
    if(!empty($apikey)){
      $this->logged_in_user = (object) array(
        'apikey' => sanitize_text_field( $apikey )
      );
      return true;
    }
    
    return false;
  }

  /**
   * Logs out a user
   * 
   * @return string $apikey
   */
  private function logout(){
    $accountDetails = get_option( 'tethered_account_details' );
    if(!empty($accountDetails)){
      update_option( 'tethered_account_details', '' );

      /* Lets also remove a connected site if any */
      if(!empty($this->connected_site) && !empty($this->connected_site_details) && !empty($this->connected_site_details->id)){
        $this->removeSite($this->connected_site_details->id);
      }
    }
  }

  /**
   * Logs in a user using email and password
   * 
   * @param string $email
   * @param string $password
   * 
   * @return string $apikey
   */
  private function login($email, $password){
    if(!empty($email) && !empty($password)){

      $email = sanitize_text_field( $email );
      $password = sanitize_text_field( $password );

      $response = wp_remote_post(TETHERED::ENDPOINT_LOGIN, array(
          'body' => array(
            'email' => $email,
            'password' => $password,
          )
        )
      );

      $responseBody = wp_remote_retrieve_body($response);
      
      if(!empty($responseBody) && $responseBody != '[]'){

        $decodedResponse = json_decode($responseBody);
        
        $apikey = $decodedResponse->apikey;
        
        $this->saveAccountDetails(
          array(
            'apikey' => $apikey
          )
        );

        $this->logged_in = true;
        $this->setLoggedInUser($apikey);

        return $apikey;
      } else {
        return false;
      }

    } else {
      return false;
    }
  }

  /**
   * Registers and logs in a user using their name, email and password
   * 
   * @param string $name
   * @param string $email
   * @param string $password
   * 
   * @return object $package
   */
  private function register($name, $email, $password){
    $package = (object) array(
      'success' => false,
      'error' => false,
      'code' => false
    );
    
    if(!empty($name) && !empty($email) && !empty($password)){

      $name = sanitize_text_field( $name );
      $email = sanitize_text_field( $email );
      $password = sanitize_text_field( $password );

      $response = wp_remote_post(TETHERED::ENDPOINT_REGISTER, array(
          'body' => array(
            'email' => $email,
            'username' => $name,
            'password' => $password,
            'platform' => 'wordpress'
          )
        )
      );
      
      $responseBody = wp_remote_retrieve_body($response);

      if(!empty($responseBody) && $responseBody != '[]'){

        $decodedResponse = json_decode($responseBody);
        
        $apikey = $decodedResponse->apikey;
        
        $this->saveAccountDetails(
          array(
            'apikey' => $apikey
          )
        );

        $this->logged_in = true;
        $this->setLoggedInUser($apikey);

        $package->success = true;
        $package->apikey = $apikey;
      } else {
        // As per our API -> we assume that it would fail due to the account already existing
        $package->error = "Account with '{$email}' already exists.";
        $package->code = 409; // 409 meaning conflict
      }

    }

    return $package;
  }

  /**
   * This auto onboards the user by attempting to log them in first, if not, we then create an account for them automatically
   * 
   * Once this has been done, we then add their site automatically
   * 
   * @return boolean
   */
  private function autoOnboard(){
    // IF they have already been flagged as onboarded, then return
    if($this->onboarded){
      return false;
    }
    
    $package = (object) array(
      'success' => false,
      'error' => false,
      'existing' => false,
      'registered' => false,
      'site_added' => false,
    );
    /* Lets check that they aren't already logged in */
    if(!$this->hasLoggedIn()){
      /* Lets register an account for them */
      $user = wp_get_current_user();
      if(!empty($user) && !empty($user->data)){
        $randomPassword = wp_generate_password();

        $registered = $this->register($user->data->display_name, $user->data->user_email, $randomPassword);
        if(!empty($registered) && !empty($registered->success)){
          $package->success = true;
          $package->registered = true;
          
          // Now that they are registered, lets add their site for them
          $config_type = sanitize_text_field( 'url' );
          $name = sanitize_text_field( get_bloginfo( 'title' ) );
          $link = sanitize_text_field( get_bloginfo( 'wpurl' ) );
        
          $added = $this->add_site($config_type, $name, $link);
          if(!empty($added)){
            $package->site_added = true;
          }
          
          update_option( 'tethered_onboarded', '1' );
          update_option( 'tethered_onboarded_email', $user->data->user_email );
          update_option( 'tethered_onboarded_password', $randomPassword );
        } else {
          // Failed registration
          if(!empty($registered->error)){
            $package->existing = $user->data->user_email;
            $package->error = $registered->error;
          }
        }
      } else {
        // User couldn't be retrieved
        $package->error = "Unfortunately we were not able to automatically retrive your user details";
      }
    } else {
      // Account already logged in
    }

    return $package;
  }

  /**
   * Saves the account details for the user
   * 
   * @param array $data
   * 
   * @return boolean
   */
  private function saveAccountDetails($data){
    if(!empty($data) && is_array($data)){
      $encodedData = sanitize_text_field( json_encode($data) );
      update_option( 'tethered_account_details', $encodedData );
      return true;
    } else {
      return false;
    }
  }

  /**
   * Gets the account options for the user
   * 
   * @return object $accountDetails
   */
  private function getAccountDetails(){
    $accountDetails = get_option( 'tethered_account_details' );
    if(!empty($accountDetails)){
      return json_decode($accountDetails);
    } else {
      return false;
    }
  }

  /**
   * Adds a site based on inputted connection data
   * 
   * @param string $config_type
   * @param string $name
   * @param string $link
   * @param string $port
   * @param string $keyword
   * @param string $username
   * @param string $password
   * 
   * @return 
   */
  private function add_site($config_type, $name, $link, $port = false, $keyword = false, $username = false, $password = false){
    if(!empty($config_type) && !empty($name) && !empty($link)){

      $config_type = sanitize_text_field($config_type);
      $name = sanitize_text_field($name);
      $link = sanitize_text_field($link);
      $port = sanitize_text_field($port);
      $keyword = sanitize_text_field($keyword);
      $username = sanitize_text_field($username);
      $password = sanitize_text_field($password);
      
      $response = wp_remote_post(TETHERED::ENDPOINT_SITE, array(
          'body' => array(
            'apikey' => $this->logged_in_user->apikey,
            'config_type' => $config_type,
            'config_title' => $name,
            'url' => $link,
            'config_port' => $port,
            'config_keyword' => $keyword,
            'config_username' => $username,
            'config_password' => $password,
          )
        )
      );

      $responseBody = wp_remote_retrieve_body($response);

      if(!empty($responseBody) && $responseBody != '[]'){

        $decodedResponse = json_decode($responseBody);

        if(!empty($decodedResponse->id)){
          $data_array['id'] = $decodedResponse->id;
          $this->saveConnectedSiteDetails($data_array);
          return $decodedResponse->id;
        } else {
          return false;
        }
        
      } else {
        return false;
      }

    } else {
      return false;
    }
  }

  /**
   * Checks if the user has connected the site
   * 
   * @return boolean 
   */
  private function hasConnectedSite(){
    $siteDetails = $this->getConnectedSiteDetails();

    if(!empty($siteDetails)){
      $this->setConnectedSite($siteDetails);

      return true;
    }

    return false;
  }

  private function setConnectedSite($siteDetails){
    if(!empty($siteDetails)){
      $this->connected_site_details = $siteDetails;
      return true;
    }

    return false;
  }

  /**
   * Saves the connected site data
   * 
   * @param array $data
   * 
   * @return boolean
   */
  private function saveConnectedSiteDetails($data){
    if(!empty($data) && is_array($data)){
      $encodedData = sanitize_text_field( json_encode($data) );
      update_option( 'tethered_connected_site_details', $encodedData );
      return true;
    } else {
      return false;
    }
  }

  /**
   * Gets the connected site ID
   * 
   * @return object $siteDetails
   */
  private function getConnectedSiteDetails(){
    $siteDetails = get_option( 'tethered_connected_site_details' );
    if(!empty($siteDetails)){
      return json_decode($siteDetails);
    } else {
      return false;
    }
  }

  /**
   * Gets the report for the site by its id
   * 
   * @param int $id
   * 
   * @return object $report
   */
  public function getSiteReport($id){

    $rest_requested = false;
    if(!empty($id['site_id'])){
      $id = $id['site_id'];
      $rest_requested = true;
    }

    if(!empty($id) && $this->logged_in){
      $id = sanitize_text_field( $id );
      $apikey = sanitize_text_field( $this->logged_in_user->apikey );
      
      $params = array(
        'apikey' => $apikey,
        'id' => $id,
      );
      $prepared_url = $this->prepareUrl(TETHERED::ENDPOINT_REPORT, $params);

      $response = wp_remote_get($prepared_url);

      $responseBody = wp_remote_retrieve_body($response);

      if(!empty($responseBody) && $responseBody != '[]'){
        if($rest_requested){
          $report = base64_encode($responseBody);
        } else {
          $report = json_decode($responseBody);
        }

        return $report;
      } else {
        return false;
      }
    }

    return false;
  }



  /**
   * Removes a site by its id
   * 
   * @param int $id
   * 
   * @return boolean $resposne
   */
  public function removeSite($id){

    $rest_requested = false;
    if(!empty($id['site_id'])){
      $id = $id['site_id'];
      $rest_requested = true;
    }

    if(!empty($id) && $this->logged_in){
      $id = sanitize_text_field( $id );
      $apikey = sanitize_text_field( $this->logged_in_user->apikey );
      
      $params = array(
        'apikey' => $apikey,
        'id' => $id,
      );
      $prepared_url = $this->prepareUrl(TETHERED::ENDPOINT_SITE, $params);

      $response = wp_remote_request($prepared_url, array(
        'method' => "DELETE"
      ));

      delete_option( 'tethered_connected_site_details' );

      return true;
    }

    return false;
  }



  /**
   * Gets the profile details for a user by their apikey
   *
   * @param string $apikey
   * 
   * @return object $details
   */
  public function getProfileDetails($rest){

    $rest_requested = false;
    if(!empty($rest)){
      $rest_requested = true;
    }

    
    if($this->logged_in){
      $apikey = sanitize_text_field( $this->logged_in_user->apikey );
      
      $params = array(
        'apikey' => $apikey
      );
      $prepared_url = $this->prepareUrl(TETHERED::ENDPOINT_PROFILE, $params);
      
      $response = wp_remote_get($prepared_url);

      $responseBody = wp_remote_retrieve_body($response);

      if(!empty($responseBody) && $responseBody != '[]'){
        if($rest_requested){
          $details = base64_encode($responseBody);
        } else {
          $details = json_decode($responseBody);
        }

        return $details;
      } else {
        return false;
      }
    }

    return false;
  }


  /**
   * Gets the OTL Auth link for the user to automatically login to the tethered web app
   * 
   * @return 
   */
  private function get_otl_auth_link(){
    $response = wp_remote_post(TETHERED::ENDPOINT_AUTH_OTL, array(
        'body' => array(
          'apikey' => $this->logged_in_user->apikey
        )
      )
    );

    $responseBody = wp_remote_retrieve_body($response);

    if(!empty($responseBody) && $responseBody != '[]'){

      $decodedResponse = json_decode($responseBody);

      if(!empty($decodedResponse->redirect)){
        return $decodedResponse->redirect;
      } else {
        return false;
      }
      
    } else {
      return false;
    }
  }


  /**
   * Prepares a URL with GET variables
   * 
   * @return string $url
   */
  private function prepareUrl($endpoint, $params){
    if(!empty($endpoint) && !empty($params) && count($params) > 0){
      $url = $endpoint;

      $paramsQuery = http_build_query($params);
      return $url . '?' . $paramsQuery;
    } else {
      return false;
    }
  }

  /**
   * Determins if code given is considered operational
   * 
   * @param int $code
   * 
   * @return boolean
   */
  public function isOperational($code){
    if(!empty($code)){
      $code = intval(sanitize_text_field( $code ));

      if($code < TETHERED::STATUS_BAD_REQUEST_BOUNDARY){
        return true;
      } else {
        return false;
      }
    }
    return false;
  }

}