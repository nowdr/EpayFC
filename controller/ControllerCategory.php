<?php
require_once 'framework/Controller.php';
require_once 'framework/View.php';
require_once "model/Categories.php";

class ControllerCategory extends Controller {
    public function index(): void {
        $this->redirect("category", "manage");
    }

    public function manage(): void {
        $user = $this->get_user_or_redirect();

        if($user->is_admin()) {
            $categories = Categories::get_all_categories();
            (new View("manage_category"))->show(
                [
                    "categories" => $categories,
                    "user" => $user
                ]
            );
        } else {
            $this->redirect("items", "display");
        }
    }

    public function manage_category(): void {
        $user = $this->get_user_or_redirect();
        $errors = [];
        $submitted_names = [];
        $add_name = "";
        $categories = Categories::get_all_categories();

        if(!empty($_POST)){
            $action = $_POST['action'];
            $category_id = (int)$_POST['category_id'];

            if($action == "add") {
                $category = null;

            } else {
                $category = Categories::find_category_by_id($category_id);
            }
            
            switch($action) {
                case "moveUp":
                    $top_category = Categories::find_category_by_prio($category->get_priority() - 1);
                    Categories::swap($category, $top_category);
                    $this->redirect("category", "manage");
                    break;
                
                case "moveDown":
                    $bot_category = Categories::find_category_by_prio($category->get_priority() + 1);
                    Categories::swap($category, $bot_category);
                    $this->redirect("category", "manage");
                    break;

                case "save":
                    $name = $_POST['name'];
                    $category->set_name($name);
                    $errors = $category->validate_category_name();

                    if (empty($errors)) {
                        $category->update_categories();
                        $this->redirect("category", "manage");

                    } else {
                        $submitted_names[$category_id] = $name;
                    }
                    break;

                case "delete":
                    $this->redirect("category", "delete_confirm", $category_id);
                    break;

                case "add":
                    $name = $_POST['name'];
                    $new_category = new Categories(0, $name, count($categories) + 1);
                    $errors = $new_category->validate_category_name();

                    if (empty($errors)) {
                        $new_category->persist();
                        $this->redirect("category", "manage");

                    } else {
                        $add_name = $name;
                    }
                    break;
            }
        }
        (new View("manage_category"))->show(
                [
                    "categories" => $categories,
                    "user" => $user,
                    "errors" => $errors,
                    "submitted_names" => $submitted_names,
                    "add_name" => $add_name
                ]
            );
    }

    public function delete_confirm(): void {
        $user = $this->get_user_or_redirect();
        $category_id = null;
        
        if (isset($_GET['param1']) && $user->is_admin()) {
            $category_id = $_GET['param1'];
        } else {
            Tools::abort("You don't have the permission to do that.");
        }

        $category = Categories::find_category_by_id($category_id);

        (new View("delete_category"))->show(
            [
                "user" => $user,
                "category" => $category
            ]
        );
    }

    public function delete(): void {
        $user = $this->get_user_or_redirect();
        $category_id = (int)$_POST['category_id'];
        $category = Categories::find_category_by_id($category_id);
        
        if ($user->is_admin() && $category->is_empty()) {
            $category->delete_category();
            $this->redirect("category", "manage");

        } else {
            Tools::abort("You don't have the permission to do that.");
        }
    }

    public function get_categories_service(): void {
        $data = [];
        $categories = Categories::get_all_categories_and_count();
        
        foreach ($categories as $category) {
            $data[] = [
                "id" => $category->get_id(),
                "name" => $category->get_name(),
                "priority" => $category->get_priority(),
                "nb_item_assigned" => $category->get_nb_item_assigned()
            ];
        }
        echo json_encode($data);
    }

    public function get_rules_service(): void {
        $rules = 
        [
            "name" => 
            [
                "min" => Configuration::get("category_name_min_length"),
                "max" => Configuration::get("category_name_max_length")
            ]
        ];

        echo json_encode($rules);
    }

    public function is_category_name_unique_service(): void {
        $json_data = json_decode(file_get_contents("php://input"), true);
        $category_name = $json_data["category_name"];
        $id = (int)$json_data["id"] = 0 ? null : (int)$json_data["id"];

        $error = Categories::validate_unicity_category_name($category_name, (int)$id);

        header("Content-Type: application/json");
        echo json_encode(
            [
                "isUnique" => empty($error)
            ]);
    }

    public function update_service(): void {
        $json_data = json_decode(file_get_contents("php://input"), true);
        $category_name = $json_data["category_name"];
        $id = $json_data["id"];
        $count = $json_data["count"] ?? 0;

        $category = new Categories((int)$id, $category_name, 0);
        $category->update_categories();

        $data = [
            "id" => $category->get_id(),
            "name" => $category->get_name(),
            "priority" => $category->get_priority(),
            "nb_item_assigned" => $count
        ];

        header("Content-Type: application/json");
        echo json_encode($data);
    }

    public function update_priorities_service(): void {
        $json_data = json_decode(file_get_contents("php://input"), true);
        $orders = $json_data["orders"] ?? [];

        // On libère les places en mettant des priorités temporaires (ex: -1, -2, -3...)
        foreach ($orders as $item) {
            $id = (int)$item['id'];
            $category = Categories::find_category_by_id($id);
            if ($category) {
                // On met une priorité négative pour éviter les doublons avec les existants
                $category->set_priority($item['priority'] * -1); 
                $category->update_categories_priority();
            }
        }

        foreach ($orders as $item) {
            $category = Categories::find_category_by_id((int)$item['id']);
            if ($category) {
                $category->set_priority((int)$item['priority']);
                $category->update_categories_priority();
            }
        }
        
        header("Content-Type: application/json");
        echo json_encode(["status" => "success"]);
    }

    public function delete_service(): void {
        $json_data = json_decode(file_get_contents("php://input"), true);
        $category_id = $json_data["id"];
        $category = Categories::find_category_by_id($category_id);
        
        if ($category->is_empty()) {
            $category->delete_category();
        }

        header("Content-Type: application/json");
        echo json_encode(["status" => "success"]);
    }

    public function persiste_service(): void {
        $json_data = json_decode(file_get_contents("php://input"), true);
        $name = $json_data["category_name"];
        $priority = $json_data["priority"];
        $category = new Categories(0, $name, $priority);
        $new_category = $category->persist();

        $data = [
            "id" => $new_category->get_id(),
            "name" => $new_category->get_name(),
            "priority" => $new_category->get_priority(),
            "nb_item_assigned" => $new_category->get_nb_item_assigned()
        ];

        header("Content-Type: application/json");
        echo json_encode($data);
    }
}