<?php
/**
 * Interface to render a smiley code into an image or emoji.
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser\Parser\Renderers;

interface SmileyRendererInterface
{
	/**
	 * Render a smiley.
	 *
	 * @param string $search The search for the smiley.
	 * @param string $replace The replacement for the smiley.
	 *
	 * @return string The rendered smiley.
	 */
	public function render($search, $replace);
}