<?php


namespace controllers;

use components\core\ControllerException;
use components\validators\UserValidation;
use models\User;

class ResetPasswordController extends Controller
{
    public function render($params)
    {

      /*  $this->session->checkSession();
        $user = User::getInstance();
        $userId = $user->getUserById($_SESSION['USER_ID']);

        if (isset($_POST["password"])) {
            $new_data = [
                'password' => htmlspecialchars($_POST['password'])
            ];

            foreach ($new_data as $key => &$value) {
                $new_data[$key] = trim($value);
            }
            $this->updatePassword($new_data, $userId);
            $this->setSuccess("Please verify your new password");
        }*/

        $this->view->pageTitle = "Reset Password";
        $this->view->isSuccess = isset($_GET["success"]);
        $this->view->isError = isset($_GET["error"]);
    }

    /**
     *Updates password after checking if new password and repeated password match and requires
     * user to confirm new password via email
     * @param $new_data
     * @param $old_data
     */
    private
    function updatePassword($new_data, $old_data)
    {

//        TODO GET USER INSTANCE BY CHECKING EMAIL OR USERNAME BECAUSE USER IS NOT LOGGED IN HERE
        $user = User::getInstance();
        $userId = $_SESSION['USER_ID'];

        $userValidator = UserValidation::getInstance();
        try {
            $userValidator->validateNewPassword($new_data, $old_data);
        } catch (ControllerException $exception) {
            $this->setError($exception->getMessage());
        }

        $new_data += ["user_id" => $userId];
        $email = $user->getUserByEmail($userId->email);

        //TODO doesn't send email
        if ($user->updatePassword($new_data)) {
            $this->confirmationEmail($user, $email);
        }

        /* if (!$user->updatePassword($new_data)) {
             $this->setError("Something went wrong");
         }*/


    }

    /**
     * Sends email to user to confirm changes
     * @param $email
     */
  /*  private function confirmationEmail($user, $email)
    {
        $iniFile = Utility::getIniFile();

        if (filter_var($iniFile['EMAIL_ENABLED'], FILTER_VALIDATE_BOOLEAN)) {
            // Send the notification email to the email address
            $emailService = EmailService::getInstance();
            $emailService->sendEmail($user->email,
                "Your password has been updated.");
        } else {
            $this->setError(
                "Email failed to send"
            );
        }
    }*/
}