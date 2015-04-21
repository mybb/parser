<?php

namespace MyBB\Parser;

use MyBB\Parser\Exceptions\ParserInvalidClassException;
use MyBB\Parser\Parser\ParserInterface;

class ParserFactory
{
	/**
	 * @param string $parser
	 *
	 * @return \MyBB\Parser\Parser\ParserInterface
	 *
	 * @throws ParserInvalidClassException($class) Thrown if the specified parser could not be loaded.
	 */
	public static function make($parser)
	{
		$class = "MyBB\\Parser\\Parser\\" . ucfirst($parser);
		$instance = app()->make($class);

		if (!$instance || !($instance instanceof ParserInterface)) {
			throw new ParserInvalidClassException($class);
		}

		return $instance;
	}
}
