<?php namespace API;

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

use \Respect\Validation\Validator as v;

abstract class RouteEmailsController {
    
    /*
     * Database Connection Instance
     */
    private $DBConn;
    
    /*
     * Database Table Prefix String
     */
    private $dbTablePrefix;

    /*
     * System Email Helper Instance
     */
    protected $EmailService;
    
    /*
     * System Variables Helper Instance
     */
    protected $SystemVariables;
    
    /*
     * Variables that are available for all email templates.
     */
    protected $commonEmailReplaceVariables;
    
    /*
     * System Logger Instance
     */
    protected $ApiLogging;
    
    /*
     * Email Log File Name String
     */
    private $logFileName = 'route_emails';

    /**
     * System Variables Handler to manage the use of variables stored in the database
     * to be used throught the API.
     * 
     * $EmailService = new EmailService( new \API\ApiDBConn(), new \API\SystemVariables(), new \API\ApiLogging() );
     *
     * @param  \API\ApiDBConn       $dbConn  Database Connection Helper Method
     * @param  \API\SystemVariables $SystemVariables  Database System Variables Helper Method
     * @param  \API\ApiLogging      $ApiLogging  System Logging Helper Method
     */
    public function __construct(\API\ApiDBConn $ApiDBConn, \API\SystemVariables $SystemVariables, \API\ApiLogging $ApiLogging) {
        $this->DBConn = $ApiDBConn;
        $this->dbTablePrefix = $ApiDBConn->prefix();
        
        $this->SystemVariables = $SystemVariables;
        $this->ApiLogging = $ApiLogging;

        $this->EmailService = new EmailService($SystemVariables, $ApiLogging);

        $this->commonEmailReplaceVariables = array(
            'WEBSITE_TITLE' => $SystemVariables->get('WEBSITE_TITLE'),
            'WEBSITE_URL' => $SystemVariables->get('WEBSITE_URL'),
            'LOGIN_URL' => $SystemVariables->get('WEBSITE_URL') . '/login'
        );
    }
    
    public function sendEmailFromTemplate($templateId, $recipientEmail, $recipientName = '', $templateParams = []) {
        if (!v::email()->validate($recipientEmail)) {
            return array('error' => true, 'msg' => "The following email address is invalid: '{$recipientEmail}'.");
        }

        // Retrieve template
        $emailTemplate = $this->selectEmailTemplate($templateId);
        if (!$emailTemplate) {
            return array('error' => true, 'msg' => "Error generating email <{$templateId}>");
        }
        
        $email = array(
            'recipientName' => $recipientName,
            'recipientEmail' => $recipientEmail,
            'subject' => $this->replaceTemplateVariables($emailTemplate->subject, $templateParams),
            'htmlBody' => $this->replaceTemplateVariables($emailTemplate->bodyHtml, $templateParams),
            'plainBody' => $this->replaceTemplateVariables($emailTemplate->bodyPlain, $templateParams)
        );
        
        // If a from email is set
        if($emailTemplate->fromEmail) {
            $email['fromEmail'] = $emailTemplate->fromEmail;
            
            if($emailTemplate->fromName) {
                $email['fromName'] = $emailTemplate->fromName;
            }
        }
        
        // If a reply to email is set
        if($emailTemplate->replyEmail) {
            $email['replyEmail'] = $emailTemplate->replyEmail;
            
            if($emailTemplate->replyName) {
                $email['replyName'] = $emailTemplate->replyName;
            }
        }

        return $this->EmailService->sendEmail($email);
    }

    private function replaceTemplateVariables($templateText, $variableArray) {
        // Template substitution is for parms named @EMAIL@, @FIRST_NAME@, etc     
        foreach($variableArray AS $key => $value) {
            $templateText = str_replace("@{$key}@", $value, $templateText);
        }
        return $templateText;
    }
    
    public function selectEmailTemplate($templateId) {
        $template = $this->DBConn->selectOne("SELECT id, identifier, from_email AS fromEmail, from_name AS fromName, "
                . "reply_email AS replyEmail, reply_name AS replyName, subject, body_html AS bodyHtml, body_plain AS bodyPlain "
                . "FROM {$this->dbTablePrefix}email_templates WHERE identifier = :identifier LIMIT 1;", 
                array(':identifier' => $templateId));
        if (!$template) {
            $this->ApiLogging->write("ERROR RETRIEVING EMAIL TEMPLATE, templateId <{$templateId}>", LOG_ERR, $this->logFileName);
            return false;
        }
        return $template;
    }
}