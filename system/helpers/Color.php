<?php
namespace system\helpers;

/**
 * Color
 */
class Color
{

    // PHP charCodeAt
    private static function charCodeAt($str, $index)
    {
        $char = mb_substr($str, $index, 1, 'UTF-8');
        if (mb_check_encoding($char, 'UTF-8')) {
            $ret = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');
            return hexdec(bin2hex($ret));
        } else {
            return null;
        }
    }

    // Str To Color
    public function textToColor($str)
    {
        $hash = 0;
        $colour = "#";
        for ($i = 0; $i < strlen($str); $i++) {
            $hash = self::charCodeAt($str, $i) + (($hash << 5) - $hash);
        }
        for ($i = 0; $i < 3; $i++) {
            $colour .= substr("00" . dechex(($hash >> ($i * 2)) & 0xff), -2);
        }
        return $colour;
    }
}
