<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>


<div class="wrap">


<form  method="post" action="options.php" enctype="multipart/form-data">
    <?php   
        settings_fields('universal-facebook-settings');
        do_settings_sections('universal-facebook-settings');
    ?>

<div class="settings-section">
<h1><?php echo __('Facebook Settings', 'Universal'); ?></h1>

<p>Enable the view of your Facebook post in your mobile app. You can turn on or off push notification and exclude Facebook post that links to your website.</p>
            
            <table class="form-table">

                <tr>
                    <th scope="row"><label for="uni_plg_facebook_api"><?php echo __('Enable Facebook', 'Universal'); ?></label></td>
                    <td><input type="checkbox" name="uni_plg_facebook_api"<?php if('on' == get_option('uni_plg_facebook_api')) echo ' checked'; ?>></td>
                </tr>

                <tr>
                    <td scope="row"><label for="uni_plg_facebook_appid">Facebook APP ID</label></td>
                    <td><input name="uni_plg_facebook_appid" type="text" id="uni_plg_facebook_appid" value="<?php echo get_option('uni_plg_facebook_appid'); ?>" class="regular-text">
                    <p class="description">This is your Facebook APP ID, you can <a target="_blank" href="https://developers.facebook.com/apps/">find it here</a>.</p></td>
                </tr>

                <tr>
                    <td scope="row"><label for="uni_plg_facebook_secret">Facebook SECRET KEY</label></td>
                    <td><input name="uni_plg_facebook_secret" type="text" id="uni_plg_facebook_secret" value="<?php echo get_option('uni_plg_facebook_secret'); ?>" class="regular-text">
                    <p class="description">This is your Facebook SECRET KEY, you can <a target="_blank" href="https://developers.facebook.com/apps/">find it here</a>.</p></td>
                </tr>

                <tr>
                    <td scope="row"><label for="uni_plg_facebook_pageid">Facebook Page ID</label></td>
                    <td><input name="uni_plg_facebook_pageid" type="text" id="uni_plg_facebook_pageid" value="<?php echo get_option('uni_plg_facebook_pageid'); ?>" class="regular-text">
                    <p class="description">This is your Facebook Page ID, you can <a target="_blank" href="http://findmyfbid.com/">find it here</a>.</p></td>
                </tr>
                
                </tbody>
            </table>
</div>

<?php submit_button(); ?>
</form>


</div>
<div class="clear"></div>