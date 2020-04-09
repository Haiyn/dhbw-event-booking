<?php


namespace controllers;

use models\User;

class EditProfileController extends Controller
{

    /*do i have to check if session exists if i can only open editing when on user profile WHEN LOGGED IN??
    i.e. the user profile itself is not accessible if not logged in, therefore no "edit profile" button*/
    public function render($params)
    {
        if (isset($_POST["username"]) || isset($_POST["first_name"]) || isset($_POST["last_name"]) || isset($_POST["email"]) || isset($_POST["password"])){
            $new_data = [
                'username' => filter_var(htmlspecialchars($_POST['username']), FILTER_SANITIZE_STRING),
                'first_name' => filter_var(htmlspecialchars($_POST['first_name']), FILTER_SANITIZE_STRING),
                'last_name' => filter_var(htmlspecialchars($_POST['last_name']), FILTER_SANITIZE_STRING),
                'email' => filter_var(htmlspecialchars($_POST['email']), FILTER_SANITIZE_EMAIL),
                'password' => htmlspecialchars($_POST['password']),
            ];

            foreach ($new_data as $key => &$value) {
                $new_data[$key] = trim($value);
            }

            /*TODO validate if entered data is in correct form*/
            $this->updateInfo($new_data);
            $this->setSuccess("Profile successfully updated");
        }

        $this->view->pageTitle = "Edit Profile";
        $this->view->isSuccess = isset($_GET["success"]);

    }


    private function updateInfo($new_data){

        if (isset($_POST["update"])){
            /*TODO update values in database from user input*/


        }
    }




}