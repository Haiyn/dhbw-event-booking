<?php

namespace controllers;

use components\core\Utility;
use components\email\EmailService;
use components\validators\ValidatorException;
use components\validators\UserValidator;
use models\User;

/**
 * Class PasswordSaveController
 * Controls resetting password and saving to database
 * @package controllers
 */
class PasswordSaveController extends Controller
{
    public function render($params)
    {

        if (isset($_GET['hash'])) {

            $hash = htmlspecialchars($_GET['hash']);
            $user = User::getInstance();

            //Save button pressed on password change
            if (isset($_POST["password"])) {
                $new_data = [
                    'password' => htmlspecialchars($_POST['password'])
                ];

                foreach ($new_data as $key => &$value) {
                    $new_data[$key] = trim($value);
                }
                $this->updatePassword($new_data);
                $this->setSuccess("You have successfully changed your password");
            }
        } else {
            $this->setError("Sorry, something went wrong!");

        }


        $this->view->pageTitle = "Password Save";
        $this->view->isSuccess = isset($_GET["success"]);
        $this->view->isError = isset($_GET["error"]);
    }

    /**
     *Updates password after checking if new password and repeated password match and requires
     * user to confirm new password via email
     * @param $new_data
     */
    private
    function updatePassword($new_data)
    {
        $user = User::getInstance();
        /* $userId = $_SESSION['USER_ID'];*/

        $userValidator = UserValidator::getInstance();
        try {
            $userValidator->validateNewPassword($new_data);
        } catch (ValidatorException $exception) {
            $this->setError($exception->getMessage());
        }

        //$new_data += ["user_id" => $userId];

        if (!$user->updatePassword($new_data)) {
            $this->setError("Something went wrong");
        }
    }
}

