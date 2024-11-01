<?php
if (!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')) {
	exit();
}
delete_option('uni_plg_banner_active');
delete_option('uni_plg_google_play_link');
delete_option('uni_plg_apple_store_link');
delete_option('uni_plg_windows_store_link');
delete_option('uni_plg_download_app_onmobile');
delete_option('uni_plg_applist_header');
delete_option('uni_plg_download_message');
delete_option('uni_plg_icon_url');
delete_option('uni_plg_push_notification_token');
delete_option('uni_plg_enable_auto_push');
delete_option('uni_plg_faldon_position');
delete_option('uni_plg_applist_active');
delete_option('uni_plg_insterstitial_active');
delete_option('uni_plg_insterstitial_frec_visit');

//Handles delete of database tables
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();
global $wpdb;
$table_name = $wpdb->prefix.'uni_push_notification_table';
$wpdb->query( "DROP TABLE IF EXISTS ".$table_name );
?>