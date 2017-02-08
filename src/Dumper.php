<?php

declare(strict_types=1);

namespace Moccalotto\Stringy;

use Throwable;

/**
 * Turns variables into a easy-debuggable textual representation.
 */
class Dumper
{
    /**
     * @var int
     */
    protected $maxStringLength;

    /**
     * Constructor.
     *
     * @param int $maxStringLength Max length of dumped strings.
     *     this does not mean that the outputted string cannot be longer than
     *     @maxStringLength, it just means that if we dump a string, that particular
     *     string cannot be longer than $maxStringLength.
     *     A string is usually dumped like so:
     *     String("kim")
     */
    public function __construct(int $maxStringLength)
    {
        $this->maxStringLength = $maxStringLength;
    }

    /**
     * Static helper
     */
    public static function stringify($variable, int $maxStringLength = PHP_INT_MAX) : Stringy
    {
        // for some reason, phpcs gives the follwing error:
        // "Arguments with default values must be at the end of the argument list"
        // We ignore that :-)
        // @codingStandardsIgnoreLine
        return (new static($maxStringLength))->dump($variable);
    }

    /**
     * Turn a scalar into a string.
     */
    protected function scalar($scalar) : Stringy
    {
        if (is_int($scalar)) {
            return Stringy::create('Int(%d)')->format([$scalar]);
        }

        if (is_float($scalar)) {
            return Stringy::create('Float(%s)')->format([(float) $scalar]);
        }

        if (is_bool($scalar)) {
            return Stringy::create('Bool(%s)')->format([$scalar ? 'TRUE' : 'FALSE']);
        }

        return Stringy::create($scalar)
            ->shorten($this->maxStringLength)
            ->replace("\r", ' ')
            ->replace("\n", ' ')
            ->includeIn('String("%s")');
    }

    protected function callable($callable) : Stringy
    {
        if (is_string($callable)) {
            return Stringy::create('Callable(%s)')->format([$callable]);
        }

        if (is_object($callable)) {
            return Stringy::create(get_class($callable))->includeIn('Callable(%s->__invoke)');
        }

        if (is_array($callable)) {
            [$classOrObject, $method] = $callable;

            return is_object($classOrObject)
                ? Stringy::create('Callable(%s->%s)')->format([get_class($classOrObject), $method])
                : Stringy::create('Callable(%s::%s)')->format([$classOrObject, $method]);
        }
    }

    protected function resource($resource)
    {
        return Stringy::create('Resource(#%s: %s)')
        ->format([
            Stringy::create((string) $resource)->replace('Resource id #', ''),
            get_resource_type($resource),
        ]);
    }

    protected function object($object)
    {
        if ($object instanceof Throwable) {
            return Stringy::create('Object(%s: %s:%s)')
                ->format([
                    get_class($object),
                    $object->getFile(),
                    $object->getLine(),
                ]);
        }

        if (method_exists($object, '__toString')) {
            return Stringy::create((string) $object)
                ->shorten($this->maxStringLength, PHP_EOL)
                ->replace("\r", ' ')
                ->replace("\n", ' ')
                ->includeIn('Object(%2$s: %1$s)', [get_class($object)]);
        }

        return Stringy::create('Object(%s)')->format([get_class($object)]);
    }

    protected function array($array)
    {
        return Stringy::create('Array(%d entries)')->format([count($array)]);
    }

    public function dump($variable)
    {
        if (is_scalar($variable)) {
            return $this->scalar($variable);
        }

        // check for callable before objects and arrays.
        // callables can be strings, objects or arrays,
        // but we do not want to interpret strings like
        // "strlen", "count" as callables.
        if (is_callable($variable)) {
            return $this->callable($variable);
        }

        if (is_null($variable)) {
            return Stringy::create('NULL');
        }

        if (is_resource($variable)) {
            return $this->resource($variable);
        }

        if (is_object($variable)) {
            return $this->object($variable);
        }

        if (is_array($variable)) {
            return $this->array($variable);
        }

        throw new RuntimeException(sprintf(
            'Unknown type: %s',
            gettype($variable)
        ));
    }
}
