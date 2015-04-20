<?php

namespace MyBB\Parser\Parser;

use League\CommonMark\CommonMarkConverter;

class Markdown implements IParser
{
	/** @var CommonMarkConverter $converter */
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
	 * @param bool   $allowHTML
	 *
	 * @return string
	 */
	public function parse($message, $allowHTML)
	{
		return $this->converter->convertToHtml($message);
	}

	/**
	 * @param $message
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
	 * @param $format
	 */
	public function setDateFormatting($format)
	{
		// TODO: Implement setDateFormatting() method.
	}

	/**
	 * @param $url
	 */
	public function setPostURL($url)
	{
		// TODO: Implement setPostURL() method.
	}
}
