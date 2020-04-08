<?php

namespace controllers;

use components\core\Utility;
use models\User;

class LoginController extends Controller
{
    public function render($parameters)
    {
        if (isset($_POST["emailOrId"]) && isset($_POST["password"])) {

            // Sanitize the data by removing any harmful code and markup
            $user_data = [
                "emailOrId" => filter_var(htmlspecialchars($_POST["emailOrId"]), FILTER_SANITIZE_STRING, FILTER_SANITIZE_EMAIL),
                "password" => htmlspecialchars($_POST["password"])
            ];

            // Trim every value to assert that no whitespaces are submitted
            foreach ($user_data as $key => &$value) {
                $user_data[$key] = trim($value);
            }

            $this->loginUser($user_data);
        }
        $this->view->pageTitle = "Login";
        $this->view->isSuccess = isset($_GET["success"]);
        $this->view->isError = isset($_GET["error"]);
    }

    private function loginUser($user_data)
    {
        if (isset($_POST['login'])) {

            $user = User::getInstance();

            $password_hash = md5(Utility::getIniFile()['AUTH_SALT'] . $user_data['password']);

            // Get the user, check for username first, then for email if username is not found
            $foundUser = $user->getUserByUsername($user_data['emailOrId']);
            if (empty($foundUser)) {
                $validEmail = $user->getUserByEmail($user_data['emailOrId']);
            }

            if (!empty($foundUser)) {
                $validPassword = $foundUser->password;
                if ($password_hash == $validPassword) {
                    // Check if the user has verified their email
                    if (!$foundUser->verified) {
                        // If not, redirect to the register verify handler
                        $this->setError("Please confirm your email address. Follow
                        <a href='/register?verify={$foundUser->email}'> to confirm</a>.");
                    } else {
                        // Refresh the session and set a logged in session, then redirect
                        $this->session->setSession($foundUser->user_id);
                        $this->redirect("event-overview");
                    }
                } else {
                    $this->setError("Invalid password");
                }
            } else {
                // No user with the given username or email was found
                $this->setError("Invalid Username or Password");
            }
        }
    }
}

