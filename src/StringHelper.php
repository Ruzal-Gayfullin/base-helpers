<?php

namespace Gayfullin\BaseHelpers;

class StringHelper
{
    public static function isAllowedBase64StringFile(string $string, array &$matches = []): bool
    {
        return (bool)preg_match("/^data:image\/(" . implode('|', FileHelper::ALLOWED_EXTENSIONS) . ");base64/i", $string, $matches);
    }

    public static function cleanTextFromDuplicateSpaces($text)
    {
        $text = trim($text);
        $text = nl2br($text, true);
        $text = str_replace("\xc2\xa0", ' ', $text);
        $text = preg_replace('/\xc2\xa0/', ' ', $text);
        $text = preg_replace("/([\s\r\n])+/", " ", $text);
        $text = str_replace("<br /> ", "<br />", $text);
        $text = str_replace("<br /><br /><br />", "<br /><br />", $text);
        $text = str_replace("<br /><br /><br />", "<br /><br />", $text);
        $text = str_replace("<br /> ", "<br />", $text);
        $text = str_replace("<br /><br /><br />", "<br /><br />", $text);
        $text = str_replace("<br /><br /><br />", "<br /><br />", $text);
        $text = str_replace("<br /> ", "<br />", $text);
        $text = str_replace("<br /><br /><br />", "<br /><br />", $text);
        $text = str_replace("<br /><br /><br />", "<br /><br />", $text);
        $text = str_replace("<br /> ", "<br />", $text);
        $text = str_replace("<br /><br /><br />", "<br /><br />", $text);
        $text = str_replace("<br /><br /><br />", "<br /><br />", $text);
        $text = str_replace("<br /> ", "<br />", $text);
        $text = str_replace("<br /><br /><br />", "<br /><br />", $text);
        $text = str_replace("<br /><br /><br />", "<br /><br />", $text);
        $text = str_replace("<br />", PHP_EOL, $text);
        return $text;
    }

    public static function proceedHtmlToText($text, $advanced = true)
    {
        $text = html_entity_decode($text, null, "UTF-8");
        $text = html_entity_decode($text, null, "UTF-8");
        $text = htmlspecialchars_decode($text);
        $text = htmlspecialchars_decode($text);

        $text = str_replace('<br />', PHP_EOL, $text);
        $text = str_replace('<br/>', PHP_EOL, $text);
        $text = str_replace('<br>', PHP_EOL, $text);

        $text = preg_replace('/(<img.*?src="([^"]*)"([^>]*))/is', '==IMAGE==image:\2==IMAGE==', $text);
        $text = preg_replace('/(<h([1-6])[^>]*>(.*?)<\/h\2>)/is', '==HEADER==header:\3==HEADER==', $text);
        $text = preg_replace('/(<blockquote[^>]*>(.*?)<\/blockquote>)/is', '==QUOTE==quote:\2==QUOTE==', $text);
        $text = preg_replace('/(<li[^>]*>(.*?)<\/li>)/is', '==LI==li:\2==LI==', $text);
        $text = preg_replace('/(<p[^>]*>(.*?)<\/p>)/is', '==P==\2==P==', $text);
        $text = preg_replace('/(<style[^>]*>(.*?)<\/style>)/is', '', $text);

        $result = preg_split("/(==IMAGE==|==HEADER==|==QUOTE==|==LI==|==P==)/is", $text);

        $textArray = array();
        foreach ($result as $v) {
            if (mb_strpos($v, "image:") !== 0) {
                if (mb_strpos($v, ">") === 0) {
                    $v = mb_substr($v, 1);
                }
                if (empty($keepTags)) {
                    $tmp = trim(htmlspecialchars(stripcslashes(strip_tags($v))));
                } else {
                    $tmp = trim(strip_tags($v, $keepTags));
                    $tmp = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $tmp);
                }
                if (!empty($tmp)) {
                    if (mb_strpos($v, "image:") !== 0 && mb_strpos($v, "header:") !== 0 && mb_strpos($v, "quote:") !== 0 && mb_strpos($v, "li:") !== 0) {
                        $tmp = explode("\n", $tmp);
                        foreach ($tmp as $tmptxt) {
                            $textArray[] = self::cleanTextFromDuplicateSpaces($tmptxt);
                        }
                    } else {
                        $textArray[] = self::cleanTextFromDuplicateSpaces($tmp);
                    }
                }
            }
        }
        $resultArray = array();
        $i = 0;
        foreach ($textArray as $v) {
            $text = $v;
            if (mb_strpos($v, "img:") === 0) {
                $text = mb_substr($v, 4);
                $key = "img$i";
            } elseif (mb_strpos($v, "header:") === 0) {
                $text = mb_substr($v, 7);
                if ($advanced) {
                    $key = "h$i";
                } else {
                    $key = "p$i";
                }
            } elseif (mb_strpos($v, "quote:") === 0) {
                $text = mb_substr($v, 6);
                if ($advanced) {
                    $key = "q$i";
                } else {
                    $key = "p$i";
                }
            } elseif (mb_strpos($v, "li:") === 0) {
                $text = mb_substr($v, 3);
                if ($advanced) {
                    $key = "li$i";
                } else {
                    $key = "p$i";
                }
            } else {
                $key = "p$i";
            }
            $text = trim($text);
            if (!empty($text)) {
                $resultArray[$key] = $text;
                $i++;
            }

        }
        $text = implode(PHP_EOL, $resultArray);
        $text = self::cleanTextFromDuplicateSpaces($text);
        return $text;

    }
}