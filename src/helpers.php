<?php

use Moccalotto\Stringy\Stringy;

if (!function_exists('str')) {
    /**
     * Stringify a string.
     *
     * @param Stringy|string $string   The string to be Stringyfied.
     *                                 If $string is a (descendant of) Stringy, it will
     *                                 be cloned and converted to using $encoding.
     * @param string|null    $encoding The encoding of the $string
     *
     * @return Stringy
     */
    function str($string = '', string $encoding = null) : Stringy
    {
        return Stringy::create($string, $encoding);
    }
}
