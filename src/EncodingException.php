<?php

namespace Moccalotto\Stringy;

use Exception;

class EncodingException extends StringyException
{
    /**
     * @var string
     */
    protected $encoding;

    /**
     * Constructor
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
