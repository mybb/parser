<?php
/**
 * Interface for repositories used to get smiley codes.
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace Mybb\Parser\Database\Repositories;

use Illuminate\Support\Collection;

interface SmileyRepositoryInterface
{
	/**
	 * Get all of the defined smileys.
	 *
	 * Smileys should be returned as an array of ['find' => 'replace'].
	 *
	 * @return array
	 */
	public function getAllForParsing();

	/**
	 * Get all defined smileys.
	 *
	 * @return Collection
	 */
	public function getAll();
}
