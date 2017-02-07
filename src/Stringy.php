<?php

declare(strict_types=1);

namespace Moccalotto\Stringy;

use ArrayAccess;
use UnexpectedValueException;

/**
 * TODO:
 * shorten
 * urlencode
 * camelCase
 * studlyCase
 * titleCase
 * snakeCase
 * uncase (turn cased string into spaced words)
 * random()
 * singular
 * plural
 * https://github.com/ICanBoogie/Inflector.
 */
class Stringy implements ArrayAccess
{
    use Traits\HasArrayAccess;

    /**
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

    public static function createMany(array $strings, $encoding = null)
    {
        return array_map(function ($string) use ($encoding) {
            return static::create($string, $encoding);
        }, $strings);
    }

    public static function mapMany(array $strings, callable $callable) : array
    {
        return array_map($callable, static::createMany($strings));
    }

    protected static function utf8(string $string, string $encoding)
    {
        if (!in_array($encoding, mb_list_encodings())) {
            throw new EncodingException('Encoding not supported', $string, $encoding);
        }

        if (!mb_check_encoding($string, $encoding)) {
            throw new EncodingException('Invalid string', $string, $encoding);
        }

        $string = mb_convert_encoding($string, 'UTF-8', $encoding);

        return $string;
    }

    /**
     * Constructor.
     */
    public function __construct(string $string = '', string $currentEncoding = null)
    {
        $this->string = static::utf8($string, $currentEncoding ?? mb_internal_encoding());
    }

    /**
     * Get the inner string of this object encoded as $encoding.
     *
     * @param $encodedAs The encoding to get the string as. NULL = mb_internal_encoding
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
            return null;
        }

        $matchCount = count($matches[0]);

        // index is too high
        if ($index >= $matchCount) {
            return null;
        }

        // index is negative, correct it into a positive index
        if ($index < 0) {
            $index = $matchCount + $index;
        }

        // index was so low it could not be correct (i.e. too few matches)
        if ($index < 0) {
            return null;
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
     * Return the text that comes after $start and before $stop.
     *
     * @param Stringy|string $start
     * @param Stringy|string $stop
     * @param int            $pairIndex search for the nth pair and
     *                       find what lies between those strings.
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
     * @param int $start
     * @param int $length
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

    public function includeIn($string)
    {
        return static::create($string)->format([$this]);
    }

    public function format(array $args)
    {
        $result = @vsprintf($this, static::createMany($args));

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
        }, $strings))->includeIn('/(^%s)+/u');

        return static::create(preg_replace($regex, '', $this->string));
    }

    public function rightTrimAll(array $strings)
    {
        $regex = static::create('|')->glue(static::mapMany($strings, function ($string) {
            return $string->escapeForRegex('/');
        }))->includeIn('/(%s)+$/u');

        return static::create(preg_replace($regex, '', $this->string), 'UTF-8');
    }

    public function startsWith($needle) : bool
    {
        return $this->positionOf($needle) === 0;
    }

    public function endsWith($needle) : bool
    {
        $pos = $this->positionOf($needle);

        if ($pos === null) {
            return false;
        }

        return $pos + static::create($needle)->length() === $this->length();
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

    public function limit(int $length)
    {
        return $this->substring(0, $length);
    }

    public function slug($separator = '-', string $replaceBadCharWith = '')
    {
        return $this
            ->lower()
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
                return preg_replace(static::create($separator)->escapeForRegex('/')->surroundWith('/', '+/u'), $separator, $stringy->string);
            });
            ;
    }

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

    public function characters() : array
    {
        return static::createMany(preg_split('//u', $this->string, -1, PREG_SPLIT_NO_EMPTY));
    }

    public function __toString()
    {
        return $this->string();
    }

    public function __debugInfo()
    {
        return [
            'string' => $this->string(),
            'length' => $this->length(),
            'width'  => mb_strwidth($this->string),
        ];
    }
}
