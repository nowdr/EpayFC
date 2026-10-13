<?php

require_once "framework/Model.php";

class User extends Model {

    public function __construct(
        private string $full_name, 
        private string $email, 
        private string $pseudo, 
        private string $password, 
        private string $role,
        private string $picture_path = "ressources/default_profile_picture.jpg",
        private ?int $id = null,
        private ?string $iban = null
    ) {}

    public function get_full_name(): string {
        return $this->full_name;
    }

    public function get_email(): string {
        return $this->email;
    }

    public function get_pseudo(): string {
        return $this->pseudo;
    }

    public function get_password(): string {
        return $this->password;
    }

    public function get_role(): string {
        return $this->role;
    }
    public function get_picture_path(): string {
        return $this->picture_path;
    }

    public function get_id(): ?int {
        return $this->id;
    }
    public function get_iban(): ?string {
        return $this->iban;
    }
    
    public function set_full_name(string $full_name): void {
        $this->full_name = $full_name;
    }

    public function set_email(string $email): void {
        $this->email = $email;
    }

    public function set_pseudo(string $pseudo): void {
        $this->pseudo = $pseudo;
    }

    public function set_password(string $password): void {
        $this->password = $password;
    }

    public function set_picture_path(string $picture_path): void {
        $this->picture_path = $picture_path;
    }
    
    public function set_iban(?string $iban): void {
        $this->iban = $iban;
    }

    public static function get_user_by_id(int $id) : ?User {
        $query = self::execute("SELECT * FROM users WHERE id = :id", ["id" => $id]);
        $data = $query->fetch();
        return $data ? self::build($data) : null;
    }

    public static function get_user_by_field(string $field, string $value) : ?User {
        $query = self::execute("SELECT * FROM users WHERE $field = :value", ["value" => $value]);
        $data = $query->fetch();
        return $data ? self::build($data) : null;
    }

    public function persist() : User {
        self::execute(
            "INSERT INTO users (full_name, email, pseudo, password, role) VALUES (:full_name, :email, :pseudo, :password, :role)",
            [
                "full_name" => $this->full_name,
                "email" => $this->email,
                "pseudo" => $this->pseudo,
                "password" => $this->password,
                "role" => $this->role
            ]
        );
        $this->id = self::lastInsertId();
        return $this;
    }

    public function update_profile(array $data) : void {
        $this->full_name = $data["full_name"];
        $this->pseudo = $data["pseudo"];
        $this->email = $data["email"];
        $this->iban = $data["iban"];
        self::execute(
            "UPDATE users SET full_name=:full_name, pseudo=:pseudo, email=:email, iban=:iban WHERE id=:id",
            [
                "full_name" => $this->full_name,
                "pseudo" => $this->pseudo,
                "email" => $this->email,
                "id" => $this->id,
                "iban" => $this->iban
            ]
        );
    }

    public function update_password(string $new_password) : void {
        $this->password = $new_password;
        self::execute("UPDATE users SET password=:password WHERE id=:id",
            ["password" => $new_password, "id" => $this->id]);
    }

    public function update_profile_picture(string $new_picture_path) : void {
        $this->picture_path = $new_picture_path;
        self::execute("UPDATE users SET picture_path=:picture_path WHERE id=:id",
            ["picture_path" => $new_picture_path, "id" => $this->id]);
    }

    public static function validate_login(string $email, string $password) : array {
        $errors = [];
        $user = self::get_user_by_field("email", $email);

        if (!$user || !self::check_password($password, $user->get_password())) {
            $errors["login"] = "Incorrect email or password. Please try again.";
        }

        return $errors;
    }

    public static function validate_profile_data(string $full_name, string $email, string $pseudo, ?string $iban = null) : array {
        $errors = [];

        $full_name_min_length = Configuration::get("full_name_min_length");
        $email_regex = "/" . Configuration::get("email_format_regex") . "/";
        $pseudo_min_length = Configuration::get("pseudo_min_length");
        $pseudo_max_length = Configuration::get("pseudo_max_length");

        if (empty($full_name) || strlen($full_name) < $full_name_min_length) {
            $errors["full_name"] = "Name must contain at least $full_name_min_length characters.";
        }
        if (empty($email) || !preg_match($email_regex, $email)) {
            $errors["email"] = "Invalid email address format. Please use following format : \"local-part@domain.tld\".";
        }
        if (empty($pseudo) || strlen($pseudo) < $pseudo_min_length || strlen($pseudo) > $pseudo_max_length) {
            $errors["pseudo"] = "Pseudo must be between $pseudo_min_length and $pseudo_max_length characters long.";
        }
        if (!empty($iban)) {
            $iban_regex = "/" . Configuration::get("iban_format_regex") . "/";
            
            if (!preg_match($iban_regex, $iban)) {
                $errors["iban"] = "Invalid IBAN format. Please use following format : \"BE99 9999 9999 9999 9999\".";
            }
        }
        return $errors;
    }

    public static function validate_unicity_email(string $email, ?int $id = null) : array {
        $errors = [];
        $user = self::get_user_by_field("email", $email);

        if ($user && ($id == null || $id !== $user->get_id())) {
                $errors["email"] = "This email is already taken.";
            }

        return $errors;
    }

    public static function validate_unicity_pseudo(string $pseudo, ?int $id = null) : array {
        $errors = [];
        $user = self::get_user_by_field("pseudo", $pseudo);

        if ($user && ($id == null || $id !== $user->get_id())) {
                $errors["pseudo"] = "This username is already taken.";
            }

        return $errors;
    }

    public static function validate_unicity_full_name(string $full_name, ?int $id = null) : array {
        $errors = [];
        $user = self::get_user_by_field("full_name", $full_name);

        if ($user && ($id == null || $id !== $user->get_id())) {
                $errors["full_name"] = "This name is already taken.";
            }

        return $errors;
    }

    public static function validate_password(string $password) : array {
        $errors = [];
        $min_length = Configuration::get("password_min_length");
        $password_regex = "/" . Configuration::get("password_format_regex") . "/";

        if (strlen($password) < $min_length) {
            $errors["password"] = "Password must contain at least $min_length characters.";
        } else if (!preg_match($password_regex, $password)) {
            $errors["password"] = "Password must contain one lowercase letter, one uppercase letter, one number and one special character."; 
        }
        return $errors;
    }
    
    public static function validate_passwords(string $password, string $password_confirm) : array {
        $errors = self::validate_password($password);
        if ($password != $password_confirm) {
            $errors["password_confirm"] = "You have to enter twice the same password.";
        }
        return $errors;
    }

    public function change_password(string $current_password, string $hashed_password, string $new_password, string $new_password_confirm) : array {
        $errors = [];
        if (!self::check_password($current_password, $hashed_password)) {
            $errors["current_password"] = "Wrong password. Try again.";
        }
        return array_merge($errors, self::validate_passwords($new_password, $new_password_confirm));
    }

    public function get_user_data() : array {
        return [
            "full_name" => $this->full_name,
            "email" => $this->email,
            "pseudo" => $this->pseudo,
            "iban" => $this->iban
        ];
    }

    public function is_admin() : bool {
        return $this->role == "admin";
    }

    private static function check_password(string $clear_password, string $hashed_password) : bool {
        return password_verify($clear_password, $hashed_password);
    }

    private static function build(array $data) : User {
        return new User(
            $data["full_name"],
            $data["email"],
            $data["pseudo"],
            $data["password"],
            $data["role"],
            $data["picture_path"] ?? "ressources/default_profile_picture.jpg",
            $data["id"],
            $data["iban"]
        );
    }
}