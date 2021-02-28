<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Utils
{
    public static function getJavaScriptValue($value): string
    {
        $result = '';
        $type = gettype($value);

        switch ($type) {
            case 'boolean':
            case 'integer':
            case 'double':
            case 'NULL':
                $result = var_export($value, true);
                break;

            case 'string':
                $result = "`{$value}`";
                break;

            case 'array':
                $result = json_encode($value);
                break;

            default:
                throw new \TypeError;
                break;
        }

        return $result;
    }
}