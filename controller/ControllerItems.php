<?php
require_once 'framework/Controller.php';
require_once 'framework/View.php';
require_once 'model/Item.php';
require_once 'model/Bid.php';
require_once "model/User.php";
require_once "utils/PictureService.php";
require_once "utils/Encoder.php";
require_once "model/Categories.php";
require_once "model/ItemPictures.php";

class ControllerItems extends Controller {
    public function index(): void {
        $this->redirect("items", "display");
    }

    public function display(): void {
        $user = $this->get_user_or_false();
        $source = "from-browse";
        $user_research = isset($_GET['param1']) ? (string)$_GET['param1']: null;
        $user_id = $user ? $user->get_id() : null;
        $items = Item::get_all_items_view($user_id);

        $itemsParticipated = [];
        $otherItems = [];

        if ($user) {
            $userbids = Bid::get_user_bids_by_id($user->get_id());

            foreach ($items as $item) {
                $highestBidByItem = Bid::get_winning_bid_by_item_id($item->get_id());
                if (in_array($item->get_id(), $userbids)) {
                    $item->set_hasBidByUser(true);
                    if ($highestBidByItem && $highestBidByItem->get_owner() == $user->get_id()) {
                        $item->set_isHighestBidder(true);
                    }
                    $itemsParticipated[] = $item;
                } else {
                    $otherItems[] = $item;
                }
            }
        } else {
            $otherItems = $items;
        }

        (new View("browse_items"))->show(
            [
                "user" => $user,
                "source" => $source,
                "itemsParticipated" => $itemsParticipated,
                "otherItems" => $otherItems,
                "user_research" => $user_research
            ]
        );
    }

    public function my_items(): void {
        $user = $this->get_user_or_redirect();
        $source = "from-my-items";
        $user_research = isset($_GET['param1']) ? (string)$_GET['param1']: null;
        $userItems = Item::get_users_active_items_by_id($user->get_id());
        $userNonActiveItems = Item::get_users_unactive_unsold_items_by_id($user->get_id());
        $userSoldedItems = Item::get_users_solded_items_by_id($user->get_id());

        (new View("my_items"))->show(
            [
                "user" => $user,
                "source" => $source,
                "user_research" => $user_research,
                "userItems" => $userItems,
                "userNonActiveItems" => $userNonActiveItems,
                "userSoldedItems" => $userSoldedItems,
            ]
        );
    }

    public function edit() : void {
        if(isset($_GET['param1'])) {

            $user = $this->get_user_or_redirect();
            $source = (isset($_GET['param2'])) ? (string)$_GET['param2'] : "from-my-items";
            $user_research = isset($_GET['param3']) ? (string)$_GET['param3']: null;
            $is_edit = true;
            $item_id = (int)$_GET['param1'];
            $item = Item::get_item_by_id($item_id);
            $categories = Categories::get_all_categories();
            $errors = [];

            if(!$item){
                Tools::abort("The requested item could not be found. It may have been deleted, or the link is incorrect.");
            }

            if($item->get_owner() != $user->get_id()){
                $this->redirect("items", "open", $item_id, $user_research);
            }

            $instant_purchase = ($item->get_is_auction() == 1) ? $item->get_buy_now_price() : "";
            $sale_price = ($item->get_is_auction() == 0) ? $item->get_buy_now_price() : "";
            $checked_categories = Categories::get_id_of_categories_for_item($item_id);

            if (!empty($_POST)) {

                $item->set_title($_POST["title"]);
                $item->set_description(!empty($_POST["description"]) ? $_POST["description"] : null);
                $item->set_duration_days((int)$_POST["duration_days"]);
                $item->set_starting_bid(!empty($_POST["starting_bid"]) ? (float)$_POST["starting_bid"] : 0.00);
                $item->set_buy_now_price(!empty($_POST["sale_price"]) 
                    ? (float)$_POST["sale_price"] 
                    : (!empty($_POST["instant_purchase"]) ? (float)$_POST["instant_purchase"] : null));
                $checked_categories = !empty($_POST["categories"]) ? $_POST["categories"] : [];

                $errors = $item->validate_item_data($user->get_id(), (float)$_POST["instant_purchase"] ?? null, (float)$_POST["sale_price"] ?? null);

                if(count($checked_categories) > 3) {
                    $errors["checkbox"] = "Can't check more than 3 Categories.";
                }

                if (empty($errors)) {
                    $item->update();
                    Categories::update_item_categories($item_id, $checked_categories);
                    if ($user_research != null) {
                        $this->redirect("items", "open", $item->get_id(), $source ,$user_research);
                    } else {
                        $this->redirect("items", "open", $item->get_id(), $source);
                    }
                    
                }
            }

            (new View("add_edit_item"))->show(
                [
                    "user" => $user,
                    "source" => $source,
                    "user_research" => $user_research,
                    "item" => $item,
                    "instant_purchase" => $instant_purchase,
                    "sale_price" => $sale_price,
                    "is_edit" => $is_edit,
                    "errors" => $errors,
                    "categories" => $categories,
                    "checked_categories" => $checked_categories
                ]
            );
        } else {
            $this->redirect("items", "display");
        }
    }

    public function add() : void {
        $user = $this->get_user_or_redirect();
        $is_edit = false;
        $item = new Item("", $user->get_id(), 7, new DateTime());
        $instant_purchase = "";
        $sale_price = "";
        $categories = Categories::get_all_categories();
        $user_research = null;
        $source = null;
        $errors = [];
        $checked_categories = [];

        if (!empty($_POST)) {
            $item->set_title($_POST["title"] ?? "");
            $item->set_description(!empty($_POST["description"]) ? $_POST["description"] : null);
            $item->set_duration_days((int)($_POST["duration_days"]));
            $item->set_created_at(new DateTime(AppTime::get_current_datetime()));
            $item->set_starting_bid(!empty($_POST["starting_bid"]) ? (float)$_POST["starting_bid"] : 0.00);
            $checked_categories = !empty($_POST["categories"]) ? $_POST["categories"] : [];
            
            $item->set_buy_now_price(!empty($_POST["sale_price"])
                ? (float)$_POST["sale_price"] 
                : (!empty($_POST["instant_purchase"]) ? (float)$_POST["instant_purchase"] : null));

            $instant_purchase = (float)$_POST["instant_purchase"] ?? "";
            $sale_price = (float)$_POST["sale_price"] ?? "";

            $errors = $item->validate_item_data($user->get_id(), $instant_purchase, $sale_price);

            if(count($checked_categories) > 3) {
                $errors["checkbox"] = "Can't check more than 3 Categories.";
            }

            if (empty($errors)) {
                if ($newItem = $item->persist()) {
                    Categories::assign_categories($newItem->get_id(), $checked_categories);
                    $this->redirect("items", "my_items");
                } else {
                    $errors["global"] = "A technical error occurred while creating the item.";
                }
            }
        }
        
        (new View("add_edit_item"))->show(
            [
                "user" => $user,
                "item" => $item,
                "instant_purchase" => $instant_purchase,
                "sale_price" => $sale_price,
                "is_edit" => $is_edit,
                "errors" => $errors,
                "categories" => $categories,
                "checked_categories" => $checked_categories,
                "user_research" => $user_research,
                "source" => $source
            ]
        );
    }

    public function open() : void {
        $user = $this->get_user_or_false();
        $item_id =  (isset($_GET['param1'])) ? (int)$_GET['param1'] : 0;
        $item = Item::get_item_by_id($item_id);
        $source = (isset($_GET['param2'])) ? (string)$_GET['param2'] : "from-browse";
        $user_research = isset($_GET['param3']) ? (string)$_GET['param3']: null;
        $back_url = $this->get_back_url($source, $user_research);
        $image_index = (isset($_GET['param3'])) ? (int)$_GET['param3'] : 0;

        if(!$item){
            Tools::abort("The requested item could not be found. It may have been deleted, or the link is incorrect.");
        }
        $seller = User::get_user_by_id($item->get_owner());
        $bids = Bid::get_bids_by_item($item->get_id());
        $item_cat = Categories::get_categories_for_item($item_id);

        $main_image_path = 'ressources/default_item_picture.jpg';
        $pictures = $item->get_pictures();
        if(!empty($pictures)){
            $idx = isset($pictures[$image_index]) ? $image_index : 0;
            $main_image_path = $pictures[$idx]['picture_path'];
        }
        
        $current_price = (count($bids) > 0) ? $bids[0]->get_amount() : $item->get_starting_bid();

        $winner_id = null;
        $winner_pseudo = null;
        if(count($bids) > 0){
            $winner_id = $bids[0]->get_owner();
        }
        $now = new DateTime(AppTime::get_current_datetime());
        $end = $item->get_end_at();
        $is_finished = ($now > $end) || $item->get_buy_now_reached();
        $is_owner = ($user && $user->get_id() === $item->get_owner());
        $is_highest_bidder = ($user && $winner_id === $user->get_id());

        (new View("open_item"))->show(
            [
                "user" => $user,
                "source" => $source,
                "research" => $user_research,
                "back_url" => $back_url,
                "item" => $item,
                "bids" => $bids,
                "seller" => $seller,                
                "image_index" => $image_index,  
                "main_image_path" => $main_image_path,
                "current_price" => $current_price,
                "is_finished" => $is_finished,
                "is_highest_bidder" => $is_highest_bidder,
                "is_owner" => $is_owner,
                "winner_pseudo" => $winner_pseudo,
                "categories" => $item_cat
            ]
        );
    }

    public function bid() : void {
        $user = $this->get_user_or_redirect();
        $item_id = (int)($_POST['item_id'] ?? 0);
        $item = Item::get_item_by_id($item_id);

        if (!$item) {
            Tools::abort("The requested item could not be found. It may have been deleted, or the link is incorrect.");
        } elseif ($item->get_buy_now_reached() || new DateTime(AppTime::get_current_datetime()) > $item->get_end_at()) {
            Tools::abort("The listing for this item has ended and is no longer active.");
        } elseif ($item->get_owner() == $user->get_id()) {
            Tools::abort("You cannot perform this action on your own item.");
        }

        $bid = new Bid($user->get_id(), $item_id, new DateTime(), (float)($_POST['bid_amount'] ?? 0), $user->get_pseudo());
        $errors = $bid->validate($user->get_id());
        if(count($errors) > 0){
            $this->redirect("items", "open", $item_id);
        }else{
            $bid->persist();
            $this->redirect("items", "open", $item_id);
        }
    }


    public function buy() : void {
        $user = $this->get_user_or_redirect();

        if (isset($_POST['item_id'])) {
            $item_id = (int)$_POST['item_id'];
            $item = Item::get_item_by_id($item_id);

            if (!$item) {
                Tools::abort("The requested item could not be found. It may have been deleted, or the link is incorrect.");
            } elseif ($item->get_buy_now_reached() || new DateTime(AppTime::get_current_datetime()) > $item->get_end_at()) {
                Tools::abort("The listing for this item has ended and is no longer active.");
            } elseif ($item->get_owner() == $user->get_id()) {
                Tools::abort("You cannot perform this action on your own item.");
            } else {
                $bid = new Bid(
                $user->get_id(),
                $item_id,
                new DateTime(AppTime::get_current_datetime()),
                (float)$item->get_buy_now_price(),
                $user->get_pseudo()
                );

                $bid->persist();
                $this->redirect("items", "open", $item_id);
            }
        } else {
            $this->redirect("items");
        }
    }
    
    public function delete_confirm(): void {
        $user = $this->get_user_or_redirect();
        $item_id = null;
        
        if (isset($_GET['param1'])) {
            $item_id = (int)$_GET['param1'];
            $source = (isset($_GET['param2'])) ? (string)$_GET['param2'] : "from-my-items";
            $user_research = isset($_GET['param3']) ? (string)$_GET['param3']: null;
            $item = Item::get_item_by_id($item_id);
            $bids = Bid::get_bids_by_item($item_id);

            if (!$item || $item->get_owner() != $user->get_id() || count($bids) > 0) {
                $this->redirect("items", "open", $item_id);
            }

            (new View("delete_confirm"))->show(
                [
                    "user" => $user,
                    "source" => $source,
                    "user_research" => $user_research,
                    "item" => $item
                ]
            );
        } else {
            $this->redirect("items", "index");
        }


    }

    public function delete(): void {
        $user = $this->get_user_or_redirect();

        $item_id = (int)($_POST['item_id'] ?? 0);
        $user_research = isset($_POST['user_research']) ? (string)$_POST['user_research']: null;
        
        $item = Item::get_item_by_id($item_id);
        $bids = Bid::get_bids_by_item($item_id);

        if ($item && $item->get_owner() == $user->get_id() && count($bids) == 0) {
            $item->delete();
        }
        if($user_research != null) {
            $this->redirect("items", "my_items", $user_research);
        } else {
            $this->redirect("items", "my_items");
        }
        
    }

    public function manage_images(): void {
        $user = $this->get_user_or_redirect();
        $item_id = (isset($_GET['param1'])) ? (int)$_GET['param1'] : 0;
        $source = (isset($_GET['param2'])) ? (string)$_GET['param2'] : "from-my-items";
        $user_research = isset($_GET['param3']) ? (string)$_GET['param3']: null;
        
        
        $item = Item::get_item_by_id($item_id);
        $bids = Bid::get_bids_by_item($item_id);

        if (!$item || $item->get_owner() != $user->get_id() || count($bids) > 0) {
            $this->redirect("items", "open", $item_id);
        }

        (new View("manage_images"))->show(
            [
                "user" => $user,
                "source" => $source,
                "user_research" => $user_research,
                "item" => $item
            ]
        );
    }

    public function move_image(): void {
        $user = $this->get_user_or_redirect();
        
        $item_id = (int)($_POST['item_id'] ?? 0);
        $current_prio = (int)($_POST['priority'] ?? 0);
        $direction = $_POST['direction'] ?? ''; 
        $source = (string)$_POST['source'] ?? "from-my-items";

        $item = Item::get_item_by_id($item_id);

        if ($item && $item->get_owner() == $user->get_id()) {
            $item->swap_pictures($current_prio, $direction);
        }
        $this->redirect("items", "manage_images", $item_id, $source);
    }

    public function upload_image(): void {
        $user = $this->get_user_or_redirect();

        $item_id = (int)($_POST['item_id'] ?? 0);
        $item = Item::get_item_by_id($item_id);
        $source_url = (string)$_POST['source'] ?? "from-my-items";
        $user_research = (string)$_POST['user_research'] ?? null;

        if (!$item || $item->get_owner() != $user->get_id()) {
            $this->redirect("items", "index");
        }

        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image_file'];
            $errors = ItemPictures::validate($file);

            if (empty($errors)) {
                $upload_dir = "uploads/items/" . $item_id;
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $filename = uniqid('img_'); 
                
                if (PictureService::upload_item_picture($file, $upload_dir, $filename)) {
                    $db_path = $upload_dir . "/" . $filename . ".jpg";
                    $item->add_picture($db_path); 
                }
            }
        }
        $this->redirect("items", "manage_images", $item_id, $source_url, $user_research);
    }
    
    public function delete_image(): void {
        $user = $this->get_user_or_redirect();
        
        $item_id = (int)($_POST['item_id'] ?? 0);
        $priority = (int)($_POST['priority'] ?? 0);
        $source = (string)$_POST['source'] ?? "from-my-items";
        $user_research = (string)$_POST['user_research'] ?? null;

        $item = Item::get_item_by_id($item_id);
        
        if ($item && $item->get_owner() == $user->get_id()) {
            $item->delete_picture_by_prio($priority);
        }
        if($user_research != null) {
            $this->redirect("items", "manage_images", $item_id, $source, $user_research);
        } else {
            $this->redirect("items", "manage_images", $item_id, $source);
        }
        
    }

    public function update_priorities_service(): void {
        $user = $this->get_user_or_false();
        $data = json_decode(file_get_contents('php://input'), true);
        
        if ($user && isset($data['item_id']) && isset($data['order'])) {
            $item_id = (int)$data['item_id'];
            $order = $data['order']; 
            
            $item = Item::get_item_by_id($item_id);
            
            if ($item && $item->get_owner() == $user->get_id()) {
                foreach ($order as $index => $picture_path) {
                    ItemPictures::update_priority($item_id, $picture_path, -($index + 1));
                }
                
                foreach ($order as $index => $picture_path) {
                    ItemPictures::update_priority($item_id, $picture_path, $index);
                }
                
                echo json_encode(["success" => true]);
                return;
            }
        }
        
        http_response_code(403);
        echo json_encode(["success" => false, "error" => "Unauthorized or bad request"]);
    }

    private function get_back_url(string $source, ?string $user_research = null) : string {
        return match($source) {
            "from-browse" => "items/display" . "/" . $user_research,
            "from-my-items" => "items/my_items" . "/" . $user_research,
            "from-sales" => "user/sales" . "/" . $user_research,
            "from-purchases" => "user/purchases" . "/" . $user_research,
            default => "items/display" . "/" . $user_research
        };
    }

    public function get_categoriesList_service(): void {
        $categoriesObjects = Categories::get_all_categories();

        $categoriesList = [];
        foreach ($categoriesObjects as $category) {
            $categoriesList[] = [
                "id" => $category->get_id(),
                "name" => $category->get_name()
            ];
        }
        header('Content-Type: application/json');
        echo json_encode(["categoriesList" => $categoriesList]);

    }
    
    public function search_service(): void { 
        $user = $this->get_user_or_false();
        $user_input = $_POST["entry"] ?? "";
        $selected_category = $_POST["categoryId"] != "" ? (int)$_POST["categoryId"] : null;
        $source = $_POST["source"];
        $user_id = $user ? $user->get_id() : null;
        $searchState = ["user_input" => $user_input, "selected_category" => $selected_category];
        $encoded_entry = Encoder::url_safe_encode($searchState);
        $itemsParticipated = [];
        $otherItems = [];

        if ($source == "from-browse") {
            $items = Item::get_all_items_view($user_id, $user_input, $selected_category);

            if ($user) {
                $userbids = Bid::get_user_bids_by_id($user->get_id());
                foreach ($items as $item) {
                    $highestBidByItem = Bid::get_winning_bid_by_item_id($item->get_id());

                    if (in_array($item->get_id(), $userbids)) {
                        $item->set_hasBidByUser(true);

                        if ($highestBidByItem && $highestBidByItem->get_owner() == $user->get_id()) {
                            $item->set_isHighestBidder(true);
                        }
                        $itemsParticipated[] = $item;
                    } else {
                        $otherItems[] = $item;
                    }
                }
            } else {
                $otherItems = $items;
            }
            echo json_encode(["participated" => $itemsParticipated, "others" => $otherItems, "encoded" => $encoded_entry]);

        } else {
            $userItems = Item::get_users_active_items_by_id($user->get_id(), $user_input, $selected_category);
            $userNonActiveItems = Item::get_users_unactive_unsold_items_by_id($user->get_id(), $user_input, $selected_category);
            $userSoldedItems = Item::get_users_solded_items_by_id($user->get_id(), $user_input, $selected_category);

            echo json_encode(["userItems" => $userItems,
                            "userNonActiveItems" => $userNonActiveItems, 
                            "userSoldedItems" => $userSoldedItems, 
                            "encoded" => $encoded_entry]);
        }
    }

    public function delete_image_service(): void {
        $user = $this->get_user_or_false();
        $data = json_decode(file_get_contents('php://input'), true);

        if ($user && isset($data['item_id']) && isset($data['priority'])) {
            $item_id = (int)$data['item_id'];
            $priority = (int)$data['priority'];

            $item = Item::get_item_by_id($item_id);

            if ($item && $item->get_owner() == $user->get_id()) {
                $item->delete_picture_by_prio($priority);
                echo json_encode(["success" => true]);
                return;
            }
        }
        
        http_response_code(403);
        echo json_encode(["success" => false, "error" => "Unauthorized"]);
    }

    public function get_rules_service() : void {
        $rules =
        [
            "title" =>
            [
                "min" => Configuration::get("title_min_length"),
                "max" => Configuration::get("title_max_length")
            ],
            "description" =>
            [
                "min" => Configuration::get("description_min_length")
            ],
            "durationDays" =>
            [
                "min" => Configuration::get("min_duration_days"),
                "max" => Configuration::get("max_duration_days")
            ],
            "price" =>
            [
                "regex" => Configuration::get("price_format_regex")
            ]
        ];

        echo json_encode($rules);
    }

    public function is_title_unique_service() : void {
        $user = $this->get_user_or_false();
        $id = $user ? $user->get_id() : null;

        $json_data = json_decode(file_get_contents("php://input"), true);
        $title = $json_data["title"];
        $item_id = $json_data["itemId"] !== "" ? (int) $json_data["itemId"] : null;
        $error = Item::validate_unicity_title($title, $id, $item_id);

        header("Content-Type: application/json");
        echo json_encode(
            [
                "isUnique" => empty($error)
            ]);
    }

    public function get_pictures_service(): void {
        $item_id = (isset($_GET['param1'])) ? (int)$_GET['param1'] : 0;
        $item = Item::get_item_by_id($item_id);
        
        header('Content-Type: application/json');
        if ($item) {
            echo json_encode($item->get_pictures());
        } else {
            echo json_encode([]);
        }
    }

    public function decode_url_service(): void {
        $json_data = json_decode(file_get_contents("php://input"), true);
        $encoded_research = $json_data["user_research_encoded"];

        header('Content-Type: application/json');
        if ($encoded_research) {
            echo json_encode(
                [
                    "decoded_research" => Encoder::url_safe_decode($encoded_research)
                ]);
        } else {
            echo json_encode([]);
        }

    }
}
?>