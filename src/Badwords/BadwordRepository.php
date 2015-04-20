<?php

namespace MyBB\Parser\Badwords;

use Illuminate\Contracts\Foundation\Application;

class BadwordRepository implements IBadwordRepository
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
