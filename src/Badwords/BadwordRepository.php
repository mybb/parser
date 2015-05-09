<?php
/**
 * Default badword parser
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/auth
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser\Badwords;

use Illuminate\Contracts\Foundation\Application;

class BadwordRepository implements BadwordRepositoryInterface
{
	/**
	 * @var Application
	 */
	private $app;

	/**
	 * @param Application $app
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * @return array
	 */
	public function getAllAsArray()
	{
		$badwords = $this->get();
		$prepared = array();
		foreach ($badwords as $badword) {
			$prepared[$badword->find] = $badword->replace;
		}

		return $prepared;
	}

	/**
	 * @return array
	 */
	private function get()
	{
		$db = $this->app->make('db');

		return $db->table('parser_badwords')->get(['find', 'replace']);
	}
}
