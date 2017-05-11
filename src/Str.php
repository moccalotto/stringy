<?php

namespace Moccalotto\Stringy;

use Exception;
use BadFunctionCallException;

/**
 * Facade.
 *
 * @method bool is($sourceStr, $string)
 * @method bool contains($sourceStr, $needle, int $index = 0)
 * @method bool startsWith($sourceStr, $needle)
 * @method bool endsWith($sourceStr, $needle)
 * @method int length($sourceStr)
 * @method int size($sourceStr)
 * @method Stringy positionOf($sourceStr, $needle, int $index = 0)
 * @method Stringy positionOfLast($sourceStr, $needle)
 * @method array characters($sourceStr)
 * @method array words($sourceStr)
 * @method Stringy transform($sourceStr, callable $callable)
 * @method Stringy substring($sourceStr, int $start, int $length = null)
 * @method Stringy after($sourceStr, $needle, int $index = 0)
 * @method Stringy before($sourceStr, $needle, int $index = 0)
 * @method Stringy between($sourceStr, $start, $stop, int $pairIndex = 0)
 * @method Stringy removeAfter($sourceStr, $needle, int $index = 0)
 * @method Stringy removeBefore($sourceStr, $needle, int $index = 0)
 * @method Stringy repeat($sourceStr, int $times)
 * @method Stringy unrepeat($sourceStr, $substring)
 * @method Stringy rightPadded($sourceStr, int $totalLengthOfResult, $padding = ' ')
 * @method Stringy leftPadded($sourceStr, int $totalLengthOfResult, $padding = ' ')
 * @method Stringy centered($sourceStr, int $totalLengthOfResult, $padding = ' ', $tieBreak = 'left')
 * @method Stringy leftTrim($sourceStr, $needle)
 * @method Stringy rightTrim($sourceStr, $needle)
 * @method Stringy leftTrimAll($sourceStr, array $strings)
 * @method Stringy rightTrimAll($sourceStr, array $strings)
 * @method Stringy append($sourceStr, $other)
 * @method Stringy prepend($sourceStr, $other)
 * @method Stringy surroundWith($sourceStr, $left, $right = null)
 * @method Stringy upper($sourceStr)
 * @method Stringy lower($sourceStr)
 * @method Stringy ucwords($sourceStr)
 * @method Stringy lcwords($sourceStr)
 * @method Stringy ucfirst($sourceStr)
 * @method Stringy lcfirst($sourceStr)
 * @method array explode($sourceStr, $pattern, int $limit = PHP_INT_MAX)
 * @method Stringy glue($sourceStr, array $strings)
 * @method Stringy replace($sourceStr, $search, $replace)
 * @method Stringy replaceMany($sourceStr, array $replacePairs)
 * @method Stringy remove($sourceStr, $search)
 * @method Stringy removeMany($sourceStr, array $searches)
 * @method Stringy asciiSafe($sourceStr)
 * @method Stringy entityEncoded($sourceStr, int $flags = ENT_QUOTES | ENT_HTML5)
 * @method Stringy escapeForHtml($sourceStr, int $flags = ENT_QUOTES | ENT_HTML5)
 * @method Stringy escapeForRegex($sourceStr, $delimiter)
 * @method Stringy includeIn($sourceStr, $string, array $extraParams = [])
 * @method Stringy format($sourceStr, array $args)
 * @method Stringy reverse($sourceStr)
 * @method Stringy limit($sourceStr, int $length)
 * @method Stringy shorten($maxLength, $breakPoint = '', $padding = '…')
 * @method Stringy normalizeSpace($sourceStr, $separator = ' ')
 * @method Stringy studlyCase($sourceStr)
 * @method Stringy camelCase($sourceStr)
 * @method Stringy snakeCase($sourceStr, $delimiter = '_')
 * @method Stringy uncase($sourceStr, $snakeCaseDelimiter = '_')
 * @method Stringy titleCase($sourceStr)
 * @method Stringy urlencode($sourceStr)
 * @method Stringy slug($sourceStr, $separator = '-', string $replaceBadCharWith = '')
 * @method Stringy cycle($sourceStr, int $minChars = 1, int $minCycles = 2)
 * @method Stringy randomChar($sourceStr)
 */
abstract class Str
{
    public static function __callStatic($name, $args)
    {
        if (!$args) {
            throw new BadFunctionCallException('Missing first argument');
        }

        try {
            $string = (string) array_shift($args);
        } catch (Exception $e) {
            throw new BadFunctionCallException('First argument must be a string', 0, $e);
        }

        $stringy = Stringy::create($string);

        if (!is_callable([$stringy, $name])) {
            throw new BadFunctionCallException(sprintf('Method »%s« does not exist', $name));
        }

        return call_user_func_array([$stringy, $name], $args);
    }

    public static function random()
    {
        return call_user_func_array([Stringy::class, 'random'], func_get_args());
    }
}
