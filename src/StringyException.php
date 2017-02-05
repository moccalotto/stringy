<?php

namespace Moccalotto\Stringy;

use Exception;

class StringyException extends Exception
{
    /**
     * @var string
     */
    protected $string;

    /**
     * @var string
     */
    protected $encoding;

    /**
     * Constructor
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
