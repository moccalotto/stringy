<?php

namespace Moccalotto\Stringy;

/**
 * Class for creating serializable stack traces.
 */
class Tracer
{
    /**
     * Simplify a stack trace.
     *
     * @param array $trace a stack trace as retreived via debug_backtrace or $exception->getTrace()
     *
     * @return array an array reminiscent of the original stack trace with a few exceptions:
     *               - The 'object' entry is not present.
     *               - The array is ALWAYS serializable.
     *               - All entries in the $args array are strings describing the value.
     */
    public function simplify(array $trace) : array
    {
        return array_map([$this, 'simplifySingleTraceElement'], $trace);
    }

    /**
     * Format a single entry in a stack trace so that it becomes serializable.
     *
     * @param array $traceElement
     *
     * @return array
     */
    public function simplifySingleTraceElement(array $traceElement) : array
    {
        return array_filter([
            'file' => $traceElement['file'],
            'line' => (int) $traceElement['line'],
            'class' => $traceElement['class'] ?? null,
            'function' => $traceElement['function'] ?? null,
            'type' => $traceElement['type'] ?? null,
            'args' => $this->stringifyArgs($traceElement['args'] ?? []),
            'call' => $this->makeCallString($traceElement),
        ], function ($entry) {
            return $entry !== null;
        });
    }

    public function makeCallString(array $traceElement) : string
    {
        if (isset($traceElement['type'])) {
            return vsprintf('%s%s%s(%s)', [
                $traceElement['class'],
                $traceElement['type'],
                $traceElement['function'],
                implode(', ', $this->stringifyArgs($traceElement['args'] ?? null)),
            ]);
        }

        if (isset($traceElement['function'])) {
            return vsprintf('%s(%s)', [
                $traceElement['function'],
                implode(', ', $this->stringifyArgs($traceElement['args'] ?? null)),
            ]);
        }

        return vsprintf('%s::%s', [
            $traceElement['file'],
            $traceElement['line'],
        ]);
    }

    /**
     * Turn a list of function arguments into a list of strings.
     *
     * @param string[] $args
     *
     * @return string[] An array where all the values are strings that describe each parameter
     */
    public function stringifyArgs(array $args) : array
    {
        return array_map(function ($arg) {
            return Dumper::stringify($arg)->string();
        }, $args);
    }
}
