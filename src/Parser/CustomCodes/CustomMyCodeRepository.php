<?php
/**
 * Default custom code parser
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/auth
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser\Parser\CustomCodes;

use Illuminate\Contracts\Foundation\Application;

class CustomMyCodeRepository implements CustomCodeRepositoryInterface
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
	public function getParsableCodes()
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
		$db = $this->app->make('db');

		return $db->table('parser_mycode')->orderBy('parseorder')->get(['regex', 'replacement']);
	}
}
