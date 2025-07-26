<?php

class TextHealper
{
    public function __construct() {}

    public function getLocation(string $filePath): string {
        $pathInfo = pathinfo($filePath);
        $pathInfo["dirname"] = str_replace(_PS_CORE_DIR_, "", $pathInfo["dirname"]);
        $location = $pathInfo["dirname"] . "/" . $pathInfo["basename"];
        return ltrim($location, "/");
    }

    public function implodeId(int $id): string {
        $folders = str_split((string) $id);
        return implode("/", $folders) . "/";
    }

    public function clearText(string $text): string {
        $text = strip_tags($text);
        return preg_replace("/\s+/", " ", $text);
    }

    public function shortText(string $text, int $length = 100): string {
        return mb_strimwidth($text, 0, $length, "...");
    }
}
