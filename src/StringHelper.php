<?php

namespace Gayfullin\BaseHelpers;

class StringHelper
{
    public static function isAllowedBase64StringFile(string $string, array &$matches = []): bool
    {
        return (bool)preg_match("/^data:image\/(" . implode('|', FileHelper::ALLOWED_EXTENSIONS) . ");base64/i", $string, $matches);
    }
}