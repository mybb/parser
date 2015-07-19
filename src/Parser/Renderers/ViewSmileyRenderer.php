<?php
/**
 * Rendererer for smileys utilising the view system.
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser\Parser\Renderers;

use Illuminate\Contracts\View\Factory;

class ViewSmileyRenderer implements SmileyRendererInterface
{
	const VIEW_PATH = 'parser::smiley';
	/**
	 * @var Factory $viewFactory
	 */
	private $viewFactory;

	/**
	 * @param Factory $viewFactory
	 */
	public function __construct(Factory $viewFactory)
	{
		$this->viewFactory = $viewFactory;
	}

	/**
	 * Render a smiley.
	 *
	 * @param string $search The search for the smiley.
	 * @param string $replace The replacement for the smiley.
	 *
	 * @return string The rendered smiley.
	 */
	public function render($search, $replace)
	{
		return $this->viewFactory->make(
			static::VIEW_PATH,
			compact(
				'search',
				'replace'
			)
		)->render();
	}
}