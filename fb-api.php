<?php 
//Php file to fetch data from facebook and print the json result

$app_id = get_option('uni_plg_facebook_appid', "");
$secret_key = get_option('uni_plg_facebook_secret', "");
$pageid = get_option('uni_plg_facebook_pageid', "");

if ($app_id == "" || $secret_key == "")
{
  echo json_encode(array("error" => array("message" => "Wrong AppID or SecretKey for Facebook settings.", "code" => "400"))); return; //return empty json array
}

if (get_option("uni_plg_facebook_api","") === "")
{
  echo json_encode(array("error" => array("message" => "Facebook API are not enabled by default.", "code" => "400"))); return; 
}

$fb = new uni_plg_FacebookWrapper();
// $token = $fb->uni_plg_facebook_getAccessToken($app_id,$secret_key);
$token = $app_id."|".$secret_key;

if (isset($post_id))
{
    if (isset($fb_comments))
    {
        $data = $fb->uni_plg_facebook_getComments($post_id, $token, $limit);
        echo json_encode($data);
    }
    else
    {
        $post = $fb->uni_plg_facebook_getPost($post_id, $token);
        echo json_encode($post);
    }
}
else if (isset($push))
{
    if (get_option("uni_plg_enable_auto_push","") === "")
    {
        echo json_encode(array("error" => array("message" => "Automatic push notification are not enabled.", "code" => "400"))); return; 
    }

    if (get_option("uni_plg_facebook_push","") === "")
    {
        echo json_encode(array("error" => array("message" => "Push notification of Facebook content are not enabled.", "code" => "400"))); return; 
    }

    //get last facebook post ID
    $post = $fb->uni_plg_facebook_lastPost($pageid, $token);
    $transient = get_transient( 'uni_plg_fb_last_id' );

    //save last id and refresh duration in case id was the same
    set_transient('uni_plg_fb_last_id', $post->id, 60*60*24);

    //if transient was empty save new transient with ID and do nothing (return) - this should occur only first time
    if ( false === $transient ) 
        return;

    if (true || $transient !== $post->id)
    { //new article found. if the ID is != from transient send push and save new transient
        uni_plg_sendPushNotificationCurl($post->getPushID(),$post->id,$post->getTitle(),$post->getBody(),"facebook","facebook");
        echo json_encode(array("success" => array("message" => "Notification sent on Facebook topic for post ".$post->id, "code" => "200"))); return; 
    }
    else
        echo json_encode(array("success" => array("message" => "No new Facebook post to send. Last checked is ".$post->id, "code" => "200"))); return; 
}
else
{
    $posts = $fb->uni_plg_facebook_getPosts($pageid, $token, $limit);
    echo json_encode($posts);
}




?>