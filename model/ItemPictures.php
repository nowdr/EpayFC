<?php
require_once "framework/Model.php";

class ItemPictures extends Model {

    public function __construct(
        private int $item,
        private int $priority,
        private string $picture_path
    ) {}

    public function get_item() {
        return $this->item;
    }

    public function get_priority() {
        return $this->priority;
    }

    public function get_picture_path() {
        return $this->picture_path;
    }

    public function delete() : void {
        $this->delete_file_system();
        $this->delete_db();

    }

    private function delete_db() {
    self::execute("DELETE FROM item_pictures WHERE item = :item AND priority = :priority", [
            "item" => $this->item,
            "priority" => $this->priority
        ]);
    }

    private function delete_file_system(){
        if(file_exists($this->picture_path)){
            unlink($this->picture_path);
        }
        $path_info = pathinfo($this->picture_path);
        $thumbnail_path = $path_info['dirname'] . "/" . $path_info['filename'] . "_thumbnail." . $path_info['extension'];

        if(file_exists($thumbnail_path)){
            unlink($thumbnail_path);
        }
    }

    public function save(){
        self::execute("INSERT INTO item_pictures (item, priority, picture_path) 
                       VALUES (:item, :prio, :path)", [
            "item" => $this->item,
            "prio" => $this->priority,
            "path" => $this->picture_path
        ]);
        return $this;
    }

    public static function validate(array $file): array {
        $errors = [];
        
        if ($file['size'] > Configuration::get("max_file_size_upload")) {
            $errors[] = "The file is too large.";
        }

        $allowed_extensions = explode(',', Configuration::get("supported_formats"));
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_extensions)) {
            $errors[] = "Format not supported. Allowed: " . Configuration::get("supported_formats");
        }

        return $errors;
    }

    public static function update_priority(int $item, string $picture_path, int $new_priority): void {
        self::execute("UPDATE item_pictures SET priority = :priority WHERE item = :item AND picture_path = :picture_path", [
            "priority" => $new_priority,
            "item" => $item,
            "picture_path" => $picture_path
        ]);
    }
}