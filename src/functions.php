<?php

if (!function_exists('t')) {
    function t(mixed ...$args): void
    {
        $isCli = PHP_SAPI === 'cli';
        if (!$isCli && !array_filter(headers_list(), fn ($value): bool => str_starts_with(strtolower($value), 'content-type'))) {
            header('Content-Type: text/html; charset=UTF-8');
        }

        $filter = function (mixed &$value) use (&$filter): void {
            switch (gettype($value)) {
                case 'NULL':
                    $value = 'null';
                    break;
                case 'boolean':
                    if ($value === true) {
                        $value = 'true';
                    } else {
                        $value = 'false';
                    }
                    break;
                case 'string':
                    $value = "'{$value}'";
                    break;
                case 'array':
                    array_walk($value, $filter);
                    break;
            }
        };

        if ($isCli) {
            foreach ($args as $value) {
                $filter($value);
                print_r($value);
                echo PHP_EOL;
            }
        } else {
            foreach ($args as $value) {
                $filter($value);
                echo '<pre>';
                print_r($value);
                echo '</pre>';
            }
        }
    }
}

if (!function_exists('tt')) {
    function tt(mixed ...$args): void
    {
        call_user_func_array('t', $args);
        die();
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return Ep::getEnv()->get($key, $default);
    }
}
