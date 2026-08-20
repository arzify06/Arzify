<?php
/**
 * Desire Travel - Multi-Language Helper
 */

require_once __DIR__ . '/../config/config.php';

function getTranslations(): array {
    static $translations = null;
    global $CURRENT_LANG;

    if ($translations === null) {
        $langFile = BASE_DIR . "/config/languages/{$CURRENT_LANG}.php";
        if (file_exists($langFile)) {
            $translations = require $langFile;
        } else {
            $translations = require BASE_DIR . "/config/languages/en.php";
        }
    }
    return $translations;
}

/**
 * Translate a language key
 */
function __(string $key, string $default = ''): string {
    $translations = getTranslations();
    if (isset($translations[$key])) {
        return $translations[$key];
    }
    // Fallback to English if missing in current lang
    static $enFallback = null;
    if ($enFallback === null) {
        $enFallback = require BASE_DIR . "/config/languages/en.php";
    }
    if (isset($enFallback[$key])) {
        return $enFallback[$key];
    }
    return $default !== '' ? $default : $key;
}

/**
 * Echo translated string safely with HTML escaping
 */
function _e(string $key, string $default = ''): void {
    echo htmlspecialchars(__($key, $default), ENT_QUOTES, 'UTF-8');
}
