# stringy

[![Build Status](https://travis-ci.org/moccalotto/stringy.svg)](https://travis-ci.org/moccalotto/stringy)

Easy, powerful and fluent string handling

## Installation

## Documentation

{:toc}

### Constructors

Normal constructor.

```php
/**
 * Constructor.
 *
 * @param string      $string          the string contents of the Stringy object
 * @param string|null $currentEncoding the current encoding of $string
 */
public function __construct(string $string = '', string $currentEncoding = null)
```

Static factory.

```php
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
```

Helper factory function.

```php
/**
 * Stringify a string
 *
 * @param Stringy|string $string   The string to be Stringyfied.
 *                                 If $string is a (descendant of) Stringy, it will
 *                                 be cloned and converted to using $encoding.
 * @param string|null    $encoding The encoding of the $string
 *
 * @return Stringy
 */
function str($string = '', string $encoding = null) : Stringy
```


Create a random string.

```php
/**
 * Factory for a random string of a given length.
 *
 * @return Stringy
 */
public static function random($length)
```


### Get the content string.

```php
/**
 * Get the content string of this object encoded as $encoding.
 *
 * @param string|null $encodedAs The encoding to get the string as. NULL = mb_internal_encoding
 *
 * @return string
 */
public function string($encodedAs = null) : string
```

### String comparison

```php
/**
 * Compare this string to another.
 *
 * @param Stringy|string $string
 *
 * @return bool only true if the two strings are equal using strict (===) comparison.
 */
public function is($string) : bool
```

/**
 * Get the length (in characters) of the content string.
 *
 * @return int
 */
public function length() : int

/**
 * Get the size (in bytes) of the content string.
 *
 * @return int
 */
public function size() : int

/**
 * Does the string contain $needle.
 *
 * @param Stringy|string $needle
 * @param int            $index
 *
 * @return bool
 */
public function contains($needle, int $index = 0) : bool

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

/**
 * Transform the string.
 *
 * @param callable $callable a function with the signature (Stringy $string) : Stringy|string
 *
 * @return Stringy
 */
public function transform(callable $callable)

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
 * @return Stringy a clone of $this with a content string containing the
 *                 part that comes after $needle. If $needle is not
 *                 found, an empty Stringy is returned
 */
public function after($needle, int $index = 0)

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
 * @return Stringy a clone of $this with a content string containing the
 *                 part that comes after $needle. If $needle is not
 *                 found, an empty Stringy is returned
 */
public function before($needle, int $index = 0)

/**
 * Remove the substring that comes after the nth $needle.
 *
 * @param Stringy|string $needle
 * @param int            $index
 *
 * @return Stringy
 */
public function removeAfter($needle, int $index = 0)

/**
 * Remove the substring that comes before the nth $needle.
 *
 * @param Stringy|string $needle
 * @param int            $index
 *
 * @return Stringy
 */
public function removeBefore($needle, int $index = 0)

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

/**
 * Repeat this string a number of times.
 *
 * @param int $times
 *
 * @return Stringy a string repeated $times times
 */
public function repeat(int $times)

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

/**
 * Append padding to the right hand side of the string.
 *
 * @param int            $totalLengthOfResult the total length of the result string
 * @param Stringy|string $padding             the padding character to use
 *
 * @return Stringy
 */
public function rightPadded(int $totalLengthOfResult, $padding = ' ')

/**
 * Prepend padding to the left hand side of the string.
 *
 * @param int            $totalLengthOfResult the total length of the result string
 * @param Stringy|string $padding             the padding character to use
 *
 * @return Stringy
 */
public function leftPadded(int $totalLengthOfResult, $padding = ' ')

/**
 * Add padding to both sides of the content string such that it becomes centered.
 *
 * @param int            $totalLengthOfResult the total length of the result string
 * @param Stringy|string $padding             the padding character to use
 * @param string         $tieBreak            Can either be "left" or "right".
 *                                            In case the content string cannot be
 *                                            centered, should the content string
 *                                            be to the left of center or the right of
 *                                            center.
 *
 * @return Stringy
 */
public function centered(int $totalLengthOfResult, $padding = ' ', $tieBreak = 'left')

/**
 * Convert the content string to uppercase.
 *
 * @return Stringy
 */
public function upper()

/**
 * Convert the content string to lowercase.
 *
 * @return Stringy
 */
public function lower()

/**
 * Turn the first letter of every word uppercase.
 *
 * Does not interfere with the casing of the rest of the letters.
 * Words are defined as strings separated by a word-boundary (such as white space, dashes, dots, etc.)
 *
 * @return Stringy
 */
public function ucwords()

/**
 * Turn the first letter of every word lowercase.
 *
 * Does not interfere with the casing of the rest of the letters.
 * Words are defined as strings separated by a word-boundary (such as white space, dashes, dots, etc.)
 *
 * @return Stringy
 */
public function lcwords()

/**
 * Turn first letter lowercased.
 *
 * Do not change the casing of the rest of the letters.
 */
public function lcfirst()

/**
 * Turn first letter uppercased.
 *
 * Do not change the casing of the rest of the letters.
 */
public function ucfirst()

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

/**
 * Remove a substring. (I.e. replace $search with an empty string).
 *
 * @param Stringy|string $search the substring to be removed
 *
 * @return Stringy
 */
public function remove($search)

/**
 * Remove a number of substrings.
 *
 * @param array $searches an array of strings (or Stringy objects)
 *                        to be removed
 *
 * @return Stringy
 */
public function removeMany(array $searches)

/**
 * Escape a string so it can be used in a regular expression.
 *
 * @param Stringy|string $delimiter the delimiter used to start and
 *                                  terminate the regular expression.
 *                                  Usually the forward slash will be
 *                                  used to encluse regular expression.
 *
 * @return Stringy|string
 */
public function escapeForRegex($delimiter)

/**
 * Append a string to $this.
 *
 * @param Stringy|string $other
 *
 * @return Stringy a clone of $this where contents of $other is prepended
 */
public function append($other)

/**
 * Prepend a string to $this.
 *
 * @param Stringy|string $other
 *
 * @return Stringy a clone of $this where contents of $other is prepended
 */
public function prepend($other)

/**
 * Surround the content string with two other strings.
 *
 * Essentially the same as calling prepend and append in one single operation.
 *
 * @param Stringy|string      $left  the string to be prepended to the content string
 * @param Stringy|string|null $right The string to be appended to the content string.
 *                                   if NULL, the $left string will be used.
 *
 * @return Stringy
 */
public function surroundWith($left, $right = null)

/**
 * Remove all instances of $needle from the beginning of the content string.
 *
 * @param Stringy|string $needle the substring to remove from the
 *                               beginning of the content string
 *
 * @return Stringy
 */
public function leftTrim($needle)

/**
 * Remove all instances of $needle from the end of the content string.
 *
 * @param Stringy|string $needle the substring to remove from the
 *                               end of the content string
 *
 * @return Stringy
 */
public function rightTrim($needle)

/**
 * Include the content string in another, using sprintf syntax.
 *
 * @see http://php.net/manual/function.sprintf.php
 *
 * @param Stringy|string $string      The sprintf-template string to use.
 *                                    Must include at least one "%s"
 * @param array          $extraParams extra params to use in the sprintf operation
 *
 * @return Stringy
 */
public function includeIn($string, array $extraParams = [])

/**
 * Use the content string as a sprintf-template string.
 *
 * @see http://php.net/manual/function.vsprintf.php
 *
 * @param array $args an array of args to use á la vsprintf
 *
 * @return Stringy
 */
public function format(array $args)

public function leftTrimAll(array $strings)

public function rightTrimAll(array $strings)

public function startsWith($needle) : bool

public function endsWith($needle) : bool

public function reverse()

public function glue(array $strings)

/**
 * Limit the length of the content string by truncating it.
 *
 * @param int $length the maximum length (in characters) of the content string
 *
 * @return Stringy
 */
public function limit(int $length)

/**
 * Convert the content string into an array of words.
 *
 * Note that this method will not correctly split kanji, thai, braille, and
 * other scripts where words are not necessarily clearly bounded.
 *
 * @return Stringy[]
 */
public function words() : array

/**
 * Turn the normally worded (or snakeCased) string into a StudlyCasedVersionOfItself.
 *
 * @return Stringy
 */
public function studlyCase()

/**
 * Turn the normally worded (or snakeCased) string into a camelCasedVersionOfItself.
 *
 * @return Stringy
 */
public function camelCase()

/**
 * Convert a normally worded, studly cased, and/or camel cased string into a snake_cased_version_of_itself.
 *
 * @param Stringy|string $delimiter
 */
public function snakeCase($delimiter = '_')

/**
 * Turn a studly-, snake- and/or camel cased word into a string of space-separated lowercase words.
 *
 * @param Stringy|string $snakeCaseDelimiter the delimiter used to separate snake-cased words in the
 *                                           content string
 *
 * @return Stringy
 */
public function uncase($snakeCaseDelimiter = '_')

/**
 * Convert a studly- or snake cased string into a Title Cased Version Of Itself:.
 *
 * @return Stringy
 */
public function titleCase()

/**
 * Turn this string into a url-encoed version of itself.
 *
 * The url-encoding is performed while the string is encoed as mb_internal_encoding.
 *
 * @return Stringy
 */
public function urlencode()

/**
 * Turn this string into a url-friendly "slug".
 *
 * @see https://en.wikipedia.org/wiki/Semantic_URL#Slug
 *
 * @param Stringy|string $separator          seperator used to separate words
 * @param Stringy|string $replaceBadCharWith if a non-translatable character is found,
 *                                           replace it with this character
 *
 * @return Stringy
 */
public function slug($separator = '-', string $replaceBadCharWith = '')

/**
 * Ensure that all characters are ASCII.
 *
 * Convert non-ascii characters to ASCII if possible (i.e. 'ü' is converted to 'u' and 'æ' to 'ae').
 * Remove any characters that cannot be converted (i.e. most characters that are not based on the latin script).
 *
 * @return Stringy
 */
public function asciiSafe()

/**
 * Convert all non-ASCII  characters into html entities.
 *
 * @see http://php.net/manual/function.htmlentities.php
 *
 * @param int $flags See php documentation for htmlentities
 *
 * @return Stringy
 */
public function entityEncoded(int $flags = ENT_QUOTES | ENT_HTML5)

/**
 * Escape this string for use as html text.
 *
 * @see http://php.net/manual/function.htmlspecialchars.php
 *
 * @param int $flags See php documentation for htmlspecialchars
 *
 * @return Stringy
 */
public function escapeForHtml(int $flags = ENT_QUOTES | ENT_HTML5)

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

/**
 * Detect if the string ends with a repeating pattern.
 *
 * The pattern MUST appear at least $minCycles number of times in a row.
 * The pattern MUST be at least $minChars characters long.
 * The string must end with part of the pattern.
 *
 * In the string: "Start Foo Bar Baz Foo Bar Baz Foo", the pattern would be " Foo Bar Baz"
 * In the string: "Start Foo Bar Baz Foo Bar Baz End", there would be no pattern because the string
 * does not end with the part of the pattern.
 *
 * @param int $minChars  The minimum length (in characters) of the pattern.
 * @param int $minCycles The minimum number of full cycles the pattern must appear in.
 *
 * @return Stringy
 */
public function cycle(int $minChars = 1, int $minCycles = 2)

/**
 * Get a random character from the content string.
 *
 * @return Stringy
 */
public function randomChar()

/**
 * Get an array of characters in the content string.
 *
 * @return Stringy[]
 */
public function characters() : array

/**
 * Get the content string encoded as the system's default encoding.
 *
 * @return string
 */
public function __toString()

/**
 * Get the debug info of the string.
 *
 * Useful for PSY shell debugging, var_dump, etc
 *
 * @return array
 */
public function __debugInfo()
