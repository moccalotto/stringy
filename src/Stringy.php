<?php

namespace Moccalotto\Stringy;

use ArrayAccess;

class Stringy implements ArrayAccess
{
    use Traits\HasArrayAccess;

    /**
     * @var string
     */
    protected $string;

    /**
     * @var string
     */
    protected $encoding;

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
        if ($string instanceof static) {
            return $string->withEncoding($encoding ?? $string->encoding);
        }

        // support for descendants.
        if ($string instanceof self) {
            return new static($string->string, $string->encoding);
        }

        return new static($string, $encoding);
    }

    /**
     * Constructor.
     */
    public function __construct(string $string = '', $encoding = null)
    {
        $this->string = $string;
        $this->encoding = $encoding ?? mb_internal_encoding();

        if (!mb_check_encoding($string, $this->encoding)) {
            throw new EncodingException($string, $encoding);
        }
    }

    /**
     * Create a new Stringy object that has the same encoding as this string.
     *
     * @param string $string   The string that is to be the base of the new Stringy object
     * @param string $encoding The current encoding of the string
     *
     * @return Stringy object that has $string as a base and the same encoding as $this
     */
    protected function createCompatible($string, $encoding = null)
    {
        return static::create($string, $encoding)->withEncoding($this->encoding);
    }

    /**
     * Get the inner string of this object.
     *
     * @return string
     */
    public function string() : string
    {
        return $this->string;
    }

    /**
     * Get the encoding of the inner string.
     *
     * @return string
     */
    public function encoding() : string
    {
        return $this->encoding;
    }

    /**
     * Get the length (in characters) of the inner string.
     *
     * @return int
     */
    public function length() : int
    {
        return mb_strlen($this->string, $this->encoding);
    }

    /**
     * Does the string match the given regex?.
     *
     * @see http://php.net/manual/function.preg-match.php
     *
     * @param string $regex
     *
     * @return bool
     */
    public function matches($regex) : bool
    {
        return (bool) preg_match($regex, $this->string);
    }

    /**
     * Does the string contain $needle.
     *
     * @param Stringy|string $needle
     *
     * @return bool
     */
    public function contains($needle)
    {
        return $this->strpos($needle) !== false;
    }

    /**
     * Get the position of the $needle string.
     *
     * @see http://php.net/manual/function.mb-strpos.php
     *
     * @param Stringy|string $needle
     *
     * @return int|false
     */
    public function strpos($needle, int $offset = 0)
    {
        return mb_strpos(
            $this->string,
            $this->createCompatible($needle),
            $offset,
            $this->encoding
        );
    }

    /**
     * Find position of last occurrence of a string in a string.
     *
     * @see http://php.net/manual/function.mb-strrpos.php
     *
     * @param Stringy|string $needle
     *
     * @return int|false
     */
    public function strrpos($needle, int $offset = 0)
    {
        return mb_strrpos(
            $this->string,
            $this->createCompatible($needle),
            $offset,
            $this->encoding
        );
    }

    /**
     * Find a the position of the (first or last) occurrence of a string.
     *
     * @param Stringy|string $needle  The string to search for
     * @param string         $context Must be either 'first' or 'last'
     *                                If context is 'first', we search from the beginning of the string,
     *                                If context is 'last', we search from the end of the string
     *
     * @return int|bool The position of the first character of the $needle found.
     *                  FALSE if $needle could not be found
     */
    public function positionOf($needle, string $context)
    {
        $methodMap = [
            'first' => 'strpos',
            'last' => 'strrpos',
        ];

        if (!isset($methodMap[$context])) {
            throw new StringyException(vsprintf('Bad Context »%s«. It must be one of [%s]', [
                $context,
                implode(', ', $methodMap),
            ]));
        }

        $method = $methodMap[$context];

        return $this->$method($needle);
    }

    /**
     * Get the part of the string that comes after $needle.
     *
     * @example: str('foo bar baz')->after('foo ') == 'bar baz'
     *
     * @param Stringy|string $needle
     * @param string         $context Must be either 'first' or 'last'
     *                                If context is 'first', we search from the beginning of the string,
     *                                If context is 'last', we search from the end of the string
     *
     * @return Stringy a clone of $this with a inner string containing the
     *                 part that comes after $needle. If $needle is not
     *                 found, an empty Stringy is returned
     */
    public function after($needle, $context = 'first')
    {
        $other = $this->createCompatible($needle);

        if ($other->length() === 0) {
            return $this->clone();
        }

        $pos = $this->positionOf($needle, $context);

        if ($pos === false) {
            return $this->clone('');
        }

        return $this->substring($pos + $other->length());
    }

    /**
     * Get the part of the string that comes after $needle.
     *
     * @example: str('foo bar baz')->before('foo ') == 'bar baz'
     *
     * @param Stringy|string $needle
     * @param string         $context Must be either 'first' or 'last'
     *                                If context is 'first', we search from the beginning of the string,
     *                                If context is 'last', we search from the end of the string
     *
     * @return Stringy a clone of $this with a inner string containing the
     *                 part that comes after $needle. If $needle is not
     *                 found, an empty Stringy is returned
     */
    public function before($needle, $context = 'first')
    {
        $other = $this->createCompatible($needle);

        if ($other->length() === 0) {
            return $this->clone();
        }

        $pos = $this->positionOf($needle, $context);

        if ($pos === false) {
            return $this->clone('');
        }

        return $this->substring(0, $pos);
    }

    /**
     * Return the text that comes after $start and before $stop.
     *
     * @param Stringy|string $start
     * @param Stringy|string $stop
     *
     * @return Stringy the string between $start and $stop
     */
    public function between($start, $stop)
    {
        return $this->after($start)
            ->before($stop);
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
        return $this->transform(function ($string) use ($start, $length) {
            return $this->clone(mb_substr($string, $start, $length, $this->encoding));
        });
    }

    /**
     * Repeat this string a number of times.
     *
     * @param int $times
     *
     * @return Stringy a string repeated $times times.
     */
    public function repeat(int $times)
    {
        if ($times == 0) {
            return $this->clone('');
        }

        if ($times < 0) {
            throw new StringyException('Cannot repeat a string a negative number of times');
        }

        return $this->clone(
            str_repeat($this->string, $times)
        );
    }

    public function rightPadded(int $totalLengthOfResult, $padding = ' ')
    {
        $padding = $this->createCompatible($padding)[0];

        $paddingLength = $totalLengthOfResult - $this->length();

        if ($paddingLength <= 0) {
            return $this->clone();
        }

        return $this->append($padding->repeat($paddingLength));
    }


    public function leftPadded(int $totalLengthOfResult, $padding = ' ')
    {
        $padding = $this->createCompatible($padding)[0];

        $paddingLength = $totalLengthOfResult - $this->length();

        if ($paddingLength <= 0) {
            return $this->clone();
        }

        return $this->prepend($padding->repeat($paddingLength));
    }

    public function centered(int $totalLengthOfResult, $padding = ' ')
    {
        return $this->leftPadded(floor($totalLengthOfResult / 2), $padding)
            ->rightPadded($totalLengthOfResult, $padding);
    }

    /**
     * Alter the inner string.
     *
     * @param callable $callable A function with the signature:
     *                           function(string $string, [Stringy $original]) : string
     *
     * @return Stringy a clone of $this with the result of $callable as its contents
     */
    public function transform(callable $callable)
    {
        return $this->clone($callable($this->string, $this));
    }

    /**
     * Transform this string along with another string, but asure that
     * the other string has the same encoding as this string.
     *
     * @param Stringy|string $other
     * @param callable       $callable A function with the signature:
     *                                 function(string $string, string $other, [Stringy $original]) : string
     *
     * @return Stringy a clone of $this with the result of $callable as its contents
     */
    public function transformWithOther($other, callable $callable)
    {
        return $this->clone($callable(
            $this->string,
            $this->createCompatible($other),
            $this
        ));
    }

    public function clone(string $string = null, string $encoding = null)
    {
        return new static(
            $string ?? $this->string,
            $encoding ?? $this->encoding
        );
    }

    public function toUpper()
    {
        return $this->transform(function ($string, $original) {
            return mb_strtoupper($string, $original->encoding());
        });
    }

    public function toLower()
    {
        return $this->transform(function ($string, $original) {
            return mb_strtolower($string, $original->encoding());
        });
    }

    /**
     * Split the string into segments.
     *
     * @see http://php.net/manual/function.explode.php
     *
     * @param string $pattern
     * @param int    $limit
     *
     * @return Stringy[] array of strings as Stringy instances
     */
    public function explode(string $pattern, int $limit = PHP_INT_MAX) : array
    {
        return array_map(function ($string) {
            return $this->clone($string);
        }, explode(
            $this->createCompatible($pattern),
            $this->string,
            $limit
        ));
    }

    /**
     * Replace all occurrences of the $search string with the $replacement string.
     *
     * @see http://php.net/manual/function.str-replace.php
     *
     * @param string $search
     * @param string $replace
     *
     * @return Stringy
     */
    public function replace(string $search, string $replace)
    {
        return $this->clone(str_replace(
            $this->createCompatible($search),
            $this->createCompatible($replace),
            $this->string
        ));
    }

    public function append(string $other)
    {
        return $this->transformWithOther($other, function ($string, $other) {
            return $string.$other;
        });
    }

    public function prepend(string $other)
    {
        return $this->transformWithOther($other, function ($string, $other) {
            return $other.$string;
        });
    }

    public function limit(int $length)
    {
        return $this->substring(0, $length);
    }

    public function withEncoding(string $encoding)
    {
        $converted = mb_convert_encoding(
            $this->string,
            $encoding,
            $this->encoding
        );

        return $this->clone($converted, $encoding);
    }

    public function toUtf8()
    {
        return $this->withEncoding('UTF-8');
    }

    public function toSlug($separator = '-', string $replaceBadCharWith = '')
    {
        return $this->toUtf8()
            ->toLower()
            ->toAscii()
            ->transform(function ($string) use ($separator) {

                // convert all spaces to the $separator character.
                return preg_replace('/(\s|_)+/m', $separator, $string);
            })->transform(function ($string) use ($replaceBadCharWith) {

                // Convert any non-allowed character into the $replaceBadCharWith
                return preg_replace('/[^a-z0-9-]/', $replaceBadCharWith, $string);
            });
    }

    public function toAscii()
    {
        return $this->toUtf8()->transform(function ($string) {
            return iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
        })->clone(null, 'ASCII');
    }

    public function toHtmlEntities()
    {
        return $this->withEncoding('HTML-ENTITIES');
    }

    public function toSystemEncoding()
    {
        return $this->withEncoding(mb_internal_encoding());
    }

    public function characters() : array
    {
        $utf8 = $this->toUtf8()->string;

        return array_map(
            function ($character) {
                return $this->createCompatible($character, 'UTF-8');
            },
            preg_split('//u', $utf8, -1, PREG_SPLIT_NO_EMPTY)
        );
    }

    public function __toString()
    {
        return $this->toSystemEncoding()->string;
    }

    public function __debugInfo()
    {
        return [
            'string' => $this->string,
            'encoding' => $this->encoding,
        ];
    }
}
