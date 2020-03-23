<?php

namespace controllers;

use stdClass;

abstract class Controller
{
    public $viewName;
    protected $view;

    function __construct() {
        $this->view = new stdClass();
    }

    abstract public function render($params);

    protected final function redirect($url) {
        header("Location: $url");
        header("Connection: close");
        exit;
    }

    public final function showView() {
        extract((array)$this->view);
        require dirname(__DIR__)."/views/{$this->viewName}/{$this->viewName}.phtml";
    }



    /**
     * Load the model with the given name.
     * loadModel("SongModel") would include models/songmodel.php and create the object in the controller, like this:
     * $songs_model = $this->loadModel('SongsModel');
     * Note that the model class name is written in "CamelCase", the model's filename is the same in lowercase letters
     * @param string $model_name The name of the model
     * @return object model
     */
    public function loadModel($model_name)
    {
        require '../models/' . $model_name . '.php';
        // return new model (and pass the database connection to the model)
        return new $model_name($this->db);
    }
}