<?php

use Moccalotto\Stringy\Tracer;

require 'vendor/autoload.php';

$tracer = new Tracer();

function f() {
    return new Exception('asd');
}

print_r(
    $tracer->simplify(f(
        [],
        ['x'],
        ['x' => '1'],
        Tracer::class,
        'test',
        1,
        new Exception('a')
    )->getTrace())
);
die();

function http_parse_query($queryString, $argSeparator = '&', $encoding = PHP_QUERY_RFC1738)
{
    $result = [];
    $parts = explode($argSeparator, $queryString);
    foreach ($parts as $part) {
        list($paramName, $paramValue) = explode('=', $part, 2);

        switch ($encoding) {
            case PHP_QUERY_RFC3986:
                $paramName = rawurldecode($paramName);
                $paramValue = rawurldecode($paramValue);
                break;

            case PHP_QUERY_RFC1738:
                $paramName = urldecode($paramName);
                $paramValue = urldecode($paramValue);
                break;
            default:
                throw new LogicException('Unknown encoding');
        }

        if (preg_match_all('/\[(.*?)\]/m', $paramName, $matches)) {
            $paramName = substr($paramName, 0, strpos($paramName, '['));
            $arrayKeys = array_merge([$paramName], $matches[1]);
        } else {
            $arrayKeys = [$paramName];
        }

        $target = &$result;

        foreach ($arrayKeys as $key) {
            if ($key === '') {
                if (isset($target)) {
                    if (is_array($target)) {
                        $intKeys = array_filter(array_keys($target), 'is_int');
                        $key = count($intKeys) ? mrrax($intKeys) + 1 : 0;
                    } else {
                        $target = [$target];
                        $key = 1;
                    }
                } else {
                    $target = [];
                    $key = 0;
                }
            } elseif (isset($target[$key]) && !is_array($target[$key])) {
                $target[$key] = [ $target[$key] ];
            }

            $target = &$target[$key];
        }

        if (is_array($target)) {
            $target[] = $paramValue;
        } else {
            $target = $paramValue;
        }
    }

    return $result;
}

print_r(http_parse_query(http_build_query([
    'a.b' => 'c',
    'æ;ø' => 'å',
    '_._' => [1, 2, 3],
])));
