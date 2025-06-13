<?php
class Config {
    private static $settings = [
        'app_name' => 'Minha Aplicação',
        'base_url' => null,
        'environment' => 'development',
        'debug' => true
    ];

    // Detectar base URL
    public static function getBaseUrl() {
        if (self::$settings['base_url'] === null) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $scriptName = $_SERVER['SCRIPT_NAME'];
            $baseDir = str_replace('/index.php', '', $scriptName);
            self::$settings['base_url'] = $protocol . '://' . $host . $baseDir;
        }
        return self::$settings['base_url'];
    }

    // Gerar URL
    public static function url($path = '') {
        $baseUrl = self::getBaseUrl();
        $path = ltrim($path, '/');
        return $baseUrl . '/' . $path;
    }

    public static function get($key) {
        return self::$settings[$key] ?? null;
    }

    public static function set($key, $value) {
        self::$settings[$key] = $value;
    }
}

function base_url($path = '') {
    return Config::url($path);
}

function redirect($path, $params = []) {
    $url = base_url($path);
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    header("Location: $url");
    exit();
}

function getParam($key, $default = null) {
    return isset($_GET[$key]) ? htmlspecialchars($_GET[$key], ENT_QUOTES, 'UTF-8') : $default;
}

function getAllParams() {
    return array_map(function($value) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }, $_GET);
}

function asset($path) {
    return base_url('assets/' . ltrim($path, '/'));
}
?>