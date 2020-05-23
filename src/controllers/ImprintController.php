<?php

namespace controllers;

/**
 * Class ImprintController
 * Controls the imprint page.
 * @package controllers
 */
class ImprintController extends Controller
{
    public function render()
    {
        $this->view->pageTitle = "Imprint";
    }
}
