<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>

<form class="form-page-debug"  method="post" enctype="multipart/form-data">

<div class="settings-container">
  <h1><?php echo __('Debug Output', 'Universal'); ?></h1>
  <div class="settings-wrapper">
    <table>
    <thead>
        <tr>
     <th>Key</th>
     <th>Value</th>
        </tr>
    </thead>

    <?php
        global $wpdb;
        $results = $wpdb->get_results( "select * from $wpdb->options where option_name like 'uni_plg_%'", OBJECT );

        foreach ($results as $key => $value) : ?>
            <tr><td><b><?=$value->option_name?></b></td><td><?=$value->option_value?></td></tr>
        <?php endforeach; ?>

  </table>  
  </div>
</div>

<div class="settings-container">
  <h1><?php echo __('Scheduled notifications', 'Universal'); ?></h1>
  <div class="settings-wrapper">

    <?php

        $cron = _get_cron_array();
        $schedules = wp_get_schedules();
        $date_format = _x( 'M j, Y @ G:i', 'Publish box date format', 'Universal' );
        foreach ( $cron as $timestamp => $cronhooks ) {
          foreach ( (array) $cronhooks as $hook => $events ) {
            foreach ( (array) $events as $key => $event ) {
              $cron[ $timestamp ][ $hook ][ $key ][ 'date' ] = date_i18n( $date_format, $timestamp );
            }
          }
        }

        //delete cron if var is set
        if (isset($_POST['cron_delete_timestamp']))
        {
            foreach ($cron[$_POST['cron_delete_timestamp']] as $key => $hash)
            {
              foreach ($hash as $elem => $val)
                wp_clear_scheduled_hook( 'uni_plg_post_published_notification', $val["args"] );
            } 
            unset($cron[$_POST['cron_delete_timestamp']]);
        }

        $vars = array();
        $vars[ 'cron' ] = $cron;
        $vars[ 'schedules' ] = $schedules;

        foreach ($vars AS $key => $val)
          $$key = $val;
            
        if (file_exists (UNI_PLG_PATH . '/view/admin/cron-gui.php'))
          include( UNI_PLG_PATH . '/view/admin/cron-gui.php');
        else
          echo "<p>Rendering of admin template ".UNI_PLG_PATH."/view/admin/cron-gui.php failed</p>";

    ?>
    <br/><br/>
  </div>

  <div class="settings-container">
    <h1><?php echo __('Console Push Debug', 'Universal'); ?></h1>
    <div class="settings-wrapper">
        <table>
            <tr>
                <td><b>Push ID</b></td>
                <td><input style="width: 400px" type="text" name="push_id" placehold="1"/></td>
            </tr>
            <tr>
                <td><b>Push title</b></td>
                <td><input style="width: 400px" type="text" name="push_title" placehold="title"/></td>
            </tr>
            <tr>
                <td><b>Push body</b></td>
                <td><textarea style="width: 400px" rows="3" cols=3 name="push_body"></textarea></td>
            </tr>
            <tr>
                <td><b>Topic</b></td>
                <td><input style="width: 400px" rows="3" cols=3 name="push_topic"/></td>
            </tr>
        </table>
        <?php submit_button('Send Push') ?>

    </div>
  </div>


</div>
</form>
