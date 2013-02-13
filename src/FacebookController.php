<?php
namespace Mwfacebookapp;
require_once __DIR__ . '/../vendor/facebook/php-sdk/src/facebook.php';
require_once __DIR__.'/CloudControlController.php';

use Symfony\Component\HttpFoundation\Response;
use Mwfacebookapp\CloudControlController;


class FacebookController {
    /**
     * @var array
     */
    private $facebookConfig;
    
    public function __construct(){
        $creds = CloudControlController::getCredentials('CONFIG');
        $this->facebookConfig = array(
            'appUrl' => "http://apps.facebook.com/mwfacebookapp",
            'cookies' => 'true',
            'appId' => $creds['CONFIG_VARS']['APP_ID'],
            'secret' => $creds['CONFIG_VARS']['SECRET_KEY']
        );
    }
    
    public function loggedIn(){
        $facebook = new \Facebook($this->facebookConfig);
        $user = $facebook->getUser();
        if ($user) {
            try {
                $facebook->api('/me');
                return true;
            } catch (\FacebookApiException $e) {
                // 
            }
        }
        return false;
    }
    
    public function login(){
        $facebook = new \Facebook($this->facebookConfig);
        $loginUrl = $facebook->getLoginUrl(array(
            'redirect_uri' => $this->facebookConfig['appUrl']
        ));
        $content = sprintf("<script type='text/javascript'>top.location.href = '%s';</script>", $loginUrl);
        return new Response($content, 200, array('content-type' => 'text/html'));
    }
}