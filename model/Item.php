<?php

use LDAP\Result;

require_once "framework/Model.php";
require_once "utils/AppTime.php";
require_once "model/User.php";

class Item extends Model implements JsonSerializable {

    public function __construct(
        private string $title,
        private int $owner,
        private int $duration_days,
        private DateTime $created_at, 
        private ?string $description = null,
        private ?float $buy_now_price = null,
        private ?float $starting_bid = 00.00,
        private ?int $id = null,
        private int $bid_count = 0,
        private int $is_direct_sale = 0,
        private int $is_auction = 0,
        private int $has_buy_now = 0,
        private int $has_bids = 0,
        private int $buy_now_reached = 0,
        private int $not_purchase_direct_sale = 0,
        private string $owner_pseudo = "", 
        private string $remaining_time = "",
        private array $pictures = [],
        private ?DateTime $end_at = null,
        private ?float $max_bid = NULL,
        private bool $hasBidByUser = false,
        private bool $isHighestBidder = false 
        ) {}

    public function get_title(): string {
        return $this->title;
    }

    public function get_owner(): int {
        return $this->owner;
    }

    public function get_duration_days(): int {
        return $this->duration_days;
    }

    public function get_created_at(): DateTime {
        return $this->created_at;
    }

    public function get_description(): ?string {
        return $this->description;
    }

    public function get_buy_now_price(): ?float {
        return $this->buy_now_price;
    }

    public function get_starting_bid(): ?float {
        return $this->starting_bid;
    }

    public function get_id(): ?int {
        return $this->id;
    }

    public function get_bid_count(): int {
        return $this->bid_count;
    }

    public function get_is_direct_sale(): int {
        return $this->is_direct_sale;
    }

    public function get_is_auction(): int {
        return $this->is_auction;
    }

    public function get_has_buy_now(): int {
        return $this->has_buy_now;
    }

    public function get_has_bids(): int {
        return $this->has_bids;
    }

    public function get_buy_now_reached(): int {
        return $this->buy_now_reached;
    }

    public function get_not_purchase_direct_sale(): int {
        return $this->not_purchase_direct_sale;
    }

    public function get_owner_pseudo(): string {
        return $this->owner_pseudo;
    }

    public function get_remaining_time(): string {
        return $this->remaining_time;
    }

    public function get_pictures(): array {
        return $this->pictures;
    }

    public function get_end_at(): ?DateTime {
        return $this->end_at;
    }

    public function get_max_bid(): ?float {
        return $this->max_bid;
    }

    public function get_hasBidByUser(): bool {
        return $this->hasBidByUser;
    }

    public function get_isHighestBidder(): bool {
        return $this->isHighestBidder;
    }

    public function set_title(string $title): void {
        $this->title = $title;
    }

    public function set_description(?string $description): void {
        $this->description = $description;
    }

    public function set_duration_days(int $duration_days): void {
        $this->duration_days = $duration_days;
    }

    public function set_starting_bid(?float $starting_bid): void {
        $this->starting_bid = $starting_bid;
    }

    public function set_buy_now_price(?float $buy_now_price): void {
        $this->buy_now_price = $buy_now_price;
    }

    public function set_created_at(DateTime $created_at): void {
        $this->created_at = $created_at;
    }

    public function set_hasBidByUser(bool $hasBidByUser): void {
        $this->hasBidByUser = $hasBidByUser;
    }

    public function set_isHighestBidder(bool $isHighestBidder): void {
        $this->isHighestBidder = $isHighestBidder;
    }

    public function jsonSerialize(): array {
        return [
            "id" => $this->get_id(),
            "title" => $this->get_title(),
            "description" => $this->get_description(),
            "owner_pseudo" => $this->get_owner_pseudo(),
            "remaining_time" => $this->get_remaining_time(),

            "is_auction" => $this->get_is_auction(),
            "has_buy_now" => $this->get_has_buy_now(),
            "has_bids" => $this->get_has_bids(),

            "starting_bid" => $this->get_starting_bid(),
            "buy_now_price" => $this->get_buy_now_price(),
            "max_bid" => $this->get_max_bid(),

            "pictures" => $this->get_pictures(),

            // champs “UI”
            "hasBidByUser" => $this->get_hasBidByUser(),
            "isHighestBidder" => $this->get_isHighestBidder(),
        ];
    }

    public static function get_all_items_view(?int $user_id = null, ?string $user_research = null, ?int $selected_category = null) : array {
        $search_pattern = "%" . $user_research . "%";
        $search_pattern_cat = "%" . $selected_category . "%";
        $results = [];

        if($user_id == null){
            //query excluant les items de la personne connectée
            $query = self::execute("SELECT v_items_status.*, users.pseudo
                                FROM v_items_status
                                LEFT JOIN users ON v_items_status.owner = users.id
                                WHERE end_at > :now AND buy_now_reached = 0 AND (title LIKE :search OR description LIKE :search OR users.pseudo LIKE :search) 
                                    AND v_items_status.id IN (SELECT item 
                                                                FROM item_categories 
                                                                WHERE category LIKE :search_cat)
                                ORDER BY end_at;", ["now" => AppTime::get_current_datetime(), "search" => $search_pattern, "search_cat" => $search_pattern_cat]);
            $datas = $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            //query prenant tous les items vu que personne n'est connecté
            $query = self::execute("SELECT v_items_status.*, users.pseudo
                                FROM v_items_status
                                LEFT JOIN users ON v_items_status.owner = users.id
                                WHERE end_at > :now AND buy_now_reached = 0 AND v_items_status.owner != :user_id AND (title LIKE :search OR description LIKE :search OR users.pseudo LIKE :search)
                                    AND v_items_status.id IN (SELECT item 
                                                                FROM item_categories 
                                                                WHERE category LIKE :search_cat)
                                ORDER BY end_at;", ["now" => AppTime::get_current_datetime(), "user_id" => $user_id, "search" => $search_pattern, "search_cat" => $search_pattern_cat]);
            $datas = $query->fetchAll(PDO::FETCH_ASSOC);
        }
        

        if (empty($datas)) return [];

        // Récupérer toutes les images pour ces items d'un seul coup
        $ids = array_column($datas, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $imgQuery = self::execute("SELECT * FROM item_pictures WHERE item IN ($placeholders) ORDER BY priority ASC", $ids);
        $allPictures = $imgQuery->fetchAll(PDO::FETCH_ASSOC);

        // Organiser les images par ID d'item
        $picturesByItem = [];
        foreach ($allPictures as $img) {
            $picturesByItem[$img['item']][] = $img;
        }

        // Construire les objets
        foreach ($datas as $data) {
            // On passe les images déjà extraites au builder
            $itemPictures = $picturesByItem[$data['id']] ?? [];
            $results[] = self::build($data, $itemPictures);
        }
        return $results;
    }

    public static function get_users_active_items_by_id(int $id, ?string $user_research = null, ?int $selected_category = null) : array {
        $search_pattern = "%" . $user_research . "%";
        $search_pattern_cat = "%" . $selected_category . "%";
        $results = [];
        $query = self::execute("SELECT v_items_status.*, users.pseudo
                                FROM v_items_status
                                LEFT JOIN users ON v_items_status.owner = users.id
                                WHERE owner = :id AND end_at > :now AND buy_now_reached = 0 AND (title LIKE :search OR description LIKE :search OR users.pseudo LIKE :search)
                                    AND v_items_status.id IN (SELECT item 
                                                                FROM item_categories 
                                                                WHERE category LIKE :search_cat)
                                GROUP BY v_items_status.id, users.pseudo
                                ORDER BY end_at DESC;", ["id" => $id, "now" => AppTime::get_current_datetime(), "search" => $search_pattern, "search_cat" => $search_pattern_cat]);
        $datas = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($datas as $data){
            $results[] = self::build($data);
        }
        return $results;
    }

    public static function get_users_unactive_unsold_items_by_id(int $id, ?string $user_research = null, ?int $selected_category = null) : array {
        $search_pattern = "%" . $user_research . "%";
        $search_pattern_cat = "%" . $selected_category . "%";
        $results = [];
        $query = self::execute("SELECT v_items_status.*, users.pseudo
                                FROM v_items_status
                                LEFT JOIN users ON v_items_status.owner = users.id
                                WHERE owner = :id AND end_at < :now AND has_bids = 0 AND buy_now_reached = 0 AND (title LIKE :search OR description LIKE :search OR users.pseudo LIKE :search)
                                    AND v_items_status.id IN (SELECT item 
                                                                FROM item_categories 
                                                                WHERE category LIKE :search_cat)
                                GROUP BY v_items_status.id, users.pseudo
                                ORDER BY end_at DESC;", ["id" => $id, "now" =>AppTime::get_current_datetime(), "search" => $search_pattern, "search_cat" => $search_pattern_cat]);
        $datas = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($datas as $data){
            $results[] = self::build($data);
        }
        return $results;
    }

    public static function get_users_solded_items_by_id(int $id, ?string $user_research = null, ?int $selected_category = null) : array {
        $search_pattern = "%" . $user_research . "%";
        $search_pattern_cat = "%" . $selected_category . "%";
        $results = [];
        $query = self::execute("SELECT v_items_status.*, users.pseudo
                                FROM v_items_status
                                LEFT JOIN users ON v_items_status.owner = users.id
                                WHERE owner = :id AND (buy_now_reached = 1 OR (end_at < :now AND has_bids = 1)) AND (title LIKE :search OR description LIKE :search OR users.pseudo LIKE :search)
                                    AND v_items_status.id IN (SELECT item 
                                                                FROM item_categories 
                                                                WHERE category LIKE :search_cat)
                                GROUP BY v_items_status.id, users.pseudo
                                ORDER BY end_at DESC;", ["id" => $id, "now" =>AppTime::get_current_datetime(), "search" => $search_pattern, "search_cat" => $search_pattern_cat]);
        $datas = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($datas as $data){
            $results[] = self::build($data);
        }
        return $results;
    }

    public static function get_item_pictures_by_id(int $id) : array {
        $query = self::execute("SELECT picture_path, priority
                                From item_pictures
                                WHERE item = :id
                                ORDER BY priority ASC", ["id" => $id]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function get_purchased_items_by_user(int $user_id) : array {
        $result = [];
        $query = self::execute("SELECT DISTINCT v.*, u.pseudo
                                FROM v_items_status v
                                JOIN bids b ON v.id = b.item
                                JOIN users u ON v.owner = u.id
                                WHERE b.owner = :user_id
                                AND b.amount = v.max_bid
                                AND (v.buy_now_reached OR v.end_at < :now)
                                ORDER BY v.end_at DESC;"
                                , ["user_id" => $user_id, "now" => AppTime::get_current_datetime()]);

        $items = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($items as $item) {
            $result[] = self::build($item);
        }
        return $result;
    }

    public static function get_closing_dates(array $items_id) : array {
        $result = [];
        if (!empty($items_id)) {
            $sql_items_id = implode(",", array_fill(0, count($items_id), "?"));
            $query = self::execute("SELECT v.id, CASE WHEN v.buy_now_reached THEN MAX(b.created_at) ELSE v.end_at END AS closing_date
                                    FROM v_items_status v
                                    JOIN bids b ON b.item = v.id
                                    WHERE v.id IN ($sql_items_id)
                                    GROUP BY v.id;"
                                    , $items_id);
            $rows = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $result[$row["id"]] = $row["closing_date"]; 
            }
        }
        return $result;
    }

    public static function get_item_by_id(int $id) : ?Item {
        $query = self::execute("SELECT v_items_status.*, users.pseudo
                                FROM v_items_status
                                LEFT JOIN users ON v_items_status.owner = users.id
                                WHERE v_items_status.id = :id", ["id" => $id]);
        $data = $query->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }
        return self::build($data);
    }
    
    public function delete(): void {
        $picturesData = self::get_item_pictures_by_id($this->id);
        foreach ($picturesData as $data) {
            $pic = new ItemPictures($this->id, $data['priority'], $data['picture_path']);
            $pic->delete();
        }
        Categories::delete($this->id);
        self::execute("DELETE FROM items WHERE id = :id", ["id" => $this->id]);
    }

    public function add_picture(string $path): void {
        $query = self::execute("SELECT COALESCE(MAX(priority), -1) + 1 as next_prio 
                                FROM item_pictures WHERE item = :id", ["id" => $this->id]);
        $data = $query->fetch(PDO::FETCH_ASSOC);
        $next_prio = $data['next_prio'];

        self::execute("INSERT INTO item_pictures (item, picture_path, priority) 
                       VALUES (:id, :path, :prio)", [
            "id"   => $this->id,
            "path" => $path,
            "prio" => $next_prio
        ]);
    }

    public static function remaining_time(DateTime $end_at, int $buy_now_reached) : string{
        $start_at = new DateTime(AppTime::get_current_datetime());
        $remaining = $start_at->diff($end_at);

        if ($remaining->invert == 1 || $buy_now_reached == 1){
            return "Closed";
        } elseif ($remaining->days > 0) {
            return $remaining->format("%ad %hh left");
        } else {
            return $remaining->format("%hh %im left");
        }
    }

    public function validate_item_data(int $user_id, ?float $instant_purchase = null, ?float $sale_price = null): array {
        $errors = [];

        $title_min_length = Configuration::get("title_min_length");
        $title_max_length = Configuration::get("title_max_length");
        $min_duration_days = Configuration::get("min_duration_days");
        $max_duration_days = Configuration::get("max_duration_days");
        $description_min_length = Configuration::get("description_min_length");
        $price_regex = "/" . Configuration::get("price_format_regex") . "/";

        if (empty($this->title) || strlen($this->title) < $title_min_length || strlen($this->title) > $title_max_length) {
            $errors["title"] = "Title must be between $title_min_length and $title_max_length characters.";
        } else {
            $unicity_error = self::validate_unicity_title($this->title, $user_id, $this->id);
            if ($unicity_error) $errors["title"] = $unicity_error;
        }

        if ($this->duration_days < $min_duration_days || $this->duration_days > $max_duration_days) {
            $errors["duration_days"] = "Sale Duration must be a whole number between $min_duration_days and $max_duration_days days.";
        }

        if (!empty($this->description) && (strlen($this->description) < $description_min_length)) {
            $errors["description"] = "Description must be at least $description_min_length characters.";
        }

        $has_auction = ($this->starting_bid != 0);
        $has_direct = !empty($sale_price);

        if (!$has_auction && !$has_direct) {
            $errors["starting_bid"] = "Please set either an auction or direct sale amount to continue.";
            $errors["sale_price"] = "Please set either an auction or direct sale amount to continue.";
        }
        // validation enchere
        if ($has_auction) {
            if (!preg_match($price_regex, $this->starting_bid) || $this->starting_bid <= 0) {
                $errors["starting_bid"] = "Starting bid must be a positive number (up to 8 digits) with no more than two decimal places.";
            } elseif ($has_direct) {
                $errors["starting_bid"] = "Cannot list as both an auction and a direct sale simultaneously.";
            }
            // Si achat immediat
            if (!empty($instant_purchase)) {
                if (!preg_match($price_regex, $instant_purchase)) {
                    $errors["instant_purchase"] = "Instant purchase must be a positive number (up to 8 digits) with no more than two decimal places.";
                } elseif ($instant_purchase <= $this->starting_bid) {
                    $errors["instant_purchase"] = "Instant purchase must be higher than starting bid.";
                }
            }
        }
        // validation vente directe
        if ($has_direct) {
            if (!preg_match($price_regex, $sale_price) || $sale_price <= 0) {
                $errors["sale_price"] = "Sale price must be a positive number (up to 8 digits) with no more than two decimal places.";
            } elseif ($has_auction) {
                $errors["sale_price"] = "Cannot list as both an auction and a direct sale simultaneously.";
            } elseif (!empty($instant_purchase)) {
                $errors["instant_purchase"] = "Instant purchase is not available for direct sale items.";
            }
        }

        return $errors;
    }

    public static function validate_unicity_title(string $title, int $user_id, ?int $item_id = null) : ?string {
        $active_items = self::get_users_active_items_by_id($user_id);

        foreach ($active_items as $item) {
            if ($item_id === null || $item->get_id() != $item_id) {
                if (strtolower($item->get_title()) === strtolower($title)) {
                    return "An item with this title already exists.";
                }
            }
        }
        return null;
    }

    public function persist() : Item {
        $createdAtStr = $this->created_at->format('Y-m-d H:i:s');
        self::execute("INSERT INTO items (title, description, owner, created_at, buy_now_price, duration_days, starting_bid) 
            VALUES (:title, :description, :owner, :created_at, :buy_now_price, :duration_days, :starting_bid)",
            [
                "title"         => $this->title,
                "description"   => $this->description,
                "owner"         => $this->owner,
                "created_at"    => $createdAtStr,
                "buy_now_price" => $this->buy_now_price,
                "duration_days" => $this->duration_days,
                "starting_bid"  => $this->starting_bid
            ]
        );
        $this->id = self::lastInsertId();
        return $this;
    }

    public function update() : Item {
        self::execute("UPDATE items SET title = :title,
                                        description = :description, 
                                        buy_now_price = :buy_now_price,
                                        duration_days = :duration_days,
                                        starting_bid = :starting_bid
                    WHERE id = :id", 
            [
                "title"         => $this->title,
                "description"   => $this->description,
                "duration_days" => $this->duration_days,
                "buy_now_price" => $this->buy_now_price,
                "starting_bid"  => $this->starting_bid,
                "id"            => $this->id
            ]
        );
        return $this;
    }

    public function swap_pictures(int $current_prio, string $direction): void {
        $operator = ($direction === 'left' || $direction === 'up') ? '<' : '>';
        $order = ($direction === 'left' || $direction === 'up') ? 'DESC' : 'ASC';
        
        $id = $this->get_id();

        $query = self::execute("SELECT * FROM item_pictures 
                                WHERE item = :id AND priority $operator :prio 
                                ORDER BY priority $order LIMIT 1", 
                                ["id" => $id, "prio" => $current_prio]);
        $neighbor = $query->fetch(PDO::FETCH_ASSOC);

        if ($neighbor) {
            $neighbor_prio = $neighbor['priority'];
            self::execute("UPDATE item_pictures SET priority = -1 WHERE item = :id AND priority = :prio", ["id" => $id, "prio" => $current_prio]);
            self::execute("UPDATE item_pictures SET priority = :p1 WHERE item = :id AND priority = :p2", ["id" => $id, "p1" => $current_prio, "p2" => $neighbor_prio]);
            self::execute("UPDATE item_pictures SET priority = :p2 WHERE item = :id AND priority = -1", ["id" => $id, "p2" => $neighbor_prio]);
        }
    }

    public function delete_picture_by_prio(int $priority): void {
        $query = self::execute("SELECT picture_path FROM item_pictures WHERE item = :id AND priority = :prio", 
                                ["id" => $this->id, "prio" => $priority]);
        $pic = $query->fetch(PDO::FETCH_ASSOC);

        if ($pic) {
            PictureService::delete_picture_files($pic['picture_path']);
            self::execute("DELETE FROM item_pictures WHERE item = :id AND priority = :prio", 
                        ["id" => $this->id, "prio" => $priority]);
        }
    }

    private static function build(array $data, ?array $pictures = null) : Item {

        if ($pictures === null) {
            $pictures = self::get_item_pictures_by_id($data["id"]);
        }

        if (empty($pictures)) {
            $pictures = [[
                "picture_path" => "ressources/default_item_picture.jpg",
                "priority" => 0
                ]];
        }

        $remaining_time = self::remaining_time(new DateTime($data["end_at"]), (int)$data["buy_now_reached"]);
        return new Item(
            $data["title"],
            $data["owner"],
            $data["duration_days"],
            new DateTime($data["created_at"]),
            $data["description"],
            $data["buy_now_price"],
            $data["starting_bid"],
            $data["id"],
            $data["bid_count"],
            $data["is_direct_sale"],
            $data["is_auction"],
            $data["has_buy_now"],
            $data["has_bids"],
            $data["buy_now_reached"],
            $data["not_purchased_direct_sale"],
            $data["pseudo"],
            $remaining_time,
            $pictures,
            new DateTime($data["end_at"]),
            $data["max_bid"],
            false,
            false
        );
    }
}