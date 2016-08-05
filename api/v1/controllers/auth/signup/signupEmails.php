<?php namespace API;

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

use \Respect\Validation\Validator as v;

class SignupEmails extends RouteEmailsController {

    public function sendWebsiteSignupEmailConfirmation($resetToken, $userEmail, $userFirstName = '', $userLastName = '') {
        $emailParams = array_merge($this->commonEmailReplaceVariables, array(
            'EMAIL' => $userEmail,
            'FIRST_NAME' => $userFirstName,
            'LAST_NAME' => $userLastName,
            'RESET_PASSWORD_URL' => $this->commonEmailReplaceVariables['WEBSITE_URL'] . '/signup/confirm-email/' . $resetToken
        ));
         
        return $this->sendEmailFromTemplate('NEW_USER_EMAIL_CONFIRMATION_LINK', $userEmail, "$userFirstName $userLastName", $emailParams);
    }
    
    public function sendWebsiteSignupSuccess($userEmail, $userFirstName = '', $userLastName = '') {
        $emailParams = array_merge($this->commonEmailReplaceVariables, array(
            'EMAIL' => $userEmail,
            'FIRST_NAME' => $userFirstName,
            'LAST_NAME' => $userLastName
        ));
         
        return $this->sendEmailFromTemplate('NEW_USER_CONFIRMED', $userEmail, "$userFirstName $userLastName", $emailParams);
    }
    
}