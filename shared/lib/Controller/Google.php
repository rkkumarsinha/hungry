<?php

class Controller_Google extends AbstractController {
  public $client = null;
  public $google = null;
  public $config = null;
  public $user = null;
  public $hfrom = null;
  public $call_loginfunction = true;
  public $social_content = 0;

  function init(){
    parent::init();

    $this->config = $config = $this->app->getConfig('Google');
    $redirect_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?page=signin&hfrom=Google';
    $this->client = new Google_Client();
    $this->client->setApplicationName($config['application_name']);
    $this->client->setClientId($config['client_id']);
    $this->client->setClientSecret($config['client_secret']);
    $this->client->setRedirectUri($redirect_url);
    $this->client->addScope("email");
    $this->client->addScope("profile");

    if($this->call_loginfunction)
      $this->loginStatus();
  }

  function getLoginUrl(){
    $service = new Google_Service_Oauth2($this->client);
    return $authUrl = $this->client->createAuthUrl();
  }

  function loginStatus($accessToken_app=null){

    if(!$this->client)
      throw new \Exception("Google config not loaded");
    
    if($this->hfrom != "Google"){
      return false;
    }
    
    $service = new Google_Service_Oauth2($this->client);

    if($accessToken_app){
      $accessToken = $accessToken_app;
    }else{

      if(!isset($_GET['code']))
        return false;

      if (isset($_GET['code'])) {
        $this->client->authenticate($_GET['code']);
        $accessToken = $_SESSION['access_token'] = $access_token =  $this->client->getAccessToken();
      }else
        $accessToken = false;
    }

    //if we have access_token continue, or else get login URL for user
    if ($accessToken) {
      
      $this->client->setAccessToken($accessToken);
    } else {
      return false;
    }

    try{
      $user = $service->userinfo->get(); //get user info
    }catch(\Exception $e){
        return false;
    }
    // var_dump($user);
    // var_dump($user->id);

    // check if return userid is already exit or not
    // if exit then load user and return user
    // else create user and associate with access token
    $access_token = $this->add('Model_AccessToken');
    $access_token->addCondition('social_app','Google');
    $access_token->addCondition('return_userid',$user->id);
    
    $access_token->tryLoadAny();
    if($access_token->loaded()){
      $access_token['social_access_token'] = is_array($accessToken)?$accessToken['access_token']:$accessToken;
      $access_token['social_content'] = $accessToken;

      if($access_token['social_content'] === $access_token['social_access_token'] and $this->social_content)
        $access_token['social_content'] = $this->social_content;

      $access_token->save();
      return $this->add('Model_User')->load($access_token['user_id']);
    }

    $new_user = $this->add('Model_User');
    $new_user->addCondition('email',$user->email);
    $new_user->tryLoadAny();

    $new_user['name'] = $user->name;
    $new_user['gender'] = ucfirst($user->gender);
    // $new_user['social_access_token'] = $accessToken;
    // $new_user['social_content'] = $accessToken;
    $new_user['is_active'] = 1;
    $new_user['is_verified'] = 1;
    $new_user['type'] = 'user';
    $new_user['updated_at'] = date('Y-m-d H:i:s');
    $new_user['received_newsletter'] = 1;


    // temporary off due to get_header is not working on server or wrapper is not allowed
    // if($user->picture){

      // $image_url_array = explode("/", $user->picture);
      
      // $file = $this->add('filestore\Model_File',array('policy_add_new_type'=>true,'import_mode'=>'copy','import_source'=>$user->picture));
      // $file['filestore_volume_id'] = $file->getAvailableVolumeID();
      // $picture_name = $image_url_array[count($image_url_array)-1];

      // $file['original_filename'] = "google_".$user->name."_".$picture_name;
      // $file->save();

      // $new_user['image_id'] = $file->id;
    // } 
      
    // $new_user['profile_picture_url'] = $user->picture;
    $new_user->save();
    $this->user = $new_user;
    
    $social_data = $accessToken;
    if(!is_array($accessToken) and is_array(json_decode($accessToken,true)))
        $social_data = json_decode($accessToken,true);

    $access_token = $this->add('Model_AccessToken');
    $access_token['social_app'] = 'Google';
    $access_token['social_access_token'] = is_array($social_data)?$social_data['access_token']:$accessToken;
    $access_token['social_content'] = $social_data;

    if($access_token['social_content'] === $access_token['social_access_token'] and $this->social_content)
        $access_token['social_content'] = $this->social_content;

    $access_token['return_userid'] = $user->id;
    $access_token['profile_picture_url'] = $user->picture;
    $access_token['user_id'] = $new_user->id;
    $access_token->save();
    // var_dump($this->user->id);
    return $new_user;
  }
}