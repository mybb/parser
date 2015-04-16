<?php

namespace MyBB\Parser\Exceptions;

use \RuntimeException;

class ParserInvalidClassException extends RuntimeException
{

	protected $message = 'parser::exceptions.invalid_class';

	public function __construct($class, $code = 0, \Exception $previous = null)
	{
		$message = trans($this->message, compact('class'));

		parent::__construct($message, $code, $previous);
	}
}
