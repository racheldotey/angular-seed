<?php namespace API;

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

use \Respect\Validation\Validator as v;

class PasswordManagmentEmails extends RouteEmailsController {
    
    public function sendForgotPasswordLink($resetToken, $userEmail, $userFirstName = '', $userLastName = '') {
        $emailParams = array_merge($this->commonEmailReplaceVariables, array(
            'EMAIL' => $userEmail,
            'FIRST_NAME' => $userFirstName,
            'LAST_NAME' => $userLastName,
            'RESET_PASSWORD_URL' => $websiteUrl . '/' . $resetToken
        ));
         
        return $this->sendEmailFromTemplate('FORGOT_PASSWORD_TOKEN_EMAIL', $userEmail, "$userFirstName $userLastName", $emailParams);
    }
    
    public function sendPasswordResetSuccess($userEmail, $userFirstName = '', $userLastName = '') {
        $emailParams = array_merge($this->commonEmailReplaceVariables, array(
            'EMAIL' => $userEmail,
            'FIRST_NAME' => $userFirstName,
            'LAST_NAME' => $userLastName
        ));
         
        return $this->sendEmailFromTemplate('USER_PASSWORD_CHANGED', $userEmail, "$userFirstName $userLastName", $emailParams);
    }

}