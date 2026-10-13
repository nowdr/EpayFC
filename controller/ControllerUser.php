<?php
    require_once "model/User.php";
    require_once "model/Item.php";
    require_once "model/Bid.php";
    require_once "framework/View.php";
    require_once "framework/Controller.php";
    require_once "utils/PictureService.php";

class ControllerUser extends Controller {
    public function index(): void {
        $this->redirect("user", "profile");
    }

    public function profile(): void {
        $user = $this->get_user_or_redirect();

        (new View("profile"))->show(
            [
                "user" => $user
            ]
        );
    }

    public function sales() : void {
        $user = $this->get_user_or_redirect();
        $source = "from-sales";
        $items = Item::get_users_solded_items_by_id($user->get_id());
        
        $items_count = count($items);
        $total_revenue = 0;
        $average_ticket = 0;
        $top_bidder = "None";
        $bids = [];
        $closing_dates = null;

        if ($items_count > 0) {
            $items_id = [];
            $bidders = [];

            foreach ($items as $index => $item) {
                $items_id[] = $item->get_id();
                $current_bid = Bid::get_winning_bid_by_item_id($item->get_id());
                $bids[$index] = $current_bid;
                if ($current_bid != null) {
                    $bidders[] = $current_bid->get_owner_pseudo();
                }
                $total_revenue += $item->get_max_bid();
            }

            $closing_dates = Item::get_closing_dates($items_id);

            $bidders_count = array_count_values($bidders);
            $top_bidder = array_search(max($bidders_count), $bidders_count);

            $average_ticket = round($total_revenue / $items_count, 2);
        }

        (new View("sales")) ->show(
            [
                "user" => $user,
                "source" => $source,
                "items" => $items, 
                "items_count" => $items_count, 
                "closing_dates" => $closing_dates, 
                "total_revenue" => $total_revenue,
                "top_bidder" => $top_bidder, 
                "average_ticket" => $average_ticket, 
                "bids" => $bids
            ]
        );
    }

    public function purchases() : void {
        $user = $this->get_user_or_redirect();
        $source = "from-purchases";
        $items = Item::get_purchased_items_by_user($user->get_id());
        
        $items_count = count($items);
        $total_spent = 0;
        $average_ticket = 0;
        $top_seller = "None";
        $closing_dates = null;

        if ($items_count > 0) {
            $items_id = [];
            $sellers = [];

            foreach ($items as $item) {
                $items_id[] = $item->get_id();
                $sellers[] = $item->get_owner_pseudo();
                $total_spent += $item->get_max_bid();
            }

            $closing_dates = Item::get_closing_dates($items_id);

            $sellers_count = array_count_values($sellers);
            $top_seller = array_search(max($sellers_count), $sellers_count);

            $average_ticket = round($total_spent / $items_count, 2);
        }

        (new View("purchases")) ->show(
            [
                "user" => $user,
                "source" => $source,
                "items" => $items,
                "items_count" => $items_count,
                "closing_dates" => $closing_dates,
                "total_spent" => $total_spent,
                "top_seller" => $top_seller,
                "average_ticket" => $average_ticket
            ]
        );
    }

    public function edit_profile() : void {
        $user = $this->get_user_or_redirect();
        $temp_data = $user->get_user_data();
        $errors = [];

        if (isset($_POST["full_name"]) && isset($_POST["pseudo"]) && isset($_POST["email"])) {
            $temp_data["full_name"] = $_POST["full_name"];
            $temp_data["pseudo"] = $_POST["pseudo"];
            $temp_data["email"] = strtolower($_POST["email"]);

            if (isset($_POST["iban"])) {
                $temp_data["iban"] = $_POST["iban"];
            }

            $errors = User::validate_profile_data($temp_data["full_name"], $temp_data["email"], $temp_data["pseudo"], $temp_data["iban"]);
            if (!isset($errors["full_name"])) {
                $errors = array_merge($errors, User::validate_unicity_full_name($temp_data["full_name"], $user->get_id()));
            }
            if (!isset($errors["pseudo"])) {
                $errors = array_merge($errors, User::validate_unicity_pseudo($temp_data["pseudo"], $user->get_id()));
            }
            if (!isset($errors["email"])) {
                $errors = array_merge($errors, User::validate_unicity_email($temp_data["email"], $user->get_id()));
            }

            if(empty($errors)) {
                $user->update_profile($temp_data);
                $this->redirect("user", "profile");
            }
        }
        
        (new View ("edit_profile"))->show(
            [
                "user" => $user,
                "temp_data" => $temp_data,
                "errors" => $errors
            ]
        );
    }

    public function change_password() : void {
        $user = $this->get_user_or_redirect();
        $current_password = "";
        $new_password = "";
        $new_password_confirm = "";
        $errors = [];

        if (isset($_POST["current_password"]) && isset($_POST["new_password"]) && isset($_POST["new_password_confirm"])) {
            $current_password = $_POST["current_password"];
            $new_password = $_POST["new_password"];
            $new_password_confirm = $_POST["new_password_confirm"];
            
            $errors = $user->change_password($current_password, $user->get_password(), $new_password, $new_password_confirm);

            if (empty($errors)) {
                $user->update_password(password_hash($new_password, PASSWORD_BCRYPT));
                $this->redirect("user", "profile");
            }
        }

        (new View("change_password"))->show(
            [
                "user" => $user,
                "errors" => $errors
            ]
        );
    }

    public function profile_picture() : void {
        $user = $this->get_user_or_redirect();
        $picture_service = new PictureService();
        $errors = [];

        if (isset($_POST["action"])) {
            $action = $_POST["action"];

            if ($action === "upload" && isset($_FILES["profile_picture"])) {
                $profile_picture = $_FILES["profile_picture"];

                $errors = $picture_service->validate($profile_picture);

                if (empty($errors)) {
                    $picture_service->upload($user, $profile_picture);
                    $this->redirect("user", "profile");
                }
            } elseif ($action === "delete") {
                $picture_service->delete($user);
                $this->redirect("user", "profile");
            }
        }

        (new View("profile_picture")) ->show(
            [
                "user" => $user,
                "errors" => $errors
            ]
        );
    }

    public function logout(): void {
        parent::logout();
    }

    public function get_rules_service() : void {
        $rules = 
        [
            "fullName" => 
            [
                "min" => Configuration::get("full_name_min_length"),
                "max" => Configuration::get("full_name_max_length")
            ],
            "email" =>
            [
                "max" => Configuration::get("email_max_length"),
                "regex" => Configuration::get("email_format_regex")
            ],
            "pseudo" =>
            [
                "min" => Configuration::get("pseudo_min_length"),
                "max" => Configuration::get("pseudo_max_length")
            ],
            "password" =>
            [
                "min" => Configuration::get("password_min_length"),
                "max" => Configuration::get("password_max_length"),
                "regex" => Configuration::get("password_format_regex")
            ],
            "iban" =>
            [
                "max" => Configuration::get("iban_max_length"),
                "regex" => Configuration::get("iban_format_regex")
            ]
        ];

        echo json_encode($rules);
    }

    public function is_full_name_unique_service() : void {
        $user = $this->get_user_or_false();
        $id = $user ? $user->get_id() : null;

        $json_data = json_decode(file_get_contents("php://input"), true);
        $full_name = $json_data["full_name"];
        $error = User::validate_unicity_full_name($full_name, $id);

        header("Content-Type: application/json");
        echo json_encode(
            [
                "isUnique" => empty($error)
            ]);
    }

    public function is_email_unique_service() : void {
        $user = $this->get_user_or_false();
        $id = $user ? $user->get_id() : null;

        $json_data = json_decode(file_get_contents("php://input"), true);
        $email = $json_data["email"];
        $error = User::validate_unicity_email($email, $id);

        header("Content-Type: application/json");
        echo json_encode(
            [
                "isUnique" => empty($error)
            ]);
    }

    public function is_pseudo_unique_service() : void {
        $user = $this->get_user_or_false();
        $id = $user ? $user->get_id() : null;
        
        $json_data = json_decode(file_get_contents("php://input"), true);
        $pseudo = $json_data["pseudo"];
        $error = User::validate_unicity_pseudo($pseudo, $id);

        header("Content-Type: application/json");
        echo json_encode(
            [
                "isUnique" => empty($error)
            ]);
    }    
}
?>

