<?php

namespace Library;

class CSRF
{
    private static $tokenName = 'csrf_token';
    private static $tokenLife = 3600;

    public static function generate()
    {
        if (!isset($_SESSION[self::$tokenName])) {
            $_SESSION[self::$tokenName] = bin2hex(random_bytes(32));
        }
        $_SESSION[self::$tokenName . '_time'] = time();
        return $_SESSION[self::$tokenName];
    }

    public static function getToken()
    {
        return self::generate();
    }

    public static function validate($token)
    {
        if (!isset($_SESSION[self::$tokenName]) || !isset($_SESSION[self::$tokenName . '_time'])) {
            return false;
        }

        if (time() - $_SESSION[self::$tokenName . '_time'] > self::$tokenLife) {
            unset($_SESSION[self::$tokenName], $_SESSION[self::$tokenName . '_time']);
            return false;
        }

        $valid = hash_equals($_SESSION[self::$tokenName], $token);
        
        if ($valid) {
            unset($_SESSION[self::$tokenName], $_SESSION[self::$tokenName . '_time']);
        }

        return $valid;
    }

    public static function getInput()
    {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function getMetaTag()
    {
        $token = self::getToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
