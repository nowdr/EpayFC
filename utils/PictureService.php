<?php

require_once "framework/Configuration.php";

class PictureService {

    public function validate(array $profile_picture) : array {
        $errors = [];

        if ($profile_picture["error"] !== UPLOAD_ERR_OK) {
            $errors[] = "An error occured during the file upload.";
            return $errors;
        }

        $max_size = Configuration::get("max_file_size_upload");
        
        if ($profile_picture["size"] > $max_size) {
            $errors[] = "The file is too large. Maximum size allowed is " . ($max_size/1048576) . "MB.";
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($profile_picture["tmp_name"]);

        $mime_map = [
            "image/jpeg" => "jpg",
            "image/png" => "png",
            "image/gif" => "gif",
            "image/webp" => "webp"
        ];
        
        $supported_formats = Configuration::get("supported_formats");
        $allowed_extensions = explode(",", $supported_formats);

        if (isset($mime_map[$mime_type])) {
            $extension = $mime_map[$mime_type];
            if (!in_array($extension, $allowed_extensions)) {
                $errors[] = "Format not supported. (Allowed : " . str_replace(",", ", ", $supported_formats) . ").";
            }
        } else {
            $errors[] = "The file is not a valid or recognized image.";
        }

        return $errors;
    }

    public function upload(User $user, array $profile_picture) : void {
        $storage_path = str_replace("%ID%", $user->get_id(), Configuration::get("storage_path_users"));

        $this->clear_directory($storage_path);
        
        if (!is_dir($storage_path)) {
            mkdir($storage_path, 0755, true);
        }

        $source_image = @imagecreatefromstring(file_get_contents($profile_picture["tmp_name"]));

        if ($source_image) {
            $source_width = imagesx($source_image);
            $source_height = imagesy($source_image);
            $base_name = "profile";

            [$main_width, $main_height] = $this->calculate_dimensions(
                $source_width, 
                $source_height, 
                Configuration::get("main_image_width"), 
                Configuration::get("main_image_height")
            );
            
            $main_path = $storage_path . $base_name . ".jpg";
            
            $this->process_and_save(
                $source_image, 
                $source_width, 
                $source_height, 
                $main_width, 
                $main_height, 
                $main_path, 
                90
            );

            [$thumb_width, $thumb_height] = $this->calculate_dimensions(
                $source_width, 
                $source_height, 
                Configuration::get("thumbnail_width"), 
                Configuration::get("thumbnail_height")
            );
            
            $thumb_path = $storage_path . $base_name . Configuration::get("thumbnail_suffix") . ".jpg";

            $this->process_and_save(
                $source_image, 
                $source_width, 
                $source_height, 
                $thumb_width, 
                $thumb_height, 
                $thumb_path, 
                80
            );

            $user->update_profile_picture($main_path);
        }
    }

    public function delete(User $user) : void {
        $storage_path = str_replace("%ID%", $user->get_id(), Configuration::get("storage_path_users"));
        $this->clear_directory($storage_path);
        
        if (is_dir($storage_path)) {
            rmdir($storage_path);
        }
        
        $user->update_profile_picture("ressources/default_profile_picture.jpg");
    }

    private function clear_directory(string $storage_path) : void {        
        if (is_dir($storage_path)) {
            $files = glob($storage_path . "*");
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }

    private function calculate_dimensions(int $source_width, int $source_height, int $max_width, int $max_height) : array {
        $ratio = min($max_width / $source_width, $max_height / $source_height);
        
        if ($ratio > 1) {
            $ratio = 1;
        }

        return [round($source_width * $ratio), round($source_height * $ratio)];
    }

    private function process_and_save(GdImage $source_image, int $source_width, int $source_height, int $target_width, int $target_height, string $destination_path, int $quality) : void {
        $new_image = imagecreatetruecolor($target_width, $target_height);
        
        $white = imagecolorallocate($new_image, 255, 255, 255);
        imagefilledrectangle($new_image, 0, 0, $target_width, $target_height, $white);

        imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $target_width, $target_height, $source_width, $source_height);

        imagejpeg($new_image, $destination_path, $quality);
    }

   public static function upload_item_picture(array $file, string $dest_dir, string $name): bool {
        $source = imagecreatefromstring(file_get_contents($file['tmp_name']));
        if (!$source) return false;

        list($width, $height) = getimagesize($file['tmp_name']);
        
        $sizes = [
            "" => Configuration::get("main_image_width") ?? 1080, 
            Configuration::get("thumbnail_suffix") ?? "_thumbnail" => Configuration::get("thumbnail_width") ?? 360
        ];

        foreach ($sizes as $suffix => $max_size) {
            $ratio = min($max_size / $width, $max_size / $height);
            if ($ratio > 1) $ratio = 1;
            
            $new_width = round($width * $ratio);
            $new_height = round($height * $ratio);

            $dst = imagecreatetruecolor($new_width, $new_height);
            
            $white = imagecolorallocate($dst, 255, 255, 255);
            imagefill($dst, 0, 0, $white);

            imagecopyresampled($dst, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

            $full_path = $dest_dir . "/" . $name . $suffix . ".jpg";
            imagejpeg($dst, $full_path, 90); 
        }
        
        return true;
    }

    public static function delete_picture_files(string $path): void {
        if (file_exists($path)) {
            unlink($path);
        }
        
        $thumb = str_replace(".jpg", "_thumbnail.jpg", $path);
        if (file_exists($thumb)) {
            unlink($thumb);
        }
    }
}