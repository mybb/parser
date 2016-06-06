<?php
/**
 * Repository to retrieve bad words using Eloquent.
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace Mybb\Parser\Database\Repositories\Eloquent;

use Illuminate\Support\Collection;
use MyBB\Parser\Database\Models\BadWord;
use MyBB\Parser\Database\Repositories\BadWordRepositoryInterface;

class BadWordRepository implements BadWordRepositoryInterface
{
	/**
	 * @var BadWord $model
	 */
	private $model;

	/**
	 * @param BadWord $model
	 */
	public function __construct(BadWord $model)
	{
		$this->model = $model;
	}

	/**
	 * Get all bad words.
	 *
	 * @return Collection
	 */
	function getAll()
	{
		return $this->model->all();
	}

	/**
	 * Get all of the defined bad words as an array ready for parsing.
	 *
	 * Bad words should be returned in the form [find => replace].
	 *
	 * @return array
	 */
	function getAllForParsing()
	{
		return $this->model->lists('replace', 'find');
	}
}
