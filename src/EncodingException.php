<?php

namespace Moccalotto\Stringy;

use Exception;

class EncodingException extends StringyException
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
    public function __construct(string $string, string $encoding, Exception $previous = null)
    {
        $this->encoding = $encoding;
        $this->string = $string;

        parent::__construct(
            sprintf('The string does not satisfy the encoding »%s«', $encoding),
            $string,
            $encoding,
            $previous
        );
    }
}
