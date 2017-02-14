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
 * Exception during a Stringy operation.
 */
class StringyException extends Exception
{
    /**
     * The original content string.
     *
     * @var string
     */
    protected $string;

    /**
     * The original enconding.
     *
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
    public function __construct(string $message, string $string, string $encoding = 'UTF-8', Exception $previous = null)
    {
        $this->encoding = $encoding;
        $this->string = $string;

        parent::__construct($message, 0, $previous);
    }

    public function getEncoding() : string
    {
        return $this->encoding;
    }

    public function getString() : string
    {
        return $this->getString();
    }
}
