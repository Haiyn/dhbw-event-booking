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

        //The save button was pressed
        if (isset($_POST["username"]) || isset($_POST["first_name"]) || isset($_POST["last_name"])) {
            $new_data = [
                'username' => filter_var(htmlspecialchars($_POST['username']), FILTER_SANITIZE_STRING),
                'first_name' => filter_var(htmlspecialchars($_POST['first_name']), FILTER_SANITIZE_STRING),
                'last_name' => filter_var(htmlspecialchars($_POST['last_name']), FILTER_SANITIZE_STRING),
            ];

            foreach ($new_data as $key => &$value) {
                $new_data[$key] = trim($value);
            }
            $this->updatePersonalInfo($new_data, $userId);
            $this->setSuccess("Profile successfully updated");
        }

        $this->view->pageTitle = "Edit Profile";
        $this->view->isSuccess = isset($_GET["success"]);
        $this->view->isError = isset($_GET["error"]);

    }

    /* || isset($_POST["email"]) || isset($_POST["password"]))
    'email' => filter_var(htmlspecialchars($_POST['email']), FILTER_SANITIZE_EMAIL),
                    'password' => htmlspecialchars($_POST['password']),
    */


    /**
     * Updates username, first name or last name
     * @param $new_data * new data to be saved to database
     * @param $old_data *existing data
     */
    private function updatePersonalInfo($new_data, $old_data)
    {
        $user = User::getInstance();
        $userId = $user->getUserById($_SESSION['USER_ID']);
        //$data["username"] = $userId->username;

         $userValidator = UserValidation::getInstance();
         try {
             $userValidator->validateNewData($new_data, $old_data);
         } catch (ControllerException $exception) {
             $this->setError($exception->getMessage());
         }

        $new_data += ["user_id" => $userId];
        $user->updateUserData($new_data);

        if (!$userId->updateUser($new_data)) {
            $this->setError("Something went wrong");
        }

    }


    /**
     *Updates email address and requires the user to confirm new address
     * @param $new_data
     * @param $old_data
     */
    private function updateEmail($new_data, $old_data)
    {
        $user = User::getInstance();

        if (isset($_SESSION["USER_ID"])) {
            $user_data["user_id"] = $user->getUserById($_SESSION["USER_ID"]);
        }
//TODO how to get new email address?
        $this->checkData($new_data, $old_data);
        $user->updateUser($new_data);
        $email = $user->getUserByEmail($new_data['email']);
        $this->confirmEmail($email);

    }

    /**
     *Updates password after checking if new password and repeated password match and requires
     * user to confirm new password via email
     * @param $new_data
     * @param $old_data
     */
    private function updatePassword($new_data, $old_data)
    {
        $user = User::getInstance();

        if (isset($_SESSION["USER_ID"])) {
            $user_data["user_id"] = $user->getUserById($_SESSION["USER_ID"]);
        }

        $this->checkData($new_data, $old_data);

        if ($new_data['password'] !== $old_data->password && strlen($new_data["password"]) > 32) {
            $this->setError("Length of password cannot exceed max length of 32.");
        }

        if ($new_data['password'] !== $new_data['password_repeat']) {
            $this->setError("Entered passwords do not match!");
        } else {
            $user->updateUser($new_data);

            //TODO how to get user email?
            $email = $user->getUserByEmail($_SESSION['email']);
            $this->confirmEmail($email);

        }


    }

    /**
     * Sends email to user to confirm changes
     * @param $email
     */
    private function confirmEmail($email)
    {
        $iniFile = Utility::getIniFile();
        $user = User::getInstance();
        $hash = $user->getUserByEmail($email)->verification_hash;

        if (filter_var($iniFile['EMAIL_ENABLED'], FILTER_VALIDATE_BOOLEAN)) {
            // Send the notification email to the email address
            $emailService = EmailService::getInstance();
            $emailService->sendEmail($email,
                "Confirm your email address",
                "Follow <a href='{$iniFile['URL']}/confirm?hash={$hash}'>this link</a> to confirm your email address.");
        } else {
            $this->setError(
                "Email failed to send"
            );
        }
    }


    /**
     * Updates user info depending on button clicked
     * @param $new_data
     * @param $old_data
     */
    private function updateInfo($new_data, $old_data)
    {
        if (isset($_POST["updatePersonal"])) {
            $this->updatePersonalInfo($new_data, $old_data);
        } elseif (isset($_POST["updateEmail"])) {
            $this->updateEmail();
        } elseif (isset($_POST["updatePassword"])) {
            $this->updatePassword();
        }
        $this->setSuccess("Successfully updated profile");

        /* if (isset($_POST["update"])) {
             $user = User::getInstance();

             if (isset($_SESSION["USER_ID"])) {
                 $data["user_id"] = $user->getUserById($_SESSION["USER_ID"]);
             }

             $this->checkData($new_data, $old_data);

             $user->updateUser($new_data);

         }*/
    }


}

