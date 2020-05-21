<?php

namespace controllers;

use components\core\Utility;
use components\email\EmailService;
use models\User;

/**
 * Class PasswordResetController
 * Verifies user email and sends link to reset password
 * @package controllers
 */
class PasswordResetController extends Controller
{
    public function render($params)
    {
        if (isset($_POST['email'])) {
            $email = filter_var(htmlspecialchars($_POST['email']), FILTER_SANITIZE_EMAIL);

            $this->sendResetPasswordEmail($email);
            $this->setSuccess("The verification link has been successfully sent to 
                    <strong>{$email}</strong>");

        }

        $this->view->pageTitle = "Password Reset";
        $this->view->isSuccess = isset($_GET["success"]);
        $this->view->isError = isset($_GET["error"]);
    }


    /**
     * Verifies user by email and sends a link that will redirect to a form
     * for new password input
     * @param $email
     */
    private function sendResetPasswordEmail($email)
    {
        $initFile = Utility::getIniFile();
        $user = User::getInstance();
        $hash = $user->getUserByEmail($email)->verification_hash;

        if (empty($hash)) {
            $this->setError("There is no user registered with this email address");
        }
        if (filter_var($initFile['EMAIL_ENABLED'], FILTER_VALIDATE_BOOLEAN)) {
            $emailService = EmailService::getInstance();
            $emailService->sendEmail(
                $email,
                "Reset your password",
                "Follow <a href='" . Utility::getApplicationURL() . "/password-save?hash={$hash}'>this link</a> 
                to reset your password.");
        }
    }

}
