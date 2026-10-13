<?php
require_once "model/User.php";
require_once "framework/View.php";
require_once "framework/Controller.php";

class ControllerMain extends Controller {
    public function index() : void {
        if ($this->user_logged()) {
            $this->redirect("items", "display");
        } else {
            $this->redirect("main", "login");
        }
    }

    public function login() : void {
        $email = "";
        $password = "";
        $errors = [];

        if ($this->user_logged()) {
            $this->redirect("items", "display");
        } else {
            if (isset($_POST["email"]) && isset($_POST["password"])) {
                $email = strtolower($_POST["email"]);
                $password = $_POST["password"];

                $errors = User::validate_login($email, $password);

                if (empty($errors)) {
                    $user = User::get_user_by_field("email", $email);
                    $this->log_user($user, "items");
                }
            }
        }
        
        (new View("login"))->show(
            [
                "email" => $email,
                "errors" => $errors
            ]
        );
    }

    public function signup() : void {
        $full_name = "";
        $email = "";
        $pseudo = "";
        $password = "";
        $password_confirm = "";
        $errors = [];

        if ($this->user_logged()) {
            $this->redirect("items", "display");
        } else {
            if (isset($_POST["full_name"]) && isset($_POST["email"]) && isset($_POST["pseudo"]) && isset($_POST["password"]) && isset($_POST["password_confirm"])) {
                $full_name = $_POST["full_name"];
                $email = strtolower($_POST["email"]);
                $pseudo = $_POST["pseudo"];
                $password = $_POST["password"];
                $password_confirm = $_POST["password_confirm"];

                $errors = User::validate_profile_data($full_name, $email, $pseudo);
                if (!isset($errors["email"])) {
                    $errors = array_merge($errors, User::validate_unicity_email($email));
                }
                if (!isset($errors["pseudo"])) {
                    $errors = array_merge($errors, User::validate_unicity_pseudo($pseudo));
                }
                if (!isset($errors["full_name"])) {
                    $errors = array_merge($errors, User::validate_unicity_full_name($full_name));
                }
                $errors = array_merge($errors, User::validate_passwords($password, $password_confirm));

                if (empty($errors)) {
                    $user = new User($full_name, $email, $pseudo, password_hash($password, PASSWORD_BCRYPT), "user");
                    $user->persist();
                    $this->log_user($user, "items");
                }
            }
        }

        (new View("signup"))->show(
            [
                "full_name" => $full_name,
                "email" => $email,
                "pseudo" => $pseudo,
                "errors" => $errors
            ]
        );
    }
}