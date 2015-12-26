<?php
/**
 * Markdown parser
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser\Parser;

use League\CommonMark\CommonMarkConverter;

class Markdown implements ParserInterface
{
	/**
	 * @var CommonMarkConverter
	 */
	private $converter;

	/**
	 * @param CommonMarkConverter $converter
	 */
	public function __construct(CommonMarkConverter $converter)
	{
		$this->converter = $converter;
	}

	/**
	 * @param string $message
	 * @param bool $allowHTML
	 *
	 * @return string
	 */
	public function parse($message, $allowHTML)
	{
		return $this->converter->convertToHtml($message);
	}

	/**
	 * @param string $message
	 *
	 * @return string
	 */
	public function parsePlain($message)
	{
		return preg_replace("#\*\*(.*?)\*\*#si", "$1", $message);
	}

	/**
	 * @param bool $allow
	 */
	public function allowBasicCode($allow = true)
	{
		// TODO: Implement allowBasicCode() method.
	}

	/**
	 * @param bool $allow
	 */
	public function allowSymbolCode($allow = true)
	{
		// TODO: Implement allowSymbolCode() method.
	}

	/**
	 * @param bool $allow
	 */
	public function allowLinkCode($allow = true)
	{
		// TODO: Implement allowLinkCode() method.
	}

	/**
	 * @param bool $allow
	 */
	public function allowEmailCode($allow = true)
	{
		// TODO: Implement allowEmailCode() method.
	}

	/**
	 * @param bool $allow
	 */
	public function allowColorCode($allow = true)
	{
		// TODO: Implement allowColorCode() method.
	}

	/**
	 * @param bool $allow
	 */
	public function allowSizeCode($allow = true)
	{
		// TODO: Implement allowSizeCode() method.
	}

	/**
	 * @param bool $allow
	 */
	public function allowFontCode($allow = true)
	{
		// TODO: Implement allowFontCode() method.
	}

	/**
	 * @param bool $allow
	 */
	public function allowAlignCode($allow = true)
	{
		// TODO: Implement allowAlignCode() method.
	}

	/**
	 * @param bool $allow
	 */
	public function allowImgCode($allow = true)
	{
		// TODO: Implement allowImgCode() method.
	}

	/**
	 * @param bool $allow
	 */
	public function allowVideoCode($allow = true)
	{
		// TODO: Implement allowVideoCode() method.
	}

	/**
	 * @param bool $short
	 */
	public function shortenURLs($short = true)
	{
		// TODO: Implement shortenURLs() method.
	}

	/**
	 * @param bool $on
	 */
	public function useNoFollow($on = true)
	{
		// TODO: Implement useNofollow() method.
	}

	/**
	 * @param string|callable $format
	 */
	public function setDateFormatting($format)
	{
		// TODO: Implement setDateFormatting() method.
	}

	/**
	 * @param string|callable $url
	 */
	public function setPostURL($url)
	{
		// TODO: Implement setPostURL() method.
	}
}
