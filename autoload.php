<?php
// ⚙️ Enable error display for debugging (temporary)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 🔒 Start secure session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_start();
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }
}

// ✅ استدعاء ملف connect.php من config داخل public_html
require_once __DIR__ . '/config/connect.php';

// 🌐 محاولة تحميل نظام الترجمة (اختياري)
$translate_path = $_SERVER['DOCUMENT_ROOT'] . '/translate.php';
if (file_exists($translate_path)) {
    require_once $translate_path;
} else {
    error_log("⚠️ translate.php not found at: $translate_path");
}

// ✅ Auto-translate output for admin pages (اختياري)
if (!function_exists('auto_translate_output')) {
    ob_start(function($buffer) {
        if (!empty($_SESSION['lang']) && function_exists('__t')) {
            $lang_file = $_SERVER['DOCUMENT_ROOT'] . '/lang/' . $_SESSION['lang'] . '.php';
            if (file_exists($lang_file)) {
                $lang_data = include $lang_file;
                if (is_array($lang_data)) {
                    foreach ($lang_data as $key => $val) {
                        $buffer = str_replace('{{' . $key . '}}', $val, $buffer);
                    }
                }
            }
        }
        return $buffer;
    });
}

// ✅ fallback functions if translation system was deleted
if (!function_exists('lang_dir')) {
    function lang_dir() {
        return 'ltr'; // ثابت دائمًا يسار لليمين
    }
}

if (!function_exists('__t')) {
    function __t($text) {
        return $text; // عرض النص كما هو بدون ترجمة
    }
}
