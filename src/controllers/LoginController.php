<?php

namespace controllers;

use components\core\Utility;
use models\User;

class LoginController extends Controller
{
    public function render($parameters)
    {
        session_start();
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

            $this->view->pageTitle = "Login";
            $this->view->isSuccess = isset($_GET["success"]);
            $this->view->isError = isset($_GET["error"]);
        }
    }

    private function loginUser($user_data)
    {
        if (isset($_POST['login'])) {

            $user = User::newInstance();

            $password_hash = md5(Utility::getIniFile()['AUTH_SALT'] . $user_data['password']);

            $validUsername = $user->getUserByUsername($user_data['emailOrId']);
            $validEmail = $user->getUserByEmail($user_data['emailOrId']);


            if ($validUsername) {
                $validPassword = $validUsername->password;
                if ($password_hash == $validPassword) {
                    $this->redirect("event-overview");
                } else {
                    $this->setError("Invalid password");
                }
            } else if ($validEmail) {
                $validPassword = $validEmail->password;
                if ($password_hash == $validPassword) {
                    $this->redirect("event-overview");
                } else {
                    $this->setError("Invalid password");
                }
            } else {
                $this->setError("Invalid Username or Password");
            }
        }
    }
}




