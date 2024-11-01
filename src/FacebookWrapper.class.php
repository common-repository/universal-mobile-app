<?php

class uni_plg_FacebookWrapper 
{ 
  function uni_plg_facebook_lastPost($pageid, $token)
  {
      $data = $this->uni_plg_facebook_posts($pageid, $token, 1);
      $dataobj = json_decode($data, false);
      if (count($dataobj->data) > 0)
      {
          $post = new uni_plg_FacebookPost();
          $post->bind($dataobj->data[0]);
          return $post;
      }
  }

  function uni_plg_facebook_getPost($postid, $token)
  {
      $data = $this->uni_plg_facebook_posts_single($postid, $token);
      $dataobj = json_decode($data, false);
      if (isset($dataobj))
      {
          $post = new uni_plg_FacebookPost();
          $post->bind($dataobj);
          return $post;
      }
  }

  function uni_plg_facebook_getPosts($pageid, $token, $limit)
  {
      $data = $this->uni_plg_facebook_posts($pageid, $token, $limit);
      $dataobj = json_decode($data, false);
      if (count($dataobj->data) > 0)
      {
          $posts = array();
          foreach ($dataobj->data as $key => $value) {
              $post = new uni_plg_FacebookPost();
              $post->bind($value);
              if (!$post->isWordpressPost()) //does not add post that links to wordpress
                  $posts[] = $post;
          }
          return array("data" => $posts);
      }
  }

  function uni_plg_facebook_getComments($postid, $token, $limit)
  {
      $data = $this->uni_plg_facebook_comments($postid, $token, $limit);
      $dataobj = json_decode($data, false);
      if (count($dataobj->comments->data) > 0)
      {
          $posts = array();
          foreach ($dataobj->comments->data as $key => $value) {
              $post = new uni_plg_FacebookComment();
              $post->bind($value);
              $posts[] = $post;
          }
          return array("comments" => array("data" => $posts));
      }
  }

  function uni_plg_facebook_posts_single($postid, $token) 
  {
      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://graph.facebook.com/v2.8/".$postid."?access_token=".$token."&fields=full_picture,picture,caption,message,type,description,link,permalink_url,properties,name,created_time,shares,likes.limit(1).summary(true),comments.limit(1).summary(true)",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
          "cache-control: no-cache"
        ),
      ));

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        echo "cURL Error #:" . $err;
      } else {
        return $response;
      }
  }

  function uni_plg_facebook_posts($pageid, $token, $limit) 
  {
      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://graph.facebook.com/v2.8/".$pageid."/posts?access_token=".$token."&limit=".$limit."&fields=full_picture,picture,shares,caption,message,type,description,link,permalink_url,properties,name,created_time,likes.limit(1).summary(true),comments.limit(5).summary(true){like_count,from,message}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
          "cache-control: no-cache"
        ),
      ));

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        echo "cURL Error #:" . $err;
      } else {
        return $response;
      }
  }

  function uni_plg_facebook_comments($postid, $token, $limit)
  {
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://graph.facebook.com/v2.8/".$postid."?fields=comments.limit(".$limit."){like_count,message,created_time,from}&access_token=".$token,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
          "cache-control: no-cache"
        ),
      ));

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        echo "cURL Error #:" . $err;
      } else {
        return $response;
      }
  }

  function uni_plg_facebook_getAccessToken($app_id,$secret_key) 
  {
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://graph.facebook.com/oauth/access_token?%20client_id=".$app_id."&client_secret=".$secret_key."&%20grant_type=client_credentials",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
          "cache-control: no-cache"
        ),
      ));

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        return "";
      } else 
      {
          $response_data = json_decode($response,true);
          if (array_key_exists('access_token', $response_data)) 
          {
            //   set_transient('uni_plg_fb_access_token', $response_data['access_token'], 60*10);
              return $response_data['access_token'];
          }
          else
          {
              $token = explode("=", $response);
              return $token[1];
          }
      }
  }

}
?>