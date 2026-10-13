<?php

require_once 'View.php';

class Tools {

    //nettoie le string donné
    public static function sanitize(string $var): string {
        return trim(htmlspecialchars($var, ENT_QUOTES, "UTF-8"));
    }

    //dirige vers la page d'erreur
    public static function abort(string $err): void {
        http_response_code(500);
        (new View("error"))->show(array("error" => $err));
        die;
    }

    private static function base64url_encode(string $data) : string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64url_decode(string $data) : string {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }

    public static function url_safe_encode(mixed $data) : string {
        return self::base64url_encode(gzcompress(json_encode($data), 9));
    }

    public static function url_safe_decode(string $data) : mixed {
        return json_decode(@gzuncompress(self::base64url_decode($data)), true, 512, JSON_OBJECT_AS_ARRAY);
    }
}
