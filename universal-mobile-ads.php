<?php
/*
Plugin name: Universal Network Web Mobile Ads
Plugin URI: https://universal.myappfree.it
Version: c
*/

// $cron_jobs = get_option( 'cron' );
// var_dump($cron_jobs);


//Register php autoloader for classes
spl_autoload_register( 'uni_plg_autoloader' );
function uni_plg_autoloader( $class_name ) {
  if ( false !== strpos( $class_name, 'uni_plg_' ) ) {
    $classes_dir = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
    $class_file = str_replace( 'uni_plg_', "", $class_name ) . '.class.php';
    require_once $classes_dir . $class_file;
  }
}

function uni_plg_do_cUrl()
{
    $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST"
      ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    if ($err) 
    {
        printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', 'cURL plugin is not working. Please check with your system admin.<br/>'.$err ); 
    } 
    else 
    {
        printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-success', 'cURL is working.'); 
    }

}

//Region push notificaiton
function uni_plg_sendPushNotificationCurl($ID, $content_id, $title, $smallcontent, $type, $topic = "global")
{

  $curl = curl_init();

  $to = '/topics/'.$topic;
  if ('/topics/developer' === get_option('uni_plg_push_debug'))
    $to = '/topics/developer';

  $fields = array(
    'to' => $to,
    'delay_while_idle' => false,
    'data' => array(
        'title' => $title,
        'body' => $smallcontent,
        'id' => $ID,
        'content_id' => $content_id,
        'type' => $type
    ),
    'notification' => array (
        'sound' => "default",
        'badge' => "1",
        "title" => $title,
        "body" => $smallcontent
    )
  );
                                            
  $data_string = json_encode($fields);                                                                                   
  $key = get_option('uni_plg_push_notification_token');                                                                     

  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $data_string,
    CURLOPT_HTTPHEADER => array(
      "authorization: key=".$key,
      "cache-control: no-cache",
      "content-type: application/json",
      'Content-Length: ' . strlen($data_string)
    ),
  ));

  $response = curl_exec($curl);
  $err = curl_error($curl);

  curl_close($curl);

  $headers[] = '';

  //DEBUG email
  if (!empty(get_option('uni_plg_push_email_debug', '')))
  {
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail( get_option('uni_plg_push_email_debug', ''), $title, $smallcontent, $headers );
  }


  if ($err) 
  {
    add_action( 'admin_notices', 'uni_send_push_error_notice' );
  } else {

  }

}

function uni_send_push_error_notice() 
{
	$class = 'notice notice-error';
	$message = __( 'Universal App: An error has occurred while sending the push!', 'universal-mobile-ads' );
	printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
}

function uni_send_push_success_notice() 
{
	$class = 'notice notice-success';
	$message = __( 'Universal App: Push scheduled for article on', 'universal-mobile-ads' );
	printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
}

function uni_plg_push_event_hook( $ID, $post ) 
{
    //get post title and description trimmed
    $title = $post->post_title;
    $permalink = get_permalink( $ID );
    $content = $post->post_content;
    $content = apply_filters('the_content', $content);
    $content = wp_strip_all_tags($content);
    $smallcontent = htmlspecialchars_decode(substr($content, 0, 100));

    //exec curl
    uni_plg_sendPushNotificationCurl($ID, $ID, $title, $smallcontent, "article");
}

// schedule_post_expiration_event runs when a Post is Published
function uni_plg_post_justpublished( $new_status, $old_status, $post ) 
{
    if ( $old_status != 'publish'  &&  $new_status == 'publish' && isset($post) && $post->post_type == "post" ) 
    {
        // A function to perform actions when a post status changes from any status to publish.
       
        //Check if we are in the allowed time window
        //TODO: implement

        if ('on' == get_option('uni_plg_enable_auto_push') && //is auto push enabled?
            !uni_push_already_scheduled($post->ID) && //does we already have scheduled an event?
            uni_push_categories_allowed($post->ID)) //is the category enabled?
        {
            uni_push_save_db($post);
            $delay = intval(get_option('uni_plg_push_delay', 30));

            uni_plg_push_event_hook($post->ID, $post);
            //wp_schedule_single_event( time() + $delay , 'uni_plg_post_published_notification', array($post->ID, $post) );
        }
    }
}

add_action( 'transition_post_status', 'uni_plg_post_justpublished', 10, 3 );
//add_action( 'uni_plg_post_published_notification', 'uni_plg_push_event_hook', 10, 2 );

function uni_push_categories_allowed($id)
{
    $categories_push_enabled = get_option( 'uni_plg_push_categories' );
    $post_categories = wp_get_post_categories( $id, 'ids' );

    if ( $categories_push_enabled !== false ) 
    {
      foreach ($categories_push_enabled as $key => $value) 
      {
         if ( in_array ( $key, $post_categories ) )
            return true;
      }
    }

    return false;
}

if (!function_exists('uni_push_already_scheduled')) {
  function uni_push_already_scheduled($content_id)
  {
      global $wpdb;
      //check for existing push scheduled in last 24 hours
      $push_scheduled = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix."uni_push_notification_table WHERE content_id LIKE '$content_id' AND push_time >= DATE_SUB(NOW(), INTERVAL 1 DAY)" );
      //if yes return false
      if ( null !== $push_scheduled ) {
        return true;
      } else {
        return false;
      }
  }
}

if (!function_exists('uni_push_save_db')) {
  function uni_push_save_db($post)
  {
      global $wpdb;
      
      $wpdb->insert( 
                    $wpdb->prefix."uni_push_notification_table", 
                    array( 
                      'content_id' => $post->ID, 
                      'title' => $post->post_title,
                      'notification_type' => 'article',
                      'topic' => '/topics/global',
                    ), 
                    array( 
                      '%s', 
                      '%s',
                      '%s',
                      '%s',
                    ) 
                  );
  }
}


//end region

if(!defined('ABSPATH')) {
  exit();
}

if (is_admin()) 
{
  add_action('admin_menu', 'Universal_menu');
	add_action('admin_init', 'Universal_admin_init');
}


abstract class uni_plg_UniversalMobileAds 
{
  const DISPLAY_INTERSTITIAL  = 'INTERSTITIAL';
  const DISPLAY_BANNER_TOP    = 'BANNER_TOP';
  const DISPLAY_BANNER_BOTTOM = 'BANNER_BOTTOM';
  const DISPLAY_NONE          = 'NONE';
  public static $s_DISPLAY = DISPLAY_NONE;
  public static $i_ad_to_show = 0;
  public static $st_content = Array();

  public static function setDisplay($s_display_new = DISPLAY_NONE) 
  {
    self::$s_DISPLAY = $s_display_new;
  }
  public static function getDisplay() 
  {
    return self::$s_DISPLAY;
  }
  public static function isInterstitial() 
  {
    if (self::$s_DISPLAY == self::DISPLAY_INTERSTITIAL) {
      return true;
    }
    return false;
  }

  function Universal_get_javascript_head() 
  {
    $s_html = "<script type='text/javascript'>\n";
    $s_html .= "//Universal banner for mobile\n";
    $s_html .= 'var uni_plg_google_play_link = "'.get_option('uni_plg_google_play_link').'";'."\n";
    $s_html .= 'var uni_plg_apple_store_link = "'.get_option('uni_plg_apple_store_link').'";'."\n";
    $s_html .= 'var uni_plg_windows_store_link = "'.get_option('uni_plg_windows_store_link').'";'."\n";
    $s_html .= 'var uni_plg_download_app_onmobile = "'.get_option('uni_plg_download_app_onmobile').'";'."\n";
    $s_html .= 'var uni_plg_download_message = "'.get_option('uni_plg_download_message').'";'."\n";
    $s_html .= 'var uni_plg_icon_url = "'.get_option('uni_plg_icon_url').'";'."\n";

    if (get_option('uni_plg_download_app_onmobile') == "on")
    {
        $s_html .= "if (uni_plg_device_isMobile()) {\n uni_showDownloadBlogApp(uni_plg_google_play_link, uni_plg_apple_store_link, uni_plg_windows_store_link, uni_plg_download_message, uni_plg_icon_url);\n};\n";
    }
    
    $s_html .= "</script>\n";
    return $s_html;
  }

  static function Universal_get_javascript_variables() 
  {
    $s_html = "<script type='text/javascript'>\n";
    $s_html .= "//Universal variables\n";
    $s_html .= 'var s_apikey = "'.get_option('Universal_token').'";'."\n";
    $s_html .= 'var u_applistheader = "'.get_option('uni_plg_applist_header').'";'."\n";
    $s_html .= 'var s_uni_source = "'.$_SERVER['SERVER_NAME'].'";'."\n";
    $s_html .= 'var s_lang = "'.substr(get_bloginfo('language'), 0, 2).'";'."\n";
    $s_html .= 'var s_uni_tool = "wordpress";'."\n";
	  $s_html .= 'var s_version = "v1";'."\n";
    if (uni_plg_UniversalMobileAds::isInterstitial()) {
      $s_html .= 'var b_interstitial = true;'."\n";
    } else {
      $s_html .= 'var b_interstitial = false;'."\n";
    }
    $s_html .= 'var s_target_div = "Universal_ad";'."\n";
    $s_html .= "if (uni_plg_device_isMobile()) {\n uni_plg_doAjaxRequest(s_version, s_apikey, s_lang, s_uni_source, s_uni_tool, s_target_div, b_interstitial, u_applistheader);\n};\n";
    $s_html .= "</script>\n";
    return $s_html;
  }
  
  public static function insertBannerDiv() 
  {
    echo '<div id="UniversalBanner" exstyle="display:none"></div>';
  }
}

function Universal_banner_insert_div() 
{
  uni_plg_UniversalMobileAds::insertBannerDiv();
}

function uni_plg_Universal_init()
{
  global $plugin_dir;
  load_plugin_textdomain('Universal', false, dirname(plugin_basename(__FILE__)).'/languages');

  wp_register_style( 'universal-app-admin-css', plugins_url( 'universal-mobile-app/css/admin.css' ), '', '0.20');

  define( 'UNI_PLG_PATH', plugin_dir_path( __FILE__ ) );

    if (('' != get_option('Universal_token')) ) 
    {
      $domain_name = $_SERVER['HTTP_HOST'];
      $bol_show_interstitial_ad = false;
      if ('on' == get_option('uni_plg_insterstitial_active') || 'on' == get_option('uni_plg_applist_active') || 'on' == get_option('uni_plg_banner_active')) 
      {
        if(isset($_COOKIE['Universal_interstitial']) && $_COOKIE['Universal_interstitial']) {
          $Universal_interstitial = intval($_COOKIE['Universal_interstitial']);
        } else {
          $Universal_interstitial = 0;
        }
        if (!$Universal_interstitial) {
          setcookie('Universal_interstitial', 1, time() + 86400);
          $bol_show_interstitial_ad = true;
        } elseif($Universal_interstitial <= intval(get_option('uni_plg_insterstitial_frec_visit'))) {
          $Universal_interstitial++;
          setcookie('Universal_interstitial', $Universal_interstitial, time() + 86400);
          $bol_show_interstitial_ad = true;
        }
        if ($bol_show_interstitial_ad) {
          add_action('wp_footer', 'Universal_add_interstitial');
          add_action('wp_enqueue_scripts', 'Universal_interstitial_name_scripts');
          uni_plg_UniversalMobileAds::setDisplay(uni_plg_UniversalMobileAds::DISPLAY_INTERSTITIAL);
        }
      }
      if (!$bol_show_interstitial_ad) //interstitial not shown
	    {
        // if( ('top' == get_option('uni_plg_faldon_position'))   ) {
          // add_action('wp_head', 'Universal_add_insert_head');
          // add_action('wp_enqueue_scripts', 'register_smartbanner_scripts');
          // add_action('wp_footer', 'Universal_banner_insert_div');
          // uni_plg_UniversalMobileAds::setDisplay(uni_plg_UniversalMobileAds::DISPLAY_BANNER_TOP);
        // }
        // if ('bottom' == get_option('uni_plg_faldon_position')) {
          // add_action('wp_head', 'Universal_add_insert_footer');
          // add_action('wp_enqueue_scripts', 'register_smartbanner_scripts');
          // add_action('wp_footer', 'Universal_banner_insert_div');
          // uni_plg_UniversalMobileAds::setDisplay(uni_plg_UniversalMobileAds::DISPLAY_BANNER_BOTTOM);
        // }
      }
    }
      
      if ('on' == get_option('uni_plg_download_app_onmobile') || 'on' == get_option('uni_plg_applist_active') || 'on' == get_option('uni_plg_banner_active')) //not showing interstitial - load other ads
      {
          add_action('wp_head', 'Universal_add_insert_head');
          add_action('wp_enqueue_scripts', 'register_smartbanner_scripts');
          add_action('wp_footer', 'Universal_banner_insert_div');
          add_action('wp_footer', 'Universal_add_insert_footer');
          //uni_plg_UniversalMobileAds::setDisplay(uni_plg_UniversalMobileAds::DISPLAY_BANNER_TOP);
      }

}

function register_smartbanner_scripts() 
{
  wp_register_script('UniversalScript', plugin_dir_url(__FILE__).'/js/universal_interstitial.js'."?d=".time(), false);
  wp_enqueue_script('UniversalScript');
  wp_register_style('Universalstyle', plugin_dir_url(__FILE__).'/css/universal_smartbanner.css'."?d=".time());
  wp_enqueue_style('Universalstyle');
}

function Universal_interstitial_name_scripts() 
{
  wp_register_script('UniversalScript', plugin_dir_url(__FILE__).'/js/universal_interstitial.js'."?d=".time(), false);
  wp_enqueue_script('UniversalScript');
  wp_register_style('UniversalInter', plugin_dir_url(__FILE__).'/css/universal_interstitial.css'."?d=".time());
  wp_enqueue_style('UniversalInter');
}

function Universal_admin_init() 
{
  add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'Universal_ads_add_action_links' );
  if ('' == get_option('uni_plg_push_notification_token')) {
    add_action('admin_notices', '_Universal_register');
  }
  add_action('admin_post_reg', 'prefix_admin_reg');
  add_settings_field('Universal-publisher-info-settings', 'Universal-publisher-info-settings', 'uni_plg_update_message_callback', 'Universal_post');
  register_setting('Universal-publisher-info-settings', 'Universal_token', 'uni_plg_sanitize_option_Universal_token');

  //Universal register database table
  uni_plg_createDatabase();
}

if (!function_exists('uni_plg_createDatabase')) {
function uni_plg_createDatabase()
{
    global $wpdb;
    $table_name = $wpdb->prefix.'uni_push_notification_table';
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        //table not in database. Create new table
        $charset_collate = $wpdb->get_charset_collate();
    
        $sql = "CREATE TABLE $table_name (
              id int NOT NULL AUTO_INCREMENT,
              content_id varchar(255) NOT NULL,
              title text NOT NULL,
              notification_type varchar(255) NOT NULL,
              topic varchar(255) NOT NULL,
              push_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              UNIQUE KEY id (id)
        ) $charset_collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
    else{
    }
}
}


function Universal_ads_add_action_links($links )
{
  $mylinks = array('<a href="' . admin_url( 'options-general.php?page=Universal_post' ) . '">' .   __("Settings", "Universal") .  '</a>', );
  return array_merge( $links, $mylinks );
}

function _Universal_register() 
{
  echo '<div id="message" class="error">';
  echo '  <p>'.__('Universal Mobile Ads plugin <a href="admin.php?page=Universal_post">needs your push notification token</a> to send push notification.', 'Universal').'</p>';
  echo '</div>';
}

if (!function_exists('Universal_menu')) 
{
  function Universal_menu() 
  {
    $page_title = __('Universal Mobile Ads', 'Universal');
    $menu_title = __('Universal App', 'Universal');
    $capability = 'manage_options';
    $menu_slug  = 'Universal_post';
    $function   = 'Universal_menu_admin';
    $icon_url   = 'dashicons-smartphone';

    //Add General settings page
    add_menu_page( $page_title,  $menu_title, $capability, $menu_slug, $function, $icon_url, 6  );

    //Add Social page
    add_submenu_page( $menu_slug,  "Social Connect", "Social", $capability, "universal_admin_social_page", "universal_admin_social_page" );

    //Add categories upload page
    add_submenu_page( $menu_slug,  "Categories Images", "Categories", $capability, "universal_app_categories", "Universal_categories_admin" );

    //Add debug page
    add_submenu_page( $menu_slug,  "Debug", "Debug", $capability, "universal_app_debug", "universal_debug_admin" );

    //Add Push notification page
    //add_submenu_page( $menu_slug,  "Push Notification", "Push Notification", $capability, "universal_app_push", "Universal_app_pushadmin" );

    //Add Web Monetization page

    add_action('admin_init', 'update_Universal_publisher_info');
  }
}

function satize_integer_delay($value) 
{
    $myint = intval( $value );
    if ($myint < 15)
      return 15;
    else return $myint;
}

if (!function_exists('update_Universal_publisher_info')) {
  function update_Universal_publisher_info() 
  {
    register_setting('Universal-publisher-info-settings', 'uni_plg_banner_active');
    register_setting('Universal-publisher-info-settings', 'uni_plg_google_play_link');
    register_setting('Universal-publisher-info-settings', 'uni_plg_apple_store_link');
    register_setting('Universal-publisher-info-settings', 'uni_plg_windows_store_link');
    register_setting('Universal-publisher-info-settings', 'uni_plg_download_app_onmobile');
    register_setting('Universal-publisher-info-settings', 'uni_plg_applist_header');
    register_setting('Universal-publisher-info-settings', 'uni_plg_download_message');
    register_setting('Universal-publisher-info-settings', 'uni_plg_icon_url');
    register_setting('Universal-publisher-info-settings', 'uni_plg_push_notification_token');
    register_setting('Universal-publisher-info-settings', 'uni_plg_push_email_debug');
    register_setting('Universal-publisher-info-settings', 'uni_plg_push_delay');

    add_filter('sanitize_option_uni_plg_push_delay', 'satize_integer_delay');
    add_filter('sanitize_option_uni_plg_push_email_debug','sanitize_email');

    register_setting('Universal-publisher-info-settings', 'uni_plg_enable_auto_push');
    register_setting('Universal-publisher-info-settings', 'uni_plg_push_debug');
    register_setting('Universal-publisher-info-settings', 'uni_plg_faldon_position');
	  register_setting('Universal-publisher-info-settings', 'uni_plg_applist_active');
    register_setting('Universal-publisher-info-settings', 'uni_plg_insterstitial_active');
    register_setting('Universal-publisher-info-settings', 'uni_plg_insterstitial_frec_visit',  'uni_plg_update_message_callback');
    register_setting('Universal-publisher-info-settings', 'uni_plg_facebook_push');


    register_setting('universal-facebook-settings', 'uni_plg_facebook_api');
    register_setting('universal-facebook-settings', 'uni_plg_facebook_appid');
    register_setting('universal-facebook-settings', 'uni_plg_facebook_secret');
    register_setting('universal-facebook-settings', 'uni_plg_facebook_pageid');

  }
}


if (!function_exists('universal_debug_admin')) 
{
  function universal_debug_admin() 
  {
	    wp_enqueue_style( 'universal-app-admin-css' );

      uni_plg_do_cUrl();

      //handle push console
      if (isset($_POST['submit'])) 
      {
          if (!is_super_admin())//only super-admins can send push
          {
              echo "Only super admins can send push notification.";
              return;
          }
               
            
          $id = $_POST['push_id'];
          $title = $_POST['push_title'];
          $body = $_POST['push_body'];
          $topic = $_POST['push_topic'];

          if (!empty($title) && !empty($body) && !empty($topic) && !empty($id))
          {
             uni_plg_sendPushNotificationCurl($id, $id, $title, $body, 'raw', $topic);
             printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-success', 'Notification sent.'); 
          }
      }


      if (file_exists (plugin_dir_path( __FILE__ ) . '/view/admin/debug.php'))
          include( plugin_dir_path( __FILE__ ) . '/view/admin/debug.php');
  }
}

if (!function_exists('Universal_categories_admin')) 
{
  function Universal_categories_admin() 
  {
	    wp_enqueue_style( 'universal-app-admin-css' );

      if (file_exists (plugin_dir_path( __FILE__ ) . '/view/admin/categories.php'))
          include( plugin_dir_path( __FILE__ ) . '/view/admin/categories.php');

  }
}

if (!function_exists('universal_admin_social_page')) 
{
  function universal_admin_social_page() 
  {
	    wp_enqueue_style( 'universal-app-admin-css' );

      if (file_exists (plugin_dir_path( __FILE__ ) . '/view/admin/social-settings.php'))
          include( plugin_dir_path( __FILE__ ) . '/view/admin/social-settings.php');
  }
}


if (!function_exists('Universal_menu_admin')) {
  function Universal_menu_admin() {
?>

  <style>
    .form-table {background: #fff; }
    .settings-container {background: #fff; }
    .settings-wrapper {padding: 0 25px;}
    form {padding: 0 30px;}
    h1 {padding: 15px 0; color: #fff; background-color: #19B5FE; text-align: center; margin-bottom: 0px;}
  </style>

  <form method="post" action="options.php">

<div class="settings-container">
<h1><?php echo __('Universal Mobile Ads', 'Universal'); ?></h1>
<div class="settings-wrapper">
<?php
    settings_fields('Universal-publisher-info-settings');
    do_settings_sections('Universal-publisher-info-settings');
?>
 
    <table class="form-table">
    <tr>
        <h2><?php echo __('Configure your Universal API Key', 'Universal'); ?></h2>
<?php
    if (get_option('Universal_token')) {
?>
    <p class="description"><?php echo __('Your API Key is OK. Now you can start setting up your Universal Mobile Ads plugin.', 'Universal'); ?></p>
<?php
    }
?>
    </tr>
      <tr>
        <th scope="row"><label for="Universal_token"><?php echo __('API Key', 'Universal'); ?></label></th>
        <td><input name="Universal_token" type="text" id="Universal_token" class="regular-text" value="<?php echo get_option('Universal_token'); ?>"></td>
      </tr>
    </table>
    <p class="description">
<?php 
    if ('' == get_option('Universal_token')) {
      echo __('We need your API Key. Sign in at Universal Network to <a href="http://universal.myappfree.it" target="_blank">retrieve your API Key</a> or sign up at Universal to <a href="http://universal.myappfree.it/account/register" target="_blank">create a new account</a>.', 'Universal');
    }
?>
    </p>

    <div style="top: 250px; border: none;" id="signin_message"></div>

    <table class="form-table">

    <tr>
          <h2><?php echo __('App List settings', 'Universal'); ?></h2>
      <?php
      echo __('The App list is a custom control for displaying several app campaigns. Include the tag <b>[universalapplist]</b> anywhere in your website and it will be replaced with mobile ads.', 'Universal');
      ?>
    </tr>

      <tr>
        <th scope="row"><label for="uni_plg_applist_header"><?php echo __('Applist Header', 'Universal'); ?></label></th>
        <td><input name="uni_plg_applist_header" type="text" id="uni_plg_applist_header" placeholder="We suggest those apps for your phone" value="<?php echo get_option('uni_plg_applist_header'); ?>" class="regular-text"></td>
      </tr>

      <tr>
        <th scope="row"><label for="uni_plg_applist_active"><?php echo __('Active', 'Universal'); ?></label></th>
        <td><input type="checkbox" name="uni_plg_applist_active"<?php if('on' == get_option('uni_plg_applist_active')) echo ' checked'; ?>></td>
      </tr>

    </table>
	
    <table class="form-table">

<!--<tr>
	<h2><?php echo __('Banner settings', 'Universal'); ?></h2>
      <label>
        <?php
          echo __('The banner is displayed on top, or on the bottom of the mobile device. Its size automatically adjusts up on the screen resolution of device of the visitor and the screen\'s orientation', 'Universal');
        ?>
      </label>
</tr>-->
<!--
      <tr>
        <th scope="row"><label for="uni_plg_banner_active"><?php echo __('Active', 'Universal'); ?></label></th>
        <td><input type="checkbox" name="uni_plg_banner_active"<?php if('on' == get_option('uni_plg_banner_active')) echo ' checked'; ?>></td>
      </tr>-->
      <!--<tr>
        <th scope="row"><label for="uni_plg_faldon_position"><?php echo __('Position', 'Universal'); ?></label></th>
        <td><select name="uni_plg_faldon_position" id="uni_plg_faldon_position">
          <option value="top"<?php if('top' == get_option('uni_plg_faldon_position')) echo ' selected'; ?>><?php echo __('Screen Top', 'Universal'); ?></option>
          <option value="bottom"<?php if('bottom' == get_option('uni_plg_faldon_position')) echo ' selected'; ?>><?php echo __('Screen Bottom', 'Universal'); ?></option>
        </select></td>
      </tr>-->
    </table>
</div>
</div>
    <!-- Enable "download app bloppy app" when users are from mobile -->


<div class="settings-container">
<h1><?php echo __('Mobile App Settings', 'Universal'); ?></h1>
<div class="settings-wrapper">

    <table class="form-table">
    <tbody><tr>

    <tr>
    <th scope="row"><label for="uni_plg_icon_url">Icon Url</label></th>
    <td><input name="uni_plg_icon_url" type="text" id="uni_plg_icon_url" value="<?php echo get_option('uni_plg_icon_url'); ?>" class="regular-text"><br/><img src="<?php echo get_option('uni_plg_icon_url'); ?>" width=50 height=50 /></td>
    </tr>

    <th scope="row"><label for="uni_plg_google_play_link">Google Play Link</label></th>
    <td><input name="uni_plg_google_play_link" type="text" id="uni_plg_google_play_link" value="<?php echo get_option('uni_plg_google_play_link'); ?>" class="regular-text">
    <p class="description" id="tagline-description">This is used to redirect users to download your app on Google Play.</p></td>
    </tr>
    <tr>
    <th scope="row"><label for="uni_plg_apple_store_link">Apple Store Link</label></th>
    <td><input name="uni_plg_apple_store_link" type="text" id="uni_plg_apple_store_link" value="<?php echo get_option('uni_plg_apple_store_link'); ?>" class="regular-text">
    <p class="description" id="tagline-description">This is used to redirect users to download your app on App Store.</p></td>
    </tr>
    <tr>
    <th scope="row"><label for="uni_plg_windows_store_link">Windows Store Link</label></th>
    <td><input name="uni_plg_windows_store_link" type="text" id="uni_plg_windows_store_link" value="<?php echo get_option('uni_plg_windows_store_link'); ?>" class="regular-text">
    <p class="description" id="tagline-description">This is used to redirect users to download your app on Windows Store.</p></td>
    </tr>

    <tr>
    <th scope="row"><label for="uni_plg_download_message">Download message</label></th>
    <td><input name="uni_plg_download_message" type="text" id="uni_plg_download_message"  value="<?php echo get_option('uni_plg_download_message'); ?>" class="regular-text">
    <p class="description" id="tagline-description">Message displayed near the popup banner.</p></td>
    </tr>

    <tr>
        <th scope="row"><label for="uni_plg_download_app_onmobile"><?php echo __('Enable download Banner on mobile', 'Universal'); ?></label></th>
        <td><input type="checkbox" name="uni_plg_download_app_onmobile"<?php if('on' == get_option('uni_plg_download_app_onmobile')) echo ' checked'; ?>></td>
    </tr>

    </tbody>
    </table>
  </div>
  </div>


<div class="settings-container">
<h1><?php echo __('Push notification Settings', 'Universal'); ?></h1>
<div class="settings-wrapper">
    
    <table class="form-table">
        <tbody>

        <tr>
	<h2><?php echo __('Enable push notification', 'Universal'); ?></h2>
      <label>
        <?php
          echo __('By enabling this option you will send a push notification every time an article is published.', 'Universal');
        ?>
      </label>
</tr>

        <tr>
          <th scope="row"><label for="uni_plg_enable_auto_push"><?php echo __('Active', 'Universal'); ?></label></th>
          <td><input type="checkbox" name="uni_plg_enable_auto_push"<?php if('on' == get_option('uni_plg_enable_auto_push')) echo ' checked'; ?>></td>
        </tr>

       <tr>
          <th scope="row"><label for="uni_plg_facebook_push"><?php echo __('Send Push on Facebok post', 'Universal'); ?></label></th>
          <td><input type="checkbox" name="uni_plg_facebook_push"<?php if('on' == get_option('uni_plg_facebook_push')) echo ' checked'; ?>></td>
        </tr>

        <tr>
          <th scope="row"><label for="uni_plg_push_debug"><?php echo __('Developer mode', 'Universal'); ?></label></th>
          <td>
              <select name="uni_plg_push_debug" id="uni_plg_push_debug">
                <option <?php if('/topics/global' == get_option('uni_plg_push_debug')) echo 'selected="selected"' ?> value="/topics/global">No (global topic)</option>
                <option <?php if('/topics/global' != get_option('uni_plg_push_debug')) echo 'selected="selected"' ?> value="/topics/developer">Yes (developer topic)</option>
            </select>          
          </td>
        </tr>

        <tr>
            <th scope="row"><label for="uni_plg_push_email_debug">Debug email</label></th>
            <td><input name="uni_plg_push_email_debug" type="text" id="uni_plg_push_email_debug" value="<?php echo get_option('uni_plg_push_email_debug'); ?>" class="regular-text">
            <p class="description" id="tagline-description">Send an email for debug purposes every time a push notification is sent. Keep it blank to disable this feature.</p></td>
        </tr>

        <tr>
          <th scope="row"><label for="uni_plg_push_delay"><?php echo __('Delay to send push (in seconds)', 'Universal'); ?></label></th>
          <td><input type="text" name="uni_plg_push_delay" value="<?php echo get_option('uni_plg_push_delay', 30) ?>" />
          <p class="description" id="tagline-description">Min suggested value is 15 seconds for caching reasons.</p></td>
        </tr>
 
        <tr>
            <th scope="row"><label for="uni_plg_push_notification_token">Push notification Token</label></th>
            <td><input name="uni_plg_push_notification_token" type="text" id="uni_plg_push_notification_token" value="<?php echo get_option('uni_plg_push_notification_token'); ?>" class="regular-text">
            <p class="description" id="tagline-description">This is your unique token to send push notification. Do not share it.</p></td>
        </tr>

        </tbody>
    </table>
</div>
</div>	


	<!--
    <h2><?php echo __('Interstitial settings', 'Universal'); ?></h2>
<?php
    echo __('The Interstitial banner shows when a visitor comes and must be closed to continue to the site.', 'Universal');
    settings_fields('Universal-publisher-info-settings');
    do_settings_sections('Universal-publisher-info-settings');
?>
    <table class="form-table">
      <tr>
        <th scope="row"><label for="uni_plg_insterstitial_active"><?php echo __('Active', 'Universal'); ?></label></th>
        <td><input type="checkbox" name="uni_plg_insterstitial_active"<?php if('on' == get_option('uni_plg_insterstitial_active')) echo ' checked'; ?>></td>
      </tr>
      <tr>
        <th scope="row"><label for="uni_plg_insterstitial_frec_visit"><?php echo __('Daily frequency', 'Universal') ?></label></th>
        <td><select name="uni_plg_insterstitial_frec_visit" id="uni_plg_insterstitial_frec_visit">
          <option value="1"<?php if('1' == get_option('uni_plg_insterstitial_frec_visit')) echo ' selected'; ?>>1</option>
          <option value="2"<?php if('2' == get_option('uni_plg_insterstitial_frec_visit')) echo ' selected'; ?>>2</option>
          <option value="3"<?php if('3' == get_option('uni_plg_insterstitial_frec_visit')) echo ' selected'; ?>>3</option>
          <option value="4"<?php if('4' == get_option('uni_plg_insterstitial_frec_visit')) echo ' selected'; ?>>4</option>
          <option value="5"<?php if('5' == get_option('uni_plg_insterstitial_frec_visit')) echo ' selected'; ?>>5</option>
        </select></td>
      </tr>
    </table>
    <p class="description"><?php echo __('Require use of user browser cookie. Daily frequency is the maximum impressions every 24 hours.', 'Universal'); ?></p>
	
	!-->
    <p class="submit"><?php submit_button(); ?></p>
  </form>
<?php
  }
}
function Universal_add_insert_footer() 
{
    $adUrls =   get_faldon_ad_url();
    uni_plg_get_js_mobilebanner();
}

function Universal_add_insert_head() 
{
   
}


add_shortcode( 'universalapplist', 'applist_shortcode' );

function applist_shortcode() 
{
  return '<div class="u_universal_network_applist"></div>';
}

function Universal_add_interstitial() {
  $adUrls = get_interstitial_ad_url();
  echo '<div id="Universal_fullscreen" style="display:none">'."\n";
  echo '    <div id="Universal_ad"></div>'."\n";
  echo '</div>';
}
function get_interstitial_ad_url() {
  // echo uni_plg_UniversalMobileAds::Universal_get_javascript_variables();
  // $st_return_array = Array();
  // return $st_return_array;
}
function uni_plg_get_js_mobilebanner() 
{
  echo uni_plg_UniversalMobileAds::Universal_get_javascript_head();
  return Array();
}
function get_faldon_ad_url() {
  echo uni_plg_UniversalMobileAds::Universal_get_javascript_variables();
  return Array();
}
function uni_plg_send_request($url, $params, $return_json = false) {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $r = curl_exec($ch);
  curl_close($ch);
  return $return_json ? json_encode(json_decode($r)) : json_decode($r);
}
function uni_plg_sanitize_option_Universal_token($value) {
    add_settings_error('Universal', 'Universal',  __('API Key saved', 'Universal'), 'updated');
    return sanitize_text_field($value);
}
function uni_plg_update_message_callback($value) {
  add_settings_error('Universal', 'Universal', __( 'The settings have been saved', 'Universal'), 'updated');
  return $value;
}
add_action('plugins_loaded', 'uni_plg_Universal_init');



// add_action( 'init', 'wpse9870_init_internal' );
// function wpse9870_init_internal()
// {
//     add_rewrite_rule( 'my-api.php$', 'index.php?wpse9870_api=1', 'top' );
// }

add_filter( 'query_vars', 'fb_api_query_vars' );
function fb_api_query_vars( $query_vars )
{
    $query_vars[] = 'fb_api';
    $query_vars[] = 'limit';
    $query_vars[] = 'post_id';
    $query_vars[] = "fb_comments";
    $query_vars[] = 'push';
    return $query_vars;
}

add_action( 'parse_request', 'facebook_api_parse_request' );
function facebook_api_parse_request( &$wp )
{
    if ( array_key_exists( 'fb_api', $wp->query_vars ) ) 
    {
         $vars = array();

         if ( array_key_exists( 'limit', $wp->query_vars )) 
            $vars[ 'limit' ] = $wp->query_vars["limit"];
         else
            $vars[ 'limit' ] = "30";

         if ( array_key_exists( 'post_id', $wp->query_vars )) 
            $vars[ 'post_id' ] = $wp->query_vars["post_id"];

         if ( array_key_exists( 'fb_comments', $wp->query_vars )) 
            $vars[ 'fb_comments' ] = $wp->query_vars["fb_comments"];

         if ( array_key_exists( 'push', $wp->query_vars )) 
            $vars[ 'push' ] = $wp->query_vars["push"];

        foreach ($vars AS $key => $val)
          $$key = $val;

        include 'fb-api.php';
        exit();
    }
    return;
}


?>