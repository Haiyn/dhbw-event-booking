<?php


namespace controllers;

use components\core\ControllerException;
use components\validators\UserValidation;
use models\User;

class EditProfileController extends Controller
{
    public function render($params)
    {
        $this->session->checkSession();
        $user = User::getInstance();
        $userId = $user->getUserById($_SESSION['USER_ID']);

        //Save button pressed on personal info
        if (isset($_POST["username"]) || isset($_POST["first_name"]) || isset($_POST["last_name"]) || isset($_POST["email"])) {
            $new_data = [
                'username' => filter_var(htmlspecialchars($_POST['username']), FILTER_SANITIZE_STRING),
                'first_name' => filter_var(htmlspecialchars($_POST['first_name']), FILTER_SANITIZE_STRING),
                'last_name' => filter_var(htmlspecialchars($_POST['last_name']), FILTER_SANITIZE_STRING),
                'email' => filter_var(htmlspecialchars($_POST['email']), FILTER_SANITIZE_EMAIL)
            ];

            foreach ($new_data as $key => &$value) {
                $new_data[$key] = trim($value);
            }
            $this->updatePersonalInfo($new_data, $userId);
            $this->setSuccess("Profile successfully updated");
        }

        //Save button pressed on password change
        if (isset($_POST["password"])) {
            $new_data = [
                'password' => htmlspecialchars($_POST['password'])
            ];

            foreach ($new_data as $key => &$value) {
                $new_data[$key] = trim($value);
            }
            $this->updatePassword($new_data, $userId);
            $this->setSuccess("Please verify your new password");
        }

        $this->view->pageTitle = "Edit Profile";
        $this->view->isSuccess = isset($_GET["success"]);
        $this->view->isError = isset($_GET["error"]);

    }


    /**
     * Updates username, first name, last name or email
     * @param $new_data * new data to be saved to database
     * @param $old_data *existing data
     */
    private
    function updatePersonalInfo($new_data, $old_data)
    {
        $user = User::getInstance();
        $userId = $_SESSION['USER_ID'];

        // Check if username is already in database
        $existingUser = $user->getUserByUsername($new_data["username"]);
        if (!empty($existingUser)) {
            $this->setError("This username is already taken!");
        }

        // Check if email is already in database
        /* $existingUser = $user->getUserByEmail($new_data["email"]);
         if (!empty($existingUser)) {
             $this->setError("An account with this E-Mail is already registered!");
         }*/

        $userValidator = UserValidation::getInstance();
        try {
            $userValidator->validateNewData($new_data, $old_data);
        } catch (ControllerException $exception) {
            $this->setError($exception->getMessage());
        }

        $new_data += ["user_id" => $userId];

        //TODO data is updated but error displays
        //TODO if nothing entered, deletes existing data (ex. if no email entered to update, empties email in database
        if (!$user->updateUserData($new_data)) {
            $this->setError("Something went wrong");
        }
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
        if ($user->updatePassword($new_data)){
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
    private function confirmationEmail($user, $email)
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
    }


}

