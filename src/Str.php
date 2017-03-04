<?php

namespace Moccalotto\Stringy;

use Exception;
use BadFunctionCallException;

/**
 * Facade
 *
 * @method bool is($string)
 * @method bool contains($needle, int $index = 0)
 * @method bool startsWith($needle)
 * @method bool endsWith($needle)
 * @method int length()
 * @method int size()
 * @method Stringy positionOf($needle, int $index = 0)
 * @method Stringy positionOfLast($needle)
 * @method array characters()
 * @method array words()
 * @method Stringy transform(callable $callable)
 * @method Stringy substring(int $start, int $length = null)
 * @method Stringy after($needle, int $index = 0)
 * @method Stringy before($needle, int $index = 0)
 * @method Stringy between($start, $stop, int $pairIndex = 0)
 * @method Stringy removeAfter($needle, int $index = 0)
 * @method Stringy removeBefore($needle, int $index = 0)
 * @method Stringy repeat(int $times)
 * @method Stringy unrepeat($substring)
 * @method Stringy rightPadded(int $totalLengthOfResult, $padding = ' ')
 * @method Stringy leftPadded(int $totalLengthOfResult, $padding = ' ')
 * @method Stringy centered(int $totalLengthOfResult, $padding = ' ', $tieBreak = 'left')
 * @method Stringy leftTrim($needle)
 * @method Stringy rightTrim($needle)
 * @method Stringy leftTrimAll(array $strings)
 * @method Stringy rightTrimAll(array $strings)
 * @method Stringy append($other)
 * @method Stringy prepend($other)
 * @method Stringy surroundWith($left, $right = null)
 * @method Stringy upper()
 * @method Stringy lower()
 * @method Stringy ucwords()
 * @method Stringy lcwords()
 * @method Stringy ucfirst()
 * @method Stringy lcfirst()
 * @method array explode($pattern, int $limit = PHP_INT_MAX)
 * @method Stringy glue(array $strings)
 * @method Stringy replace($search, $replace)
 * @method Stringy replaceMany(array $replacePairs)
 * @method Stringy remove($search)
 * @method Stringy removeMany(array $searches)
 * @method Stringy asciiSafe()
 * @method Stringy entityEncoded(int $flags = ENT_QUOTES | ENT_HTML5)
 * @method Stringy escapeForHtml(int $flags = ENT_QUOTES | ENT_HTML5)
 * @method Stringy escapeForRegex($delimiter)
 * @method Stringy includeIn($string, array $extraParams = [])
 * @method Stringy format(array $args)
 * @method Stringy reverse()
 * @method Stringy limit(int $length)
 * @method Stringy shorten($maxLength, $breakPoint = '', $padding = '…')
 * @method Stringy normalizeSpace($separator = ' ')
 * @method Stringy studlyCase()
 * @method Stringy camelCase()
 * @method Stringy snakeCase($delimiter = '_')
 * @method Stringy uncase($snakeCaseDelimiter = '_')
 * @method Stringy titleCase()
 * @method Stringy urlencode()
 * @method Stringy slug($separator = '-', string $replaceBadCharWith = '')
 * @method Stringy cycle(int $minChars = 1, int $minCycles = 2)
 * @method Stringy randomChar()
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
