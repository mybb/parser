<?php
/**
 * Smilie repository using Eloquent to retrieve smilies from the database.
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser\Database\Repositories\Eloquent;

use Illuminate\Support\Collection;
use Mybb\Parser\Database\Models\Smiley;
use Mybb\Parser\Database\Repositories\SmileyRepositoryInterface;

class SmileyRepository implements SmileyRepositoryInterface
{
	/**
	 * @var Smiley $model
	 */
	protected $model;

	/**
	 * @param Smiley $model The smiley model to use.
	 */
	public function __construct(Smiley $model)
	{
		$this->model = $model;
	}

	/**
	 * @return array
	 */
	public function getParseableSmileys()
	{
		$smilies = $this->get();
		$prepared = array();
		foreach ($smilies as $smilie) {
			$smilie->find = str_replace("\r", "", $smilie->find);
			$smilie->find = explode("\n", $smilie->find);
			foreach ($smilie->find as $s) {
				$s = $this->filterHtml($s);

				$prepared[$s] = $this->getSmilieCode(
					$smilie->image,
					$smilie->sid,
					$s
				);

				// workaround for smilies starting with ;
				if ($s[0] == ";") {
					$prepared += array(
						"&amp$s" => "&amp$s",
						"&lt$s"  => "&lt$s",
						"&gt$s"  => "&gt$s",
					);
				}
			}
		}

		return $prepared;
	}

	/**
	 * @return array
	 */
	private function get()
	{
		$db = $this->app->make('db');

		return $db->table('parser_smilies')->orderBy('disporder')->get(
			['sid', 'find', 'image']
		);
	}

	/**
	 * @param string $message
	 *
	 * @return string
	 */
	private function filterHtml($message)
	{
		$message = preg_replace(
			"#&(?!\#[0-9]+;)#si",
			"&amp;",
			$message
		); // fix & but allow unicode
		$message = str_replace("<", "&lt;", $message);
		$message = str_replace(">", "&gt;", $message);

		return $message;
	}

	/**
	 * @param string $image
	 * @param int    $id
	 * @param string $alt
	 *
	 * @return string
	 */
	private function getSmilieCode($image, $id, $alt = '')
	{
		$code = '<img src=":image" alt=":alt" title=":alt" class="smilie smilie_:id">';

		return str_replace(
			[':image', ':alt', ':id'],
			[$image, $alt, $id],
			$code
		);
	}

	/**
	 * Get all defined smileys.
	 *
	 * @return Collection
	 */
	public function getAll()
	{
		return $this->model->orderBy('disporder')->all();
	}
}
