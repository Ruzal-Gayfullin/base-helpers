<?php

namespace Gayfullin\BaseHelpers;

use Exception;

class GeneratorUUID
{
    /**
     *
     * @param int $length
     * @return string
     */
    public static function model001(int $length = 8): string
    {
        $uuid = '';
        for ($i = 0; $i < $length; $i++) {
            $uuid .= chr(rand(32, 126));
        }
        return $uuid;
    }

    /**
     *
     * @param int $length
     * @return string
     */
    public static function model002(int $length = 8): string
    {
        $uuid = '';
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $limit = strlen($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $uuid .= $characters[rand(0, $limit)];
        }
        return $uuid;
    }

    /**
     *
     * @param int $length
     * @return string
     */
    public static function model003(int $length = 8): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $uuid = substr(str_shuffle($characters), 0, $length);
        return $uuid;
    }

    /**
     *
     * @param int $length
     * @return string
     */
    public static function model004(int $length = 8): string
    {
        $uuid = openssl_random_pseudo_bytes(ceil($length * 0.67), $crypto_strong);
        $uuid = str_replace(['=', '/', '+'], '', base64_encode($uuid));
        $uuid = substr($uuid, 0, $length);
        return $uuid;
    }

    /**
     *
     * @param int $length
     * @return string
     */
    public static function model005(int $length = 8): string
    {
        try {
            $uuid = str_replace(['=', '/', '+'], '', base64_encode(random_bytes($length)));
            $uuid = substr($uuid, 0, $length);
            return $uuid;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     *
     * @param int $upper
     * @param int $lower
     * @param int $numeric
     * @param int $other
     * @return string
     */
    public static function model006($upper = 2, $lower = 3, $numeric = 2, $other = 1): string
    {
        $uuid = '';
        $uuid_order = [];
        for ($i = 0; $i < $upper; $i++) {
            $uuid_order[] = chr(rand(65, 90));
        }
        for ($i = 0; $i < $lower; $i++) {
            $uuid_order[] = chr(rand(97, 122));
        }
        for ($i = 0; $i < $numeric; $i++) {
            $uuid_order[] = chr(rand(48, 57));
        }
        for ($i = 0; $i < $other; $i++) {
            $uuid_order[] = chr(rand(33, 47));
        }
        shuffle($uuid_order);
        foreach ($uuid_order as $char) {
            $uuid .= $char;
        }
        return $uuid;
    }

    /**
     *
     * @param int $length
     * @param string $available_sets
     * @return string
     */
    public static function model007(int $length = 8, string $available_sets = 'luds'): string
    {
        $symbols = [];
        $uuid = '';
        $str = '';
        if (strpos($available_sets, 'l') !== false) {
            $symbols[] = 'abcdefghjkmnpqrstuvwxyz';
        }
        if (strpos($available_sets, 'u') !== false) {
            $symbols[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        }
        if (strpos($available_sets, 'd') !== false) {
            $symbols[] = '23456789';
        }
        if (strpos($available_sets, 's') !== false) {
            $symbols[] = ',;:!?.$/*-+&@_+;./*&?$-!,';
        }
        foreach ($symbols as $symbol) {
            $uuid .= $symbol[array_rand(str_split($symbol))];
            $str .= $symbol;
        }
        $str = str_split($str);
        for ($i = 0; $i < $length - count($symbols); $i++) {
            $uuid .= $str[array_rand($str)];
        }
        $uuid = str_shuffle($uuid);
        return $uuid;
    }

    /**
     *
     * @param int $length
     * @return string
     */
    public static function model008(int $length = 8): string
    {
        $uuid = '';
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $i = 0;
        $characters_length = strlen($characters) - 1;
        while ($i < $length) {
            $char = substr($characters, mt_rand(0, $characters_length), 1);
            if (!strstr($uuid, $char)) {
                $uuid .= $char;
                $i++;
            }
        }
        return $uuid;
    }

    /**
     *
     * @param int $length
     * @return string
     */
    public static function model009(int $length = 8): string
    {
        $uuid = substr(str_shuffle(strtolower(sha1(rand() . time() . 'here our salt'))), 0, $length);
        for ($i = 0, $c = $length; $i < $c; $i++) {
            $uuid[$i] = rand(0, 100) > 50 ? strtoupper($uuid[$i]) : $uuid[$i];
        }
        return $uuid;
    }

    /**
     *
     * @param int $length
     * @return false|string
     * @throws Exception
     */
    public static function model010(int $length = 8): string
    {
        if (function_exists('random_bytes')) {
            try {
                $bytes = random_bytes(ceil($length / 2));
            } catch (Exception $e) {
                return $e->getMessage();
            }
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
        } else {
            throw new Exception("no cryptographically secure random function available");
        }
        $uuid = substr(bin2hex($bytes), 0, $length);
        for ($i = 0, $c = $length; $i < $c; $i++) {
            $uuid[$i] = rand(0, 100) > 50 ? strtoupper($uuid[$i]) : $uuid[$i];
        }
        return $uuid;
    }

    /**
     *
     * @param int $num_alpha
     * @param int $num_digit
     * @param int $num_non_alpha
     * @return string
     */
    public static function model011($num_alpha = 4, $num_digit = 2, $num_non_alpha = 2): string
    {
        $alpha = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits = '0123456789';
        $special = ',;:!?.$/*-+&@_+;./*&?$-!,';
        return str_shuffle(
            substr(str_shuffle($alpha), 0, $num_alpha) .
            substr(str_shuffle($digits), 0, $num_digit) .
            substr(str_shuffle($special), 0, $num_non_alpha)
        );
    }

    /**
     *
     * @param int $length
     * @param string $available_sets
     * @return string
     */
    public static function model012(int $length = 8, string $available_sets = 'luds'): string
    {
        $symbols = [];
        $uuid = $str = $oldIdx = '';
        $available_sets = str_split($available_sets);
        if (in_array('l', $available_sets)) { // Letters lower
            $symbols[] = 'abcdefghjkmnpqrstuvwxyz';
        }
        if (in_array('u', $available_sets)) { // Letters upper
            $symbols[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        }
        if (in_array('d', $available_sets)) { // Digits
            $symbols[] = '23456789';
        }
        if (in_array('s', $available_sets)) { // Special symbols
            $symbols[] = '!@#$%&*?';
        }
        if (in_array(1, $available_sets)) { // Russian Letters lower
            $symbols[] = 'абвгдеёжзийклмнпрстуфхцчшщыэюяъь';
        }
        if (in_array(2, $available_sets)) { // Russian Letters lower
            $symbols[] = 'АБВГДЕЁЖЗИЙКЛМНПРСТУФХЦШЩЫЭЮЯЪЬ';
        }
        $symbols = implode('', $symbols);
        $symbols = preg_split('//u', $symbols, null, PREG_SPLIT_NO_EMPTY);

        for ($i = 0; $i < $length; $i++) {
            do {
                $new_idx = array_rand($symbols);
            } while ($new_idx == $oldIdx);
            $uuid .= $symbols[$new_idx];
            $oldIdx = $new_idx;
        }
        return $uuid;
    }
}