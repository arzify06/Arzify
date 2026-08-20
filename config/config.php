<?php
/**
 * Desire Travel - System Configuration
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Base Application Constants
define('APP_NAME', 'Desire Travel');
define('APP_TAGLINE', 'Premium Intercity Bus Fleet & Ticketing Portal');
define('APP_VERSION', '2.0.0');

// Base Paths & URLs
define('BASE_DIR', dirname(__DIR__));

// Determine current URL path dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = preg_replace('#/(admin|employee|includes|helpers|config|assets)(/.*)?$#i', '', $scriptDir);
$baseUrl = rtrim($protocol . $host . $basePath, '/') . '/';
define('BASE_URL', $baseUrl);

// Database Configurations
define('DB_HOST', 'localhost');
define('DB_PORT', 8080); // Default XAMPP custom port, fallback to 3306 automatically
define('DB_NAME', 'desire_travel_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Available System Themes
$AVAILABLE_THEMES = [
    'theme-navy'    => ['name' => 'Royal Navy', 'color' => '#1e3c72', 'accent' => '#2a5298'],
    'theme-emerald' => ['name' => 'Emerald Green', 'color' => '#0f766e', 'accent' => '#14b8a6'],
    'theme-crimson' => ['name' => 'Luxury Crimson', 'color' => '#881337', 'accent' => '#e11d48'],
    'theme-dark'    => ['name' => 'Cyber Dark', 'color' => '#0f172a', 'accent' => '#38bdf8'],
    'theme-amber'   => ['name' => 'Sunset Amber', 'color' => '#c2410c', 'accent' => '#f97316'],
];

// Available System Languages
$AVAILABLE_LANGS = [
    'en' => ['name' => 'English', 'code' => 'en', 'flag' => '🇬🇧'],
    'gu' => ['name' => 'ગુજરાતી (Gujarati)', 'code' => 'gu', 'flag' => '🇮🇳'],
];

// Handle Active Theme in Session / Cookie
if (isset($_GET['set_theme']) && array_key_exists($_GET['set_theme'], $AVAILABLE_THEMES)) {
    $_SESSION['app_theme'] = $_GET['set_theme'];
    setcookie('desire_theme', $_GET['set_theme'], time() + (86400 * 30), "/");
    // Redirect back to clean URL
    $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?');
    $query = $_GET;
    unset($query['set_theme']);
    if (!empty($query)) {
        $redirectUrl .= '?' . http_build_query($query);
    }
    header("Location: " . $redirectUrl);
    exit;
}

$CURRENT_THEME = $_SESSION['app_theme'] ?? $_COOKIE['desire_theme'] ?? 'theme-navy';

// Handle Active Language in Session / Cookie
if (isset($_GET['set_lang']) && array_key_exists($_GET['set_lang'], $AVAILABLE_LANGS)) {
    $_SESSION['app_lang'] = $_GET['set_lang'];
    setcookie('desire_lang', $_GET['set_lang'], time() + (86400 * 30), "/");
    // Redirect back to clean URL
    $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?');
    $query = $_GET;
    unset($query['set_lang']);
    if (!empty($query)) {
        $redirectUrl .= '?' . http_build_query($query);
    }
    header("Location: " . $redirectUrl);
    exit;
}

$CURRENT_LANG = $_SESSION['app_lang'] ?? $_COOKIE['desire_lang'] ?? 'en';
