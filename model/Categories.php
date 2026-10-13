<?php

require_once "framework/Model.php";
require_once "utils/AppTime.php";
require_once "model/User.php";

class Categories extends Model {

    public function __construct(
        private int $id,
        private string $name,
        private int $priority,
        private ?int $nb_item_assigned = null
        ) {}

    public function get_id(): int {
        return $this->id;
    }

    public function get_name(): string {
        return $this->name;
    }

    public function set_name(string $new_name): string {
        return $this->name = $new_name;
    }

    public function get_priority(): int {
        return $this->priority;
    }

    public function set_priority(int $new_priority): int {
        return $this->priority = $new_priority;
    }

    public function get_nb_item_assigned() {
        return $this->nb_item_assigned;
    }

    public static function get_categories_for_item(int $item_id) : array {
        $results = [];

        $query = self::execute("SELECT id, name, priority
                                FROM categories
                                RIGHT JOIN item_categories ON item_categories.category = id
                                WHERE item = :item_id
                                ORDER BY name;", ["item_id" => $item_id]);
        $data_stream = $query->fetchAll(PDO::FETCH_ASSOC);

        if (empty($data_stream)) return [];

        foreach ($data_stream as $data) {
            $results[] = self::build($data);
        }

        return $results;
    }

    public static function get_id_of_categories_for_item(int $item_id) : array {
        $results = [];

        $query = self::execute("SELECT id
                                FROM categories
                                RIGHT JOIN item_categories ON item_categories.category = id
                                WHERE item = :item_id
                                ORDER BY priority;", ["item_id" => $item_id]);
        $results = $query->fetchAll(PDO::FETCH_COLUMN);

        if (empty($results)) return [];
        
        return $results;
    }

    public static function find_category_by_id(int $category_id) : Categories {
        $query = self::execute("SELECT *
                                FROM categories
                                WHERE id = :id;", ["id" => $category_id]);
        $data = $query->fetch(PDO::FETCH_ASSOC);

        $results = self::build($data);
        return $results;
    }

    public static function find_category_by_prio(int $prio) : Categories {
        $query = self::execute("SELECT * 
                                FROM categories 
                                WHERE priority = :prio;", ["prio" => $prio]);
        $data = $query->fetch(PDO::FETCH_ASSOC);

        $results = self::build($data);
        return $results;
    }

    public static function get_all_categories() : array {
        $results = [];

        $query = self::execute("SELECT *
                                FROM categories
                                ORDER BY priority;", []);
        $data_stream = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data_stream as $data) {
            $results[] = self::build($data);
        }

        return $results;
    }

    public static function get_all_categories_and_count() : array {
        $results = [];

        $query = self::execute("SELECT *, (SELECT count(*)
                                            FROM item_categories
                                            WHERE categories.id = category) as nb_item_assigned
                                FROM categories
                                ORDER BY priority;", []);
        $data_stream = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data_stream as $data) {
            $results[] = self::build($data);
        }

        return $results;
    }

    public static function update_item_categories(int $item_id, array $checked_categories) : void {
        self::delete($item_id);

        foreach($checked_categories as $categories) {
            self::execute("INSERT INTO item_categories (item, category) 
                            VALUES (:item, :category)",
            [
                "item"     => $item_id,
                "category" => $categories
            ]
            );
        }
    }

    public function update_categories() : Categories {
            self::execute("UPDATE categories 
                            SET name = :new_name 
                            WHERE id = :id", 

            [
                "id"       => $this->id, 
                "new_name" => $this->name
            ]
        );
        return $this;
    }

        public function update_categories_priority() : Categories {
            self::execute("UPDATE categories 
                            SET priority = :new_priority 
                            WHERE id = :id", 

            [
                "id"       => $this->id, 
                "new_priority" => $this->priority
            ]
        );
        return $this;
    }

    public static function delete(int $item_id) : void {
        self::execute("DELETE 
                        FROM item_categories 
                        WHERE item = (SELECT id
                                        FROM items 
                                        WHERE id = :id);", ["id" => $item_id]);
    }

    public function delete_category() : void {
        $category_priority = $this->priority;
        self::execute("DELETE
                        FROM categories
                        WHERE id = :id;", ["id" => $this->id]);
        
        self::execute("UPDATE categories
                        SET priority = priority - 1
                        WHERE priority > :priority;", ["priority" => $category_priority]);
    }

    public function is_empty() : bool {
        $results = [];
        $query = self::execute("SELECT *
                                FROM item_categories
                                WHERE category = :id", ["id" => $this->id]);
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        return empty($results);
    }

    public static function swap(Categories $category_A, Categories $category_B) : void {
        $prio_cat_a = $category_A->get_priority();
        $prio_cat_b = $category_B->get_priority();

        self::execute("UPDATE categories SET priority = -1 WHERE id = :id", ["id" => $category_A->get_id()]);
        self::execute("UPDATE categories SET priority = :prio WHERE id = :id", ["id" => $category_B->get_id(), "prio" => $prio_cat_a]);
        self::execute("UPDATE categories SET priority = :prio WHERE id = :id", ["id" => $category_A->get_id(), "prio" => $prio_cat_b]);
    }

    public static function assign_categories(int $item_id, array $checked_categories) : void {

        foreach($checked_categories as $category) {
            self::execute("INSERT INTO item_categories (item, category) 
                VALUES (:item, :category)",
                [
                    "item"     => $item_id,
                    "category" => $category
                ]
            );
        }
    }

    public function validate_category_name(): array {
        $errors = [];

        $name_min_length = Configuration::get("category_name_min_length");
        $name_max_length = Configuration::get("category_name_max_length");

        if (empty($this->name) || strlen($this->name) <  $name_min_length || strlen($this->name) > $name_max_length) {
            $errors[$this->id] = "The name of the category must be between $name_min_length and $name_max_length characters.";
        } else {
            $unicity_error = self::validate_unicity_category_name($this->name, $this->id);
            if ($unicity_error) $errors[$this->id] = $unicity_error;
        }
        return $errors;
    }

    public static function validate_unicity_category_name(string $name, ?int $id = null) : ?string {
        $existing_categories = self::get_all_categories();

        foreach ($existing_categories as $category) {
            if ($id === null || $category->get_id() != $id) {
                if (strtolower($category->get_name()) == strtolower($name)) {
                    return "An category with this name already exists.";
                }
            }
        }
        return null;
    }

    public function persist() : Categories {
        self::execute("INSERT INTO categories (name, priority) 
                        VALUES (:name, :priority)",
            [
                "name"     => $this->name,
                "priority" => $this->priority
            ]
        );
        $this->id = self::lastInsertId();
        return $this;
    }

    private static function build(array $data) : Categories {
        return new Categories(
            $data["id"],
            $data["name"],
            $data["priority"],
            $data["nb_item_assigned"] ?? null
        );
    }
}