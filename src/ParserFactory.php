<?php

namespace MyBB\Parser;

use MyBB\Parser\Parser\ParserInterface;

class ParserFactory
{
	/**
	 * @param string $parser
	 *
	 * @return \MyBB\Parser\Parser\ParserInterface
	 *
	 * @throws \RuntimeException Thrown if the specified parser could not be loaded.
	 */
	public static function make($parser)
	{
		$class = "MyBB\\Parser\\Parser\\" . ucfirst($parser);
		$instance = app()->make($class);

		if (!$instance || !($instance instanceof ParserInterface)) {
			throw new \RuntimeException("Couldn't load parser {$class}");
		}

		return $instance;
	}
}
