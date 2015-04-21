<?php

namespace MyBB\Parser\Smilies;

use Illuminate\Contracts\Foundation\Application;

class SmilieRepository implements SmilieRepositoryInterface
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
	public function getParsableSmilies()
	{
		$smilies = $this->get();
		$prepared = array();
		foreach ($smilies as $smilie) {
			$smilie->find = str_replace("\r", "", $smilie->find);
			$smilie->find = explode("\n", $smilie->find);
			foreach ($smilie->find as $s) {
				$s = $this->filterHtml($s);

				$prepared[$s] = $this->getSmilieCode($smilie->image, $smilie->sid, $s);

				// workaround for smilies starting with ;
				if ($s[0] == ";") {
					$prepared += array(
						"&amp$s" => "&amp$s",
						"&lt$s" => "&lt$s",
						"&gt$s" => "&gt$s",
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

		return $db->table('parser_smilies')->orderBy('disporder')->get(['sid', 'find', 'image']);
	}

	/**
	 * @param string $message
	 *
	 * @return string
	 */
	private function filterHtml($message)
	{
		$message = preg_replace("#&(?!\#[0-9]+;)#si", "&amp;", $message); // fix & but allow unicode
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

		return str_replace([':image', ':alt', ':id'], [$image, $alt, $id], $code);
	}
}
