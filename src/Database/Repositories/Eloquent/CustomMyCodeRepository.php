<?php
/**
 * Default custom code parser
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser\Database\Repositories\Eloquent;

use MyBB\Parser\Database\Models\MyCode;
use MyBB\Parser\Database\Repositories\CustomMyCodeRepositoryInterface;

class CustomMyMyCodeRepository implements CustomMyCodeRepositoryInterface
{
	/**
	 * @var MyCode $model
	 */
	private $model;

	/**
	 * @param MyCode $model
	 */
	public function __construct(MyCode $model)
	{
		$this->model = $model;
	}

	/**
	 * @return array
	 */
	public function getParseableCodes()
	{
		$codes = $this->get();
		$prepared = array();
		foreach ($codes as $code) {
			$prepared[] = ['regex' => $code->regex, 'replacement' => $code->replacement];
		}

		return $prepared;
	}

	/**
	 * @return array
	 */
	private function get()
	{
		return $this->model->orderBy('parseorder')->get(['regex', 'replacement']);
	}
}
