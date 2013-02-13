<?php
namespace Mwfacebookapp;
use CloudControl\API;
use CloudControl\TokenRequiredError;
use CloudControl\UnauthorizedError;


class CloudControlController {
    
    /**
     * @var \Api
     */
    private $api;   
    private static $creds = array();
    
    public function __construct() {
        $this->api = new API();
    }
    
    public function initApi($app){
        $token = $app['session']->get('token');
        if ($token){
            $this->api->setToken($token);
        }
    }
    
    public function login($app, $request){
        $form = $app['form.factory']->createBuilder('form')
        ->add('email', 'text', array('label' => "Email"))
        ->add('password', 'password', array('label' => "Password"))
        ->getForm();
        if ('POST' == $request->getMethod()) {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $this->api->auth(
                    $data['email'],
                    $data['password']
                );
                $app['session']->set('token', $this->api->getToken());
                return $app->redirect('/');
            }
        }
        return $app['twig']->render('login.twig', array(
            'form' => $form->createView()));
    }
    
    public function logout($app){
        $app['session']->set('token', '');
        $this->api = new API();
        return $app->redirect('/login');
    }
    
    public function appList($app){
        try {
            $applicationList = $this->api->application_getList();
            return $app['twig']->render('applist.twig', array(
                'applicationList' => $applicationList));
        } catch (UnauthorizedError $e) {
            return $app->redirect('/login');
        } catch (TokenRequiredError $e) {
            return $app->redirect('/login');
        }
    }
    
    public function appDetails($app, $applicationName){
        try {
            $application = $this->api->application_getDetails($applicationName);
            return $app['twig']->render('appDetails.twig', array(
                'application' => $application, 'applicationName' => $applicationName));
        } catch (UnauthorizedError $e) {
            return $app->redirect('/login');
        } catch (TokenRequiredError $e) {
            return $app->redirect('/login');
        }
    }
    
    public function deploymentDetails($app, $applicationName, $deploymentName){
        try {
            $deployment = $this->api->deployment_getDetails($applicationName, $deploymentName);
            return $app['twig']->render('deploymentDetails.twig', array(
                'deployment' => $deployment));
        } catch (UnauthorizedError $e) {
            return $app->redirect('/login');
        } catch (TokenRequiredError $e) {
            return $app->redirect('/login');
        }
    }
    
    public static function getCredentials($addonName){
        if (empty(self::$creds) && !empty($_SERVER['HTTP_HOST']) && isset($_ENV['CRED_FILE'])) {
            // read the credentials file
            $string = file_get_contents($_ENV['CRED_FILE'], false);
            if ($string == false) {
                throw new \Exception('Could not read credentials file');
            }
            // the file contains a JSON string, decode it and return an associative array
            $creds = json_decode($string, true);

            $error = json_last_error();
            if ($error != JSON_ERROR_NONE){
                $json_errors = array(
                    JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
                    JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
                    JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
                    JSON_ERROR_SYNTAX => 'Syntax error',
                    JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
                );
                throw new \Exception(sprintf('A json error occured while reading the credentials file: %s', $json_errors[$error]));
            }
            self::$creds = $creds;
        }
        if (!array_key_exists($addonName, self::$creds)){
            throw new \Exception(sprintf('No credentials found for addon %s. Please make sure you have added the config addon.', $addonName));
        }
        return self::$creds[$addonName];
    }
}
