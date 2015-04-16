<?php

namespace MyBB\Parser;

use MyBB\Parser\Exceptions\ParserInvalidClassException;
use MyBB\Parser\Parser\IParser;

class ParserFactory
{
    /**
     * @param $parser string
     *
     * @return \MyBB\Parser\Parser\IParser
     *
     * @throws ParserInvalidClassException Thrown if the specified parser could not be loaded.
     */
    public static function make($parser)
    {
        $class = "MyBB\\Parser\\Parser\\" . ucfirst($parser);
        $instance = app()->make($class);

        if (!$instance || !($instance instanceof IParser)) {
            throw new ParserInvalidClassException($class);
        }

        return $instance;
    }
}
