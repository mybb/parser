<?php
/**
 * Minimum search count exception
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser\Exceptions;

use \RuntimeException;

class ParserSearchWordMinimumException extends RuntimeException
{
	/**
	 * @var string
	 */
	protected $message = 'parser::exceptions.search_word_minimum';

	/**
	 * @param null       $message
	 * @param int        $code
	 * @param \Exception $previous
	 */
	public function __construct($message = null, $code = 0, \Exception $previous = null)
	{
		if ($message === null) {
			$message = trans($this->message);
		}

		parent::__construct($message, $code, $previous);
	}
}
