<?php

class Controller_Facebook extends AbstractController {
  public $fb=null;
  public $config=null;
  public $user = null;
  public $hfrom = null;
  public $call_loginfunction = true;
  public $isWebsiteCheck = false;
  function init(){
    parent::init();

    $this->config = $config = $this->app->getConfig('Facebook');
    $this->fb = $fb = new \Facebook\Facebook([
              'app_id' => $config['app_id'],
              'app_secret' => $config['app_secret'],
              'default_graph_version' => 'v2.6',
              'default_access_token' => $config['app_id'].'|'.$config['app_secret']
        ]);

    if($this->call_loginfunction)
      $this->loginStatus();
  }

  function loginStatus($accessToken_app=null){
    
    if(!$this->fb)
      throw new \Exception("Facebook config not loaded");
    
    if($this->hfrom != "Facebook"){
      return false;
    }
    
    // cross broser state mismatch error solved
    if($this->isWebsiteCheck)
      $_SESSION["FBRLH_state"] = $_SESSION["FBRLH_persist"];

    if($accessToken_app){
        $accessToken = $accessToken_app;
    }else{
        $helper = $this->fb->getRedirectLoginHelper();
        
        try {
          $accessToken = $helper->getAccessToken();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
          // When Graph returns an error
          echo 'Graph returned an error: ' . $e->getMessage();
          exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
          // When validation fails or other local issues
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
          exit;
        }
    }


    if (isset($accessToken)) {
      // Logged in!
      $_SESSION['facebook_access_token'] = (string) $accessToken;
        
        $oAuth2Client = $this->fb->getOAuth2Client();
        // Exchanges a short-lived access token for a long-lived one
        $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
        
        $this->fb->setDefaultAccessToken($longLivedAccessToken);
        try {
          $response = $this->fb->get('/me');
          $userNode = $response->getGraphUser();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
          // When Graph returns an error
          echo 'Graph returned an error: ' . $e->getMessage();
          exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
          // When validation fails or other local issues
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
          exit;
        }

        $url = "http://graph.facebook.com/".$userNode->getId()."/picture?type=large";
        // $headers = get_headers($url, 1);
        
        // if($accessToken_app){
        //   return [
        //           "longLivedAccessToken"=> $longLivedAccessToken,
        //           "userNode"
        //         ];
        // }else{
          $new_user = $this->updateUser($longLivedAccessToken,$userNode,isset($headers['Location'])?$headers['Location']:false,$url);
          $this->user = $new_user;
        // }

        return $new_user;
      }

      return false;
  }

  function updateUser($longLivedAccessToken,$userNode,$profile_picture=false,$profile_picture_url=null){
      // check if return userid is already exit or not
      // if exit then load user and return user
      // else create user and associate with access token
      $access_token = $this->add('Model_AccessToken');
      $access_token->addCondition('social_app','Facebook');
      $access_token->addCondition('return_userid',$userNode->getId());
      $access_token->tryLoadAny();
      if($access_token->loaded()){
        $access_token['social_access_token'] = (string)$longLivedAccessToken;
        $access_token->save();

        return $this->add('Model_User')->load($access_token['user_id']);
      }

      $new_user = $this->add('Model_User');
      $new_user['email'] = $userNode->getEmail();
      $new_user['gender'] = $userNode->getGender();
      $new_user['dob'] = $userNode->getBirthday();
      $new_user['name'] = $userNode->getName();
      $new_user['age_range'] = $userNode->getField('age_range');
      $new_user['is_active'] = 1;
      $new_user['is_verified'] = 1;
      $new_user['type'] = 'user';
      $new_user['updated_at'] = date('Y-m-d H:i:s');
      $new_user['received_newsletter'] = 1;
      

      if($profile_picture){
        $file = $this->add('filestore\Model_File',array('policy_add_new_type'=>true,'import_mode'=>'copy','import_source'=>$profile_picture));
        $file['filestore_volume_id'] = $file->getAvailableVolumeID();
        $file['original_filename'] = "Facebook_".$userNode->getName();
        $file->save();
        $new_user['image_id'] = $file->id;
      }
      
      $new_user->save();

      $access_token = $this->add('Model_AccessToken');
      $access_token['social_app'] = 'Facebook';
      $access_token['social_access_token'] = (string)$longLivedAccessToken;
      $access_token['return_userid'] = $userNode->getId();
      $access_token['profile_picture_url'] = $profile_picture_url;
      $access_token['user_id'] = $new_user->id;
      $access_token->save();

      return $new_user;
  }

  function getLoginUrl(){
      if(!$this->fb)
        throw new \Exception("Facebook Config Errro");
      
      $helper = $this->fb->getRedirectLoginHelper();
      $permissions = $this->config['scope'];
      $redirect_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?page=signin&hfrom=Facebook';
      $loginUrl = $helper->getLoginUrl($redirect_url, $permissions);
      return $loginUrl;
  }
}