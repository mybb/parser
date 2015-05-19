<?php
/**
 * Invalid parser class
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser\Exceptions;

use RuntimeException;

class ParserInvalidClassException extends RuntimeException
{
	/**
	 * @var string
	 */
	protected $message = 'parser::exceptions.invalid_class';

	/**
	 * @param string     $class
	 * @param int        $code
	 * @param \Exception $previous
	 */
	public function __construct($class, $code = 0, \Exception $previous = null)
	{
		$message = trans($this->message, compact('class'));

		parent::__construct($message, $code, $previous);
	}
}
