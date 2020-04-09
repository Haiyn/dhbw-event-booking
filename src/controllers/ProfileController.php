<?php


namespace controllers;

use models\User;

class ProfileController extends Controller {

    public function render($params)
    {
/*TODO
must be accessible from every page when logged in (check if user session is there)
function to edit data
when editing email, must demand email verification
if user's own profile, must be able to edit
if not own profile, must not be able to edit

add all events created by user??
add all events visited by user??*/

        $this->view->pageTitle = "My Profile";
    }

}