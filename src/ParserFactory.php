<?php

namespace MyBB\Parser;

use MyBB\Parser\Parser\IParser;

class ParserFactory
{
    /**
     * @param $parser string
     *
     * @return \MyBB\Parser\Parser\IParser
     *
     * @throws \RuntimeException Thrown if the specified parser could not be loaded.
     */
    public static function make($parser)
    {
        $class = "MyBB\\Parser\\Parser\\" . ucfirst($parser);
        $instance = app()->make($class);

        if (!$instance || !($instance instanceof IParser)) {
            throw new \RuntimeException("Couldn't load parser {$class}");
        }

        return $instance;
    }
}
