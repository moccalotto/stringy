# stringy

[![Build Status](https://travis-ci.org/moccalotto/stringy.svg)](https://travis-ci.org/moccalotto/stringy)

Easy, powerful and fluent string handling

## Installation

## Documentation

The `Stringy` object is immutable. This means that all operations that return a `Stringy` instance
will return a *new* instnace and not a modified version of the current instance. This means that
you will not have to `clone` the object if you need to do two different, branching operations 
on a given string. 

On the other hand, it is quite likely that you cannot identity-compare two stringy objects because
they are very short-lived. In cases where you need to check if two stringy objects contain the same
string, we suggest using the `is()` method. See the example below:

```php
$s0 = str('Foo');

$s1 = $s0->lower(),
$s2 = $s0->lower();

if ($s1 === $s2) {
    echo 'this code will not be executed';
}

if ($s1->is($s2)) {
    echo 'this will work just fine';
}

if (strval($s1) === strval($s2)) {
    echo 'this will also work';
}
```


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

```php
/**
 * Does the string contain $needle.
 *
 * @param Stringy|string $needle
 * @param int            $index
 *
 * @return bool
 */
public function contains($needle, int $index = 0) : bool
```

```php
/**
 * Does the string start with $needle ?
 *
 * @param Stringy|string $needle.
 *
 * @return bool
 */
public function startsWith($needle) : bool
```

```php
/**
 * Does the string end with $needle ?
 *
 * @param Stringy|string $needle.
 *
 * @return bool
 */
public function endsWith($needle) : bool
```


### Length (characters)

```php
/**
 * Get the length (in characters) of the content string.
 *
 * @return int
 */
public function length() : int
```

### Size (bytes)

```php
/**
 * Get the size (in bytes) of the content string.
 *
 * @return int
 */
public function size() : int
```


### Position of substring

```php
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
 *                  in case $needle was not found.
 */
public function positionOf($needle, int $index = 0)
```

### Getting words and characters from the string.

You can get individual characters via the php array accessor language construct like so:

```php
$str = str('foo bar baz');

$str[0]->string() === 'f'; // true
$str[1]->string() === 'o'; // true
$str[2]->string() === 'o'; // true

$str[-3]->string() === 'b' // true
$str[-2]->string() === 'a' // true
$str[-1]->string() === 'z' // true

$x = $str[30]; // OutOfRangeException
```

```php
/**
 * Convert the content string into an array of words.
 *
 * Note that this method will not correctly split kanji, thai, braille, and
 * other scripts where words are not necessarily clearly bounded.
 *
 * @return Stringy[]
 */
public function words() : array
```

```php
/**
 * Get an array of characters in the content string.
 *
 * @return Stringy[]
 */
public function characters() : array
```

### Map/transform the string

```php
/**
 * Transform the string.
 *
 * @param callable $callable a function with the signature (Stringy $string) : Stringy|string
 *
 * @return Stringy
 */
public function transform(callable $callable)
```

Examples:

```php
$str = str('foo bar baz');

$formatted = $str->transform(function (Stringy $stringy) {
    return str_rot13($stringy->asciiSafe());
});

print $formatted->string();
```

### Fetch a substring

```
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
```


### Fetch segments of a string based on searches.

Fetch the part of the string that comes after a given search term.

```php
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
```

Fetch the part of the string that comes before a given search term.
```php
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
```

Fetch the part of the string that resides between two search terms.

```php
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
```

### Remove parts of the string based on search terms.

Remove the part of the string that comes after the given search term.

```php
/**
 * Remove the substring that comes after the nth $needle.
 *
 * @param Stringy|string $needle
 * @param int            $index
 *
 * @return Stringy
 */
public function removeAfter($needle, int $index = 0)
```

Remove the part of the string that comes before the given search term.

```php
/**
 * Remove the substring that comes before the nth $needle.
 *
 * @param Stringy|string $needle
 * @param int            $index
 *
 * @return Stringy
 */
public function removeBefore($needle, int $index = 0)
```

### Repetition


Repeating a string

```php
/**
 * Repeat this string a number of times.
 *
 * @param int $times
 *
 * @return Stringy a string repeated $times times
 */
public function repeat(int $times)
```

```php
/**
 * Remove repititions of substrings.
 *
 * If a substring is repeated 2 or more times in a row within the
 * content string,reduce it to being there only once.
 *
 * str('hello    world')->unrepeat(' ') would be turned into 'hello world'
 * str('foo    bar    baz')->unrepeat(' ') would be turned into 'foo bar baz'
 *
 * @param Stringy|string $substring
 *
 * @return Stringy
 */
public function unrepeat($substring)
```

### Add padding

```php
/**
 * Append padding to the right hand side of the string.
 *
 * @param int            $totalLengthOfResult the total length of the result string
 * @param Stringy|string $padding             the padding character to use
 *
 * @return Stringy
 */
public function rightPadded(int $totalLengthOfResult, $padding = ' ')
```

```php
/**
 * Prepend padding to the left hand side of the string.
 *
 * @param int            $totalLengthOfResult the total length of the result string
 * @param Stringy|string $padding             the padding character to use
 *
 * @return Stringy
 */
public function leftPadded(int $totalLengthOfResult, $padding = ' ')
```

```php
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
```

### Remove Padding

```php
/**
 * Remove all instances of $needle from the beginning of the content string.
 *
 * @param Stringy|string $needle the substring to remove from the
 *                               beginning of the content string
 *
 * @return Stringy
 */
public function leftTrim($needle)
```

```php
/**
 * Remove all instances of $needle from the end of the content string.
 *
 * @param Stringy|string $needle the substring to remove from the
 *                               end of the content string
 *
 * @return Stringy
 */
public function rightTrim($needle)
```

```php
/**
 * Remove all occurances of $strings from the beginning of the content string.
 *
 * @param array $strings An array of strings or Stringy objects.
 *
 * @return Stringy
 */
public function leftTrimAll(array $strings)
```

```php
/**
 * Remove all occurances of $strings from the end of the content string.
 *
 * @param array $strings An array of strings or Stringy objects.
 *
 * @return Stringy
 */
public function rightTrimAll(array $strings)
```

### Adjust Casing

```php
/**
 * Convert the content string to uppercase.
 *
 * @return Stringy
 */
public function upper()
```

```php
/**
 * Convert the content string to lowercase.
 *
 * @return Stringy
 */
public function lower()
```

```php
/**
 * Turn the first letter of every word uppercase.
 *
 * Does not interfere with the casing of the rest of the letters.
 * Words are defined as strings separated by a word-boundary (such as white space, dashes, dots, etc.)
 *
 * @return Stringy
 */
public function ucwords()
```

```php
/**
 * Turn the first letter of every word lowercase.
 *
 * Does not interfere with the casing of the rest of the letters.
 * Words are defined as strings separated by a word-boundary (such as white space, dashes, dots, etc.)
 *
 * @return Stringy
 */
public function lcwords()
```

```php
/**
 * Turn first letter uppercased.
 *
 * Do not change the casing of the rest of the letters.
 */
public function ucfirst()
```

```php
/**
 * Turn first letter lowercased.
 *
 * Do not change the casing of the rest of the letters.
 */
public function lcfirst()
```
