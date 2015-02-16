<?php namespace MyBB\Parser;

use MyBB\Parser\Parser\IParser;
use Psy\Exception\RuntimeException;

class ParserFactory
{
    /**
     * @param $parser string
     * @return \MyBB\Parser\Parser\IParser
     */
    public static function make($parser)
    {
        $class = "MyBB\\Parser\\Parser\\" . ucfirst($parser);
        $app = app();
        $i = $app->make($class);

        if (!$i || !($i instanceof IParser)) {
            throw new RuntimeException("Couldn't load parser {$class}");
        }

        return $i;
    }
}