<?php
// FILE: /app/core/Session.php

class Session {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
            ini_set('session.use_strict_mode', 1);
            ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
            session_start();
        }
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    public static function remove($key) {
        unset($_SESSION[$key]);
    }

    public static function clear() {
        $_SESSION = [];
    }

    public static function destroy() {
        self::clear();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function flash($key, $value = null) {
        if ($value === null) {
            $data = self::get('_flash_' . $key);
            self::remove('_flash_' . $key);
            return $data;
        }

        self::set('_flash_' . $key, $value);
    }

    public static function hasFlash($key) {
        return self::has('_flash_' . $key);
    }

    public static function regenerate() {
        session_regenerate_id(true);
    }

    public static function setFlash($key, $value) {
        self::flash($key, $value);
    }

    public static function getFlash($key, $default = null) {
        $value = self::flash($key);
        return $value !== null ? $value : $default;
    }
}
