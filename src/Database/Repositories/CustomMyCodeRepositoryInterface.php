<?php
/**
 * Interface for repositories used to get MyCodes.
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser\Database\Repositories;

interface CustomMyCodeRepositoryInterface
{
	/**
	 * @return array
	 */
	public function getParseableCodes();
}
