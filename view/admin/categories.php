<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>


<div class="wrap">

 <!-- Funzione per gestire le categorie! -->
  <?php
  //Code to create folder for upload if doesn't exists
  $upload_dir = wp_upload_dir(); 
  $plugin_upload_dir = $upload_dir['basedir'] . '/universal-app-content';
  if(!file_exists($plugin_upload_dir)) wp_mkdir_p($plugin_upload_dir);

  //handle file upload
  foreach ($_FILES as $key => $value) 
  {
      if (file_exists($value['tmp_name']) || is_uploaded_file($value['tmp_name']))
      {
          //check if the file is .png format
          $allowed =  array('png');
          $filename = $value['name'];
          $ext = pathinfo($filename, PATHINFO_EXTENSION);
          if(!in_array($ext,$allowed)) 
          {
              add_settings_error(
                  'uni_plugin_error_upload',
                  esc_attr( 'settings_updated' ),
                  'Uploaded file is not a valid image or it is not png file.',
                  'error'
              );
          }
          else
          {
              //upload in the directory
              $source = $value['tmp_name'];
              $destination = trailingslashit( $plugin_upload_dir ) .$key.'.png';
              move_uploaded_file( $source, $destination );
          }
      }
  }


//handle update categories
$option_name = 'uni_plg_push_categories';
$categories_push_enabled = get_option( $option_name );
$new_value = array();

if (isset($_POST['submit']))
{
    if (isset($_POST['push_enable']))
        foreach ($_POST['push_enable'] as $key => $value)
        {
            $new_value[$key] = "on";
        }

    // The option already exists, so we just update it.
    update_option( $option_name, $new_value );
}
else if ( $categories_push_enabled !== false ) 
{
    //create the option if doesn't exist as empty array
    $deprecated = null;
    $autoload = 'no';
    add_option( $option_name, array(), $deprecated, $autoload );
}

//refresh data
$categories_push_enabled = get_option( $option_name );

?>
        <!-- Form to handle the upload - The enctype value here is very important -->
        <form  method="post" enctype="multipart/form-data">

                <div class="settings-container">

<div class="settings-wrapper">
    
    <h1><?php echo __('Categories Icons', 'Universal'); ?></h1>

                <p>Upload icons that will be visible on the mobile app foreach category. If you want to hide a specific category from app just add <b>#nomobile#</b> anywhere on the description.<br/> Click on <span style="color: green; font-weight: bold;">Visible on app</span> or on <span style="color: red; font-weight: bold;">Hidden on app</span> to edit the specified category description.</p>

     <table class="form-table">
      <thead>
          <tr>
            <th>Category Name</th>
            <th>Icon</th>
            <th>Upload</th>
            <th>Push Notification</th>
            <th>Visible in menu</th>
          </tr>
      </thead>

        <tbody>



          <?php
            $cat = get_categories();
            foreach ($cat as $value) : 
          ?>

            <tr>
              <td scope="row">
                <label for="cat<?=$value->cat_ID;?>"><?=$value->name;?></label>
              </td>
              <td>
                <?php if (file_exists(trailingslashit( $plugin_upload_dir ) . 'cat' . $value->cat_ID . '.png')) : ?>
                  <img width="30" height="30" src="/wp-content/uploads/universal-app-content/cat<?=$value->cat_ID;?>.png" />
                <?php endif; ?>
              </td>
              <td><input type='file' id='cat<?=$value->cat_ID;?>' name='cat<?=$value->cat_ID;?>'></input></td>
              <td>
              <input type='checkbox'
                <?php if (array_key_exists($value->cat_ID,$categories_push_enabled))
                    echo 'checked';
                ?> id='push_category-<?=$value->cat_ID;?>' name='push_enable[<?=$value->cat_ID;?>]'></input></td>
              <td>
                <a href="<?php echo admin_url( 'term.php?taxonomy=category&tag_ID='.$value->cat_ID.'&post_type=post&wp_http_referer=%2Fwp-admin%2Fedit-tags.php%3Ftaxonomy%3Dcategory' ) ?>">
                <?php 
                  if (strpos($value->description, "#nomobile#") === false) 
                    echo '<span style="color: green; font-weight: bold;">Visible on app</span>';
                  else
                    echo '<span style="color: red; font-weight: bold;">Hidden on app</span>';
                ?>
                </a>
                <input type="checkbox" style="display: none;" name="cat_enable[cat<?=$value->cat_ID;?>]" <?php if (strpos($value->description, "#nomobile#") === false) echo ' checked'; ?>>
              </td>
            </tr>

            <?php endforeach; ?>


        </tbody>
    </table>
</div>
</div>	
        <?php submit_button('Update') ?>

        </form>


</div>
<div class="clear"></div>