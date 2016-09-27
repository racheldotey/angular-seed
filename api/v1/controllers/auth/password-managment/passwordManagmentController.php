<?php namespace API;

require_once dirname(__FILE__) . '/passwordManagmentDB.php';

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

use \Respect\Validation\Validator as v;

class PasswordManagmentController extends RouteController {

    protected $slimContainer;
   
    public function __construct(\Interop\Container\ContainerInterface $slimContainer) {
        $this->slimContainer = $slimContainer;
    }
    
    public function requestResetEmail($request, $response, $args) {
        return $this->render($response, 200, 'requestResetEmail');
    }
    
    public function validateResetToken($request, $response, $args) {
        return $this->render($response, 200, 'validateResetToken');
    }
    
    public function changeUserPassword($request, $response, $args) {
        return $this->render($response, 200, 'changeUserPassword');
    }




    static function forgotpassword($app) {
        $result = AuthControllerNative::forgotpassword($app);
        if ($result['frgtauthenticated']) {
            return $app->render(200, $result);
        } else {
            return $app->render(400, $result);
        }
    }

    static function getforgotpasswordemail($app) {
        $result = AuthControllerNative::getforgotpasswordemail($app);
        if ($result['frgtauthenticatedemail']) {
            return $app->render(200, $result);
        } else {
            return $app->render(400, $result);
        }
    }

    static function resetpassword($app) {
        $result = AuthControllerNative::resetpassword($app);
        if ($result['resetpasswordauthenticated']) {
            return $app->render(200, $result);
        } else {
            return $app->render(400, $result);
        }
    }

    static function changeUserPasswords($app) {
        $post = $app->request->post();
        if ((!v::key('userId', v::stringType())->validate($post) && !v::key('email', v::stringType())->validate($post)) ||
                !v::key('current', v::stringType())->validate($post)) {
            return $app->render(400, array('msg' => "Password could not be changed. Check your parameters and try again."));
        } else if (!AuthControllerNative::validatePasswordRequirements($post, 'new')) {
            return $app->render(400, array('msg' => "Invalid Password. Check your parameters and try again."));
        }

        $savedPassword = (v::key('userId', v::stringType())->validate($post)) ? AuthData::selectUserPasswordById($post['userId']) :
                AuthData::selectUserPasswordByEmail($post['email']);

        if (!$savedPassword) {
            return $app->render(400, array('msg' => "User not found. Check your parameters and try again."));
        } else if (!password_verify($post['current'], $savedPassword)) {
            return $app->render(400, array('msg' => "Invalid user password. Unable to verify request."));
        } else {
            if (AuthData::updateUserPassword(array(':id' => $post['userId'], ':password' => password_hash($post['new'], PASSWORD_DEFAULT)))) {
                return $app->render(200, array('msg' => "Password successfully changed."));
            } else {
                return $app->render(400, array('msg' => "Password could not be changed. Try again later."));
            }
        }
    }


    
    ///// 
    ///// Login
    ///// 
    static function forgotpasswords($app){
        $post = $app->request->post();
        // Validate input parameters
        if(!v::key('email', v::email())->validate($post)) {
            return array('frgtauthenticated' => false, 'msg' => 'Forgot password failed. Check your parameters and try again.');
        }
        // Validate the user email and password
        $found = self::forgotpassword_validateFoundUser($post,$app);
        return $found;
    }
    
    static function resetpasswords($app){
        $post = $app->request->post();
        $found = self::getresetpassword_validateFoundUser($post);
        return $found;
    }
    
    private static function getresetpassword_validateFoundUser($post) {
        $result_array=array();
        $user = AuthData::selectUsertokenExpiry($post['email']);
        if(!$user) {
            // Validate existing user
            return array('resetpasswordauthenticated' => false, 'msg' => 'User failed. A user with that email id could not be found.');
        } else{
            $userDetail = AuthData::selectUserAndPasswordByEmail($post['email']);
            $strtotime1 =  strtotime($user->fortgotpassword_duration);
            $date = date("d M Y H:i:s");
            $strtotime =  strtotime($date);
            $diff = $strtotime - $strtotime1;
            $diff_in_hrs = round($diff/3600);
            if($diff_in_hrs > 1){
                $result_array=array('resetpasswordauthenticated' => false, 'msg' => 'Your reset password token is expired. 
                  Please try again to request forgot password.');
            }else{
                $userUpdate = AuthData::resetUserPassword(array(':email' => $post['email'], ':password' => password_hash($post['password'], PASSWORD_DEFAULT),':usertoken' => '', ':fortgotpassword_duration' => ''));
                if(!$userUpdate) {
                    $result_array=array('resetpasswordauthenticated' => false, 'msg' => 'Some error occured while updating password. 
                       please try again.');
                }
                else{
                    $result_array=array('resetpasswordauthenticated' => true,  'msg' => 'Your password has been reset successfully. Please login');
                }
            }
            if($result_array["resetpasswordauthenticated"]==false){
                $mail_variables=array(
                    "SMTP_SERVER_HOST"=>"Host",
                    "SMTP_SERVER_PORT"=>"Port",
                    "SMTP_SERVER_USERNAME"=>"Username",
                    "SMTP_SERVER_PASSWORD"=>"Password",
                    "SMTP_SMTP_DEBUG"=>'SMTPDebug',
                    "SMTP_DEBUGOUTPUT" => 'Debugoutput',
                    "SMTP_SECURE"=>"SMTPSecure",
                    "SMTP_AUTH"=>"SMTPAuth",
                    "PASSWORD_RESET_EMAIL_FROM"=>"From",
                    "PASSWORD_RESET_FAILED_EMAIL_SUBJECT"=>"Subject",
                    "PASSWORD_RESET_FAILED_EMAIL_BODY"=>"Body"
                    );
            }
            else{
                $mail_variables=array(
                    "SMTP_SERVER_HOST"=>"Host",
                    "SMTP_SERVER_PORT"=>"Port",
                    "SMTP_SERVER_USERNAME"=>"Username",
                    "SMTP_SERVER_PASSWORD"=>"Password",
                    "SMTP_SMTP_DEBUG"=>'SMTPDebug',
                    "SMTP_DEBUGOUTPUT" => 'Debugoutput',
                    "SMTP_SECURE"=>"SMTPSecure",
                    "SMTP_AUTH"=>"SMTPAuth",
                    "PASSWORD_RESET_EMAIL_FROM"=>"From",
                    "PASSWORD_RESET_SUCCESS_EMAIL_SUBJECT"=>"Subject",
                    "PASSWORD_RESET_SUCCESS_EMAIL_BODY"=>"Body"
                    );
            }
            $mail = new \PHPMailer;
            $mail->IsSMTP(); 
            foreach($mail_variables as $name=>$value){
                $config_data = ConfigData::getVariableByName($name);
                if($config_data && $config_data->disabled!=1){
                    $mail->{$value}=$config_data->value;
                }
            }
            $config_data = ConfigData::getVariableByName("PASSWORD_RESET_ROOT_URL");
            $root_url =($config_data->value=='')?ApiConfig::get('WEBSITE_URL'):$config_data->value;
            $mail->setFrom($mail->From, 'Triviajoint');
            $mail->addAddress($userDetail->email, $userDetail->nameFirst." ".$userDetail->nameLast);
            $mail->isHTML(true);
            $fields=array("!@FIRSTNAME@!","!@LASTNAME@!",'!@WEBSITEURL@!');
            $values=array($userDetail->nameFirst,$userDetail->nameLast,$root_url);
            $mail->Body=str_replace($fields, $values, $mail->Body);
            $mail->send();
            return $result_array; 
        }
    }
    
    static function getforgotpasswordemails($app){
        $post = $app->request->post();
        $found = self::getforgotpasswordemail_validateFoundUser($post);
        return $found;
    }
    
    private static function getforgotpasswordemail_validateFoundUser($post) {
        $user = AuthData::selectUserByUsertoken($post['usertoken']);
        if(!$user) {
            // Validate existing user
            return array('frgtauthenticatedemail' => false, 'msg' => 'Usertoken failed. A user with that usertoken could not be found.');
        } 
        return array('frgtauthenticatedemail' => true,  'user' => $user);
    }
    
    private static function forgotpassword_validateFoundUser($post,$app) {
        $user = AuthData::selectUserAndPasswordByEmail($post['email']);
        if(!$user) {
            // Validate existing user
            return array('frgtauthenticated' => false, 'msg' => 'Forgotpassword failed. A user with that email could not be found.');
        } 
        $usertoken = md5(date('Y-m-d H:i:s')*rand(9,99999));
        $fortgotpassword_duration = date('Y-m-d H:i:s');
        $userforgotupdate = AuthData::updateforgotpassworddata(array(':email' => $post['email'], ':usertoken' => $usertoken, ':fortgotpassword_duration' => $fortgotpassword_duration));
        $mail = new \PHPMailer;
        $mail->IsSMTP(); 
        $mail_variables=array(
            "SMTP_SERVER_HOST"=>"Host",
            "SMTP_SERVER_PORT"=>"Port",
            "SMTP_SERVER_USERNAME"=>"Username",
            "SMTP_SERVER_PASSWORD"=>"Password",
            "SMTP_SMTP_DEBUG"=>'SMTPDebug',
            "SMTP_DEBUGOUTPUT" => 'Debugoutput',
            "SMTP_SECURE"=>"SMTPSecure",
            "SMTP_AUTH"=>"SMTPAuth",
            "PASSWORD_RESET_EMAIL_FROM"=>"From",
            "PASSWORD_RESET_EMAIL_SUBJECT"=>"Subject",
            "PASSWORD_RESET_EMAIL_BODY"=>"Body"
            );
        foreach($mail_variables as $name=>$value){
            $config_data = ConfigData::getVariableByName($name);
            if($config_data && $config_data->disabled!=1){
                $mail->{$value}=$config_data->value;
            }
        }
        $config_data = ConfigData::getVariableByName("PASSWORD_RESET_ROOT_URL");
        $root_url =($config_data->value=='')?ApiConfig::get('WEBSITE_URL'):$config_data->value;
        $mail->setFrom($mail->From, 'Triviajoint');
        $mail->addAddress($user->email, $user->nameFirst." ".$user->nameLast);
        $mail->isHTML(true);
        $fields=array("!@FIRSTNAME@!","!@LASTNAME@!",'!@WEBSITEURL@!','!@LINKID@!');
        $values=array($user->nameFirst,$user->nameLast,$root_url,$usertoken);
        $mail->Body=str_replace($fields, $values, $mail->Body);
        if(!$mail->send()){
            return array('frgtauthenticated' => false,  'msg' => 'Email could not be sent for reset password. Please try again later.');
        } else {
            return array('frgtauthenticated' => true,  'msg' => 'Email has been sent to your email address for reset password.');
        }
    }
    
    
    
    
    ///// 
    ///// Password Managment
    ///// 
    
    static function updateUserPassword($app) {
        $post = $app->request->post();
        if(!v::key('userId', v::stringType())->validate($post) || 
            !v::key('current', v::stringType())->validate($post) || 
            !v::key('new', v::stringType())->validate($post)) 
        {
            return false;
        }
        return self::login_logoutCurrentAccount($app->request->post());
    }
}