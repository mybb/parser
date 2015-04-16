<?php

namespace MyBB\Parser\Exceptions;

use \RuntimeException;

class ParserSearchWordMinimumException extends RuntimeException
{

	protected $message = 'parser::exceptions.search_word_minimum';

	public function __construct($message = null, $code = 0, \Exception $previous = null)
	{
		if ($message === null) {
			$message = trans($this->message);
		}

		parent::__construct($message, $code, $previous);
	}
}
