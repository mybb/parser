<?php
/**
 * Parse badwords
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/auth
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser\Badwords;

interface BadwordRepositoryInterface
{
	/**
	 * @return array
	 */
	public function getAllAsArray();
}
