<?php
require_once "framework/Controller.php";
require_once "utils/AppTime.php";

class ControllerTest extends Controller {
    public function index(): void {
        (new View("openItem")) -> show();
    }
}