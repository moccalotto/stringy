<?php

/*
 * This file is part of the Stringy package.
 *
 * @package Stringy
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */
declare(strict_types=1);

namespace Moccalotto\Stringy;

use Countable;
use ArrayAccess;
use UnexpectedValueException;

/**
 * A php-string turned into an immutable object.
 */
class Stringy implements ArrayAccess, Countable
{
    use Traits\HasArrayAccess;

    /**
     * UTF-8 encoded contents of this object.
     *
     * @var string
     */
    protected $string;

    /**
     * Factory.
     *
     * @param Stringy|string $string   The string to be Stringyfied.
     *                                 If $string is a (descendant of) Stringy, it will
     *                                 be cloned and converted to using $encoding
     * @param string|null    $encoding The encoding of the $string
     *
     * @return Stringy
     */
    public static function create($string = '', string $encoding = null)
    {
        if ($string instanceof self) {
            return new static($string->string, 'UTF-8');
        }

        return new static($string, $encoding);
    }

    /**
     * Turn an array of strings into Stringy objects.
     *
     * @param array       $strings  array of strings or Stringy objects
     * @param string|null $encoding
     *
     * @return Stringy[]
     */
    public static function createMany(array $strings, $encoding = null) : array
    {
        return array_map(function ($string) use ($encoding) {
            return static::create($string, $encoding);
        }, $strings);
    }

    /**
     * Turn an array of strings into Stringy objects and map them.
     *
     * @param Stringy|string $strings
     * @param callable       $callable
     *
     * @return array
     */
    public static function mapMany(array $strings, callable $callable) : array
    {
        return array_map($callable, static::createMany($strings));
    }

    /**
     * Factory for a random string of a given length.
     *
     * @return Stringy
     */
    public static function random($length)
    {
        $res = static::create(base64_encode(random_bytes($length)), 'UTF-8')
            ->replace('/', '')
            ->replace('+', '')
            ->replace('=', '');

        $remainder = $length - $res->length();

        if ($remainder > 0) {
            return $res->append(static::random($remainder));
        }

        return $res->substring(0, $length);
    }

    protected static function toUtf8(string $string, string $encoding)
    {
        if (!in_array($encoding, mb_list_encodings())) {
            throw new EncodingException('Encoding not supported', $string, $encoding);
        }

        if (!($encoding === 'pass' || mb_check_encoding($string, $encoding))) {
            throw new EncodingException('Invalid string', $string, $encoding);
        }

        $string = mb_convert_encoding($string, 'UTF-8', $encoding);

        return $string;
    }

    /**
     * Constructor.
     *
     * @param string      $string          the string contents of the Stringy object
     * @param string|null $currentEncoding the current encoding of $string
     */
    public function __construct(string $string = '', string $currentEncoding = null)
    {
        $this->string = static::toUtf8($string, $currentEncoding ?? mb_internal_encoding());
    }

    /**
     * Get the inner string of this object encoded as $encoding.
     *
     * @param string|null $encodedAs The encoding to get the string as. NULL = mb_internal_encoding
     *
     * @return string
     */
    public function string($encodedAs = null) : string
    {
        if ($encodedAs === null) {
            $encodedAs = mb_internal_encoding();
        }
        if (!in_array($encodedAs, mb_list_encodings())) {
            throw new EncodingException('Encoding not supported', $this->string, $encodedAs);
        }

        $string = mb_convert_encoding($this->string, $encodedAs, 'UTF-8');

        if (!mb_check_encoding($string, $encodedAs)) {
            throw new EncodingException('Invalid string', $string, $encodedAs);
        }

        return $string;
    }

    /**
     * Compare this string to another.
     *
     * @param Stringy|string $string
     *
     * @return bool only true if the two strings are equal
     */
    public function is($string) : bool
    {
        return static::create($string)->string === $this->string;
    }

    /**
     * Get the length (in characters) of the inner string.
     *
     * @return int
     */
    public function length() : int
    {
        return mb_strlen($this->string, 'UTF-8');
    }

    /**
     * Does the string contain $needle.
     *
     * @param Stringy|string $needle
     * @param int            $index
     *
     * @return bool
     */
    public function contains($needle, int $index = 0) : bool
    {
        return $this->positionOf($needle, $index) !== null;
    }

    /**
     * Find a the position of the first character of $needle within this string.
     *
     * @param Stringy|string $needle The string to search for
     * @param int            $index  Which occurrance of the string to get the position of.
     *                               0 means the first match, 1 means the second match, etc.
     *                               -1 means the last match, -2 means the penultimate match, etc
     *
     * @return int|null The position of the first character of the $needle found.
     *                  NULL if $needle with the given $index could not be found
     *                  NOTE: that this behavior deviates from strpos in that strpos returns FALSE
     *                  in case $needle was not found
     */
    public function positionOf($needle, int $index = 0)
    {
        if (!preg_match_all(
            static::create($needle)->escapeForRegex('/')->prepend('/')->append('/u')->string,
            $this->string,
            $matches,
            PREG_OFFSET_CAPTURE
        )) {
            return;
        }

        $matchCount = count($matches[0]);

        // index is too high
        if ($index >= $matchCount) {
            return;
        }

        // index is negative, correct it into a positive index
        if ($index < 0) {
            $index = $matchCount + $index;
        }

        // index was so low it could not be correct (i.e. too few matches)
        if ($index < 0) {
            return;
        }

        return $matches[0][$index][1];
    }

    /**
     * Transform the string.
     *
     * @param callable $callable a function with the signature (Stringy $string) : Stringy|string
     *
     * @return Stringy
     */
    public function transform(callable $callable)
    {
        return static::create($callable(clone $this));
    }

    /**
     * Get the part of the string that comes after $needle.
     *
     * @example: str('foo bar baz')->after('foo ') == 'bar baz'
     *
     * @param Stringy|string $needle
     * @param int            $index  Which occurrance of the string to search for:
     *                               0 means the first match, 1 means the second match, etc.
     *                               -1 means the last match, -2 means the penultimate match, etc
     *
     * @return Stringy a clone of $this with a inner string containing the
     *                 part that comes after $needle. If $needle is not
     *                 found, an empty Stringy is returned
     */
    public function after($needle, int $index = 0)
    {
        $other = static::create($needle);

        if ($other->length() === 0) {
            return clone $this;
        }

        $pos = $this->positionOf($needle, $index);

        if ($pos === null) {
            return static::create('');
        }

        return $this->substring($pos + $other->length());
    }

    /**
     * Get the part of the string before the first character of $needle.
     *
     * @example: str('foo bar baz')->before('foo ') == 'bar baz'
     *
     * @param Stringy|string $needle
     * @param int            $index  Which occurrance of the string to search for:
     *                               0 means the first match, 1 means the second match, etc.
     *                               -1 means the last match, -2 means the penultimate match, etc
     *
     * @return Stringy a clone of $this with a inner string containing the
     *                 part that comes after $needle. If $needle is not
     *                 found, an empty Stringy is returned
     */
    public function before($needle, int $index = 0)
    {
        $other = static::create($needle);

        if ($other->length() === 0) {
            return clone $this;
        }

        $pos = $this->positionOf($needle, $index);

        if ($pos === null) {
            return static::create('');
        }

        return $this->substring(0, $pos);
    }

    /**
     * Remove the substring that comes after the nth $needle.
     *
     * @param Stringy|string $needle
     * @param int            $index
     *
     * @return Stringy
     */
    public function removeAfter($needle, int $index = 0)
    {
        if (!$this->contains($needle, $index)) {
            return clone $this;
        }

        return $this->before($needle, $index);
    }

    /**
     * Remove the substring that comes before the nth $needle.
     *
     * @param Stringy|string $needle
     * @param int            $index
     *
     * @return Stringy
     */
    public function removeBefore($needle, int $index = 0)
    {
        if (!$this->contains($needle, $index)) {
            return clone $this;
        }

        return $this->after($needle, $index);
    }

    /**
     * Return the text that comes after $start and before $stop.
     *
     * @param Stringy|string $start
     * @param Stringy|string $stop
     * @param int            $pairIndex search for the nth pair and
     *                                  find what lies between those strings
     *
     * @return Stringy the string between $start and $stop
     */
    public function between($start, $stop, int $pairIndex = 0)
    {
        $idxStart = $this->positionOf($start, $pairIndex);
        $idxStop = $this->positionOf($stop, $pairIndex);

        if ($idxStart === null || $idxStop === null) {
            return static::create('');
        }

        $pos = $idxStart + static::create($start)->length();
        $length = $idxStop - $pos;

        if ($length <= 0) {
            return static::create('');
        }

        return $this->substring($pos, $length);
    }

    /**
     * Get a substring.
     *
     * @see http://php.net/manual/function.mb-substr.php
     *      for details about the $start and $length paramteers
     *
     * @param int      $start  The offset of the substring.
     *                         If negative, it counts backwards
     *                         from the end of the content string.
     * @param int|null $length The length of the substring to extract.
     *                         If negative, it counts backwards from
     *                         the end of the content string.
     *                         If NULL, the entire string after $start
     *                         is extracted.
     *
     * @return Stringy
     */
    public function substring(int $start, int $length = null)
    {
        return static::create(mb_substr($this->string, $start, $length, 'UTF-8'), 'UTF-8');
    }

    /**
     * Repeat this string a number of times.
     *
     * @param int $times
     *
     * @return Stringy a string repeated $times times
     */
    public function repeat(int $times)
    {
        if ($times == 0) {
            return static::create('');
        }

        if ($times < 0) {
            throw new StringyException('Cannot repeat a string a negative number of times', $this->string);
        }

        return static::create(str_repeat($this->string, $times));
    }

    /**
     * If a substring is repeated 2 or more times in a rowwithin the content string, reduce it to being there only once.
     *
     * str('hello    world')->unrepeat(' ') would be turned into 'hello world'
     * str('foo    bar    baz')->unrepeat(' ') would be turned into 'foo bar baz'
     *
     * @param Stringy|string $substring
     *
     * @return Stringy
     */
    public function unrepeat($substring)
    {
        $regex = static::create($substring)
            ->escapeForRegex('/')
            ->includeIn('/(%s)+/u');

        return static::create(preg_replace(
            $regex->string,
            '$1',
            $this->string
        ), 'UTF-8');
    }

    public function rightPadded(int $totalLengthOfResult, $padding = ' ')
    {
        $padding = static::create($padding)[0];

        $paddingLength = $totalLengthOfResult - $this->length();

        if ($paddingLength <= 0) {
            return clone $this;
        }

        return $this->append($padding->repeat($paddingLength));
    }

    public function leftPadded(int $totalLengthOfResult, $padding = ' ')
    {
        $padding = static::create($padding)[0];

        $paddingLength = $totalLengthOfResult - $this->length();

        if ($paddingLength <= 0) {
            return clone $this;
        }

        return $this->prepend($padding->repeat($paddingLength));
    }

    public function centered(int $totalLengthOfResult, $padding = ' ', $tieBreak = 'left')
    {
        $methodMap = [
            'left' => 'floor',
            'right' => 'ceil',
        ];

        if (!isset($methodMap[$tieBreak])) {
            throw new UnexpectedValueException(sprintf(
                'tieBreak must be one [%s]',
                implode(', ', array_keys($methodMap))
            ));
        }

        $tieBreakerMethod = $methodMap[$tieBreak];

        $leftLength = (int) $tieBreakerMethod(($totalLengthOfResult + $this->length()) / 2);

        return $this->leftPadded(
            $leftLength,
            $padding
        )->rightPadded(
            $totalLengthOfResult,
            $padding
        );
    }

    public function upper()
    {
        return static::create(mb_strtoupper($this->string, 'UTF-8'), 'UTF-8');
    }

    public function lower()
    {
        return static::create(mb_strtolower($this->string, 'UTF-8'), 'UTF-8');
    }

    /**
     * Turn the first letter of every word uppercase.
     *
     * Does not interfere with the casing of the rest of the letters.
     * Words are defined as strings separated by a word-boundary (such as white space, dashes, dots, etc.)
     *
     * @return Stringy
     */
    public function ucwords()
    {
        return $this->transform(function ($stringy) {
            return preg_replace_callback('/\b\w+/u', function ($matches) {
                $match = static::create($matches[0], 'UTF-8');

                return $match
                    ->limit(1)
                    ->upper()
                    ->append($match->substring(1));
            }, $stringy->string);
        });
    }

    /**
     * Turn the first letter of every word lowercase.
     *
     * Does not interfere with the casing of the rest of the letters.
     * Words are defined as strings separated by a word-boundary (such as white space, dashes, dots, etc.)
     *
     * @return Stringy
     */
    public function lcwords()
    {
        return $this->transform(function ($stringy) {
            return preg_replace_callback('/\b\w+/u', function ($matches) {
                $match = static::create($matches[0], 'UTF-8');

                return $match
                    ->limit(1)
                    ->lower()
                    ->append($match->substring(1));
            }, $stringy->string);
        });
    }

    /**
     * Turn first letter lowercased.
     *
     * Do not change the casing of the rest of the letters.
     */
    public function lcfirst()
    {
        if ($this->length() === 0) {
            return clone $this;
        }

        $first = $this->limit(1)->lower();

        return $first->append($this->substring(1));
    }

    /**
     * Turn first letter uppercased.
     *
     * Do not change the casing of the rest of the letters.
     */
    public function ucfirst()
    {
        if ($this->length() === 0) {
            return clone $this;
        }

        $first = $this->limit(1)->upper();

        return $first->append($this->substring(1));
    }

    /**
     * Split the string into segments.
     *
     * @see http://php.net/manual/function.explode.php
     *
     * @param Stringy|string $pattern
     * @param int            $limit
     *
     * @return Stringy[] array of strings as Stringy instances
     */
    public function explode($pattern, int $limit = PHP_INT_MAX) : array
    {
        return static::createMany(explode(
            static::create($pattern)->string,
            $this->string,
            $limit
        ));
    }

    /**
     * Replace all occurrences of the $search string with the $replacement string.
     *
     * @see http://php.net/manual/function.str-replace.php
     *
     * @param Stringy|string $search
     * @param Stringy|string $replace
     *
     * @return Stringy
     */
    public function replace($search, $replace)
    {
        return static::create(str_replace(
            static::create($search),
            static::create($replace),
            $this->string
        ), 'UTF-8');
    }

    /**
     * Perform to => from translation.
     *
     * @see http://php.net/manual/function.strtr.php
     *
     * @param array $replacePairs an array in the form array('from' => 'to', ...).
     *
     * @return Stringy a Stringy where all the occurrences
     *                 of the array keys have been replaced by the corresponding values.
     *                 The longest keys will be tried first.
     *                 Once a substring has been replaced,
     *                 its new value will not be searched again.
     */
    public function replaceMany(array $replacePairs)
    {
        return $this->transform(function ($stringy) use ($replacePairs) {
            return strtr((string) $stringy, $replacePairs);
        });
    }

    /**
     * Remove a substring. (I.e. replace $search with an empty string).
     *
     * @param Stringy|string $search the substring to be removed.
     *
     * @return Stringy
     */
    public function remove($search)
    {
        return $this->replace($search, '');
    }

    public function removeMany(array $searches)
    {
        return $this->replaceMany(array_combine(
            $searches,
            array_fill(0, count($searches), '')
        ));
    }

    public function escapeForRegex($delimiter)
    {
        return static::create(preg_quote(
            $this->string,
            static::create($delimiter)->string
        ), 'UTF-8');
    }

    /**
     * Append a string to $this.
     *
     * @param Stringy|string $other
     *
     * @return Stringy a clone of $this where contents of $other is prepended
     */
    public function append($other)
    {
        return static::create(
            $this->string . static::create($other)->string,
            'UTF-8'
        );
    }

    /**
     * Prepend a string to $this.
     *
     * @param Stringy|string $other
     *
     * @return Stringy a clone of $this where contents of $other is prepended
     */
    public function prepend($other)
    {
        return static::create(
            static::create($other)->string . $this->string,
            'UTF-8'
        );
    }

    public function surroundWith($left, $right = null)
    {
        return $this->prepend($left)->append($right ?? $left);
    }

    public function leftTrim($needle)
    {
        return $this->leftTrimAll([$needle]);
    }

    public function rightTrim($needle)
    {
        return $this->rightTrimAll([$needle]);
    }

    public function includeIn($string, array $extraParams = [])
    {
        return static::create($string)->format(array_merge(
            [$this],
            $extraParams
        ));
    }

    public function format(array $args)
    {
        $result = @vsprintf($this->string, $args);

        if ($result === false) {
            $error = error_get_last()['message'];
            throw new StringyException(
                sprintf('Could not format string: %s', $error),
                $this->string
            );
        }

        return static::create($result);
    }

    public function leftTrimAll(array $strings)
    {
        $regex = static::create('|')->glue(static::mapMany($strings, function ($string) {
            return $string->escapeForRegex('/');
        }, $strings))->includeIn('/^(%s)+/u');

        return static::create(preg_replace($regex->string, '', $this->string), 'UTF-8');
    }

    public function rightTrimAll(array $strings)
    {
        $regex = static::create('|')->glue(static::mapMany($strings, function ($string) {
            return $string->escapeForRegex('/');
        }))->includeIn('/(%s)+$/u');

        return static::create(preg_replace($regex->string, '', $this->string), 'UTF-8');
    }

    public function startsWith($needle) : bool
    {
        $needleStringy = static::create($needle);

        return $this->substring(0, $needleStringy->length())->string == $needleStringy->string;
    }

    public function endsWith($needle) : bool
    {
        $needleStringy = static::create($needle);

        return $this->substring(-$needleStringy->length())->string == $needleStringy->string;
    }

    public function reverse()
    {
        return static::create('')->glue(array_reverse($this->characters()));
    }

    public function glue(array $strings)
    {
        return static::create(implode(
            $this->string,
            static::mapMany($strings, function ($string) {
                return $string->string;
            })
        ));
    }

    /**
     * Limit the length of the content string by truncating it.
     *
     * @param int $length the maximum length (in characters) of the content string
     *
     * @return Stringy
     */
    public function limit(int $length)
    {
        return $this->substring(0, $length);
    }

    /**
     * Convert the content string into an array of words.
     *
     * Note that this method will not correctly split kanji, thai, braille, and
     * other scripts where words are not necessarily clearly bounded.
     *
     * @return Stringy[]
     */
    public function words() : array
    {
        preg_match_all('/\w+/u', $this->string, $matches);

        return static::createMany($matches[0], 'UTF-8');
    }

    /**
     * Turn the normally worded (or snakeCased) string into a StudlyCasedVersionOfItself.
     *
     * @return Stringy
     */
    public function studlyCase()
    {
        return $this->replace('-', ' ')
            ->replace('_', ' ')
            ->unrepeat(' ')
            ->ucwords()
            ->replace(' ', '');
    }

    /**
     * Turn the normally worded (or snakeCased) string into a camelCasedVersionOfItself.
     *
     * @return Stringy
     */
    public function camelCase()
    {
        return $this->studlyCase()->lcfirst();
    }

    /**
     * Convert a normally worded, studly cased, and/or camel cased string into a snake_cased_version_of_itself.
     *
     * @param Stringy|string $delimiter
     */
    public function snakeCase($delimiter = '_')
    {
        return static::create(
            preg_replace(
                '/(.)(?=\p{Lu})/u',
                static::create($delimiter)->prepend('$1'),
                $this->string
            ),
            'UTF-8'
        )->lower();
    }

    /**
     * Turn a studly-, snake- and/or camel cased word into a string of space-separated lowercase words.
     *
     * @return Stringy
     */
    public function uncase($snakeCaseDelimiter = '_')
    {
        return $this->snakeCase(' ')->replace($snakeCaseDelimiter, ' ')->unrepeat(' ');
    }

    /**
     * Convert a studly- or snake cased string into a Title Cased Version Of Itself:.
     *
     * @return Stringy
     */
    public function titleCase()
    {
        return static::create(mb_convert_case($this->string, MB_CASE_TITLE, 'UTF-8'), 'UTF-8');
    }

    /**
     * Turn this string into a url-encoed version of itself.
     *
     * The url-encoding is performed while the string is encoed as mb_internal_encoding.
     *
     * @return Stringy
     */
    public function urlencode()
    {
        return static::create($this->string());
    }

    public function slug($separator = '-', string $replaceBadCharWith = '')
    {
        return $this
            ->snakeCase()
            ->asciiSafe()
            ->transform(function ($stringy) use ($replaceBadCharWith, $separator) {
                return preg_replace_callback('/[^a-z0-9]/u', function ($matches) use ($replaceBadCharWith, $separator) {
                    if ($matches[0] == $separator) {
                        return $separator;
                    }

                    if (preg_match('/\s|-|_|:/', $matches[0])) {
                        return $separator;
                    }

                    return $replaceBadCharWith;
                }, $stringy->string);
            })
            ->transform(function ($stringy) use ($separator) {
                return preg_replace(
                    static::create($separator)->escapeForRegex('/')->includeIn('/%s+/u'),
                    $separator,
                    $stringy->string
                );
            });
    }

    /**
     * Ensure that all characters are ASCII.
     *
     * Convert non-ascii characters to ASCII if possible (i.e. 'ü' is converted to 'u' and 'æ' to 'ae').
     * Remove any characters that cannot be converted (i.e. most characters that are not based on the latin script).
     *
     * @return Stringy
     */
    public function asciiSafe()
    {
        return static::create(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $this->string), 'ASCII');
    }

    public function entityEncoded(int $flags = ENT_QUOTES | ENT_HTML5)
    {
        return $this->transform(function ($stringy) use ($flags) {
            return htmlentities(
                $stringy->string,
                $flags,
                'UTF-8'
            );
        });
    }

    public function escapeForHtml(int $flags = ENT_QUOTES | ENT_HTML5)
    {
        return $this->transform(function ($stringy) use ($flags) {
            return htmlspecialchars(
                $stringy->string,
                $flags,
                'UTF-8'
            );
        });
    }

    /**
     * Truncate a string in a pretty way.
     *
     * @param int            $maxLength  The maximum length of the string
     * @param Stringy|string $breakPoint The "signature" of the point where the string is to be "cut off".
     *                                   Default value is "" which means that we allow a cut-off anywhere.
     * @param Stringy|string $padding    The padding to add to the end of the string, to indicate it has been truncated
     *
     * @return Stringy|string the truncated string'
     */
    public function shorten($maxLength, $breakPoint = '', $padding = '…')
    {
        if ($this->length() <= $maxLength) {
            return clone $this;
        }

        $padding = self::create($padding);

        return $this->substring(0, $maxLength - $padding->length())
            ->removeAfter($breakPoint, -1)
            ->append($padding);
    }

    /**
     * Detect if the string ends with a cycle and return said cycle.
     *
     * The substring MUST repeat at least one full cycle and be more than $minLength characters long.
     * The substring does not have to repeat a whole number of times. For instance, it can repeat 2.4 times.
     *
     * A valid cycle would be:   "Start Foo Bar Baz Foo Bar Baz Foo"
     * An invalid cycle would be "Start Foo Bar Baz Foo Bar Baz End"
     *
     * @param int $minLength
     *
     * @return Stringy
     */
    public function cycle(int $minLength = 1)
    {
        $length = $this->length();

        for ($subLength = $length >> $minLength; $subLength > 0; --$subLength) {
            for ($offset = 0; $offset + $subLength < $length; ++$offset) {
                $substring = $this->substring($offset, $subLength);
                $restLength = $length - $offset;
                $possibleCycles = (int) ($restLength / $subLength);
                for ($cycles = $possibleCycles; $cycles > 1; --$cycles) {
                    $needle = $substring->repeat($cycles);
                    $rest = $this->substring($offset + $needle->length());

                    if (!$substring->startsWith($rest)) {
                        break;
                    }

                    if ($this->endsWith($needle->append($rest))) {
                        return $substring;
                    }
                }
            }
        }

        return static::create('', 'UTF-8');
    }

    /**
     * Get a random character from the content string.
     */
    public function randomChar()
    {
        $index = mt_rand(0, $this->length() - 1);

        return $this[$index];
    }

    /**
     * Get an array of characters in the content string.
     *
     * @return Stringy[]
     */
    public function characters() : array
    {
        return static::createMany(preg_split('//u', $this->string, -1, PREG_SPLIT_NO_EMPTY));
    }

    /**
     * Get the content string encoded as the system's default encoding.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->string();
    }

    /**
     * Get the debug info of the string.
     *
     * Useful for PSY shell debugging, var_dump, etc
     *
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'string' => $this->string(),
            'length' => $this->length(),
            'width' => mb_strwidth($this->string),
        ];
    }
}
