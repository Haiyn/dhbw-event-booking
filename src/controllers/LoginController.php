<?php

namespace controllers;

use components\validators\UserValidator;
use components\validators\ValidatorException;
use models\User;

/**
 * Class LoginController
 * Controls the login form for users.
 * @package controllers
 */
class LoginController extends Controller
{
    public function render()
    {
        if (isset($_POST["emailOrId"]) && isset($_POST["password"])) {
            // Sanitize the data by removing any harmful code and markup
            $user_data = [
                "emailOrId" => filter_var(
                    htmlspecialchars($_POST["emailOrId"]),
                    FILTER_SANITIZE_STRING,
                    FILTER_SANITIZE_EMAIL
                ),
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
            // Get the user, check for username first, then for email if username is not found
            $user = User::getInstance();
            $user_data['foundUser'] = $user->getUserByUsername($user_data['emailOrId']);
            if (empty($user_data['foundUser'])) {
                $user_data['foundUser'] = $user->getUserByEmail($user_data['emailOrId']);
            }

            // Validate all data
            $userValidator = UserValidator::getInstance();
            try {
                $userValidator->validateLoginData($user_data);
            } catch (ValidatorException $exception) {
                $this->setError($exception->getMessage());
            }

            // Everything successful, refresh the session and set a logged in session, then redirect
            $this->session->setSession($user_data['foundUser']->user_id);
            $this->redirect("home");
        }
    }
}
