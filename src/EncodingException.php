<?php

/*
 * This file is part of the Stringy package.
 *
 * @package Stringy
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Moccalotto\Stringy;

use Exception;

/**
 * This class represents an exception that occurs when a string
 * cannot be converted from one encoding to another.
 */
class EncodingException extends StringyException
{
    /**
     * @var string
     */
    protected $encoding;

    /**
     * Constructor.
     *
     * @param string    $message  The message of the exception
     * @param string    $string   The content string
     * @param string    $encoding The attempted target encoding
     * @param Exception $previous Any previous exception that might have triggered $this
     */
    public function __construct(string $message, string $string, string $encoding, Exception $previous = null)
    {
        $this->encoding = $encoding;
        $this->string = $string;

        parent::__construct(
            sprintf('Encoding exception: %s', $message),
            $string,
            $encoding,
            $previous
        );
    }
}
